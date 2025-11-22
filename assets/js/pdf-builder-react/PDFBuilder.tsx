import React, { useState, useEffect } from 'react';
import { BuilderProvider } from './contexts/builder/BuilderContext.tsx';
import { CanvasSettingsProvider } from './contexts/CanvasSettingsContext.tsx';
import { PDFBuilderContent } from './components/PDFBuilderContent.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';

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
    console.log('🔍 PDFBuilder adding event listener for pdfBuilderUpdateCanvasDimensions');
    const handleUpdateDimensions = (event: CustomEvent) => {
      const { width, height } = event.detail;
      console.log('🔍 PDFBuilder received pdfBuilderUpdateCanvasDimensions:', { width, height });
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
