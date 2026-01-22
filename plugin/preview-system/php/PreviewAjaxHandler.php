<?php

/**
 * PDF Builder Pro - Aperçu AJAX Handler
 * Gère les requêtes AJAX pour la génération d'aperçus PDF
 */

namespace PDF_Builder\PreviewSystem;

use PDF_Builder\Generators\GeneratorManager;
use PDF_Builder\Generators\BaseGenerator;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PreviewAjaxHandler {
    
    /**
     * Initialise les hooks AJAX
     */
    public static function init() {
        add_action('wp_ajax_pdf_builder_generate_preview', [self::class, 'generatePreviewAjax']);
        add_action('wp_ajax_nopriv_pdf_builder_generate_preview', [self::class, 'generatePreviewAjax']);
    }

    /**
     * Handler AJAX pour la génération d'aperçu
     */
    public static function generatePreviewAjax() {
        try {
            error_log('[PREVIEW AJAX] ===== NOUVEAU APPEL AJAX =====');
            error_log('[PREVIEW AJAX] Utilisateur courant: ' . get_current_user_id());
            error_log('[PREVIEW AJAX] User peut manage_options? ' . (current_user_can('manage_options') ? 'OUI' : 'NON'));
            
            // Vérifier la permission
            if (!current_user_can('manage_options')) {
                error_log('[PREVIEW AJAX] ERREUR: Permissions insuffisantes pour user ' . get_current_user_id());
                wp_send_json_error('Permissions insuffisantes', 403);
            }

            // Vérifier le nonce
            $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
            error_log('[PREVIEW AJAX] Nonce reçu: ' . (empty($nonce) ? 'VIDE' : substr($nonce, 0, 20) . '...'));
            
            if (!wp_verify_nonce($nonce, 'pdf_builder_nonce')) {
                error_log('[PREVIEW AJAX] ERREUR: Nonce invalide');
                wp_send_json_error('Nonce invalide', 403);
            }

            error_log('[PREVIEW AJAX] ✓ Nonce valide');

            // Récupérer les données
            $template_data_json = isset($_POST['template_data']) ? sanitize_text_field($_POST['template_data']) : '{}';
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
            $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'pdf';  // Focus PDF
            $quality = isset($_POST['quality']) ? intval($_POST['quality']) : 150;

            error_log('[PREVIEW AJAX] Template ID: ' . ($template_id ? $template_id : 'NON FOURNI'));
            error_log('[PREVIEW AJAX] Format: ' . $format . ', Quality: ' . $quality);

            // Parser JSON du frontend
            $template_data = json_decode($template_data_json, true);

            // Si template_id est fourni, récupérer depuis la base de données
            if ($template_id && $template_id > 0) {
                error_log('[PREVIEW AJAX] Récupération du template depuis la BD - ID: ' . $template_id);
                $db_template_data = self::loadTemplateFromDatabase($template_id);
                
                if ($db_template_data) {
                    $template_data = $db_template_data;
                    error_log('[PREVIEW AJAX] ✓ Template chargé depuis la BD');
                } else {
                    error_log('[PREVIEW AJAX] Template non trouvé dans la BD, utilisation des données du frontend');
                }
            }

            if (!is_array($template_data)) {
                wp_send_json_error('Données du template invalides');
            }

            error_log('[PREVIEW AJAX] Requête reçue - Format: ' . $format . ', Quality: ' . $quality);
            error_log('[PREVIEW AJAX] Template data keys: ' . implode(', ', array_keys($template_data)));

            // Générer l'aperçu
            $result = self::generatePreview($template_data, $format, $quality);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            wp_send_json_success($result);

        } catch (\Exception $e) {
            error_log('[PREVIEW AJAX ERROR] ' . $e->getMessage());
            wp_send_json_error('Erreur serveur: ' . $e->getMessage());
        }
    }

    /**
     * Charge les données du template depuis la base de données
     * Utilise uniquement la colonne template_data de la table wp_pdf_builder_templates
     */
    private static function loadTemplateFromDatabase(int $template_id): ?array {
        try {
            error_log('[PREVIEW DB] === Début du chargement du template ID: ' . $template_id . ' ===');
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'pdf_builder_templates';
            
            // Récupérer le template depuis la table
            $template = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id, name, template_data FROM {$table_name} WHERE id = %d",
                    $template_id
                ),
                ARRAY_A
            );
            
            if (!$template) {
                error_log('[PREVIEW DB] ❌ Template non trouvé pour ID: ' . $template_id);
                return null;
            }

            error_log('[PREVIEW DB] Template trouvé - Nom: ' . $template['name']);
            
            if (empty($template['template_data'])) {
                error_log('[PREVIEW DB] ❌ Colonne template_data vide');
                return null;
            }

            error_log('[PREVIEW DB] Taille template_data: ' . strlen($template['template_data']) . ' bytes');

            // Décoder le JSON
            $template_data = json_decode($template['template_data'], true);
            if ($template_data === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log('[PREVIEW DB] ❌ Erreur de décodage JSON: ' . json_last_error_msg());
                return null;
            }

            error_log('[PREVIEW DB] ✓ JSON décodé - Clés: ' . implode(', ', array_keys($template_data)));
            
            // Ajouter les infos du template
            $template_data['template_id'] = $template_id;
            $template_data['template_name'] = $template['name'];

            error_log('[PREVIEW DB] ✓ Template chargé avec succès');
            return $template_data;

        } catch (\Exception $e) {
            error_log('[PREVIEW DB ERROR] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Génère l'aperçu simple
     */
    private static function generatePreview(array $template_data, string $format = 'png', int $quality = 150) {
        try {
            error_log('[PREVIEW] Début de la génération - Format: ' . $format);

            // Créer une image simple avec les données du template
            $image = imagecreatetruecolor(800, 600);
            $bg_color = imagecolorallocate($image, 240, 240, 240);
            $text_color = imagecolorallocate($image, 50, 50, 50);
            $header_color = imagecolorallocate($image, 52, 152, 219);

            imagefill($image, 0, 0, $bg_color);
            
            // En-tête
            imagefilledrectangle($image, 0, 0, 800, 60, $header_color);
            imagestring($image, 5, 20, 20, 'PDF Preview - ' . ($template_data['template_name'] ?? 'Template'), imagecolorallocate($image, 255, 255, 255));
            
            // Contenu
            imagestring($image, 3, 20, 80, 'Template ID: ' . ($template_data['template_id'] ?? 'N/A'), $text_color);
            imagestring($image, 3, 20, 100, 'Format: ' . $format, $text_color);
            imagestring($image, 3, 20, 120, 'Qualité: ' . $quality, $text_color);
            
            $y = 160;
            if (isset($template_data['elements']) && is_array($template_data['elements'])) {
                imagestring($image, 3, 20, $y, 'Éléments: ' . count($template_data['elements']), $text_color);
                $y += 20;
            }
            
            // Dimensions canvas
            if (isset($template_data['canvasWidth']) && isset($template_data['canvasHeight'])) {
                imagestring($image, 3, 20, $y, 'Canvas: ' . $template_data['canvasWidth'] . 'x' . $template_data['canvasHeight'], $text_color);
                $y += 20;
            }
            
            // Footer
            imagefilledrectangle($image, 0, 570, 800, 600, imagecolorallocate($image, 200, 200, 200));
            imagestring($image, 3, 20, 577, 'Généré par PDF Builder Pro - ' . date('Y-m-d H:i:s'), $text_color);

            // Exporter en image
            ob_start();
            if ($format === 'jpg') {
                imagejpeg($image, null, $quality);
            } else {
                imagepng($image);
            }
            $image_data = ob_get_clean();
            imagedestroy($image);

            error_log('[PREVIEW] Image générée avec succès - ' . strlen($image_data) . ' bytes');

            return [
                'image_url' => 'data:image/' . ($format === 'jpg' ? 'jpeg' : 'png') . ';base64,' . base64_encode($image_data),
                'format' => $format,
                'success' => true,
                'fallback' => false,
                'generator' => 'simple-image'
            ];

        } catch (\Exception $e) {
            error_log('[PREVIEW ERROR] ' . $e->getMessage());
            // Fallback à une image vide
            return [
                'image_url' => self::generatePlaceholderImage($format),
                'format' => $format,
                'fallback' => true,
                'success' => true,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtient le data provider
     */
    private static function getDataProvider() {
        // Utiliser SampleDataProvider par défaut
        $provider_class = 'PDF_Builder\\Data\\SampleDataProvider';
        
        if (class_exists($provider_class)) {
            return new $provider_class();
        }

        // Fallback simple
        return new class implements \PDF_Builder\Interfaces\DataProviderInterface {
            public function getData($key = null) {
                return [];
            }
        };
    }

    /**
     * Sauvegarde un PDF temporairement et retourne une URL accessible
     */
    private static function savePdfTemporary(string $pdf_content): string {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }

        // Générer un nom de fichier unique
        $filename = 'preview-' . uniqid() . '.pdf';
        $filepath = $temp_dir . '/' . $filename;

        // Sauvegarder le PDF
        file_put_contents($filepath, $pdf_content);

        // Retourner l'URL accessible
        return $upload_dir['baseurl'] . '/pdf-builder-temp/' . $filename;
    }

    /**
     * Génère une image placeholder
     */
    private static function generatePlaceholderImage(string $format = 'png'): string {
        $image = imagecreatetruecolor(800, 600);
        
        $bg_color = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 100, 100, 100);

        imagefill($image, 0, 0, $bg_color);
        imagestring($image, 5, 50, 50, 'PDF Preview Placeholder', $text_color);

        ob_start();
        if ($format === 'jpg') {
            imagejpeg($image);
        } else {
            imagepng($image);
        }
        $image_data = ob_get_clean();
        imagedestroy($image);

        return 'data:image/' . ($format === 'jpg' ? 'jpeg' : 'png') . ';base64,' . base64_encode($image_data);
    }
}
