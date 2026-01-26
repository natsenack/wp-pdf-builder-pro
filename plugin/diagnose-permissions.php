<?php
/**
 * Script de diagnostic pour les permissions PDF Builder
 * Peut être exécuté directement ou inclus dans WordPress
 */

// Vérification d'accès direct - seulement si on n'est pas dans un contexte de diagnostic
if (!defined('ABSPATH') && !isset($_GET['direct_access'])) {
    exit('Accès direct interdit - Utilisez ?direct_access=1 pour le diagnostic');
}

// Si accès direct demandé, on définit les constantes WordPress minimales
if (!defined('ABSPATH') && isset($_GET['direct_access'])) {
    // Simuler un environnement WordPress minimal pour le diagnostic
    define('ABSPATH', dirname(__FILE__) . '/../../../');
    define('WPINC', 'wp-includes');

    // Charger wp-load.php si possible
    $wp_load = ABSPATH . 'wp-load.php';
    if (file_exists($wp_load)) {
        require_once $wp_load;
    } else {
        echo "<h1>❌ Impossible de charger WordPress</h1>";
        echo "<p>Le fichier wp-load.php n'a pas été trouvé à : " . $wp_load . "</p>";
        echo "<p>Assurez-vous que ce script est placé dans le dossier plugins de WordPress.</p>";
        exit;
    }
}

// Forcer l'affichage des erreurs pour le diagnostic
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier si on est connecté
if (!is_user_logged_in()) {
    echo "<h1>❌ Non connecté</h1>";
    echo "<p>Vous devez être connecté pour accéder à cette page de diagnostic.</p>";
    echo "<a href='" . wp_login_url() . "'>Se connecter</a>";
    exit;
}

$user = wp_get_current_user();
echo "<h1>🔍 Diagnostic Permissions PDF Builder</h1>";
echo "<h2>Informations utilisateur</h2>";
echo "<ul>";
echo "<li><strong>ID utilisateur :</strong> " . $user->ID . "</li>";
echo "<li><strong>Nom d'utilisateur :</strong> " . $user->user_login . "</li>";
echo "<li><strong>Email :</strong> " . $user->user_email . "</li>";
echo "<li><strong>Rôles :</strong> " . implode(', ', $user->roles) . "</li>";
echo "</ul>";

// Vérifier les permissions
echo "<h2>Vérification des permissions</h2>";

$allowed_roles = ['administrator', 'editor', 'shop_manager'];
$user_roles = $user->roles;
$has_permission = false;

echo "<h3>Rôles autorisés :</h3>";
echo "<ul>";
foreach ($allowed_roles as $role) {
    $has_role = in_array($role, $user_roles);
    $status = $has_role ? "✅" : "❌";
    echo "<li>$status $role</li>";
    if ($has_role) $has_permission = true;
}
echo "</ul>";

echo "<h3>Résultat :</h3>";
if ($has_permission) {
    echo "<p style='color: green; font-weight: bold;'>✅ Vous avez les permissions nécessaires pour accéder à l'éditeur PDF.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Vous n'avez pas les permissions nécessaires.</p>";
    echo "<p>Rôles requis : administrator, editor, ou shop_manager</p>";
}

// Vérifier les capacités WordPress
echo "<h2>Vérification des capacités WordPress</h2>";
$capabilities = [
    'manage_options',
    'edit_posts',
    'read'
];

echo "<ul>";
foreach ($capabilities as $cap) {
    $has_cap = current_user_can($cap);
    $status = $has_cap ? "✅" : "❌";
    echo "<li>$status $cap</li>";
}
echo "</ul>";

// Test d'accès à la page
echo "<h2>Test d'accès à la page</h2>";
$page_url = admin_url('admin.php?page=pdf-builder-react-editor');
echo "<p><strong>URL de l'éditeur :</strong> <a href='$page_url' target='_blank'>$page_url</a></p>";

// Vérifier si la page existe
global $submenu;
$found = false;
if (isset($submenu['pdf-builder-pro'])) {
    foreach ($submenu['pdf-builder-pro'] as $item) {
        if (isset($item[2]) && $item[2] === 'pdf-builder-react-editor') {
            $found = true;
            break;
        }
    }
}

if ($found) {
    echo "<p>✅ La page de l'éditeur est enregistrée dans le menu admin.</p>";
} else {
    echo "<p>❌ La page de l'éditeur n'est pas trouvée dans le menu admin.</p>";
}

// Informations système
echo "<h2>Informations système</h2>";
echo "<ul>";
echo "<li><strong>WordPress version :</strong> " . get_bloginfo('version') . "</li>";
echo "<li><strong>PHP version :</strong> " . phpversion() . "</li>";
echo "<li><strong>Plugin actif :</strong> " . (is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php') ? 'Oui' : 'Non') . "</li>";
echo "</ul>";

// Test de chargement du plugin
echo "<h2>Test de chargement du plugin</h2>";
if (class_exists('PDF_Builder_Admin')) {
    echo "<p>✅ Classe PDF_Builder_Admin chargée.</p>";
} else {
    echo "<p>❌ Classe PDF_Builder_Admin non trouvée.</p>";
}

if (function_exists('pdf_builder_register_ajax_handlers')) {
    echo "<p>✅ Fonction pdf_builder_register_ajax_handlers disponible.</p>";
} else {
    echo "<p>❌ Fonction pdf_builder_register_ajax_handlers non trouvée.</p>";
}
?>