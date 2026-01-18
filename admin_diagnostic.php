<?php
/**
 * Script de diagnostic pour vérifier l'enregistrement des pages admin
 */

if (!defined('ABSPATH')) {
    die('Accès direct non autorisé');
}

// Simuler l'environnement WordPress si nécessaire
if (!function_exists('wp_die')) {
    function wp_die($message) {
        die($message);
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($cap) {
        return true; // Simuler un admin
    }
}

echo "<h1>Diagnostic des Pages Admin PDF Builder</h1>\n";

// Vérifier si AdminPages.php existe et est chargé
$admin_pages_path = plugin_dir_path(__FILE__) . 'includes/AdminPages.php';
echo "<h2>1. Vérification du fichier AdminPages.php</h2>\n";
echo "Chemin du fichier: $admin_pages_path<br>\n";
echo "Fichier existe: " . (file_exists($admin_pages_path) ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "<br>\n";

if (file_exists($admin_pages_path)) {
    echo "Classe AdminPages existe: " . (class_exists('PDFBuilderPro\\V2\\AdminPages') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "<br>\n";

    // Tester le chargement du fichier
    echo "<br>Test de chargement du fichier:<br>\n";
    ob_start();
    try {
        require_once $admin_pages_path;
        $output = ob_get_clean();
        echo '<span style="color:green">Chargement réussi</span><br>\n';
        if (!empty($output)) {
            echo "Output: $output<br>\n";
        }
    } catch (Exception $e) {
        $output = ob_get_clean();
        echo '<span style="color:red">Erreur lors du chargement: ' . $e->getMessage() . '</span><br>\n';
    }

    // Vérifier si la classe existe maintenant
    echo "Classe AdminPages après chargement: " . (class_exists('PDFBuilderPro\\V2\\AdminPages') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "<br>\n";

    // Vérifier si la méthode register existe
    if (class_exists('PDFBuilderPro\\V2\\AdminPages')) {
        echo "Méthode register existe: " . (method_exists('PDFBuilderPro\\V2\\AdminPages', 'register') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "<br>\n";

        // Tester l'appel à register
        if (method_exists('PDFBuilderPro\\V2\\AdminPages', 'register')) {
            echo "<br>Test d'appel à AdminPages::register():<br>\n";
            try {
                PDFBuilderPro\V2\AdminPages::register();
                echo '<span style="color:green">Appel réussi</span><br>\n';
            } catch (Exception $e) {
                echo '<span style="color:red">Erreur lors de l\'appel: ' . $e->getMessage() . '</span><br>\n';
            }
        }
    }
}

// Vérifier les hooks WordPress
echo "<h2>2. Vérification des hooks WordPress</h2>\n";
global $wp_filter;

if (isset($wp_filter['admin_menu'])) {
    echo "Hook admin_menu enregistré: <span style=\"color:green\">OUI</span><br>\n";
    echo "Nombre de callbacks: " . count($wp_filter['admin_menu']->callbacks) . "<br>\n";

    // Lister les callbacks
    echo "<br>Callbacks enregistrés:<br>\n";
    foreach ($wp_filter['admin_menu']->callbacks as $priority => $callbacks) {
        echo "Priorité $priority:<br>\n";
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                if (is_object($callback['function'][0])) {
                    echo "  - " . get_class($callback['function'][0]) . "::" . $callback['function'][1] . "<br>\n";
                } else {
                    echo "  - " . $callback['function'][0] . "::" . $callback['function'][1] . "<br>\n";
                }
            } elseif (is_string($callback['function'])) {
                echo "  - " . $callback['function'] . "<br>\n";
            } else {
                echo "  - [fonction anonyme]<br>\n";
            }
        }
    }
} else {
    echo "Hook admin_menu enregistré: <span style=\"color:red\">NON</span><br>\n";
}

// Vérifier les pages admin enregistrées
echo "<h2>3. Pages admin enregistrées</h2>\n";
global $menu, $submenu;

if (isset($menu) && is_array($menu)) {
    echo "Menu principal trouvé: <span style=\"color:green\">OUI</span><br>\n";
    echo "Nombre d'éléments dans le menu: " . count($menu) . "<br>\n";

    // Chercher PDF Builder dans le menu
    $pdf_builder_found = false;
    foreach ($menu as $item) {
        if (isset($item[2]) && strpos($item[2], 'pdf-builder') !== false) {
            $pdf_builder_found = true;
            echo "<br>Élément PDF Builder trouvé:<br>\n";
            echo "  - Titre: " . (isset($item[0]) ? $item[0] : 'N/A') . "<br>\n";
            echo "  - Capacité: " . (isset($item[1]) ? $item[1] : 'N/A') . "<br>\n";
            echo "  - Slug: " . (isset($item[2]) ? $item[2] : 'N/A') . "<br>\n";
            break;
        }
    }

    if (!$pdf_builder_found) {
        echo "<span style=\"color:red\">Aucun élément PDF Builder trouvé dans le menu principal</span><br>\n";
    }
} else {
    echo "Menu principal trouvé: <span style=\"color:red\">NON</span><br>\n";
}

if (isset($submenu) && is_array($submenu)) {
    echo "<br>Submenu trouvé: <span style=\"color:green\">OUI</span><br>\n";

    // Chercher les sous-menus de pdf-builder
    if (isset($submenu['pdf-builder'])) {
        echo "Sous-menu pdf-builder trouvé: <span style=\"color:green\">OUI</span><br>\n";
        echo "Nombre de sous-menus: " . count($submenu['pdf-builder']) . "<br>\n";

        echo "<br>Sous-menus:<br>\n";
        foreach ($submenu['pdf-builder'] as $item) {
            echo "  - " . (isset($item[0]) ? $item[0] : 'N/A') . " → " . (isset($item[2]) ? $item[2] : 'N/A') . "<br>\n";
        }
    } else {
        echo "Sous-menu pdf-builder trouvé: <span style=\"color:red\">NON</span><br>\n";
        echo "Sous-menus disponibles:<br>\n";
        foreach (array_keys($submenu) as $key) {
            echo "  - $key<br>\n";
        }
    }
} else {
    echo "Submenu trouvé: <span style=\"color:red\">NON</span><br>\n";
}

// Vérifier les erreurs PHP
echo "<h2>4. Vérification des erreurs PHP</h2>\n";
$error_reporting = error_reporting();
echo "Error reporting: $error_reporting<br>\n";

if (function_exists('error_get_last')) {
    $last_error = error_get_last();
    if ($last_error) {
        echo "Dernière erreur PHP:<br>\n";
        echo "  - Type: " . $last_error['type'] . "<br>\n";
        echo "  - Message: " . $last_error['message'] . "<br>\n";
        echo "  - Fichier: " . $last_error['file'] . "<br>\n";
        echo "  - Ligne: " . $last_error['line'] . "<br>\n";
    } else {
        echo "Aucune erreur PHP récente<br>\n";
    }
}

echo "<h2>5. Recommandations</h2>\n";
echo "<ul>\n";
echo "<li>Vérifiez que le plugin PDF Builder Pro est activé</li>\n";
echo "<li>Vérifiez que vous êtes connecté en tant qu'administrateur</li>\n";
echo "<li>Essayez de vider le cache WordPress et du navigateur</li>\n";
echo "<li>Vérifiez les logs d'erreur PHP</li>\n";
echo "<li>Essayez de désactiver/réactiver le plugin</li>\n";
echo "</ul>\n";

echo "<br><br><a href=\"javascript:history.back()\">Retour</a>\n";