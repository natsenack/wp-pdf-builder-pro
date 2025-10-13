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

  // Générer l'aperçu quand la modale s'ouvre
  useEffect(() => {
    if (isOpen && elements.length > 0) {
      generatePreview();
    }
  }, [isOpen, elements]);

  const generatePreview = async () => {
    setLoading(true);
    setError(null);

    try {
      console.log('Génération aperçu côté serveur pour', elements.length, 'éléments');

      // Vérifier que les variables AJAX sont disponibles
      let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;
      let nonce = window.pdfBuilderAjax?.nonce || pdfBuilderNonce;

      if (!ajaxUrl || !nonce) {
        throw new Error('Variables AJAX non disponibles. Rechargez la page.');
      }

      console.log('Variables AJAX utilisées:', { ajaxUrl: ajaxUrl.substring(0, 50) + '...', nonceLength: nonce.length });
      console.log('Valeur du nonce envoyé:', nonce);
      console.log('Timestamp envoi:', Date.now());

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_generate_preview');
      formData.append('nonce', nonce);
      formData.append('elements', JSON.stringify(elements));

      // Faire l'appel AJAX
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        console.log('Aperçu généré avec succès:', data.data);
        setPreviewData(data.data);
      } else {
        throw new Error(data.data || 'Erreur génération aperçu');
      }

    } catch (err) {
      console.error('Erreur génération aperçu:', err);
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handlePrint = () => {
    console.log('Génération PDF finale...');

    // Vérifier que les variables AJAX sont disponibles
    let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;
    let nonce = window.pdfBuilderAjax?.nonce || pdfBuilderNonce;

    if (!ajaxUrl || !nonce) {
      console.error('Variables AJAX manquantes:', { ajaxUrl, nonce });
      alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
      return;
    }

    // Préparer les données pour l'AJAX
    const formData = new FormData();
    formData.append('action', 'pdf_builder_generate_pdf');
    formData.append('nonce', nonce);
    formData.append('elements', JSON.stringify(elements));

    console.log('Envoi requête génération PDF...');

    // Afficher un indicateur de chargement
    const printButton = document.querySelector('.btn-primary');
    if (printButton) {
      const originalText = printButton.textContent;
      printButton.textContent = '⏳ Génération PDF...';
      printButton.disabled = true;
    }

    // Envoyer la requête AJAX
    fetch(ajaxUrl, {
      method: 'POST',
      body: formData
    })
    .then(response => {
      console.log('Réponse reçue:', response.status);
      if (!response.ok) {
        throw new Error('Erreur réseau: ' + response.status);
      }
      return response.json().catch(jsonError => {
        console.error('Erreur parsing JSON:', jsonError);
        throw new Error('Réponse invalide du serveur (pas du JSON)');
      });
    })
    .then(data => {
      console.log('Données reçues:', data);

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
    })
    .catch(error => {
      console.error('Erreur génération PDF:', error);
      alert('Erreur lors de la génération du PDF: ' + error.message);
    })
    .finally(() => {
      // Restaurer le bouton
      if (printButton) {
        printButton.textContent = '👁️ Imprimer PDF';
        printButton.disabled = false;
      }
    });
  };

  if (!isOpen) return null;

  return (
    <div className="preview-modal-overlay" onClick={onClose}>
      <div className="preview-modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="preview-modal-header">
          <h3>📄 Aperçu PDF - PDF Builder Pro v2.0</h3>
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

          {previewData && previewData.success && (
            <div className="preview-content">
              <div style={{
                textAlign: 'center',
                marginBottom: '20px',
                padding: '10px',
                background: '#e8f5e8',
                borderRadius: '4px',
                border: '1px solid #c3e6c3'
              }}>
                <strong>✅ Aperçu généré avec succès</strong><br/>
                <small>{previewData.elements_count} éléments • {previewData.width}×{previewData.height}px</small>
              </div>

              <div style={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'flex-start',
                minHeight: '400px'
              }}>
                <img
                  src={`data:image/png;base64,${previewData.preview}`}
                  alt="Aperçu PDF"
                  style={{
                    maxWidth: '100%',
                    maxHeight: '600px',
                    border: '1px solid #e2e8f0',
                    borderRadius: '8px',
                    boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)'
                  }}
                />
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