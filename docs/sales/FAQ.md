# FAQ — PDF Builder Pro V2

## ❓ Questions générales

### Q : Dois-je avoir des connaissances en code ?
**A :** Non ! PDF Builder Pro est conçu pour les non-développeurs. Utilisez l'éditeur drag & drop pour créer des templates sans une seule ligne de code.

Cependant, si vous êtes développeur, vous pouvez accéder à l'éditeur HTML avancé et l'API REST pour les automatisations complexes.

---

### Q : Fonctionne-t-il sur tous les sites WordPress ?
**A :** Oui, PDF Builder Pro fonctionne sur tous les sites WordPress (5.0+), indépendamment du thème ou des autres plugins.

Exceptions :
- **WooCommerce** : seulement avec WooCommerce 5.0+
- **Performance** : sur très petits serveurs (<256MB RAM), vous risquez des timeouts

---

### Q : Est-ce compatible avec les éditeurs visuels (Elementor, Divi) ?
**A :** Oui ! Vous pouvez intégrer PDF Builder Pro dans vos pages construites avec Elementor ou Divi via shortcode ou bloc Gutenberg.

```
[pdf-builder template-id="123"]
```

---

### Q : Puis-je utiliser PDF Builder Pro sur plusieurs sites ?
**A :** Oui, mais chaque site nécessite sa propre licence.
- Version Gratuite : 1 site illimité
- Version Premium : 1 licence = 1 site (multi-licences disponibles)
- Multisite WordPress : chaque installation WordPress = 1 licence

---

### Q : Quels types de documents puis-je générer ?
**A :** Tous types : factures, devis, bons de commande, certificats, contrats, rapports, étiquettes, tickets... L'éditeur est **100% flexible**.

---

## ⚙️ Installation & configuration

### Q : Combien de temps prend l'installation ?
**A :** 5 minutes pour la base. Installation complète (configuration WooCommerce, premiers templates) : 30 minutes.

---

### Q : Ai-je besoin d'un serveur spécial pour générer les PDF ?
**A :** Non. PDF Builder Pro utilise DomPDF natif (PHP pur), sans serveur externe requis.

**Optionnel** : Puppeteer/Chromium pour designs complexes (JavaScript rendering), installable localement.

---

### Q : Comment ajouter mon logo ?
**A :** 
1. **Paramètres > Général** : upload logo entreprise (affect tous templates)
2. **Éditeur template** : insérer un logo spécifique en drag & drop

---

### Q : Puis-je personnaliser les templates existants ?
**A :** Oui ! Dupliquer un template fourni et modifier selon vos besoins (couleurs, polices, layout).

---

### Q : Comment formatter les prix en devise différente ?
**A :** L'éditeur détecte automatiquement le produit/commande devise. Vous pouvez :
1. Forcer devise manuelle dans template
2. Convertir en temps réel (taux live)
3. Afficher symbole ou code ($ / EUR)

---

## 🎨 Éditor & templates

### Q : Puis-je créer des templates depuis zéro ?
**A :** Oui ! Créer → design entièrement libre (drag & drop) → ajouter champs dynamiques → sauvegarder.

**Ou** : partir d'un template existant, dupliquer, modifier.

---

### Q : Comment ajouter des variables dynamiques (numéro commande, client, etc.) ?
**A :** Dans l'éditeur, panneau droit "Variables" affiche tous les champs disponibles :
- **Client** : nom, email, adresse
- **Commande** : numéro, date, total, devise
- **Produits** : titre, SKU, quantité, prix
- **Custom** : champs ACF, post meta

Glissez-déposer le champ dans le template.

---

### Q : Puis-je utiliser des formules (sommes, pourcentages) ?
**A :** Oui ! Champs spéciaux "Calcul" :
- `[SUBTOTAL] + [TAX]` → total TTC
- `[TOTAL] * 0.9` → avec 10% remise
- `[PRICE] * [QTY]` → ligne total

---

### Q : Puis-je ajouter du HTML custom ?
**A :** Oui (version Premium) : éditeur HTML avancé pour créer des sections complexes.

Version Gratuite : utiliser des éléments pré-construits.

---

### Q : Comment créer des tableaux dynamiques (listes produits) ?
**A :** Éditeur → insérer "Tableau dynamique" → configurer colonnes (titre, prix, qty) → ajouter lignes depuis variables produit.

---

### Q : Puis-je importer des logos/images de ma médiathèque ?
**A :** Oui ! Éditeur → insérer image → choisir depuis médiathèque WordPress.

---

## 🔗 WooCommerce & e-commerce

### Q : Puis-je générer automatiquement les factures depuis WooCommerce ?
**A :** Oui ! Paramètres WooCommerce → statuts de génération automatique (paiement reçu, expédié, etc.).

Chaque changement de statut génère le PDF automatiquement.

---

### Q : Puis-je envoyer la facture au client automatiquement ?
**A :** Oui ! Paramètres WooCommerce → ✅ "Envoyer email au client" → configure l'email qui sera inclus.

---

### Q : Puis-je générer plusieurs documents depuis une commande (facture + bon livraison) ?
**A :** Oui ! Configurer différents templates pour différents statuts.

Ex : 
- Statut "Payé" → template Facture
- Statut "Préparation" → template Bon de commande
- Statut "Expédié" → template Bon de livraison

---

### Q : Puis-je générer des factures pour plusieurs commandes en masse ?
**A :** Oui ! **WooCommerce > Commandes > Action en masse > "Générer PDF en masse"** (Premium).

---

### Q : Mon stock WooCommerce change-t-il après génération PDF ?
**A :** Non. PDF Builder Pro ne modifie pas le stock. C'est à vous de gérer le stock manuellement ou via plugin de sync.

---

### Q : Puis-je créer des factures proforma (prévisionnels) ?
**A :** Oui ! Créer un template "Facture proforma" avec badge "Facture prévisionnelle". Générez avant paiement de la commande.

---

## 📊 Rapports & analytics

### Q : Puis-je voir combien de PDF j'ai généré ?
**A :** Oui ! Dashboard PDF Builder → **Statistiques** :
- Nombre PDF ce mois
- Templates les plus utilisés
- Poids total généré
- Performance moyenne

---

### Q : Puis-je exporter les rapports ?
**A :** Oui ! Bouton "Exporter" dans Statistiques :
- **CSV** : import Excel/Sheets
- **JSON** : pour outils BI
- **PDF** : rapport mis en forme

---

### Q : Puis-je voir qui a créé/modifié les templates ?
**A :** Oui ! **Sécurité > Audit log** affiche :
- Qui
- Quand
- Quoi (créé, modifié, supprimé)
- Changements détaillés

Conservé 90 jours par défaut.

---

## 🔒 Sécurité & RGPD

### Q : PDF Builder Pro est-il conforme RGPD ?
**A :** Oui ! Nous proposons :
- **Audit log complet** : traçabilité 100%
- **Droit d'accès** : export données en JSON/CSV
- **Droit à l'oubli** : anonymisez données avec 1 clic
- **Consentements** : opt-in/out pour cookies/traçabilité
- **Chiffrement** : AES-256 données sensibles

---

### Q : Mes données client sont-elles sécurisées ?
**A :** Oui !
- **Chiffrement** : AES-256 au repos
- **TLS/SSL** : en transit
- **Pas d'envoi serveurs externes** : tout reste sur votre serveur
- **Backups** : automatiques et chiffrées

---

### Q : Comment puis-je me conformer à RGPD pour la facturation ?
**A :** Paramètres RGPD :
1. ✅ Activer audit log
2. ✅ Configurer consentements
3. ✅ Définir durée conservation (ex : 10 ans pour factures légales)
4. ✅ Mettre à jour CGV/politique privacy

---

### Q : Puis-je anonymiser les données client ?
**A :** Oui ! **Sécurité > Droit à l'oubli** :
- Sélectionner client
- Cliquer "Anonymiser"
- Toutes les données confidentielles sont supprimées

---

## ⚡ Performance & cache

### Q : How fast are PDFs generated?
**A :** Dépend du complexité :
- **Simple** (texte + chiffres) : 0,5–1s
- **Moyen** (images, tableaux) : 1–2s
- **Complexe** (beaucoup images, styles) : 2–5s

**Avec cache activé** : instant (après 1ère génération, 1h retention)

---

### Q : Comment activer le cache ?
**A :** **Paramètres > Système > Cache** → ✅ Activé
- TTL par défaut : 3600 secondes (1h)
- Économie : 40–60% temps génération
- Auto-invalidation : quand template/données changent

---

### Q : Le cache ralentira-t-il mon site ?
**A :** Non ! Le cache l'accélère en réduisant calculs PDF. Impact mémoire : ~5 MB par 100 templates.

---

### Q : Puis-je vider le cache manuellement ?
**A :** Oui ! **Paramètres > Système > Bouton "Vider cache"** → 1 clic.

---

## 🚀 API & intégrations

### Q : Puis-je générer des PDF via API ?
**A :** Oui ! Endpoint :
```
POST /wp-json/api/v1/generate
{
  "template_id": 123,
  "customer_id": 456,
  "order_id": 789
}
```

Retourne l'URL du PDF généré.

---

### Q : Puis-je intégrer PDF Builder avec mon CRM externe ?
**A :** Oui via webhooks ou API :
1. **Webhooks** : déclenchez action externe après génération
2. **API** : envoyez données depuis CRM, récupérez PDF
3. **Zapier** : connectez sans code

---

### Q : Combien d'appels API puis-je faire ?
**A :** Dépend du plan :
- **Gratuit** : 100 appels/jour
- **Pro** : 1,000 appels/jour
- **Entreprise** : illimité

---

## 💰 Tarification & licences

### Q : Qu'est-ce que je gagne en version Premium vs Gratuite ?
**A :** Voir [PRICING.md](./PRICING.md) pour tableau complet.

**Résumé** :
- (+) 25+ templates pros
- (+) WooCommerce intégration complète
- (+) API REST avancée (1000 appels/jour)
- (+) Webhooks & automation
- (+) Support email prioritaire

---

### Q : Puis-je tester Pro avant d'acheter ?
**A :** Oui ! Période d'essai 14 jours gratuite. Accès complet à toutes les features Pro.

---

### Q : Comment renouveler une licence Pro ?
**A :** Renouvelle automatiquement chaque année (sauf désabonnement). Facturation annuelle ou mensuelle.

Gérez subscription sur votre compte client.

---

### Q : Puis-je annuler la licence ?
**A :** Oui ! Accès à la page de gestion compte → "Annuler l'abonnement". Arrête au prochain cycle de facturation.

---

## 🆘 Problèmes & troubleshooting

### Q : "PDF ne génère pas" — quoi faire ?
**A :** Voir [INSTALLATION.md — Troubleshooting](./INSTALLATION.md#-troubleshooting).

Checklist : PHP ≥7.4, mémoire >256MB, Chromium installé.

---

### Q : "Licence invalide" — solution ?
**A :** Vérifier clé exacte (pas d'espaces), domaine autorisé. Contacter support : support@pdfbuilder.pro

---

### Q : Où trouver les logs d'erreur ?
**A :** **debug.log** dans `/wp-content/` (si WP_DEBUG = true)

Ou **Paramètres > Système > Logs** affiche les erreurs PDF Builder.

---

### Q : Qui contacter pour support ?
**A :** 
- 📧 **Email** : support@pdfbuilder.pro
- 💬 **Forum** : community.pdfbuilder.pro
- 🎥 **Video help** : youtube.com/@pdfbuilderofficial
- 📖 **Docs** : docs.pdfbuilder.pro

---

## 📞 Encore des questions ?

Consultez la **[documentation complète](https://docs.pdfbuilder.pro)** ou **[contactez support](mailto:support@pdfbuilder.pro)**.

Nous sommes disponibles lun-ven 9h-17h CET 💪
