// ============================================================================
// PDF Builder React Bundle - Entry Point - IMMEDIATE EXECUTION
// ============================================================================

// Import the main PDF Builder component
import PDFBuilder from '@/ts/components/PDFBuilder';

// THIS CODE RUNS IMMEDIATELY - Not wrapped in a function
console.log('🔥 [PDF BUNDLE] IMMEDIATE EXECUTION - BOOTSTRAP PHASE');
console.log('🔥 [PDF BUNDLE] PDFBuilder imported, type:', typeof PDFBuilder);

// Get WordPress globals
var React = window.React;
var ReactDOM = window.ReactDOM;

console.log('🔥 [PDF BUNDLE] React available?', typeof React);
console.log('🔥 [PDF BUNDLE] ReactDOM available?', typeof ReactDOM);

// Define the initialization function
function initPDFBuilderReact() {
  console.log('🔧 [PDF BUNDLE] initPDFBuilderReact called');
  
  try {
    // Check for container
    var container = document.getElementById('pdf-builder-react-root');
    if (!container) {
      console.error('❌ [PDF BUNDLE] Container not found');
      return false;
    }
    
    console.log('✅ [PDF BUNDLE] Container found');
    
    // Check React
    if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
      console.error('❌ [PDF BUNDLE] React or ReactDOM not available');
      return false;
    }
    
    console.log('✅ [PDF BUNDLE] React dependencies OK');
    
    // Hide loading, show editor
    var loadingEl = document.getElementById('pdf-builder-react-loading');
    var editorEl = document.getElementById('pdf-builder-react-editor');
    
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    
    console.log('🎨 [PDF BUNDLE] Creating React root...');
    
    // Create root and render
    var root = ReactDOM.createRoot(container);
    var element = React.createElement(PDFBuilder);
    
    console.log('🎨 [PDF BUNDLE] Rendering component...');
    root.render(element);
    
    console.log('✅ [PDF BUNDLE] Rendered successfully');
    return true;
    
  } catch (error) {
    console.error('❌ [PDF BUNDLE] Error:', error.message);
    console.error('❌ [PDF BUNDLE] Stack:', error.stack);
    return false;
  }
}

// Export for external use
var exports = { initPDFBuilderReact };

console.log('🌐 [PDF BUNDLE] Assigning to window.pdfBuilderReact');

// Assign to window IMMEDIATELY
window.pdfBuilderReact = exports;

console.log('✅ [PDF BUNDLE] window.pdfBuilderReact assigned:', typeof window.pdfBuilderReact);
console.log('✅ [PDF BUNDLE] window.pdfBuilderReact.initPDFBuilderReact:', typeof window.pdfBuilderReact.initPDFBuilderReact);

// Export as default for webpack
export default exports;
