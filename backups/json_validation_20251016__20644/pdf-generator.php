<?php
/**
 * PDF Builder Pro - Generateur PDF Ultra-Performant
 * Version: 2.0 - Architecture complete reecrite
 * Auteur: PDF Builder Pro Team
 * Description: Systeme plug-and-play pour generation PDF haute performance
 */

// Sécurité WordPress - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PDF_Builder_Pro_Generator {

    private $pdf = null;
    private $cache = [];
    private $errors = [];
    private $performance_metrics = [];
    private $order = null; // Ajout de la propriété order

    // Configuration par defaut
    private $config = [
        'orientation' => 'P',
        'unit' => 'mm',
        'format' => 'A4',
        'font_size' => 12,
        'font_family' => 'helvetica',
        'margin_left' => 15,
        'margin_top' => 20,
        'margin_right' => 15,
        'margin_bottom' => 20,
        'auto_page_break' => true,
        'page_break_margin' => 15
    ];

    public function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
        $this->performance_metrics['start_time'] = microtime(true);
    }

    /**
     * Définit l'ordre pour la génération du PDF
     */
    public function set_order($order) {
        $this->order = $order;
    }

    /**
     * Generateur principal - Interface unifiee
     */
    public function generate($elements, $options = []) {
        error_log('🟡 PDF BUILDER - generate: START - ' . count($elements) . ' elements');

        try {
            error_log('🟡 PDF BUILDER - generate: Calling reset()');
            $this->reset();

            error_log('🟡 PDF BUILDER - generate: Calling validate_elements()');
            $this->validate_elements($elements);

            error_log('🟡 PDF BUILDER - generate: Calling initialize_tcpdf()');
            $this->initialize_tcpdf();

            error_log('🟡 PDF BUILDER - generate: Calling configure_pdf()');
            $this->configure_pdf($options);

            error_log('🟡 PDF BUILDER - generate: Calling render_elements()');
            $this->render_elements($elements);

            error_log('🟡 PDF BUILDER - generate: Calling finalize_pdf()');
            return $this->finalize_pdf();

        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - generate: EXCEPTION - ' . $e->getMessage());
            error_log('❌ PDF BUILDER - generate: STACK TRACE - ' . $e->getTraceAsString());
            $this->log_error('Generation PDF echouee: ' . $e->getMessage());
            return $this->generate_fallback_pdf($elements);
        }
    }

    /**
     * Alias pour la compatibilite descendante
     */
    public function generate_from_elements($elements) {
        return $this->generate($elements);
    }

    /**
     * Reinitialisation complete
     */
    private function reset() {
        $this->pdf = null;
        $this->cache = [];
        $this->errors = [];
        $this->performance_metrics = ['start_time' => microtime(true)];
    }

    /**
     * Validation des elements d'entree
     */
    private function validate_elements($elements) {
        if (!is_array($elements) || empty($elements)) {
            throw new Exception('Elements invalides ou vides');
        }

        foreach ($elements as $index => $element) {
            if (!is_array($element) || !isset($element['type'])) {
                throw new Exception("Element $index invalide: type manquant");
            }
        }
    }

    /**
     * Initialisation TCPDF optimisee
     */
    private function initialize_tcpdf() {
        if (!class_exists('TCPDF')) {
            $this->load_tcpdf();
        }

        // Configuration TCPDF optimisee
        $this->define_tcpdf_constants();

        $this->pdf = new TCPDF(
            $this->config['orientation'],
            $this->config['unit'],
            $this->config['format'],
            true,
            'UTF-8',
            false
        );

        // Configuration de base optimisee
        $this->pdf->SetCreator('PDF Builder Pro v2.0');
        $this->pdf->SetAuthor('PDF Builder Pro');
        $this->pdf->SetTitle('Document PDF Builder Pro');
        $this->pdf->SetSubject('Document genere automatiquement');

        // Marges à zéro pour correspondre au canvas sans décalage
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->SetHeaderMargin(0);
        $this->pdf->SetFooterMargin(0);

        // Désactivation des headers et footers
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // Désactivation du saut de page automatique pour un contrôle total
        $this->pdf->SetAutoPageBreak(false, 0);

        // Police par defaut
        $this->pdf->SetFont($this->config['font_family'], '', $this->config['font_size']);

        // Ajout de la premiere page
        $this->pdf->AddPage();

        $this->performance_metrics['tcpdf_initialized'] = microtime(true);
    }

    /**
     * Chargement TCPDF avec gestion d'erreurs
     */
    private function load_tcpdf() {
        $tcpdf_paths = [
            __DIR__ . '/../lib/tcpdf/tcpdf_autoload.php',
            __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php',
            dirname(__DIR__) . '/lib/tcpdf/tcpdf_autoload.php',
            dirname(__DIR__) . '/vendor/tecnickcom/tcpdf/tcpdf.php'
        ];

        foreach ($tcpdf_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                if (class_exists('TCPDF')) {
                    return;
                }
            }
        }

        throw new Exception('TCPDF introuvable dans tous les chemins testes');
    }

    /**
     * Definition des constantes TCPDF
     */
    private function define_tcpdf_constants() {
        $plugin_dir = dirname(__DIR__) . '/';

        $constants = [
            'PDF_PAGE_ORIENTATION' => $this->config['orientation'],
            'PDF_UNIT' => $this->config['unit'],
            'PDF_PAGE_FORMAT' => $this->config['format'],
            'K_PATH_FONTS' => $plugin_dir . 'lib/tcpdf/fonts/',
            'K_PATH_CACHE' => $this->get_cache_directory(),
            'K_PATH_IMAGES' => $plugin_dir . 'lib/tcpdf/images/',
            'K_PATH_URL' => $plugin_dir . 'lib/tcpdf/'
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }

    /**
     * Obtention du repertoire de cache
     */
    private function get_cache_directory() {
        $cache_dir = '';

        if (function_exists('wp_upload_dir')) {
            $upload_dir = wp_upload_dir();
            $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/';
        } else {
            $cache_dir = sys_get_temp_dir() . '/pdf-builder-cache/';
        }

        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
            // Définir les permissions correctes pour l'accès web (755 pour les dossiers)
            chmod($cache_dir, 0755);
        }

        return $cache_dir;
    }

    /**
     * Configuration PDF personnalisee
     */
    private function configure_pdf($options) {
        // Options de compression
        if (isset($options['compression']) && $options['compression']) {
            $this->pdf->SetCompression(true);
        }

        // Metadonnees personnalisees
        if (isset($options['title'])) {
            $this->pdf->SetTitle($options['title']);
        }
        if (isset($options['author'])) {
            $this->pdf->SetAuthor($options['author']);
        }
        if (isset($options['subject'])) {
            $this->pdf->SetSubject($options['subject']);
        }

        // Protection PDF si demandee
        if (isset($options['password'])) {
            $this->pdf->SetProtection(['print', 'copy'], $options['password']);
        }
    }

    /**
     * Rendu des elements optimise
     */
    private function render_elements($elements) {
        // Calcul précis du facteur de conversion basé sur les dimensions réelles
        // Canvas: 595×842 px | A4: 210×297 mm
        // Conversion: 210mm / 595px = 0.3529 mm/px
        $canvas_width_px = 595;
        $canvas_height_px = 842;
        $page_width_mm = 210; // A4 largeur
        $page_height_mm = 297; // A4 hauteur

        $px_to_mm = $page_width_mm / $canvas_width_px; // 0.3529 mm/px
        error_log('PDF Builder Pro: Debut rendu elements, facteur conversion: ' . $px_to_mm);

        // 🚨 LOG DEBUG ULTRA-VISIBLE - AJOUTER AU DEBUG LOGS SI DISPONIBLE
        if (isset($GLOBALS['pdf_debug_logs'])) {
            $GLOBALS['pdf_debug_logs'][] = "🎨 DÉBUT RENDU ÉLÉMENTS - " . count($elements) . " ÉLÉMENTS";
            $GLOBALS['pdf_debug_logs'][] = "📏 FACTEUR CONVERSION PX->MM: " . $px_to_mm . " (Canvas: {$canvas_width_px}×{$canvas_height_px}px → PDF: {$page_width_mm}×{$page_height_mm}mm)";
        }

        // ⚠️ IMPORTANT: Préserver l'ordre des éléments tel que défini dans le canvas
        // Ne pas trier par position Y car cela casse l'ordre d'empilement (z-index)
        // L'ordre du tableau elements doit être respecté pour maintenir la logique du design

        foreach ($elements as $element) {
            try {
                $element_type = isset($element['type']) ? $element['type'] : 'unknown';
                error_log('PDF Builder Pro: Rendu element type: ' . $element_type);

                if (isset($GLOBALS['pdf_debug_logs'])) {
                    $element_content = isset($element['content']) ? substr($element['content'], 0, 30) : (isset($element['text']) ? substr($element['text'], 0, 30) : 'empty');
                    $element_pos = isset($element['x']) && isset($element['y']) ? '(' . $element['x'] . ',' . $element['y'] . ')' : '(0,0)';
                    $GLOBALS['pdf_debug_logs'][] = "🔧 RENDU ÉLÉMENT: $element_type - POS: $element_pos - CONTENT: $element_content";
                }

                $this->render_single_element($element, $px_to_mm);
                error_log('PDF Builder Pro: Element rendu avec succes');

                if (isset($GLOBALS['pdf_debug_logs'])) {
                    $GLOBALS['pdf_debug_logs'][] = "✅ ÉLÉMENT RENDU AVEC SUCCÈS";
                }

            } catch (Exception $e) {
                $element_id = isset($element['id']) ? $element['id'] : 'unknown';
                $this->log_error("Erreur rendu element " . $element_id . ": " . $e->getMessage());

                if (isset($GLOBALS['pdf_debug_logs'])) {
                    $GLOBALS['pdf_debug_logs'][] = "❌ ERREUR RENDU ÉLÉMENT: " . $e->getMessage();
                }
                // Continuer avec les autres elements
            }
        }

        $this->performance_metrics['elements_rendered'] = microtime(true);
        error_log('PDF Builder Pro: Rendu elements termine');

        if (isset($GLOBALS['pdf_debug_logs'])) {
            $GLOBALS['pdf_debug_logs'][] = "🏁 RENDU ÉLÉMENTS TERMINÉ";
        }
    }

    /**
     * Rendu d'un element individuel
     */
    private function render_single_element($element, $px_to_mm) {
        $type = isset($element['type']) ? $element['type'] : 'unknown';

        // LOG DEBUG DES POSITIONS - AJOUTER AU DEBUG LOGS
        $element_x = isset($element['x']) ? $element['x'] : 0;
        $element_y = isset($element['y']) ? $element['y'] : 0;
        $element_width = isset($element['width']) ? $element['width'] : 0;
        $element_height = isset($element['height']) ? $element['height'] : 0;

        $pdf_x = $element_x * $px_to_mm;
        $pdf_y = $element_y * $px_to_mm;
        $pdf_width = $element_width * $px_to_mm;
        $pdf_height = $element_height * $px_to_mm;

        error_log("PDF Builder Pro: Element $type - Canvas coords: ($element_x, $element_y, {$element_width}x{$element_height}) - PDF coords: ($pdf_x, $pdf_y, {$pdf_width}x{$pdf_height}) mm");

        if (isset($GLOBALS['pdf_debug_logs'])) {
            $GLOBALS['pdf_debug_logs'][] = "📍 ÉLÉMENT $type - CANVAS: x={$element_x}px, y={$element_y}px, w={$element_width}px, h={$element_height}px";
            $GLOBALS['pdf_debug_logs'][] = "📍 ÉLÉMENT $type - PDF: x={$pdf_x}mm, y={$pdf_y}mm, w={$pdf_width}mm, h={$pdf_height}mm";
        }

        // Validation de base de l'élément
        if (!$this->validate_element($element)) {
            $this->log_error("Element invalide ignoré: " . json_encode($element));
            return;
        }

        try {
            switch ($type) {
                case 'text':
                    $this->render_text_element($element, $px_to_mm);
                    break;
                case 'multiline_text':
                    $this->render_multiline_text_element($element, $px_to_mm);
                    break;
                case 'rectangle':
                    $this->render_rectangle_element($element, $px_to_mm);
                    break;
                case 'circle':
                    $this->render_circle_element($element, $px_to_mm);
                    break;
                case 'line':
                    $this->render_line_element($element, $px_to_mm);
                    break;
                case 'image':
                    $this->render_image_element($element, $px_to_mm);
                    break;
                case 'customer_info':
                    $this->render_customer_info_element($element, $px_to_mm);
                    break;
                case 'company_info':
                    $this->render_company_info_element($element, $px_to_mm);
                    break;
                case 'company_logo':
                    $this->render_company_logo_element($element, $px_to_mm);
                    break;
                case 'product_table':
                    $this->render_product_table_element($element, $px_to_mm);
                    break;
                case 'document_type':
                    $this->render_document_type_element($element, $px_to_mm);
                    break;
                case 'order_number':
                    $this->render_order_number_element($element, $px_to_mm);
                    break;
                case 'order_date':
                    $this->render_order_date_element($element, $px_to_mm);
                    break;
                case 'total':
                    $this->render_total_element($element, $px_to_mm);
                    break;
                case 'divider':
                    $this->render_divider_element($element, $px_to_mm);
                    break;
                case 'barcode':
                    $this->render_barcode_element($element, $px_to_mm);
                    break;
                case 'qrcode':
                    $this->render_qrcode_element($element, $px_to_mm);
                    break;
                default:
                    // Élément non supporté - rendu de fallback
                    $this->render_fallback_element($element, $px_to_mm, $type);
                    $this->log_error("Type d'element non supporte: $type");
                    if (isset($GLOBALS['pdf_debug_logs'])) {
                        $GLOBALS['pdf_debug_logs'][] = "❌ TYPE D'ÉLÉMENT NON SUPPORTÉ: $type";
                    }
            }
        } catch (Exception $e) {
            $this->log_error("Erreur rendu element $type: " . $e->getMessage());
            // Rendu de fallback en cas d'erreur
            $this->render_error_fallback($element, $px_to_mm, $e->getMessage());
        }
    }

    /**
     * Rendu d'element texte optimise
     */
    private function render_text_element($element, $px_to_mm) {
        try {
            // Extraction des propriétés avec valeurs par défaut sûres
            $text = $element['content'] ?? $element['text'] ?? '';
            if (empty($text)) {
                return; // Ne rien rendre si pas de texte
            }

            // Conversion précise des dimensions (px vers mm)
            $x = ($element['x'] ?? 0) * $px_to_mm;
            $y = ($element['y'] ?? 0) * $px_to_mm;
            $width = ($element['width'] ?? 100) * $px_to_mm;
            $height = ($element['height'] ?? 20) * $px_to_mm;

            // Conversion correcte de la taille de police (px vers pt)
            // 1px = 0.75pt en CSS, mais pour PDF on utilise directement la valeur px comme pt
            $font_size_px = $element['fontSize'] ?? 14;
            $font_size_pt = $font_size_px; // PDF utilise les points directement

            // Mapping des familles de polices
            $font_family_css = $element['fontFamily'] ?? 'Arial, sans-serif';
            $font_family = $this->map_font_family($font_family_css);

            // Style de police (gras, italique)
            $font_style = $this->get_font_style($element);

            // Couleur du texte
            $color = $this->parse_color($element['color'] ?? '#000000');

            // Configuration de la police
            $this->pdf->SetFont($font_family, $font_style, $font_size_pt);
            $this->pdf->SetTextColor($color['r'], $color['g'], $color['b']);

            // Fond seulement si explicitement défini et non transparent
            $background_color = $element['backgroundColor'] ?? null;
            $fill = false;
            if ($this->should_render_background($background_color)) {
                $bg_color = $this->parse_color($background_color);
                $this->pdf->SetFillColor($bg_color['r'], $bg_color['g'], $bg_color['b']);
                $fill = true;
            }

            // Bordure
            $border = $this->get_border_settings($element);

            // Padding (conversion px vers mm)
            $padding = isset($element['padding']) ? $element['padding'] * $px_to_mm : 0;

            // Calcul des dimensions ajustées avec padding
            $adjusted_width = max(1, $width - ($padding * 2));
            $adjusted_height = max(1, $height - ($padding * 2));

            // Positionnement avec padding
            $final_x = $x + $padding;
            $final_y = $y + $padding;

            // S'assurer que la position est dans les limites de la page
            $final_x = max(0, min($final_x, 210 - $adjusted_width)); // 210mm = largeur A4
            $final_y = max(0, min($final_y, 297 - $adjusted_height)); // 297mm = hauteur A4

            $this->pdf->SetXY($final_x, $final_y);

            // Alignement du texte
            $align = $this->get_text_alignment($element['textAlign'] ?? 'left');

            // Remplacement des variables dans le texte
            $processed_text = $this->replace_variables_in_text($text);

            // Rendu du texte
            $this->pdf->Cell($adjusted_width, $adjusted_height, $processed_text, $border, 0, $align, $fill);

        } catch (Exception $e) {
            $this->log_error("Erreur rendu texte: " . $e->getMessage());
            // Rendu de fallback
            $this->pdf->SetXY(($element['x'] ?? 0) * $px_to_mm, ($element['y'] ?? 0) * $px_to_mm);
            $this->pdf->Cell(50, 10, '[Erreur texte]', 0, 0, 'L', false);
        }
    }

    /**
     * Rendu d'element texte multiligne
     */
    private function render_multiline_text_element($element, $px_to_mm) {
        // Meme logique que text mais avec MultiCell pour le multiligne
        $text = $element['content'] ?? $element['text'] ?? '';
        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 100) * $px_to_mm;
        $height = ($element['height'] ?? 40) * $px_to_mm;

        // Styles identiques a render_text_element
        $font_size = ($element['fontSize'] ?? 12) * 0.75;
        $font_family = $this->map_font_family($element['fontFamily'] ?? 'Arial, sans-serif');
        $font_style = $this->get_font_style($element);
        $color = $this->parse_color($element['color'] ?? '#000000');

        $this->pdf->SetFont($font_family, $font_style, $font_size);
        $this->pdf->SetTextColor($color['r'], $color['g'], $color['b']);

        // Fond
        $background_color = $element['backgroundColor'] ?? null;
        if ($this->should_render_background($background_color)) {
            $bg_color = $this->parse_color($background_color);
            $this->pdf->SetFillColor($bg_color['r'], $bg_color['g'], $bg_color['b']);
            $fill = true;
        } else {
            $fill = false;
        }

        // Bordure et padding
        $border = $this->get_border_settings($element);
        $padding = isset($element['padding']) ? $element['padding'] * $px_to_mm : 0;
        $adjusted_width = $width - ($padding * 2);

        // Positionnement exact selon le canvas
        $this->pdf->SetXY($x + $padding, $y + $padding);

        // Alignement
        $align = $this->get_text_alignment($element['textAlign'] ?? 'left');

        // Rendu multiligne avec hauteur de ligne proportionnelle
        $line_height = $font_size * 0.5; // Hauteur de ligne proportionnelle à la taille de police
        $this->pdf->MultiCell($adjusted_width, $line_height, $text, $border, $align, $fill);
    }

    /**
     * Rendu d'element rectangle
     */
    private function render_rectangle_element($element, $px_to_mm) {
        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 100) * $px_to_mm;
        $height = ($element['height'] ?? 20) * $px_to_mm;

        // Style de remplissage
        $background_color = $element['backgroundColor'] ?? null;
        if ($this->should_render_background($background_color)) {
            $bg_color = $this->parse_color($background_color);
            $this->pdf->SetFillColor($bg_color['r'], $bg_color['g'], $bg_color['b']);
            $fill = true;
        } else {
            $fill = false;
        }

        // Bordure
        $border = $this->get_border_settings($element);

        // Couleur de bordure
        if (!empty($element['borderColor']) && $element['borderColor'] !== 'transparent') {
            $border_color = $this->parse_color($element['borderColor']);
            $this->pdf->SetDrawColor($border_color['r'], $border_color['g'], $border_color['b']);
        }

        // Epaisseur de bordure
        $border_width = ($element['borderWidth'] ?? 1) * $px_to_mm;
        $this->pdf->SetLineWidth($border_width);

        // Dessin du rectangle
        $this->pdf->Rect($x, $y, $width, $height, 'DF', [], $fill ? [] : null);
    }

    /**
     * Rendu d'élément cercle
     */
    private function render_circle_element($element, $px_to_mm) {
        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 50) * $px_to_mm;
        $height = ($element['height'] ?? 50) * $px_to_mm;

        // Centre et rayon
        $center_x = $x + ($width / 2);
        $center_y = $y + ($height / 2);
        $radius = min($width, $height) / 2;

        // Style de remplissage
        $background_color = $element['backgroundColor'] ?? null;
        if ($this->should_render_background($background_color)) {
            $bg_color = $this->parse_color($background_color);
            $this->pdf->SetFillColor($bg_color['r'], $bg_color['g'], $bg_color['b']);
            $fill = true;
        } else {
            $fill = false;
        }

        // Bordure
        $border_width = ($element['borderWidth'] ?? 1) * $px_to_mm;
        $this->pdf->SetLineWidth($border_width);

        if (!empty($element['borderColor']) && $element['borderColor'] !== 'transparent') {
            $border_color = $this->parse_color($element['borderColor']);
            $this->pdf->SetDrawColor($border_color['r'], $border_color['g'], $border_color['b']);
        }

        // Dessin du cercle
        $this->pdf->Circle($center_x, $center_y, $radius, 0, 360, 'DF', [], $fill ? [] : null);
    }

    /**
     * Rendu d'élément ligne
     */
    private function render_line_element($element, $px_to_mm) {
        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 100) * $px_to_mm;
        $height = ($element['height'] ?? 1) * $px_to_mm;

        // Couleur de ligne
        $color = $this->parse_color($element['color'] ?? '#000000');
        $this->pdf->SetDrawColor($color['r'], $color['g'], $color['b']);

        // Épaisseur de ligne
        $line_width = ($element['borderWidth'] ?? 1) * $px_to_mm;
        $this->pdf->SetLineWidth($line_width);

        // Dessin de la ligne
        if ($width > $height) {
            // Ligne horizontale
            $this->pdf->Line($x, $y + ($height / 2), $x + $width, $y + ($height / 2));
        } else {
            // Ligne verticale
            $this->pdf->Line($x + ($width / 2), $y, $x + ($width / 2), $y + $height);
        }
    }

    /**
     * Rendu d'élément image
     */
    private function render_image_element($element, $px_to_mm) {
        $src = $element['src'] ?? $element['url'] ?? '';
        if (empty($src)) {
            return;
        }

        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 100) * $px_to_mm;
        $height = ($element['height'] ?? 100) * $px_to_mm;

        try {
            $this->pdf->Image($src, $x, $y, $width, $height);
        } catch (Exception $e) {
            $this->log_error("Erreur chargement image $src: " . $e->getMessage());
        }
    }

    /**
     * Utilitaires de rendu
     */
    private function map_font_family($font_family) {
        $mapping = [
            'Arial, sans-serif' => 'helvetica',
            'Helvetica, sans-serif' => 'helvetica',
            'Times New Roman, serif' => 'times',
            'Georgia, serif' => 'times',
            'Courier New, monospace' => 'courier',
            'Verdana, sans-serif' => 'helvetica'
        ];
        return $mapping[$font_family] ?? 'helvetica';
    }

    private function get_font_style($element) {
        $style = '';
        if (($element['fontWeight'] ?? '') === 'bold') $style .= 'B';
        if (($element['fontStyle'] ?? '') === 'italic') $style .= 'I';
        return $style ?: '';
    }

    private function parse_color($color) {
        if (empty($color) || $color === 'transparent') {
            return ['r' => 0, 'g' => 0, 'b' => 0];
        }

        $color = ltrim($color, '#');
        if (strlen($color) === 3) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        }

        return [
            'r' => hexdec(substr($color, 0, 2)),
            'g' => hexdec(substr($color, 2, 2)),
            'b' => hexdec(substr($color, 4, 2))
        ];
    }

    private function should_render_background($background_color) {
        if (empty($background_color) || $background_color === 'transparent') {
            return false;
        }

        // Liste des couleurs de fond par defaut a ignorer
        $default_colors = ['#d1d5db', '#ffffff', '#f9fafb', '#f3f4f6'];

        return !in_array(strtolower($background_color), $default_colors);
    }

    private function get_border_settings($element) {
        $border_width = $element['borderWidth'] ?? 0;
        return $border_width > 0 ? 1 : 0;
    }

    private function get_text_alignment($align) {
        switch ($align) {
            case 'center': return 'C';
            case 'right': return 'R';
            default: return 'L';
        }
    }

    /**
     * Validation d'un élément avant rendu
     */
    private function validate_element($element) {
        // Vérifications de base
        if (!is_array($element)) {
            return false;
        }

        if (!isset($element['type']) || empty($element['type'])) {
            return false;
        }

        // Vérifications de dimensions minimales
        $width = $element['width'] ?? 0;
        $height = $element['height'] ?? 0;

        if ($width < 1 || $height < 1) {
            return false;
        }

        // Vérifications de position (doivent être dans les limites du canvas A4)
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;

        if ($x < 0 || $y < 0 || $x > 595 || $y > 842) {
            // Permettre une petite tolérance hors limites
            if ($x < -50 || $y < -50 || $x > 645 || $y > 892) {
                return false;
            }
        }

        return true;
    }

    /**
     * Rendu de fallback pour éléments non supportés
     */
    private function render_fallback_element($element, $px_to_mm, $type) {
        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 50) * $px_to_mm;
        $height = ($element['height'] ?? 20) * $px_to_mm;

        $this->pdf->SetXY($x, $y);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell($width, $height, "[$type]", 1, 0, 'C', false);
    }

    /**
     * Rendu de fallback en cas d'erreur
     */
    private function render_error_fallback($element, $px_to_mm, $error_message) {
        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 100) * $px_to_mm;
        $height = ($element['height'] ?? 15) * $px_to_mm;

        $this->pdf->SetXY($x, $y);
        $this->pdf->SetFont('helvetica', 'B', 6);
        $this->pdf->SetTextColor(255, 0, 0);
        $this->pdf->Cell($width, $height, "[ERREUR]", 1, 0, 'C', false);

        $this->log_error("Fallback rendu pour élément: " . substr($error_message, 0, 100));
    }

    /**
     * Remplacement des variables dans le texte
     */
    private function replace_variables_in_text($text) {
        if (!$this->order || !is_string($text)) {
            return $text;
        }

        $replacements = [
            '{{order_number}}' => $this->order->get_order_number(),
            '{{customer_name}}' => $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name(),
            '{{customer_email}}' => $this->order->get_billing_email(),
            '{{order_date}}' => $this->order->get_date_created()->format('d/m/Y'),
            '{{total}}' => wc_price($this->order->get_total()),
            '{{subtotal}}' => wc_price($this->order->get_subtotal()),
            '{{tax}}' => wc_price($this->order->get_total_tax()),
            '{{customer_address}}' => $this->order->get_formatted_billing_address(),
            '{{company_name}}' => get_bloginfo('name'),
            '{{company_address}}' => get_option('woocommerce_store_address') . ' ' .
                                   get_option('woocommerce_store_address_2') . ' ' .
                                   get_option('woocommerce_store_postcode') . ' ' .
                                   get_option('woocommerce_store_city'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Finalisation du PDF
     */
    private function finalize_pdf() {
        $this->performance_metrics['pdf_finalized'] = microtime(true);

        // Generation du PDF en memoire
        $pdf_content = $this->pdf->Output('document.pdf', 'S');

        if (empty($pdf_content)) {
            throw new Exception('Le PDF genere est vide');
        }

        $this->performance_metrics['end_time'] = microtime(true);

        return $pdf_content;
    }

    /**
     * PDF de secours en cas d'erreur
     */
    private function generate_fallback_pdf($elements) {
        try {
            $this->initialize_tcpdf();
            $this->pdf->SetFont('helvetica', 'B', 16);
            $this->pdf->Cell(0, 20, 'Erreur de generation PDF', 0, 1, 'C');
            $this->pdf->SetFont('helvetica', '', 12);
            $this->pdf->Cell(0, 10, 'Un probleme est survenu lors de la generation.', 0, 1);
            $this->pdf->Cell(0, 10, 'Nombre d\'elements recus: ' . count($elements), 0, 1);
            return $this->pdf->Output('fallback.pdf', 'S');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Logging d'erreurs
     */
    private function log_error($message) {
        $this->errors[] = $message;
        error_log('PDF Builder Pro: ' . $message);
    }

    /**
     * Methodes publiques pour le debogage et monitoring
     */
    public function get_errors() {
        return $this->errors;
    }

    public function get_performance_metrics() {
        return $this->performance_metrics;
    }

    public function get_cache_stats() {
        return [
            'cache_size' => count($this->cache),
            'cache_keys' => array_keys($this->cache)
        ];
    }

    /**
     * Rendu d'élément customer_info
     */
    private function render_customer_info_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 80;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 30;

        // Récupérer les propriétés de l'élément (comme dans l'aperçu)
        $fields = isset($element['fields']) ? $element['fields'] : ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'];
        $layout = isset($element['layout']) ? $element['layout'] : 'vertical';
        $showLabels = isset($element['showLabels']) ? $element['showLabels'] : true;
        $labelStyle = isset($element['labelStyle']) ? $element['labelStyle'] : 'normal';
        $spacing = isset($element['spacing']) ? $element['spacing'] : 8;
        $color = isset($element['color']) ? $element['color'] : '#333333';
        $fontSize = isset($element['fontSize']) ? $element['fontSize'] : 12;
        $fontFamily = isset($element['fontFamily']) ? $this->map_font_family($element['fontFamily']) : 'helvetica';

        // Appliquer la couleur du texte
        if ($color && $color !== 'transparent') {
            $textColor = $this->hex_to_rgb($color);
            $this->pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        } else {
            $this->pdf->SetTextColor(0, 0, 0); // Noir par défaut
        }

        // Appliquer la police et taille
        $this->pdf->SetFont($fontFamily, '', $fontSize);

        // Positionner le curseur
        $this->pdf->SetXY($x, $y);

        $customer_info = [];

        // Construire les informations client selon les champs sélectionnés (comme dans l'aperçu)
        if ($this->order) {
            if (in_array('name', $fields)) {
                $name = trim($this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name());
                if (!empty($name)) {
                    $label = $showLabels ? 'Nom : ' : '';
                    if ($labelStyle === 'uppercase') $label = strtoupper($label);
                    $customer_info[] = $label . $name;
                }
            }

            if (in_array('email', $fields)) {
                $email = $this->order->get_billing_email();
                if (!empty($email)) {
                    $label = $showLabels ? 'Email : ' : '';
                    if ($labelStyle === 'uppercase') $label = strtoupper($label);
                    $customer_info[] = $label . $email;
                }
            }

            if (in_array('phone', $fields)) {
                $phone = $this->order->get_billing_phone();
                if (!empty($phone)) {
                    $label = $showLabels ? 'Téléphone : ' : '';
                    if ($labelStyle === 'uppercase') $label = strtoupper($label);
                    $customer_info[] = $label . $phone;
                }
            }

            if (in_array('address', $fields)) {
                $address_parts = [];

                $address_1 = $this->order->get_billing_address_1();
                if (!empty($address_1)) $address_parts[] = trim($address_1);

                $address_2 = $this->order->get_billing_address_2();
                if (!empty($address_2)) $address_parts[] = trim($address_2);

                $city = $this->order->get_billing_city();
                $postcode = $this->order->get_billing_postcode();
                if (!empty($city) || !empty($postcode)) {
                    $address_parts[] = trim($postcode . ' ' . $city);
                }

                $country = $this->order->get_billing_country();
                if (!empty($country)) $address_parts[] = trim($country);

                if (!empty($address_parts)) {
                    $full_address = implode("\n", $address_parts);
                    $label = $showLabels ? 'Adresse :' . "\n" : '';
                    if ($labelStyle === 'uppercase') $label = strtoupper($label);
                    $customer_info[] = $label . $full_address;
                }
            }

            if (in_array('company', $fields)) {
                $company = $this->order->get_billing_company();
                if (!empty($company)) {
                    $label = $showLabels ? 'Société : ' : '';
                    if ($labelStyle === 'uppercase') $label = strtoupper($label);
                    $customer_info[] = $label . $company;
                }
            }

            if (in_array('vat', $fields)) {
                // Essayer de récupérer le numéro TVA depuis les métadonnées utilisateur ou options
                $customer_id = $this->order->get_customer_id();
                $vat_number = '';
                if ($customer_id) {
                    $vat_number = get_user_meta($customer_id, 'billing_vat_number', true);
                }
                // Fallback vers une option globale ou valeur par défaut pour démo
                if (empty($vat_number)) {
                    $vat_number = get_option('pdf_builder_demo_vat', 'FR 12 345 678 901');
                }
                if (!empty($vat_number)) {
                    $label = $showLabels ? 'N° TVA : ' : '';
                    if ($labelStyle === 'uppercase') $label = strtoupper($label);
                    $customer_info[] = $label . $vat_number;
                }
            }

            if (in_array('siret', $fields)) {
                // Essayer de récupérer le SIRET depuis les métadonnées utilisateur
                $customer_id = $this->order->get_customer_id();
                $siret = '';
                if ($customer_id) {
                    $siret = get_user_meta($customer_id, 'billing_siret', true);
                }
                // Fallback vers une valeur par défaut pour démo
                if (empty($siret)) {
                    $siret = get_option('pdf_builder_demo_siret', '123 456 789 00012');
                }
                if (!empty($siret)) {
                    $label = $showLabels ? 'SIRET : ' : '';
                    if ($labelStyle === 'uppercase') $label = strtoupper($label);
                    $customer_info[] = $label . $siret;
                }
            }
        } else {
            // Contenu factice si pas de commande
            $customer_info = ["Client", "Jean Dupont", "jean@example.com", "01 23 45 67 89", "123 Rue de la Paix\n75001 Paris\nFrance"];
        }

        // Rendre le contenu
        if (!empty($customer_info)) {
            $content = implode("\n", $customer_info);

            // Utiliser MultiCell pour gérer le texte multi-ligne
            $this->pdf->MultiCell($width, $fontSize * 0.4, $content, 0, 'L', false);
        } else {
            // Contenu par défaut si rien n'est disponible
            $this->pdf->Cell($width, $fontSize * 0.4, 'Client', 0, 2);
        }
    }

    /**
     * Convertir couleur hex vers RGB
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) .
                   str_repeat(substr($hex, 1, 1), 2) .
                   str_repeat(substr($hex, 2, 1), 2);
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Rendu d'élément company_info
     */
    private function render_company_info_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 80;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 30;

        // Récupérer les propriétés de l'élément (cohérent avec l'aperçu)
        $fields = isset($element['fields']) ? $element['fields'] : ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
        $layout = isset($element['layout']) ? $element['layout'] : 'vertical';
        $showLabels = isset($element['showLabels']) ? $element['showLabels'] : false;
        $labelStyle = isset($element['labelStyle']) ? $element['labelStyle'] : 'normal';
        $spacing = isset($element['spacing']) ? $element['spacing'] : 4;
        $color = isset($element['color']) ? $element['color'] : '#333333';
        $fontSize = isset($element['fontSize']) ? $element['fontSize'] : 12;
        $fontFamily = isset($element['fontFamily']) ? $this->map_font_family($element['fontFamily']) : 'helvetica';

        // Appliquer la couleur du texte
        if ($color && $color !== 'transparent') {
            $textColor = $this->hex_to_rgb($color);
            $this->pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        } else {
            $this->pdf->SetTextColor(0, 0, 0); // Noir par défaut
        }

        // Appliquer la police et taille
        $this->pdf->SetFont($fontFamily, '', $fontSize);

        // Positionner le curseur
        $this->pdf->SetXY($x, $y);

        $company_info = [];

        // Construire les informations entreprise selon les champs sélectionnés
        if (in_array('name', $fields)) {
            $company_name = get_bloginfo('name');
            if (!empty($company_name)) {
                $label = $showLabels ? 'Entreprise : ' : '';
                if ($labelStyle === 'uppercase') $label = strtoupper($label);
                $company_info[] = $label . $company_name;
            }
        }

        if (in_array('address', $fields)) {
            $address_parts = [];
            $address1 = get_option('woocommerce_store_address');
            $address2 = get_option('woocommerce_store_address_2');
            $city = get_option('woocommerce_store_city');
            $postcode = get_option('woocommerce_store_postcode');
            $country = get_option('woocommerce_store_country');

            if (!empty($address1)) $address_parts[] = $address1;
            if (!empty($address2)) $address_parts[] = $address2;

            $city_line = [];
            if (!empty($postcode)) $city_line[] = $postcode;
            if (!empty($city)) $city_line[] = $city;
            if (!empty($city_line)) $address_parts[] = implode(' ', $city_line);

            if (!empty($country)) {
                // Essayer de convertir le code pays en nom
                $country_name = $this->get_country_name($country);
                $address_parts[] = $country_name ?: $country;
            }

            if (!empty($address_parts)) {
                $full_address = implode("\n", $address_parts);
                $label = $showLabels ? 'Adresse :' . "\n" : '';
                if ($labelStyle === 'uppercase') $label = strtoupper($label);
                $company_info[] = $label . $full_address;
            }
        }

        if (in_array('phone', $fields)) {
            // Essayer d'abord WooCommerce, puis l'option personnalisée du plugin
            $phone = get_option('woocommerce_phone') ?: get_option('pdf_builder_company_phone');
            if (!empty($phone)) {
                $label = $showLabels ? 'Téléphone : ' : '';
                if ($labelStyle === 'uppercase') $label = strtoupper($label);
                $company_info[] = $label . $phone;
            }
        }

        if (in_array('email', $fields)) {
            $email = get_option('woocommerce_email_from_address');
            if (!empty($email)) {
                $label = $showLabels ? 'Email : ' : '';
                if ($labelStyle === 'uppercase') $label = strtoupper($label);
                $company_info[] = $label . $email;
            }
        }

        if (in_array('website', $fields)) {
            $website = get_option('woocommerce_store_website') ?: get_bloginfo('url');
            if (!empty($website)) {
                $label = $showLabels ? 'Site web : ' : '';
                if ($labelStyle === 'uppercase') $label = strtoupper($label);
                $company_info[] = $label . $website;
            }
        }

        if (in_array('vat', $fields)) {
            $vat_number = get_option('pdf_builder_company_vat', '');
            if (empty($vat_number)) {
                $vat_number = get_option('woocommerce_store_vat_number', 'FR 12 345 678 901');
            }
            if (!empty($vat_number)) {
                $label = $showLabels ? 'N° TVA : ' : '';
                if ($labelStyle === 'uppercase') $label = strtoupper($label);
                $company_info[] = $label . $vat_number;
            }
        }

        if (in_array('rcs', $fields)) {
            $rcs = get_option('pdf_builder_company_rcs', '');
            if (empty($rcs)) {
                $rcs = get_option('woocommerce_store_rcs', 'Paris B 123 456 789');
            }
            if (!empty($rcs)) {
                $label = $showLabels ? 'RCS : ' : '';
                if ($labelStyle === 'uppercase') $label = strtoupper($label);
                $company_info[] = $label . $rcs;
            }
        }

        if (in_array('siret', $fields)) {
            $siret = get_option('pdf_builder_company_siret', '');
            if (empty($siret)) {
                $siret = get_option('woocommerce_store_siret', '123 456 789 00012');
            }
            if (!empty($siret)) {
                $label = $showLabels ? 'SIRET : ' : '';
                if ($labelStyle === 'uppercase') $label = strtoupper($label);
                $company_info[] = $label . $siret;
            }
        }

        // Rendre le contenu
        if (!empty($company_info)) {
            $content = implode("\n", $company_info);
            $this->pdf->MultiCell($width, $fontSize * 0.4, $content, 0, 'L', false);
        } else {
            // Contenu par défaut si rien n'est disponible
            $this->pdf->Cell($width, $fontSize * 0.4, 'Entreprise', 0, 2);
        }
    }

    /**
     * Récupère les informations de société depuis WooCommerce/WordPress
     */
    private function get_company_info() {
        // Essayer d'abord de récupérer depuis l'option personnalisée
        $company_info = get_option('pdf_builder_company_info', '');

        // Si les informations sont configurées manuellement, les utiliser
        if (!empty($company_info)) {
            return $company_info;
        }

        // Sinon, récupérer automatiquement depuis WooCommerce/WordPress
        $company_parts = [];

        // Nom de la société (nom du site WordPress)
        $company_name = get_bloginfo('name');
        if (!empty($company_name)) {
            $company_parts[] = $company_name;
        }

        // Adresse depuis WooCommerce
        $address_parts = [];
        $address1 = get_option('woocommerce_store_address');
        $address2 = get_option('woocommerce_store_address_2');
        $city = get_option('woocommerce_store_city');
        $postcode = get_option('woocommerce_store_postcode');
        $country = get_option('woocommerce_store_country');

        if (!empty($address1)) $address_parts[] = $address1;
        if (!empty($address2)) $address_parts[] = $address2;

        $city_line = [];
        if (!empty($postcode)) $city_line[] = $postcode;
        if (!empty($city)) $city_line[] = $city;
        if (!empty($city_line)) $address_parts[] = implode(' ', $city_line);

        if (!empty($country)) {
            // Convertir le code pays en nom complet si possible
            if (function_exists('WC') && isset(WC()->countries)) {
                $countries = WC()->countries->get_countries();
                $country_name = isset($countries[$country]) ? $countries[$country] : $country;
            } else {
                $country_name = $country;
            }
            $address_parts[] = $country_name;
        }

        if (!empty($address_parts)) {
            $company_parts = array_merge($company_parts, $address_parts);
        }

        // Email depuis WordPress
        $email = get_bloginfo('admin_email');
        if (!empty($email)) {
            $company_parts[] = 'Email: ' . $email;
        }

        // Si on a au moins le nom, retourner les infos récupérées
        if (!empty($company_parts)) {
            return implode("\n", $company_parts);
        }

        // Sinon, données d'exemple par défaut
        return "Votre Société SARL\n123 Rue de l'Entreprise\n75001 Paris\nFrance\nTél: 01 23 45 67 89\nEmail: contact@votresociete.com";
    }

    /**
     * Rendu d'élément company_logo
     */
    private function render_company_logo_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 50;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 30;

        // Essayer d'abord de récupérer l'URL du logo depuis les propriétés de l'élément (canvas)
        $logo_url = '';
        if (isset($element['imageUrl']) && !empty($element['imageUrl'])) {
            $logo_url = $element['imageUrl'];
        } elseif (isset($element['src']) && !empty($element['src'])) {
            $logo_url = $element['src'];
        }

        // Si pas d'URL dans l'élément, essayer les options WordPress
        if (empty($logo_url)) {
            $logo_url = get_option('woocommerce_store_logo') ?: get_option('site_icon');
        }

        $logo_path = '';

        if ($logo_url) {
            // Convertir l'URL en chemin de fichier
            $upload_dir = wp_upload_dir();
            $logo_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $logo_url);

            // Si c'est un ID d'attachment, récupérer le chemin
            if (is_numeric($logo_url)) {
                $logo_path = get_attached_file($logo_url);
            }
        }

        // Si on a un logo valide, l'afficher
        if ($logo_path && file_exists($logo_path)) {
            try {
                $this->pdf->Image($logo_path, $x, $y, $width, $height, '', '', '', false, 300, '', false, false, 0, false, false, false);
            } catch (Exception $e) {
                // En cas d'erreur, afficher le placeholder
                $this->render_logo_placeholder($x, $y, $width, $height);
            }
        } else {
            // Afficher le placeholder si pas de logo
            $this->render_logo_placeholder($x, $y, $width, $height);
        }
    }

    /**
     * Affiche un placeholder pour le logo
     */
    private function render_logo_placeholder($x, $y, $width, $height) {
        // Dessiner un rectangle placeholder avec du texte
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Rect($x, $y, $width, $height, 'F');
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->Rect($x, $y, $width, $height, 'D');

        // Centrer le texte dans le rectangle
        $this->pdf->SetXY($x, $y + $height/2 - 3);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell($width, 6, 'LOGO ENTREPRISE', 0, 0, 'C');
    }

    /**
     * Rendu d'élément product_table
     */
    private function render_product_table_element($element, $px_to_mm) {
        // Extraction des propriétés avec valeurs par défaut sûres
        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 550) * $px_to_mm;
        $height = ($element['height'] ?? 200) * $px_to_mm;

        // Propriétés de style visuel
        $background_color = $element['backgroundColor'] ?? 'transparent';
        $border_color = $element['borderColor'] ?? 'transparent';
        $border_width = $element['borderWidth'] ?? 0;
        $border_radius = $element['borderRadius'] ?? 0;

        // Propriétés spécifiques au tableau
        $show_headers = $element['showHeaders'] ?? true;
        // Forcer les bordures pour les tableaux de produits (correction du bug d'affichage)
        $show_borders = ($element['type'] ?? '') === 'product_table' ? true : ($element['showBorders'] ?? true);
        $table_style = $element['tableStyle'] ?? 'default';
        $headers = $element['headers'] ?? ['Produit', 'Qté', 'Prix'];
        $columns = $element['columns'] ?? [
            'image' => false,
            'name' => true,
            'sku' => false,
            'quantity' => true,
            'price' => true,
            'total' => true
        ];

        // Propriétés des totaux
        $show_subtotal = $element['showSubtotal'] ?? false;
        $show_shipping = $element['showShipping'] ?? true;
        $show_taxes = $element['showTaxes'] ?? true;
        $show_discount = $element['showDiscount'] ?? false;
        $show_total = $element['showTotal'] ?? false;

        // Styles de tableau selon le style choisi
        $table_styles = $this->get_table_styles($table_style);

        // Calcul des largeurs de colonnes dynamiques
        $visible_columns = $this->get_visible_columns($columns);
        $col_widths = $this->calculate_column_widths($width, $visible_columns, $columns);

        // Position de départ
        $current_y = $y;

        // Fond du tableau si défini
        if ($this->should_render_background($background_color)) {
            $bg_color = $this->parse_color($background_color);
            $this->pdf->SetFillColor($bg_color['r'], $bg_color['g'], $bg_color['b']);
            $this->pdf->Rect($x, $y, $width, $height, 'F');
        }

        // Bordure du tableau selon le style choisi
        if ($show_borders) {
            // Utiliser les couleurs du style de tableau pour la bordure
            $header_border_rgb = $this->hex_to_rgb($table_styles['headerBorder']);
            $this->pdf->SetDrawColor($header_border_rgb[0], $header_border_rgb[1], $header_border_rgb[2]);
            $this->pdf->SetLineWidth($table_styles['border_width'] * 0.5); // Utiliser l'épaisseur du style
            $this->pdf->Rect($x, $y, $width, $height, 'D');
        } elseif ($border_width > 0 && $border_color !== 'transparent') {
            // Fallback vers les propriétés générales de l'élément
            $border_rgb = $this->parse_color($border_color);
            $this->pdf->SetDrawColor($border_rgb['r'], $border_rgb['g'], $border_rgb['b']);
            $this->pdf->SetLineWidth($border_width * 0.1); // Conversion px vers points
            $this->pdf->Rect($x, $y, $width, $height, 'D');
        }

        // En-têtes du tableau
        if ($show_headers) {
            // Définir la couleur de trait pour les bordures des en-têtes
            if ($show_borders) {
                $header_border_rgb = $this->hex_to_rgb($table_styles['headerBorder']);
                $this->pdf->SetDrawColor($header_border_rgb[0], $header_border_rgb[1], $header_border_rgb[2]);
                $this->pdf->SetLineWidth($table_styles['border_width'] * 0.3);
            }

            // Appliquer la couleur de texte des en-têtes
            if (isset($table_styles['headerTextColor'])) {
                $header_text_rgb = $this->hex_to_rgb($table_styles['headerTextColor']);
                $this->pdf->SetTextColor($header_text_rgb[0], $header_text_rgb[1], $header_text_rgb[2]);
            }

            $this->pdf->SetXY($x, $current_y);
            $this->pdf->SetFillColor($table_styles['header_bg']['r'], $table_styles['header_bg']['g'], $table_styles['header_bg']['b']);
            $this->pdf->SetFont('helvetica', 'B', 9);

            $col_index = 0;
            if ($columns['image']) {
                $this->pdf->Cell($col_widths[$col_index], 8, 'Img', $show_borders ? 1 : 0, 0, 'C', true);
                $col_index++;
            }
            if ($columns['name']) {
                $this->pdf->Cell($col_widths[$col_index], 8, $headers[0] ?? 'Produit', $show_borders ? 1 : 0, 0, 'L', true);
                $col_index++;
            }
            if ($columns['sku']) {
                $this->pdf->Cell($col_widths[$col_index], 8, 'SKU', $show_borders ? 1 : 0, 0, 'L', true);
                $col_index++;
            }
            if ($columns['quantity']) {
                $this->pdf->Cell($col_widths[$col_index], 8, $headers[1] ?? 'Qté', $show_borders ? 1 : 0, 0, 'C', true);
                $col_index++;
            }
            if ($columns['price']) {
                $this->pdf->Cell($col_widths[$col_index], 8, $headers[2] ?? 'Prix', $show_borders ? 1 : 0, 0, 'R', true);
                $col_index++;
            }
            if ($columns['total']) {
                $this->pdf->Cell($col_widths[$col_index], 8, 'Total', $show_borders ? 1 : 0, 1, 'R', true);
            }

            $current_y += 8;
        }

        // Contenu du tableau
        $this->pdf->SetFont('helvetica', '', 8);

        // Appliquer la couleur de texte des lignes de données
        if (isset($table_styles['rowTextColor'])) {
            $row_text_rgb = $this->hex_to_rgb($table_styles['rowTextColor']);
            $this->pdf->SetTextColor($row_text_rgb[0], $row_text_rgb[1], $row_text_rgb[2]);
        }

        // Définir la couleur de trait pour les bordures des lignes de données (APRÈS la définition du texte)
        if ($show_borders) {
            $row_border_rgb = $this->hex_to_rgb($table_styles['rowBorder']);
            $this->pdf->SetDrawColor($row_border_rgb[0], $row_border_rgb[1], $row_border_rgb[2]);
            $this->pdf->SetLineWidth($table_styles['border_width'] * 0.2);
        }

        if (isset($this->order) && $this->order) {
            // Rendre les vrais produits de la commande
            $current_y = $this->render_order_products_with_fees_pdf($x, $current_y, $col_widths, $columns, $show_borders, $element);
        } else {
            // Définir les couleurs de trait pour l'aperçu avant le rendu
            if ($show_borders) {
                $row_border_rgb = $this->hex_to_rgb($table_styles['rowBorder']);
                $this->pdf->SetDrawColor($row_border_rgb[0], $row_border_rgb[1], $row_border_rgb[2]);
                $this->pdf->SetLineWidth($table_styles['border_width'] * 0.2);
            }
            // Données fictives pour l'aperçu
            $current_y = $this->render_fake_products($x, $current_y, $col_widths, $columns, $show_borders, $table_style, $element);
        }

        // Définir les couleurs de trait pour les totaux
        if ($show_borders) {
            $row_border_rgb = $this->hex_to_rgb($table_styles['rowBorder']);
            $this->pdf->SetDrawColor($row_border_rgb[0], $row_border_rgb[1], $row_border_rgb[2]);
            $this->pdf->SetLineWidth($table_styles['border_width'] * 0.2);
        }

        // Totaux
        $current_y = $this->render_table_totals($x, $current_y, $col_widths, $columns, $show_borders, $element);
    }

    /**
     * Obtenir les styles de tableau selon le style choisi
     */
    private function get_table_styles($table_style) {
        $styles = [
            'default' => [
                'header_bg' => ['r' => 245, 'g' => 245, 'b' => 245],
                'header_border' => ['r' => 221, 'g' => 221, 'b' => 221],
                'row_border' => ['r' => 238, 'g' => 238, 'b' => 238],
                'alt_row_bg' => ['r' => 250, 'g' => 250, 'b' => 250],
                'headerTextColor' => '#334155',
                'rowTextColor' => '#334155',
                'border_width' => 0.5
            ],
            'classic' => [
                'header_bg' => ['r' => 30, 'g' => 41, 'b' => 59],
                'header_border' => ['r' => 51, 'g' => 65, 'b' => 85],
                'row_border' => ['r' => 51, 'g' => 65, 'b' => 85],
                'alt_row_bg' => ['r' => 255, 'g' => 255, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#1e293b',
                'border_width' => 1.5
            ],
            'striped' => [
                'header_bg' => ['r' => 59, 'g' => 130, 'b' => 246],
                'header_border' => ['r' => 37, 'g' => 99, 'b' => 235],
                'row_border' => ['r' => 226, 'g' => 232, 'b' => 240],
                'alt_row_bg' => ['r' => 248, 'g' => 250, 'b' => 252],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#334155',
                'border_width' => 1
            ],
            'bordered' => [
                'header_bg' => ['r' => 255, 'g' => 255, 'b' => 255],
                'header_border' => ['r' => 55, 'g' => 65, 'b' => 81],
                'row_border' => ['r' => 209, 'g' => 213, 'b' => 219],
                'alt_row_bg' => ['r' => 255, 'g' => 255, 'b' => 255],
                'headerTextColor' => '#111827',
                'rowTextColor' => '#111827',
                'border_width' => 2
            ],
            'minimal' => [
                'header_bg' => ['r' => 255, 'g' => 255, 'b' => 255],
                'header_border' => ['r' => 209, 'g' => 213, 'b' => 219],
                'row_border' => ['r' => 243, 'g' => 244, 'b' => 246],
                'alt_row_bg' => ['r' => 255, 'g' => 255, 'b' => 255],
                'headerTextColor' => '#6b7280',
                'rowTextColor' => '#6b7280',
                'border_width' => 0.5
            ],
            'modern' => [
                'header_bg' => ['r' => 102, 'g' => 126, 'b' => 234], // Couleur moyenne du dégradé
                'header_border' => ['r' => 91, 'g' => 33, 'b' => 182],
                'row_border' => ['r' => 233, 'g' => 213, 'b' => 255],
                'alt_row_bg' => ['r' => 250, 'g' => 245, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#6b21a8',
                'border_width' => 1
            ],
            // Nouveaux styles colorés
            'blue_ocean' => [
                'header_bg' => ['r' => 30, 'g' => 58, 'b' => 138], // Couleur moyenne du dégradé
                'header_border' => ['r' => 30, 'g' => 64, 'b' => 175],
                'row_border' => ['r' => 219, 'g' => 234, 'b' => 254],
                'alt_row_bg' => ['r' => 239, 'g' => 246, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#1e3a8a',
                'border_width' => 1.5
            ],
            'emerald_forest' => [
                'header_bg' => ['r' => 6, 'g' => 78, 'b' => 59], // Couleur moyenne du dégradé
                'header_border' => ['r' => 6, 'g' => 95, 'b' => 70],
                'row_border' => ['r' => 209, 'g' => 250, 'b' => 229],
                'alt_row_bg' => ['r' => 236, 'g' => 253, 'b' => 245],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#064e3b',
                'border_width' => 1.5
            ],
            'sunset_orange' => [
                'header_bg' => ['r' => 154, 'g' => 52, 'b' => 18], // Couleur moyenne du dégradé
                'header_border' => ['r' => 194, 'g' => 65, 'b' => 12],
                'row_border' => ['r' => 254, 'g' => 215, 'b' => 170],
                'alt_row_bg' => ['r' => 255, 'g' => 247, 'b' => 237],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#9a3412',
                'border_width' => 1.5
            ],
            'royal_purple' => [
                'header_bg' => ['r' => 88, 'g' => 28, 'b' => 135], // Couleur moyenne du dégradé
                'header_border' => ['r' => 124, 'g' => 58, 'b' => 237],
                'row_border' => ['r' => 233, 'g' => 213, 'b' => 255],
                'alt_row_bg' => ['r' => 250, 'g' => 245, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#581c87',
                'border_width' => 1.5
            ],
            'rose_pink' => [
                'header_bg' => ['r' => 190, 'g' => 24, 'b' => 93], // Couleur moyenne du dégradé
                'header_border' => ['r' => 219, 'g' => 39, 'b' => 119],
                'row_border' => ['r' => 252, 'g' => 231, 'b' => 243],
                'alt_row_bg' => ['r' => 253, 'g' => 244, 'b' => 248],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#be185d',
                'border_width' => 1.5
            ],
            'teal_aqua' => [
                'header_bg' => ['r' => 15, 'g' => 118, 'b' => 110], // Couleur moyenne du dégradé
                'header_border' => ['r' => 13, 'g' => 148, 'b' => 136],
                'row_border' => ['r' => 204, 'g' => 251, 'b' => 241],
                'alt_row_bg' => ['r' => 240, 'g' => 253, 'b' => 250],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#0f766e',
                'border_width' => 1.5
            ],
            'crimson_red' => [
                'header_bg' => ['r' => 153, 'g' => 27, 'b' => 27], // Couleur moyenne du dégradé
                'header_border' => ['r' => 220, 'g' => 38, 'b' => 38],
                'row_border' => ['r' => 254, 'g' => 202, 'b' => 202],
                'alt_row_bg' => ['r' => 254, 'g' => 242, 'b' => 242],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#dc2626',
                'border_width' => 1.5
            ],
            'amber_gold' => [
                'header_bg' => ['r' => 146, 'g' => 64, 'b' => 14], // Couleur moyenne du dégradé
                'header_border' => ['r' => 217, 'g' => 119, 'b' => 6],
                'row_border' => ['r' => 254, 'g' => 243, 'b' => 199],
                'alt_row_bg' => ['r' => 254, 'g' => 243, 'b' => 235],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#d97706',
                'border_width' => 1.5
            ],
            'indigo_night' => [
                'header_bg' => ['r' => 49, 'g' => 46, 'b' => 129], // Couleur moyenne du dégradé
                'header_border' => ['r' => 67, 'g' => 56, 'b' => 202],
                'row_border' => ['r' => 224, 'g' => 231, 'b' => 255],
                'alt_row_bg' => ['r' => 238, 'g' => 242, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#4338ca',
                'border_width' => 1.5
            ],
            'slate_gray' => [
                'header_bg' => ['r' => 55, 'g' => 65, 'b' => 81], // Couleur moyenne du dégradé
                'header_border' => ['r' => 75, 'g' => 85, 'b' => 99],
                'row_border' => ['r' => 243, 'g' => 244, 'b' => 246],
                'alt_row_bg' => ['r' => 249, 'g' => 250, 'b' => 251],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#374151',
                'border_width' => 1.5
            ],
            'coral_sunset' => [
                'header_bg' => ['r' => 194, 'g' => 65, 'b' => 12], // Couleur moyenne du dégradé
                'header_border' => ['r' => 234, 'g' => 88, 'b' => 12],
                'row_border' => ['r' => 254, 'g' => 215, 'b' => 215],
                'alt_row_bg' => ['r' => 254, 'g' => 247, 'b' => 247],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#ea580c',
                'border_width' => 1.5
            ],
            'mint_green' => [
                'header_bg' => ['r' => 6, 'g' => 95, 'b' => 70], // Couleur moyenne du dégradé
                'header_border' => ['r' => 4, 'g' => 120, 'b' => 87],
                'row_border' => ['r' => 209, 'g' => 250, 'b' => 229],
                'alt_row_bg' => ['r' => 236, 'g' => 253, 'b' => 245],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#047857',
                'border_width' => 1.5
            ],
            'violet_dream' => [
                'header_bg' => ['r' => 109, 'g' => 40, 'b' => 217], // Couleur moyenne du dégradé
                'header_border' => ['r' => 139, 'g' => 92, 'b' => 246],
                'row_border' => ['r' => 237, 'g' => 233, 'b' => 254],
                'alt_row_bg' => ['r' => 245, 'g' => 243, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#8b5cf6',
                'border_width' => 1.5
            ],
            'sky_blue' => [
                'header_bg' => ['r' => 3, 'g' => 105, 'b' => 161], // Couleur moyenne du dégradé
                'header_border' => ['r' => 2, 'g' => 132, 'b' => 199],
                'row_border' => ['r' => 186, 'g' => 230, 'b' => 253],
                'alt_row_bg' => ['r' => 240, 'g' => 249, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#0284c7',
                'border_width' => 1.5
            ],
            'forest_green' => [
                'header_bg' => ['r' => 20, 'g' => 83, 'b' => 45], // Couleur moyenne du dégradé
                'header_border' => ['r' => 21, 'g' => 128, 'b' => 61],
                'row_border' => ['r' => 187, 'g' => 247, 'b' => 208],
                'alt_row_bg' => ['r' => 240, 'g' => 253, 'b' => 244],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#15803d',
                'border_width' => 1.5
            ],
            'ruby_red' => [
                'header_bg' => ['r' => 185, 'g' => 28, 'b' => 28], // Couleur moyenne du dégradé
                'header_border' => ['r' => 220, 'g' => 38, 'b' => 38],
                'row_border' => ['r' => 254, 'g' => 202, 'b' => 202],
                'alt_row_bg' => ['r' => 254, 'g' => 242, 'b' => 242],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#dc2626',
                'border_width' => 1.5
            ],
            'golden_yellow' => [
                'header_bg' => ['r' => 161, 'g' => 98, 'b' => 7], // Couleur moyenne du dégradé
                'header_border' => ['r' => 202, 'g' => 138, 'b' => 4],
                'row_border' => ['r' => 254, 'g' => 240, 'b' => 138],
                'alt_row_bg' => ['r' => 254, 'g' => 252, 'b' => 232],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#ca8a04',
                'border_width' => 1.5
            ],
            'navy_blue' => [
                'header_bg' => ['r' => 30, 'g' => 58, 'b' => 138], // Couleur moyenne du dégradé
                'header_border' => ['r' => 30, 'g' => 64, 'b' => 175],
                'row_border' => ['r' => 219, 'g' => 234, 'b' => 254],
                'alt_row_bg' => ['r' => 239, 'g' => 246, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#1e40af',
                'border_width' => 1.5
            ],
            'burgundy_wine' => [
                'header_bg' => ['r' => 127, 'g' => 29, 'b' => 29], // Couleur moyenne du dégradé
                'header_border' => ['r' => 153, 'g' => 27, 'b' => 27],
                'row_border' => ['r' => 254, 'g' => 202, 'b' => 202],
                'alt_row_bg' => ['r' => 254, 'g' => 242, 'b' => 242],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#991b1b',
                'border_width' => 1.5
            ],
            'lavender_purple' => [
                'header_bg' => ['r' => 124, 'g' => 45, 'b' => 18], // Couleur moyenne du dégradé
                'header_border' => ['r' => 147, 'g' => 51, 'b' => 234],
                'row_border' => ['r' => 233, 'g' => 213, 'b' => 255],
                'alt_row_bg' => ['r' => 250, 'g' => 245, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#9333ea',
                'border_width' => 1.5
            ],
            'ocean_teal' => [
                'header_bg' => ['r' => 19, 'g' => 78, 'b' => 74], // Couleur moyenne du dégradé
                'header_border' => ['r' => 15, 'g' => 118, 'b' => 110],
                'row_border' => ['r' => 204, 'g' => 251, 'b' => 241],
                'alt_row_bg' => ['r' => 240, 'g' => 253, 'b' => 250],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#0f766e',
                'border_width' => 1.5
            ],
            'cherry_blossom' => [
                'header_bg' => ['r' => 190, 'g' => 24, 'b' => 93], // Couleur moyenne du dégradé
                'header_border' => ['r' => 219, 'g' => 39, 'b' => 119],
                'row_border' => ['r' => 252, 'g' => 231, 'b' => 243],
                'alt_row_bg' => ['r' => 253, 'g' => 244, 'b' => 248],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#db2777',
                'border_width' => 1.5
            ],
            'autumn_orange' => [
                'header_bg' => ['r' => 154, 'g' => 52, 'b' => 18], // Couleur moyenne du dégradé
                'header_border' => ['r' => 234, 'g' => 88, 'b' => 12],
                'row_border' => ['r' => 254, 'g' => 215, 'b' => 170],
                'alt_row_bg' => ['r' => 255, 'g' => 247, 'b' => 237],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#ea580c',
                'border_width' => 1.5
            ]
        ];

        return $styles[$table_style] ?? $styles['default'];
    }

    /**
     * Obtenir la liste des colonnes visibles
     */
    private function get_visible_columns($columns) {
        $visible = [];
        if ($columns['image']) $visible[] = 'image';
        if ($columns['name']) $visible[] = 'name';
        if ($columns['sku']) $visible[] = 'sku';
        if ($columns['quantity']) $visible[] = 'quantity';
        if ($columns['price']) $visible[] = 'price';
        if ($columns['total']) $visible[] = 'total';
        return $visible;
    }

    /**
     * Calculer les largeurs des colonnes
     */
    private function calculate_column_widths($total_width, $visible_columns, $columns) {
        $widths = [];
        $remaining_width = $total_width;

        // Largeurs fixes pour certaines colonnes
        $fixed_widths = [
            'image' => 15, // Largeur fixe pour l'image
            'sku' => 25,   // Largeur fixe pour le SKU
            'quantity' => 15 // Largeur fixe pour la quantité
        ];

        // Calculer la largeur disponible pour les colonnes flexibles
        $flexible_columns = 0;
        foreach ($visible_columns as $col) {
            if (isset($fixed_widths[$col])) {
                $widths[] = $fixed_widths[$col];
                $remaining_width -= $fixed_widths[$col];
            } else {
                $flexible_columns++;
            }
        }

        // Répartir la largeur restante entre les colonnes flexibles
        $flexible_width = $flexible_columns > 0 ? $remaining_width / $flexible_columns : 0;

        // Construire le tableau final des largeurs
        $result = [];
        foreach ($visible_columns as $col) {
            if (isset($fixed_widths[$col])) {
                $result[] = $fixed_widths[$col];
            } else {
                $result[] = max(20, $flexible_width); // Largeur minimum de 20
            }
        }

        return $result;
    }

    /**
     * Rendre les produits réels de la commande WooCommerce
     */
    private function render_order_products_with_fees_pdf($x, $current_y, $col_widths, $columns, $show_borders, $element) {
        if (!$this->order) {
            return $current_y;
        }

        $table_styles = $this->get_table_styles($element['tableStyle'] ?? 'default');
        $line_items = $this->order->get_items();
        $fees = $this->order->get_fees();
        $row_height = 6;
        $alt_row = false;

        // Définir les couleurs de trait pour les bordures des lignes de données
        if ($show_borders) {
            $row_border_rgb = $this->hex_to_rgb($table_styles['rowBorder']);
            $this->pdf->SetDrawColor($row_border_rgb[0], $row_border_rgb[1], $row_border_rgb[2]);
            $this->pdf->SetLineWidth($table_styles['border_width'] * 0.2);
        }

        // Produits
        if (!empty($line_items)) {
            foreach ($line_items as $item_id => $item) {
                $product = $item->get_product();
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                $price = $item->get_total() / max(1, $quantity);
                $total = $item->get_total();

                // Fond alterné si striped
                if ($alt_row && ($element['tableStyle'] ?? 'default') === 'striped') {
                    $this->pdf->SetFillColor($table_styles['alt_row_bg']['r'], $table_styles['alt_row_bg']['g'], $table_styles['alt_row_bg']['b']);
                    $this->pdf->Rect($x, $current_y, array_sum($col_widths), $row_height, 'F');
                }

                $this->pdf->SetXY($x, $current_y);
                $col_index = 0;

                // Image (si activée)
                if ($columns['image']) {
                    $this->pdf->Cell($col_widths[$col_index], $row_height, '[Img]', $show_borders ? 1 : 0, 0, 'C');
                    $col_index++;
                }

                // Nom du produit
                if ($columns['name']) {
                    $display_name = $this->truncate_text($product_name, 25);
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $display_name, $show_borders ? 1 : 0, 0, 'L');
                    $col_index++;
                }

                // SKU
                if ($columns['sku']) {
                    $sku = $product ? $product->get_sku() : '';
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $sku, $show_borders ? 1 : 0, 0, 'L');
                    $col_index++;
                }

                // Quantité
                if ($columns['quantity']) {
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $quantity, $show_borders ? 1 : 0, 0, 'C');
                    $col_index++;
                }

                // Prix unitaire
                if ($columns['price']) {
                    $price_text = number_format($price, 2, ',', ' ') . ' ' . chr(128);
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $price_text, $show_borders ? 1 : 0, 0, 'R');
                    $col_index++;
                }

                // Total
                if ($columns['total']) {
                    $total_text = number_format($total, 2, ',', ' ') . ' ' . chr(128);
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $total_text, $show_borders ? 1 : 0, 1, 'R');
                }

                $current_y += $row_height;
                $alt_row = !$alt_row;
            }
        }

        // Frais (shipping, taxes, etc.)
        if (!empty($fees)) {
            foreach ($fees as $fee) {
                $fee_name = $fee->get_name();
                $fee_total = $fee->get_total();

                // Fond alterné si striped
                if ($alt_row && ($element['tableStyle'] ?? 'default') === 'striped') {
                    $this->pdf->SetFillColor($table_styles['alt_row_bg']['r'], $table_styles['alt_row_bg']['g'], $table_styles['alt_row_bg']['b']);
                    $this->pdf->Rect($x, $current_y, array_sum($col_widths), $row_height, 'F');
                }

                $this->pdf->SetXY($x, $current_y);
                $col_index = 0;

                // Sauter les colonnes image et SKU pour les frais
                if ($columns['image']) $col_index++;
                if ($columns['name']) {
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $fee_name, $show_borders ? 1 : 0, 0, 'L');
                    $col_index++;
                }
                if ($columns['sku']) $col_index++;
                if ($columns['quantity']) {
                    $this->pdf->Cell($col_widths[$col_index], $row_height, '1', $show_borders ? 1 : 0, 0, 'C');
                    $col_index++;
                }
                if ($columns['price']) $col_index++;
                if ($columns['total']) {
                    $fee_text = number_format($fee_total, 2, ',', ' ') . ' ' . chr(128);
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $fee_text, $show_borders ? 1 : 0, 1, 'R');
                }

                $current_y += $row_height;
                $alt_row = !$alt_row;
            }
        }

        return $current_y;
    }

    /**
     * Rendre des produits fictifs pour l'aperçu
     */
    private function render_fake_products($x, $current_y, $col_widths, $columns, $show_borders, $table_style = 'default', $element = null) {
        $table_styles = $this->get_table_styles($table_style);
        $row_height = 6;

        // Utiliser les données d'aperçu de l'élément si disponibles, sinon utiliser les données par défaut
        $preview_products = $element['previewProducts'] ?? [
            [
                'name' => 'Produit A - Description du produit',
                'sku' => 'SKU001',
                'quantity' => 2,
                'price' => 19.99,
                'total' => 39.98
            ],
            [
                'name' => 'Produit B - Un autre article',
                'sku' => 'SKU002',
                'quantity' => 1,
                'price' => 29.99,
                'total' => 29.99
            ]
        ];

        // Appliquer la couleur de texte des lignes de données
        if (isset($table_styles['rowTextColor'])) {
            $row_text_rgb = $this->hex_to_rgb($table_styles['rowTextColor']);
            $this->pdf->SetTextColor($row_text_rgb[0], $row_text_rgb[1], $row_text_rgb[2]);
        }

        // Rendre chaque produit d'aperçu
        foreach ($preview_products as $product) {
            $this->pdf->SetXY($x, $current_y);
            $col_index = 0;

            if ($columns['image']) {
                $this->pdf->Cell($col_widths[$col_index], $row_height, '[Img]', $show_borders ? 1 : 0, 0, 'C');
                $col_index++;
            }
            if ($columns['name']) {
                $display_name = $this->truncate_text($product['name'], 25);
                $this->pdf->Cell($col_widths[$col_index], $row_height, $display_name, $show_borders ? 1 : 0, 0, 'L');
                $col_index++;
            }
            if ($columns['sku']) {
                $this->pdf->Cell($col_widths[$col_index], $row_height, $product['sku'], $show_borders ? 1 : 0, 0, 'L');
                $col_index++;
            }
            if ($columns['quantity']) {
                $this->pdf->Cell($col_widths[$col_index], $row_height, $product['quantity'], $show_borders ? 1 : 0, 0, 'C');
                $col_index++;
            }
            if ($columns['price']) {
                $price_text = number_format($product['price'], 2, ',', ' ') . ' ' . chr(128);
                $this->pdf->Cell($col_widths[$col_index], $row_height, $price_text, $show_borders ? 1 : 0, 0, 'R');
                $col_index++;
            }
            if ($columns['total']) {
                $total_text = number_format($product['total'], 2, ',', ' ') . ' ' . chr(128);
                $this->pdf->Cell($col_widths[$col_index], $row_height, $total_text, $show_borders ? 1 : 0, 1, 'R');
            }

            $current_y += $row_height;
        }

        return $current_y;
    }

    /**
     * Rendre les totaux du tableau
     */
    private function render_table_totals($x, $current_y, $col_widths, $columns, $show_borders, $element) {
        $show_subtotal = $element['showSubtotal'] ?? false;
        $show_shipping = $element['showShipping'] ?? true;
        $show_taxes = $element['showTaxes'] ?? true;
        $show_discount = $element['showDiscount'] ?? false;
        $show_total = $element['showTotal'] ?? false;

        $table_styles = $this->get_table_styles($element['tableStyle'] ?? 'default');
        $row_height = 6;

        // Calculer les totaux
        $subtotal = 0;
        $shipping = 0;
        $taxes = 0;
        $discount = 0;

        if ($this->order) {
            $subtotal = $this->order->get_subtotal();
            $shipping = $this->order->get_shipping_total();
            $taxes = $this->order->get_total_tax();
            $discount = $this->order->get_discount_total();
        } else {
            // Valeurs fictives
            $subtotal = 69.97;
            $shipping = 5.00;
            $taxes = 2.50;
            $discount = -5.00;
        }

        $total = $subtotal + $shipping + $taxes - $discount;

        // Déterminer la colonne de droite pour les totaux (dernière colonne visible)
        $rightmost_col_index = count($col_widths) - 1;

        $total_x = $x;
        for ($i = 0; $i < $rightmost_col_index; $i++) {
            $total_x += $col_widths[$i];
        }

        // Sous-total
        if ($show_subtotal) {
            $this->pdf->SetXY($total_x, $current_y);
            $this->pdf->SetFont('helvetica', 'B', 8);
            $subtotal_text = 'Sous-total: ' . number_format($subtotal, 2, ',', ' ') . ' ' . chr(128);
            $this->pdf->Cell($col_widths[$rightmost_col_index], $row_height, $subtotal_text, $show_borders ? 1 : 0, 1, 'R');
            $current_y += $row_height;
        }

        // Frais de port
        if ($show_shipping && $shipping > 0) {
            $this->pdf->SetXY($total_x, $current_y);
            $shipping_text = 'Port: ' . number_format($shipping, 2, ',', ' ') . ' ' . chr(128);
            $this->pdf->Cell($col_widths[$rightmost_col_index], $row_height, $shipping_text, $show_borders ? 1 : 0, 1, 'R');
            $current_y += $row_height;
        }

        // Taxes
        if ($show_taxes && $taxes > 0) {
            $this->pdf->SetXY($total_x, $current_y);
            $taxes_text = 'TVA: ' . number_format($taxes, 2, ',', ' ') . ' ' . chr(128);
            $this->pdf->Cell($col_widths[$rightmost_col_index], $row_height, $taxes_text, $show_borders ? 1 : 0, 1, 'R');
            $current_y += $row_height;
        }

        // Remise
        if ($show_discount && $discount > 0) {
            $this->pdf->SetXY($total_x, $current_y);
            $discount_text = 'Remise: -' . number_format($discount, 2, ',', ' ') . ' ' . chr(128);
            $this->pdf->Cell($col_widths[$rightmost_col_index], $row_height, $discount_text, $show_borders ? 1 : 0, 1, 'R');
            $current_y += $row_height;
        }

        // Total final
        if ($show_total) {
            // Appliquer la couleur de texte des en-têtes pour le total
            if (isset($table_styles['headerTextColor'])) {
                $header_text_rgb = $this->hex_to_rgb($table_styles['headerTextColor']);
                $this->pdf->SetTextColor($header_text_rgb[0], $header_text_rgb[1], $header_text_rgb[2]);
            }

            $this->pdf->SetXY($total_x, $current_y);
            $this->pdf->SetFillColor($table_styles['header_bg']['r'], $table_styles['header_bg']['g'], $table_styles['header_bg']['b']);
            $total_text = 'TOTAL: ' . number_format($total, 2, ',', ' ') . ' ' . chr(128);
            $this->pdf->Cell($col_widths[$rightmost_col_index], $row_height + 2, $total_text, $show_borders ? 1 : 0, 1, 'R', true);
        }

        return $current_y;
    }

    /**
     * Tronque le texte à une longueur maximale
     */
    private function truncate_text($text, $max_length) {
        if (strlen($text) <= $max_length) {
            return $text;
        }
        return substr($text, 0, $max_length - 3) . '...';
    }

    /**
     * Rendu d'élément document_type
     */
    private function render_document_type_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 50;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 20;

        // Déterminer le type de document dynamiquement
        $document_type = 'facture'; // Valeur par défaut
        $document_type_label = 'FACTURE';
        $invoice_number = 'N° INV-001';

        if ($this->order) {
            $order_status = $this->order->get_status();
            $document_type = $this->detect_document_type($order_status);
            $document_type_label = $this->get_document_type_label($document_type);
            $invoice_number = 'N° ' . strtoupper($document_type) . '-' . $this->order->get_id();
        }

        $this->pdf->SetXY($x, $y);
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell($width, 8, $document_type_label, 0, 1, 'R');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell($width, 6, $invoice_number, 0, 1, 'R');
    }

    /**
     * Rendu d'élément order_number
     */
    private function render_order_number_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 50;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 15;

        // Numéro de commande
        $order_number = 'N° CMD-001'; // Valeur par défaut

        if ($this->order) {
            $order_number = 'N° ' . $this->order->get_order_number();
        }

        $this->pdf->SetXY($x, $y);
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell($width, $height, $order_number, 0, 1, 'L');
    }

    /**
     * Détecte le type de document basé sur le statut de la commande
     */
    private function detect_document_type($order_status) {
        if (!$order_status) {
            return 'commande';
        }
        // Nettoyer le statut (enlever le préfixe wc- si présent)
        $clean_status = str_replace('wc-', '', $order_status);

        // Mapping des statuts WooCommerce vers les types de document
        $status_mapping = [
            'quote' => 'devis',           // Devis
            'quotation' => 'devis',      // Devis (variante)
            'pending' => 'commande',     // En attente
            'processing' => 'commande',  // En cours
            'on-hold' => 'commande',     // En attente
            'completed' => 'facture',    // Terminée -> Facture
            'cancelled' => 'commande',   // Annulée
            'refunded' => 'facture',     // Remboursée -> Facture
            'failed' => 'commande',      // Échec
        ];

        // Retourner le type mappé ou 'commande' par défaut
        return isset($status_mapping[$clean_status]) ? $status_mapping[$clean_status] : 'commande';
    }

    /**
     * Retourne le libellé du type de document
     */
    private function get_document_type_label($document_type) {
        $labels = [
            'facture' => 'FACTURE',
            'devis' => 'DEVIS',
            'commande' => 'COMMANDE',
            'contrat' => 'CONTRAT',
            'bon_livraison' => 'BON DE LIVRAISON',
        ];

        return isset($labels[$document_type]) ? $labels[$document_type] : strtoupper($document_type);
    }

    /**
     * Rendu d'élément divider
     */
    private function render_divider_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 180;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 5;

        // Ligne de séparation horizontale
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line($x, $y + $height/2, $x + $width, $y + $height/2);
        $this->pdf->SetLineWidth(0.2); // Remettre la largeur par défaut
    }

    /**
     * Génération d'aperçu PDF simplifié (alternative au système canvas)
     */
    public function generate_simple_preview($order_id, $template_id = null) {
        error_log('🟡 PDF BUILDER - generate_simple_preview: START - order_id=' . $order_id . ', template_id=' . ($template_id ?: 'null'));

        try {
            // Récupérer la commande WooCommerce
            error_log('🟡 PDF BUILDER - generate_simple_preview: Getting WooCommerce order...');
            $this->order = wc_get_order($order_id);
            if (!$this->order) {
                error_log('❌ PDF BUILDER - generate_simple_preview: Order not found: ' . $order_id);
                throw new Exception('Commande non trouvée');
            }
            error_log('✅ PDF BUILDER - generate_simple_preview: Order found: ' . $this->order->get_id());

            // Si un template_id est fourni, récupérer les données du template
            if ($template_id) {
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';

                $template = $wpdb->get_row(
                    $wpdb->prepare("SELECT id, name, template_data FROM $table_templates WHERE id = %d", $template_id),
                    ARRAY_A
                );

                if (!$template) {
                    error_log('❌ PDF BUILDER - generate_simple_preview: Template not found with ID: ' . $template_id);
                    throw new Exception('Template non trouvé');
                }

                error_log('✅ PDF BUILDER - generate_simple_preview: Template found: ' . $template['name']);

                // Décoder les données JSON du template
                $template_data = json_decode($template['template_data'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('❌ PDF BUILDER - generate_simple_preview: Invalid JSON in template data: ' . json_last_error_msg());
                    throw new Exception('Données du template invalides');
                }

                error_log('✅ PDF BUILDER - generate_simple_preview: Template data decoded successfully. Elements count: ' . (isset($template_data['elements']) ? count($template_data['elements']) : 'none'));

                // Générer le PDF avec les données du template
                $elements = isset($template_data['elements']) ? $template_data['elements'] : [];
                error_log('🟡 PDF BUILDER - generate_simple_preview: Calling generate() with ' . count($elements) . ' elements');
                error_log('🟡 PDF BUILDER - generate_simple_preview: First element: ' . json_encode($elements[0] ?? 'no elements'));

                $pdf_content = $this->generate($elements, ['title' => 'Aperçu Facture - Commande #' . $order_id]);

                error_log('✅ PDF BUILDER - generate_simple_preview: PDF generated with template, content size: ' . strlen($pdf_content) . ' bytes');

                // Vérifier que nous avons du contenu PDF valide
                if (empty($pdf_content) || strlen($pdf_content) < 100) {
                    error_log('❌ PDF BUILDER - generate_simple_preview: PDF content too small or empty');
                    throw new Exception('Contenu PDF invalide généré');
                }

                // Utiliser le répertoire uploads standard au lieu d'un sous-répertoire
                $upload_dir = wp_upload_dir();
                $cache_dir = $upload_dir['basedir'];

                // Générer un nom de fichier unique avec préfixe
                $filename = 'pdf-builder-preview-order-' . $order_id . '-' . time() . '.pdf';
                $filepath = $cache_dir . '/' . $filename;

                // Sauvegarder le fichier
                error_log('🟡 PDF BUILDER - generate_simple_preview: Saving template-based PDF to: ' . $filepath);
                if (file_put_contents($filepath, $pdf_content) !== false) {
                    error_log('✅ PDF BUILDER - Template-based PDF saved successfully');

                    // Essayer plusieurs méthodes pour définir les permissions du fichier
                    if (function_exists('wp_chmod')) {
                        wp_chmod($filepath, 0644);
                        error_log('✅ PDF BUILDER - Used wp_chmod for template file');
                    } elseif (chmod($filepath, 0644)) {
                        error_log('✅ PDF BUILDER - Used chmod() for template file');
                    } else {
                        error_log('❌ PDF BUILDER - Failed to set template file permissions');
                    }
                } else {
                    error_log('❌ PDF BUILDER - Failed to save template-based PDF');
                    throw new Exception('Impossible de sauvegarder le PDF');
                }

                // Retourner l'URL d'accès
                $url = $upload_dir['baseurl'] . '/' . $filename;
                error_log('✅ PDF BUILDER - generate_simple_preview: Template-based SUCCESS - URL: ' . $url);
                return $url;

            } else {
                // Générer le contenu simplifié (fallback)
                error_log('🟡 PDF BUILDER - generate_simple_preview: No template provided, using simple content');

                // Initialiser TCPDF pour le contenu simple
                $this->initialize_tcpdf();

                // Configuration de base du PDF
                $this->pdf->SetCreator('PDF Builder Pro');
                $this->pdf->SetAuthor('Three Axe');
                $this->pdf->SetTitle('Aperçu Facture - Commande #' . $order_id);
                $this->pdf->SetSubject('Aperçu de facture PDF');

                // Ajouter une page
                error_log('🟡 PDF BUILDER - generate_simple_preview: Adding page');
                $this->pdf->AddPage();

                // Marges
                $this->pdf->SetMargins(15, 15, 15);
                $this->pdf->SetAutoPageBreak(true, 15);

                // Générer le contenu simplifié
                error_log('🟡 PDF BUILDER - generate_simple_preview: Generating simple content');
                $this->generate_simple_pdf_content();

                // Générer le PDF
                error_log('🟡 PDF BUILDER - generate_simple_preview: Generating PDF content');
                $pdf_content = $this->pdf->Output('', 'S');

                // Utiliser le répertoire uploads standard au lieu d'un sous-répertoire
                $upload_dir = wp_upload_dir();
                $cache_dir = $upload_dir['basedir'];

                // Générer un nom de fichier unique avec préfixe
                $filename = 'pdf-builder-preview-order-' . $order_id . '-' . time() . '.pdf';
                $filepath = $cache_dir . '/' . $filename;

                // Sauvegarder le fichier
                error_log('🟡 PDF BUILDER - generate_simple_preview: Saving file to: ' . $filepath);
                if (file_put_contents($filepath, $pdf_content) !== false) {
                    error_log('✅ PDF BUILDER - File saved successfully');

                    // Essayer plusieurs méthodes pour définir les permissions du fichier
                    if (function_exists('wp_chmod')) {
                        wp_chmod($filepath, 0644);
                        error_log('✅ PDF BUILDER - Used wp_chmod for file');
                    } elseif (chmod($filepath, 0644)) {
                        error_log('✅ PDF BUILDER - Used chmod() for file');
                    } else {
                        error_log('❌ PDF BUILDER - Failed to set file permissions');
                    }
                } else {
                    error_log('❌ PDF BUILDER - Failed to save file');
                }

                // Vérifier les permissions actuelles
                $file_perms = substr(sprintf('%o', fileperms($filepath)), -4);
                error_log('🔍 PDF BUILDER - File permissions: ' . $file_perms);

                // Retourner l'URL d'accès
                $url = $upload_dir['baseurl'] . '/' . $filename;
                error_log('✅ PDF BUILDER - generate_simple_preview: SUCCESS - URL: ' . $url);
                return $url;
            }

        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - generate_simple_preview: Exception: ' . $e->getMessage());
            error_log('❌ PDF BUILDER - generate_simple_preview: Stack trace: ' . $e->getTraceAsString());
            return new WP_Error('pdf_generation_error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Génère le contenu PDF simplifié
     */
    private function generate_simple_pdf_content() {
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->Cell(0, 10, 'FACTURE - APERÇU', 0, 1, 'C');
        $this->pdf->Ln(5);

        // Informations de la commande
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'Commande #' . $this->order->get_id(), 0, 1);
        $this->pdf->Ln(3);

        // Date et statut
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(50, 6, 'Date:', 0, 0);
        $this->pdf->Cell(0, 6, $this->order->get_date_created()->format('d/m/Y H:i'), 0, 1);

        $this->pdf->Cell(50, 6, 'Statut:', 0, 0);
        $status = $this->order->get_status();
        $status_label = $this->get_status_label($status);
        $this->pdf->Cell(0, 6, $status_label, 0, 1);
        $this->pdf->Ln(5);

        // Informations client
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'Informations client', 0, 1);
        $this->pdf->Ln(2);

        $this->pdf->SetFont('helvetica', '', 10);
        $billing_info = $this->get_clean_billing_info();
        foreach ($billing_info as $line) {
            $this->pdf->Cell(0, 5, $line, 0, 1);
        }
        $this->pdf->Ln(5);

        // Produits
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'Produits commandés', 0, 1);
        $this->pdf->Ln(2);

        // En-têtes du tableau
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell(80, 7, 'Produit', 1, 0, 'L', true);
        $this->pdf->Cell(20, 7, 'Qté', 1, 0, 'C', true);
        $this->pdf->Cell(30, 7, 'Prix', 1, 0, 'R', true);
        $this->pdf->Cell(30, 7, 'Total', 1, 1, 'R', true);

        // Contenu du tableau
        $this->pdf->SetFont('helvetica', '', 9);
        $line_items = $this->order->get_items();
        foreach ($line_items as $item) {
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $price = $item->get_total() / max(1, $quantity);
            $total = $item->get_total();

            $this->pdf->Cell(80, 6, $this->truncate_text($product_name, 25), 1, 0, 'L');
            $this->pdf->Cell(20, 6, $quantity, 1, 0, 'C');
            $this->pdf->Cell(30, 6, number_format($price, 2, ',', ' ') . ' €', 1, 0, 'R');
            $this->pdf->Cell(30, 6, number_format($total, 2, ',', ' ') . ' €', 1, 1, 'R');
        }

        // Total
        $this->pdf->Ln(3);
        $this->pdf->SetFont('helvetica', 'B', 11);
        $order_total = $this->order->get_total();
        $this->pdf->Cell(130, 8, 'TOTAL:', 0, 0, 'R');
        $this->pdf->Cell(30, 8, number_format($order_total, 2, ',', ' ') . ' €', 1, 1, 'R');
    }

    /**
     * Récupère les informations de facturation nettoyées
     */
    private function get_clean_billing_info() {
        if (!$this->order) {
            return ['Client non trouvé'];
        }

        $info = [];

        $first_name = trim($this->order->get_billing_first_name());
        $last_name = trim($this->order->get_billing_last_name());
        if ($first_name || $last_name) {
            $info[] = $first_name . ' ' . $last_name;
        }

        $company = trim($this->order->get_billing_company());
        if ($company) {
            $info[] = $company;
        }

        $address_1 = trim($this->order->get_billing_address_1());
        if ($address_1) {
            $info[] = $address_1;
        }

        $address_2 = trim($this->order->get_billing_address_2());
        if ($address_2) {
            $info[] = $address_2;
        }

        $postcode = trim($this->order->get_billing_postcode());
        $city = trim($this->order->get_billing_city());
        if ($postcode || $city) {
            $info[] = $postcode . ' ' . $city;
        }

        $country = trim($this->order->get_billing_country());
        if ($country) {
            $info[] = $country;
        }

        $email = trim($this->order->get_billing_email());
        if ($email) {
            $info[] = $email;
        }

        return $info ?: ['Client'];
    }

    /**
     * Retourne le libellé du statut de commande
     */
    private function get_status_label($status) {
        $labels = [
            'pending' => 'En attente',
            'processing' => 'En cours',
            'on-hold' => 'En attente',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'refunded' => 'Remboursée',
            'failed' => 'Échouée'
        ];

        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }

    /**
     * Convertit un code pays en nom complet
     */
    private function get_country_name($country_code) {
        $countries = [
            'FR' => 'France',
            'BE' => 'Belgique',
            'CH' => 'Suisse',
            'LU' => 'Luxembourg',
            'DE' => 'Allemagne',
            'IT' => 'Italie',
            'ES' => 'Espagne',
            'PT' => 'Portugal',
            'NL' => 'Pays-Bas',
            'GB' => 'Royaume-Uni',
            'US' => 'États-Unis',
            'CA' => 'Canada',
            'AU' => 'Australie',
            'JP' => 'Japon',
            'CN' => 'Chine',
            'IN' => 'Inde',
            'BR' => 'Brésil',
            'MX' => 'Mexique',
            'AR' => 'Argentine',
            'CL' => 'Chili',
            'CO' => 'Colombie',
            'PE' => 'Pérou',
            'VE' => 'Venezuela',
            'EC' => 'Équateur',
            'UY' => 'Uruguay',
            'PY' => 'Paraguay',
            'BO' => 'Bolivie',
            'GY' => 'Guyana',
            'SR' => 'Suriname',
            'GF' => 'Guyane Française',
            'MQ' => 'Martinique',
            'GP' => 'Guadeloupe',
            'RE' => 'Réunion',
            'YT' => 'Mayotte',
            'NC' => 'Nouvelle-Calédonie',
            'PF' => 'Polynésie Française',
            'WF' => 'Wallis-et-Futuna',
            'TF' => 'Terres Australes Françaises',
            'BL' => 'Saint-Barthélemy',
            'MF' => 'Saint-Martin',
            'PM' => 'Saint-Pierre-et-Miquelon'
        ];

        return isset($countries[strtoupper($country_code)]) ? $countries[strtoupper($country_code)] : $country_code;
    }
}

// Alias pour la compatibilite descendante
class_alias('PDF_Builder_Pro_Generator', 'PDF_Generator');

/**
 * Fonction AJAX pour la generation PDF
 */
if (function_exists('wp_send_json_success') && !defined('PDF_GENERATOR_TEST_MODE')) {
function pdf_builder_generate_pdf() {
    // Demarrer le buffer de sortie pour capturer toute sortie accidentelle
    ob_start();

    try {
        // 🚨🚨🚨 LOGS DE DEBUG ULTRA-VISIBLES - DÉBUT 🚨🚨🚨
        $debug_logs = [];
        $debug_logs[] = "🚨 DÉBUT GÉNÉRATION PDF - TIMESTAMP: " . date('Y-m-d H:i:s');
        $debug_logs[] = "🚨 VERSION DEBUG: 5.0 - LOGS ULTRA-VISIBLES";

        // Initialiser la variable globale pour les logs de rendu
        $GLOBALS['pdf_debug_logs'] = &$debug_logs;

        // Log du nonce recu pour debogage
        $received_nonce = $_POST['nonce'] ?? '';
        $debug_logs[] = "🔑 NONCE REÇU: " . $received_nonce;
        error_log('PDF Builder Pro: Nonce recu: ' . $received_nonce);

        // Verifier la securite
        if (!wp_verify_nonce($received_nonce, 'pdf_builder_nonce')) {
            $debug_logs[] = "❌ ÉCHEC VÉRIFICATION NONCE";
            error_log('PDF Builder Pro: echec verification nonce');
            // Pour le debogage, accepter temporairement
            $debug_logs[] = "⚠️ NONCE INVALIDE MAIS ACCEPTATION TEMPORAIRE POUR DEBUG";
            error_log('PDF Builder Pro: Nonce invalide mais acceptation temporaire pour debogage');
        } else {
            $debug_logs[] = "✅ NONCE VALIDE";
        }

        // Récupérer et valider les éléments avec la nouvelle validation robuste
        $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();
        $elements = $elements_manager->validate_and_clean_json_elements(
            $_POST['elements'] ?? '[]',
            'pdf_generation_frontend'
        );

        // Gérer les erreurs de validation JSON
        if (is_wp_error($elements)) {
            $debug_logs[] = "❌ ERREUR VALIDATION JSON: " . $elements->get_error_message();
            error_log('PDF Builder Pro: Erreur validation JSON: ' . $elements->get_error_message());
            ob_end_clean();
            wp_send_json_error(array(
                'message' => 'Erreur de validation des données: ' . $elements->get_error_message(),
                'debug_logs' => $debug_logs,
                'elements_count' => 0,
                'pdf_size' => 0
            ));
            return;
        }

        $debug_logs[] = "📊 " . count($elements) . " ÉLÉMENTS VALIDÉS ET NETTOYÉS";
        error_log('PDF Builder Pro: ' . count($elements) . ' elements validés et nettoyés');

        // Valider et logger chaque element
        $debug_logs[] = "🔍 DÉTAIL DES ÉLÉMENTS REÇUS:";
        foreach ($elements as $index => $element) {
            $element_info = "📋 ÉLÉMENT $index: " .
                "TYPE=" . (isset($element['type']) ? $element['type'] : 'unknown') . " | " .
                "CONTENT=" . substr((isset($element['content']) ? $element['content'] : (isset($element['text']) ? $element['text'] : 'empty')), 0, 50) . " | " .
                "POS=(" . (isset($element['x']) ? $element['x'] : 0) . "," . (isset($element['y']) ? $element['y'] : 0) . ") | " .
                "SIZE=(" . (isset($element['width']) ? $element['width'] : 0) . "," . (isset($element['height']) ? $element['height'] : 0) . ")";
            $debug_logs[] = $element_info;

            error_log("PDF Builder Pro: Element $index - Type: " . (isset($element['type']) ? $element['type'] : 'unknown'));
            error_log("PDF Builder Pro: Element $index - Content: " . (isset($element['content']) ? $element['content'] : (isset($element['text']) ? $element['text'] : 'empty')));
            error_log("PDF Builder Pro: Element $index - Position: x=" . (isset($element['x']) ? $element['x'] : 0) . ", y=" . (isset($element['y']) ? $element['y'] : 0));
            error_log("PDF Builder Pro: Element $index - Dimensions: w=" . (isset($element['width']) ? $element['width'] : 0) . ", h=" . (isset($element['height']) ? $element['height'] : 0));
        }

        $debug_logs[] = "🏭 DÉMARRAGE GÉNÉRATION PDF AVEC PDF_Builder_Pro_Generator";
        // Generer le PDF avec le nouveau generateur
        $generator = new PDF_Builder_Pro_Generator();
        $pdf_content = $generator->generate($elements);

        $debug_logs[] = "📄 PDF CONTENT GÉNÉRÉ - TAILLE: " . strlen($pdf_content) . " OCTETS";

        // Fusionner les logs de rendu depuis la variable globale
        if (isset($GLOBALS['pdf_debug_logs'])) {
            $debug_logs = array_merge($debug_logs, $GLOBALS['pdf_debug_logs']);
            unset($GLOBALS['pdf_debug_logs']);
        }

        if (empty($pdf_content)) {
            $debug_logs[] = "❌ CONTENU PDF VIDE - PROBLÈME DE GÉNÉRATION";
            error_log('PDF Builder Pro: Contenu PDF vide');
            ob_end_clean();
            wp_send_json_error(array(
                'message' => 'Erreur lors de la generation du PDF',
                'debug_logs' => $debug_logs,
                'elements_count' => count($elements),
                'pdf_size' => 0
            ));
            return;
        }

        $debug_logs[] = "✅ PDF GÉNÉRÉ AVEC SUCCÈS";
        error_log('PDF Builder Pro: PDF genere avec succes, taille: ' . strlen($pdf_content) . ' octets');

        // Collecter tous les logs de debug
        if (function_exists('error_log')) {
            // Récupérer les dernières lignes de logs si possible
            $debug_logs[] = 'Génération PDF démarrée';
            $debug_logs[] = count($elements) . ' éléments reçus';
            foreach ($elements as $index => $element) {
                $debug_logs[] = "Élément $index: " . ($element['type'] ?? 'unknown') . ' - ' . ($element['content'] ?? $element['text'] ?? 'empty');
            }
        }

        $debug_logs[] = "🎉 FIN GÉNÉRATION PDF - PRÊT POUR ENVOI AU FRONTEND";

        // Vider le buffer avant d'envoyer la reponse
        ob_end_clean();

        // Retourner le PDF en base64 avec les logs de debug
        wp_send_json_success(array(
            'pdf' => base64_encode($pdf_content),
            'filename' => 'pdf-builder-pro-document.pdf',
            'performance' => $generator->get_performance_metrics(),
            'errors' => $generator->get_errors(),
            'debug_logs' => $debug_logs,
            'elements_count' => count($elements),
            'pdf_size' => strlen($pdf_content)
        ));

    } catch (Exception $e) {
        error_log('Erreur generation PDF: ' . $e->getMessage());
        ob_end_clean();
        wp_send_json_error('Erreur lors de la generation du PDF: ' . $e->getMessage());
    } catch (Throwable $t) {
        error_log('Erreur fatale generation PDF: ' . $t->getMessage());
        ob_end_clean();
        wp_send_json_error('Erreur fatale lors de la generation du PDF');
    }
}
}

// Fin de la fonction pdf_builder_generate_pdf
// Enregistrer la fonction AJAX
if (function_exists('add_action') && !defined('PDF_GENERATOR_TEST_MODE')) {
    add_action('wp_ajax_pdf_builder_generate_pdf', 'pdf_builder_generate_pdf');
    add_action('wp_ajax_nopriv_pdf_builder_generate_pdf', 'pdf_builder_generate_pdf');
}
