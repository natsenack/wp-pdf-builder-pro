# 🚀 Guide d'Installation

Installation complète et configuration de PDF Builder Pro pour WordPress.

## 📋 Prérequis Système

### Configuration Minimale Requise

| Composant | Version Minimale | Version Recommandée |
|-----------|------------------|-------------------|
| **WordPress** | 5.0 | 6.0+ |
| **PHP** | 7.4 | 8.1+ |
| **MySQL** | 5.7 | 8.0+ |
| **Mémoire PHP** | 128M | 256M+ |
| **Espace Disque** | 100M | 500M+ |

### Extensions PHP Requises

```bash
# Extensions indispensables
php-mbstring     # Support des chaînes multi-octets
php-xml          # Traitement XML
php-zip          # Compression ZIP
php-curl         # Requêtes HTTP
php-gd           # Manipulation d'images
php-intl         # Internationalisation

# Extensions recommandées
php-imagick      # Traitement d'images avancé
php-opcache      # Cache d'opcode
```

### Vérification des Prérequis

```php
// Script de vérification des prérequis
function check_pdf_builder_requirements() {
    $requirements = [
        'wordpress' => [
            'name' => 'WordPress',
            'current' => get_bloginfo('version'),
            'required' => '5.0',
            'status' => version_compare(get_bloginfo('version'), '5.0', '>=')
        ],
        'php' => [
            'name' => 'PHP',
            'current' => PHP_VERSION,
            'required' => '7.4',
            'status' => version_compare(PHP_VERSION, '7.4', '>=')
        ],
        'memory' => [
            'name' => 'Mémoire PHP',
            'current' => ini_get('memory_limit'),
            'required' => '128M',
            'status' => return_bytes(ini_get('memory_limit')) >= return_bytes('128M')
        ]
    ];

    $extensions = [
        'mbstring', 'xml', 'zip', 'curl', 'gd', 'intl'
    ];

    echo '<h2>Vérification des Prérequis PDF Builder Pro</h2>';
    echo '<table class="widefat">';

    foreach ($requirements as $req) {
        $status_icon = $req['status'] ? '✅' : '❌';
        $status_class = $req['status'] ? 'success' : 'error';

        echo "<tr class='{$status_class}'>";
        echo "<td>{$req['name']}</td>";
        echo "<td>{$req['current']}</td>";
        echo "<td>{$req['required']}</td>";
        echo "<td>{$status_icon}</td>";
        echo '</tr>';
    }

    echo '</table>';

    echo '<h3>Extensions PHP</h3>';
    echo '<ul>';

    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $status = $loaded ? '✅' : '❌';
        echo "<li>{$ext}: {$status}</li>";
    }

    echo '</ul>';
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
```

## 📦 Installation

### Méthode 1: Via WordPress Admin

1. **Connexion à l'admin WordPress**
   - Aller dans `Extensions > Ajouter`
   - Rechercher "PDF Builder Pro"

2. **Installation**
   - Cliquer sur "Installer maintenant"
   - Attendre la fin du téléchargement

3. **Activation**
   - Cliquer sur "Activer"
   - Le plugin est maintenant opérationnel

### Méthode 2: Téléchargement Manuel

```bash
# Télécharger la dernière version
wget https://downloads.wordpress.org/plugin/pdf-builder-pro.latest-stable.zip

# Extraire dans le dossier plugins
unzip pdf-builder-pro.latest-stable.zip -d /path/to/wp-content/plugins/

# Activer via WP-CLI
wp plugin activate pdf-builder-pro
```

### Méthode 3: Via Composer

```bash
# Ajouter le repository
composer config repositories.pdf-builder-pro vcs https://github.com/wp-pdf-builder-pro/pdf-builder-pro

# Installer
composer require wp-pdf-builder-pro/pdf-builder-pro

# Activer
wp plugin activate pdf-builder-pro
```

## ⚙️ Configuration de Base

### Configuration Automatique

Après activation, PDF Builder Pro configure automatiquement :

- ✅ Création des tables de base de données
- ✅ Configuration des dossiers de cache
- ✅ Activation de l'API REST
- ✅ Permissions par défaut

### Configuration Manuelle Avancée

```php
// Dans wp-config.php ou functions.php

// Configuration de base
define('PDF_BUILDER_REST_API_ENABLED', true);
define('PDF_BUILDER_ALLOW_PUBLIC_ACCESS', false);

// Sécurité
define('PDF_BUILDER_API_RATE_LIMIT', 100); // Requêtes par heure
define('PDF_BUILDER_MAX_FILE_SIZE', '10MB');
define('PDF_BUILDER_CACHE_TTL', 3600); // 1 heure

// Performance
define('PDF_BUILDER_MEMORY_LIMIT', '256M');
define('PDF_BUILDER_EXECUTION_TIMEOUT', 30);

// Stockage personnalisé
define('PDF_BUILDER_STORAGE_PATH', WP_CONTENT_DIR . '/custom-pdf-storage/');
define('PDF_BUILDER_STORAGE_URL', WP_CONTENT_URL . '/custom-pdf-storage/');

// Base de données
define('PDF_BUILDER_DB_TABLE_PREFIX', 'pdf_builder_');
```

### Configuration via Interface Admin

1. **Accéder aux paramètres**
   - `Réglages > PDF Builder`

2. **Configuration générale**
   - Format de papier par défaut
   - Qualité d'image
   - Cache activé/désactivé

3. **Configuration sécurité**
   - Clés API
   - Permissions utilisateurs
   - Rate limiting

## 🔧 Configuration WooCommerce

### Intégration Automatique

```php
// Hook d'activation pour configurer WooCommerce
register_activation_hook(__FILE__, 'setup_pdf_builder_woocommerce');

function setup_pdf_builder_woocommerce() {
    if (class_exists('WooCommerce')) {
        // Créer des templates par défaut
        create_default_woocommerce_templates();

        // Configurer les hooks
        setup_woocommerce_hooks();

        // Ajouter des options
        add_option('pdf_builder_wc_invoice_template', 1);
        add_option('pdf_builder_wc_quote_template', 2);
        add_option('pdf_builder_auto_generate_invoice', 'yes');
    }
}

function create_default_woocommerce_templates() {
    // Template de facture
    $invoice_template = [
        'name' => 'Facture WooCommerce',
        'description' => 'Template de facture pour WooCommerce',
        'elements' => [
            // En-tête
            [
                'type' => 'text',
                'content' => 'FACTURE',
                'position' => ['x' => 200, 'y' => 20],
                'style' => ['fontSize' => 24, 'fontWeight' => 'bold']
            ],
            // Informations commande
            [
                'type' => 'dynamic-text',
                'content' => 'Commande N°: {{order_number}}',
                'position' => ['x' => 20, 'y' => 60]
            ],
            [
                'type' => 'dynamic-text',
                'content' => 'Date: {{order_date}}',
                'position' => ['x' => 350, 'y' => 60]
            ],
            // Informations client
            [
                'type' => 'dynamic-text',
                'content' => 'Client: {{customer_name}}',
                'position' => ['x' => 20, 'y' => 80]
            ],
            [
                'type' => 'dynamic-text',
                'content' => 'Email: {{customer_email}}',
                'position' => ['x' => 20, 'y' => 95]
            ],
            // Tableau des articles
            [
                'type' => 'table',
                'position' => ['x' => 20, 'y' => 120],
                'columns' => [
                    ['header' => 'Article', 'width' => 200],
                    ['header' => 'Qté', 'width' => 40],
                    ['header' => 'Prix', 'width' => 80],
                    ['header' => 'Total', 'width' => 80]
                ],
                'data' => '{{order_items}}'
            ],
            // Totaux
            [
                'type' => 'dynamic-text',
                'content' => 'Sous-total: {{subtotal}} €',
                'position' => ['x' => 350, 'y' => 200]
            ],
            [
                'type' => 'dynamic-text',
                'content' => 'TVA: {{tax_total}} €',
                'position' => ['x' => 350, 'y' => 215]
            ],
            [
                'type' => 'dynamic-text',
                'content' => 'Livraison: {{shipping_total}} €',
                'position' => ['x' => 350, 'y' => 230]
            ],
            [
                'type' => 'dynamic-text',
                'content' => 'Total: {{order_total}} €',
                'position' => ['x' => 350, 'y' => 250],
                'style' => ['fontSize' => 14, 'fontWeight' => 'bold']
            ]
        ]
    ];

    // Enregistrer le template via API
    wp_remote_post('/wp-json/pdf-builder/v1/templates', [
        'body' => json_encode($invoice_template),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . wp_generate_password(32, false)
        ]
    ]);
}
```

### Configuration des Hooks WooCommerce

```php
// Hooks pour génération automatique
add_action('woocommerce_order_status_completed', 'auto_generate_invoice', 10, 1);
add_action('woocommerce_order_status_processing', 'auto_generate_packing_slip', 10, 1);

function auto_generate_invoice($order_id) {
    if (get_option('pdf_builder_auto_generate_invoice') !== 'yes') {
        return;
    }

    $order = wc_get_order($order_id);

    // Préparer les données
    $order_data = prepare_order_data($order);

    // Générer la facture
    $result = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
        'body' => json_encode([
            'template_id' => get_option('pdf_builder_wc_invoice_template'),
            'data' => $order_data
        ]),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . get_option('pdf_builder_api_key')
        ]
    ]);

    if (!is_wp_error($result)) {
        $response = json_decode(wp_remote_retrieve_body($result));

        if ($response->success) {
            // Sauvegarder l'URL du PDF
            update_post_meta($order_id, '_pdf_invoice_url', $response->pdf_url);

            // Ajouter une note à la commande
            $order->add_order_note('Facture PDF générée: ' . $response->pdf_url);
        }
    }
}

function prepare_order_data($order) {
    $items = [];

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        $items[] = [
            'name' => $item->get_name(),
            'sku' => $product ? $product->get_sku() : '',
            'quantity' => $item->get_quantity(),
            'price' => wc_price($item->get_total() / $item->get_quantity()),
            'total' => wc_price($item->get_total())
        ];
    }

    return [
        'order_number' => $order->get_order_number(),
        'order_date' => $order->get_date_created()->format('d/m/Y'),
        'customer_name' => $order->get_formatted_billing_full_name(),
        'customer_email' => $order->get_billing_email(),
        'customer_address' => $order->get_formatted_billing_address(),
        'order_items' => $items,
        'subtotal' => wc_price($order->get_subtotal()),
        'tax_total' => wc_price($order->get_total_tax()),
        'shipping_total' => wc_price($order->get_shipping_total()),
        'order_total' => wc_price($order->get_total())
    ];
}
```

## 🔐 Configuration Sécurité

### Génération des Clés API

```php
// Générer une clé API sécurisée
function generate_pdf_api_key() {
    $key = wp_generate_password(64, false, false);

    // Sauvegarder dans les options
    update_option('pdf_builder_api_key', $key);

    return $key;
}

// Générer lors de l'activation
register_activation_hook(__FILE__, function() {
    if (!get_option('pdf_builder_api_key')) {
        generate_pdf_api_key();
    }
});
```

### Configuration des Permissions

```php
// Définir les capacités personnalisées
add_action('init', 'register_pdf_builder_capabilities');

function register_pdf_builder_capabilities() {
    $roles = ['administrator', 'editor', 'shop_manager'];

    foreach ($roles as $role_name) {
        $role = get_role($role_name);

        if ($role) {
            // Permissions de base
            $role->add_cap('pdf_builder_create_templates');
            $role->add_cap('pdf_builder_edit_templates');
            $role->add_cap('pdf_builder_delete_templates');

            // Permissions génération
            $role->add_cap('pdf_builder_generate_pdf');
            $role->add_cap('pdf_builder_view_generated_pdfs');

            // Permissions admin (admin seulement)
            if ($role_name === 'administrator') {
                $role->add_cap('pdf_builder_manage_settings');
                $role->add_cap('pdf_builder_view_analytics');
            }
        }
    }
}
```

### Configuration CORS

```php
// Autoriser les requêtes cross-origin pour l'API
add_action('rest_api_init', 'setup_pdf_builder_cors');

function setup_pdf_builder_cors() {
    // Autoriser les origines spécifiques
    $allowed_origins = [
        'https://monsite.com',
        'https://app.monsite.com',
        'http://localhost:3000' // Pour le développement
    ];

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    if (in_array($origin, $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce');
        header('Access-Control-Allow-Credentials: true');
    }
}
```

## 🚀 Test d'Installation

### Script de Test Automatique

```php
// Test complet de l'installation
function run_pdf_builder_installation_test() {
    $tests = [
        'database' => test_database_connection(),
        'filesystem' => test_filesystem_permissions(),
        'api' => test_api_endpoints(),
        'generation' => test_pdf_generation()
    ];

    $all_passed = !in_array(false, $tests);

    echo '<div class="notice ' . ($all_passed ? 'notice-success' : 'notice-error') . '">';
    echo '<h3>Test d\'installation PDF Builder Pro</h3>';

    foreach ($tests as $test_name => $passed) {
        $icon = $passed ? '✅' : '❌';
        echo "<p>{$icon} Test {$test_name}: " . ($passed ? 'Réussi' : 'Échoué') . '</p>';
    }

    if ($all_passed) {
        echo '<p><strong>Installation réussie ! PDF Builder Pro est prêt à être utilisé.</strong></p>';
    } else {
        echo '<p><strong>Des problèmes ont été détectés. Vérifiez la configuration.</strong></p>';
    }

    echo '</div>';
}

function test_database_connection() {
    global $wpdb;
    return $wpdb->check_connection();
}

function test_filesystem_permissions() {
    $upload_dir = wp_upload_dir();
    $test_file = $upload_dir['basedir'] . '/pdf-builder-test.txt';

    // Tester l'écriture
    $write_result = file_put_contents($test_file, 'test');

    // Tester la lecture
    $read_result = file_get_contents($test_file);

    // Nettoyer
    unlink($test_file);

    return $write_result !== false && $read_result === 'test';
}

function test_api_endpoints() {
    $response = wp_remote_get('/wp-json/pdf-builder/v1/templates');
    return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
}

function test_pdf_generation() {
    $test_data = [
        'template_id' => 1,
        'data' => ['test' => 'installation'],
        'test_mode' => true
    ];

    $response = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
        'body' => json_encode($test_data),
        'headers' => [
            'Content-Type' => 'application/json',
            'X-WP-Nonce' => wp_create_nonce('wp_rest')
        ]
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $result = json_decode(wp_remote_retrieve_body($response));
    return isset($result->success) && $result->success;
}

// Exécuter le test
add_action('admin_notices', 'run_pdf_builder_installation_test');
```

### Vérifications Manuelles

1. **Base de données**
   - Vérifier que les tables `wp_pdf_builder_*` existent
   - Contrôler les permissions d'accès

2. **Système de fichiers**
   - Dossier `wp-content/uploads/pdf-builder-cache/` créé et accessible
   - Permissions 755 sur les dossiers

3. **API REST**
   - Endpoint `/wp-json/pdf-builder/v1/` accessible
   - Authentification fonctionnelle

4. **Génération PDF**
   - Créer un template simple
   - Générer un PDF de test
   - Vérifier le téléchargement

## 🔄 Mise à Jour

### Mise à Jour Automatique

PDF Builder Pro supporte les mises à jour automatiques via WordPress :

1. **Notifications**
   - Mises à jour disponibles affichées dans l'admin
   - Possibilité de mise à jour en un clic

2. **Sauvegarde automatique**
   - Templates sauvegardés avant mise à jour
   - Rollback possible en cas de problème

### Mise à Jour Manuelle

```bash
# Sauvegarder les templates
wp pdf-builder export-templates --output=backup-templates.json

# Désactiver le plugin
wp plugin deactivate pdf-builder-pro

# Supprimer l'ancienne version
wp plugin delete pdf-builder-pro

# Installer la nouvelle version
wp plugin install pdf-builder-pro --activate

# Restaurer les templates
wp pdf-builder import-templates backup-templates.json
```

### Migration de Version

```php
// Hook de mise à jour
add_action('upgrader_process_complete', 'pdf_builder_upgrade_completed', 10, 2);

function pdf_builder_upgrade_completed($upgrader_object, $options) {
    if ($options['action'] === 'update' && $options['type'] === 'plugin') {
        foreach ($options['plugins'] as $plugin) {
            if ($plugin === 'pdf-builder-pro/pdf-builder-pro.php') {
                // Exécuter les migrations
                run_pdf_builder_migrations();

                // Vider le cache
                wp_cache_flush();

                // Log la mise à jour
                error_log('PDF Builder Pro mis à jour vers ' . PDF_BUILDER_VERSION);
            }
        }
    }
}

function run_pdf_builder_migrations() {
    $current_version = get_option('pdf_builder_version', '1.0.0');

    // Migration 1.1.0: Ajout du champ metadata
    if (version_compare($current_version, '1.1.0', '<')) {
        migrate_to_1_1_0();
    }

    // Migration 1.2.0: Changement structure base de données
    if (version_compare($current_version, '1.2.0', '<')) {
        migrate_to_1_2_0();
    }

    // Mettre à jour la version
    update_option('pdf_builder_version', PDF_BUILDER_VERSION);
}
```

---

**📖 Voir aussi :**
- [Configuration avancée](../technical/configuration.md)
- [Dépannage](../technical/troubleshooting.md)
- [Tutoriels](../tutorials/)