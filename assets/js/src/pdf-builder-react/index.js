// Import des composants React
import React, { useState } from 'react';
import { Header } from './components/header/Header';
import './components/header/Header.css';

// État de l'application
let currentTemplate = null;
let isModified = false;

function initPDFBuilderReact() {
  console.log('PDF Builder React initialized successfully!');

  // Vérifier si React est disponible
  if (typeof window.React === 'undefined') {
    console.error('React is not loaded');
    return false;
  }

  if (typeof window.ReactDOM === 'undefined') {
    console.error('ReactDOM is not loaded');
    return false;
  }

  // Vérifier si le container existe
  const container = document.getElementById('pdf-builder-react-root');
  if (!container) {
    console.error('Container #pdf-builder-react-root not found');
    return false;
  }

  console.log('All dependencies loaded, initializing React...');

  // Masquer le loading et afficher l'éditeur
  const loadingEl = document.getElementById('pdf-builder-react-loading');
  const editorEl = document.getElementById('pdf-builder-react-editor');

  if (loadingEl) loadingEl.style.display = 'none';
  if (editorEl) editorEl.style.display = 'block';

  // Composant principal de l'éditeur React
  function PDFBuilderApp() {
    const [templateName, setTemplateName] = useState(currentTemplate?.name || '');
    const [modified, setModified] = useState(isModified);
    const [isSaving, setIsSaving] = useState(false);

    const handleSave = () => {
      setIsSaving(true);
      // Simulation de sauvegarde
      setTimeout(() => {
        setIsSaving(false);
        setModified(false);
        isModified = false;
        if (!currentTemplate) {
          currentTemplate = { name: templateName || 'Template sans nom' };
        }
        alert('Template sauvegardé avec succès !');
      }, 1000);
    };

    const handlePreview = () => {
      alert('Fonctionnalité d\'aperçu à venir...');
    };

    const handleNewTemplate = () => {
      if (modified && !confirm('Vous avez des modifications non sauvegardées. Voulez-vous vraiment créer un nouveau template ?')) {
        return;
      }
      currentTemplate = null;
      isModified = false;
      setTemplateName('');
      setModified(false);
      alert('Nouveau template créé !');
    };

    const isNew = !currentTemplate;

    return window.React.createElement('div', { className: 'pdf-builder-app' },
      window.React.createElement(Header, {
        templateName: templateName,
        isNewTemplate: isNew,
        isModified: modified,
        onSave: handleSave,
        onPreview: handlePreview,
        onNewTemplate: handleNewTemplate,
        isSaving: isSaving
      }),
      window.React.createElement('div', {
        className: 'pdf-builder-workspace',
        style: {
          flex: 1,
          padding: '20px',
          backgroundColor: '#f8f9fa',
          minHeight: 'calc(100vh - 80px)'
        }
      },
        window.React.createElement('div', {
          style: {
            textAlign: 'center',
            padding: '40px',
            backgroundColor: '#fff',
            border: '2px dashed #007cba',
            borderRadius: '8px'
          }
        },
          window.React.createElement('div', { style: { fontSize: '48px', marginBottom: '20px' } }, '🎨'),
          window.React.createElement('h3', { style: { color: '#007cba', marginBottom: '10px' } }, 'Zone de travail'),
          window.React.createElement('p', { style: { color: '#666' } }, 'L\'éditeur complet sera bientôt disponible ici...')
        )
      )
    );
  }

  // Créer et rendre l'application React
  try {
    const root = window.ReactDOM.createRoot(container);
    root.render(window.React.createElement(PDFBuilderApp));
    console.log('React component rendered successfully');

  } catch (error) {
    console.error('Error rendering React component:', error);
    container.innerHTML = '<p>Erreur lors du rendu React: ' + error.message + '</p>';
    return false;
  }

  return true;
}

// Export temporaire pour test
const testExport = 'Hello from React Builder';

// Export default pour webpack
export default {
  initPDFBuilderReact,
  testExport
};