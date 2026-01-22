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
            // TEST - Confirmer que la fonction est appelée
            exit('AJAX_HANDLER_CALLED_OK');
            
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
     * Génère l'aperçu PDF à partir des données du template
     */
    private static function generatePreview(array $template_data, string $format = 'png', int $quality = 150) {
        try {
            error_log('[PREVIEW] Début de la génération - Format: ' . $format);
            
            // Si format PDF, générer un vrai PDF à partir du template_data
            if ($format === 'pdf') {
                return self::generatePdfFromTemplate($template_data, $quality);
            }
            
            // Sinon créer une image simple
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
     * Génère un PDF à partir des données du template
     */
    private static function generatePdfFromTemplate(array $template_data, int $quality = 150) {
        try {
            error_log('[PREVIEW PDF] Début génération PDF à partir du template');
            
            // Charger l'autoloader Composer si nécessaire
            $autoload_path = dirname(__FILE__) . '/../../vendor/autoload.php';
            if (file_exists($autoload_path)) {
                require_once($autoload_path);
                error_log('[PREVIEW PDF] Autoloader Composer chargé');
            }
            
            // Vérifier si dompdf est disponible
            if (!class_exists('Dompdf\Dompdf')) {
                error_log('[PREVIEW PDF] ❌ Dompdf non disponible - Utilisant fallback');
                return self::generateSimplePdfFallback($template_data);
            }

            error_log('[PREVIEW PDF] ✓ Dompdf trouvé');

            // Créer une instance de Dompdf
            $dompdf = new \Dompdf\Dompdf([
                'enable_remote' => false,
                'isHtml5ParserEnabled' => true,
            ]);

            // Générer le HTML à partir des données du template
            $html = self::generateHtmlFromTemplate($template_data);
            
            error_log('[PREVIEW PDF] HTML généré - ' . strlen($html) . ' bytes');

            // Charger le HTML dans Dompdf
            $dompdf->loadHtml($html);
            
            // Définir le format du papier et l'orientation
            $width = isset($template_data['canvasWidth']) ? (int)$template_data['canvasWidth'] : 800;
            $height = isset($template_data['canvasHeight']) ? (int)$template_data['canvasHeight'] : 600;
            
            // Utiliser A4 par défaut
            $dompdf->setPaper('A4', 'portrait');
            
            error_log('[PREVIEW PDF] Papier défini - A4');
            
            // Rendre le PDF
            $dompdf->render();
            
            error_log('[PREVIEW PDF] Render effectué');
            
            // Récupérer le contenu du PDF
            $pdf_content = $dompdf->output();
            
            error_log('[PREVIEW PDF] PDF généré avec succès - ' . strlen($pdf_content) . ' bytes');

            // Sauvegarder le PDF temporairement
            $pdf_url = self::savePdfTemporarily($pdf_content);
            
            error_log('[PREVIEW PDF] PDF sauvegardé - URL: ' . $pdf_url);

            return [
                'image_url' => $pdf_url,
                'format' => 'pdf',
                'success' => true,
                'fallback' => false,
                'generator' => 'dompdf'
            ];

        } catch (\Exception $e) {
            error_log('[PREVIEW PDF ERROR] Exception: ' . $e->getMessage());
            error_log('[PREVIEW PDF ERROR] Trace: ' . $e->getTraceAsString());
            return self::generateSimplePdfFallback($template_data);
        }
    }

    /**
     * Génère du HTML à partir des données du template
     */
    private static function generateHtmlFromTemplate(array $template_data): string {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; line-height: 1.5; }
        .container { padding: 20px; }
        h1 { font-size: 24px; margin-bottom: 20px; }
        .element { margin: 10px 0; padding: 10px; border: 1px solid #ddd; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>' . htmlspecialchars($template_data['template_name'] ?? 'Template') . '</h1>';
        
        if (!empty($template_data['description'])) {
            $html .= '<p>' . htmlspecialchars($template_data['description']) . '</p>';
        }
        
        // Afficher les éléments du template
        if (!empty($template_data['elements']) && is_array($template_data['elements'])) {
            $html .= '<div style="margin: 20px 0;">';
            $html .= '<h2 style="font-size: 18px; margin-bottom: 10px;">Éléments (' . count($template_data['elements']) . ')</h2>';
            
            foreach ($template_data['elements'] as $element) {
                $html .= '<div class="element">';
                $html .= '<strong>' . htmlspecialchars($element['type'] ?? 'Element') . '</strong>';
                if (!empty($element['content'])) {
                    $html .= ': ' . htmlspecialchars(substr($element['content'], 0, 100));
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        // Ajouter les informations du template
        $html .= '<div class="footer">
            <p><strong>Informations du Template:</strong></p>
            <ul>';
        
        if (!empty($template_data['canvasWidth']) && !empty($template_data['canvasHeight'])) {
            $html .= '<li>Dimensions: ' . htmlspecialchars($template_data['canvasWidth']) . ' x ' . htmlspecialchars($template_data['canvasHeight']) . '</li>';
        }
        
        if (!empty($template_data['version'])) {
            $html .= '<li>Version: ' . htmlspecialchars($template_data['version']) . '</li>';
        }
        
        $html .= '<li>Généré: ' . date('Y-m-d H:i:s') . '</li>
            </ul>
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Génère un PDF simple en fallback (sans dompdf)
     */
    private static function generateSimplePdfFallback(array $template_data) {
        try {
            error_log('[PREVIEW PDF] Génération PDF fallback simple');
            
            // Créer une image et la retourner en base64
            $image = imagecreatetruecolor(800, 600);
            $bg_color = imagecolorallocate($image, 255, 255, 255);
            $text_color = imagecolorallocate($image, 0, 0, 0);
            
            imagefill($image, 0, 0, $bg_color);
            imagestring($image, 5, 20, 20, $template_data['template_name'] ?? 'Template', $text_color);
            imagestring($image, 3, 20, 50, 'PDF Fallback Preview', $text_color);
            
            ob_start();
            imagepng($image);
            $image_data = ob_get_clean();
            imagedestroy($image);
            
            return [
                'image_url' => 'data:image/png;base64,' . base64_encode($image_data),
                'format' => 'pdf',
                'success' => true,
                'fallback' => true,
                'generator' => 'fallback-image'
            ];
        } catch (\Exception $e) {
            error_log('[PREVIEW PDF FALLBACK ERROR] ' . $e->getMessage());
            return [
                'image_url' => self::generatePlaceholderImage('png'),
                'format' => 'pdf',
                'fallback' => true,
                'success' => true,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sauvegarde un PDF temporairement et retourne l'URL
     */
    private static function savePdfTemporarily(string $pdf_content): string {
        try {
            error_log('[PREVIEW PDF SAVE] Début sauvegarde PDF temporaire');
            
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'];
            $base_url = $upload_dir['baseurl'];
            
            error_log('[PREVIEW PDF SAVE] Upload dir: ' . $base_dir);
            
            $temp_dir = $base_dir . '/pdf-builder-temp';

            if (!is_dir($temp_dir)) {
                error_log('[PREVIEW PDF SAVE] Création répertoire: ' . $temp_dir);
                @mkdir($temp_dir, 0755, true);
            }

            $filename = 'preview-' . uniqid() . '.pdf';
            $filepath = $temp_dir . '/' . $filename;
            
            error_log('[PREVIEW PDF SAVE] Sauvegarde fichier: ' . $filepath);
            
            $bytes_written = file_put_contents($filepath, $pdf_content);
            
            if ($bytes_written === false) {
                error_log('[PREVIEW PDF SAVE] ❌ Erreur écriture fichier');
                throw new \Exception('Impossible de sauvegarder le PDF');
            }
            
            error_log('[PREVIEW PDF SAVE] ✓ Fichier sauvegardé - ' . $bytes_written . ' bytes');
            
            $pdf_url = $base_url . '/pdf-builder-temp/' . $filename;
            
            error_log('[PREVIEW PDF SAVE] URL générée: ' . $pdf_url);
            
            return $pdf_url;
            
        } catch (\Exception $e) {
            error_log('[PREVIEW PDF SAVE ERROR] ' . $e->getMessage());
            throw $e;
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
