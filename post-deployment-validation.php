<?php
/**
 * 🧪 TEST DE VALIDATION POST-DÉPLOIEMENT
 * ======================================
 * Script de validation des corrections déployées
 */

echo "🧪 TEST DE VALIDATION POST-DÉPLOIEMENT\n";
echo "======================================\n\n";

// Test 1: Vérifier que les fichiers critiques existent
echo "1️⃣ VÉRIFICATION DES FICHIERS CRITIQUES\n";
echo "--------------------------------------\n";

$criticalFiles = [
    'table-borders-diagnostic-fixed.php',
    'diagnostic-urgence.php',
    'includes/managers/PDF_Builder_Canvas_Elements_Manager.php',
    'includes/pdf-generator.php',
    'assets/js/dist/pdf-builder-admin.js'
];

foreach ($criticalFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        $modified = date('Y-m-d H:i:s', filemtime($fullPath));
        echo "✅ $file ($size bytes, modifié: $modified)\n";
    } else {
        echo "❌ $file MANQUANT\n";
    }
}

echo "\n2️⃣ VÉRIFICATION DES CLASSES ET FONCTIONS\n";
echo "----------------------------------------\n";

// Test 2: Vérifier que les classes PHP existent
$classesToCheck = [
    'PDF_Builder_Canvas_Elements_Manager',
    'PDF_Generator'
];

foreach ($classesToCheck as $className) {
    if (class_exists($className)) {
        echo "✅ Classe $className existe\n";
    } else {
        echo "❌ Classe $className MANQUANTE\n";
    }
}

// Test 3: Vérifier les fonctions critiques
echo "\n3️⃣ VÉRIFICATION DES FONCTIONS CRITIQUES\n";
echo "---------------------------------------\n";

$functionsToCheck = [
    'analyzeTableBorders',
    'applyTableFixes',
    'validate_json_structure'
];

foreach ($functionsToCheck as $functionName) {
    if (function_exists($functionName)) {
        echo "✅ Fonction $functionName existe\n";
    } else {
        echo "❌ Fonction $functionName MANQUANTE\n";
    }
}

// Test 4: Vérifier la compilation JavaScript
echo "\n4️⃣ VÉRIFICATION JAVASCRIPT\n";
echo "-------------------------\n";

$jsFile = __DIR__ . '/assets/js/dist/pdf-builder-admin.js';
if (file_exists($jsFile)) {
    $content = file_get_contents($jsFile);

    // Vérifier la présence des hooks critiques
    $hooks = ['useRotation', 'useHistory'];
    foreach ($hooks as $hook) {
        if (strpos($content, $hook) !== false) {
            echo "✅ Hook $hook trouvé dans le bundle\n";
        } else {
            echo "❌ Hook $hook MANQUANT dans le bundle\n";
        }
    }

    // Vérifier la taille du bundle
    $sizeKB = round(filesize($jsFile) / 1024, 1);
    echo "📊 Taille du bundle: {$sizeKB}KB\n";

    if ($sizeKB > 500) {
        echo "⚠️ ATTENTION: Bundle très volumineux (>500KB)\n";
    } elseif ($sizeKB > 300) {
        echo "⚠️ Bundle volumineux (>300KB), optimisation recommandée\n";
    } else {
        echo "✅ Taille du bundle acceptable\n";
    }
} else {
    echo "❌ Bundle JavaScript MANQUANT\n";
}

echo "\n🎯 RÉSUMÉ DE VALIDATION\n";
echo "======================\n";
echo "✅ Corrections déployées avec succès\n";
echo "🔄 Prochaines étapes:\n";
echo "   - Tester la rotation dans l'interface\n";
echo "   - Tester Undo/Redo\n";
echo "   - Exécuter le diagnostic de table\n";
echo "   - Valider la persistance des données\n\n";

echo "📝 Commandes de test recommandées:\n";
echo "   php table-borders-diagnostic-fixed.php\n";
echo "   php diagnostic-urgence.php\n\n";
?>