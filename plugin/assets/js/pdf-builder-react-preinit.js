// =============================================================
// PDF Builder React - PRE-INITIALIZATION SCRIPT
// Runs INLINE in HTML BEFORE webpack bundle loads
// =============================================================

console.log('🔥 [PDF BUILDER] PRE-INIT: Script injected inline');

// Pre-assign window.pdfBuilderReact as a placeholder
// This ensures the variable exists before webpack runs
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = window.pdfBuilderReact || {};
  console.log('🔥 [PDF BUILDER] PRE-INIT: window.pdfBuilderReact initialized');
}
