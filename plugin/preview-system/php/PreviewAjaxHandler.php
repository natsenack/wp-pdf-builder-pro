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
        
        $template_data = [];
        if ($template_id && $template_id > 0) {
            $template_data = self::loadTemplateFromDatabase($template_id);
        }
        
        if (!is_array($template_data) || empty($template_data)) {
            wp_send_json_error('Template non trouve');
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
        
        $html = '<html><body style="font-family: Arial; margin: 20px;">
            <h1>' . htmlspecialchars($template_data['template_name'] ?? 'Template') . '</h1>
            <p>' . htmlspecialchars($template_data['description'] ?? '') . '</p>
            <hr>
            <p><strong>Elements:</strong> ' . (isset($template_data['elements']) ? count($template_data['elements']) : 0) . '</p>
            <p><strong>Dimensions:</strong> ' . ($template_data['canvasWidth'] ?? 'N/A') . ' x ' . ($template_data['canvasHeight'] ?? 'N/A') . '</p>
            <p><small>Genere: ' . date('Y-m-d H:i:s') . '</small></p>
        </body></html>';
        
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
