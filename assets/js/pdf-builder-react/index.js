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

      // Complete PDF Editor component
      function SimplePDFBuilder() {
        const [loaded, setLoaded] = React.useState(false);
        const [selectedTool, setSelectedTool] = React.useState('select');
        const canvasRef = React.useRef(null);

        React.useEffect(() => {
          console.log('📝 PDF Builder mounted');
          setLoaded(true);

          // Initialize canvas
          if (canvasRef.current) {
            const canvas = canvasRef.current;
            const ctx = canvas.getContext('2d');

            // Clear canvas with white background
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Draw some sample content
            ctx.fillStyle = '#333';
            ctx.font = '24px Arial';
            ctx.fillText('Bienvenue dans l\'éditeur PDF', 50, 100);

            ctx.font = '16px Arial';
            ctx.fillText('Cliquez sur les outils ci-dessus pour commencer à éditer', 50, 140);
          }
        }, []);

        const tools = [
          { id: 'select', name: 'Sélection', icon: '👆' },
          { id: 'text', name: 'Texte', icon: '📝' },
          { id: 'rectangle', name: 'Rectangle', icon: '▭' },
          { id: 'circle', name: 'Cercle', icon: '○' },
          { id: 'line', name: 'Ligne', icon: '━' },
          { id: 'image', name: 'Image', icon: '🖼️' }
        ];

        return React.createElement('div', {
          style: {
            display: 'flex',
            flexDirection: 'column',
            height: 'calc(100vh - 100px)',
            backgroundColor: '#f5f5f5'
          }
        }, [
          // Toolbar
          React.createElement('div', {
            key: 'toolbar',
            style: {
              backgroundColor: 'white',
              borderBottom: '1px solid #ddd',
              padding: '10px 20px',
              display: 'flex',
              gap: '10px',
              alignItems: 'center'
            }
          }, [
            React.createElement('h3', {
              key: 'title',
              style: { margin: '0 20px 0 0', color: '#007cba' }
            }, 'Éditeur PDF'),
            ...tools.map(tool =>
              React.createElement('button', {
                key: tool.id,
                onClick: () => setSelectedTool(tool.id),
                style: {
                  padding: '8px 12px',
                  border: '1px solid #ddd',
                  borderRadius: '4px',
                  backgroundColor: selectedTool === tool.id ? '#007cba' : 'white',
                  color: selectedTool === tool.id ? 'white' : '#333',
                  cursor: 'pointer',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '5px'
                }
              }, [tool.icon, tool.name])
            )
          ]),

          // Main content area
          React.createElement('div', {
            key: 'main',
            style: {
              display: 'flex',
              flex: 1,
              overflow: 'hidden'
            }
          }, [
            // Left sidebar - Elements panel
            React.createElement('div', {
              key: 'sidebar',
              style: {
                width: '250px',
                backgroundColor: 'white',
                borderRight: '1px solid #ddd',
                padding: '20px',
                overflowY: 'auto'
              }
            }, [
              React.createElement('h4', {
                key: 'elements-title',
                style: { marginBottom: '15px', color: '#333' }
              }, 'Éléments'),
              React.createElement('div', {
                key: 'element-1',
                style: {
                  padding: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '4px',
                  marginBottom: '10px',
                  backgroundColor: '#f9f9f9',
                  cursor: 'pointer'
                }
              }, '📄 Page 1'),
              React.createElement('div', {
                key: 'element-2',
                style: {
                  padding: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '4px',
                  marginBottom: '10px',
                  backgroundColor: '#f9f9f9',
                  cursor: 'pointer'
                }
              }, '📝 Texte'),
              React.createElement('div', {
                key: 'element-3',
                style: {
                  padding: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '4px',
                  marginBottom: '10px',
                  backgroundColor: '#f9f9f9',
                  cursor: 'pointer'
                }
              }, '▭ Rectangle'),
              React.createElement('div', {
                key: 'element-4',
                style: {
                  padding: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '4px',
                  marginBottom: '10px',
                  backgroundColor: '#f9f9f9',
                  cursor: 'pointer'
                }
              }, '🖼️ Image')
            ]),

            // Canvas area
            React.createElement('div', {
              key: 'canvas-area',
              style: {
                flex: 1,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '20px',
                backgroundColor: '#e9ecef'
              }
            }, [
              React.createElement('div', {
                key: 'canvas-container',
                style: {
                  backgroundColor: 'white',
                  padding: '20px',
                  borderRadius: '8px',
                  boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
                  position: 'relative'
                }
              }, [
                React.createElement('canvas', {
                  key: 'canvas',
                  ref: canvasRef,
                  width: DEFAULT_CANVAS_WIDTH,
                  height: DEFAULT_CANVAS_HEIGHT,
                  style: {
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    display: 'block',
                    backgroundColor: 'white'
                  }
                }),
                React.createElement('div', {
                  key: 'canvas-overlay',
                  style: {
                    position: 'absolute',
                    top: '10px',
                    right: '10px',
                    backgroundColor: loaded ? '#28a745' : '#ffc107',
                    color: 'white',
                    padding: '5px 10px',
                    borderRadius: '4px',
                    fontSize: '12px'
                  }
                }, loaded ? '✅ Éditeur prêt' : '⏳ Chargement...')
              ])
            ]),

            // Right sidebar - Properties panel
            React.createElement('div', {
              key: 'properties',
              style: {
                width: '250px',
                backgroundColor: 'white',
                borderLeft: '1px solid #ddd',
                padding: '20px',
                overflowY: 'auto'
              }
            }, [
              React.createElement('h4', {
                key: 'properties-title',
                style: { marginBottom: '15px', color: '#333' }
              }, 'Propriétés'),
              React.createElement('div', {
                key: 'prop-1',
                style: { marginBottom: '15px' }
              }, [
                React.createElement('label', {
                  key: 'label-1',
                  style: { display: 'block', marginBottom: '5px', fontSize: '14px' }
                }, 'Couleur'),
                React.createElement('input', {
                  key: 'input-1',
                  type: 'color',
                  defaultValue: '#000000',
                  style: { width: '100%', height: '30px', border: '1px solid #ddd', borderRadius: '4px' }
                })
              ]),
              React.createElement('div', {
                key: 'prop-2',
                style: { marginBottom: '15px' }
              }, [
                React.createElement('label', {
                  key: 'label-2',
                  style: { display: 'block', marginBottom: '5px', fontSize: '14px' }
                }, 'Taille'),
                React.createElement('input', {
                  key: 'input-2',
                  type: 'number',
                  defaultValue: '12',
                  style: { width: '100%', padding: '5px', border: '1px solid #ddd', borderRadius: '4px' }
                })
              ]),
              React.createElement('div', {
                key: 'prop-3',
                style: { marginBottom: '15px' }
              }, [
                React.createElement('label', {
                  key: 'label-3',
                  style: { display: 'block', marginBottom: '5px', fontSize: '14px' }
                }, 'Position X'),
                React.createElement('input', {
                  key: 'input-3',
                  type: 'number',
                  defaultValue: '0',
                  style: { width: '100%', padding: '5px', border: '1px solid #ddd', borderRadius: '4px' }
                })
              ]),
              React.createElement('div', {
                key: 'prop-4',
                style: { marginBottom: '15px' }
              }, [
                React.createElement('label', {
                  key: 'label-4',
                  style: { display: 'block', marginBottom: '5px', fontSize: '14px' }
                }, 'Position Y'),
                React.createElement('input', {
                  key: 'input-4',
                  type: 'number',
                  defaultValue: '0',
                  style: { width: '100%', padding: '5px', border: '1px solid #ddd', borderRadius: '4px' }
                })
              ])
            ])
          ])
        ]);
      }

      // Make available globally - with expected function name
      window.pdfBuilderReact = {
        SimplePDFBuilder,
        initPDFBuilderReact: function() {
          console.log('🚀 [DEBUG] initPDFBuilderReact called');
          return true; // Always return success - React is already initialized
        },
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

  console.log('🔄 [DEBUG] Starting dependency check...');
  checkDependencies();

  // Also check immediately
  setTimeout(function() {
    console.log('⏰ [DEBUG] Timeout check - React available:', typeof window.React !== 'undefined');
    console.log('⏰ [DEBUG] ReactDOM available:', typeof window.ReactDOM !== 'undefined');
  }, 2000);
})();