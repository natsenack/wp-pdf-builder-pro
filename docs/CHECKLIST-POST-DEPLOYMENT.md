# ✅ CHECKLIST POST-DÉPLOIEMENT

**Date :** 19 octobre 2025  
**Responsable :** Équipe Support  
**Priorité :** HAUTE - À vérifier dans les 24 heures

---

## 📋 Phase 1 : Vérification Technique (0-1 heure)

### 1. Vérifier que le fichier est déployé

- [ ] Fichier `PDF_Builder_Template_Manager.php` présent sur le serveur
- [ ] Taille du fichier : ~22.6 KB
- [ ] Permissions de fichier : 644 ou 755

**Commande FTP :**
```bash
ls -lh wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_Template_Manager.php
```

### 2. Vérifier l'activation du logging

- [ ] Fichier `wp-config.php` a `WP_DEBUG` = true
- [ ] Fichier `wp-config.php` a `WP_DEBUG_LOG` = true
- [ ] Répertoire `wp-content/` est accessible en écriture

**Fichier `wp-config.php` doit contenir :**
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 3. Vérifier l'accès à WordPress

- [ ] Backend WordPress accessible
- [ ] Utilisateur admin peut se connecter
- [ ] Aucune erreur PHP sur la page d'accueil

---

## 📝 Phase 2 : Test Fonctionnel (1-2 heures)

### 1. Test de Sauvegarde Simple

**Étapes :**
1. Se connecter en tant qu'admin
2. Accéder à : PDF Builder → Éditeur Templates
3. Ouvrir un template existant OU en créer un nouveau
4. Effectuer une modification mineure (ex: changer la couleur d'un élément)
5. Cliquer sur "Sauvegarder"

**Résultats attendus :**
- [ ] Le template est sauvegardé sans erreur
- [ ] Le navigateur affiche un message "Modifications du canvas sauvegardées"
- [ ] Dans la console du navigateur : aucune erreur 500

### 2. Vérifier les Logs

**Commande :**
```bash
tail -f wp-content/debug.log | grep "PDF Builder"
```

**Résultats attendus :**
- [ ] Au moins une ligne "✅ SUCCÈS" apparaît
- [ ] Les 9 étapes de logging sont visibles
- [ ] Pas d'erreur "❌" (sauf dans les logs anciens)

**Exemple de logs attendus :**
```
[PDF Builder] Template Save - ✅ Permissions vérifiées pour user ID 1
[PDF Builder] Template Save - ✅ Nonce valide
[PDF Builder] Template Save - ✅ JSON valide
[PDF Builder] Template Save - ✅ Structure validée
[PDF Builder] Template Save - ✅ SUCCÈS: Template ID=X sauvegardé
```

### 3. Test avec Template Invalide

**Étapes :**
1. Accéder à la base de données via phpMyAdmin
2. Trouver la table `wp_pdf_builder_templates`
3. Éditer un template et supprimer la propriété `"version"`
4. Sauvegarder
5. Retourner au backend et essayer de charger ce template

**Résultats attendus :**
- [ ] Le log affiche une erreur "Structure invalide"
- [ ] L'erreur spécifie "Propriété obligatoire manquante: 'version'"
- [ ] Le template n'est pas corrompu en BD

---

## 🔍 Phase 3 : Analyse des Logs (2-4 heures)

### 1. Compter les Sauvegardes Réussies

**Commande :**
```bash
grep "Template Save.*SUCCÈS" wp-content/debug.log | wc -l
```

**Cible :** Au moins 1 (de notre test)

### 2. Vérifier l'Absence d'Erreurs Critiques

**Commande :**
```bash
grep "Template Save.*❌" wp-content/debug.log
```

**Résultat attendu :** Aucune ligne (ou seulement les anciens logs)

### 3. Analyser les Performances

**Commande :**
```bash
grep "Template Save" wp-content/debug.log | tail -10
```

**Vérifier :**
- [ ] Les timestamps sont proches (pas de lenteur)
- [ ] Les éléments sont sauvegardés correctement
- [ ] Pas de patterns d'erreurs récurrentes

---

## 🛡️ Phase 4 : Vérification de Sécurité (30 minutes)

### 1. Vérifier que les Permissions sont Appliquées

**Test :**
1. Se déconnecter du backend
2. Essayer d'accéder à l'AJAX directement :
   ```
   POST /wp-admin/admin-ajax.php?action=pdf_builder_pro_save_template
   ```

**Résultat attendu :**
- [ ] Erreur "Permissions insuffisantes" retournée
- [ ] Log affiche "❌ Permissions insuffisantes pour user ID 0"

### 2. Vérifier le Nonce

**Test :**
1. Se connecter en tant qu'admin
2. Modifier une sauvegarde et envoyer un nonce invalide

**Résultat attendu :**
- [ ] Erreur "Nonce invalide" retournée
- [ ] Log affiche "❌ Nonce invalide reçu"

### 3. Vérifier la Validation JSON

**Test :**
1. Envoyer un JSON malformé via AJAX

**Résultat attendu :**
- [ ] Erreur "Données JSON invalides" retournée
- [ ] Log affiche "❌ Erreur JSON: [détails]"

---

## 📊 Phase 5 : Report (1 heure)

### Créer un Rapport

**Fichier à créer :** `POST-DEPLOYMENT-REPORT-19-OCT-2025.md`

Inclure :
```
✅ Tests réussis : [Nombre]
❌ Tests échoués : [Nombre]
⚠️ Anomalies : [Liste]
📊 Performances : [Résumé]
🔐 Sécurité : [Statut]
📝 Recommandations : [Liste]
```

---

## 🆘 Si Quelque Chose ne Fonctionne Pas

### Erreur : Fichier Non Trouvé

```bash
# Vérifier que le fichier existe
ls -lh /path/to/wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_Template_Manager.php

# Vérifier les permissions
stat /path/to/wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_Template_Manager.php
```

**Solution :** Redéployer via FTP

### Erreur : Pas de Logs

```bash
# Vérifier que debug.log existe
ls -lh wp-content/debug.log

# Vérifier les permissions
stat wp-content/debug.log

# Vérifier wp-config.php
grep WP_DEBUG wp-config.php
```

**Solution :** Activer le debug et créer le fichier de logs

### Erreur : Validation Échoue

```bash
# Vérifier les logs d'erreur
grep "Template Save.*❌" wp-content/debug.log

# Vérifier le template en BD
SELECT id, name, LENGTH(template_data) FROM wp_pdf_builder_templates;
```

**Solution :** Vérifier la structure du template en BD

### Erreur : Performance Dégradée

```bash
# Comparer les temps de sauvegarde
grep "Template Save" wp-content/debug.log | tail -5

# Analyser les éléments
grep "éléments=" wp-content/debug.log
```

**Solution :** Réduire le nombre d'éléments ou optimiser

---

## 📞 Escalade si Nécessaire

Si après ces étapes le problème persiste :

### Contacts

1. **Support Technique :** [Email support]
2. **Développeur Principal :** [Contact]
3. **Administrateur Serveur :** [Contact]

### Informations à Fournir

1. Ligne de log complète avec erreur
2. Screenshot de l'erreur
3. WordPress version
4. PHP version
5. Étapes pour reproduire

---

## ✅ Validation Finale

### Signature de Clôture

Après complétude de toutes les étapes :

```
Déploiement date    : 19 octobre 2025
Personne responsable : [Nom]
Date de vérification : [Date]
Statut              : ✅ VALIDE / ⚠️ À CORRIGER / ❌ ÉCHOUÉ
```

---

## 🎯 Résumé en 5 Points

1. ✅ **Code Vérifié**
   - Fichier PHP présent sur le serveur
   - Permissions correctes
   - Pas d'erreurs de syntaxe

2. ✅ **Logging Activé**
   - `wp-config.php` configuré
   - Fichier `debug.log` créé
   - Logs visibles après sauvegarde

3. ✅ **Fonctionnel**
   - Test de sauvegarde simple réussi
   - Validation fonctionne
   - Logs complets affichés

4. ✅ **Sécurisé**
   - Permissions vérifiées
   - Nonce validé
   - JSON validé

5. ✅ **Production Ready**
   - Aucune erreur critique
   - Performance acceptable
   - Documentation complète

---

**À Compléter Par :** [Responsable Support]  
**Date :** [À remplir]  
**Heure d'achèvement :** [À remplir]
