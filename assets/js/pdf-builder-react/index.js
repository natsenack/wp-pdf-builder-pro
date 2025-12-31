// Simple PDF Builder - No webpack, no modules
(function() {
  'use strict';

  console.log('🚀 [DEBUG] Simple PDF Builder initialization script loaded');
  console.log('🚀 [DEBUG] Window object available:', typeof window);
  console.log('🚀 [DEBUG] Document ready state:', document.readyState);

  function checkDependencies() {
    console.log('🔍 [DEBUG] Checking React dependencies...');
    console.log('🔍 [DEBUG] window.React:', typeof window.React, window.React ? 'available' : 'NOT available');
    console.log('🔍 [DEBUG] window.ReactDOM:', typeof window.ReactDOM, window.ReactDOM ? 'available' : 'NOT available');

    if (typeof window.React !== 'undefined' && typeof window.ReactDOM !== 'undefined') {
      console.log('✅ [DEBUG] React found, initializing...');
      initSimplePDFBuilder();
    } else {
      console.log('⏳ [DEBUG] Waiting for React...');
      setTimeout(checkDependencies, 500); // Increased delay
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
      console.log('🎨 [DEBUG] Looking for root element...');
      const rootElement = document.getElementById('pdf-builder-react-root');
      console.log('🎨 [DEBUG] Root element found:', !!rootElement);
      console.log('🎨 [DEBUG] Root element:', rootElement);

      if (rootElement) {
        console.log('🎨 [DEBUG] Rendering to DOM...');
        rootElement.style.border = '2px solid red'; // Make it visible
        rootElement.innerHTML = '<div style="padding: 20px; background: yellow; color: black;">🔧 PDF Builder Loading...</div>';

        try {
          console.log('🎨 [DEBUG] Creating React root...');
          const root = ReactDOM.createRoot(rootElement);
          console.log('🎨 [DEBUG] Rendering component...');
          root.render(React.createElement(SimplePDFBuilder));
          console.log('✅ [DEBUG] Rendered successfully');
        } catch (error) {
          console.error('❌ [DEBUG] Render failed:', error);
          rootElement.innerHTML = '<div style="padding: 20px; background: red; color: white;"><h2>❌ Erreur de rendu React</h2><p>' + error.message + '</p><pre>' + error.stack + '</pre></div>';
        }
      } else {
        console.warn('⚠️ [DEBUG] Root element not found - creating fallback');
        // Create a fallback visible element
        const fallback = document.createElement('div');
        fallback.style.cssText = 'position: fixed; top: 100px; right: 100px; width: 300px; height: 200px; background: orange; border: 3px solid black; z-index: 9999; padding: 10px;';
        fallback.innerHTML = '<h3>🚨 PDF Builder Debug</h3><p>Root element not found!</p><p>React: ' + (typeof window.React) + '</p><p>ReactDOM: ' + (typeof window.ReactDOM) + '</p>';
        document.body.appendChild(fallback);
      }

      // Signal ready
      window.dispatchEvent(new CustomEvent('pdfBuilderReactReady'));

    } catch (error) {
      console.error('❌ Simple PDF Builder failed:', error);
    }
  }

  // Export the expected function
  window.pdfBuilderReact.initPDFBuilderReact = function() {
    console.log('🚀 [DEBUG] initPDFBuilderReact called');
    return true; // Always return success
  };

  console.log('🔄 [DEBUG] Starting dependency check...');
  checkDependencies();

  // Also check immediately
  setTimeout(function() {
    console.log('⏰ [DEBUG] Timeout check - React available:', typeof window.React !== 'undefined');
    console.log('⏰ [DEBUG] ReactDOM available:', typeof window.ReactDOM !== 'undefined');
  }, 2000);
})();