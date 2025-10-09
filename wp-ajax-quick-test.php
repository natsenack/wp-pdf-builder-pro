<?php
/**
 * Test rapide - Action AJAX PDF Builder
 */

// Inclure WordPress
$wp_load_path = dirname(__FILE__) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    die('Erreur: Impossible de trouver wp-load.php');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Action AJAX PDF Builder</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .result { margin: 10px 0; padding: 10px; border-radius: 3px; }
        .success { background: #e6ffe6; border: 1px solid #ccffcc; color: #00b894; }
        .error { background: #ffe6e6; border: 1px solid #ffcccc; color: #d63031; }
        .info { background: #e3f2fd; border: 1px solid #bbdefb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Test Action AJAX PDF Builder</h1>

        <div class="result info">
            <strong>Plugin chargé:</strong> ✅<br>
            <strong>Utilisateur connecté:</strong> <?php echo is_user_logged_in() ? '✅ OUI' : '❌ NON'; ?><br>
            <strong>Action enregistrée:</strong>
            <?php
            $action_registered = has_action('wp_ajax_pdf_builder_preview');
            echo $action_registered ? '<span style="color:green">✅ OUI</span>' : '<span style="color:red">❌ NON</span>';
            ?>
        </div>

        <?php if ($action_registered): ?>
        <div class="result success">
            <h2>✅ SUCCÈS !</h2>
            <p>L'action AJAX <code>wp_ajax_pdf_builder_preview</code> est enregistrée.</p>
            <p><strong>L'aperçu PDF devrait maintenant fonctionner !</strong></p>
            <p>Retournez dans l'éditeur PDF Builder Pro et testez l'aperçu.</p>
        </div>
        <?php else: ?>
        <div class="result error">
            <h2>❌ ÉCHEC</h2>
            <p>L'action AJAX <code>wp_ajax_pdf_builder_preview</code> n'est toujours pas enregistrée.</p>
            <p>Le plugin ne se charge pas correctement.</p>
        </div>
        <?php endif; ?>

        <div class="result info">
            <h3>Actions de test enregistrées :</h3>
            <ul>
                <li><code>wp_ajax_test_basic_simple</code>: <?php echo has_action('wp_ajax_test_basic_simple') ? '✅' : '❌'; ?></li>
                <li><code>wp_ajax_pdf_builder_test_simple</code>: <?php echo has_action('wp_ajax_pdf_builder_test_simple') ? '✅' : '❌'; ?></li>
            </ul>
        </div>
    </div>
</body>
</html>