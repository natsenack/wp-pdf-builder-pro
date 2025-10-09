import React, { useState } from 'react';

export const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
  const [expandedCategories, setExpandedCategories] = useState({
    'Mises en Page': true,
    'Médias': false,
    'Données Dynamiques': false,
    'Éléments Avancés': false,
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
      name: 'Mises en Page',
      elements: [
        { type: 'layout-header', label: 'En-tête', icon: '📄', description: 'Section d\'en-tête pour le document' },
        { type: 'layout-footer', label: 'Pied de Page', icon: '📄', description: 'Section de pied de page' },
        { type: 'layout-sidebar', label: 'Barre Latérale', icon: '📄', description: 'Barre latérale' },
        { type: 'layout-section', label: 'Section', icon: '📄', description: 'Section de contenu' },
        { type: 'layout-container', label: 'Conteneur', icon: '📦', description: 'Conteneur flexible' }
      ]
    },
    {
      name: 'Médias',
      elements: [
        { type: 'image', label: 'Image', icon: '🖼️', description: 'Insérer une image' },
        { type: 'image-upload', label: 'Télécharger Image', icon: '📤', description: 'Uploader et insérer une image' },
        { type: 'logo', label: 'Logo', icon: '🏷️', description: 'Logo de l\'entreprise' },
        { type: 'barcode', label: 'Code-barres', icon: '📊', description: 'Code-barres' },
        { type: 'qrcode', label: 'QR Code', icon: '📱', description: 'Code QR' },
        { type: 'qrcode-dynamic', label: 'QR Code Dynamique', icon: '🔗', description: 'QR Code avec contenu dynamique' },
        { type: 'icon', label: 'Icône', icon: '🎯', description: 'Icône vectorielle' }
      ]
    },
    {
      name: 'Données Dynamiques',
      elements: [
        { type: 'dynamic-text', label: 'Texte Dynamique', icon: '🔄', description: 'Texte avec variables' },
        { type: 'formula', label: 'Formule', icon: '🧮', description: 'Calcul mathématique' },
        { type: 'conditional-text', label: 'Texte Conditionnel', icon: '❓', description: 'Texte affiché selon conditions' },
        { type: 'counter', label: 'Compteur', icon: '🔢', description: 'Compteur automatique' },
        { type: 'date-dynamic', label: 'Date Dynamique', icon: '📅', description: 'Date avec format personnalisé' },
        { type: 'currency', label: 'Devise', icon: '💱', description: 'Format monétaire' },
        { type: 'table-dynamic', label: 'Tableau Dynamique', icon: '📊', description: 'Tableau avec données variables' }
      ]
    },
    {
      name: 'Éléments Avancés',
      elements: [
        { type: 'gradient-box', label: 'Boîte Dégradé', icon: '🌈', description: 'Boîte avec dégradé de couleur' },
        { type: 'shadow-box', label: 'Boîte avec Ombre', icon: '📦', description: 'Boîte avec effet d\'ombre' },
        { type: 'rounded-box', label: 'Boîte Arrondie', icon: '🔄', description: 'Boîte avec coins arrondis' },
        { type: 'border-box', label: 'Boîte avec Bordure', icon: '🔲', description: 'Boîte avec bordure stylisée' },
        { type: 'background-pattern', label: 'Motif d\'Arrière-plan', icon: '🎨', description: 'Arrière-plan avec motif' },
        { type: 'watermark', label: 'Filigrane', icon: '💧', description: 'Texte ou image en filigrane' },
        { type: 'progress-bar', label: 'Barre de Progression', icon: '📊', description: 'Barre de progression visuelle' }
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