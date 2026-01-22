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
            // Vérifier la permission
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes', 403);
            }

            // Vérifier le nonce
            $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
            if (!wp_verify_nonce($nonce, 'pdf_builder_nonce')) {
                wp_send_json_error('Nonce invalide', 403);
            }

            // Récupérer les données
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
            $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'pdf';

            // Charger depuis la BD si ID fourni
            $template_data = [];
            if ($template_id && $template_id > 0) {
                $template_data = self::loadTemplateFromDatabase($template_id);
            }

            if (!is_array($template_data) || empty($template_data)) {
                wp_send_json_error('Template non trouvé');
            }

            // Générer le PDF
            $result = self::generatePreview($template_data, $format);
            wp_send_json_success($result);

        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
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
     * Génère le PDF à partir des données du template
     */
    private static function generatePreview(array $template_data, string $format = 'pdf', int $quality = 150) {
        // Charger Dompdf
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';
        
        if (!class_exists('Dompdf\Dompdf')) {
            return ['error' => 'Dompdf non disponible', 'fallback' => true];
        }

        // Créer le HTML
        $html = '<html><body style="font-family: Arial; margin: 20px;">
            <h1>' . htmlspecialchars($template_data['template_name'] ?? 'Template') . '</h1>
            <p>' . htmlspecialchars($template_data['description'] ?? '') . '</p>
            <hr>
            <p><strong>Éléments:</strong> ' . (isset($template_data['elements']) ? count($template_data['elements']) : 0) . '</p>
            <p><strong>Dimensions:</strong> ' . ($template_data['canvasWidth'] ?? 'N/A') . ' x ' . ($template_data['canvasHeight'] ?? 'N/A') . '</p>
            <p><small>Généré: ' . date('Y-m-d H:i:s') . '</small></p>
        </body></html>';

        // Créer le PDF
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourner le PDF
        $pdf_content = $dompdf->output();
        $pdf_url = self::savePdfTemporarily($pdf_content);

        return [
            'image_url' => $pdf_url,
            'success' => true,
            'fallback' => false,
            'format' => 'pdf'
        ];
    }

    /**
     * Sauvegarde un PDF temporairement et retourne l'URL
     */
    private static function savePdfTemporarily(string $pdf_content): string {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        $base_url = $upload_dir['baseurl'];
        $temp_dir = $base_dir . '/pdf-builder-temp';

        if (!is_dir($temp_dir)) {
            @mkdir($temp_dir, 0755, true);
        }

        $filename = 'preview-' . uniqid() . '.pdf';
        $filepath = $temp_dir . '/' . $filename;
        file_put_contents($filepath, $pdf_content);

        return $base_url . '/pdf-builder-temp/' . $filename;
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
