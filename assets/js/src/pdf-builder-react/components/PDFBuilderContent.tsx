import React, { useState, useEffect, memo } from 'react';
import { Canvas } from './canvas/Canvas.tsx';
import { Toolbar } from './toolbar/Toolbar.tsx';
import { PropertiesPanel } from './properties/PropertiesPanel.tsx';
import { Header } from './header/Header.tsx';
import { ElementLibrary } from './element-library/ElementLibrary.tsx';
import { SaveTooltip } from './ui/SaveTooltip.tsx';
import { useTemplate } from '../hooks/useTemplate.ts';
import { useAutoSave } from '../hooks/useAutoSave.ts';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from '../constants/canvas.ts';

interface PDFBuilderContentProps {
  width?: number;
  height?: number;
  className?: string;
}

export const PDFBuilderContent = memo(function PDFBuilderContent({
  width = DEFAULT_CANVAS_WIDTH,
  height = DEFAULT_CANVAS_HEIGHT,
  className
}: PDFBuilderContentProps) {
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

  // Hook pour la sauvegarde automatique
  const {
    state: autoSaveState,
    isSaving: isAutoSaving,
    lastSavedAt,
    error: autoSaveError,
    saveNow: retryAutoSave,
    clearError: clearAutoSaveError,
    progress
  } = useAutoSave();

  // Effet pour gérer le scroll et ajuster le padding
  useEffect(() => {
    const handleScroll = () => {
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      setIsHeaderFixed(scrollTop > 100);
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <>
      {/* SaveIndicator - affiché dans le coin supérieur droit */}
      <SaveTooltip
        state={autoSaveState}
        lastSavedAt={lastSavedAt}
        error={autoSaveError}
        progress={progress}
        onSaveNow={retryAutoSave}
      />

      <div
        className={`pdf-builder ${className || ''}`}
        style={{
          display: 'flex',
          flexDirection: 'column',
          width: '100%',
          height: '100%',
          gap: '12px',
          padding: '12px',
          backgroundColor: '#ffffff',
          border: '1px solid #ddd',
          borderRadius: '8px',
          paddingTop: isHeaderFixed ? '132px' : '12px',
          paddingLeft: '0px',
          transition: 'padding 0.3s ease'
        }}
      >
        {/* Header en haut */}
        <Header
          templateName={templateName || ''}
          templateDescription={templateDescription || ''}
          templateTags={templateTags || []}
          canvasWidth={canvasWidth || 794}
          canvasHeight={canvasHeight || 1123}
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
            <div
              style={{
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
              }}
            >
              {/* Indicateur de dimensions A4 */}
              <div
                style={{
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
                }}
              >
                A4: {width}×{height}px
              </div>
              <Canvas width={width} height={height} />
            </div>
          </div>

          {/* Panneau de propriétés à droite */}
          <div
            style={{
              flexShrink: 0,
              width: '430px',
              position: 'sticky',
              top: '110px',
              height: 'fit-content',
              maxHeight: 'calc(100vh - 32px)'
            }}
          >
            <PropertiesPanel />
          </div>
        </div>
      </div>
    </>
  );
});
