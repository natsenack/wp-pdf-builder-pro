import React from 'react';
import { CanvasElement } from './CanvasElement';
import WooCommerceElement from './WooCommerceElements';

// Cache busting: PreviewModal updated to render canvas elements directly - v2.0

const PreviewModal = ({
  isOpen,
  onClose,
  elements = [],
  canvasWidth = 595,
  canvasHeight = 842,
  zoom = 1
}) => {
  if (!isOpen) return null;

  const handlePrint = () => {
    // Ouvrir l'aperçu dans une nouvelle fenêtre pour l'impression
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    if (printWindow) {
      // Copier le contenu de l'aperçu actuel
      const previewContent = document.querySelector('.preview-content');
      if (previewContent) {
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>Impression PDF Builder Pro</title>
            <style>
              body {
                margin: 0;
                padding: 20px;
                font-family: Arial, sans-serif;
                background: white;
              }
              .print-container {
                max-width: ${canvasWidth}px;
                margin: 0 auto;
                background: white;
                position: relative;
              }
              .preview-canvas {
                border: none !important;
                margin: 0;
                padding: 0;
              }
              @media print {
                body { margin: 0; }
                .print-container { max-width: none; }
              }
            </style>
          </head>
          <body>
            <div class="print-container">
              ${previewContent.outerHTML}
            </div>
            <script>
              window.onload = function() {
                setTimeout(function() {
                  window.print();
                  window.close();
                }, 500);
              };
            </script>
          </body>
          </html>
        `);
        printWindow.document.close();
      } else {
        // Fallback si l'aperçu n'est pas trouvé
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>Impression PDF</title>
          </head>
          <body>
            <p>Contenu d'aperçu non disponible pour l'impression.</p>
            <script>window.print(); window.close();</script>
          </body>
          </html>
        `);
        printWindow.document.close();
      }
    }
  };

  if (!isOpen) return null;

  return (
    <div className="preview-modal-overlay" onClick={onClose}>
      <div className="preview-modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="preview-modal-header">
          <h3>[PDF] Aperçu PDF - PDF Builder Pro</h3>
          <button className="preview-modal-close" onClick={onClose}>×</button>
        </div>

        <div className="preview-modal-body">
          <div className="preview-content">
            <div
              className="preview-canvas"
              style={{
                width: canvasWidth,
                height: canvasHeight,
                margin: '0 auto',
                border: '1px solid #e2e8f0',
                background: 'white',
                position: 'relative',
                overflow: 'hidden',
                transform: `scale(${zoom})`,
                transformOrigin: 'top center'
              }}
            >
              {/* Éléments normaux rendus comme composants */}
              {elements
                .filter(el => !el.type.startsWith('woocommerce-'))
                .map(element => (
                  <CanvasElement
                    key={element.id}
                    element={element}
                    isSelected={false} // Pas de sélection en mode aperçu
                    zoom={1}
                    snapToGrid={false} // Pas de grille en aperçu
                    gridSize={10}
                    canvasWidth={canvasWidth}
                    canvasHeight={canvasHeight}
                    onSelect={() => {}} // Pas d'interaction en aperçu
                    onUpdate={() => {}} // Pas de mise à jour en aperçu
                    onRemove={() => {}} // Pas de suppression en aperçu
                    onContextMenu={() => {}} // Pas de menu contextuel en aperçu
                    dragAndDrop={false} // Pas de drag & drop en aperçu
                  />
                ))}

              {/* Éléments WooCommerce */}
              {elements
                .filter(el => el.type.startsWith('woocommerce-'))
                .map(element => (
                  <WooCommerceElement
                    key={element.id}
                    element={element}
                    isSelected={false} // Pas de sélection en mode aperçu
                    onSelect={() => {}} // Pas d'interaction en aperçu
                    onUpdate={() => {}} // Pas de mise à jour en aperçu
                    dragAndDrop={false} // Pas de drag & drop en aperçu
                    zoom={1}
                    canvasWidth={canvasWidth}
                    canvasHeight={canvasHeight}
                  />
                ))}
            </div>
          </div>
        </div>

        <div className="preview-modal-footer">
          <button className="btn btn-secondary" onClick={onClose}>
            ❌ Fermer
          </button>
          <button className="btn btn-primary" onClick={handlePrint}>
            🖨️ Imprimer
          </button>
        </div>
      </div>
    </div>
  );
};

export default PreviewModal;