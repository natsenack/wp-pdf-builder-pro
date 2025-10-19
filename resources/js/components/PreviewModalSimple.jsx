import { useState, useEffect } from 'react';

// Aperçu simplifié - utilise uniquement le serveur pour générer l'aperçu

const PreviewModal = ({
  isOpen,
  onClose,
  elements = [],
  canvasWidth = 595,
  canvasHeight = 842,
  ajaxurl,
  pdfBuilderNonce
}) => {
  const [previewData, setPreviewData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fonction simplifiée pour nettoyer les éléments avant envoi
  const cleanElementsForJSON = (elements) => {
    return elements.map(element => {
      const cleaned = { ...element };
      // Supprimer seulement les propriétés non sérialisables de base
      delete cleaned.tempId;
      delete cleaned.isDragging;
      delete cleaned.isResizing;
      return cleaned;
    });
  };

  // Générer l'aperçu côté serveur uniquement
  const generateServerPreview = async () => {
    setLoading(true);
    setError(null);
    setPreviewData(null);

    try {
      // Nettoyer les éléments
      const cleanedElements = cleanElementsForJSON(elements);
      const jsonString = JSON.stringify(cleanedElements);

      // Obtenir un nonce frais
      const nonceFormData = new FormData();
      nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');

      const nonceResponse = await fetch(ajaxurl || window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: nonceFormData
      });

      if (!nonceResponse.ok) {
        throw new Error(`Erreur nonce: ${nonceResponse.status}`);
      }

      const nonceData = await nonceResponse.json();
      if (!nonceData.success) {
        throw new Error('Impossible d\'obtenir un nonce');
      }

      // Préparer l'appel pour l'aperçu unifié
      const formData = new FormData();
      formData.append('action', 'pdf_builder_unified_preview');
      formData.append('nonce', nonceData.data.nonce);
      formData.append('elements', jsonString);

      const response = await fetch(ajaxurl || window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.success && data.data && data.data.url) {
        setPreviewData({
          url: data.data.url,
          elements_count: elements.length,
          width: canvasWidth,
          height: canvasHeight
        });
      } else {
        throw new Error(data.data || 'Erreur génération aperçu');
      }

    } catch (error) {
      console.error('Erreur aperçu:', error);
      setError(error.message);
    } finally {
      setLoading(false);
    }
  };

  // Générer l'aperçu quand la modale s'ouvre
  useEffect(() => {
    if (isOpen && elements.length > 0) {
      generateServerPreview();
    }
  }, [isOpen, elements.length]);

  if (!isOpen) return null;

  return (
    <div
      style={{
        position: 'fixed',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        backgroundColor: 'rgba(0, 0, 0, 0.7)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 10000
      }}
      onClick={onClose}
    >
      <div
        style={{
          backgroundColor: 'white',
          borderRadius: '8px',
          width: '90%',
          maxWidth: '1200px',
          height: '90%',
          maxHeight: '800px',
          display: 'flex',
          flexDirection: 'column',
          boxShadow: '0 10px 30px rgba(0, 0, 0, 0.3)'
        }}
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div
          style={{
            padding: '20px',
            borderBottom: '1px solid #e2e8f0',
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center'
          }}
        >
          <h3 style={{ margin: 0, color: '#1e293b' }}>
            📄 Aperçu PDF
          </h3>
          <button
            onClick={onClose}
            style={{
              background: 'none',
              border: 'none',
              fontSize: '24px',
              cursor: 'pointer',
              color: '#64748b',
              padding: '0',
              width: '30px',
              height: '30px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}
          >
            ×
          </button>
        </div>

        {/* Body */}
        <div
          style={{
            flex: 1,
            padding: '20px',
            display: 'flex',
            flexDirection: 'column',
            overflow: 'hidden'
          }}
        >
          {loading && (
            <div
              style={{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                flex: 1,
                color: '#64748b'
              }}
            >
              <div
                style={{
                  width: '40px',
                  height: '40px',
                  border: '4px solid #e2e8f0',
                  borderTop: '4px solid #3b82f6',
                  borderRadius: '50%',
                  animation: 'spin 1s linear infinite',
                  marginBottom: '16px'
                }}
              />
              <p style={{ margin: 0 }}>Génération de l'aperçu...</p>
            </div>
          )}

          {error && (
            <div
              style={{
                backgroundColor: '#fef2f2',
                border: '1px solid #fecaca',
                borderRadius: '6px',
                padding: '16px',
                color: '#dc2626',
                textAlign: 'center'
              }}
            >
              <h4 style={{ margin: '0 0 8px 0' }}>❌ Erreur</h4>
              <p style={{ margin: 0 }}>{error}</p>
            </div>
          )}

          {previewData && !loading && (
            <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
              {/* Info */}
              <div
                style={{
                  backgroundColor: '#f8fafc',
                  border: '1px solid #e2e8f0',
                  borderRadius: '6px',
                  padding: '12px',
                  marginBottom: '16px',
                  fontSize: '14px',
                  color: '#475569'
                }}
              >
                <strong>{previewData.elements_count} élément{previewData.elements_count !== 1 ? 's' : ''}</strong> •
                Dimensions: {previewData.width}×{previewData.height}px
              </div>

              {/* Aperçu */}
              <div style={{ flex: 1, border: '1px solid #e2e8f0', borderRadius: '6px', overflow: 'hidden' }}>
                <iframe
                  src={previewData.url}
                  style={{
                    width: '100%',
                    height: '100%',
                    border: 'none'
                  }}
                  title="Aperçu PDF"
                />
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div
          style={{
            padding: '20px',
            borderTop: '1px solid #e2e8f0',
            display: 'flex',
            justifyContent: 'flex-end'
          }}
        >
          <button
            onClick={onClose}
            style={{
              backgroundColor: '#64748b',
              color: 'white',
              border: 'none',
              padding: '8px 16px',
              borderRadius: '4px',
              cursor: 'pointer',
              fontSize: '14px'
            }}
          >
            Fermer
          </button>
        </div>
      </div>
    </div>
  );
};

export default PreviewModal;