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
    }

    /**
     * Charge les scripts et styles d'administration
     */
    public function loadAdminScripts($hook = null)
    {
        // error_log('[WP AdminScriptLoader] loadAdminScripts called with hook: ' . $hook);

        // Styles CSS de base
        wp_enqueue_style('pdf-builder-admin', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css', [], PDF_BUILDER_PRO_VERSION);

        // Charger SETTINGS CSS et JS pour les pages settings
        if (strpos($hook, 'pdf-builder') !== false || strpos($hook, 'settings') !== false) {
            // error_log('[WP AdminScriptLoader] Loading settings scripts for hook: ' . $hook);

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
                    $version_param,
                    true
                );
            } else {
                wp_enqueue_script(
                    'pdf-builder-settings-tabs',
                    PDF_BUILDER_PRO_ASSETS_URL . 'js/settings-tabs-improved.js',
                    ['jquery'],
                    $version_param,
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

            // Définir les paramètres de debug JavaScript pour les notifications aussi
            $settings = get_option('pdf_builder_settings', array());
            $debug_settings = [
                'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
                'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
                'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
                'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
            ];
            wp_add_inline_script('pdf-builder-notifications', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');

            // Localize notifications data pour les pages de paramètres
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

            // Localiser les variables AJAX
            wp_localize_script('pdf-builder-settings-tabs', 'pdfBuilderAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_settings')
            ]);

            // Définir les paramètres de debug JavaScript
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
        wp_enqueue_script('pdf-preview-api-client', PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-preview-api-client.js', ['jquery'], $version_param, true);
        wp_enqueue_script('pdf-preview-integration', PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-preview-integration.js', ['pdf-preview-api-client'], $version_param, true);

        // Localize ajaxurl for integration script
        wp_localize_script('pdf-preview-integration', 'pdfBuilderAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_order_actions')
        ]);

        // Outils développeur asynchrones
        wp_enqueue_script('pdf-builder-developer-tools', PDF_BUILDER_PRO_ASSETS_URL . 'js/developer-tools.js', ['jquery', 'pdf-preview-api-client'], $version_param, true);
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-developer-tools: ' . PDF_BUILDER_PRO_ASSETS_URL . 'js/developer-tools.js');
        // error_log('[WP AdminScriptLoader] Current page: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set'));
        // error_log('[WP AdminScriptLoader] Current hook: ' . $hook);

        // Définir les paramètres de debug JavaScript pour developer-tools.js
        $settings = get_option('pdf_builder_settings', array());
        $debug_settings = [
            'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
            'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
            'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
            'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
        ];
        wp_add_inline_script('pdf-builder-developer-tools', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');

        // Localize pdfBuilderAjax for API Preview scripts
        wp_localize_script('pdf-preview-api-client', 'pdfBuilderAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_order_actions'),
            'version' => PDF_BUILDER_PRO_VERSION,
            'timestamp' => time(),
            'strings' => [
                'error_loading_preview' => __('Erreur lors du chargement de l\'aperçu', 'pdf-builder-pro'),
                'generating_pdf' => __('Génération du PDF en cours...', 'pdf-builder-pro'),
            ]
        ]);

        // Définir les paramètres de debug JavaScript pour tous les scripts
        $settings = get_option('pdf_builder_settings', array());
        $debug_settings = [
            'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
            'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
            'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
            'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
        ];
        wp_add_inline_script('pdf-preview-api-client', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');
        wp_add_inline_script('pdf-builder-developer-tools', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');

        // Nonce pour les templates
        wp_add_inline_script('pdf-preview-api-client', 'var pdfBuilderTemplatesNonce = "' . wp_create_nonce('pdf_builder_templates') . '";');

        // Scripts pour l'éditeur React
        if (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-react-editor') {
            // error_log('[WP AdminScriptLoader] Loading React editor scripts for page: ' . $_GET['page']);
            $this->loadReactEditorScripts();
        } else {
            // error_log('[WP AdminScriptLoader] NOT loading React editor scripts, page is: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set') . ', hook: ' . $hook);
        }
    }

    /**
     * Charge les scripts spécifiques à l'éditeur React
     */
    private function loadReactEditorScripts()
    {
        // error_log('[WP AdminScriptLoader] loadReactEditorScripts called at ' . date('Y-m-d H:i:s'));

        $cache_bust = time();
        $version_param = PDF_BUILDER_PRO_VERSION . '-' . $cache_bust;

        // AJAX throttle manager
        $throttle_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/ajax-throttle.js';
        wp_enqueue_script('pdf-builder-ajax-throttle', $throttle_url, [], $cache_bust, true);
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-ajax-throttle: ' . $throttle_url);

        // Notifications system
        $notifications_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/notifications.js';
        wp_enqueue_script('pdf-builder-notifications', $notifications_url, ['jquery'], $cache_bust, true);
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-notifications: ' . $notifications_url);

        // Notifications CSS
        $notifications_css_url = PDF_BUILDER_PRO_ASSETS_URL . 'css/notifications.css';
        wp_enqueue_style('pdf-builder-notifications', $notifications_css_url, [], $cache_bust);
        // error_log('[WP AdminScriptLoader] Enqueued pdf-builder-notifications CSS: ' . $notifications_css_url);

        // Localize notifications data
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

        // Définir les paramètres de debug JavaScript pour l'éditeur React aussi
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

        // Bundle React
        $react_script_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-react.js';
        
        // Localize script data BEFORE enqueuing
        $localize_data = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_templates'),
            'version' => PDF_BUILDER_PRO_VERSION,
            'templateId' => isset($_GET['template_id']) ? intval($_GET['template_id']) : 0,
            'isEdit' => isset($_GET['template_id']) && intval($_GET['template_id']) > 0,
        ];

        // error_log('[WP AdminScriptLoader] Localize data prepared: ' . print_r($localize_data, true));

        // Charger les données du template si template_id est fourni
        if (isset($_GET['template_id']) && intval($_GET['template_id']) > 0) {
            $template_id = intval($_GET['template_id']);
            // error_log('[WP AdminScriptLoader] Loading template data for ID: ' . $template_id);
            $existing_template_data = $this->admin->getTemplateProcessor()->loadTemplateRobust($template_id);
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
        }

        wp_localize_script('pdf-builder-react', 'pdfBuilderData', $localize_data);
        error_log('[WP AdminScriptLoader] wp_localize_script called for pdf-builder-react with data: ' . json_encode($localize_data));

        // Also set window.pdfBuilderData directly
        wp_add_inline_script('pdf-builder-react', 'console.log("🔧 [WP INLINE] Setting window.pdfBuilderData"); window.pdfBuilderData = ' . wp_json_encode($localize_data) . '; console.log("🔧 [WP INLINE] window.pdfBuilderData set to", window.pdfBuilderData);', 'before');
        error_log('[WP AdminScriptLoader] wp_add_inline_script called to set window.pdfBuilderData');

        wp_enqueue_script('pdf-builder-react', $react_script_url, ['pdf-builder-wrap'], $version_param, true);
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react: ' . $react_script_url);

        // Définir les paramètres de debug JavaScript pour React
        $settings = get_option('pdf_builder_settings', array());
        $debug_settings = [
            'javascript' => isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'],
            'javascript_verbose' => isset($settings['pdf_builder_debug_javascript_verbose']) && $settings['pdf_builder_debug_javascript_verbose'],
            'php' => isset($settings['pdf_builder_debug_php']) && $settings['pdf_builder_debug_php'],
            'ajax' => isset($settings['pdf_builder_debug_ajax']) && $settings['pdf_builder_debug_ajax']
        ];
        wp_add_inline_script('pdf-builder-react', 'window.pdfBuilderDebugSettings = ' . wp_json_encode($debug_settings) . ';', 'before');

        // Init helper
        $init_helper_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-init.js';
        wp_enqueue_script('pdf-builder-react-init', $init_helper_url, ['pdf-builder-react'], $cache_bust, true);
        error_log('[WP AdminScriptLoader] Enqueued pdf-builder-react-init: ' . $init_helper_url);

        // Scripts de l'API Preview
        $preview_client_path = PDF_BUILDER_ASSETS_DIR . 'js/pdf-preview-api-client.js';
        $preview_client_mtime = file_exists($preview_client_path) ? filemtime($preview_client_path) : time();
        $version_param_api = PDF_BUILDER_PRO_VERSION . '-' . $preview_client_mtime;
        
        wp_enqueue_script('pdf-preview-api-client', PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-preview-api-client.js', ['jquery'], $version_param_api, true);
        wp_enqueue_script('pdf-preview-integration', PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-preview-integration.js', ['pdf-preview-api-client'], $version_param_api, true);

        // Script d'initialisation avec debug - exécuté immédiatement après la localisation
        $init_script = "
        console.log('🔧 [WP] Script d\'initialisation exécuté à ' + new Date().toISOString());
        console.log('🔧 [WP] Vérification window.pdfBuilderData dans 100ms...');
        setTimeout(function() {
            console.log('🔧 [WP] Localized data après timeout:', window.pdfBuilderData);
            if (window.pdfBuilderData) {
                console.log('✅ [WP] ajaxUrl:', window.pdfBuilderData.ajaxUrl);
                console.log('✅ [WP] nonce:', window.pdfBuilderData.nonce);
                console.log('✅ [WP] version:', window.pdfBuilderData.version);
                console.log('✅ [WP] templateId:', window.pdfBuilderData.templateId);
                console.log('✅ [WP] Toutes les clés:', Object.keys(window.pdfBuilderData));
            } else {
                console.error('❌ [WP] pdfBuilderData not found on window après timeout');
                console.log('❌ [WP] window keys avec pdfBuilder:', Object.keys(window).filter(key => key.includes('pdfBuilder')));
                console.log('❌ [WP] Toutes les clés window:', Object.keys(window));
            }
        }, 100);
        ";
        wp_add_inline_script('pdf-builder-react', $init_script, 'after');
        error_log('[WP AdminScriptLoader] wp_add_inline_script called for pdf-builder-react');

        // Script de diagnostic supplémentaire qui s'exécute plus tôt
        $diagnostic_script = "
        jQuery(document).ready(function($) {
            console.log('🔧 [WP] Document ready - vérification pdfBuilderData à ' + new Date().toISOString());
            setTimeout(function() {
                console.log('🔧 [WP] pdfBuilderData dans document ready:', window.pdfBuilderData);
                if (!window.pdfBuilderData) {
                    console.error('❌ [WP] pdfBuilderData toujours undefined dans document ready');
                    console.log('❌ [WP] Vérification des scripts chargés...');
                    var scripts = document.getElementsByTagName('script');
                    for (var i = 0; i < scripts.length; i++) {
                        if (scripts[i].src && scripts[i].src.includes('pdf-builder-react')) {
                            console.log('❌ [WP] Script trouvé:', scripts[i].src);
                        }
                    }
                } else {
                    console.log('✅ [WP] pdfBuilderData trouvé dans document ready');
                }
            }, 500);
        });
        ";
        wp_add_inline_script('jquery', $diagnostic_script, 'after');
        error_log('[WP AdminScriptLoader] Diagnostic script added to jquery');
    }
}
