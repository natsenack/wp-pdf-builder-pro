<?php
/**
 * Gestionnaire de Templates Intelligents - PDF Builder Pro
 *
 * Système de templates dynamiques avec logique avancée
 * Inspiré de l'architecture de woo-pdf-invoice-builder
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Templates Intelligents
 */
class PDF_Builder_Template_Intelligence_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Template_Intelligence_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $database;

    /**
     * Cache manager
     * @var PDF_Builder_Cache_Manager
     */
    private $cache;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Bibliothèque de templates prédéfinis
     * @var array
     */
    private $template_library = [
        'invoice' => [
            'name' => 'Facture Professionnelle',
            'description' => 'Template facture avec en-tête, tableau des articles, et pied de page',
            'category' => 'business',
            'variables' => ['order_number', 'customer_name', 'order_date', 'order_total', 'order_items_table'],
            'preview_image' => 'invoice-template.jpg'
        ],
        'quote' => [
            'name' => 'Devis Moderne',
            'description' => 'Template devis avec design moderne et conditions',
            'category' => 'business',
            'variables' => ['quote_number', 'customer_name', 'valid_until', 'quote_total', 'quote_items_table'],
            'preview_image' => 'quote-template.jpg'
        ],
        'receipt' => [
            'name' => 'Reçu Simple',
            'description' => 'Template reçu minimaliste pour confirmations de paiement',
            'category' => 'business',
            'variables' => ['receipt_number', 'customer_name', 'payment_date', 'amount_paid', 'payment_method'],
            'preview_image' => 'receipt-template.jpg'
        ],
        'certificate' => [
            'name' => 'Certificat',
            'description' => 'Template certificat avec design élégant',
            'category' => 'personal',
            'variables' => ['certificate_title', 'recipient_name', 'issue_date', 'issuer_name', 'certificate_text'],
            'preview_image' => 'certificate-template.jpg'
        ],
        'newsletter' => [
            'name' => 'Newsletter',
            'description' => 'Template newsletter responsive',
            'category' => 'marketing',
            'variables' => ['newsletter_title', 'content_blocks', 'unsubscribe_link', 'social_links'],
            'preview_image' => 'newsletter-template.jpg'
        ]
    ];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->database = $core->get_database_manager();
        $this->cache = $core->get_cache_manager();
        $this->logger = $core->get_logger();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Template_Intelligence_Manager
     */
    public static function getInstance(): PDF_Builder_Template_Intelligence_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Analyser l'usage des templates pour recommandations
     *
     * @return array
     */
    public function analyze_template_usage(): array {
        $cache_key = 'template_usage_analysis';
        $analysis = $this->cache->get($cache_key);

        if ($analysis === false) {
            global $wpdb;

            // Analyser les templates les plus utilisés
            $popular_templates = $wpdb->get_results("
                SELECT t.id, t.name, t.category, COUNT(d.id) as usage_count,
                       AVG(d.created_at) as avg_creation_time
                FROM {$wpdb->prefix}pdf_builder_templates t
                LEFT JOIN {$wpdb->prefix}pdf_builder_documents d ON t.id = d.template_id
                WHERE t.status = 'active'
                GROUP BY t.id, t.name, t.category
                ORDER BY usage_count DESC
                LIMIT 10
            ");

            // Analyser les patterns d'usage par catégorie
            $category_usage = $wpdb->get_results("
                SELECT category, COUNT(*) as count,
                       AVG(created_at) as avg_created_at
                FROM {$wpdb->prefix}pdf_builder_templates
                WHERE status = 'active'
                GROUP BY category
            ");

            $analysis = [
                'popular_templates' => $popular_templates,
                'category_usage' => $category_usage,
                'total_templates' => count($popular_templates),
                'analyzed_at' => current_time('mysql')
            ];

            $this->cache->set($cache_key, $analysis, 3600); // Cache 1 heure
        }

        return $analysis;
    }

    /**
     * Recommander des templates basés sur l'usage historique
     *
     * @param string $context
     * @return array
     */
    public function recommend_templates(string $context = 'general'): array {
        $analysis = $this->analyze_template_usage();

        $recommendations = [];

        switch ($context) {
            case 'business':
                // Prioriser les templates business
                $business_templates = array_filter($analysis['popular_templates'],
                    fn($t) => in_array($t->category, ['business', 'invoice', 'quote']));
                $recommendations = array_slice($business_templates, 0, 3);
                break;

            case 'marketing':
                // Templates marketing
                $marketing_templates = array_filter($analysis['popular_templates'],
                    fn($t) => in_array($t->category, ['marketing', 'newsletter']));
                $recommendations = array_slice($marketing_templates, 0, 3);
                break;

            default:
                // Templates populaires généraux
                $recommendations = array_slice($analysis['popular_templates'], 0, 5);
                break;
        }

        // Si pas assez de recommandations, ajouter des templates prédéfinis
        if (count($recommendations) < 3) {
            $library_templates = $this->get_template_library($context);
            $recommendations = array_merge($recommendations,
                array_slice($library_templates, 0, 3 - count($recommendations)));
        }

        return $recommendations;
    }

    /**
     * Obtenir la bibliothèque de templates prédéfinis
     *
     * @param string $category
     * @return array
     */
    public function get_template_library(string $category = 'all'): array {
        if ($category === 'all') {
            return $this->template_library;
        }

        return array_filter($this->template_library,
            fn($template) => $template['category'] === $category);
    }

    /**
     * Installer un template prédéfini
     *
     * @param string $template_key
     * @param array $customizations
     * @return int|false
     */
    public function install_template(string $template_key, array $customizations = []): int|false {
        if (!isset($this->template_library[$template_key])) {
            return false;
        }

        $template_data = $this->template_library[$template_key];

        // Générer le contenu HTML du template
        $html_content = $this->generate_template_html($template_key, $customizations);

        // Créer le template dans la base de données
        $template_record = [
            'name' => $template_data['name'],
            'description' => $template_data['description'],
            'type' => 'pdf',
            'content' => $html_content,
            'settings' => wp_json_encode([
                'variables' => $template_data['variables'],
                'category' => $template_data['category'],
                'is_predesigned' => true,
                'template_key' => $template_key
            ]),
            'status' => 'active',
            'category_id' => $this->get_or_create_category($template_data['category']),
            'author_id' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];

        $template_id = $this->database->create_template($template_record);

        if ($template_id) {
            $this->logger->info('Template prédéfini installé', [
                'template_key' => $template_key,
                'template_id' => $template_id,
                'name' => $template_data['name']
            ]);
        }

        return $template_id;
    }

    /**
     * Générer le HTML d'un template prédéfini
     *
     * @param string $template_key
     * @param array $customizations
     * @return string
     */
    private function generate_template_html(string $template_key, array $customizations = []): string {
        $template_data = $this->template_library[$template_key];

        switch ($template_key) {
            case 'invoice':
                return $this->generate_invoice_html($customizations);

            case 'quote':
                return $this->generate_quote_html($customizations);

            case 'receipt':
                return $this->generate_receipt_html($customizations);

            case 'certificate':
                return $this->generate_certificate_html($customizations);

            case 'newsletter':
                return $this->generate_newsletter_html($customizations);

            default:
                return $this->generate_default_html($template_data, $customizations);
        }
    }

    /**
     * Générer HTML pour template facture
     */
    private function generate_invoice_html(array $customizations = []): string {
        $company_name = $customizations['company_name'] ?? 'Votre Entreprise';
        $company_address = $customizations['company_address'] ?? '123 Rue de l\'Entreprise\n75000 Paris\nFrance';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .company-info { float: right; text-align: right; }
                .invoice-info { float: left; }
                .invoice-title { font-size: 36px; font-weight: bold; color: #333; margin: 0; }
                .invoice-number { font-size: 18px; margin: 10px 0; }
                .customer-info { clear: both; margin: 30px 0; }
                .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .items-table th { background-color: #f5f5f5; font-weight: bold; }
                .total-section { float: right; width: 200px; }
                .total-row { display: flex; justify-content: space-between; padding: 5px 0; }
                .total-row.final { border-top: 2px solid #333; font-weight: bold; font-size: 18px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company-info">
                    <h1>' . esc_html($company_name) . '</h1>
                    <p>' . nl2br(esc_html($company_address)) . '</p>
                </div>
                <div class="invoice-info">
                    <h1 class="invoice-title">FACTURE</h1>
                    <p class="invoice-number">N° {order_number}</p>
                    <p>Date: {order_date}</p>
                </div>
            </div>

            <div class="customer-info">
                <h3>Facturé à:</h3>
                <p>{billing_company}<br>
                {billing_first_name} {billing_last_name}<br>
                {billing_address_1}<br>
                {billing_city}, {billing_state} {billing_postcode}<br>
                {billing_country}</p>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qté</th>
                        <th>Prix</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {order_items_table}
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-row">
                    <span>Sous-total:</span>
                    <span>{order_subtotal}</span>
                </div>
                <div class="total-row">
                    <span>TVA:</span>
                    <span>{order_tax}</span>
                </div>
                <div class="total-row final">
                    <span>TOTAL:</span>
                    <span>{order_total}</span>
                </div>
            </div>

            <div style="clear: both; margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p><strong>Conditions de paiement:</strong> Payable sous 30 jours</p>
                <p>Merci pour votre confiance!</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Générer HTML pour template devis
     */
    private function generate_quote_html(array $customizations = []): string {
        $company_name = $customizations['company_name'] ?? 'Votre Entreprise';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f9f9f9; }
                .container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
                .header { text-align: center; border-bottom: 3px solid #007cba; padding-bottom: 30px; margin-bottom: 40px; }
                .quote-title { font-size: 42px; color: #007cba; margin: 0; font-weight: 300; }
                .quote-subtitle { font-size: 16px; color: #666; margin: 10px 0 0 0; }
                .quote-details { display: flex; justify-content: space-between; margin: 30px 0; }
                .detail-box { background: #f8f9fa; padding: 20px; border-radius: 5px; }
                .customer-section { background: #007cba; color: white; padding: 30px; border-radius: 10px; margin: 30px 0; }
                .items-table { width: 100%; border-collapse: collapse; margin: 30px 0; background: white; }
                .items-table th { background: #007cba; color: white; padding: 15px; text-align: left; }
                .items-table td { padding: 15px; border-bottom: 1px solid #eee; }
                .total-box { background: #f8f9fa; padding: 30px; border-radius: 10px; text-align: right; }
                .total-amount { font-size: 36px; color: #007cba; font-weight: bold; }
                .validity { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 30px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1 class="quote-title">DEVIS</h1>
                    <p class="quote-subtitle">Proposition commerciale</p>
                </div>

                <div class="quote-details">
                    <div class="detail-box">
                        <h3>Devis N° {quote_number}</h3>
                        <p>Date: {quote_date}</p>
                    </div>
                    <div class="detail-box">
                        <h3>Valable jusqu\'au</h3>
                        <p>{valid_until}</p>
                    </div>
                </div>

                <div class="customer-section">
                    <h3>Destinataire</h3>
                    <p>{customer_company}<br>
                    {customer_name}<br>
                    {customer_address}<br>
                    {customer_email}</p>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Total HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        {quote_items_table}
                    </tbody>
                </table>

                <div class="total-box">
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Sous-total:</span>
                            <span>{quote_subtotal}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>TVA (20%):</span>
                            <span>{quote_tax}</span>
                        </div>
                    </div>
                    <div class="total-amount">
                        {quote_total} TTC
                    </div>
                </div>

                <div class="validity">
                    <h4>🕒 Validité de l\'offre</h4>
                    <p>Ce devis est valable 30 jours à compter de sa date d\'émission.</p>
                </div>

                <div style="text-align: center; margin-top: 50px; padding-top: 30px; border-top: 1px solid #eee;">
                    <p>Nous restons à votre disposition pour toute question.</p>
                    <p>Cordialement,<br><strong>' . esc_html($company_name) . '</strong></p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Générer HTML pour template reçu
     */
    private function generate_receipt_html(array $customizations = []): string {
        $company_name = $customizations['company_name'] ?? 'Votre Entreprise';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 40px; }
                .receipt { max-width: 400px; margin: 0 auto; border: 2px solid #333; padding: 30px; }
                .header { border-bottom: 1px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .amount { font-size: 48px; font-weight: bold; color: #28a745; margin: 30px 0; }
                .details { text-align: left; margin: 30px 0; }
                .detail-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #eee; }
                .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #333; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="receipt">
                <div class="header">
                    <h1>' . esc_html($company_name) . '</h1>
                    <p>Reçu de paiement</p>
                </div>

                <div class="amount">
                    {amount_paid}
                </div>

                <div class="details">
                    <div class="detail-row">
                        <span>N° Reçu:</span>
                        <span>{receipt_number}</span>
                    </div>
                    <div class="detail-row">
                        <span>Date:</span>
                        <span>{payment_date}</span>
                    </div>
                    <div class="detail-row">
                        <span>Client:</span>
                        <span>{customer_name}</span>
                    </div>
                    <div class="detail-row">
                        <span>Mode de paiement:</span>
                        <span>{payment_method}</span>
                    </div>
                </div>

                <div class="footer">
                    <p>✓ Paiement reçu et validé</p>
                    <p>Merci pour votre confiance !</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Générer HTML pour template certificat
     */
    private function generate_certificate_html(array $customizations = []): string {
        $company_name = $customizations['company_name'] ?? 'Votre Organisation';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: "Times New Roman", serif; text-align: center; padding: 60px; background: #f9f9f9; }
                .certificate { max-width: 800px; margin: 0 auto; background: white; padding: 80px; border: 10px solid #8B4513; position: relative; }
                .border-decoration { position: absolute; top: 20px; left: 20px; right: 20px; bottom: 20px; border: 2px solid #DAA520; }
                .title { font-size: 48px; font-weight: bold; color: #8B4513; margin: 40px 0; text-transform: uppercase; letter-spacing: 3px; }
                .subtitle { font-size: 24px; color: #666; margin: 20px 0; font-style: italic; }
                .recipient { font-size: 36px; font-weight: bold; color: #2E8B57; margin: 40px 0; text-decoration: underline; }
                .content { font-size: 18px; line-height: 1.6; margin: 40px 0; text-align: justify; }
                .signature-section { margin-top: 80px; display: flex; justify-content: space-between; }
                .signature { text-align: center; }
                .signature-line { border-bottom: 1px solid #333; width: 200px; margin: 40px auto 10px auto; }
                .date { position: absolute; bottom: 40px; right: 60px; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="certificate">
                <div class="border-decoration"></div>

                <div style="position: relative; z-index: 1;">
                    <h1 class="title">{certificate_title}</h1>

                    <p class="subtitle">est décerné à</p>

                    <h2 class="recipient">{recipient_name}</h2>

                    <div class="content">
                        {certificate_text}
                    </div>

                    <div class="signature-section">
                        <div class="signature">
                            <div class="signature-line"></div>
                            <p>{issuer_name}</p>
                            <p style="font-size: 14px; color: #666;">Directeur</p>
                        </div>
                        <div class="signature">
                            <div class="signature-line"></div>
                            <p>Date</p>
                            <p style="font-size: 14px; color: #666;">{issue_date}</p>
                        </div>
                    </div>
                </div>

                <div class="date">
                    Délivré le {issue_date}
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Générer HTML pour template newsletter
     */
    private function generate_newsletter_html(array $customizations = []): string {
        $company_name = $customizations['company_name'] ?? 'Votre Entreprise';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
                .logo { font-size: 28px; font-weight: bold; margin-bottom: 10px; }
                .tagline { font-size: 16px; opacity: 0.9; }
                .content { padding: 40px 30px; }
                .section { margin-bottom: 40px; }
                .section-title { font-size: 24px; color: #333; margin-bottom: 20px; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
                .article { margin-bottom: 30px; }
                .article-title { font-size: 20px; color: #333; margin-bottom: 10px; }
                .article-content { line-height: 1.6; color: #555; }
                .cta-button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .footer { background: #333; color: white; padding: 30px; text-align: center; }
                .social-links { margin: 20px 0; }
                .social-links a { color: white; margin: 0 10px; text-decoration: none; }
                .unsubscribe { font-size: 12px; color: #999; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">' . esc_html($company_name) . '</div>
                    <div class="tagline">Votre newsletter mensuelle</div>
                </div>

                <div class="content">
                    <div class="section">
                        <h2 class="section-title">{newsletter_title}</h2>

                        <div class="article">
                            <h3 class="article-title">Article Principal</h3>
                            <div class="article-content">
                                {content_blocks}
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin: 40px 0;">
                        <a href="#" class="cta-button">En savoir plus</a>
                    </div>
                </div>

                <div class="footer">
                    <div class="social-links">
                        {social_links}
                    </div>
                    <p>Restez connecté avec nous !</p>
                    <div class="unsubscribe">
                        <a href="{unsubscribe_link}">Se désabonner</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * Générer HTML par défaut
     */
    private function generate_default_html(array $template_data, array $customizations = []): string {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . esc_html($template_data['name']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 40px; }
                .header { text-align: center; margin-bottom: 40px; }
                .content { line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . esc_html($template_data['name']) . '</h1>
                <p>' . esc_html($template_data['description']) . '</p>
            </div>
            <div class="content">
                <p>Template générique - Personnalisez selon vos besoins.</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Obtenir ou créer une catégorie
     */
    private function get_or_create_category(string $category_slug): int {
        global $wpdb;

        // Vérifier si la catégorie existe
        $category = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare(
            "SELECT id FROM {$wpdb->prefix}pdf_builder_categories WHERE slug = %s",
            $category_slug
        ));

        if ($category) {
            return $category->id;
        }

        // Créer la catégorie
        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_categories',
            [
                'name' => ucfirst($category_slug),
                'slug' => $category_slug,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s']
        );

        return $wpdb->insert_id;
    }

    /**
     * Analyser les variables utilisées dans un template
     *
     * @param string $content
     * @return array
     */
    public function analyze_template_variables(string $content): array {
        preg_match_all('/\{([^}]+)\}/', $content, $matches);

        $variables = [];
        if (!empty($matches[1])) {
            foreach ($matches[1] as $variable) {
                $variables[] = trim($variable);
            }
        }

        return array_unique($variables);
    }

    /**
     * Valider qu'un template contient toutes les variables requises
     *
     * @param string $content
     * @param array $required_variables
     * @return array
     */
    public function validate_template_variables(string $content, array $required_variables): array {
        $found_variables = $this->analyze_template_variables($content);

        $missing = array_diff($required_variables, $found_variables);
        $extra = array_diff($found_variables, $required_variables);

        return [
            'valid' => empty($missing),
            'missing_variables' => array_values($missing),
            'extra_variables' => array_values($extra),
            'found_variables' => $found_variables
        ];
    }
}