<?php
/**
 * Test de chargement du plugin PDF Builder Pro - VERSION SIMPLIFIÉE
 * Redirige vers le test rapide pour éviter les problèmes de mémoire
 */

// Redirection vers le test rapide
header('Location: /wp-ajax-quick-test.php');
exit;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Chargement Plugin PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #e6ffe6; border-color: #ccffcc; }
        .error { background: #ffe6e6; border-color: #ffcccc; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        .info { background: #e3f2fd; border-color: #bbdefb; }
        pre { background: #f9f9f9; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test de Chargement - PDF Builder Pro</h1>

        <div class="section info">
            <h2>📊 Informations générales</h2>
            <ul>
                <li><strong>WordPress version:</strong> <?php echo get_bloginfo('version'); ?></li>
                <li><strong>Utilisateur connecté:</strong> <?php echo is_user_logged_in() ? '✅ OUI' : '❌ NON'; ?></li>
                <li><strong>PHP version:</strong> <?php echo PHP_VERSION; ?></li>
                <li><strong>Chemin WordPress:</strong> <?php echo ABSPATH; ?></li>
            </ul>
        </div>

        <div class="section <?php echo is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php') ? 'success' : 'error'; ?>">
            <h2>🔌 Statut du plugin PDF Builder Pro</h2>
            <p><strong>Plugin activé:</strong>
                <?php
                $plugin_path = 'wp-pdf-builder-pro/pdf-builder-pro.php';
                if (is_plugin_active($plugin_path)) {
                    echo ' ✅ OUI';
                } else {
                    echo ' ❌ NON';
                    echo '<br><small>Le plugin n\'est pas activé dans WordPress</small>';
                }
                ?>
            </p>

            <p><strong>Fichier principal existe:</strong>
                <?php
                $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_path;
                if (file_exists($plugin_file)) {
                    echo ' ✅ OUI (' . $plugin_file . ')';
                } else {
                    echo ' ❌ NON (' . $plugin_file . ')';
                }
                ?>
            </p>
        </div>

        <div class="section <?php echo file_exists(WP_PLUGIN_DIR . '/wp-pdf-builder-pro/bootstrap.php') ? 'success' : 'error'; ?>">
            <h2>📁 Fichiers du plugin</h2>
            <ul>
                <li><strong>bootstrap.php:</strong>
                    <?php
                    $bootstrap = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/bootstrap.php';
                    if (file_exists($bootstrap)) {
                        echo ' ✅ Existe';
                        $bootstrap_size = filesize($bootstrap);
                        echo ' (' . number_format($bootstrap_size) . ' octets)';
                    } else {
                        echo ' ❌ Manquant';
                    }
                    ?>
                </li>
                <li><strong>pdf-builder-pro.php:</strong>
                    <?php
                    $main_file = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/pdf-builder-pro.php';
                    if (file_exists($main_file)) {
                        echo ' ✅ Existe';
                        $main_size = filesize($main_file);
                        echo ' (' . number_format($main_size) . ' octets)';
                    } else {
                        echo ' ❌ Manquant';
                    }
                    ?>
                </li>
            </ul>
        </div>

        <div class="section <?php echo has_action('wp_ajax_pdf_builder_preview') ? 'success' : 'error'; ?>">
            <h2>🎯 Actions AJAX enregistrées</h2>
            <ul>
                <li><strong>wp_ajax_pdf_builder_preview:</strong>
                    <?php echo has_action('wp_ajax_pdf_builder_preview') ? ' ✅ OUI' : ' ❌ NON'; ?>
                </li>
                <li><strong>wp_ajax_nopriv_pdf_builder_preview:</strong>
                    <?php echo has_action('wp_ajax_nopriv_pdf_builder_preview') ? ' ✅ OUI' : ' ❌ NON'; ?>
                </li>
                <li><strong>wp_ajax_pdf_builder_test_simple:</strong>
                    <?php echo has_action('wp_ajax_pdf_builder_test_simple') ? ' ✅ OUI' : ' ❌ NON'; ?>
                </li>
            </ul>

            <?php if (!has_action('wp_ajax_pdf_builder_preview')): ?>
            <div class="error" style="margin-top: 10px; padding: 10px;">
                <strong>🔍 Diagnostic:</strong> L'action principale n'est pas enregistrée.<br>
                Cela signifie que le plugin ne se charge pas correctement au démarrage de WordPress.
            </div>
            <?php endif; ?>
        </div>

        <div class="section warning">
            <h2>🔧 Actions recommandées</h2>
            <ol>
                <li><strong>Vérifiez que le plugin est activé</strong> dans Extensions > Extensions installées</li>
                <li><strong>Vérifiez les erreurs PHP</strong> dans les logs du serveur</li>
                <li><strong>Testez l'activation/désactivation</strong> du plugin</li>
                <li><strong>Vérifiez les permissions</strong> des fichiers du plugin</li>
                <?php if (!is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php')): ?>
                <li><strong>Activez le plugin</strong> PDF Builder Pro dans l'admin WordPress</li>
                <?php endif; ?>
            </ol>
        </div>

        <div class="section">
            <h2>📝 Logs de débogage (dernières lignes seulement)</h2>
            <p>Contenu récent du fichier debug.log (si activé) :</p>
            <?php
            $debug_log = WP_CONTENT_DIR . '/debug.log';
            if (file_exists($debug_log) && is_readable($debug_log)) {
                $file_size = filesize($debug_log);
                echo '<p><strong>Taille du fichier:</strong> ' . number_format($file_size) . ' octets</p>';

                if ($file_size > 1024 * 1024) { // Plus de 1MB
                    echo '<p style="color: orange;">⚠️ Fichier debug.log très volumineux (' . number_format($file_size / 1024 / 1024, 1) . ' MB)</p>';
                    echo '<p>Lecture limitée aux dernières 5 lignes pour éviter les problèmes de mémoire.</p>';
                    $lines = file($debug_log);
                    $recent_lines = array_slice($lines, -5); // Dernières 5 lignes seulement
                } else {
                    $lines = file($debug_log);
                    $recent_lines = array_slice($lines, -10); // Dernières 10 lignes
                }

                echo '<pre style="max-height: 200px; overflow-y: auto;">';
                foreach ($recent_lines as $line) {
                    echo htmlspecialchars($line);
                }
                echo '</pre>';
            } else {
                echo '<p style="color: #666;">Fichier debug.log non trouvé ou non lisible.</p>';
                echo '<small>Activez WP_DEBUG et WP_DEBUG_LOG dans wp-config.php pour voir les logs.</small>';
            }
            ?>
        </div>
    </div>
</body>
</html>