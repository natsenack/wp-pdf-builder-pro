/**
 * JavaScript pour le système d'onboarding de PDF Builder Pro
 */

(function($) {
    'use strict';

    class PDFBuilderOnboarding {
        constructor() {
            this.currentStep = 1;
            this.selectedTemplate = null;
            this.init();
        }

        init() {
            this.bindEvents();
            this.initializeWizard();
        }

        bindEvents() {
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
        }

        initializeWizard() {
            const $modal = $('#pdf-builder-onboarding-modal');
            if ($modal.length) {
                this.showModal();
                this.updateProgress();
            }
        }

        showModal() {
            const $modal = $('#pdf-builder-onboarding-modal');
            $modal.fadeIn(300);

            // Focus trap pour l'accessibilité
            this.trapFocus($modal);
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