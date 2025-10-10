import React from 'react';

export const Toolbar = ({
  selectedTool,
  onToolSelect,
  zoom,
  onZoomChange,
  showGrid,
  onShowGridChange,
  snapToGrid,
  onSnapToGridChange,
  onUndo,
  onRedo,
  canUndo,
  canRedo
}) => {
  const tools = [
    { id: 'select', label: 'Sélection', icon: '👆' },
    { id: 'add-text', label: 'Texte Simple', icon: '📝' },
    { id: 'add-text-title', label: 'Titre', icon: '�' },
    { id: 'add-text-subtitle', label: 'Sous-titre', icon: '�' },
    { id: 'add-rectangle', label: 'Rectangle', icon: '▭' },
    { id: 'add-circle', label: 'Cercle', icon: '○' },
    { id: 'add-line', label: 'Ligne', icon: '━' },
    { id: 'add-arrow', label: 'Flèche', icon: '➤' },
    { id: 'add-triangle', label: 'Triangle', icon: '△' },
    { id: 'add-star', label: 'Étoile', icon: '⭐' },
    { id: 'add-divider', label: 'Séparateur', icon: '⎯' },
    { id: 'add-image', label: 'Image', icon: '🖼️' }
  ];

  return (
    <div className="toolbar">
      {/* Outils principaux */}
      <div className="toolbar-section">
        <h4>Outils</h4>
        <div className="tool-buttons">
          {tools.map(tool => (
            <button
              key={tool.id}
              className={`tool-button ${selectedTool === tool.id ? 'active' : ''}`}
              onClick={() => onToolSelect(tool.id)}
              title={tool.label}
            >
              <span className="tool-icon">{tool.icon}</span>
              <span className="tool-label">{tool.label}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Contrôles d'édition */}
      <div className="toolbar-section">
        <h4>Édition</h4>
        <div className="edit-buttons">
          <button
            className="edit-button"
            onClick={onUndo}
            disabled={!canUndo}
            title="Annuler (Ctrl+Z)"
          >
            ↶ Annuler
          </button>
          <button
            className="edit-button"
            onClick={onRedo}
            disabled={!canRedo}
            title="Rétablir (Ctrl+Y)"
          >
            ↷ Rétablir
          </button>
        </div>
      </div>

      {/* Contrôles d'affichage */}
      <div className="toolbar-section">
        <h4>Affichage</h4>
        <div className="display-controls">
          <div className="control-group">
            <label>Zoom:</label>
            <div className="zoom-controls">
              <button
                className="zoom-button"
                onClick={() => onZoomChange(Math.max(0.1, zoom - 0.1))}
                title="Zoom arrière"
              >
                🔍-
              </button>
              <span className="zoom-value">{Math.round(zoom * 100)}%</span>
              <button
                className="zoom-button"
                onClick={() => onZoomChange(Math.min(3, zoom + 0.1))}
                title="Zoom avant"
              >
                🔍+
              </button>
            </div>
          </div>

          <div className="control-group">
            <label>
              <input
                type="checkbox"
                checked={showGrid}
                onChange={(e) => onShowGridChange(e.target.checked)}
              />
              Grille
            </label>
          </div>

          <div className="control-group">
            <label>
              <input
                type="checkbox"
                checked={snapToGrid}
                onChange={(e) => onSnapToGridChange(e.target.checked)}
              />
              Aimantation
            </label>
          </div>
        </div>
      </div>
    </div>
  );
};