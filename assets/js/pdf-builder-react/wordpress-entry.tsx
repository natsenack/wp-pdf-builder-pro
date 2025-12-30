/**
 * PDF Builder React - Point d'entrée WordPress
 * Ce fichier est chargé par WordPress pour initialiser l'éditeur React
 */

// DEBUG: Log when script starts loading
debugLog('🔧 DEBUG: pdf-builder-react.js script started loading');

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
    };
    // Notification functions
    showSuccessNotification?: (message: string, duration?: number) => void;
    showErrorNotification?: (message: string, duration?: number) => void;
    showWarningNotification?: (message: string, duration?: number) => void;
    showInfoNotification?: (message: string, duration?: number) => void;
    // Canvas settings
    pdfBuilderCanvasSettings?: {
      canvas_width?: number;
      canvas_height?: number;
      debug?: boolean;
      [key: string]: any;
    };
  }
}

export function initPDFBuilderReact() {
  const container = document.getElementById('pdf-builder-react-root');

  if (!container) {
    debugWarn('PDF Builder React: Container element not found');
    return false;
  }

  // Check if React has already been initialized
  if (container.hasAttribute('data-react-initialized')) {

    return true;
  }

  // Mark as initialized
  container.setAttribute('data-react-initialized', 'true');

  // Masquer le loading et afficher l'éditeur
  const loadingEl = document.getElementById('pdf-builder-loader');
  const editorEl = document.getElementById('pdf-builder-editor-container');

  if (loadingEl) loadingEl.style.display = 'none';
  if (editorEl) editorEl.style.display = 'block';

  try {
    const root = createRoot(container);
    root.render(
      // ✅ Disabled StrictMode - it causes double rendering which messes up Canvas
      // In development, it can help catch bugs, but production needs single render
      <PDFBuilder />
    );


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
    debugError('PDF Builder React: Initialization error:', error);
    container.innerHTML = '<p>Erreur lors de l\'initialisation de l\'éditeur React.</p>';
    // Remove the initialized flag on error
    container.removeAttribute('data-react-initialized');
    return false;
  }
}

// Déclarer l'interface globale pour TypeScript
// (Déjà déclarée plus haut)

// Export pour utilisation manuelle (WordPress l'appelle explicitement)
window.initPDFBuilderReact = initPDFBuilderReact;

// Exporter l'API complète pour WordPress
debugLog('🔧 DEBUG: About to assign window.pdfBuilderReact');
window.pdfBuilderReact = {
  initPDFBuilderReact,
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  registerEditorInstance,
  resetAPI
};
debugLog('🔧 DEBUG: window.pdfBuilderReact assigned:', window.pdfBuilderReact);

