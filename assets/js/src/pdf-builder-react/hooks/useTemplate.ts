import { useEffect, useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';
import { useCanvasSettings } from '../contexts/CanvasSettingsContext.tsx';
import { LoadTemplatePayload, TemplateState } from '../types/elements';
import { debugError } from '../utils/debug';
import { normalizeElementsBeforeSave, normalizeElementsAfterLoad, debugElementState } from '../utils/elementNormalization';

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

      // Détecter le navigateur pour des en-têtes spécifiques
      const isChrome = typeof navigator !== 'undefined' &&
        /Chrome/.test(navigator.userAgent) &&
        /Google Inc/.test(navigator.vendor);

      const isFirefox = typeof navigator !== 'undefined' &&
        /Firefox/.test(navigator.userAgent);

      const isSafari = typeof navigator !== 'undefined' &&
        /Safari/.test(navigator.userAgent) &&
        !/Chrome/.test(navigator.userAgent) &&
        !/Chromium/.test(navigator.userAgent);

      // Préparer les options fetch avec des en-têtes spécifiques par navigateur
      const fetchOptions: RequestInit = {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          // En-têtes anti-cache spécifiques
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache',
          'Expires': '0'
        },
        // Mode spécifique selon le navigateur
        mode: 'cors' as RequestMode,
        credentials: 'same-origin' as RequestCredentials
      };

      // Ajustements spécifiques par navigateur
      if (isChrome) {
        // Chrome peut avoir besoin d'un mode plus permissif
        fetchOptions.mode = 'cors';
        fetchOptions.cache = 'no-cache';
        console.log('[useTemplate] Chrome détecté - Utilisation du mode CORS avec cache désactivé');
      } else if (isFirefox) {
        // Firefox gère bien le cache par défaut
        fetchOptions.cache = 'no-cache';
        console.log('[useTemplate] Firefox détecté - Cache désactivé');
      } else if (isSafari) {
        // Safari peut avoir des problèmes avec certains modes
        fetchOptions.mode = 'cors';
        console.log('[useTemplate] Safari détecté - Mode CORS forcé');
      }

      const url = `${window.pdfBuilderData?.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${window.pdfBuilderData?.nonce}&t=${cacheBreaker}`;
      console.log('[useTemplate] Fetch URL:', url);
      console.log('[useTemplate] Fetch options:', fetchOptions);

      const response = await fetch(url, fetchOptions);
      console.log('[useTemplate] AJAX response status:', response.status);
      console.log('[useTemplate] AJAX response headers:', [...response.headers.entries()]);

      if (!response.ok) {
        const errorText = await response.text();
        console.error('[useTemplate] Response error text:', errorText);
        throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
      }

      const result = await response.json();
      console.log('[useTemplate] AJAX result success:', result.success);
      console.log('🔍 [TEMPLATE LOAD] Full AJAX response:', result);
      console.log('🔍 [TEMPLATE LOAD] Response keys:', Object.keys(result));
      console.log('🔍 [TEMPLATE LOAD] result.data:', result.data);
      console.log('🔍 [TEMPLATE LOAD] result.data keys:', result.data ? Object.keys(result.data) : 'NO DATA');
      
      // Log debug info from server
      if (result.data && result.data.debug) {
        console.log('🔍 [TEMPLATE LOAD] SERVER DEBUG INFO:', result.data.debug);
        if (result.data.debug.order_element) {
          console.log('🔍 [TEMPLATE LOAD] ORDER ELEMENT FROM SERVER:', result.data.debug.order_element);
          console.log('🔍 [TEMPLATE LOAD] ORDER ELEMENT JSON:', result.data.debug.order_element_json);
          console.log('🔍 [TEMPLATE LOAD] HAS contentAlign:', result.data.debug.has_contentAlign);
          console.log('🔍 [TEMPLATE LOAD] HAS labelPosition:', result.data.debug.has_labelPosition);
          console.log('🔍 [TEMPLATE LOAD] contentAlign VALUE:', result.data.debug.contentAlign_value);
          console.log('🔍 [TEMPLATE LOAD] labelPosition VALUE:', result.data.debug.labelPosition_value);
        }
      }

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors du chargement du template');
      }

      const templateData = result.data ? result.data.template : result.template;
      const templateName = result.data ? (result.data.template_name || result.data.name) : (result.name || result.template_name);
      console.log('🔍 [TEMPLATE LOAD] Extracted templateData:', templateData);
      console.log('🔍 [TEMPLATE LOAD] Extracted templateName:', templateName);
      console.log('🔍 [TEMPLATE LOAD] templateData type:', typeof templateData);
      console.log('🔍 [TEMPLATE LOAD] templateData keys:', templateData ? Object.keys(templateData) : 'NULL');
      console.log('🔍 [TEMPLATE LOAD] templateData.elements:', templateData?.elements);
      console.log('🔍 [TEMPLATE LOAD] templateData.elements type:', typeof templateData?.elements);
      console.log('[useTemplate] Template loaded - elements count:', templateData.elements ? templateData.elements.length : 0);
      console.log('[useTemplate] Template name:', templateName);

      
      // 🔍 Tracer les éléments reçus du serveur
      if (templateData.elements) {
        console.log('🔍 [TEMPLATE LOAD] Raw elements from server:', templateData.elements);
        console.log('🔍 [TEMPLATE LOAD] First element from server:', templateData.elements[0]);
        
        // 🔍 Vérifier spécifiquement les éléments order_number
        const orderNumberElements = templateData.elements.filter(el => el.type === 'order_number');
        console.log('🔍 [TEMPLATE LOAD] Order number elements found:', orderNumberElements.length);
        if (orderNumberElements.length > 0) {
          console.log('🔍 [TEMPLATE LOAD] First order_number element properties:', orderNumberElements[0].properties);
          console.log('🔍 [TEMPLATE LOAD] First order_number element contentAlign:', orderNumberElements[0].properties?.contentAlign);
          console.log('🔍 [TEMPLATE LOAD] First order_number element labelPosition:', orderNumberElements[0].properties?.labelPosition);
        }
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

      // ✅ NORMALISER LES ÉLÉMENTS APRÈS CHARGE (CRITIQUE!)
      // Cela garantit que contentAlign, labelPosition, etc. sont préservés
      const normalizedElements = normalizeElementsAfterLoad(elements as any);
      debugElementState(normalizedElements as any, 'APRÈS CHARGEMENT');

      // 🏷️ Enrichir les éléments company_logo avec src si manquant et convertir les dates
      const enrichedElements = normalizedElements.map((el: Record<string, unknown>) => {
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
          name: templateName || templateData.name,
          elements: enrichedElements,
          canvas: canvasData,
          lastSaved: lastSavedDate
        } as LoadTemplatePayload
      });

      return true;
    } catch (error) {
      debugError('❌ [LOAD TEMPLATE] Erreur lors du chargement:', error);

      // Diagnostics spécifiques selon le navigateur
      const isChrome = typeof navigator !== 'undefined' &&
        /Chrome/.test(navigator.userAgent) &&
        /Google Inc/.test(navigator.vendor);

      const isFirefox = typeof navigator !== 'undefined' &&
        /Firefox/.test(navigator.userAgent);

      const isSafari = typeof navigator !== 'undefined' &&
        /Safari/.test(navigator.userAgent) &&
        !/Chrome/.test(navigator.userAgent) &&
        !/Chromium/.test(navigator.userAgent);

      console.error(`❌ [LOAD TEMPLATE] Échec du chargement sur ${isChrome ? 'Chrome' : isFirefox ? 'Firefox' : isSafari ? 'Safari' : 'navigateur inconnu'}`);
      console.error('❌ [LOAD TEMPLATE] Détails de l\'erreur:', {
        message: error.message,
        stack: error.stack,
        name: error.name,
        templateId: templateId,
        ajaxUrl: window.pdfBuilderData?.ajaxUrl,
        userAgent: navigator.userAgent
      });

      // Tentative de fallback pour Chrome
      if (isChrome && error.message.includes('fetch')) {
        console.warn('🔄 [LOAD TEMPLATE] Tentative de fallback pour Chrome - Nouvelle tentative avec options différentes');

        try {
          // Attendre un peu avant retry
          await new Promise(resolve => setTimeout(resolve, 1000));

          // Retry avec des options différentes
          const fallbackOptions: RequestInit = {
            method: 'GET',
            headers: {
              'Accept': 'application/json, text/plain, */*',
              'X-Requested-With': 'XMLHttpRequest'
            },
            mode: 'no-cors' as RequestMode,
            cache: 'reload'
          };

          const fallbackUrl = `${window.pdfBuilderData?.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${window.pdfBuilderData?.nonce}&fallback=1&t=${Date.now()}`;
          console.log('🔄 [LOAD TEMPLATE] Tentative fallback URL:', fallbackUrl);

          const fallbackResponse = await fetch(fallbackUrl, fallbackOptions);
          console.log('🔄 [LOAD TEMPLATE] Fallback response status:', fallbackResponse.status);

          if (fallbackResponse.ok || fallbackResponse.status === 0) { // no-cors peut retourner status 0
            console.log('✅ [LOAD TEMPLATE] Fallback réussi !');
            // Traiter la réponse même si elle est opaque
            return true;
          }
        } catch (fallbackError) {
          console.error('❌ [LOAD TEMPLATE] Échec du fallback:', fallbackError);
        }
      }

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
    console.log('[useTemplate] useEffect - templateId:', templateId);
    console.log('[useTemplate] useEffect - state.template:', state.template);

    if (templateId) {
      console.log('[useTemplate] Loading existing template:', templateId);
      loadExistingTemplate(templateId);
    } else {
      console.log('[useTemplate] No template ID found, creating new template');
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

      // ✅ NORMALISER LES ÉLÉMENTS AVANT SAUVEGARDE
      // Cela garantit que contentAlign, labelPosition, etc. ne sont jamais perdus
      const normalizedElements = normalizeElementsBeforeSave(state.elements as any);
      debugElementState(normalizedElements as any, 'AVANT SAUVEGARDE');

      // Structure simple et propre pour la sauvegarde
      const templateData = {
        elements: normalizedElements,
        canvasWidth: canvasWidth,
        canvasHeight: canvasHeight,
        version: '1.0'
      };

      console.log('🔍 [TEMPLATE SAVE] About to send templateData:', templateData);
      console.log('🔍 [TEMPLATE SAVE] templateData.elements length:', templateData.elements.length);
      console.log('🔍 [TEMPLATE SAVE] First element keys:', Object.keys(templateData.elements[0] || {}));

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
