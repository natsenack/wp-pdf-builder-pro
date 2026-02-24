<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * PDF Builder Pro - Contrôleur REST API
 * Point d'entrée pour les endpoints REST avec vérification de licence
 */

namespace PDF_Builder\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class PDF_Builder_REST_Controller {

    protected $namespace = 'pdf-builder/v1';

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route($this->namespace, '/test', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'test_endpoint'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        register_rest_route($this->namespace, '/generate-pdf', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'generate_pdf'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        register_rest_route($this->namespace, '/webhooks', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'create_webhook'),
            'permission_callback' => array($this, 'check_permission'),
        ));
        register_rest_route($this->namespace, '/license-info', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_license_info'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }

    public function check_permission() {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('unauthorized', __("Vous n'avez pas la permission d'acceder a cette ressource.", 'pdf-builder-pro'), array('status' => 403));
        }
        return true;
    }

    public function test_endpoint(\WP_REST_Request $request) {
        return rest_ensure_response(array(
            'status'       => 'success',
            'message'      => __('API PDF Builder Pro fonctionne correctement!', 'pdf-builder-pro'),
            'license_info' => PDF_Builder_API_Helper::get_license_info(),
        ));
    }

    public function generate_pdf(\WP_REST_Request $request) {
        $is_premium = PDF_Builder_API_Helper::is_premium();
        if (!$is_premium) {
            PDF_Builder_API_Helper::log_premium_attempt('generate_pdf_advanced');
            return PDF_Builder_API_Helper::check_premium_license(__('Generation PDF avancee', 'pdf-builder-pro'));
        }
        $template_id = $request->get_param('template_id');
        if (empty($template_id)) {
            return new \WP_Error('missing_template', __("L'ID de template est requis.", 'pdf-builder-pro'), array('status' => 400));
        }
        return rest_ensure_response(array(
            'status'      => 'success',
            'message'     => __('Generation PDF lancee.', 'pdf-builder-pro'),
            'template_id' => $template_id,
        ));
    }

    public function create_webhook(\WP_REST_Request $request) {
        $is_premium = PDF_Builder_API_Helper::is_premium();
        if (!$is_premium) {
            PDF_Builder_API_Helper::log_premium_attempt('create_webhook');
            return PDF_Builder_API_Helper::check_premium_license(__('Webhooks', 'pdf-builder-pro'));
        }
        $url    = $request->get_param('url');
        $events = $request->get_param('events');
        if (empty($url) || empty($events)) {
            return new \WP_Error('missing_params', __('URL et evenements sont requis.', 'pdf-builder-pro'), array('status' => 400));
        }
        return rest_ensure_response(array(
            'status'     => 'success',
            'message'    => __('Webhook cree.', 'pdf-builder-pro'),
            'webhook_id' => uniqid('webhook_'),
        ));
    }

    public function get_license_info(\WP_REST_Request $request) {
        return rest_ensure_response(PDF_Builder_API_Helper::get_license_info());
    }
}
