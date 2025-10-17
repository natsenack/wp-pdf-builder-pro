import { useState, useEffect, useCallback, useMemo, memo } from 'react';
import { useElementCustomization } from '../hooks/useElementCustomization';
import { useElementSynchronization } from '../hooks/useElementSynchronization';
import { elementCustomizationService } from '../services/ElementCustomizationService';
import { isPropertyAllowed, ELEMENT_TYPE_MAPPING } from '../utilities/elementPropertyRestrictions';

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
  // Logo entreprise (même propriétés que logo)
  company_logo: {
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
  // Mentions légales
  mentions: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['mentions'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Informations entreprise
  company_info: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['company_fields'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Type de document
  document_type: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['document_type'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Numéro de commande
  order_number: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['order_number'],
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

const PropertiesPanel = memo(({
  selectedElements,
  elements,
  onPropertyChange,
  onBatchUpdate
}) => {
  // États pour mémoriser les valeurs précédentes
  const [previousBackgroundColor, setPreviousBackgroundColor] = useState('#ffffff');
  const [previousBorderWidth, setPreviousBorderWidth] = useState(0);
  const [previousBorderColor, setPreviousBorderColor] = useState('#000000');
  const [isBackgroundEnabled, setIsBackgroundEnabled] = useState(false);
  const [isBorderEnabled, setIsBorderEnabled] = useState(false);

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
    3000 // autoSaveDelay - increased to reduce AJAX calls
  );

  // Obtenir l'élément sélectionné (mémorisé pour éviter les re-renders)
  const selectedElement = useMemo(() => {
    return selectedElements.length > 0
      ? elements.find(el => el.id === selectedElements[0])
      : null;
  }, [selectedElements, elements]);

  // Mettre à jour les valeurs précédentes quand l'élément change
  useEffect(() => {
    if (selectedElement) {
      // Initialiser les valeurs précédentes avec les valeurs actuelles de l'élément
      setPreviousBackgroundColor(selectedElement.backgroundColor || '#ffffff');
      // Pour borderWidth, s'assurer qu'on a au moins 1 pour la restauration
      const initialBorderWidth = selectedElement.borderWidth && selectedElement.borderWidth > 0 ? selectedElement.borderWidth : 1;
      setPreviousBorderWidth(initialBorderWidth);
      setPreviousBorderColor(selectedElement.borderColor || '#000000');
    }
  }, [selectedElement]); // Ne dépendre que de selectedElement pour éviter les boucles

  // Synchroniser l'état du toggle fond
  useEffect(() => {
    const shouldBeEnabled = !!localProperties.backgroundColor && localProperties.backgroundColor !== 'transparent';
    setIsBackgroundEnabled(shouldBeEnabled);
  }, [localProperties.backgroundColor]);

  // Synchroniser l'état du toggle bordures
  useEffect(() => {
    setIsBorderEnabled(!!localProperties.border && (localProperties.borderWidth || 0) > 0);
  }, [localProperties.border, localProperties.borderWidth]);

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
    // Vérifier si la propriété backgroundColor est autorisée pour ce type d'élément
    const isBackgroundAllowed = selectedElement?.type ? isPropertyAllowed(selectedElement.type, 'backgroundColor') : true;
    if (!isBackgroundAllowed) {
      console.warn('Fond non contrôlable pour ce type d\'élément');
      return;
    }

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
  }, [selectedElement?.backgroundColor, previousBackgroundColor, handlePropertyChange, selectedElement?.type]);

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
                    disabled={false}
                    onChange={(e) => {
                      if (e.target.checked) {
                        handlePropertyChange(selectedElement.id, 'backgroundColor', '#ffffff');
                      } else {
                        handlePropertyChange(selectedElement.id, 'backgroundColor', 'transparent');
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

            {/* Contrôles de police (uniquement pour les éléments qui les supportent, sauf les tableaux de produits) */}
            {allowedControls.includes('font') && selectedElement.type !== 'product_table' && (
              <FontControls
                elementId={selectedElement.id}
                properties={localProperties}
                onPropertyChange={handlePropertyChange}
              />
            )}

            {/* Bordures (uniquement si activées et autorisées) */}
            {allowedControls.includes('borders') && localProperties.borderWidth >= 0 && (
              <div className="properties-group">
                <h4>🔲 Bordures & Coins Arrondis</h4>

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

                  <div className="property-row">
                    <label>Coins arrondis:</label>
                    <div className="slider-container">
                      <input
                        type="range"
                        min="0"
                        max="50"
                        value={localProperties.borderRadius ?? 4}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', safeParseInt(e.target.value, 0))}
                        className="slider"
                      />
                      <span className="slider-value">{localProperties.borderRadius ?? 4}px</span>
                    </div>
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

            {/* Calques et profondeur (toujours disponible sauf pour les tableaux de produits) */}
            {allowedControls.includes('layers') && selectedElement.type !== 'product_table' && (
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
            {/* Contenu texte (uniquement pour les éléments texte) */}
            {allowedControls.includes('text') && selectedElement.type === 'text' && (
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
                      [Ord] N° commande
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

            {/* Variables dynamiques pour les éléments layout (header/footer/section) */}
            {allowedControls.includes('variables') && (selectedElement.type === 'layout-header' ||
              selectedElement.type === 'layout-footer' || selectedElement.type === 'layout-section') && (
              <div className="properties-group">
                <h4>🔄 Variables dynamiques</h4>

                <div className="property-row">
                  <label>Variables disponibles:</label>
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
                      [Ord] N° commande
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

            {/* Contrôles image (uniquement pour les éléments logo) */}
            {allowedControls.includes('image') && (selectedElement.type === 'logo' || selectedElement.type === 'company_logo') && (
              <div className="properties-group">
                <h4>[Img] Image</h4>

                <div className="property-row">
                  <label>URL de l'image:</label>
                  <input
                    type="url"
                    value={localProperties.imageUrl || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'imageUrl', e.target.value)}
                    placeholder="https://exemple.com/logo.jpg"
                  />
                </div>

                <div className="property-row">
                  <label>Alt text:</label>
                  <input
                    type="text"
                    value={localProperties.alt || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'alt', e.target.value)}
                    placeholder="Logo de l'entreprise"
                  />
                </div>

                <div className="property-row">
                  <label>Adaptation:</label>
                  <select
                    value={localProperties.fit || 'contain'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'fit', e.target.value)}
                  >
                    <option value="contain">Contenir (intégral)</option>
                    <option value="cover">Couvrir (zoom)</option>
                    <option value="fill">Remplir</option>
                    <option value="none">Aucune</option>
                  </select>
                </div>
              </div>
            )}

            {/* Contrôles tableau produits (uniquement pour les éléments product_table) */}
            {allowedControls.includes('table') && selectedElement.type === 'product_table' && (
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
                  <div className="table-style-selector">
                    {[
                      {
                        value: 'default',
                        label: 'Défaut',
                        headerBg: '#f8fafc',
                        headerBorder: '#e2e8f0',
                        rowBorder: '#f1f5f9',
                        altRowBg: '#fafbfc',
                        borderWidth: 1,
                        textColor: '#334155'
                      },
                      {
                        value: 'classic',
                        label: 'Classique',
                        headerBg: '#1e293b',
                        headerBorder: '#334155',
                        rowBorder: '#334155',
                        altRowBg: '#ffffff',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'striped',
                        label: 'Alterné',
                        headerBg: '#3b82f6',
                        headerBorder: '#2563eb',
                        rowBorder: '#e2e8f0',
                        altRowBg: '#f8fafc',
                        borderWidth: 1,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'bordered',
                        label: 'Encadré',
                        headerBg: '#ffffff',
                        headerBorder: '#374151',
                        rowBorder: '#d1d5db',
                        altRowBg: '#ffffff',
                        borderWidth: 2,
                        textColor: '#111827'
                      },
                      {
                        value: 'minimal',
                        label: 'Minimal',
                        headerBg: '#ffffff',
                        headerBorder: '#f3f4f6',
                        rowBorder: '#f9fafb',
                        altRowBg: '#ffffff',
                        borderWidth: 0.5,
                        textColor: '#6b7280'
                      },
                      {
                        value: 'modern',
                        label: 'Moderne',
                        gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        headerBorder: '#5b21b6',
                        rowBorder: '#e9d5ff',
                        altRowBg: '#faf5ff',
                        borderWidth: 1,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'blue_ocean',
                        label: 'Océan Bleu',
                        gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                        headerBorder: '#1e40af',
                        rowBorder: '#dbeafe',
                        altRowBg: '#eff6ff',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'emerald_forest',
                        label: 'Forêt Émeraude',
                        gradient: 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
                        headerBorder: '#065f46',
                        rowBorder: '#d1fae5',
                        altRowBg: '#ecfdf5',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'sunset_orange',
                        label: 'Coucher Orange',
                        gradient: 'linear-gradient(135deg, #9a3412 0%, #f97316 100%)',
                        headerBorder: '#c2410c',
                        rowBorder: '#fed7aa',
                        altRowBg: '#fff7ed',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'royal_purple',
                        label: 'Royal Violet',
                        gradient: 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)',
                        headerBorder: '#7c3aed',
                        rowBorder: '#e9d5ff',
                        altRowBg: '#faf5ff',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'rose_pink',
                        label: 'Rose Bonbon',
                        gradient: 'linear-gradient(135deg, #be185d 0%, #f472b6 100%)',
                        headerBorder: '#db2777',
                        rowBorder: '#fce7f3',
                        altRowBg: '#fdf2f8',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'teal_aqua',
                        label: 'Aigue-marine',
                        gradient: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
                        headerBorder: '#0d9488',
                        rowBorder: '#ccfbf1',
                        altRowBg: '#f0fdfa',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'crimson_red',
                        label: 'Rouge Cramoisi',
                        gradient: 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)',
                        headerBorder: '#dc2626',
                        rowBorder: '#fecaca',
                        altRowBg: '#fef2f2',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'amber_gold',
                        label: 'Or Ambré',
                        gradient: 'linear-gradient(135deg, #92400e 0%, #f59e0b 100%)',
                        headerBorder: '#d97706',
                        rowBorder: '#fef3c7',
                        altRowBg: '#fffbeb',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'indigo_night',
                        label: 'Nuit Indigo',
                        gradient: 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)',
                        headerBorder: '#4338ca',
                        rowBorder: '#e0e7ff',
                        altRowBg: '#eef2ff',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'slate_gray',
                        label: 'Ardoise',
                        gradient: 'linear-gradient(135deg, #374151 0%, #6b7280 100%)',
                        headerBorder: '#4b5563',
                        rowBorder: '#f3f4f6',
                        altRowBg: '#f9fafb',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'coral_sunset',
                        label: 'Corail Couchant',
                        gradient: 'linear-gradient(135deg, #c2410c 0%, #fb7185 100%)',
                        headerBorder: '#ea580c',
                        rowBorder: '#fed7d7',
                        altRowBg: '#fef7f7',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'mint_green',
                        label: 'Menthe Fraîche',
                        gradient: 'linear-gradient(135deg, #065f46 0%, #34d399 100%)',
                        headerBorder: '#047857',
                        rowBorder: '#d1fae5',
                        altRowBg: '#ecfdf5',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'violet_dream',
                        label: 'Rêve Violet',
                        gradient: 'linear-gradient(135deg, #6d28d9 0%, #c084fc 100%)',
                        headerBorder: '#8b5cf6',
                        rowBorder: '#ede9fe',
                        altRowBg: '#f5f3ff',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'sky_blue',
                        label: 'Ciel Bleu',
                        gradient: 'linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%)',
                        headerBorder: '#0284c7',
                        rowBorder: '#bae6fd',
                        altRowBg: '#f0f9ff',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'forest_green',
                        label: 'Vert Forêt',
                        gradient: 'linear-gradient(135deg, #14532d 0%, #22c55e 100%)',
                        headerBorder: '#15803d',
                        rowBorder: '#bbf7d0',
                        altRowBg: '#f0fdf4',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'ruby_red',
                        label: 'Rouge Rubis',
                        gradient: 'linear-gradient(135deg, #b91c1c 0%, #f87171 100%)',
                        headerBorder: '#dc2626',
                        rowBorder: '#fecaca',
                        altRowBg: '#fef2f2',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'golden_yellow',
                        label: 'Jaune Doré',
                        gradient: 'linear-gradient(135deg, #a16207 0%, #eab308 100%)',
                        headerBorder: '#ca8a04',
                        rowBorder: '#fef08a',
                        altRowBg: '#fefce8',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'navy_blue',
                        label: 'Bleu Marine',
                        gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                        headerBorder: '#1e40af',
                        rowBorder: '#dbeafe',
                        altRowBg: '#eff6ff',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'burgundy_wine',
                        label: 'Vin Bordeaux',
                        gradient: 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
                        headerBorder: '#991b1b',
                        rowBorder: '#fecaca',
                        altRowBg: '#fef2f2',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'lavender_purple',
                        label: 'Lavande',
                        gradient: 'linear-gradient(135deg, #7c2d12 0%, #a855f7 100%)',
                        headerBorder: '#9333ea',
                        rowBorder: '#e9d5ff',
                        altRowBg: '#faf5ff',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'ocean_teal',
                        label: 'Océan Sarcelle',
                        gradient: 'linear-gradient(135deg, #134e4a 0%, #14b8a6 100%)',
                        headerBorder: '#0f766e',
                        rowBorder: '#ccfbf1',
                        altRowBg: '#f0fdfa',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'cherry_blossom',
                        label: 'Cerisier',
                        gradient: 'linear-gradient(135deg, #be185d 0%, #fb7185 100%)',
                        headerBorder: '#db2777',
                        rowBorder: '#fce7f3',
                        altRowBg: '#fdf2f8',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      },
                      {
                        value: 'autumn_orange',
                        label: 'Automne',
                        gradient: 'linear-gradient(135deg, #9a3412 0%, #fb923c 100%)',
                        headerBorder: '#ea580c',
                        rowBorder: '#fed7aa',
                        altRowBg: '#fff7ed',
                        borderWidth: 1.5,
                        textColor: '#ffffff'
                      }
                    ].map((style) => (
                      <button
                        key={style.value}
                        type="button"
                        className={`table-style-option ${localProperties.tableStyle === style.value ? 'active' : ''}`}
                        onClick={() => handlePropertyChange(selectedElement.id, 'tableStyle', style.value)}
                        title={`${style.label} - Style ${style.label.toLowerCase()} avec dégradé moderne`}
                      >
                        <div className="table-preview" style={{ maxHeight: '60px', overflow: 'hidden' }}>
                          {/* Header row */}
                          <div
                            className="table-header"
                            style={{
                              background: style.gradient || style.headerBg,
                              border: `${style.borderWidth}px solid ${style.headerBorder}`,
                              borderBottom: 'none',
                              color: style.textColor
                            }}
                          >
                            <div className="table-cell" style={{ borderRight: `${style.borderWidth}px solid ${style.headerBorder}` }}>Produit</div>
                            <div className="table-cell" style={{ borderRight: `${style.borderWidth}px solid ${style.headerBorder}` }}>Qté</div>
                            <div className="table-cell">Prix</div>
                          </div>
                          {/* Data rows */}
                          <div
                            className="table-row"
                            style={{
                              backgroundColor: style.altRowBg,
                              border: `${style.borderWidth}px solid ${style.rowBorder}`,
                              borderTop: 'none',
                              color: style.textColor
                            }}
                          >
                            <div className="table-cell" style={{ borderRight: `${style.borderWidth}px solid ${style.rowBorder}` }}>Article 1</div>
                            <div className="table-cell" style={{ borderRight: `${style.borderWidth}px solid ${style.rowBorder}` }}>2</div>
                            <div className="table-cell">15.99€</div>
                          </div>
                          <div
                            className="table-row"
                            style={{
                              backgroundColor: 'white',
                              border: `${style.borderWidth}px solid ${style.rowBorder}`,
                              borderTop: 'none',
                              color: style.textColor
                            }}
                          >
                            <div className="table-cell" style={{ borderRight: `${style.borderWidth}px solid ${style.rowBorder}` }}>Article 2</div>
                            <div className="table-cell" style={{ borderRight: `${style.borderWidth}px solid ${style.rowBorder}` }}>1</div>
                            <div className="table-cell">8.50€</div>
                          </div>
                        </div>
                        <span className="style-label">{style.label}</span>
                      </button>
                    ))}
                  </div>
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

                <div className="property-row">
                  <label>Afficher les bordures des cellules:</label>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={localProperties.showBorders ?? true}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'showBorders', e.target.checked)}
                    />
                    <span className="toggle-slider"></span>
                  </label>
                </div>

                <div className="property-row">
                  <label>Couleurs individuelles des produits:</label>
                  <div className="product-colors-editor">
                    {(localProperties.previewProducts || [
                      { name: 'Produit 1', quantity: 2, price: 15.99, total: 31.98 },
                      { name: 'Produit 2', quantity: 1, price: 8.50, total: 8.50 },
                      { name: 'Produit 3', quantity: 3, price: 12.00, total: 36.00 }
                    ]).map((product, index) => (
                      <div key={index} className="product-color-item">
                        <span className="product-name">{product.name || `Produit ${index + 1}`}</span>
                        <div className="color-controls">
                          <div className="color-control">
                            <label>Fond:</label>
                            <input
                              type="color"
                              value={product.backgroundColor || '#ffffff'}
                              onChange={(e) => {
                                const newProducts = [...(localProperties.previewProducts || [
                                  { name: 'Produit 1', quantity: 2, price: 15.99, total: 31.98 },
                                  { name: 'Produit 2', quantity: 1, price: 8.50, total: 8.50 },
                                  { name: 'Produit 3', quantity: 3, price: 12.00, total: 36.00 }
                                ])];
                                newProducts[index] = { ...newProducts[index], backgroundColor: e.target.value };
                                handlePropertyChange(selectedElement.id, 'previewProducts', newProducts);
                              }}
                            />
                          </div>
                          <div className="color-control">
                            <label>Texte:</label>
                            <input
                              type="color"
                              value={product.color || '#000000'}
                              onChange={(e) => {
                                const newProducts = [...(localProperties.previewProducts || [
                                  { name: 'Produit 1', quantity: 2, price: 15.99, total: 31.98 },
                                  { name: 'Produit 2', quantity: 1, price: 8.50, total: 8.50 },
                                  { name: 'Produit 3', quantity: 3, price: 12.00, total: 36.00 }
                                ])];
                                newProducts[index] = { ...newProducts[index], color: e.target.value };
                                handlePropertyChange(selectedElement.id, 'previewProducts', newProducts);
                              }}
                            />
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* Contrôles informations client (uniquement pour les éléments customer_info) */}
            {allowedControls.includes('customer_fields') && selectedElement.type === 'customer_info' && (
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
                      { key: 'vat', label: 'N° TVA' },
                      { key: 'siret', label: 'SIRET' }
                    ].map(({ key, label }) => (
                      <label key={key} className="checkbox-item">
                        <input
                          type="checkbox"
                          checked={localProperties.fields?.includes(key) ?? true}
                          onChange={(e) => {
                            const currentFields = localProperties.fields || ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'];
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
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'spacing', safeParseInt(e.target.value, 10))}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.spacing || 8}px</span>
                  </div>
                </div>
              </div>
            )}

            {/* Contrôles mentions légales (uniquement pour les éléments mentions) */}
            {allowedControls.includes('mentions') && selectedElement.type === 'mentions' && (
              <div className="properties-group">
                <h4>📄 Mentions légales</h4>

                <div className="property-row">
                  <label>Informations à afficher:</label>
                  <div className="checkbox-group">
                    {[
                      { key: 'showEmail', label: 'Email' },
                      { key: 'showPhone', label: 'Téléphone' },
                      { key: 'showSiret', label: 'SIRET' },
                      { key: 'showVat', label: 'N° TVA' },
                      { key: 'showAddress', label: 'Adresse' },
                      { key: 'showWebsite', label: 'Site web' },
                      { key: 'showCustomText', label: 'Texte personnalisé' }
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

                {localProperties.showCustomText && (
                  <div className="property-row">
                    <label>Texte personnalisé:</label>
                    <input
                      type="text"
                      value={localProperties.customText || ''}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'customText', e.target.value)}
                      placeholder="Ex: Mentions légales personnalisées..."
                    />
                  </div>
                )}

                <div className="property-row">
                  <label>Disposition:</label>
                  <select
                    value={localProperties.layout || 'horizontal'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'layout', e.target.value)}
                  >
                    <option value="horizontal">Horizontale</option>
                    <option value="vertical">Verticale</option>
                  </select>
                </div>

                <div className="property-row">
                  <label>Séparateur:</label>
                  <input
                    type="text"
                    value={localProperties.separator || ' • '}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'separator', e.target.value)}
                    placeholder=" • "
                    style={{ width: '60px' }}
                  />
                </div>

                <div className="property-row">
                  <label>Interligne:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0.8"
                      max="2.0"
                      step="0.1"
                      value={localProperties.lineHeight || 1.2}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'lineHeight', parseFloat(e.target.value))}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.lineHeight || 1.2}</span>
                  </div>
                </div>
              </div>
            )}

            {/* Contrôles informations entreprise (uniquement pour les éléments company_info) */}
            {allowedControls.includes('company_fields') && selectedElement.type === 'company_info' && (
              <div className="properties-group">
                <h4>🏢 Informations Entreprise</h4>

                <div className="property-row">
                  <label>Champs à afficher:</label>
                  <div className="checkbox-group">
                    {[
                      { key: 'name', label: 'Nom' },
                      { key: 'address', label: 'Adresse' },
                      { key: 'phone', label: 'Téléphone' },
                      { key: 'email', label: 'Email' },
                      { key: 'website', label: 'Site web' },
                      { key: 'vat', label: 'N° TVA' },
                      { key: 'rcs', label: 'RCS' },
                      { key: 'siret', label: 'SIRET' }
                    ].map(({ key, label }) => (
                      <label key={key} className="checkbox-item">
                        <input
                          type="checkbox"
                          checked={localProperties.fields?.includes(key) ?? true}
                          onChange={(e) => {
                            const currentFields = localProperties.fields || ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
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
                      checked={localProperties.showLabels ?? false}
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
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'spacing', safeParseInt(e.target.value, 10))}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.spacing || 8}px</span>
                  </div>
                </div>
              </div>
            )}

            {/* Contrôles type de document (uniquement pour les éléments document_type) */}
            {allowedControls.includes('document_type') && selectedElement.type === 'document_type' && (
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
              </div>
            )}

            {/* Contrôles numéro de commande (uniquement pour les éléments order_number) */}
            {allowedControls.includes('order_number') && selectedElement.type === 'order_number' && (
              <div className="properties-group">
                <h4>🔢 Numéro de Commande</h4>

                <div className="property-row">
                  <label>Format d'affichage:</label>
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
                      checked={localProperties.showLabel ?? true}
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
              </div>
            )}

            {/* Contrôles de police disponibles pour tous les éléments qui les supportent */}
            {allowedControls.includes('font') && (
              <FontControls
                elementId={selectedElement.id}
                properties={localProperties}
                onPropertyChange={handlePropertyChange}
              />
            )}

            {/* Contrôles d'image disponibles uniquement pour les éléments logo */}
            {allowedControls.includes('image') && (selectedElement.type === 'logo' || selectedElement.type === 'company_logo') && (
              <div className="properties-group">
                <h4>[Img] Image</h4>

              <div className="property-row">
                <label>URL de l'image:</label>
                <div className="input-with-button">
                  <input
                    type="text"
                    value={localProperties.imageUrl || localProperties.src || ''}
                    onChange={(e) => {
                      handlePropertyChange(selectedElement.id, 'imageUrl', e.target.value);
                      handlePropertyChange(selectedElement.id, 'src', e.target.value);
                    }}
                    placeholder="https://exemple.com/image.png"
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
                          z-index: 100;
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
                        title.textContent = 'Sélectionner une image depuis la médiathèque';
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
                        closeBtn.onclick = () => {
                          // Vérifier que la modale existe encore avant de la supprimer
                          if (modal && modal.parentNode === document.body) {
                            document.body.removeChild(modal);
                          }
                        };

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
                            handlePropertyChange(selectedElement.id, 'src', item.source_url);
                            // Vérifier que la modale existe encore avant de la supprimer
                            if (modal && modal.parentNode === document.body) {
                              document.body.removeChild(modal);
                            }
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
                    📁 Médiathèque
                  </button>
                </div>
              </div>

              <div className="property-row">
                <label>Texte alternatif:</label>
                <input
                  type="text"
                  value={localProperties.alt || ''}
                  onChange={(e) => handlePropertyChange(selectedElement.id, 'alt', e.target.value)}
                  placeholder="Description de l'image"
                />
              </div>

              <div className="property-row">
                <label>Ajustement:</label>
                <select
                  value={localProperties.objectFit || localProperties.fit || 'cover'}
                  onChange={(e) => {
                    handlePropertyChange(selectedElement.id, 'objectFit', e.target.value);
                    handlePropertyChange(selectedElement.id, 'fit', e.target.value);
                  }}
                >
                  <option value="cover">Couvrir</option>
                  <option value="contain">Contenir</option>
                  <option value="fill">Remplir</option>
                  <option value="none">Aucun</option>
                  <option value="scale-down">Réduire</option>
                </select>
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
                  defaultColor="#333333"
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

            {/* Contrôles de contenu disponibles pour tous les éléments sauf les tableaux de produits */}
            {selectedElement.type !== 'product_table' && (
              <div className="properties-group">
                <h4>� Contenu</h4>

                <div className="property-row">
                  <label>Texte/Contenu:</label>
                  <input
                    type="text"
                    value={localProperties.content || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                    placeholder="Texte à afficher"
                  />
                </div>

                <div className="property-row">
                  <label>Format:</label>
                  <input
                    type="text"
                    value={localProperties.format || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'format', e.target.value)}
                    placeholder="Format d'affichage (optionnel)"
                  />
                </div>

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
              </div>
            )}

            {/* Contrôles de champs disponibles pour tous les éléments sauf les tableaux de produits */}
            {selectedElement.type !== 'product_table' && (
              <div className="properties-group">
                <h4>📋 Champs & Options</h4>

                <div className="property-row">
                  <label>Champs à afficher:</label>
                  <div className="checkbox-group">
                    {[
                      { key: 'name', label: 'Nom' },
                      { key: 'address', label: 'Adresse' },
                      { key: 'phone', label: 'Téléphone' },
                      { key: 'email', label: 'Email' },
                      { key: 'website', label: 'Site web' },
                      { key: 'vat', label: 'N° TVA' },
                      { key: 'image', label: 'Image' },
                      { key: 'sku', label: 'SKU' },
                      { key: 'quantity', label: 'Quantité' },
                      { key: 'price', label: 'Prix' },
                      { key: 'total', label: 'Total' }
                    ].map(({ key, label }) => (
                      <label key={key} className="checkbox-item">
                        <input
                          type="checkbox"
                          checked={localProperties.fields?.includes(key) ?? false}
                          onChange={(e) => {
                            const currentFields = localProperties.fields || [];
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
                  <label>Afficher l'étiquette:</label>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={localProperties.showLabel ?? false}
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
                      value={localProperties.labelText || ''}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'labelText', e.target.value)}
                      placeholder="Texte de l'étiquette"
                    />
                  </div>
                )}

                <div className="property-row">
                  <label>Afficher les bordures:</label>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={localProperties.showBorders ?? true}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'showBorders', e.target.checked)}
                    />
                    <span className="toggle-slider"></span>
                  </label>
                </div>

                <div className="property-row">
                  <label>Afficher les en-têtes:</label>
                  <label className="toggle">
                    <input
                      type="checkbox"
                      checked={localProperties.showHeaders ?? false}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'showHeaders', e.target.checked)}
                    />
                    <span className="toggle-slider"></span>
                  </label>
                </div>
              </div>
            )}
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
