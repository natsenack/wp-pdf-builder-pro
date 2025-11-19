import React from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { useCanvasSettings } from '../../contexts/CanvasSettingsContext.tsx';
import { BuilderMode } from '../../types/elements';

interface ToolbarProps {
  className?: string;
}

export function Toolbar({ className }: ToolbarProps) {
  const builder = useBuilder();
  const canvasSettings = useCanvasSettings();
  const { state, setMode, undo, redo, reset, toggleGrid, toggleGuides } = builder;

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
    if (toggleGrid && canvasSettings.gridShow) {
      toggleGrid();
    }
  };

  const handleToggleGuides = () => {
    console.log('🔧 Toolbar: handleToggleGuides called');
    console.log('🔧 Toolbar: canvasSettings.guidesEnabled =', canvasSettings.guidesEnabled);
    console.log('🔧 Toolbar: state.template.showGuides =', state.template.showGuides);
    if (toggleGuides && canvasSettings.guidesEnabled) {
      console.log('🔧 Toolbar: calling toggleGuides()');
      toggleGuides();
    } else {
      console.log('🔧 Toolbar: toggleGuides not called - toggleGuides exists:', !!toggleGuides, 'guidesEnabled:', canvasSettings.guidesEnabled);
    }
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
            onClick={handleUndo}
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
            onClick={handleRedo}
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
            onClick={handleReset}
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
            onClick={handleToggleGrid}
            disabled={!canvasSettings.gridShow}
            style={{
              padding: '6px 12px',
              border: '1px solid #ccc',
              borderRadius: '4px',
              backgroundColor: !canvasSettings.gridShow ? '#f0f0f0' : (state.canvas.showGrid ? '#007acc' : '#ffffff'),
              color: !canvasSettings.gridShow ? '#999' : (state.canvas.showGrid ? '#ffffff' : '#000000'),
              cursor: !canvasSettings.gridShow ? 'not-allowed' : 'pointer',
              fontSize: '12px',
              opacity: !canvasSettings.gridShow ? 0.6 : 1
            }}
          >
            {state.canvas.showGrid ? '⬜ Grille ON' : '▦ Grille OFF'}
          </button>
          <button
            onClick={handleToggleGuides}
            disabled={!canvasSettings.guidesEnabled}
            style={{
              padding: '6px 12px',
              border: '1px solid #ccc',
              borderRadius: '4px',
              backgroundColor: !canvasSettings.guidesEnabled ? '#f0f0f0' : (state.template.showGuides ? '#007acc' : '#ffffff'),
              color: !canvasSettings.guidesEnabled ? '#999' : (state.template.showGuides ? '#ffffff' : '#000000'),
              cursor: !canvasSettings.guidesEnabled ? 'not-allowed' : 'pointer',
              fontSize: '12px',
              opacity: !canvasSettings.guidesEnabled ? 0.6 : 1
            }}
          >
            {state.template.showGuides ? '📏 Guides ON' : '📐 Guides OFF'}
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
