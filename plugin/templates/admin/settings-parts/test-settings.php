<?php
/**
 * Test script for PDF Builder Pro settings
 * Tests basic functionality without full WordPress environment
 */

// Define minimal WordPress constants
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Mock WordPress functions
function get_option($key, $default = '') {
    // Mock some options for testing
    $mock_options = [
        'pdf_builder_canvas_format' => 'A4',
        'pdf_builder_canvas_dpi' => 96,
        'pdf_builder_canvas_width' => 794,
        'pdf_builder_canvas_height' => 1123,
        'pdf_builder_cache_enabled' => false,
        'pdf_builder_cache_ttl' => 3600,
    ];
    return $mock_options[$key] ?? $default;
}

function wp_verify_nonce($nonce, $action) {
    return true; // Mock successful nonce verification
}

function wp_send_json_success($data) {
    echo "SUCCESS: " . json_encode($data) . "\n";
}

function wp_send_json_error($data) {
    echo "ERROR: " . json_encode($data) . "\n";
}

function sanitize_text_field($text) {
    return trim($text);
}

// Load required files
echo "Loading Conventions.php...\n";
if (file_exists(__DIR__ . '/../../core/Conventions.php')) {
    require_once __DIR__ . '/../../core/Conventions.php';
    echo "✅ Conventions.php loaded successfully\n";
} else {
    echo "❌ Conventions.php not found at: " . __DIR__ . '/../../core/Conventions.php' . "\n";
    echo "Skipping WordPress-dependent tests...\n";
    goto image_conversion_tests;
}

echo "Loading settings-ajax.php...\n";
require_once __DIR__ . '/settings-ajax.php';

echo "Testing PAPER_FORMATS constant...\n";
if (defined('\PDF_Builder\PAPER_FORMATS')) {
    echo "✅ PAPER_FORMATS constant is defined\n";
    print_r(\PDF_Builder\PAPER_FORMATS);
} else {
    echo "❌ PAPER_FORMATS constant is not defined\n";
}

echo "\nTesting AJAX functions...\n";

// Test cache clear function
echo "Testing pdf_builder_clear_cache_handler...\n";
$_POST = [
    'security' => 'test_nonce',
    'action' => 'pdf_builder_clear_cache'
];

try {
    pdf_builder_clear_cache_handler();
    echo "✅ pdf_builder_clear_cache_handler executed successfully\n";
} catch (Exception $e) {
    echo "❌ Error in pdf_builder_clear_cache_handler: " . $e->getMessage() . "\n";
}

image_conversion_tests:

echo "\n=== TESTING IMAGE CONVERSION (Jours 5-7) ===\n";

// Test ImageConverter class
echo "Testing ImageConverter class...\n";
try {
    require_once 'I:/wp-pdf-builder-pro/plugin/src/utilities/ImageConverter.php';

    if (class_exists('PDF_Builder\Utilities\ImageConverter')) {
        echo "✅ ImageConverter class loaded successfully\n";

        // Test extension check
        $extensions = \PDF_Builder\Utilities\ImageConverter::checkImageExtensions();
        echo "📊 Image extensions status:\n";
        echo "   - Imagick: " . ($extensions['imagick'] ? '✅ Available' : '❌ Not available') . "\n";
        echo "   - GD: " . ($extensions['gd'] ? '✅ Available' : '❌ Not available') . "\n";
        echo "   - Recommended: " . $extensions['recommended'] . "\n";

        // Test with sample PDF content (mock)
        echo "\n🧪 Testing PDF to PNG conversion (mock data)...\n";

        // Create a minimal mock PDF content (this would normally come from DomPDF)
        $mock_pdf_content = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Mock PDF Content) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000200 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n284\n%%EOF";

        $test_params = [
            'format' => 'png',
            'quality' => 150
        ];

        $result = \PDF_Builder\Utilities\ImageConverter::convertPdfToImage($mock_pdf_content, $test_params);

        if ($result['success']) {
            echo "✅ PDF to PNG conversion successful\n";
            echo "   - Converter used: " . $result['converter'] . "\n";
            echo "   - Format: " . $result['format'] . "\n";
            echo "   - Quality: " . $result['quality'] . "\n";
            echo "   - File size: " . $result['file_size'] . " bytes\n";
            echo "   - Image data length: " . strlen($result['image_data']) . " bytes\n";
        } else {
            echo "❌ PDF to PNG conversion failed\n";
            echo "   - Error: " . $result['error'] . "\n";
            echo "   - Converter attempted: " . $result['converter'] . "\n";
        }

        // Test PNG to JPG conversion
        echo "\n🧪 Testing PDF to JPG conversion (mock data)...\n";
        $test_params_jpg = [
            'format' => 'jpg',
            'quality' => 85
        ];

        $result_jpg = \PDF_Builder\Utilities\ImageConverter::convertPdfToImage($mock_pdf_content, $test_params_jpg);

        if ($result_jpg['success']) {
            echo "✅ PDF to JPG conversion successful\n";
            echo "   - Converter used: " . $result_jpg['converter'] . "\n";
            echo "   - Format: " . $result_jpg['format'] . "\n";
            echo "   - Quality: " . $result_jpg['quality'] . "\n";
            echo "   - File size: " . $result_jpg['file_size'] . " bytes\n";
        } else {
            echo "❌ PDF to JPG conversion failed\n";
            echo "   - Error: " . $result_jpg['error'] . "\n";
        }

    } else {
        echo "❌ ImageConverter class not found\n";
    }

} catch (Exception $e) {
    echo "❌ Error testing ImageConverter: " . $e->getMessage() . "\n";
}

// Test PreviewImageAPI class
echo "\n🧪 Testing PreviewImageAPI class...\n";
try {
    require_once 'I:/wp-pdf-builder-pro/plugin/api/PreviewImageAPI.php';

    if (class_exists('PDF_Builder\Api\PreviewImageAPI')) {
        echo "✅ PreviewImageAPI class loaded successfully\n";

        // Note: Skipping instance creation due to dependencies on WordPress/GeneratorManager
        echo "ℹ️  Skipping PreviewImageAPI instantiation (requires WordPress environment)\n";

        // Test cache directory creation
        $cache_dir = (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : sys_get_temp_dir())
            . '/cache/wp-pdf-builder-previews/';
        if (file_exists($cache_dir)) {
            echo "✅ Cache directory exists: " . $cache_dir . "\n";
        } else {
            echo "❌ Cache directory not found: " . $cache_dir . "\n";
        }

    } else {
        echo "❌ PreviewImageAPI class not found\n";
    }

} catch (Exception $e) {
    echo "❌ Error testing PreviewImageAPI: " . $e->getMessage() . "\n";
}

echo "\n=== IMAGE CONVERSION TESTS COMPLETED ===\n";

echo "\nTest completed.\n";
?>