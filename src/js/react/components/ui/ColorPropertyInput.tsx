import React from 'react';

interface ColorPropertyInputProps {
  label: string;
  value: string;
  defaultValue?: string;
  onChange: (value: string) => void;
  disabled?: boolean;
}

export function ColorPropertyInput({
  label,
  value,
  defaultValue = '#000000',
  onChange,
  disabled = false
}: ColorPropertyInputProps) {
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    onChange(e.target.value);
  };

  return (
    <div style={{ marginBottom: '8px' }}>
      <label style={{
        display: 'block',
        fontSize: '11px',
        fontWeight: 'bold',
        marginBottom: '4px',
        color: '#333'
      }}>
        {label}
      </label>
      <input
        type="color"
        value={value || defaultValue}
        onChange={handleChange}
        disabled={disabled}
        style={{
          width: '100%',
          height: '32px',
          border: '1px solid #ccc',
          borderRadius: '3px',
          cursor: disabled ? 'not-allowed' : 'pointer',
          opacity: disabled ? 0.6 : 1
        }}
      />
    </div>
  );
}