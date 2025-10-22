<?php
/**
 * PDF Builder Pro - TableRenderer
 * Phase 3.3.4 - Renderer spécialisé pour les tableaux
 *
 * Gère le rendu des éléments de tableaux :
 * - product_table : Tableaux de produits WooCommerce avec calculs
 */

namespace PDF_Builder\Renderers;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class TableRenderer {

    /**
     * Types d'éléments supportés par ce renderer
     */
    const SUPPORTED_TYPES = ['product_table'];

    /**
     * Styles CSS par défaut pour les tableaux
     */
    const DEFAULT_STYLES = [
        'border-collapse' => 'collapse',
        'border-spacing' => '0',
        'width' => '100%',
        'font-family' => 'Arial, sans-serif',
        'font-size' => '12px',
        'color' => '#000000'
    ];

    /**
     * Colonnes par défaut pour les tableaux de produits
     */
    const DEFAULT_COLUMNS = [
        'product' => ['label' => 'Produit', 'width' => '40%', 'align' => 'left'],
        'quantity' => ['label' => 'Qté', 'width' => '15%', 'align' => 'center'],
        'price' => ['label' => 'Prix', 'width' => '20%', 'align' => 'right'],
        'total' => ['label' => 'Total', 'width' => '25%', 'align' => 'right']
    ];

    /**
     * Rend un élément tableau
     *
     * @param array $elementData Données de l'élément
     * @param array $context Contexte de rendu (données du provider)
     * @return array Résultat du rendu HTML/CSS
     */
    public function render(array $elementData, array $context = []): array {
        // Validation des données d'entrée
        if (!$this->validateElementData($elementData)) {
            return [
                'html' => '<!-- Erreur: Données élément invalides -->',
                'css' => '',
                'error' => 'Données élément invalides'
            ];
        }

        $type = $elementData['type'] ?? 'product_table';
        $properties = $elementData['properties'] ?? [];

        // Rendu selon le type d'élément
        switch ($type) {
            case 'product_table':
                return $this->renderProductTable($properties, $context);

            default:
                return [
                    'html' => '<!-- Erreur: Type d\'élément non supporté -->',
                    'css' => '',
                    'error' => 'Type d\'élément non supporté: ' . $type
                ];
        }
    }

    /**
     * Rend un tableau de produits WooCommerce
     *
     * @param array $properties Propriétés du tableau
     * @param array $context Données du contexte
     * @return array Résultat du rendu
     */
    private function renderProductTable(array $properties, array $context): array {
        // Récupération des données de produits
        $products = $this->getProductData($context);

        if (empty($products)) {
            return [
                'html' => '<div class="table-placeholder">Aucun produit à afficher</div>',
                'css' => '.table-placeholder { padding: 20px; text-align: center; color: #999; font-style: italic; }',
                'error' => null
            ];
        }

        // Configuration des colonnes
        $columns = $this->getTableColumns($properties);

        // Génération du HTML du tableau
        $html = $this->generateTableHTML($products, $columns, $properties);

        // Génération des styles CSS
        $css = $this->generateTableStyles($properties, $columns);

        return [
            'html' => $html,
            'css' => $css,
            'error' => null
        ];
    }

    /**
     * Valide les données de l'élément
     *
     * @param array $elementData Données à valider
     * @return bool True si valide
     */
    private function validateElementData(array $elementData): bool {
        return isset($elementData['type']) &&
               in_array($elementData['type'], self::SUPPORTED_TYPES);
    }

    /**
     * Récupère les données des produits depuis le contexte
     *
     * @param array $context Données du contexte
     * @return array Liste des produits
     */
    private function getProductData(array $context): array {
        // Récupération des items de commande WooCommerce
        $orderItems = $context['order_items'] ?? [];

        if (empty($orderItems)) {
            return [];
        }

        $products = [];
        foreach ($orderItems as $item) {
            $products[] = [
                'name' => $item['name'] ?? 'Produit inconnu',
                'quantity' => $item['quantity'] ?? 1,
                'price' => $item['price'] ?? 0,
                'total' => $item['total'] ?? 0,
                'sku' => $item['sku'] ?? '',
                'variation' => $item['variation'] ?? []
            ];
        }

        return $products;
    }

    /**
     * Récupère la configuration des colonnes
     *
     * @param array $properties Propriétés du tableau
     * @return array Configuration des colonnes
     */
    private function getTableColumns(array $properties): array {
        $columnsConfig = $properties['columns'] ?? self::DEFAULT_COLUMNS;

        // Validation et normalisation des colonnes
        $columns = [];
        foreach ($columnsConfig as $key => $config) {
            $columns[$key] = [
                'label' => $config['label'] ?? ucfirst($key),
                'width' => $config['width'] ?? 'auto',
                'align' => $config['align'] ?? 'left',
                'visible' => $config['visible'] ?? true
            ];
        }

        return $columns;
    }

    /**
     * Génère le HTML du tableau
     *
     * @param array $products Données des produits
     * @param array $columns Configuration des colonnes
     * @param array $properties Propriétés du tableau
     * @return string HTML généré
     */
    private function generateTableHTML(array $products, array $columns, array $properties): string {
        $html = '<table class="pdf-product-table">';

        // En-tête du tableau
        $html .= '<thead><tr>';
        foreach ($columns as $key => $column) {
            if (!$column['visible']) continue;

            $style = sprintf('width: %s; text-align: %s;',
                $column['width'],
                $column['align']
            );

            $html .= sprintf('<th style="%s">%s</th>',
                htmlspecialchars($style),
                htmlspecialchars($column['label'])
            );
        }
        $html .= '</tr></thead>';

        // Corps du tableau
        $html .= '<tbody>';
        foreach ($products as $product) {
            $html .= '<tr>';
            foreach ($columns as $key => $column) {
                if (!$column['visible']) continue;

                $value = $this->formatCellValue($key, $product, $properties);
                $style = sprintf('text-align: %s;', $column['align']);

                $html .= sprintf('<td style="%s">%s</td>',
                    htmlspecialchars($style),
                    $value
                );
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        // Pied du tableau avec totaux
        $totals = $this->calculateTotals($products, $properties);
        if (!empty($totals)) {
            $html .= '<tfoot>';
            foreach ($totals as $totalRow) {
                $html .= '<tr class="table-total-row">';
                foreach ($columns as $key => $column) {
                    if (!$column['visible']) continue;

                    $value = $totalRow[$key] ?? '';
                    $style = sprintf('text-align: %s; font-weight: bold;', $column['align']);

                    $html .= sprintf('<td style="%s">%s</td>',
                        htmlspecialchars($style),
                        $value
                    );
                }
                $html .= '</tr>';
            }
            $html .= '</tfoot>';
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Formate la valeur d'une cellule selon son type
     *
     * @param string $columnKey Clé de la colonne
     * @param array $product Données du produit
     * @param array $properties Propriétés du tableau
     * @return string Valeur formatée
     */
    private function formatCellValue(string $columnKey, array $product, array $properties): string {
        switch ($columnKey) {
            case 'product':
                $name = htmlspecialchars($product['name']);
                if (!empty($product['sku'])) {
                    $name .= '<br><small style="color: #666;">SKU: ' . htmlspecialchars($product['sku']) . '</small>';
                }
                return $name;

            case 'quantity':
                return (int)$product['quantity'];

            case 'price':
                return $this->formatCurrency($product['price'], $properties);

            case 'total':
                return $this->formatCurrency($product['total'], $properties);

            default:
                return htmlspecialchars($product[$columnKey] ?? '');
        }
    }

    /**
     * Formate une valeur monétaire
     *
     * @param float $amount Montant
     * @param array $properties Propriétés du tableau
     * @return string Montant formaté
     */
    private function formatCurrency(float $amount, array $properties): string {
        $currency = $properties['currency'] ?? 'EUR';
        $locale = $properties['locale'] ?? 'fr_FR';

        // Formatage simple pour l'instant
        return number_format($amount, 2, ',', ' ') . ' ' . $currency;
    }

    /**
     * Calcule les totaux du tableau
     *
     * @param array $products Données des produits
     * @param array $properties Propriétés du tableau
     * @return array Lignes de totaux
     */
    private function calculateTotals(array $products, array $properties): array {
        $showSubtotal = $properties['show_subtotal'] ?? true;
        $showTax = $properties['show_tax'] ?? true;
        $showTotal = $properties['show_total'] ?? true;

        $totals = [];

        if ($showSubtotal) {
            $subtotal = array_sum(array_column($products, 'total'));
            $totals[] = [
                'product' => 'Sous-total',
                'quantity' => '',
                'price' => '',
                'total' => $this->formatCurrency($subtotal, $properties)
            ];
        }

        // Calcul de la TVA si demandé
        if ($showTax) {
            $taxRate = $properties['tax_rate'] ?? 20.0; // 20% par défaut
            $subtotal = array_sum(array_column($products, 'total'));
            $taxAmount = $subtotal * ($taxRate / 100);

            $totals[] = [
                'product' => sprintf('TVA (%s%%)', $taxRate),
                'quantity' => '',
                'price' => '',
                'total' => $this->formatCurrency($taxAmount, $properties)
            ];
        }

        if ($showTotal) {
            $total = array_sum(array_column($products, 'total'));
            if ($showTax) {
                $taxRate = $properties['tax_rate'] ?? 20.0;
                $total += $total * ($taxRate / 100);
            }

            $totals[] = [
                'product' => 'Total',
                'quantity' => '',
                'price' => '',
                'total' => $this->formatCurrency($total, $properties)
            ];
        }

        return $totals;
    }

    /**
     * Génère les styles CSS du tableau
     *
     * @param array $properties Propriétés du tableau
     * @param array $columns Configuration des colonnes
     * @return string CSS généré
     */
    private function generateTableStyles(array $properties, array $columns): string {
        $css = [];

        // Styles de base du tableau
        $css[] = '.pdf-product-table {';
        $css[] = '  border-collapse: collapse;';
        $css[] = '  width: 100%;';
        $css[] = '  margin: 10px 0;';
        $css[] = '}';

        // Styles des cellules
        $css[] = '.pdf-product-table th, .pdf-product-table td {';
        $css[] = '  padding: 8px 12px;';
        $css[] = '  border: 1px solid #ddd;';
        $css[] = '  vertical-align: top;';
        $css[] = '}';

        // Styles de l'en-tête
        $css[] = '.pdf-product-table th {';
        $css[] = '  background-color: #f5f5f5;';
        $css[] = '  font-weight: bold;';
        $css[] = '  text-transform: uppercase;';
        $css[] = '  font-size: 11px;';
        $css[] = '}';

        // Styles des lignes alternées
        if ($properties['alternate_rows'] ?? false) {
            $css[] = '.pdf-product-table tbody tr:nth-child(even) {';
            $css[] = '  background-color: #f9f9f9;';
            $css[] = '}';
        }

        // Styles des lignes de total
        $css[] = '.pdf-product-table .table-total-row {';
        $css[] = '  background-color: #e8f4f8;';
        $css[] = '  border-top: 2px solid #007cba;';
        $css[] = '}';

        // Styles des lignes de total td
        $css[] = '.pdf-product-table .table-total-row td {';
        $css[] = '  font-weight: bold;';
        $css[] = '  padding: 10px 12px;';
        $css[] = '}';

        return implode("\n", $css);
    }
}