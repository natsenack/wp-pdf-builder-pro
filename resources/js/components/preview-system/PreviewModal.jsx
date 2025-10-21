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

    return (
      <PreviewProvider>
        <PreviewModalWithContext legacyProps={props} />
      </PreviewProvider>
    );
  } catch (error) {
    return false;
  }
};

// Composant interne qui gère la logique legacy
const PreviewModalWithContext = ({ legacyProps }) => {
  try {
    const { state: { isOpen }, actions: { openPreview, closePreview } } = usePreviewContext();

    // Ouvrir automatiquement si des props legacy sont passées
    React.useEffect(() => {
      if (legacyProps && !isOpen) {
        const initialData = legacyProps.elements || null;
        const initialMode = legacyProps.mode || 'canvas';
        openPreview(initialMode, initialData);
      }
    }, [legacyProps, isOpen, openPreview]);

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
};

export default PreviewModal;
