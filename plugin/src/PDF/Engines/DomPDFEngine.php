<?php
/**
 * DomPDF Engine - Moteur de génération PDF par défaut (fallback)
 * 
 * @package PDF_Builder_Pro
 * @subpackage PDF\Engines
 * @version 1.0.0
 */

namespace PDF_Builder\PDF\Engines;

use Dompdf\Dompdf;
use Dompdf\Options;

class DomPDFEngine implements PDFEngineInterface {
    
    private $debug_enabled;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->debug_enabled = get_option('pdf_builder_debug_enabled', false);
    }
    
    /**
     * Génère un PDF à partir de HTML
     * 
     * @param string $html Contenu HTML
     * @param array $options Options de génération [width, height, orientation, etc.]
     * @return string|false Contenu PDF binaire ou false en cas d'erreur
     */
    public function generate($html, $options = []) {
        $this->debug_log("========== GÉNÉRATION PDF DOMPDF ==========");
        $this->debug_log("HTML size: " . strlen($html) . " bytes");
        
        try {
            require_once PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php';
            
            // Initialiser DomPDF
            $dompdf = $this->init_dompdf($options);
            
            // Charger le HTML
            $this->debug_log("Chargement HTML dans DOMPDF");
            $dompdf->loadHtml($html);
            
            // Configurer le papier
            list($width, $height, $orientation) = $this->configure_paper_size($dompdf, $options);
            
            $this->debug_log("Rendu PDF - Format: {$width}x{$height}pt, Orientation: {$orientation}");
            
            // Générer le PDF
            $dompdf->render();
            
            // Récupérer le contenu
            $pdf_content = $dompdf->output();
            
            $this->debug_log("PDF généré avec succès - Taille: " . strlen($pdf_content) . " bytes");
            
            return $pdf_content;
            
        } catch (\Exception $e) {
            $this->debug_log("Erreur DomPDF: " . $e->getMessage(), "ERROR");
            return false;
        }
    }
    
    /**
     * Génère une image à partir de HTML (via Imagick)
     * 
     * @param string $html Contenu HTML
     * @param array $options Options [format => 'png'|'jpg', width, height, quality]
     * @return string|false Contenu image binaire ou false
     */
    public function generate_image($html, $options = []) {
        $this->debug_log("========== GÉNÉRATION IMAGE DOMPDF+IMAGICK ==========");
        
        if (!extension_loaded('imagick')) {
            $this->debug_log("Extension Imagick non disponible", "ERROR");
            return false;
        }
        
        try {
            $format = $options['format'] ?? 'png';
            
            // Étape 1: Générer le PDF
            $pdf_content = $this->generate($html, $options);
            
            if ($pdf_content === false) {
                return false;
            }
            
            // Étape 2: Sauvegarder PDF temporaire
            $temp_pdf = sys_get_temp_dir() . '/pdf-builder-' . uniqid() . '.pdf';
            file_put_contents($temp_pdf, $pdf_content);
            
            $this->debug_log("PDF temporaire créé: " . filesize($temp_pdf) . " bytes");
            
            // Étape 3: Convertir PDF → Image avec Imagick
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150); // DPI
            $imagick->readImage($temp_pdf . '[0]'); // Première page
            $imagick->setImageFormat($format === 'jpg' ? 'jpeg' : 'png');
            
            if ($format === 'jpg') {
                $imagick->setImageCompressionQuality($options['quality'] ?? 90);
            }
            
            // Redimensionner si nécessaire
            if (isset($options['width']) && isset($options['height'])) {
                $imagick->resizeImage(
                    $options['width'], 
                    $options['height'], 
                    \Imagick::FILTER_LANCZOS, 
                    1, 
                    true
                );
            }
            
            $image_content = $imagick->getImageBlob();
            
            // Nettoyer
            $imagick->clear();
            $imagick->destroy();
            unlink($temp_pdf);
            
            $this->debug_log("Image générée avec succès - Taille: " . strlen($image_content) . " bytes");
            
            return $image_content;
            
        } catch (\Exception $e) {
            $this->debug_log("Erreur génération image: " . $e->getMessage(), "ERROR");
            return false;
        }
    }
    
    /**
     * Initialise DomPDF avec configuration optimale
     * 
     * @param array $custom_options Options personnalisées
     * @return Dompdf Instance DomPDF
     */
    private function init_dompdf($custom_options = []) {
        $default_options = [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'fontHeightRatio' => 1.1,
            'isUnicode' => true,
            'enable_font_subsetting' => false,
            'defaultPaperSize' => 'A4',
            'dpi' => 96,
            'enable_php' => false,
            'enable_javascript' => false,
            'enable_remote' => true,
            'chroot' => ABSPATH,
        ];
        
        $options = array_merge($default_options, $custom_options);
        
        $this->debug_log("DomPDF initialisé avec DPI: " . $options['dpi']);
        
        return new Dompdf($options);
    }
    
    /**
     * Configure le format papier pour DomPDF
     * 
     * @param Dompdf $dompdf Instance DomPDF
     * @param array $options Options
     * @return array [width_pt, height_pt, orientation]
     */
    private function configure_paper_size($dompdf, $options) {
        // Dimensions par défaut : A4 @ 96 DPI (794×1123px)
        $width = $options['width'] ?? 794;
        $height = $options['height'] ?? 1123;
        $orientation = ($width > $height) ? 'landscape' : 'portrait';
        
        // Convertir pixels en points (1px = 0.75pt pour DomPDF)
        $width_pt = $width * 0.75;
        $height_pt = $height * 0.75;
        
        $dompdf->setPaper([0, 0, $width_pt, $height_pt], $orientation);
        
        return [$width_pt, $height_pt, $orientation];
    }
    
    /**
     * Retourne le nom du moteur
     * 
     * @return string Nom du moteur
     */
    public function get_name() {
        return 'DomPDF';
    }
    
    /**
     * Vérifie si le moteur est disponible
     * 
     * @return bool True si disponible
     */
    public function is_available() {
        return class_exists('Dompdf\\Dompdf');
    }
    
    /**
     * Logger conditionnel
     * 
     * @param string $message Message
     * @param string $level Niveau (INFO, WARNING, ERROR)
     */
    private function debug_log($message, $level = 'INFO') {
        if ($this->debug_enabled || (defined('WP_DEBUG') && WP_DEBUG)) {
            error_log("[DomPDF Engine - {$level}] {$message}");
        }
    }
}
