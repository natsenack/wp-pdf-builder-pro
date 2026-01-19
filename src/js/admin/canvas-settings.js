/**
 * PDF Builder Canvas Settings JavaScript - Nouveau système de sauvegarde
 * Version: 2.0 - Refonte complète du système de sauvegarde des modals
 */
console.log('🚀🚀🚀 CANVAS MODAL SCRIPT LOADING - START 🚀🚀🚀');
console.log('[CANVAS_MODAL_SAVE] SCRIPT FILE START - canvas-settings.js file execution begins');

(function($) {
    'use strict';

    console.log('📦 CANVAS MODAL SCRIPT - jQuery wrapper entered');

    // LOG CRITIQUE - Script chargé
    console.log('[CANVAS_MODAL_SAVE] SCRIPT LOADED - canvas-settings.js has been loaded and executed');

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
        }
    }

    // LOG CRITIQUE - Définition de la classe
    console.log('[CANVAS_MODAL_SAVE] DEFINING CanvasModalManager class');

    // Classe principale pour la gestion des modals canvas
    class CanvasModalManager {
        constructor() {
            this.modals = {};
            this.currentModal = null;
            this.isInitialized = false;
            log('INFO', 'CanvasModalManager constructor called');
        }

        /**
         * Enregistre tous les modals disponibles
         */
        registerModals() {
            log(LOG_LEVELS.INFO, 'registerModals called - looking for modal elements');

            const modalIds = [
                'canvas-affichage-modal-overlay',
                'canvas-navigation-modal-overlay',
                'canvas-comportement-modal-overlay',
                'canvas-systeme-modal-overlay'
            ];

            log(LOG_LEVELS.INFO, 'Searching for modal IDs:', modalIds);

            modalIds.forEach(modalId => {
                const modal = document.getElementById(modalId);
                log(LOG_LEVELS.DEBUG, `Checking modal ${modalId}:`, { found: !!modal, element: modal });

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

            log(LOG_LEVELS.INFO, `Modal registration complete. Registered ${Object.keys(this.modals).length} modals:`, Object.keys(this.modals));

            // Vérifier les boutons de configuration
            const configButtons = document.querySelectorAll('.canvas-configure-btn');
            log(LOG_LEVELS.INFO, `Found ${configButtons.length} configure buttons in DOM`);
            configButtons.forEach((btn, index) => {
                log(LOG_LEVELS.DEBUG, `Configure button ${index}:`, {
                    element: btn,
                    className: btn.className,
                    parentElement: btn.parentElement,
                    parentClass: btn.parentElement ? btn.parentElement.className : 'no parent'
                });
            });

            // Lier les événements maintenant que tout est prêt
            console.log('🔗🔗🔗 ABOUT TO CALL bindEvents() 🔗🔗🔗');
            log(LOG_LEVELS.INFO, 'About to call bindEvents()');
            this.bindEvents();
            console.log('✅✅✅ bindEvents() COMPLETED SUCCESSFULLY ✅✅✅');
            log(LOG_LEVELS.INFO, 'bindEvents() completed successfully');
        }

        /**
         * Lie les événements pour tous les modals
         */
        bindEvents() {
            console.log('🔗🔗🔗 BINDEVENTS CALLED - SETTING UP EVENT LISTENERS 🔗🔗🔗');
            log(LOG_LEVELS.DEBUG, 'Binding modal events...');

            // Boutons de configuration (pour ouvrir les modals)
            document.addEventListener('click', (e) => {
                console.log('🖱️ DOCUMENT CLICK DETECTED:', e.target.tagName, e.target.className);
                log(LOG_LEVELS.DEBUG, 'Document click detected', {
                    target: e.target.className,
                    tagName: e.target.tagName,
                    targetId: e.target.id,
                    targetText: e.target.textContent ? e.target.textContent.substring(0, 20) : '',
                    eventPhase: e.eventPhase,
                    isTrusted: e.isTrusted
                });
                const configBtn = e.target.closest('.canvas-configure-btn');
                if (configBtn) {
                    console.log('🎯🎯🎯 CONFIGURE BUTTON FOUND AND CLICKED! 🎯🎯🎯');
                    console.log('Button details:', configBtn.className, configBtn.id, configBtn.textContent);
                    log(LOG_LEVELS.INFO, 'Configure button FOUND and clicked!', {
                        button: configBtn,
                        className: configBtn.className,
                        buttonId: configBtn.id,
                        buttonText: configBtn.textContent,
                        parentElement: configBtn.parentElement,
                        parentClass: configBtn.parentElement ? configBtn.parentElement.className : 'no parent'
                    });
                    e.preventDefault();
                    console.log('🔄 CALLING handleConfigureButtonClick...');
                    log(LOG_LEVELS.INFO, 'Calling handleConfigureButtonClick...');
                    this.handleConfigureButtonClick(configBtn);
                } else {
                    console.log('❌ No configure button found in click target');
                    log(LOG_LEVELS.DEBUG, 'No configure button found in click target');
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

            log(LOG_LEVELS.INFO, 'All event listeners attached successfully');
            log(LOG_LEVELS.DEBUG, 'Modal events bound successfully');
        }

        /**
         * Gère le clic sur un bouton de configuration
         */
        handleConfigureButtonClick(button) {
            console.log('🎯🎯🎯 handleConfigureButtonClick STARTED 🎯🎯🎯');
            console.log('Button received:', button.className, button.id, button.textContent);
            log(LOG_LEVELS.INFO, 'handleConfigureButtonClick STARTED', {
                button: button,
                buttonClass: button.className,
                buttonTag: button.tagName,
                buttonId: button.id,
                buttonParent: button.parentElement,
                buttonParentClass: button.parentElement ? button.parentElement.className : 'no parent',
                allButtonsInDOM: document.querySelectorAll('.canvas-configure-btn').length,
                allButtons: Array.from(document.querySelectorAll('.canvas-configure-btn')).map(btn => ({
                    element: btn,
                    className: btn.className,
                    parentElement: btn.parentElement,
                    parentClass: btn.parentElement ? btn.parentElement.className : 'no parent'
                }))
            });

            console.log('🔍 STEP 1: Looking for parent card...');
            log(LOG_LEVELS.INFO, 'Step 1: Looking for parent card...');
            const card = button.closest('.canvas-card');
            console.log('🔍 STEP 1 RESULT:', card ? 'CARD FOUND' : 'NO CARD FOUND');
            console.log('Card details:', card ? card.className : 'no card');
            log(LOG_LEVELS.INFO, 'Step 1 result:', {
                cardFound: !!card,
                card: card,
                cardClass: card ? card.className : 'no card found',
                cardTag: card ? card.tagName : 'no card found'
            });
            if (!card) {
                log(LOG_LEVELS.ERROR, 'Configure button clicked but no parent card found', {
                    button: button,
                    allCardsInDOM: document.querySelectorAll('.canvas-card').length,
                    allCards: Array.from(document.querySelectorAll('.canvas-card')).map(card => ({
                        element: card,
                        className: card.className,
                        dataCategory: card.getAttribute('data-category'),
                        innerHTML: card.innerHTML.substring(0, 100) + '...'
                    }))
                });
                return;
            }

            log(LOG_LEVELS.INFO, 'Parent card found', {
                card: card,
                cardClass: card.className,
                cardTag: card.tagName,
                cardDataCategory: card.getAttribute('data-category'),
                cardChildren: Array.from(card.children).map(child => ({
                    tag: child.tagName,
                    class: child.className,
                    id: child.id,
                    textContent: child.textContent ? child.textContent.substring(0, 50) : ''
                })),
                cardInnerHTML: card.innerHTML.substring(0, 200) + '...'
            });

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
            console.log('🚪🚪🚪 openModal STARTED for category:', category, '🚪🚪🚪');
            log(LOG_LEVELS.INFO, 'openModal STARTED', {
                category: category,
                availableModals: Object.keys(this.modals),
                modalData: this.modals[category],
                currentModal: this.currentModal,
                isInitialized: this.isInitialized
            });

            console.log('🔍 STEP 1: Checking modal data...');
            log(LOG_LEVELS.INFO, 'Step 1: Checking modal data...');
            const modalData = this.modals[category];
            if (!modalData) {
                console.log('❌ STEP 1 FAILED: Unknown category', category);
                log(LOG_LEVELS.ERROR, `Cannot open modal: unknown category ${category}`, {
                    availableCategories: Object.keys(this.modals),
                    requestedCategory: category
                });
                return;
            }

            console.log('✅ STEP 1 PASSED: Modal data found for', category);
            log(LOG_LEVELS.INFO, 'Step 1 PASSED: Modal data found', {
                modalId: modalData.id,
                modalElement: modalData.element,
                modalExistsInDOM: !!modalData.element
            });

            console.log('🔍 STEP 2: Getting modal element...');
            log(LOG_LEVELS.INFO, 'Step 2: Getting modal element...');
            const modalElement = modalData.element;
            if (!modalElement) {
                console.log('❌ STEP 2 FAILED: Modal element not found for', category);
                log(LOG_LEVELS.ERROR, `Modal element not found for category: ${category}`);
                return;
            }

            console.log('✅ STEP 2 PASSED: Modal element found');
            log(LOG_LEVELS.INFO, 'Step 2 PASSED: Modal element found', {
                modalElement: modalElement,
                modalId: modalElement.id,
                modalClass: modalElement.className,
                currentDisplay: modalElement.style.display
            });

            log(LOG_LEVELS.INFO, 'Step 3: Closing any open modals...');
            this.closeAllModals();
            log(LOG_LEVELS.INFO, 'Step 3 PASSED: closeAllModals completed');

            log(LOG_LEVELS.INFO, 'Step 4: Setting modal display to flex...');
            modalData.element.style.display = 'flex';
            log(LOG_LEVELS.INFO, 'Step 4 PASSED: Modal display set to flex');

            log(LOG_LEVELS.INFO, 'Step 5: Setting body overflow and current modal...');
            document.body.style.overflow = 'hidden';
            this.currentModal = modalData;
            log(LOG_LEVELS.INFO, 'Step 5 PASSED: Body overflow hidden and current modal set');

            log(LOG_LEVELS.INFO, `Modal ${category} display set to flex`, {
                modalElement: modalData.element,
                newDisplay: modalData.element.style.display,
                bodyOverflow: document.body.style.overflow,
                modalVisibility: modalData.element ? window.getComputedStyle(modalData.element).visibility : 'no element',
                modalOpacity: modalData.element ? window.getComputedStyle(modalData.element).opacity : 'no element'
            });

            log(LOG_LEVELS.INFO, 'Step 6: Checking syncModalValues function...');
            log(LOG_LEVELS.INFO, 'Step 6 PASSED: syncModalValues handled');

            console.log('🎉🎉🎉 MODAL', category, 'OPENED SUCCESSFULLY - ALL STEPS COMPLETED 🎉🎉🎉');
            log(LOG_LEVELS.INFO, `Modal ${category} opened successfully - ALL STEPS COMPLETED`);
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

    // LOG CRITIQUE - Classe complètement définie
    console.log('[CANVAS_MODAL_SAVE] CanvasModalManager class fully defined');

    // Initialisation globale
    $(document).ready(function() {
        console.log('🎯🎯🎯 JQUERY DOCUMENT READY FIRED - DOM LOADED 🎯🎯🎯');
        console.log('[CANVAS_MODAL_SAVE] JQUERY DOCUMENT READY CALLED');
        log(LOG_LEVELS.INFO, 'jQuery document ready fired - DOM is loaded');
        log(LOG_LEVELS.INFO, 'Document ready, initializing Canvas Modal Manager...');

        console.log('🔍 CHECKING JQUERY AVAILABILITY...');
        // Vérifier que jQuery est disponible
        if (typeof $ === 'undefined') {
            console.error('❌❌❌ CRITICAL ERROR: jQuery not available!');
            console.error('[CANVAS_MODAL_SAVE] CRITICAL ERROR: jQuery not available!');
            return;
        }

        console.log('✅ JQUERY AVAILABLE, CHECKING GLOBAL VARIABLES');
        console.log('[CANVAS_MODAL_SAVE] JQUERY AVAILABLE, CHECKING GLOBAL VARIABLES');

        // Vérifier que les variables globales sont disponibles
        if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
            log(LOG_LEVELS.ERROR, 'CRITICAL ERROR: window.pdfBuilderCanvasSettings not defined! Script localization failed.');
            return;
        }

        log(LOG_LEVELS.INFO, 'window.pdfBuilderCanvasSettings is defined', {
            hasAjaxUrl: !!window.pdfBuilderCanvasSettings.ajax_url,
            hasNonce: !!window.pdfBuilderCanvasSettings.nonce,
            settingsKeys: Object.keys(window.pdfBuilderCanvasSettings)
        });

        // Vérifier qu'on est sur la bonne page
        const currentUrl = window.location.href;
        const isSettingsPage = currentUrl.includes('pdf-builder-settings') && currentUrl.includes('tab=contenu');
        log(LOG_LEVELS.INFO, 'Page check:', {
            currentUrl: currentUrl,
            isSettingsPage: isSettingsPage,
            hasTabContenu: currentUrl.includes('tab=contenu'),
            hasPdfBuilderSettings: currentUrl.includes('pdf-builder-settings')
        });

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

        log(LOG_LEVELS.INFO, 'All DOM checks passed - proceeding with initialization');

        // Créer et initialiser le gestionnaire de modals
        try {
            log(LOG_LEVELS.INFO, 'About to create CanvasModalManager instance');
            window.canvasModalManager = new CanvasModalManager();
            log(LOG_LEVELS.INFO, 'CanvasModalManager instance created, calling init()');
            window.canvasModalManager.init();
            log(LOG_LEVELS.INFO, 'Canvas Modal System ready');
        } catch (error) {
            log(LOG_LEVELS.ERROR, 'CRITICAL ERROR: Failed to initialize CanvasModalManager:', error);
            console.error('[CANVAS_MODAL_SAVE] Exception during initialization:', error);
        }
    });

})(jQuery);

