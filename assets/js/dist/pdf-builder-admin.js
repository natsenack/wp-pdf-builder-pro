/******/ (() => { // webpackBootstrap
// Script ultra-minimal pour tester l'exécution de base
(function () {
  'use strict';

  // Définition immédiate de la variable globale
  if (typeof window !== 'undefined') {
    window.PDFBuilderPro = {
      test: 'EXECUTED',
      version: 'ultra-minimal',
      timestamp: Date.now(),
      init: function init(containerId, options) {
        console.log('PDFBuilderPro.init executed:', containerId, options);
        return {
          success: true,
          executed: true
        };
      }
    };

    // Log visible immédiatement
    console.log('🚀 PDF Builder Pro: ULTRA MINIMAL SCRIPT EXECUTED SUCCESSFULLY 🚀');
    console.log('window.PDFBuilderPro:', window.PDFBuilderPro);

    // Erreur visible pour confirmer
    throw new Error('✅ CONFIRMATION: PDF Builder Pro script executed at ' + new Date().toISOString());
  }
})();
/******/ })()
;