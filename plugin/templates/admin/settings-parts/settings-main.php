<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());
?>
<main class="wrap" id="pdf-builder-settings-wrapper">
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1>Parametres PDF Builder Pro</h1>
    </header>

    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs" role="tablist" aria-label="Onglets des paramètres PDF Builder">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general" role="tab" aria-selected="true" aria-controls="general">Général</a>
        <a href="#licence" class="nav-tab" data-tab="licence" role="tab" aria-selected="false" aria-controls="licence">Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme" role="tab" aria-selected="false" aria-controls="systeme">Système</a>
        <a href="#acces" class="nav-tab" data-tab="acces" role="tab" aria-selected="false" aria-controls="acces">Accès</a>
        <a href="#securite" class="nav-tab" data-tab="securite" role="tab" aria-selected="false" aria-controls="securite">Sécurité</a>
        <a href="#pdf" class="nav-tab" data-tab="pdf" role="tab" aria-selected="false" aria-controls="pdf">PDF</a>
        <a href="#contenu" class="nav-tab" data-tab="contenu" role="tab" aria-selected="false" aria-controls="contenu">Contenu</a>
        <a href="#templates" class="nav-tab" data-tab="templates" role="tab" aria-selected="false" aria-controls="templates">Modèles</a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur" role="tab" aria-selected="false" aria-controls="developpeur">Développeur</a>
    </nav>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper" role="tabpanel" aria-live="polite">
        <!-- LOG AVANT GENERAL -->
        <script>console.log('📍 LOG AVANT GENERAL - Inclusion du fichier general');</script>
        <div id="general" class="tab-content active" role="tabpanel" aria-labelledby="tab-general">
            <?php require_once 'settings-general.php'; ?>
        </div>

        <div id="licence" class="tab-content" role="tabpanel" aria-labelledby="tab-licence">
            <?php require_once 'settings-licence.php'; ?>
        </div>

        <div id="systeme" class="tab-content" role="tabpanel" aria-labelledby="tab-systeme">
            <?php require_once 'settings-systeme.php'; ?>
        </div>

        <div id="acces" class="tab-content" role="tabpanel" aria-labelledby="tab-acces">
            <?php require_once 'settings-acces.php'; ?>
        </div>

        <div id="securite" class="tab-content" role="tabpanel" aria-labelledby="tab-securite">
            <?php require_once 'settings-securite.php'; ?>
        </div>

        <div id="pdf" class="tab-content" role="tabpanel" aria-labelledby="tab-pdf">
            <?php require_once 'settings-pdf.php'; ?>
        </div>

        <div id="contenu" class="tab-content" role="tabpanel" aria-labelledby="tab-contenu">
            <?php require_once 'settings-contenu.php'; ?>
        </div>

        <div id="templates" class="tab-content" role="tabpanel" aria-labelledby="tab-templates">
            <?php require_once 'settings-templates.php'; ?>
        </div>

        <div id="developpeur" class="tab-content" role="tabpanel" aria-labelledby="tab-developpeur">
            <?php require_once 'settings-developpeur.php'; ?>
        </div>
        <div id="acces" class="tab-content" role="tabpanel" aria-labelledby="tab-acces">
            <?php require_once 'settings-acces.php'; ?>
        </div>
        <div id="securite" class="tab-content" role="tabpanel" aria-labelledby="tab-securite">
            <?php require_once 'settings-securite.php'; ?>
        </div>
        <div id="pdf" class="tab-content" role="tabpanel" aria-labelledby="tab-pdf">
            <?php require_once 'settings-pdf.php'; ?>
        </div>
        <div id="contenu" class="tab-content" role="tabpanel" aria-labelledby="tab-contenu">
            <?php require_once 'settings-contenu.php'; ?>
        </div>
        <div id="templates" class="tab-content" role="tabpanel" aria-labelledby="tab-templates">
            <?php require_once 'settings-templates.php'; ?>
        </div>
        <div id="developpeur" class="tab-content" role="tabpanel" aria-labelledby="tab-developpeur">
            <?php require_once 'settings-developpeur.php'; ?>
        </div>
    </section>

    <!-- SCRIPT DE NAVIGATION DIRECT INLINE - SOLUTION DE SECOURS -->
    <script>
    console.log('🔥 PDF BUILDER - NAVIGATION DIRECT INLINE - VERSION SECOURS');
    
    try {
    (function() {
        
        function switchTab(tabId) {
            console.log('📍 SWITCH vers:', tabId);
            
            const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
            const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
            
            // Désactiver tous
            tabButtons.forEach(function(btn) {
                btn.classList.remove('nav-tab-active');
                btn.setAttribute('aria-selected', 'false');
            });
            tabContents.forEach(function(content) {
                content.classList.remove('active');
            });
            
            // Activer l'onglet cible
            const targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
            let targetContent = document.getElementById(tabId);
            
            if (targetBtn) {
                targetBtn.classList.add('nav-tab-active');
                targetBtn.setAttribute('aria-selected', 'true');
            }
            
            if (targetContent) {
                targetContent.classList.add('active');
            }
        }
        
        function handleTabClick(event) {
            event.preventDefault();
            event.stopPropagation();
            
            const tabId = event.currentTarget.getAttribute('data-tab');
            if (!tabId) return;
            
            switchTab(tabId);
        }
        
        function initializeTabs() {
            const tabsContainer = document.getElementById('pdf-builder-tabs');
            const contentContainer = document.getElementById('pdf-builder-tab-content');
            
            if (!tabsContainer || !contentContainer) {
                return false;
            }
            
            const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
            
            // Attacher les événements
            tabButtons.forEach(function(btn) {
                btn.removeEventListener('click', handleTabClick);
                btn.addEventListener('click', handleTabClick);
            });
            
            // Activer le premier onglet
            if (tabButtons[0]) {
                const firstTab = tabButtons[0].getAttribute('data-tab');
                setTimeout(function() {
                    switchTab(firstTab);
                }, 100);
            }
            
            return true;
        }
        
        // Démarrage
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initializeTabs, 50);
            });
        } else {
            setTimeout(initializeTabs, 50);
        }
        
        // Nouvelle tentative après délai
        setTimeout(function() {
            initializeTabs();
        }, 500);
        
        // Export pour diagnostic
        window.PDF_BUILDER_DIRECT = {
            switchTab: switchTab,
            initialize: initializeTabs
        };
        
    })();
    } catch(error) {
        console.error('🔥 PDF BUILDER - ERREUR dans script inline:', error);
    }
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

// Chemin correct vers le script settings-tabs.js dans le dossier assets/js
$script_path = plugins_url('../assets/js/settings-tabs.js', __FILE__);
error_log('PDF Builder: Enqueue script path: ' . $script_path);
error_log('PDF Builder: __FILE__ value: ' . __FILE__);
error_log('PDF Builder: plugin_dir_path: ' . plugin_dir_path(__FILE__));

// Vérifier que le fichier existe
$script_file = plugin_dir_path(__FILE__) . '../assets/js/settings-tabs.js';
if (file_exists($script_file)) {
    error_log('PDF Builder: Script file found: ' . $script_file);
} else {
    error_log('PDF Builder: Script file NOT found: ' . $script_file);
}

// Ajouter un script de diagnostic pour vérifier le chargement
add_action('wp_head', function() {
    echo '<script>';
    echo 'console.log("🔥 PDF BUILDER - DIAGNOSTIC HEAD: Vérification chargement script");';
    echo 'console.log("🔥 PDF BUILDER - jQuery chargé:", typeof jQuery !== "undefined");';
    echo 'console.log("🔥 PDF BUILDER - PDF_BUILDER_CONFIG:", typeof PDF_BUILDER_CONFIG !== "undefined");';
    echo 'setTimeout(function() {';
    echo '    console.log("🔥 PDF BUILDER - Vérification script settings-tabs après 2s");';
    echo '    console.log("🔥 PDF BUILDER - jQuery.version:", jQuery.fn.jquery);';
    echo '    console.log("🔥 PDF BUILDER - Scripts chargés:", document.scripts.length);';
    echo '    for (var i = 0; i < document.scripts.length; i++) {';
    echo '        if (document.scripts[i].src && document.scripts[i].src.indexOf("settings-tabs") > -1) {';
    echo '            console.log("🔥 PDF BUILDER - Script settings-tabs trouvé:", document.scripts[i].src);';
    echo '        }';
    echo '    }';
    echo '}, 2000);';
    echo '</script>';
});

// TEMPORAIRE: Charger le script directement pour diagnostic
$direct_script_path = plugin_dir_path(__FILE__) . '../assets/js/settings-tabs.js';
if (file_exists($direct_script_path)) {
    echo '<script>';
    echo 'console.log("🔥 PDF BUILDER - CHARGEMENT DIRECT FORCE DU SCRIPT");';
    echo 'window.PDF_BUILDER_CONFIG = ' . json_encode($js_config) . ';';
    echo file_get_contents($direct_script_path);
    echo '</script>';
} else {
    echo '<script>console.error("🔥 PDF BUILDER - Script direct non trouvé:", "' . $direct_script_path . '");</script>';
    
    // Fallback: charger le script de force
    $force_script_path = plugin_dir_path(__FILE__) . '../assets/js/tabs-force.js';
    if (file_exists($force_script_path)) {
        echo '<script>';
        echo 'console.log("🔥 PDF BUILDER - CHARGEMENT SCRIPT FORCE EN FALLBACK");';
        echo 'window.PDF_BUILDER_CONFIG = ' . json_encode($js_config) . ';';
        echo file_get_contents($force_script_path);
        echo '</script>';
    }
}
?>


<?php

    // Inclure le diagnostic pour diagnostic
    require_once __DIR__ . '/diagnostic-tabs.php';

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


