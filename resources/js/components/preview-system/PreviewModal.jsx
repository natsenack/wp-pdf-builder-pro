import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { PreviewRenderer } from './PreviewRenderer';
import { CanvasMode } from './modes/CanvasMode';
import { MetaboxMode } from './modes/MetaboxMode';

// DEBUG: Confirm deployment
console.log('PDF Builder: PreviewModal component loaded - deployment confirmed');

/**
 * Modal principal pour l'aperçu unifié PDF Builder Pro
 * Supporte deux modes : Canvas (données exemple) et Metabox (données réelles)
 */
const PreviewModal = ({
  isOpen,
  onClose,
  mode = 'canvas', // 'canvas' ou 'metabox'
  elements = [],
  orderId = null,
  templateData = {},
  templateId = null,
  nonce = null
}) => {
  console.log('PDF Builder Debug: PreviewModal render - isOpen:', isOpen, 'mode:', mode, 'templateId:', templateId, 'timestamp:', Date.now());

  const [isLoading, setIsLoading] = useState(false);
  const [previewData, setPreviewData] = useState(null);
  const [error, setError] = useState(null);
  const [templateElements, setTemplateElements] = useState(elements);
  const [preventAutoClose, setPreventAutoClose] = useState(true); // Protection contre la fermeture automatique

  // Désactiver la protection contre la fermeture automatique après le chargement
  useEffect(() => {
    if (!isLoading && previewData) {
      const timer = setTimeout(() => {
        setPreventAutoClose(false);
        console.log('PDF Builder Debug: Auto-close protection disabled');
      }, 1000); // Attendre 1 seconde après le chargement
      return () => clearTimeout(timer);
    }
  }, [isLoading, previewData]);

  // Chargement des éléments du template en mode metabox
  useEffect(() => {
    console.log('PDF Builder Debug: useEffect triggered - isOpen:', isOpen, 'mode:', mode, 'templateId:', templateId);

    if (!isOpen || mode !== 'metabox') {
      console.log('PDF Builder Debug: Skipping loadTemplateElements - condition not met');
      return;
    }

    const loadTemplateElements = async () => {
      console.log('PDF Builder Debug: loadTemplateElements called with templateId:', templateId);

      if (!templateId) {
        console.log('PDF Builder Debug: Template ID manquant');
        setError('ID du template manquant pour le mode metabox');
        return;
      }

      try {
        console.log('PDF Builder Debug: Making AJAX request to:', window.ajaxurl || '/wp-admin/admin-ajax.php');
        console.log('PDF Builder Debug: Request params:', {
          action: 'pdf_builder_get_canvas_elements',
          template_id: templateId,
          nonce: nonce || window.pdfBuilderPro?.nonce || ''
        });

        const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'pdf_builder_get_canvas_elements',
            template_id: templateId,
            nonce: nonce || window.pdfBuilderPro?.nonce || ''
          })
        });

        console.log('PDF Builder Debug: AJAX response status:', response.status);
        const result = await response.json();
        console.log('PDF Builder Debug: AJAX response data:', result);

        if (result.success && result.data && result.data.elements) {
          console.log('PDF Builder Debug: Elements loaded successfully:', result.data.elements.length, 'elements');
          console.log('PDF Builder Debug: Elements details:', result.data.elements);
          setTemplateElements(result.data.elements);
        } else {
          console.log('PDF Builder Debug: AJAX request failed:', result);
          console.log('PDF Builder Debug: Result data:', result.data);
          throw new Error(result.data?.message || 'Erreur lors du chargement des éléments du template');
        }
      } catch (err) {
        console.error('PDF Builder Debug: Exception during AJAX call:', err);
        console.error('Erreur lors du chargement des éléments du template:', err);
        setError(err.message || 'Erreur lors du chargement du template');
      }
    };

    loadTemplateElements();
  }, [isOpen, mode, templateId, nonce]);

  // Chargement des données selon le mode
  useEffect(() => {
    console.log('PDF Builder Debug: loadPreviewData useEffect triggered');
    console.log('PDF Builder Debug: Conditions - isOpen:', isOpen, 'templateElements:', templateElements?.length || 0);

    if (!isOpen || !templateElements || templateElements.length === 0) {
      console.log('PDF Builder Debug: Skipping preview data load - conditions not met');
      return;
    }

    const loadPreviewData = async () => {
      console.log('PDF Builder Debug: Starting preview data load');
      setIsLoading(true);
      setError(null);

      try {
        console.log('PDF Builder Debug: Calling currentMode.loadData with:', {
          elementsCount: templateElements.length,
          orderId: orderId,
          templateData: templateData
        });

        const data = await currentMode.loadData(templateElements, orderId, templateData);
        console.log('PDF Builder Debug: Preview data loaded successfully:', data);

        setPreviewData(data);
        console.log('PDF Builder Debug: Preview data set in state');
      } catch (err) {
        console.error('PDF Builder Debug: Error loading preview data:', err);
        console.error('Erreur lors du chargement des données d\'aperçu:', err);
        setError(err.message || 'Erreur lors du chargement de l\'aperçu');
      } finally {
        setIsLoading(false);
        console.log('PDF Builder Debug: Loading finished, isLoading set to false');
      }
    };

    loadPreviewData();
  }, [isOpen, templateElements, orderId, currentMode]);

  // Gestionnaire de fermeture depuis l'overlay - avec protection contre la fermeture automatique
  const handleOverlayClose = useCallback((e) => {
    // Empêcher la fermeture automatique pendant le chargement ou pendant la protection
    if (isLoading || preventAutoClose) {
      console.log('PDF Builder Debug: Preventing overlay close - loading:', isLoading, 'preventAutoClose:', preventAutoClose);
      return;
    }
    console.log('PDF Builder Debug: Overlay clicked - closing modal');
    handleClose();
  }, [handleClose, isLoading, preventAutoClose]);

  // Gestionnaire de fermeture depuis le bouton - toujours autorisé
  const handleButtonClose = useCallback((e) => {
    console.log('PDF Builder Debug: Close button clicked - closing modal');
    e.stopPropagation(); // Prevent overlay close
    handleClose();
  }, [handleClose]);

  if (!isOpen) {
    console.log('PDF Builder Debug: PreviewModal not rendering - isOpen is false');
    return null;
  }

  console.log('PDF Builder Debug: PreviewModal rendering JSX - isOpen:', isOpen, 'isLoading:', isLoading, 'error:', !!error, 'previewData:', !!previewData);

  try {
    return (
      <div className="preview-modal-overlay" onClick={handleOverlayClose}>
      <div className="preview-modal-content" onClick={(e) => e.stopPropagation()}>
        {/* Header de la modale */}
        <div className="preview-modal-header">
          <h3>
            {mode === 'canvas' ? '🖼️ Aperçu Canvas' : '📄 Aperçu Commande'}
          </h3>
          <button
            className="preview-modal-close"
            onClick={handleButtonClose}
            title="Fermer l'aperçu"
          >
            ×
          </button>
        </div>

        {/* Corps de la modale */}
        <div className="preview-modal-body">
          {isLoading && (
            <div className="preview-loading">
              <div className="preview-spinner"></div>
              <p>Chargement de l'aperçu...</p>
            </div>
          )}

          {error && (
            <div className="preview-error">
              <p>❌ {error}</p>
              <button
                onClick={() => window.location.reload()}
                className="preview-retry-btn"
              >
                Réessayer
              </button>
            </div>
          )}

          {!isLoading && !error && previewData && (
            <div className="preview-content">
              {(() => {
                try {
                  return (
                    <PreviewRenderer
                      elements={templateElements}
                      previewData={previewData}
                      mode={mode}
                    />
                  );
                } catch (rendererError) {
                  console.error('PDF Builder Debug: PreviewRenderer error:', rendererError);
                  return (
                    <div className="preview-renderer-error">
                      <p>❌ Erreur lors du rendu de l'aperçu</p>
                      <p>Détails: {rendererError.message}</p>
                      <button onClick={() => window.location.reload()}>
                        Recharger la page
                      </button>
                    </div>
                  );
                }
              })()}
            </div>
          )}
        </div>

        {/* Footer avec informations */}
        <div className="preview-modal-footer">
          <div className="preview-info">
            <span className="preview-mode-badge">
              {mode === 'canvas' ? 'Mode Exemple' : 'Mode Réel'}
            </span>
            <span className="preview-elements-count">
              {templateElements.length} élément{templateElements.length > 1 ? 's' : ''}
            </span>
          </div>
          <div className="preview-actions">
            <button
              className="preview-download-btn"
              disabled={isLoading || !!error}
              title="Télécharger le PDF"
            >
              📥 PDF
            </button>
          </div>
        </div>
      </div>
    </div>
  );
  } catch (renderError) {
    console.error('PDF Builder Debug: Error rendering PreviewModal JSX:', renderError);
    console.error('PDF Builder Debug: Render error details:', {
      isOpen,
      isLoading,
      error,
      previewData: !!previewData,
      templateElementsCount: templateElements?.length,
      mode
    });

    // Fallback en cas d'erreur de rendu
    return (
      <div className="preview-modal-overlay" onClick={handleOverlayClose}>
        <div className="preview-modal-content" onClick={(e) => e.stopPropagation()}>
          <div className="preview-modal-header">
            <h3>❌ Erreur d'aperçu</h3>
            <button
              className="preview-modal-close"
              onClick={handleButtonClose}
              title="Fermer l'aperçu"
            >
              ×
            </button>
          </div>
          <div className="preview-modal-body">
            <div className="preview-error">
              <p>Une erreur s'est produite lors du rendu de l'aperçu.</p>
              <p>Détails: {renderError.message}</p>
              <button
                onClick={() => window.location.reload()}
                className="preview-retry-btn"
              >
                Recharger la page
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }
};

export default PreviewModal;