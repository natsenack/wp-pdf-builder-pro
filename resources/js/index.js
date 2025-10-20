// Tous les imports doivent être au niveau supérieur du module
import React from 'react';
import { createElement } from 'react';
import ReactDOM from 'react-dom';
import { PDFCanvasEditor } from './components/PDFCanvasEditor';

// PDF BUILDER DEBUG: File loaded successfully - TIMESTAMP: ${Date.now()}
console.log('=== PDF BUILDER FILE LOADED === TIMESTAMP:', Date.now());

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

        // Fonction pour afficher l'aperçu dans la metabox WooCommerce
        window.pdfBuilderShowPreview = function(orderId, templateId, nonce) {
            console.log('=== PDF BUILDER DEBUG: pdfBuilderShowPreview START ===');
            console.log('Parameters:', { orderId, templateId, nonce });

            // VERSION DE FALLBACK SIMPLE - Test de l'affichage de base
            console.log('=== USING FALLBACK MODAL ===');

            try {
                // Supprimer toute modal existante pour éviter les doublons
                const existingModal = document.getElementById('pdf-builder-preview-modal');
                if (existingModal) {
                    existingModal.remove();
                    console.log('=== REMOVED EXISTING MODAL ===');
                }

                // Créer une modal simple en HTML pur
                const modal = document.createElement('div');
                modal.id = 'pdf-builder-preview-modal';
                modal.innerHTML = `
                    <div style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.8);
                        z-index: 999999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <div style="
                            background: white;
                            padding: 30px;
                            border-radius: 8px;
                            max-width: 600px;
                            width: 90%;
                            text-align: center;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                        ">
                            <h2 style="color: #007cba; margin-bottom: 20px;">📄 Aperçu PDF - Commande #${orderId}</h2>
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;">
                                <p style="margin: 10px 0;"><strong>Commande ID:</strong> ${orderId}</p>
                                <p style="margin: 10px 0;"><strong>Template ID:</strong> ${templateId}</p>
                                <p style="margin: 10px 0;"><strong>Statut:</strong> <span style="color: #28a745;">Prêt pour génération</span></p>
                            </div>
                            <p style="color: #666; margin: 20px 0; font-size: 14px;">
                                Fonctionnalité d'aperçu en cours de développement.<br>
                                Utilisez le bouton "Générer PDF" pour créer le document.
                            </p>
                            <div style="margin-top: 25px;">
                                <button onclick="this.parentElement.parentElement.parentElement.remove()" style="
                                    background: #007cba;
                                    color: white;
                                    border: none;
                                    padding: 12px 24px;
                                    border-radius: 4px;
                                    cursor: pointer;
                                    font-size: 14px;
                                    margin-right: 10px;
                                ">Fermer</button>
                                <button onclick="alert('Fonctionnalité de génération PDF à implémenter'); this.parentElement.parentElement.parentElement.remove()" style="
                                    background: #28a745;
                                    color: white;
                                    border: none;
                                    padding: 12px 24px;
                                    border-radius: 4px;
                                    cursor: pointer;
                                    font-size: 14px;
                                ">Générer PDF</button>
                            </div>
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);
                console.log('=== FALLBACK MODAL CREATED AND APPENDED TO BODY ===');
                console.log('Modal element:', modal);
                console.log('Body children count:', document.body.children.length);
                
                // Forcer l'affichage et ajouter une animation
                modal.style.display = 'block';
                modal.style.opacity = '1';
                console.log('=== MODAL DISPLAYED SUCCESSFULLY ===');

            } catch (error) {
                console.error('=== ERROR CREATING FALLBACK MODAL ===', error);
                alert('Erreur lors de l\'affichage de l\'aperçu: ' + error.message);
            }

            return; // Ne pas exécuter le code React pour le moment

            // Créer ou récupérer la modal d'aperçu
            let modalContainer = document.getElementById('pdf-builder-preview-modal');
            if (!modalContainer) {
                console.log('Creating modal container');
                modalContainer = document.createElement('div');
                modalContainer.id = 'pdf-builder-preview-modal';
                modalContainer.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                `;
                document.body.appendChild(modalContainer);
                console.log('Modal container created and appended');
            } else {
                console.log('Modal container already exists');
            }

            // Créer le conteneur React pour la modal
            let previewRoot = document.getElementById('pdf-builder-preview-root');
            if (!previewRoot) {
                console.log('Creating preview root');
                previewRoot = document.createElement('div');
                previewRoot.id = 'pdf-builder-preview-root';
                previewRoot.style.cssText = 'width: 100%; height: 100%;';
                modalContainer.appendChild(previewRoot);
                console.log('Preview root created and appended');
            } else {
                console.log('Preview root already exists');
            }

            console.log('Setting modal to display flex');
            modalContainer.style.display = 'flex';

            console.log('Starting dynamic import');

            // Importer dynamiquement la PreviewModal
            import('./components/preview-system/PreviewModal').then(({ default: PreviewModal }) => {
                console.log('=== PDF BUILDER SUCCESS: Import successful ===');

                // Créer l'élément React pour la modal d'aperçu
                const previewElement = createElement(PreviewModal, {
                    isOpen: true,
                    onClose: () => {
                        console.log('=== PDF BUILDER: Modal close requested ===');
                        modalContainer.style.display = 'none';
                        ReactDOM.unmountComponentAtNode(previewRoot);
                    },
                    mode: 'metabox',
                    orderId: orderId,
                    templateId: templateId,
                    nonce: nonce
                });

                console.log('=== PDF BUILDER: Rendering React component ===');
                console.log('ReactDOM available:', typeof ReactDOM);
                console.log('React available:', typeof React);
                console.log('createElement available:', typeof createElement);

                // Rendre la modal
                try {
                    ReactDOM.render(previewElement, previewRoot);
                    modalContainer.style.display = 'flex';
                    console.log('=== PDF BUILDER: Modal should be visible now ===');
                } catch (renderError) {
                    console.error('=== PDF BUILDER RENDER ERROR ===', renderError);
                    // Fallback: try to show an alert
                    alert('Erreur de rendu React: ' + renderError.message);
                }
            }).catch(error => {
                console.error('=== PDF BUILDER ERROR: Import failed ===', error);
                alert('Erreur lors du chargement du système d\'aperçu. Veuillez recharger la page.');
            });
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

