import React from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext';
import { useCanvasSettings } from '../../contexts/CanvasSettingsContext';
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
    if (toggleGrid && canvasSettings.gridShow) {
      toggleGrid();
    }
  };

  const handleToggleGuides = () => {
    if (toggleGuides && canvasSettings.guidesEnabled) {
      toggleGuides();
    }
  };

  const handleToggleSnapToGrid = () => {
    // Vérifier que la grille globale est activée avant d'autoriser l'accrochage
    if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled) {
      const newSnapToGrid = !state.canvas.snapToGrid;
      if (setCanvas) {
        setCanvas({ snapToGrid: newSnapToGrid });
      }
    }
  };

  return (
    <div
      className={`pdf-builder-toolbar ${className || ''}`}
    >
      <div style={{
        display: 'flex',
        flexDirection: 'column',
        gap: '12px',
        padding: '16px',
        backgroundColor: '#ffffff',
        border: '1px solid #e1e5e9',
        borderRadius: '8px',
        boxShadow: '0 2px 8px rgba(0, 0, 0, 0.1)',
        maxHeight: '140px',
        width: '100%'
      }}>
        {/* Première ligne : Outils + Actions principales + Informations */}
        <div style={{
          display: 'flex',
          gap: '16px',
          alignItems: 'flex-start',
          flexDirection: 'row',
          minWidth: '220px'
        }}>
          {/* Outils de création */}
          <section style={{
            display: 'flex',
            flexDirection: 'column',
            gap: '8px',
            minWidth: '220px',
            flex: 'none'
          }}>
            <div style={{
              fontSize: '13px',
              fontWeight: '600',
              color: '#374151',
              textTransform: 'uppercase',
              letterSpacing: '0.5px',
              borderLeft: '3px solid #3b82f6',
              paddingLeft: '8px',
              display: 'block'
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
              disabled={!canvasSettings.gridShow}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: !canvasSettings.gridShow ? '#f9fafb' : (state.canvas.showGrid ? '#3b82f6' : '#ffffff'),
                color: !canvasSettings.gridShow ? '#9ca3af' : (state.canvas.showGrid ? '#ffffff' : '#374151'),
                cursor: !canvasSettings.gridShow ? 'not-allowed' : 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                boxShadow: state.canvas.showGrid ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                opacity: !canvasSettings.gridShow ? 0.6 : 1,
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (canvasSettings.gridShow && !state.canvas.showGrid) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (canvasSettings.gridShow && !state.canvas.showGrid) {
                  e.currentTarget.style.backgroundColor = '#ffffff';
                  e.currentTarget.style.borderColor = '#d1d5db';
                }
              }}
            >
              {state.canvas.showGrid ? '⬜ Grille' : '▦ Grille'}
            </button>
            <button
              onClick={handleToggleSnapToGrid}
              disabled={!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled}
              style={{
                padding: '8px 12px',
                border: '1px solid #d1d5db',
                borderRadius: '6px',
                backgroundColor: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? '#f9fafb' : (state.canvas.snapToGrid ? '#3b82f6' : '#ffffff'),
                color: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? '#9ca3af' : (state.canvas.snapToGrid ? '#ffffff' : '#374151'),
                cursor: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? 'not-allowed' : 'pointer',
                fontSize: '13px',
                fontWeight: '500',
                transition: 'all 0.2s ease',
                boxShadow: state.canvas.snapToGrid ? '0 1px 3px rgba(59, 130, 246, 0.3)' : 'none',
                opacity: (!canvasSettings.gridShow || !canvasSettings.gridSnapEnabled) ? 0.6 : 1,
                minWidth: '90px'
              }}
              onMouseEnter={(e) => {
                if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled && !state.canvas.snapToGrid) {
                  e.currentTarget.style.backgroundColor = '#f8fafc';
                  e.currentTarget.style.borderColor = '#9ca3af';
                }
              }}
              onMouseLeave={(e) => {
                if (canvasSettings.gridShow && canvasSettings.gridSnapEnabled && !state.canvas.snapToGrid) {
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

            {/* Zoom - Toujours affiché */}
            <div style={{
              display: 'flex',
              alignItems: 'center',
              gap: '4px',
              padding: '6px 10px',
              backgroundColor: '#f8fafc',
              borderRadius: '6px',
              border: '1px solid #e2e8f0'
            }}>
              <span style={{ fontSize: '12px', color: '#64748b', fontWeight: '500' }}>🔍</span>
              <button
                onClick={() => {
                  // Zoom out
                  const newZoom = Math.max(canvasSettings.zoomMin, state.canvas.zoom - canvasSettings.zoomStep);
                  if (setCanvas) {
                    setCanvas({ zoom: newZoom });
                  }
                }}
                style={{
                  padding: '2px 6px',
                  border: '1px solid #d1d5db',
                  borderRadius: '4px',
                  backgroundColor: '#ffffff',
                  color: '#374151',
                  cursor: 'pointer',
                  fontSize: '12px',
                  fontWeight: '600',
                  minWidth: '24px'
                }}
                title="Zoom arrière"
              >
                ➖
              </button>
              <span style={{
                fontSize: '12px',
                fontWeight: '600',
                color: '#374151',
                minWidth: '40px',
                textAlign: 'center'
              }}>
                {state.canvas.zoom}%
              </span>
              <button
                onClick={() => {
                  // Zoom in
                  const newZoom = Math.min(canvasSettings.zoomMax, state.canvas.zoom + canvasSettings.zoomStep);
                  if (setCanvas) {
                    setCanvas({ zoom: newZoom });
                  }
                }}
                style={{
                  padding: '2px 6px',
                  border: '1px solid #d1d5db',
                  borderRadius: '4px',
                  backgroundColor: '#ffffff',
                  color: '#374151',
                  cursor: 'pointer',
                  fontSize: '12px',
                  fontWeight: '600',
                  minWidth: '24px'
                }}
                title="Zoom avant"
              >
                ➕
              </button>
              <span style={{ fontSize: '10px', color: '#94a3b8', margin: '0 2px' }}>|</span>
              <button
                onClick={() => {
                  // Fit to screen (zoom to fit canvas)
                  if (setCanvas) {
                    const fitZoom = Math.max(canvasSettings.zoomMin, Math.min(100, canvasSettings.zoomMax));
                    setCanvas({ zoom: fitZoom });
                  }
                }}
                style={{
                  padding: '4px 8px',
                  border: '1px solid #d1d5db',
                  borderRadius: '4px',
                  backgroundColor: '#ffffff',
                  color: '#374151',
                  cursor: 'pointer',
                  fontSize: '11px',
                  fontWeight: '500'
                }}
                title="Adapter à l'écran"
              >
                🔄
              </button>
            </div>
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
    </div>
  );
}



