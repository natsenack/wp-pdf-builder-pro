import { useContext, useEffect, useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';
import { LoadTemplatePayload, TemplateState } from '../types/elements';

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
  const loadExistingTemplate = async (templateId: string) => {
    console.log('🔄 [LOAD TEMPLATE] Début du chargement du template:', templateId);
    try {
      // Faire un appel API pour récupérer les données du template
      const response = await fetch(`${window.pdfBuilderData?.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${window.pdfBuilderData?.nonce}`);

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      console.log('📡 [LOAD TEMPLATE] Réponse API reçue:', result);

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors du chargement du template');
      }

      const templateData = result.data;
      console.log('📊 [LOAD TEMPLATE] Données du template:', templateData);

      // Parse JSON strings
      let elements = [];
      let canvas = null;
      try {
        console.log('🔍 [LOAD TEMPLATE] Parsing elements:', typeof templateData.elements, templateData.elements);
        // Check if elements is already an object or needs parsing
        if (typeof templateData.elements === 'string') {
          elements = JSON.parse(templateData.elements);
          console.log('✅ [LOAD TEMPLATE] Elements parsed from string:', elements.length, 'éléments');
        } else if (Array.isArray(templateData.elements)) {
          elements = templateData.elements;
          console.log('✅ [LOAD TEMPLATE] Elements already array:', elements.length, 'éléments');
        } else {
          elements = [];
          console.log('⚠️ [LOAD TEMPLATE] Elements not string or array, using empty array');
        }

        console.log('🔍 [LOAD TEMPLATE] Parsing canvas:', typeof templateData.canvas, templateData.canvas);
        // Same for canvas
        if (typeof templateData.canvas === 'string') {
          canvas = JSON.parse(templateData.canvas);
          console.log('✅ [LOAD TEMPLATE] Canvas parsed from string');
        } else if (templateData.canvas && typeof templateData.canvas === 'object') {
          canvas = templateData.canvas;
          console.log('✅ [LOAD TEMPLATE] Canvas already object');
        } else {
          canvas = null;
          console.log('⚠️ [LOAD TEMPLATE] Canvas not valid, using null');
        }
      } catch (parseError) {
        console.error('❌ [LOAD TEMPLATE] Erreur de parsing:', parseError);
        elements = [];
        canvas = null;
      }

      console.log('🚀 [LOAD TEMPLATE] Dispatch LOAD_TEMPLATE avec:', {
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

    } catch (error) {
      // En cas d'erreur, on peut afficher un message d'erreur à l'utilisateur
    }
  };

  // Effet pour charger automatiquement un template existant au montage
  useEffect(() => {
    const templateId = getTemplateIdFromUrl();
    if (templateId) {
      loadExistingTemplate(templateId);
    }
  }, []);

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