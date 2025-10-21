import React from 'react';

// TEST SIMPLE - Export direct
export function useSimplePreview() {
  console.log('[TEST] useSimplePreview called');

  return {
    elements: [],
    templateData: { width: 595, height: 842 },
    previewData: {},
    scale: 0.8,
    zoom: 1,
    isFullscreen: false,
    actualScale: 0.8,
    canvasWidth: 595,
    canvasHeight: 842,
    displayWidth: 476,
    displayHeight: 673.6,
    containerStyle: { width: '100%', height: '100%' },
    canvasStyle: { width: 595, height: 842, backgroundColor: '#fff' },
    canvasWrapperStyle: { width: 476, height: 673.6 },
    renderElements: () => React.createElement('div', {}, 'Test elements'),
    setElements: () => {},
    setTemplateData: () => {},
    setPreviewData: () => {},
    setScale: () => {},
    setZoom: () => {},
    setFullscreen: () => {}
  };
}