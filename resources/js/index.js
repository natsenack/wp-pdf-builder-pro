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
      console.log('[PDF Builder] 🎨 ===== PDF EDITOR INIT START =====');
      console.log('[PDF Builder] 📍 Container ID:', containerId);
      console.log('[PDF Builder] 📋 Options received:', options);
      console.log('[PDF Builder] 🏷️ Template name:', options.templateName || 'N/A');
      console.log('[PDF Builder] 🆔 Template ID:', options.templateId || 'N/A');
      console.log('[PDF Builder] 🆕 Is new template:', options.isNew);
      console.log('[PDF Builder] 🔢 Initial elements count:', options.initialElements?.length || 0);

      // Ensure initialElements is always an array
      if (options.initialElements && typeof options.initialElements === 'object' && !Array.isArray(options.initialElements)) {
        console.log('[PDF Builder] 🔄 Converting initialElements object to array in editor init');
        options.initialElements = Object.values(options.initialElements);
        console.log('[PDF Builder] ✅ Conversion completed, new count:', options.initialElements.length);
      } else if (!options.initialElements) {
        console.log('[PDF Builder] 📝 Setting empty initialElements array');
        options.initialElements = [];
      } else {
        console.log('[PDF Builder] ✅ initialElements already an array with length:', options.initialElements.length);
      }

      if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
        console.log('PDF Builder Pro init called for', containerId, 'with options:', options);
        console.log('Initial elements received:', options.initialElements, 'Count:', options.initialElements ? options.initialElements.length : 0);
      }

      try {
        console.log('[PDF Builder] 🔍 Checking React availability...');
        if (!React || !ReactDOM) {
          throw new Error('React not available for PDF Editor');
        }
        console.log('[PDF Builder] ✅ React available');

        console.log('[PDF Builder] 🔍 Checking PDFEditor component...');
        if (!PDFEditor) {
          throw new Error('PDFEditor component not available');
        }
        console.log('[PDF Builder] ✅ PDFEditor component available');

        console.log('[PDF Builder] 🔍 Finding container element...');
        const container = document.getElementById(containerId);
        if (!container) {
          throw new Error('Container element \'' + containerId + '\' not found');
        }
        console.log('[PDF Builder] ✅ Container found:', container);

        console.log('[PDF Builder] 🎨 Preparing React element for PDF Editor...');
        
        // Récupérer les paramètres canvas depuis les données globales
        const canvasSettings = window.pdfBuilderCanvasSettings || window.pdfBuilderData?.canvasSettings || {};
        console.log('[PDF Builder] 🎨 Canvas settings from global:', canvasSettings);
        
        const reactElement = React.createElement(PDFEditor, {
          initialElements: options.initialElements || [],
          onSave: options.onSave || (() => {}),
          templateName: options.templateName || '',
          isNew: options.isNew || false,
          templateId: options.templateId || null,
          canvasSettings: canvasSettings
        });
        console.log('[PDF Builder] ✅ React element created for template:', options.templateName);

        // Utiliser la nouvelle API React 18+ si disponible, sinon fallback vers render
        console.log('[PDF Builder] 🔧 Checking ReactDOM API...');
        if (ReactDOM.createRoot) {
          console.log('[PDF Builder] ✅ Using React 18+ createRoot API');
          // React 18+ API
          const root = ReactDOM.createRoot(container);
          console.log('[PDF Builder] 🚀 Rendering PDF Editor with createRoot...');
          root.render(reactElement);
          console.log('[PDF Builder] ✅ PDF Editor rendered successfully with createRoot');
        } else {
          console.log('[PDF Builder] ⚠️ Using legacy render API');
          // Legacy API fallback
          ReactDOM.render(reactElement, container);
          console.log('[PDF Builder] ✅ PDF Editor rendered successfully with legacy render');
        }

        console.log('[PDF Builder] 🎉 ===== PDF EDITOR INIT SUCCESS =====');
        console.log('[PDF Builder] 📊 Template loaded in editor:', options.templateName, 'with', options.initialElements.length, 'elements');

        if (window.pdfBuilderDebug || window.location.hostname === 'localhost') {
          console.log('PDF Editor initialized successfully in container:', containerId);
          console.log('Initial elements count:', (options.initialElements || []).length);
        }

        return true;
      } catch (error) {
        console.error('[PDF Builder] ❌ PDF Editor init failed:', error);
        console.error('[PDF Builder] 📋 Failed options:', options);
        throw error;
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
    console.log('[PDF Builder] 📋 Editor initialization started');
    console.log('[PDF Builder] 🔍 Checking for template data...');

    // Vérifier que pdfBuilderData existe
    if (!window.pdfBuilderData) {
      console.error('[PDF Builder] ❌ pdfBuilderData not found on window');
      console.log('[PDF Builder] Available window keys with pdfBuilder:', Object.keys(window).filter(key => key.includes('pdfBuilder')));
      return false;
    }

    console.log('[PDF Builder] ✅ pdfBuilderData found:', window.pdfBuilderData);
    console.log('[PDF Builder] pdfBuilderData keys:', Object.keys(window.pdfBuilderData));
    console.log('[PDF Builder] 📄 Template ID from data:', window.pdfBuilderData.templateId);
    console.log('[PDF Builder] 📝 Template Name from data:', window.pdfBuilderData.templateName);

    // Si on a un templateId, charger les données du template
    if (window.pdfBuilderData.templateId && window.pdfBuilderData.templateId !== '0') {
      console.log('[PDF Builder] 🎯 Template ID detected:', window.pdfBuilderData.templateId);
      console.log('[PDF Builder] 🔄 Starting AJAX template loading process...');
      console.log('[PDF Builder] 📡 AJAX URL:', window.pdfBuilderData.ajaxUrl);
      console.log('[PDF Builder] 🔐 Nonce:', window.pdfBuilderData.nonce);

      // Faire un appel AJAX pour charger le template
      const formData = new FormData();
      formData.append('action', 'pdf_builder_load_template');
      formData.append('template_id', window.pdfBuilderData.templateId);
      formData.append('nonce', window.pdfBuilderData.nonce || '');

      console.log('[PDF Builder] 📤 Sending AJAX request for template:', window.pdfBuilderData.templateId);

      fetch(window.pdfBuilderData.ajaxUrl, {
        method: 'POST',
        body: formData
      })
      .then(response => {
        console.log('[PDF Builder] 📥 AJAX response received, status:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('[PDF Builder] 📊 AJAX response parsed:', data);
        console.log('[PDF Builder] ✅/❌ Success flag:', data.success);

        if (data.success) {
          console.log('[PDF Builder] ✅ Template loaded successfully:', data.data);
          console.log('[PDF Builder] 📋 Template data structure:', typeof data.data.template, data.data.template);
          console.log('[PDF Builder] 🧩 Template elements:', data.data.template?.elements);
          console.log('[PDF Builder] 🔢 Template elements count:', data.data.template?.elements?.length || 0);
          console.log('[PDF Builder] 📛 Template name:', data.data.name);
          console.log('[PDF Builder] 🆔 Template ID:', data.data.id);

          // Préparer les options avec les données chargées
          let elements = data.data.template.elements;
          console.log('[PDF Builder] 🔄 Processing template elements...');
          console.log('[PDF Builder] 📊 Elements type before processing:', typeof elements);

          if (Array.isArray(elements)) {
            console.log('[PDF Builder] ✅ Elements already in array format');
            // Déjà un array
            elements = elements;
          } else if (typeof elements === 'object' && elements !== null) {
            console.log('[PDF Builder] 🔄 Converting object elements to array (template loading fix)');
            console.log('[PDF Builder] 📋 Object keys:', Object.keys(elements));
            // Objet avec IDs comme clés - convertir en array (fix for template loading)
            elements = Object.values(elements);
            console.log('[PDF Builder] ✅ Conversion completed, new length:', elements.length);
          } else {
            console.log('[PDF Builder] ⚠️ No valid elements found, using empty array');
            elements = [];
          }

          const options = {
            initialElements: elements,
            templateName: data.data.name || window.pdfBuilderData.templateName || '',
            isNew: false,
            templateId: window.pdfBuilderData.templateId
          };

          console.log('[PDF Builder] 🎯 Prepared options with loaded template:', options);
          console.log('[PDF Builder] 📊 Initial elements type:', typeof options.initialElements, 'length:', options.initialElements.length);
          console.log('[PDF Builder] 🚀 Calling window.pdfBuilderPro.init with template data...');
          console.log('[PDF Builder] 🎨 About to render PDF Editor with template:', options.templateName);
          return window.pdfBuilderPro.init('pdf-builder-react-root', options);
        } else {
          console.error('[PDF Builder] ❌ Failed to load template:', data.data);
          console.log('[PDF Builder] 🔄 Using fallback empty template...');
          // Fallback avec des données vides
          const options = {
            initialElements: [],
            templateName: window.pdfBuilderData.templateName || '',
            isNew: true,
            templateId: window.pdfBuilderData.templateId
          };
          console.log('[PDF Builder] 🎯 Fallback options:', options);
          console.log('[PDF Builder] 🚀 Calling window.pdfBuilderPro.init with fallback data...');
          return window.pdfBuilderPro.init('pdf-builder-react-root', options);
        }
      })
      .catch(error => {
        console.error('[PDF Builder] ❌ AJAX error loading template:', error);
        console.log('[PDF Builder] 🔄 Using fallback empty template after error...');
        // Fallback avec des données vides
        const options = {
          initialElements: [],
          templateName: window.pdfBuilderData.templateName || '',
          isNew: true,
          templateId: window.pdfBuilderData.templateId
        };
        console.log('[PDF Builder] 🎯 Fallback options after error:', options);
        console.log('[PDF Builder] 🚀 Calling window.pdfBuilderPro.init with error fallback data...');
        return window.pdfBuilderPro.init('pdf-builder-react-root', options);
      });

      return true; // L'appel AJAX est asynchrone, on retourne true immédiatement
    } else {
      console.log('[PDF Builder] 📝 No template ID found, creating new template');
      console.log('[PDF Builder] 🆕 Template ID is:', window.pdfBuilderData.templateId);

      // Préparer les options pour un nouveau template
      const options = {
        initialElements: [],
        templateName: window.pdfBuilderData.templateName || '',
        isNew: true,
        templateId: null
      };

      console.log('[PDF Builder] 🎯 Prepared options for new template:', options);
      console.log('[PDF Builder] 🚀 Calling window.pdfBuilderPro.init for new template...');
      return window.pdfBuilderPro.init('pdf-builder-react-root', options);
    }
  };

} catch (error) {
  console.error('PDF Builder Pro: Main bundle failed to load:', error);
}
