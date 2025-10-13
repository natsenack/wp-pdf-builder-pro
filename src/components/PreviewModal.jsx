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

    // Fonction helper pour générer le contenu des éléments spéciaux
    const getSpecialElementContent = (element) => {
      switch (element.type) {
        case 'company_logo':
          if (element.src) {
            return `<img src="${element.src}" style="width: 100%; height: 100%; object-fit: contain;" alt="Logo entreprise" />`;
          }
          return 'Logo Entreprise';

        case 'customer_info':
          return 'Informations Client<br/>Nom: Jean Dupont<br/>Email: jean@example.com<br/>Téléphone: +33 1 23 45 67 89';

        case 'company_info':
          return 'Ma Société SARL<br/>123 Rue de l\'Entreprise<br/>75001 Paris, France<br/>Tél: +33 1 23 45 67 89<br/>contact@masociete.com';

        case 'product_table':
          return `
            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
              <thead>
                <tr style="background-color: #f8f9fa;">
                  <th style="border: 1px solid #ddd; padding: 4px;">Produit</th>
                  <th style="border: 1px solid #ddd; padding: 4px;">Qté</th>
                  <th style="border: 1px solid #ddd; padding: 4px;">Prix</th>
                  <th style="border: 1px solid #ddd; padding: 4px;">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="border: 1px solid #ddd; padding: 4px;">Produit A</td>
                  <td style="border: 1px solid #ddd; padding: 4px;">2</td>
                  <td style="border: 1px solid #ddd; padding: 4px;">19.99€</td>
                  <td style="border: 1px solid #ddd; padding: 4px;">39.98€</td>
                </tr>
                <tr>
                  <td style="border: 1px solid #ddd; padding: 4px;">Produit B</td>
                  <td style="border: 1px solid #ddd; padding: 4px;">1</td>
                  <td style="border: 1px solid #ddd; padding: 4px;">29.99€</td>
                  <td style="border: 1px solid #ddd; padding: 4px;">29.99€</td>
                </tr>
              </tbody>
            </table>
          `;

        case 'document_type':
          return 'FACTURE';

        case 'divider':
          return '<hr style="width: 100%; border: none; border-top: 1px solid #d1d5db; margin: 0;" />';

        default:
          return element.type;
      }
    };

    // Ouvrir l'aperçu dans une nouvelle fenêtre pour l'impression
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    if (printWindow) {
      // Générer le contenu HTML pour l'impression avec zoom = 1
      const printContent = `
        <div class="print-canvas" style="
          width: ${canvasWidth}px;
          height: ${canvasHeight}px;
          margin: 0 auto;
          border: none;
          background: white;
          position: relative;
          overflow: hidden;
          box-sizing: border-box;
        ">
          ${elements
            .map(element => {
              // Traiter différemment les éléments WooCommerce
              if (element.type.startsWith('woocommerce-')) {
                const getWCLabel = (type) => {
                  const labels = {
                    'woocommerce-invoice-number': 'Numéro Facture',
                    'woocommerce-invoice-date': 'Date Facture',
                    'woocommerce-order-number': 'N° Commande',
                    'woocommerce-order-date': 'Date Commande',
                    'woocommerce-billing-address': 'Adresse Facturation',
                    'woocommerce-shipping-address': 'Adresse Livraison',
                    'woocommerce-customer-name': 'Nom Client',
                    'woocommerce-customer-email': 'Email Client',
                    'woocommerce-payment-method': 'Paiement',
                    'woocommerce-order-status': 'Statut',
                    'woocommerce-products-table': 'Tableau Produits',
                    'woocommerce-products-simple': 'Liste Produits',
                    'woocommerce-subtotal': 'Sous-total',
                    'woocommerce-discount': 'Remise',
                    'woocommerce-shipping': 'Livraison',
                    'woocommerce-taxes': 'Taxes',
                    'woocommerce-total': 'Total',
                    'woocommerce-refund': 'Remboursement',
                    'woocommerce-fees': 'Frais',
                    'woocommerce-quote-number': 'N° Devis',
                    'woocommerce-quote-date': 'Date Devis',
                    'woocommerce-quote-validity': 'Validité',
                    'woocommerce-quote-notes': 'Notes Devis'
                  };
                  return labels[type] || 'Élément WC';
                };

                return `
                  <div style="
                    position: absolute;
                    left: ${element.x}px;
                    top: ${element.y}px;
                    width: ${element.width}px;
                    height: ${element.height}px;
                    font-size: ${element.fontSize || 14}px;
                    font-family: ${element.fontFamily || 'Arial'};
                    color: ${element.color || '#333333'};
                    font-weight: ${element.fontWeight || 'normal'};
                    background-color: ${element.backgroundColor || '#ffffff'};
                    border: ${element.borderWidth ? `${element.borderWidth}px ${element.borderStyle || 'solid'} ${element.borderColor || '#dddddd'}` : '1px solid #dddddd'};
                    border-radius: ${element.borderRadius || 0}px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                    word-break: break-word;
                    overflow: hidden;
                  ">
                    ${getWCLabel(element.type)}
                  </div>
                `;
              } else {
                // Gérer les éléments spéciaux et normaux
                return `
                  <div style="
                    position: absolute;
                    left: ${element.x}px;
                    top: ${element.y}px;
                    width: ${element.width}px;
                    height: ${element.height}px;
                    font-size: ${element.fontSize || 14}px;
                    font-family: ${element.fontFamily || 'Arial'};
                    color: ${element.color || '#1e293b'};
                    background-color: ${element.backgroundColor || 'transparent'};
                    border: ${element.borderWidth ? `${element.borderWidth}px ${element.borderStyle || 'solid'} ${element.borderColor || 'transparent'}` : 'none'};
                    border-radius: ${element.borderRadius || 0}px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                    word-break: break-word;
                    overflow: hidden;
                  ">
                    ${getSpecialElementContent(element)}
                  </div>
                `;
              }
            }).join('')}
        </div>
      `;

      console.log('Generated printContent:', printContent);

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
              background: #f8f9fa;
            }
            .print-container {
              background: white;
              border: 1px solid #e2e8f0;
              border-radius: 4px;
              padding: 20px;
              max-width: ${canvasWidth + 40}px;
              margin: 0 auto;
              position: relative;
            }
            .print-canvas {
              width: ${canvasWidth}px !important;
              height: ${canvasHeight}px !important;
              margin: 0 auto;
              border: none !important;
              background: white;
              position: relative;
              overflow: hidden;
            }
            @media print {
              body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
              }
              .print-container {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                max-width: none !important;
              }
              .print-canvas {
                width: ${canvasWidth}px !important;
                height: ${canvasHeight}px !important;
                margin: 0 auto !important;
                border: none !important;
                background: white !important;
                position: relative !important;
                overflow: hidden !important;
                box-sizing: border-box !important;
                transform: scale(1.1) !important;
                transform-origin: center top !important;
              }
              @page {
                size: A4;
                margin: 1cm !important; /* Marges équilibrées pour centrer le contenu */
              }
              * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
              }
            }
          </style>
        </head>
        <body>
          <div class="print-container">
            ${printContent}
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