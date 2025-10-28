import React, { useRef, useEffect, useCallback } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { useCanvasDrop } from '../../hooks/useCanvasDrop.ts';
import { useCanvasInteraction } from '../../hooks/useCanvasInteraction.ts';
import { Point, Element } from '../../types/elements';
import { wooCommerceManager } from '../../utils/WooCommerceElementsManager';

interface CanvasProps {
  width: number;
  height: number;
  className?: string;
}

export function Canvas({ width, height, className }: CanvasProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const { state, dispatch } = useBuilder();

  // Cache pour les images chargées
  const imageCache = useRef<Map<string, HTMLImageElement>>(new Map());

  // Utiliser les hooks pour les interactions
  const { handleDrop, handleDragOver } = useCanvasDrop({
    canvasRef,
    canvasWidth: width,
    canvasHeight: height
  });

  const { handleCanvasClick, handleMouseDown, handleMouseMove, handleMouseUp } = useCanvasInteraction({
    canvasRef
  });

  // Fonction de rendu du canvas
  const renderCanvas = useCallback(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Appliquer transformation (zoom, pan)
    ctx.save();
    ctx.translate(state.canvas.pan.x, state.canvas.pan.y);
    ctx.scale(state.canvas.zoom, state.canvas.zoom);

    // Dessiner la grille si activée
    if (state.canvas.showGrid) {
      drawGrid(ctx, width, height, state.canvas.gridSize);
    }

    // Dessiner les éléments
    console.log('Canvas rendering', state.elements.length, 'elements');
    state.elements.forEach((element, index) => {
      console.log(`Drawing element ${index}:`, element.type, 'at', element.x, element.y);
      drawElement(ctx, element);
    });

    // Dessiner la sélection
    if (state.selection.selectedElements.length > 0) {
      drawSelection(ctx, state.selection.selectedElements, state.elements);
    }

    ctx.restore();
  }, [state, width, height]);

  // Fonction pour dessiner la grille
  const drawGrid = (ctx: CanvasRenderingContext2D, w: number, h: number, size: number) => {
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 1;

    for (let x = 0; x <= w; x += size) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, h);
      ctx.stroke();
    }

    for (let y = 0; y <= h; y += size) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(w, y);
      ctx.stroke();
    }
  };

  // Fonction pour dessiner un élément
  const drawElement = (ctx: CanvasRenderingContext2D, element: Element) => {
    ctx.save();

    // Appliquer transformation de l'élément
    ctx.translate(element.x, element.y);
    if (element.rotation) {
      ctx.rotate((element.rotation * Math.PI) / 180);
    }

    // Dessiner selon le type d'élément
    switch (element.type) {
      case 'rectangle':
        drawRectangle(ctx, element);
        break;
      case 'circle':
        drawCircle(ctx, element);
        break;
      case 'text':
        drawText(ctx, element);
        break;
      case 'line':
        drawLine(ctx, element);
        break;
      case 'product_table':
        drawProductTable(ctx, element);
        break;
      case 'customer_info':
        drawCustomerInfo(ctx, element);
        break;
      case 'company_info':
        drawCompanyInfo(ctx, element);
        break;
      case 'company_logo':
        drawCompanyLogo(ctx, element);
        break;
      case 'order_number':
        drawOrderNumber(ctx, element);
        break;
      case 'document_type':
        drawDocumentType(ctx, element);
        break;
      case 'dynamic-text':
        drawDynamicText(ctx, element);
        break;
      case 'mentions':
        drawMentions(ctx, element);
        break;
      default:
        // Élément générique - dessiner un rectangle simple
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, element.width, element.height);
    }

    ctx.restore();
  };

  // Fonctions de dessin spécifiques
  const drawRectangle = (ctx: CanvasRenderingContext2D, element: Element) => {
    const fillColor = (element as any).fillColor || '#ffffff';
    const strokeColor = (element as any).strokeColor || '#000000';
    const strokeWidth = (element as any).strokeWidth || 1;
    const borderRadius = (element as any).borderRadius || 0;

    ctx.fillStyle = fillColor;
    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;

    if (borderRadius > 0) {
      roundedRect(ctx, 0, 0, element.width, element.height, borderRadius);
      ctx.fill();
      ctx.stroke();
    } else {
      ctx.fillRect(0, 0, element.width, element.height);
      ctx.strokeRect(0, 0, element.width, element.height);
    }
  };

  const drawCircle = (ctx: CanvasRenderingContext2D, element: Element) => {
    const fillColor = (element as any).fillColor || '#ffffff';
    const strokeColor = (element as any).strokeColor || '#000000';
    const strokeWidth = (element as any).strokeWidth || 1;

    const centerX = element.width / 2;
    const centerY = element.height / 2;
    const radius = Math.min(centerX, centerY);

    ctx.fillStyle = fillColor;
    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;

    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.fill();
    ctx.stroke();
  };

  const drawText = (ctx: CanvasRenderingContext2D, element: Element) => {
    const text = (element as any).text || 'Text';
    const fontSize = (element as any).fontSize || 16;
    const color = (element as any).color || '#000000';
    const align = (element as any).align || 'left';

    ctx.fillStyle = color;
    ctx.font = `${fontSize}px Arial`;
    ctx.textAlign = align as CanvasTextAlign;

    ctx.fillText(text, 0, fontSize);
  };

  const drawLine = (ctx: CanvasRenderingContext2D, element: Element) => {
    const strokeColor = (element as any).strokeColor || '#000000';
    const strokeWidth = (element as any).strokeWidth || 2;

    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;

    ctx.beginPath();
    ctx.moveTo(0, element.height / 2); // Centre verticalement
    ctx.lineTo(element.width, element.height / 2); // Ligne horizontale droite
    ctx.stroke();
  };

  // Fonction utilitaire pour rectangle arrondi
  const roundedRect = (ctx: CanvasRenderingContext2D, x: number, y: number, width: number, height: number, radius: number) => {
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.lineTo(x + width - radius, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    ctx.lineTo(x + width, y + height - radius);
    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    ctx.lineTo(x + radius, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    ctx.lineTo(x, y + radius);
    ctx.quadraticCurveTo(x, y, x + radius, y);
    ctx.closePath();
  };

  // Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
  const drawProductTable = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const showHeaders = props.showHeaders !== false;
    const showBorders = props.showBorders !== false;
    const showAlternatingRows = props.showAlternatingRows !== false;
    const fontSize = props.fontSize || 11;
    const showSku = props.showSku !== false;
    const showDescription = props.showDescription !== false;
    const showQuantity = props.showQuantity !== false;
    const showShipping = props.showShipping !== false;
    const showTax = props.showTax !== false;
    const showGlobalDiscount = props.showGlobalDiscount !== false;
    const textColor = props.textColor || '#000000';
    const borderRadius = props.borderRadius || 0;

    let products: Array<{
      sku: string;
      name: string;
      description: string;
      qty: number;
      price: number;
      discount: number;
      total: number;
    }>;
    let shippingCost: number;
    let taxRate: number;
    let globalDiscount: number;
    let orderFees: number;
    let currency: string;

    // Utiliser les données WooCommerce si en mode commande, sinon données fictives
    if (state.previewMode === 'command' && wooCommerceManager.getOrderData()) {
      const orderData = wooCommerceManager.getOrderData()!;
      const orderItems = wooCommerceManager.getOrderItems();
      const orderTotals = wooCommerceManager.getOrderTotals();

      products = orderItems;
      shippingCost = orderTotals.shipping;
      taxRate = orderTotals.tax > 0 ? (orderTotals.tax / orderTotals.subtotal) * 100 : 20;
      globalDiscount = orderTotals.discount;
      orderFees = 0; // Les frais de commande sont déjà inclus dans les items
      currency = orderData.currency;
    } else {
      // Données fictives pour le mode éditeur
      shippingCost = props.shippingCost || 8.50;
      taxRate = props.taxRate || 20;
      globalDiscount = props.globalDiscount || 5;
      orderFees = props.orderFees || 2.50;
      currency = '€';

      products = [
        {
          sku: 'TSHIRT-001',
          name: 'T-shirt Premium Bio',
          description: 'T-shirt en coton biologique, coupe slim',
          qty: 2,
          price: 29.99,
          discount: 0,
          total: 59.98
        },
        {
          sku: 'JEAN-045',
          name: 'Jean Slim Fit Noir',
          description: 'Jean stretch confort, taille haute',
          qty: 1,
          price: 89.99,
          discount: 10.00,
          total: 79.99
        },
        {
          sku: 'SHOES-089',
          name: 'Chaussures Running Pro',
          description: 'Chaussures de running avec semelle amortissante',
          qty: 1,
          price: 129.99,
          discount: 0,
          total: 129.99
        },
        {
          sku: 'HOODIE-112',
          name: 'Sweat à Capuche',
          description: 'Sweat molletonné, capuche ajustable',
          qty: 1,
          price: 49.99,
          discount: 5.00,
          total: 44.99
        }
      ];
    }

    // Calcul du total avec remises (même logique pour données fictives et réelles)
    const subtotal = products.reduce((sum, product) => sum + (product.price * product.qty), 0);
    const itemDiscounts = products.reduce((sum, product) => sum + product.discount, 0);
    const subtotalAfterItemDiscounts = subtotal - itemDiscounts;

    // Sous-total incluant les frais de commande
    const subtotalWithOrderFees = subtotalAfterItemDiscounts + orderFees;

    // Appliquer la remise globale sur le sous-total incluant les frais de commande (seulement si affichée)
    const globalDiscountAmount = (globalDiscount > 0 && showGlobalDiscount) ? (subtotalWithOrderFees * globalDiscount / 100) : 0;
    const subtotalAfterGlobalDiscount = subtotalWithOrderFees - globalDiscountAmount;    // Ajouter les frais de port (seulement si affichés)
    const subtotalWithShipping = subtotalAfterGlobalDiscount + (showShipping ? shippingCost : 0);

    // Calculer les taxes (seulement si affichées)
    const taxAmount = (taxRate > 0 && showTax) ? (subtotalWithShipping * taxRate / 100) : 0;

    // Total final
    const finalTotal = subtotalWithShipping + taxAmount;

    // Configuration des colonnes
    interface TableColumn {
      key: string;
      label: string;
      width: number;
      align: 'left' | 'center' | 'right';
      x: number;
    }

    const columns: TableColumn[] = [];
    columns.push({ key: 'name', label: 'Produit', width: showSku && showDescription ? 0.35 : showSku || showDescription ? 0.45 : 0.55, align: 'left', x: 0 });
    if (showSku) columns.push({ key: 'sku', label: 'SKU', width: 0.15, align: 'left', x: 0 });
    if (showDescription) columns.push({ key: 'description', label: 'Description', width: 0.25, align: 'left', x: 0 });
    if (showQuantity) columns.push({ key: 'qty', label: 'Qté', width: 0.08, align: 'center', x: 0 });
    columns.push({ key: 'price', label: 'Prix', width: 0.12, align: 'right', x: 0 });
    columns.push({ key: 'total', label: 'Total', width: 0.12, align: 'right', x: 0 });

    // Normaliser les largeurs
    const totalWidth = columns.reduce((sum, col) => sum + col.width, 0);
    columns.forEach(col => col.width = col.width / totalWidth);

    // Calcul des positions X des colonnes
    let currentX = 8;
    columns.forEach(col => {
      col.x = currentX;
      currentX += col.width * (element.width - 16);
    });

    // Fond
    ctx.fillStyle = props.backgroundColor || '#ffffff';
    ctx.fillRect(0, 0, element.width, element.height);

    // Bordure extérieure
    if (showBorders) {
      ctx.strokeStyle = props.borderColor || '#d1d5db';
      ctx.lineWidth = props.borderWidth || 1;
      if (borderRadius > 0) {
        roundedRect(ctx, 0, 0, element.width, element.height, borderRadius);
        ctx.stroke();
      } else {
        ctx.strokeRect(0, 0, element.width, element.height);
      }
    }

    ctx.textAlign = 'left';
    let currentY = showHeaders ? 25 : 15;

    // En-têtes avec style professionnel
    if (showHeaders) {
      ctx.fillStyle = props.headerBackgroundColor || '#f9fafb';
      // Utiliser roundedRect si borderRadius > 0, sinon fillRect normal
      if (borderRadius > 0) {
        roundedRect(ctx, 1, 1, element.width - 2, 32, borderRadius);
        ctx.fill();
      } else {
        ctx.fillRect(1, 1, element.width - 2, 32);
      }

      ctx.fillStyle = props.headerTextColor || '#374151';
      ctx.font = `bold ${fontSize + 1}px Arial`;
      ctx.textBaseline = 'top';

      columns.forEach(col => {
        ctx.textAlign = col.align as CanvasTextAlign;
        const textX = col.align === 'right' ? col.x + col.width * (element.width - 16) - 4 :
                     col.align === 'center' ? col.x + (col.width * (element.width - 16)) / 2 :
                     col.x;
        ctx.fillText(col.label, textX, 10); // Ajusté pour centrer dans la hauteur plus grande
      });

      // Ligne de séparation sous les en-têtes
      ctx.strokeStyle = '#e5e7eb';
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.moveTo(4, 34); // Ajusté pour la nouvelle hauteur
      ctx.lineTo(element.width - 4, 34);
      ctx.stroke();

      currentY = 42; // Ajusté pour la nouvelle hauteur d'entête
    } else {
      currentY = 15;
    }

    // Calcul de la hauteur uniforme des lignes (augmentée)
    const rowHeight = showDescription ? 50 : 35;

    // Produits avec alternance de couleurs
    ctx.font = `${fontSize}px Arial`;
    ctx.textBaseline = 'middle';

    products.forEach((product, index) => {
      // Calcul de la position Y absolue pour cette ligne
      const rowY = currentY + index * (rowHeight + 4);

      // Fond alterné pour les lignes (sans bordures)
      if (showAlternatingRows && index % 2 === 1) {
        ctx.fillStyle = props.alternateRowColor || '#f9fafb';
        // Utiliser roundedRect si borderRadius > 0
        if (borderRadius > 0) {
          roundedRect(ctx, 1, rowY, element.width - 2, rowHeight, borderRadius);
          ctx.fill();
        } else {
          ctx.fillRect(1, rowY, element.width - 2, rowHeight);
        }
      }

      ctx.fillStyle = textColor; // Utiliser la couleur du texte depuis les propriétés

      columns.forEach(col => {
        ctx.textAlign = col.align as CanvasTextAlign;
        const textX = col.align === 'right' ? col.x + col.width * (element.width - 16) - 4 :
                     col.align === 'center' ? col.x + (col.width * (element.width - 16)) / 2 :
                     col.x;

        let text = '';
        switch (col.key) {
          case 'name': text = product.name; break;
          case 'sku': text = product.sku; break;
          case 'description': text = product.description; break;
          case 'qty': text = product.qty.toString(); break;
          case 'price': text = `${product.price.toFixed(2)}${currency}`; break;
          case 'discount': text = product.discount > 0 ? `${product.discount.toFixed(2)}${currency}` : '-'; break;
          case 'total': text = `${product.total.toFixed(2)}${currency}`; break;
        }

        // Gestion du texte qui dépasse
        const maxWidth = col.width * (element.width - 16) - 8;
        if (ctx.measureText(text).width > maxWidth && col.key === 'name') {
          // Tronquer avec "..."
          let truncated = text;
          while (ctx.measureText(truncated + '...').width > maxWidth && truncated.length > 0) {
            truncated = truncated.slice(0, -1);
          }
          text = truncated + '...';
        }

        ctx.fillText(text, textX, rowY + rowHeight / 2);
      });

    });

    // Positionnement pour la section des totaux (après toutes les lignes de produits)
    currentY = 55 + products.length * (rowHeight + 4) + 8;

    // Section des totaux

    // Ligne de séparation avant les totaux
    ctx.strokeStyle = '#d1d5db';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(element.width - 200, currentY);
    ctx.lineTo(element.width - 8, currentY);
    ctx.stroke();

    currentY += 20;

    // Affichage des totaux
    ctx.font = `bold ${fontSize}px Arial`;
    ctx.fillStyle = textColor; // Utiliser la couleur du texte
    ctx.textAlign = 'left';

    const totalsY = currentY;
    ctx.fillText('Sous-total:', element.width - 200, totalsY);
    ctx.textAlign = 'right';
    ctx.fillText(`${subtotalWithOrderFees.toFixed(2)}${currency}`, element.width - 8, totalsY);

    currentY += 18;

    // Remises combinées (articles + globale) - proviennent de coupons WooCommerce
    const totalDiscounts = itemDiscounts + (showGlobalDiscount ? globalDiscountAmount : 0);
    if (totalDiscounts > 0) {
      ctx.textAlign = 'left';
      ctx.fillStyle = '#059669'; // Garder le vert pour la remise (couleur spéciale)
      ctx.fillText('Coupon:', element.width - 200, currentY);
      ctx.textAlign = 'right';
      ctx.fillText(`-${totalDiscounts.toFixed(2)}${currency}`, element.width - 8, currentY);
      currentY += 18;
    }

    // Frais de port
    if (shippingCost > 0 && showShipping) {
      ctx.textAlign = 'left';
      ctx.fillStyle = textColor; // Utiliser la couleur du texte
      ctx.fillText('Frais de port:', element.width - 200, currentY);
      ctx.textAlign = 'right';
      ctx.fillText(`${shippingCost.toFixed(2)}${currency}`, element.width - 8, currentY);
      currentY += 18;
    }

    // Taxes
    if (taxAmount > 0 && showTax) {
      ctx.textAlign = 'left';
      ctx.fillStyle = textColor; // Utiliser la couleur du texte
      ctx.fillText(`TVA (${taxRate}%):`, element.width - 200, currentY);
      ctx.textAlign = 'right';
      ctx.fillText(`${taxAmount.toFixed(2)}${currency}`, element.width - 8, currentY);
      currentY += 18;
    }

    currentY += 8; // Plus d'espace avant la ligne de séparation du total
    ctx.strokeStyle = textColor; // Utiliser la couleur du texte pour la ligne
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(element.width - 200, currentY - 5);
    ctx.lineTo(element.width - 8, currentY - 5);
    ctx.stroke();

    currentY += 8; // Plus d'espace après la ligne de séparation
    ctx.font = `bold ${fontSize + 2}px Arial`;
    ctx.fillStyle = textColor; // Utiliser la couleur du texte pour le total
    ctx.textAlign = 'left';
    ctx.fillText('TOTAL:', element.width - 200, currentY);
    ctx.textAlign = 'right';
    ctx.fillText(`${finalTotal.toFixed(2)}${currency}`, element.width - 8, currentY);
  };

  const drawCustomerInfo = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 12;
    const layout = props.layout || 'vertical';
    const showHeaders = props.showHeaders !== false;
    const showBorders = props.showBorders !== false;
    const showFullName = props.showFullName !== false;
    const showAddress = props.showAddress !== false;
    const showEmail = props.showEmail !== false;
    const showPhone = props.showPhone !== false;

    // Fond
    ctx.fillStyle = props.backgroundColor || '#ffffff';
    ctx.fillRect(0, 0, element.width, element.height);

    // Bordures
    if (showBorders) {
      ctx.strokeStyle = props.borderColor || '#e5e7eb';
      ctx.lineWidth = 1;
      ctx.strokeRect(0, 0, element.width, element.height);
    }

    ctx.fillStyle = props.textColor || '#000000';
    ctx.font = `bold ${fontSize + 2}px Arial`;
    ctx.textAlign = 'left';

    let y = showHeaders ? 25 : 15;

    // En-tête
    if (showHeaders) {
      ctx.fillStyle = props.headerTextColor || '#111827';
      ctx.fillText('Informations Client', 10, y);
      y += 20;
      ctx.fillStyle = props.textColor || '#000000';
    }

    // Informations client fictives ou réelles selon le mode
    let customerData: {
      name: string;
      address: string;
      email: string;
      phone: string;
    };

    if (state.previewMode === 'command') {
      customerData = wooCommerceManager.getCustomerInfo();
    } else {
      // Données fictives pour le mode éditeur
      customerData = {
        name: 'Marie Dupont',
        address: '15 rue des Lilas, 75001 Paris',
        email: 'marie.dupont@email.com',
        phone: '+33 6 12 34 56 78'
      };
    }

    ctx.font = `${fontSize}px Arial`;

    if (layout === 'vertical') {
      if (showFullName) {
        ctx.fillText(customerData.name, 10, y);
        y += 18;
      }
      if (showAddress) {
        ctx.fillText(customerData.address, 10, y);
        y += 18;
      }
      if (showEmail) {
        ctx.fillText(customerData.email, 10, y);
        y += 18;
      }
      if (showPhone) {
        ctx.fillText(customerData.phone, 10, y);
      }
    } else if (layout === 'horizontal') {
      let text = '';
      if (showFullName) text += customerData.name;
      if (showEmail) text += (text ? ' - ' : '') + customerData.email;
      if (text) ctx.fillText(text, 10, y);

      if (showPhone) {
        ctx.fillText(customerData.phone, element.width - ctx.measureText(customerData.phone).width - 10, y);
      }
    } else if (layout === 'compact') {
      let compactText = '';
      if (showFullName) compactText += customerData.name;
      if (showAddress) compactText += (compactText ? ' • ' : '') + customerData.address.split(',')[0];
      if (showEmail) compactText += (compactText ? ' • ' : '') + customerData.email;
      if (showPhone) compactText += (compactText ? ' • ' : '') + customerData.phone;

      // Wrap text if too long
      const maxWidth = element.width - 20;
      const words = compactText.split(' ');
      let line = '';
      let compactY = y;

      for (let i = 0; i < words.length; i++) {
        const testLine = line + words[i] + ' ';
        const metrics = ctx.measureText(testLine);
        if (metrics.width > maxWidth && i > 0) {
          ctx.fillText(line, 10, compactY);
          line = words[i] + ' ';
          compactY += 16;
        } else {
          line = testLine;
        }
      }
      ctx.fillText(line, 10, compactY);
    }
  };

  const drawCompanyInfo = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 12;
    const textAlign = props.textAlign || 'left';
    const theme = (props.theme || 'corporate') as keyof typeof themes;
    const showHeaders = props.showHeaders !== false; // Par défaut true
    const showBorders = props.showBorders !== false; // Par défaut true
    const showCompanyName = props.showCompanyName !== false; // Par défaut true
    const showAddress = props.showAddress !== false; // Par défaut true
    const showPhone = props.showPhone !== false; // Par défaut true
    const showEmail = props.showEmail !== false; // Par défaut true
    const showSiret = props.showSiret !== false; // Par défaut true

    // Définition des thèmes
    const themes = {
      corporate: {
        backgroundColor: '#ffffff',
        borderColor: '#1f2937',
        textColor: '#374151',
        headerTextColor: '#111827'
      },
      modern: {
        backgroundColor: '#ffffff',
        borderColor: '#3b82f6',
        textColor: '#1e40af',
        headerTextColor: '#1e3a8a'
      },
      elegant: {
        backgroundColor: '#ffffff',
        borderColor: '#8b5cf6',
        textColor: '#6d28d9',
        headerTextColor: '#581c87'
      },
      minimal: {
        backgroundColor: '#ffffff',
        borderColor: '#e5e7eb',
        textColor: '#374151',
        headerTextColor: '#111827'
      },
      professional: {
        backgroundColor: '#ffffff',
        borderColor: '#059669',
        textColor: '#047857',
        headerTextColor: '#064e3b'
      }
    };

    const currentTheme = themes[theme] || themes.corporate;

    ctx.fillStyle = currentTheme.backgroundColor;
    ctx.fillRect(0, 0, element.width, element.height);

    // Appliquer les bordures si demandé
    if (showBorders) {
      ctx.strokeStyle = currentTheme.borderColor;
      ctx.lineWidth = 1;
      ctx.strokeRect(0, 0, element.width, element.height);
    }

    ctx.fillStyle = currentTheme.textColor;
    ctx.textAlign = textAlign as CanvasTextAlign;

    let y = 20;

    // Informations entreprise hybrides : props configurables + valeurs par défaut
    const companyData = {
      name: props.companyName || 'Ma Boutique En Ligne',
      address: props.companyAddress || '25 avenue des Commerçants',
      city: props.companyCity || '69000 Lyon',
      siret: props.companySiret || 'SIRET: 123 456 789 00012',
      tva: props.companyTva || 'TVA: FR 12 345 678 901',
      email: props.companyEmail || 'contact@maboutique.com',
      phone: props.companyPhone || '+33 4 12 34 56 78'
    };

    // Calcul de la position X selon l'alignement
    let x = 10;
    if (textAlign === 'center') {
      x = element.width / 2;
    } else if (textAlign === 'right') {
      x = element.width - 10;
    }

    // Afficher le nom de l'entreprise si demandé
    if (showCompanyName) {
      ctx.fillStyle = currentTheme.headerTextColor;
      ctx.font = `bold ${fontSize + 2}px Arial`;
      ctx.fillText(companyData.name, x, y);
      y += 18;
      ctx.fillStyle = currentTheme.textColor;
    }

    // Afficher l'adresse si demandée
    if (showAddress) {
      ctx.font = `${fontSize}px Arial`;
      ctx.fillText(companyData.address, x, y);
      y += 15;
      ctx.fillText(companyData.city, x, y);
      y += 18;
    }

    // Afficher le SIRET si demandé
    if (showSiret) {
      ctx.fillText(companyData.siret, x, y);
      y += 15;
    }

    // Afficher la TVA (toujours affichée pour le moment)
    ctx.fillText(companyData.tva, x, y);
    y += 15;

    // Afficher l'email si demandé
    if (showEmail) {
      ctx.fillText(companyData.email, x, y);
      y += 15;
    }

    // Afficher le téléphone si demandé
    if (showPhone) {
      ctx.fillText(companyData.phone, x, y);
    }
  };

  const drawCompanyLogo = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const logoUrl = props.logoUrl || '';
    const fit = props.fit || 'contain';
    const alignment = props.alignment || 'left';

    // Fond transparent
    ctx.fillStyle = 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    if (logoUrl) {
      // Vérifier si l'image est en cache
      let img = imageCache.current.get(logoUrl);

      if (!img) {
        // Créer une nouvelle image et la mettre en cache
        img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = logoUrl;
        imageCache.current.set(logoUrl, img);
      }

      // Si l'image est chargée, la dessiner
      if (img.complete && img.naturalHeight !== 0) {
        // Calculer les dimensions et position
        let logoWidth = element.width - 20;
        let logoHeight = element.height - 20;

        // Respecter les proportions si demandé
        if (props.maintainAspectRatio !== false) {
          const aspectRatio = img.naturalWidth / img.naturalHeight;
          if (logoWidth / logoHeight > aspectRatio) {
            logoWidth = logoHeight * aspectRatio;
          } else {
            logoHeight = logoWidth / aspectRatio;
          }
        }

        let x = 10;
        if (alignment === 'center') {
          x = (element.width - logoWidth) / 2;
        } else if (alignment === 'right') {
          x = element.width - logoWidth - 10;
        }

        const y = (element.height - logoHeight) / 2;

        // Dessiner l'image
        ctx.drawImage(img, x, y, logoWidth, logoHeight);
      } else {
        // Image en cours de chargement ou erreur, dessiner un placeholder
        drawLogoPlaceholder(ctx, element, alignment, img.complete ? 'Erreur' : 'Chargement...');
      }
    } else {
      // Pas d'URL, dessiner un placeholder
      drawLogoPlaceholder(ctx, element, alignment, 'LOGO');
    }
  };

  // Fonction helper pour dessiner un placeholder de logo
  const drawLogoPlaceholder = (ctx: CanvasRenderingContext2D, element: Element, alignment: string, text: string) => {
    const logoWidth = Math.min(element.width - 20, 120);
    const logoHeight = Math.min(element.height - 20, 60);

    let x = 10;
    if (alignment === 'center') {
      x = (element.width - logoWidth) / 2;
    } else if (alignment === 'right') {
      x = element.width - logoWidth - 10;
    }

    const y = (element.height - logoHeight) / 2;

    // Rectangle du logo
    ctx.fillStyle = '#f0f0f0';
    ctx.strokeStyle = '#ccc';
    ctx.lineWidth = 1;
    ctx.fillRect(x, y, logoWidth, logoHeight);
    ctx.strokeRect(x, y, logoWidth, logoHeight);

    // Texte du placeholder
    ctx.fillStyle = '#666';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(text, x + logoWidth / 2, y + logoHeight / 2 + 4);
  };

  const drawOrderNumber = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 14;
    const textAlign = props.textAlign || 'left'; // left, center, right
    const showLabel = props.showLabel !== false; // Par défaut true
    const showDate = props.showDate !== false; // Par défaut true
    const labelPosition = props.labelPosition || 'above'; // above, left, right, below
    const numberPosition = props.numberPosition || 'inline'; // inline, below

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = '#000000';
    ctx.font = `bold ${fontSize}px Arial`;

    // Numéro de commande et date fictifs ou réels selon le mode
    let orderNumber: string;
    let orderDate: string;

    if (state.previewMode === 'command') {
      orderNumber = wooCommerceManager.getOrderNumber();
      orderDate = wooCommerceManager.getOrderDate();
    } else {
      // Données fictives pour le mode éditeur
      orderNumber = 'CMD-2024-01234';
      orderDate = '27/10/2024';
    }

    // Calcul de la position X selon l'alignement
    let x: number;
    if (textAlign === 'left') {
      x = 10;
    } else if (textAlign === 'center') {
      x = element.width / 2;
    } else { // right
      x = element.width - 10;
    }

    let y = 20;

    // Afficher selon la position du libellé et du numéro
    if (showLabel) {
      if (labelPosition === 'above') {
        // Libellé au-dessus, numéro en-dessous
        ctx.textAlign = textAlign as CanvasTextAlign;
        ctx.fillText('N° de commande:', x, y);
        y += 18;
        ctx.fillText(orderNumber, x, y);
      } else if (labelPosition === 'below') {
        // Numéro au-dessus, libellé en-dessous
        ctx.textAlign = textAlign as CanvasTextAlign;
        ctx.fillText(orderNumber, x, y);
        y += 18;
        ctx.fillText('N° de commande:', x, y);
      } else if (labelPosition === 'left') {
        // Libellé à gauche, numéro à droite (centré)
        ctx.textAlign = 'left';
        const labelX = 10;
        const numberX = element.width / 2;
        ctx.fillText('N° de commande:', labelX, y);
        ctx.fillText(orderNumber, numberX, y);
      } else if (labelPosition === 'right') {
        // Numéro à gauche, libellé à droite (centré)
        ctx.textAlign = 'left';
        const numberX = 10;
        const labelX = element.width / 2;
        ctx.fillText(orderNumber, numberX, y);
        ctx.fillText('N° de commande:', labelX, y);
      }
    } else {
      // Pas de libellé, juste le numéro
      ctx.textAlign = textAlign as CanvasTextAlign;
      ctx.fillText(orderNumber, x, y);
    }

    // Afficher la date sur une nouvelle ligne avec le même alignement (si activé)
    if (showDate) {
      ctx.font = `${fontSize - 2}px Arial`;
      ctx.textAlign = textAlign as CanvasTextAlign;
      ctx.fillText(`Date: ${orderDate}`, x, y + 20);
    }
  };

  const drawDynamicText = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const template = props.template || 'Commande #{order_number}';
    const fontSize = props.fontSize || 14;

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = '#000000';
    ctx.font = `${fontSize}px Arial`;
    ctx.textAlign = 'left';

    // Remplacer les variables par des valeurs fictives ou réelles selon le mode
    let orderNumber: string;
    let customerName: string;
    let orderDate: string;
    let total: string;

    if (state.previewMode === 'command') {
      const orderData = wooCommerceManager.getOrderData();
      const customerInfo = wooCommerceManager.getCustomerInfo();
      const orderTotals = wooCommerceManager.getOrderTotals();

      orderNumber = wooCommerceManager.getOrderNumber();
      customerName = customerInfo.name;
      orderDate = wooCommerceManager.getOrderDate();
      total = `${orderTotals.total.toFixed(2)}${orderTotals.currency}`;
    } else {
      // Données fictives pour le mode éditeur
      orderNumber = 'CMD-2024-01234';
      customerName = 'Marie Dupont';
      orderDate = '27/10/2024';
      total = '279.96€';
    }

    const processedText = template
      .replace('#{order_number}', orderNumber)
      .replace('#{customer_name}', customerName)
      .replace('#{order_date}', orderDate)
      .replace('#{total}', total);

    ctx.fillText(processedText, 10, 25);
  };

  const drawMentions = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 10;
    const textAlign = props.textAlign || 'left';
    const text = props.text || 'SARL au capital de 10 000€ - RCS Lyon 123 456 789\nTVA FR 12 345 678 901 - SIRET 123 456 789 00012\ncontact@maboutique.com - +33 4 12 34 56 78';

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = props.textColor || '#666666';
    ctx.font = `${fontSize}px Arial`;
    ctx.textAlign = textAlign as CanvasTextAlign;

    const mentions = text.split('\n');
    let y = 15;
    mentions.forEach((mention: string) => {
      const x = textAlign === 'center' ? element.width / 2 : textAlign === 'right' ? element.width - 10 : 10;
      ctx.fillText(mention, x, y);
      y += fontSize + 2;
    });
  };

  const drawDocumentType = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 18;
    const fontWeight = props.fontWeight || 'bold';
    const textAlign = props.textAlign || 'left';
    const textColor = props.textColor || '#000000';

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = textColor;
    ctx.font = `${fontWeight} ${fontSize}px Arial`;
    ctx.textAlign = textAlign as CanvasTextAlign;

    // Type de document fictif ou réel selon le mode
    let documentType: string;

    if (state.previewMode === 'command') {
      // En mode commande réel, on pourrait récupérer le type depuis WooCommerce
      // Pour l'instant, on utilise la valeur configurée ou une valeur par défaut
      documentType = props.documentType || 'FACTURE';
    } else {
      // Données fictives pour le mode éditeur
      documentType = props.documentType || 'FACTURE';
    }

    // Convertir les valeurs techniques en texte lisible
    const documentTypeLabels: { [key: string]: string } = {
      'FACTURE': 'FACTURE',
      'DEVIS': 'DEVIS',
      'BON_COMMANDE': 'BON DE COMMANDE',
      'AVOIR': 'AVOIR',
      'RELEVE': 'RELEVE',
      'CONTRAT': 'CONTRAT'
    };

    documentType = documentTypeLabels[documentType] || documentType;

    const x = textAlign === 'center' ? element.width / 2 : textAlign === 'right' ? element.width - 10 : 10;
    const y = element.height / 2 + fontSize / 3; // Centrer verticalement

    ctx.fillText(documentType, x, y);
  };

  // Fonction pour dessiner la sélection
  const drawSelection = (ctx: CanvasRenderingContext2D, selectedIds: string[], elements: Element[]) => {
    const selectedElements = elements.filter(el => selectedIds.includes(el.id));
    if (selectedElements.length === 0) return;

    // Calculer les bounds de sélection
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

    selectedElements.forEach(el => {
      minX = Math.min(minX, el.x);
      minY = Math.min(minY, el.y);
      maxX = Math.max(maxX, el.x + el.width);
      maxY = Math.max(maxY, el.y + el.height);
    });

    // Dessiner le rectangle de sélection
    ctx.strokeStyle = '#007acc';
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    ctx.strokeRect(minX - 5, minY - 5, (maxX - minX) + 10, (maxY - minY) + 10);
    ctx.setLineDash([]);
  };

  // Redessiner quand l'état change
  useEffect(() => {
    renderCanvas();
  }, [renderCanvas]);

  return (
    <canvas
      ref={canvasRef}
      width={width}
      height={height}
      className={className}
      onClick={handleCanvasClick}
      onMouseDown={handleMouseDown}
      onMouseMove={handleMouseMove}
      onMouseUp={handleMouseUp}
      onDrop={handleDrop}
      onDragOver={handleDragOver}
      style={{
        border: '1px solid #ccc',
        cursor: 'crosshair',
        backgroundColor: '#ffffff'
      }}
    />
  );
}