import React from 'react';
import Accordion from '../Accordion';
import ColorPicker from '../ColorPicker';
import { safeParseInt } from '../utils/helpers';

const renderBordersSection = (selectedElement, localProperties, handlePropertyChange, activeTab) => {
  // Les bordures sont disponibles pour tous les éléments
  return (
    <Accordion
      key="borders"
      title="Bordures & Coins Arrondis"
      icon="🔲"
      defaultOpen={false}
      className="properties-accordion"
    >
      <ColorPicker
        label="Couleur bordure"
        value={localProperties.borderColor || '#000000'}
        onChange={(value) => handlePropertyChange(selectedElement.id, 'borderColor', value)}
        presets={['#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155', '#000000']}
      />

      <div className="property-row">
        <label>Épaisseur bordure:</label>
        <div className="slider-container">
          <input
            type="range"
            min="0"
            max="10"
            value={localProperties.borderWidth ?? 0}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', safeParseInt(e.target.value, 0))}
            className="slider"
          />
          <span className="slider-value">{localProperties.borderWidth ?? 0}px</span>
        </div>
      </div>

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
    </Accordion>
  );
};

export default renderBordersSection;