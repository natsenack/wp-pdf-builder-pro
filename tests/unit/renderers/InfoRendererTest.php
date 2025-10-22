<?php
/**
 * Tests pour InfoRenderer
 *
 * @package PDF_Builder_Pro
 * @subpackage Tests
 */

use PHPUnit\Framework\TestCase;
use PDF_Builder\Renderers\InfoRenderer;

class InfoRendererTest extends TestCase
{
    private InfoRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new InfoRenderer();
    }

    /**
     * Test que les types supportés sont corrects
     */
    public function testSupportedTypes()
    {
        $this->assertEquals(['customer_info', 'company_info', 'mentions'], InfoRenderer::SUPPORTED_TYPES);
    }

    /**
     * Test rendu customer_info avec données valides
     */
    public function testRenderValidCustomerInfo()
    {
        $elementData = [
            'type' => 'customer_info',
            'properties' => [
                'layout' => 'vertical',
                'show_labels' => true,
                'fields' => ['first_name', 'last_name', 'email']
            ]
        ];

        $context = [
            'customer' => [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@email.com',
                'phone' => '0123456789'
            ]
        ];

        $result = $this->renderer->render($elementData, $context);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('css', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertNull($result['error']);

        // Vérifier que le HTML contient les informations client
        $this->assertStringContains('customer-info', $result['html']);
        $this->assertStringContains('Jean', $result['html']);
        $this->assertStringContains('Dupont', $result['html']);
        $this->assertStringContains('jean.dupont@email.com', $result['html']);

        // Vérifier que le CSS est généré
        $this->assertStringContains('.customer-info', $result['css']);
    }

    /**
     * Test rendu company_info avec template default
     */
    public function testRenderCompanyInfoDefaultTemplate()
    {
        $elementData = [
            'type' => 'company_info',
            'properties' => [
                'template' => 'default'
            ]
        ];

        $context = [
            'company' => [
                'name' => 'Ma Société SARL',
                'address' => '123 Rue de la Paix, 75001 Paris',
                'phone' => '0145678901',
                'email' => 'contact@masociete.com'
            ]
        ];

        $result = $this->renderer->render($elementData, $context);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('css', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertNull($result['error']);

        // Vérifier que le HTML contient les informations société
        $this->assertStringContains('company-info', $result['html']);
        $this->assertStringContains('Ma Société SARL', $result['html']);
        $this->assertStringContains('123 Rue de la Paix', $result['html']);
    }

    /**
     * Test rendu mentions avec template default
     */
    public function testRenderMentionsDefaultTemplate()
    {
        $elementData = [
            'type' => 'mentions',
            'properties' => [
                'template' => 'default'
            ]
        ];

        $context = [
            'company' => [
                'name' => 'Test Company',
                'capital' => '10000',
                'vat' => 'FR123456789'
            ]
        ];

        $result = $this->renderer->render($elementData, $context);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('css', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertNull($result['error']);

        // Vérifier que le HTML contient les mentions
        $this->assertStringContains('mentions', $result['html']);
        $this->assertStringContains('Mentions légales', $result['html']);
        $this->assertStringContains('Document généré le', $result['html']);
    }

    /**
     * Test rendu avec données vides
     */
    public function testRenderEmptyCustomerInfo()
    {
        $elementData = [
            'type' => 'customer_info',
            'properties' => []
        ];

        $context = [
            'customer' => []
        ];

        $result = $this->renderer->render($elementData, $context);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('css', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertNull($result['error']);

        // Vérifier le message d'absence d'informations
        $this->assertStringContains('Informations client non disponibles', $result['html']);
        $this->assertStringContains('customer-info-placeholder', $result['html']);
    }

    /**
     * Test rendu avec type non supporté
     */
    public function testRenderUnsupportedType()
    {
        $elementData = [
            'type' => 'unsupported_info_type',
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
        $reflection = new ReflectionClass(InfoRenderer::class);
        $method = $reflection->getMethod('validateElementData');
        $method->setAccessible(true);

        // Test données valides
        $validData = ['type' => 'customer_info'];
        $this->assertTrue($method->invoke($this->renderer, $validData));

        // Test données invalides
        $invalidData = ['type' => 'invalid_type'];
        $this->assertFalse($method->invoke($this->renderer, $invalidData));

        // Test données sans type
        $noTypeData = ['properties' => []];
        $this->assertFalse($method->invoke($this->renderer, $noTypeData));
    }

    /**
     * Test récupération des données client
     */
    public function testGetCustomerData()
    {
        $reflection = new ReflectionClass(InfoRenderer::class);
        $method = $reflection->getMethod('getCustomerData');
        $method->setAccessible(true);

        $context = [
            'customer' => [
                'first_name' => 'Marie',
                'last_name' => 'Dubois'
            ]
        ];

        $customerData = $method->invoke($this->renderer, $context);

        $this->assertIsArray($customerData);
        $this->assertEquals('Marie', $customerData['first_name']);
        $this->assertEquals('Dubois', $customerData['last_name']);
    }

    /**
     * Test formatage des valeurs de champs
     */
    public function testFormatFieldValue()
    {
        $reflection = new ReflectionClass(InfoRenderer::class);
        $method = $reflection->getMethod('formatFieldValue');
        $method->setAccessible(true);

        // Test valeur string
        $result = $method->invoke($this->renderer, 'email', 'test@email.com');
        $this->assertEquals('test@email.com', $result);

        // Test valeur array
        $result = $method->invoke($this->renderer, 'address', ['line1', 'line2']);
        $this->assertEquals('line1, line2', $result);
    }

    /**
     * Test formatage des champs société
     */
    public function testFormatCompanyFieldValue()
    {
        $reflection = new ReflectionClass(InfoRenderer::class);
        $method = $reflection->getMethod('formatCompanyFieldValue');
        $method->setAccessible(true);

        // Test capital
        $result = $method->invoke($this->renderer, 'capital', '10000.50');
        $this->assertStringContains('10 000,50 €', $result);

        // Test TVA
        $result = $method->invoke($this->renderer, 'vat', 'FR123456789');
        $this->assertEquals('TVA : FR123456789', $result);

        // Test téléphone
        $result = $method->invoke($this->renderer, 'phone', '0123456789');
        $this->assertEquals('Tél : 0123456789', $result);
    }

    /**
     * Test remplacement des variables dans les mentions
     */
    public function testReplaceMentionsVariables()
    {
        $reflection = new ReflectionClass(InfoRenderer::class);
        $method = $reflection->getMethod('replaceMentionsVariables');
        $method->setAccessible(true);

        $content = 'Capital social : [capital] €, TVA : [vat]';
        $companyData = [
            'capital' => '50000',
            'vat' => 'FR987654321'
        ];

        $result = $method->invoke($this->renderer, $content, $companyData);

        $this->assertStringContains('Capital social : 50000 €', $result);
        $this->assertStringContains('TVA : FR987654321', $result);
    }

    /**
     * Test génération des styles CSS pour les informations
     */
    public function testGenerateInfoStyles()
    {
        $reflection = new ReflectionClass(InfoRenderer::class);
        $method = $reflection->getMethod('generateInfoStyles');
        $method->setAccessible(true);

        $properties = [];
        $css = $method->invoke($this->renderer, $properties, 'customer-info');

        $this->assertIsString($css);
        $this->assertStringContains('.customer-info', $css);
        $this->assertStringContains('margin: 10px 0', $css);
        $this->assertStringContains('.info-label', $css);
        $this->assertStringContains('font-weight: bold', $css);
    }

    /**
     * Test génération des styles CSS pour les mentions
     */
    public function testGenerateMentionsStyles()
    {
        $reflection = new ReflectionClass(InfoRenderer::class);
        $method = $reflection->getMethod('generateMentionsStyles');
        $method->setAccessible(true);

        $properties = [];
        $css = $method->invoke($this->renderer, $properties);

        $this->assertIsString($css);
        $this->assertStringContains('.mentions', $css);
        $this->assertStringContains('.mentions-title', $css);
        $this->assertStringContains('.mentions-content', $css);
        $this->assertStringContains('border-top: 1px solid #ddd', $css);
    }

    /**
     * Test rendu avec layout horizontal pour customer_info
     */
    public function testRenderCustomerInfoHorizontalLayout()
    {
        $elementData = [
            'type' => 'customer_info',
            'properties' => [
                'layout' => 'horizontal',
                'show_labels' => true,
                'fields' => ['first_name', 'last_name']
            ]
        ];

        $context = [
            'customer' => [
                'first_name' => 'Jean',
                'last_name' => 'Dupont'
            ]
        ];

        $result = $this->renderer->render($elementData, $context);

        $this->assertIsArray($result);
        $this->assertStringContains('Prénom: Jean', $result['html']);
        $this->assertStringContains('Nom: Dupont', $result['html']);
        $this->assertStringContains(' | ', $result['html']); // Séparateur horizontal
    }
}