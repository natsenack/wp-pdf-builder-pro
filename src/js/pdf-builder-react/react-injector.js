/**
 * React Injector
 * Ensures React is available globally and in module scopes
 * Import this first in any bundled code that needs React
 */

if (typeof window !== 'undefined' && window.React) {
  globalThis.React = window.React;
  globalThis.ReactDOM = window.ReactDOM;
  console.log('✅ [react-injector] React + ReactDOM injected into global scope');
} else {
  console.warn('[react-injector] React not yet available on window');
}

export default window.React;
