<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * État du Système - PDF Builder Pro
 * Version: 1.0.0
 * Description: Vérifie l'état général du système et des dépendances
 */

// Sécurité : vérifier que nous sommes en mode développement
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    die('Cet outil n\'est disponible qu\'en mode développement (WP_DEBUG = true)');
}

// Vérifier les permissions administrateur
if (!current_user_can('manage_options')) {
    die('Permissions insuffisantes');
}

// Fonction pour vérifier les exigences système
function check_system_requirements() {
    $requirements = [
        'php_version' => [
            'required' => '7.4.0',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'message' => 'PHP 7.4+ requis pour une compatibilité optimale'
        ],
        'wordpress_version' => [
            'required' => '5.0',
            'current' => get_bloginfo('version'),
            'status' => version_compare(get_bloginfo('version'), '5.0', '>='),
            'message' => 'WordPress 5.0+ requis'
        ],
        'memory_limit' => [
            'required' => '128M',
            'current' => ini_get('memory_limit'),
            'status' => return_bytes(ini_get('memory_limit')) >= return_bytes('128M'),
            'message' => 'Au moins 128M de mémoire requis pour la génération PDF'
        ],
        'max_execution_time' => [
            'required' => '30',
            'current' => ini_get('max_execution_time'),
            'status' => ini_get('max_execution_time') >= 30 || ini_get('max_execution_time') == 0,
            'message' => 'Au moins 30 secondes de temps d\'exécution requis'
        ],
        'upload_max_filesize' => [
            'required' => '10M',
            'current' => ini_get('upload_max_filesize'),
            'status' => return_bytes(ini_get('upload_max_filesize')) >= return_bytes('10M'),
            'message' => 'Au moins 10M pour l\'upload de fichiers'
        ]
    ];

    return $requirements;
}

// Fonction pour vérifier les extensions PHP requises
function check_php_extensions() {
    $extensions = [
        'gd' => [
            'required' => true,
            'loaded' => extension_loaded('gd'),
            'message' => 'Requis pour la manipulation d\'images et la génération PDF'
        ],
        'mbstring' => [
            'required' => true,
            'loaded' => extension_loaded('mbstring'),
            'message' => 'Requis pour le support Unicode et l\'encodage des caractères'
        ],
        'xml' => [
            'required' => true,
            'loaded' => extension_loaded('xml'),
            'message' => 'Requis pour le traitement XML'
        ],
        'zip' => [
            'required' => true,
            'loaded' => extension_loaded('zip'),
            'message' => 'Requis pour les archives ZIP et les exports'
        ],
        'curl' => [
            'required' => false,
            'loaded' => extension_loaded('curl'),
            'message' => 'Recommandé pour les requêtes HTTP externes'
        ],
        'openssl' => [
            'required' => false,
            'loaded' => extension_loaded('openssl'),
            'message' => 'Recommandé pour les connexions sécurisées'
        ],
        'pdo' => [
            'required' => true,
            'loaded' => extension_loaded('pdo'),
            'message' => 'Requis pour l\'accès à la base de données'
        ],
        'pdo_mysql' => [
            'required' => true,
            'loaded' => extension_loaded('pdo_mysql'),
            'message' => 'Requis pour les connexions MySQL'
        ]
    ];

    return $extensions;
}

// Fonction pour vérifier les permissions des dossiers
function check_directory_permissions() {
    global $wp_filesystem;

    $dirs = [
        WP_CONTENT_DIR => 'wp-content',
        WP_CONTENT_DIR . '/uploads' => 'wp-content/uploads',
        WP_PLUGIN_DIR => 'wp-content/plugins',
        get_template_directory() => 'theme actif',
        WP_CONTENT_DIR . '/cache' => 'wp-content/cache (si utilisé)',
    ];

    // Ajouter les dossiers spécifiques au plugin
    $plugin_dirs = [
        plugin_dir_path(__FILE__) . '../cache' => 'plugin cache',
        plugin_dir_path(__FILE__) . '../uploads' => 'plugin uploads',
    ];

    $dirs = array_merge($dirs, $plugin_dirs);

    $results = [];
    foreach ($dirs as $path => $name) {
        if (file_exists($path)) {
            $writable = is_writable($path);
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $results[$name] = [
                'path' => $path,
                'writable' => $writable,
                'permissions' => $perms,
                'exists' => true
            ];
        } else {
            $results[$name] = [
                'path' => $path,
                'writable' => false,
                'permissions' => 'N/A',
                'exists' => false
            ];
        }
    }

    return $results;
}

// Fonction pour vérifier la connectivité de la base de données
function check_database_connection() {
    global $wpdb;

    $results = [
        'connection' => false,
        'query_time' => 0,
        'error' => ''
    ];

    $start_time = microtime(true);

    try {
        // Test simple de connexion
        $test_query = $wpdb->query("SELECT 1");
        $results['connection'] = ($test_query !== false);
        $results['query_time'] = round((microtime(true) - $start_time) * 1000, 2); // en ms

        if (!$results['connection']) {
            $results['error'] = $wpdb->last_error;
        }
    } catch (Exception $e) {
        $results['error'] = $e->getMessage();
    }

    return $results;
}

// Fonction pour vérifier les plugins actifs
function check_active_plugins() {
    $active_plugins = get_option('active_plugins', []);
    $plugin_data = [];

    foreach ($active_plugins as $plugin) {
        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
        if (file_exists($plugin_file)) {
            $data = get_plugin_data($plugin_file);
            $plugin_data[] = [
                'name' => $data['Name'],
                'version' => $data['Version'],
                'author' => $data['Author'],
                'file' => $plugin
            ];
        }
    }

    return $plugin_data;
}

// Fonction pour vérifier les constantes importantes
function check_important_constants() {
    $constants = [
        'WP_DEBUG' => [
            'value' => defined('WP_DEBUG') ? WP_DEBUG : false,
            'recommended' => false,
            'message' => 'Désactiver en production pour de meilleures performances'
        ],
        'WP_DEBUG_LOG' => [
            'value' => defined('WP_DEBUG_LOG') ? WP_DEBUG_LOG : false,
            'recommended' => null,
            'message' => 'Active la journalisation des erreurs dans un fichier'
        ],
        'WP_DEBUG_DISPLAY' => [
            'value' => defined('WP_DEBUG_DISPLAY') ? WP_DEBUG_DISPLAY : true,
            'recommended' => false,
            'message' => 'Masque les erreurs à l\'écran en production'
        ],
        'WP_MEMORY_LIMIT' => [
            'value' => defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : '40M',
            'recommended' => '256M',
            'message' => 'Limite mémoire pour WordPress'
        ],
        'WP_MAX_MEMORY_LIMIT' => [
            'value' => defined('WP_MAX_MEMORY_LIMIT') ? WP_MAX_MEMORY_LIMIT : '256M',
            'recommended' => '256M',
            'message' => 'Limite mémoire maximale pour WordPress'
        ]
    ];

    return $constants;
}

// Fonction utilitaire pour convertir les tailles en bytes
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;

    switch($last) {
        case 'g':
            $val *= 1024 * 1024 * 1024;
            break;
        case 'm':
            $val *= 1024 * 1024;
            break;
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}

// Fonction pour calculer le score global
function calculate_overall_score($checks) {
    $total_checks = 0;
    $passed_checks = 0;

    foreach ($checks as $category => $items) {
        foreach ($items as $item) {
            if (isset($item['status'])) {
                $total_checks++;
                if ($item['status']) {
                    $passed_checks++;
                }
            } elseif (isset($item['loaded'])) {
                $total_checks++;
                if ($item['loaded']) {
                    $passed_checks++;
                }
            } elseif (isset($item['writable'])) {
                $total_checks++;
                if ($item['writable']) {
                    $passed_checks++;
                }
            }
        }
    }

    return $total_checks > 0 ? round(($passed_checks / $total_checks) * 100, 1) : 0;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 État du Système - PDF Builder Pro</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .section { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status { padding: 15px; border-radius: 4px; margin: 15px 0; }
        .status.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .check-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 20px 0; }
        .check-card { border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; background: #f8f9fa; }
        .check-passed { border-color: #28a745; background: #d4edda; }
        .check-failed { border-color: #dc3545; background: #f8d7da; }
        .check-warning { border-color: #ffc107; background: #fff3cd; }
        .check-icon { font-size: 18px; margin-right: 8px; }
        .score-circle { width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; margin: 10px; }
        .score-excellent { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .score-good { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
        .score-poor { background: linear-gradient(135deg, #dc3545, #e83e8c); color: white; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        .plugin-list { max-height: 300px; overflow-y: auto; }
        .constant-value { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .btn { background: #007cba; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 10px 5px; }
        .btn:hover { background: #005a87; }
        .btn.secondary { background: #6c757d; }
        .btn.secondary:hover { background: #545b62; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 État du Système - PDF Builder Pro</h1>
            <p>Vérification complète de l'état du système et des dépendances</p>
        </div>

        <?php
        // Collecter toutes les vérifications
        $system_req = check_system_requirements();
        $php_ext = check_php_extensions();
        $dir_perms = check_directory_permissions();
        $db_conn = check_database_connection();
        $active_plugins = check_active_plugins();
        $constants = check_important_constants();

        $all_checks = [
            'system' => $system_req,
            'extensions' => $php_ext,
            'directories' => $dir_perms
        ];

        $overall_score = calculate_overall_score($all_checks);

        // Déterminer la classe du score
        $score_class = 'score-excellent';
        if ($overall_score < 70) {
            $score_class = 'score-poor';
        } elseif ($overall_score < 85) {
            $score_class = 'score-good';
        }
        ?>

        <!-- Score Global -->
        <div class="section">
            <h2>📊 Score Global du Système</h2>
            <div style="text-align: center;">
                <div class="score-circle <?php echo $score_class; ?>">
                    <?php echo $overall_score; ?>%
                </div>
                <p>
                    <?php if ($overall_score >= 85): ?>
                        <strong>Excellent !</strong> Votre système est bien configuré.
                    <?php elseif ($overall_score >= 70): ?>
                        <strong>Bon</strong> Quelques optimisations recommandées.
                    <?php else: ?>
                        <strong>À améliorer</strong> Des problèmes importants nécessitent attention.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Exigences Système -->
        <div class="section">
            <h2>⚙️ Exigences Système</h2>
            <div class="check-grid">
                <?php foreach ($system_req as $req => $info): ?>
                    <div class="check-card <?php echo $info['status'] ? 'check-passed' : 'check-failed'; ?>">
                        <div>
                            <span class="check-icon"><?php echo $info['status'] ? '✅' : '❌'; ?></span>
                            <strong><?php echo ucwords(str_replace('_', ' ', $req)); ?></strong>
                        </div>
                        <div style="margin: 8px 0;">
                            <small>Requis: <?php echo $info['required']; ?> | Actuel: <?php echo $info['current']; ?></small>
                        </div>
                        <div>
                            <small><?php echo $info['message']; ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Extensions PHP -->
        <div class="section">
            <h2>🔌 Extensions PHP</h2>
            <div class="check-grid">
                <?php foreach ($php_ext as $ext => $info): ?>
                    <?php
                    $status_class = 'check-passed';
                    $icon = '✅';
                    if (!$info['loaded']) {
                        if ($info['required']) {
                            $status_class = 'check-failed';
                            $icon = '❌';
                        } else {
                            $status_class = 'check-warning';
                            $icon = '⚠️';
                        }
                    }
                    ?>
                    <div class="check-card <?php echo $status_class; ?>">
                        <div>
                            <span class="check-icon"><?php echo $icon; ?></span>
                            <strong><?php echo $ext; ?></strong>
                            <?php if ($info['loaded']): ?>
                                <small>(v<?php echo phpversion($ext); ?>)</small>
                            <?php endif; ?>
                        </div>
                        <div style="margin: 8px 0;">
                            <small><?php echo $info['required'] ? 'Requis' : 'Recommandé'; ?></small>
                        </div>
                        <div>
                            <small><?php echo $info['message']; ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Permissions des Dossiers -->
        <div class="section">
            <h2>📁 Permissions des Dossiers</h2>
            <table>
                <tr><th>Dossier</th><th>Existe</th><th>Inscriptible</th><th>Permissions</th></tr>
                <?php foreach ($dir_perms as $name => $info): ?>
                    <tr>
                        <td><?php echo $name; ?></td>
                        <td><?php echo $info['exists'] ? '✅' : '❌'; ?></td>
                        <td><?php echo $info['writable'] ? '✅' : '❌'; ?></td>
                        <td><code><?php echo $info['permissions']; ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Base de Données -->
        <div class="section">
            <h2>🗄️ Base de Données</h2>
            <div class="check-grid">
                <div class="check-card <?php echo $db_conn['connection'] ? 'check-passed' : 'check-failed'; ?>">
                    <div>
                        <span class="check-icon"><?php echo $db_conn['connection'] ? '✅' : '❌'; ?></span>
                        <strong>Connexion</strong>
                    </div>
                    <div style="margin: 8px 0;">
                        <small>État: <?php echo $db_conn['connection'] ? 'Connecté' : 'Échec'; ?></small>
                    </div>
                    <?php if ($db_conn['connection']): ?>
                        <div><small>Temps de requête: <?php echo $db_conn['query_time']; ?>ms</small></div>
                    <?php else: ?>
                        <div><small class="error">Erreur: <?php echo $db_conn['error']; ?></small></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Plugins Actifs -->
        <div class="section">
            <h2>🔧 Plugins Actifs (<?php echo count($active_plugins); ?>)</h2>
            <div class="plugin-list">
                <table>
                    <tr><th>Plugin</th><th>Version</th><th>Auteur</th></tr>
                    <?php foreach ($active_plugins as $plugin): ?>
                        <tr>
                            <td><?php echo $plugin['name']; ?></td>
                            <td><?php echo $plugin['version']; ?></td>
                            <td><?php echo $plugin['author']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- Constantes Importantes -->
        <div class="section">
            <h2>📋 Constantes Importantes</h2>
            <table>
                <tr><th>Constante</th><th>Valeur</th><th>Recommandé</th><th>Description</th></tr>
                <?php foreach ($constants as $name => $info): ?>
                    <tr>
                        <td><code><?php echo $name; ?></code></td>
                        <td><span class="constant-value"><?php echo is_bool($info['value']) ? ($info['value'] ? 'true' : 'false') : $info['value']; ?></span></td>
                        <td><?php echo $info['recommended'] !== null ? '<code>' . $info['recommended'] . '</code>' : 'N/A'; ?></td>
                        <td><?php echo $info['message']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Actions -->
        <div class="section">
            <h2>🛠️ Actions Recommandées</h2>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="btn" onclick="location.reload()">🔄 Actualiser les vérifications</button>
                <button class="btn secondary" onclick="window.open('<?php echo admin_url('site-health.php'); ?>', '_blank')">🏥 Santé du site WordPress</button>
                <button class="btn secondary" onclick="window.open('<?php echo admin_url('plugins.php'); ?>', '_blank')">🔧 Gérer les plugins</button>
            </div>

            <?php if ($overall_score < 85): ?>
                <div class="status warning" style="margin-top: 20px;">
                    <strong>⚠️ Améliorations recommandées :</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php if ($overall_score < 70): ?>
                            <li>Vérifiez les extensions PHP manquantes et contactez votre hébergeur</li>
                            <li>Corrigez les permissions des dossiers inscriptibles</li>
                        <?php endif; ?>
                        <li>Augmentez la limite mémoire si nécessaire (WP_MEMORY_LIMIT)</li>
                        <li>Désactivez WP_DEBUG en production</li>
                        <li>Optimisez les plugins actifs si des conflits sont suspectés</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>