/**
 * PDF Builder React - Point d'entrée WordPress
 * Ce fichier est chargé par WordPress pour initialiser l'éditeur React
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder';

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
    console.error('Container #pdf-builder-react-root not found');
    return;
  }

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

    console.log('PDF Builder React initialized successfully');
  } catch (error) {
    console.error('Failed to initialize PDF Builder React:', error);
    container.innerHTML = '<p>Erreur lors de l\'initialisation de l\'éditeur React.</p>';
  }
}

// Auto-initialisation si le DOM est déjà prêt
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPDFBuilderReact);
} else {
  initPDFBuilderReact();
}

// Export pour utilisation manuelle
(window as any).initPDFBuilderReact = initPDFBuilderReact;