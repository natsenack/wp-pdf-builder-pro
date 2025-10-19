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
        error_log('[PDF Generator] Order set: ' . ($order ? 'Order ID: ' . $order->get_id() : 'null'));
    }

    /**
     * Generateur principal - Interface unifiee SANS TCPDF
     */
    public function generate($elements, $options = []) {
        if (isset($options['is_preview']) && $options['is_preview']) {
            $this->set_preview_mode(true);
        }

        error_log('[PDF Generator] Generate called with ' . count($elements) . ' elements, order: ' . ($this->order ? $this->order->get_id() : 'null'));

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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .pdf-container { 
            position: relative;
            width: 595px; 
            height: 842px; 
            background: white; 
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .canvas-element { position: absolute; overflow: hidden; }
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

        error_log('[PDF Generator] Rendering element type: ' . $type . ', content: ' . ($element['content'] ?? $element['text'] ?? 'no content'));

        // Donner des dimensions par défaut si manquantes
        if (empty($coords['width']) || $coords['width'] <= 0) {
            $coords['width'] = 100; // Largeur par défaut
        }
        if (empty($coords['height']) || $coords['height'] <= 0) {
            $coords['height'] = 50; // Hauteur par défaut
        }

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
        $style .= 'box-sizing: border-box; ';
        
        if (isset($element['properties'])) {
            $style .= $this->extract_element_styles($element['properties']);
        }
        
        // Ajouter les styles directement de l'élément s'ils existent
        if (isset($element['color'])) {
            $style .= 'color: ' . esc_attr($element['color']) . '; ';
        }
        if (isset($element['backgroundColor'])) {
            $style .= 'background-color: ' . esc_attr($element['backgroundColor']) . '; ';
        }
        if (isset($element['fontSize'])) {
            $style .= 'font-size: ' . intval($element['fontSize']) . 'px; ';
        }
        if (isset($element['fontWeight'])) {
            $style .= 'font-weight: ' . esc_attr($element['fontWeight']) . '; ';
        }
        if (isset($element['textAlign'])) {
            $style .= 'text-align: ' . esc_attr($element['textAlign']) . '; ';
        }
        if (isset($element['border'])) {
            $style .= 'border: ' . esc_attr($element['border']) . '; ';
        }
        if (isset($element['borderColor'])) {
            $style .= 'border-color: ' . esc_attr($element['borderColor']) . '; ';
        }
        if (isset($element['borderWidth'])) {
            $style .= 'border-width: ' . intval($element['borderWidth']) . 'px; ';
        }

        switch ($type) {
            case 'text':
            case 'dynamic-text':
            case 'multiline_text':
                $content = $element['content'] ?? $element['text'] ?? $element['customContent'] ?? '';
                // Pour dynamic-text, remplacer les variables si un ordre est défini
                if ($type === 'dynamic-text' && $this->order) {
                    $original_content = $content;
                    $content = $this->replace_order_variables($content, $this->order);
                    error_log('[PDF Generator] Dynamic-text replacement: "' . $original_content . '" -> "' . $content . '"');
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; white-space: pre-wrap; word-wrap: break-word;'>" . wp_kses_post($content) . "</div>";

            case 'image':
            case 'company_logo':
                $src = $element['imageUrl'] ?? $element['src'] ?? '';
                if (!$src && $type === 'company_logo') {
                    $custom_logo_id = get_theme_mod('custom_logo');
                    if ($custom_logo_id) {
                        $src = wp_get_attachment_image_url($custom_logo_id, 'full');
                    }
                }
                if ($src) {
                    return "<img class='canvas-element' src='" . esc_url($src) . "' style='" . esc_attr($style) . "; object-fit: contain;' />";
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;'>Logo</div>";

            case 'rectangle':
                return "<div class='canvas-element' style='" . esc_attr($style) . "; border: 1px solid #ccc;'></div>";

            case 'divider':
            case 'line':
                return "<div class='canvas-element' style='" . esc_attr($style) . "; background-color: #cccccc;'></div>";

            case 'product_table':
                return "<div class='canvas-element' style='" . esc_attr($style) . "; border: 1px solid #ddd; overflow: auto;'>Tableau produits</div>";

            case 'customer_info':
                return "<div class='canvas-element' style='" . esc_attr($style) . "; font-size: 12px; line-height: 1.4;'>Informations client</div>";

            default:
                return "<div class='canvas-element' style='" . esc_attr($style) . "'>[$type]</div>";
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

    /**
     * Remplace les variables de commande et compagnie dans le contenu
     */
    private function replace_order_variables($content, $order = null) {
        // Variables de compagnie (toujours disponibles)
        $company_replacements = array(
            '{{company_name}}' => get_bloginfo('name'),
            '{{company_email}}' => get_option('pdf_builder_company_email', ''),
            '{{company_phone}}' => get_option('pdf_builder_company_phone', ''),
            '{{company_siret}}' => get_option('pdf_builder_company_siret', ''),
            '{{company_vat}}' => get_option('pdf_builder_company_vat', ''),
            '{{company_address}}' => get_option('pdf_builder_company_address', ''),
            '{{company_info}}' => $this->format_complete_company_info(),
        );

        // Variables de commande (seulement si ordre existe)
        $order_replacements = array();
        if ($order) {
            $billing_address = $order->get_formatted_billing_address();
            $shipping_address = $order->get_formatted_shipping_address();

            $order_replacements = array(
                '{{order_id}}' => $order->get_id(),
                '{{order_number}}' => $order->get_order_number(),
            '{{order_date}}' => $order->get_date_created() ? $order->get_date_created()->format('d/m/Y') : date('d/m/Y'),
            '{{order_date_time}}' => $order->get_date_created() ? $order->get_date_created()->format('d/m/Y H:i:s') : date('d/m/Y H:i:s'),
                '{{customer_name}}' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
                '{{customer_first_name}}' => $order->get_billing_first_name(),
                '{{customer_last_name}}' => $order->get_billing_last_name(),
                '{{customer_email}}' => $order->get_billing_email(),
                '{{customer_phone}}' => $order->get_billing_phone(),
                '{{billing_company}}' => $order->get_billing_company(),
                '{{billing_address_1}}' => $order->get_billing_address_1(),
                '{{billing_address_2}}' => $order->get_billing_address_2(),
                '{{billing_city}}' => $order->get_billing_city(),
                '{{billing_state}}' => $order->get_billing_state(),
                '{{billing_postcode}}' => $order->get_billing_postcode(),
                '{{billing_country}}' => $order->get_billing_country(),
                '{{billing_address}}' => $billing_address ?: 'Adresse de facturation non disponible',
                '{{shipping_first_name}}' => $order->get_shipping_first_name(),
                '{{shipping_last_name}}' => $order->get_shipping_last_name(),
                '{{shipping_company}}' => $order->get_shipping_company(),
                '{{shipping_address_1}}' => $order->get_shipping_address_1(),
                '{{shipping_address_2}}' => $order->get_shipping_address_2(),
                '{{shipping_city}}' => $order->get_shipping_city(),
                '{{shipping_state}}' => $order->get_shipping_state(),
                '{{shipping_postcode}}' => $order->get_shipping_postcode(),
                '{{shipping_country}}' => $order->get_shipping_country(),
                '{{shipping_address}}' => $shipping_address ?: 'Adresse de livraison non disponible',
                '{{total}}' => function_exists('wc_price') ? wc_price($order->get_total()) : $order->get_total(),
                '{{order_total}}' => function_exists('wc_price') ? wc_price($order->get_total()) : $order->get_total(),
                '{{subtotal}}' => function_exists('wc_price') ? wc_price($order->get_subtotal()) : $order->get_subtotal(),
                '{{order_subtotal}}' => function_exists('wc_price') ? wc_price($order->get_subtotal()) : $order->get_subtotal(),
                '{{tax}}' => function_exists('wc_price') ? wc_price($order->get_total_tax()) : $order->get_total_tax(),
                '{{order_tax}}' => function_exists('wc_price') ? wc_price($order->get_total_tax()) : $order->get_total_tax(),
                '{{shipping_total}}' => function_exists('wc_price') ? wc_price($order->get_shipping_total()) : $order->get_shipping_total(),
                '{{order_shipping}}' => function_exists('wc_price') ? wc_price($order->get_shipping_total()) : $order->get_shipping_total(),
                '{{discount_total}}' => function_exists('wc_price') ? wc_price($order->get_discount_total()) : $order->get_discount_total(),
                '{{payment_method}}' => $order->get_payment_method_title(),
                '{{order_status}}' => function_exists('wc_get_order_status_name') ? wc_get_order_status_name($order->get_status()) : $order->get_status(),
                '{{currency}}' => $order->get_currency(),
                '{{date}}' => date('d/m/Y'),
                '{{due_date}}' => date('d/m/Y', strtotime('+30 days')),
            );
        }

        // Fusionner les remplacements
        $all_replacements = array_merge($order_replacements, $company_replacements);

        // Créer les arrays pour les différents formats de variables
        $double_brace_replacements = $all_replacements;

        // Variables avec crochets [variable]
        $bracket_replacements = array();
        foreach ($all_replacements as $key => $value) {
            $bracket_key = str_replace(['{{', '}}'], ['[', ']'], $key);
            $bracket_replacements[$bracket_key] = $value;
        }

        // Variables avec accolades simples {variable}
        $single_brace_replacements = array();
        foreach ($all_replacements as $key => $value) {
            $single_key = str_replace(['{{', '}}'], ['{', '}'], $key);
            $single_brace_replacements[$single_key] = $value;
        }

        // Appliquer les remplacements dans l'ordre : simples, doubles, crochets
        $content = str_replace(array_keys($single_brace_replacements), array_values($single_brace_replacements), $content);
        $content = str_replace(array_keys($double_brace_replacements), array_values($double_brace_replacements), $content);
        $content = str_replace(array_keys($bracket_replacements), array_values($bracket_replacements), $content);

        return $content;
    }

    /**
     * Formate les informations complètes de la compagnie
     */
    private function format_complete_company_info() {
        $company_info = get_option('pdf_builder_company_info', '');
        if (!empty($company_info)) {
            return $company_info;
        }

        $company_parts = [];
        $company_name = get_bloginfo('name');
        if (!empty($company_name)) {
            $company_parts[] = $company_name;
        }

        $address_parts = [];
        $company_address = get_option('pdf_builder_company_address', '');
        if (!empty($company_address)) {
            $address_parts[] = $company_address;
        }

        $company_city = get_option('pdf_builder_company_city', '');
        if (!empty($company_city)) {
            $address_parts[] = $company_city;
        }

        $company_postcode = get_option('pdf_builder_company_postcode', '');
        if (!empty($company_postcode)) {
            $address_parts[] = $company_postcode;
        }

        if (!empty($address_parts)) {
            $company_parts = array_merge($company_parts, $address_parts);
        }

        $company_email = get_option('pdf_builder_company_email', '');
        if (!empty($company_email)) {
            $company_parts[] = $company_email;
        }

        $company_phone = get_option('pdf_builder_company_phone', '');
        if (!empty($company_phone)) {
            $company_parts[] = $company_phone;
        }

        $company_siret = get_option('pdf_builder_company_siret', '');
        if (!empty($company_siret)) {
            $company_parts[] = 'SIRET: ' . $company_siret;
        }

        $company_vat = get_option('pdf_builder_company_vat', '');
        if (!empty($company_vat)) {
            $company_parts[] = 'TVA: ' . $company_vat;
        }

        return implode("\n", $company_parts);
    }
}

// Alias pour compatibilité
class_alias('PDF_Builder_Pro_Generator', 'PDF_Generator');
