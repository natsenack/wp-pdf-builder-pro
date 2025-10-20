<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Dual PDF Generator
 * Génération PDF hybride combinant screenshot haute-fidélité et TCPDF structuré
 */

class PDF_Builder_Dual_PDF_Generator
{

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Screenshot renderer
     */
    private $screenshot_renderer;

    /**
     * TCPDF renderer
     */
    private $tcpdf_renderer;

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->screenshot_renderer = new PDF_Builder_Screenshot_Renderer($main_instance);
        $this->tcpdf_renderer = new PDF_Builder_TCPDF_Renderer($main_instance);
    }

    /**
     * Générer PDF en mode dual (hybride)
     *
     * @param  array  $canvas_data     Données
     *                                 du canvas
     * @param  array  $structured_data Données structurées
     *                                 (WooCommerce)
     * @param  string $filename        Nom du fichier
     * @return string|false Chemin du PDF ou false en cas d'erreur
     */
    public function generate_dual_pdf($canvas_data, $structured_data = [], $filename = null)
    {
        try {
            if (!$filename) {
                $filename = 'dual-pdf-' . time() . '.pdf';
            }

            // Créer le répertoire de destination
            $upload_dir = wp_upload_dir();
            $pdf_dir = $upload_dir['basedir'] . '/pdf-builder-dual';
            if (!file_exists($pdf_dir)) {
                wp_mkdir_p($pdf_dir);
            }

            $pdf_path = $pdf_dir . '/' . $filename;

            // Stratégie 1: Screenshot haute-fidélité (priorité)
            $screenshot_pdf = $this->generate_screenshot_pdf($canvas_data, $filename);
            if ($screenshot_pdf && $this->is_pdf_valid($screenshot_pdf)) {
                // Si le screenshot est réussi et valide, l'utiliser comme base
                if (copy($screenshot_pdf, $pdf_path)) {
                    $this->log_generation('screenshot', $pdf_path);
                    return $pdf_path;
                }
            }

            // Stratégie 2: TCPDF avec données structurées (fallback)
            $tcpdf_result = $this->tcpdf_renderer->generate_structured_pdf($canvas_data, $structured_data, $pdf_path);
            if ($tcpdf_result) {
                $this->log_generation('tcpdf_fallback', $pdf_path);
                return $pdf_path;
            }

            // Stratégie 3: Fusion intelligente (si les deux sont disponibles)
            $hybrid_pdf = $this->generate_hybrid_pdf($canvas_data, $structured_data, $filename);
            if ($hybrid_pdf) {
                if (copy($hybrid_pdf, $pdf_path)) {
                    $this->log_generation('hybrid', $pdf_path);
                    return $pdf_path;
                }
            }

            $this->log_generation('failed', null);
            return false;

        } catch (Exception $e) {
            error_log('Erreur génération PDF dual: ' . $e->getMessage());
            $this->log_generation('error', null, $e->getMessage());
            return false;
        }
    }

    /**
     * Générer PDF via screenshot uniquement
     */
    private function generate_screenshot_pdf($canvas_data, $filename)
    {
        $temp_filename = 'temp_screenshot_' . time() . '.pdf';
        return $this->screenshot_renderer->generate_pdf_from_canvas($canvas_data, $temp_filename);
    }

    /**
     * Générer PDF hybride (screenshot + TCPDF fusion)
     */
    private function generate_hybrid_pdf($canvas_data, $structured_data, $filename)
    {
        try {
            // Étape 1: Générer screenshot haute-fidélité
            $screenshot_pdf = $this->generate_screenshot_pdf($canvas_data, 'temp_screenshot_' . time() . '.pdf');
            if (!$screenshot_pdf) {
                return false;
            }

            // Étape 2: Générer couche TCPDF avec données structurées
            $tcpdf_overlay = $this->generate_tcpdf_overlay($structured_data, 'temp_overlay_' . time() . '.pdf');
            if (!$tcpdf_overlay) {
                // Retourner juste le screenshot si l'overlay échoue
                return $screenshot_pdf;
            }

            // Étape 3: Fusionner les PDFs
            $merged_pdf = $this->merge_pdfs($screenshot_pdf, $tcpdf_overlay, $filename);

            // Nettoyer les fichiers temporaires
            if (file_exists($screenshot_pdf)) { unlink($screenshot_pdf);
            }
            if (file_exists($tcpdf_overlay)) { unlink($tcpdf_overlay);
            }

            return $merged_pdf;

        } catch (Exception $e) {
            error_log('Erreur génération PDF hybride: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer overlay TCPDF avec données structurées
     */
    private function generate_tcpdf_overlay($structured_data, $filename)
    {
        if (empty($structured_data)) {
            return false;
        }

        return $this->tcpdf_renderer->generate_overlay_pdf($structured_data, $filename);
    }

    /**
     * Fusionner deux PDFs
     */
    private function merge_pdfs($pdf1, $pdf2, $output_filename)
    {
        try {
            // Utiliser TCPDF pour la fusion si FPDI est disponible
            if (class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
                return $this->merge_with_fpdi($pdf1, $pdf2, $output_filename);
            }

            // Fallback: combiner les contenus HTML et régénérer
            return $this->merge_by_regeneration($pdf1, $pdf2, $output_filename);

        } catch (Exception $e) {
            error_log('Erreur fusion PDFs: ' . $e->getMessage());
            return $pdf1; // Retourner le PDF principal en cas d'erreur
        }
    }

    /**
     * Fusionner PDFs avec FPDI
     */
    private function merge_with_fpdi($pdf1, $pdf2, $output_filename)
    {
        include_once WP_PLUGIN_DIR . '/wp-pdf-builder-pro/lib/tcpdf/tcpdf_autoload.php';
        include_once WP_PLUGIN_DIR . '/wp-pdf-builder-pro/lib/fpdi/autoload.php';

        $pdf = new setasign\Fpdi\Tcpdf\Fpdi();

        // Importer la première page du PDF 1
        $pageCount1 = $pdf->setSourceFile($pdf1);
        for ($pageNo = 1; $pageNo <= $pageCount1; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
        }

        // Importer la première page du PDF 2 en overlay
        $pageCount2 = $pdf->setSourceFile($pdf2);
        if ($pageCount2 > 0) {
            $templateId = $pdf->importPage(1);
            $pdf->AddPage();
            $pdf->useTemplate($templateId, 0, 0, 0, 0, true); // Overlay mode
        }

        $upload_dir = wp_upload_dir();
        $output_path = $upload_dir['basedir'] . '/pdf-builder-dual/' . $output_filename;
        $pdf->Output($output_path, 'F');

        return $output_path;
    }

    /**
     * Fusionner PDFs par régénération (fallback)
     */
    private function merge_by_regeneration($pdf1, $pdf2, $output_filename)
    {
        // Pour l'instant, retourner le PDF principal
        // TODO: Implémenter une vraie fusion si nécessaire
        return $pdf1;
    }

    /**
     * Vérifier si un PDF est valide
     */
    private function is_pdf_valid($pdf_path)
    {
        if (!file_exists($pdf_path)) {
            return false;
        }

        $size = filesize($pdf_path);
        if ($size < 1024) { // Moins de 1KB = probablement corrompu
            return false;
        }

        // Vérifier l'en-tête PDF
        $handle = fopen($pdf_path, 'r');
        $header = fread($handle, 8);
        fclose($handle);

        return strpos($header, '%PDF-') === 0;
    }

    /**
     * Logger la génération PDF
     */
    private function log_generation($method, $pdf_path, $error = null)
    {
        $logger = new PDF_Builder_Logger();
        $message = sprintf(
            'Génération PDF dual - Méthode: %s, Succès: %s%s',
            $method,
            $pdf_path ? 'Oui' : 'Non',
            $error ? ', Erreur: ' . $error : ''
        );
        $logger->log($message, $pdf_path ? 'info' : 'error', 'dual_pdf_generator');
    }

    /**
     * Obtenir les statistiques de génération
     */
    public function get_generation_stats()
    {
        return [
            'screenshot_capabilities' => $this->screenshot_renderer->get_system_capabilities(),
            'tcpdf_available' => class_exists('TCPDF'),
            'fpdi_available' => class_exists('setasign\Fpdi\Tcpdf\Fpdi'),
            'hybrid_supported' => $this->is_hybrid_supported()
        ];
    }

    /**
     * Vérifier si la génération hybride est supportée
     */
    private function is_hybrid_supported()
    {
        $capabilities = $this->screenshot_renderer->get_system_capabilities();
        return $capabilities['wkhtmltopdf'] || $capabilities['puppeteer'];
    }

    /**
     * Nettoyer les fichiers temporaires
     */
    public function cleanup_temp_files()
    {
        $upload_dir = wp_upload_dir();
        $temp_dirs = [
            $upload_dir['basedir'] . '/pdf-builder-screenshots',
            $upload_dir['basedir'] . '/pdf-builder-dual'
        ];

        foreach ($temp_dirs as $dir) {
            if (file_exists($dir)) {
                $files = glob($dir . '/temp_*');
                foreach ($files as $file) {
                    if (file_exists($file) && (time() - filemtime($file)) > 3600) { // Plus d'1h
                        unlink($file);
                    }
                }
            }
        }
    }
}
