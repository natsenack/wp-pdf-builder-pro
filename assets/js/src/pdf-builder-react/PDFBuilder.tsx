import React from 'react';
import { BuilderProvider } from './contexts/builder/BuilderContext.tsx';
import { PDFBuilderContent } from './components/PDFBuilderContent.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';

interface PDFBuilderProps {
  width?: number;
  height?: number;
  className?: string;
}

export function PDFBuilder({
  width = DEFAULT_CANVAS_WIDTH,
  height = DEFAULT_CANVAS_HEIGHT,
  className
}: PDFBuilderProps) {
  return (
    <BuilderProvider>
      <PDFBuilderContent
        width={width}
        height={height}
        className={className}
      />
    </BuilderProvider>
  );
}

// Export des composants individuels pour une utilisation modulaire
export { PDFBuilderContent };
