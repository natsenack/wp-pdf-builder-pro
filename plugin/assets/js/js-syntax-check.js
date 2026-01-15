/**
 * JS Syntax Check Script - DISABLED
 * Seulement log les erreurs, pas d'affichage d'overlay ni de reload
 */

(function () {
  "use strict";

  console.log("[PDF Builder] js-syntax-check.js loaded - DISABLED MODE");

  // Juste logger les erreurs détectées mais ne rien faire
  window.addEventListener("error", function (e) {
    if (e.filename && e.filename.includes("pdf-builder")) {
      console.error(
        "[PDF Builder] Script error detected:",
        e.message,
        "at",
        e.filename
      );
    }
  });

  // Logging passif des scripts chargés
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", logScripts);
  } else {
    logScripts();
  }

  function logScripts() {
    console.log("[PDF Builder] All scripts loaded, no blocking");
  }
})();
