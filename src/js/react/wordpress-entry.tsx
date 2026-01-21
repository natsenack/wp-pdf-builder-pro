// ABSOLUTE START - TRY CATCH WRAPPING ENTIRE MODULE



// IMMEDIATE API CREATION - Create API before any imports to handle extension conflicts
// This ensures window.pdfBuilderReact exists even if imports fail
window.pdfBuilderReact = {
  initPDFBuilderReact: function(containerId) {
    
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



// DEBUG: Log that the script is loading
// console.log('🚀 [WORDPRESS-ENTRY] Script loading started - before try block');

// Now try the full React implementation
try {
import { debugError, debugWarn, debugLog } from './utils/debug';



import {
  registerEditorInstance,
  loadTemplate,
  getEditorState,
  setEditorState,
  getCurrentTemplate,
  exportTemplate,
  saveTemplate,
  resetAPI,
  updateCanvasDimensions,
  updateRotationSettings
} from './api/global-api';




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


// DEBUG HELPER FUNCTION - AFTER IMPORTS (Use the global console)
const addDebugToDOM = (msg: string) => {
  logToDebugConsole(msg);
  
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
  // console.log('🚀 [WORDPRESS-ENTRY] initPDFBuilderReact FUNCTION CALLED - STARTING INITIALIZATION');
  addDebugToDOM('💥 initPDFBuilderReact STARTED at ' + new Date().toISOString());

  try {
    // Step 1: Check container
    const container = document.getElementById('pdf-builder-react-root');
    
    addDebugToDOM('🔍 Container found: ' + !!container);

    if (!container) {
      
      
      addDebugToDOM('❌ RETURNING FALSE: No container');
      return false;
    }

    // Step 2: Check if already initialized
    const isInitialized = container.hasAttribute('data-react-initialized');
    
    addDebugToDOM('🔍 Already initialized: ' + isInitialized);

    if (isInitialized) {
      
      addDebugToDOM('✅ Already initialized, returning true');
      return true;
    }

    // Step 3: Mark as initialized
    container.setAttribute('data-react-initialized', 'true');
    
    addDebugToDOM('✅ Marked as initialized');

    // Step 4: Show editor, hide loading
    const loadingEl = document.getElementById('pdf-builder-loader');
    const editorEl = document.getElementById('pdf-builder-editor-container');
    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';
    
    addDebugToDOM('🔄 UI updated');

    // Step 5: Initialize React
    
    addDebugToDOM('⚛️ Checking React');
    
    addDebugToDOM('⚛️ typeof React: ' + typeof React);
    
    addDebugToDOM('⚛️ typeof createRoot: ' + typeof createRoot);

    if (typeof React === 'undefined') {
      
      
      addDebugToDOM('❌ RETURNING FALSE: React undefined');
      return false;
    }

    if (typeof createRoot === 'undefined') {
      
      
      addDebugToDOM('❌ RETURNING FALSE: createRoot undefined');
      return false;
    }

    
    addDebugToDOM('✅ React ready, creating root');
    let root;
    try {
      root = createRoot(container);
      
      addDebugToDOM('✅ Root created');
    } catch (rootError) {
      const rootErr = rootError instanceof Error ? rootError : new Error(String(rootError));
      
      
      
      
      addDebugToDOM('❌ createRoot failed: ' + rootErr.message);
      container.removeAttribute('data-react-initialized');
      return false;
    }

    
    
    
    addDebugToDOM('🎨 Rendering PDFBuilder');

    // Try to render with error boundary
    try {
      
      root.render(<PDFBuilder />);
      
      addDebugToDOM('✅ PDFBuilder rendered');
    } catch (renderError) {
      const error = renderError instanceof Error ? renderError : new Error(String(renderError));
      
      
      
      
      addDebugToDOM('❌ Render error: ' + error.message);

      // Try to render a simple fallback component
      try {
        
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
        
        addDebugToDOM('✅ Fallback render successful');
        return true; // Return true since we rendered something
      } catch (fallbackError) {
        const fallbackErr = fallbackError instanceof Error ? fallbackError : new Error(String(fallbackError));
        
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
      
      addDebugToDOM('📄 Loading existing template');
      setTimeout(() => {
        try {
          loadTemplate(existingTemplate);
          
          addDebugToDOM('✅ Template loaded');
        } catch (templateError) {
          
          addDebugToDOM('❌ Template load error');
        }
      }, 100);
    } else {
      
      addDebugToDOM('📄 No existing template');
    }

    
    addDebugToDOM('🎉 SUCCESS: completed');
    return true;

  } catch (error) {
    const err = error instanceof Error ? error : new Error(String(error));
    
    
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
  updateCanvasDimensions,
  updateRotationSettings,
  _isWebpackBundle: true
};

} catch (moduleError) {
  // CATCH EXTENSION ERROR - Even if something breaks, don't replace working API
  
  
  

  // Only create stub API if the full API wasn't successfully created
  if (!window.pdfBuilderReact || window.pdfBuilderReact._isFallbackMode) {
    // Create minimal API stub so wrapper doesn't hang
    window.initPDFBuilderReact = function() {
      
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

    
  } else {
    
  }
}
// END OUTER TRY-CATCH


