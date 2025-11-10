<?php
$content = file_get_contents('plugin/src/Managers/PDF_Builder_Template_Migrator.php');

// Rename class to PascalCase
$content = str_replace('class PDF_Builder_Template_Migrator', 'class PdfBuilderTemplateMigrator', $content);

file_put_contents('plugin/src/Managers/PDF_Builder_Template_Migrator.php', $content);
echo "Fixed PDF_Builder_Template_Migrator.php\n";