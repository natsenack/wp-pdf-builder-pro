import React from 'react';

// Contrôles pour les informations client
const CustomerControls = ({ elementId, properties, onPropertyChange }) => {
  return (
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
            { key: 'vat', label: 'N° TVA' }
          ].map(({ key, label }) => (
            <label key={key} className="checkbox-item">
              <input
                type="checkbox"
                checked={properties.fields?.includes(key) ?? true}
                onChange={(e) => {
                  const currentFields = properties.fields || ['name', 'email', 'phone', 'address', 'company', 'vat'];
                  const newFields = e.target.checked
                    ? [...currentFields, key]
                    : currentFields.filter(f => f !== key);
                  onPropertyChange(elementId, 'fields', newFields);
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
          value={properties.layout || 'vertical'}
          onChange={(e) => onPropertyChange(elementId, 'layout', e.target.value)}
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
            checked={properties.showLabels ?? true}
            onChange={(e) => onPropertyChange(elementId, 'showLabels', e.target.checked)}
          />
          <span className="toggle-slider"></span>
        </label>
      </div>

      {properties.showLabels && (
        <div className="property-row">
          <label>Style des étiquettes:</label>
          <select
            value={properties.labelStyle || 'normal'}
            onChange={(e) => onPropertyChange(elementId, 'labelStyle', e.target.value)}
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
            value={properties.spacing || 8}
            onChange={(e) => onPropertyChange(elementId, 'spacing', parseInt(e.target.value, 10))}
            className="slider"
          />
          <span className="slider-value">{properties.spacing || 8}px</span>
        </div>
      </div>
    </div>
  );
};

export default CustomerControls;