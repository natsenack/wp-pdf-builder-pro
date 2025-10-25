import { useState } from 'react';
import '../../scss/styles/ElementLibrary.css';

const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
  const [showHeaderTemplatesModal, setShowHeaderTemplatesModal] = useState(false);

  // Bibliothèque d'éléments - boutons simples
  const elements = [
    {
      type: 'product_table',
      fieldID: 'products_table',
      label: 'Tableau Produits',
      icon: '📋',
      description: 'Tableau des produits commandés',
      defaultProperties: {
        x: 30,
        y: 270,
        width: 530,
        height: 200,
        showBorders: true,
        tableFormat: 'full',
        tableColorPrimary: '#667eea',
        tableColorSecondary: '#e8ebff',
        columns: {
          image: false,
          name: true,
          sku: false,
          quantity: true,
          price: true,
          total: true
        },
        showSubtotal: true,
        showShipping: true,
        showTaxes: true,
        showDiscount: false,
        showTotal: true,
        showTableBorder: false
      }
    },
    {
      type: 'customer_info',
      fieldID: 'customer_info',
      label: 'Fiche Client',
      icon: '👤',
      description: 'Informations détaillées du client',
      defaultProperties: {
        showHeaders: true,
        showBorders: false,
        fields: ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'],
        layout: 'vertical', // 'vertical' ou 'horizontal'
        showLabels: true,
        labelStyle: 'bold', // 'normal', 'bold', 'uppercase'
        spacing: 8 // espacement en pixels
      }
    },
    {
      type: 'company_logo',
      fieldID: 'company_logo',
      label: 'Logo Entreprise',
      icon: '🏢',
      description: 'Logo et identité visuelle de l\'entreprise',
      defaultProperties: {
        src: '', // Propriété principale pour l'image (compatible avec les éléments image)
        imageUrl: '', // Propriété de fallback pour compatibilité
        width: 150,
        height: 80,
        alignment: 'left', // 'left', 'center', 'right'
        fit: 'contain', // 'contain', 'cover', 'fill'
        autoResize: true, // Redimensionnement automatique selon les dimensions naturelles
        showBorder: false,
        borderRadius: 0,
        borderWidth: 0,
        borderStyle: 'solid',
        borderColor: 'transparent'
      }
    },
    {
      type: 'company_info',
      fieldID: 'company_info',
      label: 'Informations Entreprise',
      icon: '[D]',
      description: 'Nom, adresse, contact et TVA de l\'entreprise',
      defaultProperties: {
        showHeaders: false,
        showBorders: false,
        fields: ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'],
        layout: 'vertical',
        showLabels: false,
        labelStyle: 'normal',
        spacing: 4,
        fontSize: 12,
        fontFamily: 'Arial',
        fontWeight: 'normal',
        textAlign: 'left', // 'left', 'center', 'right'
        // Nouvelles propriétés pour mapping WooCommerce
        template: 'default', // 'default', 'commercial', 'legal', 'minimal'
        showCompanyName: true,
        showAddress: true,
        showContact: true,
        showLegal: true,
        // Données de prévisualisation
        previewCompanyName: 'Ma Société SARL',
        previewAddress: '123 Rue de l\'Entreprise\n75001 Paris, France',
        previewPhone: '+33 1 23 45 67 89',
        previewEmail: 'contact@masociete.com',
        previewWebsite: 'www.masociete.com',
        previewVat: 'FR12345678901',
        previewSiret: '12345678901234',
        previewRcs: 'RCS Paris 123456789'
      }
    },
    {
      type: 'order_number',
      fieldID: 'order_number',
      label: 'Numéro de Commande',
      icon: '🔢',
      description: 'Référence de commande avec date et formatage configurable',
      defaultProperties: {
        // Formatage
        format: 'Commande #{order_number} - {order_date}',
        availableFormats: [
          'Commande #{order_number} - {order_date}',
          'CMD-{order_year}-{order_number}',
          'Facture N°{order_number} du {order_date}',
          'Bon de livraison #{order_number}',
          '{order_number}/{order_year}',
          'N° {order_number} - {order_date}'
        ],

        // Style
        fontSize: 14,
        fontFamily: 'Arial',
        fontWeight: 'bold',
        textAlign: 'right', // 'left', 'center', 'right'
        color: '#333333',
        labelColor: '#666666',
        lineHeight: 1.2,

        // Affichage
        showLabel: true,
        labelText: 'N° de commande:',

        // Bordures et fond
        backgroundColor: 'transparent',
        borderWidth: 0,
        borderStyle: 'solid',
        borderColor: '#e5e7eb',
        borderRadius: 0,

        // Données de prévisualisation
        previewOrderNumber: '12345',
        previewOrderDate: '15/10/2025',
        previewOrderYear: '2025',
        previewOrderMonth: '10',
        previewOrderDay: '15'
      }
    },
    {
      type: 'dynamic-text',
      fieldID: 'dynamic_text',
      label: 'Texte Dynamique',
      icon: '📝',
      description: 'Texte avec variables dynamiques',
      defaultProperties: {
        template: 'total_only',
        customContent: '{{order_total}} €',
        fontSize: 14,
        fontFamily: 'Arial',
        fontWeight: 'normal',
        textAlign: 'left',
        color: '#333333'
      }
    },
    {
      type: 'mentions',
      fieldID: 'mentions',
      label: 'Mentions légales',
      icon: '📄',
      description: 'Informations légales (email, SIRET, téléphone, etc.)',
      defaultProperties: {
        showEmail: true,
        showPhone: true,
        showSiret: true,
        showVat: false,
        showAddress: false,
        showWebsite: false,
        showCustomText: false,
        customText: '',
        fontSize: 8,
        fontFamily: 'Arial',
        fontWeight: 'normal',
        textAlign: 'center',
        color: '#666666',
        lineHeight: 1.2,
        separator: ' • ',
        layout: 'horizontal' // 'horizontal' ou 'vertical'
      }
    }
  ];

  const handleHeaderTemplateSelect = (template) => {
    // Ici on peut ajouter la logique pour appliquer le modèle sélectionné
    setShowHeaderTemplatesModal(false);
    // Appliquer le contenu du modèle sélectionné
    onAddElement('text', {
      x: 50,
      y: 50,
      width: 300,
      height: 60,
      text: template.sample.replace('\\n', '\n'),
      fontSize: template.fontSize || 16,
      fontWeight: template.fontWeight || 'normal'
    });
  };

  const headerTemplates = [
    {
      id: 'classic',
      name: 'Classique',
      sample: '🏢 ENTREPRISE\n123 Rue de la Paix\n75000 Paris',
      fontSize: 14,
      fontWeight: 'bold'
    },
    {
      id: 'modern',
      name: 'Moderne',
      sample: '✨ ENTREPRISE MODERNE\nInnovation & Qualité\ncontact@entreprise.com',
      fontSize: 16,
      fontWeight: 'bold'
    },
    {
      id: 'minimal',
      name: 'Minimal',
      sample: 'ENTREPRISE\nAdresse • Téléphone • Email',
      fontSize: 12,
      fontWeight: 'normal'
    },
    {
      id: 'elegant',
      name: 'Élégant',
      sample: '🎩 Maison Élégante\nParis, France\nwww.entreprise.com',
      fontSize: 15,
      fontWeight: 'bold'
    }
  ];

  // Gestionnaire pour le drag start
  const handleDragStart = (e, element, index) => {
    e.dataTransfer.setData('application/json', JSON.stringify({
      type: 'element',
      elementType: element.type,
      defaultProperties: {
        x: 50 + (index * 20),
        y: 100 + (index * 20),
        width: 300,
        height: 150,
        ...element.defaultProperties
      }
    }));
    e.dataTransfer.effectAllowed = 'copy';
  };

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
                    <div className="template-sample">
                      {template.sample.split('\n').map((line, index) => (
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
          <h3>📚 Bibliothèque d'Éléments</h3>
          <p className="library-subtitle">Cliquez sur les blocs pour les ajouter au canvas</p>
        </div>

        <div className="library-content">
          {/* Liste simple d'éléments avec boutons */}
          <div className="elements-list">
            {elements.map((element, index) => (
              <button
                key={index}
                className="element-button"
                draggable={true}
                onDragStart={(e) => handleDragStart(e, element, index)}
                onClick={() => {
                  onAddElement(element.type, {
                    x: 50 + (index * 20),
                    y: 100 + (index * 20),
                    width: 300,
                    height: 150,
                    ...element.defaultProperties
                  });
                }}
                title={element.description}
              >
                <span className="element-icon">{element.icon}</span>
                <div className="element-info">
                  <div className="element-label">{element.label}</div>
                  <div className="element-description">{element.description}</div>
                </div>
              </button>
            ))}
          </div>
        </div>
      </div>
    </>
  );
};

export default ElementLibrary;
