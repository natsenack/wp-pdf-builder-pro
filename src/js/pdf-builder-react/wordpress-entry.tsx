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
  console.error('🚨🚨🚨 EXTREME_DEBUG_V3: initPDFBuilderReact CALLED at ' + new Date().toISOString() + ' 🚨🚨🚨');
  console.log('🚨🚨🚨 EXTREME_DEBUG_V3: initPDFBuilderReact CALLED at ' + new Date().toISOString() + ' 🚨🚨🚨');
  console.error('UNIQUE_DEBUG: initPDFBuilderReact called at ' + new Date().toISOString());

  // Debug: Check if container exists
  const container = document.getElementById('pdf-builder-react-root');
  console.error('UNIQUE_DEBUG: container element: ' + container);
  console.log('🔧 container element:', container);
  console.log('🔧 container found:', !!container);

  if (!container) {
    console.error('UNIQUE_DEBUG: Container element not found');
    console.log('PDF Builder React: Container element not found');
    console.log('🔧 Available elements with pdf-builder in ID:');
    const allElements = document.querySelectorAll('[id*="pdf-builder"]');
    allElements.forEach(el => console.log('  -', el.id, el));
    return false;
  }

  // Check if React has already been initialized
  const isInitialized = container.hasAttribute('data-react-initialized');
  console.error('UNIQUE_DEBUG: container already initialized: ' + isInitialized);
  console.log('🔧 container already initialized:', isInitialized);

  if (isInitialized) {
    console.error('UNIQUE_DEBUG: React already initialized');
    console.log('🔧 React already initialized');
    return true;
  }

  // Mark as initialized
  container.setAttribute('data-react-initialized', 'true');
  console.error('UNIQUE_DEBUG: Marked as initialized');
  console.log('🔧 Marked as initialized');

  // Masquer le loading et afficher l'éditeur
  const loadingEl = document.getElementById('pdf-builder-loader');
  const editorEl = document.getElementById('pdf-builder-editor-container');

  console.error('UNIQUE_DEBUG: loadingEl found: ' + !!loadingEl + ' editorEl found: ' + !!editorEl);
  console.log('🔧 loadingEl found:', !!loadingEl, 'editorEl found:', !!editorEl);

  if (loadingEl) loadingEl.style.display = 'none';
  if (editorEl) editorEl.style.display = 'block';

  try {
    console.error('UNIQUE_DEBUG: About to check if React is available');
    console.log('🔧 About to check if React is available');

    // Vérifier que React est disponible
    if (typeof React === 'undefined') {
      console.error('UNIQUE_DEBUG: React is not available');
      throw new Error('React is not loaded');
    }
    if (typeof createRoot === 'undefined') {
      console.error('UNIQUE_DEBUG: createRoot is not available');
      throw new Error('createRoot is not available');
    }

    console.error('UNIQUE_DEBUG: React is available, about to create React root');
    console.log('🔧 React is available, about to create React root');
    const root = createRoot(container);
    console.error('UNIQUE_DEBUG: Root created successfully');
    console.log('🔧 Root created successfully');

    console.error('UNIQUE_DEBUG: About to render PDFBuilder');
    console.log('🔧 About to render PDFBuilder');

    root.render(
      // ✅ Disabled StrictMode - it causes double rendering which messes up Canvas
      // In development, it can help catch bugs, but production needs single render
      <PDFBuilder />
    );
    console.error('UNIQUE_DEBUG: PDFBuilder rendered successfully');
    console.log('🔧 PDFBuilder rendered successfully');

    // Charger les données initiales du template s'il y en a
    const dataWindow = window as unknown as { pdfBuilderData?: { existingTemplate?: unknown } };
    const existingTemplate = dataWindow.pdfBuilderData?.existingTemplate;
    if (existingTemplate) {
      const tpl = existingTemplate as { id?: string; elements?: unknown[] };

      // Charger le template via l'API globale
      setTimeout(() => {
        loadTemplate(existingTemplate);
      }, 100);
    }

    return true;

  } catch (error) {
    console.error('UNIQUE_DEBUG: Error during React initialization:', error);
    alert('PDF Builder React: Initialization error: ' + error);
    console.log('PDF Builder React: Initialization error:', error);
    // Don't hide the container on error, so we can see it
    // container.innerHTML = '<p>Erreur lors de l\'initialisation de l\'éditeur React.</p>';
    // Remove the initialized flag on error
    container.removeAttribute('data-react-initialized');
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

