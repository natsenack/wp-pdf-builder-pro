/**
 * JS Syntax Check Script
 * Vérifie la syntaxe des fichiers JS chargés et bloque en cas d'erreur
 */

(function() {
  'use strict';

  console.log('[PDF Builder] js-syntax-check.js loaded');

  // Collecter les erreurs
  const jsErrors = [];
  
  // Écouter les erreurs de script
  window.addEventListener('error', function(e) {
    if (e.filename && e.filename.includes('pdf-builder')) {
      jsErrors.push('[' + e.filename + ':' + e.lineno + '] ' + e.message);
      console.error('[PDF Builder] Script error detected:', e.message, 'at', e.filename);
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkSyntax);
  } else {
    checkSyntax();
  }

  function checkSyntax() {
    console.log('[PDF Builder] Running JS syntax checks');
    
    const scripts = document.querySelectorAll('script[src*="pdf-builder"]');
    const missingScripts = [];
    
    scripts.forEach(script => {
      try {
        if (script.src) {
          console.log('[PDF Builder] Script loaded:', script.src);
          
          // Vérifier si le script a eu des erreurs de chargement
          if (!script.getAttribute('data-loaded')) {
            // On peut ajouter une flag 'data-loaded' au script si besoin
          }
        }
      } catch (e) {
        console.error('[PDF Builder] Script error:', e);
        jsErrors.push('Script error: ' + e.message);
      }
    });

    // Vérifier les erreurs CSS 404
    checkCSSErrors();

    // Vérifier les erreurs JS globales
    if (jsErrors.length > 0) {
      console.warn('[PDF Builder] Found ' + jsErrors.length + ' error(s)');
      blockPageWithErrors(jsErrors);
    } else {
      console.log('[PDF Builder] All scripts syntax OK');
    }
  }

  function checkCSSErrors() {
    // Écouter les erreurs de chargement de CSS
    const links = document.querySelectorAll('link[rel="stylesheet"]');
    links.forEach(link => {
      if (link.href && link.href.includes('pdf-builder')) {
        link.addEventListener('error', function() {
          jsErrors.push('CSS 404: ' + this.href);
          console.error('[PDF Builder] CSS failed to load:', this.href);
        });
      }
    });
  }

  function blockPageWithErrors(errors) {
    // Afficher immédiatement l'overlay d'erreur
    if (window.pdfBuilderForceReload && window.pdfBuilderForceReload.addError) {
      errors.forEach(error => {
        window.pdfBuilderForceReload.addError(error);
      });
      
      // Afficher l'overlay avec les erreurs
      showErrorOverlay(errors);
    }
  }

  function showErrorOverlay(errors) {
    // Créer l'overlay de blocage
    const overlay = document.createElement('div');
    overlay.id = 'pdf-builder-error-overlay';
    overlay.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 999999;
    `;

    const errorBox = document.createElement('div');
    errorBox.style.cssText = `
      background: white;
      padding: 40px;
      border-radius: 8px;
      max-width: 600px;
      max-height: 70vh;
      overflow-y: auto;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    `;

    let errorHTML = `
      <h2 style="color: #d32f2f; margin-bottom: 20px;">⚠️ Erreurs détectées</h2>
      <p style="color: #666; margin-bottom: 20px;">La page a rencontré des problèmes lors du chargement. Les erreurs suivantes ont été enregistrées:</p>
      <div style="background: #f5f5f5; padding: 15px; border-left: 4px solid #d32f2f; margin-bottom: 20px;">
    `;

    errors.forEach((error, index) => {
      errorHTML += `
        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
          <strong style="color: #d32f2f;">Erreur ${index + 1}:</strong><br>
          <code style="display: block; background: white; padding: 10px; margin-top: 5px; font-size: 12px; white-space: pre-wrap; word-break: break-all;">
            ${escapeHtml(error)}
          </code>
        </div>
      `;
    });

    errorHTML += `
      </div>
      <p style="color: #999; font-size: 12px; margin-bottom: 20px;">
        <em>Temps: ${new Date().toLocaleString()}</em>
      </p>
      <div style="display: flex; gap: 10px;">
        <button id="pdf-builder-retry-btn" style="flex: 1; padding: 12px; background: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
          ↻ Réessayer
        </button>
        <button id="pdf-builder-continue-btn" style="flex: 1; padding: 12px; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
          Continuer quand même
        </button>
      </div>
    `;

    errorBox.innerHTML = errorHTML;
    overlay.appendChild(errorBox);
    document.body.appendChild(overlay);

    // Ajouter les event listeners
    document.getElementById('pdf-builder-retry-btn').addEventListener('click', function() {
      location.reload();
    });

    document.getElementById('pdf-builder-continue-btn').addEventListener('click', function() {
      overlay.remove();
      console.log('[PDF Builder] User chose to continue despite errors');
    });
  }

  function escapeHtml(text) {
    if (!text) return '';
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  }
})();
