import { useState, useEffect, useCallback, useMemo, memo } from 'react';
// Import styles for the accordion component so webpack bundles them
import '../../scss/styles/Accordion.css';
import { TEMPLATE_PRESETS, ELEMENT_PROPERTY_PROFILES } from './PropertiesPanel/utils/constants';
import Accordion from './PropertiesPanel/Accordion';
import ColorPicker from './PropertiesPanel/ColorPicker';
import FontControls from './PropertiesPanel/FontControls';
import { shouldShowSection, safeParseFloat, safeParseInt, getSmartPropertyOrder } from './PropertiesPanel/utils/helpers';
import renderColorsSection from './PropertiesPanel/sections/ColorsSection';
import renderTypographySection from './PropertiesPanel/sections/TypographySection';
import renderFontSection from './PropertiesPanel/sections/FontSection';
import renderBordersSection from './PropertiesPanel/sections/BordersSection';
import renderEffectsSection from './PropertiesPanel/sections/EffectsSection';
import { useElementCustomization } from '../hooks/useElementCustomization';
import { useElementSynchronization } from '../hooks/useElementSynchronization';
import { elementCustomizationService } from '../services/ElementCustomizationService';

// TEMPLATE_PRESETS moved to ./PropertiesPanel/utils/constants.js
// ELEMENT_PROPERTY_PROFILES moved to ./PropertiesPanel/utils/constants.js

// Helper functions moved to ./PropertiesPanel/utils/helpers.js

// ColorPicker moved to ./PropertiesPanel/ColorPicker.jsx

// FontControls moved to ./PropertiesPanel/FontControls.jsx

// renderColorsSection moved to ./PropertiesPanel/sections/ColorsSection.jsx

// renderFontSection moved to ./PropertiesPanel/sections/FontSection.jsx

// renderTypographySection moved to ./PropertiesPanel/sections/TypographySection.jsx

// renderBordersSection moved to ./PropertiesPanel/sections/BordersSection.jsx

// renderEffectsSection moved to ./PropertiesPanel/sections/EffectsSection.jsx

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
    return selectedElements.length > 0 ? selectedElements[0] : null;
  }, [selectedElements]);

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

    // DEBUG: Log temporaire pour tracer les changements de template
    if (property === 'template') {
      console.log('[DEBUG] Template changé:', { elementId, oldValue: localProperties.template, newValue: validatedValue });
    }

    // Synchronisation immédiate pour les changements critiques et de style
    if ([
      'x', 'y', 'width', 'height', // Position et dimensions
      'color', 'fontSize', 'fontFamily', 'fontWeight', 'fontStyle', // Texte et typographie
      'textAlign', 'lineHeight', 'letterSpacing', 'textDecoration', // Mise en forme texte
      'backgroundColor', 'backgroundOpacity', // Fond
      'borderColor', 'borderWidth', 'borderStyle', 'borderRadius', // Bordures
      'boxShadowColor', 'boxShadowBlur', 'boxShadowSpread', // Ombres
      'opacity', 'textShadowBlur', // Transparence et effets
      'tablePrimaryColor', 'tableSecondaryColor', // Couleurs thème tableau
      // Assurer une synchronisation immédiate des templates dynamiques
      'template', 'customContent'
    ].includes(property)) {
      syncImmediate(elementId, property, validatedValue);
    }
  }, [customizationChange, syncImmediate]);

  // Gestionnaire pour le toggle "Aucun fond"
  const handleNoBackgroundToggle = useCallback((elementId, checked) => {
    // Vérifier si la propriété backgroundColor est autorisée pour ce type d'élément
    const isBackgroundAllowed = selectedElement?.type ? isPropertyAllowedForElement(selectedElement.type, activeTab, 'backgroundColor') : true;
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

    // Obtenir l'ordre intelligent des propriétés pour ce type d'élément
    const smartOrder = getSmartPropertyOrder(selectedElement.type, activeTab);

    // Obtenir le profil de propriétés pour ce type d'élément
    const elementProfile = ELEMENT_PROPERTY_PROFILES[selectedElement.type] || ELEMENT_PROPERTY_PROFILES['default'];
    const tabProfile = elementProfile[activeTab] || { sections: [], properties: {} };
    const allowedControls = tabProfile.sections || [];

    switch (activeTab) {
      case 'appearance':
        return (
          <div className="tab-content">
            {smartOrder.map(section => {
              switch (section) {
                case 'colors':
                  return renderColorsSection(selectedElement, localProperties, handlePropertyChange, activeTab);
                case 'typography':
                  return renderTypographySection(selectedElement, localProperties, handlePropertyChange, activeTab);
                case 'borders':
                  return allowedControls.includes('borders') ?
                    renderBordersSection(selectedElement, localProperties, handlePropertyChange, isBorderEnabled, setIsBorderEnabled, setPreviousBorderWidth, setPreviousBorderColor, previousBorderWidth, previousBorderColor, activeTab) : null;
                case 'effects':
                  return allowedControls.includes('effects') ?
                    renderEffectsSection(selectedElement, localProperties, handlePropertyChange, activeTab) : null;
                default:
                  return null;
              }
            })}
          </div>
        );

      case 'layout':
        return (
          <div className="tab-content">
            {/* Position précise (toujours disponible) */}
            {allowedControls.includes('position') && (
              <Accordion
                key="position"
                title="Position Précise"
                icon="📍"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}

            {/* Dimensions avec contraintes (toujours disponible) */}
            {allowedControls.includes('dimensions') && (
              <Accordion
                key="dimensions"
                title="Dimensions"
                icon="📏"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}

            {/* Transformation (toujours disponible) */}
            {allowedControls.includes('transform') && (
              <Accordion
                key="transform"
                title="Transformation"
                icon="🔄"
                defaultOpen={false}
                className="properties-accordion"
              >

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

                {/* Mise à l'échelle */}
                <div className="property-row">
                  <label>Mise à l'échelle X:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0.1"
                      max="3.0"
                      step="0.1"
                      value={localProperties.scaleX || 1}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'scaleX', parseFloat(e.target.value))}
                      className="slider"
                    />
                    <span className="slider-value">{(localProperties.scaleX || 1).toFixed(1)}x</span>
                  </div>
                </div>

                <div className="property-row">
                  <label>Mise à l'échelle Y:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0.1"
                      max="3.0"
                      step="0.1"
                      value={localProperties.scaleY || 1}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'scaleY', parseFloat(e.target.value))}
                      className="slider"
                    />
                    <span className="slider-value">{(localProperties.scaleY || 1).toFixed(1)}x</span>
                  </div>
                </div>

                {/* Bouton de remise à l'échelle */}
                <div className="property-row">
                  <label></label>
                  <button
                    className="reset-scale-btn"
                    onClick={() => {
                      handlePropertyChange(selectedElement.id, 'scaleX', 1);
                      handlePropertyChange(selectedElement.id, 'scaleY', 1);
                    }}
                    title="Remettre à l'échelle normale"
                  >
                    🔄 Réinitialiser échelle
                  </button>
                </div>
              </Accordion>
            )}

            {/* Calques et profondeur (toujours disponible sauf pour les tableaux de produits) */}
            {allowedControls.includes('layers') && selectedElement.type !== 'product_table' && (
              <Accordion
                key="layers"
                title="Calques"
                icon="📚"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}
          </div>
        );

      case 'content':
        return (
          <div className="tab-content">
            {/* Contenu texte (uniquement pour les éléments texte) */}
            {allowedControls.includes('text') && selectedElement.type === 'text' && (
              <Accordion
                key="text-content"
                title="Contenu texte"
                icon="📝"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}

            {/* Variables dynamiques pour les éléments layout (header/footer/section) */}
            {allowedControls.includes('variables') && (selectedElement.type === 'layout-header' ||
              selectedElement.type === 'layout-footer' || selectedElement.type === 'layout-section') && (
              <Accordion
                key="dynamic-variables"
                title="Variables dynamiques"
                icon="🔄"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}

            {/* Contrôles tableau produits (uniquement pour les éléments product_table) */}
            {allowedControls.includes('table') && selectedElement.type === 'product_table' && (
              <Accordion
                key="product-table"
                title="Tableau produits"
                icon="📊"
                defaultOpen={false}
                className="properties-accordion"
              >
                {/* Section principale compacte */}
                <div className="table-controls-compact">
                  {/* Colonnes et totaux */}
                  <div className="table-section">
                    <div className="section-title">📋 Configuration</div>
                    <div className="compact-grid">
                      <div className="grid-item">
                        <label className="compact-label">Colonnes:</label>
                        <div className="checkbox-grid">
                          {[
                            { key: 'image', label: 'Img' },
                            { key: 'name', label: 'Nom' },
                            { key: 'sku', label: 'SKU' },
                            { key: 'quantity', label: 'Qté' },
                            { key: 'price', label: 'Prix' },
                            { key: 'total', label: 'Total' }
                          ].map(({ key, label }) => (
                            <label key={key} className="checkbox-compact">
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

                      <div className="grid-item">
                        <label className="compact-label">Totaux:</label>
                        <div className="checkbox-grid">
                          {[
                            { key: 'showSubtotal', label: 'Sous-t.' },
                            { key: 'showShipping', label: 'Port' },
                            { key: 'showTaxes', label: 'TVA' },
                            { key: 'showDiscount', label: 'Remise' },
                            { key: 'showTotal', label: 'Total' }
                          ].map(({ key, label }) => (
                            <label key={key} className="checkbox-compact">
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
                  </div>

                  {/* Bordures */}
                  <div className="table-section">
                    <div className="section-title">🔲 Bordures</div>
                    <div className="border-controls">
                      <label className="toggle-compact">
                        <input
                          type="checkbox"
                          checked={localProperties.showBorders ?? true}
                          onChange={(e) => handlePropertyChange(selectedElement.id, 'showBorders', e.target.checked)}
                        />
                        <span className="toggle-slider-small"></span>
                        Cellules
                      </label>

                      <label className="toggle-compact">
                        <input
                          type="checkbox"
                          checked={localProperties.showTableBorder ?? false}
                          onChange={(e) => handlePropertyChange(selectedElement.id, 'showTableBorder', e.target.checked)}
                        />
                        <span className="toggle-slider-small"></span>
                        Extérieure
                      </label>
                    </div>
                  </div>
                </div>

                {/* Styles du tableau */}
                <div className="table-section">
                  <div className="section-title">🎨 Style du tableau</div>
                  <div className="table-style-selector-compact">
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
                        headerBg: '#e0f2fe',
                        headerBorder: '#0ea5e9',
                        rowBorder: '#f0f9ff',
                        altRowBg: '#f8fafc',
                        borderWidth: 1,
                        textColor: '#0c4a6e'
                      },
                      {
                        value: 'bordered',
                        label: 'Encadré',
                        headerBg: '#f8fafc',
                        headerBorder: '#94a3b8',
                        rowBorder: '#e2e8f0',
                        altRowBg: '#ffffff',
                        borderWidth: 1,
                        textColor: '#475569'
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
                        gradient: 'linear-gradient(135deg, #e9d5ff 0%, #ddd6fe 100%)',
                        headerBorder: '#a855f7',
                        rowBorder: '#f3e8ff',
                        altRowBg: '#faf5ff',
                        borderWidth: 1,
                        textColor: '#6b21a8'
                      },
                      {
                        value: 'blue_ocean',
                        label: 'Océan',
                        gradient: 'linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%)',
                        headerBorder: '#3b82f6',
                        rowBorder: '#eff6ff',
                        altRowBg: '#eff6ff',
                        borderWidth: 1,
                        textColor: '#1e40af'
                      },
                      {
                        value: 'emerald_forest',
                        label: 'Forêt',
                        gradient: 'linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%)',
                        headerBorder: '#10b981',
                        rowBorder: '#ecfdf5',
                        altRowBg: '#ecfdf5',
                        borderWidth: 1,
                        textColor: '#065f46'
                      },
                      {
                        value: 'sunset_orange',
                        label: 'Coucher',
                        gradient: 'linear-gradient(135deg, #fed7aa 0%, #fdba74 100%)',
                        headerBorder: '#f97316',
                        rowBorder: '#fff7ed',
                        altRowBg: '#fff7ed',
                        borderWidth: 1,
                        textColor: '#c2410c'
                      },
                      {
                        value: 'royal_purple',
                        label: 'Royal',
                        gradient: 'linear-gradient(135deg, #e9d5ff 0%, #d8b4fe 100%)',
                        headerBorder: '#a855f7',
                        rowBorder: '#faf5ff',
                        altRowBg: '#faf5ff',
                        borderWidth: 1,
                        textColor: '#7c3aed'
                      },
                      {
                        value: 'rose_pink',
                        label: 'Rose',
                        gradient: 'linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%)',
                        headerBorder: '#f472b6',
                        rowBorder: '#fdf2f8',
                        altRowBg: '#fdf2f8',
                        borderWidth: 1,
                        textColor: '#db2777'
                      },
                      {
                        value: 'teal_aqua',
                        label: 'Aigue',
                        gradient: 'linear-gradient(135deg, #ccfbf1 0%, #a7f3d0 100%)',
                        headerBorder: '#14b8a6',
                        rowBorder: '#f0fdfa',
                        altRowBg: '#f0fdfa',
                        borderWidth: 1,
                        textColor: '#0d9488'
                      }
                    ].map((style) => (
                      <button
                        key={style.value}
                        type="button"
                        className={`table-style-option-compact ${localProperties.tableStyle === style.value ? 'active' : ''}`}
                        onClick={() => handlePropertyChange(selectedElement.id, 'tableStyle', style.value)}
                        title={`${style.label} - Style ${style.label.toLowerCase()}`}
                      >
                        <div className="table-sample-compact">
                          <div
                            className="table-header-compact"
                            style={{
                              background: style.gradient || style.headerBg,
                              border: `1px solid ${style.headerBorder}`,
                              color: style.textColor
                            }}
                          >
                            P|Q|P
                          </div>
                          <div
                            className="table-row-compact"
                            style={{
                              backgroundColor: style.altRowBg,
                              border: `1px solid ${style.rowBorder}`,
                              borderTop: 'none',
                              color: style.textColor
                            }}
                          >
                            A1|2|15€
                          </div>
                        </div>
                        <span className="style-label-compact">{style.label}</span>
                      </button>
                    ))}
                  </div>
                </div>

                {/* Couleurs thème (primaire & secondaire) */}
                <div className="table-section">
                  <div className="section-title">🎨 Couleurs thème</div>
                  <div className="colors-compact">
                    <div className="color-row">
                      <span className="color-label">Primaire:</span>
                      <input
                        type="color"
                        value={localProperties.tablePrimaryColor || '#667eea'}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'tablePrimaryColor', e.target.value)}
                        title="Couleur primaire (en-têtes, bordures, totaux)"
                      />
                      <span className="color-hint" style={{ fontSize: '0.75em', color: '#666' }}>
                        En-têtes & bordures
                      </span>
                    </div>
                    <div className="color-row">
                      <span className="color-label">Secondaire:</span>
                      <input
                        type="color"
                        value={localProperties.tableSecondaryColor || '#f5f5f5'}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'tableSecondaryColor', e.target.value)}
                        title="Couleur secondaire (lignes paires, fonds)"
                      />
                      <span className="color-hint" style={{ fontSize: '0.75em', color: '#666' }}>
                        Fonds alternés
                      </span>
                    </div>
                  </div>
                </div>

                {/* Couleurs individuelles (compact) */}
                <div className="table-section">
                  <div className="section-title">🎨 Couleurs personnalisées</div>
                  <p style={{ fontSize: '0.85em', color: '#999', marginBottom: '12px' }}>
                    ⚠️ Surcharge les couleurs thème si définies
                  </p>
                  <div className="colors-compact">
                    <div className="color-row">
                      <span className="color-label">Pairs:</span>
                      <input
                        type="color"
                        value={localProperties.evenRowBg || '#ffffff'}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'evenRowBg', e.target.value)}
                        title="Fond lignes paires"
                      />
                      <input
                        type="color"
                        value={localProperties.evenRowTextColor || '#000000'}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'evenRowTextColor', e.target.value)}
                        title="Texte lignes paires"
                      />
                    </div>
                    <div className="color-row">
                      <span className="color-label">Impairs:</span>
                      <input
                        type="color"
                        value={localProperties.oddRowBg || '#f9fafb'}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'oddRowBg', e.target.value)}
                        title="Fond lignes impaires"
                      />
                      <input
                        type="color"
                        value={localProperties.oddRowTextColor || '#000000'}
                        onChange={(e) => handlePropertyChange(selectedElement.id, 'oddRowTextColor', e.target.value)}
                        title="Texte lignes impaires"
                      />
                    </div>
                  </div>
                </div>

                {/* Bouton de réinitialisation du tableau */}
                <div className="property-row">
                  <label></label>
                  <button
                    className="reset-table-btn"
                    onClick={() => {
                      // Réinitialiser les colonnes
                      handlePropertyChange(selectedElement.id, 'columns', {
                        image: false,
                        name: true,
                        sku: false,
                        quantity: true,
                        price: true,
                        total: true
                      });

                      // Réinitialiser le style
                      handlePropertyChange(selectedElement.id, 'tableStyle', 'default');

                      // Réinitialiser les totaux
                      handlePropertyChange(selectedElement.id, 'showSubtotal', false);
                      handlePropertyChange(selectedElement.id, 'showShipping', true);
                      handlePropertyChange(selectedElement.id, 'showTaxes', true);
                      handlePropertyChange(selectedElement.id, 'showDiscount', true);
                      handlePropertyChange(selectedElement.id, 'showTotal', true);

                      // Réinitialiser les bordures
                      handlePropertyChange(selectedElement.id, 'showBorders', true);

                      // Réinitialiser la bordure extérieure du tableau
                      handlePropertyChange(selectedElement.id, 'showTableBorder', false);

                      // Réinitialiser les couleurs individuelles
                      handlePropertyChange(selectedElement.id, 'evenRowBg', '#ffffff');
                      handlePropertyChange(selectedElement.id, 'evenRowTextColor', '#000000');
                      handlePropertyChange(selectedElement.id, 'oddRowBg', '#f9fafb');
                      handlePropertyChange(selectedElement.id, 'oddRowTextColor', '#000000');

                      // Réinitialiser les couleurs thème
                      handlePropertyChange(selectedElement.id, 'tablePrimaryColor', '#667eea');
                      handlePropertyChange(selectedElement.id, 'tableSecondaryColor', '#f5f5f5');
                    }}
                    title="Réinitialiser toutes les propriétés du tableau aux valeurs par défaut"
                    style={{
                      padding: '6px 12px',
                      backgroundColor: '#dc2626',
                      border: '1px solid #b91c1c',
                      borderRadius: '4px',
                      color: '#ffffff',
                      fontSize: '12px',
                      fontWeight: '500',
                      cursor: 'pointer',
                      marginTop: '8px',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '4px'
                    }}
                  >
                    🔄 Reset
                  </button>
                </div>
              </Accordion>
            )}

            {/* Contrôles informations client (uniquement pour les éléments customer_info) */}
            {allowedControls.includes('customer_fields') && selectedElement.type === 'customer_info' && (
              <Accordion
                key="customer-info"
                title="Informations client"
                icon="👤"
                defaultOpen={false}
                className="properties-accordion"
              >

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
                            const allFields = ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'];
                            const currentFields = localProperties.fields || allFields;
                            const newFields = e.target.checked
                              ? allFields.filter(field => field === key || currentFields.includes(field))
                              : allFields.filter(field => field !== key && currentFields.includes(field));
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

                {localProperties.showLabels && (
                  <div className="property-row">
                    <label>Alignement des étiquettes:</label>
                    <select
                      value={localProperties.labelAlign || 'left'}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'labelAlign', e.target.value)}
                    >
                      <option value="left">Gauche</option>
                      <option value="center">Centre</option>
                      <option value="right">Droite</option>
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
              </Accordion>
            )}

            {/* Contrôles mentions légales (uniquement pour les éléments mentions) */}
            {allowedControls.includes('mentions') && selectedElement.type === 'mentions' && (
              <Accordion
                key="legal-mentions"
                title="Mentions légales"
                icon="📄"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}

            {/* Contrôles texte dynamique (uniquement pour les éléments dynamic-text) */}
            {allowedControls.includes('dynamic_text') && selectedElement.type === 'dynamic-text' && (
              <Accordion
                key="dynamic-text"
                title="Texte Dynamique"
                icon="📝"
                defaultOpen={false}
                className="properties-accordion"
              >

                <div className="property-row">
                  <label>Modèle:</label>
                  <select
                    value={localProperties.template || 'total_only'}
                    onChange={(e) => {
                      const newTemplate = e.target.value;
                      const oldTemplate = localProperties.template;
                      
                      handlePropertyChange(selectedElement.id, 'template', newTemplate);
                      
                      // Appliquer les presets en batch pour éviter les conflits de synchronisation
                      if (newTemplate !== oldTemplate) {
                        const preset = TEMPLATE_PRESETS[newTemplate];
                        if (preset && onBatchUpdate) {
                          // Créer un objet avec toutes les propriétés du preset
                          const presetUpdates = {};
                          
                          Object.entries(preset).forEach(([property, defaultValue]) => {
                            // Appliquer seulement si la propriété n'est pas déjà personnalisée
                            // ou si elle a la valeur par défaut du template précédent
                            const currentValue = localProperties[property];
                            const oldPreset = oldTemplate ? TEMPLATE_PRESETS[oldTemplate] : null;
                            const oldDefaultValue = oldPreset ? oldPreset[property] : null;
                            
                            // Appliquer le preset si :
                            // 1. La propriété n'est pas définie, ou
                            // 2. Elle a la valeur par défaut du template précédent
                            if (currentValue === undefined || currentValue === oldDefaultValue) {
                              presetUpdates[property] = defaultValue;
                              console.log('[DEBUG] Preset property will be applied:', property, '=', defaultValue);
                            } else {
                              console.log('[DEBUG] Preset property skipped (already customized):', property, 'current:', currentValue, 'old default:', oldDefaultValue);
                            }
                          });
                          
                          // Appliquer toutes les propriétés du preset en une seule opération
                          if (Object.keys(presetUpdates).length > 0) {
                            console.log('[DEBUG] Applying preset batch update:', presetUpdates);
                            onBatchUpdate([{
                              elementId: selectedElement.id,
                              properties: presetUpdates
                            }]);
                          } else {
                            console.log('[DEBUG] No preset properties to apply');
                          }
                        } else {
                          console.log('[DEBUG] No preset or onBatchUpdate not available');
                        }
                      }
                    }}
                  >
                    <option value="total_only">💰 Total uniquement</option>
                    <option value="order_info">📋 Informations commande</option>
                    <option value="customer_info">👤 Informations client</option>
                    <option value="customer_address">🏠 Adresse client complète</option>
                    <option value="full_header">📄 En-tête complet</option>
                    <option value="invoice_header">📋 En-tête facture détaillé</option>
                    <option value="order_summary">🧾 Récapitulatif commande</option>
                    <option value="payment_info">💳 Informations paiement</option>
                    <option value="payment_terms">📅 Conditions de paiement</option>
                    <option value="shipping_info">🚚 Adresse de livraison</option>
                    <option value="thank_you">🙏 Message de remerciement</option>
                    <option value="legal_notice">⚖️ Mentions légales</option>
                    <option value="bank_details">🏦 Coordonnées bancaires</option>
                    <option value="contact_info">📞 Informations de contact</option>
                    <option value="order_confirmation">✅ Confirmation de commande</option>
                    <option value="delivery_note">📦 Bon de livraison</option>
                    <option value="warranty_info">🛡️ Garantie produit</option>
                    <option value="return_policy">↩️ Politique de retour</option>
                    <option value="signature_line">✍️ Ligne de signature</option>
                    <option value="invoice_footer">📄 Pied de facture</option>
                    <option value="terms_conditions">📋 CGV</option>
                    <option value="quality_guarantee">⭐ Garantie qualité</option>
                    <option value="eco_friendly">🌱 Engagement écologique</option>
                    <option value="follow_up">📊 Suivi commande</option>
                    <option value="custom">🎨 Personnalisé</option>
                  </select>
                </div>

                {/* Bouton pour revenir aux valeurs par défaut du template */}
                {localProperties.template && localProperties.template !== 'custom' && (
                  <div className="property-row">
                    <label></label>
                    <button
                      className="reset-template-btn"
                      onClick={() => {
                        const preset = TEMPLATE_PRESETS[localProperties.template];
                        if (preset) {
                          Object.entries(preset).forEach(([property, value]) => {
                            handlePropertyChange(selectedElement.id, property, value);
                          });
                        }
                      }}
                      title="Réinitialiser aux valeurs par défaut du template"
                      style={{
                        padding: '6px 12px',
                        backgroundColor: '#f3f4f6',
                        border: '1px solid #d1d5db',
                        borderRadius: '4px',
                        color: '#374151',
                        fontSize: '12px',
                        cursor: 'pointer',
                        marginTop: '4px'
                      }}
                    >
                      🔄 Valeurs par défaut
                    </button>
                  </div>
                )}

                {localProperties.template === 'custom' && (
                  <div className="property-row">
                    <label>Contenu personnalisé:</label>
                    <textarea
                      value={localProperties.customContent || ''}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'customContent', e.target.value)}
                      placeholder="Utilisez des variables comme {{order_total}}, {{customer_name}}, etc."
                      rows={4}
                      style={{ width: '100%', resize: 'vertical', minHeight: '80px' }}
                    />
                  </div>
                )}

                <div className="property-row" style={{ marginTop: '12px', padding: '8px', backgroundColor: '#f8fafc', borderRadius: '4px' }}>
                  <label style={{ fontWeight: 'bold', marginBottom: '8px', display: 'block' }}>Variables disponibles:</label>
                  <div className="variables-badges">
                    <div className="variable-group">
                      <span className="group-icon">💰</span>
                      <span className="group-label">Commande:</span>
                      <span className="variable-badges">
                        <span className="variable-badge" title="Montant total de la commande">{'{{order_total}}'}</span>
                        <span className="variable-badge" title="Numéro de commande">{'{{order_number}}'}</span>
                        <span className="variable-badge" title="Date de la commande">{'{{order_date}}'}</span>
                        <span className="variable-badge" title="Sous-total HT">{'{{order_subtotal}}'}</span>
                        <span className="variable-badge" title="Montant TVA">{'{{order_tax}}'}</span>
                        <span className="variable-badge" title="Frais de port">{'{{order_shipping}}'}</span>
                      </span>
                    </div>
                    <div className="variable-group">
                      <span className="group-icon">👤</span>
                      <span className="group-label">Client:</span>
                      <span className="variable-badges">
                        <span className="variable-badge" title="Nom du client">{'{{customer_name}}'}</span>
                        <span className="variable-badge" title="Email du client">{'{{customer_email}}'}</span>
                        <span className="variable-badge" title="Adresse de facturation">{'{{billing_address}}'}</span>
                        <span className="variable-badge" title="Adresse de livraison">{'{{shipping_address}}'}</span>
                      </span>
                    </div>
                    <div className="variable-group">
                      <span className="group-icon">📅</span>
                      <span className="group-label">Dates:</span>
                      <span className="variable-badges">
                        <span className="variable-badge" title="Date actuelle">{'{{date}}'}</span>
                        <span className="variable-badge" title="Date d'échéance">{'{{due_date}}'}</span>
                      </span>
                    </div>
                  </div>
                </div>
              </Accordion>
            )}

            {/* Contrôles informations entreprise (uniquement pour les éléments company_info) */}
            {allowedControls.includes('company_fields') && selectedElement.type === 'company_info' && (
              <Accordion
                key="company-info"
                title="Informations Entreprise"
                icon="🏢"
                defaultOpen={false}
                className="properties-accordion"
              >

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
                            const allFields = ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
                            const currentFields = localProperties.fields || allFields;
                            const newFields = e.target.checked
                              ? allFields.filter(field => field === key || currentFields.includes(field))
                              : allFields.filter(field => field !== key && currentFields.includes(field));
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

                {localProperties.showLabels && (
                  <div className="property-row">
                    <label>Alignement des étiquettes:</label>
                    <select
                      value={localProperties.labelAlign || 'left'}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'labelAlign', e.target.value)}
                    >
                      <option value="left">Gauche</option>
                      <option value="center">Centre</option>
                      <option value="right">Droite</option>
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
              </Accordion>
            )}

            {/* Contrôles type de document (uniquement pour les éléments document_type) */}
            {allowedControls.includes('document_type') && selectedElement.type === 'document_type' && (
              <Accordion
                key="document-type"
                title="Type de Document"
                icon="📋"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}

            {/* Contrôles numéro de commande (uniquement pour les éléments order_number) */}
            {allowedControls.includes('order_number') && selectedElement.type === 'order_number' && (
              <Accordion
                key="order-number"
                title="Numéro de Commande"
                icon="🔢"
                defaultOpen={false}
                className="properties-accordion"
              >

                <div className="property-row">
                  <label>Format d'affichage:</label>
                  <input
                    type="text"
                    value={localProperties.format || 'Commande #{order_number}'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'format', e.target.value)}
                    placeholder="Commande #{order_number}"
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

                {localProperties.showLabel && (
                  <div className="property-row">
                    <label>Alignement de l'étiquette:</label>
                    <select
                      value={localProperties.labelAlign || 'left'}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'labelAlign', e.target.value)}
                    >
                      <option value="left">Gauche</option>
                      <option value="center">Centre</option>
                      <option value="right">Droite</option>
                    </select>
                  </div>
                )}
              </Accordion>
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
              <Accordion
                key="image-controls"
                title="Image"
                icon="🖼️"
                defaultOpen={false}
                className="properties-accordion"
              >

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
            </Accordion>
            )}
            {/* Contrôles pour le type de document */}
            {selectedElement.type === 'document_type' && (
              <Accordion
                key="document-type-alt"
                title="Type de Document"
                icon="📋"
                defaultOpen={false}
                className="properties-accordion"
              >

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
            </Accordion>
)}

            {/* Contrôles de contenu disponibles pour tous les éléments sauf les tableaux de produits */}
            {selectedElement.type !== 'product_table' && (
              <Accordion
                key="content-controls"
                title="Contenu"
                icon="📝"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}

            {/* Contrôles de champs disponibles pour tous les éléments sauf les tableaux de produits */}
            {selectedElement.type !== 'product_table' && (
              <Accordion
                key="fields-options"
                title="Champs & Options"
                icon="📋"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}
          </div>
        );

      case 'effects':
        return (
          <div className="tab-content">
            {/* Transparence & Visibilité (toujours disponible si autorisé) */}
            {allowedControls.includes('opacity') && (
              <Accordion
                key="opacity"
                title="Transparence & Visibilité"
                icon="🌟"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
            )}

            {/* Ombres & Effets (uniquement si autorisé) */}
            {allowedControls.includes('shadows') && (
              <Accordion
                key="shadows"
                title="Ombres & Effets"
                icon="✨"
                defaultOpen={false}
                className="properties-accordion"
              >

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

                    <div className="property-row">
                      <label>Flou:</label>
                      <div className="slider-container">
                        <input
                          type="range"
                          min="0"
                          max="20"
                          value={localProperties.shadowBlur || 5}
                          onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowBlur', safeParseInt(e.target.value, 5))}
                          className="slider"
                        />
                        <span className="slider-value">{localProperties.shadowBlur || 5}px</span>
                      </div>
                    </div>
                  </>
                )}
              </Accordion>
            )}

            {/* Filtres visuels (uniquement si autorisé) */}
            {allowedControls.includes('filters') && (
              <Accordion
                key="filters"
                title="Filtres visuels"
                icon="🎭"
                defaultOpen={false}
                className="properties-accordion"
              >

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
              </Accordion>
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
