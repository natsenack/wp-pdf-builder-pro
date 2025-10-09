import React, { useState, useEffect } from 'react';

export const PreviewModal = ({ isOpen, onClose, templateData, canvasWidth, canvasHeight }) => {
  const [previewHtml, setPreviewHtml] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (isOpen && templateData) {
      generatePreview();
    }
  }, [isOpen, templateData]);

  const generatePreview = async () => {
    setLoading(true);
    setError('');

    try {
      // Convertir les données du canvas au format attendu par le backend
      const formattedData = {
        pages: [{
          size: {
            width: canvasWidth || 595,
            height: canvasHeight || 842
          },
          margins: {
            top: 20,
            right: 20,
            bottom: 20,
            left: 20
          },
          elements: templateData.elements.map(element => ({
            type: element.type,
            position: {
              x: element.x,
              y: element.y
            },
            size: {
              width: element.width,
              height: element.height
            },
            style: {
              color: element.color || '#000000',
              fontSize: element.fontSize || 14,
              fontWeight: element.fontWeight || 'normal',
              fillColor: element.backgroundColor || 'transparent',
              borderColor: element.borderColor || '#000000',
              borderWidth: element.borderWidth || 0
            },
            content: element.text || element.content || ''
          }))
        }]
      };

      // Faire l'appel AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_pro_preview_pdf');
      formData.append('nonce', window.pdfBuilderAjax?.nonce || '');
      formData.append('template_data', JSON.stringify(formattedData));

      const response = await fetch(window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        setPreviewHtml(result.data.html);
      } else {
        setError(result.data || 'Une erreur inconnue est survenue');
      }
    } catch (err) {
      console.error('Erreur lors de l\'aperçu:', err);
      setError('Erreur de connexion: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  const handlePrint = () => {
    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Impression PDF</title>
          <style>
            body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
            .print-content { max-width: ${canvasWidth + 40}px; margin: 0 auto; }
          </style>
        </head>
        <body>
          <div class="print-content">
            ${previewHtml}
          </div>
        </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  };

  if (!isOpen) return null;

  return (
    <div className="preview-modal-overlay" onClick={onClose}>
      <div className="preview-modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="preview-modal-header">
          <h3>📄 Aperçu PDF - PDF Builder Pro</h3>
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
              <h4>❌ Erreur lors de la génération de l'aperçu</h4>
              <p>{error}</p>
            </div>
          )}

          {!loading && !error && previewHtml && (
            <div className="preview-content">
              <div
                className="preview-canvas"
                style={{
                  width: canvasWidth,
                  height: canvasHeight,
                  margin: '0 auto',
                  border: '1px solid #e2e8f0',
                  background: 'white',
                  position: 'relative',
                  overflow: 'hidden'
                }}
                dangerouslySetInnerHTML={{ __html: previewHtml }}
              />
            </div>
          )}
        </div>

        <div className="preview-modal-footer">
          <button className="btn btn-secondary" onClick={onClose}>
            ❌ Fermer
          </button>
          <button className="btn btn-primary" onClick={handlePrint} disabled={loading || error}>
            🖨️ Imprimer
          </button>
        </div>
      </div>
    </div>
  );
};