import React, { useState } from 'react';

export const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
  const [expandedCategories, setExpandedCategories] = useState({
    // Bibliothèque d'éléments vidée complètement
  });
  const [showHeaderTemplatesModal, setShowHeaderTemplatesModal] = useState(false);

  const toggleCategory = (categoryName) => {
    setExpandedCategories(prev => ({
      ...prev,
      [categoryName]: !prev[categoryName]
    }));
  };

  const elementCategories = [
    // Bibliothèque d'éléments complètement vidée
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
