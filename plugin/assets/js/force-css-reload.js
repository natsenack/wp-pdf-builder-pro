/**
 * Force CSS Reload Script - DISABLED
 * Seulement logger les CSS chargés, pas de modification
 */

(function () {
  "use strict";

  console.log("[PDF Builder] force-css-reload.js loaded - PASSIVE MODE");

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", logCSSFiles);
  } else {
    logCSSFiles();
  }

  function logCSSFiles() {
    console.log("[PDF Builder] Logging CSS files loaded");

    const links = document.querySelectorAll('link[rel="stylesheet"]');
    let count = 0;
    links.forEach((link) => {
      if (link.href.includes("pdf-builder")) {
        console.log("[PDF Builder] CSS loaded:", link.href);
        count++;
      }
    });
    console.log("[PDF Builder] Total PDF Builder CSS files:", count);
  }
})();
