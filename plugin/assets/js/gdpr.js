/**
 * JavaScript pour la gestion RGPD de PDF Builder Pro
 */

(function($) {
    'use strict';

    class PDFBuilderGDPR {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.loadConsentStatus();
        }

        bindEvents() {
            // Utiliser la délégation d'événements pour les onglets RGPD
            $(document).on('click', '.gdpr-tab', (e) => this.handleTabSwitch(e));

            // Gestion des formulaires
            $(document).on('submit', '#gdpr-consent-form', (e) => this.handleConsentForm(e));
            $(document).on('submit', '#gdpr-security-form', (e) => this.handleSecurityForm(e));

            // Boutons d'actions utilisateur
            $(document).on('click', '#export-data-btn', () => this.handleExportData());
            $(document).on('click', '#delete-data-btn', () => this.handleDeleteData());
            $(document).on('click', '#portability-btn', () => this.handleDataPortability());

            // Boutons d'audit
            $(document).on('click', '#refresh-audit', () => this.refreshAuditLog());
            $(document).on('click', '#export-audit', () => this.exportAuditLog());

            // Gestion des consentements individuels
            $(document).on('change', '.consent-toggle', (e) => this.handleConsentToggle(e));
        }

        handleTabSwitch(e) {
            e.preventDefault();

            const tab = $(e.currentTarget).data('tab');

            // Retirer la classe active de tous les onglets
            $('.gdpr-tab').removeClass('active');
            // Ajouter la classe active à l'onglet cliqué
            $(e.currentTarget).addClass('active');

            // Masquer tous les contenus d'onglets
            $('.gdpr-tab-content').removeClass('active');
            // Afficher le contenu de l'onglet sélectionné
            $('#' + tab + '-tab').addClass('active');
        }

        handleConsentForm(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            formData.append('action', 'pdf_builder_save_gdpr_settings');
            formData.append('nonce', pdfBuilderGDPR.nonce);

            this.showLoading();

            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.hideLoading();
                    if (response.success) {
                        this.showSuccess(response.data.message);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showError(pdfBuilderGDPR.strings.error);
                }
            });
        }

        handleSecurityForm(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            formData.append('action', 'pdf_builder_save_gdpr_security');
            formData.append('nonce', pdfBuilderGDPR.nonce);

            this.showLoading();

            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.hideLoading();
                    if (response.success) {
                        this.showSuccess(response.data.message);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showError(pdfBuilderGDPR.strings.error);
                }
            });
        }

        handleExportData() {
            if (!confirm('Êtes-vous sûr de vouloir exporter toutes vos données ?')) {
                return;
            }

            this.showLoading();

            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_export_user_data',
                    nonce: pdfBuilderGDPR.nonce
                },
                success: (response) => {
                    this.hideLoading();
                    if (response.success) {
                        this.showSuccess(response.data.message);
                        // Télécharger le fichier
                        if (response.data.download_url) {
                            window.open(response.data.download_url, '_blank');
                        }
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showError(pdfBuilderGDPR.strings.error);
                }
            });
        }

        handleDeleteData() {
            if (!confirm(pdfBuilderGDPR.strings.confirm_delete)) {
                return;
            }

            this.showLoading();

            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_delete_user_data',
                    nonce: pdfBuilderGDPR.nonce
                },
                success: (response) => {
                    this.hideLoading();
                    if (response.success) {
                        this.showSuccess(response.data.message);
                        // Rediriger après suppression
                        setTimeout(() => {
                            window.location.href = admin_url('index.php');
                        }, 2000);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showError(pdfBuilderGDPR.strings.error);
                }
            });
        }

        handleDataPortability() {
            const format = $('#portability-format').val() || 'json';

            this.showLoading();

            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_request_data_portability',
                    nonce: pdfBuilderGDPR.nonce,
                    format: format
                },
                success: (response) => {
                    this.hideLoading();
                    if (response.success) {
                        this.showSuccess(response.data.message);
                        // Afficher les données ou proposer le téléchargement
                        this.displayPortableData(response.data.data, format);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showError(pdfBuilderGDPR.strings.error);
                }
            });
        }

        handleConsentToggle(e) {
            const consentType = $(e.target).data('consent-type');
            const granted = $(e.target).is(':checked');

            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_save_consent',
                    nonce: pdfBuilderGDPR.nonce,
                    consent_type: consentType,
                    granted: granted
                },
                success: (response) => {
                    if (response.success) {
                        this.showSuccess(response.data.message);
                    } else {
                        this.showError(response.data.message);
                        // Remettre l'état précédent
                        $(e.target).prop('checked', !granted);
                    }
                },
                error: () => {
                    this.showError(pdfBuilderGDPR.strings.error);
                    // Remettre l'état précédent
                    $(e.target).prop('checked', !granted);
                }
            });
        }

        refreshAuditLog() {
            this.showLoading();

            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_refresh_audit_log',
                    nonce: pdfBuilderGDPR.nonce
                },
                success: (response) => {
                    this.hideLoading();
                    if (response.success) {
                        $('.audit-table tbody').html(response.data.html);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showError(pdfBuilderGDPR.strings.error);
                }
            });
        }

        exportAuditLog() {
            const startDate = $('#audit-start-date').val();
            const endDate = $('#audit-end-date').val();

            const url = pdfBuilderGDPR.ajax_url + '?action=pdf_builder_export_audit_log&nonce=' + pdfBuilderGDPR.nonce;
            if (startDate) url += '&start_date=' + startDate;
            if (endDate) url += '&end_date=' + endDate;

            window.open(url, '_blank');
        }

        loadConsentStatus() {
            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_get_consent_status',
                    nonce: pdfBuilderGDPR.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateConsentUI(response.data.consents);
                    }
                }
            });
        }

        updateConsentUI(consents) {
            Object.keys(consents).forEach(type => {
                const checkbox = $(`.consent-toggle[data-consent-type="${type}"]`);
                if (checkbox.length) {
                    checkbox.prop('checked', consents[type].granted);
                }
            });
        }

        displayPortableData(data, format) {
            if (format === 'json') {
                const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'user-data-portability.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } else {
                // Pour XML ou autres formats
                const blob = new Blob([data], {type: 'application/xml'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'user-data-portability.xml';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
        }

        showLoading() {
            if (!$('.gdpr-loading-overlay').length) {
                $('body').append('<div class="gdpr-loading-overlay"><div class="gdpr-spinner"></div></div>');
            }
            $('.gdpr-loading-overlay').show();
        }

        hideLoading() {
            $('.gdpr-loading-overlay').hide();
        }

        showSuccess(message) {
            this.showNotice(message, 'success');
        }

        showError(message) {
            this.showNotice(message, 'error');
        }

        showNotice(message, type) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const notice = $(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);

            $('.wrap > h1').after(notice);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                notice.fadeOut(() => notice.remove());
            }, 5000);

            // Dismissible functionality
            notice.on('click', '.notice-dismiss', function() {
                notice.fadeOut(() => notice.remove());
            });
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        if (typeof pdfBuilderGDPR !== 'undefined') {
            new PDFBuilderGDPR();
        }
    });

})(jQuery);