import React from 'react';
import Accordion from '../Accordion';
import ColorPicker from '../ColorPicker';
import { safeParseInt, safeParseFloat } from '../utils/helpers';
import { ELEMENT_PROPERTY_PROFILES } from '../utils/constants';

const renderEffectsSection = (selectedElement, localProperties, handlePropertyChange, activeTab) => {
  // Récupérer le profil de l'élément pour connaître les effets disponibles
  const elementProfile = ELEMENT_PROPERTY_PROFILES[selectedElement.type] || ELEMENT_PROPERTY_PROFILES['default'];
  const effectsProfile = elementProfile.effects || {};
  const availableEffects = effectsProfile.properties || {};

  // Vérifier si un effet est disponible
  const hasEffect = (effectName) => {
    return Object.values(availableEffects).some(effectList =>
      Array.isArray(effectList) && effectList.includes(effectName)
    );
  };

  return (
    <Accordion
      key="effects"
      title="Effets"
      icon="✨"
      defaultOpen={false}
      className="properties-accordion"
    >
      {/* Opacité - disponible pour tous */}
      {hasEffect('opacity') && (
        <div className="property-row">
          <label>Opacité:</label>
          <div className="slider-container">
            <input
              type="range"
              min="0"
              max="1"
              step="0.1"
              value={localProperties.opacity ?? 1}
              onChange={(e) => handlePropertyChange(selectedElement.id, 'opacity', safeParseFloat(e.target.value, 1))}
              className="slider"
            />
            <span className="slider-value">{Math.round((localProperties.opacity ?? 1) * 100)}%</span>
          </div>
        </div>
      )}

      {/* Ombres */}
      {hasEffect('shadow') && (
        <>
          <div className="property-row">
            <label>Ombre:</label>
            <input
              type="checkbox"
              checked={localProperties.shadow || false}
              onChange={(e) => handlePropertyChange(selectedElement.id, 'shadow', e.target.checked)}
            />
          </div>

          {localProperties.shadow && (
            <>
              <ColorPicker
                label="Couleur ombre"
                value={localProperties.shadowColor || '#000000'}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'shadowColor', value)}
                presets={['#000000', '#ffffff', '#64748b', '#ef4444', '#3b82f6']}
              />

              <div className="property-row">
                <label>Décalage X:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="-20"
                    max="20"
                    step="1"
                    value={localProperties.shadowOffsetX ?? 2}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetX', safeParseInt(e.target.value, 2))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.shadowOffsetX ?? 2}px</span>
                </div>
              </div>

              <div className="property-row">
                <label>Décalage Y:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="-20"
                    max="20"
                    step="1"
                    value={localProperties.shadowOffsetY ?? 2}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetY', safeParseInt(e.target.value, 2))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.shadowOffsetY ?? 2}px</span>
                </div>
              </div>

              {hasEffect('shadowBlur') && (
                <div className="property-row">
                  <label>Flou ombre:</label>
                  <div className="slider-container">
                    <input
                      type="range"
                      min="0"
                      max="20"
                      step="1"
                      value={localProperties.shadowBlur ?? 0}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowBlur', safeParseInt(e.target.value, 0))}
                      className="slider"
                    />
                    <span className="slider-value">{localProperties.shadowBlur ?? 0}px</span>
                  </div>
                </div>
              )}
            </>
          )}
        </>
      )}

      {/* Filtres d'image - seulement pour les éléments qui les supportent */}
      {hasEffect('brightness') && (
        <>
          <div className="property-row">
            <label>Luminosité:</label>
            <div className="slider-container">
              <input
                type="range"
                min="0"
                max="2"
                step="0.1"
                value={localProperties.brightness ?? 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'brightness', safeParseFloat(e.target.value, 1))}
                className="slider"
              />
              <span className="slider-value">{localProperties.brightness ?? 1}</span>
            </div>
          </div>

          <div className="property-row">
            <label>Contraste:</label>
            <div className="slider-container">
              <input
                type="range"
                min="0"
                max="2"
                step="0.1"
                value={localProperties.contrast ?? 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'contrast', safeParseFloat(e.target.value, 1))}
                className="slider"
              />
              <span className="slider-value">{localProperties.contrast ?? 1}</span>
            </div>
          </div>

          <div className="property-row">
            <label>Saturation:</label>
            <div className="slider-container">
              <input
                type="range"
                min="0"
                max="2"
                step="0.1"
                value={localProperties.saturate ?? 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'saturate', safeParseFloat(e.target.value, 1))}
                className="slider"
              />
              <span className="slider-value">{localProperties.saturate ?? 1}</span>
            </div>
          </div>
        </>
      )}
    </Accordion>
  );
};export default renderEffectsSection;