import { useEffect, useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';
import { useCanvasSettings } from '../contexts/CanvasSettingsContext.tsx';
import { LoadTemplatePayload, TemplateState } from '../types/elements';
import { debugLog, debugError } from '../utils/debug';

export function useTemplate() {
  const { state, dispatch } = useBuilder();
  const { canvasWidth, canvasHeight } = useCanvasSettings();

  // Détecter si on est sur un template existant via l'URL ou les données localisées
  const getTemplateIdFromUrl = (): string | null => {
    // Priorité 1: Utiliser le templateId des données PHP localisées
    if (window.pdfBuilderData?.templateId) {
      debugLog('🔍 [useTemplate] Template ID from localized data:', window.pdfBuilderData.templateId);
      return window.pdfBuilderData.templateId.toString();
    }
    
    // Priorité 2: Utiliser le paramètre URL (pour compatibilité)
    const urlParams = new URLSearchParams(window.location.search);
    const urlTemplateId = urlParams.get('template_id');
    if (urlTemplateId) {
      debugLog('🔍 [useTemplate] Template ID from URL:', urlTemplateId);
      return urlTemplateId;
    }
    
    debugLog('⚠️ [useTemplate] No template ID found (neither localized data nor URL param)');
    return null;
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

      const templateData = result.data.template;
      debugLog('📊 [LOAD TEMPLATE] Données du template brutes:', templateData);
      
      // 🔍 Tracer les éléments reçus du serveur
      if (templateData.elements) {
        try {
          const elementsForDebug = typeof templateData.elements === 'string' 
            ? JSON.parse(templateData.elements)
            : templateData.elements;
          
          debugLog('🔍 [LOAD TEMPLATE] Éléments bruts du serveur:', {
            type: typeof templateData.elements,
            count: Array.isArray(elementsForDebug) ? elementsForDebug.length : 'N/A',
            firstElement: Array.isArray(elementsForDebug) ? elementsForDebug[0] : 'N/A',
            hasCompanyLogo: Array.isArray(elementsForDebug) ? elementsForDebug.some((e: Record<string, unknown>) => e.type === 'company_logo') : false,
            logoElements: Array.isArray(elementsForDebug) 
              ? elementsForDebug.filter((e: Record<string, unknown>) => e.type === 'company_logo')
              : []
          });
        } catch (e) {
          debugLog('🔍 [LOAD TEMPLATE] Could not trace elements:', e);
        }
      }

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

      // 🏷️ Enrichir les éléments company_logo avec src si manquant et convertir les dates
      const enrichedElements = elements.map((el: Record<string, unknown>) => {
        let enrichedElement = { ...el };
        
        // Enrichir les éléments company_logo avec src si manquant
        if (el.type === 'company_logo' && (!el.src || !el.logoUrl)) {
          debugLog('🏷️ [LOAD TEMPLATE] Logo sans src trouvé, recherche de src:', {
            elementId: el.id,
            currentSrc: el.src,
            currentLogoUrl: el.logoUrl,
            elementKeys: Object.keys(el)
          });
          
          // Essayer d'obtenir le logo depuis les propriétés de l'élément
          const logoUrl = (el.src as string) || (el.logoUrl as string) || (el.defaultSrc as string) || '';
          if (logoUrl) {
            enrichedElement.src = logoUrl;
          }
        }
        
        // Convertir les propriétés de date en objets Date valides
        if (enrichedElement.createdAt) {
          try {
            const createdAt = new Date(enrichedElement.createdAt as string | number | Date);
            enrichedElement.createdAt = isNaN(createdAt.getTime()) ? new Date() : createdAt;
          } catch {
            enrichedElement.createdAt = new Date();
          }
        } else {
          enrichedElement.createdAt = new Date();
        }
        
        if (enrichedElement.updatedAt) {
          try {
            const updatedAt = new Date(enrichedElement.updatedAt as string | number | Date);
            enrichedElement.updatedAt = isNaN(updatedAt.getTime()) ? new Date() : updatedAt;
          } catch {
            enrichedElement.updatedAt = new Date();
          }
        } else {
          enrichedElement.updatedAt = new Date();
        }
        
        return enrichedElement;
      });

      debugLog('📋 [LOAD TEMPLATE] Premiers éléments après enrichissement:', enrichedElements.slice(0, 2));

      // Créer une date valide pour lastSaved
      let lastSavedDate: Date;
      try {
        if (templateData.updated_at) {
          lastSavedDate = new Date(templateData.updated_at);
          // Vérifier si la date est valide
          if (isNaN(lastSavedDate.getTime())) {
            lastSavedDate = new Date();
          }
        } else {
          lastSavedDate = new Date();
        }
      } catch {
        lastSavedDate = new Date();
      }

      dispatch({
        type: 'LOAD_TEMPLATE',
        payload: {
          id: templateId,
          name: templateData.name,
          elements: enrichedElements,
          canvas: canvas,
          lastSaved: lastSavedDate
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
            lastSaved: templateData.updated_at ? (() => {
              try {
                const date = new Date(templateData.updated_at);
                return isNaN(date.getTime()) ? new Date() : date;
              } catch {
                return new Date();
              }
            })() : new Date()
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
  }, [loadExistingTemplate, dispatch]);

  // Effet pour charger automatiquement un template existant au montage
  // ✅ Dépendance vide: charger une seule fois au montage du composant
  useEffect(() => {
    const templateId = getTemplateIdFromUrl();
    debugLog('🔍 [useTemplate] Template ID from URL:', templateId, 'URL:', window.location.href);
    if (templateId) {
      debugLog('🔄 [useTemplate] Loading template:', templateId);
      loadExistingTemplate(templateId);
    } else {
      debugLog('⚠️ [useTemplate] No template ID in URL');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
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
      formData.append('canvas', JSON.stringify({
        width: canvasWidth,
        height: canvasHeight
      }));
      formData.append('nonce', window.pdfBuilderData?.nonce || '');

      console.log('💾 [SAVE TEMPLATE] Envoi des données:', {
        templateId,
        templateName: state.template.name,
        elementsCount: state.elements.length,
        canvasData: { width: canvasWidth, height: canvasHeight },
        firstElement: state.elements[0]
      });

      // Faire un appel API pour sauvegarder le template
      const response = await fetch(window.pdfBuilderData?.ajaxUrl || '', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      console.log('✅ [SAVE TEMPLATE] Réponse du serveur:', result);

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
      console.error('❌ [SAVE TEMPLATE] Erreur:', error);
      throw error; // Re-throw pour que l'appelant puisse gérer l'erreur
    } finally {
      dispatch({ type: 'SET_TEMPLATE_SAVING', payload: false });
    }
  }, [state.elements, state.template.name, dispatch, canvasWidth, canvasHeight]);

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
    isLoading: state.template.isLoading, // ✅ NEW: Template is loading
    lastSaved: state.template.lastSaved,
    isEditingExistingTemplate: isEditingExistingTemplate(),
    saveTemplate,
    previewTemplate,
    newTemplate,
    setTemplateModified,
    updateTemplateSettings
  };
}