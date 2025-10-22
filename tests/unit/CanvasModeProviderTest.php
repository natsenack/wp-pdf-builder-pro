<?php
/**
 * Tests pour CanvasModeProvider
 *
 * @package PDF_Builder_Pro
 * @subpackage Tests
 */

use PHPUnit\Framework\TestCase;
use PDF_Builder_Pro\Providers\CanvasModeProvider;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CanvasModeProviderTest extends TestCase
{
    private CanvasModeProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new CanvasModeProvider();
    }

    /**
     * Test récupération des données de base
     */
    public function testGetBaseData()
    {
        $context = ['template_id' => 'test_template'];
        $data = $this->provider->getBaseData($context);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('template_id', $data);
        $this->assertArrayHasKey('mode', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals('canvas', $data['mode']);
        $this->assertEquals('test_template', $data['template_id']);
    }

    /**
     * Test récupération des données client
     */
    public function testGetCustomerData()
    {
        $data = $this->provider->getCustomerData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('first_name', $data);
        $this->assertArrayHasKey('last_name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('address', $data);
        $this->assertEquals('Marie', $data['first_name']);
        $this->assertEquals('Dubois', $data['last_name']);
        $this->assertEquals('marie.dubois@email.com', $data['email']);
        $this->assertIsArray($data['address']);
    }

    /**
     * Test récupération des données commande
     */
    public function testGetOrderData()
    {
        $data = $this->provider->getOrderData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('order_number', $data);
        $this->assertArrayHasKey('order_date', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('items', $data);
        $this->assertEquals('CMD-2024-0456', $data['order_number']);
        $this->assertEquals(354.97, $data['total']);
        $this->assertIsArray($data['items']);
        $this->assertCount(1, $data['items']);
    }

    /**
     * Test récupération des données société
     */
    public function testGetCompanyData()
    {
        $data = $this->provider->getCompanyData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('address', $data);
        $this->assertArrayHasKey('bank_details', $data);
        $this->assertEquals('Votre Société SARL', $data['name']);
        $this->assertEquals('contact@votresociete.com', $data['email']);
        $this->assertIsArray($data['address']);
        $this->assertIsArray($data['bank_details']);
    }

    /**
     * Test récupération des données système
     */
    public function testGetSystemData()
    {
        $data = $this->provider->getSystemData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('current_date', $data);
        $this->assertArrayHasKey('current_time', $data);
        $this->assertArrayHasKey('page_number', $data);
        $this->assertArrayHasKey('document_type', $data);
        $this->assertEquals(1, $data['page_number']);
        $this->assertEquals('invoice', $data['document_type']);
        $this->assertMatchesRegularExpression('/^\d{2}\/\d{2}\/\d{4}$/', $data['current_date']);
    }

    /**
     * Test vérification de complétude des données
     */
    public function testCheckDataCompleteness()
    {
        $requiredKeys = ['customer_name', 'order_number', 'company_name'];
        $result = $this->provider->checkDataCompleteness($requiredKeys);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('complete', $result);
        $this->assertArrayHasKey('missing', $result);
        $this->assertIsBool($result['complete']);
        $this->assertIsArray($result['missing']);
    }

    /**
     * Test génération de données fictives
     */
    public function testGenerateMockData()
    {
        $templateKeys = ['customer_name', 'order_number', 'order_total', 'unknown_key'];
        $mockData = $this->provider->generateMockData($templateKeys);

        $this->assertIsArray($mockData);
        $this->assertArrayHasKey('customer_name', $mockData);
        $this->assertArrayHasKey('order_number', $mockData);
        $this->assertArrayHasKey('order_total', $mockData);
        $this->assertArrayHasKey('unknown_key', $mockData);
        $this->assertEquals('Marie Dubois', $mockData['customer_name']);
        $this->assertEquals('CMD-2024-0456', $mockData['order_number']);
        $this->assertEquals('354,97 €', $mockData['order_total']);
        $this->assertStringStartsWith('[Donnée fictive:', $mockData['unknown_key']);
    }

    /**
     * Test nettoyage des données
     */
    public function testSanitizeData()
    {
        $rawData = [
            'name' => 'Test & <script>alert("xss")</script>',
            'email' => 'test@example.com',
            'nested' => [
                'value' => '<b>bold</b> text'
            ]
        ];

        $sanitized = $this->provider->sanitizeData($rawData);

        $this->assertIsArray($sanitized);
        $this->assertEquals('Test &amp; &lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $sanitized['name']);
        $this->assertEquals('test@example.com', $sanitized['email']);
        $this->assertIsArray($sanitized['nested']);
        $this->assertEquals('&lt;b&gt;bold&lt;/b&gt; text', $sanitized['nested']['value']);
    }

    /**
     * Test système de cache
     */
    public function testCacheSystem()
    {
        $testData = ['test' => 'value'];
        $cacheKey = 'test_key';

        // Test mise en cache
        $result = $this->provider->cacheData($cacheKey, $testData, 60);
        $this->assertTrue($result);

        // Test récupération depuis le cache
        $cachedData = $this->provider->getCachedData($cacheKey);
        $this->assertEquals($testData, $cachedData);

        // Test invalidation du cache
        $invalidateResult = $this->provider->invalidateCache($cacheKey);
        $this->assertTrue($invalidateResult);

        // Vérifier que les données ne sont plus en cache
        $cachedDataAfterInvalidate = $this->provider->getCachedData($cacheKey);
        $this->assertNull($cachedDataAfterInvalidate);
    }

    /**
     * Test cohérence des données fictives
     */
    public function testMockDataConsistency()
    {
        // Récupérer toutes les données
        $customerData = $this->provider->getCustomerData();
        $orderData = $this->provider->getOrderData();
        $companyData = $this->provider->getCompanyData();

        // Vérifier la cohérence client
        $this->assertEquals('Marie Dubois', $customerData['full_name']);
        $this->assertEquals('marie.dubois@email.com', $customerData['email']);

        // Vérifier la cohérence commande
        $this->assertEquals('CMD-2024-0456', $orderData['order_number']);
        $this->assertEquals('Marie', $orderData['shipping_address']['first_name']);
        $this->assertEquals('Dubois', $orderData['shipping_address']['last_name']);

        // Vérifier la cohérence société
        $this->assertEquals('Votre Société SARL', $companyData['name']);
        $this->assertEquals('contact@votresociete.com', $companyData['email']);
    }
}