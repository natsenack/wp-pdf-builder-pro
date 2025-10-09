import React, { useState } from 'react';
import '../styles/ElementLibrary.css';

export const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
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
        showHeaders: true,
        showBorders: true,
        headers: ['Produit', 'Qté', 'Prix'],
        dataSource: 'order_items',
        columns: {
          image: true,
          name: true,
          sku: false,
          quantity: true,
          price: true,
          total: true
        },
        showSubtotal: false,
        showShipping: true,
        showTaxes: true,
        showDiscount: false,
        showTotal: false
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
        showBorders: true,
        fields: {
          name: true,
          email: true,
          phone: true,
          address: true,
          company: false,
          vat: false
        },
        layout: 'vertical', // 'vertical' ou 'horizontal'
        showLabels: true,
        labelStyle: 'bold', // 'normal', 'bold', 'uppercase'
        spacing: 'normal' // 'compact', 'normal', 'spacious'
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

  // Gestionnaire pour le drag start - REMOVED: plus d'éléments à dragger

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
          <h3>📚 Bibliothèque d'Éléments</h3>
          <p className="library-subtitle">Glissez les blocs vers le canvas pour les ajouter</p>
        </div>

        <div className="library-content">
          {/* Liste simple d'éléments avec boutons */}
          <div className="elements-list">
            {elements.map((element, index) => (
              <button
                key={index}
                className="element-button"
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