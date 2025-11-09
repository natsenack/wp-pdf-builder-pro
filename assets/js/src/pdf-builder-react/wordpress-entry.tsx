/**
 * PDF Builder React - Point d'entrée WordPress
 * Ce fichier est chargé par WordPress pour initialiser l'éditeur React
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder';
import { debugLog, debugError, debugWarn } from './utils/debug';
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
    pdfBuilderReactData: {
      nonce: string;
      ajaxUrl: string;
      strings: {
        loading: string;
        error: string;
      };
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
    debugLog('PDF Builder React: Already initialized, skipping');
    return true;
  }

  // Mark as initialized
  container.setAttribute('data-react-initialized', 'true');

  // Masquer le loading et afficher l'éditeur
  const loadingEl = document.getElementById('pdf-builder-react-loading');
  const editorEl = document.getElementById('pdf-builder-react-editor');

  if (loadingEl) loadingEl.style.display = 'none';
  if (editorEl) editorEl.style.display = 'block';

  try {
    const root = createRoot(container);
    root.render(
      <React.StrictMode>
        <PDFBuilder />
      </React.StrictMode>
    );
    debugLog('PDF Builder React: Successfully initialized');

    // Charger les données initiales du template s'il y en a
    const dataWindow = window as unknown as { pdfBuilderData?: { existingTemplate?: unknown } };
    const existingTemplate = dataWindow.pdfBuilderData?.existingTemplate;
    if (existingTemplate) {
      const tpl = existingTemplate as { id?: string; elements?: unknown[] };
      debugLog('PDF Builder React: Loading existing template data', {
        id: tpl.id,
        elementsCount: Array.isArray(tpl.elements) ? tpl.elements.length : 0
      });
      
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
declare global {
  interface Window {
    initPDFBuilderReact: typeof initPDFBuilderReact;
    pdfBuilderReact: {
      loadTemplate: typeof loadTemplate;
      getEditorState: typeof getEditorState;
      setEditorState: typeof setEditorState;
      getCurrentTemplate: typeof getCurrentTemplate;
      exportTemplate: typeof exportTemplate;
      saveTemplate: typeof saveTemplate;
      registerEditorInstance: typeof registerEditorInstance;
      resetAPI: typeof resetAPI;
    };
  }
}

// Export pour utilisation manuelle (WordPress l'appelle explicitement)
window.initPDFBuilderReact = initPDFBuilderReact;

// Exporter l'API complète pour WordPress
window.pdfBuilderReact = {
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  registerEditorInstance,
  resetAPI
};