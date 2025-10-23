// Expose React globally for WordPress environment
import React from 'react';
import ReactDOM from 'react-dom';

// FORCER l'exposition globale - méthode agressive
window.React = React;
window.ReactDOM = ReactDOM;

// Vérification immédiate

// Test de fonctionnement
try {
  const testElement = React.createElement('div', null, 'test');
} catch (e) {
  // React.createElement test failed
}
