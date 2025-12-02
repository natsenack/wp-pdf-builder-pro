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

// Log what was exported
console.log('✅ [react-shim-wrapper] Module exports set:', {
  useState: typeof module.exports.useState,
  useRef: typeof module.exports.useRef,
  createElement: typeof module.exports.createElement
});

// 🔧 POLYFILL: Force passive: true for all addEventListener calls by default
// This eliminates "[Violation] Added non-passive event listener" warnings
// Events that need preventDefault() explicitly set passive: false
if (typeof window !== 'undefined' && window.EventTarget) {
  const originalAddEventListener = window.EventTarget.prototype.addEventListener;
  
  window.EventTarget.prototype.addEventListener = function(type, listener, options) {
    // Events that need preventDefault capability (scroll-blocking)
    const scrollBlockingEvents = new Set([
      'touchstart', 'touchmove', 'wheel',
      'mousedown', 'mousemove', 'mouseup'
    ]);
    
    // If options is a boolean, keep it as-is (for backward compatibility)
    if (typeof options === 'boolean') {
      return originalAddEventListener.call(this, type, listener, options);
    }
    
    // If options is an object, ensure passive is set appropriately
    if (typeof options === 'object' && options !== null) {
      // Only override if passive wasn't explicitly set
      if (!('passive' in options)) {
        // For scroll-blocking events without explicit passive, default to false to allow preventDefault
        if (scrollBlockingEvents.has(type)) {
          // Keep the original call for events that might need preventDefault
          return originalAddEventListener.call(this, type, listener, options);
        } else {
          // For non-blocking events, force passive: true
          options.passive = true;
        }
      }
      return originalAddEventListener.call(this, type, listener, options);
    }
    
    // If no options provided, add passive: true for non-blocking events
    if (typeof options === 'undefined') {
      return originalAddEventListener.call(this, type, listener, { passive: true });
    }
    
    return originalAddEventListener.call(this, type, listener, options);
  };
  
  console.log('✅ [react-shim-wrapper] Passive event listener polyfill installed');
}
