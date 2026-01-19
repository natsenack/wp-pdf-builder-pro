/**
 * PDF Builder Canvas Settings JavaScript
 * Version: 2.0 - Modal management system
 */

(function($) {
    'use strict';

    // Configuration du système de logs
    const LOG_PREFIX = '[CANVAS_MODAL]';
    const LOG_LEVELS = {
        ERROR: 'ERROR'
    };

    // Fonction de logging simplifiée pour production
    function log(level, message, data = null) {
        if (level === LOG_LEVELS.ERROR) {
            console.error(`${LOG_PREFIX} ${message}`, data || '');
        }
    }

    // Classe principale pour la gestion des modals canvas
    class CanvasModalManager {
        constructor() {
            this.modals = {};
            this.currentModal = null;
            this.isInitialized = false;
        }

        /**
         * Initialise le gestionnaire de modals
         */
        init() {
            this.registerModals();
            this.isInitialized = true;
        }

        /**
         * Enregistre tous les modals disponibles
         */
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
                        category: category,
                        applyButton: modal.querySelector('.canvas-modal-apply'),
                        cancelButton: modal.querySelector('.canvas-modal-cancel'),
                        closeButton: modal.querySelector('.canvas-modal-close')
                    };
                }
            });

            // Lier les événements
            this.bindEvents();
        }

        /**
         * Lie les événements pour tous les modals
         */
        bindEvents() {
            console.log('[CANVAS_MODAL] bindEvents called');

            // Boutons de configuration (pour ouvrir les modals)
            document.addEventListener('click', (e) => {
                const configBtn = e.target.closest('.canvas-configure-btn');
                console.log('[CANVAS_MODAL] Click detected, target:', e.target);
                console.log('[CANVAS_MODAL] Closest .canvas-configure-btn:', configBtn);

                if (configBtn) {
                    console.log('[CANVAS_MODAL] Configure button clicked:', configBtn);
                    e.preventDefault();
                    this.handleConfigureButtonClick(configBtn);
                }
            });

            // Boutons d'application (pour sauvegarder)
            document.addEventListener('click', (e) => {
                const applyBtn = e.target.closest('.canvas-modal-apply');
                if (applyBtn) {
                    e.preventDefault();
                    this.handleApplyButtonClick(applyBtn);
                }
            });

            // Boutons d'annulation et de fermeture
            document.addEventListener('click', (e) => {
                const cancelBtn = e.target.closest('.canvas-modal-cancel');
                const closeBtn = e.target.closest('.canvas-modal-close');

                if (cancelBtn || closeBtn) {
                    e.preventDefault();
                    this.handleCloseButtonClick(cancelBtn || closeBtn);
                }
            });

            // Clic en dehors du modal pour fermer
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('canvas-modal-overlay')) {
                    this.closeModal(e.target);
                }
            });
        }

        /**
         * Gère le clic sur un bouton de configuration
         */
        handleConfigureButtonClick(button) {
            console.log('[CANVAS_MODAL] handleConfigureButtonClick called with button:', button);
            console.log('[CANVAS_MODAL] Button classes:', button.className);
            console.log('[CANVAS_MODAL] Button parent element:', button.parentElement);

            const card = button.closest('.canvas-card');
            console.log('[CANVAS_MODAL] Found card element:', card);

            if (!card) {
                console.error('[CANVAS_MODAL] No parent card found for button');
                return;
            }

            const category = card.getAttribute('data-category');
            console.log('[CANVAS_MODAL] Card data-category:', category);

            if (!category) {
                console.error('[CANVAS_MODAL] No category found on card');
                return;
            }

            console.log('[CANVAS_MODAL] Opening modal for category:', category);
            this.openModal(category);
        }

        /**
         * Gère le clic sur un bouton d'application
         */
        async handleApplyButtonClick(button) {
            const modal = button.closest('.canvas-modal-overlay');
            if (!modal) {
                console.error('[CANVAS_MODAL] Apply button clicked but no parent modal found');
                return;
            }

            const category = button.getAttribute('data-category');
            if (!category) {
                console.error('[CANVAS_MODAL] Apply button clicked but no category found');
                return;
            }

            await this.saveModalSettings(modal, category);
        }

        /**
         * Gère le clic sur un bouton de fermeture
         */
        handleCloseButtonClick(button) {
            const modal = button.closest('.canvas-modal-overlay');
            if (modal) {
                this.closeModal(modal);
            }
        }

        /**
         * Ouvre un modal spécifique
         */
        openModal(category) {
            console.log('[CANVAS_MODAL] openModal called for category:', category);
            const modalData = this.modals[category];
            if (!modalData) {
                console.error('[CANVAS_MODAL] Unknown category:', category);
                return;
            }

            const modalElement = modalData.element;
            if (!modalElement) {
                console.error('[CANVAS_MODAL] Modal element not found for category:', category);
                return;
            }

            console.log('[CANVAS_MODAL] Opening modal element:', modalElement.id);
            this.closeAllModals();
            modalElement.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            this.currentModal = modalData;
        }

        /**
         * Ferme un modal spécifique
         */
        closeModal(modalElement) {
            if (!modalElement) return;

            modalElement.style.display = 'none';
            document.body.style.overflow = '';
            this.currentModal = null;
        }

        /**
         * Ferme tous les modals
         */
        closeAllModals() {
            Object.values(this.modals).forEach(modalData => {
                modalData.element.style.display = 'none';
            });
            document.body.style.overflow = '';
            this.currentModal = null;
        }

        /**
         * Sauvegarde les paramètres d'un modal
         */
        async saveModalSettings(modalElement, category) {
            try {
                // Collecter les données du formulaire
                const formData = this.collectModalData(modalElement, category);

                // Désactiver le bouton pendant la sauvegarde
                const applyButton = modalElement.querySelector('.canvas-modal-apply');
                if (applyButton) {
                    applyButton.disabled = true;
                    applyButton.textContent = '⏳ Sauvegarde...';
                }

                // Envoyer la requête AJAX
                const response = await this.sendSaveRequest(formData);

                if (response.success) {
                    // Fermer le modal
                    this.closeModal(modalElement);

                    // Recharger la page pour refléter les changements
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } else {
                    throw new Error(response.data?.message || 'Erreur inconnue lors de la sauvegarde');
                }

            } catch (error) {
                console.error('[CANVAS_MODAL] Save failed:', error);

                // Afficher une notification d'erreur
                this.showNotification(`Erreur lors de la sauvegarde: ${error.message}`, 'error');

            } finally {
                // Réactiver le bouton
                const applyButton = modalElement.querySelector('.canvas-modal-apply');
                if (applyButton) {
                    applyButton.disabled = false;
                    applyButton.textContent = '✅ Appliquer';
                }
            }
        }

        /**
         * Collecte les données d'un modal
         */
        collectModalData(modalElement, category) {
            const formData = new FormData();
            let fieldCount = 0;

            // Ajouter les métadonnées
            formData.append('action', 'pdf_builder_save_canvas_modal');
            formData.append('category', category);
            formData.append('nonce', window.pdfBuilderCanvasSettings?.nonce || '');

            // Collecter tous les inputs du modal
            const inputs = modalElement.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                const name = input.name;
                const type = input.type;

                if (!name || input.disabled) {
                    return; // Ignorer les inputs sans nom ou désactivés
                }

                let value = null;

                if (type === 'checkbox') {
                    value = input.checked ? '1' : '0';
                } else if (type === 'radio') {
                    if (input.checked) {
                        value = input.value;
                    } else {
                        return; // Ne pas ajouter les radios non cochées
                    }
                } else {
                    value = input.value;
                }

                if (value !== null) {
                    formData.append(name, value);
                    fieldCount++;
                }
            });

            formData.append('field_count', fieldCount.toString());

            return formData;
        }

        /**
         * Envoie la requête de sauvegarde
         */
        async sendSaveRequest(formData) {
            const url = window.pdfBuilderCanvasSettings?.ajax_url || ajaxurl;

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        resolve(response);
                    },
                    error: (xhr, status, error) => {
                        reject(new Error(`Erreur AJAX: ${error}`));
                    }
                });
            });
        }

        /**
         * Affiche une notification
         */
        showNotification(message, type) {
            // Essayer d'utiliser le système de notification existant
            if (typeof showSystemNotification !== 'undefined') {
                showSystemNotification(message, type);
            } else {
                // Fallback vers alert
                alert(message);
            }
        }

        /**
         * Obtient la catégorie d'un modal à partir de son élément
         */
        getModalCategory(modalElement) {
            for (const [category, modalData] of Object.entries(this.modals)) {
                if (modalData.element === modalElement) {
                    return category;
                }
            }
            return null;
        }
    }

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        console.log('[CANVAS_MODAL] DOMContentLoaded, starting initialization');

        // Petit délai pour s'assurer que tout est bien chargé
        setTimeout(function() {
            initCanvasModals();
        }, 100);
    });

    // Fonction d'initialisation séparée pour plus de clarté
    function initCanvasModals() {
        console.log('[CANVAS_MODAL] initCanvasModals called');

        // Vérifications préalables
        if (typeof window.jQuery === 'undefined' && typeof $ === 'undefined') {
            console.error('[CANVAS_MODAL] jQuery not available');
            return;
        }

        // Utiliser jQuery s'il est disponible
        var $ = window.jQuery || window.$;

        if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
            console.error('[CANVAS_MODAL] pdfBuilderCanvasSettings not defined');
            return;
        }

        // Vérifier la page actuelle
        const currentUrl = window.location.href;
        console.log('[CANVAS_MODAL] Current URL:', currentUrl);

        if (!currentUrl.includes('page=pdf-builder-settings') || !currentUrl.includes('tab=contenu')) {
            console.log('[CANVAS_MODAL] Not on settings page, exiting');
            return; // Pas sur la page des paramètres
        }

        // Vérifier les éléments DOM critiques
        const modalIds = [
            'canvas-affichage-modal-overlay',
            'canvas-navigation-modal-overlay',
            'canvas-comportement-modal-overlay',
            'canvas-systeme-modal-overlay'
        ];

        console.log('[CANVAS_MODAL] Checking for modal elements...');
        modalIds.forEach(id => {
            const element = document.getElementById(id);
            console.log(`[CANVAS_MODAL] Modal ${id}:`, element ? 'FOUND' : 'NOT FOUND');
        });

        const missingModals = modalIds.filter(id => !document.getElementById(id));
        if (missingModals.length > 0) {
            console.error('[CANVAS_MODAL] Missing modal elements:', missingModals);
            return;
        }

        // Vérifier que les boutons de configuration existent
        const configButtons = document.querySelectorAll('.canvas-configure-btn');
        console.log('[CANVAS_MODAL] Found configure buttons:', configButtons.length);
        if (configButtons.length === 0) {
            console.error('[CANVAS_MODAL] No configure buttons found!');
            return;
        }

        console.log('[CANVAS_MODAL] Found', configButtons.length, 'configure buttons');

        // Créer et initialiser le gestionnaire de modals
        try {
            console.log('[CANVAS_MODAL] Creating CanvasModalManager...');
            window.canvasModalManager = new CanvasModalManager();
            console.log('[CANVAS_MODAL] Calling init()...');
            window.canvasModalManager.init();
            console.log('[CANVAS_MODAL] Canvas Modal System ready');
        } catch (error) {
            console.error('[CANVAS_MODAL] Exception during initialization:', error);
        }
    }

})(jQuery);