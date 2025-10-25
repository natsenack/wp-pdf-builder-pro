// Import global fallbacks first
import './globalFallback.js';

// Main application entry point that actually uses all components
import React from 'react';
import ReactDOM from 'react-dom/client';
import { PDFCanvasEditor } from './components/PDFCanvasEditor.jsx';

// Initialize the application
const init = (containerId, options = {}) => {
  console.log('PDF Builder Pro: Initialisation de l\'éditeur', { containerId, options });

  const container = document.getElementById(containerId);
  if (!container) {
    console.error('PDF Builder Pro: Container non trouvé', containerId);
    return;
  }

  // Clear any existing content
  container.innerHTML = '';

  // Create React 18 root and render
  const root = ReactDOM.createRoot(container);
  root.render(
    React.createElement(PDFCanvasEditor, {
      options: options
    })
  );

  console.log('PDF Builder Pro: Éditeur initialisé avec succès');
};

// Make it globally available
if (typeof window !== 'undefined') {
  if (!window.pdfBuilderPro) {
    window.pdfBuilderPro = {};
  }
  // Étendre l'objet existant avec la fonction init
  window.pdfBuilderPro.init = init;
}

// Export for ES6 modules
export { init };
