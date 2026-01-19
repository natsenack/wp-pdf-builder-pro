/**
 * PDF Builder Canvas Settings JavaScript - Version simplifiee
 */
(function($) {
    'use strict';

    // Configuration du systeme de logs
    const LOG_PREFIX = '[CANVAS_MODAL_SAVE]';

    // Fonction de logging unifiee
    function log(level, message, data = null) {
        const timestamp = new Date().toISOString();
        const logMessage = `${LOG_PREFIX} ${timestamp} [${level}] ${message}`;

        console.log(logMessage);
        if (data) {
            console.log(`${LOG_PREFIX} Data:`, data);
        }
    }

    // Classe principale pour la gestion des modals canvas
    class CanvasModalManager {
        constructor() {
            this.modals = {};
            this.currentModal = null;
            this.isInitialized = false;
            log('INFO', 'CanvasModalManager initialized');
        }

        init() {
            if (this.isInitialized) {
                log('WARN', 'CanvasModalManager already initialized');
                return;
            }

            log('INFO', 'Initializing Canvas Modal System...');

            this.registerModals();
            this.bindEvents();
            this.isInitialized = true;

            log('INFO', 'Canvas Modal System initialized successfully');
        }

        registerModals() {
            const modalIds = [
                'canvas-affichage-modal-overlay',
                'canvas-navigation-modal-overlay',
                'canvas-comportement-modal-overlay',
                'canvas-systeme-modal-overlay'
            ];

            modalIds.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    const category = modalId.replace('canvas-', '').replace('-modal-overlay', '');
                    this.modals[category] = {
                        id: modalId,
                        element: modal,
                        category: category
                    };
                    log('DEBUG', `Registered modal: ${category}`);
                } else {
                    log('WARN', `Modal not found: ${modalId}`);
                }
            });
        }

        bindEvents() {
            log('DEBUG', 'Binding modal events...');

            // Boutons de configuration (pour ouvrir les modals)
            document.addEventListener('click', (e) => {
                const configBtn = e.target.closest('.canvas-configure-btn');
                if (configBtn) {
                    e.preventDefault();
                    this.handleConfigureButtonClick(configBtn);
                }
            });

            log('DEBUG', 'Modal events bound successfully');
        }

        handleConfigureButtonClick(button) {
            const card = button.closest('.canvas-card');
            if (!card) {
                log('ERROR', 'Configure button clicked but no parent card found');
                return;
            }

            const category = card.getAttribute('data-category');
            if (!category) {
                log('ERROR', 'Configure button clicked but no category found on card');
                return;
            }

            log('INFO', `Opening modal for category: ${category}`);
            this.openModal(category);
        }

        openModal(category) {
            const modalData = this.modals[category];
            if (!modalData) {
                log('ERROR', `Cannot open modal: unknown category ${category}`);
                return;
            }

            log('INFO', `Opening modal: ${category}`);

            // Fermer tout modal ouvert
            this.closeAllModals();

            // Ouvrir le modal demande
            modalData.element.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            this.currentModal = modalData;

            log('INFO', `Modal ${category} opened successfully`);
        }

        closeModal(modalElement) {
            if (!modalElement) return;

            log('DEBUG', 'Closing modal');

            modalElement.style.display = 'none';
            document.body.style.overflow = '';
            this.currentModal = null;
        }

        closeAllModals() {
            Object.values(this.modals).forEach(modalData => {
                modalData.element.style.display = 'none';
            });
            document.body.style.overflow = '';
            this.currentModal = null;
        }
    }

    // Initialisation globale
    $(document).ready(function() {
        log('INFO', 'Document ready, initializing Canvas Modal Manager...');

        // Verifier que jQuery est disponible
        if (typeof $ === 'undefined') {
            console.error('[CANVAS_MODAL_SAVE] CRITICAL ERROR: jQuery not available!');
            return;
        }

        // Verifier que les elements DOM existent
        const modalIds = [
            'canvas-affichage-modal-overlay',
            'canvas-navigation-modal-overlay',
            'canvas-comportement-modal-overlay',
            'canvas-systeme-modal-overlay'
        ];

        let missingModals = [];
        modalIds.forEach(id => {
            if (!document.getElementById(id)) {
                missingModals.push(id);
            }
        });

        if (missingModals.length > 0) {
            log('ERROR', 'CRITICAL ERROR: Missing modal elements:', missingModals);
            return;
        }

        // Verifier que les boutons de configuration existent
        const configButtons = document.querySelectorAll('.canvas-configure-btn');
        if (configButtons.length === 0) {
            log('ERROR', 'CRITICAL ERROR: No configure buttons found!');
            return;
        }

        log('INFO', `Found ${configButtons.length} configure buttons and all modals`);

        // Creer et initialiser le gestionnaire de modals
        try {
            window.canvasModalManager = new CanvasModalManager();
            window.canvasModalManager.init();
            log('INFO', 'Canvas Modal System ready');
        } catch (error) {
            log('ERROR', 'CRITICAL ERROR: Failed to initialize CanvasModalManager:', error);
            console.error('[CANVAS_MODAL_SAVE] Exception during initialization:', error);
        }
    });

})(jQuery);
