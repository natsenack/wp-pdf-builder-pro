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
  const borderColor = String(props.borderColor || '#cccccc');
  const headerColor = String(props.headerColor || '#f0f0f0');

  // Dessiner le cadre du tableau
  ctx.strokeStyle = borderColor;
  ctx.lineWidth = 1;
  ctx.strokeRect(0, 0, element.width, element.height);

  // Dessiner l'en-tête
  ctx.fillStyle = headerColor;
  ctx.fillRect(0, 0, element.width, 30);

  // Texte d'en-tête
  ctx.fillStyle = '#333333';
  ctx.font = '12px Arial';
  ctx.textAlign = 'left';
  ctx.fillText('Tableau Produits', 10, 20);

  // Lignes de séparation
  ctx.strokeStyle = borderColor;
  ctx.beginPath();
  ctx.moveTo(0, 30);
  ctx.lineTo(element.width, 30);
  ctx.stroke();

  // Contenu d'exemple
  ctx.fillStyle = '#666666';
  ctx.font = '10px Arial';
  ctx.fillText('Produit 1 - Qté: 2 - Prix: 25.00€', 10, 50);
  ctx.fillText('Produit 2 - Qté: 1 - Prix: 15.00€', 10, 70);
};

const drawCustomerInfo = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#333333');

  ctx.fillStyle = textColor;
  ctx.font = '12px Arial';
  ctx.textAlign = 'left';

  const lines = [
    'Informations Client',
    'Nom: {{customer_name}}',
    'Email: {{customer_email}}',
    'Téléphone: {{customer_phone}}',
    'Adresse: {{customer_address}}'
  ];

  lines.forEach((line, index) => {
    ctx.fillText(line, 0, 20 + index * 18);
  });
};

const drawCompanyInfo = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#333333');

  ctx.fillStyle = textColor;
  ctx.font = '12px Arial';
  ctx.textAlign = 'left';

  const lines = [
    'Informations Société',
    'Nom: {{company_name}}',
    'Adresse: {{company_address}}',
    'Téléphone: {{company_phone}}',
    'Email: {{company_email}}'
  ];

  lines.forEach((line, index) => {
    ctx.fillText(line, 0, 20 + index * 18);
  });
};

const drawDocumentType = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#333333');
  const title = String(props.title || 'FACTURE');

  ctx.fillStyle = textColor;
  ctx.font = 'bold 16px Arial';
  ctx.textAlign = 'center';
  ctx.fillText(title, element.width / 2, element.height / 2);
};

const drawMentions = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#666666');

  ctx.fillStyle = textColor;
  ctx.font = '10px Arial';
  ctx.textAlign = 'left';

  const mentions = [
    'Mentions légales',
    'TVA non applicable, art. 293 B du CGI',
    'Paiement à 30 jours'
  ];

  mentions.forEach((mention, index) => {
    ctx.fillText(mention, 0, 15 + index * 15);
  });
};

const drawCompanyLogo = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const borderColor = String(props.borderColor || '#cccccc');

  // Dessiner un rectangle pour représenter le logo
  ctx.strokeStyle = borderColor;
  ctx.lineWidth = 1;
  ctx.strokeRect(0, 0, element.width, element.height);

  // Texte placeholder
  ctx.fillStyle = '#999999';
  ctx.font = '12px Arial';
  ctx.textAlign = 'center';
  ctx.fillText('[LOGO]', element.width / 2, element.height / 2);
};

const drawWooCommerceField = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as Record<string, unknown>;
  const textColor = String(props.textColor || '#333333');
  const label = String(props.label || element.type.replace('_', ' ').toUpperCase());

  ctx.fillStyle = textColor;
  ctx.font = '12px Arial';
  ctx.textAlign = 'left';

  // Label
  ctx.fillText(label + ':', 0, 15);

  // Valeur d'exemple avec variable
  const value = `{{${element.type}}}`;
  ctx.fillText(value, 0, 35);
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
      console.log('Rendering', state.elements.length, 'elements:', state.elements);
      state.elements.forEach((element, index) => {
        console.log(`Drawing element ${index}:`, element.type, 'at', element.x, element.y, 'size', element.width, 'x', element.height);
        drawElement(ctx, element);
      });
    } else {
      console.log('No elements to render');
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