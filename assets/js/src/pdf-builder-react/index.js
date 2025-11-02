// Import des composants React
import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';

// État de l'application
let currentTemplate = null;
let isModified = false;

console.log('🚀 PDF Builder React bundle starting execution...');

function initPDFBuilderReact() {
  console.log('✅ initPDFBuilderReact function called');

  try {
    // Vérifier si le container existe
    const container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 Container element:', container);
    if (!container) {
      console.error('❌ Container #pdf-builder-react-root not found');
      return false;
    }

    console.log('✅ Container found, checking dependencies...');

    // Vérifier les dépendances
    if (typeof React === 'undefined') {
      console.error('❌ React is not available');
      return false;
    }
    if (typeof ReactDOM === 'undefined') {
      console.error('❌ ReactDOM is not available');
      return false;
    }
    console.log('✅ React dependencies available');

    console.log('🎯 All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    const loadingEl = document.getElementById('pdf-builder-react-loading');
    const editorEl = document.getElementById('pdf-builder-react-editor');

    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';

    console.log('🎨 Creating React root...');

    // Créer et rendre l'application React
    const root = ReactDOM.createRoot(container);
    console.log('🎨 React root created, rendering component...');

    root.render(React.createElement(PDFBuilder, { width: DEFAULT_CANVAS_WIDTH, height: DEFAULT_CANVAS_HEIGHT })); // A4 portrait dimensions
    console.log('✅ React component rendered successfully');

    return true;

  } catch (error) {
    console.error('❌ Error in initPDFBuilderReact:', error);
    console.error('❌ Error stack:', error.stack);
    const container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + error.message + '</p><pre>' + error.stack + '</pre>';
    }
    return false;
  }
}

console.log('📦 Creating exports object...');

// Export default pour webpack
const exports = {
  initPDFBuilderReact
};

console.log('🌐 Assigning to window...');

// Assigner la fonction à window pour l'accès global depuis WordPress
if (typeof window !== 'undefined') {
  console.log('🔍 Before assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  window.pdfBuilderReact = exports;
  console.log('✅ window.pdfBuilderReact assigned successfully');
  console.log('🔍 After assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  console.log('🔍 window.pdfBuilderReact object:', window.pdfBuilderReact);
  console.log('🔍 window object:', window);
  console.log('🔍 window === globalThis:', window === globalThis);

  // Vérifier immédiatement si l'assignation persiste
  setTimeout(function() {
    console.log('⏰ 100ms after assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  }, 100);

  setTimeout(function() {
    console.log('⏰ 500ms after assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  }, 500);

} else {
  console.error('❌ window is not available');
}

console.log('🎉 PDF Builder React bundle execution completed');

export default exports;