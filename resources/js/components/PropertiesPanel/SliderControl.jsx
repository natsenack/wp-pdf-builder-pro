import React from 'react';
import { safeParseFloat, safeParseInt } from './utils/helpers';

/**
 * Composant SliderControl réutilisable pour les contrôles de slider
 */
const SliderControl = ({
  label,
  value,
  min,
  max,
  step = 1,
  unit = '',
  onChange,
  className = '',
  parser = safeParseFloat
}) => {
  const handleChange = (e) => {
    const newValue = parser(e.target.value);
    onChange(newValue);
  };

  return (
    <div className={`property-row ${className}`}>
      <label>{label}:</label>
      <div className="slider-container">
        <input
          type="range"
          min={min}
          max={max}
          step={step}
          value={value}
          onChange={handleChange}
          className="slider"
        />
        <span className="slider-value">{value}{unit}</span>
      </div>
    </div>
  );
};

export default SliderControl;