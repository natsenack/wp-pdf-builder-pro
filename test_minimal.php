<?php
require_once __DIR__ . '/src/Managers/PDF_Builder_Variable_Mapper.php';
echo "Class loaded\n";
try {
    $mapper = new \PDF_Builder\Managers\PDFBuilderVariableMapper(null);
    echo "Mapper created successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
