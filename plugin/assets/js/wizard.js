/**
 * JavaScript pour le wizard d'installation PDF Builder Pro
 */

/* global wp */

var pdfBuilderWizard = {
    currentStep: 'welcome',
    isLoading: false,

    init: function() {
        this.bindEvents();
        this.updateNavigation();
        this.initLogoPreview();
    },

    bindEvents: function() {
        var self = this;

        // Navigation
        jQuery(document).on('click', '.wizard-navigation .button-primary', function(e) {
            e.preventDefault();
            var nextStep = jQuery(this).data('next-step');
            if (nextStep) {
                self.nextStep(nextStep);
            }
        });

        jQuery(document).on('click', '.wizard-navigation .button-secondary', function(e) {
            e.preventDefault();
            var prevStep = jQuery(this).data('prev-step');
            if (prevStep) {
                self.previousStep(prevStep);
            }
        });

        // Upload logo
        jQuery('#upload-logo').on('click', function(e) {
            e.preventDefault();
            self.openMediaUploader();
        });

        // Formulaire entreprise
        jQuery('#company-form').on('submit', function(e) {
            e.preventDefault();
            self.saveCompanyData();
        });

        // Aperçu du logo
        jQuery('#company_logo').on('input', function() {
            self.updateLogoPreview(jQuery(this).val());
        });
    },

    nextStep: function(step) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoading();

        // Validation de l'étape actuelle
        if (!this.validateCurrentStep()) {
            this.isLoading = false;
            this.hideLoading();
            return;
        }

        // Sauvegarde des données si nécessaire
        this.saveCurrentStepData().done(function(response) {
            if (response.success) {
                pdfBuilderWizard.navigateToStep(step);
            } else {
                pdfBuilderWizard.showError(response.message || 'Erreur lors de la sauvegarde');
            }
            pdfBuilderWizard.isLoading = false;
            pdfBuilderWizard.hideLoading();
        }).fail(function() {
            pdfBuilderWizard.showError('Erreur de communication avec le serveur');
            pdfBuilderWizard.isLoading = false;
            pdfBuilderWizard.hideLoading();
        });
    },

    previousStep: function(step) {
        this.navigateToStep(step);
    },

    skipStep: function(step) {
        // Passer l'étape sans validation ni sauvegarde
        this.navigateToStep(step);
    },

    navigateToStep: function(step) {
        var url = new URL(window.location);
        url.searchParams.set('step', step);
        window.location.href = url.toString();
    },

    validateCurrentStep: function() {
        var currentStep = this.getCurrentStep();

        switch (currentStep) {
            case 'company':
                return this.validateCompanyForm();
            default:
                return true;
        }
    },

    validateCompanyForm: function() {
        var companyName = jQuery('#company_name').val().trim();
        if (!companyName) {
            this.showError('Le nom de l\'entreprise est obligatoire');
            return false;
        }
        return true;
    },

    saveCurrentStepData: function() {
        var currentStep = this.getCurrentStep();

        switch (currentStep) {
            case 'company':
                return this.saveCompanyData();
            case 'template':
                return this.createTemplate();
            case 'complete':
                return this.completeSetup();
            default:
                return jQuery.Deferred().resolve({success: true});
        }
    },

    saveCompanyData: function() {
        var data = {
            company_name: jQuery('#company_name').val(),
            company_address: jQuery('#company_address').val(),
            company_phone: jQuery('#company_phone').val(),
            company_email: jQuery('#company_email').val(),
            company_logo: jQuery('#company_logo').val()
        };

        return jQuery.ajax({
            url: pdfBuilderWizard.ajax_url,
            type: 'POST',
            data: {
                action: 'pdf_builder_wizard_step',
                step: 'save_company',
                data: data,
                nonce: pdfBuilderWizard.nonce
            }
        });
    },

    createTemplate: function() {
        return jQuery.ajax({
            url: pdfBuilderWizard.ajax_url,
            type: 'POST',
            data: {
                action: 'pdf_builder_wizard_step',
                step: 'create_template',
                nonce: pdfBuilderWizard.nonce
            }
        });
    },

    completeSetup: function() {
        return jQuery.ajax({
            url: pdfBuilderWizard.ajax_url,
            type: 'POST',
            data: {
                action: 'pdf_builder_wizard_step',
                step: 'complete',
                nonce: pdfBuilderWizard.nonce
            }
        });
    },

    finish: function() {
        // Rediriger vers la page principale du plugin
        window.location.href = pdfBuilderWizard.adminUrl;
    },

    openMediaUploader: function() {

        if (typeof wp !== 'undefined' && wp.media) {
            var mediaUploader = wp.media({
                title: 'Choisir un logo',
                button: {
                    text: 'Utiliser ce logo'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                jQuery('#company_logo').val(attachment.url);
                pdfBuilderWizard.updateLogoPreview(attachment.url);
            });

            mediaUploader.open();
        } else {
            // Fallback pour les anciennes versions
            var uploadFrame = wp.media({
                title: 'Choisir un logo',
                button: {
                    text: 'Utiliser ce logo'
                },
                multiple: false
            });

            uploadFrame.on('select', function() {
                var attachment = uploadFrame.state().get('selection').first().toJSON();
                jQuery('#company_logo').val(attachment.url);
                pdfBuilderWizard.updateLogoPreview(attachment.url);
            });

            uploadFrame.open();
        }
    },

    updateLogoPreview: function(logoUrl) {
        var previewContainer = jQuery('#logo-preview');
        var previewImg = jQuery('#logo-preview-img');

        if (logoUrl && logoUrl.trim() !== '') {
            previewImg.attr('src', logoUrl);
            previewContainer.show();
        } else {
            previewContainer.hide();
        }
    },

    initLogoPreview: function() {
        var logoUrl = jQuery('#company_logo').val();
        if (logoUrl && logoUrl.trim() !== '') {
            this.updateLogoPreview(logoUrl);
        }
    },

    getCurrentStep: function() {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('step') || 'welcome';
    },

    updateNavigation: function() {
        var currentStep = this.getCurrentStep();
        var steps = ['welcome', 'dependencies', 'company', 'template', 'complete'];
        var currentIndex = steps.indexOf(currentStep);

        // Mettre à jour les boutons de navigation
        jQuery('.wizard-navigation .button-secondary').data('prev-step', currentIndex > 0 ? steps[currentIndex - 1] : null);
        jQuery('.wizard-navigation .button-primary').data('next-step', currentIndex < steps.length - 1 ? steps[currentIndex + 1] : null);
    },

    showLoading: function() {
        jQuery('.wizard-content').append('<div class="wizard-loading"><span class="spinner is-active"></span> Chargement...</div>');
    },

    hideLoading: function() {
        jQuery('.wizard-loading').remove();
    },

    showError: function(message) {
        // Supprimer les erreurs précédentes
        jQuery('.wizard-error').remove();

        // Afficher la nouvelle erreur
        jQuery('.wizard-content').prepend('<div class="wizard-error notice notice-error"><p>' + message + '</p></div>');

        // Scroll vers le haut
        jQuery('html, body').animate({scrollTop: 0}, 300);
    },

    showSuccess: function(message) {
        // Supprimer les messages précédents
        jQuery('.wizard-message').remove();

        // Afficher le message de succès
        jQuery('.wizard-content').prepend('<div class="wizard-message notice notice-success"><p>' + message + '</p></div>');

        // Auto-hide après 3 secondes
        setTimeout(function() {
            jQuery('.wizard-message').fadeOut();
        }, 3000);
    }
};

// Initialisation
jQuery(document).ready(function() {
    pdfBuilderWizard.init();
});