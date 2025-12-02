<?php
    /**
     * PDF Builder Pro - Main Settings Logic (CENTRALIZED JS VERSION)
     * Core settings processing with CLEAN, CENTRALIZED JavaScript
     * Updated: 2025-11-18 20:10:00
     */

    // Logs
    error_log('PDF Builder: CENTRALIZED JS settings-main.php loaded');

    // Security checks
    if (!defined('ABSPATH')) {
        exit('Direct access forbidden');
    }

    if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) {
        wp_die(__('You do not have permission to access this page.', 'pdf-builder-pro'));
    }

    // Load dependencies
    require_once dirname(__FILE__) . '/settings-styles.php';

    // Enable centralized JavaScript system
    $centralized_js_mode = true;
    $notices = [];

    // Load current settings - DIRECTLY FROM DATABASE OPTIONS
    $settings = get_option('pdf_builder_settings', []);
    $company_phone_manual = get_option('pdf_builder_company_phone_manual', '');
    $company_siret = get_option('pdf_builder_company_siret', '');
    $company_vat = get_option('pdf_builder_company_vat', '');
    $company_rcs = get_option('pdf_builder_company_rcs', '');
    $company_capital = get_option('pdf_builder_company_capital', '');

    // DEBUG: Log loaded values for troubleshooting
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('PDF Builder SETTINGS LOADED:');
        error_log(' - company_siret: ' . $company_siret);
        error_log(' - company_vat: ' . $company_vat);
        error_log(' - company_rcs: ' . $company_rcs);
        error_log(' - company_capital: ' . $company_capital);
    }

    // Add nonce for AJAX
    wp_nonce_field('pdf_builder_settings', '_wpnonce_pdf_builder');

    // Log AJAX handlers registration
    error_log('PDF Builder: Registering AJAX handlers...');

    // Basic form processing fallback (NO AJAX)
    if (isset($_POST['submit']) && isset($_POST['_wpnonce'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'pdf_builder_settings')) {
            update_option('pdf_builder_company_phone_manual', sanitize_text_field($_POST['company_phone_manual'] ?? ''));
            update_option('pdf_builder_company_siret', sanitize_text_field($_POST['company_siret'] ?? ''));
            update_option('pdf_builder_company_vat', sanitize_text_field($_POST['company_vat'] ?? ''));
            update_option('pdf_builder_company_rcs', sanitize_text_field($_POST['company_rcs'] ?? ''));
            update_option('pdf_builder_company_capital', sanitize_text_field($_POST['company_capital'] ?? ''));

            $notices[] = '<div class="notice notice-success"><p>Settings saved (fallback mode)!</p></div>';
        }
    }

    // Load centralized JavaScript
?>
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1><?php _e('⚙️ PDF Builder Pro Settings (JS CENTRALIZED)', 'pdf-builder-pro'); ?></h1>
    </header>

    <div id="pdf-builder-notifications"></div>

    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general">⚙️ General</a>
        <a href="#licence" class="nav-tab" data-tab="licence">🔑 Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme">🖥️ System</a>
        <a href="#acces" class="nav-tab" data-tab="acces">🔐 Access</a>
        <a href="#securite" class="nav-tab" data-tab="securite">🛡️ Security</a>
        <a href="#pdf" class="nav-tab" data-tab="pdf">📄 PDF</a>
        <a href="#contenu" class="nav-tab" data-tab="contenu">🎨 Content</a>
        <a href="#templates" class="nav-tab" data-tab="templates">📋 Templates</a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur">🛠️ Developer</a>
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
     * CENTRALIZED PDF BUILDER SETTINGS JAVASCRIPT SYSTEM
     * Clean, organized, and maintainable
     */

    (function() {
        'use strict';

        // Global config from PHP
        const PDF_BUILDER_CONFIG = <?php echo json_encode($js_config); ?>;

        /**
         * MAIN SETTINGS CONTROLLER CLASS
         * Centralized control for all settings functionality
         */
        class PDF_Builder_Settings_Controller {
            constructor() {
                this.config = PDF_BUILDER_CONFIG;
                this.Validator = new SettingsValidator();
                this.ShapeManager = new SettingsUI();
                this.SaveManager = new SettingsSaver();
                this.Notifier = new SettingsNotifier();
                this.PreviewManager = new SettingsPreview();
            }

            init() {
                console.log('🎯 Initializing Centralized PDF Builder Settings System');

                // Initialize all components
                this.bindTabEvents();
                this.bindSaveEvents();
                this.initializePreviews();

                console.log('✅ PDF Builder Settings System Initialized');
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
         * SETTINGS VALIDATOR CLASS
         * Centralized validation system
         */
        class SettingsValidator {
            constructor() {
                this.rules = {
                    phone: {
                        pattern: /^[\d\s\-\+\(\)]{10,}$/,
                        message: 'Invalid phone number'
                    },
                    siret: {
                        pattern: /^\d{14}$/,
                        message: 'SIRET must be exactly 14 digits'
                    },
                    vat: {
                        pattern: /^[A-Z]{2}[\w\d]{8,12}$/i,
                        message: 'Invalid VAT number format'
                    }
                };
            }

            validateField(field, value) {
                const rule = this.rules[field];
                if (!rule) return true; // No validation rule

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
         * SETTINGS UI MANAGER
         * Handles all UI updates and interactions
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
         * SETTINGS SAVER
         * Centralized save functionality for all tabs
         */
        class SettingsSaver {
            constructor() {
                this.validator = null;
                this.ui = null;
                this.notifier = null;
                this.saveButton = document.getElementById('pdf-builder-save-button');
            }

            setDependencies(validator, ui, notifier) {
                this.validator = validator;
                this.ui = ui;
                this.notifier = notifier;
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
                console.log('💾 Saving all settings...');

                if (this.ui && this.saveButton) {
                    this.ui.setButtonState(this.saveButton, 'loading');
                }

                try {
                    const formData = this.collectAllSettings();

                    // Send to server using jQuery AJAX instead of fetch
                    const response = await jQuery.ajax({
                        url: PDF_BUILDER_CONFIG.ajax_url,
                        type: 'POST',
                        data: {
                            'action': 'pdf_builder_save_all_settings',
                            'nonce': PDF_BUILDER_CONFIG.nonce,
                            ...formData
                        },
                        dataType: 'json'
                    });

                    if (response.success) {
                        if (this.notifier) {
                            this.notifier.show('✅ All settings saved successfully!', 'success');
                        }
                    } else {
                        throw new Error(response.data?.message || 'Save failed');
                    }

                } catch (error) {
                    console.error('Save error:', error);
                    if (this.notifier) {
                        this.notifier.show('❌ Error saving settings: ' + error.message, 'error');
                    }
                } finally {
                    if (this.ui && this.saveButton) {
                        this.ui.setButtonState(this.saveButton, 'reset');
                    }
                }
            }

            async saveTabSettings(tabId) {
                console.log('💾 Saving tab settings:', tabId);

                if (this.validator && !this.validator.validateTab(tabId)) {
                    this.notifier?.show('❌ Please fix validation errors', 'error');
                    return;
                }

                const formData = this.collectTabSettings(tabId);

                try {
                    const response = await jQuery.ajax({
                        url: PDF_BUILDER_CONFIG.ajax_url,
                        type: 'POST',
                        data: {
                            'action': 'pdf_builder_save_tab_settings',
                            'tab': tabId,
                            'nonce': PDF_BUILDER_CONFIG.nonce,
                            ...formData
                        },
                        dataType: 'json'
                    });

                    if (response.success && this.notifier) {
                        this.notifier.show(`✅ ${tabId.charAt(0).toUpperCase() + tabId.slice(1)} settings saved!`, 'success');
                    }

                } catch (error) {
                    console.error('Tab save error:', error);
                    if (this.notifier) {
                        this.notifier.show('❌ Error saving settings', 'error');
                    }
                }
            }

            collectAllSettings() {
                const formData = {};

                // Collect from all tab contents
                document.querySelectorAll('.tab-content input, .tab-content select, .tab-content textarea').forEach(field => {
                    if (field.name) {
                        if (field.type === 'checkbox') {
                            formData[field.name] = field.checked ? '1' : '0';
                        } else {
                            formData[field.name] = field.value;
                        }
                    }
                });

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
                    'general': 'Save General',
                    'licence': 'Save License',
                    'systeme': 'Save System',
                    'acces': 'Save Access',
                    'securite': 'Save Security',
                    'pdf': 'Save PDF',
                    'contenu': 'Save Canvas',
                    'templates': 'Save Templates',
                    'developpeur': 'Save Developer'
                };

                const btnText = this.saveButton?.querySelector('.btn-text');
                if (btnText) {
                    btnText.textContent = tabNames[tabId] || 'Save Everything';
                }
            }
        }

        /**
         * SETTINGS NOTIFIER
         * Centralized notification system
         */
        class SettingsNotifier {
            constructor() {
                this.container = document.getElementById('pdf-builder-notifications');
            }

            show(message, type = 'info') {
                if (!this.container) return;

                const notification = document.createElement('div');
                notification.className = `notice notice-${type} is-dismissible`;
                notification.innerHTML = `
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                `;

                // Add dismiss functionality
                notification.querySelector('.notice-dismiss').addEventListener('click', () => {
                    notification.remove();
                });

                this.container.appendChild(notification);

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 5000);
            }
        }

        /**
         * SETTINGS PREVIEW MANAGER
         * Handles live previews for settings
         */
        class SettingsPreview {
            constructor() {
                this.previews = {};
            }

            init() {
                // Phone preview
                this.bindPreview('company_phone_manual', '.company-phone-preview', (val) => val);

                // SIRET preview
                this.bindPreview('company_siret', '.company-siret-preview', (val) => val);

                // VAT preview
                this.bindPreview('company_vat', '.company-vat-preview', (val) => val);

                // RCS preview
                this.bindPreview('company_rcs', '.company-rcs-preview', (val) => val);

                // Capital preview
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
                settingsController.ShapeManager,
                settingsController.Notifier
            );
        });

    })();
</script>

<style>
    /* Centralized Settings Styles */
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

    /* Notifications styling */
    #pdf-builder-notifications .notice {
        margin: 20px 0;
        padding: 12px 16px;
        border-radius: 4px;
        border-left: 4px solid;
    }

    #pdf-builder-notifications .notice-success {
        border-left-color: #46b450;
        background: #d4edda;
        color: #155724;
    }

    #pdf-builder-notifications .notice-error {
        border-left-color: #dc3232;
        background: #f8d7da;
        color: #721c24;
    }

    #pdf-builder-notifications .notice:notice-info {
        border-left-color: #00a0d2;
        background: #d1ecf1;
        color: #0e4459;
    }
</style>
<?php

    // Include diagnostic if needed
    if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
        require_once __DIR__ . '/tab-diagnostic.php';
    }

    // AJAX HANDLERS - Centralized save functionality
    add_action('wp_ajax_pdf_builder_save_all_settings', function() {
        try {
            // Verify nonce
            if (!wp_verify_nonce(sanitize_text_field($_POST['nonce'] ?? ''), 'pdf_builder_settings_ajax')) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            // Collect and sanitize all form data
            $updated_fields = [];
            foreach ($_POST as $key => $value) {
                // Skip WordPress internal fields
                if (in_array($key, ['action', 'nonce'])) continue;

                if (is_array($value)) {
                    $updated_fields[$key] = array_map('sanitize_text_field', $value);
                } else {
                    $updated_fields[$key] = sanitize_text_field($value);
                }
            }

            // Update individual settings
            $settings_map = [
                'company_phone_manual' => 'pdf_builder_company_phone_manual',
                'company_siret' => 'pdf_builder_company_siret',
                'company_vat' => 'pdf_builder_company_vat',
                'company_rcs' => 'pdf_builder_company_rcs',
                'company_capital' => 'pdf_builder_company_capital'
            ];

            foreach ($settings_map as $form_key => $option_key) {
                if (isset($updated_fields[$form_key])) {
                    update_option($option_key, $updated_fields[$form_key]);
                }
            }

            // Update main settings array
            $existing_settings = get_option('pdf_builder_settings', []);
            $new_settings = array_merge($existing_settings, $updated_fields);
            update_option('pdf_builder_settings', $new_settings);

            error_log('PDF Builder: AJAX - Settings saved successfully');

            wp_send_json_success([
                'message' => 'Settings saved successfully',
                'updated' => count($updated_fields)
            ]);

        } catch (Exception $e) {
            error_log('PDF Builder: AJAX Error - ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    });

    add_action('wp_ajax_pdf_builder_save_tab_settings', function() {
        try {
            // Verify nonce
            if (!wp_verify_nonce(sanitize_text_field($_POST['nonce'] ?? ''), 'pdf_builder_settings_ajax')) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            $tab = sanitize_text_field($_POST['tab'] ?? 'unknown');

            // Collect tab-specific data
            $updated_fields = [];
            foreach ($_POST as $key => $value) {
                if (in_array($key, ['action', 'nonce', 'tab'])) continue;
                $updated_fields[$key] = sanitize_text_field($value);
            }

            // Process based on tab
            switch ($tab) {
                case 'general':
                    update_option('pdf_builder_company_phone_manual', $updated_fields['company_phone_manual'] ?? '');
                    update_option('pdf_builder_company_siret', $updated_fields['company_siret'] ?? '');
                    update_option('pdf_builder_company_vat', $updated_fields['company_vat'] ?? '');
                    update_option('pdf_builder_company_rcs', $updated_fields['company_rcs'] ?? '');
                    update_option('pdf_builder_company_capital', $updated_fields['company_capital'] ?? '');
                    break;
                case 'acces':
                    // Handle access roles (array of roles)
                    if (isset($_POST['pdf_builder_allowed_roles']) && is_array($_POST['pdf_builder_allowed_roles'])) {
                        $allowed_roles = array_map('sanitize_text_field', $_POST['pdf_builder_allowed_roles']);
                        // Always include administrator
                        if (!in_array('administrator', $allowed_roles)) {
                            $allowed_roles[] = 'administrator';
                        }
                        update_option('pdf_builder_allowed_roles', $allowed_roles);
                    }
                    break;
                default:
                    // General settings handler for other tabs
                    foreach ($updated_fields as $key => $value) {
                        if (strpos($key, 'pdf_builder_') === 0 || strpos($key, 'systeme_') === 0) {
                            update_option('pdf_builder_' . str_replace(['pdf_builder_', 'systeme_'], '', $key), $value);
                        }
                    }
                    break;
            }

            wp_send_json_success([
                'message' => ucfirst($tab) . ' settings saved successfully',
                'tab' => $tab
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    });

    add_action('wp_ajax_pdf_builder_deactivate_license', function() {
        try {
            // Verify nonce
            if (!wp_verify_nonce(sanitize_text_field($_POST['nonce'] ?? ''), 'pdf_builder_deactivate')) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            // Check user capability
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }

            // Clear all license data
            delete_option('pdf_builder_license_key');
            delete_option('pdf_builder_license_status');
            delete_option('pdf_builder_license_expires');
            delete_option('pdf_builder_license_activated_at');
            delete_option('pdf_builder_license_test_key');
            delete_option('pdf_builder_license_test_key_expires');
            delete_option('pdf_builder_license_test_mode_enabled');

            // Reset to free mode
            update_option('pdf_builder_license_status', 'free');

            error_log('PDF Builder: License deactivated successfully via AJAX');

            wp_send_json_success([
                'message' => 'License deactivated successfully',
                'status' => 'free'
            ]);

        } catch (Exception $e) {
            error_log('PDF Builder: License deactivation error - ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    });

    // Developer Settings AJAX Handler
    error_log('PDF Builder: Registering developer settings AJAX handler at line ' . __LINE__);
    add_action('wp_ajax_pdf_builder_developer_save_settings', function() {
        error_log('PDF Builder Developer: AJAX handler STARTED at ' . date('Y-m-d H:i:s'));

        try {
            // Log all POST data for debugging
            error_log('PDF Builder Developer: POST data received: ' . print_r($_POST, true));

            // Verify nonce
            $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
            $nonce_valid = wp_verify_nonce($nonce_value, 'pdf_builder_settings_ajax');
            error_log('PDF Builder Developer: Nonce verification result: ' . ($nonce_valid ? 'VALID' : 'INVALID'));

            if (!$nonce_valid) {
                error_log('PDF Builder Developer: Nonce verification failed');
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            // Check user capability
            $has_capability = current_user_can('manage_options');
            error_log('PDF Builder Developer: User capability check: ' . ($has_capability ? 'HAS' : 'NO'));

            if (!$has_capability) {
                error_log('PDF Builder Developer: Insufficient permissions');
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }

            // Get the setting key and value
            $setting_key = sanitize_text_field($_POST['setting_key'] ?? '');
            $setting_value = sanitize_text_field($_POST['setting_value'] ?? '');

            error_log("PDF Builder Developer: Setting key: '{$setting_key}', value: '{$setting_value}'");

            // Validate setting key (only allow developer settings)
            $allowed_keys = [
                'pdf_builder_developer_enabled',
                'pdf_builder_canvas_debug_enabled',
                'pdf_builder_developer_password'
            ];

            if (!in_array($setting_key, $allowed_keys)) {
                error_log("PDF Builder Developer: Invalid setting key: {$setting_key}");
                wp_send_json_error(['message' => 'Invalid setting key']);
                return;
            }

            // Get existing settings
            $settings = get_option('pdf_builder_settings', []);

            // Update the specific setting
            $settings[$setting_key] = $setting_value;

            // Save back to database
            $updated = update_option('pdf_builder_settings', $settings);
            error_log("PDF Builder Developer: update_option result: " . ($updated ? 'SUCCESS' : 'NO CHANGE'));

            wp_send_json_success([
                'message' => 'Developer setting saved successfully',
                'setting' => $setting_key,
                'value' => $setting_value
            ]);

        } catch (Exception $e) {
            error_log('PDF Builder Developer: AJAX Error - ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    });
?>

<?php require_once __DIR__ . '/settings-modals.php'; ?>
