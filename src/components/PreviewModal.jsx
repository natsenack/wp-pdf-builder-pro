import React, { useState, useEffect } from 'react';

export const PreviewModal = ({ isOpen, onClose, templateData, canvasWidth, canvasHeight }) => {
  const [previewHtml, setPreviewHtml] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [customStyles, setCustomStyles] = useState({
    // Styles globaux
    backgroundColor: '#f8fafc',
    textColor: '#1a202c',
    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',

    // Styles par type d'élément
    header: {
      backgroundColor: '#667eea',
      textColor: '#ffffff',
      fontSize: 16,
      fontWeight: '600',
      borderRadius: 8,
      padding: 12
    },
    footer: {
      backgroundColor: '#2d3748',
      textColor: '#e2e8f0',
      fontSize: 14,
      fontWeight: '400',
      borderRadius: 6,
      padding: 10
    },
    text: {
      backgroundColor: 'rgba(255,255,255,0.9)',
      textColor: '#1a202c',
      fontSize: 14,
      fontWeight: '400',
      borderRadius: 4,
      padding: 8
    },
    container: {
      backgroundColor: 'rgba(102, 126, 234, 0.1)',
      textColor: '#667eea',
      fontSize: 14,
      fontWeight: '400',
      borderRadius: 6,
      padding: 12,
      borderStyle: 'dashed',
      borderWidth: 2,
      borderColor: '#667eea'
    }
  });
  const [showCustomizationPanel, setShowCustomizationPanel] = useState(false);

  useEffect(() => {
    if (isOpen && templateData) {
      generatePreview();
    }
  }, [isOpen, templateData]);

  const generatePreview = () => {
    setLoading(true);
    setError('');

    // Utiliser directement l'URL admin-ajax.php de WordPress
    const ajaxUrl = '/wp-admin/admin-ajax.php';

      console.log('PDF Builder Preview: Utilisation de l\'URL AJAX:', ajaxUrl);
      console.log('PDF Builder Preview: Données template:', templateData);

      // Validation et nettoyage des données avant sérialisation
      if (!templateData || !templateData.elements || !Array.isArray(templateData.elements)) {
        setError('Données template invalides: éléments manquants');
        setLoading(false);
        return;
      }

      // Fonction pour nettoyer les objets (supprimer les propriétés non sérialisables)
      const sanitizeObject = (obj) => {
        if (obj === null || typeof obj !== 'object') {
          return obj;
        }

        if (Array.isArray(obj)) {
          return obj.map(sanitizeObject);
        }

        const cleaned = {};
        for (const [key, value] of Object.entries(obj)) {
          // Ignorer les propriétés qui commencent par $ ou _ (internes React)
          if (key.startsWith('$') || key.startsWith('_')) {
            continue;
          }

          // Convertir les valeurs non sérialisables
          if (typeof value === 'function') {
            continue; // Ignorer les fonctions
          }

          cleaned[key] = sanitizeObject(value);
        }
        return cleaned;
      };

  // Générer le CSS personnalisé basé sur les styles choisis
  const generateCustomCSS = () => {
    const styles = customStyles;
    return `
        * { box-sizing: border-box; }
        body {
            font-family: ${styles.fontFamily};
            margin: 0;
            padding: 20px;
            background: ${styles.backgroundColor};
            color: ${styles.textColor};
            line-height: 1.5;
        }
        .pdf-page {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin: 0 auto 20px auto;
            position: relative;
            overflow: hidden;
        }
        .pdf-element {
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .pdf-element.layout-header {
            background: linear-gradient(135deg, ${styles.header.backgroundColor} 0%, ${styles.header.backgroundColor}99 100%) !important;
            color: ${styles.header.textColor} !important;
            font-size: ${styles.header.fontSize}px !important;
            font-weight: ${styles.header.fontWeight} !important;
            border-radius: ${styles.header.borderRadius}px !important;
            padding: ${styles.header.padding}px !important;
        }
        .pdf-element.layout-footer {
            background: ${styles.footer.backgroundColor} !important;
            color: ${styles.footer.textColor} !important;
            font-size: ${styles.footer.fontSize}px !important;
            font-weight: ${styles.footer.fontWeight} !important;
            border-radius: ${styles.footer.borderRadius}px !important;
            padding: ${styles.footer.padding}px !important;
        }
        .pdf-element.text {
            background: ${styles.text.backgroundColor} !important;
            color: ${styles.text.textColor} !important;
            font-size: ${styles.text.fontSize}px !important;
            font-weight: ${styles.text.fontWeight} !important;
            border-radius: ${styles.text.borderRadius}px !important;
            padding: ${styles.text.padding}px !important;
            border: 1px solid #e2e8f0;
        }
        .pdf-element.layout-container {
            background: ${styles.container.backgroundColor} !important;
            color: ${styles.container.textColor} !important;
            font-size: ${styles.container.fontSize}px !important;
            font-weight: ${styles.container.fontWeight} !important;
            border-radius: ${styles.container.borderRadius}px !important;
            padding: ${styles.container.padding}px !important;
            border: ${styles.container.borderWidth}px ${styles.container.borderStyle} ${styles.container.borderColor} !important;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-style: italic;
        }
        .pdf-element.rectangle {
            background: #e2e8f0;
            border: 2px solid #cbd5e0;
        }
        .pdf-element img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .pdf-placeholder {
            color: #a0aec0;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        @media print {
            body { background: white; }
            .pdf-page { box-shadow: none; border: none; }
        }
    `;
  };

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
          elements: templateData.elements.map(element => {
            const cleanedElement = sanitizeObject(element);
            return {
              type: cleanedElement.type || 'text',
              position: {
                x: parseFloat(cleanedElement.x) || 0,
                y: parseFloat(cleanedElement.y) || 0
              },
              size: {
                width: parseFloat(cleanedElement.width) || 100,
                height: parseFloat(cleanedElement.height) || 50
              },
              style: {
                color: cleanedElement.color || '#333333',
                fontSize: parseInt(cleanedElement.fontSize) || 14,
                fontWeight: cleanedElement.fontWeight || 'normal',
                fillColor: cleanedElement.backgroundColor || '#f8fafc',
                borderColor: cleanedElement.borderColor || '#e2e8f0',
                borderWidth: parseInt(cleanedElement.borderWidth) || 1
              },
              content: cleanedElement.text || cleanedElement.content || ''
            };
          })
        }]

      };

      // Ajouter les styles personnalisés aux données
      formattedData.customStyles = customStyles;

      console.log('PDF Builder Preview: Données formatées:', formattedData);

      let jsonString;
      try {
        jsonString = JSON.stringify(formattedData);
        console.log('PDF Builder Preview: JSON string:', jsonString);
      } catch (jsonError) {
        console.error('PDF Builder Preview: Erreur JSON:', jsonError);
        setError('Erreur de sérialisation JSON: ' + jsonError.message);
        setLoading(false);
        return;
      }

      // Préparer les données pour l'AJAX avec FormData (méthode WordPress standard)
      const formData = new FormData();
      formData.append('action', 'pdf_builder_preview');
      formData.append('nonce', pdfBuilderAjax.nonce);
      formData.append('template_data', jsonString);

      console.log('PDF Builder Preview: Envoi de la requête AJAX...');

      // Utiliser jQuery AJAX au lieu de fetch pour compatibilité WordPress
      return new Promise((resolve, reject) => {
        jQuery.ajax({
          url: ajaxUrl,
          type: 'POST',
          data: formData,
          processData: false, // Important pour FormData
          contentType: false, // Important pour FormData
          success: function(response) {
            console.log('PDF Builder Preview: Réponse jQuery AJAX:', response);
            if (response.success) {
              setPreviewHtml(response.data.html);
              setLoading(false);
              resolve();
            } else {
              console.error('PDF Builder Preview: Erreur dans la réponse:', response.data);
              setError(response.data || 'Une erreur inconnue est survenue');
              setLoading(false);
              reject(new Error(response.data || 'Une erreur inconnue est survenue'));
            }
          },
          error: function(xhr, status, error) {
            console.log('PDF Builder Preview: Erreur AJAX:', xhr.status, xhr.statusText);
            const errorText = xhr.responseText || 'Erreur HTTP ' + xhr.status;
            console.log('PDF Builder Preview: Contenu de l\'erreur:', errorText);
            setError('Erreur lors de l\'aperçu: ' + errorText);
            setLoading(false);
            reject(new Error('Erreur HTTP ' + xhr.status + ': ' + xhr.statusText));
          }
        });
      });
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
          <div className="preview-modal-actions">
            <button
              className={`preview-customize-btn ${showCustomizationPanel ? 'active' : ''}`}
              onClick={() => setShowCustomizationPanel(!showCustomizationPanel)}
              title="Personnaliser l'apparence"
            >
              🎨 Personnaliser
            </button>
            <button className="preview-modal-close" onClick={onClose}>×</button>
          </div>
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

          {showCustomizationPanel && (
            <div className="customization-panel">
              <h4>🎨 Personnalisation de l'Aperçu</h4>

              {/* Styles globaux */}
              <div className="customization-section">
                <h5>🌐 Styles Globaux</h5>
                <div className="customization-controls">
                  <div className="control-group">
                    <label>Couleur de fond:</label>
                    <input
                      type="color"
                      value={customStyles.backgroundColor}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        backgroundColor: e.target.value
                      }))}
                    />
                  </div>
                  <div className="control-group">
                    <label>Couleur du texte:</label>
                    <input
                      type="color"
                      value={customStyles.textColor}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        textColor: e.target.value
                      }))}
                    />
                  </div>
                </div>
              </div>

              {/* Styles des éléments */}
              <div className="customization-section">
                <h5>📄 En-tête</h5>
                <div className="customization-controls">
                  <div className="control-group">
                    <label>Couleur de fond:</label>
                    <input
                      type="color"
                      value={customStyles.header.backgroundColor}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        header: { ...prev.header, backgroundColor: e.target.value }
                      }))}
                    />
                  </div>
                  <div className="control-group">
                    <label>Couleur du texte:</label>
                    <input
                      type="color"
                      value={customStyles.header.textColor}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        header: { ...prev.header, textColor: e.target.value }
                      }))}
                    />
                  </div>
                  <div className="control-group">
                    <label>Taille police:</label>
                    <input
                      type="range"
                      min="12"
                      max="24"
                      value={customStyles.header.fontSize}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        header: { ...prev.header, fontSize: parseInt(e.target.value) }
                      }))}
                    />
                    <span>{customStyles.header.fontSize}px</span>
                  </div>
                </div>
              </div>

              <div className="customization-section">
                <h5>📋 Pied de page</h5>
                <div className="customization-controls">
                  <div className="control-group">
                    <label>Couleur de fond:</label>
                    <input
                      type="color"
                      value={customStyles.footer.backgroundColor}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        footer: { ...prev.footer, backgroundColor: e.target.value }
                      }))}
                    />
                  </div>
                  <div className="control-group">
                    <label>Couleur du texte:</label>
                    <input
                      type="color"
                      value={customStyles.footer.textColor}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        footer: { ...prev.footer, textColor: e.target.value }
                      }))}
                    />
                  </div>
                </div>
              </div>

              <div className="customization-section">
                <h5>📝 Éléments texte</h5>
                <div className="customization-controls">
                  <div className="control-group">
                    <label>Couleur de fond:</label>
                    <input
                      type="color"
                      value={customStyles.text.backgroundColor}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        text: { ...prev.text, backgroundColor: e.target.value }
                      }))}
                    />
                  </div>
                  <div className="control-group">
                    <label>Couleur du texte:</label>
                    <input
                      type="color"
                      value={customStyles.text.textColor}
                      onChange={(e) => setCustomStyles(prev => ({
                        ...prev,
                        text: { ...prev.text, textColor: e.target.value }
                      }))}
                    />
                  </div>
                </div>
              </div>

              <div className="customization-actions">
                <button
                  className="preview-regenerate-btn"
                  onClick={() => {
                    setLoading(true);
                    setError('');
                    generatePreview();
                  }}
                  disabled={loading}
                >
                  🔄 Actualiser l'aperçu
                </button>
              </div>
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