/**
 * Main entry point for PDF Builder React bundle V2
 *
 * This is the REAL PDF Builder with:
 * - Header toolbar with all controls
 * - Sidebar with element library
 * - Canvas editor with full functionality
 * - Properties panel for element editing
 */

// DEBUG: Mark that module execution started
if (typeof window !== 'undefined') {
  (window as any).__pdfBuilderReactModuleLoaded = true;
  console.log('[INDEX MODULE] Module loaded and executing');
}

// Self-assign to window immediately - before anything else
(function() {
  console.log('[INDEX TOP LEVEL] IIFE execution started');
  
  // Import everything inside to ensure it's part of the IIFE closure
  const React = require('react');
  const { PDFBuilder } = require('./PDFBuilder');
  const createLogger = require('@utils/logger').default;
  const { getDOMContainer } = require('@utils/dom');
  
  require('../../css/main.css');
  
  const logger = createLogger('PDFBuilderReact');
  
  console.log('[INDEX TOP LEVEL] Dependencies loaded');
  console.log('[INDEX TOP LEVEL] React available:', typeof React);
  console.log('[INDEX TOP LEVEL] PDFBuilder available:', typeof PDFBuilder);
    
    // Support both WordPress React (no createRoot) and modern React
    let createRoot: any;
    
    if (typeof (window as any).ReactDOM !== 'undefined' && (window as any).ReactDOM.createRoot) {
      console.log('[INDEX TOP LEVEL] Using ReactDOM.createRoot');
      createRoot = (window as any).ReactDOM.createRoot;
    } else if (typeof (window as any).ReactDOM !== 'undefined') {
      // Fallback for WordPress React without createRoot
      console.log('[INDEX TOP LEVEL] Using ReactDOM.render fallback');
      createRoot = (container: any) => ({
        render: (element: any) => {
          (window as any).ReactDOM.render(element, container);
        },
        unmount: () => {
          (window as any).ReactDOM.unmountComponentAtNode(container);
        }
      });
    } else {
      console.log('[INDEX TOP LEVEL] Using require fallback');
      try {
        const RDom = require('react-dom/client');
        createRoot = RDom.createRoot;
      } catch (e) {
        console.error('[INDEX TOP LEVEL] No ReactDOM available:', e);
        createRoot = (container: any) => ({
          render: () => console.warn('[INDEX TOP LEVEL] No renderer available'),
          unmount: () => {}
        });
      }
    }
    
    // ============================================================================
    // MAIN INITIALIZATION FUNCTION
    // ============================================================================

    function initPDFBuilderReact(containerId: string = 'pdf-builder-react-root'): boolean {
      console.log('[INDEX] initPDFBuilderReact called with:', containerId);
      
      try {
        const container = getDOMContainer(containerId);
        if (!container) {
          logger.error(`Container not found: ${containerId}`);
          return false;
        }

        logger.info(`Initializing PDF Builder in container: ${containerId}`);

        try {
          const root = createRoot(container);
          const element = React.createElement(PDFBuilder);
          root.render(element);
          logger.info('✅ PDF Builder initialized successfully');
        return true;
      } catch (renderError) {
        logger.error('Failed to render PDF Builder:', renderError);
        return false;
      }
    } catch (err) {
      logger.error('Initialization failed:', err);
      return false;
    }
  }

  // ============================================================================
  // API OBJECT
  // ============================================================================
  
  const api = {
    initPDFBuilderReact,
    version: '2.0.0',
    logger,
    PDFBuilder,
  };
  
  // THE CRITICAL ASSIGNMENT - RIGHT HERE AT MODULE LEVEL
  console.log('[INDEX TOP LEVEL] ========== ASSIGNING TO WINDOW ==========');
  (window as any).pdfBuilderReact = api;
  (window as any).initPDFBuilderReact = initPDFBuilderReact;
  console.log('[INDEX TOP LEVEL] ✅ window.pdfBuilderReact assigned!');
  console.log('[INDEX TOP LEVEL] window.pdfBuilderReact type:', typeof (window as any).pdfBuilderReact);
  console.log('[INDEX TOP LEVEL] initPDFBuilderReact type:', typeof (window as any).initPDFBuilderReact);
  
  // Try auto-init
  setTimeout(() => {
    console.log('[INDEX TOP LEVEL] Auto-init check...');
    const rootElement = document.getElementById('pdf-builder-react-root');
    if (rootElement && (window as any).pdfBuilderReact?.initPDFBuilderReact) {
      console.log('[INDEX TOP LEVEL] Auto-initializing...');
      (window as any).pdfBuilderReact.initPDFBuilderReact();
    }
  }, 100);
})();

export {};
