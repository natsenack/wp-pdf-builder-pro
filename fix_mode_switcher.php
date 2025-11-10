<?php
/**
 * Fix PSR12 violations in PDF_Builder_Mode_Switcher.php
 * - Rename class to PascalCase
 */

$content = file_get_contents('plugin/src/Managers/PDF_Builder_Mode_Switcher.php');

// Rename class
$content = str_replace('class PDF_Builder_Mode_Switcher', 'class PdfBuilderModeSwitcher', $content);

file_put_contents('plugin/src/Managers/PDF_Builder_Mode_Switcher.php', $content);

echo "Fixed PDF_Builder_Mode_Switcher.php\n";