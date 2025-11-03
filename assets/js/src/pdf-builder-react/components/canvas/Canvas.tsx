import React, { useRef, useEffect, useCallback, memo } from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { useCanvasDrop } from '../../hooks/useCanvasDrop.ts';
import { useCanvasInteraction } from '../../hooks/useCanvasInteraction.ts';
import { Element } from '../../types/elements';

interface CanvasProps {
  width: number;
  height: number;
  className?: string;
}

// Fonctions utilitaires de dessin (définies en dehors du composant)
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

const drawRectangle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const fillColor = String(props.backgroundColor || props.fillColor || '#ffffff');
  const strokeColor = String(props.borderColor || props.strokeColor || '#000000');
  const strokeWidth = Number(props.borderWidth || props.strokeWidth || 1);
  const borderRadius = Number(props.borderRadius || 0);

  ctx.fillStyle = fillColor;
  ctx.strokeStyle = strokeColor;
  ctx.lineWidth = strokeWidth;

  if (borderRadius > 0) {
    // Rectangle avec coins arrondis
    ctx.beginPath();
    ctx.roundRect(0, 0, element.width, element.height, borderRadius);
    ctx.fill();
    ctx.stroke();
  } else {
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.strokeRect(0, 0, element.width, element.height);
  }
};

const drawCircle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const fillColor = String(props.fillColor || '#ffffff');
  const strokeColor = String(props.strokeColor || '#000000');
  const strokeWidth = Number(props.strokeWidth || 1);

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
  const props = element as Record<string, unknown>;
  const text = String(props.text || 'Sample Text');
  const fontSize = Number(props.fontSize || 16);
  const fontFamily = String(props.fontFamily || 'Arial');
  const color = String(props.textColor || props.color || '#000000');
  const align = String(props.align || 'left');

  ctx.fillStyle = color;
  ctx.font = fontSize + 'px ' + fontFamily;
  ctx.textAlign = align as CanvasTextAlign;

  const lines = text.split('\n');
  const lineHeight = fontSize * 1.2;
  let y = fontSize;

  lines.forEach((line: string) => {
    ctx.fillText(line, element.width / 2, y);
    y += lineHeight;
  });
};

const drawLine = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const strokeColor = String(props.strokeColor || '#000000');
  const strokeWidth = Number(props.strokeWidth || 1);

  ctx.strokeStyle = strokeColor;
  ctx.lineWidth = strokeWidth;

  ctx.beginPath();
  ctx.moveTo(0, element.height / 2);
  ctx.lineTo(element.width, element.height / 2);
  ctx.stroke();
};

// Fonctions de dessin pour les éléments WooCommerce
const drawProductTable = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const borderColor = String(props.borderColor || '#e5e7eb');
  const headerBgColor = String(props.headerBackgroundColor || '#f9fafb');
  const headerTextColor = String(props.headerTextColor || '#111827');
  const textColor = String(props.textColor || '#374151');
  const alternateRowColor = String(props.alternateRowColor || '#f9fafb');
  const showHeaders = Boolean(props.showHeaders !== false);
  const showBorders = Boolean(props.showBorders !== false);
  const fontSize = Number(props.fontSize || 11);

  // Dimensions des colonnes
  const colWidths = [200, 60, 80, 80]; // Produit, Qté, Prix, Total
  const rowHeight = 25;
  const headerHeight = 30;
  const totalWidth = colWidths.reduce((sum, w) => sum + w, 0);

  // Ajuster la largeur de l'élément si nécessaire
  const elementWidth = Math.max(element.width, totalWidth + 20);

  // Dessiner les bordures si activées
  if (showBorders) {
    ctx.strokeStyle = borderColor;
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, elementWidth, element.height);
  }

  // Dessiner l'en-tête si activé
  if (showHeaders) {
    ctx.fillStyle = headerBgColor;
    ctx.fillRect(0, 0, elementWidth, headerHeight);

    if (showBorders) {
      ctx.strokeStyle = borderColor;
      ctx.strokeRect(0, 0, elementWidth, headerHeight);
    }

    // Texte de l'en-tête
    ctx.fillStyle = headerTextColor;
    ctx.font = `bold ${fontSize + 2}px Arial`;
    ctx.textAlign = 'left';

    const headers = ['Produit', 'Qté', 'Prix', 'Total'];
    let xPos = 5;
    headers.forEach((header, index) => {
      ctx.fillText(header, xPos, 20);
      xPos += colWidths[index];
    });

    // Ligne de séparation
    ctx.strokeStyle = borderColor;
    ctx.beginPath();
    ctx.moveTo(0, headerHeight);
    ctx.lineTo(elementWidth, headerHeight);
    ctx.stroke();
  }

  // Données d'exemple de produits
  const products = [
    { name: 'T-shirt Premium', qty: 2, price: 29.99 },
    { name: 'Jean Slim Fit', qty: 1, price: 89.99 },
    { name: 'Chaussures Sport', qty: 1, price: 129.99 }
  ];

  // Dessiner les lignes de produits
  ctx.font = `${fontSize}px Arial`;
  ctx.fillStyle = textColor;

  products.forEach((product, index) => {
    const yPos = (showHeaders ? headerHeight : 0) + (index * rowHeight) + 20;

    // Couleur alternée des lignes
    if (index % 2 === 1) {
      ctx.fillStyle = alternateRowColor;
      ctx.fillRect(0, yPos - 15, elementWidth, rowHeight);
      ctx.fillStyle = textColor;
    }

    // Dessiner les bordures de ligne si activées
    if (showBorders) {
      ctx.strokeStyle = borderColor;
      ctx.strokeRect(0, yPos - 15, elementWidth, rowHeight);
    }

    let xPos = 5;
    ctx.textAlign = 'left';

    // Produit
    ctx.fillText(product.name, xPos, yPos);
    xPos += colWidths[0];

    // Quantité
    ctx.textAlign = 'center';
    ctx.fillText(product.qty.toString(), xPos + colWidths[1] / 2, yPos);
    xPos += colWidths[1];

    // Prix
    ctx.fillText(`${product.price.toFixed(2)}€`, xPos + colWidths[2] - 5, yPos);
    xPos += colWidths[2];

    // Total
    const total = product.qty * product.price;
    ctx.fillText(`${total.toFixed(2)}€`, xPos + colWidths[3] - 5, yPos);
  });

  // Calculer et afficher le total
  const totalAmount = products.reduce((sum, product) => sum + (product.qty * product.price), 0);
  const totalY = (showHeaders ? headerHeight : 0) + (products.length * rowHeight) + 25;

  // Ligne de séparation pour le total
  ctx.strokeStyle = borderColor;
  ctx.beginPath();
  ctx.moveTo(elementWidth - 100, totalY - 10);
  ctx.lineTo(elementWidth - 5, totalY - 10);
  ctx.stroke();

  // Total
  ctx.fillStyle = headerTextColor;
  ctx.font = `bold ${fontSize + 1}px Arial`;
  ctx.textAlign = 'right';
  ctx.fillText(`Total: ${totalAmount.toFixed(2)}€`, elementWidth - 5, totalY + 5);
};

const drawCustomerInfo = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#374151');
  const fontSize = Number(props.fontSize || 12);
  const showHeaders = Boolean(props.showHeaders !== false);
  const showBorders = Boolean(props.showBorders !== false);
  const backgroundColor = String(props.backgroundColor || 'transparent');
  const borderColor = String(props.borderColor || '#e5e7eb');
  const borderWidth = Number(props.borderWidth || 0);

  // Fond si nécessaire
  if (backgroundColor !== 'transparent') {
    ctx.fillStyle = backgroundColor;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Bordure si activée
  if (showBorders && borderWidth > 0) {
    ctx.strokeStyle = borderColor;
    ctx.lineWidth = borderWidth;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = textColor;
  ctx.font = `${fontSize}px Arial`;
  ctx.textAlign = 'left';

  let yPos = showBorders ? 15 : 10;

  // En-tête si activé
  if (showHeaders) {
    ctx.font = `bold ${fontSize + 2}px Arial`;
    ctx.fillText('Informations Client', showBorders ? 10 : 0, yPos);
    yPos += 25;
    ctx.font = `${fontSize}px Arial`;
  }

  // Données d'exemple client
  const customerData = [
    { label: 'Nom', value: 'Dupont Marie' },
    { label: 'Email', value: 'marie.dupont@email.com' },
    { label: 'Téléphone', value: '+33 6 12 34 56 78' },
    { label: 'Adresse', value: '15 rue de la Paix' },
    { label: 'Ville', value: '75002 Paris, France' }
  ];

  customerData.forEach((item) => {
    ctx.fillText(`${item.label}: ${item.value}`, showBorders ? 10 : 0, yPos);
    yPos += 18;
  });
};

const drawCompanyInfo = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#374151');
  const fontSize = Number(props.fontSize || 12);
  const showHeaders = Boolean(props.showHeaders !== false);
  const showBorders = Boolean(props.showBorders !== false);
  const backgroundColor = String(props.backgroundColor || 'transparent');
  const borderColor = String(props.borderColor || '#e5e7eb');
  const borderWidth = Number(props.borderWidth || 0);

  // Fond si nécessaire
  if (backgroundColor !== 'transparent') {
    ctx.fillStyle = backgroundColor;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Bordure si activée
  if (showBorders && borderWidth > 0) {
    ctx.strokeStyle = borderColor;
    ctx.lineWidth = borderWidth;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = textColor;
  ctx.font = `${fontSize}px Arial`;
  ctx.textAlign = 'left';

  let yPos = showBorders ? 15 : 10;

  // En-tête si activé
  if (showHeaders) {
    ctx.font = `bold ${fontSize + 2}px Arial`;
    ctx.fillText('Informations Société', showBorders ? 10 : 0, yPos);
    yPos += 25;
    ctx.font = `${fontSize}px Arial`;
  }

  // Données d'exemple société
  const companyData = [
    { label: 'Société', value: 'Mon Entreprise SARL' },
    { label: 'SIRET', value: '123 456 789 00012' },
    { label: 'Adresse', value: '25 avenue des Champs' },
    { label: 'Ville', value: '75008 Paris, France' },
    { label: 'Téléphone', value: '+33 1 42 86 75 30' },
    { label: 'Email', value: 'contact@monentreprise.com' },
    { label: 'TVA', value: 'FR 12 345 678 901' }
  ];

  companyData.forEach((item) => {
    ctx.fillText(`${item.label}: ${item.value}`, showBorders ? 10 : 0, yPos);
    yPos += 18;
  });
};

const drawDocumentType = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#111827');
  const title = String(props.title || 'FACTURE');
  const fontSize = Number(props.fontSize || 18);
  const backgroundColor = String(props.backgroundColor || 'transparent');
  const borderColor = String(props.borderColor || '#e5e7eb');
  const borderWidth = Number(props.borderWidth || 0);

  // Fond si nécessaire
  if (backgroundColor !== 'transparent') {
    ctx.fillStyle = backgroundColor;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Bordure si activée
  if (borderWidth > 0) {
    ctx.strokeStyle = borderColor;
    ctx.lineWidth = borderWidth;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = textColor;
  ctx.font = `bold ${fontSize}px Arial`;
  ctx.textAlign = 'center';
  ctx.fillText(title, element.width / 2, element.height / 2);

  // Ajouter un numéro de document d'exemple
  ctx.font = `${fontSize - 4}px Arial`;
  ctx.fillText('N° 2025-00123', element.width / 2, element.height / 2 + 25);
};

const drawMentions = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#6b7280');
  const fontSize = Number(props.fontSize || 10);
  const backgroundColor = String(props.backgroundColor || 'transparent');
  const borderColor = String(props.borderColor || '#e5e7eb');
  const borderWidth = Number(props.borderWidth || 0);

  // Fond si nécessaire
  if (backgroundColor !== 'transparent') {
    ctx.fillStyle = backgroundColor;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Bordure si activée
  if (borderWidth > 0) {
    ctx.strokeStyle = borderColor;
    ctx.lineWidth = borderWidth;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = textColor;
  ctx.font = `${fontSize}px Arial`;
  ctx.textAlign = 'left';

  const mentions = [
    'Mentions légales',
    'TVA non applicable, art. 293 B du CGI',
    'Paiement à 30 jours fin de mois',
    'Escompte pour paiement anticipé: 2%',
    'Règlement par virement bancaire'
  ];

  const startY = borderWidth > 0 ? 15 : 10;
  mentions.forEach((mention, index) => {
    ctx.fillText(mention, borderWidth > 0 ? 10 : 0, startY + index * 15);
  });
};

const drawCompanyLogo = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const borderColor = String(props.borderColor || '#e5e7eb');
  const borderWidth = Number(props.borderWidth || 1);
  const backgroundColor = String(props.backgroundColor || '#ffffff');

  // Fond
  ctx.fillStyle = backgroundColor;
  ctx.fillRect(0, 0, element.width, element.height);

  // Bordure
  ctx.strokeStyle = borderColor;
  ctx.lineWidth = borderWidth;
  ctx.strokeRect(0, 0, element.width, element.height);

  // Placeholder pour le logo
  ctx.fillStyle = '#9ca3af';
  ctx.font = 'bold 14px Arial';
  ctx.textAlign = 'center';
  ctx.fillText('[LOGO]', element.width / 2, element.height / 2);

  ctx.font = '10px Arial';
  ctx.fillText('Entreprise', element.width / 2, element.height / 2 + 18);
};

const drawWooCommerceField = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#374151');
  const fontSize = Number(props.fontSize || 12);
  const backgroundColor = String(props.backgroundColor || 'transparent');
  const borderColor = String(props.borderColor || '#e5e7eb');
  const borderWidth = Number(props.borderWidth || 0);

  // Fond si nécessaire
  if (backgroundColor !== 'transparent') {
    ctx.fillStyle = backgroundColor;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Bordure si activée
  if (borderWidth > 0) {
    ctx.strokeStyle = borderColor;
    ctx.lineWidth = borderWidth;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = textColor;
  ctx.font = `${fontSize}px Arial`;
  ctx.textAlign = 'left';

  // Déterminer le label et la valeur selon le type
  let label = '';
  let value = '';

  switch (element.type) {
    case 'order_number':
      label = 'Commande N°';
      value = '2025-00123';
      break;
    case 'woocommerce-order-date':
      label = 'Date';
      value = '15 novembre 2025';
      break;
    case 'woocommerce-invoice-number':
      label = 'Facture N°';
      value = 'F2025-00123';
      break;
    default:
      label = element.type.replace('_', ' ').toUpperCase();
      value = '[Valeur]';
  }

  // Label
  ctx.font = `bold ${fontSize}px Arial`;
  ctx.fillText(`${label}:`, borderWidth > 0 ? 10 : 0, 15);

  // Valeur
  ctx.font = `${fontSize}px Arial`;
  ctx.fillText(value, borderWidth > 0 ? 10 : 0, 35);
};

const drawElement = (ctx: CanvasRenderingContext2D, element: Element) => {
  // Vérifier si l'élément est visible
  if (!element.visible) {
    return;
  }

  // Vérifier que l'élément a des dimensions valides
  if (!element.width || !element.height || element.width <= 0 || element.height <= 0) {
    return;
  }

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
    case 'text-title':
    case 'text-subtitle':
    case 'dynamic-text':
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
    case 'document_type':
      drawDocumentType(ctx, element);
      break;
    case 'mentions':
      drawMentions(ctx, element);
      break;
    case 'company_logo':
      drawCompanyLogo(ctx, element);
      break;
    case 'order_number':
    case 'woocommerce-order-date':
    case 'woocommerce-invoice-number':
      drawWooCommerceField(ctx, element);
      break;
    default:
      // Élément générique - dessiner un rectangle simple avec le type
      ctx.strokeStyle = '#666666';
      ctx.lineWidth = 1;
      ctx.strokeRect(0, 0, element.width, element.height);

      // Afficher le type de l'élément
      ctx.fillStyle = '#666666';
      ctx.font = '12px Arial';
      ctx.textAlign = 'center';
      ctx.fillText(element.type, element.width / 2, element.height / 2);
  }

  ctx.restore();
};

const drawSelection = (ctx: CanvasRenderingContext2D, selectedElements: string[], elements: Element[]) => {
  selectedElements.forEach((elementId) => {
    const element = elements.find(el => el.id === elementId);
    if (element) {
      ctx.save();
      ctx.translate(element.x, element.y);

      // Dessiner rectangle de sélection
      ctx.strokeStyle = '#007cba';
      ctx.lineWidth = 2;
      ctx.setLineDash([5, 5]);
      ctx.strokeRect(-2, -2, element.width + 4, element.height + 4);
      ctx.setLineDash([]);

      ctx.restore();
    }
  });
};

export const Canvas = memo(function Canvas({ width, height, className }: CanvasProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const { state } = useBuilder();

  // Utiliser les hooks pour les interactions
  const { handleDrop, handleDragOver } = useCanvasDrop({
    canvasRef,
    canvasWidth: width,
    canvasHeight: height,
    elements: state.elements || []
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
    if (state.elements && state.elements.length > 0) {
      state.elements.forEach((element) => {
        drawElement(ctx, element);
      });
    }

    // Dessiner la sélection
    if (state.selection && state.selection.selectedElements && state.selection.selectedElements.length > 0 && state.elements) {
      drawSelection(ctx, state.selection.selectedElements, state.elements);
    }

    ctx.restore();
  }, [state, width, height]);

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
});