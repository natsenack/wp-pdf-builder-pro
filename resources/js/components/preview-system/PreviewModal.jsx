import React, { useState, useEffect, useCallback } from 'react';
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
  const [isLoading, setIsLoading] = useState(false);
  const [previewData, setPreviewData] = useState(null);
  const [error, setError] = useState(null);
  const [templateElements, setTemplateElements] = useState(elements);

  // Sélection du mode de fonctionnement
  const currentMode = mode === 'metabox' ? MetaboxMode : CanvasMode;

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

        const result = await response.json();

        if (result.success && result.data && result.data.elements) {
          setTemplateElements(result.data.elements);
        } else {
          throw new Error(result.data?.message || 'Erreur lors du chargement des éléments du template');
        }
      } catch (err) {
        console.error('Erreur lors du chargement des éléments du template:', err);
        setError(err.message || 'Erreur lors du chargement du template');
      }
    };

    loadTemplateElements();
  }, [isOpen, mode, templateId, nonce]);

  // Chargement des données selon le mode
  useEffect(() => {
    if (!isOpen) return;

    const loadPreviewData = async () => {
      setIsLoading(true);
      setError(null);

      try {
        const data = await currentMode.loadData(templateElements, orderId, templateData);
        setPreviewData(data);
      } catch (err) {
        console.error('Erreur lors du chargement des données d\'aperçu:', err);
        setError(err.message || 'Erreur lors du chargement de l\'aperçu');
      } finally {
        setIsLoading(false);
      }
    };

    loadPreviewData();
  }, [isOpen, mode, templateElements, orderId, templateData, currentMode]);

  // Gestionnaire de fermeture
  const handleClose = useCallback(() => {
    setPreviewData(null);
    setError(null);
    onClose();
  }, [onClose]);

  if (!isOpen) return null;

  return (
    <div className="preview-modal-overlay" onClick={handleClose}>
      <div className="preview-modal-content" onClick={(e) => e.stopPropagation()}>
        {/* Header de la modale */}
        <div className="preview-modal-header">
          <h3>
            {mode === 'canvas' ? '🖼️ Aperçu Canvas' : '📄 Aperçu Commande'}
          </h3>
          <button
            className="preview-modal-close"
            onClick={handleClose}
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
            <PreviewRenderer
              elements={templateElements}
              previewData={previewData}
              mode={mode}
            />
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
};

export default PreviewModal;