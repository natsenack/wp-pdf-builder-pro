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

    private function render_element_to_html($element) {
        $type = $element['type'] ?? 'text';
        $coords = $this->extract_element_coordinates($element, 1); // Garder en pixels pour HTML

        error_log('[PDF Generator] Rendering element type: ' . $type . ', id: ' . ($element['id'] ?? 'no-id'));

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
        
        // Utiliser la fonction centralisée pour extraire tous les styles
        if (isset($element['properties'])) {
            $additional_styles = $this->extract_element_styles($element['properties']);
            if (!empty($additional_styles)) {
                $style .= $additional_styles . '; ';
            }
        }
        
        // Propriétés directes de l'élément (fallback si pas dans properties)
        $direct_properties = [
            'color', 'backgroundColor', 'fontSize', 'fontWeight', 'fontStyle', 'fontFamily',
            'textAlign', 'textDecoration', 'lineHeight', 'border', 'borderColor', 'borderWidth',
            'borderStyle', 'borderRadius', 'opacity', 'rotation', 'scale', 'shadow',
            'shadowColor', 'shadowOffsetX', 'shadowOffsetY', 'shadowBlur', 'visible',
            'brightness', 'contrast', 'saturate', 'blur', 'hueRotate', 'sepia', 'grayscale', 'invert'
        ];
        
        foreach ($direct_properties as $prop) {
            if (isset($element[$prop]) && !isset($element['properties'][$prop])) {
                $css_prop = $this->convert_property_to_css($prop, $element[$prop], $element);
                if ($css_prop) {
                    $style .= $css_prop . '; ';
                }
            }
        }

        try {
            return $this->render_element_content($element, $style, $type);
        } catch (Exception $e) {
            error_log('[PDF Generator] Error rendering element ' . $type . ': ' . $e->getMessage());
            return "<div class='canvas-element' style='" . esc_attr($style) . "; background: #ffe6e6; border: 1px solid #ff0000; display: flex; align-items: center; justify-content: center; color: #ff0000;'>Erreur: {$type}</div>";
        }
    }

    private function render_element_content($element, $style, $type) {
        // LOG COMPLET DE DIAGNOSTIC POUR TOUS LES ÉLÉMENTS
        error_log('[PDF Generator] === RENDERING ELEMENT: ' . $type . ' ===');
        error_log('[PDF Generator] Element full config: ' . json_encode($element));
        error_log('[PDF Generator] Element has ' . count($element) . ' total properties');
        error_log('[PDF Generator] Base style applied: ' . $style);

        switch ($type) {
            case 'text':
            case 'dynamic-text':
            case 'multiline_text':
                // LOG DES PROPRIÉTÉS TEXT
                error_log('[PDF Generator] TEXT ELEMENT - Type: ' . $type);
                error_log('[PDF Generator] TEXT ELEMENT - Content sources: customContent=' . ($element['customContent'] ?? 'NOT_SET') . ', content=' . ($element['content'] ?? 'NOT_SET') . ', text=' . ($element['text'] ?? 'NOT_SET'));

                // Pour dynamic-text, prioriser customContent, sinon content/text
                if ($type === 'dynamic-text') {
                    $content = $element['customContent'] ?? $element['content'] ?? $element['text'] ?? '';
                } else {
                    $content = $element['content'] ?? $element['text'] ?? '';
                }

                error_log('[PDF Generator] TEXT ELEMENT - Final content: "' . $content . '"');

                // Pour dynamic-text, remplacer les variables si un ordre est défini
                if ($type === 'dynamic-text' && $this->order) {
                    $original_content = $content;
                    $content = $this->replace_order_variables($content, $this->order);
                    error_log('[PDF Generator] DYNAMIC-TEXT - Variable replacement: "' . $original_content . '" -> "' . $content . '"');
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; white-space: pre-wrap; word-wrap: break-word;'>" . wp_kses_post($content) . "</div>";

            case 'image':
            case 'company_logo':
                // LOG DES PROPRIÉTÉS IMAGE
                error_log('[PDF Generator] IMAGE ELEMENT - Type: ' . $type);
                error_log('[PDF Generator] IMAGE ELEMENT - Sources: imageUrl=' . ($element['imageUrl'] ?? 'NOT_SET') . ', src=' . ($element['src'] ?? 'NOT_SET'));

                $src = $element['imageUrl'] ?? $element['src'] ?? '';
                if (!$src && $type === 'company_logo') {
                    $custom_logo_id = get_theme_mod('custom_logo');
                    $src = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : '';
                    error_log('[PDF Generator] COMPANY LOGO - Theme logo ID: ' . ($custom_logo_id ?: 'NONE') . ', URL: ' . ($src ?: 'NONE'));
                }

                error_log('[PDF Generator] IMAGE ELEMENT - Final src: ' . ($src ?: 'EMPTY'));

                if ($src) {
                    return "<img class='canvas-element' src='" . esc_url($src) . "' style='" . esc_attr($style) . "; object-fit: contain;' />";
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;'>Logo</div>";

            case 'rectangle':
                // LOG DES PROPRIÉTÉS RECTANGLE
                error_log('[PDF Generator] RECTANGLE ELEMENT - All properties extracted via CSS');
                return "<div class='canvas-element' style='" . esc_attr($style) . "; border: 1px solid #ccc;'></div>";

            case 'divider':
            case 'line':
                // LOG DES PROPRIÉTÉS LINE
                error_log('[PDF Generator] LINE ELEMENT - lineColor: ' . ($element['lineColor'] ?? 'DEFAULT(#64748b)') . ', lineWidth: ' . ($element['lineWidth'] ?? 'DEFAULT(2)'));

                $line_color = $element['lineColor'] ?? '#64748b';
                $line_width = $element['lineWidth'] ?? 2;
                $style .= "border-bottom: {$line_width}px solid {$line_color}; height: {$line_width}px;";

                error_log('[PDF Generator] LINE ELEMENT - Applied style: border-bottom: ' . $line_width . 'px solid ' . $line_color);

                return "<div class='canvas-element' style='" . esc_attr($style) . ";'></div>";

            case 'product_table':
                // LOGS DÉTAILLÉS POUR DIAGNOSTIC
                error_log('[PDF Generator] === PRODUCT_TABLE RENDERING START ===');
                error_log('[PDF Generator] Element ID: ' . ($element['id'] ?? 'unknown'));
                error_log('[PDF Generator] Element properties count: ' . count($element));
                error_log('[PDF Generator] Element properties: ' . json_encode($element));

                $table_html = '';
                if ($this->order) {
                    // NOUVELLE APPROCHE : Générer d'abord avec les données fictives du canvas, puis remplacer par les vraies données
                    $table_html = $this->generate_table_html_from_canvas_template($element);

                    error_log('[PDF Generator] Table HTML generated from canvas template, length: ' . strlen($table_html));
                    $show_headers = $element['showHeaders'] ?? true;
                    $show_borders = $element['showBorders'] ?? true;
                    $headers = $element['headers'] ?? ['Produit', 'Qté', 'Prix', 'Total'];
                    $columns = $element['columns'] ?? ['image' => false, 'name' => true, 'sku' => false, 'quantity' => true, 'price' => true, 'total' => true];
                    $table_style = $element['tableStyle'] ?? 'classic';
                    $even_row_bg = $element['evenRowBg'] ?? '#ffffff';
                    $odd_row_bg = $element['oddRowBg'] ?? '#ebebeb';
                    $odd_row_text_color = $element['oddRowTextColor'] ?? '#666666';

                    // Appliquer les styles prédéfinis selon tableStyle
                    $table_styles = $this->get_table_styles($table_style);
                    $even_row_bg = $element['evenRowBg'] ?? $table_styles['even_row_bg'];
                    $odd_row_bg = $element['oddRowBg'] ?? $table_styles['odd_row_bg'];
                    $odd_row_text_color = $element['oddRowTextColor'] ?? $table_styles['odd_row_text_color'];
                    $header_bg = $element['headerBg'] ?? $table_styles['header_bg'];
                    $header_text_color = $element['headerTextColor'] ?? $table_styles['header_text_color'];
                    $border_color = $element['borderColor'] ?? $table_styles['border_color'];
                    $name_color = $element['nameColor'] ?? $table_styles['name_color'];
                    $quantity_color = $element['quantityColor'] ?? $table_styles['quantity_color'];
                    $price_color = $element['priceColor'] ?? $table_styles['price_color'];
                    $total_color = $element['totalColor'] ?? $table_styles['total_color'];

                    // Styles spécifiques pour les colonnes (récupérés depuis l'élément)
                    $name_style = $element['nameStyle'] ?? 'font-weight: 500;';
                    $quantity_style = $element['quantityStyle'] ?? 'text-align: center;';
                    $price_style = $element['priceStyle'] ?? 'text-align: right;';
                    $total_style = $element['totalStyle'] ?? 'text-align: right; font-weight: bold;';

                    // LOG DÉTAILLÉ DE CHAQUE PROPRIÉTÉ RÉCUPÉRÉE
                    error_log('[PDF Generator] === TABLE PROPERTIES FROM ELEMENT ===');
                    error_log('[PDF Generator] evenRowBg: ' . ($element['evenRowBg'] ?? 'NOT_SET -> using: ' . $even_row_bg));
                    error_log('[PDF Generator] oddRowBg: ' . ($element['oddRowBg'] ?? 'NOT_SET -> using: ' . $odd_row_bg));
                    error_log('[PDF Generator] oddRowTextColor: ' . ($element['oddRowTextColor'] ?? 'NOT_SET -> using: ' . $odd_row_text_color));
                    error_log('[PDF Generator] headerBg: ' . ($element['headerBg'] ?? 'NOT_SET -> using: ' . $header_bg));
                    error_log('[PDF Generator] headerTextColor: ' . ($element['headerTextColor'] ?? 'NOT_SET -> using: ' . $header_text_color));
                    error_log('[PDF Generator] borderColor: ' . ($element['borderColor'] ?? 'NOT_SET -> using: ' . $border_color));
                    error_log('[PDF Generator] nameColor: ' . ($element['nameColor'] ?? 'NOT_SET -> using: ' . $name_color));
                    error_log('[PDF Generator] quantityColor: ' . ($element['quantityColor'] ?? 'NOT_SET -> using: ' . $quantity_color));
                    error_log('[PDF Generator] priceColor: ' . ($element['priceColor'] ?? 'NOT_SET -> using: ' . $price_color));
                    error_log('[PDF Generator] totalColor: ' . ($element['totalColor'] ?? 'NOT_SET -> using: ' . $total_color));
                    error_log('[PDF Generator] nameStyle: ' . ($element['nameStyle'] ?? 'NOT_SET -> using: ' . $name_style));
                    error_log('[PDF Generator] quantityStyle: ' . ($element['quantityStyle'] ?? 'NOT_SET -> using: ' . $quantity_style));
                    error_log('[PDF Generator] priceStyle: ' . ($element['priceStyle'] ?? 'NOT_SET -> using: ' . $price_style));
                    error_log('[PDF Generator] totalStyle: ' . ($element['totalStyle'] ?? 'NOT_SET -> using: ' . $total_style));
                    error_log('[PDF Generator] === END TABLE PROPERTIES ===');

                    error_log('[PDF Generator] Table style applied: ' . $table_style);
                    error_log('[PDF Generator] Table style colors: header_bg=' . $header_bg . ', border=' . $border_color);
                    error_log('[PDF Generator] Column colors - name: ' . $name_color . ', quantity: ' . $quantity_color . ', price: ' . $price_color . ', total: ' . $total_color);
                    error_log('[PDF Generator] Column styles - name: ' . $name_style . ', quantity: ' . $quantity_style . ', price: ' . $price_style . ', total: ' . $total_style);
                    error_log('[PDF Generator] Table config - headers: ' . json_encode($headers));
                    error_log('[PDF Generator] Table config - columns: ' . json_encode($columns));
                    error_log('[PDF Generator] Table config - table_style: ' . $table_style);
                    error_log('[PDF Generator] Table config - colors: even_bg=' . $even_row_bg . ', odd_bg=' . $odd_row_bg . ', odd_text=' . $odd_row_text_color);

                    // Appliquer les propriétés CSS générales au tableau
                    $table_css = 'width: 100%;';

                    // Couleur de fond générale du tableau
                    if (isset($element['backgroundColor']) && $element['backgroundColor'] !== 'transparent') {
                        $table_css .= ' background-color: ' . esc_attr($element['backgroundColor']) . ';';
                        error_log('[PDF Generator] Table background color: ' . $element['backgroundColor']);
                    }

                    // Couleur du texte générale
                    if (isset($element['color'])) {
                        $table_css .= ' color: ' . esc_attr($element['color']) . ';';
                        error_log('[PDF Generator] Table text color: ' . $element['color']);
                    }

                    // Taille de police (remplace la valeur par défaut)
                    if (isset($element['fontSize'])) {
                        $table_css .= ' font-size: ' . intval($element['fontSize']) . 'px;';
                        error_log('[PDF Generator] Table font size: ' . $element['fontSize']);
                    } else {
                        $table_css .= ' font-size: 12px;'; // Valeur par défaut
                    }

                    // Famille de police
                    if (isset($element['fontFamily'])) {
                        $table_css .= ' font-family: ' . esc_attr($element['fontFamily']) . ';';
                        error_log('[PDF Generator] Table font family: ' . $element['fontFamily']);
                    }

                    // Poids de la police
                    if (isset($element['fontWeight'])) {
                        $table_css .= ' font-weight: ' . esc_attr($element['fontWeight']) . ';';
                        error_log('[PDF Generator] Table font weight: ' . $element['fontWeight']);
                    }

                    // Style de la police
                    if (isset($element['fontStyle'])) {
                        $table_css .= ' font-style: ' . esc_attr($element['fontStyle']) . ';';
                        error_log('[PDF Generator] Table font style: ' . $element['fontStyle']);
                    }

                    // Bordures générales - ajouter une bordure par défaut si aucune n'est spécifiée
                    $border_width = $element['borderWidth'] ?? 1; // Bordure de 1px par défaut
                    $border_style = $element['borderStyle'] ?? 'solid';
                    $border_color = $element['borderColor'] ?? '#cccccc'; // Gris clair par défaut
                    if ($border_width > 0) {
                        $table_css .= " border: {$border_width}px {$border_style} {$border_color};";
                        error_log('[PDF Generator] Table border: ' . $border_width . 'px ' . $border_style . ' ' . $border_color);
                    }

                    // Rayon des bordures
                    $border_radius = $element['borderRadius'] ?? 0;
                    if ($border_radius > 0) {
                        $table_css .= " border-radius: {$border_radius}px;";
                        error_log('[PDF Generator] Table border radius: ' . $border_radius);
                    }

                    // Ombres
                    if (isset($element['shadow']) && $element['shadow']) {
                        $shadow_color = $element['shadowColor'] ?? '#000000';
                        $shadow_offset_x = $element['shadowOffsetX'] ?? 2;
                        $shadow_offset_y = $element['shadowOffsetY'] ?? 2;
                        $shadow_blur = $element['shadowBlur'] ?? 1;
                        $table_css .= " box-shadow: {$shadow_offset_x}px {$shadow_offset_y}px {$shadow_blur}px {$shadow_color};";
                        error_log('[PDF Generator] Table shadow: ' . $shadow_offset_x . 'px ' . $shadow_offset_y . 'px ' . $shadow_blur . 'px ' . $shadow_color);
                    }

                    // Ajouter le border-collapse pour les bordures de cellules
                    if ($show_borders) {
                        $table_css .= ' border-collapse: collapse;';
                    }

                    // Couleurs spécifiques pour les colonnes (utiliser les propriétés variables ou valeurs par défaut)
                    // NOTE: Les couleurs sont maintenant définies plus haut avec priorité sur les propriétés de l'élément

                    // Style des bordures pour les cellules
                    $cell_border_style = $show_borders ? "border: 1px solid {$border_color};" : '';

                    error_log('[PDF Generator] Table CSS: ' . $table_css);
                    error_log('[PDF Generator] Cell border style: ' . $cell_border_style);

                    $table_html .= "<table style='{$table_css}'>";

                    error_log('[PDF Generator] Final table CSS: ' . $table_css);
                    error_log('[PDF Generator] Cell border style: ' . $cell_border_style);

                    // Headers
                    if ($show_headers) {
                        error_log('[PDF Generator] Rendering table headers');
                        $table_html .= "<thead><tr style='background-color: {$header_bg}; color: {$header_text_color};'>";
                        foreach ($headers as $header) {
                            $table_html .= "<th style='padding: 8px; text-align: left; {$cell_border_style} font-weight: bold;'>{$header}</th>";
                        }
                        $table_html .= "</tr></thead>";
                    }
                    $table_html .= "<tbody>";
                    $row_count = 0;
                    $items = $this->order->get_items();
                    foreach ($items as $item) {
                        $row_count++;
                        $is_even = ($row_count % 2 === 0);
                        $bg_color = $is_even ? $even_row_bg : $odd_row_bg;
                        $text_color = $is_even ? ($even_row_text_color ?? 'inherit') : $odd_row_text_color;

                        error_log('[PDF Generator] Row ' . $row_count . ' - is_even: ' . ($is_even ? 'true' : 'false') . ', bg: ' . $bg_color . ', text: ' . $text_color);
                        error_log('[PDF Generator] Colors config - even_bg: ' . $even_row_bg . ', odd_bg: ' . $odd_row_bg . ', odd_text: ' . $odd_row_text_color);

                        $table_html .= "<tr style='background-color: {$bg_color}; color: {$text_color};'>";

                        // Product Name
                        if ($columns['name']) {
                            $product_name = $item->get_name();
                            error_log('[PDF Generator] Product name: ' . $product_name);
                            $table_html .= "<td style='padding: 8px; {$cell_border_style} {$name_style} color: {$name_color};'>{$product_name}</td>";
                        }

                        // Quantity
                        if ($columns['quantity']) {
                            $quantity = $item->get_quantity();
                            error_log('[PDF Generator] Quantity: ' . $quantity);
                            $table_html .= "<td style='padding: 8px; {$cell_border_style} {$quantity_style} color: {$quantity_color};'>{$quantity}</td>";
                        }

                        // Price
                        if ($columns['price']) {
                            $product = $item->get_product();
                            $price = $product ? $product->get_price() : 0;
                            $price_formatted = function_exists('wc_price') ? wc_price($price) : $price;
                            error_log('[PDF Generator] Price: ' . $price . ' -> ' . $price_formatted);
                            $table_html .= "<td style='padding: 8px; {$cell_border_style} {$price_style} color: {$price_color};'>{$price_formatted}</td>";
                        }

                        // Total
                        if ($columns['total']) {
                            $total = function_exists('wc_price') ? wc_price($item->get_total()) : $item->get_total();
                            error_log('[PDF Generator] Total: ' . $total);
                            $table_html .= "<td style='padding: 8px; {$cell_border_style} {$total_style} color: {$total_color};'>{$total}</td>";
                        }

                        $table_html .= "</tr>";
                    }
                    
                    // Subtotal, Shipping, Taxes, Total
                    $show_subtotal = $element['showSubtotal'] ?? true;
                    $show_shipping = $element['showShipping'] ?? true;
                    $show_taxes = $element['showTaxes'] ?? true; // Afficher les taxes par défaut
                    $show_discount = $element['showDiscount'] ?? true;
                    $show_total = $element['showTotal'] ?? true;

                    error_log('[PDF Generator] Summary display options:');
                    error_log('[PDF Generator] - show_subtotal: ' . ($show_subtotal ? 'true' : 'false'));
                    error_log('[PDF Generator] - show_shipping: ' . ($show_shipping ? 'true' : 'false'));
                    error_log('[PDF Generator] - show_taxes: ' . ($show_taxes ? 'true' : 'false'));
                    error_log('[PDF Generator] - show_discount: ' . ($show_discount ? 'true' : 'false'));
                    error_log('[PDF Generator] - show_total: ' . ($show_total ? 'true' : 'false'));
                    
                    if ($show_subtotal || $show_shipping || $show_taxes || $show_discount || $show_total) {
                        $table_html .= "<tr style='background-color: #f9f9f9; font-weight: bold;'><td colspan='" . count(array_filter($columns)) . "' style='padding: 8px; {$cell_border_style} text-align: right;'>";
                        
                        $summary_lines = [];
                        
                        if ($show_subtotal) {
                            $subtotal = function_exists('wc_price') ? wc_price($this->order->get_subtotal()) : $this->order->get_subtotal();
                            $summary_lines[] = "Sous-total: {$subtotal}";
                            error_log('[PDF Generator] Subtotal: ' . $subtotal);
                        }
                        
                        if ($show_shipping && $this->order->get_shipping_total() > 0) {
                            $shipping = function_exists('wc_price') ? wc_price($this->order->get_shipping_total()) : $this->order->get_shipping_total();
                            $summary_lines[] = "Livraison: {$shipping}";
                            error_log('[PDF Generator] Shipping: ' . $shipping);
                        }
                        
                        if ($show_taxes && $this->order->get_total_tax() > 0) {
                            $tax = function_exists('wc_price') ? wc_price($this->order->get_total_tax()) : $this->order->get_total();
                            $summary_lines[] = "TVA: {$tax}";
                            error_log('[PDF Generator] Tax: ' . $tax);
                        }
                        
                        if ($show_discount && $this->order->get_discount_total() > 0) {
                            $discount = function_exists('wc_price') ? wc_price($this->order->get_discount_total()) : $this->order->get_discount_total();
                            $summary_lines[] = "Remise: -{$discount}";
                            error_log('[PDF Generator] Discount: ' . $discount);
                        }
                        
                        if ($show_total) {
                            $total = function_exists('wc_price') ? wc_price($this->order->get_total()) : $this->order->get_total();
                            $summary_lines[] = "TOTAL: {$total}";
                            error_log('[PDF Generator] Order total: ' . $total);
                        }
                        
                        $table_html .= implode('<br>', $summary_lines);
                        $table_html .= "</td></tr>";
                    }
                    
                    $table_html .= "</tbody></table>";
                }

                error_log('[PDF Generator] === PRODUCT_TABLE RENDERING END ===');
                error_log('[PDF Generator] Generated HTML length: ' . strlen($table_html));
                error_log('[PDF Generator] Generated HTML preview: ' . substr($table_html, 0, 200) . '...');

                return "<div class='canvas-element' style='" . esc_attr($style) . "; overflow: auto;'>" . $table_html . "</div>";

            case 'customer_info':
                // LOG DES PROPRIÉTÉS CUSTOMER INFO
                error_log('[PDF Generator] CUSTOMER INFO - fields: ' . json_encode($element['fields'] ?? 'DEFAULT'));
                error_log('[PDF Generator] CUSTOMER INFO - showLabels: ' . ($element['showLabels'] ?? 'DEFAULT(true)'));
                error_log('[PDF Generator] CUSTOMER INFO - labelStyle: ' . ($element['labelStyle'] ?? 'DEFAULT(normal)'));

                $customer_info = '';
                if ($this->order) {
                    $fields = $element['fields'] ?? ['name', 'phone', 'address', 'email'];
                    $show_labels = $element['showLabels'] ?? true;
                    $label_style = $element['labelStyle'] ?? 'normal';

                    error_log('[PDF Generator] CUSTOMER INFO - Processing fields: ' . implode(', ', $fields));

                    $customer_parts = [];

                    if (in_array('name', $fields)) {
                        $name = trim($this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name());
                        if ($name) {
                            $label = $show_labels ? ($label_style === 'bold' ? '<strong>Nom:</strong> ' : 'Nom: ') : '';
                            $customer_parts[] = $label . $name;
                        }
                    }

                    if (in_array('phone', $fields)) {
                        $phone = $this->order->get_billing_phone();
                        if ($phone) {
                            $label = $show_labels ? ($label_style === 'bold' ? '<strong>Tél:</strong> ' : 'Tél: ') : '';
                            $customer_parts[] = $label . $phone;
                        }
                    }

                    if (in_array('email', $fields)) {
                        $email = $this->order->get_billing_email();
                        if ($email) {
                            $label = $show_labels ? ($label_style === 'bold' ? '<strong>Email:</strong> ' : 'Email: ') : '';
                            $customer_parts[] = $label . $email;
                        }
                    }

                    if (in_array('address', $fields)) {
                        $address = $this->order->get_formatted_billing_address();
                        if ($address) {
                            $label = $show_labels ? ($label_style === 'bold' ? '<strong>Adresse:</strong><br>' : 'Adresse:<br>') : '';
                            $customer_parts[] = $label . nl2br($address);
                        }
                    }

                    $customer_info = implode('<br>', $customer_parts);
                    error_log('[PDF Generator] CUSTOMER INFO - Generated content: ' . str_replace('<br>', ' | ', $customer_info));
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; font-size: 12px; line-height: 1.4;'>" . wp_kses_post($customer_info ?: 'Informations client') . "</div>";

            case 'company_info':
                // LOG DES PROPRIÉTÉS COMPANY INFO
                error_log('[PDF Generator] COMPANY INFO - fields: ' . json_encode($element['fields'] ?? 'DEFAULT'));

                $company_info = '';
                if ($this->order) {
                    $fields = $element['fields'] ?? ['name', 'address', 'phone', 'rcs'];

                    error_log('[PDF Generator] COMPANY INFO - Processing fields: ' . implode(', ', $fields));

                    $company_parts = [];

                    if (in_array('name', $fields)) {
                        $name = get_bloginfo('name');
                        if ($name) {
                            $company_parts[] = '<strong>' . esc_html($name) . '</strong>';
                        }
                    }

                    if (in_array('address', $fields)) {
                        $address = get_option('pdf_builder_company_address', '');
                        if ($address) {
                            $company_parts[] = nl2br(esc_html($address));
                        }
                    }

                    if (in_array('phone', $fields)) {
                        $phone = get_option('pdf_builder_company_phone', '');
                        if ($phone) {
                            $company_parts[] = 'Tél: ' . esc_html($phone);
                        }
                    }

                    if (in_array('rcs', $fields)) {
                        $rcs = get_option('pdf_builder_company_siret', '');
                        if ($rcs) {
                            $company_parts[] = 'SIRET: ' . esc_html($rcs);
                        }
                    }

                    $company_info = implode('<br>', $company_parts);
                    error_log('[PDF Generator] COMPANY INFO - Generated content: ' . str_replace('<br>', ' | ', $company_info));
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; font-size: 12px; line-height: 1.4;'>" . wp_kses_post($company_info ?: '[company_info]') . "</div>";

            case 'order_number':
                // LOG DES PROPRIÉTÉS ORDER NUMBER
                error_log('[PDF Generator] ORDER NUMBER - format: ' . ($element['format'] ?? 'DEFAULT'));
                error_log('[PDF Generator] ORDER NUMBER - showLabel: ' . ($element['showLabel'] ?? 'DEFAULT(true)'));
                error_log('[PDF Generator] ORDER NUMBER - labelText: ' . ($element['labelText'] ?? 'DEFAULT'));

                $order_number = '';
                if ($this->order) {
                    $format = $element['format'] ?? 'Commande #{order_number}';
                    $show_label = $element['showLabel'] ?? true;
                    $label_text = $element['labelText'] ?? 'N° de commande:';

                    // Replace variables in format
                    $order_number = $this->replace_order_variables($format, $this->order);

                    if ($show_label && $label_text) {
                        $order_number = '<strong>' . esc_html($label_text) . '</strong><br>' . $order_number;
                    }

                    error_log('[PDF Generator] ORDER NUMBER - Final content: ' . str_replace('<br>', ' | ', $order_number));
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; font-size: 14px; font-weight: bold; text-align: right;'>" . wp_kses_post($order_number ?: 'Texte') . "</div>";

            case 'document_type':
                // LOG DES PROPRIÉTÉS DOCUMENT TYPE
                error_log('[PDF Generator] DOCUMENT TYPE - documentType: ' . ($element['documentType'] ?? 'DEFAULT(invoice)'));

                $doc_type = $element['documentType'] ?? 'invoice';
                $doc_label = $doc_type === 'invoice' ? 'FACTURE' : ($doc_type === 'quote' ? 'DEVIS' : strtoupper($doc_type));

                error_log('[PDF Generator] DOCUMENT TYPE - Generated label: ' . $doc_label);

                return "<div class='canvas-element' style='" . esc_attr($style) . "; font-size: 18px; font-weight: bold; text-align: center;'>" . esc_html($doc_label) . "</div>";

            case 'mentions':
                // LOG DES PROPRIÉTÉS MENTIONS
                error_log('[PDF Generator] MENTIONS - showEmail: ' . ($element['showEmail'] ?? 'DEFAULT(true)'));
                error_log('[PDF Generator] MENTIONS - showPhone: ' . ($element['showPhone'] ?? 'DEFAULT(true)'));
                error_log('[PDF Generator] MENTIONS - showSiret: ' . ($element['showSiret'] ?? 'DEFAULT(true)'));
                error_log('[PDF Generator] MENTIONS - showVat: ' . ($element['showVat'] ?? 'DEFAULT(false)'));
                error_log('[PDF Generator] MENTIONS - showAddress: ' . ($element['showAddress'] ?? 'DEFAULT(false)'));
                error_log('[PDF Generator] MENTIONS - showWebsite: ' . ($element['showWebsite'] ?? 'DEFAULT(false)'));
                error_log('[PDF Generator] MENTIONS - showCustomText: ' . ($element['showCustomText'] ?? 'DEFAULT(false)'));
                error_log('[PDF Generator] MENTIONS - customText: ' . ($element['customText'] ?? 'NOT_SET'));
                error_log('[PDF Generator] MENTIONS - separator: ' . ($element['separator'] ?? 'DEFAULT( • )'));

                $mentions = '';
                if ($this->order) {
                    $show_email = $element['showEmail'] ?? true;
                    $show_phone = $element['showPhone'] ?? true;
                    $show_siret = $element['showSiret'] ?? true;
                    $show_vat = $element['showVat'] ?? false;
                    $show_address = $element['showAddress'] ?? false;
                    $show_website = $element['showWebsite'] ?? false;
                    $show_custom_text = $element['showCustomText'] ?? false;
                    $custom_text = $element['customText'] ?? '';
                    $separator = $element['separator'] ?? ' • ';

                    $mention_parts = [];

                    if ($show_email) {
                        $email = get_option('pdf_builder_company_email', '');
                        if ($email) {
                            $mention_parts[] = esc_html($email);
                        }
                    }

                    if ($show_phone) {
                        $phone = get_option('pdf_builder_company_phone', '');
                        if ($phone) {
                            $mention_parts[] = esc_html($phone);
                        }
                    }

                    if ($show_siret) {
                        $siret = get_option('pdf_builder_company_siret', '');
                        if ($siret) {
                            $mention_parts[] = 'SIRET ' . esc_html($siret);
                        }
                    }

                    if ($show_vat) {
                        $vat = get_option('pdf_builder_company_vat', '');
                        if ($vat) {
                            $mention_parts[] = 'TVA ' . esc_html($vat);
                        }
                    }

                    if ($show_address) {
                        $address = get_option('pdf_builder_company_address', '');
                        if ($address) {
                            $mention_parts[] = esc_html($address);
                        }
                    }

                    if ($show_website) {
                        $website = get_option('home');
                        if ($website) {
                            $mention_parts[] = esc_html($website);
                        }
                    }

                    if ($show_custom_text && $custom_text) {
                        $mention_parts[] = esc_html($custom_text);
                    }

                    $mentions = implode($separator, $mention_parts);
                    error_log('[PDF Generator] MENTIONS - Generated content: ' . $mentions);
                }
                return "<div class='canvas-element' style='" . esc_attr($style) . "; font-size: 8px; text-align: center;'>" . esc_html($mentions ?: 'Texte') . "</div>";
        }
    }

    /**
     * Extraire les styles CSS des propriétés de l'élément
     */
    private function extract_element_styles($properties) {
        $styles = [];

        // Couleur de fond
        if (isset($properties['backgroundColor'])) {
            $styles[] = 'background-color: ' . esc_attr($properties['backgroundColor']);
        }

        // Couleur du texte
        if (isset($properties['color'])) {
            $styles[] = 'color: ' . esc_attr($properties['color']);
        }

        // Taille de police
        if (isset($properties['fontSize'])) {
            $styles[] = 'font-size: ' . intval($properties['fontSize']) . 'px';
        }

        // Poids de la police
        if (isset($properties['fontWeight'])) {
            $styles[] = 'font-weight: ' . esc_attr($properties['fontWeight']);
        }

        // Style de la police
        if (isset($properties['fontStyle'])) {
            $styles[] = 'font-style: ' . esc_attr($properties['fontStyle']);
        }

        // Famille de police
        if (isset($properties['fontFamily'])) {
            $styles[] = 'font-family: ' . esc_attr($properties['fontFamily']);
        }

        // Alignement du texte
        if (isset($properties['textAlign'])) {
            $styles[] = 'text-align: ' . esc_attr($properties['textAlign']);
        }

        // Décoration du texte
        if (isset($properties['textDecoration'])) {
            $styles[] = 'text-decoration: ' . esc_attr($properties['textDecoration']);
        }

        // Hauteur de ligne
        if (isset($properties['lineHeight'])) {
            $styles[] = 'line-height: ' . esc_attr($properties['lineHeight']);
        }

        // Opacité
        if (isset($properties['opacity'])) {
            $styles[] = 'opacity: ' . floatval($properties['opacity']);
        }

        // Bordures
        $border_width = $properties['borderWidth'] ?? 0;
        $border_style = $properties['borderStyle'] ?? 'solid';
        $border_color = $properties['borderColor'] ?? '#000000';
        $border_radius = $properties['borderRadius'] ?? 0;

        if ($border_width > 0) {
            $styles[] = "border: {$border_width}px {$border_style} {$border_color}";
        }

        if ($border_radius > 0) {
            $styles[] = "border-radius: {$border_radius}px";
        }

        // Ombre
        if (isset($properties['shadow']) && $properties['shadow']) {
            $shadow_color = $properties['shadowColor'] ?? '#000000';
            $shadow_offset_x = $properties['shadowOffsetX'] ?? 2;
            $shadow_offset_y = $properties['shadowOffsetY'] ?? 2;
            $shadow_blur = $properties['shadowBlur'] ?? 1;
            $styles[] = "box-shadow: {$shadow_offset_x}px {$shadow_offset_y}px {$shadow_blur}px {$shadow_color}";
        }

        // Transformations
        $transforms = [];
        if (isset($properties['rotation']) && $properties['rotation'] != 0) {
            $transforms[] = 'rotate(' . intval($properties['rotation']) . 'deg)';
        }
        if (isset($properties['scale']) && $properties['scale'] != 1) {
            $transforms[] = 'scale(' . floatval($properties['scale']) . ')';
        }
        if (!empty($transforms)) {
            $styles[] = 'transform: ' . implode(' ', $transforms);
        }

        // Filtres CSS
        $filters = [];
        if (isset($properties['brightness']) && $properties['brightness'] != 100) {
            $filters[] = "brightness({$properties['brightness']}%)";
        }
        if (isset($properties['contrast']) && $properties['contrast'] != 100) {
            $filters[] = "contrast({$properties['contrast']}%)";
        }
        if (isset($properties['saturate']) && $properties['saturate'] != 100) {
            $filters[] = "saturate({$properties['saturate']}%)";
        }
        if (isset($properties['blur']) && $properties['blur'] > 0) {
            $filters[] = "blur({$properties['blur']}px)";
        }
        if (isset($properties['hueRotate']) && $properties['hueRotate'] != 0) {
            $filters[] = "hue-rotate({$properties['hueRotate']}deg)";
        }
        if (isset($properties['sepia']) && $properties['sepia'] > 0) {
            $filters[] = "sepia({$properties['sepia']}%)";
        }
        if (isset($properties['grayscale']) && $properties['grayscale'] > 0) {
            $filters[] = "grayscale({$properties['grayscale']}%)";
        }
        if (isset($properties['invert']) && $properties['invert'] > 0) {
            $filters[] = "invert({$properties['invert']}%)";
        }
        if (!empty($filters)) {
            $styles[] = 'filter: ' . implode(' ', $filters);
        }

        // Visibilité
        if (isset($properties['visible']) && !$properties['visible']) {
            $styles[] = 'display: none';
        }
    }

    /**
     * Convertir une propriété d'élément en propriété CSS
     */
    private function convert_property_to_css($property, $value, $element = null) {
        switch ($property) {
            case 'color':
                return 'color: ' . esc_attr($value);
            case 'backgroundColor':
                return 'background-color: ' . esc_attr($value);
            case 'fontSize':
                return 'font-size: ' . intval($value) . 'px';
            case 'fontWeight':
                return 'font-weight: ' . esc_attr($value);
            case 'fontStyle':
                return 'font-style: ' . esc_attr($value);
            case 'textAlign':
                return 'text-align: ' . esc_attr($value);
            case 'fontFamily':
                return 'font-family: ' . esc_attr($value);
            case 'textDecoration':
                return 'text-decoration: ' . esc_attr($value);
            case 'opacity':
                return 'opacity: ' . floatval($value);
            case 'border':
            case 'borderColor':
            case 'borderWidth':
            case 'borderStyle':
            case 'borderRadius':
                // Les propriétés de bordure sont gérées ensemble dans extract_element_styles
                return null;
            case 'rotation':
            case 'scale':
                // Les transformations sont gérées ensemble dans extract_element_styles
                return null;
            case 'brightness':
                return $value != 100 ? "filter: brightness({$value}%)" : null;
            case 'contrast':
                return $value != 100 ? "filter: contrast({$value}%)" : null;
            case 'saturate':
                return $value != 100 ? "filter: saturate({$value}%)" : null;
            case 'blur':
                return $value > 0 ? "filter: blur({$value}px)" : null;
            case 'hueRotate':
                return $value != 0 ? "filter: hue-rotate({$value}deg)" : null;
            case 'sepia':
                return $value > 0 ? "filter: sepia({$value}%)" : null;
            case 'grayscale':
                return $value > 0 ? "filter: grayscale({$value}%)" : null;
            case 'invert':
                return $value > 0 ? "filter: invert({$value}%)" : null;
            case 'shadowColor':
            case 'shadowOffsetX':
            case 'shadowOffsetY':
            case 'shadowBlur':
                // Les propriétés shadow sont gérées ensemble dans extract_element_styles
                return null;
            default:
                return null;
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

        // Appliquer les remplacements dans l'ordre : doubles, simples, crochets
        $original_content = $content;
        $content = str_replace(array_keys($double_brace_replacements), array_values($double_brace_replacements), $content);
        error_log('[PDF Generator] After double brace replacements: "' . $original_content . '" -> "' . $content . '"');
        $content = str_replace(array_keys($single_brace_replacements), array_values($single_brace_replacements), $content);
        error_log('[PDF Generator] After single brace replacements: "' . $original_content . '" -> "' . $content . '"');
        $content = str_replace(array_keys($bracket_replacements), array_values($bracket_replacements), $content);
        error_log('[PDF Generator] After bracket replacements: "' . $original_content . '" -> "' . $content . '"');

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

    /**
     * Retourne les styles prédéfinis pour les tableaux selon le style choisi
     */
    private function get_table_styles($style) {
        $styles = [
            'default' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f9f9f9',
                'odd_row_text_color' => '#333333',
                'header_bg' => '#f5f5f5',
                'header_text_color' => '#333333',
                'border_color' => '#ddd',
                'name_color' => 'inherit',
                'quantity_color' => '#2563eb',
                'price_color' => '#16a34a',
                'total_color' => '#dc2626'
            ],
            'classic' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#ebebeb',
                'odd_row_text_color' => '#666666',
                'header_bg' => '#f5f5f5',
                'header_text_color' => '#333333',
                'border_color' => '#ddd',
                'name_color' => 'inherit',
                'quantity_color' => '#2563eb',
                'price_color' => '#16a34a',
                'total_color' => '#dc2626'
            ],
            'striped' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f8f9fa',
                'odd_row_text_color' => '#495057',
                'header_bg' => '#e9ecef',
                'header_text_color' => '#212529',
                'border_color' => '#dee2e6',
                'name_color' => 'inherit',
                'quantity_color' => '#007bff',
                'price_color' => '#28a745',
                'total_color' => '#dc3545'
            ],
            'bordered' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#ffffff',
                'odd_row_text_color' => '#333333',
                'header_bg' => '#f8f9fa',
                'header_text_color' => '#495057',
                'border_color' => '#dee2e6',
                'name_color' => 'inherit',
                'quantity_color' => '#6c757d',
                'price_color' => '#28a745',
                'total_color' => '#dc3545'
            ],
            'minimal' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#ffffff',
                'odd_row_text_color' => '#333333',
                'header_bg' => '#ffffff',
                'header_text_color' => '#666666',
                'border_color' => '#f0f0f0',
                'name_color' => 'inherit',
                'quantity_color' => '#999999',
                'price_color' => '#666666',
                'total_color' => '#333333'
            ],
            'modern' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f8f9ff',
                'odd_row_text_color' => '#2d3748',
                'header_bg' => '#2d3748',
                'header_text_color' => '#ffffff',
                'border_color' => '#e2e8f0',
                'name_color' => 'inherit',
                'quantity_color' => '#3182ce',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'blue_ocean' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f0f8ff',
                'odd_row_text_color' => '#2c5282',
                'header_bg' => '#2c5282',
                'header_text_color' => '#ffffff',
                'border_color' => '#90cdf4',
                'name_color' => 'inherit',
                'quantity_color' => '#3182ce',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'emerald_forest' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f0fff4',
                'odd_row_text_color' => '#22543d',
                'header_bg' => '#22543d',
                'header_text_color' => '#ffffff',
                'border_color' => '#9ae6b4',
                'name_color' => 'inherit',
                'quantity_color' => '#38a169',
                'price_color' => '#3182ce',
                'total_color' => '#e53e3e'
            ],
            'sunset_orange' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#fffaf0',
                'odd_row_text_color' => '#9c4221',
                'header_bg' => '#9c4221',
                'header_text_color' => '#ffffff',
                'border_color' => '#fbb6ce',
                'name_color' => 'inherit',
                'quantity_color' => '#dd6b20',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'royal_purple' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#faf5ff',
                'odd_row_text_color' => '#553c9a',
                'header_bg' => '#553c9a',
                'header_text_color' => '#ffffff',
                'border_color' => '#d6bcfa',
                'name_color' => 'inherit',
                'quantity_color' => '#805ad5',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'rose_pink' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#fff5f5',
                'odd_row_text_color' => '#97266d',
                'header_bg' => '#97266d',
                'header_text_color' => '#ffffff',
                'border_color' => '#fed7d7',
                'name_color' => 'inherit',
                'quantity_color' => '#d53f8c',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'teal_aqua' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#e6fffa',
                'odd_row_text_color' => '#234e52',
                'header_bg' => '#234e52',
                'header_text_color' => '#ffffff',
                'border_color' => '#81e6d9',
                'name_color' => 'inherit',
                'quantity_color' => '#319795',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'crimson_red' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#fff5f5',
                'odd_row_text_color' => '#9b2c2c',
                'header_bg' => '#9b2c2c',
                'header_text_color' => '#ffffff',
                'border_color' => '#feb2b2',
                'name_color' => 'inherit',
                'quantity_color' => '#e53e3e',
                'price_color' => '#38a169',
                'total_color' => '#c53030'
            ],
            'amber_gold' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#fffff0',
                'odd_row_text_color' => '#744210',
                'header_bg' => '#744210',
                'header_text_color' => '#ffffff',
                'border_color' => '#fbd38d',
                'name_color' => 'inherit',
                'quantity_color' => '#d69e2e',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'indigo_night' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f7fafc',
                'odd_row_text_color' => '#2a4365',
                'header_bg' => '#2a4365',
                'header_text_color' => '#ffffff',
                'border_color' => '#a0aec0',
                'name_color' => 'inherit',
                'quantity_color' => '#4c51bf',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'slate_gray' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f7fafc',
                'odd_row_text_color' => '#2d3748',
                'header_bg' => '#2d3748',
                'header_text_color' => '#ffffff',
                'border_color' => '#a0aec0',
                'name_color' => 'inherit',
                'quantity_color' => '#4a5568',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'coral_sunset' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#fff5f5',
                'odd_row_text_color' => '#c05621',
                'header_bg' => '#c05621',
                'header_text_color' => '#ffffff',
                'border_color' => '#fed7cc',
                'name_color' => 'inherit',
                'quantity_color' => '#ed8936',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'mint_green' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f0fff4',
                'odd_row_text_color' => '#276749',
                'header_bg' => '#276749',
                'header_text_color' => '#ffffff',
                'border_color' => '#9ae6b4',
                'name_color' => 'inherit',
                'quantity_color' => '#38a169',
                'price_color' => '#319795',
                'total_color' => '#e53e3e'
            ],
            'violet_dream' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#faf5ff',
                'odd_row_text_color' => '#6b46c1',
                'header_bg' => '#6b46c1',
                'header_text_color' => '#ffffff',
                'border_color' => '#d6bcfa',
                'name_color' => 'inherit',
                'quantity_color' => '#805ad5',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'sky_blue' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#ebf8ff',
                'odd_row_text_color' => '#2a69ac',
                'header_bg' => '#2a69ac',
                'header_text_color' => '#ffffff',
                'border_color' => '#90cdf4',
                'name_color' => 'inherit',
                'quantity_color' => '#4299e1',
                'price_color' => '#38a169',
                'total_color' => '#e53e3e'
            ],
            'forest_green' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#f0fff4',
                'odd_row_text_color' => '#22543d',
                'header_bg' => '#22543d',
                'header_text_color' => '#ffffff',
                'border_color' => '#9ae6b4',
                'name_color' => 'inherit',
                'quantity_color' => '#38a169',
                'price_color' => '#3182ce',
                'total_color' => '#e53e3e'
            ],
            'ruby_red' => [
                'even_row_bg' => '#ffffff',
                'odd_row_bg' => '#fff5f5',
                'odd_row_text_color' => '#9b2c2c',
                'header_bg' => '#9b2c2c',
                'header_text_color' => '#ffffff',
                'border_color' => '#feb2b2',
                'name_color' => 'inherit',
                'quantity_color' => '#e53e3e',
                'price_color' => '#38a169',
                'total_color' => '#c53030'
            ]
        ];

        return $styles[$style] ?? $styles['default'];
    }

    /**
     * Génère le HTML du tableau en utilisant d'abord un template avec données fictives du canvas, puis remplace par les vraies données
     */
    private function generate_table_html_from_canvas_template($element) {
        // Créer des données fictives pour générer le template HTML du canvas
        $fake_data = $this->create_fake_order_data_for_template();

        // Générer le HTML du tableau avec les données fictives (mais préserve tous les styles du canvas)
        $fake_html = $this->generate_fake_table_html($element, $fake_data);

        // Remplacer les données fictives par les vraies données de la commande
        $real_data = $this->create_real_order_data();
        $real_html = $this->replace_fake_data_with_real_data($fake_html, $fake_data, $real_data);

        return $real_html;
    }

    /**
     * Crée des données fictives pour générer le template HTML du canvas
     */
    private function create_fake_order_data_for_template() {
        return [
            'items' => [
                ['name' => 'FAKE_PRODUCT_1', 'quantity' => 1, 'price' => 10.00, 'total' => 10.00],
                ['name' => 'FAKE_PRODUCT_2', 'quantity' => 2, 'price' => 20.00, 'total' => 40.00],
            ],
            'subtotal' => 50.00,
            'shipping' => 5.00,
            'tax' => 10.00,
            'discount' => 0.00,
            'total' => 65.00
        ];
    }

    /**
     * Crée les vraies données de la commande
     */
    private function create_real_order_data() {
        if (!$this->order) {
            return $this->create_fake_order_data_for_template(); // Fallback
        }

        $items = [];
        foreach ($this->order->get_items() as $item) {
            $product = $item->get_product();
            $price = $product ? $product->get_price() : 0;
            $price_formatted = function_exists('wc_price') ? wc_price($price) : $price;
            $total_formatted = function_exists('wc_price') ? wc_price($item->get_total()) : $item->get_total();

            $items[] = [
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $price_formatted,
                'total' => $total_formatted
            ];
        }

        $subtotal = function_exists('wc_price') ? wc_price($this->order->get_subtotal()) : $this->order->get_subtotal();
        $shipping = function_exists('wc_price') ? wc_price($this->order->get_shipping_total()) : $this->order->get_shipping_total();
        $tax = function_exists('wc_price') ? wc_price($this->order->get_total_tax()) : $this->order->get_total_tax();
        $discount = function_exists('wc_price') ? wc_price($this->order->get_total_discount()) : $this->order->get_total_discount();
        $total = function_exists('wc_price') ? wc_price($this->order->get_total()) : $this->order->get_total();

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total
        ];
    }

    /**
     * Génère le HTML du tableau avec les données fictives mais préserve tous les styles du canvas
     */
    private function generate_fake_table_html($element, $fake_data) {
        // Utiliser exactement la même logique que l'ancien code pour générer le HTML
        // mais avec les données fictives au lieu des vraies données

        $show_headers = $element['showHeaders'] ?? true;
        $show_borders = $element['showBorders'] ?? true;
        $headers = $element['headers'] ?? ['Produit', 'Qté', 'Prix', 'Total'];
        $columns = $element['columns'] ?? ['image' => false, 'name' => true, 'sku' => false, 'quantity' => true, 'price' => true, 'total' => true];
        $table_style = $element['tableStyle'] ?? 'classic';

        // Appliquer les styles prédéfinis selon tableStyle
        $table_styles = $this->get_table_styles($table_style);
        $even_row_bg = $element['evenRowBg'] ?? $table_styles['even_row_bg'];
        $odd_row_bg = $element['oddRowBg'] ?? $table_styles['odd_row_bg'];
        $odd_row_text_color = $element['oddRowTextColor'] ?? $table_styles['odd_row_text_color'];
        $header_bg = $element['headerBg'] ?? $table_styles['header_bg'];
        $header_text_color = $element['headerTextColor'] ?? $table_styles['header_text_color'];
        $border_color = $element['borderColor'] ?? $table_styles['border_color'];
        $name_color = $element['nameColor'] ?? $table_styles['name_color'];
        $quantity_color = $element['quantityColor'] ?? $table_styles['quantity_color'];
        $price_color = $element['priceColor'] ?? $table_styles['price_color'];
        $total_color = $element['totalColor'] ?? $table_styles['total_color'];

        // Styles spécifiques pour les colonnes
        $name_style = $element['nameStyle'] ?? 'font-weight: 500;';
        $quantity_style = $element['quantityStyle'] ?? 'text-align: center;';
        $price_style = $element['priceStyle'] ?? 'text-align: right;';
        $total_style = $element['totalStyle'] ?? 'text-align: right; font-weight: bold;';

        // Appliquer les propriétés CSS générales au tableau
        $table_css = 'width: 100%;';

        // Couleur de fond générale du tableau
        if (isset($element['backgroundColor']) && $element['backgroundColor'] !== 'transparent') {
            $table_css .= ' background-color: ' . esc_attr($element['backgroundColor']) . ';';
        }

        // Couleur du texte générale
        if (isset($element['color'])) {
            $table_css .= ' color: ' . esc_attr($element['color']) . ';';
        }

        // Taille de police
        if (isset($element['fontSize'])) {
            $table_css .= ' font-size: ' . intval($element['fontSize']) . 'px;';
        } else {
            $table_css .= ' font-size: 12px;'; // Valeur par défaut
        }

        // Famille de police
        if (isset($element['fontFamily'])) {
            $table_css .= ' font-family: ' . esc_attr($element['fontFamily']) . ';';
        }

        // Poids de la police
        if (isset($element['fontWeight'])) {
            $table_css .= ' font-weight: ' . esc_attr($element['fontWeight']) . ';';
        }

        // Style de la police
        if (isset($element['fontStyle'])) {
            $table_css .= ' font-style: ' . esc_attr($element['fontStyle']) . ';';
        }

        // Bordures générales - ajouter une bordure par défaut si aucune n'est spécifiée
        $border_width = $element['borderWidth'] ?? 1; // Bordure de 1px par défaut
        $border_style = $element['borderStyle'] ?? 'solid';
        $border_color = $element['borderColor'] ?? '#cccccc'; // Gris clair par défaut
        if ($border_width > 0) {
            $table_css .= " border: {$border_width}px {$border_style} {$border_color};";
        }

        // Rayon des bordures
        $border_radius = $element['borderRadius'] ?? 0;
        if ($border_radius > 0) {
            $table_css .= " border-radius: {$border_radius}px;";
        }

        // Ombres
        if (isset($element['shadow']) && $element['shadow']) {
            $shadow_color = $element['shadowColor'] ?? '#000000';
            $shadow_offset_x = $element['shadowOffsetX'] ?? 2;
            $shadow_offset_y = $element['shadowOffsetY'] ?? 2;
            $shadow_blur = $element['shadowBlur'] ?? 1;
            $table_css .= " box-shadow: {$shadow_offset_x}px {$shadow_offset_y}px {$shadow_blur}px {$shadow_color};";
        }

        // Ajouter le border-collapse pour les bordures de cellules
        if ($show_borders) {
            $table_css .= ' border-collapse: collapse;';
        }

        // Style des bordures pour les cellules
        $cell_border_style = $show_borders ? "border: 1px solid {$border_color};" : '';

        $table_html = "<table style='{$table_css}'>";

        // Headers
        if ($show_headers) {
            $table_html .= "<thead><tr style='background-color: {$header_bg}; color: {$header_text_color};'>";
            foreach ($headers as $header) {
                $table_html .= "<th style='padding: 8px; text-align: left; {$cell_border_style} font-weight: bold;'>{$header}</th>";
            }
            $table_html .= "</tr></thead>";
        }

        $table_html .= "<tbody>";

        // Utiliser les données fictives pour générer les lignes
        $row_count = 0;
        foreach ($fake_data['items'] as $item) {
            $row_count++;
            $is_even = ($row_count % 2 === 0);
            $bg_color = $is_even ? $even_row_bg : $odd_row_bg;
            $text_color = $is_even ? ($odd_row_text_color ?? 'inherit') : $odd_row_text_color;

            $table_html .= "<tr style='background-color: {$bg_color}; color: {$text_color};'>";

            // Product Name
            if ($columns['name']) {
                $table_html .= "<td style='padding: 8px; {$cell_border_style} {$name_style} color: {$name_color};'>{$item['name']}</td>";
            }

            // Quantity
            if ($columns['quantity']) {
                $table_html .= "<td style='padding: 8px; {$cell_border_style} {$quantity_style} color: {$quantity_color};'>{$item['quantity']}</td>";
            }

            // Price
            if ($columns['price']) {
                $table_html .= "<td style='padding: 8px; {$cell_border_style} {$price_style} color: {$price_color};'>{$item['price']}</td>";
            }

            // Total
            if ($columns['total']) {
                $table_html .= "<td style='padding: 8px; {$cell_border_style} {$total_style} color: {$total_color};'>{$item['total']}</td>";
            }

            $table_html .= "</tr>";
        }

        // Subtotal, Shipping, Taxes, Total
        $show_subtotal = $element['showSubtotal'] ?? true;
        $show_shipping = $element['showShipping'] ?? true;
        $show_taxes = $element['showTaxes'] ?? true;
        $show_discount = $element['showDiscount'] ?? true;
        $show_total = $element['showTotal'] ?? true;

        if ($show_subtotal || $show_shipping || $show_taxes || $show_discount || $show_total) {
            $table_html .= "<tr style='background-color: #f9f9f9; font-weight: bold;'><td colspan='" . count(array_filter($columns)) . "' style='padding: 8px; {$cell_border_style} text-align: right;'>";

            $summary_lines = [];

            if ($show_subtotal) {
                $summary_lines[] = "Sous-total: {$fake_data['subtotal']}";
            }

            if ($show_shipping && $fake_data['shipping'] > 0) {
                $summary_lines[] = "Livraison: {$fake_data['shipping']}";
            }

            if ($show_taxes && $fake_data['tax'] > 0) {
                $summary_lines[] = "TVA: {$fake_data['tax']}";
            }

            if ($show_discount && $fake_data['discount'] > 0) {
                $summary_lines[] = "Remise: -{$fake_data['discount']}";
            }

            if ($show_total) {
                $summary_lines[] = "TOTAL: {$fake_data['total']}";
            }

            $table_html .= implode('<br>', $summary_lines);
            $table_html .= "</td></tr>";
        }

        $table_html .= "</tbody></table>";

        return $table_html;
    }

    /**
     * Remplace les données fictives par les vraies données dans le HTML généré
     */
    private function replace_fake_data_with_real_data($html, $fake_data, $real_data) {
        // Remplacer les noms de produits fictifs
        foreach ($fake_data['items'] as $index => $fake_item) {
            if (isset($real_data['items'][$index])) {
                $real_item = $real_data['items'][$index];
                $html = str_replace($fake_item['name'], $real_item['name'], $html);
                $html = str_replace((string)$fake_item['quantity'], (string)$real_item['quantity'], $html);
                $html = str_replace($fake_item['price'], $real_item['price'], $html);
                $html = str_replace($fake_item['total'], $real_item['total'], $html);
            }
        }

        // Remplacer les totaux
        $html = str_replace($fake_data['subtotal'], $real_data['subtotal'], $html);
        $html = str_replace($fake_data['shipping'], $real_data['shipping'], $html);
        $html = str_replace($fake_data['tax'], $real_data['tax'], $html);
        $html = str_replace($fake_data['discount'], $real_data['discount'], $html);
        $html = str_replace($fake_data['total'], $real_data['total'], $html);

        return $html;
    }
}
