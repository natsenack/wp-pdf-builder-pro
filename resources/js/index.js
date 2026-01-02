// PDF Builder Pro - Main Bundle
// Met à jour l'objet pdfBuilderPro existant avec React et les composants

// Import React normalement - sera bundlé avec webpack
import React from 'react';
import ReactDOM from 'react-dom';

// Import the real PDFEditor component
import { PDFEditor } from './components/PDFEditor.jsx';

try {
  // Exposer React globalement pour la compatibilité
  if (typeof window !== 'undefined') {
    window.React = React;
    window.ReactDOM = ReactDOM;
  }

  // CRÉER OU METTRE À JOUR L'OBJET GLOBAL avec React et les composants
  if (typeof window !== 'undefined') {

    // Créer l'objet s'il n'existe pas
    if (!window.pdfBuilderPro) {
      window.pdfBuilderPro = {};
    }

    // Mettre à jour l'objet avec React
    window.pdfBuilderPro.React = React;
    window.pdfBuilderPro.ReactDOM = ReactDOM;

    // REMPLACER la méthode init existante par celle qui utilise React
    window.pdfBuilderPro.init = function(containerId, options = {}) {
    if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
      console.log('PDF Builder Pro init called for', containerId, 'with options:', options);
      console.log('Initial elements received:', options.initialElements, 'Count:', options.initialElements ? options.initialElements.length : 0);
    }

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
            if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
              console.log('PDF Editor saving elements:', elements);
            }
            
            try {
              // Fonction pour nettoyer les éléments avant sérialisation
              const sanitizeForJSON = (obj) => {
                if (obj === null || typeof obj !== 'object') {
                  return obj;
                }
                
                if (Array.isArray(obj)) {
                  return obj.map(sanitizeForJSON);
                }
                
                const cleaned = {};
                for (const [key, value] of Object.entries(obj)) {
                  // Ignorer les fonctions, undefined, et les propriétés commençant par _
                  if (typeof value !== 'function' && value !== undefined && !key.startsWith('_')) {
                    cleaned[key] = sanitizeForJSON(value);
                  }
                }
                return cleaned;
              };
              
              const sanitizedElements = sanitizeForJSON(elements);
              if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
                console.log('Sanitized elements for JSON:', sanitizedElements);
              }
              
              // Utiliser la nouvelle fonction saveTemplate qui ne fait pas d'AJAX
              const saveResult = await onSave(elements);
              
              if (saveResult) {
                if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
                  console.log('Template data prepared successfully:', saveResult);
                }
                // Les données sont maintenant disponibles dans saveResult
                // L'appelant peut décider quoi faire avec (sauvegarde locale, export, etc.)
                if (window.pdfBuilderPro && window.pdfBuilderPro.showNotice) {
                  window.pdfBuilderPro.showNotice('Données du template préparées avec succès !', 'success');
                }
              } else {
                console.error('Save failed: no result returned');
                if (window.pdfBuilderPro && window.pdfBuilderPro.showNotice) {
                  window.pdfBuilderPro.showNotice('Erreur lors de la préparation des données', 'error');
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

      return true;

    } catch (error) {
      console.error('PDF Builder Pro init failed:', error);
      return false;
    }
  };

} else {
  console.error('Warning - pdfBuilderPro object not found, creating new one');

  // Fallback: créer l'objet si pas déjà créé par le script loader
  const pdfBuilderPro = {
    version: '2.0.0',
    React: React,
    ReactDOM: ReactDOM,
    editors: new Map(),

    init: function(containerId, options = {}) {
      if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
        console.log('PDF Builder Pro init called for', containerId, 'with options:', options);
      }

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
    return pdfBuilderPro;
  };

  // AJOUTER la fonction manquante pour l'initialisation React
  window.pdfBuilderReact = window.pdfBuilderReact || {};
  window.pdfBuilderReact.initPDFBuilderReact = function() {
    console.log('[PDF Builder] initPDFBuilderReact called');

    // Vérifier que pdfBuilderData existe
    if (!window.pdfBuilderData) {
      console.error('[PDF Builder] pdfBuilderData not found on window');
      return false;
    }

    console.log('[PDF Builder] pdfBuilderData found:', window.pdfBuilderData);

    // Préparer les options pour l'initialisation
    const options = {
      initialElements: window.pdfBuilderData.initialElements || [],
      templateName: window.pdfBuilderData.templateName || '',
      isNew: !window.pdfBuilderData.hasExistingData,
      templateId: window.pdfBuilderData.templateId || null
    };

    console.log('[PDF Builder] Initializing with options:', options);

    // Appeler la vraie fonction d'initialisation
    return window.pdfBuilderPro.init('pdf-builder-react-root', options);
  };

  // AJOUTER la fonction manquante pour l'initialisation React
  window.pdfBuilderReact = window.pdfBuilderReact || {};
  window.pdfBuilderReact.initPDFBuilderReact = function() {
    console.log('[PDF Builder] initPDFBuilderReact called');

    // Vérifier que pdfBuilderData existe
    if (!window.pdfBuilderData) {
      console.error('[PDF Builder] pdfBuilderData not found on window');
      return false;
    }

    console.log('[PDF Builder] pdfBuilderData found:', window.pdfBuilderData);

    // Préparer les options pour l'initialisation
    const options = {
      initialElements: window.pdfBuilderData.initialElements || [],
      templateName: window.pdfBuilderData.templateName || '',
      isNew: !window.pdfBuilderData.hasExistingData,
      templateId: window.pdfBuilderData.templateId || null
    };

    console.log('[PDF Builder] Initializing with options:', options);

    // Appeler la vraie fonction d'initialisation
    return window.pdfBuilderPro.init('pdf-builder-react-root', options);
  };
}

} catch (error) {
  console.error('PDF Builder Pro: Main bundle failed to load:', error);
}
