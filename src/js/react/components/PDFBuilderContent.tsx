import React, { useState, useEffect, memo, useCallback } from "react";
import { Canvas } from "./canvas/Canvas";
import { Toolbar } from "./toolbar/Toolbar";
import { PropertiesPanel } from "./properties/PropertiesPanel";
import { Header } from "./header/Header";
import { ElementLibrary } from "./element-library/ElementLibrary";
import { useTemplate } from "../hooks/useTemplate";
import { useBuilder } from "../contexts/builder/BuilderContext";
import { useCanvasSettings, DEFAULT_SETTINGS } from "../contexts/CanvasSettingsContext";
import {
  DEFAULT_CANVAS_WIDTH,
  DEFAULT_CANVAS_HEIGHT,
} from "../constants/canvas";
import { injectResponsiveUtils } from "../utils/responsive";
import { useIsMobile, useIsTablet } from "../hooks/useResponsive";
import { debugLog, debugError } from "../utils/debug";

// Déclaration des types pour les fonctions de notification globales
declare global {
  interface Window {
    showSuccessNotification?: (message: string, duration?: number) => void;
    showErrorNotification?: (message: string, duration?: number) => void;
    showWarningNotification?: (message: string, duration?: number) => void;
    showInfoNotification?: (message: string, duration?: number) => void;
  }
}

// ✅ Add spin animation
const spinStyles = `
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
`;

// Inject CSS
if (typeof document !== "undefined") {
  const style = document.createElement("style");
  style.textContent = spinStyles;
  document.head.appendChild(style);
}

interface PDFBuilderContentProps {
  width?: number;
  height?: number;
  className?: string;
}

export const PDFBuilderContent = memo(function PDFBuilderContent({
  width = DEFAULT_CANVAS_WIDTH,
  height = DEFAULT_CANVAS_HEIGHT,
  className,
}: PDFBuilderContentProps) {  
  debugLog("🏗️ PDFBuilderContent: Component initialized with props:", {
    width,
    height,
    className,
  });

  const { state } = useBuilder();
  const isPreviewMode = state.previewMode === 'command';

  const [isHeaderFixed, setIsHeaderFixed] = useState(false);
  const [isPropertiesPanelOpen, setIsPropertiesPanelOpen] = useState(false);
  const [manualSaveSuccess, setManualSaveSuccess] = useState(false);

  // Vérification de licence pour les fonctionnalités premium
  const isPremium = window.pdfBuilderData?.license?.isPremium || false;

  // DEBUG: Log license data reception
  // console.log('🔑 [PDFBuilderContent DEBUG] License data:', {
  //   windowPdfBuilderData: window.pdfBuilderData,
  //   license: window.pdfBuilderData?.license,
  //   isPremium: isPremium
  // });

  debugLog("📱 PDFBuilderContent: Initial state set:", {
    isHeaderFixed,
    isPropertiesPanelOpen,
    manualSaveSuccess,
    isPreviewMode,
  });

  // Hooks responsives
  const isMobile = useIsMobile();
  const isTablet = useIsTablet();

  debugLog("📱 PDFBuilderContent: Responsive hooks:", { isMobile, isTablet });

  const {
    templateName,
    templateDescription,
    canvasWidth,
    canvasHeight,
    marginTop,
    marginBottom,
    showGuides,
    snapToGrid,
    isNewTemplate,
    isModified,
    isSaving,
    isLoading, // ✅ NEW: Template is loading
    isEditingExistingTemplate,
    saveTemplate,
    previewTemplate,
    newTemplate,
    updateTemplateSettings,
  } = useTemplate();

  debugLog("📋 PDFBuilderContent: useTemplate hook values:", {
    templateName,
    templateDescription,
    canvasWidth,
    canvasHeight,
    marginTop,
    marginBottom,
    showGuides,
    snapToGrid,
    isNewTemplate,
    isModified,
    isSaving,
    isLoading,
    isEditingExistingTemplate,
  });

  // Hook pour les paramètres du canvas
  const canvasSettings = useCanvasSettings();

  debugLog("🎨 PDFBuilderContent: Canvas settings:", canvasSettings);


  // Vérifier les erreurs de chargement des paramètres du canvas
  useEffect(() => {
    if (canvasSettings.error) {
      debugError(
        "❌ PDFBuilderContent: Canvas settings error:",
        canvasSettings.error
      );

      // Afficher une notification d'erreur
      if (typeof window !== "undefined" && window.showErrorNotification) {
        debugLog(
          "🔔 PDFBuilderContent: Showing canvas settings error notification"
        );
        window.showErrorNotification(
          `Erreur lors du chargement des paramètres: ${canvasSettings.error}`
        );
      }
    }
  }, [canvasSettings.error]);

  // Injection des utilitaires responsives
  useEffect(() => {
    debugLog("🔧 PDFBuilderContent: Injecting responsive utils");
    injectResponsiveUtils();
    debugLog("✅ PDFBuilderContent: Responsive utils injected");
  }, []);

  // Effet pour gérer le scroll et ajuster le padding
  useEffect(() => {
    debugLog("📜 PDFBuilderContent: Setting up scroll handler");

    const handleScroll = () => {
      const scrollTop =
        window.pageYOffset || document.documentElement.scrollTop;
      const newIsHeaderFixed = scrollTop > 100;
      debugLog(
        "📜 PDFBuilderContent: Scroll detected, scrollTop:",
        scrollTop,
        "isHeaderFixed:",
        newIsHeaderFixed
      );
      setIsHeaderFixed(newIsHeaderFixed);
    };

    window.addEventListener("scroll", handleScroll, { passive: true });
    debugLog("✅ PDFBuilderContent: Scroll handler added");

    return () => {
      debugLog("🧹 PDFBuilderContent: Cleaning up scroll handler");
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  // Wrapper pour sauvegarder
  const saveTemplateWithAutoSave = useCallback(async () => {
    debugLog("💾 PDFBuilderContent: Manual save initiated");

    try {
      // Effectuer la sauvegarde manuelle
      debugLog("🔄 PDFBuilderContent: Calling saveTemplate...");
      await saveTemplate();
      debugLog("✅ PDFBuilderContent: Manual save successful");
      debugLog("[PDF_BUILDER] Manual save successful");

      // Afficher une notification de succès
      if (typeof window !== "undefined" && window.showSuccessNotification) {
        debugLog("🔔 PDFBuilderContent: Showing success notification");
        window.showSuccessNotification("Template sauvegardé avec succès !");
      }
    } catch (manualSaveError) {
      debugError("❌ PDFBuilderContent: Manual save failed:", manualSaveError);
      debugError("[PDF_BUILDER] Manual save failed:", manualSaveError);

      // Afficher une notification d'erreur
      if (typeof window !== "undefined" && window.showErrorNotification) {
        debugLog("🔔 PDFBuilderContent: Showing error notification");
        window.showErrorNotification(
          "Erreur lors de la sauvegarde du template"
        );
      }

      throw manualSaveError; // Re-throw pour que l'UI montre l'erreur
    }
  }, [saveTemplate]);

  return (
    <>
      <div
        className={`pdf-builder ${className || ""}`}
        style={{
          display: "flex",
          flexDirection: "column",
          width: "100%",
          gap: "0px",
          padding: "0px",
          backgroundColor: "#ffffff",
          border: "none",
          borderRadius: "0px",
          paddingTop: isHeaderFixed && !isPreviewMode ? "132px" : "0px",
          transition: "padding 0.3s ease",
        }}
      >
        {/* Header en haut - masqué en mode preview */}
        {!isPreviewMode && (
          <Header
            templateName={templateName || ""}
            templateDescription={templateDescription || ""}
            canvasWidth={canvasWidth || 794}
            canvasHeight={canvasHeight || 1123}
            showGuides={showGuides || true}
            snapToGrid={snapToGrid || false}
            isNewTemplate={isNewTemplate}
            isModified={isModified}
            isSaving={isSaving}
            isLoading={isLoading}
            isEditingExistingTemplate={isEditingExistingTemplate}
            onSave={saveTemplateWithAutoSave}
            onPreview={previewTemplate}
            onNewTemplate={newTemplate}
            onUpdateTemplateSettings={updateTemplateSettings}
          />
        )}

        {/* Toolbar sous le header - masqué en mode preview */}
        {!isPreviewMode && (
          <div style={{ flexShrink: 0, padding: "12px 12px 0 12px" }}>
            <Toolbar />
          </div>
        )}

        {/* Contenu principal */}
        <div style={{ display: "flex", flex: 1, gap: "0", padding: isPreviewMode ? "0" : "12px" }}>
          {/* Sidebar des éléments WooCommerce - masqué en mode preview */}
          {!isPreviewMode && <ElementLibrary />}

          {/* Zone centrale avec le canvas */}
          <div
            style={{
              flex: 1,
              display: "flex",
              flexDirection: "column",
              position: "relative",
            }}
          >
            <div
              style={{
                flex: 1,
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                backgroundColor: !isPremium
                  ? DEFAULT_SETTINGS.containerBackgroundColor // Fond par défaut en mode gratuit
                  : (canvasSettings.containerBackgroundColor || DEFAULT_SETTINGS.containerBackgroundColor),
                border: "1px solid #e0e0e0",
                borderRadius: "4px",
                overflow: "auto",
                position: "relative",
                paddingTop: "20px",
                paddingBottom: "20px",
              }}
            >
              {/* Indicateur de dimensions avec format et DPI */}
              {!isPreviewMode && (
                <div
                  style={{
                    position: "absolute",
                    top: "8px",
                    right: "8px",
                    backgroundColor: "rgba(0, 122, 204, 0.9)",
                    color: "white",
                    padding: "4px 8px",
                    borderRadius: "4px",
                    fontSize: "12px",
                    fontWeight: "bold",
                    zIndex: 10,
                  }}
                >
                  {(() => {
                    const format =
                      (window as any).pdfBuilderCanvasSettings
                        ?.default_canvas_format || "A4";
                    const dpi =
                      (window as any).pdfBuilderCanvasSettings
                        ?.default_canvas_dpi || 96;
                    const orientation =
                      (window as any).pdfBuilderCanvasSettings
                        ?.default_canvas_orientation || "portrait";
                    const paperFormats = (window as any)
                      .pdfBuilderPaperFormats || {
                      A4: { width: 210, height: 297 },
                      A3: { width: 297, height: 420 },
                      A5: { width: 148, height: 210 },
                      Letter: { width: 215.9, height: 279.4 },
                      Legal: { width: 215.9, height: 355.6 },
                      Tabloid: { width: 279.4, height: 431.8 },
                    };

                    // Récupérer les dimensions en mm
                    const dimsMM = paperFormats[format] || paperFormats["A4"];

                    // Calculer les dimensions en pixels avec le DPI actuel
                    const pixelsPerMM = dpi / 25.4;
                    let widthPx = Math.round(dimsMM.width * pixelsPerMM);
                    let heightPx = Math.round(dimsMM.height * pixelsPerMM);

                    // Inverser si orientation paysage
                    if (orientation === "landscape") {
                      [widthPx, heightPx] = [heightPx, widthPx];
                    }

                    return `${format}: ${widthPx}×${heightPx}px (${dpi} DPI)`;
                  })()}
                </div>
              )}

              {/* Bouton Générer PDF en mode preview */}
              {isPreviewMode && (
                <button
                  onClick={() => {
                    // Récupérer les paramètres URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const templateId = urlParams.get('template_id');
                    const orderId = urlParams.get('order_id');
                    
                    if (!templateId || !orderId) {
                      if (window.showErrorNotification) {
                        window.showErrorNotification('Paramètres manquants pour générer le PDF');
                      }
                      return;
                    }

                    // Construire l'URL de génération PDF
                    const pdfUrl = `${window.location.origin}/wp-admin/admin-ajax.php?action=pdf_builder_generate_pdf&template_id=${templateId}&order_id=${orderId}`;
                    
                    // Ouvrir dans un nouvel onglet
                    window.open(pdfUrl, '_blank');
                  }}
                  style={{
                    position: "absolute",
                    top: "16px",
                    right: "16px",
                    backgroundColor: "#0073aa",
                    color: "white",
                    padding: "12px 24px",
                    borderRadius: "6px",
                    fontSize: "14px",
                    fontWeight: "600",
                    border: "none",
                    cursor: "pointer",
                    boxShadow: "0 2px 8px rgba(0, 115, 170, 0.3)",
                    zIndex: 10,
                    display: "flex",
                    alignItems: "center",
                    gap: "8px",
                    transition: "all 0.2s ease",
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.backgroundColor = "#005a87";
                    e.currentTarget.style.transform = "translateY(-1px)";
                    e.currentTarget.style.boxShadow = "0 4px 12px rgba(0, 115, 170, 0.4)";
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.backgroundColor = "#0073aa";
                    e.currentTarget.style.transform = "translateY(0)";
                    e.currentTarget.style.boxShadow = "0 2px 8px rgba(0, 115, 170, 0.3)";
                  }}
                  title="Télécharger le PDF"
                >
                  <svg 
                    width="16" 
                    height="16" 
                    viewBox="0 0 24 24" 
                    fill="none" 
                    stroke="currentColor" 
                    strokeWidth="2" 
                    strokeLinecap="round" 
                    strokeLinejoin="round"
                  >
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                  </svg>
                  Générer PDF
                </button>
              )}

              {/* ✅ Loading spinner overlay */}
              {isLoading && (
                <div
                  style={{
                    position: "absolute",
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: "rgba(255, 255, 255, 0.7)",
                    display: "flex",
                    justifyContent: "center",
                    alignItems: "center",
                    zIndex: 100,
                    borderRadius: "4px",
                  }}
                >
                  <div style={{ textAlign: "center" }}>
                    <div
                      style={{
                        width: "40px",
                        height: "40px",
                        border: "4px solid #e0e0e0",
                        borderTop: "4px solid #007acc",
                        borderRadius: "50%",
                        animation: "spin 1s linear infinite",
                        margin: "0 auto 12px",
                      }}
                    />
                    <p style={{ margin: 0, color: "#666", fontSize: "14px" }}>
                      Chargement du template...
                    </p>
                  </div>
                </div>
              )}

              {/* ✅ ONLY render Canvas when template is loaded OR it's a new template */}
              {!isLoading && (
                <>
                  {debugLog(
                    "🎨 PDFBuilderContent: Rendering Canvas component",
                    { canvasWidth, canvasHeight }
                  )}
                  <Canvas
                    width={canvasWidth || width}
                    height={canvasHeight || height}
                  />
                </>
              )}
            </div>

            {/* Bouton toggle pour le panneau de propriétés - masqué en mode preview */}
            {!isPreviewMode && (
              <button
                onClick={() => {
                  debugLog(
                    "🔘 PDFBuilderContent: Properties panel toggle clicked, current state:",
                    isPropertiesPanelOpen
                  );
                  setIsPropertiesPanelOpen(!isPropertiesPanelOpen);
                  debugLog(
                    "🔄 PDFBuilderContent: Properties panel state changed to:",
                    !isPropertiesPanelOpen
                  );
                }}
                style={{
                  position: "absolute",
                  top: "50%",
                  right: isPropertiesPanelOpen ? "-12px" : "0px",
                  transform: "translateY(-50%)",
                  zIndex: 20,
                  padding: "8px 6px",
                  backgroundColor: "#007acc",
                  color: "white",
                  border: "none",
                  borderRadius: "4px 0 0 4px",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: "bold",
                  boxShadow: "0 2px 4px rgba(0,0,0,0.2)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  width: "24px",
                  height: "60px",
                  writingMode: "vertical-rl",
                  textOrientation: "mixed",
                }}
                title={
                  isPropertiesPanelOpen
                    ? "Fermer le panneau de propriétés"
                    : "Ouvrir le panneau de propriétés"
                }
              >
                {isPropertiesPanelOpen ? "▷" : "◁"}
              </button>
            )}
          </div>

          {/* Panneau de propriétés à droite - masqué en mode preview */}
          {!isPreviewMode && isPropertiesPanelOpen && (
            <div
              style={{
                flexShrink: 0,
                width: "430px",
                position: "sticky",
                top: "110px",
                height: "fit-content",
                maxHeight: "calc(100vh - 32px)",
              }}
            >
              <PropertiesPanel />
            </div>
          )}
        </div>
      </div>
    </>
  );
});


