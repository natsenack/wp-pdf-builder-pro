import React from 'react';
import { BuilderProvider } from './contexts/builder/BuilderContext';
import { Canvas } from './components/canvas/Canvas';
import { Toolbar } from './components/toolbar/Toolbar';
import { PropertiesPanel } from './components/properties/PropertiesPanel';
import { Header } from './components/header/Header';
import { useTemplate } from './hooks/useTemplate';

interface PDFBuilderProps {
  width?: number;
  height?: number;
  className?: string;
}

function PDFBuilderContent({
  width = 800,
  height = 600,
  className
}: PDFBuilderProps) {
  const {
    templateName,
    isNewTemplate,
    isModified,
    isSaving,
    saveTemplate,
    previewTemplate,
    newTemplate
  } = useTemplate();

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
      borderRadius: '8px'
    }}>
      {/* Header en haut */}
      <Header
        templateName={templateName}
        isNewTemplate={isNewTemplate}
        isModified={isModified}
        isSaving={isSaving}
        onSave={saveTemplate}
        onPreview={previewTemplate}
        onNewTemplate={newTemplate}
      />

      {/* Contenu principal */}
      <div style={{ display: 'flex', flex: 1, gap: '12px' }}>
        {/* Toolbar à gauche */}
        <div style={{ flexShrink: 0 }}>
          <Toolbar />
        </div>

        {/* Zone principale avec le canvas */}
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
          <div style={{
            flex: 1,
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            backgroundColor: '#f8f8f8',
            border: '1px solid #e0e0e0',
            borderRadius: '4px',
            overflow: 'hidden'
          }}>
            <Canvas
              width={width}
              height={height}
            />
          </div>
        </div>

        {/* Panneau de propriétés à droite */}
        <div style={{ flexShrink: 0, width: '280px' }}>
          <PropertiesPanel />
        </div>
      </div>
    </div>
  );
}

export function PDFBuilder({
  width = 800,
  height = 600,
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