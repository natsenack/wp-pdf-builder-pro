import React, { useState, useEffect, memo, useCallback } from 'react';
import { Canvas } from './canvas/Canvas.tsx';
import { Toolbar } from './toolbar/Toolbar.tsx';
import { PropertiesPanel } from './properties/PropertiesPanel.tsx';
import { Header } from './header/Header.tsx';
import { ElementLibrary } from './element-library/ElementLibrary.tsx';
import { useTemplate } from '../hooks/useTemplate.ts';
import { useCanvasSettings } from '../contexts/CanvasSettingsContext.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from '../constants/canvas.ts';
import { injectResponsiveUtils } from '../utils/responsive.ts';
import { useIsMobile, useIsTablet } from '../hooks/useResponsive.ts';

// ✅ Add spin animation
const spinStyles = `
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
`;

// Inject CSS
if (typeof document !== 'undefined') {
  const style = document.createElement('style');
  style.textContent = spinStyles;
  document.head.appendChild(style);
}

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
  const [manualSaveSuccess, setManualSaveSuccess] = useState(false);

  // Hooks responsives
  const isMobile = useIsMobile();
  const isTablet = useIsTablet();

  const {
    templateName,
    templateDescription,
    canvasWidth,
    canvasHeight,
    marginTop,
    marginBottom,
    showGuides,
    snapToGrid,
    isNewTemplate,
    isModified,
    isSaving,
    isLoading, // ✅ NEW: Template is loading
    isEditingExistingTemplate,
    saveTemplate,
    previewTemplate,
    newTemplate,
    updateTemplateSettings
  } = useTemplate();

  // Hook pour les paramètres du canvas
  const canvasSettings = useCanvasSettings();

  // Injection des utilitaires responsives
  useEffect(() => {
    injectResponsiveUtils();
  }, []);

  // Effet pour gérer le scroll et ajuster le padding
  useEffect(() => {
    const handleScroll = () => {
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      setIsHeaderFixed(scrollTop > 100);
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Wrapper pour sauvegarder
  const saveTemplateWithAutoSave = useCallback(async () => {
    try {
      // Effectuer la sauvegarde manuelle
      await saveTemplate();
      console.log('[PDF_BUILDER] Manual save successful');
      
      // Afficher l'indicateur de succès temporaire
      setManualSaveSuccess(true);
      setTimeout(() => setManualSaveSuccess(false), 3000); // Disparaît après 3 secondes
      
    } catch (manualSaveError) {
      console.error('[PDF_BUILDER] Manual save failed:', manualSaveError);
      throw manualSaveError; // Re-throw pour que l'UI montre l'erreur
    }
  }, [saveTemplate]);

  return (
    <>
      {/* Indicateur de succès manuel temporaire */}
      {manualSaveSuccess && (
        <div
          style={{
            position: 'fixed',
            top: '50px',
            right: '20px',
            padding: '14px 20px',
            WebkitBorderRadius: '6px',
            MozBorderRadius: '6px',
            msBorderRadius: '6px',
            OBorderRadius: '6px',
            borderRadius: '6px',
            background: '#4CAF50',
            border: '2px solid #388E3C',
            WebkitBoxShadow: '0 4px 16px rgba(0, 0, 0, 0.3)',
            MozBoxShadow: '0 4px 16px rgba(0, 0, 0, 0.3)',
            msBoxShadow: '0 4px 16px rgba(0, 0, 0, 0.3)',
            OBoxShadow: '0 4px 16px rgba(0, 0, 0, 0.3)',
            boxShadow: '0 4px 16px rgba(0, 0, 0, 0.3)',
            fontSize: '14px',
            fontWeight: 'bold',
            fontFamily: 'Arial, sans-serif',
            color: '#fff',
            zIndex: 999999,
            display: '-webkit-box',
            display: '-webkit-flex',
            display: '-moz-box',
            display: '-ms-flexbox',
            display: 'flex',
            WebkitBoxAlign: 'center',
            WebkitAlignItems: 'center',
            MozBoxAlign: 'center',
            msFlexAlign: 'center',
            alignItems: 'center',
            WebkitGap: '12px',
            MozGap: '12px',
            gap: '12px',
            minWidth: '200px',
            animation: 'slideIn 0.3s ease-out'
          }}
        >
          <span style={{ fontSize: '16px' }}>✓</span>
          <span>Template sauvegardé !</span>
        </div>
      )}

      {/* Styles pour les animations des notifications */}
      <style>{`
        @keyframes slideIn {
          from {
            -webkit-transform: translateX(100px);
            -moz-transform: translateX(100px);
            -ms-transform: translateX(100px);
            -o-transform: translateX(100px);
            transform: translateX(100px);
            opacity: 0;
          }
          to {
            -webkit-transform: translateX(0);
            -moz-transform: translateX(0);
            -ms-transform: translateX(0);
            -o-transform: translateX(0);
            transform: translateX(0);
            opacity: 1;
          }
        }
      `}</style>

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
          canvasWidth={canvasWidth || 794}
          canvasHeight={canvasHeight || 1123}
          showGuides={showGuides || true}
          snapToGrid={snapToGrid || false}
          isNewTemplate={isNewTemplate}
          isModified={isModified}
          isSaving={isSaving}
          isLoading={isLoading}
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
                backgroundColor: canvasSettings.containerBackgroundColor || '#f8f8f8',
                border: '1px solid #e0e0e0',
                borderRadius: '4px',
                overflow: 'visible',
                position: 'relative',
                paddingTop: '20px',
                paddingBottom: '20px'
              }}
            >
              {/* Indicateur de dimensions avec format et DPI */}
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
                {(() => {
                  const format = (window as any).pdfBuilderCanvasSettings?.default_canvas_format || 'A4';
                  const dpi = (window as any).pdfBuilderCanvasSettings?.default_canvas_dpi || 96;
                  const orientation = (window as any).pdfBuilderCanvasSettings?.default_canvas_orientation || 'portrait';
                  const paperFormats = (window as any).pdfBuilderPaperFormats || {
                    'A4': { width: 210, height: 297 },
                    'A3': { width: 297, height: 420 },
                    'A5': { width: 148, height: 210 },
                    'Letter': { width: 215.9, height: 279.4 },
                    'Legal': { width: 215.9, height: 355.6 },
                    'Tabloid': { width: 279.4, height: 431.8 }
                  };

                  // Récupérer les dimensions en mm
                  const dimsMM = paperFormats[format] || paperFormats['A4'];

                  // Calculer les dimensions en pixels avec le DPI actuel
                  const pixelsPerMM = dpi / 25.4;
                  let widthPx = Math.round(dimsMM.width * pixelsPerMM);
                  let heightPx = Math.round(dimsMM.height * pixelsPerMM);

                  // Inverser si orientation paysage
                  if (orientation === 'landscape') {
                    [widthPx, heightPx] = [heightPx, widthPx];
                  }

                  return `${format}: ${widthPx}×${heightPx}px (${dpi} DPI)`;
                })()}
              </div>
              
              {/* ✅ Loading spinner overlay */}
              {isLoading && (
                <div
                  style={{
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    backgroundColor: 'rgba(255, 255, 255, 0.7)',
                    display: 'flex',
                    justifyContent: 'center',
                    alignItems: 'center',
                    zIndex: 100,
                    borderRadius: '4px'
                  }}
                >
                  <div style={{ textAlign: 'center' }}>
                    <div
                      style={{
                        width: '40px',
                        height: '40px',
                        border: '4px solid #e0e0e0',
                        borderTop: '4px solid #007acc',
                        borderRadius: '50%',
                        animation: 'spin 1s linear infinite',
                        margin: '0 auto 12px'
                      }}
                    />
                    <p style={{ margin: 0, color: '#666', fontSize: '14px' }}>
                      Chargement du template...
                    </p>
                  </div>
                </div>
              )}
              
              {/* ✅ ONLY render Canvas when template is loaded OR it's a new template */}
              {!isLoading && <Canvas width={width} height={height} />}
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
