import React, {
  useState,
  useEffect,
  useCallback,
  memo,
  useDeferredValue,
} from "react";
import { TemplateState } from "../../types/elements";
import { useBuilder } from "../../contexts/builder/BuilderContext";
// Preview system removed

import { useCanvasSettings } from "../../contexts/CanvasSettingsContext";
import { debugLog, debugError } from "../../utils/debug";

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
    isEditingExistingTemplate,
  );

  // État pour le flag du mode développeur (récupéré dynamiquement)
  const [developerModeActive, setDeveloperModeActive] = useState(false);

  // Récupérer le flag du mode développeur dynamiquement
  useEffect(() => {
    const checkDeveloperMode = async () => {
      try {
        const ajaxUrl =
          (window as any).pdfBuilderData?.ajaxUrl || "/wp-admin/admin-ajax.php";
        const nonce = (window as any).pdfBuilderData?.nonce || "";

        const formData = new FormData();
        formData.append("action", "pdf_builder_get_developer_mode");
        if (nonce) {
          formData.append("nonce", nonce);
        }

        const response = await fetch(ajaxUrl, {
          method: "POST",
          body: formData,
        });

        if (response.ok) {
          const result = await response.json();

          if (result.success && result.data) {
            const isActive =
              result.data.developerModeActive === true ||
              result.data.developerModeActive === "1";
            setDeveloperModeActive(isActive);
          }
        }
      } catch (error) {
        // Fallback: essayer d'obtenir du pdfBuilderData
        const existingValue =
          (window as any).pdfBuilderData?.developerModeActive || false;
        setDeveloperModeActive(
          existingValue === true || existingValue === "true",
        );
      }
    };

    // Délai court pour s'assurer que pdfBuilderData est disponible
    const timer = setTimeout(checkDeveloperMode, 100);
    return () => clearTimeout(timer);
  }, []);

  // Debug logging
  useEffect(() => {}, []);

  const { state, dispatch } = useBuilder();
  const canvasSettings = useCanvasSettings();
  const [hoveredButton, setHoveredButton] = useState<string | null>(null);
  const [showSettingsModal, setShowSettingsModal] = useState(false);
  const [showJsonModal, setShowJsonModal] = useState(false);
  const [jsonModalMode, setJsonModalMode] = useState<"json" | "html">("json");
  const [copySuccess, setCopySuccess] = useState(false);
  const [isGeneratingHtml, setIsGeneratingHtml] = useState(false);
  const [isHeaderFixed, setIsHeaderFixed] = useState(false);
  const [generatedHtml, setGeneratedHtml] = useState<string>("");
  const [performanceMetrics, setPerformanceMetrics] = useState({
    fps: 0,
    memoryUsage: 0,
    lastUpdate: 0,
  });
  const [editedTemplateName, setEditedTemplateName] = useState(templateName);
  const [editedTemplateDescription, setEditedTemplateDescription] =
    useState(templateDescription);

  // États pour le drag du modal JSON
  const [modalPosition, setModalPosition] = useState<{ x: number; y: number }>({
    x: 0,
    y: 0,
  });
  const [isDraggingModal, setIsDraggingModal] = useState(false);
  const [dragStart, setDragStart] = useState<{ x: number; y: number } | null>(
    null,
  );
  const [editedCanvasWidth, setEditedCanvasWidth] = useState(canvasWidth);
  const [editedCanvasHeight, setEditedCanvasHeight] = useState(canvasHeight);
  const [canvasOrientation, setCanvasOrientation] = useState<
    "portrait" | "landscape"
  >(canvasWidth < canvasHeight ? "portrait" : "landscape");
  const [showPredefinedTemplates, setShowPredefinedTemplates] = useState(false);
  const [orientationPermissions, setOrientationPermissions] = useState<{
    allowPortrait: boolean;
    allowLandscape: boolean;
    defaultOrientation: "portrait" | "landscape";
    availableOrientations: string[];
  }>({
    allowPortrait: true,
    allowLandscape: true,
    defaultOrientation: "portrait",
    availableOrientations: ["portrait", "landscape"],
  });

  // États pour la modale d'aperçu
  const [showPreviewModal, setShowPreviewModal] = useState(false);
  const [previewOrderId, setPreviewOrderId] = useState("");
  const [isGeneratingPreview, setIsGeneratingPreview] = useState(false);
  const [availableOrders, setAvailableOrders] = useState<
    Array<{
      id: string;
      number: string;
      customer: string;
      date: string;
      total: string;
    }>
  >([]);
  const [isLoadingOrders, setIsLoadingOrders] = useState(false);
  const [activeEngine, setActiveEngine] = useState<{
    name: string;
    icon: string;
  } | null>(null);

  // Vérifier le statut premium depuis pdfBuilderData
  const isPremium = (window as any).pdfBuilderData?.license?.isPremium || false;

  // Charger le moteur PDF actif
  const loadActiveEngine = async () => {
    try {
      const response = await fetch(
        (window as any).pdfBuilderData?.ajaxUrl || "",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            action: "pdf_builder_get_active_engine",
            nonce: (window as any).pdfBuilderNonce || "",
          }),
        },
      );

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.data) {
          setActiveEngine({
            name: data.data.display_name,
            icon: data.data.icon,
          });
        }
      }
    } catch (error) {
      console.error("Erreur lors du chargement du moteur PDF:", error);
    }
  };

  // Ouvrir la modale d'aperçu
  const handlePreview = async () => {
    setShowPreviewModal(true);
    setPreviewOrderId("");
    setIsLoadingOrders(true);

    // Charger le moteur actif en parallèle
    loadActiveEngine();

    try {
      // Récupérer la liste des commandes WooCommerce
      const response = await fetch(
        (window as any).pdfBuilderData?.ajaxUrl || "",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            action: "pdf_builder_get_orders_list",
            nonce: (window as any).pdfBuilderNonce || "",
          }),
        },
      );

      if (response.ok) {
        const data = await response.json();

        if (data.success && data.data) {
          setAvailableOrders(data.data);
        } else {
          alert(
            "Erreur lors du chargement des commandes: " +
              (data.data?.message || "Erreur inconnue"),
          );
        }
      } else {
        alert("Erreur réseau lors du chargement des commandes");
      }
    } catch (error) {
      console.error("Erreur lors du chargement des commandes:", error);
      alert(
        "Erreur: " + (error instanceof Error ? error.message : String(error)),
      );
    } finally {
      setIsLoadingOrders(false);
    }
  };

  // Ouvrir le HTML avec boutons Télécharger et Imprimer
  const openDebugHTML = async () => {
    if (!previewOrderId || previewOrderId.trim() === "") {
      alert("Veuillez sélectionner une commande");
      return;
    }

    const templateId = state.template?.id;
    if (!templateId) {
      alert("Erreur: Template ID manquant.");
      return;
    }

    try {
      // Récupérer le HTML via endpoint
      const formData = new FormData();
      formData.append("action", "pdf_builder_get_preview_html");
      formData.append("template_id", templateId.toString());
      formData.append("order_id", previewOrderId.trim());
      formData.append("nonce", (window as any).pdfBuilderNonce || "");

      const response = await fetch(
        (window as any).pdfBuilderData?.ajaxUrl || "/wp-admin/admin-ajax.php",
        {
          method: "POST",
          body: formData,
        },
      );

      if (!response.ok) {
        throw new Error(`Erreur serveur ${response.status}`);
      }

      const result = await response.json();
      if (!result.success) {
        throw new Error(result.data?.message || "Erreur inconnue");
      }

      const { html, order_number } = result.data;

      // Extraire les styles du head et le contenu du body
      const stylesMatch = html.match(/<head[^>]*>(.*?)<\/head>/is);
      const bodyMatch = html.match(/<body[^>]*>(.*?)<\/body>/is);
      const originalStyles = stylesMatch ? stylesMatch[1] : "";
      const bodyContent = bodyMatch ? bodyMatch[1] : html;

      // Créer une page HTML avec le contenu et les boutons
      const htmlPage = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facture ${order_number} - HTML</title>
    ${originalStyles}
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 12px;
            z-index: 1000;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .btn-download {
            background: #2271b1;
            color: white;
        }
        .btn-download:hover {
            background: #135e96;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(34,113,177,0.3);
        }
        .btn-print {
            background: #10b981;
            color: white;
        }
        .btn-print:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .btn-zoom {
            background: #6b7280;
            color: white;
            padding: 12px 16px;
        }
        .btn-zoom:hover {
            background: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(107,114,128,0.3);
        }
        .zoom-level {
            background: #f3f4f6;
            color: #374151;
            padding: 12px 16px;
            font-weight: bold;
            border-radius: 6px;
            min-width: 70px;
            text-align: center;
        }
        .content-wrapper {
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            transition: transform 0.2s;
            transform-origin: center top;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .content-wrapper {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn btn-zoom" onclick="zoomOut()" title="Zoom arrière">
            <span>🔍➖</span>
        </button>
        <div class="zoom-level" id="zoomLevel">100%</div>
        <button class="btn btn-zoom" onclick="zoomIn()" title="Zoom avant">
            <span>🔍➕</span>
        </button>
        <button class="btn btn-download" onclick="downloadHTML()">
            <span>📥</span>
            <span>Télécharger HTML</span>
        </button>
        <button class="btn btn-print" onclick="window.print()">
            <span>🖨️</span>
            <span>Imprimer</span>
        </button>
    </div>
    
    <div class="content-wrapper" id="contentWrapper">
        ${bodyContent}
    </div>
    
    <script>
        let zoomScale = 1.0;
        const wrapper = document.getElementById('contentWrapper');
        const zoomLevelDisplay = document.getElementById('zoomLevel');
        
        function updateZoom() {
            wrapper.style.transform = 'scale(' + zoomScale + ')';
            zoomLevelDisplay.textContent = Math.round(zoomScale * 100) + '%';
        }
        
        function zoomIn() {
            if (zoomScale < 3.0) {
                zoomScale += 0.25;
                updateZoom();
            }
        }
        
        function zoomOut() {
            if (zoomScale > 0.25) {
                zoomScale -= 0.25;
                updateZoom();
            }
        }
        
        function downloadHTML() {
            const content = \`${html.replace(/`/g, "\\`").replace(/\$/g, "\\$")}\`;
            const blob = new Blob([content], { type: 'text/html' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'facture-${order_number}.html';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }
    </script>
</body>
</html>`;

      // Ouvrir dans un blob
      const htmlBlob = new Blob([htmlPage], { type: "text/html" });
      const htmlUrl = URL.createObjectURL(htmlBlob);
      window.open(htmlUrl, "_blank");

      setTimeout(() => URL.revokeObjectURL(htmlUrl), 2000);
    } catch (error) {
      console.error("[HTML] Erreur:", error);
      alert("Erreur lors de la récupération du HTML");
    }
  };

  // Générer un PDF via AJAX
  const generatePDF = async () => {
    if (!previewOrderId || previewOrderId.trim() === "") {
      alert("Veuillez sélectionner une commande");
      return;
    }

    const templateId = state.template?.id;
    if (!templateId) {
      alert(
        "Erreur: Template ID manquant. Veuillez d'abord enregistrer le template.",
      );
      return;
    }

    setIsGeneratingPreview(true);
    try {
      const formData = new FormData();
      formData.append("action", "pdf_builder_generate_pdf");
      formData.append("template_id", templateId.toString());
      formData.append("order_id", previewOrderId.trim());
      formData.append("nonce", (window as any).pdfBuilderNonce || "");

      const response = await fetch(
        (window as any).pdfBuilderData?.ajaxUrl || "/wp-admin/admin-ajax.php",
        {
          method: "POST",
          body: formData,
        },
      );

      if (!response.ok) {
        throw new Error("Erreur lors de la génération du PDF");
      }

      // Ouvrir le PDF dans un nouvel onglet
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      window.open(url, "_blank");

      setShowPreviewModal(false);
    } catch (error) {
      console.error("[PREVIEW] Erreur génération PDF:", error);
      alert(
        "Erreur lors de la génération du PDF. Vérifiez la console pour plus de détails.",
      );
    } finally {
      setIsGeneratingPreview(false);
    }
  };

  // PNG/JPG - Génération d'image (premium uniquement)
  const generateImage = async (format: "png" | "jpg") => {
    if (!isPremium) {
      alert(
        `La génération en format ${format.toUpperCase()} est une fonctionnalité premium.\n\nActivez votre licence premium pour débloquer cette option.`,
      );
      return;
    }

    if (!previewOrderId || previewOrderId.trim() === "") {
      alert("Veuillez sélectionner une commande");
      return;
    }

    const templateId = state.template?.id;
    if (!templateId) {
      alert(
        "Erreur: Template ID manquant. Veuillez d'abord enregistrer le template.",
      );
      return;
    }

    setIsGeneratingPreview(true);
    try {
      // Appel direct à Puppeteer (backend) — génération PNG/JPG haute qualité
      const formData = new FormData();
      formData.append("action", "pdf_builder_generate_image");
      formData.append("template_id", templateId.toString());
      formData.append("order_id", previewOrderId.trim());
      formData.append("format", format);
      formData.append("nonce", (window as any).pdfBuilderNonce || "");

      const response = await fetch(
        (window as any).pdfBuilderData?.ajaxUrl || "/wp-admin/admin-ajax.php",
        {
          method: "POST",
          body: formData,
        },
      );

      if (!response.ok) {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
          const errorData = await response.json();
          // Afficher details en priorité (message d'erreur PHP réel)
          throw new Error(
            errorData.data?.details ||
              errorData.data?.message ||
              errorData.message ||
              `Erreur ${response.status}`,
          );
        }
        throw new Error(`Erreur serveur ${response.status}`);
      }

      // Lire le blob binaire retourné par Puppeteer et le convertir en data URL stable
      const blob = await response.blob();
      const imageDataUrl = await new Promise<string>((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result as string);
        reader.onerror = reject;
        reader.readAsDataURL(blob);
      });

      const order_number = previewOrderId.trim();
      const fileName = `facture-${order_number}.${format}`;

      // Créer une page HTML avec l'image et les boutons
      const htmlPage = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facture ${order_number}</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 12px;
            z-index: 1000;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .btn-download {
            background: #2271b1;
            color: white;
        }
        .btn-download:hover {
            background: #135e96;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(34,113,177,0.3);
        }
        .btn-print {
            background: #10b981;
            color: white;
        }
        .btn-print:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .btn-zoom {
            background: #6b7280;
            color: white;
            padding: 12px 16px;
        }
        .btn-zoom:hover {
            background: #4b5563;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(107,114,128,0.3);
        }
        .zoom-level {
            background: #f3f4f6;
            color: #374151;
            padding: 12px 16px;
            font-weight: bold;
            border-radius: 6px;
            min-width: 70px;
            text-align: center;
        }
        .image-container {
            margin-top: 60px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.1);
            max-width: 50%;
            transition: transform 0.2s;
            transform-origin: center top;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .image-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn btn-zoom" onclick="zoomOut()" title="Zoom arrière">
            <span>🔍➖</span>
        </button>
        <div class="zoom-level" id="zoomLevel">100%</div>
        <button class="btn btn-zoom" onclick="zoomIn()" title="Zoom avant">
            <span>🔍➕</span>
        </button>
        <button class="btn btn-download" onclick="downloadImage()">
            <span>📥</span>
            <span>Télécharger</span>
        </button>
        <button class="btn btn-print" onclick="window.print()">
            <span>🖨️</span>
            <span>Imprimer</span>
        </button>
    </div>
    
    <div class="image-container" id="imageContainer">
        <img src="${imageDataUrl}" alt="Facture ${order_number}" />
    </div>
    
    <script>
        let zoomScale = 1.0;
        const container = document.getElementById('imageContainer');
        const zoomLevelDisplay = document.getElementById('zoomLevel');
        
        function updateZoom() {
            container.style.transform = 'scale(' + zoomScale + ')';
            zoomLevelDisplay.textContent = Math.round(zoomScale * 100) + '%';
        }
        
        function zoomIn() {
            if (zoomScale < 3.0) {
                zoomScale += 0.25;
                updateZoom();
            }
        }
        
        function zoomOut() {
            if (zoomScale > 0.25) {
                zoomScale -= 0.25;
                updateZoom();
            }
        }
        
        function downloadImage() {
            const link = document.createElement('a');
            link.href = '${imageDataUrl}';
            link.download = '${fileName}';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>`;

      // Créer un blob HTML et l'ouvrir
      const htmlBlob = new Blob([htmlPage], { type: "text/html" });
      const htmlUrl = URL.createObjectURL(htmlBlob);
      window.open(htmlUrl, "_blank");

      // Pas besoin de révoquer - les data URLs sont stables
      setShowPreviewModal(false);
    } catch (error) {
      console.error(
        `[PREVIEW] Erreur génération ${format.toUpperCase()}:`,
        error,
      );

      const errorMessage =
        error instanceof Error ? error.message : String(error);
      alert(
        `Erreur lors de la génération ${format.toUpperCase()}\n\n${errorMessage}`,
      );
    } finally {
      setIsGeneratingPreview(false);
    }
  };

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
        const availableOrientations = (window as any).availableOrientations || [
          "portrait",
          "landscape",
        ];

        const orientationPermissions = {
          allowPortrait: availableOrientations.includes("portrait"),
          allowLandscape: availableOrientations.includes("landscape"),
          defaultOrientation: ((window as any).pdfBuilderCanvasSettings
            ?.default_canvas_orientation || "portrait") as
            | "portrait"
            | "landscape",
          availableOrientations: availableOrientations,
        };

        setOrientationPermissions(orientationPermissions);
      } catch (error) {
        debugError(
          "Erreur lors du chargement des permissions d'orientation",
          error,
        );
        // Fallback en cas d'erreur
        setOrientationPermissions({
          allowPortrait: true,
          allowLandscape: true,
          defaultOrientation: "portrait",
          availableOrientations: ["portrait", "landscape"],
        });
      }
    };

    loadOrientationPermissions();
  }, []);

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
        setPerformanceMetrics((prev) => ({
          fps: Math.floor(Math.random() * 20) + 40, // Simulation FPS 40-60
          memoryUsage: Math.floor(Math.random() * 50) + 80, // Simulation mémoire 80-130MB
          lastUpdate: now,
        }));
      };

      const interval = setInterval(updateMetrics, 2000); // Update every 2 seconds
      updateMetrics(); // Initial update

      return () => clearInterval(interval);
    }
  }, [canvasSettings.performanceMonitoring]);

  // State pour le throttling du scroll
  const [scrollTimeout, setScrollTimeout] = useState<number | null>(null);

  // Optimisation: mémoriser le handler de scroll avec throttling
  const handleScroll = useCallback(() => {
    if (scrollTimeout) return; // Si un timeout est déjà en cours, ignorer

    setScrollTimeout(
      window.setTimeout(() => {
        const scrollTop =
          window.pageYOffset || document.documentElement.scrollTop;
        // Le header devient fixe après 120px de scroll
        setIsHeaderFixed(scrollTop > 120);
        setScrollTimeout(null);
      }, 50),
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

  // Effet pour gérer le drag du modal JSON
  useEffect(() => {
    if (!isDraggingModal || !dragStart) return;

    const handleMouseMove = (e: MouseEvent) => {
      const deltaX = e.clientX - dragStart.x;
      const deltaY = e.clientY - dragStart.y;
      setModalPosition({ x: deltaX, y: deltaY });
    };

    const handleMouseUp = () => {
      setIsDraggingModal(false);
      setDragStart(null);
    };

    document.addEventListener("mousemove", handleMouseMove);
    document.addEventListener("mouseup", handleMouseUp);

    return () => {
      document.removeEventListener("mousemove", handleMouseMove);
      document.removeEventListener("mouseup", handleMouseUp);
    };
  }, [isDraggingModal, dragStart]);

  // Reset du drag quand on change de mode ou on ferme le modal
  useEffect(() => {
    setIsDraggingModal(false);
    setDragStart(null);
  }, [jsonModalMode, showJsonModal]);

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

  // Fonction pour générer HTML qui simule un PDF avec les paramètres du plugin
  const generatePDFSimulationHTML = (
    elementsInput?: any[],
    canvasInput?: any,
    templateInput?: any,
  ) => {
    // Utiliser les paramètres passés OU les données locales (pour compatibilité)
    const canvasWidth =
      canvasInput?.width || state.template?.canvasWidth || 794;
    const canvasHeight =
      canvasInput?.height || state.template?.canvasHeight || 1123;
    const elements = elementsInput || state.elements || [];
    const template = templateInput || state.template || {};

    // Helper pour calculer les dimensions ajustées avec padding
    const calculateAdjustedDimensions = (
      width: number,
      height: number,
      padding: any,
    ): { width: number; height: number; paddingStyle: string } => {
      let paddingStyle = "";
      let adjustedW = width;
      let adjustedH = height;

      if (padding) {
        const top = (padding?.top || 0) as number;
        const bottom = (padding?.bottom || 0) as number;
        const left = (padding?.left || 0) as number;
        const right = (padding?.right || 0) as number;

        paddingStyle = `padding: ${top}px ${right}px ${bottom}px ${left}px;`;

        // Réduire la hauteur et largeur pour compenser le padding
        adjustedH = Math.max(0, height - top - bottom);
        adjustedW = Math.max(0, width - left - right);
      }

      return {
        width: adjustedW,
        height: adjustedH,
        paddingStyle,
      };
    };

    // Helper pour créer un conteneur flexbox interne si nécessaire pour l'alignement vertical
    const createFlexAlignmentWrapper = (
      content: string,
      verticalAlign: string | undefined,
      textAlign: string | undefined,
    ): { content: string; containerClass: string } => {
      if (!verticalAlign || verticalAlign === "top") {
        return { content, containerClass: "pdfb-element-flex-start" };
      }

      const justifyValue =
        textAlign === "center"
          ? "center"
          : textAlign === "right"
            ? "flex-end"
            : "flex-start";

      const alignValue =
        verticalAlign === "middle" || verticalAlign === "center"
          ? "center"
          : verticalAlign === "bottom"
            ? "flex-end"
            : "flex-start";

      // Wrapper le contenu si c'est du texte simple
      if (!content.includes("<div") && !content.includes("<table")) {
        return {
          content: `<div style="width: 100%; height: 100%; display: flex; align-items: ${alignValue}; justify-content: ${justifyValue};">${content}</div>`,
          containerClass: "",
        };
      }

      return { content, containerClass: "pdfb-element-flex" };
    };

    // Helper functions pour convertir les propriétés en CSS
    const buildSpacing = (value: any): string => {
      if (!value) return "";
      if (typeof value === "number") return `${value}px`;
      if (typeof value === "object") {
        const top = value.top || 0;
        const right = value.right || 0;
        const bottom = value.bottom || 0;
        const left = value.left || 0;
        return `${top}px ${right}px ${bottom}px ${left}px`;
      }
      return "";
    };

    const buildBorder = (border: any): string => {
      if (!border) return "";
      if (!border.width) return "";
      const width = border.width || 1;
      const style = border.style || "solid";
      const color = border.color || "#e5e7eb";
      return `${width}px ${style} ${color}`;
    };

    const buildFlexLayout = (
      layout: string | undefined,
      gap: number = 8,
    ): string => {
      if (!layout) return "";
      if (layout === "horizontal") {
        return `display: flex; flex-direction: row; gap: ${gap}px;`;
      } else if (layout === "vertical") {
        return `display: flex; flex-direction: column; gap: ${gap}px;`;
      }
      return "";
    };

    // Helper pour mapper text-align CSS à flexbox justify-content
    const mapTextAlignToJustifyContent = (
      textAlign: string | undefined,
    ): string => {
      switch (textAlign) {
        case "center":
          return "center";
        case "right":
          return "flex-end";
        case "left":
        default:
          return "flex-start";
      }
    };

    // Helper pour générer les styles globaux applicables au contenu interne
    const buildGlobalStyles = (el: any): string => {
      let globalStyle = "";
      if (el.fontFamily) globalStyle += `font-family: ${el.fontFamily};`;
      if (el.fontWeight && el.fontWeight !== "normal")
        globalStyle += `font-weight: ${el.fontWeight};`;
      if (el.fontStyle && el.fontStyle !== "normal")
        globalStyle += `font-style: ${el.fontStyle};`;
      if (el.textDecoration && el.textDecoration !== "none")
        globalStyle += `text-decoration: ${el.textDecoration};`;
      if (el.textTransform && el.textTransform !== "none")
        globalStyle += `text-transform: ${el.textTransform};`;
      if (el.wordSpacing && el.wordSpacing !== "normal")
        globalStyle += `word-spacing: ${el.wordSpacing};`;
      return globalStyle;
    };

    // Construire le HTML simulant un PDF avec CSS amélioré
    let html = `<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aperçu PDF - ${template.name || "Template"}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body { padding: 20px; background: #f5f5f5; }
    .pdf-wrapper {display: flex; justify-content: center; }
    .pdf-page {
      width: ${canvasWidth}px;
      height: ${canvasHeight}px;
      position: relative;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      background: white;
      font-family: Arial, sans-serif;
    }
    .element { 
      position: absolute; 
      box-sizing: border-box;
      overflow: hidden;
      padding: 0;
      margin: 0;
      font-family: Arial, sans-serif;
      font-size: 12px;
      white-space: pre-wrap;
      display: block;
    }
    .element > * { 
      word-wrap: break-word;
      margin: 0;
      padding: 0;
      font-family: inherit;
    }
    .pdfb-element-flex {
      display: flex !important;
      align-items: center;
    }
    .pdfb-element-flex-start {
      align-items: flex-start !important;
    }
    .pdfb-element-flex-end {
      align-items: flex-end !important;
    }
    table { 
      border-collapse: collapse;
      width: 100%;
      font-size: inherit;
      margin: 0;
      padding: 0;
      table-layout: fixed;
    }
    table td, table th { 
      padding: 4px 6px;
      margin: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      word-wrap: break-word;
    }
  </style>
</head>
<body>
  <div class="pdf-wrapper">
    <div class="pdf-page">`;

    // Ajouter chaque élément avec ses dimensions et position
    if (elements && elements.length > 0) {
      elements.forEach((element: any) => {
        const x = element.x || 0;
        const y = element.y || 0;
        const w = element.width || 100;
        const h = element.height || 50;
        const visible = element.visible !== false;

        if (!visible) return;

        // Calculer les dimensions ajustées si padding
        const {
          width: adjustedW,
          height: adjustedH,
          paddingStyle,
        } = calculateAdjustedDimensions(w, h, element.padding);

        // Construire les styles UNIQUEMENT à partir du JSON
        let styles = `left: ${x}px; top: ${y}px; width: ${adjustedW}px; height: ${adjustedH}px;`;
        let elementClasses = "element";

        // ===== FONT STYLES =====
        if (element.fontSize) styles += ` font-size: ${element.fontSize}px;`;
        if (element.fontFamily)
          styles += ` font-family: ${element.fontFamily};`;
        if (element.fontWeight && element.fontWeight !== "normal")
          styles += ` font-weight: ${element.fontWeight};`;
        if (element.fontStyle && element.fontStyle !== "normal")
          styles += ` font-style: ${element.fontStyle};`;
        if (element.textDecoration && element.textDecoration !== "none")
          styles += ` text-decoration: ${element.textDecoration};`;
        if (element.wordSpacing && element.wordSpacing !== "normal")
          styles += ` word-spacing: ${element.wordSpacing};`;
        if (element.textTransform && element.textTransform !== "none")
          styles += ` text-transform: ${element.textTransform};`;
        if (element.textAlign) styles += ` text-align: ${element.textAlign};`;
        if (element.verticalAlign && element.verticalAlign !== "baseline")
          styles += ` line-height: ${adjustedH}px;`;

        // ===== COLORS =====
        if (element.textColor) styles += ` color: ${element.textColor};`;
        // backgroundColor seulement si showBackground=true OU si backgroundColor n'est pas transparent
        if (
          element.backgroundColor &&
          element.backgroundColor !== "transparent" &&
          element.showBackground !== false
        ) {
          styles += ` background-color: ${element.backgroundColor};`;
        }

        // ===== BORDERS & RADIUS =====
        if (element.borderWidth && element.borderWidth > 0) {
          styles += ` border: ${element.borderWidth}px solid ${element.borderColor || "#e5e7eb"};`;
        }
        if (element.borderRadius && element.borderRadius > 0) {
          styles += ` border-radius: ${element.borderRadius}px;`;
        }

        // ===== PADDING =====
        if (paddingStyle) styles += ` ${paddingStyle}`;

        // ===== SHADOWS =====
        if (element.shadowBlur && element.shadowBlur > 0) {
          const offsetX = element.shadowOffsetX || 0;
          const offsetY = element.shadowOffsetY || 0;
          const blur = element.shadowBlur || 0;
          const color = element.shadowColor || "#000000";
          styles += ` box-shadow: ${offsetX}px ${offsetY}px ${blur}px ${color};`;
        }

        // ===== TRANSFORMS & OPACITY =====
        if (element.rotation && element.rotation !== 0)
          styles += ` transform: rotate(${element.rotation}deg);`;
        if (element.opacity !== undefined && element.opacity < 1)
          styles += ` opacity: ${element.opacity};`;

        let content = "";

        // ===== CONTENU PAR TYPE D'ÉLÉMENT =====
        switch (element.type) {
          case "text":
          case "dynamic_text":
            content = element.text || element.content || "Texte";

            // Appliquer styles globaux (font properties)
            const globalStylesText = buildGlobalStyles(element);
            if (globalStylesText) styles += ` ${globalStylesText}`;

            // Ajouter margin si présent
            if (element.margin) {
              const marginStr = buildSpacing(element.margin);
              if (marginStr) styles += ` margin: ${marginStr};`;
            }
            if (element.border) {
              const borderStr = buildBorder(element.border);
              if (borderStr) styles += ` border: ${borderStr};`;
            }

            // Gérer l'alignement vertical - créer un wrapper flexbox interne si nécessaire
            if (
              element.verticalAlign === "middle" ||
              element.verticalAlign === "center" ||
              element.verticalAlign === "bottom"
            ) {
              const justifyValue =
                element.textAlign === "center"
                  ? "center"
                  : element.textAlign === "right"
                    ? "flex-end"
                    : "flex-start";

              const alignValue =
                element.verticalAlign === "middle" ||
                element.verticalAlign === "center"
                  ? "center"
                  : element.verticalAlign === "bottom"
                    ? "flex-end"
                    : "flex-start";

              content = `<div style="width: 100%; height: 100%; display: flex; flex-direction: row; align-items: ${alignValue}; justify-content: ${justifyValue}; flex-wrap: wrap;">${content}</div>`;
            } else {
              // Top alignment (default) - utiliser simple text-align
              if (element.textAlign)
                content = `<div style="text-align: ${element.textAlign}; width: 100%; height: 100%; overflow: hidden;">${content}</div>`;
            }
            break;

          case "document_type":
            content =
              element.title || element.text || element.content || "FACTURE";
            // Appliquer styles globaux (font properties)
            const globalStylesDoc = buildGlobalStyles(element);
            if (globalStylesDoc) styles += ` ${globalStylesDoc}`;

            // Ajouter margin si présent
            if (element.margin) {
              const marginStr = buildSpacing(element.margin);
              if (marginStr) styles += ` margin: ${marginStr};`;
            }
            if (element.border) {
              const borderStr = buildBorder(element.border);
              if (borderStr) styles += ` border: ${borderStr};`;
            }

            // Gérer l'alignement vertical
            const docJustifyValue =
              element.textAlign === "center"
                ? "center"
                : element.textAlign === "right"
                  ? "flex-end"
                  : "flex-start";

            const docAlignValue =
              element.verticalAlign === "middle" ||
              element.verticalAlign === "center"
                ? "center"
                : element.verticalAlign === "bottom"
                  ? "flex-end"
                  : "flex-start";

            content = `<div style="width: 100%; height: 100%; display: flex; align-items: ${docAlignValue}; justify-content: ${docJustifyValue};">${content}</div>`;
            break;

          case "company_logo":
          case "image":
            if (element.src) {
              const globalStylesImg = buildGlobalStyles(element);
              let imgStyles = `width: 100%; height: 100%; display: block; object-fit: ${element.objectFit || "cover"};`;
              if (element.opacity !== undefined && element.opacity < 1)
                imgStyles += ` opacity: ${element.opacity};`;
              if (element.borderRadius && element.borderRadius > 0)
                imgStyles += ` border-radius: ${element.borderRadius}px;`;
              if (globalStylesImg) imgStyles += ` ${globalStylesImg}`;
              content = `<img src="${element.src}" style="${imgStyles}" />`;
            } else {
              content = "📦";
            }
            // Margin/border pour le conteneur
            if (element.margin) {
              const marginStr = buildSpacing(element.margin);
              if (marginStr) styles += ` margin: ${marginStr};`;
            }
            if (element.border) {
              const borderStr = buildBorder(element.border);
              if (borderStr) styles += ` border: ${borderStr};`;
            }
            break;

          case "line":
          case "separator":
            let lineWidth = element.strokeWidth || 1;
            let lineColor = element.strokeColor || "#000000";
            let lineStyle = element.borderStyle || "solid";

            // Support pour les styles de bordure alternatives
            if (element.style) {
              lineStyle = element.style;
            }

            // Créer une vraie ligne avec div interne de hauteur fixe
            let lineInnerStyle = "";
            if (lineStyle === "dashed") {
              lineInnerStyle = `border-bottom: ${lineWidth}px dashed ${lineColor}; width: 100%; margin: auto 0;`;
            } else if (lineStyle === "dotted") {
              lineInnerStyle = `border-bottom: ${lineWidth}px dotted ${lineColor}; width: 100%; margin: auto 0;`;
            } else {
              // Solid : background-color + height
              lineInnerStyle = `background-color: ${lineColor}; height: ${lineWidth}px; width: 100%; margin: auto 0;`;
            }
            content = `<div style="${lineInnerStyle}"></div>`;

            // Conteneur flexible pour centrer verticalement
            styles += ` display: flex; align-items: center;`;

            // Margin
            if (element.margin) {
              const marginStr = buildSpacing(element.margin);
              if (marginStr) styles += ` margin: ${marginStr};`;
            }
            break;

          case "product_table":
          case "table":
            // Styles du TABLEAU - appliqués à partir du JSON
            let tableStyles = `border-collapse: collapse; width: 100%; table-layout: fixed;`;

            // Police globale - utiliser globalFontSize/Family si présentes, sinon fallback
            if (element.globalFontSize)
              tableStyles += ` font-size: ${element.globalFontSize}px;`;
            else if (element.fontSize)
              tableStyles += ` font-size: ${element.fontSize}px;`;

            if (element.globalFontFamily)
              tableStyles += ` font-family: ${element.globalFontFamily};`;
            else if (element.fontFamily)
              tableStyles += ` font-family: ${element.fontFamily};`;

            if (element.fontWeight && element.fontWeight !== "normal")
              tableStyles += ` font-weight: ${element.fontWeight};`;
            if (element.fontStyle && element.fontStyle !== "normal")
              tableStyles += ` font-style: ${element.fontStyle};`;
            if (element.textDecoration && element.textDecoration !== "none")
              tableStyles += ` text-decoration: ${element.textDecoration};`;
            if (element.wordSpacing && element.wordSpacing !== "normal")
              tableStyles += ` word-spacing: ${element.wordSpacing};`;
            if (
              element.backgroundColor &&
              element.backgroundColor !== "transparent"
            )
              tableStyles += ` background-color: ${element.backgroundColor};`;

            // Styles pour les cellules du corps
            let cellStyles = `padding: 8px; overflow: hidden; text-overflow: ellipsis;`;
            if (element.textAlign)
              cellStyles += ` text-align: ${element.textAlign};`;
            if (element.verticalAlign && element.verticalAlign !== "baseline")
              cellStyles += ` vertical-align: ${element.verticalAlign};`;
            if (element.showBorders && element.borderWidth)
              cellStyles += ` border: ${element.borderWidth}px solid ${element.borderColor || "#e5e7eb"};`;
            if (element.bodyTextColor)
              cellStyles += ` color: ${element.bodyTextColor};`;
            else if (element.textColor)
              cellStyles += ` color: ${element.textColor};`;
            if (
              element.bodyBackgroundColor &&
              element.bodyBackgroundColor !== "transparent"
            )
              cellStyles += ` background-color: ${element.bodyBackgroundColor};`;
            if (element.bodyFontSize)
              cellStyles += ` font-size: ${element.bodyFontSize}px;`;
            if (element.bodyFontFamily)
              cellStyles += ` font-family: ${element.bodyFontFamily};`;
            if (element.bodyFontWeight)
              cellStyles += ` font-weight: ${element.bodyFontWeight};`;
            if (element.bodyFontStyle)
              cellStyles += ` font-style: ${element.bodyFontStyle};`;

            // Styles pour les en-têtes
            let headerStyle = `padding: 8px; overflow: hidden; text-overflow: ellipsis;`;
            if (element.textAlign)
              headerStyle += ` text-align: ${element.textAlign};`;
            if (element.verticalAlign && element.verticalAlign !== "baseline")
              headerStyle += ` vertical-align: ${element.verticalAlign};`;
            if (element.showBorders && element.borderWidth)
              headerStyle += ` border: ${element.borderWidth}px solid ${element.borderColor || "#e5e7eb"};`;
            if (
              element.headerBackgroundColor &&
              element.headerBackgroundColor !== "transparent"
            )
              headerStyle += ` background-color: ${element.headerBackgroundColor};`;
            if (element.headerTextColor)
              headerStyle += ` color: ${element.headerTextColor};`;
            if (element.headerFontSize)
              headerStyle += ` font-size: ${element.headerFontSize}px;`;
            if (element.headerFontFamily)
              headerStyle += ` font-family: ${element.headerFontFamily};`;
            if (element.headerFontWeight)
              headerStyle += ` font-weight: ${element.headerFontWeight};`;
            if (element.headerFontStyle)
              headerStyle += ` font-style: ${element.headerFontStyle};`;

            // Styles pour les lignes de données
            let rowStyle = `padding: 8px; overflow: hidden; text-overflow: ellipsis;`;
            if (element.textAlign)
              rowStyle += ` text-align: ${element.textAlign};`;
            if (element.verticalAlign && element.verticalAlign !== "baseline")
              rowStyle += ` vertical-align: ${element.verticalAlign};`;
            if (element.showBorders && element.borderWidth)
              rowStyle += ` border: ${element.borderWidth}px solid ${element.borderColor || "#e5e7eb"};`;
            if (element.rowTextColor)
              rowStyle += ` color: ${element.rowTextColor};`;
            else if (element.textColor)
              rowStyle += ` color: ${element.textColor};`;
            if (element.rowFontSize)
              rowStyle += ` font-size: ${element.rowFontSize}px;`;
            if (element.rowFontFamily)
              rowStyle += ` font-family: ${element.rowFontFamily};`;
            if (element.rowFontWeight)
              rowStyle += ` font-weight: ${element.rowFontWeight};`;
            if (element.rowFontStyle)
              rowStyle += ` font-style: ${element.rowFontStyle};`;

            // Styles pour la ligne totale
            let totalStyle = `padding: 8px; overflow: hidden; text-overflow: ellipsis;`;
            if (element.textAlign)
              totalStyle += ` text-align: ${element.textAlign};`;
            if (element.verticalAlign && element.verticalAlign !== "baseline")
              totalStyle += ` vertical-align: ${element.verticalAlign};`;
            if (element.showBorders && element.borderWidth)
              totalStyle += ` border: ${element.borderWidth}px solid ${element.borderColor || "#e5e7eb"};`;
            if (element.totalTextColor)
              totalStyle += ` color: ${element.totalTextColor};`;
            else if (element.textColor)
              totalStyle += ` color: ${element.textColor};`;
            if (element.totalFontSize)
              totalStyle += ` font-size: ${element.totalFontSize}px;`;
            if (element.totalFontFamily)
              totalStyle += ` font-family: ${element.totalFontFamily};`;
            if (element.totalFontWeight)
              totalStyle += ` font-weight: ${element.totalFontWeight};`;
            if (element.totalFontStyle)
              totalStyle += ` font-style: ${element.totalFontStyle};`;
            totalStyle += ` font-weight: bold;`;

            const tableId = `table-${element.id}`;
            let tableCSS = "";

            // CSS pour lignes alternées
            if (element.showAlternatingRows && element.alternateRowColor) {
              tableCSS = `<style>#${tableId} tbody tr:nth-child(odd) td { background-color: ${element.alternateRowColor}; }</style>`;
            }

            // Génération du contenu du tableau à partir du JSON
            if (element.content) {
              // Si le contenu HTML existe, l'utiliser
              let wrappedContent = element.content
                .replace(/<th([^>]*)>/g, `<th style="${headerStyle}"$1>`)
                .replace(/<td([^>]*)>/g, `<td style="${cellStyles}"$1>`);
              content =
                tableCSS +
                `<table id="${tableId}" style="${tableStyles}">${wrappedContent}</table>`;
            } else {
              // Sinon, créer un tableau d'exemple basé sur les propriétés
              const cols = [
                element.showSku ? "SKU" : null,
                "Produit",
                element.showDescription ? "Description" : null,
                element.showQuantity ? "Qty" : "Quantité",
                "Prix Unit.",
                "Total",
                element.showShipping ? "Shipping" : null,
                element.showTax ? "Tax" : null,
              ].filter(Boolean);

              // Calcul des montants pour la ligne totale
              const baseAmount = 100.0;
              const quantity = 1;
              const subtotal = baseAmount * quantity;
              const shippingCost = element.shippingCost || 10.0;
              const globalDiscount = element.globalDiscount || 0;
              const taxRate = element.taxRate || 0.2;
              const taxAmount = (subtotal - globalDiscount) * taxRate;
              const total =
                subtotal +
                shippingCost -
                globalDiscount +
                taxAmount +
                (element.orderFees || 0);

              let tableHTML =
                tableCSS + `<table id="${tableId}" style="${tableStyles}">`;

              // En-têtes - affichés uniquement si showHeaders n'est pas false
              if (element.showHeaders !== false) {
                tableHTML += `<thead><tr>`;
                tableHTML += cols
                  .map((col) => `<th style="${headerStyle}">${col}</th>`)
                  .join("");
                tableHTML += `</tr></thead>`;
              }

              tableHTML += `<tbody>`;
              // Ligne de données
              tableHTML += `<tr>`;
              tableHTML += cols
                .map((col) => {
                  let cellValue = "N/A";
                  if (col === "SKU") cellValue = "SKU001";
                  else if (col === "Produit") cellValue = "Produit Exemple";
                  else if (col === "Description")
                    cellValue = "Description du produit";
                  else if (col === "Qty" || col === "Quantité")
                    cellValue = quantity.toString();
                  else if (col === "Prix Unit.")
                    cellValue = baseAmount.toFixed(2) + " €";
                  else if (col === "Total")
                    cellValue = subtotal.toFixed(2) + " €";
                  else if (col === "Shipping")
                    cellValue = shippingCost.toFixed(2) + " €";
                  else if (col === "Tax")
                    cellValue = taxAmount.toFixed(2) + " €";
                  return `<td style="${rowStyle}">${cellValue}</td>`;
                })
                .join("");
              tableHTML += `</tr>`;

              // Ligne de discount global si activée
              if (element.showGlobalDiscount && globalDiscount > 0) {
                tableHTML += `<tr><td colspan="${cols.length - 1}" style="${rowStyle}">Discount:</td><td style="${rowStyle}">-${globalDiscount.toFixed(2)} €</td></tr>`;
              }

              // Ligne totale
              tableHTML += `<tr>`;
              tableHTML += cols
                .map((col, idx) => {
                  let totalValue = "N/A";
                  if (idx === cols.length - 1) {
                    totalValue = total.toFixed(2) + " €";
                  } else if (col === "Produit") {
                    totalValue = "TOTAL";
                  } else if (
                    col === "SKU" ||
                    col === "Description" ||
                    col === "Qty" ||
                    col === "Quantité" ||
                    col === "Prix Unit." ||
                    col === "Shipping" ||
                    col === "Tax"
                  ) {
                    totalValue = "";
                  }
                  return `<td style="${totalStyle}">${totalValue}</td>`;
                })
                .join("");
              tableHTML += `</tr>`;

              tableHTML += `</tbody></table>`;
              content = tableHTML;
            }

            // Ajouter margin/border si présents
            if (element.margin) {
              const marginStr = buildSpacing(element.margin);
              if (marginStr) styles += ` margin: ${marginStr};`;
            }
            if (element.border) {
              const borderStr = buildBorder(element.border);
              if (borderStr) styles += ` border: ${borderStr};`;
            }
            break;

          case "company_info":
            // Helper pour convertir une valeur en string (gère les objets)
            const convertToString = (value: any): string => {
              if (!value) return "";
              if (typeof value === "string") return value;
              if (typeof value === "object") {
                // Si c'est un objet avec une propriété principale (comme téléphone)
                if (value.value) return String(value.value);
                if (value[0]) return String(value[0]);
                if (value.phone) return String(value.phone);
                // Pour d'autres objets, essayer de retourner une représentation utile
                return JSON.stringify(value);
              }
              return String(value);
            };

            // Helper pour valider si une valeur doit être affichée
            const isValidValue = (value: string | any): boolean => {
              const stringValue = convertToString(value);
              return !!(
                stringValue &&
                stringValue.trim() !== "" &&
                stringValue !== "Non indiqué" &&
                stringValue !== "{}"
              );
            };

            // Récupérer les données d'entreprise (d'abord de l'élément, puis du plugin)
            const getCompanyDataForHtml = () => {
              const pluginCompany =
                (window as any).pdfBuilderData?.company || {};
              return {
                name: convertToString(
                  element.companyName || pluginCompany.name || "",
                ),
                address: convertToString(
                  element.companyAddress || pluginCompany.address || "",
                ),
                city: convertToString(
                  element.companyCity || pluginCompany.city || "",
                ),
                phone: convertToString(
                  element.companyPhone || pluginCompany.phone || "",
                ),
                email: convertToString(
                  element.companyEmail || pluginCompany.email || "",
                ),
                website: convertToString(
                  element.companyWebsite || pluginCompany.website || "",
                ),
                siret: convertToString(
                  element.companySiret || pluginCompany.siret || "",
                ),
                tva: convertToString(
                  element.companyTva || pluginCompany.vat || "",
                ),
                rcs: convertToString(
                  element.companyRcs || pluginCompany.rcs || "",
                ),
                capital: convertToString(
                  element.companyCapital || pluginCompany.capital || "",
                ),
              };
            };

            const companyData = getCompanyDataForHtml();

            // Construire le contenu HTML à partir des propriétés du JSON
            let companyContent = element.content || element.text;

            // Si pas de contenu prédéfini, construire à partir des propriétés
            if (!companyContent) {
              const companyParts: string[] = [];
              const globalStylesCompany = buildGlobalStyles(element);

              // Nom de l'entreprise
              if (
                element.showCompanyName !== false &&
                isValidValue(companyData.name)
              ) {
                const headerFontSize =
                  element.headerFontSize || element.fontSize || 14;
                const headerFontWeight = element.headerFontWeight || "bold";
                const headerColor =
                  element.headerTextColor || element.textColor || "#000000";
                companyParts.push(
                  `<div style="font-size: ${headerFontSize}px; font-weight: ${headerFontWeight}; color: ${headerColor}; ${globalStylesCompany}">${companyData.name}</div>`,
                );
              }

              // Adresse
              if (
                element.showAddress !== false &&
                isValidValue(companyData.address)
              ) {
                let addressText = companyData.address;
                if (isValidValue(companyData.city))
                  addressText += `, ${companyData.city}`;
                const bodyFontSize =
                  element.bodyFontSize || element.fontSize || 12;
                const bodyFontWeight =
                  element.bodyFontWeight || element.fontWeight || "normal";
                const bodyColor =
                  element.bodyTextColor || element.textColor || "#666666";
                companyParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">${addressText}</div>`,
                );
              }

              // Téléphone
              if (
                element.showPhone !== false &&
                isValidValue(companyData.phone)
              ) {
                const bodyFontSize =
                  element.bodyFontSize || element.fontSize || 12;
                const bodyFontWeight =
                  element.bodyFontWeight || element.fontWeight || "normal";
                const bodyColor =
                  element.bodyTextColor || element.textColor || "#666666";
                companyParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">Tél: ${companyData.phone}</div>`,
                );
              }

              // Email
              if (
                element.showEmail !== false &&
                isValidValue(companyData.email)
              ) {
                const bodyFontSize =
                  element.bodyFontSize || element.fontSize || 12;
                const bodyFontWeight =
                  element.bodyFontWeight || element.fontWeight || "normal";
                const bodyColor =
                  element.bodyTextColor || element.textColor || "#666666";
                companyParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">Email: ${companyData.email}</div>`,
                );
              }

              // Site web
              if (
                element.showWebsite !== false &&
                isValidValue(companyData.website)
              ) {
                const bodyFontSize =
                  element.bodyFontSize || element.fontSize || 12;
                const bodyFontWeight =
                  element.bodyFontWeight || element.fontWeight || "normal";
                const bodyColor =
                  element.bodyTextColor || element.textColor || "#666666";
                companyParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">${companyData.website}</div>`,
                );
              }

              // SIRET
              if (
                element.showSiret !== false &&
                isValidValue(companyData.siret)
              ) {
                const bodyFontSize =
                  element.bodyFontSize || element.fontSize || 12;
                const bodyFontWeight =
                  element.bodyFontWeight || element.fontWeight || "normal";
                const bodyColor =
                  element.bodyTextColor || element.textColor || "#666666";
                companyParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">SIRET: ${companyData.siret}</div>`,
                );
              }

              // TVA
              if (element.showVat !== false && isValidValue(companyData.tva)) {
                const bodyFontSize =
                  element.bodyFontSize || element.fontSize || 12;
                const bodyFontWeight =
                  element.bodyFontWeight || element.fontWeight || "normal";
                const bodyColor =
                  element.bodyTextColor || element.textColor || "#666666";
                companyParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">TVA: ${companyData.tva}</div>`,
                );
              }

              // RCS
              if (element.showRcs !== false && isValidValue(companyData.rcs)) {
                const bodyFontSize =
                  element.bodyFontSize || element.fontSize || 12;
                const bodyFontWeight =
                  element.bodyFontWeight || element.fontWeight || "normal";
                const bodyColor =
                  element.bodyTextColor || element.textColor || "#666666";
                companyParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">RCS: ${companyData.rcs}</div>`,
                );
              }

              // Capital sociale
              if (
                element.showCapital !== false &&
                isValidValue(companyData.capital)
              ) {
                const bodyFontSize =
                  element.bodyFontSize || element.fontSize || 12;
                const bodyFontWeight =
                  element.bodyFontWeight || element.fontWeight || "normal";
                const bodyColor =
                  element.bodyTextColor || element.textColor || "#666666";
                companyParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">Capital: ${companyData.capital} €</div>`,
                );
              }

              // Assembler le HTML en fonction du layout
              const bodyFontSize =
                element.bodyFontSize || element.fontSize || 12;
              const bodyFontWeight =
                element.bodyFontWeight || element.fontWeight || "normal";
              const bodyColor =
                element.bodyTextColor || element.textColor || "#666666";

              const layout = element.layout || "vertical";

              if (layout === "horizontal") {
                // Mode HORIZONTAL : regrouper les infos par type
                const parts: string[] = [];

                // Ligne 1: Nom de l'entreprise
                if (
                  element.showCompanyName !== false &&
                  isValidValue(companyData.name)
                ) {
                  const headerFontSize =
                    element.headerFontSize || element.fontSize || 14;
                  const headerFontWeight = element.headerFontWeight || "bold";
                  const headerColor =
                    element.headerTextColor || element.textColor || "#000000";
                  parts.push(
                    `<div style="font-size: ${headerFontSize}px; font-weight: ${headerFontWeight}; color: ${headerColor}; width: 100%; ${globalStylesCompany}">${companyData.name}</div>`,
                  );
                }

                // Ligne 2: Adresse + Ville
                let addressLine = "";
                if (
                  element.showAddress !== false &&
                  isValidValue(companyData.address)
                ) {
                  addressLine = companyData.address;
                  if (isValidValue(companyData.city))
                    addressLine += `, ${companyData.city}`;
                }
                if (addressLine) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; width: 100%; ${globalStylesCompany}">${addressLine}</div>`,
                  );
                }

                // Ligne 3: Email + Téléphone
                let contactLine = "";
                if (
                  element.showEmail !== false &&
                  isValidValue(companyData.email)
                ) {
                  contactLine += companyData.email;
                }
                if (
                  element.showPhone !== false &&
                  isValidValue(companyData.phone)
                ) {
                  contactLine += (contactLine ? " | " : "") + companyData.phone;
                }
                if (contactLine) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; width: 100%; ${globalStylesCompany}">${contactLine}</div>`,
                  );
                }

                // Ligne 4: Infos légales (SIRET | RCS | TVA | Capital)
                let legalLine = "";
                if (
                  element.showSiret !== false &&
                  isValidValue(companyData.siret)
                ) {
                  legalLine += companyData.siret;
                }
                if (
                  element.showRcs !== false &&
                  isValidValue(companyData.rcs)
                ) {
                  legalLine += (legalLine ? " | " : "") + companyData.rcs;
                }
                if (
                  element.showVat !== false &&
                  isValidValue(companyData.tva)
                ) {
                  legalLine += (legalLine ? " | " : "") + companyData.tva;
                }
                if (
                  element.showCapital !== false &&
                  isValidValue(companyData.capital)
                ) {
                  legalLine +=
                    (legalLine ? " | " : "") + companyData.capital + " €";
                }
                if (legalLine) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; width: 100%; ${globalStylesCompany}">${legalLine}</div>`,
                  );
                }

                companyContent = `<div style="display: flex; flex-direction: column; gap: 4px;">${parts.join("")}</div>`;
              } else if (layout === "compact") {
                // Mode COMPACT : nom en en-tête, puis infos avec séparateurs bullet points
                const parts: string[] = [];

                if (
                  element.showCompanyName !== false &&
                  isValidValue(companyData.name)
                ) {
                  const headerFontSize =
                    element.headerFontSize || element.fontSize || 14;
                  const headerFontWeight = element.headerFontWeight || "bold";
                  const headerColor =
                    element.headerTextColor || element.textColor || "#000000";
                  parts.push(
                    `<div style="font-size: ${headerFontSize}px; font-weight: ${headerFontWeight}; color: ${headerColor}; width: 100%; ${globalStylesCompany}">${companyData.name}</div>`,
                  );
                }

                let compactText = "";
                if (
                  element.showAddress !== false &&
                  isValidValue(companyData.address)
                ) {
                  compactText += companyData.address;
                  if (isValidValue(companyData.city))
                    compactText += `, ${companyData.city}`;
                }
                if (
                  element.showEmail !== false &&
                  isValidValue(companyData.email)
                ) {
                  compactText += (compactText ? " • " : "") + companyData.email;
                }
                if (
                  element.showPhone !== false &&
                  isValidValue(companyData.phone)
                ) {
                  compactText += (compactText ? " • " : "") + companyData.phone;
                }
                if (
                  element.showSiret !== false &&
                  isValidValue(companyData.siret)
                ) {
                  compactText += (compactText ? " • " : "") + companyData.siret;
                }
                if (
                  element.showRcs !== false &&
                  isValidValue(companyData.rcs)
                ) {
                  compactText += (compactText ? " • " : "") + companyData.rcs;
                }
                if (
                  element.showVat !== false &&
                  isValidValue(companyData.tva)
                ) {
                  compactText += (compactText ? " • " : "") + companyData.tva;
                }
                if (
                  element.showCapital !== false &&
                  isValidValue(companyData.capital)
                ) {
                  compactText +=
                    (compactText ? " • " : "") + companyData.capital + " €";
                }

                if (compactText) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; width: 100%; word-wrap: break-word; ${globalStylesCompany}">${compactText}</div>`,
                  );
                }

                companyContent = `<div style="display: flex; flex-direction: column; gap: 4px;">${parts.join("")}</div>`;
              } else {
                // Mode VERTICAL (défaut) : chaque ligne séparée
                const parts: string[] = [];

                // Nom de l'entreprise
                if (
                  element.showCompanyName !== false &&
                  isValidValue(companyData.name)
                ) {
                  const headerFontSize =
                    element.headerFontSize || element.fontSize || 14;
                  const headerFontWeight = element.headerFontWeight || "bold";
                  const headerColor =
                    element.headerTextColor || element.textColor || "#000000";
                  parts.push(
                    `<div style="font-size: ${headerFontSize}px; font-weight: ${headerFontWeight}; color: ${headerColor}; ${globalStylesCompany}">${companyData.name}</div>`,
                  );
                }

                // Adresse
                if (
                  element.showAddress !== false &&
                  isValidValue(companyData.address)
                ) {
                  let addressText = companyData.address;
                  if (isValidValue(companyData.city))
                    addressText += `, ${companyData.city}`;
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">${addressText}</div>`,
                  );
                }

                // Email
                if (
                  element.showEmail !== false &&
                  isValidValue(companyData.email)
                ) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">Email: ${companyData.email}</div>`,
                  );
                }

                // Téléphone
                if (
                  element.showPhone !== false &&
                  isValidValue(companyData.phone)
                ) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">Tél: ${companyData.phone}</div>`,
                  );
                }

                // Infos légales
                if (
                  element.showSiret !== false &&
                  isValidValue(companyData.siret)
                ) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">SIRET: ${companyData.siret}</div>`,
                  );
                }
                if (
                  element.showRcs !== false &&
                  isValidValue(companyData.rcs)
                ) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">RCS: ${companyData.rcs}</div>`,
                  );
                }
                if (
                  element.showVat !== false &&
                  isValidValue(companyData.tva)
                ) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">TVA: ${companyData.tva}</div>`,
                  );
                }
                if (
                  element.showCapital !== false &&
                  isValidValue(companyData.capital)
                ) {
                  parts.push(
                    `<div style="font-size: ${bodyFontSize}px; font-weight: ${bodyFontWeight}; color: ${bodyColor}; ${globalStylesCompany}">Capital: ${companyData.capital} €</div>`,
                  );
                }

                const fontSize = element.fontSize || 12;
                const gap = Math.round(fontSize * 0.1);
                companyContent = `<div style="display: flex; flex-direction: column; gap: ${gap}px;">${parts.join("")}</div>`;
              }

              content =
                companyContent || "<div>Entreprise non configurée</div>";

              // Background
              if (
                element.showBackground &&
                element.backgroundColor &&
                element.backgroundColor !== "transparent"
              ) {
                styles += ` background-color: ${element.backgroundColor};`;
              }

              // Border (borderWidth et borderColor au lieu de border objet)
              if (
                element.borderWidth &&
                element.borderWidth > 0 &&
                element.borderColor
              ) {
                styles += ` border: ${element.borderWidth}px solid ${element.borderColor};`;
              }

              // Rotation
              if (element.rotation && element.rotation !== 0) {
                styles += ` transform: rotate(${element.rotation}deg);`;
              }

              // Shadow (box-shadow)
              if (element.shadowBlur && element.shadowBlur > 0) {
                const shadowOffsetX = element.shadowOffsetX || 0;
                const shadowOffsetY = element.shadowOffsetY || 0;
                const shadowColor = element.shadowColor || "#000000";
                const shadowBlur = element.shadowBlur || 0;
                styles += ` box-shadow: ${shadowOffsetX}px ${shadowOffsetY}px ${shadowBlur}px ${shadowColor};`;
              }

              // Padding depuis JSON (peut être nombre ou objet {top, right, bottom, left})
              if (element.padding) {
                const paddingStr = buildSpacing(element.padding);
                if (paddingStr) styles += ` padding: ${paddingStr};`;
              } else {
                styles += ` padding: 8px;`;
              }

              // Margin depuis JSON
              if (element.margin) {
                const marginStr = buildSpacing(element.margin);
                if (marginStr) styles += ` margin: ${marginStr};`;
              }

              // Border depuis JSON (objet {width, style, color})
              if (element.border) {
                const borderStr = buildBorder(element.border);
                if (borderStr) styles += ` border: ${borderStr};`;
              }

              // BorderRadius
              if (element.borderRadius && element.borderRadius > 0) {
                styles += ` border-radius: ${element.borderRadius}px;`;
              }

              // Layout property (vertical ou horizontal)
              if (element.layout) {
                const layoutStr = buildFlexLayout(element.layout);
                if (layoutStr) styles += ` ${layoutStr}`;
              }

              // Separator si activé
              if (element.separator) {
                styles += ` border-bottom: 1px solid ${element.borderColor || "#e5e7eb"};`;
              }

              styles += ` overflow: auto;`;
            }
            break;

          case "customer_info":
            // Générer le contenu du client avec styles appliqués depuis JSON
            if (!element.content && !element.text) {
              const headerFontSize = element.headerFontSize || 14;
              const headerFontFamily = element.headerFontFamily || "Arial";
              const headerFontWeight = element.headerFontWeight || "bold";
              const headerFontStyle = element.headerFontStyle || "normal";
              const headerTextColor = element.headerTextColor || "#111827";

              const bodyFontSize = element.bodyFontSize || 12;
              const bodyFontFamily = element.bodyFontFamily || "Arial";
              const bodyFontWeight = element.bodyFontWeight || "normal";
              const bodyFontStyle = element.bodyFontStyle || "normal";
              const bodyTextColor = element.textColor || "#374151";

              const customerParts = [];

              if (element.showHeaders !== false) {
                customerParts.push(
                  `<div style="font-size: ${headerFontSize}px; font-family: ${headerFontFamily}; font-weight: ${headerFontWeight}; font-style: ${headerFontStyle}; color: ${headerTextColor}; margin-bottom: 4px;">Client</div>`,
                );
              }

              if (element.showFullName !== false) {
                customerParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-family: ${bodyFontFamily}; font-weight: ${bodyFontWeight}; font-style: ${bodyFontStyle}; color: ${bodyTextColor}; margin: 0;">Prénom Nom</div>`,
                );
              }

              if (element.showAddress !== false) {
                customerParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-family: ${bodyFontFamily}; font-weight: ${bodyFontWeight}; font-style: ${bodyFontStyle}; color: ${bodyTextColor}; margin: 0;">123 Rue de la Paix, 75000 Paris</div>`,
                );
              }

              if (element.showEmail !== false) {
                customerParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-family: ${bodyFontFamily}; font-weight: ${bodyFontWeight}; font-style: ${bodyFontStyle}; color: ${bodyTextColor}; margin: 0; line-height: 1.1;">client@example.com</div>`,
                );
              }

              if (element.showPhone !== false) {
                customerParts.push(
                  `<div style="font-size: ${bodyFontSize}px; font-family: ${bodyFontFamily}; font-weight: ${bodyFontWeight}; font-style: ${bodyFontStyle}; color: ${bodyTextColor}; margin: 0;">+01 23 45 67 89</div>`,
                );
              }

              content = customerParts.join("");
            } else {
              content = element.content || element.text;
            }

            // Appliquer styles globaux (font properties)
            const globalStylesCustomer = buildGlobalStyles(element);
            if (globalStylesCustomer) styles += ` ${globalStylesCustomer}`;

            // Background depuis JSON
            if (
              element.backgroundColor &&
              element.backgroundColor !== "transparent" &&
              element.showBackground !== false
            ) {
              styles += ` background-color: ${element.backgroundColor};`;
            }

            // Padding depuis JSON (peut être nombre ou objet)
            if (element.padding) {
              const paddingStr = buildSpacing(element.padding);
              if (paddingStr) styles += ` padding: ${paddingStr};`;
            } else {
              styles += ` padding: 8px;`;
            }

            // Margin depuis JSON
            if (element.margin) {
              const marginStr = buildSpacing(element.margin);
              if (marginStr) styles += ` margin: ${marginStr};`;
            }

            // Border depuis JSON (objet {width, style, color})
            if (element.border) {
              const borderStr = buildBorder(element.border);
              if (borderStr) styles += ` border: ${borderStr};`;
            }

            // BorderRadius
            if (element.borderRadius && element.borderRadius > 0) {
              styles += ` border-radius: ${element.borderRadius}px;`;
            }

            // Layout property (vertical ou horizontal) avec gap calculé
            if (element.layout) {
              const fontSize = element.fontSize || 12;
              const gap = Math.round(fontSize * 0.1);
              const layoutStr = buildFlexLayout(element.layout, gap);
              if (layoutStr) styles += ` ${layoutStr}`;
            }

            // showLabels et labelPosition
            if (element.showLabels && element.labelPosition) {
              styles += ` --label-position: ${element.labelPosition};`;
            }

            styles += ` overflow: auto;`;
            break;

          case "mentions":
          case "note":
            // Récupérer les données d'entreprise pour générer les mentions
            const getMentionsDataForHtml = () => {
              const pluginCompany =
                (window as any).pdfBuilderData?.company || {};
              return {
                email: element.email || pluginCompany.email || "",
                phone: element.phone || pluginCompany.phone || "",
                siret: element.siret || pluginCompany.siret || "",
                tva: element.tva || pluginCompany.vat || "",
              };
            };

            // Si contenu personnalisé, l'utiliser; sinon générer depuis les propriétés
            content = element.content || element.text;

            if (!content) {
              const mentionsData = getMentionsDataForHtml();
              const mentionParts: string[] = [];

              // Ajouter email si requis et disponible
              if (
                element.showEmail !== false &&
                mentionsData.email &&
                mentionsData.email.trim()
              ) {
                mentionParts.push(mentionsData.email);
              }

              // Ajouter téléphone si requis et disponible
              if (
                element.showPhone !== false &&
                mentionsData.phone &&
                mentionsData.phone.trim()
              ) {
                mentionParts.push(mentionsData.phone);
              }

              // Ajouter SIRET si requis et disponible
              if (
                element.showSiret !== false &&
                mentionsData.siret &&
                mentionsData.siret.trim()
              ) {
                mentionParts.push(`SIRET: ${mentionsData.siret}`);
              }

              // Ajouter TVA si requis et disponible
              if (
                element.showVat !== false &&
                mentionsData.tva &&
                mentionsData.tva.trim()
              ) {
                mentionParts.push(`TVA: ${mentionsData.tva}`);
              }

              // Assembler avec le séparateur
              const separator = element.separator || " • ";
              content = mentionParts.join(separator);
            }

            // Appliquer styles globaux (font properties)
            const globalStylesMentions = buildGlobalStyles(element);
            if (globalStylesMentions) styles += ` ${globalStylesMentions}`;

            // Padding depuis JSON
            if (element.padding) {
              const paddingStr = buildSpacing(element.padding);
              if (paddingStr) styles += ` padding: ${paddingStr};`;
            }

            // Margin depuis JSON
            if (element.margin) {
              const marginStr = buildSpacing(element.margin);
              if (marginStr) styles += ` margin: ${marginStr};`;
            }

            // Border depuis JSON
            if (element.border) {
              const borderStr = buildBorder(element.border);
              if (borderStr) styles += ` border: ${borderStr};`;
            }

            // BorderRadius
            if (element.borderRadius && element.borderRadius > 0) {
              styles += ` border-radius: ${element.borderRadius}px;`;
            }

            // Background
            if (
              element.showBackground &&
              element.backgroundColor &&
              element.backgroundColor !== "transparent"
            ) {
              styles += ` background-color: ${element.backgroundColor};`;
            }

            // Separator property
            if (element.showSeparator) {
              styles += ` border-bottom: 1px solid ${element.borderColor || "#e5e7eb"};`;
            }

            // Layout property avec gap calculé
            if (element.layout) {
              const fontSize = element.fontSize || 12;
              const gap = Math.round(fontSize * 0.1);
              const layoutStr = buildFlexLayout(element.layout, gap);
              if (layoutStr) styles += ` ${layoutStr}`;
            }
            break;

          case "woocommerce_invoice_number":
            const invoiceNum = element.text || element.content || "001";
            const invoiceFormat = element.format || "FAC-{order_number}";
            let invoiceContent = invoiceFormat.replace(
              "{order_number}",
              invoiceNum,
            );
            const globalStylesInvoice = buildGlobalStyles(element);

            // Gérer l'alignement vertical comme dans Canvas
            if (
              element.verticalAlign === "middle" ||
              element.verticalAlign === "center"
            ) {
              styles += ` display: flex; align-items: center; justify-content: ${element.textAlign === "center" ? "center" : element.textAlign === "right" ? "flex-end" : "flex-start"};`;
            } else if (element.verticalAlign === "bottom") {
              styles += ` display: flex; align-items: flex-end; justify-content: ${element.textAlign === "center" ? "center" : element.textAlign === "right" ? "flex-end" : "flex-start"};`;
            } else {
              // top alignment (default)
              styles += ` display: flex; align-items: flex-start; justify-content: ${element.textAlign === "center" ? "center" : element.textAlign === "right" ? "flex-end" : "flex-start"};`;
            }

            // Si label à afficher
            if (element.showLabel && element.labelText) {
              const labelText = element.labelText || "Invoice:";
              const headerFontSize =
                element.headerFontSize || element.fontSize || 12;
              const numberFontSize =
                element.numberFontSize || element.fontSize || 14;
              const labelColor =
                element.headerTextColor || element.textColor || "#000000";
              const numberColor = element.textColor || "#000000";

              invoiceContent = `
                <div style="display: flex; flex-direction: column; gap: 4px;">
                  <div style="font-size: ${headerFontSize}px; color: ${labelColor}; font-weight: ${element.headerFontWeight || "normal"}; ${globalStylesInvoice}">${labelText}</div>
                  <div style="font-size: ${numberFontSize}px; color: ${numberColor}; font-weight: ${element.fontWeight || "normal"}; ${globalStylesInvoice}">${invoiceContent}</div>
                </div>
              `;
            }
            content = invoiceContent;

            // contentAlign property
            if (element.contentAlign) {
              styles += ` text-align: ${element.contentAlign};`;
            }
            // Padding/margin/border
            if (element.padding) {
              const paddingStr = buildSpacing(element.padding);
              if (paddingStr) styles += ` padding: ${paddingStr};`;
            }
            if (element.margin) {
              const marginStr = buildSpacing(element.margin);
              if (marginStr) styles += ` margin: ${marginStr};`;
            }
            if (element.border) {
              const borderStr = buildBorder(element.border);
              if (borderStr) styles += ` border: ${borderStr};`;
            }
            break;

          default:
            content =
              element.text ||
              element.content ||
              element.label ||
              `[${element.type}]`;
        }

        // Wrapper l'élément avec tous les styles
        let containerStyles = styles;

        // Pour les éléments complexes, utiliser overflow: auto plutôt que visible
        if (
          element.type === "customer_info" ||
          element.type === "product_table" ||
          element.type === "company_info"
        ) {
          // Autoriser le scroll si le contenu est trop volumineux
          containerStyles = containerStyles.replace(
            "overflow: hidden",
            "overflow: auto",
          );
        }

        html += `<div class="element" style="${containerStyles}">${content}</div>`;
      });
    } else {
      html += `<div style="padding: 40px; text-align: center; color: #999;">
        <p style="font-size: 14px; margin-bottom: 10px;">🎨 Canvas vide</p>
        <p style="font-size: 12px;">Aucun élément n'a été ajouté au template.</p>
      </div>`;
    }

    html += `
    </div>
  </div>
</body>
</html>`;

    return html;
  };

  // Fonction pour générer et afficher l'aperçu HTML (vrai HTML, pas PNG)
  const handleShowHtmlPreview = async () => {
    setIsGeneratingHtml(true);
    try {
      // Utiliser generatePDFSimulationHTML pour générer du vrai HTML positionné
      const html = generatePDFSimulationHTML(
        state.elements,
        state.canvas,
        state.template,
      );
      setGeneratedHtml(html);
      setJsonModalMode("html");
    } catch (error) {
      console.error("Erreur lors de la génération HTML:", error);
      alert(
        `❌ Erreur: ${error instanceof Error ? error.message : "Erreur inconnue"}`,
      );
    } finally {
      setIsGeneratingHtml(false);
    }
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
          onClick={() => {
            if (!isPremium) {
              // Appel du modal PHP partagé injecté via admin_footer
              (window as any).showUpgradeModal?.("template");
              return;
            }
            // Utilisateur premium : naviguer vers éditeur vierge
            const ajaxUrl: string =
              (window as any).pdfBuilderData?.ajaxUrl || "";
            const adminBase = ajaxUrl
              ? ajaxUrl.replace("admin-ajax.php", "admin.php")
              : window.location.href.split("?")[0];
            const newUrl = adminBase + "?page=pdf-builder-pro";
            if (
              window.confirm(
                "Créer un nouveau template ? Les modifications non sauvegardées seront perdues.",
              )
            ) {
              window.location.href = newUrl;
            }
          }}
          onMouseEnter={() => setHoveredButton("new")}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving && isPremium ? 0.6 : !isPremium ? 0.7 : 1,
            // Non-premium : toujours cliquable pour afficher le modal d'upgrade
            pointerEvents: isSaving && isPremium ? "none" : "auto",
            ...(!isPremium && {
              border: "1px solid #d1d5db",
              color: "#9ca3af",
              cursor: "not-allowed",
            }),
          }}
          title={
            !isPremium
              ? "Fonctionnalité premium - Activez votre licence"
              : "Créer un nouveau template"
          }
        >
          <span>➕</span>
          <span>Nouveau</span>
          {!isPremium && (
            <span
              style={{ fontSize: "10px", color: "#d97706", marginLeft: "4px" }}
            >
              🔒
            </span>
          )}
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
                      "_blank",
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
                      "_blank",
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
                      "_blank",
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
                      "_blank",
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
          onClick={handlePreview}
          onMouseEnter={() => setHoveredButton("preview")}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? "none" : "auto",
          }}
          title="Générer un aperçu PDF/PNG/JPG avec une commande réelle"
        >
          <span>👁️</span>
          <span>Aperçu</span>
        </button>

        {developerModeActive && (
          <>
            <div
              style={{
                width: "1px",
                height: "24px",
                backgroundColor: "#e0e0e0",
              }}
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
          </>
        )}

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
                  (error instanceof Error ? error.message : "Erreur inconnue"),
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
        <div
          className="pdfb-canvas-modal-overlay"
          style={{
            position: "fixed",
            top: 0,
            left: 0,
            width: "100%",
            height: "100%",
            background: "rgba(0, 0, 0, 0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 10000,
          }}
        >
          <div
            className="pdfb-canvas-modal-container"
            style={{
              background: "#fff",
              borderRadius: "8px",
              boxShadow: "0 10px 40px rgba(0, 0, 0, 0.2)",
              width: "500px",
              maxWidth: "90vw",
              maxHeight: "85vh",
              overflow: "hidden",
              display: "flex",
              flexDirection: "column",
            }}
          >
            <div
              className="pdfb-canvas-modal-header"
              style={{
                background: "#f5f5f5",
                borderBottom: "1px solid #ddd",
                padding: "20px 24px",
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
              }}
            >
              <h3
                style={{
                  margin: 0,
                  fontSize: "16px",
                  fontWeight: 600,
                  color: "#333",
                }}
              >
                <span style={{ fontSize: "24px" }}>📄</span> Paramètres du
                template
              </h3>
              <button
                type="button"
                className="pdfb-canvas-modal-close"
                onClick={() => setShowSettingsModal(false)}
                title="Fermer"
                style={{
                  background: "none",
                  border: "none",
                  cursor: "pointer",
                  fontSize: "24px",
                  color: "#666",
                  padding: 0,
                  width: "32px",
                  height: "32px",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                }}
              >
                &times;
              </button>
            </div>
            <div
              className="pdfb-canvas-modal-body"
              style={{
                padding: "24px",
                overflowY: "auto",
                flex: 1,
              }}
            >
              <div
                className="pdfb-setting-group"
                style={{ marginBottom: "20px" }}
              >
                <label
                  className="pdfb-setting-label"
                  style={{
                    display: "block",
                    marginBottom: "8px",
                    fontSize: "14px",
                    fontWeight: 500,
                    color: "#333",
                  }}
                >
                  Nom du template
                </label>
                <input
                  type="text"
                  value={editedTemplateName}
                  onChange={(e) => setEditedTemplateName(e.target.value)}
                  className="pdfb-setting-input"
                  placeholder="Entrez le nom du template"
                  autoComplete="off"
                  name="template_name_unique"
                  style={{
                    width: "100%",
                    padding: "8px 12px",
                    border: "1px solid #ddd",
                    borderRadius: "4px",
                    fontSize: "14px",
                  }}
                />
              </div>

              <div
                className="pdfb-setting-group"
                style={{ marginBottom: "20px" }}
              >
                <label
                  className="pdfb-setting-label"
                  style={{
                    display: "block",
                    marginBottom: "8px",
                    fontSize: "14px",
                    fontWeight: 500,
                    color: "#333",
                  }}
                >
                  Description
                </label>
                <textarea
                  value={editedTemplateDescription}
                  onChange={(e) => setEditedTemplateDescription(e.target.value)}
                  className="pdfb-setting-textarea"
                  placeholder="Description du template..."
                  rows={3}
                  style={{
                    width: "100%",
                    padding: "8px 12px",
                    border: "1px solid #ddd",
                    borderRadius: "4px",
                    fontSize: "14px",
                    resize: "vertical",
                  }}
                />
              </div>

              <div
                className="pdfb-setting-group"
                style={{ marginBottom: "20px" }}
              >
                <label
                  className="pdfb-setting-label"
                  style={{
                    display: "block",
                    marginBottom: "8px",
                    fontSize: "14px",
                    fontWeight: 500,
                    color: "#333",
                  }}
                >
                  Dimensions du canvas
                </label>
                <div
                  className="pdfb-setting-input-group"
                  style={{ display: "flex", alignItems: "center", gap: "8px" }}
                >
                  <input
                    type="number"
                    value={editedCanvasWidth}
                    disabled={true}
                    className="pdfb-setting-input pdfb-setting-input-disabled"
                    placeholder="Largeur"
                    style={{
                      flex: 1,
                      padding: "8px 12px",
                      border: "1px solid #ddd",
                      borderRadius: "4px",
                      fontSize: "14px",
                      background: "#f5f5f5",
                      color: "#999",
                    }}
                  />
                  <span
                    className="pdfb-setting-input-separator"
                    style={{ color: "#999", fontSize: "16px" }}
                  >
                    ×
                  </span>
                  <input
                    type="number"
                    value={editedCanvasHeight}
                    disabled={true}
                    className="pdfb-setting-input pdfb-setting-input-disabled"
                    placeholder="Hauteur"
                    style={{
                      flex: 1,
                      padding: "8px 12px",
                      border: "1px solid #ddd",
                      borderRadius: "4px",
                      fontSize: "14px",
                      background: "#f5f5f5",
                      color: "#999",
                    }}
                  />
                  <span
                    className="pdfb-setting-unit"
                    style={{ color: "#666", fontSize: "14px" }}
                  >
                    px
                  </span>
                </div>
              </div>

              <div
                className="pdfb-setting-group"
                style={{ marginBottom: "20px" }}
              >
                <label
                  className="pdfb-setting-label"
                  style={{
                    display: "block",
                    marginBottom: "8px",
                    fontSize: "14px",
                    fontWeight: 500,
                    color: "#333",
                  }}
                >
                  Statut
                </label>
                <div
                  className="pdfb-setting-status-tags"
                  style={{ display: "flex", gap: "8px", flexWrap: "wrap" }}
                >
                  {isNewTemplate && (
                    <span
                      className="pdfb-status-tag pdfb-status-new"
                      style={{
                        padding: "4px 12px",
                        borderRadius: "12px",
                        fontSize: "12px",
                        fontWeight: 500,
                        background: "#e7f3ff",
                        color: "#0073aa",
                        border: "1px solid #0073aa",
                      }}
                    >
                      Nouveau template
                    </span>
                  )}
                  {deferredIsModified && (
                    <span
                      className="pdfb-status-tag pdfb-status-modified"
                      style={{
                        padding: "4px 12px",
                        borderRadius: "12px",
                        fontSize: "12px",
                        fontWeight: 500,
                        background: "#fff3cd",
                        color: "#856404",
                        border: "1px solid #ffc107",
                      }}
                    >
                      Modifié
                    </span>
                  )}
                  {isEditingExistingTemplate && (
                    <span
                      className="pdfb-status-tag pdfb-status-editing"
                      style={{
                        padding: "4px 12px",
                        borderRadius: "12px",
                        fontSize: "12px",
                        fontWeight: 500,
                        background: "#d4edda",
                        color: "#155724",
                        border: "1px solid #28a745",
                      }}
                    >
                      Édition existante
                    </span>
                  )}
                </div>
              </div>

              <div
                className="pdfb-setting-group"
                style={{ marginBottom: "20px" }}
              >
                <label
                  className="pdfb-setting-label"
                  style={{
                    display: "block",
                    marginBottom: "8px",
                    fontSize: "14px",
                    fontWeight: 500,
                    color: "#333",
                  }}
                >
                  Informations système
                </label>
                <div
                  className="pdfb-setting-info"
                  style={{
                    fontSize: "12px",
                    color: "#666",
                    lineHeight: "1.6",
                    background: "#f9f9f9",
                    padding: "12px",
                    borderRadius: "4px",
                    border: "1px solid #e0e0e0",
                  }}
                >
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
                      Performance: {performanceMetrics.fps} FPS,{" "}
                      {performanceMetrics.memoryUsage}MB RAM
                    </div>
                  )}
                  {canvasSettings.debugMode && (
                    <div>
                      Debug: FPS Target {canvasSettings.fpsTarget}, Memory Limit{" "}
                      {canvasSettings.memoryLimitJs}MB
                    </div>
                  )}
                </div>
              </div>
            </div>
            <div
              className="pdfb-canvas-modal-footer"
              style={{
                borderTop: "1px solid #ddd",
                padding: "16px 24px",
                display: "flex",
                justifyContent: "flex-end",
                gap: "12px",
                background: "#f9f9f9",
              }}
            >
              <button
                onClick={() => setShowSettingsModal(false)}
                className="pdfb-canvas-modal-btn pdfb-canvas-modal-btn-secondary"
                style={{
                  padding: "10px 20px",
                  border: "1px solid #ccc",
                  borderRadius: "4px",
                  background: "#fff",
                  color: "#333",
                  fontSize: "14px",
                  fontWeight: 500,
                  cursor: "pointer",
                  transition: "all 0.2s",
                }}
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
                className="pdfb-canvas-modal-btn pdfb-canvas-modal-btn-primary"
                style={{
                  padding: "10px 20px",
                  border: "none",
                  borderRadius: "4px",
                  background: "#0073aa",
                  color: "#fff",
                  fontSize: "14px",
                  fontWeight: 500,
                  cursor: "pointer",
                  transition: "all 0.2s",
                }}
              >
                Sauvegarder
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modale d'aperçu avec sélection de format */}
      {showPreviewModal && (
        <div className="pdfb-canvas-modal-overlay" style={{ display: "flex" }}>
          <div
            className="pdfb-canvas-modal-container"
            style={{ maxWidth: "500px" }}
          >
            <div className="pdfb-canvas-modal-header">
              <h3>
                <span style={{ fontSize: "24px" }}>👁️</span> Générer un aperçu
                {activeEngine && (
                  <span
                    style={{
                      marginLeft: "12px",
                      fontSize: "14px",
                      color: "#059669",
                      backgroundColor: "#d1fae5",
                      padding: "4px 10px",
                      borderRadius: "4px",
                      fontWeight: "500",
                    }}
                  >
                    {activeEngine.icon} {activeEngine.name}
                  </span>
                )}
              </h3>
              <button
                type="button"
                className="pdfb-canvas-modal-close"
                onClick={() => setShowPreviewModal(false)}
                title="Fermer"
              >
                &times;
              </button>
            </div>
            <div className="pdfb-canvas-modal-body">
              <div className="pdfb-setting-group">
                <label className="pdfb-setting-label">
                  Numéro de commande WooCommerce{" "}
                  <span style={{ fontSize: "12px", color: "#e11d48" }}>*</span>
                </label>
                {isLoadingOrders ? (
                  <div
                    style={{
                      padding: "8px 12px",
                      border: "1px solid #d1d5db",
                      borderRadius: "4px",
                      backgroundColor: "#f9fafb",
                      color: "#6b7280",
                      fontSize: "14px",
                    }}
                  >
                    ⏳ Chargement des commandes...
                  </div>
                ) : (
                  <select
                    value={previewOrderId}
                    onChange={(e) => setPreviewOrderId(e.target.value)}
                    className="pdfb-setting-input"
                    disabled={isGeneratingPreview}
                    style={{
                      cursor: isGeneratingPreview ? "not-allowed" : "pointer",
                    }}
                  >
                    <option value="">
                      {availableOrders.length === 0
                        ? "Aucune commande trouvée"
                        : "Sélectionnez une commande"}
                    </option>
                    {availableOrders.map((order) => (
                      <option key={order.id} value={order.id}>
                        #{order.number} - {order.customer} ({order.date}) -{" "}
                        {order.total}
                      </option>
                    ))}
                  </select>
                )}
                <div className="pdfb-setting-hint">
                  Sélectionnez la commande WooCommerce pour générer le document
                  avec les vraies données (client, produits, totaux, etc.)
                </div>
              </div>

              <div className="pdfb-setting-group" style={{ marginTop: "24px" }}>
                <label className="pdfb-setting-label">Format de sortie</label>
                <div
                  style={{
                    display: "grid",
                    gridTemplateColumns: "1fr 1fr 1fr 1fr",
                    gap: "12px",
                  }}
                >
                  <button
                    onClick={generatePDF}
                    disabled={isGeneratingPreview || !previewOrderId.trim()}
                    style={{
                      padding: "12px 16px",
                      border: "2px solid #2271b1",
                      borderRadius: "6px",
                      backgroundColor: "#2271b1",
                      color: "white",
                      fontSize: "14px",
                      fontWeight: "600",
                      cursor:
                        isGeneratingPreview || !previewOrderId.trim()
                          ? "not-allowed"
                          : "pointer",
                      opacity:
                        isGeneratingPreview || !previewOrderId.trim() ? 0.5 : 1,
                      transition: "all 0.2s",
                      display: "flex",
                      flexDirection: "column",
                      alignItems: "center",
                      gap: "4px",
                    }}
                  >
                    <span style={{ fontSize: "24px" }}>📄</span>
                    <span>PDF</span>
                  </button>

                  <button
                    onClick={() => generateImage("png")}
                    disabled={
                      isGeneratingPreview ||
                      !isPremium ||
                      !previewOrderId.trim()
                    }
                    title={
                      !isPremium
                        ? "Fonctionnalité premium - Activez votre licence"
                        : !previewOrderId.trim()
                          ? "Veuillez sélectionner une commande"
                          : "Générer en PNG avec les données de la commande"
                    }
                    style={{
                      padding: "12px 16px",
                      border: isPremium
                        ? "2px solid #059669"
                        : "2px solid #d1d5db",
                      borderRadius: "6px",
                      backgroundColor: isPremium ? "#10b981" : "#f9fafb",
                      color: isPremium ? "white" : "#6b7280",
                      fontSize: "14px",
                      fontWeight: "600",
                      cursor:
                        isGeneratingPreview ||
                        !isPremium ||
                        !previewOrderId.trim()
                          ? "not-allowed"
                          : "pointer",
                      opacity:
                        isGeneratingPreview ||
                        !isPremium ||
                        !previewOrderId.trim()
                          ? 0.5
                          : 1,
                      transition: "all 0.2s",
                      display: "flex",
                      flexDirection: "column",
                      alignItems: "center",
                      gap: "4px",
                    }}
                  >
                    <span style={{ fontSize: "24px" }}>📸</span>
                    <span>PNG</span>
                    {!isPremium && (
                      <span style={{ fontSize: "10px", color: "#d97706" }}>
                        Premium
                      </span>
                    )}
                  </button>

                  <button
                    onClick={() => generateImage("jpg")}
                    disabled={
                      isGeneratingPreview ||
                      !isPremium ||
                      !previewOrderId.trim()
                    }
                    title={
                      !isPremium
                        ? "Fonctionnalité premium - Activez votre licence"
                        : !previewOrderId.trim()
                          ? "Veuillez sélectionner une commande"
                          : "Générer en JPG avec les données de la commande"
                    }
                    style={{
                      padding: "12px 16px",
                      border: isPremium
                        ? "2px solid #059669"
                        : "2px solid #d1d5db",
                      borderRadius: "6px",
                      backgroundColor: isPremium ? "#10b981" : "#f9fafb",
                      color: isPremium ? "white" : "#6b7280",
                      fontSize: "14px",
                      fontWeight: "600",
                      cursor:
                        isGeneratingPreview ||
                        !isPremium ||
                        !previewOrderId.trim()
                          ? "not-allowed"
                          : "pointer",
                      opacity:
                        isGeneratingPreview ||
                        !isPremium ||
                        !previewOrderId.trim()
                          ? 0.5
                          : 1,
                      transition: "all 0.2s",
                      display: "flex",
                      flexDirection: "column",
                      alignItems: "center",
                      gap: "4px",
                    }}
                  >
                    <span style={{ fontSize: "24px" }}>📸</span>
                    <span>JPG</span>
                    {!isPremium && (
                      <span style={{ fontSize: "10px", color: "#d97706" }}>
                        Premium
                      </span>
                    )}
                  </button>

                  {developerModeActive && (
                    <button
                      onClick={openDebugHTML}
                      disabled={isGeneratingPreview || !previewOrderId.trim()}
                      title="Ouvrir le HTML brut pour inspection (debug)"
                      style={{
                        padding: "12px 16px",
                        border: "2px solid #6b7280",
                        borderRadius: "6px",
                        backgroundColor: "#f3f4f6",
                        color: "#374151",
                        fontSize: "14px",
                        fontWeight: "600",
                        cursor:
                          isGeneratingPreview || !previewOrderId.trim()
                            ? "not-allowed"
                            : "pointer",
                        opacity:
                          isGeneratingPreview || !previewOrderId.trim()
                            ? 0.5
                            : 1,
                        transition: "all 0.2s",
                        display: "flex",
                        flexDirection: "column",
                        alignItems: "center",
                        gap: "4px",
                      }}
                    >
                      <span style={{ fontSize: "24px" }}>🔍</span>
                      <span>HTML</span>
                    </button>
                  )}
                </div>
              </div>

              {isGeneratingPreview && (
                <div
                  style={{
                    marginTop: "16px",
                    padding: "12px",
                    backgroundColor: "#dbeafe",
                    borderRadius: "6px",
                    textAlign: "center",
                    color: "#1e40af",
                    fontSize: "14px",
                  }}
                >
                  ⏳ Génération en cours...
                </div>
              )}
            </div>
            <div className="pdfb-canvas-modal-footer">
              <button
                onClick={() => setShowPreviewModal(false)}
                className="pdfb-canvas-modal-btn pdfb-canvas-modal-btn-secondary"
                disabled={isGeneratingPreview}
              >
                Annuler
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
              maxWidth: "65vw",
              width: "100%",
              height: "87vh",
              display: "flex",
              flexDirection: "column",
              boxShadow: "0 10px 40px rgba(0, 0, 0, 0.3)",
              position: "fixed",
              top: "50%",
              left: "50%",
              transform: `translate(calc(-50% + ${modalPosition.x}px), calc(-50% + ${modalPosition.y}px))`,
              transition: isDraggingModal ? "none" : "transform 0.1s ease-out",
            }}
          >
            {/* Header avec Toggle JSON/HTML */}
            <div
              onMouseDown={(e) => {
                // Ne pas dragguer si on clique sur un bouton
                if ((e.target as HTMLElement).closest("button")) return;
                setIsDraggingModal(true);
                setDragStart({ x: e.clientX, y: e.clientY });
              }}
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                marginBottom: "16px",
                borderBottom: "1px solid #e0e0e0",
                paddingBottom: "12px",
                cursor: isDraggingModal ? "grabbing" : "grab",
                userSelect: "none",
              }}
            >
              <div
                style={{ display: "flex", alignItems: "center", gap: "12px" }}
              >
                <h3
                  style={{
                    margin: 0,
                    fontSize: "18px",
                    fontWeight: "600",
                    color: "#1a1a1a",
                  }}
                >
                  {jsonModalMode === "json" ? "📋" : "🎨"}{" "}
                  {jsonModalMode === "json" ? "JSON" : "Aperçu HTML"}
                </h3>
                {/* Toggle Buttons */}
                <div
                  style={{
                    display: "flex",
                    gap: "6px",
                    borderRadius: "4px",
                    border: "1px solid #ddd",
                    padding: "3px",
                  }}
                >
                  <button
                    onClick={() => setJsonModalMode("json")}
                    style={{
                      padding: "6px 12px",
                      border: "none",
                      borderRadius: "3px",
                      backgroundColor:
                        jsonModalMode === "json" ? "#007cba" : "#f0f0f0",
                      color: jsonModalMode === "json" ? "#fff" : "#333",
                      cursor: "pointer",
                      fontSize: "12px",
                      fontWeight: jsonModalMode === "json" ? "bold" : "normal",
                    }}
                    title="Afficher le JSON"
                  >
                    JSON
                  </button>
                  <button
                    onClick={handleShowHtmlPreview}
                    disabled={isGeneratingHtml}
                    style={{
                      padding: "6px 12px",
                      border: "none",
                      borderRadius: "3px",
                      backgroundColor:
                        jsonModalMode === "html" ? "#10a37f" : "#f0f0f0",
                      color: jsonModalMode === "html" ? "#fff" : "#333",
                      cursor: isGeneratingHtml ? "not-allowed" : "pointer",
                      fontSize: "12px",
                      fontWeight: jsonModalMode === "html" ? "bold" : "normal",
                      opacity: isGeneratingHtml ? 0.6 : 1,
                    }}
                    title="Afficher l'aperçu HTML"
                  >
                    {isGeneratingHtml ? "⏳" : "🎨"} HTML
                  </button>
                </div>
              </div>
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

            {/* Content - JSON or HTML */}
            <div
              style={{
                flex: 1,
                overflow: "auto",
                display: "flex",
                flexDirection: "column",
              }}
            >
              {jsonModalMode === "json" ? (
                <pre
                  style={{
                    margin: 0,
                    padding: "16px",
                    fontFamily: "monospace",
                    whiteSpace: "pre-wrap",
                    wordBreak: "break-word",
                    fontSize: "12px",
                  }}
                >
                  {JSON.stringify(
                    {
                      ...state.template,
                      elements: state.elements,
                    },
                    null,
                    2,
                  )}
                </pre>
              ) : (
                <iframe
                  srcDoc={generatedHtml}
                  style={{
                    width: "100%",
                    height: "100%",
                    flex: 1,
                    border: "none",
                    borderRadius: "4px",
                    backgroundColor: "#f5f5f5",
                  }}
                  title="Aperçu PDF"
                />
              )}
            </div>

            {/* Footer with Buttons */}
            <div
              style={{
                display: "flex",
                gap: "12px",
                justifyContent: "flex-start",
                alignItems: "center",
                flexWrap: "wrap",
              }}
            >
              {/* Bouton Copier JSON */}
              <button
                onClick={() => {
                  navigator.clipboard.writeText(
                    JSON.stringify(
                      {
                        ...state.template,
                        elements: state.elements,
                      },
                      null,
                      2,
                    ),
                  );
                  setCopySuccess(true);
                  setTimeout(() => setCopySuccess(false), 2000);
                }}
                style={{
                  padding: "10px 16px",
                  backgroundColor: copySuccess ? "#28a745" : "#0073aa",
                  color: "#ffffff",
                  border: "none",
                  borderRadius: "6px",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: "500",
                  display: "flex",
                  alignItems: "center",
                  gap: "6px",
                  transition: "background-color 0.2s ease",
                }}
              >
                {copySuccess ? (
                  <>
                    <span>✅</span>
                    <span>Copié!</span>
                  </>
                ) : (
                  <>
                    <span>📋</span>
                    <span>Copier JSON</span>
                  </>
                )}
              </button>

              {/* Bouton Télécharger JSON */}
              <button
                onClick={() => {
                  const jsonString = JSON.stringify(
                    {
                      ...state.template,
                      elements: state.elements,
                    },
                    null,
                    2,
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
                  padding: "10px 16px",
                  backgroundColor: "#666",
                  color: "#ffffff",
                  border: "none",
                  borderRadius: "6px",
                  cursor: "pointer",
                  fontSize: "14px",
                  fontWeight: "500",
                  display: "flex",
                  alignItems: "center",
                  gap: "6px",
                }}
              >
                <span>📥</span>
                <span>Télécharger</span>
              </button>

              {/* Bouton Fermer */}
              <button
                onClick={() => setShowJsonModal(false)}
                style={{
                  marginLeft: "auto",
                  padding: "10px 16px",
                  backgroundColor: "#f0f0f0",
                  color: "#333",
                  border: "1px solid #ddd",
                  borderRadius: "6px",
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
    </div>
  );
});
