import React from 'react';

interface NumericPropertyInputProps {
  label: string;
  value: number | undefined;
  defaultValue: number;
  min?: number;
  max?: number;
  step?: number;
  unit?: string;
  onChange: (value: number) => void;
  description?: string;
  showValue?: boolean;
}

export function NumericPropertyInput({
  label,
  value,
  defaultValue,
  min = 1,
  max,
  step = 1,
  unit = 'px',
  onChange,
  description,
  showValue = true
}: NumericPropertyInputProps) {
  const currentValue = value ?? defaultValue;

  return (
    <div style={{ marginBottom: '12px' }}>
      <label style={{
        display: 'block',
        fontSize: '12px',
        fontWeight: 'bold',
        marginBottom: '4px'
      }}>
        {label}
        {showValue && (
          <span style={{ color: '#666', fontSize: '10px', fontWeight: 'normal' }}>
            {' '}({currentValue}{unit})
          </span>
        )}
      </label>
      <input
        type="number"
        min={min}
        max={max}
        step={step}
        value={currentValue}
        onChange={(e) => {
          const newValue = parseFloat(e.target.value) || defaultValue;
          onChange(newValue);
        }}
        style={{
          width: '100%',
          padding: '4px 8px',
          border: '1px solid #ccc',
          borderRadius: '3px',
          fontSize: '12px'
        }}
        placeholder={`Valeur en ${unit}`}
      />
      {description && (
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>
          {description}
        </small>
      )}
    </div>
  );
}