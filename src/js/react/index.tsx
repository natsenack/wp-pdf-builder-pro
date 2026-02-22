/**
 * PDF Builder Pro - React Application Entry Point
 * Main entry point for the React-based PDF editor
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder';
import '../../css/main.css';



// API for WordPress integration
const pdfBuilderReactAPI = {
  initPDFBuilderReact: function(containerId: string = 'pdf-builder-react-root') {

    try {
      const container = document.getElementById(containerId);
      if (!container) {
        return false;
      }

      // Create React root if it doesn't exist
      if (!container._reactRoot) {
        container._reactRoot = createRoot(container);
      }

      // Render the PDF Builder component
      container._reactRoot.render(
        React.createElement(PDFBuilder, {
          containerId,
          timestamp: Date.now()
        })
      );

      return true;

    } catch (error) {
      return false;
    }
  },

  version: '2.0.0',
  isInitialized: false,

  // Utility methods
  getContainer: function(containerId: string = 'pdf-builder-react-root') {
    return document.getElementById(containerId);
  },

  destroy: function(containerId: string = 'pdf-builder-react-root') {
    try {
      const container = document.getElementById(containerId);
      if (container && container._reactRoot) {
        container._reactRoot.unmount();
        container._reactRoot = null;
        console.log('[PDF Builder] React app destroyed');
        return true;
      }
      return false;
    } catch (error) {
      return false;
    }
  }
};

// Make API globally available
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = pdfBuilderReactAPI;
}

// Export for potential module usage
export default pdfBuilderReactAPI;
