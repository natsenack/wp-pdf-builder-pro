<?php
/**
 * Script de diagnostic pour l'erreur 403 Forbidden
 * sur la page pdf-builder-react-editor
 */

// Inclure WordPress
require_once('../../../wp-load.php');

// Vérifier si l'utilisateur est connecté
if (!is_user_logged_in()) {
    echo "❌ ERREUR: Utilisateur non connecté\n";
    exit;
}

$user = wp_get_current_user();
echo "👤 Utilisateur connecté: {$user->user_login} (ID: {$user->ID})\n";
echo "📧 Email: {$user->user_email}\n";
echo "🎭 Rôles: " . implode(', ', $user->roles) . "\n";

// Vérifier les capacités
echo "\n🔐 Capacités:\n";
echo "  - manage_options: " . (current_user_can('manage_options') ? '✅' : '❌') . "\n";
echo "  - edit_posts: " . (current_user_can('edit_posts') ? '✅' : '❌') . "\n";
echo "  - read: " . (current_user_can('read') ? '✅' : '❌') . "\n";

// Vérifier les permissions du plugin
$allowed_roles = ['administrator', 'editor', 'shop_manager'];
$user_has_access = false;

foreach ($user->roles as $role) {
    if (in_array($role, $allowed_roles)) {
        $user_has_access = true;
        echo "  - Rôle autorisé trouvé: {$role} ✅\n";
        break;
    }
}

if (!$user_has_access) {
    echo "❌ AUCUN RÔLE AUTORISÉ - C'est pourquoi vous avez une erreur 403!\n";
    echo "💡 Rôles autorisés: " . implode(', ', $allowed_roles) . "\n";
}

// Vérifier les paramètres GET
echo "\n🌐 Paramètres GET:\n";
if (isset($_GET['page'])) {
    echo "  - page: {$_GET['page']}\n";
}
if (isset($_GET['template_id'])) {
    echo "  - template_id: {$_GET['template_id']}\n";
}

// Vérifier si le plugin est actif
echo "\n🔌 État du plugin:\n";
if (function_exists('is_plugin_active')) {
    $plugin_file = 'wp-pdf-builder-pro/pdf-builder-pro.php';
    $is_active = is_plugin_active($plugin_file);
    echo "  - Plugin actif: " . ($is_active ? '✅' : '❌') . "\n";

    if (!$is_active) {
        echo "❌ LE PLUGIN N'EST PAS ACTIF!\n";
    }
}

// Vérifier les constantes du plugin
echo "\n⚙️ Constantes du plugin:\n";
echo "  - PDF_BUILDER_PLUGIN_FILE: " . (defined('PDF_BUILDER_PLUGIN_FILE') ? '✅' : '❌') . "\n";
echo "  - PDF_BUILDER_PLUGIN_DIR: " . (defined('PDF_BUILDER_PLUGIN_DIR') ? '✅' : '❌') . "\n";

// Vérifier les actions AJAX
echo "\n🔄 Actions AJAX:\n";
$ajax_actions = [
    'pdf_builder_generate_preview',
    'pdf_builder_save_template',
    'pdf_builder_load_template'
];

foreach ($ajax_actions as $action) {
    $hook = "wp_ajax_{$action}";
    $has_action = has_action($hook);
    echo "  - {$action}: " . ($has_action ? '✅' : '❌') . "\n";
}

// Vérifier les pages admin enregistrées
echo "\n📄 Pages admin:\n";
global $submenu, $menu;

if (isset($submenu['pdf-builder-pro'])) {
    echo "  - Sous-menu 'pdf-builder-pro' trouvé ✅\n";
    foreach ($submenu['pdf-builder-pro'] as $item) {
        echo "    - {$item[0]} -> {$item[2]}\n";
    }
} else {
    echo "  - Sous-menu 'pdf-builder-pro' non trouvé ❌\n";
}

// Vérifier les erreurs récentes dans les logs
echo "\n📋 Logs récents (dernières 10 lignes):\n";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    $lines = file($log_file);
    $recent_lines = array_slice($lines, -10);
    foreach ($recent_lines as $line) {
        echo "  " . trim($line) . "\n";
    }
} else {
    echo "  Aucun fichier debug.log trouvé\n";
}

echo "\n🎯 RÉSUMÉ:\n";
if ($user_has_access && $is_active) {
    echo "✅ Configuration correcte - Le problème pourrait être ailleurs\n";
} else {
    echo "❌ Problème de configuration détecté\n";
}

echo "\n🔧 SOLUTIONS POSSIBLES:\n";
if (!$user_has_access) {
    echo "1. Donner à l'utilisateur un rôle autorisé (administrator, editor, ou shop_manager)\n";
}
if (!$is_active) {
    echo "2. Activer le plugin PDF Builder Pro\n";
}
echo "3. Vider le cache du navigateur\n";
echo "4. Vérifier les plugins de sécurité (Wordfence, etc.)\n";
echo "5. Désactiver temporairement les plugins de sécurité pour tester\n";
?>