<?php

/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

// Empêcher l'accès direct (sauf pour les tests)
if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit('Direct access not allowed');
}

// Activer le débogage pour capture les erreurs PHP critiques
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}
if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', false); // Ne pas afficher en frontend, seulement logger
}

error_log('[BOOTSTRAP] bootstrap.php loaded at ' . microtime(true));

// ========================================================================
// ✅ INJECTION DU NONCE DANS LE HEAD - TRÈS TÔT
// Cela s'exécute avant admin_head et garantit que le nonce est disponible
// ========================================================================
// Fonction d'injection du nonce
function pdf_builder_inject_nonce() {
    // Vérifier qu'on est sur la bonne page
    if (!is_admin()) {
        return; // Pas sur une page admin
    }
    
    $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    if ($page !== 'pdf-builder-react-editor') {
        return; // Pas sur la page de l'éditeur
    }
    
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        return; // Pas de permission
    }
    
    // Créer le nonce
    $nonce = wp_create_nonce('pdf_builder_ajax');
    
    // Injecter directement
    $ajax_url = admin_url('admin-ajax.php');
    
    // Générer le script en bloc unique
    $script = <<<'SCRIPT'
<script type="text/javascript">
(function() {
    window.pdfBuilderData = {
        nonce: '%NONCE%',
        ajaxUrl: '%AJAX_URL%',
        templateId: null,
        _timestamp: %TIMESTAMP%
    };
    window.pdfBuilderNonce = '%NONCE%';
    
    console.warn('[BOOTSTRAP] Injection du nonce et AJAX URL');
    console.log('[BOOTSTRAP] nonce =', window.pdfBuilderNonce);
    console.log('[BOOTSTRAP] ajaxUrl =', window.pdfBuilderData.ajaxUrl);
    
    // TEST DIRECT
    window.testPDFPreview = function() {
        console.warn('🔴 TEST PREVIEW CALLED');
        
        // Vérification des données
        console.log('[TEST] window.pdfBuilderData =', window.pdfBuilderData);
        console.log('[TEST] window.pdfBuilderNonce =', window.pdfBuilderNonce);
        
        // Utiliser le nonce de bootstrap (frais) et l'ajaxUrl du pdfBuilderData (qui peut être créé par React)
        const nonce = window.pdfBuilderNonce;  // Nonce frais de bootstrap
        const ajaxurl = window.pdfBuilderData ? window.pdfBuilderData.ajaxUrl : undefined;  // NOTE: camelCase!
        const templateId = window.pdfBuilderData ? window.pdfBuilderData.templateId : null;
        
        console.log('[TEST] nonce (from bootstrap) =', nonce);
        console.log('[TEST] ajaxurl (from pdfBuilderData) =', ajaxurl);
        console.log('[TEST] templateId (from pdfBuilderData) =', templateId);
        
        if (!ajaxurl) {
            console.error('[TEST] ERROR: ajaxurl not found in window.pdfBuilderData!');
            console.error('[TEST] Full pdfBuilderData:', window.pdfBuilderData);
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'pdf_builder_generate_preview');
        formData.append('nonce', nonce);
        
        // Si on a un template_id, l'envoyer au lieu de template_data
        if (templateId && templateId > 0) {
            console.log('[TEST] ✅ Template ID trouvé:', templateId);
            console.log('[TEST] Récupération du template depuis la BD...');
            formData.append('template_id', templateId);
        } else {
            // Sinon envoyer des données template valides
            const templateData = {
                elements: [],
                styles: {},
                settings: {
                    pageSize: 'A4',
                    orientation: 'portrait'
                }
            };
            console.log('[TEST] ❌ Template ID vide - Sending template_data:', templateData);
            formData.append('template_data', JSON.stringify(templateData));
        }
        
        formData.append('format', 'pdf');  // Focus PDF only for now
        formData.append('quality', '150');
        
        console.log('[TEST] Fetching POST to:', ajaxurl, 'with nonce:', nonce);
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(r => {
            console.log('[TEST] Response status:', r.status);
            if (!r.ok) {
                return r.text().then(text => {
                    console.error('[TEST] Server error (HTML):', text.substring(0, 500));
                    throw new Error('Server error: ' + text.substring(0, 200));
                });
            }
            return r.json();
        })
        .then(d => {
            if (d.success) {
                console.log('[TEST] ✅ SUCCESS - Preview data:', d.data);
                if (d.data && d.data.image_url) {
                    console.log('[TEST] Image URL récupérée:', d.data.image_url.substring(0, 100) + '...');
                }
            } else {
                console.warn('[TEST] ⚠️ Server returned error:', d.data);
            }
        })
        .catch(e => console.error('[TEST] ❌ FETCH ERROR:', e));
    };
    
    console.log('🟢 testPDFPreview registered. Call: window.testPDFPreview()');
})();
</script>
SCRIPT;
    
    // Remplacer les placeholders
    $script = str_replace('%NONCE%', esc_js($nonce), $script);
    $script = str_replace('%AJAX_URL%', esc_js($ajax_url), $script);
    $script = str_replace('%TIMESTAMP%', time(), $script);
    
    echo $script;
}

// Ajouter le hook admin_head avec priorité très haute
add_action('admin_head', 'pdf_builder_inject_nonce', 1);

// Vérifier si on est sur une page admin
if (is_admin()) {
    error_log('[BOOTSTRAP] We are in admin area');
    error_log('[BOOTSTRAP] Current page: ' . (isset($_GET['page']) ? $_GET['page'] : 'no page param'));
    error_log('[BOOTSTRAP] REQUEST_URI: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'no uri'));
} else {
    error_log('[BOOTSTRAP] Not in admin area');
}

// Définir les constantes essentielles si elles ne sont pas déjà définies
if (!defined('PDF_BUILDER_PLUGIN_FILE')) {
    define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
}
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
}

// ============================================================================
// ✅ CHARGEMENT CENTRALISÉ DE L'AUTOLOADER COMPOSER
// ============================================================================

/**
 * Chargement unique et centralisé de l'autoloader Composer
 * Évite les chargements redondants dans différents fichiers
 */
if (!class_exists('Dompdf\Dompdf') && file_exists(PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php';
}

// ============================================================================
// ✅ FONCTIONS WRAPPER POUR LA TABLE PERSONNALISÉE DE PARAMÈTRES
// ============================================================================

/**
 * Récupérer une option depuis la table personnalisée wp_pdf_builder_settings
 * Fallback vers wp_options si la table n'existe pas
 */
function pdf_builder_get_option($option_name, $default = false) {
    // Charger le Settings Table Manager
    if (!class_exists('PDF_Builder\Database\Settings_Table_Manager')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Database/Settings_Table_Manager.php';
    }
    
    return \PDF_Builder\Database\Settings_Table_Manager::get_option($option_name, $default);
}

/**
 * Mettre à jour une option dans la table personnalisée wp_pdf_builder_settings
 */
function pdf_builder_update_option($option_name, $option_value, $autoload = 'yes') {
    // Charger le Settings Table Manager
    if (!class_exists('PDF_Builder\Database\Settings_Table_Manager')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Database/Settings_Table_Manager.php';
    }
    
    return \PDF_Builder\Database\Settings_Table_Manager::update_option($option_name, $option_value, $autoload);
}

/**
 * Supprimer une option depuis la table personnalisée wp_pdf_builder_settings
 */
function pdf_builder_delete_option($option_name) {
    // Charger le Settings Table Manager
    if (!class_exists('PDF_Builder\Database\Settings_Table_Manager')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Database/Settings_Table_Manager.php';
    }
    
    return \PDF_Builder\Database\Settings_Table_Manager::delete_option($option_name);
}

/**
 * Récupérer tous les paramètres PDF Builder
 */
function pdf_builder_get_all_options() {
    // Charger le Settings Table Manager
    if (!class_exists('PDF_Builder\Database\Settings_Table_Manager')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Database/Settings_Table_Manager.php';
    }
    
    return \PDF_Builder\Database\Settings_Table_Manager::get_all_options();
}

// ============================================================================
// ✅ FONCTION DE CHARGEMENT D'URGENCE DES UTILITAIRES
// ============================================================================

/**
 * Fonction d'urgence pour charger les utilitaires si nécessaire
 * Peut être appelée depuis n'importe où pour garantir la disponibilité des classes
 */
function pdf_builder_load_utilities_emergency() {
    static $utilities_loaded = false;

    if ($utilities_loaded) {
        return;
    }

    $utilities = array(
        'PDF_Builder_Onboarding_Manager.php',
        'PDF_Builder_GDPR_Manager.php'
    );

    foreach ($utilities as $utility) {
        $utility_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/' . $utility;
        if (file_exists($utility_path) && !class_exists('PDF_Builder\\Utilities\\' . str_replace('.php', '', $utility))) {
            require_once $utility_path;
        }
    }

    $utilities_loaded = true;
}

// ============================================================================
// ✅ FONCTION GLOBALE DE VÉRIFICATION DE CLASSE
// ============================================================================

/**
 * Fonction globale pour vérifier et charger la classe Onboarding Manager
 * Peut être appelée depuis n'importe où dans le code
 */
function pdf_builder_ensure_onboarding_manager() {
    if (!class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
        pdf_builder_load_utilities_emergency();

        // Double vérification avec chargement manuel
        $onboarding_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php';
        if (file_exists($onboarding_path)) {
            require_once $onboarding_path;
        }
    }

    return class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager');
}

/**
 * Fonction globale GARANTIE pour obtenir l'instance Onboarding Manager
 * Utilise la classe alias qui est toujours disponible
 */
function pdf_builder_get_onboarding_manager() {
    // Essayer d'abord la vraie classe
    if (class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
        return \PDF_Builder\Utilities\PDF_Builder_Onboarding_Manager::get_instance();
    }

    // Fallback vers la classe alias (toujours disponible)
    if (class_exists('PDF_Builder_Onboarding_Manager_Alias')) {
        return PDF_Builder_Onboarding_Manager_Alias::get_instance();
    }

    // Dernier recours - créer une instance standalone
    return PDF_Builder_Onboarding_Manager_Standalone::get_instance();
}

/**
 * Fonction de diagnostic pour l'Onboarding Manager
 * Affiche des informations de debug si la classe n'est pas trouvée
 */
function pdf_builder_diagnose_onboarding_manager() {
    $class_exists = class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager');
    $alias_exists = class_exists('PDF_Builder_Onboarding_Manager_Alias');
    $standalone_exists = class_exists('PDF_Builder_Onboarding_Manager_Standalone');
    $file_exists = file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php');

    $message = "=== DIAGNOSTIC PDF_Builder_Onboarding_Manager ===\n";
    $message .= "Classe réelle existe: " . ($class_exists ? 'OUI' : 'NON') . "\n";
    $message .= "Classe alias existe: " . ($alias_exists ? 'OUI' : 'NON') . "\n";
    $message .= "Classe standalone existe: " . ($standalone_exists ? 'OUI' : 'NON') . "\n";
    $message .= "Fichier existe: " . ($file_exists ? 'OUI' : 'NON') . "\n";
    $message .= "Plugin activé: " . (defined('PDF_BUILDER_PLUGIN_DIR') ? 'OUI' : 'NON') . "\n";
    $message .= "Bootstrap chargé: " . (function_exists('pdf_builder_load_utilities_emergency') ? 'OUI' : 'NON') . "\n";

    if (!$class_exists) {
        $message .= "Tentative de chargement d'urgence...\n";
        pdf_builder_ensure_onboarding_manager();
        $message .= "Après chargement: " . (class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager') ? 'SUCCÈS' : 'ÉCHEC') . "\n";
    }

    $message .= "===========================================\n";

    return $message;
}

// ============================================================================
// INITIALISATION DIFFÉRÉE - UNIQUEMENT APRÈS QUE WORDPRESS SOIT CHARGÉ
// ============================================================================

// Tous les hooks et initialisations sont maintenant différés jusqu'à ce que WordPress soit prêt
if (function_exists('add_action')) {
    // Initialiser l'Onboarding Manager une fois WordPress chargé
    add_action('plugins_loaded', function() {
        // Charger les utilitaires d'urgence si nécessaire
        pdf_builder_load_utilities_emergency();

        // Créer les classes d'alias pour la compatibilité
        if (!class_exists('PDF_Builder_Onboarding_Manager_Standalone')) {
            // Créer une version standalone de la classe pour les cas d'urgence
            class PDF_Builder_Onboarding_Manager_Standalone {
                private static $instance = null;

                public static function get_instance() {
                    if (self::$instance === null) {
                        self::$instance = new self();
                    }
                    return self::$instance;
                }

                public function __construct() {
                    // Constructeur minimal pour compatibilité
                }

                // Méthodes minimales pour éviter les erreurs
                public function check_onboarding_status() { return false; }
                public function ajax_complete_onboarding_step() { return false; }
                public function ajax_skip_onboarding() { return false; }
                public function ajax_reset_onboarding() { return false; }
                public function ajax_load_onboarding_step() { return false; }
                public function ajax_save_template_selection() { return false; }
                public function ajax_save_freemium_mode() { return false; }
                public function ajax_update_onboarding_step() { return false; }
                public function ajax_save_template_assignment() { return false; }
                public function ajax_mark_onboarding_complete() { return false; }
            }
        }

        // Maintenant charger la vraie classe si possible
        if (!class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
            $onboarding_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php';
            if (file_exists($onboarding_path)) {
                require_once $onboarding_path;
            } else {
                // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder: Fichier Onboarding Manager introuvable: ' . $onboarding_path); }
            }
        }

        // Alias pour compatibilité - utiliser la vraie classe si disponible, sinon la standalone
        if (!class_exists('PDF_Builder_Onboarding_Manager_Alias')) {
            if (class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
                class PDF_Builder_Onboarding_Manager_Alias extends PDF_Builder\Utilities\PDF_Builder_Onboarding_Manager {}
            } else {
                class PDF_Builder_Onboarding_Manager_Alias extends PDF_Builder_Onboarding_Manager_Standalone {}
            }
        }

        // Vérification finale et création de l'instance
        if (!class_exists('PDF_Builder_Onboarding_Manager')) {
            class_alias('PDF_Builder_Onboarding_Manager_Alias', 'PDF_Builder_Onboarding_Manager');

            // Créer l'instance maintenant que WordPress est chargé
            try {
                PDF_Builder_Onboarding_Manager_Alias::get_instance();
            } catch (Exception $e) {
                // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder: Erreur lors de la création de l\'instance Onboarding Manager: ' . $e->getMessage()); }
            }
        }
    }, 0);

    // Initialiser l'API Preview après que WordPress soit chargé
    add_action('init', function() {
        if (class_exists('\\PDF_Builder\\Api\\PreviewImageAPI')) {
            new \PDF_Builder\Api\PreviewImageAPI();
        }
        // Initialiser le handler AJAX pour l'aperçu
        if (class_exists('\\PDF_Builder\\PreviewSystem\\PreviewAjaxHandler')) {
            \PDF_Builder\PreviewSystem\PreviewAjaxHandler::init();
        }
    });

    // Force HTTPS if enabled in settings (simple redirect to https if not SSL)
        add_action('template_redirect', function() {
            // Skip CLI, AJAX, REST requests and cron
            if (defined('WP_CLI') && WP_CLI) return;
            if (defined('DOING_AJAX') && DOING_AJAX) return;
            if (defined('REST_REQUEST') && REST_REQUEST) return;

            $force = pdf_builder_get_option('pdf_builder_force_https', '0');
            if ($force === '1' || $force === 1) {
                // Consider common reverse proxy headers to detect SSL
                $is_forwarded_ssl = (
                    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
                    (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') ||
                    (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
                );
                if (!is_ssl() && !$is_forwarded_ssl) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder HTTPS] Redirecting to HTTPS. host=' . ($_SERVER['HTTP_HOST'] ?? '') . ', uri=' . ($_SERVER['REQUEST_URI'] ?? '')); }
                    }
                    $host = $_SERVER['HTTP_HOST'] ?? '';
                    $uri = $_SERVER['REQUEST_URI'] ?? '';
                    if (!empty($host)) {
                        $redirect = 'https://' . $host . $uri;
                        // Preserver host and redirect safely
                        wp_safe_redirect($redirect, 301);
                        exit;
                    }
                }
            }
        }, 1);
    // Also enforce HTTPS for the administration pages if configured
    add_action('admin_init', function() {
        // Skip CLI, AJAX and REST calls
        if (defined('WP_CLI') && WP_CLI) return;
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        $force = pdf_builder_get_option('pdf_builder_force_https', '0');
        if ($force === '1' || $force === 1) {
            $is_forwarded_ssl = (
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') ||
                (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
            );
            if (!is_ssl() && !$is_forwarded_ssl) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder HTTPS] Admin redirecting to HTTPS. host=' . ($_SERVER['HTTP_HOST'] ?? '') . ', uri=' . ($_SERVER['REQUEST_URI'] ?? '')); }
                }
                $host = $_SERVER['HTTP_HOST'] ?? '';
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                if (!empty($host)) {
                    $redirect = 'https://' . $host . $uri;
                    wp_safe_redirect($redirect, 301);
                    exit;
                }
            }
        }
    }, 1);
}

// Enregistrer les paramètres principaux
add_action('admin_init', function() {
    register_setting('pdf_builder_settings', 'pdf_builder_settings', array(
        'type' => 'array',
        'description' => 'Paramètres principaux PDF Builder Pro',
        'sanitize_callback' => function($input) {
            // Log détaillé pour déboguer la sauvegarde
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] SANITIZE CALLBACK - Input type: ' . gettype($input)); }
            if (is_array($input)) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] SANITIZE CALLBACK - Input count: ' . count($input)); }
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] SANITIZE CALLBACK - Input keys: ' . implode(', ', array_keys($input))); }
                
                // Log spécifique pour les paramètres templates
                if (isset($input['pdf_builder_default_template'])) {
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Template par défaut: ' . $input['pdf_builder_default_template']); }
                }
                if (isset($input['pdf_builder_template_library_enabled'])) {
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Bibliothèque templates: ' . $input['pdf_builder_template_library_enabled']); }
                }
            } else {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] SANITIZE CALLBACK - Input is not array: ' . print_r($input, true)); }
            }
            
            // Validation et nettoyage des données
            if (!is_array($input)) {
                return array();
            }
            
            return $input;
        },
        'default' => array()
    ));
});

// Initialiser les variables $_SERVER manquantes pour éviter les erreurs PHP 8.1+
if (!isset($_SERVER['HTTP_B701CD7'])) {
    $_SERVER['HTTP_B701CD7'] = '';
}

// Fonction pour charger le core du plugin
function pdf_builder_load_core()
{
    static $loaded = false;
    if ($loaded) {
        return;
    }

    // Charger le autoloader pour le nouveau système PSR-4 - DISABLED
    // if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php')) {
    //     require_once PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
    // }

    // Charger les constantes
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/constants.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/constants.php';
    }

    // Charger le gestionnaire de configuration
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/config-manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/config-manager.php';
    }



    // HOTFIX: Charger le correctif pour les notifications avant PDF_Builder_Core
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'hotfix-notifications.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'hotfix-notifications.php';
    }

    // Charger la classe principale PDF_Builder_Core depuis src/
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Core.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Core.php';
    }

    // Charger les managers essentiels depuis src/Managers/
    $managers = array(
        // Tous les managers sont maintenant chargés par autoloader (namespace PDF_Builder\Managers\)
        // 'PDF_Builder_Backup_Restore_Manager.php',
        // 'PDF_Builder_Canvas_Manager.php',
        // 'PDF_Builder_Drag_Drop_Manager.php',
        'PDF_Builder_Feature_Manager.php', // Chargé pour les fonctionnalités freemium
        'PDF_Builder_License_Manager.php', // Manuellement chargé car autoloader désactivé
        'PDF_Builder_Logger.php', // Logger pour le débogage
        // 'PDF_Builder_PDF_Generator.php',
        // 'PDF_Builder_Resize_Manager.php',
        // 'PDF_Builder_Settings_Manager.php',
        // 'PDF_Builder_Status_Manager.php',
        // 'PDF_Builder_Template_Manager.php',
        // 'PDF_Builder_Variable_Mapper.php',
        // 'PDF_Builder_WooCommerce_Integration.php'
    );
    foreach ($managers as $manager) {
        $manager_path = PDF_BUILDER_PLUGIN_DIR . 'src/Managers/' . $manager;
        if (file_exists($manager_path)) {
            require_once $manager_path;
        }
    }

    // Charger les managers Admin depuis src/Admin/Managers/
    $admin_managers = array(
        'SettingsManager.php',
        'TemplateManager.php'
    );
    foreach ($admin_managers as $manager) {
        $manager_path = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Managers/' . $manager;
        if (file_exists($manager_path)) {
            require_once $manager_path;
        }
    }

    // Charger les handlers Admin depuis src/Admin/Handlers/
    $admin_handlers = array(
        'AjaxHandler.php'
    );
    foreach ($admin_handlers as $handler) {
        $handler_path = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Handlers/' . $handler;
        if (file_exists($handler_path)) {
            require_once $handler_path;
        }
    }

    // Charger les renderers Admin depuis src/Admin/Renderers/
    $admin_renderers = array(
        'HTMLRenderer.php'
    );
    foreach ($admin_renderers as $renderer) {
        $renderer_path = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Renderers/' . $renderer;
        if (file_exists($renderer_path)) {
            require_once $renderer_path;
        }
    }

    // Charger les processors Admin depuis src/Admin/Processors/
    $admin_processors = array(
        'TemplateProcessor.php'
    );
    foreach ($admin_processors as $processor) {
        $processor_path = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Processors/' . $processor;
        if (file_exists($processor_path)) {
            require_once $processor_path;
        }
    }

    // Charger les utilitaires de données Admin depuis src/Admin/Data/
    $admin_data_utils = array(
        'DataUtils.php'
    );
    foreach ($admin_data_utils as $data_util) {
        $data_util_path = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Data/' . $data_util;
        if (file_exists($data_util_path)) {
            require_once $data_util_path;
        }
    }

    // Charger les providers Admin depuis src/Admin/Providers/
    $admin_providers = array(
        'DashboardDataProvider.php'
    );
    foreach ($admin_providers as $provider) {
        $provider_path = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Providers/' . $provider;
        if (file_exists($provider_path)) {
            require_once $provider_path;
        }
    }

    // Forcer le déploiement - marqueur de test

    // Charger les utilitaires essentiels depuis src/utilities/
    $utilities = array(
        'PDF_Builder_Onboarding_Manager.php',
        'PDF_Builder_GDPR_Manager.php'
    );
    foreach ($utilities as $utility) {
        $utility_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/' . $utility;
        if (file_exists($utility_path)) {
            require_once $utility_path;
        }
    }

    // Charger les classes Core essentielles
    $core_classes = array(
        'PDF_Builder_Security_Validator.php',
        'PDF_Builder_MU_Plugin_Blocker.php',
        'PDF_Builder_Nonce_Manager.php',
        'PDF_Builder_Unified_Ajax_Handler.php'
    );
    foreach ($core_classes as $core_class) {
        $core_path = PDF_BUILDER_PLUGIN_DIR . 'src/Core/' . $core_class;
        if (file_exists($core_path)) {
            require_once $core_path;
        }
    }

    // Charger TemplateDefaults depuis core/
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/TemplateDefaults.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/TemplateDefaults.php';
    }

    // Charger les gestionnaires centralisés
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/security-manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/security-manager.php';
    }
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/sanitizer.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/sanitizer.php';
    }

    // Charger les mappings centralisés
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/mappings.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/mappings.php';
    }

    // Charger la classe d'administration depuis src/
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php') && !class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
    }

    // Charger le handler AJAX pour les paramètres Canvas
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Canvas_AJAX_Handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Canvas_AJAX_Handler.php';
    }

    // Charger le gestionnaire de modèles prédéfinis
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'templates/admin/predefined-templates-manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'templates/admin/predefined-templates-manager.php';
    }

    // Charger le contrôleur PDF
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Generator_Controller.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Generator_Controller.php';
    }

    // Charger le handler AJAX d'image de prévisualisation (Phase 3.0)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/preview-image-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/preview-image-handler.php';
    }

    // Charger les handlers AJAX pour les paramètres

    // ============================================================================
    // ✅ INITIALISATION DE L'OBJET WP POUR COMPATIBILITÉ
    // ============================================================================

    /**
     * Initialise l'objet wp global pour éviter les erreurs "wp is not defined"
     * Cette fonction s'exécute très tôt pour garantir la disponibilité de wp
     */
    add_action('admin_enqueue_scripts', function() {
        // Ajouter un script inline qui définit wp si il n'existe pas
        wp_add_inline_script('jquery', '
            if (typeof window.wp === "undefined") {
                window.wp = {
                    api: {
                        models: {},
                        collections: {},
                        views: {}
                    },
                    ajax: {
                        send: function() { return { done: function() {}, fail: function() {} }; }
                    },
                    media: {
                        controller: {
                            Library: function() {},
                            FeaturedImage: function() {}
                        },
                        view: {
                            MediaFrame: {
                                Select: function() {},
                                Post: function() {}
                            }
                        }
                    },
                    util: {
                        parseArgs: function() { return {}; }
                    },
                    template: function() { return ""; }
                };
                
            }
        ', 'before');
    }, 0); // Priorité 0 pour s'exécuter en premier

    // Enregistrer les scripts pour la page de paramètres
    add_action('admin_enqueue_scripts', function() {
        if (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-settings') {
            // Le JavaScript est inclus directement dans les templates, pas besoin de fichier séparé
            wp_localize_script('jquery', 'pdf_builder_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_ajax')
            ));

            // Localisation pour settings-page.js qui utilise pdfBuilderAjax
            wp_localize_script('jquery', 'pdfBuilderAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_ajax'),
                'debug' => array(
                    'enabled' => true,
                    'level' => 'info',
                    'console' => true,
                    'server' => false
                ),
                'strings' => array(
                    'loading' => __('Chargement...', 'pdf-builder-pro'),
                    'error' => __('Erreur', 'pdf-builder-pro'),
                    'success' => __('Succès', 'pdf-builder-pro'),
                    'saving' => __('Sauvegarde en cours...', 'pdf-builder-pro')
                )
            ));
        }
    });

    // 🚀 CHARGEMENT OPTIMISÉ DE REACT POUR L'ÉDITEUR
    add_action('admin_enqueue_scripts', function($hook) {
        // Charger React sur TOUTES les pages admin pour éviter les problèmes de dépendances
        wp_enqueue_script('react', false, [], false, true);
        wp_enqueue_script('react-dom', false, ['react'], false, true);

        // Charger seulement le bundle sur la page de l'éditeur React
        if ($hook === 'pdf-builder_page_pdf-builder-react-editor' || (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-react-editor')) {
            error_log('[BOOTSTRAP] Loading React scripts for hook: ' . $hook . ', page: ' . (isset($_GET['page']) ? $_GET['page'] : 'none'));

            // Charger le bundle PDF Builder (optimisé avec code splitting)
            $bundle_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react-wrapper.min.js';
            wp_enqueue_script(
                'pdf-builder-react-bundle',
                $bundle_url,
                ['react', 'react-dom', 'jquery'],
                PDF_BUILDER_VERSION . '-' . time(),
                true
            );

            // Localiser les variables nécessaires
            $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 1;
            $localize_data = [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_order_actions'),
                'version' => PDF_BUILDER_VERSION,
                'templateId' => $template_id,
                'isEdit' => $template_id > 0,
                'timestamp' => time(),
                'debug' => WP_DEBUG,
                'strings' => [
                    'loading' => __('Chargement...', 'pdf-builder-pro'),
                    'error' => __('Erreur', 'pdf-builder-pro'),
                    'success' => __('Succès', 'pdf-builder-pro'),
                ]
            ];

            // Charger les données du template si template_id est fourni
            if ($template_id > 0) {
                // On ne peut pas accéder à $this->template_processor ici, donc on fait un appel AJAX simple
                // Les données seront chargées via AJAX dans l'app React
            }

            wp_localize_script('pdf-builder-react-bundle', 'pdfBuilderAjax', $localize_data);
        }
    });    // Charger le handler AJAX pour générer les styles des éléments
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/element-styles-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/element-styles-handler.php';
    }

    // Charger l'injecteur de styles pour le canvas (inline)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/canvas-style-injector-inline.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/canvas-style-injector-inline.php';
    }

    // Charger le handler AJAX pour rendre le template en HTML
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/render-template-html.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/render-template-html.php';
    }

    // Charger le handler AJAX pour les templates
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/PDF_Builder_Templates_Ajax.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/PDF_Builder_Templates_Ajax.php';
    }

    $loaded = true;
}

// Fonction pour charger les nouvelles classes PDF_Builder
function pdf_builder_load_new_classes()
{
    static $new_classes_loaded = false;
    if ($new_classes_loaded) {
        return;
    }

    // Charger les interfaces et classes de données
    $data_classes = [
        'src/Interfaces/DataProviderInterface.php',
        'config/data/SampleDataProvider.php',
        'config/data/EditorDataProvider.php',
        'config/data/WooCommerceDataProvider.php'
    ];
    foreach ($data_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les générateurs
    $generator_classes = [
        'src/Generators/BaseGenerator.php',
        'src/Generators/PDFGenerator.php',
        'src/Generators/GeneratorManager.php'
    ];
    foreach ($generator_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les éléments et contrats
    $element_classes = [
        'src/Elements/ElementContracts.php'
    ];
    foreach ($element_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger le core et conventions
    $core_classes = [
        'src/Core/Conventions.php'
    ];
    foreach ($core_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger l'API
    // Preview system moved to preview-system folder
    require_once PDF_BUILDER_PLUGIN_DIR . 'preview-system/index.php';
    
    $api_classes = [
        'api/MediaDiagnosticAPI.php',
        'api/MediaLibraryFixAPI.php'
    ];
    foreach ($api_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les états
    $state_classes = [
        'config/states/PreviewStateManager.php'
    ];
    foreach ($state_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    $new_classes_loaded = true;
}

// Fonction principale de chargement du bootstrap
function pdf_builder_load_bootstrap()
{
    // Protection globale contre les chargements multiples
    static $bootstrap_loaded = false;
    if ($bootstrap_loaded || (defined('PDF_BUILDER_BOOTSTRAP_LOADED') && PDF_BUILDER_BOOTSTRAP_LOADED)) {
        return;
    }
    $bootstrap_loaded = true;

    // CHARGER L'AUTOLOADER POUR LES NOUVELLES CLASSES (PDF_Builder) - DISABLED
    // if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php')) {
    //     require_once PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
    // }

    // Charger la configuration si pas déjà faite
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'config/config.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'config/config.php';
    }

    // Charger le core maintenant que l'autoloader est prêt
    pdf_builder_load_core();
    pdf_builder_load_new_classes();

    // Charger manuellement le Thumbnail Manager pour s'assurer qu'il est disponible
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Thumbnail_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Thumbnail_Manager.php';
    }

    // CHARGER LE HANDLER D'EXPIRATION DE LICENCE
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/License/license-expiration-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/License/license-expiration-handler.php';
    }

    // CHARGER LE GESTIONNAIRE DES LIMITES DE SÉCURITÉ
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Security/Security_Limits_Handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Security/Security_Limits_Handler.php';
    }

    // CHARGER LE GESTIONNAIRE DE RATE LIMITING
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Security/Rate_Limiter.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Security/Rate_Limiter.php';
    }

    // INITIALISER LE VALIDATEUR DE SÉCURITÉ APRÈS LE CHARGEMENT DE WORDPRESS
    if (class_exists('PDF_Builder\\Core\\PDF_Builder_Security_Validator')) {
        \PDF_Builder\Core\PDF_Builder_Security_Validator::get_instance()->init();
    }

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE CANVAS
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Canvas/Canvas_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Canvas/Canvas_Manager.php';
    }

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE SAUVEGARDE/RESTAURATION
    // Nécessaire pour l'onglet système même si la sauvegarde automatique n'est pas activée
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Backup_Restore_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Backup_Restore_Manager.php';
        // Initialiser l'instance
        \PDF_Builder\Managers\PdfBuilderBackupRestoreManager::getInstance();
    }

    // INITIALISER LE GESTIONNAIRE DE TUTORIELS - SUPPRIMÉ

    // ENREGISTRER LES HANDLERS AJAX POUR LE CANVAS
    if (class_exists('PDF_Builder\\Admin\\Canvas_AJAX_Handler')) {
        \PDF_Builder\Admin\Canvas_AJAX_Handler::register_hooks();
    }

    // CHARGER LE GESTIONNAIRE DE NOTIFICATIONS
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Notification_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Notification_Manager.php';
        // Initialiser l'instance
        PDF_Builder_Notification_Manager::get_instance();
    }

    // CHARGER LE GESTIONNAIRE DE PRÉFÉRENCES DE L'ÉDITEUR PDF
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDFEditorPreferences.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDFEditorPreferences.php';
        // Initialiser l'instance
        PDFEditorPreferences::get_instance();
    }

    // Charger les fonctions globales de préférences si elles ne sont pas déjà définies
    if (!function_exists('pdf_builder_get_user_preference')) {
        /**
         * Obtenir une préférence utilisateur
         */
        function pdf_builder_get_user_preference($key, $default = null) {
            $preferences = PDFEditorPreferences::get_instance();
            $all_prefs = $preferences->get_preferences();
            return isset($all_prefs[$key]) ? $all_prefs[$key] : $default;
        }

        /**
         * Sauvegarder une préférence utilisateur
         */
        function pdf_builder_set_user_preference($key, $value) {
            $preferences = PDFEditorPreferences::get_instance();
            $current = $preferences->get_preferences();
            $current[$key] = $value;
            return $preferences->save_preferences($current);
        }

        /**
         * Obtenir toutes les préférences utilisateur
         */
        function pdf_builder_get_all_user_preferences() {
            $preferences = PDFEditorPreferences::get_instance();
            return $preferences->get_preferences();
        }
    }

    // CHARGER LES STYLES ET SCRIPTS DES NOTIFICATIONS - DESACTIVE TEMPORAIREMENT
    /*
    add_action('admin_enqueue_scripts', function() {
        // Charger le CSS des notifications
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'resources/assets/css/notifications.css')) {
            wp_enqueue_style(
                'pdf-builder-notifications',
                PDF_BUILDER_PLUGIN_URL . 'resources/assets/css/notifications.css',
                array(),
                PDF_BUILDER_VERSION . '-' . time(),
                'all'
            );
        }

        // Charger le JavaScript des notifications
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'resources/assets/js/notifications.js')) {
            wp_enqueue_script(
                'pdf-builder-notifications',
                PDF_BUILDER_PLUGIN_URL . 'resources/assets/js/notifications.js',
                array('jquery'),
                PDF_BUILDER_VERSION . '-' . time(),
                true
            );

            // Localiser les variables pour les notifications
            wp_localize_script('pdf-builder-notifications', 'pdfBuilderAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_ajax'),
                'version' => PDF_BUILDER_VERSION,
                'timestamp' => time(),
                'debug' => WP_DEBUG,
                'strings' => [
                    'loading' => __('Chargement...', 'pdf-builder-pro'),
                    'error' => __('Erreur', 'pdf-builder-pro'),
                    'success' => __('Succès', 'pdf-builder-pro'),
                ]
            ]);

            // Localiser les données de notifications pour JavaScript
            wp_localize_script('pdf-builder-notifications', 'pdfBuilderNotifications', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_notifications'),
                'settings' => [
                    'enabled' => true,
                    'position' => 'top-right',
                    'duration' => 5000,
                    'max_notifications' => 5,
                    'animation' => 'slide',
                    'theme' => 'modern'
                ],
                'strings' => [
                    'success' => __('Succès', 'pdf-builder-pro'),
                    'error' => __('Erreur', 'pdf-builder-pro'),
                    'warning' => __('Avertissement', 'pdf-builder-pro'),
                    'info' => __('Information', 'pdf-builder-pro'),
                    'close' => __('Fermer', 'pdf-builder-pro')
                ]
            ]);

            // Définir les paramètres de debug JavaScript UNIQUEMENT pour notifications.js
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $debug_settings = [
                'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
                'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
                'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
                'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
            ];
            $debug_json = wp_json_encode($debug_settings);
            if ($debug_json !== false) {
                wp_add_inline_script('pdf-builder-notifications', 'window.pdfBuilderDebugSettings = ' . $debug_json . ';', 'before');
            }
        }

        // Charger les outils développeur
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'resources/assets/js/developer-tools.js')) {
            wp_enqueue_script(
                'pdf-builder-developer-tools',
                PDF_BUILDER_PLUGIN_URL . 'resources/assets/js/developer-tools.js',
                array('jquery', 'pdf-builder-notifications'),
                PDF_BUILDER_VERSION . '-' . time(),
                true
            );

            // Définir les paramètres de debug JavaScript pour developer-tools.js
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $debug_settings = [
                'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
                'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
                'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
                'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
            ];
            $debug_json = wp_json_encode($debug_settings);
            if ($debug_json !== false) {
                wp_add_inline_script('pdf-builder-developer-tools', 'window.pdfBuilderDebugSettings = ' . $debug_json . ';', 'before');
            }
        }
    });
    */

    // INITIALISER LE GESTIONNAIRE D'ONBOARDING
    // Retarder complètement le chargement et l'initialisation au hook 'init'
    add_action('init', function() {
        // Les utilitaires sont déjà chargés ci-dessus dans le même hook 'init'
        if (!class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
            // Fallback: charger manuellement si la classe n'est pas trouvée
            $onboarding_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php';
            if (file_exists($onboarding_path)) {
                require_once $onboarding_path;
            }
        }

        // Utiliser la classe alias qui garantit la disponibilité
        if (class_exists('PDF_Builder_Onboarding_Manager_Alias')) {
            // Instancier explicitement pour s'assurer que les hooks AJAX sont enregistrés
            PDF_Builder_Onboarding_Manager_Alias::get_instance();
        } elseif (class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
            \PDF_Builder\Utilities\PDF_Builder_Onboarding_Manager::get_instance();
        }
    }, 5);

    // INITIALISER LE GESTIONNAIRE RGPD
    // Retarder complètement le chargement et l'initialisation au hook 'init'
    add_action('init', function() {
        // Les utilitaires sont déjà chargés ci-dessus dans le même hook 'init'
        if (!class_exists('PDF_Builder\\Utilities\\PDF_Builder_GDPR_Manager')) {
            // Fallback: charger manuellement si la classe n'est pas trouvée
            $gdpr_path = PDF_BUILDER_PLUGIN_DIR . 'src/Utilities/PDF_Builder_GDPR_Manager.php';
            if (file_exists($gdpr_path)) {
                require_once $gdpr_path;
            }
        }
        if (class_exists('PDF_Builder\\Utilities\\PDF_Builder_GDPR_Manager')) {
            \PDF_Builder\Utilities\PDF_Builder_GDPR_Manager::get_instance();
        }
    }, 5);

    // CHARGER LES HOOKS AJAX ESSENTIELS TOUJOURS, MÊME EN MODE FALLBACK
    pdf_builder_register_essential_ajax_hooks();

    // INSTANCIER L'API PREVIEW POUR LES ROUTES REST (Étape 1.4)
    add_action('init', function() {
        if (class_exists('PDF_Builder\\Api\\PreviewImageAPI')) {
            new \PDF_Builder\Api\PreviewImageAPI();
        }
    });

    // Vérification que les classes essentielles sont chargées
    if (class_exists('PDF_Builder\\Core\\PdfBuilderCore')) {
        $core = \PDF_Builder\Core\PdfBuilderCore::getInstance();
        if (method_exists($core, 'init')) {
            $core->init();
        }

        // Initialiser l'interface d'administration dans l'admin OU lors d'AJAX pour nos actions
        // DÉPLACÉ DANS LE HOOK 'init' pour que $_REQUEST soit disponible
        add_action('init', function() use ($core) {
            // Créer l'instance admin plus tôt pour éviter les problèmes de timing
            // La classe gérera elle-même si elle doit s'activer
            if (class_exists('PDF_Builder\\Admin\\PdfBuilderAdminNew')) {
                try {
                    $admin = \PDF_Builder\Admin\PdfBuilderAdminNew::getInstance($core);
                } catch (Exception $e) {
                    // Fallback en cas d'erreur
                    add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
                }
            } elseif (wp_doing_ajax()) {
                // Ne rien faire pour les appels AJAX non-PDF
            } else {
                // Fallback: enregistrer un menu simple si la classe principale n'est pas disponible
                add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
            }
        }, 1);
    } else {
        // Fallback: enregistrer un menu simple si le core n'est pas disponible
        add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
    }

    // Marquer comme chargé globalement
    define('PDF_BUILDER_BOOTSTRAP_LOADED', true);
}

// Fonction simple pour enregistrer le menu admin - DISABLED: Conflit avec PDF_Builder_Admin.php
function pdf_builder_register_admin_menu_simple()
{
    // DISABLED: Garder seulement le système principal PDF_Builder_Admin.php
    // add_menu_page(
    //     'PDF Builder Pro',
    //     'PDF Builder',
    //     'read',
    //     'pdf-builder-pro',
    //     'pdf_builder_admin_page_simple',
    //     'dashicons-pdf',
    //     30
    // );
    // add_submenu_page(
    //     'pdf-builder-pro',
    //     __('Templates', 'pdf-builder-pro'),
    //     __('Templates', 'pdf-builder-pro'),
    //     'read',
    //     'pdf-builder-templates',
    //     'pdf_builder_templates_page_simple'
    // );
}

// Callbacks simples
function pdf_builder_admin_page_simple()
{
    if (!is_user_logged_in()) {
        wp_die(__('Vous devez être connecté.', 'pdf-builder-pro'));
    }
    echo '<div class="wrap"><h1>PDF Builder Pro</h1><p>Page principale en cours de développement.</p></div>';
}

function pdf_builder_templates_page_simple()
{
    if (!is_user_logged_in()) {
        wp_die(__('Vous devez être connecté.', 'pdf-builder-pro'));
    }
    echo '<div class="wrap"><h1>Templates</h1><p>Page templates en cours de développement.</p></div>';
}

// Fonction pour enregistrer les hooks AJAX essentiels
function pdf_builder_register_essential_ajax_hooks()
{
    // Charger les classes nécessaires pour les handlers AJAX
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Template_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Template_Manager.php';
    }

    // PDF_Builder_Admin.php déjà chargé plus haut

    // Créer une instance du template manager pour les handlers AJAX
    $template_manager = null;
    if (class_exists('PDF_Builder\\Managers\\PdfBuilderTemplateManager')) {
        $template_manager = new \PDF_Builder\Managers\PdfBuilderTemplateManager();
    }

    // Enregistrer les hooks AJAX essentiels - DISABLED: Conflit avec AjaxHandler.php
    // Garder seulement le système principal AjaxHandler.php
    /*
    // add_action('wp_ajax_pdf_builder_save_template', function() use ($template_manager) {
    //     $this->debug_log('Bootstrap save handler called\');
    //     if ($template_manager && method_exists($template_manager, 'ajaxSaveTemplateV3')) {
    //         $template_manager->ajaxSaveTemplateV3();
    //     } else {
    //         $this->debug_log('Template manager not available\');
    //         // Fallback handler
    //         pdf_builder_fallback_ajax_save_template();
    //     }
    // });

    // add_action('wp_ajax_pdf_builder_load_template', function() use ($template_manager) {
    //     if ($template_manager && method_exists($template_manager, 'ajaxLoadTemplate')) {
    //         $template_manager->ajaxLoadTemplate();
    //     } else {
    //         // Fallback handler
    //         pdf_builder_fallback_ajax_load_template();
    //     }
    // });

    // Action AJAX appelée par React pour charger un template - DISABLED: Conflit avec AjaxHandler.php
    // add_action('wp_ajax_pdf_builder_get_template', function() use ($template_manager) {
    //     if ($template_manager && method_exists($template_manager, 'ajaxLoadTemplate')) {
    //         $template_manager->ajaxLoadTemplate();
    //     } else {
    //         // Fallback handler
    //         pdf_builder_fallback_ajax_load_template();
    //     }
    // });

    // add_action('wp_ajax_pdf_builder_auto_save_template', function() use ($template_manager) {
    //     if ($template_manager && method_exists($template_manager, 'ajax_auto_save_template')) {
    //         $template_manager->ajax_auto_save_template();
    //     } else {
    //         // Fallback handler
    //         pdf_builder_fallback_ajax_auto_save_template();
    //     }
    // });
    */
}

// Fonction de chargement différé (maintenant vide car les hooks sont enregistrés au bootstrap)
function pdf_builder_load_core_when_needed()
{
    // Les hooks essentiels sont déjà enregistrés dans pdf_builder_load_bootstrap()
}

// Handlers AJAX de fallback - DISABLED: Plus utilisés après désactivation des actions AJAX
/*
function pdf_builder_fallback_ajax_save_template()
{
    // Vérifications de base
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Récupérer les données
    $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

    if (empty($template_data) || !$template_id) {
        wp_send_json_error('Données manquantes');
        return;
    }

    // Décoder le JSON pour vérifier les données
    $decoded_data = json_decode($template_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Données JSON invalides');
        return;
    }

    // Sauvegarder dans la base de données
    global $wpdb;
    $table = $wpdb->prefix . 'pdf_builder_templates';

    $result = $wpdb->update(
        $table,
        ['template_data' => $template_data, 'updated_at' => current_time('mysql')],
        ['id' => $template_id],
        ['%s', '%s'],
        ['%d']
    );

    if ($result !== false) {
        wp_send_json_success(['message' => 'Template sauvegardé avec succès']);
    } else {
        wp_send_json_error('Erreur lors de la sauvegarde');
    }
}

function pdf_builder_fallback_ajax_load_template()
{
    // Vérifications de base
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

    if (!$template_id) {
        wp_send_json_error('ID de template manquant');
        return;
    }

    // Charger depuis la base de données
    global $wpdb;
    $table = $wpdb->prefix . 'pdf_builder_templates';

    $template = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $template_id),
        ARRAY_A
    );

    if ($template) {
        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Erreur de décodage JSON');
            return;
        }

        wp_send_json_success([
            'template' => $template_data,
            'id' => $template['id'],
            'name' => $template['name']
        ]);
    } else {
        wp_send_json_error('Template non trouvé');
    }
}

function pdf_builder_fallback_ajax_auto_save_template()
{
    // Même logique que save_template mais pour l'auto-save
    pdf_builder_fallback_ajax_save_template();
}
*/

// Chargement différé du core
function pdf_builder_load_core_on_demand()
{
    static $core_loaded = false;
    if ($core_loaded) {
        return;
    }

    // Chargement d'urgence des utilitaires dès le départ
    pdf_builder_load_utilities_emergency();

    // Détection ultra-rapide
    $load_core = false;
    if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') === 0) {
        $load_core = true;
    } elseif (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'pdf_builder') === 0) {
        $load_core = true;
    } elseif (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action'])) {
        $pdf_builder_ajax_actions = [
            'pdf_builder_save_template',
            'pdf_builder_load_template',
            'pdf_builder_auto_save_template',
            // Actions AJAX de l'Onboarding Manager
            'pdf_builder_complete_onboarding_step',
            'pdf_builder_skip_onboarding',
            'pdf_builder_reset_onboarding',
            'pdf_builder_load_onboarding_step',
            'pdf_builder_save_template_selection',
            'pdf_builder_save_freemium_mode',
            'pdf_builder_update_onboarding_step',
            'pdf_builder_save_template_assignment',
            'pdf_builder_mark_onboarding_complete'
        ];
        if (in_array($_REQUEST['action'], $pdf_builder_ajax_actions)) {
            $load_core = true;
        }
    }

    if ($load_core) {
        pdf_builder_load_core();
        if (class_exists('PDF_Builder\Core\PdfBuilderCore')) {
            try {
                \PDF_Builder\Core\PdfBuilderCore::getInstance()->init();
                $core_loaded = true;
            } catch (Exception $e) {
                return;
            }
        }
    }
}

// Initialiser les paramètres par défaut du canvas
function pdf_builder_init_canvas_defaults()
{
    $defaults = [
        'canvas_element_borders_enabled' => true,
        'canvas_border_width' => 1,
        'canvas_border_color' => '#007cba',
        'canvas_border_spacing' => 2,
        'canvas_resize_handles_enabled' => true,
        'canvas_handle_size' => 8,
        'canvas_handle_color' => '#007cba',
        'canvas_handle_hover_color' => '#ffffff'
    ];

    foreach ($defaults as $option => $default_value) {
        if (get_option($option) === false) {
            add_option($option, $default_value);
        }
    }
}

// Defer the call to ensure WordPress is fully loaded
add_action('init', 'pdf_builder_init_canvas_defaults');

// AJAX handler pour obtenir un nonce frais
function pdf_builder_ajax_get_fresh_nonce()
{
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }

    $nonce = wp_create_nonce('pdf_builder_ajax');
    wp_send_json_success(array(
        'nonce' => $nonce,
        'timestamp' => time()
    ));
}

// AJAX handler pour récupérer un template par ID
function pdf_builder_ajax_get_template()
{
    // Vérifier le nonce de sécurité
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'pdf_builder_ajax')) {
        wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
        return;
    }

    // Vérifier les permissions utilisateur
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(__('Permission refusée.', 'pdf-builder-pro'));
        return;
    }

    // Valider et récupérer l'ID du template
    $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
    if (!$template_id || $template_id < 1) {
        wp_send_json_error(__('ID du template manquant ou invalide.', 'pdf-builder-pro'));
        return;
    }

    // Récupérer le template depuis la table personnalisée
    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);

    // Si le template n'est pas trouvé dans la table personnalisée, chercher dans wp_posts
    if (!$template) {
        $post = get_post($template_id);
        if ($post && $post->post_type === 'pdf_template') {
            $template_data_raw = get_post_meta($post->ID, '_pdf_template_data', true);
            if (!empty($template_data_raw)) {
                $template = array(
                    'id' => $post->ID,
                    'name' => $post->post_title,
                    'template_data' => $template_data_raw,
                    'created_at' => $post->post_date,
                    'updated_at' => $post->post_modified
                );
            }
        }
    }

    if (!$template) {
        wp_send_json_error(__('Template non trouvé.', 'pdf-builder-pro'));
        return;
    }

    // Décoder les données JSON du template
    $template_data = json_decode($template['template_data'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(__('Erreur lors du décodage des données du template.', 'pdf-builder-pro'));
        return;
    }

    // Gérer les différents formats de données
    $elements = [];
    $canvas = null;

    if (is_array($template_data)) {
        if (isset($template_data['elements'])) {
            $elements = $template_data['elements'];
            $canvas = isset($template_data['canvas']) ? $template_data['canvas'] : null;
        } elseif (isset($template_data['pages']) && is_array($template_data['pages']) && !empty($template_data['pages'])) {
            $elements = $template_data['pages'][0]['elements'] ?? [];
            $canvas = isset($template_data['canvas']) ? $template_data['canvas'] : null;
        } else {
            $elements = $template_data;
            $canvas = null;
        }
    } else {
        wp_send_json_error(__('Format de données du template invalide.', 'pdf-builder-pro'));
        return;
    }

    // Traiter les éléments
    if (is_string($elements)) {
        $unescaped_elements = stripslashes($elements);
        $decoded_elements = json_decode($unescaped_elements, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $elements = $decoded_elements;
        } else {
            $elements = [];
        }
    } elseif (!is_array($elements)) {
        $elements = [];
    }

    // Traiter le canvas si présent
    if ($canvas !== null) {
        if (is_string($canvas)) {
            $unescaped_canvas = stripslashes($canvas);
            $decoded_canvas = json_decode($unescaped_canvas, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $canvas = $decoded_canvas;
            } else {
                $canvas = null;
            }
        } elseif (!is_array($canvas) && !is_null($canvas)) {
            $canvas = null;
        }
    }

    // Vérifier que elements est défini
    if (!isset($elements)) {
        wp_send_json_error(__('Données du template incomplètes.', 'pdf-builder-pro'));
        return;
    }

    // Transformer les éléments dans le format attendu par React
    $transformed_elements = [];
    foreach ($elements as $element) {
        $transformed_element = [];

        // Copier les propriétés de base
        if (isset($element['id'])) {
            $transformed_element['id'] = $element['id'];
        }
        if (isset($element['type'])) {
            $transformed_element['type'] = $element['type'];
        }
        if (isset($element['content'])) {
            $transformed_element['content'] = $element['content'];
        }

        // Gérer les positions
        if (isset($element['position']['x'])) {
            $transformed_element['x'] = (int)$element['position']['x'];
        } elseif (isset($element['x'])) {
            $transformed_element['x'] = (int)$element['x'];
        }

        if (isset($element['position']['y'])) {
            $transformed_element['y'] = (int)$element['position']['y'];
        } elseif (isset($element['y'])) {
            $transformed_element['y'] = (int)$element['y'];
        }

        // Gérer les dimensions
        if (isset($element['size']['width'])) {
            $transformed_element['width'] = (int)$element['size']['width'];
        } elseif (isset($element['width'])) {
            $transformed_element['width'] = (int)$element['width'];
        }

        if (isset($element['size']['height'])) {
            $transformed_element['height'] = (int)$element['size']['height'];
        } elseif (isset($element['height'])) {
            $transformed_element['height'] = (int)$element['height'];
        }

        // Copier les autres propriétés de style
        $style_properties = ['fontSize', 'fontWeight', 'color', 'textAlign', 'verticalAlign',
            'backgroundColor', 'borderColor', 'borderWidth', 'borderStyle', 'rotation', 'opacity'];

        if (isset($element['style']) && is_array($element['style'])) {
            foreach ($style_properties as $prop) {
                if (isset($element['style'][$prop])) {
                    $transformed_element[$prop] = $element['style'][$prop];
                }
            }
        } else {
            foreach ($style_properties as $prop) {
                if (isset($element[$prop])) {
                    $transformed_element[$prop] = $element[$prop];
                }
            }
        }

        // Pour les éléments text, utiliser content comme text
        if (isset($element['type']) && $element['type'] === 'text' && isset($element['content'])) {
            $transformed_element['text'] = $element['content'];
        }

        // Copier d'autres propriétés utiles
        $copy_properties = ['visible', 'locked', 'zIndex', 'name', 'src', 'logoUrl', 'defaultSrc',
            'alignment', 'borderRadius'];
        foreach ($copy_properties as $prop) {
            if (isset($element[$prop])) {
                $transformed_element[$prop] = $element[$prop];
            }
        }

        // Propriétés par défaut
        if (!isset($transformed_element['x'])) {
            $transformed_element['x'] = 0;
        }
        if (!isset($transformed_element['y'])) {
            $transformed_element['y'] = 0;
        }
        if (!isset($transformed_element['width'])) {
            $transformed_element['width'] = 100;
        }
        if (!isset($transformed_element['height'])) {
            $transformed_element['height'] = 50;
        }
        if (!isset($transformed_element['visible'])) {
            $transformed_element['visible'] = true;
        }
        if (!isset($transformed_element['locked'])) {
            $transformed_element['locked'] = false;
        }

        $transformed_elements[] = $transformed_element;
    }

    $elements = $transformed_elements;

    // Enrichir les logos company_logo avec src si absent
    foreach ($elements as &$el) {
        if (isset($el['type']) && $el['type'] === 'company_logo') {
            if (empty($el['src']) && empty($el['logoUrl'])) {
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) {
                    $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                    if ($logo_url) {
                        $el['src'] = $logo_url;
                    }
                } else {
                    $site_logo_id = get_option('site_logo');
                    if ($site_logo_id) {
                        $logo_url = wp_get_attachment_image_url($site_logo_id, 'full');
                        if ($logo_url) {
                            $el['src'] = $logo_url;
                        }
                    }
                }
            }
        }
    }
    unset($el);

    $cache_data = array(
        'id' => $template['id'],
        'name' => $template['name'],
        'elements' => $elements,
        'canvas' => $canvas
    );
    wp_send_json_success($cache_data);
}

// ============================================================================
// CHARGER LES HANDLERS AJAX
// ============================================================================

// Inclure et initialiser les handlers AJAX
$ajax_handlers_path = PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/Ajax_Handlers.php';
if (file_exists($ajax_handlers_path)) {
    require_once $ajax_handlers_path;
}

// ============================================================================
// INITIALISER LES PARAMÈTRES CANVAS PAR DÉFAUT
// ============================================================================

/**
 * Initialise les paramètres canvas avec leurs valeurs par défaut
 * Cette fonction ne fait rien si les paramètres existent déjà
 */
function pdf_builder_initialize_canvas_defaults() {
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $canvas_defaults = [
        // Dimensions & Format
        'pdf_builder_canvas_format' => 'A4',
        'pdf_builder_canvas_orientation' => 'portrait',
        'pdf_builder_canvas_unit' => 'px',
        'pdf_builder_canvas_dpi' => 96,
        'pdf_builder_canvas_width' => 794,
        'pdf_builder_canvas_height' => 1123,

        // Affichage & Dimensions
        'pdf_builder_canvas_allow_portrait' => '1',
        'pdf_builder_canvas_allow_landscape' => '1',
        'pdf_builder_canvas_default_orientation' => 'portrait',

        // Apparence
        'pdf_builder_canvas_bg_color' => '#ffffff',
        'pdf_builder_canvas_border_color' => '#cccccc',
        'pdf_builder_canvas_border_width' => 1,
        'pdf_builder_canvas_shadow_enabled' => '0',
        'pdf_builder_canvas_container_bg_color' => '#f8f9fa',

        // Zoom & Navigation
        'pdf_builder_canvas_zoom_min' => 10,
        'pdf_builder_canvas_zoom_max' => 500,
        'pdf_builder_canvas_zoom_default' => 100,
        'pdf_builder_canvas_zoom_step' => 25,

        // Grille
        'pdf_builder_canvas_grid_enabled' => '1',
        'pdf_builder_canvas_grid_size' => 20,
        'pdf_builder_canvas_snap_to_grid' => '1',
        'pdf_builder_canvas_guides_enabled' => '1',

        // Interactions
        'pdf_builder_canvas_drag_enabled' => '1',
        'pdf_builder_canvas_resize_enabled' => '1',
        'pdf_builder_canvas_rotate_enabled' => '0',
        'pdf_builder_canvas_multi_select' => '1',
        'pdf_builder_canvas_keyboard_shortcuts' => '1',
        'pdf_builder_canvas_selection_mode' => 'bounding_box',

        // Export
        'pdf_builder_canvas_export_format' => 'png',
        'pdf_builder_canvas_export_quality' => 90,
        'pdf_builder_canvas_export_transparent' => '0',

        // Performance
        'pdf_builder_canvas_fps_target' => 60,
        'pdf_builder_canvas_memory_limit_js' => 128,
        'pdf_builder_canvas_memory_limit_php' => 256,
        'pdf_builder_canvas_lazy_loading_editor' => '1',
        'pdf_builder_canvas_preload_critical' => '1',
        'pdf_builder_canvas_lazy_loading_plugin' => '1',

        // Debug
        'pdf_builder_canvas_debug_enabled' => '0',
        'pdf_builder_canvas_performance_monitoring' => '0',
        'pdf_builder_canvas_error_reporting' => '0',
    ];

    foreach ($canvas_defaults as $option_name => $default_value) {
        if (!get_option($option_name)) {
            update_option($option_name, $default_value);
        }
    }

    $initialized = true;
}

// Initialiser les paramètres canvas par défaut
add_action('init', 'pdf_builder_initialize_canvas_defaults');

// ============================================================================
// INITIALISER LE SYSTÈME DE MIGRATION (DÉPLACÉ PLUS HAUT)
// ============================================================================
// Le système de migration est maintenant initialisé juste après constants.php

// ============================================================================
// CHARGER LE LOADER DES STYLES DE LA PAGE DE PARAMÈTRES
// ============================================================================
// Charge le CSS de settings au moment approprié (admin_print_styles)
if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'pdf-builder-settings') {
    require_once __DIR__ . '/templates/admin/settings-loader.php';
}

// ============================================================================
// ACTIONS AJAX POUR LES PARAMÈTRES (DÉPLACÉES DEPUIS settings-main.php)
// ============================================================================

// Gestionnaire AJAX des paramètres développeur
add_action('wp_ajax_pdf_builder_developer_save_settings', function() {
    // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder Développeur: Gestionnaire AJAX DÉMARRÉ à ' . date('Y-m-d H:i:s')); }

    try {
        // Journaliser toutes les données POST pour le débogage
        // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder Développeur: Données POST reçues: ' . print_r($_POST, true)); }

        // Vérifier le nonce
        $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
        $nonce_valid = wp_verify_nonce($nonce_value, 'pdf_builder_ajax');
        // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder Développeur: Résultat de vérification du nonce: ' . ($nonce_valid ? 'VALIDE' : 'INVALIDE')); }

        if (!$nonce_valid) {
            // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder Développeur: Échec de vérification du nonce'); }
            wp_send_json_error(['message' => 'Échec de vérification de sécurité']);
            return;
        }

        // Vérifier la capacité utilisateur
        $has_capability = current_user_can('manage_options');
        // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder Développeur: Vérification de capacité utilisateur: ' . ($has_capability ? 'A' : 'NON')); }

        if (!$has_capability) {
            // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder Développeur: Permissions insuffisantes'); }
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Obtenir la clé et la valeur du paramètre
        $setting_key = sanitize_text_field($_POST['setting_key'] ?? '');
        $setting_value = sanitize_text_field($_POST['setting_value'] ?? '');

        // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("PDF Builder Développeur: Clé paramètre: '{$setting_key}', valeur: '{$setting_value}'"); }

        // Valider la clé de paramètre (autoriser seulement les paramètres développeur)
        $allowed_keys = [
            'pdf_builder_developer_enabled',
            'pdf_builder_canvas_debug_enabled',
            'pdf_builder_developer_password'
        ];

        if (!in_array($setting_key, $allowed_keys)) {
            // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("PDF Builder Développeur: Clé paramètre invalide: {$setting_key}"); }
            wp_send_json_error(['message' => 'Clé paramètre invalide']);
            return;
        }

        // Obtenir les paramètres existants
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Mettre à jour le paramètre spécifique
        $settings[$setting_key] = $setting_value;

        // Sauvegarder en base de données
        $updated = pdf_builder_update_option('pdf_builder_settings', $settings);
        // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log("PDF Builder Développeur: Résultat update_option: " . ($updated ? 'SUCCÈS' : 'AUCUN CHANGEMENT')); }

        wp_send_json_success([
            'message' => 'Paramètre développeur sauvegardé avec succès',
            'setting' => $setting_key,
            'value' => $setting_value
        ]);

    } catch (Exception $e) {
        // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder Développeur: Erreur AJAX - ' . $e->getMessage()); }
        wp_send_json_error(['message' => $e->getMessage()]);
    }
});

// ============================================================================
// ✅ CHARGER LE SCRIPT DE MIGRATION CANVAS
// ============================================================================

$migration_ajax_path = PDF_BUILDER_PLUGIN_DIR . 'migrate_canvas_settings_ajax.php';
if (file_exists($migration_ajax_path)) {
    require_once $migration_ajax_path;
}

// ============================================================================
// ✅ INITIALISATION DU PLANIFICATEUR DE TÂCHES
// ============================================================================

// ============================================================================
// ✅ CHARGER LE SCRIPT DE DIAGNOSTIC REST API
// FIN DU BOOTSTRAP
// ============================================================================



