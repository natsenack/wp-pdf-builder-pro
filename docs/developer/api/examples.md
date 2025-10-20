# 💡 API REST - Exemples d'Usage

Collection d'exemples pratiques pour utiliser l'API REST de PDF Builder Pro dans différents scénarios.

## 🚀 Démarrage Rapide

### Créer et Générer un PDF Simple

```javascript
// 1. Créer un template simple
const templateData = {
  name: "Facture Rapide",
  description: "Template de test",
  elements: [
    {
      type: "text",
      content: "FACTURE",
      position: { x: 200, y: 50 },
      style: { fontSize: 24, fontWeight: "bold" }
    },
    {
      type: "dynamic-text",
      content: "Client: {{customer_name}}",
      position: { x: 50, y: 100 }
    },
    {
      type: "dynamic-text",
      content: "Total: {{order_total}} €",
      position: { x: 50, y: 120 }
    }
  ]
};

// Créer le template
const createResponse = await fetch('/wp-json/pdf-builder/v1/templates', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpApiSettings.nonce
  },
  body: JSON.stringify(templateData)
});

const { template } = await createResponse.json();

// 2. Générer un PDF
const pdfData = {
  template_id: template.id,
  data: {
    customer_name: "Jean Dupont",
    order_total: "299.99"
  }
};

const pdfResponse = await fetch('/wp-json/pdf-builder/v1/generate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpApiSettings.nonce
  },
  body: JSON.stringify(pdfData)
});

const { pdf_url } = await pdfResponse.json();
console.log('PDF généré:', pdf_url);
```

## 📧 Intégration WooCommerce

### Générer une Facture Automatique

```php
// Hook sur nouvelle commande
add_action('woocommerce_order_status_completed', 'generate_invoice_pdf', 10, 1);

function generate_invoice_pdf($order_id) {
    $order = wc_get_order($order_id);

    // Données de la commande
    $order_data = [
        'order_number' => $order->get_order_number(),
        'customer_name' => $order->get_formatted_billing_full_name(),
        'customer_email' => $order->get_billing_email(),
        'order_date' => $order->get_date_created()->format('d/m/Y'),
        'order_total' => $order->get_formatted_order_total(),
        'payment_method' => $order->get_payment_method_title()
    ];

    // Articles de la commande
    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = [
            'name' => $item->get_name(),
            'quantity' => $item->get_quantity(),
            'price' => $item->get_total()
        ];
    }

    // Générer le PDF
    $pdf_request = [
        'template_id' => get_option('pdf_builder_invoice_template_id'),
        'data' => array_merge($order_data, ['items' => $items])
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

        // Attacher le PDF à la commande
        if ($result->success) {
            update_post_meta($order_id, '_pdf_invoice_url', $result->pdf_url);

            // Envoyer par email
            $attachments = [$result->pdf_url];
            wc()->mailer()->emails['WC_Email_Customer_Invoice']->trigger($order_id, $order, '', '', $attachments);
        }
    }
}
```

### Générer un Devis sur Demande

```php
// Shortcode pour génération de devis
add_shortcode('pdf_quote_generator', 'pdf_quote_shortcode');

function pdf_quote_shortcode($atts) {
    ob_start();
    ?>
    <form id="quote-form" method="post">
        <input type="text" name="customer_name" placeholder="Nom du client" required>
        <input type="email" name="customer_email" placeholder="Email" required>
        <textarea name="description" placeholder="Description du projet"></textarea>
        <input type="number" name="estimated_price" placeholder="Prix estimé" step="0.01">
        <button type="submit">Générer Devis</button>
    </form>

    <script>
    document.getElementById('quote-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const quoteData = {
            customer_name: formData.get('customer_name'),
            customer_email: formData.get('customer_email'),
            description: formData.get('description'),
            estimated_price: formData.get('estimated_price'),
            quote_date: new Date().toLocaleDateString('fr-FR'),
            quote_number: 'DEV-' + Date.now()
        };

        try {
            const response = await fetch('/wp-json/pdf-builder/v1/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: JSON.stringify({
                    template_id: 2, // ID du template devis
                    data: quoteData
                })
            });

            const result = await response.json();

            if (result.success) {
                // Télécharger ou afficher le PDF
                window.open(result.pdf_url, '_blank');
            } else {
                alert('Erreur lors de la génération du devis');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur réseau');
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
```

## 🔄 Intégration CRM

### Synchronisation Salesforce

```php
// Webhook pour synchronisation Salesforce
add_action('pdf_builder_pdf_generated', 'sync_pdf_to_salesforce', 10, 2);

function sync_pdf_to_salesforce($pdf_url, $data) {
    // Configuration Salesforce
    $sf_config = get_option('salesforce_integration');

    // Préparer les données
    $contact_data = [
        'FirstName' => $data['customer_first_name'],
        'LastName' => $data['customer_last_name'],
        'Email' => $data['customer_email'],
        'AccountId' => $data['account_id']
    ];

    // Créer/Mettre à jour le contact
    $contact_response = wp_remote_post($sf_config['api_url'] . '/sobjects/Contact', [
        'body' => json_encode($contact_data),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $sf_config['access_token']
        ]
    ]);

    if (!is_wp_error($contact_response)) {
        $contact_result = json_decode(wp_remote_retrieve_body($contact_response));

        // Attacher le PDF comme document
        $document_data = [
            'Name' => 'Facture_' . $data['order_number'] . '.pdf',
            'Body' => base64_encode(file_get_contents($pdf_url)),
            'ParentId' => $contact_result->id
        ];

        wp_remote_post($sf_config['api_url'] . '/sobjects/Attachment', [
            'body' => json_encode($document_data),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $sf_config['access_token']
            ]
        ]);
    }
}
```

### Intégration HubSpot

```javascript
// Intégration frontend HubSpot
class HubSpotIntegration {
    constructor(apiKey) {
        this.apiKey = apiKey;
        this.baseUrl = 'https://api.hubapi.com';
    }

    async createContact(contactData) {
        const response = await fetch(`${this.baseUrl}/crm/v3/objects/contacts`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.apiKey}`
            },
            body: JSON.stringify({
                properties: {
                    firstname: contactData.firstName,
                    lastname: contactData.lastName,
                    email: contactData.email,
                    company: contactData.company
                }
            })
        });

        return await response.json();
    }

    async attachPDF(contactId, pdfUrl, filename) {
        // Télécharger le PDF
        const pdfResponse = await fetch(pdfUrl);
        const pdfBlob = await pdfResponse.blob();

        // Créer FormData pour l'upload
        const formData = new FormData();
        formData.append('file', pdfBlob, filename);

        const uploadResponse = await fetch(`${this.baseUrl}/files/v3/files`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.apiKey}`
            },
            body: formData
        });

        const fileResult = await uploadResponse.json();

        // Associer le fichier au contact
        await fetch(`${this.baseUrl}/crm/v3/objects/contacts/${contactId}/associations/files/${fileResult.id}/document`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${this.apiKey}`
            }
        });
    }
}

// Utilisation
const hubspot = new HubSpotIntegration('your_api_key');

// Après génération PDF
const contact = await hubspot.createContact({
    firstName: 'Jean',
    lastName: 'Dupont',
    email: 'jean@example.com',
    company: 'ABC Corp'
});

await hubspot.attachPDF(contact.id, pdfUrl, 'facture.pdf');
```

## 📱 Application Mobile

### API pour App React Native

```javascript
// Service PDF pour app mobile
class PDFService {
    constructor(baseUrl, apiKey) {
        this.baseUrl = baseUrl;
        this.apiKey = apiKey;
    }

    async getTemplates() {
        const response = await fetch(`${this.baseUrl}/wp-json/pdf-builder/v1/templates`, {
            headers: {
                'Authorization': `Bearer ${this.apiKey}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Erreur récupération templates');
        }

        return await response.json();
    }

    async generatePDF(templateId, data) {
        const response = await fetch(`${this.baseUrl}/wp-json/pdf-builder/v1/generate`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.apiKey}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                template_id: templateId,
                data: data,
                options: {
                    format: 'PDF',
                    compression: 'NORMAL'
                }
            })
        });

        if (!response.ok) {
            throw new Error('Erreur génération PDF');
        }

        const result = await response.json();

        // Télécharger le PDF
        const pdfResponse = await fetch(result.pdf_url);
        const pdfBlob = await pdfResponse.blob();

        return pdfBlob;
    }

    async getPreview(templateId) {
        const response = await fetch(`${this.baseUrl}/wp-json/pdf-builder/v1/generate/preview`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.apiKey}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                template_id: templateId,
                preview: true
            })
        });

        const result = await response.json();
        return result.preview_url;
    }
}

// Utilisation dans l'app
const pdfService = new PDFService('https://monsite.com', 'my_api_key');

// Récupérer templates
const templates = await pdfService.getTemplates();

// Générer PDF
const pdfBlob = await pdfService.generatePDF(1, {
    customer_name: 'Marie Curie',
    order_total: '150.00'
});

// Aperçu
const previewUrl = await pdfService.getPreview(1);
```

## 🔧 Automatisation

### Génération PDF en Lot

```php
// Tâche cron pour génération en lot
add_action('pdf_builder_batch_generate', 'process_batch_pdf_generation');

function process_batch_pdf_generation() {
    // Récupérer les commandes en attente
    $pending_orders = get_posts([
        'post_type' => 'shop_order',
        'post_status' => 'wc-processing',
        'meta_query' => [
            [
                'key' => '_pdf_generated',
                'compare' => 'NOT EXISTS'
            ]
        ],
        'posts_per_page' => 10 // Traiter par lots de 10
    ]);

    foreach ($pending_orders as $order_post) {
        $order = wc_get_order($order_post->ID);

        // Générer PDF
        $pdf_result = wp_remote_post('/wp-json/pdf-builder/v1/generate', [
            'body' => json_encode([
                'template_id' => get_option('pdf_builder_batch_template_id'),
                'data' => [
                    'order_id' => $order->get_id(),
                    'customer_name' => $order->get_formatted_billing_full_name(),
                    'order_total' => $order->get_total(),
                    'order_date' => $order->get_date_created()->format('Y-m-d')
                ]
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . get_option('pdf_builder_cron_api_key')
            ]
        ]);

        if (!is_wp_error($pdf_result)) {
            $result = json_decode(wp_remote_retrieve_body($pdf_result));

            if ($result->success) {
                // Marquer comme traité
                update_post_meta($order->get_id(), '_pdf_generated', 'yes');
                update_post_meta($order->get_id(), '_pdf_url', $result->pdf_url);

                // Log succès
                error_log("PDF généré pour commande {$order->get_id()}: {$result->pdf_url}");
            }
        }

        // Pause pour éviter surcharge
        sleep(1);
    }
}

// Programmer la tâche cron
if (!wp_next_scheduled('pdf_builder_batch_generate')) {
    wp_schedule_event(time(), 'hourly', 'pdf_builder_batch_generate');
}
```

### Monitoring et Alertes

```php
// Dashboard de monitoring
add_action('admin_menu', 'pdf_builder_monitoring_menu');

function pdf_builder_monitoring_menu() {
    add_submenu_page(
        'pdf-builder',
        'Monitoring PDF',
        'Monitoring',
        'manage_options',
        'pdf-builder-monitoring',
        'pdf_builder_monitoring_page'
    );
}

function pdf_builder_monitoring_page() {
    // Récupérer métriques
    $response = wp_remote_get('/wp-json/pdf-builder/v1/metrics?period=day', [
        'headers' => [
            'Authorization' => 'Bearer ' . get_option('pdf_builder_admin_api_key')
        ]
    ]);

    if (!is_wp_error($response)) {
        $metrics = json_decode(wp_remote_retrieve_body($response));

        echo '<div class="wrap">';
        echo '<h1>Monitoring PDF Builder</h1>';

        echo '<div class="metrics-grid">';
        echo "<div class='metric-card'><h3>Générations Aujourd'hui</h3><span class='metric-value'>{$metrics->metrics->total_generations}</span></div>";
        echo "<div class='metric-card'><h3>Temps Moyen</h3><span class='metric-value'>{$metrics->metrics->average_generation_time}s</span></div>";
        echo "<div class='metric-card'><h3>Cache Hit Rate</h3><span class='metric-value'>{$metrics->metrics->cache_hit_rate}%</span></div>";
        echo "<div class='metric-card'><h3>Taux d'Erreur</h3><span class='metric-value'>{$metrics->metrics->error_rate}%</span></div>";
        echo '</div>';

        echo '</div>';
    }
}

// Styles CSS
add_action('admin_head', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-monitoring') {
        echo '<style>
            .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
            .metric-card { background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; text-align: center; }
            .metric-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; }
            .metric-value { font-size: 32px; font-weight: bold; color: #007cba; }
        </style>';
    }
});
```

---

**📖 Voir aussi :**
- [Endpoints API](./endpoints.md)
- [Authentification](./authentication.md)
- [Tutoriels d'intégration](../tutorials/)