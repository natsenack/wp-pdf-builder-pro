<?php
/**
 * NOUVEAU SYSTÈME D'APERÇU PDF - Côté Serveur avec TCPDF
 * Génère des miniatures d'aperçu pour l'interface utilisateur
 */

if (!defined('PDF_PREVIEW_TEST_MODE')) {
    define('PDF_PREVIEW_TEST_MODE', true);
}
if (!defined('PDF_GENERATOR_TEST_MODE')) {
    define('PDF_GENERATOR_TEST_MODE', true);
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
            $html .= $this->render_special_element($element, $scale);
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Rend un élément spécial (basé sur PreviewModal.jsx)
     */
    private function render_special_element($element, $scale = 1) {
        $base_style = sprintf(
            'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx; z-index: %d;',
            ($element['x'] ?? 0) * $scale,
            ($element['y'] ?? 0) * $scale,
            ($element['width'] ?? 100) * $scale,
            ($element['height'] ?? 50) * $scale,
            $element['zIndex'] ?? 1
        );

        $element_type = $element['type'] ?? 'unknown';

        switch ($element_type) {
            case 'text':
                $text_style = sprintf(
                    'width: 100%%; height: 100%%; font-size: %dpx; color: %s; font-weight: %s; font-style: %s; text-align: %s; line-height: 1.2; white-space: pre-wrap; overflow: hidden; padding: 4px; box-sizing: border-box;',
                    ($element['fontSize'] ?? 16) * $scale,
                    $element['color'] ?? '#000000',
                    (isset($element['fontWeight']) && $element['fontWeight'] === 'bold') ? 'bold' : 'normal',
                    (isset($element['fontStyle']) && $element['fontStyle'] === 'italic') ? 'italic' : 'normal',
                    $element['textAlign'] ?? 'left'
                );

                return sprintf(
                    '<div style="%s"><div style="%s">%s</div></div>',
                    $base_style,
                    $text_style,
                    htmlspecialchars($element['content'] ?? $element['text'] ?? 'Texte')
                );

            case 'rectangle':
                $rect_style = sprintf(
                    'width: 100%%; height: 100%%; background-color: %s; border: %s; border-radius: %dpx;',
                    $element['fillColor'] ?? 'transparent',
                    $element['borderWidth'] ? sprintf('%dpx solid %s', $element['borderWidth'], $element['borderColor'] ?? '#000000') : 'none',
                    $element['borderRadius'] ?? 0
                );

                return sprintf('<div style="%s"><div style="%s"></div></div>', $base_style, $rect_style);

            case 'image':
                $img_style = 'width: 100%; height: 100%; object-fit: cover;';
                return sprintf(
                    '<div style="%s"><img src="%s" alt="%s" style="%s" onerror="this.style.display=\'none\'" /></div>',
                    $base_style,
                    htmlspecialchars($element['src'] ?? ''),
                    htmlspecialchars($element['alt'] ?? 'Image'),
                    $img_style
                );

            case 'line':
                $line_style = sprintf(
                    'width: 100%%; height: 0; border-top: %dpx solid %s;',
                    $element['strokeWidth'] ?? 1,
                    $element['strokeColor'] ?? '#000000'
                );

                return sprintf('<div style="%s"><div style="%s"></div></div>', $base_style, $line_style);

            case 'divider':
                $divider_style = sprintf(
                    'width: 100%%; height: %dpx; background-color: %s; margin: %dpx 0;',
                    $element['thickness'] ?? 2,
                    $element['color'] ?? '#cccccc',
                    ($element['margin'] ?? 10) * $scale
                );

                return sprintf('<div style="%s"><div style="%s"></div></div>', $base_style, $divider_style);

            case 'product_table':
                $table_html = '<div style="width: 100%; height: 100%; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; font-size: 10px; background-color: white;">';
                $table_html .= '<div style="display: flex; background-color: #f5f5f5; padding: 4px; font-weight: bold; border-bottom: 1px solid #ddd;">';
                $table_html .= '<div style="flex: 1;">Produit</div>';
                $table_html .= '<div style="width: 60px; text-align: center;">Qté</div>';
                $table_html .= '<div style="width: 80px; text-align: right;">Prix</div>';
                $table_html .= '<div style="width: 80px; text-align: right;">Total</div>';
                $table_html .= '</div>';
                $table_html .= '<div style="padding: 4px; border-bottom: 1px solid #eee;">';
                $table_html .= '<div style="display: flex;">';
                $table_html .= '<div style="flex: 1;">Produit A - Description</div>';
                $table_html .= '<div style="width: 60px; text-align: center;">2</div>';
                $table_html .= '<div style="width: 80px; text-align: right;">19.99€</div>';
                $table_html .= '<div style="width: 80px; text-align: right;">39.98€</div>';
                $table_html .= '</div>';
                $table_html .= '</div>';
                $table_html .= '<div style="padding: 4px; font-weight: bold; text-align: right;">Total: 39.98€</div>';
                $table_html .= '</div>';

                return sprintf('<div style="%s">%s</div>', $base_style, $table_html);

            case 'customer_info':
                $customer_html = '<div style="padding: 8px; font-size: 12px; line-height: 1.4;">';
                $customer_html .= '<div style="font-weight: bold; margin-bottom: 4px;">Client</div>';
                $customer_html .= '<div>Jean Dupont</div>';
                $customer_html .= '<div>123 Rue de la Paix</div>';
                $customer_html .= '<div>75001 Paris</div>';
                $customer_html .= '<div>France</div>';
                $customer_html .= '</div>';

                return sprintf('<div style="%s">%s</div>', $base_style, $customer_html);

            case 'company_info':
                $company_html = '<div style="padding: 8px; font-size: 12px; line-height: 1.4;">';
                $company_html .= '<div style="font-weight: bold; margin-bottom: 4px;">ABC Company SARL</div>';
                $company_html .= '<div>456 Avenue des Champs</div>';
                $company_html .= '<div>75008 Paris</div>';
                $company_html .= '<div>France</div>';
                $company_html .= '<div>Tél: 01 23 45 67 89</div>';
                $company_html .= '</div>';

                return sprintf('<div style="%s">%s</div>', $base_style, $company_html);

            case 'company_logo':
                $logo_html = '<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 8px; background-color: ' . ($element['backgroundColor'] ?? 'transparent') . ';">';

                if (isset($element['imageUrl']) && $element['imageUrl']) {
                    $logo_html .= sprintf('<img src="%s" alt="Logo entreprise" style="max-width: 100%%; max-height: 100%%; object-fit: contain;" />', htmlspecialchars($element['imageUrl']));
                } else {
                    $logo_html .= '<div style="width: 100%; height: 100%; background-color: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px;">🏢 Logo</div>';
                }

                $logo_html .= '</div>';

                return sprintf('<div style="%s">%s</div>', $base_style, $logo_html);

            case 'order_number':
                $order_html = '<div style="padding: 8px; font-size: 14px; font-weight: bold; color: ' . ($element['color'] ?? '#333') . ';">';
                $order_html .= '<div style="font-size: 12px; color: #666; margin-bottom: 2px;">N° de commande:</div>';
                $order_html .= '<div>CMD-2025-00123</div>';
                $order_html .= '</div>';

                return sprintf('<div style="%s">%s</div>', $base_style, $order_html);

            case 'document_type':
                $doc_type = $element['documentType'] ?? '';
                $doc_text = match($doc_type) {
                    'invoice' => 'FACTURE',
                    'quote' => 'DEVIS',
                    'receipt' => 'REÇU',
                    'order' => 'COMMANDE',
                    'credit_note' => 'AVOIR',
                    default => 'DOCUMENT'
                };

                $doc_html = '<div style="padding: 8px; font-size: 18px; font-weight: bold; color: ' . ($element['color'] ?? '#1e293b') . '; text-align: center;">';
                $doc_html .= $doc_text;
                $doc_html .= '</div>';

                return sprintf('<div style="%s">%s</div>', $base_style, $doc_html);

            case 'progress-bar':
                $progress_html = '<div style="width: 100%; height: 100%; background-color: #e5e7eb; border-radius: 10px; overflow: hidden;">';
                $progress_html .= sprintf('<div style="width: %d%%; height: 100%%; background-color: %s; border-radius: 10px;"></div>', $element['progressValue'] ?? 75, $element['progressColor'] ?? '#3b82f6');
                $progress_html .= '</div>';

                return sprintf('<div style="%s">%s</div>', $base_style, $progress_html);

            default:
                $unknown_html = '<div style="width: 100%; height: 100%; background-color: #f0f0f0; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666; padding: 4px; box-sizing: border-box;">';
                $unknown_html .= 'Élément inconnu: ' . htmlspecialchars($element_type);
                $unknown_html .= '</div>';

                return sprintf('<div style="%s">%s</div>', $base_style, $unknown_html);
        }
    }
}

// Fonction AJAX pour l'aperçu
function pdf_builder_generate_preview() {
    // Démarrer la bufferisation de sortie pour capturer toute sortie accidentelle
    ob_start();

    try {
        // Vérifier que les fonctions WordPress sont disponibles
        if (!function_exists('wp_verify_nonce') || !function_exists('wp_send_json_error') || !function_exists('wp_send_json_success')) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Fonctions WordPress non disponibles']);
            exit;
        }

        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_nonce')) {
            ob_end_clean();
            wp_send_json_error('Nonce invalide');
            return;
        }

        // Récupérer les éléments
        $elements = json_decode(stripslashes($_POST['elements'] ?? '[]'), true);

        if (empty($elements)) {
            ob_end_clean();
            wp_send_json_error('Aucun élément à prévisualiser');
            return;
        }

        // Générer l'aperçu avec fallback automatique
        $preview_generator = new PDF_Preview_Generator();
        $result = $preview_generator->generate_preview($elements, 400, 566);

        // Nettoyer le buffer avant d'envoyer la réponse
        ob_end_clean();

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            error_log('PDF Preview Error: ' . $result['error']);
            wp_send_json_error('Erreur génération aperçu: ' . $result['error']);
        }

    } catch (Exception $e) {
        // Nettoyer le buffer et logger l'erreur
        ob_end_clean();
        error_log('Exception aperçu PDF: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        wp_send_json_error('Erreur serveur: ' . $e->getMessage());
    } catch (Error $e) {
        // Capturer aussi les erreurs fatales PHP
        ob_end_clean();
        error_log('Erreur fatale aperçu PDF: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        wp_send_json_error('Erreur fatale serveur: ' . $e->getMessage());
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