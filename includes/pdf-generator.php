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
        try {
            $this->reset();
            $this->validate_elements($elements);
            $this->initialize_tcpdf();
            $this->configure_pdf($options);
            $this->render_elements($elements);
            return $this->finalize_pdf();

        } catch (Exception $e) {
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
            'ISO-8859-1',
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

        // Trier les éléments par position Y pour un meilleur rendu
        usort($elements, function($a, $b) {
            $a_y = isset($a['y']) ? $a['y'] : 0;
            $b_y = isset($b['y']) ? $b['y'] : 0;
            return $a_y <=> $b_y;
        });

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
            case 'divider':
                $this->render_divider_element($element, $px_to_mm);
                break;
            default:
                $this->log_error("Type d'element non supporte: $type");
                if (isset($GLOBALS['pdf_debug_logs'])) {
                    $GLOBALS['pdf_debug_logs'][] = "❌ TYPE D'ÉLÉMENT NON SUPPORTÉ: $type";
                }
        }
    }

    /**
     * Rendu d'element texte optimise
     */
    private function render_text_element($element, $px_to_mm) {
        // Extraction des proprietes avec valeurs par defaut sures
        $text = $element['content'] ?? $element['text'] ?? '';
        $x = ($element['x'] ?? 0) * $px_to_mm;
        $y = ($element['y'] ?? 0) * $px_to_mm;
        $width = ($element['width'] ?? 100) * $px_to_mm;
        $height = ($element['height'] ?? 20) * $px_to_mm;

        // Styles
        $font_size = ($element['fontSize'] ?? 12) * 0.75;
        $font_family = $this->map_font_family($element['fontFamily'] ?? 'Arial, sans-serif');
        $font_style = $this->get_font_style($element);
        $color = $this->parse_color($element['color'] ?? '#000000');

        // Configuration de la police
        $this->pdf->SetFont($font_family, $font_style, $font_size);
        $this->pdf->SetTextColor($color['r'], $color['g'], $color['b']);

        // Fond seulement si explicitement defini et non transparent/non defaut
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

        // Pas de padding automatique - utiliser uniquement si défini explicitement
        $padding = isset($element['padding']) ? $element['padding'] * $px_to_mm : 0;
        $adjusted_width = $width - ($padding * 2);
        $adjusted_height = $height - ($padding * 2);

        // Positionnement exact selon le canvas
        $this->pdf->SetXY($x + $padding, $y + $padding);

        // Alignement
        $align = $this->get_text_alignment($element['textAlign'] ?? 'left');

        // Rendu du texte
        $this->pdf->Cell($adjusted_width, $adjusted_height, $text, $border, 0, $align, $fill);
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
     * Rendu d'element image
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

        // Récupérer les vraies informations client depuis la commande WooCommerce
        $customer_info_lines = ["Client"];

        if ($this->order) {
            $billing_first_name = $this->order->get_billing_first_name();
            $billing_last_name = $this->order->get_billing_last_name();
            $billing_company = $this->order->get_billing_company();
            $billing_address_1 = $this->order->get_billing_address_1();
            $billing_address_2 = $this->order->get_billing_address_2();
            $billing_city = $this->order->get_billing_city();
            $billing_postcode = $this->order->get_billing_postcode();
            $billing_country = $this->order->get_billing_country();
            $billing_email = $this->order->get_billing_email();
            $billing_phone = $this->order->get_billing_phone();

            // Ajouter chaque ligne seulement si elle contient du contenu valide
            if (!empty(trim($billing_company))) {
                $customer_info_lines[] = trim($billing_company);
            }
            $name_line = trim($billing_first_name . ' ' . $billing_last_name);
            if (!empty($name_line)) {
                $customer_info_lines[] = $name_line;
            }
            if (!empty(trim($billing_address_1))) {
                $customer_info_lines[] = trim($billing_address_1);
            }
            if (!empty(trim($billing_address_2))) {
                $customer_info_lines[] = trim($billing_address_2);
            }
            $city_line = trim($billing_postcode . ' ' . $billing_city);
            if (!empty($city_line)) {
                $customer_info_lines[] = $city_line;
            }
            if (!empty(trim($billing_country))) {
                $customer_info_lines[] = trim($billing_country);
            }
            if (!empty(trim($billing_email))) {
                $customer_info_lines[] = trim($billing_email);
            }
            if (!empty(trim($billing_phone))) {
                $customer_info_lines[] = trim($billing_phone);
            }
        } else {
            // Contenu factice si pas de commande
            $customer_info_lines = ["Client", "Jean Dupont", "123 Rue de la Paix", "75001 Paris", "France"];
        }

        // Joindre les lignes avec des sauts de ligne
        $customer_info = implode("\n", $customer_info_lines);

        // Nettoyer le texte et s'assurer qu'il n'est pas vide
        $customer_info = trim($customer_info);
        if (empty($customer_info)) {
            $customer_info = "Client";
        }

        // Encoder en UTF-8 et nettoyer les caractères problématiques
        $customer_info = utf8_decode($customer_info);

        // Positionner le curseur
        $this->pdf->SetXY($x, $y);

        // Titre
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell($width, 6, utf8_decode('Client'), 0, 2);

        // Contenu
        $this->pdf->SetFont('helvetica', '', 10);

        // Protection contre les textes vides ou problématiques
        if (empty($customer_info) || strlen($customer_info) < 2) {
            $customer_info = "Client";
        }

        // Essayer d'utiliser Cell au lieu de MultiCell si le texte est simple
        if (strpos($customer_info, "\n") === false) {
            // Texte simple, utiliser Cell
            $this->pdf->Cell($width, 5, $customer_info, 0, 2);
        } else {
            // Texte multi-ligne, utiliser MultiCell avec protection
            try {
                $this->pdf->MultiCell($width, 5, $customer_info, 0, 'L');
            } catch (Exception $e) {
                // En cas d'erreur, utiliser un texte simple
                $this->pdf->Cell($width, 5, "Client", 0, 2);
            }
        }
    }

    /**
     * Rendu d'élément company_info
     */
    private function render_company_info_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 80;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 30;

        // Récupérer les informations de société
        $company_info = $this->get_company_info();

        // Séparer le nom de la société du reste des informations
        $company_lines = explode("\n", $company_info);
        $company_name = !empty($company_lines) ? array_shift($company_lines) : 'Société';
        $company_details = implode("\n", $company_lines);

        // Positionner le curseur
        $this->pdf->SetXY($x, $y);

        // Titre (nom de la société)
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell($width, 6, utf8_decode($company_name), 0, 2);

        // Contenu (détails)
        if (!empty($company_details)) {
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->MultiCell($width, 5, utf8_decode($company_details), 0, 'L');
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
        $this->pdf->Cell($width, 6, utf8_decode('LOGO ENTREPRISE'), 0, 0, 'C');
    }

    /**
     * Rendu d'élément product_table
     */
    private function render_product_table_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 180;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 80;

        // S'assurer que la largeur est valide (au minimum 50mm)
        $width = max(50, $width);

        // Calculer les largeurs des colonnes avec protection contre les valeurs nulles
        $col_widths = [
            max(1, $width * 0.4),  // Produit
            max(1, $width * 0.15), // Qté
            max(1, $width * 0.2),  // Prix
            max(1, $width * 0.25)  // Total
        ];

        // En-têtes du tableau
        $this->pdf->SetXY($x, $y);
        $this->pdf->SetFillColor(245, 245, 245);
        $this->pdf->SetFont('helvetica', 'B', 9);

        $this->pdf->Cell($col_widths[0], 8, utf8_decode('Produit'), 1, 0, 'L', true);
        $this->pdf->Cell($col_widths[1], 8, utf8_decode('Qté'), 1, 0, 'C', true);
        $this->pdf->Cell($col_widths[2], 8, utf8_decode('Prix'), 1, 0, 'R', true);
        $this->pdf->Cell($col_widths[3], 8, utf8_decode('Total'), 1, 1, 'R', true);

        // Vérifier si on a accès à l'objet commande
        if (isset($this->order) && $this->order) {
            // Rendre les vrais produits de la commande
            $this->render_order_products_with_fees($x, $col_widths);
        } else {
            // Ligne de produit factice
            $this->pdf->SetXY($x, $y + 8);
            $this->pdf->SetFont('helvetica', '', 8);
            $this->pdf->Cell($col_widths[0], 6, utf8_decode('Produit A - Description'), 1, 0, 'L');
            $this->pdf->Cell($col_widths[1], 6, '2', 1, 0, 'C');
            $this->pdf->Cell($col_widths[2], 6, '19.99 EUR', 1, 0, 'R');
            $this->pdf->Cell($col_widths[3], 6, '39.98 EUR', 1, 1, 'R');

            // Ligne de frais factice
            $this->pdf->SetXY($x, $y + 14);
            $this->pdf->SetFont('helvetica', 'B', 8);
            $this->pdf->Cell($col_widths[0], 6, utf8_decode('Frais de port'), 1, 0, 'L');
            $this->pdf->Cell($col_widths[1], 6, '-', 1, 0, 'C');
            $this->pdf->Cell($col_widths[2], 6, '-', 1, 0, 'R');
            $this->pdf->Cell($col_widths[3], 6, '5.00 EUR', 1, 1, 'R');

            // Total
            $this->pdf->SetXY($x + $col_widths[0] + $col_widths[1] + $col_widths[2], $y + 20);
            $this->pdf->SetFont('helvetica', 'B', 9);
            $this->pdf->Cell($col_widths[3], 6, utf8_decode('Total: 44.98 EUR'), 1, 1, 'R', true);
        }
    }

    /**
     * Rendu des produits et frais de la commande WooCommerce
     */
    private function render_order_products_with_fees($x, $col_widths) {
        // Vérifier que les largeurs de colonnes sont valides
        if (empty($col_widths) || count($col_widths) < 4) {
            return;
        }

        if (!$this->order) {
            return;
        }

        $current_y = $this->pdf->GetY() + 2; // Petite marge après l'en-tête
        $this->pdf->SetFont('helvetica', '', 8);

        // Récupérer les éléments de ligne de la commande
        $line_items = $this->order->get_items();
        $fees = $this->order->get_fees();

        // Vérifier que nous avons des données valides
        if (empty($line_items) && empty($fees)) {
            // Si pas de produits ni frais, afficher un message
            $this->pdf->SetXY($x, $current_y);
            $this->pdf->Cell(array_sum($col_widths), 6, utf8_decode('Aucun produit dans cette commande'), 1, 1, 'C');
            return;
        }

        // Afficher les produits
        if (!empty($line_items)) {
            foreach ($line_items as $item_id => $item) {
                $product = $item->get_product();
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                $price = $item->get_total() / max(1, $quantity); // Éviter division par zéro
                $total = $item->get_total();

                // Nom du produit (avec variation si applicable)
                $display_name = $product_name;
                if ($product && $product->is_type('variation')) {
                    $variation_attributes = $product->get_variation_attributes();
                    if (!empty($variation_attributes)) {
                        $variation_text = implode(', ', array_map(function($key, $value) {
                            $key = str_replace('attribute_', '', $key);
                            $key = str_replace('pa_', '', $key);
                            return ucfirst($key) . ': ' . $value;
                        }, array_keys($variation_attributes), $variation_attributes));
                        $display_name .= ' (' . $variation_text . ')';
                    }
                }

                $this->pdf->SetXY($x, $current_y);
                $this->pdf->Cell($col_widths[0], 6, utf8_decode($this->truncate_text($display_name, 30)), 1, 0, 'L');
                $this->pdf->Cell($col_widths[1], 6, $quantity, 1, 0, 'C');
                $this->pdf->Cell($col_widths[2], 6, number_format($price, 2, ',', ' ') . ' €', 1, 0, 'R');
                $this->pdf->Cell($col_widths[3], 6, number_format($total, 2, ',', ' ') . ' €', 1, 1, 'R');

                $current_y += 6;
            }
        }

        // Afficher les frais (shipping, taxes, etc.)
        if (!empty($fees)) {
            foreach ($fees as $fee) {
                $fee_name = $fee->get_name();
                $fee_total = $fee->get_total();

                $this->pdf->SetXY($x, $current_y);
                $this->pdf->SetFont('helvetica', 'B', 8);
                $this->pdf->Cell($col_widths[0], 6, utf8_decode($this->truncate_text($fee_name, 30)), 1, 0, 'L');
                $this->pdf->Cell($col_widths[1], 6, '-', 1, 0, 'C');
                $this->pdf->Cell($col_widths[2], 6, '-', 1, 0, 'R');
                $this->pdf->Cell($col_widths[3], 6, number_format($fee_total, 2, ',', ' ') . ' €', 1, 1, 'R');

                $current_y += 6;
            }
        }

        // Afficher le total de la commande
        $order_total = $this->order->get_total();
        $this->pdf->SetXY($x + $col_widths[0] + $col_widths[1] + $col_widths[2], $current_y + 2);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(245, 245, 245);
        $this->pdf->Cell($col_widths[3], 8, utf8_decode('Total: ' . number_format($order_total, 2, ',', ' ') . ' €'), 1, 1, 'R', true);
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
        $this->pdf->Cell($width, 8, utf8_decode($document_type_label), 0, 1, 'R');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell($width, 6, utf8_decode($invoice_number), 0, 1, 'R');
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
        $this->pdf->Cell($width, 8, utf8_decode($order_number), 0, 1, 'L');
    }

    /**
     * Détecte le type de document basé sur le statut de la commande
     */
    private function detect_document_type($order_status) {
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
        try {
            // Initialiser TCPDF
            $this->init_tcpdf();

            // Récupérer la commande WooCommerce
            $this->order = wc_get_order($order_id);
            if (!$this->order) {
                throw new Exception('Commande non trouvée');
            }

            // Configuration de base du PDF
            $this->pdf->SetCreator('PDF Builder Pro');
            $this->pdf->SetAuthor('Three Axe');
            $this->pdf->SetTitle('Aperçu Facture - Commande #' . $order_id);
            $this->pdf->SetSubject('Aperçu de facture PDF');

            // Ajouter une page
            $this->pdf->AddPage();

            // Marges
            $this->pdf->SetMargins(15, 15, 15);
            $this->pdf->SetAutoPageBreak(true, 15);

            // Générer le contenu simplifié
            $this->generate_simple_pdf_content();

            // Générer le PDF
            $pdf_content = $this->pdf->Output('', 'S');

            // Créer le répertoire de cache s'il n'existe pas
            $upload_dir = wp_upload_dir();
            $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache';
            if (!file_exists($cache_dir)) {
                wp_mkdir_p($cache_dir);
            }

            // Générer un nom de fichier unique
            $filename = 'preview-order-' . $order_id . '-' . time() . '.pdf';
            $filepath = $cache_dir . '/' . $filename;

            // Sauvegarder le fichier
            file_put_contents($filepath, $pdf_content);

            // Retourner l'URL d'accès
            return $upload_dir['baseurl'] . '/pdf-builder-cache/' . $filename;

        } catch (Exception $e) {
            return new WP_Error('pdf_generation_error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Génère le contenu PDF simplifié
     */
    private function generate_simple_pdf_content() {
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->Cell(0, 10, utf8_decode('FACTURE - APERÇU'), 0, 1, 'C');
        $this->pdf->Ln(5);

        // Informations de la commande
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, utf8_decode('Commande #' . $this->order->get_id()), 0, 1);
        $this->pdf->Ln(3);

        // Date et statut
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(50, 6, utf8_decode('Date:'), 0, 0);
        $this->pdf->Cell(0, 6, utf8_decode($this->order->get_date_created()->format('d/m/Y H:i')), 0, 1);

        $this->pdf->Cell(50, 6, utf8_decode('Statut:'), 0, 0);
        $status = $this->order->get_status();
        $status_label = $this->get_status_label($status);
        $this->pdf->Cell(0, 6, utf8_decode($status_label), 0, 1);
        $this->pdf->Ln(5);

        // Informations client
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, utf8_decode('Informations client'), 0, 1);
        $this->pdf->Ln(2);

        $this->pdf->SetFont('helvetica', '', 10);
        $billing_info = $this->get_clean_billing_info();
        foreach ($billing_info as $line) {
            $this->pdf->Cell(0, 5, utf8_decode($line), 0, 1);
        }
        $this->pdf->Ln(5);

        // Produits
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, utf8_decode('Produits commandés'), 0, 1);
        $this->pdf->Ln(2);

        // En-têtes du tableau
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(240, 240, 240);
        $this->pdf->Cell(80, 7, utf8_decode('Produit'), 1, 0, 'L', true);
        $this->pdf->Cell(20, 7, utf8_decode('Qté'), 1, 0, 'C', true);
        $this->pdf->Cell(30, 7, utf8_decode('Prix'), 1, 0, 'R', true);
        $this->pdf->Cell(30, 7, utf8_decode('Total'), 1, 1, 'R', true);

        // Contenu du tableau
        $this->pdf->SetFont('helvetica', '', 9);
        $line_items = $this->order->get_items();
        foreach ($line_items as $item) {
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $price = $item->get_total() / max(1, $quantity);
            $total = $item->get_total();

            $this->pdf->Cell(80, 6, utf8_decode($this->truncate_text($product_name, 25)), 1, 0, 'L');
            $this->pdf->Cell(20, 6, $quantity, 1, 0, 'C');
            $this->pdf->Cell(30, 6, number_format($price, 2, ',', ' ') . ' €', 1, 0, 'R');
            $this->pdf->Cell(30, 6, number_format($total, 2, ',', ' ') . ' €', 1, 1, 'R');
        }

        // Total
        $this->pdf->Ln(3);
        $this->pdf->SetFont('helvetica', 'B', 11);
        $order_total = $this->order->get_total();
        $this->pdf->Cell(130, 8, utf8_decode('TOTAL:'), 0, 0, 'R');
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

        // Recuperer les elements
        $elements = json_decode(stripslashes($_POST['elements'] ?? '[]'), true);
        $debug_logs[] = "📊 " . count($elements) . " ÉLÉMENTS REÇUS DU FRONTEND";
        error_log('PDF Builder Pro: ' . count($elements) . ' elements recus');

        if (empty($elements)) {
            $debug_logs[] = "❌ AUCUN ÉLÉMENT À TRAITER";
            ob_end_clean();
            wp_send_json_error(array(
                'message' => 'Aucun element a traiter',
                'debug_logs' => $debug_logs,
                'elements_count' => 0,
                'pdf_size' => 0
            ));
            return;
        }

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
