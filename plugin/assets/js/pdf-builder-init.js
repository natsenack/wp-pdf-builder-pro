/**
 * PDF Builder Init Helper
 */

(function () {
  "use strict";

  console.log("[PDF Builder] Init helper loaded");

  window.pdfBuilderInit = {
    start: function (config) {
      console.log("[PDF Builder] Starting initialization with config:", config);

      if (window.pdfBuilderReactWrapper && window.pdfBuilderReactWrapper.init) {
        return window.pdfBuilderReactWrapper.init(config);
      } else {
        console.error("[PDF Builder] React wrapper not available");
        return false;
      }
    },
  };

  // Chercher le root element et initialiser automatiquement
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      const rootElement = document.getElementById("pdf-builder-root");
      if (rootElement && window.pdfBuilderData) {
        console.log("[PDF Builder] Auto-initializing React");
        window.pdfBuilderInit.start(window.pdfBuilderData);
      }
    });
  }
})();
