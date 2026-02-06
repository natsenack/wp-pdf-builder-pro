/**
 * PDF Builder Pro - React Application Entry Point
 * Main entry point for the React-based PDF editor
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder';
import '../../css/main.css';

// Debug logging
console.log('[PDF Builder] ===== REACT APP INITIALIZING =====');
console.log('[PDF Builder] React version:', React.version);
console.log('[PDF Builder] Timestamp:', new Date().toISOString());
console.log('[PDF Builder] Window available:', typeof window !== 'undefined');
console.log('[PDF Builder] Document available:', typeof document !== 'undefined');

// API for WordPress integration
const pdfBuilderReactAPI = {
  initPDFBuilderReact: function(containerId: string = 'pdf-builder-react-root') {
    console.log('[PDF Builder] initPDFBuilderReact called with container:', containerId);

    try {
      const container = document.getElementById(containerId);
      if (!container) {
        console.error('[PDF Builder] Container not found:', containerId);
        return false;
      }

      console.log('[PDF Builder] Container found, creating React root...');

      // Create React root if it doesn't exist
      if (!container._reactRoot) {
        container._reactRoot = createRoot(container);
        console.log('[PDF Builder] React root created');
      }

      // Render the PDF Builder component
      console.log('[PDF Builder] Rendering PDFBuilder component...');
      container._reactRoot.render(
        React.createElement(PDFBuilder, {
          containerId,
          timestamp: Date.now()
        })
      );

      console.log('[PDF Builder] ✅ PDF Builder React app initialized successfully');
      return true;

    } catch (error) {
      console.error('[PDF Builder] ❌ Failed to initialize React app:', error);
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
      console.error('[PDF Builder] Error destroying React app:', error);
      return false;
    }
  }
};

// Make API globally available
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = pdfBuilderReactAPI;
  console.log('[PDF Builder] window.pdfBuilderReact API assigned');
} else {
  console.error('[PDF Builder] Window not available - cannot assign global API');
}

console.log('[PDF Builder] ===== REACT APP INITIALIZATION COMPLETE =====');

// Export for potential module usage
export default pdfBuilderReactAPI;
