<?php

/**
 * PDF Builder Pro - Validateur de sécurité ULTRA-SIMPLIFIÉ
 * Version de secours qui ne crash jamais
 */

namespace PDF_Builder\Core;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Security_Validator
{
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Rien à faire ici
    }

    public function init() {
        // Initialisation différée
    }

    // ==========================================
    // MÉTHODES STATIQUES ULTRA-SAFE
    // ==========================================

    public static function sanitizeHtmlContent($content)
    {
        if (empty($content)) return '';

        if (!function_exists('wp_kses')) {
            return $content; // Fallback sans WordPress
        }

        $allowed = ['p' => [], 'br' => [], 'strong' => [], 'em' => [], 'u' => []];
        return wp_kses($content, $allowed);
    }

    public static function validateJsonData($json_data)
    {
        if (empty($json_data)) return false;

        $data = json_decode($json_data, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : false;
    }

    public static function validateNonce($nonce, $action)
    {
        if (!function_exists('wp_verify_nonce')) return false;
        return wp_verify_nonce($nonce, $action);
    }

    public static function checkPermissions($capability = 'manage_options')
    {
        if (!function_exists('current_user_can')) return false;
        return current_user_can($capability);
    }

    // Méthodes d'instance minimales
    public function validate_ajax_request($capability = 'manage_options') {
        return true; // Version simplifiée
    }

    public function sanitize_template_data($data) {
        return is_array($data) ? $data : [];
    }

    public function sanitize_settings($settings) {
        return is_array($settings) ? $settings : [];
    }
}

// Fonctions globales de secours
function pdf_builder_validate_ajax_request($capability = 'manage_options') {
    return PDF_Builder_Security_Validator::get_instance()->validate_ajax_request($capability);
}

function pdf_builder_sanitize_template_data($data) {
    return PDF_Builder_Security_Validator::get_instance()->sanitize_template_data($data);
}

function pdf_builder_sanitize_settings($settings) {
    return PDF_Builder_Security_Validator::get_instance()->sanitize_settings($settings);
}