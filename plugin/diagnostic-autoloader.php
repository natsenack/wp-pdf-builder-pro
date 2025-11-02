<?php
/**
 * Diagnostic script for PDF Builder Pro autoloader
 * Version: 2025-11-02 14:21
 */

// Test direct du fichier DataProviderInterface
echo "<h1>🔍 Diagnostic Autoloader PDF Builder Pro</h1>";

$file_path = __DIR__ . '/interfaces/DataProviderInterface.php';
echo "<h2>Test du fichier DataProviderInterface.php</h2>";
echo "<p>Chemin du fichier: <code>$file_path</code></p>";

if (file_exists($file_path)) {
    echo "<p style='color: green;'>✅ Le fichier existe</p>";

    $content = file_get_contents($file_path);
    echo "<p>Taille du fichier: " . strlen($content) . " caractères</p>";

    if (strpos($content, 'interface DataProviderInterface') !== false) {
        echo "<p style='color: green;'>✅ L'interface DataProviderInterface est définie dans le fichier</p>";
    } else {
        echo "<p style='color: red;'>❌ L'interface DataProviderInterface n'est pas trouvée dans le fichier</p>";
        echo "<h3>Contenu du fichier:</h3>";
        echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>";
    }

    // Test de chargement de la classe
    echo "<h3>Test de chargement de la classe:</h3>";
    try {
        require_once $file_path;
        if (interface_exists('WP_PDF_Builder_Pro\Interfaces\DataProviderInterface', false)) {
            echo "<p style='color: green;'>✅ L'interface a été chargée avec succès</p>";
        } else {
            echo "<p style='color: red;'>❌ L'interface n'a pas été chargée</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur lors du chargement: " . $e->getMessage() . "</p>";
    }

} else {
    echo "<p style='color: red;'>❌ Le fichier n'existe pas</p>";
}

// Test de l'autoloader
echo "<h2>Test de l'autoloader</h2>";

if (file_exists(__DIR__ . '/core/autoloader.php')) {
    echo "<p style='color: green;'>✅ Fichier autoloader.php trouvé</p>";

    try {
        require_once __DIR__ . '/core/autoloader.php';

        if (class_exists('PDF_Builder_Autoloader')) {
            echo "<p style='color: green;'>✅ Classe PDF_Builder_Autoloader chargée</p>";

            // Test de chargement automatique
            if (interface_exists('WP_PDF_Builder_Pro\Interfaces\DataProviderInterface', false)) {
                echo "<p style='color: green;'>✅ L'interface DataProviderInterface est déjà chargée (par autoloader?)</p>";
            } else {
                echo "<p>Test de chargement automatique de WP_PDF_Builder_Pro\Interfaces\DataProviderInterface...</p>";

                // Forcer le chargement
                class_exists('WP_PDF_Builder_Pro\Interfaces\DataProviderInterface');

                if (interface_exists('WP_PDF_Builder_Pro\Interfaces\DataProviderInterface', false)) {
                    echo "<p style='color: green;'>✅ L'interface a été chargée automatiquement</p>";
                } else {
                    echo "<p style='color: red;'>❌ Échec du chargement automatique</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>❌ Classe PDF_Builder_Autoloader non trouvée</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur autoloader: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Fichier autoloader.php non trouvé</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url('tools.php?page=pdf-builder-validator') . "'>Retour à l'outil de validation</a></p>";
?>