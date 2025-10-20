// Expose React globally for WordPress environment
import React from 'react';
import ReactDOM from 'react-dom';

// FORCER l'exposition globale - méthode agressive
window.React = React;
window.ReactDOM = ReactDOM;

// Vérification immédiate
console.log('React global exposure - React:', typeof window.React);
console.log('React global exposure - ReactDOM:', typeof window.ReactDOM);

// Test de fonctionnement
try {
  const testElement = React.createElement('div', null, 'test');
  console.log('React.createElement test successful');
} catch (e) {
  console.error('React.createElement test failed:', e);
}
