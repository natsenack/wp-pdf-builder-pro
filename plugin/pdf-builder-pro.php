<?php
/**
 * Plugin Name: PDF Builder Pro
 * Plugin URI: https://github.com/natsenack/wp-pdf-builder-pro
 * Description: Constructeur de PDF professionnel ultra-performant avec architecture modulaire avancée
 * Version: 1.1.0
 * Author: Natsenack
 * Author URI: https://github.com/natsenack
 * License: GPL v2 or later
 * Text Domain: pdf-builder-pro
 * Domain Path: /languages
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Définir les constantes du plugin
define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
// PDF_BUILDER_PLUGIN_URL sera défini dans constants.php avec plugins_url()
define('PDF_BUILDER_VERSION', '1.1.0');

// Désactiver les avertissements de dépréciation pour la compatibilité PHP 8.1+
error_reporting(error_reporting() & ~E_DEPRECATED);

// Hook d'activation
register_activation_hook(__FILE__, 'pdf_builder_activate');

// Hook de désactivation
register_deactivation_hook(__FILE__, 'pdf_builder_deactivate');

/**
 * Fonction d'activation
 */
function pdf_builder_activate() {
    // Créer une table de logs si nécessaire
    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_builder_logs';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            log_message text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    update_option('pdf_builder_version', '1.1.0');
}

/**
 * Fonction de désactivation
 */
function pdf_builder_deactivate() {
    delete_option('pdf_builder_activated');
}

// Charger le plugin de manière standard
add_action('plugins_loaded', 'pdf_builder_init');
add_action('plugins_loaded', 'pdf_builder_load_textdomain', 1);

/**
 * Charger le domaine de traduction
 */
function pdf_builder_load_textdomain() {
    load_plugin_textdomain('pdf-builder-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * Initialiser le plugin
 */
function pdf_builder_init() {
    // Vérifier que WordPress est prêt
    if (!function_exists('get_option') || !defined('ABSPATH')) {
        return;
    }

    // Ajouter les headers de cache pour les assets
    add_action('wp_enqueue_scripts', 'pdf_builder_add_asset_cache_headers', 1);
    add_action('admin_enqueue_scripts', 'pdf_builder_add_asset_cache_headers', 1);

    // Charger le bootstrap (version minimale - validée et fonctionnelle)
    $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap-minimal.php';
    if (file_exists($bootstrap_path)) {
        require_once $bootstrap_path;

        // Démarrer le plugin
        if (function_exists('pdf_builder_load_bootstrap')) {
            pdf_builder_load_bootstrap();
        }
    } else {
        // Log si bootstrap n'existe pas
        error_log('PDF Builder Pro: Bootstrap minimal introuvable');
    }

    // Tools for development/tests removed from production bootstrap

    // Charger le moniteur de performance
    $performance_monitor_path = plugin_dir_path(__FILE__) . 'src/Managers/PDF_Builder_Performance_Monitor.php';
    if (file_exists($performance_monitor_path)) {
        require_once $performance_monitor_path;
    }

    // Inclure l'interface d'administration du validateur (uniquement en admin)
    if (is_admin()) {
        require_once plugin_dir_path(__FILE__) . 'admin-validator.php';
    }

    /**
     * PDF Builder Pro - Interface Admin Simplifiée
     * Validateur intégré directement dans le plugin principal
     */
    if (is_admin()) {
        add_action('admin_menu', 'pdf_builder_add_simple_validator_page');
        add_action('admin_bar_menu', 'pdf_builder_add_admin_bar_link', 999);
    }

    function pdf_builder_add_simple_validator_page() {
        add_submenu_page(
            'tools.php',
            'PDF Builder Validator',
            '🧪 PDF Builder Validator',
            'manage_options',
            'pdf-builder-validator',
            'pdf_builder_simple_validator_page'
        );
    }

    function pdf_builder_add_admin_bar_link($wp_admin_bar) {
        if (current_user_can('manage_options')) {
            $wp_admin_bar->add_node([
                'id'    => 'pdf-builder-validator',
                'title' => '🧪 PDF Builder Validator',
                'href'  => admin_url('tools.php?page=pdf-builder-validator'),
                'meta'  => ['class' => 'pdf-builder-validator-link']
            ]);
        }
    }

    function pdf_builder_simple_validator_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions suffisantes.'));
        }

        echo '<div class="wrap">';
        echo '<h1>🧪 PDF Builder Pro - Validation Serveur</h1>';

        if (isset($_POST['run_validation'])) {
            echo '<div class="notice notice-info"><p>🔄 Validation en cours... Veuillez patienter.</p></div>';

            // Rediriger vers le validateur principal
            $validator_url = plugins_url('server-validator.php', __FILE__) . '?force_direct=1&run_validation=1';
            echo '<script>window.location.href = "' . esc_js($validator_url) . '";</script>';
            exit;
        }

        ?>
        <div class="card" style="max-width: 800px; margin: 20px 0;">
            <h2>Validation Complète du Plugin</h2>
            <p>Cette validation teste toutes les fonctionnalités de PDF Builder Pro sur ce serveur.</p>

            <div class="validation-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <h3>📋 Tests Effectués</h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li>Configuration WordPress et PHP</li>
                    <li>Activation et chargement du plugin</li>
                    <li>Classes et autoloader</li>
                    <li>Base de données et tables</li>
                    <li>Assets JavaScript/CSS</li>
                    <li>APIs et génération PDF</li>
                    <li>Intégration WooCommerce</li>
                    <li>Performance et sécurité</li>
                </ul>
            </div>

            <div class="validation-warning" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">
                <h3>⚠️ Important</h3>
                <p><strong>Temps d'exécution :</strong> ~30 secondes</p>
                <p><strong>Ne pas fermer cette page</strong> pendant la validation</p>
                <p><strong>Score cible :</strong> 100/100 pour production</p>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('pdf_builder_validation'); ?>
                <p>
                    <input type="submit" name="run_validation" class="button button-primary button-hero"
                           value="🚀 Lancer la Validation Complète"
                           onclick="this.value='🔄 Validation en cours...'; this.disabled=true; this.form.submit();">
                </p>
            </form>

            <div class="validation-links" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <h3>🔗 Liens Utiles</h3>
                <ul>
                    <li><a href="<?php echo plugins_url('server-validator.php', __FILE__); ?>?force_direct=1" target="_blank">Accès direct au validateur</a></li>
                    <li><a href="<?php echo plugins_url('SERVER-VALIDATION-GUIDE.md', __FILE__); ?>" target="_blank">Guide de validation complet</a></li>
                    <li><a href="https://github.com/natsenack/wp-pdf-builder-pro" target="_blank">Repository GitHub</a></li>
                </ul>
            </div>
        </div>

        <style>
            .card { background: white; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .validation-info { background: #f8f9fa !important; }
            .validation-warning { background: #fff3cd !important; border: 1px solid #ffeaa7 !important; }
            .button-hero { font-size: 16px !important; padding: 15px 30px !important; height: auto !important; }
            #wp-admin-bar-pdf-builder-validator a {
                background: linear-gradient(45deg, #3498db, #2980b9) !important;
                color: white !important;
            }
            #wp-admin-bar-pdf-builder-validator a:hover {
                background: linear-gradient(45deg, #2980b9, #21618c) !important;
            }
        </style>
        <?php

        echo '</div>';
    }

    /**
     * PDF Builder Pro - Menus Principaux du Plugin
     * Interface complète pour gérer templates, éditeur, paramètres
     */
    if (is_admin()) {
        add_action('admin_menu', 'pdf_builder_add_main_menu');
    }

    function pdf_builder_add_main_menu() {
        // Menu principal PDF Builder Pro
        add_menu_page(
            'PDF Builder Pro',
            '📄 PDF Builder',
            'manage_options',
            'pdf-builder-main',
            'pdf_builder_main_page',
            'dashicons-pdf',
            30
        );

        // Sous-menus
        add_submenu_page(
            'pdf-builder-main',
            'Tableau de Bord',
            '📊 Tableau de Bord',
            'manage_options',
            'pdf-builder-main',
            'pdf_builder_main_page'
        );

        add_submenu_page(
            'pdf-builder-main',
            'Templates PDF',
            '📝 Templates',
            'manage_options',
            'pdf-builder-templates',
            'pdf_builder_templates_page'
        );

        add_submenu_page(
            'pdf-builder-main',
            'Éditeur PDF',
            '🎨 Éditeur',
            'manage_options',
            'pdf-builder-editor',
            'pdf_builder_editor_page'
        );

        add_submenu_page(
            'pdf-builder-main',
            'Paramètres',
            '⚙️ Paramètres',
            'manage_options',
            'pdf-builder-settings',
            'pdf_builder_settings_page'
        );

        add_submenu_page(
            'pdf-builder-main',
            'Outils',
            '🔧 Outils',
            'manage_options',
            'pdf-builder-tools',
            'pdf_builder_tools_page'
        );
    }

    function pdf_builder_main_page() {
        ?>
        <div class="wrap">
            <h1>📄 PDF Builder Pro - Tableau de Bord</h1>

            <div class="welcome-panel">
                <div class="welcome-panel-content">
                    <h2>🎉 Bienvenue dans PDF Builder Pro !</h2>
                    <p class="about-description">Créez des PDF professionnels avec notre éditeur avancé et nos templates personnalisables.</p>
                    <div class="welcome-panel-column-container">
                        <div class="welcome-panel-column">
                            <h3>🚀 Démarrage Rapide</h3>
                            <ul>
                                <li><a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>">Créer votre premier template</a></li>
                                <li><a href="<?php echo admin_url('admin.php?page=pdf-builder-editor'); ?>">Utiliser l'éditeur visuel</a></li>
                                <li><a href="<?php echo admin_url('tools.php?page=pdf-builder-validator'); ?>">Valider l'installation</a></li>
                            </ul>
                        </div>
                        <div class="welcome-panel-column">
                            <h3>📊 Statistiques</h3>
                            <ul>
                                <li>Templates créés: <strong><?php echo pdf_builder_get_templates_count(); ?></strong></li>
                                <li>PDF générés: <strong><?php echo pdf_builder_get_pdfs_count(); ?></strong></li>
                                <li>Version: <strong>1.4.0</strong></li>
                            </ul>
                        </div>
                        <div class="welcome-panel-column welcome-panel-last">
                            <h3>🔗 Liens Utiles</h3>
                            <ul>
                                <li><a href="https://github.com/natsenack/wp-pdf-builder-pro" target="_blank">Documentation</a></li>
                                <li><a href="<?php echo admin_url('admin.php?page=diagnostic-pdf-builder'); ?>">Diagnostic système</a></li>
                                <li><a href="<?php echo admin_url('tools.php?page=pdf-builder-validator'); ?>">Validation serveur</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    function pdf_builder_templates_page() {
        ?>
        <div class="wrap">
            <h1>📝 Gestion des Templates PDF</h1>
            <p>Gérez vos templates PDF personnalisés.</p>

            <div class="card">
                <h2>Créer un Nouveau Template</h2>
                <p><a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&action=new'); ?>" class="button button-primary">🎨 Créer un Template</a></p>
            </div>

            <div class="card">
                <h2>Templates Existants</h2>
                <p>Liste de vos templates PDF...</p>
                <p><em>Fonctionnalité en développement</em></p>
            </div>
        </div>
        <?php
    }

    function pdf_builder_editor_page() {
        ?>
        <div class="wrap">
            <h1>🎨 Éditeur PDF Builder Pro</h1>
            <p>Éditeur visuel pour créer des templates PDF.</p>

            <div class="card">
                <h2>Éditeur Visuel</h2>
                <p>L'éditeur PDF avancé sera chargé ici...</p>
                <p><em>Fonctionnalité en développement - Utilisez l'éditeur JavaScript pour le moment</em></p>
            </div>
        </div>
        <?php
    }

    function pdf_builder_settings_page() {
        ?>
        <div class="wrap">
            <h1>⚙️ Paramètres PDF Builder Pro</h1>
            <p>Configurez les paramètres de votre plugin PDF.</p>

            <form method="post" action="options.php">
                <?php
                settings_fields('pdf_builder_settings');
                do_settings_sections('pdf_builder_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    function pdf_builder_tools_page() {
        ?>
        <div class="wrap">
            <h1>🔧 Outils PDF Builder Pro</h1>
            <p>Outils et utilitaires pour PDF Builder.</p>

            <div class="card">
                <h2>Outils Disponibles</h2>
                <ul>
                    <li><a href="<?php echo admin_url('tools.php?page=pdf-builder-validator'); ?>">🧪 Validateur Serveur</a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=diagnostic-pdf-builder'); ?>">🔍 Diagnostic Système</a></li>
                    <li><a href="<?php echo admin_url('tools.php?page=debug-pdf-builder'); ?>">🐛 Debug Menu</a></li>
                </ul>
            </div>
        </div>
        <?php
    }

    // Fonctions utilitaires
    function pdf_builder_get_templates_count() {
        // Simulation - à remplacer par requête réelle
        return 0;
    }

    function pdf_builder_get_pdfs_count() {
        // Simulation - à remplacer par requête réelle
        return 0;
    }
}

/**
 * Ajouter les headers de cache pour les assets du plugin
 */
function pdf_builder_add_asset_cache_headers() {
    // Headers de cache pour les assets du plugin (1 semaine)
    $cache_time = 604800; // 7 jours en secondes

    // Pour les assets JavaScript
    if (isset($_SERVER['REQUEST_URI']) &&
        (strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/wp-pdf-builder-pro/assets/js/') !== false ||
         strpos($_SERVER['REQUEST_URI'], '/wp-content/plugins/wp-pdf-builder-pro/assets/css/') !== false)) {

        // Headers de cache
        header('Cache-Control: public, max-age=' . $cache_time);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
        header('ETag: "' . md5($_SERVER['REQUEST_URI'] . filemtime($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) . '"');

        // Compression si supportée
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false &&
            strpos($_SERVER['REQUEST_URI'], '.gz') !== false) {
            header('Content-Encoding: gzip');
        }
    }
}

// Gérer les téléchargements PDF en frontend
add_action('init', 'pdf_builder_handle_pdf_downloads');

/**
 * Gérer les téléchargements PDF
 */
function pdf_builder_handle_pdf_downloads() {
    if (isset($_GET['pdf_download'])) {
        // Charger le bootstrap pour gérer le téléchargement
        $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
        if (file_exists($bootstrap_path)) {
            require_once $bootstrap_path;
            if (function_exists('pdf_builder_load_bootstrap')) {
                pdf_builder_load_bootstrap();
            }
        }
    }
}