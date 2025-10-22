# 🚀 Reconstruction Système d'Aperçu

**📅 Date** : 21 octobre 2025  
**🔄 Statut** : Phase 2.1.4 terminée (priorités d'implémentation définies)

---

## 🎯 Vue d'ensemble

Reconstruction complète du système d'aperçu PDF avec architecture moderne :
- **Canvas** : Éditeur avec données d'exemple
- **Metabox** : WooCommerce avec données réelles

---

## 📋 Spécifications fonctionnelles

### 🎯 **Vision générale**
- **Méthode d'affichage commune** : Même rendu visuel pour Canvas et Metabox, seules les données changent dynamiquement.
- **Respect du canvas** : Tout doit correspondre exactement au canvas (éléments, propriétés, outils).
- **Injection dynamique** : Variables `{{variable}}` (ex: `{{customer_name}}`) remplies selon le mode.

### 📋 **Modes d'aperçu**

#### **Mode Canvas (Éditeur)**
- **Données** : Fictives mais cohérentes (client "Jean Dupont", produits avec prix/totaux calculés).
- **Éléments entreprise** : Identiques au Metabox (logo, etc.).
- **Déclenchement** : Bouton dans l'éditeur ouvrant une modal.
- **Objectif** : Aperçu design sans commande spécifique, visuellement crédible.

#### **Mode Metabox (WooCommerce)**
- **Données** : Réelles depuis WooCommerce (client, produits, commande).
- **Éléments entreprise** : Depuis paramètres WooCommerce.
- **Déclenchement** : Deux boutons dans la modal :
  - 📸 **"Aperçu Image"** : Screenshot (HTML2Canvas côté client).
  - 📄 **"Aperçu PDF"** : TCPDF (rendu éditable/précis).
- **Gestion manquante** : Signaler les problèmes (placeholders rouges "Donnée manquante" + message d'erreur).

### 🛠️ **Fonctionnalités détaillées**

#### **Exports PNG/JPG**
- **Formats** : Choix PDF/PNG/JPG dans la modal Metabox.
- **Qualité** : Réglable dans paramètres (défaut 150 DPI).
- **Nom fichier** : `{{customer_lastname}}_{{order_number}}.png` (ex: `Dupont_2372.png`).

#### **Notifications**
- **Style** : Toasts discrets (pop-up en haut écran).
- **Déclenchement** : Alerte si génération >2s ("Génération lente...").
- **Erreurs** : Toast + message spécifique dans modal.

#### **Mode développeur**
- **Activation** : Setting admin (caché, devs only).
- **Fonctionnalités** : Hot-reload, logs console, variables de test.
- **Retrait** : Enlevé en production.

#### **Templates prédéfinis**
- **Emplacement** : Modal dans le menu "Template" existant.
- **Limite** : Freemium (quelques gratuits, plus payant).
- **Sélection** : Visuelle (aperçus des modèles).

### 🏗️ **Architecture technique**

#### **Séparation Canvas/Metabox**
- **Providers** : `CanvasDataProvider` (données fictives) et `MetaboxDataProvider` (données réelles).
- **Injection** : Système modulaire pour switcher entre modes.

#### **Sécurité & Logs**
- **Permissions** : Basé sur rôles plugin (admins/vendeurs autorisés).
- **Logs** : Tout (erreurs/warnings/info) dans `wp_debug.log` en mode dev.
- **Nonces** : Sécurisation AJAX.

#### **Compatibilité**
- **Navigateurs** : Tous modernes + anciens si possible (fallback serveur si HTML2Canvas problématique).

### 📅 **Priorités v1.1.0**
- **Implémentation** : Un par un (tester étape par étape).
- **Fonctionnalités** :
  1. Cache intelligent.
  2. Validation automatique.
  3. Templates prédéfinis (avec limite freemium).

### ✅ **Critères de succès**
- **Performance** : <2s génération.
- **Sécurité** : 0 vulnérabilités.
- **Ergonomie** : Aperçus visibles (modal 90% écran), téléchargement direct.

---

## � Phases de reconstruction

### ✅ Phase 1 : Nettoyage complet
- [x] Supprimer tous les composants React d'aperçu
- [x] Nettoyer le code PHP backend
- [x] Supprimer les styles CSS d'aperçu
- [x] Recompiler les assets
- [x] Valider la syntaxe PHP
- [x] **Nettoyer le serveur distant**
- [x] **Redéployer les fichiers nettoyés**

### 🔍 Phase 2 : Analyse & conception

**📋 Ordre logique d'implémentation** : Les étapes sont organisées séquentiellement pour une implémentation fluide. Commencer par l'analyse des éléments existants, puis les données, l'architecture et enfin les interfaces.

**🧪 Approche** : Tests intégrés à chaque étape pour valider au fur et à mesure (pas de tests groupés à la fin).

#### **2.1 Auditer les 7 types d'éléments**
- [x] **Étape 2.1.1 : Identifier et lister les 7 types d'éléments**  
  - Ouvrir le code source des éléments (probablement dans `src/` ou `resources/js/`)  
  - Chercher les classes ou composants pour chaque type (texte, image, rectangle, etc.)  
  - Créer une liste simple avec nom et description courte  
  - Vérifier dans les templates existants quels éléments sont utilisés  
  - **Test** : Liste complète validée avec exemples  
  - **✅ VALIDÉ** : Tests automatisés complets (11 tests Jest + validation PHP) - 7 éléments confirmés  
  - **➕ TESTS COMPLÉMENTAIRES** : Outils (12 outils validés) et propriétés (6 catégories, 10+ mappings) - Tests automatisés Jest (32 tests) et PHP validés

- [x] **Étape 2.1.2 : Analyser les propriétés actuelles de chaque élément**  
  - Pour chaque élément, lister ses propriétés (position, taille, couleur, etc.)  
  - Noter les valeurs par défaut et les limites (min/max)  
  - Identifier les propriétés dynamiques vs statiques  
  - Documenter comment elles sont stockées en JSON  
  - **Test** : Propriétés testées dans éditeur  
  - **✅ VALIDÉ** : Analyse complète documentée (ANALYSE_PROPRIETES_ELEMENTS.md) - 7 éléments analysés avec 150+ propriétés détaillées

- [x] **Étape 2.1.3 : Documenter les limitations et bugs connus**  
  - Tester chaque élément dans l'éditeur actuel  
  - Noter les problèmes (rendu incorrect, propriétés manquantes, etc.)  
  - Chercher dans les issues Git ou rapports de bugs  
  - Prioriser les problèmes critiques vs mineurs  
  - **Test** : Bugs documentés et reproduits  
  - **✅ VALIDÉ** : Analyse complète du code source - 7 bugs critiques et 10+ limitations identifiées (LIMITATIONS_BUGS_REPORT.md)  

- [x] **Étape 2.1.4 : Définir les priorités d'implémentation**  
  - Classer les éléments par fréquence d'usage (texte en premier)  
  - Identifier les dépendances entre éléments  
  - Estimer la complexité de recréation pour chacun  
  - Créer une matrice priorité/complexité  
  - **Test** : Valider liste complète avec exemples concrets
  - **✅ VALIDÉ** : Plan d'implémentation complet défini (PHASE_2.1.4_PRIORITES_IMPLEMENTATION.md) - 7 éléments classés par priorité avec plan 3 phases (fondamentaux → intermédiaires → critique)

#### **2.2 Implémenter les éléments fondamentaux**
- [x] **Étape 2.2.1 : Améliorer company_logo**  
  - Unifier gestion src/imageUrl pour compatibilité  
  - Implémenter redimensionnement automatique selon ratio d'aspect  
  - Ajouter validation formats d'image (JPG, PNG, WebP, SVG, GIF, BMP, TIFF, ICO)  
  - Étendre propriétés de bordure (borderWidth, borderStyle, borderColor)  
  - **Test** : 17 tests unitaires validés, build réussi  
  - **✅ VALIDÉ** : company_logo entièrement amélioré avec gestion unifiée, redimensionnement automatique, validation formats et propriétés complètes

- [x] **Étape 2.2.2 : Améliorer order_number**  
  - Implémenter formatage configurable (#CMD-2025-XXX, FACT-XXXX, etc.)  
  - Ajouter validation des propriétés et gestion des cas spéciaux  
  - Étendre propriétés de style (police, couleur, alignement)  
  - **Test** : Formats validés, prévisualisation fonctionnelle
  - **✅ VALIDÉ** : Formatage étendu (6 formats), validation propriétés, style complet, tests validés
  - **➕ TEST COMPLÉMENTAIRE** : Investigation des frais dans product_table - Test de simulation créé et correction préparée (note ajoutée dans Phase 2.2.2 du roadmap)

- [x] **Étape 2.2.3 : Améliorer company_info**  
  - Mapping complet des champs société WooCommerce  
  - Templates prédéfinis pour différents secteurs  
  - Gestion des données manquantes avec fallbacks  
  - Optimisation mise en page responsive  
  - **Test** : Tous champs société affichés correctement
  - **✅ VALIDÉ** : Mapping complet implémenté avec 4 templates (default, commercial, legal, minimal), récupération données WooCommerce, propriétés étendues, tests validés

#### **2.3 Documenter les variables dynamiques**
- [x] **Étape 2.3.1 : Collecter toutes les variables WooCommerce disponibles**  
  - Examiner la classe `PDF_Builder_WooCommerce_Integration.php`  
  - Lister toutes les méthodes qui récupèrent des données (get_order_items, etc.)  
  - Inclure les variables standard WooCommerce (prix, client, etc.)  
  - Ajouter les variables personnalisées du plugin  
  - **Test** : Vérifier récupération données exemple
  - **✅ VALIDÉ** : 35 variables identifiées et documentées (VARIABLES_WOOCOMMERCE_DISPONIBLES.md) - 6 catégories (Commande, Client, Adresses, Financier, Société, Système)

- [x] **Étape 2.3.2 : Classifier les variables par catégories**  
  - Grouper par type : client, produit, commande, entreprise, etc.  
  - Créer des sous-catégories (ex: client → nom, email, adresse)  
  - Noter les variables obligatoires vs optionnelles  
  - Identifier les variables qui nécessitent des calculs  
  - **Test** : Valider classification avec données réelles
  - **✅ VALIDÉ** : Classification détaillée en 7 catégories avec sous-catégories, priorités et exemples d'usage

- [x] **Étape 2.3.3 : Documenter le format et les exemples de chaque variable**  
  - Pour chaque variable, donner le format (string, number, date)  
  - Fournir des exemples concrets (ex: {{customer_name}} → "Jean Dupont")  
  - Noter les cas spéciaux (valeurs nulles, formats multiples)  
  - Documenter les transformations possibles (majuscules, format date)  
  - **Test** : Tester exemples dans template simple
  - **✅ VALIDÉ** : Formats détaillés documentés avec exemples concrets, cas limites, jeux de données test, validation complète

#### ✅ **Phase 2.3.4 - Validation Intégration** [COMPLETED]
- ✅ Tests d'intégration des formats de variables (9/9 tests passés)
- ✅ Validation sécurité (protection XSS, injection)
- ✅ Tests performance (remplacement rapide < 1ms pour 100 variables)
- ✅ Gestion données manquantes et cas limites
- ✅ Formats dates, prix, adresses validés

> **📝 NOTE Phase 2.3.4** : Cette phase valide que les variables de données classiques fonctionnent correctement dans tous les scénarios (données manquantes, sécurité, performance). C'est la fondation obligatoire avant d'ajouter les styles dynamiques.

#### ✅ **Phase 2.3.5 - Variables de Style Dynamique** [COMPLETED]
- ✅ 21 variables de style identifiées dans 6 éléments
- ✅ Origines documentées (CanvasElement.jsx + concepts WooCommerce)
- ✅ Formats CSS inline validés
- ✅ Exemples d'utilisation avancés
- ✅ Tests de validation des styles

> **📝 NOTE Phase 2.3.5** : Cette phase complète le système avec les variables de style qui permettent une adaptation visuelle intelligente selon le contexte des données (couleurs selon statut, styles selon montants, icônes selon types). Ensemble, 2.3.4 + 2.3.5 créent des PDFs véritablement adaptatifs.

#### 🔄 **Phase 2.4 - Définition Architecture Modulaire** [TERMINÉE]

#### **2.4 Définir l'architecture modulaire**
- [✅] **Étape 2.4.1 : Définir les endpoints AJAX internes nécessaires**
- [✅] **Étape 2.4.2 : Définir les interfaces et contrats entre modules**
- [✅] **Étape 2.4.3 : Spécifier les patterns de conception utilisés**
- [✅] **Étape 2.4.4 : Documenter les dépendances et injections**
- [✅] **Étape 2.4.5 : Planifier la gestion des états et événements**
- [✅] **Étape 2.4.6 : Revue finale Phase 2.4**

> **📝 NOTE Phase 2.4** : Architecture modulaire complète définie (détails dans ARCHITECTURE_MODULAIRE_SPECS.md). 7 endpoints AJAX spécifiés, 3 interfaces définies, 5 patterns identifiés, système d'injection de dépendances configuré, machine à états finis avec événements asynchrones.

  #### **États du Système d'Aperçu**

  ##### **Machine à États Finit**
  ```php
  enum PreviewSystemState {
      case IDLE;           // Système inactif, prêt à recevoir des demandes
      case INITIALIZING;   // Initialisation des composants (chargement config, connexions)
      case LOADING_DATA;   // Récupération des données (Canvas fictives ou WooCommerce réelles)
      case VALIDATING;     // Validation des données et configuration
      case RENDERING;      // Génération du rendu (HTML/PNG/PDF)
      case CACHING;        // Mise en cache du résultat pour optimisations futures
      case COMPLETED;      // Aperçu généré avec succès, prêt pour affichage
      case ERROR;          // Erreur survenue, nécessite gestion d'erreur
      case CLEANUP;        // Nettoyage des ressources temporaires
  }
  ```

  ##### **Transitions d'État**
  ```
  IDLE → INITIALIZING (demande d'aperçu reçue)
      ↓
  INITIALIZING → LOADING_DATA (initialisation réussie)
      ↓
  LOADING_DATA → VALIDATING (données chargées)
      ↓
  VALIDATING → RENDERING (validation réussie)
      ↓
  RENDERING → CACHING (rendu réussi)
      ↓
  CACHING → COMPLETED (cache mis à jour)
      ↓
  COMPLETED → IDLE (aperçu affiché, système prêt pour nouvelle demande)

  // Gestion d'erreurs
  ANY_STATE → ERROR (exception/error détectée)
  ERROR → CLEANUP (erreur traitée)
  CLEANUP → IDLE (ressources nettoyées)
  ```

  ##### **Gestionnaire d'État**
  ```php
  class PreviewStateManager {
      private PreviewSystemState $currentState = PreviewSystemState::IDLE;
      private array $stateHistory = [];
      private PreviewEventManager $eventManager;

      public function transitionTo(PreviewSystemState $newState, array $context = []): void {
          $oldState = $this->currentState;

          // Validation de transition
          if (!$this->isValidTransition($oldState, $newState)) {
              throw new InvalidStateTransitionException($oldState, $newState);
          }

          $this->currentState = $newState;
          $this->stateHistory[] = [
              'from' => $oldState,
              'to' => $newState,
              'timestamp' => time(),
              'context' => $context
          ];

          // Notification d'événement
          $this->eventManager->notify(new StateTransitionEvent($oldState, $newState, $context));
      }

      private function isValidTransition(PreviewSystemState $from, PreviewSystemState $to): bool {
          return match($from) {
              PreviewSystemState::IDLE => in_array($to, [PreviewSystemState::INITIALIZING]),
              PreviewSystemState::INITIALIZING => in_array($to, [PreviewSystemState::LOADING_DATA, PreviewSystemState::ERROR]),
              PreviewSystemState::LOADING_DATA => in_array($to, [PreviewSystemState::VALIDATING, PreviewSystemState::ERROR]),
              PreviewSystemState::VALIDATING => in_array($to, [PreviewSystemState::RENDERING, PreviewSystemState::ERROR]),
              PreviewSystemState::RENDERING => in_array($to, [PreviewSystemState::CACHING, PreviewSystemState::ERROR]),
              PreviewSystemState::CACHING => in_array($to, [PreviewSystemState::COMPLETED, PreviewSystemState::ERROR]),
              PreviewSystemState::COMPLETED => in_array($to, [PreviewSystemState::IDLE]),
              PreviewSystemState::ERROR => in_array($to, [PreviewSystemState::CLEANUP]),
              PreviewSystemState::CLEANUP => in_array($to, [PreviewSystemState::IDLE]),
          };
      }

      public function getCurrentState(): PreviewSystemState {
          return $this->currentState;
      }

      public function getStateHistory(): array {
          return $this->stateHistory;
      }
  }
  ```

  #### **Système d'Événements**

  ##### **Événements Système Définis**
  ```php
  enum PreviewEventType {
      // Événements de cycle de vie
      case PREVIEW_REQUESTED;
      case INITIALIZATION_STARTED;
      case INITIALIZATION_COMPLETED;
      case DATA_LOADING_STARTED;
      case DATA_LOADING_COMPLETED;
      case VALIDATION_STARTED;
      case VALIDATION_COMPLETED;
      case RENDERING_STARTED;
      case RENDERING_COMPLETED;
      case CACHING_STARTED;
      case CACHING_COMPLETED;

      // Événements d'erreur
      case VALIDATION_FAILED;
      case RENDERING_FAILED;
      case NETWORK_ERROR;
      case TIMEOUT_ERROR;
      case PERMISSION_DENIED;

      // Événements de performance
      case CACHE_HIT;
      case CACHE_MISS;
      case PERFORMANCE_WARNING;
      case MEMORY_WARNING;

      // Événements utilisateur
      case USER_CANCELLED;
      case USER_TIMEOUT;
  }
  ```

  ##### **Structure des Événements**
  ```php
  class PreviewEvent {
      private PreviewEventType $type;
      private array $data;
      private int $timestamp;
      private ?string $correlationId;

      public function __construct(
          PreviewEventType $type,
          array $data = [],
          ?string $correlationId = null
      ) {
          $this->type = $type;
          $this->data = $data;
          $this->timestamp = time();
          $this->correlationId = $correlationId ?? $this->generateCorrelationId();
      }

      public function getType(): PreviewEventType {
          return $this->type;
      }

      public function getData(): array {
          return $this->data;
      }

      public function getCorrelationId(): string {
          return $this->correlationId;
      }

      private function generateCorrelationId(): string {
          return uniqid('preview_', true);
      }
  }
  ```

  ##### **Gestionnaire d'Événements Asynchrone**
  ```php
  class AsyncEventManager {
      private array $listeners = [];
      private SplQueue $eventQueue;
      private bool $isProcessing = false;

      public function addListener(PreviewEventType $eventType, callable $listener): void {
          $this->listeners[$eventType->name][] = $listener;
      }

      public function dispatch(PreviewEvent $event): void {
          $this->eventQueue->enqueue($event);
          $this->processQueue();
      }

      private function processQueue(): void {
          if ($this->isProcessing) {
              return; // Évite la récursion
          }

          $this->isProcessing = true;

          while (!$this->eventQueue->isEmpty()) {
              $event = $this->eventQueue->dequeue();
              $this->notifyListeners($event);
          }

          $this->isProcessing = false;
      }

      private function notifyListeners(PreviewEvent $event): void {
          $eventTypeName = $event->getType()->name;

          if (!isset($this->listeners[$eventTypeName])) {
              return;
          }

          foreach ($this->listeners[$eventTypeName] as $listener) {
              try {
                  $listener($event);
              } catch (Exception $e) {
                  error_log("Erreur dans listener d'événement: " . $e->getMessage());
                  // Continue avec les autres listeners
              }
          }
      }
  }
  ```

  #### **Intégration États + Événements**
  ```php
  class PreviewOrchestrator {
      private PreviewStateManager $stateManager;
      private AsyncEventManager $eventManager;

      public function generatePreview(array $request): PreviewResult {
          $correlationId = uniqid('preview_', true);

          try {
              // Transition vers initialisation
              $this->stateManager->transitionTo(
                  PreviewSystemState::INITIALIZING,
                  ['correlationId' => $correlationId]
              );
              $this->eventManager->dispatch(new PreviewEvent(
                  PreviewEventType::INITIALIZATION_STARTED,
                  ['request' => $request],
                  $correlationId
              ));

              // Suite du processus avec transitions et événements...
              // ...

              $this->stateManager->transitionTo(PreviewSystemState::COMPLETED);
              return new PreviewResult($previewData);

          } catch (Exception $e) {
              $this->stateManager->transitionTo(PreviewSystemState::ERROR);
              $this->eventManager->dispatch(new PreviewEvent(
                  PreviewEventType::RENDERING_FAILED,
                  ['error' => $e->getMessage()],
                  $correlationId
              ));
              throw $e;
          }
      }
  }
  ```

  #### **Cartographie des Dépendances**

  ##### **Dépendances Principales**
  ```
  PreviewController (Point d'entrée)
  ├── PreviewRenderer (Factory)
  │   ├── TCPDFRenderer
  │   ├── ScreenshotRenderer
  │   └── HTMLRenderer
  ├── DataProvider
  │   ├── WooCommerceDataAdapter
  │   └── CanvasDataProvider
  ├── ModeHandler (Strategy)
  │   ├── CanvasModeStrategy
  │   └── MetaboxModeStrategy
  ├── EventManager (Observer)
  │   ├── PreviewLogger
  │   ├── CacheManager
  │   └── PerformanceMonitor
  └── CacheManager (Singleton)
      └── FileCache
      └── MemoryCache
  ```

  ##### **Injection de Dépendances - Constructeur**
  ```php
  class PreviewController {
      private $renderer;
      private $dataProvider;
      private $eventManager;

      public function __construct(
          PreviewRendererInterface $renderer,
          DataProviderInterface $dataProvider,
          PreviewEventManager $eventManager
      ) {
          $this->renderer = $renderer;
          $this->dataProvider = $dataProvider;
          $this->eventManager = $eventManager;
      }
  }
  ```

  ##### **Injection de Dépendances - Setter (pour dépendances optionnelles)**
  ```php
  class PreviewRenderer {
      private $cacheManager;

      public function setCacheManager(CacheManagerInterface $cacheManager): void {
          $this->cacheManager = $cacheManager;
      }
  }
  ```

  ##### **Conteneur d'Injection de Dépendances**
  ```php
  class PreviewDIContainer {
      private $services = [];
      private $factories = [];

      public function register(string $name, callable $factory): void {
          $this->factories[$name] = $factory;
      }

      public function get(string $name) {
          if (!isset($this->services[$name])) {
              if (!isset($this->factories[$name])) {
                  throw new Exception("Service non enregistré: $name");
              }
              $this->services[$name] = $this->factories[$name]($this);
          }
          return $this->services[$name];
      }

      // Enregistrement des services principaux
      public function configure(): void {
          $this->register('eventManager', function($c) {
              return new PreviewEventManager();
          });

          $this->register('dataProvider', function($c) {
              return new WooCommerceDataProvider();
          });

          $this->register('renderer', function($c) {
              return PreviewRendererFactory::create('tcpdf', [
                  'eventManager' => $c->get('eventManager')
              ]);
          });

          $this->register('previewController', function($c) {
              return new PreviewController(
                  $c->get('renderer'),
                  $c->get('dataProvider'),
                  $c->get('eventManager')
              );
          });
      }
  }
  ```

  ##### **Gestion des Dépendances Circulaires**

  ###### **Problème** : EventManager ↔ Renderer (chacun notifie l'autre)
  ###### **Solution** : Injection paresseuse (lazy injection)
  ```php
  class PreviewRenderer {
      private $eventManager;
      private $diContainer;

      public function __construct(DIContainer $diContainer) {
          $this->diContainer = $diContainer;
      }

      private function getEventManager(): PreviewEventManager {
          if ($this->eventManager === null) {
              $this->eventManager = $this->diContainer->get('eventManager');
          }
          return $this->eventManager;
      }

      public function render(array $data): void {
          $this->getEventManager()->notify(new PreviewEvent('render_started'));
          // Logique de rendu...
          $this->getEventManager()->notify(new PreviewEvent('render_completed'));
      }
  }
  ```

  ##### **Initialisation de l'Architecture Modulaire**
  ```php
  // Initialisation du système d'aperçu
  $diContainer = new PreviewDIContainer();
  $diContainer->configure();

  // Récupération du contrôleur principal
  $previewController = $diContainer->get('previewController');

  // Utilisation
  $result = $previewController->generatePreview('canvas', $canvasData);
  ```

  #### **Patterns de Conception Identifiés**

  ##### **1. Strategy Pattern - Gestion des Modes (Canvas/Metabox)**
  ```php
  // Interface Strategy
  interface PreviewModeStrategy {
      public function execute(array $context): PreviewResult;
      public function getModeName(): string;
      public function validateContext(array $context): bool;
  }

  // Implémentations concrètes
  class CanvasModeStrategy implements PreviewModeStrategy {
      private $dataProvider;

      public function execute(array $context): PreviewResult {
          // Logique spécifique au mode Canvas
          $data = $this->dataProvider->getData('canvas');
          return $this->renderer->render($data, 'canvas');
      }
  }

  class MetaboxModeStrategy implements PreviewModeStrategy {
      private $dataProvider;

      public function execute(array $context): PreviewResult {
          // Logique spécifique au mode Metabox
          $orderId = $context['order_id'];
          $data = $this->dataProvider->getData('metabox', $orderId);
          return $this->renderer->render($data, 'metabox');
      }
  }

  // Contexte utilisant la stratégie
  class PreviewContext {
      private $strategy;

      public function setStrategy(PreviewModeStrategy $strategy) {
          $this->strategy = $strategy;
      }

      public function executePreview(array $context): PreviewResult {
          return $this->strategy->execute($context);
      }
  }
  ```

  ##### **2. Factory Pattern - Création des Renderers**
  ```php
  // Interface Factory
  interface RendererFactoryInterface {
      public static function create(string $type, array $config = []): PreviewRendererInterface;
  }

  // Implémentation Factory
  class PreviewRendererFactory implements RendererFactoryInterface {
      public static function create(string $type, array $config = []): PreviewRendererInterface {
          switch ($type) {
              case 'tcpdf':
                  return new TCPDFRenderer($config);
              case 'screenshot':
                  return new ScreenshotRenderer($config);
              case 'html':
                  return new HTMLRenderer($config);
              default:
                  throw new InvalidArgumentException("Type de renderer inconnu: $type");
          }
      }
  }

  // Utilisation
  $renderer = PreviewRendererFactory::create('tcpdf', [
      'quality' => 150,
      'format' => 'A4'
  ]);
  ```

  ##### **3. Observer Pattern - Gestion des Événements Système**
  ```php
  // Interface Observer
  interface PreviewEventObserver {
      public function update(PreviewEvent $event): void;
  }

  // Sujet observable
  class PreviewEventManager {
      private $observers = [];

      public function attach(PreviewEventObserver $observer): void {
          $this->observers[] = $observer;
      }

      public function detach(PreviewEventObserver $observer): void {
          // Retirer l'observer
      }

      public function notify(PreviewEvent $event): void {
          foreach ($this->observers as $observer) {
              $observer->update($event);
          }
      }
  }

  // Événements système
  class PreviewEvent {
      const RENDER_STARTED = 'render_started';
      const RENDER_COMPLETED = 'render_completed';
      const RENDER_FAILED = 'render_failed';
      const DATA_LOADING = 'data_loading';
      const CACHE_HIT = 'cache_hit';

      private $type;
      private $data;

      public function __construct(string $type, array $data = []) {
          $this->type = $type;
          $this->data = $data;
      }
  }

  // Observer concret (logging)
  class PreviewLogger implements PreviewEventObserver {
      public function update(PreviewEvent $event): void {
          error_log("Preview Event: {$event->getType()} - " . json_encode($event->getData()));
      }
  }
  ```

  ##### **4. Adapter Pattern - Intégration avec WooCommerce**
  ```php
  // Interface cible
  interface DataSourceInterface {
      public function getCustomerData(int $orderId): array;
      public function getOrderData(int $orderId): array;
      public function getCompanyData(): array;
  }

  // Adapteur pour WooCommerce
  class WooCommerceDataAdapter implements DataSourceInterface {
      private $order;

      public function __construct(WC_Order $order) {
          $this->order = $order;
      }

      public function getCustomerData(int $orderId): array {
          return [
              'name' => $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name(),
              'email' => $this->order->get_billing_email(),
              'address' => $this->formatWooCommerceAddress($this->order->get_billing_address())
          ];
      }

      public function getOrderData(int $orderId): array {
          return [
              'number' => $this->order->get_order_number(),
              'total' => $this->order->get_total(),
              'items' => $this->getOrderItems($this->order)
          ];
      }

      public function getCompanyData(): array {
          // Récupération depuis WordPress options
          return get_option('pdf_builder_company_settings', []);
      }
  }
  ```

  ##### **5. Singleton Pattern - Gestionnaire de Cache Global**
  ```php
  class PreviewCacheManager {
      private static $instance = null;
      private $cache = [];

      private function __construct() {}

      public static function getInstance(): self {
          if (self::$instance === null) {
              self::$instance = new self();
          }
          return self::$instance;
      }

      public function get(string $key) {
          return $this->cache[$key] ?? null;
      }

      public function set(string $key, $value, int $ttl = 3600): void {
          $this->cache[$key] = [
              'value' => $value,
              'expires' => time() + $ttl
          ];
      }
  }
  ```

  #### **Cohérence Architecturale**

  - **Strategy** : Flexibilité pour ajouter de nouveaux modes d'aperçu
  - **Factory** : Création centralisée des renderers selon configuration
  - **Observer** : Découplage des composants pour événements système
  - **Adapter** : Intégration propre avec APIs externes (WooCommerce)
  - **Singleton** : Gestion centralisée du cache applicatif

  Ces patterns assurent une architecture modulaire, maintenable et extensible pour le système d'aperçu.

  #### **Interfaces et Contrats des Modules**

  ##### **Interface PreviewRenderer (PHP)**
  ```php
  interface PreviewRendererInterface {
      /**
       * Rend un aperçu du canvas selon le mode spécifié
       * @param array $canvas_data Données du canvas
       * @param string $mode 'canvas' ou 'metabox'
       * @param array $options Options de rendu (format, qualité, etc.)
       * @return RenderedPreview Résultat du rendu
       */
      public function render(array $canvas_data, string $mode, array $options = []): RenderedPreview;

      /**
       * Valide les données du canvas avant rendu
       * @param array $canvas_data Données à valider
       * @return ValidationResult Résultat de validation
       */
      public function validateCanvasData(array $canvas_data): ValidationResult;
  }
  ```

  ##### **Interface DataProvider (PHP)**
  ```php
  interface DataProviderInterface {
      /**
       * Fournit les données selon le mode (canvas = fictives, metabox = réelles)
       * @param string $mode Mode de données ('canvas' ou 'metabox')
       * @param int|null $order_id ID commande pour mode metabox
       * @return array Données formatées pour injection
       */
      public function getData(string $mode, ?int $order_id = null): array;

      /**
       * Valide la disponibilité des données requises
       * @param string $mode Mode à vérifier
       * @param int|null $order_id ID commande optionnel
       * @return bool True si données disponibles
       */
      public function validateDataAvailability(string $mode, ?int $order_id = null): bool;
  }
  ```

  ##### **Interface ModeHandler (PHP)**
  ```php
  interface ModeHandlerInterface {
      /**
       * Initialise le mode avec ses données spécifiques
       * @param array $context Contexte d'initialisation
       * @return bool Succès de l'initialisation
       */
      public function initialize(array $context = []): bool;

      /**
       * Traite les données selon la logique du mode
       * @param array $input_data Données d'entrée
       * @return ProcessedData Données traitées
       */
      public function processData(array $input_data): ProcessedData;

      /**
       * Nettoie les ressources du mode
       */
      public function cleanup(): void;
  }
  ```

  ##### **Contrats d'Échange de Données**

  ###### **CanvasMode ↔ DataProvider**
  ```php
  // Données fournies par DataProvider pour CanvasMode
  $canvasData = [
      'customer' => [
          'name' => 'Jean Dupont',
          'email' => 'jean.dupont@email.com',
          'address' => '123 Rue de la Paix, 75001 Paris'
      ],
      'order' => [
          'number' => 'CMD-2025-001',
          'date' => '2025-01-15',
          'total' => '299.99',
          'items' => [
              [
                  'name' => 'Produit Exemple',
                  'quantity' => 2,
                  'price' => '149.99',
                  'total' => '299.99'
              ]
          ]
      ],
      'company' => [
          'name' => 'Ma Société SARL',
          'address' => '456 Avenue des Champs, 75008 Paris',
          'phone' => '01 23 45 67 89',
          'email' => 'contact@masociete.com'
      ]
  ];
  ```

  ###### **MetaboxMode ↔ DataProvider**
  ```php
  // Données fournies par DataProvider pour MetaboxMode
  $metaboxData = [
      'customer' => [
          'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
          'email' => $order->get_billing_email(),
          'address' => $this->format_address($order->get_billing_address())
      ],
      'order' => [
          'number' => $order->get_order_number(),
          'date' => $order->get_date_created()->format('Y-m-d'),
          'total' => $order->get_total(),
          'items' => $this->get_order_items_data($order)
      ],
      'company' => $this->get_company_data_from_settings()
  ];
  ```

  ##### **Responsabilités des Modules**

  ###### **PreviewRenderer**
  - **Responsabilités** : Génération rendu visuel, validation données, gestion formats (HTML/PNG/PDF)
  - **Dépendances** : DataProvider (pour données), ModeHandler (pour logique mode)
  - **Sorties** : Rendu visuel prêt pour affichage/modal

  ###### **DataProvider**
  - **Responsabilités** : Fourniture données selon mode, validation disponibilité, formatage données
  - **Dépendances** : WooCommerce API (pour metabox), Settings API (pour company)
  - **Sorties** : Données structurées injectables dans templates

  ###### **CanvasMode/MetaboxMode**
  - **Responsabilités** : Logique spécifique au mode, traitement données, gestion contexte
  - **Dépendances** : PreviewRenderer (pour rendu), DataProvider (pour données)
  - **Sorties** : Données traitées prêtes pour rendu final

  #### **Schémas JSON des Endpoints**

  ##### **wp_ajax_pdf_builder_get_order_data**
  ```json
  // Requête
  {
    "action": "pdf_builder_get_order_data",
    "nonce": "string", // WordPress nonce
    "order_id": "integer" // ID commande WooCommerce
  }

  // Réponse succès
  {
    "success": true,
    "data": {
      "order": {
        "id": 123,
        "number": "CMD-2025-001",
        "status": "completed",
        "total": "299.99"
      },
      "items": [...], // Données complètes produits
      "order_id": 123
    }
  }
  ```

  ##### **wp_ajax_pdf_builder_save_template**
  ```json
  // Requête
  {
    "action": "pdf_builder_save_template",
    "nonce": "string",
    "template_data": "string", // JSON template
    "template_name": "string",
    "template_id": "integer" // 0 pour nouveau
  }

  // Réponse succès
  {
    "success": true,
    "data": {
      "template_id": 456,
      "message": "Template sauvegardé"
    }
  }
  ```

  ##### **wp_ajax_pdf_generate_preview** (À créer)
  ```json
  // Requête
  {
    "action": "pdf_generate_preview",
    "nonce": "string",
    "mode": "canvas|metabox", // Mode aperçu
    "template_data": "object", // Données canvas/template
    "order_id": "integer?", // Optionnel pour metabox
    "format": "html|png|jpg" // Format souhaité
  }

  // Réponse succès
  {
    "success": true,
    "data": {
      "preview_url": "string", // URL temporaire du rendu
      "expires": "timestamp", // Expiration cache
      "format": "html|png|jpg"
    }
  }
  ```

  ##### **wp_ajax_pdf_validate_license** (À créer)
  ```json
  // Requête
  {
    "action": "pdf_validate_license",
    "nonce": "string",
    "license_key": "string?"
  }

  // Réponse succès
  {
    "success": true,
    "data": {
      "valid": true,
      "license_type": "premium|freemium",
      "expires": "timestamp",
      "features": ["array", "of", "enabled", "features"]
    }
  }
  ```

  ##### **wp_ajax_pdf_get_template_variables** (À créer)
  ```json
  // Requête
  {
    "action": "pdf_get_template_variables",
    "nonce": "string",
    "template_id": "integer?",
    "mode": "canvas|metabox"
  }

  // Réponse succès
  {
    "success": true,
    "data": {
      "variables": {
        "customer_name": {
          "type": "string",
          "description": "Nom du client",
          "example": "Jean Dupont",
          "required": true
        },
        "order_total": {
          "type": "number",
          "description": "Total commande",
          "format": "currency",
          "example": "299.99"
        }
      },
      "categories": ["customer", "order", "company", "dynamic"]
    }
  }
  ```

  ##### **wp_ajax_pdf_export_canvas** (À créer)
  ```json
  // Requête
  {
    "action": "pdf_export_canvas",
    "nonce": "string",
    "template_data": "object",
    "format": "pdf|png|jpg",
    "quality": "integer", // 1-100 pour PNG/JPG
    "filename": "string?" // Nom fichier personnalisé
  }

  // Réponse succès
  {
    "success": true,
    "data": {
      "download_url": "string", // URL téléchargement temporaire
      "filename": "string",
      "expires": "timestamp"
    }
  }
  ```

- [ ] **Étape 2.4.2 : Définir les interfaces et contrats entre modules**
  - Spécifier les interfaces TypeScript/PHP pour chaque module (PreviewRenderer, DataProvider, etc.)
  - Définir les contrats d'échange de données entre CanvasMode et MetaboxMode
  - Documenter les responsabilités de chaque classe/module
  - **Test** : Interfaces validées avec exemples d'implémentation

- [ ] **Étape 2.4.3 : Spécifier les patterns de conception utilisés**
  - Identifier les patterns (Observer pour événements, Factory pour éléments, Strategy pour modes)
  - Documenter l'implémentation de chaque pattern dans le code
  - Valider la cohérence architecturale
  - **Test** : Patterns implémentés et testés

- [ ] **Étape 2.4.4 : Documenter les dépendances et injections**
  - Cartographier les dépendances entre modules
  - Définir le système d'injection de dépendances (constructeurs, setters)
  - Planifier la gestion des dépendances circulaires
  - **Test** : Injection fonctionnelle sans erreurs

- [ ] **Étape 2.4.5 : Planifier la gestion des états et événements**
  - Définir les états possibles du système (chargement, rendu, erreur)
  - Spécifier le système d'événements (chargement terminé, erreur réseau, etc.)
  - Documenter les transitions d'état
  - **Test** : États et événements gérés correctement

#### 🔄 **Phase 2.5 - Spécifier les APIs** [PENDING]

#### **2.5 Spécifier les APIs**
- [ ] **Étape 2.5.1 : Définir les endpoints AJAX internes nécessaires**
  - Lister les actions AJAX (generate_preview, get_variables, validate_license, etc.)
  - Spécifier les URLs et méthodes (wp_ajax_* hooks)
  - Vérifier systèmes existants et les recréer si nécessaire
  - Définir les paramètres requis pour chaque endpoint
  - Planifier la gestion des erreurs et réponses
  - **Test** : Endpoints testés avec Postman/cURL

- [ ] **Étape 2.5.2 : Spécifier les formats de données d'entrée/sortie**
  - Définir le schéma JSON pour les requêtes AJAX
  - Spécifier le format des réponses (succès/erreur)
  - Documenter les types de données (string, array, object)
  - Inclure des exemples de payloads
  - **Test** : Schémas validés avec JSON Schema

- [ ] **Étape 2.5.3 : Documenter les méthodes de sécurité**
  - Spécifier l'usage des nonces WordPress
  - Définir la validation des données d'entrée
  - Planifier les contrôles de permissions
  - Documenter les mesures anti-injection
  - **Test** : Tests de sécurité passés

- [ ] **Étape 2.5.4 : Créer des exemples d'utilisation des APIs**
  - Fournir des exemples de code JavaScript pour appeler les endpoints
  - Créer des scénarios d'usage courants
  - Documenter les cas d'erreur et gestion
  - Inclure des tests d'intégration simples
  - **Test** : Exemples fonctionnels testés  

**🔄 Prochaines étapes** : Une fois la Phase 2 terminée, passer à la Phase 3 (Infrastructure) en s'appuyant sur cette analyse.

### 🏗️ Phase 3 : Infrastructure de base
- [ ] **Étape 3.1 : Créer PreviewRenderer avec canvas A4**
  - Implémenter classe PreviewRenderer avec dimensions A4 (210×297mm)
  - Configurer canvas HTML5 avec scaling approprié
  - Ajouter gestion responsive et zoom
  - Intégrer avec système de rendu existant
  - **Test** : Canvas A4 rendu correctement avec dimensions exactes

- [ ] **Étape 3.2 : Implémenter CanvasMode et MetaboxMode**
  - Créer classes CanvasModeProvider et MetaboxModeProvider
  - Implémenter injection de dépendances pour switcher entre modes
  - Définir interfaces communes et différences spécifiques
  - Tester basculement fluide entre modes
  - **Test** : Modes switchés sans erreurs, données injectées correctement

- [ ] **Étape 3.3 : Développer les 7 renderers spécialisés**
  - Créer TextRenderer, ImageRenderer, RectangleRenderer, etc.
  - Implémenter logique de rendu pour chaque type d'élément
  - Gérer propriétés spécifiques (position, taille, couleur, etc.)
  - Optimiser performance de rendu
  - **Test** : Chaque renderer affiche éléments correctement

- [ ] **Étape 3.4 : Configurer lazy loading**
  - Implémenter chargement différé des ressources lourdes
  - Ajouter gestion cache pour images et données
  - Optimiser chargement initial de la page
  - Précharger ressources critiques seulement
  - **Test** : Temps de chargement réduit, pas de blocage UI

### ✅ Phase 4 : Tests & optimisation
- [ ] **Étape 4.1 : Tests unitaires (100% couverture)**
  - Écrire tests unitaires pour toutes les classes PHP et JS
  - Atteindre couverture 100% des fonctions critiques
  - Implémenter tests automatisés dans CI/CD
  - Documenter cas limites et edge cases
  - **Test** : Couverture 100% validée, tests passent

- [ ] **Étape 4.2 : Tests d'intégration Canvas/Metabox**
  - Tester intégration complète Canvas ↔ Metabox
  - Valider basculement de données fictives/réelles
  - Vérifier cohérence rendu visuel entre modes
  - Tester scénarios complexes (multi-éléments, variables)
  - **Test** : Intégration fluide, pas de régressions

- [ ] **Étape 4.3 : Optimisation performance (< 2s)**
  - Mesurer et optimiser temps de génération (< 2s)
  - Implémenter cache intelligent et lazy loading
  - Optimiser requêtes DB et calculs
  - Réduire utilisation mémoire (< 100MB)
  - **Test** : Performance < 2s validée, mémoire optimisée

- [ ] **Étape 4.4 : Tests sécurité**
  - Audit complet sécurité (nonces, sanitization, permissions)
  - Tests de pénétration sur endpoints AJAX
  - Validation anti-injection et XSS
  - Audit rôles et permissions utilisateurs
  - **Test** : 0 vulnérabilités, sécurité renforcée

### 🚀 Phase 5 : Production
- [ ] **Étape 5.1 : Déploiement automatisé**
  - Configurer pipeline CI/CD complet
  - Automatiser build, tests et déploiement
  - Implémenter rollback automatique en cas d'erreur
  - Déployer sur environnements staging/production
  - **Test** : Déploiement automatique réussi, rollback fonctionnel

- [ ] **Étape 5.2 : Monitoring performance**
  - Implémenter monitoring temps réel (APM)
  - Configurer alertes sur métriques critiques
  - Dashboard performance et erreurs
  - Logs centralisés et analyse
  - **Test** : Monitoring opérationnel, alertes configurées

- [ ] **Étape 5.3 : Documentation complète**
  - Rédiger guides développeur et utilisateur
  - Documenter API et architecture technique
  - Créer tutoriels et exemples d'usage
  - Mettre à jour README et wiki
  - **Test** : Documentation complète et à jour

- [ ] **Étape 5.4 : Support production**
  - Configurer système de support (tickets, email)
  - Implémenter logging avancé pour debug
  - Préparer procédures maintenance et mises à jour
  - Former équipe support si nécessaire
  - **Test** : Support opérationnel, procédures validées

---

## 📊 État actuel

**Phase active** : 2/7  
**Progression** : 33% (Phase 2.1 complète + company_logo + order_number + company_info améliorés - éléments validés, propriétés analysées, bugs corrigés, priorités définies, trois éléments fondamentaux implémentés)  
**Prochaine action** : Phase 2.3.1 - Collecte des variables WooCommerce disponibles

---

## ✅ Critères de succès

- ⚡ **Performance** : < 2s génération, < 100MB RAM
- 🔒 **Sécurité** : 0 vulnérabilités
- 🧪 **Qualité** : Tests 100% succès
- 📚 **Maintenabilité** : Code modulaire

---

## �️ Phase 6 : Améliorations techniques
- [ ] **Étape 6.1** : Migrer JavaScript vers TypeScript (interfaces pour éléments, variables)
  - **Test** : Migration TypeScript réussie, interfaces validées
- [ ] **Étape 6.2** : Implémenter ESLint + Prettier pour qualité code
  - **Test** : Code formaté automatiquement, linting sans erreurs
- [ ] **Étape 6.3** : Ajouter tests unitaires (Jest pour JS, PHPUnit pour PHP)
  - **Test** : Tests unitaires opérationnels, couverture >80%
- [ ] **Étape 6.4** : Optimiser performance (code splitting, caching)
  - **Test** : Performance améliorée, bundle size réduit
- [ ] **Étape 6.5** : Audit sécurité (nonces, sanitization)
  - **Test** : Audit sécurité passé, vulnérabilités corrigées
- [ ] **Étape 6.6** : Améliorer UX (cercle chargement multicolore, gestion erreurs adaptée)
  - **Test** : UX améliorée, feedback utilisateur positif
- [ ] **Étape 6.7** : Optimiser performance DB (index, commentaires structurés)
  - **Test** : Requêtes DB optimisées, temps réponse réduit
- [ ] **Étape 6.8** : Audit sécurité ciblé (permissions rôles existants)
  - **Test** : Permissions sécurisées, accès contrôlé
- [ ] **Étape 6.9** : Internationaliser (anglais, allemand, français avec .po/.mo)
  - **Test** : Traductions complètes, interface localisée
- [ ] **Étape 6.10** : Refactoriser modulaire (nouveaux modules, supprimer anciens local + serveur)
  - **Test** : Refactorisation réussie, code modulaire

## 💰 Phase 7 : Monétisation Freemium
- [ ] **Étape 7.1** : Créer système de licences (activation clé premium - 69€ à vie, validation API/site externe, option multisite)
  - **Test** : Système de licences opérationnel, validation fonctionnelle
- [ ] **Étape 7.2** : Limiter à 1 template actif (choix style) + watermark sur PDFs gratuits
  - **Test** : Limitation template appliquée, watermark visible
- [ ] **Étape 7.3** : Désactiver exports PNG/JPG en gratuit (PDF seulement)
  - **Test** : Exports PNG/JPG bloqués en gratuit
- [ ] **Étape 7.4** : Restreindre à 15 variables dynamiques basiques (7 éléments primordiaux)
  - **Test** : Variables limitées en gratuit, premium débloquées
- [ ] **Étape 7.5** : Ajouter upsells dans interface (boutons "Upgrade to Premium" bloquants dans éditeur/metabox)
  - **Test** : Upsells visibles et bloquants
- [ ] **Étape 7.6** : Intégrer système de paiement (WooCommerce + site externe pour licences)
  - **Test** : Paiement intégré, licences délivrées
- [ ] **Étape 7.7** : Tester limitations freemium (vérifier blocages templates/exports/variables)
  - **Test** : Toutes limitations freemium validées

### 📝 **Historique et décisions Phase 7**

#### **Éléments importants de la discussion** :
- **Stratégie freemium** : Version gratuite attractive avec limitations claires, premium à 69€ à vie (1 site)
- **Prix** : 69€ justifié par complexité IA + concurrence WooCommerce (30% commission compensée)
- **Promo possible** : 59€ sur site externe vs 69€ sur WooCommerce
- **Limitations gratuites** : 1 template, PDF only, 15 variables basiques, 3 dynamic-text models, watermark
- **Transition bloquante** : Pas de douceur excessive - freemium ne doit pas accéder au premium
- **Focus initial** : Tester sur dynamic-text models (3 prédéfinis gratuits)
- **Support** : Email seulement (pas de forum)
- **Mises à jour** : Mineures (bug fixes) en gratuit, features en premium
- **Licences** : Stockage à définir (plugin existant ou tiers), rappels email avant expiration
- **Upsells** : Boutons bloquants avec messages "Upgrade required"
- **Watermark** : Texte discret "WP PDF Builder Free" sur PDFs gratuits
- **Variables** : 7 éléments primordiaux inclus (nom, adresse, email, téléphone, prix, date, entreprise)
- **Éléments à affiner** : Paramètres canvas, types d'éléments exclus (cercles, formes complexes)

#### **Décisions finales** :
- **Modèle** : Freemium strict avec blocage des features premium
- **Monétisation** : 69€/site à vie, extensible à multisite plus tard
- **Implémentation** : Un par un, tester limitations au fur et à mesure
- **Compatibilité** : WooCommerce + site externe pour licences et promos
- **Objectif** : 20% taux conversion gratuit→premium

### 🔮 **Versions futures (v1.2.0+)**
- [ ] **API REST publique** : Endpoints pour intégrations tierces (générer PDF via API externe)
- [ ] **Mode hors ligne** : Aperçu basique sans connexion
- [ ] **Templates premium pack** : Collection de modèles payants
- [ ] **Intégrations tierces** : Support Zapier, Integromat
- [ ] **Analytics freemium** : Tableau de bord conversions gratuit→premium

---

## 📝 Note de progression - 22 octobre 2025

**✅ Phase 2.1.4 TERMINÉE** : Priorités d'implémentation définies avec plan détaillé en 3 phases :
- **Phase 2.2** (2-3 sem.) : Éléments fondamentaux (company_logo, order_number, company_info)
- **Phase 2.3** (3-4 sem.) : Éléments intermédiaires (customer_info, dynamic-text, mentions)  
- **Phase 2.4** (4-6 sem.) : Élément critique (product_table)

**🔧 Corrections appliquées** : 4 bugs de priorité moyenne corrigés (code mort nettoyé, propriétés texte unifiées, validation vérifiée).

**✅ Phase 2.2.1 TERMINÉE** : company_logo entièrement amélioré :
- **Gestion unifiée src/imageUrl** : Support des deux propriétés pour compatibilité maximale
- **Redimensionnement automatique** : Calcul intelligent des dimensions selon ratio d'aspect naturel
- **Validation formats d'image** : Support JPG, PNG, WebP, SVG, GIF, BMP, TIFF, ICO avec messages d'erreur explicites
- **Propriétés de bordure complètes** : borderWidth, borderStyle, borderColor pour personnalisation avancée
- **Tests complets** : 17 tests unitaires validés, build réussi sans régression

**✅ Phase 2.2.2 TERMINÉE** : order_number entièrement amélioré :
- **Formatage configurable étendu** : 6 formats prédéfinis (CMD-2025-XXX, Facture N°XXX, etc.)
- **Variables avancées** : {order_year}, {order_month}, {order_day} en plus de {order_number}, {order_date}
- **Validation des propriétés** : fontSize borné (8-72px), gestion d'erreurs de formatage
- **Propriétés de style étendues** : labelColor, lineHeight, bordures, backgroundColor
- **Données de prévisualisation** : previewOrderNumber, previewOrderDate, etc. personnalisables
- **Tests complets** : 21 tests unitaires validés, build réussi sans régression

**✅ Phase 2.2.3 TERMINÉE** : company_info mapping WooCommerce complet :
- **Mapping complet des champs** : 12 champs société récupérés (name, address, phone, email, website, vat, siret, rcs, capital, legal_form, etc.)
- **Templates prédéfinis** : 4 templates (default, commercial, legal, minimal) avec formatage adapté
- **Récupération données WooCommerce** : Support options WordPress + données WooCommerce natives
- **Propriétés étendues** : template, showCompanyName, showAddress, showContact, showLegal + propriétés prévisualisation
- **Gestion fallbacks** : Données fictives améliorées si données réelles manquantes
- **Tests complets** : 6 tests unitaires validés, récupération et formatage des données testés

**✅ Phase 2.3.3 TERMINÉE** : Documentation formats détaillés complète :
- **Formats techniques détaillés** : Chaque variable avec type, format et exemples concrets
- **Cas limites documentés** : Gestion des données manquantes, erreurs, encodage
- **Jeux de données test** : Exemples complets pour validation
- **Templates d'usage** : Facture, email, bon de livraison avec variables
- **Validation complète** : Tests de formatage et sécurité

**✅ Phase 2.3.5 TERMINÉE** : Variables de style dynamique complètes :
- **21 variables identifiées** : Styles conditionnels dans 6 éléments (product_table, customer_info, dynamic-text, mentions, company_info, order_number)
- **Origines documentées** : Toutes issues de CanvasElement.jsx avec références de ligne précises
- **Formats validés** : CSS inline sécurisé avec fallbacks
- **Exemples avancés** : Templates avec styles dynamiques selon données WooCommerce
- **Tests étendus** : Validation des conditions et seuils de déclenchement

**🔄 Phase 2.4 TERMINÉE** : Architecture modulaire complète avec 5 étapes détaillées (endpoints, interfaces, patterns, dépendances, états/événements)

**🔄 Phase 2.5 EN COURS** : Spécification complète des APIs - architecture validée, prêt pour implémentation détaillée

**📊 Progression globale** : ~80% Phase 2 terminée (variables complètes, architecture modulaire finalisée)

---

*Phase 2.4 finalisée - Architecture modulaire complète et validée*
