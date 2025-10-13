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
    console.log('handlePrint called with elements:', elements);
    console.log('canvasWidth:', canvasWidth, 'canvasHeight:', canvasHeight);

    // Debug: Log details of each element
    elements.forEach((element, index) => {
      console.log(`Element ${index}:`, {
        type: element.type,
        text: element.text,
        src: element.src,
        content: element.content,
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    });

    // Préparer les données pour l'AJAX
    const formData = new FormData();
    formData.append('action', 'pdf_builder_generate_pdf');
    formData.append('nonce', pdfBuilderAjax.nonce);
    formData.append('elements', JSON.stringify(elements));
    formData.append('canvasWidth', canvasWidth);
    formData.append('canvasHeight', canvasHeight);

    // Afficher un indicateur de chargement
    const printButton = document.querySelector('.btn-primary');
    const originalText = printButton.textContent;
    printButton.textContent = '⏳ Génération PDF...';
    printButton.disabled = true;

    // Envoyer la requête AJAX
    fetch(pdfBuilderAjax.ajaxurl, {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Erreur réseau: ' + response.status);
      }
      return response.blob();
    })
    .then(blob => {
      // Créer un URL pour le blob PDF
      const pdfUrl = URL.createObjectURL(blob);

      // Ouvrir le PDF dans une nouvelle fenêtre ou le télécharger
      const link = document.createElement('a');
      link.href = pdfUrl;
      link.download = 'pdf-builder-pro-document.pdf';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      // Libérer l'URL du blob
      URL.revokeObjectURL(pdfUrl);

      console.log('PDF généré et téléchargé avec succès');
    })
    .catch(error => {
      console.error('Erreur lors de la génération du PDF:', error);
      alert('Erreur lors de la génération du PDF: ' + error.message);
    })
    .finally(() => {
      // Restaurer le bouton
      printButton.textContent = originalText;
      printButton.disabled = false;
    });
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
          <div className="preview-content" style={{
            padding: '20px',
            background: '#f8f9fa',
            borderRadius: '4px'
          }}>
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