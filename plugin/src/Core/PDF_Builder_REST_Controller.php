<?php
/**
 * PDF Builder Pro - Contrôleur REST API
 * Point d'entrée pour les endpoints REST avec vérification de licence
 */

namespace PDF_Builder\Core;

use WP_REST_Controller;
use WP_REST_Server;

class PDF_Builder_REST_Controller extends WP_REST_Controller {

    protected $namespace = 'pdf-builder/v1';

    /**
     * Initialiser les routes REST
     */
    public function register_routes() {
        // Route de test
        register_rest_route($this->namespace, '/test', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'test_endpoint'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Route de génération PDF (premium)
        register_rest_route($this->namespace, '/generate-pdf', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'generate_pdf'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Route webhooks (premium uniquement)
        register_rest_route($this->namespace, '/webhooks', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'create_webhook'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Route licence
        register_rest_route($this->namespace, '/license-info', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'get_license_info'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }

    /**
     * Vérifier les permissions d'accès
     */
    public function check_permission() {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('unauthorized', __('Vous n\'avez pas la permission d\'accéder à cette ressource.', 'pdf-builder-pro'), array('status' => 403));
        }
        return true;
    }

    /**
     * Endpoint de test
     */
    public function test_endpoint(\WP_REST_Request $request) {
        return rest_ensure_response(array(
            'status' => 'success',
            'message' => __('API PDF Builder Pro fonctionne correctement!', 'pdf-builder-pro'),
            'license_info' => PDF_Builder_API_Helper::get_license_info(),
        ));
    }

    /**
     * Endpoint de génération PDF
     */
    public function generate_pdf(\WP_REST_Request $request) {
        // Vérifier la licence premium
        $is_premium = PDF_Builder_API_Helper::is_premium();
        if (!$is_premium) {
            PDF_Builder_API_Helper::log_premium_attempt('generate_pdf_advanced');
            $license_error = PDF_Builder_API_Helper::check_premium_license(__('Génération PDF avancée', 'pdf-builder-pro'));
            return $license_error;
        }

        PDF_Builder_API_Helper::log_premium_attempt('generate_pdf_advanced');

        // Récupérer les paramètres
        $template_id = $request->get_param('template_id');
        $data = $request->get_param('data');

        if (empty($template_id)) {
            return new \WP_Error('missing_template', __('L\'ID de template est requis.', 'pdf-builder-pro'), array('status' => 400));
        }

        // TODO: Implémentation de la génération PDF
        return rest_ensure_response(array(
            'status' => 'success',
            'message' => __('Génération PDF lancée.', 'pdf-builder-pro'),
            'template_id' => $template_id,
        ));
    }

    /**
     * Endpoint création webhooks (premium)
     */
    public function create_webhook(\WP_REST_Request $request) {
        // Vérifier la licence premium
        $is_premium = PDF_Builder_API_Helper::is_premium();
        if (!$is_premium) {
            PDF_Builder_API_Helper::log_premium_attempt('create_webhook');
            $license_error = PDF_Builder_API_Helper::check_premium_license(__('Webhooks', 'pdf-builder-pro'));
            return $license_error;
        }

        PDF_Builder_API_Helper::log_premium_attempt('create_webhook');

        $url = $request->get_param('url');
        $events = $request->get_param('events');

        if (empty($url) || empty($events)) {
            return new \WP_Error('missing_params', __('URL et événements sont requis.', 'pdf-builder-pro'), array('status' => 400));
        }

        // TODO: Implémentation webhooks
        return rest_ensure_response(array(
            'status' => 'success',
            'message' => __('Webhook créé.', 'pdf-builder-pro'),
            'webhook_id' => uniqid('webhook_'),
        ));
    }

    /**
     * Endpoint info licence
     */
    public function get_license_info(\WP_REST_Request $request) {
        return rest_ensure_response(PDF_Builder_API_Helper::get_license_info());
    }
}
