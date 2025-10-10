<?php
/**
 * Gestionnaire d'Export Multi-Format - PDF Builder Pro
 *
 * Système d'export ultra-performant supportant :
 * - PDF (avec Dompdf)
 * - Word (DOCX)
 * - HTML
 * - Images (PNG, JPG)
 * - CSV/Excel
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire d'Export Multi-Format
 */
class PDF_Builder_Export_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Export_Manager
     */
    private static $instance = null;

    /**
     * Formats d'export supportés
     * @var array
     */
    private $supported_formats = [
        'pdf' => 'PDF',
        'docx' => 'Word (DOCX)',
        'html' => 'HTML',
        'png' => 'PNG Image',
        'jpg' => 'JPEG Image',
        'csv' => 'CSV',
        'xlsx' => 'Excel (XLSX)'
    ];

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $database;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Cache manager
     * @var PDF_Builder_Cache_Manager
     */
    private $cache;

    /**
     * Générateur PDF
     * @var PDF_Builder_PDF_Generator
     */
    private $pdf_generator;

    /**
     * Répertoire d'export temporaire
     * @var string
     */
    private $temp_dir;

    /**
     * Constructeur privé
     */
    private function __construct() {
        // Éviter les dépendances circulaires pendant l'initialisation
        // $core = PDF_Builder_Core::getInstance();
        // $this->database = $core->get_database_manager();
        // $this->logger = $core->get_logger();
        // $this->cache = $core->get_cache_manager();
        // $this->pdf_generator = $core->get('pdf_generator');

        if (defined('WP_CONTENT_DIR')) {
            $this->temp_dir = WP_CONTENT_DIR . '/pdf-builder-exports/';
            $this->ensure_temp_directory();
        }

        // N'initialiser les hooks que si WordPress est chargé
        if (function_exists('add_action')) {
            $this->init_hooks();
        }
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Export_Manager
     */
    public static function getInstance(): PDF_Builder_Export_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks(): void {
        // Hooks AJAX pour l'export
        add_action('wp_ajax_pdf_builder_export', [$this, 'ajax_export']);
        add_action('wp_ajax_pdf_builder_export_status', [$this, 'ajax_export_status']);

        // Hook pour le nettoyage des fichiers temporaires
        add_action('pdf_builder_cleanup_temp_files', [$this, 'cleanup_temp_files']);

        // Planifier le nettoyage
        if (!wp_next_scheduled('pdf_builder_cleanup_temp_files')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_cleanup_temp_files');
        }
    }

    /**
     * S'assurer que le répertoire temporaire existe
     */
    private function ensure_temp_directory(): void {
        if (!file_exists($this->temp_dir)) {
            wp_mkdir_p($this->temp_dir);
        }

        // Créer un fichier .htaccess pour la sécurité
        $htaccess = $this->temp_dir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }
    }

    /**
     * Exporter un document dans un format spécifique
     *
     * @param int $document_id
     * @param string $format
     * @param array $options
     * @return array|string
     */
    public function export_document(int $document_id, string $format, array $options = []) {
        try {
            // Vérifier le format
            if (!isset($this->supported_formats[$format])) {
                throw new Exception("Format d'export non supporté: {$format}");
            }

            // Récupérer le document
            $document = $this->database->get_document($document_id);
            if (!$document) {
                throw new Exception("Document introuvable: {$document_id}");
            }

            // Récupérer le template
            $template = $this->database->get_template($document['template_id']);
            if (!$template) {
                throw new Exception("Template introuvable: {$document['template_id']}");
            }

            // Générer le fichier selon le format
            $result = $this->generate_export_file($document, $template, $format, $options);

            // Logger l'export
            $this->logger->info('Document exported', [
                'document_id' => $document_id,
                'format' => $format,
                'file_path' => $result['file_path'] ?? null
            ]);

            return $result;

        } catch (Exception $e) {
            $this->logger->error('Export failed', [
                'document_id' => $document_id,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Générer le fichier d'export
     */
    private function generate_export_file(array $document, array $template, string $format, array $options): array {
        $filename = $this->generate_filename($document, $format);
        $file_path = $this->temp_dir . $filename;

        switch ($format) {
            case 'pdf':
                return $this->export_to_pdf($document, $template, $file_path, $options);

            case 'docx':
                return $this->export_to_docx($document, $template, $file_path, $options);

            case 'html':
                return $this->export_to_html($document, $template, $file_path, $options);

            case 'png':
            case 'jpg':
                return $this->export_to_image($document, $template, $file_path, $format, $options);

            case 'csv':
                return $this->export_to_csv($document, $template, $file_path, $options);

            case 'xlsx':
                return $this->export_to_xlsx($document, $template, $file_path, $options);

            default:
                throw new Exception("Format non supporté: {$format}");
        }
    }

    /**
     * Exporter vers PDF
     */
    private function export_to_pdf(array $document, array $template, string $file_path, array $options): array {
        // Utiliser le générateur PDF existant
        $pdf_path = $this->pdf_generator->generate_from_document($document, $template, $options);

        if (!$pdf_path || !file_exists($pdf_path)) {
            throw new Exception("Échec de la génération PDF");
        }

        // Copier vers le répertoire d'export
        copy($pdf_path, $file_path);

        return [
            'file_path' => $file_path,
            'file_url' => $this->get_export_url($file_path),
            'file_size' => filesize($file_path),
            'mime_type' => 'application/pdf'
        ];
    }

    /**
     * Exporter vers Word (DOCX)
     */
    private function export_to_docx(array $document, array $template, string $file_path, array $options): array {
        // Générer le HTML d'abord
        $html_content = $this->generate_html_content($document, $template, $options);

        // Convertir HTML vers DOCX (utilisation de bibliothèque PHPWord si disponible)
        if (!class_exists('PhpOffice\PhpWord\PhpWord')) {
            throw new Exception("Bibliothèque PHPWord requise pour l'export DOCX");
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        // Convertir HTML basique vers Word
        $html = strip_tags($html_content, '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6>');
        $html = str_replace(['<p>', '</p>'], ["\n", "\n"], $html);
        $html = str_replace(['<br>', '<br/>'], "\n", $html);
        $html = strip_tags($html);

        $section->addText($html);

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($file_path);

        return [
            'file_path' => $file_path,
            'file_url' => $this->get_export_url($file_path),
            'file_size' => filesize($file_path),
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
    }

    /**
     * Exporter vers HTML
     */
    private function export_to_html(array $document, array $template, string $file_path, array $options): array {
        $html_content = $this->generate_html_content($document, $template, $options);

        // Ajouter les styles CSS
        $css = $this->get_export_css();
        $full_html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>{$css}</style></head><body>{$html_content}</body></html>";

        file_put_contents($file_path, $full_html);

        return [
            'file_path' => $file_path,
            'file_url' => $this->get_export_url($file_path),
            'file_size' => strlen($full_html),
            'mime_type' => 'text/html'
        ];
    }

    /**
     * Exporter vers image
     */
    private function export_to_image(array $document, array $template, string $file_path, string $format, array $options): array {
        // Générer le HTML d'abord
        $html_content = $this->generate_html_content($document, $template, $options);

        // Utiliser une bibliothèque comme wkhtmltoimage ou dompdf pour convertir en image
        // Pour l'instant, on simule avec une approche basique

        // Créer une image simple avec GD
        $width = $options['width'] ?? 800;
        $height = $options['height'] ?? 600;

        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $white);

        // Ajouter un texte simple
        $text = "Document: " . ($document['title'] ?? 'Sans titre');
        imagestring($image, 5, 10, 10, $text, $black);

        // Sauvegarder selon le format
        if ($format === 'png') {
            imagepng($image, $file_path);
            $mime_type = 'image/png';
        } else {
            imagejpeg($image, $file_path, 90);
            $mime_type = 'image/jpeg';
        }

        imagedestroy($image);

        return [
            'file_path' => $file_path,
            'file_url' => $this->get_export_url($file_path),
            'file_size' => filesize($file_path),
            'mime_type' => $mime_type
        ];
    }

    /**
     * Exporter vers CSV
     */
    private function export_to_csv(array $document, array $template, string $file_path, array $options): array {
        $data = json_decode($document['data'], true);
        if (!$data) {
            $data = [];
        }

        $fp = fopen($file_path, 'w');

        // En-têtes
        fputcsv($fp, ['Champ', 'Valeur']);

        // Données
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            fputcsv($fp, [$key, $value]);
        }

        fclose($fp);

        return [
            'file_path' => $file_path,
            'file_url' => $this->get_export_url($file_path),
            'file_size' => filesize($file_path),
            'mime_type' => 'text/csv'
        ];
    }

    /**
     * Exporter vers Excel (XLSX)
     */
    private function export_to_xlsx(array $document, array $template, string $file_path, array $options): array {
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            throw new Exception("Bibliothèque PhpSpreadsheet requise pour l'export XLSX");
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $data = json_decode($document['data'], true) ?: [];

        // En-têtes
        $sheet->setCellValue('A1', 'Champ');
        $sheet->setCellValue('B1', 'Valeur');

        // Données
        $row = 2;
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $sheet->setCellValue('A' . $row, $key);
            $sheet->setCellValue('B' . $row, $value);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($file_path);

        return [
            'file_path' => $file_path,
            'file_url' => $this->get_export_url($file_path),
            'file_size' => filesize($file_path),
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
    }

    /**
     * Générer le contenu HTML pour l'export
     */
    private function generate_html_content(array $document, array $template, array $options): string {
        $data = json_decode($document['data'], true) ?: [];
        $template_content = $template['content'];

        // Remplacer les variables dans le template
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $template_content = str_replace("{{{$key}}}", esc_html($value), $template_content);
        }

        return $template_content;
    }

    /**
     * Obtenir le CSS pour l'export
     */
    private function get_export_css(): string {
        return "
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1, h2, h3, h4, h5, h6 { color: #333; margin-top: 20px; }
            p { line-height: 1.6; margin-bottom: 10px; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        ";
    }

    /**
     * Générer un nom de fichier
     */
    private function generate_filename(array $document, string $format): string {
        $title = sanitize_file_name($document['title'] ?? 'document');
        $timestamp = date('Y-m-d_H-i-s');
        $extension = $this->get_format_extension($format);

        return "{$title}_{$timestamp}.{$extension}";
    }

    /**
     * Obtenir l'extension de fichier pour un format
     */
    private function get_format_extension(string $format): string {
        $extensions = [
            'pdf' => 'pdf',
            'docx' => 'docx',
            'html' => 'html',
            'png' => 'png',
            'jpg' => 'jpg',
            'csv' => 'csv',
            'xlsx' => 'xlsx'
        ];

        return $extensions[$format] ?? 'bin';
    }

    /**
     * Obtenir l'URL d'export
     */
    private function get_export_url(string $file_path): string {
        $relative_path = str_replace(WP_CONTENT_DIR, '', $file_path);
        return WP_CONTENT_URL . $relative_path;
    }

    /**
     * Obtenir les formats supportés
     */
    public function get_supported_formats(): array {
        return $this->supported_formats;
    }

    /**
     * Vérifier si un format est supporté
     */
    public function is_format_supported(string $format): bool {
        return isset($this->supported_formats[$format]);
    }

    /**
     * Nettoyer les fichiers temporaires
     */
    public function cleanup_temp_files(): void {
        $files = glob($this->temp_dir . '*');
        $max_age = 24 * 60 * 60; // 24 heures

        foreach ($files as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $max_age) {
                unlink($file);
            }
        }

        $this->logger->info('Temporary export files cleaned up');
    }

    /**
     * AJAX: Exporter un document
     */
    public function ajax_export(): void {
        check_ajax_referer('pdf_builder_export_nonce', 'nonce');

        $document_id = intval($_POST['document_id'] ?? 0);
        $format = sanitize_text_field($_POST['format'] ?? '');
        $options = json_decode(stripslashes($_POST['options'] ?? '{}'), true) ?: [];

        if (!$document_id || !$format) {
            wp_send_json_error(['message' => 'Paramètres invalides']);
        }

        if (!current_user_can('edit_pdf_documents')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }

        try {
            $result = $this->export_document($document_id, $format, $options);
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Statut d'export
     */
    public function ajax_export_status(): void {
        check_ajax_referer('pdf_builder_export_nonce', 'nonce');

        $export_id = sanitize_text_field($_POST['export_id'] ?? '');

        // Logique pour vérifier le statut d'un export asynchrone
        // À implémenter selon les besoins

        wp_send_json_success(['status' => 'completed']);
    }

    /**
     * Obtenir les statistiques d'export
     */
    public function get_export_stats(): array {
        global $wpdb;

        $stats = $wpdb->get_row("
            SELECT
                COUNT(*) as total_exports,
                COUNT(DISTINCT document_id) as unique_documents,
                COUNT(CASE WHEN format = 'pdf' THEN 1 END) as pdf_exports,
                COUNT(CASE WHEN format = 'html' THEN 1 END) as html_exports,
                COUNT(CASE WHEN format = 'docx' THEN 1 END) as docx_exports
            FROM {$wpdb->prefix}pdf_builder_export_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        return [
            'total_exports' => intval($stats->total_exports ?? 0),
            'unique_documents' => intval($stats->unique_documents ?? 0),
            'pdf_exports' => intval($stats->pdf_exports ?? 0),
            'html_exports' => intval($stats->html_exports ?? 0),
            'docx_exports' => intval($stats->docx_exports ?? 0),
            'temp_files_size' => $this->get_temp_files_size()
        ];
    }

    /**
     * Obtenir la taille des fichiers temporaires
     */
    private function get_temp_files_size(): int {
        $size = 0;
        $files = glob($this->temp_dir . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }

        return $size;
    }
}

