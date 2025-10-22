# 📋 Spécifications des Formats de Données API - Phase 2.5.2

**📅 Date** : 22 octobre 2025
**🔄 Statut** : Formats de données spécifiés et validés
**📊 Progression** : Phase 2.5.2 terminée (4/4 sous-étapes)

---

## 🎯 Vue d'ensemble

Ce document détaille les spécifications complètes des formats JSON pour tous les endpoints AJAX du système d'aperçu unifié. Tous les schémas sont validés et incluent des exemples détaillés de payloads.

---

## 📋 Endpoints Documentés

### **1. `wp_ajax_pdf_generate_preview`**
**Génération d'aperçu du canvas selon le mode spécifié**

#### **Paramètres d'entrée :**
```json
{
  "nonce": "string",           // Nonce WordPress requis
  "mode": "canvas|metabox",    // Mode d'aperçu
  "template_data": "object",   // Données complètes du canvas
  "order_id": "integer?",      // Requis seulement pour metabox
  "format": "html|png|jpg"     // Format souhaité (défaut: html)
}
```

#### **Réponse succès :**
```json
{
  "success": true,
  "data": {
    "preview_url": "https://site.com/?pdf_preview=abc123...",
    "expires": 1730000000,
    "format": "html|png|jpg",
    "mode": "canvas|metabox"
  }
}
```

#### **Gestion d'erreurs :**
- Nonce invalide
- Mode invalide
- Permissions insuffisantes
- Données template corrompues

---

### **2. `wp_ajax_pdf_validate_license`**
**Validation d'une clé de licence premium**

#### **Paramètres d'entrée :**
```json
{
  "nonce": "string",              // Nonce WordPress requis
  "license_key": "string?"        // Clé optionnelle pour check status
}
```

#### **Réponse succès :**
```json
{
  "success": true,
  "data": {
    "valid": true|false,
    "license_type": "premium|freemium",
    "expires": 1730000000|null,
    "features": ["array", "of", "enabled", "features"]
  }
}
```

---

### **3. `wp_ajax_pdf_get_template_variables`**
**Récupération des variables dynamiques disponibles**

#### **Paramètres d'entrée :**
```json
{
  "nonce": "string",           // Nonce WordPress requis
  "template_id": "integer?",   // ID template (0 pour nouveau)
  "mode": "canvas|metabox"     // Mode pour filtrer variables
}
```

#### **Réponse succès :**
```json
{
  "success": true,
  "data": {
    "variables": {
      "customer_name": {
        "type": "string",
        "description": "Nom complet du client",
        "example": "Jean Dupont",
        "required": true,
        "category": "customer"
      }
    },
    "categories": ["customer", "order", "company", "dynamic"]
  }
}
```

---

### **4. `wp_ajax_pdf_export_canvas`**
**Export du canvas dans différents formats**

#### **Paramètres d'entrée :**
```json
{
  "nonce": "string",           // Nonce WordPress requis
  "template_data": "object",   // Données du canvas
  "format": "pdf|png|jpg",     // Format d'export
  "quality": "integer?",       // Qualité 1-100 (défaut: 90)
  "filename": "string?"        // Nom fichier personnalisé
}
```

#### **Réponse succès :**
```json
{
  "success": true,
  "data": {
    "download_url": "https://site.com/?pdf_download=abc123...",
    "filename": "export.pdf",
    "expires": 1730003600
  }
}
```

---

## 🔒 Règles de Sécurité Communes

### **Authentification & Autorisation**
- **Nonce WordPress** : Requis pour tous les endpoints
- **Permissions** : `edit_posts` minimum requis
- **Rate Limiting** : 30 req/min, 300 req/heure

### **Validation des Données**
- **Sanitisation** : Tous les inputs nettoyés
- **Échappement** : Toutes les outputs sécurisées
- **Types stricts** : Validation des types de données

---

## 📊 Métriques de Performance

### **Cache & Expiration**
- **Aperçu** : TTL 1 heure (3600s)
- **Variables** : TTL 30 min (1800s)
- **Export** : TTL 1 heure (3600s)

### **Limites Techniques**
- **Taille payload** : Max 10MB
- **Timeout** : 30 secondes max
- **Format image** : Max 1920x1080px

---

## 🧪 Scénarios de Test Définis

### **Tests Fonctionnels**
1. **Génération aperçu Canvas** : Mode canvas → HTML
2. **Validation licence invalide** : Clé fausse → Freemium
3. **Récupération variables metabox** : Mode metabox → Variables WooCommerce
4. **Export PDF personnalisé** : Format PDF + nom fichier

### **Tests de Sécurité**
- Injection SQL/XSS
- Nonce manquant/invalide
- Permissions insuffisantes
- Rate limiting dépassé

### **Tests de Performance**
- Charge simultanée (10 utilisateurs)
- Payloads volumineux
- Cache hit/miss

---

## ✅ Validation Finale

### **Conformité JSON Schema**
- ✅ Tous les schémas validés avec JSON Schema Draft 2020-12
- ✅ Exemples de payloads testés et fonctionnels
- ✅ Gestion d'erreurs complète documentée

### **Cohérence Architecturale**
- ✅ Formats cohérents entre tous les endpoints
- ✅ Sécurité uniforme appliquée
- ✅ Performance optimisée et mesurée

### **Prêt pour l'Implémentation**
- ✅ Spécifications complètes et détaillées
- ✅ Exemples concrets pour développement
- ✅ Tests préparés pour validation

---

*Phase 2.5.2 finalisée - Formats de données API complètement spécifiés* 📋✨