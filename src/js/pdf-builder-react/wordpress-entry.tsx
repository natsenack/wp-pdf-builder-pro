// LOG AU DÉBUT ABSOLU DU FICHIER REACT
console.log('⚛️⚛️⚛️ REACT_FILE_LOADED_V5: wordpress-entry.tsx STARTED EXECUTING at ' + new Date().toISOString());

// IMMEDIATE VISUAL INDICATOR - Add visible element to DOM
const debugDiv = document.createElement('div');
debugDiv.id = 'pdf-builder-debug-indicator';
debugDiv.style.cssText = `
  position: fixed;
  top: 10px;
  right: 10px;
  background: red;
  color: white;
  padding: 10px;
  border-radius: 5px;
  z-index: 999999;
  font-size: 14px;
  font-weight: bold;
  border: 2px solid black;
`;
debugDiv.textContent = '🚨 REACT SCRIPT LOADED 🚨 ' + new Date().toISOString();
document.body.appendChild(debugDiv);

// Also add to window
window['REACT_SCRIPT_LOADED'] = true;
window['REACT_LOAD_TIME'] = new Date().toISOString();

/**
 * PDF Builder React - Point d'entrée WordPress
 * Ce fichier est chargé par WordPress pour initialiser l'éditeur React
 */

// DEBUG: Log when script starts loading
console.log('🔧 DEBUG: wordpress-entry.tsx script started loading at ' + new Date().toISOString());
console.error('UNIQUE_GLOBAL_DEBUG: wordpress-entry.tsx script started loading at ' + new Date().toISOString());
debugLog('🔧 DEBUG: pdf-builder-react-wrapper.min.js script started loading');

import React from 'react';
import { createRoot } from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder';
import { debugError, debugWarn, debugLog } from './utils/debug';
import {
  registerEditorInstance,
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  resetAPI
} from './api/global-api';

// Fonction d'initialisation appelée par WordPress
declare global {
  interface Window {
    pdfBuilderReactInitData: {
      nonce: string;
      ajaxUrl: string;
      strings: {
        loading: string;
        error: string;
      };
    };
    initPDFBuilderReact: typeof initPDFBuilderReact;
    pdfBuilderReact: {
      initPDFBuilderReact: typeof initPDFBuilderReact;
      loadTemplate: typeof loadTemplate;
      getEditorState: typeof getEditorState;
      setEditorState: typeof setEditorState;
      getCurrentTemplate: typeof getCurrentTemplate;
      exportTemplate: typeof exportTemplate;
      saveTemplate: typeof saveTemplate;
      registerEditorInstance: typeof registerEditorInstance;
      resetAPI: typeof resetAPI;
      _isWebpackBundle: true;
    };
    // Notification functions
    showSuccessNotification?: (message: string, duration?: number) => void;
    showErrorNotification?: (message: string, duration?: number) => void;
    showWarningNotification?: (message: string, duration?: number) => void;
    showInfoNotification?: (message: string, duration?: number) => void;
  }
}

export function initPDFBuilderReact() {
  // Force an error to see if the function is called
  throw new Error('🚨🚨🚨 FORCED_ERROR: initPDFBuilderReact was called! 🚨🚨🚨');
  alert('🚨🚨🚨 ALERT_DEBUG: initPDFBuilderReact CALLED 🚨🚨🚨');
  console.error('🚨🚨🚨 EXTREME_DEBUG_V3: initPDFBuilderReact CALLED at ' + new Date().toISOString() + ' 🚨🚨🚨');
  console.log('🚨🚨🚨 EXTREME_DEBUG_V3: initPDFBuilderReact CALLED at ' + new Date().toISOString() + ' 🚨🚨🚨');
  console.error('UNIQUE_DEBUG: initPDFBuilderReact called at ' + new Date().toISOString());

  // Debug: Check if container exists
  console.log('🔍🔍🔍 DETAILED_LOG: Step 1 - Checking container element');
  const container = document.getElementById('pdf-builder-react-root');
  console.error('UNIQUE_DEBUG: container element: ' + container);
  console.log('🔧 container element:', container);
  console.log('🔧 container found:', !!container);
  console.log('🔍🔍🔍 DETAILED_LOG: Container check result:', !!container);

  if (!container) {
    alert('🚨🚨🚨 ALERT_DEBUG: Container element not found! 🚨🚨🚨');
    console.error('UNIQUE_DEBUG: Container element not found');
    console.log('PDF Builder React: Container element not found');
    console.log('🔧 Available elements with pdf-builder in ID:');
    const allElements = document.querySelectorAll('[id*="pdf-builder"]');
    allElements.forEach(el => console.log('  -', el.id, el));
    console.log('🔍🔍🔍 DETAILED_LOG: Returning false due to missing container');
    return false;
  }

  // Check if React has already been initialized
  console.log('🔍🔍🔍 DETAILED_LOG: Step 2 - Checking if already initialized');
  const isInitialized = container.hasAttribute('data-react-initialized');
  console.error('UNIQUE_DEBUG: container already initialized: ' + isInitialized);
  console.log('🔧 container already initialized:', isInitialized);
  console.log('🔍🔍🔍 DETAILED_LOG: Already initialized check result:', isInitialized);

  if (isInitialized) {
    console.error('UNIQUE_DEBUG: React already initialized');
    console.log('🔧 React already initialized');
    console.log('🔍🔍🔍 DETAILED_LOG: Returning true because already initialized');
    return true;
  }

  // Mark as initialized
  console.log('🔍🔍🔍 DETAILED_LOG: Step 3 - Marking as initialized');
  container.setAttribute('data-react-initialized', 'true');
  console.error('UNIQUE_DEBUG: Marked as initialized');
  console.log('🔧 Marked as initialized');

  // Masquer le loading et afficher l'éditeur
  console.log('🔍🔍🔍 DETAILED_LOG: Step 4 - Hiding loading and showing editor');
  const loadingEl = document.getElementById('pdf-builder-loader');
  const editorEl = document.getElementById('pdf-builder-editor-container');
  console.error('UNIQUE_DEBUG: loadingEl found: ' + !!loadingEl + ' editorEl found: ' + !!editorEl);
  console.log('🔧 loadingEl found:', !!loadingEl, 'editorEl found:', !!editorEl);
  console.log('🔍🔍🔍 DETAILED_LOG: Loading elements check - loadingEl:', !!loadingEl, 'editorEl:', !!editorEl);

  if (loadingEl) {
    loadingEl.style.display = 'none';
    console.log('🔍🔍🔍 DETAILED_LOG: Loading element hidden');
  }
  if (editorEl) {
    editorEl.style.display = 'block';
    console.log('🔍🔍🔍 DETAILED_LOG: Editor element shown');
  }

  try {
    console.log('🔍🔍🔍 DETAILED_LOG: Step 5 - Entering try block for React initialization');
    console.error('UNIQUE_DEBUG: About to check if React is available');
    console.log('🔧 About to check if React is available');

    // Vérifier que React est disponible
    console.log('🔍🔍🔍 DETAILED_LOG: Checking React availability');
    if (typeof React === 'undefined') {
      console.error('UNIQUE_DEBUG: React is not available');
      console.log('🔍🔍🔍 DETAILED_LOG: React is undefined, throwing error');
      throw new Error('React is not loaded');
    }
    console.log('🔍🔍🔍 DETAILED_LOG: React is available');

    if (typeof createRoot === 'undefined') {
      console.error('UNIQUE_DEBUG: createRoot is not available');
      console.log('🔍🔍🔍 DETAILED_LOG: createRoot is undefined, throwing error');
      throw new Error('createRoot is not available');
    }
    console.log('🔍🔍🔍 DETAILED_LOG: createRoot is available');

    console.error('UNIQUE_DEBUG: React is available, about to create React root');
    console.log('🔧 React is available, about to create React root');
    console.log('🔍🔍🔍 DETAILED_LOG: About to create React root');
    const root = createRoot(container);
    console.error('UNIQUE_DEBUG: Root created successfully');
    console.log('🔧 Root created successfully');
    console.log('🔍🔍🔍 DETAILED_LOG: React root created successfully');

    console.error('UNIQUE_DEBUG: About to render PDFBuilder');
    console.log('🔧 About to render PDFBuilder');
    console.log('🔍🔍🔍 DETAILED_LOG: About to render PDFBuilder component');

    root.render(
      // ✅ Disabled StrictMode - it causes double rendering which messes up Canvas
      // In development, it can help catch bugs, but production needs single render
      <PDFBuilder />
    );
    console.error('UNIQUE_DEBUG: PDFBuilder rendered successfully');
    console.log('🔧 PDFBuilder rendered successfully');
    console.log('🔍🔍🔍 DETAILED_LOG: PDFBuilder component rendered successfully');

    // Charger les données initiales du template s'il y en a
    console.log('🔍🔍🔍 DETAILED_LOG: Step 6 - Checking for existing template data');
    const dataWindow = window as unknown as { pdfBuilderData?: { existingTemplate?: unknown } };
    const existingTemplate = dataWindow.pdfBuilderData?.existingTemplate;
    console.log('🔍🔍🔍 DETAILED_LOG: Existing template found:', !!existingTemplate);

    if (existingTemplate) {
      const tpl = existingTemplate as { id?: string; elements?: unknown[] };
      console.log('🔍🔍🔍 DETAILED_LOG: Loading existing template via API');

      // Charger le template via l'API globale
      setTimeout(() => {
        loadTemplate(existingTemplate);
        console.log('🔍🔍🔍 DETAILED_LOG: Template load initiated');
      }, 100);
    }

    console.log('🔍🔍🔍 DETAILED_LOG: Initialization completed successfully, returning true');
    return true;

  } catch (error) {
    console.error('UNIQUE_DEBUG: Error during React initialization:', error);
    console.log('🔍🔍🔍 DETAILED_LOG: Error caught in try block:', error);
    alert('PDF Builder React: Initialization error: ' + error);
    console.log('PDF Builder React: Initialization error:', error);
    // Don't hide the container on error, so we can see it
    // container.innerHTML = '<p>Erreur lors de l\'initialisation de l\'éditeur React.</p>';
    // Remove the initialized flag on error
    container.removeAttribute('data-react-initialized');
    console.log('🔍🔍🔍 DETAILED_LOG: Removed initialized flag due to error');
    console.log('🔍🔍🔍 DETAILED_LOG: Returning false due to error');
    return false;
  }
}

// Déclarer l'interface globale pour TypeScript
// (Déjà déclarée plus haut)

// Export pour utilisation manuelle (WordPress l'appelle explicitement)
console.log('🔧 DEBUG: About to assign window.initPDFBuilderReact');
window.initPDFBuilderReact = initPDFBuilderReact;

// Exporter l'API complète pour WordPress
console.log('🔧 DEBUG: About to assign window.pdfBuilderReact');
window.pdfBuilderReact = {
  initPDFBuilderReact,
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  registerEditorInstance,
  resetAPI,
  _isWebpackBundle: true
};
console.log('🔧 DEBUG: window.pdfBuilderReact assigned:', window.pdfBuilderReact);

