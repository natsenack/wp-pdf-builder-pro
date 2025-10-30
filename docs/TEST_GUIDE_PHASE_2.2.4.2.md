# 🧪 Guide de Test - Phase 2.2.4.2

## 📌 Avant de commencer
- ✅ npm run build déjà exécuté
- ✅ Tous les fichiers modifiés et compilés
- ✅ WordPress + WooCommerce installés

---

## 🧪 Test 1 : Vérifier le bouton "Aperçu" dans la metabox

### Étapes :
1. Allez dans **WordPress Admin → WooCommerce → Commandes**
2. Ouvrez une commande existante (ou créez-en une)
3. Regardez la section **"PDF Builder Pro"** dans le sidebar
4. Vérifiez la présence des boutons :
   - ✅ **"👁️ Aperçu"** (bouton primaire bleu)
   - ✅ **"📄 Générer PDF"** (bouton secondaire gris)

### Résultat attendu :
- Les deux boutons doivent être visibles et actifs
- Le texte doit être lisible
- Les boutons doivent avoir du spacing approprié

---

## 🧪 Test 2 : Cliquer sur "Aperçu"

### Étapes :
1. Cliquez sur **"👁️ Aperçu"**
2. Une fenêtre popup doit s'ouvrir (pop-ups autorisés)
3. Attendez le chargement des données

### Résultat attendu :
- ✅ Une nouvelle fenêtre s'ouvre
- ✅ Un titre "Aperçu de la commande" s'affiche
- ✅ Les données de la commande s'affichent progressivement

---

## 🧪 Test 3 : Vérifier les données affichées

### Vérifications :

#### **En-tête**
- ✅ Numéro de commande correct (ex: Commande #123)
- ✅ Date de la commande (30/10/2025)
- ✅ ID template

#### **Informations client - Facturation**
- ✅ Nom complet (Prénom Nom)
- ✅ Adresse ligne 1 et 2 (si applicable)
- ✅ Code postal + Ville
- ✅ Email
- ✅ Téléphone

#### **Informations client - Livraison**
- ✅ Même données si adresse de livraison = facturation
- ✅ Adresse différente si configurée

#### **Tableau des articles**
- ✅ Nom des produits corrects
- ✅ Quantités correctes
- ✅ Prix unitaires corrects
- ✅ Totaux correctes

#### **Totaux**
- ✅ Sous-total correct
- ✅ Livraison correcte (si applicable)
- ✅ Taxes correctes
- ✅ **TOTAL** correct et en évidence

---

## 🧪 Test 4 : Contrôles Zoom

### Étapes :
1. Dans la popup, testez les boutons zoom :
   - Zoom - (doit réduire de 25%)
   - Zoom + (doit augmenter de 25%)
   - 100% (doit revenir à la taille normale)

2. Vérifiez que le pourcentage affiché change

### Résultat attendu :
- ✅ Le contenu se redimensionne correctement
- ✅ Le pourcentage s'affiche correctement
- ✅ Min = 25%, Max = 200%

---

## 🧪 Test 5 : Bouton Imprimer

### Étapes :
1. Cliquez sur **"🖨️ Imprimer"**
2. La boîte de dialogue d'impression doit s'ouvrir

### Résultat attendu :
- ✅ Boîte de dialogue d'impression s'ouvre
- ✅ Aperçu avant impression visible
- ✅ Format A4 en portrait
- ✅ Données correctement mises en page

---

## 🧪 Test 6 : Bouton Fermer

### Étapes :
1. Cliquez sur **"❌ Fermer"**

### Résultat attendu :
- ✅ Fenêtre popup se ferme
- ✅ Retour à la metabox

---

## 🧪 Test 7 : Gestion des erreurs

### Scénario 1 : Pas de permission
1. Connectez-vous avec un compte sans permissions WooCommerce
2. Ouvrez une commande
3. Cliquez sur "Aperçu"

**Résultat attendu** : Message d'erreur "Permissions insuffisantes"

### Scénario 2 : Nonce invalide
1. Nonce expiré (attendre >12h)
2. Cliquez sur "Aperçu"

**Résultat attendu** : Message d'erreur "Sécurité: Nonce invalide"

### Scénario 3 : Commande invalide
1. Modifiez l'URL pour utiliser un ID commande inexistant
2. Cliquez sur "Aperçu"

**Résultat attendu** : Message d'erreur "Commande introuvable"

---

## 🧪 Test 8 : Variables dynamiques

### Vérification :
Dans le HTML généré, cherchez les variables remplacées :

```html
<!-- Doit être remplacé -->
AVANT: {{customer_name}}
APRÈS: Jean Dupont

AVANT: {{order_number}}
APRÈS: CMD-2025-001

AVANT: {{order_total}}
APRÈS: 299,99 €
```

### Résultat attendu :
- ✅ Toutes les variables `{{...}}` sont remplacées
- ✅ Les valeurs sont correctes
- ✅ Aucun placeholder visible

---

## 🧪 Test 9 : Responsive mobile

### Étapes :
1. Ouvrez le DevTools (F12)
2. Mode responsive (Ctrl+Shift+M)
3. Testez sur différentes résolutions :
   - 320px (téléphone)
   - 768px (tablette)
   - 1024px (desktop)

### Résultat attendu :
- ✅ Popup reste visible et usable
- ✅ Contenu responsive
- ✅ Boutons accessibles au doigt
- ✅ Pas de débordement

---

## 🧪 Test 10 : Performance

### Mesures :
1. Ouvrez DevTools → Onglet Network
2. Cliquez sur "Aperçu"
3. Observez :
   - Nombre de requêtes AJAX (doit être 1)
   - Temps de réponse (< 1s souhaité)
   - Taille de la réponse

### Résultat attendu :
- ✅ 1 seule requête AJAX
- ✅ Temps < 1000ms
- ✅ Taille < 50KB

---

## 📊 Checklist finale

- [ ] Bouton "Aperçu" visible
- [ ] Popup s'ouvre au clic
- [ ] Données client correctes
- [ ] Articles corrects
- [ ] Totaux corrects
- [ ] Zoom fonctionne
- [ ] Imprimer fonctionne
- [ ] Fermer fonctionne
- [ ] Messages d'erreur lisibles
- [ ] Mobile responsive
- [ ] Performance < 1s
- [ ] Pas d'erreurs console

---

## ⚠️ Points de vigilance

- **Pop-ups bloqués** : Autorisez les pop-ups pour ce site
- **Permissions** : Compte utilisateur doit avoir accès WooCommerce
- **JavaScript** : Doit être activé
- **Sécurité** : Nonce doit être valide (< 12h)

---

## 📝 Notes

- Si "Aperçu" ne s'ouvre pas, vérifiez la console du navigateur (F12)
- Les données doivent correspondre exactement à la commande WooCommerce
- Le design HTML popup peut être personnalisé ultérieurement (Phase 2.2.4.3)

---

## ✅ Succès !

Si tous les tests passent, **Phase 2.2.4.2 est opérationnelle** ! 🎉
