import React from 'react';

// Contrôles pour les éléments image/logo
const ImageControls = ({ elementId, properties, onPropertyChange }) => {
  return (
    <div className="properties-group">
      <h4>[Img] Image</h4>

      <div className="property-row">
        <label>URL de l'image:</label>
        <input
          type="url"
          value={properties.src || ''}
          onChange={(e) => onPropertyChange(elementId, 'src', e.target.value)}
          placeholder="https://exemple.com/image.jpg"
        />
      </div>

      <div className="property-row">
        <label>Alt text:</label>
        <input
          type="text"
          value={properties.alt || ''}
          onChange={(e) => onPropertyChange(elementId, 'alt', e.target.value)}
          placeholder="Description de l'image"
        />
      </div>

      <div className="property-row">
        <label>Adaptation:</label>
        <select
          value={properties.objectFit || 'cover'}
          onChange={(e) => onPropertyChange(elementId, 'objectFit', e.target.value)}
        >
          <option value="cover">Couvrir (zoom)</option>
          <option value="contain">Contenir (intégral)</option>
          <option value="fill">Remplir</option>
          <option value="none">Aucune</option>
        </select>
      </div>
    </div>
  );
};

export default ImageControls;