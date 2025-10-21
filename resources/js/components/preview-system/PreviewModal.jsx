import React from 'react';
import { PreviewProvider } from './context/PreviewProvider';
import { usePreviewContext } from './context/PreviewContext';
import PreviewModalComponent from './components/PreviewModal';

/**
 * PreviewModal - Wrapper avec PreviewProvider pour compatibilité
 * Ce fichier existe pour compatibilité avec les anciens imports
 */
const PreviewModal = (props) => {
  console.log('🎭 PreviewModal rendu avec props:', props);
  try {
  // Ne rien rendre si props est undefined ou null
  if (!props) {
    console.log('🎭 PreviewModal: props null/undefined, rien rendu');
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
  console.log('🎭 PreviewModalWithContext rendu avec legacyProps:', legacyProps);
  try {
    const { state: { isOpen }, actions: { openPreview, closePreview } } = usePreviewContext();
    console.log('🎭 PreviewModalWithContext: isOpen du context:', isOpen);

        // Ref pour éviter les ouvertures multiples
    const hasOpenedRef = React.useRef(false);
    const prevPropsRef = React.useRef(null);

    // Ouvrir automatiquement si des props legacy sont passées (une seule fois)
    React.useEffect(() => {
      console.log('🎭 useEffect ouverture: legacyProps?', !!legacyProps, 'isOpen?', isOpen, 'hasOpened?', hasOpenedRef.current);
      if (legacyProps && !isOpen && !hasOpenedRef.current) {
        const initialData = legacyProps.elements || null;
        const initialMode = legacyProps.mode || 'canvas';
        console.log('🎭 Ouverture automatique du modal avec mode:', initialMode, 'data:', initialData);
        openPreview(initialMode, initialData);
        hasOpenedRef.current = true;
        prevPropsRef.current = { elements: initialData, mode: initialMode };
      }
    }, [legacyProps, openPreview]); // Removed isOpen to prevent re-runs

    // Réinitialiser le ref si les props changent significativement
    React.useEffect(() => {
      if (legacyProps) {
        const currentElements = legacyProps.elements || [];
        const currentMode = legacyProps.mode || 'canvas';
        const currentProps = { elements: currentElements, mode: currentMode };
        if (prevPropsRef.current && JSON.stringify(currentProps) !== JSON.stringify(prevPropsRef.current)) {
          hasOpenedRef.current = false;
        }
      }
    }, [legacyProps]);

    // Gérer la fermeture legacy
    React.useEffect(() => {
      if (legacyProps && legacyProps.onClose) {
        const handleClose = () => {
          if (legacyProps.onClose) {
            legacyProps.onClose();
          }
          closePreview();
        };
        // TODO: Attacher handleClose au context si nécessaire
      }
    }, [legacyProps, closePreview]);

    return <PreviewModalComponent />;
  } catch (error) {
    console.error('PDF Builder: PreviewModalWithContext CRITICAL ERROR:', error);
    console.error('PDF Builder: PreviewModalWithContext error stack:', error.stack);
    console.error('PDF Builder: PreviewModalWithContext legacyProps that caused error:', legacyProps);
    return null;
  }
});

export default React.memo(PreviewModal);
