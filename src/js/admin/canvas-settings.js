/**
 * PDF Builder Canvas Settings JavaScript - Nouveau système de sauvegarde
 * Version: 2.0 - Refonte complète du système de sauvegarde des modals
 */
(function($) {
    'use strict';

    // Configuration du système de logs
    const LOG_PREFIX = '[CANVAS_MODAL_SAVE]';
    const LOG_LEVELS = {
        DEBUG: 'DEBUG',
        INFO: 'INFO',
        WARN: 'WARN',
        ERROR: 'ERROR'
    };

    // Fonction de logging unifiée
    function log(level, message, data = null) {
        const timestamp = new Date().toISOString();
        const logMessage = `${LOG_PREFIX} ${timestamp} [${level}] ${message}`;

        console.log(logMessage);
        if (data) {
            console.log(`${LOG_PREFIX} Data:`, data);
        }

        // Envoyer aussi au système de logs PHP si disponible
        try {
            if (typeof window.pdfBuilderCanvasSettings !== 'undefined' &&
                window.pdfBuilderCanvasSettings.ajax_url &&
                typeof $ !== 'undefined') {
                $.ajax({
                    url: window.pdfBuilderCanvasSettings.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_log_client_event',
                        level: level,
                        message: message,
                        data: JSON.stringify(data),
                        nonce: window.pdfBuilderCanvasSettings.nonce
                    },
                    async: true,
                    error: function() {
                        // Silent fail pour les logs
                    }
                });
            }
        } catch (e) {
            console.warn(`${LOG_PREFIX} Failed to send log to server:`, e);

        /**
         * Initialise le systeme de gestion des modals
         */
        init() {
            if (this.isInitialized) {
                log(LOG_LEVELS.WARN, 'CanvasModalManager already initialized');
                return;
            }

            log(LOG_LEVELS.INFO, 'Initializing Canvas Modal System...');

            this.registerModals();
            this.bindEvents();
            this.isInitialized = true;

            log(LOG_LEVELS.INFO, 'Canvas Modal System initialized successfully');
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
                    log(LOG_LEVELS.DEBUG, `Registered modal: ${category}`, { modalId, hasApplyButton: !!this.modals[category].applyButton });
                } else {
                    log(LOG_LEVELS.WARN, `Modal not found: ${modalId}`);
                }
            });
        }

        /**
         * Lie les événements pour tous les modals
         */
        bindEvents() {
            log(LOG_LEVELS.DEBUG, 'Binding modal events...');

            // Boutons de configuration (pour ouvrir les modals)
            document.addEventListener('click', (e) => {
                const configBtn = e.target.closest('.canvas-configure-btn');
                if (configBtn) {
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

            log(LOG_LEVELS.DEBUG, 'Modal events bound successfully');
        }

        /**
         * Gère le clic sur un bouton de configuration
         */
        handleConfigureButtonClick(button) {
            const card = button.closest('.canvas-card');
            if (!card) {
                log(LOG_LEVELS.ERROR, 'Configure button clicked but no parent card found');
                return;
            }

            const category = card.getAttribute('data-category');
            if (!category) {
                log(LOG_LEVELS.ERROR, 'Configure button clicked but no category found on card');
                return;
            }

            log(LOG_LEVELS.INFO, `Opening modal for category: ${category}`);
            this.openModal(category);
        }

        /**
         * Gère le clic sur un bouton d'application
         */
        async handleApplyButtonClick(button) {
            const modal = button.closest('.canvas-modal-overlay');
            if (!modal) {
                log(LOG_LEVELS.ERROR, 'Apply button clicked but no parent modal found');
                return;
            }

            const category = button.getAttribute('data-category');
            if (!category) {
                log(LOG_LEVELS.ERROR, 'Apply button clicked but no category found');
                return;
            }

            log(LOG_LEVELS.INFO, `Applying settings for modal: ${category}`);
            await this.saveModalSettings(modal, category);
        }

        /**
         * Gère le clic sur un bouton de fermeture
         */
        handleCloseButtonClick(button) {
            const modal = button.closest('.canvas-modal-overlay');
            if (modal) {
                log(LOG_LEVELS.DEBUG, 'Closing modal via button click');
                this.closeModal(modal);
            }
        }

        /**
         * Ouvre un modal spécifique
         */
        openModal(category) {
            const modalData = this.modals[category];
            if (!modalData) {
                log(LOG_LEVELS.ERROR, `Cannot open modal: unknown category ${category}`);
                return;
            }

            log(LOG_LEVELS.INFO, `Opening modal: ${category}`, { modalId: modalData.id });

            // Fermer tout modal ouvert
            this.closeAllModals();

            // Ouvrir le modal demandé
            modalData.element.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            this.currentModal = modalData;

            // Synchroniser les valeurs du modal avec les paramètres actuels
            this.syncModalValues(modalData);

            log(LOG_LEVELS.INFO, `Modal ${category} opened successfully`);
        }

        /**
         * Ferme un modal spécifique
         */
        closeModal(modalElement) {
            if (!modalElement) return;

            const category = this.getModalCategory(modalElement);
            log(LOG_LEVELS.DEBUG, `Closing modal: ${category || 'unknown'}`);

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
         * Synchronise les valeurs du modal avec les paramètres actuels
         */
        syncModalValues(modalData) {
            log(LOG_LEVELS.DEBUG, `Syncing values for modal: ${modalData.category}`);

            // Cette fonction peut être étendue pour synchroniser les valeurs
            // Pour l'instant, on suppose que les valeurs sont déjà correctes
        }

        /**
         * Sauvegarde les paramètres d'un modal
         */
        async saveModalSettings(modalElement, category) {
            log(LOG_LEVELS.INFO, `Starting save process for modal: ${category}`);

            try {
                // Collecter les données du formulaire
                const formData = this.collectModalData(modalElement, category);

                log(LOG_LEVELS.DEBUG, `Collected form data for ${category}:`, {
                    fieldCount: formData.getAll ? formData.getAll('field_count')[0] : 'unknown',
                    action: formData.get('action'),
                    category: formData.get('category')
                });

                // Désactiver le bouton pendant la sauvegarde
                const applyButton = modalElement.querySelector('.canvas-modal-apply');
                if (applyButton) {
                    applyButton.disabled = true;
                    applyButton.textContent = '⏳ Sauvegarde...';
                }

                // Envoyer la requête AJAX
                const response = await this.sendSaveRequest(formData);

                log(LOG_LEVELS.INFO, `Save response received for ${category}:`, response);

                if (response.success) {
                    log(LOG_LEVELS.INFO, `Settings saved successfully for ${category}`);

                    // Afficher une notification de succès
                    this.showNotification('Paramètres sauvegardés avec succès !', 'success');

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
                log(LOG_LEVELS.ERROR, `Save failed for modal ${category}:`, error);

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
            log(LOG_LEVELS.DEBUG, `Collecting data from modal: ${category}`);

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

                    log(LOG_LEVELS.DEBUG, `Collected field: ${name} = ${value}`);
                }
            });

            formData.append('field_count', fieldCount.toString());

            log(LOG_LEVELS.INFO, `Collected ${fieldCount} fields from modal ${category}`);
            return formData;
        }

        /**
         * Envoie la requête de sauvegarde
         */
        async sendSaveRequest(formData) {
            const url = window.pdfBuilderCanvasSettings?.ajax_url || ajaxurl;

            log(LOG_LEVELS.DEBUG, `Sending AJAX request to: ${url}`);

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        log(LOG_LEVELS.DEBUG, 'AJAX request successful', response);
                        resolve(response);
                    },
                    error: (xhr, status, error) => {
                        log(LOG_LEVELS.ERROR, 'AJAX request failed', { xhr, status, error });
                        reject(new Error(`Erreur AJAX: ${error}`));
                    }
                });
            });
        }

        /**
         * Affiche une notification
         */
        showNotification(message, type) {
            log(LOG_LEVELS.DEBUG, `Showing notification: ${type} - ${message}`);

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

    // Initialisation globale
    $(document).ready(function() {
        log(LOG_LEVELS.INFO, 'Document ready, initializing Canvas Modal Manager...');

        // Vérifier que jQuery est disponible
        if (typeof $ === 'undefined') {
            console.error('[CANVAS_MODAL_SAVE] CRITICAL ERROR: jQuery not available!');
            return;
        }

        // Vérifier que les variables globales sont disponibles
        if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
            log(LOG_LEVELS.ERROR, 'CRITICAL ERROR: window.pdfBuilderCanvasSettings not defined! Script localization failed.');
            return;
        }

        // Vérifier que les éléments DOM existent
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
            log(LOG_LEVELS.ERROR, 'CRITICAL ERROR: Missing modal elements:', missingModals);
            return;
        }

        // Vérifier que les boutons de configuration existent
        const configButtons = document.querySelectorAll('.canvas-configure-btn');
        if (configButtons.length === 0) {
            log(LOG_LEVELS.ERROR, 'CRITICAL ERROR: No configure buttons found!');
            return;
        }

        log(LOG_LEVELS.INFO, `Found ${configButtons.length} configure buttons and all modals`);

        // Créer et initialiser le gestionnaire de modals
        try {
            window.canvasModalManager = new CanvasModalManager();
            window.canvasModalManager.init();
            log(LOG_LEVELS.INFO, 'Canvas Modal System ready');
        } catch (error) {
            log(LOG_LEVELS.ERROR, 'CRITICAL ERROR: Failed to initialize CanvasModalManager:', error);
            console.error('[CANVAS_MODAL_SAVE] Exception during initialization:', error);
        }
    });

})(jQuery);

