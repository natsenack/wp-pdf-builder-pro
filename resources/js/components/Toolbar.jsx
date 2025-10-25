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
  snapToElements,
  onSnapToElementsChange,
  onUndo,
  onRedo,
  canUndo,
  canRedo,
  settings = {} // Paramètres du backend
}) => {
  const [activeTab, setActiveTab] = React.useState('home');
  
  // Récupérer les paramètres de grille et aimantation depuis le backend
  // Les paramètres sont en snake_case du backend et correspondent aux paramètres généraux
  const isGridEnabled = settings.show_grid !== false;
  const isSnapEnabled = settings.snap_to_grid !== false;
  const areSnapsEnabled = settings.snap_to_elements !== false;

  const textTools = [
    { id: 'select', label: 'Sélection (V)', icon: '👆', shortcut: 'V' },
    { id: 'add-text', label: 'Texte Simple (T)', icon: '📝', shortcut: 'T' },
    { id: 'add-text-title', label: 'Titre (H)', icon: '📄', shortcut: 'H' },
    { id: 'add-text-subtitle', label: 'Sous-titre (S)', icon: '📋', shortcut: 'S' }
  ];

  const shapeTools = [
    { id: 'add-rectangle', label: 'Rectangle (R)', icon: '▭', shortcut: 'R' },
    { id: 'add-circle', label: 'Cercle (C)', icon: '○', shortcut: 'C' },
    { id: 'add-line', label: 'Ligne (L)', icon: '━', shortcut: 'L' },
    { id: 'add-arrow', label: 'Flèche (A)', icon: '➤', shortcut: 'A' },
    { id: 'add-triangle', label: 'Triangle (3)', icon: '△', shortcut: '3' },
    { id: 'add-star', label: 'Étoile (5)', icon: '⭐', shortcut: '5' }
  ];

  const insertTools = [
    { id: 'add-divider', label: 'Séparateur (D)', icon: '⎯', shortcut: 'D' },
    { id: 'add-image', label: 'Image (I)', icon: '🖼️', shortcut: 'I' },
    { id: 'add-dynamic-text', label: 'Texte Dynamique (Y)', icon: '📝', shortcut: 'Y' }
  ];

  const tabs = [
    { id: 'home', label: 'Accueil', icon: '🏠' },
    { id: 'insert', label: 'Insertion', icon: '➕' },
    { id: 'view', label: 'Affichage', icon: '👁️' }
  ];

  return (
    <div className="toolbar ribbon-toolbar">
      {/* Onglets principaux */}
      <div className="toolbar-tabs">
        {tabs.map(tab => (
          <button
            key={tab.id}
            className={`tab-button ${activeTab === tab.id ? 'active' : ''}`}
            onClick={() => setActiveTab(tab.id)}
          >
            <span className="tab-icon">{tab.icon}</span>
            <span className="tab-label">{tab.label}</span>
          </button>
        ))}
      </div>

      {/* Contenu des onglets */}
      <div className="toolbar-content">
        {activeTab === 'home' && (
          <div className="tab-content">
            {/* Groupe Presse-papiers */}
            <div className="toolbar-group">
              <h5>Presse-papiers</h5>
              <div className="group-buttons">
                <button
                  className="edit-button"
                  onClick={onUndo}
                  disabled={!canUndo}
                  title="Annuler (Ctrl+Z)"
                >
                  <div className="button-content">
                    <span className="button-icon">↶</span>
                    <span className="button-text">Annuler</span>
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
                  </div>
                </button>
              </div>
            </div>

            {/* Groupe Outils */}
            <div className="toolbar-group">
              <h5>Outils</h5>
              <div className="group-buttons">
                {textTools.map(tool => (
                  <button
                    key={tool.id}
                    className={`tool-button ${selectedTool === tool.id ? 'active' : ''}`}
                    onClick={() => onToolSelect(tool.id)}
                    title={tool.label}
                  >
                    <div className="tool-content">
                      <span className="tool-icon">{tool.icon}</span>
                      <span className="tool-label">{tool.shortcut}</span>
                    </div>
                  </button>
                ))}
              </div>
            </div>

            {/* Groupe Formes */}
            <div className="toolbar-group">
              <h5>Formes</h5>
              <div className="group-buttons shapes-grid">
                {shapeTools.map(tool => (
                  <button
                    key={tool.id}
                    className={`tool-button ${selectedTool === tool.id ? 'active' : ''}`}
                    onClick={() => onToolSelect(tool.id)}
                    title={tool.label}
                  >
                    <div className="tool-content">
                      <span className="tool-icon">{tool.icon}</span>
                      <span className="tool-label">{tool.shortcut}</span>
                    </div>
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}

        {activeTab === 'insert' && (
          <div className="tab-content">
            {/* Groupe Éléments */}
            <div className="toolbar-group">
              <h5>Éléments</h5>
              <div className="group-buttons">
                {insertTools.map(tool => (
                  <button
                    key={tool.id}
                    className={`tool-button ${selectedTool === tool.id ? 'active' : ''}`}
                    onClick={() => onToolSelect(tool.id)}
                    title={tool.label}
                  >
                    <div className="tool-content">
                      <span className="tool-icon">{tool.icon}</span>
                      <span className="tool-label">{tool.shortcut}</span>
                    </div>
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}

        {activeTab === 'view' && (
          <div className="tab-content">
            {/* Groupe Zoom */}
            <div className="toolbar-group">
              <h5>Zoom</h5>
              <div className="group-buttons">
                <div className="zoom-controls">
                  <button
                    className="zoom-button"
                    onClick={() => onZoomChange(Math.max(0.1, zoom - 0.1))}
                    title="Zoom arrière (Ctrl+-)"
                  >
                    <span className="button-icon">🔍</span>
                    <span className="button-text">-</span>
                  </button>
                  <span className="zoom-value">{Math.round(zoom * 100)}%</span>
                  <button
                    className="zoom-button"
                    onClick={() => onZoomChange(Math.min(3, zoom + 0.1))}
                    title="Zoom avant (Ctrl+=)"
                  >
                    <span className="button-icon">🔍</span>
                    <span className="button-text">+</span>
                  </button>
                </div>
              </div>
            </div>

            {/* Groupe Affichage */}
            <div className="toolbar-group">
              <h5>Affichage</h5>
              <div className="group-buttons">
                <div className="display-options">
                  <label 
                    className={`toggle-label ${!isGridEnabled ? 'disabled' : ''}`}
                    title={!isGridEnabled ? '❌ Grille désactivée dans les paramètres généraux' : 'Afficher/Masquer la grille (G)'}
                    style={!isGridEnabled ? { opacity: 0.5, cursor: 'not-allowed', position: 'relative' } : { position: 'relative' }}
                  >
                    <input
                      type="checkbox"
                      checked={isGridEnabled ? showGrid : false}
                      onChange={(e) => isGridEnabled && onShowGridChange(e.target.checked)}
                      disabled={!isGridEnabled}
                    />
                    <span className="toggle-text">Grille</span>
                    <span className="toggle-shortcut">(G)</span>
                    {!isGridEnabled && <span className="tooltip-hint">❌ Grille désactivée dans les paramètres généraux</span>}
                  </label>
                  <label 
                    className={`toggle-label ${!isSnapEnabled ? 'disabled' : ''}`}
                    title={!isSnapEnabled ? '❌ Aimantation à la grille désactivée dans les paramètres généraux' : 'Aimanter à la grille (X)'}
                    style={!isSnapEnabled ? { opacity: 0.5, cursor: 'not-allowed', position: 'relative' } : { position: 'relative' }}
                  >
                    <input
                      type="checkbox"
                      checked={isSnapEnabled ? snapToGrid : false}
                      onChange={(e) => isSnapEnabled && onSnapToGridChange(e.target.checked)}
                      disabled={!isSnapEnabled}
                    />
                    <span className="toggle-text">Aimantation</span>
                    <span className="toggle-shortcut">(X)</span>
                    {!isSnapEnabled && <span className="tooltip-hint">❌ Aimantation à la grille désactivée dans les paramètres généraux</span>}
                  </label>
                  <label 
                    className={`toggle-label ${!areSnapsEnabled ? 'disabled' : ''}`}
                    title={!areSnapsEnabled ? '❌ Aimantation aux éléments désactivée dans les paramètres généraux' : 'Aimanter aux éléments (Z)'}
                    style={!areSnapsEnabled ? { opacity: 0.5, cursor: 'not-allowed', position: 'relative' } : { position: 'relative' }}
                  >
                    <input
                      type="checkbox"
                      checked={areSnapsEnabled ? snapToElements : false}
                      onChange={(e) => areSnapsEnabled && onSnapToElementsChange(e.target.checked)}
                      disabled={!areSnapsEnabled}
                    />
                    <span className="toggle-text">Aimantation</span>
                    <span className="toggle-shortcut">(Z)</span>
                    {!areSnapsEnabled && <span className="tooltip-hint">❌ Aimantation aux éléments désactivée dans les paramètres généraux</span>}
                  </label>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

