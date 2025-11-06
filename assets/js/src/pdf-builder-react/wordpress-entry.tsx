/**
 * PDF Builder React - Point d'entrée WordPress
 * Ce fichier est chargé par WordPress pour initialiser l'éditeur React
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder';
import { debugLog, debugError, debugWarn } from './utils/debug';

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
    return true;

  } catch (error) {
    debugError('PDF Builder React: Initialization error:', error);
    container.innerHTML = '<p>Erreur lors de l\'initialisation de l\'éditeur React.</p>';
    // Remove the initialized flag on error
    container.removeAttribute('data-react-initialized');
    return false;
  }
}

// Export pour utilisation manuelle (WordPress l'appelle explicitement)
(window as any).initPDFBuilderReact = initPDFBuilderReact;