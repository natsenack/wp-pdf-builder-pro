import React, { useEffect, useRef, useCallback } from 'react';
import { usePreviewContext } from '../context/PreviewContext';
import { usePerformanceMonitor } from '../hooks/usePerformanceMonitor';
import { getPageDimensions, calculateOptimalZoom } from '../utils/previewUtils';

/**
 * CanvasRenderer - Renderer spécialisé pour l'aperçu des éléments Canvas
 * Rend les éléments d'édition en temps réel avec interactions
 */
function CanvasRenderer({ elements = [], scale = 1, interactive = false, className = '' }) {
  const canvasRef = useRef(null);
  const containerRef = useRef(null);
  const { measureOperation } = usePerformanceMonitor('CanvasRenderer');

  // Dimensions de la page
  const pageDims = getPageDimensions('A4', 96);

  // Rendu des éléments Canvas
  const renderElements = useCallback((ctx, elements, scale) => {
    // Fond blanc
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, pageDims.width * scale, pageDims.height * scale);

    // Appliquer l'échelle
    ctx.save();
    ctx.scale(scale, scale);

    // Bordure de page
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 1 / scale; // Ajuster pour l'échelle
    ctx.strokeRect(10, 10, pageDims.width - 20, pageDims.height - 20);

    // Guides de marge (optionnel)
    ctx.strokeStyle = '#f0f0f0';
    ctx.setLineDash([5, 5]);
    ctx.strokeRect(50, 50, pageDims.width - 100, pageDims.height - 100);
    ctx.setLineDash([]);

    // Rendre chaque élément
    elements.forEach((element, index) => {
      renderElement(ctx, element, index, scale);
    });

    ctx.restore();
  }, [pageDims]);

  // Rendu d'un élément individuel
  const renderElement = (ctx, element, index, scale) => {
    const {
      type,
      x = 0,
      y = 0,
      width = 100,
      height = 50,
      content = '',
      properties = {}
    } = element;

    ctx.save();

    // Positionnement
    ctx.translate(x, y);

    switch (type) {
      case 'text':
        renderTextElement(ctx, { content, ...properties });
        break;

      case 'dynamic-text':
        renderDynamicTextElement(ctx, { content, ...properties });
        break;

      case 'rectangle':
        renderRectangleElement(ctx, { width, height, ...properties });
        break;

      case 'image':
        renderImageElement(ctx, { width, height, ...properties });
        break;

      case 'line':
        renderLineElement(ctx, { width, height, ...properties });
        break;

      case 'product_table':
        renderTableElement(ctx, { width, height, ...properties });
        break;

      default:
        renderUnknownElement(ctx, { type, width, height });
    }

    // Sélection visuelle si interactif
    if (interactive && element.selected) {
      ctx.strokeStyle = '#007cba';
      ctx.lineWidth = 2 / scale;
      ctx.strokeRect(-2, -2, width + 4, height + 4);
    }

    ctx.restore();
  };

  // Rendu texte
  const renderTextElement = (ctx, { content, fontSize = 12, fontFamily = 'Arial', color = '#000000', textAlign = 'left' }) => {
    ctx.fillStyle = color;
    ctx.font = `${fontSize}px ${fontFamily}`;
    ctx.textAlign = textAlign;
    ctx.fillText(content, 0, fontSize);
  };

  // Rendu texte dynamique
  const renderDynamicTextElement = (ctx, props) => {
    // Style spécial pour le texte dynamique
    ctx.fillStyle = '#0066cc';
    renderTextElement(ctx, props);

    // Indicateur visuel
    ctx.strokeStyle = '#0066cc';
    ctx.lineWidth = 1;
    ctx.strokeRect(-2, -2, props.width + 4, props.height + 4);
  };

  // Rendu rectangle
  const renderRectangleElement = (ctx, { width, height, fillColor = '#cccccc', strokeColor = '#000000', strokeWidth = 1, fill = true, stroke = true }) => {
    if (fill) {
      ctx.fillStyle = fillColor;
      ctx.fillRect(0, 0, width, height);
    }
    if (stroke) {
      ctx.strokeStyle = strokeColor;
      ctx.lineWidth = strokeWidth;
      ctx.strokeRect(0, 0, width, height);
    }
  };

  // Rendu image
  const renderImageElement = (ctx, { width, height, src, alt = 'Image' }) => {
    if (src) {
      const img = new Image();
      img.onload = () => {
        ctx.drawImage(img, 0, 0, width, height);
      };
      img.src = src;
    } else {
      // Placeholder
      ctx.fillStyle = '#f0f0f0';
      ctx.fillRect(0, 0, width, height);
      ctx.fillStyle = '#666666';
      ctx.font = '12px Arial';
      ctx.textAlign = 'center';
      ctx.fillText(alt, width / 2, height / 2);
    }
  };

  // Rendu ligne
  const renderLineElement = (ctx, { x2 = 100, y2 = 0, color = '#000000', lineWidth = 1 }) => {
    ctx.strokeStyle = color;
    ctx.lineWidth = lineWidth;
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(x2, y2);
    ctx.stroke();
  };

  // Rendu tableau produits
  const renderTableElement = (ctx, { width, height, products = [] }) => {
    const rowHeight = 20;
    const colWidth = width / 4;

    // En-têtes
    ctx.fillStyle = '#f5f5f5';
    ctx.fillRect(0, 0, width, rowHeight);

    ctx.fillStyle = '#333333';
    ctx.font = '12px Arial';
    ctx.textAlign = 'left';

    const headers = ['Produit', 'Qté', 'Prix', 'Total'];
    headers.forEach((header, i) => {
      ctx.fillText(header, i * colWidth + 5, 15);
    });

    // Lignes de produits
    products.slice(0, Math.floor((height - rowHeight) / rowHeight)).forEach((product, i) => {
      const y = (i + 1) * rowHeight;

      ctx.fillStyle = i % 2 === 0 ? '#ffffff' : '#f9f9f9';
      ctx.fillRect(0, y, width, rowHeight);

      ctx.fillStyle = '#333333';
      ctx.fillText(product.name || 'Produit', 5, y + 15);
      ctx.textAlign = 'center';
      ctx.fillText(product.qty || '1', colWidth + colWidth / 2, y + 15);
      ctx.fillText(product.price || '0€', 2 * colWidth + colWidth / 2, y + 15);
      ctx.fillText(product.total || '0€', 3 * colWidth + colWidth / 2, y + 15);
      ctx.textAlign = 'left';
    });

    // Bordures
    ctx.strokeStyle = '#dddddd';
    ctx.lineWidth = 1;
    for (let i = 0; i <= products.length + 1; i++) {
      ctx.beginPath();
      ctx.moveTo(0, i * rowHeight);
      ctx.lineTo(width, i * rowHeight);
      ctx.stroke();
    }
    for (let i = 0; i <= 4; i++) {
      ctx.beginPath();
      ctx.moveTo(i * colWidth, 0);
      ctx.lineTo(i * colWidth, height);
      ctx.stroke();
    }
  };

  // Rendu élément inconnu
  const renderUnknownElement = (ctx, { type, width, height }) => {
    ctx.fillStyle = '#ffebee';
    ctx.fillRect(0, 0, width, height);
    ctx.fillStyle = '#c62828';
    ctx.font = '10px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(`[${type}]`, width / 2, height / 2);
  };

  // Effet de rendu
  useEffect(() => {
    if (!canvasRef.current) return;

    measureOperation('Canvas Render', () => {
      const canvas = canvasRef.current;
      const ctx = canvas.getContext('2d');

      // Ajuster la taille du canvas
      const scaledWidth = pageDims.width * scale;
      const scaledHeight = pageDims.height * scale;

      canvas.width = scaledWidth;
      canvas.height = scaledHeight;

      // Rendre les éléments
      renderElements(ctx, elements, scale);
    });
  }, [elements, scale, renderElements, measureOperation, pageDims]);

  // Gestion du redimensionnement
  useEffect(() => {
    const handleResize = () => {
      if (containerRef.current && canvasRef.current) {
        const container = containerRef.current;
        const canvas = canvasRef.current;

        const optimalZoom = calculateOptimalZoom(
          { width: pageDims.width, height: pageDims.height },
          { width: container.clientWidth, height: container.clientHeight }
        );

        // Ici on pourrait ajuster le zoom automatiquement
        console.log('Optimal zoom:', optimalZoom);
      }
    };

    window.addEventListener('resize', handleResize);
    handleResize(); // Appel initial

    return () => window.removeEventListener('resize', handleResize);
  }, [pageDims]);

  return (
    <div ref={containerRef} className={`canvas-renderer ${className}`}>
      <canvas
        ref={canvasRef}
        className="canvas-renderer-canvas"
        style={{
          maxWidth: '100%',
          height: 'auto',
          border: '1px solid #e0e0e0',
          borderRadius: '4px',
          cursor: interactive ? 'crosshair' : 'default'
        }}
      />

      {/* Overlay pour interactions si nécessaire */}
      {interactive && (
        <div
          className="canvas-renderer-overlay"
          style={{
            position: 'absolute',
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            pointerEvents: 'none'
          }}
        />
      )}
    </div>
  );
}

export default React.memo(CanvasRenderer);