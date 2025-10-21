import React from 'react';

/**
 * SYSTÈME D'APERÇU V3.0 - VERSION NETTOYÉE
 * Simplifié au maximum - aucune complication
 */

// Hook d'aperçu principal - SEUL export qui compte
export function useSimplePreview() {
  const [state, setState] = React.useState({
    elements: [],
    templateData: { width: 595, height: 842, orientation: 'Portrait' },
    previewData: {},
    scale: 0.8,
    zoom: 1,
    isFullscreen: false
  });

  // Dimensions fixes
  const canvasWidth = state.templateData.width;
  const canvasHeight = state.templateData.height;
  const containerWidth = 800;
  const containerHeight = 600;

  // Calcul d'échelle simple
  const scaleX = containerWidth / canvasWidth;
  const scaleY = containerHeight / canvasHeight;
  const actualScale = Math.min(scaleX, scaleY, 1);

  const displayWidth = canvasWidth * actualScale;
  const displayHeight = canvasHeight * actualScale;

  // Styles pré-calculés
  const containerStyle = {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'flex-start',
    padding: '20px',
    backgroundColor: '#f5f5f7',
    overflow: 'auto'
  };

  const canvasStyle = {
    width: canvasWidth,
    height: canvasHeight,
    backgroundColor: '#ffffff',
    position: 'relative',
    boxShadow: '0 8px 32px rgba(0, 0, 0, 0.12)',
    border: '1px solid #e5e5e7',
    borderRadius: '8px',
    overflow: 'hidden',
    transform: `scale(${actualScale})`,
    transformOrigin: 'top center',
    margin: `${20 / actualScale}px auto`
  };

  const canvasWrapperStyle = {
    width: displayWidth,
    height: displayHeight,
    margin: '20px auto',
    position: 'relative'
  };

  // Fonction de rendu simplifié
  const renderElements = React.useCallback(() => {
    if (!state.elements || state.elements.length === 0) {
      return null;
    }

    return state.elements.map((element) => {
      if (!element) return null;

      const elementStyle = {
        position: 'absolute',
        left: (element.x || 0) * 1,
        top: (element.y || 0) * 1,
        width: (element.width || 100) * 1,
        height: (element.height || 100) * 1,
        boxSizing: 'border-box',
        overflow: 'hidden',
        backgroundColor: element.backgroundColor || 'transparent',
        border: element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor || '#000'}` : 'none',
        fontSize: `${(element.fontSize || 14)}px`,
        color: element.color || '#000000',
        padding: element.padding || '5px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: element.textAlign === 'center' ? 'center' : 'flex-start'
      };

      return (
        <div
          key={element.id || Math.random()}
          style={elementStyle}
          data-element-id={element.id}
          data-element-type={element.type}
        >
          {element.content || element.text || element.value || ''}
        </div>
      );
    });
  }, [state.elements]);

  return {
    elements: state.elements,
    templateData: state.templateData,
    previewData: state.previewData,
    scale: state.scale,
    zoom: state.zoom,
    isFullscreen: state.isFullscreen,
    actualScale,
    canvasWidth,
    canvasHeight,
    displayWidth,
    displayHeight,
    containerStyle,
    canvasStyle,
    canvasWrapperStyle,
    renderElements,
    setElements: (elements) => setState(prev => ({ ...prev, elements })),
    setTemplateData: (templateData) => setState(prev => ({ ...prev, templateData })),
    setPreviewData: (previewData) => setState(prev => ({ ...prev, previewData })),
    setScale: (scale) => setState(prev => ({ ...prev, scale })),
    setZoom: (zoom) => setState(prev => ({ ...prev, zoom })),
    setFullscreen: (isFullscreen) => setState(prev => ({ ...prev, isFullscreen }))
  };
}

export default { useSimplePreview };
