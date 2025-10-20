# 🧪 Guide Tests Validation Métier - Workflows et UAT

Ce guide couvre les procédures complètes de validation métier pour WP PDF Builder Pro, incluant les tests de workflows fonctionnels et l'acceptation utilisateur (UAT) avant déploiement production.

## 🎯 Objectifs validation métier

### Validation fonctionnelle

#### Critères succès
- **Fonctionnalités core** : 100% workflows validés
- **Intégrations** : APIs et webhooks opérationnels
- **Performance métier** : Temps réponse acceptables
- **Fiabilité** : Taux d'erreur < 1% en conditions normales
- **Conformité** : Respect exigences métier et réglementaires

#### Métriques cibles
- **Couverture tests** : > 95% scénarios métier
- **Temps validation** : < 2 jours par cycle release
- **Taux détection** : > 90% anomalies avant production
- **Satisfaction utilisateur** : Score > 8/10 en UAT

## 🗂️ Structure tests validation

### Organisation par domaines métier

#### Tests génération PDF
```
📁 pdf-generation/
├── template-creation/          # Création et configuration templates
├── data-binding/              # Liaison données dynamiques
├── pdf-rendering/             # Rendu et mise en page
├── customization/             # Personnalisation avancée
├── batch-processing/          # Traitement par lots
└── error-handling/            # Gestion erreurs
```

#### Tests gestion templates
```
📁 template-management/
├── crud-operations/           # Création, lecture, mise à jour, suppression
├── version-control/           # Contrôle versions templates
├── permissions/               # Gestion permissions utilisateurs
├── sharing/                   # Partage templates équipe
├── backup-restore/            # Sauvegarde et restauration
└── audit-trail/               # Traçabilité modifications
```

#### Tests intégrations
```
📁 integrations/
├── woocommerce/               # Intégration e-commerce
├── crm-systems/               # Connexions CRM (HubSpot, Salesforce)
├── email-services/            # Services email (SendGrid, Mailchimp)
├── cloud-storage/             # Stockage cloud (AWS S3, Google Cloud)
├── webhooks/                  # Notifications automatiques
└── apis/                      # APIs REST personnalisées
```

## 📋 Scénarios de test détaillés

### Workflow génération PDF standard

#### Scénario 1 : Commande e-commerce simple
```gherkin
Fonctionnalité: Génération facture PDF depuis WooCommerce

Contexte:
    Étant donné un site WooCommerce configuré
    Et un template facture PDF actif
    Et un client authentifié

Scénario: Génération automatique facture
    Quand une commande est passée avec statut "processing"
    Alors un PDF facture est automatiquement généré
    Et le PDF contient toutes les informations commande
    Et le PDF est attaché à l'email client
    Et le PDF est stocké dans l'historique commandes

Scénario: Personnalisation facture client VIP
    Étant donné un client avec statut VIP
    Quand une commande VIP est passée
    Alors le template VIP est utilisé
    Et des éléments personnalisés sont ajoutés
    Et la facture inclut offres spéciales

Scénario: Gestion erreurs génération
    Étant donné un template corrompu
    Quand la génération PDF échoue
    Alors une erreur est loggée
    Et un email admin est envoyé
    Et la commande reste en statut "processing"
    Et une tentative retry est programmée
```

#### Tests automatisés workflow
```python
#!/usr/bin/env python3
# test-pdf-generation-workflow.py

import pytest
import requests
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestPDFGenerationWorkflow:
    def setup_method(self):
        self.driver = webdriver.Chrome()
        self.driver.implicitly_wait(10)
        self.api_base = "https://staging.pdf-builder.com/wp-json/wp-pdf-builder/v1"

    def teardown_method(self):
        self.driver.quit()

    def test_complete_order_to_pdf_workflow(self):
        """Test workflow complet commande → PDF"""
        # 1. Création commande WooCommerce via API
        order_data = {
            "customer_id": 123,
            "line_items": [
                {"product_id": 456, "quantity": 2, "price": 29.99}
            ],
            "billing": {
                "first_name": "Jean",
                "last_name": "Dupont",
                "email": "jean.dupont@test.local"
            }
        }

        # Simulation commande (en vrai utiliser WooCommerce API)
        order_response = self.create_test_order(order_data)
        order_id = order_response['id']

        # 2. Vérification génération PDF automatique
        pdf_generation = self.wait_for_pdf_generation(order_id)
        assert pdf_generation['status'] == 'completed'

        # 3. Validation contenu PDF
        pdf_content = self.get_pdf_content(pdf_generation['pdf_url'])
        assert "Jean Dupont" in pdf_content
        assert "29.99" in pdf_content
        assert order_id in pdf_content

        # 4. Vérification email envoyé
        email_sent = self.check_email_sent(order_data['billing']['email'])
        assert email_sent is True

        # 5. Validation stockage historique
        history_entry = self.get_order_history(order_id)
        assert history_entry['pdf_generated'] is True
        assert history_entry['pdf_url'] is not None

    def test_vip_customer_customization(self):
        """Test personnalisation clients VIP"""
        # Client VIP
        vip_customer = {
            "id": 789,
            "vip_status": True,
            "custom_discount": 15
        }

        order_data = {
            "customer_id": vip_customer['id'],
            "line_items": [{"product_id": 456, "quantity": 1, "price": 29.99}]
        }

        order_response = self.create_test_order(order_data)
        pdf_generation = self.wait_for_pdf_generation(order_response['id'])

        # Vérifications VIP
        pdf_content = self.get_pdf_content(pdf_generation['pdf_url'])
        assert "VIP Customer" in pdf_content
        assert "15% Discount" in pdf_content
        assert "Premium Template" in pdf_content

    def test_error_handling_and_recovery(self):
        """Test gestion erreurs et recovery"""
        # Simulation template corrompu
        self.corrupt_template(123)

        order_data = {"customer_id": 123, "line_items": [{"product_id": 456, "quantity": 1}]}
        order_response = self.create_test_order(order_data)

        # Attendre première tentative d'échec
        failed_generation = self.wait_for_pdf_generation_failure(order_response['id'])
        assert failed_generation['status'] == 'failed'
        assert 'template_corrupted' in failed_generation['error']

        # Vérifier retry automatique
        retry_generation = self.wait_for_pdf_retry(order_response['id'])
        assert retry_generation['status'] == 'completed'

        # Vérifier notifications admin
        admin_notification = self.check_admin_notification()
        assert admin_notification['type'] == 'pdf_generation_error'
        assert order_response['id'] in admin_notification['message']

    # Helper methods
    def create_test_order(self, order_data):
        # Simulation création commande
        return {"id": 1001, "status": "processing"}

    def wait_for_pdf_generation(self, order_id, timeout=60):
        # Attendre génération PDF
        import time
        for _ in range(timeout):
            generation = self.get_pdf_generation_status(order_id)
            if generation['status'] == 'completed':
                return generation
            time.sleep(1)
        raise TimeoutError("PDF generation timeout")

    def get_pdf_content(self, pdf_url):
        # Extraction contenu PDF (utiliser pdfplumber ou similaire)
        return "Mock PDF content with customer data"

    def check_email_sent(self, email):
        # Vérification email envoyé
        return True

    def get_order_history(self, order_id):
        return {"pdf_generated": True, "pdf_url": "/pdfs/order_1001.pdf"}

    def corrupt_template(self, template_id):
        # Simulation corruption template
        pass

    def wait_for_pdf_generation_failure(self, order_id):
        return {"status": "failed", "error": "template_corrupted"}

    def wait_for_pdf_retry(self, order_id):
        return {"status": "completed"}

    def check_admin_notification(self):
        return {"type": "pdf_generation_error", "message": "PDF generation failed for order 1001"}
```

### Tests gestion templates avancée

#### Scénario gestion versions
```gherkin
Fonctionnalité: Gestion versions templates

Contexte:
    Étant donné un template PDF existant version 1.0
    Et l'utilisateur a droits édition

Scénario: Création nouvelle version
    Quand je modifie le template
    Et je sauvegarde comme nouvelle version
    Alors la version 1.1 est créée
    Et la version 1.0 reste disponible
    Et un historique versions est maintenu

Scénario: Restauration version précédente
    Étant donné un template version 2.0 avec problème
    Quand je restaure la version 1.5
    Alors le template revient à l'état version 1.5
    Et une nouvelle version 2.1 est créée
    Et l'historique inclut l'action restauration

Scénario: Comparaison versions
    Quand je compare version 1.0 et 2.0
    Alors les différences sont mises en évidence
    Et je peux voir qui a fait chaque modification
    Et les dates modifications sont affichées
```

#### Tests UI gestion templates
```javascript
// test-template-management-ui.js

describe('Template Management UI Tests', () => {
    beforeEach(() => {
        cy.login('admin', 'password');
        cy.visit('/wp-admin/admin.php?page=pdf-templates');
    });

    it('should create new template version', () => {
        // Ouvrir template existant
        cy.contains('Sample Invoice Template').click();

        // Modifier template
        cy.get('.template-editor').within(() => {
            cy.get('[data-element="header"]').type('Updated Header');
        });

        // Sauvegarder nouvelle version
        cy.contains('Save as New Version').click();
        cy.get('#version-comment').type('Added company logo');
        cy.contains('Save Version').click();

        // Vérifications
        cy.contains('Version 1.1 created successfully').should('be.visible');
        cy.get('.version-history').should('contain', '1.1');
        cy.get('.version-history').should('contain', '1.0');
    });

    it('should restore previous version', () => {
        // Aller dans historique versions
        cy.contains('Sample Invoice Template').click();
        cy.contains('Version History').click();

        // Sélectionner version à restaurer
        cy.get('.version-item').contains('1.5').within(() => {
            cy.contains('Restore').click();
        });

        // Confirmer restauration
        cy.contains('Restore Version').click();

        // Vérifications
        cy.contains('Version restored successfully').should('be.visible');
        cy.get('.current-version').should('contain', '2.1 (restored from 1.5)');
    });

    it('should compare template versions', () => {
        cy.contains('Sample Invoice Template').click();
        cy.contains('Version History').click();

        // Sélectionner versions à comparer
        cy.get('.version-checkbox').eq(0).check(); // v2.0
        cy.get('.version-checkbox').eq(2).check(); // v1.5
        cy.contains('Compare Selected').click();

        // Vérifier interface comparaison
        cy.get('.diff-view').should('be.visible');
        cy.get('.diff-added').should('contain', 'New field added');
        cy.get('.diff-removed').should('contain', 'Old field removed');

        // Vérifier métadonnées
        cy.get('.version-info').should('contain', 'Modified by: admin');
        cy.get('.version-info').should('contain', 'Date:');
    });

    it('should manage template permissions', () => {
        cy.contains('Sample Invoice Template').click();
        cy.contains('Permissions').click();

        // Ajouter permission utilisateur
        cy.get('#user-search').type('editor_user');
        cy.contains('editor_user').click();
        cy.get('#permission-level').select('edit');
        cy.contains('Add Permission').click();

        // Vérifications
        cy.contains('Permission added successfully').should('be.visible');
        cy.get('.permissions-list').should('contain', 'editor_user');
        cy.get('.permissions-list').should('contain', 'Can edit');

        // Tester comme utilisateur avec permission
        cy.logout();
        cy.login('editor_user', 'password');
        cy.visit('/wp-admin/admin.php?page=pdf-templates');

        cy.contains('Sample Invoice Template').should('be.visible');
        cy.contains('Sample Invoice Template').click();

        // Vérifier droits édition
        cy.get('.template-editor').should('be.visible');
        cy.get('.save-button').should('be.enabled');
    });
});
```

## 🔄 Tests intégrations système

### Intégration WooCommerce

#### Tests synchronisation commandes
```php
<?php
// test-woocommerce-integration.php

class WooCommerceIntegrationTest extends WP_UnitTestCase {
    public function setUp() {
        parent::setUp();

        // Configuration test WooCommerce
        $this->woocommerce = $this->setup_woocommerce();
        $this->pdf_builder = $this->setup_pdf_builder();
    }

    public function test_order_pdf_generation_trigger() {
        // Créer commande test
        $order = wc_create_order([
            'customer_id' => 1,
            'billing_email' => 'test@example.com',
            'line_items' => [
                [
                    'product_id' => 123,
                    'quantity' => 2,
                    'total' => 59.98
                ]
            ]
        ]);

        // Changer statut pour déclencher génération PDF
        $order->update_status('processing');

        // Attendre traitement asynchrone
        $this->wait_for_async_processing();

        // Vérifications
        $pdf_generated = get_post_meta($order->get_id(), '_pdf_generated', true);
        $this->assertTrue($pdf_generated, 'PDF should be generated for processing order');

        $pdf_url = get_post_meta($order->get_id(), '_pdf_url', true);
        $this->assertNotEmpty($pdf_url, 'PDF URL should be stored');

        // Vérifier contenu PDF
        $pdf_content = $this->extract_pdf_content($pdf_url);
        $this->assertContains('test@example.com', $pdf_content);
        $this->assertContains('59.98', $pdf_content);
    }

    public function test_bulk_order_pdf_generation() {
        // Créer plusieurs commandes
        $orders = [];
        for ($i = 0; $i < 10; $i++) {
            $order = wc_create_order([
                'customer_id' => $i + 1,
                'billing_email' => "customer{$i}@example.com",
                'line_items' => [
                    [
                        'product_id' => 123,
                        'quantity' => 1,
                        'total' => 29.99
                    ]
                ]
            ]);
            $order->update_status('processing');
            $orders[] = $order;
        }

        // Attendre traitement bulk
        $this->wait_for_bulk_processing();

        // Vérifications
        foreach ($orders as $order) {
            $pdf_generated = get_post_meta($order->get_id(), '_pdf_generated', true);
            $this->assertTrue($pdf_generated, "PDF should be generated for order {$order->get_id()}");

            $pdf_url = get_post_meta($order->get_id(), '_pdf_url', true);
            $this->assertNotEmpty($pdf_url, "PDF URL should exist for order {$order->get_id()}");
        }

        // Vérifier performance
        $processing_time = $this->get_bulk_processing_time();
        $this->assertLessThan(300, $processing_time, 'Bulk processing should complete within 5 minutes');
    }

    public function test_order_status_change_handling() {
        $order = wc_create_order([
            'customer_id' => 1,
            'line_items' => [['product_id' => 123, 'quantity' => 1]]
        ]);

        // Processing → PDF généré
        $order->update_status('processing');
        $this->wait_for_async_processing();
        $pdf_1 = get_post_meta($order->get_id(), '_pdf_url', true);

        // Completed → PDF mis à jour si nécessaire
        $order->update_status('completed');
        $this->wait_for_async_processing();
        $pdf_2 = get_post_meta($order->get_id(), '_pdf_url', true);

        // Vérifier si PDF régénéré ou même URL
        // (dépend configuration - certains statuts peuvent nécessiter régénération)
        $this->assertNotEmpty($pdf_2, 'PDF should exist after status change');
    }

    public function test_custom_order_fields_integration() {
        // Activer plugin champs personnalisés WooCommerce
        activate_plugin('woocommerce-custom-fields');

        $order = wc_create_order([
            'customer_id' => 1,
            'line_items' => [['product_id' => 123, 'quantity' => 1]],
            'meta_data' => [
                ['key' => 'delivery_date', 'value' => '2025-01-15'],
                ['key' => 'special_instructions', 'value' => 'Handle with care']
            ]
        ]);

        $order->update_status('processing');
        $this->wait_for_async_processing();

        // Vérifier champs personnalisés dans PDF
        $pdf_content = $this->extract_pdf_content(
            get_post_meta($order->get_id(), '_pdf_url', true)
        );

        $this->assertContains('January 15, 2025', $pdf_content);
        $this->assertContains('Handle with care', $pdf_content);
    }

    private function setup_woocommerce() {
        // Configuration WooCommerce test
        update_option('woocommerce_currency', 'EUR');
        update_option('woocommerce_default_country', 'FR');

        // Créer produits test
        wc_create_product([
            'name' => 'Test Product',
            'regular_price' => '29.99',
            'sku' => 'TEST-123'
        ]);

        return WC();
    }

    private function setup_pdf_builder() {
        // Configuration PDF Builder test
        update_option('pdf_builder_auto_generate', 'yes');
        update_option('pdf_builder_order_status_trigger', 'processing');

        // Créer template test
        $template_id = wp_insert_post([
            'post_title' => 'Test Invoice Template',
            'post_type' => 'pdf_template',
            'post_status' => 'publish'
        ]);

        update_post_meta($template_id, 'template_data', 'test_template_data');

        return $template_id;
    }

    private function wait_for_async_processing() {
        // Attendre traitement asynchrone (queue WordPress)
        sleep(5); // Ajuster selon configuration
    }

    private function wait_for_bulk_processing() {
        sleep(15); // Plus long pour traitement bulk
    }

    private function extract_pdf_content($pdf_url) {
        // Utiliser bibliothèque PDF pour extraire contenu
        // (tcpdf, fpdi, etc.)
        return 'Mock PDF content extraction';
    }

    private function get_bulk_processing_time() {
        // Mesurer temps traitement bulk
        return 180; // secondes
    }
}
```

### Tests APIs externes

#### Validation webhooks
```python
#!/usr/bin/env python3
# test-webhook-integrations.py

import pytest
import requests
import json
from unittest.mock import Mock, patch
from flask import Flask, request, jsonify

class TestWebhookIntegrations:
    def setup_method(self):
        self.app = Flask(__name__)
        self.client = self.app.test_client()
        self.webhook_url = "https://staging.pdf-builder.com/webhook/pdf-generated"

        # Configuration routes test
        self.setup_test_routes()

    def setup_test_routes(self):
        @self.app.route('/webhook/pdf-generated', methods=['POST'])
        def pdf_generated_webhook():
            data = request.get_json()
            # Traiter webhook
            return jsonify({"status": "received"}), 200

    def test_pdf_generation_webhook_trigger(self):
        """Test déclenchement webhook après génération PDF"""
        # Simuler génération PDF réussie
        pdf_data = {
            "order_id": 1001,
            "pdf_url": "/pdfs/order_1001.pdf",
            "customer_email": "customer@test.local",
            "total_amount": 149.99,
            "generated_at": "2025-01-15T10:30:00Z"
        }

        # Déclencher génération (simulée)
        with patch('requests.post') as mock_post:
            mock_post.return_value.status_code = 200

            # Simuler appel webhook
            response = self.trigger_pdf_generation(pdf_data)

            # Vérifier webhook appelé
            mock_post.assert_called_once_with(
                self.webhook_url,
                json=pdf_data,
                headers={'Content-Type': 'application/json'},
                timeout=30
            )

            # Vérifier réponse
            assert response['status'] == 'webhook_sent'

    def test_webhook_retry_on_failure(self):
        """Test retry webhook en cas d'échec"""
        pdf_data = {"order_id": 1002, "pdf_url": "/pdfs/order_1002.pdf"}

        # Simuler échecs successifs puis succès
        call_count = 0
        def mock_response(*args, **kwargs):
            nonlocal call_count
            call_count += 1
            if call_count < 3:
                # Échec HTTP
                response = Mock()
                response.status_code = 500
                response.raise_for_status.side_effect = requests.exceptions.HTTPError()
                return response
            else:
                # Succès
                response = Mock()
                response.status_code = 200
                return response

        with patch('requests.post', side_effect=mock_response) as mock_post:
            response = self.trigger_pdf_generation_with_retry(pdf_data)

            # Vérifier 3 tentatives
            assert mock_post.call_count == 3
            assert response['status'] == 'webhook_sent_after_retry'

    def test_webhook_payload_validation(self):
        """Test validation payload webhook"""
        # Payload valide
        valid_payload = {
            "event": "pdf_generated",
            "order_id": 1003,
            "pdf_url": "/pdfs/order_1003.pdf",
            "timestamp": "2025-01-15T10:30:00Z",
            "signature": "valid_signature"
        }

        response = self.client.post(
            '/webhook/pdf-generated',
            json=valid_payload,
            headers={'Content-Type': 'application/json'}
        )

        assert response.status_code == 200
        response_data = json.loads(response.data)
        assert response_data['status'] == 'received'

        # Payload invalide - champs manquants
        invalid_payload = {
            "event": "pdf_generated"
            # order_id manquant
        }

        response = self.client.post(
            '/webhook/pdf-generated',
            json=invalid_payload,
            headers={'Content-Type': 'application/json'}
        )

        assert response.status_code == 400

    def test_webhook_security_validation(self):
        """Test sécurité webhook - signature"""
        payload = {
            "event": "pdf_generated",
            "order_id": 1004,
            "pdf_url": "/pdfs/order_1004.pdf"
        }

        # Signature valide
        import hmac
        import hashlib
        secret = "webhook_secret_key"
        signature = hmac.new(
            secret.encode(),
            json.dumps(payload, sort_keys=True).encode(),
            hashlib.sha256
        ).hexdigest()

        headers = {
            'Content-Type': 'application/json',
            'X-Signature': signature
        }

        response = self.client.post('/webhook/pdf-generated', json=payload, headers=headers)
        assert response.status_code == 200

        # Signature invalide
        headers['X-Signature'] = 'invalid_signature'
        response = self.client.post('/webhook/pdf-generated', json=payload, headers=headers)
        assert response.status_code == 403

    def test_concurrent_webhook_processing(self):
        """Test traitement webhooks concurrents"""
        import threading
        import time

        results = []
        errors = []

        def send_webhook(payload, index):
            try:
                response = self.client.post(
                    '/webhook/pdf-generated',
                    json=payload,
                    headers={'Content-Type': 'application/json'}
                )
                results.append((index, response.status_code))
            except Exception as e:
                errors.append((index, str(e)))

        # Lancer 10 webhooks simultanés
        threads = []
        for i in range(10):
            payload = {
                "event": "pdf_generated",
                "order_id": 1000 + i,
                "pdf_url": f"/pdfs/order_{1000 + i}.pdf"
            }
            thread = threading.Thread(target=send_webhook, args=(payload, i))
            threads.append(thread)
            thread.start()

        # Attendre completion
        for thread in threads:
            thread.join()

        # Vérifications
        assert len(results) == 10, f"Expected 10 results, got {len(results)}"
        assert len(errors) == 0, f"Unexpected errors: {errors}"

        for index, status_code in results:
            assert status_code == 200, f"Webhook {index} failed with status {status_code}"

    # Helper methods
    def trigger_pdf_generation(self, pdf_data):
        # Simuler déclenchement génération PDF
        return {"status": "webhook_sent"}

    def trigger_pdf_generation_with_retry(self, pdf_data):
        # Simuler avec retry logic
        return {"status": "webhook_sent_after_retry"}
```

## 👥 Tests acceptation utilisateur (UAT)

### Organisation sessions UAT

#### Planning et préparation
```markdown
# Plan UAT - Phase 7.4

## Objectifs
- Valider fonctionnalités métier critiques
- Collecter feedback utilisateurs finaux
- Identifier problèmes UX/UI
- Confirmer performance acceptable

## Participants
- **Product Owner** : Jean Dupont
- **Business Analyst** : Marie Martin
- **Utilisateurs clés** :
  - Alice (Service Commercial)
  - Bob (Service Comptabilité)
  - Charlie (Service Client)
- **Équipe développement** : Support technique

## Environnement
- **URL** : https://uat.pdf-builder.com
- **Données** : Anonymisées production
- **Monitoring** : Activé temps réel

## Planning sessions
- **Session 1** : 15/01/2025 10:00-12:00 - Fonctionnalités core
- **Session 2** : 16/01/2025 10:00-12:00 - Intégrations
- **Session 3** : 17/01/2025 10:00-12:00 - Performance et charge
```

#### Scripts de test UAT
```gherkin
# uat-test-scripts.feature

Fonctionnalité: Scripts UAT - Génération PDF

@critical @smoke
Scénario: UAT-001 - Création template basique
    Étant donné que je suis connecté comme administrateur
    Quand je vais dans "PDF Templates > Add New"
    Et je saisis un nom de template
    Et j'ajoute un élément texte "Facture"
    Et je sauvegarde le template
    Alors le template est créé avec succès
    Et il apparaît dans la liste des templates

@critical @workflow
Scénario: UAT-002 - Génération PDF commande WooCommerce
    Étant donné qu'il y a une commande en statut "En cours"
    Quand je vais dans les détails de la commande
    Et je clique sur "Générer PDF"
    Alors un PDF est généré
    Et il contient les bonnes informations commande
    Et il est automatiquement attaché à la commande

@important @integration
Scénario: UAT-003 - Intégration email automatique
    Étant donné qu'un PDF vient d'être généré
    Quand le système envoie l'email client
    Alors l'email contient le PDF en pièce jointe
    Et le sujet est "Votre facture #123"
    Et le corps contient le lien de téléchargement

@performance
Scénario: UAT-004 - Génération PDF sous charge
    Étant donné que 50 utilisateurs génèrent simultanément des PDF
    Quand j'attends la fin des générations
    Alors tous les PDF sont générés en moins de 30 secondes
    Et aucun échec n'est observé
    Et les ressources serveur restent stables
```

### Collecte feedback utilisateurs

#### Formulaire évaluation UAT
```html
<!-- uat-feedback-form.html -->
<div class="uat-feedback-form">
    <h3>Évaluation Session UAT</h3>

    <form id="feedback-form">
        <!-- Informations session -->
        <div class="form-section">
            <h4>Informations générales</h4>
            <label>Nom évaluateur: <input type="text" name="evaluator_name" required></label>
            <label>Rôle: <input type="text" name="evaluator_role" required></label>
            <label>Scénario testé: <select name="test_scenario" required>
                <option value="">Sélectionner...</option>
                <option value="template-creation">Création template</option>
                <option value="pdf-generation">Génération PDF</option>
                <option value="woocommerce-integration">Intégration WooCommerce</option>
                <option value="performance">Performance</option>
            </select></label>
        </div>

        <!-- Évaluation fonctionnalités -->
        <div class="form-section">
            <h4>Évaluation fonctionnalités</h4>

            <div class="rating-question">
                <label>Fonctionnalité répond-elle aux besoins métier?</label>
                <div class="rating-scale">
                    <input type="radio" name="business_fit" value="1" id="bf1"><label for="bf1">1</label>
                    <input type="radio" name="business_fit" value="2" id="bf2"><label for="bf2">2</label>
                    <input type="radio" name="business_fit" value="3" id="bf3"><label for="bf3">3</label>
                    <input type="radio" name="business_fit" value="4" id="bf4"><label for="bf4">4</label>
                    <input type="radio" name="business_fit" value="5" id="bf5"><label for="bf5">5</label>
                </div>
                <small>1 = Ne répond pas du tout, 5 = Répond parfaitement</small>
            </div>

            <div class="rating-question">
                <label>Facilité d'utilisation (UX)?</label>
                <div class="rating-scale">
                    <input type="radio" name="usability" value="1" id="u1"><label for="u1">1</label>
                    <input type="radio" name="usability" value="2" id="u2"><label for="u2">2</label>
                    <input type="radio" name="usability" value="3" id="u3"><label for="u3">3</label>
                    <input type="radio" name="usability" value="4" id="u4"><label for="u4">4</label>
                    <input type="radio" name="usability" value="5" id="u5"><label for="u5">5</label>
                </div>
            </div>

            <div class="rating-question">
                <label>Performance acceptable?</label>
                <div class="rating-scale">
                    <input type="radio" name="performance" value="1" id="p1"><label for="p1">1</label>
                    <input type="radio" name="usability" value="2" id="p2"><label for="p2">2</label>
                    <input type="radio" name="usability" value="3" id="p3"><label for="p3">3</label>
                    <input type="radio" name="usability" value="4" id="p4"><label for="p4">4</label>
                    <input type="radio" name="usability" value="5" id="p5"><label for="p5">5</label>
                </div>
            </div>
        </div>

        <!-- Problèmes identifiés -->
        <div class="form-section">
            <h4>Problèmes identifiés</h4>
            <textarea name="issues" rows="5" placeholder="Décrire les problèmes rencontrés, bugs, ou points d'amélioration..."></textarea>

            <div class="issue-priority">
                <label>Priorité du problème principal:</label>
                <select name="issue_priority">
                    <option value="">Aucun problème majeur</option>
                    <option value="low">Faible - Amélioration mineure</option>
                    <option value="medium">Moyen - Impact fonctionnel limité</option>
                    <option value="high">Élevé - Bloquant pour utilisation</option>
                    <option value="critical">Critique - Impossible d'utiliser</option>
                </select>
            </div>
        </div>

        <!-- Recommandations -->
        <div class="form-section">
            <h4>Recommandations</h4>
            <textarea name="recommendations" rows="3" placeholder="Suggestions d'amélioration, nouvelles fonctionnalités..."></textarea>
        </div>

        <!-- Validation -->
        <div class="form-section">
            <h4>Validation</h4>
            <label>
                <input type="checkbox" name="approve_for_production" value="yes">
                J'approuve ce déploiement en production
            </label>

            <label>
                <input type="checkbox" name="require_fixes" value="yes">
                Des corrections sont nécessaires avant déploiement
            </label>
        </div>

        <button type="submit" class="submit-feedback">Soumettre évaluation</button>
    </form>
</div>

<style>
.uat-feedback-form { max-width: 800px; margin: 0 auto; }
.form-section { margin: 2rem 0; padding: 1rem; border: 1px solid #ddd; border-radius: 5px; }
.rating-scale { display: flex; gap: 1rem; margin: 0.5rem 0; }
.rating-scale input[type="radio"] { display: none; }
.rating-scale label { padding: 0.5rem 1rem; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; }
.rating-scale input[type="radio"]:checked + label { background: #007cba; color: white; }
.submit-feedback { background: #007cba; color: white; padding: 1rem 2rem; border: none; border-radius: 5px; cursor: pointer; }
</style>
```

#### Traitement résultats UAT
```python
#!/usr/bin/env python3
# process-uat-feedback.py

import pandas as pd
import matplotlib.pyplot as plt
from collections import Counter
import json

class UATFeedbackProcessor:
    def __init__(self, feedback_file):
        with open(feedback_file, 'r') as f:
            self.feedback_data = json.load(f)

    def generate_uat_report(self):
        """Génère rapport complet UAT"""
        df = pd.DataFrame(self.feedback_data)

        report = {
            'summary': self._calculate_summary_metrics(df),
            'detailed_ratings': self._analyze_ratings(df),
            'issues_analysis': self._analyze_issues(df),
            'recommendations': self._compile_recommendations(df),
            'approval_status': self._check_approval_status(df)
        }

        return report

    def _calculate_summary_metrics(self, df):
        """Calcule métriques générales"""
        total_evaluators = len(df)
        scenarios_tested = df['test_scenario'].value_counts().to_dict()

        # Score moyen par catégorie
        avg_scores = {}
        rating_columns = ['business_fit', 'usability', 'performance']
        for col in rating_columns:
            if col in df.columns:
                avg_scores[col] = df[col].astype(float).mean()

        # Taux approbation
        approval_rate = (df['approve_for_production'] == 'yes').mean() * 100

        return {
            'total_evaluators': total_evaluators,
            'scenarios_tested': scenarios_tested,
            'average_scores': avg_scores,
            'approval_rate': approval_rate
        }

    def _analyze_ratings(self, df):
        """Analyse détaillée des notes"""
        rating_analysis = {}

        for scenario in df['test_scenario'].unique():
            scenario_data = df[df['test_scenario'] == scenario]
            rating_analysis[scenario] = {
                'count': len(scenario_data),
                'business_fit': scenario_data['business_fit'].astype(float).describe().to_dict(),
                'usability': scenario_data['usability'].astype(float).describe().to_dict(),
                'performance': scenario_data['performance'].astype(float).describe().to_dict()
            }

        return rating_analysis

    def _analyze_issues(self, df):
        """Analyse problèmes identifiés"""
        issues = []

        for _, row in df.iterrows():
            if row.get('issues') and row['issues'].strip():
                issues.append({
                    'evaluator': row['evaluator_name'],
                    'scenario': row['test_scenario'],
                    'issue': row['issues'],
                    'priority': row.get('issue_priority', 'unknown')
                })

        # Comptage par priorité
        priority_counts = Counter([issue['priority'] for issue in issues])

        return {
            'total_issues': len(issues),
            'issues_by_priority': dict(priority_counts),
            'detailed_issues': issues
        }

    def _compile_recommendations(self, df):
        """Compile recommandations"""
        recommendations = []

        for _, row in df.iterrows():
            if row.get('recommendations') and row['recommendations'].strip():
                recommendations.append({
                    'evaluator': row['evaluator_name'],
                    'scenario': row['test_scenario'],
                    'recommendation': row['recommendations']
                })

        return recommendations

    def _check_approval_status(self, df):
        """Vérifie statut approbation production"""
        total_evaluators = len(df)
        approved_count = (df['approve_for_production'] == 'yes').sum()
        require_fixes_count = (df['require_fixes'] == 'yes').sum()

        approval_status = 'pending'

        if approved_count == total_evaluators and require_fixes_count == 0:
            approval_status = 'approved'
        elif require_fixes_count > 0:
            approval_status = 'requires_fixes'
        elif approved_count < total_evaluators * 0.8:  # Moins de 80% approbation
            approval_status = 'rejected'

        return {
            'status': approval_status,
            'approved_count': approved_count,
            'require_fixes_count': require_fixes_count,
            'total_evaluators': total_evaluators
        }

    def generate_visual_report(self, output_file='uat_report.png'):
        """Génère graphique rapport UAT"""
        df = pd.DataFrame(self.feedback_data)

        fig, ((ax1, ax2), (ax3, ax4)) = plt.subplots(2, 2, figsize=(12, 8))

        # Scores moyens par catégorie
        categories = ['business_fit', 'usability', 'performance']
        scores = [df[col].astype(float).mean() for col in categories]
        ax1.bar(categories, scores)
        ax1.set_title('Scores Moyens par Catégorie')
        ax1.set_ylim(0, 5)

        # Distribution scénarios testés
        scenario_counts = df['test_scenario'].value_counts()
        ax2.pie(scenario_counts.values, labels=scenario_counts.index, autopct='%1.1f%%')
        ax2.set_title('Répartition Scénarios Testés')

        # Priorités problèmes
        priority_counts = Counter()
        for _, row in df.iterrows():
            if row.get('issue_priority'):
                priority_counts[row['issue_priority']] += 1

        priorities = list(priority_counts.keys())
        counts = list(priority_counts.values())
        ax3.bar(priorities, counts)
        ax3.set_title('Priorités Problèmes Identifiés')

        # Statut approbation
        approval_status = self._check_approval_status(df)
        statuses = ['Approuvé', 'Nécessite corrections', 'Rejeté']
        status_counts = [
            approval_status['approved_count'],
            approval_status['require_fixes_count'],
            approval_status['total_evaluators'] - approval_status['approved_count'] - approval_status['require_fixes_count']
        ]
        ax4.bar(statuses, status_counts)
        ax4.set_title('Statut Approbation Production')

        plt.tight_layout()
        plt.savefig(output_file, dpi=300, bbox_inches='tight')
        plt.close()

# Utilisation
processor = UATFeedbackProcessor('uat_feedback.json')
report = processor.generate_uat_report()
processor.generate_visual_report()

print("UAT Report Summary:")
print(f"Total Evaluators: {report['summary']['total_evaluators']}")
print(f"Approval Rate: {report['summary']['approval_rate']:.1f}%")
print(f"Total Issues: {report['issues_analysis']['total_issues']}")
print(f"Approval Status: {report['approval_status']['status']}")
```

---

*Guide Tests Validation Métier - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\testing\business-validation\business-validation-guide.md