// Tous les imports doivent être au niveau supérieur du module
import React from 'react';
import { createElement } from 'react';
import ReactDOM from 'react-dom';
// import './react-global'; // REMOVED - on expose directement ici
import { PDFCanvasEditor } from './components/PDFCanvasEditor';

// FORCER L'EXPOSITION GLOBALE DE REACT ICI
if (typeof window !== 'undefined') {
    window.React = React;
    window.ReactDOM = ReactDOM;
}

// Forcer l'inclusion de tous les hooks personnalisés
import /* webpackMode: "eager" */ * as hooks from './hooks';


// Système de protection et monitoring - SIMPLIFIÉ
const PDFBuilderSecurity = {
    errors: [],
    initialized: false,

    // Log sécurisé des erreurs
    logError(error, context = '') {
        const errorInfo = {
            message: error.message,
            stack: error.stack,
            context,
            timestamp: new Date().toISOString(),
            userAgent: navigator?.userAgent,
            url: window?.location?.href
        };

        this.errors.push(errorInfo);
    },

    // Protection contre les appels multiples - améliorée
    preventMultipleInit() {
        const now = Date.now();
        const lastInit = window._pdfBuilderLastInit || 0;
        const timeSinceLastInit = now - lastInit;

        // Si plus de 5 secondes se sont écoulées depuis la dernière initialisation,
        // permettre une réinitialisation (utile pour les rechargements de page)
        if (window._pdfBuilderInitialized && timeSinceLastInit < 5000) {
            // Log silencieux au lieu d'un avertissement intrusif
            return false;
        }

        window._pdfBuilderInitialized = true;
        window._pdfBuilderLastInit = now;
        return true;
    }
};

// Test des imports de base avec protection
try {

    // Exposer React globalement pour compatibilité - FORCER L'INCLUSION
    if (typeof window !== 'undefined') {
        window.React = React;
        window.ReactDOM = ReactDOM;
        // Forcer l'utilisation pour éviter l'optimisation webpack
        window._forceReactInclusion = { React, ReactDOM, createElement };
    }
} catch (error) {
    PDFBuilderSecurity.logError(error, 'React initialization');
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


        // Assigner explicitement les méthodes comme propriétés propres de l'instance
    }

    // Initialiser l'éditeur dans un conteneur
    init(containerId, options = {}) {

        try {
            // Vérification stricte du containerId
            if (!containerId || typeof containerId !== 'string') {
                throw new Error('ContainerId must be a non-empty string');
            }

            const container = document.getElementById(containerId);
            if (!container) {
                throw new Error(`Container with ID "${containerId}" does not exist in the DOM`);
            }

            // Vérifier la disponibilité de React et ReactDOM
            if (!React || !ReactDOM) {
                throw new Error('React or ReactDOM is not available. Make sure the scripts are loaded properly.');
            }

            // Vérifier que PDFCanvasEditor est disponible
            if (!PDFCanvasEditor) {
                throw new Error('PDFCanvasEditor component is not available. Check for compilation errors.');
            }

            // Options par défaut avec validation
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

            // Validation des options critiques
            if (typeof defaultOptions.width !== 'number' || defaultOptions.width <= 0) {
                defaultOptions.width = 595;
            }
            if (typeof defaultOptions.height !== 'number' || defaultOptions.height <= 0) {
                defaultOptions.height = 842;
            }

            // Créer l'éditeur React avec protection
            const editorElement = createElement(PDFCanvasEditor, {
                options: defaultOptions,
                ref: (ref) => {
                    // Stocker la référence du composant
                    this.canvas = ref;
                }
            });

            // Vérifier que l'élément a été créé correctement
            if (!editorElement) {
                throw new Error('Failed to create React element for PDFCanvasEditor');
            }

            ReactDOM.render(editorElement, container);
            this.editors.set(containerId, { container, options: defaultOptions });


        } catch (error) {
            // Fallback visuel pour l'utilisateur
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = `
                    <div style="
                        color: #721c24;
                        background-color: #f8d7da;
                        border: 1px solid #f5c6cb;
                        border-radius: 4px;
                        padding: 15px;
                        margin: 10px 0;
                        font-family: Arial, sans-serif;
                        font-size: 14px;
                    ">
                        <strong>Erreur PDF Builder Pro</strong><br>
                        Impossible d'initialiser l'éditeur. Vérifiez la console pour plus de détails.<br>
                        <small>Erreur: ${error.message}</small>
                    </div>
                `;
            }

            // Re-throw pour permettre la gestion en amont si nécessaire
            throw error;
        }
    }

    // Détruire un éditeur
    destroy(containerId) {
        try {
            const editor = this.editors.get(containerId);
            if (editor) {
                // Vérifier que ReactDOM est disponible avant de démonter
                if (ReactDOM && ReactDOM.unmountComponentAtNode) {
                    ReactDOM.unmountComponentAtNode(editor.container);
                }
                this.editors.delete(containerId);
            }
        } catch (error) {
            // Forcer la suppression même en cas d'erreur
            this.editors.delete(containerId);
        }
    }

    // Obtenir les données d'un éditeur
    getData(containerId) {
        // Cette méthode pourrait être étendue pour récupérer l'état actuel
        return null;
    }

    // Obtenir les éléments du canvas actif
    getElements() {
        try {
            if (this.canvas && typeof this.canvas.getElements === 'function') {
                return this.canvas.getElements();
            }
            return [];
        } catch (error) {
            return [];
        }
    }
}

// Instance globale
const pdfBuilderPro = new PDFBuilderPro();

// Export par défaut pour webpack
export default pdfBuilderPro;

// Attacher à window pour WordPress - FORCER L'EXPOSITION DIRECTE
try {
    if (typeof window !== 'undefined') {
        // Forcer l'assignation directe de l'instance, pas du module webpack
        window.PDFBuilderPro = pdfBuilderPro;
        window.pdfBuilderPro = pdfBuilderPro;

    } else {
    }
} catch (error) {
}
// Marquer comme initialisé pour éviter les conflits
PDFBuilderSecurity.preventMultipleInit();

// Attacher à window pour WordPress - simplifié
window.PDFBuilderPro = pdfBuilderPro;
// Alias pour compatibilité
window.pdfBuilderPro = pdfBuilderPro;
