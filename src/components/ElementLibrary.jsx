import React, { useState } from 'react';
import VariableManager from '../utilities/VariableManager';
import '../styles/ElementLibrary.css';

export const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
  const [showHeaderTemplatesModal, setShowHeaderTemplatesModal] = useState(false);

  // Quelques éléments avec contenu réaliste pour commencer
  const elementCategories = [
    {
      name: 'Informations Commande',
      elements: [
        {
          type: 'order-info',
          label: 'Numéro & Date',
          icon: '📋',
          description: 'Numéro de commande et date',
          previewContent: 'Commande [order_number]\nDate: [order_date]',
          defaultProperties: {
            text: 'Commande [order_number]\nDate: [order_date]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'order-total',
          label: 'Total Commande',
          icon: '💰',
          description: 'Montant total de la commande',
          previewContent: 'Total: [order_total]',
          defaultProperties: {
            text: 'Total: [order_total]',
            fontSize: 14,
            fontFamily: 'Arial',
            fontWeight: 'bold'
          }
        }
      ]
    },
    {
      name: 'Informations Client',
      elements: [
        {
          type: 'customer-info',
          label: 'Nom Client',
          icon: '👤',
          description: 'Nom et email du client',
          previewContent: '[customer_name]\n[customer_email]',
          defaultProperties: {
            text: '[customer_name]\n[customer_email]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'billing-address',
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
        }
      ]
    },
    {
      name: 'Éléments de Base',
      elements: [
        {
          type: 'text',
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
