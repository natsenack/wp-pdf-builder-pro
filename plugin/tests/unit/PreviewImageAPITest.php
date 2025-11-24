<?php

namespace PDF_Builder\Test\Api;

use PHPUnit\Framework\TestCase;
use PDF_Builder\Api\PreviewImageAPI;

/**
 * Test class for PreviewImageAPI
 */
class PreviewImageAPITest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = new PreviewImageAPI();
    }

    public function testApiInstantiation()
    {
        $this->assertInstanceOf(PreviewImageAPI::class, $this->api);
    }

    public function testCacheKeyGeneration()
    {
        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'template_data' => ['test' => 'data']
        ];

        $cacheKey = $this->api->generateCacheKey($params);
        $this->assertIsString($cacheKey);
        $this->assertNotEmpty($cacheKey);
    }

    public function testParameterValidation()
    {
        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'template_data' => json_encode(['test' => 'data'])
        ];

        // This should not throw an exception
        $validated = $this->api->validateRestParams($params);
        $this->assertIsArray($validated);
        $this->assertEquals('editor', $validated['context']);
        $this->assertEquals('png', $validated['format']);
        $this->assertEquals(150, $validated['quality']);
    }

    public function testInvalidFormatValidation()
    {
        $params = [
            'context' => 'editor',
            'format' => 'invalid',
            'quality' => 150,
            'template_data' => json_encode(['test' => 'data'])
        ];

        $validated = $this->api->validateRestParams($params);
        // Should default to 'png' for invalid format
        $this->assertEquals('png', $validated['format']);
    }

    public function testQualityBoundsValidation()
    {
        // Test minimum quality
        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 10, // Below minimum
            'template_data' => json_encode(['test' => 'data'])
        ];

        $validated = $this->api->validateRestParams($params);
        $this->assertEquals(50, $validated['quality']); // Should be clamped to minimum

        // Test maximum quality
        $params['quality'] = 500; // Above maximum
        $validated = $this->api->validateRestParams($params);
        $this->assertEquals(300, $validated['quality']); // Should be clamped to maximum
    }
}