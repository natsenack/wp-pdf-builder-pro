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
    $template_defaults_path = dirname(__DIR__, 2) . '/src/Core/TemplateDefaults.php';
    if (file_exists($template_defaults_path)) {
        require_once $template_defaults_path;
    } else {
        // Fallback: essayer avec plugin_dir_path si disponible
        if (defined('PDF_BUILDER_PLUGIN_DIR')) {
            $template_defaults_path = PDF_BUILDER_PLUGIN_DIR . 'src/Core/TemplateDefaults.php';
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
    // Recharger le compteur après création
    $templates_count = \PDF_Builder\Admin\PdfBuilderAdmin::count_user_templates(get_current_user_id());
}

// Récupérer les DPI autorisés depuis les paramètres du plugin
$allowed_dpis = get_option('pdf_builder_canvas_allowed_dpis', ['96', '150', '300']);
if (!is_array($allowed_dpis)) {
    $allowed_dpis = ['96', '150', '300'];
}

// Récupérer les orientations autorisées depuis les paramètres du plugin
$allowed_orientations = get_option('pdf_builder_canvas_allowed_orientations', ['portrait']);
if (!is_array($allowed_orientations)) {
    $allowed_orientations = ['portrait'];
}

// Récupérer les formats autorisés depuis les paramètres du plugin
$allowed_formats = get_option('pdf_builder_canvas_allowed_formats', ['A4']);
if (!is_array($allowed_formats)) {
    $allowed_formats = ['A4'];
}

// Fonction pour récupérer les options de format avec leurs labels
function get_format_options() {
    return [
        'A4' => 'A4 (210×297mm)',
        'A3' => 'A3 (297×420mm)',
        'Letter' => 'Letter (8.5×11")',
        'Legal' => 'Legal (8.5×14")',
        'EtiquetteColis' => 'Étiquette Colis (10×15cm)'
    ];
}

// Fonction pour calculer les dimensions du canvas en pixels selon format, DPI et orientation
function calculate_canvas_dimensions($format, $dpi, $orientation) {
    // Dimensions physiques des formats en millimètres
    $format_dimensions = [
        'A4' => ['width' => 210, 'height' => 297],
        'A3' => ['width' => 297, 'height' => 420],
        'Letter' => ['width' => 215.9, 'height' => 279.4], // 8.5 x 11 pouces en mm
        'Legal' => ['width' => 215.9, 'height' => 355.6],  // 8.5 x 14 pouces en mm
        'EtiquetteColis' => ['width' => 100, 'height' => 150] // 10 x 15 cm en mm
    ];
    
    if (!isset($format_dimensions[$format])) {
        $format = 'A4'; // Format par défaut
    }
    
    $dimensions = $format_dimensions[$format];
    
    // Conversion mm vers pouces (1 pouce = 25.4 mm)
    $width_inches = $dimensions['width'] / 25.4;
    $height_inches = $dimensions['height'] / 25.4;
    
    // Calcul des dimensions en pixels
    $width_px = round($width_inches * $dpi);
    $height_px = round($height_inches * $dpi);
    
    // Inversion si orientation paysage
    if ($orientation === 'landscape') {
        return ['width' => $height_px, 'height' => $width_px];
    }
    
    return ['width' => $width_px, 'height' => $height_px];
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
    <h1><?php _e('📄 Gestion des Templates PDF', 'pdf-builder-pro'); ?></h1>

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
                <button class="button" id="upgrade-required-btn"
                        onclick="showTemplateLimitNotice(); showUpgradeModal('template')"
                        style="background-color: #dc3545; border-color: #dc3545; color: white;">
                    <span class="dashicons dashicons-lock"></span>
                    <?php _e('Créer un Template (Premium)', 'pdf-builder-pro'); ?>
                </button>
            <?php endif; ?>

            <button id="open-template-gallery" class="button" style="margin-left: 10px;" onclick="showTemplateLimitNotice(); showUpgradeModal('gallery')">
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
            <div id="template-limit-notice" class="notice notice-info" style="margin: 15px 0; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; position: relative;">
                <a href="#" onclick="dismissTemplateLimitNotice(); return false;" style="position: absolute; top: 5px; right: 5px; color: #dc3545; font-size: 20px; font-weight: bold; text-decoration: none; line-height: 1;" title="Fermer">
                    ✕
                </a>
                <h4 style="margin: 0 0 10px 0; color: #0c5460;">
                    <span class="dashicons dashicons-info" style="margin-right: 5px;"></span>
                    <?php _e('Limite de Templates Atteinte', 'pdf-builder-pro'); ?>
                </h4>
                <p style="margin: 0 0 10px 0; color: #0c5460;">
                    <?php printf(
                        __('Vous avez créé %d template gratuit sur 1. Passez en Premium pour créer des templates illimités !', 'pdf-builder-pro'),
                        $templates_count
                    ); ?>
                </p>
                <a href="#" onclick="showUpgradeModal('template')" class="button button-primary">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php _e('Passer en Premium - 69€ à vie', 'pdf-builder-pro'); ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Message pour utilisateurs gratuits sans templates -->
        <?php if (!$is_premium && $templates_count === 0): ?>
            <!-- Message supprimé selon demande utilisateur -->
        <?php endif; ?>

        <!-- Section de filtrage -->
        <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; border: 1px solid #dee2e6;">
            <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">Filtrer par type</h3>
            <div style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; gap: 10px; flex-wrap: wrap; -webkit-flex-wrap: wrap; -moz-flex-wrap: wrap; -ms-flex-wrap: wrap; -o-flex-wrap: wrap;">
                <button class="filter-btn button active" data-filter="all" style="font-size: 12px; padding: 6px 12px; background: #6c757d; border-color: #6c757d; color: white;">📄 Tous</button>
                <button class="filter-btn button" data-filter="facture" style="font-size: 12px; padding: 6px 12px; background: #e3f2fd; border-color: #2196f3; color: #1565c0;">🧾 Factures</button>
                <button class="filter-btn button" data-filter="devis" style="font-size: 12px; padding: 6px 12px; background: #e8f5e8; border-color: #4caf50; color: #2e7d32;">📋 Devis</button>
                <button class="filter-btn button" data-filter="commande" style="font-size: 12px; padding: 6px 12px; background: #fff3e0; border-color: #ff9800; color: #e65100;">📦 Commandes</button>
                <button class="filter-btn button" data-filter="contrat" style="font-size: 12px; padding: 6px 12px; background: #ffebee; border-color: #f44336; color: #b71c1c;">📑 Contrats</button>
                <button class="filter-btn button" data-filter="newsletter" style="font-size: 12px; padding: 6px 12px; background: #f3e5f5; border-color: #9c27b0; color: #6a1b9a;">📰 Newsletters</button>
                <button class="filter-btn button" data-filter="autre" style="font-size: 12px; padding: 6px 12px; background: #f5f5f5; border-color: #9e9e9e; color: #424242;">📄 Autres</button>
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

            // Vérifier si la colonne thumbnail_url existe
            $columns = $wpdb->get_results("DESCRIBE $table_templates");
            $thumbnail_column_exists = false;
            foreach ($columns as $column) {
                if ($column->Field === 'thumbnail_url') {
                    $thumbnail_column_exists = true;
                    break;
                }
            }

            // Adapter la requête selon la disponibilité de la colonne
            if ($thumbnail_column_exists) {
                $templates = $wpdb->get_results("SELECT id, name, thumbnail_url, created_at, updated_at, is_default, template_data FROM $table_templates ORDER BY id", ARRAY_A);
            } else {
                $templates = $wpdb->get_results("SELECT id, name, '' as thumbnail_url, created_at, updated_at, is_default, template_data FROM $table_templates ORDER BY id", ARRAY_A);
            }

            if (!empty($templates)) {
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">';

                foreach ($templates as $template) {
                    $template_id = $template['id'];
                    $template_name = esc_html($template['name']);
                    $thumbnail_url = isset($template['thumbnail_url']) ? $template['thumbnail_url'] : '';
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

                    echo '<div class="template-card template-type-' . $template_type . '" data-category="' . $template_type . '" style="border: 2px solid #dee2e6; border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -o-box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; -webkit-transition: all 0.3s ease; -moz-transition: all 0.3s ease; -o-transition: all 0.3s ease; cursor: pointer; min-height: 350px; position: relative;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.1)\';">';

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
                    if (!empty($thumbnail_url)) {
                        echo '<div style="width: 120px; height: 80px; margin: 0 auto 10px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #f8f9fa;">';
                        echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($template_name) . '" style="width: 100%; height: 100%; object-fit: cover;" />';
                        echo '</div>';
                    } else {
                        echo '<div style="font-size: 3rem; margin-bottom: 10px;">' . $icon . '</div>';
                    }
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
                    
                    // Bouton dupliquer - désactivé pour les utilisateurs gratuits ayant atteint la limite
                    $duplicate_disabled = (!$is_premium && $templates_count >= 1) ? 'disabled' : '';
                    $duplicate_title = (!$is_premium && $templates_count >= 1) ? 'Duplication limitée à 1 template (Premium requis)' : 'Dupliquer ce template';
                    $duplicate_class = (!$is_premium && $templates_count >= 1) ? 'button-disabled' : 'button-primary';
                    $duplicate_onclick = (!$is_premium && $templates_count >= 1) ? '' : 'onclick="duplicateTemplate(' . $template_id . ', \'' . addslashes($template_name) . '\')"';
                    
                    echo '<button class="button ' . $duplicate_class . '" style="flex: 1; font-size: 16px;" ' . $duplicate_onclick . ' ' . $duplicate_disabled . ' title="' . $duplicate_title . '">📋</button>';
                    
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
                            <button class="gallery-filter-btn button active" data-filter="all" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; -webkit-border-radius: 20px; -moz-border-radius: 20px; -ms-border-radius: 20px; -o-border-radius: 20px; background: #6c757d; border-color: #6c757d; color: white;">📄 Tous</button>
                            <button class="gallery-filter-btn button" data-filter="facture" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; -webkit-border-radius: 20px; -moz-border-radius: 20px; -ms-border-radius: 20px; -o-border-radius: 20px; background: #e3f2fd; border-color: #2196f3; color: #1565c0;">🧾 Factures</button>
                            <button class="gallery-filter-btn button" data-filter="devis" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; -webkit-border-radius: 20px; -moz-border-radius: 20px; -ms-border-radius: 20px; -o-border-radius: 20px; background: #e8f5e8; border-color: #4caf50; color: #2e7d32;">📋 Devis</button>
                            <!-- Catégories désactivées temporairement -->
                            <!-- <button class="gallery-filter-btn button button-secondary" data-filter="commande" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #fff3e0; border-color: #ff9800; color: #e65100;">📦 Commandes</button> -->
                            <!-- <button class="gallery-filter-btn button button-secondary" data-filter="contrat" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #ffebee; border-color: #f44336; color: #b71c1c;">📑 Contrats</button> -->
                            <!-- <button class="gallery-filter-btn button button-secondary" data-filter="newsletter" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #f3e5f5; border-color: #9c27b0; color: #6a1b9a;">📰 Newsletters</button> -->
                        </div>
                    </div>

                    <!-- Grille des modèles prédéfinis -->
                    <div id="predefined-templates-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">

                        <?php
                        // Charger les modèles prédéfinis depuis TemplateDefaults
                        $templates = [];

                        // Templates gratuits (toujours disponibles)
                        $free_templates = \PDF_Builder\TemplateDefaults::get_free_templates();
                        foreach ($free_templates as $slug => $template_data) {
                            $templates[] = [
                                'slug' => $slug,
                                'name' => $template_data['name'],
                                'category' => $template_data['category'],
                                'description' => $template_data['description'] ?? '',
                                'icon' => '📄', // Default icon
                                'is_premium' => false
                            ];
                        }

                        // Templates premium (seulement pour utilisateurs premium)
                        if ($is_premium) {
                            $premium_templates = \PDF_Builder\TemplateDefaults::get_premium_templates();
                            foreach ($premium_templates as $slug => $template_data) {
                                $templates[] = [
                                    'slug' => $slug,
                                    'name' => $template_data['name'],
                                    'category' => $template_data['category'],
                                    'description' => $template_data['description'] ?? '',
                                    'icon' => '⭐', // Premium icon
                                    'is_premium' => true
                                ];
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
                                        <?php if ($template['is_premium']): ?>
                                        <div style="position: absolute; top: 15px; right: 15px; background: #ffd700; color: #000; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;">PREMIUM</div>
                                        <?php endif; ?>
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
                            <label style="display: block; margin-bottom: 5px;">Format</label>
                            <select id="template-format" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <?php
                                $format_options = get_format_options();
                                foreach ($allowed_formats as $format):
                                    $label = isset($format_options[$format]) ? $format_options[$format] : $format;
                                ?>
                                    <option value="<?php echo esc_attr($format); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">DPI (résolution)</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <select id="template-dpi" style="flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                    <?php foreach ($allowed_dpis as $dpi): ?>
                                        <option value="<?php echo esc_attr($dpi); ?>"><?php echo esc_html($dpi); ?> DPI</option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="resolution-display" style="background: #f8f9fa; padding: 8px 12px; border-radius: 4px; font-size: 12px; color: #666; min-width: 120px; text-align: center;">
                                    Résolution: -- × -- px
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Orientation</label>
                            <select id="template-orientation" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <?php
                                $orientation_labels = [
                                    'portrait' => 'Portrait (Vertical)',
                                    'landscape' => 'Paysage (Horizontal)'
                                ];
                                foreach ($allowed_orientations as $orientation):
                                    $label = isset($orientation_labels[$orientation]) ? $orientation_labels[$orientation] : ucfirst($orientation);
                                ?>
                                    <option value="<?php echo esc_attr($orientation); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
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
            <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
            <h3><?php _e('Aucun template trouvé', 'pdf-builder-pro'); ?></h3>
            <p><?php _e('Créez votre premier template pour commencer à concevoir des PDF personnalisés.', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<!-- ✅ JavaScript déplacé vers settings-main.php pour éviter les conflits de navigation -->

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

/* Masquer la notification de limite de templates quand elle est dismissed */
#template-limit-notice.dismissed {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    height: 0 !important;
    overflow: hidden !important;
    pointer-events: none !important;
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
    -webkit-box-shadow: 0 0 0 2px rgba(108,117,125,0.5);
    -moz-box-shadow: 0 0 0 2px rgba(108,117,125,0.5);
    -ms-box-shadow: 0 0 0 2px rgba(108,117,125,0.5);
    -o-box-shadow: 0 0 0 2px rgba(108,117,125,0.5);
    box-shadow: 0 0 0 2px rgba(108,117,125,0.5);
    font-weight: bold;
}

/* Style pour les boutons désactivés (freemium) */
.button-disabled {
    background-color: #f0f0f0 !important;
    border-color: #ddd !important;
    color: #999 !important;
    cursor: not-allowed !important;
    opacity: 0.6 !important;
}

.button-disabled:hover {
    background-color: #f0f0f0 !important;
    border-color: #ddd !important;
    color: #999 !important;
    transform: none !important;
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
    console.log('showUpgradeModal called with reason:', reason);
    const modal = document.getElementById('upgrade-modal-' + reason);
    console.log('Modal element:', modal);
    if (modal) {
        modal.style.display = 'flex';
        console.log('Modal displayed');

        // Tracking pour analytics (si disponible)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'upgrade_modal_shown', {
                'reason': reason,
                'user_type': 'free',
                'page': 'templates'
            });
        }
    } else {
        console.error('Modal upgrade-modal-' + reason + ' not found');
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
        // console.error('Erreur vérification limite:', error);
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

// Fonction pour masquer la notification de limite de templates
function dismissTemplateLimitNotice() {
    console.log('dismissTemplateLimitNotice called');
    const notice = document.getElementById('template-limit-notice');
    console.log('Notice element:', notice);
    if (notice) {
        // Masquer avec une classe CSS au lieu de supprimer du DOM
        notice.classList.add('dismissed');
        console.log('Notification masquée avec classe CSS');

        // Sauvegarder l'état de masquage dans localStorage
        localStorage.setItem('pdf_builder_template_limit_dismissed', 'true');
        console.log('État sauvegardé dans localStorage');
    } else {
        console.error('Element template-limit-notice non trouvé');
    }
}

// Fonction pour réafficher la notification de limite de templates
function showTemplateLimitNotice() {
    console.log('showTemplateLimitNotice called');
    const notice = document.getElementById('template-limit-notice');
    if (notice) {
        // Réafficher en supprimant la classe CSS
        notice.classList.remove('dismissed');
        console.log('Notification réaffichée');
    }

    // Supprimer l'état de masquage de localStorage
    localStorage.removeItem('pdf_builder_template_limit_dismissed');
    console.log('État de masquage supprimé du localStorage');
}

// Vérifier au chargement de la page si la notification a été masquée
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking for dismissed notice');
    const dismissed = localStorage.getItem('pdf_builder_template_limit_dismissed');
    console.log('Dismissed status:', dismissed);
    if (dismissed === 'true') {
        dismissTemplateLimitNotice();
    }

    // Observer les changements sur la notification
    const notice = document.getElementById('template-limit-notice');
    if (notice) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    console.log('Notification class changed:', notice.className);
                    console.log('Has dismissed class:', notice.classList.contains('dismissed'));
                }
            });
        });
        observer.observe(notice, { attributes: true, attributeFilter: ['class'] });
        console.log('MutationObserver set up for notification');

        // Vérification périodique pour forcer le masquage si nécessaire
        setInterval(function() {
            const dismissed = localStorage.getItem('pdf_builder_template_limit_dismissed');
            if (dismissed === 'true' && !notice.classList.contains('dismissed')) {
                console.log('Forcing dismiss class back on notification');
                notice.classList.add('dismissed');
            }
        }, 1000); // Vérifier chaque seconde
    }

    // Gestion du filtrage des templates
    const filterButtons = document.querySelectorAll('.filter-btn');
    const templateItems = document.querySelectorAll('.template-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));

            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');

            const filterValue = this.getAttribute('data-filter');

            templateItems.forEach(item => {
                if (filterValue === 'all') {
                    item.style.display = 'block';
                } else {
                    const itemCategory = item.getAttribute('data-category') || 'autre';
                    if (itemCategory === filterValue) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });
});

// Fonctions pour la gestion des paramètres du template
let currentTemplateId = null;

// Fonction pour calculer et afficher la résolution en temps réel
function updateResolutionDisplay() {
    const formatSelect = document.getElementById('template-format');
    const dpiSelect = document.getElementById('template-dpi');
    const orientationSelect = document.getElementById('template-orientation');
    const resolutionDisplay = document.getElementById('resolution-display');
    
    if (!formatSelect || !dpiSelect || !orientationSelect || !resolutionDisplay) return;
    
    const format = formatSelect.value;
    const dpi = parseInt(dpiSelect.value);
    const orientation = orientationSelect.value;
    
    if (isNaN(dpi) || dpi <= 0) {
        resolutionDisplay.textContent = 'Résolution: -- × -- px';
        return;
    }
    
    // Dimensions physiques des formats en millimètres
    const formatDimensions = {
        'A4': { width: 210, height: 297 },
        'A3': { width: 297, height: 420 },
        'Letter': { width: 215.9, height: 279.4 }, // 8.5 x 11 pouces en mm
        'Legal': { width: 215.9, height: 355.6 },  // 8.5 x 14 pouces en mm
        'EtiquetteColis': { width: 100, height: 150 } // 10 x 15 cm en mm
    };
    
    const dimensions = formatDimensions[format] || formatDimensions['A4'];
    
    // Conversion mm vers pouces (1 pouce = 25.4 mm)
    let widthInches = dimensions.width / 25.4;
    let heightInches = dimensions.height / 25.4;
    
    // Calcul des dimensions en pixels
    let widthPx = Math.round(widthInches * dpi);
    let heightPx = Math.round(heightInches * dpi);
    
    // Inversion si orientation paysage
    if (orientation === 'landscape') {
        [widthPx, heightPx] = [heightPx, widthPx];
    }
    
    resolutionDisplay.textContent = `Résolution: ${widthPx} × ${heightPx} px`;
}

// Fonction pour mettre à jour les dimensions du canvas dans l'éditeur
function updateCanvasDimensions() {
    const formatSelect = document.getElementById('template-format');
    const dpiSelect = document.getElementById('template-dpi');
    const orientationSelect = document.getElementById('template-orientation');
    
    if (!formatSelect || !dpiSelect || !orientationSelect) return;
    
    const format = formatSelect.value;
    const dpi = parseInt(dpiSelect.value);
    const orientation = orientationSelect.value;
    
    // Dimensions physiques des formats en millimètres
    const formatDimensions = {
        'A4': { width: 210, height: 297 },
        'A3': { width: 297, height: 420 },
        'Letter': { width: 215.9, height: 279.4 },
        'Legal': { width: 215.9, height: 355.6 },
        'EtiquetteColis': { width: 100, height: 150 }
    };
    
    const dimensions = formatDimensions[format] || formatDimensions['A4'];
    
    // Conversion mm vers pouces (1 pouce = 25.4 mm)
    let widthInches = dimensions.width / 25.4;
    let heightInches = dimensions.height / 25.4;
    
    // Calcul des dimensions en pixels
    let widthPx = Math.round(widthInches * dpi);
    let heightPx = Math.round(heightInches * dpi);
    
    // Inversion si orientation paysage
    if (orientation === 'landscape') {
        [widthPx, heightPx] = [heightPx, widthPx];
    }
    
    // Mettre à jour les paramètres du canvas dans l'éditeur
    if (window.pdfBuilderCanvasSettings) {
        window.pdfBuilderCanvasSettings.canvas_width = widthPx;
        window.pdfBuilderCanvasSettings.canvas_height = heightPx;
        window.pdfBuilderCanvasSettings.canvas_format = format;
        window.pdfBuilderCanvasSettings.canvas_dpi = dpi;
        window.pdfBuilderCanvasSettings.canvas_orientation = orientation;
    }
    
    // Mettre à jour les indicateurs dans l'interface canvas
    updateCanvasIndicators(widthPx, heightPx, dpi, format, dimensions.width, dimensions.height);
    
    // Déclencher un événement pour notifier l'éditeur du changement
    if (window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('canvasSettingsChanged', {
            detail: {
                width: widthPx,
                height: heightPx,
                format: format,
                dpi: dpi,
                orientation: orientation
            }
        }));
    }
}

// Fonction pour mettre à jour les indicateurs de dimensions dans l'interface canvas
function updateCanvasIndicators(widthPx, heightPx, dpi, format, widthMm, heightMm) {
    // Mettre à jour les éléments d'indicateur s'ils existent
    const widthElement = document.getElementById('card-canvas-width');
    const heightElement = document.getElementById('card-canvas-height');
    const dpiElement = document.getElementById('card-canvas-dpi');
    
    if (widthElement) widthElement.textContent = widthPx;
    if (heightElement) heightElement.textContent = heightPx;
    if (dpiElement) dpiElement.textContent = `${dpi} DPI - ${format} (${widthMm}×${heightMm}mm)`;
    
    // Utiliser aussi CanvasPreviewManager si disponible
    if (window.CanvasPreviewManager) {
        const widthEl = window.CanvasPreviewManager.getCardElement ? 
            window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-width') : widthElement;
        const heightEl = window.CanvasPreviewManager.getCardElement ? 
            window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-height') : heightElement;
        const dpiEl = window.CanvasPreviewManager.getCardElement ? 
            window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-dpi') : dpiElement;
        
        if (widthEl) window.CanvasPreviewManager.updateElement(widthEl, 'textContent', widthPx);
        if (heightEl) window.CanvasPreviewManager.updateElement(heightEl, 'textContent', heightPx);
        if (dpiEl) window.CanvasPreviewManager.updateElement(dpiEl, 'textContent', `${dpi} DPI - ${format} (${widthMm}×${heightMm}mm)`);
    }
}

// Fonction pour définir les valeurs par défaut selon le format
function setFormatDefaults(format) {
    const formatDefaults = {
        'A4': { dpi: '96', orientation: 'portrait' },
        'A3': { dpi: '150', orientation: 'portrait' },
        'Letter': { dpi: '96', orientation: 'portrait' },
        'Legal': { dpi: '96', orientation: 'portrait' },
        'EtiquetteColis': { dpi: '150', orientation: 'portrait' }
    };
    
    const defaults = formatDefaults[format] || formatDefaults['A4'];
    
    // Mettre à jour les selects
    const dpiSelect = document.getElementById('template-dpi');
    const orientationSelect = document.getElementById('template-orientation');
    
    if (dpiSelect) dpiSelect.value = defaults.dpi;
    if (orientationSelect) orientationSelect.value = defaults.orientation;
    
    // Mettre à jour l'affichage de la résolution
    updateResolutionDisplay();
    
    // Calculer et mettre à jour les dimensions du canvas pour l'éditeur
    updateCanvasDimensions();
}

// Écouteur pour mettre à jour les valeurs quand le format change
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'template-format') {
        setFormatDefaults(e.target.value);
    }
});

// Écouteur pour mettre à jour la résolution et les dimensions du canvas quand le DPI change
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'template-dpi') {
        updateResolutionDisplay();
        updateCanvasDimensions();
    }
});

// Écouteur pour mettre à jour la résolution et les dimensions du canvas quand l'orientation change
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'template-orientation') {
        updateResolutionDisplay();
        updateCanvasDimensions();
    }
});

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Mise à jour initiale si nécessaire
    updateResolutionDisplay();
});

function openTemplateSettings(templateId, templateName) {
    currentTemplateId = templateId;
    
    // Afficher le modal
    document.getElementById('template-settings-modal').style.display = 'flex';
    
    // Mettre à jour le titre
    document.getElementById('template-settings-title').textContent = '⚙️ Paramètres du Template: ' + templateName;
    
    // Charger les paramètres actuels
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'pdf_builder_load_template_settings',
            'nonce': pdfBuilderTemplatesNonce,
            'template_id': templateId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remplir les champs
            document.getElementById('template-name-input').value = data.data.name || '';
            document.getElementById('template-description-input').value = data.data.description || '';
            document.getElementById('template-format').value = data.data.format || 'A4';
            document.getElementById('template-dpi').value = data.data.dpi || '96';
            document.getElementById('template-orientation').value = data.data.orientation || 'portrait';
            document.getElementById('template-category').value = data.data.category || 'autre';
            
            // Mettre à jour l'affichage de la résolution
            updateResolutionDisplay();
            
            // Mettre à jour les dimensions du canvas dans l'éditeur
            updateCanvasDimensions();
        } else {
            console.error('Erreur chargement paramètres:', data.data);
            alert('Erreur lors du chargement des paramètres du template');
        }
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
        alert('Erreur lors du chargement des paramètres du template');
    });
}

function closeTemplateSettings() {
    document.getElementById('template-settings-modal').style.display = 'none';
    currentTemplateId = null;
}

function saveTemplateSettings() {
    if (!currentTemplateId) {
        alert('Erreur: ID du template manquant');
        return;
    }
    
    const name = document.getElementById('template-name-input').value.trim();
    if (!name) {
        alert('Le nom du template est obligatoire');
        return;
    }
    
    // Collecter les données
    const data = {
        'action': 'pdf_builder_save_template_settings',
        'nonce': pdfBuilderTemplatesNonce,
        'template_id': currentTemplateId,
        'name': name,
        'description': document.getElementById('template-description-input').value,
        'format': document.getElementById('template-format').value,
        'dpi': document.getElementById('template-dpi').value,
        'orientation': document.getElementById('template-orientation').value,
        'category': document.getElementById('template-category').value
    };
    
    // Sauvegarder
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Paramètres sauvegardés avec succès');
            closeTemplateSettings();
            // Recharger la page pour afficher les changements
            location.reload();
        } else {
            console.error('Erreur sauvegarde:', data.data);
            alert('Erreur lors de la sauvegarde: ' + (data.data || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
        alert('Erreur lors de la sauvegarde des paramètres');
    });
}
</script>