/**
 * React Shim Wrapper
 * This module is aliased as 'react' in webpack externals
 * It ensures all React exports (including hooks) are available to bundled code
 */

const React = window.React;

if (!React) {
  console.error('❌ [react-shim-wrapper] window.React is not available!');
  throw new Error('React not available on window.React');
}

console.log('✅ [react-shim-wrapper] React shim loaded, making hooks available');

// Make React the default export
module.exports = React;

// Export ALL React properties explicitly so destructuring works
Object.keys(React).forEach(key => {
  module.exports[key] = React[key];
});

// Ensure hooks are definitely exported
module.exports.useState = React.useState;
module.exports.useEffect = React.useEffect;
module.exports.useRef = React.useRef;
module.exports.useCallback = React.useCallback;
module.exports.useMemo = React.useMemo;
module.exports.useContext = React.useContext;
module.exports.useReducer = React.useReducer;
module.exports.useLayoutEffect = React.useLayoutEffect;
module.exports.useId = React.useId;
module.exports.useTransition = React.useTransition;
module.exports.useDeferredValue = React.useDeferredValue;
module.exports.useImperativeHandle = React.useImperativeHandle;
module.exports.useDebugValue = React.useDebugValue;
module.exports.useSyncExternalStore = React.useSyncExternalStore;

module.exports.createElement = React.createElement;
module.exports.Fragment = React.Fragment;
