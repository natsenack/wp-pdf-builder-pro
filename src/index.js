// Tous les imports doivent être au niveau supérieur du module
import React from 'react';
import { createElement } from 'react';
import ReactDOM from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';

// Forcer l'inclusion de tous les hooks personnalisés
import /* webpackMode: "eager" */ * as hooks from './hooks';

console.log('PDF Builder Pro: Script execution started - with proper imports');

// Test des imports de base
try {
    console.log('Testing React availability...');
    console.log('React version:', React.version);
    console.log('ReactDOM available:', typeof ReactDOM);

    // Exposer React globalement pour compatibilité
    if (typeof window !== 'undefined') {
        window.React = React;
        window.ReactDOM = ReactDOM;
        console.log('PDF Builder Pro: React exposed globally');
    }
} catch (error) {
    console.error('React test failed:', error);
}

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
        console.log('PDFBuilderPro.init called with:', containerId, options);

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

        try {
            // Créer l'éditeur React
            const editorElement = createElement(PDFCanvasEditor, {
                options: defaultOptions
            });

            ReactDOM.render(editorElement, container);
            this.editors.set(containerId, { container, options: defaultOptions });

            console.log('PDFBuilderPro: Editor initialized successfully');
        } catch (error) {
            console.error('PDFBuilderPro: Failed to initialize editor:', error);
        }
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
console.log('PDF Builder Pro: PDFBuilderPro instance created');

// Attacher à window pour WordPress - avec vérification
if (typeof window !== 'undefined') {
    window.PDFBuilderPro = pdfBuilderPro;
    // Alias pour compatibilité
    window.pdfBuilderPro = pdfBuilderPro;
    console.log('PDF Builder Pro: PDFBuilderPro attached to window');
}

