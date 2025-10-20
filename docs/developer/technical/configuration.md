# ⚙️ Configuration Avancée

Configuration approfondie et optimisation de PDF Builder Pro.

## 🔧 Constantes de Configuration

### Configuration de Base

```php
// Dans wp-config.php

// === CONFIGURATION DE BASE ===
define('PDF_BUILDER_REST_API_ENABLED', true);
define('PDF_BUILDER_ALLOW_PUBLIC_ACCESS', false);
define('PDF_BUILDER_VERSION', '1.0.0');

// === SÉCURITÉ ===
define('PDF_BUILDER_API_RATE_LIMIT', 100); // Requêtes par heure
define('PDF_BUILDER_MAX_FILE_SIZE', '10MB');
define('PDF_BUILDER_CACHE_TTL', 3600); // 1 heure
define('PDF_BUILDER_SESSION_TIMEOUT', 3600); // 1 heure

// === PERFORMANCE ===
define('PDF_BUILDER_MEMORY_LIMIT', '256M');
define('PDF_BUILDER_EXECUTION_TIMEOUT', 30); // secondes
define('PDF_BUILDER_MAX_CONCURRENT_JOBS', 5);

// === STOCKAGE ===
define('PDF_BUILDER_STORAGE_PATH', WP_CONTENT_DIR . '/pdf-builder-storage/');
define('PDF_BUILDER_STORAGE_URL', WP_CONTENT_URL . '/pdf-builder-storage/');
define('PDF_BUILDER_MAX_STORAGE_SIZE', '1GB');

// === BASE DE DONNÉES ===
define('PDF_BUILDER_DB_TABLE_PREFIX', 'pdf_builder_');
define('PDF_BUILDER_DB_CHARSET', 'utf8mb4');
define('PDF_BUILDER_DB_COLLATE', 'utf8mb4_unicode_ci');

// === LOGGING ===
define('PDF_BUILDER_LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('PDF_BUILDER_LOG_MAX_SIZE', '10MB');
define('PDF_BUILDER_LOG_RETENTION', 30); // jours
```

### Configuration TCPDF

```php
// Configuration avancée de TCPDF
add_filter('pdf_builder_tcpdf_config', 'custom_tcpdf_config');

function custom_tcpdf_config($config) {
    return array_merge($config, [
        // Configuration générale
        'creator' => 'Mon Entreprise',
        'author' => get_bloginfo('name'),
        'subject' => 'Document généré automatiquement',

        // Polices
        'default_font' => 'dejavusans',
        'default_font_size' => 10,
        'default_font_monospaced' => 'dejavusansmono',

        // Marges par défaut (mm)
        'margin_top' => 15,
        'margin_right' => 15,
        'margin_bottom' => 15,
        'margin_left' => 15,
        'margin_header' => 5,
        'margin_footer' => 10,

        // Images
        'image_scale_ratio' => 1.25,
        'image_compression_quality' => 75,

        // Sécurité
        'allow_local_files' => false,
        'enable_disk_cache' => true,
        'disk_cache_size' => 10 * 1024 * 1024, // 10MB

        // Performance
        'max_image_resolution' => 300, // DPI
        'jpeg_quality' => 75,
        'png_compression' => 9
    ]);
}
```

## 🗄️ Configuration Base de Données

### Optimisation des Tables

```sql
-- Optimisation des tables pour de gros volumes
ALTER TABLE wp_pdf_builder_templates
    ADD INDEX idx_status_created (status, created_at),
    ADD INDEX idx_name (name(50)),
    ADD FULLTEXT idx_description (description);

ALTER TABLE wp_pdf_builder_pdfs
    ADD INDEX idx_template_date (template_id, generated_at),
    ADD INDEX idx_user_date (generated_by, generated_at),
    ADD INDEX idx_expires (expires_at),
    ADD INDEX idx_size (file_size);

-- Partitionnement pour les gros volumes (MySQL 5.6+)
ALTER TABLE wp_pdf_builder_metrics
    PARTITION BY RANGE (YEAR(created_at)) (
        PARTITION p2023 VALUES LESS THAN (2024),
        PARTITION p2024 VALUES LESS THAN (2025),
        PARTITION p_future VALUES LESS THAN MAXVALUE
    );

-- Configuration InnoDB pour les performances
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL innodb_log_file_size = 268435456;    -- 256MB
SET GLOBAL innodb_flush_log_at_trx_commit = 2;  -- Performance vs Durabilité
```

### Configuration de Connexion

```php
// Configuration avancée de la base de données
add_filter('pdf_builder_db_config', 'custom_db_config');

function custom_db_config($config) {
    global $wpdb;

    return array_merge($config, [
        // Connexion persistante
        'persistent' => true,

        // Timeout de connexion
        'connect_timeout' => 5,
        'read_timeout' => 30,

        // Pool de connexions
        'max_connections' => 10,
        'min_connections' => 2,

        // Cache des requêtes préparées
        'prepared_statement_cache_size' => 100,

        // Compression des données
        'compress' => true,

        // SSL pour les connexions sécurisées
        'ssl' => [
            'ca' => '/path/to/ca.pem',
            'cert' => '/path/to/client-cert.pem',
            'key' => '/path/to/client-key.pem'
        ]
    ]);
}
```

## 💾 Configuration du Cache

### Cache Multi-Niveau

```php
// Configuration du système de cache
add_filter('pdf_builder_cache_config', 'custom_cache_config');

function custom_cache_config($config) {
    return [
        // Cache WordPress Object Cache
        'wordpress' => [
            'enabled' => true,
            'ttl' => 3600,
            'groups' => ['templates', 'pdfs', 'analytics']
        ],

        // Cache Redis
        'redis' => [
            'enabled' => true,
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'database' => 1,
            'ttl' => 7200,
            'serializer' => Redis::SERIALIZER_PHP
        ],

        // Cache fichier
        'file' => [
            'enabled' => true,
            'path' => WP_CONTENT_DIR . '/cache/pdf-builder/',
            'ttl' => 86400, // 24h
            'max_size' => '100MB',
            'compression' => true
        ],

        // Cache mémoire (APCu)
        'apcu' => [
            'enabled' => extension_loaded('apcu'),
            'ttl' => 1800,
            'prefix' => 'pdf_builder_'
        ]
    ];
}

// Stratégies de cache par type de données
add_filter('pdf_builder_cache_strategies', 'custom_cache_strategies');

function custom_cache_strategies($strategies) {
    return [
        'templates' => [
            'ttl' => 3600, // 1h - templates changent peu
            'invalidation' => 'manual', // Invalidation manuelle
            'warmup' => true // Préchargement au démarrage
        ],
        'pdf_urls' => [
            'ttl' => 86400, // 24h - URLs de téléchargement
            'invalidation' => 'time', // Invalidation temporelle
            'warmup' => false
        ],
        'analytics' => [
            'ttl' => 300, // 5min - données fréquemment mises à jour
            'invalidation' => 'manual',
            'warmup' => false
        ],
        'user_permissions' => [
            'ttl' => 1800, // 30min - permissions utilisateur
            'invalidation' => 'user_action', // Invalidation sur action user
            'warmup' => false
        ]
    ];
}
```

### Cache des Templates

```php
// Cache intelligent des templates
class TemplateCache {

    private $cache_manager;

    public function __construct() {
        $this->cache_manager = new CacheManager();
    }

    /**
     * Cache conditionnel basé sur la fraîcheur des données
     */
    public function getTemplateWithSmartCache($template_id) {
        $cache_key = "template_{$template_id}";
        $template = $this->cache_manager->get($cache_key, 'templates');

        if ($template) {
            // Vérifier si le template a été modifié récemment
            $last_modified = $this->getTemplateLastModified($template_id);
            $cache_time = $this->cache_manager->getCacheTime($cache_key, 'templates');

            if ($last_modified > $cache_time) {
                // Cache périmé, recharger
                $template = $this->loadFreshTemplate($template_id);
                $this->cache_manager->set($cache_key, $template, 'templates');
            }
        } else {
            // Pas en cache, charger
            $template = $this->loadFreshTemplate($template_id);
            $this->cache_manager->set($cache_key, $template, 'templates');
        }

        return $template;
    }

    /**
     * Préchargement des templates fréquents
     */
    public function warmupFrequentTemplates() {
        global $wpdb;

        $frequent_ids = $wpdb->get_col($wpdb->prepare("
            SELECT template_id
            FROM {$wpdb->prefix}pdf_builder_pdfs
            WHERE generated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY template_id
            ORDER BY COUNT(*) DESC
            LIMIT 20
        "));

        foreach ($frequent_ids as $template_id) {
            $this->getTemplateWithSmartCache($template_id);
        }
    }

    /**
     * Invalidation intelligente du cache
     */
    public function invalidateTemplateCache($template_id, $reason = 'update') {
        $cache_key = "template_{$template_id}";

        // Invalidation du cache principal
        $this->cache_manager->delete($cache_key, 'templates');

        // Invalidation des caches dérivés
        $this->invalidateDerivedCaches($template_id);

        // Log de l'invalidation
        $this->logCacheInvalidation($template_id, $reason);
    }
}
```

## 🔄 Configuration des Files d'Attente

### File d'Attente Asynchrone

```php
// Configuration des files d'attente
add_filter('pdf_builder_queue_config', 'custom_queue_config');

function custom_queue_config($config) {
    return [
        // Type de file d'attente
        'driver' => 'database', // database, redis, rabbitmq

        // Configuration Database
        'database' => [
            'table' => $wpdb->prefix . 'pdf_builder_queue',
            'max_attempts' => 3,
            'retry_delay' => 60 // secondes
        ],

        // Configuration Redis
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'queue' => 'pdf_builder_jobs',
            'timeout' => 30
        ],

        // Workers
        'workers' => [
            'max_workers' => 5,
            'max_runtime' => 300, // 5 minutes
            'memory_limit' => '128M',
            'sleep_time' => 5 // secondes entre les vérifications
        ],

        // Priorités
        'priorities' => [
            'high' => 100,
            'normal' => 50,
            'low' => 10
        ],

        // Monitoring
        'monitoring' => [
            'enabled' => true,
            'metrics_retention' => 7 // jours
        ]
    ];
}

// Gestionnaire de file d'attente personnalisé
class CustomPDFQueue extends PDF_Queue_Manager {

    /**
     * Traitement par lots pour optimiser les performances
     */
    public function processBatch($jobs) {
        // Grouper par type de job
        $grouped_jobs = $this->groupJobsByType($jobs);

        foreach ($grouped_jobs as $type => $type_jobs) {
            switch ($type) {
                case 'pdf_generation':
                    $this->processPDFGenerationBatch($type_jobs);
                    break;
                case 'template_processing':
                    $this->processTemplateBatch($type_jobs);
                    break;
                case 'cleanup':
                    $this->processCleanupBatch($type_jobs);
                    break;
            }
        }
    }

    /**
     * Traitement optimisé des générations PDF
     */
    private function processPDFGenerationBatch($jobs) {
        // Grouper par template pour réduire les chargements
        $template_groups = [];

        foreach ($jobs as $job) {
            $template_id = $job->data['template_id'];
            $template_groups[$template_id][] = $job;
        }

        foreach ($template_groups as $template_id => $group_jobs) {
            // Charger le template une seule fois
            $template = $this->template_manager->getTemplate($template_id);

            // Traiter tous les jobs du groupe
            foreach ($group_jobs as $job) {
                try {
                    $pdf = $this->pdf_manager->generatePDF($template, $job->data);
                    $this->completeJob($job, $pdf);
                } catch (Exception $e) {
                    $this->failJob($job, $e);
                }
            }
        }
    }
}
```

## 🔒 Configuration Sécurité Avancée

### Chiffrement des Données

```php
// Configuration du chiffrement
add_filter('pdf_builder_encryption_config', 'custom_encryption_config');

function custom_encryption_config($config) {
    return [
        // Algorithme de chiffrement
        'algorithm' => 'AES-256-GCM',

        // Clés de chiffrement
        'keys' => [
            'data' => defined('PDF_BUILDER_ENCRYPTION_KEY')
                ? PDF_BUILDER_ENCRYPTION_KEY
                : wp_generate_password(32, true, true),

            'files' => defined('PDF_BUILDER_FILE_ENCRYPTION_KEY')
                ? PDF_BUILDER_FILE_ENCRYPTION_KEY
                : wp_generate_password(32, true, true)
        ],

        // Données à chiffrer
        'encrypt_data' => [
            'user_personal_data' => true,
            'sensitive_template_data' => true,
            'api_keys' => true
        ],

        // Chiffrement des fichiers
        'encrypt_files' => [
            'enabled' => false, // Activer seulement si nécessaire
            'method' => 'zip_crypto', // zip_crypto, openssl
            'password' => wp_generate_password(16, true)
        ],

        // Gestion des clés
        'key_rotation' => [
            'enabled' => true,
            'interval' => 90, // jours
            'keep_old_keys' => 3 // garder 3 anciennes clés
        ]
    ];
}

// Chiffrement des données sensibles
class DataEncryptor {

    private $cipher;
    private $key;

    public function __construct() {
        $this->cipher = 'AES-256-GCM';
        $this->key = $this->getEncryptionKey();
    }

    /**
     * Chiffre des données
     */
    public function encrypt($data) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            json_encode($data),
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * Déchiffre des données
     */
    public function decrypt($encrypted_data) {
        $data = base64_decode($encrypted_data);
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);

        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return json_decode($decrypted, true);
    }

    private function getEncryptionKey() {
        $key = get_option('pdf_builder_encryption_key');

        if (!$key) {
            $key = wp_generate_password(32, true, true);
            update_option('pdf_builder_encryption_key', $key);
        }

        return $key;
    }
}
```

### Audit et Logging

```php
// Configuration du système d'audit
add_filter('pdf_builder_audit_config', 'custom_audit_config');

function custom_audit_config($config) {
    return [
        // Niveau de logging
        'log_level' => 'INFO', // DEBUG, INFO, WARNING, ERROR

        // Destinations des logs
        'handlers' => [
            'file' => [
                'enabled' => true,
                'path' => WP_CONTENT_DIR . '/logs/pdf-builder.log',
                'max_size' => '10MB',
                'max_files' => 5
            ],
            'database' => [
                'enabled' => true,
                'table' => $wpdb->prefix . 'pdf_builder_audit_log',
                'retention' => 90 // jours
            ],
            'external' => [
                'enabled' => false,
                'service' => 'logstash',
                'host' => 'localhost',
                'port' => 5044
            ]
        ],

        // Événements à logger
        'events' => [
            'user_actions' => true,
            'api_calls' => true,
            'pdf_generation' => true,
            'security_events' => true,
            'performance_metrics' => true,
            'errors' => true
        ],

        // Format des logs
        'format' => [
            'timestamp' => 'c', // ISO 8601
            'include_user' => true,
            'include_ip' => true,
            'include_user_agent' => true,
            'include_request_id' => true
        ],

        // Alertes
        'alerts' => [
            'enabled' => true,
            'email' => get_option('admin_email'),
            'events' => ['security_events', 'errors'],
            'thresholds' => [
                'error_rate' => 10, // erreurs par minute
                'security_events' => 5 // événements par heure
            ]
        ]
    ];
}

// Classe d'audit personnalisée
class PDF_Audit_Logger {

    public function logEvent($event_type, $data, $level = 'INFO') {
        $log_entry = [
            'timestamp' => current_time('c'),
            'event_type' => $event_type,
            'level' => $level,
            'user_id' => get_current_user_id(),
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_id' => $this->generateRequestId(),
            'data' => $data
        ];

        // Log dans toutes les destinations configurées
        $this->logToFile($log_entry);
        $this->logToDatabase($log_entry);
        $this->checkAlerts($log_entry);
    }

    private function logToFile($entry) {
        $log_file = WP_CONTENT_DIR . '/logs/pdf-builder.log';
        $log_line = json_encode($entry) . PHP_EOL;

        // Rotation automatique des logs
        if (file_exists($log_file) && filesize($log_file) > 10 * 1024 * 1024) {
            $this->rotateLogFile($log_file);
        }

        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }

    private function rotateLogFile($log_file) {
        $max_files = 5;

        // Décaler les anciens fichiers
        for ($i = $max_files - 1; $i >= 1; $i--) {
            $old_file = $log_file . '.' . $i;
            $new_file = $log_file . '.' . ($i + 1);

            if (file_exists($old_file)) {
                rename($old_file, $new_file);
            }
        }

        // Renommer le fichier actuel
        rename($log_file, $log_file . '.1');
    }
}
```

## 📊 Configuration Monitoring

### Métriques et Alertes

```php
// Configuration du monitoring
add_filter('pdf_builder_monitoring_config', 'custom_monitoring_config');

function custom_monitoring_config($config) {
    return [
        // Métriques système
        'system_metrics' => [
            'enabled' => true,
            'interval' => 60, // secondes
            'metrics' => [
                'cpu_usage' => true,
                'memory_usage' => true,
                'disk_usage' => true,
                'db_connections' => true
            ]
        ],

        // Métriques application
        'app_metrics' => [
            'enabled' => true,
            'metrics' => [
                'pdf_generation_count' => true,
                'pdf_generation_time' => true,
                'api_response_time' => true,
                'error_rate' => true,
                'cache_hit_rate' => true,
                'queue_size' => true
            ]
        ],

        // Seuils d'alerte
        'alerts' => [
            'memory_usage' => 80, // pourcentage
            'cpu_usage' => 70,
            'error_rate' => 5, // pourcentage
            'response_time' => 5000, // millisecondes
            'queue_size' => 100 // jobs en attente
        ],

        // Notifications
        'notifications' => [
            'email' => [
                'enabled' => true,
                'recipients' => [get_option('admin_email')],
                'threshold' => 'warning' // warning, error, critical
            ],
            'slack' => [
                'enabled' => false,
                'webhook_url' => '',
                'channel' => '#pdf-builder-alerts'
            ]
        ],

        // Stockage des métriques
        'storage' => [
            'retention' => 30, // jours
            'aggregation' => [
                'hourly' => 24, // heures
                'daily' => 30,   // jours
                'monthly' => 12  // mois
            ]
        ]
    ];
}

// Collecteur de métriques personnalisé
class PDF_Metrics_Collector {

    private $metrics = [];

    public function collectSystemMetrics() {
        $this->metrics['cpu_usage'] = $this->getCPUUsage();
        $this->metrics['memory_usage'] = $this->getMemoryUsage();
        $this->metrics['disk_usage'] = $this->getDiskUsage();
        $this->metrics['db_connections'] = $this->getDBConnections();
    }

    public function collectAppMetrics() {
        global $wpdb;

        // Métriques de génération PDF
        $this->metrics['pdf_generation_count'] = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_pdfs
            WHERE generated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");

        // Temps moyen de génération
        $this->metrics['pdf_generation_time'] = $wpdb->get_var("
            SELECT AVG(TIMESTAMPDIFF(SECOND, generated_at, NOW()))
            FROM {$wpdb->prefix}pdf_builder_pdfs
            WHERE generated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");

        // Taux d'erreur
        $error_count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_audit_log
            WHERE level = 'ERROR' AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");

        $total_requests = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_audit_log
            WHERE timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");

        $this->metrics['error_rate'] = $total_requests > 0
            ? ($error_count / $total_requests) * 100
            : 0;
    }

    private function getCPUUsage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? 0;
        }
        return 0;
    }

    private function getMemoryUsage() {
        return memory_get_peak_usage(true) / 1024 / 1024; // MB
    }

    private function getDiskUsage() {
        $path = WP_CONTENT_DIR;
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        return $total > 0 ? (($total - $free) / $total) * 100 : 0;
    }

    private function getDBConnections() {
        global $wpdb;
        return $wpdb->num_queries ?? 0;
    }
}
```

---

**📖 Voir aussi :**
- [Architecture système](../technical/architecture.md)
- [Sécurité](../technical/security.md)
- [Dépannage](../technical/troubleshooting.md)