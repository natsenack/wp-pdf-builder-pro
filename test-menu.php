<?php
// Test rapide des menus WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Simuler l'environnement admin
if (!function_exists('add_menu_page')) {
    echo "WordPress pas chargé correctement\n";
    exit;
}

echo "=== TEST RAPIDE DES MENUS ===\n";

// Vérifier si notre menu existe
global $menu, $submenu;

echo "Menus trouvés avec 'pdf-builder':\n";
$found = false;
foreach ($menu as $item) {
    if (isset($item[2]) && strpos($item[2], 'pdf-builder') !== false) {
        echo "- Menu: " . $item[0] . " (slug: " . $item[2] . ")\n";
        $found = true;
    }
}

if (!$found) {
    echo "❌ Aucun menu PDF Builder trouvé\n";
}

echo "\nSous-menus pour 'pdf-builder-pro':\n";
if (isset($submenu['pdf-builder-pro'])) {
    foreach ($submenu['pdf-builder-pro'] as $sub) {
        echo "- " . $sub[0] . " (slug: " . $sub[2] . ")\n";
    }
} else {
    echo "❌ Aucun sous-menu trouvé\n";
}

echo "\n=== FIN TEST ===\n";
?>