<?php
/**
 * PDF Builder Pro - Preview System (Ultra-Minimal)
 * 
 * Juste l'essentiel :
 * - Hook AJAX simple
 * - Pas de génération
 * - Pas de cache
 * - Zéro complexité
 */

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit;
}

class PreviewSystem
{
    public static function boot()
    {
        add_action('wp_ajax_pdf_preview', [__CLASS__, 'handle']);
        add_action('wp_ajax_nopriv_pdf_preview', [__CLASS__, 'deny']);
    }

    public static function handle()
    {
        header('Content-Type: application/json');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied', 403);
        }

        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_nonce')) {
            wp_send_json_error('Invalid nonce', 401);
        }

        wp_send_json_success(['message' => 'Preview ready']);
    }

    public static function deny()
    {
        wp_send_json_error('Authentication required', 401);
    }
}

// Démarrer
if (function_exists('add_action')) {
    PreviewSystem::boot();
}
