import { useContext, useEffect, useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';
import { LoadTemplatePayload, TemplateState } from '../types/elements';
import { debugLog, debugError } from '../utils/debug';

// Extension de Window pour pdfBuilderData
declare global {
  interface Window {
    pdfBuilderData?: {
      ajaxUrl: string;
      nonce: string;
    };
  }
}

export function useTemplate() {
  const { state, dispatch } = useBuilder();

  // Détecter si on est sur un template existant via l'URL
  const getTemplateIdFromUrl = (): string | null => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('template_id');
  };

  const isEditingExistingTemplate = (): boolean => {
    return getTemplateIdFromUrl() !== null;
  };

  // Charger un template existant
  const loadExistingTemplate = useCallback(async (templateId: string) => {
    debugLog('🔄 [LOAD TEMPLATE] Début du chargement du template:', templateId);
    try {
      // Faire un appel API pour récupérer les données du template
      const response = await fetch(`${window.pdfBuilderData?.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${window.pdfBuilderData?.nonce}`);

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      debugLog('📡 [LOAD TEMPLATE] Réponse API reçue:', result);

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors du chargement du template');
      }

      const templateData = result.data;
      debugLog('📊 [LOAD TEMPLATE] Données du template:', templateData);

      // Parse JSON strings
      let elements = [];
      let canvas = null;
      try {
        debugLog('🔍 [LOAD TEMPLATE] Parsing elements:', typeof templateData.elements, templateData.elements);
        // Check if elements is already an object or needs parsing
        if (typeof templateData.elements === 'string') {
          elements = JSON.parse(templateData.elements);
          debugLog('✅ [LOAD TEMPLATE] Elements parsed from string:', elements.length, 'éléments');
        } else if (Array.isArray(templateData.elements)) {
          elements = templateData.elements;
          debugLog('✅ [LOAD TEMPLATE] Elements already array:', elements.length, 'éléments');
        } else {
          elements = [];
          debugLog('⚠️ [LOAD TEMPLATE] Elements not string or array, using empty array');
        }

        debugLog('🔍 [LOAD TEMPLATE] Parsing canvas:', typeof templateData.canvas, templateData.canvas);
        // Same for canvas
        if (typeof templateData.canvas === 'string') {
          canvas = JSON.parse(templateData.canvas);
          debugLog('✅ [LOAD TEMPLATE] Canvas parsed from string');
        } else if (templateData.canvas && typeof templateData.canvas === 'object') {
          canvas = templateData.canvas;
          debugLog('✅ [LOAD TEMPLATE] Canvas already object');
        } else {
          canvas = null;
          debugLog('⚠️ [LOAD TEMPLATE] Canvas not valid, using null');
        }
      } catch (parseError) {
        debugError('❌ [LOAD TEMPLATE] Erreur de parsing:', parseError);
        elements = [];
        canvas = null;
      }

      debugLog('🚀 [LOAD TEMPLATE] Dispatch LOAD_TEMPLATE avec:', {
        id: templateId,
        name: templateData.name,
        elementsCount: elements.length,
        canvas: canvas
      });

      dispatch({
        type: 'LOAD_TEMPLATE',
        payload: {
          id: templateId,
          name: templateData.name,
          elements: elements,
          canvas: canvas,
          lastSaved: new Date(templateData.updated_at)
        } as LoadTemplatePayload
      });

      return true;
    } catch (error) {
      debugError('❌ [LOAD TEMPLATE] Erreur lors du chargement:', error);
      return false;
    }
  }, [dispatch]);

  // 🎯 Écouter les événements de chargement de template depuis l'API globale
  useEffect(() => {
    const handleLoadTemplate = (event: Event) => {
      const customEvent = event as CustomEvent;
      const templateData = customEvent.detail;
      debugLog('📡 [useTemplate] Événement pdfBuilderLoadTemplate reçu:', templateData);
      debugLog('🔍 [useTemplate] Propriétés du templateData:', {
        hasElements: 'elements' in templateData,
        elementsType: typeof templateData.elements,
        elementsValue: templateData.elements,
        hasCanvas: 'canvas' in templateData,
        canvasType: typeof templateData.canvas,
        canvasValue: templateData.canvas,
        allKeys: Object.keys(templateData),
        elementsCount: Array.isArray(templateData.elements) ? templateData.elements.length : 'not array'
      });

      if (!templateData || !templateData.id) {
        debugError('❌ [useTemplate] Données de template invalides');
        return;
      }

      // Si les éléments sont déjà fournis, les utiliser directement
      if (templateData.elements && Array.isArray(templateData.elements)) {
        debugLog('✅ [useTemplate] Éléments fournis directement, chargement sans AJAX');
        dispatch({
          type: 'LOAD_TEMPLATE',
          payload: {
            id: templateData.id,
            name: templateData.name || 'Template',
            elements: templateData.elements,
            canvas: templateData.canvas || null,
            lastSaved: templateData.updated_at ? new Date(templateData.updated_at) : new Date()
          } as LoadTemplatePayload
        });
      } else {
        // Sinon, utiliser l'API AJAX
        debugLog('🔄 [useTemplate] Éléments non fournis, utilisation de l\'API AJAX');
        loadExistingTemplate(templateData.id).catch((error: unknown) => {
          debugError('❌ [useTemplate] Erreur lors du chargement du template:', error);
        });
      }
    };

    document.addEventListener('pdfBuilderLoadTemplate', handleLoadTemplate);
    return () => {
      document.removeEventListener('pdfBuilderLoadTemplate', handleLoadTemplate);
    };
  }, [loadExistingTemplate]);

  // Effet pour charger automatiquement un template existant au montage
  useEffect(() => {
    const templateId = getTemplateIdFromUrl();
    if (templateId) {
      loadExistingTemplate(templateId);
    }
  }, [loadExistingTemplate]);

  const saveTemplate = useCallback(async () => {
    dispatch({ type: 'SET_TEMPLATE_SAVING', payload: true });

    try {
      const templateId = getTemplateIdFromUrl();

      // Préparer les données à envoyer
      const formData = new FormData();
      formData.append('action', 'pdf_builder_save_template');
      formData.append('template_id', templateId || '0');
      formData.append('template_name', state.template.name || 'Template sans nom');
      formData.append('elements', JSON.stringify(state.elements));
      formData.append('canvas', JSON.stringify(state.canvas));
      formData.append('nonce', window.pdfBuilderData?.nonce || '');

      // Faire un appel API pour sauvegarder le template
      const response = await fetch(window.pdfBuilderData?.ajaxUrl || '', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors de la sauvegarde du template');
      }

      dispatch({
        type: 'SAVE_TEMPLATE',
        payload: {
          id: result.data.id,
          name: result.data.name
        }
      });
    } catch (error) {
      throw error; // Re-throw pour que l'appelant puisse gérer l'erreur
    } finally {
      dispatch({ type: 'SET_TEMPLATE_SAVING', payload: false });
    }
  }, [state.elements, state.canvas, state.template.name, dispatch]);

  const previewTemplate = useCallback(() => {
    dispatch({ type: 'SET_SHOW_PREVIEW_MODAL', payload: true });
  }, [dispatch]);

  const newTemplate = useCallback(() => {
    dispatch({ type: 'NEW_TEMPLATE' });
  }, [dispatch]);

  const setTemplateModified = useCallback((modified: boolean) => {
    dispatch({ type: 'SET_TEMPLATE_MODIFIED', payload: modified });
  }, [dispatch]);

  const updateTemplateSettings = useCallback((settings: Partial<TemplateState>) => {
    dispatch({ type: 'UPDATE_TEMPLATE_SETTINGS', payload: settings });
  }, [dispatch]);

  return {
    templateName: state.template.name,
    templateDescription: state.template.description,
    templateTags: state.template.tags,
    canvasWidth: state.template.canvasWidth,
    canvasHeight: state.template.canvasHeight,
    marginTop: state.template.marginTop,
    marginBottom: state.template.marginBottom,
    showGuides: state.template.showGuides,
    snapToGrid: state.template.snapToGrid,
    isNewTemplate: state.template.isNew,
    isModified: state.template.isModified,
    isSaving: state.template.isSaving,
    lastSaved: state.template.lastSaved,
    isEditingExistingTemplate: isEditingExistingTemplate(),
    saveTemplate,
    previewTemplate,
    newTemplate,
    setTemplateModified,
    updateTemplateSettings
  };
}