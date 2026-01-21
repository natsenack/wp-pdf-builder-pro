<?php
/**
 * PDF Builder Pro - React Assets Loader V2
 * Charge les assets React pour l'interface d'administration
 */

namespace PDF_Builder\Includes;

class ReactAssetsV2
{
    /**
     * Instance unique
     */
    private static $instance = null;

    /**
     * Récupère l'instance unique
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur
     */
    private function __construct()
    {
        $this->registerHooks();
    }

    /**
     * Enregistre les hooks WordPress
     */
    private function registerHooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueReactAssets'], 10);
    }

    /**
     * Charge les assets React
     */
    public function enqueueReactAssets($hook)
    {
        // Charger seulement sur les pages du plugin
        $allowed_hooks = [
            'toplevel_page_pdf-builder-react-editor',
            'pdf-builder_page_pdf-builder-react-editor',
            'pdf-builder-pro_page_pdf-builder-settings'
        ];

        if (!in_array($hook, $allowed_hooks)) {
            return;
        }

        // Définir les chemins des assets
        $assets_url = PDF_BUILDER_PRO_ASSETS_URL;
        $assets_path = PDF_BUILDER_PRO_ASSETS_PATH;
        $version = PDF_BUILDER_VERSION;

        // Charger les dépendances CSS
        wp_enqueue_style(
            'pdf-builder-react',
            $assets_url . 'css/pdf-builder-react.min.css',
            [],
            $version
        );

        wp_enqueue_style(
            'notifications-css',
            $assets_url . 'css/notifications.min.css',
            [],
            $version
        );

        // Charger les dépendances JavaScript
        wp_enqueue_script(
            'react-vendor',
            $assets_url . 'js/react-vendor.min.js',
            [],
            $version,
            true
        );

        wp_enqueue_script(
            'pdf-builder-react',
            $assets_url . 'js/pdf-builder-react.min.js',
            ['react-vendor', 'jquery', 'wp-util'],
            $version,
            true
        );

        wp_enqueue_script(
            'canvas-settings',
            $assets_url . 'js/canvas-settings.min.js',
            ['react-vendor', 'jquery', 'wp-util'],
            $version,
            true
        );

        wp_enqueue_script(
            'pdf-builder-react-wrapper',
            $assets_url . 'js/pdf-builder-react-wrapper.min.js',
            ['pdf-builder-react', 'canvas-settings'],
            $version,
            true
        );

        wp_enqueue_script(
            'pdf-builder-react-init',
            $assets_url . 'js/pdf-builder-react-init.min.js',
            ['pdf-builder-react-wrapper'],
            $version,
            true
        );

        // Scripts utilitaires
        wp_enqueue_script(
            'ajax-throttle',
            $assets_url . 'js/ajax-throttle.min.js',
            ['jquery'],
            $version,
            true
        );

        wp_enqueue_script(
            'notifications',
            $assets_url . 'js/notifications.min.js',
            ['jquery'],
            $version,
            true
        );

        wp_enqueue_script(
            'pdf-builder-wrap',
            $assets_url . 'js/pdf-builder-wrap.min.js',
            ['jquery'],
            $version,
            true
        );

        wp_enqueue_script(
            'pdf-builder-init',
            $assets_url . 'js/pdf-builder-init.min.js',
            ['pdf-builder-wrap'],
            $version,
            true
        );

        // Localiser les scripts pour AJAX
        wp_localize_script('pdf-builder-react', 'pdfBuilderAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_ajax_nonce'),
            'strings' => [
                'loading' => __('Chargement...', 'pdf-builder-pro'),
                'error' => __('Erreur', 'pdf-builder-pro'),
                'success' => __('Succès', 'pdf-builder-pro'),
            ]
        ]);

        // Ajouter des variables globales pour React
        wp_localize_script('pdf-builder-react', 'pdfBuilderSettings', [
            'isPremium' => defined('PDF_BUILDER_PREMIUM') && PDF_BUILDER_PREMIUM,
            'version' => PDF_BUILDER_VERSION,
            'assetsUrl' => $assets_url,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_ajax_nonce'),
        ]);
    }
}

// Initialiser la classe
ReactAssetsV2::getInstance();