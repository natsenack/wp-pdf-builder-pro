<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - PDF Generator Manager
 * Gestion centralisée de la génération PDF
 */

class PDF_Builder_PDF_Generator {

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance) {
        $this->main = $main_instance;
        $this->init_hooks();
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // AJAX handlers pour la génération PDF
        add_action('wp_ajax_pdf_builder_generate_pdf_from_canvas', [$this, 'ajax_generate_pdf_from_canvas']);
        add_action('wp_ajax_pdf_builder_preview_pdf', [$this, 'ajax_preview_pdf']);
        add_action('wp_ajax_pdf_builder_download_pdf', [$this, 'ajax_download_pdf']);
        add_action('wp_ajax_pdf_builder_unified_preview', [$this, 'ajax_unified_preview']);
    }

    /**
     * AJAX - Générer PDF depuis les données canvas
     */
    public function ajax_generate_pdf_from_canvas() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $canvas_data = isset($_POST['canvas_data']) ? $_POST['canvas_data'] : '';

        if (empty($canvas_data)) {
            wp_send_json_error('Données canvas manquantes');
        }

        try {
            // Décoder les données canvas
            $canvas_elements = json_decode($canvas_data, true);
            
            if (!$canvas_elements || !is_array($canvas_elements)) {
                wp_send_json_error('Format JSON invalide pour les données canvas');
            }

            $pdf_path = $this->generate_pdf_from_template_data($canvas_elements, 'canvas-' . time() . '.pdf');

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

                wp_send_json_success(array(
                    'url' => $pdf_url,
                    'path' => $pdf_path
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Prévisualiser PDF
     */
    public function ajax_preview_pdf() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';

        if (empty($template_data)) {
            wp_send_json_error('Données template manquantes');
        }

        try {
            $pdf_path = $this->generate_pdf_from_template_data(json_decode($template_data, true), 'preview-' . time() . '.pdf');

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

                wp_send_json_success(array(
                    'url' => $pdf_url,
                    'path' => $pdf_path
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Télécharger PDF
     */
    public function ajax_download_pdf() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
        $filename = isset($_POST['filename']) ? sanitize_file_name($_POST['filename']) : 'document.pdf';

        if (empty($template_data)) {
            wp_send_json_error('Données template manquantes');
        }

        try {
            $pdf_path = $this->generate_pdf_from_template_data(json_decode($template_data, true), $filename);

            if ($pdf_path && file_exists($pdf_path)) {
                wp_send_json_success(array(
                    'path' => $pdf_path,
                    'filename' => $filename
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Aperçu unifié (pour le canvas editor)
     */
    public function ajax_unified_preview() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Lire les données depuis POST (FormData)
        if (!isset($_POST['nonce']) || !isset($_POST['elements'])) {
            wp_send_json_error('Données de requête invalides');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $elements = $_POST['elements'];

        // Debug: Log what we received
        error_log('[PDF Builder Preview] Raw elements received: ' . substr($elements, 0, 200) . '...');
        error_log('[PDF Builder Preview] Elements length: ' . strlen($elements));

        try {
            // Décoder depuis base64
            $jsonString = base64_decode($elements);
            if ($jsonString === false) {
                error_log('[PDF Builder Preview] Base64 decode failed');
                wp_send_json_error('Erreur décodage base64');
                return;
            }

            error_log('[PDF Builder Preview] Decoded JSON length: ' . strlen($jsonString));
            error_log('[PDF Builder Preview] Decoded JSON start: ' . substr($jsonString, 0, 200) . '...');

            // Décoder les éléments JSON
            $canvas_elements = json_decode($jsonString, true);
            
            if ($canvas_elements === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log('[PDF Builder Preview] JSON decode error: ' . json_last_error_msg());
                wp_send_json_error('Erreur décodage JSON: ' . json_last_error_msg());
                return;
            }
            
            if (!$canvas_elements || !is_array($canvas_elements)) {
                error_log('[PDF Builder Preview] Invalid canvas elements structure');
                wp_send_json_error('Format JSON invalide pour les éléments');
                return;
            }

            error_log('[PDF Builder Preview] Successfully decoded ' . count($canvas_elements) . ' elements');

            // DEBUG: Confirm new code path is running
            error_log('[PDF Builder Preview] NEW CODE PATH: Using internal HTML generation');

            // Générer le HTML directement sans dépendre du contrôleur externe
            $html_content = $this->generate_html_from_elements($canvas_elements);
            
            error_log('[PDF Builder Preview] HTML generation completed, length: ' . strlen($html_content));
            
            if (!empty($html_content)) {
                // Créer un fichier HTML temporaire pour l'aperçu
                $upload_dir = wp_upload_dir();
                $html_dir = $upload_dir['basedir'] . '/pdf-builder';
                error_log('[PDF Builder Preview] Upload dir: ' . $upload_dir['basedir']);
                error_log('[PDF Builder Preview] HTML dir: ' . $html_dir);
                
                if (!file_exists($html_dir)) {
                    wp_mkdir_p($html_dir);
                    error_log('[PDF Builder Preview] Created HTML directory');
                }

                $filename = 'preview-' . time() . '.html';
                $html_path = $html_dir . '/' . $filename;
                error_log('[PDF Builder Preview] HTML path: ' . $html_path);
                
                $write_result = file_put_contents($html_path, $html_content);
                error_log('[PDF Builder Preview] File write result: ' . ($write_result !== false ? 'SUCCESS (' . $write_result . ' bytes)' : 'FAILED'));
                
                $html_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $html_path);
                error_log('[PDF Builder Preview] HTML URL: ' . $html_url);

                wp_send_json_success(array(
                    'url' => $html_url,
                    'path' => $html_path,
                    'elements_count' => count($canvas_elements),
                    'type' => 'html'
                ));
            } else {
                error_log('[PDF Builder Preview] HTML content is empty');
                wp_send_json_error('Erreur lors de la génération du HTML d\'aperçu');
            }
        } catch (Exception $e) {
            error_log('[PDF Builder Preview] Exception: ' . $e->getMessage());
            wp_send_json_error('Erreur traitement données: ' . $e->getMessage());
        }
    }

    /**
     * Générer PDF depuis les données template
     */
    private function generate_pdf_from_template_data($template, $filename) {
        if (!$template || !is_array($template)) {
            throw new Exception('Données template invalides');
        }

        // Générer le HTML
        $html = $this->generate_html_from_template_data($template);

        // Créer le répertoire uploads s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_path = $pdf_dir . '/' . $filename;

        // Générer le PDF avec TCPDF
        require_once plugin_dir_path(dirname(__FILE__)) . '../../lib/tcpdf/tcpdf_autoload.php';

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuration PDF
        $pdf->SetCreator('PDF Builder Pro');
        $pdf->SetAuthor('PDF Builder Pro');
        $pdf->SetTitle('Document PDF');
        $pdf->SetSubject('Document généré par PDF Builder Pro');
        $pdf->SetKeywords('PDF, Builder, Pro');

        // Supprimer les headers par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        // Écrire le HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Sauvegarder le PDF
        $pdf->Output($pdf_path, 'F');

        return $pdf_path;
    }

    /**
     * Générer HTML depuis les données template
     */
    private function generate_html_from_template_data($template) {
        // Utiliser la même fonction que l'aperçu commande pour la cohérence
        if (class_exists('PDF_Builder\Admin\PDF_Builder_Admin')) {
            $admin = \PDF_Builder\Admin\PDF_Builder_Admin::getInstance();
            if (method_exists($admin, 'generate_unified_html')) {
                return $admin->generate_unified_html($template);
            }
        }

        // Fallback vers l'ancienne implémentation si nécessaire
        return $this->generate_unified_html_legacy($template);
    }

    /**
     * Générer HTML unifié (version legacy - conservée pour compatibilité)
     */
    private function generate_unified_html_legacy($template, $order = null) {
        $html = '<div style="font-family: Arial, sans-serif; padding: 20px;">';

        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            $elements = $firstPage['elements'] ?? [];
        } elseif (isset($template['elements']) && is_array($template['elements'])) {
            $elements = $template['elements'];
        } else {
            $elements = [];
        }

        if (is_array($elements)) {
            foreach ($elements as $element) {
                // Gérer les deux formats de structure des éléments
                if (isset($element['position']) && isset($element['size'])) {
                    $x = $element['position']['x'] ?? 0;
                    $y = $element['position']['y'] ?? 0;
                    $width = $element['size']['width'] ?? 100;
                    $height = $element['size']['height'] ?? 50;
                } else {
                    $x = $element['x'] ?? 0;
                    $y = $element['y'] ?? 0;
                    $width = $element['width'] ?? 100;
                    $height = $element['height'] ?? 50;
                }

                $style = sprintf(
                    'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                    $x, $y, $width, $height
                );

                if (isset($element['style'])) {
                    if (isset($element['style']['color'])) {
                        $style .= ' color: ' . $element['style']['color'] . ';';
                    }
                    if (isset($element['style']['fontSize'])) {
                        $style .= ' font-size: ' . $element['style']['fontSize'] . 'px;';
                    }
                    if (isset($element['style']['fontWeight'])) {
                        $style .= ' font-weight: ' . $element['style']['fontWeight'] . ';';
                    }
                    if (isset($element['style']['fillColor'])) {
                        $style .= ' background-color: ' . $element['style']['fillColor'] . ';';
                    }
                }

                $content = $element['content'] ?? '';

                // Remplacer les variables si on a une commande WooCommerce
                if ($order) {
                    $content = $this->replace_order_variables($content, $order);
                }

                switch ($element['type']) {
                    case 'text':
                        $final_content = $order ? $this->replace_order_variables($content, $order) : $content;
                        $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($final_content));
                        break;

                    case 'invoice_number':
                        if ($order) {
                            $invoice_number = $order->get_id() . '-' . time();
                            $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($invoice_number));
                        }
                        break;

                    default:
                        $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($content));
                        break;
                }
            }
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Convertir les éléments en format template
     */
    private function convert_elements_to_template($elements) {
        return array(
            'pages' => array(
                array(
                    'elements' => $elements
                )
            )
        );
    }

    /**
     * Générer le HTML à partir des éléments du canvas
     */
    private function generate_html_from_elements($elements) {
        error_log('[PDF Builder Preview] Starting HTML generation for ' . count($elements) . ' elements');
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Preview</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .pdf-container { 
            position: relative;
            width: 595px; 
            height: 842px; 
            background: white; 
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .canvas-element { position: absolute; overflow: hidden; }
    </style>
</head>
<body>
    <div class="pdf-container">';

        foreach ($elements as $element) {
            $html .= $this->render_element_to_html($element);
        }

        $html .= '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Rendre un élément individuel en HTML
     */
    private function render_element_to_html($element) {
        $type = $element['type'] ?? 'text';
        
        // Extraire les coordonnées
        $x = $element['position']['x'] ?? $element['x'] ?? 0;
        $y = $element['position']['y'] ?? $element['y'] ?? 0;
        $width = $element['size']['width'] ?? $element['width'] ?? 100;
        $height = $element['size']['height'] ?? $element['height'] ?? 50;

        // Dimensions par défaut si manquantes
        if (empty($width) || $width <= 0) $width = 100;
        if (empty($height) || $height <= 0) $height = 50;

        // Contraindre dans les limites A4 (595x842 pixels)
        $canvas_width = 595;
        $canvas_height = 842;
        $x = max(0, min($canvas_width - $width, $x));
        $y = max(0, min($canvas_height - $height, $y));

        $style = sprintf(
            'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
            $x, $y, $width, $height
        );

        // Appliquer les styles CSS des propriétés
        $style .= 'box-sizing: border-box; ';
        
        // Styles de base depuis les propriétés
        if (isset($element['properties'])) {
            if (isset($element['properties']['color'])) {
                $style .= 'color: ' . $element['properties']['color'] . '; ';
            }
            if (isset($element['properties']['backgroundColor'])) {
                $style .= 'background-color: ' . $element['properties']['backgroundColor'] . '; ';
            }
            if (isset($element['properties']['fontSize'])) {
                $style .= 'font-size: ' . $element['properties']['fontSize'] . 'px; ';
            }
            if (isset($element['properties']['fontWeight'])) {
                $style .= 'font-weight: ' . $element['properties']['fontWeight'] . '; ';
            }
            if (isset($element['properties']['textAlign'])) {
                $style .= 'text-align: ' . $element['properties']['textAlign'] . '; ';
            }
            if (isset($element['properties']['border'])) {
                $style .= 'border: ' . $element['properties']['border'] . '; ';
            }
        }

        // Rendre selon le type d'élément
        switch ($type) {
            case 'text':
            case 'dynamic-text':
            case 'multiline_text':
                $content = $element['content'] ?? $element['text'] ?? $element['customContent'] ?? '';
                $style .= 'white-space: pre-wrap; word-wrap: break-word; ';
                return "<div class='canvas-element' style='" . esc_attr($style) . "'>" . wp_kses_post($content) . "</div>";

            case 'image':
            case 'company_logo':
                $src = $element['imageUrl'] ?? $element['src'] ?? '';
                if (!$src && $type === 'company_logo') {
                    $custom_logo_id = get_theme_mod('custom_logo');
                    $src = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';
                }
                if ($src) {
                    $style .= 'object-fit: contain; ';
                    return "<img class='canvas-element' src='" . esc_url($src) . "' style='" . esc_attr($style) . "' />";
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;'>Image</div>";

            case 'rectangle':
                $style .= 'border: 1px solid #ccc; ';
                return "<div class='canvas-element' style='" . esc_attr($style) . "'></div>";

            case 'divider':
            case 'line':
                $line_color = $element['lineColor'] ?? '#64748b';
                $line_width = $element['lineWidth'] ?? 2;
                $style .= "border-bottom: {$line_width}px solid {$line_color}; height: {$line_width}px;";
                return "<div class='canvas-element' style='" . esc_attr($style) . "'></div>";

            default:
                return "<div class='canvas-element' style='" . esc_attr($style) . "; background: #ffe6e6; border: 1px solid #ff0000; display: flex; align-items: center; justify-content: center; color: #ff0000;'>{$type}</div>";
        }
    }

    /**
     * Remplacer les variables de commande
     */
    private function replace_order_variables($content, $order) {
        // Variables simples
        $content = str_replace('{{order_id}}', $order->get_id(), $content);
        $content = str_replace('{{order_number}}', $order->get_order_number(), $content);
        $content = str_replace('{{order_date}}', $order->get_date_created()->date('d/m/Y'), $content);
        $content = str_replace('{{order_total}}', $order->get_formatted_order_total(), $content);

        // Informations client
        $content = str_replace('{{customer_name}}', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), $content);
        $content = str_replace('{{customer_email}}', $order->get_billing_email(), $content);

        return $content;
    }
}