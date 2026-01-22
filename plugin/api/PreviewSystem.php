<?php
/**
 * PDF Builder Pro - Preview System (Minimal & Clean v2)
 * 
 * Architecture ultra-simple :
 * - Aucune génération au démarrage
 * - Génération à la demande AJAX uniquement
 * - Cache fichier simple
 * - Zéro dépendance complexe
 */

namespace PDF_Builder\Api;

if (!defined('ABSPATH')) {
    exit;
}

class PreviewSystem
{
    private static $init = false;
    private static $cache_dir;

    /**
     * Initialiser une seule fois
     */
    public static function boot()
    {
        if (self::$init) {
            return;
        }
        self::$init = true;

        // S'enregistrer sur les hooks WordPress
        add_action('wp_ajax_pdf_preview', [__CLASS__, 'handleAjax']);
        add_action('wp_ajax_nopriv_pdf_preview', [__CLASS__, 'denyAccess']);
    }

    /**
     * Handler AJAX principal
     */
    public static function handleAjax()
    {
        header('Content-Type: application/json');

        try {
            // 1. Permissions
            if (!current_user_can('manage_options')) {
                throw new \Exception('Access denied', 403);
            }

            // 2. Nonce
            if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_nonce')) {
                throw new \Exception('Invalid nonce', 401);
            }

            // 3. Données
            $data = json_decode(stripslashes($_POST['data'] ?? ''), true);
            if (!$data || !isset($data['elements']) || !is_array($data['elements'])) {
                throw new \Exception('Invalid template data', 400);
            }

            // 4. Générer
            $url = self::generatePreview($data);

            wp_send_json_success(['url' => $url]);

        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Refuser l'accès non-authentifié
     */
    public static function denyAccess()
    {
        http_response_code(401);
        wp_send_json_error('Authentication required');
    }

    /**
     * Générer l'aperçu (cœur du système)
     */
    private static function generatePreview($template_data)
    {
        // Initialiser cache
        $cache_dir = self::getCacheDir();
        $cache_key = md5(json_encode($template_data));
        $cache_file = $cache_dir . '/' . $cache_key . '.png';

        // Vérifier cache existant
        if (file_exists($cache_file)) {
            $age = time() - filemtime($cache_file);
            if ($age < 86400) { // 24h
                return self::getCacheUrl() . '/' . $cache_key . '.png';
            }
            unlink($cache_file);
        }

        // Générer nouvelle image
        try {
            $generator = new \PDF_Builder\Generators\GeneratorManager();
            $provider = new \PDF_Builder\Data\EditorDataProvider($template_data);

            $result = $generator->generatePreview(
                $template_data,
                $provider,
                'png',
                [
                    'quality' => intval($_POST['quality'] ?? 150),
                    'max_width' => 1024,
                    'max_height' => 1024
                ]
            );

            if (empty($result['file']) || !file_exists($result['file'])) {
                throw new \Exception('Generation failed');
            }

            // Copier au cache
            copy($result['file'], $cache_file);
            @unlink($result['file']);

            return self::getCacheUrl() . '/' . $cache_key . '.png';

        } catch (\Exception $e) {
            throw new \Exception('Preview generation error: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir le chemin du cache
     */
    private static function getCacheDir()
    {
        if (!self::$cache_dir) {
            $upload_dir = wp_upload_dir();
            self::$cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/previews';
            if (!is_dir(self::$cache_dir)) {
                wp_mkdir_p(self::$cache_dir);
            }
        }
        return self::$cache_dir;
    }

    /**
     * Obtenir l'URL du cache
     */
    private static function getCacheUrl()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/pdf-builder-cache/previews';
    }

    /**
     * Nettoyer le cache
     */
    public static function cleanCache()
    {
        $dir = self::getCacheDir();
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*.png');
        if (!$files) {
            return;
        }

        $now = time();
        $max_age = 7 * 86400; // 7 jours

        foreach ($files as $file) {
            if ($now - filemtime($file) > $max_age) {
                @unlink($file);
            }
        }
    }
}

// ============================================================================
// DÉMARRAGE AUTOMATIQUE
// ============================================================================

// S'initialiser dès le chargement
if (function_exists('add_action')) {
    PreviewSystem::boot();
}
