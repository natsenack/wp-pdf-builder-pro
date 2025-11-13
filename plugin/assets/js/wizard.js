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
        // Test AJAX au chargement
        this.testAjax();
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

    testAjax: function() {
        console.log('PDF Builder Wizard: Testing AJAX...');
        console.log('PDF Builder Wizard: ajax_url =', pdfBuilderWizard.ajax_url);
        console.log('PDF Builder Wizard: global ajaxurl =', typeof ajaxurl !== 'undefined' ? ajaxurl : 'undefined');
        console.log('PDF Builder Wizard: nonce =', pdfBuilderWizard.nonce);
        jQuery.ajax({
            url: '/wp-content/plugins/wp-pdf-builder-pro/ajax-handler.php',
            type: 'POST',
            data: {
                action: 'test_ajax'
            },
            success: function(response) {
                console.log('PDF Builder Wizard: AJAX test successful:', response);
            },
            error: function(xhr, status, error) {
                console.error('PDF Builder Wizard: AJAX test failed:', xhr, status, error);
            }
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
            console.log('PDF Builder Wizard: AJAX response:', response);
            if (response.success) {
                pdfBuilderWizard.showSuccess('Étape sauvegardée avec succès !');
                pdfBuilderWizard.navigateToStep(step);
            } else {
                console.error('PDF Builder Wizard: Error response:', response);
                pdfBuilderWizard.showError(response.message || 'Erreur lors de la sauvegarde des données.');
            }
            pdfBuilderWizard.isLoading = false;
            pdfBuilderWizard.hideLoading();
        }).fail(function(xhr, status, error) {
            console.error('PDF Builder Wizard: AJAX failed:', xhr, status, error);
            pdfBuilderWizard.showError('Erreur de communication avec le serveur. Vérifiez votre connexion internet.');
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
            this.showError('Le nom de l\'entreprise est obligatoire pour continuer.');
            return false;
        }

        var email = jQuery('#company_email').val().trim();
        if (email && !this.isValidEmail(email)) {
            this.showError('Veuillez saisir une adresse email valide.');
            return false;
        }

        var logoUrl = jQuery('#company_logo').val().trim();
        if (logoUrl && !this.isValidUrl(logoUrl)) {
            this.showError('Veuillez saisir une URL valide pour le logo.');
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

        console.log('PDF Builder Wizard: Sending data:', data);

        return jQuery.ajax({
            url: '/wp-content/plugins/wp-pdf-builder-pro/ajax-handler.php',
            type: 'POST',
            data: {
                action: 'pdf_builder_wizard_step',
                step: 'save_company',
                data: data,
                nonce: typeof pdfBuilderWizard !== 'undefined' && pdfBuilderWizard.nonce ? pdfBuilderWizard.nonce : ''
            }
        });
    },

    createTemplate: function() {
        return jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_wizard_step',
                step: 'create_template',
                nonce: typeof pdfBuilderWizard !== 'undefined' && pdfBuilderWizard.nonce ? pdfBuilderWizard.nonce : ''
            }
        });
    },

    completeSetup: function() {
        return jQuery.ajax({
            url: '/wp-content/plugins/wp-pdf-builder-pro/ajax-handler.php',
            type: 'POST',
            data: {
                action: 'pdf_builder_wizard_step',
                step: 'complete',
                nonce: typeof pdfBuilderWizard !== 'undefined' && pdfBuilderWizard.nonce ? pdfBuilderWizard.nonce : ''
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
            // Vérifier si l'URL est valide avant d'afficher
            if (this.isValidUrl(logoUrl)) {
                previewImg.attr('src', logoUrl);
                previewImg.off('error').on('error', function() {
                    pdfBuilderWizard.showError('Impossible de charger l\'image. Vérifiez que l\'URL est accessible.');
                    previewContainer.hide();
                });
                previewImg.off('load').on('load', function() {
                    previewContainer.show();
                });
                previewContainer.show();
            } else {
                this.showError('URL du logo invalide. Veuillez saisir une URL complète (https://...).');
                previewContainer.hide();
            }
        } else {
            previewContainer.hide();
        }
    },

    showError: function(message) {
        this.showMessage(message, 'error');
    },

    showSuccess: function(message) {
        this.showMessage(message, 'success');
    },

    showInfo: function(message) {
        this.showMessage(message, 'info');
    },

    showMessage: function(message, type) {
        // Supprimer les messages existants
        jQuery('.wizard-message').remove();

        // Créer le message
        var messageHtml = '<div class="wizard-message wizard-message-' + type + '">' +
            '<span class="wizard-message-icon">' +
                (type === 'error' ? '⚠️' : type === 'success' ? '✅' : 'ℹ️') +
            '</span>' +
            '<span class="wizard-message-text">' + message + '</span>' +
            '<button class="wizard-message-close" onclick="jQuery(this).parent().fadeOut()">&times;</button>' +
            '</div>';

        // Insérer le message en haut du contenu
        jQuery('.wizard-content').prepend(messageHtml);

        // Auto-hide après 5 secondes (sauf pour les erreurs)
        if (type !== 'error') {
            setTimeout(function() {
                jQuery('.wizard-message').fadeOut();
            }, 5000);
        }

        // Scroll vers le message
        jQuery('.wizard-content')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    initLogoPreview: function() {
        var logoUrl = jQuery('#company_logo').val();
        if (logoUrl && logoUrl.trim() !== '') {
            this.updateLogoPreview(logoUrl);
            this.showInfo('Logo détecté automatiquement depuis votre configuration WooCommerce.');
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

    isValidEmail: function(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    isValidUrl: function(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    },
};

// Initialisation
jQuery(document).ready(function() {
    pdfBuilderWizard.init();
});