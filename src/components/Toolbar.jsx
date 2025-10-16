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
    { id: 'select', label: 'Sélection (V)', icon: '👆', shortcut: 'V' },
    { id: 'add-text', label: 'Texte Simple (T)', icon: '📝', shortcut: 'T' },
    { id: 'add-text-title', label: 'Titre (H)', icon: '📄', shortcut: 'H' },
    { id: 'add-text-subtitle', label: 'Sous-titre (S)', icon: '📋', shortcut: 'S' },
    { id: 'add-rectangle', label: 'Rectangle (R)', icon: '▭', shortcut: 'R' },
    { id: 'add-circle', label: 'Cercle (C)', icon: '○', shortcut: 'C' },
    { id: 'add-line', label: 'Ligne (L)', icon: '━', shortcut: 'L' },
    { id: 'add-arrow', label: 'Flèche (A)', icon: '➤', shortcut: 'A' },
    { id: 'add-triangle', label: 'Triangle (3)', icon: '△', shortcut: '3' },
    { id: 'add-star', label: 'Étoile (5)', icon: '⭐', shortcut: '5' },
    { id: 'add-divider', label: 'Séparateur (D)', icon: '⎯', shortcut: 'D' },
    { id: 'add-image', label: 'Image (I)', icon: '🖼️', shortcut: 'I' }
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
              <div className="tool-content">
                <span className="tool-icon">{tool.icon}</span>
                <span className="tool-shortcut">{tool.shortcut}</span>
              </div>
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
            <div className="button-content">
              <span className="button-icon">↶</span>
              <span className="button-text">Annuler</span>
              <span className="button-shortcut">Ctrl+Z</span>
            </div>
          </button>
          <button
            className="edit-button"
            onClick={onRedo}
            disabled={!canRedo}
            title="Rétablir (Ctrl+Y)"
          >
            <div className="button-content">
              <span className="button-icon">↷</span>
              <span className="button-text">Rétablir</span>
              <span className="button-shortcut">Ctrl+Y</span>
            </div>
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
                title="Zoom arrière (Ctrl+-)"
              >
                <div className="button-content">
                  <span className="button-icon">🔍</span>
                  <span className="button-text">-</span>
                  <span className="button-shortcut">Ctrl+-</span>
                </div>
              </button>
              <span className="zoom-value">{Math.round(zoom * 100)}%</span>
              <button
                className="zoom-button"
                onClick={() => onZoomChange(Math.min(3, zoom + 0.1))}
                title="Zoom avant (Ctrl+=)"
              >
                <div className="button-content">
                  <span className="button-icon">🔍</span>
                  <span className="button-text">+</span>
                  <span className="button-shortcut">Ctrl+=</span>
                </div>
              </button>
            </div>
          </div>

          <div className="canvas-controls-column">
            <div className="control-group">
              <label>
                <input
                  type="checkbox"
                  checked={showGrid}
                  onChange={(e) => onShowGridChange(e.target.checked)}
                />
                Grille
                <span className="control-shortcut">(G)</span>
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
                <span className="control-shortcut">(M)</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      {/* Section d'aide avec raccourcis */}
      <div className="toolbar-section">
        <h4>Raccourcis</h4>
        <div className="shortcuts-help">
          <div className="shortcut-group">
            <span className="shortcut-label">Outils:</span>
            <span className="shortcut-keys">V T H S R C L A 3 5 D I</span>
          </div>
          <div className="shortcut-group">
            <span className="shortcut-label">Édition:</span>
            <span className="shortcut-keys">Ctrl+Z/Y</span>
          </div>
          <div className="shortcut-group">
            <span className="shortcut-label">Zoom:</span>
            <span className="shortcut-keys">Ctrl+/- Roulette</span>
          </div>
          <div className="shortcut-group">
            <span className="shortcut-label">Affichage:</span>
            <span className="shortcut-keys">G M</span>
          </div>
        </div>
      </div>
    </div>
  );
};

