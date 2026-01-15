/**
 * AJAX Throttle - Throttle les requêtes AJAX
 */

window.pdfBuilderAjaxThrottle = (function() {
  const pending = {};

  return {
    request: function(action, data) {
      if (pending[action]) {
        console.log('[PDF Builder] AJAX throttled:', action);
        return pending[action];
      }

      pending[action] = fetch(pdfBuilderAjax.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: action,
          nonce: pdfBuilderAjax.nonce,
          ...data
        })
      }).finally(() => {
        delete pending[action];
      });

      return pending[action];
    }
  };
})();

console.log('[PDF Builder] AJAX Throttle loaded');
