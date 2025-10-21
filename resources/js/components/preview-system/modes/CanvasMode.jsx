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
  const { state } = usePreviewContext();
  const { data, config } = state;

  // Récupérer les éléments depuis la config (passés via PreviewModal)
  const elements = config?.elements || [];
  const previewData = data || {};

  // Dimensions du canvas (A4 par défaut)
  const canvasWidth = config?.templateData?.width || 595;
  const canvasHeight = config?.templateData?.height || 842;

  // Fonction pour obtenir le renderer approprié selon le type d'élément
  const getRenderer = (element) => {
    const elementKey = `${element.type}_${element.id}`;
    const elementData = previewData[elementKey] || {};

    const commonProps = {
      element: { ...element, ...elementData },
      previewData,
      mode: 'canvas'
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
        return <PDFRenderer key={element.id} {...commonProps} />;
      case 'watermark':
        return <WatermarkRenderer key={element.id} {...commonProps} />;
      case 'progress-bar':
        return <ProgressBarRenderer key={element.id} {...commonProps} />;
      case 'mentions':
        return <MentionsRenderer key={element.id} {...commonProps} />;
      default:
        // Pour les éléments non reconnus, afficher un placeholder
        return (
          <div
            key={element.id}
            style={{
              position: 'absolute',
              left: element.x || 0,
              top: element.y || 0,
              width: element.width || 100,
              height: element.height || 50,
              backgroundColor: '#f0f0f0',
              border: '2px dashed #ccc',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '12px',
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
      {/* Canvas avec fond blanc simulant le PDF */}
      <div
        className="canvas-mode-canvas"
        style={{
          width: canvasWidth,
          height: canvasHeight,
          backgroundColor: '#ffffff',
          position: 'relative',
          margin: '0 auto',
          boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
          border: '1px solid #e1e1e1'
        }}
      >
        {/* Rendre tous les éléments à leurs positions */}
        {elements.map(element => getRenderer(element))}

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