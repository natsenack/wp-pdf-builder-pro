# 📋 Fonctionnalité : Visualiseur JSON Brut du Template

## 🎯 Vue d'ensemble

La fonctionnalité **JSON Viewer** permet aux développeurs et administrateurs de visualiser le JSON brut de chaque template directement depuis l'éditeur.

## 🚀 Accès à la fonctionnalité

1. Ouvrez un template dans l'éditeur
2. Cliquez sur le bouton **"👁️ Aperçu"** dans le header
3. Une modale s'affichera avec :
   - Le JSON formaté et complet du template
   - L'ID du template dans le titre
   - Des options pratiques

## 📊 Contenu affiché

La modale affiche la structure complète du template en JSON :

```json
{
  "id": 123,
  "name": "Facture Professionnelle",
  "description": "Template pour les factures",
  "tags": ["facture", "client"],
  "canvasWidth": 794,
  "canvasHeight": 1123,
  "elements": [
    {
      "id": "elem_001",
      "type": "text",
      "content": "FACTURE",
      "x": 50,
      "y": 30,
      "width": 100,
      "height": 20,
      "style": {
        "fontSize": 24,
        "fontWeight": "bold",
        "color": "#000000"
      }
    },
    // ... autres éléments
  ],
  "settings": {
    // Paramètres du template
  }
}
```

## 🛠️ Fonctionnalités

### 1. Visualisation JSON Formatée
- JSON bien formaté et indenté pour une meilleure lisibilité
- Affichage dans un conteneur scrollable
- Fond gris clair pour distinguer du reste de l'interface

### 2. Copie dans le Presse-papiers
- Bouton **"📋 Copier JSON"**
- Copie le JSON complet dans le presse-papiers
- Confirmation visuelle : "✅ Copié!" pendant 2 secondes
- Utilise l'API `navigator.clipboard`

### 3. Téléchargement du Fichier JSON
- Bouton **"💾 Télécharger"**
- Crée automatiquement un fichier JSON avec :
  - Nom : `template-[ID]-[timestamp].json`
  - Exemple : `template-123-1698774355000.json`
- Télécharge directement sur l'ordinateur de l'utilisateur

### 4. Fermeture
- Bouton **"Fermer"**
- Bouton de fermeture "×" en haut à droite
- Clic sur le fond sombre ferme aussi la modale

## 🎨 Design

- Modale centrée sur l'écran
- Largeur maximale : 90% de la fenêtre
- Hauteur maximale : 85% de la fenêtre
- Fond semi-transparent avec overlay
- Bordures arrondies et ombre douce
- Police monospace (Courier New) pour le JSON
- Boutons avec couleurs cohérentes :
  - Copier : bleu WordPress (#0073aa)
  - Télécharger : vert (#10a37f)
  - Fermer : gris (#f8f8f8)

## 💡 Cas d'usage

### Pour les développeurs
- Déboguer la structure des templates
- Exporter des templates pour analyse
- Valider le JSON généré
- Intégration avec des outils externes (curl, Postman, etc.)

### Pour les administrateurs
- Archiver les configurations de templates
- Migrer des templates entre environnements
- Créer des backups manuels
- Audit et versioning

### Pour la documentation
- Générer des exemples de structure JSON
- Créer des templates de démarrage
- Partager les configurations avec l'équipe

## 📝 Notes techniques

### Fichier modifié
- `assets/js/src/pdf-builder-react/components/header/Header.tsx`

### États React ajoutés
- `showJsonModal` : Contrôle l'affichage de la modale
- `copySuccess` : Indicateur de succès de la copie

### Hooks utilisés
- `useState` : Gestion des états
- `navigator.clipboard.writeText()` : Copie du JSON
- `Blob` + `URL.createObjectURL()` : Téléchargement du fichier

### Intégration avec BuilderContext
- Accès au `state.template` complet
- Affichage du templateName comme ID

## 🔄 Interaction avec l'aperçu existant

Quand vous cliquez sur **"Aperçu"** :
1. ✅ La modale JSON s'ouvre
2. ✅ L'aperçu du preview s'affiche aussi (via `onPreview()`)

Vous avez donc simultanément :
- La modale JSON avec le code brut
- La modale d'aperçu du PDF rendu

Fermez la modale JSON pour voir l'aperçu en plein écran.

## 🔐 Sécurité

- Données stockées uniquement en mémoire (pas d'envoi à distance)
- Téléchargement local uniquement (pas de transmission réseau)
- Compatible avec tous les navigateurs modernes supportant :
  - `Clipboard API`
  - `Blob API`
  - `URL.createObjectURL()`

## 📦 Version

- **Introducet en** : v1.0.0-1eplo25-20251101-211153
- **Statut** : ✅ Produit
- **Compatibility** : React 18+, TypeScript 4.5+
