# Système d'Aperçu Unifié - Phase 8.1
## Refonte Performante et Sobre

### 🎯 Objectif
Refonte complète du système d'aperçu modal pour atteindre :
- **Performance** : < 1.5s chargement, < 50MB mémoire
- **Puissance** : API extensible, renderers spécialisés
- **Sobriété** : Fonctionnalités essentielles uniquement

### 🏗️ Architecture

#### Pattern Provider + Hooks
```jsx
import { PreviewProvider, usePreview } from './preview-system';

function App() {
  return (
    <PreviewProvider>
      <YourComponent />
    </PreviewProvider>
  );
}

function YourComponent() {
  const { openPreview, closePreview, isOpen } = usePreview();
  // Utilisation du contexte global
}
```

#### Structure Modulaire
```
preview-system/
├── context/          # État global optimisé
├── components/       # Composants UI
├── modes/           # Logique par mode (Canvas/Metabox)
├── renderers/       # Renderers spécialisés
├── hooks/           # Hooks personnalisés
└── utils/           # Utilitaires sécurité/performance
```

### 🚀 Utilisation Rapide

#### 1. Import et Initialisation
```jsx
import { PreviewModal, initializePreviewSystem } from './preview-system';

// Initialisation (optionnel)
const system = initializePreviewSystem({
  enablePerformanceMonitoring: true,
  enableLazyLoading: true
});
```

#### 2. Utilisation dans un Composant
```jsx
import { usePreview } from './preview-system';

function MyComponent() {
  const { openPreview, closePreview, isOpen } = usePreview();

  const handleOpenPreview = () => {
    openPreview('canvas', {
      elements: myElements,
      templateData: myData
    });
  };

  return (
    <div>
      <button onClick={handleOpenPreview}>
        Ouvrir Aperçu
      </button>
      <PreviewModal />
    </div>
  );
}
```

### ⚡ Optimisations Performance

#### Lazy Loading
```jsx
import { useLazyLoad } from './preview-system';

function LazyComponent() {
  const { elementRef, hasTriggered } = useLazyLoad();

  return (
    <div ref={elementRef}>
      {hasTriggered && <HeavyComponent />}
    </div>
  );
}
```

#### Monitoring Performance
```jsx
import { usePerformanceMonitor } from './preview-system';

function MyComponent() {
  const { measureOperation } = usePerformanceMonitor('MyComponent');

  const handleClick = () => {
    measureOperation('Heavy Operation', () => {
      // Opération lourde
      return expensiveCalculation();
    });
  };

  return <button onClick={handleClick}>Click</button>;
}
```

### 🔒 Sécurité

#### Validation Automatique
```jsx
import { validatePreviewElements, sanitizeString } from './preview-system';

// Validation des éléments
const safeElements = validatePreviewElements(userElements);

// Sanitisation des chaînes
const safeText = sanitizeString(userInput);
```

### 📊 Métriques Cibles

| Métrique | Cible | Actuel | Status |
|----------|-------|--------|--------|
| Temps chargement | < 1.5s | ~2.5s | 🔄 En cours |
| Taille bundle | < 200KB | 785KB | 🔄 En cours |
| Mémoire peak | < 50MB | ~80MB | 🔄 En cours |
| Tests coverage | > 90% | 0% | 📋 À faire |

### 🧪 Tests Requis

#### Tests Unitaires
- ✅ Context & Provider
- ✅ Hooks personnalisés
- ✅ Utilitaires sécurité
- 📋 Composants UI
- 📋 Renderers

#### Tests d'Intégration
- 📋 Flux complet d'aperçu
- 📋 Interactions utilisateur
- 📋 Changements de mode

#### Tests Performance
- 📋 Métriques temps réel
- 📋 Utilisation mémoire
- 📋 Impact bundle size

### 🚀 Déploiement

#### Build Optimisé
```bash
npm run build  # Bundle splitting automatique
```

#### Configuration Production
```javascript
// webpack.config.js
{
  optimization: {
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        preview: {
          test: /preview-system/,
          name: 'preview-system',
          chunks: 'all'
        }
      }
    }
  }
}
```

### 📈 Roadmap Phase 8.1

#### Semaine 1 : Architecture & Core
- [x] Context + Provider
- [x] Hooks personnalisés
- [x] Utilitaires sécurité
- [ ] Composants UI de base
- [ ] Intégration existante

#### Semaine 2 : Renderers & Optimisations
- [ ] Renderers spécialisés
- [ ] Lazy loading avancé
- [ ] Bundle splitting
- [ ] Tests unitaires

#### Semaine 3 : Tests & Validation
- [ ] Tests d'intégration
- [ ] Tests performance
- [ ] Optimisations finales
- [ ] Documentation

#### Semaine 4 : Déploiement & Monitoring
- [ ] Build production
- [ ] Migration smooth
- [ ] Monitoring post-déploiement
- [ ] Documentation développeur

### 🔧 API Reference

#### usePreview Hook
```typescript
interface PreviewState {
  isOpen: boolean;
  mode: 'canvas' | 'metabox';
  currentPage: number;
  totalPages: number;
  zoom: number;
  loading: boolean;
  error: string | null;
  data: any;
}

interface PreviewActions {
  openPreview(mode: string, data: any, config?: object): void;
  closePreview(): void;
  setPage(page: number): void;
  setZoom(zoom: number): void;
  setLoading(loading: boolean): void;
  setError(error: string | null): void;
  setData(data: any): void;
}

const { state, actions } = usePreview(): PreviewState & PreviewActions;
```

### 📞 Support & Maintenance

- **Issues** : GitHub Issues avec label `preview-system`
- **Performance** : Monitoring automatique via hooks
- **Sécurité** : Audits automatisés intégrés CI/CD
- **Documentation** : Mise à jour automatique via JSDoc

---

**Version** : 8.1.0-alpha
**Status** : Développement actif
**Échéance** : 4 semaines