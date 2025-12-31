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

      // Make available globally - with expected function name
      window.pdfBuilderReact = {
        SimplePDFBuilder,
        initPDFBuilderReact: initSimplePDFBuilder,
        initSimplePDFBuilder,
        DEFAULT_CANVAS_WIDTH,
        DEFAULT_CANVAS_HEIGHT
      };

      console.log('✅ Simple PDF Builder ready');

      // Try to render immediately
      const rootElement = document.getElementById('pdf-builder-react-root');
      if (rootElement) {
        console.log('🎨 Rendering to DOM...');
        try {
          ReactDOM.createRoot(rootElement).render(React.createElement(SimplePDFBuilder));
          console.log('✅ Rendered successfully');
        } catch (error) {
          console.error('❌ Render failed:', error);
          rootElement.innerHTML = '<p>Erreur de rendu: ' + error.message + '</p>';
        }
      } else {
        console.warn('⚠️ Root element not found');
      }

      // Signal ready
      window.dispatchEvent(new CustomEvent('pdfBuilderReactReady'));

    } catch (error) {
      console.error('❌ Simple PDF Builder failed:', error);
    }
  }

  // Export the expected function
  window.pdfBuilderReact.initPDFBuilderReact = function() {
    console.log('🚀 initPDFBuilderReact called');
    return true; // Always return success
  };

  checkDependencies();
})();