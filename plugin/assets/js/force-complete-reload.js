/**
 * Force Complete Reload Script - DISABLED
 * Système de blocage d'erreurs sans auto-reload
 * Ne s'exécute que si des erreurs sont détectées
 */

(function () {
  "use strict";

  console.log(
    "[PDF Builder] force-complete-reload.js loaded - DISABLED AUTO-RELOAD"
  );

  // NE PAS exécuter automatiquement
  // Attendre que js-syntax-check.js détecte les erreurs

  function showErrorOverlay(errors) {
    // Créer l'overlay de blocage
    const overlay = document.createElement("div");
    overlay.id = "pdf-builder-error-overlay";
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

    const errorBox = document.createElement("div");
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
    document
      .getElementById("pdf-builder-retry-btn")
      .addEventListener("click", function () {
        location.reload();
      });

    document
      .getElementById("pdf-builder-continue-btn")
      .addEventListener("click", function () {
        overlay.remove();
        console.log("[PDF Builder] User chose to continue despite errors");
      });

    // Bloquer les clics sur la page
    document.addEventListener(
      "click",
      function (e) {
        if (
          e.target.id !== "pdf-builder-retry-btn" &&
          e.target.id !== "pdf-builder-continue-btn"
        ) {
          if (!e.target.closest("#pdf-builder-error-overlay")) {
            e.stopPropagation();
          }
        }
      },
      true
    );
  }

  function escapeHtml(text) {
    if (!text) return "";
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
  }

  // Exposition globale pour accès externe
  window.pdfBuilderForceReload = {
    addError: function (error) {
      const errorKey = "pdfBuilderErrors";
      const errors = JSON.parse(localStorage.getItem(errorKey) || "[]");
      errors.push(error);
      localStorage.setItem(errorKey, JSON.stringify(errors));
    },

    showErrors: function (errors) {
      if (errors && errors.length > 0) {
        showErrorOverlay(errors);
      }
    },

    reload: function () {
      // Ne rien faire - pas de reload automatique
      console.log("[PDF Builder] Reload requested but auto-reload is disabled");
    },
  };
})();
