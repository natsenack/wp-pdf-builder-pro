// Import des composants React
import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';
import { debugLog, debugError } from './utils/debug';

// Composant ErrorBoundary pour capturer les erreurs de rendu
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null, errorInfo: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    debugError('❌ React Error Boundary caught an error:', error);
    debugError('❌ Error Info:', errorInfo);
    this.setState({
      error: error,
      errorInfo: errorInfo
    });
  }

  render() {
    if (this.state.hasError) {
      return React.createElement('div', {
        style: {
          padding: '20px',
          border: '1px solid #ff6b6b',
          borderRadius: '5px',
          backgroundColor: '#ffe6e6',
          color: '#d63031',
          fontFamily: 'Arial, sans-serif'
        }
      }, 
        React.createElement('h2', null, 'Erreur dans l\'éditeur PDF'),
        React.createElement('p', null, 'Une erreur s\'est produite lors du rendu de l\'éditeur. Veuillez rafraîchir la page.'),
        React.createElement('details', { style: { whiteSpace: 'pre-wrap' } },
          React.createElement('summary', null, 'Détails de l\'erreur'),
          this.state.error && this.state.error.toString(),
          React.createElement('br'),
          this.state.errorInfo && this.state.errorInfo.componentStack
        )
      );
    }

    return this.props.children;
  }
}

// État de l'application
let currentTemplate = null;
let isModified = false;

debugLog('🚀 PDF Builder React bundle starting execution...');

function initPDFBuilderReact() {
  debugLog('✅ initPDFBuilderReact function called');

  try {
    // Vérifier si le container existe
    const container = document.getElementById('pdf-builder-react-root');
    debugLog('🔍 Container element:', container);
    if (!container) {
      debugError('❌ Container #pdf-builder-react-root not found');
      return false;
    }

    debugLog('✅ Container found, checking dependencies...');

    // Vérifier les dépendances
    if (typeof React === 'undefined') {
      debugError('❌ React is not available');
      return false;
    }
    if (typeof ReactDOM === 'undefined') {
      debugError('❌ ReactDOM is not available');
      return false;
    }
    debugLog('✅ React dependencies available');

    debugLog('🎯 All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    const loadingEl = document.getElementById('pdf-builder-react-loading');
    const editorEl = document.getElementById('pdf-builder-react-editor');

    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';

    debugLog('🎨 Creating React root...');

    // Créer et rendre l'application React
    const root = ReactDOM.createRoot(container);
    debugLog('🎨 React root created, rendering component...');

    root.render(React.createElement(ErrorBoundary, null, 
      React.createElement(PDFBuilder, { width: DEFAULT_CANVAS_WIDTH, height: DEFAULT_CANVAS_HEIGHT })
    ));
    debugLog('✅ React component rendered successfully');

    return true;

  } catch (error) {
    debugError('❌ Error in initPDFBuilderReact:', error);
    debugError('❌ Error stack:', error.stack);
    const container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + error.message + '</p><pre>' + error.stack + '</pre>';
    }
    return false;
  }
}

debugLog('📦 Creating exports object...');

// Export default pour webpack
const exports = {
  initPDFBuilderReact
};

debugLog('🌐 Assigning to window...');

// Assigner la fonction à window pour l'accès global depuis WordPress
if (typeof window !== 'undefined') {
  debugLog('🔍 Before assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);

  // Approche ultime : assignation forcée avec surveillance agressive
  let assignmentCount = 0;
  const maxAssignments = 10;

  function forceAssign() {
    try {
      // Vérifier si la propriété existe déjà
      if (Object.getOwnPropertyDescriptor(window, 'pdfBuilderReact')) {
        // Si elle existe, essayer de la redéfinir seulement si configurable
        const descriptor = Object.getOwnPropertyDescriptor(window, 'pdfBuilderReact');
        if (descriptor.configurable) {
          Object.defineProperty(window, 'pdfBuilderReact', {
            value: exports,
            writable: false,
            configurable: true, // Permettre la redéfinition
            enumerable: true
          });
        } else {
          // Si non configurable, ne rien faire
          debugLog('ℹ️ Property already defined and non-configurable, skipping redefinition');
          return;
        }
      } else {
        // Première assignation
        Object.defineProperty(window, 'pdfBuilderReact', {
          value: exports,
          writable: false,
          configurable: true, // Permettre la redéfinition future
          enumerable: true
        });
      }
      assignmentCount++;
      debugLog(`🔄 Force assignment #${assignmentCount} successful`);

      // Vérifier immédiatement si ça tient
      setTimeout(() => {
        if (typeof window.pdfBuilderReact === 'undefined') {
          debugLog('⚠️ Assignment lost immediately, reassigning...');
          if (assignmentCount < maxAssignments) {
            forceAssign();
          }
        }
      }, 1);

    } catch (error) {
      debugError('❌ Force assignment failed:', error);
    }
  }

  // Assignation initiale
  forceAssign();

  // Surveillance agressive : vérifier toutes les 10ms pendant les 2 premières secondes
  let surveillanceCount = 0;
  const surveillanceInterval = setInterval(() => {
    surveillanceCount++;

    if (typeof window.pdfBuilderReact === 'undefined') {
      debugLog(`🚨 pdfBuilderReact lost at check #${surveillanceCount}, reassigning...`);
      try {
        // Vérifier si la propriété existe déjà
        if (Object.getOwnPropertyDescriptor(window, 'pdfBuilderReact')) {
          const descriptor = Object.getOwnPropertyDescriptor(window, 'pdfBuilderReact');
          if (descriptor.configurable) {
            Object.defineProperty(window, 'pdfBuilderReact', {
              value: exports,
              writable: false,
              configurable: true,
              enumerable: true
            });
          } else {
            // Fallback direct seulement si nécessaire
            try {
              window.pdfBuilderReact = exports;
            } catch (error) {
              debugError('❌ Fallback assignment also failed:', error);
            }
          }
        } else {
          Object.defineProperty(window, 'pdfBuilderReact', {
            value: exports,
            writable: false,
            configurable: true,
            enumerable: true
          });
        }
      } catch (error) {
        debugError('❌ Surveillance reassignment failed:', error);
        // Fallback direct
        try {
          window.pdfBuilderReact = exports;
        } catch (fallbackError) {
          debugError('❌ Fallback assignment also failed:', fallbackError);
        }
      }
    }

    // Arrêter la surveillance après 2 secondes
    if (surveillanceCount > 200) { // 200 * 10ms = 2 secondes
      clearInterval(surveillanceInterval);
      debugLog('✅ Aggressive surveillance ended');
    }
  }, 10);

  // Surveillance de maintenance : vérifier toutes les 100ms indéfiniment
  setInterval(() => {
    if (typeof window.pdfBuilderReact === 'undefined') {
      debugLog('🔄 Maintenance: pdfBuilderReact lost, reassigning...');
      try {
        // Vérifier si la propriété existe déjà
        if (Object.getOwnPropertyDescriptor(window, 'pdfBuilderReact')) {
          const descriptor = Object.getOwnPropertyDescriptor(window, 'pdfBuilderReact');
          if (descriptor.configurable) {
            Object.defineProperty(window, 'pdfBuilderReact', {
              value: exports,
              writable: false,
              configurable: true,
              enumerable: true
            });
          } else {
            // Fallback direct seulement si nécessaire
            try {
              window.pdfBuilderReact = exports;
            } catch (error) {
              debugError('❌ Fallback assignment also failed:', error);
            }
          }
        } else {
          Object.defineProperty(window, 'pdfBuilderReact', {
            value: exports,
            writable: false,
            configurable: true,
            enumerable: true
          });
        }
      } catch (error) {
        debugError('❌ Maintenance reassignment failed:', error);
        // Fallback direct
        try {
          window.pdfBuilderReact = exports;
        } catch (fallbackError) {
          debugError('❌ Fallback assignment also failed:', fallbackError);
        }
      }
    }
  }, 100);

  debugLog('🔍 After assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  debugLog('🔍 window.pdfBuilderReact object:', window.pdfBuilderReact);
  debugLog('🔍 window object:', window);
  debugLog('🔍 window === globalThis:', window === globalThis);

  // Vérifier immédiatement si l'assignation persiste
  setTimeout(function() {
    debugLog('⏰ 100ms after assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  }, 100);

  setTimeout(function() {
    debugLog('⏰ 500ms after assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  }, 500);

} else {
  debugError('❌ window is not available');
}

debugLog('🎉 PDF Builder React bundle execution completed');

export default exports;