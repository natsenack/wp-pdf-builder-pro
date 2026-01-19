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
            // Boutons de configuration (pour ouvrir les modals)
            document.addEventListener('click', (e) => {
                const configBtn = e.target.closest('.canvas-configure-btn');
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
            const card = button.closest('.canvas-card');
            if (!card) {
                console.error('[CANVAS_MODAL] No parent card found for button');
                return;
            }

            const category = card.getAttribute('data-category');
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
                
                return;
            }

            const category = button.getAttribute('data-category');
            if (!category) {
                
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
    $(document).ready(function() {
        // Vérifications préalables
        if (typeof $ === 'undefined') {
            
            return;
        }

        if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
            
            return;
        }

        // Vérifier la page actuelle
        const currentUrl = window.location.href;
        if (!currentUrl.includes('page=pdf-builder-settings') || !currentUrl.includes('tab=contenu')) {
            return; // Pas sur la page des paramètres
        }

        // Vérifier les éléments DOM critiques
        const modalIds = [
            'canvas-affichage-modal-overlay',
            'canvas-navigation-modal-overlay',
            'canvas-comportement-modal-overlay',
            'canvas-systeme-modal-overlay'
        ];

        const missingModals = modalIds.filter(id => !document.getElementById(id));
        if (missingModals.length > 0) {
            
            return;
        }

        // Vérifier que les boutons de configuration existent
        const configButtons = document.querySelectorAll('.canvas-configure-btn');
        if (configButtons.length === 0) {
            
            return;
        }

        // Créer et initialiser le gestionnaire de modals
        try {
            window.canvasModalManager = new CanvasModalManager();
            window.canvasModalManager.init();
        } catch (error) {
            
        }
    });

})(jQuery);
            
            const modalElement = modalData.element;
            if (!modalElement) {
                console.log('❌ STEP 2 FAILED: Modal element not found for', category);
                
                return;
            }

            console.log('✅ STEP 2 PASSED: Modal element found');
            log(LOG_LEVELS.ERROR, 'Step 2 PASSED: Modal element found', {
                modalElement: modalElement,
                modalId: modalElement.id,
                modalClass: modalElement.className,
                currentDisplay: modalElement.style.display
            });

            
            this.closeAllModals();
            

            
            modalData.element.style.display = 'flex';
            

            
            document.body.style.overflow = 'hidden';
            this.currentModal = modalData;
            

            log(LOG_LEVELS.ERROR, `Modal ${category} display set to flex`, {
                modalElement: modalData.element,
                newDisplay: modalData.element.style.display,
                bodyOverflow: document.body.style.overflow,
                modalVisibility: modalData.element ? window.getComputedStyle(modalData.element).visibility : 'no element',
                modalOpacity: modalData.element ? window.getComputedStyle(modalData.element).opacity : 'no element'
            });

            
            

            console.log('🎉🎉🎉 MODAL', category, 'OPENED SUCCESSFULLY - ALL STEPS COMPLETED 🎉🎉🎉');
            
        }

        /**
         * Ferme un modal spécifique
         */
        closeModal(modalElement) {
            if (!modalElement) return;

            const category = this.getModalCategory(modalElement);
            

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
            

            // Cette fonction peut être étendue pour synchroniser les valeurs
            // Pour l'instant, on suppose que les valeurs sont déjà correctes
        }

        /**
         * Sauvegarde les paramètres d'un modal
         */
        async saveModalSettings(modalElement, category) {
            

            try {
                // Collecter les données du formulaire
                const formData = this.collectModalData(modalElement, category);

                log(LOG_LEVELS.ERROR, `Collected form data for ${category}:`, {
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

                

                if (response.success) {
                    

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

    // LOG CRITIQUE - Classe complètement définie
    console.log('[CANVAS_MODAL_SAVE] CanvasModalManager class fully defined');

    // Initialisation globale
    $(document).ready(function() {
        console.log('🎯🎯🎯 JQUERY DOCUMENT READY FIRED - DOM LOADED 🎯🎯🎯');
        console.log('[CANVAS_MODAL_SAVE] JQUERY DOCUMENT READY CALLED');
        
        

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
            
            return;
        }

        log(LOG_LEVELS.ERROR, 'window.pdfBuilderCanvasSettings is defined', {
            hasAjaxUrl: !!window.pdfBuilderCanvasSettings.ajax_url,
            hasNonce: !!window.pdfBuilderCanvasSettings.nonce,
            settingsKeys: Object.keys(window.pdfBuilderCanvasSettings)
        });

        // Vérifier qu'on est sur la bonne page
        const currentUrl = window.location.href;
        const isSettingsPage = currentUrl.includes('pdf-builder-settings') && currentUrl.includes('tab=contenu');
        log(LOG_LEVELS.ERROR, 'Page check:', {
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
            
            return;
        }

        // Vérifier que les boutons de configuration existent
        const configButtons = document.querySelectorAll('.canvas-configure-btn');
        if (configButtons.length === 0) {
            
            return;
        }

        

        

        // Créer et initialiser le gestionnaire de modals
        try {
            
            window.canvasModalManager = new CanvasModalManager();
            
            window.canvasModalManager.init();
            
        } catch (error) {
            
            console.error('[CANVAS_MODAL_SAVE] Exception during initialization:', error);
        }
    });

})(jQuery);

