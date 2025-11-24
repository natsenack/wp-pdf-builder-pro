<?php

namespace PDF_Builder\Test\Security;

use PHPUnit\Framework\TestCase;
use PDF_Builder\Api\PreviewImageAPI;

/**
 * Security test for Preview API
 */
class PreviewSecurityTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = new PreviewImageAPI();
    }

    public function testPermissionValidation()
    {
        // Mock request for editor context
        $editorRequest = $this->createMock(\WP_REST_Request::class);
        $editorRequest->method('getParam')->willReturnMap([
            ['context', 'editor']
        ]);

        // Should require manage_options capability
        $this->assertTrue($this->api->checkRestPermissions($editorRequest));

        // Mock request for metabox context
        $metaboxRequest = $this->createMock(\WP_REST_Request::class);
        $metaboxRequest->method('getParam')->willReturnMap([
            ['context', 'metabox']
        ]);

        // Should require edit_shop_orders capability
        $this->assertTrue($this->api->checkRestPermissions($metaboxRequest));
    }

    public function testInvalidContextRejection()
    {
        $invalidRequest = $this->createMock(\WP_REST_Request::class);
        $invalidRequest->method('getParam')->willReturnMap([
            ['context', 'invalid']
        ]);

        $this->assertFalse($this->api->checkRestPermissions($invalidRequest));
    }

    public function testParameterSanitization()
    {
        // Test with potentially malicious input
        $maliciousParams = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'templateData' => [
                'elements' => [
                    [
                        'type' => 'text',
                        'content' => '<script>alert("xss")</script>',
                        'x' => 100,
                        'y' => 100
                    ]
                ]
            ]
        ];

        $validated = $this->api->validateRestParams($maliciousParams);

        // Should still be valid but sanitized
        $this->assertIsArray($validated);
        $this->assertEquals('editor', $validated['context']);
        $this->assertEquals('png', $validated['format']);
    }

    public function testTemplateDataValidation()
    {
        $validTemplateData = json_encode([
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'Valid content',
                    'x' => 100,
                    'y' => 100
                ]
            ]
        ]);

        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'templateData' => $validTemplateData
        ];

        $validated = $this->api->validateRestParams($params);
        $this->assertIsArray($validated);
        $this->assertArrayHasKey('template_data', $validated);
    }

    public function testInvalidTemplateDataRejection()
    {
        $invalidTemplateData = 'invalid json';

        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'templateData' => $invalidTemplateData
        ];

        $this->expectException(\Exception::class);
        $this->api->validateRestParams($params);
    }

    public function testQualityBoundsEnforcement()
    {
        // Test quality too low
        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 10,
            'templateData' => json_encode(['elements' => []])
        ];

        $validated = $this->api->validateRestParams($params);
        $this->assertGreaterThanOrEqual(50, $validated['quality']);

        // Test quality too high
        $params['quality'] = 500;
        $validated = $this->api->validateRestParams($params);
        $this->assertLessThanOrEqual(300, $validated['quality']);
    }
}