// ============================================================================
// PDF Builder React - MAIN BUNDLE
// Exports initPDFBuilderReact function to window.pdfBuilderReact
// Pre-init script ensures window.pdfBuilderReact and debug array exist first
// ============================================================================

// Define the initialization function
function initPDFBuilderReact() {
  window.pdfBuilderReactDebug.push('FUNCTION_CALLED_STARTED');
  window.pdfBuilderReactDebug.push('IMMEDIATE_RETURN_TEST');
  return false;
  
  try {
    window.pdfBuilderReactDebug.push('FUNCTION_IN_TRY_BLOCK');
    // Get globals
    var React = window.React;
    var ReactDOM = window.ReactDOM;
    window.pdfBuilderReactDebug.push('FUNCTION_AFTER_GLOBALS');
    // Just return true to test if function works at all
    window.pdfBuilderReactDebug.push('FUNCTION_RETURNING_TRUE');
    return true;
    console.log('🔧 [PDF BUNDLE] __webpack_modules__ available:', !!window.__webpack_modules__);
    console.log('🔧 [PDF BUNDLE] __webpack_require__ available:', !!window.__webpack_require__);
    
    // Check for container
    var container = document.getElementById('pdf-builder-react-root');
    console.log('🔧 [PDF BUNDLE] Container element:', container ? 'FOUND' : 'NOT FOUND');
    
    if (!container) {
      console.error('❌ [PDF BUNDLE] ERROR: Container not found');
      return false;
    }
    
    // Validate React
    if (typeof React === 'undefined' || !React) {
      console.error('❌ [PDF BUNDLE] ERROR: React undefined or null');
      return false;
    }
    
    if (typeof ReactDOM === 'undefined' || !ReactDOM) {
      console.error('❌ [PDF BUNDLE] ERROR: ReactDOM undefined or null');
      return false;
    }
    
    if (typeof ReactDOM.createRoot !== 'function') {
      console.error('❌ [PDF BUNDLE] ERROR: ReactDOM.createRoot not a function');
      return false;
    }
    
    console.log('✅ [PDF BUNDLE] React dependencies validated');
    
    // Check webpack modules count
    var moduleCount = Object.keys(window.__webpack_modules__ || {}).length;
    console.log('🔧 [PDF BUNDLE] Webpack modules count:', moduleCount);
    
    // Get UI elements safely
    var loadingEl = document.getElementById('pdf-builder-react-loading');
    var editorEl = document.getElementById('pdf-builder-react-editor');
    
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    
    console.log('🎨 [PDF BUNDLE] Creating React root...');
    var root = ReactDOM.createRoot(container);
    
    // Try to get PDFBuilder from webpack modules if available
    var PDFBuilder = null;
    
    if (typeof __webpack_modules__ !== 'undefined') {
      for (var key in __webpack_modules__) {
        var mod = __webpack_modules__[key];
        if (mod && mod.exports && mod.exports.default) {
          var exp = mod.exports.default;
          if (typeof exp === 'function' && (exp.$$typeof || exp.prototype)) {
            PDFBuilder = exp;
            console.log('🎨 [PDF BUNDLE] Found PDFBuilder in module cache');
            break;
          }
        }
      }
    }
    
    if (!PDFBuilder) {
      console.error('❌ [PDF BUNDLE] ERROR: PDFBuilder component not found');
      return false;
    }
    
    console.log('🎨 [PDF BUNDLE] Creating element from PDFBuilder component...');
    var element = React.createElement(PDFBuilder);
    
    console.log('🎨 [PDF BUNDLE] Rendering to root...');
    root.render(element);
    
    console.log('✅ [PDF BUNDLE] Rendered successfully!');
    return true;
    
  } catch (error) {
    window.pdfBuilderReactDebug.push('ERROR: ' + error.message);
    console.error('❌ [PDF BUNDLE] EXCEPTION:', error.message);
    console.error('❌ [PDF BUNDLE] Stack:', error.stack);
    return false;
  }
}

// Force immediate assignment at module level
// This runs when webpack loads the module, before anything else
if (typeof window !== 'undefined') {
  window.pdfBuilderReactDebug = window.pdfBuilderReactDebug || [];
  window.pdfBuilderReactDebug.push('MODULE_LEVEL_EXECUTION');
}

window.pdfBuilderReact = window.pdfBuilderReact || {};
window.pdfBuilderReact.initPDFBuilderReact = initPDFBuilderReact;

if (typeof window !== 'undefined') {
  window.pdfBuilderReactDebug.push('FUNCTION_ASSIGNED');
}

// Export for module systems
export default initPDFBuilderReact;
