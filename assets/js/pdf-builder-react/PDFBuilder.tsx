import React, { useState, useEffect } from 'react';
import { BuilderProvider } from './contexts/builder/BuilderContext';
import { CanvasSettingsProvider } from './contexts/CanvasSettingsContext';
import { PDFBuilderContent } from './components/PDFBuilderContent';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas';

console.log('🔧 [PDFBuilder.tsx] Import successful. React:', typeof React, 'useState:', typeof useState, 'useEffect:', typeof useEffect);

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
  console.log('🔧 PDFBuilder: Component initialized with props:', { initialWidth, initialHeight, className });

  const [dimensions, setDimensions] = useState({
    width: initialWidth,
    height: initialHeight
  });

  console.log('📏 PDFBuilder: Initial dimensions set:', dimensions);

  // Écouter les changements de dimensions depuis l'API globale
  useEffect(() => {
    console.log('🎧 PDFBuilder: Setting up dimension change listener');

    const handleUpdateDimensions = (event: CustomEvent) => {
      console.log('📡 PDFBuilder: Received dimension update event:', event.detail);
      const { width, height } = event.detail;
      console.log('🔄 PDFBuilder: Updating dimensions to:', { width, height });
      setDimensions({ width, height });
    };

    document.addEventListener('pdfBuilderUpdateCanvasDimensions', handleUpdateDimensions as EventListener);
    console.log('✅ PDFBuilder: Dimension change listener added');

    return () => {
      console.log('🧹 PDFBuilder: Cleaning up dimension change listener');
      document.removeEventListener('pdfBuilderUpdateCanvasDimensions', handleUpdateDimensions as EventListener);
    };
  }, []);

  console.log('🎨 PDFBuilder: Rendering with dimensions:', dimensions);

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

