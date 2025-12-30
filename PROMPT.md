# 🎯 PROMPT SYSTEM - PDF Builder Pro

> **Utilisation** : Copie ce prompt dans un LLM pour continuer le développement du projet avec continuité

---

## 🔄 CONTEXTE DU PROJET

**Projet** : PDF Builder Pro (WordPress Plugin v1.1.0)  
**Date** : 30 décembre 2025  
**Statut** : Phase de nettoyage & refactoring (Phase 0)  
**Stack** : PHP 7.4+, Vanilla JS, WordPress 5.0+, Canvas 2D API  

### 📊 État Actuel
- ✅ Migration React → Vanilla JS réussie (-71% bundle)
- ✅ Canvas 2D fonctionnel, WooCommerce intégré
- ⚠️ Architecture hybride confuse à nettoyer
- ⚠️ Système AJAX fragmenté à unifier
- ⚠️ Bootstrap complexe (1672 lignes) à refactoriser

### 📂 Structure Clé
```
wp-pdf-builder-pro/
├── plugin/src/           ← Backend PHP (namespaced PSR-4)
│   ├── AJAX/             ← Handlers AJAX (À UNIFIER)
│   ├── Admin/            ← Pages admin
│   ├── Canvas/           ← Rendering
│   ├── Core/             ← Noyau
│   └── ... (10+ autres modules)
├── assets/js/            ← Frontend Vanilla JS
│   ├── pdf-canvas-vanilla.js    (principal)
│   ├── settings-*.js            (UI)
│   └── fallbacks/               (React legacy)
├── docs/                 ← Documentation
├── tests/                ← Suite tests
└── plugin/bootstrap.php  ← Entry point (À refactoriser)
```

---

## 🎯 MISSION IMMÉDIATE (Phase 0)

**Objectif** : Nettoyer et stabiliser la base de code avant améliorations

### ✅ Tâches de Phase 0

#### Tâche 1 : Audit des dépendances React
**Fichier clé** : `package.json`
**Problème** : React et dépendances listées mais NOT utilisées en production
**À faire** :
```
1. Analyser tous les imports de 'react' dans le codebase
2. Confirmer que AUCUN code de production ne dépend de React
3. Lister toutes les dépendances React (react, react-dom, @wordpress/element, etc.)
4. Créer liste des dépendances à supprimer du package.json
5. Vérifier que webpack.config.cjs n'utilise pas React en entry
```

**Output attendu** :
```json
{
  "dépendances_à_supprimer": [
    "react@^18.2.0",
    "react-dom@^18.2.0",
    "@wordpress/element@^6.32.0",
    "@babel/preset-react@^7.23.3"
  ],
  "devDependencies_à_vérifier": [
    "@babel/preset-react"
  ],
  "presets_babel": []
}
```

#### Tâche 2 : Audit du code mort
**Fichiers cibles** : Tout le codebase
**Problème** : Code commenté, références mortes, fallbacks inutiles
**À faire** :
```
1. Chercher tous les // FIXME, // TODO, /* DEPRECATED */, // COMMENTE...
2. Chercher les try-catch fallbacks React (pdf_builder_ensure_*, etc.)
3. Documenter chaque bloc problématique
4. Décider : supprimer ou garder ?
```

**Fichiers suspects** :
- `plugin/pdf-builder-pro.php` (ligne ~41 : plugins_loaded commentée)
- `plugin/bootstrap.php` (fallbacks multiples)
- `assets/js/fallbacks/` (réactif legacy)
- `assets/js/pdf-builder-react-wrapper.js` (React wrapper)

**Output attendu** :
```
Code mort trouvé :
├── pdf-builder-pro.php : 3 blocs commentés
├── bootstrap.php : 5 fallback functions
├── assets/js/fallbacks/ : À examiner
└── [liste détaillée]
```

#### Tâche 3 : Nettoyage fichiers temporaires
**À supprimer** :
```
[ ] temp.js (racine)
[ ] test_canvas_options.php (racine)
```

**À vérifier** :
```
[ ] build/backups/ - Nécessaires ?
[ ] Fichiers .bak, .tmp - Autres endroits ?
```

#### Tâche 4 : Documentation des problèmes
**Format** : Créer CLEANUP_ISSUES.md avec structure :
```markdown
## Problème : [Titre]
- Fichier : `chemin/fichier.ext`
- Ligne(s) : X-Y
- Sévérité : [CRITIQUE/MOYEN/FAIBLE]
- Description : ...
- Solution proposée : ...
- Effort : [1h / 2-4h / 4-8h]
```

**Exemple** :
```markdown
## Problème : Registration AJAX commentée
- Fichier : `plugin/pdf-builder-pro.php`
- Ligne : 41
- Sévérité : CRITIQUE
- Description : La ligne `add_action('plugins_loaded', 'pdf_builder_register_ajax_handlers', 5);` 
  est commentée, ce qui signifie les handlers AJAX ne sont pas enregistrés correctement.
- Solution : Uncomment et tester que les handlers se chargent
- Effort : 30min
```

---

## 🏗️ PHASE 1 : UNIFICATION AJAX (À VENIR)

**Contexte** : Système AJAX fragmenté doit être unifié
**Fichiers clés** : 
- `plugin/src/AJAX/Ajax_Handlers.php` (PRINCIPAL)
- `plugin/src/AJAX/cache-handlers.php`
- `plugin/src/AJAX/PDF_Builder_Templates_Ajax.php`
- `docs/AJAX_SYSTEM.md` (architecture documentée)

**Objectif** : 
1. Créer unified dispatcher AJAX
2. Centraliser tous les handlers
3. Standardiser error responses
4. Documenter tous les endpoints

**Endpoints AJAX à documenter** :
```
POST /wp-admin/admin-ajax.php
├── action=pdf_builder_save_all_settings
├── action=pdf_builder_save_template
├── action=pdf_builder_load_template
├── action=pdf_builder_delete_template
├── action=pdf_builder_clear_cache
├── action=pdf_builder_clear_all_cache
├── action=pdf_builder_get_preview_data
├── action=pdf_builder_optimize_database
└── [+ others à identifier]
```

---

## 🏗️ PHASE 2 : REFACTORING BOOTSTRAP (À VENIR)

**Contexte** : bootstrap.php = 1672 lignes, trop complexe
**Fichiers clés** :
- `plugin/bootstrap.php` (À diviser)
- `plugin/pdf-builder-pro.php` (Point d'entrée)

**Objectif** :
1. Diviser en modules : loader.php, hooks.php, utilities.php, etc.
2. Simplifier flux d'initialisation
3. Supprimer fallbacks complexes
4. Ajouter logging clair

---

## 📋 CHECKLIST PHASE 0 EN DÉTAIL

### ✅ Audit & Documentation (1-2 jours)
```
[ ] Tâche 1 : Analyser dépendances React
    [ ] Chercher tous les imports 'react'
    [ ] Analyser webpack config
    [ ] Lister dépendances à supprimer
    
[ ] Tâche 2 : Audit code mort
    [ ] Scanner // FIXME, // TODO, /* ... */
    [ ] Documenter fallbacks React
    [ ] Vérifier test_canvas_options.php
    
[ ] Tâche 3 : Nettoyage files
    [ ] Supprimer temp.js
    [ ] Supprimer test_canvas_options.php
    [ ] Archiver ou conserver ?
    
[ ] Tâche 4 : Créer CLEANUP_ISSUES.md
    [ ] Documenter chaque problème
    [ ] Estimer effort
    [ ] Proposer solutions
```

### 🔧 Nettoyage (2-3 jours)
```
[ ] Tâche 5 : Supprimer dépendances React
    [ ] Mettre à jour package.json
    [ ] Vérifier webpack.config.cjs
    [ ] Mettre à jour babel.config.js
    [ ] Tester build : npm run build
    
[ ] Tâche 6 : Nettoyer code commenté
    [ ] plugin/pdf-builder-pro.php
    [ ] plugin/bootstrap.php
    [ ] assets/js/**/*.js
    
[ ] Tâche 7 : Vérifier sécurité
    [ ] Audit npm audit
    [ ] Mettre à jour dépendances critiques
    [ ] Tester qu'aucune regression

[ ] Tâche 8 : Tests
    [ ] npm test (les tests doivent passer)
    [ ] Vérifier build ne plante pas
    [ ] Tests d'intégration basiques
```

---

## 🔍 POINTS À VÉRIFIER

### ⚠️ Avant de Supprimer React
1. **Vérifier aucun import React** :
   ```bash
   grep -r "import.*from.*react" --include="*.js" assets/js/
   grep -r "require.*react" --include="*.js" assets/js/
   ```

2. **Vérifier webpack config** :
   - Pas d'entry points React
   - Pas de loaders React
   - Pas de plugins React

3. **Vérifier PHP frontend** :
   - `wp_enqueue_script()` n'inclut pas React builds
   - Pas de `<script>` tags React manuels

### ⚠️ Avant de Supprimer Fichiers
1. Vérifier aucune référence à `temp.js` ou `test_canvas_options.php`
2. Vérifier pas d'imports de ces fichiers
3. Considérer si à archiver en lieu de supprimer

### ⚠️ Impact sur Autres Systèmes
- AJAX handlers : Doivent rester fonctionnels
- Canvas rendering : Ne dépend pas de React
- WooCommerce : Ne dépend pas de React
- Settings UI : Vanilla JS uniquement

---

## 📌 NOTES IMPORTANTES

1. **Ne PAS supprimer** :
   - `assets/js/pdf-builder-react/` - à auditer d'abord
   - `plugin/src/` - À nettoyer mais pas supprimer
   - Tests - À compléter, pas supprimer

2. **À documenter** :
   - Pourquoi React était là (contexte historique)
   - Quand décision Vanilla JS a été prise
   - Migrations effectuées

3. **À vérifier après nettoyage** :
   - Bundle size avec `webpack-bundle-analyzer`
   - Tests passent : `npm test`
   - Build fonctionne : `npm run build`
   - Plugin se charge : Tester sur WordPress

---

## 🚀 PROCHAINES PHASES (Après Phase 0)

### Phase 1 : Unification AJAX (2 semaines)
- Créer unified dispatcher
- Centraliser handlers
- Documenter endpoints
- Tests AJAX

### Phase 2 : Refactoring Bootstrap (1-2 semaines)
- Diviser en modules
- Simplifier loading
- Logging clair
- Tests initialization

### Phase 3 : Tests & Sécurité (2 semaines)
- Compléter tests
- Security audit
- CI/CD setup
- Coverage 80%+

---

## 📞 DÉCISIONS À PRENDRE

1. **React Complètement Supprimé ?**
   - OUI → Supprimer toutes dépendances
   - NON → Garder pour fallback ? (risqué)
   - Recommandation : OUI (Phase 1 complete = Vanilla JS = target)

2. **Fallbacks Complexes ?**
   - pdf_builder_ensure_* functions → Supprimer
   - pdf_builder_load_utilities_emergency → Simplifier
   - Recommandation : Simplifier vers loader unique

3. **Code Commenté ?**
   - Garder pour historique ? → Non (utiliser git)
   - Supprimer complètement ? → OUI
   - Recommandation : Supprimer et utiliser git log

4. **Tests Manuels ?**
   - Garder manual-test.php ? → Non (utiliser tests auto)
   - Recommandation : Convertir en tests Jest/PHPUnit

---

## 📚 RESSOURCES

- [ANALYSE_COMPLETE.md](ANALYSE_COMPLETE.md) - Analyse détaillée
- [APERCU_UNIFIED_ROADMAP.md](docs/APERCU_UNIFIED_ROADMAP.md) - Roadmap phases
- [AJAX_SYSTEM.md](docs/AJAX_SYSTEM.md) - Architecture AJAX
- [README.md](README.md) - Structure projet

---

## 💬 TEMPLATE DE RÉPONSE

Quand tu utilises ce prompt, fournis :

```
## Phase 0 - Nettoyage Progress

### ✅ Tâche 1 : Audit Dépendances React
**Status** : [NOT_STARTED / IN_PROGRESS / COMPLETED / BLOCKED]
**Trouvailles** :
- React trouvée à [liste des imports]
- Dépendances à supprimer : [liste]
**Action** : [Next steps]

### ✅ Tâche 2 : Audit Code Mort
**Status** : [NOT_STARTED / IN_PROGRESS / COMPLETED / BLOCKED]
**Trouvailles** :
- Fichiers suspects : [liste]
- Blocs commentés : [compte]
**Action** : [Next steps]

### ✅ Tâche 3 : Nettoyage Files
**Status** : [NOT_STARTED / IN_PROGRESS / COMPLETED / BLOCKED]
**Fichiers à supprimer** :
- temp.js
- test_canvas_options.php
**Action** : [Confirmer avant suppression]

### 📊 Résumé
- Effort total estimé : XXh
- Blockers : [liste]
- Recommandations : [liste]
```

---

**Document généré** : 30 décembre 2025  
**Version** : 1.0  
**Prêt à** : Utiliser avec Claude/ChatGPT pour continuer le développement
