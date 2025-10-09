import React, { useState } from 'react';
import VariableManager from '../utilities/VariableManager';
import '../styles/ElementLibrary.css';

export const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
  const [showHeaderTemplatesModal, setShowHeaderTemplatesModal] = useState(false);

  // Éléments restructurés selon l'architecture du plugin concurrent
  // Utilise 'text' pour les éléments texte et 'field' pour les subfields
  const elementCategories = [
    {
      name: 'Informations Commande',
      elements: [
        {
          type: 'field', // Type 'field' pour subfield comme dans FieldFactory::GetSubField()
          fieldID: 'order_number',
          label: 'Numéro Commande',
          icon: '📋',
          description: 'Numéro de la commande',
          previewContent: '[order_number]',
          defaultProperties: {
            text: '[order_number]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'order_date',
          label: 'Date Commande',
          icon: '📅',
          description: 'Date de la commande',
          previewContent: '[order_date]',
          defaultProperties: {
            text: '[order_date]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'order_subtotal',
          label: 'Sous-total',
          icon: '💵',
          description: 'Sous-total de la commande',
          previewContent: '[order_subtotal]',
          defaultProperties: {
            text: '[order_subtotal]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'order_tax',
          label: 'TVA',
          icon: '�',
          description: 'Montant de la TVA',
          previewContent: '[order_tax]',
          defaultProperties: {
            text: '[order_tax]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'payment_method',
          label: 'Méthode Paiement',
          icon: '💳',
          description: 'Méthode de paiement utilisée',
          previewContent: '[payment_method]',
          defaultProperties: {
            text: '[payment_method]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'shipping_method',
          label: 'Méthode Livraison',
          icon: '🚚',
          description: 'Méthode de livraison',
          previewContent: '[shipping_method]',
          defaultProperties: {
            text: '[shipping_method]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'text', // Type 'text' pour texte libre comme dans FieldFactory::GetField('text')
          fieldID: 'order_info_combined',
          label: 'Info Commande Combinée',
          icon: '📄',
          description: 'Informations combinées de commande',
          previewContent: 'Commande [order_number] - Date: [order_date]\nTotal: [order_total]',
          defaultProperties: {
            text: 'Commande [order_number] - Date: [order_date]\nTotal: [order_total]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        }
      ]
    },
    {
      name: 'Informations Client',
      elements: [
        {
          type: 'field',
          fieldID: 'customer_name',
          label: 'Nom Client',
          icon: '👤',
          description: 'Nom du client',
          previewContent: '[customer_name]',
          defaultProperties: {
            text: '[customer_name]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'customer_email',
          label: 'Email Client',
          icon: '📧',
          description: 'Adresse email du client',
          previewContent: '[customer_email]',
          defaultProperties: {
            text: '[customer_email]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'billing_address',
          label: 'Adresse Facturation',
          icon: '📍',
          description: 'Adresse de facturation',
          previewContent: '[billing_address]',
          defaultProperties: {
            text: '[billing_address]',
            fontSize: 11,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'shipping_address',
          label: 'Adresse Livraison',
          icon: '🏠',
          description: 'Adresse de livraison',
          previewContent: '[shipping_address]',
          defaultProperties: {
            text: '[shipping_address]',
            fontSize: 11,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'text',
          fieldID: 'customer_info_combined',
          label: 'Info Client Combinée',
          icon: '👥',
          description: 'Informations client combinées',
          previewContent: '[customer_name]\n[customer_email]\n[billing_address]',
          defaultProperties: {
            text: '[customer_name]\n[customer_email]\n[billing_address]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        }
      ]
    },
    {
      name: 'Éléments de Base',
      elements: [
        {
          type: 'text',
          fieldID: 'custom_text',
          label: 'Texte Libre',
          icon: '📝',
          description: 'Champ de texte personnalisable',
          previewContent: 'Votre texte ici...',
          defaultProperties: {
            text: 'Votre texte ici...',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'rectangle',
          fieldID: 'shape_rectangle',
          label: 'Rectangle',
          icon: '▭',
          description: 'Forme rectangulaire',
          previewContent: '',
          defaultProperties: {
            fillColor: '#e0e0e0',
            strokeColor: '#000000',
            strokeWidth: 1
          }
        }
      ]
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
          {elementCategories.map((category, categoryIndex) => (
            <div key={categoryIndex} className="element-category">
              <h4 className="category-title">{category.name}</h4>
              <div className="elements-grid">
                {category.elements.map((element, elementIndex) => (
                  <div
                    key={elementIndex}
                    className={`element-item ${selectedTool === element.type ? 'selected' : ''}`}
                    onClick={() => {
                      onToolSelect(element.type);
                      onAddElement(element.type, {
                        x: 50 + (elementIndex * 20),
                        y: 50 + (categoryIndex * 100),
                        width: element.type === 'rectangle' ? 200 : 250,
                        height: element.type === 'rectangle' ? 100 : 60,
                        fieldID: element.fieldID, // Ajout du fieldID comme dans FieldDTO
                        ...element.defaultProperties
                      });
                    }}
                    title={element.description}
                  >
                    <div className="element-icon">{element.icon}</div>
                    <div className="element-info">
                      <div className="element-label">{element.label}</div>
                      <div className="element-preview">
                        {element.previewContent ? (
                          <div className="preview-text">
                            {VariableManager.processTextForPreview(element.previewContent).split('\n').map((line, i) => (
                              <div key={i} className="preview-line">{line}</div>
                            ))}
                          </div>
                        ) : (
                          <div className="preview-shape">
                            {element.type === 'rectangle' && (
                              <div
                                className="shape-preview"
                                style={{
                                  width: '40px',
                                  height: '20px',
                                  backgroundColor: element.defaultProperties?.fillColor || '#e0e0e0',
                                  border: `${element.defaultProperties?.strokeWidth || 1}px solid ${element.defaultProperties?.strokeColor || '#000'}`
                                }}
                              />
                            )}
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
    </>
  );
};
