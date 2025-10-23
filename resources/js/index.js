// PDF Builder Pro - Main Bundle
// Met à jour l'objet pdfBuilderPro existant avec React et les composants

console.log('🚀 PDF Builder Pro: Main bundle starting...');
console.error('🚀 PDF Builder Pro: Main bundle loading...');

// Import React directement depuis node_modules
import React from 'react';
import ReactDOM from 'react-dom';

// Exposer React globalement pour la compatibilité
if (typeof window !== 'undefined') {
  window.React = React;
  window.ReactDOM = ReactDOM;
  console.error('🚀 PDF Builder Pro: React exposed globally');
}

// METTRE À JOUR L'OBJET GLOBAL EXISTANT avec React et les composants
if (typeof window !== 'undefined' && window.pdfBuilderPro) {
  console.error('🚀 PDF Builder Pro: Updating existing pdfBuilderPro object with React');

  // Mettre à jour l'objet existant avec React
  window.pdfBuilderPro.React = React;
  window.pdfBuilderPro.ReactDOM = ReactDOM;

  // REMPLACER la méthode init existante par celle qui utilise React
  window.pdfBuilderPro.init = function(containerId, options = {}) {
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
  window.initializePDFBuilderPro = function() {
    console.error('🚀 PDF Builder Pro: initializePDFBuilderPro called');
    return pdfBuilderPro;
  };
}

// Composant PDFEditor simplifié pour éviter les problèmes de code splitting
const PDFEditor = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  const [elements, setElements] = React.useState(initialElements);
  const [showPreview, setShowPreview] = React.useState(false);

  const handlePreview = () => {
    setShowPreview(true);
  };

  const handleSave = () => {
    if (onSave) {
      onSave(elements);
    }
  };

  return React.createElement('div', { className: 'pdf-editor-container', style: { width: '100%', height: '600px', border: '1px solid #ccc', borderRadius: '4px', display: 'flex', flexDirection: 'column' } }, [
    React.createElement('div', { key: 'toolbar', className: 'pdf-editor-toolbar', style: { padding: '10px', borderBottom: '1px solid #ccc', backgroundColor: '#f5f5f5', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } }, [
      React.createElement('div', { key: 'title', style: { fontWeight: 'bold' } }, 'PDF Builder Pro - ' + (templateName || 'Nouveau Template')),
      React.createElement('div', { key: 'actions', style: { display: 'flex', gap: '10px' } }, [
        React.createElement('button', { key: 'preview', onClick: handlePreview, style: { padding: '8px 16px', backgroundColor: '#007cba', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' } }, 'Aperçu'),
        React.createElement('button', { key: 'save', onClick: handleSave, style: { padding: '8px 16px', backgroundColor: '#28a745', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' } }, 'Sauvegarder')
      ])
    ]),
    React.createElement('div', { key: 'canvas', className: 'pdf-editor-canvas', style: { flex: 1, padding: '20px', backgroundColor: '#ffffff', overflow: 'auto' } }, [
      React.createElement('div', { key: 'placeholder', style: { textAlign: 'center', color: '#666', fontSize: '18px', marginTop: '100px' } }, 'Zone d\'édition PDF - Fonctionnalité en développement'),
      React.createElement('div', { key: 'elements-count', style: { marginTop: '20px', fontSize: '14px', color: '#999' } }, 'Éléments: ' + elements.length)
    ]),
    showPreview && React.createElement('div', { key: 'preview-modal', style: { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000 }, onClick: () => setShowPreview(false) }, [
      React.createElement('div', { style: { backgroundColor: 'white', padding: '20px', borderRadius: '8px', maxWidth: '600px', maxHeight: '80vh', overflow: 'auto' }, onClick: (e) => e.stopPropagation() }, [
        React.createElement('h3', { key: 'title' }, 'Aperçu du PDF'),
        React.createElement('p', { key: 'content' }, 'Template: ' + (templateName || 'Nouveau Template')),
        React.createElement('p', { key: 'elements' }, 'Nombre d\'éléments: ' + elements.length),
        React.createElement('button', { key: 'close', onClick: () => setShowPreview(false), style: { marginTop: '20px', padding: '8px 16px', backgroundColor: '#6c757d', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' } }, 'Fermer')
      ])
    ])
  ]);
};

console.error('🚀 PDF Builder Pro: Main bundle loaded successfully');
