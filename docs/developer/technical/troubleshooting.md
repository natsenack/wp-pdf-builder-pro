# 🔧 Dépannage

Guide de résolution des problèmes courants avec PDF Builder Pro.

## 🚨 Problèmes Courants

### Erreur "Memory exhausted"

**Symptômes :**
- Erreur PHP : `Fatal error: Allowed memory size exhausted`
- Génération PDF qui échoue sur les gros templates

**Solutions :**

1. **Augmenter la limite mémoire PHP**
   ```php
   // Dans wp-config.php
   define('WP_MEMORY_LIMIT', '256M');
   define('WP_MAX_MEMORY_LIMIT', '512M');

   // Ou dans .htaccess
   php_value memory_limit 256M
   ```

2. **Configuration spécifique PDF Builder**
   ```php
   // Dans functions.php
   add_filter('pdf_builder_memory_limit', function() {
       return '512M';
   });
   ```

3. **Optimiser le template**
   - Réduire le nombre d'images haute résolution
   - Utiliser des polices système au lieu de polices personnalisées
   - Compresser les images avant l'upload

### Erreur "Maximum execution time exceeded"

**Symptômes :**
- Timeout lors de la génération PDF
- Erreur : `Maximum execution time of 30 seconds exceeded`

**Solutions :**

1. **Augmenter le timeout PHP**
   ```php
   // Dans wp-config.php
   set_time_limit(120); // 2 minutes

   // Ou dans .htaccess
   php_value max_execution_time 120
   ```

2. **Configuration asynchrone**
   ```php
   // Activer le traitement asynchrone
   add_filter('pdf_builder_async_generation', '__return_true');

   // Configurer la file d'attente
   add_filter('pdf_builder_queue_config', function($config) {
       $config['workers']['max_runtime'] = 300; // 5 minutes
       return $config;
   });
   ```

3. **Optimiser les templates lourds**
   - Diviser les gros templates en plusieurs pages
   - Pré-calculer les éléments complexes
   - Utiliser le cache pour les éléments répétitifs

### Erreur "TCPDF ERROR"

**Symptômes :**
- Erreurs TCPDF spécifiques
- PDFs corrompus ou vides

**Solutions :**

1. **Vérifier les permissions des dossiers**
   ```bash
   # Corriger les permissions
   chown -R www-data:www-data /path/to/wp-content/uploads/pdf-builder-cache/
   chmod -R 755 /path/to/wp-content/uploads/pdf-builder-cache/
   ```

2. **Vérifier la configuration TCPDF**
   ```php
   add_filter('pdf_builder_tcpdf_config', function($config) {
       return array_merge($config, [
           'disk_cache' => true,
           'allow_local_files' => false,
           'image_scale_ratio' => 1.0, // Réduire pour éviter les erreurs mémoire
       ]);
   });
   ```

3. **Problèmes d'images**
   - Vérifier que les images existent et sont accessibles
   - Convertir les images problématiques en JPG/PNG
   - Redimensionner les images trop grandes

### Erreur API "401 Unauthorized"

**Symptômes :**
- Requêtes API rejetées avec erreur 401
- Problèmes d'authentification

**Solutions :**

1. **Vérifier la clé API**
   ```php
   // Régénérer la clé API
   $new_key = wp_generate_password(64, false);
   update_option('pdf_builder_api_key', $new_key);

   // Afficher la nouvelle clé
   echo 'Nouvelle clé API: ' . $new_key;
   ```

2. **Vérifier les permissions utilisateur**
   ```php
   // Vérifier les capacités de l'utilisateur actuel
   $user = wp_get_current_user();
   if (user_can($user, 'pdf_builder_generate_pdf')) {
       echo 'Utilisateur autorisé';
   } else {
       echo 'Utilisateur non autorisé';
   }
   ```

3. **Configuration CORS**
   ```php
   // Ajouter les origines autorisées
   add_filter('allowed_http_origins', function($origins) {
       $origins[] = 'https://mondomaine.com';
       $origins[] = 'http://localhost:3000';
       return $origins;
   });
   ```

## 🔍 Outils de Diagnostic

### Script de Diagnostic Automatique

```php
// Script de diagnostic complet
function pdf_builder_diagnostic() {
    echo '<div class="wrap">';
    echo '<h1>Diagnostic PDF Builder Pro</h1>';

    // Tests système
    run_system_tests();

    // Tests base de données
    run_database_tests();

    // Tests API
    run_api_tests();

    // Tests génération
    run_generation_tests();

    echo '</div>';
}

function run_system_tests() {
    echo '<h2>Tests Système</h2>';
    echo '<table class="widefat">';

    // Test mémoire
    $memory_limit = ini_get('memory_limit');
    $status = return_bytes($memory_limit) >= return_bytes('128M') ? '✅' : '❌';
    echo "<tr><td>Limite mémoire PHP</td><td>{$memory_limit}</td><td>{$status}</td></tr>";

    // Test temps d'exécution
    $time_limit = ini_get('max_execution_time');
    $status = $time_limit >= 30 || $time_limit == 0 ? '✅' : '❌';
    echo "<tr><td>Timeout PHP</td><td>{$time_limit}s</td><td>{$status}</td></tr>";

    // Test extensions
    $extensions = ['mbstring', 'xml', 'zip', 'curl', 'gd'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $status = $loaded ? '✅' : '❌';
        echo "<tr><td>Extension {$ext}</td><td>" . ($loaded ? 'Chargée' : 'Manquante') . "</td><td>{$status}</td></tr>";
    }

    echo '</table>';
}

function run_database_tests() {
    global $wpdb;

    echo '<h2>Tests Base de Données</h2>';
    echo '<table class="widefat">';

    // Test connexion
    $connected = $wpdb->check_connection();
    $status = $connected ? '✅' : '❌';
    echo "<tr><td>Connexion DB</td><td>" . ($connected ? 'OK' : 'Échec') . "</td><td>{$status}</td></tr>";

    // Test tables
    $tables = [
        'pdf_builder_templates' => 'Templates',
        'pdf_builder_pdfs' => 'PDFs générés',
        'pdf_builder_metrics' => 'Métriques'
    ];

    foreach ($tables as $table => $name) {
        $exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table}'");
        $status = $exists ? '✅' : '❌';
        echo "<tr><td>Table {$name}</td><td>" . ($exists ? 'Existe' : 'Manquante') . "</td><td>{$status}</td></tr>";
    }

    echo '</table>';
}

function run_api_tests() {
    echo '<h2>Tests API</h2>';

    // Test endpoint templates
    $response = wp_remote_get('/wp-json/pdf-builder/v1/templates');
    $code = wp_remote_retrieve_response_code($response);
    $status = $code === 200 ? '✅' : '❌';
    echo "<p>Endpoint Templates: {$status} (Code: {$code})</p>";

    // Test génération
    $test_data = [
        'template_id' => 1,
        'data' => ['test' => 'diagnostic']
    ];

    $response = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
        'body' => json_encode($test_data),
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WP-Nonce' => wp_create_nonce('wp_rest')
        ]
    ]);

    $code = wp_remote_retrieve_response_code($response);
    $status = $code === 200 ? '✅' : '❌';
    echo "<p>Endpoint Génération: {$status} (Code: {$code})</p>";
}

function run_generation_tests() {
    echo '<h2>Tests Génération PDF</h2>';

    try {
        // Créer un template de test simple
        $template_data = [
            'name' => 'Template Test Diagnostic',
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'Test de génération PDF',
                    'position' => ['x' => 50, 'y' => 50],
                    'style' => ['fontSize' => 16]
                ]
            ]
        ];

        $response = wp_remote_post('/wp-json/pdf-builder/v1/templates', [
            'body' => json_encode($template_data),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-WP-Nonce' => wp_create_nonce('wp_rest')
            ]
        ]);

        $result = json_decode(wp_remote_retrieve_body($response));

        if ($result && $result->success) {
            echo '<p>✅ Création template test: Succès</p>';

            // Tester la génération
            $gen_response = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
                'body' => json_encode([
                    'template_id' => $result->template->id,
                    'data' => []
                ]),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-WP-Nonce' => wp_create_nonce('wp_rest')
                ]
            ]);

            $gen_result = json_decode(wp_remote_retrieve_body($gen_response));

            if ($gen_result && $gen_result->success) {
                echo '<p>✅ Génération PDF test: Succès</p>';
                echo '<p>URL du PDF: <a href="' . $gen_result->pdf_url . '" target="_blank">' . $gen_result->pdf_url . '</a></p>';
            } else {
                echo '<p>❌ Génération PDF test: Échec</p>';
                echo '<pre>' . print_r($gen_result, true) . '</pre>';
            }
        } else {
            echo '<p>❌ Création template test: Échec</p>';
            echo '<pre>' . print_r($result, true) . '</pre>';
        }

    } catch (Exception $e) {
        echo '<p>❌ Erreur lors des tests: ' . $e->getMessage() . '</p>';
    }
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;

    switch($last) {
        case 'g': $val *= 1024 * 1024 * 1024; break;
        case 'm': $val *= 1024 * 1024; break;
        case 'k': $val *= 1024; break;
    }

    return $val;
}

// Ajouter la page de diagnostic
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'Diagnostic PDF Builder',
        'Diagnostic PDF',
        'manage_options',
        'pdf-builder-diagnostic',
        'pdf_builder_diagnostic'
    );
});
```

### Logs Détaillés

```php
// Activer les logs détaillés pour le débogage
add_filter('pdf_builder_log_config', function($config) {
    return array_merge($config, [
        'level' => 'DEBUG',
        'handlers' => [
            'file' => [
                'enabled' => true,
                'path' => WP_CONTENT_DIR . '/debug-pdf-builder.log',
                'max_size' => '50MB'
            ]
        ]
    ]);
});

// Fonction pour afficher les logs récents
function show_recent_logs() {
    $log_file = WP_CONTENT_DIR . '/debug-pdf-builder.log';

    if (file_exists($log_file)) {
        $logs = file($log_file);
        $recent_logs = array_slice($logs, -50); // Derniers 50 logs

        echo '<h3>Logs récents</h3>';
        echo '<pre style="background: #f5f5f5; padding: 10px; max-height: 400px; overflow: auto;">';
        foreach ($recent_logs as $log) {
            echo htmlspecialchars($log);
        }
        echo '</pre>';
    } else {
        echo '<p>Aucun log trouvé.</p>';
    }
}
```

## 🛠️ Solutions Avancées

### Problèmes de Performance

**Génération lente :**

```php
// Optimisations de performance
add_filter('pdf_builder_performance_config', function($config) {
    return array_merge($config, [
        // Cache agressif
        'cache' => [
            'templates' => 7200, // 2h
            'pdfs' => 3600,      // 1h
            'images' => 86400    // 24h
        ],

        // Optimisations TCPDF
        'tcpdf' => [
            'disk_cache' => true,
            'image_cache' => true,
            'font_cache' => true
        ],

        // Traitement par lots
        'batch_processing' => [
            'enabled' => true,
            'batch_size' => 10,
            'parallel_processing' => true
        ]
    ]);
});
```

**Mémoire excessive :**

```php
// Gestion optimisée de la mémoire
class MemoryOptimizedPDFGenerator extends PDF_Generator {

    public function generate($template, $data) {
        // Libérer la mémoire avant génération
        $this->cleanupMemory();

        // Générer par chunks
        $chunks = $this->splitIntoChunks($template->getElements());

        foreach ($chunks as $chunk) {
            $this->processChunk($chunk, $data);
            $this->cleanupMemory(); // Libérer après chaque chunk
        }

        return $this->finalizePDF();
    }

    private function cleanupMemory() {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        // Libérer les variables temporaires
        unset($temp_variables);
    }

    private function splitIntoChunks($elements, $chunk_size = 50) {
        return array_chunk($elements, $chunk_size);
    }
}
```

### Problèmes de Cache

**Cache corrompu :**

```php
// Fonction de nettoyage du cache
function clear_pdf_builder_cache() {
    global $wpdb;

    // Vider le cache WordPress
    wp_cache_flush();

    // Vider le cache des transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

    // Vider le cache fichier
    $cache_dir = WP_CONTENT_DIR . '/cache/pdf-builder/';
    if (is_dir($cache_dir)) {
        $this->deleteDirectory($cache_dir);
        mkdir($cache_dir, 0755, true);
    }

    // Vider le cache Redis (si utilisé)
    if (class_exists('Redis')) {
        $redis = new Redis();
        if ($redis->connect('127.0.0.1', 6379)) {
            $redis->flushDB();
        }
    }

    wp_die('Cache PDF Builder vidé avec succès.');
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return;

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
    }

    rmdir($dir);
}
```

### Problèmes de Base de Données

**Tables corrompues :**

```php
// Réparation des tables
function repair_pdf_builder_tables() {
    global $wpdb;

    $tables = [
        'pdf_builder_templates',
        'pdf_builder_pdfs',
        'pdf_builder_metrics'
    ];

    foreach ($tables as $table) {
        $full_table_name = $wpdb->prefix . $table;

        // Réparer la table
        $wpdb->query("REPAIR TABLE {$full_table_name}");

        // Optimiser la table
        $wpdb->query("OPTIMIZE TABLE {$full_table_name}");

        // Vérifier la table
        $result = $wpdb->get_row("CHECK TABLE {$full_table_name}");

        echo "<p>Table {$table}: {$result->Msg_text}</p>";
    }
}
```

**Deadlocks :**

```php
// Gestion des deadlocks avec retry
class DeadlockSafeDatabase {

    public function executeWithRetry($query, $max_retries = 3) {
        $attempts = 0;

        while ($attempts < $max_retries) {
            try {
                $result = $this->db->query($query);
                return $result;
            } catch (Exception $e) {
                if ($this->isDeadlockException($e) && $attempts < $max_retries - 1) {
                    $attempts++;
                    usleep(rand(100000, 500000)); // Attendre 0.1-0.5 secondes
                    continue;
                }
                throw $e;
            }
        }
    }

    private function isDeadlockException($e) {
        return strpos($e->getMessage(), 'Deadlock found') !== false ||
               strpos($e->getMessage(), 'Lock wait timeout') !== false;
    }
}
```

## 🚨 Alertes et Monitoring

### Système d'Alertes

```php
// Configuration des alertes
add_filter('pdf_builder_alert_config', function($config) {
    return [
        'enabled' => true,
        'email_recipients' => [get_option('admin_email')],
        'alerts' => [
            'memory_exhausted' => [
                'enabled' => true,
                'threshold' => 80, // pourcentage
                'cooldown' => 3600 // 1 heure entre les alertes
            ],
            'generation_timeout' => [
                'enabled' => true,
                'threshold' => 60, // secondes
                'cooldown' => 1800
            ],
            'error_rate' => [
                'enabled' => true,
                'threshold' => 10, // pourcentage
                'cooldown' => 3600
            ],
            'disk_space' => [
                'enabled' => true,
                'threshold' => 90, // pourcentage
                'cooldown' => 86400 // 1 jour
            ]
        ]
    ];
});

// Classe de monitoring
class PDF_Monitor {

    private $alerts_sent = [];

    public function checkSystemHealth() {
        // Vérifier la mémoire
        $memory_usage = $this->getMemoryUsage();
        if ($memory_usage > 80) {
            $this->sendAlert('memory_exhausted', "Utilisation mémoire: {$memory_usage}%");
        }

        // Vérifier l'espace disque
        $disk_usage = $this->getDiskUsage();
        if ($disk_usage > 90) {
            $this->sendAlert('disk_space', "Espace disque: {$disk_usage}%");
        }

        // Vérifier le taux d'erreur
        $error_rate = $this->getErrorRate();
        if ($error_rate > 10) {
            $this->sendAlert('error_rate', "Taux d'erreur: {$error_rate}%");
        }
    }

    private function sendAlert($type, $message) {
        $cooldown = $this->getAlertCooldown($type);

        if (!isset($this->alerts_sent[$type]) ||
            (time() - $this->alerts_sent[$type]) > $cooldown) {

            $subject = "Alerte PDF Builder: {$type}";
            $recipients = get_option('pdf_builder_alert_recipients', [get_option('admin_email')]);

            wp_mail($recipients, $subject, $message);

            $this->alerts_sent[$type] = time();
        }
    }

    private function getMemoryUsage() {
        return (memory_get_peak_usage(true) / ini_get('memory_limit')) * 100;
    }

    private function getDiskUsage() {
        $path = WP_CONTENT_DIR;
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        return (($total - $free) / $total) * 100;
    }

    private function getErrorRate() {
        global $wpdb;

        $hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $errors = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_audit_log
            WHERE level = 'ERROR' AND timestamp > %s
        ", $hour_ago));

        $total = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_audit_log
            WHERE timestamp > %s
        ", $hour_ago));

        return $total > 0 ? ($errors / $total) * 100 : 0;
    }
}
```

---

**📖 Voir aussi :**
- [Installation](../tutorials/installation.md)
- [Configuration avancée](../technical/configuration.md)
- [Sécurité](../technical/security.md)