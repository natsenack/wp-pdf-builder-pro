/**
 * PDF Builder Utils
 * Utilitaires généraux
 */

window.pdfBuilderUtils = {
  log: function(msg, data) {
    console.log('[PDF Builder]', msg, data);
  },
  error: function(msg, error) {
    console.error('[PDF Builder]', msg, error);
  },
  debounce: function(func, wait) {
    let timeout;
    return function() {
      clearTimeout(timeout);
      timeout = setTimeout(func, wait);
    };
  }
};

console.log('[PDF Builder] Utils loaded');
