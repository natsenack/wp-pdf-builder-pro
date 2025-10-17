/******/ (() => { // webpackBootstrap
// Script de test minimal pour diagnostiquer les problèmes de chargement
console.log('PDF Builder Pro: Script execution started');

// Test immédiat de définition de variable globale
if (typeof window !== 'undefined') {
  window.PDFBuilderPro = {
    test: 'ok',
    version: 'debug-test',
    init: function init(containerId, options) {
      console.log('PDFBuilderPro.init called with:', containerId, options);
      return {
        success: true
      };
    }
  };
  console.log('PDF Builder Pro: Minimal PDFBuilderPro defined on window');
}

// Test de chargement React (commenté pour éviter les erreurs)
// try {
//     console.log('Testing React import...');
//     import React from 'react';
//     import ReactDOM from 'react-dom';
//     console.log('React loaded successfully');
// } catch (error) {
//     console.error('React loading failed:', error);
// }
/******/ })()
;