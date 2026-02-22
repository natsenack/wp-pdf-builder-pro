/**
 * React Shim Wrapper - Dynamic Proxy + Global Injection
 * Provides access to React hooks at runtime
 * CRITICAL: Injects hooks into global scope so they're available to all transpiled code
 */

// Get React from WordPress global
const React = window.React;

if (!React) {
  throw new Error('React not available on window.React');
}

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
}

// Create a Proxy that dynamically resolves React properties at access time
const ReactProxy = new Proxy(React, {
  get: function(target, prop, receiver) {
    const value = Reflect.get(target, prop, receiver);
    if (typeof prop === 'string' && prop.startsWith('use')) {
      if (!value) {
        return window.React[prop];
      }
    }
    return value;
  }
});

// Export the proxy as default
module.exports = ReactProxy;
module.exports.default = ReactProxy;

// Also export hooks explicitly for named imports
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

