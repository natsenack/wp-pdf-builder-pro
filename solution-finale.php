<?php
// FORCE RELOAD ABSOLU - Solution finale
require_once('../../../wp-load.php');

echo "<h1>FORCE RELOAD ABSOLU - Solution finale</h1>";

// 1. Détecter et supprimer l'ancienne classe si elle existe
echo "<h2>1. Nettoyage de l'ancienne classe</h2>";
if (class_exists('PDF_Builder_Admin', false)) {
    echo "<p>❌ ANCIENNE CLASSE PDF_Builder_Admin TROUVÉE - CONFLIT DÉTECTÉ</p>";
    // On ne peut pas "désenregistrer" une classe en PHP, mais on peut la marquer comme inutilisable
    echo "<p>⚠️ L'ancienne classe existe encore - cela cause le problème</p>";
} else {
    echo "<p>✅ Aucune ancienne classe PDF_Builder_Admin détectée</p>";
}

// 2. Vérifier la nouvelle classe
echo "<h2>2. Vérification de la nouvelle classe</h2>";
if (class_exists('PDF_Builder_Admin_New', false)) {
    echo "<p>✅ PDF_Builder_Admin_New existe</p>";
} else {
    echo "<p>❌ PDF_Builder_Admin_New n'existe pas - CHARGEMENT MANQUÉ</p>";
    // Forcer le chargement
    $file = plugin_dir_path(__FILE__) . 'includes/classes/class-pdf-builder-admin-new.php';
    if (file_exists($file)) {
        require_once $file;
        echo "<p>✅ PDF_Builder_Admin_New chargée manuellement</p>";
    }
}

// 3. Supprimer toutes les actions AJAX existantes
echo "<h2>3. Nettoyage des actions AJAX</h2>";
remove_all_actions('wp_ajax_pdf_builder_load_canvas_elements');
echo "<p>✅ Toutes les actions AJAX supprimées</p>";

// 4. Créer une nouvelle instance de la classe correcte
echo "<h2>4. Création de l'instance correcte</h2>";
if (class_exists('PDF_Builder_Admin_New')) {
    try {
        $instance = PDF_Builder_Admin_New::getInstance(null);
        echo "<p>✅ Instance PDF_Builder_Admin_New créée</p>";

        // Vérifier que l'action est maintenant enregistrée
        global $wp_filter;
        $action = 'wp_ajax_pdf_builder_load_canvas_elements';
        if (isset($wp_filter[$action])) {
            echo "<p>✅ Action AJAX enregistrée avec la nouvelle classe</p>";
        } else {
            echo "<p>❌ Action AJAX PAS enregistrée</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Erreur lors de la création de l'instance: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Impossible de créer l'instance - classe manquante</p>";
}

// 5. Générer le nonce correct
echo "<h2>5. Génération du nonce correct</h2>";
$user_id = get_current_user_id();
$correct_nonce = wp_create_nonce('pdf_builder_canvas_v4_' . $user_id);
$old_nonce = '1cff71fef9';

echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>Ancien nonce (problématique):</strong> $old_nonce</p>";
echo "<p><strong>Nouveau nonce correct:</strong> $correct_nonce</p>";
echo "<p><strong>Différent:</strong> " . ($correct_nonce !== $old_nonce ? "✅ OUI" : "❌ NON") . "</p>";

// 6. Test de validation
echo "<h2>6. Test de validation du nonce</h2>";
$valid_old = wp_verify_nonce($old_nonce, 'pdf_builder_canvas_v4_' . $user_id);
$valid_new = wp_verify_nonce($correct_nonce, 'pdf_builder_canvas_v4_' . $user_id);

echo "<p><strong>Ancien nonce valide:</strong> " . ($valid_old ? "✅ OUI" : "❌ NON") . "</p>";
echo "<p><strong>Nouveau nonce valide:</strong> " . ($valid_new ? "✅ OUI" : "❌ NON") . "</p>";

echo "<hr>";
echo "<h2>INSTRUCTIONS FINALES</h2>";
echo "<ol>";
echo "<li><strong>Fermez complètement votre navigateur</strong> (tous les onglets)</li>";
echo "<li><strong>Rouvrez le navigateur</strong></li>";
echo "<li><strong>Videz le cache du navigateur:</strong> Ctrl+Shift+R (ou Cmd+Shift+R sur Mac)</li>";
echo "<li><strong>Allez à l'éditeur PDF Builder</strong></li>";
echo "<li><strong>Vérifiez dans la console:</strong> pdfBuilderAjax.nonce devrait être '$correct_nonce'</li>";
echo "<li><strong>Testez le chargement des éléments canvas</strong></li>";
echo "</ol>";

echo "<p><strong>Résultat attendu:</strong> Le nonce devrait être '$correct_nonce' et l'erreur 'Nonce invalide' devrait disparaître.</p>";

if ($correct_nonce === $old_nonce) {
    echo "<p style='color: red; font-weight: bold;'>⚠️ ALERT: Le nonce n'a pas changé ! Le problème persiste.</p>";
}
?>