<?php
/**
 * AJAX Handler: PDF Preview Image Generator
 * 
 * Cet AJAX handler génère des images PNG d'aperçu du PDF
 * en utilisant TCPDF côté serveur pour un rendu précis
 * 
 * @package PDF_Builder_Pro
 * @subpackage AJAX
 */

// Charger WordPress
if (!function_exists('add_action')) {
    $wp_load = __DIR__ . '/../../wp-load.php';
    if (!file_exists($wp_load)) {
        $wp_load = __DIR__ . '/../../../../wp-load.php';
    }
    if (file_exists($wp_load)) {
        require_once $wp_load;
    } else {
        die('Could not load WordPress');
    }
}

/**
 * Action AJAX pour générer une image de prévisualisation
 */
add_action('wp_ajax_pdf_builder_preview_image', function() {
    // Vérification des permissions
    if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
        wp_send_json_error(['message' => 'Permission denied'], 403);
        return;
    }

    // Vérification du nonce
    $nonce = $_POST['nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'pdf_builder_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce'], 403);
        return;
    }

    // Récupération et validation des paramètres
    $order_id = intval($_POST['order_id'] ?? 0);
    $template_id = intval($_POST['template_id'] ?? 0);

    if (!$order_id || !$template_id) {
        wp_send_json_error(['message' => 'Missing parameters']);
        return;
    }

    // Récupérer la commande WooCommerce
    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error(['message' => 'Order not found']);
        return;
    }

    // Récupérer le template
    global $wpdb;
    $table = $wpdb->prefix . 'pdf_builder_templates';
    $template = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $template_id
    ));

    if (!$template) {
        wp_send_json_error(['message' => 'Template not found']);
        return;
    }

    // Décoder les données du template
    $template_data = json_decode($template->data, true);
    if (!$template_data) {
        wp_send_json_error(['message' => 'Invalid template data']);
        return;
    }

    try {
        // Charger TCPDF
        if (!class_exists('TCPDF')) {
            require_once dirname(__FILE__) . '/vendor/autoload.php';
        }

        // Créer une instance TCPDF
        $pdf = new \TCPDF(
            $template_data['canvas']['orientation'] ?? 'P',
            'mm',
            $template_data['canvas']['pageFormat'] ?? 'A4'
        );

        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        // Fond de page
        $bgcolor = $template_data['canvas']['backgroundColor'] ?? '#ffffff';
        $rgb = pdf_builder_hex_to_rgb($bgcolor);
        $pdf->SetFillColor($rgb['r'], $rgb['g'], $rgb['b']);
        $pdf->Rect(0, 0, 210, 297, 'F');

        // Rendu des éléments
        $elements = $template_data['elements'] ?? [];
        foreach ($elements as $element) {
            pdf_builder_render_element_preview($pdf, $element, $order);
        }

        // Générer une image PNG temporaire
        $temp_file = tempnam(sys_get_temp_dir(), 'pdf_preview_');
        $pdf->Output($temp_file, 'F');

        // Convertir en image si Imagick est disponible
        if (extension_loaded('imagick')) {
            $image = new \Imagick();
            $image->setResolution(150, 150);
            $image->readImage($temp_file);
            $image->setImageFormat('png');
            $image_data = $image->getImageBlob();
        } else {
            // Fallback: retourner le PDF
            $image_data = file_get_contents($temp_file);
        }

        // Nettoyer
        @unlink($temp_file);

        // Retourner l'image en base64
        wp_send_json_success([
            'image' => 'data:image/png;base64,' . base64_encode($image_data),
            'format' => 'png'
        ]);

    } catch (Exception $e) {
        error_log('PDF Preview Error: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Rendering error: ' . $e->getMessage()]);
    }
});

/**
 * Convertit une couleur HEX en RGB
 */
function pdf_builder_hex_to_rgb($hex) {
    $hex = str_replace('#', '', $hex);
    return [
        'r' => hexdec(substr($hex, 0, 2)),
        'g' => hexdec(substr($hex, 2, 2)),
        'b' => hexdec(substr($hex, 4, 2))
    ];
}

/**
 * Rend un élément sur le PDF
 */
function pdf_builder_render_element_preview($pdf, $element, $order) {
    $type = $element['type'] ?? 'text';
    $x = floatval($element['x'] ?? 10) / 3.78; // Conversion pixels -> mm
    $y = floatval($element['y'] ?? 10) / 3.78;
    $w = floatval($element['width'] ?? 50) / 3.78;
    $h = floatval($element['height'] ?? 10) / 3.78;

    switch ($type) {
        case 'rectangle':
            pdf_builder_render_rectangle($pdf, $element, $x, $y, $w, $h);
            break;

        case 'text':
        case 'dynamic-text':
            pdf_builder_render_text($pdf, $element, $order, $x, $y, $w, $h);
            break;

        case 'product_table':
            pdf_builder_render_product_table($pdf, $element, $order, $x, $y, $w, $h);
            break;

        case 'company_logo':
            pdf_builder_render_logo($pdf, $element, $x, $y, $w, $h);
            break;

        case 'customer_info':
            pdf_builder_render_customer_info($pdf, $element, $order, $x, $y, $w, $h);
            break;

        case 'company_info':
            pdf_builder_render_company_info($pdf, $element, $x, $y, $w, $h);
            break;
    }
}

function pdf_builder_render_rectangle($pdf, $element, $x, $y, $w, $h) {
    $fill_color = $element['fillColor'] ?? $element['backgroundColor'] ?? '#ffffff';
    $stroke_color = $element['strokeColor'] ?? $element['borderColor'] ?? '#000000';
    $stroke_width = floatval($element['strokeWidth'] ?? $element['borderWidth'] ?? 0.5);

    $fill_rgb = pdf_builder_hex_to_rgb($fill_color);
    $stroke_rgb = pdf_builder_hex_to_rgb($stroke_color);

    $pdf->SetFillColor($fill_rgb['r'], $fill_rgb['g'], $fill_rgb['b']);
    $pdf->SetDrawColor($stroke_rgb['r'], $stroke_rgb['g'], $stroke_rgb['b']);
    $pdf->SetLineWidth($stroke_width);

    $pdf->Rect($x, $y, $w, $h, 'FD');
}

function pdf_builder_render_text($pdf, $element, $order, $x, $y, $w, $h) {
    $text = $element['text'] ?? '';
    $text = pdf_builder_replace_variables($text, $order);

    $font_size = floatval($element['fontSize'] ?? 12) / 2.834; // Pixels -> Points
    $font_family = $element['fontFamily'] ?? 'Arial';
    $color = $element['color'] ?? $element['textColor'] ?? '#000000';
    $align = $element['textAlign'] ?? $element['align'] ?? 'L';

    $rgb = pdf_builder_hex_to_rgb($color);
    $pdf->SetTextColor($rgb['r'], $rgb['g'], $rgb['b']);
    $pdf->SetFont($font_family, '', $font_size);
    $pdf->SetXY($x, $y);
    $pdf->MultiCell($w, $h, $text, 0, $align);
}

function pdf_builder_render_product_table($pdf, $element, $order, $x, $y, $w, $h) {
    $font_size = floatval($element['fontSize'] ?? 10) / 2.834;
    $pdf->SetFont('Arial', '', $font_size);
    $pdf->SetTextColor(0, 0, 0);

    $headers = ['Produit', 'Quantité', 'Prix', 'Total'];
    $col_widths = [$w * 0.5, $w * 0.15, $w * 0.175, $w * 0.175];

    $current_y = $y;
    $line_height = 7;

    // En-têtes
    $pdf->SetFillColor(51, 51, 51);
    $pdf->SetTextColor(255, 255, 255);
    for ($i = 0; $i < count($headers); $i++) {
        $col_x = $x + array_sum(array_slice($col_widths, 0, $i));
        $pdf->SetXY($col_x, $current_y);
        $pdf->Cell($col_widths[$i], $line_height, $headers[$i], 1, 0, 'C', true);
    }

    $current_y += $line_height;

    // Lignes
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product) continue;

        $data = [
            $product->get_name(),
            $item->get_quantity(),
            wc_price($product->get_price(), ['echo' => false]),
            wc_price($item->get_total(), ['echo' => false])
        ];

        for ($i = 0; $i < count($data); $i++) {
            $col_x = $x + array_sum(array_slice($col_widths, 0, $i));
            $pdf->SetXY($col_x, $current_y);
            $pdf->Cell($col_widths[$i], $line_height, strip_tags($data[$i]), 1, 0, 'L', false);
        }

        $current_y += $line_height;
    }

    // Total
    $total_col_x = $x + $col_widths[0] + $col_widths[1];
    $pdf->SetXY($total_col_x, $current_y);
    $pdf->Cell($col_widths[2], $line_height, 'Total:', 1, 0, 'R');
    $pdf->Cell($col_widths[3], $line_height, strip_tags(wc_price($order->get_total(), ['echo' => false])), 1, 0, 'R');
}

function pdf_builder_render_logo($pdf, $element, $x, $y, $w, $h) {
    $image_url = $element['src'] ?? $element['imageUrl'] ?? '';

    if ($image_url && filter_var($image_url, FILTER_VALIDATE_URL)) {
        try {
            $temp_file = download_url($image_url, 60);
            if (!is_wp_error($temp_file)) {
                @$pdf->Image($temp_file, $x, $y, $w, $h);
                @unlink($temp_file);
            }
        } catch (Exception $e) {
            error_log('Logo error: ' . $e->getMessage());
        }
    }
}

function pdf_builder_render_customer_info($pdf, $element, $order, $x, $y, $w, $h) {
    $text = sprintf(
        "%s\n%s\n%s\n%s %s\n%s",
        $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        $order->get_billing_email(),
        $order->get_billing_phone(),
        $order->get_billing_address_1(),
        $order->get_billing_postcode(),
        $order->get_billing_city()
    );

    $font_size = floatval($element['fontSize'] ?? 10) / 2.834;
    $pdf->SetFont('Arial', '', $font_size);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($x, $y);
    $pdf->MultiCell($w, $h, $text, 0, 'L');
}

function pdf_builder_render_company_info($pdf, $element, $x, $y, $w, $h) {
    $company_name = get_option('woocommerce_store_name', 'Ma Société');
    $text = $company_name . "\n+33 1 23 45 67 89\ncontact@example.com";

    $font_size = floatval($element['fontSize'] ?? 10) / 2.834;
    $pdf->SetFont('Arial', '', $font_size);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($x, $y);
    $pdf->MultiCell($w, $h, $text, 0, 'L');
}

function pdf_builder_replace_variables($text, $order) {
    return str_replace(
        [
            '{{customer_name}}',
            '{{customer_email}}',
            '{{customer_phone}}',
            '{{order_number}}',
            '{{order_date}}',
            '{{order_total}}'
        ],
        [
            $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            $order->get_billing_email(),
            $order->get_billing_phone(),
            $order->get_order_number(),
            $order->get_date_created()->format('d/m/Y'),
            strip_tags(wc_price($order->get_total(), ['echo' => false]))
        ],
        $text
    );
}
