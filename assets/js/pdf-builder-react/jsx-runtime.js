/**
 * React JSX Runtime Shim
 * Provides the JSX runtime for automatic JSX transform
 * This is imported automatically when using @babel/preset-react with runtime: 'automatic'
 */

const React = window.React;

if (!React) {
  console.error('❌ [jsx-runtime] window.React is not available!');
  throw new Error('React is not available on window.React');
}

console.log('✅ [jsx-runtime] React JSX runtime loaded from window.React');

// Export the JSX functions using ES module syntax
export const jsx = React.createElement;
export const jsxs = React.createElement;
export const Fragment = React.Fragment;

export default {
  jsx: React.createElement,
  jsxs: React.createElement,
  Fragment: React.Fragment
};
