<?php

namespace PDF_Builder\PreviewSystem;

if (!defined('ABSPATH')) {
    exit('Acces interdit');
}

class PreviewAjaxHandler {
    
    public static function init() {
        add_action('wp_ajax_pdf_builder_generate_preview', [self::class, 'generatePreviewAjax']);
        add_action('wp_ajax_nopriv_pdf_builder_generate_preview', [self::class, 'generatePreviewAjax']);
    }

    public static function generatePreviewAjax() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes', 403);
        }
        
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pdf_builder_nonce')) {
            wp_send_json_error('Nonce invalide', 403);
        }
        
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'pdf';
        
        // D'abord, essayer de charger les données depuis POST (template_data envoyé par le frontend)
        $template_data = [];
        if (isset($_POST['template_data'])) {
            $json_data = sanitize_text_field($_POST['template_data']);
            $decoded = json_decode($json_data, true);
            if (is_array($decoded)) {
                $template_data = $decoded;
            }
        }
        
        // Si pas de template_data dans POST, essayer depuis la DB
        if (empty($template_data) && $template_id && $template_id > 0) {
            $template_data = self::loadTemplateFromDatabase($template_id);
        }
        
        if (!is_array($template_data) || empty($template_data)) {
            wp_send_json_error('Template non trouvé', 400);
        }
        
        $result = self::generatePreview($template_data, $format);
        wp_send_json_success($result);
    }

    private static function loadTemplateFromDatabase(int $template_id): array {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_templates';
        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT id, name, template_data FROM {$table_name} WHERE id = %d", $template_id),
            ARRAY_A
        );
        
        if (!$template) {
            return [];
        }
        
        $template_data = json_decode($template['template_data'], true);
        if (!is_array($template_data)) {
            $template_data = [];
        }
        
        $template_data['template_id'] = $template['id'];
        $template_data['template_name'] = $template['name'];
        
        return $template_data;
    }

    private static function generatePreview(array $template_data, string $format = 'pdf'): array {
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';
        
        if (!class_exists('Dompdf\Dompdf')) {
            return ['error' => 'Dompdf non disponible', 'fallback' => true];
        }
        
        // Construire HTML à partir du template
        $html = self::buildHtmlFromTemplate($template_data);
        
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $pdf_content = $dompdf->output();
        $pdf_url = self::savePdfTemporarily($pdf_content);
        
        return [
            'image_url' => $pdf_url,
            'success' => true,
            'fallback' => false,
            'format' => 'pdf'
        ];
    }
    
    private static function buildHtmlFromTemplate(array $template_data): string {
        $elements = $template_data['elements'] ?? [];
        $canvas_width = intval($template_data['canvasWidth'] ?? 800);
        $canvas_height = intval($template_data['canvasHeight'] ?? 1123);
        
        // Convertir pixels en mm pour PDF (1px ≈ 0.264583mm)
        $mm_per_px = 0.264583;
        $pdf_width = $canvas_width * $mm_per_px;
        $pdf_height = $canvas_height * $mm_per_px;
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .canvas {
            position: relative;
            width: ' . $pdf_width . 'mm;
            height: ' . $pdf_height . 'mm;
            background-color: white;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .element {
            position: absolute;
            overflow: hidden;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .product-table th {
            background-color: #f9fafb;
            color: #111827;
            padding: 8px;
            text-align: left;
            border: 1px solid #e5e7eb;
            font-weight: bold;
        }
        .product-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            color: #374151;
        }
        .product-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .customer-info, .company-info {
            background-color: #e5e7eb;
            padding: 10px;
            font-size: 12px;
            line-height: 1.5;
        }
        .info-label {
            font-weight: bold;
            color: #111827;
        }
        .info-value {
            color: #374151;
        }
        .document-type {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }
        .dynamic-text {
            font-size: 14px;
            line-height: 1.3;
            white-space: pre-wrap;
        }
        .line-element {
            background-color: #000000;
        }
        .mentions {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.2;
        }
        .order-number {
            font-size: 14px;
            color: #374151;
            text-align: right;
        }
        .company-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .order-date {
            font-size: 12px;
            color: #374151;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="canvas">';
        
        // Renderer chaque élément
        foreach ($elements as $element) {
            $type = $element['type'] ?? '';
            $x = ($element['x'] ?? 0) * $mm_per_px;
            $y = ($element['y'] ?? 0) * $mm_per_px;
            $width = ($element['width'] ?? 100) * $mm_per_px;
            $height = ($element['height'] ?? 50) * $mm_per_px;
            
            $style = "position: absolute; left: {$x}mm; top: {$y}mm; width: {$width}mm; height: {$height}mm;";
            
            // Appliquer les styles du JSON
            if (!empty($element['backgroundColor']) && $element['backgroundColor'] !== 'transparent') {
                $style .= "background-color: {$element['backgroundColor']};";
            }
            if (!empty($element['borderWidth']) && $element['borderWidth'] > 0) {
                $style .= "border: {$element['borderWidth']}px solid {$element['borderColor']};";
            }
            if (!empty($element['textColor'])) {
                $style .= "color: {$element['textColor']};";
            }
            if (!empty($element['fontSize'])) {
                $style .= "font-size: {$element['fontSize']}px;";
            }
            if (!empty($element['fontWeight'])) {
                $style .= "font-weight: {$element['fontWeight']};";
            }
            if (!empty($element['textAlign'])) {
                $style .= "text-align: {$element['textAlign']};";
            }
            if (!empty($element['padding'])) {
                $style .= "padding: {$element['padding']}px;";
            }
            
            $html .= '<div class="element ' . htmlspecialchars($type) . '" style="' . $style . '">';
            
            // Rendu selon le type
            switch ($type) {
                case 'product_table':
                    $html .= '<table class="product-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Qty</th>
                                <th>Prix Unit.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Produit exemple</td>
                                <td>1</td>
                                <td>100.00 €</td>
                                <td>100.00 €</td>
                            </tr>
                        </tbody>
                    </table>';
                    break;
                    
                case 'customer_info':
                    $html .= '<div class="customer-info">
                        <div class="info-label">Informations Client</div>
                        <div class="info-value">Nom: [Customer Name]</div>
                        <div class="info-value">Adresse: [Customer Address]</div>
                        <div class="info-value">Email: [Customer Email]</div>
                        <div class="info-value">Téléphone: [Customer Phone]</div>
                    </div>';
                    break;
                    
                case 'company_info':
                    $html .= '<div class="company-info">
                        <div class="info-label">Infos Entreprise</div>
                        <div class="info-value">[Company Name]</div>
                        <div class="info-value">[Company Address]</div>
                        <div class="info-value">Email: [Company Email]</div>
                        <div class="info-value">Tél: [Company Phone]</div>
                        <div class="info-value">SIRET: [SIRET]</div>
                        <div class="info-value">TVA: [VAT]</div>
                    </div>';
                    break;
                    
                case 'document_type':
                    $html .= '<div class="document-type">' . htmlspecialchars($element['title'] ?? 'DOCUMENT') . '</div>';
                    break;
                    
                case 'line':
                    $html .= '<div class="line-element" style="width: 100%; height: ' . ($element['strokeWidth'] ?? 1) . 'px; background-color: ' . ($element['strokeColor'] ?? '#000000') . ';"></div>';
                    break;
                    
                case 'dynamic-text':
                    $html .= '<div class="dynamic-text">' . htmlspecialchars($element['text'] ?? '') . '</div>';
                    break;
                    
                case 'mentions':
                    $html .= '<div class="mentions">
                        Email • Téléphone • SIRET • TVA
                    </div>';
                    break;
                    
                case 'order_number':
                    $html .= '<div class="order-number">
                        <strong>Commande:</strong> [Order #]<br>
                    </div>';
                    break;
                    
                case 'company_logo':
                    if (!empty($element['src'])) {
                        $html .= '<img src="' . htmlspecialchars($element['src']) . '" alt="Logo" class="company-logo">';
                    }
                    break;
                    
                case 'woocommerce_order_date':
                    $html .= '<div class="order-date">
                        Date: ' . date('d/m/Y') . '
                    </div>';
                    break;
                    
                default:
                    $html .= '<div>[Element: ' . htmlspecialchars($type) . ']</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '
    </div>
</body>
</html>';
        
        return $html;
    }

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
}
