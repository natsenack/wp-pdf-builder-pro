import React, { useState, useEffect } from 'react';
import { BuilderProvider } from './contexts/builder/BuilderContext';
import { CanvasSettingsProvider } from './contexts/CanvasSettingsContext';
import { PDFBuilderContent } from './components/PDFBuilderContent';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas';

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
  const [dimensions, setDimensions] = useState({
    width: initialWidth,
    height: initialHeight
  });

  // Écouter les changements de dimensions depuis l'API globale
  useEffect(() => {
    const handleUpdateDimensions = (event: CustomEvent) => {
      const { width, height } = event.detail;
      setDimensions({ width, height });
    };

    document.addEventListener('pdfBuilderUpdateCanvasDimensions', handleUpdateDimensions as EventListener);

    return () => {
      document.removeEventListener('pdfBuilderUpdateCanvasDimensions', handleUpdateDimensions as EventListener);
    };
  }, []);

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

