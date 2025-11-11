import { useEffect, useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';
import { useCanvasSettings } from '../contexts/CanvasSettingsContext.tsx';
import { LoadTemplatePayload, TemplateState } from '../types/elements';
import { debugError } from '../utils/debug';

export function useTemplate() {
  const { state, dispatch } = useBuilder();
  const { canvasWidth, canvasHeight } = useCanvasSettings();

  // Détecter si on est sur un template existant via l'URL ou les données localisées
  const getTemplateIdFromUrl = useCallback((): string | null => {
    // Priorité 1: Utiliser le templateId des données PHP localisées
    if (window.pdfBuilderData?.templateId) {

      return window.pdfBuilderData.templateId.toString();
    }
    
    // Priorité 2: Utiliser le paramètre URL (pour compatibilité)
    const urlParams = new URLSearchParams(window.location.search);
    const urlTemplateId = urlParams.get('template_id');
    if (urlTemplateId) {

      return urlTemplateId;
    }
    

    return null;
  }, []);

  const isEditingExistingTemplate = (): boolean => {
    return getTemplateIdFromUrl() !== null;
  };

  // Charger un template existant
  const loadExistingTemplate = useCallback(async (templateId: string) => {
    console.log('[useTemplate] Loading template with ID:', templateId);

    try {
      // ✅ CRITICAL: Add timestamp to AJAX URL to prevent caching
      // This ensures F5 and Ctrl+F5 load fresh data from server
      // The server also sends no-cache headers, this is backup
      const cacheBreaker = Date.now();
      const response = await fetch(`${window.pdfBuilderData?.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${window.pdfBuilderData?.nonce}&t=${cacheBreaker}`);
      console.log('[useTemplate] AJAX response status:', response.status);

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      console.log('[useTemplate] AJAX result success:', result.success);
      console.log('🔍 [TEMPLATE LOAD] Full AJAX response:', result);
      console.log('🔍 [TEMPLATE LOAD] Response keys:', Object.keys(result));

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors du chargement du template');
      }

      const templateData = result.data ? result.data.template : result.template;
      console.log('🔍 [TEMPLATE LOAD] Extracted templateData:', templateData);
      console.log('🔍 [TEMPLATE LOAD] templateData type:', typeof templateData);
      console.log('🔍 [TEMPLATE LOAD] templateData keys:', templateData ? Object.keys(templateData) : 'NULL');
      console.log('[useTemplate] Template loaded - elements count:', templateData.elements ? templateData.elements.length : 0);

      
      // 🔍 Tracer les éléments reçus du serveur
      if (templateData.elements) {
        // Log elements for debugging
      }

      // Parse JSON strings
      let elements = [];
      let canvasData = null;
      try {

        // Check if elements is already an object or needs parsing
        if (typeof templateData.elements === 'string') {
          elements = JSON.parse(templateData.elements);

        } else if (Array.isArray(templateData.elements)) {
          elements = templateData.elements;

        } else {
          elements = [];

        }

        console.log('[useTemplate] LOAD - parsed elements count:', elements.length);

        // 🔍 Log détaillé des éléments order_number chargés (même format que sauvegarde)
        const loadedOrderNumberElements = elements.filter((el: Record<string, unknown>) => el.type === 'order_number');
        console.log('🔍 [TEMPLATE LOAD] Order number elements loaded:', loadedOrderNumberElements.length);
        loadedOrderNumberElements.forEach((el: Record<string, unknown>, index: number) => {
          console.log(`🔍 [TEMPLATE LOAD] Order element ${index}:`, {
            id: el.id,
            contentAlign: el.contentAlign || 'NOT SET',
            labelPosition: el.labelPosition || 'NOT SET',
            showLabel: el.showLabel,
            labelText: el.labelText || 'NOT SET',
            allProperties: Object.keys(el)
          });
        });


        // ✅ CORRECTION: Support both old format (canvas: {width, height}) and new format (canvasWidth, canvasHeight)
        if (templateData.canvasWidth && templateData.canvasHeight) {
          canvasData = {
            width: templateData.canvasWidth,
            height: templateData.canvasHeight
          };

        } else if (typeof templateData.canvas === 'string') {
          canvasData = JSON.parse(templateData.canvas);

        } else if (templateData.canvas && typeof templateData.canvas === 'object') {
          canvasData = templateData.canvas;

        } else {
          canvasData = { width: 210, height: 297 };

        }
      } catch (parseError) {
        debugError('❌ [LOAD TEMPLATE] Erreur de parsing:', parseError);
        elements = [];
        canvasData = { width: 210, height: 297 };
      }

      // 🏷️ Enrichir les éléments company_logo avec src si manquant et convertir les dates
      const enrichedElements = elements.map((el: Record<string, unknown>) => {
        let enrichedElement = { ...el };
        
        // ✅ CORRECTION: Enrichir les éléments company_logo SEULEMENT si src ET logoUrl sont vides
        if (el.type === 'company_logo' && !el.src && !el.logoUrl) {
          
          // Essayer d'obtenir le logo depuis les propriétés de l'élément
          const logoUrl = (el.defaultSrc as string) || '';
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


      enrichedElements.slice(0, 3).forEach((_el: Record<string, unknown>, _idx: number) => {

      });

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

      // 🔍 Log final des éléments order_number avant envoi au contexte
      const finalOrderNumberElements = enrichedElements.filter((el: Record<string, unknown>) => el.type === 'order_number');
      console.log('🔍 [TEMPLATE LOAD] Final order number elements before dispatch:', finalOrderNumberElements.length);
      finalOrderNumberElements.forEach((el: Record<string, unknown>, index: number) => {
        console.log(`🔍 [TEMPLATE LOAD] Final order element ${index} before dispatch:`, {
          id: el.id,
          contentAlign: el.contentAlign || 'NOT SET',
          labelPosition: el.labelPosition || 'NOT SET',
          showLabel: el.showLabel,
          labelText: el.labelText || 'NOT SET',
          allProperties: Object.keys(el)
        });
      });

      dispatch({
        type: 'LOAD_TEMPLATE',
        payload: {
          id: templateId,
          name: templateData.name,
          elements: enrichedElements,
          canvas: canvasData,
          lastSaved: lastSavedDate
        } as LoadTemplatePayload
      });

      return true;
    } catch (error) {
      debugError('❌ [LOAD TEMPLATE] Erreur lors du chargement:', error);
      return false;
    }
  }, [dispatch]);

  // 🎯 DISABLED: Event-based template loading causes race conditions with useEffect
  // Both methods try to load the same template, causing flashing/alternating canvas
  // The useEffect approach (reading URL) is more reliable and runs once per page load
  useEffect(() => {
    // ✅ Event listener disabled to prevent race conditions
    // Only useEffect with URL reading will load templates now
    return () => {
      // cleanup
    };
  }, []);

  // Effet pour charger automatiquement un template existant au montage
  // ✅ Dépendance vide: charger une seule fois au montage du composant
  useEffect(() => {
    const templateId = getTemplateIdFromUrl();

    if (templateId) {
      loadExistingTemplate(templateId);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Sauvegarder un template manuellement
  const saveTemplate = useCallback(async () => {
    console.log('🔍 [TEMPLATE SAVE] Starting manual save...');

    dispatch({ type: 'SET_TEMPLATE_SAVING', payload: true });

    try {
      const templateId = getTemplateIdFromUrl();

      if (!templateId) {
        throw new Error('Aucun template chargé pour la sauvegarde');
      }

      console.log('🔍 [TEMPLATE SAVE] Template ID:', templateId);
      console.log('🔍 [TEMPLATE SAVE] Elements count:', state.elements.length);

      // Structure simple et propre pour la sauvegarde
      const templateData = {
        elements: state.elements,
        canvasWidth: canvasWidth,
        canvasHeight: canvasHeight,
        version: '1.0'
      };

      // Log détaillé des éléments order_number
      const orderNumberElements = templateData.elements.filter((el: Record<string, unknown>) => el.type === 'order_number');
      console.log('🔍 [TEMPLATE SAVE] Order number elements:', orderNumberElements.length);
      orderNumberElements.forEach((el: Record<string, unknown>, index: number) => {
        console.log(`🔍 [TEMPLATE SAVE] Order element ${index}:`, {
          id: el.id,
          contentAlign: el.contentAlign || 'NOT SET',
          labelPosition: el.labelPosition || 'NOT SET',
          showLabel: el.showLabel,
          labelText: el.labelText || 'NOT SET',
          allProperties: Object.keys(el)
        });
      });

      const formData = new FormData();
      formData.append('action', 'pdf_builder_save_template');
      formData.append('template_id', templateId);
      formData.append('template_name', state.template.name || 'Template sans nom');
      formData.append('template_data', JSON.stringify(templateData));
      formData.append('nonce', window.pdfBuilderData?.nonce || '');

      console.log('[useTemplate] SAVE - Sending request...');

      const response = await fetch(window.pdfBuilderData?.ajaxUrl || '', {
        method: 'POST',
        body: formData
      });

      console.log('[useTemplate] SAVE - Response status:', response.status);

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      console.log('[useTemplate] SAVE - Server response:', result);

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors de la sauvegarde');
      }

      console.log('[useTemplate] SAVE - Save successful, updating state');

      dispatch({
        type: 'SAVE_TEMPLATE',
        payload: {
          id: result.data.id,
          name: result.data.name
        }
      });

      console.log('[useTemplate] SAVE - State updated, save complete');

    } catch (error) {
      console.error('[useTemplate] SAVE - Error:', error);
      throw error;
    } finally {
      dispatch({ type: 'SET_TEMPLATE_SAVING', payload: false });
    }
  }, [state.elements, state.template.name, dispatch, canvasWidth, canvasHeight, getTemplateIdFromUrl]);

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
