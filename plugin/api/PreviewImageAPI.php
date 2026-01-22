<?php

namespace PDF_Builder\Api;

use PDF_Builder\Generators\GeneratorManager;
use PDF_Builder\Data\EditorDataProvider;

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Preview Image API - Gestion des aperçus
 * 
 * NOTES:
 * - La génération AJAX est gérée par SimplePreviewGenerator
 * - Cette classe gère uniquement les routes REST pour compatibilité
 * - Pour la génération d'aperçus, utiliser SimplePreviewGenerator
 */
class PreviewImageAPI
{
    private $generator_manager;
    private static $cache_dir;

    public function __construct()
    {
        $this->generator_manager = new GeneratorManager();
        
        // Initialiser le cache
        $upload_dir = wp_upload_dir();
        self::$cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/previews';
        
        if (!is_dir(self::$cache_dir)) {
            wp_mkdir_p(self::$cache_dir);
        }

        // Enregistrer les routes REST
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    /**
     * Enregistrer les routes REST API
     */
    public function register_rest_routes()
    {
        // Route pour l'aperçu REST
        register_rest_route('wp-pdf-builder-pro/v1', '/preview', array(
            'methods' => 'POST',
            'callback' => array($this, 'handleRestPreview'),
            'permission_callback' => array($this, 'checkRestPermissions'),
            'args' => array(
                'context' => array(
                    'required' => true,
                    'type' => 'string',
                    'enum' => array('editor', 'metabox'),
                    'description' => 'Contexte d\'utilisation'
                ),
                'templateData' => array(
                    'required' => true,
                    'type' => 'object',
                    'description' => 'Données du template'
                ),
                'quality' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 150,
                    'description' => 'Qualité (50-300)'
                )
            )
        ));

        // Route pour le download
        register_rest_route('wp-pdf-builder-pro/v1', '/download', array(
            'methods' => 'POST',
            'callback' => array($this, 'handleDownload'),
            'permission_callback' => array($this, 'checkRestPermissions'),
            'args' => array(
                'templateData' => array(
                    'required' => true,
                    'type' => 'object'
                ),
                'format' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('pdf', 'png', 'jpg'),
                    'default' => 'pdf'
                )
            )
        ));
    }

    /**
     * Vérifier les permissions REST
     */
    public function checkRestPermissions($request)
    {
        $context = $request->getParam('context') ?? 'editor';
        
        switch ($context) {
            case 'editor':
                return current_user_can('manage_options');
            case 'metabox':
                return current_user_can('edit_shop_orders');
            default:
                return false;
        }
    }

    /**
     * Handler REST pour aperçu
     */
    public function handleRestPreview($request)
    {
        try {
            $template_data = $request->getParam('templateData');
            $quality = intval($request->getParam('quality') ?? 150);
            
            if (!$template_data) {
                return new \WP_Error(
                    'missing_template',
                    'Template data is required',
                    ['status' => 400]
                );
            }

            // Générer l'aperçu
            $result = $this->generatePreviewImage($template_data, $quality);

            return new \WP_REST_Response([
                'success' => true,
                'url' => $result['url']
            ], 200);

        } catch (\Exception $e) {
            return new \WP_Error(
                'preview_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Handler REST pour téléchargement
     */
    public function handleDownload($request)
    {
        try {
            $template_data = $request->getParam('templateData');
            $format = $request->getParam('format') ?? 'pdf';

            if (!$template_data) {
                return new \WP_Error(
                    'missing_template',
                    'Template data is required',
                    ['status' => 400]
                );
            }

            // Générer le fichier
            $result = $this->generator_manager->generatePreview(
                $template_data,
                new EditorDataProvider($template_data),
                $format,
                ['quality' => 150]
            );

            if (empty($result['file']) || !file_exists($result['file'])) {
                throw new \Exception('Generation failed');
            }

            // Préparer le download
            $filename = 'document-' . time() . '.' . $format;
            
            return new \WP_REST_Response([
                'success' => true,
                'filename' => $filename,
                'file' => base64_encode(file_get_contents($result['file']))
            ], 200);

        } catch (\Exception $e) {
            return new \WP_Error(
                'download_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Générer une image d'aperçu
     */
    private function generatePreviewImage($template_data, $quality = 150)
    {
        // Créer une clé de cache
        $cache_key = md5(json_encode($template_data) . $quality);
        $cache_file = self::$cache_dir . '/' . $cache_key . '.png';
        
        // Utiliser le cache s'il existe
        if (file_exists($cache_file)) {
            $age = time() - filemtime($cache_file);
            if ($age < 86400) { // 24h
                $upload_dir = wp_upload_dir();
                return [
                    'url' => $upload_dir['baseurl'] . '/pdf-builder-cache/previews/' . $cache_key . '.png'
                ];
            }
        }

        // Générer
        $result = $this->generator_manager->generatePreview(
            $template_data,
            new EditorDataProvider($template_data),
            'png',
            ['quality' => $quality, 'max_width' => 1024, 'max_height' => 1024]
        );

        if (!empty($result['file']) && file_exists($result['file'])) {
            copy($result['file'], $cache_file);
            $upload_dir = wp_upload_dir();
            return [
                'url' => $upload_dir['baseurl'] . '/pdf-builder-cache/previews/' . $cache_key . '.png'
            ];
        }

        throw new \Exception('Failed to generate preview image');
    }

    /**
     * Nettoyer le cache des aperçus
     */
    public static function cleanupCache()
    {
        if (!is_dir(self::$cache_dir)) {
            return;
        }

        $files = glob(self::$cache_dir . '/*.png');
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