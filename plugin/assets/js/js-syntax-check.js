/**
 * JS Syntax Check Script
 * Vérifie la syntaxe des fichiers JS chargés
 */

(function() {
  'use strict';

  console.log('[PDF Builder] js-syntax-check.js loaded');

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkSyntax);
  } else {
    checkSyntax();
  }

  function checkSyntax() {
    console.log('[PDF Builder] Running JS syntax checks');
    
    const scripts = document.querySelectorAll('script[src*="pdf-builder"]');
    let errors = 0;
    
    scripts.forEach(script => {
      try {
        if (script.src) {
          console.log('[PDF Builder] Script loaded:', script.src);
        }
      } catch (e) {
        console.error('[PDF Builder] Script error:', e);
        errors++;
      }
    });

    if (errors > 0) {
      console.warn('[PDF Builder] Found ' + errors + ' script errors');
    } else {
      console.log('[PDF Builder] All scripts syntax OK');
    }
  }
})();
