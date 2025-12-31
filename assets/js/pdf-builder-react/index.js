// ============================================================================
// PDF Builder React - MAIN BUNDLE
// Exports initPDFBuilderReact function to window.pdfBuilderReact
// Pre-init script ensures window.pdfBuilderReact exists before this runs
// ============================================================================

// Define the initialization function
function initPDFBuilderReact() {
  console.log('🔧 [PDF BUNDLE] initPDFBuilderReact CALLED');
  
  try {
    // Get globals
    var React = window.React;
    var ReactDOM = window.ReactDOM;
    
    console.log('🔧 [PDF BUNDLE] React type:', typeof React);
    console.log('🔧 [PDF BUNDLE] ReactDOM type:', typeof ReactDOM);
    
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
    
    // Hide loading, show editor
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
    console.error('❌ [PDF BUNDLE] EXCEPTION:', error.message);
    console.error('❌ [PDF BUNDLE] Stack:', error.stack);
    return false;
  }
}

// Assign to window.pdfBuilderReact
// The pre-init script ensures this object already exists, so we just add the function
if (typeof window.pdfBuilderReact === 'object') {
  window.pdfBuilderReact.initPDFBuilderReact = initPDFBuilderReact;
  console.log('✅ [PDF BUNDLE] Successfully assigned initPDFBuilderReact to window.pdfBuilderReact');
} else {
  // Fallback if pre-init didn't run
  window.pdfBuilderReact = { initPDFBuilderReact: initPDFBuilderReact };
  console.log('⚠️ [PDF BUNDLE] FALLBACK: Created window.pdfBuilderReact (pre-init may not have run)');
}

// Export for CommonJS/module systems
export default { initPDFBuilderReact: initPDFBuilderReact };
