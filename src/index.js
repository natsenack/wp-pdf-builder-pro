import React from 'react';
import ReactDOM from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';
import './styles/editor.css';

// Forcer l'inclusion avec des imports dynamiques
const forceIncludeModules = async () => {
  try {
    // Importer dynamiquement tous les modules critiques
    const [useHistoryModule, useRotationModule, useResizeModule, CanvasElementModule] = await Promise.all([
      import(/* webpackMode: "eager" */ './hooks/useHistory'),
      import(/* webpackMode: "eager" */ './hooks/useRotation'),
      import(/* webpackMode: "eager" */ './hooks/useResize'),
      import(/* webpackMode: "eager" */ './components/CanvasElement')
    ]);

    // Stocker les références dans window pour forcer l'inclusion
    window._forceInclude = {
      useHistory: useHistoryModule.useHistory,
      useRotation: useRotationModule.useRotation,
      useResize: useResizeModule.useResize,
      CanvasElement: CanvasElementModule.CanvasElement
    };

    console.log('Dynamic imports loaded:', Object.keys(window._forceInclude));
  } catch (error) {
    console.error('Error loading dynamic imports:', error);
  }
};

// Exécuter immédiatement pour forcer l'inclusion
forceIncludeModules();

// Forcer l'inclusion de tous les hooks personnalisés avec webpackMode eager
import /* webpackMode: "eager" */ { useHistory } from './hooks/useHistory';
import /* webpackMode: "eager" */ { useRotation } from './hooks/useRotation';
import /* webpackMode: "eager" */ { useResize } from './hooks/useResize';

// Forcer l'inclusion des composants principaux
import { CanvasElement } from './components/CanvasElement';

// Composant wrapper pour forcer l'inclusion de tous les hooks et composants
const ForceIncludeWrapper = () => {
  // Forcer l'utilisation de tous les hooks
  const history = useHistory();
  const rotation = useRotation(() => {
    console.log('Rotation callback triggered');
  });
  const resize = useResize();

  // Forcer l'utilisation des valeurs des hooks
  console.log('Force include hooks:', history, rotation, resize);

  // Forcer l'utilisation du composant CanvasElement
  const dummyElement = {
    id: 'force-include-element',
    type: 'rectangle',
    x: 0,
    y: 0,
    width: 100,
    height: 100,
    backgroundColor: 'transparent',
    borderColor: 'transparent',
    borderWidth: 0,
    rotation: 0
  };

  // Simuler un événement de rotation pour déclencher useRotation
  const mockEvent = {
    preventDefault: () => {},
    stopPropagation: () => {},
    clientX: 50,
    clientY: 50
  };

  return React.createElement('div', {
    style: { position: 'absolute', left: '-9999px', top: '-9999px', visibility: 'hidden' },
    'data-force-include': 'true'
  },
    // Forcer le rendu de CanvasElement avec des props qui déclenchent les hooks
    React.createElement(CanvasElement, {
      element: dummyElement,
      isSelected: true, // Forcer la sélection pour déclencher plus de logique
      zoom: 1,
      onSelect: () => {},
      onUpdate: () => {},
      onRemove: () => {},
      onContextMenu: (e) => {
        // Simuler un événement contextuel qui pourrait déclencher useRotation
        rotation.handleRotationStart(mockEvent);
      },
      dragAndDrop: {
        isDragging: false,
        dragOffset: { x: 0, y: 0 },
        handleMouseDown: () => {},
        handleDragStart: () => {},
        handleDragOver: () => {},
        handleDrop: () => {}
      }
    })
  );
};

// Classe principale pour l'éditeur PDF
class PDFBuilderPro {
  constructor() {
    this.version = '2.0.0';
    this.editors = new Map();

    // Références explicites pour forcer l'inclusion
    this._forceInclude = {
      useHistory: useHistory,
      useRotation: useRotation,
      useResize: useResize,
      CanvasElement: CanvasElement,
      ForceIncludeWrapper: ForceIncludeWrapper,
      PDFCanvasEditor: PDFCanvasEditor
    };

    // Forcer l'inclusion via window pour être absolument sûr
    if (typeof window !== 'undefined') {
      window._forceIncludeHooks = {
        useHistory,
        useRotation,
        useResize
      };
      window._forceIncludeComponents = {
        CanvasElement,
        PDFCanvasEditor,
        ForceIncludeWrapper
      };
    }

    console.log('Force include components loaded:', Object.keys(this._forceInclude));
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

    // Créer un conteneur pour l'éditeur et le wrapper de force inclusion
    const editorContainer = React.createElement('div', { className: 'pdf-builder-container' },
      // Composant wrapper invisible qui force l'inclusion
      React.createElement(ForceIncludeWrapper),
      // Éditeur principal
      React.createElement(PDFCanvasEditor, {
        options: defaultOptions
      })
    );

    ReactDOM.render(editorContainer, container);
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
export { CanvasElement };

// Forcer l'inclusion explicite de PDFCanvasEditor pour éviter le tree shaking
if (typeof window !== 'undefined') {
  // Référence explicite pour forcer webpack à inclure PDFCanvasEditor
  window._forceIncludePDFCanvasEditor = PDFCanvasEditor;
  // Références supplémentaires pour forcer l'inclusion de tous les composants et hooks
  window._forceIncludeCanvasElement = CanvasElement;
  window._forceIncludeForceIncludeWrapper = ForceIncludeWrapper;
  window._forceIncludeUseHistory = useHistory;
  window._forceIncludeUseRotation = useRotation;
  window._forceIncludeUseResize = useResize;
}