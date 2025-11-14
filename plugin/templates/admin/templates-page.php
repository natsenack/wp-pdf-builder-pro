<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * Templates Page - PDF Builder Pro
 * Gestion des templates PDF
 */

// Inclure TemplateDefaults si pas déjà chargé
if (!class_exists('PDF_Builder\TemplateDefaults')) {
    // Chemin absolu vers TemplateDefaults.php
    $template_defaults_path = dirname(__DIR__, 2) . '/core/TemplateDefaults.php';
    if (file_exists($template_defaults_path)) {
        require_once $template_defaults_path;
    } else {
        // Fallback: essayer avec plugin_dir_path si disponible
        if (defined('PDF_BUILDER_PLUGIN_DIR')) {
            $template_defaults_path = PDF_BUILDER_PLUGIN_DIR . 'plugin/core/TemplateDefaults.php';
            if (file_exists($template_defaults_path)) {
                require_once $template_defaults_path;
            }
        }
    }
}

// ✅ FIX: Créer le nonce directement dans le template PHP
$templates_nonce = wp_create_nonce('pdf_builder_templates');

// Vérifications freemium
$user_can_create = \PDF_Builder\Admin\PdfBuilderAdmin::can_create_template();
$templates_count = \PDF_Builder\Admin\PdfBuilderAdmin::count_user_templates(get_current_user_id());
$is_premium = \PDF_Builder\Admin\PdfBuilderAdmin::is_premium_user();

// Créer templates par défaut si aucun template et utilisateur gratuit
if ($templates_count === 0 && !$is_premium) {
    \PDF_Builder\TemplateDefaults::create_default_templates_for_user(get_current_user_id());
    // Recharger la page pour afficher les templates
    wp_redirect(admin_url('admin.php?page=pdf-builder-templates'));
    exit;
}
?>

<!-- ✅ FIX: Localiser le nonce immédiatement pour le JavaScript inline -->
<script>
var pdfBuilderTemplatesNonce = '<?php echo esc_js($templates_nonce); ?>';
var ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
var pdfBuilderAjax = {
    nonce: '<?php echo esc_js(wp_create_nonce('pdf_builder_ajax')); ?>',
    editor_url: '<?php echo esc_js(admin_url('admin.php?page=pdf-builder-react-editor')); ?>'
};
</script>

<div class="wrap">
    <h1><?php _e('[DOC] Gestion des Templates PDF', 'pdf-builder-pro'); ?></h1>

    <!-- Debug section removed for production: API debug UI and tests have been stripped -->

    <div style="background: #fff; padding: 20px; border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -o-box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2><?php _e('Templates Disponibles', 'pdf-builder-pro'); ?></h2>

        <div style="margin: 20px 0;">
            <?php if ($user_can_create): ?>
                <a href="#" class="button button-primary" id="create-template-btn">
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e('Créer un Template', 'pdf-builder-pro'); ?>
                </a>
            <?php else: ?>
                <button class="button button-secondary" id="upgrade-required-btn"
                        onclick="showUpgradeModal('template_limit')"
                        style="background-color: #dc3545; border-color: #dc3545; color: white;">
                    <span class="dashicons dashicons-lock"></span>
                    <?php _e('Créer un Template (Premium)', 'pdf-builder-pro'); ?>
                </button>
            <?php endif; ?>

            <button id="open-template-gallery" class="button button-secondary" style="margin-left: 10px;">
                🎨 <?php _e('Parcourir les Modèles', 'pdf-builder-pro'); ?>
            </button>

            <!-- DEBUG: Affichage temporaire du nombre de templates -->
            <span style="margin-left: 20px; color: #666; font-size: 12px; font-style: italic;">
                📊 Templates créés: <strong><?php echo $templates_count; ?></strong>
                <?php if (!$is_premium): ?>
                    (limite: 1)
                <?php else: ?>
                    (illimité)
                <?php endif; ?>
            </span>
        </div>

        <!-- Message limitation freemium -->
        <?php if (!$is_premium && $templates_count >= 1): ?>
            <div class="notice notice-info" style="margin: 15px 0; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px;">
                <h4 style="margin: 0 0 10px 0; color: #0c5460;">
                    <span class="dashicons dashicons-info" style="margin-right: 5px;"></span>
                    <?php _e('Limite de Templates Atteinte', 'pdf-builder-pro'); ?>
                </h4>
                <p style="margin: 0 0 10px 0; color: #0c5460;">
                    <?php printf(
                        __('Vous avez créé %d templates gratuits sur 1. Passez en Premium pour créer des templates illimités !', 'pdf-builder-pro'),
                        $templates_count
                    ); ?>
                </p>
                <a href="#" onclick="showUpgradeModal('template_limit')" class="button button-primary">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php _e('Passer en Premium - 69€ à vie', 'pdf-builder-pro'); ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Message pour utilisateurs gratuits sans templates -->
        <?php if (!$is_premium && $templates_count === 0): ?>
            <div class="notice notice-success" style="margin: 15px 0; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
                <h4 style="margin: 0 0 10px 0; color: #155724;">
                    <span class="dashicons dashicons-yes" style="margin-right: 5px;"></span>
                    <?php _e('Créez Votre Premier Template', 'pdf-builder-pro'); ?>
                </h4>
                <p style="margin: 0; color: #155724;">
                    <?php _e('Commencez par créer votre premier template personnalisé. Passez en Premium pour accéder à des modèles prédéfinis !', 'pdf-builder-pro'); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Section de filtrage -->
        <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; border: 1px solid #dee2e6;">
            <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">Filtrer par type</h3>
            <div style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; gap: 10px; flex-wrap: wrap; -webkit-flex-wrap: wrap; -moz-flex-wrap: wrap; -ms-flex-wrap: wrap; -o-flex-wrap: wrap;">
                <button class="filter-btn button button-secondary active" data-filter="all" style="font-size: 12px; padding: 6px 12px;">📄 Tous</button>
                <button class="filter-btn button button-secondary" data-filter="facture" style="font-size: 12px; padding: 6px 12px; background: #007cba; border-color: #007cba; color: white;">🧾 Factures</button>
                <button class="filter-btn button button-secondary" data-filter="devis" style="font-size: 12px; padding: 6px 12px; background: #28a745; border-color: #28a745; color: white;">📋 Devis</button>
                <button class="filter-btn button button-secondary" data-filter="commande" style="font-size: 12px; padding: 6px 12px; background: #ffc107; border-color: #ffc107; color: #212529;">📦 Commandes</button>
                <button class="filter-btn button button-secondary" data-filter="contrat" style="font-size: 12px; padding: 6px 12px; background: #dc3545; border-color: #dc3545; color: white;">📑 Contrats</button>
                <button class="filter-btn button button-secondary" data-filter="newsletter" style="font-size: 12px; padding: 6px 12px; background: #6f42c1; border-color: #6f42c1; color: white;">📰 Newsletters</button>
                <button class="filter-btn button button-secondary" data-filter="autre" style="font-size: 12px; padding: 6px 12px; background: #6c757d; border-color: #6c757d; color: white;">📄 Autres</button>
            </div>
        </div>

        <div id="templates-list" style="margin-top: 20px;">

            <!-- Section Templates Utilisateur -->
            <h3 style="margin: 30px 0 15px 0; color: #23282d; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
                📝 Mes Templates Personnalisés
            </h3>
            <p style="color: #666; margin-bottom: 20px;">
                Gérez vos templates personnalisés créés et modifiés.
            </p>

            <?php
            // Récupérer tous les templates pour debug
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $templates = $wpdb->get_results("SELECT id, name, created_at, updated_at, is_default, template_data FROM $table_templates ORDER BY id", ARRAY_A);

            if (!empty($templates)) {
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">';

                foreach ($templates as $template) {
                    $template_id = $template['id'];
                    $template_name = esc_html($template['name']);
                    $created_at = isset($template['created_at']) ? $template['created_at'] : null;
                    $updated_at = isset($template['updated_at']) ? $template['updated_at'] : null;
                    $is_default = isset($template['is_default']) ? (bool)$template['is_default'] : false;

                    // Extraire les données du template
                    $template_data = json_decode($template['template_data'] ?? '{}', true);
                    $template_category = $template_data['category'] ?? 'autre';

                    // Utiliser la catégorie stockée pour déterminer le type
                    $template_type = $template_category;

                    $button_text = '⚙️ Paramètres';
                    $button_action = 'openTemplateSettings';

                    // Déterminer l'icône et la description basée sur la catégorie du template
                    $icon = '📄'; // Default
                    $description = 'Template personnalisé';
                    $features = ['✓ Contenu personnalisable', '✓ Mise en page flexible', '✓ Éléments dynamiques', '✓ Export PDF'];

                    if ($template_type === 'facture') {
                        $icon = '🧾';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ En-tête société', '✓ Informations client', '✓ Tableau des articles', '✓ Totaux & TVA'];
                    } elseif ($template_type === 'devis') {
                        $icon = '📋';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ Présentation entreprise', '✓ Détails du projet', '✓ Conditions & validité', '✓ Signature numérique'];
                    } elseif ($template_type === 'commande') {
                        $icon = '📦';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ Numéro de commande', '✓ Liste des produits', '✓ Modalités de paiement', '✓ Conditions générales'];
                    } elseif ($template_type === 'contrat') {
                        $icon = '📑';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ Parties contractantes', '✓ Objet du contrat', '✓ Conditions & obligations', '✓ Clauses légales'];
                    } elseif ($template_type === 'newsletter') {
                        $icon = '📰';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ En-tête accrocheur', '✓ Sections d\'articles', '✓ Call-to-action', '✓ Pied de page'];
                    }

                    echo '<div class="template-card template-type-' . $template_type . '" style="border: 2px solid #dee2e6; border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -o-box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; -webkit-transition: all 0.3s ease; -moz-transition: all 0.3s ease; -o-transition: all 0.3s ease; cursor: pointer; min-height: 350px; position: relative;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.1)\';">';

                    // Conteneur pour organiser le contenu de la carte
                    echo '<div style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; flex-direction: column; -webkit-flex-direction: column; -moz-flex-direction: column; -ms-flex-direction: column; -o-flex-direction: column; height: 100%;">';

                    // Badge du type de template en haut à gauche

                    echo '<div class="default-template-icon" style="position: absolute; top: 10px; right: 10px; font-size: 20px; cursor: pointer; opacity: ' . ($is_default ? '1' : '0.5') . ';" onclick="toggleDefaultTemplate(' . $template_id . ', \'' . $template_type . '\', \'' . addslashes($template_name) . '\')" title="' . ($is_default ? 'Template par défaut' : 'Définir comme template par défaut') . '">';
                    echo $is_default ? '⭐' : '☆';
                    echo '</div>';

                    // Badge du type de template en haut à gauche
                    $type_colors = [
                        'facture' => '#007cba',
                        'devis' => '#28a745',
                        'commande' => '#ffc107',
                        'contrat' => '#dc3545',
                        'newsletter' => '#6f42c1',
                        'autre' => '#6c757d'
                    ];
                    $type_color = isset($type_colors[$template_type]) ? $type_colors[$template_type] : $type_colors['autre'];
                    $type_labels = [
                        'facture' => 'Facture',
                        'devis' => 'Devis',
                        'commande' => 'Commande',
                        'contrat' => 'Contrat',
                        'newsletter' => 'Newsletter',
                        'autre' => 'Autre'
                    ];
                    $type_label = isset($type_labels[$template_type]) ? $type_labels[$template_type] : $type_labels['autre'];

                    echo '<div class="template-type-badge" style="position: absolute; top: 10px; left: 10px; background: ' . $type_color . '; color: white; padding: 4px 8px; border-radius: 12px; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">';
                    echo $type_label;
                    echo '</div>';

                    echo '<div style="text-align: center; margin-bottom: 15px; margin-top: 40px;">';
                    echo '<div style="font-size: 3rem; margin-bottom: 10px;">' . $icon . '</div>';
                    echo '<h3 style="margin: 0; color: #23282d;">' . $template_name . '</h3>';
                    echo '<p style="color: #666; margin: 5px 0;">' . $description . '</p>';
                    echo '</div>';
                    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; -ms-border-radius: 4px; -o-border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666; flex-grow: 1; -webkit-flex-grow: 1; -moz-flex-grow: 1; -ms-flex-grow: 1; -o-flex-grow: 1;">';
                    foreach ($features as $feature) {
                        echo '<div>' . $feature . '</div>';
                    }
                    echo '</div>';
                    echo '<div style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; gap: 10px; margin-top: auto;">';
                    echo '<a href="' . admin_url('admin.php?page=pdf-builder-react-editor&template_id=' . $template_id) . '" class="button button-secondary" style="flex: 1; text-align: center; font-size: 16px;" title="Éditer ce template">✏️</a>';
                    echo '<button class="button button-secondary" style="flex: 1; font-size: 16px;" onclick="' . $button_action . '(' . $template_id . ', \'' . addslashes($template_name) . '\')" title="Paramètres">⚙️</button>';
                    echo '<button class="button button-primary" style="flex: 1; font-size: 16px;" onclick="duplicateTemplate(' . $template_id . ', \'' . addslashes($template_name) . '\')" title="Dupliquer ce template">📋</button>';
                    echo '<button class="button button-danger" style="flex: 1; font-size: 16px;" onclick="confirmDeleteTemplate(' . $template_id . ', \'' . addslashes($template_name) . '\')" title="Supprimer">🗑️</button>';
                    echo '</div>';
                    echo '</div>'; // Fermeture du conteneur flex
                    echo '</div>';
                }

                echo '</div>';
            } else {
                echo '<p>' . __('Template principal introuvable. Veuillez contacter l\'administrateur.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Modale de la galerie de modèles prédéfinis -->
        <div id="template-gallery-modal" class="template-gallery-modal template-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
            <div class="template-modal-content" style="background: #fff; border-radius: 12px; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; padding: 0; max-width: 1200px; width: 95%; max-height: 90vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4); -webkit-box-shadow: 0 20px 60px rgba(0,0,0,0.4); -moz-box-shadow: 0 20px 60px rgba(0,0,0,0.4); -ms-box-shadow: 0 20px 60px rgba(0,0,0,0.4); -o-box-shadow: 0 20px 60px rgba(0,0,0,0.4);">
                <div class="template-modal-header" style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; justify-content: space-between; -webkit-justify-content: space-between; -moz-justify-content: space-between; -ms-justify-content: space-between; -o-justify-content: space-between; align-items: center; -webkit-align-items: center; -moz-align-items: center; -ms-align-items: center; -o-align-items: center; padding: 25px 30px; border-bottom: 1px solid #e1e8ed; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div>
                        <h2 style="margin: 0; font-size: 24px; font-weight: 600;">🎨 Galerie de Modèles Prédéfinis</h2>
                        <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">Choisissez un modèle professionnel pour commencer</p>
                    </div>
                    <button onclick="closeTemplateGallery()" style="background: rgba(255,255,255,0.2); border: none; font-size: 24px; cursor: pointer; color: white; padding: 8px; border-radius: 50%; -webkit-border-radius: 50%; -moz-border-radius: 50%; -ms-border-radius: 50%; -o-border-radius: 50%; width: 40px; height: 40px; display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; align-items: center; -webkit-align-items: center; -moz-align-items: center; -ms-align-items: center; -o-align-items: center; justify-content: center; -webkit-justify-content: center; -moz-justify-content: center; -ms-justify-content: center; -o-justify-content: center;">×</button>
                </div>

                <div style="padding: 30px; max-height: calc(90vh - 120px); overflow-y: auto;">
                    <!-- Filtres de catégorie -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">Filtrer par catégorie</h3>
                        <div style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; gap: 10px; flex-wrap: wrap; -webkit-flex-wrap: wrap; -moz-flex-wrap: wrap; -ms-flex-wrap: wrap; -o-flex-wrap: wrap;">
                            <button class="gallery-filter-btn button button-secondary active" data-filter="all" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; -webkit-border-radius: 20px; -moz-border-radius: 20px; -ms-border-radius: 20px; -o-border-radius: 20px;">📄 Tous</button>
                            <button class="gallery-filter-btn button button-secondary" data-filter="facture" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; -webkit-border-radius: 20px; -moz-border-radius: 20px; -ms-border-radius: 20px; -o-border-radius: 20px; background: #007cba; border-color: #007cba; color: white;">🧾 Factures</button>
                            <button class="gallery-filter-btn button button-secondary" data-filter="devis" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; -webkit-border-radius: 20px; -moz-border-radius: 20px; -ms-border-radius: 20px; -o-border-radius: 20px; background: #28a745; border-color: #28a745; color: white;">📋 Devis</button>
                            <!-- Catégories désactivées temporairement -->
                            <!-- <button class="gallery-filter-btn button button-secondary" data-filter="commande" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #ffc107; border-color: #ffc107; color: #212529;">📦 Commandes</button> -->
                            <!-- <button class="gallery-filter-btn button button-secondary" data-filter="contrat" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #dc3545; border-color: #dc3545; color: white;">📑 Contrats</button> -->
                            <!-- <button class="gallery-filter-btn button button-secondary" data-filter="newsletter" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #6f42c1; border-color: #6f42c1; color: white;">📰 Newsletters</button> -->
                        </div>
                    </div>

                    <!-- Grille des modèles prédéfinis -->
                    <div id="predefined-templates-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">

                        <?php
                        // Charger les modèles prédéfinis depuis le dossier
                        $predefined_dir = plugin_dir_path(__FILE__) . '../predefined/';

                        // Créer le dossier s'il n'existe pas
                        if (!file_exists($predefined_dir)) {
                            wp_mkdir_p($predefined_dir);
                        }

                        $templates = [];

                        if (is_dir($predefined_dir)) {
                            $files = glob($predefined_dir . '*.json');
                            foreach ($files as $file) {
                                $slug = basename($file, '.json');
                                $content = file_get_contents($file);
                                $data = json_decode($content, true);

                                if ($data && isset($data['name'])) {
                                    $category = $data['category'] ?? 'autre';

                                    // Désactiver les modèles qui ne sont pas des devis ou factures
                                    if (!in_array($category, ['devis', 'facture'])) {
                                        continue;
                                    }

                                    $templates[] = [
                                        'slug' => $slug,
                                        'name' => $data['name'],
                                        'category' => $category,
                                        'description' => $data['description'] ?? '',
                                        'icon' => $data['icon'] ?? '📄',
                                        'preview_svg' => $data['preview_svg'] ?? ''
                                    ];
                                }
                            }
                        }

                        if (empty($templates)) : ?>
                            <!-- Aucun modèle prédéfini disponible pour le moment -->
                        <?php else : ?>
                            <?php foreach ($templates as $template) :
                                $type_colors = [
                                    'facture' => '#007cba',
                                    'devis' => '#28a745',
                                    'commande' => '#ffc107',
                                    'contrat' => '#dc3545',
                                    'newsletter' => '#6f42c1',
                                    'autre' => '#6c757d'
                                ];
                                $type_color = isset($type_colors[$template['category']]) ? $type_colors[$template['category']] : $type_colors['autre'];
                                ?>
                                <!-- Template <?php echo esc_attr($template['name']); ?> -->
                                <div class="predefined-template-card" data-category="<?php echo esc_attr($template['category']); ?>" style="border: 2px solid #e1e8ed; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';">
                                    <div style="height: 160px; background: linear-gradient(135deg, <?php echo $template['category'] === 'facture' ? '#667eea 0%, #764ba2 100%' : ($template['category'] === 'devis' ? '#28a745 0%, #20c997 100%' : '#6c757d 0%, #495057 100%'); ?>); display: flex; align-items: center; justify-content: center; position: relative;">
                                        <div style="font-size: 4rem; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?php echo esc_html($template['icon']); ?></div>
                                        <div style="position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: <?php echo $type_color; ?>; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;"><?php echo esc_html(strtoupper($template['category'])); ?></div>
                                    </div>
                                    <div style="padding: 20px;">
                                        <h3 style="margin: 0 0 10px 0; color: #23282d; font-size: 18px;"><?php echo esc_html($template['name']); ?></h3>
                                        <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;"><?php echo esc_html($template['description']); ?></p>
                                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                            <span style="background: #f0f8ff; color: <?php echo $type_color; ?>; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Template prédéfini</span>
                                            <span style="background: #f0f8ff; color: <?php echo $type_color; ?>; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Prêt à utiliser</span>
                                            <span style="background: #f0f8ff; color: <?php echo $type_color; ?>; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Personnalisable</span>
                                        </div>
                                        <button class="button button-primary" style="width: 100%; border-radius: 6px;" onclick="selectPredefinedTemplate('<?php echo esc_attr($template['slug']); ?>')">Charger dans l'Éditeur</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Modale des paramètres du template -->
        <div id="template-settings-modal" class="template-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 100; align-items: center; justify-content: center;">
            <div class="template-modal-content" style="background: #fff; border-radius: 8px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <div class="template-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                    <h2 id="template-settings-title" style="margin: 0; color: #23282d;">⚙️ Paramètres du Template</h2>
                    <button onclick="closeTemplateSettings()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0;">×</button>
                </div>

                <div id="template-settings-content">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #23282d;">Nom du template</label>
                        <input type="text" id="template-name-input" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #23282d;">Description</label>
                        <textarea id="template-description-input" rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 10px; color: #23282d;">Paramètres avancés</label>

                        <div style="margin-bottom: 15px;">
                            <label style="display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; -webkit-box-align: center; -webkit-align-items: center; -moz-box-align: center; -ms-flex-align: center; align-items: center; cursor: pointer;">
                                <input type="checkbox" id="template-public" style="margin-right: 8px;">
                                <span>Template public (visible par tous les utilisateurs)</span>
                            </label>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Format de papier</label>
                            <select id="template-paper-size" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <option value="A4">A4 (594 × 1123 px)</option>
                                <option value="A3">A3 (840 × 1191 px)</option>
                                <option value="Letter">Letter (612 × 792 px)</option>
                                <option value="Legal">Legal (612 × 1008 px)</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Orientation</label>
                            <select id="template-orientation" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Paysage</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 5px; color: #23282d;">Catégorie</label>
                        <select id="template-category" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="facture">Facture</option>
                            <option value="devis">Devis</option>
                            <option value="commande">Bon de commande</option>
                            <option value="contrat">Contrat</option>
                            <option value="newsletter">Newsletter</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                </div>

                <div class="template-modal-footer" style="display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; -webkit-box-pack: end; -webkit-justify-content: flex-end; -moz-box-pack: end; -ms-flex-pack: end; justify-content: flex-end; -webkit-gap: 10px; -moz-gap: 10px; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
                    <button onclick="closeTemplateSettings()" class="button button-secondary">Annuler</button>
                    <button onclick="saveTemplateSettings()" class="button button-primary">💾 Enregistrer</button>
                </div>
            </div>
        </div>

        <div id="no-templates" style="display: none; text-align: center; padding: 40px; color: #666;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">[DOC]</div>
            <h3><?php _e('Aucun template trouvé', 'pdf-builder-pro'); ?></h3>
            <p><?php _e('Créez votre premier template pour commencer à concevoir des PDF personnalisés.', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<script>
let currentTemplateId = null;

// Définir ajaxurl si pas déjà défini (nécessaire pour l'admin WordPress)
if (typeof ajaxurl === 'undefined') {
    ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
}

// Définir le nonce pour les templates
var pdfBuilderTemplatesNonce = '<?php echo wp_create_nonce("pdf_builder_templates"); ?>';

function openTemplateSettings(templateId, templateName) {
    currentTemplateId = templateId;

    // Afficher la modale des paramètres
    document.getElementById('template-settings-modal').style.display = 'flex';

    // Remettre le bouton d'enregistrement à son état initial
    const saveButton = document.querySelector('.template-modal-footer .button-primary');
    if (saveButton) {
        saveButton.innerHTML = '💾 Enregistrer';
        saveButton.style.background = '';
        saveButton.disabled = false;
    }

    // Pré-remplir les champs avec les données actuelles
    document.getElementById('template-name-input').value = templateName;

    // Charger les paramètres depuis la base de données via AJAX
    loadTemplateSettings(templateId);
}

function closeTemplateSettings() {
    document.getElementById('template-settings-modal').style.display = 'none';
    currentTemplateId = null;
}

function updateTemplateTitle() {
    const categorySelect = document.getElementById('template-category');
    const titleElement = document.getElementById('template-settings-title');
    
    if (!categorySelect || !titleElement) return;
    
    const category = categorySelect.value;
    const categoryLabels = {
        'facture': 'Facture',
        'devis': 'Devis',
        'commande': 'Bon de Commande',
        'contrat': 'Contrat',
        'newsletter': 'Newsletter',
        'autre': 'Template'
    };
    
    const categoryLabel = categoryLabels[category] || 'Template';
    titleElement.innerHTML = `⚙️ Paramètres du ${categoryLabel}`;
    
    // Mettre à jour aussi le badge de la carte template correspondante
    updateTemplateCardBadge(category);
}

function updateTemplateCardBadge(category) {
    // Trouver la carte template correspondante
    const templateCard = document.querySelector(`.template-card [onclick*="${currentTemplateId}"]`);
    if (!templateCard) return;
    
    const card = templateCard.closest('.template-card');
    if (!card) return;
    
    // Mettre à jour le badge
    const badge = card.querySelector('.template-type-badge');
    if (!badge) return;
    
    const categoryColors = {
        'facture': '#007cba',
        'devis': '#28a745',
        'commande': '#ffc107',
        'contrat': '#dc3545',
        'newsletter': '#6f42c1',
        'autre': '#6c757d'
    };
    
    const categoryLabels = {
        'facture': 'Facture',
        'devis': 'Devis',
        'commande': 'Commande',
        'contrat': 'Contrat',
        'newsletter': 'Newsletter',
        'autre': 'Autre'
    };
    
    const color = categoryColors[category] || categoryColors['autre'];
    const label = categoryLabels[category] || categoryLabels['autre'];
    
    badge.style.background = color;
    badge.textContent = label;
    
    // Mettre à jour aussi l'icône si elle existe
    const iconElement = card.querySelector('div[style*="font-size: 3rem"]');
    if (iconElement) {
        const categoryIcons = {
            'facture': '🧾',
            'devis': '📋',
            'commande': '📦',
            'contrat': '📑',
            'newsletter': '📰',
            'autre': '📄'
        };
        iconElement.textContent = categoryIcons[category] || categoryIcons['autre'];
    }
    
    // Mettre à jour la classe CSS de la carte pour le filtrage
    // Supprimer toutes les classes template-type-*
    card.classList.forEach(className => {
        if (className.startsWith('template-type-')) {
            card.classList.remove(className);
        }
    });
    // Ajouter la nouvelle classe
    card.classList.add('template-type-' + category);
}

function loadTemplateSettings(templateId) {
    // Faire l'appel AJAX pour charger les paramètres
    jQuery.post(ajaxurl, {
        action: 'pdf_builder_load_template_settings',
        template_id: templateId,
        nonce: pdfBuilderTemplatesNonce
    }, function(response) {
        if (response.success) {
            // Remplir les champs avec les données chargées
            const settings = response.data;
            document.getElementById('template-name-input').value = settings.name || '';
            document.getElementById('template-description-input').value = settings.description || '';
            document.getElementById('template-category').value = settings.category || 'autre';
            document.getElementById('template-public').checked = settings.is_public || false;
            document.getElementById('template-paper-size').value = settings.paper_size || 'A4';
            document.getElementById('template-orientation').value = settings.orientation || 'portrait';
            
            // Mettre à jour le titre en fonction de la catégorie
            updateTemplateTitle();
            
            // Ajouter un event listener pour mettre à jour le titre en temps réel
            document.getElementById('template-category').addEventListener('change', updateTemplateTitle);
        } else {
            // Erreur - utiliser des valeurs par défaut

            document.getElementById('template-description-input').value = 'Description du template...';
            document.getElementById('template-public').checked = false;
            document.getElementById('template-paper-size').value = 'A4';
            document.getElementById('template-orientation').value = 'portrait';

            // Déterminer la catégorie basée sur le nom
            const templateName = document.getElementById('template-name-input').value.toLowerCase();
            let category = 'autre';
            if (templateName.includes('facture')) category = 'facture';
            else if (templateName.includes('devis')) category = 'devis';
            else if (templateName.includes('commande')) category = 'commande';
            else if (templateName.includes('contrat')) category = 'contrat';
            else if (templateName.includes('newsletter')) category = 'newsletter';

            document.getElementById('template-category').value = category;
            
            // Mettre à jour le titre en fonction de la catégorie
            updateTemplateTitle();
            
            // Ajouter un event listener pour mettre à jour le titre en temps réel
            document.getElementById('template-category').addEventListener('change', updateTemplateTitle);
        }
    }).fail(function(xhr, status, error) {

        // Valeurs par défaut en cas d'erreur
        document.getElementById('template-description-input').value = 'Description du template...';
        document.getElementById('template-public').checked = false;
        document.getElementById('template-paper-size').value = 'A4';
        document.getElementById('template-orientation').value = 'portrait';
        document.getElementById('template-category').value = 'autre';
    });
}

function saveTemplateSettings() {
    const settings = {
        template_id: currentTemplateId,
        name: document.getElementById('template-name-input').value,
        description: document.getElementById('template-description-input').value,
        is_public: document.getElementById('template-public').checked ? 1 : 0,
        paper_size: document.getElementById('template-paper-size').value,
        orientation: document.getElementById('template-orientation').value,
        category: document.getElementById('template-category').value
    };

    // Afficher un indicateur de chargement
    const saveButton = document.querySelector('.template-modal-footer .button-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '⏳ Sauvegarde...';
    saveButton.disabled = true;

    // Faire l'appel AJAX
    jQuery.post(ajaxurl, {
        action: 'pdf_builder_save_template_settings',
        nonce: pdfBuilderTemplatesNonce,
        ...settings
    }, function(response) {
        if (response.success) {
            // Afficher un message de succès
            saveButton.innerHTML = '✅ Enregistré !';
            saveButton.style.background = '#28a745';

            // Fermer la modale après un délai
            setTimeout(() => {
                closeTemplateSettings();
                // Afficher un message de succès sans recharger la page
                showSuccessMessage('Paramètres du template sauvegardés avec succès !');
            }, 1500);
        } else {
            // Erreur
            saveButton.innerHTML = '❌ Erreur';
            saveButton.style.background = '#dc3545';
            alert('Erreur lors de la sauvegarde: ' + ((response.data && response.data.message) || 'Erreur inconnue'));

            // Remettre le bouton normal après un délai
            setTimeout(() => {
                saveButton.innerHTML = originalText;
                saveButton.style.background = '';
                saveButton.disabled = false;
            }, 3000);
        }
    }).fail(function(xhr, status, error) {
        // Erreur de réseau
        saveButton.innerHTML = '❌ Erreur réseau';
        saveButton.style.background = '#dc3545';
        alert('Erreur de connexion: ' + error);

        // Remettre le bouton normal
        setTimeout(() => {
            saveButton.innerHTML = originalText;
            saveButton.style.background = '';
            saveButton.disabled = false;
        }, 3000);
    });
}

function confirmDeleteTemplate(templateId, templateName) {
    if (confirm('Êtes-vous sûr de vouloir supprimer le template "' + templateName + '" ?\n\n⚠️ Cette action est irréversible.')) {
        deleteTemplate(templateId, templateName);
    }
}

function deleteTemplate(templateId, templateName) {
    // Désactiver le bouton pendant la suppression
    const deleteButton = event.target;
    const originalText = deleteButton.innerHTML;
    deleteButton.disabled = true;
    deleteButton.innerHTML = '⏳ Suppression...';

    // Trouver la carte template parente
    const templateCard = deleteButton.closest('.template-card');

    // Faire l'appel AJAX pour supprimer le template
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'pdf_builder_delete_template',
            template_id: templateId,
            nonce: pdfBuilderTemplatesNonce
        },
        success: function(response) {
            if (response.success) {
                // Succès - supprimer la carte du DOM avec animation
                templateCard.style.transition = '-webkit-transition: all 0.3s ease; -moz-transition: all 0.3s ease; -o-transition: all 0.3s ease; transition: all 0.3s ease';
                templateCard.style.opacity = '0';
                templateCard.style.transform = '-webkit-transform: scale(0.95); -moz-transform: scale(0.95); -ms-transform: scale(0.95); -o-transform: scale(0.95); transform: scale(0.95)';

                setTimeout(() => {
                    templateCard.remove();
                }, 300);

                // Afficher un message de succès temporaire
                showSuccessMessage('✅ Template "' + templateName + '" supprimé avec succès !');
            } else {
                // Erreur
                deleteButton.innerHTML = '❌ Erreur';
                deleteButton.style.background = '#dc3545';
                alert('Erreur lors de la suppression: ' + (response.data.message || 'Erreur inconnue'));

                // Remettre le bouton normal après un délai
                setTimeout(() => {
                    deleteButton.innerHTML = originalText;
                    deleteButton.style.background = '';
                    deleteButton.disabled = false;
                }, 3000);
            }
        },
        error: function(xhr, status, error) {
            // Erreur de connexion
            deleteButton.innerHTML = '❌ Erreur';
            deleteButton.style.background = '#dc3545';
            alert('Erreur de connexion lors de la suppression');

            // Remettre le bouton normal après un délai
            setTimeout(() => {
                deleteButton.innerHTML = originalText;
                deleteButton.style.background = '';
                deleteButton.disabled = false;
            }, 3000);
        }
    });
}

function toggleDefaultTemplate(templateId, templateType, templateName) {
    // Trouver l'icône du template actuel en utilisant l'attribut onclick
    const currentIcon = Array.from(document.querySelectorAll('.default-template-icon')).find(icon =>
        icon.onclick && icon.onclick.toString().includes(`toggleDefaultTemplate(${templateId}`)
    );
    const isCurrentlyDefault = currentIcon && currentIcon.style.opacity === '1';

    // Préparer les données pour AJAX
    const data = {
        action: 'pdf_builder_set_default_template',
        template_id: templateId,
        is_default: isCurrentlyDefault ? 0 : 1,
        nonce: pdfBuilderTemplatesNonce
    };

    // Afficher un indicateur de chargement
    if (currentIcon) {
        currentIcon.style.pointerEvents = 'none';
        currentIcon.innerHTML = '⏳';
    }

    // Faire l'appel AJAX
    jQuery.post(ajaxurl, data, function(response) {
        // AJAX success
        if (response.success) {
            // Mettre à jour l'icône et le titre
            if (currentIcon) {
                const newIsDefault = !isCurrentlyDefault;
                currentIcon.innerHTML = newIsDefault ? '⭐' : '☆';
                currentIcon.style.opacity = newIsDefault ? '1' : '0.5';
                currentIcon.title = newIsDefault ? 'Template par défaut' : 'Définir comme template par défaut';
                currentIcon.style.pointerEvents = 'auto';
            }

            // Afficher un message de succès temporaire
            showSuccessMessage(response.data.message);

            // Mettre à jour les autres icônes pour retirer le statut par défaut (seulement du même type)
            if (!isCurrentlyDefault) {
                document.querySelectorAll('.default-template-icon').forEach(icon => {
                    // Vérifier si cette icône appartient à un template du même type
                    if (icon !== currentIcon && icon.onclick && icon.onclick.toString().includes(`'${templateType}'`)) {
                        icon.innerHTML = '☆';
                        icon.style.opacity = '0.5';
                        icon.title = 'Définir comme template par défaut';
                    }
                });
            }
        } else {
            // Erreur - garder l'icône décochée si c'était une tentative de définition par défaut
            if (currentIcon) {
                currentIcon.innerHTML = '☆';
                currentIcon.style.opacity = '0.5';
                currentIcon.title = 'Définir comme template par défaut';
                currentIcon.style.pointerEvents = 'auto';
            }
            showErrorMessage((response.data && response.data.message) || 'Erreur lors de la modification du statut par défaut');
        }
    }).fail(function(xhr, status, error) {
        // AJAX failed
        // Erreur de réseau
        if (currentIcon) {
            currentIcon.innerHTML = isCurrentlyDefault ? '*' : '-';
            currentIcon.style.pointerEvents = 'auto';
        }
        showErrorMessage('Erreur de connexion: ' + error);
    });
}

// Fonction pour afficher un message de succès temporaire
function showSuccessMessage(message) {
    // Supprimer les anciens messages
    const existingMessages = document.querySelectorAll('.pdf-builder-message');
    existingMessages.forEach(msg => msg.remove());

    // Créer le nouveau message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'pdf-builder-message success';
    messageDiv.style.cssText = `
        position: fixed;
        top: 40px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 12px 20px;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        -ms-border-radius: 4px;
        -o-border-radius: 4px;
        border-radius: 4px;
        -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        -o-box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        z-index: 100;
        font-weight: bold;
        max-width: 400px;
    `;
    messageDiv.innerHTML = '✅ ' + message;

    document.body.appendChild(messageDiv);

    // Faire disparaître le message après 3 secondes
    setTimeout(() => {
        messageDiv.style.transition = '-webkit-transition: opacity 0.5s; -moz-transition: opacity 0.5s; -o-transition: opacity 0.5s; transition: opacity 0.5s';
        messageDiv.style.opacity = '0';
        setTimeout(() => messageDiv.remove(), 500);
    }, 3000);
}

// Fonction pour afficher un message d'erreur temporaire
function showErrorMessage(message) {
    // Supprimer les anciens messages
    const existingMessages = document.querySelectorAll('.pdf-builder-message');
    existingMessages.forEach(msg => msg.remove());

    // Créer le nouveau message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'pdf-builder-message error';
    messageDiv.style.cssText = `
        position: fixed;
        top: 40px;
        right: 20px;
        background: #dc3545;
        color: white;
        padding: 12px 20px;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        -ms-border-radius: 4px;
        -o-border-radius: 4px;
        border-radius: 4px;
        -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        -o-box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        z-index: 100;
        font-weight: bold;
        max-width: 400px;
    `;
    messageDiv.innerHTML = '❌ ' + message;

    document.body.appendChild(messageDiv);

    // Faire disparaître le message après 5 secondes
    setTimeout(() => {
        messageDiv.style.transition = '-webkit-transition: opacity 0.5s; -moz-transition: opacity 0.5s; -o-transition: opacity 0.5s; transition: opacity 0.5s';
        messageDiv.style.opacity = '0';
        setTimeout(() => messageDiv.remove(), 500);
    }, 5000);
}

// Fonction de filtrage des templates
function filterTemplates(filterType) {
    const cards = document.querySelectorAll('.template-card');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    // Mettre à jour les boutons actifs
    filterButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-filter') === filterType) {
            btn.classList.add('active');
        }
    });
    
    // Filtrer les cartes
    cards.forEach(card => {
        if (filterType === 'all') {
            card.style.display = 'block';
        } else {
            if (card.classList.contains('template-type-' + filterType)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        }
    });
}

// Fonction pour dupliquer un template
function duplicateTemplate(templateId, templateName) {
    // Afficher un indicateur de chargement
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '⏳ Duplication...';
    button.disabled = true;

    // Demander le nom du nouveau template
    const newTemplateName = prompt('Entrez le nom du template dupliqué :', templateName + ' (Copie)');

    if (!newTemplateName || newTemplateName.trim() === '') {
        // Annuler si pas de nom
        button.innerHTML = originalText;
        button.disabled = false;
        return;
    }

    // Faire l'appel AJAX pour dupliquer le template
    jQuery.post(ajaxurl, {
        action: 'pdf_builder_duplicate_template',
        nonce: pdfBuilderTemplatesNonce,
        template_id: templateId,
        template_name: newTemplateName.trim()
    }, function(response) {
        if (response.success) {
            // Succès - afficher un message et recharger la page
            showSuccessMessage('Template "' + newTemplateName + '" dupliqué avec succès !');

            // Recharger la page après un délai pour afficher le nouveau template
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            // Erreur
            showErrorMessage((response.data && response.data.message) || 'Erreur lors de la duplication du template');

            // Remettre le bouton normal
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }).fail(function(xhr, status, error) {
        // Erreur de réseau
        showErrorMessage('Erreur de connexion: ' + error);

        // Remettre le bouton normal
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Fonction pour sélectionner un template prédéfini
function selectPredefinedTemplate(templateSlug) {
    // Afficher un indicateur de chargement
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '⏳ Chargement...';
    button.disabled = true;

    // Faire l'appel AJAX pour charger le modèle prédéfini dans le template ID 1
    jQuery.post(ajaxurl, {
        action: 'pdf_builder_load_predefined_into_editor',
        nonce: pdfBuilderTemplatesNonce,
        template_slug: templateSlug
    }, function(response) {
        if (response.success) {
            // Succès - afficher un message et rediriger vers l'éditeur
            showSuccessMessage('Modèle prédéfini chargé dans l\'éditeur ! Redirection...');

            // Fermer la modale de la galerie
            closeTemplateGallery();

            // Rediriger vers l'éditeur après un court délai
            setTimeout(function() {
                window.location.href = response.data.redirect_url;
            }, 1500);
        } else {
            // Erreur
            showErrorMessage(response.data?.message || 'Erreur lors du chargement du modèle prédéfini');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }).fail(function() {
        showErrorMessage('Erreur de communication avec le serveur');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}



// Function to close template gallery (for HTML onclick)
function closeTemplateGallery() {
    jQuery('.template-gallery-modal').fadeOut(300);
}

// Function to open template gallery
function openTemplateGallery() {
    document.getElementById('template-gallery-modal').style.display = 'flex';
    // Initialize gallery filters
    initializeGalleryFilters();
}

// Function to initialize gallery filters
function initializeGalleryFilters() {
    const filterButtons = document.querySelectorAll('.gallery-filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            const filterType = this.getAttribute('data-filter');
            filterGalleryTemplates(filterType);
        });
    });
}

// Function to filter gallery templates
function filterGalleryTemplates(filterType) {
    const templates = document.querySelectorAll('.predefined-template-card');

    templates.forEach(template => {
        const category = template.getAttribute('data-category');

        if (filterType === 'all' || category === filterType) {
            template.style.display = 'block';
        } else {
            template.style.display = 'none';
        }
    });
}

// Initialiser le filtrage au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter les event listeners aux boutons de filtrage
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter');
            filterTemplates(filterType);
        });
    });

    // Event listener pour ouvrir la galerie de modèles
    const openGalleryBtn = document.getElementById('open-template-gallery');
    if (openGalleryBtn) {
        // Supprimé - géré plus bas avec logique freemium
        // openGalleryBtn.addEventListener('click', openTemplateGallery);
    }
});

// Fonction pour afficher les modals d'upgrade
function showUpgradeModal(type) {
    if (type === 'gallery') {
        document.getElementById('upgrade-modal-gallery').style.display = 'flex';
    } else if (type === 'template' || type === 'template_limit') {
        document.getElementById('upgrade-modal-template').style.display = 'flex';
    }
}

// Fermer la galerie de modèles en cliquant en dehors
document.getElementById('template-gallery-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTemplateGallery();
    }
});

// Fermer avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('template-settings-modal').style.display === 'flex') {
            closeTemplateSettings();
        }
        if (document.getElementById('template-gallery-modal').style.display === 'flex') {
            closeTemplateGallery();
        }
    }
});


</script>

<style>
.template-modal {
    animation: modalFadeIn 0.3s ease-out;
}

.template-modal-content {
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        -webkit-transform: translateY(-50px) scale(0.9);
        -moz-transform: translateY(-50px) scale(0.9);
        -ms-transform: translateY(-50px) scale(0.9);
        -o-transform: translateY(-50px) scale(0.9);
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        -webkit-transform: translateY(0) scale(1);
        -moz-transform: translateY(0) scale(1);
        -ms-transform: translateY(0) scale(1);
        -o-transform: translateY(0) scale(1);
        transform: translateY(0) scale(1);
    }
}

.template-modal-content input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.template-modal-content select:hover,
.template-modal-content input:hover,
.template-modal-content textarea:hover {
    border-color: #007cba;
}

.template-modal-content select:focus,
.template-modal-content input:focus,
.template-modal-content textarea:focus {
    border-color: #007cba;
    -webkit-box-shadow: 0 0 0 1px #007cba;
    -moz-box-shadow: 0 0 0 1px #007cba;
    -ms-box-shadow: 0 0 0 1px #007cba;
    -o-box-shadow: 0 0 0 1px #007cba;
    box-shadow: 0 0 0 1px #007cba;
    outline: none;
}

.button-danger {
    background: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #fff !important;
}

.button-danger:hover {
    background: #c82333 !important;
    border-color: #bd2130 !important;
}



.gallery-filter-btn.active {
    -webkit-box-shadow: 0 0 0 2px rgba(0,123,186,0.5) !important;
    -moz-box-shadow: 0 0 0 2px rgba(0,123,186,0.5) !important;
    -ms-box-shadow: 0 0 0 2px rgba(0,123,186,0.5) !important;
    -o-box-shadow: 0 0 0 2px rgba(0,123,186,0.5) !important;
    box-shadow: 0 0 0 2px rgba(0,123,186,0.5) !important;
    font-weight: bold !important;
}


</style>

<!-- Modal d'upgrade pour templates freemium -->
<div id="upgrade-modal-template" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div class="modal-header" style="padding: 20px 30px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #23282d; font-size: 24px;">🚀 Débloquer la Création de Templates</h3>
            <button class="modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 30px;">
            <div class="upgrade-feature" style="text-align: center; margin-bottom: 30px;">
                <div class="feature-icon" style="font-size: 64px; margin-bottom: 20px;">🎨</div>
                <h4 style="color: #23282d; font-size: 20px; margin-bottom: 15px;">Templates Illimités & Personnalisés</h4>
                <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                    Créez autant de templates PDF que vous voulez avec votre propre design et branding.
                </p>
                <ul style="text-align: left; background: #f8f9fa; padding: 20px; border-radius: 8px; list-style: none; margin: 0;">
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Templates personnalisés illimités</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Import/Export de templates</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Thèmes CSS avancés</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Variables dynamiques premium</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Support prioritaire</strong></li>
                </ul>
            </div>
            <div class="pricing" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 8px; color: white;">
                <div class="price" style="font-size: 36px; font-weight: bold; margin-bottom: 10px;">69€ <span style="font-size: 16px; font-weight: normal;">à vie</span></div>
                <p style="margin: 10px 0 20px 0; opacity: 0.9;">Paiement unique, pas d'abonnement</p>
                <a href="https://threeaxe.fr/contact/?subject=Upgrade%20PDF%20Builder%20Pro" class="button button-primary" style="background: white; color: #667eea; border: none; padding: 12px 30px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; border-radius: 6px;">
                    <span class="dashicons dashicons-cart" style="margin-right: 5px;"></span>
                    Commander Maintenant
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'upgrade pour galerie de modèles -->
<div id="upgrade-modal-gallery" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div class="modal-header" style="padding: 20px 30px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #23282d; font-size: 24px;">🎨 Modèles Prédéfinis Premium</h3>
            <button class="modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 30px;">
            <div class="upgrade-feature" style="text-align: center; margin-bottom: 30px;">
                <div class="feature-icon" style="font-size: 64px; margin-bottom: 20px;">🖼️</div>
                <h4 style="color: #23282d; font-size: 20px; margin-bottom: 15px;">Galerie de Modèles Professionnels</h4>
                <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                    Accédez à notre collection de templates professionnels prédéfinis pour factures, devis et plus encore.
                </p>
                <ul style="text-align: left; background: #f8f9fa; padding: 20px; border-radius: 8px; list-style: none; margin: 0;">
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>10+ templates professionnels</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Factures, devis, contrats</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Designs modernes et élégants</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Prêts à personnaliser</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">✅ <strong>Mises à jour régulières</strong></li>
                </ul>
            </div>
            <div class="pricing" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 8px; color: white;">
                <div class="price" style="font-size: 36px; font-weight: bold; margin-bottom: 10px;">69€ <span style="font-size: 16px; font-weight: normal;">à vie</span></div>
                <p style="margin: 10px 0 20px 0; opacity: 0.9;">Paiement unique, pas d'abonnement</p>
                <a href="https://threeaxe.fr/contact/?subject=Upgrade%20PDF%20Builder%20Pro" class="button button-primary" style="background: white; color: #667eea; border: none; padding: 12px 30px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; border-radius: 6px;">
                    <span class="dashicons dashicons-cart" style="margin-right: 5px;"></span>
                    Commander Maintenant
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour afficher modal upgrade
function showUpgradeModal(reason) {
    const modal = document.getElementById('upgrade-modal-' + reason);
    if (modal) {
        modal.style.display = 'flex';

        // Tracking pour analytics (si disponible)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'upgrade_modal_shown', {
                'reason': reason,
                'user_type': 'free',
                'page': 'templates'
            });
        }
    }
}

// Gestionnaire pour bouton créer template
document.getElementById('create-template-btn')?.addEventListener('click', function(e) {
    e.preventDefault();

    // Vérifier limite côté client (sécurité supplémentaire)
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'pdf_builder_check_template_limit',
            'nonce': pdfBuilderAjax.nonce
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.can_create) {
            // Rediriger vers éditeur
            window.location.href = pdfBuilderAjax.editor_url;
        } else {
            showUpgradeModal('template');
        }
    })
    .catch(error => {
        console.error('Erreur vérification limite:', error);
        showUpgradeModal('template');
    });
});

// Gestionnaire pour bouton galerie de modèles (uniquement premium)
document.getElementById('open-template-gallery')?.addEventListener('click', function(e) {
    e.preventDefault();
    <?php if ($is_premium): ?>
        // Ouvrir la galerie pour utilisateurs premium
        document.getElementById('template-gallery-modal').style.display = 'flex';
    <?php else: ?>
        // Montrer modal upgrade pour utilisateurs gratuits
        showUpgradeModal('gallery');
    <?php endif; ?>
});

// Fermer modal au clic sur overlay ou bouton close
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay') || e.target.classList.contains('modal-close')) {
        e.target.closest('.modal-overlay').style.display = 'none';
    }
});
</script> 
 
