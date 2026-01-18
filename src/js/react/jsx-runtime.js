/**
 * React JSX Runtime Shim
 * Provides the JSX runtime for automatic JSX transform
 * This is imported automatically when using @babel/preset-react with runtime: 'automatic'
 */

const React = window.React;

if (!React) {
  throw new Error('React is not available on window.React');
}

// Export the JSX functions
module.exports = {
  jsx: React.createElement,
  jsxs: React.createElement,
  Fragment: React.Fragment
};

module.exports.default = module.exports;

