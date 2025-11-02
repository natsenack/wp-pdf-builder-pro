<?php
namespace WP_PDF_Builder_Pro\Generators;

use Dompdf\Dompdf;
use Dompdf\Options;
use WP_PDF_Builder_Pro\Data\DataProviderInterface;

/**
 * Classe PDFGenerator
 * Générateur PDF utilisant DomPDF avec fallback Canvas
 */
class PDFGenerator extends BaseGenerator {

    /** @var Dompdf Instance DomPDF */
    private $dompdf;

    /** @var bool Indique si DomPDF est disponible */
    private $dompdf_available;

    /** @var string HTML généré */
    private $generated_html;

    /** @var array Métriques de performance */
    private $performance_metrics;

    /**
     * {@inheritDoc}
     */
    protected function initialize(): void {
        $this->dompdf_available = $this->checkDomPDFAvailability();
        $this->performance_metrics = [
            'start_time' => microtime(true),
            'memory_start' => memory_get_usage(true),
            'html_generation_time' => 0,
            'pdf_generation_time' => 0,
            'fallback_used' => false
        ];

        if ($this->dompdf_available) {
            $this->initializeDomPDF();
        }
    }

    /**
     * Vérifie si DomPDF est disponible
     *
     * @return bool True si DomPDF est disponible
     */
    private function checkDomPDFAvailability(): bool {
        return class_exists('Dompdf\Dompdf');
    }

    /**
     * Initialise DomPDF avec les options optimisées
     */
    private function initializeDomPDF(): void {
        $options = new Options();

        // Configuration optimisée pour les aperçus
        $options->set('isRemoteEnabled', $this->config['enable_remote'] ?? false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultMediaType', 'screen');
        $options->set('dpi', $this->config['dpi'] ?? 96);

        // Configuration mémoire et performance
        $options->set('tempDir', $this->config['temp_dir'] ?? sys_get_temp_dir());
        $options->set('fontCache', $this->config['temp_dir'] ?? sys_get_temp_dir());
        $options->set('logOutputFile', null); // Désactiver logs fichier en mode aperçu

        // Configuration format
        $options->set('defaultPaperSize', $this->config['format'] ?? 'A4');
        $options->set('defaultPaperOrientation', $this->config['orientation'] ?? 'portrait');

        $this->dompdf = new Dompdf($options);
    }

    /**
     * {@inheritDoc}
     */
    public function generate(string $output_type = 'pdf') {
        // Validation du template
        if (!$this->validateTemplate()) {
            throw new \Exception('Template validation failed');
        }

        // Génération HTML
        $html_start = microtime(true);
        $this->generated_html = $this->generateHTML();
        $this->performance_metrics['html_generation_time'] = microtime(true) - $html_start;

        // Tentative génération PDF avec DomPDF
        if ($this->dompdf_available) {
            try {
                $result = $this->generateWithDomPDF($output_type);
                $this->logPerformanceMetrics();
                return $result;
            } catch (\Exception $e) {
                $this->logWarning("DomPDF generation failed: " . $e->getMessage());
                $this->performance_metrics['fallback_used'] = true;
            }
        }

        // Fallback vers Canvas
        $this->logInfo("Using Canvas fallback for PDF generation");
        return $this->generateWithCanvas($output_type);
    }

    /**
     * Génère avec DomPDF
     *
     * @param string $output_type Type de sortie
     * @return mixed Résultat de la génération
     */
    private function generateWithDomPDF(string $output_type) {
        $pdf_start = microtime(true);

        // Chargement du HTML
        $this->dompdf->loadHtml($this->generated_html);

        // Rendu du PDF
        $this->dompdf->render();

        $this->performance_metrics['pdf_generation_time'] = microtime(true) - $pdf_start;

        // Vérification mémoire
        $current_memory = memory_get_usage(true);
        $memory_used = $current_memory - $this->performance_metrics['memory_start'];

        if ($memory_used > 100 * 1024 * 1024) { // 100MB
            $this->logWarning("High memory usage detected: " . round($memory_used / 1024 / 1024, 2) . "MB");
        }

        // Retour selon le type demandé
        switch ($output_type) {
            case 'pdf':
                return $this->dompdf->output();

            case 'png':
            case 'jpg':
                return $this->convertPDFToImage($output_type);

            default:
                throw new \Exception("Unsupported output type: {$output_type}");
        }
    }

    /**
     * Génère avec Canvas (fallback)
     *
     * @param string $output_type Type de sortie
     * @return mixed Résultat de la génération
     */
    private function generateWithCanvas(string $output_type) {
        $this->logInfo("Generating with Canvas fallback");

        // Pour le fallback Canvas, nous retournons les données nécessaires
        // pour que JavaScript puisse générer l'aperçu côté client
        return [
            'fallback' => true,
            'method' => 'canvas',
            'html' => $this->generated_html,
            'template_data' => $this->template_data,
            'config' => $this->config,
            'output_type' => $output_type
        ];
    }

    /**
     * Convertit le PDF en image
     *
     * @param string $format Format d'image (png ou jpg)
     * @return string Données de l'image
     */
    private function convertPDFToImage(string $format): string {
        // Utilisation de Imagick si disponible pour la conversion
        if (extension_loaded('imagick')) {
            return $this->convertWithImagick($format);
        }

        // Fallback : génération d'une image placeholder avec message
        $this->logWarning("Imagick not available for PDF to image conversion");
        return $this->generatePlaceholderImage($format);
    }

    /**
     * Convertit avec Imagick
     *
     * @param string $format Format d'image
     * @return string Données de l'image
     */
    private function convertWithImagick(string $format): string {
        try {
            $imagick = new \Imagick();
            $imagick->readImageBlob($this->dompdf->output());
            $imagick->setImageFormat($format);

            // Configuration qualité
            if ($format === 'jpg') {
                $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality($this->config['quality'] ?? 90);
            }

            // Redimensionnement si nécessaire
            $maxWidth = $this->config['max_width'] ?? 1200;
            $maxHeight = $this->config['max_height'] ?? 1600;

            if ($imagick->getImageWidth() > $maxWidth || $imagick->getImageHeight() > $maxHeight) {
                $imagick->thumbnailImage($maxWidth, $maxHeight, true);
            }

            return $imagick->getImageBlob();

        } catch (\Exception $e) {
            $this->logError("Imagick conversion failed: " . $e->getMessage());
            return $this->generatePlaceholderImage($format);
        }
    }

    /**
     * Génère une image placeholder avec message d'erreur
     *
     * @param string $format Format d'image
     * @return string Données de l'image placeholder
     */
    private function generatePlaceholderImage(string $format): string {
        // Création d'une image simple avec GD si disponible
        if (function_exists('imagecreatetruecolor')) {
            return $this->generatePlaceholderWithGD($format);
        }

        // Retour d'une réponse JSON avec les informations d'erreur
        return json_encode([
            'error' => true,
            'message' => 'Image conversion not available. Please use PDF output.',
            'fallback_html' => $this->generated_html
        ]);
    }

    /**
     * Génère un placeholder avec GD
     *
     * @param string $format Format d'image
     * @return string Données de l'image
     */
    private function generatePlaceholderWithGD(string $format): string {
        $width = 800;
        $height = 600;

        $image = imagecreatetruecolor($width, $height);

        // Couleurs
        $bg_color = imagecolorallocate($image, 240, 240, 240);
        $text_color = imagecolorallocate($image, 100, 100, 100);
        $border_color = imagecolorallocate($image, 200, 200, 200);

        // Remplissage fond
        imagefill($image, 0, 0, $bg_color);

        // Bordure
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $border_color);

        // Texte
        $font_size = 5; // Taille par défaut GD
        $text = "PDF Preview - Image conversion not available";
        $text_width = imagefontwidth($font_size) * strlen($text);
        $text_height = imagefontheight($font_size);

        $x = ($width - $text_width) / 2;
        $y = ($height - $text_height) / 2;

        imagestring($image, $font_size, $x, $y, $text, $text_color);

        // Capture de l'image
        ob_start();
        $function = 'image' . $format;
        if (function_exists($function)) {
            $function($image);
        } else {
            imagepng($image); // Fallback PNG
        }
        $image_data = ob_get_clean();

        imagedestroy($image);

        return $image_data;
    }

    /**
     * Log les métriques de performance
     */
    private function logPerformanceMetrics(): void {
        $total_time = microtime(true) - $this->performance_metrics['start_time'];
        $memory_used = memory_get_usage(true) - $this->performance_metrics['memory_start'];

        $metrics = [
            'total_time' => round($total_time, 3) . 's',
            'html_generation' => round($this->performance_metrics['html_generation_time'], 3) . 's',
            'pdf_generation' => round($this->performance_metrics['pdf_generation_time'], 3) . 's',
            'memory_used' => round($memory_used / 1024 / 1024, 2) . 'MB',
            'fallback_used' => $this->performance_metrics['fallback_used'],
            'template_elements' => count($this->template_data['template']['elements'] ?? [])
        ];

        $this->logInfo("PDF Generation Metrics: " . json_encode($metrics));

        // Vérification des seuils de performance
        if ($total_time > 2.0) {
            $this->logWarning("Slow generation detected: {$total_time}s");
        }

        if ($memory_used > 50 * 1024 * 1024) { // 50MB
            $this->logWarning("High memory usage: " . round($memory_used / 1024 / 1024, 2) . "MB");
        }
    }

    /**
     * Retourne les métriques de performance
     *
     * @return array Métriques de performance
     */
    public function getPerformanceMetrics(): array {
        return $this->performance_metrics;
    }

    /**
     * Retourne le HTML généré (pour debug)
     *
     * @return string|null HTML généré ou null si pas encore généré
     */
    public function getGeneratedHTML(): ?string {
        return $this->generated_html;
    }

    /**
     * Force l'utilisation du fallback Canvas
     *
     * @param bool $force True pour forcer le fallback
     */
    public function forceCanvasFallback(bool $force = true): void {
        $this->dompdf_available = !$force;
    }

    /**
     * Vérifie si DomPDF est opérationnel
     *
     * @return bool True si DomPDF est disponible
     */
    public function isDomPDFAvailable(): bool {
        return $this->dompdf_available;
    }
}