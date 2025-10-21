/**
 * SYSTÈME D'APERÇU ULTRA-SIMPLE - VERSION 3.0
 * Export principal pour le nouveau système d'aperçu
 */

// Import du système ultra-simple
import {
  SimpleCanvasPreview,
  SimplePreviewModal,
  SimplePreviewTest,
  SimpleElementRenderer,
  usePreviewScaling
} from './SimplePreviewSystem_v3';

// Hook pour intégrer facilement le système
export function useSimplePreview() {
  const [previewState, setPreviewState] = React.useState({
    isOpen: false,
    elements: [],
    templateWidth: 595,
    templateHeight: 842,
    title: 'Aperçu PDF'
  });

  const openPreview = React.useCallback((config) => {
    setPreviewState({
      isOpen: true,
      elements: config.elements || [],
      templateWidth: config.templateWidth || 595,
      templateHeight: config.templateHeight || 842,
      title: config.title || 'Aperçu PDF'
    });
  }, []);

  const closePreview = React.useCallback(() => {
    setPreviewState(prev => ({ ...prev, isOpen: false }));
  }, []);

  return {
    isOpen: previewState.isOpen,
    openPreview,
    closePreview,
    PreviewModal: () => (
      <SimplePreviewModal
        isOpen={previewState.isOpen}
        onClose={closePreview}
        elements={previewState.elements}
        templateWidth={previewState.templateWidth}
        templateHeight={previewState.templateHeight}
        title={previewState.title}
      />
    )
  };
}

// Composant de test autonome
export function PreviewSystemTestV3() {
  return <SimplePreviewTest />;
}

// Export de tous les composants
export {
  SimpleCanvasPreview,
  SimplePreviewModal,
  SimpleElementRenderer,
  usePreviewScaling
};

// Export par défaut
export default {
  SimpleCanvasPreview,
  SimplePreviewModal,
  SimplePreviewTest,
  useSimplePreview,
  PreviewSystemTestV3
};