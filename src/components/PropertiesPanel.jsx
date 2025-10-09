import React, { useState, useEffect, useCallback } from 'react';
import { useElementCustomization } from '../hooks/useElementCustomization';
import { useElementSynchronization } from '../hooks/useElementSynchronization';
import { elementCustomizationService } from '../services/ElementCustomizationService';
import '../styles/PropertiesPanel.css';

// Composant pour les contrôles de couleur avec presets
const ColorPicker = ({ label, value, onChange, presets = [] }) => (
  <div className="property-row">
    <label>{label}:</label>
    <div className="color-picker-container">
      <input
        type="color"
        value={value || '#1e293b'}
        onChange={(e) => onChange(e.target.value)}
        className="color-input"
      />
      <div className="color-presets">
        {presets.map((preset, index) => (
          <button
            key={index}
            className="color-preset"
            style={{ backgroundColor: preset }}
            onClick={() => onChange(preset)}
            title={preset}
          />
        ))}
      </div>
    </div>
  </div>
);

// Composant pour les contrôles de police
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
          value={properties.fontSize || 14}
          onChange={(e) => onPropertyChange(elementId, 'fontSize', parseInt(e.target.value))}
          className="slider"
        />
        <span className="slider-value">{properties.fontSize || 14}px</span>
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
          className={`style-btn ${properties.textDecoration === 'underline' ? 'active' : ''}`}
          onClick={() => onPropertyChange(elementId, 'textDecoration', properties.textDecoration === 'underline' ? 'none' : 'underline')}
          title="Souligné"
        >
          <u>U</u>
        </button>
        <button
          className={`style-btn ${properties.textDecoration === 'line-through' ? 'active' : ''}`}
          onClick={() => onPropertyChange(elementId, 'textDecoration', properties.textDecoration === 'line-through' ? 'none' : 'line-through')}
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

export const PropertiesPanel = ({
  selectedElements,
  elements,
  onPropertyChange,
  onBatchUpdate
}) => {
  // Utiliser les hooks de personnalisation et synchronisation
  const {
    localProperties,
    activeTab,
    setActiveTab,
    handlePropertyChange: customizationChange
  } = useElementCustomization(selectedElements, elements, onPropertyChange);

  const { syncImmediate, syncBatch } = useElementSynchronization(
    elements,
    onPropertyChange,
    onBatchUpdate,
    true, // autoSave
    1000 // autoSaveDelay
  );

  // Obtenir l'élément sélectionné pour l'affichage
  const selectedElement = selectedElements.length > 0
    ? elements.find(el => el.id === selectedElements[0])
    : null;

  // Gestionnaire unifié de changement de propriété
  const handlePropertyChange = useCallback((elementId, property, value) => {
    // Validation via le service
    if (!elementCustomizationService.validateProperty(property, value)) {
      console.warn(`Propriété invalide: ${property} = ${value}`);
      return;
    }

    // Utiliser le hook de personnalisation pour la gestion locale
    customizationChange(elementId, property, value);

    // Synchronisation immédiate pour les changements critiques
    if (['x', 'y', 'width', 'height'].includes(property)) {
      syncImmediate(elementId, property, value);
    }
  }, [customizationChange, syncImmediate]);

  // Rendu des onglets
  const renderTabs = useCallback(() => (
    <div className="properties-tabs">
      <button
        className={`tab-btn ${activeTab === 'appearance' ? 'active' : ''}`}
        onClick={() => setActiveTab('appearance')}
      >
        🎨 Apparence
      </button>
      <button
        className={`tab-btn ${activeTab === 'layout' ? 'active' : ''}`}
        onClick={() => setActiveTab('layout')}
      >
        📐 Mise en page
      </button>
      <button
        className={`tab-btn ${activeTab === 'content' ? 'active' : ''}`}
        onClick={() => setActiveTab('content')}
      >
        📝 Contenu
      </button>
      <button
        className={`tab-btn ${activeTab === 'effects' ? 'active' : ''}`}
        onClick={() => setActiveTab('effects')}
      >
        ✨ Effets
      </button>
    </div>
  ), [activeTab]);

  // Rendu du contenu selon l'onglet actif
  const renderTabContent = useCallback(() => {
    if (!selectedElement) {
      return (
        <div className="no-selection">
          <div className="no-selection-icon">👆</div>
          <p>Sélectionnez un élément pour modifier ses propriétés</p>
          {selectedElements.length > 1 && (
            <p className="selection-info">
              {selectedElements.length} éléments sélectionnés
            </p>
          )}
        </div>
      );
    }

    switch (activeTab) {
      case 'appearance':
        return (
          <div className="tab-content">
            <div className="properties-group">
              <h4>🎨 Couleurs</h4>

              <ColorPicker
                label="Texte"
                value={localProperties.color}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'color', value)}
                presets={['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1']}
              />

              <ColorPicker
                label="Fond"
                value={localProperties.fillColor}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'fillColor', value)}
                presets={['#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8']}
              />

              <ColorPicker
                label="Bordure"
                value={localProperties.borderColor}
                onChange={(value) => handlePropertyChange(selectedElement.id, 'borderColor', value)}
                presets={['#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155']}
              />
            </div>

            {(selectedElement.type === 'text' || selectedElement.type === 'layout-header' ||
              selectedElement.type === 'layout-footer' || selectedElement.type === 'layout-section') && (
              <FontControls
                elementId={selectedElement.id}
                properties={localProperties}
                onPropertyChange={handlePropertyChange}
              />
            )}

            <div className="properties-group">
              <h4>🔲 Bordures & Coins</h4>

              <div className="property-row">
                <label>Épaisseur bordure:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="10"
                    value={localProperties.borderWidth || 0}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.borderWidth || 0}px</span>
                </div>
              </div>

              <div className="property-row">
                <label>Arrondi des coins:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="50"
                    value={localProperties.borderRadius || 0}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.borderRadius || 0}px</span>
                </div>
              </div>
            </div>
          </div>
        );

      case 'layout':
        return (
          <div className="tab-content">
            {/* Presets de mise en page pour factures/devis */}
            <div className="properties-group">
              <h4>📋 Presets Facturation</h4>
              <div className="preset-buttons">
                <button
                  className="preset-btn"
                  onClick={() => {
                    // Position en-tête facture
                    handlePropertyChange(selectedElement.id, 'x', 20);
                    handlePropertyChange(selectedElement.id, 'y', 20);
                    handlePropertyChange(selectedElement.id, 'width', 170);
                    handlePropertyChange(selectedElement.id, 'height', 30);
                  }}
                  title="Position en-tête (logo, société)"
                >
                  🏢 En-tête
                </button>
                <button
                  className="preset-btn"
                  onClick={() => {
                    // Position infos client
                    handlePropertyChange(selectedElement.id, 'x', 20);
                    handlePropertyChange(selectedElement.id, 'y', 60);
                    handlePropertyChange(selectedElement.id, 'width', 80);
                    handlePropertyChange(selectedElement.id, 'height', 40);
                  }}
                  title="Section informations client"
                >
                  👤 Client
                </button>
                <button
                  className="preset-btn"
                  onClick={() => {
                    // Position tableau articles
                    handlePropertyChange(selectedElement.id, 'x', 20);
                    handlePropertyChange(selectedElement.id, 'y', 110);
                    handlePropertyChange(selectedElement.id, 'width', 170);
                    handlePropertyChange(selectedElement.id, 'height', 120);
                  }}
                  title="Tableau des articles/services"
                >
                  📊 Tableau
                </button>
                <button
                  className="preset-btn"
                  onClick={() => {
                    // Position totaux
                    handlePropertyChange(selectedElement.id, 'x', 110);
                    handlePropertyChange(selectedElement.id, 'y', 240);
                    handlePropertyChange(selectedElement.id, 'width', 80);
                    handlePropertyChange(selectedElement.id, 'height', 30);
                  }}
                  title="Section totaux (HT, TVA, TTC)"
                >
                  💰 Totaux
                </button>
                <button
                  className="preset-btn"
                  onClick={() => {
                    // Position pied de page
                    handlePropertyChange(selectedElement.id, 'x', 20);
                    handlePropertyChange(selectedElement.id, 'y', 270);
                    handlePropertyChange(selectedElement.id, 'width', 170);
                    handlePropertyChange(selectedElement.id, 'height', 20);
                  }}
                  title="Pied de page (mentions légales)"
                >
                  📄 Pied
                </button>
                <button
                  className="preset-btn"
                  onClick={() => {
                    // Position numéro facture
                    handlePropertyChange(selectedElement.id, 'x', 120);
                    handlePropertyChange(selectedElement.id, 'y', 20);
                    handlePropertyChange(selectedElement.id, 'width', 70);
                    handlePropertyChange(selectedElement.id, 'height', 15);
                  }}
                  title="Numéro de facture/devis"
                >
                  🔢 N° Facture
                </button>
              </div>
            </div>

            {/* Alignements spécialisés facturation */}
            <div className="properties-group">
              <h4>� Alignements Facturation</h4>
              <div className="billing-alignments">
                <div className="alignment-row">
                  <button
                    className="align-btn billing"
                    onClick={() => handlePropertyChange(selectedElement.id, 'x', 20)}
                    title="Aligner à gauche (descriptions)"
                  >
                    📝 Gauche
                  </button>
                  <button
                    className="align-btn billing"
                    onClick={() => handlePropertyChange(selectedElement.id, 'x', 95)}
                    title="Centrer horizontalement"
                  >
                    🎯 Centre
                  </button>
                  <button
                    className="align-btn billing"
                    onClick={() => handlePropertyChange(selectedElement.id, 'x', 150)}
                    title="Aligner à droite (montants)"
                  >
                    💵 Droite
                  </button>
                </div>
                <div className="alignment-row">
                  <button
                    className="align-btn billing"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', 20)}
                    title="Position en-tête"
                  >
                    ⬆️ En-tête
                  </button>
                  <button
                    className="align-btn billing"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', 148)}
                    title="Milieu de page"
                  >
                    📄 Milieu
                  </button>
                  <button
                    className="align-btn billing"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', 270)}
                    title="Position pied de page"
                  >
                    ⬇️ Pied
                  </button>
                </div>
              </div>
            </div>

            {/* Dimensions standardisées facturation */}
            <div className="properties-group">
              <h4>📏 Dimensions Standard</h4>

              <div className="property-row">
                <label>Taille:</label>
                <div className="standard-sizes">
                  <button
                    className="size-btn"
                    onClick={() => {
                      handlePropertyChange(selectedElement.id, 'width', 170);
                      handlePropertyChange(selectedElement.id, 'height', 10);
                    }}
                    title="Ligne de tableau"
                  >
                    📏 Ligne
                  </button>
                  <button
                    className="size-btn"
                    onClick={() => {
                      handlePropertyChange(selectedElement.id, 'width', 80);
                      handlePropertyChange(selectedElement.id, 'height', 40);
                    }}
                    title="Bloc d'informations"
                  >
                    📦 Bloc
                  </button>
                  <button
                    className="size-btn"
                    onClick={() => {
                      handlePropertyChange(selectedElement.id, 'width', 50);
                      handlePropertyChange(selectedElement.id, 'height', 15);
                    }}
                    title="Petit élément (logo, numéro)"
                  >
                    🔸 Petit
                  </button>
                  <button
                    className="size-btn"
                    onClick={() => {
                      handlePropertyChange(selectedElement.id, 'width', 170);
                      handlePropertyChange(selectedElement.id, 'height', 120);
                    }}
                    title="Tableau complet"
                  >
                    📊 Tableau
                  </button>
                </div>
              </div>

              {/* Colonnes pour tableaux */}
              <div className="property-row">
                <label>Colonnes:</label>
                <div className="column-presets">
                  <button
                    className="column-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'width', 50)}
                    title="Colonne description (50mm)"
                  >
                    📝 Desc.
                  </button>
                  <button
                    className="column-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'width', 25)}
                    title="Colonne quantité (25mm)"
                  >
                    🔢 Qté
                  </button>
                  <button
                    className="column-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'width', 30)}
                    title="Colonne prix (30mm)"
                  >
                    💰 Prix
                  </button>
                  <button
                    className="column-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'width', 35)}
                    title="Colonne total (35mm)"
                  >
                    🧮 Total
                  </button>
                </div>
              </div>
            </div>

            {/* Espacement et marges */}
            <div className="properties-group">
              <h4>📏 Espacement Facturation</h4>

              <div className="property-row">
                <label>Marge standard:</label>
                <div className="margin-buttons">
                  <button
                    className="margin-btn"
                    onClick={() => {
                      handlePropertyChange(selectedElement.id, 'x', 20);
                      handlePropertyChange(selectedElement.id, 'y', (localProperties.y || 0) + 5);
                    }}
                    title="Marge normale (5mm)"
                  >
                    📐 Normal
                  </button>
                  <button
                    className="margin-btn"
                    onClick={() => {
                      handlePropertyChange(selectedElement.id, 'x', 20);
                      handlePropertyChange(selectedElement.id, 'y', (localProperties.y || 0) + 10);
                    }}
                    title="Grande marge (10mm)"
                  >
                    📏 Large
                  </button>
                  <button
                    className="margin-btn"
                    onClick={() => {
                      handlePropertyChange(selectedElement.id, 'x', 20);
                      handlePropertyChange(selectedElement.id, 'y', (localProperties.y || 0) + 2);
                    }}
                    title="Petite marge (2mm)"
                  >
                    📏 Étroit
                  </button>
                </div>
              </div>

              {/* Lignes de tableau */}
              <div className="property-row">
                <label>Ligne suivante:</label>
                <div className="line-spacing">
                  <button
                    className="spacing-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', (localProperties.y || 0) + 8)}
                    title="Ligne de tableau (8mm)"
                  >
                    📊 +8mm
                  </button>
                  <button
                    className="spacing-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', (localProperties.y || 0) + 12)}
                    title="Section suivante (12mm)"
                  >
                    📄 +12mm
                  </button>
                  <button
                    className="spacing-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'y', (localProperties.y || 0) + 20)}
                    title="Nouvelle section (20mm)"
                  >
                    📑 +20mm
                  </button>
                </div>
              </div>
            </div>

            {/* Position précise avec grille */}
            <div className="properties-group">
              <h4>📍 Position Précise</h4>

              <div className="property-row">
                <label>X (mm):</label>
                <div className="input-with-unit">
                  <input
                    type="number"
                    value={Math.round(localProperties.x || 0)}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'x', parseInt(e.target.value))}
                    step="1"
                    min="0"
                    max="210"
                  />
                  <span className="unit">mm</span>
                </div>
              </div>

              <div className="property-row">
                <label>Y (mm):</label>
                <div className="input-with-unit">
                  <input
                    type="number"
                    value={Math.round(localProperties.y || 0)}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'y', parseInt(e.target.value))}
                    step="1"
                    min="0"
                    max="297"
                  />
                  <span className="unit">mm</span>
                </div>
              </div>

              {/* Grille d'alignement */}
              <div className="property-row">
                <label>Grille:</label>
                <div className="grid-buttons">
                  <button
                    className="grid-btn"
                    onClick={() => {
                      const x = Math.round((localProperties.x || 0) / 10) * 10;
                      handlePropertyChange(selectedElement.id, 'x', x);
                    }}
                    title="Aligner sur grille 10mm"
                  >
                    📐 10mm
                  </button>
                  <button
                    className="grid-btn"
                    onClick={() => {
                      const y = Math.round((localProperties.y || 0) / 5) * 5;
                      handlePropertyChange(selectedElement.id, 'y', y);
                    }}
                    title="Aligner sur grille 5mm"
                  >
                    📐 5mm
                  </button>
                </div>
              </div>
            </div>

            {/* Calques et profondeur */}
            <div className="properties-group">
              <h4>📚 Organisation</h4>

              <div className="property-row">
                <label>Calque:</label>
                <div className="layer-buttons">
                  <button
                    className="layer-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'zIndex', 1)}
                    title="Arrière-plan"
                  >
                    🔙 Arrière
                  </button>
                  <button
                    className="layer-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'zIndex', 5)}
                    title="Plan normal"
                  >
                    📄 Normal
                  </button>
                  <button
                    className="layer-btn"
                    onClick={() => handlePropertyChange(selectedElement.id, 'zIndex', 10)}
                    title="Premier plan"
                  >
                    🔛 Devant
                  </button>
                </div>
              </div>
            </div>
          </div>
        );

      case 'content':
        return (
          <div className="tab-content">
            {selectedElement.type === 'text' && (
              <div className="properties-group">
                <h4>📝 Contenu texte</h4>

                <div className="property-row">
                  <label>Texte:</label>
                  <textarea
                    value={localProperties.text || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'text', e.target.value)}
                    rows={4}
                    placeholder="Saisissez votre texte ici..."
                  />
                </div>

                <div className="property-row">
                  <label>Variables dynamiques:</label>
                  <div className="variables-list">
                    <button className="variable-btn" onClick={() => {
                      const currentText = localProperties.text || '';
                      handlePropertyChange(selectedElement.id, 'text', currentText + '{{date}}');
                    }}>
                      📅 Date
                    </button>
                    <button className="variable-btn" onClick={() => {
                      const currentText = localProperties.text || '';
                      handlePropertyChange(selectedElement.id, 'text', currentText + '{{order_number}}');
                    }}>
                      🏷️ N° commande
                    </button>
                    <button className="variable-btn" onClick={() => {
                      const currentText = localProperties.text || '';
                      handlePropertyChange(selectedElement.id, 'text', currentText + '{{customer_name}}');
                    }}>
                      👤 Client
                    </button>
                    <button className="variable-btn" onClick={() => {
                      const currentText = localProperties.text || '';
                      handlePropertyChange(selectedElement.id, 'text', currentText + '{{total}}');
                    }}>
                      💰 Total
                    </button>
                  </div>
                </div>
              </div>
            )}

            {(selectedElement.type === 'image' || selectedElement.type === 'company_logo') && (
              <div className="properties-group">
                <h4>🖼️ Image</h4>

                <div className="property-row">
                  <label>URL de l'image:</label>
                  <input
                    type="url"
                    value={localProperties.src || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'src', e.target.value)}
                    placeholder="https://exemple.com/image.jpg"
                  />
                </div>

                <div className="property-row">
                  <label>Alt text:</label>
                  <input
                    type="text"
                    value={localProperties.alt || ''}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'alt', e.target.value)}
                    placeholder="Description de l'image"
                  />
                </div>

                <div className="property-row">
                  <label>Adaptation:</label>
                  <select
                    value={localProperties.objectFit || 'cover'}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'objectFit', e.target.value)}
                  >
                    <option value="cover">Couvrir (zoom)</option>
                    <option value="contain">Contenir (intégral)</option>
                    <option value="fill">Remplir</option>
                    <option value="none">Aucune</option>
                  </select>
                </div>
              </div>
            )}

            {selectedElement.type === 'product_table' && (
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
                          checked={localProperties.columns?.[key] ?? true}
                          onChange={(e) => handlePropertyChange(selectedElement.id, `columns.${key}`, e.target.checked)}
                        />
                        {label}
                      </label>
                    ))}
                  </div>
                </div>
              </div>
            )}
          </div>
        );

      case 'effects':
        return (
          <div className="tab-content">
            <div className="properties-group">
              <h4>🌟 Transparence & Visibilité</h4>

              <div className="property-row">
                <label>Opacité:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="100"
                    value={localProperties.opacity || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'opacity', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.opacity || 100}%</span>
                </div>
              </div>

              <div className="property-row">
                <label>Visibilité:</label>
                <label className="toggle">
                  <input
                    type="checkbox"
                    checked={localProperties.visible !== false}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'visible', e.target.checked)}
                  />
                  <span className="toggle-slider"></span>
                </label>
              </div>
            </div>

            <div className="properties-group">
              <h4>✨ Ombres & Effets</h4>

              <div className="property-row">
                <label>Ombre:</label>
                <label className="toggle">
                  <input
                    type="checkbox"
                    checked={localProperties.shadow || false}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'shadow', e.target.checked)}
                  />
                  <span className="toggle-slider"></span>
                </label>
              </div>

              {localProperties.shadow && (
                <>
                  <ColorPicker
                    label="Couleur ombre"
                    value={localProperties.shadowColor}
                    onChange={(value) => handlePropertyChange(selectedElement.id, 'shadowColor', value)}
                    presets={['#000000', '#374151', '#6b7280', '#9ca3af']}
                  />

                  <div className="property-row">
                    <label>Décalage X:</label>
                    <input
                      type="number"
                      value={localProperties.shadowOffsetX || 2}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetX', parseInt(e.target.value))}
                      min="-20"
                      max="20"
                    />
                  </div>

                  <div className="property-row">
                    <label>Décalage Y:</label>
                    <input
                      type="number"
                      value={localProperties.shadowOffsetY || 2}
                      onChange={(e) => handlePropertyChange(selectedElement.id, 'shadowOffsetY', parseInt(e.target.value))}
                      min="-20"
                      max="20"
                    />
                  </div>
                </>
              )}
            </div>

            <div className="properties-group">
              <h4>🎭 Filtres visuels</h4>

              <div className="property-row">
                <label>Luminosité:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="200"
                    value={localProperties.brightness || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'brightness', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.brightness || 100}%</span>
                </div>
              </div>

              <div className="property-row">
                <label>Contraste:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="200"
                    value={localProperties.contrast || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'contrast', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.contrast || 100}%</span>
                </div>
              </div>

              <div className="property-row">
                <label>Saturation:</label>
                <div className="slider-container">
                  <input
                    type="range"
                    min="0"
                    max="200"
                    value={localProperties.saturate || 100}
                    onChange={(e) => handlePropertyChange(selectedElement.id, 'saturate', parseInt(e.target.value))}
                    className="slider"
                  />
                  <span className="slider-value">{localProperties.saturate || 100}%</span>
                </div>
              </div>
            </div>
          </div>
        );

      default:
        return null;
    }
  }, [activeTab, selectedElement, localProperties, handlePropertyChange, selectedElements.length]);

  return (
    <div className="properties-panel">
      <div className="properties-header">
        <h3>Propriétés</h3>
        {selectedElement && (
          <div className="element-info">
            <span className="element-type">{selectedElement.type}</span>
            <span className="element-id">#{selectedElement.id}</span>
          </div>
        )}
      </div>

      {renderTabs()}
      <div className="properties-content">
        {renderTabContent()}
      </div>
    </div>
  );
};