import React, {
  useState,
  useEffect,
  useCallback,
  memo,
  useDeferredValue,
} from "react";
import { TemplateState } from "../../types/elements";
import { useBuilder } from "../../contexts/builder/BuilderContext";
import { usePreview } from "../../hooks/usePreview";

console.log('[REACT HEADER COMPONENT] ===== FILE LOADED =====');
console.log('[REACT HEADER COMPONENT] Component file loaded and executing at:', new Date().toISOString());
console.log('[REACT HEADER COMPONENT] React available:', typeof React);
console.log('[REACT HEADER COMPONENT] useState available:', typeof useState);
console.log('[REACT HEADER COMPONENT] useBuilder available:', typeof useBuilder);
console.log('[REACT HEADER COMPONENT] usePreview available:', typeof usePreview);
import { useCanvasSettings } from "../../contexts/CanvasSettingsContext";
import { debugLog, debugError } from "../../utils/debug";

// Extension de Window pour l'API Preview
declare global {
  interface Window {
    pdfPreviewAPI?: {
      generateEditorPreview: (
        templateData: Record<string, unknown>,
        options?: { format?: string; quality?: number }
      ) => Promise<Record<string, unknown>>;
      generateOrderPreview: (
        templateData: Record<string, unknown>,
        orderId: number,
        options?: { format?: string; quality?: number }
      ) => Promise<Record<string, unknown>>;
    };
  }
}

interface HeaderProps {
  templateName: string;
  templateDescription: string;
  canvasWidth: number;
  canvasHeight: number;
  showGuides: boolean;
  snapToGrid: boolean;
  isNewTemplate: boolean;
  isModified: boolean;
  isSaving: boolean;
  isLoading: boolean;
  isEditingExistingTemplate: boolean;
  onSave: () => void;
  onPreview: () => void;
  onNewTemplate: () => void;
  onUpdateTemplateSettings: (settings: Partial<TemplateState>) => void;
}

export const Header = memo(function Header({
  templateName,
  templateDescription,
  canvasWidth,
  canvasHeight,
  showGuides,
  snapToGrid,
  isNewTemplate,
  isModified,
  isSaving,
  isLoading,
  isEditingExistingTemplate,
  onSave,
  onPreview: _onPreview,
  onNewTemplate,
  onUpdateTemplateSettings,
}: HeaderProps) {
  // Use deferred values for frequently changing props to prevent cascading re-renders
  const deferredIsModified = useDeferredValue(isModified);
  const deferredIsSaving = useDeferredValue(isSaving);
  const deferredIsLoading = useDeferredValue(isLoading);
  const deferredIsEditingExistingTemplate = useDeferredValue(
    isEditingExistingTemplate
  );
  // Debug logging
  useEffect(() => {}, []);

  const { state } = useBuilder();
  const canvasSettings = useCanvasSettings();
  const [hoveredButton, setHoveredButton] = useState<string | null>(null);
  const [showSettingsModal, setShowSettingsModal] = useState(false);
  const [showJsonModal, setShowJsonModal] = useState(false);
  const [copySuccess, setCopySuccess] = useState(false);
  const [jsonModalView, setJsonModalView] = useState<'json' | 'html'>('json');
  const [generatedHtml, setGeneratedHtml] = useState<string>('');
  const [isGeneratingHtml, setIsGeneratingHtml] = useState(false);
  const [isHeaderFixed, setIsHeaderFixed] = useState(false);
  const [performanceMetrics, setPerformanceMetrics] = useState({
    fps: 0,
    memoryUsage: 0,
    lastUpdate: 0
  });
  const [editedTemplateName, setEditedTemplateName] = useState(templateName);
  const [editedTemplateDescription, setEditedTemplateDescription] =
    useState(templateDescription);
  const [editedCanvasWidth, setEditedCanvasWidth] = useState(canvasWidth);
  const [editedCanvasHeight, setEditedCanvasHeight] = useState(canvasHeight);
  const [canvasOrientation, setCanvasOrientation] = useState<
    "portrait" | "landscape"
  >(canvasWidth < canvasHeight ? "portrait" : "landscape");
  const [showPredefinedTemplates, setShowPredefinedTemplates] = useState(false);
  const [orientationPermissions, setOrientationPermissions] = useState({
    allowPortrait: true,
    allowLandscape: true,
    defaultOrientation: "portrait",
  });

  // Utiliser le hook usePreview pour la gestion de l'aperçu
  const {
    isModalOpen: showPreviewModal,
    openModal: openPreviewModal,
    closeModal: closePreviewModal,
    isGenerating: isGeneratingPreview,
    previewUrl: previewImageUrl,
    error: previewError,
    format: previewFormat,
    setFormat: setPreviewFormat,
    generatePreview,
    clearPreview,
  } = usePreview();

  // Debug logging
  useEffect(() => {
    debugLog("🔄 [PDF Builder] État bouton Enregistrer mis à jour", {
      templateName,
      buttonState: {
        disabled: deferredIsSaving || !deferredIsModified || deferredIsLoading,
        isSaving: deferredIsSaving,
        isModified: deferredIsModified,
        isLoading: deferredIsLoading,
        canSave: !deferredIsSaving && deferredIsModified && !deferredIsLoading,
      },
      timestamp: new Date().toISOString(),
    });
  }, [deferredIsSaving, deferredIsModified, deferredIsLoading, templateName]);

  // Charger les permissions d'orientation du canvas
  useEffect(() => {
    const loadOrientationPermissions = () => {
      try {
        // Utiliser les variables window directement au lieu d'un appel AJAX
        const availableOrientations = (window as any).availableOrientations || ['portrait', 'landscape'];
        
        const orientationPermissions = {
          allowPortrait: availableOrientations.includes('portrait'),
          allowLandscape: availableOrientations.includes('landscape'),
          defaultOrientation: (window as any).pdfBuilderCanvasSettings?.default_canvas_orientation || 'portrait',
          availableOrientations: availableOrientations
        };

        setOrientationPermissions(orientationPermissions);
      } catch (error) {
        debugError(
          "Erreur lors du chargement des permissions d'orientation",
          error
        );
        // Fallback en cas d'erreur
        setOrientationPermissions({
          allowPortrait: true,
          allowLandscape: true,
          defaultOrientation: 'portrait',
          availableOrientations: ['portrait', 'landscape']
        });
      }
    };

    loadOrientationPermissions();
  }, []);

  useEffect(() => {}, [showPreviewModal]);

  // Synchroniser les états locaux avec les props quand elles changent
  useEffect(() => {
    setEditedTemplateName(templateName);
  }, [templateName]);

  useEffect(() => {
    setEditedTemplateDescription(templateDescription);
  }, [templateDescription]);

  useEffect(() => {
    setEditedCanvasWidth(canvasWidth);
  }, [canvasWidth]);

  useEffect(() => {
    setEditedCanvasHeight(canvasHeight);
  }, [canvasHeight]);

  // ✅ SYSTÈME PARAMÈTRES: Monitoring des performances du canvas
  useEffect(() => {
    if (canvasSettings.performanceMonitoring) {
      const updateMetrics = () => {
        // Simuler la récupération des métriques (dans un vrai cas, on utiliserait getPerformanceMetrics du hook)
        const now = Date.now();
        setPerformanceMetrics(prev => ({
          fps: Math.floor(Math.random() * 20) + 40, // Simulation FPS 40-60
          memoryUsage: Math.floor(Math.random() * 50) + 80, // Simulation mémoire 80-130MB
          lastUpdate: now
        }));
      };

      const interval = setInterval(updateMetrics, 2000); // Update every 2 seconds
      updateMetrics(); // Initial update

      return () => clearInterval(interval);
    }
  }, [canvasSettings.performanceMonitoring]);

  // State pour le throttling du scroll
  const [scrollTimeout, setScrollTimeout] = useState<NodeJS.Timeout | null>(
    null
  );

  // Optimisation: mémoriser le handler de scroll avec throttling
  const handleScroll = useCallback(() => {
    if (scrollTimeout) return; // Si un timeout est déjà en cours, ignorer

    setScrollTimeout(
      setTimeout(() => {
        const scrollTop =
          window.pageYOffset || document.documentElement.scrollTop;
        // Le header devient fixe après 120px de scroll
        setIsHeaderFixed(scrollTop > 120);
        setScrollTimeout(null);
      }, 50)
    ); // Délai de 50ms pour éviter les changements trop fréquents
  }, [scrollTimeout]);

  // Effet pour gérer le scroll et rendre le header fixe
  useEffect(() => {
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, [handleScroll]);

  // Effet pour fermer le dropdown des modèles prédéfinis quand on clique ailleurs
  useEffect(() => {
    const handleClickOutside = (event: Event) => {
      const target = event.target as HTMLElement;
      if (
        showPredefinedTemplates &&
        !target.closest("[data-predefined-dropdown]")
      ) {
        setShowPredefinedTemplates(false);
      }
    };

    if (showPredefinedTemplates) {
      document.addEventListener("mousedown", handleClickOutside, {
        passive: true,
      });
      return () =>
        document.removeEventListener("mousedown", handleClickOutside);
    }
  }, [showPredefinedTemplates]);

  // Fonction pour générer l'aperçu HTML dans le modal
  const generateHtmlPreview = useCallback(async () => {
    if (isGeneratingHtml) return;
    
    setIsGeneratingHtml(true);
    try {
      console.log('[HTML PREVIEW] Starting HTML preview generation');

      // Transformer les éléments pour l'aperçu HTML
      const transformedElements = state.elements && state.elements.length > 0 
        ? state.elements.map((element: any) => ({
            ...element,
            properties: {
              ...Object.keys(element)
                .filter(key => !['id', 'type', 'x', 'y', 'width', 'height', 'rotation', 'visible', 'locked', 'createdAt', 'updatedAt'].includes(key))
                .reduce((obj, key) => ({ ...obj, [key]: element[key] }), {}),
              ...(element.fillColor && { backgroundColor: element.fillColor }),
              ...(element.strokeColor && { borderColor: element.strokeColor }),
              ...(element.strokeWidth && { borderWidth: element.strokeWidth }),
            }
          }))
        : [];

      // Construire les données à envoyer
      const templateData = {
        elements: transformedElements,
        canvasWidth: state.canvas.width,
        canvasHeight: state.canvas.height,
        template: state.template,
      };

      const requestData = {
        action: 'pdf_builder_generate_html_preview',
        nonce: (window as any).pdfBuilderNonce,
        data: JSON.stringify({
          pageOptions: {
            template: templateData
          }
        })
      };

      // Utiliser URLSearchParams pour l'encodage correct
      const params = new URLSearchParams();
      Object.entries(requestData).forEach(([key, value]) => {
        params.append(key, String(value));
      });

      console.log('[HTML PREVIEW] Sending request with:');
      console.log('[HTML PREVIEW] - Elements count:', transformedElements.length);
      console.log('[HTML PREVIEW] - Canvas:', state.canvas.width, 'x', state.canvas.height);
      console.log('[HTML PREVIEW] - Template:', state.template?.name || 'N/A');

      const response = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString(),
        credentials: 'same-origin',
      });

      console.log('[HTML PREVIEW] Response status:', response.status);

      const responseText = await response.text();
      console.log('[HTML PREVIEW] Response length:', responseText.length);

      if (!response.ok) {
        console.error('[HTML PREVIEW] HTTP error:', response.status);
        throw new Error(`Erreur serveur: ${response.status}`);
      }

      if (responseText.trim() === '0') {
        console.error('[HTML PREVIEW] Server returned 0 (nonce or action issue)');
        throw new Error('Erreur d\'authentification ou d\'action non reconnue');
      }

      // Parser la réponse JSON
      let data: any;
      try {
        data = JSON.parse(responseText);
      } catch (e) {
        console.warn('[HTML PREVIEW] Response is not valid JSON');
        // Si ce n'est pas du JSON, utiliser la réponse comme HTML brut
        setGeneratedHtml(responseText);
        setJsonModalView('html');
        return;
      }

      if (data.success && data.data && data.data.html) {
        console.log('[HTML PREVIEW] HTML generation successful');
        setGeneratedHtml(data.data.html);
        setJsonModalView('html');
      } else {
        console.error('[HTML PREVIEW] Response error:', data);
        const errorMsg = data.data?.message || 'Erreur inconnue lors de la génération HTML';
        throw new Error(errorMsg);
      }
    } catch (error) {
      console.error('[HTML PREVIEW] Error occurred:', error);
      const errorMessage = error instanceof Error ? error.message : String(error);
      alert(`❌ Erreur: ${errorMessage}`);
    } finally {
      setIsGeneratingHtml(false);
    }
  }, [state, isGeneratingHtml]);

  const buttonBaseStyles = {
    padding: "10px 16px",
    border: "none",
    borderRadius: "6px",
    cursor: "pointer",
    fontSize: "14px",
    fontWeight: "500",
    display: "flex",
    alignItems: "center",
    gap: "6px",
    whiteSpace: "nowrap" as const,
  };

  const primaryButtonStyles = {
    ...buttonBaseStyles,
    backgroundColor: "#4CAF50",
    color: "#fff",
    boxShadow:
      hoveredButton === "save" ? "0 4px 12px rgba(76, 175, 80, 0.3)" : "none",
  };

  const secondaryButtonStyles = {
    ...buttonBaseStyles,
    backgroundColor: "#fff",
    border: "1px solid #ddd",
    color: "#333",
    boxShadow:
      hoveredButton === "preview-image" ||
      hoveredButton === "preview-pdf" ||
      hoveredButton === "new"
        ? "0 2px 8px rgba(0, 0, 0, 0.1)"
        : "none",
  };

  return (
    <div
      style={{
        display: "flex",
        alignItems: "center",
        justifyContent: "space-between",
        padding: isHeaderFixed ? "16px" : "12px",
        paddingLeft: isHeaderFixed ? "16px" : "12px",
        paddingRight: isHeaderFixed ? "16px" : "12px",
        backgroundColor: "#ffffff",
        borderBottom: "2px solid #e0e0e0",
        borderRadius: "0px",
        boxShadow: isHeaderFixed
          ? "0 4px 12px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.1)"
          : "none",
        gap: "16px",
        position: isHeaderFixed ? "fixed" : "relative",
        top: isHeaderFixed ? "32px" : "auto",
        left: isHeaderFixed ? "160px" : "auto",
        right: isHeaderFixed ? "0" : "auto",
        width: isHeaderFixed ? "calc(100% - 160px)" : "auto",
        zIndex: 1000,
        boxSizing: "border-box",
        transition: "all 0.25s ease-in-out",
      }}
    >
      {/* Left Section - Title and Status */}
      <div
        style={{
          display: "flex",
          alignItems: "center",
          gap: "12px",
          minWidth: 0,
          flex: 1,
        }}
      >
        <div
          style={{
            display: "flex",
            alignItems: "baseline",
            gap: "12px",
            minWidth: 0,
          }}
        >
          <h2
            style={{
              margin: 0,
              fontSize: "20px",
              fontWeight: "600",
              color: "#1a1a1a",
              overflow: "hidden",
              textOverflow: "ellipsis",
              whiteSpace: "nowrap",
            }}
          >
            {templateName || "Sans titre"}
          </h2>

          {/* Status Badges */}
          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: "8px",
              flexShrink: 0,
            }}
          >
            {deferredIsModified && (
              <span
                style={{
                  fontSize: "12px",
                  padding: "4px 10px",
                  backgroundColor: "#fff3cd",
                  color: "#856404",
                  borderRadius: "4px",
                  fontWeight: "500",
                  border: "1px solid #ffeaa7",
                  display: "flex",
                  alignItems: "center",
                  gap: "4px",
                }}
              >
                <span style={{ fontSize: "16px" }}>●</span>
                Modifié
              </span>
            )}
            {isNewTemplate && (
              <span
                style={{
                  fontSize: "12px",
                  padding: "4px 10px",
                  backgroundColor: "#d1ecf1",
                  color: "#0c5460",
                  borderRadius: "4px",
                  fontWeight: "500",
                  border: "1px solid #bee5eb",
                }}
              >
                Nouveau
              </span>
            )}
          </div>
        </div>
      </div>

      {/* Right Section - Action Buttons */}
      <div
        style={{
          display: "flex",
          gap: "10px",
          flexShrink: 0,
          alignItems: "center",
        }}
      >
        <button
          onClick={onNewTemplate}
          onMouseEnter={() => setHoveredButton("new")}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? "none" : "auto",
          }}
          title="Créer un nouveau template"
        >
          <span>➕</span>
          <span>Nouveau</span>
        </button>

        <div style={{ position: "relative" }} data-predefined-dropdown>
          <button
            onClick={() => setShowPredefinedTemplates(!showPredefinedTemplates)}
            onMouseEnter={() => setHoveredButton("predefined")}
            onMouseLeave={() => setHoveredButton(null)}
            style={{
              ...secondaryButtonStyles,
              opacity: isSaving ? 0.6 : 1,
              pointerEvents: isSaving ? "none" : "auto",
            }}
            title="Modèles prédéfinis"
          >
            <span>🎨</span>
            <span>Modèles Prédéfinis</span>
            <span style={{ marginLeft: "4px", fontSize: "12px" }}>▼</span>
          </button>

          {showPredefinedTemplates && (
            <div
              style={{
                position: "absolute",
                top: "100%",
                right: 0,
                background: "white",
                border: "1px solid #e0e0e0",
                borderRadius: "8px",
                boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                zIndex: 1001,
                minWidth: "280px",
                maxHeight: "400px",
                overflowY: "auto",
              }}
            >
              <div
                style={{
                  padding: "12px 16px",
                  borderBottom: "1px solid #e0e0e0",
                  background: "#f8f9fa",
                  fontWeight: "600",
                  fontSize: "14px",
                  color: "#23282d",
                }}
              >
                🎨 Modèles Prédéfinis
              </div>

              {/* Liste des modèles prédéfinis */}
              <div style={{ padding: "8px 0" }}>
                <div
                  style={{
                    padding: "12px 16px",
                    cursor: "pointer",
                    borderBottom: "1px solid #f0f0f0",
                    display: "flex",
                    alignItems: "center",
                    gap: "12px",
                  }}
                  onClick={() => {
                    // Ouvrir la page des templates prédéfinis
                    window.open(
                      "/wp-admin/admin.php?page=pdf-builder-templates",
                      "_blank"
                    );
                    setShowPredefinedTemplates(false);
                  }}
                  onMouseEnter={(e) =>
                    (e.currentTarget.style.backgroundColor = "#f8f9fa")
                  }
                  onMouseLeave={(e) =>
                    (e.currentTarget.style.backgroundColor = "transparent")
                  }
                >
                  <span style={{ fontSize: "20px" }}>🧾</span>
                  <div>
                    <div style={{ fontWeight: "500", color: "#23282d" }}>
                      Facture Professionnelle
                    </div>
                    <div style={{ fontSize: "12px", color: "#666" }}>
                      Template professionnel pour factures
                    </div>
                  </div>
                </div>

                <div
                  style={{
                    padding: "12px 16px",
                    cursor: "pointer",
                    borderBottom: "1px solid #f0f0f0",
                    display: "flex",
                    alignItems: "center",
                    gap: "12px",
                  }}
                  onClick={() => {
                    // Ouvrir la page des templates prédéfinis
                    window.open(
                      "/wp-admin/admin.php?page=pdf-builder-templates",
                      "_blank"
                    );
                    setShowPredefinedTemplates(false);
                  }}
                  onMouseEnter={(e) =>
                    (e.currentTarget.style.backgroundColor = "#f8f9fa")
                  }
                  onMouseLeave={(e) =>
                    (e.currentTarget.style.backgroundColor = "transparent")
                  }
                >
                  <span style={{ fontSize: "20px" }}>📋</span>
                  <div>
                    <div style={{ fontWeight: "500", color: "#23282d" }}>
                      Devis Commercial
                    </div>
                    <div style={{ fontSize: "12px", color: "#666" }}>
                      Template professionnel pour devis
                    </div>
                  </div>
                </div>

                <div
                  style={{
                    padding: "12px 16px",
                    cursor: "pointer",
                    borderBottom: "1px solid #f0f0f0",
                    display: "flex",
                    alignItems: "center",
                    gap: "12px",
                  }}
                  onClick={() => {
                    // Ouvrir la page des templates prédéfinis
                    window.open(
                      "/wp-admin/admin.php?page=pdf-builder-templates",
                      "_blank"
                    );
                    setShowPredefinedTemplates(false);
                  }}
                  onMouseEnter={(e) =>
                    (e.currentTarget.style.backgroundColor = "#f8f9fa")
                  }
                  onMouseLeave={(e) =>
                    (e.currentTarget.style.backgroundColor = "transparent")
                  }
                >
                  <span style={{ fontSize: "20px" }}>📦</span>
                  <div>
                    <div style={{ fontWeight: "500", color: "#23282d" }}>
                      Bon de Commande
                    </div>
                    <div style={{ fontSize: "12px", color: "#666" }}>
                      Template professionnel pour commandes
                    </div>
                  </div>
                </div>

                <div
                  style={{
                    padding: "12px 16px",
                    cursor: "pointer",
                    display: "flex",
                    alignItems: "center",
                    gap: "12px",
                    color: "#007cba",
                    fontWeight: "500",
                  }}
                  onClick={() => {
                    // Ouvrir la page des templates prédéfinis
                    window.open(
                      "/wp-admin/admin.php?page=pdf-builder-templates",
                      "_blank"
                    );
                    setShowPredefinedTemplates(false);
                  }}
                  onMouseEnter={(e) =>
                    (e.currentTarget.style.backgroundColor = "#f8f9fa")
                  }
                  onMouseLeave={(e) =>
                    (e.currentTarget.style.backgroundColor = "transparent")
                  }
                >
                  <span style={{ fontSize: "16px" }}>📚</span>
                  <span>Voir tous les modèles...</span>
                </div>
              </div>
            </div>
          )}
        </div>

        <button
          onClick={() => {
            console.log('[REACT HEADER] ===== APERÇU BUTTON CLICKED =====');
            console.log('[REACT HEADER] Aperçu button clicked - opening preview modal');
            console.log('[REACT HEADER] Current state before opening modal:');
            console.log('[REACT HEADER] - showPreviewModal:', showPreviewModal);
            console.log('[REACT HEADER] - isGeneratingPreview:', isGeneratingPreview);
            console.log('[REACT HEADER] - previewImageUrl:', previewImageUrl);
            console.log('[REACT HEADER] - previewError:', previewError);
            console.log('[REACT HEADER] - previewFormat:', previewFormat);
            console.log('[REACT HEADER] - Template state elements count:', state.elements?.length || 0);
            console.log('[REACT HEADER] - Template state has content:', !!(state.elements && state.elements.length > 0));
            console.log('[REACT HEADER] - usePreview hook available:', typeof usePreview);
            console.log('[REACT HEADER] - openModal function available:', typeof openPreviewModal);
            console.log('[REACT HEADER] About to call openPreviewModal()');
            openPreviewModal();
            console.log('[REACT HEADER] openPreviewModal() called successfully');
            console.log('[REACT HEADER] ===== APERÇU BUTTON CLICK HANDLER COMPLETED =====');
          }}
          onMouseEnter={() => setHoveredButton("preview")}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? "none" : "auto",
          }}
          title="Générer un aperçu du PDF (Image ou PDF)"
        >
          <span>👁️</span>
          <span>Aperçu</span>
        </button>

        <div
          style={{ width: "1px", height: "24px", backgroundColor: "#e0e0e0" }}
        />

        <button
          onClick={() => setShowJsonModal(true)}
          onMouseEnter={() => setHoveredButton("json")}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? "none" : "auto",
          }}
          title="Voir et copier le JSON du canvas"
        >
          <span>📄</span>
          <span>JSON</span>
        </button>

        <button
          onClick={() => setShowSettingsModal(true)}
          onMouseEnter={() => setHoveredButton("settings")}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? "none" : "auto",
          }}
          title="Paramètres du template"
        >
          <span>⚙️</span>
          <span>Paramètres</span>
        </button>

        <button
          onClick={async () => {
            const startTime = performance.now();
            debugLog("🚀 [PDF Builder] Bouton Enregistrer cliqué", {
              templateName,
              isModified: deferredIsModified,
              isSaving: deferredIsSaving,
              isLoading: deferredIsLoading,
              timestamp: new Date().toISOString(),
              // Informations détaillées sur le canvas
              canvasInfo: {
                width: canvasWidth,
                height: canvasHeight,
                showGuides,
                snapToGrid,
              },
              // Informations sur les éléments
              elementsInfo: {
                totalElements: state.elements?.length || 0,
                elementTypes:
                  state.elements?.reduce((acc: Record<string, number>, el) => {
                    acc[el.type] = (acc[el.type] || 0) + 1;
                    return acc;
                  }, {}) || {},
              },
              // État du builder
              builderState: {
                template: state.template
                  ? {
                      name: state.template.name,
                      description: state.template.description,
                      hasBackground: !!state.canvas.backgroundColor,
                    }
                  : null,
                selectedElement: state.selection.selectedElements[0] || null,
                zoom: state.canvas.zoom || 1,
              },
              // Paramètres canvas
              canvasSettings: {
                guidesEnabled: canvasSettings.guidesEnabled,
                memoryLimit: canvasSettings.memoryLimitJs,
              },
            });

            try {
              debugLog("⏳ [PDF Builder] Début de la sauvegarde...");
              await onSave();
              const endTime = performance.now();
              const saveDuration = endTime - startTime;

              debugLog("✅ [PDF Builder] Sauvegarde réussie", {
                templateName,
                timestamp: new Date().toISOString(),
                duration: `${saveDuration.toFixed(2)}ms`,
                performance: {
                  saveTime: saveDuration,
                  elementsCount: state.elements?.length || 0,
                  templateSize: JSON.stringify(state.template).length,
                  elementsSize: JSON.stringify(state.elements).length,
                },
                // Vérification post-sauvegarde
                postSaveState: {
                  isModified: false, // Devrait être false après sauvegarde
                  isSaving: false,
                },
              });

              // Log des métriques de performance
              debugLog("📊 [PDF Builder] Métriques de sauvegarde", {
                duration: saveDuration,
                avgTimePerElement: state.elements?.length
                  ? saveDuration / state.elements.length
                  : 0,
                memoryUsage: (performance as any).memory
                  ? {
                      used: (performance as any).memory.usedJSHeapSize,
                      total: (performance as any).memory.totalJSHeapSize,
                      limit: (performance as any).memory.jsHeapSizeLimit,
                    }
                  : "N/A",
              });
            } catch (error) {
              const endTime = performance.now();
              const failedDuration = endTime - startTime;

              debugError("❌ [PDF Builder] Erreur lors de la sauvegarde:", {
                error:
                  error instanceof Error
                    ? {
                        message: error.message,
                        stack: error.stack,
                        name: error.name,
                      }
                    : error,
                templateName,
                timestamp: new Date().toISOString(),
                duration: `${failedDuration.toFixed(2)}ms`,
                context: {
                  isModified: deferredIsModified,
                  isSaving: deferredIsSaving,
                  elementsCount: state.elements?.length || 0,
                },
              });
              alert(
                "Erreur lors de la sauvegarde: " +
                  (error instanceof Error ? error.message : "Erreur inconnue")
              );
            }
          }}
          disabled={
            deferredIsSaving || !deferredIsModified || deferredIsLoading
          }
          onMouseEnter={() => {
            debugLog("👆 [PDF Builder] Souris sur bouton Enregistrer", {
              templateName,
              buttonState: {
                disabled:
                  deferredIsSaving || !deferredIsModified || deferredIsLoading,
                isSaving: deferredIsSaving,
                isModified: deferredIsModified,
                isLoading: deferredIsLoading,
              },
              timestamp: new Date().toISOString(),
            });
            setHoveredButton("save");
          }}
          onMouseLeave={() => {
            debugLog("👋 [PDF Builder] Souris quitte bouton Enregistrer", {
              templateName,
              timestamp: new Date().toISOString(),
            });
            setHoveredButton(null);
          }}
          style={{
            ...primaryButtonStyles,
            opacity:
              deferredIsSaving || !deferredIsModified || deferredIsLoading
                ? 0.6
                : 1,
            pointerEvents:
              deferredIsSaving || !deferredIsModified || deferredIsLoading
                ? "none"
                : "auto",
          }}
          title={
            deferredIsLoading
              ? "Chargement du template..."
              : deferredIsModified
              ? deferredIsEditingExistingTemplate
                ? "Modifier le template"
                : "Enregistrer les modifications"
              : "Aucune modification"
          }
        >
          <span>{deferredIsSaving ? "⟳" : "💾"}</span>
          <span>
            {deferredIsSaving
              ? "Enregistrement..."
              : deferredIsEditingExistingTemplate
              ? "Modifier"
              : "Enregistrer"}
          </span>
        </button>
      </div>

      {/* Modale des paramètres du template */}
      {showSettingsModal && (
        <div className="canvas-modal-overlay" style={{ display: "flex" }}>
          <div className="canvas-modal-container">
            <div className="canvas-modal-header">
              <h3 style={{ margin: 0, fontSize: "20px", fontWeight: "600" }}>
                <span style={{ fontSize: "24px" }}>📄</span> Paramètres du template
              </h3>
              <button
                type="button"
                className="canvas-modal-close"
                onClick={() => setShowSettingsModal(false)}
                title="Fermer"
                style={{
                  background: "none",
                  border: "none",
                  fontSize: "24px",
                  cursor: "pointer",
                  color: "#666",
                  padding: "4px",
                }}
              >
                &times;
              </button>
            </div>
            <div className="canvas-modal-body">
              <div className="setting-group">
                <label className="setting-label">Nom du template</label>
                <input
                  type="text"
                  value={editedTemplateName}
                  onChange={(e) => setEditedTemplateName(e.target.value)}
                  className="setting-input"
                  placeholder="Entrez le nom du template"
                />
              </div>

              <div className="setting-group">
                <label className="setting-label">Description</label>
                <textarea
                  value={editedTemplateDescription}
                  onChange={(e) => setEditedTemplateDescription(e.target.value)}
                  className="setting-textarea"
                  placeholder="Description du template..."
                  rows={3}
                />
              </div>

              <div className="setting-group">
                <label className="setting-label">Dimensions du canvas</label>
                <div className="setting-input-group">
                  <input
                    type="number"
                    value={editedCanvasWidth}
                    disabled={true}
                    className="setting-input setting-input-disabled"
                    placeholder="Largeur"
                  />
                  <span className="setting-input-separator">×</span>
                  <input
                    type="number"
                    value={editedCanvasHeight}
                    disabled={true}
                    className="setting-input setting-input-disabled"
                    placeholder="Hauteur"
                  />
                  <span className="setting-unit">px</span>
                </div>
                <div className="setting-hint">Les dimensions sont contrôlées par l'orientation</div>
              </div>

              <div className="setting-group">
                <label className="setting-label">Orientation</label>
                <select
                  value={canvasOrientation}
                  onChange={(e) => {
                    const orientation = e.target.value as "portrait" | "landscape";
                    setCanvasOrientation(orientation);
                    // Mettre à jour les dimensions en fonction de l'orientation
                    let newWidth = 794;
                    let newHeight = 1123;
                    if (orientation === "landscape") {
                      newWidth = 1123;
                      newHeight = 794;
                    }
                    setEditedCanvasWidth(newWidth);
                    setEditedCanvasHeight(newHeight);
                    // Mettre à jour le template immédiatement
                    onUpdateTemplateSettings({
                      canvasWidth: newWidth,
                      canvasHeight: newHeight,
                    });
                  }}
                  className="setting-select"
                >
                  {orientationPermissions.allowPortrait && (
                    <option value="portrait">Portrait (794×1123 px)</option>
                  )}
                  {orientationPermissions.allowLandscape && (
                    <option value="landscape">Paysage (1123×794 px)</option>
                  )}
                </select>
                {(!orientationPermissions.allowPortrait ||
                  !orientationPermissions.allowLandscape) && (
                  <div className="setting-hint">
                    Certaines orientations sont désactivées dans les paramètres du plugin.
                  </div>
                )}
              </div>

              <div className="setting-group">
                <label className="setting-label">Options d'affichage</label>
                <div className="setting-checkbox-group">
                  <label className="setting-checkbox-label">
                    <input
                      type="checkbox"
                      checked={showGuides}
                      onChange={(e) => onUpdateTemplateSettings({ showGuides: e.target.checked })}
                      className="setting-checkbox"
                    />
                    Afficher les guides
                  </label>
                  <label className="setting-checkbox-label">
                    <input
                      type="checkbox"
                      checked={snapToGrid}
                      onChange={(e) => onUpdateTemplateSettings({ snapToGrid: e.target.checked })}
                      className="setting-checkbox"
                    />
                    Aimantation à la grille
                  </label>
                </div>
                <div className="setting-group">
                  <label className="setting-label">Statut</label>
                  <div className="setting-status-tags">
                    {isNewTemplate && (
                      <span className="status-tag status-new">Nouveau template</span>
                    )}
                    {deferredIsModified && (
                      <span className="status-tag status-modified">Modifié</span>
                    )}
                    {isEditingExistingTemplate && (
                      <span className="status-tag status-editing">Édition existante</span>
                    )}
                  </div>
                </div>

                <div className="setting-group">
                  <label className="setting-label">Informations système</label>
                  <div className="setting-info">
                    <div>Template ID: {templateName || "N/A"}</div>
                    <div>
                      Dernière modification: {new Date().toLocaleString("fr-FR")}
                    </div>
                    <div>
                      État:{" "}
                      {deferredIsSaving
                        ? "Enregistrement..."
                        : deferredIsModified
                        ? "Modifié"
                        : "Sauvegardé"}
                    </div>
                    {canvasSettings.performanceMonitoring && (
                      <div>
                        Performance: {performanceMetrics.fps} FPS, {performanceMetrics.memoryUsage}MB RAM
                      </div>
                    )}
                    {canvasSettings.debugMode && (
                      <div>
                        Debug: FPS Target {canvasSettings.fpsTarget}, Memory Limit {canvasSettings.memoryLimitJs}MB
                      </div>
                    )}
                  </div>
                </div>

              </div>
            </div>
              <div className="canvas-modal-footer">
                <button
                  onClick={() => setShowSettingsModal(false)}
                  className="canvas-modal-btn canvas-modal-btn-secondary"
                >
                  Annuler
                </button>
                <button
                  onClick={() => {
                    // Sauvegarder les paramètres du template
                    onUpdateTemplateSettings({
                      name: editedTemplateName,
                      description: editedTemplateDescription,
                      canvasWidth: editedCanvasWidth,
                      canvasHeight: editedCanvasHeight,
                      showGuides: showGuides,
                      snapToGrid: snapToGrid,
                    });

                    setShowSettingsModal(false);
                  }}
                  className="canvas-modal-btn canvas-modal-btn-primary"
                >
                  Sauvegarder
                </button>
              </div>
          </div>
        </div>
      )}

      {/* Modale JSON brut du template */}
      {showJsonModal && (
        <div
          style={{
            position: "fixed",
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: "rgba(0, 0, 0, 0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 1001,
          }}
        >
          <div
            style={{
              backgroundColor: "#ffffff",
              borderRadius: "8px",
              padding: "24px",
              maxWidth: "35vw",
              width: "100%",
              maxHeight: "85vh",
              display: "flex",
              flexDirection: "column",
              boxShadow: "0 10px 40px rgba(0, 0, 0, 0.3)",
            }}
          >
            {/* Header */}
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                marginBottom: "16px",
                borderBottom: "1px solid #e0e0e0",
                paddingBottom: "12px",
              }}
            >
              <h3
                style={{
                  margin: 0,
                  fontSize: "18px",
                  fontWeight: "600",
                  color: "#1a1a1a",
                }}
              >
                📋 Données du Template (ID: {templateName || "N/A"})
              </h3>
              <button
                onClick={() => setShowJsonModal(false)}
                style={{
                  background: "none",
                  border: "none",
                  fontSize: "24px",
                  cursor: "pointer",
                  color: "#666",
                  padding: "4px",
                }}
                title="Fermer"
              >
                ×
              </button>
            </div>

            {/* Tabs Navigation */}
            <div
              style={{
                display: "flex",
                gap: "8px",
                marginBottom: "16px",
                borderBottom: "2px solid #e0e0e0",
                paddingBottom: "0px",
              }}
            >
              <button
                onClick={() => setJsonModalView('json')}
                style={{
                  padding: "12px 20px",
                  border: "none",
                  borderBottom: jsonModalView === 'json' ? "3px solid #0073aa" : "3px solid transparent",
                  borderRadius: "0px",
                  backgroundColor: jsonModalView === 'json' ? "#f0f8ff" : "transparent",
                  color: jsonModalView === 'json' ? "#0073aa" : "#666",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: jsonModalView === 'json' ? "600" : "500",
                  transition: "all 0.2s ease",
                }}
                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = jsonModalView !== 'json' ? "#f9f9f9" : "#f0f8ff"}
                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = jsonModalView === 'json' ? "#f0f8ff" : "transparent"}
              >
                📄 Données JSON
              </button>
              <button
                onClick={() => {
                  if (generatedHtml.trim() === '') {
                    alert('Veuillez d\'abord générer l\'aperçu HTML');
                  } else {
                    setJsonModalView('html');
                  }
                }}
                style={{
                  padding: "12px 20px",
                  border: "none",
                  borderBottom: jsonModalView === 'html' ? "3px solid #10a37f" : "3px solid transparent",
                  borderRadius: "0px",
                  backgroundColor: jsonModalView === 'html' ? "#f0fdf4" : "transparent",
                  color: jsonModalView === 'html' ? "#10a37f" : "#666",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: jsonModalView === 'html' ? "600" : "500",
                  transition: "all 0.2s ease",
                }}
                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = jsonModalView !== 'html' ? "#f9f9f9" : "#f0fdf4"}
                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = jsonModalView === 'html' ? "#f0fdf4" : "transparent"}
              >
                🌐 Aperçu HTML
              </button>
            </div>

            {/* Generation Action Bar */}
            {jsonModalView === 'html' && (
              <div
                style={{
                  display: "flex",
                  gap: "12px",
                  marginBottom: "16px",
                  padding: "12px",
                  backgroundColor: "#fffbf0",
                  borderRadius: "6px",
                  border: "1px solid #ffd699",
                }}
              >
                <button
                  onClick={generateHtmlPreview}
                  disabled={isGeneratingHtml}
                  style={{
                    flex: 1,
                    padding: "10px 16px",
                    border: "none",
                    borderRadius: "6px",
                    backgroundColor: isGeneratingHtml ? "#ccc" : "#10a37f",
                    color: "#fff",
                    cursor: isGeneratingHtml ? "not-allowed" : "pointer",
                    fontSize: "14px",
                    fontWeight: "500",
                    transition: "all 0.2s ease",
                    opacity: isGeneratingHtml ? 0.7 : 1,
                  }}
                >
                  {isGeneratingHtml ? (
                    <>
                      <span>⏳</span>
                      <span style={{ marginLeft: "8px" }}>Génération en cours...</span>
                    </>
                  ) : (
                    <>
                      <span>✨</span>
                      <span style={{ marginLeft: "8px" }}>Générer l'aperçu HTML</span>
                    </>
                  )}
                </button>
              </div>
            )}

            {/* Content Area */}
            <div
              style={{
                flex: 1,
                overflow: "auto",
                backgroundColor: jsonModalView === 'json' ? "#f5f5f5" : "#ffffff",
                borderRadius: "6px",
                padding: jsonModalView === 'json' ? "16px" : "0px",
                border: "1px solid #ddd",
                marginBottom: "16px",
                maxHeight: "450px",
                display: "flex",
                flexDirection: "column",
              }}
            >
              {jsonModalView === 'json' ? (
                <pre
                  style={{
                    fontFamily: "'Courier New', monospace",
                    fontSize: "11px",
                    lineHeight: "1.4",
                    color: "#1e1e1e",
                    margin: 0,
                    whiteSpace: "pre-wrap",
                    wordBreak: "break-word",
                    background: "transparent",
                    padding: 0,
                  }}
                >
                  {JSON.stringify(
                    {
                      ...state.template,
                      elements: state.elements,
                    },
                    null,
                    2
                  )}
                </pre>
              ) : generatedHtml.trim() === '' ? (
                <div
                  style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    height: "100%",
                    color: "#999",
                    fontSize: "14px",
                    fontStyle: "italic",
                  }}
                >
                  Aucun aperçu HTML généré. Cliquez sur "Générer l'aperçu HTML" pour commencer.
                </div>
              ) : (
                <div
                  style={{
                    width: "100%",
                    flex: 1,
                    overflow: "auto",
                    backgroundColor: "#ffffff",
                    padding: "12px",
                    borderRadius: "4px",
                    fontSize: "13px",
                    lineHeight: "1.5",
                  }}
                  dangerouslySetInnerHTML={{ __html: generatedHtml }}
                />
              )}
            </div>

            {/* Footer with Buttons */}
            <div
              style={{
                display: "flex",
                gap: "12px",
                justifyContent: "flex-end",
                alignItems: "center",
              }}
            >
              {jsonModalView === 'json' && (
                <>
                  <button
                    onClick={() => {
                      navigator.clipboard.writeText(
                        JSON.stringify(
                          {
                            ...state.template,
                            elements: state.elements,
                          },
                          null,
                          2
                        )
                      );
                      setCopySuccess(true);
                      setTimeout(() => setCopySuccess(false), 2000);
                    }}
                    style={{
                      padding: "8px 16px",
                      backgroundColor: "#0073aa",
                      color: "#ffffff",
                      border: "none",
                      borderRadius: "4px",
                      cursor: "pointer",
                      fontSize: "14px",
                      fontWeight: "500",
                      opacity: copySuccess ? 0.7 : 1,
                    }}
                    title="Copier le JSON"
                  >
                    {copySuccess ? "✅ Copié!" : "📋 Copier JSON"}
                  </button>
                  <button
                    onClick={() => {
                      const jsonString = JSON.stringify(
                        {
                          ...state.template,
                          elements: state.elements,
                        },
                        null,
                        2
                      );
                      const blob = new Blob([jsonString], {
                        type: "application/json",
                      });
                      const url = URL.createObjectURL(blob);
                      const link = document.createElement("a");
                      link.href = url;
                      link.download = `template-${
                        templateName || "export"
                      }-${new Date().getTime()}.json`;
                      link.click();
                      URL.revokeObjectURL(url);
                    }}
                    style={{
                      padding: "8px 16px",
                      backgroundColor: "#10a37f",
                      color: "#ffffff",
                      border: "none",
                      borderRadius: "4px",
                      cursor: "pointer",
                      fontSize: "14px",
                      fontWeight: "500",
                    }}
                    title="Télécharger le JSON"
                  >
                    💾 Télécharger
                  </button>
                </>
              )}
              <button
                onClick={() => setShowJsonModal(false)}
                style={{
                  padding: "8px 16px",
                  border: "1px solid #ddd",
                  borderRadius: "4px",
                  backgroundColor: "#f8f8f8",
                  color: "#333",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: "500",
                }}
              >
                Fermer
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modale d'aperçu PDF */}
      {showPreviewModal && (
        <div
          style={{
            position: "fixed",
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: "rgba(0, 0, 0, 0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 1001,
          }}
          onLoad={() => {
            console.log('[REACT HEADER] ===== PREVIEW MODAL RENDERING =====');
            console.log('[REACT HEADER] Preview modal is open');
            console.log('[REACT HEADER] Modal state:');
            console.log('[REACT HEADER] - showPreviewModal:', showPreviewModal);
            console.log('[REACT HEADER] - isGeneratingPreview:', isGeneratingPreview);
            console.log('[REACT HEADER] - previewImageUrl:', previewImageUrl);
            console.log('[REACT HEADER] - previewError:', previewError);
            console.log('[REACT HEADER] - previewFormat:', previewFormat);
            console.log('[REACT HEADER] Modal rendering timestamp:', Date.now());
          }}
        >
          <div
            style={{
              backgroundColor: "#ffffff",
              borderRadius: "8px",
              padding: "24px",
              maxWidth: "90vw",
              width: "600px",
              maxHeight: "90vh",
              overflow: "auto",
              boxShadow: "0 4px 20px rgba(0, 0, 0, 0.15)",
            }}
          >
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                marginBottom: "20px",
              }}
            >
              <h3
                style={{
                  margin: 0,
                  fontSize: "18px",
                  fontWeight: "600",
                  color: "#1a1a1a",
                }}
              >
                Aperçu du PDF
              </h3>
              <button
                onClick={() => {
                  closePreviewModal();
                  clearPreview();
                }}
                style={{
                  background: "none",
                  border: "none",
                  fontSize: "24px",
                  cursor: "pointer",
                  color: "#666",
                  padding: "0",
                  width: "30px",
                  height: "30px",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                }}
                title="Fermer"
              >
                ×
              </button>
            </div>

            {/* Options de format */}
            <div style={{ marginBottom: "20px" }}>
              <label
                style={{
                  display: "block",
                  fontSize: "14px",
                  fontWeight: "500",
                  color: "#333",
                  marginBottom: "8px",
                }}
              >
                Format d&apos;export :
              </label>
              <div style={{ display: "flex", gap: "10px" }}>
                {[
                  { value: "png", label: "PNG", icon: "🖼️" },
                  { value: "jpg", label: "JPG", icon: "📷" },
                  { value: "pdf", label: "PDF", icon: "📄" },
                ].map((format) => (
                  <button
                    key={format.value}
                    onClick={() =>
                      setPreviewFormat(format.value as "png" | "jpg" | "pdf")
                    }
                    style={{
                      padding: "8px 16px",
                      border: `2px solid ${
                        previewFormat === format.value ? "#007cba" : "#ddd"
                      }`,
                      borderRadius: "6px",
                      backgroundColor:
                        previewFormat === format.value ? "#f0f8ff" : "#fff",
                      color:
                        previewFormat === format.value ? "#007cba" : "#333",
                      cursor: "pointer",
                      fontSize: "14px",
                      fontWeight: "500",
                      display: "flex",
                      alignItems: "center",
                      gap: "6px",
                    }}
                  >
                    <span>{format.icon}</span>
                    <span>{format.label}</span>
                  </button>
                ))}
              </div>
            </div>

            {/* Bouton de génération */}
            <div style={{ marginBottom: "20px" }}>
              <button
                onClick={async () => {
                  console.log('[HEADER COMPONENT] ===== MODAL PREVIEW BUTTON CLICKED =====');
                  console.log('[HEADER COMPONENT] Preview button clicked in modal');
                  console.log('[HEADER COMPONENT] Timestamp:', Date.now());
                  console.log('[HEADER COMPONENT] State template:', state.template);
                  console.log('[HEADER COMPONENT] State template ID:', state.template?.id);
                  console.log('[HEADER COMPONENT] State elements:', state.elements);
                  console.log('[HEADER COMPONENT] State elements count:', state.elements?.length || 0);
                  console.log('[HEADER COMPONENT] Preview format:', previewFormat);
                  console.log('[HEADER COMPONENT] Is generating:', isGeneratingPreview);
                  console.log('[HEADER COMPONENT] generatePreview function available:', typeof generatePreview);
                  console.log('[HEADER COMPONENT] About to call generatePreview');

                  await generatePreview(
                    {
                      ...state.template,
                      elements: state.elements,
                      template_id: state.template?.id || window.pdfBuilderData?.templateId || null,
                    },
                    {
                      format: previewFormat,
                      quality: 150,
                    }
                  );

                  console.log('[HEADER COMPONENT] generatePreview call completed');
                }}
                disabled={isGeneratingPreview}
                style={{
                  padding: "12px 24px",
                  backgroundColor: isGeneratingPreview ? "#ccc" : "#007cba",
                  color: "#fff",
                  border: "none",
                  borderRadius: "6px",
                  cursor: isGeneratingPreview ? "not-allowed" : "pointer",
                  fontSize: "16px",
                  fontWeight: "500",
                  display: "flex",
                  alignItems: "center",
                  gap: "8px",
                }}
              >
                {isGeneratingPreview ? (
                  <>
                    <span>⟳</span>
                    <span>Génération en cours...</span>
                  </>
                ) : (
                  <>
                    <span>🎨</span>
                    <span>Générer l&apos;aperçu</span>
                  </>
                )}
              </button>
            </div>

            {/* Affichage de l'erreur */}
            {previewError && (
              <div
                style={{
                  padding: "12px",
                  backgroundColor: "#f8d7da",
                  border: "1px solid #f5c6cb",
                  borderRadius: "4px",
                  color: "#721c24",
                  marginBottom: "20px",
                }}
              >
                <strong>Erreur:</strong> {previewError}
                {console.log('[HEADER COMPONENT] Displaying error in UI:', previewError)}
              </div>
            )}

            {/* Affichage de l'aperçu */}
            {previewImageUrl && (
              <div style={{ textAlign: "center" }}>
                <img
                  src={previewImageUrl}
                  alt="Aperçu du PDF"
                  style={{
                    maxWidth: "100%",
                    maxHeight: "400px",
                    border: "1px solid #ddd",
                    borderRadius: "4px",
                    boxShadow: "0 2px 8px rgba(0, 0, 0, 0.1)",
                  }}
                />
                <div style={{ marginTop: "10px" }}>
                  <a
                    href={previewImageUrl}
                    download={`apercu-${
                      templateName || "template"
                    }.${previewFormat}`}
                    style={{
                      padding: "8px 16px",
                      backgroundColor: "#28a745",
                      color: "#fff",
                      textDecoration: "none",
                      borderRadius: "4px",
                      fontSize: "14px",
                      fontWeight: "500",
                    }}
                  >
                    💾 Télécharger
                  </a>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
});


