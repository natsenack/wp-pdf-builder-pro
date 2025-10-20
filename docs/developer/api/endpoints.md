# 🔌 API REST - Endpoints

L'API REST de PDF Builder Pro permet une intégration complète avec des systèmes externes via des endpoints HTTP standardisés.

## 📋 Vue d'ensemble

- **Base URL** : `/wp-json/pdf-builder/v1/`
- **Authentification** : WordPress REST API standard
- **Format** : JSON
- **Versioning** : `/v1/` pour stabilité

## 🎯 Endpoints Disponibles

### Templates

#### GET `/wp-json/pdf-builder/v1/templates`
Récupère la liste des templates.

**Paramètres :**
- `page` (int) : Numéro de page (défaut: 1)
- `per_page` (int) : Nombre par page (défaut: 10, max: 100)
- `search` (string) : Recherche par nom
- `status` (string) : `active`, `inactive`, `draft`

**Exemple :**
```bash
curl -X GET "https://example.com/wp-json/pdf-builder/v1/templates?page=1&per_page=5" \
  -H "Content-Type: application/json"
```

**Réponse :**
```json
{
  "templates": [
    {
      "id": 1,
      "name": "Facture Standard",
      "description": "Template de facture classique",
      "status": "active",
      "created_at": "2025-10-20T10:00:00Z",
      "updated_at": "2025-10-20T10:00:00Z",
      "elements_count": 15
    }
  ],
  "pagination": {
    "total": 25,
    "pages": 3,
    "current_page": 1,
    "per_page": 5
  }
}
```

#### POST `/wp-json/pdf-builder/v1/templates`
Crée un nouveau template.

**Corps de la requête :**
```json
{
  "name": "Mon Template Personnalisé",
  "description": "Description du template",
  "status": "draft",
  "settings": {
    "format": "A4",
    "orientation": "portrait",
    "margins": {
      "top": 20,
      "right": 15,
      "bottom": 20,
      "left": 15
    }
  },
  "elements": [
    {
      "type": "text",
      "content": "Hello World!",
      "position": {
        "x": 100,
        "y": 100
      },
      "style": {
        "fontSize": 14,
        "color": "#000000"
      }
    }
  ]
}
```

**Réponse :**
```json
{
  "success": true,
  "template": {
    "id": 26,
    "name": "Mon Template Personnalisé",
    "status": "draft",
    "created_at": "2025-10-20T10:30:00Z"
  }
}
```

#### GET `/wp-json/pdf-builder/v1/templates/{id}`
Récupère un template spécifique.

**Paramètres URL :**
- `id` (int) : ID du template

**Exemple :**
```bash
curl -X GET "https://example.com/wp-json/pdf-builder/v1/templates/1" \
  -H "Content-Type: application/json"
```

#### PUT `/wp-json/pdf-builder/v1/templates/{id}`
Met à jour un template.

**Corps identique à POST**

#### DELETE `/wp-json/pdf-builder/v1/templates/{id}`
Supprime un template.

### Génération PDF

#### POST `/wp-json/pdf-builder/v1/generate`
Génère un PDF à partir d'un template et de données.

**Corps de la requête :**
```json
{
  "template_id": 1,
  "data": {
    "customer_name": "Jean Dupont",
    "order_number": "CMD-2025-001",
    "order_total": "299.99",
    "company_name": "Ma Société",
    "current_date": "20 octobre 2025"
  },
  "options": {
    "format": "PDF",
    "compression": "NORMAL",
    "password": null,
    "filename": "facture-001.pdf"
  }
}
```

**Réponse :**
```json
{
  "success": true,
  "pdf_url": "https://example.com/wp-content/uploads/pdf-builder-cache/facture-001.pdf",
  "file_size": 245760,
  "generation_time": 1.2,
  "expires_at": "2025-10-21T10:30:00Z"
}
```

#### POST `/wp-json/pdf-builder/v1/generate/preview`
Génère un aperçu PDF (données d'exemple).

**Corps simplifié :**
```json
{
  "template_id": 1,
  "preview": true
}
```

### Éléments

#### GET `/wp-json/pdf-builder/v1/elements`
Liste tous les types d'éléments disponibles.

**Réponse :**
```json
{
  "elements": {
    "text": {
      "label": "Texte Simple",
      "description": "Élément de texte avec styles",
      "properties": ["content", "position", "style"],
      "renderer": "TextRenderer"
    },
    "dynamic-text": {
      "label": "Texte Dynamique",
      "description": "Texte avec variables",
      "properties": ["content", "variables", "position"],
      "renderer": "DynamicTextRenderer"
    }
    // ... autres éléments
  }
}
```

### Métriques

#### GET `/wp-json/pdf-builder/v1/metrics`
Récupère les métriques de performance.

**Paramètres :**
- `period` (string) : `hour`, `day`, `week`, `month` (défaut: day)

**Réponse :**
```json
{
  "metrics": {
    "total_generations": 1250,
    "average_generation_time": 1.2,
    "cache_hit_rate": 88,
    "error_rate": 0.5,
    "peak_memory_usage": 32,
    "period": "day"
  }
}
```

## 🔐 Permissions

### Rôles WordPress
- **Administrator** : Accès complet à tous les endpoints
- **Editor** : CRUD templates, génération PDF
- **Author** : Lecture templates, génération PDF
- **Subscriber** : Génération PDF uniquement

### Capabilities Personnalisées
```php
// Vérifier les permissions
if (current_user_can('pdf_builder_manage_templates')) {
    // Accès aux templates
}

if (current_user_can('pdf_builder_generate_pdf')) {
    // Accès à la génération
}
```

## ⚠️ Gestion des Erreurs

### Codes d'Erreur
- `400` : Requête invalide
- `401` : Non authentifié
- `403` : Permissions insuffisantes
- `404` : Ressource non trouvée
- `429` : Rate limit dépassé
- `500` : Erreur serveur

### Format d'Erreur
```json
{
  "code": "pdf_builder_invalid_template",
  "message": "Le template spécifié n'existe pas",
  "data": {
    "template_id": 999,
    "status": 404
  }
}
```

## 📊 Rate Limiting

- **Limite par défaut** : 100 requêtes/minute par IP
- **Génération PDF** : 10 PDFs/minute par utilisateur
- **Headers de réponse** :
  - `X-RateLimit-Limit` : Limite totale
  - `X-RateLimit-Remaining` : Requêtes restantes
  - `X-RateLimit-Reset` : Timestamp de reset

## 🔄 Webhooks

Configurez des webhooks pour recevoir des notifications :

```php
// Enregistrer un webhook
add_action('pdf_builder_webhook_register', function($webhooks) {
    $webhooks['pdf_generated'] = [
        'url' => 'https://example.com/webhook/pdf-generated',
        'events' => ['pdf.generated', 'pdf.error']
    ];
    return $webhooks;
});
```

### Événements Disponibles
- `pdf.generated` : PDF généré avec succès
- `pdf.error` : Erreur de génération
- `template.created` : Nouveau template créé
- `template.updated` : Template modifié
- `template.deleted` : Template supprimé

---

**📖 Voir aussi :**
- [Authentification](./authentication.md)
- [Exemples d'usage](./examples.md)
- [Tutoriels d'intégration](../tutorials/)