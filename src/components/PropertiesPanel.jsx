import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { useElementCustomization } from '../hooks/useElementCustomization';
import { useElementSynchronization } from '../hooks/useElementSynchronization';
import { elementCustomizationService } from '../services/ElementCustomizationService';
import { getControlComponent } from './property-controls';
import '../styles/PropertiesPanel.css';

// Profils de propriétés contextuelles par type d'élément
const ELEMENT_PROPERTY_PROFILES = {
  // Éléments texte
  text: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-header': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-footer': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-section': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Éléments image/logo
  logo: {
    appearance: ['colors', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['image'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Tableaux produits
  product_table: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['table'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Informations client
  customer_info: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['customer_fields'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Type de document
  document_type: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['document_type'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Éléments par défaut (forme géométrique)
  default: {
    appearance: ['colors', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: [],
    effects: ['opacity', 'shadows', 'filters']
  }
};

// Fonction helper pour parser les valeurs numériques de manière sécurisée
const safeParseInt = (value, defaultValue = 0) => {
  if (value === null || value === undefined || value === '') return defaultValue;
  const parsed = parseInt(value, 10);
  return isNaN(parsed) ? defaultValue : parsed;
};

const safeParseFloat = (value, defaultValue = 0) => {
  if (value === null || value === undefined || value === '') return defaultValue;
  const parsed = parseFloat(value);
  return isNaN(parsed) ? defaultValue : parsed;
};

// Composant amélioré pour les contrôles de couleur avec presets
const ColorPicker = ({ label, value, onChange, presets = [], defaultColor = '#ffffff' }) => {
  // Fonction pour valider et normaliser une couleur hex
  const normalizeColor = (color) => {
    if (!color || color === 'transparent') return defaultColor;
    if (color.startsWith('#') && (color.length === 4 || color.length === 7)) return color;
    return defaultColor; // fallback
  };

  // Valeur normalisée pour l'input color
  const inputValue = normalizeColor(value);

  // Fonction pour vérifier si une couleur est valide pour les presets
  const isValidColor = (color) => {
    return color && color !== 'transparent' && color.startsWith('#');
  };

  return (
    <div className="property-row">
      <label>{label}:</label>
      <div className="color-picker-container">
        <input
          type="color"
          value={inputValue}
          onChange={(e) => {
            const newColor = e.target.value;
            onChange(newColor);
          }}
          className="color-input"
          title={`Couleur actuelle: ${value || 'transparent'}`}
        />
        <div className="color-presets">
          {presets.filter(isValidColor).map((preset, index) => (
            <button
              key={index}
              className={`color-preset ${value === preset ? 'active' : ''}`}
              style={{
                backgroundColor: preset,
                border: value === preset ? '2px solid #2563eb' : '1px solid #e2e8f0'
              }}
              onClick={() => onChange(preset)}
              title={`${label}: ${preset}`}
              aria-label={`Sélectionner la couleur ${preset}`}
            />
          ))}
          {/* Bouton spécial pour transparent si dans les presets */}
          {presets.includes('transparent') && (
            <button
              className={`color-preset transparent ${value === 'transparent' ? 'active' : ''}`}
              style={{
                background: value === 'transparent' ?
                  'repeating-conic-gradient(#f0f0f0 0% 25%, #ffffff 0% 50%) 50% / 10px 10px' :
                  'repeating-conic-gradient(#e2e8f0 0% 25%, #ffffff 0% 50%) 50% / 10px 10px',
                border: value === 'transparent' ? '2px solid #2563eb' : '1px solid #e2e8f0'
              }}
              onClick={() => onChange('transparent')}
              title={`${label}: Transparent`}
              aria-label="Rendre transparent"
            />
          )}
        </div>
      </div>
    </div>
  );
};

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
          value={properties.fontSize ?? 14}
          onChange={(e) => onPropertyChange(elementId, 'fontSize', safeParseInt(e.target.value, 14))}
          className="slider"
        />
        <span className="slider-value">{properties.fontSize ?? 14}px</span>
      </div>
    </div>

    <div className="property-row">
      <label>Interligne:</label>
      <div className="slider-container">
        <input
          type="range"
          min="0.8"
          max="3"
          step="0.1"
          value={properties.lineHeight ?? 1.2}
          onChange={(e) => onPropertyChange(elementId, 'lineHeight', safeParseFloat(e.target.value, 1.2))}
          className="slider"
        />
        <span className="slider-value">{properties.lineHeight ?? 1.2}</span>
      </div>
    </div>

    <div className="property-row">
      <label>Espacement lettres:</label>
      <div className="slider-container">
        <input
          type="range"
          min="-2"
          max="10"
          step="0.1"
          value={properties.letterSpacing ?? 0}
          onChange={(e) => onPropertyChange(elementId, 'letterSpacing', safeParseFloat(e.target.value, 0))}
          className="slider"
        />
        <span className="slider-value">{properties.letterSpacing ?? 0}px</span>
      </div>
    </div>

    <div className="property-row">
      <label>Opacité texte:</label>
      <div className="slider-container">
        <input
          type="range"
          min="0"
          max="1"
          step="0.1"
          value={properties.opacity ?? 1}
          onChange={(e) => onPropertyChange(elementId, 'opacity', safeParseFloat(e.target.value, 1))}
          className="slider"
        />
        <span className="slider-value">{Math.round((properties.opacity ?? 1) * 100)}%</span>
      </div>
    </div>

    <div className="property-row">
      <label>Ombre texte:</label>
      <div className="slider-container">
        <input
          type="range"
          min="0"
          max="5"
          step="0.1"
          value={properties.textShadowBlur ?? 0}
          onChange={(e) => onPropertyChange(elementId, 'textShadowBlur', safeParseFloat(e.target.value, 0))}
          className="slider"
        />
        <span className="slider-value">{properties.textShadowBlur ?? 0}px</span>
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
          className={`style-btn ${(properties.textDecoration || '').includes('underline') ? 'active' : ''}`}
          onClick={() => {
            const currentDecorations = properties.textDecoration ? properties.textDecoration.split(' ') : [];
            const hasUnderline = currentDecorations.includes('underline');
            const newDecorations = hasUnderline
              ? currentDecorations.filter(d => d !== 'underline')
              : [...currentDecorations, 'underline'];
            onPropertyChange(elementId, 'textDecoration', newDecorations.join(' ') || 'none');
          }}
          title="Souligné"
        >
          <u>U</u>
        </button>
        <button
          className={`style-btn ${(properties.textDecoration || '').includes('line-through') ? 'active' : ''}`}
          onClick={() => {
            const currentDecorations = properties.textDecoration ? properties.textDecoration.split(' ') : [];
            const hasLineThrough = currentDecorations.includes('line-through');
            const newDecorations = hasLineThrough
              ? currentDecorations.filter(d => d !== 'line-through')
              : [...currentDecorations, 'line-through'];
            onPropertyChange(elementId, 'textDecoration', newDecorations.join(' ') || 'none');
          }}
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

const PropertiesPanel = React.memo(({
  selectedElements,
  elements,
  onPropertyChange,
  onBatchUpdate
}) => {
  // États pour mémoriser les valeurs précédentes
  const [previousBackgroundColor, setPreviousBackgroundColor] = useState('#ffffff');
  const [previousBorderWidth, setPreviousBorderWidth] = useState(0);
  const [previousBorderColor, setPreviousBorderColor] = useState('#000000');
  const [isBackgroundEnabled, setIsBackgroundEnabled] = useState(true);
  const [isBorderEnabled, setIsBorderEnabled] = useState(false);

  // Log des props pour débogage (seulement quand elles changent)
  useEffect(() => {
  }, [selectedElements?.length, elements?.length]); // Éviter les références d'objets instables

  // Utiliser les hooks de personnalisation et synchronisation
  const {
    localProperties,
    activeTab,
    setActiveTab,
    handlePropertyChange: customizationChange
  } = useElementCustomization(selectedElements, elements, onPropertyChange);

  // Log du hook (seulement quand il change)
  // useEffect(() => {
  // }, [activeTab, selectedElement?.id]); // Éviter de logger localProperties qui change souvent

  const { syncImmediate, syncBatch } = useElementSynchronization(
    elements,
    onPropertyChange,
    onBatchUpdate,
    true, // autoSave
    1000 // autoSaveDelay
  );

  // Obtenir l'élément sélectionné (mémorisé pour éviter les re-renders)
  const selectedElement = useMemo(() => {
    return selectedElements.length > 0
      ? elements.find(el => el.id === selectedElements[0])
      : null;
  }, [selectedElements, elements]);

  // Mettre à jour les valeurs précédentes et l'état des toggles quand l'élément change
  useEffect(() => {
    if (selectedElement) {
      // Initialiser les valeurs précédentes avec les valeurs actuelles de l'élément
      setPreviousBackgroundColor(selectedElement.backgroundColor || '#ffffff');
      // Pour borderWidth, s'assurer qu'on a au moins 1 pour la restauration
      const initialBorderWidth = selectedElement.borderWidth && selectedElement.borderWidth > 0 ? selectedElement.borderWidth : 1;
      setPreviousBorderWidth(initialBorderWidth);
      setPreviousBorderColor(selectedElement.borderColor || '#000000');

      // Initialiser l'état des toggles basé sur les propriétés actuelles
      setIsBackgroundEnabled(false); // Désactivé par défaut
      setIsBorderEnabled(!!selectedElement.borderWidth && selectedElement.borderWidth > 0);
    }
  }, [selectedElement?.id]); // Ne dépendre que de l'ID de l'élément sélectionné

  // Gestionnaire unifié de changement de propriété
  const handlePropertyChange = useCallback((elementId, property, value) => {

    // Empêcher la couleur du texte d'être transparente
    if (property === 'color' && value === 'transparent') {
      value = '#333333';
    }

    // Validation via le service (sauf pour les propriétés boolean qui sont toujours valides)
    const isBooleanProperty = typeof value === 'boolean' || property.startsWith('columns.');
    let validatedValue = value; // Valeur par défaut

    if (!isBooleanProperty) {
      try {
        validatedValue = elementCustomizationService.validateProperty(property, value);
        if (validatedValue === undefined || validatedValue === null) {
          console.warn(`Propriété invalide: ${property} = ${value}`);
          return;
        }
      } catch (error) {
        console.warn(`Erreur de validation pour ${property}:`, error);
        return;
      }
    }

    // Utiliser le hook de personnalisation pour la gestion locale
    customizationChange(elementId, property, validatedValue);

    // Synchronisation immédiate pour les changements critiques
    if (['x', 'y', 'width', 'height'].includes(property)) {
      syncImmediate(elementId, property, validatedValue);
    }
  }, [customizationChange, syncImmediate]);

  // Gestionnaire pour le toggle "Aucun fond"
  const handleNoBackgroundToggle = useCallback((elementId, checked) => {

    if (checked) {
      // Sauvegarder la couleur actuelle avant de la désactiver
      if (selectedElement?.backgroundColor && selectedElement.backgroundColor !== 'transparent') {
        setPreviousBackgroundColor(selectedElement.backgroundColor);
      } else if (!previousBackgroundColor) {
        // Si pas de couleur précédente sauvegardée, utiliser la valeur par défaut
        setPreviousBackgroundColor('#ffffff');
      }
      handlePropertyChange(elementId, 'backgroundColor', 'transparent');
    } else {
      // Restaurer la couleur précédente (avec fallback)
      const colorToRestore = previousBackgroundColor || '#ffffff';
      handlePropertyChange(elementId, 'backgroundColor', colorToRestore);
    }
  }, [selectedElement?.backgroundColor, previousBackgroundColor, handlePropertyChange]);

  // Gestionnaire pour le toggle "Aucune bordure"
  const handleNoBorderToggle = useCallback((elementId, checked) => {

    if (checked) {
      // Sauvegarder l'épaisseur actuelle avant de la désactiver
      if (selectedElement?.borderWidth && selectedElement.borderWidth > 0) {
        setPreviousBorderWidth(selectedElement.borderWidth);
      } else {
        // Si pas de bordure ou bordure = 0, sauvegarder 2 comme valeur par défaut (plus visible)
        setPreviousBorderWidth(2);
      }
      handlePropertyChange(elementId, 'borderWidth', 0);
    } else {
      // Restaurer l'épaisseur précédente, au minimum 2
      const widthToRestore = Math.max(previousBorderWidth || 2, 2);
      handlePropertyChange(elementId, 'borderWidth', widthToRestore);
    }
  }, [selectedElement?.borderWidth, previousBorderWidth, handlePropertyChange]);

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
        [Aa] Contenu
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

    // Obtenir le profil de propriétés pour ce type d'élément
    const elementProfile = ELEMENT_PROPERTY_PROFILES[selectedElement.type] || ELEMENT_PROPERTY_PROFILES.default;
    const allowedControls = elementProfile[activeTab] || [];

    switch (activeTab) {
      case 'appearance':
        return (
          <div className="tab-content">
            <div className="properties-group">
              <h4>🎨 Couleurs & Apparence</h4>

              <ColorPicker
                label="Texte"
                value={localProperties.color}
                onChange={(value) => {
                  handlePropertyChange(selectedElement.id, 'color', value);
                }}
                presets={['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#000000']}
                defaultColor="#333333"
              />

              {/* Contrôle du fond */}
              <div className="property-row">
                <span>Fond activé:</span>
                <label className="toggle">
                  <input
                    type="checkbox"
                    checked={isBackgroundEnabled}
                    onChange={(e) => {
                      if (e.target.checked) {
                        const colorToSet = previousBackgroundColor || '#ffffff';
                        handlePropertyChange(selectedElement.id, 'backgroundColor', colorToSet);
                        setIsBackgroundEnabled(true);
                      } else {
                        setPreviousBackgroundColor(localProperties.backgroundColor || '#ffffff');
                        handlePropertyChange(selectedElement.id, 'backgroundColor', 'transparent');
                        setIsBackgroundEnabled(false);
                      }
                    }}
                  />
                  <span className="toggle-slider"></span>
                </label>
              </div>

              {/* Couleur du fond (conditionnelle) */}
              <div style={{
                display: isBackgroundEnabled ? 'block' : 'none',
                transition: 'opacity 0.3s ease'
              }}>
                <ColorPicker
                  label="Fond"
                  value={localProperties.backgroundColor === 'transparent' ? '#ffffff' : localProperties.backgroundColor}
                  onChange={(value) => {
                    handlePropertyChange(selectedElement.id, 'backgroundColor', value);
                  }}
                  presets={['transparent', '#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8']}
                />

                {/* Opacité du fond */}
                <div className="property-row">
                  <label>Opacité fond:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="1"
                      step="0.1"
                      value={localProperties.backgroundOpacity ?? 1}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundOpacity', safeParseFloat(e.target.value, 1))}
                      className="slider"
                    />
                    <span className="slider-value">{Math.round((localProperties.backgroundOpacity ?? 1) * 100)}%</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Contrôles de police (uniquement pour les éléments qui les supportent) */}
            {allowedControls.includes('font') && (
              <FontControls
                elementId={selectedElement.id}
                properties={localProperties}
                onPropertyChange={handlePropertyChange}
              />
            )}

            {/* Bordures (uniquement si activées et autorisées) */}
            {allowedControls.includes('borders') && localProperties.borderWidth >= 0 && (
              <div className="properties-group">
                <h4>🔲 Bordures</h4>

                {/* Contrôle d'activation des bordures */}
                <div className="property-row">
                  <span>Bordures activées:</span>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={isBorderEnabled}
                      onChange={(e) => {
                        if (e.target.checked) {
                          const widthToSet = previousBorderWidth || 1;
                          const colorToSet = previousBorderColor || '#000000';
                          handlePropertyChange(selectedElement.id, 'border', true);
                          handlePropertyChange(selectedElement.id, 'borderWidth', widthToSet);
                          handlePropertyChange(selectedElement.id, 'borderColor', colorToSet);
                          setIsBorderEnabled(true);
                        } else {
                          setPreviousBorderWidth(localProperties.borderWidth || 1);
                          setPreviousBorderColor(localProperties.borderColor || '#000000');
                          handlePropertyChange(selectedElement.id, 'border', false);
                          handlePropertyChange(selectedElement.id, 'borderWidth', 0);
                          setIsBorderEnabled(false);
                        }
                      }}
                    />
                    <span className="toggle-slider"></span>
                  </label>
                </div>

                {/* Contrôles des bordures (conditionnels) */}
                <div style={{
                  display: localProperties.borderWidth > 0 ? 'block' : 'none',
                  transition: 'opacity 0.3s ease'
                }}>
                  <ColorPicker
                    label="Couleur bordure"
                    value={localProperties.borderColor || '#000000'}
                    onChange={(value) => handlePropertyChange(selectedElement.id, 'borderColor', value)}
                    presets={['#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155', '#000000']}
                  />

                  <div className="property-row">
                    <label>Style bordure:</label>
                    <select
                      value={localProperties.borderStyle || 'solid'}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'borderStyle', e.target.value)}
                      className="styled-select"
                    >
                      <option value="solid">Continue</option>
                      <option value="dashed">Tirets</option>
                      <option value="dotted">Pointillés</option>
                      <option value="double">Double</option>
                    </select>
                  </div>

                  <div className="property-row">
                    <label>Épaisseur bordure:</label>
                    <div className="slider-container">
                      <input
                        type="range"
                        min="0"
                        max="10"
                        value={localProperties.borderWidth ?? 1}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', safeParseInt(e.target.value, 1))}
                        className="slider"
                      />
                      <span className="slider-value">{localProperties.borderWidth ?? 1}px</span>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* Coins Arrondis (uniquement si autorisés) */}
            {allowedControls.includes('borders') && (
              <div className="properties-group">
                <h4>🔲 Coins Arrondis</h4>

                <div className="property-row">
                  <label>Coins arrondis:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="50"
                      value={localProperties.borderRadius ?? 0}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', safeParseInt(e.target.value, 0))}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.borderRadius ?? 0}px</span>
                  </div>
                </div>
              </div>
            )}

            {/* Effets (uniquement si autorisés) */}
            {allowedControls.includes('effects') && (
              <div className="properties-group">
                <h4>✨ Effets</h4>

                <ColorPicker
                  label="Ombre"
                  value={localProperties.boxShadowColor || '#000000'}
                  onChange={(value) => handlePropertyChange(selectedElement.id, 'boxShadowColor', value)}
                  presets={['#000000', '#ffffff', '#64748b', '#ef4444', '#3b82f6']}
                />

                <div className="property-row">
                  <label>Flou ombre:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="20"
                      value={localProperties.boxShadowBlur ?? 0}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'boxShadowBlur', safeParseInt(e.target.value, 0))}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.boxShadowBlur ?? 0}px</span>
                  </div>
                </div>

                <div className="property-row">
                  <label>Décalage ombre:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="10"
                      value={localProperties.boxShadowSpread ?? 0}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'boxShadowSpread', safeParseInt(e.target.value, 0))}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.boxShadowSpread ?? 0}px</span>
                  </div>
                </div>
              </div>
            )}
          </div>
        );

      case 'layout':
        return (
          <div className="tab-content">
            {/* Position précise (toujours disponible) */}
            {allowedControls.includes('position') && (
              <div className="properties-group">
                <h4>📍 Position Précise</h4>

                <div className="property-row">
                  <label>X:</label>
                  <div className="input-with-unit">
                    <input
                      type="number"
                      value={Math.round(localProperties.x || 0)}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'x', safeParseInt(e.target.value, 0))}
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
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'y', safeParseInt(e.target.value, 0))}
                      step="1"
                    />
                    <span className="unit">mm</span>
                  </div>
                </div>
              </div>
            )}

            {/* Dimensions avec contraintes (toujours disponible) */}
            {allowedControls.includes('dimensions') && (
              <div className="properties-group">
                <h4>📏 Dimensions</h4>

                <div className="property-row">
                  <label>Largeur:</label>
                  <div className="input-with-unit">
                    <input
                      type="number"
                      value={Math.round(localProperties.width || 100)}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'width', safeParseInt(e.target.value, 100))}
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
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'height', safeParseInt(e.target.value, 50))}
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
            )}

            {/* Transformation (toujours disponible) */}
            {allowedControls.includes('transform') && (
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
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'rotation', safeParseInt(e.target.value, 0))}
                      onDoubleClick={() => handlePropertyChange(selectedElement.id, 'rotation', 0)}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.rotation || 0}°</span>
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
            )}

            {/* Calques et profondeur (toujours disponible) */}
            {allowedControls.includes('layers') && (
              <div className="properties-group">
                <h4>📚 Calques</h4>

                <div className="property-row">
                  <label>Profondeur (Z-index):</label>
                  <input
                    type="number"
                    value={localProperties.zIndex || 0}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'zIndex', safeParseInt(e.target.value, 0))}
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
            )}
          </div>
        );

      case 'content':
        return (
          <div className="tab-content">
            {/* Rendu dynamique des contrôles selon les permissions du profil */}
            {allowedControls.map(controlName => {
              const ControlComponent = getControlComponent(controlName);
              if (!ControlComponent) return null;

              return (
                <ControlComponent
                  key={controlName}
                  elementId={selectedElement.id}
                  properties={localProperties}
                  onPropertyChange={handlePropertyChange}
                />
              );
            })}
          </div>
        );

      case 'effects':
        return (
          <div className="tab-content">
            {/* Transparence & Visibilité (toujours disponible si autorisé) */}
            {allowedControls.includes('opacity') && (
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
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'opacity', safeParseInt(e.target.value, 100))}
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
            )}

            {/* Ombres & Effets (uniquement si autorisé) */}
            {allowedControls.includes('shadows') && (
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
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetX', safeParseInt(e.target.value, 0))}
                        min="-20"
                        max="20"
                      />
                    </div>

                    <div className="property-row">
                      <label>Décalage Y:</label>
                      <input
                        type="number"
                        value={localProperties.shadowOffsetY || 2}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetY', safeParseInt(e.target.value, 0))}
                        min="-20"
                        max="20"
                      />
                    </div>
                  </>
                )}
              </div>
            )}

            {/* Filtres visuels (uniquement si autorisé) */}
            {allowedControls.includes('filters') && (
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
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'brightness', safeParseInt(e.target.value, 100))}
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
            )}
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
});

export default PropertiesPanel;