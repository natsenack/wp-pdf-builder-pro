/**
 * Main entry point for PDF Builder React bundle V2
 *
 * This is the REAL PDF Builder with:
 * - Header toolbar with all controls
 * - Sidebar with element library
 * - Canvas editor with full functionality
 * - Properties panel for element editing
 */

console.log('[INDEX TOP LEVEL] ===== BUNDLE EXECUTION START =====');
console.log('[INDEX TOP LEVEL] Bundle file loaded and executing at:', new Date().toISOString());
console.log('[INDEX TOP LEVEL] Current window object:', typeof window);
console.log('[INDEX TOP LEVEL] React available:', typeof React);
console.log('[INDEX TOP LEVEL] ReactDOM available:', typeof (window as any).ReactDOM);

import React from 'react';
import { PDFBuilder } from './PDFBuilder';
import createLogger from '@utils/logger';
import { getDOMContainer } from '@utils/dom';
import '../../css/main.css';

console.log('[INDEX TOP LEVEL] Imports complete, initializing API');

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
  console.log('[INDEX TOP LEVEL] Using react-dom/client');
  try {
    const { createRoot: createRootFn } = require('react-dom/client');
    createRoot = createRootFn;
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
// API OBJECT - EXPORT AS DEFAULT
// ============================================================================

const api = {
  initPDFBuilderReact,
  version: '2.0.0',
  logger,
  PDFBuilder,
};

console.log('[INDEX TOP LEVEL] ========== EXPORTING API ==========');
console.log('[INDEX TOP LEVEL] API object:', api);

// Make API globally available for WordPress integration
(window as any).pdfBuilderReact = api;
console.log('[INDEX TOP LEVEL] window.pdfBuilderReact assigned:', (window as any).pdfBuilderReact);

export default api;
