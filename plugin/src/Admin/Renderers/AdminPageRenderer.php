<?php

/**
 * PDF Builder Pro - Admin Page Renderer
 * Responsable du rendu HTML de la page d'administration (Tableau de bord)
 */

namespace PDF_Builder\Admin\Renderers;

class AdminPageRenderer
{
    private $admin;

    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    public function renderAdminPage()
    {
        // Récupérer les données nécessaires depuis l'admin
        $stats = $this->admin->getDashboardStats();
        $plugin_version = $this->admin->getPluginVersion();

        // Ici on reproduit l'UI complète d'administration
        ob_start();
        ?>
        <div class="wrap">
            <div class="pdf-builder-dashboard">
                <div class="dashboard-header">
                    <h1>📄 PDF Builder Pro</h1>
                    <p class="dashboard-subtitle">Constructeur de PDF professionnel avec éditeur visuel avancé</p>
                    <div class="dashboard-meta">
                        <span class="version-info">Version <?php echo \esc_html($plugin_version); ?></span>
                        <span class="last-update">Dernière mise à jour: <?php echo date('d/m/Y'); ?></span>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['templates']); ?></div>
                            <div class="stat-label">Templates</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📄</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['documents']); ?></div>
                            <div class="stat-label">Documents générés</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📈</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['today']); ?></div>
                            <div class="stat-label">Aujourd'hui</div>
                        </div>
                    </div>
                </div>

                <!-- Actions principales -->
                <div class="dashboard-actions">
                    <div class="action-card primary">
                        <h3>⚛️ Créer un nouveau PDF</h3>
                        <p>Utilisez notre éditeur React moderne pour concevoir vos documents</p>
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-react-editor'); ?>"
                            class="button button-primary">
                            Ouvrir l'Éditeur React
                        </a>
                    </div>

                    <div class="action-card">
                        <h3>📋 Gérer les Templates</h3>
                        <p>Créez, modifiez et organisez vos modèles de documents</p>
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>"
                            class="button button-secondary">
                            Voir les Templates
                        </a>
                    </div>

                    <div class="action-card">
                        <h3>⚙️ Paramètres & Configuration</h3>
                        <p>Configurez les paramètres avancés, polices, qualité d'impression et options WooCommerce</p>
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>"
                            class="button button-secondary">
                            ➕ Créer un Template
                        </a>
                    </div>
                </div>

                <!-- Guide rapide -->
                <div class="dashboard-guide">
                    <h3>🚀 Guide de démarrage rapide</h3>
                    <div class="guide-steps">
                        <div class="step">
                            <span class="step-number">1</span>
                            <div class="step-content">
                                <h4>🛠️ Configuration initiale</h4>
                                <p>Vérifiez la version Pro/Gratuite et les statistiques de votre installation</p>
                                <small>💡 La page d'accueil affiche automatiquement votre version et les métriques en temps réel</small>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">2</span>
                            <div class="step-content">
                                <h4>📋 Créez votre premier template</h4>
                                <p>Allez dans "Templates PDF" → "Créer un nouveau template"</p>
                                <small>💡 Utilisez l'éditeur React avec Canvas avancé, grille d'aimantation et guides</small>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">3</span>
                            <div class="step-content">
                                <h4>🎨 Concevez votre PDF</h4>
                                <p>Ajoutez des éléments : texte, images, formes, code-barres, variables WooCommerce</p>
                                <small>💡 Les propriétés sont organisées en accordéons pour une meilleure ergonomie</small>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">4</span>
                            <div class="step-content">
                                <h4>🛒 Intégrez WooCommerce</h4>
                                <p>Utilisez les variables dynamiques : {{order_number}}, {{customer_name}}, etc.</p>
                                <small>💡 Aperçu direct dans les metabox des commandes WooCommerce</small>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">5</span>
                            <div class="step-content">
                                <h4>⚙️ Configurez les paramètres avancés</h4>
                                <p>Ajustez les marges, la qualité d'impression, la compression PDF</p>
                                <small>💡 Paramètres Canvas complets : dimensions, orientation, grille, zoom</small>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">6</span>
                            <div class="step-content">
                                <h4>📤 Générez et testez</h4>
                                <p>Prévisualisez votre PDF et ajustez si nécessaire</p>
                                <small>💡 Utilisez l'API Preview intégrée pour des aperçus haute qualité</small>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">7</span>
                            <div class="step-content">
                                <h4>🔄 Automatisez (optionnel)</h4>
                                <p>Configurez des workflows automatisés pour la génération en masse</p>
                                <small>💡 Idéal pour factures, devis, reçus WooCommerce</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fonctionnalités complètes -->
                <div class="dashboard-features">
                    <h3>✨ Fonctionnalités de PDF Builder Pro</h3>
                    <div class="features-grid">
                        <!-- Éditeur React -->
                        <div class="feature-category">
                            <h4>⚛️ Éditeur React</h4>
                            <ul>
                                <li>Interface moderne et réactive</li>
                                <li>Éditeur visuel en temps réel</li>
                                <li>Composants modulaires</li>
                                <li>Performance optimisée</li>
                                <li>Navigation intuitive</li>
                                <li>API Preview intégrée</li>
                                <li>Chargement et initialisation améliorés</li>
                                <li>Paramètres Canvas étendus</li>
                                <li>Grille d'aimantation</li>
                                <li>Guides et marges de sécurité</li>
                                <li>Zoom et navigation avancés</li>
                                <li>Multi-sélection et manipulation</li>
                            </ul>
                        </div>

                        <!-- Éléments de Design -->
                        <div class="feature-category">
                            <h4>📐 Éléments de Design</h4>
                            <ul>
                                <li>Textes avec formatage riche</li>
                                <li>Images et logos</li>
                                <li>Formes géométriques</li>
                                <li>Lignes et bordures</li>
                                <li>Code-barres et QR codes</li>
                                <li>Éléments WooCommerce</li>
                                <li>Variables dynamiques</li>
                                <li>Charts et graphiques</li>
                                <li>Signatures numériques</li>
                            </ul>
                        </div>

                        <!-- Gestion des Templates -->
                        <div class="feature-category">
                            <h4>📋 Gestion des Templates</h4>
                            <ul>
                                <li>Création de modèles personnalisés</li>
                                <li>Import/Export de templates</li>
                                <li>Catégorisation avancée</li>
                                <li>Templates prédéfinis</li>
                                <li>Historique des versions</li>
                                <li>Partage d'équipe</li>
                                <li>Sauvegarde automatique</li>
                                <li>Validation JSON automatique</li>
                                <li>Réparation données corrompues</li>
                                <li>Stabilité sauvegarde améliorée</li>
                            </ul>
                        </div>

                        <!-- Intégration WooCommerce -->
                        <div class="feature-category">
                            <h4>🛒 WooCommerce</h4>
                            <ul>
                                <li>Factures automatiques</li>
                                <li>Bon de livraison</li>
                                <li>Étiquettes de produits</li>
                                <li>Intégration commandes</li>
                                <li>Variables dynamiques</li>
                                <li>Support HPOS</li>
                                <li>Gestion des statuts</li>
                            </ul>
                        </div>

                        <!-- Export et Qualité -->
                        <div class="feature-category">
                            <h4>📤 Export & Qualité</h4>
                            <ul>
                                <li>PDF haute qualité</li>
                                <li>Compression intelligente</li>
                                <li>Polices embarquées</li>
                                <li>Métadonnées PDF</li>
                                <li>Formats multiples</li>
                                <li>Optimisation web</li>
                                <li>Signature numérique</li>
                            </ul>
                        </div>

                        <!-- Paramètres Avancés -->
                        <div class="feature-category">
                            <h4>⚙️ Paramètres Avancés</h4>
                            <ul>
                                <li>Configuration React</li>
                                <li>Paramètres de performance</li>
                                <li>Gestion des rôles</li>
                                <li>Cache intelligent</li>
                                <li>Logs détaillés</li>
                                <li>Actions de maintenance</li>
                            </ul>
                        </div>

                        <!-- API et Intégrations -->
                        <div class="feature-category">
                            <h4>🔗 API & Intégrations</h4>
                            <ul>
                                <li>API REST complète</li>
                                <li>Webhooks personnalisés</li>
                                <li>Intégration Zapier</li>
                                <li>Support JSON</li>
                                <li>Import CSV/Excel</li>
                                <li>Connexions externes</li>
                                <li>Callbacks JavaScript</li>
                            </ul>
                        </div>

                        <!-- Interface Utilisateur Améliorée -->
                        <div class="feature-category">
                            <h4>🎨 Interface Utilisateur</h4>
                            <ul>
                                <li>Accordéons organisés pour les propriétés</li>
                                <li>Page d'accueil pleine largeur</li>
                                <li>Statistiques dynamiques en temps réel</li>
                                <li>Navigation intuitive et moderne</li>
                                <li>Responsive design optimisé</li>
                                <li>Thème sombre/clair adaptable</li>
                                <li>Performance AJAX - Requêtes plus rapides et fiables</li>
                                <li>Health checks automatiques</li>
                                <li>Monitoring intégré et logs détaillés</li>
                                <li>Fallbacks visuels en cas d'erreur</li>
                                <li>Messages d'erreur informatifs</li>
                            </ul>
                        </div>

                        <!-- Sécurité et Performance -->
                        <div class="feature-category">
                            <h4>🔒 Sécurité & Performance</h4>
                            <ul>
                                <li>Validation stricte des données</li>
                                <li>Protection CSRF et nonces</li>
                                <li>Sanitisation automatique</li>
                                <li>Cache optimisé</li>
                                <li>Compression GZIP</li>
                                <li>Monitoring des ressources</li>
                                <li>Logs de sécurité détaillés</li>
                                <li>Fail-safe initialization</li>
                                <li>Error boundaries React</li>
                                <li>Memory leaks prevention</li>
                            </ul>
                        </div>

                        <!-- Intégration WooCommerce -->
                        <div class="feature-category">
                            <h4>🛒 WooCommerce</h4>
                            <ul>
                                <li>Variables de commande intégrées</li>
                                <li>Aperçu dans metabox commande</li>
                                <li>Endpoint AJAX pour données</li>
                                <li>Remplacement automatique des variables</li>
                                <li>Templates spécialisés (Facture, Devis, Reçu)</li>
                                <li>Intégration transparente</li>
                            </ul>
                        </div>

                        <!-- Gestion des Versions -->
                        <div class="feature-category">
                            <h4>📦 Gestion des Versions</h4>
                            <ul>
                                <li>Version Pro avec fonctionnalités complètes</li>
                                <li>Version Gratuite avec fonctionnalités de base</li>
                                <li>Détection automatique de licence</li>
                                <li>Mise à jour transparente</li>
                                <li>Compatibilité ascendante</li>
                                <li>Historique des versions</li>
                                <li>Support multi-versions</li>
                            </ul>
                        </div>

                    </div>

                    <!-- Nouvelles fonctionnalités -->
                    <div class="new-features">
                        <h4>🆕 Nouvelles fonctionnalités (v1.1.0)</h4>
                        <div class="new-features-list">
                            <div class="new-feature-item">
                                <span class="feature-badge">INTERFACE</span>
                                <strong>Accordéons organisés</strong> - Police globale du tableau maintenant dans un accordéon pliable
                            </div>
                            <div class="new-feature-item">
                                <span class="feature-badge">VERSION</span>
                                <strong>Gestion des versions</strong> - Système pro/gratuit avec détection automatique de licence
                            </div>
                            <div class="new-feature-item">
                                <span class="feature-badge">STATS</span>
                                <strong>Statistiques dynamiques</strong> - Comptage en temps réel des templates et documents
                            </div>
                            <div class="new-feature-item">
                                <span class="feature-badge">UI</span>
                                <strong>Page d'accueil optimisée</strong> - Pleine largeur et informations de version
                            </div>
                            <div class="new-feature-item">
                                <span class="feature-badge">PERF</span>
                                <strong>Synchronisation des versions</strong> - Gestion centralisée et cohérente des numéros de version
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .pdf-builder-dashboard {
                        width: 100%;
                        padding: 0 20px;
                        box-sizing: border-box;
                    }

                    .dashboard-meta {
                        display: flex;
                        gap: 20px;
                        margin-top: 10px;
                        font-size: 14px;
                        color: #666;
                    }

                    .version-info {
                        color: #2271b1;
                        font-weight: 500;
                    }

                    .last-update {
                        color: #666;
                    }

                    .dashboard-subtitle {
                        color: #666;
                        font-size: 16px;
                        margin-top: 10px;
                    }

                    .dashboard-stats {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 20px;
                        margin-bottom: 30px;
                    }

                    .stat-card {
                        background: #fff;
                        border: 1px solid #e1e1e1;
                        border-radius: 8px;
                        padding: 20px;
                        display: flex;
                        align-items: center;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }

                    .stat-icon {
                        font-size: 32px;
                        margin-right: 15px;
                    }

                    .stat-number {
                        font-size: 28px;
                        font-weight: bold;
                        color: #2271b1;
                    }

                    .stat-label {
                        color: #666;
                        font-size: 14px;
                    }

                    .dashboard-actions {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                        gap: 20px;
                        margin-bottom: 30px;
                    }

                    .action-card {
                        background: #fff;
                        border: 1px solid #e1e1e1;
                        border-radius: 8px;
                        padding: 25px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }

                    .action-card.primary {
                        border-color: #2271b1;
                        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
                    }

                    .action-card h3 {
                        margin-top: 0;
                        color: #1d2327;
                    }

                    .action-card p {
                        color: #666;
                        margin-bottom: 15px;
                    }

                    .dashboard-guide {
                        background: #fff;
                        border: 1px solid #e1e1e1;
                        border-radius: 8px;
                        padding: 25px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }

                    .guide-steps {
                        display: flex;
                        flex-wrap: nowrap;
                        gap: 15px;
                        margin-top: 20px;
                        justify-content: flex-start;
                        overflow-x: auto;
                        padding-bottom: 10px;
                    }

                    .step {
                        display: flex;
                        align-items: flex-start;
                        min-width: 200px;
                        flex: 0 0 auto;
                        max-width: 250px;
                    }

                    .step-number {
                        background: #2271b1;
                        color: white;
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        margin-right: 15px;
                        flex-shrink: 0;
                    }

                    .step-content h4 {
                        margin: 0 0 5px 0;
                        color: #1d2327;
                    }

                    .step-content small {
                        display: block;
                        color: #888;
                        font-size: 12px;
                        margin-top: 5px;
                        font-style: italic;
                    }

                    /* Styles pour la section fonctionnalités */
                    .dashboard-features {
                        background: #fff;
                        border: 1px solid #e1e1e1;
                        border-radius: 8px;
                        padding: 25px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        margin-top: 30px;
                    }

                    .dashboard-features h3 {
                        margin-top: 0;
                        color: #1d2327;
                        border-bottom: 2px solid #2271b1;
                        padding-bottom: 10px;
                    }

                    .features-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                        gap: 25px;
                        margin-top: 25px;
                    }

                    .feature-category {
                        background: #f8f9fa;
                        border: 1px solid #e9ecef;
                        border-radius: 6px;
                        padding: 20px;
                    }

                    .feature-category h4 {
                        margin: 0 0 15px 0;
                        color: #2271b1;
                        font-size: 16px;
                        border-bottom: 1px solid #dee2e6;
                        padding-bottom: 8px;
                    }

                    .feature-category ul {
                        list-style: none;
                        padding: 0;
                        margin: 0;
                    }

                    .feature-category li {
                        padding: 4px 0;
                        color: #495057;
                        font-size: 14px;
                        position: relative;
                        padding-left: 20px;
                    }

                    .feature-category li:before {
                        content: "✓";
                        color: #28a745;
                        font-weight: bold;
                        position: absolute;
                        left: 0;
                    }

                    /* Styles pour les nouvelles fonctionnalités */
                    .new-features {
                        margin-top: 30px;
                        padding-top: 20px;
                        border-top: 1px solid #dee2e6;
                    }

                    .new-features h4 {
                        color: #2271b1;
                        margin-bottom: 15px;
                    }

                    .new-features-list {
                        display: grid;
                        gap: 10px;
                    }

                    .new-feature-item {
                        display: flex;
                        align-items: center;
                        padding: 10px;
                        background: #f8f9ff;
                        border-radius: 4px;
                        border-left: 4px solid #2271b1;
                    }

                    .feature-badge {
                        background: #2271b1;
                        color: white;
                        padding: 2px 8px;
                        border-radius: 12px;
                        font-size: 10px;
                        font-weight: bold;
                        margin-right: 10px;
                        flex-shrink: 0;
                    }

                    .new-feature-item strong {
                        color: #1d2327;
                    }
                </style>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        return $html;
    }
}

