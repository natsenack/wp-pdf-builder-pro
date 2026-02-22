/**
 * PDF Builder React Loader
 * Ensures the React bundle executes and exports are available
 */

(function() {
  'use strict';
  
  // Ensure window is available
  if (typeof window === 'undefined') {
    return;
  }
  
  // Wait for the pdfBuilderReact API to be initialized from the bundle
  let retries = 0;
  const maxRetries = 100;
  
  function checkAndInit() {
    retries++;
    
    if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
      console.log('[PDF BUILDER LOADER] ✅ pdfBuilderReact API detected and ready');
      return;
    }
    
    if (retries >= maxRetries) {
      console.warn('[PDF BUILDER LOADER] ⚠️ pdfBuilderReact API not found after ' + maxRetries + ' retries');
      // Create a fallback stub
      if (!window.pdfBuilderReact) {
        window.pdfBuilderReact = {
          initPDFBuilderReact: function() {
            console.error('[PDF BUILDER LOADER] Error: pdfBuilderReact not initialized');
            return false;
          },
          _isFallback: true
        };
      }
      return;
    }
    
    setTimeout(checkAndInit, 50);
  }
  
  // Start checking when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkAndInit);
  } else {
    checkAndInit();
  }
})();
