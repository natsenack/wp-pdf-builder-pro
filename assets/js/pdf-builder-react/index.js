// ============================================================================
// PDF Builder React Bundle - Entry Point
// ============================================================================

console.log('🚀 [PDF Builder] React bundle loading...');

// Import des composants React
import React from 'react';
import ReactDOM from 'react-dom/client';

// Flag pour afficher les logs d'initialisation détaillés
const DEBUG_VERBOSE = true;

if (DEBUG_VERBOSE) console.log('🚀 PDF Builder React bundle starting execution...');

function initPDFBuilderReact() {
  console.log('🔧 [PDF Builder] initPDFBuilderReact function called');
  if (DEBUG_VERBOSE) console.log('✅ initPDFBuilderReact function called');

  try {
    // Vérifier si le container existe
    const container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 [PDF Builder] Container element:', container);
    if (DEBUG_VERBOSE) console.log('🔍 Container element:', container);
    if (!container) {
      console.error('❌ [PDF Builder] Container #pdf-builder-react-root not found');
      return false;
    }

    console.log('✅ [PDF Builder] Container found, checking dependencies...');
    if (DEBUG_VERBOSE) console.log('✅ Container found, checking dependencies...');

    // Vérifier les dépendances
    console.log('🔧 [PDF Builder] Checking React availability:', typeof React);
    if (typeof React === 'undefined') {
      console.error('❌ [PDF Builder] React is not available');
      return false;
    }
    console.log('🔧 [PDF Builder] Checking ReactDOM availability:', typeof ReactDOM);
    if (typeof ReactDOM === 'undefined') {
      console.error('❌ [PDF Builder] ReactDOM is not available');
      return false;
    }
    console.log('✅ [PDF Builder] React dependencies available');
    if (DEBUG_VERBOSE) console.log('✅ React dependencies available');

    console.log('🎯 [PDF Builder] All dependencies loaded, initializing React...');
    if (DEBUG_VERBOSE) console.log('🎯 All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    const loadingEl = document.getElementById('pdf-builder-react-loading');
    const editorEl = document.getElementById('pdf-builder-react-editor');

    console.log('🎨 [PDF Builder] Hiding loading, showing editor:', { loadingEl, editorEl });
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';

    console.log('🎨 [PDF Builder] Creating React root...');
    if (DEBUG_VERBOSE) console.log('🎨 Creating React root...');

    // Créer et rendre l'application React
    const root = ReactDOM.createRoot(container);
    console.log('🎨 [PDF Builder] React root created, rendering component...');
    if (DEBUG_VERBOSE) console.log('🎨 React root created, rendering component...');

    root.render(React.createElement('div', { style: { padding: '20px', border: '1px solid green', backgroundColor: 'lightgreen' } }, '✅ React is working! PDF Builder will load here.'));
    console.log('✅ [PDF Builder] React component rendered successfully');
    if (DEBUG_VERBOSE) console.log('✅ React component rendered successfully');

    return true;

  } catch (error) {
    console.error('❌ [PDF Builder] Error in initPDFBuilderReact:', error);
    console.error('❌ [PDF Builder] Error stack:', error.stack);
    const container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + error.message + '</p><pre>' + error.stack + '</pre>';
    }
    return false;
  }
}

if (DEBUG_VERBOSE) console.log('📦 Creating exports object...');

// Export default pour webpack
const exports = {
  initPDFBuilderReact
};

if (DEBUG_VERBOSE) console.log('🌐 Assigning to window...');

// Wrapper IIFE for immediate execution
(function() {
  console.log('🔄 [PDF Builder] IIFE starting...');
  if (typeof window === 'undefined') {
    console.warn('⚠️ [PDF Builder] Window not available, skipping global assignment');
    return;
  }

  // CRITICAL: Assign the exports object directly and immediately
  window.pdfBuilderReact = exports;
  console.log('🌐 [PDF Builder] Assigned to window.pdfBuilderReact:', window.pdfBuilderReact);
  
  // Verify immediately
  if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
    console.log('✅ [PDF Builder] initPDFBuilderReact function is available globally');
  } else {
    console.error('❌ [PDF Builder] initPDFBuilderReact function NOT available globally');
  }
}).call(window);

if (DEBUG_VERBOSE) console.log('🎉 PDF Builder React bundle execution completed');
