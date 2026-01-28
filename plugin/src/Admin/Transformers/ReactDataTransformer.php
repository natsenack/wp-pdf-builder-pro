<?php

/**
 * PDF Builder Pro - React Data Transformer
 * Responsable de la transformation des éléments pour React
 */

namespace PDF_Builder\Admin\Transformers;

/**
 * Classe responsable de la transformation des données d'éléments pour l'éditeur React
 */
class ReactDataTransformer
{
    /**
     * Transforme les éléments pour l'éditeur React
     * Standardise les positions, dimensions, et ajoute les logos manquants
     *
     * @param array $elements Éléments à transformer
     * @return array Éléments transformés pour React
     */
    public function transformElementsForReact($elements)
    {
        if (!is_array($elements)) {
            return [];
        }

        $transformed_elements = [];
        foreach ($elements as $element) {
            // Commencer par une copie COMPLÈTE de l'élément
            // Cela préserve TOUTES les propriétés personnalisées
            $transformed_element = $element;

            // Gestion spéciale pour les éléments company_logo
            if (isset($element['type']) && $element['type'] === 'company_logo') {
                $logo_url = $this->resolveCompanyLogo($element);
                if ($logo_url) {
                    $transformed_element['logoUrl'] = $logo_url;
                }
            }

            // Valider et standardiser les positions et dimensions
            $transformed_element = $this->standardizePositionAndSize($transformed_element, $element);

            // Ajouter les propriétés par défaut
            if (!isset($transformed_element['visible'])) {
                $transformed_element['visible'] = true;
            }
            if (!isset($transformed_element['locked'])) {
                $transformed_element['locked'] = false;
            }

            $transformed_elements[] = $transformed_element;
        }

        return $transformed_elements;
    }

    /**
     * Résout l'URL du logo de l'entreprise
     *
     * @param array $element Élément courant
     * @return string|null URL du logo ou null
     */
    private function resolveCompanyLogo($element)
    {
        // Vérifier l'URL d'image directe
        if (isset($element['imageUrl'])) {
            return $element['imageUrl'];
        }

        if (isset($element['content'])) {
            return $element['content'];
        }

        // Vérifier le logo personnalisé du thème
        $custom_logo_id = \get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_url = \wp_get_attachment_image_url($custom_logo_id, 'full');
            if ($logo_url) {
                return $logo_url;
            }
        }

        // Vérifier le logo du site WordPress
        $site_logo_id = get_option('site_logo');
        if ($site_logo_id) {
            $logo_url = wp_get_attachment_image_url($site_logo_id, 'full');
            if ($logo_url) {
                return $logo_url;
            }
        }

        return null;
    }

    /**
     * Standardise les positions et dimensions d'un élément
     *
     * @param array $transformed_element Élément transformé
     * @param array $element Élément original
     * @return array Élément avec positions/dimensions standardisées
     */
    private function standardizePositionAndSize($transformed_element, $element)
    {
        // Position X
        if (!isset($transformed_element['x'])) {
            $transformed_element['x'] = (int) ($element['position']['x'] ?? 0);
        } else {
            $transformed_element['x'] = (int) $transformed_element['x'];
        }

        // Position Y
        if (!isset($transformed_element['y'])) {
            $transformed_element['y'] = (int) ($element['position']['y'] ?? 0);
        } else {
            $transformed_element['y'] = (int) $transformed_element['y'];
        }

        // Largeur
        if (!isset($transformed_element['width'])) {
            $transformed_element['width'] = (int) ($element['size']['width'] ?? 100);
        } else {
            $transformed_element['width'] = (int) $transformed_element['width'];
        }

        // Hauteur
        if (!isset($transformed_element['height'])) {
            $transformed_element['height'] = (int) ($element['size']['height'] ?? 50);
        } else {
            $transformed_element['height'] = (int) $transformed_element['height'];
        }

        return $transformed_element;
    }
}

