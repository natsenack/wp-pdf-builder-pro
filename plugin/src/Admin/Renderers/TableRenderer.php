<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags

/**
 * PDF Builder Pro - Table Renderer
 * Responsable du rendu HTML des tableaux de produits
 */

namespace PDF_Builder\Admin\Renderers;

/**
 * Classe responsable du rendu des tableaux de produits/commandes
 */
class TableRenderer
{
    /**
     * Rend le HTML d'un tableau de produits pour une commande
     *
     * @param object $order Objet WooCommerce Order
     * @param array $element Données de l'élément tableau
     * @param string|null $text_color Couleur du texte (optionnel)
     * @param string|null $font_family Famille de police (optionnel)
     * @param string|null $font_size Taille de police (optionnel)
     * @return string HTML du tableau
     */
    public function renderProductTableHtml($order, $element, $text_color = null, $font_family = null, $font_size = null)
    {
        if (!$order) {
            return '<div style="padding: 10px; color: #999; font-size: 12px;">Aucune commande</div>';
        }

        // Extraire les propriétés CSS de l'élément
        $text_color = $text_color ?? ($element['color'] ?? '#000');
        $font_family = $font_family ?? ($element['fontFamily'] ?? 'Arial');
        $font_size = $font_size ?? ($element['fontSize'] ?? 10);
        $font_weight = $element['fontWeight'] ?? 'normal';
        $text_align = $element['textAlign'] ?? 'left';
        $border_style = $element['borderStyle'] ?? 'solid';

        // Propriétés spécifiques au tableau
        $table_style = $element['tableStyle'] ?? 'default';
        $show_headers = isset($element['showHeaders']) ? (bool) $element['showHeaders'] : true;
        $show_borders = isset($element['showBorders']) ? (bool) $element['showBorders'] : true;
        $border_width = max(0, ($element['borderWidth'] ?? 0));

        // Extraire les colonnes à afficher
        $columns = $element['columns'] ?? ['name' => true, 'quantity' => true, 'price' => true, 'total' => true];
        if (is_array($columns) && !isset($columns['name'])) {
            $columns = array_fill_keys($columns, true);
        }

        // Filtrer pour ne garder que les colonnes visibles
        $visible_columns = is_array($columns) ? array_filter($columns, function ($v) {
            return (bool) $v;
        }) : [];

        // Récupérer les headers
        $headers = $element['headers'] ?? [];
        $default_headers = [
            'image' => 'Image',
            'name' => 'Produit',
            'sku' => 'SKU',
            'quantity' => 'Qté',
            'price' => 'Prix',
            'total' => 'Total'
        ];

        // Styles de tableau prédéfinis
        $table_styles = $this->getTableStyles();
        $style_config = $table_styles[$table_style] ?? $table_styles['default'];

        // Construire le HTML du tableau
        $html = '<table style="width: 100%; height: 100%; border-collapse: collapse; font-family: ' . esc_attr($font_family) . '; font-size: ' . esc_attr($font_size) . 'px; color: ' . esc_attr($text_color) . '; font-weight: ' . esc_attr($font_weight) . '; text-align: ' . esc_attr($text_align) . ';">';

        // En-têtes
        if ($show_headers) {
            $html .= $this->renderTableHeader($visible_columns, $headers, $default_headers, $style_config, $show_borders, $border_width, $border_style, $text_align);
        }

        // Lignes produits
        $html .= '<tbody>';
        $line_items = $order->get_items();
        $row_index = 0;

        foreach ($line_items as $item) {
            $html .= $this->renderTableRow($item, $visible_columns, $style_config, $row_index, $show_borders, $border_width, $border_style);
            $row_index++;
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Retourne les styles prédéfinis de tableaux
     *
     * @return array Styles disponibles
     */
    private function getTableStyles()
    {
        return [
            'default' => [
                'header_bg' => '#f8fafc',
                'header_color' => '#334155',
                'header_border' => '#e2e8f0',
                'row_bg' => 'transparent',
                'row_border' => '#e2e8f0',
                'alt_row_bg' => '#fafbfc',
            ],
            'classic' => [
                'header_bg' => '#1e293b',
                'header_color' => '#ffffff',
                'header_border' => '#334155',
                'row_bg' => 'transparent',
                'row_border' => '#334155',
                'alt_row_bg' => '#ffffff',
            ],
            'modern' => [
                'header_bg' => '#3b82f6',
                'header_color' => '#ffffff',
                'header_border' => '#2563eb',
                'row_bg' => 'transparent',
                'row_border' => '#e2e8f0',
                'alt_row_bg' => '#f8fafc',
            ],
            'minimal' => [
                'header_bg' => '#ffffff',
                'header_color' => '#6b7280',
                'header_border' => '#d1d5db',
                'row_bg' => 'transparent',
                'row_border' => '#f3f4f6',
                'alt_row_bg' => '#ffffff',
            ]
        ];
    }

    /**
     * Rend l'en-tête du tableau
     *
     * @param array $visible_columns Colonnes visibles
     * @param array $headers En-têtes personnalisés
     * @param array $default_headers En-têtes par défaut
     * @param array $style_config Configuration de style
     * @param bool $show_borders Afficher les bordures
     * @param int $border_width Largeur de la bordure
     * @param string $border_style Style de la bordure
     * @param string $text_align Alignement du texte
     * @return string HTML de l'en-tête
     */
    private function renderTableHeader($visible_columns, $headers, $default_headers, $style_config, $show_borders, $border_width, $border_style, $text_align)
    {
        $header_bg = $style_config['header_bg'];
        $header_color = $style_config['header_color'];
        $header_border = $style_config['header_border'];
        $border_display = $show_borders ? "border-bottom: {$border_width}px {$border_style} {$header_border};" : '';

        $html = '<thead><tr style="background-color: ' . esc_attr($header_bg) . '; color: ' . esc_attr($header_color) . '; ' . $border_display . '">';

        foreach ($visible_columns as $col_key => $col_value) {
            $col_header = $headers[$col_key] ?? $default_headers[$col_key] ?? ucfirst($col_key);
            $html .= '<th style="padding: 3px 4px; text-align: ' . esc_attr($text_align) . '; font-weight: 700; word-break: break-word;">' . esc_html($col_header) . '</th>';
        }

        $html .= '</tr></thead>';

        return $html;
    }

    /**
     * Rend une ligne du tableau
     *
     * @param object $item Élément WooCommerce
     * @param array $visible_columns Colonnes visibles
     * @param array $style_config Configuration de style
     * @param int $row_index Index de la ligne
     * @param bool $show_borders Afficher les bordures
     * @param int $border_width Largeur de la bordure
     * @param string $border_style Style de la bordure
     * @return string HTML de la ligne
     */
    private function renderTableRow($item, $visible_columns, $style_config, $row_index, $show_borders, $border_width, $border_style)
    {
        $row_bg = ($row_index % 2 === 0) ? $style_config['row_bg'] : $style_config['alt_row_bg'];
        $row_border = $style_config['row_border'];
        $border_display = $show_borders ? "border-bottom: {$border_width}px {$border_style} {$row_border};" : '';

        $html = '<tr style="background-color: ' . esc_attr($row_bg) . '; ' . $border_display . '">';

        foreach ($visible_columns as $col_key => $col_value) {
            $cell_content = $this->getCellContent($item, $col_key);
            $html .= '<td style="padding: 3px 4px; word-break: break-word;">' . $cell_content . '</td>';
        }

        $html .= '</tr>';

        return $html;
    }

    /**
     * Retourne le contenu d'une cellule
     *
     * @param object $item Élément WooCommerce
     * @param string $column_key Clé de la colonne
     * @return string Contenu de la cellule
     */
    private function getCellContent($item, $column_key)
    {
        switch ($column_key) {
            case 'image':
                $product = $item->get_product();
                $image_url = get_the_post_thumbnail_url($product->get_id(), 'thumbnail');
                return $image_url ? '<img src="' . esc_attr($image_url) . '" style="max-width: 50px; height: auto;" />' : '—';

            case 'name':
                return esc_html($item->get_name());

            case 'sku':
                $product = $item->get_product();
                return $product ? esc_html($product->get_sku()) : '—';

            case 'quantity':
                return (int) $item->get_quantity();

            case 'price':
                return wc_price($item->get_subtotal());

            case 'total':
                return wc_price($item->get_total());

            default:
                return '—';
        }
    }
}



