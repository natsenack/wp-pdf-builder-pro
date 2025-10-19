# 🔍 Guide Rapide : Comment Consulter les Logs

**Document rapide pour monitorer le système en production**

---

## 🚀 Démarrage Rapide

### 1. Activer les Logs WordPress

**Fichier :** `wp-config.php` (racine WordPress)

```php
// Ajouter ces lignes (avant la ligne "/* That's all, stop editing! */")
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);  // Ne pas montrer les erreurs en front
```

**Fichier de logs créé :** `wp-content/debug.log`

---

## 📊 Consulter les Logs en Temps Réel

### Via FTP / Panel d'Hébergement

1. Connectez-vous à votre hébergement
2. Naviguez vers : `wp-content/debug.log`
3. Téléchargez ou visualisez le fichier

### Via Terminal SSH

```bash
# Voir les logs en temps réel
tail -f wp-content/debug.log

# Voir uniquement les logs PDF Builder
tail -f wp-content/debug.log | grep "PDF Builder"

# Voir uniquement les erreurs (❌)
tail -f wp-content/debug.log | grep "PDF Builder.*❌"

# Voir uniquement les succès (✅)
tail -f wp-content/debug.log | grep "PDF Builder.*✅"
```

---

## 🔎 Filtrer les Logs

### Rechercher des Erreurs de Sauvegarde

```bash
grep "Template Save.*❌" wp-content/debug.log
```

**Résultat exemple :**
```
[PDF Builder] Template Save - ❌ ERREUR: Permissions insuffisantes pour user ID 0
[PDF Builder] Template Save - ❌ JSON invalide: Syntax error
```

### Rechercher des Sauvegardes Réussies

```bash
grep "Template Save.*SUCCÈS" wp-content/debug.log
```

**Résultat exemple :**
```
[PDF Builder] Template Save - ✅ SUCCÈS: Template ID=123 sauvegardé avec 25 éléments
[PDF Builder] Template Save - ✅ SUCCÈS: Template ID=124 sauvegardé avec 15 éléments
```

### Rechercher des Sauvegardes de Templates Spécifiques

```bash
grep "Template Save.*ID=123" wp-content/debug.log
```

### Compter les Sauvegardes Réussies

```bash
grep "Template Save.*SUCCÈS" wp-content/debug.log | wc -l
```

---

## 📈 Analyser les Performances

### Compter les Erreurs

```bash
grep "Template Save.*❌" wp-content/debug.log | wc -l
```

### Erreurs les Plus Fréquentes

```bash
grep "Template Save.*❌" wp-content/debug.log | sed 's/.*❌ //' | sort | uniq -c | sort -rn
```

**Résultat exemple :**
```
  5 ERREUR: Permissions insuffisantes pour user ID 0
  2 JSON invalide: Syntax error
  1 Nonce invalide reçu
```

---

## 📝 Interpréter les Logs

### Structure d'un Log Complet

```
[PDF Builder] Template Save - ✅ Permissions vérifiées pour user ID 1
                    │                 │                    │
                 Préfixe           Emoji (statut)      Détail spécifique
```

### Emojis Utilisés

| Emoji | Signification | Action |
|-------|---------------|--------|
| ✅ | Succès / Étape validée | Tout va bien |
| ❌ | Erreur / Échec | Vérifier le problème |
| ⚠️ | Avertissement / Anomalie | Prise de note requise |

### Flux Typique d'une Sauvegarde Réussie

```
✅ Permissions vérifiées
   → ✅ Nonce valide
      → Données reçues (taille)
         → ✅ JSON valide
            → ✅ Structure validée (N éléments)
               → Création/Mise à jour
                  → ✅ Vérification post-sauvegarde
                     → ✅ SUCCÈS
```

### Flux d'une Sauvegarde Échouée

```
✅ Permissions vérifiées
   → ❌ ERREUR: Nonce invalide reçu
      → Réponse d'erreur au client
         → Utilisateur voit l'erreur
```

---

## 🔧 Cas de Débogage Courants

### Cas 1 : Template ne se sauvegarde pas

**Logs à chercher :**
```bash
grep "Template Save" wp-content/debug.log | tail -20
```

**Analyse :**
- Si ❌ Permission → Vérifier les droits utilisateur
- Si ❌ Nonce → Vérifier le nonce WordPress
- Si ❌ JSON → Vérifier les données envoyées
- Si ❌ Structure → Voir les erreurs spécifiques

### Cas 2 : Erreur "Structure invalide"

**Logs à chercher :**
```bash
grep "Template Save.*Structure invalide" wp-content/debug.log -A 5
```

**Analyse :**
Chaque ligne après affiche l'erreur spécifique (prop manquante, type invalide, etc.)

### Cas 3 : Problème de chargement

**Logs à chercher :**
```bash
grep "Template Load.*❌" wp-content/debug.log
```

**Erreurs possibles :**
- Template introuvable
- JSON corrompue
- Structure invalide

### Cas 4 : Performance lente

**Logs à chercher :**
```bash
grep "Template Save.*Données reçues" wp-content/debug.log | tail -10
```

**Analyser :**
Si taille JSON > 1MB, envisager la compression

---

## 📊 Dashboard Rapide

### Script pour Générer un Rapport

```bash
#!/bin/bash

echo "=== RAPPORT PDF BUILDER PRO ==="
echo ""
echo "📊 Statistiques Globales:"
echo "Total sauvegardes réussies: $(grep 'Template Save.*SUCCÈS' /var/www/wp-content/debug.log 2>/dev/null | wc -l)"
echo "Total erreurs: $(grep 'Template Save.*❌' /var/www/wp-content/debug.log 2>/dev/null | wc -l)"
echo "Total chargements: $(grep 'Template Load.*SUCCÈS' /var/www/wp-content/debug.log 2>/dev/null | wc -l)"
echo ""
echo "❌ Erreurs récentes:"
grep 'Template Save.*❌' /var/www/wp-content/debug.log 2>/dev/null | tail -5
echo ""
echo "✅ Dernières sauvegardes réussies:"
grep 'Template Save.*SUCCÈS' /var/www/wp-content/debug.log 2>/dev/null | tail -5
```

---

## 🛠️ Maintenance des Logs

### Logs Qui Grossissent Trop ?

```bash
# Voir la taille du fichier
ls -lh wp-content/debug.log

# Archiver les anciens logs
mv wp-content/debug.log wp-content/debug.log.backup

# Garder uniquement les logs des 7 derniers jours
find wp-content -name "debug.log*" -mtime +7 -delete
```

---

## 🔐 Sécurité des Logs

### ⚠️ Important

Les logs contiennent :
- ✅ Safe : User IDs, Template IDs, Nombres d'éléments
- ⚠️ Attention : Données JSON (peut contenir du contenu sensible)
- ❌ Ne jamais partager : Les logs contiennent peut-être des données sensibles

### Masquer les Données Sensibles

```bash
# Remplacer les détails sensibles avant de partager
sed 's/template_data=.*/template_data=[REDACTED]/g' wp-content/debug.log
```

---

## 📞 Support - Signaler une Erreur

Quand vous signalez une erreur, incluez :

1. **La ligne de log complète :**
```
[PDF Builder] Template Save - ❌ ERREUR: ...
```

2. **Les logs avant (2-3 lignes) :**
```
[PDF Builder] Template Save - ✅ Permissions vérifiées pour user ID 1
[PDF Builder] Template Save - ✅ Nonce valide
[PDF Builder] Template Save - ❌ ERREUR: ...
```

3. **Les logs après (1-2 lignes) :**
```
[PDF Builder] Template Save - ❌ ERREUR: ...
[WordPress] Call to undefined function...
```

---

## ✅ Checklist de Monitoring

### Quotidien

- [ ] Vérifier qu'aucune erreur ❌ n'apparaît
- [ ] Confirmer les ✅ SUCCÈS pour chaque sauvegarde

### Hebdomadaire

- [ ] Analyser les erreurs récurrentes
- [ ] Vérifier la taille du fichier debug.log
- [ ] Compter le nombre de templates sauvegardés

### Mensuel

- [ ] Archiver les anciens logs
- [ ] Analyser les tendances de performance
- [ ] Mettre à jour la documentation

---

**Guide créé :** 19 octobre 2025  
**Version :** 1.0  
**Utilité :** Débogage et monitoring en production
