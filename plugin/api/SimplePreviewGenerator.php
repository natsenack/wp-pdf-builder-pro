<?php
/**
 * PDF Builder Pro - Simple Preview Generator
 * Version: Zero de zéro - Approche minimale et fonctionnelle
 * 
 * Point d'entrée unique pour tous les aperçus
 * - Reçoit template_data en JSON
 * - Valide la structure
 * - Génère image PNG via GeneratorManager
 * - Retourne URL
 */

namespace PDF_Builder\Api;

use PDF_Builder\Generators\GeneratorManager;
use PDF_Builder\Config\Data\EditorDataProvider;

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

class SimplePreviewGenerator
{
    private $generator_manager;
    private $cache_dir;
    private $cache_url;

    public function __construct()
    {
        $this->generator_manager = new GeneratorManager();
        
        // Dossier de cache
        $upload_dir = wp_upload_dir();
        $this->cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/previews';
        $this->cache_url = $upload_dir['baseurl'] . '/pdf-builder-cache/previews';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }
    }

    /**
     * Point d'entrée AJAX unique
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
            $nonce = $_POST['_wpnonce'] ?? $_POST['nonce'] ?? '';
            if (!wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
                throw new \Exception('Invalid nonce', 401);
            }

            // 3. Récupérer et valider template_data
            $template_data = $this->getTemplateData();
            if (empty($template_data)) {
                throw new \Exception('No template data provided', 400);
            }

            // 4. Normaliser la structure
            $normalized = $this->normalizeTemplateData($template_data);

            // 5. Générer l'aperçu avec cache
            $result = $this->generatePreview($normalized);

            // 6. Répondre avec l'URL
            wp_send_json_success([
                'image_url' => $result['url'],
                'cache_key' => $result['cache_key'],
                'timestamp' => current_time('mysql')
            ]);

        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    /**
     * Récupérer template_data de plusieurs sources possibles
     */
    private function getTemplateData()
    {
        // Priority: template_id > template_data POST
        if (!empty($_POST['template_id'])) {
            $template_id = intval($_POST['template_id']);
            $template = get_post($template_id);
            
            if (!$template) {
                throw new \Exception("Template ID {$template_id} not found", 404);
            }
            
            $template_json = get_post_meta($template_id, '_pdf_builder_template', true);
            if (empty($template_json)) {
                throw new \Exception("Template ID {$template_id} has no data", 404);
            }
            
            return json_decode($template_json, true);
        }

        // Fallback: template_data direct
        if (!empty($_POST['template_data'])) {
            return json_decode(stripslashes($_POST['template_data']), true);
        }

        return null;
    }

    /**
     * Normaliser template_data pour qu'il ait toujours .elements
     */
    private function normalizeTemplateData($data)
    {
        if (isset($data['template']['elements'])) {
            // Format: {template: {elements: [...]}}
            return $data;
        } elseif (isset($data['elements'])) {
            // Format: {elements: [...]}
            return [
                'template' => $data
            ];
        } else {
            throw new \Exception('Invalid template structure: no elements found', 400);
        }
    }

    /**
     * Générer l'aperçu avec cache simple
     */
    private function generatePreview($template_data)
    {
        // Clé de cache basée sur le hash du template
        $cache_key = md5(json_encode($template_data));
        $cache_file = $this->cache_dir . '/' . $cache_key . '.png';
        $cache_url = $this->cache_url . '/' . $cache_key . '.png';

        // Utiliser le cache s'il existe et est encore valide (< 24h)
        if (file_exists($cache_file)) {
            $file_age = time() - filemtime($cache_file);
            if ($file_age < 86400) {
                return [
                    'url' => $cache_url,
                    'cache_key' => $cache_key,
                    'from_cache' => true
                ];
            }
        }

        // Générer l'image avec EditorDataProvider (données réelles)
        $data_provider = new EditorDataProvider($template_data);
        
        $result = $this->generator_manager->generatePreview(
            $template_data['template'] ?? $template_data,
            $data_provider,
            'png',
            [
                'quality' => intval($_POST['quality'] ?? 150),
                'max_width' => 1024,
                'max_height' => 1024
            ]
        );

        // Sauvegarder le résultat
        if (!empty($result['file'])) {
            if (copy($result['file'], $cache_file)) {
                return [
                    'url' => $cache_url,
                    'cache_key' => $cache_key,
                    'from_cache' => false
                ];
            }
        }

        throw new \Exception('Failed to generate preview image', 500);
    }
}

// === ENREGISTREMENT DE L'ACTION AJAX ===
if (!function_exists('pdf_builder_simple_preview_init')) {
    function pdf_builder_simple_preview_init()
    {
        // Enregistrer les actions AJAX
        add_action('wp_ajax_pdf_builder_generate_preview', function() {
            $generator = new SimplePreviewGenerator();
            $generator->handle();
            exit;
        });

        add_action('wp_ajax_nopriv_pdf_builder_generate_preview', function() {
            // Rejecter les non-authentifiés
            http_response_code(401);
            wp_send_json_error('Authentication required');
            exit;
        });
    }
    add_action('init', 'pdf_builder_simple_preview_init');
}
?>
