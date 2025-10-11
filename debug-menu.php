<?php
// Définir les constantes nécessaires pour éviter l'erreur d'accès direct
define('WP_USE_THEMES', false);
define('ABSPATH', dirname(__FILE__) . '/');

// Simuler un environnement WordPress minimal
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

// Inclure les fichiers nécessaires
require_once 'bootstrap.php';

// Simuler l'action admin_menu
do_action('admin_menu');

global $menu, $submenu;

echo "=== MENU PRINCIPAL ===\n";
$found = false;
foreach ($menu as $item) {
    if (isset($item[2]) && strpos($item[2], 'pdf-builder') !== false) {
        print_r($item);
        $found = true;
    }
}
if (!$found) {
    echo "Aucun menu principal trouvé avec 'pdf-builder'\n";
}

echo "\n=== SOUS-MENUS ===\n";
if (isset($submenu['pdf-builder-pro'])) {
    print_r($submenu['pdf-builder-pro']);
} else {
    echo "Aucun sous-menu trouvé pour 'pdf-builder-pro'\n";
}