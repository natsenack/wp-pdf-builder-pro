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

// Vérifier si les pages admin sont enregistrées
echo "<h2>Test d'enregistrement des pages admin</h2>";

// Test d'enregistrement des pages admin
echo "<h2>Test d'enregistrement des pages admin</h2>";

// Méthode alternative : créer l'instance manuellement et tester directement
echo "<h3>Test direct de l'enregistrement des menus</h3>";

if (class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
    try {
        // Créer l'instance si elle n'existe pas
        $admin_instance = \PDF_Builder\Admin\PdfBuilderAdminNew::getInstance();

        // Vérifier l'état du flag menu_added avant
        $reflection = new ReflectionClass('PDF_Builder\Admin\PdfBuilderAdminNew');
        $menu_added_property = $reflection->getProperty('menu_added');
        $menu_added_property->setAccessible(true);

        $menu_added_before = $menu_added_property->getValue();
        echo "<p>Flag menu_added avant addAdminMenu(): " . ($menu_added_before ? 'true' : 'false') . "</p>";

        // Sauvegarder l'état actuel des menus
        global $menu, $submenu;
        $menu_backup = $menu ?? [];
        $submenu_backup = $submenu ?? [];

        // Forcer la réinitialisation du flag pour le test
        $_GET['force_menu_reset'] = '1';

        // Appeler directement la méthode addAdminMenu
        echo "<p>🔄 Appel direct de addAdminMenu()...</p>";
        $admin_instance->addAdminMenu();
        echo "<p>✅ addAdminMenu() exécutée avec succès.</p>";

        // Nettoyer
        unset($_GET['force_menu_reset']);

        // Vérifier l'état du flag après
        $menu_added_after = $menu_added_property->getValue();
        echo "<p>Flag menu_added après addAdminMenu(): " . ($menu_added_after ? 'true' : 'false') . "</p>";

        // Vérifier si nos menus existent maintenant
        $menu_found = false;
        $submenu_found = false;

        if (isset($menu) && is_array($menu)) {
            foreach ($menu as $item) {
                if (isset($item[2]) && $item[2] === 'pdf-builder-pro') {
                    $menu_found = true;
                    echo "<p>✅ Menu principal 'pdf-builder-pro' trouvé.</p>";
                    break;
                }
            }
        }

        if (isset($submenu) && isset($submenu['pdf-builder-pro']) && is_array($submenu['pdf-builder-pro'])) {
            foreach ($submenu['pdf-builder-pro'] as $item) {
                if (isset($item[2]) && $item[2] === 'pdf-builder-react-editor') {
                    $submenu_found = true;
                    echo "<p>✅ Sous-menu 'pdf-builder-react-editor' trouvé.</p>";
                    break;
                }
            }
        }

        if (!$menu_found) {
            echo "<p>❌ Menu principal 'pdf-builder-pro' non trouvé après addAdminMenu().</p>";
        }

        if (!$submenu_found) {
            echo "<p>❌ Sous-menu 'pdf-builder-react-editor' non trouvé après addAdminMenu().</p>";
        }

        // Restaurer l'état
        $menu = $menu_backup;
        $submenu = $submenu_backup;

    } catch (Exception $e) {
        echo "<p>❌ Erreur lors du test direct : " . $e->getMessage() . "</p>";
    } catch (Error $e) {
        echo "<p>❌ Erreur fatale lors du test direct : " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Classe PdfBuilderAdminNew non disponible pour le test direct.</p>";
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

// Forcer le chargement de la classe si elle n'est pas déjà chargée
if (!class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
    echo "<p>🔄 Tentative de chargement de PdfBuilderAdminNew...</p>";

    // Simuler l'initialisation comme dans le bootstrap
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
        echo "<p>✅ Fichier PDF_Builder_Admin.php chargé.</p>";
    } else {
        echo "<p>❌ Fichier PDF_Builder_Admin.php introuvable.</p>";
    }
}

if (class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
    echo "<p>✅ Classe PdfBuilderAdminNew chargée.</p>";

    // Tester l'instanciation
    try {
        $admin = \PDF_Builder\Admin\PdfBuilderAdminNew::getInstance();
        echo "<p>✅ Instance PdfBuilderAdminNew créée avec succès.</p>";
    } catch (Exception $e) {
        echo "<p>❌ Erreur lors de l'instanciation : " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Classe PdfBuilderAdminNew non trouvée.</p>";
}

if (function_exists('pdf_builder_register_ajax_handlers')) {
    echo "<p>✅ Fonction pdf_builder_register_ajax_handlers disponible.</p>";
} else {
    echo "<p>❌ Fonction pdf_builder_register_ajax_handlers non trouvée.</p>";
}
?>