<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * Page Développeur - PDF Builder Pro
 * Accessible uniquement pour le développeur principal
 *
 * ⚠️ NOTE POUR LE DÉVELOPPEMENT :
 * Cette page est réservée au développement et au débogage.
 * À la fin du développement, vous pouvez :
 * 1. Supprimer complètement ce fichier
 * 2. Commenter/désactiver l'ajout au menu dans class-pdf-builder-admin.php
 * 3. Ou définir PDF_BUILDER_DEV_MODE à false dans wp-config.php
 */



// Vérifier le mode développeur (peut être désactivé en production)
if (!defined('PDF_BUILDER_DEV_MODE') || !PDF_BUILDER_DEV_MODE) {
    wp_die(__('Page développeur désactivée. Définissez PDF_BUILDER_DEV_MODE à true dans wp-config.php pour l\'activer.', 'pdf-builder-pro'));
}

// Vérifier que c'est bien le développeur principal (utilisateur ID 1)
$current_user = wp_get_current_user();
if ($current_user->ID !== 1) {
    wp_die(__('Accès refusé. Cette page est réservée au développeur.', 'pdf-builder-pro'));
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    wp_die(__('Vous devez être administrateur pour accéder à cette page.', 'pdf-builder-pro'));
}

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le nonce
    if (!isset($_POST['pdf_builder_developer_nonce']) ||
        !wp_verify_nonce($_POST['pdf_builder_developer_nonce'], 'pdf_builder_developer_action')) {
        wp_die(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
    }

    // Exécuter du code PHP depuis la console
    if (isset($_POST['execute_php_code']) && !empty($_POST['php_code'])) {
        $php_code = stripslashes($_POST['php_code']);
        $execution_result = '';

        // Protection contre les codes trop longs
        if (strlen($php_code) > 10000) {
            $execution_result = '❌ Code PHP trop long (maximum 10,000 caractères).';
        }
        // Protection contre les exécutions dangereuses
        elseif (stripos($php_code, 'exec(') !== false || stripos($php_code, 'shell_exec(') !== false ||
                stripos($php_code, 'system(') !== false || stripos($php_code, 'passthru(') !== false ||
                stripos($php_code, 'eval(') !== false || stripos($php_code, 'create_function(') !== false) {
            $execution_result = '❌ Commandes système ou fonctions dangereuses interdites pour des raisons de sécurité.';
        } else {
            try {
                // Limiter le temps d'exécution et la mémoire
                ini_set('max_execution_time', 10); // 10 secondes max
                $old_memory_limit = ini_get('memory_limit');
                ini_set('memory_limit', '128M'); // Limiter à 128MB

                ob_start();
                eval($php_code);
                $execution_result = ob_get_clean();

                // Restaurer les limites
                ini_set('memory_limit', $old_memory_limit);

            } catch (Throwable $e) {
                $execution_result = 'Erreur PHP : ' . $e->getMessage() . "\n" . $e->getTraceAsString();
            }
        }

        // Stocker le résultat en session pour l'afficher
        $_SESSION['php_execution_result'] = $execution_result;
        $_SESSION['executed_code'] = $php_code;
    }

    // Nettoyer les logs
    if (isset($_POST['clear_logs'])) {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($log_file)) {
            file_put_contents($log_file, '');
            $admin_notices[] = '<div class="notice notice-success"><p>' . __('Logs nettoyés avec succès.', 'pdf-builder-pro') . '</p></div>';
        }
    }

    // Nettoyer le cache des options
    if (isset($_POST['clear_options_cache'])) {
        wp_cache_flush();
        $admin_notices[] = '<div class="notice notice-success"><p>' . __('Cache des options nettoyé.', 'pdf-builder-pro') . '</p></div>';
    }
}

// Récupérer les informations système
$system_info = array(
    'wordpress_version' => get_bloginfo('version'),
    'php_version' => PHP_VERSION,
    'mysql_version' => $GLOBALS['wpdb']->get_var("SELECT VERSION()"),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'wp_debug' => defined('WP_DEBUG') && WP_DEBUG ? 'Activé' : 'Désactivé',
    'wp_debug_log' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'Activé' : 'Désactivé',
    'wp_debug_display' => defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'Activé' : 'Désactivé',
);

// Récupérer les logs d'erreur récents (limiter la taille pour éviter les erreurs mémoire)
$debug_log_content = '';
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    // Vérifier la taille du fichier (max 10MB pour éviter les problèmes mémoire)
    $file_size = filesize($log_file);
    if ($file_size > 10485760) { // 10MB
        $debug_log_content = "⚠️ Fichier debug.log trop volumineux (" . number_format($file_size / 1024 / 1024, 2) . " MB). Affichage limité aux dernières lignes.\n\n";

        // Lire seulement les derniers 2MB du fichier
        $handle = fopen($log_file, 'r');
        if ($handle) {
            fseek($handle, max(0, $file_size - 2097152)); // 2MB avant la fin
            $content = fread($handle, 2097152);
            fclose($handle);

            // Prendre les 100 dernières lignes
            $lines = explode("\n", $content);
            $lines = array_slice($lines, -100);
            $debug_log_content .= implode("\n", $lines);
        }
    } else {
        $debug_log_content = file_get_contents($log_file);
        // Garder seulement les 100 dernières lignes
        $lines = explode("\n", $debug_log_content);
        $lines = array_slice($lines, -100);
        $debug_log_content = implode("\n", $lines);
    }
}

// Récupérer les options du plugin (avec protection contre les données volumineuses)
$plugin_options = array();
global $wpdb;
$option_names = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_%' OR option_name LIKE 'canvas_%' ORDER BY option_name");
foreach ($option_names as $option_name) {
    $option_value = get_option($option_name);

    // Protection contre les options trop volumineuses
    if (is_string($option_value) && strlen($option_value) > 10000) {
        $plugin_options[$option_name] = '[DONNÉES TROP VOLUMINEUSES - ' . number_format(strlen($option_value)) . ' caractères]';
    } elseif (is_array($option_value) && count($option_value) > 100) {
        $plugin_options[$option_name] = '[TABLEAU TROP GRAND - ' . count($option_value) . ' éléments]';
    } else {
        $plugin_options[$option_name] = $option_value;
    }
}

// Onglet actif
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'system-info';

?>
<div class="wrap">
    <h1><?php _e('🛠️ Page Développeur - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <?php
    // Afficher les notices d'admin
    if (!empty($admin_notices)) {
        foreach ($admin_notices as $notice) {
            echo $notice;
        }
    }

    // Afficher le résultat de l'exécution PHP
    if (isset($_SESSION['php_execution_result'])) {
        echo '<div class="notice notice-info"><p><strong>' . __('Résultat de l\'exécution PHP :', 'pdf-builder-pro') . '</strong></p>';
        echo '<pre style="background: #f1f1f1; padding: 10px; margin-top: 10px; max-height: 300px; overflow: auto;">' . esc_html($_SESSION['php_execution_result']) . '</pre>';
        echo '</div>';
        unset($_SESSION['php_execution_result']);
        unset($_SESSION['executed_code']);
    }
    ?>

    <!-- Onglets -->
    <h2 class="nav-tab-wrapper">
        <a href="?page=pdf-builder-developer&tab=system-info" class="nav-tab <?php echo $active_tab === 'system-info' ? 'nav-tab-active' : ''; ?>">
            <?php _e('📊 Infos Système', 'pdf-builder-pro'); ?>
        </a>
        <a href="?page=pdf-builder-developer&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
            <?php _e('📝 Logs & Erreurs', 'pdf-builder-pro'); ?>
        </a>
        <a href="?page=pdf-builder-developer&tab=plugin-options" class="nav-tab <?php echo $active_tab === 'plugin-options' ? 'nav-tab-active' : ''; ?>">
            <?php _e('⚙️ Options Plugin', 'pdf-builder-pro'); ?>
        </a>
        <a href="?page=pdf-builder-developer&tab=php-console" class="nav-tab <?php echo $active_tab === 'php-console' ? 'nav-tab-active' : ''; ?>">
            <?php _e('💻 Console PHP', 'pdf-builder-pro'); ?>
        </a>
        <a href="?page=pdf-builder-developer&tab=database" class="nav-tab <?php echo $active_tab === 'database' ? 'nav-tab-active' : ''; ?>">
            <?php _e('🗄️ Base de Données', 'pdf-builder-pro'); ?>
        </a>
    </h2>

    <div class="tab-content" style="margin-top: 20px;">
        <?php if ($active_tab === 'system-info'): ?>
            <!-- Onglet Infos Système -->
            <div class="pdf-builder-dev-section">
                <h3><?php _e('Informations Système', 'pdf-builder-pro'); ?></h3>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Paramètre', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('Valeur', 'pdf-builder-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($system_info as $key => $value): ?>
                            <tr>
                                <td><strong><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></strong></td>
                                <td><?php echo esc_html($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pdf-builder-dev-section">
                <h3><?php _e('Constantes WordPress', 'pdf-builder-pro'); ?></h3>
                <table class="widefat striped">
                    <tbody>
                        <tr>
                            <td><strong>ABSPATH</strong></td>
                            <td><?php echo esc_html(ABSPATH); ?></td>
                        </tr>
                        <tr>
                            <td><strong>WP_CONTENT_DIR</strong></td>
                            <td><?php echo esc_html(WP_CONTENT_DIR); ?></td>
                        </tr>
                        <tr>
                            <td><strong>WP_PLUGIN_DIR</strong></td>
                            <td><?php echo esc_html(WP_PLUGIN_DIR); ?></td>
                        </tr>
                        <tr>
                            <td><strong>WP_DEBUG</strong></td>
                            <td><?php echo defined('WP_DEBUG') && WP_DEBUG ? 'true' : 'false'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>WP_DEBUG_LOG</strong></td>
                            <td><?php echo defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'true' : 'false'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>WP_DEBUG_DISPLAY</strong></td>
                            <td><?php echo defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'true' : 'false'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <?php elseif ($active_tab === 'logs'): ?>
            <!-- Onglet Logs & Erreurs -->
            <div class="pdf-builder-dev-section">
                <h3><?php _e('Logs d\'erreurs WordPress (debug.log)', 'pdf-builder-pro'); ?></h3>
                <form method="post" style="margin-bottom: 20px;">
                    <?php wp_nonce_field('pdf_builder_developer_action', 'pdf_builder_developer_nonce'); ?>
                    <input type="submit" name="clear_logs" class="button button-secondary" value="<?php _e('Nettoyer les logs', 'pdf-builder-pro'); ?>" />
                </form>

                <div style="background: #f1f1f1; padding: 15px; border: 1px solid #ddd; max-height: 500px; overflow: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;"><?php echo esc_html($debug_log_content ?: 'Aucun log trouvé ou fichier debug.log inexistant.'); ?></pre>
                </div>
            </div>

            <div class="pdf-builder-dev-section">
                <h3><?php _e('Logs PHP récents', 'pdf-builder-pro'); ?></h3>
                <div style="background: #f1f1f1; padding: 15px; border: 1px solid #ddd; max-height: 300px; overflow: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;"><?php
                        $error_log = ini_get('error_log');
                        if ($error_log && file_exists($error_log)) {
                            $file_size = filesize($error_log);
                            if ($file_size > 10485760) { // 10MB
                                echo "⚠️ Fichier error_log PHP trop volumineux (" . number_format($file_size / 1024 / 1024, 2) . " MB).\n";
                                echo "Utilisez un outil externe pour examiner ce fichier.\n";
                                echo "Chemin : " . esc_html($error_log);
                            } else {
                                $content = file_get_contents($error_log);
                                $lines = explode("\n", $content);
                                $lines = array_slice($lines, -50); // Dernières 50 lignes
                                echo esc_html(implode("\n", $lines));
                            }
                        } else {
                            echo 'Fichier error_log PHP non trouvé ou non accessible.';
                        }
                    ?></pre>
                </div>
            </div>

        <?php elseif ($active_tab === 'plugin-options'): ?>
            <!-- Onglet Options Plugin -->
            <div class="pdf-builder-dev-section">
                <h3><?php _e('Options du Plugin PDF Builder Pro', 'pdf-builder-pro'); ?></h3>
                <form method="post" style="margin-bottom: 20px;">
                    <?php wp_nonce_field('pdf_builder_developer_action', 'pdf_builder_developer_nonce'); ?>
                    <input type="submit" name="clear_options_cache" class="button button-secondary" value="<?php _e('Nettoyer le cache des options', 'pdf-builder-pro'); ?>" />
                </form>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Option', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('Valeur', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('Type', 'pdf-builder-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plugin_options as $key => $value): ?>
                            <tr>
                                <td><strong><?php echo esc_html($key); ?></strong></td>
                                <td>
                                    <?php if (is_array($value) || is_object($value)): ?>
                                        <details>
                                            <summary><?php _e('Cliquer pour voir le contenu', 'pdf-builder-pro'); ?></summary>
                                            <pre style="margin-top: 10px;"><?php echo esc_html(print_r($value, true)); ?></pre>
                                        </details>
                                    <?php elseif (is_bool($value)): ?>
                                        <?php echo $value ? 'true' : 'false'; ?>
                                    <?php else: ?>
                                        <?php echo esc_html($value); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(gettype($value)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($active_tab === 'php-console'): ?>
            <!-- Onglet Console PHP -->
            <div class="pdf-builder-dev-section">
                <h3><?php _e('Console PHP - Exécuter du code', 'pdf-builder-pro'); ?></h3>
                <div class="notice notice-warning">
                    <p><strong><?php _e('⚠️ ATTENTION :', 'pdf-builder-pro'); ?></strong> <?php _e('Cette console permet d\'exécuter du code PHP directement sur le serveur. Utilisez-la avec précaution !', 'pdf-builder-pro'); ?></p>
                </div>

                <form method="post">
                    <?php wp_nonce_field('pdf_builder_developer_action', 'pdf_builder_developer_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Code PHP à exécuter', 'pdf-builder-pro'); ?></th>
                            <td>
                                <textarea name="php_code" rows="10" cols="80" style="font-family: monospace; width: 100%;"><?php
                                    echo isset($_SESSION['executed_code']) ? esc_textarea($_SESSION['executed_code']) : "// Exemple :\n// echo 'Hello World!';\n// var_dump(get_option('pdf_builder_settings'));\n// global \$wpdb;\n// var_dump(\$wpdb->get_results('SELECT * FROM ' . \$wpdb->posts . ' LIMIT 5'));\n";
                                ?></textarea>
                                <p class="description"><?php _e('Le code sera exécuté avec eval(). Les variables globales WordPress sont disponibles.', 'pdf-builder-pro'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="execute_php_code" class="button button-primary" value="<?php _e('Exécuter le code', 'pdf-builder-pro'); ?>" />
                    </p>
                </form>
            </div>

        <?php elseif ($active_tab === 'database'): ?>
            <!-- Onglet Base de Données -->
            <div class="pdf-builder-dev-section">
                <h3><?php _e('Informations Base de Données', 'pdf-builder-pro'); ?></h3>
                <table class="widefat striped">
                    <tbody>
                        <tr>
                            <td><strong><?php _e('Préfixe des tables', 'pdf-builder-pro'); ?></strong></td>
                            <td><?php echo esc_html($GLOBALS['wpdb']->prefix); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Charset', 'pdf-builder-pro'); ?></strong></td>
                            <td><?php echo esc_html($GLOBALS['wpdb']->charset); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Collation', 'pdf-builder-pro'); ?></strong></td>
                            <td><?php echo esc_html($GLOBALS['wpdb']->collate); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Nombre total de tables', 'pdf-builder-pro'); ?></strong></td>
                            <td><?php
                                $tables = $GLOBALS['wpdb']->get_results("SHOW TABLES", ARRAY_N);
                                echo count($tables);
                            ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pdf-builder-dev-section">
                <h3><?php _e('Tables du plugin', 'pdf-builder-pro'); ?></h3>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Table', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('Nombre d\'enregistrements', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('Taille (approximative)', 'pdf-builder-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $plugin_tables = array(
                            $GLOBALS['wpdb']->prefix . 'pdf_builder_templates',
                            $GLOBALS['wpdb']->prefix . 'pdf_builder_elements',
                            $GLOBALS['wpdb']->prefix . 'pdf_builder_settings'
                        );

                        foreach ($plugin_tables as $table):
                            $exists = $GLOBALS['wpdb']->get_var("SHOW TABLES LIKE '$table'") === $table;
                            if ($exists):
                                $count = $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM $table");
                                $size = $GLOBALS['wpdb']->get_var("SELECT ROUND(SUM(data_length + index_length) / 1024, 2) as size_kb FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '$table'");
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($table); ?></strong></td>
                                <td><?php echo esc_html($count); ?></td>
                                <td><?php echo esc_html($size); ?> KB</td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><strong><?php echo esc_html($table); ?></strong></td>
                                <td colspan="2"><em><?php _e('Table inexistante', 'pdf-builder-pro'); ?></em></td>
                            </tr>
                        <?php endif; endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pdf-builder-dev-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.pdf-builder-dev-section h3 {
    margin-top: 0;
    color: #23282d;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.tab-content {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<?php
// Fin du fichier
?>
