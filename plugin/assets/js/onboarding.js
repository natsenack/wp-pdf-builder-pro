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
            this.bindEvents();
            this.initializeWizard();
            this.setupKeyboardNavigation();
            this.setupAutoSave();
            this.trackAnalytics('onboarding_started');
        }

        bindEvents() {
            // Événements existants
            $(document).on('click', '[data-action="next-step"]', (e) => {
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

        setupKeyboardNavigation() {
            $(document).on('keydown', (e) => {
                if (!$('#pdf-builder-onboarding-modal').is(':visible')) return;

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
            const $currentBtn = $(`[data-action="next-step"][data-step="${this.currentStep}"]`);
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
            const $modal = $('#pdf-builder-onboarding-modal');
            $modal.fadeIn(400, () => {
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
            const $button = $(`[data-action="next-step"][data-step="${step}"]`);
            const stepAction = this.getStepAction(step);

            $button.prop('disabled', true).text('Sauvegarde en cours...');

            if (typeof pdfBuilderOnboarding === 'undefined') {
                console.error('pdfBuilderOnboarding object not found');
                return;
            }

            $.ajax({
                url: pdfBuilderOnboarding.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_complete_onboarding_step',
                    nonce: pdfBuilderOnboarding.nonce,
                    step: step,
                    step_action: stepAction,
                    woocommerce_options: this.getWoocommerceOptions()
                },
                success: (response) => {
                    if (response.success) {
                        if (response.data.completed) {
                            this.showCompletionMessage();
                        } else if (response.data.redirect_to) {
                            window.location.href = response.data.redirect_to;
                        } else {
                            this.goToNextStep(response.data.next_step);
                        }
                    }
                },
                error: () => {
                    $button.prop('disabled', false).text($button.data('original-text') || 'Continuer');
                    this.showError('Erreur lors de la sauvegarde de l\'étape');
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
            const $button = $('[data-action="next-step"]');
            const templateName = $card.find('h4').text();
            $button.text(`Créer ${templateName}`);
        }

        skipWoocommerceSetup() {
            // Passer directement à l'étape suivante
            this.completeStep(4);
        }

        goToNextStep(nextStep) {
            // Animation de transition
            const $currentStep = $('.onboarding-step');
            $currentStep.fadeOut(200, () => {
                // Mettre à jour le contenu pour la nouvelle étape
                this.loadStepContent(nextStep);
                $currentStep.fadeIn(300);
            });

            this.currentStep = nextStep;
            this.updateProgress();
        }

        loadStepContent(step) {
            // Cette fonction serait appelée depuis PHP pour charger le contenu dynamique
            // Pour l'instant, on simule avec les données existantes
            console.log('Loading step content for step:', step);
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