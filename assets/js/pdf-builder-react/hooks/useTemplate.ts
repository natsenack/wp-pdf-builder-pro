import { useEffect, useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext';
import { useCanvasSettings } from '../contexts/CanvasSettingsContext';
import { LoadTemplatePayload, TemplateState } from '../types/elements';
import { debugError, debugWarn } from '../utils/debug';
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
    console.log('🔄 [useTemplate] loadExistingTemplate called with templateId:', templateId);
    console.log('🔄 [useTemplate] window.pdfBuilderData at start:', window.pdfBuilderData);
    console.log('🔄 [useTemplate] window.pdfBuilderData?.ajaxUrl:', window.pdfBuilderData?.ajaxUrl);
    console.log('🔄 [useTemplate] window.pdfBuilderData?.nonce:', window.pdfBuilderData?.nonce);
    console.log('🔄 [useTemplate] window keys containing pdfBuilder:', Object.keys(window).filter(key => key.includes('pdfBuilder')));
    console.log('🔄 [useTemplate] window.pdfBuilderData?.existingTemplate:', window.pdfBuilderData?.existingTemplate);
    console.log('🔄 [useTemplate] window.pdfBuilderData?.hasExistingData:', window.pdfBuilderData?.hasExistingData);

    try {
      // ✅ PRIORITÉ: Utiliser les données localisées si disponibles (plus rapide et fiable)
      if (window.pdfBuilderData?.existingTemplate && window.pdfBuilderData?.hasExistingData) {
        console.log('🔄 [useTemplate] USING LOCALIZED DATA PATH for template:', templateId);
        const templateData = window.pdfBuilderData.existingTemplate;
        console.log('🔄 [useTemplate] templateData:', templateData);
        console.log('🔄 [useTemplate] templateData.name:', templateData?.name);
        console.log('🔄 [useTemplate] templateData._db_name:', templateData?._db_name);
        console.log('🔄 [useTemplate] templateData keys:', Object.keys(templateData || {}));

        // Utiliser le nom du JSON en priorité (s'il existe et n'est pas vide), sinon le nom de la DB, sinon fallback explicite
        const templateName = (templateData?.name && templateData.name.trim() !== '') ?
                           templateData.name :
                           (templateData?._db_name && templateData._db_name.trim() !== '') ?
                           templateData._db_name :
                           `[NOM NON RÉCUPÉRÉ - ID: ${templateId}]`;
        console.log('🔄 [useTemplate] Final template name:', templateName);
        // console.log('📋 [LOAD TEMPLATE] Utilisation des données localisées pour template:', templateId, 'Nom:', templateData.name);

        // Parse JSON strings if needed
        let elements = [];
        let canvasData = null;
        try {
          if (typeof templateData.elements === 'string') {
            elements = JSON.parse(templateData.elements);
          } else if (Array.isArray(templateData.elements)) {
            elements = templateData.elements;
          } else {
            elements = [];
          }

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
          debugError('❌ [LOAD TEMPLATE] Erreur de parsing des données localisées:', parseError);
          elements = [];
          canvasData = { width: 210, height: 297 };
        }

        const normalizedElements = normalizeElementsAfterLoad(elements as any);
        const enrichedElements = normalizedElements.map((el: Record<string, unknown>) => {
          let enrichedElement = { ...el };
          if (el.type === 'company_logo' && !el.src && !el.logoUrl) {
            const logoUrl = (el.defaultSrc as string) || '';
            if (logoUrl) {
              enrichedElement.src = logoUrl;
            }
          }
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

        let lastSavedDate: Date;
        try {
          if (templateData.updated_at) {
            lastSavedDate = new Date(templateData.updated_at);
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
            name: templateName,
            elements: enrichedElements,
            canvas: canvasData,
            lastSaved: lastSavedDate
          } as LoadTemplatePayload
        });

        return true;
      }

      // ✅ FALLBACK: Utiliser AJAX si les données localisées ne sont pas disponibles
      console.log('🔄 [useTemplate] USING AJAX FALLBACK PATH for template:', templateId);
      console.log('🔄 [useTemplate] Checking window.pdfBuilderData again:', window.pdfBuilderData);
      console.log('🔄 [useTemplate] ajaxUrl for AJAX call:', window.pdfBuilderData?.ajaxUrl);
      console.log('🔄 [useTemplate] nonce for AJAX call:', window.pdfBuilderData?.nonce);

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

      console.log('🔄 [useTemplate] Browser detection:', { isChrome, isFirefox, isSafari });

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
        console.log('🔄 [useTemplate] Using Chrome-specific options');
      } else if (isFirefox) {
        // Firefox gère bien le cache par défaut
        fetchOptions.cache = 'no-cache';
        console.log('🔄 [useTemplate] Using Firefox-specific options');
      } else if (isSafari) {
        // Safari peut avoir des problèmes avec certains modes
        fetchOptions.mode = 'cors';
        console.log('🔄 [useTemplate] Using Safari-specific options');
      }

      const cacheBreaker = Date.now();
      const url = `${window.pdfBuilderData?.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${window.pdfBuilderData?.nonce}&t=${cacheBreaker}`;

      console.log('🔄 [useTemplate] About to fetch URL:', url);
      console.log('🔄 [useTemplate] Fetch options:', fetchOptions);

      const response = await fetch(url, fetchOptions);

      if (!response.ok) {
        const errorText = await response.text();
        debugError('[useTemplate] Response error text:', errorText);
        throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
      }

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors du chargement du template');
      }

      console.log('🔄 [useTemplate] AJAX result:', result);
      console.log('🔄 [useTemplate] result.data:', result.data);
      console.log('🔄 [useTemplate] result.template:', result.template);
      console.log('🔄 [useTemplate] result.template_name:', result.template_name);

      const templateData = result.data ? result.data.template : result.template;
      const ajaxTemplateName = result.data ? (result.data.template_name || result.data.name) : (result.name || result.template_name);

      console.log('🔄 [useTemplate] templateData:', templateData);
      console.log('🔄 [useTemplate] ajaxTemplateName:', ajaxTemplateName);

      // Appliquer la même logique de fallback que pour les données localisées
      const templateName = (ajaxTemplateName && ajaxTemplateName.trim() !== '') ?
                         ajaxTemplateName :
                         (templateData?.name && templateData.name.trim() !== '') ?
                         templateData.name :
                         `[NOM NON RÉCUPÉRÉ - ID: ${templateId}]`;

      
      // 🔍 Tracer les éléments reçus du serveur
      if (templateData.elements) {
        // 🔍 Vérifier spécifiquement les éléments order_number
        const orderNumberElements = templateData.elements.filter((el: any) => el.type === 'order_number');
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

      dispatch({
        type: 'LOAD_TEMPLATE',
        payload: {
          id: templateId,
          name: templateName,
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

      debugError(`❌ [LOAD TEMPLATE] Échec du chargement sur ${isChrome ? 'Chrome' : isFirefox ? 'Firefox' : isSafari ? 'Safari' : 'navigateur inconnu'}`);
      debugError('❌ [LOAD TEMPLATE] Détails de l\'erreur:', {
        message: error instanceof Error ? error.message : 'Unknown error',
        stack: error instanceof Error ? error.stack : undefined,
        name: error instanceof Error ? error.name : 'Unknown',
        templateId: templateId,
        ajaxUrl: window.pdfBuilderData?.ajaxUrl,
        userAgent: navigator.userAgent
      });

      // Tentative de fallback pour Chrome
      if (isChrome && (error instanceof Error && error.message.includes('fetch'))) {
        debugWarn('🔄 [LOAD TEMPLATE] Tentative de fallback pour Chrome - Nouvelle tentative avec options différentes');

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

          const fallbackResponse = await fetch(fallbackUrl, fallbackOptions);

          if (fallbackResponse.ok || fallbackResponse.status === 0) { // no-cors peut retourner status 0
            // Traiter la réponse même si elle est opaque
            return true;
          }
        } catch (fallbackError) {
          debugError('❌ [LOAD TEMPLATE] Échec du fallback:', fallbackError);
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

    if (templateId) {
      // Timeout de sécurité : forcer isLoading à false après 10 secondes si le chargement échoue
      const loadingTimeout = setTimeout(() => {
        debugError('[useTemplate] Loading timeout reached, forcing isLoading to false');
        dispatch({ type: 'SET_TEMPLATE_LOADING', payload: false });
      }, 10000);

      // Charger le template avec gestion d'erreur améliorée
      loadExistingTemplate(templateId)
        .then(() => {
          clearTimeout(loadingTimeout);
        })
        .catch((error) => {
          clearTimeout(loadingTimeout);
          debugError('[useTemplate] Template loading failed:', error);
          // Force isLoading to false on error
          dispatch({ type: 'SET_TEMPLATE_LOADING', payload: false });
        });
    } else {
      // Si pas de template ID, forcer isLoading à false pour nouveau template
      dispatch({ type: 'NEW_TEMPLATE' });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Sauvegarder un template manuellement
  const saveTemplate = useCallback(async () => {
    // console.log('[PDF_BUILDER_FRONTEND] Starting template save...');
    dispatch({ type: 'SET_TEMPLATE_SAVING', payload: true });

    try {
      const templateId = getTemplateIdFromUrl();
      // console.log('[PDF_BUILDER_FRONTEND] Template ID:', templateId);

      if (!templateId) {
        throw new Error('Aucun template chargé pour la sauvegarde');
      }

      // Vérifier que le template est complètement chargé
      if (!state.template.name || state.template.name.trim() === '') {
        // console.log('[PDF_BUILDER_FRONTEND] Template name not loaded yet, skipping save');
        return; // Ne pas lancer d'erreur, juste ignorer
      }

      // console.log('[PDF_BUILDER_FRONTEND] Template name:', state.template.name);

      if (!window.pdfBuilderData?.ajaxUrl) {
        throw new Error('URL AJAX non disponible');
      }

      if (!window.pdfBuilderData?.nonce) {
        throw new Error('Nonce non disponible');
      }

      // console.log('[PDF_BUILDER_FRONTEND] AJAX URL available:', !!window.pdfBuilderData?.ajaxUrl);
      // console.log('[PDF_BUILDER_FRONTEND] Nonce available:', !!window.pdfBuilderData?.nonce);

      // ✅ NORMALISER LES ÉLÉMENTS AVANT SAUVEGARDE
      // Cela garantit que contentAlign, labelPosition, etc. ne sont jamais perdus
      const normalizedElements = normalizeElementsBeforeSave(state.elements as any);
      debugElementState(normalizedElements as any, 'AVANT SAUVEGARDE');

      // 🔍 DEBUG: Log complet des propriétés des éléments avant sauvegarde
      // console.log('[PDF_BUILDER_FRONTEND] Éléments avant normalisation:', state.elements);
      // console.log('[PDF_BUILDER_FRONTEND] Éléments après normalisation:', normalizedElements);
      
      // Vérifier les propriétés spéciales
      normalizedElements.forEach((el, idx) => {
        // console.log(`[PDF_BUILDER_FRONTEND] Élément ${idx} (${el.type}) propriétés:`, Object.keys(el));
        // Chercher des propriétés avec emoji ou "interactions"
        Object.keys(el).forEach(key => {
          if (key.includes('🎯') || key.includes('interactions') || key.includes('comportement') || key.includes('behavior')) {
            // console.log(`[PDF_BUILDER_FRONTEND] Propriété spéciale trouvée: ${key} =`, el[key]);
          }
        });
      });

      // Structure simple et propre pour la sauvegarde
      const templateData = {
        elements: normalizedElements,
        canvasWidth: canvasWidth,
        canvasHeight: canvasHeight,
        version: '1.0',
        // Inclure les paramètres du template
        name: state.template.name,
        description: state.template.description,
        showGuides: state.template.showGuides,
        snapToGrid: state.template.snapToGrid,
        marginTop: state.template.marginTop,
        marginBottom: state.template.marginBottom
      };
      
      const formData = new FormData();
      formData.append('action', 'pdf_builder_save_template');
      formData.append('template_id', templateId);
      formData.append('template_name', state.template.name || 'Nouveau template');
      formData.append('template_description', state.template.description || '');
      formData.append('template_data', JSON.stringify(templateData));
      formData.append('nonce', window.pdfBuilderData?.nonce || '');

      // Ajouter les paramètres du template
      formData.append('show_guides', state.template.showGuides ? '1' : '0');
      formData.append('snap_to_grid', state.template.snapToGrid ? '1' : '0');
      formData.append('margin_top', (state.template.marginTop || 0).toString());
      formData.append('margin_bottom', (state.template.marginBottom || 0).toString());
      formData.append('canvas_width', (state.template.canvasWidth || canvasWidth).toString());
      formData.append('canvas_height', (state.template.canvasHeight || canvasHeight).toString());

      // console.log('[PDF_BUILDER_FRONTEND] Data to send:');
      // console.log('- Template ID:', templateId);
      // console.log('- Template Name:', state.template.name || 'Nouveau template');
      // console.log('- Elements count:', normalizedElements.length);
      // console.log('- Canvas size:', canvasWidth, 'x', canvasHeight);
      // console.log('- Template data size:', JSON.stringify(templateData).length, 'characters');
      // console.log('- Nonce:', window.pdfBuilderData?.nonce ? 'Present' : 'Missing');

      const response = await fetch(window.pdfBuilderData?.ajaxUrl || '', {
        method: 'POST',
        body: formData
      });

      // console.log('[PDF_BUILDER_FRONTEND] HTTP Response status:', response.status);

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();
      // console.log('[PDF_BUILDER_FRONTEND] Server response:', result);

      if (!result.success) {
        debugError('[PDF_BUILDER_FRONTEND] Server returned error:', result.data);
        const errorMessage = result.data || 'Unknown error during save';
        throw new Error(errorMessage);
      }

      // console.log('[PDF_BUILDER_FRONTEND] Save successful! Template ID:', result.data?.template_id);

      dispatch({
        type: 'SAVE_TEMPLATE',
        payload: {
          id: result.data.template_id || result.data.id,
          name: result.data.name
        }
      });

    } catch (error) {
      debugError('[PDF_BUILDER_FRONTEND] Save failed:', error);
      debugError('[useTemplate] SAVE - Error:', error);
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

