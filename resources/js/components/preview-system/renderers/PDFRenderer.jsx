import React, { useEffect, useRef, useState } from 'react';
import { usePreviewContext } from '../context/PreviewContext';
import { usePerformanceMonitor } from '../hooks/usePerformanceMonitor';
import { getPageDimensions, calculateOptimalZoom } from '../utils/previewUtils';

/**
 * PDFRenderer - Renderer spécialisé pour l'aperçu PDF
 * Utilise Canvas HTML5 pour le rendu haute qualité
 */
function PDFRenderer({ pageData, scale = 1, className = '' }) {
  const canvasRef = useRef(null);
  const { measureOperation } = usePerformanceMonitor('PDFRenderer');
  const [renderStatus, setRenderStatus] = useState('idle'); // idle, rendering, complete, error

  useEffect(() => {
    if (!pageData || !canvasRef.current) return;

    setRenderStatus('rendering');

    measureOperation('PDF Page Render', async () => {
      try {
        const canvas = canvasRef.current;
        const ctx = canvas.getContext('2d');

        // Dimensions de la page (A4 par défaut)
        const pageDims = getPageDimensions('A4', 96);
        const scaledWidth = pageDims.width * scale;
        const scaledHeight = pageDims.height * scale;

        // Ajuster la taille du canvas
        canvas.width = scaledWidth;
        canvas.height = scaledHeight;

        // Fond blanc
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, scaledWidth, scaledHeight);

        // Appliquer l'échelle
        ctx.save();
        ctx.scale(scale, scale);

        // Rendu simulé du contenu PDF
        // Ici : logique réelle de rendu PDF (screenshot + TCPDF)
        renderPDFContent(ctx, pageData, pageDims.width, pageDims.height);

        ctx.restore();

        setRenderStatus('complete');
      } catch (error) {
        console.error('Erreur rendu PDF:', error);
        setRenderStatus('error');
      }
    });
  }, [pageData, scale, measureOperation]);

  // Fonction de rendu simulé (remplacer par vraie logique)
  const renderPDFContent = (ctx, data, width, height) => {
    // Fond de page
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    // Bordure de page
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 1;
    ctx.strokeRect(10, 10, width - 20, height - 20);

    // Contenu simulé
    if (data.elements) {
      data.elements.forEach((element, index) => {
        renderElement(ctx, element, index);
      });
    }

    // Numéro de page
    ctx.fillStyle = '#666666';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(`Page ${data.number || 1}`, width / 2, height - 20);
  };

  // Rendu d'un élément individuel
  const renderElement = (ctx, element, index) => {
    const { type, x = 0, y = 0, content = '', ...props } = element;

    ctx.save();

    switch (type) {
      case 'text':
        ctx.fillStyle = props.color || '#000000';
        ctx.font = `${props.fontSize || 12}px ${props.fontFamily || 'Arial'}`;
        ctx.textAlign = props.textAlign || 'left';
        ctx.fillText(content, x, y);
        break;

      case 'rectangle':
        ctx.fillStyle = props.fillColor || '#cccccc';
        ctx.strokeStyle = props.strokeColor || '#000000';
        ctx.lineWidth = props.strokeWidth || 1;
        if (props.fill) ctx.fillRect(x, y, props.width || 100, props.height || 50);
        if (props.stroke) ctx.strokeRect(x, y, props.width || 100, props.height || 50);
        break;

      case 'line':
        ctx.strokeStyle = props.color || '#000000';
        ctx.lineWidth = props.width || 1;
        ctx.beginPath();
        ctx.moveTo(x, y);
        ctx.lineTo((props.x2 || x + 100), (props.y2 || y));
        ctx.stroke();
        break;

      default:
        // Élément non supporté - rendu générique
        ctx.fillStyle = '#ffcccc';
        ctx.fillRect(x, y, 50, 20);
        ctx.fillStyle = '#000000';
        ctx.font = '10px Arial';
        ctx.fillText(`${type}`, x + 5, y + 15);
    }

    ctx.restore();
  };

  return (
    <div className={`pdf-renderer ${className}`}>
      <canvas
        ref={canvasRef}
        className={`pdf-canvas ${renderStatus}`}
        style={{
          maxWidth: '100%',
          height: 'auto',
          border: renderStatus === 'error' ? '2px solid #ff6b6b' : '1px solid #e0e0e0',
          borderRadius: '4px'
        }}
      />

      {/* Indicateurs de statut */}
      {renderStatus === 'rendering' && (
        <div className="pdf-renderer-loading">
          <div className="pdf-spinner"></div>
          <span>Rendu en cours...</span>
        </div>
      )}

      {renderStatus === 'error' && (
        <div className="pdf-renderer-error">
          <span>Erreur de rendu</span>
        </div>
      )}
    </div>
  );
}

export default React.memo(PDFRenderer);