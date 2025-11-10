<?php
// Script pour corriger toutes les références à PDF_Builder_Admin vers PdfBuilderAdmin

$files = [
    'plugin/bootstrap.php',
    'plugin/src/Managers/PDF_Builder_PDF_Generator.php',
    'plugin/src/Core/PDF_Builder_Core.php',
    'plugin/src/Admin/PDF_Builder_Admin.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        // Corriger les références de classe
        $content = str_replace('PDF_Builder_Admin', 'PdfBuilderAdmin', $content);
        $content = str_replace('PDF_Builder\\Admin\\PDF_Builder_Admin', 'PDF_Builder\\Admin\\PdfBuilderAdmin', $content);
        $content = str_replace('\\PDF_Builder\\Admin\\PDF_Builder_Admin', '\\PDF_Builder\\Admin\\PdfBuilderAdmin', $content);

        file_put_contents($file, $content);
        echo "Updated references in $file\n";
    }
}

// Corriger le constructeur dans PDF_Builder_Admin.php
$file = 'plugin/src/Admin/PDF_Builder_Admin.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace('public function Construct(', 'public function __construct(', $content);
    file_put_contents($file, $content);
    echo "Fixed constructor in PDF_Builder_Admin.php\n";
}

echo "All references updated!\n";
?>