/**
 * Force Complete Reload Script
 * Force un rechargement complet de la page si besoin
 */

(function() {
  'use strict';

  console.log('[PDF Builder] force-complete-reload.js loaded');

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initForceCompleteReload);
  } else {
    initForceCompleteReload();
  }

  function initForceCompleteReload() {
    console.log('[PDF Builder] Checking for reload trigger');
    
    // Vérifier si on a un tag de reload dans localStorage
    const reloadKey = 'pdfBuilderForceReload';
    const lastReload = localStorage.getItem(reloadKey);
    const now = new Date().getTime();
    
    if (lastReload && (now - parseInt(lastReload)) < 60000) {
      console.log('[PDF Builder] Recently reloaded, skipping');
      localStorage.removeItem(reloadKey);
      return;
    }

    // Forcer le rechargement CSS
    const links = document.querySelectorAll('link[rel="stylesheet"]');
    links.forEach(link => {
      if (link.href.includes('pdf-builder')) {
        const timestamp = new Date().getTime();
        const separator = link.href.includes('?') ? '&' : '?';
        const newHref = link.href.split('?')[0].split('&')[0] + separator + 't=' + timestamp;
        link.href = newHref;
        console.log('[PDF Builder] Forced CSS reload:', link.href);
      }
    });

    // Stocker le timestamp du dernier reload
    localStorage.setItem(reloadKey, now.toString());
  }

  // Exposition globale pour accès externe
  window.pdfBuilderForceReload = {
    reload: function() {
      initForceCompleteReload();
    }
  };
})();
