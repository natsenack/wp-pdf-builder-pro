<?php
/**
 * PDF Builder Pro - Simple Preview Generator (v2 - Cleaned)
 * 
 * Point d'entrée unique pour la génération d'aperçus
 * - Reçoit template_data en JSON
 * - Valide la structure basique
 * - Génère image PNG via GeneratorManager
 * - Retourne URL avec cache
 */

namespace PDF_Builder\Api;

use PDF_Builder\Generators\GeneratorManager;
use PDF_Builder\Data\EditorDataProvider;

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

class SimplePreviewGenerator
{
    private $generator_manager;
    private $cache_dir;
    private $cache_url;
    private const CACHE_DURATION = 86400; // 24 heures

    public function __construct()
    {
        $this->generator_manager = new GeneratorManager();
        
        // Initialiser les chemins de cache
        $this->initCachePaths();
    }

    /**
     * Initialiser les chemins du cache
     */
    private function initCachePaths()
    {
        $upload_dir = wp_upload_dir();
        $this->cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/previews';
        $this->cache_url = $upload_dir['baseurl'] . '/pdf-builder-cache/previews';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }
    }

    /**
     * Point d'entrée AJAX principal
     */
    public function handle()
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
                throw new \Exception('Invalid nonce', 401);
            }

            // 3. Récupérer et valider template_data
            $template_data = $this->extractTemplateData();

            // 4. Générer l'aperçu
            $result = $this->generatePreview($template_data);

            // 5. Répondre avec succès
            wp_send_json_success([
                'url' => $result['url'],
                'cached' => $result['cached'] ?? false
            ]);

        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Extraire template_data du POST
     */
    private function extractTemplateData()
    {
        $raw = $_POST['template_data'] ?? '';
        
        if (empty($raw)) {
            throw new \Exception('No template data provided', 400);
        }

        $data = json_decode(stripslashes($raw), true);
        
        if (!is_array($data)) {
            throw new \Exception('Invalid JSON in template_data', 400);
        }

        // Vérifier qu'il y a des éléments
        if (!isset($data['elements']) && !isset($data['template']['elements'])) {
            throw new \Exception('No elements found in template', 400);
        }

        return $data;
    }

    /**
     * Générer l'aperçu avec cache
     */
    private function generatePreview($template_data)
    {
        // Créer une clé de cache
        $cache_key = $this->generateCacheKey($template_data);
        $cache_file = $this->cache_dir . '/' . $cache_key . '.png';
        $cache_url = $this->cache_url . '/' . $cache_key . '.png';

        // Vérifier le cache existant
        if (file_exists($cache_file)) {
            $age = time() - filemtime($cache_file);
            if ($age < self::CACHE_DURATION) {
                return [
                    'url' => $cache_url,
                    'cached' => true
                ];
            }
            // Supprimer le fichier expiré
            @unlink($cache_file);
        }

        // Générer la nouvelle image
        $temp_file = $this->generateImageFile($template_data);
        
        if (!file_exists($temp_file)) {
            throw new \Exception('Failed to generate preview image', 500);
        }

        // Copier au cache
        if (!copy($temp_file, $cache_file)) {
            @unlink($temp_file);
            throw new \Exception('Failed to cache preview image', 500);
        }

        @unlink($temp_file);

        return [
            'url' => $cache_url,
            'cached' => false
        ];
    }

    /**
     * Générer le fichier image
     */
    private function generateImageFile($template_data)
    {
        // Normaliser les données
        $elements = $template_data['elements'] ?? $template_data['template']['elements'] ?? [];
        
        if (empty($elements)) {
            throw new \Exception('No elements to render', 400);
        }

        // Utiliser le GeneratorManager
        $result = $this->generator_manager->generatePreview(
            ['elements' => $elements],
            new EditorDataProvider($template_data),
            'png',
            [
                'quality' => intval($_POST['quality'] ?? 150),
                'max_width' => 1024,
                'max_height' => 1024
            ]
        );

        if (empty($result['file']) || !file_exists($result['file'])) {
            throw new \Exception('Generator returned no file', 500);
        }

        return $result['file'];
    }

    /**
     * Générer une clé de cache unique
     */
    private function generateCacheKey($data)
    {
        $elements = $data['elements'] ?? $data['template']['elements'] ?? [];
        $quality = intval($_POST['quality'] ?? 150);
        
        return md5(json_encode($elements) . $quality);
    }
}

// === ENREGISTREMENT AJAX - Appels directs ===
// Enregistrer les actions AJAX directement sans fonction wrapper
add_action('wp_ajax_pdf_builder_generate_preview', function() {
    $generator = new SimplePreviewGenerator();
    $generator->handle();
    wp_die();
});

add_action('wp_ajax_nopriv_pdf_builder_generate_preview', function() {
    http_response_code(401);
    wp_send_json_error('Authentication required');
    wp_die();
});
