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
    console.log('PDF Builder Pro v' + this.version + ' - Initializing editor in:', containerId);

    const container = document.getElementById(containerId);
    if (!container) {
      console.error('Container not found:', containerId);
      return;
    }

    // Options par défaut
    const defaultOptions = {
      templateId: null,
      isNew: true,
      width: 595, // A4 width in points
      height: 842, // A4 height in points
      zoom: 1,
      gridSize: 10,
      snapToGrid: true,
      ...options
    };

    // Créer l'éditeur React
    const editorElement = React.createElement(PDFCanvasEditor, {
      options: defaultOptions,
      onSave: (data) => this.handleSave(data),
      onPreview: (data) => this.handlePreview(data)
    });

    ReactDOM.render(editorElement, container);
    this.editors.set(containerId, { container, options: defaultOptions });

    console.log('PDF Builder Pro editor initialized successfully');
  }

  // Gérer la sauvegarde
  handleSave(data) {
    console.log('Saving template:', data);
    // Ici on pourrait envoyer les données au serveur
  }

  // Gérer l'aperçu
  handlePreview(data) {
    console.log('Generating preview:', data);
    // Ici on pourrait ouvrir une fenêtre d'aperçu
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

// Attacher à window pour WordPress
if (typeof window !== 'undefined') {
  window.PDFBuilderPro = pdfBuilderPro;
}

// Export pour les modules ES6
export default pdfBuilderPro;