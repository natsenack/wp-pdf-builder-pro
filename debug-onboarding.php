<?php
/**
 * Diagnostic complet et réparation de l'onboarding PDF Builder Pro
 * À placer dans la racine de WordPress et accéder via navigateur
 */

echo "<h1>🔍 Diagnostic Avancé - Onboarding PDF Builder Pro</h1>";
echo "<style>
body{font-family:Arial,sans-serif;margin:20px;max-width:800px;}
.status-ok{color:green;font-weight:bold;}
.status-error{color:red;font-weight:bold;}
.status-warn{color:orange;font-weight:bold;}
.section{background:#f9f9f9;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #007cba;}
.code{background:#f4f4f4;padding:10px;border-radius:3px;font-family:monospace;}
.btn{background:#007cba;color:white;padding:10px 20px;border:none;border-radius:3px;cursor:pointer;text-decoration:none;display:inline-block;margin:5px;}
.btn:hover{background:#005a87;}
</style>";

// Section 1: État du système
echo "<div class='section'>";
echo "<h2>1. État du Système WordPress</h2>";

// Vérifier si on est dans WordPress
if (!defined('ABSPATH')) {
    echo "<p class='status-error'>❌ Pas dans un environnement WordPress</p>";
    exit;
}

echo "<p class='status-ok'>✅ Environnement WordPress détecté</p>";
echo "<p><strong>Version WordPress:</strong> " . get_bloginfo('version') . "</p>";
echo "<p><strong>Utilisateur actuel:</strong> " . wp_get_current_user()->display_name . " (ID: " . get_current_user_id() . ")</p>";
echo "<p><strong>Page actuelle:</strong> " . (isset($_GET['page']) ? $_GET['page'] : 'Aucune') . "</p>";
echo "</div>";

// Section 2: État du plugin
echo "<div class='section'>";
echo "<h2>2. État du Plugin PDF Builder Pro</h2>";

$plugin_active = is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php');
echo "<p><strong>Plugin actif:</strong> " . ($plugin_active ? "<span class='status-ok'>✅ Oui</span>" : "<span class='status-error'>❌ Non</span>") . "</p>";

// Vérifier les classes
$classes = [
    'PDF_Builder_Onboarding_Manager' => class_exists('PDF_Builder_Onboarding_Manager'),
    'PDF_Builder_Core' => class_exists('PDF_Builder_Core'),
];

foreach ($classes as $class => $loaded) {
    echo "<p><strong>$class:</strong> " . ($loaded ? "<span class='status-ok'>✅ Chargée</span>" : "<span class='status-error'>❌ Non trouvée</span>") . "</p>";
}
echo "</div>";

// Section 3: État de l'onboarding
echo "<div class='section'>";
echo "<h2>3. État de l'Onboarding</h2>";

$onboarding_options = get_option('pdf_builder_onboarding', []);
echo "<p><strong>Options d'onboarding:</strong></p>";
echo "<pre class='code'>" . print_r($onboarding_options, true) . "</pre>";

// Vérifier l'instance
try {
    if (class_exists('PDF_Builder_Onboarding_Manager')) {
        $onboarding = PDF_Builder_Onboarding_Manager::get_instance();
        echo "<p class='status-ok'>✅ Instance créée avec succès</p>";

        echo "<p><strong>Onboarding terminé:</strong> " . ($onboarding->is_onboarding_completed() ? "<span class='status-ok'>Oui</span>" : "<span class='status-error'>Non</span>") . "</p>";
        echo "<p><strong>Onboarding ignoré:</strong> " . ($onboarding->is_onboarding_skipped() ? "<span class='status-warn'>Oui</span>" : "<span class='status-ok'>Non</span>") . "</p>";

        // Tester les étapes
        $steps = $onboarding->get_onboarding_steps();
        echo "<p><strong>Nombre d'étapes:</strong> " . count($steps) . "</p>";

    } else {
        echo "<p class='status-error'>❌ Impossible de créer l'instance</p>";
    }
} catch (Exception $e) {
    echo "<p class='status-error'>❌ Erreur: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Section 4: Vérification des fichiers
echo "<div class='section'>";
echo "<h2>4. Vérification des Fichiers</h2>";

$files = [
    WP_PLUGIN_DIR . '/wp-pdf-builder-pro/src/utilities/PDF_Builder_Onboarding_Manager.php',
    WP_PLUGIN_DIR . '/wp-pdf-builder-pro/assets/js/onboarding.js',
    WP_PLUGIN_DIR . '/wp-pdf-builder-pro/assets/css/onboarding.css',
    WP_PLUGIN_DIR . '/wp-pdf-builder-pro/bootstrap.php'
];

foreach ($files as $file) {
    $exists = file_exists($file);
    $size = $exists ? filesize($file) : 0;
    echo "<p><strong>" . basename($file) . ":</strong> " . ($exists ? "<span class='status-ok'>✅ Existe ({$size} octets)</span>" : "<span class='status-error'>❌ Manquant</span>") . "</p>";
}
echo "</div>";

// Section 5: Test de rendu
echo "<div class='section'>";
echo "<h2>5. Test de Rendu du Modal</h2>";

if (isset($_GET['test_render'])) {
    try {
        if (class_exists('PDF_Builder_Onboarding_Manager')) {
            $onboarding = PDF_Builder_Onboarding_Manager::get_instance();
            echo "<p class='status-ok'>✅ Test de rendu demandé</p>";

            ob_start();
            $onboarding->render_onboarding_wizard();
            $output = ob_get_clean();

            if (strpos($output, 'pdf-builder-onboarding-modal') !== false) {
                echo "<p class='status-ok'>✅ Modal rendu correctement</p>";
                echo "<details><summary>Voir le HTML généré (aperçu)</summary>";
                echo "<pre class='code'>" . htmlspecialchars(substr($output, 0, 2000)) . (strlen($output) > 2000 ? "\n\n[... HTML tronqué ...]" : "") . "</pre>";
                echo "</details>";
            } else {
                echo "<p class='status-error'>❌ Problème avec le rendu du modal</p>";
                echo "<pre class='code'>" . htmlspecialchars(substr($output, 0, 1000)) . "</pre>";
            }
        }
    } catch (Exception $e) {
        echo "<p class='status-error'>❌ Erreur lors du rendu: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p><a href='?test_render=1' class='btn'>🧪 Tester le rendu du modal</a></p>";
}
echo "</div>";

// Section 6: Actions de réparation
echo "<div class='section'>";
echo "<h2>6. Actions de Réparation</h2>";

echo "<p><a href='?reset_onboarding=1' class='btn' onclick='return confirm(\"Êtes-vous sûr de vouloir réinitialiser l'onboarding ?\")'>🔄 Réinitialiser l'Onboarding</a></p>";
echo "<p><a href='?force_show=1' class='btn'>👁️ Forcer l'Affichage</a></p>";
echo "<p><a href='" . admin_url('admin.php?page=pdf-builder-pro') . "' class='btn' target='_blank'>📍 Aller à PDF Builder</a></p>";

// Traiter les actions
if (isset($_GET['reset_onboarding'])) {
    $deleted = delete_option('pdf_builder_onboarding');
    echo "<div style='background:#d4edda;color:#155724;padding:10px;border-radius:3px;margin:10px 0;'>";
    echo "<strong>✅ Onboarding réinitialisé !</strong> (" . ($deleted ? "Option supprimée" : "Option inexistante") . ")";
    echo "<br><a href='" . admin_url('admin.php?page=pdf-builder-pro') . "' target='_blank'>Aller à PDF Builder Pro</a>";
    echo "</div>";
}

if (isset($_GET['force_show'])) {
    // Forcer l'affichage en modifiant temporairement les options
    update_option('pdf_builder_onboarding', [
        'completed' => false,
        'skipped' => false,
        'current_step' => 1,
        'steps_completed' => [],
        'first_login' => time(),
        'last_activity' => time()
    ]);
    echo "<div style='background:#d4edda;color:#155724;padding:10px;border-radius:3px;margin:10px 0;'>";
    echo "<strong>✅ Affichage forcé activé !</strong>";
    echo "<br><a href='" . admin_url('admin.php?page=pdf-builder-pro') . "' target='_blank'>Aller à PDF Builder Pro</a>";
    echo "</div>";
}
echo "</div>";

// Section 7: Debug JavaScript
echo "<div class='section'>";
echo "<h2>7. Debug JavaScript</h2>";
echo "<p>Ouvrez les outils de développement (F12) et vérifiez :</p>";
echo "<ul>";
echo "<li><strong>Console:</strong> Cherchez les erreurs liées à 'onboarding' ou 'PDF_Builder'</li>";
echo "<li><strong>Network:</strong> Vérifiez que onboarding.js et onboarding.css se chargent</li>";
echo "<li><strong>Elements:</strong> Cherchez la classe 'pdf-builder-onboarding-modal'</li>";
echo "</ul>";
echo "<p><strong>Code JavaScript de test:</strong></p>";
echo "<pre class='code'>// À coller dans la console
if (typeof pdfBuilderOnboarding !== 'undefined') {
    console.log('✅ pdfBuilderOnboarding chargé:', pdfBuilderOnboarding);
} else {
    console.log('❌ pdfBuilderOnboarding non trouvé');
}</pre>";
echo "</div>";

echo "<hr>";
echo "<p><small>Diagnostic généré le " . current_time('d/m/Y H:i:s') . " - <a href='?'>Actualiser</a></small></p>";