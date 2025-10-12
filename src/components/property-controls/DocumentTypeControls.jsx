import React from 'react';

// Contrôles pour le type de document
const DocumentTypeControls = ({ elementId, properties, onPropertyChange }) => {
  return (
    <div className="properties-group">
      <h4>📋 Type de Document</h4>

      <div className="property-row">
        <label>Type de document:</label>
        <select
          value={properties.documentType || 'invoice'}
          onChange={(e) => onPropertyChange(elementId, 'documentType', e.target.value)}
        >
          <option value="invoice">Facture</option>
          <option value="quote">Devis</option>
          <option value="receipt">Reçu</option>
          <option value="order">Commande</option>
          <option value="credit_note">Avoir</option>
        </select>
      </div>
    </div>
  );
};

export default DocumentTypeControls;