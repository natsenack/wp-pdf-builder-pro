import React, { useRef, useEffect, useCallback, memo } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { useCanvasSettings } from '../../contexts/CanvasSettingsContext.tsx';
import { useCanvasDrop } from '../../hooks/useCanvasDrop.ts';
import { useCanvasInteraction } from '../../hooks/useCanvasInteraction.ts';
import { Element, ShapeElementProperties, TextElementProperties, LineElementProperties, ProductTableElementProperties, CustomerInfoElementProperties, CompanyInfoElementProperties, ImageElementProperties, OrderNumberElementProperties, MentionsElementProperties, DocumentTypeElementProperties, BuilderState } from '../../types/elements';
import { wooCommerceManager } from '../../utils/WooCommerceElementsManager';
import { elementChangeTracker } from '../../utils/ElementChangeTracker';
import { CanvasMonitoringDashboard } from '../../utils/CanvasMonitoringDashboard';
import { ContextMenu, ContextMenuItem } from '../ui/ContextMenu.tsx';

// Fonctions utilitaires de dessin (déplacées en dehors du composant pour éviter les avertissements React Compiler)

// Fonction helper pour normaliser les couleurs
const normalizeColor = (color: string): string => {
  if (!color || color === 'transparent') {
    return 'rgba(0,0,0,0)'; // Transparent
  }
  return color;
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

// Fonctions de dessin pour les éléments
const drawRectangle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as ShapeElementProperties;
  const fillColor = normalizeColor(props.fillColor || '#ffffff');
  const strokeColor = normalizeColor(props.strokeColor || '#000000');
  const strokeWidth = props.strokeWidth || 1;
  const borderRadius = props.borderRadius || 0;

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
  const props = element as ShapeElementProperties;
  const fillColor = normalizeColor(props.fillColor || '#ffffff');
  const strokeColor = normalizeColor(props.strokeColor || '#000000');
  const strokeWidth = props.strokeWidth || 1;

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
  const props = element as TextElementProperties;
  const text = props.text || 'Text';
  const fontSize = props.fontSize || 16;
  const color = normalizeColor(props.color || '#000000');
  const align = props.align || 'left';

  ctx.fillStyle = color;
  ctx.font = `${fontSize}px Arial`;
  ctx.textAlign = align as CanvasTextAlign;

  const x = align === 'center' ? element.width / 2 : align === 'right' ? element.width : 0;
  ctx.fillText(text, x, fontSize);
};

const drawLine = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as LineElementProperties;
  const strokeColor = normalizeColor(props.strokeColor || '#000000');
  const strokeWidth = props.strokeWidth || 2;

  ctx.strokeStyle = strokeColor;
  ctx.lineWidth = strokeWidth;

  ctx.beginPath();
  ctx.moveTo(0, element.height / 2); // Centre verticalement
  ctx.lineTo(element.width, element.height / 2); // Ligne horizontale droite
  ctx.stroke();
};

// Fonction pour dessiner une image
const drawImage = (ctx: CanvasRenderingContext2D, element: Element, imageCache: React.MutableRefObject<Map<string, HTMLImageElement>>) => {
  const props = element as Element & { src?: string; objectFit?: string };
  const imageUrl = props.src || '';

  if (!imageUrl) {
    // Pas d'URL, dessiner un placeholder
    ctx.fillStyle = '#f0f0f0';
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.strokeStyle = '#cccccc';
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, element.width, element.height);
    ctx.fillStyle = '#999999';
    ctx.font = '14px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('Image', element.width / 2, element.height / 2);
    return;
  }

  // Vérifier si l'image est en cache
  let img = imageCache.current.get(imageUrl);

  if (!img) {
    // Créer une nouvelle image et la mettre en cache
    img = document.createElement('img');
    img.crossOrigin = 'anonymous';
    img.src = imageUrl;
    imageCache.current.set(imageUrl, img);
  }

  // Si l'image est chargée, la dessiner
  if (img.complete && img.naturalHeight !== 0) {
    // Appliquer object-fit
    const objectFit = props.objectFit || 'cover';
    let drawX = 0, drawY = 0, drawWidth = element.width, drawHeight = element.height;
    let sourceX = 0, sourceY = 0, sourceWidth = img.naturalWidth, sourceHeight = img.naturalHeight;

    if (objectFit === 'contain') {
      const ratio = Math.min(element.width / img.naturalWidth, element.height / img.naturalHeight);
      drawWidth = img.naturalWidth * ratio;
      drawHeight = img.naturalHeight * ratio;
      drawX = (element.width - drawWidth) / 2;
      drawY = (element.height - drawHeight) / 2;
    } else if (objectFit === 'cover') {
      const ratio = Math.max(element.width / img.naturalWidth, element.height / img.naturalHeight);
      sourceWidth = element.width / ratio;
      sourceHeight = element.height / ratio;
      sourceX = (img.naturalWidth - sourceWidth) / 2;
      sourceY = (img.naturalHeight - sourceHeight) / 2;
    } else if (objectFit === 'fill') {
      // Utiliser les dimensions de l'élément directement
    } else if (objectFit === 'scale-down') {
      if (img.naturalWidth > element.width || img.naturalHeight > element.height) {
        const ratio = Math.min(element.width / img.naturalWidth, element.height / img.naturalHeight);
        drawWidth = img.naturalWidth * ratio;
        drawHeight = img.naturalHeight * ratio;
        drawX = (element.width - drawWidth) / 2;
        drawY = (element.height - drawHeight) / 2;
      }
    }

    ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, drawX, drawY, drawWidth, drawHeight);
  } else {
    // Image en cours de chargement ou erreur, dessiner un placeholder
    ctx.fillStyle = '#e0e0e0';
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.strokeStyle = '#999999';
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, element.width, element.height);
    ctx.fillStyle = '#666666';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(img.complete ? 'Erreur' : 'Chargement...', element.width / 2, element.height / 2);
  }
};

// Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
const drawProductTable = (ctx: CanvasRenderingContext2D, element: Element, state: BuilderState) => {
  const props = element as ProductTableElementProperties;
  
  // ✅ BUGFIX-020: Validate element has minimum size for rendering
  const minWidth = 100;
  const minHeight = 50;
  
  if (element.width < minWidth || element.height < minHeight) {
    // Element too small, draw placeholder
    ctx.fillStyle = '#f0f0f0';
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.fillStyle = '#999999';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Trop petit', element.width / 2, element.height / 2);
    return;
  }
  
  const showHeaders = props.showHeaders !== false;
  const showBorders = props.showBorders !== false;
  const showAlternatingRows = props.showAlternatingRows !== false;
  const fontSize = props.fontSize || 11;
  const fontFamily = props.fontFamily || 'Arial';
  const fontWeight = props.fontWeight || 'normal';
  const fontStyle = props.fontStyle || 'normal';
  const showSku = props.showSku !== false;
  const showDescription = props.showDescription !== false;
  const showQuantity = props.showQuantity !== false;
  const showShipping = props.showShipping !== false;
  const showTax = props.showTax !== false;
  const showGlobalDiscount = props.showGlobalDiscount !== false;
  const textColor = normalizeColor(props.textColor || '#000000');
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
  // ✅ BUGFIX-015: Validate WooCommerceManager access safely
  if (state.previewMode === 'command' && wooCommerceManager?.getOrderData?.()) {
    const orderData = wooCommerceManager.getOrderData();
    if (orderData) {
      const orderItems = wooCommerceManager.getOrderItems?.() || [];
      const orderTotals = wooCommerceManager.getOrderTotals?.() || { shipping: 0, tax: 0, subtotal: 0, discount: 0 };

      products = orderItems;
      shippingCost = orderTotals.shipping;
      taxRate = orderTotals.tax > 0 ? (orderTotals.tax / orderTotals.subtotal) * 100 : 20;
      globalDiscount = orderTotals.discount;
      orderFees = 0; // Les frais de commande sont déjà inclus dans les items
      currency = orderData.currency || '€';
    } else {
      // Fallback if orderData is null despite passing the check - use demo data
      shippingCost = props.shippingCost || 8.50;
      taxRate = props.taxRate || 20;
      globalDiscount = props.globalDiscount || 5;
      orderFees = props.orderFees || 2.50;
      currency = '€';
      
      products = [
        {
          sku: 'DEMO-001',
          name: 'Sample Product',
          description: 'Demo product',
          qty: 1,
          price: 29.99,
          discount: 0,
          total: 29.99
        }
      ];
    }
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
    ctx.font = `${fontStyle} ${fontWeight} ${fontSize + 1}px ${fontFamily}`;
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
  ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
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
  ctx.font = `${fontStyle} bold ${fontSize + 2}px ${fontFamily}`;
  ctx.fillStyle = textColor; // Utiliser la couleur du texte pour le total
  ctx.textAlign = 'left';
  ctx.fillText('TOTAL:', element.width - 200, currentY);
  ctx.textAlign = 'right';
  ctx.fillText(`${finalTotal.toFixed(2)}${currency}`, element.width - 8, currentY);
};

// Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
const drawCustomerInfo = (ctx: CanvasRenderingContext2D, element: Element, state: BuilderState) => {
  const props = element as CustomerInfoElementProperties;
  const fontSize = props.fontSize || 12;
  const fontFamily = props.fontFamily || 'Arial';
  const fontWeight = props.fontWeight || 'normal';
  const fontStyle = props.fontStyle || 'normal';
  // Propriétés de police pour l'en-tête
  const headerFontSize = props.headerFontSize || fontSize + 2;
  const headerFontFamily = props.headerFontFamily || fontFamily;
  const headerFontWeight = props.headerFontWeight || fontWeight;
  const headerFontStyle = props.headerFontStyle || fontStyle;
  // Propriétés de police pour le corps du texte
  const bodyFontSize = props.bodyFontSize || fontSize;
  const bodyFontFamily = props.bodyFontFamily || fontFamily;
  const bodyFontWeight = props.bodyFontWeight || fontWeight;
  const bodyFontStyle = props.bodyFontStyle || fontStyle;
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

  ctx.fillStyle = normalizeColor(props.textColor || '#000000');
  ctx.font = `${headerFontStyle} ${headerFontWeight} ${headerFontSize}px ${headerFontFamily}`;
  ctx.textAlign = 'left';

  let y = showHeaders ? 25 : 15;

  // En-tête
  if (showHeaders) {
    ctx.fillStyle = normalizeColor(props.headerTextColor || '#111827');
    ctx.fillText('Informations Client', 10, y);
    y += 20;
    ctx.fillStyle = normalizeColor(props.textColor || '#000000');
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

  ctx.font = `${bodyFontStyle} ${bodyFontWeight} ${bodyFontSize}px ${bodyFontFamily}`;

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
  const props = element as CompanyInfoElementProperties;

  const fontSize = props.fontSize || 12;
  const fontFamily = props.fontFamily || 'Arial';
  const fontWeight = props.fontWeight || 'normal';
  const fontStyle = props.fontStyle || 'normal';
  // Propriétés de police pour l'en-tête (nom de l'entreprise)
  const headerFontSize = props.headerFontSize || Math.round(fontSize * 1.2);
  const headerFontFamily = props.headerFontFamily || fontFamily;
  const headerFontWeight = props.headerFontWeight || 'bold';
  const headerFontStyle = props.headerFontStyle || fontStyle;
  // Propriétés de police pour le corps du texte
  const bodyFontSize = props.bodyFontSize || fontSize;
  const bodyFontFamily = props.bodyFontFamily || fontFamily;
  const bodyFontWeight = props.bodyFontWeight || fontWeight;
  const bodyFontStyle = props.bodyFontStyle || fontStyle;
  const textAlign = 'left'; // Forcer alignement à gauche pour company_info
  const theme = (props.theme || 'corporate') as keyof typeof themes;
  // const showHeaders = props.showHeaders !== false; // Par défaut true
  const showBorders = props.showBorders !== false; // Par défaut true
  const showCompanyName = props.showCompanyName !== false; // Par défaut true
  const showAddress = props.showAddress !== false; // Par défaut true
  const showPhone = props.showPhone !== false; // Par défaut true
  const showEmail = props.showEmail !== false; // Par défaut true
  const showSiret = props.showSiret !== false; // Par défaut true
  const showVat = props.showVat !== false; // Par défaut true

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

  // Utiliser les couleurs personnalisées si définies, sinon utiliser le thème
  const bgColor = normalizeColor(props.backgroundColor || currentTheme.backgroundColor);
  const borderCol = normalizeColor(props.borderColor || currentTheme.borderColor);
  const txtColor = normalizeColor(props.textColor || currentTheme.textColor);
  const headerTxtColor = normalizeColor(props.headerTextColor || currentTheme.headerTextColor);

  ctx.fillStyle = bgColor;
  ctx.fillRect(0, 0, element.width, element.height);

  // Appliquer les bordures si demandé
  if (showBorders) {
    ctx.strokeStyle = borderCol;
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = txtColor;
  ctx.textAlign = textAlign as CanvasTextAlign;

  // Calcul de la position X (toujours aligné à gauche pour company_info)
  let x = 10;

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

  // Afficher le nom de l'entreprise si demandé
  if (showCompanyName) {
    ctx.fillStyle = headerTxtColor;
    ctx.font = `${headerFontStyle} ${headerFontWeight} ${headerFontSize}px ${headerFontFamily}`;
    ctx.fillText(companyData.name, x, y);
    y += Math.round(fontSize * 1.5);
    ctx.fillStyle = txtColor;
  }

  // Police normale pour les autres éléments
  ctx.font = `${bodyFontStyle} ${bodyFontWeight} ${bodyFontSize}px ${bodyFontFamily}`;

  // Afficher l'adresse si demandée
  if (showAddress) {
    ctx.fillText(companyData.address, x, y);
    y += Math.round(fontSize * 1.2);
    ctx.fillText(companyData.city, x, y);
    y += Math.round(fontSize * 1.5);
  }

  // Afficher le SIRET si demandé
  if (showSiret) {
    ctx.fillText(companyData.siret, x, y);
    y += Math.round(fontSize * 1.2);
  }

  // Afficher la TVA si demandée
  if (showVat) {
    ctx.fillText(companyData.tva, x, y);
    y += Math.round(fontSize * 1.2);
  }

  // Afficher l'email si demandé
  if (showEmail) {
    ctx.fillText(companyData.email, x, y);
    y += Math.round(fontSize * 1.2);
  }

  // Afficher le téléphone si demandé
  if (showPhone) {
    ctx.fillText(companyData.phone, x, y);
  }
};

const drawOrderNumber = (ctx: CanvasRenderingContext2D, element: Element, state: BuilderState) => {
  try {
    const props = element as OrderNumberElementProperties;

    const fontSize = props.fontSize || 14;
  const fontFamily = props.fontFamily || 'Arial';
  const fontWeight = props.fontWeight || 'normal';
  const fontStyle = props.fontStyle || 'normal';
  // Propriétés de police pour le label
  const labelFontSize = props.labelFontSize || fontSize;
  const labelFontFamily = props.labelFontFamily || fontFamily;
  const labelFontWeight = props.labelFontWeight || 'bold';
  const labelFontStyle = props.labelFontStyle || fontStyle;
  // Propriétés de police pour le numéro
  const numberFontSize = props.numberFontSize || fontSize;
  const numberFontFamily = props.numberFontFamily || fontFamily;
  const numberFontWeight = props.numberFontWeight || fontWeight;
  const numberFontStyle = props.numberFontStyle || fontStyle;
  // Propriétés de police pour la date
  const dateFontSize = props.dateFontSize || (fontSize - 2);
  const dateFontFamily = props.dateFontFamily || fontFamily;
  const dateFontWeight = props.dateFontWeight || fontWeight;
  const dateFontStyle = props.dateFontStyle || fontStyle;
  // const textAlign = props.textAlign || 'left'; // left, center, right
  // Propriétés d'alignement spécifiques
  // const labelTextAlign = props.labelTextAlign || textAlign;
  // const numberTextAlign = props.numberTextAlign || textAlign;
  // const dateTextAlign = props.dateTextAlign || textAlign;
  const contentAlign = props.contentAlign || 'left'; // Alignement général du contenu dans l'élément
  const showLabel = props.showLabel !== false; // Par défaut true
  const showDate = props.showDate !== false; // Par défaut true
  const labelPosition = props.labelPosition || 'above'; // above, left, right, below
  const labelText = props.labelText || 'N° de commande:'; // Texte personnalisable du libellé

  // Fonction helper pour calculer la position X selon l'alignement général du contenu
  // const calculateContentX = (align: string) => {
  //   if (align === 'left') {
  //     return 10;
  //   } else if (align === 'center') {
  //     return element.width / 2;
  //   } else { // right
  //     return element.width - 10;
  //   }
  // };

  // Fonction helper pour calculer la position X selon l'alignement du texte
  // const calculateX = (align: string) => {
  //   if (align === 'left') {
  //     return 10;
  //   } else if (align === 'center') {
  //     return element.width / 2;
  //   } else { // right
  //     return element.width - 10;
  //   }
  // };

  ctx.fillStyle = props.backgroundColor || 'transparent';
  ctx.fillRect(0, 0, element.width, element.height);

  ctx.fillStyle = '#000000';

  // Numéro de commande et date fictifs ou réels selon le mode
  let orderNumber: string;
  let orderDate: string;

  if (state.previewMode === 'command') {
    orderNumber = wooCommerceManager.getOrderNumber();
    orderDate = wooCommerceManager.getOrderDate();
  } else {
    // Utiliser les données WooCommerce si disponibles, sinon valeurs par défaut
    orderNumber = wooCommerceManager.getOrderNumber() || 'CMD-2024-01234';
    orderDate = wooCommerceManager.getOrderDate() || '27/10/2024';
  }

  let y = 20;

  // Calculer la largeur totale du contenu pour l'alignement général
  let totalContentWidth = 0;
  if (showLabel) {
    if (labelPosition === 'above' || labelPosition === 'below') {
      // Pour les positions verticales, prendre la largeur maximale
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      const labelWidth = ctx.measureText(labelText).width;
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      const numberWidth = ctx.measureText(orderNumber).width;
      totalContentWidth = Math.max(labelWidth, numberWidth);
    } else {
      // Pour les positions latérales, calculer la largeur combinée
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      const labelWidth = ctx.measureText(labelText).width;
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      const numberWidth = ctx.measureText(orderNumber).width;
      totalContentWidth = labelWidth + numberWidth + 15; // 15px d'espace
    }
  } else {
    // Juste le numéro
    ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
    totalContentWidth = ctx.measureText(orderNumber).width;
  }

  // Calculer le décalage pour l'alignement général du contenu
  let contentOffsetX = 0;
  if (contentAlign === 'center') {
    contentOffsetX = (element.width - totalContentWidth) / 2 - 10; // -10 car on commence à 10
  } else if (contentAlign === 'right') {
    contentOffsetX = element.width - totalContentWidth - 20; // -20 pour les marges
  }

  if (showLabel) {
    if (labelPosition === 'above') {
      // Libellé au-dessus, numéro en-dessous - utiliser l'alignement général du contenu
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      ctx.textAlign = contentAlign as CanvasTextAlign;
      const labelX = contentAlign === 'left' ? 10 + contentOffsetX :
                    contentAlign === 'center' ? element.width / 2 :
                    element.width - 10;
      ctx.fillText(labelText, labelX, y);
      y += 18;
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      ctx.textAlign = contentAlign as CanvasTextAlign;
      const numberX = contentAlign === 'left' ? 10 + contentOffsetX :
                     contentAlign === 'center' ? element.width / 2 :
                     element.width - 10;
      ctx.fillText(orderNumber, numberX, y);
    } else if (labelPosition === 'below') {
      // Numéro au-dessus, libellé en-dessous - utiliser l'alignement général du contenu
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      ctx.textAlign = contentAlign as CanvasTextAlign;
      const numberX = contentAlign === 'left' ? 10 + contentOffsetX :
                     contentAlign === 'center' ? element.width / 2 :
                     element.width - 10;
      ctx.fillText(orderNumber, numberX, y);
      y += 18;
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      ctx.textAlign = contentAlign as CanvasTextAlign;
      const labelX = contentAlign === 'left' ? 10 + contentOffsetX :
                    contentAlign === 'center' ? element.width / 2 :
                    element.width - 10;
      ctx.fillText(labelText, labelX, y);
    } else if (labelPosition === 'left') {
      // Libellé à gauche, numéro à droite - avec espacement optimal et alignement général
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      ctx.textAlign = 'left' as CanvasTextAlign;
      const labelX = 10 + contentOffsetX;
      ctx.fillText(labelText, labelX, y);

      // Calculer l'espace disponible pour centrer le numéro ou l'aligner intelligemment
      const labelWidth = ctx.measureText(labelText).width;
      const numberX = labelX + labelWidth + 15; // 15px d'espace après le libellé

      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      ctx.textAlign = 'left' as CanvasTextAlign;
      ctx.fillText(orderNumber, numberX, y);
    } else if (labelPosition === 'right') {
      // Numéro à gauche, libellé à droite - avec espacement optimal et alignement général
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      ctx.textAlign = 'left' as CanvasTextAlign;
      const numberX = 10 + contentOffsetX;
      ctx.fillText(orderNumber, numberX, y);

      // Calculer la position du libellé après le numéro
      const numberWidth = ctx.measureText(orderNumber).width;
      const labelX = numberX + numberWidth + 15; // 15px d'espace après le numéro

      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      ctx.textAlign = 'left' as CanvasTextAlign;
      ctx.fillText(labelText, labelX, y);
    }
  } else {
    // Pas de libellé, juste le numéro avec alignement général du contenu
    ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
    ctx.textAlign = contentAlign as CanvasTextAlign;
    // Pour le cas sans libellé, utiliser directement calculateContentX sans contentOffsetX
    // car contentOffsetX est calculé pour centrer le contenu total, mais ici on n'a que le numéro
    if (contentAlign === 'left') {
      ctx.fillText(orderNumber, 10, y);
    } else if (contentAlign === 'center') {
      ctx.fillText(orderNumber, element.width / 2, y);
    } else { // right
      ctx.fillText(orderNumber, element.width - 10, y);
    }
  }

  // Afficher la date sur une nouvelle ligne avec le même alignement général
  if (showDate) {
    ctx.font = `${dateFontStyle} ${dateFontWeight} ${dateFontSize}px ${dateFontFamily}`;
    ctx.textAlign = contentAlign as CanvasTextAlign;
    // Pour la date, utiliser directement calculateContentX sans contentOffsetX
    // car contentOffsetX est calculé pour centrer le contenu total
    if (contentAlign === 'left') {
      ctx.fillText(`Date: ${orderDate}`, 10, y + 20);
    } else if (contentAlign === 'center') {
      ctx.fillText(`Date: ${orderDate}`, element.width / 2, y + 20);
    } else { // right
      ctx.fillText(`Date: ${orderDate}`, element.width - 10, y + 20);
    }
  }
  } catch {
    // Erreur silencieuse dans drawOrderNumber
  }
};

const drawDocumentType = (ctx: CanvasRenderingContext2D, element: Element, state: BuilderState) => {
  const props = element as DocumentTypeElementProperties;
  const fontSize = props.fontSize || 18;
  const fontFamily = props.fontFamily || 'Arial';
  const fontWeight = props.fontWeight || 'bold';
  const fontStyle = props.fontStyle || 'normal';
  const textAlign = props.textAlign || 'left';
  const textColor = props.textColor || '#000000';

  ctx.fillStyle = props.backgroundColor || 'transparent';
  ctx.fillRect(0, 0, element.width, element.height);

  ctx.fillStyle = textColor;
  ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
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

interface CanvasProps {
  width: number;
  height: number;
  className?: string;
}

// Flag global pour afficher les logs détaillés des éléments (debug)
const DEBUG_DRAW = false;
const DEBUG_VERBOSE = false; // Set to true to see detailed logs
const DEBUG_MONITORING = false; // Set to true to see real-time element tracking logs

// Constantes pour le cache des images
const MAX_CACHE_ITEMS = 100; // Max 100 images in cache

export const Canvas = memo(function Canvas({ width, height, className }: CanvasProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  
  // ✅ Track derniers éléments rendus pour éviter double rendu
  const lastRenderedElementsRef = useRef<string>('');
  const renderCountRef = useRef<number>(0);
  
  const { state, dispatch } = useBuilder();
  const canvasSettings = useCanvasSettings();

  if (DEBUG_DRAW) console.log('🎬 [COMPONENT] Canvas RE-RENDER - Selection:', state.selection.selectedElements, 'Elements:', state.elements.length);

  // État pour le menu contextuel
  const [contextMenu, setContextMenu] = React.useState<{
    isVisible: boolean;
    position: { x: number; y: number };
    elementId?: string;
  }>({
    isVisible: false,
    position: { x: 0, y: 0 }
  });

  // Cache pour les images chargées
  const imageCache = useRef<Map<string, HTMLImageElement>>(new Map());
  
  // ✅ CORRECTION 7: Tracker les URLs rendues pour détecter changements
  const renderedLogoUrlsRef = useRef<Map<string, string>>(new Map()); // elementId -> logoUrl

  // ✅ CORRECTION 2: Fonction pour nettoyer le cache des images
  const cleanupImageCache = useCallback(() => {
    const cache = imageCache.current;
    
    // ✅ BUGFIX-005: Fix inaccurate size calculation
    // Limit by number of items, not by size (size calculation is unreliable)
    // Reason: We can't accurately measure actual memory used by images
    // - naturalWidth * naturalHeight * 4 assumes RGBA format
    // - But images may be compressed, grayscale, different bit depths
    // - Browser might use different encoding than expected
    // Solution: Limit by item count and let browser's GC handle memory
    
    if (cache.size > MAX_CACHE_ITEMS) {
      if (DEBUG_DRAW) console.warn(`🧹 [CACHE] Cache exceeds ${MAX_CACHE_ITEMS} items (size: ${cache.size}), cleaning...`);
      
      // Supprimer les 10 plus anciennes entrées (FIFO)
      const entriesToRemove = Math.min(10, Math.ceil(cache.size * 0.1));
      let removed = 0;
      
      for (const [url] of cache) {
        if (removed >= entriesToRemove) break;
        
        cache.delete(url);
        removed++;
        if (DEBUG_DRAW) console.log(`  ✅ Removed from cache: ${url}`);
      }
      
      if (DEBUG_DRAW) console.log(`✅ [CACHE] Cleanup complete - ${removed} images removed, cache now has ${cache.size} items`);
    }
  }, []);

  // ✅ Utiliser le hook pour nettoyer le cache de temps en temps
  useEffect(() => {
    const interval = setInterval(() => {
      cleanupImageCache();
    }, 30000); // Nettoyage tous les 30 secondes
    
    return () => clearInterval(interval);
  }, [cleanupImageCache]);

  // Utiliser les hooks pour les interactions
  const { handleDrop, handleDragOver, handleDragLeave, isDragOver } = useCanvasDrop({
    canvasRef,
    canvasWidth: width,
    canvasHeight: height,
    elements: state.elements || []
  });

  const { handleCanvasClick, handleMouseDown, handleMouseMove, handleMouseUp, handleContextMenu } = useCanvasInteraction({
    canvasRef,
    canvasWidth: width,
    canvasHeight: height
  });

  // ✅ BUGFIX-007: Memoize drawGrid to prevent recreation on every render
  const drawGrid = useCallback((ctx: CanvasRenderingContext2D, w: number, h: number, size: number, color: string) => {
    ctx.strokeStyle = color;
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
  }, []);  // No deps - pure function

  // Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode

  // Fonction helper pour dessiner un placeholder de logo
  const drawLogoPlaceholder = useCallback((ctx: CanvasRenderingContext2D, element: Element, alignment: string, text: string) => {
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
  }, []);

  const drawCompanyLogo = useCallback((ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as ImageElementProperties;
    const logoUrl = props.src || props.logoUrl || '';
    // const fit = props.fit || 'contain';
    const alignment = props.alignment || 'left';

    if (logoUrl) if (DEBUG_DRAW) console.log('🏷️ [LOGO] drawCompanyLogo called - logoUrl:', logoUrl, 'src:', props.src, 'logoUrl prop:', props.logoUrl);

    // ✅ CORRECTION 7: Détecter si l'URL a changé
    const lastRenderedUrl = renderedLogoUrlsRef.current.get(element.id);
    if (logoUrl !== lastRenderedUrl) {
      if (DEBUG_DRAW) console.log('✅ [LOGO SYNC] URL changée pour', element.id, ':', lastRenderedUrl, '→', logoUrl);
      renderedLogoUrlsRef.current.set(element.id, logoUrl);
    }

    // Fond transparent
    ctx.fillStyle = 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    if (logoUrl) {
      // Vérifier si l'image est en cache
      let img = imageCache.current.get(logoUrl);

      if (!img) {
        if (DEBUG_DRAW) console.log('🖼️ [LOGO] Creating new image for URL:', logoUrl);
        img = document.createElement('img');
        img.crossOrigin = 'anonymous';
        img.src = logoUrl;
        imageCache.current.set(logoUrl, img);

        // Gérer les erreurs de chargement
        img.onerror = () => {
          console.error('❌ [LOGO] Image failed to load:', logoUrl);
        };

        // Gérer le chargement réussi - force un re-render du canvas
        img.onload = () => {
          const loadedImg = img; // Capture img in closure
          if (DEBUG_DRAW) console.log('✅ [LOGO] Image loaded:', logoUrl, 'size:', loadedImg!.naturalWidth, 'x', loadedImg!.naturalHeight);
        };
      } else {
        if (DEBUG_DRAW) console.log('🖼️ [LOGO] Using cached image:', logoUrl, 'complete:', img.complete, 'naturalHeight:', img.naturalHeight);
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
        drawLogoPlaceholder(ctx, element, alignment, img.complete ? 'Erreur' : 'Company_logo');
      }
    } else {
      // Pas d'URL, dessiner un placeholder
      drawLogoPlaceholder(ctx, element, alignment, 'Company_logo');
    }
  }, [drawLogoPlaceholder]);  // ✅ BUGFIX-008: REMOVED setImageLoadCounter

  // ✅ BUGFIX-007: Memoize drawDynamicText to prevent recreation on every render
  const drawDynamicText = useCallback((ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as TextElementProperties;
    const text = props.text || 'Texte personnalisable';
    const fontSize = props.fontSize || 14;
    const fontFamily = props.fontFamily || 'Arial';
    const fontWeight = props.fontWeight || 'normal';
    const fontStyle = props.fontStyle || 'normal';
    const autoWrap = props.autoWrap !== false; // Par défaut activé

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = '#000000';
    ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
    ctx.textAlign = 'left';

    // Remplacer les variables génériques par des valeurs par défaut
    const processedText = text
      .replace(/\[date\]/g, new Date().toLocaleDateString('fr-FR'))
      .replace(/\[nom\]/g, 'Dupont')
      .replace(/\[prenom\]/g, 'Marie')
      .replace(/\[entreprise\]/g, 'Ma Société')
      .replace(/\[telephone\]/g, '+33 1 23 45 67 89')
      .replace(/\[email\]/g, 'contact@masociete.com')
      .replace(/\[site\]/g, 'www.masociete.com')
      .replace(/\[ville\]/g, 'Paris')
      .replace(/\[siret\]/g, '123 456 789 00012')
      .replace(/\[tva\]/g, 'FR 12 345 678 901')
      .replace(/\[capital\]/g, '10 000')
      .replace(/\[rcs\]/g, 'Paris B 123 456 789');

    if (autoWrap) {
      // Fonction pour diviser le texte en lignes selon la largeur disponible
      const wrapText = (text: string, maxWidth: number): string[] => {
        const words = text.split(' ');
        const lines: string[] = [];
        let currentLine = '';

        for (const word of words) {
          const testLine = currentLine + (currentLine ? ' ' : '') + word;
          const metrics = ctx.measureText(testLine);

          if (metrics.width > maxWidth && currentLine) {
            lines.push(currentLine);
            currentLine = word;
          } else {
            currentLine = testLine;
          }
        }

        if (currentLine) {
          lines.push(currentLine);
        }

        return lines;
      };

      // Gérer les sauts de ligne existants (\n)
      const paragraphs = processedText.split('\n');
      let y = 25;

      paragraphs.forEach((paragraph: string) => {
        if (paragraph.trim()) {
          const lines = wrapText(paragraph, element.width - 20); // Marge de 10px de chaque côté
          lines.forEach((line: string) => {
            ctx.fillText(line, 10, y);
            y += fontSize + 4; // Espacement entre lignes
          });
        } else {
          y += fontSize + 4; // Ligne vide
        }
      });
    } else {
      // Comportement original : gérer uniquement les \n existants
      const lines = processedText.split('\n');
      let y = 25;
      lines.forEach((line: string) => {
        ctx.fillText(line, 10, y);
        y += fontSize + 4;
      });
    }
  }, []);  // No deps - pure function

  // ✅ BUGFIX-007: Memoize drawMentions to prevent recreation on every render
  const drawMentions = useCallback((ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as MentionsElementProperties;
    const fontSizeRaw = props.fontSize || 10;
    
    // ✅ BUGFIX-021: Robust font size parsing for various formats
    let fontSize: number;
    if (typeof fontSizeRaw === 'number') {
      fontSize = fontSizeRaw;
    } else if (typeof fontSizeRaw === 'string') {
      // Try removing 'px', 'em', 'rem', 'pt' suffixes
      const numStr = fontSizeRaw.replace(/px|em|rem|pt|%/g, '').trim();
      fontSize = parseFloat(numStr) || 10;
      // If it's 'em' or 'rem', convert to approximate px (1em ≈ 16px)
      if (fontSizeRaw.includes('em') || fontSizeRaw.includes('rem')) {
        fontSize = fontSize * 16;
      }
    } else {
      fontSize = 10;
    }
    
    // Ensure fontSize is reasonable
    fontSize = Math.max(6, Math.min(72, fontSize));
    
    const fontFamily = props.fontFamily || 'Arial';
    const fontWeight = props.fontWeight || 'normal';
    const fontStyle = props.fontStyle || 'normal';
    const textAlign = props.textAlign || 'left';
    const text = props.text || 'SARL au capital de 10 000€ - RCS Lyon 123 456 789\nTVA FR 12 345 678 901 - SIRET 123 456 789 00012\ncontact@maboutique.com - +33 4 12 34 56 78';
    const showSeparator = props.showSeparator !== false;
    const separatorStyle = props.separatorStyle || 'solid';
    const theme = (props.theme || 'legal') as keyof typeof themes;

    // Définition des thèmes pour les mentions
    const themes = {
      legal: {
        backgroundColor: '#ffffff',
        borderColor: '#6b7280',
        textColor: '#374151',
        headerTextColor: '#111827'
      },
      subtle: {
        backgroundColor: '#f9fafb',
        borderColor: '#e5e7eb',
        textColor: '#6b7280',
        headerTextColor: '#374151'
      },
      minimal: {
        backgroundColor: '#ffffff',
        borderColor: '#f3f4f6',
        textColor: '#9ca3af',
        headerTextColor: '#6b7280'
      }
    };

    const currentTheme = themes[theme] || themes.legal;

    // Utiliser les couleurs personnalisées si définies, sinon utiliser le thème
    const bgColor = normalizeColor(props.backgroundColor || currentTheme.backgroundColor);
    const txtColor = normalizeColor(props.textColor || currentTheme.textColor);

    ctx.fillStyle = bgColor;
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = txtColor;

    let y = 15;

    // Dessiner le séparateur si activé
    if (showSeparator) {
      ctx.strokeStyle = txtColor;
      ctx.lineWidth = 1;

      if (separatorStyle === 'double') {
        ctx.beginPath();
        ctx.moveTo(10, y - 5);
        ctx.lineTo(element.width - 10, y - 5);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(10, y - 2);
        ctx.lineTo(element.width - 10, y - 2);
        ctx.stroke();
      } else {
        ctx.setLineDash(separatorStyle === 'dashed' ? [5, 5] : separatorStyle === 'dotted' ? [2, 2] : []);
        ctx.beginPath();
        ctx.moveTo(10, y - 5);
        ctx.lineTo(element.width - 10, y - 5);
        ctx.stroke();
        ctx.setLineDash([]); // Reset line dash
      }

      y += 10; // Espace après le séparateur
    }

    ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
    ctx.textAlign = textAlign as CanvasTextAlign;

    // Fonction de wrapping du texte
    const wrapText = (text: string, maxWidth: number): string[] => {
      if (!text) return [''];

      // Traiter chaque paragraphe séparément (séparé par \n)
      const paragraphs = text.split('\n');
      const wrappedParagraphs: string[] = [];

      for (const paragraph of paragraphs) {
        if (paragraph.trim() === '') {
          // Ligne vide (séparateur), on la garde telle quelle
          wrappedParagraphs.push('');
          continue;
        }

        // Wrapper le paragraphe comme avant
        const words = paragraph.split(' ');
        const lines: string[] = [];
        let currentLine = '';

        for (const word of words) {
          const testLine = currentLine ? currentLine + ' ' + word : word;
          const metrics = ctx.measureText(testLine);

          if (metrics.width > maxWidth && currentLine) {
            // Le mot ne rentre pas, on passe à la ligne
            lines.push(currentLine);
            currentLine = word;
          } else {
            currentLine = testLine;
          }
        }

        if (currentLine) {
          lines.push(currentLine);
        }

        wrappedParagraphs.push(...lines);
      }

      return wrappedParagraphs;
    };

    // Wrapper le texte selon la largeur disponible
    const maxWidth = element.width - 20; // Marge de 20px
    const wrappedLines = wrapText(text, maxWidth);

    // Calculer le nombre maximum de lignes qui peuvent tenir
    const lineHeight = fontSize + 2;
    const maxLines = Math.floor((element.height - (showSeparator ? 25 : 15)) / lineHeight);

    // Rendre seulement les lignes qui tiennent
    wrappedLines.slice(0, maxLines).forEach((line: string, index: number) => {
      const x = textAlign === 'center' ? element.width / 2 : textAlign === 'right' ? element.width - 10 : 10;
      const lineY = (showSeparator ? 25 : 15) + index * lineHeight;
      ctx.fillText(line, x, lineY);
    });
  }, []);  // No deps - pure function

  // ✅ BUGFIX-001/004: Memoize drawElement but pass state as parameter to avoid dependency cycle
  const drawElement = useCallback((ctx: CanvasRenderingContext2D, element: Element, currentState: BuilderState) => {
    // Vérifier si l'élément est visible
    if (element.visible === false) {
      if (DEBUG_DRAW) console.log(`  ⊘ [DRAW] ${element.type} caché (visible=false)`);
      return;
    }

    if (DEBUG_DRAW) console.log(`  ✏️ [DRAW] ${element.type} (${element.id}) à (${element.x}, ${element.y})`);

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
        drawProductTable(ctx, element, currentState);
        break;
      case 'customer_info':
        drawCustomerInfo(ctx, element, currentState);
        break;
      case 'company_info':
        drawCompanyInfo(ctx, element);
        break;
      case 'company_logo':
        drawCompanyLogo(ctx, element);
        break;
      case 'order_number':
        drawOrderNumber(ctx, element, currentState);
        break;
      case 'document_type':
        drawDocumentType(ctx, element, currentState);
        break;
      case 'dynamic-text':
        drawDynamicText(ctx, element);
        break;
      case 'mentions':
        drawMentions(ctx, element);
        break;
      case 'image':
        drawImage(ctx, element, imageCache);
        break;
      default:
        // Élément générique - dessiner un rectangle simple
        if (DEBUG_DRAW) console.warn(`    ⚠️ [DRAW] Élément inconnu: ${element.type}`);
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, element.width, element.height);
    }
    ctx.restore();
  }, [drawCompanyLogo, drawDynamicText, drawMentions]);  // ✅ BUGFIX-007: Include memoized draw functions

  // Fonction pour dessiner la sélection
  function drawSelection(ctx: CanvasRenderingContext2D, selectedIds: string[], elements: Element[]) {
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

    // Rectangle de sélection
    ctx.strokeStyle = '#007acc';
    ctx.lineWidth = 1;
    ctx.setLineDash([5, 5]);
    ctx.strokeRect(minX - 2, minY - 2, maxX - minX + 4, maxY - minY + 4);

    // Poignées de redimensionnement
    const handleSize = 6;
    ctx.fillStyle = '#007acc';
    ctx.setLineDash([]);

    // Coins
    ctx.fillRect(minX - handleSize/2, minY - handleSize/2, handleSize, handleSize);
    ctx.fillRect(maxX - handleSize/2, minY - handleSize/2, handleSize, handleSize);
    ctx.fillRect(minX - handleSize/2, maxY - handleSize/2, handleSize, handleSize);
    ctx.fillRect(maxX - handleSize/2, maxY - handleSize/2, handleSize, handleSize);

    // Centres des côtés
    const midX = (minX + maxX) / 2;
    const midY = (minY + maxY) / 2;
    ctx.fillRect(midX - handleSize/2, minY - handleSize/2, handleSize, handleSize);
    ctx.fillRect(midX - handleSize/2, maxY - handleSize/2, handleSize, handleSize);
    ctx.fillRect(minX - handleSize/2, midY - handleSize/2, handleSize, handleSize);
    ctx.fillRect(maxX - handleSize/2, midY - handleSize/2, handleSize, handleSize);

    // Afficher les dimensions pour chaque élément sélectionné
    selectedElements.forEach(el => {
      if (selectedIds.includes(el.id)) {
        // Coordonnées
        const x = el.x;
        const y = el.y;
        const width = el.width;
        const height = el.height;

        // Afficher les dimensions en pixels sur le coin supérieur droit
        ctx.font = '11px Arial';
        ctx.fillStyle = '#007acc';
        ctx.textAlign = 'right';
        ctx.textBaseline = 'top';

        const dimensionText = `${(width * 1).toFixed(1)}×${(height * 1).toFixed(1)}px`;
        const padding = 4;
        const textWidth = ctx.measureText(dimensionText).width;
        
        // Fond blanc pour meilleure lisibilité
        ctx.fillStyle = 'white';
        ctx.fillRect(x + width - textWidth - padding * 2, y - 20, textWidth + padding * 2, 18);
        
        // Texte
        ctx.fillStyle = '#007acc';
        ctx.font = 'bold 11px Arial';
        ctx.fillText(dimensionText, x + width - padding, y - 16);
      }
    });
  };

  // Fonctions pour gérer le menu contextuel
  const showContextMenu = useCallback((x: number, y: number, elementId?: string) => {
    if (DEBUG_VERBOSE) console.log('Setting context menu visible at:', x, y, 'elementId:', elementId);
    setContextMenu({
      isVisible: true,
      position: { x, y },
      elementId
    });
  }, []);

  const hideContextMenu = useCallback(() => {
    setContextMenu(prev => ({ ...prev, isVisible: false }));
  }, []);

  const handleContextMenuAction = useCallback((action: string, elementId?: string) => {
    if (!elementId) return;

    switch (action) {
      case 'bring-to-front': {
        // Déplacer l'élément à la fin du tableau (devant tous les autres)
        const elementIndex = state.elements.findIndex(el => el.id === elementId);
        if (elementIndex !== -1) {
          const element = state.elements[elementIndex];
          const newElements = [
            ...state.elements.slice(0, elementIndex),
            ...state.elements.slice(elementIndex + 1),
            element
          ];
          dispatch({ type: 'SET_ELEMENTS', payload: newElements });
        }
        break;
      }
      case 'send-to-back': {
        // Déplacer l'élément au début du tableau (derrière tous les autres)
        const elementIndex = state.elements.findIndex(el => el.id === elementId);
        if (elementIndex !== -1) {
          const element = state.elements[elementIndex];
          const newElements = [
            element,
            ...state.elements.slice(0, elementIndex),
            ...state.elements.slice(elementIndex + 1)
          ];
          dispatch({ type: 'SET_ELEMENTS', payload: newElements });
        }
        break;
      }
      case 'bring-forward': {
        // Déplacer l'élément d'une position vers l'avant
        const elementIndex = state.elements.findIndex(el => el.id === elementId);
        if (elementIndex !== -1 && elementIndex < state.elements.length - 1) {
          const newElements = [...state.elements];
          [newElements[elementIndex], newElements[elementIndex + 1]] =
          [newElements[elementIndex + 1], newElements[elementIndex]];
          dispatch({ type: 'SET_ELEMENTS', payload: newElements });
        }
        break;
      }
      case 'send-backward': {
        // Déplacer l'élément d'une position vers l'arrière
        const elementIndex = state.elements.findIndex(el => el.id === elementId);
        if (elementIndex > 0) {
          const newElements = [...state.elements];
          [newElements[elementIndex], newElements[elementIndex - 1]] =
          [newElements[elementIndex - 1], newElements[elementIndex]];
          dispatch({ type: 'SET_ELEMENTS', payload: newElements });
        }
        break;
      }
      case 'duplicate': {
        // Dupliquer l'élément avec un nouvel ID et un léger décalage
        const element = state.elements.find(el => el.id === elementId);
        if (element) {
          const duplicatedElement = {
            ...element,
            id: `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            x: element.x + 10,
            y: element.y + 10,
            createdAt: new Date(),
            updatedAt: new Date()
          };
          dispatch({ type: 'ADD_ELEMENT', payload: duplicatedElement });
        }
        break;
      }
      case 'copy': {
        // Copier l'élément dans le presse-papiers interne
        const element = state.elements.find(el => el.id === elementId);
        if (element) {
          // TODO: Implémenter le presse-papiers interne
          if (DEBUG_VERBOSE) console.log('Copy functionality not yet implemented');
        }
        break;
      }
      case 'cut': {
        // Couper l'élément (copier puis supprimer)
        const element = state.elements.find(el => el.id === elementId);
        if (element) {
          // TODO: Implémenter le presse-papiers interne
          if (DEBUG_VERBOSE) console.log('Cut functionality not yet implemented');
          // dispatch({ type: 'REMOVE_ELEMENT', payload: elementId });
        }
        break;
      }
      case 'reset-size': {
        // Réinitialiser la taille de l'élément à ses dimensions par défaut
        const element = state.elements.find(el => el.id === elementId);
        if (element) {
          const defaultSizes: { [key: string]: { width: number; height: number } } = {
            rectangle: { width: 100, height: 100 },
            circle: { width: 100, height: 100 },
            text: { width: 100, height: 30 },
            line: { width: 100, height: 2 },
            product_table: { width: 400, height: 200 },
            customer_info: { width: 300, height: 80 },
            company_info: { width: 300, height: 120 },
            company_logo: { width: 150, height: 80 },
            order_number: { width: 200, height: 40 },
            document_type: { width: 150, height: 30 },
            'dynamic-text': { width: 200, height: 60 },
            mentions: { width: 400, height: 80 }
          };

          const defaultSize = defaultSizes[element.type] || { width: 100, height: 100 };
          dispatch({
            type: 'UPDATE_ELEMENT',
            payload: {
              id: elementId,
              updates: { width: defaultSize.width, height: defaultSize.height }
            }
          });
        }
        break;
      }
      case 'fit-to-content': {
        // Ajuster la taille de l'élément à son contenu (pour le texte principalement)
        const element = state.elements.find(el => el.id === elementId);
        if (element && (element.type === 'text' || element.type === 'dynamic-text')) {
          // Pour les éléments texte, ajuster la hauteur selon le contenu
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          if (ctx) {
            const props = element as TextElementProperties;
            const fontSize = props.fontSize || 14;
            const fontFamily = props.fontFamily || 'Arial';
            const fontWeight = props.fontWeight || 'normal';
            const fontStyle = props.fontStyle || 'normal';

            ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;

            const text = props.text || 'Texte';
            const lines = text.split('\n');
            const lineHeight = fontSize + 4;
            const contentHeight = lines.length * lineHeight + 20; // Marges

            dispatch({
              type: 'UPDATE_ELEMENT',
              payload: {
                id: elementId,
                updates: { height: Math.max(contentHeight, 30) }
              }
            });
          }
        }
        break;
      }
      case 'delete':
        dispatch({ type: 'REMOVE_ELEMENT', payload: elementId });
        break;
      case 'lock': {
        // Basculer l'état verrouillé de l'élément
        const element = state.elements.find(el => el.id === elementId);
        if (element) {
          dispatch({
            type: 'UPDATE_ELEMENT',
            payload: {
              id: elementId,
              updates: { locked: !element.locked }
            }
          });
        }
        break;
      }
    }
  }, [state.elements, dispatch]);

  const getContextMenuItems = useCallback((elementId?: string): ContextMenuItem[] => {
    if (!elementId) {
      // Menu contextuel pour le canvas vide
      return [
        {
          id: 'section-edit',
          section: 'ÉDITION'
        },
        {
          id: 'paste',
          label: 'Coller',
          icon: '📋',
          shortcut: 'Ctrl+V',
          action: () => {
            // TODO: Implémenter le collage depuis le presse-papiers
            if (DEBUG_VERBOSE) console.log('Paste functionality not yet implemented');
          },
          disabled: true // Désactiver jusqu'à implémentation
        },
        {
          id: 'select-all',
          label: 'Tout sélectionner',
          icon: '☑️',
          shortcut: 'Ctrl+A',
          action: () => {
            // Sélectionner tous les éléments
            const allElementIds = state.elements.map(el => el.id);
            dispatch({ type: 'SET_SELECTION', payload: allElementIds });
          }
        }
      ];
    }

    // Menu contextuel pour un élément
    const element = state.elements.find(el => el.id === elementId);
    const isLocked = element?.locked || false;

    const items: ContextMenuItem[] = [
      // Section Ordre des calques
      {
        id: 'section-layers',
        section: 'CALQUES'
      },
      {
        id: 'layer-order',
        label: 'Ordre des calques',
        icon: '📚',
        children: [
          {
            id: 'bring-to-front',
            label: 'Premier plan',
            icon: '⬆️',
            shortcut: 'Ctrl+↑',
            action: () => handleContextMenuAction('bring-to-front', elementId),
            disabled: isLocked
          },
          {
            id: 'send-to-back',
            label: 'Arrière plan',
            icon: '⬇️',
            shortcut: 'Ctrl+↓',
            action: () => handleContextMenuAction('send-to-back', elementId),
            disabled: isLocked
          },
          {
            id: 'bring-forward',
            label: 'Avancer d\'un plan',
            icon: '↗️',
            shortcut: 'Ctrl+Shift+↑',
            action: () => handleContextMenuAction('bring-forward', elementId),
            disabled: isLocked
          },
          {
            id: 'send-backward',
            label: 'Reculer d\'un plan',
            icon: '↙️',
            shortcut: 'Ctrl+Shift+↓',
            action: () => handleContextMenuAction('send-backward', elementId),
            disabled: isLocked
          }
        ]
      },
      { id: 'separator1', separator: true },

      // Section Édition
      {
        id: 'section-edit',
        section: 'ÉDITION'
      },
      {
        id: 'duplicate',
        label: 'Dupliquer',
        icon: '📋',
        shortcut: 'Ctrl+D',
        action: () => handleContextMenuAction('duplicate', elementId),
        disabled: isLocked,
        children: [
          {
            id: 'duplicate-here',
            label: 'Dupliquer ici',
            icon: '📋',
            action: () => handleContextMenuAction('duplicate', elementId),
            disabled: isLocked
          },
          {
            id: 'duplicate-multiple',
            label: 'Dupliquer plusieurs...',
            icon: '📋📋',
            action: () => handleContextMenuAction('duplicate-multiple', elementId),
            disabled: isLocked
          }
        ]
      },
      {
        id: 'clipboard',
        label: 'Presse-papiers',
        icon: '📄',
        children: [
          {
            id: 'copy',
            label: 'Copier',
            icon: '📄',
            shortcut: 'Ctrl+C',
            action: () => handleContextMenuAction('copy', elementId),
            disabled: false
          },
          {
            id: 'cut',
            label: 'Couper',
            icon: '✂️',
            shortcut: 'Ctrl+X',
            action: () => handleContextMenuAction('cut', elementId),
            disabled: isLocked
          }
        ]
      },
      { id: 'separator2', separator: true },

      // Section Taille
      {
        id: 'section-size',
        section: 'TAILLE'
      },
      {
        id: 'reset-size',
        label: 'Taille par défaut',
        icon: '📏',
        shortcut: 'Ctrl+0',
        action: () => handleContextMenuAction('reset-size', elementId),
        disabled: isLocked
      },
      {
        id: 'fit-to-content',
        label: 'Ajuster au contenu',
        icon: '📐',
        shortcut: 'Ctrl+Shift+F',
        action: () => handleContextMenuAction('fit-to-content', elementId),
        disabled: isLocked || !(element?.type === 'text' || element?.type === 'dynamic-text')
      },
      { id: 'separator3', separator: true },

      // Section État
      {
        id: 'section-state',
        section: 'ÉTAT'
      },
      {
        id: 'lock',
        label: isLocked ? 'Déverrouiller' : 'Verrouiller',
        icon: isLocked ? '🔓' : '🔒',
        shortcut: isLocked ? 'Ctrl+Shift+L' : 'Ctrl+L',
        action: () => handleContextMenuAction('lock', elementId)
      },
      { id: 'separator4', separator: true },

      // Section Danger
      {
        id: 'section-danger',
        section: 'SUPPRESSION'
      },
      {
        id: 'delete',
        label: 'Supprimer',
        icon: '🗑️',
        shortcut: 'Suppr',
        action: () => handleContextMenuAction('delete', elementId),
        disabled: false
      }
    ];

    return items;
  }, [state.elements, handleContextMenuAction, dispatch]);

  // Gestionnaire de clic droit pour le canvas
  const handleCanvasContextMenu = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    event.preventDefault();
    handleContextMenu(event, (x, y, elementId) => {
      showContextMenu(x, y, elementId);
    });
  }, [handleContextMenu, showContextMenu]);

  // Fonction de rendu du canvas
  const renderCanvas = useCallback(() => {
    const canvas = canvasRef.current;
    if (!canvas) {
      if (DEBUG_DRAW) console.warn('❌ [CANVAS] Canvas ref est NULL!');
      return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
      if (DEBUG_DRAW) console.warn('❌ [CANVAS] Context 2D non disponible!');
      return;
    }

    // Clear canvas with white background (matching PDF background)
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    // DEBUG: Log elements
    if (state.elements.length === 0) {
      if (DEBUG_DRAW) console.warn('⚠️ Canvas has 0 elements!');
    } else {
      if (DEBUG_DRAW) console.log(`✅ [CANVAS] Rendering ${state.elements.length} elements`);
    }

    // Appliquer transformation (zoom, pan)
    // Note: zoom est en pourcentage (100 = 100%), donc diviser par 100
    ctx.save();
    
    ctx.translate(state.canvas.pan.x, state.canvas.pan.y);
    
    const zoomScale = state.canvas.zoom / 100;
    ctx.scale(zoomScale, zoomScale);

    // NOTE: Les marges seront réactivées après que le rendu des éléments soit fixé
    // const showMargins = canvasSettings.showMargins;
    // if (showMargins && canvasSettings) {
    //   const marginTopPx = (canvasSettings.marginTop || 0) * 3.78;
    //   const marginLeftPx = (canvasSettings.marginLeft || 0) * 3.78;
    //   ctx.translate(marginLeftPx, marginTopPx);
    // }

    // Dessiner la grille si activée (utiliser les paramètres Canvas Settings et l'état du toggle)
    if (canvasSettings.gridShow && state.canvas.showGrid) {
      drawGrid(ctx, width, height, canvasSettings.gridSize, canvasSettings.gridColor);
    }

    // Dessiner les éléments
    state.elements.forEach((element) => {
      drawElement(ctx, element, state);  // ✅ BUGFIX-001/004: Pass state as parameter
    });

    // Dessiner la sélection
    if (state.selection.selectedElements.length > 0) {
      if (DEBUG_DRAW) console.log(`📦 [CANVAS] Dessin de la sélection: ${state.selection.selectedElements.length} éléments`);
      drawSelection(ctx, state.selection.selectedElements, state.elements);
    }

    ctx.restore();
  }, [width, height, canvasSettings, state, drawElement, drawGrid]);  // ✅ BUGFIX-007: Include memoized drawGrid

  // Redessiner quand l'état change
  useEffect(() => {
    // ✅ BUGFIX: Include ALL essential visual properties in hash
    // This ensures canvas re-renders when ANY visual property changes
    let elementsHash = '';
    for (let i = 0; i < state.elements.length; i++) {
      const e = state.elements[i];
      // Create a comprehensive hash of all element properties
      // Use updatedAt to detect any changes, plus key visual properties
      const getUpdatedAtTime = (updatedAt: unknown): number => {
        if (updatedAt instanceof Date && !isNaN(updatedAt.getTime())) {
          return updatedAt.getTime();
        }
        if (typeof updatedAt === 'string' || typeof updatedAt === 'number') {
          const date = new Date(updatedAt);
          if (!isNaN(date.getTime())) {
            return date.getTime();
          }
        }
        return 0;
      };

      const hashData = {
        id: e.id,
        updatedAt: getUpdatedAtTime(e.updatedAt),
        x: e.x, y: e.y, width: e.width, height: e.height,
        rotation: e.rotation || 0,
        opacity: e.opacity || 1,
        visible: e.visible
      };
      elementsHash += JSON.stringify(hashData) + ';';
    }

    // ✅ IMPORTANT: Include selection in hash to trigger re-render when selection changes
    const selectionHash = JSON.stringify(state.selection.selectedElements);
    elementsHash += selectionHash;
    
    // ✅ Skip si on vient déjà de render les MÊMES positions/tailles
    if (lastRenderedElementsRef.current === elementsHash) {
    if (DEBUG_DRAW) console.log('⏭️ [EFFECT] Skip rendu - mêmes éléments & positions que dernière fois');
      return;
    }
    
    lastRenderedElementsRef.current = elementsHash;
    renderCountRef.current++;
    
    // 🔍 REAL-TIME MONITORING: Track all element property changes
    elementChangeTracker.debugEnabled = DEBUG_MONITORING;
    const changes = elementChangeTracker.trackElements(state.elements);
    if (DEBUG_MONITORING && changes.length > 0) {
      console.log(`📊 [REAL-TIME] ${changes.length} changement(s) détecté(s):`);
      changes.forEach(change => {
        if (change.changeType === 'created') {
          console.log(`  ✨ Créé: ${change.elementId}`);
        } else if (change.changeType === 'deleted') {
          console.log(`  🗑️ Supprimé: ${change.elementId}`);
        } else if (change.changeType === 'property_changed') {
          console.log(`  ${change.property}: ${change.oldValue} → ${change.newValue}`);
        }
      });
    }
    
    if (DEBUG_DRAW) console.log(`🔄 [EFFECT] useEffect de rendu déclenché (${renderCountRef.current}), state.elements.length=`, state.elements.length);
    const timer = setTimeout(() => {
      if (DEBUG_DRAW) console.log('🔄 [EFFECT] Appel renderCanvas immédiatement');
      renderCanvas();
    }, 0);
    return () => clearTimeout(timer);
  }, [state, renderCanvas]);  // ✅ BUGFIX-008: REMOVED imageLoadCounter - it causes double renders when images load

  // ✅ CORRECTION 1: Ajouter beforeunload event pour avertir des changements non-sauvegardés
  useEffect(() => {
    const handleBeforeUnload = (e: Event) => {
      if (state.template.isModified) {
        if (DEBUG_VERBOSE) console.warn('⚠️ [BEFOREUNLOAD] Changements non-sauvegardés!');
        e.preventDefault();
      }
    };

    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => window.removeEventListener('beforeunload', handleBeforeUnload);
  }, [state.template.isModified]);

  // 🎯 Initialize monitoring dashboard
  useEffect(() => {
    CanvasMonitoringDashboard.initialize();
    // Silent initialization
  }, []);

  return (
    <>
      <canvas
        ref={canvasRef}
        width={width}
        height={height}
        className={className}
        onClick={handleCanvasClick}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onContextMenu={handleCanvasContextMenu}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        style={{
          border: `2px solid ${isDragOver ? '#007acc' : '#ccc'}`,
          cursor: 'crosshair',
          backgroundColor: '#ffffff',
          transition: 'border-color 0.2s ease'
        }}
      />
      {contextMenu.isVisible && (
        <ContextMenu
          items={getContextMenuItems(contextMenu.elementId)}
          position={contextMenu.position}
          onClose={hideContextMenu}
          isVisible={contextMenu.isVisible}
        />
      )}
    </>
  );
});
