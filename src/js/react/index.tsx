/**
 * MINIMAL TEST - PDF Builder React Bundle
 */

// IMMEDIATE EXECUTION - Test if bundle runs at all
(function() {
  console.log('[MINIMAL BUNDLE] ===== BUNDLE IS EXECUTING =====');
  console.log('[MINIMAL BUNDLE] Timestamp:', new Date().toISOString());
  console.log('[MINIMAL BUNDLE] Window available:', typeof window !== 'undefined');
  console.log('[MINIMAL BUNDLE] Document available:', typeof document !== 'undefined');

  // Test basic functionality
  if (typeof window !== 'undefined') {
    window.pdfBuilderBundleExecuted = 'YES_' + Date.now();
    console.log('[MINIMAL BUNDLE] Set test variable:', window.pdfBuilderBundleExecuted);
  }
})();

// Create minimal API without any imports
console.log('[MINIMAL BUNDLE] Creating minimal API...');

const minimalApi = {
  initPDFBuilderReact: function(containerId: string) {
    console.log('[MINIMAL API] initPDFBuilderReact called with:', containerId);

    const container = document.getElementById(containerId);
    if (container) {
      container.innerHTML = `
        <div style="padding: 20px; border: 2px solid green; border-radius: 5px; background: #e8f5e8; margin: 20px;">
          <h3 style="color: green; margin: 0 0 10px 0;">✅ PDF Builder Bundle Working!</h3>
          <p style="margin: 5px 0;">The React bundle is executing correctly.</p>
          <p style="margin: 5px 0;">Container ID: <strong>${containerId}</strong></p>
          <p style="margin: 5px 0;">Timestamp: <strong>${new Date().toISOString()}</strong></p>
          <p style="margin: 5px 0; color: blue;">Next step: Restore React functionality</p>
        </div>
      `;
      console.log('[MINIMAL API] Container updated successfully');
      return true;
    } else {
      console.error('[MINIMAL API] Container not found:', containerId);
      return false;
    }
  },
  version: '0.1.0-minimal-test',
  bundleExecuted: true,
  timestamp: Date.now()
};

console.log('[MINIMAL BUNDLE] API created:', minimalApi);

// Export as default - webpack will handle this
export default minimalApi;

// Also assign to window immediately and with delay to ensure it works
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = minimalApi;
  console.log('[MINIMAL BUNDLE] window.pdfBuilderReact assigned immediately');

  // Also assign with a small delay in case webpack needs time
  setTimeout(() => {
    window.pdfBuilderReact = minimalApi;
    console.log('[MINIMAL BUNDLE] window.pdfBuilderReact assigned with delay');
  }, 1);
} else {
  console.error('[MINIMAL BUNDLE] Window not available!');
}

console.log('[MINIMAL BUNDLE] ===== BUNDLE EXECUTION COMPLETE =====');
