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
      gap: '12px',
      padding: '16px',
      backgroundColor: '#ffffff',
      border: '1px solid #e1e5e9',
      borderRadius: '8px',
      boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
      maxHeight: '140px'
    }}>
      {/* Première ligne : Outils + Actions principales + Informations */}
      <div style={{ display: 'flex', gap: '16px', alignItems: 'flex-start' }}>
        {/* Outils de création */}
        <section style={{ display: 'flex', flexDirection: 'column', gap: '8px', minWidth: '220px' }}>
          <div style={{
            fontSize: '13px',
            fontWeight: '600',
            color: '#374151',
            textTransform: 'uppercase',
            letterSpacing: '0.5px',
            borderLeft: '3px solid #3b82f6',
            paddingLeft: '8px'
          }}>
            Outils
          </div>
          <div style={{
            display: 'flex',
            flexWrap: 'wrap',
            gap: '6px',
            maxHeight: '80px',
            alignContent: 'flex-start'
          }}>
            {tools.map(tool => (
              <button
                key={tool.mode}
                onClick={() => handleModeChange(tool.mode)}
                style={{
                  padding: '8px 12px',
                  border: '1px solid #d1d5db',
                  borderRadius: '6px',
                  backgroundColor: state.mode === tool.mode ? '#3b82f6' : '#ffffff',
                  color: state.mode === tool.mode ? '#ffffff' : '#374151',
                  cursor: 'pointer',
                  fontSize: '13px',
                  fontWeight: '500',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px',
                  transition: 'all 0.2s ease',
                  boxShadow: state.mode === tool.mode ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                  minWidth: '90px',
                  justifyContent: 'center'
                }}
                onMouseEnter={(e) => {
                  if (state.mode !== tool.mode) {
                    e.currentTarget.style.backgroundColor = '#f8fafc';
                    e.currentTarget.style.borderColor = '#9ca3af';
                  }
                }}
                onMouseLeave={(e) => {
                  if (state.mode !== tool.mode) {
                    e.currentTarget.style.backgroundColor = '#ffffff';
                    e.currentTarget.style.borderColor = '#d1d5db';
                  }
                }}
              >
                <span style={{ fontSize: '14px' }}>{tool.icon}</span>
                <span>{tool.label}</span>
              </button>
            ))}
          </div>
        </section>

        {/* Actions principales */}
        <section style={{ display: 'flex', flexDirection: 'column', gap: '8px', flex: 1 }}>
          <div style={{
            fontSize: '13px',
            fontWeight: '600',
            color: '#374151',
            textTransform: 'uppercase',
            letterSpacing: '0.5px',
            borderLeft: '3px solid #10b981',
            paddingLeft: '8px'
          }}>
            Actions
          </div>
          <div style={{
            display: 'flex',
            flexWrap: 'wrap',
            gap: '6px',
            maxHeight: '80px',
            alignContent: 'flex-start'
          }}>
            {/* Historique */}
            <button
              onClick={handleUndo}
              disabled={!state.history.canUndo}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: state.history.canUndo ? '#ffffff' : '#f9fafb',
                color: state.history.canUndo ? '#374151' : '#9ca3af',
                cursor: state.history.canUndo ? 'pointer' : 'not-allowed',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (state.history.canUndo) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (state.history.canUndo) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              ↶ Annuler
            </button>
            <button
              onClick={handleRedo}
              disabled={!state.history.canRedo}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: state.history.canRedo ? '#ffffff' : '#f9fafb',
                color: state.history.canRedo ? '#374151' : '#9ca3af',
                cursor: state.history.canRedo ? 'pointer' : 'not-allowed',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (state.history.canRedo) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (state.history.canRedo) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              ↷ Rétablir
            </button>

            {/* Grille */}
            <button
              onClick={handleToggleGrid}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: state.canvas.showGrid ? '#3b82f6' : '#ffffff',
                color: state.canvas.showGrid ? '#ffffff' : '#374151',
                cursor: 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                boxShadow: state.canvas.showGrid ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (!state.canvas.showGrid) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (!state.canvas.showGrid) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              {state.canvas.showGrid ? '⬜ Grille' : '▦ Grille'}
            </button>
            <button
              onClick={handleToggleSnapToGrid}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: state.canvas.snapToGrid ? '#3b82f6' : '#ffffff',
                color: state.canvas.snapToGrid ? '#ffffff' : '#374151',
                cursor: 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                boxShadow: state.canvas.snapToGrid ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (!state.canvas.snapToGrid) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (!state.canvas.snapToGrid) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              🧲 Snap
            </button>
            <button
              onClick={handleToggleGuides}
              disabled={!canvasSettings.guidesEnabled}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: !canvasSettings.guidesEnabled ? '#f9fafb' : (state.template.showGuides ? '#3b82f6' : '#ffffff'),
                color: !canvasSettings.guidesEnabled ? '#9ca3af' : (state.template.showGuides ? '#ffffff' : '#374151'),
                cursor: !canvasSettings.guidesEnabled ? 'not-allowed' : 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                boxShadow: state.template.showGuides ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                opacity: !canvasSettings.guidesEnabled ? 0.6 : 1,
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (canvasSettings.guidesEnabled && !state.template.showGuides) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (canvasSettings.guidesEnabled && !state.template.showGuides) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              {state.template.showGuides ? '📏 Guides' : '📐 Guides'}
            </button>

            {/* Réinitialiser */}
            <button
              onClick={handleReset}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: '#ffffff',
                color: '#374151',
                cursor: 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.backgroundColor = '#fef2f2';
                e.currentTarget.style.borderColor = '#fca5a5';
                e.currentTarget.style.color = '#dc2626';
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.backgroundColor = '#ffffff';
                e.currentTarget.style.borderColor = '#d1d5db';
                e.currentTarget.style.color = '#374151';
              }}
            >
              🔄 Reset
            </button>
          </div>
        </section>

        {/* Informations - intégrées dans la première ligne */}
        <section style={{ display: 'flex', flexDirection: 'column', gap: '6px', minWidth: '160px', marginLeft: 'auto' }}>
          <div style={{
            fontSize: '13px',
            fontWeight: '600',
            color: '#374151',
            textTransform: 'uppercase',
            letterSpacing: '0.5px',
            borderLeft: '3px solid #f59e0b',
            paddingLeft: '8px'
          }}>
            Infos
          </div>
          <div style={{
            fontSize: '12px',
            color: '#6b7280',
            display: 'flex',
            flexDirection: 'column',
            gap: '2px',
            backgroundColor: '#f9fafb',
            padding: '6px',
            borderRadius: '6px',
            border: '1px solid #e5e7eb'
          }}>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>Éléments:</span>
              <span style={{ fontWeight: '600', color: '#374151' }}>{state.elements.length}</span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>Sélection:</span>
              <span style={{ fontWeight: '600', color: '#374151' }}>{state.selection.selectedElements.length}</span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>Mode:</span>
              <span style={{ fontWeight: '600', color: '#374151' }}>{state.mode}</span>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <span>Zoom:</span>
              <span style={{ fontWeight: '600', color: '#374151' }}>{state.canvas.zoom}%</span>
            </div>
          </div>
        </section>
      </div>
    </div>
  );
}
