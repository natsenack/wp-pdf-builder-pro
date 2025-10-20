import React, { useMemo } from 'react';
import { TextRenderer } from './renderers/TextRenderer';
import { ImageRenderer } from './renderers/ImageRenderer';
import { TableRenderer } from './renderers/TableRenderer';
import { CustomerInfoRenderer } from './renderers/CustomerInfoRenderer';
import { CompanyInfoRenderer } from './renderers/CompanyInfoRenderer';
import { OrderNumberRenderer } from './renderers/OrderNumberRenderer';
import { DynamicTextRenderer } from './renderers/DynamicTextRenderer';
import { MentionsRenderer } from './renderers/MentionsRenderer';
import { RectangleRenderer } from './renderers/RectangleRenderer';
import { BarcodeRenderer } from './renderers/BarcodeRenderer';
import { ProgressBarRenderer } from './renderers/ProgressBarRenderer';
import { WatermarkRenderer } from './renderers/WatermarkRenderer';

/**
 * Moteur de rendu unifié pour l'aperçu PDF
 * Gère le rendu de tous les types d'éléments selon leurs propriétés
 */
const PreviewRenderer = ({
  elements = [],
  previewData = {},
  mode = 'canvas'
}) => {
  // Configuration du canvas d'aperçu (format A4 approximatif)
  const CANVAS_CONFIG = {
    width: 794, // A4 width in pixels at 96 DPI
    height: 1123, // A4 height in pixels at 96 DPI
    backgroundColor: '#ffffff',
    padding: 40
  };

  // Mapping des types d'éléments vers leurs renderers
  const rendererMap = useMemo(() => ({
    text: TextRenderer,
    'dynamic-text': DynamicTextRenderer,
    'conditional-text': DynamicTextRenderer, // Utilise le même renderer
    product_table: TableRenderer,
    customer_info: CustomerInfoRenderer,
    company_logo: ImageRenderer,
    company_info: CompanyInfoRenderer,
    order_number: OrderNumberRenderer,
    document_type: OrderNumberRenderer, // Utilise le même renderer
    mentions: MentionsRenderer,
    image: ImageRenderer,
    logo: ImageRenderer, // Alias pour company_logo
    rectangle: RectangleRenderer,
    line: RectangleRenderer, // Utilise le même renderer
    'shape-rectangle': RectangleRenderer,
    'shape-circle': RectangleRenderer,
    'shape-line': RectangleRenderer,
    'shape-arrow': RectangleRenderer,
    'shape-triangle': RectangleRenderer,
    'shape-star': RectangleRenderer,
    divider: RectangleRenderer, // Utilise le même renderer
    'progress-bar': ProgressBarRenderer,
    barcode: BarcodeRenderer,
    qrcode: BarcodeRenderer,
    watermark: WatermarkRenderer,
    // Fallback pour les éléments non supportés
    default: ({ element, previewData }) => (
      <div
        className="preview-element preview-element-unsupported"
        style={{
          position: 'absolute',
          left: element.x || 0,
          top: element.y || 0,
          width: element.width || 200,
          height: element.height || 50,
          border: '2px dashed #ff6b6b',
          backgroundColor: '#ffeaea',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '12px',
          color: '#ff6b6b',
          borderRadius: '4px'
        }}
      >
        ⚠️ Élément non supporté: {element.type}
      </div>
    )
  }), []);

  // Rendu d'un élément individuel avec gestion d'erreur robuste
  const renderElement = (element) => {
    if (!element || typeof element !== 'object') {
      console.warn('PDF Builder Debug: Invalid element:', element);
      return null;
    }

    if (!element.type) {
      console.warn('PDF Builder Debug: Element missing type:', element);
      return null;
    }

    try {
      const Renderer = rendererMap[element.type] || rendererMap.default;

      if (!Renderer) {
        console.warn('PDF Builder Debug: No renderer found for type:', element.type);
        return null;
      }

      return (
        <Renderer
          key={element.id || Math.random()}
          element={element}
          previewData={previewData}
          mode={mode}
        />
      );
    } catch (error) {
      console.error('PDF Builder Debug: Error rendering element:', element.type, element.id, error);
      // Renderer de fallback ultra-simple
      return (
        <div
          key={element.id || Math.random()}
          style={{
            position: 'absolute',
            left: (element.x || 0) + 'px',
            top: (element.y || 0) + 'px',
            width: (element.width || 200) + 'px',
            height: (element.height || 50) + 'px',
            border: '1px solid #ff6b6b',
            backgroundColor: '#ffeaea',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '10px',
            color: '#ff6b6b',
            borderRadius: '2px',
            padding: '2px',
            boxSizing: 'border-box'
          }}
        >
          ⚠️ {element.type || 'Unknown'}
        </div>
      );
    }
  };

  return (
    <div className="preview-renderer">
      {/* Conteneur du canvas d'aperçu */}
      <div
        className="preview-canvas"
        style={{
          width: CANVAS_CONFIG.width,
          height: CANVAS_CONFIG.height,
          backgroundColor: CANVAS_CONFIG.backgroundColor,
          position: 'relative',
          margin: '0 auto',
          border: '1px solid #e1e5e9',
          borderRadius: '8px',
          overflow: 'hidden',
          boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
        }}
      >
        {/* Zone de padding du document */}
        <div
          className="preview-document-area"
          style={{
            position: 'absolute',
            top: CANVAS_CONFIG.padding,
            left: CANVAS_CONFIG.padding,
            right: CANVAS_CONFIG.padding,
            bottom: CANVAS_CONFIG.padding,
            backgroundColor: 'transparent'
          }}
        >
          {/* Rendu de tous les éléments */}
          {elements.map(renderElement)}
        </div>

        {/* Guides de marge pour l'édition */}
        {mode === 'canvas' && (
          <>
            <div
              className="preview-margin-guide top"
              style={{
                position: 'absolute',
                top: 0,
                left: 0,
                right: 0,
                height: CANVAS_CONFIG.padding,
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderBottom: '1px dashed #3b82f6'
              }}
            />
            <div
              className="preview-margin-guide bottom"
              style={{
                position: 'absolute',
                bottom: 0,
                left: 0,
                right: 0,
                height: CANVAS_CONFIG.padding,
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderTop: '1px dashed #3b82f6'
              }}
            />
            <div
              className="preview-margin-guide left"
              style={{
                position: 'absolute',
                top: 0,
                bottom: 0,
                left: 0,
                width: CANVAS_CONFIG.padding,
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderRight: '1px dashed #3b82f6'
              }}
            />
            <div
              className="preview-margin-guide right"
              style={{
                position: 'absolute',
                top: 0,
                bottom: 0,
                right: 0,
                width: CANVAS_CONFIG.padding,
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderLeft: '1px dashed #3b82f6'
              }}
            />
          </>
        )}
      </div>

      {/* Informations de debug (uniquement en mode développement) */}
      {process.env.NODE_ENV === 'development' && (
        <div className="preview-debug-info" style={{
          marginTop: '20px',
          padding: '10px',
          backgroundColor: '#f8f9fa',
          border: '1px solid #dee2e6',
          borderRadius: '4px',
          fontSize: '12px',
          color: '#6c757d'
        }}>
          <strong>Debug Info:</strong>
          <br />• Mode: {mode}
          <br />• Éléments: {elements.length}
          <br />• Données disponibles: {Object.keys(previewData).length} clés
          <br />• Dimensions canvas: {CANVAS_CONFIG.width}×{CANVAS_CONFIG.height}px
        </div>
      )}
    </div>
  );
};

export { PreviewRenderer };