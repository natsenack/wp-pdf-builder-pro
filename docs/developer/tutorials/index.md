# 📚 Tutoriels Développeur

Guides étape par étape pour intégrer PDF Builder Pro dans vos projets WordPress.

## 🚀 Installation et Configuration

### Installation Automatique

```bash
# Via Composer (recommandé)
composer require wp-pdf-builder-pro/pdf-builder-pro

# Ou via WP-CLI
wp plugin install pdf-builder-pro --activate

# Ou téléchargement manuel depuis wordpress.org
```

### Configuration de Base

```php
// Dans functions.php ou plugin personnalisé

// 1. Initialiser l'API
add_action('init', 'initialize_pdf_builder');

function initialize_pdf_builder() {
    // Activer l'API REST
    if (!defined('PDF_BUILDER_REST_API_ENABLED')) {
        define('PDF_BUILDER_REST_API_ENABLED', true);
    }

    // Configurer les permissions
    if (!defined('PDF_BUILDER_ALLOW_PUBLIC_ACCESS')) {
        define('PDF_BUILDER_ALLOW_PUBLIC_ACCESS', false);
    }
}

// 2. Configurer les templates par défaut
add_action('pdf_builder_init', 'setup_default_templates');

function setup_default_templates() {
    // Créer un template de facture par défaut
    $invoice_template = [
        'name' => 'Facture Standard',
        'description' => 'Template de facture professionnel',
        'elements' => [
            [
                'type' => 'text',
                'content' => 'FACTURE',
                'position' => ['x' => 200, 'y' => 30],
                'style' => ['fontSize' => 24, 'fontWeight' => 'bold']
            ],
            [
                'type' => 'dynamic-text',
                'content' => 'N° {{invoice_number}}',
                'position' => ['x' => 400, 'y' => 50]
            ]
        ]
    ];

    // Enregistrer via API
    wp_remote_post('/wp-json/pdf-builder/v1/templates', [
        'body' => json_encode($invoice_template),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . get_option('pdf_builder_api_key')
        ]
    ]);
}
```

### Configuration Avancée

```php
// Configuration complète dans wp-config.php

// Sécurité
define('PDF_BUILDER_API_RATE_LIMIT', 100); // Requêtes par heure
define('PDF_BUILDER_MAX_FILE_SIZE', '10MB');
define('PDF_BUILDER_CACHE_TTL', 3600); // 1 heure

// Performance
define('PDF_BUILDER_MEMORY_LIMIT', '256M');
define('PDF_BUILDER_EXECUTION_TIMEOUT', 30); // secondes

// Stockage
define('PDF_BUILDER_STORAGE_PATH', WP_CONTENT_DIR . '/pdf-builder-storage/');
define('PDF_BUILDER_STORAGE_URL', WP_CONTENT_URL . '/pdf-builder-storage/');

// Base de données
define('PDF_BUILDER_DB_TABLE_PREFIX', 'pdf_builder_');
define('PDF_BUILDER_DB_VERSION', '1.0.0');
```

## 🎨 Créer Votre Premier Template

### Template Simple avec Texte Statique

```php
// Créer un template basique
$template_data = [
    'name' => 'Carte de Visite',
    'description' => 'Template simple pour carte de visite',
    'page_format' => 'A6', // Format carte de visite
    'orientation' => 'landscape',
    'elements' => [
        // Logo/Entreprise
        [
            'type' => 'text',
            'content' => 'MON ENTREPRISE',
            'position' => ['x' => 10, 'y' => 10],
            'style' => [
                'fontSize' => 14,
                'fontWeight' => 'bold',
                'color' => '#333333'
            ]
        ],
        // Ligne de séparation
        [
            'type' => 'line',
            'start' => ['x' => 10, 'y' => 25],
            'end' => ['x' => 140, 'y' => 25],
            'style' => [
                'width' => 0.5,
                'color' => '#cccccc'
            ]
        ],
        // Coordonnées
        [
            'type' => 'text',
            'content' => 'Jean Dupont\nDirecteur Commercial\njean@entreprise.com\n+33 1 23 45 67 89',
            'position' => ['x' => 10, 'y' => 30],
            'style' => [
                'fontSize' => 8,
                'lineHeight' => 1.2
            ]
        ]
    ]
];

// Créer via API
$response = wp_remote_post('/wp-json/pdf-builder/v1/templates', [
    'body' => json_encode($template_data),
    'headers' => [
        'Content-Type' => 'application/json',
        'X-WP-Nonce' => wp_create_nonce('wp_rest')
    ]
]);
```

### Template Dynamique avec Variables

```php
// Template avec données dynamiques
$dynamic_template = [
    'name' => 'Devis Client',
    'description' => 'Template de devis personnalisable',
    'elements' => [
        // En-tête
        [
            'type' => 'text',
            'content' => 'DEVIS',
            'position' => ['x' => 200, 'y' => 20],
            'style' => ['fontSize' => 20, 'fontWeight' => 'bold']
        ],
        // Informations client
        [
            'type' => 'dynamic-text',
            'content' => 'Client: {{client_name}}',
            'position' => ['x' => 20, 'y' => 60]
        ],
        [
            'type' => 'dynamic-text',
            'content' => 'Email: {{client_email}}',
            'position' => ['x' => 20, 'y' => 75]
        ],
        [
            'type' => 'dynamic-text',
            'content' => 'Date: {{quote_date}}',
            'position' => ['x' => 350, 'y' => 60]
        ],
        // Tableau des prestations
        [
            'type' => 'table',
            'position' => ['x' => 20, 'y' => 100],
            'columns' => [
                ['header' => 'Prestation', 'width' => 200],
                ['header' => 'Quantité', 'width' => 60],
                ['header' => 'Prix HT', 'width' => 80],
                ['header' => 'Total HT', 'width' => 80]
            ],
            'data' => '{{services}}', // Variable contenant le tableau
            'style' => [
                'headerBackground' => '#f0f0f0',
                'borderWidth' => 0.5
            ]
        ],
        // Total
        [
            'type' => 'dynamic-text',
            'content' => 'Total HT: {{total_ht}} €',
            'position' => ['x' => 350, 'y' => 200],
            'style' => ['fontWeight' => 'bold']
        ],
        [
            'type' => 'dynamic-text',
            'content' => 'TVA (20%): {{tva}} €',
            'position' => ['x' => 350, 'y' => 215]
        ],
        [
            'type' => 'dynamic-text',
            'content' => 'Total TTC: {{total_ttc}} €',
            'position' => ['x' => 350, 'y' => 235],
            'style' => ['fontSize' => 14, 'fontWeight' => 'bold']
        ]
    ]
];

// Générer un devis avec ce template
$quote_data = [
    'template_id' => 2, // ID du template
    'data' => [
        'client_name' => 'Marie Dubois',
        'client_email' => 'marie@example.com',
        'quote_date' => date('d/m/Y'),
        'services' => [
            ['Prestation Web', '1', '2500.00', '2500.00'],
            ['Maintenance mensuelle', '12', '150.00', '1800.00']
        ],
        'total_ht' => '4300.00',
        'tva' => '860.00',
        'total_ttc' => '5160.00'
    ]
];

$response = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
    'body' => json_encode($quote_data),
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . get_option('pdf_builder_api_key')
    ]
]);
```

## 🛒 Intégration WooCommerce

### Générer des Factures Automatiques

```php
// Hook sur nouvelle commande
add_action('woocommerce_order_status_processing', 'generate_order_invoice', 10, 1);

function generate_order_invoice($order_id) {
    $order = wc_get_order($order_id);

    // Vérifier si facture déjà générée
    if (get_post_meta($order_id, '_pdf_invoice_generated', true)) {
        return;
    }

    // Préparer les données
    $invoice_data = [
        'invoice_number' => 'INV-' . $order_id,
        'order_date' => $order->get_date_created()->format('d/m/Y'),
        'customer_name' => $order->get_formatted_billing_full_name(),
        'customer_address' => $order->get_formatted_billing_address(),
        'customer_email' => $order->get_billing_email(),
        'payment_method' => $order->get_payment_method_title(),
        'order_items' => [],
        'subtotal' => $order->get_subtotal(),
        'tax_total' => $order->get_total_tax(),
        'shipping_total' => $order->get_shipping_total(),
        'order_total' => $order->get_total()
    ];

    // Ajouter les articles
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();

        $invoice_data['order_items'][] = [
            'name' => $item->get_name(),
            'sku' => $product ? $product->get_sku() : '',
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total(),
            'total' => $item->get_total() + $item->get_total_tax()
        ];
    }

    // Générer le PDF
    $pdf_request = [
        'template_id' => get_option('pdf_builder_invoice_template_id'),
        'data' => $invoice_data,
        'options' => [
            'filename' => 'facture-' . $order_id . '.pdf',
            'save_to_order' => true
        ]
    ];

    $response = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
        'body' => json_encode($pdf_request),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . get_option('pdf_builder_api_key')
        ]
    ]);

    if (!is_wp_error($response)) {
        $result = json_decode(wp_remote_retrieve_body($response));

        if ($result->success) {
            // Sauvegarder les métadonnées
            update_post_meta($order_id, '_pdf_invoice_generated', 'yes');
            update_post_meta($order_id, '_pdf_invoice_url', $result->pdf_url);
            update_post_meta($order_id, '_pdf_invoice_number', $invoice_data['invoice_number']);

            // Ajouter une note à la commande
            $order->add_order_note('Facture PDF générée: ' . $result->pdf_url);

            // Optionnel: envoyer par email
            if (get_option('pdf_builder_auto_send_invoice')) {
                send_invoice_email($order_id, $result->pdf_url);
            }
        }
    }
}

// Fonction d'envoi d'email
function send_invoice_email($order_id, $pdf_url) {
    $order = wc_get_order($order_id);

    $subject = 'Votre facture - Commande #' . $order->get_order_number();
    $message = 'Bonjour,<br><br>Votre facture est disponible en pièce jointe.';

    $attachments = [$pdf_url];

    wc()->mailer()->emails['WC_Email_Customer_Invoice']->trigger($order_id, $order, '', '', $attachments);
}
```

### Ajouter un Bouton de Téléchargement dans Mon Compte

```php
// Ajouter un onglet "Factures" dans Mon Compte
add_filter('woocommerce_account_menu_items', 'add_invoice_menu_item');

function add_invoice_menu_item($items) {
    $items['invoices'] = 'Mes Factures';
    return $items;
}

add_action('woocommerce_account_invoices_endpoint', 'display_invoices_content');

function display_invoices_content() {
    $user_id = get_current_user_id();

    // Récupérer les commandes de l'utilisateur
    $customer_orders = wc_get_orders([
        'customer' => $user_id,
        'status' => ['completed', 'processing'],
        'limit' => -1
    ]);

    echo '<h2>Mes Factures</h2>';

    if (empty($customer_orders)) {
        echo '<p>Aucune commande trouvée.</p>';
        return;
    }

    echo '<table class="woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive">';
    echo '<thead><tr>';
    echo '<th>N° Commande</th>';
    echo '<th>Date</th>';
    echo '<th>Total</th>';
    echo '<th>Facture</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($customer_orders as $order) {
        $invoice_url = get_post_meta($order->get_id(), '_pdf_invoice_url', true);
        $invoice_number = get_post_meta($order->get_id(), '_pdf_invoice_number', true);

        echo '<tr>';
        echo '<td>' . $order->get_order_number() . '</td>';
        echo '<td>' . $order->get_date_created()->format('d/m/Y') . '</td>';
        echo '<td>' . $order->get_formatted_order_total() . '</td>';
        echo '<td>';

        if ($invoice_url) {
            echo '<a href="' . esc_url($invoice_url) . '" class="button" target="_blank">';
            echo 'Télécharger ' . ($invoice_number ? $invoice_number : 'Facture');
            echo '</a>';
        } else {
            echo '<span class="invoice-pending">En préparation</span>';
        }

        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

// Ajouter l'endpoint
add_action('init', 'add_invoices_endpoint');

function add_invoices_endpoint() {
    add_rewrite_endpoint('invoices', EP_ROOT | EP_PAGES);
}

// Styles CSS
add_action('wp_enqueue_scripts', 'add_invoice_styles');

function add_invoice_styles() {
    if (is_account_page()) {
        wp_add_inline_style('woocommerce-inline', '
            .invoice-pending { color: #999; font-style: italic; }
            .woocommerce-table--order-downloads .button {
                padding: 8px 16px;
                background: #007cba;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
        ');
    }
}
```

## 🔧 Extensions et Personnalisations

### Créer une Extension Personnalisée

```php
<?php
/**
 * Plugin Name: PDF Builder Pro - Extension Custom Elements
 * Description: Ajoute des éléments personnalisés au PDF Builder
 * Version: 1.0.0
 * Author: Votre Nom
 */

// Sécurité
if (!defined('ABSPATH')) exit;

// Classe principale de l'extension
class PDF_Builder_Custom_Elements {

    public function __construct() {
        add_action('init', [$this, 'init']);
    }

    public function init() {
        // Enregistrer les nouveaux types d'éléments
        add_filter('pdf_builder_element_types', [$this, 'register_custom_elements']);

        // Hook pour le rendu
        add_action('pdf_builder_render_element', [$this, 'render_custom_element'], 10, 3);
    }

    // Enregistrer les nouveaux éléments
    public function register_custom_elements($element_types) {
        $element_types['qr_code'] = [
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

        $element_types['barcode'] = [
            'name' => 'Code-barres',
            'description' => 'Génère un code-barres',
            'properties' => [
                'code' => [
                    'type' => 'text',
                    'label' => 'Code à encoder',
                    'required' => true
                ],
                'type' => [
                    'type' => 'select',
                    'label' => 'Type de code',
                    'options' => [
                        'CODE128' => 'Code 128',
                        'EAN13' => 'EAN-13',
                        'UPC-A' => 'UPC-A'
                    ],
                    'default' => 'CODE128'
                ]
            ]
        ];

        return $element_types;
    }

    // Rendu des éléments personnalisés
    public function render_custom_element($element, $data, $pdf) {
        switch ($element['type']) {
            case 'qr_code':
                $this->render_qr_code($element, $data, $pdf);
                break;
            case 'barcode':
                $this->render_barcode($element, $data, $pdf);
                break;
        }
    }

    private function render_qr_code($element, $data, $pdf) {
        // Utiliser une bibliothèque QR code (tcpdf inclus)
        $content = $this->process_variables($element['content'], $data);
        $size = $element['size'] ?? 100;

        $pdf->write2DBarcode($content, 'QRCODE,H', $element['position']['x'], $element['position']['y'], $size, $size);
    }

    private function render_barcode($element, $data, $pdf) {
        $code = $this->process_variables($element['code'], $data);
        $type = $element['type'] ?? 'CODE128';

        $pdf->write1DBarcode($code, $type, $element['position']['x'], $element['position']['y'], 60, 15);
    }

    private function process_variables($content, $data) {
        // Remplacer les variables {{variable}}
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
}

// Initialiser l'extension
new PDF_Builder_Custom_Elements();
```

### Intégration avec un CRM Externe

```php
// Classe pour intégration CRM
class PDF_Builder_CRM_Integration {

    private $crm_api_url;
    private $crm_api_key;

    public function __construct() {
        $this->crm_api_url = get_option('pdf_builder_crm_api_url');
        $this->crm_api_key = get_option('pdf_builder_crm_api_key');

        add_action('pdf_builder_pdf_generated', [$this, 'sync_to_crm'], 10, 2);
    }

    public function sync_to_crm($pdf_url, $data) {
        // Créer/Mettre à jour le contact dans le CRM
        $contact_data = [
            'first_name' => $data['customer_first_name'] ?? '',
            'last_name' => $data['customer_last_name'] ?? '',
            'email' => $data['customer_email'] ?? '',
            'company' => $data['customer_company'] ?? '',
            'pdf_documents' => [$pdf_url]
        ];

        $response = wp_remote_post($this->crm_api_url . '/contacts', [
            'body' => json_encode($contact_data),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->crm_api_key
            ]
        ]);

        if (!is_wp_error($response)) {
            $result = json_decode(wp_remote_retrieve_body($response));

            // Log la synchronisation
            error_log('CRM Sync - Contact ID: ' . $result->id . ', PDF: ' . $pdf_url);
        }
    }

    // Méthode pour récupérer des données du CRM
    public function get_crm_data($contact_id) {
        $response = wp_remote_get($this->crm_api_url . '/contacts/' . $contact_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->crm_api_key
            ]
        ]);

        if (!is_wp_error($response)) {
            return json_decode(wp_remote_retrieve_body($response));
        }

        return null;
    }
}

// Hook pour enrichir les données avant génération PDF
add_filter('pdf_builder_template_data', 'enrich_with_crm_data', 10, 2);

function enrich_with_crm_data($data, $template_id) {
    // Si le template nécessite des données CRM
    if (get_post_meta($template_id, '_use_crm_data', true)) {
        $crm = new PDF_Builder_CRM_Integration();

        if (isset($data['crm_contact_id'])) {
            $crm_data = $crm->get_crm_data($data['crm_contact_id']);

            if ($crm_data) {
                // Fusionner les données
                $data = array_merge($data, [
                    'crm_company_size' => $crm_data->company_size,
                    'crm_industry' => $crm_data->industry,
                    'crm_last_contact' => $crm_data->last_contact_date
                ]);
            }
        }
    }

    return $data;
}
```

## 🔍 Débogage et Monitoring

### Logs Détaillés

```php
// Activer les logs détaillés
add_action('init', 'enable_pdf_builder_debug');

function enable_pdf_builder_debug() {
    if (WP_DEBUG) {
        // Log toutes les générations PDF
        add_action('pdf_builder_pdf_generated', 'log_pdf_generation', 10, 2);
        add_action('pdf_builder_pdf_generation_failed', 'log_pdf_error', 10, 2);
    }
}

function log_pdf_generation($pdf_url, $data) {
    $log_entry = sprintf(
        "[PDF Generated] Template: %s, User: %s, URL: %s, Data: %s",
        $data['template_id'] ?? 'unknown',
        get_current_user_id(),
        $pdf_url,
        json_encode($data)
    );

    error_log($log_entry);
}

function log_pdf_error($error, $data) {
    $log_entry = sprintf(
        "[PDF Error] Template: %s, User: %s, Error: %s, Data: %s",
        $data['template_id'] ?? 'unknown',
        get_current_user_id(),
        $error,
        json_encode($data)
    );

    error_log($log_entry);
}
```

### Page de Diagnostic

```php
// Ajouter une page de diagnostic
add_action('admin_menu', 'add_pdf_diagnostic_page');

function add_pdf_diagnostic_page() {
    add_submenu_page(
        'tools.php',
        'Diagnostic PDF Builder',
        'Diagnostic PDF',
        'manage_options',
        'pdf-builder-diagnostic',
        'pdf_diagnostic_page'
    );
}

function pdf_diagnostic_page() {
    echo '<div class="wrap">';
    echo '<h1>Diagnostic PDF Builder Pro</h1>';

    // Test de base de données
    echo '<h2>Test Base de Données</h2>';
    global $wpdb;

    $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_%'");
    echo '<p>Tables PDF Builder: ' . ($tables_exist ? '✅ Présentes' : '❌ Manquantes') . '</p>';

    // Test permissions dossiers
    echo '<h2>Test Permissions</h2>';
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/pdf-builder-cache/';

    echo '<p>Dossier cache: ' . (is_writable($pdf_dir) ? '✅ Accessible en écriture' : '❌ Problème de permissions') . '</p>';

    // Test API
    echo '<h2>Test API REST</h2>';
    $api_test = wp_remote_get('/wp-json/pdf-builder/v1/templates');

    if (!is_wp_error($api_test)) {
        $response_code = wp_remote_retrieve_response_code($api_test);
        echo '<p>API Templates: ' . ($response_code === 200 ? '✅ Fonctionnelle' : '❌ Code ' . $response_code) . '</p>';
    } else {
        echo '<p>API Templates: ❌ Erreur de connexion</p>';
    }

    // Test génération PDF
    echo '<h2>Test Génération PDF</h2>';
    echo '<button id="test-pdf-generation" class="button button-primary">Tester la génération PDF</button>';
    echo '<div id="test-result"></div>';

    ?>
    <script>
    document.getElementById('test-pdf-generation').addEventListener('click', async function() {
        const button = this;
        button.disabled = true;
        button.textContent = 'Test en cours...';

        try {
            const response = await fetch('/wp-json/pdf-builder/v1/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: JSON.stringify({
                    template_id: 1,
                    data: { test: 'diagnostic' },
                    test_mode: true
                })
            });

            const result = await response.json();
            document.getElementById('test-result').innerHTML =
                result.success ?
                '<p style="color: green;">✅ Génération réussie: ' + result.pdf_url + '</p>' :
                '<p style="color: red;">❌ Erreur: ' + result.message + '</p>';

        } catch (error) {
            document.getElementById('test-result').innerHTML =
                '<p style="color: red;">❌ Erreur réseau: ' + error.message + '</p>';
        }

        button.disabled = false;
        button.textContent = 'Tester la génération PDF';
    });
    </script>
    <?php

    echo '</div>';
}
```

---

**📖 Voir aussi :**
- [Guide d'installation](../tutorials/installation.md)
- [Exemples d'usage API](../api/examples.md)
- [Documentation technique](../technical/)