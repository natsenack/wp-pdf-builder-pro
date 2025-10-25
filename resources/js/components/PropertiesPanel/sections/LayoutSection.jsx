import React from 'react';
import Accordion from '../Accordion';
import { safeParseInt, safeParseFloat } from '../utils/helpers';

const renderLayoutSection = (selectedElement, localProperties, handlePropertyChange, activeTab) => {
  return (
    <Accordion
      key="layout"
      title="Position & Taille"
      icon="📐"
      defaultOpen={false}
      className="properties-accordion"
    >
      {/* Position */}
      <div className="property-row">
        <label>Position X:</label>
        <div className="slider-container">
          <input
            type="range"
            min="0"
            max="800"
            value={localProperties.x ?? 0}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'x', safeParseInt(e.target.value, 0))}
            className="slider"
          />
          <span className="slider-value">{localProperties.x ?? 0}px</span>
        </div>
      </div>

      <div className="property-row">
        <label>Position Y:</label>
        <div className="slider-container">
          <input
            type="range"
            min="0"
            max="1000"
            value={localProperties.y ?? 0}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'y', safeParseInt(e.target.value, 0))}
            className="slider"
          />
          <span className="slider-value">{localProperties.y ?? 0}px</span>
        </div>
      </div>

      {/* Dimensions */}
      <div className="property-row">
        <label>Largeur:</label>
        <div className="slider-container">
          <input
            type="range"
            min="10"
            max="800"
            value={localProperties.width ?? 200}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'width', safeParseInt(e.target.value, 200))}
            className="slider"
          />
          <span className="slider-value">{localProperties.width ?? 200}px</span>
        </div>
      </div>

      <div className="property-row">
        <label>Hauteur:</label>
        <div className="slider-container">
          <input
            type="range"
            min="10"
            max="600"
            value={localProperties.height ?? 100}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'height', safeParseInt(e.target.value, 100))}
            className="slider"
          />
          <span className="slider-value">{localProperties.height ?? 100}px</span>
        </div>
      </div>

      {/* Transformations */}
      <div className="property-row">
        <label>Rotation:</label>
        <div className="slider-container">
          <input
            type="range"
            min="-180"
            max="180"
            value={localProperties.rotation ?? 0}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'rotation', safeParseInt(e.target.value, 0))}
            className="slider"
          />
          <span className="slider-value">{localProperties.rotation ?? 0}°</span>
        </div>
      </div>

      {/* Scale disponible seulement pour PRODUCT_TABLE */}
      {selectedElement.type === 'PRODUCT_TABLE' && (
        <>
          <div className="property-row">
            <label>Échelle X:</label>
            <div className="slider-container">
              <input
                type="range"
                min="0.1"
                max="2"
                step="0.1"
                value={localProperties.scaleX ?? 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'scaleX', safeParseFloat(e.target.value, 1))}
                className="slider"
              />
              <span className="slider-value">{localProperties.scaleX ?? 1}</span>
            </div>
          </div>

          <div className="property-row">
            <label>Échelle Y:</label>
            <div className="slider-container">
              <input
                type="range"
                min="0.1"
                max="2"
                step="0.1"
                value={localProperties.scaleY ?? 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'scaleY', safeParseFloat(e.target.value, 1))}
                className="slider"
              />
              <span className="slider-value">{localProperties.scaleY ?? 1}</span>
            </div>
          </div>
        </>
      )}

      {/* Layers */}
      <div className="property-row">
        <label>Calque (Z-Index):</label>
        <div className="slider-container">
          <input
            type="range"
            min="0"
            max="100"
            value={localProperties.zIndex ?? 1}
            onChange={(e) => handlePropertyChange(selectedElement.id, 'zIndex', safeParseInt(e.target.value, 1))}
            className="slider"
          />
          <span className="slider-value">{localProperties.zIndex ?? 1}</span>
        </div>
      </div>
    </Accordion>
  );
};

export default renderLayoutSection;