/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 571:
/***/ (function(module, exports) {

// Simple PDF Builder - No webpack, no modules
(function() {
  'use strict';

  console.log('🚀 Simple PDF Builder initialization');

  function checkDependencies() {
    if (typeof window.React !== 'undefined' && typeof window.ReactDOM !== 'undefined') {
      console.log('✅ React found, initializing...');
      initSimplePDFBuilder();
    } else {
      console.log('⏳ Waiting for React...');
      setTimeout(checkDependencies, 100);
    }
  }

  function initSimplePDFBuilder() {
    try {
      const React = window.React;
      const ReactDOM = window.ReactDOM;

      // Simple constants
      const DEFAULT_CANVAS_WIDTH = 595;
      const DEFAULT_CANVAS_HEIGHT = 842;

      // Simple component
      function SimplePDFBuilder() {
        const [loaded, setLoaded] = React.useState(false);

        React.useEffect(() => {
          console.log('📝 PDF Builder mounted');
          setLoaded(true);
        }, []);

        return React.createElement('div', {
          style: {
            padding: '20px',
            border: '2px solid #007cba',
            borderRadius: '8px',
            margin: '20px',
            backgroundColor: '#f8f9fa'
          }
        }, [
          React.createElement('h3', {
            key: 'title',
            style: { color: '#007cba', marginBottom: '10px' }
          }, 'Éditeur PDF Simple'),
          React.createElement('p', {
            key: 'status',
            style: { color: loaded ? '#28a745' : '#6c757d' }
          }, loaded ? '✅ Éditeur prêt' : '⏳ Chargement...'),
          React.createElement('div', {
            key: 'canvas-container',
            style: {
              marginTop: '20px',
              border: '1px solid #dee2e6',
              borderRadius: '4px',
              overflow: 'auto'
            }
          }, React.createElement('canvas', {
            width: DEFAULT_CANVAS_WIDTH,
            height: DEFAULT_CANVAS_HEIGHT,
            style: {
              maxWidth: '100%',
              height: 'auto',
              display: 'block',
              backgroundColor: 'white'
            }
          }))
        ]);
      }

      // Make available globally
      window.pdfBuilderReact = {
        SimplePDFBuilder,
        initSimplePDFBuilder,
        DEFAULT_CANVAS_WIDTH,
        DEFAULT_CANVAS_HEIGHT
      };

      console.log('✅ Simple PDF Builder ready');

      // Signal ready
      window.dispatchEvent(new CustomEvent('pdfBuilderReactReady'));

    } catch (error) {
      console.error('❌ Simple PDF Builder failed:', error);
    }
  }

  checkDependencies();
})();

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module doesn't tell about it's top-level declarations so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__(571);
/******/ 	
/******/ })()
;
//# sourceMappingURL=pdf-builder-react.bundle.js.map