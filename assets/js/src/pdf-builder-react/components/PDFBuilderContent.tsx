import React, { useState, useEffect, memo, useCallback } from 'react';
import { Canvas } from './canvas/Canvas.tsx';
import { Toolbar } from './toolbar/Toolbar.tsx';
import { PropertiesPanel } from './properties/PropertiesPanel.tsx';
import { Header } from './header/Header.tsx';
import { ElementLibrary } from './element-library/ElementLibrary.tsx';
import { SaveIndicator } from './ui/SaveIndicatorSimple.tsx';
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
  const [isPropertiesPanelOpen, setIsPropertiesPanelOpen] = useState(false);

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
    lastSavedAt,
    error: autoSaveError,
    saveNow: retryAutoSave,
    triggerSave,
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

  // Wrapper pour sauvegarder avec auto-save
  const saveTemplateWithAutoSave = useCallback(async () => {
    triggerSave(); // Déclenche l'auto-save
    await saveTemplate(); // Et la sauvegarde manuelle
  }, [saveTemplate, triggerSave]);

  return (
    <>
      {/* SaveIndicator - affiché dans le coin supérieur droit */}
      <SaveIndicator
        state={autoSaveState}
        lastSavedAt={lastSavedAt}
        error={autoSaveError}
        onRetry={retryAutoSave}
        progress={progress}
        showProgressBar={autoSaveState === 'saving'}
      />

      <div
        className={`pdf-builder ${className || ''}`}
        style={{
          display: 'flex',
          flexDirection: 'column',
          width: '100%',
          height: '100%',
          gap: '0px',
          padding: '0px',
          backgroundColor: '#ffffff',
          border: 'none',
          borderRadius: '0px',
          paddingTop: isHeaderFixed ? '132px' : '0px',
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
          onSave={saveTemplateWithAutoSave}
          onPreview={previewTemplate}
          onNewTemplate={newTemplate}
          onUpdateTemplateSettings={updateTemplateSettings}
        />

        {/* Toolbar sous le header */}
        <div style={{ flexShrink: 0, padding: '12px 12px 0 12px' }}>
          <Toolbar />
        </div>

        {/* Contenu principal */}
        <div style={{ display: 'flex', flex: 1, gap: '0', padding: '12px' }}>
          {/* Sidebar des éléments WooCommerce */}
          <ElementLibrary />

          {/* Zone centrale avec le canvas */}
          <div style={{ flex: 1, display: 'flex', flexDirection: 'column', position: 'relative' }}>
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

            {/* Bouton toggle pour le panneau de propriétés */}
            <button
              onClick={() => setIsPropertiesPanelOpen(!isPropertiesPanelOpen)}
              style={{
                position: 'absolute',
                top: '50%',
                right: isPropertiesPanelOpen ? '-12px' : '0px',
                transform: 'translateY(-50%)',
                zIndex: 20,
                padding: '8px 6px',
                backgroundColor: '#007acc',
                color: 'white',
                border: 'none',
                borderRadius: '4px 0 0 4px',
                cursor: 'pointer',
                fontSize: '14px',
                fontWeight: 'bold',
                boxShadow: '0 2px 4px rgba(0,0,0,0.2)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '24px',
                height: '60px',
                writingMode: 'vertical-rl',
                textOrientation: 'mixed'
              }}
              title={isPropertiesPanelOpen ? 'Fermer le panneau de propriétés' : 'Ouvrir le panneau de propriétés'}
            >
              {isPropertiesPanelOpen ? '▷' : '◁'}
            </button>
          </div>

          {/* Panneau de propriétés à droite */}
          {isPropertiesPanelOpen && (
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
          )}
        </div>
      </div>
    </>
  );
});
