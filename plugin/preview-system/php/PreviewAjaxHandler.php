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
     */
    private static function loadTemplateFromDatabase(int $template_id): ?array {
        try {
            error_log('[PREVIEW DB] === Début du chargement du template ID: ' . $template_id . ' ===');
            
            // Récupérer le post du template
            $post = get_post($template_id, ARRAY_A);
            
            if (!$post) {
                error_log('[PREVIEW DB] ❌ Post non trouvé pour ID: ' . $template_id);
                return null;
            }

            error_log('[PREVIEW DB] Post trouvé - Type: ' . $post['post_type'] . ', Titre: ' . $post['post_title']);
            
            if ($post['post_type'] !== 'pdf_template') {
                error_log('[PREVIEW DB] ❌ Post type incorrect: ' . $post['post_type'] . ' (attendu: pdf_template)');
                return null;
            }

            // Récupérer les métadonnées du template
            error_log('[PREVIEW DB] Récupération de _pdf_template_data...');
            $template_data = get_post_meta($template_id, '_pdf_template_data', true);

            if (!$template_data) {
                error_log('[PREVIEW DB] ❌ Pas de métadonnées trouvées pour la clé _pdf_template_data');
                // Lister toutes les métadonnées pour debug
                $all_meta = get_post_meta($template_id);
                error_log('[PREVIEW DB] Clés de métadonnées disponibles: ' . implode(', ', array_keys($all_meta)));
                return null;
            }

            error_log('[PREVIEW DB] Métadonnées trouvées - Type: ' . gettype($template_data) . ', Taille: ' . strlen((is_string($template_data) ? $template_data : json_encode($template_data))));

            // Si c'est une chaîne JSON, la décoder
            if (is_string($template_data)) {
                error_log('[PREVIEW DB] Décodage du JSON...');
                $decoded = json_decode($template_data, true);
                if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    error_log('[PREVIEW DB] ❌ Erreur de décodage JSON: ' . json_last_error_msg());
                    error_log('[PREVIEW DB] Premier 500 caractères: ' . substr($template_data, 0, 500));
                    return null;
                }
                error_log('[PREVIEW DB] ✓ JSON décodé avec succès - Type: ' . gettype($decoded));
                $template_data = $decoded;
            } else {
                error_log('[PREVIEW DB] Données déjà en format ' . gettype($template_data));
            }

            // Ajouter les informations du post si nécessaire
            if (!isset($template_data['template_id'])) {
                $template_data['template_id'] = $template_id;
            }

            if (!isset($template_data['template_name'])) {
                $template_data['template_name'] = $post['post_title'];
            }

            error_log('[PREVIEW DB] ✓ Template chargé avec succès - ID: ' . $template_id . ', Nom: ' . $post['post_title'] . ', Clés données: ' . implode(', ', array_keys($template_data)));
            return $template_data;

        } catch (\Exception $e) {
            error_log('[PREVIEW DB ERROR] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Génère l'aperçu avec le système de fallback
     */
    private static function generatePreview(array $template_data, string $format = 'png', int $quality = 150) {
        try {
            error_log('[PREVIEW] Début de la génération - Format: ' . $format);

            // Préparer les données
            $final_template_data = [
                'template' => $template_data,
            ];

            // Obtenir le data provider (utilisateur courant par défaut)
            $data_provider = self::getDataProvider();

            // Créer le manager de générateurs
            $generator_manager = new GeneratorManager();

            // Générer l'aperçu
            $output_type = $format === 'pdf' ? 'pdf' : 'png';
            error_log('[PREVIEW] Appel GeneratorManager->generatePreview() avec output_type: ' . $output_type);
            
            $preview_result = $generator_manager->generatePreview(
                $final_template_data,
                $data_provider,
                $output_type,
                [
                    'quality' => $quality,
                    'is_preview' => true,
                ]
            );

            error_log('[PREVIEW] Génération complétée - Type retour: ' . gettype($preview_result));
            if (is_array($preview_result)) {
                error_log('[PREVIEW] Résultat est un array avec clés: ' . implode(', ', array_keys($preview_result)));
            } elseif (is_string($preview_result)) {
                error_log('[PREVIEW] Résultat est une string de ' . strlen($preview_result) . ' bytes');
            } elseif ($preview_result === null) {
                error_log('[PREVIEW] Résultat est NULL');
            } else {
                error_log('[PREVIEW] Résultat est de type: ' . gettype($preview_result));
            }

            // === FOCUS: PDF ONLY ===
            // Pour l'instant, on se concentre UNIQUEMENT sur PDF

            // Format 1: String direct = PDF binary content
            if (is_string($preview_result)) {
                error_log('[PREVIEW] PDF généré avec succès - taille: ' . strlen($preview_result) . ' bytes');
                $pdf_file = self::savePdfTemporary($preview_result);
                return [
                    'image_url' => $pdf_file,
                    'format' => 'pdf',
                    'success' => true
                ];
            }

            // Format 2: Array avec PDF file path
            if (is_array($preview_result) && isset($preview_result['file'])) {
                error_log('[PREVIEW] PDF fichier généré: ' . $preview_result['file']);
                return [
                    'image_url' => $preview_result['file'],
                    'format' => 'pdf',
                    'success' => true
                ];
            }

            // Format 3: Fallback - error
            error_log('[PREVIEW] Format inattendu ou erreur: ' . gettype($preview_result));
            return [
                'image_url' => self::generatePlaceholderImage($format),
                'format' => $format,
                'fallback' => true,
                'success' => true
            ];
            error_log('[PREVIEW ERROR] ' . $e->getMessage());
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
