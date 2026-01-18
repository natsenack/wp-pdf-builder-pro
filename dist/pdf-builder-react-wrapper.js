/**
 * PDF Builder React Wrapper V2
 * Handles loading and initialization of React bundle in WordPress
 */

(function() {
  'use strict';

  

  // Configuration
  const CONFIG = {
    bundleUrl: '/wp-content/plugins/wp-pdf-builder-pro/assets/js/pdf-builder-react.min.js',
    styleUrl: '/wp-content/plugins/wp-pdf-builder-pro/assets/css/pdf-builder-react.min.css',
    vendorsUrl: '/wp-content/plugins/wp-pdf-builder-pro/assets/js/vendors.min.js',
    containerId: 'pdf-builder-react-root',
    timeout: 5000,
  };

  /**
   * Load a script dynamically
   */
  function loadScript(src, attributes = {}) {
    return new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = src;
      script.type = 'text/javascript';
      
      Object.assign(script, attributes);
      
      script.onload = () => {
        
        resolve(script);
      };
      
      script.onerror = () => {
        
        reject(new Error(`Failed to load: ${src}`));
      };
      
      document.head.appendChild(script);
    });
  }

  /**
   * Load a stylesheet dynamically
   */
  function loadStylesheet(href) {
    return new Promise((resolve, reject) => {
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = href;
      
      link.onload = () => {
        
        resolve(link);
      };
      
      link.onerror = () => {
        
        reject(new Error(`Failed to load: ${href}`));
      };
      
      document.head.appendChild(link);
    });
  }

  /**
   * Wait for React module to be available
   */
  function waitForModule(timeout = CONFIG.timeout) {
    return new Promise((resolve, reject) => {
      const startTime = Date.now();
      
      const checkModule = () => {
        if (typeof window.pdfBuilderReact !== 'undefined' && window.pdfBuilderReact.initPDFBuilderReact) {
          
          resolve(window.pdfBuilderReact);
          return;
        }
        
        if (Date.now() - startTime > timeout) {
          
          reject(new Error('Module loading timeout'));
          return;
        }
        
        setTimeout(checkModule, 100);
      };
      
      checkModule();
    });
  }

  /**
   * Initialize the PDF Builder React application
   */
  async function initializePDFBuilder() {
    try {
      
      
      // Wait for DOM ready
      if (document.readyState === 'loading') {
        await new Promise((resolve) => {
          document.addEventListener('DOMContentLoaded', resolve, { once: true });
        });
      }
      
      // Load stylesheet
      await loadStylesheet(CONFIG.styleUrl);
      
      // Load vendors (React, ReactDOM, etc.)
      await loadScript(CONFIG.vendorsUrl);
      
      
      // Load main bundle
      await loadScript(CONFIG.bundleUrl);
      
      
      // Wait for module initialization
      const module = await waitForModule();
      
      
      // Initialize React app
      const container = document.getElementById(CONFIG.containerId);
      if (container) {
        
        const success = module.initPDFBuilderReact(CONFIG.containerId);
        
        if (success) {
          
        } else {
          
        }
      } else {
        
      }
      
    } catch (error) {
      
    }
  }

  // Wait for DOM ready and initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePDFBuilder);
  } else {
    initializePDFBuilder();
  }
})();

