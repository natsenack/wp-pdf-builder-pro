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



  // Try to execute the module immediately
  try {
    // Import the module dynamically
    const module = require('./pdf-builder-react.min.js');

    if (module && module.default) {

    } else {

    }
  } catch (error) {


    // Fallback: try to access the webpack bundle directly
    if (window.pdfBuilderReact) {

    } else {

      window.pdfBuilderReact = {
        initPDFBuilderReact: function() {

          return false;
        },
        _isFallback: true,
        _error: 'Module execution failed'
      };
    }
  }
})();