<?php
define('PHPUNIT_RUNNING', true);
require_once 'plugin/bootstrap.php';

$controller = new PDF_Generator_Controller();

// Test customer-info
$testElement = array('type' => 'customer-info', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 50);
$result = $controller->renderElement($testElement, array());
echo 'Test customer-info: ' . (strpos($result, 'customer-info') !== false ? 'SUCCESS' : 'FAILED') . PHP_EOL;

// Test order-number
$testElement2 = array('type' => 'order-number', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 50);
$result2 = $controller->renderElement($testElement2, array());
echo 'Test order-number: ' . (strpos($result2, 'order-number') !== false ? 'SUCCESS' : 'FAILED') . PHP_EOL;

// Test company-info
$testElement3 = array('type' => 'company-info', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 50);
$result3 = $controller->renderElement($testElement3, array());
echo 'Test company-info: ' . (strpos($result3, 'company-info') !== false ? 'SUCCESS' : 'FAILED') . PHP_EOL;

echo 'Tests completed.' . PHP_EOL;