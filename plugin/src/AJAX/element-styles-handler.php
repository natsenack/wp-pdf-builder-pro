<?php

/**
 * PDF Builder Pro - Dynamic Element Styles
 * Génère les styles CSS dynamiques pour les éléments du canvas basés sur le template
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Cette fonction sera appelée pour générer des styles CSS pour les éléments
function pdf_builder_get_element_inline_styles($element)
{

    $styles = [];
// Récupérer les propriétés de l'élément
    $type = $element['type'] ?? '';
    $properties = $element['properties'] ?? [];
// Appliquer les styles selon le type
    switch ($type) {
        case 'rectangle':
        case 'shape':
            if (isset($properties['fillColor'])) {
                $styles['background-color'] = $properties['fillColor'];
            }
            if (isset($properties['strokeColor']) && isset($properties['strokeWidth'])) {
                                                                                                                                                                                                             $styles['border'] = $properties['strokeWidth'] . 'px solid ' . $properties['strokeColor'];
            }

            break;
        case 'circle':
            if (isset($properties['fillColor'])) {
                $styles['background-color'] = $properties['fillColor'];
            }
            if (isset($properties['strokeColor']) && isset($properties['strokeWidth'])) {
                $styles['border'] = $properties['strokeWidth'] . 'px solid ' . $properties['strokeColor'];
            }
            $styles['border-radius'] = '50%';

            break;
        case 'line':
            if (isset($properties['strokeColor'])) {
                $styles['border-top'] = ($properties['strokeWidth'] ?? 1) . 'px solid ' . $properties['strokeColor'];
            }

            break;
        case 'text':
        case 'document_type':
        case 'order_number':
        case 'dynamic-text':
            if (isset($properties['color']) || isset($properties['textColor'])) {
                $color = $properties['color'] ?? $properties['textColor'] ?? '#000000';
                $styles['color'] = $color;
            }
            if (isset($properties['fontSize'])) {
                $styles['font-size'] = $properties['fontSize'] . 'px';
            }
            if (isset($properties['fontFamily'])) {
                $styles['font-family'] = $properties['fontFamily'];
            }
            if (isset($properties['fontWeight'])) {
                $styles['font-weight'] = $properties['fontWeight'];
            }
            if (isset($properties['textAlign'])) {
                $styles['text-align'] = $properties['textAlign'];
            }
            if (isset($properties['backgroundColor'])) {
                $styles['background-color'] = $properties['backgroundColor'];
            }

            break;
        case 'product_table':
        case 'company_info':
        case 'customer_info':
            if (isset($properties['backgroundColor'])) {
                $styles['background-color'] = $properties['backgroundColor'];
            }
            if (isset($properties['borderColor']) && isset($properties['borderWidth'])) {
                $styles['border'] = $properties['borderWidth'] . 'px solid ' . $properties['borderColor'];
            }

            break;
    }

    return $styles;
}

// Convertir array de styles en string CSS
function pdf_builder_styles_to_string($styles)
{

    $css = [];
    foreach ($styles as $property => $value) {
        $css[] = $property . ': ' . $value;
    }
    return implode('; ', $css) . (count($css) > 0 ? ';' : '');
}

/**
 * AJAX - Générer les styles pour les éléments du template
 */
function pdf_builder_ajax_get_element_styles()
{

    try {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Récupérer les éléments depuis le POST
        $elements_json = isset($_POST['elements']) ? wp_unslash($_POST['elements']) : '[]';
        $elements = json_decode($elements_json, true);
        if (!is_array($elements)) {
            wp_send_json_error('Éléments invalides');
        }

        $element_styles = [];
// Générer les styles pour chaque élément
        foreach ($elements as $element) {
            $element_id = $element['id'] ?? '';
            if (empty($element_id)) {
                continue;
            }

            $styles = pdf_builder_get_element_inline_styles($element);
            $element_styles[$element_id] = $styles;
        }

        wp_send_json_success([
            'styles' => $element_styles
        ]);
    } catch (Exception $e) {
        wp_send_json_error('Erreur: ' . $e->getMessage());
    }
}

add_action('wp_ajax_pdf_builder_get_element_styles', 'pdf_builder_ajax_get_element_styles');
