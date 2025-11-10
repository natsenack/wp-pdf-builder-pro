<?php
$content = file_get_contents('plugin/src/Managers/PDF_Builder_Logger.php');

// Rename class to PascalCase
$content = str_replace('class PDF_Builder_Logger', 'class PdfBuilderLogger', $content);

file_put_contents('plugin/src/Managers/PDF_Builder_Logger.php', $content);
echo "Fixed PDF_Builder_Logger.php\n";