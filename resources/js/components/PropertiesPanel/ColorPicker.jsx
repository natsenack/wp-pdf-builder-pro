import React from 'react';

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

export default ColorPicker;