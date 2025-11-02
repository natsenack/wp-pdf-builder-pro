// Import des composants React
import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';

// État de l'application
let currentTemplate = null;
let isModified = false;

function initPDFBuilderReact() {
  console.log('PDF Builder React initialized successfully!');

  // Vérifier si React est disponible
  if (typeof window.React === 'undefined') {
    console.error('React is not loaded');
    return false;
  }

  if (typeof window.ReactDOM === 'undefined') {
    console.error('ReactDOM is not loaded');
    return false;
  }

  // Vérifier si le container existe
  const container = document.getElementById('pdf-builder-react-root');
  if (!container) {
    console.error('Container #pdf-builder-react-root not found');
    return false;
  }

  console.log('All dependencies loaded, initializing React...');

  // Masquer le loading et afficher l'éditeur
  const loadingEl = document.getElementById('pdf-builder-react-loading');
  const editorEl = document.getElementById('pdf-builder-react-editor');

  if (loadingEl) loadingEl.style.display = 'none';
  if (editorEl) editorEl.style.display = 'block';

  // Créer et rendre l'application React
  try {
    const root = ReactDOM.createRoot(container);
    root.render(React.createElement(PDFBuilder, { width: DEFAULT_CANVAS_WIDTH, height: DEFAULT_CANVAS_HEIGHT })); // A4 portrait dimensions
    console.log('React component rendered successfully');

  } catch (error) {
    console.error('Error rendering React component:', error);
    container.innerHTML = '<p>Erreur lors du rendu React: ' + error.message + '</p>';
    return false;
  }

  return true;
}

// Export default pour webpack
const exports = {
  initPDFBuilderReact
};

// Assigner la fonction à window pour l'accès global depuis WordPress
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = exports;
}

export default exports;