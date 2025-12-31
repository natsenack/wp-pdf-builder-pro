/**
 * PDF Builder React Loader
 * 
 * Simple wrapper that waits for the bundle to load and initializes it.
 * Executes immediately when this script loads.
 */

(function() {
  console.log('🔥 [PDF Builder Loader] LOADER SCRIPT EXECUTING - IMMEDIATE', new Date().toISOString());
  
  // Wait for the bundle UMD wrapper to create the pdfBuilderReact global
  var maxAttempts = 60;
  var attempt = 0;
  var interval;
  
  function checkAndInitialize() {
    attempt++;
    console.log('🔍 [PDF Builder Loader] Check attempt', attempt + '/' + maxAttempts, '- pdfBuilderReact available?', typeof window.pdfBuilderReact !== 'undefined');
    
    if (typeof window.pdfBuilderReact !== 'undefined' && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
      console.log('✅ [PDF Builder Loader] pdfBuilderReact found! Calling initPDFBuilderReact...');
      console.log('✅ [PDF Builder Loader] window.React type:', typeof window.React);
      console.log('✅ [PDF Builder Loader] window.ReactDOM type:', typeof window.ReactDOM);
      console.log('✅ [PDF Builder Loader] Container element:', document.getElementById('pdf-builder-react-root') ? 'FOUND' : 'NOT FOUND');
      
      clearInterval(interval);
      
      try {
        var result = window.pdfBuilderReact.initPDFBuilderReact();
        console.log('✅ [PDF Builder Loader] initPDFBuilderReact returned:', result);
        
        if (result === false) {
          console.warn('⚠️ [PDF Builder Loader] initPDFBuilderReact returned false - check bundle logs for details');
          // Try to capture what's in window to debug
          console.log('⚠️ [PDF Builder Loader] Available on window:', {
            React: typeof window.React,
            ReactDOM: typeof window.ReactDOM,
            container: !!document.getElementById('pdf-builder-react-root'),
            editor: !!document.getElementById('pdf-builder-react-editor'),
            pdfBuilderReact: !!window.pdfBuilderReact
          });
        }
      } catch (error) {
        console.error('❌ [PDF Builder Loader] EXCEPTION:', error.message);
        console.error('❌ [PDF Builder Loader] Stack:', error.stack);
      }
      return;
    }
    
    if (attempt >= maxAttempts) {
      console.error('❌ [PDF Builder Loader] Max attempts reached, pdfBuilderReact not available');
      clearInterval(interval);
      return;
    }
  }
  
  // Start checking immediately
  interval = setInterval(checkAndInitialize, 100);
  checkAndInitialize(); // Check immediately first
})();
