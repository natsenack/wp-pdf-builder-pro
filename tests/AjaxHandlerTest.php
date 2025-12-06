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