import React from 'react';
import { PreviewProvider } from './context/PreviewProvider';
import { usePreviewContext } from './context/PreviewContext';
import PreviewModalComponent from './components/PreviewModal';

/**
 * PreviewModal - Wrapper avec PreviewProvider pour compatibilité
 * Ce fichier existe pour compatibilité avec les anciens imports
 */
const PreviewModal = (props) => {
  try {
  // Ne rien rendre si props est undefined ou null
  if (!props) {
    return <div></div>;
  }

    return <PreviewModalWithContext legacyProps={props} />;
  } catch (error) {
    console.error('🎭 PreviewModal: Erreur dans PreviewModal:', error);
    return false;
  }
};

// Composant interne qui gère la logique legacy
const PreviewModalWithContext = React.memo(({ legacyProps }) => {
  try {
    const { state: { isOpen }, actions: { openPreview, closePreview } } = usePreviewContext();

    // Ouvrir automatiquement si des props legacy indiquent que la modal doit être ouverte
    React.useEffect(() => {
      if (legacyProps && legacyProps.isOpen && !isOpen) {
        const openModal = async () => {
          const initialData = legacyProps.elements || null;
          const initialMode = legacyProps.mode || 'canvas';

          // Charger les données d'aperçu si nécessaire
          let previewData = null;
          if (initialMode === 'canvas' && initialData && initialData.length > 0) {
            try {
              // Importer dynamiquement CanvasMode pour éviter les dépendances circulaires
              const { default: CanvasMode } = await import('./modes/CanvasMode');
              if (CanvasMode.loadData) {
                previewData = await CanvasMode.loadData(initialData, null, legacyProps.templateData || {});
              }
            } catch (error) {
              console.error('Erreur lors du chargement des données d\'aperçu:', error);
            }
          }

          openPreview(initialMode, previewData, { elements: initialData, templateData: legacyProps.templateData });
        };

        openModal();
      } else if (legacyProps && !legacyProps.isOpen && isOpen) {
        // Fermer la modal si les props legacy indiquent qu'elle doit être fermée
        closePreview();
      }
    }, [legacyProps, openPreview, closePreview, isOpen]);

    return <PreviewModalComponent />;
  } catch (error) {
    console.error('PDF Builder: PreviewModalWithContext CRITICAL ERROR:', error);
    console.error('PDF Builder: PreviewModalWithContext error stack:', error.stack);
    console.error('PDF Builder: PreviewModalWithContext legacyProps that caused error:', legacyProps);
    return null;
  }
});

export default React.memo(PreviewModal);
