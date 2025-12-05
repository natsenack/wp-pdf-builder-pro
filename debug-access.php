<?php
// Debug script pour vérifier la récupération des rôles
echo "<h1>Debug - Récupération des rôles d'accès</h1>";

// Simuler l'environnement WordPress
if (!function_exists('get_option')) {
    function get_option($option, $default = null) {
        // Simuler une base de données
        static $options = [
            'pdf_builder_allowed_roles' => ['administrator', 'editor']
        ];
        return isset($options[$option]) ? $options[$option] : $default;
    }
}

if (!function_exists('pdf_builder_safe_get_option')) {
    function pdf_builder_safe_get_option($option, $default = '') {
        return get_option($option, $default);
    }
}

// Test de récupération
$allowed_roles = pdf_builder_safe_get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
echo "<p>Rôles récupérés: " . implode(', ', $allowed_roles) . "</p>";

if (!is_array($allowed_roles)) {
    $allowed_roles = ['administrator', 'editor', 'shop_manager'];
    echo "<p>Conversion en array par défaut</p>";
}

// Simuler les rôles WordPress
$all_roles = [
    'administrator' => ['name' => 'Administrator'],
    'editor' => ['name' => 'Editor'],
    'author' => ['name' => 'Author'],
    'contributor' => ['name' => 'Contributor'],
    'subscriber' => ['name' => 'Subscriber'],
    'shop_manager' => ['name' => 'Shop Manager']
];

echo "<h2>Test des checkboxes:</h2>";
echo "<ul>";
foreach ($all_roles as $role_key => $role) {
    $is_selected = in_array($role_key, $allowed_roles);
    $checked = $is_selected ? 'checked' : '';
    echo "<li>{$role_key}: " . ($is_selected ? '✅ SÉLECTIONNÉ' : '❌ Non sélectionné') . " - Checkbox: <input type='checkbox' {$checked} disabled></li>";
}
echo "</ul>";
?>