<?php
/**
 * PDF_Builder_Preview_Image_Controller
 *
 * Génère des images PNG/JPG d'aperçu PDF côté serveur
 * Utilise TCPDF pour le rendu réel des éléments
 *
 * @package PDF_Builder_Pro
 * @since 1.1.0
 */

namespace PDF_Builder_Pro\Controllers;

use PDF_Builder_Pro\Managers\PDF_Builder_WooCommerce_Integration;
use PDF_Builder_Pro\Renderers\PreviewRenderer;

class PDF_Builder_Preview_Image_Controller
{
    private $woo_integration;
    private $renderer;

    public function __construct()
    {
        $this->woo_integration = new PDF_Builder_WooCommerce_Integration();
        add_action('wp_ajax_pdf_builder_generate_preview_image', [$this, 'ajax_generate_preview_image']);
    }

    /**
     * Génère une image PNG d'aperçu du PDF
     *
     * @return void
     */
    public function ajax_generate_preview_image()
    {
        try {
            // Vérifier les permissions
            if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            $nonce = $_POST['nonce'] ?? '';
            if (!wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
                wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider les entrées
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
            $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'png'; // png, jpg

            if (!$order_id || !$template_id) {
                wp_send_json_error('Paramètres manquants');
                return;
            }

            // Valider le format
            if (!in_array($format, ['png', 'jpg', 'pdf'])) {
                $format = 'png';
            }

            // Récupérer les données de prévisualisation
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            $template = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id)
            );

            if (!$template) {
                wp_send_json_error('Template non trouvé');
                return;
            }

            // Décoder les données du template
            $template_data = json_decode($template->data, true);
            if (!$template_data) {
                wp_send_json_error('Données du template invalides');
                return;
            }

            // Récupérer la commande WooCommerce
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error('Commande non trouvée');
                return;
            }

            // Générer l'image d'aperçu
            $preview_image = $this->generate_preview_image(
                $order,
                $template_data,
                $format
            );

            if (!$preview_image) {
                wp_send_json_error('Impossible de générer l\'aperçu');
                return;
            }

            // Retourner l'image en base64
            wp_send_json_success([
                'image' => 'data:image/' . $format . ';base64,' . base64_encode($preview_image),
                'format' => $format,
                'type' => 'image/' . $format
            ]);

        } catch (\Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Génère une image PNG/JPG de l'aperçu
     *
     * @param \WC_Order $order La commande WooCommerce
     * @param array $template_data Données du template
     * @param string $format Format de sortie (png, jpg)
     * @return string|false Image en tant que chaîne binaire
     */
    private function generate_preview_image($order, $template_data, $format = 'png')
    {
        require_once PDF_BUILDER_PRO_PLUGIN_DIR . '/vendor/autoload.php';

        try {
            // Créer une instance TCPDF pour le rendu
            $pdf = new \TCPDF(
                $template_data['canvas']['orientation'] ?? 'P',
                'mm',
                $template_data['canvas']['pageFormat'] ?? 'A4'
            );

            // Configuration du PDF
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);
            $pdf->AddPage();

            // Couleur de fond
            $bgcolor = $template_data['canvas']['backgroundColor'] ?? '#ffffff';
            $this->set_pdf_color($pdf, $bgcolor, 'fill');
            $pdf->Rect(0, 0, 210, 297, 'F'); // A4 dimensions en mm

            // Rendu des éléments
            $elements = $template_data['elements'] ?? [];
            foreach ($elements as $element) {
                $this->render_element_on_pdf($pdf, $element, $order);
            }

            // Générer l'image d'aperçu
            if ($format === 'jpg') {
                // Utiliser imagick si disponible pour JPG
                return $this->pdf_to_image($pdf, 'jpg');
            } else {
                // PNG par défaut
                return $this->pdf_to_image($pdf, 'png');
            }

        } catch (\Exception $e) {
            error_log('Preview image generation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Rend un élément sur le PDF
     *
     * @param \TCPDF $pdf Instance TCPDF
     * @param array $element Données de l'élément
     * @param \WC_Order $order Commande WooCommerce
     * @return void
     */
    private function render_element_on_pdf($pdf, $element, $order)
    {
        $type = $element['type'] ?? 'text';
        $x = $element['x'] ?? 10;
        $y = $element['y'] ?? 10;
        $width = $element['width'] ?? 50;
        $height = $element['height'] ?? 10;

        // Convertir les pixels en mm (96 DPI = 25.4mm)
        $x_mm = $x / 3.78;
        $y_mm = $y / 3.78;
        $w_mm = $width / 3.78;
        $h_mm = $height / 3.78;

        switch ($type) {
            case 'rectangle':
                $this->render_rectangle($pdf, $element, $x_mm, $y_mm, $w_mm, $h_mm);
                break;

            case 'text':
            case 'dynamic-text':
                $this->render_text($pdf, $element, $order, $x_mm, $y_mm, $w_mm, $h_mm);
                break;

            case 'company_logo':
                $this->render_logo($pdf, $element, $x_mm, $y_mm, $w_mm, $h_mm);
                break;

            case 'order_number':
                $this->render_order_number($pdf, $element, $order, $x_mm, $y_mm, $w_mm, $h_mm);
                break;

            case 'product_table':
                $this->render_product_table($pdf, $element, $order, $x_mm, $y_mm, $w_mm, $h_mm);
                break;

            case 'customer_info':
                $this->render_customer_info($pdf, $element, $order, $x_mm, $y_mm, $w_mm, $h_mm);
                break;

            case 'company_info':
                $this->render_company_info($pdf, $element, $x_mm, $y_mm, $w_mm, $h_mm);
                break;

            case 'line':
                $this->render_line($pdf, $element, $x_mm, $y_mm, $w_mm, $h_mm);
                break;
        }
    }

    /**
     * Rend un rectangle sur le PDF
     */
    private function render_rectangle($pdf, $element, $x, $y, $w, $h)
    {
        $fill_color = $element['fillColor'] ?? $element['backgroundColor'] ?? '#ffffff';
        $stroke_color = $element['strokeColor'] ?? $element['borderColor'] ?? '#000000';
        $stroke_width = $element['strokeWidth'] ?? $element['borderWidth'] ?? 0.5;

        $this->set_pdf_color($pdf, $fill_color, 'fill');
        $this->set_pdf_color($pdf, $stroke_color, 'stroke');
        $pdf->SetLineWidth($stroke_width);

        $pdf->Rect($x, $y, $w, $h, 'FD');
    }

    /**
     * Rend du texte sur le PDF avec variables remplacées
     */
    private function render_text($pdf, $element, $order, $x, $y, $w, $h)
    {
        $text = $element['text'] ?? '';
        $text = $this->replace_variables($text, $order);

        $font_size = $element['fontSize'] ?? 12;
        $font_family = $element['fontFamily'] ?? 'Arial';
        $color = $element['color'] ?? $element['textColor'] ?? '#000000';
        $align = $element['textAlign'] ?? $element['align'] ?? 'L';

        $this->set_pdf_color($pdf, $color, 'text');
        $pdf->SetFont($font_family, '', $font_size / 2.834); // Convertir pixels en pt
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $h, $text, 0, $align);
    }

    /**
     * Rend le tableau des produits
     */
    private function render_product_table($pdf, $element, $order, $x, $y, $w, $h)
    {
        $font_size = $element['fontSize'] ?? 10;
        $this->set_pdf_color($pdf, '#000000', 'text');
        $pdf->SetFont('Arial', '', $font_size / 2.834);

        // En-têtes du tableau
        $headers = ['Produit', 'Quantité', 'Prix', 'Total'];
        $col_widths = [$w * 0.5, $w * 0.15, $w * 0.175, $w * 0.175];

        // Dessiner le tableau
        $current_y = $y;
        $line_height = $h / 5;

        // En-têtes
        $this->set_pdf_color($pdf, '#333333', 'fill');
        $pdf->SetFillColor(51, 51, 51);
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->SetXY($x + array_sum(array_slice($col_widths, 0, $i)), $current_y);
            $pdf->Cell($col_widths[$i], $line_height, $headers[$i], 1, 0, 'C', true);
        }

        $current_y += $line_height;

        // Lignes de produits
        $this->set_pdf_color($pdf, '#000000', 'text');
        $pdf->SetFillColor(255, 255, 255);
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) continue;

            $data = [
                $product->get_name(),
                $item->get_quantity(),
                wc_price($product->get_price()),
                wc_price($item->get_total())
            ];

            for ($i = 0; $i < count($data); $i++) {
                $pdf->SetXY($x + array_sum(array_slice($col_widths, 0, $i)), $current_y);
                $pdf->Cell($col_widths[$i], $line_height, $data[$i], 1, 0, 'L', false);
            }

            $current_y += $line_height;
        }

        // Total
        $pdf->SetXY($x + $col_widths[0] + $col_widths[1], $current_y);
        $pdf->Cell($col_widths[2], $line_height, 'Total:', 1, 0, 'R');
        $pdf->Cell($col_widths[3], $line_height, wc_price($order->get_total()), 1, 0, 'R');
    }

    /**
     * Rend les infos client
     */
    private function render_customer_info($pdf, $element, $order, $x, $y, $w, $h)
    {
        $text = sprintf(
            "%s\n%s\n%s\n%s %s\n%s",
            $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            $order->get_billing_email(),
            $order->get_billing_phone(),
            $order->get_billing_address_1(),
            $order->get_billing_postcode(),
            $order->get_billing_city()
        );

        $this->set_pdf_color($pdf, $element['color'] ?? '#000000', 'text');
        $pdf->SetFont('Arial', '', ($element['fontSize'] ?? 10) / 2.834);
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $h, $text, 0, 'L');
    }

    /**
     * Rend le numéro de commande
     */
    private function render_order_number($pdf, $element, $order, $x, $y, $w, $h)
    {
        $text = 'CMD-' . $order->get_order_number();
        $this->render_text($pdf, array_merge($element, ['text' => $text]), $order, $x, $y, $w, $h);
    }

    /**
     * Rend le logo de l'entreprise
     */
    private function render_logo($pdf, $element, $x, $y, $w, $h)
    {
        $image_url = $element['src'] ?? $element['imageUrl'] ?? '';

        if ($image_url) {
            try {
                // Si c'est une URL, la télécharger en temporaire
                if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                    $temp_file = download_url($image_url);
                    if (!is_wp_error($temp_file)) {
                        $pdf->Image($temp_file, $x, $y, $w, $h);
                        unlink($temp_file);
                    }
                } else if (file_exists($image_url)) {
                    $pdf->Image($image_url, $x, $y, $w, $h);
                }
            } catch (\Exception $e) {
                error_log('Logo rendering error: ' . $e->getMessage());
            }
        } else {
            // Placeholder gris
            $this->set_pdf_color($pdf, '#e0e0e0', 'fill');
            $pdf->Rect($x, $y, $w, $h, 'F');
            $this->set_pdf_color($pdf, '#999999', 'text');
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY($x, $y + $h / 2 - 2);
            $pdf->Cell($w, 4, 'LOGO', 0, 0, 'C');
        }
    }

    /**
     * Rend les infos de l'entreprise
     */
    private function render_company_info($pdf, $element, $x, $y, $w, $h)
    {
        $company_name = get_option('woocommerce_store_name', 'Mon Entreprise');
        $text = $company_name . "\n+33 1 23 45 67 89\ncontact@example.com";

        $this->set_pdf_color($pdf, $element['color'] ?? '#000000', 'text');
        $pdf->SetFont('Arial', '', ($element['fontSize'] ?? 10) / 2.834);
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $h, $text, 0, 'L');
    }

    /**
     * Rend une ligne horizontale
     */
    private function render_line($pdf, $element, $x, $y, $w, $h)
    {
        $color = $element['color'] ?? $element['borderColor'] ?? '#000000';
        $width = $element['height'] ?? $element['borderWidth'] ?? 0.5;

        $this->set_pdf_color($pdf, $color, 'stroke');
        $pdf->SetLineWidth($width / 2.834);
        $pdf->Line($x, $y + $h / 2, $x + $w, $y + $h / 2);
    }

    /**
     * Définit la couleur du PDF
     */
    private function set_pdf_color($pdf, $hex_color, $type = 'fill')
    {
        $hex = str_replace('#', '', $hex_color);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        if ($type === 'fill') {
            $pdf->SetFillColor($r, $g, $b);
        } elseif ($type === 'stroke') {
            $pdf->SetDrawColor($r, $g, $b);
        } elseif ($type === 'text') {
            $pdf->SetTextColor($r, $g, $b);
        }
    }

    /**
     * Convertit un PDF en image
     */
    private function pdf_to_image($pdf, $format = 'png')
    {
        // Obtenir le contenu PDF
        $pdf_content = $pdf->Output('', 'S');

        // Utiliser Imagick ou autre pour convertir si disponible
        if (extension_loaded('imagick')) {
            try {
                $image = new \Imagick();
                $image->setResolution(150, 150);
                $image->readImageBlob($pdf_content, 'pdf');
                $image->setImageFormat($format);
                return $image->getImageBlob();
            } catch (\Exception $e) {
                error_log('Imagick conversion error: ' . $e->getMessage());
            }
        }

        // Fallback : retourner le PDF tel quel si pas d'Imagick
        return $pdf_content;
    }

    /**
     * Remplace les variables dynamiques dans le texte
     */
    private function replace_variables($text, $order)
    {
        $variables = [
            '{{customer_name}}' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            '{{customer_email}}' => $order->get_billing_email(),
            '{{customer_phone}}' => $order->get_billing_phone(),
            '{{order_number}}' => $order->get_order_number(),
            '{{order_date}}' => $order->get_date_created()->format('d/m/Y'),
            '{{order_total}}' => wc_price($order->get_total()),
        ];

        return str_replace(array_keys($variables), array_values($variables), $text);
    }
}

// Instancier le contrôleur
new PDF_Builder_Preview_Image_Controller();
