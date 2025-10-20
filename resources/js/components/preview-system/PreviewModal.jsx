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
                  padding: '20px',
                  background: '#f8f9fa',
                  borderRadius: '8px',
                  border: '2px solid #007cba',
                  minHeight: '400px'
                }}>
                  {/* En-tête de l'aperçu PDF */}
                  <div style={{
                    background: 'white',
                    padding: '15px',
                    borderRadius: '6px',
                    marginBottom: '20px',
                    border: '1px solid #dee2e6',
                    boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
                  }}>
                    <h3 style={{
                      margin: '0 0 10px 0',
                      color: '#007cba',
                      fontSize: '18px',
                      fontWeight: '600',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '8px'
                    }}>
                      📄 Aperçu PDF - {mode === 'canvas' ? 'Mode Exemple' : 'Commande Réelle'}
                      {orderId && <span style={{ fontSize: '14px', color: '#6c757d' }}>(ID: {orderId})</span>}
                    </h3>
                    <div style={{
                      display: 'flex',
                      gap: '15px',
                      fontSize: '13px',
                      color: '#6c757d'
                    }}>
                      <span>📊 {Array.isArray(templateElements) ? templateElements.length : 0} élément(s)</span>
                      <span>📅 {new Date().toLocaleDateString('fr-FR')}</span>
                      <span>⏰ {new Date().toLocaleTimeString('fr-FR')}</span>
                    </div>
                  </div>

                  {/* Zone de rendu PDF simulé */}
                  <div style={{
                    background: 'white',
                    border: '1px solid #dee2e6',
                    borderRadius: '6px',
                    padding: '20px',
                    minHeight: '300px',
                    position: 'relative',
                    boxShadow: '0 2px 8px rgba(0,0,0,0.1)'
                  }}>
                    {/* Simulation d'une page PDF A4 à échelle réduite */}
                    <div style={{
                      background: 'white',
                      width: '420px', // A4 width à 72 DPI ≈ 595px, réduit à 420px pour la modal
                      minHeight: '595px', // A4 height à 72 DPI ≈ 842px, réduit à 595px
                      margin: '0 auto',
                      padding: '20px', // Changé de mm à px pour cohérence
                      boxShadow: '0 0 10px rgba(0,0,0,0.2)',
                      border: '1px solid #e9ecef',
                      position: 'relative',
                      transform: 'scale(0.8)', // Échelle réduite pour mieux rentrer
                      transformOrigin: 'top center',
                      marginBottom: '-80px' // Compensation pour l'échelle
                    }}>
                      {/* En-tête de page */}
                      <div style={{
                        borderBottom: '2px solid #007cba',
                        paddingBottom: '10px', // Changé de mm à px
                        marginBottom: '15px', // Changé de mm à px
                        textAlign: 'center'
                      }}>
                        <h1 style={{
                          margin: '0',
                          color: '#007cba',
                          fontSize: '18pt', // Réduit pour l'échelle
                          fontWeight: 'bold'
                        }}>
                          {mode === 'canvas' ? 'APERÇU TEMPLATE' : 'FACTURE / BON DE COMMANDE'}
                        </h1>
                        <p style={{
                          margin: '5px 0 0 0', // Changé de mm à px
                          color: '#6c757d',
                          fontSize: '10pt' // Réduit pour l'échelle
                        }}>
                          Généré le {new Date().toLocaleDateString('fr-FR')} à {new Date().toLocaleTimeString('fr-FR')}
                        </p>
                      </div>

                      {/* Contenu basé sur les éléments du template */}
                      <div style={{ flex: 1 }}>
                        {mode === 'canvas' ? (
                          /* Mode Canvas : Afficher le rendu visuel de l'éditeur */
                          <div style={{ display: 'grid', gap: '30px' }}> {/* Changé de 15mm à 30px */}
                            <div style={{
                              background: 'white',
                              border: '2px solid #007cba',
                              borderRadius: '8px',
                              padding: '30px', // Changé de 15mm à 30px
                              boxShadow: '0 4px 12px rgba(0,123,186,0.1)'
                            }}>
                              <h2 style={{
                                margin: '0 0 20px 0', // Changé de 10mm à 20px
                                color: '#007cba',
                                fontSize: '18pt',
                                textAlign: 'center',
                                borderBottom: '1px solid #dee2e6',
                                paddingBottom: '10px' // Changé de 5mm à 10px
                              }}>
                                🖼️ Aperçu Visuel - Éditeur Canvas
                              </h2>

                              {/* Zone de rendu simulée A4 avec éléments positionnés */}
                              <div style={{
                                background: '#f8f9fa',
                                border: '1px solid #dee2e6',
                                borderRadius: '4px',
                                padding: '10px', // Changé de mm à px
                                position: 'relative',
                                minHeight: '400px' // Changé de mm à px
                              }}>
                                {/* Page A4 simulée à échelle réduite */}
                                <div style={{
                                  background: 'white',
                                  width: '420px', // A4 width réduite
                                  minHeight: '595px', // A4 height réduite
                                  margin: '0 auto',
                                  padding: '15px', // Changé de mm à px
                                  boxShadow: '0 0 8px rgba(0,0,0,0.15)',
                                  border: '1px solid #e9ecef',
                                  position: 'relative',
                                  overflow: 'hidden',
                                  transform: 'scale(0.8)', // Échelle réduite
                                  transformOrigin: 'top center',
                                  marginBottom: '-80px' // Compensation pour l'échelle
                                }}>
                                  {/* Grille de fond pour aider au positionnement */}
                                  <div style={{
                                    position: 'absolute',
                                    top: '15px', // Changé de mm à px
                                    left: '15px', // Changé de mm à px
                                    right: '15px', // Changé de mm à px
                                    bottom: '15px', // Changé de mm à px
                                    backgroundImage: `
                                      linear-gradient(rgba(0,123,186,0.1) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(0,123,186,0.1) 1px, transparent 1px)
                                    `,
                                    backgroundSize: '20px 20px', // Changé de mm à px (10mm ≈ 20px à l'échelle)
                                    pointerEvents: 'none',
                                    opacity: 0.3
                                  }} />

                                  {/* Éléments positionnés selon leurs coordonnées */}
                                  {Array.isArray(templateElements) && templateElements.length > 0 ? (
                                    templateElements.map((element, index) => {
                                      // Calculer les positions en pixels (conversion depuis les coordonnées du canvas)
                                      // À l'échelle 0.8, on ajuste les coordonnées pour qu'elles correspondent
                                      const scaleFactor = 0.8;
                                      const x = (element.x || 0) * scaleFactor;
                                      const y = (element.y || 0) * scaleFactor;
                                      const width = (element.width || 50) * scaleFactor;
                                      const height = (element.height || 20) * scaleFactor;

                                      return (
                                        <div
                                          key={index}
                                          style={{
                                            position: 'absolute',
                                            left: `${15 + x}px`, // 15px pour le padding de la page
                                            top: `${15 + y}px`,
                                            width: `${width}px`,
                                            minHeight: `${height}px`,
                                            background: element.type === 'text' ? '#e3f2fd' :
                                                       element.type === 'image' ? '#f3e5f5' :
                                                       element.type === 'rectangle' ? '#e8f5e8' :
                                                       (element.type && element.type.startsWith('woocommerce-')) ? '#efebe9' :
                                                       element.type === 'barcode' ? '#eceff1' :
                                                       '#fff3e0',
                                            border: `2px solid ${
                                              element.type === 'text' ? '#2196f3' :
                                              element.type === 'image' ? '#9c27b0' :
                                              element.type === 'rectangle' ? '#4caf50' :
                                              (element.type && element.type.startsWith('woocommerce-')) ? '#795548' :
                                              element.type === 'barcode' ? '#607d8b' :
                                              '#ff9800'
                                            }`,
                                            borderRadius: '3px',
                                            padding: '6px', // Changé de 3mm à 6px
                                            boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
                                            overflow: 'hidden',
                                            display: 'flex',
                                            flexDirection: 'column',
                                            justifyContent: 'center',
                                            alignItems: 'center',
                                            fontSize: '10pt', // Réduit pour l'échelle
                                            color: '#333',
                                            zIndex: index + 1
                                          }}
                                          title={`Élément ${index + 1} - ${element.type || 'Inconnu'} (${Math.round(x/0.8)}, ${Math.round(y/0.8)}) - ${Math.round(width/0.8)}x${Math.round(height/0.8)}px`}
                                        >
                                          {/* Indicateur de type d'élément */}
                                          <div style={{
                                            position: 'absolute',
                                            top: '4px', // Changé de 2mm à 4px
                                            right: '4px', // Changé de 2mm à 4px
                                            background: element.type === 'text' ? '#2196f3' :
                                                       element.type === 'image' ? '#9c27b0' :
                                                       element.type === 'rectangle' ? '#4caf50' :
                                                       (element.type && element.type.startsWith('woocommerce-')) ? '#795548' :
                                                       element.type === 'barcode' ? '#607d8b' :
                                                       '#ff9800',
                                            color: 'white',
                                            padding: '2px 4px', // Changé de 1mm 2mm à 2px 4px
                                            borderRadius: '4px', // Changé de 2mm à 4px
                                            fontSize: '7pt', // Réduit pour l'échelle
                                            fontWeight: 'bold'
                                          }}>
                                            {element.type === 'text' ? 'T' :
                                             element.type === 'image' ? '🖼️' :
                                             element.type === 'rectangle' ? '▭' :
                                             (element.type && element.type.startsWith('woocommerce-')) ? '🛒' :
                                             element.type === 'barcode' ? '▍' :
                                             '?'}
                                          </div>

                                          {/* Contenu de l'élément */}
                                          {element.type === 'text' && element.content ? (
                                            <div style={{
                                              fontSize: '10pt',
                                              textAlign: 'center',
                                              lineHeight: '1.3',
                                              wordBreak: 'break-word',
                                              fontFamily: element.fontFamily || 'Arial, sans-serif',
                                              fontWeight: element.fontWeight || 'normal',
                                              fontStyle: element.fontStyle || 'normal',
                                              color: element.color || '#333'
                                            }}>
                                              {typeof element.content === 'string' ?
                                                element.content.length > 30 ?
                                                  element.content.substring(0, 30) + '...' :
                                                  element.content :
                                                'Texte d\'exemple'
                                              }
                                            </div>
                                          ) : element.type === 'image' ? (
                                            <div style={{
                                              width: '100%',
                                              height: '60px',
                                              background: 'linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%)',
                                              backgroundSize: '8px 8px',
                                              backgroundPosition: '0 0, 0 4px, 4px -4px, -4px 0px',
                                              border: '2px dashed #ccc',
                                              borderRadius: '4px',
                                              display: 'flex',
                                              alignItems: 'center',
                                              justifyContent: 'center',
                                              color: '#666',
                                              fontSize: '20pt'
                                            }}>
                                              �
                                            </div>
                                          ) : element.type === 'rectangle' ? (
                                            <div style={{
                                              width: '100%',
                                              height: '100%',
                                              background: element.fillColor || element.backgroundColor || '#4caf50',
                                              borderRadius: element.borderRadius ? `${element.borderRadius}px` : '2px',
                                              border: element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor || '#333'}` : 'none'
                                            }} />
                                          ) : element.type === 'woocommerce-order-number' ? (
                                            <div style={{
                                              fontSize: '12pt',
                                              fontWeight: 'bold',
                                              textAlign: 'center',
                                              color: '#007cba'
                                            }}>
                                              #WC-2025-001
                                            </div>
                                          ) : element.type === 'woocommerce-billing-address' ? (
                                            <div style={{
                                              fontSize: '9pt',
                                              textAlign: 'left',
                                              lineHeight: '1.2'
                                            }}>
                                              Jean Dupont<br />
                                              123 Rue de la Paix<br />
                                              75001 Paris<br />
                                              France
                                            </div>
                                          ) : element.type === 'woocommerce-order-date' ? (
                                            <div style={{
                                              fontSize: '10pt',
                                              textAlign: 'center'
                                            }}>
                                              21/10/2025
                                            </div>
                                          ) : element.type === 'woocommerce-customer-name' ? (
                                            <div style={{
                                              fontSize: '11pt',
                                              textAlign: 'center',
                                              fontWeight: 'bold'
                                            }}>
                                              Jean Dupont
                                            </div>
                                          ) : element.type === 'woocommerce-products-table' ? (
                                            <div style={{
                                              fontSize: '8pt',
                                              width: '100%'
                                            }}>
                                              <div style={{ display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid #ccc', paddingBottom: '2px', marginBottom: '2px' }}>
                                                <span>Produit</span>
                                                <span>Qté</span>
                                                <span>Prix</span>
                                              </div>
                                              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                                <span>T-Shirt Blanc</span>
                                                <span>1</span>
                                                <span>25,00 €</span>
                                              </div>
                                            </div>
                                          ) : element.type === 'woocommerce-subtotal' ? (
                                            <div style={{
                                              fontSize: '10pt',
                                              textAlign: 'right'
                                            }}>
                                              Sous-total: 25,00 €
                                            </div>
                                          ) : element.type === 'woocommerce-taxes' ? (
                                            <div style={{
                                              fontSize: '10pt',
                                              textAlign: 'right'
                                            }}>
                                              TVA (20%): 5,00 €
                                            </div>
                                          ) : element.type === 'woocommerce-shipping' ? (
                                            <div style={{
                                              fontSize: '10pt',
                                              textAlign: 'right'
                                            }}>
                                              Livraison: 5,90 €
                                            </div>
                                          ) : element.type === 'woocommerce-order-total' ? (
                                            <div style={{
                                              fontSize: '12pt',
                                              fontWeight: 'bold',
                                              textAlign: 'right',
                                              color: '#007cba'
                                            }}>
                                              Total: 35,90 €
                                            </div>
                                          ) : (
                                            <div style={{
                                              fontSize: '14pt',
                                              opacity: 0.6,
                                              textAlign: 'center'
                                            }}>
                                              {element.type === 'line' ? '━' :
                                               element.type === 'circle' ? '○' :
                                               element.type === 'barcode' ? '▍▍▍▍▍' :
                                               element.type || 'Élément'}
                                            </div>
                                          )}

                                          {/* Dimensions en bas à droite */}
                                          <div style={{
                                            position: 'absolute',
                                            bottom: '4px', // Changé de 2mm à 4px
                                            right: '4px', // Changé de 2mm à 4px
                                            background: 'rgba(0,0,0,0.7)',
                                            color: 'white',
                                            padding: '2px', // Changé de 1mm à 2px
                                            borderRadius: '2px', // Changé de 1mm à 2px
                                            fontSize: '6pt' // Réduit pour l'échelle
                                          }}>
                                            {Math.round(width/0.8)}×{Math.round(height/0.8)}
                                          </div>
                                        </div>
                                      );
                                    })
                                  ) : (
                                    <div style={{
                                      position: 'absolute',
                                      top: '50%',
                                      left: '50%',
                                      transform: 'translate(-50%, -50%)',
                                      textAlign: 'center',
                                      color: '#6c757d'
                                    }}>
                                      <div style={{ fontSize: '38pt', marginBottom: '20px', opacity: 0.5 }}>🖼️</div> {/* Changé de 10mm à 20px */}
                                      <p style={{ margin: '0', fontSize: '11pt' }}> {/* Réduit pour l'échelle */}
                                        Aucun élément dans le canvas
                                      </p>
                                      <p style={{ margin: '10px 0 0 0', fontSize: '9pt' }}> {/* Changé de 5mm à 10px */}
                                        Ajoutez des éléments pour les voir positionnés ici
                                      </p>
                                    </div>
                                  )}
                                </div>

                                {/* Légende des couleurs */}
                                <div style={{
                                  marginTop: '20px', // Changé de 10mm à 20px
                                  padding: '16px', // Changé de 8mm à 16px
                                  background: 'white',
                                  borderRadius: '4px',
                                  border: '1px solid #dee2e6'
                                }}>
                                  <h4 style={{
                                    margin: '0 0 10px 0', // Changé de 5mm à 10px
                                    color: '#007cba',
                                    fontSize: '10pt' // Réduit pour l'échelle
                                  }}>
                                    📋 Légende des éléments :
                                  </h4>
                                  <div style={{
                                    display: 'grid',
                                    gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))', // Ajusté pour plus d'éléments
                                    gap: '6px',
                                    fontSize: '9pt'
                                  }}>
                                    <span><span style={{color:'#2196f3'}}>■</span> Texte</span>
                                    <span><span style={{color:'#9c27b0'}}>■</span> Image</span>
                                    <span><span style={{color:'#4caf50'}}>■</span> Rectangle</span>
                                    <span><span style={{color:'#ff9800'}}>■</span> Formes</span>
                                    <span><span style={{color:'#795548'}}>■</span> WooCommerce</span>
                                    <span><span style={{color:'#607d8b'}}>■</span> Codes-barres</span>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        ) : (
                          /* Mode Metabox : Afficher le JSON du template */
                          <div style={{ display: 'grid', gap: '20px' }}> {/* Changé de 10mm à 20px */}
                            <div style={{
                              background: 'white',
                              border: '2px solid #28a745',
                              borderRadius: '8px',
                              padding: '30px', // Changé de 15mm à 30px
                              boxShadow: '0 4px 12px rgba(40,167,69,0.1)'
                            }}>
                              <h2 style={{
                                margin: '0 0 20px 0', // Changé de 10mm à 20px
                                color: '#28a745',
                                fontSize: '18pt',
                                textAlign: 'center',
                                borderBottom: '1px solid #dee2e6',
                                paddingBottom: '10px' // Changé de 5mm à 10px
                              }}>
                                📄 Données JSON du Template
                              </h2>
                              <div style={{
                                background: '#f8f9fa',
                                border: '1px solid #dee2e6',
                                borderRadius: '4px',
                                padding: '20px', // Changé de 10mm à 20px
                                fontFamily: 'monospace',
                                fontSize: '9pt',
                                lineHeight: '1.4',
                                maxHeight: '300px', // Changé de 150mm à 300px
                                overflow: 'auto'
                              }}>
                                <div style={{
                                  background: '#28a745',
                                  color: 'white',
                                  padding: '4px 10px', // Changé de 2mm 5mm à 4px 10px
                                  borderRadius: '3px',
                                  marginBottom: '10px', // Changé de 5mm à 10px
                                  display: 'inline-block',
                                  fontSize: '10pt',
                                  fontWeight: 'bold'
                                }}>
                                  📋 Template JSON ({Array.isArray(templateElements) ? templateElements.length : 0} éléments)
                                </div>
                                <pre style={{
                                  margin: '0',
                                  whiteSpace: 'pre-wrap',
                                  wordBreak: 'break-word',
                                  color: '#495057'
                                }}>
                                  {Array.isArray(templateElements) && templateElements.length > 0 ?
                                    JSON.stringify(templateElements, null, 2) :
                                    '{\n  "template": [],\n  "message": "Aucune donnée JSON disponible"\n}'
                                  }
                                </pre>
                              </div>
                              {Array.isArray(templateElements) && templateElements.length > 0 && (
                                <div style={{
                                  marginTop: '20px', // Changé de 10mm à 20px
                                  padding: '16px', // Changé de 8mm à 16px
                                  background: '#d4edda',
                                  border: '1px solid #c3e6cb',
                                  borderRadius: '4px',
                                  fontSize: '10pt'
                                }}>
                                  <strong style={{ color: '#155724' }}>📊 Analyse du Template:</strong>
                                  <div style={{
                                    marginTop: '3mm',
                                    display: 'grid',
                                    gridTemplateColumns: 'repeat(auto-fit, minmax(60mm, 1fr))',
                                    gap: '3mm'
                                  }}>
                                    <span>• <strong>{templateElements.length}</strong> élément(s) total</span>
                                    <span>• <strong>{templateElements.filter(e => e.type).length}</strong> élément(s) typés</span>
                                    <span>• <strong>{templateElements.filter(e => e.content).length}</strong> élément(s) avec contenu</span>
                                    <span>• <strong>{templateElements.filter(e => e.x !== undefined && e.y !== undefined).length}</strong> élément(s) positionnés</span>
                                  </div>
                                </div>
                              )}
                            </div>
                          </div>
                        )}
                      </div>

                      {/* Pied de page */}
                      <div style={{
                        borderTop: '1px solid #dee2e6',
                        paddingTop: '10px', // Changé de mm à px
                        marginTop: '20px', // Changé de mm à px
                        textAlign: 'center',
                        fontSize: '8pt', // Réduit pour l'échelle
                        color: '#6c757d'
                      }}>
                        <p style={{ margin: '0' }}>
                          PDF Builder Pro - Aperçu généré automatiquement
                        </p>
                        <p style={{ margin: '2px 0 0 0' }}> {/* Changé de mm à px */}
                          Page 1 sur 1
                        </p>
                      </div>
                    </div>
                  </div>

                  {/* Informations techniques */}
                  <div style={{
                    marginTop: '15px',
                    padding: '10px',
                    background: mode === 'canvas' ? '#e7f3ff' : '#d4edda',
                    borderRadius: '4px',
                    fontSize: '12px',
                    color: mode === 'canvas' ? '#0066cc' : '#155724',
                    border: mode === 'canvas' ? '1px solid #b3d9ff' : '1px solid #c3e6cb'
                  }}>
                    <strong>{mode === 'canvas' ? '🖼️ Mode Canvas:' : '📄 Mode Metabox:'}</strong>
                    <div style={{ marginTop: '5px', display: 'flex', gap: '15px', flexWrap: 'wrap' }}>
                      <span>• Éléments: {Array.isArray(templateElements) ? templateElements.length : 0}</span>
                      <span>• Mode: {mode === 'canvas' ? 'Éditeur Visuel' : 'Données JSON'}</span>
                      <span>• Données: {previewData ? '✅ Chargées' : '❌ Manquantes'}</span>
                      <span>• Template: {templateId || 'N/A'}</span>
                      {mode === 'canvas' && (
                        <span>• Positionnés: {Array.isArray(templateElements) ? templateElements.filter(e => e.x !== undefined && e.y !== undefined).length : 0}</span>
                      )}
                      {mode === 'metabox' && (
                        <span>• Format: JSON</span>
                      )}
                    </div>
                  </div>
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