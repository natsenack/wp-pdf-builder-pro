import React from 'react';
import { useSimplePreview } from '../SimplePreviewSystem_v3';

/**
 * Mode Canvas - Version 3.0 ultra-simple
 * Système d'aperçu avec calculs mathématiques parfaits
 */

function CanvasMode() {
  const {
    elements,
    templateData,
    previewData,
    scale,
    zoom,
    isFullscreen,
    actualScale,
    canvasWidth,
    canvasHeight,
    displayWidth,
    displayHeight,
    containerStyle,
    canvasStyle,
    canvasWrapperStyle,
    renderElements
  } = useSimplePreview();

  return (
    <div style={containerStyle} className="canvas-mode-container">
      {/* En-tête d'information */}
      <div style={{
        marginBottom: '20px',
        padding: '12px 20px',
        backgroundColor: 'white',
        borderRadius: '8px',
        border: '1px solid #e5e5e7',
        fontSize: '14px',
        color: '#1d1d1f',
        display: 'flex',
        alignItems: 'center',
        gap: '20px',
        minWidth: '300px',
        justifyContent: 'center'
      }}>
        <span>📄 {canvasWidth} × {canvasHeight} points</span>
        <span>|</span>
        <span>🔍 {Math.round(actualScale * 100)}%</span>
        <span>|</span>
        <span>📦 {elements.length} éléments</span>
        {previewData && Object.keys(previewData).length > 0 && (
          <>
            <span>|</span>
            <span style={{ color: '#34c759' }}>✓ Données injectées</span>
          </>
        )}
      </div>

      {/* Wrapper du canvas pour l'échelle */}
      <div style={canvasWrapperStyle}>
        {/* Canvas principal */}
        <div style={canvasStyle} className="preview-canvas">
          {/* Rendu de tous les éléments avec le système v3.0 */}
          {renderElements()}

          {/* Message si aucun élément */}
          {elements.length === 0 && (
            <div style={{
              position: 'absolute',
              top: '50%',
              left: '50%',
              transform: 'translate(-50%, -50%)',
              textAlign: 'center',
              color: '#8e8e93',
              fontSize: '16px',
              fontWeight: '500'
            }}>
              <div style={{ fontSize: '48px', marginBottom: '16px' }}>📄</div>
              <div>Canvas vide</div>
              <div style={{ fontSize: '14px', marginTop: '8px', fontWeight: '400' }}>
                Ajoutez des éléments dans l'éditeur pour les voir ici
              </div>
            </div>
          )}

          {/* Grille de référence (optionnelle) */}
          {process.env.NODE_ENV === 'development' && (
            <svg
              style={{
                position: 'absolute',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                pointerEvents: 'none',
                opacity: 0.1
              }}
            >
              <defs>
                <pattern
                  id="grid"
                  width="20"
                  height="20"
                  patternUnits="userSpaceOnUse"
                >
                  <path
                    d="M 20 0 L 0 0 0 20"
                    fill="none"
                    stroke="#999"
                    strokeWidth="1"
                  />
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
          )}
        </div>
      </div>

      {/* Informations détaillées en bas */}
      <div style={{
        marginTop: '20px',
        padding: '16px 20px',
        backgroundColor: 'white',
        borderRadius: '8px',
        border: '1px solid #e5e5e7',
        fontSize: '13px',
        color: '#6e6e73',
        maxWidth: '600px',
        textAlign: 'center',
        lineHeight: '1.5'
      }}>
        <div style={{ marginBottom: '8px', fontWeight: '600', color: '#1d1d1f' }}>
          📊 Détails de l'aperçu
        </div>
        <div style={{ display: 'flex', justifyContent: 'center', gap: '20px', flexWrap: 'wrap' }}>
          <span>Format: {templateData.orientation || 'Portrait'}</span>
          <span>Échelle: {Math.round(scale * 100)}%</span>
          <span>Zoom: {Math.round(zoom * 100)}%</span>
          <span>Résolution: {Math.round(canvasWidth * 0.3528)} × {Math.round(canvasHeight * 0.3528)} mm</span>
        </div>
      </div>
    </div>
  );
}

export default CanvasMode;
