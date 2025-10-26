// Test script to check if the main bundle can execute
console.log('🔍 Testing main bundle execution...');

// Simulate the global environment
global.window = {
  React: {
    createElement: () => ({}),
    useState: () => [null, () => {}],
    useEffect: () => {},
    useCallback: () => () => {},
    useRef: () => ({ current: null }),
    Component: class {},
    Fragment: () => ({})
  },
  ReactDOM: {
    createRoot: () => ({
      render: () => console.log('ReactDOM.render called')
    })
  },
  pdfBuilderInitReact: undefined
};

// In browser, self === window
global.self = global.window;

// Load and execute runtime first
try {
  const fs = require('fs');
  const runtime = fs.readFileSync('assets/js/dist/runtime.5a3a6b88d4542257f277.js', 'utf8');
  console.log('Runtime loaded, length:', runtime.length);

  eval(runtime);
  console.log('Runtime executed successfully');

  // Now load the main bundle
  const bundle = fs.readFileSync('assets/js/dist/pdf-builder-admin-debug.js', 'utf8');
  console.log('Bundle loaded, length:', bundle.length);

  // Execute the bundle
  eval(bundle);

  console.log('Bundle executed successfully');
  console.log('pdfBuilderInitReact defined:', typeof global.window.pdfBuilderInitReact);

} catch (error) {
  console.error('Execution failed:', error.message);
  console.error('Stack:', error.stack);
}