import React, { useState } from 'react';
import VariableManager from '../utilities/VariableManager';
import '../styles/ElementLibrary.css';

export const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
  const [showHeaderTemplatesModal, setShowHeaderTemplatesModal] = useState(false);

  // Système de blocs en grille pour les éléments
  const elementCategories = [
    {
      name: 'Éléments de Base',
      elements: [
        {
          type: 'text',
          fieldID: 'custom_text',
          label: 'Texte Libre',
          icon: '📝',
          description: 'Bloc de texte personnalisable',
          blockContent: 'Cliquez pour éditer ce texte...',
          defaultProperties: {
            text: 'Cliquez pour éditer ce texte...',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'text',
          fieldID: 'title_block',
          label: 'Titre',
          icon: '📄',
          description: 'Bloc de titre',
          blockContent: 'TITRE DU DOCUMENT',
          defaultProperties: {
            text: 'TITRE DU DOCUMENT',
            fontSize: 18,
            fontFamily: 'Arial',
            fontWeight: 'bold'
          }
        }
      ]
    },
    {
      name: 'Informations Commande',
      elements: [
        {
          type: 'field',
          fieldID: 'order_number',
          label: 'Numéro Commande',
          icon: '📋',
          description: 'Bloc numéro de commande',
          blockContent: '[order_number]',
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
          description: 'Bloc date de commande',
          blockContent: '[order_date]',
          defaultProperties: {
            text: '[order_date]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        },
        {
          type: 'field',
          fieldID: 'order_total',
          label: 'Total Commande',
          icon: '💰',
          description: 'Bloc montant total',
          blockContent: '[order_total]',
          defaultProperties: {
            text: '[order_total]',
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
          type: 'field',
          fieldID: 'customer_name',
          label: 'Nom Client',
          icon: '👤',
          description: 'Bloc nom du client',
          blockContent: '[customer_name]',
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
          description: 'Bloc email du client',
          blockContent: '[customer_email]',
          defaultProperties: {
            text: '[customer_email]',
            fontSize: 12,
            fontFamily: 'Arial',
            fontWeight: 'normal'
          }
        }
      ]
    },
    {
      name: 'Éléments Visuels',
      elements: [
        {
          type: 'image',
          fieldID: 'custom_image',
          label: 'Image',
          icon: '🖼️',
          description: 'Bloc image',
          blockContent: '[IMAGE]',
          defaultProperties: {
            width: 100,
            height: 100
          }
        },
        {
          type: 'separator',
          fieldID: 'horizontal_line',
          label: 'Ligne Séparatrice',
          icon: '➖',
          description: 'Bloc ligne horizontale',
          blockContent: '────────────',
          defaultProperties: {
            height: 1,
            color: '#000000'
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

  // Gestionnaire pour le drag start
  const handleDragStart = (e, element) => {
    e.dataTransfer.setData('application/json', JSON.stringify({
      type: 'new-element',
      elementType: element.type,
      fieldID: element.fieldID,
      defaultProps: element.defaultProperties
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
          {elementCategories.map((category, categoryIndex) => (
            <div key={categoryIndex} className="element-category">
              <h4 className="category-title">{category.name}</h4>
              <div className="elements-grid">
                {category.elements.map((element, elementIndex) => (
                  <div
                    key={elementIndex}
                    className="element-grid-block"
                    data-type={element.type}
                    draggable
                    onDragStart={(e) => handleDragStart(e, element)}
                    title={`${element.label}: ${element.description}`}
                  >
                    <div className="grid-block-header">
                      <span className="grid-block-icon">{element.icon}</span>
                      <span className="grid-block-title">{element.label}</span>
                    </div>
                    <div className="grid-block-content">
                      {element.blockContent ? (
                        <div className="grid-block-preview">
                          {VariableManager.processTextForPreview(element.blockContent).split('\n').slice(0, 2).map((line, i) => (
                            <div key={i} className="grid-block-line">{line}</div>
                          ))}
                        </div>
                      ) : (
                        <div className="grid-block-visual">
                          {element.type === 'image' && (
                            <div className="grid-image-placeholder">🖼️</div>
                          )}
                          {element.type === 'separator' && (
                            <div className="grid-separator-preview">━━━━━</div>
                          )}
                        </div>
                      )}
                    </div>
                    <div className="grid-block-footer">
                      <small>{element.description}</small>
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