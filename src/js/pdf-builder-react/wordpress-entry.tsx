// LOG AU DÉBUT ABSOLU DU FICHIER REACT
console.log('⚛️⚛️⚛️ REACT_FILE_LOADED_V5: wordpress-entry.tsx STARTED EXECUTING at ' + new Date().toISOString());
console.error('🚨🚨🚨 CRITICAL: React script execution started - if you see this, script is running');

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
(window as any)['REACT_SCRIPT_LOADED'] = true;
(window as any)['REACT_LOAD_TIME'] = new Date().toISOString();

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
  // LOG CRITIQUE - DÉBUT
  console.log('💥 NUCLEAR_DEBUG_V1: initPDFBuilderReact STARTED');

  try {
    // Step 1: Check container
    const container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 Container found:', !!container);

    if (!container) {
      console.error('❌ FAIL: Container element not found');
      return false;
    }

    // Step 2: Check if already initialized
    const isInitialized = container.hasAttribute('data-react-initialized');
    console.log('🔍 Already initialized:', isInitialized);

    if (isInitialized) {
      console.log('✅ SUCCESS: Already initialized');
      return true;
    }

    // Step 3: Mark as initialized
    container.setAttribute('data-react-initialized', 'true');
    console.log('✅ Container marked as initialized');

    // Step 4: Show editor, hide loading
    const loadingEl = document.getElementById('pdf-builder-loader');
    const editorEl = document.getElementById('pdf-builder-editor-container');
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    console.log('🔄 UI updated: loading hidden, editor shown');

    // Step 5: Initialize React
    console.log('⚛️ Checking React availability');

    if (typeof React === 'undefined') {
      console.error('❌ FAIL: React not loaded');
      return false;
    }

    if (typeof createRoot === 'undefined') {
      console.error('❌ FAIL: createRoot not available');
      return false;
    }

    console.log('✅ React ready, creating root');
    const root = createRoot(container);
    console.log('✅ Root created successfully');

    console.log('🎨 Rendering PDFBuilder component');
    console.log('🎨 PDFBuilder component available:', typeof PDFBuilder);
    console.log('🎨 PDFBuilder import successful');

    // Try to render with error boundary
    try {
      console.log('🎨 Attempting to render PDFBuilder...');
      root.render(<PDFBuilder />);
      console.log('✅ PDFBuilder rendered successfully');
    } catch (renderError) {
      const error = renderError instanceof Error ? renderError : new Error(String(renderError));
      console.error('❌ FAIL: PDFBuilder render error:', error);
      console.error('❌ FAIL: Render error stack:', error.stack);
      console.error('❌ FAIL: Render error message:', error.message);
      console.error('❌ FAIL: Render error name:', error.name);

      // Try to render a simple fallback component
      try {
        console.log('🔄 Trying fallback render...');
        root.render(
          <div style={{ padding: '20px', background: '#ffebee', border: '1px solid #f44336', borderRadius: '4px', color: '#c62828' }}>
            <h3>Erreur de rendu React</h3>
            <p>Le composant PDFBuilder n'a pas pu être rendu. Erreur: {error.message}</p>
            <details>
              <summary>Détails de l'erreur</summary>
              <pre>{error.stack}</pre>
            </details>
          </div>
        );
        console.log('✅ Fallback render successful');
        return true; // Return true since we rendered something
      } catch (fallbackError) {
        const fallbackErr = fallbackError instanceof Error ? fallbackError : new Error(String(fallbackError));
        console.error('❌ FAIL: Fallback render also failed:', fallbackErr);
        container.removeAttribute('data-react-initialized');
        return false;
      }
    }

    // Charger les données initiales du template s'il y en a
    // Step 6: Load template data if available
    const dataWindow = window as unknown as { pdfBuilderData?: { existingTemplate?: unknown } };
    const existingTemplate = dataWindow.pdfBuilderData?.existingTemplate;

    if (existingTemplate) {
      console.log('📄 Loading existing template');
      setTimeout(() => {
        try {
          loadTemplate(existingTemplate);
          console.log('✅ Template loaded');
        } catch (templateError) {
          console.error('❌ Template load error:', templateError);
        }
      }, 100);
    } else {
      console.log('📄 No existing template');
    }

    console.log('🎉 SUCCESS: initPDFBuilderReact completed');
    return true;

  } catch (error) {
    const err = error instanceof Error ? error : new Error(String(error));
    console.error('❌ FAIL: React initialization error:', err);
    console.error('❌ FAIL: Error stack:', err.stack);

    // Try to remove initialization flag if container exists
    const container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.removeAttribute('data-react-initialized');
    }

    return false;
  }
}

// Déclarer l'interface globale pour TypeScript
// (Déjà déclarée plus haut)

// Export pour utilisation manuelle (WordPress l'appelle explicitement)
window.initPDFBuilderReact = initPDFBuilderReact;

// Exporter l'API complète pour WordPress
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

