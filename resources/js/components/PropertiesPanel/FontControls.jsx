import React from 'react';
import { safeParseFloat, safeParseInt } from './utils/helpers';

const FontControls = ({ elementId, properties, onPropertyChange }) => (
  <div className="properties-group">
    <h4>🎨 Police & Style</h4>

    <div className="property-row">
      <label>Famille:</label>
      <select
        value={properties.fontFamily || 'Inter'}
        onChange={(e) => onPropertyChange(elementId, 'fontFamily', e.target.value)}
      >
        <option value="Inter">Inter</option>
        <option value="Arial">Arial</option>
        <option value="Helvetica">Helvetica</option>
        <option value="Times New Roman">Times New Roman</option>
        <option value="Courier New">Courier New</option>
        <option value="Georgia">Georgia</option>
        <option value="Verdana">Verdana</option>
        <option value="Roboto">Roboto</option>
        <option value="Open Sans">Open Sans</option>
      </select>
    </div>

    <div className="property-row">
      <label>Taille:</label>
      <div className="slider-container">
        <input
          type="range"
          min="8"
          max="72"
          value={properties.fontSize ?? 14}
          onChange={(e) => onPropertyChange(elementId, 'fontSize', safeParseInt(e.target.value, 14))}
          className="slider"
        />
        <span className="slider-value">{properties.fontSize ?? 14}px</span>
      </div>
    </div>

    <div className="property-row">
      <label>Interligne:</label>
      <div className="slider-container">
        <input
          type="range"
          min="0.8"
          max="3"
          step="0.1"
          value={properties.lineHeight ?? 1.2}
          onChange={(e) => onPropertyChange(elementId, 'lineHeight', safeParseFloat(e.target.value, 1.2))}
          className="slider"
        />
        <span className="slider-value">{properties.lineHeight ?? 1.2}</span>
      </div>
    </div>

    <div className="property-row">
      <label>Espacement lettres:</label>
      <div className="slider-container">
        <input
          type="range"
          min="-2"
          max="10"
          step="0.1"
          value={properties.letterSpacing ?? 0}
          onChange={(e) => onPropertyChange(elementId, 'letterSpacing', safeParseFloat(e.target.value, 0))}
          className="slider"
        />
        <span className="slider-value">{properties.letterSpacing ?? 0}px</span>
      </div>
    </div>

    <div className="property-row">
      <label>Opacité texte:</label>
      <div className="slider-container">
        <input
          type="range"
          min="0"
          max="1"
          step="0.1"
          value={properties.opacity ?? 1}
          onChange={(e) => onPropertyChange(elementId, 'opacity', safeParseFloat(e.target.value, 1))}
          className="slider"
        />
        <span className="slider-value">{Math.round((properties.opacity ?? 1) * 100)}%</span>
      </div>
    </div>

    <div className="property-row">
      <label>Ombre texte:</label>
      <div className="slider-container">
        <input
          type="range"
          min="0"
          max="5"
          step="0.1"
          value={properties.textShadowBlur ?? 0}
          onChange={(e) => onPropertyChange(elementId, 'textShadowBlur', safeParseFloat(e.target.value, 0))}
          className="slider"
        />
        <span className="slider-value">{properties.textShadowBlur ?? 0}px</span>
      </div>
    </div>

    <div className="property-row">
      <label>Style du texte:</label>
      <div className="style-buttons-grid">
        <button
          className={`style-btn ${properties.fontWeight === 'bold' ? 'active' : ''}`}
          onClick={() => onPropertyChange(elementId, 'fontWeight', properties.fontWeight === 'bold' ? 'normal' : 'bold')}
          title="Gras"
        >
          <strong>B</strong>
        </button>
        <button
          className={`style-btn ${properties.fontStyle === 'italic' ? 'active' : ''}`}
          onClick={() => onPropertyChange(elementId, 'fontStyle', properties.fontStyle === 'italic' ? 'normal' : 'italic')}
          title="Italique"
        >
          <em>I</em>
        </button>
        <button
          className={`style-btn ${(properties.textDecoration || '').includes('underline') ? 'active' : ''}`}
          onClick={() => {
            const currentDecorations = properties.textDecoration ? properties.textDecoration.split(' ') : [];
            const hasUnderline = currentDecorations.includes('underline');
            const newDecorations = hasUnderline
              ? currentDecorations.filter(d => d !== 'underline')
              : [...currentDecorations, 'underline'];
            onPropertyChange(elementId, 'textDecoration', newDecorations.join(' ') || 'none');
          }}
          title="Souligné"
        >
          <u>U</u>
        </button>
        <button
          className={`style-btn ${(properties.textDecoration || '').includes('line-through') ? 'active' : ''}`}
          onClick={() => {
            const currentDecorations = properties.textDecoration ? properties.textDecoration.split(' ') : [];
            const hasLineThrough = currentDecorations.includes('line-through');
            const newDecorations = hasLineThrough
              ? currentDecorations.filter(d => d !== 'line-through')
              : [...currentDecorations, 'line-through'];
            onPropertyChange(elementId, 'textDecoration', newDecorations.join(' ') || 'none');
          }}
          title="Barré"
        >
          <s>S</s>
        </button>
      </div>
    </div>

    <div className="property-row">
      <label>Alignement:</label>
      <div className="alignment-buttons">
        {[
          { value: 'left', icon: '⬅️', label: 'Gauche' },
          { value: 'center', icon: '⬌', label: 'Centre' },
          { value: 'right', icon: '➡️', label: 'Droite' },
          { value: 'justify', icon: '⬌⬅️', label: 'Justifié' }
        ].map(({ value, icon, label }) => (
          <button
            key={value}
            className={`align-btn ${properties.textAlign === value ? 'active' : ''}`}
            onClick={() => onPropertyChange(elementId, 'textAlign', value)}
            title={label}
          >
            {icon}
          </button>
        ))}
      </div>
    </div>
  </div>
);

export default FontControls;