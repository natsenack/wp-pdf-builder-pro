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
      console.log('[PDF Builder] ===== INIT START ===== NEW VERSION WITH DEBUG LOGS =====');
      console.log('[PDF Builder] Container ID:', containerId);
      console.log('[PDF Builder] Options received:', options);

      // Debug logging for initialElements - FORCE REBUILD
      console.log('[PDF Builder] Checking initialElements:', options.initialElements, 'type:', typeof options.initialElements, 'isArray:', Array.isArray(options.initialElements));

      // Ensure initialElements is always an array
      if (options.initialElements && typeof options.initialElements === 'object' && !Array.isArray(options.initialElements)) {
        console.log('[PDF Builder] Converting initialElements object to array. Type:', typeof options.initialElements, 'Keys:', Object.keys(options.initialElements));
        const converted = Object.values(options.initialElements);
        console.log('[PDF Builder] Conversion result:', converted, 'Length:', converted.length);
        options.initialElements = converted;
        console.log('[PDF Builder] Converted initialElements object to array:', options.initialElements.length, 'elements');
      } else if (!options.initialElements) {
        options.initialElements = [];
        console.log('[PDF Builder] Set initialElements to empty array');
      } else {
        console.log('[PDF Builder] initialElements is already an array with length:', options.initialElements.length);
      }

      console.log('[PDF Builder] Initial elements count:', options.initialElements.length);

      if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
        console.log('PDF Builder Pro init called for', containerId, 'with options:', options);
        console.log('Initial elements received:', options.initialElements, 'Count:', options.initialElements ? options.initialElements.length : 0);
      }

      try {
        console.log('[PDF Builder] Checking React availability...');
        if (!React || !ReactDOM) {
          throw new Error('React not available for PDF Editor');
        }
        console.log('[PDF Builder] ✅ React available');

        console.log('[PDF Builder] Checking PDFEditor component...');
        if (!PDFEditor) {
          throw new Error('PDFEditor component not available');
        }
        console.log('[PDF Builder] ✅ PDFEditor component available');

        console.log('[PDF Builder] Finding container element...');
        const container = document.getElementById(containerId);
        if (!container) {
          throw new Error('Container element \'' + containerId + '\' not found');
        }
        console.log('[PDF Builder] ✅ Container found:', container);

        console.log('[PDF Builder] Preparing React element...');
        const reactElement = React.createElement(PDFEditor, {
          initialElements: options.initialElements || [],
          onSave: options.onSave || (() => {}),
          templateName: options.templateName || '',
          isNew: options.isNew || false,
          templateId: options.templateId || null
        });
        console.log('[PDF Builder] ✅ React element created');

        // Utiliser la nouvelle API React 18+ si disponible, sinon fallback vers render
        console.log('[PDF Builder] Checking ReactDOM API...');
        if (ReactDOM.createRoot) {
          console.log('[PDF Builder] Using React 18+ createRoot API');
          // React 18+ API
          const root = ReactDOM.createRoot(container);
          console.log('[PDF Builder] Root created, rendering...');
          root.render(reactElement);
          console.log('[PDF Builder] ✅ Rendered with createRoot');
        } else {
          console.log('[PDF Builder] Using legacy render API');
          // Legacy API pour compatibilité
          ReactDOM.render(reactElement, container);
          console.log('[PDF Builder] ✅ Rendered with legacy render');
        }

        console.log('[PDF Builder] ===== INIT SUCCESS =====');

        if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
          console.log('PDF Editor initialized successfully in container:', containerId);
          console.log('Initial elements count:', (options.initialElements || []).length);
        }

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
  }

  // AJOUTER la fonction manquante pour l'initialisation React
  window.pdfBuilderReact = window.pdfBuilderReact || {};
  window.pdfBuilderReact.initPDFBuilderReact = function() {
    console.log('[PDF Builder] ===== initPDFBuilderReact CALLED =====');

    // Vérifier que pdfBuilderData existe
    if (!window.pdfBuilderData) {
      console.error('[PDF Builder] ❌ pdfBuilderData not found on window');
      console.log('[PDF Builder] Available window keys with pdfBuilder:', Object.keys(window).filter(key => key.includes('pdfBuilder')));
      return false;
    }

    console.log('[PDF Builder] ✅ pdfBuilderData found:', window.pdfBuilderData);
    console.log('[PDF Builder] pdfBuilderData keys:', Object.keys(window.pdfBuilderData));

    // Si on a un templateId, charger les données du template
    if (window.pdfBuilderData.templateId && window.pdfBuilderData.templateId !== '0') {
      console.log('[PDF Builder] Template ID found, loading template data via AJAX...');

      // Faire un appel AJAX pour charger le template
      const formData = new FormData();
      formData.append('action', 'pdf_builder_load_template');
      formData.append('template_id', window.pdfBuilderData.templateId);
      formData.append('nonce', window.pdfBuilderData.nonce || '');

      fetch(window.pdfBuilderData.ajaxUrl, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('[PDF Builder] ✅ Template loaded successfully:', data.data);
          console.log('[PDF Builder] Template data structure:', typeof data.data.template, data.data.template);
          console.log('[PDF Builder] Template elements:', data.data.template?.elements);
          console.log('[PDF Builder] Template elements count:', data.data.template?.elements?.length || 0);

          // Préparer les options avec les données chargées
          let elements = data.data.template.elements;
          if (Array.isArray(elements)) {
            // Déjà un array
            elements = elements;
          } else if (typeof elements === 'object' && elements !== null) {
            // Objet avec IDs comme clés - convertir en array (fix for template loading)
            elements = Object.values(elements);
          } else {
            elements = [];
          }

          const options = {
            initialElements: elements,
            templateName: data.data.name || window.pdfBuilderData.templateName || '',
            isNew: false,
            templateId: window.pdfBuilderData.templateId
          };

          console.log('[PDF Builder] Prepared options with loaded template:', options);
          console.log('[PDF Builder] Initial elements type:', typeof options.initialElements, 'length:', options.initialElements.length);
          console.log('[PDF Builder] Calling window.pdfBuilderPro.init...');
          return window.pdfBuilderPro.init('pdf-builder-react-root', options);
        } else {
          console.error('[PDF Builder] ❌ Failed to load template:', data.data);
          // Fallback avec des données vides
          const options = {
            initialElements: [],
            templateName: window.pdfBuilderData.templateName || '',
            isNew: true,
            templateId: window.pdfBuilderData.templateId
          };
          console.log('[PDF Builder] Fallback options:', options);
          return window.pdfBuilderPro.init('pdf-builder-react-root', options);
        }
      })
      .catch(error => {
        console.error('[PDF Builder] ❌ AJAX error loading template:', error);
        // Fallback avec des données vides
        const options = {
          initialElements: [],
          templateName: window.pdfBuilderData.templateName || '',
          isNew: true,
          templateId: window.pdfBuilderData.templateId
        };
        console.log('[PDF Builder] Fallback options after error:', options);
        return window.pdfBuilderPro.init('pdf-builder-react-root', options);
      });

      return true; // L'appel AJAX est asynchrone, on retourne true immédiatement
    } else {
      console.log('[PDF Builder] No template ID, using empty template');

      // Préparer les options pour un nouveau template
      const options = {
        initialElements: [],
        templateName: window.pdfBuilderData.templateName || '',
        isNew: true,
        templateId: null
      };

      console.log('[PDF Builder] Prepared options for new template:', options);
      console.log('[PDF Builder] Calling window.pdfBuilderPro.init...');
      return window.pdfBuilderPro.init('pdf-builder-react-root', options);
    }
  };

} catch (error) {
  console.error('PDF Builder Pro: Main bundle failed to load:', error);
}
