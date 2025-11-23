<?php

namespace PDF_Builder\Test\Generators;

use PHPUnit\Framework\TestCase;
use PDF_Builder\Generators\BaseGenerator;
use PDF_Builder\Data\SampleDataProvider;

/**
 * Test class for BaseGenerator
 */
class BaseGeneratorTest extends TestCase
{
    private $dataProvider;
    private $templateData;

    protected function setUp(): void
    {
        $this->dataProvider = new SampleDataProvider('canvas');
        $this->templateData = [
            'id' => 1,
            'name' => 'Test Template',
            'elements' => [
                [
                    'id' => 'text1',
                    'type' => 'text',
                    'content' => 'Hello {{customer_name}}',
                    'style' => [
                        'fontSize' => '14px',
                        'color' => '#000000'
                    ],
                    'x' => 10,
                    'y' => 10,
                    'width' => 200,
                    'height' => 50
                ]
            ]
        ];
    }

    public function testInstantiation()
    {
        $generator = $this->getMockForAbstractClass(
            BaseGenerator::class,
            [$this->templateData, $this->dataProvider, true, []]
        );

        $this->assertInstanceOf(BaseGenerator::class, $generator);
    }

    public function testTemplateDataStorage()
    {
        $generator = $this->getMockForAbstractClass(
            BaseGenerator::class,
            [$this->templateData, $this->dataProvider, true, []]
        );

        // Test that template data is stored (using reflection since properties are protected)
        $reflection = new \ReflectionClass($generator);
        $property = $reflection->getProperty('template_data');
        $property->setAccessible(true);

        $this->assertEquals($this->templateData, $property->getValue($generator));
    }

    public function testDataProviderStorage()
    {
        $generator = $this->getMockForAbstractClass(
            BaseGenerator::class,
            [$this->templateData, $this->dataProvider, true, []]
        );

        $reflection = new \ReflectionClass($generator);
        $property = $reflection->getProperty('data_provider');
        $property->setAccessible(true);

        $this->assertSame($this->dataProvider, $property->getValue($generator));
    }

    public function testPreviewModeStorage()
    {
        $generator = $this->getMockForAbstractClass(
            BaseGenerator::class,
            [$this->templateData, $this->dataProvider, true, []]
        );

        $reflection = new \ReflectionClass($generator);
        $property = $reflection->getProperty('is_preview');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($generator));
    }

    public function testDefaultConfig()
    {
        $generator = $this->getMockForAbstractClass(
            BaseGenerator::class,
            [$this->templateData, $this->dataProvider, true, []]
        );

        $reflection = new \ReflectionClass($generator);
        $method = $reflection->getMethod('getDefaultConfig');
        $method->setAccessible(true);

        $config = $method->invoke($generator);
        $this->assertIsArray($config);
        $this->assertArrayHasKey('quality', $config);
    }
}