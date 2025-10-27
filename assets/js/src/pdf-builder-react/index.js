// Import des composants React
import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder.tsx';

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
    root.render(React.createElement(PDFBuilder, { width: 1200, height: 800 }));
    console.log('React component rendered successfully');

  } catch (error) {
    console.error('Error rendering React component:', error);
    container.innerHTML = '<p>Erreur lors du rendu React: ' + error.message + '</p>';
    return false;
  }

  return true;
}

// Export temporaire pour test
const testExport = 'Hello from React Builder';

// Export default pour webpack
export default {
  initPDFBuilderReact,
  testExport
};