/**
 * PDF Builder Pro - GDPR JavaScript
 * Version: 1.1.0
 */

(function($) {
    'use strict';

    var PDF_Builder_GDPR = {

        init: function() {
            this.bindEvents();
            this.initConsent();
        },

        bindEvents: function() {
            var self = this;

            // Boutons de consentement
            $(document).on('click', '.gdpr-accept-all', function(e) {
                e.preventDefault();
                self.acceptAll();
            });

            $(document).on('click', '.gdpr-reject-all', function(e) {
                e.preventDefault();
                self.rejectAll();
            });

            $(document).on('click', '.gdpr-save-preferences', function(e) {
                e.preventDefault();
                self.savePreferences();
            });

            // Basculement des préférences individuelles
            $(document).on('change', '.gdpr-consent-toggle', function() {
                var category = $(this).data('category');
                var consented = $(this).is(':checked');
                self.updateConsent(category, consented);
            });

            // Ouvrir les paramètres détaillés
            $(document).on('click', '.gdpr-show-details', function(e) {
                e.preventDefault();
                self.showDetails();
            });

            // Fermer les paramètres détaillés
            $(document).on('click', '.gdpr-hide-details', function(e) {
                e.preventDefault();
                self.hideDetails();
            });
        },

        initConsent: function() {
            // Charger les préférences existantes
            this.loadPreferences();
        },

        acceptAll: function() {
            $('.gdpr-consent-toggle').prop('checked', true);
            this.saveAllPreferences(true);
            this.hideBanner();
        },

        rejectAll: function() {
            $('.gdpr-consent-toggle').prop('checked', false);
            this.saveAllPreferences(false);
            this.hideBanner();
        },

        savePreferences: function() {
            var preferences = this.getCurrentPreferences();
            this.savePreferencesAjax(preferences);
            this.hideDetails();
        },

        updateConsent: function(category, consented) {
            // Mettre à jour l'état visuel
            var toggle = $('.gdpr-consent-toggle[data-category="' + category + '"]');
            toggle.prop('checked', consented);

            // Sauvegarder automatiquement si configuré
            if (pdfBuilderGDPR.auto_save) {
                var preferences = this.getCurrentPreferences();
                this.savePreferencesAjax(preferences);
            }
        },

        getCurrentPreferences: function() {
            var preferences = {};

            $('.gdpr-consent-toggle').each(function() {
                var category = $(this).data('category');
                var consented = $(this).is(':checked');
                preferences[category] = consented;
            });

            return preferences;
        },

        saveAllPreferences: function(consented) {
            var preferences = {};

            $('.gdpr-consent-toggle').each(function() {
                var category = $(this).data('category');
                preferences[category] = consented;
            });

            this.savePreferencesAjax(preferences);
        },

        savePreferencesAjax: function(preferences) {
            this.ajaxRequest('save_gdpr_preferences', {
                preferences: preferences
            }, function(response) {
                if (response.success) {
                    // Afficher un message de succès
                    self.showMessage(pdfBuilderGDPR.messages.saved || 'Préférences sauvegardées');
                }
            });
        },

        loadPreferences: function() {
            this.ajaxRequest('load_gdpr_preferences', {}, function(response) {
                if (response.success && response.data.preferences) {
                    var preferences = response.data.preferences;

                    $.each(preferences, function(category, consented) {
                        $('.gdpr-consent-toggle[data-category="' + category + '"]').prop('checked', consented);
                    });
                }
            });
        },

        showDetails: function() {
            $('.gdpr-details').slideDown();
            $('.gdpr-show-details').hide();
            $('.gdpr-hide-details').show();
        },

        hideDetails: function() {
            $('.gdpr-details').slideUp();
            $('.gdpr-hide-details').hide();
            $('.gdpr-show-details').show();
        },

        hideBanner: function() {
            $('.gdpr-consent-banner').fadeOut();
        },

        showMessage: function(message) {
            // Afficher un message temporaire
            var notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
            $('.gdpr-container').prepend(notice);

            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        },

        ajaxRequest: function(action, data, callback) {
            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_' + action,
                    nonce: pdfBuilderGDPR.nonce,
                    data: data
                },
                success: function(response) {
                    if (response.success && callback) {
                        callback(response);
                    }
                },
                error: function() {
                    // console.error('Erreur AJAX dans le GDPR');
                }
            });
        }
    };

    // Initialisation
    $(document).ready(function() {
        PDF_Builder_GDPR.init();
    });

})(jQuery);