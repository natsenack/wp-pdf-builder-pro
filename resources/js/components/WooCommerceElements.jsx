import { useRef, useCallback } from 'react';
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
      borderStyle: 'solid',
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
        defaults.columns = {
          image: true,
          name: true,
          sku: true,
          quantity: true,
          price: true,
          total: true
        };
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
const WooCommerceElement = ({
  element,
  isSelected,
  onSelect,
  onUpdate,
  dragAndDrop,
  zoom = 1,
  canvasWidth,
  canvasHeight,
  orderData = {},
  onContextMenu,
  snapToGrid = true,
  gridSize = 10
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
    snapToGrid,
    gridSize,
    canvasWidth,
    canvasHeight
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
    border: isSelected
      ? `2px solid #007cba`
      : element.borderWidth > 0
        ? `${element.borderWidth || 1}px ${element.borderStyle || 'solid'} ${element.borderColor || 'transparent'}`
        : 'none',
    backgroundColor: element.backgroundColor || 'transparent',
    color: element.color || '#333333',
    fontSize: (element.fontSize || 14) * zoom,
    fontFamily: element.fontFamily || 'Arial, sans-serif',
    padding: (element.padding || 8) * zoom,
    borderRadius: (element.borderRadius || 4) * zoom,
    boxSizing: 'border-box',
    overflow: 'hidden',
    '--element-border-width': isSelected ? '2px' : (element.borderWidth > 0 ? `${element.borderWidth || 1}px` : '0px')
  };

  // Fonction pour obtenir le contenu dynamique selon le type d'élément
  const getElementContent = (type) => {
    switch (type) {
      case 'woocommerce-invoice-number':
        return orderData.invoice_number || 'INV-001';
      case 'woocommerce-invoice-date':
        return orderData.invoice_date || '15/10/2025';
      case 'woocommerce-order-number':
        return orderData.order_number || '#12345';
      case 'woocommerce-order-date':
        return orderData.order_date || '15/10/2025';
      case 'woocommerce-customer-name':
        return orderData.customer_name || 'John Doe';
      case 'woocommerce-customer-email':
        return orderData.customer_email || 'john.doe@example.com';
      case 'woocommerce-billing-address':
        return orderData.billing_address || '123 Rue de Test\n75001 Paris\nFrance';
      case 'woocommerce-shipping-address':
        return orderData.shipping_address || '456 Rue de Livraison\n75002 Paris\nFrance';
      case 'woocommerce-payment-method':
        return orderData.payment_method || 'Carte bancaire';
      case 'woocommerce-order-status':
        return orderData.order_status || 'Traitée';
      case 'woocommerce-subtotal':
        return orderData.subtotal || '45,00 €';
      case 'woocommerce-discount':
        return orderData.discount || '-5,00 €';
      case 'woocommerce-shipping':
        return orderData.shipping || '5,00 €';
      case 'woocommerce-taxes':
        return orderData.tax || '9,00 €';
      case 'woocommerce-total':
        return orderData.total || '54,00 €';
      case 'woocommerce-refund':
        return orderData.refund || '0,00 €';
      case 'woocommerce-fees':
        return orderData.fees || '1,50 €';
      case 'woocommerce-quote-number':
        return orderData.quote_number || 'QUO-001';
      case 'woocommerce-quote-date':
        return orderData.quote_date || '15/10/2025';
      case 'woocommerce-quote-validity':
        return orderData.quote_validity || '30 jours';
      case 'woocommerce-quote-notes':
        return orderData.quote_notes || 'Conditions spéciales : paiement à 30 jours.';
      case 'woocommerce-products-table':
        if (orderData.products && orderData.products.length > 0) {
          return orderData.products.map(product =>
            `${product.name} x${product.quantity} - ${product.total}`
          ).join('\n');
        }
        return 'Produit Test 1 x1 - 25,00 €\nProduit Test 2 x2 - 20,00 €';
      case 'woocommerce-products-simple':
        if (orderData.products && orderData.products.length > 0) {
          return orderData.products.map(product =>
            `${product.quantity}x ${product.name}`
          ).join('\n');
        }
        return '1x Produit Test 1\n2x Produit Test 2';
      default:
        return '[Contenu dynamique WooCommerce]';
    }
  };

  return (
    <>
      <div
        ref={elementRef}
        style={baseStyle}
        onClick={handleClick}
        onMouseDown={handleMouseDown}
        onContextMenu={onContextMenu}
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
          {getElementContent(element.type)}
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
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
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
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
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
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
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
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
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
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
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
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
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
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
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
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
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

export default WooCommerceElement;
