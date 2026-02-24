<?php
/**
 * Template for PDF Builder Pro Dashboard
 *
 * @var array $stats Dashboard statistics
 * @var string $plugin_version Plugin version
 * @var bool $is_premium Premium status
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <div class="pdfb-pdf-builder-dashboard">
        <div class="pdfb-dashboard-header">
            <h1>📄 PDF Builder Pro</h1>
            <p class="pdfb-dashboard-subtitle">Constructeur de PDF professionnel avec éditeur visuel avancé</p>
            <div class="pdfb-dashboard-meta">
                <span class="pdfb-version-info">Version <?php echo esc_html($plugin_version); ?></span>
                <span class="pdfb-last-update">Dernière mise à jour: <?php echo esc_html(gmdate('d/m/Y')); ?></span>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="pdfb-dashboard-stats">
            <div class="pdfb-stat-card">
                <div class="pdfb-stat-icon">📋</div>
                <div class="pdfb-stat-content">
                    <div class="pdfb-stat-number"><?php echo number_format($stats['templates']); ?></div>
                    <div class="pdfb-stat-label">Templates</div>
                </div>
            </div>
            <div class="pdfb-stat-card">
                <div class="pdfb-stat-icon">📄</div>
                <div class="pdfb-stat-content">
                    <div class="pdfb-stat-number"><?php echo number_format($stats['documents']); ?></div>
                    <div class="pdfb-stat-label">Documents générés</div>
                </div>
            </div>
            <div class="pdfb-stat-card">
                <div class="pdfb-stat-icon">📈</div>
                <div class="pdfb-stat-content">
                    <div class="pdfb-stat-number"><?php echo number_format($stats['today']); ?></div>
                    <div class="pdfb-stat-label">Aujourd'hui</div>
                </div>
            </div>
        </div>

        <!-- Actions principales -->
        <div class="pdfb-dashboard-actions">
            <div class="pdfb-action-card pdfb-primary <?php echo !$is_premium ? 'pdfb-premium-locked' : ''; ?>">
                <h3>⚛️ Créer un nouveau PDF<?php if (!$is_premium): ?> <span class="pdfb-premium-badge">PRO</span><?php endif; ?></h3>
                <p>Utilisez notre éditeur React moderne pour concevoir vos documents</p>
                <?php if ($is_premium): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-react-editor')); ?>"
                        class="button button-primary">
                        Ouvrir l'Éditeur React
                    </a>
                <?php else: ?>
                    <button type="button" class="button button-primary pdfb-premium-button" 
                        onclick="alert('Cette fonctionnalité nécessite une licence Premium. Veuillez mettre à niveau votre licence dans les paramètres.')">
                        🔒 Fonction Premium
                    </button>
                <?php endif; ?>
            </div>

            <div class="pdfb-action-card">
                <h3>📋 Gérer les Templates</h3>
                <p>Créez, modifiez et organisez vos modèles de documents</p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-templates')); ?>"
                    class="button button-secondary">
                    Voir les Templates
                </a>
            </div>

            <div class="pdfb-action-card <?php echo !$is_premium ? 'pdfb-premium-locked' : ''; ?>">
                <h3>⚙️ Paramètres & Configuration<?php if (!$is_premium): ?> <span class="pdfb-premium-badge">PRO</span><?php endif; ?></h3>
                <p>Configurez les paramètres avancés, polices, qualité d'impression et options WooCommerce</p>
                <?php if ($is_premium): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-settings')); ?>"
                        class="button button-secondary">
                        Ouvrir les Paramètres
                    </a>
                <?php else: ?>
                    <button type="button" class="button button-secondary pdfb-premium-button" 
                        onclick="alert('Cette fonctionnalité nécessite une licence Premium. Veuillez mettre à niveau votre licence dans les paramètres.')">
                        🔒 Fonction Premium
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Guide rapide -->
        <div class="pdfb-dashboard-guide">
            <h3>🚀 Guide de démarrage rapide</h3>
            <div class="pdfb-guide-steps">
                <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-pro')); ?>" class="pdfb-step pdfb-step-link">
                    <span class="pdfb-step-number">1</span>
                    <div class="pdfb-step-content">
                        <h4>🛠️ Configuration initiale</h4>
                        <p>Vérifiez la version Pro/Gratuite et les statistiques de votre installation</p>
                        <small>💡 La page d'accueil affiche automatiquement votre version et les métriques en temps réel</small>
                    </div>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-templates')); ?>" class="pdfb-step pdfb-step-link">
                    <span class="pdfb-step-number">2</span>
                    <div class="pdfb-step-content">
                        <h4>📋 Créez votre premier template</h4>
                        <p>Allez dans "Templates PDF" → "Créer un nouveau template"</p>
                        <small>💡 Utilisez l'éditeur React avec Canvas avancé, grille d'aimantation et guides</small>
                    </div>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-react-editor')); ?>" class="pdfb-step pdfb-step-link">
                    <span class="pdfb-step-number">3</span>
                    <div class="pdfb-step-content">
                        <h4>🎨 Concevez votre PDF</h4>
                        <p>Ajoutez des éléments : texte, images, formes, code-barres, variables WooCommerce</p>
                        <small>💡 Les propriétés sont organisées en accordéons pour une meilleure ergonomie</small>
                    </div>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-settings')); ?>" class="pdfb-step pdfb-step-link">
                    <span class="pdfb-step-number">4</span>
                    <div class="pdfb-step-content">
                        <h4>🛒 Intégrez WooCommerce</h4>
                        <p>Utilisez les variables dynamiques : {{order_number}}, {{customer_name}}, etc.</p>
                        <small>💡 Aperçu direct dans les metabox des commandes WooCommerce</small>
                    </div>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-settings')); ?>" class="pdfb-step pdfb-step-link">
                    <span class="pdfb-step-number">5</span>
                    <div class="pdfb-step-content">
                        <h4>⚙️ Configurez les paramètres avancés</h4>
                        <p>Ajustez les marges, la qualité d'impression, la compression PDF</p>
                        <small>💡 Paramètres Canvas complets : dimensions, orientation, grille, zoom</small>
                    </div>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-react-editor')); ?>" class="pdfb-step pdfb-step-link">
                    <span class="pdfb-step-number">6</span>
                    <div class="pdfb-step-content">
                        <h4>📤 Générez et testez</h4>
                        <p>Prévisualisez votre PDF et ajustez si nécessaire</p>
                        <small>💡 Utilisez l'API Preview intégrée pour des aperçus haute qualité</small>
                    </div>
                </a>
                <div class="pdfb-step">
                    <span class="pdfb-step-number">7</span>
                    <div class="pdfb-step-content">
                        <h4>🔄 Automatisez (optionnel)</h4>
                        <p>Configurez des workflows automatisés pour la génération en masse</p>
                        <small>💡 Idéal pour factures, devis, reçus WooCommerce</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fonctionnalités complètes -->
        <div class="pdfb-dashboard-features">
            <h3>✨ Fonctionnalités de PDF Builder Pro</h3>
            <div class="pdfb-features-grid">
                <!-- Éditeur React -->
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
                <div class="pdfb-feature-category">
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
            <div class="pdfb-new-features">
                <h4>🆕 Nouvelles fonctionnalités (v1.0.1.1)</h4>
                <div class="pdfb-new-features-list">
                    <div class="pdfb-new-feature-item">
                        <span class="pdfb-feature-badge">INTERFACE</span>
                        <strong>Accordéons organisés</strong> - Police globale du tableau maintenant dans un accordéon pliable
                    </div>
                    <div class="pdfb-new-feature-item">
                        <span class="pdfb-feature-badge">VERSION</span>
                        <strong>Gestion des versions</strong> - Système pro/gratuit avec détection automatique de licence
                    </div>
                    <div class="pdfb-new-feature-item">
                        <span class="pdfb-feature-badge">STATS</span>
                        <strong>Statistiques dynamiques</strong> - Comptage en temps réel des templates et documents
                    </div>
                    <div class="pdfb-new-feature-item">
                        <span class="pdfb-feature-badge">UI</span>
                        <strong>Page d'accueil optimisée</strong> - Pleine largeur et informations de version
                    </div>
                    <div class="pdfb-new-feature-item">
                        <span class="pdfb-feature-badge">PERF</span>
                        <strong>Synchronisation des versions</strong> - Gestion centralisée et cohérente des numéros de version
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




