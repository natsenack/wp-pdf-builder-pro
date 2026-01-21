<?php

/**
 * PDF Builder Pro - Style Builder
 * Responsable de la construction des styles CSS pour les éléments
 */

namespace PDF_Builder\Admin\Builders;

/**
 * Classe responsable de la construction des styles CSS des éléments
 */
class StyleBuilder
{
    /**
     * Construit le style CSS pour un élément
     *
     * @param array $element Données de l'élément
     * @param string $base_style Style de base à appliquer
     * @return string Style CSS généré
     */
    public function buildElementStyle($element, $base_style = '')
    {
        $style = $base_style;
        $properties_found = [];

        // ========== PROPRIÉTÉS DE TEXTE ==========

        if (isset($element['color']) && !empty($element['color'])) {
            $style .= "color: {$element['color']}; ";
            $properties_found[] = 'color=' . $element['color'];
        }

        if (isset($element['fontSize']) && $element['fontSize'] > 0) {
            $style .= "font-size: {$element['fontSize']}px; ";
            $properties_found[] = 'fontSize=' . $element['fontSize'];
        }

        if (isset($element['fontWeight'])) {
            $weight = is_numeric($element['fontWeight']) ? $element['fontWeight'] : $element['fontWeight'];
            $style .= "font-weight: {$weight}; ";
            $properties_found[] = 'fontWeight=' . $weight;
        }

        if (isset($element['fontStyle']) && !empty($element['fontStyle'])) {
            $style .= "font-style: {$element['fontStyle']}; ";
            $properties_found[] = 'fontStyle=' . $element['fontStyle'];
        }

        if (isset($element['fontFamily']) && !empty($element['fontFamily'])) {
            $style .= "font-family: {$element['fontFamily']}; ";
            $properties_found[] = 'fontFamily=' . $element['fontFamily'];
        }

        if (isset($element['textAlign']) && !empty($element['textAlign'])) {
            $style .= "text-align: {$element['textAlign']}; ";
            $properties_found[] = 'textAlign=' . $element['textAlign'];
        }

        if (isset($element['lineHeight']) && !empty($element['lineHeight'])) {
            $line_height = is_numeric($element['lineHeight']) ? $element['lineHeight'] . 'px' : $element['lineHeight'];
            $style .= "line-height: {$line_height}; ";
            $properties_found[] = 'lineHeight=' . $element['lineHeight'];
        }

        if (isset($element['textDecoration']) && !empty($element['textDecoration'])) {
            $style .= "text-decoration: {$element['textDecoration']}; ";
            $properties_found[] = 'textDecoration=' . $element['textDecoration'];
        }

        if (isset($element['textTransform']) && !empty($element['textTransform'])) {
            $style .= "text-transform: {$element['textTransform']}; ";
            $properties_found[] = 'textTransform=' . $element['textTransform'];
        }

        if (isset($element['letterSpacing']) && $element['letterSpacing'] != 0) {
            $style .= "letter-spacing: {$element['letterSpacing']}px; ";
            $properties_found[] = 'letterSpacing=' . $element['letterSpacing'];
        }

        // ========== PROPRIÉTÉS DE FOND ET BORDURES ==========

        if (isset($element['showBackground']) && $element['showBackground'] && isset($element['backgroundColor'])) {
            $bg_color = $element['backgroundColor'];
            if (isset($element['backgroundOpacity']) && $element['backgroundOpacity'] > 0 && $element['backgroundOpacity'] < 1) {
                $opacity_hex = dechex((int) round($element['backgroundOpacity'] * 255));
                $bg_color = substr($bg_color, 0, 7) . $opacity_hex;
                $properties_found[] = 'backgroundOpacity=' . $element['backgroundOpacity'];
            }
            $style .= "background-color: {$bg_color}; ";
            $properties_found[] = 'backgroundColor=' . $bg_color;
        }

        if (isset($element['padding']) && $element['padding'] > 0) {
            $style .= "padding: {$element['padding']}px; ";
            $properties_found[] = 'padding=' . $element['padding'];
        }

        if (isset($element['margin']) && $element['margin'] > 0) {
            $style .= "margin: {$element['margin']}px; ";
            $properties_found[] = 'margin=' . $element['margin'];
        }

        if (isset($element['borderWidth']) && $element['borderWidth'] > 0) {
            $border_color = isset($element['borderColor']) ? $element['borderColor'] : '#000000';
            $border_style_val = isset($element['borderStyle']) ? $element['borderStyle'] : 'solid';
            $style .= "border: {$element['borderWidth']}px {$border_style_val} {$border_color}; ";
            $properties_found[] = 'border=' . $element['borderWidth'];
        }

        if (isset($element['borderRadius']) && $element['borderRadius'] > 0) {
            $style .= "border-radius: {$element['borderRadius']}px; ";
            $properties_found[] = 'borderRadius=' . $element['borderRadius'];
        }

        // ========== PROPRIÉTÉS DE TRANSFORMATION ET EFFETS ==========

        if (isset($element['opacity']) && $element['opacity'] < 1) {
            $style .= "opacity: {$element['opacity']}; ";
            $properties_found[] = 'opacity=' . $element['opacity'];
        }

        $transform_parts = [];
        if (isset($element['rotation']) && $element['rotation'] != 0 && pdf_builder_get_option('pdf_builder_canvas_rotate_enabled', '1') == '1') {
            $transform_parts[] = "rotate({$element['rotation']}deg)";
            $properties_found[] = 'rotation=' . $element['rotation'];
        }
        if (isset($element['scale']) && $element['scale'] != 100) {
            $scale_value = $element['scale'] / 100;
            $transform_parts[] = "scale({$scale_value})";
            $properties_found[] = 'scale=' . $element['scale'];
        }
        if (!empty($transform_parts)) {
            $style .= "transform: " . implode(' ', $transform_parts) . "; ";
        }

        $filter_parts = [];
        if (isset($element['brightness']) && $element['brightness'] != 100) {
            $filter_parts[] = "brightness({$element['brightness']}%)";
            $properties_found[] = 'brightness=' . $element['brightness'];
        }
        if (isset($element['contrast']) && $element['contrast'] != 100) {
            $filter_parts[] = "contrast({$element['contrast']}%)";
            $properties_found[] = 'contrast=' . $element['contrast'];
        }
        if (isset($element['saturate']) && $element['saturate'] != 100) {
            $filter_parts[] = "saturate({$element['saturate']}%)";
            $properties_found[] = 'saturate=' . $element['saturate'];
        }
        if (!empty($filter_parts)) {
            $style .= "filter: " . implode(' ', $filter_parts) . "; ";
        }

        // ========== PROPRIÉTÉS D'OMBRE ==========

        if (isset($element['boxShadowColor']) && !empty($element['boxShadowColor'])) {
            $shadow_x = isset($element['boxShadowX']) ? $element['boxShadowX'] : 0;
            $shadow_y = isset($element['boxShadowY']) ? $element['boxShadowY'] : 0;
            $shadow_blur = isset($element['boxShadowBlur']) ? $element['boxShadowBlur'] : 10;
            $shadow_spread = isset($element['boxShadowSpread']) ? $element['boxShadowSpread'] : 0;
            $style .= "box-shadow: {$shadow_x}px {$shadow_y}px {$shadow_blur}px {$shadow_spread}px {$element['boxShadowColor']}; ";
            $properties_found[] = 'boxShadow=' . $element['boxShadowColor'];
        } elseif (isset($element['shadow']) && $element['shadow']) {
            $style .= "box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); ";
            $properties_found[] = 'shadow=true';
        }

        // ========== PROPRIÉTÉS D'IMAGE ==========

        if (isset($element['objectFit']) && !empty($element['objectFit'])) {
            $style .= "object-fit: {$element['objectFit']}; ";
            $properties_found[] = 'objectFit=' . $element['objectFit'];
        } elseif (isset($element['fit']) && !empty($element['fit'])) {
            $style .= "object-fit: {$element['fit']}; ";
            $properties_found[] = 'fit=' . $element['fit'];
        }

        // ========== AUTRES PROPRIÉTÉS ==========

        if (isset($element['zIndex']) && $element['zIndex'] > 0) {
            $style .= "z-index: {$element['zIndex']}; ";
            $properties_found[] = 'zIndex=' . $element['zIndex'];
        }

        $style .= "box-sizing: border-box; ";

        return $style;
    }
}

