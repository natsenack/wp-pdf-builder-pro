/**
 * Main entry point for PDF Builder React bundle V2
 * 
 * This is the REAL PDF Builder with:
 * - Header toolbar with all controls
 * - Sidebar with element library
 * - Canvas editor with full functionality
 * - Properties panel for element editing
 */

import React from 'react';
import { createRoot, Root } from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder';
import createLogger from '@utils/logger';
import { getDOMContainer } from '@utils/dom';
import '../../css/main.css';

const logger = createLogger('PDFBuilderReact');

// ============================================================================
// MODULE INITIALIZATION
// ============================================================================

logger.info('🚀 PDF Builder V2 module execution started');
logger.debug('React available:', typeof React);

let reactRoot: Root | null = null;

// ============================================================================
// MAIN INITIALIZATION FUNCTION
// ============================================================================

/**
 * Initialize the PDF Builder React application
 * @param containerId - HTML element id where React will mount
 * @returns true if initialization succeeded, false otherwise
 */
function initPDFBuilderReact(containerId: string = 'pdf-builder-react-root'): boolean {
  logger.info(`Initializing PDF Builder in container: ${containerId}`);
  
  try {
    // Get container
    const container = getDOMContainer(containerId);
    if (!container) {
      logger.error(`Container not found: ${containerId}`);
      return false;
    }

    // Create React root
    reactRoot = createRoot(container);
    logger.debug('React root created');

    // Render the REAL PDF Builder component
    reactRoot.render(
      <PDFBuilder 
        width={1200} 
        height={800}
        className="pdf-builder-main"
      />
    );
    logger.info('✅ PDF Builder rendered successfully');

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
if (typeof window !== 'undefined') {
  (window as any).pdfBuilderReact = api;
  (window as any).initPDFBuilderReact = initPDFBuilderReact;
  logger.info('✅ PDF Builder API exported to window');
}

// Default export for testing/bundling
export { initPDFBuilderReact, PDFBuilder, api };
export default api;


