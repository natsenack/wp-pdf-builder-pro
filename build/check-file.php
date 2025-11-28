<?php
/**
 * Script de vérification du contenu du Security Validator sur le serveur
 */

echo "<h1>Vérification du contenu du Security Validator</h1>\n";

$file_path = '/var/www/nats/data/www/threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/src/Core/PDF_Builder_Security_Validator.php';

echo "<h2>Chemin du fichier</h2>\n";
echo "<p>$file_path</p>\n";

echo "<h2>Le fichier existe-t-il ?</h2>\n";
if (file_exists($file_path)) {
    echo "<p>✅ OUI - Taille: " . filesize($file_path) . " octets</p>\n";

    echo "<h2>Début du fichier (20 premières lignes)</h2>\n";
    echo "<pre>\n";
    $handle = fopen($file_path, 'r');
    for ($i = 0; $i < 20; $i++) {
        $line = fgets($handle);
        if ($line === false) break;
        echo htmlspecialchars($line);
    }
    fclose($handle);
    echo "</pre>\n";

    echo "<h2>Test d'inclusion</h2>\n";
    try {
        require_once $file_path;
        echo "<p>✅ Inclusion réussie</p>\n";

        if (class_exists('PDF_Builder_Security_Validator')) {
            echo "<p>✅ Classe trouvée</p>\n";
        } else {
            echo "<p>❌ Classe NON trouvée</p>\n";
        }

    } catch (Exception $e) {
        echo "<p>❌ Exception: " . $e->getMessage() . "</p>\n";
    } catch (Error $e) {
        echo "<p>❌ Erreur fatale: " . $e->getMessage() . "</p>\n";
        echo "<p>Ligne: " . $e->getLine() . "</p>\n";
    }

} else {
    echo "<p>❌ NON - Le fichier n'existe pas</p>\n";
}

echo "<h2>Contenu du répertoire Core</h2>\n";
$core_dir = dirname($file_path);
if (is_dir($core_dir)) {
    $files = scandir($core_dir);
    echo "<pre>\n";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "$file\n";
        }
    }
    echo "</pre>\n";
} else {
    echo "<p>❌ Répertoire Core n'existe pas</p>\n";
}
?>