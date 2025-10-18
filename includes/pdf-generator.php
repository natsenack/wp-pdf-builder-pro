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
    private $is_preview = false; // Mode aperçu

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
     * Définit si c'est pour l'aperçu
     */
    public function set_preview_mode($is_preview = false) {
        $this->is_preview = $is_preview;
        if ($is_preview) {
            // Dimensions d'aperçu : 400x566px
            $this->config['format'] = [105.8, 149.8]; // Conversion px vers mm à 96dpi
        }
    }

    /**
     * Extrait les coordonnées d'un élément avec support des deux formats
     * Format éditeur: position.x/y, size.width/height
     * Format legacy: x/y, width/height
     */
    private function extract_element_coordinates($element, $px_to_mm = 1) {
        // Support pour les deux formats : position/size (éditeur) et x/y/width/height direct
        $element_x = isset($element['position']['x']) ? $element['position']['x'] : (isset($element['x']) ? $element['x'] : 0);
        $element_y = isset($element['position']['y']) ? $element['position']['y'] : (isset($element['y']) ? $element['y'] : 0);
        $element_width = isset($element['size']['width']) ? $element['size']['width'] : (isset($element['width']) ? $element['width'] : 0);
        $element_height = isset($element['size']['height']) ? $element['size']['height'] : (isset($element['height']) ? $element['height'] : 0);

        // LOG: Coordonnées extraites

        $result = [
            'x' => $element_x * $px_to_mm,
            'y' => $element_y * $px_to_mm,
            'width' => $element_width * $px_to_mm,
            'height' => $element_height * $px_to_mm
        ];


        return $result;
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

        // Configurer le mode aperçu si demandé
        if (isset($options['is_preview']) && $options['is_preview']) {
            $this->set_preview_mode(true);
        }

        try {
            $this->reset();

            $this->validate_elements($elements);

            $this->initialize_tcpdf();

            $this->configure_pdf($options);

            $this->render_elements($elements);

            $pdf_content = $this->finalize_pdf();


            return $pdf_content;

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


        // Vérifier si un élément product_table existe déjà
        $has_product_table = false;
        foreach ($elements as $element) {
            if (isset($element['type']) && $element['type'] === 'product_table') {
                $has_product_table = true;
                break;
            }
        }


        // Si aucun élément product_table n'existe, en ajouter un par défaut
        if (!$has_product_table) {

            $default_product_table = [
                'id' => 'auto_product_table_' . time(),
                'type' => 'product_table',
                'x' => 20,
                'y' => 120,
                'width' => 550,
                'height' => 200,
                'backgroundColor' => 'transparent',
                'borderColor' => 'transparent',
                'borderWidth' => 0,
                'borderStyle' => 'solid',
                'borderRadius' => 0,
                'showHeaders' => true,
                'showBorders' => false,
                'tableStyle' => 'default',
                'columns' => [
                    'image' => false,
                    'name' => true,
                    'sku' => false,
                    'quantity' => true,
                    'price' => true,
                    'total' => true
                ],
                'headers' => ['Produit', 'Qté', 'Prix'],
                'showSubtotal' => false,  // Désactivé par défaut pour l'auto-injection
                'showShipping' => false,  // Désactivé par défaut pour l'auto-injection
                'showTaxes' => false,     // Désactivé par défaut pour l'auto-injection
                'showDiscount' => false,  // Désactivé par défaut pour l'auto-injection
                'showTotal' => true,      // Total activé par défaut
                'visible' => true,
                'opacity' => 100
            ];

            $elements[] = $default_product_table;
        }


        // Calcul précis du facteur de conversion basé sur les dimensions réelles
        // Canvas: 595×842 px | A4: 210×297 mm
        // Conversion: 210mm / 595px = 0.3529 mm/px
        $canvas_width_px = 595;
        $canvas_height_px = 842;
        $page_width_mm = 210; // A4 largeur
        $page_height_mm = 297; // A4 hauteur

        $px_to_mm = $page_width_mm / $canvas_width_px; // 0.3529 mm/px

        // ⚠️ IMPORTANT: Préserver l'ordre des éléments tel que défini dans le canvas
        // Ne pas trier par position Y car cela casse l'ordre d'empilement (z-index)
        // L'ordre du tableau elements doit être respecté pour maintenir la logique du design

        foreach ($elements as $element) {
            try {
                $element_type = isset($element['type']) ? $element['type'] : 'unknown';
                $element_id = isset($element['id']) ? $element['id'] : 'no-id';


                $this->render_single_element($element, $px_to_mm);

            } catch (Exception $e) {
                $element_id = isset($element['id']) ? $element['id'] : 'unknown';
                $this->log_error("Erreur rendu element " . $element_id . ": " . $e->getMessage());
                // Continuer avec les autres elements
            }
        }

        $this->performance_metrics['elements_rendered'] = microtime(true);
    }

    /**
     * Rendu d'un element individuel
     */
    private function render_single_element($element, $px_to_mm) {
        $type = isset($element['type']) ? $element['type'] : 'unknown';

        // Vérifier si l'élément est visible - si non visible, ne pas le rendre
        $is_visible = $element['visible'] ?? true;
        if (!$is_visible) {
            return; // Élément invisible, ignorer le rendu
        }

        // LOG: Début rendu élément

        // Calcul des coordonnées PDF avec support des deux formats
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $pdf_x = $coords['x'];
        $pdf_y = $coords['y'];
        $pdf_width = $coords['width'];
        $pdf_height = $coords['height'];

        // LOG: Coordonnées calculées

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
                case 'mentions':
                    $this->render_mentions_element($element, $px_to_mm);
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
                case 'dynamic-text':
                    $this->render_dynamic_text_element($element, $px_to_mm);
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
            // LOG: Début rendu texte

            // Extraction des propriétés avec valeurs par défaut sûres
            $text = $element['content'] ?? $element['text'] ?? '';
            if (empty($text)) {
                return; // Ne rien rendre si pas de texte
            }

            // Utiliser extract_element_coordinates pour cohérence avec l'éditeur
            $coords = $this->extract_element_coordinates($element, $px_to_mm);
            $x = $coords['x'];
            $y = $coords['y'];
            $width = $coords['width'];
            $height = $coords['height'];

            // LOG: Coordonnées texte

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

            // LOG: Propriétés police

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

            // Padding (conversion px vers mm) - même logique que l'éditeur
            $padding = isset($element['padding']) ? $element['padding'] * $px_to_mm : 0;

            // Calcul des dimensions ajustées avec padding (comme dans l'éditeur)
            $adjusted_width = max(1, $width - ($padding * 2));
            $adjusted_height = max(1, $height - ($padding * 2));

            // Positionnement avec padding (comme dans l'éditeur)
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
            $coords = $this->extract_element_coordinates($element, $px_to_mm);
            $this->pdf->SetXY($coords['x'], $coords['y']);
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
        // LOG: Début rendu rectangle

        // Utiliser extract_element_coordinates pour cohérence avec l'éditeur
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $x = $coords['x'];
        $y = $coords['y'];
        $width = $coords['width'];
        $height = $coords['height'];

        // LOG: Coordonnées rectangle

        // Style de remplissage - utiliser fillColor comme dans l'éditeur
        $background_color = $element['fillColor'] ?? $element['backgroundColor'] ?? null;
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
        } else {
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
        // LOG: Début rendu cercle

        // Utiliser extract_element_coordinates pour cohérence avec l'éditeur
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $x = $coords['x'];
        $y = $coords['y'];
        $width = $coords['width'];
        $height = $coords['height'];

        // Centre et rayon
        $center_x = $x + ($width / 2);
        $center_y = $y + ($height / 2);
        $radius = min($width, $height) / 2;

        // LOG: Géométrie cercle

        // Style de remplissage - utiliser fillColor comme dans l'éditeur
        $background_color = $element['fillColor'] ?? $element['backgroundColor'] ?? null;
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
        } else {
        }

        // Dessin du cercle
        $this->pdf->Circle($center_x, $center_y, $radius, 0, 360, 'DF', [], $fill ? [] : null);
    }

    /**
     * Rendu d'élément numéro de commande
     */
    private function render_order_number_element($element, $px_to_mm) {
        // LOG: Début rendu numéro de commande

        if (!$this->order) {
            return;
        }

        // Utiliser extract_element_coordinates pour cohérence avec l'éditeur
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $x = $coords['x'];
        $y = $coords['y'];
        $width = $coords['width'];
        $height = $coords['height'];

        // LOG: Coordonnées

        // Format du numéro de commande
        $format = $element['format'] ?? 'Commande #{order_number}';
        $order_number = $this->order->get_order_number();
        $text = str_replace('{order_number}', $order_number, $format);

        // LOG: Contenu généré

        // Propriétés de style
        $font_size = ($element['fontSize'] ?? 14) * 0.75; // Conversion px vers pt
        $color = $this->parse_color($element['color'] ?? '#000000');
        $font_family = $this->map_font_family($element['fontFamily'] ?? 'Arial');
        $font_style = $this->get_font_style($element);
        $text_align = $element['textAlign'] ?? 'left';

        // LOG: Styles

        // Configuration de la police
        $this->pdf->SetFont($font_family, $font_style, $font_size);
        $this->pdf->SetTextColor($color['r'], $color['g'], $color['b']);

        // Positionnement et rendu
        $this->pdf->SetXY($x, $y);
        $this->pdf->Cell($width, $height, $text, 0, 0, strtoupper(substr($text_align, 0, 1)));

    }

    /**
     * Rendu d'élément type de document
     */
    private function render_document_type_element($element, $px_to_mm) {
        // LOG: Début rendu type de document

        // Utiliser extract_element_coordinates pour cohérence avec l'éditeur
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $x = $coords['x'];
        $y = $coords['y'];
        $width = $coords['width'];
        $height = $coords['height'];

        // LOG: Coordonnées

        // Type de document
        $doc_type = $element['documentType'] ?? 'invoice';
        $text = $doc_type === 'invoice' ? 'FACTURE' : 'DEVIS';

        // LOG: Contenu

        // Propriétés de style
        $font_size = ($element['fontSize'] ?? 18) * 0.75; // Conversion px vers pt
        $color = $this->parse_color($element['color'] ?? '#1e293b');
        $font_family = $this->map_font_family($element['fontFamily'] ?? 'Arial');
        $font_style = $this->get_font_style($element);
        $text_align = $element['textAlign'] ?? 'center';

        // LOG: Styles

        // Configuration de la police
        $this->pdf->SetFont($font_family, $font_style, $font_size);
        $this->pdf->SetTextColor($color['r'], $color['g'], $color['b']);

        // Positionnement et rendu
        $this->pdf->SetXY($x, $y);
        $this->pdf->Cell($width, $height, $text, 0, 0, strtoupper(substr($text_align, 0, 1)));

    }

    /**
     * Rendu d'élément ligne
     */
    private function render_line_element($element, $px_to_mm) {
        // Pour les lignes, utiliser toute la largeur de la page comme dans l'éditeur
        $page_width_mm = 210; // Largeur A4 en mm
        $x = 0; // Commencer à gauche de la page
        $width = $page_width_mm; // Utiliser toute la largeur

        // Utiliser extract_element_coordinates pour la position Y uniquement
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $y = $coords['y'];
        $height = $coords['height'];

        // LOG: Ligne avec largeur pleine

        // Couleur de ligne (utilise lineColor ou strokeColor ou color)
        $color = $this->parse_color($element['lineColor'] ?? $element['strokeColor'] ?? $element['color'] ?? '#000000');
        $this->pdf->SetDrawColor($color['r'], $color['g'], $color['b']);

        // Épaisseur de ligne (utilise lineWidth ou strokeWidth ou borderWidth)
        $line_width = ($element['lineWidth'] ?? $element['strokeWidth'] ?? $element['borderWidth'] ?? 1) * $px_to_mm;
        $this->pdf->SetLineWidth($line_width);

        // Dessin de la ligne horizontale centrée verticalement dans son rectangle
        $line_y = $y + ($height / 2);
        $this->pdf->Line($x, $line_y, $x + $width, $line_y);

    }

    /**
     * Rendu d'élément image
     */
    private function render_image_element($element, $px_to_mm) {
        $src = $element['src'] ?? $element['imageUrl'] ?? $element['url'] ?? '';
        if (empty($src)) {
            return;
        }

        // LOG: Début rendu image

        // Utiliser extract_element_coordinates pour cohérence avec l'éditeur
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $x = $coords['x'];
        $y = $coords['y'];
        $width = $coords['width'];
        $height = $coords['height'];

        // LOG: Coordonnées image

        try {
            // Vérifier si l'image existe
            if (!file_exists($src) && !filter_var($src, FILTER_VALIDATE_URL)) {
            }

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

        $result = !in_array(strtolower($background_color), $default_colors);
        return $result;
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
     * Rendu d'élément mentions légales
     */
    private function render_mentions_element($element, $px_to_mm) {
        // LOG: Début rendu mentions

        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 80;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 15;

        // LOG: Coordonnées

        // Récupérer les propriétés de l'élément
        $color = isset($element['color']) ? $element['color'] : '#666666';
        $fontSize = isset($element['fontSize']) ? $element['fontSize'] : 8;
        $fontFamily = isset($element['fontFamily']) ? $this->map_font_family($element['fontFamily']) : 'helvetica';
        $textAlign = isset($element['textAlign']) ? $element['textAlign'] : 'center';
        $layout = isset($element['layout']) ? $element['layout'] : 'horizontal';
        $separator = isset($element['separator']) ? $element['separator'] : ' • ';

        // LOG: Propriétés de style
        $lineHeight = isset($element['lineHeight']) ? $element['lineHeight'] : 1.2;

        // Appliquer la couleur du texte
        if ($color && $color !== 'transparent') {
            $textColor = $this->hex_to_rgb($color);
            $this->pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        } else {
            $this->pdf->SetTextColor(102, 102, 102); // Gris par défaut
        }

        // Appliquer la police et taille
        $this->pdf->SetFont($fontFamily, '', $fontSize);

        // Positionner le curseur
        $this->pdf->SetXY($x, $y);

        $mentions = [];

        // Construire les mentions selon les options sélectionnées
        if (isset($element['showEmail']) && $element['showEmail']) {
            $email = get_option('pdf_builder_company_email', 'contact@monsite.com');
            $mentions[] = $email;
        }

        if (isset($element['showPhone']) && $element['showPhone']) {
            $phone = get_option('pdf_builder_company_phone', '01 23 45 67 89');
            $mentions[] = $phone;
        }

        if (isset($element['showSiret']) && $element['showSiret']) {
            $siret = get_option('pdf_builder_company_siret', '123 456 789 00012');
            $mentions[] = 'SIRET: ' . $siret;
        }

        if (isset($element['showVat']) && $element['showVat']) {
            $vat = get_option('pdf_builder_company_vat', 'FR 12 345 678 901');
            $mentions[] = 'TVA: ' . $vat;
        }

        if (isset($element['showAddress']) && $element['showAddress']) {
            $address = get_option('pdf_builder_company_address', '123 Rue de la Paix, 75001 Paris');
            $mentions[] = $address;
        }

        if (isset($element['showWebsite']) && $element['showWebsite']) {
            $website = get_option('pdf_builder_company_website', 'www.monsite.com');
            $mentions[] = $website;
        }

        if (isset($element['showCustomText']) && $element['showCustomText'] && isset($element['customText'])) {
            $mentions[] = $element['customText'];
        }

        // Rendre le contenu
        if (!empty($mentions)) {
            // LOG: Mentions collectées

            if ($layout === 'vertical') {
                // LOG: Rendu vertical

                // Affichage vertical
                $currentY = $y;
                foreach ($mentions as $mention) {
                    $this->pdf->SetXY($x, $currentY);
                    $this->pdf->Cell($width, $fontSize * $lineHeight * $px_to_mm, $mention, 0, 0, $textAlign === 'center' ? 'C' : ($textAlign === 'right' ? 'R' : 'L'));
                    $currentY += $fontSize * $lineHeight * $px_to_mm;
                }
            } else {
                // Affichage horizontal avec séparateur
                $content = implode($separator, $mentions);

                // LOG: Rendu horizontal

                $this->pdf->MultiCell($width, $fontSize * $lineHeight * $px_to_mm, $content, 0, $textAlign === 'center' ? 'C' : ($textAlign === 'right' ? 'R' : 'L'), false);
            }
        } else {
            // LOG: Aucun contenu

            // Contenu par défaut si rien n'est configuré
            $this->pdf->Cell($width, $fontSize * $lineHeight * $px_to_mm, 'Mentions légales', 0, 0, 'C');
        }
    }

    /**
     * Rendu d'élément dynamic-text
     */
    private function render_dynamic_text_element($element, $px_to_mm) {
        $x = isset($element['x']) ? $element['x'] * $px_to_mm : 10;
        $y = isset($element['y']) ? $element['y'] * $px_to_mm : 10;
        $width = isset($element['width']) ? $element['width'] * $px_to_mm : 80;
        $height = isset($element['height']) ? $element['height'] * $px_to_mm : 20;

        // Récupérer les propriétés de l'élément
        $template = isset($element['template']) ? $element['template'] : 'total_only';
        $customContent = isset($element['customContent']) ? $element['customContent'] : '{{order_total}} €';
        $color = isset($element['color']) ? $element['color'] : '#333333';
        $fontSize = isset($element['fontSize']) ? $element['fontSize'] : 14;
        $fontFamily = isset($element['fontFamily']) ? $this->map_font_family($element['fontFamily']) : 'helvetica';
        $fontWeight = isset($element['fontWeight']) ? $element['fontWeight'] : 'normal';
        $textAlign = isset($element['textAlign']) ? $element['textAlign'] : 'left';
        $lineHeight = isset($element['lineHeight']) ? $element['lineHeight'] : 1.4;

        // Fonction pour obtenir le contenu selon le template
        $content = $this->get_template_content($template, $customContent);

        // Appliquer la couleur du texte
        if ($color && $color !== 'transparent') {
            $textColor = $this->hex_to_rgb($color);
            $this->pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        } else {
            $this->pdf->SetTextColor(51, 51, 51); // Gris foncé par défaut
        }

        // Appliquer la police et taille
        $this->pdf->SetFont($fontFamily, '', $fontSize);

        // Positionner le curseur
        $this->pdf->SetXY($x, $y);

        // Remplacer les variables dynamiques
        $processedContent = $this->replace_dynamic_variables($content);

        // Convertir l'alignement pour TCPDF
        $tcpdfAlign = 'L'; // Left par défaut
        if ($textAlign === 'center') {
            $tcpdfAlign = 'C';
        } elseif ($textAlign === 'right') {
            $tcpdfAlign = 'R';
        }

        // Calculer la hauteur de ligne
        $lineHeightPx = $fontSize * $lineHeight;

        // Utiliser MultiCell pour gérer le texte multi-ligne avec retour à la ligne automatique
        $this->pdf->MultiCell($width, $lineHeightPx * $px_to_mm, $processedContent, 0, $tcpdfAlign, false);
    }

    /**
     * Remplacement des variables dynamiques dans le contenu
     */
    private function replace_dynamic_variables($content) {
        if (!$this->order) {
            // Valeurs par défaut pour l'aperçu quand pas de commande
            return str_replace(
                ['{{order_total}}', '{{order_number}}', '{{customer_name}}', '{{customer_email}}', '{{date}}', '{{order_date}}', '{{due_date}}'],
                ['125.99 €', 'CMD-2025-00123', 'Jean Dupont', 'jean@example.com', date('d/m/Y'), date('d/m/Y', strtotime('-2 days')), date('d/m/Y', strtotime('+30 days'))],
                $content
            );
        }

        // Remplacement avec les vraies données de la commande
        $replacements = [
            '{{order_total}}' => wc_price($this->order->get_total()),
            '{{order_number}}' => $this->order->get_order_number(),
            '{{customer_name}}' => trim($this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name()),
            '{{customer_email}}' => $this->order->get_billing_email(),
            '{{date}}' => date('d/m/Y'),
            '{{order_date}}' => date('d/m/Y', strtotime($this->order->get_date_created())),
            '{{due_date}}' => date('d/m/Y', strtotime('+30 days')),
            '{{order_subtotal}}' => wc_price($this->order->get_subtotal()),
            '{{order_tax}}' => wc_price($this->order->get_total_tax()),
            '{{order_shipping}}' => wc_price($this->order->get_shipping_total()),
            '{{billing_address}}' => $this->order->get_formatted_billing_address(),
            '{{shipping_address}}' => $this->order->get_formatted_shipping_address(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Obtenir le contenu selon le template sélectionné
     */
    private function get_template_content($template, $customContent) {
        $templates = [
            'total_only' => '{{order_total}} €',
            'order_info' => 'Commande {{order_number}} - {{order_date}}',
            'customer_info' => '{{customer_name}} - {{customer_email}}',
            'customer_address' => '{{customer_name}}\n{{billing_address}}',
            'full_header' => 'Facture N° {{order_number}}\nClient: {{customer_name}}\nTotal: {{order_total}} €',
            'invoice_header' => 'FACTURE N° {{order_number}}\nDate: {{date}}\nClient: {{customer_name}}\n{{billing_address}}',
            'order_summary' => 'Sous-total: {{order_subtotal}} €\nFrais de port: {{order_shipping}} €\nTVA: {{order_tax}} €\nTotal: {{order_total}} €',
            'payment_info' => 'Échéance: {{due_date}}\nMontant: {{order_total}} €',
            'payment_terms' => 'Conditions de paiement: 30 jours\nÉchéance: {{due_date}}\nMontant dû: {{order_total}} €',
            'shipping_info' => 'Adresse de livraison:\n{{shipping_address}}',
            'thank_you' => 'Merci pour votre commande !\nNous vous remercions de votre confiance.',
            'legal_notice' => 'TVA non applicable - art. 293 B du CGI\nPaiement à 30 jours fin de mois',
            'bank_details' => 'Coordonnées bancaires:\nIBAN: FR76 1234 5678 9012 3456 7890 123\nBIC: BNPAFRPP',
            'contact_info' => 'Contact: contact@monentreprise.com\nTél: 01 23 45 67 89',
            'order_confirmation' => 'CONFIRMATION DE COMMANDE\nCommande {{order_number}} du {{order_date}}\nStatut: Confirmée',
            'delivery_note' => 'BON DE LIVRAISON\nCommande {{order_number}}\nDestinataire: {{customer_name}}\n{{shipping_address}}',
            'warranty_info' => 'Garantie: 2 ans pièces et main d\'œuvre\nService après-vente: sav@monentreprise.com',
            'return_policy' => 'Droit de rétractation: 14 jours\nRetour sous 30 jours pour défauts',
            'signature_line' => 'Signature du client:\n\n_______________________________\nDate: {{date}}',
            'invoice_footer' => 'Facture générée automatiquement le {{date}}\nConservez cette facture pour vos archives',
            'terms_conditions' => 'Conditions générales de vente disponibles sur notre site\nwww.monentreprise.com/conditions',
            'quality_guarantee' => 'Tous nos produits sont garantis contre les défauts\nService qualité: qualite@monentreprise.com',
            'eco_friendly' => 'Entreprise engagée pour l\'environnement\nEmballages recyclables et biodégradables',
            'follow_up' => 'Suivi de commande: {{order_number}}\nContact: suivi@monentreprise.com',
            'custom' => $customContent ?: '{{order_total}} €'
        ];

        return isset($templates[$template]) ? $templates[$template] : $templates['total_only'];
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
            if (!empty($city_line)) {
                $city_part = implode(' ', $city_line);
                if (!empty($country)) {
                    // Essayer de convertir le code pays en nom
                    $country_name = $this->get_country_name($country);
                    $city_part .= ' - ' . ($country_name ?: $country);
                }
                $address_parts[] = $city_part;
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

        try {
            // Extraction des propriétés avec valeurs par défaut sûres
            $coords = $this->extract_element_coordinates($element, $px_to_mm);
            $x = $coords['x'];
            $y = $coords['y'];
            $width = $coords['width'] ?: 550 * $px_to_mm;
            $height = $coords['height'] ?: 200 * $px_to_mm;

            // Propriétés de style visuel
            $background_color = $element['backgroundColor'] ?? 'transparent';
            $border_width = ($element['borderWidth'] ?? 1) * $px_to_mm;
            $border_color = $element['borderColor'] ?? 'transparent';

            // Propriétés spécifiques au tableau
            $show_headers = $element['showHeaders'] ?? true;
            $show_borders = $element['showBorders'] ?? false;
            $table_style = $element['tableStyle'] ?? 'default';

            // Validation et extraction sécurisée des headers
            $headers = [];
            if (isset($element['headers']) && is_array($element['headers'])) {
                $headers = $element['headers'];
            } elseif (isset($element['header']) && is_array($element['header'])) {
                // Correction: si 'header' au singulier est utilisé au lieu de 'headers'
                $headers = $element['header'];
            } else {
                $headers = ['Produit', 'Qté', 'Prix'];
            }

            $columns = $element['columns'] ?? [
                'image' => true,
                'name' => true,
                'sku' => false,
                'quantity' => true,
                'price' => true,
                'total' => true
            ];

            // Propriétés des totaux - valeurs par défaut cohérentes avec l'interface
            $show_subtotal = $element['showSubtotal'] ?? true;   // Activé par défaut
            $show_shipping = $element['showShipping'] ?? true;  // Activé par défaut
            $show_taxes = $element['showTaxes'] ?? true;        // Activé par défaut
            $show_discount = $element['showDiscount'] ?? false; // Désactivé par défaut
            $show_total = $element['showTotal'] ?? true;        // Activé par défaut

            // Styles de tableau selon le style choisi
            $table_styles = $this->get_table_styles($table_style);

            // Calcul des largeurs de colonnes dynamiques
            $visible_columns = $this->get_visible_columns($columns);
            $col_widths = $this->calculate_column_widths($width, $visible_columns, $columns);

            // Calculer la hauteur totale du tableau pour le fond
            $table_height = $this->calculate_table_height($element, $columns);

            // Position de départ
            $current_y = $y;

            // Fond du tableau si défini (utiliser la hauteur calculée du tableau)
            if ($this->should_render_background($background_color)) {
                $bg_color = $this->parse_color($background_color);
                $this->pdf->SetFillColor($bg_color['r'], $bg_color['g'], $bg_color['b']);
                $this->pdf->Rect($x, $y, $width, $table_height, 'F');
            } else {
            }

            // Bordure du tableau selon le style choisi
            if ($show_borders) {
                // Utiliser les couleurs du style de tableau pour la bordure
                if (is_array($table_styles['header_border'])) {
                    $this->pdf->SetDrawColor($table_styles['header_border']['r'], $table_styles['header_border']['g'], $table_styles['header_border']['b']);
                } else {
                    $header_border_rgb = $this->hex_to_rgb($table_styles['header_border']);
                    $this->pdf->SetDrawColor($header_border_rgb[0], $header_border_rgb[1], $header_border_rgb[2]);
                }
                $this->pdf->SetLineWidth($table_styles['border_width'] * 0.5); // Utiliser l'épaisseur du style
                $this->pdf->Rect($x, $y, $width, $table_height, 'D');
            } elseif ($border_width > 0 && $border_color !== 'transparent') {
                // Fallback vers les propriétés générales de l'élément
                $border_rgb = $this->parse_color($border_color);
                $this->pdf->SetDrawColor($border_rgb['r'], $border_rgb['g'], $border_rgb['b']);
                $this->pdf->SetLineWidth($border_width * 0.1); // Conversion px vers points
                $this->pdf->Rect($x, $y, $width, $table_height, 'D');
            }

            // En-têtes du tableau
            if ($show_headers) {
                // Définir la couleur de trait pour les bordures des en-têtes
                if ($show_borders) {
                    if (is_array($table_styles['header_border'])) {
                        $this->pdf->SetDrawColor($table_styles['header_border']['r'], $table_styles['header_border']['g'], $table_styles['header_border']['b']);
                    } else {
                        $header_border_rgb = $this->hex_to_rgb($table_styles['header_border']);
                        $this->pdf->SetDrawColor($header_border_rgb[0], $header_border_rgb[1], $header_border_rgb[2]);
                    }
                    $this->pdf->SetLineWidth($table_styles['border_width'] * 0.3);
                }

                // Appliquer la couleur de texte des en-têtes
                if (isset($table_styles['headerTextColor'])) {
                    $header_text_rgb = $this->hex_to_rgb($table_styles['headerTextColor']);
                    $this->pdf->SetTextColor($header_text_rgb[0], $header_text_rgb[1], $header_text_rgb[2]);
                }

                $this->pdf->SetXY($x, $current_y);
                $this->pdf->SetFillColor($table_styles['header_bg']['r'], $table_styles['header_bg']['g'], $table_styles['header_bg']['b']);
                
                // Utiliser la taille de police du style pour les en-têtes
                $header_font_size = isset($table_styles['headerFontSize']) ? (int) filter_var($table_styles['headerFontSize'], FILTER_SANITIZE_NUMBER_INT) : 9;
                $header_cell_height = $header_font_size * 1.2; // Hauteur de cellule basée sur la taille de police
                $this->pdf->SetFont('helvetica', 'B', $header_font_size);

                $col_index = 0;
                if ($columns['image']) {
                    $this->pdf->Cell($col_widths[$col_index], $header_cell_height, 'Img', $show_borders ? 1 : 0, 0, 'C', true);
                    $col_index++;
                }
                if ($columns['name']) {
                    $this->pdf->Cell($col_widths[$col_index], $header_cell_height, $headers[0] ?? 'Produit', $show_borders ? 1 : 0, 0, 'L', true);
                    $col_index++;
                }
                if ($columns['sku']) {
                    $this->pdf->Cell($col_widths[$col_index], $header_cell_height, 'SKU', $show_borders ? 1 : 0, 0, 'L', true);
                    $col_index++;
                }
                if ($columns['quantity']) {
                    $this->pdf->Cell($col_widths[$col_index], $header_cell_height, $headers[1] ?? 'Qté', $show_borders ? 1 : 0, 0, 'C', true);
                    $col_index++;
                }
                if ($columns['price']) {
                    $this->pdf->Cell($col_widths[$col_index], $header_cell_height, $headers[2] ?? 'Prix', $show_borders ? 1 : 0, 0, 'R', true);
                    $col_index++;
                }
                if ($columns['total']) {
                    $this->pdf->Cell($col_widths[$col_index], $header_cell_height, 'Total', $show_borders ? 1 : 0, 1, 'R', true);
                }

                $current_y += $header_cell_height;
            }

            // Contenu du tableau
            // Utiliser la taille de police du style pour les données
            $row_font_size = isset($table_styles['rowFontSize']) ? (int) filter_var($table_styles['rowFontSize'], FILTER_SANITIZE_NUMBER_INT) : 8;
            $this->pdf->SetFont('helvetica', '', $row_font_size);

            // Appliquer la couleur de texte des lignes de données
            if (isset($table_styles['rowTextColor'])) {
                $row_text_rgb = $this->hex_to_rgb($table_styles['rowTextColor']);
                $this->pdf->SetTextColor($row_text_rgb[0], $row_text_rgb[1], $row_text_rgb[2]);
            }

            // Définir la couleur de trait pour les bordures des lignes de données (APRÈS la définition du texte)
            if ($show_borders) {
                if (is_array($table_styles['row_border'])) {
                    $this->pdf->SetDrawColor($table_styles['row_border']['r'], $table_styles['row_border']['g'], $table_styles['row_border']['b']);
                } else {
                    $row_border_rgb = $this->hex_to_rgb($table_styles['row_border']);
                    $this->pdf->SetDrawColor($row_border_rgb[0], $row_border_rgb[1], $row_border_rgb[2]);
                }
                $this->pdf->SetLineWidth($table_styles['border_width'] * 0.2);
            }

            if (isset($this->order) && $this->order) {
                // LOG: Utilisation des vraies données de commande
                // Rendre les vrais produits de la commande
                $current_y = $this->render_order_products_with_fees_pdf($x, $current_y, $col_widths, $columns, $show_borders, $element);
            } else {
                // Définir les couleurs de trait pour l'aperçu avant le rendu
                if ($show_borders) {
                    if (is_array($table_styles['row_border'])) {
                        $this->pdf->SetDrawColor($table_styles['row_border']['r'], $table_styles['row_border']['g'], $table_styles['row_border']['b']);
                    } else {
                        $row_border_rgb = $this->hex_to_rgb($table_styles['row_border']);
                        $this->pdf->SetDrawColor($row_border_rgb[0], $row_border_rgb[1], $row_border_rgb[2]);
                    }
                    $this->pdf->SetLineWidth($table_styles['border_width'] * 0.2);
                }
                // Données fictives pour l'aperçu
                $current_y = $this->render_fake_products($x, $current_y, $col_widths, $columns, $show_borders, $table_style, $element);
            }

            // Définir les couleurs de trait pour les totaux
            if ($show_borders) {
                if (is_array($table_styles['row_border'])) {
                    $this->pdf->SetDrawColor($table_styles['row_border']['r'], $table_styles['row_border']['g'], $table_styles['row_border']['b']);
                } else {
                    $row_border_rgb = $this->hex_to_rgb($table_styles['row_border']);
                    $this->pdf->SetDrawColor($row_border_rgb[0], $row_border_rgb[1], $row_border_rgb[2]);
                }
                $this->pdf->SetLineWidth($table_styles['border_width'] * 0.2);
            }

            // Totaux
            $current_y = $this->render_table_totals($x, $current_y, $col_widths, $columns, $show_borders, $element);

        } catch (Exception $e) {
            // Rendu de secours pour l'élément en erreur
            $this->render_error_fallback($element, $px_to_mm, 'Erreur tableau produits: ' . $e->getMessage());
        }
    }

    /**
     * Calcule la hauteur totale du tableau produits
     */
    private function calculate_table_height($element, $columns) {
        // Obtenir les styles pour calculer les hauteurs dynamiques
        $table_styles = $this->get_table_styles($element['tableStyle'] ?? 'default');
        
        $header_font_size = isset($table_styles['headerFontSize']) ? (int) filter_var($table_styles['headerFontSize'], FILTER_SANITIZE_NUMBER_INT) : 9;
        $row_font_size = isset($table_styles['rowFontSize']) ? (int) filter_var($table_styles['rowFontSize'], FILTER_SANITIZE_NUMBER_INT) : 8;
        
        $header_height = ($element['showHeaders'] ?? true) ? $header_font_size * 1.2 : 0;
        $row_height = $row_font_size * 1.2;
        
        // Compter les produits
        $product_count = 0;
        if (isset($this->order) && $this->order) {
            $line_items = $this->order->get_items();
            $product_count = count($line_items);
            // Ajouter les frais s'il y en a
            $fees = $this->order->get_fees();
            if (!empty($fees)) {
                $product_count += count($fees);
            }
        } else {
            // Données fictives pour l'aperçu
            $product_count = 2;
        }
        
        // Hauteur des lignes de produits
        $products_height = $product_count * $row_height;
        
        // Hauteur des totaux
        $totals_height = 0;
        if ($element['showSubtotal'] ?? false) $totals_height += $row_height;
        if ($element['showShipping'] ?? true) $totals_height += $row_height;
        if ($element['showTaxes'] ?? true) $totals_height += $row_height;
        if ($element['showDiscount'] ?? false) $totals_height += $row_height;
        if ($element['showTotal'] ?? false) $totals_height += $row_height + 2; // Total a une hauteur supplémentaire
        
        return $header_height + $products_height + $totals_height;
    }

    /**
     * Obtenir les styles de tableau selon le style choisi
     */
    private function get_table_styles($table_style) {
        $styles = [
            'default' => [
                'header_bg' => ['r' => 248, 'g' => 250, 'b' => 252], // #f8fafc
                'header_border' => ['r' => 226, 'g' => 232, 'b' => 240], // #e2e8f0
                'row_border' => ['r' => 0, 'g' => 0, 'b' => 0], // #000000
                'row_bg' => 'transparent',
                'alt_row_bg' => ['r' => 250, 'g' => 251, 'b' => 252], // #fafbfc
                'headerTextColor' => '#334155',
                'rowTextColor' => '#334155',
                'border_width' => 2.0,
                'headerFontWeight' => '600',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px',
                'shadow' => '0 1px 3px rgba(0, 0, 0, 0.1)',
                'borderRadius' => '4px'
            ],
            'classic' => [
                'header_bg' => ['r' => 30, 'g' => 41, 'b' => 59], // #1e293b
                'header_border' => ['r' => 51, 'g' => 65, 'b' => 85], // #334155
                'row_border' => ['r' => 51, 'g' => 65, 'b' => 85], // #334155
                'row_bg' => 'transparent',
                'alt_row_bg' => ['r' => 255, 'g' => 255, 'b' => 255], // #ffffff
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#1e293b',
                'border_width' => 1.5,
                'headerFontWeight' => '700',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px',
                'shadow' => '0 2px 8px rgba(0, 0, 0, 0.15)',
                'borderRadius' => '0px'
            ],
            'modern' => [
                'header_bg' => ['r' => 59, 'g' => 130, 'b' => 246], // Bleu moderne
                'header_border' => ['r' => 37, 'g' => 99, 'b' => 235], // #2563eb
                'row_border' => ['r' => 226, 'g' => 232, 'b' => 240], // #e2e8f0
                'row_bg' => 'transparent',
                'alt_row_bg' => ['r' => 248, 'g' => 250, 'b' => 252], // #f8fafc
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#334155',
                'border_width' => 1.0,
                'headerFontWeight' => '600',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px',
                'shadow' => '0 1px 4px rgba(59, 130, 246, 0.2)',
                'borderRadius' => '6px'
            ],
            'minimal' => [
                'header_bg' => ['r' => 255, 'g' => 255, 'b' => 255], // #ffffff
                'header_border' => ['r' => 209, 'g' => 213, 'b' => 219], // #d1d5db
                'row_border' => ['r' => 243, 'g' => 244, 'b' => 246], // #f3f4f6
                'alt_row_bg' => ['r' => 255, 'g' => 255, 'b' => 255], // #ffffff
                'headerTextColor' => '#6b7280',
                'rowTextColor' => '#6b7280',
                'border_width' => 0.5,
                'headerFontWeight' => '500',
                'headerFontSize' => '10px',
                'rowFontSize' => '9px',
                'shadow' => 'none',
                'borderRadius' => '0px'
            ],
            'slate_gray' => [
                'header_bg' => ['r' => 55, 'g' => 65, 'b' => 81], // Gris ardoise
                'header_border' => ['r' => 75, 'g' => 85, 'b' => 99],
                'row_border' => ['r' => 243, 'g' => 244, 'b' => 246],
                'alt_row_bg' => ['r' => 249, 'g' => 250, 'b' => 251],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#374151',
                'border_width' => 1.5
            ],
            'coral' => [
                'header_bg' => ['r' => 248, 'g' => 113, 'b' => 113], // Corail
                'header_border' => ['r' => 252, 'g' => 165, 'b' => 165],
                'row_border' => ['r' => 254, 'g' => 226, 'b' => 226],
                'alt_row_bg' => ['r' => 255, 'g' => 245, 'b' => 245],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#dc2626',
                'border_width' => 1.5,
                'headerFontWeight' => '600',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px',
                'shadow' => '0 4px 16px rgba(248, 113, 113, 0.3)',
                'borderRadius' => '6px'
            ],
            'teal' => [
                'header_bg' => ['r' => 20, 'g' => 184, 'b' => 166], // Sarcelle
                'header_border' => ['r' => 13, 'g' => 148, 'b' => 136],
                'row_border' => ['r' => 204, 'g' => 251, 'b' => 241],
                'alt_row_bg' => ['r' => 240, 'g' => 253, 'b' => 250],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#0f766e',
                'border_width' => 1.5
            ],
            'indigo' => [
                'header_bg' => ['r' => 99, 'g' => 102, 'b' => 241], // Indigo
                'header_border' => ['r' => 129, 'g' => 140, 'b' => 248],
                'row_border' => ['r' => 224, 'g' => 231, 'b' => 255],
                'alt_row_bg' => ['r' => 238, 'g' => 242, 'b' => 255],
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#3730a3',
                'border_width' => 1.5
            ],
            'amber' => [
                'header_bg' => ['r' => 245, 'g' => 158, 'b' => 11], // Ambre
                'header_border' => ['r' => 251, 'g' => 191, 'b' => 36],
                'row_border' => ['r' => 254, 'g' => 240, 'b' => 138],
                'alt_row_bg' => ['r' => 255, 'g' => 251, 'b' => 235],
                'headerTextColor' => '#92400e',
                'rowTextColor' => '#92400e',
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
        $column_keys = ['image', 'name', 'sku', 'quantity', 'price', 'total'];

        foreach ($column_keys as $key) {
            if (isset($columns[$key]) && $columns[$key] !== false) {
                $visible[] = $key;
            }
        }

        return $visible;
    }

    /**
     * Calculer les largeurs des colonnes
     */
    private function calculate_column_widths($total_width, $visible_columns, $columns) {
        $widths = [];

        // Largeurs fixes proportionnelles au Canvas/Aperçu (ratio conservé)
        // Canvas: sku=80px, quantity=60px, price=80px, total=80px
        // Ratio sku:quantity = 4:3, price:total = 1:1
        $fixed_widths = [
            'image' => 15,  // Largeur fixe pour l'image
            'sku' => 25,    // Largeur fixe pour le SKU (équivalent 80px)
            'quantity' => 19, // Largeur fixe pour la quantité (équivalent 60px)
            'price' => 25,  // Largeur fixe pour le prix (équivalent 80px)
            'total' => 25   // Largeur fixe pour le total (équivalent 80px)
        ];

        // La colonne 'name' prend tout l'espace restant (comme flex: 1 dans CSS)
        $used_width = 0;
        $has_name_column = false;

        foreach ($visible_columns as $col) {
            if (isset($fixed_widths[$col])) {
                $used_width += $fixed_widths[$col];
            } elseif ($col === 'name') {
                $has_name_column = true;
            }
        }

        // Calculer la largeur de la colonne name (espace restant)
        $name_width = $has_name_column ? max(30, $total_width - $used_width) : 0;

        // Construire le tableau final des largeurs dans l'ordre des colonnes visibles
        foreach ($visible_columns as $col) {
            if (isset($fixed_widths[$col])) {
                $widths[] = $fixed_widths[$col];
            } elseif ($col === 'name') {
                $widths[] = $name_width;
            }
        }

        return $widths;
    }

    /**
     * Rendre les produits réels de la commande WooCommerce
     */
    private function render_order_products_with_fees_pdf($x, $current_y, $col_widths, $columns, $show_borders, $element) {
        if (!$this->order) {
            return $current_y;
        }

        error_log("RENDER_DEBUG: Starting render_order_products_with_fees_pdf");
        error_log("RENDER_DEBUG: columns config: " . json_encode($columns));
        error_log("RENDER_DEBUG: total column enabled: " . ($columns['total'] ? 'YES' : 'NO'));

        $table_styles = $this->get_table_styles($element['tableStyle'] ?? 'default');
        $line_items = $this->order->get_items();
        $fees = $this->order->get_fees();
        // Utiliser la taille de police du style pour calculer la hauteur des lignes
        $row_font_size = isset($table_styles['rowFontSize']) ? (int) filter_var($table_styles['rowFontSize'], FILTER_SANITIZE_NUMBER_INT) : 8;
        $row_height = $row_font_size * 1.2; // Hauteur de ligne basée sur la taille de police
        $alt_row = false;

        error_log("RENDER_DEBUG: Order ID: " . $this->order->get_id());
        error_log("RENDER_DEBUG: Line items count: " . count($line_items));
        error_log("RENDER_DEBUG: Fees count: " . count($fees));
        error_log("RENDER_DEBUG: Order total: " . $this->order->get_total());
        error_log("RENDER_DEBUG: Order subtotal: " . $this->order->get_subtotal());

        // Définir les couleurs de trait pour les bordures des lignes de données
        if ($show_borders) {
            $row_border_color = isset($table_styles['rowBorder']) ? $table_styles['rowBorder'] : '#e2e8f0';
            $row_border_rgb = $this->hex_to_rgb($row_border_color);
            $this->pdf->SetDrawColor($row_border_rgb[0], $row_border_rgb[1], $row_border_rgb[2]);
            $this->pdf->SetLineWidth($table_styles['border_width'] * 0.2);
        }

        // Produits
        if (!empty($line_items)) {
            error_log("RENDER_DEBUG: Processing " . count($line_items) . " line items");
            foreach ($line_items as $item_id => $item) {
                $product = $item->get_product();
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                $price = $item->get_total() / max(1, $quantity);
                $total = $item->get_total();

                // Debug détaillé pour les totaux
                error_log("TOTAL_DEBUG: {$product_name}");
                error_log("  - Quantity: {$quantity}");
                error_log("  - get_total(): " . $item->get_total());
                error_log("  - get_subtotal(): " . $item->get_subtotal());
                error_log("  - get_total_tax(): " . $item->get_total_tax());
                error_log("  - Calculated unit price: {$price}");
                error_log("  - Displayed total: {$total}");

                // Fond alterné selon les propriétés personnalisées ou le style par défaut
                if (!$alt_row && isset($element['evenRowBg']) && $element['evenRowBg'] !== 'transparent') {
                    // Ligne paire : couleur personnalisée pour les lignes paires
                    $bg_rgb = $this->parse_color($element['evenRowBg']);
                    $this->pdf->SetFillColor($bg_rgb['r'], $bg_rgb['g'], $bg_rgb['b']);
                    $this->pdf->Rect($x, $current_y, array_sum($col_widths), $row_height, 'F');
                } elseif ($alt_row && isset($element['oddRowBg']) && $element['oddRowBg'] !== 'transparent') {
                    // Ligne impaire : couleur personnalisée pour les lignes impaires
                    $bg_rgb = $this->parse_color($element['oddRowBg']);
                    $this->pdf->SetFillColor($bg_rgb['r'], $bg_rgb['g'], $bg_rgb['b']);
                    $this->pdf->Rect($x, $current_y, array_sum($col_widths), $row_height, 'F');
                } elseif ($alt_row && isset($table_styles['alt_row_bg'])) {
                    // Fallback : alternance par défaut du style de tableau
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
                    $price_text = number_format($price, 2, ',', ' ') . ' ' . '€';
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $price_text, $show_borders ? 1 : 0, 0, 'R');
                    $col_index++;
                }

                // Total
                if ($columns['total']) {
                    $total_text = number_format($total, 2, ',', ' ') . ' ' . '€';
                    error_log("RENDER_DEBUG: Displaying total for {$product_name}: {$total_text} (raw: {$total})");
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $total_text, $show_borders ? 1 : 0, 1, 'R');
                } else {
                    error_log("RENDER_DEBUG: Total column disabled for {$product_name}");
                }

                $current_y += $row_height;
                $alt_row = !$alt_row;
            }
        }

        // Frais (shipping, taxes, etc.)
        if (!empty($fees)) {
            error_log("RENDER_DEBUG: Processing " . count($fees) . " fees");
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
                if ($columns['price']) {
                    $price_text = number_format($fee_total, 2, ',', ' ') . ' ' . '€';
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $price_text, $show_borders ? 1 : 0, 0, 'R');
                    $col_index++;
                }
                if ($columns['total']) {
                    $fee_text = number_format($fee_total, 2, ',', ' ') . ' ' . '€';
                    error_log("RENDER_DEBUG: Displaying fee total for {$fee_name}: {$fee_text} (raw: {$fee_total})");
                    $this->pdf->Cell($col_widths[$col_index], $row_height, $fee_text, $show_borders ? 1 : 0, 1, 'R');
                } else {
                    error_log("RENDER_DEBUG: Fee total column disabled for {$fee_name}");
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
        // Utiliser la taille de police du style pour calculer la hauteur des lignes
        $row_font_size = isset($table_styles['rowFontSize']) ? (int) filter_var($table_styles['rowFontSize'], FILTER_SANITIZE_NUMBER_INT) : 8;
        $row_height = $row_font_size * 1.2; // Hauteur de ligne basée sur la taille de police

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

        // LOG: Produits d'aperçu préparés
        // Rendre chaque produit d'aperçu
        foreach ($preview_products as $index => $product) {
            // Appliquer la couleur de fond selon la parité des lignes
            $product_bg_color = $product['backgroundColor'] ?? $product['bgColor'] ?? null;
            if ($product_bg_color && $product_bg_color !== 'transparent') {
                $bg_rgb = $this->parse_color($product_bg_color);
                $this->pdf->SetFillColor($bg_rgb['r'], $bg_rgb['g'], $bg_rgb['b']);
                $this->pdf->Rect($x, $current_y, array_sum($col_widths), $row_height, 'F');
            } elseif ($index % 2 === 0 && isset($element['evenRowBg']) && $element['evenRowBg'] !== 'transparent') {
                // Ligne paire : couleur personnalisée pour les lignes paires
                $bg_rgb = $this->parse_color($element['evenRowBg']);
                $this->pdf->SetFillColor($bg_rgb['r'], $bg_rgb['g'], $bg_rgb['b']);
                $this->pdf->Rect($x, $current_y, array_sum($col_widths), $row_height, 'F');
            } elseif ($index % 2 === 1 && isset($element['oddRowBg']) && $element['oddRowBg'] !== 'transparent') {
                // Ligne impaire : couleur personnalisée pour les lignes impaires
                $bg_rgb = $this->parse_color($element['oddRowBg']);
                $this->pdf->SetFillColor($bg_rgb['r'], $bg_rgb['g'], $bg_rgb['b']);
                $this->pdf->Rect($x, $current_y, array_sum($col_widths), $row_height, 'F');
            } elseif ($index % 2 === 1 && isset($table_styles['alt_row_bg'])) {
                // Fallback : alternance par défaut du style de tableau
                $this->pdf->SetFillColor($table_styles['alt_row_bg']['r'], $table_styles['alt_row_bg']['g'], $table_styles['alt_row_bg']['b']);
                $this->pdf->Rect($x, $current_y, array_sum($col_widths), $row_height, 'F');
            }

            // Appliquer la couleur de texte selon la parité des lignes
            $product_text_color = $product['color'] ?? $product['textColor'] ?? null;
            if ($product_text_color) {
                $text_rgb = $this->parse_color($product_text_color);
                $this->pdf->SetTextColor($text_rgb['r'], $text_rgb['g'], $text_rgb['b']);
            } elseif ($index % 2 === 0 && isset($element['evenRowTextColor'])) {
                // Ligne paire : couleur de texte personnalisée
                $text_rgb = $this->parse_color($element['evenRowTextColor']);
                $this->pdf->SetTextColor($text_rgb['r'], $text_rgb['g'], $text_rgb['b']);
            } elseif ($index % 2 === 1 && isset($element['oddRowTextColor'])) {
                // Ligne impaire : couleur de texte personnalisée
                $text_rgb = $this->parse_color($element['oddRowTextColor']);
                $this->pdf->SetTextColor($text_rgb['r'], $text_rgb['g'], $text_rgb['b']);
            } elseif (isset($table_styles['rowTextColor'])) {
                // Fallback : couleur par défaut du style de tableau
                $row_text_rgb = $this->hex_to_rgb($table_styles['rowTextColor']);
                $this->pdf->SetTextColor($row_text_rgb[0], $row_text_rgb[1], $row_text_rgb[2]);
            }

            $this->pdf->SetXY($x, $current_y);
            $col_index = 0;
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
                $price_text = number_format($product['price'], 2, ',', ' ') . ' ' . '€';
                $this->pdf->Cell($col_widths[$col_index], $row_height, $price_text, $show_borders ? 1 : 0, 0, 'R');
                $col_index++;
            }
            if ($columns['total']) {
                $total_text = number_format($product['total'], 2, ',', ' ') . ' ' . '€';
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
        $show_subtotal = $element['showSubtotal'] ?? true;
        $show_shipping = $element['showShipping'] ?? true;
        $show_taxes = $element['showTaxes'] ?? true;
        $show_discount = $element['showDiscount'] ?? false;
        $show_total = $element['showTotal'] ?? true;

        $table_styles = $this->get_table_styles($element['tableStyle'] ?? 'default');
        $row_height = 6;

        // Calculer les totaux
        $subtotal = 0;
        $shipping = 0;
        $taxes = 0;
        $discount = 0;

        if ($this->order) {
            // Calculer le subtotal manuellement en additionnant les totaux des lignes et frais
            $subtotal = 0;
            $line_items = $this->order->get_items();
            foreach ($line_items as $item) {
                $subtotal += $item->get_total();
            }
            // Ajouter les frais au subtotal
            $fees = $this->order->get_fees();
            foreach ($fees as $fee) {
                $subtotal += $fee->get_total();
            }
            error_log("DEBUG_TOTALS: Manual subtotal calculation (with fees): {$subtotal}");
            $shipping = $this->order->get_shipping_total();
            $taxes = $this->order->get_total_tax();
            $discount = $this->order->get_discount_total();
            error_log("DEBUG_TOTALS: Shipping: {$shipping}, Taxes: {$taxes}, Discount: {$discount}");
            error_log("DEBUG_TOTALS: WooCommerce get_subtotal(): " . $this->order->get_subtotal());
            error_log("DEBUG_TOTALS: WooCommerce get_total(): " . $this->order->get_total());
        } else {
            // Valeurs fictives
            $subtotal = 69.97;
            $shipping = 5.00;
            $taxes = 2.25;
            $discount = -5.00;
        }

        $total = $subtotal + $shipping + $taxes - $discount;

        // Déterminer les colonnes pour les totaux (2 dernières colonnes pour plus de place)
        $rightmost_col_index = count($col_widths) - 1;
        $second_rightmost_col_index = max(0, count($col_widths) - 2);

        $total_x = $x;
        for ($i = 0; $i < $second_rightmost_col_index; $i++) {
            $total_x += $col_widths[$i];
        }

        // Largeur totale pour les totaux (2 dernières colonnes)
        $total_width = $col_widths[$second_rightmost_col_index] + $col_widths[$rightmost_col_index];

        // Sous-total
        if ($show_subtotal) {
            $this->pdf->SetXY($total_x, $current_y);
            $this->pdf->SetFont('helvetica', 'B', 8);
            $subtotal_text = 'Sous-total: ' . number_format($subtotal, 2, ',', ' ') . ' ' . '€';
            $this->pdf->Cell($total_width, $row_height + 2, $subtotal_text, $show_borders ? 1 : 0, 1, 'R');
            $current_y += $row_height + 2;
        }

        // Frais de port
        if ($show_shipping && $shipping != 0) {
            $this->pdf->SetXY($total_x, $current_y);
            $shipping_text = 'Port: ' . number_format($shipping, 2, ',', ' ') . ' ' . '€';
            $this->pdf->Cell($total_width, $row_height + 2, $shipping_text, $show_borders ? 1 : 0, 1, 'R');
            $current_y += $row_height + 2;
        }

        // Taxes
        if ($show_taxes && $taxes != 0) {
            $this->pdf->SetXY($total_x, $current_y);
            $taxes_text = 'TVA: ' . number_format($taxes, 2, ',', ' ') . ' ' . '€';
            $this->pdf->Cell($total_width, $row_height + 2, $taxes_text, $show_borders ? 1 : 0, 1, 'R');
            $current_y += $row_height + 2;
        }

        // Remise
        if ($show_discount && $discount != 0) {
            $this->pdf->SetXY($total_x, $current_y);
            $discount_text = 'Remise: ' . ($discount > 0 ? '-' : '') . number_format(abs($discount), 2, ',', ' ') . ' ' . '€';
            $this->pdf->Cell($total_width, $row_height + 2, $discount_text, $show_borders ? 1 : 0, 1, 'R');
            $current_y += $row_height + 2;
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
            $total_text = 'TOTAL: ' . number_format($total, 2, ',', ' ') . ' ' . '€';
            $this->pdf->Cell($total_width, $row_height + 2, $total_text, $show_borders ? 1 : 0, 1, 'R', true);
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
        // Utiliser extract_element_coordinates pour cohérence avec l'éditeur
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $x = $coords['x'];
        $y = $coords['y'];
        $width = $coords['width'];
        $height = $coords['height'];

        // Couleur de fond (comme dans l'éditeur)
        $color = $this->parse_color($element['color'] ?? $element['fillColor'] ?? '#cccccc');
        $this->pdf->SetFillColor($color['r'], $color['g'], $color['b']);

        // Épaisseur (thickness ou height, comme dans l'éditeur)
        $thickness = ($element['thickness'] ?? $element['height'] ?? 2) * $px_to_mm;

        // Dessiner un rectangle rempli pour le divider
        $this->pdf->Rect($x, $y, $width, $thickness, 'F');
    }

    /**
     * Génération d'aperçu PDF simplifié (alternative au système canvas)
     */
    public function generate_simple_preview($order_id, $template_id = null) {

        try {
            // Récupérer la commande WooCommerce
            $this->order = wc_get_order($order_id);
            if (!$this->order) {
                throw new Exception('Commande non trouvée');
            }

            // Si un template_id est fourni, récupérer les données du template
            if ($template_id) {
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';

                $template = $wpdb->get_row(
                    $wpdb->prepare("SELECT id, name, template_data FROM $table_templates WHERE id = %d", $template_id),
                    ARRAY_A
                );

                if (!$template) {
                    throw new Exception('Template non trouvé');
                }


                // Décoder les données JSON du template
                $template_data = json_decode($template['template_data'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Données du template invalides');
                }


                // Générer le PDF avec les données du template
                $elements = isset($template_data['elements']) ? $template_data['elements'] : [];

                $pdf_content = $this->generate($elements, ['title' => 'Aperçu Facture - Commande #' . $order_id]);


                // Vérifier que nous avons du contenu PDF valide
                if (empty($pdf_content) || strlen($pdf_content) < 100) {
                    throw new Exception('Contenu PDF invalide généré');
                }

                // Utiliser le répertoire uploads standard au lieu d'un sous-répertoire
                $upload_dir = wp_upload_dir();
                $cache_dir = $upload_dir['basedir'];

                // Générer un nom de fichier unique avec préfixe
                $filename = 'pdf-builder-preview-order-' . $order_id . '-' . time() . '.pdf';
                $filepath = $cache_dir . '/' . $filename;

                // Sauvegarder le fichier
                if (file_put_contents($filepath, $pdf_content) !== false) {

                    // Essayer plusieurs méthodes pour définir les permissions du fichier
                    if (function_exists('wp_chmod')) {
                        wp_chmod($filepath, 0644);
                    } elseif (chmod($filepath, 0644)) {
                    } else {
                    }
                } else {
                    throw new Exception('Impossible de sauvegarder le PDF');
                }

                // Retourner l'URL d'accès
                $url = $upload_dir['baseurl'] . '/' . $filename;
                return $url;

            } else {
                // Générer le contenu simplifié (fallback)

                // Initialiser TCPDF pour le contenu simple
                $this->initialize_tcpdf();

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

                // Utiliser le répertoire uploads standard au lieu d'un sous-répertoire
                $upload_dir = wp_upload_dir();
                $cache_dir = $upload_dir['basedir'];

                // Générer un nom de fichier unique avec préfixe
                $filename = 'pdf-builder-preview-order-' . $order_id . '-' . time() . '.pdf';
                $filepath = $cache_dir . '/' . $filename;

                // Sauvegarder le fichier
                if (file_put_contents($filepath, $pdf_content) !== false) {

                    // Essayer plusieurs méthodes pour définir les permissions du fichier
                    if (function_exists('wp_chmod')) {
                        wp_chmod($filepath, 0644);
                    } elseif (chmod($filepath, 0644)) {
                    } else {
                    }
                } else {
                }

                // Vérifier les permissions actuelles
                $file_perms = substr(sprintf('%o', fileperms($filepath)), -4);

                // Retourner l'URL d'accès
                $url = $upload_dir['baseurl'] . '/' . $filename;
                return $url;
            }

        } catch (Exception $e) {
            return new WP_Error('pdf_generation_error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Rend les éléments en HTML avec le même style que PreviewModal.jsx
     */
    private function render_html_preview($elements, $order_id) {
        // Dimensions A4 en pixels (72 DPI)
        $canvas_width = 595;
        $canvas_height = 842;
        $zoom = 1;

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu PDF - Commande #' . $order_id . '</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .preview-container {
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 0 auto;
            max-width: ' . ($canvas_width + 40) . 'px;
            position: relative;
        }
        .preview-header {
            background: #2563eb;
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .canvas {
            width: ' . $canvas_width . 'px;
            height: ' . $canvas_height . 'px;
            background: white;
            position: relative;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .element {
            position: absolute;
            box-sizing: border-box;
        }
        .text-element {
            font-size: 16px;
            color: #000000;
            font-weight: normal;
            font-style: normal;
            text-decoration: none;
            text-align: left;
            line-height: 1.2;
            white-space: pre-wrap;
            overflow: hidden;
            padding: 4px;
        }
        .rectangle-element {
            background-color: transparent;
            border: none;
            border-radius: 0;
        }
        .image-element {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0;
        }
        .line-element {
            left: 0;
            width: 100%;
            height: 1px;
            background-color: #000000;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            👁️ Aperçu PDF - Commande #' . $order_id . '
        </div>
        <div class="canvas">';

        // Rendre chaque élément
        foreach ($elements as $index => $element) {
            if (!isset($element['x'], $element['y'], $element['width'], $element['height'])) {
                continue;
            }

            $element_padding = $element['padding'] ?? 0;
            $z_index = $element['zIndex'] ?? ($index + 1);

            $style = 'position: absolute; ' .
                    'left: ' . (($element['x'] + $element_padding) * $zoom) . 'px; ' .
                    'top: ' . (($element['y'] + $element_padding) * $zoom) . 'px; ' .
                    'width: ' . (max(1, ($element['width'] - ($element_padding * 2))) * $zoom) . 'px; ' .
                    'height: ' . (max(1, ($element['height'] - ($element_padding * 2))) * $zoom) . 'px; ' .
                    'z-index: ' . $z_index . ';';

            // Styles spéciaux pour certains types
            if ($element['type'] === 'line') {
                $style = 'position: absolute; left: 0; width: 100%; z-index: ' . $z_index . ';';
            }

            $html .= '<div class="element ' . $element['type'] . '-element" style="' . $style . '">';

            switch ($element['type']) {
                case 'text':
                    $text_style = 'width: 100%; height: 100%; ' .
                                 'font-size: ' . (($element['fontSize'] ?? 16) * $zoom) . 'px; ' .
                                 'color: ' . ($element['color'] ?? '#000000') . '; ' .
                                 'font-weight: ' . (($element['fontWeight'] === 'bold') ? 'bold' : 'normal') . '; ' .
                                 'font-style: ' . (($element['fontStyle'] === 'italic') ? 'italic' : 'normal') . '; ' .
                                 'text-decoration: ' . ($element['textDecoration'] ?? 'none') . '; ' .
                                 'text-align: ' . ($element['textAlign'] ?? 'left') . '; ' .
                                 'line-height: ' . ($element['lineHeight'] ?? '1.2') . '; ' .
                                 'white-space: pre-wrap; ' .
                                 'overflow: hidden; ' .
                                 'padding: ' . (4 * $zoom) . 'px; ' .
                                 'box-sizing: border-box;';

                    $html .= '<div style="' . $text_style . '">' .
                             htmlspecialchars($element['content'] ?? $element['text'] ?? 'Texte') .
                             '</div>';
                    break;

                case 'rectangle':
                    $rect_style = 'width: 100%; height: 100%; ' .
                                 'background-color: ' . ($element['fillColor'] ?? 'transparent') . '; ' .
                                 'border: ' . ($element['borderWidth'] ? (($element['borderWidth'] * $zoom) . 'px ' . ($element['borderStyle'] ?? 'solid') . ' ' . ($element['borderColor'] ?? '#000000')) : 'none') . '; ' .
                                 'border-radius: ' . (($element['borderRadius'] ?? 0) * $zoom) . 'px;';

                    $html .= '<div style="' . $rect_style . '"></div>';
                    break;

                case 'image':
                    $img_style = 'width: 100%; height: 100%; ' .
                                'object-fit: ' . ($element['objectFit'] ?? 'cover') . '; ' .
                                'border-radius: ' . (($element['borderRadius'] ?? 0) * $zoom) . 'px;';

                    $html .= '<img src="' . htmlspecialchars($element['src'] ?? $element['imageUrl'] ?? '') . '" ' .
                             'alt="' . htmlspecialchars($element['alt'] ?? 'Image') . '" ' .
                             'style="' . $img_style . '" ' .
                             'onerror="this.style.display=\'none\'">';
                    break;

                case 'line':
                    $line_style = 'width: 100%; height: ' . (($element['borderWidth'] ?? 1) * $zoom) . 'px; ' .
                                 'background-color: ' . ($element['borderColor'] ?? '#000000') . ';';

                    $html .= '<div style="' . $line_style . '"></div>';
                    break;

                case 'product_table':
                    // Rendu simplifié du tableau produits
                    $html .= $this->render_product_table_html($element, $zoom);
                    break;

                default:
                    $html .= '<div style="width: 100%; height: 100%; background: #f0f0f0; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #666;">' .
                             htmlspecialchars($element['type'] ?? 'unknown') .
                             '</div>';
                    break;
            }

            $html .= '</div>';
        }

        $html .= '        </div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Rend un tableau de produits en HTML
     */
    private function render_product_table_html($element, $zoom) {
        $table_style = $element['tableStyle'] ?? 'default';
        $show_headers = $element['showHeaders'] ?? true;
        $show_borders = $element['showBorders'] ?? false;
        $columns = $element['columns'] ?? [
            'image' => true,
            'name' => true,
            'sku' => false,
            'quantity' => true,
            'price' => true,
            'total' => true
        ];

        // Obtenir les styles du tableau
        $table_styles = $this->get_table_styles($table_style);

        // Styles CSS pour le tableau
        $table_css = sprintf(
            'width: 100%%; border-collapse: collapse; font-size: %dpx; font-family: %s; %s',
            $table_styles['rowFontSize'] * $zoom,
            'Arial, sans-serif',
            isset($table_styles['shadow']) ? 'box-shadow: ' . $table_styles['shadow'] . ';' : ''
        );

        // Style de bordure du tableau
        if ($show_borders) {
            $table_css .= sprintf(' border: %dpx solid %s;', $table_styles['border_width'], $table_styles['rowBorder']);
        }

        $html = '<table style="' . $table_css . '">';

        // En-têtes du tableau
        if ($show_headers) {
            $header_bg = isset($table_styles['gradient']) ? $table_styles['gradient'] : $table_styles['headerBg'];
            $header_style = sprintf(
                'background: %s; color: %s; font-weight: %s; font-size: %dpx; padding: %dpx; border: %s;',
                $header_bg,
                $table_styles['headerTextColor'],
                $table_styles['headerFontWeight'],
                $table_styles['headerFontSize'] * $zoom,
                8 * $zoom,
                $show_borders ? $table_styles['border_width'] . 'px solid ' . $table_styles['headerBorder'] : 'none'
            );

            $html .= '<thead><tr>';

            $col_index = 0;
            if ($columns['image']) {
                $html .= '<th style="' . $header_style . ' text-align: center;">Img</th>';
                $col_index++;
            }
            if ($columns['name']) {
                $headers = $element['headers'] ?? ['Produit', 'Qté', 'Prix'];
                $html .= '<th style="' . $header_style . ' text-align: left;">' . htmlspecialchars($headers[0] ?? 'Produit') . '</th>';
                $col_index++;
            }
            if ($columns['sku']) {
                $html .= '<th style="' . $header_style . ' text-align: left;">SKU</th>';
                $col_index++;
            }
            if ($columns['quantity']) {
                $html .= '<th style="' . $header_style . ' text-align: center;">' . htmlspecialchars($headers[1] ?? 'Qté') . '</th>';
                $col_index++;
            }
            if ($columns['price']) {
                $html .= '<th style="' . $header_style . ' text-align: right;">' . htmlspecialchars($headers[2] ?? 'Prix') . '</th>';
                $col_index++;
            }
            if ($columns['total']) {
                $html .= '<th style="' . $header_style . ' text-align: right;">Total</th>';
            }

            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';

        // Données fictives pour l'aperçu
        $products = [
            ['name' => 'Produit exemple 1', 'sku' => 'PROD-001', 'qty' => 2, 'price' => 25.00, 'total' => 50.00],
            ['name' => 'Produit exemple 2', 'sku' => 'PROD-002', 'qty' => 1, 'price' => 15.50, 'total' => 15.50],
        ];

        foreach ($products as $index => $product) {
            // Alternance des couleurs de ligne selon le style
            $row_bg = ($index % 2 === 0) ? $table_styles['rowBg'] : $table_styles['altRowBg'];
            $row_text_color = $table_styles['rowTextColor'];

            $row_style = sprintf(
                'background-color: %s; color: %s; font-size: %dpx; padding: %dpx; border: %s;',
                $row_bg,
                $row_text_color,
                $table_styles['rowFontSize'] * $zoom,
                8 * $zoom,
                $show_borders ? $table_styles['border_width'] . 'px solid ' . $table_styles['rowBorder'] : 'none'
            );

            $html .= '<tr>';

            $col_index = 0;
            if ($columns['image']) {
                $html .= '<td style="' . $row_style . ' text-align: center;"><div style="width: 20px; height: 20px; background: #ddd; display: inline-block;"></div></td>';
                $col_index++;
            }
            if ($columns['name']) {
                $html .= '<td style="' . $row_style . ' text-align: left;">' . htmlspecialchars($product['name']) . '</td>';
                $col_index++;
            }
            if ($columns['sku']) {
                $html .= '<td style="' . $row_style . ' text-align: left;">' . htmlspecialchars($product['sku']) . '</td>';
                $col_index++;
            }
            if ($columns['quantity']) {
                $html .= '<td style="' . $row_style . ' text-align: center;">' . $product['qty'] . '</td>';
                $col_index++;
            }
            if ($columns['price']) {
                $html .= '<td style="' . $row_style . ' text-align: right;">' . number_format($product['price'], 2) . ' €</td>';
                $col_index++;
            }
            if ($columns['total']) {
                $html .= '<td style="' . $row_style . ' text-align: right; font-weight: bold;">' . number_format($product['total'], 2) . ' €</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
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
        // Verifier la securite
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_nonce')) {
            ob_end_clean();
            wp_send_json_error(array(
                'message' => 'Sécurité: Nonce invalide',
                'debug_logs' => [],
                'elements_count' => 0,
                'pdf_size' => 0
            ));
            return;
        }

        // Récupérer et valider les éléments avec la nouvelle validation robuste
        $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();
        $elements = $elements_manager->validate_and_clean_json_elements(
            $_POST['elements'] ?? '[]',
            'pdf_generation_frontend'
        );

        // Gérer les erreurs de validation JSON
        if (is_wp_error($elements)) {
            ob_end_clean();
            wp_send_json_error(array(
                'message' => 'Erreur de validation des données: ' . $elements->get_error_message(),
                'debug_logs' => [],
                'elements_count' => 0,
                'pdf_size' => 0
            ));
            return;
        }

        // Generer le PDF avec le nouveau generateur
        $generator = new PDF_Builder_Pro_Generator();
        $pdf_content = $generator->generate($elements, ['is_preview' => true]);

        if (empty($pdf_content)) {
            ob_end_clean();
            wp_send_json_error(array(
                'message' => 'Erreur lors de la generation du PDF',
                'debug_logs' => [],
                'elements_count' => count($elements),
                'pdf_size' => 0
            ));
            return;
        }

        // Vider le buffer avant d'envoyer la reponse
        ob_end_clean();

        // Retourner le PDF en base64
        wp_send_json_success(array(
            'pdf' => base64_encode($pdf_content),
            'filename' => 'pdf-builder-pro-document.pdf',
            'performance' => $generator->get_performance_metrics(),
            'errors' => $generator->get_errors(),
            'debug_logs' => [],
            'elements_count' => count($elements),
            'pdf_size' => strlen($pdf_content)
        ));

    } catch (Exception $e) {
        ob_end_clean();
        wp_send_json_error('Erreur lors de la generation du PDF: ' . $e->getMessage());
    } catch (Throwable $t) {
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
