import React, { useMemo } from 'react';
import { usePreviewContext } from '../context/PreviewContext';
import ElementRenderer from '../renderers/ElementRenderer';

/**
 * CanvasMode - Mode d'aperçu canvas utilisant le système principal
 * Rend les éléments avec leurs propriétés et outils
 */
function CanvasMode() {
  const {
    state: { loading, error, data: previewData, config },
    actions: { clearPreview }
  } = usePreviewContext();

  // Extraire les éléments et les données de template depuis la config
  const { elements = [], templateData = {} } = config;

  // Calculer les dimensions et l'échelle optimales
  const canvasConfig = useMemo(() => {
    const pageWidth = 595; // A4 width in points
    const pageHeight = 842; // A4 height in points
    const containerWidth = 800; // Largeur max du conteneur
    const scale = Math.min(1, containerWidth / pageWidth);

    return {
      pageWidth,
      pageHeight,
      containerWidth,
      scale,
      displayWidth: pageWidth * scale,
      displayHeight: pageHeight * scale
    };
  }, []);

  if (loading) {
    return (
      <div className="canvas-mode-loading">
        <div className="spinner">Chargement...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="canvas-mode-error">
        <p>Erreur: {error}</p>
        <button onClick={clearPreview}>Réessayer</button>
      </div>
    );
  }

  return (
    <div className="canvas-mode">
      <div className="canvas-container" style={{
        width: `${canvasConfig.containerWidth}px`,
        margin: '0 auto',
        border: '1px solid #e0e0e0',
        borderRadius: '8px',
        overflow: 'hidden',
        backgroundColor: '#f8f9fa'
      }}>
        {/* En-tête avec informations */}
        <div style={{
          padding: '12px 20px',
          backgroundColor: 'white',
          borderBottom: '1px solid #e0e0e0',
          fontSize: '14px',
          color: '#1d1d1f',
          display: 'flex',
          alignItems: 'center',
          gap: '20px'
        }}>
          <span>📄 {Math.round(canvasConfig.pageWidth)} × {Math.round(canvasConfig.pageHeight)} points</span>
          <span>|</span>
          <span>🔍 {Math.round(canvasConfig.scale * 100)}%</span>
          <span>|</span>
          <span>📦 {elements && elements.length ? elements.length : 0} éléments</span>
        </div>

        {/* Zone de rendu */}
        <div style={{
          padding: '20px',
          backgroundColor: '#f8f9fa',
          minHeight: `${canvasConfig.displayHeight + 40}px`
        }}>
          <div style={{
            width: `${canvasConfig.displayWidth}px`,
            height: `${canvasConfig.displayHeight}px`,
            backgroundColor: 'white',
            boxShadow: '0 4px 12px rgba(0,0,0,0.1)',
            margin: '0 auto',
            position: 'relative',
            overflow: 'hidden'
          }}>
            {elements && elements.length > 0 ? (
              <div style={{
                transform: `scale(${canvasConfig.scale})`,
                transformOrigin: 'top left',
                width: `${canvasConfig.pageWidth}px`,
                height: `${canvasConfig.pageHeight}px`,
                position: 'relative'
              }}>
                {elements.map((element, index) => (
                  <ElementRenderer
                    key={`${element.type}-${index}`}
                    element={element}
                    scale={1} // Échelle déjà appliquée au conteneur
                    templateData={templateData}
                    interactive={false}
                  />
                ))}
              </div>
            ) : (
              <div style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                height: '100%',
                color: '#6b7280',
                fontSize: '16px'
              }}>
                <div style={{ textAlign: 'center' }}>
                  <div style={{ fontSize: '48px', marginBottom: '16px' }}>📄</div>
                  <p>Aucun élément à afficher</p>
                  <p style={{ fontSize: '14px', marginTop: '8px' }}>
                    Ajoutez des éléments dans l'éditeur pour les voir ici
                  </p>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Informations de debug (optionnel) */}
        {process.env.NODE_ENV === 'development' && elements && elements.length > 0 && (
          <div style={{
            padding: '12px 20px',
            backgroundColor: '#f3f4f6',
            borderTop: '1px solid #e0e0e0',
            fontSize: '12px',
            color: '#6b7280'
          }}>
            <details>
              <summary style={{ cursor: 'pointer', fontWeight: 'bold' }}>
                Debug: Éléments ({elements.length})
              </summary>
              <pre style={{
                marginTop: '8px',
                backgroundColor: 'white',
                padding: '8px',
                borderRadius: '4px',
                overflow: 'auto',
                maxHeight: '200px'
              }}>
                {JSON.stringify(elements, null, 2)}
              </pre>
            </details>
          </div>
        )}
      </div>
    </div>
  );
}

// Fonction utilitaire pour charger des données (utilisée par PreviewModal)
CanvasMode.loadData = async (elements, templateData, config) => {
  // Logique de chargement des données d'aperçu
  return {
    elements: elements || [],
    templateData: templateData || {},
    config: config || {}
  };
};

export default CanvasMode;