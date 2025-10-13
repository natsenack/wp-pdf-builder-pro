<?php
/**
 * Script de correction des permissions TCPDF
 * À exécuter sur le serveur pour corriger les problèmes de permissions
 */

// Fonction pour créer les répertoires nécessaires avec les bonnes permissions
function fix_tcpdf_permissions() {
    $plugin_dir = plugin_dir_path(__FILE__);
    $tcpdf_dir = $plugin_dir . 'lib/tcpdf/';

    echo "🔧 Correction des permissions TCPDF...<br>";

    // 1. Créer le répertoire de cache dans uploads
    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/';

    if (!file_exists($cache_dir)) {
        if (wp_mkdir_p($cache_dir)) {
            echo "✅ Répertoire cache créé : $cache_dir<br>";
        } else {
            echo "❌ Impossible de créer le répertoire cache<br>";
        }
    }

    // Définir les permissions du répertoire cache (755)
    if (file_exists($cache_dir)) {
        chmod($cache_dir, 0755);
        echo "✅ Permissions cache définies (755)<br>";
    }

    // 2. Vérifier et corriger les permissions TCPDF
    if (file_exists($tcpdf_dir)) {
        // Permissions du répertoire principal TCPDF
        chmod($tcpdf_dir, 0755);
        echo "✅ Permissions TCPDF principales définies (755)<br>";

        // Permissions des sous-répertoires
        $subdirs = ['fonts', 'include'];
        foreach ($subdirs as $subdir) {
            $full_path = $tcpdf_dir . $subdir;
            if (file_exists($full_path)) {
                chmod($full_path, 0755);
                echo "✅ Permissions $subdir définies (755)<br>";
            }
        }

        // Permissions des fichiers principaux
        $main_files = ['tcpdf.php', 'tcpdf_autoconfig.php', 'autoload.php'];
        foreach ($main_files as $file) {
            $full_path = $tcpdf_dir . $file;
            if (file_exists($full_path)) {
                chmod($full_path, 0644);
                echo "✅ Permissions $file définies (644)<br>";
            }
        }
    }

    // 3. Créer un fichier .htaccess pour protéger le cache
    $htaccess = $cache_dir . '.htaccess';
    if (!file_exists($htaccess)) {
        $content = "Order deny,allow\nDeny from all\n";
        file_put_contents($htaccess, $content);
        chmod($htaccess, 0644);
        echo "✅ Fichier .htaccess de protection créé<br>";
    }

    echo "🎉 Correction des permissions terminée !<br>";
}

// Fonction pour tester TCPDF après correction
function test_tcpdf_after_fix() {
    echo "<br>🧪 Test de TCPDF après correction...<br>";

    try {
        // Tester le chargement de TCPDF
        $tcpdf_path = plugin_dir_path(__FILE__) . 'lib/tcpdf/tcpdf_autoload.php';
        if (file_exists($tcpdf_path)) {
            require_once $tcpdf_path;
            echo "✅ TCPDF chargé avec succès<br>";

            if (class_exists('TCPDF')) {
                echo "✅ Classe TCPDF disponible<br>";

                // Tester la création d'une instance
                $pdf = new TCPDF();
                echo "✅ Instance TCPDF créée avec succès<br>";
                return true;
            } else {
                echo "❌ Classe TCPDF non trouvée<br>";
            }
        } else {
            echo "❌ Fichier autoload TCPDF introuvable<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erreur lors du test : " . $e->getMessage() . "<br>";
    } catch (Error $e) {
        echo "❌ Erreur fatale lors du test : " . $e->getMessage() . "<br>";
    }

    return false;
}

// Exécuter les corrections si appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(dirname(dirname(__FILE__))) . '/');
    }

    // Simuler WordPress pour les tests
    if (!function_exists('plugin_dir_path')) {
        function plugin_dir_path($file) {
            return dirname(dirname($file)) . '/';
        }
    }

    if (!function_exists('wp_upload_dir')) {
        function wp_upload_dir() {
            return [
                'basedir' => dirname(dirname(dirname(__FILE__))) . '/uploads',
                'baseurl' => 'http://localhost/uploads'
            ];
        }
    }

    if (!function_exists('wp_mkdir_p')) {
        function wp_mkdir_p($dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            return true;
        }
    }

    fix_tcpdf_permissions();
    test_tcpdf_after_fix();
}