import React, { useRef, useEffect, useCallback } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { useCanvasDrop } from '../../hooks/useCanvasDrop.ts';
import { useCanvasInteraction } from '../../hooks/useCanvasInteraction.ts';
import { Point, Element } from '../../types/elements';

interface CanvasProps {
  width: number;
  height: number;
  className?: string;
}

export function Canvas({ width, height, className }: CanvasProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const { state, dispatch } = useBuilder();

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
    state.elements.forEach(element => {
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
    const strokeWidth = (element as any).strokeWidth || 1;
    const x2 = (element as any).x2 || element.width;
    const y2 = (element as any).y2 || element.height;

    ctx.strokeStyle = strokeColor;
    ctx.lineWidth = strokeWidth;

    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(x2, y2);
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

  // Fonctions de rendu WooCommerce avec données fictives
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
    const shippingCost = props.shippingCost || 8.50; // Exemple de frais de port
    const taxRate = props.taxRate || 20; // Exemple de TVA 20%
    const globalDiscount = props.globalDiscount || 5; // Exemple de coupon panier "PROMO5" (5% sur le total)
    const orderFees = props.orderFees || 2.50; // Exemple de frais de commande

    // Données fictives plus réalistes de produits WooCommerce
    // Données d'exemple WooCommerce avec remises de coupons appliqués
    const products = [
      {
        sku: 'TSHIRT-001',
        name: 'T-shirt Premium Bio',
        description: 'T-shirt en coton biologique, coupe slim',
        qty: 2,
        price: 29.99,
        discount: 0, // Pas de remise individuelle sur ce produit
        total: 59.98
      },
      {
        sku: 'JEAN-045',
        name: 'Jean Slim Fit Noir',
        description: 'Jean stretch confort, taille haute',
        qty: 1,
        price: 89.99,
        discount: 10.00, // Remise de 10€ via coupon "ETE2025"
        total: 79.99
      },
      {
        sku: 'SHOES-089',
        name: 'Chaussures Running Pro',
        description: 'Chaussures de running avec semelle amortissante',
        qty: 1,
        price: 129.99,
        discount: 0, // Pas de remise individuelle
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

    // Calcul du total avec remises
    const subtotal = products.reduce((sum, product) => sum + (product.price * product.qty), 0);
    const itemDiscounts = products.reduce((sum, product) => sum + product.discount, 0);
    const subtotalAfterItemDiscounts = subtotal - itemDiscounts;

    // Sous-total incluant les frais de commande
    const subtotalWithOrderFees = subtotalAfterItemDiscounts + orderFees;

    // Appliquer la remise globale sur le sous-total incluant les frais de commande (seulement si affichée)
    const globalDiscountAmount = (globalDiscount > 0 && showGlobalDiscount) ? (subtotalWithOrderFees * globalDiscount / 100) : 0;
    const subtotalAfterGlobalDiscount = subtotalWithOrderFees - globalDiscountAmount;

    // Ajouter les frais de port (seulement si affichés)
    const subtotalWithShipping = subtotalAfterGlobalDiscount + (showShipping ? shippingCost : 0);

    // Calculer les taxes (seulement si affichées)
    const taxAmount = (taxRate > 0 && showTax) ? (subtotalWithShipping * taxRate / 100) : 0;

    // Total final
    const finalTotal = subtotalWithShipping + taxAmount;

    // Devise par défaut (géré par WooCommerce)
    const currency = '€';

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
      ctx.strokeRect(0, 0, element.width, element.height);
    }

    ctx.textAlign = 'left';
    let currentY = showHeaders ? 25 : 15;

    // En-têtes avec style professionnel
    if (showHeaders) {
      ctx.fillStyle = props.headerBackgroundColor || '#f9fafb';
      ctx.fillRect(1, 1, element.width - 2, 22);

      ctx.fillStyle = props.headerTextColor || '#374151';
      ctx.font = `bold ${fontSize + 1}px Arial`;
      ctx.textBaseline = 'top';

      columns.forEach(col => {
        ctx.textAlign = col.align as CanvasTextAlign;
        const textX = col.align === 'right' ? col.x + col.width * (element.width - 16) - 4 :
                     col.align === 'center' ? col.x + (col.width * (element.width - 16)) / 2 :
                     col.x;
        ctx.fillText(col.label, textX, 6);
      });

      // Ligne de séparation sous les en-têtes
      ctx.strokeStyle = '#e5e7eb';
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.moveTo(4, 24);
      ctx.lineTo(element.width - 4, 24);
      ctx.stroke();

      currentY = 45;
    }

    // Calcul de la hauteur uniforme des lignes
    const rowHeight = showDescription ? 40 : 28;

    // Produits avec alternance de couleurs
    ctx.font = `${fontSize}px Arial`;
    ctx.textBaseline = 'middle';

    products.forEach((product, index) => {

      // Fond alterné pour les lignes
      if (showAlternatingRows && index % 2 === 1) {
        ctx.fillStyle = props.alternateRowColor || '#f9fafb';
        ctx.fillRect(1, currentY - 2, element.width - 2, rowHeight);
      }

      ctx.fillStyle = '#000000';

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

        ctx.fillText(text, textX, currentY + rowHeight / 2);
      });

      currentY += rowHeight + 4;

      // Ligne de séparation subtile entre les produits
      if (index < products.length - 1) {
        ctx.strokeStyle = '#f3f4f6';
        ctx.lineWidth = 0.5;
        ctx.beginPath();
        ctx.moveTo(8, currentY - 6);
        ctx.lineTo(element.width - 8, currentY - 6);
        ctx.stroke();
      }
    });

    // Section des totaux
    currentY += 8;

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
    ctx.fillStyle = '#374151';
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
      ctx.fillStyle = '#059669'; // Vert pour la remise
      ctx.fillText('Coupon:', element.width - 200, currentY);
      ctx.textAlign = 'right';
      ctx.fillText(`-${totalDiscounts.toFixed(2)}${currency}`, element.width - 8, currentY);
      currentY += 18;
    }

    // Frais de port
    if (shippingCost > 0 && showShipping) {
      ctx.textAlign = 'left';
      ctx.fillStyle = '#374151';
      ctx.fillText('Frais de port:', element.width - 200, currentY);
      ctx.textAlign = 'right';
      ctx.fillText(`${shippingCost.toFixed(2)}${currency}`, element.width - 8, currentY);
      currentY += 18;
    }

    // Taxes
    if (taxAmount > 0 && showTax) {
      ctx.textAlign = 'left';
      ctx.fillStyle = '#374151';
      ctx.fillText(`TVA (${taxRate}%):`, element.width - 200, currentY);
      ctx.textAlign = 'right';
      ctx.fillText(`${taxAmount.toFixed(2)}${currency}`, element.width - 8, currentY);
      currentY += 18;
    }

    currentY += 8; // Plus d'espace avant la ligne de séparation du total
    ctx.strokeStyle = '#374151';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(element.width - 200, currentY - 5);
    ctx.lineTo(element.width - 8, currentY - 5);
    ctx.stroke();

    currentY += 8; // Plus d'espace après la ligne de séparation
    ctx.font = `bold ${fontSize + 2}px Arial`;
    ctx.fillStyle = '#111827';
    ctx.textAlign = 'left';
    ctx.fillText('TOTAL:', element.width - 200, currentY);
    ctx.textAlign = 'right';
    ctx.fillText(`${finalTotal.toFixed(2)}${currency}`, element.width - 8, currentY);
  };

  const drawCustomerInfo = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 12;
    const layout = props.layout || 'vertical';

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = '#000000';
    ctx.font = `bold ${fontSize + 2}px Arial`;
    ctx.textAlign = 'left';

    let y = 20;

    // Informations client fictives
    const customerData = {
      name: 'Marie Dupont',
      address: '15 rue des Lilas',
      city: '75001 Paris',
      email: 'marie.dupont@email.com',
      phone: '+33 6 12 34 56 78'
    };

    if (layout === 'vertical') {
      ctx.fillText(customerData.name, 0, y);
      y += 18;

      ctx.font = `${fontSize}px Arial`;
      ctx.fillText(customerData.address, 0, y);
      y += 15;
      ctx.fillText(customerData.city, 0, y);
      y += 18;
      ctx.fillText(customerData.email, 0, y);
      y += 15;
      ctx.fillText(customerData.phone, 0, y);
    } else {
      // Layout horizontal
      ctx.fillText(`${customerData.name} - ${customerData.email}`, 0, y);
      ctx.fillText(customerData.phone, element.width - 100, y);
    }
  };

  const drawCompanyInfo = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 12;

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = '#000000';
    ctx.font = `bold ${fontSize + 2}px Arial`;
    ctx.textAlign = 'left';

    let y = 20;

    // Informations entreprise fictives
    const companyData = {
      name: 'Ma Boutique En Ligne',
      address: '25 avenue des Commerçants',
      city: '69000 Lyon',
      siret: 'SIRET: 123 456 789 00012',
      tva: 'TVA: FR 12 345 678 901',
      email: 'contact@maboutique.com',
      phone: '+33 4 12 34 56 78'
    };

    ctx.fillText(companyData.name, 0, y);
    y += 18;

    ctx.font = `${fontSize}px Arial`;
    ctx.fillText(companyData.address, 0, y);
    y += 15;
    ctx.fillText(companyData.city, 0, y);
    y += 18;
    ctx.fillText(companyData.siret, 0, y);
    y += 15;
    ctx.fillText(companyData.tva, 0, y);
    y += 15;
    ctx.fillText(companyData.email, 0, y);
    y += 15;
    ctx.fillText(companyData.phone, 0, y);
  };

  const drawCompanyLogo = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fit = props.fit || 'contain';
    const alignment = props.alignment || 'left';

    // Fond transparent
    ctx.fillStyle = 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    // Dessiner un logo fictif (rectangle avec texte)
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
    ctx.fillStyle = '#007acc';
    ctx.fillRect(x, y, logoWidth, logoHeight);

    // Texte du logo
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 16px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('LOGO', x + logoWidth / 2, y + logoHeight / 2 + 6);
  };

  const drawOrderNumber = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 14;
    const textAlign = props.textAlign || 'right';

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = '#000000';
    ctx.font = `bold ${fontSize}px Arial`;
    ctx.textAlign = textAlign as CanvasTextAlign;

    const orderNumber = 'CMD-2024-01234';
    const orderDate = '27/10/2024';

    let x = textAlign === 'right' ? element.width - 10 : textAlign === 'center' ? element.width / 2 : 10;

    ctx.fillText(`Commande: ${orderNumber}`, x, 20);
    ctx.font = `${fontSize - 2}px Arial`;
    ctx.fillText(`Date: ${orderDate}`, x, 40);
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

    // Remplacer les variables par des valeurs fictives
    const processedText = template
      .replace('#{order_number}', 'CMD-2024-01234')
      .replace('#{customer_name}', 'Marie Dupont')
      .replace('#{order_date}', '27/10/2024')
      .replace('#{total}', '279.96€');

    ctx.fillText(processedText, 10, 25);
  };

  const drawMentions = (ctx: CanvasRenderingContext2D, element: Element) => {
    const props = element as any;
    const fontSize = props.fontSize || 10;
    const textAlign = props.textAlign || 'left';

    ctx.fillStyle = props.backgroundColor || 'transparent';
    ctx.fillRect(0, 0, element.width, element.height);

    ctx.fillStyle = '#666666';
    ctx.font = `${fontSize}px Arial`;
    ctx.textAlign = textAlign as CanvasTextAlign;

    const mentions = [
      'SARL au capital de 10 000€ - RCS Lyon 123 456 789',
      'TVA FR 12 345 678 901 - SIRET 123 456 789 00012',
      'contact@maboutique.com - +33 4 12 34 56 78'
    ];

    let y = 15;
    mentions.forEach(mention => {
      ctx.fillText(mention, textAlign === 'center' ? element.width / 2 : 10, y);
      y += 12;
    });
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