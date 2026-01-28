<?php

/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

error_log('[DEBUG] PDF Builder: bootstrap.php loaded');

// Empêcher l'accès direct (sauf pour les tests)
if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit('Direct access not allowed');
}

// Le débogage est déjà configuré dans wp-config.php

// ========================================================================
// ✅ CHARGEMENT DE L'AUTOLOADER COMPOSER
// ========================================================================
$autoload_path = PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else {
    error_log('[BOOTSTRAP] Composer autoloader not found at: ' . $autoload_path);
}

// ========================================================================
// ✅ INJECTION DU NONCE DANS LE HEAD - TRÈS TÔT
// Cela s'exécute avant admin_head et garantit que le nonce est disponible
// ========================================================================
// Fonction d'injection du nonce
function pdf_builder_inject_nonce() {
    // Vérifier qu'on est sur la bonne page
    if (!\is_admin()) {
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
    
})();
</script>

SCRIPT;
    
    // Remplacer les placeholders
    $script = str_replace('%NONCE%', $nonce ? \esc_js($nonce) : '', $script);
    $script = str_replace('%AJAX_URL%', $ajax_url ? \esc_js($ajax_url) : '', $script);
    $script = str_replace('%TIMESTAMP%', time(), $script);
    
    echo $script;
}

add_action('admin_head', 'pdf_builder_inject_nonce', 1);

// Vérifier si on est sur une page admin
if (\is_admin()) {
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
// ✅ FONCTION DE CHARGEMENT D'URGENCE DES UTILITAIRES
// ============================================================================

/**
 * Fonction d'urgence pour charger les utilitaires si nécessaire
 * Peut être appelée depuis n'importe où pour garantir la disponibilité des classes
 */
function pdf_builder_load_utilities_emergency() {
    static $utilities_loaded = false;

    if ($utilities_loaded) {
        error_log('[DEBUG] PDF Builder: Utilities already loaded, skipping');
        return;
    }

    error_log('[DEBUG] PDF Builder: Loading utilities emergency mode');

    $utilities = array(
        'PDF_Builder_Onboarding_Manager.php',
        'PDF_Builder_GDPR_Manager.php'
    );

    foreach ($utilities as $utility) {
        $utility_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/' . $utility;
        if (file_exists($utility_path) && !class_exists('PDF_Builder\\Utilities\\' . str_replace('.php', '', $utility))) {
            error_log('[DEBUG] PDF Builder: Loading utility: ' . $utility);
            require_once $utility_path;
            $class_name = 'PDF_Builder\\Utilities\\' . str_replace('.php', '', $utility);
            if (class_exists($class_name)) {
                error_log('[DEBUG] PDF Builder: Utility loaded successfully: ' . $class_name);
            } else {
                error_log('[WARN] PDF Builder: Utility class not found after loading: ' . $class_name);
            }
        } elseif (class_exists('PDF_Builder\\Utilities\\' . str_replace('.php', '', $utility))) {
            error_log('[DEBUG] PDF Builder: Utility already loaded: ' . $utility);
        } else {
            error_log('[ERROR] PDF Builder: Utility file not found: ' . $utility_path);
        }
    }

    $utilities_loaded = true;
    error_log('[DEBUG] PDF Builder: Utilities emergency loading completed');
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
        error_log('[DEBUG] PDF Builder: Onboarding Manager class not found, attempting emergency load');
        pdf_builder_load_utilities_emergency();

        // Double vérification avec chargement manuel
        $onboarding_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php';
        if (file_exists($onboarding_path)) {
            error_log('[DEBUG] PDF Builder: Loading Onboarding Manager from: ' . $onboarding_path);
            require_once $onboarding_path;
            if (class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
                error_log('[DEBUG] PDF Builder: Onboarding Manager loaded successfully');
            } else {
                error_log('[ERROR] PDF Builder: Onboarding Manager class still not found after manual loading');
            }
        } else {
            error_log('[ERROR] PDF Builder: Onboarding Manager file not found at: ' . $onboarding_path);
        }
    } else {
        error_log('[DEBUG] PDF Builder: Onboarding Manager class already available');
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

    error_log('[DEBUG] PDF Builder: === DIAGNOSTIC Onboarding Manager ===');
    error_log('[DEBUG] PDF Builder: Real class exists: ' . ($class_exists ? 'YES' : 'NO'));
    error_log('[DEBUG] PDF Builder: Alias class exists: ' . ($alias_exists ? 'YES' : 'NO'));
    error_log('[DEBUG] PDF Builder: Standalone class exists: ' . ($standalone_exists ? 'YES' : 'NO'));
    error_log('[DEBUG] PDF Builder: File exists: ' . ($file_exists ? 'YES' : 'NO'));
    error_log('[DEBUG] PDF Builder: Plugin dir defined: ' . (defined('PDF_BUILDER_PLUGIN_DIR') ? 'YES' : 'NO'));

    $message = "=== DIAGNOSTIC PDF_Builder_Onboarding_Manager ===\n";
    $message .= "Classe réelle existe: " . ($class_exists ? 'OUI' : 'NON') . "\n";
    $message .= "Classe alias existe: " . ($alias_exists ? 'OUI' : 'NON') . "\n";
    $message .= "Classe standalone existe: " . ($standalone_exists ? 'OUI' : 'NON') . "\n";
    $message .= "Fichier existe: " . ($file_exists ? 'OUI' : 'NON') . "\n";
    $message .= "Plugin activé: " . (defined('PDF_BUILDER_PLUGIN_DIR') ? 'OUI' : 'NON') . "\n";
    $message .= "Bootstrap chargé: " . (function_exists('pdf_builder_load_utilities_emergency') ? 'OUI' : 'NON') . "\n";

    if (!$class_exists) {
        error_log('[DEBUG] PDF Builder: Attempting emergency loading of Onboarding Manager');
        $message .= "Tentative de chargement d'urgence...\n";
        pdf_builder_ensure_onboarding_manager();
        $after_load = class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager');
        error_log('[DEBUG] PDF Builder: After emergency load: ' . ($after_load ? 'SUCCESS' : 'FAILED'));
        $message .= "Après chargement: " . ($after_load ? 'SUCCÈS' : 'ÉCHEC') . "\n";
    } else {
        error_log('[DEBUG] PDF Builder: Onboarding Manager already available');
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
            if (!\is_ssl() && !$is_forwarded_ssl) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder HTTPS] Redirecting to HTTPS. host=' . ($_SERVER['HTTP_HOST'] ?? '') . ', uri=' . ($_SERVER['REQUEST_URI'] ?? '')); }
                }
                $host = $_SERVER['HTTP_HOST'] ?? '';
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                if (!empty($host)) {
                    $redirect = 'https://' . $host . $uri;
                    // Preserver host and redirect safely
                    \wp_safe_redirect($redirect, 301);
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
            if (!\is_ssl() && !$is_forwarded_ssl) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder HTTPS] Admin redirecting to HTTPS. host=' . ($_SERVER['HTTP_HOST'] ?? '') . ', uri=' . ($_SERVER['REQUEST_URI'] ?? '')); }
                }
                $host = $_SERVER['HTTP_HOST'] ?? '';
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                if (!empty($host)) {
                    $redirect = 'https://' . $host . $uri;
                    \wp_safe_redirect($redirect, 301);
                    exit;
                }
            }
        }
    }, 1);
}

// Enregistrer les paramètres principaux
add_action('admin_init', function() {
    \register_setting('pdf_builder_settings', 'pdf_builder_settings', array(
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
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/security-manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/security-manager.php';
        if (class_exists('PDF_Builder_Security_Manager')) {
            error_log('[DEBUG] PDF Builder: PDF_Builder_Security_Manager loaded successfully');
        } else {
            error_log('[ERROR] PDF Builder: PDF_Builder_Security_Manager class not found after loading');
        }
    } else {
        error_log('[ERROR] PDF Builder: security-manager.php file not found at: ' . PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/security-manager.php');
    }
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/sanitizer.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/sanitizer.php';
        if (function_exists('pdf_builder_sanitize_input')) {
            error_log('[DEBUG] PDF Builder: Sanitizer functions loaded successfully');
        } else {
            error_log('[WARN] PDF Builder: Sanitizer functions not available after loading');
        }
    } else {
        error_log('[ERROR] PDF Builder: sanitizer.php file not found at: ' . PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/sanitizer.php');
    }

    // Charger les mappings centralisés
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/mappings.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/mappings.php';
        if (defined('PDF_BUILDER_MAPPINGS_LOADED')) {
            error_log('[DEBUG] PDF Builder: Mappings loaded successfully');
        } else {
            error_log('[WARN] PDF Builder: Mappings constant not defined after loading');
        }
    } else {
        error_log('[ERROR] PDF Builder: mappings.php file not found at: ' . PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/mappings.php');
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
            \wp_localize_script('jquery', 'pdf_builder_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_ajax')
            ));

            // Localisation pour settings-page.js qui utilise pdfBuilderAjax
            \wp_localize_script('jquery', 'pdfBuilderAjax', array(
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
        \wp_enqueue_script('react', false, [], false, true);
        \wp_enqueue_script('react-dom', false, ['react'], false, true);

        // Charger seulement le bundle sur la page de l'éditeur React
        if ($hook === 'pdf-builder_page_pdf-builder-react-editor' || (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-react-editor')) {
            error_log('[BOOTSTRAP] Loading React scripts for hook: ' . $hook . ', page: ' . (isset($_GET['page']) ? $_GET['page'] : 'none'));

            // Charger le bundle PDF Builder (optimisé avec code splitting)
            $bundle_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react-wrapper.min.js';
            \wp_enqueue_script(
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

            \wp_localize_script('pdf-builder-react-bundle', 'pdfBuilderAjax', $localize_data);
        }
    });
    // Les handlers AJAX sont maintenant chargés automatiquement par autoloader PSR-4

    $loaded = true;
}

// Fonction pour charger les nouvelles classes PDF_Builder
function pdf_builder_load_new_classes()
{
    static $new_classes_loaded = false;
    if ($new_classes_loaded) {
        return;
    }

    // Les classes PSR-4 sont maintenant chargées automatiquement par l'autoloader
    // Seuls les fichiers spéciaux qui ne suivent pas PSR-4 sont chargés manuellement

    // Charger l'API Preview (système spécial)
    require_once PDF_BUILDER_PLUGIN_DIR . 'preview-system/index.php';

    $new_classes_loaded = true;
}

// Fonction principale de chargement du bootstrap
function pdf_builder_load_bootstrap()
{
    error_log('[DEBUG] PDF Builder: pdf_builder_load_bootstrap() called');

    // Protection globale contre les chargements multiples
    static $bootstrap_loaded = false;
    if ($bootstrap_loaded || (defined('PDF_BUILDER_BOOTSTRAP_LOADED') && PDF_BUILDER_BOOTSTRAP_LOADED)) {
        error_log('[DEBUG] PDF Builder: Bootstrap already loaded, skipping');
        return;
    }
    $bootstrap_loaded = true;

    error_log('[DEBUG] PDF Builder: Starting bootstrap loading process');

    // Charger le core (toujours nécessaire)
    pdf_builder_load_core();

    // Charger les nouvelles classes (toujours nécessaire)
    pdf_builder_load_new_classes();

    // Charger les composants selon le contexte
    if (\is_admin() || \wp_doing_ajax()) {
        pdf_builder_load_admin_components();
    }

    if (!\is_admin()) {
        pdf_builder_load_frontend_components();
    }

    // Marquer comme chargé globalement
    define('PDF_BUILDER_BOOTSTRAP_LOADED', true);
}

// Fonction pour charger les composants admin
function pdf_builder_load_admin_components()
{
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
    // Maintenant chargé automatiquement par autoloader PSR-4

    // INITIALISER LE VALIDATEUR DE SÉCURITÉ APRÈS LE CHARGEMENT DE WORDPRESS
    if (class_exists('PDF_Builder\\Core\\PDF_Builder_Security_Validator')) {
        \PDF_Builder\Core\PDF_Builder_Security_Validator::get_instance()->init();
    }

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE CANVAS
    // Maintenant chargé automatiquement par autoloader PSR-4

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE SAUVEGARDE/RESTAURATION
    // Maintenant chargé automatiquement par autoloader PSR-4
    if (class_exists('PDF_Builder\\Managers\\PDF_Builder_Backup_Restore_Manager')) {
        \PDF_Builder\Managers\PDF_Builder_Backup_Restore_Manager::getInstance();
    }

    // ENREGISTRER LES HANDLERS AJAX POUR LE CANVAS
    if (class_exists('PDF_Builder\\Admin\\Canvas_AJAX_Handler')) {
        \PDF_Builder\Admin\Canvas_AJAX_Handler::register_hooks();
    }

    // CHARGER LE GESTIONNAIRE DE NOTIFICATIONS
    // Maintenant chargé automatiquement par autoloader PSR-4
    if (class_exists('PDF_Builder\\Core\\PDF_Builder_Notification_Manager')) {
        PDF_Builder_Notification_Manager::get_instance();
    }

    // CHARGER LE GESTIONNAIRE DE PRÉFÉRENCES DE L'ÉDITEUR PDF
    // Maintenant chargé automatiquement par autoloader PSR-4
    if (class_exists('PDF_Builder\\Core\\PDFEditorPreferences')) {
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

    // INITIALISER LE GESTIONNAIRE D'ONBOARDING
    add_action('init', function() {
        if (!class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
            $onboarding_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php';
            if (file_exists($onboarding_path)) {
                require_once $onboarding_path;
            }
        }

        if (class_exists('PDF_Builder_Onboarding_Manager_Alias')) {
            PDF_Builder_Onboarding_Manager_Alias::get_instance();
        } elseif (class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
            \PDF_Builder\Utilities\PDF_Builder_Onboarding_Manager::get_instance();
        }
    }, 5);

    // INITIALISER LE GESTIONNAIRE RGPD
    add_action('init', function() {
        if (!class_exists('PDF_Builder\\Utilities\\PDF_Builder_GDPR_Manager')) {
            $gdpr_path = PDF_BUILDER_PLUGIN_DIR . 'src/Utilities/PDF_Builder_GDPR_Manager.php';
            if (file_exists($gdpr_path)) {
                require_once $gdpr_path;
            }
        }
        if (class_exists('PDF_Builder\\Utilities\\PDF_Builder_GDPR_Manager')) {
            \PDF_Builder\Utilities\PDF_Builder_GDPR_Manager::get_instance();
        }
    }, 5);

    // CHARGER LES HOOKS AJAX ESSENTIELS
    pdf_builder_register_essential_ajax_hooks();

    // INSTANCIER L'API PREVIEW POUR LES ROUTES REST
    add_action('init', function() {
        if (class_exists('PDF_Builder\\Api\\PreviewImageAPI')) {
            new \PDF_Builder\Api\PreviewImageAPI();
        }
    });

    // Initialiser l'interface d'administration
    if (class_exists('PDF_Builder\\Core\\PdfBuilderCore')) {
        $core = \PDF_Builder\Core\PdfBuilderCore::getInstance();
        if (method_exists($core, 'init')) {
            $core->init();
        }

        // Forcer le chargement manuel de PdfBuilderAdminNew si nécessaire
        if (!class_exists('PdfBuilderAdminNew')) {
            $admin_file = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
            if (file_exists($admin_file)) {
                require_once $admin_file;
            }
        }

        if (class_exists('PdfBuilderAdminNew')) {
            try {
                $admin = PdfBuilderAdminNew::getInstance($core);
            } catch (Exception $e) {
                add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
            }
        } else {
            add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
        }
    } else {
        add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
    }

    // Toujours enregistrer le menu de base, indépendamment des autres conditions
    add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
}

/**
 * Fonction de fallback pour enregistrer un menu admin simple
 * Utilisée quand la classe PdfBuilderAdminNew ne peut pas être chargée
 */
function pdf_builder_register_admin_menu_simple() {
    // Menu principal avec icône distinctive
    \add_menu_page(
        __('PDF Builder Pro - Gestionnaire de PDF', 'pdf-builder-pro'),
        __('PDF Builder', 'pdf-builder-pro'),
        'manage_options',
        'pdf-builder-pro',
        'pdf_builder_simple_admin_page',
        'dashicons-pdf',
        25
    );

    // Page d'accueil (sous-menu principal)
    \add_submenu_page(
        'pdf-builder-pro',
        __('Accueil - PDF Builder Pro', 'pdf-builder-pro'),
        __('🏠 Accueil', 'pdf-builder-pro'),
        'manage_options',
        'pdf-builder-pro', // Même slug que le menu principal
        'pdf_builder_simple_admin_page'
    );

    // Paramètres et configuration
    \add_submenu_page(
        'pdf-builder-pro',
        __('Paramètres - PDF Builder Pro', 'pdf-builder-pro'),
        __('⚙️ Paramètres', 'pdf-builder-pro'),
        'manage_options',
        'pdf-builder-settings',
        'pdf_builder_simple_settings_page'
    );

    // Page de diagnostic des permissions
    \add_submenu_page(
        'pdf-builder-pro',
        __('Diagnostic - PDF Builder Pro', 'pdf-builder-pro'),
        __('🔍 Diagnostic', 'pdf-builder-pro'),
        'manage_options',
        'pdf-builder-diagnostic',
        'pdf_builder_diagnostic_page'
    );
}

/**
 * Page admin simple de fallback
 */
function pdf_builder_simple_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php \_e('PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
        <div class="notice notice-warning">
            <p><?php \_e('Le système d\'administration avancé n\'a pas pu être chargé. Utilisation du mode de secours.', 'pdf-builder-pro'); ?></p>
        </div>
        <p><?php \_e('Bienvenue dans PDF Builder Pro. Le système d\'administration complet n\'est pas disponible pour le moment.', 'pdf-builder-pro'); ?></p>
        <p><?php \_e('Vous pouvez accéder aux paramètres via le menu latéral.', 'pdf-builder-pro'); ?></p>
    </div>
    <?php
}

/**
 * Page de paramètres simple de fallback
 */
function pdf_builder_simple_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php \_e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
        <div class="notice notice-info">
            <p><?php \_e('Paramètres simplifiés - Le système avancé n\'est pas disponible.', 'pdf-builder-pro'); ?></p>
        </div>
        <form method="post" action="options.php">
            <?php \settings_fields('pdf_builder_settings'); ?>
            <?php \do_settings_sections('pdf_builder_settings'); ?>
            <?php \submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Page de diagnostic des permissions
 */
function pdf_builder_diagnostic_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.', 'pdf-builder-pro'));
    }

    echo '<div class="wrap">';
    echo '<h1>' . __('Diagnostic du système de permissions - PDF Builder Pro', 'pdf-builder-pro') . '</h1>';

    echo '<div class="notice notice-info">';
    echo '<p>' . __('Cette page diagnostique le système de permissions et de sécurité du plugin.', 'pdf-builder-pro') . '</p>';
    echo '</div>';

    echo '<h2>' . __('1. Test des fonctions WordPress', 'pdf-builder-pro') . '</h2>';
    echo '<ul>';

    $functions_to_test = [
        'wp_verify_nonce' => __('Vérification des nonces', 'pdf-builder-pro'),
        'current_user_can' => __('Vérification des permissions utilisateur', 'pdf-builder-pro'),
        'wp_send_json_error' => __('Envoi de réponses JSON d\'erreur', 'pdf-builder-pro'),
        'wp_send_json_success' => __('Envoi de réponses JSON de succès', 'pdf-builder-pro'),
        'wp_roles' => __('Gestion des rôles WordPress', 'pdf-builder-pro'),
        'wp_create_nonce' => __('Création de nonces', 'pdf-builder-pro'),
        'sanitize_text_field' => __('Nettoyage des champs texte', 'pdf-builder-pro'),
    ];

    foreach ($functions_to_test as $function => $description) {
        if (function_exists($function)) {
            echo '<li style="color: green;">✓ ' . $description . ' (' . $function . ')</li>';
        } else {
            echo '<li style="color: red;">✗ ' . $description . ' (' . $function . ') - ' . __('Fonction non disponible', 'pdf-builder-pro') . '</li>';
        }
    }

    if (defined('ARRAY_A')) {
        echo '<li style="color: green;">✓ ' . __('Constante ARRAY_A définie', 'pdf-builder-pro') . '</li>';
    } else {
        echo '<li style="color: red;">✗ ' . __('Constante ARRAY_A non définie', 'pdf-builder-pro') . '</li>';
    }

    echo '</ul>';

    echo '<h2>' . __('2. Test des classes de gestion AJAX', 'pdf-builder-pro') . '</h2>';
    echo '<ul>';

    $classes_to_test = [
        'PDF_Builder_Ajax_Base' => __('Classe de base pour les handlers AJAX', 'pdf-builder-pro'),
        'PDF_Builder_Settings_Ajax_Handler' => __('Handler AJAX des paramètres', 'pdf-builder-pro'),
        'PDF_Builder_Templates_Ajax' => __('Handler AJAX des templates', 'pdf-builder-pro'),
    ];

    foreach ($classes_to_test as $class => $description) {
        if (class_exists($class)) {
            echo '<li style="color: green;">✓ ' . $description . ' (' . $class . ')</li>';
        } else {
            echo '<li style="color: red;">✗ ' . $description . ' (' . $class . ') - ' . __('Classe non disponible', 'pdf-builder-pro') . '</li>';
        }
    }

    echo '</ul>';

    echo '<h2>' . __('3. Test des permissions utilisateur', 'pdf-builder-pro') . '</h2>';
    echo '<ul>';

    $current_user = wp_get_current_user();
    echo '<li>' . __('Utilisateur actuel:', 'pdf-builder-pro') . ' ' . $current_user->user_login . ' (ID: ' . $current_user->ID . ')</li>';

    $capabilities_to_test = [
        'manage_options' => __('Gérer les options', 'pdf-builder-pro'),
        'edit_posts' => __('Éditer les articles', 'pdf-builder-pro'),
        'upload_files' => __('Télécharger des fichiers', 'pdf-builder-pro'),
    ];

    foreach ($capabilities_to_test as $cap => $description) {
        if (current_user_can($cap)) {
            echo '<li style="color: green;">✓ ' . $description . ' (' . $cap . ')</li>';
        } else {
            echo '<li style="color: orange;">⚠ ' . $description . ' (' . $cap . ') - ' . __('Permission non accordée', 'pdf-builder-pro') . '</li>';
        }
    }

    echo '</ul>';

    echo '<h2>' . __('4. Test des nonces', 'pdf-builder-pro') . '</h2>';
    echo '<ul>';

    $test_nonce = wp_create_nonce('pdf_builder_test');
    echo '<li>' . __('Nonce de test créé:', 'pdf-builder-pro') . ' ' . substr($test_nonce, 0, 10) . '...</li>';

    if (wp_verify_nonce($test_nonce, 'pdf_builder_test')) {
        echo '<li style="color: green;">✓ ' . __('Vérification du nonce réussie', 'pdf-builder-pro') . '</li>';
    } else {
        echo '<li style="color: red;">✗ ' . __('Échec de la vérification du nonce', 'pdf-builder-pro') . '</li>';
    }

    echo '</ul>';

    echo '<h2>' . __('5. Test des namespaces', 'pdf-builder-pro') . '</h2>';
    echo '<ul>';

    $files_to_check = [
        'src/AJAX/Ajax_Handlers.php' => __('Namespace global', 'pdf-builder-pro'),
        'src/AJAX/PDF_Builder_Templates_Ajax.php' => 'PDF_Builder\\AJAX',
        'src/Managers/PDF_Builder_PDF_Generator.php' => 'PDF_Builder\\Managers',
        'src/Managers/PDF_Builder_WooCommerce_Integration.php' => 'PDF_Builder\\Managers'
    ];

    foreach ($files_to_check as $file => $expected_namespace) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $file;
        if (file_exists($file_path)) {
            $content = file_get_contents($file_path);
            $namespace_found = false;

            if ($expected_namespace === __('Namespace global', 'pdf-builder-pro')) {
                $namespace_found = strpos($content, 'namespace ') === false;
            } else {
                $namespace_found = strpos($content, 'namespace ' . $expected_namespace) !== false;
            }

            if ($namespace_found) {
                echo '<li style="color: green;">✓ ' . $file . ' : ' . $expected_namespace . '</li>';
            } else {
                echo '<li style="color: red;">✗ ' . $file . ' : ' . __('Namespace incorrect', 'pdf-builder-pro') . '</li>';
            }
        } else {
            echo '<li style="color: red;">✗ ' . $file . ' : ' . __('Fichier introuvable', 'pdf-builder-pro') . '</li>';
        }
    }

    echo '</ul>';

    echo '<div class="notice notice-success">';
    echo '<p><strong>' . __('Diagnostic terminé.', 'pdf-builder-pro') . '</strong> ' . __('Si vous voyez des éléments en rouge, il peut y avoir des problèmes de configuration.', 'pdf-builder-pro') . '</p>';
    echo '</div>';

    echo '</div>';
}

// Fonction pour charger les composants frontend
function pdf_builder_load_frontend_components()
{
    // Pour l'instant, pas de composants spécifiques au frontend
    // Ajouter ici les chargements spécifiques au frontend si nécessaire
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
    if (class_exists('PDF_Builder\\Managers\\PDF_Builder_Template_Manager')) {
        $template_manager = new \PDF_Builder\Managers\PDF_Builder_Template_Manager();
    }

}

// Fonction de chargement différé (maintenant vide car les hooks sont enregistrés au bootstrap)
function pdf_builder_load_core_when_needed()
{
    // Les hooks essentiels sont déjà enregistrés dans pdf_builder_load_bootstrap()
}

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
    if (\is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') === 0) {
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
            \add_option($option, $default_value);
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
    if (!isset($_GET['nonce']) || !\wp_verify_nonce($_GET['nonce'], 'pdf_builder_ajax')) {
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
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), \ARRAY_A);

    // Si le template n'est pas trouvé dans la table personnalisée, chercher dans wp_posts
    if (!$template) {
        $post = \get_post($template_id);
        if ($post && $post->post_type === 'pdf_template') {
            $template_data_raw = \get_post_meta($post->ID, '_pdf_template_data', true);
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
                $custom_logo_id = \get_theme_mod('custom_logo');
                if ($custom_logo_id) {
                    $logo_url = \wp_get_attachment_image_url($custom_logo_id, 'full');
                    if ($logo_url) {
                        $el['src'] = $logo_url;
                    }
                } else {
                    $site_logo_id = \get_option('site_logo');
                    if ($site_logo_id) {
                        $logo_url = \wp_get_attachment_image_url($site_logo_id, 'full');
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
if (\is_admin() && isset($_GET['page']) && $_GET['page'] === 'pdf-builder-settings') {
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
        $nonce_valid = \wp_verify_nonce($nonce_value, 'pdf_builder_ajax');
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

// Initialiser le bootstrap du plugin - maintenant géré dans pdf-builder-pro.php
// add_action('plugins_loaded', 'pdf_builder_load_bootstrap', 5); // Supprimé - géré dans pdf-builder-pro.php



