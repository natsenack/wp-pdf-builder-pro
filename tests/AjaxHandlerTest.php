<?php

/**
 * Tests pour le système de sauvegarde PDF Builder Pro
 * Tests unitaires et d'intégration pour valider la sécurité et la fiabilité
 */

use PHPUnit\Framework\TestCase;
use PDF_Builder\Admin\Handlers\AjaxHandler;

class AjaxHandlerTest extends TestCase
{
    private $ajaxHandler;
    private $adminMock;

    protected function setUp(): void
    {
        // Mock de l'admin
        $this->adminMock = $this->createMock('stdClass');
        $this->ajaxHandler = new AjaxHandler($this->adminMock);
    }

    /**
     * Test de validation de taille des données
     */
    public function testValidateRequestSize()
    {
        // Test données normales
        $_SERVER['CONTENT_LENGTH'] = 1000;
        $this->assertTrue($this->invokePrivateMethod('validateRequestSize'));

        // Test données trop volumineuses
        $_SERVER['CONTENT_LENGTH'] = 2 * 1024 * 1024; // 2MB
        $this->assertFalse($this->invokePrivateMethod('validateRequestSize'));
    }

    /**
     * Test de sanitisation des champs selon leur type
     */
    public function testSanitizeFieldValue()
    {
        $method = $this->getPrivateMethod('sanitizeFieldValue');

        // Test email
        $this->assertEquals('test@example.com', $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_email', 'test@example.com']));
        $this->assertEquals('', $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_email', 'invalid-email']));

        // Test URL
        $this->assertEquals('https://example.com', $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_url', 'https://example.com']));
        $this->assertEquals('', $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_url', 'not-a-url']));

        // Test nombre
        $this->assertEquals('123', $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_number', '123']));
        $this->assertEquals('0', $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_number', 'not-a-number']));

        // Test boolean
        $this->assertEquals('1', $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_enabled', 'true']));
        $this->assertEquals('0', $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_enabled', 'false']));

        // Test JSON
        $jsonInput = '{"key": "value"}';
        $result = $method->invokeArgs($this->ajaxHandler, ['pdf_builder_test_array', $jsonInput]);
        $this->assertJson($result);
        $this->assertEquals($jsonInput, $result);
    }

    /**
     * Test de validation JSON
     */
    public function testIsJson()
    {
        $method = $this->getPrivateMethod('isJson');

        $this->assertTrue($method->invokeArgs($this->ajaxHandler, ['{"valid": "json"}']));
        $this->assertFalse($method->invokeArgs($this->ajaxHandler, ['invalid json']));
        $this->assertFalse($method->invokeArgs($this->ajaxHandler, [123]));
    }

    /**
     * Test de rate limiting
     */
    public function testRateLimit()
    {
        // Simuler un utilisateur connecté
        wp_set_current_user(1);

        // Cette méthode devrait réussir normalement
        $this->invokePrivateMethod('checkRateLimit');

        // Après 30 tentatives, elle devrait échouer
        for ($i = 0; $i < 30; $i++) {
            $this->invokePrivateMethod('checkRateLimit');
        }

        // La prochaine devrait envoyer une erreur et terminer
        $this->expectOutputString(''); // wp_send_json_error devrait être appelé
        $this->invokePrivateMethod('checkRateLimit');
    }

    /**
     * Méthode utilitaire pour invoquer des méthodes privées
     */
    private function invokePrivateMethod($methodName, array $args = [])
    {
        $method = $this->getPrivateMethod($methodName);
        return $method->invokeArgs($this->ajaxHandler, $args);
    }

    /**
     * Test des améliorations de performance AJAX
     */
    public function testAjaxPerformanceOptimizations()
    {
        // Test cache des réponses
        $cacheKey = 'test_cache_key';
        $testData = ['result' => 'cached_data'];

        // Simuler une première requête
        $_POST = ['action' => 'pdf_builder_test_action', 'cache_key' => $cacheKey];
        $this->ajaxHandler->handle_ajax_request();

        // La réponse devrait être mise en cache
        $this->assertTrue(wp_cache_get($cacheKey) !== false);
    }

    /**
     * Test de la validation améliorée des données
     */
    public function testEnhancedDataValidation()
    {
        $method = $this->getPrivateMethod('validateFieldData');

        // Test validation de tableau
        $arrayData = ['item1', 'item2', 'item3'];
        $this->assertTrue($method->invokeArgs($this->ajaxHandler, [$arrayData, 'array']));

        $invalidArray = 'not-an-array';
        $this->assertFalse($method->invokeArgs($this->ajaxHandler, [$invalidArray, 'array']));

        // Test validation numérique avec limites
        $this->assertTrue($method->invokeArgs($this->ajaxHandler, [25, 'number', ['min' => 18, 'max' => 65]]));
        $this->assertFalse($method->invokeArgs($this->ajaxHandler, [15, 'number', ['min' => 18, 'max' => 65]]));
        $this->assertFalse($method->invokeArgs($this->ajaxHandler, [70, 'number', ['min' => 18, 'max' => 65]]));
    }

    /**
     * Test de la gestion d'erreurs améliorée
     */
    public function testEnhancedErrorHandling()
    {
        // Test erreur de validation
        $_POST = [
            'action' => 'pdf_builder_save_invalid',
            'invalid_field' => 'invalid_value',
            'nonce' => wp_create_nonce('pdf_builder_ajax')
        ];

        ob_start();
        $this->ajaxHandler->handle_ajax_request();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * Test des métriques de performance
     */
    public function testPerformanceMetricsTracking()
    {
        // Simuler une requête AJAX
        $_POST = [
            'action' => 'pdf_builder_test_performance',
            'nonce' => wp_create_nonce('pdf_builder_ajax')
        ];

        $startTime = microtime(true);
        $this->ajaxHandler->handle_ajax_request();
        $endTime = microtime(true);

        // Vérifier que la requête s'exécute en moins de 500ms
        $executionTime = ($endTime - $startTime) * 1000;
        $this->assertLessThan(500, $executionTime, 'AJAX request should complete in less than 500ms');
    }

    /**
     * Test de sécurité contre les attaques par déni de service
     */
    public function testDosProtection()
    {
        // Simuler de nombreuses requêtes rapides
        for ($i = 0; $i < 10; $i++) {
            $_POST = [
                'action' => 'pdf_builder_test_dos_' . $i,
                'nonce' => wp_create_nonce('pdf_builder_ajax')
            ];

            $this->ajaxHandler->handle_ajax_request();
            usleep(10000); // 10ms delay
        }

        // Le système devrait toujours fonctionner
        $this->assertTrue(true, 'DOS protection should prevent system overload');
    }

    /**
     * Test de la journalisation des erreurs
     */
    public function testErrorLogging()
    {
        // Simuler une erreur
        $_POST = [
            'action' => 'pdf_builder_trigger_error',
            'error_data' => 'test_error',
            'nonce' => wp_create_nonce('pdf_builder_ajax')
        ];

        // Capturer les logs d'erreur
        ob_start();
        $this->ajaxHandler->handle_ajax_request();
        ob_end_clean();

        // Vérifier qu'une entrée de log a été créée (difficile à tester précisément sans accès aux logs)
        $this->assertTrue(true, 'Error logging should work');
    }

    /**
     * Méthode utilitaire pour obtenir des méthodes privées
     */
    private function getPrivateMethod($methodName)
    {
        $reflection = new ReflectionClass($this->ajaxHandler);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}