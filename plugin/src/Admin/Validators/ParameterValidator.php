<?php

/**
 * PDF Builder Pro - Parameter Validator
 * Responsable de la validation des paramètres
 */

namespace PDF_Builder\Admin\Validators;

/**
 * Classe responsable de la validation des paramètres POST
 */
class ParameterValidator
{
    /**
     * Filtre et valide les paramètres du canvas depuis POST
     *
     * @param array $post_data Données POST à valider
     * @return array Paramètres validés et filtrés
     */
    public function filterCanvasParameters($post_data)
    {
        if (!is_array($post_data)) {
            return [];
        }

        $filtered = [];

        // Paramètres numériques entiers
        $int_params = ['canvas_width', 'canvas_height', 'grid_size', 'zoom_level'];
        foreach ($int_params as $param) {
            if (isset($post_data[$param])) {
                $filtered[$param] = (int) $post_data[$param];
            }
        }

        // Paramètres booléens
        $bool_params = ['show_grid', 'snap_to_grid', 'snap_to_elements'];
        foreach ($bool_params as $param) {
            if (isset($post_data[$param])) {
                $filtered[$param] = (bool) $post_data[$param];
            }
        }

        // Paramètres texte (sécurisés)
        $text_params = ['template_name', 'background_color'];
        foreach ($text_params as $param) {
            if (isset($post_data[$param])) {
                $filtered[$param] = sanitize_text_field($post_data[$param]);
            }
        }

        // Paramètres JSON (validés)
        $json_params = ['elements', 'pages'];
        foreach ($json_params as $param) {
            if (isset($post_data[$param])) {
                $decoded = json_decode($post_data[$param], true);
                if (is_array($decoded)) {
                    $filtered[$param] = $decoded;
                }
            }
        }

        return $filtered;
    }

    /**
     * Valide et nettoie les propriétés d'un élément
     *
     * @param array $element Élément à valider
     * @return array Élément validé
     */
    public function validateElement($element)
    {
        if (!is_array($element)) {
            return [];
        }

        $validated = [];

        // ID de l'élément
        if (isset($element['id'])) {
            $validated['id'] = sanitize_key($element['id']);
        }

        // Type d'élément
        if (isset($element['type'])) {
            $validated['type'] = sanitize_key($element['type']);
        }

        // Position et taille
        foreach (['x', 'y', 'width', 'height'] as $dim) {
            if (isset($element[$dim])) {
                $validated[$dim] = (int) $element[$dim];
            }
        }

        // Contenu texte
        if (isset($element['content'])) {
            $validated['content'] = wp_kses_post($element['content']);
        }

        // Propriétés de style
        if (isset($element['style']) && is_array($element['style'])) {
            $validated['style'] = $this->validateStyles($element['style']);
        }

        // Propriétés supplémentaires valides
        $valid_props = ['visible', 'locked', 'zIndex', 'rotation', 'opacity'];
        foreach ($valid_props as $prop) {
            if (isset($element[$prop])) {
                if ($prop === 'zIndex') {
                    $validated[$prop] = (int) $element[$prop];
                } elseif ($prop === 'visible' || $prop === 'locked') {
                    $validated[$prop] = (bool) $element[$prop];
                } else {
                    $validated[$prop] = (float) $element[$prop];
                }
            }
        }

        return $validated;
    }

    /**
     * Valide les propriétés de style
     *
     * @param array $styles Propriétés de style
     * @return array Styles validés
     */
    private function validateStyles($styles)
    {
        $validated = [];

        $allowed_styles = [
            'color', 'fontSize', 'fontFamily', 'fontWeight', 'fontStyle',
            'textAlign', 'backgroundColor', 'padding', 'margin', 'borderWidth',
            'borderColor', 'borderRadius', 'opacity', 'zIndex'
        ];

        foreach ($allowed_styles as $style) {
            if (isset($styles[$style])) {
                $validated[$style] = sanitize_text_field($styles[$style]);
            }
        }

        return $validated;
    }
}

