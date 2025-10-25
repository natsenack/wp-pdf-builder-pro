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
import renderLayoutSection from './PropertiesPanel/sections/LayoutSection';
import renderContentSection from './PropertiesPanel/sections/ContentSection';
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
                default:
                  return null;
              }
            })}
          </div>
        );

      case 'layout':
        return (
          <div className="tab-content">
            {renderLayoutSection(selectedElement, localProperties, handlePropertyChange, activeTab)}
          </div>
        );


      case 'content':
        return (
          <div className="tab-content">
            {renderContentSection(selectedElement, localProperties, handlePropertyChange, activeTab)}
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
