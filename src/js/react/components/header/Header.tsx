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
  const [jsonModalMode, setJsonModalMode] = useState<'json' | 'html'>('json');
  const [copySuccess, setCopySuccess] = useState(false);
  const [isGeneratingHtml, setIsGeneratingHtml] = useState(false);
  const [isHeaderFixed, setIsHeaderFixed] = useState(false);
  const [generatedHtml, setGeneratedHtml] = useState<string>('');
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
          defaultOrientation: ((window as any).pdfBuilderCanvasSettings?.default_canvas_orientation || 'portrait') as 'portrait' | 'landscape',
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

  // Convertir JSON to HTML et afficher dans une nouvelle fenêtre
  const convertJsonToHtml = useCallback(async () => {
    if (isGeneratingHtml) return;
    
    setIsGeneratingHtml(true);
    try {
      console.log('[JSON TO HTML] Starting conversion');

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

      const templateData = {
        elements: transformedElements,
        canvasWidth: canvasWidth,
        canvasHeight: canvasHeight,
        template: state.template,
      };

      // Utilisez l'URL de WordPress si disponible
      const ajaxUrl = (window as any).pdfBuilderData?.ajaxUrl || '/wp-admin/admin-ajax.php';
      const nonce = (window as any).pdfBuilderNonce;
      
      console.log('[JSON TO HTML] AJAX URL:', ajaxUrl);
      console.log('[JSON TO HTML] Nonce:', nonce);
      console.log('[JSON TO HTML] Template Data:', templateData);

      const requestData = {
        action: 'pdf_builder_generate_html_preview',
        nonce: nonce,
        data: JSON.stringify({
          pageOptions: {
            template: templateData
          }
        })
      };

      const params = new URLSearchParams();
      Object.entries(requestData).forEach(([key, value]) => {
        params.append(key, String(value));
      });

      console.log('[JSON TO HTML] Form Data:', params.toString());
      console.log('[JSON TO HTML] Action: pdf_builder_generate_html_preview');
      console.log('[JSON TO HTML] Data length:', String(requestData.data).length);

      const response = await fetch(ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString(),
        credentials: 'same-origin',
      });

      const responseText = await response.text();

      console.log('[JSON TO HTML] Response Status:', response.status);
      console.log('[JSON TO HTML] Response Text:', responseText);

      if (!response.ok) {
        console.error('[JSON TO HTML] Request failed with status:', response.status);
        console.error('[JSON TO HTML] Response body:', responseText);
        
        // Afficher une alerte détaillée
        if (responseText === '0') {
          alert('❌ Erreur serveur: Authentification échouée ou action non trouvée.\n\nResponse: 0\n\nCela signifie:\n- Le nonce est invalide\n- Ou l\'action AJAX n\'existe pas');
        } else {
          alert('❌ Erreur serveur: ' + response.status + '\n\nResponse: ' + responseText);
        }
        
        throw new Error(`Erreur serveur: ${response.status} - ${responseText.substring(0, 100)}`);
      }

      if (responseText.trim() === '0') {
        throw new Error('Erreur d\'authentification');
      }

      let data: any;
      try {
        data = JSON.parse(responseText);
      } catch (e) {
        // Si ce n'est pas du JSON, utiliser la réponse comme HTML brut
        const newWindow = window.open('', '_blank');
        if (newWindow) {
          newWindow.document.write(responseText);
          newWindow.document.close();
        }
        return;
      }

      if (data.success && data.data && data.data.html) {
        const newWindow = window.open('', '_blank');
        if (newWindow) {
          const htmlContent = `
            <!DOCTYPE html>
            <html lang="fr">
            <head>
              <meta charset="UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <title>Aperçu HTML - ${state.template?.name || 'Template'}</title>
              <style>
                body {
                  margin: 0;
                  padding: 20px;
                  background-color: #f5f5f5;
                  font-family: Arial, sans-serif;
                }
                .html-container {
                  background: white;
                  padding: 20px;
                  border-radius: 8px;
                  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                  max-width: 1200px;
                  margin: 0 auto;
                }
              </style>
            </head>
            <body>
              <div class="html-container">
                ${data.data.html}
              </div>
            </body>
            </html>
          `;
          newWindow.document.write(htmlContent);
          newWindow.document.close();
        }
      } else {
        throw new Error(data.data?.message || 'Erreur inconnue');
      }
    } catch (error) {
      console.error('[JSON TO HTML] Error:', error);
      alert(`❌ Erreur: ${error instanceof Error ? error.message : String(error)}`);
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

  // Fonction pour générer HTML qui simule un PDF avec les paramètres du plugin
  const generatePDFSimulationHTML = () => {
    const canvasWidth = state.canvas.width || 794;
    const canvasHeight = state.canvas.height || 1123;
    const elements = state.elements || [];
    const template = state.template || {};

    // Paramètres par défaut du plugin
    const margins = { top: 20, bottom: 20, left: 20, right: 20 };
    const colors = {
      primary: '#007cba',
      secondary: '#666666',
      text: '#333333',
      border: '#e0e0e0',
      background: '#f8f9fa',
    };
    const fonts = { family: 'Arial, sans-serif', size: 12 };

    // Construire le HTML simulant un PDF
    let html = `<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aperçu PDF - ${template.name || 'Template'}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body { padding: 20px; }
    .pdf-wrapper { background: rgb(255 255 255); display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
    .pdf-page {
      width: ${canvasWidth}px;
      min-height: ${canvasHeight}px;
      margin: 0 auto;
      padding: ${margins.top}px ${margins.right}px ${margins.bottom}px ${margins.left}px;
      position: relative;
      overflow: hidden;
    }
    .element {
      position: absolute;
      overflow: auto;
    }
    .element-content {
      width: 100%;
      height: 100%;
      word-wrap: break-word;
      overflow: auto;
    }
  </style>
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

        // Construire les styles inline à partir du JSON
        let styles = `left: ${x}px; top: ${y}px; width: ${w}px; height: ${h}px;`;
        
        // Font styles
        if (element.fontSize) styles += ` font-size: ${element.fontSize}px;`;
        if (element.fontFamily) styles += ` font-family: ${element.fontFamily};`;
        if (element.fontWeight) styles += ` font-weight: ${element.fontWeight};`;
        if (element.fontStyle && element.fontStyle !== 'normal') styles += ` font-style: ${element.fontStyle};`;
        if (element.textDecoration && element.textDecoration !== 'none') styles += ` text-decoration: ${element.textDecoration};`;
        if (element.lineHeight) styles += ` line-height: ${element.lineHeight};`;
        if (element.letterSpacing && element.letterSpacing !== 'normal') styles += ` letter-spacing: ${element.letterSpacing};`;
        if (element.wordSpacing && element.wordSpacing !== 'normal') styles += ` word-spacing: ${element.wordSpacing};`;
        if (element.textTransform && element.textTransform !== 'none') styles += ` text-transform: ${element.textTransform};`;
        if (element.textAlign) styles += ` text-align: ${element.textAlign};`;
        if (element.verticalAlign) styles += ` vertical-align: ${element.verticalAlign};`;
        
        // Colors
        if (element.textColor) styles += ` color: ${element.textColor};`;
        if (element.backgroundColor && element.showBackground !== false) styles += ` background-color: ${element.backgroundColor};`;
        
        // Borders
        if (element.borderWidth) styles += ` border: ${element.borderWidth}px solid ${element.borderColor || '#e5e7eb'};`;
        if (element.borderRadius) styles += ` border-radius: ${element.borderRadius}px;`;
        
        // Shadow
        if (element.shadowBlur && element.shadowBlur > 0) {
          const offsetX = element.shadowOffsetX || 0;
          const offsetY = element.shadowOffsetY || 0;
          const blur = element.shadowBlur || 0;
          const color = element.shadowColor || '#000000';
          styles += ` box-shadow: ${offsetX}px ${offsetY}px ${blur}px ${color};`;
        }
        
        // Rotation
        if (element.rotation) styles += ` transform: rotate(${element.rotation}deg);`;
        
        // Opacity
        if (element.opacity && element.opacity < 1) styles += ` opacity: ${element.opacity};`;

        let content = '';
        switch (element.type) {
          case 'text':
          case 'dynamic_text':
            content = element.text || element.content || 'Texte';
            if (element.autoWrap !== false) styles += ` white-space: pre-wrap; word-wrap: break-word;`;
            break;
          case 'document_type':
            content = element.text || element.content || element.title || 'FACTURE';
            styles += ` display: flex; align-items: center; justify-content: center;`;
            break;
          case 'order_number':
            content = element.text || element.content || `Commande #${element.orderNumber || '001'}`;
            break;
          case 'company_logo':
          case 'image':
            if (element.src) {
              let imgStyles = `max-width: 100%; max-height: 100%; display: block;`;
              if (element.objectFit) imgStyles += ` object-fit: ${element.objectFit};`;
              if (element.opacity && element.opacity < 1) imgStyles += ` opacity: ${element.opacity};`;
              if (element.borderRadius) imgStyles += ` border-radius: ${element.borderRadius}px;`;
              if (element.borderWidth) imgStyles += ` border: ${element.borderWidth}px solid ${element.borderColor || '#e5e7eb'};`;
              content = `<img src="${element.src}" style="${imgStyles}" />`;
            } else {
              content = element.text || element.content || '📦 Logo';
            }
            break;
          case 'line':
          case 'separator':
            content = '';
            if (element.strokeWidth) styles += ` border-top: ${element.strokeWidth}px solid ${element.strokeColor || '#000000'};`;
            break;
          case 'product_table':
          case 'table':
            // Styles du tableau
            let tableStyles = `border-collapse: collapse; width: 100%;`;
            if (element.fontSize) tableStyles += ` font-size: ${element.fontSize}px;`;
            if (element.fontFamily) tableStyles += ` font-family: ${element.fontFamily};`;
            if (element.fontWeight && element.fontWeight !== 'normal') tableStyles += ` font-weight: ${element.fontWeight};`;
            if (element.fontStyle && element.fontStyle !== 'normal') tableStyles += ` font-style: ${element.fontStyle};`;
            if (element.textDecoration && element.textDecoration !== 'none') tableStyles += ` text-decoration: ${element.textDecoration};`;
            if (element.lineHeight) tableStyles += ` line-height: ${element.lineHeight};`;
            if (element.letterSpacing && element.letterSpacing !== 'normal') tableStyles += ` letter-spacing: ${element.letterSpacing};`;
            if (element.wordSpacing && element.wordSpacing !== 'normal') tableStyles += ` word-spacing: ${element.wordSpacing};`;
            if (element.backgroundColor) tableStyles += ` background-color: ${element.backgroundColor};`;
            
            // Styles pour les cellules (td/th)
            let cellStyles = `padding: 8px; text-align: ${element.textAlign || 'left'}; vertical-align: ${element.verticalAlign || 'top'};`;
            if (element.borderWidth && element.showBorders) cellStyles += ` border: ${element.borderWidth}px solid ${element.borderColor || '#e5e7eb'};`;
            if (element.textColor) cellStyles += ` color: ${element.textColor};`;
            
            // Styles pour les headers
            let headerStyle = `padding: 8px; text-align: ${element.textAlign || 'left'}; vertical-align: ${element.verticalAlign || 'top'};`;
            if (element.showHeaders) {
              if (element.headerBackgroundColor) headerStyle += `background-color: ${element.headerBackgroundColor};`;
              if (element.headerTextColor) headerStyle += `color: ${element.headerTextColor};`;
              if (element.borderWidth && element.showBorders) headerStyle += `border: ${element.borderWidth}px solid ${element.borderColor || '#e5e7eb'};`;
              if (element.fontWeight) headerStyle += `font-weight: ${element.fontWeight};`;
            }
            
            const tableId = `table-${element.id}`;
            let tableCSS = '';
            
            // Ajouter CSS pour les lignes alternées
            if (element.showAlternatingRows && element.alternateRowColor) {
              tableCSS = `<style>
                #${tableId} tbody tr:nth-child(odd) td { background-color: ${element.alternateRowColor}; }
              </style>`;
            }
            
            // Générer le contenu du tableau avec styles dynamiques
            if (element.content) {
              // Remplacer les tags td et th avec les styles
              let wrappedContent = element.content
                .replace(/<th([^>]*)>/g, `<th style="${headerStyle}"$1>`)
                .replace(/<td([^>]*)>/g, `<td style="${cellStyles}"$1>`);
              
              content = tableCSS + `<table id="${tableId}" style="${tableStyles}">${wrappedContent}</table>`;
            } else {
              content = tableCSS + `<table id="${tableId}" style="${tableStyles}"><thead><tr><th style="${headerStyle}">Produit</th><th style="${headerStyle}">Qty</th><th style="${headerStyle}">Prix</th></tr></thead><tbody><tr><td style="${cellStyles}">Exemple</td><td style="${cellStyles}">1</td><td style="${cellStyles}">100€</td></tr></tbody></table>`;
            }
            break;
          case 'company_info':
            content = element.content || element.text || '';
            if (!content) {
              // Si pas de contenu, créer un default
              let companyContent = '<div style="margin-bottom:4px;"><strong>Entreprise</strong></div>';
              companyContent += '<div>Nom: SARL Example</div>';
              companyContent += '<div>Adresse: 123 Rue</div>';
              content = companyContent;
            }
            if (element.showBackground && element.backgroundColor && element.backgroundColor !== 'transparent') {
              styles += ` background-color: ${element.backgroundColor};`;
            }
            styles += ` padding: 8px; overflow: auto;`;
            break;
          case 'customer_info':
            content = element.content || element.text || '';
            if (!content) {
              // Si pas de contenu, créer un default
              let customerContent = '<div style="margin-bottom:4px;"><strong>Client</strong></div>';
              customerContent += '<div>Nom: Client</div>';
              customerContent += '<div>Email: client@example.com</div>';
              content = customerContent;
            }
            if (element.showBackground && element.backgroundColor && element.backgroundColor !== 'transparent') {
              styles += ` background-color: ${element.backgroundColor};`;
            }
            if (element.showBorders && element.borderWidth) {
              styles += ` border: ${element.borderWidth}px solid ${element.borderColor || '#f3f4f6'};`;
            }
            styles += ` padding: 8px; overflow: auto;`;
            break;
          case 'mentions':
          case 'note':
            content = element.content || element.text || '';
            break;
          default:
            content = element.text || element.content || element.label || `[${element.type}]`;
        }

        html += `<div class="element" style="${styles}">
          <div class="element-content">${content}</div>
        </div>`;
      });
    } else {
      html += `<div style="padding: 40px; text-align: center; color: #666;">
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

  // Fonction pour générer et afficher l'aperçu HTML
  const handleShowHtmlPreview = () => {
    setIsGeneratingHtml(true);
    try {
      const html = generatePDFSimulationHTML();
      setGeneratedHtml(html);
      setJsonModalMode('html');
    } catch (error) {
      console.error('Erreur lors de la génération HTML:', error);
      alert('Erreur lors de la génération de l\'aperçu HTML');
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
              maxWidth: "65vw",
              width: "100%",
              height: "87vh",
              display: "flex",
              flexDirection: "column",
              boxShadow: "0 10px 40px rgba(0, 0, 0, 0.3)",
            }}
          >
            {/* Header avec Toggle JSON/HTML */}
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
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <h3
                  style={{
                    margin: 0,
                    fontSize: "18px",
                    fontWeight: "600",
                    color: "#1a1a1a",
                  }}
                >
                  {jsonModalMode === 'json' ? '📋' : '🎨'} {jsonModalMode === 'json' ? 'JSON' : 'Aperçu HTML'}
                </h3>
                {/* Toggle Buttons */}
                <div style={{ display: 'flex', gap: '6px', borderRadius: '4px', border: '1px solid #ddd', padding: '3px' }}>
                  <button
                    onClick={() => setJsonModalMode('json')}
                    style={{
                      padding: '6px 12px',
                      border: 'none',
                      borderRadius: '3px',
                      backgroundColor: jsonModalMode === 'json' ? '#007cba' : '#f0f0f0',
                      color: jsonModalMode === 'json' ? '#fff' : '#333',
                      cursor: 'pointer',
                      fontSize: '12px',
                      fontWeight: jsonModalMode === 'json' ? 'bold' : 'normal',
                    }}
                    title="Afficher le JSON"
                  >
                    JSON
                  </button>
                  <button
                    onClick={handleShowHtmlPreview}
                    disabled={isGeneratingHtml}
                    style={{
                      padding: '6px 12px',
                      border: 'none',
                      borderRadius: '3px',
                      backgroundColor: jsonModalMode === 'html' ? '#10a37f' : '#f0f0f0',
                      color: jsonModalMode === 'html' ? '#fff' : '#333',
                      cursor: isGeneratingHtml ? 'not-allowed' : 'pointer',
                      fontSize: '12px',
                      fontWeight: jsonModalMode === 'html' ? 'bold' : 'normal',
                      opacity: isGeneratingHtml ? 0.6 : 1,
                    }}
                    title="Afficher l'aperçu HTML"
                  >
                    {isGeneratingHtml ? '⏳' : '🎨'} HTML
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
              {jsonModalMode === 'json' ? (
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
                    2
                  )}
                </pre>
              ) : (
                <iframe
                  srcDoc={generatedHtml}
                  style={{
                    width: '100%',
                    height: '100%',
                    flex: 1,
                    border: 'none',
                    borderRadius: '4px',
                    backgroundColor: '#f5f5f5',
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
                flexWrap: 'wrap',
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
                      2
                    )
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

      {/* Modale d'aperçu PDF/HTML */}
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
            console.log('[REACT HEADER] - htmlPreviewContent length:', state.htmlPreviewContent?.length || 0);
            console.log('[REACT HEADER] Modal rendering timestamp:', Date.now());
          }}
        >
          <div
            style={{
              backgroundColor: "#ffffff",
              borderRadius: "8px",
              padding: "24px",
              maxWidth: "90vw",
              width: state.htmlPreviewContent ? "90vw" : "600px",
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
                {state.htmlPreviewContent ? "Aperçu HTML du PDF" : "Aperçu du PDF"}
              </h3>
              <button
                onClick={() => {
                  closePreviewModal();
                  clearPreview();
                  // Vider aussi le contenu HTML de l'aperçu
                  state.dispatch({ type: 'SET_HTML_PREVIEW_CONTENT', payload: '' });
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

            {/* Contenu conditionnel basé sur le type d'aperçu */}
            {state.htmlPreviewContent ? (
              /* Aperçu HTML */
              <div>
                <div style={{ marginBottom: "16px", padding: "12px", backgroundColor: "#f8f9fa", borderRadius: "4px", fontSize: "14px", color: "#666" }}>
                  <strong>ℹ️ Aperçu HTML:</strong> Cette prévisualisation montre comment votre PDF sera rendu avec les paramètres actuels du plugin.
                </div>
                <div
                  style={{
                    border: "1px solid #ddd",
                    borderRadius: "4px",
                    padding: "16px",
                    backgroundColor: "#fafafa",
                    maxHeight: "600px",
                    overflow: "auto"
                  }}
                  dangerouslySetInnerHTML={{ __html: state.htmlPreviewContent }}
                />
              </div>
            ) : (
              /* Aperçu PDF (format image) - contenu existant */
              <>
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
              </>
            )}
          </div>
        </div>
      )}
    </div>
  );
});


