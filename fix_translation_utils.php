<?php
/**
 * Fix PSR12 violations in PDF_Builder_Translation_Utils.php
 * - Add namespace
 * - Rename class to PascalCase
 * - Convert method names from snake_case to camelCase
 */

$content = file_get_contents('plugin/src/Managers/PDF_Builder_Translation_Utils.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n",
    $content
);

// Rename class
$content = str_replace('class PDF_Builder_Translation_Utils', 'class PdfBuilderTranslationUtils', $content);

// Convert method names from snake_case to camelCase
$methodMappings = [
    'detect_language' => 'detectLanguage',
    'load_translations' => 'loadTranslations',
    'load_textdomain' => 'loadTextdomain',
    'set_locale' => 'setLocale',
    'translate_with_vars' => 'translateWithVars',
    'get_current_language' => 'getCurrentLanguage',
    'is_language_supported' => 'isLanguageSupported',
    'get_supported_languages' => 'getSupportedLanguages',
    'format_date' => 'formatDate',
    'format_number' => 'formatNumber',
    'get_locale_info' => 'getLocaleInfo',
    'clear_cache' => 'clearCache'
];

foreach ($methodMappings as $old => $new) {
    // Replace method declarations
    $content = preg_replace("/function $old\(/", "function $new(", $content);
    // Replace method calls within the class
    $content = preg_replace("/\$this->$old\(/", "\$this->$new(", $content);
    // Replace static method calls
    $content = preg_replace("/self::$old\(/", "self::$new(", $content);
    $content = preg_replace("/PDF_Builder_Translation_Utils::$old\(/", "PdfBuilderTranslationUtils::$new(", $content);
}

file_put_contents('plugin/src/Managers/PDF_Builder_Translation_Utils.php', $content);

echo "Fixed PDF_Builder_Translation_Utils.php\n";