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
        // Styles CSS de base
        wp_enqueue_style('pdf-builder-admin', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css', [], PDF_BUILDER_PRO_VERSION);

        // Charger SETTINGS CSS et JS pour les pages settings
        if (strpos($hook, 'pdf-builder') !== false || strpos($hook, 'settings') !== false) {
            wp_enqueue_style(
                'pdf-builder-settings-tabs',
                PDF_BUILDER_PRO_ASSETS_URL . 'css/settings-tabs.css',
                [],
                PDF_BUILDER_PRO_VERSION
            );
            
            wp_enqueue_script(
                'pdf-builder-settings-tabs',
                PDF_BUILDER_PRO_ASSETS_URL . 'js/settings-tabs-improved.js',
                ['jquery'],
                PDF_BUILDER_PRO_VERSION,
                true
            );

            // Localiser les variables AJAX
            wp_localize_script('pdf-builder-settings-tabs', 'pdfBuilderAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_settings')
            ]);
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

        // Nonce pour les templates
        wp_add_inline_script('pdf-preview-api-client', 'var pdfBuilderTemplatesNonce = "' . wp_create_nonce('pdf_builder_templates') . '";');

        // Scripts pour l'éditeur React
        if ($hook === 'pdf-builder_page_pdf-builder-react-editor') {
            $this->loadReactEditorScripts();
        }
    }

    /**
     * Charge les scripts spécifiques à l'éditeur React
     */
    private function loadReactEditorScripts()
    {
        $cache_bust = time();
        $version_param = PDF_BUILDER_PRO_VERSION . '-' . $cache_bust;

        // AJAX throttle manager
        $throttle_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/ajax-throttle.js';
        wp_enqueue_script('pdf-builder-ajax-throttle', $throttle_url, [], $cache_bust, true);

        // Wrapper script
        $wrap_helper_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-wrap.js';
        wp_enqueue_script('pdf-builder-wrap', $wrap_helper_url, ['pdf-builder-ajax-throttle'], $cache_bust, true);

        // Bundle React
        $react_script_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-react.js';
        wp_enqueue_script('pdf-builder-react', $react_script_url, ['pdf-builder-wrap'], $version_param, true);

        // Init helper
        $init_helper_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-init.js';
        wp_enqueue_script('pdf-builder-react-init', $init_helper_url, ['pdf-builder-react'], $cache_bust, true);

        // Scripts de l'API Preview
        $preview_client_path = PDF_BUILDER_ASSETS_DIR . 'js/pdf-preview-api-client.js';
        $preview_client_mtime = file_exists($preview_client_path) ? filemtime($preview_client_path) : time();
        $version_param_api = PDF_BUILDER_PRO_VERSION . '-' . $preview_client_mtime;
        
        wp_enqueue_script('pdf-preview-api-client', PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-preview-api-client.js', ['jquery'], $version_param_api, true);
        wp_enqueue_script('pdf-preview-integration', PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-preview-integration.js', ['pdf-preview-api-client'], $version_param_api, true);

        // Localize script data
        $localize_data = [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_templates'),
            'version' => PDF_BUILDER_PRO_VERSION,
            'templateId' => isset($_GET['template_id']) ? intval($_GET['template_id']) : 0,
            'isEdit' => isset($_GET['template_id']) && intval($_GET['template_id']) > 0,
        ];

        // Charger les données du template si template_id est fourni
        if (isset($_GET['template_id']) && intval($_GET['template_id']) > 0) {
            $template_id = intval($_GET['template_id']);
            $existing_template_data = $this->admin->getTemplateProcessor()->loadTemplateRobust($template_id);
            if ($existing_template_data && isset($existing_template_data['elements'])) {
                $localize_data['initialElements'] = $existing_template_data['elements'];
                $localize_data['initialTemplate'] = $existing_template_data;
            }
        }

        wp_localize_script('pdf-builder-react', 'pdfBuilderData', $localize_data);

        // Script d'initialisation
        $init_script = "(function() {})();";
        wp_add_inline_script('pdf-builder-react', $init_script, 'after');
    }
}
