<?php
/**
 * Diagnostic complet de l'onboarding
 * À placer dans le dossier racine de WordPress et accéder via navigateur
 */

echo "<h1>🔍 Diagnostic Onboarding PDF Builder Pro</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .ok{color:green;} .error{color:red;} .warn{color:orange;} pre{background:#f5f5f5;padding:10px;border-radius:4px;}</style>";

// 1. Vérifier si le plugin est actif
echo "<h2>1. État du Plugin</h2>";
if (function_exists('is_plugin_active')) {
    $active = is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php');
    echo $active ? "<p class='ok'>✅ Plugin actif</p>" : "<p class='error'>❌ Plugin inactif</p>";
} else {
    echo "<p class='warn'>⚠️ Fonction is_plugin_active non disponible</p>";
}

// 2. Vérifier les classes
echo "<h2>2. Classes Chargées</h2>";
$classes = [
    'PDF_Builder_Onboarding_Manager' => class_exists('PDF_Builder_Onboarding_Manager'),
    'PDF_Builder_Core' => class_exists('PDF_Builder_Core'),
    'PDF_Builder_Notification_Manager' => class_exists('PDF_Builder_Notification_Manager')
];

foreach ($classes as $class => $exists) {
    echo $exists ? "<p class='ok'>✅ $class chargée</p>" : "<p class='error'>❌ $class non trouvée</p>";
}

// 3. Vérifier l'instance d'onboarding
echo "<h2>3. Instance Onboarding Manager</h2>";
try {
    if (class_exists('PDF_Builder_Onboarding_Manager')) {
        $onboarding = PDF_Builder_Onboarding_Manager::get_instance();
        echo "<p class='ok'>✅ Instance créée avec succès</p>";

        // Vérifier les options
        $options = get_option('pdf_builder_onboarding', []);
        echo "<p>Options actuelles: <pre>" . print_r($options, true) . "</pre></p>";

        // Vérifier si onboarding terminé
        $completed = $onboarding->is_onboarding_completed();
        $skipped = $onboarding->is_onboarding_skipped();
        echo "<p>Onboarding terminé: " . ($completed ? "<span class='ok'>Oui</span>" : "<span class='error'>Non</span>") . "</p>";
        echo "<p>Onboarding ignoré: " . ($skipped ? "<span class='warn'>Oui</span>" : "<span class='ok'>Non</span>") . "</p>";

    } else {
        echo "<p class='error'>❌ Impossible de créer l'instance</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur: " . $e->getMessage() . "</p>";
}

// 4. Vérifier les fichiers
echo "<h2>4. Fichiers Nécessaires</h2>";
$files = [
    WP_PLUGIN_DIR . '/wp-pdf-builder-pro/src/utilities/PDF_Builder_Onboarding_Manager.php',
    WP_PLUGIN_DIR . '/wp-pdf-builder-pro/assets/js/onboarding.js',
    WP_PLUGIN_DIR . '/wp-pdf-builder-pro/assets/css/onboarding.css'
];

foreach ($files as $file) {
    $exists = file_exists($file);
    echo $exists ? "<p class='ok'>✅ " . basename($file) . " existe</p>" : "<p class='error'>❌ " . basename($file) . " manquant</p>";
}

// 5. Test de réinitialisation
echo "<h2>5. Réinitialisation Force</h2>";
echo "<p><strong>Pour forcer l'affichage de l'onboarding:</strong></p>";
echo "<form method='post'>";
echo "<input type='hidden' name='reset_onboarding' value='1'>";
echo "<button type='submit' style='background:#007cba;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;'>🔄 Réinitialiser l'Onboarding</button>";
echo "</form>";

if (isset($_POST['reset_onboarding'])) {
    delete_option('pdf_builder_onboarding');
    echo "<p class='ok'>✅ Onboarding réinitialisé ! Actualisez la page PDF Builder Pro.</p>";
    echo "<script>alert('Onboarding réinitialisé ! Allez maintenant sur la page PDF Builder Pro.');</script>";
}

// 6. Instructions
echo "<h2>6. Instructions de Test</h2>";
echo "<ol>";
echo "<li>Si l'onboarding est réinitialisé, allez sur: <code>http://localhost/wp-admin/admin.php?page=pdf-builder-pro</code></li>";
echo "<li>Ouvrez les outils de développement (F12) et vérifiez l'onglet Console pour les erreurs</li>";
echo "<li>Vérifiez l'onglet Network pour voir si les fichiers JS/CSS se chargent</li>";
echo "<li>Si rien ne s'affiche, vérifiez les logs PHP pour les erreurs</li>";
echo "</ol>";

echo "<hr>";
echo "<p><small>Diagnostic généré le " . date('d/m/Y H:i:s') . "</small></p>";