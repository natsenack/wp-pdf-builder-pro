// Import global fallbacks first
import './globalFallback.js';

// Main application entry point that actually uses all components
import React from 'react';
import ReactDOM from 'react-dom/client';
import { PDFCanvasEditor } from './components/PDFCanvasEditor.jsx';

// Initialize the application
const init = (containerId, options = {}) => {
  console.log('🚀 PDF Builder Pro: init() appelée avec', { containerId, options });
  console.log('🚀 React disponible:', typeof React);
  console.log('🚀 ReactDOM disponible:', typeof ReactDOM);
  console.log('🚀 ReactDOM.createRoot disponible:', typeof ReactDOM?.createRoot);

  const container = document.getElementById(containerId);
  console.log('🚀 Container recherché:', containerId, 'trouvé:', !!container);
  
  if (!container) {
    console.error('❌ PDF Builder Pro: Container non trouvé', containerId);
    return;
  }

  console.log('✅ PDF Builder Pro: Container trouvé', container);

  // Clear any existing content
  container.innerHTML = '';

  console.log('🚀 Tentative de création du root React...');
  // Create React 18 root and render
  const root = ReactDOM.createRoot(container);
  console.log('✅ Root React créé:', !!root);
  root.render(
    React.createElement(PDFCanvasEditor, {
      options: options
    })
  );

  console.log('✅ PDF Builder Pro: Éditeur initialisé avec succès');
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
