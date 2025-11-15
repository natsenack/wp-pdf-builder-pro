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
            // Événements existants
            $(document).on('click', '.complete-step', (e) => {
                e.preventDefault();
                const step = $(e.currentTarget).data('step');
                this.completeStep(step);
            });

            $(document).on('click', '[data-action="skip-onboarding"]', (e) => {
                e.preventDefault();
                this.skipOnboarding();
            });

            $(document).on('click', '.template-card', (e) => {
                e.preventDefault();
                this.selectTemplate($(e.currentTarget));
            });

            $(document).on('click', '.skip-woocommerce', (e) => {
                e.preventDefault();
                this.skipWoocommerceSetup();
            });

            // Navigation avec les boutons précédent/suivant
            $(document).on('click', '.button-previous', (e) => {
                e.preventDefault();

                const $button = $(e.currentTarget);
                const originalText = $button.html();

                // Feedback visuel immédiat
                $button.prop('disabled', true)
                       .html('<span class="dashicons dashicons-update spin"></span> Chargement...');

                // Charger l'étape précédente via AJAX
                const prevStep = this.currentStep - 1;
                if (prevStep >= 1) {
                    this.loadStep(prevStep);
                } else {
                    // Si on ne peut pas aller plus loin, remettre le bouton à l'état normal
                    $button.prop('disabled', false).html(originalText);
                }
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
            console.log('PDF Builder Onboarding: Current step set to', this.currentStep);

            this.showModal();
            this.announceStep();

            // Vérifier si l'étape actuelle doit avancer automatiquement
            this.checkAutoAdvance();
        }

        /**
         * Vérifier si l'étape actuelle doit avancer automatiquement
         */
        checkAutoAdvance() {
            // Pour les étapes chargées via AJAX, c'est géré dans loadStep
            // Pour l'étape initiale, on vérifie via une requête AJAX
            if (this.currentStep === 2) { // Étape de vérification d'environnement
                console.log('PDF Builder Onboarding: Auto-advancing step 2 after 3 seconds');
                setTimeout(() => {
                    this.completeStep(2);
                }, 3000);
            }
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
            $modal.fadeIn(400, () => {
                console.log('PDF Builder Onboarding: Modal fadeIn complete');
                console.log('PDF Builder Onboarding: Modal display after fadeIn:', $modal.css('display'));
                console.log('PDF Builder Onboarding: Modal visibility:', $modal.css('visibility'));
                console.log('PDF Builder Onboarding: Modal opacity:', $modal.css('opacity'));
                console.log('PDF Builder Onboarding: Modal z-index:', $modal.css('z-index'));
                // Animation d'entrée améliorée
                $modal.find('.modal-content').addClass('modal-entrance-animation');
                this.announceStep();
                this.focusFirstElement();
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
            const stepTitle = $(`.onboarding-step[data-step="${this.currentStep}"] h3`).text();
            if (stepTitle) {
                this.announceToScreenReader(`Étape ${this.currentStep} sur 5: ${stepTitle}`);
            }
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
                this.loadStepContent(stepNumber);
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

        completeStep(step) {
            const $button = $(`.complete-step[data-step="${step}"]`);
            const stepAction = this.getStepAction(step);

            // Sauvegarder le texte original
            const originalText = $button.text();
            $button.data('original-text', originalText);

            // Désactiver le bouton avec feedback visuel amélioré
            $button.prop('disabled', true)
                   .html('<span class="dashicons dashicons-update spin"></span> Sauvegarde...');

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
                    step_action: stepAction,
                    woocommerce_options: this.getWoocommerceOptions()
                },
                success: (response) => {
                    clearTimeout(timeoutId);
                    if (response.success) {
                        // Feedback de succès rapide
                        $button.html('<span class="dashicons dashicons-yes"></span> Terminé !');

                        setTimeout(() => {
                            if (response.data.completed) {
                                this.showCompletionMessage();
                            } else if (response.data.redirect_to) {
                                window.location.href = response.data.redirect_to;
                            } else {
                                // Charger l'étape suivante via AJAX au lieu de recharger la page
                                this.loadStep(response.data.next_step);
                            }
                        }, 500);
                    } else {
                        $button.prop('disabled', false).html(originalText);
                        this.showError('Erreur lors de la sauvegarde');
                    }
                },
                error: (xhr, status, error) => {
                    clearTimeout(timeoutId);
                    $button.prop('disabled', false).html(originalText);

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
                case 3:
                    return this.selectedTemplate ? 'create_template' : null;
                case 4:
                    return 'configure_woocommerce';
                default:
                    return null;
            }
        }

        getWoocommerceOptions() {
            const options = {};
            $('.woocommerce-setup input[type="checkbox"]').each(function() {
                options[$(this).attr('name')] = $(this).is(':checked');
            });
            return options;
        }

        selectTemplate($card) {
            $('.template-card').removeClass('selected');
            $card.addClass('selected');
            this.selectedTemplate = $card.data('template');

            // Mettre à jour le texte du bouton
            const $button = $('.complete-step');
            const templateName = $card.find('h4').text();
            $button.text(`Créer ${templateName}`);
        }

        skipWoocommerceSetup() {
            // Passer directement à l'étape suivante
            this.completeStep(4);
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
            console.log('PDF Builder Onboarding: loadStep called with step:', step);
            const $modal = $('#pdf-builder-onboarding-modal');
            const $content = $modal.find('.modal-body .step-content');
            console.log('PDF Builder Onboarding: modal found:', $modal.length, 'content found:', $content.length);

            // Afficher un indicateur de chargement
            $content.html(`
                <div class="onboarding-loading">
                    <div class="loading-spinner"></div>
                    <p>Chargement de l'étape...</p>
                </div>
            `);

            // Faire la requête AJAX pour charger l'étape
            console.log('PDF Builder Onboarding: Making AJAX request for step:', step);
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
                    console.log('PDF Builder Onboarding: AJAX success, response:', response);
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
                            }
                        } else {
                            $prevButton.remove();
                        }

                        // Réactiver les boutons de navigation
                        $('.button-previous, .complete-step').prop('disabled', false);

                        // Animation d'entrée pour le nouveau contenu
                        $content.find('.onboarding-step-content').hide().fadeIn(300);

                        // Gérer les étapes automatiques
                        if (response.data.auto_advance) {
                            console.log('PDF Builder Onboarding: Auto-advancing step after', response.data.auto_advance_delay, 'ms');
                            setTimeout(() => {
                                this.completeStep(step);
                            }, response.data.auto_advance_delay || 3000);
                        }

                        // Tracker l'événement
                        this.trackAnalytics('step_loaded', { step: step });

                    } else {
                        console.error('PDF Builder Onboarding: AJAX response not successful:', response);
                        this.showError('Erreur lors du chargement de l\'étape');
                        // Recharger la page en cas d'erreur pour revenir à un état stable
                        window.location.reload();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('PDF Builder Onboarding: AJAX error:', status, error, xhr);
                    this.showError('Erreur de connexion lors du chargement de l\'étape');
                    // Recharger la page en cas d'erreur pour revenir à un état stable
                    window.location.reload();
                }
            });
        }

        updateProgress() {
            const totalSteps = 5;
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
    }

    // Initialiser quand le DOM est prêt
    $(document).ready(() => {
        if ($('#pdf-builder-onboarding-modal').length) {
            new PDFBuilderOnboarding();
        }
    });

})(jQuery);