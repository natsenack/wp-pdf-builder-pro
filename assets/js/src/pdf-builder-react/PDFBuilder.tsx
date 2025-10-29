import React, { useState, useEffect } from 'react';
import { BuilderProvider } from './contexts/builder/BuilderContext.tsx';
import { Canvas } from './components/canvas/Canvas.tsx';
import { Toolbar } from './components/toolbar/Toolbar.tsx';
import { PropertiesPanel } from './components/properties/PropertiesPanel.tsx';
import { Header } from './components/header/Header.tsx';
import { ElementLibrary } from './components/element-library/ElementLibrary.tsx';
import { useTemplate } from './hooks/useTemplate.ts';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';

interface PDFBuilderProps {
  width?: number;
  height?: number;
  className?: string;
}

function PDFBuilderContent({
  width = DEFAULT_CANVAS_WIDTH, // A4 portrait width in pixels (210mm at 96 DPI)
  height = DEFAULT_CANVAS_HEIGHT, // A4 portrait height in pixels (297mm at 96 DPI)
  className
}: PDFBuilderProps) {
  console.log('PDFBuilderContent rendering...');

  const [isHeaderFixed, setIsHeaderFixed] = useState(false);

  const {
    templateName,
    templateDescription,
    templateTags,
    canvasWidth,
    canvasHeight,
    marginTop,
    marginBottom,
    showGuides,
    snapToGrid,
    isNewTemplate,
    isModified,
    isSaving,
    isEditingExistingTemplate,
    saveTemplate,
    previewTemplate,
    newTemplate,
    updateTemplateSettings
  } = useTemplate();

  console.log('useTemplate hook returned:', { templateName, isNewTemplate, isModified, isSaving });

  // Effet pour gérer le scroll et ajuster le padding
  useEffect(() => {
    const handleScroll = () => {
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      setIsHeaderFixed(scrollTop > 100);
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  console.log('About to render Toolbar component');

  return (
    <div className={`pdf-builder ${className || ''}`} style={{
      display: 'flex',
      flexDirection: 'column',
      width: '100%',
      height: '100%',
      gap: '12px',
      padding: '12px',
      backgroundColor: '#ffffff',
      border: '1px solid #ddd',
      borderRadius: '8px',
      paddingTop: isHeaderFixed ? '132px' : '12px', // Ajustement dynamique du padding-top
      paddingLeft: '0px', // Espace pour le menu WordPress (160px) + marge (12px)
      transition: 'padding 0.3s ease'
    }}>
      {/* Header en haut */}
      <Header
        templateName={templateName || ''}
        templateDescription={templateDescription || ''}
        templateTags={templateTags || []}
        canvasWidth={canvasWidth || 595}
        canvasHeight={canvasHeight || 842}
        marginTop={marginTop || 20}
        marginBottom={marginBottom || 20}
        showGuides={showGuides || true}
        snapToGrid={snapToGrid || false}
        isNewTemplate={isNewTemplate}
        isModified={isModified}
        isSaving={isSaving}
        isEditingExistingTemplate={isEditingExistingTemplate}
        onSave={saveTemplate}
        onPreview={previewTemplate}
        onNewTemplate={newTemplate}
        onUpdateTemplateSettings={updateTemplateSettings}
      />

      {/* Toolbar sous le header */}
      <div style={{ flexShrink: 0 }}>
        <Toolbar />
      </div>

      {/* Contenu principal */}
      <div style={{ display: 'flex', flex: 1, gap: '0' }}>
        {/* Sidebar des éléments WooCommerce */}
        <ElementLibrary />

        {/* Zone centrale avec le canvas */}
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
          <div style={{
            flex: 1,
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            backgroundColor: '#f8f8f8',
            border: '1px solid #e0e0e0',
            borderRadius: '4px',
            overflow: 'hidden',
            position: 'relative',
            paddingTop: '20px',
            paddingBottom: '20px'
          }}>
            {/* Indicateur de dimensions A4 */}
            <div style={{
              position: 'absolute',
              top: '8px',
              right: '8px',
              backgroundColor: 'rgba(0, 122, 204, 0.9)',
              color: 'white',
              padding: '4px 8px',
              borderRadius: '4px',
              fontSize: '12px',
              fontWeight: 'bold',
              zIndex: 10
            }}>
              A4: {width}×{height}px
            </div>
            <Canvas
              width={width}
              height={height}
            />
          </div>
        </div>

        {/* Panneau de propriétés à droite */}
        <div style={{ flexShrink: 0, width: '430px' }}>
          <PropertiesPanel />
        </div>
      </div>
    </div>
  );
}

export function PDFBuilder({
  width = DEFAULT_CANVAS_WIDTH, // A4 portrait width in pixels (210mm at 96 DPI)
  height = DEFAULT_CANVAS_HEIGHT, // A4 portrait height in pixels (297mm at 96 DPI)
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
export { Canvas, Toolbar, PropertiesPanel, BuilderProvider };