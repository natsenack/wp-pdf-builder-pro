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
        $json = json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: monospace;
            font-size: 10px;
            margin: 10px;
            padding: 10px;
            background-color: #f5f5f5;
        }
        pre {
            background-color: white;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h2>Template Data (JSON)</h2>
    <pre>' . htmlspecialchars($json) . '</pre>
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
