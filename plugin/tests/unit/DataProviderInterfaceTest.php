<?php

namespace PDF_Builder\Test\Interfaces;

use PHPUnit\Framework\TestCase;
use PDF_Builder\Interfaces\DataProviderInterface;
use PDF_Builder\Data\SampleDataProvider;

/**
 * Test class for DataProviderInterface compliance
 */
class DataProviderInterfaceTest extends TestCase
{
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new SampleDataProvider('canvas');
    }

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(DataProviderInterface::class, $this->provider);
    }

    public function testInterfaceMethodsExist()
    {
        $this->assertTrue(method_exists($this->provider, 'getVariableValue'));
        $this->assertTrue(method_exists($this->provider, 'hasVariable'));
        $this->assertTrue(method_exists($this->provider, 'getAllVariables'));
    }

    public function testGetVariableValueReturnsString()
    {
        $result = $this->provider->getVariableValue('customer_name');
        $this->assertIsString($result);
    }

    public function testHasVariableReturnsBool()
    {
        $result = $this->provider->hasVariable('customer_name');
        $this->assertIsBool($result);
    }

    public function testGetAllVariablesReturnsArray()
    {
        $result = $this->provider->getAllVariables();
        $this->assertIsArray($result);
    }

    public function testNonExistentVariable()
    {
        $this->assertFalse($this->provider->hasVariable('nonexistent_var'));
        $this->assertIsString($this->provider->getVariableValue('nonexistent_var'));
    }
}