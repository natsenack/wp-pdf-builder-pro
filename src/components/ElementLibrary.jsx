import React, { useState } from 'react';

export const ElementLibrary = ({ onAddElement, selectedTool, onToolSelect }) => {
  const [searchTerm, setSearchTerm] = useState('');

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
      name: 'Formes',
      elements: [
        { type: 'rectangle', label: 'Rectangle', icon: '▭', description: 'Forme rectangulaire' },
        { type: 'line', label: 'Ligne', icon: '━', description: 'Ligne horizontale' }
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
    }
  ];

  const handleElementClick = (elementType, defaultProps = {}) => {
    onToolSelect(`add-${elementType}`);
  };

  const filteredCategories = elementCategories.map(category => ({
    ...category,
    elements: category.elements.filter(element =>
      element.label.toLowerCase().includes(searchTerm.toLowerCase()) ||
      element.description.toLowerCase().includes(searchTerm.toLowerCase())
    )
  })).filter(category => category.elements.length > 0);

  return (
    <div className="element-library">
      <div className="library-header">
        <h3>📚 Bibliothèque</h3>
        <div className="search-box">
          <input
            type="text"
            placeholder="Rechercher..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <span className="search-icon">🔍</span>
        </div>
      </div>

      <div className="library-content">
        {filteredCategories.map(category => (
          <div key={category.name} className="element-category">
            <h4 className="category-title">{category.name}</h4>
            <div className="element-grid">
              {category.elements.map(element => (
                <div
                  key={`${element.type}-${element.label}`}
                  className={`element-item ${selectedTool === `add-${element.type}` ? 'selected' : ''}`}
                  onClick={() => handleElementClick(element.type, element.defaultProps)}
                  title={element.description}
                >
                  <div className="element-icon">{element.icon}</div>
                  <div className="element-label">{element.label}</div>
                  <div className="element-description">{element.description}</div>
                </div>
              ))}
            </div>
          </div>
        ))}

        {filteredCategories.length === 0 && (
          <div className="no-results">
            <div className="no-results-icon">🔍</div>
            <p>Aucun élément trouvé pour "{searchTerm}"</p>
          </div>
        )}
      </div>

      <div className="library-footer">
        <div className="quick-actions">
          <button
            className="quick-action-btn"
            onClick={() => handleElementClick('text', { text: 'Nouveau texte', fontSize: 14 })}
            title="Ajouter un texte rapidement"
          >
            ⚡ Texte rapide
          </button>
          <button
            className="quick-action-btn"
            onClick={() => handleElementClick('rectangle', { width: 100, height: 50 })}
            title="Ajouter un rectangle rapidement"
          >
            ▭ Forme rapide
          </button>
        </div>
      </div>
    </div>
  );
};