<?php
/**
 * Vérification Rapide des Menus PDF Builder Pro
 * Script simple pour vérifier si les menus sont enregistrés
 */

echo "<h1>🔍 Vérification Rapide des Menus PDF Builder Pro</h1>";

// Vérifier si le plugin est actif
$plugin_active = is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php');
echo "<h2>📦 État du Plugin</h2>";
echo "<p>Plugin actif: " . ($plugin_active ? '✅ OUI' : '❌ NON') . "</p>";

// Vérifier les menus enregistrés
global $menu, $submenu;
echo "<h2>📋 Menus Enregistrés</h2>";

$found_menus = [];
foreach ($menu as $menu_item) {
    if (stripos($menu_item[0], 'pdf') !== false || stripos($menu_item[2], 'pdf-builder') !== false) {
        $found_menus[] = $menu_item;
    }
}

if (empty($found_menus)) {
    echo "<p>❌ Aucun menu PDF Builder trouvé dans \$menu</p>";
} else {
    echo "<p>✅ Menus PDF Builder trouvés:</p><ul>";
    foreach ($found_menus as $menu_item) {
        echo "<li>" . esc_html($menu_item[0]) . " → " . esc_html($menu_item[2]) . "</li>";
    }
    echo "</ul>";
}

// Vérifier les sous-menus
echo "<h2>📋 Sous-Menus Enregistrés</h2>";
$found_submenus = [];
if (isset($submenu['pdf-builder-main'])) {
    $found_submenus = $submenu['pdf-builder-main'];
}

if (empty($found_submenus)) {
    echo "<p>❌ Aucun sous-menu trouvé pour 'pdf-builder-main'</p>";
} else {
    echo "<p>✅ Sous-menus trouvés:</p><ul>";
    foreach ($found_submenus as $submenu_item) {
        echo "<li>" . esc_html($submenu_item[0]) . " → " . esc_html($submenu_item[2]) . "</li>";
    }
    echo "</ul>";
}

// Vérifier les hooks
echo "<h2>🔗 Hooks Enregistrés</h2>";
global $wp_filter;
$admin_menu_hooks = isset($wp_filter['admin_menu']) ? $wp_filter['admin_menu'] : null;

if ($admin_menu_hooks) {
    echo "<p>✅ Hook 'admin_menu' trouvé</p>";
    $callbacks = [];
    foreach ($admin_menu_hooks->callbacks as $priority => $hooks) {
        foreach ($hooks as $hook) {
            if (is_array($hook['function'])) {
                $callback_name = get_class($hook['function'][0]) . '::' . $hook['function'][1];
            } else {
                $callback_name = $hook['function'];
            }
            if (stripos($callback_name, 'pdf_builder') !== false) {
                $callbacks[] = $callback_name . " (priorité: $priority)";
            }
        }
    }

    if (!empty($callbacks)) {
        echo "<p>Fonctions PDF Builder dans admin_menu:</p><ul>";
        foreach ($callbacks as $callback) {
            echo "<li>" . esc_html($callback) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ Aucune fonction PDF Builder trouvée dans admin_menu</p>";
    }
} else {
    echo "<p>❌ Hook 'admin_menu' non trouvé</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url() . "'>← Retour à l'admin WordPress</a></p>";
?>