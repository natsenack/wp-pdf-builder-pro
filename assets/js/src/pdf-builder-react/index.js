// Import des composants React
import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';

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
    console.error('❌ React Error Boundary caught an error:', error);
    console.error('❌ Error Info:', errorInfo);
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

console.log('🚀 PDF Builder React bundle starting execution...');

function initPDFBuilderReact() {
  console.log('✅ initPDFBuilderReact function called');

  try {
    // Vérifier si le container existe
    const container = document.getElementById('pdf-builder-react-root');
    console.log('🔍 Container element:', container);
    if (!container) {
      console.error('❌ Container #pdf-builder-react-root not found');
      return false;
    }

    console.log('✅ Container found, checking dependencies...');

    // Vérifier les dépendances
    if (typeof React === 'undefined') {
      console.error('❌ React is not available');
      return false;
    }
    if (typeof ReactDOM === 'undefined') {
      console.error('❌ ReactDOM is not available');
      return false;
    }
    console.log('✅ React dependencies available');

    console.log('🎯 All dependencies loaded, initializing React...');

    // Masquer le loading et afficher l'éditeur
    const loadingEl = document.getElementById('pdf-builder-react-loading');
    const editorEl = document.getElementById('pdf-builder-react-editor');

    if (loadingEl) loadingEl.style.display = 'none';
    if (editorEl) editorEl.style.display = 'block';

    console.log('🎨 Creating React root...');

    // Créer et rendre l'application React
    const root = ReactDOM.createRoot(container);
    console.log('🎨 React root created, rendering component...');

    root.render(React.createElement(ErrorBoundary, null, 
      React.createElement(PDFBuilder, { width: DEFAULT_CANVAS_WIDTH, height: DEFAULT_CANVAS_HEIGHT })
    ));
    console.log('✅ React component rendered successfully');

    return true;

  } catch (error) {
    console.error('❌ Error in initPDFBuilderReact:', error);
    console.error('❌ Error stack:', error.stack);
    const container = document.getElementById('pdf-builder-react-root');
    if (container) {
      container.innerHTML = '<p>❌ Erreur lors du rendu React: ' + error.message + '</p><pre>' + error.stack + '</pre>';
    }
    return false;
  }
}

console.log('📦 Creating exports object...');

// Export default pour webpack
const exports = {
  initPDFBuilderReact
};

console.log('🌐 Assigning to window...');

// Assigner la fonction à window pour l'accès global depuis WordPress
if (typeof window !== 'undefined') {
  console.log('🔍 Before assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);

  // Approche ultime : assignation forcée avec surveillance agressive
  let assignmentCount = 0;
  const maxAssignments = 10;

  function forceAssign() {
    try {
      window.pdfBuilderReact = exports;
      assignmentCount++;
      console.log(`🔄 Force assignment #${assignmentCount} successful`);

      // Vérifier immédiatement si ça tient
      setTimeout(() => {
        if (typeof window.pdfBuilderReact === 'undefined') {
          console.log('⚠️ Assignment lost immediately, reassigning...');
          if (assignmentCount < maxAssignments) {
            forceAssign();
          }
        }
      }, 1);

    } catch (error) {
      console.error('❌ Force assignment failed:', error);
    }
  }

  // Assignation initiale
  forceAssign();

  // Surveillance agressive : vérifier toutes les 10ms pendant les 2 premières secondes
  let surveillanceCount = 0;
  const surveillanceInterval = setInterval(() => {
    surveillanceCount++;

    if (typeof window.pdfBuilderReact === 'undefined') {
      console.log(`🚨 pdfBuilderReact lost at check #${surveillanceCount}, reassigning...`);
      forceAssign();
    }

    // Arrêter la surveillance après 2 secondes
    if (surveillanceCount > 200) { // 200 * 10ms = 2 secondes
      clearInterval(surveillanceInterval);
      console.log('✅ Aggressive surveillance ended');
    }
  }, 10);

  // Surveillance de maintenance : vérifier toutes les 100ms indéfiniment
  setInterval(() => {
    if (typeof window.pdfBuilderReact === 'undefined') {
      console.log('🔄 Maintenance: pdfBuilderReact lost, reassigning...');
      window.pdfBuilderReact = exports;
    }
  }, 100);

  console.log('🔍 After assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  console.log('🔍 window.pdfBuilderReact object:', window.pdfBuilderReact);
  console.log('🔍 window object:', window);
  console.log('🔍 window === globalThis:', window === globalThis);

  // Vérifier immédiatement si l'assignation persiste
  setTimeout(function() {
    console.log('⏰ 100ms after assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  }, 100);

  setTimeout(function() {
    console.log('⏰ 500ms after assignment - window.pdfBuilderReact:', typeof window.pdfBuilderReact);
  }, 500);

} else {
  console.error('❌ window is not available');
}

console.log('🎉 PDF Builder React bundle execution completed');

export default exports;