// Tous les imports doivent être au niveau supérieur du module
import React from 'react';
import { createElement } from 'react';
import ReactDOM from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';

// PDF BUILDER DEBUG: File loaded successfully - TIMESTAMP: ${Date.now()}
console.log('=== PDF BUILDER FILE LOADED === TIMESTAMP:', Date.now());

// TEST IMMÉDIAT DE CHARGEMENT - Ajouter un élément visible dès le chargement
(function() {
    const testElement = document.createElement('div');
    testElement.id = 'pdf-builder-loaded-indicator';
    testElement.style.cssText = `
        position: fixed !important;
        bottom: 10px !important;
        right: 10px !important;
        background: #007cba !important;
        color: white !important;
        padding: 8px 12px !important;
        border-radius: 4px !important;
        font-size: 11px !important;
        font-weight: bold !important;
        z-index: 99999 !important;
        border: 2px solid #005a87 !important;
    `;
    testElement.innerHTML = 'PDF Builder ✓ Loaded at ' + new Date().toLocaleTimeString();
    document.body.appendChild(testElement);
    console.log('=== PDF BUILDER LOADED INDICATOR CREATED ===');
})();

// Forcer l'inclusion de tous les hooks personnalisés
import /* webpackMode: "eager" */ * as hooks from './hooks';


// Système de protection et monitoring
const PDFBuilderSecurity = {
    healthChecks: [],
    errors: [],
    initialized: false,

    // Health check pour vérifier que toutes les dépendances sont disponibles
    performHealthCheck() {

        const checks = {
            react: typeof React === 'object' && React.createElement,
            reactDom: typeof ReactDOM === 'object' && ReactDOM.render,
            pdfCanvasEditor: PDFCanvasEditor && (typeof PDFCanvasEditor === 'function' || typeof PDFCanvasEditor === 'object'),
            hooks: typeof hooks === 'object',
            window: typeof window !== 'undefined',
            document: typeof document !== 'undefined'
        };

        this.healthChecks = checks;
        const allHealthy = Object.values(checks).every(Boolean);

        if (allHealthy) {
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

    // Protection contre les appels multiples - améliorée
    preventMultipleInit() {
        const now = Date.now();
        const lastInit = window._pdfBuilderLastInit || 0;
        const timeSinceLastInit = now - lastInit;

        // Si plus de 5 secondes se sont écoulées depuis la dernière initialisation,
        // permettre une réinitialisation (utile pour les rechargements de page)
        if (window._pdfBuilderInitialized && timeSinceLastInit < 5000) {
            // Log silencieux au lieu d'un avertissement intrusif
            console.debug('PDF Builder Pro: Multiple initialization attempt prevented (last init:', new Date(lastInit).toLocaleTimeString() + ')');
            return false;
        }

        window._pdfBuilderInitialized = true;
        window._pdfBuilderLastInit = now;
        return true;
    }
};

// Test des imports de base avec protection
try {

    // Exposer React globalement pour compatibilité
    if (typeof window !== 'undefined') {
        window.React = React;
        window.ReactDOM = ReactDOM;
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

    // Obtenir les éléments du canvas actif
    getElements() {
        try {
            if (this.canvas && typeof this.canvas.getElements === 'function') {
                return this.canvas.getElements();
            }
            console.warn('PDFBuilderPro: No active canvas or getElements method not available');
            return [];
        } catch (error) {
            console.error('PDFBuilderPro: Error getting elements:', error);
            return [];
        }
    }
}

// Instance globale
const pdfBuilderPro = new PDFBuilderPro();

// Attacher à window pour WordPress - avec vérification et protection
if (typeof window !== 'undefined') {
    // Effectuer le health check avant d'exposer l'instance
    if (PDFBuilderSecurity.performHealthCheck()) {
        window.PDFBuilderPro = pdfBuilderPro;
        // Alias pour compatibilité
        window.pdfBuilderPro = pdfBuilderPro;

        // Fonction pour afficher l'aperçu dans la metabox WooCommerce - RECREATION COMPLETE PHASE 8
        window.pdfBuilderShowPreview = function(orderId, templateId, nonce) {
            console.log('=== PDF BUILDER PHASE 8: pdfBuilderShowPreview START ===');
            console.log('Parameters:', { orderId, templateId, nonce, timestamp: Date.now() });

            // 1. IMMÉDIAT: Créer un indicateur visuel que la fonction est appelée
            const visualIndicator = document.createElement('div');
            visualIndicator.id = 'phase8-visual-indicator';
            visualIndicator.style.cssText = `
                position: fixed !important;
                top: 10px !important;
                right: 10px !important;
                background: #ff6b35 !important;
                color: white !important;
                padding: 15px !important;
                border-radius: 8px !important;
                z-index: 1000000 !important;
                font-weight: bold !important;
                font-size: 14px !important;
                border: 3px solid #ff4500 !important;
                box-shadow: 0 4px 12px rgba(255,107,53,0.3) !important;
            `;
            visualIndicator.innerHTML = `
                🔥 PHASE 8 ACTIVE<br>
                Order: ${orderId}<br>
                Template: ${templateId}<br>
                Time: ${new Date().toLocaleTimeString()}<br>
                <span style="color: yellow;">MODAL LOADING...</span>
            `;
            document.body.appendChild(visualIndicator);
            console.log('=== VISUAL INDICATOR CREATED ===');

            // 2. Supprimer toute modal existante
            const existingModal = document.getElementById('pdf-builder-preview-modal');
            if (existingModal) {
                existingModal.remove();
                console.log('=== REMOVED EXISTING MODAL ===');
            }

            // 3. Créer le conteneur de modal
            const modalContainer = document.createElement('div');
            modalContainer.id = 'pdf-builder-preview-modal';
            modalContainer.style.cssText = `
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: rgba(0,0,0,0.8) !important;
                z-index: 999999 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            `;
            document.body.appendChild(modalContainer);
            console.log('=== MODAL CONTAINER CREATED ===');

            // 4. Créer le contenu de la modal avec React
            const modalContent = document.createElement('div');
            modalContent.id = 'pdf-builder-preview-root';
            modalContent.style.cssText = `
                background: white !important;
                border-radius: 12px !important;
                width: 90vw !important;
                height: 90vh !important;
                max-width: 1200px !important;
                max-height: 800px !important;
                position: relative !important;
                overflow: hidden !important;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3) !important;
            `;
            modalContainer.appendChild(modalContent);

            // 5. Bouton de fermeture
            const closeButton = document.createElement('button');
            closeButton.innerHTML = '✕';
            closeButton.style.cssText = `
                position: absolute !important;
                top: 15px !important;
                right: 15px !important;
                background: #dc3545 !important;
                color: white !important;
                border: none !important;
                border-radius: 50% !important;
                width: 40px !important;
                height: 40px !important;
                font-size: 20px !important;
                cursor: pointer !important;
                z-index: 1000001 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            `;
            closeButton.onclick = function() {
                modalContainer.remove();
                visualIndicator.remove();
                console.log('=== MODAL CLOSED ===');
            };
            modalContent.appendChild(closeButton);

            // 6. Contenu de chargement initial
            const loadingContent = document.createElement('div');
            loadingContent.style.cssText = `
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                height: 100% !important;
                color: #666 !important;
                font-size: 18px !important;
            `;
            loadingContent.innerHTML = `
                <div style="font-size: 48px; margin-bottom: 20px;">🔄</div>
                <div style="font-weight: bold; margin-bottom: 10px;">Chargement de l'aperçu...</div>
                <div style="font-size: 14px; color: #999;">
                    Order: ${orderId} | Template: ${templateId}<br>
                    ${new Date().toLocaleString()}
                </div>
            `;
            modalContent.appendChild(loadingContent);

            // 7. Monter le composant React PreviewModal
            try {
                console.log('=== LOADING PREVIEW MODAL COMPONENT ===');

                // Importer dynamiquement le composant PreviewModal
                import('./components/preview-system/PreviewModal.jsx').then(({ PreviewModal }) => {
                    console.log('=== PREVIEW MODAL COMPONENT LOADED ===');

                    // Créer l'élément React
                    const previewModalElement = React.createElement(PreviewModal, {
                        isOpen: true,
                        onClose: () => {
                            modalContainer.remove();
                            visualIndicator.remove();
                            console.log('=== MODAL CLOSED VIA REACT ===');
                        },
                        mode: 'metabox',
                        orderId: orderId,
                        templateId: templateId,
                        nonce: nonce
                    });

                    // Monter avec ReactDOM
                    ReactDOM.render(previewModalElement, modalContent);

                    // Mettre à jour l'indicateur visuel
                    visualIndicator.innerHTML = visualIndicator.innerHTML.replace('MODAL LOADING...', '<span style="color: #28a745;">MODAL LOADED ✓</span>');

                    console.log('=== REACT MODAL MOUNTED SUCCESSFULLY ===');
                }).catch(error => {
                    console.error('=== ERROR LOADING PREVIEW MODAL ===', error);
                    loadingContent.innerHTML = `
                        <div style="font-size: 48px; margin-bottom: 20px; color: #dc3545;">❌</div>
                        <div style="font-weight: bold; margin-bottom: 10px; color: #dc3545;">Erreur de chargement</div>
                        <div style="font-size: 14px; color: #666;">${error.message}</div>
                    `;
                    visualIndicator.innerHTML = visualIndicator.innerHTML.replace('MODAL LOADING...', '<span style="color: #dc3545;">ERROR ❌</span>');
                });

            } catch (error) {
                console.error('=== ERROR IN MODAL CREATION ===', error);
                loadingContent.innerHTML = `
                    <div style="font-size: 48px; margin-bottom: 20px; color: #dc3545;">❌</div>
                    <div style="font-weight: bold; margin-bottom: 10px; color: #dc3545;">Erreur système</div>
                    <div style="font-size: 14px; color: #666;">${error.message}</div>
                `;
                visualIndicator.innerHTML = visualIndicator.innerHTML.replace('MODAL LOADING...', '<span style="color: #dc3545;">ERROR ❌</span>');
            }

            console.log('=== PDF BUILDER PHASE 8: pdfBuilderShowPreview END ===');
        };

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

