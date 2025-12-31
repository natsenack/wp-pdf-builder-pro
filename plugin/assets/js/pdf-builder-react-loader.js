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
    
    // Debug: log full window.pdfBuilderReact structure
    if (typeof window.pdfBuilderReact !== 'undefined') {
      console.log('🔍 [PDF Builder Loader] window.pdfBuilderReact:', window.pdfBuilderReact);
      console.log('🔍 [PDF Builder Loader] typeof window.pdfBuilderReact.initPDFBuilderReact:', typeof window.pdfBuilderReact.initPDFBuilderReact);
      if (typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
        console.log('🔍 [PDF Builder Loader] Function content:', window.pdfBuilderReact.initPDFBuilderReact.toString().substring(0, 200));
      }
    }
    
    if (typeof window.pdfBuilderReact !== 'undefined' && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
      console.log('✅ [PDF Builder Loader] pdfBuilderReact found! Calling initPDFBuilderReact...');
      clearInterval(interval);
      
      try {
        var result = window.pdfBuilderReact.initPDFBuilderReact();
        console.log('✅ [PDF Builder Loader] initPDFBuilderReact called, result:', result);
        console.log('🔍 [PDF Builder Loader] Debug array after call:', window.pdfBuilderReactDebug);
      } catch (error) {
        console.error('❌ [PDF Builder Loader] Error calling initPDFBuilderReact:', error);
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
