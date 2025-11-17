/**
 * PDF Builder Pro - Developer Tools
 * Outils de développement et gestion des licences
 */

(function($) {
    'use strict';

    class PDFBuilderDeveloper {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();

            // Expose test function globally for debugging
            window.testLicenseToggle = () => this.testToggleLicenseMode();
            console.log('DEVELOPER TOOLS: testLicenseToggle() function exposed globally. Call it from console to test license mode toggle.');
        }

        bindEvents() {
            // License test mode toggle
            $(document).on('click', '.toggle-license-test-mode', (e) => this.handleToggleLicenseTestMode(e));

            // Generate test license key
            $(document).on('click', '.generate-test-license-key', (e) => this.handleGenerateTestKey(e));

            // Validate test license key
            $(document).on('click', '.validate-test-license-key', (e) => this.handleValidateTestKey(e));

            // Delete test license key
            $(document).on('click', '.delete-test-license-key', (e) => this.handleDeleteTestKey(e));

            // Cleanup license
            $(document).on('click', '.cleanup-license', (e) => this.handleCleanupLicense(e));
        }

        // Test function for license test mode toggle (accessible from console)
        testToggleLicenseMode() {
            console.log('LICENSE TEST JS: Starting toggle test mode');

            // Update UI immediately for better UX
            var $checkbox = $('#license_test_mode');
            var $status = $('#license_test_mode_status');
            var isChecked = $checkbox.is(':checked');

            $checkbox.prop('checked', !isChecked);

            if (!isChecked) {
                $status.html('✅ MODE TEST ACTIF').css({
                    'background': '#d4edda',
                    'color': '#155724'
                });
            } else {
                $status.html('❌ Mode test inactif').css({
                    'background': '#f8d7da',
                    'color': '#721c24'
                });
            }

            // Use the correct AJAX variable based on what's available
            var ajaxData = {
                action: 'pdf_builder_toggle_license_test_mode'
            };

            var ajaxUrl, ajaxNonce;
            if (typeof pdfBuilderAjax !== 'undefined') {
                ajaxUrl = pdfBuilderAjax.ajaxurl;
                ajaxNonce = pdfBuilderAjax.nonce;
                ajaxData.nonce = ajaxNonce;
            } else if (typeof pdf_builder_ajax !== 'undefined') {
                ajaxUrl = pdf_builder_ajax.ajax_url;
                // Try to get the specific nonce first, fallback to general nonce
                var specificNonce = $('#toggle_license_test_mode_nonce').val();
                if (specificNonce) {
                    ajaxData.nonce = specificNonce;
                } else {
                    ajaxData.security = pdf_builder_ajax.nonce;
                }
            } else {
                console.error('LICENSE TEST JS: No AJAX configuration found');
                this.showError('Configuration AJAX manquante');
                return;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: (response) => {
                    console.log('LICENSE TEST JS: Success response:', response);
                    if (response.success) {
                        console.log('LICENSE TEST JS: Mode toggled successfully:', response.data);
                        this.showSuccess(response.data.message);
                    } else {
                        console.error('LICENSE TEST JS: Server returned error:', response.data);
                        this.showError(response.data.message);
                        // Revert UI change on error
                        $checkbox.prop('checked', isChecked);
                        if (isChecked) {
                            $status.html('✅ MODE TEST ACTIF').css({
                                'background': '#d4edda',
                                'color': '#155724'
                            });
                        } else {
                            $status.html('❌ Mode test inactif').css({
                                'background': '#f8d7da',
                                'color': '#721c24'
                            });
                        }
                    }
                },
                error: (xhr, status, error) => {
                    console.error('LICENSE TEST JS: AJAX error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    this.showError('Erreur AJAX lors du toggle du mode test');
                    // Revert UI change on error
                    $checkbox.prop('checked', isChecked);
                    if (isChecked) {
                        $status.html('✅ MODE TEST ACTIF').css({
                            'background': '#d4edda',
                            'color': '#155724'
                        });
                    } else {
                        $status.html('❌ Mode test inactif').css({
                            'background': '#f8d7da',
                            'color': '#721c24'
                        });
                    }
                }
            });
        }

        handleToggleLicenseTestMode(e) {
            e.preventDefault();
            console.log('DEVELOPER TOOLS: Toggle license test mode clicked');

            this.testToggleLicenseMode();
        }

        handleGenerateTestKey(e) {
            e.preventDefault();
            console.log('DEVELOPER TOOLS: Generate test key clicked');

            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_generate_test_license_key',
                    nonce: pdfBuilderAjax.nonce
                },
                success: (response) => {
                    console.log('DEVELOPER TOOLS: Generate key response:', response);
                    if (response.success) {
                        this.showSuccess(response.data.message);
                        // Update UI with the generated key
                        $('.test-license-key-display').text(response.data.key);
                        $('.test-license-expires-display').text(response.data.expires);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('DEVELOPER TOOLS: Generate key AJAX error:', error);
                    this.showError('Erreur lors de la génération de la clé de test');
                }
            });
        }

        handleValidateTestKey(e) {
            e.preventDefault();
            console.log('DEVELOPER TOOLS: Validate test key clicked');

            const keyToValidate = $('.test-license-key-input').val();
            if (!keyToValidate) {
                this.showError('Veuillez entrer une clé de test à valider');
                return;
            }

            $.ajax({
                url: pdfBuilderGDPR.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_validate_test_license_key',
                    nonce: pdfBuilderGDPR.nonce,
                    key: keyToValidate
                },
                success: (response) => {
                    console.log('DEVELOPER TOOLS: Validate key response:', response);
                    if (response.success) {
                        this.showSuccess(response.data.message);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('DEVELOPER TOOLS: Validate key AJAX error:', error);
                    this.showError('Erreur lors de la validation de la clé de test');
                }
            });
        }

        handleDeleteTestKey(e) {
            e.preventDefault();
            console.log('DEVELOPER TOOLS: Delete test key clicked');

            if (!confirm('Êtes-vous sûr de vouloir supprimer la clé de test ?')) {
                return;
            }

            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_delete_test_license_key',
                    nonce: pdfBuilderAjax.nonce
                },
                success: (response) => {
                    console.log('DEVELOPER TOOLS: Delete key response:', response);
                    if (response.success) {
                        this.showSuccess(response.data.message);
                        // Clear UI
                        $('.test-license-key-display').text('');
                        $('.test-license-expires-display').text('');
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('DEVELOPER TOOLS: Delete key AJAX error:', error);
                    this.showError('Erreur lors de la suppression de la clé de test');
                }
            });
        }

        handleCleanupLicense(e) {
            e.preventDefault();
            console.log('DEVELOPER TOOLS: Cleanup license clicked');

            if (!confirm('Êtes-vous sûr de vouloir nettoyer complètement la licence ? Toutes les données de licence seront supprimées.')) {
                return;
            }

            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_cleanup_license',
                    nonce: pdfBuilderAjax.nonce
                },
                success: (response) => {
                    console.log('DEVELOPER TOOLS: Cleanup license response:', response);
                    if (response.success) {
                        this.showSuccess(response.data.message);
                        // Reload page to reflect changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('DEVELOPER TOOLS: Cleanup license AJAX error:', error);
                    this.showError('Erreur lors du nettoyage de la licence');
                }
            });
        }

        showSuccess(message) {
            // Use existing notification system or fallback to alert
            if (typeof pdfBuilderAjax !== 'undefined' && pdfBuilderAjax.showSuccess) {
                pdfBuilderAjax.showSuccess(message);
            } else {
                alert('✅ ' + message);
            }
        }

        showError(message) {
            // Use existing notification system or fallback to alert
            if (typeof pdfBuilderAjax !== 'undefined' && pdfBuilderAjax.showError) {
                pdfBuilderAjax.showError(message);
            } else {
                alert('❌ ' + message);
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        // Only initialize if we're on a developer page or if AJAX variables are available
        if (typeof pdfBuilderAjax !== 'undefined' ||
            typeof pdf_builder_ajax !== 'undefined' ||
            window.location.href.indexOf('pdf-builder-developer') !== -1 ||
            window.location.href.indexOf('developer') !== -1 ||
            window.location.href.indexOf('pdf-builder-settings') !== -1) {
            new PDFBuilderDeveloper();
        }
    });

})(jQuery);