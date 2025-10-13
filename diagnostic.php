<?php
/**
 * Diagnostic script pour vérifier le fichier class-pdf-builder-admin.php
 */

echo "<h1>Diagnostic du fichier class-pdf-builder-admin.php</h1>";

// Chemin du fichier
$file_path = __DIR__ . '/includes/classes/class-pdf-builder-admin.php';

echo "<h2>Informations sur le fichier</h2>";
echo "<p><strong>Chemin :</strong> " . $file_path . "</p>";
echo "<p><strong>Existe :</strong> " . (file_exists($file_path) ? 'Oui' : 'Non') . "</p>";

if (file_exists($file_path)) {
    echo "<p><strong>Taille :</strong> " . filesize($file_path) . " octets</p>";
    echo "<p><strong>Date de modification :</strong> " . date('Y-m-d H:i:s', filemtime($file_path)) . "</p>";

    // Lire les lignes autour de 938
    echo "<h2>Contenu autour de la ligne 938</h2>";
    $lines = file($file_path);

    if (isset($lines[937])) { // Les tableaux sont indexés à partir de 0
        echo "<h3>Ligne 938 :</h3>";
        echo "<pre>" . htmlspecialchars($lines[937]) . "</pre>";
    }

    // Afficher quelques lignes autour
    echo "<h3>Lignes 935-945 :</h3>";
    echo "<pre>";
    for ($i = 934; $i <= 944 && isset($lines[$i]); $i++) {
        echo sprintf("%4d: %s", $i+1, htmlspecialchars($lines[$i]));
    }
    echo "</pre>";

    // Vérifier la syntaxe PHP
    echo "<h2>Vérification de la syntaxe PHP</h2>";
    $output = shell_exec("php -l \"$file_path\" 2>&1");
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
} else {
    echo "<p style='color: red;'>Le fichier n'existe pas !</p>";
}
?>