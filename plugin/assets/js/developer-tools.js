/**
 * Developer Tools
 */

(function () {
  "use strict";

  console.log("[PDF Builder] Developer Tools loaded");

  window.pdfBuilderDevTools = {
    logState: function (state) {
      console.table(state);
    },
    clearCache: function () {
      localStorage.clear();
      console.log("[PDF Builder] Cache cleared");
    },
  };
})();
