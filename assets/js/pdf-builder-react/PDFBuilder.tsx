import React, { useState, useEffect } from 'react';
import { BuilderProvider } from './contexts/builder/BuilderContext';
import { CanvasSettingsProvider } from './contexts/CanvasSettingsContext';
import { PDFBuilderContent } from './components/PDFBuilderContent';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas';
import { debugLog } from './utils/debug';

debugLog('🔧 [PDFBuilder.tsx] Import successful. React:', typeof React, 'useState:', typeof useState, 'useEffect:', typeof useEffect);
debugLog('🔧 [PDFBuilder.tsx] window.pdfBuilderData at import time:', window.pdfBuilderData);
debugLog('🔧 [PDFBuilder.tsx] window keys at import time:', Object.keys(window).filter(key => key.includes('pdfBuilder')));

interface PDFBuilderProps {
  width?: number;
  height?: number;
  className?: string;
}

export function PDFBuilder({
  width: initialWidth = DEFAULT_CANVAS_WIDTH,
  height: initialHeight = DEFAULT_CANVAS_HEIGHT,
  className
}: PDFBuilderProps) {
  debugLog('🔧 PDFBuilder: Component initialized with props:', { initialWidth, initialHeight, className });
  debugLog('🔧 PDFBuilder: window.pdfBuilderData at component init:', window.pdfBuilderData);
  debugLog('🔧 PDFBuilder: window.pdfBuilderData?.ajaxUrl:', window.pdfBuilderData?.ajaxUrl);
  debugLog('🔧 PDFBuilder: window.pdfBuilderData?.nonce:', window.pdfBuilderData?.nonce);

  const [dimensions, setDimensions] = useState({
    width: initialWidth,
    height: initialHeight
  });

  debugLog('📏 PDFBuilder: Initial dimensions set:', dimensions);

  // Écouter les changements de dimensions depuis l'API globale
  useEffect(() => {
    debugLog('🎧 PDFBuilder: Setting up dimension change listener');

    const handleUpdateDimensions = (event: CustomEvent) => {
      debugLog('📡 PDFBuilder: Received dimension update event:', event.detail);
      const { width, height } = event.detail;
      debugLog('🔄 PDFBuilder: Updating dimensions to:', { width, height });
      setDimensions({ width, height });
    };

    document.addEventListener('pdfBuilderUpdateCanvasDimensions', handleUpdateDimensions as EventListener, { passive: true });
    debugLog('✅ PDFBuilder: Dimension change listener added');

    return () => {
      debugLog('🧹 PDFBuilder: Cleaning up dimension change listener');
      document.removeEventListener('pdfBuilderUpdateCanvasDimensions', handleUpdateDimensions as EventListener);
    };
  }, []);

  debugLog('🎨 PDFBuilder: Rendering with dimensions:', dimensions);

  return (
    <CanvasSettingsProvider>
      <BuilderProvider>
        <PDFBuilderContent
          width={dimensions.width}
          height={dimensions.height}
          className={className}
        />
      </BuilderProvider>
    </CanvasSettingsProvider>
  );
}

// Export des composants individuels pour une utilisation modulaire
export { PDFBuilderContent };

