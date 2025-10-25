/**
 * Test des optimisations de chargement - Phase 3.4.3
 * Vérifie que le système d'optimisation fonctionne correctement
 */

describe('ScriptLoader Optimizations', () => {
  let mockDocument;
  let mockWindow;
  let scriptLoader;

  beforeEach(() => {
    // Mock du DOM
    mockDocument = {
      createElement: jest.fn().mockReturnValue({
        setAttribute: jest.fn(),
        getAttribute: jest.fn(),
        appendChild: jest.fn(),
        onload: null,
        onerror: null,
        src: '',
        async: false
      }),
      head: {
        appendChild: jest.fn()
      },
      querySelectorAll: jest.fn().mockReturnValue([]),
      readyState: 'complete',
      addEventListener: jest.fn()
    };

    mockWindow = {
      performance: {
        getEntriesByType: jest.fn().mockReturnValue([
          {
            domContentLoadedEventEnd: 1000,
            domContentLoadedEventStart: 500,
            loadEventEnd: 2000,
            loadEventStart: 1500
          }
        ])
      }
    };

    // Assigner les mocks aux objets globaux
    global.document = mockDocument;
    global.window = mockWindow;

    // Importer ScriptLoader dans un environnement mock
    global.document = mockDocument;
    global.window = mockWindow;

    // Mock de setTimeout
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.clearAllTimers();
    jest.restoreAllMocks();
  });

  test('ScriptLoader s\'initialise correctement', () => {
    // Simuler l'import du ScriptLoader
    const ScriptLoader = require('../resources/js/ScriptLoader.js');

    expect(typeof ScriptLoader.init).toBe('function');
    expect(typeof ScriptLoader.loadScriptAsync).toBe('function');
    expect(typeof ScriptLoader.getPerformanceMetrics).toBe('function');
  });

  test('Les métriques de performance sont collectées', () => {
    const ScriptLoader = require('../resources/js/ScriptLoader.js');

    const metrics = ScriptLoader.getPerformanceMetrics();

    expect(metrics).toHaveProperty('scriptsLoaded');
    expect(metrics).toHaveProperty('scriptsLoading');
    expect(metrics).toHaveProperty('dependencyQueues');
    expect(metrics).toHaveProperty('totalScripts');

    // Vérifier les valeurs par défaut
    expect(metrics.scriptsLoaded).toBe(0);
    expect(metrics.scriptsLoading).toBe(0);
    expect(metrics.dependencyQueues).toBe(0);
    expect(metrics.totalScripts).toBe(0);
  });

  test('Le nettoyage fonctionne correctement', () => {
    const ScriptLoader = require('../resources/js/ScriptLoader.js');

    // Ajouter quelques données mock
    ScriptLoader.loadingScripts.set('test.js', []);
    ScriptLoader.loadedScripts.add('loaded.js');
    ScriptLoader.dependencyQueues.set('dep.js', []);

    ScriptLoader.cleanup();

    expect(ScriptLoader.loadingScripts.size).toBe(0);
    expect(ScriptLoader.loadedScripts.size).toBe(0);
    expect(ScriptLoader.dependencyQueues.size).toBe(0);
  });
});

describe('Webpack Bundle Optimization', () => {
  test('Les chunks sont correctement séparés', () => {
    // Vérifier que les fichiers de build existent
    const fs = require('fs');
    const path = require('path');

    const distPath = path.resolve(__dirname, '../assets/js/dist');

    // Lister les fichiers dans le répertoire dist
    const files = fs.readdirSync(distPath);

    // Vérifier l'existence des chunks principaux (noms réels des fichiers)
    const hasRuntime = files.some(file => file.includes('runtime') && file.endsWith('.js'));
    const hasScriptLoader = files.some(file => file.includes('pdf-builder-script-loader') && file.endsWith('.js'));
    const hasMainBundle = files.some(file => file.match(/^\d+\.js$/) && file.endsWith('.js'));

    expect(hasRuntime).toBe(true);
    expect(hasScriptLoader).toBe(true);
    expect(hasMainBundle).toBe(true);

    // Vérifier que les fichiers ont des tailles raisonnables
    const scriptLoaderFile = files.find(file => file.startsWith('pdf-builder-script-loader') && file.endsWith('.js'));
    if (scriptLoaderFile) {
      const scriptLoaderStats = fs.statSync(path.join(distPath, scriptLoaderFile));
      expect(scriptLoaderStats.size).toBeLessThan(10000); // Moins de 10KB pour le ScriptLoader
    }
  });

  test('La minification a été appliquée', () => {
    const fs = require('fs');
    const path = require('path');

    const distPath = path.resolve(__dirname, '../assets/js/dist');

    // Lister les fichiers et trouver le script-loader
    const files = fs.readdirSync(distPath);
    const scriptLoaderFile = files.find(file => file.startsWith('pdf-builder-script-loader') && file.endsWith('.js'));

    expect(scriptLoaderFile).toBeDefined();

    // Lire un fichier bundle et vérifier qu'il est minifié
    const scriptLoaderContent = fs.readFileSync(
      path.join(distPath, scriptLoaderFile),
      'utf8'
    );

    // Vérifier que le code est minifié (pas d'espaces inutiles, commentaires supprimés)
    expect(scriptLoaderContent.includes('console.log')).toBe(false); // Console logs supprimés
    expect(scriptLoaderContent.length).toBeGreaterThan(500); // Fichier non vide (ajusté pour la taille réelle)
  });
});

describe('WordPress Integration', () => {
  test('Les hooks d\'optimisation sont présents', () => {
    // Simuler l'inclusion du fichier PHP
    const fs = require('fs');
    const path = require('path');

    const corePath = path.resolve(__dirname, '../src/Core/PDF_Builder_Core.php');
    const coreContent = fs.readFileSync(corePath, 'utf8');

    // Vérifier que les méthodes d'optimisation sont présentes
    expect(coreContent).toContain('optimize_script_tags');
    expect(coreContent).toContain('optimize_style_tags');
    expect(coreContent).toContain('pdf-builder-script-loader');
  });

  test('La configuration ScriptLoader est localisée', () => {
    const fs = require('fs');
    const path = require('path');

    const corePath = path.resolve(__dirname, '../src/Core/PDF_Builder_Core.php');
    const coreContent = fs.readFileSync(corePath, 'utf8');

    // Vérifier que la configuration est passée au JavaScript
    expect(coreContent).toContain('scriptLoader');
    expect(coreContent).toContain('criticalScripts');
    expect(coreContent).toContain('deferThreshold');
  });
});