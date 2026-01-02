import React from 'react';
import Accordion from '../Accordion';
import { shouldShowSection, safeParseFloat, safeParseInt } from '../utils/helpers';

const renderTypographySection = (selectedElement, localProperties, handlePropertyChange, activeTab) => {
  // Vérifier si la section typography doit être affichée pour ce type d'élément
  if (!shouldShowSection('typography', selectedElement.type)) return null;

  return (
    <Accordion
      key="typography"
      title="Typographie"
      icon="📝"
      defaultOpen={false}
      className="properties-accordion"
    >

      {/* Famille de police */}
      <div className="property-row">
        <label>Police:</label>
        <select
          value={localProperties.fontFamily || 'Arial'}
          onChange={(e) => handlePropertyChange(selectedElement.id, 'fontFamily', e.target.value)}
          className="property-select"
        >
          <option value="Arial">Arial</option>
          <option value="Helvetica">Helvetica</option>
          <option value="Times New Roman">Times New Roman</option>
          <option value="Courier New">Courier New</option>
          <option value="Georgia">Georgia</option>
          <option value="Verdana">Verdana</option>
          <option value="Trebuchet MS">Trebuchet MS</option>
          <option value="Comic Sans MS">Comic Sans MS</option>
          <option value="Impact">Impact</option>
          <option value="Lucida Console">Lucida Console</option>
        </select>
      </div>

      {/* Taille de police */}
      <div className="property-row">
        <label>Taille:</label>
        <div className="slider-container">
          <input
            type="range"
            min="8"
            max="72"
            step="1"
            value={localProperties.fontSize || 12}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'fontSize', safeParseInt(e.target.value, 12))}
            className="slider"
          />
          <span className="slider-value">{localProperties.fontSize || 12}px</span>
        </div>
      </div>

      {/* Poids de police */}
      <div className="property-row">
        <label>Épaisseur:</label>
        <select
          value={localProperties.fontWeight || 'normal'}
          onChange={(e) => handlePropertyChange(selectedElement.id, 'fontWeight', e.target.value)}
          className="property-select"
        >
          <option value="normal">Normal</option>
          <option value="bold">Gras</option>
          <option value="lighter">Fin</option>
          <option value="100">100</option>
          <option value="200">200</option>
          <option value="300">300</option>
          <option value="400">400</option>
          <option value="500">500</option>
          <option value="600">600</option>
          <option value="700">700</option>
          <option value="800">800</option>
          <option value="900">900</option>
        </select>
      </div>

      {/* Style de police */}
      <div className="property-row">
        <label>Style:</label>
        <select
          value={localProperties.fontStyle || 'normal'}
          onChange={(e) => handlePropertyChange(selectedElement.id, 'fontStyle', e.target.value)}
          className="property-select"
        >
          <option value="normal">Normal</option>
          <option value="italic">Italique</option>
          <option value="oblique">Oblique</option>
        </select>
      </div>

      {/* Alignement du texte */}
      <div className="property-row">
        <label>Alignement:</label>
        <div className="alignment-buttons">
          <button
            className={`alignment-btn ${localProperties.textAlign === 'left' ? 'active' : ''}`}
            onClick={() => handlePropertyChange(selectedElement.id, 'textAlign', 'left')}
            title="Aligner à gauche"
          >
            ⬅️
          </button>
          <button
            className={`alignment-btn ${localProperties.textAlign === 'center' ? 'active' : ''}`}
            onClick={() => handlePropertyChange(selectedElement.id, 'textAlign', 'center')}
            title="Centrer"
          >
            ⬌
          </button>
          <button
            className={`alignment-btn ${localProperties.textAlign === 'right' ? 'active' : ''}`}
            onClick={() => handlePropertyChange(selectedElement.id, 'textAlign', 'right')}
            title="Aligner à droite"
          >
            ➡️
          </button>
          <button
            className={`alignment-btn ${localProperties.textAlign === 'justify' ? 'active' : ''}`}
            onClick={() => handlePropertyChange(selectedElement.id, 'textAlign', 'justify')}
            title="Justifier"
          >
            ⬌⬅️
          </button>
        </div>
      </div>

      {/* Transformation du texte */}
      <div className="property-row">
        <label>Casse:</label>
        <select
          value={localProperties.textTransform || 'none'}
          onChange={(e) => handlePropertyChange(selectedElement.id, 'textTransform', e.target.value)}
          className="property-select"
        >
          <option value="none">Aucune</option>
          <option value="uppercase">Majuscules</option>
          <option value="lowercase">Minuscules</option>
          <option value="capitalize">Première lettre</option>
        </select>
      </div>

      {/* Décoration de texte */}
      <div className="property-row">
        <label>Décoration:</label>
        <select
          value={localProperties.textDecoration || 'none'}
          onChange={(e) => handlePropertyChange(selectedElement.id, 'textDecoration', e.target.value)}
          className="property-select"
        >
          <option value="none">Aucune</option>
          <option value="underline">Souligné</option>
          <option value="overline">Surligné</option>
          <option value="line-through">Barré</option>
        </select>
      </div>

      {/* Interligne */}
      <div className="property-row">
        <label>Interligne:</label>
        <div className="slider-container">
          <input
            type="range"
            min="0.8"
            max="3"
            step="0.1"
            value={localProperties.lineHeight || 1.2}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'lineHeight', safeParseFloat(e.target.value, 1.2))}
            className="slider"
          />
          <span className="slider-value">{localProperties.lineHeight || 1.2}</span>
        </div>
      </div>

      {/* Espacement des lettres */}
      <div className="property-row">
        <label>Espacement:</label>
        <div className="slider-container">
          <input
            type="range"
            min="-2"
            max="10"
            step="0.5"
            value={localProperties.letterSpacing || 0}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'letterSpacing', safeParseFloat(e.target.value, 0))}
            className="slider"
          />
          <span className="slider-value">{localProperties.letterSpacing || 0}px</span>
        </div>
      </div>
    </Accordion>
  );
};

export default renderTypographySection;