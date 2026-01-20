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
$user_can_create = \PDF_Builder\Admin\PdfBuilderAdminNew::can_create_template();
$templates_count = \PDF_Builder\Admin\PdfBuilderAdminNew::count_user_templates(get_current_user_id());
$is_premium = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium();

// Créer templates par défaut si aucun template et utilisateur gratuit
if ($templates_count === 0 && !$is_premium) {
    \PDF_Builder\TemplateDefaults::create_default_templates_for_user(get_current_user_id());
    // Recharger le compteur après création
    $templates_count = \PDF_Builder\Admin\PdfBuilderAdminNew::count_user_templates(get_current_user_id());
}

// Récupérer les DPI disponibles depuis les paramètres canvas
$settings = pdf_builder_get_option('pdf_builder_settings', array());
$available_dpi_string = isset($settings['pdf_builder_canvas_dpi']) ? $settings['pdf_builder_canvas_dpi'] : '72,96,150';
if (is_string($available_dpi_string) && strpos($available_dpi_string, ',') !== false) {
    $available_dpis = explode(',', $available_dpi_string);
} elseif (is_array($available_dpi_string)) {
    $available_dpis = $available_dpi_string;
} else {
    // Valeur unique, la convertir en tableau
    $available_dpis = [$available_dpi_string];
}
$available_dpis = array_map('intval', $available_dpis); // S'assurer que ce sont des entiers

// Récupérer les formats disponibles depuis les paramètres canvas
$settings = pdf_builder_get_option('pdf_builder_settings', array());
$available_formats_string = isset($settings['pdf_builder_canvas_formats']) ? $settings['pdf_builder_canvas_formats'] : 'A4';
if (is_string($available_formats_string) && strpos($available_formats_string, ',') !== false) {
    $available_formats = explode(',', $available_formats_string);
} elseif (is_array($available_formats_string)) {
    $available_formats = $available_formats_string;
} else {
    // Valeur unique, la convertir en tableau
    $available_formats = [$available_formats_string];
}
$available_formats = array_map('strval', $available_formats); // S'assurer que ce sont des chaînes

// Récupérer les orientations disponibles depuis les paramètres canvas
$settings = pdf_builder_get_option('pdf_builder_settings', array());
$available_orientations_string = isset($settings['pdf_builder_canvas_orientations']) ? $settings['pdf_builder_canvas_orientations'] : 'portrait,landscape';
if (is_string($available_orientations_string) && strpos($available_orientations_string, ',') !== false) {
    $available_orientations = explode(',', $available_orientations_string);
} elseif (is_array($available_orientations_string)) {
    $available_orientations = $available_orientations_string;
} else {
    // Valeur unique, la convertir en tableau
    $available_orientations = [$available_orientations_string];
}
$available_orientations = array_map('strval', $available_orientations); // S'assurer que ce sont des chaînes

// Définir les options DPI avec leurs labels
$dpi_options = [
    72 => '72 DPI - Écran (faible qualité)',
    96 => '96 DPI - Web (qualité standard)',
    150 => '150 DPI - Impression moyenne',
    300 => '300 DPI - Haute qualité',
    600 => '600 DPI - Professionnel'
];

// Définir les options de format avec leurs labels
$format_options = [
    'A4' => '📄 A4 (210×297mm)',
    'A3' => '📃 A3 (297×420mm)',
    'Letter' => '🇺🇸 Letter (8.5×11")',
    'Legal' => '⚖️ Legal (8.5×14")',
    'Label' => '📦 Étiquette Colis (100×150mm)'
];

// Définir les options d'orientation avec leurs labels
$orientation_options = [
    'portrait' => '📱 Portrait (Vertical)',
    'landscape' => '🖥️ Paysage (Horizontal)'
];
?>

<!-- ✅ FIX: Localiser le nonce immédiatement pour le JavaScript inline -->
<script>
var pdfBuilderTemplatesNonce = '<?php echo esc_js($templates_nonce); ?>';
var ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
var pdfBuilderAjax = {
    nonce: '<?php echo esc_js(wp_create_nonce('pdf_builder_ajax')); ?>',
    editor_url: '<?php echo esc_js(admin_url('admin.php?page=pdf-builder-react-editor')); ?>'
};

// Variables pour les DPI disponibles
var availableDpis = <?php echo json_encode($available_dpis); ?>;
var dpiOptions = <?php echo json_encode($dpi_options); ?>;

// Variables pour les formats disponibles
var availableFormats = <?php echo json_encode($available_formats); ?>;
var formatOptions = <?php echo json_encode($format_options); ?>;

// Variables pour les orientations disponibles
var availableOrientations = <?php echo json_encode($available_orientations); ?>;
var orientationOptions = <?php echo json_encode($orientation_options); ?>;
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
                <button class="button button-secondary" id="upgrade-required-btn"
                        onclick="showUpgradeModal('gallery')"
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
                        __('Vous avez créé %d template gratuit sur 1. Passez en Premium pour créer des templates illimités !', 'pdf-builder-pro'),
                        $templates_count
                    ); ?>
                </p>
                <a href="#" onclick="showUpgradeModal('gallery')" class="button button-primary">
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
                $templates = $wpdb->get_results($wpdb->prepare("SELECT id, name, thumbnail_url, created_at, updated_at, is_default, template_data FROM $table_templates WHERE user_id = %d ORDER BY id", get_current_user_id()), ARRAY_A);
            } else {
                $templates = $wpdb->get_results($wpdb->prepare("SELECT id, name, '' as thumbnail_url, created_at, updated_at, is_default, template_data FROM $table_templates WHERE user_id = %d ORDER BY id", get_current_user_id()), ARRAY_A);
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

                    echo '<div class="template-card template-type-' . $template_type . '" style="border: 2px solid #dee2e6; border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -o-box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; -webkit-transition: all 0.3s ease; -moz-transition: all 0.3s ease; -o-transition: all 0.3s ease; cursor: pointer; min-height: 350px; position: relative;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.1)\';">';

                    // Conteneur pour organiser le contenu de la carte
                    echo '<div style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; flex-direction: column; -webkit-flex-direction: column; -moz-flex-direction: column; -ms-flex-direction: column; -o-flex-direction: column; height: 100%;">';

                    // Badge du type de template en haut à gauche

                    echo '<div class="default-template-icon" style="position: absolute; top: 10px; right: 10px; font-size: 20px; cursor: pointer; opacity: ' . ($is_default ? '1' : '0.5') . ';" onclick="toggleDefaultTemplate(' . intval($template_id) . ', \'' . esc_js($template_type) . '\', \'' . esc_js($template_name) . '\')" title="' . ($is_default ? 'Template par défaut' : 'Définir comme template par défaut') . '">';
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
                    echo '<a href="' . admin_url('admin.php?page=pdf-builder-react-editor&template_id=' . $template_id) . '" class="button button-secondary" style="flex: 1; text-align: center; font-size: 16px;" title="Éditer ce template" onclick="console.log(\'[PDF Builder] Edit button clicked for template ID: ' . intval($template_id) . '\'); console.log(\'[PDF Builder] Navigating to editor for template: ' . esc_js($template_name) . '\');">✏️</a>';
                    echo '<button class="button button-secondary" style="flex: 1; font-size: 16px;" onclick="' . esc_js($button_action) . '(' . intval($template_id) . ', \'' . esc_js($template_name) . '\')" title="Paramètres">⚙️</button>';
                    echo '<button class="button button-primary" style="flex: 1; font-size: 16px;" onclick="duplicateTemplate(' . intval($template_id) . ', \'' . esc_js($template_name) . '\')" title="Dupliquer ce template">📋</button>';
                    echo '<button class="button button-danger" style="flex: 1; font-size: 16px;" onclick="confirmDeleteTemplate(' . intval($template_id) . ', \'' . esc_js($template_name) . '\')" title="Supprimer">🗑️</button>';
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
                <div class="template-modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; border-bottom: 1px solid var(--pdf-border); background: var(--pdf-light); color: var(--pdf-text);">
                    <div>
                        <h2 style="margin: 0; font-size: 24px; font-weight: 600;">🎨 Galerie de Modèles Prédéfinis</h2>
                        <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 14px; color: var(--pdf-secondary);">Choisissez un modèle professionnel pour commencer</p>
                    </div>
                    <button onclick="closeTemplateGallery()" style="background: var(--pdf-border); border: none; font-size: 24px; cursor: pointer; color: var(--pdf-secondary); padding: 8px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">×</button>
                </div>

                <div style="padding: 30px; max-height: calc(90vh - 120px); overflow-y: auto;">
                    <!-- Filtres de catégorie -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">Filtrer par catégorie</h3>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button class="gallery-filter-btn button button-secondary active" data-filter="all">📄 Tous</button>
                            <button class="gallery-filter-btn button button-secondary facture" data-filter="facture">🧾 Factures</button>
                            <button class="gallery-filter-btn button button-secondary devis" data-filter="devis">📋 Devis</button>
                            <!-- Catégories désactivées temporairement -->
                            <!-- <button class="gallery-filter-btn button button-secondary commande" data-filter="commande">📦 Commandes</button> -->
                            <!-- <button class="gallery-filter-btn button button-secondary contrat" data-filter="contrat">📑 Contrats</button> -->
                            <!-- <button class="gallery-filter-btn button button-secondary newsletter" data-filter="newsletter">📰 Newsletters</button> -->
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
        <div id="template-settings-modal" class="canvas-modal-overlay" style="display: none;">
            <div class="canvas-modal-container">
                <div class="canvas-modal-header">
                    <h3 id="template-settings-title">⚙️ Paramètres du Template</h3>
                    <button onclick="closeTemplateSettingsModal()" class="canvas-modal-close">&times;</button>
                </div>

                <div id="template-settings-content" class="canvas-modal-body">
                    <div class="setting-group">
                        <label class="setting-label">Nom du template</label>
                        <input type="text" id="template-name-input" class="setting-input">
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Description</label>
                        <textarea id="template-description-input" rows="3" class="setting-textarea"></textarea>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Template public</label>
                        <div class="setting-checkbox-group">
                            <label class="setting-checkbox-label">
                                <input type="checkbox" id="template-public" class="setting-checkbox">
                                Visible par tous les utilisateurs
                            </label>
                        </div>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Format de papier</label>
                        <select id="template-paper-size" class="setting-select">
                            <option value="A4">A4 (594 × 1123 px)</option>
                            <option value="A3">A3 (840 × 1191 px)</option>
                            <option value="Letter">Letter (612 × 792 px)</option>
                            <option value="Legal">Legal (612 × 1008 px)</option>
                        </select>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Orientation</label>
                        <select id="template-orientation" class="setting-select">
                            <option value="portrait">Portrait</option>
                            <option value="landscape">Paysage</option>
                        </select>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Résolution (DPI)</label>
                        <select id="template-dpi" class="setting-select">
                            <option value="72">72 DPI (Écran)</option>
                            <option value="96">96 DPI (Web)</option>
                            <option value="150">150 DPI (Impression moyenne)</option>
                            <option value="300">300 DPI (Impression haute qualité)</option>
                            <option value="600">600 DPI (Impression professionnelle)</option>
                        </select>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Catégorie</label>
                        <select id="template-category" class="setting-select">
                            <option value="facture">Facture</option>
                            <option value="devis">Devis</option>
                            <option value="commande">Bon de commande</option>
                            <option value="contrat">Contrat</option>
                            <option value="newsletter">Newsletter</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                </div>

                <div class="canvas-modal-footer">
                    <button onclick="closeTemplateSettingsModal()" class="canvas-modal-btn canvas-modal-btn-secondary">Annuler</button>
                    <button onclick="saveTemplateSettings()" class="canvas-modal-btn canvas-modal-btn-primary">💾 Enregistrer</button>
                </div>
            </div>
        </div>

        <div id="no-templates" style="display: none; text-align: center; padding: 40px; color: var(--pdf-secondary);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
            <h3 style="color: var(--pdf-text);"><?php _e('Aucun template trouvé', 'pdf-builder-pro'); ?></h3>
            <p><?php _e('Créez votre premier template pour commencer à concevoir des PDF personnalisés.', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<!-- ✅ JavaScript déplacé vers settings-main.php pour éviter les conflits de navigation -->

<style>
/* Variables CSS pour la cohérence avec le reste du plugin */
:root {
    --pdf-primary: #007cba;      /* Bleu WordPress */
    --pdf-success: #28a745;      /* Vert succès */
    --pdf-danger: #dc3545;       /* Rouge danger */
    --pdf-warning: #ffc107;      /* Jaune warning */
    --pdf-secondary: #6c757d;    /* Gris secondaire */
    --pdf-light: #f8f9fa;        /* Fond clair */
    --pdf-border: #dee2e6;       /* Bordure */
    --pdf-text: #495057;         /* Texte principal */
}

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
    accent-color: var(--pdf-primary);
}

.template-modal-content select:hover,
.template-modal-content input:hover,
.template-modal-content textarea:hover {
    border-color: var(--pdf-primary);
}

.template-modal-content select:focus,
.template-modal-content input:focus,
.template-modal-content textarea:focus {
    border-color: var(--pdf-primary);
    -webkit-box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.25);
    -moz-box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.25);
    -ms-box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.25);
    -o-box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.25);
    box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.25);
    outline: none;
}

.button-danger {
    background: var(--pdf-danger) !important;
    border-color: var(--pdf-danger) !important;
    color: #fff !important;
}

.button-danger:hover {
    background: #c82333 !important;
    border-color: #bd2130 !important;
}

.button-success {
    background: var(--pdf-success) !important;
    border-color: var(--pdf-success) !important;
    color: #fff !important;
}

.button-success:hover {
    background: #218838 !important;
    border-color: #1e7e34 !important;
}

.button-warning {
    background: var(--pdf-warning) !important;
    border-color: var(--pdf-warning) !important;
    color: #212529 !important;
}

.button-warning:hover {
    background: #e0a800 !important;
    border-color: #d39e00 !important;
}

.gallery-filter-btn.active {
    -webkit-box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.5) !important;
    -moz-box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.5) !important;
    -ms-box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.5) !important;
    -o-box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.5) !important;
    box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.5) !important;
    font-weight: bold !important;
}

/* Styles pour les badges de type de template */
.template-type-badge {
    background: var(--pdf-primary) !important;
}

.template-type-badge.facture { background: #007cba !important; }
.template-type-badge.devis { background: #28a745 !important; }
.template-type-badge.commande { background: #ffc107 !important; color: #212529 !important; }
.template-type-badge.contrat { background: #dc3545 !important; }
.template-type-badge.newsletter { background: #6f42c1 !important; }
.template-type-badge.autre { background: var(--pdf-secondary) !important; }

/* Styles pour les boutons de filtre de galerie */
.gallery-filter-btn {
    font-size: 12px !important;
    padding: 8px 16px !important;
    border-radius: 20px !important;
    transition: all 0.2s ease !important;
}

.gallery-filter-btn.facture {
    background: var(--pdf-primary) !important;
    border-color: var(--pdf-primary) !important;
    color: white !important;
}

.gallery-filter-btn.devis {
    background: var(--pdf-success) !important;
    border-color: var(--pdf-success) !important;
    color: white !important;
}

.gallery-filter-btn.commande {
    background: var(--pdf-warning) !important;
    border-color: var(--pdf-warning) !important;
    color: #212529 !important;
}

.gallery-filter-btn.contrat {
    background: var(--pdf-danger) !important;
    border-color: var(--pdf-danger) !important;
    color: white !important;
}

.gallery-filter-btn.newsletter {
    background: #6f42c1 !important;
    border-color: #6f42c1 !important;
    color: white !important;
}

/* Styles pour les éléments d'état */
.template-status-active { color: var(--pdf-success) !important; }
.template-status-inactive { color: var(--pdf-secondary) !important; }
.template-status-error { color: var(--pdf-danger) !important; }

/* Styles pour les modals de paramètres */
.template-settings-section {
    border: 1px solid var(--pdf-border);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background: var(--pdf-light);
}

.template-settings-section h4 {
    color: var(--pdf-text);
    margin-top: 0;
    margin-bottom: 15px;
    border-bottom: 2px solid var(--pdf-primary);
    padding-bottom: 8px;
}

.template-settings-field {
    margin-bottom: 15px;
}

.template-settings-field label {
    display: block;
    font-weight: 600;
    color: var(--pdf-text);
    margin-bottom: 5px;
}

.template-settings-field input[type="text"],
.template-settings-field input[type="number"],
.template-settings-field textarea,
.template-settings-field select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid var(--pdf-border);
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.template-settings-field input:focus,
.template-settings-field textarea:focus,
.template-settings-field select:focus {
    border-color: var(--pdf-primary);
    box-shadow: 0 0 0 2px rgba(0, 123, 186, 0.25);
    outline: none;
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
        // 
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

// Fonctions pour gérer les templates
function openTemplateSettings(templateId, templateName) {
    // Ouvrir le modal des paramètres du template
    document.getElementById('template-settings-modal').style.display = 'flex';

    // Définir le titre du modal
    document.getElementById('template-settings-title').textContent = 'Configuration de "' + templateName + '"';

    // Charger les données du template
    loadTemplateSettings(templateId);
}

function closeTemplateSettingsModal() {
    document.getElementById('template-settings-modal').style.display = 'none';
}

function loadTemplateSettings(templateId) {
    // Définir l'ID du template
    document.getElementById('settings-template-id').value = templateId;

    // Faire une requête AJAX pour charger les paramètres du template
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'pdf_builder_load_template_settings',
            'template_id': templateId,
            'nonce': pdfBuilderTemplatesNonce
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.template) {
            // Remplir le formulaire avec les données du template
            document.getElementById('template-name').value = data.template.name || '';
            document.getElementById('template-description').value = data.template.description || '';
            document.getElementById('template-category').value = data.template.category || 'autre';
            document.getElementById('template-is-default').checked = data.template.is_default == 1;

            // Informations système
            document.getElementById('template-created-date').textContent = data.template.created_at || '-';
            document.getElementById('template-updated-date').textContent = data.template.updated_at || '-';
        } else {
            alert('Erreur lors du chargement des paramètres: ' + (data.message || 'Erreur inconnue'));
            closeTemplateSettingsModal();
        }
    })
    .catch(error => {
        
        alert('Erreur lors du chargement des paramètres du template');
        closeTemplateSettingsModal();
    });
}

function saveTemplateSettings() {
    const templateId = document.getElementById('settings-template-id').value;
    const templateName = document.getElementById('template-name').value;
    const templateDescription = document.getElementById('template-description').value;
    const templateCategory = document.getElementById('template-category').value;
    const isDefault = document.getElementById('template-is-default').checked ? 1 : 0;

    // Validation de base
    if (!templateName.trim()) {
        alert('Veuillez entrer un nom pour le template.');
        return;
    }

    // Faire une requête AJAX pour sauvegarder les paramètres
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'pdf_builder_save_template_settings',
            'template_id': templateId,
            'template_name': templateName,
            'template_description': templateDescription,
            'template_category': templateCategory,
            'is_default': isDefault,
            'nonce': pdfBuilderTemplatesNonce
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Paramètres du template sauvegardés avec succès !');
            closeTemplateSettingsModal();
            location.reload(); // Recharger la page pour voir les changements
        } else {
            alert('Erreur lors de la sauvegarde: ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        
        alert('Erreur lors de la sauvegarde des paramètres du template');
    });
}

function duplicateTemplate(templateId, templateName) {
    if (confirm('Êtes-vous sûr de vouloir dupliquer le template "' + templateName + '" ?')) {
        // Créer une requête AJAX pour dupliquer le template
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'pdf_builder_duplicate_template',
                'template_id': templateId,
                'nonce': pdfBuilderTemplatesNonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Template dupliqué avec succès !');
                location.reload();
            } else {
                alert('Erreur lors de la duplication: ' + (data.message || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            
            alert('Erreur lors de la duplication du template');
        });
    }
}

function confirmDeleteTemplate(templateId, templateName) {
    console.log('[DEBUG] confirmDeleteTemplate called with:', templateId, templateName);
    if (confirm('Êtes-vous sûr de vouloir supprimer définitivement le template "' + templateName + '" ?\n\nCette action ne peut pas être annulée.')) {
        console.log('[DEBUG] User confirmed deletion, sending AJAX request');
        // Créer une requête AJAX pour supprimer le template
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'pdf_builder_delete_template',
                'template_id': templateId,
                'nonce': pdfBuilderTemplatesNonce
            })
        })
        .then(response => {
            console.log('[DEBUG] AJAX response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('[DEBUG] AJAX data received:', data);
            if (data.success) {
                alert('Template supprimé avec succès !');
                location.reload();
            } else {
                alert('Erreur lors de la suppression: ' + (data.message || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            console.error('[DEBUG] AJAX error:', error);
            alert('Erreur lors de la suppression du template');
        });
    } else {
        console.log('[DEBUG] User cancelled deletion');
    }
}

function toggleDefaultTemplate(templateId, templateType, templateName) {
    // Créer une requête AJAX pour basculer le statut par défaut
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'pdf_builder_toggle_default_template',
            'template_id': templateId,
            'template_type': templateType,
            'nonce': pdfBuilderTemplatesNonce
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Statut du template mis à jour !');
            location.reload();
        } else {
            alert('Erreur lors de la mise à jour: ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        
        alert('Erreur lors de la mise à jour du template');
    });
}

function selectPredefinedTemplate(templateSlug) {
    // Rediriger vers l'éditeur avec le template prédéfini sélectionné
    const editorUrl = pdfBuilderAjax.editor_url + '&predefined_template=' + encodeURIComponent(templateSlug);
    window.location.href = editorUrl;
}
</script>

<!-- Modal des paramètres du template -->
<div id="template-settings-modal" class="template-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; align-items: center; justify-content: center;">
    <div class="template-modal-content" style="background: #fff; border-radius: 12px; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; padding: 0; max-width: 600px; width: 95%; max-height: 80vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); -webkit-box-shadow: 0 20px 60px rgba(0,0,0,0.3); -moz-box-shadow: 0 20px 60px rgba(0,0,0,0.3); -ms-box-shadow: 0 20px 60px rgba(0,0,0,0.3); -o-box-shadow: 0 20px 60px rgba(0,0,0,0.3);">

        <!-- Header du modal -->
        <div class="template-modal-header" style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; justify-content: space-between; -webkit-justify-content: space-between; -moz-justify-content: space-between; -ms-justify-content: space-between; -o-justify-content: space-between; align-items: center; -webkit-align-items: center; -moz-align-items: center; -ms-align-items: center; -o-align-items: center; padding: 25px 30px; border-bottom: 1px solid #e1e8ed; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div>
                <h2 style="margin: 0; font-size: 24px; font-weight: 600;">⚙️ Paramètres du Template</h2>
                <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;" id="template-settings-title">Configuration du template</p>
            </div>
            <button onclick="closeTemplateSettingsModal()" style="background: rgba(255,255,255,0.2); border: none; font-size: 24px; cursor: pointer; color: white; padding: 8px; border-radius: 50%; -webkit-border-radius: 50%; -moz-border-radius: 50%; -ms-border-radius: 50%; -o-border-radius: 50%; width: 40px; height: 40px; display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; align-items: center; -webkit-align-items: center; -moz-align-items: center; -ms-align-items: center; -o-align-items: center; justify-content: center; -webkit-justify-content: center; -moz-justify-content: center; -ms-justify-content: center; -o-justify-content: center;">×</button>
        </div>

        <!-- Corps du modal -->
        <div class="template-modal-body" style="padding: 30px; max-height: calc(80vh - 140px); overflow-y: auto;">

            <!-- Formulaire des paramètres -->
            <form id="template-settings-form">

                <!-- ID caché du template -->
                <input type="hidden" id="settings-template-id" name="template_id" value="">

                <!-- Nom du template -->
                <div class="settings-field" style="margin-bottom: 20px;">
                    <label for="template-name" style="display: block; margin-bottom: 8px; font-weight: 600; color: #23282d;">📝 Nom du Template</label>
                    <input type="text" id="template-name" name="template_name" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; -webkit-border-radius: 6px; -moz-border-radius: 6px; -ms-border-radius: 6px; -o-border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease; -webkit-transition: border-color 0.3s ease; -moz-transition: border-color 0.3s ease; -o-transition: border-color 0.3s ease;" placeholder="Entrez le nom du template">
                </div>

                <!-- Description du template -->
                <div class="settings-field" style="margin-bottom: 20px;">
                    <label for="template-description" style="display: block; margin-bottom: 8px; font-weight: 600; color: #23282d;">📖 Description</label>
                    <textarea id="template-description" name="template_description" rows="3" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; -webkit-border-radius: 6px; -moz-border-radius: 6px; -ms-border-radius: 6px; -o-border-radius: 6px; font-size: 14px; resize: vertical; transition: border-color 0.3s ease; -webkit-transition: border-color 0.3s ease; -moz-transition: border-color 0.3s ease; -o-transition: border-color 0.3s ease;" placeholder="Entrez une description pour ce template"></textarea>
                </div>

                <!-- Catégorie du template -->
                <div class="settings-field" style="margin-bottom: 20px;">
                    <label for="template-category" style="display: block; margin-bottom: 8px; font-weight: 600; color: #23282d;">🏷️ Catégorie</label>
                    <select id="template-category" name="template_category" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; -webkit-border-radius: 6px; -moz-border-radius: 6px; -ms-border-radius: 6px; -o-border-radius: 6px; font-size: 14px; background: white; transition: border-color 0.3s ease; -webkit-transition: border-color 0.3s ease; -moz-transition: border-color 0.3s ease; -o-transition: border-color 0.3s ease;">
                        <option value="facture">🧾 Facture</option>
                        <option value="devis">📋 Devis</option>
                        <option value="commande">📦 Commande</option>
                        <option value="contrat">📑 Contrat</option>
                        <option value="newsletter">📰 Newsletter</option>
                        <option value="autre">📄 Autre</option>
                    </select>
                </div>

                <!-- Template par défaut -->
                <div class="settings-field" style="margin-bottom: 20px;">
                    <label style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; align-items: center; -webkit-align-items: center; -moz-align-items: center; -ms-align-items: center; -o-align-items: center; cursor: pointer; font-weight: 600; color: #23282d;">
                        <input type="checkbox" id="template-is-default" name="is_default" value="1" style="margin-right: 10px; transform: scale(1.2); -webkit-transform: scale(1.2); -moz-transform: scale(1.2); -ms-transform: scale(1.2); -o-transform: scale(1.2);">
                        ⭐ Définir comme template par défaut
                    </label>
                    <p style="margin: 5px 0 0 26px; color: #666; font-size: 12px;">Ce template sera sélectionné par défaut pour ce type de document</p>
                </div>

                <!-- Informations système (readonly) -->
                <div class="settings-section" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e1e8ed;">
                    <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">ℹ️ Informations Système</h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #666; font-size: 12px;">DATE DE CRÉATION</label>
                            <span id="template-created-date" style="color: #23282d; font-size: 14px;">-</span>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #666; font-size: 12px;">DERNIÈRE MODIFICATION</label>
                            <span id="template-updated-date" style="color: #23282d; font-size: 14px;">-</span>
                        </div>
                    </div>
                </div>

            </form>

        </div>

        <!-- Footer du modal -->
        <div class="template-modal-footer" style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; justify-content: flex-end; -webkit-justify-content: flex-end; -moz-justify-content: flex-end; -ms-justify-content: flex-end; -o-justify-content: flex-end; gap: 15px; padding: 20px 30px; border-top: 1px solid #e1e8ed; background: #f8f9fa;">
            <button onclick="closeTemplateSettingsModal()" class="button button-secondary" style="padding: 10px 20px;">Annuler</button>
            <button onclick="saveTemplateSettings()" class="button button-primary" style="padding: 10px 30px; background: #667eea; border-color: #667eea;">
                <span class="dashicons dashicons-yes" style="margin-right: 5px;"></span>
                Sauvegarder
            </button>
        </div>

    </div>
</div>

<!-- JavaScript pour la gestion des templates -->
<script>
var currentTemplateId = null;

// Fonction pour ouvrir les paramètres du template
function openTemplateSettings(templateId, templateName) {
    
    currentTemplateId = templateId;
    
    // Afficher la modale
    document.getElementById('template-settings-modal').style.display = 'flex';
    
    // Mettre à jour le titre
    document.getElementById('template-settings-title').textContent = '⚙️ Paramètres de "' + templateName + '"';
    
    // Charger les paramètres du template
    loadTemplateSettings(templateId);
}

// Fonction pour fermer la modale des paramètres
function closeTemplateSettingsModal() {
    document.getElementById('template-settings-modal').style.display = 'none';
    currentTemplateId = null;
}

// Fonction pour charger les paramètres du template via AJAX
function loadTemplateSettings(templateId) {
    
    
    // Afficher un indicateur de chargement
    document.getElementById('template-settings-content').innerHTML = '<div style="text-align: center; padding: 40px;"><div style="font-size: 2rem; margin-bottom: 20px;">⏳</div><p>Chargement des paramètres...</p></div>';
    
    // Préparer les données AJAX
    var data = {
        action: 'pdf_builder_load_template_settings',
        template_id: templateId,
        nonce: pdfBuilderTemplatesNonce
    };
    
    // Faire la requête AJAX
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: data,
        success: function(response) {
            
            if (response.success && response.data && response.data.template) {
                displayTemplateSettings(response.data.template);
            } else {
                var errorMsg = response.data && response.data.message ? response.data.message : 'Erreur lors du chargement des paramètres';
                document.getElementById('template-settings-content').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><div style="font-size: 2rem; margin-bottom: 20px;">❌</div><p>' + errorMsg + '</p></div>';
                
                // Afficher une notification d'erreur
                if (typeof window.showErrorNotification !== 'undefined') {
                    window.showErrorNotification('Erreur lors du chargement des paramètres: ' + errorMsg);
                }
            }
        },
        error: function(xhr, status, error) {
            
            document.getElementById('template-settings-content').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><div style="font-size: 2rem; margin-bottom: 20px;">❌</div><p>Erreur de communication avec le serveur</p></div>';
            
            // Afficher une notification d'erreur
            if (typeof window.showErrorNotification !== 'undefined') {
                window.showErrorNotification('Erreur de communication lors du chargement des paramètres');
            }
        }
    });
}

// Fonction pour afficher les paramètres du template dans la modale
function displayTemplateSettings(template) {
    
    
    var content = document.getElementById('template-settings-content');
    
    // Valeurs par défaut depuis les paramètres du canvas
    var canvasFormat = template.canvas_settings?.default_canvas_format || 'A4';
    var canvasOrientation = template.canvas_settings?.default_canvas_orientation || 'portrait';
    var canvasDpi = template.canvas_settings?.default_canvas_dpi || 96;
    
    // Valeurs depuis template_data si elles existent
    var templateFormat = template.template_data?.canvas_format || canvasFormat;
    var templateOrientation = template.template_data?.canvas_orientation || canvasOrientation;
    var templateDpi = template.template_data?.canvas_dpi || canvasDpi;
    
    // Créer le formulaire HTML
    content.innerHTML = `
        <form id="template-settings-form">
            <!-- Nom du template -->
            <div class="settings-field" style="margin-bottom: 20px;">
                <label for="template-name" style="display: block; font-weight: bold; margin-bottom: 8px; color: #23282d;">📝 Nom du template</label>
                <input type="text" id="template-name" name="template_name" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; transition: border-color 0.3s ease;" placeholder="Entrez le nom du template" value="${template.name || ''}">
            </div>

            <!-- Description du template -->
            <div class="settings-field" style="margin-bottom: 20px;">
                <label for="template-description" style="display: block; font-weight: bold; margin-bottom: 8px; color: #23282d;">📖 Description</label>
                <textarea id="template-description" name="template_description" rows="3" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; resize: vertical; transition: border-color 0.3s ease;" placeholder="Entrez une description pour ce template">${template.description || ''}</textarea>
            </div>

            <!-- Paramètres avancés -->
            <div class="settings-section" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e1e8ed;">
                <h4 style="margin: 0 0 15px 0; color: #23282d; font-size: 14px; font-weight: 600;">⚙️ Paramètres avancés</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <!-- Format de papier -->
                    <div>
                        <label for="template-format" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555; font-size: 12px;">📄 FORMAT DE PAPIER</label>
                        <select id="template-format" name="canvas_format" style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; background: white;">
                        </select>
                        <div id="template-format-warning" style="margin-top: 5px; color: #dc3545; font-size: 11px; display: none;">
                            ⚠️ Aucun format configuré dans les paramètres canvas
                        </div>
                    </div>

                    <!-- Orientation -->
                    <div>
                        <label for="template-orientation" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555; font-size: 12px;">🔄 ORIENTATION</label>
                        <select id="template-orientation" name="canvas_orientation" style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; background: white;">
                        </select>
                        <div id="template-orientation-warning" style="margin-top: 5px; color: #dc3545; font-size: 11px; display: none;">
                            ⚠️ Aucune orientation configurée dans les paramètres canvas
                        </div>
                    </div>

                    <!-- Résolution DPI -->
                    <div style="grid-column: span 2;">
                        <label for="template-dpi" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555; font-size: 12px;">🎯 RÉSOLUTION (DPI)</label>
                        <select id="template-dpi" name="canvas_dpi" style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; background: white;">
                        </select>
                        <div id="template-dpi-warning" style="margin-top: 5px; color: #dc3545; font-size: 11px; display: none;">
                            ⚠️ Aucune résolution DPI configurée dans les paramètres canvas
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catégorie du template -->
            <div class="settings-field" style="margin-bottom: 20px;">
                <label for="template-category" style="display: block; font-weight: bold; margin-bottom: 8px; color: #23282d;">🏷️ Catégorie</label>
                <select id="template-category" name="template_category" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; background: white; transition: border-color 0.3s ease;">
                    <option value="facture">🧾 Facture</option>
                    <option value="devis">📋 Devis</option>
                    <option value="commande">📦 Commande</option>
                    <option value="contrat">📑 Contrat</option>
                    <option value="newsletter">📰 Newsletter</option>
                    <option value="autre">📄 Autre</option>
                </select>
            </div>

            <!-- Template par défaut -->
            <div class="settings-field" style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; cursor: pointer; font-weight: 600; color: #23282d;">
                    <input type="checkbox" id="template-is-default" name="is_default" value="1" style="margin-right: 10px; transform: scale(1.2);" ${template.is_default ? 'checked' : ''}>
                    ⭐ Définir comme template par défaut
                </label>
                <p style="margin: 5px 0 0 26px; color: #666; font-size: 12px;">Ce template sera sélectionné par défaut pour ce type de document</p>
            </div>

            <!-- Informations système (readonly) -->
            <div class="settings-section" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e1e8ed;">
                <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">ℹ️ Informations Système</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #666; font-size: 12px;">DATE DE CRÉATION</label>
                        <span id="template-created-date" style="color: #23282d; font-size: 14px;">${template.created_at ? new Date(template.created_at).toLocaleDateString('fr-FR') : '-'}</span>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #666; font-size: 12px;">DERNIÈRE MODIFICATION</label>
                        <span id="template-updated-date" style="color: #23282d; font-size: 14px;">${template.updated_at ? new Date(template.updated_at).toLocaleDateString('fr-FR') : '-'}</span>
                    </div>
                </div>
            </div>
        </form>
    `;
    
    // Remplir le select DPI avec les options disponibles
    var dpiSelect = document.getElementById('template-dpi');
    var dpiWarning = document.getElementById('template-dpi-warning');
    
    if (dpiSelect) {
        dpiSelect.innerHTML = ''; // Vider les options existantes
        
        if (availableDpis && availableDpis.length > 0) {
            availableDpis.forEach(function(dpiValue) {
                if (dpiOptions && dpiOptions[dpiValue]) {
                    var option = document.createElement('option');
                    option.value = dpiValue;
                    option.textContent = dpiOptions[dpiValue];
                    if (templateDpi == dpiValue) {
                        option.selected = true;
                    }
                    dpiSelect.appendChild(option);
                }
            });
            dpiWarning.style.display = 'none';
        } else {
            // Aucune option disponible
            var option = document.createElement('option');
            option.value = '';
            option.textContent = 'Aucune résolution disponible';
            dpiSelect.appendChild(option);
            dpiWarning.style.display = 'block';
        }
    }

    // Remplir le select Format avec les options disponibles
    var formatSelect = document.getElementById('template-format');
    var formatWarning = document.getElementById('template-format-warning');
    
    if (formatSelect) {
        formatSelect.innerHTML = ''; // Vider les options existantes
        
        if (availableFormats && availableFormats.length > 0) {
            availableFormats.forEach(function(formatValue) {
                if (formatOptions && formatOptions[formatValue]) {
                    var option = document.createElement('option');
                    option.value = formatValue;
                    option.textContent = formatOptions[formatValue];
                    if (templateFormat == formatValue) {
                        option.selected = true;
                    }
                    formatSelect.appendChild(option);
                }
            });
            formatWarning.style.display = 'none';
        } else {
            // Aucune option disponible
            var option = document.createElement('option');
            option.value = '';
            option.textContent = 'Aucun format disponible';
            formatSelect.appendChild(option);
            formatWarning.style.display = 'block';
        }
    }

    // Remplir le select Orientation avec les options disponibles
    var orientationSelect = document.getElementById('template-orientation');
    var orientationWarning = document.getElementById('template-orientation-warning');
    
    if (orientationSelect) {
        orientationSelect.innerHTML = ''; // Vider les options existantes
        
        if (availableOrientations && availableOrientations.length > 0) {
            availableOrientations.forEach(function(orientationValue) {
                if (orientationOptions && orientationOptions[orientationValue]) {
                    var option = document.createElement('option');
                    option.value = orientationValue;
                    option.textContent = orientationOptions[orientationValue];
                    if (templateOrientation == orientationValue) {
                        option.selected = true;
                    }
                    orientationSelect.appendChild(option);
                }
            });
            orientationWarning.style.display = 'none';
        } else {
            // Aucune option disponible
            var option = document.createElement('option');
            option.value = '';
            option.textContent = 'Aucune orientation disponible';
            orientationSelect.appendChild(option);
            orientationWarning.style.display = 'block';
        }
    }
}

// Fonction pour sauvegarder les paramètres du template
function saveTemplateSettings() {
    if (!currentTemplateId) {
        alert('Erreur: Aucun template sélectionné');
        return;
    }
    
    
    
    // Récupérer les valeurs du formulaire
    var formData = new FormData();
    formData.append('action', 'pdf_builder_save_template_settings');
    formData.append('template_id', currentTemplateId);
    formData.append('nonce', pdfBuilderTemplatesNonce);
    formData.append('template_name', document.getElementById('template-name').value);
    formData.append('template_description', document.getElementById('template-description').value);
    formData.append('template_category', document.getElementById('template-category').value);
    formData.append('is_default', document.getElementById('template-is-default').checked ? '1' : '0');
    
    // Ajouter les paramètres canvas
    formData.append('canvas_format', document.getElementById('template-format').value);
    formData.append('canvas_orientation', document.getElementById('template-orientation').value);
    formData.append('canvas_dpi', document.getElementById('template-dpi').value);
    
    // Désactiver le bouton de sauvegarde
    var saveButton = document.querySelector('#template-settings-modal .button-primary');
    var originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<span class="dashicons dashicons-update" style="margin-right: 5px;"></span> Sauvegarde...';
    saveButton.disabled = true;
    
    // Faire la requête AJAX
    fetch(ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        
        
        if (data.success) {
            // Fermer la modale
            closeTemplateSettingsModal();
            
            // Afficher une notification de succès
            if (typeof window.showSuccessNotification !== 'undefined') {
                window.showSuccessNotification('Paramètres du template sauvegardés avec succès');
            } else {
                alert('Paramètres sauvegardés avec succès !');
            }
            
            // Recharger la page pour voir les changements
            setTimeout(function() {
                window.location.reload();
            }, 1000);
            
        } else {
            var errorMsg = data.data && data.data.message ? data.data.message : 'Erreur lors de la sauvegarde';
            
            // Afficher une notification d'erreur
            if (typeof window.showErrorNotification !== 'undefined') {
                window.showErrorNotification('Erreur lors de la sauvegarde: ' + errorMsg);
            } else {
                alert('Erreur lors de la sauvegarde: ' + errorMsg);
            }
        }
    })
    .catch(function(error) {
        
        
        // Afficher une notification d'erreur
        if (typeof window.showErrorNotification !== 'undefined') {
            window.showErrorNotification('Erreur de communication lors de la sauvegarde');
        } else {
            alert('Erreur de communication lors de la sauvegarde');
        }
    })
    .finally(function() {
        // Réactiver le bouton
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    });
}

// Fonctions utilitaires pour les templates
function duplicateTemplate(templateId, templateName) {
    if (confirm('Êtes-vous sûr de vouloir dupliquer le template "' + templateName + '" ?')) {
        // Implémentation de la duplication
        
        // TODO: Implémenter la duplication via AJAX
        alert('Fonction de duplication à implémenter');
    }
}

function confirmDeleteTemplate(templateId, templateName) {
    // Cette fonction est définie plus haut dans le fichier avec l'implémentation AJAX complète
    // Ne pas redéfinir ici pour éviter l'écrasement
}

function toggleDefaultTemplate(templateId, templateType, templateName) {
    
    // TODO: Implémenter le changement de template par défaut
    alert('Fonction de changement de template par défaut à implémenter');
}

// Fonctions pour la galerie de modèles
function closeTemplateGallery() {
    document.getElementById('template-gallery-modal').style.display = 'none';
}

function selectPredefinedTemplate(slug) {
    
    // TODO: Implémenter la sélection de modèle prédéfini
    alert('Fonction de sélection de modèle prédéfini à implémenter');
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    
    
    // Gestionnaire pour le bouton "Créer un template"
    var createBtn = document.getElementById('create-template-btn');
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            window.location.href = '<?php echo admin_url('admin.php?page=pdf-builder-react-editor'); ?>';
        });
    }
    
    // Gestionnaire pour le bouton "Parcourir les modèles"
    var galleryBtn = document.getElementById('open-template-gallery');
    if (galleryBtn) {
        galleryBtn.addEventListener('click', function() {
            <?php if ($is_premium): ?>
                // Utilisateur premium : ouvrir la galerie
                document.getElementById('template-gallery-modal').style.display = 'flex';
            <?php else: ?>
                // Utilisateur gratuit : ouvrir le modal d'upgrade
                showUpgradeModal('gallery');
            <?php endif; ?>
        });
    }
    
    // Gestionnaires pour les filtres
    var filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            filterBtns.forEach(function(b) { b.classList.remove('active'); });
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            var filter = this.getAttribute('data-filter');
            filterTemplates(filter);
        });
    });
    
    // Gestionnaires pour les filtres de la galerie
    var galleryFilterBtns = document.querySelectorAll('.gallery-filter-btn');
    galleryFilterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            galleryFilterBtns.forEach(function(b) { b.classList.remove('active'); });
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            var filter = this.getAttribute('data-filter');
            filterGalleryTemplates(filter);
        });
    });
});

function filterTemplates(filter) {
    var cards = document.querySelectorAll('.template-card');
    cards.forEach(function(card) {
        if (filter === 'all' || card.classList.contains('template-type-' + filter)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterGalleryTemplates(filter) {
    var cards = document.querySelectorAll('.predefined-template-card');
    cards.forEach(function(card) {
        if (filter === 'all' || card.getAttribute('data-category') === filter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

