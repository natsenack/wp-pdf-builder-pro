import React from 'react';
import { PreviewProvider } from './context/PreviewProvider';
import { usePreview } from './context/PreviewContext';
import PreviewModalComponent from './components/PreviewModal';

/**
 * PreviewModal - Wrapper avec PreviewProvider pour compatibilité
 * Ce fichier existe pour compatibilité avec les anciens imports
 */
const PreviewModal = (props) => {
  console.log('PDF Builder: PreviewModal legacy wrapper called with props:', props);

  // Si des props sont passées, on les utilise pour initialiser le context
  const initialData = props.elements || null;
  const initialMode = props.mode || 'canvas';

  return (
    <PreviewProvider>
      <PreviewModalWithContext
        legacyProps={props}
        initialData={initialData}
        initialMode={initialMode}
      />
    </PreviewProvider>
  );
};

// Composant interne qui gère la logique legacy
const PreviewModalWithContext = ({ legacyProps, initialData, initialMode }) => {
  const { openPreview, closePreview, isOpen } = usePreview();

  // Ouvrir automatiquement si des props legacy sont passées
  React.useEffect(() => {
    if (legacyProps && !isOpen) {
      openPreview(initialMode, initialData);
    }
  }, [legacyProps, initialMode, initialData, isOpen, openPreview]);

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
};

export default PreviewModal;
