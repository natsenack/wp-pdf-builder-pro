<?php
/**
 * Script d'extraction automatique du plugin ZIP
 * À uploader dans /wp-content/plugins/ et exécuter via navigateur
 */

// Configuration
$zipFile = 'wp-pdf-builder-pro.zip';
$extractDir = 'wp-pdf-builder-pro';

echo "<h1>🔄 Extraction automatique du plugin PDF Builder Pro</h1>";
echo "<pre>";

// Vérifier si le ZIP existe
if (!file_exists($zipFile)) {
    die("❌ Erreur: Fichier ZIP '$zipFile' introuvable\n");
}

echo "✅ Fichier ZIP trouvé: $zipFile (" . filesize($zipFile) . " bytes)\n";

// Supprimer l'ancien dossier si existe
if (is_dir($extractDir)) {
    echo "🗑️ Suppression de l'ancien dossier...\n";
    deleteDirectory($extractDir);
    echo "✅ Ancien dossier supprimé\n";
}

// Extraire le ZIP
echo "📦 Extraction du ZIP...\n";
$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    if ($zip->extractTo('.')) {
        $zip->close();
        echo "✅ ZIP extrait avec succès\n";

        // Supprimer le ZIP
        if (unlink($zipFile)) {
            echo "🗑️ Fichier ZIP supprimé\n";
        }

        // Corriger les permissions
        echo "🔧 Correction des permissions...\n";
        chmod_r($extractDir, 0755);
        echo "✅ Permissions corrigées\n";

        // Vérifier que les templates sont là
        $templateFile = $extractDir . '/templates/builtin/corporate.json';
        if (file_exists($templateFile)) {
            $content = file_get_contents($templateFile);
            if (strpos($content, 'FACTURE PROFESSIONNELLE') !== false) {
                echo "🎯 ✅ Templates mis à jour avec succès !\n";
                echo "   - Texte 'FACTURE PROFESSIONNELLE' trouvé\n";
            } else {
                echo "⚠️ Templates déployés mais contenu incorrect\n";
            }
        }

        echo "\n🎉 DÉPLOIEMENT TERMINÉ !\n";
        echo "Vous pouvez maintenant :\n";
        echo "1. Vider le cache WordPress\n";
        echo "2. Tester les templates prédéfinis\n";
        echo "3. Supprimer ce fichier (extract-plugin.php)\n";

    } else {
        echo "❌ Erreur lors de l'extraction\n";
    }
} else {
    echo "❌ Impossible d'ouvrir le fichier ZIP\n";
}

echo "</pre>";

// Fonctions utilitaires
function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

function chmod_r($path, $perms) {
    if (is_dir($path)) {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                chmod_r($path . '/' . $file, $perms);
            }
        }
        chmod($path, $perms | 0x4000); // 0x4000 = S_IFDIR
    } else {
        chmod($path, $perms);
    }
}
?>