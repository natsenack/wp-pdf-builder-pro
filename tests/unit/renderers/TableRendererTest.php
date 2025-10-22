<?php
/**
 * Tests pour TableRenderer
 *
 * @package PDF_Builder_Pro
 * @subpackage Tests
 */

use PHPUnit\Framework\TestCase;
use PDF_Builder\Renderers\TableRenderer;

class TableRendererTest extends TestCase
{
    private TableRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new TableRenderer();
    }

    /**
     * Test que les types supportés sont corrects
     */
    public function testSupportedTypes()
    {
        $this->assertEquals(['product_table'], TableRenderer::SUPPORTED_TYPES);
    }

    /**
     * Test rendu avec données valides
     */
    public function testRenderValidProductTable()
    {
        $elementData = [
            'type' => 'product_table',
            'properties' => [
                'show_subtotal' => true,
                'show_tax' => true,
                'show_total' => true,
                'tax_rate' => 20.0,
                'currency' => 'EUR'
            ]
        ];

        $context = [
            'order_items' => [
                [
                    'name' => 'Produit Test 1',
                    'quantity' => 2,
                    'price' => 10.50,
                    'total' => 21.00,
                    'sku' => 'TEST001'
                ],
                [
                    'name' => 'Produit Test 2',
                    'quantity' => 1,
                    'price' => 15.75,
                    'total' => 15.75,
                    'sku' => 'TEST002'
                ]
            ]
        ];

        $result = $this->renderer->render($elementData, $context);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('css', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertNull($result['error']);

        // Vérifier que le HTML contient un tableau
        $this->assertStringContains('table', $result['html']);
        $this->assertStringContains('pdf-product-table', $result['html']);
        $this->assertStringContains('Produit Test 1', $result['html']);
        $this->assertStringContains('Produit Test 2', $result['html']);

        // Vérifier que le CSS est généré
        $this->assertStringContains('.pdf-product-table', $result['css']);
    }

    /**
     * Test rendu avec données vides
     */
    public function testRenderEmptyProductTable()
    {
        $elementData = [
            'type' => 'product_table',
            'properties' => []
        ];

        $context = [
            'order_items' => []
        ];

        $result = $this->renderer->render($elementData, $context);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('css', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertNull($result['error']);

        // Vérifier le message d'absence de produits
        $this->assertStringContains('Aucun produit à afficher', $result['html']);
        $this->assertStringContains('table-placeholder', $result['html']);
    }

    /**
     * Test rendu avec type non supporté
     */
    public function testRenderUnsupportedType()
    {
        $elementData = [
            'type' => 'unsupported_table_type',
            'properties' => []
        ];

        $context = [];

        $result = $this->renderer->render($elementData, $context);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('css', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContains('Type d\'élément non supporté', $result['error']);
    }

    /**
     * Test validation des données d'élément
     */
    public function testValidateElementData()
    {
        $reflection = new ReflectionClass(TableRenderer::class);
        $method = $reflection->getMethod('validateElementData');
        $method->setAccessible(true);

        // Test données valides
        $validData = ['type' => 'product_table'];
        $this->assertTrue($method->invoke($this->renderer, $validData));

        // Test données invalides
        $invalidData = ['type' => 'invalid_type'];
        $this->assertFalse($method->invoke($this->renderer, $invalidData));

        // Test données sans type
        $noTypeData = ['properties' => []];
        $this->assertFalse($method->invoke($this->renderer, $noTypeData));
    }

    /**
     * Test récupération des données de produits
     */
    public function testGetProductData()
    {
        $reflection = new ReflectionClass(TableRenderer::class);
        $method = $reflection->getMethod('getProductData');
        $method->setAccessible(true);

        $context = [
            'order_items' => [
                [
                    'name' => 'Test Product',
                    'quantity' => 3,
                    'price' => 25.99,
                    'total' => 77.97,
                    'sku' => 'TP001'
                ]
            ]
        ];

        $products = $method->invoke($this->renderer, $context);

        $this->assertIsArray($products);
        $this->assertCount(1, $products);
        $this->assertEquals('Test Product', $products[0]['name']);
        $this->assertEquals(3, $products[0]['quantity']);
        $this->assertEquals(25.99, $products[0]['price']);
        $this->assertEquals(77.97, $products[0]['total']);
        $this->assertEquals('TP001', $products[0]['sku']);
    }

    /**
     * Test calcul des totaux
     */
    public function testCalculateTotals()
    {
        $reflection = new ReflectionClass(TableRenderer::class);
        $method = $reflection->getMethod('calculateTotals');
        $method->setAccessible(true);

        $products = [
            ['total' => 50.00],
            ['total' => 30.00]
        ];

        $properties = [
            'show_subtotal' => true,
            'show_tax' => true,
            'show_total' => true,
            'tax_rate' => 20.0,
            'currency' => 'EUR'
        ];

        $totals = $method->invoke($this->renderer, $products, $properties);

        $this->assertIsArray($totals);
        $this->assertCount(3, $totals); // Sous-total, TVA, Total

        // Vérifier sous-total
        $this->assertEquals('Sous-total', $totals[0]['product']);
        $this->assertStringContains('80,00', $totals[0]['total']);

        // Vérifier TVA
        $this->assertStringContains('TVA (20%)', $totals[1]['product']);
        $this->assertStringContains('16,00', $totals[1]['total']);

        // Vérifier total
        $this->assertEquals('Total', $totals[2]['product']);
        $this->assertStringContains('96,00', $totals[2]['total']);
    }

    /**
     * Test formatage des valeurs de cellules
     */
    public function testFormatCellValue()
    {
        $reflection = new ReflectionClass(TableRenderer::class);
        $method = $reflection->getMethod('formatCellValue');
        $method->setAccessible(true);

        $properties = ['currency' => 'EUR'];

        // Test colonne produit
        $product = ['name' => 'Test Product', 'sku' => 'TP001'];
        $result = $method->invoke($this->renderer, 'product', $product, $properties);
        $this->assertStringContains('Test Product', $result);
        $this->assertStringContains('TP001', $result);

        // Test colonne quantité
        $result = $method->invoke($this->renderer, 'quantity', ['quantity' => 5], $properties);
        $this->assertEquals(5, $result);

        // Test colonne prix
        $result = $method->invoke($this->renderer, 'price', ['price' => 29.99], $properties);
        $this->assertStringContains('29,99', $result);
        $this->assertStringContains('EUR', $result);
    }

    /**
     * Test génération des styles CSS
     */
    public function testGenerateTableStyles()
    {
        $reflection = new ReflectionClass(TableRenderer::class);
        $method = $reflection->getMethod('generateTableStyles');
        $method->setAccessible(true);

        $properties = ['alternate_rows' => true];
        $columns = TableRenderer::DEFAULT_COLUMNS;

        $css = $method->invoke($this->renderer, $properties, $columns);

        $this->assertIsString($css);
        $this->assertStringContains('.pdf-product-table', $css);
        $this->assertStringContains('border-collapse: collapse', $css);
        $this->assertStringContains('nth-child(even)', $css); // Lignes alternées
        $this->assertStringContains('.table-total-row', $css);
    }
}