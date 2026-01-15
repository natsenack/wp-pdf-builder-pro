# 📚 Documentation Développeur - PDF Builder Pro

Bienvenue dans la documentation complète pour les développeurs de PDF Builder Pro, le plugin WordPress ultime pour la génération de PDF personnalisés.

## 🚀 Démarrage Rapide

### Installation en 5 Minutes

```bash
# 1. Installation via Composer (recommandé)
composer require wp-pdf-builder-pro/pdf-builder-pro

# 2. Activation
wp plugin activate pdf-builder-pro

# 3. Premier template
curl -X POST /wp-json/pdf-builder/v1/templates \
  -H "Content-Type: application/json" \
  -d '{"name":"Hello World","elements":[{"type":"text","content":"Hello PDF!","position":{"x":50,"y":50}}]}'

# 4. Génération PDF
curl -X POST /wp-json/pdf-builder/v1/generate \
  -H "Content-Type: application/json" \
  -d '{"template_id":1,"data":{}}'
```

## 📖 Table des Matières

### 🏗️ Architecture & Concepts
- **[Architecture Système](./technical/architecture.md)** - Vue d'ensemble technique
- **[Configuration Avancée](./technical/configuration.md)** - Paramétrage approfondi
- **[Sécurité](./technical/security.md)** - Authentification et autorisations
- **[Dépannage](./technical/troubleshooting.md)** - Résolution des problèmes

### 🔧 API & Intégration
- **[Endpoints API](./api/endpoints.md)** - Référence complète des API REST
- **[Authentification](./api/authentication.md)** - Guide d'authentification
- **[Exemples d'Usage](./api/examples.md)** - Cas d'usage pratiques

### 📚 Tutoriels
- **[Installation](./tutorials/installation.md)** - Guide d'installation détaillé
- **[Tutoriels Développeur](./tutorials/index.md)** - Guides étape par étape

## 🎯 Cas d'Usage Courants

### WooCommerce - Factures Automatiques

```php
// Hook sur nouvelle commande
add_action('woocommerce_order_status_completed', 'generate_invoice_pdf', 10, 1);

function generate_invoice_pdf($order_id) {
    $order = wc_get_order($order_id);

    // Générer la facture
    $response = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
        'body' => json_encode([
            'template_id' => get_option('invoice_template_id'),
            'data' => [
                'order_number' => $order->get_order_number(),
                'customer_name' => $order->get_formatted_billing_full_name(),
                'total' => $order->get_total()
            ]
        ]),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . get_option('pdf_builder_api_key')
        ]
    ]);

    $result = json_decode(wp_remote_retrieve_body($response));

    // Attacher à la commande
    if ($result->success) {
        update_post_meta($order_id, '_invoice_pdf', $result->pdf_url);
    }
}
```

### CRM - Synchronisation de Documents

```javascript
// Intégration HubSpot
class HubSpotIntegration {
    async syncPDF(contactId, pdfUrl, filename) {
        // Générer le PDF avec PDF Builder
        const pdfResponse = await fetch('/wp-json/pdf-builder/v1/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                template_id: 1,
                data: { contact_id: contactId }
            })
        });

        const { pdf_url } = await pdfResponse.json();

        // Attacher à HubSpot
        await this.attachToHubSpot(contactId, pdf_url, filename);
    }
}
```

### API - Génération par Webhook

```php
// Webhook endpoint personnalisé
add_action('rest_api_init', function() {
    register_rest_route('my-api/v1', '/generate-pdf', [
        'methods' => 'POST',
        'callback' => 'handle_pdf_webhook',
        'permission_callback' => 'webhook_permissions_check'
    ]);
});

function handle_pdf_webhook($request) {
    $data = $request->get_json_params();

    // Validation des données
    if (!isset($data['template_id']) || !isset($data['payload'])) {
        return new WP_Error('missing_data', 'Template ID et payload requis');
    }

    // Générer le PDF
    $response = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
        'body' => json_encode([
            'template_id' => $data['template_id'],
            'data' => $data['payload']
        ]),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . get_option('pdf_builder_webhook_key')
        ]
    ]);

    return json_decode(wp_remote_retrieve_body($response));
}
```

## 🛠️ Outils de Développement

### Environnement de Développement

```bash
# Cloner le repository
git clone https://github.com/wp-pdf-builder-pro/pdf-builder-pro.git
cd pdf-builder-pro

# Installer les dépendances
composer install
npm install

# Configuration de développement
cp wp-config-sample.php wp-config.php
# Éditer wp-config.php avec vos paramètres

# Lancer les tests
composer test
npm test

# Construire les assets
npm run build
```

### Tests Automatisés

```php
// Exemple de test unitaire
class PDF_Generation_Test extends WP_UnitTestCase {

    public function test_simple_pdf_generation() {
        // Créer un template de test
        $template_id = $this->create_test_template();

        // Générer un PDF
        $result = $this->generate_pdf($template_id, ['name' => 'Test']);

        // Vérifications
        $this->assertTrue($result['success']);
        $this->assertFileExists($result['pdf_path']);
        $this->assertGreaterThan(0, filesize($result['pdf_path']));
    }

    public function test_template_validation() {
        $invalid_template = ['name' => '']; // Nom vide

        $result = $this->create_template($invalid_template);

        $this->assertFalse($result['success']);
        $this->assertContains('name', $result['errors']);
    }
}
```

### Debugging et Logging

```php
// Activer les logs de débogage
add_filter('pdf_builder_log_config', function($config) {
    $config['level'] = 'DEBUG';
    $config['handlers']['file']['enabled'] = true;
    return $config;
});

// Logger personnalisé
class PDF_Debugger {
    public static function log($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = sprintf(
                '[PDF Builder Debug] %s | Context: %s',
                $message,
                json_encode($context)
            );

            error_log($log_message);
        }
    }
}

// Utilisation
PDF_Debugger::log('Template loaded', ['template_id' => 123, 'user_id' => get_current_user_id()]);
```

## 📊 Métriques et Monitoring

### Dashboard de Monitoring

```php
// Ajouter une page de monitoring
add_action('admin_menu', function() {
    add_menu_page(
        'PDF Builder Monitoring',
        'PDF Monitoring',
        'manage_options',
        'pdf-builder-monitoring',
        'render_monitoring_dashboard'
    );
});

function render_monitoring_dashboard() {
    global $wpdb;

    // Métriques des dernières 24h
    $metrics = $wpdb->get_row("
        SELECT
            COUNT(*) as total_generations,
            AVG(generation_time) as avg_generation_time,
            SUM(file_size) as total_size
        FROM {$wpdb->prefix}pdf_builder_pdfs
        WHERE generated_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
    ");

    // Taux d'erreur
    $errors = $wpdb->get_var("
        SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_audit_log
        WHERE level = 'ERROR' AND timestamp > DATE_SUB(NOW(), INTERVAL 1 DAY)
    ");

    $error_rate = $metrics->total_generations > 0
        ? ($errors / $metrics->total_generations) * 100
        : 0;

    ?>
    <div class="wrap">
        <h1>Monitoring PDF Builder</h1>

        <div class="metrics-grid">
            <div class="metric-card">
                <h3>Générations (24h)</h3>
                <span class="metric-value"><?php echo $metrics->total_generations; ?></span>
            </div>

            <div class="metric-card">
                <h3>Temps Moyen</h3>
                <span class="metric-value"><?php echo round($metrics->avg_generation_time, 2); ?>s</span>
            </div>

            <div class="metric-card">
                <h3>Espace Utilisé</h3>
                <span class="metric-value"><?php echo size_format($metrics->total_size); ?></span>
            </div>

            <div class="metric-card">
                <h3>Taux d'Erreur</h3>
                <span class="metric-value"><?php echo round($error_rate, 2); ?>%</span>
            </div>
        </div>
    </div>

    <style>
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .metric-card { background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; text-align: center; }
        .metric-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; }
        .metric-value { font-size: 32px; font-weight: bold; color: #007cba; }
    </style>
    <?php
}
```

## 🔧 Personnalisation Avancée

### Hooks et Filtres

```php
// Hook avant génération PDF
add_action('pdf_builder_before_generate', function($template_id, $data) {
    // Validation personnalisée
    if (empty($data['required_field'])) {
        throw new Exception('Champ requis manquant');
    }

    // Enrichissement des données
    $data['timestamp'] = current_time('timestamp');
    $data['user_ip'] = $_SERVER['REMOTE_ADDR'];

}, 10, 2);

// Filtre des données de template
add_filter('pdf_builder_template_data', function($data, $template_id) {
    // Ajouter des données globales
    $data['site_name'] = get_bloginfo('name');
    $data['site_url'] = get_site_url();

    // Données utilisateur si connecté
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $data['user_name'] = $user->display_name;
        $data['user_email'] = $user->user_email;
    }

    return $data;
}, 10, 2);

// Hook après génération PDF
add_action('pdf_builder_after_generate', function($pdf_url, $data) {
    // Notification par email
    wp_mail(
        $data['customer_email'],
        'Votre PDF est prêt',
        "Téléchargez votre PDF: {$pdf_url}"
    );

    // Log dans un système externe
    my_custom_logging_system($pdf_url, $data);
}, 10, 2);
```

### Éléments Personnalisés

```php
// Enregistrer un nouvel élément
add_filter('pdf_builder_element_types', function($elements) {
    $elements['qr_code'] = [
        'name' => 'QR Code',
        'description' => 'Génère un QR code dynamique',
        'properties' => [
            'content' => [
                'type' => 'text',
                'label' => 'Contenu du QR code',
                'required' => true
            ],
            'size' => [
                'type' => 'number',
                'label' => 'Taille (px)',
                'default' => 100
            ]
        ]
    ];

    return $elements;
});

// Renderer personnalisé
add_action('pdf_builder_render_element', function($element, $data, $pdf) {
    if ($element['type'] === 'qr_code') {
        $content = $element['content'];

        // Remplacer les variables
        foreach ($data as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        // Générer le QR code
        $pdf->write2DBarcode($content, 'QRCODE,H', $element['position']['x'], $element['position']['y'], $element['size'], $element['size']);
    }
}, 10, 3);
```

## 🌐 Internationalisation

### Support Multi-langue

```php
// Charger les traductions
add_action('init', function() {
    load_plugin_textdomain(
        'pdf-builder-pro',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
});

// Fonction d'aide pour les traductions
function pdf_builder_translate($text, $context = '') {
    return translate($text, 'pdf-builder-pro', $context);
}

// Utilisation dans les templates
add_filter('pdf_builder_template_data', function($data) {
    $data['labels'] = [
        'invoice' => __('Facture', 'pdf-builder-pro'),
        'date' => __('Date', 'pdf-builder-pro'),
        'total' => __('Total', 'pdf-builder-pro')
    ];

    return $data;
});
```

## 📈 Performance et Optimisation

### Cache Intelligent

```php
// Cache des templates avec invalidation automatique
class SmartTemplateCache {

    public function get($template_id) {
        $cache_key = "template_{$template_id}";
        $template = wp_cache_get($cache_key, 'pdf_builder');

        if (!$template) {
            $template = $this->loadFromDatabase($template_id);
            wp_cache_set($cache_key, $template, 'pdf_builder', 3600);
        }

        return $template;
    }

    public function invalidate($template_id) {
        wp_cache_delete("template_{$template_id}", 'pdf_builder');

        // Invalidation des PDFs générés avec ce template
        $this->invalidateGeneratedPdfs($template_id);
    }
}
```

### Génération Asynchrone

```php
// File d'attente pour les générations lourdes
add_filter('pdf_builder_should_queue', function($should_queue, $template, $data) {
    // Mettre en file si template complexe ou données volumineuses
    $element_count = count($template->getElements());
    $data_size = strlen(json_encode($data));

    return $element_count > 20 || $data_size > 10000;
}, 10, 3);

// Traitement de la file d'attente
add_action('pdf_builder_process_queue', function($job) {
    $pdf_generator = new PDF_Generator();
    $result = $pdf_generator->generate($job->template, $job->data);

    // Notification du résultat
    do_action('pdf_builder_queue_complete', $job, $result);
});
```

## 🔐 Sécurité

### Validation des Entrées

```php
// Validation stricte des données
add_filter('pdf_builder_validate_data', function($is_valid, $data, $template) {
    $validator = new PDF_Data_Validator();

    // Validation des types
    $is_valid &= $validator->validateTypes($data, $template->getSchema());

    // Validation des tailles
    $is_valid &= $validator->validateSizes($data);

    // Validation de sécurité
    $is_valid &= $validator->validateSecurity($data);

    return $is_valid;
}, 10, 3);
```

### Audit Complet

```php
// Audit de toutes les actions
add_action('pdf_builder_audit_log', function($action, $data, $user_id, $ip) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'pdf_builder_audit',
        [
            'action' => $action,
            'data' => json_encode($data),
            'user_id' => $user_id,
            'ip_address' => $ip,
            'timestamp' => current_time('mysql')
        ]
    );
});
```

## 📞 Support et Communauté

### Ressources de Support

- 📖 **[Documentation Complète](https://docs.pdf-builder-pro.com)**
- 💬 **[Forum Communauté](https://community.pdf-builder-pro.com)**
- 🎯 **[Issues GitHub](https://github.com/wp-pdf-builder-pro/pdf-builder-pro/issues)**
- 📧 **Support Email :** support@pdf-builder-pro.com

### Contribution

Nous accueillons les contributions ! Voir notre [Guide de Contribution](./CONTRIBUTING.md).

### Licence

PDF Builder Pro est sous licence GPL v2+. Voir [LICENSE](../LICENSE) pour plus de détails.

---

**🚀 Prêt à commencer ?** Consultez notre [Guide d'Installation](./tutorials/installation.md) ou explorez les [Exemples d'Usage](./api/examples.md).

**🎯 Questions ?** Rejoignez notre [Communauté Slack](https://pdf-builder-pro.slack.com) ou ouvrez une [Issue GitHub](https://github.com/wp-pdf-builder-pro/pdf-builder-pro/issues).