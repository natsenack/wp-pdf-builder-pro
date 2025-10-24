import React from 'react';
import Accordion from '../Accordion';
import ColorPicker from '../ColorPicker';
import { safeParseInt } from '../utils/helpers';

const renderBordersSection = (selectedElement, localProperties, handlePropertyChange, isBorderEnabled, setIsBorderEnabled, setPreviousBorderWidth, setPreviousBorderColor, previousBorderWidth, previousBorderColor, activeTab) => {
  // Les bordures sont disponibles pour tous les éléments
  if (!isBorderEnabled && localProperties.borderWidth <= 0) return null;

  return (
    <Accordion
      key="borders"
      title="Bordures & Coins Arrondis"
      icon="🔲"
      defaultOpen={false}
      className="properties-accordion"
    >

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
    </Accordion>
  );
};

export default renderBordersSection;