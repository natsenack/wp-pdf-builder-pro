import React from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { useCanvasSettings } from '../../contexts/CanvasSettingsContext.tsx';
import { BuilderMode } from '../../types/elements';

interface ToolbarProps {
  className?: string;
}

export function Toolbar({ className }: ToolbarProps) {
  const { state, dispatch, setMode, undo, redo, reset, toggleGrid, toggleGuides, setCanvas } = useBuilder();
  const canvasSettings = useCanvasSettings();

  // Vérifications de sécurité
  if (!state) {
    return <div style={{ padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }}>
      Erreur: État Builder non disponible
    </div>;
  }

  if (!state.history) {
    return <div style={{ padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }}>
      Erreur: Historique non disponible
    </div>;
  }

  if (!state.canvas) {
    return <div style={{ padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }}>
      Erreur: Canvas non disponible
    </div>;
  }

  const tools: { mode: BuilderMode; label: string; icon: string }[] = [
    { mode: 'select', label: 'Sélection', icon: '🖱️' },
    { mode: 'rectangle', label: 'Rectangle', icon: '▭' },
    { mode: 'circle', label: 'Cercle', icon: '○' },
    { mode: 'text', label: 'Texte', icon: 'T' },
    { mode: 'line', label: 'Ligne', icon: '━' },
    { mode: 'image', label: 'Image', icon: '🖼️' },
  ];

  const handleModeChange = (mode: BuilderMode) => {
    if (setMode) {
      setMode(mode);
    }
  };

  const handleUndo = () => {
    if (undo) {
      undo();
    }
  };

  const handleRedo = () => {
    if (redo) {
      redo();
    }
  };

  const handleReset = () => {
    if (reset) {
      reset();
    }
  };

  const handleToggleGrid = () => {
    if (toggleGrid) {
      toggleGrid();
    }
  };

  const handleToggleGuides = () => {
    if (toggleGuides && canvasSettings.guidesEnabled) {
      toggleGuides();
    }
  };

  const handleToggleSnapToGrid = () => {
    // Toggle snap to grid via canvas settings
    const newSnapToGrid = !state.canvas.snapToGrid;
    // Use setCanvas to update canvas state directly
    if (setCanvas) {
      setCanvas({ snapToGrid: newSnapToGrid });
    }
  };

  return (
    <div className={`pdf-builder-toolbar ${className || ''}`} style={{
      display: 'flex',
      flexDirection: 'column',
      gap: '8px',
      padding: '12px',
      backgroundColor: '#f5f5f5',
      borderRadius: '4px',
      maxHeight: '120px' // Limite à 2 lignes max
    }}>
      {/* Première ligne : Outils + Actions principales + Informations */}
      <div style={{ display: 'flex', gap: '8px', alignItems: 'flex-start' }}>
        {/* Outils de création */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', minWidth: '200px' }}>
          <h4 style={{ margin: '0 0 8px 0', fontSize: '14px', fontWeight: 'bold' }}>
            Outils
          </h4>
          <div style={{
            display: 'flex',
            flexWrap: 'wrap',
            gap: '4px',
            maxHeight: '80px', // Force le wrap sur 2 lignes max
            alignContent: 'flex-start'
          }}>
            {tools.map(tool => (
              <button
                key={tool.mode}
                onClick={() => handleModeChange(tool.mode)}
                style={{
                  padding: '8px 12px',
                  border: '1px solid #ccc',
                  borderRadius: '4px',
                  backgroundColor: state.mode === tool.mode ? '#007acc' : '#ffffff',
                  color: state.mode === tool.mode ? '#ffffff' : '#000000',
                  cursor: 'pointer',
                  fontSize: '14px',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px',
                  minWidth: '100px',
                  textAlign: 'left'
                }}
              >
                <span>{tool.icon}</span>
                <span>{tool.label}</span>
              </button>
            ))}
          </div>
        </div>

        {/* Actions principales */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', flex: 1 }}>
          <h4 style={{ margin: '0 0 8px 0', fontSize: '14px', fontWeight: 'bold' }}>
            Actions
          </h4>
          <div style={{
            display: 'flex',
            flexWrap: 'wrap',
            gap: '4px',
            maxHeight: '80px', // Force le wrap sur 2 lignes max
            alignContent: 'flex-start'
          }}>
            {/* Historique */}
            <button
              onClick={handleUndo}
              disabled={!state.history.canUndo}
              style={{
                padding: '8px 12px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                backgroundColor: state.history.canUndo ? '#ffffff' : '#f0f0f0',
                color: state.history.canUndo ? '#000000' : '#999999',
                cursor: state.history.canUndo ? 'pointer' : 'not-allowed',
                fontSize: '14px',
                minWidth: '100px',
                textAlign: 'left'
              }}
            >
              ↶ Annuler
            </button>
            <button
              onClick={handleRedo}
              disabled={!state.history.canRedo}
              style={{
                padding: '8px 12px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                backgroundColor: state.history.canRedo ? '#ffffff' : '#f0f0f0',
                color: state.history.canRedo ? '#000000' : '#999999',
                cursor: state.history.canRedo ? 'pointer' : 'not-allowed',
                fontSize: '14px',
                minWidth: '100px',
                textAlign: 'left'
              }}
            >
              ↷ Rétablir
            </button>

            {/* Grille */}
            <button
              onClick={handleToggleGrid}
              style={{
                padding: '8px 12px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                backgroundColor: state.canvas.showGrid ? '#007acc' : '#ffffff',
                color: state.canvas.showGrid ? '#ffffff' : '#000000',
                cursor: 'pointer',
                fontSize: '14px',
                minWidth: '100px',
                textAlign: 'left'
              }}
            >
              {state.canvas.showGrid ? '⬜ Grille' : '▦ Grille'}
            </button>
            <button
              onClick={handleToggleSnapToGrid}
              style={{
                padding: '8px 12px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                backgroundColor: state.canvas.snapToGrid ? '#007acc' : '#ffffff',
                color: state.canvas.snapToGrid ? '#ffffff' : '#000000',
                cursor: 'pointer',
                fontSize: '14px',
                minWidth: '100px',
                textAlign: 'left'
              }}
            >
              🧲 Snap
            </button>
            <button
              onClick={handleToggleGuides}
              disabled={!canvasSettings.guidesEnabled}
              style={{
                padding: '8px 12px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                backgroundColor: !canvasSettings.guidesEnabled ? '#f0f0f0' : (state.template.showGuides ? '#007acc' : '#ffffff'),
                color: !canvasSettings.guidesEnabled ? '#999' : (state.template.showGuides ? '#ffffff' : '#000000'),
                cursor: !canvasSettings.guidesEnabled ? 'not-allowed' : 'pointer',
                fontSize: '14px',
                minWidth: '100px',
                textAlign: 'left',
                opacity: !canvasSettings.guidesEnabled ? 0.6 : 1
              }}
            >
              {state.template.showGuides ? '📏 Guides' : '📐 Guides'}
            </button>

            {/* Réinitialiser */}
            <button
              onClick={handleReset}
              style={{
                padding: '8px 12px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                backgroundColor: '#ffffff',
                color: '#000000',
                cursor: 'pointer',
                fontSize: '14px',
                minWidth: '100px',
                textAlign: 'left'
              }}
            >
              🔄 Reset
            </button>
          </div>
        </div>

        {/* Informations - intégrées dans la première ligne */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', minWidth: '150px', marginLeft: 'auto' }}>
          <h4 style={{ margin: '0 0 8px 0', fontSize: '14px', fontWeight: 'bold' }}>
            Infos
          </h4>
          <div style={{ fontSize: '12px', color: '#666', display: 'flex', flexDirection: 'column', gap: '2px' }}>
            <div>Éléments: {state.elements.length}</div>
            <div>Sélectionnés: {state.selection.selectedElements.length}</div>
            <div>Mode: {state.mode}</div>
            <div>Zoom: {state.canvas.zoom}%</div>
          </div>
        </div>
      </div>
    </div>
  );
}
