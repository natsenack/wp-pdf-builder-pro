import React from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext';
import { BuilderMode } from '../../types/elements';

interface ToolbarProps {
  className?: string;
}

export function Toolbar({ className }: ToolbarProps) {
  const { state, setMode, undo, redo, reset } = useBuilder();

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
      backgroundColor: '#f8f9fa',
      borderBottom: '1px solid #dee2e6',
      flexWrap: 'wrap'
    }}>
      {/* Outils de sélection de mode */}
      <div style={{ display: 'flex', gap: '4px', alignItems: 'center' }}>
        {tools.map((tool) => (
          <button
            key={tool.mode}
            onClick={() => handleModeChange(tool.mode)}
            className={`toolbar-btn ${state.mode === tool.mode ? 'active' : ''}`}
            style={{
              padding: '8px 12px',
              border: '1px solid #ced4da',
              borderRadius: '4px',
              backgroundColor: state.mode === tool.mode ? '#007cba' : '#fff',
              color: state.mode === tool.mode ? '#fff' : '#495057',
              cursor: 'pointer',
              fontSize: '14px',
              display: 'flex',
              alignItems: 'center',
              gap: '6px',
              transition: 'all 0.2s'
            }}
            title={tool.label}
          >
            <span>{tool.icon}</span>
            <span>{tool.label}</span>
          </button>
        ))}
      </div>

      {/* Séparateur */}
      <div style={{ width: '1px', height: '32px', backgroundColor: '#dee2e6', margin: '0 8px' }} />

      {/* Actions Undo/Redo */}
      <div style={{ display: 'flex', gap: '4px', alignItems: 'center' }}>
        <button
          onClick={undo}
          disabled={!state.canUndo}
          style={{
            padding: '8px 12px',
            border: '1px solid #ced4da',
            borderRadius: '4px',
            backgroundColor: state.canUndo ? '#28a745' : '#e9ecef',
            color: state.canUndo ? '#fff' : '#6c757d',
            cursor: state.canUndo ? 'pointer' : 'not-allowed',
            fontSize: '14px',
            display: 'flex',
            alignItems: 'center',
            gap: '6px'
          }}
          title="Annuler"
        >
          <span>↶</span>
          <span>Annuler</span>
        </button>

        <button
          onClick={redo}
          disabled={!state.canRedo}
          style={{
            padding: '8px 12px',
            border: '1px solid #ced4da',
            borderRadius: '4px',
            backgroundColor: state.canRedo ? '#28a745' : '#e9ecef',
            color: state.canRedo ? '#fff' : '#6c757d',
            cursor: state.canRedo ? 'pointer' : 'not-allowed',
            fontSize: '14px',
            display: 'flex',
            alignItems: 'center',
            gap: '6px'
          }}
          title="Rétablir"
        >
          <span>↷</span>
          <span>Rétablir</span>
        </button>

        <button
          onClick={reset}
          style={{
            padding: '8px 12px',
            border: '1px solid #ced4da',
            borderRadius: '4px',
            backgroundColor: '#dc3545',
            color: '#fff',
            cursor: 'pointer',
            fontSize: '14px',
            display: 'flex',
            alignItems: 'center',
            gap: '6px'
          }}
          title="Réinitialiser"
        >
          <span>🔄</span>
          <span>Réinitialiser</span>
        </button>
      </div>
    </div>
  );
}
      backgroundColor: '#f5f5f5',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }}>
      {/* Outils de création */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
        <h4 style={{ margin: '0 0 8px 0', fontSize: '14px', fontWeight: 'bold' }}>
          Outils
        </h4>
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
              gap: '8px',
              minWidth: '120px',
              textAlign: 'left'
            }}
          >
            <span>{tool.icon}</span>
            <span>{tool.label}</span>
          </button>
        ))}
      </div>

      {/* Actions d'édition */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', marginTop: '16px' }}>
        <h4 style={{ margin: '0 0 8px 0', fontSize: '14px', fontWeight: 'bold' }}>
          Actions
        </h4>
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
      </div>

      {/* Informations */}
      <div style={{ marginTop: '16px', fontSize: '12px', color: '#666' }}>
        <div>Éléments: {state.elements.length}</div>
        <div>Sélectionnés: {state.selection.selectedElements.length}</div>
        <div>Mode: {state.mode}</div>
        <div>Zoom: {Math.round(state.canvas.zoom * 100)}%</div>
      </div>
    </div>
  );
}