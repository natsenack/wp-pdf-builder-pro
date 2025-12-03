<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());
?>
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1>Parametres PDF Builder Pro</h1>
    </header>

    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general">General</a>
        <a href="#licence" class="nav-tab" data-tab="licence">Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme">Systeme</a>
        <a href="#acces" class="nav-tab" data-tab="acces">Acces</a>
        <a href="#securite" class="nav-tab" data-tab="securite">Securite</a>
        <a href="#pdf" class="nav-tab" data-tab="pdf">PDF</a>
        <a href="#contenu" class="nav-tab" data-tab="contenu">Contenu</a>
        <a href="#templates" class="nav-tab" data-tab="templates">Modeles</a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur">Developpeur</a>
    </nav>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper">
        <div id="general" class="tab-content active">
            <?php require_once 'settings-general.php'; ?>
        </div>
        <div id="licence" class="tab-content">
            <?php require_once 'settings-licence.php'; ?>
        </div>
        <div id="systeme" class="tab-content">
            <?php require_once 'settings-systeme.php'; ?>
        </div>
        <div id="acces" class="tab-content">
            <?php require_once 'settings-acces.php'; ?>
        </div>
        <div id="securite" class="tab-content">
            <?php require_once 'settings-securite.php'; ?>
        </div>
        <div id="pdf" class="tab-content">
            <?php require_once 'settings-pdf.php'; ?>
        </div>
        <div id="contenu" class="tab-content">
            <?php require_once 'settings-contenu.php'; ?>
        </div>
        <div id="templates" class="tab-content">
            <?php require_once 'settings-templates.php'; ?>
        </div>
        <div id="developpeur" class="tab-content">
            <?php require_once 'settings-developpeur.php'; ?>
        </div>
    </section>

    <!-- Fallback minimal pour navigation des onglets: exécuté seulement si le script principal ne s'est pas chargé -->
    <script>
    // LOGS JS DIRECTS DANS LE HTML POUR DIAGNOSTIC
    console.log('📄 PDF Builder - PAGE HTML CHARGÉE - settings-main.php');
    console.log('📄 PDF Builder - Vérification éléments DOM au chargement HTML:', {
        wrapper: !!document.getElementById('pdf-builder-settings-wrapper'),
        tabs: !!document.getElementById('pdf-builder-tabs'),
        content: !!document.getElementById('pdf-builder-tab-content'),
        navTabs: document.querySelectorAll('#pdf-builder-tabs .nav-tab').length,
        tabContents: document.querySelectorAll('#pdf-builder-tab-content .tab-content').length
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📄 PDF Builder - DOM CONTENT LOADED - HTML ready');
        
        // Vérifier que les scripts externes sont chargés
        setTimeout(function() {
            console.log('📄 PDF Builder - TIMEOUT CHECK - Scripts externes chargés?', {
                pdfBuilderConfig: typeof PDF_BUILDER_CONFIG !== 'undefined',
                debug: !!(typeof PDF_BUILDER_CONFIG !== 'undefined' && PDF_BUILDER_CONFIG.debug),
                tabsInitialized: !!window.PDF_BUILDER_TABS_INITIALIZED
            });
        }, 200);
    });
    
    (function() {
        // Si le script en file est chargé, ne rien faire
        if (typeof window.PDF_BUILDER_CONFIG !== 'undefined') return;

        document.addEventListener('DOMContentLoaded', function() {
            console.warn('📄 PDF Builder: Script principal non détecté — activation du fallback minimal');

            const tabsContainer = document.getElementById('pdf-builder-tabs');
            const contentContainer = document.getElementById('pdf-builder-tab-content');
            if (!tabsContainer || !contentContainer) return;

            const tabButtons = tabsContainer.querySelectorAll('.nav-tab');
            const tabContents = contentContainer.querySelectorAll('.tab-content');

            tabButtons.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const tabId = btn.getAttribute('data-tab');
                    if (!tabId) return;

                    tabButtons.forEach(function(b) { b.classList.remove('nav-tab-active'); });
                    tabContents.forEach(function(c) { c.classList.remove('active'); });

                    btn.classList.add('nav-tab-active');
                    const target = document.getElementById(tabId) || document.getElementById('tab-' + tabId);
                    if (target) target.classList.add('active');

                    try { localStorage.setItem('pdf_builder_active_tab', tabId); } catch (err) { /* ignore */ }
                }, false);
            });
        });
    })();
    </script>
</main>

<?php
// Préparer la configuration pour le JavaScript
$js_config = array(
    'nonce' => wp_create_nonce('pdf_builder_settings_ajax'),
    'ajax_url' => admin_url('admin-ajax.php'),
    // activer/désactiver le debug JS depuis les options (pdf_builder_debug_javascript)
    'debug' => true, // FORCÉ À TRUE TEMPORAIREMENT POUR DIAGNOSTIC
    'debug_verbose' => !empty($settings['pdf_builder_debug_javascript_verbose']) ? true : false,
);

// Enqueue jQuery d'abord, puis notre script
wp_enqueue_script('jquery');
$script_path = plugins_url('settings-tabs.js', __FILE__); // Même dossier que settings-main.php
error_log('PDF Builder: Enqueue script path: ' . $script_path);
wp_enqueue_script('pdf-builder-settings-tabs', $script_path, array('jquery'), time(), true);
// Localiser la config APRES enqueue mais AVANT le script se charge
wp_localize_script('pdf-builder-settings-tabs', 'PDF_BUILDER_CONFIG', $js_config);
?>


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
                    // New CSS and HTML settings
                    update_option('pdf_builder_custom_css', $updated_fields['pdf_builder_custom_css'] ?? '');
                    update_option('pdf_builder_css_enabled', $updated_fields['pdf_builder_css_enabled'] ?? '0');
                    update_option('pdf_builder_invoice_template', $updated_fields['pdf_builder_invoice_template'] ?? '');
                    update_option('pdf_builder_quote_template', $updated_fields['pdf_builder_quote_template'] ?? '');
                    update_option('pdf_builder_html_enabled', $updated_fields['pdf_builder_html_enabled'] ?? '0');
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
