import React, { useRef, useCallback } from 'react';
import { useResize } from '../hooks/useResize';

/**
 * Fonction utilitaire pour obtenir le label d'un élément WooCommerce
 */
const getElementLabel = (type) => {
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

/**
 * Hook personnalisé pour gérer les éléments WooCommerce
 */
export const useWooCommerceElements = () => {
  const getElementDefaults = (type) => {
    const defaults = {
      width: 200,
      height: 60,
      fontSize: 14,
      fontFamily: 'Arial, sans-serif',
      color: '#333333',
      backgroundColor: '#ffffff',
      borderColor: '#dddddd',
      borderWidth: 1,
      borderRadius: 4,
      padding: 8
    };

    // Ajustements spécifiques selon le type
    switch (type) {
      case 'woocommerce-billing-address':
      case 'woocommerce-shipping-address':
        defaults.height = 100;
        break;
      case 'woocommerce-products-table':
        defaults.width = 400;
        defaults.height = 150;
        break;
      case 'woocommerce-products-simple':
        defaults.width = 350;
        defaults.height = 120;
        break;
      case 'woocommerce-invoice-number':
      case 'woocommerce-order-number':
      case 'woocommerce-quote-number':
        defaults.width = 150;
        defaults.height = 40;
        break;
      default:
        break;
    }

    return defaults;
  };

  const validateElement = (element) => {
    // Validation basique des propriétés requises
    return element && element.type && element.id;
  };

  return {
    getElementDefaults,
    validateElement,
    getElementLabel
  };
};

/**
 * Composant pour gérer les éléments WooCommerce dans le canvas
 * Ce composant gère l'affichage et le rendu des éléments WooCommerce
 */
export const WooCommerceElement = ({
  element,
  isSelected,
  onSelect,
  onUpdate,
  dragAndDrop,
  zoom = 1
}) => {
  const elementRef = useRef(null);

  const resize = useResize({
    onElementResize: (newRect) => {
      onUpdate({
        x: newRect.x,
        y: newRect.y,
        width: newRect.width,
        height: newRect.height
      });
    },
    snapToGrid: true,
    gridSize: 10
  });

  const handleMouseDown = (e) => {
    e.stopPropagation();

    // Calculer les coordonnées relatives au canvas (en tenant compte du zoom)
    const canvas = elementRef.current.closest('.canvas-zoom-wrapper');
    if (!canvas) return;

    const canvasRect = canvas.getBoundingClientRect();
    const elementRect = elementRef.current.getBoundingClientRect();

    // Ajuster pour le zoom - les coordonnées doivent être relatives au canvas non-zoomé
    const clickX = (e.clientX - canvasRect.left) / zoom;
    const clickY = (e.clientY - canvasRect.top) / zoom;

    const handleSize = 8 / zoom; // Ajuster la taille des poignées pour le zoom

    // Poignées de redimensionnement (coordonnées relatives au canvas)
    const handles = [
      { name: 'nw', x: element.x, y: element.y },
      { name: 'ne', x: element.x + element.width, y: element.y },
      { name: 'sw', x: element.x, y: element.y + element.height },
      { name: 'se', x: element.x + element.width, y: element.y + element.height },
      { name: 'n', x: element.x + element.width / 2, y: element.y },
      { name: 's', x: element.x + element.width / 2, y: element.y + element.height },
      { name: 'w', x: element.x, y: element.y + element.height / 2 },
      { name: 'e', x: element.x + element.width, y: element.y + element.height / 2 }
    ];

    for (const handle of handles) {
      if (
        clickX >= handle.x - handleSize/2 &&
        clickX <= handle.x + handleSize/2 &&
        clickY >= handle.y - handleSize/2 &&
        clickY <= handle.y + handleSize/2
      ) {
        resize.handleResizeStart(e, handle.name, {
          x: element.x,
          y: element.y,
          width: element.width,
          height: element.height
        }, canvasRect, zoom);
        return;
      }
    }

    // Si on clique ailleurs sur l'élément, commencer le drag
    if (dragAndDrop && dragAndDrop.handleMouseDown) {
      const canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      const canvasRect = canvas.getBoundingClientRect();
      
      dragAndDrop.handleMouseDown(e, element.id, {
        left: element.x,
        top: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    }
  };

  const handleClick = (e) => {
    e.stopPropagation();
    if (!isSelected) {
      onSelect(element.id);
    }
  };

  const baseStyle = {
    position: 'absolute',
    left: element.x * zoom,
    top: element.y * zoom,
    width: element.width * zoom,
    height: element.height * zoom,
    cursor: isSelected ? 'move' : 'pointer',
    userSelect: 'none',
    border: isSelected ? '2px solid #007cba' : '1px solid #ddd',
    backgroundColor: element.backgroundColor || '#ffffff',
    color: element.color || '#333333',
    fontSize: (element.fontSize || 14) * zoom,
    fontFamily: element.fontFamily || 'Arial, sans-serif',
    padding: (element.padding || 8) * zoom,
    borderRadius: (element.borderRadius || 4) * zoom,
    boxSizing: 'border-box',
    overflow: 'hidden'
  };

  return (
    <>
      <div
        ref={elementRef}
        style={baseStyle}
        onClick={handleClick}
        onMouseDown={handleMouseDown}
      >
        <div style={{
          fontWeight: 'bold',
          textTransform: 'uppercase',
          letterSpacing: '0.5px'
        }}>
          {getElementLabel(element.type)}
        </div>
        <div style={{
          whiteSpace: 'pre-line',
          lineHeight: '1.4',
          color: '#666'
        }}>
          [Contenu dynamique WooCommerce]
        </div>
      </div>

      {/* Poignées de redimensionnement */}
      {isSelected && (
        <>
          {/* Coin supérieur gauche */}
          <div
            className="resize-handle nw"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'nw-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'nw', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Coin supérieur droit */}
          <div
            className="resize-handle ne"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'ne-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'ne', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Coin inférieur gauche */}
          <div
            className="resize-handle sw"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'sw-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'sw', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Coin inférieur droit */}
          <div
            className="resize-handle se"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'se-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'se', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Bord supérieur */}
          <div
            className="resize-handle n"
            style={{
              position: 'absolute',
              left: (element.x + element.width / 2) * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'n-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'n', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Bord inférieur */}
          <div
            className="resize-handle s"
            style={{
              position: 'absolute',
              left: (element.x + element.width / 2) * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 's-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 's', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Bord gauche */}
          <div
            className="resize-handle w"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: (element.y + element.height / 2) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'w-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'w', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Bord droit */}
          <div
            className="resize-handle e"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: (element.y + element.height / 2) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'e-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'e', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
        </>
      )}
    </>
  );
};