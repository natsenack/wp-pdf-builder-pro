import React from 'react';
import Accordion from '../Accordion';
import ColorPicker from '../ColorPicker';
import TableStylePreview from '../TableStylePreview';
import { safeParseInt } from '../utils/helpers';

const renderContentSection = (selectedElement, localProperties, handlePropertyChange, activeTab) => {
  const renderContentControls = () => {
    switch (selectedElement.type) {
      case 'text':
        return (
          <>
            <div className="property-row">
              <label>Texte:</label>
              <textarea
                value={localProperties.text || ''}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'text', e.target.value)}
                rows="3"
                placeholder="Entrez votre texte ici..."
                className="text-input"
              />
            </div>
          </>
        );

      case 'dynamic-text':
        return (
          <>
            <div className="property-row">
              <label>Texte dynamique:</label>
              <input
                type="text"
                value={localProperties.dynamicText || ''}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'dynamicText', e.target.value)}
                placeholder="Variable ou expression..."
                className="text-input"
              />
            </div>
            <div className="property-row">
              <label>Variables:</label>
              <textarea
                value={localProperties.variables || ''}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'variables', e.target.value)}
                rows="2"
                placeholder="Variables disponibles..."
                className="text-input"
                readOnly
              />
            </div>
          </>
        );

      case 'company_logo':
        return (
          <>
            <div className="property-row">
              <label>URL Image:</label>
              <input
                type="text"
                value={localProperties.imageUrl || ''}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'imageUrl', e.target.value)}
                placeholder="https://..."
                className="text-input"
              />
            </div>
            <div className="property-row">
              <label>Texte alternatif:</label>
              <input
                type="text"
                value={localProperties.alt || ''}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'alt', e.target.value)}
                placeholder="Description de l'image..."
                className="text-input"
              />
            </div>
            <div className="property-row">
              <label>Ajustement:</label>
              <select
                value={localProperties.objectFit || 'contain'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'objectFit', e.target.value)}
                className="styled-select"
              >
                <option value="contain">Contenir</option>
                <option value="cover">Couvrir</option>
                <option value="fill">Remplir</option>
                <option value="none">Aucun</option>
                <option value="scale-down">Réduire</option>
              </select>
            </div>
          </>
        );

      case 'product_table':
        return (
          <>
            <div className="property-row">
              <label>Colonnes:</label>
              <input
                type="text"
                value={localProperties.columns || 'name,price,quantity'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'columns', e.target.value)}
                placeholder="name,price,quantity..."
                className="text-input"
              />
            </div>
            <TableStylePreview
              selectedStyle={localProperties.tableStyle || 'default'}
              onStyleSelect={(style) => handlePropertyChange(selectedElement.id, 'tableStyle', style)}
            />
            <div className="property-row">
              <label>Afficher en-têtes:</label>
              <input
                type="checkbox"
                checked={localProperties.showHeaders ?? true}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'showHeaders', e.target.checked)}
              />
            </div>
            <div className="property-row">
              <label>Afficher bordures:</label>
              <input
                type="checkbox"
                checked={localProperties.showBorders ?? true}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'showBorders', e.target.checked)}
              />
            </div>
            <div className="property-row">
              <label>Afficher sous-total:</label>
              <input
                type="checkbox"
                checked={localProperties.showSubtotal ?? true}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'showSubtotal', e.target.checked)}
              />
            </div>
            <div className="property-row">
              <label>Afficher frais de port:</label>
              <input
                type="checkbox"
                checked={localProperties.showShipping ?? true}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'showShipping', e.target.checked)}
              />
            </div>
            <div className="property-row">
              <label>Afficher taxes:</label>
              <input
                type="checkbox"
                checked={localProperties.showTaxes ?? true}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'showTaxes', e.target.checked)}
              />
            </div>
            <div className="property-row">
              <label>Afficher remise:</label>
              <input
                type="checkbox"
                checked={localProperties.showDiscount ?? false}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'showDiscount', e.target.checked)}
              />
            </div>
            <div className="property-row">
              <label>Afficher total:</label>
              <input
                type="checkbox"
                checked={localProperties.showTotal ?? true}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'showTotal', e.target.checked)}
              />
            </div>

            {/* Couleurs des lignes alternées */}
            <ColorPicker
              label="Fond lignes paires"
              value={localProperties.evenRowBg || '#ffffff'}
              onChange={(value) => handlePropertyChange(selectedElement.id, 'evenRowBg', value)}
              presets={['#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0']}
            />

            <ColorPicker
              label="Texte lignes paires"
              value={localProperties.evenRowTextColor || '#000000'}
              onChange={(value) => handlePropertyChange(selectedElement.id, 'evenRowTextColor', value)}
              presets={['#000000', '#1e293b', '#334155', '#475569']}
            />

            <ColorPicker
              label="Fond lignes impaires"
              value={localProperties.oddRowBg || '#f8fafc'}
              onChange={(value) => handlePropertyChange(selectedElement.id, 'oddRowBg', value)}
              presets={['#f8fafc', '#f1f5f9', '#e2e8f0', '#ffffff']}
            />

            <ColorPicker
              label="Texte lignes impaires"
              value={localProperties.oddRowTextColor || '#000000'}
              onChange={(value) => handlePropertyChange(selectedElement.id, 'oddRowTextColor', value)}
              presets={['#000000', '#1e293b', '#334155', '#475569']}
            />
          </>
        );

      case 'order_number':
        return (
          <>
            <div className="property-row">
              <label>Format:</label>
              <select
                value={localProperties.format || '#0001'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'format', e.target.value)}
                className="styled-select"
              >
                <option value="#0001">#0001</option>
                <option value="CMD-0001">CMD-0001</option>
                <option value="ORDER-0001">ORDER-0001</option>
                <option value="N°0001">N°0001</option>
              </select>
            </div>
            <div className="property-row">
              <label>Afficher étiquette:</label>
              <input
                type="checkbox"
                checked={localProperties.showLabel ?? true}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'showLabel', e.target.checked)}
              />
            </div>
            {localProperties.showLabel && (
              <>
                <div className="property-row">
                  <label>Texte étiquette:</label>
                  <input
                    type="text"
                    value={localProperties.labelText || 'N° Commande'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'labelText', e.target.value)}
                    className="text-input"
                  />
                </div>
                <div className="property-row">
                  <label>Alignement étiquette:</label>
                  <select
                    value={localProperties.labelAlign || 'left'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'labelAlign', e.target.value)}
                    className="styled-select"
                  >
                    <option value="left">Gauche</option>
                    <option value="center">Centre</option>
                    <option value="right">Droite</option>
                  </select>
                </div>
              </>
            )}
            <div className="property-row">
              <label>N° commande aperçu:</label>
              <input
                type="number"
                min="1"
                value={localProperties.previewOrderNumber ?? 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'previewOrderNumber', safeParseInt(e.target.value, 1))}
                className="number-input"
              />
            </div>
            <div className="property-row">
              <label>Mettre en évidence:</label>
              <input
                type="checkbox"
                checked={localProperties.highlightNumber ?? false}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'highlightNumber', e.target.checked)}
              />
            </div>
            {localProperties.highlightNumber && (
              <ColorPicker
                label="Fond numéro"
                value={localProperties.numberBackground || '#f0f0f0'}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'numberBackground', value)}
                presets={['#f0f0f0', '#ffeaa7', '#fdcb6e', '#e17055', '#fd79a8']}
              />
            )}
          </>
        );

      case 'customer_info':
        return (
          <>
            <div className="property-row">
              <label>Nom client:</label>
              <input
                type="text"
                value={localProperties.customerName || '[Nom du client]'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'customerName', e.target.value)}
                className="text-input"
                readOnly
              />
            </div>
            <div className="property-row">
              <label>Adresse client:</label>
              <textarea
                value={localProperties.customerAddress || '[Adresse du client]'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'customerAddress', e.target.value)}
                rows="2"
                className="text-input"
                readOnly
              />
            </div>
            <div className="property-row">
              <label>Téléphone client:</label>
              <input
                type="text"
                value={localProperties.customerPhone || '[Téléphone]'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'customerPhone', e.target.value)}
                className="text-input"
                readOnly
              />
            </div>
            <div className="property-row">
              <label>Email client:</label>
              <input
                type="text"
                value={localProperties.customerEmail || '[Email]'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'customerEmail', e.target.value)}
                className="text-input"
                readOnly
              />
            </div>
          </>
        );

      case 'company_info':
        return (
          <>
            <div className="property-row">
              <label>Template:</label>
              <select
                value={localProperties.template || 'default'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'template', e.target.value)}
                className="select-input"
              >
                <option value="default">Par défaut</option>
                <option value="commercial">Commercial</option>
              </select>
            </div>
          </>
        );

      case 'mentions':
        return (
          <>
            <div className="property-row">
              <label>Type de mentions:</label>
              <select
                value={localProperties.type || 'legal'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'type', e.target.value)}
                className="select-input"
              >
                <option value="legal">Mentions légales</option>
                <option value="warranty">Garantie</option>
                <option value="custom">Personnalisé</option>
              </select>
            </div>
          </>
        );

      case 'rectangle':
      case 'circle':
      case 'line':
      case 'arrow':
        // Ces éléments n'ont pas de contenu spécifique à configurer
        return (
          <div className="property-row">
            <span className="info-text">Cet élément n'a pas de propriétés de contenu spécifiques.</span>
          </div>
        );

      default:
        return null;
    }
  };

  const contentControls = renderContentControls();
  if (!contentControls) return null;

  return (
    <Accordion
      key="content"
      title="Contenu"
      icon="📝"
      defaultOpen={false}
      className="properties-accordion"
    >
      {contentControls}
    </Accordion>
  );
};

export default renderContentSection;