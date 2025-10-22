<?php

use PHPUnit\Framework\TestCase;

/**
 * Test de validation des éléments (Phase 2.1.1)
 * Teste le rendu serveur des éléments, particulièrement product_table
 */
class ElementValidationTest extends TestCase
{
    /**
     * Test du rendu HTML du product_table avec données fictives
     */
    public function testProductTableHtmlRendering()
    {
        // Simuler une commande WooCommerce fictive
        $mockOrder = $this->createMockOrderData();

        // Configuration de l'élément product_table
        $element = [
            'type' => 'product_table',
            'showHeaders' => true,
            'showBorders' => true,
            'headers' => ['Produit', 'Qté', 'Prix'],
            'columns' => [
                'image' => true,
                'name' => true,
                'sku' => false,
                'quantity' => true,
                'price' => true,
                'total' => true
            ],
            'showSubtotal' => true,
            'showShipping' => true,
            'showTaxes' => true,
            'showDiscount' => false,
            'showTotal' => true,
            'tableStyle' => 'default'
        ];

        // Inclure la classe PDF_Builder_Admin pour accéder à render_product_table_html
        require_once __DIR__ . '/../../src/Admin/PDF_Builder_Admin.php';

        // Créer une instance de test
        $admin = new PDF_Builder_Admin();

        // Appeler la méthode de rendu (on utilise la réflexion pour accéder à la méthode privée)
        $reflection = new ReflectionClass($admin);
        $method = $reflection->getMethod('render_product_table_html');
        $method->setAccessible(true);

        // Rendre le HTML
        $html = $method->invokeArgs($admin, [$mockOrder, $element, '#000000', 'Arial', 12]);

        // Assertions de validation
        $this->assertTrue(is_string($html));
        $this->assertNotEmpty($html);

        // Vérifier que le HTML contient les éléments attendus
        $this->assertContains('table', $html);
        $this->assertContains('thead', $html);
        $this->assertContains('tbody', $html);
        $this->assertContains('Produit', $html);
        $this->assertContains('Qté', $html);
        $this->assertContains('Prix', $html);

        // Vérifier que les données fictives sont présentes
        $this->assertContains('Test Product 1', $html);
        $this->assertContains('Test Product 2', $html);
        $this->assertContains('€50.00', $html);
        $this->assertContains('€25.00', $html);
    }

    /**
     * Test de validation des propriétés d'éléments
     */
    public function testElementPropertyValidation()
    {
        // Inclure les utilitaires de validation
        require_once __DIR__ . '/../../src/utilities/elementPropertyRestrictions.php';

        // Tester les restrictions pour product_table
        $this->assertTrue(isPropertyAllowed('product_table', 'backgroundColor'));
        $this->assertTrue(isPropertyAllowed('product_table', 'borderWidth'));

        // Tester la validation de propriétés
        $validResult = validateProperty('product_table', 'borderWidth', 2);
        $this->assertTrue($validResult['valid']);

        $invalidResult = validateProperty('product_table', 'borderWidth', -1);
        $this->assertFalse($invalidResult['valid']);
        $this->assertEquals('La largeur de bordure doit être un nombre positif', $invalidResult['reason']);
    }

    /**
     * Test de l'existence des 7 types d'éléments dans les mappings
     */
    public function testElementTypesMapping()
    {
        require_once __DIR__ . '/../../src/utilities/elementPropertyRestrictions.php';

        $expectedTypes = [
            'product_table',
            'customer_info',
            'company_logo',
            'company_info',
            'order_number',
            'dynamic-text',
            'mentions'
        ];

        foreach ($expectedTypes as $type) {
            $this->assertArrayHasKey($type, ELEMENT_TYPE_MAPPING);
            $this->assertContains(ELEMENT_TYPE_MAPPING[$type], ['special', 'text', 'media', 'layout']);
        }
    }

    /**
     * Crée des données fictives pour simuler une commande WooCommerce
     */
    private function createMockOrderData()
    {
        return [
            'id' => 123,
            'order_number' => 'TEST-123',
            'date_created' => '2025-10-22',
            'total' => '75.00',
            'subtotal' => '75.00',
            'shipping_total' => '0.00',
            'tax_total' => '0.00',
            'discount_total' => '0.00',
            'currency' => 'EUR',
            'line_items' => [
                [
                    'id' => 1,
                    'name' => 'Test Product 1',
                    'product_id' => 101,
                    'variation_id' => 0,
                    'quantity' => 1,
                    'price' => 50.00,
                    'subtotal' => 50.00,
                    'total' => 50.00,
                    'sku' => 'TEST-001',
                    'image' => [
                        'id' => 201,
                        'src' => 'https://example.com/image1.jpg'
                    ]
                ],
                [
                    'id' => 2,
                    'name' => 'Test Product 2',
                    'product_id' => 102,
                    'variation_id' => 0,
                    'quantity' => 2,
                    'price' => 12.50,
                    'subtotal' => 25.00,
                    'total' => 25.00,
                    'sku' => 'TEST-002',
                    'image' => [
                        'id' => 202,
                        'src' => 'https://example.com/image2.jpg'
                    ]
                ]
            ],
            'billing' => [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'company' => 'Test Company',
                'address_1' => '123 Test Street',
                'address_2' => '',
                'city' => 'Test City',
                'state' => 'Test State',
                'postcode' => '12345',
                'country' => 'FR',
                'email' => 'jean.dupont@test.com',
                'phone' => '+33123456789'
            ],
            'shipping' => [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'company' => 'Test Company',
                'address_1' => '123 Test Street',
                'address_2' => '',
                'city' => 'Test City',
                'state' => 'Test State',
                'postcode' => '12345',
                'country' => 'FR'
            ]
        ];
    }
}