/**
 * PDF Builder Pro - Onboarding JavaScript
 * Version: 1.1.0
 */

(function($) {
    'use strict';

    var PDF_Builder_Onboarding = {

        init: function() {
            this.bindEvents();
            this.initSteps();
        },

        bindEvents: function() {
            var self = this;

            // Navigation des étapes
            $(document).on('click', '.onboarding-next', function(e) {
                e.preventDefault();
                self.nextStep();
            });

            $(document).on('click', '.onboarding-prev', function(e) {
                e.preventDefault();
                self.prevStep();
            });

            // Sélection de template
            $(document).on('click', '.template-option', function() {
                var templateId = $(this).data('template');
                self.selectTemplate(templateId);
            });

            // Sélection de mode
            $(document).on('change', 'input[name="pdf_mode"]', function() {
                var mode = $(this).val();
                self.selectMode(mode);
            });

            // Finalisation
            $(document).on('click', '.onboarding-complete', function(e) {
                e.preventDefault();
                self.completeOnboarding();
            });
        },

        initSteps: function() {
            var currentStep = pdfBuilderOnboarding.current_step || 1;
            this.showStep(currentStep);
            this.updateProgress(currentStep);
        },

        showStep: function(step) {
            $('.onboarding-step').hide();
            $('.onboarding-step[data-step="' + step + '"]').show();
            this.updateNavigation(step);
        },

        updateProgress: function(currentStep) {
            var totalSteps = pdfBuilderOnboarding.total_steps;
            $('.step').removeClass('active');
            for (var i = 1; i <= currentStep; i++) {
                $('.step[data-step="' + i + '"]').addClass('active');
            }
        },

        updateNavigation: function(step) {
            var totalSteps = pdfBuilderOnboarding.total_steps;

            if (step === 1) {
                $('.onboarding-prev').hide();
            } else {
                $('.onboarding-prev').show();
            }

            if (step === totalSteps) {
                $('.onboarding-next').hide();
                $('.onboarding-complete').show();
            } else {
                $('.onboarding-next').show();
                $('.onboarding-complete').hide();
            }
        },

        nextStep: function() {
            var currentStep = pdfBuilderOnboarding.current_step || 1;
            var nextStep = currentStep + 1;

            if (nextStep <= pdfBuilderOnboarding.total_steps) {
                this.saveProgress(nextStep);
                this.showStep(nextStep);
                this.updateProgress(nextStep);
            }
        },

        prevStep: function() {
            var currentStep = pdfBuilderOnboarding.current_step || 1;
            var prevStep = currentStep - 1;

            if (prevStep >= 1) {
                this.saveProgress(prevStep);
                this.showStep(prevStep);
                this.updateProgress(prevStep);
            }
        },

        selectTemplate: function(templateId) {
            $('.template-option').removeClass('selected');
            $('.template-option[data-template="' + templateId + '"]').addClass('selected');

            this.saveOption('selected_template', templateId);
        },

        selectMode: function(mode) {
            this.saveOption('selected_mode', mode);
        },

        saveProgress: function(step) {
            this.ajaxRequest('save_onboarding_progress', {
                step: step
            });
        },

        saveOption: function(option, value) {
            this.ajaxRequest('save_onboarding_option', {
                option: option,
                value: value
            });
        },

        completeOnboarding: function() {
            this.ajaxRequest('complete_onboarding', {}, function(response) {
                if (response.success) {
                    window.location.href = pdfBuilderOnboarding.redirect_url || admin_url('admin.php?page=pdf-builder-settings');
                }
            });
        },

        ajaxRequest: function(action, data, callback) {
            var self = this;

            $.ajax({
                url: pdfBuilderOnboarding.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_' + action,
                    nonce: pdfBuilderOnboarding.nonce,
                    data: data
                },
                success: function(response) {
                    if (response.success && callback) {
                        callback(response);
                    }
                },
                error: function() {
                    // console.error('Erreur AJAX dans l\'onboarding');
                }
            });
        }
    };

    // Initialisation
    $(document).ready(function() {
        PDF_Builder_Onboarding.init();
    });

})(jQuery);