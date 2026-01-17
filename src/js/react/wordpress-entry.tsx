// ABSOLUTE START - TRY CATCH WRAPPING ENTIRE MODULE
console.log('⚛️⚛️⚛️ REACT_FILE_LOADED_V7: wordpress-entry.tsx STARTED EXECUTING');
console.error('🚨🚨🚨 CRITICAL: React script execution started');

// IMMEDIATE API CREATION - Create API before any imports to handle extension conflicts
// This ensures window.pdfBuilderReact exists even if imports fail
window.pdfBuilderReact = {
  initPDFBuilderReact: function(containerId) {
    console.log('📦 Fallback initPDFBuilderReact called for container:', containerId);
    const container = document.getElementById(containerId);
    if (container) {
      container.innerHTML = `
        <div style="padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; border-radius: 5px;">
          <h3 style="margin: 0 0 10px 0;">⚠️ Mode de compatibilité activé</h3>
          <p style="margin: 0; font-size: 14px;">
            L'éditeur React n'a pas pu se charger complètement. Cela peut être dû à une extension de navigateur.
            <br><br>
            <strong>Solutions :</strong>
            <br>• Désactivez temporairement les extensions Chrome
            <br>• Utilisez un navigateur sans extensions
            <br>• Actualisez la page (F5)
          </p>
        </div>
      `;
      return true;
    }
    return false;
  },
  _isFallbackMode: true,
  _error: 'Extension conflict detected'
};

// Also create the direct function
window.initPDFBuilderReact = window.pdfBuilderReact.initPDFBuilderReact;

console.log('✅ Fallback API created immediately');

// Now try the full React implementation
try {
import { debugError, debugWarn, debugLog } from './utils/debug';
console.log('✅ Debug utilities imported successfully');

console.log('📦 About to import API functions...');
import {
  registerEditorInstance,
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  resetAPI
} from './api/global-api';
console.log('✅ API functions imported successfully');

console.log('✅✅✅ ALL IMPORTS COMPLETED SUCCESSFULLY ✅✅✅');

// Set window flags to indicate module is loaded
(window as any)['REACT_SCRIPT_LOADED'] = true;
(window as any)['REACT_LOAD_TIME'] = new Date().toISOString();

// ESSENTIAL: Create a debug log container for ALL messages
const createDebugConsole = () => {
  let debugConsole = document.getElementById('pdf-builder-debug-console');
  if (!debugConsole) {
    debugConsole = document.createElement('div');
    debugConsole.id = 'pdf-builder-debug-console';
    debugConsole.style.cssText = `
      position: fixed;
      bottom: 10px;
      left: 10px;
      background: #000;
      color: #00ff00;
      padding: 15px;
      border-radius: 5px;
      z-index: 999999;
      font-size: 11px;
      font-family: monospace;
      max-width: 500px;
      max-height: 400px;
      overflow-y: auto;
      overflow-x: hidden;
      border: 2px solid #00ff00;
      box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
      word-break: break-word;
      white-space: pre-wrap;
    `;
    document.body.appendChild(debugConsole);
  }
  return debugConsole;
};

const logToDebugConsole = (msg: string) => {
  const debugConsole = createDebugConsole();
  const timestamp = new Date().toISOString().split('T')[1].split('.')[0];
  debugConsole.innerHTML += `[${timestamp}] ${msg}\n`;
  debugConsole.scrollTop = debugConsole.scrollHeight;
};

logToDebugConsole('✅ Debug console created');
console.log('✅ Debug console functions ready');

// DEBUG HELPER FUNCTION - AFTER IMPORTS (Use the global console)
const addDebugToDOM = (msg: string) => {
  logToDebugConsole(msg);
  console.log('[PDF-BUILDER-DEBUG]', msg);
};

// Fonction d'initialisation appelée par WordPress
declare global {
  interface Window {
    pdfBuilderReactInitData: {
      nonce: string;
      ajaxUrl: string;
      strings: {
        loading: string;
        error: string;
      };
    };
    initPDFBuilderReact: typeof initPDFBuilderReact;
    pdfBuilderReact: {
      initPDFBuilderReact: typeof initPDFBuilderReact;
      loadTemplate: typeof loadTemplate;
      getEditorState: typeof getEditorState;
      setEditorState: typeof setEditorState;
      getCurrentTemplate: typeof getCurrentTemplate;
      exportTemplate: typeof exportTemplate;
      saveTemplate: typeof saveTemplate;
      registerEditorInstance: typeof registerEditorInstance;
      resetAPI: typeof resetAPI;
      _isWebpackBundle: true;
    };
    // Notification functions
    showSuccessNotification?: (message: string, duration?: number) => void;
    showErrorNotification?: (message: string, duration?: number) => void;
    showWarningNotification?: (message: string, duration?: number) => void;
    showInfoNotification?: (message: string, duration?: number) => void;
  }
}

export function initPDFBuilderReact() {
  // LOG CRITIQUE - DÉBUT
  console.log('💥 NUCLEAR_DEBUG_V1: initPDFBuilderReact STARTED');
  addDebugToDOM('💥 initPDFBuilderReact STARTED at ' + new Date().toISOString());

  try {
    // Step 1: Check container
    const container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 Container found:', !!container);
    addDebugToDOM('🔍 Container found: ' + !!container);

    if (!container) {
      console.error('❌ FAIL: Container element not found');
      console.error('❌ RETURNING FALSE: No container');
      addDebugToDOM('❌ RETURNING FALSE: No container');
      return false;
    }

    // Step 2: Check if already initialized
    const isInitialized = container.hasAttribute('data-react-initialized');
    console.log('🔍 Already initialized:', isInitialized);
    addDebugToDOM('🔍 Already initialized: ' + isInitialized);

    if (isInitialized) {
      console.log('✅ SUCCESS: Already initialized');
      addDebugToDOM('✅ Already initialized, returning true');
      return true;
    }

    // Step 3: Mark as initialized
    container.setAttribute('data-react-initialized', 'true');
    console.log('✅ Container marked as initialized');
    addDebugToDOM('✅ Marked as initialized');

    // Step 4: Show editor, hide loading
    const loadingEl = document.getElementById('pdf-builder-loader');
    const editorEl = document.getElementById('pdf-builder-editor-container');
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    console.log('🔄 UI updated: loading hidden, editor shown');
    addDebugToDOM('🔄 UI updated');

    // Step 5: Initialize React
    console.log('⚛️ Checking React availability');
    addDebugToDOM('⚛️ Checking React');
    console.log('⚛️ typeof React:', typeof React);
    addDebugToDOM('⚛️ typeof React: ' + typeof React);
    console.log('⚛️ typeof createRoot:', typeof createRoot);
    addDebugToDOM('⚛️ typeof createRoot: ' + typeof createRoot);

    if (typeof React === 'undefined') {
      console.error('❌ FAIL: React not loaded');
      console.error('❌ RETURNING FALSE: React undefined');
      addDebugToDOM('❌ RETURNING FALSE: React undefined');
      return false;
    }

    if (typeof createRoot === 'undefined') {
      console.error('❌ FAIL: createRoot not available');
      console.error('❌ RETURNING FALSE: createRoot undefined');
      addDebugToDOM('❌ RETURNING FALSE: createRoot undefined');
      return false;
    }

    console.log('✅ React ready, creating root');
    addDebugToDOM('✅ React ready, creating root');
    let root;
    try {
      root = createRoot(container);
      console.log('✅ Root created successfully');
      addDebugToDOM('✅ Root created');
    } catch (rootError) {
      const rootErr = rootError instanceof Error ? rootError : new Error(String(rootError));
      console.error('❌ FAIL: createRoot error:', rootErr);
      console.error('❌ FAIL: createRoot error message:', rootErr.message);
      console.error('❌ FAIL: createRoot error stack:', rootErr.stack);
      console.error('❌ RETURNING FALSE: createRoot failed');
      addDebugToDOM('❌ createRoot failed: ' + rootErr.message);
      container.removeAttribute('data-react-initialized');
      return false;
    }

    console.log('🎨 Rendering PDFBuilder component');
    console.log('🎨 PDFBuilder component available:', typeof PDFBuilder);
    console.log('🎨 PDFBuilder import successful');
    addDebugToDOM('🎨 Rendering PDFBuilder');

    // Try to render with error boundary
    try {
      console.log('🎨 Attempting to render PDFBuilder...');
      root.render(<PDFBuilder />);
      console.log('✅ PDFBuilder rendered successfully');
      addDebugToDOM('✅ PDFBuilder rendered');
    } catch (renderError) {
      const error = renderError instanceof Error ? renderError : new Error(String(renderError));
      console.error('❌ FAIL: PDFBuilder render error:', error);
      console.error('❌ FAIL: Render error stack:', error.stack);
      console.error('❌ FAIL: Render error message:', error.message);
      console.error('❌ FAIL: Render error name:', error.name);
      addDebugToDOM('❌ Render error: ' + error.message);

      // Try to render a simple fallback component
      try {
        console.log('🔄 Trying fallback render...');
        addDebugToDOM('🔄 Trying fallback render');
        root.render(
          <div style={{ padding: '20px', background: '#ffebee', border: '1px solid #f44336', borderRadius: '4px', color: '#c62828' }}>
            <h3>Erreur de rendu React</h3>
            <p>Le composant PDFBuilder n'a pas pu être rendu. Erreur: {error.message}</p>
            <details>
              <summary>Détails de l'erreur</summary>
              <pre>{error.stack}</pre>
            </details>
          </div>
        );
        console.log('✅ Fallback render successful');
        addDebugToDOM('✅ Fallback render successful');
        return true; // Return true since we rendered something
      } catch (fallbackError) {
        const fallbackErr = fallbackError instanceof Error ? fallbackError : new Error(String(fallbackError));
        console.error('❌ FAIL: Fallback render also failed:', fallbackErr);
        addDebugToDOM('❌ Fallback also failed: ' + fallbackErr.message);
        container.removeAttribute('data-react-initialized');
        return false;
      }
    }

    // Charger les données initiales du template s'il y en a
    // Step 6: Load template data if available
    const dataWindow = window as unknown as { pdfBuilderData?: { existingTemplate?: unknown } };
    const existingTemplate = dataWindow.pdfBuilderData?.existingTemplate;

    if (existingTemplate) {
      console.log('📄 Loading existing template');
      addDebugToDOM('📄 Loading existing template');
      setTimeout(() => {
        try {
          loadTemplate(existingTemplate);
          console.log('✅ Template loaded');
          addDebugToDOM('✅ Template loaded');
        } catch (templateError) {
          console.error('❌ Template load error:', templateError);
          addDebugToDOM('❌ Template load error');
        }
      }, 100);
    } else {
      console.log('📄 No existing template');
      addDebugToDOM('📄 No existing template');
    }

    console.log('🎉 SUCCESS: initPDFBuilderReact completed');
    addDebugToDOM('🎉 SUCCESS: completed');
    return true;

  } catch (error) {
    const err = error instanceof Error ? error : new Error(String(error));
    console.error('❌ FAIL: React initialization error:', err);
    console.error('❌ FAIL: Error stack:', err.stack);
    addDebugToDOM('❌ EXCEPTION: ' + err.message);

    // Try to remove initialization flag if container exists
    const container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.removeAttribute('data-react-initialized');
    }

    return false;
  }
}

// Déclarer l'interface globale pour TypeScript
// (Déjà déclarée plus haut)

// Export pour utilisation manuelle (WordPress l'appelle explicitement)
window.initPDFBuilderReact = initPDFBuilderReact;

// REPLACE the fallback API with the full API since imports succeeded
window.pdfBuilderReact = {
  initPDFBuilderReact,
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  registerEditorInstance,
  resetAPI,
  _isWebpackBundle: true
};

} catch (moduleError) {
  // CATCH EXTENSION ERROR - Even if something breaks, don't replace working API
  console.error('🔥🔥🔥 MODULE-LEVEL ERROR CAUGHT (likely extension issue):', moduleError);
  console.error('🔥 Error:', moduleError instanceof Error ? moduleError.message : String(moduleError));
  console.error('🔥 Stack:', moduleError instanceof Error ? moduleError.stack : 'No stack');

  // Only create stub API if the full API wasn't successfully created
  if (!window.pdfBuilderReact || window.pdfBuilderReact._isFallbackMode) {
    // Create minimal API stub so wrapper doesn't hang
    window.initPDFBuilderReact = function() {
      console.error('❌ initPDFBuilderReact is stub (module error)');
      const container = document.getElementById('pdf-builder-react-root');
      if (container) {
        container.innerHTML = '<div style="padding: 20px; background: #ffcccc; border: 1px solid #ff0000; color: #c62828;"><h3>Erreur: Module React n\'a pas pu charger</h3><p style="font-size: 12px;">Erreur d\'extension détectée. Consultez la console pour les détails.</p></div>';
      }
      return false;
    };

    window.pdfBuilderReact = {
      initPDFBuilderReact: window.initPDFBuilderReact,
      _isWebpackBundle: true,
      _error: moduleError,
      _errorMessage: moduleError instanceof Error ? moduleError.message : String(moduleError)
    };

    console.log('✅ Minimal API created (stub mode)');
  } else {
    console.log('✅ Full API already created, keeping it despite module error');
  }
}
// END OUTER TRY-CATCH