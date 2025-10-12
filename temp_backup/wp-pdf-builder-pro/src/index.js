import React from 'react';
import ReactDOM from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';
import './styles/editor.css';

// Classe principale pour l'éditeur PDF
class PDFBuilderPro {
  constructor() {
    this.version = '2.0.0';
    this.editors = new Map();
  }

  // Initialiser l'éditeur dans un conteneur
  init(containerId, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) {
      console.error('Container not found:', containerId);
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

    // DIAGNOSTIC: Nouvelle approche - Chargement direct des éléments
    console.log('🆕 PDF Builder Pro - NOUVELLE APPROCHE: Chargement direct des éléments depuis PHP');
    console.log('📊 PDF Builder Pro - Éléments initiaux reçus:', defaultOptions.initialElements?.length || 0, 'éléments');
    console.log('🆔 PDF Builder Pro - Template ID:', defaultOptions.templateId);
    console.log('📝 PDF Builder Pro - Template Name:', defaultOptions.templateName);
    console.log('🔄 PDF Builder Pro - isNew:', defaultOptions.isNew);

    console.log('PDF Builder Pro - Initialisation avec options:', defaultOptions);

    // Créer l'éditeur React
    const editorElement = React.createElement(PDFCanvasEditor, {
      options: defaultOptions
    });

    ReactDOM.render(editorElement, container);
    this.editors.set(containerId, { container, options: defaultOptions });
  }

  // Détruire un éditeur
  destroy(containerId) {
    const editor = this.editors.get(containerId);
    if (editor) {
      ReactDOM.unmountComponentAtNode(editor.container);
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

// Export pour les modules ES6
export default pdfBuilderPro;