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

    return (
      <PreviewProvider>
        <PreviewModalWithContext legacyProps={props} />
      </PreviewProvider>
    );
  } catch (error) {
    console.error('🎭 PreviewModal: Erreur dans PreviewModal:', error);
    return false;
  }
};

// Composant interne qui gère la logique legacy
const PreviewModalWithContext = ({ legacyProps }) => {
  console.log('🎭 PreviewModalWithContext rendu avec legacyProps:', legacyProps);
  try {
    const { state: { isOpen }, actions: { openPreview, closePreview } } = usePreviewContext();
    console.log('🎭 PreviewModalWithContext: isOpen du context:', isOpen);

    // Ouvrir automatiquement si des props legacy sont passées
    React.useEffect(() => {
      console.log('🎭 useEffect ouverture: legacyProps?', !!legacyProps, 'isOpen?', isOpen);
      if (legacyProps && !isOpen) {
        const initialData = legacyProps.elements || null;
        const initialMode = legacyProps.mode || 'canvas';
        console.log('🎭 Ouverture automatique du modal avec mode:', initialMode, 'data:', initialData);
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
