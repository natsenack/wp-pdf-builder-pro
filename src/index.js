// Tous les imports doivent être au niveau supérieur du module
import React from 'react';
import { createElement } from 'react';
import ReactDOM from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';

// Forcer l'inclusion de tous les hooks personnalisés
import /* webpackMode: "eager" */ * as hooks from './hooks';

console.log('PDF Builder Pro: Script execution started - with proper imports');

// Système de protection et monitoring
const PDFBuilderSecurity = {
    healthChecks: [],
    errors: [],
    initialized: false,

    // Health check pour vérifier que toutes les dépendances sont disponibles
    performHealthCheck() {
        console.log('PDF Builder Pro: Performing health check...');

        const checks = {
            react: typeof React === 'object' && React.createElement,
            reactDom: typeof ReactDOM === 'object' && ReactDOM.render,
            pdfCanvasEditor: typeof PDFCanvasEditor === 'function',
            hooks: typeof hooks === 'object',
            window: typeof window !== 'undefined',
            document: typeof document !== 'undefined'
        };

        this.healthChecks = checks;
        const allHealthy = Object.values(checks).every(Boolean);

        if (allHealthy) {
            console.log('PDF Builder Pro: All health checks passed ✅');
            this.initialized = true;
        } else {
            console.error('PDF Builder Pro: Health check failed ❌', checks);
            this.initialized = false;
        }

        return allHealthy;
    },

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
        console.error('PDF Builder Pro Security Error:', errorInfo);
    },

    // Protection contre les appels multiples
    preventMultipleInit() {
        if (window._pdfBuilderInitialized) {
            console.warn('PDF Builder Pro: Multiple initialization attempt prevented');
            return false;
        }
        window._pdfBuilderInitialized = true;
        return true;
    }
};

// Test des imports de base avec protection
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
    PDFBuilderSecurity.logError(error, 'React initialization');
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
                console.warn('PDFBuilderPro: Invalid width, using default A4 width');
                defaultOptions.width = 595;
            }
            if (typeof defaultOptions.height !== 'number' || defaultOptions.height <= 0) {
                console.warn('PDFBuilderPro: Invalid height, using default A4 height');
                defaultOptions.height = 842;
            }

            // Créer l'éditeur React avec protection
            const editorElement = createElement(PDFCanvasEditor, {
                options: defaultOptions
            });

            // Vérifier que l'élément a été créé correctement
            if (!editorElement) {
                throw new Error('Failed to create React element for PDFCanvasEditor');
            }

            ReactDOM.render(editorElement, container);
            this.editors.set(containerId, { container, options: defaultOptions });

            console.log('PDFBuilderPro: Editor initialized successfully for container:', containerId);

        } catch (error) {
            console.error('PDFBuilderPro: Failed to initialize editor:', error);

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
                console.log('PDFBuilderPro: Editor destroyed for container:', containerId);
            }
        } catch (error) {
            console.error('PDFBuilderPro: Error during destroy:', error);
            // Forcer la suppression même en cas d'erreur
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

// Attacher à window pour WordPress - avec vérification et protection
if (typeof window !== 'undefined') {
    // Effectuer le health check avant d'exposer l'instance
    if (PDFBuilderSecurity.performHealthCheck()) {
        window.PDFBuilderPro = pdfBuilderPro;
        // Alias pour compatibilité
        window.pdfBuilderPro = pdfBuilderPro;
        console.log('PDF Builder Pro: PDFBuilderPro attached to window');

        // Marquer comme initialisé pour éviter les conflits
        PDFBuilderSecurity.preventMultipleInit();
    } else {
        console.error('PDF Builder Pro: Not attaching to window due to health check failure');
        // Exposer quand même une version limitée pour le debugging
        window.PDFBuilderPro = {
            version: '2.0.0',
            status: 'unhealthy',
            errors: PDFBuilderSecurity.errors,
            healthChecks: PDFBuilderSecurity.healthChecks
        };
    }
}

// Export par défaut pour webpack
export default pdfBuilderPro;

