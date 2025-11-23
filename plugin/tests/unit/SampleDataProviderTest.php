<?php

namespace PDF_Builder\Test\Data;

use PHPUnit\Framework\TestCase;
use PDF_Builder\Data\SampleDataProvider;

/**
 * Test class for SampleDataProvider
 */
class SampleDataProviderTest extends TestCase
{
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new SampleDataProvider('canvas');
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(SampleDataProvider::class, $this->provider);
    }

    public function testHasVariable()
    {
        $this->assertTrue($this->provider->hasVariable('customer_name'));
        $this->assertTrue($this->provider->hasVariable('order_number'));
        $this->assertFalse($this->provider->hasVariable('nonexistent_variable'));
    }

    public function testGetVariableValue()
    {
        $name = $this->provider->getVariableValue('customer_name');
        $this->assertIsString($name);
        $this->assertNotEmpty($name);

        $orderNumber = $this->provider->getVariableValue('order_number');
        $this->assertIsString($orderNumber);
        $this->assertNotEmpty($orderNumber);
    }

    public function testGetAllVariables()
    {
        $variables = $this->provider->getAllVariables();
        $this->assertIsArray($variables);
        $this->assertGreaterThan(0, count($variables));
        $this->assertContains('customer_name', $variables);
        $this->assertContains('order_number', $variables);
    }

    public function testContextHandling()
    {
        $canvasProvider = new SampleDataProvider('canvas');
        $metaboxProvider = new SampleDataProvider('metabox');

        // Both should work the same for basic variables
        $this->assertEquals(
            $canvasProvider->getVariableValue('customer_name'),
            $metaboxProvider->getVariableValue('customer_name')
        );
    }
}