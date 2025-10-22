<?php
/**
 * Tests pour MetaboxModeProvider
 *
 * @package PDF_Builder_Pro
 * @subpackage Tests
 */

use PHPUnit\Framework\TestCase;
use PDF_Builder_Pro\Providers\MetaboxModeProvider;

class MetaboxModeProviderTest extends TestCase
{
    private MetaboxModeProvider $provider;
    private $mockOrder;

    protected function setUp(): void
    {
        // Créer un mock WC_Order pour les tests
        $this->mockOrder = $this->createMock(\WC_Order::class);

        // Configuration des méthodes mockées
        $this->mockOrder->method('get_id')->willReturn(12345);
        $this->mockOrder->method('get_order_number')->willReturn('WC-12345');
        $this->mockOrder->method('get_customer_id')->willReturn(678);
        $this->mockOrder->method('get_billing_first_name')->willReturn('Jean');
        $this->mockOrder->method('get_billing_last_name')->willReturn('Dupont');
        $this->mockOrder->method('get_billing_email')->willReturn('jean.dupont@example.com');
        $this->mockOrder->method('get_billing_phone')->willReturn('+33123456789');
        $this->mockOrder->method('get_billing_company')->willReturn('Entreprise Test');
        $this->mockOrder->method('get_billing_address_1')->willReturn('123 Rue de Test');
        $this->mockOrder->method('get_billing_city')->willReturn('Paris');
        $this->mockOrder->method('get_billing_postcode')->willReturn('75001');
        $this->mockOrder->method('get_billing_country')->willReturn('FR');
        $this->mockOrder->method('get_formatted_billing_address')->willReturn('Jean Dupont\nEntreprise Test\n123 Rue de Test\n75001 Paris\nFrance');
        $this->mockOrder->method('get_total')->willReturn(150.00);
        $this->mockOrder->method('get_currency')->willReturn('EUR');
        $this->mockOrder->method('get_status')->willReturn('processing');
        $this->mockOrder->method('get_payment_method_title')->willReturn('Carte bancaire');
        $this->mockOrder->method('get_customer_note')->willReturn('Livraison rapide svp');

        $this->provider = new MetaboxModeProvider($this->mockOrder);
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
        $this->assertArrayHasKey('order_id', $data);
        $this->assertEquals('metabox', $data['mode']);
        $this->assertEquals('test_template', $data['template_id']);
        $this->assertEquals(12345, $data['order_id']);
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
        $this->assertEquals('Jean', $data['first_name']);
        $this->assertEquals('Dupont', $data['last_name']);
        $this->assertEquals('jean.dupont@example.com', $data['email']);
        $this->assertEquals('Jean Dupont', $data['full_name']);
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
        $this->assertEquals('WC-12345', $data['order_number']);
        $this->assertEquals(150.00, $data['total']);
        $this->assertEquals('processing', $data['order_status']);
        $this->assertEquals('Carte bancaire', $data['payment_method']);
        $this->assertIsArray($data['items']);
    }

    /**
     * Test récupération des données société
     */
    public function testGetCompanyData()
    {
        // Mock des options WordPress
        add_option('woocommerce_store_name', 'Ma Société SARL');
        add_option('woocommerce_store_email', 'contact@masociete.com');
        add_option('woocommerce_store_address', '456 Avenue des Champs');
        add_option('woocommerce_store_city', 'Lyon');
        add_option('woocommerce_store_postcode', '69000');

        $data = $this->provider->getCompanyData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('address', $data);
        $this->assertArrayHasKey('bank_details', $data);
        $this->assertEquals('Ma Société SARL', $data['name']);
        $this->assertEquals('contact@masociete.com', $data['email']);
        $this->assertIsArray($data['address']);
        $this->assertIsArray($data['bank_details']);

        // Nettoyer
        delete_option('woocommerce_store_name');
        delete_option('woocommerce_store_email');
        delete_option('woocommerce_store_address');
        delete_option('woocommerce_store_city');
        delete_option('woocommerce_store_postcode');
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
     * Test génération de données mock
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
        $this->assertEquals('Jean Dupont', $mockData['customer_name']);
        $this->assertEquals('WC-12345', $mockData['order_number']);
        $this->assertStringStartsWith('150,00', $mockData['order_total']);
        $this->assertStringStartsWith('[Donnée manquante:', $mockData['unknown_key']);
    }

    /**
     * Test nettoyage des données
     */
    public function testSanitizeData()
    {
        $rawData = [
            'name' => 'Test & <script>alert("xss")</script>',
            'price' => 150.50,
            'safe' => 'Normal text'
        ];

        $sanitized = $this->provider->sanitizeData($rawData);

        $this->assertIsArray($sanitized);
        $this->assertEquals('Test & alert("xss")', $sanitized['name']); // wp_kses_post permet certains tags
        $this->assertEquals('150,50', $sanitized['price']); // Format français
        $this->assertEquals('Normal text', $sanitized['safe']);
    }

    /**
     * Test système de cache
     */
    public function testCacheSystem()
    {
        $testData = ['test' => 'cached_value'];
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
     * Test avec commande nulle
     */
    public function testWithNullOrder()
    {
        $provider = new MetaboxModeProvider(null);

        $customerData = $provider->getCustomerData();
        $this->assertStringStartsWith('[', $customerData['first_name']); // Placeholder

        $orderData = $provider->getOrderData();
        $this->assertStringStartsWith('[', $orderData['order_number']); // Placeholder
    }

    /**
     * Test cohérence des données réelles
     */
    public function testRealDataConsistency()
    {
        // Récupérer toutes les données
        $customerData = $this->provider->getCustomerData();
        $orderData = $this->provider->getOrderData();
        $companyData = $this->provider->getCompanyData();

        // Vérifier la cohérence client
        $this->assertEquals('Jean Dupont', $customerData['full_name']);
        $this->assertEquals('jean.dupont@example.com', $customerData['email']);

        // Vérifier la cohérence commande
        $this->assertEquals('WC-12345', $orderData['order_number']);
        $this->assertEquals(12345, $orderData['order_id']);
        $this->assertEquals(150.00, $orderData['total']);

        // Vérifier que les données société sont récupérées
        $this->assertIsArray($companyData);
        $this->assertArrayHasKey('name', $companyData);
    }
}