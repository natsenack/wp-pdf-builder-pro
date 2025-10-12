import React from 'react';

// Contrôles pour les tableaux produits
const TableControls = ({ elementId, properties, onPropertyChange }) => {
  return (
    <div className="properties-group">
      <h4>📊 Tableau produits</h4>

      <div className="property-row">
        <label>Colonnes à afficher:</label>
        <div className="checkbox-group">
          {[
            { key: 'image', label: 'Image' },
            { key: 'name', label: 'Nom' },
            { key: 'sku', label: 'SKU' },
            { key: 'quantity', label: 'Quantité' },
            { key: 'price', label: 'Prix' },
            { key: 'total', label: 'Total' }
          ].map(({ key, label }) => (
            <label key={key} className="checkbox-item">
              <input
                type="checkbox"
                checked={properties.columns?.[key] ?? true}
                onChange={(e) => {
                  onPropertyChange(elementId, `columns.${key}`, e.target.checked);
                }}
              />
              {label}
            </label>
          ))}
        </div>
      </div>

      <div className="property-row">
        <label>Style du tableau:</label>
        <select
          value={properties.tableStyle || 'default'}
          onChange={(e) => onPropertyChange(elementId, 'tableStyle', e.target.value)}
        >
          <option value="default">Style par défaut</option>
          <option value="classic">Classique (noir/blanc)</option>
          <option value="striped">Lignes alternées</option>
          <option value="bordered">Encadré</option>
          <option value="minimal">Minimal</option>
          <option value="modern">Moderne</option>
        </select>
      </div>

      <div className="property-row">
        <label>Lignes de totaux:</label>
        <div className="checkbox-group">
          {[
            { key: 'showSubtotal', label: 'Sous-total' },
            { key: 'showShipping', label: 'Frais de port' },
            { key: 'showTaxes', label: 'Taxes' },
            { key: 'showDiscount', label: 'Remise' },
            { key: 'showTotal', label: 'Total général' }
          ].map(({ key, label }) => (
            <label key={key} className="checkbox-item">
              <input
                type="checkbox"
                checked={properties[key] || false}
                onChange={(e) => onPropertyChange(elementId, key, e.target.checked)}
              />
              {label}
            </label>
          ))}
        </div>
      </div>
    </div>
  );
};

export default TableControls;