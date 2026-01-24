<?php

/**
 * PDF Builder Pro - Admin Script Loader
 * Responsable du chargement des scripts et styles d'administration
 */

namespace PDF_Builder\Admin\Loaders;

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

        // Enregistrer le hook pour charger les scripts admin
        add_action('admin_enqueue_scripts', [$this, 'loadAdminScripts'], 20);
    }

    /**
     * Charge les scripts et styles d'administration
     */
    public function loadAdminScripts($hook = null)
    {
        // Ajouter un filtre pour corriger les templates Elementor qui sont chargés comme des scripts JavaScript
        // Appliquer toujours, pas seulement sur les pages PDF Builder
        add_filter('script_loader_tag', [$this, 'fixElementorTemplates'], 10, 3);

        // Pour la page des paramètres PDF Builder, utiliser le buffering de sortie pour filtrer les scripts inline
        if (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-settings') {
            add_action('init', [$this, 'startOutputBuffering'], 1);
            add_action('shutdown', [$this, 'endOutputBuffering'], 999);
        }

        // error_log('[WP AdminScriptLoader] loadAdminScripts called with hook: ' . $hook);

        // Styles CSS de base
        wp_enqueue_style('pdf-builder-admin', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css', [], PDF_BUILDER_PRO_VERSION);

        // Charger SETTINGS CSS et JS pour les pages settings
        if (strpos($hook, 'pdf-builder') !== false || strpos($hook, 'settings') !== false) {
            // error_log('[WP AdminScriptLoader] Loading settings scripts for hook: ' . $hook);

            // Charger les utilitaires PDF Builder en premier (PerformanceMetrics, LocalCache, etc.)
            wp_enqueue_script(
                'pdf-builder-utils',
                PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-utils.js',
                [],
                PDF_BUILDER_PRO_VERSION,
                true
            );

            wp_enqueue_style(
                'pdf-builder-settings-tabs',
                PDF_BUILDER_PRO_ASSETS_URL . 'css/settings-tabs.css',
                [],
                PDF_BUILDER_PRO_VERSION
            );
            
            // Charger settings-tabs.js pour la page de paramètres spécifique, sinon settings-tabs-improved.js
            if ($hook === 'pdf-builder_page_pdf-builder-settings') {
                wp_enqueue_script(
                    'pdf-builder-settings-tabs',
                    PDF_BUILDER_PRO_ASSETS_URL . 'js/settings-tabs.js',
                    ['jquery'],
                    PDF_BUILDER_PRO_VERSION,
                    true
                );
            } else {
                wp_enqueue_script(
                    'pdf-builder-settings-tabs',
                    PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-tabs-improved.js',
                    ['jquery'],
                    PDF_BUILDER_PRO_VERSION,
                    true
                );
            }

            // Charger le système de notifications pour les pages de paramètres
            wp_enqueue_script(
                'pdf-builder-notifications',
                PDF_BUILDER_PRO_ASSETS_URL . 'js/notifications.js',
                ['jquery'],
                PDF_BUILDER_PRO_VERSION,
                true
            );

            // Charger le CSS des notifications
            wp_enqueue_style(
                'pdf-builder-notifications',
                PDF_BUILDER_PRO_ASSETS_URL . 'css/notifications.css',
                [],
                PDF_BUILDER_PRO_VERSION
            );

            // Définir les paramètres de debug JavaScript UNIQUEMENT pour les notifications
            $settings = get_option('pdf_builder_settings', array());
            $debug_settings = [
                'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
                'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
                'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
                'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
            ];
            wp_add_inline_script('pdf-builder-notifications', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');

            // Localize notifications data pour les pages de paramètres
            wp_localize_script(
                'pdf-builder-notifications', 'pdfBuilderNotifications', [
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
                ]
            );

            // Localiser les variables AJAX
            wp_localize_script(
                'pdf-builder-settings-tabs', 'pdfBuilderAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_settings')
                ]
            );

            // Définir les paramètres de debug JavaScript UNIQUEMENT pour settings-tabs
            $settings = get_option('pdf_builder_settings', array());
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

        // Scripts de l'API Preview 1.4
        wp_enqueue_script('pdf-preview-api-client', PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-api-client.min.js', ['jquery'], $version_param, true);
        wp_enqueue_script('pdf-preview-integration', PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-integration.min.js', ['pdf-preview-api-client'], $version_param, true);

        // Localize ajaxurl for integration script
        wp_localize_script(
            'pdf-preview-integration', 'pdfBuilderAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_order_actions')
            ]
        );

        // Outils développeur asynchrones
        wp_enqueue_script('pdf-builder-developer-tools', PDF_BUILDER_PRO_ASSETS_URL . 'js/developer-tools.js', ['jquery', 'pdf-preview-api-client'], $version_param, true);
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-developer-tools: ' . PDF_BUILDER_PRO_ASSETS_URL . 'js/developer-tools.js');
        // error_log('[WP AdminScriptLoader] Current page: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set'));
        // error_log('[WP AdminScriptLoader] Current hook: ' . $hook);

        // Localize pdfBuilderAjax for API Preview scripts
        wp_localize_script(
            'pdf-preview-api-client', 'pdfBuilderAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_order_actions'),
            'version' => PDF_BUILDER_PRO_VERSION,
            'timestamp' => time(),
            'strings' => [
                'error_loading_preview' => __('Erreur lors du chargement de l\'aperçu', 'pdf-builder-pro'),
                'generating_pdf' => __('Génération du PDF en cours...', 'pdf-builder-pro'),
            ]
            ]
        );

        // Nonce pour les templates
        wp_add_inline_script('pdf-preview-api-client', 'var pdfBuilderTemplatesNonce = "' . wp_create_nonce('pdf_builder_templates') . '";');

        // Scripts pour l'éditeur React
        if (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-react-editor') {
            error_log('[WP AdminScriptLoader] Loading React editor scripts for page: ' . $_GET['page']);
            $this->loadReactEditorScripts();
        } else {
            error_log('[WP AdminScriptLoader] NOT loading React editor scripts, page is: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set') . ', hook: ' . $hook);
        }

        // Charger aussi les scripts React si on est sur une page qui contient "react-editor" dans l'URL
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'pdf-builder-react-editor') !== false) {
            error_log('[WP AdminScriptLoader] Loading React editor scripts from REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
            $this->loadReactEditorScripts();
        }
    }

    /**
     * Charge les scripts spécifiques à l'éditeur React
     */
    private function loadReactEditorScripts()
    {
        error_log('[WP AdminScriptLoader] loadReactEditorScripts called at ' . date('Y-m-d H:i:s') . ' for page: ' . (isset($_GET['page']) ? $_GET['page'] : 'unknown'));
        error_log('[WP AdminScriptLoader] REQUEST_URI: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set'));
        error_log('[WP AdminScriptLoader] Current URL: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set'));

        $cache_bust = microtime(true) . '-' . rand(1000, 9999);
        $version_param = PDF_BUILDER_PRO_VERSION . '-' . $cache_bust;

        error_log('[WP AdminScriptLoader] Cache bust: ' . $cache_bust);
        error_log('[WP AdminScriptLoader] Version param: ' . $version_param);

        // AJAX throttle manager
        $throttle_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/ajax-throttle.js';
        wp_enqueue_script('pdf-builder-ajax-throttle', $throttle_url, [], $cache_bust, true);
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-ajax-throttle: ' . $throttle_url . ' with cache_bust: ' . $cache_bust);

        // Notifications system
        $notifications_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/notifications.js';
        wp_enqueue_script('pdf-builder-notifications', $notifications_url, ['jquery'], $cache_bust, true);
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-notifications: ' . $notifications_url . ' with cache_bust: ' . $cache_bust);

        // Notifications CSS
        $notifications_css_url = PDF_BUILDER_PRO_ASSETS_URL . 'css/notifications.css';
        wp_enqueue_style('pdf-builder-notifications', $notifications_css_url, [], $cache_bust);
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-notifications CSS: ' . $notifications_css_url . ' with cache_bust: ' . $cache_bust);

        // Localize notifications data
        wp_localize_script(
            'pdf-builder-notifications', 'pdfBuilderNotifications', [
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
            ]
        );

        // Définir les paramètres de debug JavaScript UNIQUEMENT pour l'éditeur React
        $settings = get_option('pdf_builder_settings', array());
        $debug_settings = [
            'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
            'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
            'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
            'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
        ];
        wp_add_inline_script('pdf-builder-notifications', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');

        // Wrapper script
        $wrap_helper_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-wrap.js';
        wp_enqueue_script('pdf-builder-wrap', $wrap_helper_url, ['pdf-builder-ajax-throttle', 'pdf-builder-notifications'], $cache_bust, true);
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-wrap: ' . $wrap_helper_url);

        // FORCE COMPLETE RELOAD - Use ULTRA NUCLEAR aggressive version parameters to bypass ALL caching
        $nuclear_suffix = '-ULTRA-NUCLEAR-' . microtime(true) . '-' . time() . '-' . uniqid('ULTRA-NUKE', true) . '-FORCE-RELOAD-' . rand(10000000, 99999999);
        $version_param = PDF_BUILDER_PRO_VERSION . '-' . microtime(true) . '-' . rand(100000, 999999) . '-ULTRA-NUKE-' . uniqid();

        // Add random query parameter to URLs to bypass ALL caching
        $random_param = '?t=' . microtime(true) . '&r=' . rand(1000000, 9999999) . '&nuke=' . uniqid('NUKE', true) . '&ultra=' . time();

        // Load React main bundle first
        $react_main_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react.min.js' . $random_param;
        wp_enqueue_script('pdf-builder-react-main', $react_main_url, ['pdf-builder-wrap'], $version_param . $nuclear_suffix, true);
        wp_script_add_data('pdf-builder-react-main', 'type', 'text/javascript');
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-main');

        // CSS pour l'éditeur React
        $react_css_url = PDF_BUILDER_PLUGIN_URL . 'assets/css/pdf-builder-react.min.css';
        wp_enqueue_style('pdf-builder-react', $react_css_url, [], $version_param . $nuclear_suffix);

        // Wrapper script (dépend du bundle principal)
        $react_script_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react-wrapper.min.js' . $random_param;
        wp_enqueue_script('pdf-builder-react-wrapper', $react_script_url, ['pdf-builder-react-main'], $version_param . $nuclear_suffix, true);
        wp_script_add_data('pdf-builder-react-wrapper', 'type', 'text/javascript');
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-wrapper');
        
        // Localize script data BEFORE enqueuing
        $localize_data = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_templates'),
            'version' => PDF_BUILDER_PRO_VERSION,
            'templateId' => isset($_GET['template_id']) ? intval($_GET['template_id']) : 0,
            'isEdit' => isset($_GET['template_id']) && intval($_GET['template_id']) > 0,
        ];

        // Ajouter les paramètres canvas
        if (class_exists('\PDF_Builder\Canvas\Canvas_Manager')) {
            $canvas_manager = \PDF_Builder\Canvas\Canvas_Manager::get_instance();
            $canvas_settings = $canvas_manager->getAllSettings();
            $localize_data['canvasSettings'] = $canvas_settings;
            
            // Définir aussi window.pdfBuilderCanvasSettings pour la compatibilité React
            wp_add_inline_script(
                'pdf-builder-react-main', 
                'window.pdfBuilderCanvasSettings = ' . wp_json_encode($canvas_settings) . ';'
            );
        }

        // error_log('[WP AdminScriptLoader] Localize data prepared: ' . print_r($localize_data, true));

        // Charger les données du template si template_id est fourni
        if (isset($_GET['template_id']) && intval($_GET['template_id']) > 0) {
            $template_id = intval($_GET['template_id']);
            // error_log('[WP AdminScriptLoader] Loading template data for ID: ' . $template_id);

            // Vérifier que template_processor existe
            if (isset($this->admin->template_processor) && $this->admin->template_processor) {
                $existing_template_data = $this->admin->template_processor->loadTemplateRobust($template_id);
                if ($existing_template_data && isset($existing_template_data['elements'])) {
                    $localize_data['initialElements'] = $existing_template_data['elements'];
                    $localize_data['existingTemplate'] = $existing_template_data;
                    $localize_data['hasExistingData'] = true;
                    // error_log('[WP AdminScriptLoader] Template data loaded successfully for template ID: ' . $template_id);
                    // error_log('[WP AdminScriptLoader] Template name in data: ' . ($existing_template_data['name'] ?? 'NOT FOUND'));
                    // error_log('[WP AdminScriptLoader] Full template data structure: ' . json_encode($existing_template_data));
                } else {
                    // error_log('[WP AdminScriptLoader] Failed to load template data for template ID: ' . $template_id . ', data: ' . print_r($existing_template_data, true));
                }
            } else {
                // error_log('[WP AdminScriptLoader] Template processor not available, skipping template data loading');
            }
        }

        wp_localize_script('pdf-builder-react-main', 'pdfBuilderData', $localize_data);
        // error_log('[WP AdminScriptLoader] wp_localize_script called for pdf-builder-react-main');

        // Also set window.pdfBuilderData directly before React initializes
        wp_add_inline_script('pdf-builder-react-main', 'window.pdfBuilderData = ' . wp_json_encode($localize_data) . ';', 'before');
        // error_log('[WP AdminScriptLoader] wp_add_inline_script called to set window.pdfBuilderData');

        // Emergency reload script - force page reload if React scripts don't load within 5 seconds
        $emergency_reload_script = "
            (function() {
                var startTime = Date.now();
                var checkInterval = setInterval(function() {
                    if (window.pdfBuilderReact && window.pdfBuilderReact.initPDFBuilderReact) {
                        console.log('[Emergency Reload] React scripts loaded successfully');
                        clearInterval(checkInterval);
                        return;
                    }
                    if (Date.now() - startTime > 5000) {
                        console.error('[Emergency Reload] React scripts failed to load within 5 seconds - forcing page reload');
                        clearInterval(checkInterval);
                        window.location.reload(true);
                    }
                }, 100);
            })();
        ";
        wp_add_inline_script('pdf-builder-react-main', $emergency_reload_script, 'after');

        // Init helper
        $init_helper_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-init.js';
        wp_enqueue_script('pdf-builder-react-init', $init_helper_url, ['pdf-builder-react-main'], $cache_bust, true);
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-init: ' . $init_helper_url);

        // Scripts de l'API Preview
        $preview_client_path = PDF_BUILDER_ASSETS_DIR . 'js/pdf-preview-api-client.min.js';
        $preview_client_mtime = file_exists($preview_client_path) ? filemtime($preview_client_path) : time();
        $version_param_api = PDF_BUILDER_PRO_VERSION . '-' . $preview_client_mtime;
        
        wp_enqueue_script('pdf-preview-api-client', PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-api-client.min.js', ['jquery'], $version_param_api, true);
        wp_enqueue_script('pdf-preview-integration', PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-preview-integration.min.js', ['pdf-preview-api-client'], $version_param_api, true);

        // Script d'initialisation avec debug - exécuté immédiatement après la localisation
        $init_script = "
        // console.log('🔧 [WP] Script d\'initialisation exécuté à ' + new Date().toISOString());
        // console.log('🔧 [WP] Vérification window.pdfBuilderData dans 100ms...');
        setTimeout(function() {
            // console.log('🔧 [WP] Localized data après timeout:', window.pdfBuilderData);
            if (window.pdfBuilderData) {
                // console.log('✅ [WP] ajaxUrl:', window.pdfBuilderData.ajaxUrl);
                // console.log('✅ [WP] nonce:', window.pdfBuilderData.nonce);
                // console.log('✅ [WP] version:', window.pdfBuilderData.version);
                // console.log('✅ [WP] templateId:', window.pdfBuilderData.templateId);
                // console.log('✅ [WP] Toutes les clés:', Object.keys(window.pdfBuilderData));
            } else {
                // console.error('❌ [WP] pdfBuilderData not found on window après timeout');
                // console.log('❌ [WP] window keys avec pdfBuilder:', Object.keys(window).filter(key => key.includes('pdfBuilder')));
                // console.log('❌ [WP] Toutes les clés window:', Object.keys(window));
            }
        }, 100);
        ";
        wp_add_inline_script('pdf-builder-react-main', $init_script, 'after');
        // error_log('[WP AdminScriptLoader] wp_add_inline_script called for pdf-builder-react-main');

        // Script de diagnostic supplémentaire qui s'exécute plus tôt
        $diagnostic_script = "
        jQuery(document).ready(function($) {
            // console.log('🔧 [WP] Document ready - vérification pdfBuilderData à ' + new Date().toISOString());
            setTimeout(function() {
                // console.log('🔧 [WP] pdfBuilderData dans document ready:', window.pdfBuilderData);
                if (!window.pdfBuilderData) {
                    // console.error('❌ [WP] pdfBuilderData toujours undefined dans document ready');
                    // console.log('❌ [WP] Vérification des scripts chargés...');
                    var scripts = document.getElementsByTagName('script');
                    for (var i = 0; i < scripts.length; i++) {
                        if (scripts[i].src && scripts[i].src.includes('pdf-builder-react')) {
                            // console.log('❌ [WP] Script trouvé:', scripts[i].src);
                        }
                    }
                } else {
                    // console.log('✅ [WP] pdfBuilderData trouvé dans document ready');
                }
            }, 500);
        });
        ";
        wp_add_inline_script('jquery', $diagnostic_script, 'after');
        // error_log('[WP AdminScriptLoader] Diagnostic script added to jquery');
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
            if (strpos($tag, 'elementor-templates-modal__header__logo-area') !== false 
                || strpos($tag, 'elementor-templates-modal__header__logo__icon-wrapper') !== false 
                || strpos($tag, 'elementor-finder__search') !== false 
                || strpos($tag, 'elementor-finder__no-results') !== false 
                || strpos($tag, 'elementor-finder__results__category__title') !== false 
                || strpos($tag, 'elementor-finder__results__item__link') !== false
            ) {

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
        
        $content = preg_replace_callback(
            $pattern, function ($matches) {
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
            }, $content
        );
        
        error_log('[PDF Builder] Finished Elementor script filtering');
        return $content;
    }
}
