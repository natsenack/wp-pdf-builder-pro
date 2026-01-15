/**
 * PDF Builder Wrap Helper
 */

(function() {
  'use strict';

  console.log('[PDF Builder] PDF Builder Wrap loaded');

  window.pdfBuilderWrap = {
    dispatch: function(eventName, data) {
      const event = new CustomEvent(eventName, { detail: data });
      document.dispatchEvent(event);
      console.log('[PDF Builder] Event dispatched:', eventName);
    },
    on: function(eventName, callback) {
      document.addEventListener(eventName, function(e) {
        callback(e.detail);
      });
    }
  };
})();
