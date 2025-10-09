import React, { useState, useEffect } from 'react';
import '../styles/PropertiesPanel.css';

export const PropertiesPanel = ({
  selectedElements,
  elements,
  onPropertyChange
}) => {
  const [localProperties, setLocalProperties] = useState({});
  const [activeTab, setActiveTab] = useState('appearance');

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

  // Composant pour les contrôles de couleur avec presets
  const ColorPicker = ({ label, value, onChange, presets = [] }) => (
    <div className="property-row">
      <label>{label}:</label>
      <div className="color-picker-container">
        <input
          type="color"
          value={value || '#1e293b'}
          onChange={(e) => onChange(e.target.value)}
          className="color-input"
        />
        <div className="color-presets">
          {presets.map((preset, index) => (
            <button
              key={index}
              className="color-preset"
              style={{ backgroundColor: preset }}
              onClick={() => onChange(preset)}
              title={preset}
            />
          ))}
        </div>
      </div>
    </div>
  );

  // Composant pour les contrôles de police
  const FontControls = ({ elementId, properties }) => (
    <div className="properties-group">
      <h4>🎨 Police & Style</h4>

      <div className="property-row">
        <label>Famille:</label>
        <select
          value={properties.fontFamily || 'Inter'}
          onChange={(e) => handlePropertyChange(elementId, 'fontFamily', e.target.value)}
        >
          <option value="Inter">Inter</option>
          <option value="Arial">Arial</option>
          <option value="Helvetica">Helvetica</option>
          <option value="Times New Roman">Times New Roman</option>
          <option value="Courier New">Courier New</option>
          <option value="Georgia">Georgia</option>
          <option value="Verdana">Verdana</option>
          <option value="Roboto">Roboto</option>
          <option value="Open Sans">Open Sans</option>
        </select>
      </div>

      <div className="property-row">
        <label>Taille:</label>
        <div className="slider-container">
          <input
            type="range"
            min="8"
            max="72"
            value={properties.fontSize || 14}
            onChange={(e) => handlePropertyChange(elementId, 'fontSize', parseInt(e.target.value))}
            className="slider"
          />
          <span className="slider-value">{properties.fontSize || 14}px</span>
        </div>
      </div>

      <div className="property-row">
        <label>Style du texte:</label>
        <div className="style-buttons-grid">
          <button
            className={`style-btn ${properties.fontWeight === 'bold' ? 'active' : ''}`}
            onClick={() => handlePropertyChange(elementId, 'fontWeight', properties.fontWeight === 'bold' ? 'normal' : 'bold')}
            title="Gras"
          >
            <strong>B</strong>
          </button>
          <button
            className={`style-btn ${properties.fontStyle === 'italic' ? 'active' : ''}`}
            onClick={() => handlePropertyChange(elementId, 'fontStyle', properties.fontStyle === 'italic' ? 'normal' : 'italic')}
            title="Italique"
          >
            <em>I</em>
          </button>
          <button
            className={`style-btn ${properties.textDecoration === 'underline' ? 'active' : ''}`}
            onClick={() => handlePropertyChange(elementId, 'textDecoration', properties.textDecoration === 'underline' ? 'none' : 'underline')}
            title="Souligné"
          >
            <u>U</u>
          </button>
          <button
            className={`style-btn ${properties.textDecoration === 'line-through' ? 'active' : ''}`}
            onClick={() => handlePropertyChange(elementId, 'textDecoration', properties.textDecoration === 'line-through' ? 'none' : 'line-through')}
            title="Barré"
          >
            <s>S</s>
          </button>
        </div>
      </div>

      <div className="property-row">
        <label>Alignement:</label>
        <div className="alignment-buttons">
          {[
            { value: 'left', icon: '⬅️', label: 'Gauche' },
            { value: 'center', icon: '⬌', label: 'Centre' },
            { value: 'right', icon: '➡️', label: 'Droite' },
            { value: 'justify', icon: '⬌⬅️', label: 'Justifié' }
          ].map(({ value, icon, label }) => (
            <button
              key={value}
              className={`align-btn ${properties.textAlign === value ? 'active' : ''}`}
              onClick={() => handlePropertyChange(elementId, 'textAlign', value)}
              title={label}
            >
              {icon}
            </button>
          ))}
        </div>
      </div>
    </div>
  );

  // Rendu des onglets
  const renderTabs = () => (
    <div className="properties-tabs">
      <button
        className={`tab-btn ${activeTab === 'appearance' ? 'active' : ''}`}
        onClick={() => setActiveTab('appearance')}
      >
        🎨 Apparence
      </button>
      <button
        className={`tab-btn ${activeTab === 'layout' ? 'active' : ''}`}
        onClick={() => setActiveTab('layout')}
      >
        📐 Mise en page
      </button>
      <button
        className={`tab-btn ${activeTab === 'content' ? 'active' : ''}`}
        onClick={() => setActiveTab('content')}
      >
        📝 Contenu
      </button>
      <button
        className={`tab-btn ${activeTab === 'effects' ? 'active' : ''}`}
        onClick={() => setActiveTab('effects')}
      >
        ✨ Effets
      </button>
    </div>
  );

  // Rendu du contenu selon l'onglet actif
  const renderTabContent = () => {
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

    switch (activeTab) {
      case 'appearance':
        return (
          <div className="tab-content">
            <div className="properties-group">
              <h4>🎨 Couleurs</h4>

              <ColorPicker
                label="Texte"
                value={localProperties.color}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'color', value)}
                presets={['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1']}
              />

              <ColorPicker
                label="Fond"
                value={localProperties.fillColor}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'fillColor', value)}
                presets={['#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8']}
              />

              <ColorPicker
                label="Bordure"
                value={localProperties.borderColor}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'borderColor', value)}
                presets={['#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155']}
              />
            </div>

            {(selectedElement.type === 'text' || selectedElement.type === 'layout-header' ||
              selectedElement.type === 'layout-footer' || selectedElement.type === 'layout-section') && (
              <FontControls elementId={selectedElement.id} properties={localProperties} />
            )}

            <div className="properties-group">
              <h4>🔲 Bordures & Coins</h4>

              <div className="property-row">
                <label>Épaisseur bordure:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="10"
                    value={localProperties.borderWidth || 0}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.borderWidth || 0}px</span>
                </div>
              </div>

              <div className="property-row">
                <label>Arrondi des coins:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="50"
                    value={localProperties.borderRadius || 0}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.borderRadius || 0}px</span>
                </div>
              </div>
            </div>
          </div>
        );

      case 'layout':
        return (
          <div className="tab-content">
            <div className="properties-group">
              <h4>📍 Position</h4>

              <div className="property-row">
                <label>X:</label>
                <input
                  type="number"
                  value={Math.round(localProperties.x || 0)}
                  onChange={(e) => handlePropertyChange(selectedElement.id, 'x', parseInt(e.target.value))}
                  step="1"
                />
              </div>

              <div className="property-row">
                <label>Y:</label>
                <input
                  type="number"
                  value={Math.round(localProperties.y || 0)}
                  onChange={(e) => handlePropertyChange(selectedElement.id, 'y', parseInt(e.target.value))}
                  step="1"
                />
              </div>
            </div>

            <div className="properties-group">
              <h4>📏 Dimensions</h4>

              <div className="property-row">
                <label>Largeur:</label>
                <input
                  type="number"
                  value={Math.round(localProperties.width || 100)}
                  onChange={(e) => handlePropertyChange(selectedElement.id, 'width', parseInt(e.target.value))}
                  min="1"
                  step="1"
                />
              </div>

              <div className="property-row">
                <label>Hauteur:</label>
                <input
                  type="number"
                  value={Math.round(localProperties.height || 50)}
                  onChange={(e) => handlePropertyChange(selectedElement.id, 'height', parseInt(e.target.value))}
                  min="1"
                  step="1"
                />
              </div>
            </div>

            <div className="properties-group">
              <h4>🔄 Rotation & Échelle</h4>

              <div className="property-row">
                <label>Rotation:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="-180"
                    max="180"
                    value={localProperties.rotation || 0}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'rotation', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.rotation || 0}°</span>
                </div>
              </div>

              <div className="property-row">
                <label>Échelle:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="10"
                    max="200"
                    value={localProperties.scale || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'scale', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.scale || 100}%</span>
                </div>
              </div>
            </div>
          </div>
        );

      case 'content':
        return (
          <div className="tab-content">
            {selectedElement.type === 'text' && (
              <div className="properties-group">
                <h4>📝 Contenu texte</h4>

                <div className="property-row">
                  <label>Texte:</label>
                  <textarea
                    value={localProperties.text || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'text', e.target.value)}
                    rows={4}
                    placeholder="Saisissez votre texte ici..."
                  />
                </div>

                <div className="property-row">
                  <label>Variables dynamiques:</label>
                  <div className="variables-list">
                    <button className="variable-btn" onClick={() => {
                      const currentText = localProperties.text || '';
                      handlePropertyChange(selectedElement.id, 'text', currentText + '{{date}}');
                    }}>
                      📅 Date
                    </button>
                    <button className="variable-btn" onClick={() => {
                      const currentText = localProperties.text || '';
                      handlePropertyChange(selectedElement.id, 'text', currentText + '{{order_number}}');
                    }}>
                      🏷️ N° commande
                    </button>
                    <button className="variable-btn" onClick={() => {
                      const currentText = localProperties.text || '';
                      handlePropertyChange(selectedElement.id, 'text', currentText + '{{customer_name}}');
                    }}>
                      👤 Client
                    </button>
                    <button className="variable-btn" onClick={() => {
                      const currentText = localProperties.text || '';
                      handlePropertyChange(selectedElement.id, 'text', currentText + '{{total}}');
                    }}>
                      💰 Total
                    </button>
                  </div>
                </div>
              </div>
            )}

            {(selectedElement.type === 'image' || selectedElement.type === 'company_logo') && (
              <div className="properties-group">
                <h4>🖼️ Image</h4>

                <div className="property-row">
                  <label>URL de l'image:</label>
                  <input
                    type="url"
                    value={localProperties.src || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'src', e.target.value)}
                    placeholder="https://exemple.com/image.jpg"
                  />
                </div>

                <div className="property-row">
                  <label>Alt text:</label>
                  <input
                    type="text"
                    value={localProperties.alt || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'alt', e.target.value)}
                    placeholder="Description de l'image"
                  />
                </div>

                <div className="property-row">
                  <label>Adaptation:</label>
                  <select
                    value={localProperties.objectFit || 'cover'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'objectFit', e.target.value)}
                  >
                    <option value="cover">Couvrir (zoom)</option>
                    <option value="contain">Contenir (intégral)</option>
                    <option value="fill">Remplir</option>
                    <option value="none">Aucune</option>
                  </select>
                </div>
              </div>
            )}

            {selectedElement.type === 'product_table' && (
              <div className="properties-group">
                <h4>📊 Tableau produits</h4>

                <div className="property-row">
                  <label>Colonnes à afficher:</label>
                  <div className="checkbox-group">
                    {[
                      { key: 'image', label: 'Image' },
                      { key: 'name', label: 'Nom' },
                      { key: 'sku', label: 'SKU' },
                      { key: 'quantity', label: 'Quantité' },
                      { key: 'price', label: 'Prix' },
                      { key: 'total', label: 'Total' }
                    ].map(({ key, label }) => (
                      <label key={key} className="checkbox-item">
                        <input
                          type="checkbox"
                          checked={localProperties.columns?.[key] ?? true}
                          onChange={(e) => handlePropertyChange(selectedElement.id, `columns.${key}`, e.target.checked)}
                        />
                        {label}
                      </label>
                    ))}
                  </div>
                </div>
              </div>
            )}
          </div>
        );

      case 'effects':
        return (
          <div className="tab-content">
            <div className="properties-group">
              <h4>🌟 Transparence & Visibilité</h4>

              <div className="property-row">
                <label>Opacité:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="100"
                    value={localProperties.opacity || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'opacity', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.opacity || 100}%</span>
                </div>
              </div>

              <div className="property-row">
                <label>Visibilité:</label>
                <label className="toggle">
                  <input
                    type="checkbox"
                    checked={localProperties.visible !== false}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'visible', e.target.checked)}
                  />
                  <span className="toggle-slider"></span>
                </label>
              </div>
            </div>

            <div className="properties-group">
              <h4>✨ Ombres & Effets</h4>

              <div className="property-row">
                <label>Ombre:</label>
                <label className="toggle">
                  <input
                    type="checkbox"
                    checked={localProperties.shadow || false}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'shadow', e.target.checked)}
                  />
                  <span className="toggle-slider"></span>
                </label>
              </div>

              {localProperties.shadow && (
                <>
                  <ColorPicker
                    label="Couleur ombre"
                    value={localProperties.shadowColor}
                    onChange={(value) => handlePropertyChange(selectedElement.id, 'shadowColor', value)}
                    presets={['#000000', '#374151', '#6b7280', '#9ca3af']}
                  />

                  <div className="property-row">
                    <label>Décalage X:</label>
                    <input
                      type="number"
                      value={localProperties.shadowOffsetX || 2}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetX', parseInt(e.target.value))}
                      min="-20"
                      max="20"
                    />
                  </div>

                  <div className="property-row">
                    <label>Décalage Y:</label>
                    <input
                      type="number"
                      value={localProperties.shadowOffsetY || 2}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetY', parseInt(e.target.value))}
                      min="-20"
                      max="20"
                    />
                  </div>
                </>
              )}
            </div>

            <div className="properties-group">
              <h4>🎭 Filtres visuels</h4>

              <div className="property-row">
                <label>Luminosité:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="200"
                    value={localProperties.brightness || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'brightness', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.brightness || 100}%</span>
                </div>
              </div>

              <div className="property-row">
                <label>Contraste:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="200"
                    value={localProperties.contrast || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'contrast', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.contrast || 100}%</span>
                </div>
              </div>

              <div className="property-row">
                <label>Saturation:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="200"
                    value={localProperties.saturate || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'saturate', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.saturate || 100}%</span>
                </div>
              </div>
            </div>
          </div>
        );

      default:
        return null;
    }
  };

  return (
    <div className="properties-panel">
      <div className="properties-header">
        <h3>Propriétés</h3>
        {selectedElement && (
          <div className="element-info">
            <span className="element-type">{selectedElement.type}</span>
            <span className="element-id">#{selectedElement.id}</span>
          </div>
        )}
      </div>

      {renderTabs()}
      <div className="properties-content">
        {renderTabContent()}
      </div>
    </div>
  );
};
