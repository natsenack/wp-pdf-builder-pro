import React from 'react';
import { PreviewProvider } from './context/PreviewProvider';
import { usePreview } from './context/PreviewContext';
import PreviewModalComponent from './components/PreviewModal';

/**
 * PreviewModal - Wrapper avec PreviewProvider pour compatibilité
 * Ce fichier existe pour compatibilité avec les anciens imports
 */
const PreviewModal = (props) => {
  try {
    console.log('PDF Builder: PreviewModal legacy wrapper called with props:', props);
    console.log('PDF Builder: PreviewModal - props type:', typeof props);
    console.log('PDF Builder: PreviewModal - props is null:', props === null);
    console.log('PDF Builder: PreviewModal - props is undefined:', props === undefined);
    console.log('PDF Builder: PreviewModal - props truthy check:', !!props);

  // Ne rien rendre si props est undefined ou null
  if (!props) {
    console.warn('PDF Builder: PreviewModal called with undefined/null props, skipping render');
    console.log('PDF Builder: PreviewModal returning empty div due to invalid props');
    return <div></div>;
  }    console.log('PDF Builder: PreviewModal proceeding with valid props, rendering component');

    return (
      <PreviewProvider>
        <PreviewModalWithContext legacyProps={props} />
      </PreviewProvider>
    );
  } catch (error) {
    console.error('PDF Builder: PreviewModal CRITICAL ERROR:', error);
    console.error('PDF Builder: PreviewModal error stack:', error.stack);
    console.error('PDF Builder: PreviewModal props that caused error:', props);
    return false;
  }
};

// Composant interne qui gère la logique legacy
const PreviewModalWithContext = ({ legacyProps }) => {
  try {
    console.log('PDF Builder: PreviewModalWithContext called with legacyProps:', legacyProps);
    console.log('PDF Builder: PreviewModalWithContext - legacyProps type:', typeof legacyProps);

    const { openPreview, closePreview, isOpen } = usePreview();
    console.log('PDF Builder: PreviewModalWithContext - usePreview hook result:', { openPreview: typeof openPreview, closePreview: typeof closePreview, isOpen });

    // Ouvrir automatiquement si des props legacy sont passées
    React.useEffect(() => {
      console.log('PDF Builder: PreviewModalWithContext useEffect triggered with legacyProps:', legacyProps);
      if (legacyProps && !isOpen) {
        const initialData = legacyProps.elements || null;
        const initialMode = legacyProps.mode || 'canvas';
        console.log('PDF Builder: PreviewModalWithContext opening preview with:', { initialData, initialMode });
        openPreview(initialMode, initialData);
      } else {
        console.log('PDF Builder: PreviewModalWithContext skipping preview open - legacyProps:', !!legacyProps, 'isOpen:', isOpen);
      }
    }, [legacyProps, isOpen, openPreview]);

    // Gérer la fermeture legacy
    React.useEffect(() => {
      console.log('PDF Builder: PreviewModalWithContext close effect triggered');
      if (legacyProps && legacyProps.onClose) {
        const handleClose = () => {
          console.log('PDF Builder: PreviewModalWithContext handleClose called');
          if (legacyProps.onClose) {
            legacyProps.onClose();
          }
          closePreview();
        };
        // TODO: Attacher handleClose au context si nécessaire
        console.log('PDF Builder: PreviewModalWithContext close handler set up');
      } else {
        console.log('PDF Builder: PreviewModalWithContext no close handler needed');
      }
    }, [legacyProps, closePreview]);

    console.log('PDF Builder: PreviewModalWithContext about to render PreviewModalComponent');
    return <PreviewModalComponent />;
  } catch (error) {
    console.error('PDF Builder: PreviewModalWithContext CRITICAL ERROR:', error);
    console.error('PDF Builder: PreviewModalWithContext error stack:', error.stack);
    console.error('PDF Builder: PreviewModalWithContext legacyProps that caused error:', legacyProps);
    return null;
  }
};

export default PreviewModal;
