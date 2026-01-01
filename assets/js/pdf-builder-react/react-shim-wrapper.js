/**
 * React Shim Wrapper - Dynamic Proxy + Global Injection
 * Provides access to React hooks at runtime
 * CRITICAL: Injects hooks into global scope so they're available to all transpiled code
 */

// Get React from WordPress global
const React = window.React;

if (!React) {
  console.error('❌ [react-shim-wrapper] window.React is not available!');
  throw new Error('React not available on window.React');
}

console.log('✅ [react-shim-wrapper] React shim loaded with', Object.keys(React).length, 'properties');
console.log('✅ [react-shim-wrapper] Hooks available - useState:', typeof React.useState, ', useRef:', typeof React.useRef);

// CRITICAL: Inject all React hooks into global scope for transpiled code to find them
// When Babel compiles "const { useRef } = React", it becomes "function(useRef) { ... }"
// The variable must exist in a scope - we put it in the global scope
if (typeof window !== 'undefined') {
  window.useState = React.useState;
  window.useEffect = React.useEffect;
  window.useRef = React.useRef;
  window.useCallback = React.useCallback;
  window.useMemo = React.useMemo;
  window.useContext = React.useContext;
  window.useReducer = React.useReducer;
  window.useLayoutEffect = React.useLayoutEffect;
  window.useId = React.useId;
  window.useTransition = React.useTransition;
  window.useDeferredValue = React.useDeferredValue;
  window.useImperativeHandle = React.useImperativeHandle;
  window.useDebugValue = React.useDebugValue;
  window.useSyncExternalStore = React.useSyncExternalStore;
  
  // Core APIs
  window.React = React;
  window.React_createElement = React.createElement;
  window.React_Fragment = React.Fragment;
  
  console.log('✅ [react-shim-wrapper] All hooks injected into window global scope');
}

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
  }
});

// Export the proxy - must use ES module syntax for webpack browser bundle
export default ReactProxy;

// Also export hooks explicitly for named imports (ES module syntax)
export const { 
  useState, 
  useEffect, 
  useRef, 
  useCallback, 
  useMemo, 
  useContext, 
  useReducer, 
  useLayoutEffect, 
  useId, 
  useTransition, 
  useDeferredValue, 
  useImperativeHandle, 
  useDebugValue, 
  useSyncExternalStore,
  // Core APIs
  createElement,
  Fragment,
  createContext,
  memo
} = React;

// Log what was exported
console.log('✅ [react-shim-wrapper] ES Module exports configured:', {
  useState: typeof useState,
  useRef: typeof useRef,
  createElement: typeof createElement
});
