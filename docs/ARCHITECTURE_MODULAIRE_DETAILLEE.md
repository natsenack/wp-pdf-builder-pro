#### ✅ **Phase 2.4 - Définition Architecture Modulaire** [TERMINÉE]

## 🏗️ Architecture Modulaire Détaillée - Phase 2.4

**📅 Date** : 22 octobre 2025
**🔄 Statut** : Architecture complète et validée
**📊 Progression** : Phase 2.4 terminée (5/5 étapes)

---

## 🎯 Vue d'ensemble

Ce document détaille l'architecture modulaire complète définie pour le système d'aperçu unifié PDF Builder Pro. Cette architecture assure la s
éparation claire des responsabilités, l'extensibilité et la maintenabilité du système.                                                        
---

## 📋 Schémas JSON des Endpoints

### **Endpoints Existants**

#### **wp_ajax_pdf_builder_get_order_data**
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

#### **wp_ajax_pdf_builder_save_template**
```json
// Requête
{
  "action": "pdf_builder_save_template",
  "nonce": "string",
  "template_data": "string", // JSON template
  "template_name": "string",
  "order_id": "integer" // 0 pour nouveau
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

### **Endpoints à Créer**

#### **wp_ajax_pdf_generate_preview**
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

#### **wp_ajax_pdf_validate_license**
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

#### **wp_ajax_pdf_get_template_variables**
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

#### **wp_ajax_pdf_export_canvas**
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

---

## 🔧 Interfaces et Contrats des Modules

### **Interface PreviewRenderer (PHP)**
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

### **Interface DataProvider (PHP)**
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

### **Interface ModeHandler (PHP)**
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

### **Contrats d'Échange de Données**

#### **CanvasMode ↔ DataProvider**
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

#### **MetaboxMode ↔ DataProvider**
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

### **Responsabilités des Modules**

#### **PreviewRenderer**
- **Responsabilités** : Génération rendu visuel, validation données, gestion formats (HTML/PNG/PDF)
- **Dépendances** : DataProvider (pour données), ModeHandler (pour logique mode)
- **Sorties** : Rendu visuel prêt pour affichage/modal

#### **DataProvider**
- **Responsabilités** : Fourniture données selon mode, validation disponibilité, formatage données
- **Dépendances** : WooCommerce API (pour metabox), Settings API (pour company)
- **Sorties** : Données structurées injectables dans templates

#### **CanvasMode/MetaboxMode**
- **Responsabilités** : Logique spécifique au mode, traitement données, gestion contexte
- **Dépendances** : PreviewRenderer (pour rendu), DataProvider (pour données)
- **Sorties** : Données traitées prêtes pour rendu final

---

## 🏛️ Patterns de Conception

### **1. Strategy Pattern - Gestion des Modes (Canvas/Metabox)**
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

### **2. Factory Pattern - Création des Renderers**
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

### **3. Observer Pattern - Gestion des Événements Système**
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

### **4. Adapter Pattern - Intégration avec WooCommerce**
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
            'name' => $this->order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'email' => $this->order->get_billing_email(),
            'address' => $this->formatWooCommerceAddress($this->order->get_billing_address())
        ];
    }

    public function getOrderData(int $orderId): array {
        return [
            'number' => $order->get_order_number(),
            'total' => $order->get_total(),
            'items' => $this->getOrderItems($this->order)
        ];
    }

    public function getCompanyData(): array {
        // Récupération depuis WordPress options
        return get_option('pdf_builder_company_settings', []);
    }
}
```

### **5. Singleton Pattern - Gestionnaire de Cache Global**
```php
class PreviewCacheManager {
    private static $instance = null;
    private $cache = [];

    private function __construct() {}

    public static function getInstance(): PreviewCacheManager {
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

### **Cohérence Architecturale**
- **Strategy** : Flexibilité pour ajouter de nouveaux modes d'aperçu
- **Factory** : Création centralisée des renderers selon configuration
- **Observer** : Découplage des composants pour événements système
- **Adapter** : Intégration propre avec APIs externes (WooCommerce)
- **Singleton** : Gestion centralisée du cache applicatif

Ces patterns assurent une architecture modulaire, maintenable et extensible pour le système d'aperçu.

---

## 🔗 Cartographie des Dépendances

### **Dépendances Principales**
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

### **Injection de Dépendances - Constructeur**
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

### **Injection de Dépendances - Setter (pour dépendances optionnelles)**
```php
class PreviewRenderer {
    private $cacheManager;

    public function setCacheManager(CacheManagerInterface $cacheManager): void {
        $this->cacheManager = $cacheManager;
    }
}
```

### **Conteneur d'Injection de Dépendances**
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

### **Gestion des Dépendances Circulaires**
#### **Problème** : EventManager ↔ Renderer (chacun notifie l'autre)
#### **Solution** : Injection paresseuse (lazy injection)
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

### **Initialisation de l'Architecture Modulaire**
```php
// Initialisation du système d'aperçu
$diContainer = new PreviewDIContainer();
$diContainer->configure();

// Récupération du contrôleur principal
$previewController = $diContainer->get('previewController');

// Utilisation
$result = $previewController->generatePreview('canvas', $canvasData);
```

---

## 🔄 États et Événements du Système

### **Machine à États Finit**
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

### **Transitions d'État**
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

### **Gestionnaire d'État**
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

### **Système d'Événements**

#### **Événements Système Définis**
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

#### **Structure des Événements**
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

#### **Gestionnaire d'Événements Asynchrone**
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

---

## ✅ Validation Finale

### **Cohérence Architecturale**
- **Modularité** : Séparation claire des responsabilités (rendu, données, modes, événements)
- **Extensibilité** : Patterns permettant l'ajout facile de nouveaux modes/renderers
- **Maintenabilité** : Injection de dépendances évitant le couplage fort
- **Robustesse** : Gestion d'erreurs et états avec recovery automatique
- **Performance** : Cache, événements asynchrones, lazy loading

### **Prêt pour l'Implémentation**
- ✅ Architecture modulaire complète et validée
- ✅ APIs endpoints définis et spécifiés
- ✅ Base solide pour implémentation des APIs détaillées
- ✅ Tests préparés pour validation de l'architecture

---

*Document créé le 22 octobre 2025 - Architecture Phase 2.4 complète*

#### 🔄 **Phase 2.5 - Spécifier les APIs** [PENDING]
