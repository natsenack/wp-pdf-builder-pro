<?php
$templates = ['corporate', 'modern', 'minimal', 'classic'];
$baseUrl = 'http://65.108.242.181/wp-content/plugins/wp-pdf-builder-pro/assets/images/templates/';

foreach ($templates as $template) {
    $url = $baseUrl . $template . '-preview.svg';
    echo "Testing: $url\n";
    $headers = @get_headers($url, 1);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "✅ OK - $template\n";
    } else {
        echo "❌ FAIL - $template\n";
        if ($headers) {
            echo "   Response: " . $headers[0] . "\n";
        } else {
            echo "   No response\n";
        }
    }
    echo "\n";
}
?>