/**
 * PDF Builder React Wrapper V2
 * Handles loading and initialization of React bundle in WordPress
 */

(function() {
  'use strict';

  console.log('[PDF Builder V2 Wrapper] Starting initialization...');

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
        console.log(`[PDF Builder V2 Wrapper] Script loaded: ${src}`);
        resolve(script);
      };
      
      script.onerror = () => {
        console.error(`[PDF Builder V2 Wrapper] Failed to load script: ${src}`);
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
        console.log(`[PDF Builder V2 Wrapper] Stylesheet loaded: ${href}`);
        resolve(link);
      };
      
      link.onerror = () => {
        console.error(`[PDF Builder V2 Wrapper] Failed to load stylesheet: ${href}`);
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
          console.log('[PDF Builder V2 Wrapper] ✅ Module loaded successfully');
          resolve(window.pdfBuilderReact);
          return;
        }
        
        if (Date.now() - startTime > timeout) {
          console.error(`[PDF Builder V2 Wrapper] ❌ Module loading timeout after ${timeout}ms`);
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
      console.log('[PDF Builder V2 Wrapper] Loading resources...');
      
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
      console.log('[PDF Builder V2 Wrapper] Vendors loaded');
      
      // Load main bundle
      await loadScript(CONFIG.bundleUrl);
      console.log('[PDF Builder V2 Wrapper] Bundle loaded');
      
      // Wait for module initialization
      const module = await waitForModule();
      console.log('[PDF Builder V2 Wrapper] Module available');
      
      // Initialize React app
      const container = document.getElementById(CONFIG.containerId);
      if (container) {
        console.log(`[PDF Builder V2 Wrapper] Initializing in container: ${CONFIG.containerId}`);
        const success = module.initPDFBuilderReact(CONFIG.containerId);
        
        if (success) {
          console.log('[PDF Builder V2 Wrapper] ✅ PDF Builder initialized successfully');
        } else {
          console.error('[PDF Builder V2 Wrapper] ❌ Initialization returned false');
        }
      } else {
        console.error(`[PDF Builder V2 Wrapper] ❌ Container not found: ${CONFIG.containerId}`);
      }
      
    } catch (error) {
      console.error('[PDF Builder V2 Wrapper] ❌ Initialization error:', error);
    }
  }

  // Wait for DOM ready and initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePDFBuilder);
  } else {
    initializePDFBuilder();
  }
})();
