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
                        <span class="version-info">Version <?php echo esc_html($plugin_version); ?></span>
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
                    </div>
                </div>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        return $html;
    }
}
