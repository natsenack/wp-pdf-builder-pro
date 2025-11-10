<?php
/**
 * Fix PSR12 violations in PDF_Builder_Feature_Manager.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Managers/PDF_Builder_Feature_Manager.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Feature_Manager', 'class PdfBuilderFeatureManager', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'can_use_feature' => 'canUseFeature',
    'check_usage_limit' => 'checkUsageLimit',
    'increment_usage' => 'incrementUsage',
    'get_current_usage' => 'getCurrentUsage',
    'get_feature_limit' => 'getFeatureLimit',
    'get_all_features' => 'getAllFeatures',
    'get_available_features' => 'getAvailableFeatures',
    'get_premium_features' => 'getPremiumFeatures',
    'is_premium_feature' => 'isPremiumFeature',
    'get_feature_details' => 'getFeatureDetails'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Feature_Manager::$old\(/", "PdfBuilderFeatureManager::$new(", $content);
}

file_put_contents('plugin/src/Managers/PDF_Builder_Feature_Manager.php', $content);

echo "Fixed PDF_Builder_Feature_Manager.php\n";