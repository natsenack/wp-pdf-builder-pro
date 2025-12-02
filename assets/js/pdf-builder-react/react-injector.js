/**
 * React Injector
 * This file is imported first and injects React into the global scope
 * so that all transpiled JSX code can access it as a variable
 */

// Ensure React is available as a global for all transpiled code
if (typeof window !== 'undefined' && window.React) {
  // Create a global React variable accessible to all module scopes
  globalThis.React = window.React;
  globalThis.ReactDOM = window.ReactDOM;
  
  console.log('✅ [react-injector] React injected into global scope');
} else {
  console.error('❌ [react-injector] window.React not available!');
}

// Export for ES modules too
export default window.React;
