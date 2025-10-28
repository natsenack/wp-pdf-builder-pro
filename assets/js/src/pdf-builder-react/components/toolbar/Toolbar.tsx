import React from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { BuilderMode } from '../../types/elements';

interface ToolbarProps {
  className?: string;
}

export function Toolbar({ className }: ToolbarProps) {
  let state, setMode, undo, redo, reset, toggleGrid;
  try {
    const builder = useBuilder();
    state = builder.state;
    setMode = builder.setMode;
    undo = builder.undo;
    redo = builder.redo;
    reset = builder.reset;
    toggleGrid = builder.toggleGrid;
    console.log('Builder context available:', !!state); // Debug log
  } catch (error) {
    console.error('Error accessing builder context:', error);
    return <div style={{ padding: '20px', backgroundColor: '#ffcccc', border: '1px solid #ff0000' }}>
      Erreur: Contexte Builder non disponible
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
    setMode(mode);
  };

  return (
    <div className={`pdf-builder-toolbar ${className || ''}`} style={{
      display: 'flex',
      flexDirection: 'row',
      gap: '8px',
      padding: '12px',
      backgroundColor: '#f5f5f5',
      borderRadius: '4px',
      flexWrap: 'wrap',
      minHeight: '60px'
    }}>
      {/* Outils de création */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', minWidth: '200px' }}>
        <h4 style={{ margin: '0 0 8px 0', fontSize: '14px', fontWeight: 'bold' }}>
          Outils
        </h4>
        <div style={{ display: 'flex', gap: '4px', flexWrap: 'wrap' }}>
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

      {/* Actions d'édition */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', minWidth: '150px' }}>
        <h4 style={{ margin: '0 0 8px 0', fontSize: '14px', fontWeight: 'bold' }}>
          Actions
        </h4>
        <div style={{ display: 'flex', gap: '4px' }}>
          <button
            onClick={undo}
            disabled={!state.history.canUndo}
            style={{
              padding: '6px 12px',
              border: '1px solid #ccc',
              borderRadius: '4px',
              backgroundColor: state.history.canUndo ? '#ffffff' : '#f0f0f0',
              color: state.history.canUndo ? '#000000' : '#999999',
              cursor: state.history.canUndo ? 'pointer' : 'not-allowed',
              fontSize: '12px'
            }}
          >
            ↶ Annuler
          </button>
          <button
            onClick={redo}
            disabled={!state.history.canRedo}
            style={{
              padding: '6px 12px',
              border: '1px solid #ccc',
              borderRadius: '4px',
              backgroundColor: state.history.canRedo ? '#ffffff' : '#f0f0f0',
              color: state.history.canRedo ? '#000000' : '#999999',
              cursor: state.history.canRedo ? 'pointer' : 'not-allowed',
              fontSize: '12px'
            }}
          >
            ↷ Rétablir
          </button>
          <button
            onClick={reset}
            style={{
              padding: '6px 12px',
              border: '1px solid #ccc',
              borderRadius: '4px',
              backgroundColor: '#ffffff',
              color: '#000000',
              cursor: 'pointer',
              fontSize: '12px'
            }}
          >
            🔄 Réinitialiser
          </button>
          <button
            onClick={toggleGrid}
            style={{
              padding: '6px 12px',
              border: '1px solid #ccc',
              borderRadius: '4px',
              backgroundColor: state.canvas.showGrid ? '#007acc' : '#ffffff',
              color: state.canvas.showGrid ? '#ffffff' : '#000000',
              cursor: 'pointer',
              fontSize: '12px'
            }}
          >
            {state.canvas.showGrid ? '⬜ Grille ON' : '▦ Grille OFF'}
          </button>
        </div>
      </div>

      {/* Informations */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', marginLeft: 'auto', minWidth: '150px' }}>
        <h4 style={{ margin: '0 0 8px 0', fontSize: '14px', fontWeight: 'bold' }}>
          Informations
        </h4>
        <div style={{ fontSize: '12px', color: '#666', display: 'flex', flexDirection: 'column', gap: '2px' }}>
          <div>Éléments: {state.elements.length}</div>
          <div>Sélectionnés: {state.selection.selectedElements.length}</div>
          <div>Mode: {state.mode}</div>
          <div>Zoom: {Math.round(state.canvas.zoom * 100)}%</div>
        </div>
      </div>
    </div>
  );
}