<?php
/**
 * PDF Builder Pro - Preview System Initialization
 * 
 * Point d'entrée simplifié et fiable pour tous les aperçus
 * Enregistre les actions AJAX et les routes REST
 */

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Classe d'initialisation du système d'aperçu
 * S'enregistre sur les hooks WordPress standard
 */
class PreviewSystem
{
    private static $initialized = false;

    /**
     * Initialiser le système
     */
    public static function init()
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        // Enregistrer les actions AJAX
        self::registerAjaxActions();
        
        // Enregistrer les routes REST
        add_action('rest_api_init', [__CLASS__, 'registerRestRoutes']);
    }

    /**
     * Enregistrer les actions AJAX
     */
    private static function registerAjaxActions()
    {
        // Action AJAX authentifiée
        add_action('wp_ajax_pdf_builder_generate_preview', function () {
            self::handleAjaxPreviewGeneration();
        });

        // Action AJAX non authentifiée (refusée)
        add_action('wp_ajax_nopriv_pdf_builder_generate_preview', function () {
            http_response_code(401);
            wp_send_json_error('Authentication required', 401);
        });
    }

    /**
     * Handler AJAX pour la génération d'aperçu
     */
    private static function handleAjaxPreviewGeneration()
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            // 1. Vérifier les permissions
            if (!current_user_can('manage_options')) {
                throw new \Exception('Permission denied', 403);
            }

            // 2. Vérifier le nonce
            $nonce = $_POST['nonce'] ?? $_POST['_wpnonce'] ?? '';
            if (!wp_verify_nonce($nonce, 'pdf_builder_nonce')) {
                throw new \Exception('Invalid security token', 401);
            }

            // 3. Extraire les données
            $template_data = self::extractTemplateData();

            // 4. Générer l'aperçu
            $result = self::generatePreview($template_data);

            // 5. Répondre
            wp_send_json_success([
                'url' => $result['url'],
                'cached' => $result['cached'] ?? false
            ]);

        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Extraire template_data du POST
     */
    private static function extractTemplateData()
    {
        $raw = $_POST['template_data'] ?? '';
        
        if (empty($raw)) {
            throw new \Exception('No template data provided', 400);
        }

        $decoded = json_decode(stripslashes($raw), true);
        
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in template data', 400);
        }

        // Vérifier qu'il y a des éléments
        $elements = $decoded['elements'] ?? $decoded['template']['elements'] ?? null;
        if (!$elements || !is_array($elements)) {
            throw new \Exception('No elements found in template', 400);
        }

        return $decoded;
    }

    /**
     * Générer l'aperçu avec cache
     */
    private static function generatePreview($template_data)
    {
        // Initialiser le cache
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/previews';
        $cache_url = $upload_dir['baseurl'] . '/pdf-builder-cache/previews';

        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }

        // Créer clé de cache
        $cache_key = md5(json_encode($template_data) . intval($_POST['quality'] ?? 150));
        $cache_file = $cache_dir . '/' . $cache_key . '.png';
        $cache_url_full = $cache_url . '/' . $cache_key . '.png';

        // Vérifier le cache existant
        if (file_exists($cache_file)) {
            $age = time() - filemtime($cache_file);
            if ($age < 86400) { // 24 heures
                return [
                    'url' => $cache_url_full,
                    'cached' => true
                ];
            }
            @unlink($cache_file);
        }

        // Générer l'image
        try {
            $generator_manager = new \PDF_Builder\Generators\GeneratorManager();
            $data_provider = new \PDF_Builder\Data\EditorDataProvider($template_data);
            
            $result = $generator_manager->generatePreview(
                $template_data,
                $data_provider,
                'png',
                [
                    'quality' => intval($_POST['quality'] ?? 150),
                    'max_width' => 1024,
                    'max_height' => 1024
                ]
            );

            if (empty($result['file']) || !file_exists($result['file'])) {
                throw new \Exception('Generation failed - no file produced');
            }

            // Copier au cache
            if (!copy($result['file'], $cache_file)) {
                @unlink($result['file']);
                throw new \Exception('Failed to cache preview');
            }

            @unlink($result['file']);

            return [
                'url' => $cache_url_full,
                'cached' => false
            ];

        } catch (\Exception $e) {
            throw new \Exception('Preview generation error: ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer les routes REST
     */
    public static function registerRestRoutes()
    {
        register_rest_route('wp-pdf-builder-pro/v1', '/preview', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handleRestPreview'],
            'permission_callback' => [__CLASS__, 'checkRestPermissions'],
        ]);

        register_rest_route('wp-pdf-builder-pro/v1', '/download', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handleRestDownload'],
            'permission_callback' => [__CLASS__, 'checkRestPermissions'],
        ]);
    }

    /**
     * Vérifier les permissions REST
     */
    public static function checkRestPermissions($request)
    {
        return current_user_can('manage_options');
    }

    /**
     * Handler REST pour aperçu
     */
    public static function handleRestPreview($request)
    {
        try {
            $template_data = $request->get_json_params();
            if (empty($template_data)) {
                return new \WP_Error('invalid_data', 'No template data', ['status' => 400]);
            }

            $result = self::generatePreview($template_data);
            return new \WP_REST_Response(['url' => $result['url']], 200);
        } catch (\Exception $e) {
            return new \WP_Error('generation_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Handler REST pour download
     */
    public static function handleRestDownload($request)
    {
        return new \WP_REST_Response(['message' => 'Download not implemented'], 501);
    }

    /**
     * Nettoyer le cache
     */
    public static function cleanupCache()
    {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/previews';

        if (!is_dir($cache_dir)) {
            return;
        }

        $files = glob($cache_dir . '/*.png');
        if (!$files) {
            return;
        }

        $now = time();
        $max_age = 86400 * 7; // 7 jours

        foreach ($files as $file) {
            if ($now - filemtime($file) > $max_age) {
                @unlink($file);
            }
        }
    }
}

// === Initialisation automatique ===
// S'initialise dès que ce fichier est chargé (pas de dépendance sur les hooks)
if (function_exists('add_action')) {
    PreviewSystem::init();
}
