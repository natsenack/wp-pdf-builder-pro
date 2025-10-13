<?php
/**
 * Test direct TCPDF loading
 */

echo "Testing TCPDF loading...\n";

try {
    require_once __DIR__ . '/lib/tcpdf/tcpdf_autoload.php';
    echo "TCPDF loaded successfully\n";
    if (class_exists('TCPDF')) {
        echo "TCPDF class exists\n";
    } else {
        echo "TCPDF class does not exist\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed\n";