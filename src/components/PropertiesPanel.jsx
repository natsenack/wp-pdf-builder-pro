import React, { useState, useEffect } from 'react';

export const PropertiesPanel = ({
  selectedElements,
  elements,
  onPropertyChange
}) => {
  const [localProperties, setLocalProperties] = useState({});

  // Obtenir l'élément sélectionné (premier de la liste pour l'instant)
  const selectedElement = selectedElements.length > 0
    ? elements.find(el => el.id === selectedElements[0])
    : null;

  // Mettre à jour les propriétés locales quand l'élément change
  useEffect(() => {
    if (selectedElement) {
      setLocalProperties({ ...selectedElement });
    } else {
      setLocalProperties({});
    }
  }, [selectedElement]);

  // Gestionnaire de changement de propriété
  const handlePropertyChange = (elementId, property, value) => {
    setLocalProperties(prev => ({ ...prev, [property]: value }));
    onPropertyChange(elementId, property, value);
  };

  // Rendu des contrôles selon le type d'élément
  const renderElementProperties = () => {
    if (!selectedElement) {
      return (
        <div className="no-selection">
          <div className="no-selection-icon">👆</div>
          <p>Sélectionnez un élément pour modifier ses propriétés</p>
          {selectedElements.length > 1 && (
            <p className="selection-info">
              {selectedElements.length} éléments sélectionnés
            </p>
          )}
        </div>
      );
    }

    switch (selectedElement.type) {
      case 'text':
        return (
          <div className="properties-group">
            <h4>Propriétés du texte</h4>

            <div className="property-row">
              <label>Texte:</label>
              <textarea
                value={localProperties.text || ''}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'text', e.target.value)}
                rows={3}
              />
            </div>

            <div className="property-row">
              <label>Taille:</label>
              <input
                type="number"
                value={localProperties.fontSize || 14}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'fontSize', parseInt(e.target.value))}
                min="8"
                max="72"
              />
            </div>

            <div className="property-row">
              <label>Police:</label>
              <select
                value={localProperties.fontFamily || 'Arial'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'fontFamily', e.target.value)}
              >
                <option value="Arial">Arial</option>
                <option value="Helvetica">Helvetica</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Courier New">Courier New</option>
                <option value="Georgia">Georgia</option>
                <option value="Verdana">Verdana</option>
              </select>
            </div>

            <div className="property-row">
              <label>Style:</label>
              <div className="style-buttons">
                <button
                  className={localProperties.fontWeight === 'bold' ? 'active' : ''}
                  onClick={() => handlePropertyChange(selectedElement.id, 'fontWeight', localProperties.fontWeight === 'bold' ? 'normal' : 'bold')}
                >
                  <strong>B</strong>
                </button>
                <button
                  className={localProperties.fontStyle === 'italic' ? 'active' : ''}
                  onClick={() => handlePropertyChange(selectedElement.id, 'fontStyle', localProperties.fontStyle === 'italic' ? 'normal' : 'italic')}
                >
                  <em>I</em>
                </button>
                <button
                  className={localProperties.textDecoration === 'underline' ? 'active' : ''}
                  onClick={() => handlePropertyChange(selectedElement.id, 'textDecoration', localProperties.textDecoration === 'underline' ? 'none' : 'underline')}
                >
                  <u>U</u>
                </button>
              </div>
            </div>

            <div className="property-row">
              <label>Couleur:</label>
              <input
                type="color"
                value={localProperties.color || '#1e293b'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'color', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Alignement:</label>
              <div className="alignment-buttons">
                {['left', 'center', 'right'].map(align => (
                  <button
                    key={align}
                    className={localProperties.textAlign === align ? 'active' : ''}
                    onClick={() => handlePropertyChange(selectedElement.id, 'textAlign', align)}
                  >
                    {align === 'left' ? '⬅️' : align === 'center' ? '⬌' : '➡️'}
                  </button>
                ))}
              </div>
            </div>
          </div>
        );

      case 'rectangle':
        return (
          <div className="properties-group">
            <h4>Propriétés du rectangle</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.fillColor || '#ffffff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'fillColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur de bordure:</label>
              <input
                type="color"
                value={localProperties.borderColor || '#6b7280'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Épaisseur de bordure:</label>
              <input
                type="number"
                value={localProperties.borderWidth || 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', parseInt(e.target.value))}
                min="0"
                max="10"
              />
            </div>

            <div className="property-row">
              <label>Rayon des coins:</label>
              <input
                type="number"
                value={localProperties.borderRadius || 0}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', parseInt(e.target.value))}
                min="0"
                max="50"
              />
            </div>
          </div>
        );

      case 'line':
        return (
          <div className="properties-group">
            <h4>Propriétés de la ligne</h4>

            <div className="property-row">
              <label>Couleur:</label>
              <input
                type="color"
                value={localProperties.lineColor || '#6b7280'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'lineColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Épaisseur:</label>
              <input
                type="number"
                value={localProperties.lineWidth || 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'lineWidth', parseInt(e.target.value))}
                min="1"
                max="10"
              />
            </div>
          </div>
        );

      default:
        return (
          <div className="properties-group">
            <h4>Propriétés générales</h4>
            <p>Type d'élément: {selectedElement.type}</p>
          </div>
        );
    }
  };

  return (
    <div className="properties-panel">
      <div className="panel-header">
        <h3>⚙️ Propriétés</h3>
        {selectedElements.length > 1 && (
          <div className="selection-count">
            {selectedElements.length} éléments sélectionnés
          </div>
        )}
      </div>

      <div className="panel-content">
        {renderElementProperties()}

        {/* Propriétés générales */}
        {selectedElement && (
          <div className="properties-group">
            <h4>Position & Taille</h4>

            <div className="property-row">
              <label>X:</label>
              <input
                type="number"
                value={Math.round(localProperties.x || 0)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'x', parseInt(e.target.value))}
              />
            </div>

            <div className="property-row">
              <label>Y:</label>
              <input
                type="number"
                value={Math.round(localProperties.y || 0)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'y', parseInt(e.target.value))}
              />
            </div>

            <div className="property-row">
              <label>Largeur:</label>
              <input
                type="number"
                value={Math.round(localProperties.width || 100)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'width', parseInt(e.target.value))}
                min="1"
              />
            </div>

            <div className="property-row">
              <label>Hauteur:</label>
              <input
                type="number"
                value={Math.round(localProperties.height || 50)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'height', parseInt(e.target.value))}
                min="1"
              />
            </div>
          </div>
        )}
      </div>
    </div>
  );
};