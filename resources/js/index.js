// PDF Builder Pro - Main Bundle
// Met à jour l'objet pdfBuilderPro existant avec React et les composants

console.log('🚀 PDF Builder Pro: Script starting...');

// Import React normalement - sera bundlé avec webpack
import React from 'react';
import ReactDOM from 'react-dom';

// Import the real PDFEditor component
import { PDFEditor } from './components/PDFEditor.jsx';

try {
  console.log('🚀 PDF Builder Pro: Main bundle starting...');
  console.error('🚀 PDF Builder Pro: Main bundle loading...');

  // Exposer React globalement pour la compatibilité
  if (typeof window !== 'undefined') {
    window.React = React;
    window.ReactDOM = ReactDOM;
    console.error('🚀 PDF Builder Pro: React exposed globally');
  }

  // CRÉER OU METTRE À JOUR L'OBJET GLOBAL avec React et les composants
  if (typeof window !== 'undefined') {
    console.error('🚀 PDF Builder Pro: Setting up PDFBuilder object');

    // Créer l'objet s'il n'existe pas
    if (!window.pdfBuilderPro) {
      window.pdfBuilderPro = {};
    }

    // Mettre à jour l'objet avec React
    window.pdfBuilderPro.React = React;
    window.pdfBuilderPro.ReactDOM = ReactDOM;

    // REMPLACER la méthode init existante par celle qui utilise React
    window.pdfBuilderPro.init = function(containerId, options = {}) {
    console.log('PDF Builder Pro init called for', containerId, 'with options:', options);
    console.log('Initial elements received:', options.initialElements, 'Count:', options.initialElements ? options.initialElements.length : 0);

    try {
      if (!React || !ReactDOM) {
        throw new Error('React not available for PDF Editor');
      }

      const container = document.getElementById(containerId);
      if (!container) {
        throw new Error('Container element \'' + containerId + '\' not found');
      }

      // Rendre le composant React directement
      ReactDOM.render(
        React.createElement(PDFEditor, {
          initialElements: options.initialElements || [],
          onSave: async (elements) => {
            console.log('PDF Editor saving elements:', elements);
            
            try {
              // Préparer les données pour la sauvegarde
              const saveData = {
                action: 'pdf_builder_save_template',
                template_data: JSON.stringify(elements),
                template_name: options.templateName || 'Template sans nom',
                template_id: options.templateId || 0,
                nonce: window.pdfBuilderPro ? window.pdfBuilderPro.nonce : ''
              };

              // Envoyer via AJAX
              const response = await fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(saveData)
              });

              const result = await response.json();
              
              if (result.success) {
                console.log('Template saved successfully:', result.data);
                // Afficher un message de succès si possible
                if (window.pdfBuilderPro && window.pdfBuilderPro.showNotice) {
                  window.pdfBuilderPro.showNotice('Template sauvegardé avec succès !', 'success');
                }
              } else {
                console.error('Save failed:', result.data);
                if (window.pdfBuilderPro && window.pdfBuilderPro.showNotice) {
                  window.pdfBuilderPro.showNotice('Erreur lors de la sauvegarde: ' + result.data, 'error');
                }
              }
            } catch (error) {
              console.error('Save error:', error);
              if (window.pdfBuilderPro && window.pdfBuilderPro.showNotice) {
                window.pdfBuilderPro.showNotice('Erreur réseau lors de la sauvegarde', 'error');
              }
            }
          },
          templateName: options.templateName || '',
          isNew: options.isNew || false
        }),
        container
      );

      console.error('🚀 PDF Builder Pro: React app initialized successfully');
      return true;

    } catch (error) {
      console.error('PDF Builder Pro init failed:', error);
      return false;
    }
  };

  console.error('🚀 PDF Builder Pro: pdfBuilderPro object updated with React');
} else {
  console.error('🚀 PDF Builder Pro: Warning - pdfBuilderPro object not found, creating new one');

  // Fallback: créer l'objet si pas déjà créé par le script loader
  const pdfBuilderPro = {
    version: '2.0.0',
    React: React,
    ReactDOM: ReactDOM,
    editors: new Map(),

    init: function(containerId, options = {}) {
      console.log('PDF Builder Pro init called for', containerId, 'with options:', options);

      try {
        if (!React || !ReactDOM) {
          throw new Error('React not available for PDF Editor');
        }

        const container = document.getElementById(containerId);
        if (!container) {
          throw new Error('Container element \'' + containerId + '\' not found');
        }

        // Rendre le composant React directement
        ReactDOM.render(
          React.createElement(PDFEditor, {
            initialElements: options.initialElements || [],
            onSave: (elements) => {
              console.log('PDF Editor saved elements:', elements);
              // TODO: Implement save logic
            },
            templateName: options.templateName || '',
            isNew: options.isNew || false
          }),
          container
        );

        console.error('🚀 PDF Builder Pro: React app initialized successfully');
        return true;

      } catch (error) {
        console.error('PDF Builder Pro init failed:', error);
        return false;
      }
    },

    destroy: function(containerId) {
      console.log('PDF Builder Pro destroy called for', containerId);
    },

    getData: function(containerId) {
      return null;
    },

    getElements: function() {
      return [];
    }
  };

  // Définir les variables globales
  window.pdfBuilderPro = pdfBuilderPro;
  window.PDFBuilderPro = pdfBuilderPro;
  window.PDFBuilder = {
    PDFEditor: PDFEditor,
    React: React,
    ReactDOM: ReactDOM
  };
  window.initializePDFBuilderPro = function() {
    console.error('🚀 PDF Builder Pro: initializePDFBuilderPro called');
    return pdfBuilderPro;
  };
}

console.error('🚀 PDF Builder Pro: Main bundle loaded successfully');
} catch (error) {
  console.error('🚀 PDF Builder Pro: Main bundle failed to load:', error);
}
