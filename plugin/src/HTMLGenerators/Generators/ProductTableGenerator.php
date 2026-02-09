<?php
/**
 * Product table element generator
 */

namespace PDF_Builder\HTMLGenerators\Generators;

use PDF_Builder\HTMLGenerators\ElementGeneratorBase;

class ProductTableGenerator extends ElementGeneratorBase
{
    public function generateHTML()
    {
        $styles = $this->getElementStyles();
        $styleAttr = $this->createStyleString($styles);

        $html = '<div class="pdf-element pdf-product-table" ' . $styleAttr . '>';
        
        // Get columns configuration
        $columns = $this->getProperty('columns', [
            ['label' => 'Produit', 'key' => 'product_name'],
            ['label' => 'Quantité', 'key' => 'quantity'],
            ['label' => 'Prix', 'key' => 'price'],
            ['label' => 'Total', 'key' => 'total'],
        ]);

        // Table header
        $html .= '<table style="width:100%; border-collapse:collapse;">';
        $html .= '<thead>';
        $html .= '<tr style="border-bottom:1px solid #ddd;">';
        
        foreach ($columns as $column) {
            $html .= '<th style="padding:8px; text-align:left; font-weight:bold;">';
            $html .= htmlspecialchars($column['label'] ?? '');
            $html .= '</th>';
        }
        
        $html .= '</tr>';
        $html .= '</thead>';

        // Table body (populated from orderData or test data)
        $html .= '<tbody>';
        
        $items = $this->orderData['items'] ?? [];
        if (empty($items)) {
            // Test data
            $html .= '<tr style="border-bottom:1px solid #eee;">';
            foreach ($columns as $column) {
                $html .= '<td style="padding:8px;">-</td>';
            }
            $html .= '</tr>';
        } else {
            foreach ($items as $item) {
                $html .= '<tr style="border-bottom:1px solid #eee;">';
                foreach ($columns as $column) {
                    $key = $column['key'] ?? '';
                    $value = $item[$key] ?? '-';
                    $html .= '<td style="padding:8px;">' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
