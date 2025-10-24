import React from 'react';
import Accordion from '../Accordion';
import ColorPicker from '../ColorPicker';
import { shouldShowSection, safeParseFloat, safeParseInt } from '../utils/helpers';

const renderColorsSection = (selectedElement, localProperties, handlePropertyChange, activeTab) => {
  // Vérifier si la section colors doit être affichée pour ce type d'élément
  if (!shouldShowSection('colors', selectedElement.type)) return null;

  // Calculer si le fond est activé en fonction de la valeur backgroundColor
  const isBackgroundEnabled = localProperties.backgroundColor !== 'transparent';

  return (
    <Accordion
      key="colors"
      title="Couleurs & Apparence"
      icon="🎨"
      defaultOpen={false}
      className="properties-accordion"
    >
      {/* Couleur du texte - toujours disponible sauf pour les éléments qui n'ont pas de texte */}
      {selectedElement.type !== 'logo' && selectedElement.type !== 'company_logo' && (
        <ColorPicker
          label="Texte"
          value={localProperties.color}
          onChange={(value) => {
            handlePropertyChange(selectedElement.id, 'color', value);
          }}
          presets={['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#000000']}
          defaultColor="#333333"
        />
      )}

      {/* Contrôle du fond - toujours disponible */}
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
    </Accordion>
  );
};

export default renderColorsSection;