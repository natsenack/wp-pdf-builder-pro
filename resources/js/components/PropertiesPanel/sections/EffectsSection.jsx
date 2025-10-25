import React from 'react';
import Accordion from '../Accordion';
import ColorPicker from '../ColorPicker';
import { safeParseInt, safeParseFloat } from '../utils/helpers';

const renderEffectsSection = (selectedElement, localProperties, handlePropertyChange, activeTab) => {
  // Les effets sont disponibles pour tous les éléments
  return (
    <Accordion
      key="effects"
      title="Effets"
      icon="✨"
      defaultOpen={false}
      className="properties-accordion"
    >
      {/* Opacité */}
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

      {/* Ombres */}
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
                value={localProperties.shadowOffsetY ?? 2}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetY', safeParseInt(e.target.value, 2))}
                className="slider"
              />
              <span className="slider-value">{localProperties.shadowOffsetY ?? 2}px</span>
            </div>
          </div>

          <div className="property-row">
            <label>Flou ombre:</label>
            <div className="slider-container">
              <input
                type="range"
                min="0"
                max="20"
                value={localProperties.shadowBlur ?? 0}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowBlur', safeParseInt(e.target.value, 0))}
                className="slider"
              />
              <span className="slider-value">{localProperties.shadowBlur ?? 0}px</span>
            </div>
          </div>
        </>
      )}

      {/* Filtres disponibles pour certains éléments */}
      {(selectedElement.type === 'TEXT' || selectedElement.type === 'DYNAMIC-TEXT' || selectedElement.type === 'PRODUCT_TABLE') && (
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
};

export default renderEffectsSection;