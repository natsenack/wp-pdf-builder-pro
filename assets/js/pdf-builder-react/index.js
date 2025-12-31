// ============================================================================
// PDF Builder React Bundle - STANDALONE IIFE APPROACH (NO WEBPACK UMD WRAPPING)
// ============================================================================

// Immediately invoke function to escape webpack UMD wrapping
(function() {
  'use strict';
  
  if (typeof window === 'undefined') return;
  
  console.log('🔥 [PDF BUNDLE] IIFE STARTED - window context available');
  
  // Define the initialization function IMMEDIATELY (not in module scope)
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
      
      // Import PDFBuilder dynamically or inline
      // For now, we'll check if it's available in the module cache
      var PDFBuilder = null;
      
      // Try to get PDFBuilder from webpack modules if available
      if (typeof __webpack_modules__ !== 'undefined') {
        for (var key in __webpack_modules__) {
          var mod = __webpack_modules__[key];
          if (mod && mod.exports && mod.exports.default) {
            var exp = mod.exports.default;
            // Check if this looks like PDFBuilder (has render method or is a React component)
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
  
  // Assign to window IMMEDIATELY within IIFE scope
  window.pdfBuilderReact = { 
    initPDFBuilderReact: initPDFBuilderReact 
  };
  
  console.log('🔥 [PDF BUNDLE] IIFE: Assigned to window.pdfBuilderReact');
  console.log('🔥 [PDF BUNDLE] IIFE: window.pdfBuilderReact type:', typeof window.pdfBuilderReact);
  console.log('🔥 [PDF BUNDLE] IIFE: initPDFBuilderReact type:', typeof window.pdfBuilderReact.initPDFBuilderReact);
  
})();

// For webpack: this is needed but will be ignored in favor of the IIFE
export default { initPDFBuilderReact: function() { return window.pdfBuilderReact ? window.pdfBuilderReact.initPDFBuilderReact() : false; } };
