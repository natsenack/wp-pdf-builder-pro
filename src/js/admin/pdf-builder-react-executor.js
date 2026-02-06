/**
 * PDF Builder React Module Executor
 * Forces execution of the React bundle and exposes the API
 */

(function() {
  'use strict';

  // Ensure window is available
  if (typeof window === 'undefined') {
    return;
  }

  console.log('[MODULE EXECUTOR] Starting module execution...');

  // Try to execute the module immediately
  try {
    // Import the module dynamically
    const module = require('./pdf-builder-react.min.js');

    if (module && module.default) {
      console.log('[MODULE EXECUTOR] Module loaded successfully:', module.default);
      window.pdfBuilderReact = module.default;
      window.initPDFBuilderReact = module.default.initPDFBuilderReact;
      console.log('[MODULE EXECUTOR] ✅ API exposed to window');
    } else {
      console.error('[MODULE EXECUTOR] Module loaded but no default export:', module);
    }
  } catch (error) {
    console.error('[MODULE EXECUTOR] Failed to load module:', error);

    // Fallback: try to access the webpack bundle directly
    if (window.pdfBuilderReact) {
      console.log('[MODULE EXECUTOR] Found existing pdfBuilderReact');
    } else {
      console.log('[MODULE EXECUTOR] Creating fallback API');
      window.pdfBuilderReact = {
        initPDFBuilderReact: function() {
          console.error('[MODULE EXECUTOR] Fallback: React bundle failed to load');
          return false;
        },
        _isFallback: true,
        _error: 'Module execution failed'
      };
    }
  }
})();