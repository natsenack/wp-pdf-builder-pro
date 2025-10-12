import React from 'react';

// Contrôles pour les éléments texte (text, layout-header, layout-footer, layout-section)
const TextControls = ({ elementId, properties, onPropertyChange }) => {
  return (
    <div className="properties-group">
      <h4>[Aa] Contenu texte</h4>

      <div className="property-row">
        <label>Texte:</label>
        <textarea
          value={properties.text || ''}
          onChange={(e) => onPropertyChange(elementId, 'text', e.target.value)}
          rows={4}
          placeholder="Saisissez votre texte ici..."
        />
      </div>

      <div className="property-row">
        <label>Variables dynamiques:</label>
        <div className="variables-list">
          <button className="variable-btn" onClick={() => {
            const currentText = properties.text || '';
            onPropertyChange(elementId, 'text', currentText + '{{date}}');
          }}>
            📅 Date
          </button>
          <button className="variable-btn" onClick={() => {
            const currentText = properties.text || '';
            onPropertyChange(elementId, 'text', currentText + '{{order_number}}');
          }}>
            [Ord] N° commande
          </button>
          <button className="variable-btn" onClick={() => {
            const currentText = properties.text || '';
            onPropertyChange(elementId, 'text', currentText + '{{customer_name}}');
          }}>
            👤 Client
          </button>
          <button className="variable-btn" onClick={() => {
            const currentText = properties.text || '';
            onPropertyChange(elementId, 'text', currentText + '{{total}}');
          }}>
            💰 Total
          </button>
        </div>
      </div>
    </div>
  );
};

export default TextControls;