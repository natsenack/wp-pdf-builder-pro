/**
 * JavaScript pour le système d'onboarding de PDF Builder Pro
 * Version améliorée avec UX/UI avancées
 */

(function($) {
    'use strict';

    class PDFBuilderOnboarding {
        constructor() {
            this.currentStep = 1;
            this.selectedTemplate = null;
            this.startTime = Date.now();
            this.interactions = [];
            this.tooltips = {};
            this.stepCache = {}; // Cache pour les étapes chargées
            this.eventsBound = false; // Pour éviter la liaison multiple des événements
            this.init();
        }

        init() {
            console.log('PDF Builder Onboarding: Initializing...');
            this.bindEvents();
            this.initializeWizard();
            this.setupKeyboardNavigation();
            this.setupAutoSave();
            this.trackAnalytics('onboarding_started');
        }

        bindEvents() {
            // Éviter la liaison multiple des événements
            if (this.eventsBound) {
                console.log('PDF Builder Onboarding: Events already bound, skipping');
                return;
            }
            this.eventsBound = true;

            console.log('PDF Builder Onboarding: Binding events (first time only)');
            // Événements des boutons principaux
            $(document).on('click', '.complete-step', (e) => {
                console.log('PDF Builder Onboarding: Complete step button clicked');
                const $button = $(e.currentTarget);
                const step = $button.data('step');
                const actionType = $button.data('action-type');
                const isDisabled = $button.prop('disabled');

                console.log(`PDF Builder Onboarding: Button details - step: ${step}, actionType: ${actionType}, disabled: ${isDisabled}, currentStep: ${this.currentStep}`);

                if (isDisabled) {
                    console.log('PDF Builder Onboarding: Button is disabled, preventing action');
                    e.preventDefault();
                    return;
                }

                // Vérifier si le bouton appartient à l'étape courante
                if (step !== this.currentStep) {
                    console.log(`PDF Builder Onboarding: Button from step ${step} clicked but current step is ${this.currentStep}, ignoring`);
                    e.preventDefault();
                    return;
                }

                this.completeStep(step, actionType);
            });

            // Bouton pour ignorer l'étape courante
            $(document).on('click', '[data-action="skip-step"]', (e) => {
                e.preventDefault();
                this.skipCurrentStep();
            });

            // Bouton pour ignorer complètement l'onboarding
            $(document).on('click', '[data-action="skip-onboarding"]', (e) => {
                e.preventDefault();
                this.skipOnboarding();
            });

            // Bouton précédent
            $(document).on('click', '.button-previous', (e) => {
                e.preventDefault();
                this.goToPreviousStep();
            });

            // Sélection de template
            $(document).on('click', '.template-card', (e) => {
                e.preventDefault();
                this.selectTemplate($(e.currentTarget));
            });

            // Bouton pour sauter la configuration WooCommerce
            $(document).on('click', '.skip-woocommerce', (e) => {
                e.preventDefault();
                this.skipWoocommerceSetup();
            });

            // Nouveaux événements pour l'UX améliorée
            $(document).on('mouseenter', '[data-tooltip]', (e) => {
                this.showTooltip($(e.currentTarget));
            });

            $(document).on('mouseleave', '[data-tooltip]', (e) => {
                this.hideTooltip();
            });

            $(document).on('click', '.onboarding-help-btn', (e) => {
                this.showHelpModal();
            });

            $(document).on('input', '.onboarding-input', (e) => {
                this.validateInput($(e.currentTarget));
            });
        }

        initializeWizard() {
            console.log('PDF Builder Onboarding: Initializing wizard...');
            console.log('PDF Builder Onboarding: pdfBuilderOnboarding exists:', typeof pdfBuilderOnboarding !== 'undefined');
            if (typeof pdfBuilderOnboarding !== 'undefined') {
                console.log('PDF Builder Onboarding: pdfBuilderOnboarding.current_step:', pdfBuilderOnboarding.current_step);
            }

            // Vérifier si une étape spécifique est demandée via l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const forcedStep = urlParams.get('pdf_onboarding_step');

            // Initialiser l'état du wizard
            this.currentStep = forcedStep ? parseInt(forcedStep) : (typeof pdfBuilderOnboarding !== 'undefined' ? pdfBuilderOnboarding.current_step || 1 : 1);
            this.selectedTemplate = typeof pdfBuilderOnboarding !== 'undefined' ? pdfBuilderOnboarding.selected_template || null : null;
            console.log('PDF Builder Onboarding: Current step set to', this.currentStep);
            console.log('PDF Builder Onboarding: Selected template:', this.selectedTemplate);

            // S'assurer que tous les boutons sont dans un état cohérent
            this.resetButtonStates();

            // Charger l'étape actuelle via AJAX pour s'assurer que les boutons sont corrects
            this.loadStep(this.currentStep);
        }

        /**
         * Vérifier si l'étape actuelle doit avancer automatiquement
         */
        checkAutoAdvance() {
            // Cette fonction est maintenant gérée dans loadStep pour toutes les étapes
            // Rien à faire ici pour l'initialisation
        }

        setupKeyboardNavigation() {
            $(document).on('keydown', (e) => {
                if ($('#pdf-builder-onboarding-modal').css('display') === 'none') return;

                switch(e.key) {
                    case 'ArrowRight':
                    case 'ArrowDown':
                        e.preventDefault();
                        this.navigateStep('next');
                        break;
                    case 'ArrowLeft':
                    case 'ArrowUp':
                        e.preventDefault();
                        this.navigateStep('prev');
                        break;
                    case 'Enter':
                        if (!$(e.target).is('input, textarea, select')) {
                            e.preventDefault();
                            this.completeCurrentStep();
                        }
                        break;
                    case 'Escape':
                        e.preventDefault();
                        this.showExitConfirmation();
                        break;
                    case 'h':
                    case 'H':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            this.showHelpModal();
                        }
                        break;
                }
            });
        }

        setupAutoSave() {
            // Sauvegarde automatique de la progression toutes les 30 secondes
            setInterval(() => {
                this.autoSaveProgress();
            }, 30000);

            // Sauvegarde avant de quitter la page
            $(window).on('beforeunload', () => {
                this.autoSaveProgress();
            });
        }

        navigateStep(direction) {
            const totalSteps = 5;
            let newStep = this.currentStep;

            if (direction === 'next' && this.currentStep < totalSteps) {
                newStep = this.currentStep + 1;
            } else if (direction === 'prev' && this.currentStep > 1) {
                newStep = this.currentStep - 1;
            }

            if (newStep !== this.currentStep) {
                this.goToStep(newStep);
                this.trackAnalytics('step_navigation', { from: this.currentStep, to: newStep, method: 'keyboard' });
            }
        }

        completeCurrentStep() {
            const $currentBtn = $(`.complete-step[data-step="${this.currentStep}"]`);
            if ($currentBtn.length && !$currentBtn.prop('disabled')) {
                $currentBtn.click();
            }
        }

        trackAnalytics(event, data = {}) {
            // Suivre les événements d'analyse (optionnel)
            console.log('PDF Builder Onboarding: Analytics event:', event, data);
            
            // Ici, vous pourriez envoyer les données à Google Analytics, etc.
            // Pour l'instant, juste logger
            this.interactions.push({
                event: event,
                data: data,
                timestamp: Date.now()
            });
        }

        autoSaveProgress() {
            // Sauvegarde automatique de la progression
            const progressData = {
                currentStep: this.currentStep,
                selectedTemplate: this.selectedTemplate,
                interactions: this.interactions,
                timeSpent: Date.now() - this.startTime
            };
            
            // Sauvegarder via AJAX
            $.ajax({
                url: pdfBuilderOnboarding.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_auto_save_progress',
                    progress_data: JSON.stringify(progressData),
                    nonce: pdfBuilderOnboarding.nonce
                },
                success: (response) => {
                    if (response.success) {
                        console.log('PDF Builder Onboarding: Progress auto-saved');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('PDF Builder Onboarding: Auto-save failed:', error);
                }
            });
        }

        goToStep(stepNumber) {
            // Aller à une étape spécifique
            if (stepNumber >= 1 && stepNumber <= 4) {
                this.loadStep(stepNumber);
            }
        }

        skipOnboarding() {
            // Sauter l'onboarding
            if (confirm('Êtes-vous sûr de vouloir sauter l\'assistant de configuration ? Vous pourrez le relancer plus tard depuis les paramètres.')) {
                this.markOnboardingComplete();
                this.hideModal();
            }
        }

        skipWoocommerceSetup() {
            // Sauter la configuration WooCommerce
            console.log('PDF Builder Onboarding: Skipping WooCommerce setup');
            this.completeStep();
        }

        showTooltip($element) {
            // Afficher une info-bulle
            const tooltipText = $element.data('tooltip');
            if (tooltipText) {
                // Créer et afficher l'info-bulle
                const $tooltip = $('<div class="onboarding-tooltip"></div>').text(tooltipText);
                $('body').append($tooltip);
                
                const offset = $element.offset();
                $tooltip.css({
                    top: offset.top - $tooltip.outerHeight() - 10,
                    left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
                });
                
                this.tooltips[$element.attr('id') || $element.attr('class')] = $tooltip;
            }
        }

        hideTooltip() {
            // Cacher toutes les info-bulles
            $('.onboarding-tooltip').remove();
            this.tooltips = {};
        }

        showHelpModal() {
            // Afficher le modal d'aide
            const helpContent = `
                <div class="onboarding-help-modal">
                    <h3>Aide - Assistant de configuration</h3>
                    <p>Utilisez les flèches gauche/droite pour naviguer entre les étapes.</p>
                    <p>Appuyez sur Entrée pour valider une étape.</p>
                    <p>Appuyez sur Échap pour quitter l'assistant.</p>
                    <p>Ctrl+H pour afficher cette aide.</p>
                    <button class="button" onclick="$(this).closest('.onboarding-help-modal').remove()">Fermer</button>
                </div>
            `;
            $('body').append(helpContent);
        }

        validateInput($input) {
            // Valider un champ de saisie
            const value = $input.val();
            const validationType = $input.data('validation');
            
            let isValid = true;
            let errorMessage = '';
            
            switch(validationType) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    isValid = emailRegex.test(value);
                    errorMessage = isValid ? '' : 'Veuillez entrer une adresse email valide.';
                    break;
                case 'required':
                    isValid = value.trim() !== '';
                    errorMessage = isValid ? '' : 'Ce champ est obligatoire.';
                    break;
                // Ajouter d'autres types de validation si nécessaire
            }
            
            // Afficher ou masquer le message d'erreur
            const $errorElement = $input.siblings('.validation-error');
            if (!isValid) {
                if ($errorElement.length === 0) {
                    $input.after(`<div class="validation-error">${errorMessage}</div>`);
                } else {
                    $errorElement.text(errorMessage);
                }
                $input.addClass('invalid');
            } else {
                $errorElement.remove();
                $input.removeClass('invalid');
            }
            
            return isValid;
        }

        getWoocommerceOptions() {
            // Récupérer les options WooCommerce sélectionnées
            const options = {};
            $('.woocommerce-setup input[type="checkbox"]').each(function() {
                options[$(this).attr('name')] = $(this).is(':checked');
            });
            return options;
        }

        showCompletionMessage() {
            // Afficher un message de completion et fermer le modal
            alert('Configuration terminée ! Vous pouvez maintenant utiliser PDF Builder Pro.');
            this.hideModal();
            this.markOnboardingComplete();
        }

        showExitConfirmation() {
            if (confirm('Êtes-vous sûr de vouloir quitter l\'assistant de configuration ?\n\nVotre progression sera sauvegardée.')) {
                this.hideModal();
                this.trackAnalytics('onboarding_exited', { step: this.currentStep, method: 'escape' });
            }
        }

        showModal() {
            console.log('PDF Builder Onboarding: Showing modal...');
            const $modal = $('#pdf-builder-onboarding-modal');
            console.log('PDF Builder Onboarding: Modal element found:', $modal.length);
            console.log('PDF Builder Onboarding: Modal current display:', $modal.css('display'));
            console.log('PDF Builder Onboarding: Modal HTML:', $modal.prop('outerHTML').substring(0, 200) + '...');
            // Le modal est déjà affiché via CSS, juste ajouter l'animation
            const self = this; // Sauvegarder la référence this
            $modal.fadeIn(400, () => {
                console.log('PDF Builder Onboarding: Modal fadeIn complete');
                console.log('PDF Builder Onboarding: Modal display after fadeIn:', $modal.css('display'));
                console.log('PDF Builder Onboarding: Modal visibility:', $modal.css('visibility'));
                console.log('PDF Builder Onboarding: Modal opacity:', $modal.css('opacity'));
                console.log('PDF Builder Onboarding: Modal z-index:', $modal.css('z-index'));
                // Animation d'entrée améliorée
                $modal.find('.modal-content').addClass('modal-entrance-animation');
                self.announceStep();
                self.focusFirstElement();
            });

            // Overlay click to close (avec confirmation)
            $modal.on('click', (e) => {
                if (e.target === $modal[0]) {
                    this.showExitConfirmation();
                }
            });
        }

        announceStep() {
            // Annonce pour les lecteurs d'écran
            const stepTitle = $(`.onboarding-step-content[data-step-id] h2`).text();
            if (stepTitle) {
                this.announceToScreenReader(`Étape ${this.currentStep} sur 4: ${stepTitle}`);
            } else {
                this.announceToScreenReader(`Étape ${this.currentStep} sur 4`);
            }
        }

        announceToScreenReader(message) {
            // Créer un élément temporaire pour les annonces d'accessibilité
            const announcement = document.createElement('div');
            announcement.setAttribute('aria-live', 'polite');
            announcement.setAttribute('aria-atomic', 'true');
            announcement.style.position = 'absolute';
            announcement.style.left = '-10000px';
            announcement.style.width = '1px';
            announcement.style.height = '1px';
            announcement.style.overflow = 'hidden';
            announcement.textContent = message;
            document.body.appendChild(announcement);
            // Supprimer après un délai
            setTimeout(() => {
                document.body.removeChild(announcement);
            }, 1000);
        }

        focusFirstElement() {
            // Mettre le focus sur le premier élément focusable de l'étape actuelle
            const $stepContent = $(`.onboarding-step-content[data-step-id="${this.currentStep}"]`);
            const $focusableElements = $stepContent.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if ($focusableElements.length > 0) {
                $focusableElements.first().focus();
            } else {
                // Si aucun élément focusable, mettre le focus sur le titre de l'étape
                $stepContent.find('h2').attr('tabindex', '-1').focus();
            }
        }

        showExitConfirmation() {
            // Afficher une confirmation avant de quitter l'onboarding
            if (confirm('Êtes-vous sûr de vouloir quitter l\'assistant de configuration ? Votre progression sera perdue.')) {
                this.hideModal();
                // Marquer l'onboarding comme terminé pour éviter de le réafficher
                this.markOnboardingComplete();
            }
        }

        markOnboardingComplete() {
            // Marquer l'onboarding comme terminé via AJAX
            $.ajax({
                url: pdfBuilderOnboarding.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_mark_onboarding_complete',
                    nonce: pdfBuilderOnboarding.nonce
                },
                success: (response) => {
                    if (response.success) {
                        console.log('PDF Builder Onboarding: Onboarding marked as complete');
                    } else {
                        console.error('PDF Builder Onboarding: Failed to mark onboarding complete:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('PDF Builder Onboarding: AJAX error marking onboarding complete:', error);
                }
            });
        }

        hideModal() {
            // Cacher le modal avec animation
            const $modal = $('#pdf-builder-onboarding-modal');
            $modal.fadeOut(300, () => {
                // Supprimer l'animation d'entrée
                $modal.find('.modal-content').removeClass('modal-entrance-animation');
                // Restaurer le focus à l'élément qui avait le focus avant
                if (this.previousFocus) {
                    this.previousFocus.focus();
                }
            });
        }

        updateProgress() {
            // Mettre à jour l'indicateur de progression
            const $progressIndicator = $('.onboarding-progress-indicator');
            if ($progressIndicator.length > 0) {
                // Calculer le pourcentage de progression (étape actuelle / nombre total d'étapes)
                const progressPercent = (this.currentStep / 4) * 100;
                $progressIndicator.css('width', progressPercent + '%');
                $progressIndicator.attr('aria-valuenow', this.currentStep);
                $progressIndicator.attr('aria-valuemax', 4);
            }
            // Mettre à jour le texte de progression
            const $progressText = $('.onboarding-progress-text');
            if ($progressText.length > 0) {
                $progressText.text(`Étape ${this.currentStep} sur 4`);
            }
        }

        selectTemplate(templateId) {
            // Sauvegarder le template sélectionné
            this.selectedTemplate = templateId;
            console.log('PDF Builder Onboarding: Template selected:', templateId);
            
            // Mettre à jour l'interface utilisateur
            $('.template-option').removeClass('selected');
            $(`.template-option[data-template-id="${templateId}"]`).addClass('selected');
            
            // Activer le bouton suivant si un template est sélectionné
            const $nextButton = $('.button-next');
            if ($nextButton.length > 0) {
                $nextButton.prop('disabled', false);
            }
        }

        completeStep() {
            // Sauvegarder les données de l'étape actuelle si nécessaire
            if (this.currentStep === 2 && this.selectedTemplate) {
                // Sauvegarder la sélection du template
                this.saveTemplateSelection();
            }
            
            // Passer à l'étape suivante
            if (this.currentStep < 4) {
                this.loadStep(this.currentStep + 1);
            } else {
                // Onboarding terminé
                this.finishOnboarding();
            }
        }

        saveTemplateSelection() {
            // Sauvegarder la sélection du template via AJAX
            $.ajax({
                url: pdfBuilderOnboarding.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_save_template_selection',
                    template_id: this.selectedTemplate,
                    nonce: pdfBuilderOnboarding.nonce
                },
                success: (response) => {
                    if (response.success) {
                        console.log('PDF Builder Onboarding: Template selection saved successfully');
                    } else {
                        console.error('PDF Builder Onboarding: Failed to save template selection:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('PDF Builder Onboarding: AJAX error saving template selection:', error);
                }
            });
        }

        finishOnboarding() {
            // Finaliser l'onboarding
            console.log('PDF Builder Onboarding: Finishing onboarding...');
            
            // Marquer comme terminé
            this.markOnboardingComplete();
            
            // Cacher le modal
            this.hideModal();
            
            // Rediriger ou afficher un message de succès
            // Pour l'instant, juste afficher un message
            alert('Configuration terminée ! Vous pouvez maintenant utiliser PDF Builder Pro.');
        }

        goToPreviousStep() {
            const prevStep = this.currentStep - 1;
            if (prevStep >= 1) {
                this.loadStep(prevStep);
            }
        }

        loadStep(stepNumber) {
            // Charger une étape spécifique via AJAX
            console.log('PDF Builder Onboarding: Loading step', stepNumber);

            $.ajax({
                url: pdfBuilderOnboarding.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_load_onboarding_step',
                    step: stepNumber,
                    nonce: pdfBuilderOnboarding.nonce
                },
                success: (response) => {
                    if (response.success) {
                        console.log('PDF Builder Onboarding: Step loaded successfully');
                        console.log('PDF Builder Onboarding: Response data content length:', response.data.content ? response.data.content.length : 'no content');
                        this.applyStepData(stepNumber, response.data);
                    } else {
                        console.error('PDF Builder Onboarding: Failed to load step:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('PDF Builder Onboarding: AJAX error loading step:', error);
                }
            });
        }

        /**
         * Appliquer les données d'une étape chargée
         */
        applyStepData(step, data) {
            console.log('PDF Builder Onboarding: Applying step data for step', step);
            const $modal = $('#pdf-builder-onboarding-modal');
            const $content = $modal.find('.modal-body .step-content');

            // Vérifier les boutons existants avant remplacement
            const existingButtons = $content.find('.complete-step');
            console.log('PDF Builder Onboarding: Existing buttons before replacement:', existingButtons.length);

            // Vérifier aussi tous les boutons dans la modal
            const allButtonsInModal = $modal.find('.complete-step');
            console.log('PDF Builder Onboarding: All buttons in modal before replacement:', allButtonsInModal.length);

            // Mettre à jour le contenu de la modal
            $content.html(data.content);

            // Vérifier les boutons après remplacement
            const newButtons = $content.find('.complete-step');
            console.log('PDF Builder Onboarding: New buttons after replacement:', newButtons.length);

            // Vérifier aussi tous les boutons dans la modal après
            const allButtonsInModalAfter = $modal.find('.complete-step');
            console.log('PDF Builder Onboarding: All buttons in modal after replacement:', allButtonsInModalAfter.length);

            // Mettre à jour l'étape courante
            this.currentStep = step;

            // Mettre à jour la progression
            this.updateProgress();

            // Mettre à jour l'URL sans recharger la page
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('pdf_onboarding_step', step);
            window.history.replaceState({}, '', currentUrl.toString());

            // Mettre à jour la visibilité du bouton précédent
            const $prevButton = $('.button-previous');
            if (step > 1) {
                if ($prevButton.length === 0) {
                    // Créer le bouton précédent s'il n'existe pas
                    const $header = $('.modal-header');
                    $header.prepend(`
                        <button class="button button-previous" data-tooltip="Étape précédente">
                            <span class="dashicons dashicons-arrow-left-alt"></span>
                        </button>
                    `);
                } else {
                    // S'assurer que le bouton est visible et activé
                    $prevButton.show().prop('disabled', false);
                }
            } else {
                // Masquer le bouton précédent pour la première étape
                $prevButton.hide();
            }

            // Réactiver les boutons de navigation
            $('.button-previous, .complete-step').prop('disabled', false);

            // Animation d'entrée pour le nouveau contenu
            $content.find('.onboarding-step-content').hide().fadeIn(300);

            // Mettre à jour le texte du bouton principal si nécessaire
            if (data.action) {
                $('.complete-step').text(data.action);
                // Pour l'étape 2, désactiver le bouton tant qu'aucun template n'est sélectionné
                if (step === 2) {
                    $('.complete-step').prop('disabled', !this.selectedTemplate);
                    // Restaurer la sélection visuelle si un template est déjà sélectionné
                    if (this.selectedTemplate) {
                        $(`.template-card[data-template="${this.selectedTemplate}"]`).addClass('selected');
                        $('.complete-step').text('Commencer');
                    }
                }
            }

            // Gérer les étapes automatiques
            if (data.auto_advance) {
                setTimeout(() => {
                    this.completeStep(step);
                }, data.auto_advance_delay || 3000);
            }

            // Tracker l'événement
            this.trackAnalytics('step_loaded', { step: step });

            // Annoncer l'étape pour l'accessibilité
            this.announceStep();
        }

        announceToScreenReader(message) {
            // Créer un élément temporaire pour l'annonce
            const $announcer = $('<div>')
                .attr('aria-live', 'polite')
                .attr('aria-atomic', 'true')
                .css({
                    position: 'absolute',
                    left: '-10000px',
                    width: '1px',
                    height: '1px',
                    overflow: 'hidden'
                })
                .text(message);

            $('body').append($announcer);
            setTimeout(() => $announcer.remove(), 1000);
        }

        focusFirstElement() {
            // Focus sur le premier élément focusable
            const $modal = $('#pdf-builder-onboarding-modal');
            const $focusable = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').first();
            if ($focusable.length) {
                $focusable.focus();
            }
        }

        showTooltip($element) {
            const tooltipText = $element.data('tooltip');
            if (!tooltipText) return;

            // Supprimer les tooltips existants
            $('.onboarding-tooltip').remove();

            const $tooltip = $('<div>')
                .addClass('onboarding-tooltip')
                .text(tooltipText)
                .css({
                    position: 'fixed',
                    background: 'rgba(0, 0, 0, 0.8)',
                    color: 'white',
                    padding: '8px 12px',
                    borderRadius: '4px',
                    fontSize: '14px',
                    zIndex: 10000,
                    pointerEvents: 'none',
                    maxWidth: '200px',
                    textAlign: 'center'
                });

            $('body').append($tooltip);

            // Positionner le tooltip
            const elementRect = $element[0].getBoundingClientRect();
            const tooltipRect = $tooltip[0].getBoundingClientRect();

            $tooltip.css({
                left: elementRect.left + (elementRect.width / 2) - (tooltipRect.width / 2),
                top: elementRect.top - tooltipRect.height - 8
            });

            // Animation d'entrée
            $tooltip.fadeIn(200);
        }

        hideTooltip() {
            $('.onboarding-tooltip').fadeOut(200, function() {
                $(this).remove();
            });
        }

        showHelpModal() {
            const helpContent = `
                <div class="onboarding-help-modal" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);z-index:10001;max-width:400px;width:90%;">
                    <h3 style="margin-top:0;color:#1f2937;">🆘 Aide - Raccourcis Clavier</h3>
                    <ul style="list-style:none;padding:0;">
                        <li><kbd>→</kbd> <kbd>↓</kbd> Étape suivante</li>
                        <li><kbd>←</kbd> <kbd>↑</kbd> Étape précédente</li>
                        <li><kbd>Entrée</kbd> Valider l'étape</li>
                        <li><kbd>Échap</kbd> Quitter (avec confirmation)</li>
                        <li><kbd>Ctrl+H</kbd> Afficher cette aide</li>
                    </ul>
                    <button onclick="$(this).parent().fadeOut(200,function(){$(this).remove();})" style="background:#007cba;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Fermer</button>
                </div>
            `;
            $('body').append(helpContent);
            this.trackAnalytics('help_opened');
        }

        validateInput($input) {
            const value = $input.val();
            const isValid = value && value.length > 0;

            $input.toggleClass('input-valid', isValid);
            $input.toggleClass('input-invalid', !isValid && $input[0].value !== '');

            // Feedback visuel
            if (isValid) {
                this.showValidationFeedback($input, 'success', '✓ Valide');
            } else if ($input[0].value !== '') {
                this.showValidationFeedback($input, 'error', '⚠ Champ requis');
            }
        }

        showValidationFeedback($input, type, message) {
            // Supprimer les anciens feedbacks
            $input.next('.validation-feedback').remove();

            const $feedback = $('<span>')
                .addClass('validation-feedback')
                .addClass(type === 'success' ? 'feedback-success' : 'feedback-error')
                .text(message)
                .css({
                    fontSize: '12px',
                    marginLeft: '8px',
                    fontWeight: '500'
                });

            $input.after($feedback);

            // Animation
            $feedback.fadeIn(200);
        }

        autoSaveProgress() {
            const progress = {
                currentStep: this.currentStep,
                selectedTemplate: this.selectedTemplate,
                timestamp: Date.now(),
                interactions: this.interactions.length
            };

            // Sauvegarde en localStorage comme fallback
            try {
                localStorage.setItem('pdf_builder_onboarding_progress', JSON.stringify(progress));
            } catch (e) {
                // localStorage non disponible
            }

            this.trackAnalytics('progress_auto_saved', progress);
        }

        trackAnalytics(event, data = {}) {
            // Ajouter à la liste des interactions
            this.interactions.push({
                event: event,
                timestamp: Date.now(),
                step: this.currentStep,
                data: data
            });

            // Limiter à 100 interactions maximum
            if (this.interactions.length > 100) {
                this.interactions = this.interactions.slice(-100);
            }

            // Envoyer à un service d'analytics si disponible
            if (typeof gtag !== 'undefined') {
                gtag('event', event, {
                    event_category: 'onboarding',
                    event_label: `step_${this.currentStep}`,
                    custom_data: data
                });
            }
        }

        goToStep(stepNumber) {
            // Animation de transition améliorée
            const $currentStep = $('.onboarding-step');
            const direction = stepNumber > this.currentStep ? 'next' : 'prev';

            $currentStep.addClass(`slide-out-${direction === 'next' ? 'left' : 'right'}`);

            setTimeout(() => {
                // Simuler le chargement du contenu de la nouvelle étape
                this.loadStep(stepNumber);
                $currentStep.removeClass(`slide-out-left slide-out-right`);

                const $newStep = $('.onboarding-step');
                $newStep.addClass(`slide-in-${direction === 'next' ? 'right' : 'left'}`);

                setTimeout(() => {
                    $newStep.removeClass('slide-in-left slide-in-right');
                }, 300);

                this.currentStep = stepNumber;
                this.updateProgress();
                this.announceStep();
                this.trackAnalytics('step_changed', { to: stepNumber });
            }, 150);
        }

        hideModal() {
            const $modal = $('#pdf-builder-onboarding-modal');
            $modal.fadeOut(300, () => {
                $modal.remove();
            });
        }

        completeStep(step, actionType = 'next') {
            console.log(`PDF Builder Onboarding: completeStep called - step: ${step}, actionType: ${actionType}`);

            const $button = $(`.complete-step[data-step="${step}"]`);
            console.log(`PDF Builder Onboarding: Button found:`, $button.length > 0 ? 'yes' : 'no');

            // Sauvegarder le texte original
            const originalText = $button.text();
            $button.data('original-text', originalText);

            // Désactiver le bouton avec feedback visuel
            $button.prop('disabled', true);
            console.log(`PDF Builder Onboarding: Button disabled for loading`);

            // Texte de chargement selon le type d'action
            const loadingText = actionType === 'finish' ? 'Finalisation...' : 'Chargement...';
            $button.html('<span class="dashicons dashicons-update spin"></span> ' + loadingText);

            if (typeof pdfBuilderOnboarding === 'undefined') {
                console.error('pdfBuilderOnboarding object not found');
                $button.prop('disabled', false).html(originalText);
                return;
            }

            // Ajouter un timeout pour éviter les blocages trop longs
            const timeoutId = setTimeout(() => {
                $button.html('<span class="dashicons dashicons-update spin"></span> Traitement...');
            }, 1000);

            $.ajax({
                url: pdfBuilderOnboarding.ajax_url,
                type: 'POST',
                timeout: 10000, // 10 secondes timeout
                data: {
                    action: 'pdf_builder_complete_onboarding_step',
                    nonce: pdfBuilderOnboarding.nonce,
                    step: step,
                    action_type: actionType,
                    selected_template: this.selectedTemplate,
                    woocommerce_options: this.getWoocommerceOptions()
                },
                success: (response) => {
                    clearTimeout(timeoutId);
                    console.log('PDF Builder Onboarding: AJAX success - response:', response);

                    if (response.success) {
                        console.log(`PDF Builder Onboarding: Step ${step} completed successfully, next_step: ${response.data.next_step}`);

                        // Feedback de succès selon le type d'action
                        if (actionType === 'finish') {
                            $button.html('<span class="dashicons dashicons-yes"></span> Terminé !');
                        } else {
                            $button.html('<span class="dashicons dashicons-yes"></span> Étape terminée');
                        }

                        setTimeout(() => {
                            if (response.data.completed) {
                                this.showCompletionMessage();
                            } else if (response.data.redirect_to) {
                                window.location.href = response.data.redirect_to;
                            } else {
                                // Mettre à jour l'étape côté client avant de charger la suivante
                                if (typeof pdfBuilderOnboarding !== 'undefined') {
                                    pdfBuilderOnboarding.current_step = response.data.next_step;
                                }
                                // Charger l'étape suivante via AJAX au lieu de recharger la page
                                this.loadStep(response.data.next_step);
                            }
                        }, 500);
                    } else {
                        console.error('PDF Builder Onboarding: AJAX returned error:', response.data);
                        // En cas d'erreur, réactiver tous les boutons
                        this.resetButtonStates();
                        $button.html(originalText);
                        this.showError(response.data?.message || 'Erreur lors de la sauvegarde');
                    }
                },
                error: (xhr, status, error) => {
                    clearTimeout(timeoutId);
                    console.error('PDF Builder Onboarding: AJAX error:', status, error);

                    // En cas d'erreur AJAX, réactiver tous les boutons
                    this.resetButtonStates();
                    $button.html(originalText);

                    if (status === 'timeout') {
                        this.showError('Délai d\'attente dépassé. Réessayez.');
                    } else {
                        this.showError('Erreur de connexion. Vérifiez votre connexion internet.');
                    }
                }
            });
        }

        getStepAction(step) {
            switch (step) {
                case 2:
                    return this.selectedTemplate ? 'create_template' : null;
                case 3:
                    return 'configure_woocommerce';
                default:
                    return null;
            }
        }

        showError(message) {
            console.log('PDF Builder Onboarding: Showing error message:', message);
            // Afficher un message d'erreur dans le modal
            const $modal = $('#pdf-builder-onboarding-modal');
            const $body = $modal.find('.modal-body');

            // Supprimer les anciens messages d'erreur
            $body.find('.error-message').remove();

            // Ajouter le nouveau message d'erreur
            const $error = $(`
                <div class="error-message" style="
                    background: #fef2f2;
                    border: 1px solid #fecaca;
                    color: #dc2626;
                    padding: 12px;
                    border-radius: 6px;
                    margin-bottom: 16px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                ">
                    <span class="dashicons dashicons-warning"></span>
                    <span>${message}</span>
                </div>
            `);

            $body.find('.step-content').prepend($error);

            // Faire défiler vers le message d'erreur
            $error.get(0).scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            // Supprimer automatiquement après 5 secondes
            setTimeout(() => {
                $error.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        resetButtonStates() {
            console.log('PDF Builder Onboarding: Resetting button states');
            // Réactiver tous les boutons et restaurer leur état normal
            $('.button-previous, .complete-step, [data-action]').each(function() {
                const $btn = $(this);
                $btn.prop('disabled', false);

                // Restaurer le texte original si sauvegardé
                const originalText = $btn.data('original-text');
                if (originalText) {
                    $btn.html(originalText);
                }
            });
        }

        selectTemplate($card) {
            console.log('PDF Builder Onboarding: Template selected', $card.data('template'));
            $('.template-card').removeClass('selected');
            $card.addClass('selected');
            this.selectedTemplate = $card.data('template');

            // Sauvegarder la sélection côté serveur
            this.saveTemplateSelection();

            // Mettre à jour le texte du bouton pour "Continuer" et l'activer
            const $button = $('.complete-step');
            console.log(`PDF Builder Onboarding: Activating button after template selection - was disabled: ${$button.prop('disabled')}`);
            $button.text('Continuer').prop('disabled', false);
            console.log(`PDF Builder Onboarding: Button activated - text: "${$button.text()}", disabled: ${$button.prop('disabled')}`);
        }

        updateFooterButtons(stepData) {
            const $footer = $('.modal-footer');

            // Mettre à jour le bouton principal
            const $primaryButton = $footer.find('.complete-step');
            if ($primaryButton.length > 0) {
                $primaryButton.text(stepData.action || 'Continuer');
                $primaryButton.attr('data-action-type', stepData.action_type || 'next');

                // Logique cohérente : désactiver seulement si requires_selection est true
                const shouldDisable = stepData.requires_selection === true;
                $primaryButton.prop('disabled', shouldDisable);

                console.log(`PDF Builder Onboarding: Step ${stepData.step} - Button "${stepData.action}" - requires_selection: ${stepData.requires_selection} - disabled: ${shouldDisable}`);
            }
            
            // Mettre à jour ou créer le bouton secondaire
            let $secondaryButton = $footer.find('.button-secondary');
            
            if (stepData.can_skip) {
                // L'étape peut être ignorée - afficher le bouton skip-step
                const skipText = stepData.skip_text || 'Ignorer l\'étape';
                if ($secondaryButton.length === 0) {
                    $secondaryButton = $('<button>')
                        .addClass('button button-secondary')
                        .attr('data-action', 'skip-step')
                        .text(skipText);
                    $footer.prepend($secondaryButton);
                } else {
                    $secondaryButton.attr('data-action', 'skip-step').text(skipText);
                }
            } else {
                // L'étape ne peut pas être ignorée - afficher le bouton skip-onboarding
                const skipText = 'Ignorer l\'assistant';
                if ($secondaryButton.length === 0) {
                    $secondaryButton = $('<button>')
                        .addClass('button button-secondary')
                        .attr('data-action', 'skip-onboarding')
                        .text(skipText);
                    $footer.prepend($secondaryButton);
                } else {
                    $secondaryButton.attr('data-action', 'skip-onboarding').text(skipText);
                }
            }
        }

        goToPreviousStep() {
            if (this.currentStep > 1) {
                const $button = $('.button-previous');
                
                // Désactiver le bouton pendant le chargement
                $button.prop('disabled', true);
                const originalHTML = $button.html();
                $button.html('<span class="dashicons dashicons-update spin"></span>');
                
                // Les boutons seront réactivés dans loadStep() après le chargement réussi
                this.loadStep(this.currentStep - 1);
            }
        }

        skipCurrentStep() {
            // Gérer l'ignorance selon l'étape courante
            if (this.currentStep === 2) {
                // Pour l'étape 2, passer à l'étape 3 sans sélection de template
                this.selectedTemplate = null; // Aucun template sélectionné
                // Mettre à jour côté client immédiatement
                if (typeof pdfBuilderOnboarding !== 'undefined') {
                    pdfBuilderOnboarding.current_step = 3;
                }
                this.updateServerStep(3); // Mettre à jour côté serveur
                this.loadStep(3);
            } else if (this.currentStep === 3) {
                // Pour l'étape 3, sauter la configuration WooCommerce
                this.skipWoocommerceSetup();
            } else {
                // Pour les autres étapes, passer simplement à la suivante
                const nextStep = this.currentStep + 1;
                // Mettre à jour côté client immédiatement
                if (typeof pdfBuilderOnboarding !== 'undefined') {
                    pdfBuilderOnboarding.current_step = nextStep;
                }
                this.updateServerStep(nextStep);
                this.loadStep(nextStep);
            }
        }

        loadStepContent(step) {
            // Cette fonction serait appelée depuis PHP pour charger le contenu dynamique
            // Pour l'instant, on simule avec les données existantes
            console.log('Loading step content for step:', step);
        }

        /**
         * Charger une étape via AJAX
         */
        loadStep(step) {
            const $modal = $('#pdf-builder-onboarding-modal');
            const $content = $modal.find('.modal-body .step-content');

            // Afficher un indicateur de chargement
            $content.html(`
                <div class="onboarding-loading">
                    <div class="loading-spinner"></div>
                    <p>Chargement de l'étape ${step}...</p>
                    <div class="loading-progress">
                        <div class="loading-bar" style="animation: loadingProgress 2s ease-in-out infinite;"></div>
                    </div>
                </div>
            `);

            // Faire la requête AJAX pour charger l'étape
            $.ajax({
                url: pdfBuilderOnboarding.ajax_url,
                type: 'POST',
                timeout: 10000,
                data: {
                    action: 'pdf_builder_load_onboarding_step',
                    nonce: pdfBuilderOnboarding.nonce,
                    step: step
                },
                success: (response) => {
                    if (response.success) {
                        // Mettre à jour le contenu de la modal
                        $content.html(response.data.content);

                        // Mettre à jour l'étape courante
                        this.currentStep = step;

                        // Mettre à jour la progression
                        this.updateProgress();

                        // Mettre à jour l'URL sans recharger la page
                        const currentUrl = new URL(window.location);
                        currentUrl.searchParams.set('pdf_onboarding_step', step);
                        window.history.replaceState({}, '', currentUrl.toString());

                        // Mettre à jour la visibilité du bouton précédent
                        const $prevButton = $('.button-previous');
                        if (step > 1) {
                            if ($prevButton.length === 0) {
                                // Créer le bouton précédent s'il n'existe pas
                                const $header = $('.modal-header');
                                $header.prepend(`
                                    <button class="button button-previous" data-tooltip="Étape précédente">
                                        <span class="dashicons dashicons-arrow-left-alt"></span>
                                    </button>
                                `);
                            } else {
                                // S'assurer que le bouton est visible et activé
                                $prevButton.show().prop('disabled', false);
                            }
                        } else {
                            // Supprimer complètement le bouton précédent pour la première étape
                            $prevButton.remove();
                        }

                        // Mettre à jour les boutons du footer selon l'étape
                        this.updateFooterButtons(response.data);

                        // Appliquer la même logique que updateFooterButtons pour cohérence
                        const shouldDisable = response.data.requires_selection === true;
                        $('.complete-step').prop('disabled', shouldDisable);

                        console.log(`PDF Builder Onboarding: Step ${step} loaded - requires_selection: ${response.data.requires_selection} - button disabled: ${shouldDisable}`);

                        // Animation d'entrée pour le nouveau contenu
                        $content.find('.onboarding-step-content').hide().fadeIn(300);

                        // Gérer les étapes automatiques
                        if (response.data.auto_advance) {
                            setTimeout(() => {
                                this.completeStep(step);
                            }, response.data.auto_advance_delay || 3000);
                        }

                        // Tracker l'événement
                        this.trackAnalytics('step_loaded', { step: step });

                        // Réactiver tous les boutons après le chargement réussi
                        $('.button-previous, .complete-step, [data-action="skip-onboarding"]').prop('disabled', false);

                        // Remettre le HTML original du bouton précédent
                        $('.button-previous').html('<span class="dashicons dashicons-arrow-left-alt"></span>');

                    } else {
                        // En cas d'erreur, réactiver tous les boutons et afficher l'erreur
                        $('.button-previous, .complete-step, [data-action="skip-onboarding"]').prop('disabled', false);
                        this.showError('Erreur lors du chargement de l\'étape');
                        // Recharger la page en cas d'erreur pour revenir à un état stable
                        setTimeout(() => window.location.reload(), 2000);
                    }
                },
                error: (xhr, status, error) => {
                    // En cas d'erreur AJAX, réactiver tous les boutons
                    $('.button-previous, .complete-step, [data-action="skip-onboarding"]').prop('disabled', false);
                    this.showError('Erreur de connexion lors du chargement de l\'étape');
                    // Recharger la page en cas d'erreur pour revenir à un état stable
                    setTimeout(() => window.location.reload(), 2000);
                }
            });
        }

        updateProgress() {
            const totalSteps = 4; // Mis à jour après suppression de l'étape 2
            const progress = (Math.min(this.currentStep, totalSteps) / totalSteps) * 100;

            $('.progress-fill').css('width', progress + '%');
            $('.progress-text').text(`Étape ${this.currentStep} sur ${totalSteps}`);
        }

        skipOnboarding() {
            const confirmMessage = (typeof pdfBuilderOnboarding !== 'undefined' && pdfBuilderOnboarding.strings && pdfBuilderOnboarding.strings.confirm_skip)
                ? pdfBuilderOnboarding.strings.confirm_skip
                : 'Êtes-vous sûr de vouloir ignorer l\'assistant de configuration ?';

            if (confirm(confirmMessage)) {
                if (typeof pdfBuilderOnboarding !== 'undefined' && pdfBuilderOnboarding.ajax_url && pdfBuilderOnboarding.nonce) {
                    $.ajax({
                        url: pdfBuilderOnboarding.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'pdf_builder_skip_onboarding',
                            nonce: pdfBuilderOnboarding.nonce
                        },
                        success: () => {
                            this.hideModal();
                            this.showNotification('Assistant de configuration ignoré. Vous pouvez le relancer depuis les paramètres.', 'info');
                        }
                    });
                } else {
                    // Fallback si pdfBuilderOnboarding n'est pas défini
                    this.hideModal();
                }
            }
        }

        showCompletionMessage() {
            const $modal = $('.pdf-builder-onboarding-modal');
            const $body = $modal.find('.modal-body');

            $body.html(`
                <div class="onboarding-completed-message" style="text-align: center; padding: 40px 20px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">🎉</div>
                    <h2 style="color: #1d2327; margin-bottom: 16px;">Configuration terminée !</h2>
                    <p style="color: #666; font-size: 16px; margin-bottom: 30px;">
                        Votre PDF Builder Pro est maintenant prêt à être utilisé.
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <a href="/wp-admin/admin.php?page=pdf-builder-templates" class="button button-primary" style="padding: 12px 24px;">
                            Commencer à créer
                        </a>
                        <a href="/wp-admin/admin.php?page=pdf-builder-settings" class="button button-secondary" style="padding: 12px 24px;">
                            Voir les paramètres
                        </a>
                    </div>
                </div>
            `);

            // Masquer le footer et ajuster le modal
            $modal.find('.modal-footer').hide();
            $modal.find('.modal-header .progress-bar').css('width', '100%');
            $modal.find('.progress-text').text('Terminé !');

            // Auto-fermer après 3 secondes
            setTimeout(() => {
                this.hideModal();
            }, 3000);
        }

        showError(message) {
            this.showNotification(message, 'error');
        }

        showNotification(message, type = 'info') {
            // Utiliser le système de notifications existant si disponible
            if (window.PDF_Builder_Notification_Manager) {
                window.PDF_Builder_Notification_Manager.show_toast(message, type);
            } else {
                // Fallback avec alert
                alert(message);
            }
        }

        saveTemplateSelection() {
            // Sauvegarder la sélection de template via AJAX
            $.ajax({
                url: pdfBuilderOnboarding.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_save_template_selection',
                    nonce: pdfBuilderOnboarding.nonce,
                    selected_template: this.selectedTemplate
                },
                success: (response) => {
                    if (!response.success) {
                        console.warn('Failed to save template selection:', response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.warn('Error saving template selection:', error);
                }
            });
        }

        trapFocus($container) {
            const focusableElements = $container.find(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );

            const firstElement = focusableElements.first();
            const lastElement = focusableElements.last();

            $container.on('keydown', (e) => {
                if (e.key === 'Tab') {
                    if (e.shiftKey) {
                        if (document.activeElement === firstElement[0]) {
                            lastElement.focus();
                            e.preventDefault();
                        }
                    } else {
                        if (document.activeElement === lastElement[0]) {
                            firstElement.focus();
                            e.preventDefault();
                        }
                    }
                }

                if (e.key === 'Escape') {
                    this.skipOnboarding();
                }
            });

            // Focus initial
            firstElement.focus();
        }

        updateServerStep(step) {
            // Mettre à jour l'étape côté serveur
            $.ajax({
                url: pdfBuilderOnboarding.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_update_onboarding_step',
                    nonce: pdfBuilderOnboarding.nonce,
                    step: step
                },
                success: (response) => {
                    if (response.success) {
                        console.log('PDF Builder Onboarding: Step updated on server to', step);
                        // Mettre à jour aussi côté client pour cohérence
                        if (typeof pdfBuilderOnboarding !== 'undefined') {
                            pdfBuilderOnboarding.current_step = step;
                        }
                    } else {
                        console.error('PDF Builder Onboarding: Failed to update step on server');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('PDF Builder Onboarding: AJAX error updating step:', error);
                }
            });
        }
    }

    // Initialiser quand le DOM est prêt
    $(document).ready(() => {
        if ($('#pdf-builder-onboarding-modal').length) {
            new PDFBuilderOnboarding();
        }
    });

})(jQuery);