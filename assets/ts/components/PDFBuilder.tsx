import React, { useEffect, useRef } from 'react';
import TemplateSelector from '@/ts/components/TemplateSelector';
import { PDFCanvasVanilla } from '../pdf-canvas-vanilla';

/**
 * Main PDF Builder component
 */
const PDFBuilder: React.FC = () => {
  const canvasContainerRef = useRef<HTMLDivElement>(null);
  const builderRef = useRef<PDFCanvasVanilla | null>(null);

  useEffect(() => {
    const initializeBuilder = async () => {
      if (canvasContainerRef.current) {
        try {
          // Create and initialize the canvas builder
          builderRef.current = new PDFCanvasVanilla('pdf-canvas-container', {
            width: 800,
            height: 600,
            backgroundColor: '#ffffff',
            gridSize: 20,
            showGrid: true,
            zoom: 1
          });

          // Initialize the builder
          await builderRef.current.init();
          console.log('✅ PDF Canvas Builder initialized successfully');
        } catch (error) {
          console.error('❌ Error initializing PDF Canvas Builder:', error);
        }
      }
    };

    initializeBuilder();

    // Cleanup on unmount
    return () => {
      if (builderRef.current) {
        // Cleanup code if needed
        builderRef.current = null;
      }
    };
  }, []);

  return (
    <div className="pdf-builder-container">
      <div className="pdf-builder-header">
        <h1>PDF Builder Pro</h1>
        <p>Créez vos templates PDF personnalisés</p>
      </div>

      <div className="pdf-builder-content">
        <div className="pdf-builder-sidebar">
          <TemplateSelector
            onTemplateSelect={(template) => {
              console.log('Template selected:', template);
              // TODO: Implement template selection logic with builder
            }}
          />
        </div>

        {/* Canvas container for the PDF builder */}
        <div 
          ref={canvasContainerRef}
          id="pdf-canvas-container"
          className="pdf-canvas-wrapper"
          style={{
            flex: 1,
            borderLeft: '1px solid #ddd',
            overflow: 'auto'
          }}
        />
      </div>
    </div>
  );
};

export default PDFBuilder;