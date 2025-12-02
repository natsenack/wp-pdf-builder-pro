/**
 * PDF Builder React - Webpack Bundle Wrapper
 * Ensures the module is properly assigned to window when loaded
 * This file acts as the true webpack entry point that exports everything to window
 */

console.log('🔧 [WEBPACK BUNDLE] pdf-builder-react-wrapper.js loading...');

// Import the actual React module
import * as pdfBuilderReactModule from './pdf-builder-react/index.js';

// Force assignment to window IMMEDIATELY when webpack loads
if (typeof window !== 'undefined') {
  console.log('✅ [pdf-builder-wrapper] Webpack bundle executing, assigning to window.pdfBuilderReact');
  window.pdfBuilderReact = pdfBuilderReactModule.default || pdfBuilderReactModule;
  
  // Dispatch event to signal module is ready
  try {
    const event = new Event('pdfBuilderReactLoaded');
    document.dispatchEvent(event);
    console.log('✅ [pdf-builder-wrapper] pdfBuilderReactLoaded event dispatched');
  } catch (e) {
    console.error('[pdf-builder-wrapper] Error dispatching event:', e);
  }
}

// Also export it so webpack UMD can use it
export default pdfBuilderReactModule.default || pdfBuilderReactModule;
