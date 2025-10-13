import React, { useState, useEffect } from 'react';

// Nouveau système d'aperçu côté serveur avec TCPDF

const PreviewModal = ({
  isOpen,
  onClose,
  elements = [],
  canvasWidth = 595,
  canvasHeight = 842,
  zoom = 1,
  ajaxurl,
  pdfBuilderNonce
}) => {
  const [previewData, setPreviewData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fonction pour rendre le contenu du canvas en HTML
  const renderCanvasContent = (elements) => {
    if (!elements || elements.length === 0) {
      return <div style={{ padding: '20px', textAlign: 'center', color: '#666' }}>Aucun élément à afficher</div>;
    }

    return (
      <div
        style={{
          position: 'relative',
          width: canvasWidth,
          height: canvasHeight,
          backgroundColor: 'white',
          border: '1px solid #e2e8f0',
          borderRadius: '4px',
          overflow: 'hidden',
          transform: `scale(${zoom})`,
          transformOrigin: 'top left',
          margin: '0 auto'
        }}
      >
        {elements.map((element, index) => {
          const baseStyle = {
            position: 'absolute',
            left: element.x || 0,
            top: element.y || 0,
            width: element.width || 100,
            height: element.height || 50,
            zIndex: element.zIndex || index + 1
          };

          return (
            <div key={index} style={baseStyle}>
              {renderSpecialElement(element, zoom)}
            </div>
          );
        })}
      </div>
    );
  };

  // Fonction pour rendre un élément spécial (basée sur CanvasElement.jsx)
  const renderSpecialElement = (element, zoom) => {
    switch (element.type) {
      case 'text':
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              fontSize: element.fontSize || 16,
              color: element.color || '#000000',
              fontWeight: element.fontWeight === 'bold' ? 'bold' : 'normal',
              fontStyle: element.fontStyle === 'italic' ? 'italic' : 'normal',
              textAlign: element.textAlign || 'left',
              lineHeight: '1.2',
              whiteSpace: 'pre-wrap',
              overflow: 'hidden',
              padding: '4px',
              boxSizing: 'border-box'
            }}
          >
            {element.content || element.text || 'Texte'}
          </div>
        );

      case 'rectangle':
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              backgroundColor: element.fillColor || 'transparent',
              border: element.borderWidth
                ? `${element.borderWidth}px solid ${element.borderColor || '#000000'}`
                : 'none',
              borderRadius: element.borderRadius || 0
            }}
          />
        );

      case 'image':
        return (
          <img
            src={element.src || ''}
            alt={element.alt || 'Image'}
            style={{
              width: '100%',
              height: '100%',
              objectFit: 'cover'
            }}
            onError={(e) => {
              e.target.style.display = 'none';
            }}
          />
        );

      case 'line':
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              borderTop: `${element.strokeWidth || 1}px solid ${element.strokeColor || '#000000'}`,
              height: 0
            }}
          />
        );

      case 'divider':
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              backgroundColor: element.color || '#cccccc',
              height: `${element.thickness || 2}px`,
              margin: `${element.margin || 10}px 0`
            }}
          />
        );

      case 'product_table':
        // Rendu simplifié du tableau de produits
        return (
          <div style={{
            width: '100%',
            height: '100%',
            border: '1px solid #ddd',
            borderRadius: '4px',
            overflow: 'hidden',
            fontSize: '10px',
            backgroundColor: 'white'
          }}>
            <div style={{
              display: 'flex',
              backgroundColor: '#f5f5f5',
              padding: '4px',
              fontWeight: 'bold',
              borderBottom: '1px solid #ddd'
            }}>
              <div style={{ flex: 1 }}>Produit</div>
              <div style={{ width: '60px', textAlign: 'center' }}>Qté</div>
              <div style={{ width: '80px', textAlign: 'right' }}>Prix</div>
              <div style={{ width: '80px', textAlign: 'right' }}>Total</div>
            </div>
            <div style={{ padding: '4px', borderBottom: '1px solid #eee' }}>
              <div style={{ display: 'flex' }}>
                <div style={{ flex: 1 }}>Produit A - Description</div>
                <div style={{ width: '60px', textAlign: 'center' }}>2</div>
                <div style={{ width: '80px', textAlign: 'right' }}>19.99€</div>
                <div style={{ width: '80px', textAlign: 'right' }}>39.98€</div>
              </div>
            </div>
            <div style={{ padding: '4px', fontWeight: 'bold', textAlign: 'right' }}>
              Total: 39.98€
            </div>
          </div>
        );

      case 'customer_info':
        return (
          <div style={{
            padding: '8px',
            fontSize: '12px',
            lineHeight: '1.4'
          }}>
            <div style={{ fontWeight: 'bold', marginBottom: '4px' }}>Client</div>
            <div>Jean Dupont</div>
            <div>123 Rue de la Paix</div>
            <div>75001 Paris</div>
            <div>France</div>
          </div>
        );

      case 'company_info':
        return (
          <div style={{
            padding: '8px',
            fontSize: '12px',
            lineHeight: '1.4'
          }}>
            <div style={{ fontWeight: 'bold', marginBottom: '4px' }}>ABC Company SARL</div>
            <div>456 Avenue des Champs</div>
            <div>75008 Paris</div>
            <div>France</div>
            <div>Tél: 01 23 45 67 89</div>
          </div>
        );

      case 'company_logo':
        return (
          <div style={{
            width: '100%',
            height: '100%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: '8px',
            backgroundColor: element.backgroundColor || 'transparent'
          }}>
            {element.imageUrl ? (
              <img
                src={element.imageUrl}
                alt="Logo entreprise"
                style={{
                  maxWidth: '100%',
                  maxHeight: '100%',
                  objectFit: 'contain'
                }}
              />
            ) : (
              <div style={{
                width: '100%',
                height: '100%',
                backgroundColor: '#f0f0f0',
                border: '2px dashed #ccc',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: '#666',
                fontSize: '12px'
              }}>
                🏢 Logo
              </div>
            )}
          </div>
        );

      case 'order_number':
        return (
          <div style={{
            padding: '8px',
            fontSize: '14px',
            fontWeight: 'bold',
            color: element.color || '#333'
          }}>
            <div style={{ fontSize: '12px', color: '#666', marginBottom: '2px' }}>
              N° de commande:
            </div>
            <div>CMD-2025-00123</div>
          </div>
        );

      case 'document_type':
        return (
          <div style={{
            padding: '8px',
            fontSize: '18px',
            fontWeight: 'bold',
            color: element.color || '#1e293b',
            textAlign: 'center'
          }}>
            {element.documentType === 'invoice' ? 'FACTURE' :
             element.documentType === 'quote' ? 'DEVIS' :
             element.documentType === 'receipt' ? 'REÇU' :
             element.documentType === 'order' ? 'COMMANDE' :
             element.documentType === 'credit_note' ? 'AVOIR' : 'DOCUMENT'}
          </div>
        );

      case 'progress-bar':
        return (
          <div style={{
            width: '100%',
            height: '100%',
            backgroundColor: '#e5e7eb',
            borderRadius: '10px',
            overflow: 'hidden'
          }}>
            <div style={{
              width: `${element.progressValue || 75}%`,
              height: '100%',
              backgroundColor: element.progressColor || '#3b82f6',
              borderRadius: '10px'
            }} />
          </div>
        );

      default:
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              backgroundColor: '#f0f0f0',
              border: '1px dashed #ccc',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '12px',
              color: '#666',
              padding: '4px',
              boxSizing: 'border-box'
            }}
          >
            {element.type || 'Élément inconnu'}
          </div>
        );
    }
  };

  // Générer l'aperçu quand la modale s'ouvre
  useEffect(() => {
    if (isOpen && elements.length > 0) {
      // Afficher immédiatement le contenu du canvas
      setPreviewData({
        success: true,
        elements_count: elements.length,
        width: 400,
        height: 566,
        fallback: false
      });
      // Puis générer l'aperçu côté serveur en arrière-plan
      generatePreview();
    } else if (isOpen && elements.length === 0) {
      setPreviewData({
        success: true,
        elements_count: 0,
        width: 400,
        height: 566,
        fallback: false
      });
    }
  }, [isOpen, elements]);

  const generatePreview = async () => {
    // Ne pas définir loading=true car l'aperçu s'affiche déjà
    setError(null);

    try {
      console.log('Validation aperçu côté serveur pour', elements.length, 'éléments');

      // Vérifier que les variables AJAX sont disponibles
      let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;

      if (!ajaxUrl) {
        console.warn('Variables AJAX non disponibles pour validation côté serveur');
        return;
      }

      // Obtenir un nonce frais
      console.log('Obtention d\'un nonce frais pour validation...');
      const nonceFormData = new FormData();
      nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');

      const nonceResponse = await fetch(ajaxUrl, {
        method: 'POST',
        body: nonceFormData
      });

      if (!nonceResponse.ok) {
        console.warn('Erreur obtention nonce pour validation:', nonceResponse.status);
        return;
      }

      const nonceData = await nonceResponse.json();
      if (!nonceData.success) {
        console.warn('Impossible d\'obtenir un nonce frais pour validation');
        return;
      }

      const freshNonce = nonceData.data.nonce;
      console.log('Nonce frais obtenu pour validation:', freshNonce);

      console.log('Variables AJAX utilisées:', { ajaxUrl: ajaxUrl.substring(0, 50) + '...', nonceLength: freshNonce.length });
      console.log('Valeur du nonce envoyé:', freshNonce);
      console.log('Timestamp envoi:', Date.now());

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_generate_preview');
      formData.append('nonce', freshNonce);
      formData.append('elements', JSON.stringify(elements));

      // Faire l'appel AJAX en arrière-plan
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        console.warn('Erreur HTTP validation aperçu:', response.status);
        return;
      }

      const data = await response.json();

      if (data.success) {
        console.log('✅ Validation aperçu côté serveur réussie:', data.data);
        // Mettre à jour previewData avec les données du serveur si nécessaire
        setPreviewData(prev => ({
          ...prev,
          ...data.data,
          server_validated: true
        }));
      } else {
        console.warn('⚠️ Validation aperçu côté serveur échouée:', data.data);
        // Garder l'aperçu local mais marquer qu'il y a un problème serveur
        setPreviewData(prev => ({
          ...prev,
          server_error: data.data || 'Erreur validation serveur'
        }));
      }

    } catch (err) {
      console.warn('Erreur validation aperçu côté serveur:', err);
      // Ne pas afficher d'erreur car l'aperçu local fonctionne
      setPreviewData(prev => ({
        ...prev,
        server_error: err.message
      }));
    }
  };

  const handlePrint = async () => {
    console.log('Génération PDF finale...');

    let printButton = null;

    try {
      // Vérifier que les variables AJAX sont disponibles
      let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;

      if (!ajaxUrl) {
        alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
        return;
      }

      // Obtenir un nonce frais
      console.log('Obtention d\'un nonce frais pour PDF...');
      const nonceFormData = new FormData();
      nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');

      const nonceResponse = await fetch(ajaxUrl, {
        method: 'POST',
        body: nonceFormData
      });

      if (!nonceResponse.ok) {
        throw new Error(`Erreur HTTP nonce: ${nonceResponse.status}`);
      }

      const nonceData = await nonceResponse.json();
      if (!nonceData.success) {
        throw new Error('Impossible d\'obtenir un nonce frais');
      }

      const freshNonce = nonceData.data.nonce;
      console.log('Nonce frais obtenu pour PDF:', freshNonce);

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_generate_pdf');
      formData.append('nonce', freshNonce);
      formData.append('elements', JSON.stringify(elements));

      console.log('Envoi requête génération PDF...');

      // Afficher un indicateur de chargement
      printButton = document.querySelector('.btn-primary');
      if (printButton) {
        const originalText = printButton.textContent;
        printButton.textContent = '⏳ Génération PDF...';
        printButton.disabled = true;
      }

      // Envoyer la requête AJAX
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData
      });

      console.log('Réponse reçue:', response.status);
      if (!response.ok) {
        throw new Error('Erreur réseau: ' + response.status);
      }

      const data = await response.json().catch(jsonError => {
        console.error('Erreur parsing JSON:', jsonError);
        throw new Error('Réponse invalide du serveur (pas du JSON)');
      });

      console.log('Données reçues:', data);

      // 🚨🚨🚨 AFFICHAGE DES LOGS DE DEBUG SERVEUR 🚨🚨🚨
      if (data.data && data.data.debug_logs) {
        console.log('🚨 LOGS DE DEBUG SERVEUR ULTRA-VISIBLES:');
        data.data.debug_logs.forEach((log, index) => {
          console.log(`🔥 LOG ${index}: ${log}`);
        });
        console.log('🚨 FIN DES LOGS DE DEBUG SERVEUR 🚨');
      }

      if (!data.success) {
        let errorMessage = 'Erreur inconnue lors de la génération du PDF';
        if (typeof data.data === 'string') {
          errorMessage = data.data;
        } else if (typeof data.data === 'object' && data.data !== null) {
          errorMessage = data.data.message || JSON.stringify(data.data);
        }
        throw new Error(errorMessage);
      }

      if (!data.data || !data.data.pdf) {
        throw new Error('Données PDF manquantes dans la réponse');
      }

      // Convertir le PDF base64 en blob
      const pdfBase64 = data.data.pdf;
      const pdfBlob = new Blob(
        [Uint8Array.from(atob(pdfBase64), c => c.charCodeAt(0))],
        { type: 'application/pdf' }
      );

      console.log('Blob PDF créé, taille:', pdfBlob.size, 'bytes');

      if (pdfBlob.size === 0) {
        throw new Error('Le PDF généré est vide');
      }

      // Créer un URL pour le blob PDF
      const pdfUrl = URL.createObjectURL(pdfBlob);

      // Ouvrir le PDF dans une nouvelle fenêtre
      const previewWindow = window.open(pdfUrl, '_blank');

      if (!previewWindow) {
        // Fallback si le popup est bloqué
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }

      // Libérer l'URL du blob après un délai
      setTimeout(() => {
        URL.revokeObjectURL(pdfUrl);
      }, 1000);

      console.log('PDF généré et ouvert avec succès');

    } catch (error) {
      console.error('Erreur génération PDF:', error);
      alert('Erreur lors de la génération du PDF: ' + error.message);
    } finally {
      // Restaurer le bouton
      if (printButton) {
        printButton.textContent = '👁️ Imprimer PDF';
        printButton.disabled = false;
      }
    }
  };

  if (!isOpen) return null;

  return (
    <div className="preview-modal-overlay" onClick={onClose}>
      <div className="preview-modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="preview-modal-header">
          <h3>🎨 Aperçu Canvas - PDF Builder Pro v2.0</h3>
          <button className="preview-modal-close" onClick={onClose}>×</button>
        </div>

        <div className="preview-modal-body">
          {loading && (
            <div className="preview-loading">
              <div className="preview-spinner"></div>
              <p>Génération de l'aperçu...</p>
            </div>
          )}

          {error && (
            <div className="preview-error">
              <h4>❌ Erreur d'aperçu</h4>
              <p>{error}</p>
              <p><small>Le PDF pourra quand même être généré normalement.</small></p>
            </div>
          )}

          {previewData && (
            <div className="preview-content">
              <div style={{
                textAlign: 'center',
                marginBottom: '20px',
                padding: '10px',
                background: previewData.server_validated ? '#e8f5e8' : '#fff3cd',
                borderRadius: '4px',
                border: `1px solid ${previewData.server_validated ? '#c3e6c3' : '#ffeaa7'}`
              }}>
                <strong>{previewData.server_validated ? '✅' : '⚡'} Aperçu généré</strong><br/>
                <small>
                  {previewData.elements_count} élément{previewData.elements_count !== 1 ? 's' : ''} • {previewData.width}×{previewData.height}px
                  {previewData.server_validated && ' • Serveur validé'}
                  {previewData.server_error && ' • ⚠️ Problème serveur'}
                </small>
              </div>

              <div style={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'flex-start',
                minHeight: '400px',
                backgroundColor: '#f8f9fa',
                borderRadius: '8px',
                padding: '20px'
              }}>
                {renderCanvasContent(elements)}
              </div>

              {previewData.server_error && (
                <div style={{
                  marginTop: '20px',
                  padding: '15px',
                  backgroundColor: '#ffeaa7',
                  borderRadius: '6px',
                  border: '1px solid #d4a574'
                }}>
                  <h5 style={{ margin: '0 0 10px 0', color: '#856404' }}>⚠️ Note</h5>
                  <p style={{ margin: '0', fontSize: '14px', color: '#333' }}>
                    L'aperçu s'affiche correctement, mais il y a un problème de validation côté serveur: {previewData.server_error}
                  </p>
                </div>
              )}

              <div style={{
                marginTop: '20px',
                padding: '15px',
                backgroundColor: '#e8f4fd',
                borderRadius: '6px',
                border: '1px solid #b3d9ff'
              }}>
                <h5 style={{ margin: '0 0 10px 0', color: '#0066cc' }}>ℹ️ Informations du Canvas</h5>
                <p style={{ margin: '0', fontSize: '14px', color: '#333' }}>
                  <strong>Dimensions:</strong> {canvasWidth} × {canvasHeight} pixels<br/>
                  <strong>Éléments:</strong> {elements.length}<br/>
                  <strong>Zoom:</strong> {Math.round(zoom * 100)}%<br/>
                  <strong>Status:</strong> {previewData.server_validated ? 'Validé côté serveur' : 'Aperçu local'}
                </p>
              </div>
            </div>
          )}

          {!loading && !error && !previewData && (
            <div className="preview-loading">
              <p>Préparation de l'aperçu...</p>
            </div>
          )}
        </div>

        <div className="preview-modal-footer">
          <button className="btn btn-secondary" onClick={onClose}>
            ❌ Fermer
          </button>
          <button className="btn btn-primary" onClick={handlePrint}>
            👁️ Imprimer PDF
          </button>
        </div>
      </div>
    </div>
  );
};

export default PreviewModal;