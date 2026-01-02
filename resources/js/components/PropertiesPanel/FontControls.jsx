import React from 'react';
import { safeParseFloat, safeParseInt } from './utils/helpers';
import { AdaptiveControl } from './utils/AdaptiveLayout';
import './utils/AdaptiveLayout.css';

const FontControls = ({ elementId, properties, onPropertyChange }) => (
  <div className="properties-group">
    <h4>🎨 Police & Style</h4>

    <AdaptiveControl
      label="Famille de police:"
      minWidth={320}
      className="adaptive-compact"
    >
      <select
        value={properties.fontFamily || 'Inter'}
        onChange={(e) => onPropertyChange(elementId, 'fontFamily', e.target.value)}
        className="styled-select"
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
    </AdaptiveControl>

    <AdaptiveControl
      label="Taille de police:"
      minWidth={300}
      className="adaptive-compact"
    >
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
    </AdaptiveControl>

    <AdaptiveControl
      label="Interligne:"
      minWidth={300}
      className="adaptive-compact"
    >
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
    </AdaptiveControl>

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
      <label>Ombre du texte:</label>
      <div className="shadow-controls">
        <div className="shadow-color">
          <input
            type="color"
            value={properties.textShadowColor || '#000000'}
            onChange={(e) => onPropertyChange(elementId, 'textShadowColor', e.target.value)}
            title="Couleur de l'ombre"
          />
        </div>
        <div className="shadow-offsets">
          <div className="slider-group">
            <label>X:</label>
            <input
              type="range"
              min="-20"
              max="20"
              step="1"
              value={properties.textShadowOffsetX ?? 0}
              onChange={(e) => onPropertyChange(elementId, 'textShadowOffsetX', safeParseFloat(e.target.value, 0))}
            />
            <span className="slider-value">{properties.textShadowOffsetX ?? 0}px</span>
          </div>
          <div className="slider-group">
            <label>Y:</label>
            <input
              type="range"
              min="-20"
              max="20"
              step="1"
              value={properties.textShadowOffsetY ?? 0}
              onChange={(e) => onPropertyChange(elementId, 'textShadowOffsetY', safeParseFloat(e.target.value, 0))}
            />
            <span className="slider-value">{properties.textShadowOffsetY ?? 0}px</span>
          </div>
        </div>
        <div className="slider-group">
          <label>Flou:</label>
          <input
            type="range"
            min="0"
            max="20"
            step="1"
            value={properties.textShadowBlur ?? 0}
            onChange={(e) => onPropertyChange(elementId, 'textShadowBlur', safeParseFloat(e.target.value, 0))}
          />
          <span className="slider-value">{properties.textShadowBlur ?? 0}px</span>
        </div>
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

    <div className="property-row">
      <label>Transformation:</label>
      <div className="transform-buttons">
        {[
          { value: 'none', label: 'Aucune' },
          { value: 'uppercase', label: 'MAJUSCULES' },
          { value: 'lowercase', label: 'minuscules' },
          { value: 'capitalize', label: 'Première Lettre' }
        ].map(({ value, label }) => (
          <button
            key={value}
            className={`transform-btn ${properties.textTransform === value ? 'active' : ''}`}
            onClick={() => onPropertyChange(elementId, 'textTransform', value)}
            title={label}
          >
            {label}
          </button>
        ))}
      </div>
    </div>
  </div>
);

export default FontControls;