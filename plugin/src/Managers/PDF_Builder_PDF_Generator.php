<?php

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - PDF Generator Manager
 * Gestion centralisée de la génération PDF
 */

class PDF_Builder_PDF_Generator
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->init_hooks();
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks()
    {
        // AJAX handlers pour la génération PDF
        add_action('wp_ajax_pdf_builder_download_pdf', [$this, 'ajax_download_pdf']);
    }



    /**
     * AJAX - Prévisualiser PDF
     */

    /**
     * AJAX - Télécharger PDF
     */
    public function ajax_download_pdf()
    {
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
                wp_send_json_success(
                    array(
                    'path' => $pdf_path,
                    'filename' => $filename
                    )
                );
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Génération unifiée (pour le canvas editor)
     */

    /**
     * Générer PDF depuis les données template
     */
    private function generate_pdf_from_template_data($template, $filename)
    {
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

        // Générer le PDF avec Dompdf
        require_once WP_PLUGIN_DIR . '/wp-pdf-builder-pro/plugin/vendor/autoload.php';

        $dompdf = new Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('defaultFont', 'Arial');

        // Configuration PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Sauvegarder le PDF
        file_put_contents($pdf_path, $dompdf->output());

        return $pdf_path;
    }

    /**
     * Générer HTML depuis les données template
     */
    private function generate_html_from_template_data($template)
    {
        // Utiliser la même fonction que la génération commande pour la cohérence
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
    private function generate_unified_html_legacy($template, $order = null)
    {
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
                    $x,
                    $y,
                    $width,
                    $height
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
    private function convert_elements_to_template($elements)
    {
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
    private function generate_html_from_elements($elements)
    {
        // Vérifier que des éléments sont fournis
        if (empty($elements)) {
            throw new Exception('Aucun élément fourni pour la génération du PDF');
        }

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Document</title>
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
    private function render_element_to_html($element)
    {
        $type = $element['type'] ?? 'text';

        // Extraire les coordonnées
        $x = $element['position']['x'] ?? $element['x'] ?? 0;
        $y = $element['position']['y'] ?? $element['y'] ?? 0;
        $width = $element['size']['width'] ?? $element['width'] ?? 100;
        $height = $element['size']['height'] ?? $element['height'] ?? 50;

        // Dimensions par défaut si manquantes
        if (empty($width) || $width <= 0) {
            $width = 100;
        }
        if (empty($height) || $height <= 0) {
            $height = 50;
        }

        // Contraindre dans les limites A4 (595x842 pixels)
        $canvas_width = 595;
        $canvas_height = 842;
        $x = max(0, min($canvas_width - $width, $x));
        $y = max(0, min($canvas_height - $height, $y));

        $style = sprintf(
            'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
            $x,
            $y,
            $width,
            $height
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
     * Générer un PDF avec Dompdf pour un rendu fidèle
     */

    /**
     * Rendre un élément dans le PDF avec Dompdf
     */

    /**
     * Convertir une couleur hexadécimale en RGB
     */

    /**
     * Remplacer les variables de commande
     */
    private function replace_order_variables($content, $order)
    {
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
