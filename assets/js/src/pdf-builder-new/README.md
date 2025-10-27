# PDF Builder New

Nouvelle architecture modulaire et stable pour l'édition PDF, remplaçant l'ancienne implémentation React.

## 🚀 Fonctionnalités

- **Architecture modulaire** : Séparation claire des responsabilités
- **Performance optimisée** : Rendu sélectif et gestion efficace des événements
- **API extensible** : Système de plugins pour ajouter des fonctionnalités
- **Validation robuste** : Vérification des données et gestion d'erreurs
- **Gestion des templates** : Sauvegarde, chargement et export/import
- **Support multi-unités** : Conversion précise mm/cm/in/px

## 📁 Structure

```
src/pdf-builder-new/
├── core/                 # Noyau du système
│   ├── PDFBuilder.js     # Classe principale
│   ├── CanvasEngine.js   # Moteur de rendu Canvas
│   ├── ElementManager.js # Gestion des éléments
│   └── TemplateManager.js# Gestion des templates
├── ui/                   # Interface utilisateur
│   ├── UIManager.js      # Gestionnaire d'interface
│   ├── Toolbar.js        # Barre d'outils
│   ├── PropertyPanel.js  # Panneau de propriétés
│   └── CanvasContainer.js# Conteneur du canvas
├── utils/                # Utilitaires
│   ├── UnitConverter.js  # Convertisseur d'unités
│   ├── EventEmitter.js   # Système d'événements
│   └── Validation.js     # Utilitaires de validation
├── plugins/              # Extensions modulaires
│   ├── WooCommerce.js    # Intégration WooCommerce
│   └── ExportPDF.js      # Export PDF
└── index.js              # Point d'entrée principal
```

## 🛠️ Utilisation de base

```javascript
import { createPDFBuilder } from './pdf-builder-new/index.js';

// Création d'un builder
const builder = await createPDFBuilder('canvas-container', {
    width: 800,
    height: 600,
    showGrid: true,
    zoom: 1
});

// Ajout d'éléments
const textId = builder.addElement('text', {
    x: 100,
    y: 100,
    text: 'Hello World',
    fontSize: 24,
    color: '#000000'
});

const rectId = builder.addElement('rectangle', {
    x: 200,
    y: 200,
    width: 150,
    height: 100,
    fillColor: '#cccccc'
});

// Gestion des événements
builder.on('elementadded', (data) => {
    console.log('Élément ajouté:', data.element);
});

builder.on('selectionchange', (data) => {
    console.log('Sélection changée:', data.selectedElements);
});

// Rendu
builder.render();
```

## 🎯 API Principale

### PDFBuilder

Classe principale gérant l'initialisation et la coordination.

```javascript
const builder = new PDFBuilder(containerId, options);

// Méthodes principales
await builder.init();
builder.addElement(type, properties);
builder.selectElement(elementId);
builder.deleteSelectedElements();
builder.setZoom(zoomLevel);
builder.render();

// Événements
builder.on('elementadded', callback);
builder.on('selectionchange', callback);
builder.on('render', callback);
```

### ElementManager

Gestion CRUD des éléments du canvas.

```javascript
// Ajout
const elementId = builder.elementManager.addElement('rectangle', {
    x: 100, y: 100, width: 200, height: 150
});

// Modification
builder.elementManager.updateElement(elementId, { fillColor: '#ff0000' });

// Recherche
const elements = builder.elementManager.getAllElements();
const element = builder.elementManager.getElement(elementId);
```

### TemplateManager

Gestion des templates avec sauvegarde locale.

```javascript
// Création
const templateId = builder.templateManager.createTemplate('Mon Template', 'Description');

// Sauvegarde
builder.templateManager.saveTemplate();

// Chargement
builder.templateManager.loadTemplate(templateId);

// Export/Import
builder.templateManager.exportTemplate(templateId);
await builder.templateManager.importTemplate(file);
```

### CanvasEngine

Moteur de rendu optimisé avec rendu sélectif.

```javascript
// Rendu complet
builder.canvasEngine.clear();
builder.elementManager.getAllElements().forEach(element => {
    builder.canvasEngine.renderElement(element);
});

// Rendu sélectif (optimisé)
builder.canvasEngine.markDirty(x, y, width, height);
builder.canvasEngine.scheduleRender(() => {
    // Callback après rendu
});
```

## 🔧 Utilitaires

### UnitConverter

Conversion précise entre unités.

```javascript
import { unitConverter } from './utils/UnitConverter.js';

// Conversion
const pixels = unitConverter.toPixels(10, 'cm');        // 283.33px
const cm = unitConverter.fromPixels(283.33, 'cm');     // 10cm
const inches = unitConverter.convert(10, 'cm', 'in');  // 3.94in
```

### EventEmitter

Système d'événements personnalisé.

```javascript
import { eventEmitter } from './utils/EventEmitter.js';

// Écouteurs
eventEmitter.on('custom-event', (data) => {
    console.log('Event received:', data);
});

eventEmitter.once('one-time-event', callback);

// Émission
eventEmitter.emit('custom-event', { message: 'Hello' });

// Wildcards
eventEmitter.onAny((event, data) => {
    console.log('Any event:', event, data);
});
```

### Validation

Utilitaires de validation des données.

```javascript
import { validation } from './utils/Validation.js';

// Validation simple
const isValid = validation.validate('email', 'user@example.com');

// Validation d'objet
const result = validation.validateObject(data, {
    name: ['required', ['minLength', 2]],
    email: ['required', 'email'],
    age: [['range', 18, 120]]
});

// Validation d'élément
const elementValidation = validation.validateElement(pdfElement);
```

## 🔌 Système de Plugins

Architecture extensible pour ajouter des fonctionnalités.

```javascript
// Création d'un plugin
class CustomPlugin {
    constructor(pdfBuilder) {
        this.builder = pdfBuilder;
    }

    init() {
        // Initialisation du plugin
        this.builder.on('elementadded', this.onElementAdded.bind(this));
    }

    onElementAdded(data) {
        // Logique personnalisée
    }
}

// Utilisation
builder.plugins.register('custom', new CustomPlugin(builder));
```

## 📊 Migration depuis l'ancienne version

### Changements majeurs

1. **Architecture** : Passage de React à Vanilla JS modulaire
2. **API** : Nouvelle API plus simple et cohérente
3. **Performance** : Optimisations natives (rendu sélectif, RAF)
4. **Extensibilité** : Système de plugins pour les fonctionnalités

### Guide de migration

```javascript
// Ancienne version (React)
const canvas = new PDFCanvasVanilla(containerId, options);
canvas.loadTemplateData(templateData);

// Nouvelle version
const builder = await createPDFBuilder(containerId, options);
builder.templateManager.loadTemplate(templateId);
```

## 🧪 Tests et Qualité

- **Tests unitaires** : Chaque module est testé indépendamment
- **Validation** : Données validées à chaque opération
- **Performance** : Monitoring des métriques de rendu
- **Stabilité** : Gestion d'erreurs robuste

## 📈 Roadmap

- [ ] Interface utilisateur complète (toolbar, propriétés)
- [ ] Plugins WooCommerce et export PDF
- [ ] Support des calques et groupes
- [ ] Historique d'annulation/rétablissement
- [ ] Export multi-formats (PNG, SVG, PDF)
- [ ] Collaboration temps réel
- [ ] Thèmes et personnalisation

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commiter les changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## 📄 Licence

MIT - Voir le fichier LICENSE pour plus de détails.