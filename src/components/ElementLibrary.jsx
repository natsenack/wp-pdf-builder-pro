import React, { useState } from 'react';

export const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
  const [expandedCategories, setExpandedCategories] = useState({
    'Texte': true,
    'WooCommerce - Factures': false,
    'WooCommerce - Produits': false,
    'WooCommerce - Devis': false,
    'Test': false
  });
  const [showHeaderTemplatesModal, setShowHeaderTemplatesModal] = useState(false);

  const toggleCategory = (categoryName) => {
    setExpandedCategories(prev => ({
      ...prev,
      [categoryName]: !prev[categoryName]
    }));
  };
  const elementCategories = [
    {
      name: 'Texte',
      elements: [
        { type: 'text', label: 'Texte Simple', icon: '📝', description: 'Ajouter du texte' },
        { type: 'text', label: 'Titre', icon: '🏷️', description: 'Titre de section', defaultProps: { fontSize: 24, fontWeight: 'bold' } },
        { type: 'text', label: 'Sous-titre', icon: '📄', description: 'Sous-titre', defaultProps: { fontSize: 18, fontWeight: 'bold' } }
      ]
    },
    {
      name: 'WooCommerce - Factures',
      elements: [
        { type: 'woocommerce-invoice-number', label: 'Numéro de Facture', icon: '📄', description: 'Numéro de facture WooCommerce' },
        { type: 'woocommerce-invoice-date', label: 'Date de Facture', icon: '📅', description: 'Date de création de la facture' },
        { type: 'woocommerce-order-number', label: 'Numéro de Commande', icon: '🛒', description: 'Numéro de commande WooCommerce' },
        { type: 'woocommerce-order-date', label: 'Date de Commande', icon: '📅', description: 'Date de création de la commande' },
        { type: 'woocommerce-billing-address', label: 'Adresse de Facturation', icon: '🏠', description: 'Adresse de facturation du client' },
        { type: 'woocommerce-shipping-address', label: 'Adresse de Livraison', icon: '🚚', description: 'Adresse de livraison du client' },
        { type: 'woocommerce-customer-name', label: 'Nom du Client', icon: '👤', description: 'Nom complet du client' },
        { type: 'woocommerce-customer-email', label: 'Email du Client', icon: '📧', description: 'Adresse email du client' },
        { type: 'woocommerce-payment-method', label: 'Méthode de Paiement', icon: '💳', description: 'Méthode de paiement utilisée' },
        { type: 'woocommerce-order-status', label: 'Statut de Commande', icon: '📊', description: 'Statut actuel de la commande' }
      ]
    },
    {
      name: 'WooCommerce - Produits',
      elements: [
        { type: 'woocommerce-products-table', label: 'Tableau des Produits', icon: '📋', description: 'Tableau détaillé des produits commandés' },
        { type: 'woocommerce-products-simple', label: 'Liste Produits Simple', icon: '📝', description: 'Liste simple des produits sans totaux' },
        { type: 'woocommerce-subtotal', label: 'Sous-total', icon: '💰', description: 'Sous-total de la commande' },
        { type: 'woocommerce-discount', label: 'Remise', icon: '🏷️', description: 'Montant de la remise appliquée' },
        { type: 'woocommerce-shipping', label: 'Frais de Port', icon: '🚚', description: 'Coûts de livraison' },
        { type: 'woocommerce-taxes', label: 'Taxes', icon: '📊', description: 'Montant des taxes' },
        { type: 'woocommerce-total', label: 'Total', icon: '💵', description: 'Montant total de la commande' },
        { type: 'woocommerce-refund', label: 'Remboursement', icon: '↩️', description: 'Montant remboursé' },
        { type: 'woocommerce-fees', label: 'Frais Supplémentaires', icon: '💸', description: 'Frais supplémentaires' }
      ]
    },
    {
      name: 'WooCommerce - Devis',
      elements: [
        { type: 'woocommerce-quote-number', label: 'Numéro de Devis', icon: '📝', description: 'Numéro de devis WooCommerce' },
        { type: 'woocommerce-quote-date', label: 'Date de Devis', icon: '📅', description: 'Date de création du devis' },
        { type: 'woocommerce-quote-validity', label: 'Validité du Devis', icon: '⏰', description: 'Période de validité du devis' },
        { type: 'woocommerce-quote-notes', label: 'Notes du Devis', icon: '📝', description: 'Notes spécifiques au devis' }
      ]
    },
    {
      name: 'Médias',
      elements: [
        { type: 'image', label: 'Image', icon: '🖼️', description: 'Insérer une image' },
        { type: 'barcode', label: 'Code-barres', icon: '📊', description: 'Code-barres' },
        { type: 'qrcode', label: 'QR Code', icon: '📱', description: 'Code QR' }
      ]
    },
    {
      name: 'Données',
      elements: [
        { type: 'dynamic-text', label: 'Texte Dynamique', icon: '🔄', description: 'Texte avec variables' },
        { type: 'table', label: 'Tableau', icon: '📋', description: 'Tableau de données' }
      ]
    },
    {
      name: 'Test',
      elements: [
        { type: 'header-templates', label: 'Modèles d\'En-tête', icon: '🎨', description: 'Choisir un modèle d\'en-tête prédéfini' }
      ]
    }
  ];

  const handleElementClick = (elementType, defaultProps = {}) => {
    if (elementType === 'header-templates') {
      setShowHeaderTemplatesModal(true);
    } else {
      onToolSelect(`add-${elementType}`);
    }
  };

  const handleDragStart = (e, element) => {
    e.dataTransfer.setData('application/json', JSON.stringify({
      type: 'new-element',
      elementType: element.type,
      defaultProps: element.defaultProps || {}
    }));
    e.dataTransfer.effectAllowed = 'copy';
  };

  const handleHeaderTemplateSelect = (template) => {
    // Ici on peut ajouter la logique pour appliquer le modèle sélectionné
    setShowHeaderTemplatesModal(false);
    // Appliquer le contenu du modèle sélectionné
    onAddElement('text', {
      x: 50,
      y: 50,
      width: 300,
      height: 60,
      text: template.preview.replace('\\n', '\n'),
      fontSize: template.fontSize || 16,
      fontWeight: template.fontWeight || 'normal'
    });
  };

  const headerTemplates = [
    {
      id: 'classic',
      name: 'Classique',
      preview: '🏢 ENTREPRISE\n123 Rue de la Paix\n75000 Paris',
      fontSize: 14,
      fontWeight: 'bold'
    },
    {
      id: 'modern',
      name: 'Moderne',
      preview: '✨ ENTREPRISE MODERNE\nInnovation & Qualité\ncontact@entreprise.com',
      fontSize: 16,
      fontWeight: 'bold'
    },
    {
      id: 'minimal',
      name: 'Minimal',
      preview: 'ENTREPRISE\nAdresse • Téléphone • Email',
      fontSize: 12,
      fontWeight: 'normal'
    },
    {
      id: 'elegant',
      name: 'Élégant',
      preview: '🎩 Maison Élégante\nParis, France\nwww.entreprise.com',
      fontSize: 15,
      fontWeight: 'bold'
    }
  ];

  return (
    <>
      {/* Modale des modèles d'en-tête */}
      {showHeaderTemplatesModal && (
        <div className="modal-overlay" onClick={() => setShowHeaderTemplatesModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h3>🎨 Choisir un modèle d'en-tête</h3>
              <button className="modal-close" onClick={() => setShowHeaderTemplatesModal(false)}>×</button>
            </div>
            <div className="modal-body">
              <div className="templates-grid">
                {headerTemplates.map(template => (
                  <div
                    key={template.id}
                    className="template-item"
                    onClick={() => handleHeaderTemplateSelect(template)}
                  >
                    <div className="template-preview">
                      {template.preview.split('\n').map((line, index) => (
                        <div key={index} style={{
                          fontSize: template.fontSize,
                          fontWeight: template.fontWeight,
                          marginBottom: '4px',
                          whiteSpace: 'pre-wrap'
                        }}>
                          {line}
                        </div>
                      ))}
                    </div>
                    <div className="template-name">{template.name}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="element-library">
      <div className="library-header">
        <h3>📚 Bibliothèque</h3>
      </div>

      <div className="library-content">
        {elementCategories.map(category => (
          <div key={category.name} className="element-category">
            <div
              className="category-header"
              onClick={() => toggleCategory(category.name)}
            >
              <h4 className="category-title">{category.name}</h4>
              <span className={`category-toggle ${expandedCategories[category.name] ? 'expanded' : ''}`}>
                ▼
              </span>
            </div>
            {expandedCategories[category.name] && (
              <div className="element-grid">
                {category.elements.map(element => (
                  <div
                    key={`${element.type}-${element.label}`}
                    className={`element-item ${selectedTool === `add-${element.type}` ? 'selected' : ''}`}
                    onClick={() => handleElementClick(element.type, element.defaultProps)}
                    onDragStart={(e) => handleDragStart(e, element)}
                    draggable={true}
                    title={element.description}
                  >
                    <div className="element-icon">{element.icon}</div>
                    <div className="element-label">{element.label}</div>
                    <div className="element-description">{element.description}</div>
                  </div>
                ))}
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
    </>
  );
};