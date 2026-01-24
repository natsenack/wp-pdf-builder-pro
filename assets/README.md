# Structure TypeScript pour PDF Builder Pro

Ce dossier contient la structure préparée pour une migration progressive vers TypeScript.

## 📁 Structure des dossiers

```
assets/
├── js/                    # JavaScript existant (à migrer)
├── ts/                    # Nouveau code TypeScript
│   ├── components/        # Composants React (.tsx)
│   ├── types/            # Types TypeScript locaux (.d.ts)
│   ├── utils/            # Utilitaires TypeScript (.ts)
│   ├── hooks/            # Hooks React personnalisés (.ts)
│   └── lib/              # Bibliothèques internes (.ts)
├── shared/               # Code partagé entre JS/TS
│   ├── types/           # Types globaux (.d.ts)
│   │   ├── wordpress.d.ts    # Types WordPress
│   │   └── pdf-builder.d.ts  # Types PDF Builder
│   └── interfaces/      # Interfaces communes (.d.ts)
│       └── components.d.ts   # Interfaces composants
└── config/               # Configuration TypeScript
    └── tsconfig.assets.json # Config spécifique aux assets
```

## 🚀 Comment utiliser cette structure

### 1. Configuration TypeScript

La configuration `config/tsconfig.assets.json` étend la configuration principale du projet avec :
- Support ES2018 et JSX React
- Paths pour les imports simplifiés (`@/components/*`, `@/shared/*`)
- Compilation en mode `noEmit` (pour Webpack)

### 2. Types partagés

Importez les types depuis `@/shared` :

```typescript
import { PDFTemplate, AjaxResponse, BaseComponentProps } from '@/shared';
```

### 3. Création de composants

Exemple dans `ts/components/` :

```tsx
import React from 'react';
import { PDFTemplate } from '@/shared';

interface MyComponentProps {
  template: PDFTemplate;
  onSelect: (template: PDFTemplate) => void;
}

const MyComponent: React.FC<MyComponentProps> = ({ template, onSelect }) => {
  return (
    <div onClick={() => onSelect(template)}>
      {template.name}
    </div>
  );
};
```

### 4. Utilitaires

Exemple dans `ts/utils/` :

```typescript
import { PDFTemplate } from '@/shared';

export class TemplateUtils {
  static validate(template: PDFTemplate): boolean {
    return !!(template.id && template.name);
  }
}
```

### 5. Hooks personnalisés

Exemple dans `ts/hooks/` :

```typescript
import { useState, useEffect } from 'react';
import { PDFTemplate } from '@/shared';

export const useTemplates = () => {
  const [templates, setTemplates] = useState<PDFTemplate[]>([]);

  // Logique du hook...

  return { templates, loadTemplates };
};
```

## 🔄 Migration progressive

### Phase 1 : Configuration
- ✅ Structure créée
- ✅ Types de base définis
- ✅ Configuration TypeScript prête

### Phase 2 : Migration des utilitaires
1. Renommer `js/utils/*.js` → `ts/utils/*.ts`
2. Ajouter les types appropriés
3. Mettre à jour les imports

### Phase 3 : Migration des composants
1. Renommer `js/components/*.js` → `ts/components/*.tsx`
2. Ajouter les interfaces TypeScript
3. Typer les props et l'état

### Phase 4 : Migration des points d'entrée
1. Mettre à jour `js/pdf-builder-react-wrapper.js` → `ts/lib/main.tsx`
2. Configurer Webpack pour les fichiers TS/TSX

## 📋 Checklist de migration

- [ ] Configuration TypeScript opérationnelle
- [ ] Types WordPress définis
- [ ] Types PDF Builder définis
- [ ] Interfaces de composants créées
- [ ] Utilitaires migrés (0/5)
- [ ] Composants migrés (0/10)
- [ ] Tests TypeScript ajoutés
- [ ] Build Webpack configuré
- [ ] Documentation mise à jour

## 🛠️ Commandes utiles

```bash
# Vérifier les types TypeScript
npx tsc --noEmit --project assets/config/tsconfig.assets.json

# Builder avec Webpack (à configurer)
npm run build:assets

# Linter TypeScript
npx eslint assets/ts/ assets/shared/ --ext .ts,.tsx
```

## 📚 Ressources

- [Documentation TypeScript](https://www.typescriptlang.org/docs/)
- [React TypeScript](https://react-typescript-cheatsheet.netlify.app/)
- [TypeScript avec WordPress](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)