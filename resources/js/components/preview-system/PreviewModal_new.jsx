import React from 'react';
import { PreviewProvider, usePreviewContext, PREVIEW_MODES } from '../context/PreviewContext_new';
import CanvasMode from '../modes/CanvasMode_new';

/**
 * Modal d'aperçu - Version 2.0 complètement refaite
 * Interface moderne et intuitive pour l'aperçu des templates
 */

// Contrôles de navigation et d'options
function PreviewControls() {
  const { state, setMode, setScale, setZoom, zoomIn, zoomOut, zoomToFit, toggleFullscreen } = usePreviewContext();
  
  const { mode, scale, zoom, isFullscreen } = state;
  
  const controlsStyle = {
    display: 'flex',
    alignItems: 'center',
    gap: '12px',
    padding: '8px 12px',
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
    borderRadius: '8px',
    border: '1px solid rgba(0, 0, 0, 0.1)',
    backdropFilter: 'blur(10px)',
    fontSize: '14px'
  };
  
  const buttonStyle = {
    padding: '6px 12px',
    border: '1px solid #d1d5db',
    borderRadius: '6px',
    backgroundColor: 'white',
    cursor: 'pointer',
    fontSize: '13px',
    transition: 'all 0.2s ease',
    display: 'flex',
    alignItems: 'center',
    gap: '4px'
  };
  
  const activeButtonStyle = {
    ...buttonStyle,
    backgroundColor: '#3b82f6',
    color: 'white',
    borderColor: '#3b82f6'
  };
  
  return (
    <div style={controlsStyle}>
      {/* Sélecteur de mode */}
      <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
        <span style={{ fontSize: '12px', color: '#6b7280' }}>Mode:</span>
        <select
          value={mode}
          onChange={(e) => setMode(e.target.value)}
          style={{
            padding: '4px 8px',
            border: '1px solid #d1d5db',
            borderRadius: '4px',
            fontSize: '13px'
          }}
        >
          <option value={PREVIEW_MODES.CANVAS}>Canvas</option>
          <option value={PREVIEW_MODES.METABOX}>Métabox</option>
          <option value={PREVIEW_MODES.TABLE}>Tableau</option>
          <option value={PREVIEW_MODES.JSON}>JSON</option>
        </select>
      </div>
      
      {/* Contrôles de zoom */}
      <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
        <button
          onClick={zoomOut}
          style={buttonStyle}
          title="Zoom arrière"
        >
          🔍−
        </button>
        <span style={{ 
          minWidth: '60px', 
          textAlign: 'center', 
          fontSize: '12px',
          fontWeight: '500'
        }}>
          {Math.round(zoom * 100)}%
        </span>
        <button
          onClick={zoomIn}
          style={buttonStyle}
          title="Zoom avant"
        >
          🔍+
        </button>
        <button
          onClick={zoomToFit}
          style={buttonStyle}
          title="Ajuster à la taille"
        >
          📐
        </button>
      </div>
      
      {/* Contrôle d'échelle */}
      <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
        <span style={{ fontSize: '12px', color: '#6b7280' }}>Échelle:</span>
        <input
          type="range"
          min="0.1"
          max="2"
          step="0.1"
          value={scale}
          onChange={(e) => setScale(parseFloat(e.target.value))}
          style={{ width: '80px' }}
        />
        <span style={{ fontSize: '12px', minWidth: '40px' }}>
          {Math.round(scale * 100)}%
        </span>
      </div>
      
      {/* Plein écran */}
      <button
        onClick={toggleFullscreen}
        style={isFullscreen ? activeButtonStyle : buttonStyle}
        title={isFullscreen ? "Quitter le plein écran" : "Plein écran"}
      >
        {isFullscreen ? '🗗' : '🗖'}
      </button>
    </div>
  );
}

// Composant principal de la modal
function PreviewModalContent() {
  const { state, closePreview, computed } = usePreviewContext();
  
  const { isOpen, loading, error, mode, isFullscreen } = state;
  
  if (!isOpen) return null;
  
  // Styles de la modal
  const overlayStyle = {
    position: 'fixed',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.75)',
    backdropFilter: 'blur(4px)',
    zIndex: 10000,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    padding: isFullscreen ? 0 : '20px'
  };
  
  const modalStyle = {
    width: isFullscreen ? '100%' : '95%',
    height: isFullscreen ? '100%' : '90%',
    maxWidth: isFullscreen ? 'none' : '1400px',
    backgroundColor: 'white',
    borderRadius: isFullscreen ? 0 : '12px',
    boxShadow: isFullscreen ? 'none' : '0 25px 50px rgba(0, 0, 0, 0.25)',
    display: 'flex',
    flexDirection: 'column',
    overflow: 'hidden'
  };
  
  const headerStyle = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: '16px 24px',
    borderBottom: '1px solid #e5e7eb',
    backgroundColor: '#f9fafb',
    minHeight: '60px'
  };
  
  const contentStyle = {
    flex: 1,
    display: 'flex',
    flexDirection: 'column',
    overflow: 'hidden',
    position: 'relative'
  };
  
  // Rendu du contenu selon le mode
  const renderModeContent = () => {
    if (loading) {
      return (
        <div style={{
          flex: 1,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '18px',
          color: '#6b7280'
        }}>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: '48px', marginBottom: '16px' }}>⏳</div>
            <div>Chargement de l'aperçu...</div>
          </div>
        </div>
      );
    }
    
    if (error) {
      return (
        <div style={{
          flex: 1,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '16px',
          color: '#dc2626'
        }}>
          <div style={{ textAlign: 'center', padding: '40px' }}>
            <div style={{ fontSize: '48px', marginBottom: '16px' }}>❌</div>
            <div style={{ fontWeight: '600', marginBottom: '8px' }}>Erreur de chargement</div>
            <div style={{ color: '#6b7280' }}>{error}</div>
          </div>
        </div>
      );
    }
    
    switch (mode) {
      case PREVIEW_MODES.CANVAS:
        return <CanvasMode />;
        
      case PREVIEW_MODES.METABOX:
        return (
          <div style={{ padding: '20px', fontSize: '16px', textAlign: 'center' }}>
            🚧 Mode Métabox en cours de développement
          </div>
        );
        
      case PREVIEW_MODES.TABLE:
        return (
          <div style={{ padding: '20px', fontSize: '16px', textAlign: 'center' }}>
            📊 Mode Tableau en cours de développement
          </div>
        );
        
      case PREVIEW_MODES.JSON:
        return (
          <div style={{ padding: '20px' }}>
            <pre style={{
              backgroundColor: '#f3f4f6',
              padding: '20px',
              borderRadius: '8px',
              fontSize: '14px',
              overflow: 'auto',
              maxHeight: '400px'
            }}>
              {JSON.stringify(state, null, 2)}
            </pre>
          </div>
        );
        
      default:
        return (
          <div style={{ padding: '20px', fontSize: '16px', textAlign: 'center' }}>
            ❓ Mode d'aperçu non reconnu: {mode}
          </div>
        );
    }
  };
  
  return (
    <div style={overlayStyle} onClick={closePreview}>
      <div style={modalStyle} onClick={(e) => e.stopPropagation()}>
        {/* En-tête */}
        <div style={headerStyle}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <h3 style={{
              margin: 0,
              fontSize: '18px',
              fontWeight: '600',
              color: '#1f2937',
              display: 'flex',
              alignItems: 'center',
              gap: '8px'
            }}>
              📄 Aperçu PDF Builder Pro
            </h3>
            {computed.isEmpty && (
              <span style={{
                fontSize: '12px',
                color: '#f59e0b',
                backgroundColor: '#fef3c7',
                padding: '4px 8px',
                borderRadius: '4px'
              }}>
                Aucun élément
              </span>
            )}
          </div>
          
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <PreviewControls />
            <button
              onClick={closePreview}
              style={{
                padding: '8px',
                border: 'none',
                borderRadius: '6px',
                backgroundColor: '#ef4444',
                color: 'white',
                cursor: 'pointer',
                fontSize: '16px',
                width: '36px',
                height: '36px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                transition: 'all 0.2s ease'
              }}
              title="Fermer l'aperçu"
              onMouseOver={(e) => e.target.style.backgroundColor = '#dc2626'}
              onMouseOut={(e) => e.target.style.backgroundColor = '#ef4444'}
            >
              ✕
            </button>
          </div>
        </div>
        
        {/* Contenu principal */}
        <div style={contentStyle}>
          {renderModeContent()}
        </div>
        
        {/* Pied de page avec informations */}
        {!loading && !error && (
          <div style={{
            padding: '12px 24px',
            borderTop: '1px solid #e5e7eb',
            backgroundColor: '#f9fafb',
            fontSize: '13px',
            color: '#6b7280',
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center'
          }}>
            <div>
              Mode: <strong>{mode}</strong> • Éléments: <strong>{state.elements.length}</strong>
            </div>
            <div>
              PDF Builder Pro v{state.version}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

// Composant wrapper avec Provider
export function PreviewModal({ isOpen, onClose, elements = [], templateData = {}, previewData = {} }) {
  return (
    <PreviewProvider>
      <PreviewModalWrapper 
        isOpen={isOpen}
        onClose={onClose}
        elements={elements}
        templateData={templateData}
        previewData={previewData}
      />
    </PreviewProvider>
  );
}

// Composant interne qui utilise le contexte
function PreviewModalWrapper({ isOpen, onClose, elements, templateData, previewData }) {
  const { openPreview, closePreview } = usePreviewContext();
  
  // Synchroniser l'état avec les props
  React.useEffect(() => {
    if (isOpen) {
      openPreview({
        mode: PREVIEW_MODES.CANVAS,
        elements,
        templateData,
        previewData
      });
    } else {
      closePreview();
    }
  }, [isOpen, elements, templateData, previewData, openPreview, closePreview]);
  
  // Synchroniser la fermeture avec le parent
  React.useEffect(() => {
    return () => {
      if (onClose) {
        onClose();
      }
    };
  }, [onClose]);
  
  return <PreviewModalContent />;
}

export default PreviewModal;