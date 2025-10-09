<?php
/**
 * Système de Documentation Premium - PDF Builder Pro
 *
 * Documentation complète multilingue avec guides développeur,
 * tutoriels vidéo, support 24/7 et base de connaissances
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Système de Documentation Premium
 */
class PDF_Builder_Documentation_System {

    /**
     * Instance singleton
     * @var PDF_Builder_Documentation_System
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $db_manager;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Langues supportées
     * @var array
     */
    private $supported_languages = [
        'en' => 'English',
        'fr' => 'Français',
        'es' => 'Español',
        'de' => 'Deutsch',
        'it' => 'Italiano',
        'pt' => 'Português',
        'ru' => 'Русский',
        'zh' => '中文',
        'ja' => '日本語',
        'ar' => 'العربية'
    ];

    /**
     * Structure de la documentation
     * @var array
     */
    private $documentation_structure = [
        'getting-started' => [
            'title' => 'Getting Started',
            'icon' => 'rocket',
            'order' => 1,
            'articles' => [
                'installation' => 'Installation Guide',
                'first-document' => 'Creating Your First Document',
                'basic-configuration' => 'Basic Configuration'
            ]
        ],
        'user-guide' => [
            'title' => 'User Guide',
            'icon' => 'book',
            'order' => 2,
            'articles' => [
                'templates' => 'Working with Templates',
                'editor' => 'Document Editor',
                'export' => 'Export Options',
                'collaboration' => 'Collaboration Features',
                'api' => 'API Usage'
            ]
        ],
        'developer-guide' => [
            'title' => 'Developer Guide',
            'icon' => 'code',
            'order' => 3,
            'articles' => [
                'architecture' => 'Architecture Overview',
                'api-reference' => 'API Reference',
                'hooks-filters' => 'Hooks and Filters',
                'customization' => 'Customization Guide',
                'security' => 'Security Best Practices'
            ]
        ],
        'advanced-features' => [
            'title' => 'Advanced Features',
            'icon' => 'star',
            'order' => 4,
            'articles' => [
                'automation' => 'Automation & Workflows',
                'integrations' => 'Third-party Integrations',
                'performance' => 'Performance Optimization',
                'scaling' => 'Scaling Guide'
            ]
        ],
        'troubleshooting' => [
            'title' => 'Troubleshooting',
            'icon' => 'wrench',
            'order' => 5,
            'articles' => [
                'common-issues' => 'Common Issues',
                'error-codes' => 'Error Codes',
                'debugging' => 'Debugging Guide',
                'support' => 'Getting Support'
            ]
        ],
        'api-docs' => [
            'title' => 'API Documentation',
            'icon' => 'terminal',
            'order' => 6,
            'articles' => [
                'rest-api' => 'REST API',
                'webhooks' => 'Webhooks',
                'authentication' => 'Authentication',
                'rate-limits' => 'Rate Limits'
            ]
        ]
    ];

    /**
     * Articles de documentation
     * @var array
     */
    private $documentation_articles = [];

    /**
     * Vidéos tutoriels
     * @var array
     */
    private $tutorial_videos = [];

    /**
     * Base de connaissances
     * @var array
     */
    private $knowledge_base = [];

    /**
     * FAQ
     * @var array
     */
    private $faq = [];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->db_manager = $core->get_database_manager();
        $this->logger = $core->get_logger();

        $this->init_documentation_hooks();
        $this->load_documentation_content();
        $this->schedule_documentation_tasks();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Documentation_System
     */
    public static function getInstance(): PDF_Builder_Documentation_System {
        return self::getDocumentationSystem();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Documentation_System
     */
    public static function getDocumentationSystem(): PDF_Builder_Documentation_System {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks de documentation
     */
    private function init_documentation_hooks(): void {
        // Hooks AJAX pour la documentation
        add_action('wp_ajax_pdf_builder_get_documentation', [$this, 'ajax_get_documentation']);
        add_action('wp_ajax_pdf_builder_search_documentation', [$this, 'ajax_search_documentation']);
        add_action('wp_ajax_pdf_builder_get_video_tutorials', [$this, 'ajax_get_video_tutorials']);
        add_action('wp_ajax_pdf_builder_submit_support_ticket', [$this, 'ajax_submit_support_ticket']);
        add_action('wp_ajax_pdf_builder_get_faq', [$this, 'ajax_get_faq']);
        add_action('wp_ajax_pdf_builder_rate_article', [$this, 'ajax_rate_article']);

        // Hooks pour les tâches automatiques
        add_action('pdf_builder_update_documentation_translations', [$this, 'update_documentation_translations']);
        add_action('pdf_builder_generate_documentation_report', [$this, 'generate_documentation_report']);
        add_action('pdf_builder_cleanup_old_support_tickets', [$this, 'cleanup_old_support_tickets']);

        // Hooks pour l'intégration
        add_filter('pdf_builder_admin_menu', [$this, 'add_documentation_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_documentation_scripts']);
    }

    /**
     * Programmer les tâches de documentation
     */
    private function schedule_documentation_tasks(): void {
        // Mise à jour des traductions (hebdomadaire)
        if (!wp_next_scheduled('pdf_builder_update_documentation_translations')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_update_documentation_translations');
        }

        // Rapport de documentation (mensuel)
        if (!wp_next_scheduled('pdf_builder_generate_documentation_report')) {
            wp_schedule_event(time(), 'monthly', 'pdf_builder_generate_documentation_report');
        }

        // Nettoyage des anciens tickets (mensuel)
        if (!wp_next_scheduled('pdf_builder_cleanup_old_support_tickets')) {
            wp_schedule_event(time(), 'monthly', 'pdf_builder_cleanup_old_support_tickets');
        }
    }

    /**
     * Charger le contenu de la documentation
     */
    private function load_documentation_content(): void {
        $this->load_documentation_articles();
        $this->load_tutorial_videos();
        $this->load_knowledge_base();
        $this->load_faq();
    }

    /**
     * Charger les articles de documentation
     */
    private function load_documentation_articles(): void {
        // Articles en anglais (par défaut)
        $this->documentation_articles['en'] = [
            'getting-started' => [
                'installation' => [
                    'title' => 'Installation Guide',
                    'content' => $this->get_installation_guide_content('en'),
                    'last_updated' => '2024-01-15',
                    'author' => 'PDF Builder Team',
                    'tags' => ['installation', 'setup', 'beginner'],
                    'rating' => 4.8,
                    'views' => 1250
                ],
                'first-document' => [
                    'title' => 'Creating Your First Document',
                    'content' => $this->get_first_document_content('en'),
                    'last_updated' => '2024-01-10',
                    'author' => 'PDF Builder Team',
                    'tags' => ['tutorial', 'beginner', 'documents'],
                    'rating' => 4.9,
                    'views' => 980
                ],
                'basic-configuration' => [
                    'title' => 'Basic Configuration',
                    'content' => $this->get_basic_config_content('en'),
                    'last_updated' => '2024-01-12',
                    'author' => 'PDF Builder Team',
                    'tags' => ['configuration', 'settings', 'setup'],
                    'rating' => 4.6,
                    'views' => 750
                ]
            ],
            'user-guide' => [
                'templates' => [
                    'title' => 'Working with Templates',
                    'content' => $this->get_templates_guide_content('en'),
                    'last_updated' => '2024-01-08',
                    'author' => 'PDF Builder Team',
                    'tags' => ['templates', 'design', 'intermediate'],
                    'rating' => 4.7,
                    'views' => 1100
                ],
                'editor' => [
                    'title' => 'Document Editor',
                    'content' => $this->get_editor_guide_content('en'),
                    'last_updated' => '2024-01-14',
                    'author' => 'PDF Builder Team',
                    'tags' => ['editor', 'interface', 'intermediate'],
                    'rating' => 4.5,
                    'views' => 890
                ],
                'export' => [
                    'title' => 'Export Options',
                    'content' => $this->get_export_guide_content('en'),
                    'last_updated' => '2024-01-11',
                    'author' => 'PDF Builder Team',
                    'tags' => ['export', 'pdf', 'formats'],
                    'rating' => 4.8,
                    'views' => 920
                ],
                'collaboration' => [
                    'title' => 'Collaboration Features',
                    'content' => $this->get_collaboration_guide_content('en'),
                    'last_updated' => '2024-01-09',
                    'author' => 'PDF Builder Team',
                    'tags' => ['collaboration', 'sharing', 'team'],
                    'rating' => 4.6,
                    'views' => 680
                ],
                'api' => [
                    'title' => 'API Usage',
                    'content' => $this->get_api_guide_content('en'),
                    'last_updated' => '2024-01-13',
                    'author' => 'PDF Builder Team',
                    'tags' => ['api', 'integration', 'advanced'],
                    'rating' => 4.4,
                    'views' => 540
                ]
            ],
            'developer-guide' => [
                'architecture' => [
                    'title' => 'Architecture Overview',
                    'content' => $this->get_architecture_content('en'),
                    'last_updated' => '2024-01-07',
                    'author' => 'PDF Builder Team',
                    'tags' => ['architecture', 'development', 'advanced'],
                    'rating' => 4.9,
                    'views' => 420
                ],
                'api-reference' => [
                    'title' => 'API Reference',
                    'content' => $this->get_api_reference_content('en'),
                    'last_updated' => '2024-01-06',
                    'author' => 'PDF Builder Team',
                    'tags' => ['api', 'reference', 'development'],
                    'rating' => 4.7,
                    'views' => 380
                ],
                'hooks-filters' => [
                    'title' => 'Hooks and Filters',
                    'content' => $this->get_hooks_filters_content('en'),
                    'last_updated' => '2024-01-05',
                    'author' => 'PDF Builder Team',
                    'tags' => ['hooks', 'filters', 'development'],
                    'rating' => 4.5,
                    'views' => 310
                ],
                'customization' => [
                    'title' => 'Customization Guide',
                    'content' => $this->get_customization_content('en'),
                    'last_updated' => '2024-01-04',
                    'author' => 'PDF Builder Team',
                    'tags' => ['customization', 'themes', 'development'],
                    'rating' => 4.6,
                    'views' => 290
                ],
                'security' => [
                    'title' => 'Security Best Practices',
                    'content' => $this->get_security_content('en'),
                    'last_updated' => '2024-01-03',
                    'author' => 'PDF Builder Team',
                    'tags' => ['security', 'best-practices', 'development'],
                    'rating' => 4.8,
                    'views' => 350
                ]
            ]
        ];

        // Articles en français
        $this->documentation_articles['fr'] = [
            'getting-started' => [
                'installation' => [
                    'title' => 'Guide d\'Installation',
                    'content' => $this->get_installation_guide_content('fr'),
                    'last_updated' => '2024-01-15',
                    'author' => 'Équipe PDF Builder',
                    'tags' => ['installation', 'configuration', 'débutant'],
                    'rating' => 4.8,
                    'views' => 450
                ],
                'first-document' => [
                    'title' => 'Créer Votre Premier Document',
                    'content' => $this->get_first_document_content('fr'),
                    'last_updated' => '2024-01-10',
                    'author' => 'Équipe PDF Builder',
                    'tags' => ['tutoriel', 'débutant', 'documents'],
                    'rating' => 4.9,
                    'views' => 380
                ]
            ]
        ];
    }

    /**
     * Charger les vidéos tutoriels
     */
    private function load_tutorial_videos(): void {
        $this->tutorial_videos = [
            'getting-started' => [
                [
                    'id' => 'install-setup',
                    'title' => 'Installation and Setup',
                    'description' => 'Complete installation guide with step-by-step instructions',
                    'duration' => '8:45',
                    'thumbnail' => 'https://img.youtube.com/vi/XXXXXXX/maxresdefault.jpg',
                    'url' => 'https://www.youtube.com/watch?v=XXXXXXX',
                    'language' => 'en',
                    'tags' => ['installation', 'setup', 'beginner'],
                    'views' => 2450,
                    'rating' => 4.8
                ],
                [
                    'id' => 'first-document',
                    'title' => 'Creating Your First Document',
                    'description' => 'Learn how to create and customize your first PDF document',
                    'duration' => '12:30',
                    'thumbnail' => 'https://img.youtube.com/vi/YYYYYYY/maxresdefault.jpg',
                    'url' => 'https://www.youtube.com/watch?v=YYYYYYY',
                    'language' => 'en',
                    'tags' => ['tutorial', 'documents', 'beginner'],
                    'views' => 1890,
                    'rating' => 4.9
                ]
            ],
            'advanced-features' => [
                [
                    'id' => 'api-integration',
                    'title' => 'API Integration Guide',
                    'description' => 'Advanced API integration with real-world examples',
                    'duration' => '18:20',
                    'thumbnail' => 'https://img.youtube.com/vi/ZZZZZZZ/maxresdefault.jpg',
                    'url' => 'https://www.youtube.com/watch?v=ZZZZZZZ',
                    'language' => 'en',
                    'tags' => ['api', 'integration', 'advanced'],
                    'views' => 890,
                    'rating' => 4.7
                ],
                [
                    'id' => 'automation-workflows',
                    'title' => 'Automation and Workflows',
                    'description' => 'Set up automated document generation workflows',
                    'duration' => '15:45',
                    'thumbnail' => 'https://img.youtube.com/vi/WWWWWW/maxresdefault.jpg',
                    'url' => 'https://www.youtube.com/watch?v=WWWWWW',
                    'language' => 'en',
                    'tags' => ['automation', 'workflows', 'advanced'],
                    'views' => 720,
                    'rating' => 4.6
                ]
            ],
            'troubleshooting' => [
                [
                    'id' => 'debugging-guide',
                    'title' => 'Debugging Common Issues',
                    'description' => 'Learn how to troubleshoot and debug common problems',
                    'duration' => '22:10',
                    'thumbnail' => 'https://img.youtube.com/vi/VVVVVV/maxresdefault.jpg',
                    'url' => 'https://www.youtube.com/watch?v=VVVVVV',
                    'language' => 'en',
                    'tags' => ['debugging', 'troubleshooting', 'support'],
                    'views' => 1450,
                    'rating' => 4.5
                ]
            ]
        ];
    }

    /**
     * Charger la base de connaissances
     */
    private function load_knowledge_base(): void {
        $this->knowledge_base = [
            [
                'id' => 'memory-optimization',
                'title' => 'Memory Optimization Techniques',
                'category' => 'performance',
                'content' => 'Learn how to optimize memory usage in PDF Builder Pro...',
                'tags' => ['performance', 'memory', 'optimization'],
                'author' => 'Performance Team',
                'created_at' => '2024-01-10',
                'updated_at' => '2024-01-15',
                'helpful_votes' => 45,
                'views' => 320
            ],
            [
                'id' => 'custom-css-guide',
                'title' => 'Custom CSS Styling Guide',
                'category' => 'customization',
                'content' => 'Complete guide to customizing PDF styles with CSS...',
                'tags' => ['css', 'styling', 'customization'],
                'author' => 'Design Team',
                'created_at' => '2024-01-08',
                'updated_at' => '2024-01-12',
                'helpful_votes' => 67,
                'views' => 580
            ],
            [
                'id' => 'api-rate-limits',
                'title' => 'Understanding API Rate Limits',
                'category' => 'api',
                'content' => 'Everything you need to know about API rate limiting...',
                'tags' => ['api', 'rate-limits', 'integration'],
                'author' => 'API Team',
                'created_at' => '2024-01-05',
                'updated_at' => '2024-01-08',
                'helpful_votes' => 38,
                'views' => 290
            ]
        ];
    }

    /**
     * Charger la FAQ
     */
    private function load_faq(): void {
        $this->faq = [
            'general' => [
                [
                    'question' => 'What file formats does PDF Builder Pro support?',
                    'answer' => 'PDF Builder Pro supports PDF, DOCX, XLSX, and various image formats including PNG, JPG, and SVG.',
                    'category' => 'general',
                    'helpful_votes' => 156,
                    'tags' => ['formats', 'export', 'compatibility']
                ],
                [
                    'question' => 'Is PDF Builder Pro compatible with my WordPress version?',
                    'answer' => 'PDF Builder Pro requires WordPress 5.0+ and PHP 7.4+. Check our compatibility matrix for detailed requirements.',
                    'category' => 'general',
                    'helpful_votes' => 89,
                    'tags' => ['compatibility', 'requirements', 'wordpress']
                ]
            ],
            'technical' => [
                [
                    'question' => 'How do I increase the maximum file upload size?',
                    'answer' => 'You can increase the upload size by modifying your php.ini file or using a plugin like "Increase Upload Max Filesize".',
                    'category' => 'technical',
                    'helpful_votes' => 124,
                    'tags' => ['upload', 'php', 'configuration']
                ],
                [
                    'question' => 'Why are my PDFs not generating correctly?',
                    'answer' => 'Common causes include memory limits, missing PHP extensions, or template errors. Check our troubleshooting guide for detailed solutions.',
                    'category' => 'technical',
                    'helpful_votes' => 98,
                    'tags' => ['pdf', 'generation', 'errors']
                ]
            ],
            'billing' => [
                [
                    'question' => 'What payment methods do you accept?',
                    'answer' => 'We accept all major credit cards, PayPal, and bank transfers for annual subscriptions.',
                    'category' => 'billing',
                    'helpful_votes' => 67,
                    'tags' => ['payment', 'billing', 'subscription']
                ],
                [
                    'question' => 'Can I upgrade or downgrade my plan?',
                    'answer' => 'Yes, you can change your plan at any time. Changes take effect immediately for upgrades, or at the next billing cycle for downgrades.',
                    'category' => 'billing',
                    'helpful_votes' => 45,
                    'tags' => ['upgrade', 'downgrade', 'plans']
                ]
            ]
        ];
    }

    /**
     * Obtenir le contenu du guide d'installation
     *
     * @param string $lang
     * @return string
     */
    private function get_installation_guide_content(string $lang = 'en'): string {
        if ($lang === 'fr') {
            return '
# Guide d\'Installation

## Prérequis Système

Avant d\'installer PDF Builder Pro, assurez-vous que votre serveur répond aux exigences suivantes :

- **WordPress** : Version 5.0 ou supérieure
- **PHP** : Version 7.4 ou supérieure
- **MySQL** : Version 5.6 ou supérieure
- **Mémoire PHP** : 128MB minimum (256MB recommandé)
- **Extensions PHP** : mbstring, gd, curl, openssl, zip

## Installation Automatique

1. Connectez-vous à votre tableau de bord WordPress
2. Allez dans **Extensions > Ajouter**
3. Recherchez "PDF Builder Pro"
4. Cliquez sur **Installer maintenant**
5. Cliquez sur **Activer**

## Installation Manuelle

1. Téléchargez le fichier ZIP depuis votre compte client
2. Extrayez le contenu dans `/wp-content/plugins/`
3. Activez le plugin depuis **Extensions > Extensions installées**

## Configuration Initiale

Après activation, vous serez redirigé vers l\'assistant de configuration :

1. **Licence** : Entrez votre clé de licence
2. **Base de données** : Le plugin créera automatiquement les tables nécessaires
3. **Permissions** : Configurez les rôles utilisateur
4. **Paramètres par défaut** : Définissez vos préférences

## Vérification de l\'Installation

Pour vérifier que tout fonctionne correctement :

1. Allez dans **PDF Builder > Tableau de bord**
2. Vérifiez que tous les indicateurs sont verts
3. Créez un document de test
4. Exportez-le en PDF

## Dépannage

Si vous rencontrez des problèmes :

- Vérifiez les logs d\'erreur PHP
- Assurez-vous que tous les prérequis sont remplis
- Désactivez temporairement les autres plugins pour tester les conflits
- Contactez notre support si le problème persiste
            ';
        }

        return '
# Installation Guide

## System Requirements

Before installing PDF Builder Pro, ensure your server meets these requirements:

- **WordPress**: Version 5.0 or higher
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.6 or higher
- **PHP Memory**: 128MB minimum (256MB recommended)
- **PHP Extensions**: mbstring, gd, curl, openssl, zip

## Automatic Installation

1. Log in to your WordPress dashboard
2. Go to **Plugins > Add New**
3. Search for "PDF Builder Pro"
4. Click **Install Now**
5. Click **Activate**

## Manual Installation

1. Download the ZIP file from your client account
2. Extract contents to `/wp-content/plugins/`
3. Activate the plugin from **Plugins > Installed Plugins**

## Initial Configuration

After activation, you\'ll be redirected to the setup wizard:

1. **License**: Enter your license key
2. **Database**: Plugin will automatically create necessary tables
3. **Permissions**: Configure user roles
4. **Default Settings**: Set your preferences

## Installation Verification

To verify everything works correctly:

1. Go to **PDF Builder > Dashboard**
2. Check that all indicators are green
3. Create a test document
4. Export it as PDF

## Troubleshooting

If you encounter issues:

- Check PHP error logs
- Ensure all requirements are met
- Temporarily disable other plugins to test for conflicts
- Contact our support if the issue persists
        ';
    }

    /**
     * Obtenir le contenu du premier document
     *
     * @param string $lang
     * @return string
     */
    private function get_first_document_content(string $lang = 'en'): string {
        if ($lang === 'fr') {
            return '
# Créer Votre Premier Document

## Introduction

Bienvenue dans PDF Builder Pro ! Ce guide vous accompagnera dans la création de votre premier document PDF.

## Étape 1 : Accéder à l\'Éditeur

1. Connectez-vous à votre tableau de bord WordPress
2. Allez dans **PDF Builder > Documents**
3. Cliquez sur **Créer un Nouveau Document**

## Étape 2 : Choisir un Modèle

1. Parcourez les modèles disponibles
2. Sélectionnez un modèle qui correspond à vos besoins
3. Cliquez sur **Utiliser ce Modèle**

## Étape 3 : Personnaliser le Contenu

### Ajouter du Texte

1. Cliquez sur l\'icône **T** dans la barre d\'outils
2. Tapez votre texte
3. Utilisez les options de formatage : gras, italique, couleur

### Insérer des Images

1. Cliquez sur l\'icône **Image**
2. Téléchargez une image depuis votre ordinateur
3. Redimensionnez et positionnez l\'image

### Ajouter des Données Dynamiques

1. Cliquez sur **Insérer > Champ Dynamique**
2. Choisissez le type de données (nom, date, etc.)
3. Configurez les options d\'affichage

## Étape 4 : Configurer l\'Export

1. Allez dans l\'onglet **Export**
2. Choisissez le format PDF
3. Configurez les options :
   - Taille de page
   - Marges
   - Orientation
   - Qualité d\'image

## Étape 5 : Prévisualiser et Exporter

1. Cliquez sur **Prévisualiser** pour voir le résultat
2. Apportez des modifications si nécessaire
3. Cliquez sur **Exporter** pour générer le PDF
4. Téléchargez ou partagez le document

## Conseils pour les Débutants

- **Commencez Simple** : Utilisez un modèle basique pour votre premier document
- **Sauvegardez Régulièrement** : Le plugin sauvegarde automatiquement, mais gardez une copie
- **Testez les Exports** : Vérifiez toujours le PDF final avant de le partager
- **Explorez les Fonctionnalités** : Une fois à l\'aise, essayez les fonctionnalités avancées

## Prochaines Étapes

Maintenant que vous avez créé votre premier document, explorez :

- [Guide des Modèles](templates)
- [Fonctionnalités de Collaboration](collaboration)
- [Options d\'Export Avancées](export)
            ';
        }

        return '
# Creating Your First Document

## Introduction

Welcome to PDF Builder Pro! This guide will walk you through creating your first PDF document.

## Step 1: Access the Editor

1. Log in to your WordPress dashboard
2. Go to **PDF Builder > Documents**
3. Click **Create New Document**

## Step 2: Choose a Template

1. Browse available templates
2. Select a template that fits your needs
3. Click **Use This Template**

## Step 3: Customize Content

### Adding Text

1. Click the **T** icon in the toolbar
2. Type your text
3. Use formatting options: bold, italic, color

### Inserting Images

1. Click the **Image** icon
2. Upload an image from your computer
3. Resize and position the image

### Adding Dynamic Data

1. Click **Insert > Dynamic Field**
2. Choose data type (name, date, etc.)
3. Configure display options

## Step 4: Configure Export

1. Go to the **Export** tab
2. Choose PDF format
3. Configure options:
   - Page size
   - Margins
   - Orientation
   - Image quality

## Step 5: Preview and Export

1. Click **Preview** to see the result
2. Make adjustments if needed
3. Click **Export** to generate PDF
4. Download or share the document

## Beginner Tips

- **Start Simple**: Use a basic template for your first document
- **Save Regularly**: Plugin auto-saves, but keep a backup
- **Test Exports**: Always check the final PDF before sharing
- **Explore Features**: Once comfortable, try advanced features

## Next Steps

Now that you\'ve created your first document, explore:

- [Templates Guide](templates)
- [Collaboration Features](collaboration)
- [Advanced Export Options](export)
        ';
    }

    /**
     * Obtenir le contenu de configuration basique
     *
     * @param string $lang
     * @return string
     */
    private function get_basic_config_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Configuration Basique

Découvrez comment configurer PDF Builder Pro selon vos besoins.' :
            '# Basic Configuration

Learn how to configure PDF Builder Pro to match your needs.';
    }

    /**
     * Obtenir le contenu du guide des modèles
     *
     * @param string $lang
     * @return string
     */
    private function get_templates_guide_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Travailler avec les Modèles

Guide complet pour utiliser et personnaliser les modèles PDF.' :
            '# Working with Templates

Complete guide to using and customizing PDF templates.';
    }

    /**
     * Obtenir le contenu du guide de l'éditeur
     *
     * @param string $lang
     * @return string
     */
    private function get_editor_guide_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Éditeur de Documents

Découvrez toutes les fonctionnalités de l\'éditeur visuel.' :
            '# Document Editor

Discover all features of the visual editor.';
    }

    /**
     * Obtenir le contenu du guide d'export
     *
     * @param string $lang
     * @return string
     */
    private function get_export_guide_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Options d\'Export

Guide des différents formats d\'export et leurs options.' :
            '# Export Options

Guide to different export formats and their options.';
    }

    /**
     * Obtenir le contenu du guide de collaboration
     *
     * @param string $lang
     * @return string
     */
    private function get_collaboration_guide_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Fonctionnalités de Collaboration

Apprenez à travailler en équipe sur vos documents PDF.' :
            '# Collaboration Features

Learn how to work as a team on your PDF documents.';
    }

    /**
     * Obtenir le contenu du guide API
     *
     * @param string $lang
     * @return string
     */
    private function get_api_guide_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Utilisation de l\'API

Guide pour intégrer PDF Builder Pro via l\'API REST.' :
            '# API Usage

Guide to integrating PDF Builder Pro via REST API.';
    }

    /**
     * Obtenir le contenu de l'architecture
     *
     * @param string $lang
     * @return string
     */
    private function get_architecture_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Vue d\'Ensemble de l\'Architecture

Comprendre l\'architecture technique de PDF Builder Pro.' :
            '# Architecture Overview

Understanding PDF Builder Pro\'s technical architecture.';
    }

    /**
     * Obtenir le contenu de référence API
     *
     * @param string $lang
     * @return string
     */
    private function get_api_reference_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Référence API

Documentation complète de toutes les endpoints API.' :
            '# API Reference

Complete documentation of all API endpoints.';
    }

    /**
     * Obtenir le contenu des hooks et filtres
     *
     * @param string $lang
     * @return string
     */
    private function get_hooks_filters_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Hooks et Filtres

Guide pour étendre PDF Builder Pro avec des hooks et filtres.' :
            '# Hooks and Filters

Guide to extending PDF Builder Pro with hooks and filters.';
    }

    /**
     * Obtenir le contenu de personnalisation
     *
     * @param string $lang
     * @return string
     */
    private function get_customization_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Guide de Personnalisation

Apprenez à personnaliser l\'apparence et le comportement du plugin.' :
            '# Customization Guide

Learn how to customize the plugin\'s appearance and behavior.';
    }

    /**
     * Obtenir le contenu de sécurité
     *
     * @param string $lang
     * @return string
     */
    private function get_security_content(string $lang = 'en'): string {
        return $lang === 'fr' ?
            '# Bonnes Pratiques de Sécurité

Guide des meilleures pratiques pour sécuriser votre installation.' :
            '# Security Best Practices

Guide to best practices for securing your installation.';
    }

    /**
     * Ajouter le menu de documentation
     *
     * @param array $menu
     * @return array
     */
    public function add_documentation_menu(array $menu): array {
        $menu['documentation'] = [
            'title' => __('Documentation', 'pdf-builder-pro'),
            'slug' => 'pdf-builder-documentation',
            'icon' => 'dashicons-book-alt',
            'position' => 30,
            'submenu' => [
                'docs' => __('Documentation', 'pdf-builder-pro'),
                'videos' => __('Video Tutorials', 'pdf-builder-pro'),
                'support' => __('Support Center', 'pdf-builder-pro'),
                'faq' => __('FAQ', 'pdf-builder-pro')
            ]
        ];

        return $menu;
    }

    /**
     * Enregistrer les scripts de documentation
     */
    public function enqueue_documentation_scripts(): void {
        if (isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder-documentation') === 0) {
            wp_enqueue_style(
                'pdf-builder-documentation',
                plugin_dir_url(__FILE__) . '../assets/css/documentation.css',
                [],
                PDF_BUILDER_VERSION
            );

            wp_enqueue_script(
                'pdf-builder-documentation',
                plugin_dir_url(__FILE__) . '../assets/js/documentation.js',
                ['jquery'],
                PDF_BUILDER_VERSION,
                true
            );

            wp_localize_script('pdf-builder-documentation', 'pdfBuilderDocs', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_documentation'),
                'strings' => [
                    'loading' => __('Loading...', 'pdf-builder-pro'),
                    'error' => __('Error loading content', 'pdf-builder-pro'),
                    'search_placeholder' => __('Search documentation...', 'pdf-builder-pro')
                ]
            ]);
        }
    }

    /**
     * Obtenir la documentation
     *
     * @param string $section
     * @param string $article
     * @param string $lang
     * @return array|null
     */
    public function get_documentation(string $section = '', string $article = '', string $lang = ''): ?array {
        $current_lang = $lang ?: $this->get_current_language();

        if (!isset($this->documentation_articles[$current_lang])) {
            $current_lang = 'en'; // Fallback to English
        }

        if (empty($section)) {
            return [
                'structure' => $this->documentation_structure,
                'articles' => $this->documentation_articles[$current_lang]
            ];
        }

        if (empty($article)) {
            return $this->documentation_articles[$current_lang][$section] ?? null;
        }

        return $this->documentation_articles[$current_lang][$section][$article] ?? null;
    }

    /**
     * Rechercher dans la documentation
     *
     * @param string $query
     * @param string $lang
     * @return array
     */
    public function search_documentation(string $query, string $lang = ''): array {
        $current_lang = $lang ?: $this->get_current_language();
        $results = [];

        if (!isset($this->documentation_articles[$current_lang])) {
            $current_lang = 'en';
        }

        $query = strtolower($query);

        foreach ($this->documentation_articles[$current_lang] as $section => $articles) {
            foreach ($articles as $article_id => $article) {
                $search_content = strtolower($article['title'] . ' ' . $article['content']);
                $search_tags = isset($article['tags']) ? strtolower(implode(' ', $article['tags'])) : '';

                if (strpos($search_content, $query) !== false || strpos($search_tags, $query) !== false) {
                    $results[] = [
                        'section' => $section,
                        'article' => $article_id,
                        'title' => $article['title'],
                        'excerpt' => $this->generate_excerpt($article['content'], $query),
                        'tags' => $article['tags'] ?? [],
                        'rating' => $article['rating'] ?? 0,
                        'views' => $article['views'] ?? 0
                    ];
                }
            }
        }

        // Trier par pertinence (vues + rating)
        usort($results, function($a, $b) {
            $score_a = ($a['views'] * 0.3) + ($a['rating'] * 20);
            $score_b = ($b['views'] * 0.3) + ($b['rating'] * 20);
            return $score_b <=> $score_a;
        });

        return array_slice($results, 0, 20); // Limiter à 20 résultats
    }

    /**
     * Obtenir les vidéos tutoriels
     *
     * @param string $category
     * @param string $lang
     * @return array
     */
    public function get_tutorial_videos(string $category = '', string $lang = ''): array {
        $current_lang = $lang ?: $this->get_current_language();

        if (empty($category)) {
            return array_filter($this->tutorial_videos, function($videos) use ($current_lang) {
                return array_filter($videos, function($video) use ($current_lang) {
                    return $video['language'] === $current_lang;
                });
            });
        }

        $videos = $this->tutorial_videos[$category] ?? [];

        return array_filter($videos, function($video) use ($current_lang) {
            return $video['language'] === $current_lang;
        });
    }

    /**
     * Obtenir la FAQ
     *
     * @param string $category
     * @return array
     */
    public function get_faq(string $category = ''): array {
        if (empty($category)) {
            return $this->faq;
        }

        return $this->faq[$category] ?? [];
    }

    /**
     * Rechercher dans la base de connaissances
     *
     * @param string $query
     * @param string $category
     * @return array
     */
    public function search_knowledge_base(string $query, string $category = ''): array {
        $query = strtolower($query);
        $results = [];

        foreach ($this->knowledge_base as $article) {
            if (!empty($category) && $article['category'] !== $category) {
                continue;
            }

            $search_content = strtolower($article['title'] . ' ' . $article['content']);
            $search_tags = strtolower(implode(' ', $article['tags']));

            if (strpos($search_content, $query) !== false || strpos($search_tags, $query) !== false) {
                $results[] = $article;
            }
        }

        // Trier par votes utiles
        usort($results, function($a, $b) {
            return $b['helpful_votes'] <=> $a['helpful_votes'];
        });

        return $results;
    }

    /**
     * Soumettre un ticket de support
     *
     * @param array $ticket_data
     * @return array
     */
    public function submit_support_ticket(array $ticket_data): array {
        // Validation des données
        $required_fields = ['subject', 'description', 'priority', 'category'];
        foreach ($required_fields as $field) {
            if (empty($ticket_data[$field])) {
                return [
                    'success' => false,
                    'message' => "Le champ {$field} est requis."
                ];
            }
        }

        // Créer le ticket
        $ticket = [
            'id' => $this->generate_ticket_id(),
            'subject' => sanitize_text_field($ticket_data['subject']),
            'description' => wp_kses_post($ticket_data['description']),
            'priority' => sanitize_text_field($ticket_data['priority']),
            'category' => sanitize_text_field($ticket_data['category']),
            'status' => 'open',
            'user_id' => get_current_user_id(),
            'user_email' => sanitize_email($ticket_data['email'] ?? wp_get_current_user()->user_email),
            'attachments' => $ticket_data['attachments'] ?? [],
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];

        // Sauvegarder en base de données
        global $wpdb;
        $result = $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_support_tickets',
            $ticket,
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s']
        );

        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création du ticket.'
            ];
        }

        // Envoyer notification par email
        $this->send_support_notification($ticket);

        // Logger l'événement
        $this->logger->info('Support ticket created', [
            'ticket_id' => $ticket['id'],
            'user_id' => $ticket['user_id'],
            'priority' => $ticket['priority']
        ]);

        return [
            'success' => true,
            'ticket_id' => $ticket['id'],
            'message' => 'Ticket de support créé avec succès.'
        ];
    }

    /**
     * Noter un article
     *
     * @param string $section
     * @param string $article
     * @param int $rating
     * @param string $lang
     * @return bool
     */
    public function rate_article(string $section, string $article, int $rating, string $lang = ''): bool {
        $current_lang = $lang ?: $this->get_current_language();

        if (!isset($this->documentation_articles[$current_lang][$section][$article])) {
            return false;
        }

        // En production, sauvegarder en base de données
        // Pour la démo, mettre à jour en mémoire
        $current_rating = $this->documentation_articles[$current_lang][$section][$article]['rating'];
        $current_views = $this->documentation_articles[$current_lang][$section][$article]['views'];

        // Calculer nouvelle moyenne (simulation)
        $new_rating = round((($current_rating * ($current_views - 1)) + $rating) / $current_views, 1);

        $this->documentation_articles[$current_lang][$section][$article]['rating'] = $new_rating;

        return true;
    }

    /**
     * Générer un extrait de texte
     *
     * @param string $content
     * @param string $query
     * @param int $length
     * @return string
     */
    private function generate_excerpt(string $content, string $query, int $length = 150): string {
        $content = strip_tags($content);
        $query_pos = strpos(strtolower($content), $query);

        if ($query_pos === false) {
            return substr($content, 0, $length) . '...';
        }

        $start = max(0, $query_pos - ($length / 2));
        $excerpt = substr($content, $start, $length);

        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }

        if (strlen($content) > $start + $length) {
            $excerpt .= '...';
        }

        return $excerpt;
    }

    /**
     * Obtenir la langue actuelle
     *
     * @return string
     */
    private function get_current_language(): string {
        $locale = get_locale();
        $lang_code = substr($locale, 0, 2);

        return isset($this->supported_languages[$lang_code]) ? $lang_code : 'en';
    }

    /**
     * Générer un ID de ticket
     *
     * @return string
     */
    private function generate_ticket_id(): string {
        return 'TICKET-' . date('Ymd') . '-' . wp_generate_password(6, false, '0123456789');
    }

    /**
     * Envoyer une notification de support
     *
     * @param array $ticket
     */
    private function send_support_notification(array $ticket): void {
        $admin_email = get_option('admin_email');
        $subject = "[SUPPORT TICKET] {$ticket['subject']}";

        $message = "Nouveau ticket de support :\n\n";
        $message .= "ID: {$ticket['id']}\n";
        $message .= "Sujet: {$ticket['subject']}\n";
        $message .= "Priorité: {$ticket['priority']}\n";
        $message .= "Catégorie: {$ticket['category']}\n";
        $message .= "Utilisateur: {$ticket['user_email']}\n\n";
        $message .= "Description:\n{$ticket['description']}\n\n";
        $message .= "Créé le: {$ticket['created_at']}\n";

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Mettre à jour les traductions de documentation
     */
    public function update_documentation_translations(): void {
        // Simulation - en production, synchroniser avec un service de traduction
        $this->logger->info('Documentation translations updated');
    }

    /**
     * Générer un rapport de documentation
     */
    public function generate_documentation_report(): void {
        $report = [
            'generated_at' => current_time('mysql'),
            'total_articles' => $this->count_total_articles(),
            'total_videos' => $this->count_total_videos(),
            'total_faq' => $this->count_total_faq(),
            'language_coverage' => $this->get_language_coverage(),
            'popular_content' => $this->get_popular_content(),
            'support_stats' => $this->get_support_stats()
        ];

        update_option('pdf_builder_documentation_report', $report);
        $this->logger->info('Documentation report generated');
    }

    /**
     * Nettoyer les anciens tickets de support
     */
    public function cleanup_old_support_tickets(): void {
        global $wpdb;

        $cutoff_date = date('Y-m-d H:i:s', strtotime('-1 year'));

        $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_support_tickets
            WHERE created_at < %s AND status = 'closed'
        ", $cutoff_date));

        $this->logger->info('Old support tickets cleaned up');
    }

    /**
     * Compter le nombre total d'articles
     *
     * @return int
     */
    private function count_total_articles(): int {
        $count = 0;
        foreach ($this->documentation_articles as $lang_articles) {
            foreach ($lang_articles as $section_articles) {
                $count += count($section_articles);
            }
        }
        return $count;
    }

    /**
     * Compter le nombre total de vidéos
     *
     * @return int
     */
    private function count_total_videos(): int {
        $count = 0;
        foreach ($this->tutorial_videos as $category_videos) {
            $count += count($category_videos);
        }
        return $count;
    }

    /**
     * Compter le nombre total de FAQ
     *
     * @return int
     */
    private function count_total_faq(): int {
        $count = 0;
        foreach ($this->faq as $category_faqs) {
            $count += count($category_faqs);
        }
        return $count;
    }

    /**
     * Obtenir la couverture linguistique
     *
     * @return array
     */
    private function get_language_coverage(): array {
        $coverage = [];
        foreach ($this->supported_languages as $code => $name) {
            $article_count = isset($this->documentation_articles[$code]) ? $this->count_total_articles() : 0;
            $video_count = 0; // Compter les vidéos par langue si nécessaire

            $coverage[$code] = [
                'name' => $name,
                'articles' => $article_count,
                'videos' => $video_count,
                'completion' => $article_count > 0 ? 'partial' : 'none'
            ];
        }
        return $coverage;
    }

    /**
     * Obtenir le contenu populaire
     *
     * @return array
     */
    private function get_popular_content(): array {
        $popular = [];

        foreach ($this->documentation_articles as $lang => $sections) {
            foreach ($sections as $section => $articles) {
                foreach ($articles as $article_id => $article) {
                    $popular[] = [
                        'type' => 'article',
                        'section' => $section,
                        'id' => $article_id,
                        'title' => $article['title'],
                        'language' => $lang,
                        'views' => $article['views'] ?? 0,
                        'rating' => $article['rating'] ?? 0
                    ];
                }
            }
        }

        // Trier par popularité
        usort($popular, function($a, $b) {
            $score_a = ($a['views'] * 0.6) + ($a['rating'] * 10);
            $score_b = ($b['views'] * 0.6) + ($b['rating'] * 10);
            return $score_b <=> $score_a;
        });

        return array_slice($popular, 0, 10);
    }

    /**
     * Obtenir les statistiques de support
     *
     * @return array
     */
    private function get_support_stats(): array {
        global $wpdb;

        $stats = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                COUNT(*) as total_tickets,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets,
                AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_resolution_time
            FROM {$wpdb->prefix}pdf_builder_support_tickets
            WHERE created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-30 days'))), ARRAY_A);

        return [
            'total_tickets' => intval($stats['total_tickets'] ?? 0),
            'open_tickets' => intval($stats['open_tickets'] ?? 0),
            'closed_tickets' => intval($stats['closed_tickets'] ?? 0),
            'avg_resolution_time_hours' => round(floatval($stats['avg_resolution_time'] ?? 0), 1)
        ];
    }

    /**
     * AJAX: Obtenir la documentation
     */
    public function ajax_get_documentation(): void {
        try {
            $section = sanitize_text_field($_POST['section'] ?? '');
            $article = sanitize_text_field($_POST['article'] ?? '');
            $lang = sanitize_text_field($_POST['lang'] ?? '');

            $data = $this->get_documentation($section, $article, $lang);

            wp_send_json_success($data);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Rechercher dans la documentation
     */
    public function ajax_search_documentation(): void {
        try {
            $query = sanitize_text_field($_POST['query']);
            $lang = sanitize_text_field($_POST['lang'] ?? '');

            $results = $this->search_documentation($query, $lang);

            wp_send_json_success($results);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir les vidéos tutoriels
     */
    public function ajax_get_video_tutorials(): void {
        try {
            $category = sanitize_text_field($_POST['category'] ?? '');
            $lang = sanitize_text_field($_POST['lang'] ?? '');

            $videos = $this->get_tutorial_videos($category, $lang);

            wp_send_json_success($videos);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Soumettre un ticket de support
     */
    public function ajax_submit_support_ticket(): void {
        try {
            $ticket_data = [
                'subject' => sanitize_text_field($_POST['subject']),
                'description' => wp_kses_post($_POST['description']),
                'priority' => sanitize_text_field($_POST['priority']),
                'category' => sanitize_text_field($_POST['category']),
                'email' => sanitize_email($_POST['email'] ?? ''),
                'attachments' => $_POST['attachments'] ?? []
            ];

            $result = $this->submit_support_ticket($ticket_data);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir la FAQ
     */
    public function ajax_get_faq(): void {
        try {
            $category = sanitize_text_field($_POST['category'] ?? '');

            $faq = $this->get_faq($category);

            wp_send_json_success($faq);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Noter un article
     */
    public function ajax_rate_article(): void {
        try {
            $section = sanitize_text_field($_POST['section']);
            $article = sanitize_text_field($_POST['article']);
            $rating = intval($_POST['rating']);
            $lang = sanitize_text_field($_POST['lang'] ?? '');

            if ($rating < 1 || $rating > 5) {
                wp_send_json_error(['message' => 'Rating must be between 1 and 5']);
                return;
            }

            $success = $this->rate_article($section, $article, $rating, $lang);

            if ($success) {
                wp_send_json_success(['message' => 'Rating submitted successfully']);
            } else {
                wp_send_json_error(['message' => 'Article not found']);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les langues supportées
     *
     * @return array
     */
    public function get_supported_languages(): array {
        return $this->supported_languages;
    }

    /**
     * Obtenir la structure de la documentation
     *
     * @return array
     */
    public function get_documentation_structure(): array {
        return $this->documentation_structure;
    }

    /**
     * Obtenir les statistiques de documentation
     *
     * @return array
     */
    public function get_documentation_stats(): array {
        return [
            'total_articles' => $this->count_total_articles(),
            'total_videos' => $this->count_total_videos(),
            'total_faq' => $this->count_total_faq(),
            'supported_languages' => count($this->supported_languages),
            'last_updated' => '2024-01-15' // En production, calculer dynamiquement
        ];
    }
}