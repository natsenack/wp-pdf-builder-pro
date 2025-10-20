import React, { useState, useEffect, useCallback, useMemo } from 'react';
// import { PreviewProvider } from './context/PreviewProvider';
// import { PreviewRenderer } from './PreviewRenderer';
// import { CanvasMode } from './modes/CanvasMode';
// import { MetaboxMode } from './modes/MetaboxMode';

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
  console.log('PDF Builder Debug: PreviewModal COMPONENT START - isOpen:', isOpen, 'timestamp:', Date.now());

  const [isLoading, setIsLoading] = useState(false);
  const [previewData, setPreviewData] = useState(null);
  const [error, setError] = useState(null);
  const [templateElements, setTemplateElements] = useState(elements);
  const [modalOpenTime, setModalOpenTime] = useState(Date.now()); // Timestamp d'ouverture du modal

  // Protection contre la fermeture automatique : 3 secondes minimum
  const isProtectedFromAutoClose = useMemo(() => {
    const elapsed = Date.now() - modalOpenTime;
    return elapsed < 3000; // 3 secondes de protection
  }, [modalOpenTime]);

  // Définition du mode courant utilisé pour charger les données (Canvas ou Metabox)
  const currentMode = useMemo(() => {
    // TEMP: Return a dummy object instead of imported modes
    return { loadData: async () => ({ elements: [], data: null }) };
  }, [mode]);

  // Handler de fermeture qui délègue à la prop onClose si fournie
  const handleClose = useCallback(() => {
    if (onClose && typeof onClose === 'function') {
      try {
        onClose();
      } catch (err) {
        console.error('PDF Builder Debug: onClose callback threw an error:', err);
      }
    }
  }, [onClose]);

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
    // Protection absolue contre la fermeture automatique pendant 3 secondes
    if (isProtectedFromAutoClose) {
      console.log('PDF Builder Debug: Blocking overlay close - protected period active');
      return;
    }
    console.log('PDF Builder Debug: Overlay clicked - closing modal');
    handleClose();
  }, [handleClose, isProtectedFromAutoClose]);

  // Gestionnaire de fermeture depuis le bouton - toujours autorisé
  const handleButtonClose = useCallback((e) => {
    console.log('PDF Builder Debug: Close button clicked - closing modal');
    e.stopPropagation(); // Prevent overlay close
    handleClose();
  }, [handleClose]);

  console.log('🚀🚀🚀 PREVIEW MODAL START - isOpen:', isOpen, 'mode:', mode, 'templateId:', templateId);
  console.log('🚀🚀🚀 STATE CHECK - isLoading:', isLoading, 'error:', !!error, 'previewData:', !!previewData, 'templateElements:', templateElements?.length || 'undefined');

  // LOG DIAGNOSTIC IMMÉDIAT APRÈS L'APPEL DE FONCTION
  console.log('🚀🚀🚀 RIGHT AFTER FUNCTION START - about to check isOpen condition');

  if (!isOpen) {
    console.log('❌❌❌ PreviewModal not rendering - isOpen is false');
    return null;
  }

  console.log('✅✅✅ isOpen is true, continuing to render');
  console.log('🚀🚀🚀 About to render JSX - final state check:', { isLoading, error: !!error, previewData: !!previewData, templateElements: templateElements?.length });

  try {
    console.log('🚀🚀🚀 About to return JSX from PreviewModal');

    // RENDU COMPLET AVEC LOGS DE DEBUG
    return (
      <div className="preview-modal-overlay" onClick={handleOverlayClose} style={{
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100vw',
        height: '100vh',
        backgroundColor: 'rgba(0,0,0,0.8)',
        zIndex: 999999,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }}>
        {console.log('🎨🎨🎨 OVERLAY RENDERED')}
        <div className="preview-modal-content" onClick={(e) => e.stopPropagation()} style={{
          backgroundColor: 'white',
          borderRadius: '12px',
          boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
          maxWidth: '90vw',
          maxHeight: '90vh',
          width: '1200px',
          height: '800px',
          display: 'flex',
          flexDirection: 'column',
          overflow: 'hidden'
        }}>
          {console.log('🎨🎨🎨 MODAL CONTENT RENDERED')}
          {/* Header de la modale */}
          <div className="preview-modal-header" style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            padding: '15px 20px',
            borderBottom: '1px solid #e1e5e9',
            backgroundColor: '#f8f9fa'
          }}>
            <h3 style={{
              margin: 0,
              fontSize: '18px',
              fontWeight: '600',
              color: '#2c3e50'
            }}>
              {mode === 'canvas' ? '🖼️ Aperçu Canvas' : '📄 Aperçu Commande'}
              {isProtectedFromAutoClose && (
                <span style={{
                  marginLeft: '12px',
                  fontSize: '11px',
                  color: '#28a745',
                  fontWeight: '500',
                  backgroundColor: '#d4edda',
                  padding: '2px 8px',
                  borderRadius: '12px',
                  border: '1px solid #c3e6cb'
                }}>
                  Protégé
                </span>
              )}
            </h3>
            <button
              className="preview-modal-close"
              onClick={handleButtonClose}
              title="Fermer l'aperçu"
              style={{
                background: 'none',
                border: 'none',
                fontSize: '24px',
                color: '#6c757d',
                cursor: 'pointer',
                padding: '0',
                width: '30px',
                height: '30px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                borderRadius: '4px',
                transition: 'all 0.2s ease'
              }}
              onMouseEnter={(e) => e.target.style.backgroundColor = '#f8f9fa'}
              onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
            >
              ×
            </button>
          </div>

          {/* Corps de la modale */}
          <div className="preview-modal-body" style={{
            flex: 1,
            overflow: 'auto',
            backgroundColor: '#f8f9fa'
          }}>
            {console.log('🎨🎨🎨 BODY RENDERED - checking conditions:', { isLoading, error: !!error, previewData: !!previewData })}
            {isLoading && (
              <div className="preview-loading" style={{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '60px 20px',
                minHeight: '300px'
              }}>
                {console.log('🎨🎨🎨 LOADING STATE RENDERED')}
                <div className="preview-spinner" style={{
                  width: '50px',
                  height: '50px',
                  border: '4px solid #f3f3f3',
                  borderTop: '4px solid #007cba',
                  borderRadius: '50%',
                  animation: 'spin 1s linear infinite',
                  marginBottom: '20px'
                }}></div>
                <h4 style={{
                  margin: '0 0 10px 0',
                  color: '#2c3e50',
                  fontSize: '16px',
                  fontWeight: '500'
                }}>
                  Chargement de l'aperçu...
                </h4>
                <p style={{
                  margin: 0,
                  color: '#6c757d',
                  fontSize: '14px',
                  textAlign: 'center'
                }}>
                  Récupération des données de commande et préparation de l'aperçu PDF
                </p>
              </div>
            )}

            {error && (
              <div className="preview-error">
                {console.log('🎨🎨🎨 ERROR STATE RENDERED:', error)}
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
                {console.log('🎨🎨🎨 CONTENT RENDERED - previewData exists')}
                <div style={{
                  padding: '30px',
                  textAlign: 'center',
                  background: 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)',
                  borderRadius: '8px',
                  border: '2px solid #007cba'
                }}>
                  <div style={{ fontSize: '48px', marginBottom: '20px' }}>✅</div>
                  <h3 style={{ color: '#007cba', marginBottom: '15px' }}>Aperçu PDF Builder - Fonctionnel !</h3>
                  <div style={{ background: 'white', padding: '20px', borderRadius: '6px', marginBottom: '15px' }}>
                    <p style={{ margin: '8px 0', fontWeight: 'bold' }}>
                      📊 État du système:
                    </p>
                    <p style={{ margin: '5px 0' }}>• Éléments chargés: {Array.isArray(templateElements) ? templateElements.length : 0}</p>
                    <p style={{ margin: '5px 0' }}>• Données d'aperçu: {previewData ? '✅ Chargées' : '❌ Manquantes'}</p>
                    <p style={{ margin: '5px 0' }}>• Mode: {mode === 'canvas' ? '🖼️ Exemple' : '📄 Réel'}</p>
                    <p style={{ margin: '5px 0' }}>• Commande ID: {orderId || 'N/A'}</p>
                  </div>
                  <p style={{ color: '#6c757d', fontSize: '14px', marginTop: '15px' }}>
                    🎉 La modal d'aperçu fonctionne correctement !<br />
                    Le système est prêt pour l'intégration du vrai rendu PDF.
                  </p>
                </div>
              </div>
            )}
          </div>

          {/* Footer avec informations */}
          <div className="preview-modal-footer" style={{
            padding: '12px 20px',
            borderTop: '1px solid #e1e5e9',
            backgroundColor: '#f8f9fa',
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center'
          }}>
            {console.log('🎨🎨🎨 FOOTER RENDERED')}
            <div className="preview-info" style={{
              display: 'flex',
              gap: '15px',
              alignItems: 'center'
            }}>
              <span className="preview-mode-badge" style={{
                backgroundColor: mode === 'canvas' ? '#e3f2fd' : '#d4edda',
                color: mode === 'canvas' ? '#1565c0' : '#155724',
                padding: '4px 12px',
                borderRadius: '16px',
                fontSize: '12px',
                fontWeight: '500'
              }}>
                {mode === 'canvas' ? '🖼️ Mode Exemple' : '📄 Mode Réel'}
              </span>
              <span className="preview-elements-count" style={{
                color: '#6c757d',
                fontSize: '13px'
              }}>
                {Array.isArray(templateElements) ? templateElements.length : 0} élément{Array.isArray(templateElements) && templateElements.length > 1 ? 's' : ''}
              </span>
            </div>
            <div className="preview-actions">
              <button
                className="preview-download-btn"
                disabled={isLoading || !!error}
                title="Télécharger le PDF"
                style={{
                  backgroundColor: (isLoading || !!error) ? '#6c757d' : '#007cba',
                  color: 'white',
                  border: 'none',
                  padding: '8px 16px',
                  borderRadius: '6px',
                  cursor: (isLoading || !!error) ? 'not-allowed' : 'pointer',
                  fontSize: '14px',
                  fontWeight: '500',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px',
                  transition: 'background-color 0.2s ease'
                }}
              >
                📥 PDF
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  } catch (renderError) {
    console.error('PDF Builder Debug: JSX render error:', renderError);
    console.error('PDF Builder Debug: Error stack:', renderError.stack);
    return (
      <div style={{
        position: 'fixed',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        background: 'white',
        padding: '20px',
        borderRadius: '8px',
        zIndex: 1000000
      }}>
        <h3>❌ Erreur de rendu JSX</h3>
        <p>{renderError.message}</p>
        <pre style={{ fontSize: '12px', color: 'red' }}>{renderError.stack}</pre>
        <button onClick={() => window.location.reload()}>Recharger</button>
      </div>
    );
  }
};

export default PreviewModal;