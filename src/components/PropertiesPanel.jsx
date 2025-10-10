import React, { useState, useEffect, useCallback } from 'react';
import { useElementCustomization } from '../hooks/useElementCustomization';
import { useElementSynchronization } from '../hooks/useElementSynchronization';
import { elementCustomizationService } from '../services/ElementCustomizationService';
import '../styles/PropertiesPanel.css';

// Composant pour les contrôles de couleur avec presets
const ColorPicker = ({ label, value, onChange, presets = [] }) => (
  <div className="property-row">
    <label>{label}:</label>
    <div className="color-picker-container">
      <input
        type="color"
        value={value || '#333333'}
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
const FontControls = ({ elementId, properties, onPropertyChange }) => (
  <div className="properties-group">
    <h4>🎨 Police & Style</h4>

    <div className="property-row">
      <label>Famille:</label>
      <select
        value={properties.fontFamily || 'Inter'}
        onChange={(e) => onPropertyChange(elementId, 'fontFamily', e.target.value)}
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
          onChange={(e) => onPropertyChange(elementId, 'fontSize', parseInt(e.target.value))}
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
          onClick={() => onPropertyChange(elementId, 'fontWeight', properties.fontWeight === 'bold' ? 'normal' : 'bold')}
          title="Gras"
        >
          <strong>B</strong>
        </button>
        <button
          className={`style-btn ${properties.fontStyle === 'italic' ? 'active' : ''}`}
          onClick={() => onPropertyChange(elementId, 'fontStyle', properties.fontStyle === 'italic' ? 'normal' : 'italic')}
          title="Italique"
        >
          <em>I</em>
        </button>
        <button
          className={`style-btn ${properties.textDecoration === 'underline' ? 'active' : ''}`}
          onClick={() => onPropertyChange(elementId, 'textDecoration', properties.textDecoration === 'underline' ? 'none' : 'underline')}
          title="Souligné"
        >
          <u>U</u>
        </button>
        <button
          className={`style-btn ${properties.textDecoration === 'line-through' ? 'active' : ''}`}
          onClick={() => onPropertyChange(elementId, 'textDecoration', properties.textDecoration === 'line-through' ? 'none' : 'line-through')}
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
            onClick={() => onPropertyChange(elementId, 'textAlign', value)}
            title={label}
          >
            {icon}
          </button>
        ))}
      </div>
    </div>
  </div>
);

export const PropertiesPanel = ({
  selectedElements,
  elements,
  onPropertyChange,
  onBatchUpdate
}) => {
  // États pour mémoriser les valeurs précédentes
  const [previousBackgroundColor, setPreviousBackgroundColor] = useState('#ffffff');
  const [previousBorderWidth, setPreviousBorderWidth] = useState(1);

  // Utiliser les hooks de personnalisation et synchronisation
  const {
    localProperties,
    activeTab,
    setActiveTab,
    handlePropertyChange: customizationChange
  } = useElementCustomization(selectedElements, elements, onPropertyChange);

  const { syncImmediate, syncBatch } = useElementSynchronization(
    elements,
    onPropertyChange,
    onBatchUpdate,
    true, // autoSave
    1000 // autoSaveDelay
  );

  // Obtenir l'élément sélectionné depuis le hook
  const selectedElement = selectedElements.length > 0
    ? elements.find(el => el.id === selectedElements[0])
    : null;

  // Mettre à jour les valeurs précédentes quand l'élément change
  useEffect(() => {
    if (selectedElement) {
      // Initialiser les valeurs précédentes seulement si elles ne sont pas déjà définies
      // et que les valeurs actuelles ne sont pas les valeurs "désactivées"
      if (localProperties.backgroundColor && localProperties.backgroundColor !== 'transparent') {
        setPreviousBackgroundColor(localProperties.backgroundColor);
      }
      if (localProperties.borderWidth && localProperties.borderWidth > 0) {
        setPreviousBorderWidth(localProperties.borderWidth);
      }
    }
  }, [selectedElement]); // Retirer les dépendances aux propriétés locales pour éviter les écrasements

  // Gestionnaire unifié de changement de propriété
  const handlePropertyChange = useCallback((elementId, property, value) => {
    // Validation via le service (sauf pour les propriétés boolean qui sont toujours valides)
    const isBooleanProperty = typeof value === 'boolean' || property.startsWith('columns.');
    if (!isBooleanProperty && !elementCustomizationService.validateProperty(property, value)) {
      console.warn(`Propriété invalide: ${property} = ${value}`);
      return;
    }

    // Utiliser le hook de personnalisation pour la gestion locale
    customizationChange(elementId, property, value);

    // Synchronisation immédiate pour les changements critiques
    if (['x', 'y', 'width', 'height'].includes(property)) {
      syncImmediate(elementId, property, value);
    }
  }, [customizationChange, syncImmediate]);

  // Gestionnaire pour le toggle "Aucun fond"
  const handleNoBackgroundToggle = useCallback((elementId, checked) => {
    if (checked) {
      // Sauvegarder la couleur actuelle seulement si elle n'est pas déjà transparente
      if (localProperties.backgroundColor && localProperties.backgroundColor !== 'transparent') {
        setPreviousBackgroundColor(localProperties.backgroundColor);
      }
      handlePropertyChange(elementId, 'backgroundColor', 'transparent');
    } else {
      // Restaurer la couleur précédente
      handlePropertyChange(elementId, 'backgroundColor', previousBackgroundColor);
    }
  }, [localProperties.backgroundColor, previousBackgroundColor, handlePropertyChange]);

  // Gestionnaire pour le toggle "Aucune bordure"
  const handleNoBorderToggle = useCallback((elementId, checked) => {
    if (checked) {
      // Sauvegarder l'épaisseur actuelle seulement si elle n'est pas déjà 0
      if (localProperties.borderWidth && localProperties.borderWidth > 0) {
        setPreviousBorderWidth(localProperties.borderWidth);
      }
      handlePropertyChange(elementId, 'borderWidth', 0);
    } else {
      // Restaurer l'épaisseur précédente
      handlePropertyChange(elementId, 'borderWidth', previousBorderWidth);
    }
  }, [localProperties.borderWidth, previousBorderWidth, handlePropertyChange]);

  // Rendu des onglets
  const renderTabs = useCallback(() => (
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
  ), [activeTab]);

  // Rendu du contenu selon l'onglet actif
  const renderTabContent = useCallback(() => {
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
                value={localProperties.backgroundColor}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'backgroundColor', value)}
                presets={['#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8']}
              />

              <div className="property-row">
                <label>Aucun fond:</label>
                <label className="toggle">
                  <input
                    type="checkbox"
                    checked={!localProperties.backgroundColor || localProperties.backgroundColor === 'transparent'}
                    onChange={(e) => handleNoBackgroundToggle(selectedElement.id, e.target.checked)}
                  />
                  <span className="toggle-slider"></span>
                </label>
              </div>

              <ColorPicker
                label="Bordure"
                value={localProperties.borderColor}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'borderColor', value)}
                presets={['#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155']}
              />

              <div className="property-row">
                <label>Aucune bordure:</label>
                <label className="toggle">
                  <input
                    type="checkbox"
                    checked={!localProperties.borderWidth || localProperties.borderWidth === 0}
                    onChange={(e) => handleNoBorderToggle(selectedElement.id, e.target.checked)}
                  />
                  <span className="toggle-slider"></span>
                </label>
              </div>
            </div>

            {(selectedElement.type === 'text' || selectedElement.type === 'layout-header' ||
              selectedElement.type === 'layout-footer' || selectedElement.type === 'layout-section') && (
              <FontControls
                elementId={selectedElement.id}
                properties={localProperties}
                onPropertyChange={handlePropertyChange}
              />
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
            {/* Contrôles d'alignement rapide */}
            <div className="properties-group">
              <h4>🎯 Alignement Rapide</h4>
              <div className="alignment-controls">
                <div className="alignment-row">
                  <button
                    className="align-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'x', 0)}
                    title="Aligner à gauche"
                  >
                    ⬅️ Gauche
                  </button>
                  <button
                    className="align-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'x', 210)}
                    title="Centrer horizontalement"
                  >
                    ⬌ Centre
                  </button>
                  <button
                    className="align-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'x', 420)}
                    title="Aligner à droite"
                  >
                    ➡️ Droite
                  </button>
                </div>
                <div className="alignment-row">
                  <button
                    className="align-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', 0)}
                    title="Aligner en haut"
                  >
                    ⬆️ Haut
                  </button>
                  <button
                    className="align-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', 148)}
                    title="Centrer verticalement"
                  >
                    ⬍ Centre
                  </button>
                  <button
                    className="align-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', 297)}
                    title="Aligner en bas"
                  >
                    ⬇️ Bas
                  </button>
                </div>
              </div>
            </div>

            {/* Position précise */}
            <div className="properties-group">
              <h4>📍 Position Précise</h4>

              <div className="property-row">
                <label>X:</label>
                <div className="input-with-unit">
                  <input
                    type="number"
                    value={Math.round(localProperties.x || 0)}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'x', parseInt(e.target.value))}
                    step="1"
                  />
                  <span className="unit">mm</span>
                </div>
              </div>

              <div className="property-row">
                <label>Y:</label>
                <div className="input-with-unit">
                  <input
                    type="number"
                    value={Math.round(localProperties.y || 0)}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'y', parseInt(e.target.value))}
                    step="1"
                  />
                  <span className="unit">mm</span>
                </div>
              </div>
            </div>

            {/* Dimensions avec contraintes */}
            <div className="properties-group">
              <h4>📏 Dimensions</h4>

              <div className="property-row">
                <label>Largeur:</label>
                <div className="input-with-unit">
                  <input
                    type="number"
                    value={Math.round(localProperties.width || 100)}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'width', parseInt(e.target.value))}
                    min="1"
                    step="1"
                  />
                  <span className="unit">mm</span>
                </div>
              </div>

              <div className="property-row">
                <label>Hauteur:</label>
                <div className="input-with-unit">
                  <input
                    type="number"
                    value={Math.round(localProperties.height || 50)}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'height', parseInt(e.target.value))}
                    min="1"
                    step="1"
                  />
                  <span className="unit">mm</span>
                </div>
              </div>

              {/* Boutons de ratio */}
              <div className="property-row">
                <label>Ratio:</label>
                <div className="ratio-buttons">
                  <button
                    className="ratio-btn"
                    onClick={() => {
                      const newHeight = (localProperties.width || 100) * 0.75;
                      handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
                    }}
                    title="Format 4:3"
                  >
                    4:3
                  </button>
                  <button
                    className="ratio-btn"
                    onClick={() => {
                      const newHeight = (localProperties.width || 100) * (297/210);
                      handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
                    }}
                    title="Format A4"
                  >
                    A4
                  </button>
                  <button
                    className="ratio-btn"
                    onClick={() => {
                      const newHeight = (localProperties.width || 100);
                      handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
                    }}
                    title="Carré"
                  >
                    1:1
                  </button>
                </div>
              </div>
            </div>

            {/* Transformation */}
            <div className="properties-group">
              <h4>🔄 Transformation</h4>

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

              {/* Boutons de rotation rapide */}
              <div className="property-row">
                <label>Rotation rapide:</label>
                <div className="rotation-buttons">
                  <button
                    className="rotation-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'rotation', 0)}
                    title="Rotation 0°"
                  >
                    ↻ 0°
                  </button>
                  <button
                    className="rotation-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'rotation', 90)}
                    title="Rotation 90°"
                  >
                    ↻ 90°
                  </button>
                  <button
                    className="rotation-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'rotation', 180)}
                    title="Rotation 180°"
                  >
                    ↻ 180°
                  </button>
                  <button
                    className="rotation-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'rotation', -90)}
                    title="Rotation -90°"
                  >
                    ↺ -90°
                  </button>
                </div>
              </div>
            </div>

            {/* Calques et profondeur */}
            <div className="properties-group">
              <h4>📚 Calques</h4>

              <div className="property-row">
                <label>Profondeur (Z-index):</label>
                <input
                  type="number"
                  value={localProperties.zIndex || 0}
                  onChange={(e) => handlePropertyChange(selectedElement.id, 'zIndex', parseInt(e.target.value))}
                  min="0"
                  max="100"
                  step="1"
                />
              </div>

              <div className="property-row">
                <label>Actions:</label>
                <div className="layer-actions">
                  <button
                    className="layer-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'zIndex', (localProperties.zIndex || 0) + 1)}
                    title="Mettre devant"
                  >
                    ⬆️ Devant
                  </button>
                  <button
                    className="layer-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'zIndex', Math.max(0, (localProperties.zIndex || 0) - 1))}
                    title="Mettre derrière"
                  >
                    ⬇️ Derrière
                  </button>
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

            {selectedElement.type === 'image' && (
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
                          onChange={(e) => {
                            handlePropertyChange(selectedElement.id, `columns.${key}`, e.target.checked);
                          }}
                        />
                        {label}
                      </label>
                    ))}
                  </div>
                </div>

                <div className="property-row">
                  <label>Style du tableau:</label>
                  <select
                    value={localProperties.tableStyle || 'default'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'tableStyle', e.target.value)}
                  >
                    <option value="default">Style par défaut</option>
                    <option value="classic">Classique (noir/blanc)</option>
                    <option value="striped">Lignes alternées</option>
                    <option value="bordered">Encadré</option>
                    <option value="minimal">Minimal</option>
                    <option value="modern">Moderne</option>
                  </select>
                </div>

                <div className="property-row">
                  <label>Lignes de totaux:</label>
                  <div className="checkbox-group">
                    {[
                      { key: 'showSubtotal', label: 'Sous-total' },
                      { key: 'showShipping', label: 'Frais de port' },
                      { key: 'showTaxes', label: 'Taxes' },
                      { key: 'showDiscount', label: 'Remise' },
                      { key: 'showTotal', label: 'Total général' }
                    ].map(({ key, label }) => (
                      <label key={key} className="checkbox-item">
                        <input
                          type="checkbox"
                          checked={localProperties[key] || false}
                          onChange={(e) => handlePropertyChange(selectedElement.id, key, e.target.checked)}
                        />
                        {label}
                      </label>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {selectedElement.type === 'customer_info' && (
              <div className="properties-group">
                <h4>👤 Informations client</h4>

                <div className="property-row">
                  <label>Champs à afficher:</label>
                  <div className="checkbox-group">
                    {[
                      { key: 'name', label: 'Nom' },
                      { key: 'email', label: 'Email' },
                      { key: 'phone', label: 'Téléphone' },
                      { key: 'address', label: 'Adresse' },
                      { key: 'company', label: 'Société' },
                      { key: 'vat', label: 'N° TVA' }
                    ].map(({ key, label }) => (
                      <label key={key} className="checkbox-item">
                        <input
                          type="checkbox"
                          checked={localProperties.fields?.includes(key) ?? true}
                          onChange={(e) => {
                            const currentFields = localProperties.fields || ['name', 'email', 'phone', 'address', 'company', 'vat'];
                            const newFields = e.target.checked
                              ? [...currentFields, key]
                              : currentFields.filter(f => f !== key);
                            handlePropertyChange(selectedElement.id, 'fields', newFields);
                          }}
                        />
                        {label}
                      </label>
                    ))}
                  </div>
                </div>

                <div className="property-row">
                  <label>Disposition:</label>
                  <select
                    value={localProperties.layout || 'vertical'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'layout', e.target.value)}
                  >
                    <option value="vertical">Verticale</option>
                    <option value="horizontal">Horizontale</option>
                  </select>
                </div>

                <div className="property-row">
                  <label>Afficher les étiquettes:</label>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={localProperties.showLabels ?? true}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'showLabels', e.target.checked)}
                    />
                    <span className="toggle-slider"></span>
                  </label>
                </div>

                {localProperties.showLabels && (
                  <div className="property-row">
                    <label>Style des étiquettes:</label>
                    <select
                      value={localProperties.labelStyle || 'normal'}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'labelStyle', e.target.value)}
                    >
                      <option value="normal">Normal</option>
                      <option value="bold">Gras</option>
                      <option value="uppercase">Majuscules</option>
                    </select>
                  </div>
                )}

                <div className="property-row">
                  <label>Espacement:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="20"
                      value={localProperties.spacing || 8}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'spacing', parseInt(e.target.value))}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.spacing || 8}px</span>
                  </div>
                </div>
              </div>
            )}

            {/* Contrôles de police pour customer_info */}
            {selectedElement.type === 'customer_info' && (
              <FontControls
                elementId={selectedElement.id}
                properties={localProperties}
                onPropertyChange={handlePropertyChange}
              />
            )}

            {/* Contrôles pour le logo entreprise */}
            {selectedElement.type === 'company_logo' && (
              <div className="properties-group">
                <h4>🏢 Logo Entreprise</h4>

                <div className="property-row">
                  <label>Image:</label>
                  <div className="input-with-button">
                    <input
                      type="text"
                      value={localProperties.imageUrl || ''}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'imageUrl', e.target.value)}
                      placeholder="https://exemple.com/logo.png ou sélectionner ci-dessous"
                    />
                    <button
                      type="button"
                      className="media-button"
                      onClick={async () => {
                        try {
                          // Récupérer les médias WordPress via l'API REST
                          const response = await fetch('/wp-json/wp/v2/media?media_type=image&per_page=50&_embed');
                          const media = await response.json();

                          // Créer une modale simple pour sélectionner l'image
                          const modal = document.createElement('div');
                          modal.style.cssText = `
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0,0,0,0.8);
                            z-index: 9999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                          `;

                          const modalContent = document.createElement('div');
                          modalContent.style.cssText = `
                            background: white;
                            padding: 20px;
                            border-radius: 8px;
                            max-width: 600px;
                            max-height: 80vh;
                            overflow-y: auto;
                            width: 90%;
                          `;

                          const title = document.createElement('h3');
                          title.textContent = 'Sélectionner un logo depuis la médiathèque';
                          title.style.marginBottom = '15px';

                          const closeBtn = document.createElement('button');
                          closeBtn.textContent = '✕';
                          closeBtn.style.cssText = `
                            position: absolute;
                            top: 10px;
                            right: 10px;
                            background: none;
                            border: none;
                            font-size: 20px;
                            cursor: pointer;
                          `;
                          closeBtn.onclick = () => document.body.removeChild(modal);

                          const grid = document.createElement('div');
                          grid.style.cssText = `
                            display: grid;
                            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                            gap: 10px;
                            margin-top: 15px;
                          `;

                          media.forEach(item => {
                            const imgContainer = document.createElement('div');
                            imgContainer.style.cssText = `
                              border: 2px solid #ddd;
                              border-radius: 4px;
                              padding: 5px;
                              cursor: pointer;
                              transition: border-color 0.2s;
                            `;
                            imgContainer.onmouseover = () => imgContainer.style.borderColor = '#007cba';
                            imgContainer.onmouseout = () => imgContainer.style.borderColor = '#ddd';

                            const img = document.createElement('img');
                            img.src = item.source_url;
                            img.style.cssText = `
                              width: 100%;
                              height: 80px;
                              object-fit: cover;
                              border-radius: 2px;
                            `;

                            const name = document.createElement('div');
                            name.textContent = item.title.rendered.length > 15 ?
                              item.title.rendered.substring(0, 15) + '...' :
                              item.title.rendered;
                            name.style.cssText = `
                              font-size: 11px;
                              text-align: center;
                              margin-top: 5px;
                              color: #666;
                            `;

                            imgContainer.onclick = () => {
                              handlePropertyChange(selectedElement.id, 'imageUrl', item.source_url);
                              document.body.removeChild(modal);
                            };

                            imgContainer.appendChild(img);
                            imgContainer.appendChild(name);
                            grid.appendChild(imgContainer);
                          });

                          modalContent.appendChild(title);
                          modalContent.appendChild(closeBtn);
                          modalContent.appendChild(grid);
                          modal.appendChild(modalContent);
                          document.body.appendChild(modal);

                        } catch (error) {
                          console.error('Erreur lors de la récupération des médias:', error);
                          alert('Erreur lors de l\'accès à la médiathèque WordPress');
                        }
                      }}
                    >
                      � Uploader
                    </button>
                  </div>
                </div>

                <div className="property-row">
                  <label>Largeur:</label>
                  <div className="input-with-unit">
                    <input
                      type="number"
                      value={localProperties.width || 150}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'width', parseInt(e.target.value))}
                      min="20"
                      max="500"
                    />
                    <span className="unit">px</span>
                  </div>
                </div>

                <div className="property-row">
                  <label>Hauteur:</label>
                  <div className="input-with-unit">
                    <input
                      type="number"
                      value={localProperties.height || 80}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'height', parseInt(e.target.value))}
                      min="20"
                      max="300"
                    />
                    <span className="unit">px</span>
                  </div>
                </div>

                <div className="property-row">
                  <label>Alignement:</label>
                  <select
                    value={localProperties.alignment || 'left'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'alignment', e.target.value)}
                  >
                    <option value="left">Gauche</option>
                    <option value="center">Centre</option>
                    <option value="right">Droite</option>
                  </select>
                </div>

                <div className="property-row">
                  <label>Ajustement:</label>
                  <select
                    value={localProperties.fit || 'contain'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'fit', e.target.value)}
                  >
                    <option value="contain">Contenir</option>
                    <option value="cover">Couvrir</option>
                    <option value="fill">Remplir</option>
                  </select>
                </div>

                <div className="property-row">
                  <label>Afficher la bordure:</label>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={localProperties.showBorder || false}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'showBorder', e.target.checked)}
                    />
                    <span className="toggle-slider"></span>
                  </label>
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
            )}

            {/* Contrôles pour le type de document */}
            {selectedElement.type === 'document_type' && (
              <div className="properties-group">
                <h4>📋 Type de Document</h4>

                <div className="property-row">
                  <label>Type de document:</label>
                  <select
                    value={localProperties.documentType || 'invoice'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'documentType', e.target.value)}
                  >
                    <option value="invoice">Facture</option>
                    <option value="quote">Devis</option>
                    <option value="receipt">Reçu</option>
                    <option value="order">Commande</option>
                    <option value="credit_note">Avoir</option>
                  </select>
                </div>

                <FontControls
                  elementId={selectedElement.id}
                  properties={localProperties}
                  onPropertyChange={handlePropertyChange}
                />

                <div className="property-row">
                  <label>Alignement du texte:</label>
                  <select
                    value={localProperties.textAlign || 'center'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'textAlign', e.target.value)}
                  >
                    <option value="left">Gauche</option>
                    <option value="center">Centre</option>
                    <option value="right">Droite</option>
                  </select>
                </div>

                <ColorPicker
                  label="Couleur du texte"
                  value={localProperties.color}
                  onChange={(value) => handlePropertyChange(selectedElement.id, 'color', value)}
                  presets={['#1e293b', '#334155', '#475569', '#64748b', '#000000', '#dc2626', '#059669', '#7c3aed']}
                />

                <div className="property-row">
                  <label>Afficher la bordure:</label>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={localProperties.showBorder || false}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'showBorder', e.target.checked)}
                    />
                    <span className="toggle-slider"></span>
                  </label>
                </div>

                <ColorPicker
                  label="Couleur de fond"
                  value={localProperties.backgroundColor}
                  onChange={(value) => handlePropertyChange(selectedElement.id, 'backgroundColor', value)}
                  presets={['transparent', '#ffffff', '#f8fafc', '#fef3c7', '#ecfdf5', '#f0f9ff']}
                />
              </div>
            )}

            {/* Contrôles pour les informations entreprise */}
            {selectedElement.type === 'company_info' && (
              <div className="properties-group">
                <h4>📄 Informations Entreprise</h4>

                <div className="property-row">
                  <label>Champs à afficher:</label>
                  <div className="checkbox-group">
                    {[
                      { key: 'name', label: 'Nom' },
                      { key: 'address', label: 'Adresse' },
                      { key: 'phone', label: 'Téléphone' },
                      { key: 'email', label: 'Email' },
                      { key: 'website', label: 'Site web' },
                      { key: 'vat', label: 'N° TVA' }
                    ].map(({ key, label }) => (
                      <label key={key} className="checkbox-item">
                        <input
                          type="checkbox"
                          checked={localProperties.fields?.includes(key) ?? true}
                          onChange={(e) => {
                            const currentFields = localProperties.fields || ['name', 'address', 'phone', 'email', 'website', 'vat'];
                            const newFields = e.target.checked
                              ? [...currentFields, key]
                              : currentFields.filter(f => f !== key);
                            handlePropertyChange(selectedElement.id, 'fields', newFields);
                          }}
                        />
                        {label}
                      </label>
                    ))}
                  </div>
                </div>

                <FontControls
                  elementId={selectedElement.id}
                  properties={localProperties}
                  onPropertyChange={handlePropertyChange}
                />

                <div className="property-row">
                  <label>Alignement du texte:</label>
                  <select
                    value={localProperties.textAlign || 'left'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'textAlign', e.target.value)}
                  >
                    <option value="left">Gauche</option>
                    <option value="center">Centre</option>
                    <option value="right">Droite</option>
                  </select>
                </div>
              </div>
            )}

            {/* Contrôles pour le numéro de commande */}
            {selectedElement.type === 'order_number' && (
              <div className="properties-group">
                <h4>🔢 Numéro de Commande</h4>

                <div className="property-row">
                  <label>Format:</label>
                  <input
                    type="text"
                    value={localProperties.format || 'Commande #{order_number} - {order_date}'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'format', e.target.value)}
                    placeholder="Commande #{order_number} - {order_date}"
                  />
                </div>

                <div className="property-row">
                  <label>Afficher l'étiquette:</label>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={localProperties.showLabel || true}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'showLabel', e.target.checked)}
                    />
                    <span className="toggle-slider"></span>
                  </label>
                </div>

                {localProperties.showLabel && (
                  <div className="property-row">
                    <label>Texte de l'étiquette:</label>
                    <input
                      type="text"
                      value={localProperties.labelText || 'N° de commande:'}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'labelText', e.target.value)}
                      placeholder="N° de commande:"
                    />
                  </div>
                )}

                <FontControls
                  elementId={selectedElement.id}
                  properties={localProperties}
                  onPropertyChange={handlePropertyChange}
                />

                <div className="property-row">
                  <label>Alignement du texte:</label>
                  <select
                    value={localProperties.textAlign || 'right'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'textAlign', e.target.value)}
                  >
                    <option value="left">Gauche</option>
                    <option value="center">Centre</option>
                    <option value="right">Droite</option>
                  </select>
                </div>

                <ColorPicker
                  label="Couleur du texte"
                  value={localProperties.color}
                  onChange={(value) => handlePropertyChange(selectedElement.id, 'color', value)}
                  presets={['#1e293b', '#334155', '#475569', '#64748b', '#000000', '#333333']}
                />
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
  }, [activeTab, selectedElement, localProperties, handlePropertyChange, selectedElements.length]);

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