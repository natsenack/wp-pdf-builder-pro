<?php

/**
 * PDF Builder Pro - PDF HTML Generator
 * Responsable de la génération du HTML unifié pour les PDFs
 */

namespace PDF_Builder\Admin\Generators;

// Déclarations des fonctions WordPress pour l'IDE
if (!function_exists('esc_html')) {
    function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr($text) { return htmlspecialchars($text, ENT_COMPAT, 'UTF-8'); }
}
if (!function_exists('get_theme_mod')) {
    function get_theme_mod($name, $default = false) { return $default; }
}
if (!function_exists('wp_get_attachment_image_url')) {
    function wp_get_attachment_image_url($attachment_id, $size = 'thumbnail') { return ''; }
}
if (!function_exists('nl2br')) {
    function nl2br($string, $is_xhtml = true) { return str_replace(["\r\n", "\r", "\n"], $is_xhtml ? "<br />\n" : "<br>\n", $string); }
}

class PdfHtmlGenerator
{
    private $admin;

    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    public function generateUnifiedHtml($template, $order = null)
    {
        // Récupérer les paramètres canvas
        $canvas_bg_color = pdf_builder_get_option('pdf_builder_canvas_bg_color', '#ffffff');
        $canvas_border_color = pdf_builder_get_option('pdf_builder_canvas_border_color', '#cccccc');
        $canvas_border_width = intval(pdf_builder_get_option('pdf_builder_canvas_border_width', 1));
        $canvas_shadow_enabled = pdf_builder_get_option('pdf_builder_canvas_shadow_enabled', false) == '1';

        // Construire les styles du conteneur
        $container_bg = "background: {$canvas_bg_color};";
        $container_border = $canvas_border_width > 0 ? "border: {$canvas_border_width}px solid {$canvas_border_color};" : "border: none;";
        $container_shadow = $canvas_shadow_enabled ? "box-shadow: 2px 8px 16px rgba(0, 0, 0, 0.3), 0 4px 8px rgba(0, 0, 0, 0.2);" : "box-shadow: none;";

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . ($order ? 'Order #' . $order->get_id() : 'PDF') . '</title>';
        $html .= $this->getStylesCSS($canvas_bg_color, $container_bg, $container_border, $container_shadow);

        // Récupérer les marges d'impression
        $margins = ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20];
        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            if (isset($firstPage['margins'])) {
                $margins = $firstPage['margins'];
            }
        }

        $html .= '</head><body>';
        
        $container_style = sprintf('position: relative; width: 595px; height: 842px; background: %s;', $canvas_bg_color);
        if ($canvas_border_width > 0) {
            $container_style .= sprintf(' border: %dpx solid %s;', $canvas_border_width, $canvas_border_color);
        }
        
        $html .= '<div class="pdf-container" style="' . $container_style . '">';

        // Récupérer les éléments
        $elements = [];
        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            $elements = $firstPage['elements'] ?? [];
        } elseif (isset($template['elements']) && is_array($template['elements'])) {
            $elements = $template['elements'];
        }

        if (is_array($elements)) {
            usort($elements, function ($a, $b) {
                $a_y = $a['position']['y'] ?? $a['y'] ?? 0;
                $b_y = $b['position']['y'] ?? $b['y'] ?? 0;
                if ($a_y === $b_y) {
                    $a_x = $a['position']['x'] ?? $a['x'] ?? 0;
                    $b_x = $b['position']['x'] ?? $b['x'] ?? 0;
                    return $a_x <=> $b_x;
                }
                return $a_y <=> $b_y;
            });

            foreach ($elements as $element) {
                $html .= $this->renderElement($element, $order);
            }
        }

        $html .= '</div></body></html>';
        return $html;
    }

    private function getStylesCSS($canvas_bg_color, $container_bg, $container_border, $container_shadow)
    {
        return '<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            color: #333;
            line-height: 1.4;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .pdf-container {
            position: relative;
            width: 100%;
            height: 100%;
            ' . $container_bg . '
            margin: 0;
            ' . $container_border . '
            ' . $container_shadow . '
        }
        .pdf-element {
            position: absolute;
            box-sizing: border-box;
            z-index: 1;
        }
        .pdf-element.text-element {
            white-space: pre-wrap;
            word-wrap: break-word;
            z-index: 2;
        }
        .pdf-element.image-element img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .pdf-element.table-element {
            border-collapse: collapse;
        }
        .pdf-element.table-element table {
            width: 100%;
            border-collapse: collapse;
        }
        .pdf-element.table-element th,
        .pdf-element.table-element td {
            border: 1px solid #ddd;
            padding: 4px 8px;
            text-align: left;
        }
        .pdf-element.table-element th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .pdf-element.barcode,
        .pdf-element.qrcode {
            font-family: monospace;
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pdf-element.progress-bar {
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
        }
        .pdf-element.progress-bar div {
            height: 100%;
            background: linear-gradient(90deg, #007cba 0%, #005a87 100%);
            border-radius: 8px;
        }
        .pdf-element.watermark {
            opacity: 0.1;
            pointer-events: none;
            z-index: -1;
        }
        .pdf-element.divider {
            background-color: #cccccc;
            height: 2px;
        }
        @media print {
            body {
                margin: 0;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
        </style>';
    }

    private function renderElement($element, $order = null)
    {
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

        // Convertir les coordonnées pour Dompdf
        $x_pt = round($x * 0.75);
        $y_pt = round($y * 0.75);
        $width_pt = round($width * 0.75);
        $height_pt = round($height * 0.75);

        $base_style = sprintf('position: absolute; left: %dpt; top: %dpt; width: %dpt; height: %dpt;', $x_pt, $y_pt, $width_pt, $height_pt);
        
        // Ajouter les styles CSS supplémentaires
        if (isset($element['style'])) {
            if (isset($element['style']['color'])) {
                $base_style .= ' color: ' . $element['style']['color'] . ';';
            }
            if (isset($element['style']['fontSize'])) {
                $font_size_pt = round($element['style']['fontSize'] * 0.75);
                $base_style .= ' font-size: ' . $font_size_pt . 'pt;';
            }
            if (isset($element['style']['fontWeight'])) {
                $base_style .= ' font-weight: ' . $element['style']['fontWeight'] . ';';
            }
            if (isset($element['style']['fillColor'])) {
                $base_style .= ' background-color: ' . $element['style']['fillColor'] . ';';
            }
        }

        // Ajouter les propriétés CSS additionnelles
        if (isset($element['textDecoration']) && $element['textDecoration'] !== 'none') {
            $base_style .= ' text-decoration: ' . $element['textDecoration'] . ';';
        }
        if (isset($element['lineHeight']) && $element['lineHeight']) {
            $base_style .= ' line-height: ' . \floatval($element['lineHeight']) . ';';
        }
        if (isset($element['borderStyle']) && $element['borderStyle'] !== 'solid') {
            $base_style .= ' border-style: ' . $element['borderStyle'] . ';';
        }
        if (isset($element['shadow']) && $element['shadow']) {
            $offsetX = \floatval($element['shadowOffsetX'] ?? 2);
            $offsetY = \floatval($element['shadowOffsetY'] ?? 2);
            $shadowColor = $element['shadowColor'] ?? 'rgba(0,0,0,0.2)';
            $base_style .= ' box-shadow: ' . $offsetX . 'px ' . $offsetY . 'px 4px ' . $shadowColor . ';';
        }
        if (isset($element['rotation']) && $element['rotation'] !== 0 && pdf_builder_get_option('pdf_builder_canvas_rotate_enabled', '0') == '1') {
            $rotation = \floatval($element['rotation']);
            $base_style .= ' transform: rotate(' . $rotation . 'deg);';
        }
        if (isset($element['scale']) && $element['scale'] !== 100) {
            $scale = \floatval($element['scale']) / 100;
            $base_style .= ' transform: scale(' . $scale . ');';
        }

        $style = $base_style;
        $safe_style = esc_attr($style);
        $content = $element['content'] ?? '';

        $html = '';

        switch ($element['type']) {
            case 'text':
            case 'dynamic_text':
                $final_content = $order ? $this->admin->getHtmlRenderer()->replaceOrderVariables($content, $order) : $content;
                $html = sprintf('<div class="pdf-element text-element" style="%s">%s</div>', $safe_style, esc_html($final_content));
                break;

            case 'multiline_text':
                $final_content = $order ? $this->admin->getHtmlRenderer()->replaceOrderVariables($content, $order) : $content;
                $html = sprintf('<div class="pdf-element text-element" style="%s">%s</div>', $safe_style, nl2br(esc_html($final_content)));
                break;

            case 'mentions':
                $mentions = $this->buildMentions($element);
                $separator = isset($element['separator']) ? $element['separator'] : ' • ';
                $mentions_text = implode($separator, $mentions);
                $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($mentions_text));
                break;

            case 'order_date':
            case 'invoice_date':
                if ($order) {
                    $date = $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : date('d/m/Y');
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($date));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($content ?: 'Date'));
                }
                break;

            case 'invoice_number':
                if ($order) {
                    $invoice_number = $order->get_id() . '-' . time();
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($invoice_number));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($content ?: 'N° de facture'));
                }
                break;

            case 'order_number':
                if ($order) {
                    $order_number = $order->get_order_number();
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($order_number));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($content ?: 'N° de commande'));
                }
                break;

            case 'customer_name':
                if ($order) {
                    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($customer_name));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($content ?: 'Nom du client'));
                }
                break;

            case 'customer_address':
                if ($order) {
                    $address = $this->formatAddress($order, 'billing');
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, nl2br(esc_html($address)));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($content ?: 'Adresse du client'));
                }
                break;

            case 'subtotal':
                if ($order) {
                    $subtotal = $order->get_subtotal();
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, wc_price($subtotal));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($content ?: 'Sous-total'));
                }
                break;

            case 'tax':
                if ($order) {
                    $tax = $order->get_total_tax();
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, wc_price($tax));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($content ?: 'Taxes'));
                }
                break;

            case 'total':
                if ($order) {
                    $total = $order->get_total();
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, wc_price($total));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, esc_html($content ?: 'Total'));
                }
                break;

            case 'rectangle':
                $html = sprintf('<div class="pdf-element" style="%s"></div>', $safe_style);
                break;

            case 'image':
            case 'company_logo':
                $logo_url = $element['imageUrl'] ?? $content;
                if (!$logo_url) {
                    $custom_logo_id = get_theme_mod('custom_logo');
                    if ($custom_logo_id) {
                        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                    }
                }
                if (!$logo_url) {
                    $site_logo_id = get_option('site_logo');
                    if ($site_logo_id) {
                        $logo_url = wp_get_attachment_image_url($site_logo_id, 'full');
                    }
                }
                if ($logo_url) {
                    $html = sprintf('<div class="pdf-element image-element" style="%s"><img src="%s" style="width: 100%%; height: 100%%; object-fit: contain;" alt="Logo" /></div>', $style, \esc_url($logo_url));
                } else {
                    $html = sprintf('<div class="pdf-element image-element" style="%s"><div style="width: 100%%; height: 100%%; background-color: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px;">🏢 Logo</div></div>', $style);
                }
                break;

            case 'product_table':
                if ($order) {
                    $table_style = $element['tableStyle'] ?? 'default';
                    $table_html = $this->admin->generateOrderProductsTable($order, $table_style, $element);
                    $html = '<div class="pdf-element table-element" style="' . $style . '">' . $table_html . '</div>';
                } else {
                    $html = '<div class="pdf-element table-element" style="' . $style . '">' . $this->getSampleProductTable() . '</div>';
                }
                break;

            case 'company_info':
                $company_info = $this->admin->getHtmlRenderer()->formatCompleteCompanyInfo();
                $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $style, nl2br(esc_html($company_info)));
                break;

            case 'document_type':
                if ($order) {
                    $order_status = $order->get_status();
                    $document_type = $this->admin->getDataUtils()->detectDocumentType($order_status);
                    $docType = $this->admin->getDataUtils()->getDocumentTypeLabel($document_type);
                } else {
                    $docType = $content ?: 'Document';
                }
                $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($docType));
                break;

            case 'divider':
                $thickness = $element['thickness'] ?? 2;
                $color = $element['color'] ?? '#cccccc';
                $margin = $element['margin'] ?? 10;
                $divider_style = $style . sprintf('height: %dpx; background-color: %s; margin: %dpx 0;', $thickness, $color, $margin);
                $html = sprintf('<div class="pdf-element divider" style="%s"></div>', $divider_style);
                break;

            case 'watermark':
                $watermark_text = $element['content'] ?? 'CONFIDENTIEL';
                $opacity = isset($element['opacity']) ? $element['opacity'] / 100 : 0.1;
                $style .= sprintf('opacity: %s; color: rgba(0,0,0,%s); font-size: 48px; text-align: center; transform: rotate(-45deg); pointer-events: none;', $opacity, $opacity);
                $html = sprintf('<div class="pdf-element watermark" style="%s">%s</div>', $style, esc_html($watermark_text));
                break;

            case 'progress_bar':
                $progress = $element['progress'] ?? 50;
                $progress_style = $style . sprintf('background: #f0f0f0; border: 1px solid #ccc; border-radius: 10px; overflow: hidden;');
                $bar_style = sprintf('width: %d%%; height: 100%%; background: #007cba; border-radius: 8px;', $progress);
                $html = sprintf('<div class="pdf-element progress-bar" style="%s"><div style="%s"></div></div>', $progress_style, $bar_style);
                break;

            case 'barcode':
                if ($order) {
                    $barcode_data = $order->get_order_number();
                    $html = sprintf('<div class="pdf-element barcode" style="%s">*%s*</div>', $style, esc_html($barcode_data));
                } else {
                    $html = sprintf('<div class="pdf-element barcode" style="%s">*BARCODE*</div>', $style);
                }
                break;

            case 'qrcode':
                if ($order) {
                    $qr_data = 'Order: ' . $order->get_order_number();
                    $html = sprintf('<div class="pdf-element qrcode" style="%s">[QR:%s]</div>', $style, esc_html($qr_data));
                } else {
                    $html = sprintf('<div class="pdf-element qrcode" style="%s">[QR:CODE]</div>', $style);
                }
                break;

            case 'icon':
                $html = sprintf('<div class="pdf-element icon" style="%s">📄</div>', $style);
                break;

            case 'line':
                $line_style = $style . 'border-top: 2px solid #000; height: 0;';
                $html = sprintf('<div class="pdf-element line" style="%s"></div>', $line_style);
                break;

            case 'customer_info':
                $html = $this->renderCustomerInfo($order, $style);
                break;

            case 'subtotal':
                if ($order) {
                    $subtotal = $order->get_subtotal();
                    $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $safe_style, wc_price($subtotal));
                } else {
                    $html = sprintf('<div class="pdf-element" style="%s">Sous-total</div>', $safe_style);
                }
                break;

            default:
                $final_content = $order ? $this->admin->getHtmlRenderer()->replaceOrderVariables($content, $order) : $content;
                $html = sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($final_content ?: $element['type']));
                break;
        }

        return $html;
    }

    private function buildMentions($element)
    {
        $mentions = [];
        if (isset($element['showEmail']) && $element['showEmail']) {
            $email = pdf_builder_get_option('pdf_builder_company_email', '');
            if ($email) {
                $mentions[] = $email;
            }
        }
        if (isset($element['showPhone']) && $element['showPhone']) {
            $phone = pdf_builder_get_option('pdf_builder_company_phone', '');
            if ($phone) {
                $mentions[] = $phone;
            }
        }
        if (isset($element['showSiret']) && $element['showSiret']) {
            $siret = pdf_builder_get_option('pdf_builder_company_siret', '');
            if ($siret) {
                $mentions[] = 'SIRET: ' . $siret;
            }
        }
        if (isset($element['showVat']) && $element['showVat']) {
            $vat = pdf_builder_get_option('pdf_builder_company_vat', '');
            if ($vat) {
                $mentions[] = 'TVA: ' . $vat;
            }
        }
        return $mentions;
    }

    private function renderCustomerInfo($order, $style)
    {
        if ($order) {
            $billing_data = [
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'state' => $order->get_billing_state(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'payment_method' => $order->get_payment_method_title(),
                'transaction_id' => $order->get_transaction_id() ?: '',
            ];

            $full_name = trim($billing_data['first_name'] . ' ' . $billing_data['last_name']);
            $full_address = '';
            if (!empty($full_name)) {
                $full_address .= '<div style="font-weight: bold; margin-bottom: 4px;">' . esc_html($full_name) . '</div>';
            }
            if (!empty($billing_data['company'])) {
                $full_address .= '<div>' . esc_html($billing_data['company']) . '</div>';
            }
            if (!empty($billing_data['address_1'])) {
                $full_address .= '<div>' . esc_html($billing_data['address_1']) . '</div>';
            }
            if (!empty($billing_data['address_2'])) {
                $full_address .= '<div>' . esc_html($billing_data['address_2']) . '</div>';
            }
            $city_line = trim($billing_data['postcode'] . ' ' . $billing_data['city']);
            if (!empty($city_line)) {
                $full_address .= '<div>' . esc_html($city_line) . '</div>';
            }
            $country_line = '';
            if (!empty($billing_data['state'])) {
                $country_line .= $billing_data['state'] . ', ';
            }
            $country_line .= $billing_data['country'];
            if (!empty($country_line)) {
                $full_address .= '<div>' . esc_html($country_line) . '</div>';
            }

            $customer_html = '<div style="padding: 8px; font-size: 12px; line-height: 1.4;">';
            $customer_html .= $full_address;

            if (!empty($billing_data['email'])) {
                $customer_html .= '<div style="margin-top: 4px;">' . esc_html($billing_data['email']) . '</div>';
            }
            if (!empty($billing_data['phone'])) {
                $customer_html .= '<div>' . esc_html($billing_data['phone']) . '</div>';
            }
            if (!empty($billing_data['payment_method'])) {
                $customer_html .= '<div style="margin-top: 4px; font-style: italic;">Paiement: ' . esc_html($billing_data['payment_method']) . '</div>';
            }
            if (!empty($billing_data['transaction_id'])) {
                $customer_html .= '<div style="font-size: 11px; color: #666;">ID: ' . esc_html($billing_data['transaction_id']) . '</div>';
            }

            $customer_html .= '</div>';
            return sprintf('<div class="pdf-element" style="%s">%s</div>', $style, $customer_html);
        } else {
            $customer_html = '<div style="padding: 8px; font-size: 12px; line-height: 1.4;">';
            $customer_html .= '<div style="font-weight: bold; margin-bottom: 4px;">Jean Dupont</div>';
            $customer_html .= '<div>Entreprise Exemple</div>';
            $customer_html .= '<div>123 Rue de la Paix</div>';
            $customer_html .= '<div>Appartement 4B</div>';
            $customer_html .= '<div>75001 Paris</div>';
            $customer_html .= '<div>Île-de-France, France</div>';
            $customer_html .= '<div style="margin-top: 4px;">jean.dupont@example.com</div>';
            $customer_html .= '<div>+33 1 23 45 67 89</div>';
            $customer_html .= '<div style="margin-top: 4px; font-style: italic;">Paiement: Carte de crédit</div>';
            $customer_html .= '<div style="font-size: 11px; color: #666;">ID: TXN123456789</div>';
            $customer_html .= '</div>';
            return sprintf('<div class="pdf-element" style="%s">%s</div>', $style, $customer_html);
        }
    }

    private function getSampleProductTable()
    {
        $table_html = '<table style="width: 100%; border-collapse: collapse; font-size: 11px;">';
        $table_html .= '<thead>';
        $table_html .= '<tr style="background-color: #f8f9fa;">';
        $table_html .= '<th style="border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-weight: bold;">Produit</th>';
        $table_html .= '<th style="border: 1px solid #ddd; padding: 6px 8px; text-align: center; font-weight: bold; width: 60px;">Qté</th>';
        $table_html .= '<th style="border: 1px solid #ddd; padding: 6px 8px; text-align: right; font-weight: bold; width: 80px;">Prix</th>';
        $table_html .= '<th style="border: 1px solid #ddd; padding: 6px 8px; text-align: right; font-weight: bold; width: 80px;">Total</th>';
        $table_html .= '</tr></thead><tbody>';
        $table_html .= '<tr>';
        $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px;">Produit A</td>';
        $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: center;">2</td>';
        $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: right;">19.99€</td>';
        $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: right;">39.98€</td>';
        $table_html .= '</tr>';
        $table_html .= '</tbody></table>';
        return $table_html;
    }

    /**
     * Formate une adresse manuellement pour éviter l'autoloading WooCommerce
     */
    private function formatAddress($order, $type = 'billing')
    {
        $address_parts = array();

        $company = $type === 'billing' ? $order->get_billing_company() : $order->get_shipping_company();
        if (!empty($company)) {
            $address_parts[] = $company;
        }

        $first_name = $type === 'billing' ? $order->get_billing_first_name() : $order->get_shipping_first_name();
        $last_name = $type === 'billing' ? $order->get_billing_last_name() : $order->get_shipping_last_name();
        if (!empty($first_name) || !empty($last_name)) {
            $address_parts[] = trim($first_name . ' ' . $last_name);
        }

        $address_1 = $type === 'billing' ? $order->get_billing_address_1() : $order->get_shipping_address_1();
        if (!empty($address_1)) {
            $address_parts[] = $address_1;
        }

        $address_2 = $type === 'billing' ? $order->get_billing_address_2() : $order->get_shipping_address_2();
        if (!empty($address_2)) {
            $address_parts[] = $address_2;
        }

        $city = $type === 'billing' ? $order->get_billing_city() : $order->get_shipping_city();
        $postcode = $type === 'billing' ? $order->get_billing_postcode() : $order->get_shipping_postcode();
        $city_line = trim($city . ' ' . $postcode);
        if (!empty($city_line)) {
            $address_parts[] = $city_line;
        }

        $country = $type === 'billing' ? $this->getCountryName($order->get_billing_country()) : $this->getCountryName($order->get_shipping_country());
        if (!empty($country)) {
            $address_parts[] = $country;
        }

        return implode("\n", $address_parts);
    }

    /**
     * Get country name from country code
     */
    private function getCountryName($country_code)
    {
        if ((!function_exists('pdf_builder_is_woocommerce_active') || !pdf_builder_is_woocommerce_active()) || !$country_code) {
            return $country_code;
        }

        // Use get_option instead of WC()->countries to avoid autoloading
        $countries = get_option('woocommerce_countries', []);
        if (empty($countries)) {
            $countries = get_option('woocommerce_allowed_countries', []);
        }
        return isset($countries[$country_code]) ? $countries[$country_code] : $country_code;
    }
}


