import React, { useRef, useEffect, useState } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { Element } from '../../types/elements';

interface PreviewModalProps {
  isOpen: boolean;
  onClose: () => void;
  canvasWidth: number;
  canvasHeight: number;
}

export function PreviewModal({ isOpen, onClose, canvasWidth, canvasHeight }: PreviewModalProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [zoom, setZoom] = useState(1.0); // Zoom par défaut à 100% pour voir le canvas aux vraies dimensions
  const [isLoading, setIsLoading] = useState(false);
  const [previewElements, setPreviewElements] = useState<any[]>([]);
  const { state } = useBuilder();

  // Fonction pour remplacer les variables dynamiques dans le texte
  const replaceVariables = (text: string): string => {
    // Variables d'exemple pour l'aperçu (en mode éditeur)
    const variables = {
      '{{customer_name}}': 'Jean Dupont',
      '{{customer_email}}': 'jean.dupont@email.com',
      '{{customer_phone}}': '+33 1 23 45 67 89',
      '{{customer_address}}': '123 Rue de la Paix\n75001 Paris\nFrance',
      '{{order_number}}': 'CMD-2025-001',
      '{{order_date}}': '30 octobre 2025',
      '{{order_total}}': '299,99 €',
      '{{company_name}}': 'Ma Société SARL',
      '{{company_address}}': '456 Avenue des Champs\n75008 Paris\nFrance',
      '{{company_phone}}': '+33 1 98 76 54 32',
      '{{company_email}}': 'contact@masociete.com',
      '{{company_vat}}': 'FR 12 345 678 901'
    };

    let result = text;
    Object.entries(variables).forEach(([variable, value]) => {
      result = result.replace(new RegExp(variable.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), value);
    });
    return result;
  };

  // Récupérer les éléments du template depuis la base de données
  useEffect(() => {
    if (isOpen) {
      loadTemplateElements();
    }
  }, [isOpen]);

  // Redessiner le canvas quand les éléments ou le zoom changent
  useEffect(() => {
    if (previewElements.length > 0) {
      renderPreview();
    }
  }, [previewElements, zoom]);

  // Re-rendre quand les éléments changent
  useEffect(() => {
    if (isOpen && previewElements.length > 0) {
      renderPreview();
    }
  }, [isOpen, previewElements, zoom]);

  const loadTemplateElements = async () => {
    setIsLoading(true);
    try {
      // Récupérer l'ID du template depuis l'URL ou le state
      const urlParams = new URLSearchParams(window.location.search);
      const templateId = urlParams.get('template_id') || state.template?.id;

      if (!templateId) {
        setPreviewElements(state.elements); // Fallback vers le state local
        setIsLoading(false);
        return;
      }

      // Faire une requête AJAX pour récupérer les données du template
      const ajaxUrl = (window as any).ajaxurl || '/wp-admin/admin-ajax.php';
      const nonce = (window as any).pdfBuilderData?.nonce || (window as any).pdfBuilderNonce || (window as any).pdfBuilderReactData?.nonce || '';
      const response = await fetch(`${ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${nonce}`, {
        method: 'GET'
      });

      const data = await response.json();

      if (data.success && data.data && data.data.elements) {
        // Corriger automatiquement les coordonnées des éléments qui dépassent A4
        // Mais seulement si nécessaire - la plupart des éléments devraient déjà être dans les bonnes proportions
        const correctedElements = data.data.elements.map((element: any) => {
          const corrected = { ...element };

          // Vérifier si l'élément dépasse vraiment les limites (avec une marge de 10px)
          const margin = 10;
          const exceedsRight = corrected.x + corrected.width > canvasWidth + margin;
          const exceedsBottom = corrected.y + corrected.height > canvasHeight + margin;

          // Seulement corriger si l'élément dépasse vraiment
          if (exceedsRight) {
            corrected.x = Math.max(0, canvasWidth - corrected.width);
          }

          if (exceedsBottom) {
            corrected.y = Math.max(0, canvasHeight - corrected.height);
          }

          // S'assurer que les coordonnées ne sont pas négatives
          corrected.x = Math.max(0, corrected.x);
          corrected.y = Math.max(0, corrected.y);

          return corrected;
        });

        setPreviewElements(correctedElements);
      } else {
        setPreviewElements(state.elements); // Fallback
      }
    } catch (error) {
      setPreviewElements(state.elements); // Fallback
    }
    setIsLoading(false);
  };

  // Fonction pour rendre l'aperçu
  const renderPreview = () => {
    if (!canvasRef.current) return;

    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    setIsLoading(true);

    // Définir la taille du canvas pour l'aperçu (dimensions de base, le zoom sera géré par CSS ou transformation)
    canvas.width = canvasWidth;
    canvas.height = canvasHeight;

    // Clear canvas avec fond blanc
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvasWidth, canvasHeight);

    // Plus de transformation scale - le zoom est géré par CSS
    // Rendre tous les éléments avec leurs coordonnées absolues
    previewElements.forEach(element => {
      renderElement(ctx, element);
    });
    setIsLoading(false);
  };

  // Fonction simplifiée pour rendre un élément (version aperçu)
  const renderElement = (ctx: CanvasRenderingContext2D, element: Element) => {
    ctx.save();
    ctx.translate(element.x, element.y);
    if (element.rotation) {
      ctx.rotate((element.rotation * Math.PI) / 180);
    }

    const props = element as any;

    switch (element.type) {
      case 'rectangle':
        ctx.fillStyle = props.fillColor || props.backgroundColor || '#ffffff';
        ctx.strokeStyle = props.strokeColor || props.borderColor || '#000000';
        ctx.lineWidth = props.strokeWidth || props.borderWidth || 1;
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);
        break;

      case 'text':
        ctx.fillStyle = props.color || props.textColor || '#000000';
        ctx.font = `${props.fontWeight || 'normal'} ${props.fontSize || 14}px ${props.fontFamily || 'Arial'}`;
        ctx.textAlign = (props.textAlign || props.align || 'left') as CanvasTextAlign;
        ctx.textBaseline = 'top';
        const text = replaceVariables(props.text || 'Texte');
        const lines = text.split('\n');
        let y = 0;
        lines.forEach((line: string) => {
          ctx.fillText(line, 0, y);
          y += props.fontSize || 14;
        });
        break;

      case 'company_logo':
        // Rendu amélioré pour le logo
        if (props.src || props.imageUrl) {
          // Si on a une image, essayer de la charger
          const img = new Image();
          img.onload = () => {
            ctx.drawImage(img, 0, 0, element.width, element.height);
          };
          img.src = props.src || props.imageUrl;
        } else {
          // Placeholder si pas d'image
          ctx.fillStyle = '#f0f0f0';
          ctx.strokeStyle = '#ccc';
          ctx.lineWidth = 1;
          ctx.fillRect(0, 0, element.width, element.height);
          ctx.strokeRect(0, 0, element.width, element.height);
          ctx.fillStyle = '#666';
          ctx.font = '12px Arial';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText('Company Logo', element.width / 2, element.height / 2);
        }
        break;

      case 'order_number':
        // Copier la logique complète de Canvas.tsx pour cohérence
        const orderFontSize = props.fontSize || 14;
        const orderFontFamily = props.fontFamily || 'Arial';
        const orderFontWeight = props.fontWeight || 'normal';
        const orderFontStyle = props.fontStyle || 'normal';
        const orderLabelFontSize = props.labelFontSize || orderFontSize;
        const orderLabelFontFamily = props.labelFontFamily || orderFontFamily;
        const orderLabelFontWeight = props.labelFontWeight || 'bold';
        const orderLabelFontStyle = props.labelFontStyle || orderFontStyle;
        const orderNumberFontSize = props.numberFontSize || orderFontSize;
        const orderNumberFontFamily = props.numberFontFamily || orderFontFamily;
        const orderNumberFontWeight = props.numberFontWeight || orderFontWeight;
        const orderNumberFontStyle = props.numberFontStyle || orderFontStyle;
        const orderDateFontSize = props.dateFontSize || (orderFontSize - 2);
        const orderDateFontFamily = props.dateFontFamily || orderFontFamily;
        const orderDateFontWeight = props.dateFontWeight || orderFontWeight;
        const orderDateFontStyle = props.dateFontStyle || orderFontStyle;
        const orderTextAlign = props.textAlign || 'left';
        const orderLabelTextAlign = props.labelTextAlign || orderTextAlign;
        const orderNumberTextAlign = props.numberTextAlign || orderTextAlign;
        const orderDateTextAlign = props.dateTextAlign || orderTextAlign;
        const orderContentAlign = props.contentAlign || 'left'; // Alignement général du contenu dans l'élément
        const orderShowLabel = props.showLabel !== false;
        const orderShowDate = props.showDate !== false;
        const orderLabelPosition = props.labelPosition || 'above';
        const orderLabelText = props.labelText || 'N° de commande:'; // Texte personnalisable du libellé

        // Fonction helper pour calculer la position X selon l'alignement général du contenu
        const calculateOrderContentX = (align: string) => {
          if (align === 'left') {
            return 10;
          } else if (align === 'center') {
            return element.width / 2;
          } else { // right
            return element.width - 10;
          }
        };

        // Fonction helper pour calculer la position X selon l'alignement du texte
        const calculateOrderX = (align: string) => {
          if (align === 'left') {
            return 10;
          } else if (align === 'center') {
            return element.width / 2;
          } else { // right
            return element.width - 10;
          }
        };

        ctx.fillStyle = props.backgroundColor || 'transparent';
        ctx.fillRect(0, 0, element.width, element.height);

        ctx.fillStyle = '#000000';

        // Numéro de commande et date
        const orderNumber = replaceVariables('{{order_number}}') || 'CMD-XXXX-XXXX';
        const orderDate = replaceVariables('{{order_date}}') || '30/10/2025';

        let orderY = 20;

        // Calculer la largeur totale du contenu pour l'alignement général
        let orderTotalContentWidth = 0;
        if (orderShowLabel) {
          if (orderLabelPosition === 'above' || orderLabelPosition === 'below') {
            // Pour les positions verticales, prendre la largeur maximale
            ctx.font = `${orderLabelFontStyle} ${orderLabelFontWeight} ${orderLabelFontSize}px ${orderLabelFontFamily}`;
            const labelWidth = ctx.measureText(orderLabelText).width;
            ctx.font = `${orderNumberFontStyle} ${orderNumberFontWeight} ${orderNumberFontSize}px ${orderNumberFontFamily}`;
            const numberWidth = ctx.measureText(orderNumber).width;
            orderTotalContentWidth = Math.max(labelWidth, numberWidth);
          } else {
            // Pour les positions latérales, calculer la largeur combinée
            ctx.font = `${orderLabelFontStyle} ${orderLabelFontWeight} ${orderLabelFontSize}px ${orderLabelFontFamily}`;
            const labelWidth = ctx.measureText(orderLabelText).width;
            ctx.font = `${orderNumberFontStyle} ${orderNumberFontWeight} ${orderNumberFontSize}px ${orderNumberFontFamily}`;
            const numberWidth = ctx.measureText(orderNumber).width;
            orderTotalContentWidth = labelWidth + numberWidth + 15; // 15px d'espace
          }
        } else {
          // Juste le numéro
          ctx.font = `${orderNumberFontStyle} ${orderNumberFontWeight} ${orderNumberFontSize}px ${orderNumberFontFamily}`;
          orderTotalContentWidth = ctx.measureText(orderNumber).width;
        }

        // Calculer le décalage pour l'alignement général du contenu
        let orderContentOffsetX = 0;
        if (orderContentAlign === 'center') {
          orderContentOffsetX = (element.width - orderTotalContentWidth) / 2 - 10; // -10 car on commence à 10
        } else if (orderContentAlign === 'right') {
          orderContentOffsetX = element.width - orderTotalContentWidth - 20; // -20 pour les marges
        }

        // Afficher selon la position du libellé et du numéro
        if (orderShowLabel) {
          if (orderLabelPosition === 'above') {
            // Libellé au-dessus, numéro en-dessous - utiliser l'alignement général du contenu
            ctx.font = `${orderLabelFontStyle} ${orderLabelFontWeight} ${orderLabelFontSize}px ${orderLabelFontFamily}`;
            ctx.textAlign = orderContentAlign as CanvasTextAlign;
            const labelX = orderContentAlign === 'left' ? 10 + orderContentOffsetX :
                          orderContentAlign === 'center' ? element.width / 2 :
                          element.width - 10;
            ctx.fillText(orderLabelText, labelX, orderY);
            orderY += 18;
            ctx.font = `${orderNumberFontStyle} ${orderNumberFontWeight} ${orderNumberFontSize}px ${orderNumberFontFamily}`;
            ctx.textAlign = orderContentAlign as CanvasTextAlign;
            const numberX = orderContentAlign === 'left' ? 10 + orderContentOffsetX :
                           orderContentAlign === 'center' ? element.width / 2 :
                           element.width - 10;
            ctx.fillText(orderNumber, numberX, orderY);
          } else if (orderLabelPosition === 'below') {
            // Numéro au-dessus, libellé en-dessous - utiliser l'alignement général du contenu
            ctx.font = `${orderNumberFontStyle} ${orderNumberFontWeight} ${orderNumberFontSize}px ${orderNumberFontFamily}`;
            ctx.textAlign = orderContentAlign as CanvasTextAlign;
            const numberX = orderContentAlign === 'left' ? 10 + orderContentOffsetX :
                           orderContentAlign === 'center' ? element.width / 2 :
                           element.width - 10;
            ctx.fillText(orderNumber, numberX, orderY);
            orderY += 18;
            ctx.font = `${orderLabelFontStyle} ${orderLabelFontWeight} ${orderLabelFontSize}px ${orderLabelFontFamily}`;
            ctx.textAlign = orderContentAlign as CanvasTextAlign;
            const labelX = orderContentAlign === 'left' ? 10 + orderContentOffsetX :
                          orderContentAlign === 'center' ? element.width / 2 :
                          element.width - 10;
            ctx.fillText(orderLabelText, labelX, orderY);
          } else if (orderLabelPosition === 'left') {
            // Libellé à gauche, numéro à droite - avec espacement optimal et alignement général
            ctx.font = `${orderLabelFontStyle} ${orderLabelFontWeight} ${orderLabelFontSize}px ${orderLabelFontFamily}`;
            ctx.textAlign = 'left' as CanvasTextAlign;
            const labelX = 10 + orderContentOffsetX;
            ctx.fillText(orderLabelText, labelX, orderY);

            // Calculer l'espace disponible pour centrer le numéro ou l'aligner intelligemment
            const labelWidth = ctx.measureText(orderLabelText).width;
            const numberX = labelX + labelWidth + 15; // 15px d'espace après le libellé

            ctx.font = `${orderNumberFontStyle} ${orderNumberFontWeight} ${orderNumberFontSize}px ${orderNumberFontFamily}`;
            ctx.textAlign = 'left' as CanvasTextAlign;
            ctx.fillText(orderNumber, numberX, orderY);
          } else if (orderLabelPosition === 'right') {
            // Numéro à gauche, libellé à droite - avec espacement optimal et alignement général
            ctx.font = `${orderNumberFontStyle} ${orderNumberFontWeight} ${orderNumberFontSize}px ${orderNumberFontFamily}`;
            ctx.textAlign = 'left' as CanvasTextAlign;
            const numberX = 10 + orderContentOffsetX;
            ctx.fillText(orderNumber, numberX, orderY);

            // Calculer la position du libellé après le numéro
            const numberWidth = ctx.measureText(orderNumber).width;
            const labelX = numberX + numberWidth + 15; // 15px d'espace après le numéro

            ctx.font = `${orderLabelFontStyle} ${orderLabelFontWeight} ${orderLabelFontSize}px ${orderLabelFontFamily}`;
            ctx.textAlign = 'left' as CanvasTextAlign;
            ctx.fillText(orderLabelText, labelX, orderY);
          }
        } else {
          // Pas de libellé, juste le numéro avec alignement général du contenu
          ctx.font = `${orderNumberFontStyle} ${orderNumberFontWeight} ${orderNumberFontSize}px ${orderNumberFontFamily}`;
          ctx.textAlign = orderContentAlign as CanvasTextAlign;
          // Pour le cas sans libellé, utiliser directement calculateOrderContentX sans orderContentOffsetX
          // car orderContentOffsetX est calculé pour centrer le contenu total, mais ici on n'a que le numéro
          if (orderContentAlign === 'left') {
            ctx.fillText(orderNumber, 10, orderY);
          } else if (orderContentAlign === 'center') {
            ctx.fillText(orderNumber, element.width / 2, orderY);
          } else { // right
            ctx.fillText(orderNumber, element.width - 10, orderY);
          }
        }

        // Afficher la date sur une nouvelle ligne avec le même alignement général
        if (orderShowDate) {
          ctx.font = `${orderDateFontStyle} ${orderDateFontWeight} ${orderDateFontSize}px ${orderDateFontFamily}`;
          ctx.textAlign = orderContentAlign as CanvasTextAlign;
          // Pour la date, utiliser directement calculateOrderContentX sans orderContentOffsetX
          // car orderContentOffsetX est calculé pour centrer le contenu total
          if (orderContentAlign === 'left') {
            ctx.fillText(`Date: ${orderDate}`, 10, orderY + 20);
          } else if (orderContentAlign === 'center') {
            ctx.fillText(`Date: ${orderDate}`, element.width / 2, orderY + 20);
          } else { // right
            ctx.fillText(`Date: ${orderDate}`, element.width - 10, orderY + 20);
          }
        }
        break;

      case 'company_info':
        // Copier la logique complète de Canvas.tsx pour cohérence
        const companyFontSize = props.fontSize || 12;
        const companyFontFamily = props.fontFamily || 'Arial';
        const companyFontWeight = props.fontWeight || 'normal';
        const companyFontStyle = props.fontStyle || 'normal';
        const companyHeaderFontSize = props.headerFontSize || Math.round(companyFontSize * 1.2);
        const companyHeaderFontFamily = props.headerFontFamily || companyFontFamily;
        const companyHeaderFontWeight = props.headerFontWeight || 'bold';
        const companyHeaderFontStyle = props.headerFontStyle || companyFontStyle;
        const companyBodyFontSize = props.bodyFontSize || companyFontSize;
        const companyBodyFontFamily = props.bodyFontFamily || companyFontFamily;
        const companyBodyFontWeight = props.bodyFontWeight || companyFontWeight;
        const companyBodyFontStyle = props.bodyFontStyle || companyFontStyle;
        const companyTextAlign = 'left'; // Forcer alignement à gauche comme dans Canvas.tsx
        const companyTheme = (props.theme || 'corporate') as keyof typeof companyThemes;
        const companyShowHeaders = props.showHeaders !== false;
        const companyShowBorders = props.showBorders !== false;
        const companyShowCompanyName = props.showCompanyName !== false;
        const companyShowAddress = props.showAddress !== false;
        const companyShowPhone = props.showPhone !== false;
        const companyShowEmail = props.showEmail !== false;
        const companyShowSiret = props.showSiret !== false;
        const companyShowTva = props.showTva !== false;

        // Définition des thèmes (simplifiée pour aperçu)
        const companyThemes = {
          corporate: { backgroundColor: '#ffffff', borderColor: '#1f2937', textColor: '#374151', headerTextColor: '#111827' },
          modern: { backgroundColor: '#ffffff', borderColor: '#3b82f6', textColor: '#1e40af', headerTextColor: '#1e3a8a' },
          elegant: { backgroundColor: '#ffffff', borderColor: '#8b5cf6', textColor: '#6d28d9', headerTextColor: '#581c87' },
          minimal: { backgroundColor: '#ffffff', borderColor: '#e5e7eb', textColor: '#374151', headerTextColor: '#111827' },
          professional: { backgroundColor: '#ffffff', borderColor: '#059669', textColor: '#047857', headerTextColor: '#064e3b' }
        };

        const companyCurrentTheme = companyThemes[companyTheme] || companyThemes.corporate;
        const companyBgColor = props.backgroundColor || companyCurrentTheme.backgroundColor;
        const companyBorderCol = props.borderColor || companyCurrentTheme.borderColor;
        const companyTxtColor = props.textColor || companyCurrentTheme.textColor;
        const companyHeaderTxtColor = props.headerTextColor || companyCurrentTheme.headerTextColor;

        // Fond
        ctx.fillStyle = companyBgColor;
        ctx.fillRect(0, 0, element.width, element.height);

        // Bordures
        if (companyShowBorders) {
          ctx.strokeStyle = companyBorderCol;
          ctx.lineWidth = 1;
          ctx.strokeRect(0, 0, element.width, element.height);
        }

        ctx.fillStyle = companyTxtColor;
        ctx.textAlign = companyTextAlign as CanvasTextAlign;

        // Position X (toujours à gauche)
        const companyX = 10;
        let companyY = 20;

        // Informations entreprise
        const companyData = {
          name: props.companyName || 'Ma Boutique En Ligne',
          address: props.companyAddress || '25 avenue des Commerçants',
          city: props.companyCity || '69000 Lyon',
          siret: props.companySiret || 'SIRET: 123 456 789 00012',
          tva: props.companyTva || 'TVA: FR 12 345 678 901',
          email: props.companyEmail || 'contact@maboutique.com',
          phone: props.companyPhone || '+33 4 12 34 56 78'
        };

        // Afficher le nom de l'entreprise
        if (companyShowCompanyName) {
          ctx.fillStyle = companyHeaderTxtColor;
          ctx.font = `${companyHeaderFontStyle} ${companyHeaderFontWeight} ${companyHeaderFontSize}px ${companyHeaderFontFamily}`;
          ctx.fillText(companyData.name, companyX, companyY);
          companyY += Math.round(companyFontSize * 1.5);
          ctx.fillStyle = companyTxtColor;
        }

        // Police normale
        ctx.font = `${companyBodyFontStyle} ${companyBodyFontWeight} ${companyBodyFontSize}px ${companyBodyFontFamily}`;

        // Afficher l'adresse
        if (companyShowAddress) {
          ctx.fillText(companyData.address, companyX, companyY);
          companyY += Math.round(companyFontSize * 1.2);
          ctx.fillText(companyData.city, companyX, companyY);
          companyY += Math.round(companyFontSize * 1.5);
        }

        // Afficher le SIRET
        if (companyShowSiret) {
          ctx.fillText(companyData.siret, companyX, companyY);
          companyY += Math.round(companyFontSize * 1.2);
        }

        // Afficher la TVA
        if (companyShowTva) {
          ctx.fillText(companyData.tva, companyX, companyY);
          companyY += Math.round(companyFontSize * 1.2);
        }

        // Afficher l'email
        if (companyShowEmail) {
          ctx.fillText(companyData.email, companyX, companyY);
          companyY += Math.round(companyFontSize * 1.2);
        }

        // Afficher le téléphone
        if (companyShowPhone) {
          ctx.fillText(companyData.phone, companyX, companyY);
        }
        break;

      case 'product_table':
        // Rendu amélioré pour le tableau
        ctx.fillStyle = props.backgroundColor || '#f9f9f9';
        ctx.strokeStyle = props.borderColor || '#ddd';
        ctx.lineWidth = props.borderWidth || 1;
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);

        // En-têtes du tableau
        if (props.showHeaders !== false) {
          ctx.fillStyle = '#666';
          ctx.font = `${props.fontSize || 12}px Arial`;
          ctx.textAlign = 'left';
          ctx.textBaseline = 'top';
          ctx.fillText('Produit', 10, 10);
          ctx.fillText('Qté', element.width - 60, 10);
          ctx.fillText('Prix', element.width - 30, 10);
        }

        // Ligne de séparation
        ctx.strokeStyle = '#ccc';
        ctx.beginPath();
        ctx.moveTo(0, 30);
        ctx.lineTo(element.width, 30);
        ctx.stroke();
        break;

      default:
        // Élément générique avec fond gris
        ctx.fillStyle = '#e0e0e0';
        ctx.strokeStyle = '#999';
        ctx.lineWidth = 1;
        ctx.fillRect(0, 0, element.width, element.height);
        ctx.strokeRect(0, 0, element.width, element.height);

        // Texte du type d'élément
        ctx.fillStyle = '#666';
        ctx.font = '10px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(element.type, element.width / 2, element.height / 2);
    }

    ctx.restore();
  };

  useEffect(() => {
    if (isOpen) {
      renderPreview();
    }
  }, [isOpen, state.elements, zoom]);

  if (!isOpen) return null;

  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 2000
    }}>
      <div style={{
        backgroundColor: '#ffffff',
        borderRadius: '8px',
        padding: '20px',
        maxWidth: '95vw',
        maxHeight: '95vh',
        width: 'auto',
        display: 'flex',
        flexDirection: 'column',
        gap: '16px'
      }}>
        {/* Header de la modale */}
        <div style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          borderBottom: '1px solid #e0e0e0',
          paddingBottom: '12px'
        }}>
          <h3 style={{ margin: 0, fontSize: '18px', fontWeight: '600' }}>
            Aperçu du PDF
          </h3>
          <button
            onClick={onClose}
            style={{
              background: 'none',
              border: 'none',
              fontSize: '24px',
              cursor: 'pointer',
              color: '#666',
              padding: '4px'
            }}
          >
            ×
          </button>
        </div>

        {/* Contrôles */}
        <div style={{
          display: 'flex',
          gap: '12px',
          alignItems: 'center'
        }}>
          <button
            onClick={() => setZoom(Math.max(0.25, zoom - 0.25))}
            style={{
              padding: '8px 12px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#f8f8f8',
              cursor: 'pointer'
            }}
          >
            Zoom -
          </button>
          <span style={{ fontSize: '14px', fontWeight: '500' }}>
            {Math.round(zoom * 100)}%
          </span>
          <button
            onClick={() => setZoom(Math.min(3, zoom + 0.25))}
            style={{
              padding: '8px 12px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#f8f8f8',
              cursor: 'pointer'
            }}
          >
            Zoom +
          </button>
          <button
            onClick={() => setZoom(1)}
            style={{
              padding: '8px 12px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#f8f8f8',
              cursor: 'pointer'
            }}
          >
            100%
          </button>
        </div>

        {/* Canvas d'aperçu */}
        <div style={{
          border: '1px solid #e0e0e0',
          borderRadius: '4px',
          padding: '16px',
          backgroundColor: '#f9f9f9',
          overflow: 'auto',
          maxHeight: '80vh',
          maxWidth: '90vw',
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'flex-start'
        }}>
          {isLoading ? (
            <div style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              height: '200px',
              color: '#666'
            }}>
              Chargement de l'aperçu...
            </div>
          ) : (
            <canvas
              ref={canvasRef}
              style={{
                border: '1px solid #ddd',
                borderRadius: '4px',
                backgroundColor: '#ffffff',
                boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
                transform: `scale(${zoom})`,
                transformOrigin: 'top left',
                imageRendering: 'pixelated',
                width: `${canvasWidth * zoom}px`,
                height: `${canvasHeight * zoom}px`,
                maxWidth: '100%',
                maxHeight: '100%'
              }}
            />
          )}
        </div>

        {/* Actions */}
        <div style={{
          display: 'flex',
          gap: '12px',
          justifyContent: 'flex-end'
        }}>
          <button
            onClick={onClose}
            style={{
              padding: '10px 20px',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#f8f8f8',
              cursor: 'pointer'
            }}
          >
            Fermer
          </button>
          <button
            style={{
              padding: '10px 20px',
              border: 'none',
              borderRadius: '4px',
              backgroundColor: '#007bff',
              color: '#ffffff',
              cursor: 'pointer',
              fontWeight: '500'
            }}
          >
            Télécharger PDF
          </button>
        </div>
      </div>
    </div>
  );
}