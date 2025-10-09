import React from 'react';
import ReactDOM from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';
import './styles/editor.css';

// Classe principale pour l'éditeur PDF
class PDFBuilderPro {
  constructor() {
    this.version = '2.0.0';
    this.editors = new Map();
  }

  // Initialiser l'éditeur dans un conteneur
  init(containerId, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) {
      console.error('Container not found:', containerId);
      return;
    }

    // Options par défaut
    const defaultOptions = {
      templateId: null,
      isNew: true,
      width: 595, // A4 width in points
      height: 842, // A4 height in points
      zoom: 1,
      gridSize: 10,
      snapToGrid: true,
      ...options
    };

    // Créer l'éditeur React
    const editorElement = React.createElement(PDFCanvasEditor, {
      options: defaultOptions,
      onSave: (data) => this.handleSave(data),
      onPreview: (data) => this.handlePreview(data)
    });

    ReactDOM.render(editorElement, container);
    this.editors.set(containerId, { container, options: defaultOptions });
  }

  // Gérer la sauvegarde
  handleSave(data) {
    // Ici on pourrait envoyer les données au serveur
  }

  // Gérer l'aperçu
  handlePreview(data) {
    // Ouvrir une fenêtre d'aperçu avec les données du template
    const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
    
    if (previewWindow) {
      // Afficher un message de chargement
      previewWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Aperçu PDF - PDF Builder Pro</title>
          <style>
            body { 
              font-family: Arial, sans-serif; 
              text-align: center; 
              padding: 50px;
              background: #f5f5f5;
            }
            .loading { 
              font-size: 18px; 
              color: #666; 
            }
            .spinner {
              border: 4px solid #f3f3f3;
              border-top: 4px solid #3498db;
              border-radius: 50%;
              width: 40px;
              height: 40px;
              animation: spin 1s linear infinite;
              margin: 20px auto;
            }
            @keyframes spin {
              0% { transform: rotate(0deg); }
              100% { transform: rotate(360deg); }
            }
          </style>
        </head>
        <body>
          <div class="loading">
            <div class="spinner"></div>
            Génération de l'aperçu...
          </div>
        </body>
        </html>
      `);

      // Convertir les données du canvas au format attendu par le backend
      const templateData = {
        pages: [{
          size: {
            width: data.canvasWidth || 595,
            height: data.canvasHeight || 842
          },
          margins: {
            top: 20,
            right: 20,
            bottom: 20,
            left: 20
          },
          elements: data.elements.map(element => ({
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

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_pro_preview_pdf');
      formData.append('nonce', window.pdfBuilderAjax?.nonce || '');
      formData.append('template_data', JSON.stringify(templateData));

      // Faire l'appel AJAX
      fetch(window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          // Générer l'HTML d'aperçu avec les bonnes dimensions
          const html = `
            <!DOCTYPE html>
            <html>
            <head>
              <title>Aperçu PDF - PDF Builder Pro</title>
              <style>
                body { 
                  margin: 0; 
                  padding: 20px; 
                  background: #f5f5f5;
                  font-family: Arial, sans-serif;
                }
                .preview-container {
                  max-width: ${result.data.width + 40}px;
                  margin: 0 auto;
                  background: white;
                  box-shadow: 0 0 20px rgba(0,0,0,0.1);
                  border-radius: 8px;
                  overflow: hidden;
                }
                .preview-header {
                  background: #2c3e50;
                  color: white;
                  padding: 15px;
                  text-align: center;
                  font-weight: bold;
                }
                .preview-content {
                  padding: 20px;
                  min-height: ${result.data.height}px;
                  position: relative;
                }
                .preview-actions {
                  text-align: center;
                  padding: 15px;
                  background: #ecf0f1;
                  border-top: 1px solid #bdc3c7;
                }
                .btn {
                  background: #3498db;
                  color: white;
                  border: none;
                  padding: 10px 20px;
                  border-radius: 4px;
                  cursor: pointer;
                  margin: 0 5px;
                  text-decoration: none;
                  display: inline-block;
                }
                .btn:hover {
                  background: #2980b9;
                }
                .btn-secondary {
                  background: #95a5a6;
                }
                .btn-secondary:hover {
                  background: #7f8c8d;
                }
              </style>
            </head>
            <body>
              <div class="preview-container">
                <div class="preview-header">
                  📄 Aperçu PDF - PDF Builder Pro
                </div>
                <div class="preview-content">
                  ${result.data.html}
                </div>
                <div class="preview-actions">
                  <button class="btn" onclick="window.print()">🖨️ Imprimer</button>
                  <button class="btn btn-secondary" onclick="window.close()">❌ Fermer</button>
                </div>
              </div>
            </body>
            </html>
          `;
          
          previewWindow.document.open();
          previewWindow.document.write(html);
          previewWindow.document.close();
        } else {
          previewWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
              <title>Erreur - Aperçu PDF</title>
              <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; color: #e74c3c; }
              </style>
            </head>
            <body>
              <h1>❌ Erreur lors de la génération de l'aperçu</h1>
              <p>${result.data || 'Une erreur inconnue est survenue'}</p>
              <button onclick="window.close()">Fermer</button>
            </body>
            </html>
          `);
        }
      })
      .catch(error => {
        console.error('Erreur lors de l\'aperçu:', error);
        previewWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>Erreur - Aperçu PDF</title>
            <style>
              body { font-family: Arial, sans-serif; text-align: center; padding: 50px; color: #e74c3c; }
            </style>
          </head>
          <body>
            <h1>❌ Erreur de connexion</h1>
            <p>Impossible de contacter le serveur pour générer l'aperçu.</p>
            <p>Détails: ${error.message}</p>
            <button onclick="window.close()">Fermer</button>
          </body>
          </html>
        `);
      });
    } else {
      alert('Impossible d\'ouvrir la fenêtre d\'aperçu. Vérifiez que les popups ne sont pas bloqués.');
    }
  }

  // Détruire un éditeur
  destroy(containerId) {
    const editor = this.editors.get(containerId);
    if (editor) {
      ReactDOM.unmountComponentAtNode(editor.container);
      this.editors.delete(containerId);
    }
  }

  // Obtenir les données d'un éditeur
  getData(containerId) {
    // Cette méthode pourrait être étendue pour récupérer l'état actuel
    return null;
  }
}

// Instance globale
const pdfBuilderPro = new PDFBuilderPro();

// Attacher à window pour WordPress - avec vérification
if (typeof window !== 'undefined') {
  window.PDFBuilderPro = pdfBuilderPro;
  // Alias pour compatibilité
  window.pdfBuilderPro = pdfBuilderPro;
}

// Export pour les modules ES6
export default pdfBuilderPro;