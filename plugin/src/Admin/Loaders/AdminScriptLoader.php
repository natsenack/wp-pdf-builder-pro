<?php

/**
 * PDF Builder Pro - Admin Script Loader
 * Responsable du chargement des scripts et styles d'administration
 */

namespace PDF_Builder\Admin\Loaders;

// Ensure constants are loaded
if (!defined('PDF_BUILDER_ASSETS_DIR')) {
    $constants_file = dirname(dirname(dirname(dirname(__FILE__)))) . '/src/Core/core/constants.php';
    if (file_exists($constants_file)) {
        require_once $constants_file;
    }
}

// Import the logger class

/**
 * Classe responsable du chargement des scripts et styles admin
 */
class AdminScriptLoader
{
    /**
     * Instance de la classe principale
     */
    private $admin;

    /**
     * Constructeur
     */
    public function __construct($admin)
    {
        $this->admin = $admin;

        // Logger initialization removed - using error_log instead

        // Enregistrer le hook pour charger les scripts admin
        \add_action('admin_enqueue_scripts', [$this, 'loadAdminScripts'], 20);
    }

    /**
     * Charge les scripts et styles d'administration
     */
    public function loadAdminScripts($hook = null)
    {
        error_log('[WP AdminScriptLoader] loadAdminScripts called with hook: ' . ($hook ?: 'null') . ', URL: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'no url'));

        // Ajouter un filtre pour corriger les templates Elementor qui sont chargés comme des scripts JavaScript
        // Appliquer toujours, pas seulement sur les pages PDF Builder
        \add_filter('script_loader_tag', [$this, 'fixElementorTemplates'], 10, 3);

        // Pour la page des paramètres PDF Builder, utiliser le buffering de sortie pour filtrer les scripts inline
        if (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-settings') {
            \add_action('init', [$this, 'startOutputBuffering'], 1);
            \add_action('shutdown', [$this, 'endOutputBuffering'], 999);
        }

        // Styles CSS de base
        \wp_enqueue_style('pdf-builder-admin', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css', [], PDF_BUILDER_PRO_VERSION);
        
        // Charger les styles spécifiques à chaque page
        $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        
        // Dashboard styles (page d'accueil = 'pdf-builder-pro')
        if ($current_page === 'pdf-builder-pro') {
            \wp_enqueue_style(
                'pdf-builder-dashboard',
                PDF_BUILDER_PRO_ASSETS_URL . 'css/dashboard-css.min.css',
                [],
                PDF_BUILDER_PRO_VERSION . '-' . time()
            );
        }
        
        // Templates page styles
        if ($current_page === 'pdf-builder-templates') {
            \wp_enqueue_style(
                'pdf-builder-templates-page',
                PDF_BUILDER_PRO_ASSETS_URL . 'css/templates-page-css.min.css',
                [],
                PDF_BUILDER_PRO_VERSION
            );
        }
        
        // Charger le CSS unifié qui contient les styles pour le bouton flottant
        $unified_css = PDF_BUILDER_PRO_ASSETS_PATH . 'css/pdf-builder-unified.css';
        if (file_exists($unified_css)) {
            \wp_enqueue_style('pdf-builder-unified', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-unified.css', [], PDF_BUILDER_PRO_VERSION);
        }

        // Charger SETTINGS CSS et JS pour les pages settings
        // Simplifier la condition - charger pour toutes les pages admin contenant pdf-builder
        $current_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($current_url, 'pdf-builder') !== false) {
            error_log('[WP AdminScriptLoader] Loading settings scripts - URL contains pdf-builder: ' . $current_url);

            // Enqueue WordPress core scripts needed for the plugin
            wp_enqueue_script('wp-date'); // Provides moment.js
            wp_enqueue_script('wp-element'); // Provides React
            wp_enqueue_script('wp-components'); // Provides React components
            wp_enqueue_script('wp-api'); // Provides WordPress API
            wp_enqueue_script('wp-data'); // Provides Redux store
            wp_enqueue_script('wp-hooks'); // Provides hooks
            wp_enqueue_script('wp-i18n'); // Provides internationalization
            wp_enqueue_script('wp-url'); // Provides URL utilities
            wp_enqueue_script('wp-keycodes'); // Provides keycodes
            wp_enqueue_script('wp-compose'); // Provides compose utilities
            wp_enqueue_script('wp-html-entities'); // Provides HTML entities
            wp_enqueue_script('wp-primitives'); // Provides primitives
            wp_enqueue_script('wp-warning'); // Provides warning system
            wp_enqueue_script('wp-token-list'); // Provides token list
            wp_enqueue_script('wp-core-data'); // Provides core data
            wp_enqueue_script('wp-core-commands'); // Provides core commands
            wp_enqueue_script('wp-block-editor'); // Provides block editor
            wp_enqueue_script('wp-rich-text'); // Provides rich text
            wp_enqueue_script('wp-commands'); // Provides commands
            wp_enqueue_script('wp-blob'); // Provides blob utilities
            wp_enqueue_script('wp-shortcode'); // Provides shortcode
            wp_enqueue_script('wp-media-utils'); // Provides media utilities
            wp_enqueue_script('wp-notices'); // Provides notices
            wp_enqueue_script('wp-preferences'); // Provides preferences
            wp_enqueue_script('wp-preferences-persistence'); // Provides preferences persistence
            wp_enqueue_script('wp-editor'); // Provides editor
            wp_enqueue_script('wp-plugins'); // Provides plugins
            wp_enqueue_script('wp-edit-post'); // Provides edit post
            wp_enqueue_script('wp-viewport'); // Provides viewport
            wp_enqueue_script('wp-interface'); // Provides interface
            wp_enqueue_script('wp-redux-routine'); // Provides redux routine
            wp_enqueue_script('wp-priority-queue'); // Provides priority queue
            wp_enqueue_script('wp-server-side-render'); // Provides server side render
            wp_enqueue_script('wp-autop'); // Provides autop
            wp_enqueue_script('wp-wordcount'); // Provides wordcount
            wp_enqueue_script('wp-annotations'); // Provides annotations
            wp_enqueue_script('wp-dom'); // Provides DOM utilities
            wp_enqueue_script('wp-a11y'); // Provides accessibility
            wp_enqueue_script('wp-dom-ready'); // Provides DOM ready
            wp_enqueue_script('wp-polyfill'); // Provides polyfills

            // Charger les utilitaires PDF Builder en premier (PerformanceMetrics, LocalCache, etc.) - seulement si le fichier existe
            $utils_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/pdf-builder-utils.js';
            if (file_exists($utils_js)) {
                \wp_enqueue_script(
                    'pdf-builder-utils',
                    PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-utils.js',
                    ['wp-element', 'wp-components', 'wp-data', 'wp-hooks'],
                    PDF_BUILDER_PRO_VERSION,
                    true
                );
            }

            \wp_enqueue_style(
                'pdf-builder-settings-tabs',
                PDF_BUILDER_PRO_ASSETS_URL . 'css/settings-tabs.css',
                [],
                PDF_BUILDER_PRO_VERSION
            );

            // Charger settings-tabs.min.js pour TOUTES les pages PDF Builder
            if (!wp_script_is('pdf-builder-settings-tabs', 'enqueued')) {
                \wp_enqueue_script(
                    'pdf-builder-settings-tabs',
                    PDF_BUILDER_PRO_ASSETS_URL . 'js/settings-tabs.min.js',
                    ['jquery', 'wp-element', 'wp-components', 'wp-data', 'wp-hooks'],
                    PDF_BUILDER_PRO_VERSION,
                    true
                );
                error_log('[WP AdminScriptLoader] Enqueued pdf-builder-settings-tabs script - URL: ' . PDF_BUILDER_PRO_ASSETS_URL . 'js/settings-tabs.min.js');
                error_log('[WP AdminScriptLoader] Current REQUEST_URI: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set'));
            } else {
                error_log('[WP AdminScriptLoader] pdf-builder-settings-tabs already enqueued');
            }

            // Charger settings-main.min.js pour les fonctions de licence
            if (!wp_script_is('pdf-builder-settings-main', 'enqueued')) {
                \wp_enqueue_script(
                    'pdf-builder-settings-main',
                    PDF_BUILDER_PRO_ASSETS_URL . 'js/settings-main.min.js',
                    ['jquery', 'wp-element', 'wp-components', 'wp-data', 'wp-hooks'],
                    PDF_BUILDER_PRO_VERSION,
                    true
                );
                error_log('[WP AdminScriptLoader] Enqueued pdf-builder-settings-main script');
            }

            // Charger le système de notifications pour les pages de paramètres - seulement si le fichier existe
            $notifications_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/notifications.min.js';
            if (file_exists($notifications_js)) {
                \wp_enqueue_script(
                    'pdf-builder-notifications',
                    PDF_BUILDER_PRO_ASSETS_URL . 'js/notifications.min.js',
                    ['jquery', 'wp-element', 'wp-components', 'wp-notices'],
                    PDF_BUILDER_PRO_VERSION,
                    true
                );
            }

            // Charger le CSS des notifications - seulement si le fichier existe
            $notifications_css = PDF_BUILDER_PRO_ASSETS_PATH . 'css/notifications.min.css';
            if (file_exists($notifications_css)) {
                \wp_enqueue_style(
                    'pdf-builder-notifications',
                    PDF_BUILDER_PRO_ASSETS_URL . 'css/notifications.min.css',
                    [],
                    PDF_BUILDER_PRO_VERSION
                );
            }

            // Charger le script du bouton flottant de sauvegarde - seulement pour la page des paramètres
            if (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-settings') {
                $floating_save_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/floating-save.min.js';
                if (file_exists($floating_save_js)) {
                    \wp_enqueue_script(
                        'pdf-builder-floating-save',
                        PDF_BUILDER_PRO_ASSETS_URL . 'js/floating-save.min.js',
                        ['jquery', 'wp-element', 'wp-components'],
                        PDF_BUILDER_PRO_VERSION,
                        true
                    );
                } else {
                    // Fallback: injecter le script inline si le fichier n'existe pas
                    \wp_add_inline_script('jquery', "
                    (function($) {
                        $(document).ready(function() {
                            var floatingBtn = $('#pdf-builder-save-floating-btn');
                            var form = $('form[action=\"options.php\"]');
                            if (floatingBtn.length > 0 && form.length > 0) {
                                floatingBtn.on('click', function(e) {
                                    // Ne pas preventDefault - laisser le bouton submit fonctionner normalement
                                    $(this).addClass('loading');
                                    // La soumission se fera automatiquement via le type='submit'
                                });
                            }
                        });
                    })(jQuery);
                    ", 'before');
                }
                
                // Le fallback inline suffit maintenant
            }

            // Charger les styles canvas-modal pour les pages templates et settings
            if (strpos($hook, 'templates') !== false || strpos($hook, 'settings') !== false) {
                \wp_enqueue_style(
                    'pdf-builder-react',
                    PDF_BUILDER_PLUGIN_URL . 'assets/css/pdf-builder-react.min.css',
                    [],
                    PDF_BUILDER_PRO_VERSION
                );
                
                // Charger le CSS de l'onglet Système (si on est sur la page settings)
                if (strpos($hook, 'settings') !== false) {
                    \wp_enqueue_style(
                        'pdf-builder-settings-systeme',
                        PDF_BUILDER_PLUGIN_URL . 'assets/css/settings-systeme-css.min.css',
                        [],
                        PDF_BUILDER_PRO_VERSION
                    );
                }
            }

            // Définir les paramètres de debug JavaScript UNIQUEMENT pour les notifications
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $debug_settings = [
                'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
                'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
                'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
                'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
            ];
            \wp_add_inline_script('pdf-builder-notifications', 'window.pdfBuilderDebugSettings = ' . \wp_json_encode($debug_settings) . ';', 'before');

            // Localize notifications data pour les pages de paramètres
            \wp_localize_script('pdf-builder-notifications', 'pdfBuilderNotifications', [
                'ajax_url' => \admin_url('admin-ajax.php'),
                'nonce' => \wp_create_nonce('pdf_builder_notifications'),
                'settings' => [
                    'enabled' => true,
                    'position' => 'top-right',
                    'duration' => 5000,
                    'max_notifications' => 5,
                    'animation' => 'slide',
                    'theme' => 'modern'
                ],
                'strings' => [
                    'success' => \__('Succès', 'pdf-builder-pro'),
                    'error' => \__('Erreur', 'pdf-builder-pro'),
                    'warning' => \__('Avertissement', 'pdf-builder-pro'),
                    'info' => \__('Information', 'pdf-builder-pro'),
                    'close' => \__('Fermer', 'pdf-builder-pro')
                ]
            ]);

            // Localiser les variables AJAX
            \wp_localize_script('pdf-builder-settings-tabs', 'pdfBuilderAjax', [
                'ajaxurl' => \admin_url('admin-ajax.php'),
                'nonce' => \wp_create_nonce('pdf_builder_ajax')
            ]);

            // Définir les paramètres de debug JavaScript UNIQUEMENT pour settings-tabs
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $debug_settings = [
                'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
                'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
                'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
                'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
            ];
            wp_add_inline_script('pdf-builder-settings-tabs', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');
        }

        // Version du cache bust
        $version_param = PDF_BUILDER_PRO_VERSION . '-' . time();

        // Scripts de l'API Preview 1.4 - seulement si les fichiers existent
        $preview_client_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/pdf-preview-api-client.min.js';
        if (file_exists($preview_client_js)) {
            // AJOUTER UN SCRIPT DE TEST TRÈS SIMPLE

            
            // Define variables BEFORE loading the script
            $preview_data = [
                'ajaxurl' => \admin_url('admin-ajax.php'),
                'nonce' => \wp_create_nonce('pdf_builder_order_actions'),
                'version' => PDF_BUILDER_PRO_VERSION,
                'timestamp' => time(),
                'strings' => [
                    'error_loading_preview' => \__('Erreur lors du chargement de l\'aperçu', 'pdf-builder-pro'),
                    'generating_pdf' => \__('Génération du PDF en cours...', 'pdf-builder-pro'),
                ]
            ];
        \wp_add_inline_script('jquery', 'window.pdfBuilderData = ' . wp_json_encode($preview_data) . '; window.pdfBuilderNonce = "' . \wp_create_nonce('pdf_builder_ajax') . '";', 'after');
            
            \wp_enqueue_script('pdf-preview-api-client', PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-api-client.min.js', ['jquery', 'wp-element', 'wp-api'], $version_param, true);
            
            // Debug: Add script to check if variables are defined after the main script
            wp_add_inline_script('pdf-preview-api-client', '
                console.log("[DEBUG] PDF Builder variables check after script load:");
                console.log("[DEBUG] window.pdfBuilderData:", typeof window.pdfBuilderData, window.pdfBuilderData);
                console.log("[DEBUG] window.pdfBuilderNonce:", typeof window.pdfBuilderNonce, window.pdfBuilderNonce);
            ');
            
            // Debug: Add script to check if variables are defined
            wp_add_inline_script('pdf-preview-api-client', '
                console.log("[DEBUG] PDF Builder variables check:");
                console.log("[DEBUG] window.pdfBuilderData:", typeof window.pdfBuilderData, window.pdfBuilderData);
                console.log("[DEBUG] window.pdfBuilderNonce:", typeof window.pdfBuilderNonce, window.pdfBuilderNonce);
                console.log("[DEBUG] pdfBuilderData (global):", typeof pdfBuilderData, pdfBuilderData);
            ');
            
            $preview_integration_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/pdf-preview-integration.min.js';
            if (file_exists($preview_integration_js)) {
                \wp_enqueue_script('pdf-preview-integration', PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-integration.min.js', ['pdf-preview-api-client', 'wp-element', 'wp-api'], $version_param, true);

                // Localize ajaxurl for integration script
                \wp_localize_script('pdf-preview-integration', 'pdfBuilderAjax', [
                    'ajaxurl' => \admin_url('admin-ajax.php'),
                    'nonce' => \wp_create_nonce('pdf_builder_order_actions')
                ]);
            }
        }

        // Outils développeur asynchrones - seulement si le fichier existe
        $developer_tools_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/developer-tools.js';
        if (file_exists($developer_tools_js)) {
            \wp_enqueue_script('pdf-builder-developer-tools', PDF_BUILDER_PRO_ASSETS_URL . 'js/developer-tools.js', ['jquery', 'pdf-preview-api-client', 'wp-element', 'wp-api'], $version_param, true);
        }
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-developer-tools: ' . PDF_BUILDER_PRO_ASSETS_URL . 'js/developer-tools.js');
        // error_log('[WP AdminScriptLoader] Current page: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set'));
        // error_log('[WP AdminScriptLoader] Current hook: ' . $hook);

        // Scripts pour l'éditeur React
        if (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-react-editor') {
            error_log('[WP AdminScriptLoader] Loading React editor scripts for page: ' . $_GET['page']);
            $this->loadReactEditorScripts($hook);

            // Add footer DOM check only once
            \add_action('admin_footer-pdf-builder_page_pdf-builder-react-editor', function() {
                ?>
                <script>
                let scripts = document.querySelectorAll('script[src*="pdf-builder-react"]');
                scripts.forEach((script, index) => {
                });
                let initScript = document.querySelector('script[src*="pdf-builder-react-init.min.js"]');
                let mainScript = document.querySelector('script[src*="pdf-builder-react.min.js"]');
                let wrapperScript = document.querySelector('script[src*="pdf-builder-react-wrapper.min.js"]');

                // Manual init if not done
                setTimeout(function() {
                    const root = document.getElementById('pdf-builder-react-root');
                    if (root && root.children.length === 0) {
                        if (window.pdfBuilderReact && window.pdfBuilderReact.initPDFBuilderReact) {
                            window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');
                        }
                    }
                }, 1000);
                </script>
                <?php
            });
        } else {
            error_log('[WP AdminScriptLoader] NOT loading React editor scripts, page is: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set') . ', hook: ' . $hook);
        }

        // Charger aussi les scripts React si on est sur une page qui contient "react-editor" dans l'URL
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'pdf-builder-react-editor') !== false) {
            error_log('[WP AdminScriptLoader] Loading React editor scripts from REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
            $this->loadReactEditorScripts($hook);
        }
    }

    /**
     * Charge les scripts spécifiques à l'éditeur React
     */
    private function loadReactEditorScripts($hook = null)
    {
        static $scripts_loaded = false;
        if ($scripts_loaded) {
            error_log('[WP AdminScriptLoader] React editor scripts already loaded, skipping');
            return;
        }
        $scripts_loaded = true;
        
        error_log('[WP AdminScriptLoader] loadReactEditorScripts STARTED at ' . date('Y-m-d H:i:s') . ' for page: ' . (isset($_GET['page']) ? $_GET['page'] : 'unknown') . ', hook: ' . $hook);

        // VÉRIFICATION DES FICHIERS AVANT CHARGEMENT
        $throttle_file = PDF_BUILDER_PRO_ASSETS_PATH . 'js/ajax-throttle.min.js';
        $react_vendors_file = PDF_BUILDER_PRO_ASSETS_PATH . 'js/react-vendor.min.js';
        $runtime_file = PDF_BUILDER_PRO_ASSETS_PATH . 'js/runtime.min.js';
        $react_main_file = PDF_BUILDER_PRO_ASSETS_PATH . 'js/pdf-builder-react.min.js';
        $react_init_file = PDF_BUILDER_PRO_ASSETS_PATH . 'js/pdf-builder-react-init.min.js';

        error_log('[WP AdminScriptLoader] REQUEST_URI: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set'));
        error_log('[WP AdminScriptLoader] Current URL: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set'));

        // CHARGER LA MÉDIATHÈQUE WORDPRESS POUR LES COMPOSANTS REACT
        \wp_enqueue_media();
        error_log('[WP AdminScriptLoader] wp_enqueue_media() called for React editor');

        $cache_bust = microtime(true) . '-' . rand(1000, 9999);
        $version_param = PDF_BUILDER_PRO_VERSION . '-' . $cache_bust;

        error_log('[WP AdminScriptLoader] Cache bust: ' . $cache_bust);
        error_log('[WP AdminScriptLoader] Version param: ' . $version_param);

        // AJAX throttle manager
        $throttle_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/ajax-throttle.min.js';
        \wp_enqueue_script('pdf-builder-ajax-throttle', $throttle_url, [], $cache_bust, true);
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-ajax-throttle: ' . $throttle_url . ' with cache_bust: ' . $cache_bust);

        // Notifications system
        $notifications_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/notifications.min.js';
        \wp_enqueue_script('pdf-builder-notifications', $notifications_url, ['jquery', 'wp-element', 'wp-components', 'wp-notices'], $cache_bust, true);
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-notifications: ' . $notifications_url . ' with cache_bust: ' . $cache_bust);

        // Notifications CSS
        $notifications_css_url = PDF_BUILDER_PRO_ASSETS_URL . 'css/notifications.min.css';
        \wp_enqueue_style('pdf-builder-notifications', $notifications_css_url, [], $cache_bust);
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-notifications CSS: ' . $notifications_css_url . ' with cache_bust: ' . $cache_bust);

        // Localize notifications data
        \wp_localize_script('pdf-builder-notifications', 'pdfBuilderNotifications', [
            'ajax_url' => \admin_url('admin-ajax.php'),
            'nonce' => \wp_create_nonce('pdf_builder_notifications'),
            'settings' => [
                'enabled' => true,
                'position' => 'top-right',
                'duration' => 5000,
                'max_notifications' => 5,
                'animation' => 'slide',
                'theme' => 'modern'
            ],
            'strings' => [
                'success' => \__('Succès', 'pdf-builder-pro'),
                'error' => \__('Erreur', 'pdf-builder-pro'),
                'warning' => \__('Avertissement', 'pdf-builder-pro'),
                'info' => \__('Information', 'pdf-builder-pro'),
                'close' => \__('Fermer', 'pdf-builder-pro')
            ]
        ]);

        // Définir les paramètres de debug JavaScript UNIQUEMENT pour l'éditeur React
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $debug_settings = [
            'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
            'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
            'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
            'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
        ];
        wp_add_inline_script('pdf-builder-notifications', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');

        // Wrapper script
        $wrap_helper_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-wrap.min.js';
        \wp_enqueue_script('pdf-builder-wrap', $wrap_helper_url, ['pdf-builder-ajax-throttle', 'pdf-builder-notifications', 'wp-element', 'wp-components'], $cache_bust, true);
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-wrap: ' . $wrap_helper_url);

        // Enqueue WordPress core scripts for React editor
        wp_enqueue_script('wp-date'); // Provides moment.js
        wp_enqueue_script('wp-element'); // Provides React
        wp_enqueue_script('wp-components'); // Provides React components
        wp_enqueue_script('wp-api'); // Provides WordPress API
        wp_enqueue_script('wp-data'); // Provides Redux store
        wp_enqueue_script('wp-hooks'); // Provides hooks
        wp_enqueue_script('wp-i18n'); // Provides internationalization
        wp_enqueue_script('wp-url'); // Provides URL utilities
        wp_enqueue_script('wp-keycodes'); // Provides keycodes
        wp_enqueue_script('wp-compose'); // Provides compose utilities
        wp_enqueue_script('wp-html-entities'); // Provides HTML entities
        wp_enqueue_script('wp-primitives'); // Provides primitives
        wp_enqueue_script('wp-warning'); // Provides warning system
        wp_enqueue_script('wp-token-list'); // Provides token list
        wp_enqueue_script('wp-core-data'); // Provides core data
        wp_enqueue_script('wp-core-commands'); // Provides core commands
        wp_enqueue_script('wp-block-editor'); // Provides block editor
        wp_enqueue_script('wp-rich-text'); // Provides rich text
        wp_enqueue_script('wp-commands'); // Provides commands
        wp_enqueue_script('wp-blob'); // Provides blob utilities
        wp_enqueue_script('wp-shortcode'); // Provides shortcode
        wp_enqueue_script('wp-media-utils'); // Provides media utilities
        wp_enqueue_script('wp-notices'); // Provides notices
        wp_enqueue_script('wp-preferences'); // Provides preferences
        wp_enqueue_script('wp-preferences-persistence'); // Provides preferences persistence
        wp_enqueue_script('wp-editor'); // Provides editor
        wp_enqueue_script('wp-plugins'); // Provides plugins
        wp_enqueue_script('wp-edit-post'); // Provides edit post
        wp_enqueue_script('wp-viewport'); // Provides viewport
        wp_enqueue_script('wp-interface'); // Provides interface
        wp_enqueue_script('wp-redux-routine'); // Provides redux routine
        wp_enqueue_script('wp-priority-queue'); // Provides priority queue
        wp_enqueue_script('wp-server-side-render'); // Provides server side render
        wp_enqueue_script('wp-autop'); // Provides autop
        wp_enqueue_script('wp-wordcount'); // Provides wordcount
        wp_enqueue_script('wp-annotations'); // Provides annotations
        wp_enqueue_script('wp-dom'); // Provides DOM utilities
        wp_enqueue_script('wp-a11y'); // Provides accessibility
        wp_enqueue_script('wp-dom-ready'); // Provides DOM ready
        wp_enqueue_script('wp-polyfill'); // Provides polyfills

        // Enqueue React vendor bundle (contains React and ReactDOM)
        $react_vendor_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/react-vendor.min.js';
        if (!wp_script_is('pdf-builder-react-vendor', 'enqueued')) {
            \wp_enqueue_script('pdf-builder-react-vendor', $react_vendor_url, [], $version_param, false);
            error_log('[WP AdminScriptLoader] VENDOR ENQUEUED: ' . $react_vendor_url);
        }
        
        // Main React app bundle  
        $react_main_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react.min.js';
        if (!wp_script_is('pdf-builder-react-main', 'enqueued')) {
            \wp_enqueue_script('pdf-builder-react-main', $react_main_url, ['wp-element', 'wp-components', 'wp-data', 'wp-hooks', 'wp-api', 'media-views'], $version_param, true);
            \wp_script_add_data('pdf-builder-react-main', 'type', 'text/javascript');
            error_log('[WP AdminScriptLoader] MAIN ENQUEUED: ' . $react_main_url);
        }
        
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-vendor and pdf-builder-react-main with dependencies');
        
        // Localize script data BEFORE enqueuing
        $localize_data = [
            'ajaxUrl' => \admin_url('admin-ajax.php'),
            'nonce' => \wp_create_nonce('pdf_builder_ajax'),
            'version' => PDF_BUILDER_PRO_VERSION,
            'templateId' => isset($_GET['template_id']) ? \intval($_GET['template_id']) : 0,
            'isEdit' => isset($_GET['template_id']) && \intval($_GET['template_id']) > 0,
        ];

        // Ajouter les informations de licence

        if (class_exists('\PDF_Builder\Managers\PDF_Builder_License_Manager')) {
            $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            $license_data = [
                'isPremium' => $license_manager->isPremium(),
                'status' => pdf_builder_get_option('pdf_builder_license_status', 'free'),
                'hasTestMode' => !empty(pdf_builder_get_option('pdf_builder_license_test_mode_enabled', '0')),
            ];
            
            // DEBUG: Log license data being sent to JS

            $localize_data['license'] = $license_data;
        }

        // Ajouter les informations de l'entreprise depuis les paramètres du plugin ET WooCommerce
        $company_data = [
            'name' => pdf_builder_get_option('pdf_builder_company_name', ''),
            'address' => pdf_builder_get_option('pdf_builder_company_address', ''),
            'phone' => pdf_builder_get_option('pdf_builder_company_phone_manual', ''),
            'email' => pdf_builder_get_option('pdf_builder_company_email', ''),
            'siret' => pdf_builder_get_option('pdf_builder_company_siret', ''),
            'vat' => pdf_builder_get_option('pdf_builder_company_vat', ''),
            'rcs' => pdf_builder_get_option('pdf_builder_company_rcs', ''),
            'capital' => pdf_builder_get_option('pdf_builder_company_capital', ''),
        ];
        
        // Remplacer par les données WooCommerce si elles existent et que les paramètres du plugin sont vides
        if (empty($company_data['name'])) {
            $company_data['name'] = \get_bloginfo('name', '');
        }
        if (empty($company_data['address'])) {
            // Récupérer l'adresse complète depuis WooCommerce
            $store_address = \get_option('woocommerce_store_address', '');
            $store_address_2 = \get_option('woocommerce_store_address_2', '');
            $store_city = \get_option('woocommerce_store_city', '');
            $store_postcode = \get_option('woocommerce_store_postcode', '');
            $store_country = \get_option('woocommerce_store_country', '');
            
            $full_address = array_filter([$store_address, $store_address_2, $store_postcode . ' ' . $store_city, $store_country]);
            $company_data['address'] = implode(', ', $full_address);
        }
        if (empty($company_data['email'])) {
            $company_data['email'] = \get_option('admin_email', '');
        }
        
        // Si après tous les fallbacks les valeurs sont encore vides, indiquer "Non indiqué"
        if (empty($company_data['name'])) {
            $company_data['name'] = 'Non indiqué';
        }
        if (empty($company_data['address'])) {
            $company_data['address'] = 'Non indiqué';
        }
        if (empty($company_data['phone'])) {
            $company_data['phone'] = 'Non indiqué';
        }
        if (empty($company_data['email'])) {
            $company_data['email'] = 'Non indiqué';
        }
        if (empty($company_data['siret'])) {
            $company_data['siret'] = 'Non indiqué';
        }
        if (empty($company_data['vat'])) {
            $company_data['vat'] = 'Non indiqué';
        }
        if (empty($company_data['rcs'])) {
            $company_data['rcs'] = 'Non indiqué';
        }
        if (empty($company_data['capital'])) {
            $company_data['capital'] = 'Non indiqué';
        }
        
        $localize_data['company'] = $company_data;
        
        // DEBUG: Log company data being sent to JS

        // Ajouter les paramètres canvas
        if (class_exists('\PDF_Builder\Canvas\Canvas_Manager')) {
            $canvas_manager = \PDF_Builder\Canvas\Canvas_Manager::get_instance();
            $canvas_settings = $canvas_manager->getAllSettings();
            
            $localize_data['canvasSettings'] = $canvas_settings;
            
            // Définir aussi window.pdfBuilderCanvasSettings pour la compatibilité React
            wp_add_inline_script('pdf-builder-react-main', 
                'window.pdfBuilderCanvasSettings = ' . wp_json_encode($canvas_settings) . ';'
            );
        }

        // Ajouter les options disponibles pour les sélecteurs (DPI, formats, orientations)
        $available_dpi_string = pdf_builder_get_option('pdf_builder_canvas_dpi', '72,96,150');
        if (is_string($available_dpi_string) && strpos($available_dpi_string, ',') !== false) {
            $available_dpis = explode(',', $available_dpi_string);
        } elseif (is_array($available_dpi_string)) {
            $available_dpis = $available_dpi_string;
        } else {
            $available_dpis = [$available_dpi_string];
        }
        // TEST: Ajouter un script de test pour vérifier si notre JS peut s'exécuter
        wp_add_inline_script('jquery', '
            (function() {
                try {
                    // console.log("🧪 [PDF Builder Test] Script de test chargé avec succès");
                    // console.log("🧪 [PDF Builder Test] jQuery version:", jQuery.fn.jquery);
                    // console.log("🧪 [PDF Builder Test] Window object disponible:", typeof window !== "undefined");
                    
                    // Tester si on peut définir des variables globales
                    window.pdfBuilderTestExecuted = true;
                    // console.log("🧪 [PDF Builder Test] Variable globale définie:", window.pdfBuilderTestExecuted);
                    
                    // Tester si nos scripts sont chargés après un délai
                    setTimeout(function() {
                        // console.log("🔍 [PDF Builder Test] Vérification des scripts après délai:");
                        // console.log("🔍 [PDF Builder Test] pdf-builder-react.min.js chargé:", typeof window.pdfBuilderReact !== "undefined");
                        // console.log("🔍 [PDF Builder Test] pdfBuilderData disponible:", typeof window.pdfBuilderData !== "undefined");
                        if (window.pdfBuilderData) {
                            // console.log("🔍 [PDF Builder Test] pdfBuilderData.license:", window.pdfBuilderData.license);
                            // console.log("🔍 [PDF Builder Test] pdfBuilderData.canvasSettings:", !!window.pdfBuilderData.canvasSettings);
                        }
                        
                        // Tester si React est disponible
                        // console.log("🔍 [PDF Builder Test] React disponible:", typeof window.React !== "undefined");
                        // console.log("🔍 [PDF Builder Test] ReactDOM disponible:", typeof window.ReactDOM !== "undefined");
                    }, 2000);
                    
                } catch (error) {
                    // console.error("🧪 [PDF Builder Test] Erreur dans le script de test:", error);
                }
            })();
        ');        $available_dpis = array_map('strval', $available_dpis);

        $available_formats_string = pdf_builder_get_option('pdf_builder_canvas_formats', 'A4');
        if (is_string($available_formats_string) && strpos($available_formats_string, ',') !== false) {
            $available_formats = explode(',', $available_formats_string);
        } elseif (is_array($available_formats_string)) {
            $available_formats = $available_formats_string;
        } else {
            $available_formats = [$available_formats_string];
        }
        $available_formats = array_map('strval', $available_formats);

        $available_orientations_string = pdf_builder_get_option('pdf_builder_canvas_orientations', 'portrait,landscape');
        if (is_string($available_orientations_string) && strpos($available_orientations_string, ',') !== false) {
            $available_orientations = explode(',', $available_orientations_string);
        } elseif (is_array($available_orientations_string)) {
            $available_orientations = $available_orientations_string;
        } else {
            $available_orientations = [$available_orientations_string];
        }
        $available_orientations = array_map('strval', $available_orientations);

        $localize_data['availableDpis'] = $available_dpis;
        $localize_data['availableFormats'] = $available_formats;
        $localize_data['availableOrientations'] = $available_orientations;

        // Définir aussi window variables pour la compatibilité
        wp_add_inline_script('pdf-builder-react-main', 
            'window.availableDpis = ' . wp_json_encode($available_dpis) . ';'
        );
        wp_add_inline_script('pdf-builder-react-main', 
            'window.availableFormats = ' . wp_json_encode($available_formats) . ';'
        );
        wp_add_inline_script('pdf-builder-react-main', 
            'window.availableOrientations = ' . wp_json_encode($available_orientations) . ';'
        );

        // error_log('[WP AdminScriptLoader] Localize data prepared: ' . print_r($localize_data, true));

        // Charger les données du template si template_id est fourni
        if (isset($_GET['template_id']) && \intval($_GET['template_id']) > 0) {
            $template_id = \intval($_GET['template_id']);

            error_log('[WP AdminScriptLoader] Loading template data for ID: ' . $template_id . ', REQUEST_URI: ' . $_SERVER['REQUEST_URI']);

            // Utiliser le getter pour obtenir le TemplateProcessor (avec création à la demande)
            $template_processor = $this->admin->getTemplateProcessor();
            if ($template_processor) {

                error_log('[WP AdminScriptLoader] template_processor is available via getter, calling loadTemplateRobust');
                $existing_template_data = $template_processor->loadTemplateRobust($template_id);

                if ($existing_template_data && isset($existing_template_data['elements'])) {
                    $localize_data['initialElements'] = $existing_template_data['elements'];
                    $localize_data['existingTemplate'] = $existing_template_data;
                    $localize_data['hasExistingData'] = true;

                    error_log('[WP AdminScriptLoader] Template data loaded successfully for template ID: ' . $template_id);
                    error_log('[WP AdminScriptLoader] Template name in data: ' . ($existing_template_data['name'] ?? 'NOT FOUND'));
                    error_log('[WP AdminScriptLoader] Full template data structure: ' . json_encode($existing_template_data));
                } else {

                    error_log('[WP AdminScriptLoader] Failed to load template data for template ID: ' . $template_id . ', data: ' . print_r($existing_template_data, true));
                }
            } else {

                error_log('[WP AdminScriptLoader] template_processor not available even after getter attempt, skipping template data loading');
            }
        }

        // ✅ PREVIEW MODE: Charger les données réelles de la commande
        if (isset($_GET['order_id']) && \intval($_GET['order_id']) > 0 && isset($_GET['preview']) && $_GET['preview'] === '1') {
            $order_id = \intval($_GET['order_id']);
            $localize_data['previewMode'] = true;
            $localize_data['previewOrderId'] = $order_id;
            
            error_log('[WP AdminScriptLoader] PREVIEW MODE activated for order ID: ' . $order_id);
            
            // Charger les vraies données de la commande WooCommerce
            if (class_exists('WC_Order')) {
                $order = \wc_get_order($order_id);
                if ($order) {
                    // Extraire les données de la commande à injecter dans React
                    require_once PDF_BUILDER_PLUGIN_DIR . 'src/Generators/OrderDataExtractor.php';
                    $data_extractor = new \PDF_Builder\Generators\OrderDataExtractor($order);
                    $order_data = $data_extractor->get_all_data();
                    
                    $localize_data['previewOrderData'] = $order_data;
                    error_log('[WP AdminScriptLoader] Preview order data loaded for order #' . $order_id);
                } else {
                    error_log('[WP AdminScriptLoader] Order not found: ' . $order_id);
                }
            } else {
                error_log('[WP AdminScriptLoader] WooCommerce not available for preview mode');
            }
        }

        // Charger les données du template prédéfini si predefined_template est fourni
        if (isset($_GET['predefined_template']) && !empty($_GET['predefined_template'])) {
            $predefined_slug = \sanitize_key($_GET['predefined_template']);


            // Charger le template prédéfini
            if ($this->admin->predefined_templates_manager) {
                try {
                    // Simuler la requête AJAX pour charger le template prédéfini
                    $template_data = $this->admin->predefined_templates_manager->loadTemplateFromFile($predefined_slug);

                    if ($template_data && isset($template_data['json'])) {
                        $json_data = json_decode($template_data['json'], true);
                        if ($json_data && isset($json_data['elements'])) {
                            $localize_data['initialElements'] = $json_data['elements'];
                            $localize_data['existingTemplate'] = $json_data;
                            $localize_data['hasExistingData'] = true;
                            $localize_data['isPredefinedTemplate'] = true;
                            $localize_data['predefinedTemplateName'] = $template_data['name'] ?? 'Template prédéfini';

                            error_log('[WP AdminScriptLoader] Predefined template loaded successfully: ' . $predefined_slug);
                        } else {

                        }
                    } else {

                    }
                } catch (\Exception $e) {

                }
            } else {

            }
        }

        \wp_localize_script('pdf-builder-react-main', 'pdfBuilderData', $localize_data);
        error_log('[WP AdminScriptLoader] \wp_localize_script called for pdf-builder-react-main with data: ' . json_encode($localize_data));

        // Also set window.pdfBuilderData directly before React initializes
        static $inline_scripts_added = false;
        if (!$inline_scripts_added) {
            wp_add_inline_script('pdf-builder-react-main', 'window.pdfBuilderData = ' . wp_json_encode($localize_data) . ';', 'before');
            error_log('[WP AdminScriptLoader] wp_add_inline_script called to set window.pdfBuilderData');

            // Also set window.pdfBuilderNonce for AJAX calls
            wp_add_inline_script('pdf-builder-react-main', 'window.pdfBuilderNonce = "' . \wp_create_nonce('pdf_builder_ajax') . '";', 'before');
            error_log('[WP AdminScriptLoader] wp_add_inline_script called to set window.pdfBuilderNonce');
            $inline_scripts_added = true;
        }

        // DEBUG: Add inline script to check if data is available
        wp_add_inline_script('pdf-builder-react-main', '
            console.log("[DEBUG] pdfBuilderData available:", typeof window.pdfBuilderData);
            if (window.pdfBuilderData) {
                console.log("[DEBUG] pdfBuilderData.company:", window.pdfBuilderData.company);
                console.log("[DEBUG] Full pdfBuilderData:", window.pdfBuilderData);
            } else {
                console.log("[DEBUG] pdfBuilderData is not defined");
            }
        ', 'after');

        // Emergency reload script - DISABLED - Don't force reload
        // The React wrapper handles its own initialization without hard reload requirements
        /*
        $emergency_reload_script = "
            (function() {
                var startTime = Date.now();
                var checkInterval = setInterval(function() {
                    if (window.pdfBuilderReact && window.pdfBuilderReact.initPDFBuilderReact) {
                        
                        clearInterval(checkInterval);
                        return;
                    }
                    if (Date.now() - startTime > 5000) {
                        
                        clearInterval(checkInterval);
                        window.location.reload(true);
                    }
                }, 100);
            })();
        ";
        wp_add_inline_script('pdf-builder-react-main', $emergency_reload_script, 'after');
        */

        // React wrapper script - waits for pdfBuilderReact to be available
        $react_wrapper_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react-wrapper.min.js';
        if (!wp_script_is('pdf-builder-react-wrapper', 'enqueued')) {
            \wp_enqueue_script('pdf-builder-react-wrapper', $react_wrapper_url, ['pdf-builder-react-main'], $version_param, true);
            \wp_script_add_data('pdf-builder-react-wrapper', 'type', 'text/javascript');
            error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-wrapper: ' . $react_wrapper_url);
        }

        // Module executor - forces execution of the React bundle
        $react_executor_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react-executor.min.js';
        if (!wp_script_is('pdf-builder-react-executor', 'enqueued')) {
            \wp_enqueue_script('pdf-builder-react-executor', $react_executor_url, ['pdf-builder-react-main'], $version_param, true);
            \wp_script_add_data('pdf-builder-react-executor', 'type', 'text/javascript');
            error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-executor: ' . $react_executor_url);
        }

        // Add a safety check script that forces initialization
        wp_add_inline_script('pdf-builder-react-executor', '
            window.__pdfBuilderReactBundleLoaded = true;


            // If still not available after 100ms, something is wrong with the bundle
            setTimeout(function() {
                if (!window.pdfBuilderReact) {

                    // Check if webpack exported it as window.pdfBuilderReact
                    if (window.pdfBuilderReact) {

                    } else {

                        window.pdfBuilderReact = {
                            initPDFBuilderReact: function() {

                                return false;
                            },
                            _isFallback: true,
                            _error: "Bundle failed to export pdfBuilderReact"
                        };
                    }
                }
            }, 100);
        ', 'after');

        // Init helper
        $init_helper_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-init.min.js';
        if (!wp_script_is('pdf-builder-react-init', 'enqueued')) {
            \wp_enqueue_script('pdf-builder-react-init', $init_helper_url, ['pdf-builder-react-executor'], $cache_bust, true);
            error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-init: ' . $init_helper_url);
        }

        // React initialization script - initializes PDFBuilderReact component
        $random_param = '';
        $nuclear_suffix = '';
        $react_init_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react-init.min.js' . $random_param;
        if (!wp_script_is('pdf-builder-react-initializer', 'enqueued')) {
            \wp_enqueue_script('pdf-builder-react-initializer', $react_init_url, ['pdf-builder-react-executor'], $version_param . $nuclear_suffix, true);
            \wp_script_add_data('pdf-builder-react-initializer', 'type', 'text/javascript');
            error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-initializer');
        }

        // Scripts de l'API Preview
        $preview_client_path = PDF_BUILDER_ASSETS_DIR . 'js/pdf-preview-api-client.min.js';
        $preview_client_mtime = file_exists($preview_client_path) ? filemtime($preview_client_path) : time();
        $version_param_api = PDF_BUILDER_PRO_VERSION . '-' . $preview_client_mtime;
        
        \wp_enqueue_script('pdf-preview-api-client', PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-api-client.min.js', ['jquery'], $version_param_api, true);
        \wp_enqueue_script('pdf-preview-integration', PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-integration.min.js', ['pdf-preview-api-client'], $version_param_api, true);

        // Script d'initialisation avec debug - exécuté immédiatement après la localisation
        $init_script = "
        // 
        // 
        setTimeout(function() {
            // 
            if (window.pdfBuilderData) {
                // 
                // 
                // 
                // 
                // 
            } else {
                // 
                // 
                // 
            }
        }, 100);
        ";
        wp_add_inline_script('pdf-builder-react-main', $init_script, 'after');
        // error_log('[WP AdminScriptLoader] wp_add_inline_script called for pdf-builder-react-main');

        // Script de diagnostic supplémentaire qui s'exécute plus tôt
        $diagnostic_script = "
        jQuery(document).ready(function($) {
            // 
            setTimeout(function() {
                // 
                if (!window.pdfBuilderData) {
                    // 
                    // 
                    var scripts = document.getElementsByTagName('script');
                    for (var i = 0; i < scripts.length; i++) {
                        if (scripts[i].src && scripts[i].src.includes('pdf-builder-react')) {
                            // 
                        }
                    }
                } else {
                    // 
                }
            }, 500);
        });
        ";
        wp_add_inline_script('jquery', $diagnostic_script, 'after');
        // error_log('[WP AdminScriptLoader] Diagnostic script added to jquery');

        // Charger les scripts de diagnostic seulement en mode développement
        if (defined('WP_DEBUG') && WP_DEBUG) {
            wp_add_inline_script('jquery', '
                (function() {
                    try {
                        // console.log("🔍 [PAGE DIAGNOSTIC] URL actuelle:", window.location.href);
                        // console.log("🔍 [PAGE DIAGNOSTIC] Paramètre page:", new URLSearchParams(window.location.search).get("page"));
                        // console.log("🔍 [PAGE DIAGNOSTIC] Hook détecté:", "' . ($hook ?: 'null') . '");
                        
                        // Tester si le wrapper React est chargé
                        setTimeout(function() {
                            // console.log("🔍 [PAGE DIAGNOSTIC] Wrapper pdf-builder-react-wrapper.min.js chargé:", typeof window.pdfBuilderReactWrapper !== "undefined");
                            // console.log("🔍 [PAGE DIAGNOSTIC] pdfBuilderReact disponible:", typeof window.pdfBuilderReact !== "undefined");
                            if (window.pdfBuilderReact) {
                                // console.log("🔍 [PAGE DIAGNOSTIC] initPDFBuilderReact disponible:", typeof window.pdfBuilderReact.initPDFBuilderReact !== "undefined");
                            }
                            
                            // Vérifier si le script wrapper a été chargé dans le DOM
                            var wrapperScript = document.querySelector(\'script[src*="pdf-builder-react-wrapper.min.js"]\');
                            // console.log("🔍 [PAGE DIAGNOSTIC] Script wrapper dans DOM:", wrapperScript ? "trouvé" : "non trouvé");
                            if (wrapperScript) {
                                // console.log("🔍 [PAGE DIAGNOSTIC] URL du script wrapper:", wrapperScript.src);
                            }
                        }, 1000);
                        
                    } catch (error) {
                        // console.error("🔍 [PAGE DIAGNOSTIC] Erreur:", error);
                    }
                })();
            ');
        }

        // AJOUTER UN TEST DE CHARGEMENT DES SCRIPTS REACT
        wp_add_inline_script('jquery', '
            (function() {
                
                // Vérifier immédiatement si les scripts sont dans le DOM
                setTimeout(function() {
                    var scripts = document.getElementsByTagName("script");
                    var foundScripts = [];
                    for (var i = 0; i < scripts.length; i++) {
                        var src = scripts[i].src || "";
                        if (src.includes("pdf-builder-react")) {
                            foundScripts.push(src);
                        }
                    }
                    foundScripts.forEach(function(url, index) {
                    });
                    
                    // Tester si les scripts spécifiques sont présents
                    var initScript = document.querySelector(\'script[src*="pdf-builder-react-init.min.js"]\');
                    if (initScript) {
                    }
                    
                    var mainScript = document.querySelector(\'script[src*="pdf-builder-react.min.js"]\');
                    if (mainScript) {
                    }
                }, 500);
                
                // Tester le chargement après un délai plus long
                setTimeout(function() {
                    if (window.pdfBuilderReact) {
                    }
                }, 2000);
            })();
        ');

        error_log('[WP AdminScriptLoader] loadReactEditorScripts completed successfully');
    }

    /**
     * Corrige les templates Elementor qui sont chargés comme des scripts JavaScript
     * Les templates HTML doivent avoir type="text/template" au lieu de type="text/javascript"
     */
    public function fixElementorTemplates($tag, $handle, $src)
    {
        // Debug: Log all script tags to see what's being processed
        error_log('[PDF Builder] Processing script tag for handle: ' . $handle . ', src: ' . ($src ?: 'inline'));

        // Vérifier si c'est un script inline (pas de src)
        if (empty($src)) {
            // Check if the script content starts with HTML (indicating it's a template)
            if (preg_match('/^\s*<[^>]+>/', $tag)) {
                error_log('[PDF Builder] Found HTML template script, changing type to text/template for handle: ' . $handle);
                // Change type to text/template instead of removing
                $tag = preg_replace('/type=["\']text\/javascript["\']/', 'type="text/template"', $tag);
                return $tag;
            }

            // Also check for specific Elementor patterns as backup
            if (strpos($tag, 'elementor-templates-modal__header__logo-area') !== false ||
                strpos($tag, 'elementor-templates-modal__header__logo__icon-wrapper') !== false ||
                strpos($tag, 'elementor-finder__search') !== false ||
                strpos($tag, 'elementor-finder__no-results') !== false ||
                strpos($tag, 'elementor-finder__results__category__title') !== false ||
                strpos($tag, 'elementor-finder__results__item__link') !== false) {

                error_log('[PDF Builder] Found Elementor template script, changing type to text/template for handle: ' . $handle);

                // Change type to text/template instead of removing
                $tag = preg_replace('/type=["\']text\/javascript["\']/', 'type="text/template"', $tag);
                return $tag;
            }
        }

        return $tag;
    }

    /**
     * Démarre le buffering de sortie pour filtrer les scripts inline
     */
    public function startOutputBuffering()
    {
        error_log('[PDF Builder] Starting output buffering for Elementor script filtering');
        ob_start();
    }

    /**
     * Termine le buffering de sortie et filtre le contenu
     */
    public function endOutputBuffering()
    {
        $content = ob_get_clean();
        error_log('[PDF Builder] Ending output buffering, content length: ' . strlen($content));
        $content = $this->filterElementorInlineScripts($content);
        echo $content;
    }

    /**
     * Filtre les scripts inline Elementor contenant du HTML
     */
    private function filterElementorInlineScripts($content)
    {
        error_log('[PDF Builder] Starting Elementor script filtering');
        
        // Regex pour trouver les scripts inline contenant du HTML au début
        $pattern = '/<script[^>]*>(?:\s*<[^>]+>.*?)<\/script>/is';
        
        $content = preg_replace_callback($pattern, function($matches) {
            $script_tag = $matches[0];
            
            // Extraire le contenu entre les balises script
            if (preg_match('/<script[^>]*>(.*?)<\/script>/is', $script_tag, $inner_matches)) {
                $inner_content = $inner_matches[1];
                
                // Vérifier si le contenu commence par du HTML (après espaces)
                if (preg_match('/^\s*<[^>]+>/', $inner_content)) {
                    // C'est un template HTML, changer le type au lieu de supprimer
                    error_log('[PDF Builder] Changing Elementor HTML template script type to text/template: ' . substr($inner_content, 0, 100) . '...');
                    
                    // Remplacer type="text/javascript" par type="text/template"
                    $modified_tag = preg_replace('/type=["\']text\/javascript["\']/', 'type="text/template"', $script_tag);
                    return $modified_tag;
                }
            }
            
            // Garder le script normal
            return $script_tag;
        }, $content);
        
        error_log('[PDF Builder] Finished Elementor script filtering');
        return $content;
    }
}





