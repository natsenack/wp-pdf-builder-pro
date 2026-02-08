import { useEffect, useCallback, useMemo } from "react";
import { useBuilder } from "../contexts/builder/BuilderContext";
import { useCanvasSettings } from "../contexts/CanvasSettingsContext";
import { LoadTemplatePayload, TemplateState } from "../types/elements";
import { debugError, debugWarn, debugLog } from "../utils/debug";
import {
  serializeCanvasData,
  deserializeCanvasData,
  debugCanvasData,
} from "../utils/CanvasPersistence";
import { ClientNonceManager } from "../utils/ClientNonceManager";
import { configureRealDataElements } from "../utils/RealDataElementsHelper";  // ✅ NEW: Auto-configure RealData elements

export function useTemplate() {
  const { state, dispatch } = useBuilder();
  const canvasSettings = useCanvasSettings();

  // Détecter si on est sur un template existant via l'URL ou les données localisées
  const getTemplateIdFromUrl = useCallback((): string | null => {
    // Priorité 1: Utiliser le templateId du state template (après sauvegarde)
    if (state.template.id) {
      return state.template.id.toString();
    }

    // Priorité 2: Utiliser le templateId des données PHP localisées
    if (window.pdfBuilderData?.templateId) {
      return window.pdfBuilderData.templateId.toString();
    }

    // Priorité 3: Utiliser le paramètre URL (pour compatibilité)
    const urlParams = new URLSearchParams(window.location.search);
    const urlTemplateId = urlParams.get("template_id");
    if (urlTemplateId) {
      return urlTemplateId;
    }

    return null;
  }, [state.template.id]);

  const isEditingExistingTemplate = (): boolean => {
    return getTemplateIdFromUrl() !== null;
  };

  // Charger un template existant
  const loadExistingTemplate = useCallback(
    async (templateId: string) => {
      
      
      
      
      

      try {
        // ✅ PRIORITÉ: Utiliser les données localisées si disponibles (plus rapide et fiable)
        if (
          window.pdfBuilderData?.existingTemplate &&
          window.pdfBuilderData?.hasExistingData
        ) {
          
          const templateData = window.pdfBuilderData.existingTemplate;
          const templateName = templateData?.name?.trim() 
            ? templateData.name 
            : `[Template ${templateId}]`;

          // ✅ UTILISER LA COUCHE UNIFIÉE DE DÉSÉRIALISATION
          // Mode édition: utiliser les valeurs fictives
          const { elements, canvas } = deserializeCanvasData(
            templateData.template_data || templateData,
            { mode: 'editor' }
          );

          // ✅ Initialiser les propriétés RealData sur les éléments
          const configuredElements = configureRealDataElements(elements);

          debugLog(`📂 LOAD - ${configuredElements.length} éléments depuis données localisées`);
          debugCanvasData({ elements: configuredElements, canvas, version: '1.0' }, 'Données chargées');

          dispatch({
            type: "LOAD_TEMPLATE",
            payload: {
              id: templateId,
              name: templateName,
              elements: configuredElements,
              canvas,
              lastSaved: new Date(),
              showGuides: templateData.showGuides ?? true,
              snapToGrid: templateData.snapToGrid ?? false,
              marginTop: templateData.marginTop ?? 0,
              marginBottom: templateData.marginBottom ?? 0,
              description: templateData.description ?? "",
            } as LoadTemplatePayload,
          });

          return true;
        }

        // ✅ FALLBACK: Utiliser AJAX si les données localisées ne sont pas disponibles
        
        
        
        

        // Détecter le navigateur pour des en-têtes spécifiques
        const isChrome =
          typeof navigator !== "undefined" &&
          /Chrome/.test(navigator.userAgent) &&
          /Google Inc/.test(navigator.vendor);

        const isFirefox =
          typeof navigator !== "undefined" &&
          /Firefox/.test(navigator.userAgent);

        const isSafari =
          typeof navigator !== "undefined" &&
          /Safari/.test(navigator.userAgent) &&
          !/Chrome/.test(navigator.userAgent) &&
          !/Chromium/.test(navigator.userAgent);

        

        // Préparer les options fetch avec des en-têtes spécifiques par navigateur
        const fetchOptions: RequestInit = {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            // En-têtes anti-cache spécifiques
            "Cache-Control": "no-cache, no-store, must-revalidate",
            Pragma: "no-cache",
            Expires: "0",
          },
          // Mode spécifique selon le navigateur
          mode: "cors" as RequestMode,
          credentials: "same-origin" as RequestCredentials,
        };

        // Ajustements spécifiques par navigateur
        if (isChrome) {
          // Chrome peut avoir besoin d'un mode plus permissif
          fetchOptions.mode = "cors";
          fetchOptions.cache = "no-cache";
          
        } else if (isFirefox) {
          // Firefox gère bien le cache par défaut
          fetchOptions.cache = "no-cache";
          
        } else if (isSafari) {
          // Safari peut avoir des problèmes avec certains modes
          fetchOptions.mode = "cors";
          
        }

        const cacheBreaker = Date.now();
        const url = ClientNonceManager.addToUrl(
          `${ClientNonceManager.getAjaxUrl()}?action=pdf_builder_get_template&template_id=${templateId}&t=${cacheBreaker}`
        );

        
        

        const response = await fetch(url, fetchOptions);

        if (!response.ok) {
          const errorText = await response.text();
          debugError("[useTemplate] Response error text:", errorText);
          throw new Error(
            `Erreur HTTP ${response.status}: ${response.statusText}`
          );
        }

        const result = await response.json();

        if (!result.success) {
          throw new Error(
            result.data || "Erreur lors du chargement du template"
          );
        }

        // ✅ FIX: ajaxGetTemplate() retourne {template: {...}, template_name: '...', ...}
        // On doit accéder à result.data.template pour obtenir l'objet template réel
        const templateData = result.data?.template || result.data || {};

        // 🔍 Récupérer les éléments du templateData pour parsing
        const ajaxTemplateName = result.data
          ? result.data.template_name || result.data.name
          : result.name || result.template_name;

        
        

        // Appliquer la même logique de fallback que pour les données localisées
        const templateName =
          ajaxTemplateName && ajaxTemplateName.trim() !== ""
            ? ajaxTemplateName
            : templateData?.name && templateData.name.trim() !== ""
            ? templateData.name
            : `[NOM NON RÉCUPÉRÉ - ID: ${templateId}]`;

        // 🔍 Tracer les éléments reçus du serveur
        if (templateData.elements) {
          // 🔍 Vérifier spécifiquement les éléments order_number
          const orderNumberElements = templateData.elements.filter(
            (el: any) => el.type === "order_number"
          );
        }

        // ✅ UTILISER LA COUCHE UNIFIÉE POUR LE FALLBACK AUSSI
        // Mode édition: utiliser les valeurs fictives
        const { elements, canvas } = deserializeCanvasData(templateData, { mode: 'editor' });

        // ✅ Initialiser les propriétés RealData sur les éléments
        const configuredElements = configureRealDataElements(elements);

        debugLog(`📂 LOAD FALLBACK - ${configuredElements.length} éléments depuis AJAX`);
        debugCanvasData({ elements: configuredElements, canvas, version: '1.0' }, 'Données AJAX');

        dispatch({
          type: "LOAD_TEMPLATE",
          payload: {
            id: templateId,
            name: templateName,
            elements: configuredElements,
            canvas,
            lastSaved: new Date(),
            showGuides: (fallbackTemplateData as any)?.showGuides ?? true,
            snapToGrid: (fallbackTemplateData as any)?.snapToGrid ?? false,
            marginTop: (fallbackTemplateData as any)?.marginTop ?? 0,
            marginBottom: (fallbackTemplateData as any)?.marginBottom ?? 0,
            description: (fallbackTemplateData as any)?.description ?? "",
          } as LoadTemplatePayload,
        });

        return true;
      } catch (error) {
        debugError("❌ [LOAD TEMPLATE] Erreur lors du chargement:", error);

        // Diagnostics spécifiques selon le navigateur
        const isChrome =
          typeof navigator !== "undefined" &&
          /Chrome/.test(navigator.userAgent) &&
          /Google Inc/.test(navigator.vendor);

        const isFirefox =
          typeof navigator !== "undefined" &&
          /Firefox/.test(navigator.userAgent);

        const isSafari =
          typeof navigator !== "undefined" &&
          /Safari/.test(navigator.userAgent) &&
          !/Chrome/.test(navigator.userAgent) &&
          !/Chromium/.test(navigator.userAgent);

        debugError(
          `❌ [LOAD TEMPLATE] Échec du chargement sur ${
            isChrome
              ? "Chrome"
              : isFirefox
              ? "Firefox"
              : isSafari
              ? "Safari"
              : "navigateur inconnu"
          }`
        );
        debugError("❌ [LOAD TEMPLATE] Détails de l'erreur:", {
          message: error instanceof Error ? error.message : "Unknown error",
          stack: error instanceof Error ? error.stack : undefined,
          name: error instanceof Error ? error.name : "Unknown",
          templateId: templateId,
          ajaxUrl: window.pdfBuilderData?.ajaxUrl,
          userAgent: navigator.userAgent,
        });

        // Tentative de fallback pour Chrome
        if (
          isChrome &&
          error instanceof Error &&
          error.message.includes("fetch")
        ) {
          debugWarn(
            "🔄 [LOAD TEMPLATE] Tentative de fallback pour Chrome - Nouvelle tentative avec options différentes"
          );

          try {
            // Attendre un peu avant retry
            await new Promise((resolve) => setTimeout(resolve, 1000));

            // Retry avec des options différentes
            const fallbackOptions: RequestInit = {
              method: "GET",
              headers: {
                Accept: "application/json, text/plain, */*",
                "X-Requested-With": "XMLHttpRequest",
              },
              mode: "no-cors" as RequestMode,
              cache: "reload",
            };

            const fallbackUrl = ClientNonceManager.addToUrl(
              `${ClientNonceManager.getAjaxUrl()}?action=pdf_builder_get_template&template_id=${templateId}&fallback=1&t=${Date.now()}`
            );

            const fallbackResponse = await fetch(fallbackUrl, fallbackOptions);

            if (fallbackResponse.ok || fallbackResponse.status === 0) {
              // no-cors peut retourner status 0
              // Traiter la réponse même si elle est opaque
              return true;
            }
          } catch (fallbackError) {
            debugError("❌ [LOAD TEMPLATE] Échec du fallback:", fallbackError);
          }
        }

        return false;
      }
    },
    [dispatch]
  );

  /**
   * 📊 Charger template en mode APERÇU (preview)
   * 
   * Récupère les données réelles WooCommerce pour une commande
   * et charge le template avec ces données injectées
   * (aperçu miroir avec vraies données)
   */
  const loadTemplateForPreview = useCallback(
    async (templateId: string, orderId: string | number) => {
      try {
        // 1️⃣ D'abord, récupérer les données réelles de la commande
        debugLog(`📊 [PREVIEW] Récupération des données pour commande ID ${orderId}`);
        
        const orderDataUrl = ClientNonceManager.addToUrl(
          `${ClientNonceManager.getAjaxUrl()}?action=pdf_builder_get_order_data_for_preview&orderId=${orderId}`
        );

        const orderDataResponse = await fetch(orderDataUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({
            nonce: window.pdfBuilderData?.nonce || '',
          }),
        });

        if (!orderDataResponse.ok) {
          throw new Error(`Erreur HTTP ${orderDataResponse.status}: Impossible de récupérer les données de la commande`);
        }

        const orderDataResult = await orderDataResponse.json();
        if (!orderDataResult.success) {
          throw new Error(orderDataResult.data?.message || 'Erreur lors de la récupération des données');
        }

        const realOrderData = orderDataResult.data;
        debugLog(`✅ [PREVIEW] ${Object.keys(realOrderData).length} propriétés récupérées pour la commande`);
        debugLog('[PREVIEW] Données:', realOrderData);

        // 2️⃣ Charger le template avec les données réelles en mode preview
        const templateLoaded = await loadExistingTemplate(templateId);
        if (!templateLoaded) {
          throw new Error('Impossible de charger le template');
        }

        // 3️⃣ Récupérer les éléments du state et les déserialiser en mode preview
        // (C'est fait dans BuilderContext après SET_ELEMENTS)
        const templateData = window.pdfBuilderData?.existingTemplate;
        if (!templateData) {
          throw new Error('Données du template manquantes');
        }

        // 4️⃣ Réinjecter avec les données réelles
        const { elements, canvas } = deserializeCanvasData(
          templateData.template_data || templateData,
          {
            mode: 'preview',
            realOrderData: realOrderData
          }
        );

        // 🔧 Initialiser les propriétés RealData (même si déjà en mode preview)
        const configuredElements = configureRealDataElements(elements);

        debugLog(`📊 [PREVIEW] ${configuredElements.length} éléments chargés avec données réelles`);
        debugCanvasData({ elements: configuredElements, canvas, version: '1.0' }, '📊 Aperçu avec données réelles');

        // 5️⃣ Dispatcher pour mettre à jour le canvas avec les éléments du preview
        dispatch({
          type: 'SET_ELEMENTS',
          payload: configuredElements
        });

        // 6️⃣ Mettre à jour le state de preview
        dispatch({
          type: 'SET_PREVIEW_MODE',
          payload: 'preview'
        });
        
        dispatch({
          type: 'SET_ORDER_ID',
          payload: String(orderId)
        });

        return true;
      } catch (error) {
        debugError('❌ [PREVIEW] Erreur lors du chargement de l\'aperçu:', error);
        return false;
      }
    },
    [dispatch, loadExistingTemplate]
  );

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
        debugError(
          "[useTemplate] Loading timeout reached, forcing isLoading to false"
        );
        dispatch({ type: "SET_TEMPLATE_LOADING", payload: false });
      }, 10000);

      // Charger le template avec gestion d'erreur améliorée
      loadExistingTemplate(templateId)
        .then(() => {
          clearTimeout(loadingTimeout);
        })
        .catch((error) => {
          clearTimeout(loadingTimeout);
          debugError("[useTemplate] Template loading failed:", error);
          // Force isLoading to false on error
          dispatch({ type: "SET_TEMPLATE_LOADING", payload: false });
        });
    } else {
      // Si pas de template ID, forcer isLoading à false pour nouveau template
      dispatch({ type: "NEW_TEMPLATE" });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Sauvegarder un template manuellement
  const saveTemplate = useCallback(async () => {
    dispatch({ type: "SET_TEMPLATE_SAVING", payload: true });

    try {
      const templateId = getTemplateIdFromUrl();
      if (!templateId) throw new Error("Aucun template chargé");
      if (!state.template.name?.trim()) return;

      // 🔍 DEBUG: What's in state.elements RIGHT NOW?
      if (state.elements.length > 0) {
        console.log('[💾 SAVE STATE DEBUG] ALL elements in state.elements:', 
          state.elements.map((el: any) => ({
            id: el.id,
            x: el.x,
            y: el.y,
            type: el.type
          }))
        );
        // Find the company logo element specifically
        const companyLogo = state.elements.find((el: any) => el.id.includes('company_logo'));
        if (companyLogo) {
          console.log('[💾 SAVE STATE DEBUG] DRAGGED element (company_logo) in state:', {
            id: companyLogo.id,
            x: companyLogo.x,
            y: companyLogo.y
          });
        }
      }

      // Sérialiser les données du canvas
      const jsonData = serializeCanvasData(
        state.elements,
        {
          width: state.template.canvasWidth || canvasSettings.canvasWidth,
          height: state.template.canvasHeight || canvasSettings.canvasHeight,
        }
      );
      debugLog(`💾 SAVE - ${state.elements.length} éléments, ID: ${templateId}`);

      // Préparer la requête
      const formData = new FormData();
      formData.append("action", "pdf_builder_save_template");
      formData.append("template_id", templateId);
      formData.append("template_name", state.template.name);
      formData.append("template_description", state.template.description || "");
      formData.append("template_data", jsonData);
      ClientNonceManager.addToFormData(formData);

      // 🔍 LOG 3: AVANT requête AJAX
      console.log('[💾 SAVE DIAGNOSTIC] Envoi AJAX:', {
        url: ClientNonceManager.getAjaxUrl(),
        templateId,
        templateName: state.template.name,
        templateDataLength: jsonData.length,
        nonce: ClientNonceManager.getCurrentNonce()
      });

      const response = await fetch(ClientNonceManager.getAjaxUrl(), {
        method: "POST",
        body: formData,
      });

      // 🔍 LOG 4: APRÈS réponse AJAX
      console.log('[💾 SAVE DIAGNOSTIC] Réponse HTTP:', {
        status: response.status,
        statusText: response.statusText,
        headers: {
          contentType: response.headers.get('content-type')
        }
      });

      if (!response.ok) {
        const errorText = await response.text();
        debugError('[SAVE] HTTP Error:', response.status, errorText);
        console.error('[💾 SAVE DIAGNOSTIC] HTTP Error body:', errorText);
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();

      // 🔍 LOG 5: Réponse du serveur
      console.log('[💾 SAVE DIAGNOSTIC] Réponse serveur:', {
        success: result.success,
        data: result.data,
        error: result.error
      });

      if (!result.success) {
        debugError('[SAVE] Save failed:', result.data);
        if (result.data?.code === "nonce_invalid") {
          try {
            let freshNonce = result.data?.nonce;

            if (!freshNonce) {
              freshNonce = await ClientNonceManager.refreshNonce(
                ClientNonceManager.getCurrentNonce() || undefined
              );
            } else {
              ClientNonceManager.setNonce(freshNonce);
            }

            if (freshNonce) {
              return await saveTemplate();
            }
          } catch (nonceError) {
            // error
          }
        }

        throw new Error(
          result.data || "Erreur lors de la sauvegarde du template"
        );
      }

      dispatch({
        type: "SAVE_TEMPLATE",
        payload: {
          id: result.data.template_id || result.data.id,
          name: result.data.name,
        },
      });

      // Update window.pdfBuilderData to reflect the latest saved data
      // This prevents issues if the component re-mounts and loads old data
      if (window.pdfBuilderData && window.pdfBuilderData.existingTemplate) {
        window.pdfBuilderData.existingTemplate.template_data = jsonData;
        window.pdfBuilderData.existingTemplate.name = state.template.name;
        window.pdfBuilderData.existingTemplate.description = state.template.description || "";
      }

      return true;
    } catch (error) {
      debugError("[PDF_BUILDER_FRONTEND] Save failed:", error);
      debugError("[useTemplate] SAVE - Error:", error);
      throw error;
    } finally {
      dispatch({ type: "SET_TEMPLATE_SAVING", payload: false });
    }
  }, [
    state.elements,
    state.template.canvasWidth,
    state.template.canvasHeight,
    state.template.name,
    state.template.description,
    getTemplateIdFromUrl,
  ]);

  const previewTemplate = useCallback(() => {
    dispatch({ type: "SET_SHOW_PREVIEW_MODAL", payload: true });
  }, [dispatch]);

  // ✅ NEW: Exposer loadTemplateForPreview au niveau global pour que le header puisse l'appeler
  useEffect(() => {
    if (typeof window !== 'undefined') {
      (window as any).pdfBuilderLoadTemplateForPreview = loadTemplateForPreview;
    }
  }, [loadTemplateForPreview]);

  const newTemplate = useCallback(() => {
    dispatch({ type: "NEW_TEMPLATE" });
  }, [dispatch]);

  const setTemplateModified = useCallback(
    (modified: boolean) => {
      dispatch({ type: "SET_TEMPLATE_MODIFIED", payload: modified });
    },
    [dispatch]
  );

  const updateTemplateSettings = useCallback(
    (settings: Partial<TemplateState>) => {
      dispatch({ type: "UPDATE_TEMPLATE_SETTINGS", payload: settings });
    },
    [dispatch]
  );

  return useMemo(
    () => ({
      templateName: state.template.name,
      templateDescription: state.template.description,
      templateTags: state.template.tags,
      canvasWidth: state.template.canvasWidth || canvasSettings.canvasWidth,
      canvasHeight: state.template.canvasHeight || canvasSettings.canvasHeight,
      marginTop: state.template.marginTop || canvasSettings.marginTop,
      marginBottom: state.template.marginBottom || canvasSettings.marginBottom,
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
      updateTemplateSettings,
      loadTemplateForPreview,  // ✅ NEW: Charger template avec données réelles
    }),
    [
      state.template.id,
      state.template.name,
      state.template.description,
      state.template.tags,
      state.template.canvasWidth,
      state.template.canvasHeight,
      state.template.marginTop,
      state.template.marginBottom,
      state.template.showGuides,
      state.template.snapToGrid,
      state.template.isNew,
      state.template.isModified,
      state.template.isSaving,
      state.template.isLoading,
      state.template.lastSaved,
      canvasSettings, // ✅ AJOUT: Inclure tous les paramètres du canvas
      saveTemplate,
      previewTemplate,
      newTemplate,
      setTemplateModified,
      updateTemplateSettings,
      loadTemplateForPreview,  // ✅ NEW: Ajouter aux dépendances
      getTemplateIdFromUrl,
    ]
  );
}


