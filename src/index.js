import React from 'react';
import { createElement } from 'react';
import { render, unmountComponentAtNode } from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';
import './styles/editor.css';

// Forcer l'inclusion de tous les hooks personnalisés
import /* webpackMode: "eager" */ * as hooks from './hooks';

try {
    // Classe principale pour l'éditeur PDF
    class PDFBuilderPro {
        constructor() {
            this.version = '2.0.0';
            this.editors = new Map();

            // Forcer l'inclusion des hooks (ne pas supprimer cette ligne)
            this._hooks = hooks;

            // Références explicites pour forcer l'inclusion
    this._forceInclude = {
      useHistory: hooks.useHistory,
      useRotation: hooks.useRotation,
      useResize: hooks.useResize
    };
    
    // Forcer l'appel des hooks pour éviter le tree shaking
    try {
      const dummyHistory = hooks.useHistory();
      const dummyRotation = hooks.useRotation(() => {});
      const dummyResize = hooks.useResize();
      this._dummyInstances = { dummyHistory, dummyRotation, dummyResize };
    } catch (e) {
      // Ignorer les erreurs en mode SSR
    }
  }

  // Initialiser l'éditeur dans un conteneur
  init(containerId, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) {
      return;
    }

    // Options par défaut
    const defaultOptions = {
      templateId: null,
      templateName: null,
      isNew: true,
      initialElements: [],
      width: 595, // A4 width in points
      height: 842, // A4 height in points
      zoom: 1,
      gridSize: 10,
      snapToGrid: true,
      ...options
    };

    // Créer l'éditeur React
    const editorElement = createElement(PDFCanvasEditor, {
      options: defaultOptions
    });

    render(editorElement, container);
    this.editors.set(containerId, { container, options: defaultOptions });
  }

  // Détruire un éditeur
  destroy(containerId) {
    const editor = this.editors.get(containerId);
    if (editor) {
      unmountComponentAtNode(editor.container);
      this.editors.delete(containerId);
    }
  }

  // Obtenir les données d'un éditeur
  getData(containerId) {
    // Cette méthode pourrait être étendue pour récupérer l'état actuel
    return null;
  }
}

// Instance globale
const pdfBuilderPro = new PDFBuilderPro();

// Attacher à window pour WordPress - avec vérification
if (typeof window !== 'undefined') {
  window.PDFBuilderPro = pdfBuilderPro;
  // Alias pour compatibilité
  window.pdfBuilderPro = pdfBuilderPro;
}

} catch (error) {
    // Tenter de définir quand même une version basique
    window.PDFBuilderPro = {
        init: function() {
            return null;
        },
        version: 'error'
    };
    window.pdfBuilderPro = window.PDFBuilderPro;
}

// Export pour les modules ES6
// export default React;

