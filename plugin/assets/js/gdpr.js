/**
 * GDPR Manager
 */

(function() {
  'use strict';

  console.log('[PDF Builder] GDPR Manager loaded');

  window.pdfBuilderGDPR = {
    consent: function() {
      return localStorage.getItem('pdfBuilderConsent') === 'accepted';
    }
  };
})();
