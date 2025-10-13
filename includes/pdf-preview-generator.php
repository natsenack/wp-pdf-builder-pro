<?php
/**
 * NOUVEAU SYSTÈME D'APERÇU PDF - Côté Serveur avec TCPDF
 * Génère des miniatures d'aperçu pour l'interface utilisateur
 */

if (!defined('PDF_PREVIEW_TEST_MODE')) {
    define('PDF_PREVIEW_TEST_MODE', true);
}
require_once __DIR__ . '/pdf-generator.php';

class PDF_Preview_Generator {

    private $generator;

    public function __construct() {
        try {
            $this->generator = new PDF_Builder_Pro_Generator();
        } catch (Exception $e) {
            $this->generator = null;
        }
    }

    /**
     * Génère un aperçu miniature du PDF
     */
    public function generate_preview($elements, $width = 400, $height = 566) {
        try {
            // Vérifier si le générateur est disponible
            if (!$this->generator) {
                return $this->generate_fallback_preview($elements, $width, $height);
            }

            // Générer le PDF complet
            $pdf_content = $this->generator->generate($elements);

            if (!$pdf_content) {
                throw new Exception('Échec génération PDF complet');
            }

            // Créer une image miniature
            $preview_image = $this->create_pdf_thumbnail($pdf_content, $width, $height);

            if (!$preview_image) {
                throw new Exception('Échec création miniature');
            }

            return [
                'success' => true,
                'preview' => base64_encode($preview_image),
                'width' => $width,
                'height' => $height,
                'elements_count' => count($elements)
            ];

        } catch (Exception $e) {
            return $this->generate_fallback_preview($elements, $width, $height);
        }
    }

    /**
     * Génère un aperçu de fallback simple
     */
    private function generate_fallback_preview($elements, $width, $height) {
        try {
            // Créer une image simple avec GD
            if (!function_exists('imagecreatetruecolor')) {
                throw new Exception('Extension GD non disponible');
            }

            $image = imagecreatetruecolor($width, $height);

            // Fond blanc
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);

            // Bordure
            $black = imagecolorallocate($image, 0, 0, 0);
            imagerectangle($image, 0, 0, $width-1, $height-1, $black);

            // Texte d'aperçu
            $text_color = imagecolorallocate($image, 100, 100, 100);
            imagestring($image, 5, 20, 20, 'APERÇU PDF', $text_color);
            imagestring($image, 3, 20, 50, count($elements) . ' elements', $text_color);
            imagestring($image, 2, 20, $height - 30, 'Mode fallback', $text_color);

            // Convertir en PNG
            ob_start();
            imagepng($image);
            $png_data = ob_get_clean();
            imagedestroy($image);

            return [
                'success' => true,
                'preview' => base64_encode($png_data),
                'width' => $width,
                'height' => $height,
                'elements_count' => count($elements),
                'fallback' => true
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur génération aperçu: ' . $e->getMessage(),
                'elements_count' => count($elements),
                'fallback_failed' => true
            ];
        }
    }

    /**
     * Crée une miniature du PDF en utilisant TCPDF
     */
    private function create_pdf_thumbnail($pdf_content, $thumb_width, $thumb_height) {
        try {
            // Sauvegarder temporairement le PDF
            $temp_pdf = tempnam(sys_get_temp_dir(), 'pdf_preview_') . '.pdf';
            file_put_contents($temp_pdf, $pdf_content);

            // Créer une image miniature avec ImageMagick ou GD
            $thumbnail = $this->create_thumbnail_from_pdf($temp_pdf, $thumb_width, $thumb_height);

            // Nettoyer
            unlink($temp_pdf);

            return $thumbnail;

        } catch (Exception $e) {
            error_log('PDF Preview: Erreur création miniature - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée une miniature à partir du PDF (méthode simplifiée)
     */
    private function create_thumbnail_from_pdf($pdf_path, $width, $height) {
        // Vérifier si GD est disponible
        if (!function_exists('imagecreatetruecolor')) {
            throw new Exception('Extension GD non disponible pour la génération de miniatures');
        }

        // Pour l'instant, créer une image simple avec les dimensions
        // En production, utiliser ImageMagick ou une vraie conversion PDF->image

        $image = imagecreatetruecolor($width, $height);

        // Fond blanc
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        // Bordure
        $black = imagecolorallocate($image, 0, 0, 0);
        imagerectangle($image, 0, 0, $width-1, $height-1, $black);

        // Texte d'aperçu
        $text_color = imagecolorallocate($image, 100, 100, 100);
        $font_size = 12;
        imagestring($image, $font_size, 20, 20, 'APERÇU PDF', $text_color);
        imagestring($image, 3, 20, 50, 'Miniature generee', $text_color);
        imagestring($image, 2, 20, $height - 30, 'PDF Builder Pro v2.0', $text_color);

        // Convertir en PNG
        ob_start();
        imagepng($image);
        $png_data = ob_get_clean();
        imagedestroy($image);

        return $png_data;
    }

    /**
     * Génère un aperçu HTML simple (fallback)
     */
    public function generate_html_preview($elements, $scale = 0.3) {
        $html = '<div style="border: 1px solid #ddd; padding: 10px; background: white; font-family: Arial, sans-serif; font-size: ' . (12 * $scale) . 'px;">';

        foreach ($elements as $element) {
            $style = sprintf(
                'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                $element['x'] * $scale,
                $element['y'] * $scale,
                $element['width'] * $scale,
                $element['height'] * $scale
            );

            if ($element['type'] === 'text') {
                $text_style = sprintf(
                    'font-size: %dpx; color: %s; font-weight: %s; text-align: %s;',
                    ($element['fontSize'] ?? 12) * $scale,
                    $element['color'] ?? '#000000',
                    (($element['fontWeight'] ?? '') === 'bold') ? 'bold' : 'normal',
                    $element['textAlign'] ?? 'left'
                );

                $html .= sprintf(
                    '<div style="%s %s">%s</div>',
                    $style,
                    $text_style,
                    htmlspecialchars($element['content'] ?? $element['text'] ?? 'Texte')
                );

            } elseif ($element['type'] === 'rectangle') {
                $border_style = sprintf(
                    'border: %dpx solid %s; background: transparent;',
                    $element['borderWidth'] ?? 1,
                    $element['borderColor'] ?? '#000000'
                );

                $html .= sprintf('<div style="%s %s"></div>', $style, $border_style);
            }
        }

        $html .= '</div>';
        return $html;
    }
}

// Fonction AJAX pour l'aperçu
function pdf_builder_generate_preview() {
    try {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_nonce')) {
            wp_send_json_error('Nonce invalide');
            return;
        }

        // Récupérer les éléments
        $elements = json_decode(stripslashes($_POST['elements'] ?? '[]'), true);

        if (empty($elements)) {
            wp_send_json_error('Aucun élément à prévisualiser');
            return;
        }

        // Générer l'aperçu avec fallback automatique
        $preview_generator = new PDF_Preview_Generator();
        $result = $preview_generator->generate_preview($elements, 400, 566);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error('Erreur génération aperçu: ' . $result['error']);
        }

    } catch (Exception $e) {
        error_log('Erreur aperçu PDF: ' . $e->getMessage());
        wp_send_json_error('Erreur serveur: ' . $e->getMessage());
    }
}

// Endpoint de test AJAX
function pdf_builder_test_ajax() {
    wp_send_json_success(['message' => 'AJAX fonctionne', 'timestamp' => time()]);
}

// Endpoint pour obtenir un nonce frais
function pdf_builder_get_fresh_nonce() {
    try {
        wp_send_json_success([
            'nonce' => wp_create_nonce('pdf_builder_nonce'),
            'timestamp' => time()
        ]);
    } catch (Exception $e) {
        error_log('Erreur génération nonce frais: ' . $e->getMessage());
        wp_send_json_error('Erreur génération nonce');
    }
}

// Enregistrer la fonction AJAX
if (function_exists('add_action')) {
    add_action('wp_ajax_pdf_builder_generate_preview', 'pdf_builder_generate_preview');
    add_action('wp_ajax_nopriv_pdf_builder_generate_preview', 'pdf_builder_generate_preview');
    add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_get_fresh_nonce');
    add_action('wp_ajax_nopriv_pdf_builder_get_fresh_nonce', 'pdf_builder_get_fresh_nonce');
    add_action('wp_ajax_pdf_builder_test_ajax', 'pdf_builder_test_ajax');
    add_action('wp_ajax_nopriv_pdf_builder_test_ajax', 'pdf_builder_test_ajax');
}