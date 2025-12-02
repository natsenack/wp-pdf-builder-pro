<?php
    /**
     * PDF Builder Pro - Logique principale des paramètres (VERSION JS CENTRALISÉE)
     * Traitement des paramètres principal avec JavaScript NETTOYÉ, CENTRALISÉ
     * Mis à jour: 2025-12-02 03:25:00 - VIDAGE CACHE FORCÉ
     * Cache Buster: <?php echo time(); ?>
     * Correction finale: Wrapper Promise pour jQuery.ajax - VERSION 2 - RECHARGEMENT FORCÉ
     */

    // Logs
    error_log('PDF Builder: JS centralisé settings-main.php chargé');

    // Vérifications de sécurité
    if (!defined('ABSPATH')) {
        exit('Accès direct interdit');
    }

    if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) {
        wp_die(__('Vous n\'avez pas la permission d\'accéder à cette page.', 'pdf-builder-pro'));
    }

    // Charger les dépendances
    require_once dirname(__FILE__) . '/settings-styles.php';

    // Activer le système JavaScript centralisé
    $centralized_js_mode = true;
    $notices = [];

    // Charger les paramètres actuels - DIRECTEMENT DES OPTIONS DE BASE DE DONNÉES
    $settings = get_option('pdf_builder_settings', []);
    $company_phone_manual = get_option('pdf_builder_company_phone_manual', '');
    $company_siret = get_option('pdf_builder_company_siret', '');
    $company_vat = get_option('pdf_builder_company_vat', '');
    $company_rcs = get_option('pdf_builder_company_rcs', '');
    $company_capital = get_option('pdf_builder_company_capital', '');

    // DEBUG: Journaliser les valeurs chargées pour le dépannage
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('PARAMÈTRES PDF Builder CHARGÉS:');
        error_log(' - company_siret: ' . $company_siret);
        error_log(' - company_vat: ' . $company_vat);
        error_log(' - company_rcs: ' . $company_rcs);
        error_log(' - company_capital: ' . $company_capital);
    }

    // Ajouter nonce pour AJAX
    wp_nonce_field('pdf_builder_settings', '_wpnonce_pdf_builder');

    // Journaliser l'enregistrement des gestionnaires AJAX
    error_log('PDF Builder: Enregistrement des gestionnaires AJAX...');

    // Traitement de formulaire basique de secours (PAS AJAX)
    if (isset($_POST['submit']) && isset($_POST['_wpnonce'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'pdf_builder_settings')) {
            update_option('pdf_builder_company_phone_manual', sanitize_text_field($_POST['company_phone_manual'] ?? ''));
            update_option('pdf_builder_company_siret', sanitize_text_field($_POST['company_siret'] ?? ''));
            update_option('pdf_builder_company_vat', sanitize_text_field($_POST['company_vat'] ?? ''));
            update_option('pdf_builder_company_rcs', sanitize_text_field($_POST['company_rcs'] ?? ''));
            update_option('pdf_builder_company_capital', sanitize_text_field($_POST['company_capital'] ?? ''));

            $notices[] = '<div class="notice notice-success"><p>Paramètres sauvegardés (mode de secours)!</p></div>';
        }
    }

    // Load centralized JavaScript
?>
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1><?php _e('⚙️ Paramètres PDF Builder Pro (JS CENTRALISÉ)', 'pdf-builder-pro'); ?></h1>
    </header>

    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general">⚙️ Général</a>
        <a href="#licence" class="nav-tab" data-tab="licence">🔑 Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme">🖥️ Système</a>
        <a href="#acces" class="nav-tab" data-tab="acces">🔐 Accès</a>
        <a href="#securite" class="nav-tab" data-tab="securite">🛡️ Sécurité</a>
        <a href="#pdf" class="nav-tab" data-tab="pdf">📄 PDF</a>
        <a href="#contenu" class="nav-tab" data-tab="contenu">🎨 Contenu</a>
        <a href="#templates" class="nav-tab" data-tab="templates">📋 Modèles</a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur">🛠️ Développeur</a>
    </nav>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper">
        <div id="general" class="tab-content active"><?php require_once 'settings-general.php'; ?></div>
        <div id="licence" class="tab-content"><?php require_once 'settings-licence.php'; ?></div>
        <div id="systeme" class="tab-content"><?php require_once 'settings-systeme.php'; ?></div>
        <div id="acces" class="tab-content"><?php require_once 'settings-acces.php'; ?></div>
        <div id="securite" class="tab-content"><?php require_once 'settings-securite.php'; ?></div>
        <div id="pdf" class="tab-content"><?php require_once 'settings-pdf.php'; ?></div>
        <div id="contenu" class="tab-content"><?php require_once 'settings-contenu.php'; ?></div>
        <div id="templates" class="tab-content"><?php require_once 'settings-templates.php'; ?></div>
        <div id="developpeur" class="tab-content"><?php require_once 'settings-developpeur.php'; ?></div>
    </section>

    <aside id="pdf-builder-floating-save">
        <button id="pdf-builder-save-button" class="floating-save-btn" type="button">
            <span class="dashicons dashicons-cloud-upload"></span>
            <span class="btn-text">Enregistrer Tout</span>
            <span class="spinner" style="display: none;"></span>
        </button>
    </aside>
</main>

<?php // Global variables for JS
    $js_config = [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_settings_ajax'),
        'current_settings' => [
            'general' => [
                'company_phone_manual' => $company_phone_manual,
                'company_siret' => $company_siret,
                'company_vat' => $company_vat,
                'company_rcs' => $company_rcs,
                'company_capital' => $company_capital
            ]
        ],
        'debug_mode' => defined('WP_DEBUG') && WP_DEBUG
    ];
?>

<script>
    /**
     * SYSTÈME JAVASCRIPT CENTRALISÉ DES PARAMÈTRES PDF BUILDER
     * Propre, organisé et maintenable
     * Version: 2025-12-02-03-25-00 - VIDAGE CACHE FORCÉ
     * Cache Buster: <?php echo time(); ?>
     * Correction finale: Wrapper Promise pour jQuery.ajax
     */

    (function() {
        'use strict';

        // Configuration globale depuis PHP
        const PDF_BUILDER_CONFIG = <?php echo json_encode($js_config); ?>;

        // Initialiser les fonctions de notification si pas déjà définies
        if (typeof window.showSuccessNotification === 'undefined') {
            // Initialiser le système de notification centralisé
            window.simpleNotificationSystem = {
                container: null,
                
                init: function() {
                    if (!this.container) {
                        this.container = document.createElement('div');
                        this.container.id = 'pdf-builder-notifications';
                        this.container.style.cssText = `
                            position: fixed;
                            top: 40px;
                            right: 20px;
                            z-index: 9999;
                            max-width: 400px;
                            pointer-events: none;
                        `;
                        document.body.appendChild(this.container);
                    }
                },
                
                show: function(message, type = 'info') {
                    this.init();
                    
                    const notification = document.createElement('div');
                    notification.className = `pdf-notification pdf-notification-${type}`;
                    notification.style.cssText = `
                        background: white;
                        border-left: 4px solid ${this.getColor(type)};
                        border-radius: 4px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        margin-bottom: 10px;
                        padding: 12px 16px;
                        opacity: 0;
                        transform: translateX(100%);
                        transition: all 0.3s ease;
                        pointer-events: auto;
                        position: relative;
                    `;
                    
                    notification.innerHTML = `
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="flex: 1; font-size: 14px; color: #333;">${message}</span>
                            <button class="pdf-notification-close" style="
                                background: none;
                                border: none;
                                color: #999;
                                cursor: pointer;
                                font-size: 18px;
                                line-height: 1;
                                margin-left: 10px;
                                padding: 0;
                            ">&times;</button>
                        </div>
                    `;
                    
                    this.container.appendChild(notification);
                    
                    // Animer l'entrée
                    setTimeout(() => {
                        notification.style.opacity = '1';
                        notification.style.transform = 'translateX(0)';
                    }, 10);
                    
                    // Suppression automatique
                    const removeNotification = () => {
                        notification.style.opacity = '0';
                        notification.style.transform = 'translateX(100%)';
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    };
                    
                    // Bouton de fermeture
                    notification.querySelector('.pdf-notification-close').addEventListener('click', removeNotification);
                    
                    // Suppression automatique après 5 secondes
                    setTimeout(removeNotification, 5000);
                },
                
                getColor: function(type) {
                    const colors = {
                        success: '#46b450',
                        error: '#dc3232',
                        warning: '#f56e28',
                        info: '#00a0d2'
                    };
                    return colors[type] || colors.info;
                },
                
                success: function(message) {
                    this.show(message, 'success');
                },
                
                error: function(message) {
                    this.show(message, 'error');
                },
                
                warning: function(message) {
                    this.show(message, 'warning');
                },
                
                info: function(message) {
                    this.show(message, 'info');
                }
            };

            window.showSuccessNotification = function(message) {
                console.log('✅ Succès:', message);
                window.simpleNotificationSystem.success(message);
            };

            window.showErrorNotification = function(message) {
                console.error('❌ Erreur:', message);
                window.simpleNotificationSystem.error(message);
            };

            window.showWarningNotification = function(message) {
                console.warn('⚠️ Avertissement:', message);
                window.simpleNotificationSystem.warning(message);
            };

            window.showInfoNotification = function(message) {
                console.log('ℹ️ Info:', message);
                window.simpleNotificationSystem.info(message);
            };
        }

        /**
         * CLASSE CONTRÔLEUR PRINCIPAL DES PARAMÈTRES
         * Contrôle centralisé pour toutes les fonctionnalités des paramètres
         */
        class PDF_Builder_Settings_Controller {
            constructor() {
                this.config = PDF_BUILDER_CONFIG;
                this.Validator = new SettingsValidator();
                this.ShapeManager = new SettingsUI();
                this.SaveManager = new SettingsSaver();
                this.PreviewManager = new SettingsPreview();
            }

            init() {
                console.log('🎯 Initialisation du système centralisé des paramètres PDF Builder');

                // Initialiser tous les composants
                this.bindTabEvents();
                this.bindSaveEvents();
                this.initializePreviews();

                console.log('✅ Système de paramètres PDF Builder initialisé');
            }

            bindTabEvents() {
                const tabs = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
                tabs.forEach(tab => {
                    tab.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.switchTab(tab.dataset.tab);
                    });
                });
            }

            switchTab(tabId) {
                // Update tabs
                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
                document.querySelector(`[data-tab="${tabId}"]`).classList.add('nav-tab-active');

                // Update content
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');

                // Update save button text
                if (this.SaveManager) {
                    this.SaveManager.updateButtonText(tabId);
                }
            }

            bindSaveEvents() {
                this.SaveManager.bindEvents();
            }

            initializePreviews() {
                this.PreviewManager.init();
            }
        }

        /**
         * CLASSE VALIDATEUR DES PARAMÈTRES
         * Système de validation centralisé
         */
        class SettingsValidator {
            constructor() {
                this.rules = {
                    phone: {
                        pattern: /^[\d\s\-\+\(\)]{10,}$/,
                        message: 'Numéro de téléphone invalide'
                    },
                    siret: {
                        pattern: /^\d{14}$/,
                        message: 'Le SIRET doit contenir exactement 14 chiffres'
                    },
                    vat: {
                        pattern: /^[A-Z]{2}[\w\d]{8,12}$/i,
                        message: 'Format de numéro TVA invalide'
                    }
                };
            }

            validateField(field, value) {
                const rule = this.rules[field];
                if (!rule) return true; // Aucune règle de validation

                return rule.pattern.test(value) || value === '';
            }

            getValidationMessage(field) {
                return this.rules[field]?.message || '';
            }

            validateTab(tabId) {
                const form = document.querySelector(`#${tabId} form`);
                if (!form) return true;

                let isValid = true;
                const inputs = form.querySelectorAll('input, select, textarea');

                inputs.forEach(input => {
                    if (input.name && input.value) {
                        if (!this.validateField(input.name.replace('company_', ''), input.value)) {
                            this.showFieldError(input, this.getValidationMessage(input.name.replace('company_', '')));
                            isValid = false;
                        } else {
                            this.clearFieldError(input);
                        }
                    }
                });

                return isValid;
            }

            showFieldError(input, message) {
                input.classList.add('field-error');
                let errorDiv = input.parentNode.querySelector('.field-error-msg');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error-msg';
                    input.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = message;
            }

            clearFieldError(input) {
                input.classList.remove('field-error');
                const errorDiv = input.parentNode.querySelector('.field-error-msg');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
        }

        /**
         * GESTIONNAIRE UI DES PARAMÈTRES
         * Gère toutes les mises à jour et interactions UI
         */
        class SettingsUI {
            constructor() {
                this.buttons = {};
            }

            setButtonState(button, state) {
                const btnText = button.querySelector('.btn-text');
                const spinner = button.querySelector('.spinner');

                button.disabled = state === 'loading';

                if (btnText) {
                    btnText.style.display = state === 'loading' ? 'none' : 'inline';
                }
                if (spinner) {
                    spinner.style.display = state === 'loading' ? 'inline-block' : 'none';
                }
            }
        }

        /**
         * SAUVEGARDEUR DES PARAMÈTRES
         * Fonctionnalité de sauvegarde centralisée pour tous les onglets
         */
        class SettingsSaver {
            constructor() {
                this.validator = null;
                this.ui = null;
                this.saveButton = document.getElementById('pdf-builder-save-button');
            }

            setDependencies(validator, ui) {
                this.validator = validator;
                this.ui = ui;
            }

            bindEvents() {
                if (this.saveButton) {
                    this.saveButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.saveAllSettings();
                    });
                }

                // Bind individual form submits
                document.addEventListener('submit', (e) => {
                    if (e.target.closest('.tab-content')) {
                        e.preventDefault();
                        this.saveTabSettings(e.target.closest('.tab-content').id);
                    }
                });
            }

            async saveAllSettings() {
                console.log('💾 Sauvegarde de tous les paramètres...');

                if (this.ui && this.saveButton) {
                    this.ui.setButtonState(this.saveButton, 'loading');
                }

                // Stocker le contexte et les fonctions de notification pour éviter les problèmes 'this' dans les callbacks
                const self = this;
                const showSuccess = window.showSuccessNotification;
                const showError = window.showErrorNotification;

                try {
                    const formData = this.collectAllSettings();

                    // LOGS DEBUG POUR LE TOGGLE DEBUG JAVASCRIPT
                    if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                        console.log('🚀 [DEBUG JS TOGGLE] Données collectées avant envoi:', formData);
                        console.log('🚀 [DEBUG JS TOGGLE] debug_javascript dans formData:', formData['pdf_builder_debug_javascript'] || 'NON TROUVÉ');
                        console.log('🚀 [DEBUG JS TOGGLE] debug_javascript dans formData (sans prefixe):', formData['debug_javascript'] || 'NON TROUVÉ');
                        
                        // Vérifier si le champ est dans les données AJAX
                        const ajaxData = {
                            'action': 'pdf_builder_save_all_settings',
                            'nonce': PDF_BUILDER_CONFIG.nonce,
                            ...formData
                        };
                        console.log('🚀 [DEBUG JS TOGGLE] Données AJAX complètes:', ajaxData);
                    }

                    // Envoyer au serveur en utilisant jQuery AJAX wrappé dans Promise
                    // CORRIGÉ: Utilisation de fonctions fléchées pour préserver le contexte
                    const response = await new Promise((resolve, reject) => {
                        jQuery.ajax({
                            url: PDF_BUILDER_CONFIG.ajax_url,
                            type: 'POST',
                            data: {
                                'action': 'pdf_builder_save_all_settings',
                                'nonce': PDF_BUILDER_CONFIG.nonce,
                                ...formData
                            },
                            dataType: 'json',
                            success: (data) => {
                                console.log('[AJAX Succès] Réponse reçue:', data);
                                
                                // LOGS DEBUG POUR LE TOGGLE DEBUG JAVASCRIPT
                                if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                                    console.log('✅ [DEBUG JS TOGGLE] Réponse serveur reçue:', data);
                                    console.log('✅ [DEBUG JS TOGGLE] debug_javascript dans saved_settings:', data.data?.saved_settings?.debug_javascript || 'NON TROUVÉ');
                                    console.log('✅ [DEBUG JS TOGGLE] debug_javascript dans saved_settings (avec prefixe):', data.data?.saved_settings?.pdf_builder_debug_javascript || 'NON TROUVÉ');
                                }
                                
                                resolve(data);
                            },
                            error: (xhr, status, error) => {
                                console.error('[AJAX Erreur] Détails de l\'erreur:', {status, error, responseText: xhr.responseText});
                                
                                // LOGS DEBUG POUR LE TOGGLE DEBUG JAVASCRIPT
                                if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                                    console.error('❌ [DEBUG JS TOGGLE] Erreur AJAX:', {status, error, responseText: xhr.responseText});
                                }
                                
                                reject(new Error(error || 'Échec de la requête AJAX'));
                            }
                        });
                    });

                    if (response.success) {
                        console.log('✅ Tous les paramètres sauvegardés avec succès!');
                        if (typeof showSuccess === 'function') {
                            showSuccess('✅ Tous les paramètres sauvegardés avec succès!');
                        }
                    } else {
                        throw new Error(response.data?.message || 'Échec de la sauvegarde');
                    }

                } catch (error) {
                    console.error('Erreur de sauvegarde:', error);
                    if (typeof showError === 'function') {
                        showError('❌ Erreur lors de la sauvegarde des paramètres: ' + error.message);
                    }
                } finally {
                    if (this.ui && this.saveButton) {
                        this.ui.setButtonState(this.saveButton, 'reset');
                    }
                }
            }

            async saveTabSettings(tabId) {
                console.log('💾 Sauvegarde des paramètres de l\'onglet:', tabId);

                if (this.validator && !this.validator.validateTab(tabId)) {
                    if (window.showErrorNotification) {
                        window.showErrorNotification('❌ Veuillez corriger les erreurs de validation');
                    }
                    return;
                }

                // Stocker les fonctions de notification pour éviter les problèmes de contexte
                const showSuccess = window.showSuccessNotification;
                const showError = window.showErrorNotification;
                const formData = this.collectTabSettings(tabId);

                try {
                    const response = await new Promise((resolve, reject) => {
                        jQuery.ajax({
                            url: PDF_BUILDER_CONFIG.ajax_url,
                            type: 'POST',
                            data: {
                                'action': 'pdf_builder_save_tab_settings',
                                'tab': tabId,
                                'nonce': PDF_BUILDER_CONFIG.nonce,
                                ...formData
                            },
                            dataType: 'json',
                            success: (data) => {
                                console.log('[AJAX Succès] Réponse de sauvegarde d\'onglet:', data);
                                resolve(data);
                            },
                            error: (xhr, status, error) => {
                                console.error('[AJAX Erreur] Échec de la sauvegarde d\'onglet:', {status, error});
                                reject(new Error(error || 'Échec de la requête AJAX'));
                            }
                        });
                    });

                    if (response.success && typeof showSuccess === 'function') {
                        showSuccess(`✅ Paramètres ${tabId.charAt(0).toUpperCase() + tabId.slice(1)} sauvegardés!`);
                    }

                } catch (error) {
                    console.error('Erreur de sauvegarde d\'onglet:', error);
                    if (typeof showError === 'function') {
                        showError('❌ Erreur lors de la sauvegarde des paramètres');
                    }
                }
            }

            collectAllSettings() {
                const formData = {};

                // Collecter depuis tous les contenus d'onglets
                document.querySelectorAll('.tab-content input, .tab-content select, .tab-content textarea').forEach(field => {
                    if (field.name) {
                        if (field.type === 'checkbox') {
                            formData[field.name] = field.checked ? '1' : '0';
                        } else {
                            formData[field.name] = field.value;
                        }
                    }
                });

                // LOGS DEBUG POUR LE TOGGLE DEBUG JAVASCRIPT
                if (window.pdfBuilderCanvasSettings?.debug?.javascript) {
                    console.log('🔍 [DEBUG JS TOGGLE] CollectAllSettings - Champs collectés:', Object.keys(formData));
                    console.log('🔍 [DEBUG JS TOGGLE] debug_javascript dans formData:', formData['pdf_builder_debug_javascript'] || 'NON TROUVÉ');
                    console.log('🔍 [DEBUG JS TOGGLE] debug_javascript dans formData (sans prefixe):', formData['debug_javascript'] || 'NON TROUVÉ');
                    
                    // Chercher tous les champs liés au debug
                    const debugFields = Object.keys(formData).filter(key => key.includes('debug'));
                    console.log('🔍 [DEBUG JS TOGGLE] Tous les champs debug trouvés:', debugFields);
                    
                    // Vérifier l'élément DOM directement
                    const debugJsElement = document.getElementById('debug_javascript');
                    if (debugJsElement) {
                        console.log('🔍 [DEBUG JS TOGGLE] Élément DOM trouvé - checked:', debugJsElement.checked, 'value:', debugJsElement.value);
                    } else {
                        console.log('🔍 [DEBUG JS TOGGLE] Élément DOM NON trouvé');
                    }
                }

                return formData;
            }

            collectTabSettings(tabId) {
                const formData = {};
                const tabContent = document.getElementById(tabId);

                if (tabContent) {
                    const fields = tabContent.querySelectorAll('input, select, textarea');
                    fields.forEach(field => {
                        if (field.name) {
                            if (field.type === 'checkbox') {
                                formData[field.name] = field.checked ? '1' : '0';
                            } else {
                                formData[field.name] = field.value;
                            }
                        }
                    });
                }

                return formData;
            }

            updateButtonText(tabId) {
                const tabNames = {
                    'general': 'Enregistrer Général',
                    'licence': 'Enregistrer Licence',
                    'systeme': 'Enregistrer Système',
                    'acces': 'Enregistrer Accès',
                    'securite': 'Enregistrer Sécurité',
                    'pdf': 'Enregistrer PDF',
                    'contenu': 'Enregistrer Contenu',
                    'templates': 'Enregistrer Modèles',
                    'developpeur': 'Enregistrer Développeur'
                };

                const btnText = this.saveButton?.querySelector('.btn-text');
                if (btnText) {
                    btnText.textContent = tabNames[tabId] || 'Enregistrer Tout';
                }
            }
        }

        /**
         * GESTIONNAIRE D'APERÇU DES PARAMÈTRES
         * Gère les aperçus en direct pour les paramètres
         */
        class SettingsPreview {
            constructor() {
                this.previews = {};
            }

            init() {
                // Aperçu téléphone
                this.bindPreview('company_phone_manual', '.company-phone-preview', (val) => val);

                // Aperçu SIRET
                this.bindPreview('company_siret', '.company-siret-preview', (val) => val);

                // Aperçu TVA
                this.bindPreview('company_vat', '.company-vat-preview', (val) => val);

                // Aperçu RCS
                this.bindPreview('company_rcs', '.company-rcs-preview', (val) => val);

                // Aperçu capital
                this.bindPreview('company_capital', '.company-capital-preview', (val) => val ? val + ' €' : '');
            }

            bindPreview(fieldName, selector, formatter = (val) => val) {
                const input = document.querySelector(`input[name="${fieldName}"]`);
                const preview = document.querySelector(selector);

                if (!input || !preview) return;

                // Initial value
                preview.textContent = formatter(input.value);

                // Live updates
                input.addEventListener('input', () => {
                    preview.textContent = formatter(input.value);
                });
            }
        }

        // Initialize the system when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const settingsController = new PDF_Builder_Settings_Controller();
            settingsController.init();

            // Connect components
            settingsController.SaveManager.setDependencies(
                settingsController.Validator,
                settingsController.ShapeManager
            );
        });

    })();
</script>

<style>
    /* Styles centralisés des paramètres */
    .tab-content-wrapper {
        margin-top: 20px;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .floating-save-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #007cba;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 16px 24px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,123,186,0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 1000;
    }

    .floating-save-btn:hover {
        background: #005a87;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,123,186,0.4);
    }

    .floating-save-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .field-error {
        border-color: #dc3232 !important;
        box-shadow: 0 0 0 1px #dc3232 !important;
    }

    .field-error-msg {
        color: #dc3232;
        font-size: 12px;
        margin-top: 4px;
    }
</style>
<?php

    // Inclure le diagnostic si nécessaire
    if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
        require_once __DIR__ . '/tab-diagnostic.php';
    }

    // GESTIONNAIRES AJAX - Fonctionnalité de sauvegarde centralisée gérée dans settings-ajax.php

    add_action('wp_ajax_pdf_builder_save_tab_settings', function() {
        try {
            // Vérifier le nonce
            if (!wp_verify_nonce(sanitize_text_field($_POST['nonce'] ?? ''), 'pdf_builder_settings_ajax')) {
                wp_send_json_error(['message' => 'Échec de vérification de sécurité']);
                return;
            }

            $tab = sanitize_text_field($_POST['tab'] ?? 'unknown');

            // Collecter les données spécifiques à l'onglet
            $updated_fields = [];
            foreach ($_POST as $key => $value) {
                if (in_array($key, ['action', 'nonce', 'tab'])) continue;
                $updated_fields[$key] = sanitize_text_field($value);
            }

            // Traiter selon l'onglet
            switch ($tab) {
                case 'general':
                    update_option('pdf_builder_company_phone_manual', $updated_fields['company_phone_manual'] ?? '');
                    update_option('pdf_builder_company_siret', $updated_fields['company_siret'] ?? '');
                    update_option('pdf_builder_company_vat', $updated_fields['company_vat'] ?? '');
                    update_option('pdf_builder_company_rcs', $updated_fields['company_rcs'] ?? '');
                    update_option('pdf_builder_company_capital', $updated_fields['company_capital'] ?? '');
                    break;
                case 'acces':
                    // Gérer les rôles d'accès (tableau de rôles)
                    if (isset($_POST['pdf_builder_allowed_roles']) && is_array($_POST['pdf_builder_allowed_roles'])) {
                        $allowed_roles = array_map('sanitize_text_field', $_POST['pdf_builder_allowed_roles']);
                        // Toujours inclure administrator
                        if (!in_array('administrator', $allowed_roles)) {
                            $allowed_roles[] = 'administrator';
                        }
                        update_option('pdf_builder_allowed_roles', $allowed_roles);
                    }
                    break;
                default:
                    // Gestionnaire de paramètres général pour les autres onglets
                    foreach ($updated_fields as $key => $value) {
                        if (strpos($key, 'pdf_builder_') === 0 || strpos($key, 'systeme_') === 0) {
                            update_option('pdf_builder_' . str_replace(['pdf_builder_', 'systeme_'], '', $key), $value);
                        }
                    }
                    break;
            }

            wp_send_json_success([
                'message' => ucfirst($tab) . ' paramètres sauvegardés avec succès',
                'tab' => $tab
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    });

    add_action('wp_ajax_pdf_builder_deactivate_license', function() {
        try {
            // Vérifier le nonce
            if (!wp_verify_nonce(sanitize_text_field($_POST['nonce'] ?? ''), 'pdf_builder_deactivate')) {
                wp_send_json_error(['message' => 'Échec de vérification de sécurité']);
                return;
            }

            // Vérifier la capacité utilisateur
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Effacer toutes les données de licence
            delete_option('pdf_builder_license_key');
            delete_option('pdf_builder_license_status');
            delete_option('pdf_builder_license_expires');
            delete_option('pdf_builder_license_activated_at');
            delete_option('pdf_builder_license_test_key');
            delete_option('pdf_builder_license_test_key_expires');
            delete_option('pdf_builder_license_test_mode_enabled');

            // Réinitialiser en mode gratuit
            update_option('pdf_builder_license_status', 'free');

            error_log('PDF Builder: Licence désactivée avec succès via AJAX');

            wp_send_json_success([
                'message' => 'Licence désactivée avec succès',
                'status' => 'free'
            ]);

        } catch (Exception $e) {
            error_log('PDF Builder: Erreur de désactivation de licence - ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    });

    // Gestionnaire AJAX des paramètres développeur
    error_log('PDF Builder: Enregistrement du gestionnaire AJAX des paramètres développeur à la ligne ' . __LINE__);
    add_action('wp_ajax_pdf_builder_developer_save_settings', function() {
        error_log('PDF Builder Développeur: Gestionnaire AJAX DÉMARRÉ à ' . date('Y-m-d H:i:s'));

        try {
            // Journaliser toutes les données POST pour le débogage
            error_log('PDF Builder Développeur: Données POST reçues: ' . print_r($_POST, true));

            // Vérifier le nonce
            $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
            $nonce_valid = wp_verify_nonce($nonce_value, 'pdf_builder_settings_ajax');
            error_log('PDF Builder Développeur: Résultat de vérification du nonce: ' . ($nonce_valid ? 'VALIDE' : 'INVALIDE'));

            if (!$nonce_valid) {
                error_log('PDF Builder Développeur: Échec de vérification du nonce');
                wp_send_json_error(['message' => 'Échec de vérification de sécurité']);
                return;
            }

            // Vérifier la capacité utilisateur
            $has_capability = current_user_can('manage_options');
            error_log('PDF Builder Développeur: Vérification de capacité utilisateur: ' . ($has_capability ? 'A' : 'NON'));

            if (!$has_capability) {
                error_log('PDF Builder Développeur: Permissions insuffisantes');
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Obtenir la clé et la valeur du paramètre
            $setting_key = sanitize_text_field($_POST['setting_key'] ?? '');
            $setting_value = sanitize_text_field($_POST['setting_value'] ?? '');

            error_log("PDF Builder Développeur: Clé paramètre: '{$setting_key}', valeur: '{$setting_value}'");

            // Valider la clé de paramètre (autoriser seulement les paramètres développeur)
            $allowed_keys = [
                'pdf_builder_developer_enabled',
                'pdf_builder_canvas_debug_enabled',
                'pdf_builder_developer_password'
            ];

            if (!in_array($setting_key, $allowed_keys)) {
                error_log("PDF Builder Développeur: Clé paramètre invalide: {$setting_key}");
                wp_send_json_error(['message' => 'Clé paramètre invalide']);
                return;
            }

            // Obtenir les paramètres existants
            $settings = get_option('pdf_builder_settings', []);

            // Mettre à jour le paramètre spécifique
            $settings[$setting_key] = $setting_value;

            // Sauvegarder en base de données
            $updated = update_option('pdf_builder_settings', $settings);
            error_log("PDF Builder Développeur: Résultat update_option: " . ($updated ? 'SUCCÈS' : 'AUCUN CHANGEMENT'));

            wp_send_json_success([
                'message' => 'Paramètre développeur sauvegardé avec succès',
                'setting' => $setting_key,
                'value' => $setting_value
            ]);

        } catch (Exception $e) {
            error_log('PDF Builder Développeur: Erreur AJAX - ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    });
?>

<?php require_once __DIR__ . '/settings-modals.php'; ?>
