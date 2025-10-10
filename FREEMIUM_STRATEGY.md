# 💰 Stratégie Freemium pour PDF Builder Pro

## 📋 Vue d'ensemble de l'approche

Pour implémenter un modèle freemium efficace, nous allons créer une architecture modulaire qui permet de distinguer clairement les fonctionnalités gratuites des fonctionnalités premium, tout en facilitant les upgrades.

---

## 🏗️ **ARCHITECTURE DE LICENSING**

### **1. Système de Licence Centralisé**

Créons une classe `PDF_Builder_License_Manager` qui gère tous les aspects de licensing :

```php
<?php
class PDF_Builder_License_Manager {

    private static $instance = null;
    private $license_key = '';
    private $license_status = 'free';
    private $license_data = [];

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->license_key = get_option('pdf_builder_license_key', '');
        $this->license_status = get_option('pdf_builder_license_status', 'free');
        $this->license_data = get_option('pdf_builder_license_data', []);

        add_action('admin_init', array($this, 'check_license_status'));
    }

    public function is_premium() {
        return $this->license_status === 'active';
    }

    public function get_license_status() {
        return $this->license_status;
    }

    public function activate_license($license_key) {
        // Validation et activation de la licence
        $result = $this->validate_license($license_key);

        if ($result['success']) {
            update_option('pdf_builder_license_key', $license_key);
            update_option('pdf_builder_license_status', 'active');
            update_option('pdf_builder_license_data', $result['data']);
            $this->license_status = 'active';
            return ['success' => true, 'message' => 'Licence activée avec succès'];
        }

        return ['success' => false, 'message' => $result['message']];
    }

    private function validate_license($license_key) {
        // Appel à votre serveur de licences
        $api_url = 'https://api.pdfbuilderpro.com/validate-license';
        $response = wp_remote_post($api_url, [
            'body' => [
                'license_key' => $license_key,
                'site_url' => get_site_url(),
                'plugin_version' => PDF_BUILDER_VERSION
            ]
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => 'Erreur de connexion'];
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    public function check_license_status() {
        if (empty($this->license_key) || $this->license_status !== 'active') {
            return;
        }

        // Vérification périodique de la licence
        $last_check = get_option('pdf_builder_license_last_check', 0);
        $now = time();

        if ($now - $last_check > 86400) { // Vérifier une fois par jour
            $result = $this->validate_license($this->license_key);

            if (!$result['success']) {
                update_option('pdf_builder_license_status', 'expired');
                $this->license_status = 'expired';
            }

            update_option('pdf_builder_license_last_check', $now);
        }
    }
}
```

### **2. Feature Flags System**

Créons un système de flags pour contrôler l'accès aux fonctionnalités :

```php
class PDF_Builder_Feature_Manager {

    private static $features = [
        // FREE FEATURES
        'basic_templates' => ['free' => true, 'premium' => true],
        'basic_elements' => ['free' => true, 'premium' => true],
        'woocommerce_integration' => ['free' => true, 'premium' => true],
        'pdf_generation' => ['free' => true, 'premium' => true, 'limit' => 50], // 50 PDFs/mois

        // PREMIUM FEATURES
        'advanced_templates' => ['free' => false, 'premium' => true],
        'custom_elements' => ['free' => false, 'premium' => true],
        'bulk_generation' => ['free' => false, 'premium' => true],
        'api_access' => ['free' => false, 'premium' => true],
        'white_label' => ['free' => false, 'premium' => true],
        'priority_support' => ['free' => false, 'premium' => true],
        'unlimited_generation' => ['free' => false, 'premium' => true],
    ];

    public static function can_use_feature($feature_name) {
        $license_manager = PDF_Builder_License_Manager::getInstance();
        $is_premium = $license_manager->is_premium();

        if (!isset(self::$features[$feature_name])) {
            return false;
        }

        $feature = self::$features[$feature_name];

        if ($is_premium) {
            return $feature['premium'];
        }

        // Vérifier les limites pour les utilisateurs free
        if (isset($feature['limit'])) {
            return self::check_usage_limit($feature_name, $feature['limit']);
        }

        return $feature['free'];
    }

    private static function check_usage_limit($feature_name, $limit) {
        $usage_key = 'pdf_builder_usage_' . $feature_name;
        $current_usage = get_option($usage_key, 0);
        $reset_time = get_option($usage_key . '_reset', 0);

        $now = time();
        $month_start = strtotime('first day of this month');

        // Reset counter monthly
        if ($reset_time < $month_start) {
            update_option($usage_key, 0);
            update_option($usage_key . '_reset', $month_start);
            $current_usage = 0;
        }

        return $current_usage < $limit;
    }

    public static function increment_usage($feature_name) {
        $usage_key = 'pdf_builder_usage_' . $feature_name;
        $current_usage = get_option($usage_key, 0);
        update_option($usage_key, $current_usage + 1);
    }
}
```

---

## 🎯 **STRATÉGIE FREEMIUM**

### **FREE TIER - Fonctionnalités de Base**
```php
$free_features = [
    '✅ Templates de base' => '4 templates prédéfinis',
    '✅ Éléments standards' => 'Texte, image, ligne, rectangle',
    '✅ Intégration WooCommerce' => 'Variables de commande',
    '✅ Génération PDF' => '50 PDFs/mois',
    '✅ Export basique' => 'PDF uniquement',
    '✅ Support communautaire' => 'Forum et documentation'
];
```

### **PREMIUM TIER - Fonctionnalités Avancées**
```php
$premium_features = [
    '🚀 Templates avancés' => 'Bibliothèque complète + personnalisation',
    '🚀 Éléments premium' => 'Codes-barres, QR codes, graphiques',
    '🚀 Génération illimitée' => 'Pas de limite mensuelle',
    '🚀 Multi-format' => 'PDF, PNG, JPG, SVG',
    '🚀 API complète' => 'Accès développeur',
    '🚀 White-label' => 'Rebranding complet',
    '🚀 Support prioritaire' => '24/7 avec SLA',
    '🚀 Analytics' => 'Tableaux de bord détaillés'
];
```

---

## 🔧 **IMPLÉMENTATION TECHNIQUE**

### **1. Intégration dans le Core**

Modifions `PDF_Builder_Core.php` pour intégrer le système de licensing :

```php
class PDF_Builder_Core {

    private $license_manager;
    private $feature_manager;

    private function __construct() {
        // ... existing code ...

        // Initialize licensing system
        $this->license_manager = PDF_Builder_License_Manager::getInstance();
        $this->feature_manager = new PDF_Builder_Feature_Manager();
    }

    public function init() {
        // ... existing code ...

        // Add licensing checks
        $this->init_licensing();

        // ... existing code ...
    }

    private function init_licensing() {
        // Add license menu
        add_action('admin_menu', array($this, 'add_license_menu'));

        // Add upgrade notices
        add_action('admin_notices', array($this, 'show_upgrade_notices'));
    }

    public function add_license_menu() {
        add_submenu_page(
            'pdf-builder-settings',
            'Licence PDF Builder Pro',
            'Licence',
            'manage_options',
            'pdf-builder-license',
            array($this, 'license_page')
        );
    }
}
```

### **2. Page de Gestion de Licence**

Créons une page d'administration pour la gestion des licences :

```php
public function license_page() {
    if (isset($_POST['activate_license'])) {
        $license_key = sanitize_text_field($_POST['license_key']);
        $result = $this->license_manager->activate_license($license_key);

        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . $result['message'] . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . $result['message'] . '</p></div>';
        }
    }

    $license_status = $this->license_manager->get_license_status();
    $is_premium = $this->license_manager->is_premium();

    ?>
    <div class="wrap">
        <h1>Licence PDF Builder Pro</h1>

        <div class="license-status-card">
            <h3>Statut de la Licence</h3>
            <div class="status-indicator <?php echo $license_status; ?>">
                <?php echo ucfirst($license_status); ?>
            </div>

            <?php if (!$is_premium): ?>
                <div class="upgrade-prompt">
                    <h4>🔓 Passez à la version Premium</h4>
                    <p>Débloquez toutes les fonctionnalités avancées !</p>
                    <a href="https://pdfbuilderpro.com/pricing" class="button button-primary" target="_blank">
                        Voir les tarifs
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$is_premium): ?>
        <form method="post" class="license-activation-form">
            <?php wp_nonce_field('activate_license', 'license_nonce'); ?>
            <h3>Activer une Licence Premium</h3>
            <p>
                <label for="license_key">Clé de licence :</label>
                <input type="text" name="license_key" id="license_key" class="regular-text" required>
            </p>
            <p>
                <input type="submit" name="activate_license" class="button button-primary" value="Activer la licence">
            </p>
        </form>
        <?php endif; ?>

        <div class="feature-comparison">
            <h3>Comparaison des Fonctionnalités</h3>
            <?php $this->render_feature_comparison(); ?>
        </div>
    </div>
    <?php
}
```

### **3. Système de Restrictions**

Implémentons des restrictions dans les fonctionnalités clés :

```php
// Dans PDF_Generator.php
public function generate_pdf($template_data) {
    if (!PDF_Builder_Feature_Manager::can_use_feature('pdf_generation')) {
        throw new Exception('Limite de génération PDF atteinte. Passez à Premium pour continuer.');
    }

    // Générer le PDF
    $pdf = $this->create_pdf($template_data);

    // Incrémenter le compteur d'usage
    PDF_Builder_Feature_Manager::increment_usage('pdf_generation');

    return $pdf;
}

// Dans Template_Manager.php
public function get_advanced_templates() {
    if (!PDF_Builder_Feature_Manager::can_use_feature('advanced_templates')) {
        return $this->get_basic_templates_only();
    }

    return $this->get_all_templates();
}
```

### **4. Notifications d'Upgrade**

Ajoutons des notifications contextuelles pour encourager l'upgrade :

```php
public function show_upgrade_notices() {
    if ($this->license_manager->is_premium()) {
        return;
    }

    // Notice générale
    if (!get_option('pdf_builder_upgrade_notice_dismissed')) {
        ?>
        <div class="notice notice-info is-dismissible pdf-builder-upgrade-notice">
            <p>
                <strong>PDF Builder Pro</strong> : Découvrez les fonctionnalités Premium !
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-license'); ?>" class="button button-small">
                    En savoir plus
                </a>
            </p>
        </div>
        <?php
    }

    // Notice de limite atteinte
    $pdf_usage = get_option('pdf_builder_usage_pdf_generation', 0);
    if ($pdf_usage >= 45) { // Alerte à 45/50
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Attention :</strong> Vous avez utilisé <?php echo $pdf_usage; ?>/50 PDFs ce mois-ci.
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-license'); ?>">
                    Passez à Premium pour génération illimitée
                </a>
            </p>
        </div>
        <?php
    }
}
```

---

## 🎨 **INTERFACE UTILISATEUR**

### **1. Badges Premium dans l'Interface**

Ajoutons des indicateurs visuels pour les fonctionnalités premium :

```php
// Dans l'éditeur de template
public function render_element_library() {
    $elements = $this->get_available_elements();

    foreach ($elements as $element) {
        $is_premium = !$element['free'];
        $can_use = PDF_Builder_Feature_Manager::can_use_feature($element['feature_flag']);

        echo '<div class="element-item ' . ($can_use ? '' : 'premium-locked') . '">';

        if ($is_premium && !$can_use) {
            echo '<span class="premium-badge">PREMIUM</span>';
        }

        echo '<div class="element-icon">' . $element['icon'] . '</div>';
        echo '<div class="element-name">' . $element['name'] . '</div>';

        if (!$can_use) {
            echo '<button class="upgrade-button" onclick="showUpgradeModal(\'' . $element['name'] . '\')">Débloquer</button>';
        }

        echo '</div>';
    }
}
```

### **2. Modal d'Upgrade**

Créons une modal attractive pour les upgrades :

```javascript
function showUpgradeModal(featureName) {
    const modal = document.createElement('div');
    modal.className = 'pdf-builder-upgrade-modal';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="closeUpgradeModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Débloquer ${featureName}</h3>
                <span class="modal-close" onclick="closeUpgradeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Cette fonctionnalité est disponible dans la version Premium de PDF Builder Pro.</p>
                <div class="premium-benefits">
                    <h4>🔥 Avantages Premium :</h4>
                    <ul>
                        <li>✅ Génération PDF illimitée</li>
                        <li>✅ Templates avancés</li>
                        <li>✅ Support prioritaire 24/7</li>
                        <li>✅ API développeur complète</li>
                    </ul>
                </div>
                <div class="pricing-info">
                    <div class="price-highlight">
                        <span class="price">€49</span>
                        <span class="period">/an</span>
                    </div>
                    <p class="price-note">Paiement unique, pas d'abonnement</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="button" onclick="closeUpgradeModal()">Plus tard</button>
                <a href="https://pdfbuilderpro.com/pricing" class="button button-primary" target="_blank">
                    Passer à Premium
                </a>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
}
```

---

## 📊 **STRATÉGIE COMMERCIALE**

### **1. Pricing Strategy**

```php
$pricing_tiers = [
    'personal' => [
        'name' => 'Personal',
        'price' => 29,
        'features' => ['basic_templates', 'standard_elements', 'woocommerce_integration'],
        'limit' => 100 // PDFs/mois
    ],
    'professional' => [
        'name' => 'Professional',
        'price' => 79,
        'features' => ['all_personal', 'advanced_templates', 'premium_elements', 'api_access'],
        'limit' => -1 // Illimité
    ],
    'agency' => [
        'name' => 'Agency',
        'price' => 199,
        'features' => ['all_professional', 'white_label', 'multi_site', 'priority_support'],
        'limit' => -1
    ]
];
```

### **2. Conversion Funnel**

1. **Free Trial** : 14 jours complets
2. **Freemium** : Fonctionnalités de base + limites
3. **Soft Gating** : Notifications d'upgrade contextuelles
4. **Hard Gating** : Blocage des fonctionnalités premium
5. **Upselling** : Recommandations basées sur l'usage

### **3. Analytics & Tracking**

```php
class PDF_Builder_Analytics {

    public static function track_feature_usage($feature_name, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $tracking_data = [
            'feature' => $feature_name,
            'user_id' => $user_id,
            'timestamp' => time(),
            'license_status' => PDF_Builder_License_Manager::getInstance()->get_license_status()
        ];

        // Stocker localement pour analytics
        self::store_tracking_data($tracking_data);

        // Envoyer à votre serveur d'analytics (si premium)
        if (PDF_Builder_License_Manager::getInstance()->is_premium()) {
            self::send_to_analytics_server($tracking_data);
        }
    }
}
```

---

## 🚀 **PLAN DE DÉPLOIEMENT**

### **Phase 1 : Infrastructure (1-2 semaines)**
- [ ] Créer `PDF_Builder_License_Manager`
- [ ] Implémenter `PDF_Builder_Feature_Manager`
- [ ] Configurer le serveur de licences
- [ ] Créer la page de gestion de licence

### **Phase 2 : Restrictions (1 semaine)**
- [ ] Identifier les fonctionnalités à restreindre
- [ ] Implémenter les vérifications de licence
- [ ] Ajouter les limites d'usage
- [ ] Créer les messages d'erreur

### **Phase 3 : Interface (1 semaine)**
- [ ] Ajouter les badges "Premium"
- [ ] Créer la modal d'upgrade
- [ ] Implémenter les notifications
- [ ] Styliser l'interface

### **Phase 4 : Testing & Launch (1 semaine)**
- [ ] Tests de régression complets
- [ ] Validation du système de licences
- [ ] Tests des limites d'usage
- [ ] Lancement progressif

---

## 📈 **MÉTRIQUES DE SUCCÈS**

### **Conversion Metrics**
- **Free to Paid Conversion** : Objectif 5-10%
- **Trial to Paid** : Objectif 20-30%
- **Feature-based Conversion** : Tracking par fonctionnalité

### **Usage Metrics**
- **Feature Adoption** : % d'utilisateurs utilisant chaque feature
- **Limit Reach Rate** : % atteignant les limites free
- **Upgrade Triggers** : Efficacité des notifications

### **Business Metrics**
- **Monthly Recurring Revenue** : Suivi des revenus
- **Customer Lifetime Value** : Valeur client moyenne
- **Churn Rate** : Taux d'attrition des abonnements

---

*Cette stratégie freemium transforme votre plugin gratuit en une source de revenus récurrents tout en maintenant une base d'utilisateurs large.*</content>
<parameter name="filePath">g:\wp-pdf-builder-pro\FREEMIUM_STRATEGY.md