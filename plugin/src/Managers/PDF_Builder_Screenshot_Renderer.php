<?php

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Screenshot Renderer
 * Génération de PDF haute-fidélité via capture d'écran du canvas
 */

class PDF_Builder_Screenshot_Renderer
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
    }

    /**
     * Générer PDF via screenshot du canvas
     *
     * @param  array  $canvas_data Données du
     *                             canvas
     * @param  string $filename    Nom du fichier PDF
     * @return string|false Chemin du PDF généré ou false en cas d'erreur
     */
    public function generate_pdf_from_canvas($canvas_data, $filename)
    {
        try {
            // Créer le répertoire de destination
            $upload_dir = wp_upload_dir();
            $pdf_dir = $upload_dir['basedir'] . '/pdf-builder-screenshots';
            if (!file_exists($pdf_dir)) {
                wp_mkdir_p($pdf_dir);
            }

            $pdf_path = $pdf_dir . '/' . $filename;

            // Générer le HTML du canvas
            $html = $this->generate_canvas_html($canvas_data);

            // Créer un fichier HTML temporaire pour la capture
            $temp_html_file = $pdf_dir . '/temp_' . time() . '.html';
            file_put_contents($temp_html_file, $html);

            // Générer le PDF via Puppeteer/Playwright ou wkhtmltopdf
            $success = $this->generate_pdf_from_html($temp_html_file, $pdf_path);

            // Nettoyer le fichier temporaire
            if (file_exists($temp_html_file)) {
                unlink($temp_html_file);
            }

            return $success ? $pdf_path : false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Générer le HTML du canvas pour capture
     */
    private function generate_canvas_html($canvas_data)
    {
        if (!is_array($canvas_data)) {
            return '';
        }

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Builder Pro - Capture</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: white;
        }
        .pdf-canvas {
            width: 594px;
            min-height: 1123px;
            background: white;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
        }
        .canvas-element {
            position: absolute;
            box-sizing: border-box;
        }
        .text-element {
            font-size: 14px;
            line-height: 1.4;
            color: #333;
        }
        .image-element img {
            max-width: 100%;
            height: auto;
        }
        .shape-element {
            border: 1px solid #000;
        }
        @page {
            size: A4;
            margin: 15mm;
        }
        @media print {
            body { margin: 0; }
            .pdf-canvas { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="pdf-canvas">';

        foreach ($canvas_data as $element) {
            $html .= $this->render_canvas_element($element);
        }

        $html .= '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Rendre un élément du canvas en HTML
     */
    private function render_canvas_element($element)
    {
        if (!isset($element['type']) || !isset($element['properties'])) {
            return '';
        }

        $props = $element['properties'];
        $style = $this->build_element_style($props);

        $html = '<div class="canvas-element ' . esc_attr($element['type']) . '-element" style="' . $style . '">';

        switch ($element['type']) {
            case 'text':
                $html .= '<div class="text-element">' . esc_html($props['text'] ?? '') . '</div>';
                break;

            case 'image':
                if (!empty($props['src'])) {
                    $html .= '<img src="' . esc_url($props['src']) . '" alt="' . esc_attr($props['alt'] ?? '') . '">';
                }
                break;

            case 'shape':
                // Formes géométriques simples
                $shape_type = $props['shape'] ?? 'rectangle';
                if ($shape_type === 'rectangle') {
                    $html .= '<div class="shape-element" style="width: 100%; height: 100%; background: ' . esc_attr($props['fill'] ?? '#fff') . '; border: ' . esc_attr($props['strokeWidth'] ?? 1) . 'px solid ' . esc_attr($props['stroke'] ?? '#000') . ';"></div>';
                }
                break;

            case 'line':
                $html .= '<hr style="border: none; border-top: ' . esc_attr($props['strokeWidth'] ?? 1) . 'px solid ' . esc_attr($props['stroke'] ?? '#000') . '; margin: 0;">';
                break;
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Construire le style CSS d'un élément
     */
    private function build_element_style($props)
    {
        $style = [];

        // Positionnement
        if (isset($props['x'])) {
            $style[] = 'left: ' . intval($props['x']) . 'px';
        }
        if (isset($props['y'])) {
            $style[] = 'top: ' . intval($props['y']) . 'px';
        }

        // Dimensions
        if (isset($props['width'])) {
            $style[] = 'width: ' . intval($props['width']) . 'px';
        }
        if (isset($props['height'])) {
            $style[] = 'height: ' . intval($props['height']) . 'px';
        }

        // Rotation
        if (isset($props['rotation']) && $props['rotation'] != 0) {
            $style[] = 'transform: rotate(' . intval($props['rotation']) . 'deg)';
        }

        // Opacité
        if (isset($props['opacity']) && $props['opacity'] < 1) {
            $style[] = 'opacity: ' . floatval($props['opacity']);
        }

        // Z-index
        if (isset($props['zIndex'])) {
            $style[] = 'z-index: ' . intval($props['zIndex']);
        }

        return implode('; ', $style);
    }

    /**
     * Générer PDF depuis HTML via différentes méthodes
     */
    private function generate_pdf_from_html($html_file, $pdf_path)
    {
        // Méthode 1: wkhtmltopdf (si disponible)
        if ($this->is_wkhtmltopdf_available()) {
            return $this->generate_with_wkhtmltopdf($html_file, $pdf_path);
        }

        // Méthode 2: Puppeteer via Node.js (si disponible)
        if ($this->is_puppeteer_available()) {
            return $this->generate_with_puppeteer($html_file, $pdf_path);
        }

        // Méthode 3: TCPDF comme fallback (qualité réduite)
        return $this->generate_with_tcpdf_fallback($html_file, $pdf_path);
    }

    /**
     * Vérifier si wkhtmltopdf est disponible
     */
    private function is_wkhtmltopdf_available()
    {
        require_once plugin_dir_path(__FILE__) . 'PDF_Builder_Secure_Shell_Manager.php';
        return PDF_Builder_Secure_Shell_Manager::is_command_available('wkhtmltopdf');
    }

    /**
     * Générer avec wkhtmltopdf
     */
    private function generate_with_wkhtmltopdf($html_file, $pdf_path)
    {
        require_once plugin_dir_path(__FILE__) . 'PDF_Builder_Secure_Shell_Manager.php';
        return PDF_Builder_Secure_Shell_Manager::execute_wkhtmltopdf($html_file, $pdf_path);
    }

    /**
     * Vérifier si Puppeteer est disponible
     */
    private function is_puppeteer_available()
    {
        // Vérifier si Node.js est disponible via le gestionnaire sécurisé
        require_once plugin_dir_path(__FILE__) . 'PDF_Builder_Secure_Shell_Manager.php';
        $node_available = PDF_Builder_Secure_Shell_Manager::is_command_available('node');
        if (!$node_available) {
            return false;
        }

        // Vérifier si le script puppeteer existe et est sécurisé
        $script_path = plugin_dir_path(__FILE__) . '../../tools/pdf-screenshot.js';
        return file_exists($script_path) && PDF_Builder_Secure_Shell_Manager::is_secure_file_path($script_path);
    }

    /**
     * Générer avec Puppeteer
     */
    private function generate_with_puppeteer($html_file, $pdf_path)
    {
        $script_path = plugin_dir_path(__FILE__) . '../../tools/pdf-screenshot.js';

        require_once plugin_dir_path(__FILE__) . 'PDF_Builder_Secure_Shell_Manager.php';
        $output = PDF_Builder_Secure_Shell_Manager::execute_node($script_path, [$html_file, $pdf_path]);

        return $output !== false && file_exists($pdf_path) && filesize($pdf_path) > 0;
    }

    /**
     * Fallback vers TCPDF (qualité réduite)
     */
    private function generate_with_tcpdf_fallback($html_file, $pdf_path)
    {
        try {
            include_once WP_PLUGIN_DIR . '/wp-pdf-builder-pro/lib/tcpdf/tcpdf_autoload.php';

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator('PDF Builder Pro');
            $pdf->SetAuthor('PDF Builder Pro');
            $pdf->SetTitle('Document PDF');
            $pdf->SetMargins(15, 15, 15);
            $pdf->AddPage();

            // Lire le HTML et l'ajouter au PDF
            $html_content = file_get_contents($html_file);
            $pdf->writeHTML($html_content, true, false, true, false, '');

            $pdf->Output($pdf_path, 'F');
            return file_exists($pdf_path);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir les informations sur les capacités du système
     */
    public function get_system_capabilities()
    {
        return [
            'wkhtmltopdf' => $this->is_wkhtmltopdf_available(),
            'puppeteer' => $this->is_puppeteer_available(),
            'tcpdf_fallback' => true
        ];
    }
}
