<?php
/**
 * PDF Builder Pro - Generateur PDF Ultra-Performant SANS TCPDF
 * Version: 3.0 - Migration complète vers approche moderne
 * Auteur: PDF Builder Pro Team
 * Description: Systeme plug-and-play pour generation PDF haute performance sans TCPDF
 */

// Sécurité WordPress - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PDF_Builder_Pro_Generator {

    private $html_content = '';
    private $cache = [];
    private $errors = [];
    private $performance_metrics = [];
    private $order = null;
    private $is_preview = false;

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
    }

    /**
     * Extrait les coordonnées d'un élément avec support des deux formats
     */
    private function extract_element_coordinates($element, $px_to_mm = 1) {
        $element_x = isset($element['position']['x']) ? $element['position']['x'] : (isset($element['x']) ? $element['x'] : 0);
        $element_y = isset($element['position']['y']) ? $element['position']['y'] : (isset($element['y']) ? $element['y'] : 0);
        $element_width = isset($element['size']['width']) ? $element['size']['width'] : (isset($element['width']) ? $element['width'] : 0);
        $element_height = isset($element['size']['height']) ? $element['size']['height'] : (isset($element['height']) ? $element['height'] : 0);

        return [
            'x' => $element_x * $px_to_mm,
            'y' => $element_y * $px_to_mm,
            'width' => $element_width * $px_to_mm,
            'height' => $element_height * $px_to_mm
        ];
    }

    /**
     * Définit l'ordre pour la génération du PDF
     */
    public function set_order($order) {
        $this->order = $order;
    }

    /**
     * Generateur principal - Interface unifiee SANS TCPDF
     */
    public function generate($elements, $options = []) {
        if (isset($options['is_preview']) && $options['is_preview']) {
            $this->set_preview_mode(true);
        }

        try {
            $this->reset();
            $this->validate_elements($elements);

            // Générer le HTML au lieu du PDF
            $this->html_content = $this->generate_html_from_elements($elements);

            // Pour l'instant, retourner le HTML directement
            // TODO: Convertir HTML vers PDF avec une vraie bibliothèque
            return $this->html_content;

        } catch (Exception $e) {
            error_log('[PDF Builder] PDF_Builder_Pro_Generator exception: ' . $e->getMessage());
            $this->log_error('Generation PDF echouee: ' . $e->getMessage());
            return $this->generate_fallback_html($elements);
        }
    }

    /**
     * Générer du HTML à partir des éléments Canvas
     */
    private function generate_html_from_elements($elements) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Preview</title>
    <style>
        body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
        .pdf-container { width: 210mm; min-height: 297mm; background: white; margin: 0 auto; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .canvas-element { position: absolute; }
    </style>
</head>
<body>
    <div class="pdf-container">';

        foreach ($elements as $element) {
            $html .= $this->render_element_to_html($element);
        }

        $html .= '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Rendre un élément individuel en HTML
     */
    private function render_element_to_html($element) {
        $type = $element['type'] ?? 'text';
        $coords = $this->extract_element_coordinates($element, 1); // Garder en pixels pour HTML

        // CONTRAINTE: S'assurer que l'élément reste dans les limites A4 (595x842 pixels)
        $canvas_width = 595;
        $canvas_height = 842;
        $coords['x'] = max(0, min($canvas_width - $coords['width'], $coords['x']));
        $coords['y'] = max(0, min($canvas_height - $coords['height'], $coords['y']));

        $style = sprintf(
            'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
            $coords['x'], $coords['y'], $coords['width'], $coords['height']
        );

        // Appliquer les styles CSS des propriétés
        if (isset($element['properties'])) {
            $style .= $this->extract_element_styles($element['properties']);
        }

        switch ($type) {
            case 'text':
                $content = $element['content'] ?? '';
                return "<div class='canvas-element' style='$style'>$content</div>";

            case 'image':
                $src = $element['src'] ?? '';
                return "<img class='canvas-element' src='$src' style='$style' />";

            case 'rectangle':
                return "<div class='canvas-element' style='$style border: 1px solid black;'></div>";

            default:
                return "<div class='canvas-element' style='$style'>Element type: $type</div>";
        }
    }

    /**
     * Extraire les styles CSS des propriétés de l'élément
     */
    private function extract_element_styles($properties) {
        $styles = [];

        // Couleur de fond
        if (isset($properties['backgroundColor'])) {
            $styles[] = 'background-color: ' . $properties['backgroundColor'];
        }

        // Couleur du texte
        if (isset($properties['color'])) {
            $styles[] = 'color: ' . $properties['color'];
        }

        // Taille de police
        if (isset($properties['fontSize'])) {
            $styles[] = 'font-size: ' . $properties['fontSize'] . 'px';
        }

        // Famille de police
        if (isset($properties['fontFamily'])) {
            $styles[] = 'font-family: ' . $properties['fontFamily'];
        }

        // Alignement du texte
        if (isset($properties['textAlign'])) {
            $styles[] = 'text-align: ' . $properties['textAlign'];
        }

        // Décoration du texte (souligné, barré, etc.)
        if (isset($properties['textDecoration'])) {
            $styles[] = 'text-decoration: ' . $properties['textDecoration'];
        }

        // Hauteur de ligne
        if (isset($properties['lineHeight'])) {
            $styles[] = 'line-height: ' . $properties['lineHeight'];
        }

        // Style de bordure
        if (isset($properties['borderStyle'])) {
            $width = $properties['borderWidth'] ?? 1;
            $color = $properties['borderColor'] ?? '#000000';
            $styles[] = "border: {$width}px {$properties['borderStyle']} $color";
        }

        // Ombre
        if (isset($properties['shadow'])) {
            $styles[] = 'box-shadow: ' . $properties['shadow'];
        }

        // Rotation
        if (isset($properties['rotation'])) {
            $styles[] = 'transform: rotate(' . $properties['rotation'] . 'deg)';
        }

        // Échelle
        if (isset($properties['scale'])) {
            $styles[] = 'transform: scale(' . $properties['scale'] . ')';
        }

        return implode('; ', $styles);
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
        $this->html_content = '';
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
     * Génération de fallback HTML
     */
    private function generate_fallback_html($elements) {
        return '<!DOCTYPE html>
<html>
<head><title>PDF Error</title></head>
<body>
    <h1>Erreur de génération PDF</h1>
    <p>Une erreur s\'est produite lors de la génération du PDF.</p>
    <pre>' . implode("\n", $this->errors) . '</pre>
</body>
</html>';
    }

    /**
     * Log d'erreur
     */
    private function log_error($message) {
        $this->errors[] = $message;
        error_log('[PDF Builder] ' . $message);
    }
}

// Alias pour compatibilité
class_alias('PDF_Builder_Pro_Generator', 'PDF_Generator');
