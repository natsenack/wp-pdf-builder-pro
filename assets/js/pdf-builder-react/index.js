// ============================================================================
// PDF Builder React Bundle - Entry Point OPTIMISÉ avec Code Splitting
// ============================================================================

console.log('🎯 [BUNDLE START] pdf-builder-react/index.js file loaded and executing');

// Import du diagnostic de compatibilité
import '../fallbacks/browser-compatibility.js';

// Imports synchrones légers
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT, getCanvasDimensions } from './constants/canvas.ts';
import { debugLog, debugError } from './utils/debug';

// Import React pour les composants
import React from 'react';
import ReactDOM from 'react-dom';

console.log('🔧 [WEBPACK BUNDLE] pdf-builder-react/index.js starting execution...');
console.log('🔧 [WEBPACK BUNDLE] React available:', typeof React);
console.log('🔧 [WEBPACK BUNDLE] React.useRef available:', typeof React?.useRef);
console.log('🔧 [WEBPACK BUNDLE] React.useState available:', typeof React?.useState);
console.log('🔧 [WEBPACK BUNDLE] React keys:', Object.keys(React || {}).slice(0, 10));
console.log('🔧 [WEBPACK BUNDLE] createRoot available:', typeof ReactDOM?.createRoot);

// ✅ Exports React from window for fallback access
if (typeof window !== 'undefined' && !window.React) {
  window.React = React;
}
if (typeof window !== 'undefined' && !window.ReactDOM) {
  window.ReactDOM = ReactDOM;
}

// Imports synchrones des composants lourds (plus de lazy loading pour éviter les chunks webpack)
import { PDFBuilder } from './PDFBuilder.tsx';
import {
  registerEditorInstance,
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  resetAPI,
  updateCanvasDimensions
} from './api/global-api';

// Composant ErrorBoundary pour capturer les erreurs de rendu
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null, errorInfo: null };
  }

  static getDerivedStateFromError(_error) {
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
// let currentTemplate = null;
// let isModified = false;

// Flag pour afficher les logs d'initialisation détaillés
const DEBUG_VERBOSE = false;

console.log('🎯 [BUNDLE INIT] About to define initPDFBuilderReact function');

if (DEBUG_VERBOSE) debugLog('🚀 PDF Builder React bundle starting execution...');

async function initPDFBuilderReact() {
  console.log('🚀 [initPDFBuilderReact] Function called');
  if (DEBUG_VERBOSE) debugLog('✅ initPDFBuilderReact function called');

  try {
    // Vérifier si le container existe
    const container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 [initPDFBuilderReact] Container found:', !!container);
    if (DEBUG_VERBOSE) debugLog('🔍 Container element:', container);
    if (!container) {
      console.error('❌ [initPDFBuilderReact] Container #pdf-builder-react-root not found');
      debugError('❌ Container #pdf-builder-react-root not found');
      return false;
    }

    if (DEBUG_VERBOSE) debugLog('✅ Container found, checking dependencies...');

    // Vérifier les dépendances
    if (typeof React === 'undefined') {
      debugError('❌ React is not available');
      return false;
    }
    if (DEBUG_VERBOSE) debugLog('✅ React dependencies available');

    // Composants déjà chargés de manière synchrone
    if (DEBUG_VERBOSE) debugLog('✅ Components loaded synchronously, initializing React...');

    // Masquer le loading et afficher l'éditeur
    const loadingEl = document.getElementById('pdf-builder-loader');
    const editorEl = document.getElementById('pdf-builder-editor-container');

    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';

    if (DEBUG_VERBOSE) debugLog('🎨 Creating React root...');

    // Créer et rendre l'application React
    // Essayer createRoot d'abord (React 18), sinon utiliser render (compatibilité)
    let root;
    console.log('🔧 [initPDFBuilderReact] Checking ReactDOM.createRoot:', typeof ReactDOM.createRoot);
    if (ReactDOM.createRoot) {
      root = ReactDOM.createRoot(container);
      console.log('✅ [initPDFBuilderReact] Using React 18 createRoot API');
      if (DEBUG_VERBOSE) debugLog('🎨 Using React 18 createRoot API');
    } else {
      console.log('⚠️ [initPDFBuilderReact] createRoot not available, using render fallback');
      // Fallback pour anciennes versions
      if (DEBUG_VERBOSE) debugLog('🎨 Using React render API (fallback)');
    }

    console.log('🎨 [initPDFBuilderReact] About to render React component...');

    // Récupérer les dimensions dynamiques depuis les paramètres
    const canvasDimensions = getCanvasDimensions();
    const canvasWidth = canvasDimensions.width;
    const canvasHeight = canvasDimensions.height;

    console.log('📐 [initPDFBuilderReact] Canvas dimensions:', { width: canvasWidth, height: canvasHeight });

    const element = React.createElement(ErrorBoundary, null,
      React.createElement(PDFBuilder, { width: canvasWidth, height: canvasHeight })
    );

    if (root) {
      // React 18 API
      console.log('🎯 [initPDFBuilderReact] Calling root.render()...');
      root.render(element);
      console.log('✅ [initPDFBuilderReact] root.render() completed');
    } else {
      // Fallback API
      console.log('🎯 [initPDFBuilderReact] Calling ReactDOM.render()...');
      ReactDOM.render(element, container);
      console.log('✅ [initPDFBuilderReact] ReactDOM.render() completed');
    }
    console.log('✅ [initPDFBuilderReact] React rendering completed successfully');
    if (DEBUG_VERBOSE) debugLog('✅ React component rendered successfully');

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

if (DEBUG_VERBOSE) debugLog('📦 Creating exports object...');

// Export default pour webpack
const exports = {
  initPDFBuilderReact,
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  registerEditorInstance,
  resetAPI,
  updateCanvasDimensions,
  _isWebpackBundle: true
};

if (DEBUG_VERBOSE) debugLog('🌐 Assigning to window...');

// ✅ CRITICAL: Assign to window SYNCHRONOUSLY
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = exports;
  console.log('✅ [WEBPACK BUNDLE] window.pdfBuilderReact assigned manually in index.js');
}

// No complex exports - let webpack UMD handle it with the assignment above
export default exports;
