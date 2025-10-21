import React from 'react';
import { usePreviewContext } from '../context/PreviewContext';

// Import des renderers
import { TextRenderer } from '../renderers/TextRenderer';
import { RectangleRenderer } from '../renderers/RectangleRenderer';
import { ImageRenderer } from '../renderers/ImageRenderer';
import { TableRenderer } from '../renderers/TableRenderer';
import { BarcodeRenderer } from '../renderers/BarcodeRenderer';
import { DynamicTextRenderer } from '../renderers/DynamicTextRenderer';
import { CustomerInfoRenderer } from '../renderers/CustomerInfoRenderer';
import { CompanyInfoRenderer } from '../renderers/CompanyInfoRenderer';
import { OrderNumberRenderer } from '../renderers/OrderNumberRenderer';
import PDFRenderer from '../renderers/PDFRenderer';
import { WatermarkRenderer } from '../renderers/WatermarkRenderer';
import { ProgressBarRenderer } from '../renderers/ProgressBarRenderer';
import { MentionsRenderer } from '../renderers/MentionsRenderer';

/**
 * CanvasMode - Aperçu spatial du canvas avec données d'exemple
 * Rend tous les éléments du canvas à leurs positions avec des données fictives
 */
function CanvasMode() {
  console.log('CanvasMode - Component rendering started');

  const { state } = usePreviewContext();
  console.log('CanvasMode - usePreviewContext returned:', { state });

  const { data, config } = state;

  // Récupérer les éléments depuis la config (passés via PreviewModal)
  const elements = config?.elements || [];
  const previewData = data || {};

  console.log('CanvasMode - State:', state);
  console.log('CanvasMode - Config:', config);
  console.log('CanvasMode - Elements:', elements);
  console.log('CanvasMode - PreviewData:', previewData);

  // Dimensions du canvas (A4 par défaut)
  const canvasWidth = config?.templateData?.width || 595;
  const canvasHeight = config?.templateData?.height || 842;

  // Calculer l'échelle pour que le canvas tienne dans la modal
  // La modal fait environ 800px de large et 600px de haut
  // Laissons une marge de 100px de chaque côté
  const maxWidth = 600;
  const maxHeight = 400;
  const scaleX = maxWidth / canvasWidth;
  const scaleY = maxHeight / canvasHeight;
  const scale = Math.min(scaleX, scaleY, 1); // Ne pas agrandir si plus petit

  console.log('CanvasMode - Canvas dimensions:', { canvasWidth, canvasHeight });
  console.log('CanvasMode - Scale calculations:', { scaleX, scaleY, scale });

  // Fonction pour obtenir le renderer approprié selon le type d'élément
  const getRenderer = (element) => {
    console.log('CanvasMode - getRenderer called for element:', element.type, element.id);
    console.log('CanvasMode - Rendering element:', element);
    const elementKey = `${element.type}_${element.id}`;
    const elementData = previewData[elementKey] || {};

    console.log('CanvasMode - Element key:', elementKey, 'Element data:', elementData);

    const commonProps = {
      element: { ...element, ...elementData },
      previewData,
      mode: 'canvas',
      canvasScale: scale
    };

    switch (element.type) {
      case 'text':
        return <TextRenderer key={element.id} {...commonProps} />;
      case 'rectangle':
        return <RectangleRenderer key={element.id} {...commonProps} />;
      case 'image':
        return <ImageRenderer key={element.id} {...commonProps} />;
      case 'table':
        return <TableRenderer key={element.id} {...commonProps} />;
      case 'barcode':
        return <BarcodeRenderer key={element.id} {...commonProps} />;
      case 'dynamic-text':
        return <DynamicTextRenderer key={element.id} {...commonProps} />;
      case 'customer-info':
        return <CustomerInfoRenderer key={element.id} {...commonProps} />;
      case 'company-info':
        return <CompanyInfoRenderer key={element.id} {...commonProps} />;
      case 'order-number':
        return <OrderNumberRenderer key={element.id} {...commonProps} />;
      case 'pdf':
        // Placeholder pour les éléments PDF dans l'aperçu canvas
        return (
          <div
            key={element.id}
            style={{
              position: 'absolute',
              left: element.x * scale,
              top: element.y * scale,
              width: element.width * scale,
              height: element.height * scale,
              backgroundColor: '#f8f9fa',
              border: '2px solid #dee2e6',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: `${14 * scale}px`,
              color: '#6c757d',
              borderRadius: '4px'
            }}
          >
            📄 PDF Embed
          </div>
        );
      case 'watermark':
        return <WatermarkRenderer key={element.id} {...commonProps} />;
      case 'progress-bar':
        return <ProgressBarRenderer key={element.id} {...commonProps} />;
      case 'mentions':
        console.log('CanvasMode - Rendering mentions element:', element, 'with data:', elementData);
        return <MentionsRenderer key={element.id} {...commonProps} />;
      default:
        return (
          <div
            key={element.id}
            style={{
              position: 'absolute',
              left: (element.x || 0) * scale,
              top: (element.y || 0) * scale,
              width: (element.width || 100) * scale,
              height: (element.height || 50) * scale,
              backgroundColor: '#f0f0f0',
              border: '2px dashed #ccc',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: `${12 * scale}px`,
              color: '#666'
            }}
          >
            {element.type || 'unknown'}
          </div>
        );
    }
  };

  return (
    <div className="canvas-mode-preview">
      <div
        className="canvas-mode-canvas"
        style={{
          width: canvasWidth,
          height: canvasHeight,
          backgroundColor: '#ffffff',
          position: 'relative',
          margin: '0 auto',
          boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
          border: '1px solid #e1e1e1',
          transform: `scale(${scale})`,
          transformOrigin: 'top center'
        }}
      >
        {/* Rendre tous les éléments à leurs positions */}
        {elements.map(element => {
          console.log('CanvasMode - Mapping element:', element);
          return getRenderer(element);
        })}

        {/* Message d'exemple si aucun élément */}
        {elements.length === 0 && (
          <div
            style={{
              position: 'absolute',
              top: '50%',
              left: '50%',
              transform: 'translate(-50%, -50%)',
              textAlign: 'center',
              color: '#666',
              fontSize: '16px'
            }}
          >
            <div style={{ fontSize: '48px', marginBottom: '16px' }}>📄</div>
            <div>Aucun élément dans le canvas</div>
            <div style={{ fontSize: '14px', marginTop: '8px' }}>
              Ajoutez des éléments dans l'éditeur pour les voir ici
            </div>
          </div>
        )}
      </div>

      {/* Informations sur l'aperçu */}
      <div
        style={{
          marginTop: '20px',
          padding: '16px',
          backgroundColor: '#f8f9fa',
          borderRadius: '8px',
          textAlign: 'center',
          fontSize: '14px',
          color: '#666'
        }}
      >
        <strong>📋 Aperçu du Canvas</strong>
        <br />
        <span>Dimensions: {canvasWidth} × {canvasHeight} points ({Math.round(canvasWidth * 0.3528)} × {Math.round(canvasHeight * 0.3528)} mm)</span>
        <br />
        <span>Éléments: {elements.length}</span>
        {Object.keys(previewData).length > 0 && (
          <>
            <br />
            <span>🔄 Données d'exemple chargées</span>
          </>
        )}
      </div>
    </div>
  );
}

export default CanvasMode;