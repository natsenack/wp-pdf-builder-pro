import React from 'react';
import Accordion from '../Accordion';
import ColorPicker from '../ColorPicker';
import { safeParseInt } from '../utils/helpers';

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
    </Accordion>
  );
};

export default renderEffectsSection;