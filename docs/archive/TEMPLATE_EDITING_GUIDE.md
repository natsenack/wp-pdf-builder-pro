# Fonctionnalité d'édition de templates existants

## Vue d'ensemble

L'éditeur PDF Builder Pro détecte automatiquement quand un template existant doit être chargé et affiche l'interface appropriée.

## Détection automatique

### Paramètre URL
L'application détecte automatiquement le paramètre `template_id` dans l'URL :
```
https://threeaxe.fr/wp-admin/admin.php?page=pdf-builder-react-editor&template_id=1
```

### Comportement
- **Sans `template_id`** : Mode création de nouveau template
  - Bouton : "Enregistrer"
  - Badge : "Nouveau"

- **Avec `template_id`** : Mode édition de template existant
  - Bouton : "Modifier"
  - Badge : "Nouveau" (sera remplacé par le nom du template chargé)
  - Chargement automatique du template

## Interface utilisateur

### Bouton d'action principal
- **Nouveau template** : Bouton "Enregistrer" avec icône 💾
- **Template existant** : Bouton "Modifier" avec icône 💾

### États du bouton
- **Activé** : Quand des modifications ont été apportées (`isModified: true`)
- **Désactivé** : Quand aucune modification n'a été faite (`isModified: false`)

### Tooltips
- **Nouveau template** : "Enregistrer les modifications" / "Aucune modification"
- **Template existant** : "Modifier le modèle" / "Aucune modification"

## Logique de chargement

### Hook `useTemplate`
Le hook détecte automatiquement le paramètre URL au montage du composant :

```typescript
// Détection du template_id
const getTemplateIdFromUrl = (): string | null => {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get('template_id');
};

// Chargement automatique
useEffect(() => {
  const templateId = getTemplateIdFromUrl();
  if (templateId) {
    loadExistingTemplate(templateId);
  }
}, []);
```

### État du template
- `isNewTemplate` : `false` quand un template est chargé
- `templateName` : Nom du template chargé
- `isEditingExistingTemplate` : `true` quand on édite un template existant

## Sauvegarde

### Nouveau template
- Crée un nouveau template en base de données
- Génère un nouvel ID

### Template existant
- Met à jour le template existant avec l'ID fourni
- Préserve l'historique et les métadonnées

## Tests

Les tests valident :
- ✅ Détection correcte du paramètre `template_id`
- ✅ Affichage du bouton "Enregistrer" pour nouveaux templates
- ✅ Affichage du bouton "Modifier" pour templates existants
- ✅ Chargement automatique des templates existants
- ✅ États corrects du bouton (activé/désactivé)

## Architecture

### Composants modifiés
- `useTemplate.ts` : Logique de détection et chargement
- `PDFBuilder.tsx` : Passage de la prop `isEditingExistingTemplate`
- `Header.tsx` : Affichage conditionnel du texte du bouton

### État global
- Extension du `BuilderState` pour supporter les templates existants
- Actions `LOAD_TEMPLATE` pour charger des templates depuis la DB

## Utilisation en production

1. **Créer un lien** vers l'éditeur avec `template_id` :
   ```php
   $edit_url = admin_url('admin.php?page=pdf-builder-react-editor&template_id=' . $template_id);
   ```

2. **L'éditeur** détecte automatiquement le mode et charge le template

3. **L'utilisateur** voit "Modifier" au lieu d'"Enregistrer"

4. **La sauvegarde** met à jour le template existant plutôt que d'en créer un nouveau

## Évolutions futures

- Support des templates partagés/readonly
- Historique des versions de templates
- Prévisualisation des modifications avant sauvegarde
- Validation des permissions d'édition