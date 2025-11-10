<?php
$content = file_get_contents('plugin/src/PDF_Builder_Frontend_I18n.php');

// Add namespace
$content = preg_replace(
    '/^<\?php\s*\n/',
    "<?php\n\nnamespace WP_PDF_Builder_Pro\\Src;\n\n",
    $content
);

// Rename class to PascalCase
$content = str_replace('class PDF_Builder_Frontend_I18n', 'class PdfBuilderFrontendI18n', $content);

// Fix constructor name
$content = str_replace('public function Construct', 'public function __construct', $content);

file_put_contents('plugin/src/PDF_Builder_Frontend_I18n.php', $content);
echo "Fixed PDF_Builder_Frontend_I18n.php\n";