// Import des composants React
import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';
import { debugLog, debugError } from './utils/debug';

// Composant ErrorBoundary pour capturer les erreurs de rendu
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null, errorInfo: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    debugError('❌ React Error Boundary caught an error:', error);
    debugError('❌ Error Info:', errorInfo);
    this.setState({
      error: error,
      errorInfo: errorInfo
    });
  }

  render() {
    if (this.state.hasError) {
      return React.createElement('div', {
        style: {
          padding: '20px',
          border: '1px solid #ff6b6b',
          borderRadius: '5px',
          backgroundColor: '#ffe6e6',
          color: '#d63031',
          fontFamily: 'Arial, sans-serif'
        }
      }, 
        React.createElement('h2', null, 'Erreur dans l\'éditeur PDF'),
        React.createElement('p', null, 'Une erreur s\'est produite lors du rendu de l\'éditeur. Veuillez rafraîchir la page.'),
        React.createElement('details', { style: { whiteSpace: 'pre-wrap' } },
          React.createElement('summary', null, 'Détails de l\'erreur'),
          this.state.error && this.state.error.toString(),
          React.createElement('br'),
          this.state.errorInfo && this.state.errorInfo.componentStack
        )
      );
    }

    return this.props.children;
  }
}

// État de l'application
let currentTemplate = null;
let isModified = false;

debugLog('🚀 PDF Builder React bundle starting execution...');

function initPDFBuilderReact() {
  debugLog('✅ initPDFBuilderReact function called');

  try {
    // Vérifier si le container existe
    const container = document.getElementById('pdf-builder-react-root');
    debugLog('🔍 Container element:', container);
    if (!container) {
      debugError('❌ Container #pdf-builder-react-root not found');
      return false;
    }

    debugLog('✅ Container found, checking dependencies...');

    // Vérifier les dépendances
    if (typeof React === 'undefined') {
      debugError('❌ React is not available');
      return false;
    }
    if (typeof ReactDOM === 'undefined') {
      debugError('❌ ReactDOM is not available');
      return false;
    }
    debugLog('✅ React dependencies available');

    debugLog('🎯 All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    const loadingEl = document.getElementById('pdf-builder-react-loading');
    const editorEl = document.getElementById('pdf-builder-react-editor');

    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';

    debugLog('🎨 Creating React root...');

    // Créer et rendre l'application React
    const root = ReactDOM.createRoot(container);
    debugLog('🎨 React root created, rendering component...');

    root.render(React.createElement(ErrorBoundary, null, 
      React.createElement(PDFBuilder, { width: DEFAULT_CANVAS_WIDTH, height: DEFAULT_CANVAS_HEIGHT })
    ));
    debugLog('✅ React component rendered successfully');

    return true;

  } catch (error) {
    debugError('❌ Error in initPDFBuilderReact:', error);
    debugError('❌ Error stack:', error.stack);
    const container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + error.message + '</p><pre>' + error.stack + '</pre>';
    }
    return false;
  }
}

debugLog('📦 Creating exports object...');

// Export default pour webpack
const exports = {
  initPDFBuilderReact
};

debugLog('🌐 Assigning to window...');

// Wrapper IIFE for immediate execution
(function() {
  if (typeof window === 'undefined') {
    debugError('❌ window is not available');
    return;
  }

  debugLog('🔍 Before assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);

  // CRITICAL: Assign the exports object directly and immediately
  window.pdfBuilderReact = exports;
  debugLog('✅ Direct assignment successful');

  // Verify immediately
  debugLog('🔍 After assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  debugLog('🔍 window.pdfBuilderReact object keys:', Object.keys(window.pdfBuilderReact || {}));
  debugLog('🔍 window.pdfBuilderReact.initPDFBuilderReact:', typeof (window.pdfBuilderReact && window.pdfBuilderReact.initPDFBuilderReact));

  // Force verify with timing
  if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
    debugLog('✅✅ SUCCESS: initPDFBuilderReact is callable!');
  } else {
    debugError('❌❌ CRITICAL: initPDFBuilderReact is NOT available!');
    debugError('window.pdfBuilderReact:', window.pdfBuilderReact);
    debugError('exports object:', exports);
  }
}).call(window);

debugLog('🎉 PDF Builder React bundle execution completed');

// Module export for webpack/commonjs
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
  module.exports = exports;
}

export default exports;