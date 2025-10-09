import React, { useState, useEffect } from 'react';

export const PropertiesPanel = ({
  selectedElements,
  elements,
  onPropertyChange
}) => {
  const [localProperties, setLocalProperties] = useState({});

  // Obtenir l'élément sélectionné (premier de la liste pour l'instant)
  const selectedElement = selectedElements.length > 0
    ? elements.find(el => el.id === selectedElements[0])
    : null;

  // Mettre à jour les propriétés locales quand l'élément change
  useEffect(() => {
    if (selectedElement) {
      setLocalProperties({ ...selectedElement });
    } else {
      setLocalProperties({});
    }
  }, [selectedElement]);

  // Gestionnaire de changement de propriété
  const handlePropertyChange = (elementId, property, value) => {
    setLocalProperties(prev => ({ ...prev, [property]: value }));
    onPropertyChange(elementId, property, value);
  };

  // Rendu des contrôles selon le type d'élément
  const renderElementProperties = () => {
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

    switch (selectedElement.type) {
      case 'text':
        return (
          <div className="properties-group">
            <h4>Propriétés du texte</h4>

            <div className="property-row">
              <label>Texte:</label>
              <textarea
                value={localProperties.text || ''}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'text', e.target.value)}
                rows={3}
              />
            </div>

            <div className="property-row">
              <label>Taille:</label>
              <input
                type="number"
                value={localProperties.fontSize || 14}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'fontSize', parseInt(e.target.value))}
                min="8"
                max="72"
              />
            </div>

            <div className="property-row">
              <label>Police:</label>
              <select
                value={localProperties.fontFamily || 'Arial'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'fontFamily', e.target.value)}
              >
                <option value="Arial">Arial</option>
                <option value="Helvetica">Helvetica</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Courier New">Courier New</option>
                <option value="Georgia">Georgia</option>
                <option value="Verdana">Verdana</option>
              </select>
            </div>

            <div className="property-row">
              <label>Style:</label>
              <div className="style-buttons">
                <button
                  className={localProperties.fontWeight === 'bold' ? 'active' : ''}
                  onClick={() => handlePropertyChange(selectedElement.id, 'fontWeight', localProperties.fontWeight === 'bold' ? 'normal' : 'bold')}
                >
                  <strong>B</strong>
                </button>
                <button
                  className={localProperties.fontStyle === 'italic' ? 'active' : ''}
                  onClick={() => handlePropertyChange(selectedElement.id, 'fontStyle', localProperties.fontStyle === 'italic' ? 'normal' : 'italic')}
                >
                  <em>I</em>
                </button>
                <button
                  className={localProperties.textDecoration === 'underline' ? 'active' : ''}
                  onClick={() => handlePropertyChange(selectedElement.id, 'textDecoration', localProperties.textDecoration === 'underline' ? 'none' : 'underline')}
                >
                  <u>U</u>
                </button>
              </div>
            </div>

            <div className="property-row">
              <label>Couleur:</label>
              <input
                type="color"
                value={localProperties.color || '#1e293b'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'color', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Alignement:</label>
              <div className="alignment-buttons">
                {['left', 'center', 'right'].map(align => (
                  <button
                    key={align}
                    className={localProperties.textAlign === align ? 'active' : ''}
                    onClick={() => handlePropertyChange(selectedElement.id, 'textAlign', align)}
                  >
                    {align === 'left' ? '⬅️' : align === 'center' ? '⬌' : '➡️'}
                  </button>
                ))}
              </div>
            </div>
          </div>
        );

      case 'rectangle':
        return (
          <div className="properties-group">
            <h4>Propriétés du rectangle</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.fillColor || '#ffffff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'fillColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur de bordure:</label>
              <input
                type="color"
                value={localProperties.borderColor || '#6b7280'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Épaisseur de bordure:</label>
              <input
                type="number"
                value={localProperties.borderWidth || 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', parseInt(e.target.value))}
                min="0"
                max="10"
              />
            </div>

            <div className="property-row">
              <label>Rayon des coins:</label>
              <input
                type="number"
                value={localProperties.borderRadius || 0}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', parseInt(e.target.value))}
                min="0"
                max="50"
              />
            </div>
          </div>
        );

      case 'line':
        return (
          <div className="properties-group">
            <h4>Propriétés de la ligne</h4>

            <div className="property-row">
              <label>Couleur:</label>
              <input
                type="color"
                value={localProperties.lineColor || '#6b7280'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'lineColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Épaisseur:</label>
              <input
                type="number"
                value={localProperties.lineWidth || 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'lineWidth', parseInt(e.target.value))}
                min="1"
                max="10"
              />
            </div>
          </div>
        );

      case 'layout-header':
      case 'layout-footer':
      case 'layout-sidebar':
      case 'layout-section':
      case 'layout-container':
        const layoutLabels = {
          'layout-header': 'En-tête',
          'layout-footer': 'Pied de Page',
          'layout-sidebar': 'Barre Latérale',
          'layout-section': 'Section',
          'layout-container': 'Conteneur'
        };

        return (
          <div className="properties-group">
            <h4>Propriétés de {layoutLabels[selectedElement.type]}</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || (selectedElement.type === 'layout-container' ? 'transparent' : '#f8fafc')}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur de bordure:</label>
              <input
                type="color"
                value={localProperties.borderColor || (selectedElement.type === 'layout-container' ? '#cbd5e1' : '#e2e8f0')}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Épaisseur de bordure:</label>
              <input
                type="number"
                value={localProperties.borderWidth || (selectedElement.type === 'layout-container' ? 2 : 1)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', parseInt(e.target.value))}
                min="0"
                max="10"
              />
            </div>

            {selectedElement.type === 'layout-container' && (
              <div className="property-row">
                <label>Style de bordure:</label>
                <select
                  value={localProperties.borderStyle || 'dashed'}
                  onChange={(e) => handlePropertyChange(selectedElement.id, 'borderStyle', e.target.value)}
                >
                  <option value="solid">Continue</option>
                  <option value="dashed">Tirets</option>
                  <option value="dotted">Pointillés</option>
                </select>
              </div>
            )}

            <div className="property-row">
              <label>Rayon des coins:</label>
              <input
                type="number"
                value={localProperties.borderRadius || 4}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', parseInt(e.target.value))}
                min="0"
                max="50"
              />
            </div>
          </div>
        );

      // Formes et Graphiques
      case 'shape-rectangle':
      case 'shape-circle':
      case 'shape-triangle':
      case 'shape-star':
        return (
          <div className="properties-group">
            <h4>Propriétés de la forme</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#e5e7eb'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur de bordure:</label>
              <input
                type="color"
                value={localProperties.borderColor || '#000000'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Épaisseur de bordure:</label>
              <input
                type="number"
                value={localProperties.borderWidth || 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', parseInt(e.target.value))}
                min="0"
                max="10"
              />
            </div>

            {selectedElement.type === 'shape-rectangle' && (
              <div className="property-row">
                <label>Rayon des coins:</label>
                <input
                  type="number"
                  value={localProperties.borderRadius || 0}
                  onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', parseInt(e.target.value))}
                  min="0"
                  max="50"
                />
              </div>
            )}
          </div>
        );

      case 'shape-line':
        return (
          <div className="properties-group">
            <h4>Propriétés de la ligne</h4>

            <div className="property-row">
              <label>Couleur:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#6b7280'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'shape-arrow':
        return (
          <div className="properties-group">
            <h4>Propriétés de la flèche</h4>

            <div className="property-row">
              <label>Couleur:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#374151'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'divider':
        return (
          <div className="properties-group">
            <h4>Propriétés du séparateur</h4>

            <div className="property-row">
              <label>Couleur:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#d1d5db'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      // Médias
      case 'image':
      case 'image-upload':
        return (
          <div className="properties-group">
            <h4>Propriétés de l'image</h4>

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
              <label>Taille de l'arrière-plan:</label>
              <select
                value={localProperties.backgroundSize || 'contain'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundSize', e.target.value)}
              >
                <option value="contain">Contenir</option>
                <option value="cover">Couvrir</option>
                <option value="auto">Taille réelle</option>
              </select>
            </div>
          </div>
        );

      case 'logo':
        return (
          <div className="properties-group">
            <h4>Propriétés du logo</h4>

            <div className="property-row">
              <label>URL du logo:</label>
              <input
                type="url"
                value={localProperties.src || ''}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'src', e.target.value)}
                placeholder="https://exemple.com/logo.png"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#f3f4f6'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'barcode':
        return (
          <div className="properties-group">
            <h4>Propriétés du code-barres</h4>

            <div className="property-row">
              <label>Valeur:</label>
              <input
                type="text"
                value={localProperties.content || '123456789'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder="Entrez le code-barres"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#ffffff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur des barres:</label>
              <input
                type="color"
                value={localProperties.borderColor || '#000000'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'qrcode':
      case 'qrcode-dynamic':
        return (
          <div className="properties-group">
            <h4>Propriétés du QR Code</h4>

            <div className="property-row">
              <label>Contenu:</label>
              <input
                type="text"
                value={localProperties.content || 'https://exemple.com'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder="URL ou texte à encoder"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#ffffff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur des modules:</label>
              <input
                type="color"
                value={localProperties.borderColor || '#000000'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'icon':
        return (
          <div className="properties-group">
            <h4>Propriétés de l'icône</h4>

            <div className="property-row">
              <label>Icône:</label>
              <input
                type="text"
                value={localProperties.content || '🎯'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder="Emoji ou caractère"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || 'transparent'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      // Données Dynamiques
      case 'dynamic-text':
        return (
          <div className="properties-group">
            <h4>Propriétés du texte dynamique</h4>

            <div className="property-row">
              <label>Variable:</label>
              <input
                type="text"
                value={localProperties.content || '{{variable}}'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder="{{nom_client}}"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#f8fafc'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'formula':
        return (
          <div className="properties-group">
            <h4>Propriétés de la formule</h4>

            <div className="property-row">
              <label>Formule:</label>
              <input
                type="text"
                value={localProperties.content || '{{prix * quantite}}'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder="{{prix * quantite}}"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#fef3c7'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'conditional-text':
        return (
          <div className="properties-group">
            <h4>Propriétés du texte conditionnel</h4>

            <div className="property-row">
              <label>Condition:</label>
              <input
                type="text"
                value={localProperties.content || '{{condition ? "Oui" : "Non"}}'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder='{{statut === "actif" ? "✓ Actif" : "✗ Inactif"}}'
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#ecfdf5'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'counter':
        return (
          <div className="properties-group">
            <h4>Propriétés du compteur</h4>

            <div className="property-row">
              <label>Valeur de départ:</label>
              <input
                type="number"
                value={localProperties.content || 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', parseInt(e.target.value))}
                min="0"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#f0f9ff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'date-dynamic':
        return (
          <div className="properties-group">
            <h4>Propriétés de la date dynamique</h4>

            <div className="property-row">
              <label>Format:</label>
              <input
                type="text"
                value={localProperties.content || '{{date|format:Y-m-d}}'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder="{{date|format:d/m/Y}}"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#f3f4f6'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'currency':
        return (
          <div className="properties-group">
            <h4>Propriétés de la devise</h4>

            <div className="property-row">
              <label>Montant:</label>
              <input
                type="text"
                value={localProperties.content || '{{montant|currency:EUR}}'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder="{{prix|currency:USD}}"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#f0fdf4'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>
          </div>
        );

      case 'table-dynamic':
        return (
          <div className="properties-group">
            <h4>Propriétés du tableau dynamique</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#ffffff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur de bordure:</label>
              <input
                type="color"
                value={localProperties.borderColor || '#e5e7eb'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Épaisseur de bordure:</label>
              <input
                type="number"
                value={localProperties.borderWidth || 1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', parseInt(e.target.value))}
                min="0"
                max="5"
              />
            </div>
          </div>
        );

      // Éléments Avancés
      case 'gradient-box':
        return (
          <div className="properties-group">
            <h4>Propriétés de la boîte dégradé</h4>

            <div className="property-row">
              <label>Couleur 1:</label>
              <input
                type="color"
                value={localProperties.gradientColor1 || '#667eea'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'gradientColor1', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur 2:</label>
              <input
                type="color"
                value={localProperties.gradientColor2 || '#764ba2'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'gradientColor2', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Direction:</label>
              <select
                value={localProperties.gradientDirection || '45deg'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'gradientDirection', e.target.value)}
              >
                <option value="45deg">Diagonale</option>
                <option value="90deg">Vertical</option>
                <option value="0deg">Horizontal</option>
                <option value="135deg">Diagonale inverse</option>
              </select>
            </div>
          </div>
        );

      case 'shadow-box':
        return (
          <div className="properties-group">
            <h4>Propriétés de la boîte avec ombre</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#ffffff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Ombre:</label>
              <select
                value={localProperties.boxShadow || '0 4px 6px -1px rgba(0, 0, 0, 0.1)'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'boxShadow', e.target.value)}
              >
                <option value="0 1px 3px rgba(0, 0, 0, 0.1)">Légère</option>
                <option value="0 4px 6px -1px rgba(0, 0, 0, 0.1)">Normale</option>
                <option value="0 10px 15px -3px rgba(0, 0, 0, 0.1)">Forte</option>
                <option value="0 20px 25px -5px rgba(0, 0, 0, 0.1)">Très forte</option>
              </select>
            </div>
          </div>
        );

      case 'rounded-box':
        return (
          <div className="properties-group">
            <h4>Propriétés de la boîte arrondie</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#ffffff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Rayon des coins:</label>
              <input
                type="number"
                value={localProperties.borderRadius || 12}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderRadius', parseInt(e.target.value))}
                min="0"
                max="50"
              />
            </div>
          </div>
        );

      case 'border-box':
        return (
          <div className="properties-group">
            <h4>Propriétés de la boîte avec bordure</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#ffffff'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur de bordure:</label>
              <input
                type="color"
                value={localProperties.borderColor || '#3b82f6'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Épaisseur de bordure:</label>
              <input
                type="number"
                value={localProperties.borderWidth || 3}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'borderWidth', parseInt(e.target.value))}
                min="1"
                max="10"
              />
            </div>
          </div>
        );

      case 'background-pattern':
        return (
          <div className="properties-group">
            <h4>Propriétés du motif d'arrière-plan</h4>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#f8fafc'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Type de motif:</label>
              <select
                value={localProperties.patternType || 'diagonal'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'patternType', e.target.value)}
              >
                <option value="diagonal">Diagonales</option>
                <option value="grid">Grille</option>
                <option value="dots">Points</option>
                <option value="stripes">Rayures</option>
              </select>
            </div>
          </div>
        );

      case 'watermark':
        return (
          <div className="properties-group">
            <h4>Propriétés du filigrane</h4>

            <div className="property-row">
              <label>Texte:</label>
              <input
                type="text"
                value={localProperties.content || 'CONFIDENTIEL'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'content', e.target.value)}
                placeholder="Texte du filigrane"
              />
            </div>

            <div className="property-row">
              <label>Couleur:</label>
              <input
                type="color"
                value={localProperties.color || '#9ca3af'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'color', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Taille:</label>
              <input
                type="number"
                value={localProperties.fontSize || 48}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'fontSize', parseInt(e.target.value))}
                min="12"
                max="200"
              />
            </div>

            <div className="property-row">
              <label>Opacité:</label>
              <input
                type="number"
                value={localProperties.opacity || 0.1}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'opacity', parseFloat(e.target.value))}
                min="0.01"
                max="1"
                step="0.01"
              />
            </div>
          </div>
        );

      case 'progress-bar':
        return (
          <div className="properties-group">
            <h4>Propriétés de la barre de progression</h4>

            <div className="property-row">
              <label>Valeur (%):</label>
              <input
                type="number"
                value={localProperties.progressValue || 75}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'progressValue', parseInt(e.target.value))}
                min="0"
                max="100"
              />
            </div>

            <div className="property-row">
              <label>Couleur de fond:</label>
              <input
                type="color"
                value={localProperties.backgroundColor || '#e5e7eb'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'backgroundColor', e.target.value)}
              />
            </div>

            <div className="property-row">
              <label>Couleur de progression:</label>
              <input
                type="color"
                value={localProperties.progressColor || '#3b82f6'}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'progressColor', e.target.value)}
              />
            </div>
          </div>
        );

      default:
        return (
          <div className="properties-group">
            <h4>Propriétés générales</h4>
            <p>Type d'élément: {selectedElement.type}</p>
          </div>
        );
    }
  };

  return (
    <div className="properties-panel">
      <div className="panel-header">
        <h3>⚙️ Propriétés</h3>
        {selectedElements.length > 1 && (
          <div className="selection-count">
            {selectedElements.length} éléments sélectionnés
          </div>
        )}
      </div>

      <div className="panel-content">
        {renderElementProperties()}

        {/* Propriétés générales */}
        {selectedElement && (
          <div className="properties-group">
            <h4>Position & Taille</h4>

            <div className="property-row">
              <label>X:</label>
              <input
                type="number"
                value={Math.round(localProperties.x || 0)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'x', parseInt(e.target.value))}
              />
            </div>

            <div className="property-row">
              <label>Y:</label>
              <input
                type="number"
                value={Math.round(localProperties.y || 0)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'y', parseInt(e.target.value))}
              />
            </div>

            <div className="property-row">
              <label>Largeur:</label>
              <input
                type="number"
                value={Math.round(localProperties.width || 100)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'width', parseInt(e.target.value))}
                min="1"
              />
            </div>

            <div className="property-row">
              <label>Hauteur:</label>
              <input
                type="number"
                value={Math.round(localProperties.height || 50)}
                onChange={(e) => handlePropertyChange(selectedElement.id, 'height', parseInt(e.target.value))}
                min="1"
              />
            </div>
          </div>
        )}
      </div>
    </div>
  );
};