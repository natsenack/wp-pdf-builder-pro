// ============================================================================
// PDF Builder React Bundle - Entry Point - IMMEDIATE EXECUTION
// ============================================================================

// FORCE IMMEDIATE EXECUTION - These run BEFORE module wrapping
if (typeof window !== 'undefined') {
  window._pdfBundleStarting = true;
  console.log('🔥 [PDF BUNDLE] WINDOW CONTEXT AVAILABLE - Starting bootstrap');
  console.log('🔥 [PDF BUNDLE] React available?', typeof window.React);
  console.log('🔥 [PDF BUNDLE] ReactDOM available?', typeof window.ReactDOM);
}

// Import the main PDF Builder component
import PDFBuilder from '@/ts/components/PDFBuilder';

// THIS CODE RUNS IMMEDIATELY - Not wrapped in a function
console.log('🔥 [PDF BUNDLE] BOOTSTRAP PHASE - After imports');
console.log('🔥 [PDF BUNDLE] PDFBuilder imported, type:', typeof PDFBuilder);

// Get WordPress globals
var React = window.React;
var ReactDOM = window.ReactDOM;

console.log('🔥 [PDF BUNDLE] React global assignment done');
console.log('🔥 [PDF BUNDLE] ReactDOM global assignment done');

// Define the initialization function
function initPDFBuilderReact() {
  console.log('🔧 [PDF BUNDLE] initPDFBuilderReact CALLED');
  console.log('🔧 [PDF BUNDLE] React type:', typeof React, 'is function?', typeof React === 'function');
  console.log('🔧 [PDF BUNDLE] ReactDOM type:', typeof ReactDOM, 'has createRoot?', ReactDOM && typeof ReactDOM.createRoot === 'function');
  console.log('🔧 [PDF BUNDLE] PDFBuilder type:', typeof PDFBuilder);
  
  try {
    // Check for container
    var container = document.getElementById('pdf-builder-react-root');
    console.log('🔧 [PDF BUNDLE] Container element:', container ? 'FOUND' : 'NOT FOUND', container);
    if (!container) {
      console.error('❌ [PDF BUNDLE] ERROR: Container not found');
      return false;
    }
    
    console.log('✅ [PDF BUNDLE] Container found, type:', container.constructor.name);
    
    // Check React
    if (typeof React === 'undefined' || !React) {
      console.error('❌ [PDF BUNDLE] ERROR: React undefined or null');
      return false;
    }
    if (typeof ReactDOM === 'undefined' || !ReactDOM) {
      console.error('❌ [PDF BUNDLE] ERROR: ReactDOM undefined or null');
      return false;
    }
    if (typeof ReactDOM.createRoot !== 'function') {
      console.error('❌ [PDF BUNDLE] ERROR: ReactDOM.createRoot not a function, available methods:', Object.keys(ReactDOM));
      return false;
    }
    
    console.log('✅ [PDF BUNDLE] React dependencies validated');
    console.log('✅ [PDF BUNDLE] React.createElement:', typeof React.createElement);
    
    // Hide loading, show editor
    var loadingEl = document.getElementById('pdf-builder-react-loading');
    var editorEl = document.getElementById('pdf-builder-react-editor');
    
    console.log('🔧 [PDF BUNDLE] Loading element:', loadingEl ? 'found' : 'not found');
    console.log('🔧 [PDF BUNDLE] Editor element:', editorEl ? 'found' : 'not found');
    
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    
    console.log('🎨 [PDF BUNDLE] Creating React root...');
    var root = ReactDOM.createRoot(container);
    console.log('🎨 [PDF BUNDLE] Root created:', typeof root);
    
    console.log('🎨 [PDF BUNDLE] Creating element from PDFBuilder component...');
    var element = React.createElement(PDFBuilder);
    console.log('🎨 [PDF BUNDLE] Element created:', element ? 'SUCCESS' : 'FAILED');
    
    console.log('🎨 [PDF BUNDLE] Rendering to root...');
    root.render(element);
    
    console.log('✅ [PDF BUNDLE] Rendered successfully!');
    return true;
    
  } catch (error) {
    console.error('❌ [PDF BUNDLE] EXCEPTION:', error.message);
    console.error('❌ [PDF BUNDLE] Stack:', error.stack);
    console.error('❌ [PDF BUNDLE] Error object:', error);
    return false;
  }
}

// PRE-ASSIGN to window BEFORE webpack wrapper (this gets overwritten by UMD but we override it later)
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = { initPDFBuilderReact: initPDFBuilderReact };
  console.log('🌐 [PDF BUNDLE] Pre-assigned to window.pdfBuilderReact:', typeof window.pdfBuilderReact);
}

// Export for webpack UMD (but this will be ignored by our plugin)
export default { initPDFBuilderReact };
export { initPDFBuilderReact };
