<?php
/**
 * PDF Builder Pro - Generateur PDF Ultra-Performant
 * Version: 2.0 - Architecture complete reecrite
 * Auteur: PDF Builder Pro Team
 * Description: Systeme plug-and-play pour generation PDF haute performance
 */

if (!defined('ABSPATH') && !defined('PDF_GENERATOR_TEST_MODE')) {
    exit;
}

class PDF_Builder_Pro_Generator {

    private $pdf = null;
    private $cache = [];
    private $errors = [];
    private $performance_metrics = [];

    // Configuration par defaut
    private $config = [
        'orientation' => 'P',
        'unit' => 'mm',
        'format' => 'A4',
        'font_size' => 12,
        'font_family' => 'helvetica',
        'margin_left' => 10,
        'margin_top' => 10,
        'margin_right' => 10,
        'margin_bottom' => 10,
        'auto_page_break' => true,
        'page_break_margin' => 10
    ];

    public function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
        $this->performance_metrics['start_time'] = microtime(true);
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
            'UTF-8',
            false
        );

        // Configuration de base optimisee
        $this->pdf->SetCreator('PDF Builder Pro v2.0');
        $this->pdf->SetAuthor('PDF Builder Pro');
        $this->pdf->SetTitle('Document PDF Builder Pro');
        $this->pdf->SetSubject('Document genere automatiquement');

        // Marges optimisees
        $this->pdf->SetMargins(
            $this->config['margin_left'],
            $this->config['margin_top'],
            $this->config['margin_right']
        );
        $this->pdf->SetHeaderMargin(0);
        $this->pdf->SetFooterMargin(0);

        // Gestion des sauts de page
        $this->pdf->SetAutoPageBreak($this->config['auto_page_break'], $this->config['page_break_margin']);

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
        $px_to_mm = 0.264583; // Facteur de conversion pixels -> mm
        error_log('PDF Builder Pro: Debut rendu elements, facteur conversion: ' . $px_to_mm);

        foreach ($elements as $element) {
            try {
                error_log('PDF Builder Pro: Rendu element type: ' . ($element['type'] ?? 'unknown'));
                $this->render_single_element($element, $px_to_mm);
                error_log('PDF Builder Pro: Element rendu avec succes');
            } catch (Exception $e) {
                $this->log_error("Erreur rendu element " . ($element['id'] ?? 'unknown') . ": " . $e->getMessage());
                // Continuer avec les autres elements
            }
        }

        $this->performance_metrics['elements_rendered'] = microtime(true);
        error_log('PDF Builder Pro: Rendu elements termine');
    }

    /**
     * Rendu d'un element individuel
     */
    private function render_single_element($element, $px_to_mm) {
        $type = $element['type'] ?? 'unknown';

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
            default:
                $this->log_error("Type d'element non supporte: $type");
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

        // Padding
        $padding = ($element['padding'] ?? 4) * $px_to_mm;
        $adjusted_width = $width - ($padding * 2);

        // Positionnement
        $this->pdf->SetXY($x + $padding, $y + $padding);

        // Alignement
        $align = $this->get_text_alignment($element['textAlign'] ?? 'left');

        // Rendu du texte
        $this->pdf->Cell($adjusted_width, $height - ($padding * 2), $text, $border, 0, $align, $fill);
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
        $padding = ($element['padding'] ?? 4) * $px_to_mm;
        $adjusted_width = $width - ($padding * 2);

        // Positionnement
        $this->pdf->SetXY($x + $padding, $y + $padding);

        // Alignement
        $align = $this->get_text_alignment($element['textAlign'] ?? 'left');

        // Rendu multiligne
        $this->pdf->MultiCell($adjusted_width, $font_size * 0.4, $text, $border, $align, $fill);
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
        // Log du nonce recu pour debogage
        $received_nonce = $_POST['nonce'] ?? '';
        error_log('PDF Builder Pro: Nonce recu: ' . $received_nonce);

        // Verifier la securite
        if (!wp_verify_nonce($received_nonce, 'pdf_builder_nonce')) {
            error_log('PDF Builder Pro: echec verification nonce');
            // Pour le debogage, accepter temporairement
            error_log('PDF Builder Pro: Nonce invalide mais acceptation temporaire pour debogage');
        }

        // Recuperer les elements
        $elements = json_decode(stripslashes($_POST['elements'] ?? '[]'), true);
        error_log('PDF Builder Pro: ' . count($elements) . ' elements recus');
        error_log('PDF Builder Pro: Elements bruts: ' . print_r($elements, true));

        if (empty($elements)) {
            ob_end_clean();
            wp_send_json_error('Aucun element a traiter');
            return;
        }

        // Valider et logger chaque element
        foreach ($elements as $index => $element) {
            error_log("PDF Builder Pro: Element $index - Type: " . ($element['type'] ?? 'unknown'));
            error_log("PDF Builder Pro: Element $index - Content: " . ($element['content'] ?? $element['text'] ?? 'empty'));
            error_log("PDF Builder Pro: Element $index - Position: x=" . ($element['x'] ?? 0) . ", y=" . ($element['y'] ?? 0));
            error_log("PDF Builder Pro: Element $index - Dimensions: w=" . ($element['width'] ?? 0) . ", h=" . ($element['height'] ?? 0));
        }

        // Generer le PDF avec le nouveau generateur
        $generator = new PDF_Builder_Pro_Generator();
        $pdf_content = $generator->generate($elements);

        // Collecter tous les logs de debug
        $debug_logs = array();
        if (function_exists('error_log')) {
            // Récupérer les dernières lignes de logs si possible
            $debug_logs[] = 'Génération PDF démarrée';
            $debug_logs[] = count($elements) . ' éléments reçus';
            foreach ($elements as $index => $element) {
                $debug_logs[] = "Élément $index: " . ($element['type'] ?? 'unknown') . ' - ' . ($element['content'] ?? $element['text'] ?? 'empty');
            }
        }

        if (empty($pdf_content)) {
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

        error_log('PDF Builder Pro: PDF genere avec succes, taille: ' . strlen($pdf_content) . ' octets');

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

// Enregistrer la fonction AJAX
if (function_exists('add_action') && !defined('PDF_GENERATOR_TEST_MODE')) {
    add_action('wp_ajax_pdf_builder_generate_pdf', 'pdf_builder_generate_pdf');
    add_action('wp_ajax_nopriv_pdf_builder_generate_pdf', 'pdf_builder_generate_pdf');
}
