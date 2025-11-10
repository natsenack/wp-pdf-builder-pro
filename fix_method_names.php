<?php
$file = 'plugin/src/Admin/PDF_Builder_Admin.php';
$content = file_get_contents($file);

// Fonction pour convertir snake_case vers camelCase
function snakeToCamel($methodName) {
    $parts = explode('_', $methodName);
    $camel = $parts[0];
    for ($i = 1; $i < count($parts); $i++) {
        $camel .= ucfirst($parts[$i]);
    }
    return $camel;
}

// Corriger les noms de méthodes dans les déclarations de fonctions
$content = preg_replace_callback('/function ([a-z_]+)\(/', function($matches) {
    return 'function ' . snakeToCamel($matches[1]) . '(';
}, $content);

// Corriger les appels de méthodes dans la classe elle-même
$content = preg_replace_callback('/\$this->([a-z_]+)\(/', function($matches) {
    return '$this->' . snakeToCamel($matches[1]) . '(';
}, $content);

file_put_contents($file, $content);
echo "Method names corrected in PDF_Builder_Admin.php\n";
?>