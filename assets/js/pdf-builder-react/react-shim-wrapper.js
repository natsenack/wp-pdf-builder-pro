/**
 * React Shim Wrapper
 * This module is aliased as 'react' in webpack to ensure all React hooks
 * are bundled with the code instead of relying on window.React externals
 * 
 * When components import React, they actually import from this file,
 * which re-exports everything from the WordPress global React
 */

// Get React from WordPress global (it's loaded before our bundle)
const React = window.React;

if (!React) {
  console.error('❌ [react-shim-wrapper] window.React is not available! WordPress React not loaded.');
  throw new Error('React is not available on window.React. Check script loading order.');
}

console.log('✅ [react-shim-wrapper] Providing React from window.React');

// Re-export everything from React so components can import it normally
module.exports = React;
module.exports.default = React;

// Also ensure hooks are available as named exports for tree-shaking
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

// Core React APIs
module.exports.createElement = React.createElement;
module.exports.Fragment = React.Fragment;
module.exports.cloneElement = React.cloneElement;
module.exports.isValidElement = React.isValidElement;
module.exports.createContext = React.createContext;
module.exports.forwardRef = React.forwardRef;
module.exports.lazy = React.lazy;
module.exports.Suspense = React.Suspense;
module.exports.memo = React.memo;
module.exports.createRef = React.createRef;
module.exports.Children = React.Children;
module.exports.StrictMode = React.StrictMode;
module.exports.Profiler = React.Profiler;
