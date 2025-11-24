<?php

namespace PDF_Builder\Test\Integration;

use PHPUnit\Framework\TestCase;
use PDF_Builder\Api\PreviewImageAPI;

/**
 * Integration test for Canvas/Metabox transitions
 */
class CanvasMetaboxIntegrationTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = new PreviewImageAPI();
    }

    public function testEditorContextValidation()
    {
        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'templateData' => ['elements' => []]
        ];

        $validated = $this->api->validateRestParams($params);
        $this->assertEquals('editor', $validated['context']);
        $this->assertEquals('design', $validated['preview_type']);
    }

    public function testMetaboxContextValidation()
    {
        $params = [
            'context' => 'metabox',
            'format' => 'png',
            'quality' => 150,
            'templateData' => ['elements' => []],
            'orderId' => 123
        ];

        $validated = $this->api->validateRestParams($params);
        $this->assertEquals('metabox', $validated['context']);
        $this->assertEquals('order', $validated['preview_type']);
        $this->assertEquals(123, $validated['order_id']);
    }

    public function testMetaboxContextWithoutOrderId()
    {
        $params = [
            'context' => 'metabox',
            'format' => 'png',
            'quality' => 150,
            'templateData' => ['elements' => []]
        ];

        $validated = $this->api->validateRestParams($params);
        $this->assertEquals('metabox', $validated['context']);
        $this->assertEquals('design', $validated['preview_type']);
        $this->assertNull($validated['order_id']);
    }

    public function testDataConsistencyBetweenContexts()
    {
        $templateData = [
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'Test Content',
                    'x' => 100,
                    'y' => 100
                ]
            ]
        ];

        // Test editor context
        $editorParams = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'templateData' => $templateData
        ];

        $editorValidated = $this->api->validateRestParams($editorParams);

        // Test metabox context
        $metaboxParams = [
            'context' => 'metabox',
            'format' => 'png',
            'quality' => 150,
            'templateData' => $templateData
        ];

        $metaboxValidated = $this->api->validateRestParams($metaboxParams);

        // Template data should be consistent
        $this->assertEquals($editorValidated['template_data'], $metaboxValidated['template_data']);
    }

    public function testFormatSupportConsistency()
    {
        $formats = ['png', 'jpg', 'pdf'];

        foreach ($formats as $format) {
            $params = [
                'context' => 'editor',
                'format' => $format,
                'quality' => 150,
                'templateData' => ['elements' => []]
            ];

            $validated = $this->api->validateRestParams($params);
            $this->assertEquals($format, $validated['format']);
        }
    }
}