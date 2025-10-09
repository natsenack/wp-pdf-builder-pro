import React from 'react';

/**
 * Composant pour gérer les éléments WooCommerce dans le canvas
 * Ce composant gère l'affichage et le rendu des éléments WooCommerce
 */
export const WooCommerceElement = ({ element, isSelected, onSelect, onUpdate }) => {
  const handleClick = (e) => {
    e.stopPropagation();
    onSelect(element.id);
  };

  const renderElement = () => {
    const baseStyle = {
      position: 'absolute',
      left: element.x,
      top: element.y,
      width: element.width,
      height: element.height,
      border: isSelected ? '2px solid #007cba' : '1px solid #ddd',
      backgroundColor: '#ffffff',
      padding: '8px',
      fontSize: '14px',
      fontFamily: 'Arial, sans-serif',
      cursor: 'pointer',
      userSelect: 'none',
      borderRadius: '4px',
      boxShadow: isSelected ? '0 0 8px rgba(0,123,186,0.3)' : '0 2px 4px rgba(0,0,0,0.1)',
      overflow: 'hidden'
    };

    const getPlaceholderText = () => {
      switch (element.type) {
        case 'woocommerce-invoice-number':
          return 'INV-001';
        case 'woocommerce-invoice-date':
          return '2024-01-15';
        case 'woocommerce-order-number':
          return '#1234';
        case 'woocommerce-order-date':
          return '2024-01-15 10:30';
        case 'woocommerce-billing-address':
          return 'John Doe\n123 Main St\nCity, State 12345\nCountry';
        case 'woocommerce-shipping-address':
          return 'John Doe\n456 Shipping Ave\nCity, State 12345\nCountry';
        case 'woocommerce-customer-name':
          return 'John Doe';
        case 'woocommerce-customer-email':
          return 'john.doe@example.com';
        case 'woocommerce-payment-method':
          return 'Carte de crédit';
        case 'woocommerce-order-status':
          return 'Traitée';
        case 'woocommerce-products-table':
          return 'Tableau des produits\n- Produit 1 x1 $10.00\n- Produit 2 x2 $20.00\nTotal: $50.00';
        case 'woocommerce-subtotal':
          return '$45.00';
        case 'woocommerce-discount':
          return '-$5.00';
        case 'woocommerce-shipping':
          return '$5.00';
        case 'woocommerce-taxes':
          return '$2.25';
        case 'woocommerce-total':
          return '$47.25';
        case 'woocommerce-refund':
          return '-$10.00';
        case 'woocommerce-fees':
          return '$1.50';
        case 'woocommerce-quote-number':
          return 'QUO-001';
        case 'woocommerce-quote-date':
          return '2024-01-15';
        case 'woocommerce-quote-validity':
          return '30 jours';
        case 'woocommerce-quote-notes':
          return 'Conditions spéciales du devis';
        default:
          return 'Élément WooCommerce';
      }
    };

    return (
      <div
        style={baseStyle}
        onClick={handleClick}
        title={`${element.type} - Cliquez pour modifier`}
      >
        <div style={{
          fontSize: '12px',
          color: '#666',
          marginBottom: '4px',
          fontWeight: 'bold',
          textTransform: 'uppercase',
          letterSpacing: '0.5px'
        }}>
          {getElementLabel(element.type)}
        </div>
        <div style={{
          whiteSpace: 'pre-line',
          lineHeight: '1.4',
          color: '#333'
        }}>
          {getPlaceholderText()}
        </div>
      </div>
    );
  };

  return renderElement();
};

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
    'woocommerce-products-table': 'Produits',
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