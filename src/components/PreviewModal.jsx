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
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      // Créer le HTML pour l'impression basé sur les éléments rendus
      const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Impression PDF</title>
          <style>
            body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
            .print-content {
              width: ${canvasWidth}px;
              height: ${canvasHeight}px;
              margin: 0 auto;
              background: white;
              position: relative;
              border: 1px solid #e2e8f0;
            }
            .canvas-element {
              position: absolute;
              box-sizing: border-box;
            }
            .canvas-element.selected {
              outline: 2px solid #3b82f6;
            }
          </style>
        </head>
        <body>
          <div class="print-content">
            ${elements.map(element => {
              const style = {
                left: `${element.x}px`,
                top: `${element.y}px`,
                width: `${element.width}px`,
                height: `${element.height}px`,
                fontSize: `${element.fontSize || 14}px`,
                color: element.color || '#333333',
                backgroundColor: element.backgroundColor || 'transparent',
                border: element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor || '#e2e8f0'}` : 'none',
                fontWeight: element.fontWeight || 'normal',
                textAlign: element.textAlign || 'left',
                padding: '4px',
                overflow: 'hidden'
              };

              let content = element.text || element.content || '';
              if (element.type === 'image' && element.src) {
                content = `<img src="${element.src}" style="width: 100%; height: 100%; object-fit: cover;" alt="Image" />`;
              }

              return `<div class="canvas-element" style="${Object.entries(style).map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}:${value}`).join(';')}">${content}</div>`;
            }).join('')}
          </div>
        </body>
        </html>
      `;

      printWindow.document.write(printContent);
      printWindow.document.close();
      printWindow.print();
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