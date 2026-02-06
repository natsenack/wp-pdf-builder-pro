/**
 * Main entry point for PDF Builder React bundle V2
 *
 * This is the REAL PDF Builder with:
 * - Header toolbar with all controls
 * - Sidebar with element library
 * - Canvas editor with full functionality
 * - Properties panel for element editing
 */

// ============================================================================
// IMMEDIATE EXECUTION WRAPPER - Ensures module runs on load
// ============================================================================
(function() {
  // LOG IMMEDIATELY - BEFORE ANYTHING ELSE
  console.log('[REACT INDEX] ===== FILE LOADED =====');
  console.log('[REACT INDEX] React bundle loaded and executing at:', new Date().toISOString());
  console.log('[REACT INDEX] Window object available:', typeof window);
  console.log('[REACT INDEX] Document object available:', typeof document);
  console.log('[REACT INDEX] window.React available:', typeof (window as any).React);
  console.log('[REACT INDEX] window.ReactDOM available:', typeof (window as any).ReactDOM);
})();

// LOG IMMEDIATELY - BEFORE ANYTHING ELSE
console.log('[REACT INDEX] ===== FILE LOADED =====');
console.log('[REACT INDEX] React bundle loaded and executing at:', new Date().toISOString());
console.log('[REACT INDEX] Window object available:', typeof window);
console.log('[REACT INDEX] Document object available:', typeof document);
console.log('[REACT INDEX] window.React available:', typeof (window as any).React);
console.log('[REACT INDEX] window.ReactDOM available:', typeof (window as any).ReactDOM);

// CREATE FALLBACK API IMMEDIATELY - BEFORE ANYTHING ELSE
// This ensures the API exists even if React imports fail
if (typeof (window as any).pdfBuilderReact === 'undefined') {
  console.log('[REACT INDEX] Creating fallback pdfBuilderReact API...');
  (window as any).pdfBuilderReact = {
    initPDFBuilderReact: (containerId: string = 'pdf-builder-react-root') => {
      console.log('[REACT INDEX] Fallback init called for:', containerId);
      const container = document.getElementById(containerId);
      if (container) {
        container.innerHTML = '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107;">React dependencies loading...</div>';
      }
      return false;
    },
    version: '2.0.0',
    _fallbackMode: true
  };
  console.log('[REACT INDEX] Fallback API created');
}

import React from 'react';

// Support both WordPress React (no createRoot) and modern React
let createRoot: any;
console.log('[REACT INDEX] Detecting React environment...');

if (typeof (window as any).ReactDOM !== 'undefined' && (window as any).ReactDOM.createRoot) {
  console.log('[REACT INDEX] Using WordPress ReactDOM.createRoot');
  createRoot = (window as any).ReactDOM.createRoot;
} else if (typeof (window as any).ReactDOM !== 'undefined') {
  // Fallback for WordPress React without createRoot
  console.log('[REACT INDEX] Using WordPress ReactDOM.render (fallback)');
  createRoot = (container: any) => ({
    render: (element: React.ReactElement) => {
      (window as any).ReactDOM.render(element, container);
    },
    unmount: () => {
      (window as any).ReactDOM.unmountComponentAtNode(container);
    }
  });
} else {
  // If ReactDOM is not available, try requiring from node_modules
  console.log('[REACT INDEX] ReactDOM not found in window, trying node_modules');
  try {
    const { createRoot: cr } = require('react-dom/client');
    createRoot = cr;
    console.log('[REACT INDEX] Got createRoot from node_modules');
  } catch (e) {
    console.error('[REACT INDEX] Failed to load react-dom/client:', e);
    // Return a no-op
    createRoot = (container: any) => ({
      render: () => {
        console.warn('[REACT INDEX] createRoot is not available');
      },
      unmount: () => {}
    });
  }
}

import { PDFBuilder } from './PDFBuilder';
import createLogger from '@utils/logger';
import { getDOMContainer } from '@utils/dom';
import '../../css/main.css';

const logger = createLogger('PDFBuilderReact');

// ============================================================================
// MODULE INITIALIZATION
// ============================================================================

logger.info('🚀 PDF Builder V2 module execution started');
// console.log('[PDF Builder React] Module loaded, React available:', typeof React);
// console.log('[PDF Builder React] createRoot available:', typeof createRoot);

let reactRoot: any = null;

// ============================================================================
// MAIN INITIALIZATION FUNCTION
// ============================================================================

/**
 * Initialize the PDF Builder React application
 * @param containerId - HTML element id where React will mount
 * @returns true if initialization succeeded, false otherwise
 */
function initPDFBuilderReact(containerId: string = 'pdf-builder-react-root'): boolean {
  console.log('[REACT INDEX] ===== initPDFBuilderReact CALLED =====');
  console.log('[REACT INDEX] initPDFBuilderReact called with containerId:', containerId);
  console.log('[REACT INDEX] Document readyState:', document.readyState);
  console.log('[REACT INDEX] Container element exists:', !!document.getElementById(containerId));
  logger.info(`Initializing PDF Builder in container: ${containerId}`);
  // console.log('[PDF Builder React] initPDFBuilderReact called with containerId:', containerId);
  
  try {
    // Get container
    const container = getDOMContainer(containerId);
    // console.log('[PDF Builder React] Container found:', !!container, 'Element:', container);
    if (!container) {
      logger.error(`Container not found: ${containerId}`);
      // console.error('[PDF Builder React] Container not found:', containerId);
      return false;
    }

    // Create React root
    reactRoot = createRoot(container);
    logger.debug('React root created');
    // console.log('[PDF Builder React] React root created successfully');

    // Render the REAL PDF Builder component
    // console.log('[PDF Builder React] About to render PDFBuilder component');
    reactRoot.render(
      <PDFBuilder 
        width={1200} 
        height={800}
        className="pdf-builder-main"
      />
    );
    logger.info('✅ PDF Builder rendered successfully');
    // console.log('[PDF Builder React] PDFBuilder component rendered successfully');

    return true;
  } catch (error) {
    logger.error('Initialization failed:', error);
    
    // Try to render error message
    try {
      const container = getDOMContainer(containerId);
      if (container) {
        const err = error instanceof Error ? error : new Error(String(error));
        container.innerHTML = `
          <div style="padding: 20px; background: #fee; border: 1px solid #f00; border-radius: 4px; color: #c00;">
            <h2>❌ Error Loading PDF Builder</h2>
            <p>${err.message}</p>
          </div>
        `;
      }
    } catch (renderError) {
      logger.error('Failed to render error message:', renderError);
    }

    return false;
  }
}

// ============================================================================
// WINDOW EXPORTS
// ============================================================================

interface PDFBuilderReactAPI {
  initPDFBuilderReact: (containerId?: string) => boolean;
  version: string;
  logger: any;
  PDFBuilder?: typeof PDFBuilder;
}

const api: PDFBuilderReactAPI = {
  initPDFBuilderReact,
  version: '2.0.0',
  logger,
  PDFBuilder,
};

// Export to window
console.log('[REACT INDEX] About to export API to window...');
if (typeof window !== 'undefined') {
  console.log('[REACT INDEX] window is defined, assigning pdfBuilderReact');
  (window as any).pdfBuilderReact = api;
  (window as any).initPDFBuilderReact = initPDFBuilderReact;
  console.log('[REACT INDEX] ✅ pdfBuilderReact assigned to window');
  console.log('[REACT INDEX] window.pdfBuilderReact type:', typeof (window as any).pdfBuilderReact);
  console.log('[REACT INDEX] window.pdfBuilderReact.initPDFBuilderReact type:', typeof (window as any).pdfBuilderReact?.initPDFBuilderReact);
  logger.info('✅ PDF Builder API exported to window');
} else {
  console.error('[REACT INDEX] window is not defined!');
}

// ============================================================================
// IMMEDIATE INITIALIZATION CHECK
// ============================================================================
// Try to auto-init if root element exists and document is ready
(function() {
  console.log('[REACT INDEX] === AUTO-INIT CHECK ===');
  console.log('[REACT INDEX] Document ready state:', document.readyState);
  
  const rootElement = document.getElementById('pdf-builder-react-root');
  console.log('[REACT INDEX] Root element exists:', !!rootElement);
  
  if (rootElement && document.readyState === 'complete') {
    console.log('[REACT INDEX] Auto-initializing because DOM is ready...');
    if (typeof (window as any).pdfBuilderReact?.initPDFBuilderReact === 'function') {
      (window as any).pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');
    }
  } else {
    console.log('[REACT INDEX] Skipping auto-init: readyState=' + document.readyState);
  }
})();

// Default export for testing/bundling
export { initPDFBuilderReact, PDFBuilder, api };
export default api;


