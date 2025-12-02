/**
 * React Shim Wrapper - Dynamic Proxy
 * Provides access to React hooks at runtime, not module load time
 * This avoids issues where React properties aren't yet available during module initialization
 */

// Get React from WordPress global
const React = window.React;

if (!React) {
  console.error('❌ [react-shim-wrapper] window.React is not available!');
  throw new Error('React not available on window.React');
}

console.log('✅ [react-shim-wrapper] React shim loaded with', Object.keys(React).length, 'properties');
console.log('✅ [react-shim-wrapper] Hooks available - useState:', typeof React.useState, ', useRef:', typeof React.useRef);

// Create a Proxy that dynamically resolves React properties at access time
const ReactProxy = new Proxy(React, {
  get: function(target, prop, receiver) {
    const value = Reflect.get(target, prop, receiver);
    if (typeof prop === 'string' && prop.startsWith('use')) {
      if (!value) {
        console.warn(`⚠️ [react-shim] Hook ${prop} not found on React, checking window.React again...`);
        return window.React[prop];
      }
    }
    return value;
  },
  has: function(target, prop) {
    return Reflect.has(target, prop) || Reflect.has(window.React, prop);
  }
});

// Export the proxy as default
module.exports = ReactProxy;
module.exports.default = ReactProxy;

// Also export hooks explicitly
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

// Core APIs
module.exports.createElement = React.createElement;
module.exports.Fragment = React.Fragment;
module.exports.createContext = React.createContext;
module.exports.memo = React.memo;

// Log what was exported
console.log('✅ [react-shim-wrapper] Exports set:', {
  useState: typeof module.exports.useState,
  useRef: typeof module.exports.useRef,
  createElement: typeof module.exports.createElement
});
