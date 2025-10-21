import React, { Suspense, lazy, useEffect } from 'react';
import { usePreviewContext } from '../context/PreviewContext';
import ModalSkeleton from './ModalSkeleton';

// Lazy loading des modes pour optimisation performance
// CanvasMode (modes/CanvasMode) fournit des helpers statiques -> on ajoute un wrapper par défaut
const CanvasMode = lazy(() => import('../modes/CanvasMode'));
// Utiliser la version composant de Metabox (components/MetaboxMode.jsx) pour que le module exporte bien un default React component
const MetaboxMode = lazy(() => import('../MetaboxMode'));

/**
 * PreviewModal - Composant principal du système d'aperçu modal
 * Optimisé avec lazy loading, error boundaries et performance
 */
function PreviewModal() {
  const {
    state: { isOpen, mode, loading, error },
    actions: { closePreview }
  } = usePreviewContext();

  // Debug: logger les changements d'état du contexte
  React.useEffect(() => {
    console.log('🔍 PreviewModalComponent - context state changed - isOpen:', isOpen, 'mode:', mode);
  }, [isOpen, mode]);

  // Gestionnaire d'échappement clavier
  useEffect(() => {
    if (!isOpen) return;

    const handleKeyDown = (event) => {
      if (event.key === 'Escape') {
        closePreview();
      }
    };

    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, closePreview]);

  // Gestionnaire de clic sur l'overlay
  const handleOverlayClick = (event) => {
    if (event.target === event.currentTarget) {
      closePreview();
    }
  };

  // Ne rien rendre si fermé ou si pas de mode valide
  if (!isOpen || !mode) return null;

  return (
    <div className="pdf-preview-modal-overlay" onClick={handleOverlayClick}>
      <div className="pdf-preview-modal-content">
        {/* Header avec contrôles de base */}
        <div className="pdf-preview-modal-header">
          <h3 className="pdf-preview-modal-title">
            Aperçu PDF {mode === 'canvas' ? '(Éditeur)' : '(Commande)'}
          </h3>
          <button
            className="pdf-preview-modal-close"
            onClick={closePreview}
            aria-label="Fermer l'aperçu"
            type="button"
          >
            ×
          </button>
        </div>

        {/* Zone de contenu avec lazy loading */}
        <div className="pdf-preview-modal-body">
          <Suspense fallback={<ModalSkeleton />}>
            {error ? (
              <div className="pdf-preview-error">
                <p>Erreur lors du chargement de l'aperçu :</p>
                <p className="error-message">{error}</p>
                <button
                  onClick={closePreview}
                  className="pdf-preview-error-close"
                >
                  Fermer
                </button>
              </div>
            ) : (
              <>
                {mode === 'canvas' && <CanvasMode />}
                {mode === 'metabox' && <MetaboxMode />}
              </>
            )}
          </Suspense>
        </div>

        {/* Indicateur de chargement global */}
        {loading && (
          <div className="pdf-preview-loading-overlay">
            <div className="pdf-preview-spinner"></div>
            <p>Génération de l'aperçu...</p>
          </div>
        )}
      </div>
    </div>
  );
}

// Optimisation avec React.memo pour éviter les re-renders inutiles
export default React.memo(PreviewModal);