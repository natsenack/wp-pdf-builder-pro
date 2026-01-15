/**
 * Force CSS Reload Script
 * Nettoie le cache des styles et force le rechargement
 */

(function() {
  'use strict';

  console.log('[PDF Builder] force-css-reload.js loaded');

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initForceReload);
  } else {
    initForceReload();
  }

  function initForceReload() {
    console.log('[PDF Builder] Initializing force CSS reload');
    
    const links = document.querySelectorAll('link[rel="stylesheet"]');
    links.forEach(link => {
      if (link.href.includes('pdf-builder')) {
        const timestamp = new Date().getTime();
        const separator = link.href.includes('?') ? '&' : '?';
        link.href = link.href + separator + 't=' + timestamp;
        console.log('[PDF Builder] Reloaded CSS:', link.href);
      }
    });
  }
})();
