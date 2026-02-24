<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
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

// Vérifier si la notification a été rejetée (cookie)
$notice_dismissed = isset($_COOKIE['pdf_builder_template_limit_notice_dismissed']) && $_COOKIE['pdf_builder_template_limit_notice_dismissed'] === 'true';

// Vérifier le statut Premium
$is_premium = false;
if (class_exists('\PDF_Builder\Managers\PDF_Builder_License_Manager')) {
    $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
    $is_premium = $license_manager->isPremium();
}

// Compter les templates utilisateur
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';
$templates_count = (int) $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
    "SELECT COUNT(*) FROM $table_templates WHERE user_id = %d",
    get_current_user_id()
));

// Déterminer si l'utilisateur peut créer un nouveau template
// Premium: illimité, Gratuit: max 1 template
$user_can_create = $is_premium || $templates_count < 1;

// Créer templates par défaut si aucun template et utilisateur gratuit
if ($templates_count === 0 && !$is_premium) {
    \PDF_Builder\TemplateDefaults::create_default_templates_for_user(get_current_user_id());
    // Recharger le compteur après création
    $templates_count = (int) $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
        "SELECT COUNT(*) FROM $table_templates WHERE user_id = %d",
        get_current_user_id()
    ));
}

// Fonction helper pour parser les paramètres canvas
function parse_canvas_setting($setting_key, $default_value, $type = 'string') {
    // Lire directement depuis l'option séparée (cohérent avec Canvas_Manager.php)
    $setting_value = get_option($setting_key, $default_value);

    if (is_string($setting_value) && strpos($setting_value, ',') !== false) {
        $parsed = explode(',', $setting_value);
    } elseif (is_array($setting_value)) {
        $parsed = $setting_value;
    } else {
        // Valeur unique, la convertir en tableau
        $parsed = [$setting_value];
    }

    // Appliquer le type
    if ($type === 'int') {
        $parsed = array_map('intval', $parsed);
    } elseif ($type === 'string') {
        $parsed = array_map('strval', $parsed);
    }

    return $parsed;
}

// Récupérer les DPI disponibles depuis les paramètres canvas
$available_dpis = parse_canvas_setting('pdf_builder_canvas_dpi', '72,96,150', 'int');

// Récupérer les formats disponibles depuis les paramètres canvas
$available_formats = parse_canvas_setting('pdf_builder_canvas_formats', 'A4', 'string');

// Récupérer les orientations disponibles depuis les paramètres canvas
$available_orientations = parse_canvas_setting('pdf_builder_canvas_orientations', 'portrait,landscape', 'string');

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
var availableDpis = <?php echo json_encode($available_dpis, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var dpiOptions = <?php echo json_encode($dpi_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Variables pour les formats disponibles
var availableFormats = <?php echo json_encode($available_formats, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var formatOptions = <?php echo json_encode($format_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Variables pour les orientations disponibles
var availableOrientations = <?php echo json_encode($available_orientations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var orientationOptions = <?php echo json_encode($orientation_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>

<div class="wrap">
    <div class="pdfb-templates-page">
        
        <!-- Header avec actions -->
        <div class="pdfb-templates-header">
            <div class="pdfb-templates-header-left">
                <h1><?php esc_html_e('Templates PDF', 'pdf-builder-pro'); ?></h1>
                <p><?php esc_html_e('Créez et gérez vos modèles de documents PDF professionnels', 'pdf-builder-pro'); ?></p>
            </div>
            <div class="pdfb-templates-header-right">
                <?php if ($user_can_create): ?>
                    <a href="#" class="button" id="create-template-btn">
                        <span class="dashicons dashicons-plus"></span>
                        <?php esc_html_e('Créer un Template', 'pdf-builder-pro'); ?>
                    </a>
                <?php else: ?>
                    <button class="button" id="upgrade-required-btn" onclick="showUpgradeModal('gallery'); <?php if (!$is_premium): ?>showTemplateLimitNotice();<?php endif; ?>">
                        <span class="dashicons dashicons-lock"></span>
                        <?php esc_html_e('Créer un Template (Premium)', 'pdf-builder-pro'); ?>
                    </button>
                <?php endif; ?>
                <button id="open-template-gallery" class="button" onclick="<?php if ($is_premium): ?>document.getElementById('template-gallery-modal').style.display = 'flex';<?php else: ?>showUpgradeModal('gallery'); showTemplateLimitNotice();<?php endif; ?>">
                    🎨 <?php esc_html_e('Parcourir les Modèles', 'pdf-builder-pro'); ?>
                </button>
            </div>
        </div>

        <!-- Message limitation freemium -->
        <?php if (!$is_premium && $templates_count >= 1): ?>
            <div id="template-limit-notice" class="pdfb-notice-warning" style="<?php echo $notice_dismissed ? 'display: none;' : ''; ?>">
                <button type="button" class="pdfb-notice-dismiss" onclick="dismissTemplateLimitNotice()">
                    <span class="dashicons dashicons-dismiss"></span>
                </button>
                <h4>
                    <span class="dashicons dashicons-info"></span>
                    <?php esc_html_e('Limite de Templates Atteinte', 'pdf-builder-pro'); ?>
                </h4>
                <p>
                    <?php
                    printf(
                        /* translators: %d: number of templates created by the user */
                        esc_html__('Vous avez créé %d template gratuit sur 1. Passez en Premium pour créer des templates illimités !', 'pdf-builder-pro'),
                        intval($templates_count)
                    ); ?>
                </p>
                <a href="#" onclick="showUpgradeModal('gallery')" class="button button-primary">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php esc_html_e('Passer en Premium - 69.99€ à vie', 'pdf-builder-pro'); ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Barre de filtres -->
        <div class="pdfb-templates-filters">
            <strong>Filtrer par type</strong>
            <button class="pdfb-filter-btn pdfb-active" data-filter="all">📄 Tous</button>
            <button class="pdfb-filter-btn" data-filter="facture">🧾 Factures</button>
            <button class="pdfb-filter-btn" data-filter="devis">📋 Devis</button>
            <button class="pdfb-filter-btn" data-filter="commande">📦 Commandes</button>
            <button class="pdfb-filter-btn" data-filter="contrat">📑 Contrats</button>
            <button class="pdfb-filter-btn" data-filter="newsletter">📰 Newsletters</button>
            <button class="pdfb-filter-btn" data-filter="autre">📄 Autres</button>
        </div>

        <div id="templates-list" class="pdfb-templates-list">

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
            $columns = $wpdb->get_results("DESCRIBE $table_templates"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            $thumbnail_column_exists = false;
            foreach ($columns as $column) {
                if ($column->Field === 'thumbnail_url') {
                    $thumbnail_column_exists = true;
                    break;
                }
            }

            // Adapter la requête selon la disponibilité de la colonne
            if ($thumbnail_column_exists) {
                $templates = $wpdb->get_results($wpdb->prepare("SELECT id, name, thumbnail_url, created_at, updated_at, is_default, template_data FROM $table_templates WHERE user_id = %d ORDER BY id", get_current_user_id()), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            } else {
                $templates = $wpdb->get_results($wpdb->prepare("SELECT id, name, '' as thumbnail_url, created_at, updated_at, is_default, template_data FROM $table_templates WHERE user_id = %d ORDER BY id", get_current_user_id()), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            }

            if (!empty($templates)) {
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">';

                $template_counter = 0;
                foreach ($templates as $template) {
                    $template_counter++;
                    $template_id = $template['id'];
                    $template_name = $template['name'];
                    $thumbnail_url = isset($template['thumbnail_url']) ? $template['thumbnail_url'] : '';
                    
                    // Nettoyer la thumbnail_url - rejeter les URLs invalides (0.0.0.1, localhost, etc.)
                    if (!empty($thumbnail_url)) {
                        // Rejeter si contient 0.0.0.1 ou localhost ou vide
                        if (strpos($thumbnail_url, '0.0.0.1') !== false || 
                            strpos($thumbnail_url, 'localhost') !== false ||
                            strpos($thumbnail_url, '127.0.0.1') !== false ||
                            strlen(trim($thumbnail_url)) === 0) {
                            $thumbnail_url = '';
                        }
                    }
                    
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

                    // Déterminer si c'est un template "en trop" pour les utilisateurs gratuits
                    $is_excess_template = (!$is_premium && $template_counter > 1);

                    echo '<div class="pdfb-template-card template-type-' . esc_attr($template_type) . '" style="border: 2px solid #dee2e6; border-radius: 8px; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; padding: 20px; background: ' . ($is_excess_template ? '#f8f8f8' : '#fff') . '; box-shadow: 0 2px 8px rgba(0,0,0,0.1); -webkit-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -moz-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -ms-box-shadow: 0 2px 8px rgba(0,0,0,0.1); -o-box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; -webkit-transition: all 0.3s ease; -moz-transition: all 0.3s ease; -o-transition: all 0.3s ease; ' . ($is_excess_template ? 'opacity: 0.6;' : 'cursor: pointer;') . ' min-height: 350px; position: relative;" ' . ($is_excess_template ? '' : 'onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.1)\';"') . '>';

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

                    echo '<div class="pdfb-template-type-badge" style="position: absolute; top: 10px; left: 10px; background: ' . esc_attr($type_color) . '; color: white; padding: 4px 8px; border-radius: 12px; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">';
                    echo esc_html($type_label);
                    echo '</div>';

                    // Badge pour templates en trop (utilisateurs gratuits)
                    if ($is_excess_template) {
                        echo '<div class="excess-template-badge" style="position: absolute; top: 10px; right: 10px; background: #dc3545; color: white; padding: 4px 8px; border-radius: 12px; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">';
                        echo '🚫 LIMITE DÉPASSÉE';
                        echo '</div>';
                    }

                    echo '<div style="text-align: center; margin-bottom: 15px; margin-top: 40px;">';
                    // Créer un conteneur pour le preview qui sera chargé dynamiquement
                    echo '<div id="preview-' . intval($template_id) . '" class="pdfb-template-preview-container" data-template-id="' . intval($template_id) . '" style="width: 120px; height: 80px; margin: 0 auto 10px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">';
                    echo '<div style="text-align: center; color: #999;">';
                    echo '<div style="font-size: 2rem; margin-bottom: 5px;">📄</div>';
                    echo '<div style="font-size: 11px;">Aperçu</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<h3 style="margin: 0; color: #23282d;">' . esc_html($template_name) . '</h3>';
                    echo '<p style="color: #666; margin: 5px 0;">' . esc_html($description) . '</p>';
                    echo '</div>';
                    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; -ms-border-radius: 4px; -o-border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666; flex-grow: 1; -webkit-flex-grow: 1; -moz-flex-grow: 1; -ms-flex-grow: 1; -o-flex-grow: 1;">';
                    foreach ($features as $feature) {
                        echo '<div>' . esc_html($feature) . '</div>';
                    }
                    echo '</div>';
                    echo '<div style="display: flex; display: -webkit-flex; display: -moz-flex; display: -ms-flex; display: -o-flex; gap: 10px; margin-top: auto;">';
                    if ($is_excess_template) {
                        // Template en trop pour utilisateur gratuit - gros bouton supprimer au centre
                        echo '<div style="flex: 1; text-align: center;">';
                        echo '<button class="button button-danger" style="width: 100%; padding: 15px; font-size: 18px; background-color: #dc3545; border-color: #dc3545; color: white;" onclick="handleDeleteClick(' . intval($template_id) . ', \'' . esc_js($template_name) . '\')" title="Supprimer ce template (limite gratuite dépassée)">🚫 SUPPRIMER</button>';
                        echo '</div>';
                    } else {
                        // Boutons normaux
                        echo '<a href="' . esc_url(admin_url('admin.php?page=pdf-builder-react-editor&template_id=' . $template_id)) . '" class="button button-secondary" style="flex: 1; text-align: center; font-size: 16px;" title="Éditer ce template" onclick="console.log(\'[PDF Builder] Edit button clicked for template ID: ' . intval($template_id) . '\'); console.log(\'[PDF Builder] Navigating to editor for template: ' . esc_js($template_name) . '\');">✏️</a>';
                        echo '<button class="button button-secondary" style="flex: 1; font-size: 16px;" onclick="' . esc_js($button_action) . '(' . intval($template_id) . ', \'' . esc_js($template_name) . '\')" title="Paramètres">⚙️</button>';
                        if ($is_premium) {
                            echo '<button class="button button-primary" style="flex: 1; font-size: 16px;" onclick="duplicateTemplate(' . intval($template_id) . ', \'' . esc_js($template_name) . '\')" title="Dupliquer ce template">📋</button>';
                        } else {
                            echo '<button class="button button-primary" style="flex: 1; font-size: 16px; opacity: 0.5; cursor: not-allowed;" disabled title="Duplication réservée aux utilisateurs Premium">📋</button>';
                        }
                        echo '<button class="button button-danger" style="flex: 1; font-size: 16px;" onclick="handleDeleteClick(' . intval($template_id) . ', \'' . esc_js($template_name) . '\')" title="Supprimer">🗑️</button>';
                    }
                    echo '</div>';
                    echo '</div>'; // Fermeture du conteneur flex
                    echo '</div>';
                }

                echo '</div>';
            } else {
                echo '<p>' . esc_html__('Template principal introuvable. Veuillez contacter l\'administrateur.', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Modale de la galerie de modèles prédéfinis -->
        <div id="template-gallery-modal" class="pdfb-template-gallery-modal pdfb-template-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
            <div class="pdfb-template-modal-content" style="background: #fff; border-radius: 12px; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; padding: 0; max-width: 1200px; width: 95%; max-height: 90vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4); -webkit-box-shadow: 0 20px 60px rgba(0,0,0,0.4); -moz-box-shadow: 0 20px 60px rgba(0,0,0,0.4); -ms-box-shadow: 0 20px 60px rgba(0,0,0,0.4); -o-box-shadow: 0 20px 60px rgba(0,0,0,0.4);">
                <div class="pdfb-template-modal-header">
                    <div>
                        <h2 style="margin: 0; font-size: 24px; font-weight: 600;">🎨 Galerie de Modèles Prédéfinis</h2>
                        <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 14px; color: var(--pdf-text);">Choisissez un modèle professionnel pour commencer</p>
                    </div>
                    <button onclick="document.getElementById('template-gallery-modal').style.display='none'" style="background: var(--pdf-border); border: none; font-size: 24px; cursor: pointer; color: var(--pdf-secondary); padding: 8px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">×</button>
                </div>

                <div style="padding: 30px; max-height: calc(90vh - 120px); overflow-y: auto;">
                    <!-- Filtres de catégorie -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">Filtrer par catégorie</h3>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button class="pdfb-gallery-filter-btn button button-secondary active" data-filter="all">📄 Tous</button>
                            <button class="pdfb-gallery-filter-btn button button-secondary facture" data-filter="facture">🧾 Factures</button>
                            <button class="pdfb-gallery-filter-btn button button-secondary devis" data-filter="devis">📋 Devis</button>
                            <!-- Catégories désactivées temporairement -->
                            <!-- <button class="pdfb-gallery-filter-btn button button-secondary commande" data-filter="commande">📦 Commandes</button> -->
                            <!-- <button class="pdfb-gallery-filter-btn button button-secondary contrat" data-filter="contrat">📑 Contrats</button> -->
                            <!-- <button class="pdfb-gallery-filter-btn button button-secondary newsletter" data-filter="newsletter">📰 Newsletters</button> -->
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

                        // Filtrer : uniquement facture et devis
                        $templates = array_filter($templates, function($t) {
                            return in_array($t['category'], ['facture', 'devis'], true);
                        });

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
                                        <div style="position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: <?php echo esc_attr($type_color); ?>; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;"><?php echo esc_html(strtoupper($template['category'])); ?></div>
                                        <?php if ($template['is_premium']): ?>
                                        <div style="position: absolute; top: 15px; right: 15px; background: #ffd700; color: #000; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;">PREMIUM</div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="padding: 20px;">
                                        <h3 style="margin: 0 0 10px 0; color: #23282d; font-size: 18px;"><?php echo esc_html($template['name']); ?></h3>
                                        <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;"><?php echo esc_html($template['description']); ?></p>
                                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                            <span style="background: #f0f8ff; color: <?php echo esc_attr($type_color); ?>; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Template prédéfini</span>
                                            <span style="background: #f0f8ff; color: <?php echo esc_attr($type_color); ?>; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Prêt à utiliser</span>
                                            <span style="background: #f0f8ff; color: <?php echo esc_attr($type_color); ?>; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Personnalisable</span>
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

        <div id="no-templates" style="display: none; text-align: center; padding: 40px; color: var(--pdf-secondary);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
            <h3 style="color: var(--pdf-text);"><?php esc_html_e('Aucun template trouvé', 'pdf-builder-pro'); ?></h3>
            <p><?php esc_html_e('Créez votre premier template pour commencer à concevoir des PDF personnalisés.', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<!-- ✅ JavaScript déplacé vers settings-main.php pour éviter les conflits de navigation -->

<!-- Modaux premium injectés via AdminScriptLoader::renderUpgradeModals() dans admin_footer -->

<!-- JavaScript pour la gestion des templates -->
<script>
var currentTemplateId = null;
// FIN DU SCRIPT - Code nettoyé
function loadTemplateSettings(templateId) {
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

// Gestion de la notification de limite de templates
function dismissTemplateLimitNotice() {
    const notice = document.getElementById('template-limit-notice');
    if (notice) {
        notice.style.display = 'none';
        // Sauvegarder dans un cookie que l'utilisateur a fermé la notification
        document.cookie = 'pdf_builder_template_limit_notice_dismissed=true; path=/; max-age=86400'; // 24h
    }
}

function showTemplateLimitNotice() {
    const notice = document.getElementById('template-limit-notice');
    if (notice) {
        notice.style.display = 'block';
        // Supprimer le cookie de fermeture
        document.cookie = 'pdf_builder_template_limit_notice_dismissed=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
    }
}

// Vérifier au chargement de la page si la notification doit être affichée
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la notification de limite de templates
    const notice = document.getElementById('template-limit-notice');
    const cookies = document.cookie.split(';');
    let isDismissed = false;

    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'pdf_builder_template_limit_notice_dismissed' && value === 'true') {
            isDismissed = true;
            break;
        }
    }

    if (notice && isDismissed) {
        notice.style.display = 'none';
    }

    // Gestionnaire pour le bouton "Créer un template"
    var createBtn = document.getElementById('create-template-btn');
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            window.location.href = '<?php echo esc_url(admin_url('admin.php?page=pdf-builder-react-editor')); ?>';
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
    var filterBtns = document.querySelectorAll('.pdfb-filter-btn');
    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            filterBtns.forEach(function(b) { b.classList.remove('pdfb-active'); });
            // Ajouter la classe active au bouton cliqué
            this.classList.add('pdfb-active');

            var filter = this.getAttribute('data-filter');
            filterTemplates(filter);
        });
    });

    // Gestionnaires pour les filtres de la galerie
    var galleryFilterBtns = document.querySelectorAll('.pdfb-gallery-filter-btn');
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

    // Charger les aperçus de tous les templates
    var previewContainers = document.querySelectorAll('.template-preview-container');
    previewContainers.forEach(function(container) {
        var templateId = container.getAttribute('data-template-id');
        if (templateId) {
            loadTemplatePreview(templateId);
        }
    });
});

function filterTemplates(filter) {
    var cards = document.querySelectorAll('.pdfb-template-card');
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

// Fonction pour charger dynamiquement l'aperçu du template
function loadTemplatePreview(templateId) {
    var previewContainer = document.getElementById('preview-' + templateId);
    if (!previewContainer) return;
    // Afficher directement le preview stylisé (pas de AJAX nécessaire)
    showTemplateIconPreview(templateId, previewContainer);
}

// Afficher un simple aperçu avec icône et couleur du type
function showTemplateIconPreview(templateId, container) {
    const card = container.closest('.pdfb-template-card');
    if (!card) return;
    
    // Déterminer le type de template depuis les classes CSS
    let templateType = 'autre';
    const classes = card.className;
    if (classes.includes('template-type-facture')) templateType = 'facture';
    else if (classes.includes('template-type-devis')) templateType = 'devis';
    else if (classes.includes('template-type-commande')) templateType = 'commande';
    else if (classes.includes('template-type-contrat')) templateType = 'contrat';
    else if (classes.includes('template-type-newsletter')) templateType = 'newsletter';
    
    // Déterminer la couleur du type
    const colors = {
        'facture': '#007cba',
        'devis': '#28a745',
        'commande': '#ffc107',
        'contrat': '#dc3545',
        'newsletter': '#6f42c1',
        'autre': '#6c757d'
    };
    const color = colors[templateType] || colors['autre'];
    const icons = {
        'facture': '🧾',
        'devis': '📋',
        'commande': '📦',
        'contrat': '📑',
        'newsletter': '📰',
        'autre': '📄'
    };
    const icon = icons[templateType] || icons['autre'];
    
    // Afficher un aperçu stylisé
    container.innerHTML = '<div style="width: 100%; height: 100%; background: linear-gradient(135deg, ' + color + '20 0%, ' + color + '10 100%); display: flex; align-items: center; justify-content: center; border-radius: 4px; border: 2px solid ' + color + ';">' +
        '<div style="text-align: center; color: ' + color + ';">' +
        '<div style="font-size: 2rem; margin-bottom: 4px;">' + icon + '</div>' +
        '<div style="font-size: 10px; font-weight: 600; text-transform: uppercase;">' + templateType + '</div>' +
        '</div></div>';
}

function handleDeleteClick(templateId, templateName) {
    console.log('[DEBUG] handleDeleteClick appelée avec:', templateId, templateName);

    // Vérifier que la fonction confirmDeleteTemplate existe
    if (typeof confirmDeleteTemplate === 'function') {
        console.log('[DEBUG] confirmDeleteTemplate existe, appel de la fonction');
        confirmDeleteTemplate(templateId, templateName);
    } else {
        console.error('[DEBUG] confirmDeleteTemplate n\'est pas définie !');
        alert('Erreur: Fonction de suppression non disponible');
    }
}

function confirmDeleteTemplate(templateId, templateName) {
    console.log('[DEBUG] DÉBUT confirmDeleteTemplate - templateId:', templateId, 'templateName:', templateName);

    if (confirm('Êtes-vous sûr de vouloir supprimer définitivement le template "' + templateName + '" ?\n\nCette action ne peut pas être annulée.')) {
        console.log('[DEBUG] Utilisateur a confirmé la suppression');

        // Vérifier que jQuery est disponible
        if (typeof jQuery === 'undefined') {
            console.error('[DEBUG] jQuery n\'est pas disponible');
            alert('Erreur: jQuery n\'est pas chargé');
            return;
        }

        console.log('[DEBUG] jQuery disponible, ajaxurl:', ajaxurl);
        console.log('[DEBUG] pdfBuilderTemplatesNonce:', pdfBuilderTemplatesNonce);

        // Afficher les données qui vont être envoyées
        var postData = {
            action: 'pdf_builder_delete_template',
            template_id: templateId,
            nonce: pdfBuilderTemplatesNonce
        };
        console.log('[DEBUG] Données à envoyer:', postData);
        // alert('[DEBUG] Envoi AJAX - Template ID: ' + templateId + ', Nonce: ' + pdfBuilderTemplatesNonce.substring(0, 10) + '...');

        // Utiliser jQuery AJAX au lieu de fetch pour la compatibilité
        console.log('[DEBUG] Envoi de la requête AJAX...');
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: postData,
            success: function(response) {
                console.log('[DEBUG] Réponse AJAX reçue:', response);
                // alert('[DEBUG] Réponse AJAX reçue: ' + JSON.stringify(response));
                if (response.success) {
                    console.log('[DEBUG] Suppression réussie');
                    // alert('Template supprimé avec succès !');
                    location.reload();
                } else {
                    console.error('[DEBUG] Erreur dans la réponse:', response);
                    alert('Erreur lors de la suppression: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('[DEBUG] Erreur AJAX:', xhr, status, error);
                console.error('[DEBUG] Status:', status);
                console.error('[DEBUG] Error:', error);
                console.error('[DEBUG] Response text:', xhr.responseText);
                alert('[DEBUG] Erreur AJAX - Status: ' + status + ', Error: ' + error + ', Response: ' + xhr.responseText);
            }
        });
    } else {
        console.log('[DEBUG] Utilisateur a annulé la suppression');
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

function openTemplateSettings(templateId, templateName) {
    console.log('[DEBUG] openTemplateSettings appelée avec:', templateId, templateName);
    
    // Ouvrir la modale et charger les paramètres
    loadTemplateSettings(templateId);
}

function selectPredefinedTemplate(templateSlug) {
    // Rediriger vers l'éditeur avec le template prédéfini sélectionné
    const editorUrl = pdfBuilderAjax.editor_url + '&predefined_template=' + encodeURIComponent(templateSlug);
    window.location.href = editorUrl;
}
</script>

<!-- Modal des paramètres du template (dynamique - créée par JavaScript) -->
<div id="template-settings-modal" class="pdfb-template-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; align-items: center; justify-content: center;">
</div>

<script>
// Fonction pour fermer la modale des paramètres
function closeTemplateSettingsModal() {
    document.getElementById('template-settings-modal').style.display = 'none';
    currentTemplateId = null;
}

// Fonction pour charger les paramètres du template via AJAX
function loadTemplateSettings(templateId) {
    // Définir le template actuel pour la sauvegarde
    currentTemplateId = templateId;
    
    
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
                
                // Créer une modale d'erreur
                var modalContent = document.createElement('div');
                modalContent.className = ' pdfb-template-modal-content ';
                modalContent.style.cssText = 'background: #fff; border-radius: 12px; padding: 0; max-width: 600px; width: 95%; max-height: 80vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);';
                modalContent.innerHTML = `
                    <div class="pdfb-template-modal-header" style="padding: 25px 30px; border-bottom: 1px solid #e1e8ed; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div>
                            <h2 style="margin: 0; font-size: 24px; font-weight: 600;">⚙️ Erreur de chargement</h2>
                            <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">Impossible de charger les paramètres</p>
                        </div>
                        <button onclick="closeTemplateSettingsModal()" style="background: rgba(255,255,255,0.2); border: none; font-size: 24px; cursor: pointer; color: white; padding: 8px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">×</button>
                    </div>
                    <div class="pdfb-template-modal-body" style="padding: 30px; text-align: center; color: #dc3545;">
                        <div style="font-size: 2rem; margin-bottom: 20px;">❌</div>
                        <p>${errorMsg}</p>
                    </div>
                    <div class="pdfb-template-modal-footer" style="display: flex; justify-content: flex-end; gap: 15px; padding: 20px 30px; border-top: 1px solid #e1e8ed; background: #f8f9fa;">
                        <button onclick="closeTemplateSettingsModal()" class="button button-secondary" style="padding: 10px 20px;">Fermer</button>
                    </div>
                `;
                
                var modal = document.getElementById('template-settings-modal');
                modal.innerHTML = '';
                modal.appendChild(modalContent);
                modal.style.display = 'flex';
                
                // Afficher une notification d'erreur
                if (typeof window.showErrorNotification !== 'undefined') {
                    window.showErrorNotification('Erreur lors du chargement des paramètres: ' + errorMsg);
                }
            }
        },
        error: function(xhr, status, error) {
            
            // Créer une modale d'erreur
            var modalContent = document.createElement('div');
            modalContent.className = ' pdfb-template-modal-content ';
            modalContent.style.cssText = 'background: #fff; border-radius: 12px; padding: 0; max-width: 600px; width: 95%; max-height: 80vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);';
            modalContent.innerHTML = `
                <div class="pdfb-template-modal-header" style="padding: 25px 30px; border-bottom: 1px solid #e1e8ed; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div>
                        <h2 style="margin: 0; font-size: 24px; font-weight: 600;">⚙️ Erreur de chargement</h2>
                        <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">Impossible de charger les paramètres</p>
                    </div>
                    <button onclick="closeTemplateSettingsModal()" style="background: rgba(255,255,255,0.2); border: none; font-size: 24px; cursor: pointer; color: white; padding: 8px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">×</button>
                </div>
                <div class="pdfb-template-modal-body" style="padding: 30px; text-align: center; color: #dc3545;">
                    <div style="font-size: 2rem; margin-bottom: 20px;">❌</div>
                    <p>Erreur de communication avec le serveur</p>
                </div>
                <div class="pdfb-template-modal-footer" style="display: flex; justify-content: flex-end; gap: 15px; padding: 20px 30px; border-top: 1px solid #e1e8ed; background: #f8f9fa;">
                    <button onclick="closeTemplateSettingsModal()" class="button button-secondary" style="padding: 10px 20px;">Fermer</button>
                </div>
            `;
            
            var modal = document.getElementById('template-settings-modal');
            modal.innerHTML = '';
            modal.appendChild(modalContent);
            modal.style.display = 'flex';
            
            // Afficher une notification d'erreur
            if (typeof window.showErrorNotification !== 'undefined') {
                window.showErrorNotification('Erreur de communication lors du chargement des paramètres');
            }
        }
    });
}

// Fonction pour afficher les paramètres du template dans la modale
function displayTemplateSettings(template) {

    // Créer la modale complète avec indicateur de chargement
    var modal = document.getElementById('template-settings-modal');
    var modalContent = document.createElement('div');
    modalContent.className = ' pdfb-template-modal-content ';
    modalContent.style.cssText = 'background: #fff; border-radius: 12px; padding: 0; max-width: 600px; width: 95%; max-height: 80vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);';
    modalContent.innerHTML = `
        <div class="pdfb-template-modal-header" style="padding: 25px 30px; border-bottom: 1px solid #e1e8ed; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div>
                <h2 style="margin: 0; font-size: 24px; font-weight: 600;">⚙️ Paramètres du Template</h2>
                <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">Configuration de "${template.name || 'Template'}"</p>
            </div>
            <button onclick="closeTemplateSettingsModal()" style="background: rgba(255,255,255,0.2); border: none; font-size: 24px; cursor: pointer; color: white; padding: 8px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">×</button>
        </div>
        <div class="pdfb-template-modal-body" style="padding: 30px; max-height: calc(80vh - 200px); overflow-y: auto;">
            <div style="text-align: center; padding: 40px;">
                <div style="font-size: 2rem; margin-bottom: 20px;">⏳</div>
                <p>Chargement des paramètres...</p>
            </div>
        </div>
        <div class="pdfb-template-modal-footer pdfb-template-settings-modal-footer" style="padding: 20px 32px; border-top: 1px solid #e1e5e9; background: #f8f9fa; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; min-height: 80px;">
            <div class="pdfb-template-settings-footer-content" style="flex: 1; padding-right: 20px;">
                <div class="pdfb-template-settings-footer-title" style="font-size: 16px; font-weight: 600; color: #23282d; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <span class="pdfb-template-settings-icon">⚙️</span>
                    Paramètres du Template
                </div>
                <div class="pdfb-template-settings-footer-subtitle" style="font-size: 14px; color: #6c757d; font-style: italic;">
                    Configuration de "Template par défaut"
                </div>
            </div>
            <div class="pdfb-template-settings-footer-actions" style="display: flex; gap: 12px; flex-shrink: 0;">
                <button onclick="closeTemplateSettingsModal()" class="pdfb-canvas-modal-btn canvas-modal-btn-secondary" style="padding: 12px 24px; border-radius: 8px; font-weight: 500; font-size: 14px; border: none; cursor: pointer; min-width: 100px; background: #6c757d; color: #ffffff;">Annuler</button>
                <button onclick="saveTemplateSettings()" class="pdfb-canvas-modal-btn canvas-modal-btn-primary" style="padding: 12px 24px; border-radius: 8px; font-weight: 500; font-size: 14px; border: none; cursor: pointer; min-width: 100px; background: #007cba; color: #ffffff;">💾 Enregistrer</button>
            </div>
        </div>
    `;
    
    modal.innerHTML = '';
    modal.appendChild(modalContent);
    modal.style.display = 'flex';
    
    // Fermer le modal en cliquant sur l'overlay (en dehors du contenu)
    modal.onclick = function(e) {
        if (e.target === modal) {
            closeTemplateSettingsModal();
        }
    };

    // Maintenant traiter les données et remplacer le contenu
    var content = modalContent.querySelector('.pdfb-template-modal-body');
    
    // Vérifier que content existe
    if (!content) {
        console.error('[DEBUG] Element .pdfb-template-modal-body not found in modal');
        return; // Sortir si le contenu modal n'existe pas
    }

    // Valeurs par défaut depuis les paramètres du canvas
    var canvasFormat = template.canvas_settings?.default_canvas_format || 'A4';
    var canvasOrientation = template.canvas_settings?.default_canvas_orientation || 'portrait';
    var canvasDpi = template.canvas_settings?.default_canvas_dpi || 96;

    // Valeurs depuis template_data - gérer les deux formats (ancien et nouveau)
    var templateData = template.template_data || {};

    // Déterminer le format des données (ancien: canvasWidth/canvasHeight, nouveau: canvas_format/canvas_orientation)
    var templateFormat, templateOrientation, templateDpi;

    if (templateData.canvas_format && templateData.canvas_orientation && templateData.canvas_dpi) {
        // Nouveau format
        templateFormat = templateData.canvas_format;
        templateOrientation = templateData.canvas_orientation;
        templateDpi = templateData.canvas_dpi;
        console.log('[DEBUG] Utilisation du nouveau format de données');
    } else if (templateData.canvasWidth && templateData.canvasHeight) {
        // Ancien format - convertir les dimensions en format et orientation
        var width = parseFloat(templateData.canvasWidth);
        var height = parseFloat(templateData.canvasHeight);

        // Déterminer le format basé sur les dimensions (en mm approximatives)
        if (Math.abs(width - 210) < 10 && Math.abs(height - 297) < 10) {
            templateFormat = 'A4';
            templateOrientation = 'portrait';
        } else if (Math.abs(width - 297) < 10 && Math.abs(height - 210) < 10) {
            templateFormat = 'A4';
            templateOrientation = 'landscape';
        } else if (Math.abs(width - 148) < 10 && Math.abs(height - 210) < 10) {
            templateFormat = 'A5';
            templateOrientation = 'portrait';
        } else if (Math.abs(width - 210) < 10 && Math.abs(height - 148) < 10) {
            templateFormat = 'A5';
            templateOrientation = 'landscape';
        } else if (Math.abs(width - 216) < 10 && Math.abs(height - 279) < 10) { // 8.5*25.4 ≈ 216mm, 11*25.4 ≈ 279mm
            templateFormat = 'Letter';
            templateOrientation = 'portrait';
        } else if (Math.abs(width - 279) < 10 && Math.abs(height - 216) < 10) {
            templateFormat = 'Letter';
            templateOrientation = 'landscape';
        } else {
            // Format personnalisé ou inconnu - utiliser les valeurs par défaut
            templateFormat = canvasFormat;
            templateOrientation = canvasOrientation;
        }

        // Utiliser le DPI depuis template_data ou la valeur par défaut
        templateDpi = templateData.canvasDpi || canvasDpi;

        console.log('[DEBUG] Utilisation de l\'ancien format de données - conversion effectuée:', {
            width: width,
            height: height,
            detectedFormat: templateFormat,
            detectedOrientation: templateOrientation
        });
    } else {
        // Aucune donnée spécifique - utiliser les valeurs par défaut
        templateFormat = canvasFormat;
        templateOrientation = canvasOrientation;
        templateDpi = canvasDpi;
        console.log('[DEBUG] Aucune donnée canvas trouvée - utilisation des valeurs par défaut');
    }

    console.log('[DEBUG] Valeurs finales utilisées:', {
        templateFormat: templateFormat,
        templateOrientation: templateOrientation,
        templateDpi: templateDpi,
        templateData: templateData
    });
    
    // Options disponibles depuis les paramètres canvas
    var availableFormats = template.canvas_settings?.available_formats || ['A3', 'A4', 'A5', 'Letter', 'Legal'];
    var availableOrientations = template.canvas_settings?.available_orientations || ['portrait', 'landscape'];
    var availableDpis = template.canvas_settings?.available_dpi || [72, 96, 150, 300, 600];
    
    // Options d'affichage pour les selects
    var formatOptions = {
        'A3': 'A3 (297 × 420 mm)',
        'A4': 'A4 (210 × 297 mm)',
        'A5': 'A5 (148 × 210 mm)',
        'Letter': 'Letter (8.5 × 11 pouces)',
        'Legal': 'Legal (8.5 × 14 pouces)'
    };
    
    var orientationOptions = {
        'portrait': 'Portrait',
        'landscape': 'Paysage'
    };
    
    var dpiOptions = {
        72: '72 DPI (Écran)',
        96: '96 DPI (Web)',
        150: '150 DPI (Impression)',
        300: '300 DPI (Haute qualité)',
        600: '600 DPI (Très haute qualité)'
    };
    
    // Créer le formulaire HTML
    content.innerHTML = `
        <form id="template-settings-form">
            <!-- Champ caché pour le template ID -->
            <input type="hidden" id="settings-template-id" value="${currentTemplateId}">
            
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
                    <option value="facture" ${template.category === 'facture' ? 'selected' : ''}>🧾 Facture</option>
                    <option value="devis" ${template.category === 'devis' ? 'selected' : ''}>📋 Devis</option>
                    <option value="commande" ${template.category === 'commande' ? 'selected' : ''}>📦 Commande</option>
                    <option value="contrat" ${template.category === 'contrat' ? 'selected' : ''}>📑 Contrat</option>
                    <option value="newsletter" ${template.category === 'newsletter' ? 'selected' : ''}>📰 Newsletter</option>
                    <option value="autre" ${template.category === 'autre' ? 'selected' : ''}>📄 Autre</option>
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
    
    // Vérifier que les éléments existent
    if (!dpiSelect) {
        console.error('[DEBUG] Element template-dpi not found, waiting for DOM update');
        return;
    }
    
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
            if (dpiWarning) {
                dpiWarning.style.display = 'none';
            }
        } else {
            // Aucune option disponible
            var option = document.createElement('option');
            option.value = '';
            option.textContent = 'Aucune résolution disponible';
            dpiSelect.appendChild(option);
            if (dpiWarning) {
                dpiWarning.style.display = 'block';
            }
        }
    }

    // Remplir le select Format avec les options disponibles
    var formatSelect = document.getElementById('template-format');
    var formatWarning = document.getElementById('template-format-warning');
    
    // Vérifier que les éléments existent
    if (!formatSelect) {
        console.error('[DEBUG] Element template-format not found');
        return;
    }
    
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
            if (formatWarning) {
                formatWarning.style.display = 'none';
            }
        } else {
            // Aucune option disponible
            var option = document.createElement('option');
            option.value = '';
            option.textContent = 'Aucun format disponible';
            formatSelect.appendChild(option);
            if (formatWarning) {
                formatWarning.style.display = 'block';
            }
        }
    }

    // Remplir le select Orientation avec les options disponibles
    var orientationSelect = document.getElementById('template-orientation');
    var orientationWarning = document.getElementById('template-orientation-warning');
    
    // Vérifier que l'élément existe
    if (!orientationSelect) {
        console.error('[DEBUG] Element template-orientation not found');
        return;
    }
    
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
            if (orientationWarning) {
                orientationWarning.style.display = 'none';
            }
        } else {
            // Aucune option disponible
            var option = document.createElement('option');
            option.value = '';
            option.textContent = 'Aucune orientation disponible';
            orientationSelect.appendChild(option);
            if (orientationWarning) {
                orientationWarning.style.display = 'block';
            }
        }
    }

    // Afficher la modale
    var settingsModal = document.getElementById('template-settings-modal');
    if (settingsModal) {
        settingsModal.style.display = 'flex';
    } else {
        console.error('[DEBUG] Modal template-settings-modal not found');
    }
}

// Fonction pour sauvegarder les paramètres du template
function saveTemplateSettings() {
    if (!currentTemplateId) {
        if (typeof window.showErrorNotification !== 'undefined') {
            window.showErrorNotification('Erreur: Aucun template sélectionné');
        }
        return;
    }
    
    // Vérifier que tous les éléments existent avant de récupérer les valeurs
    var templateNameEl = document.getElementById('template-name');
    var templateDescriptionEl = document.getElementById('template-description');
    var templateCategoryEl = document.getElementById('template-category');
    var templateIsDefaultEl = document.getElementById('template-is-default');
    var templateFormatEl = document.getElementById('template-format');
    var templateOrientationEl = document.getElementById('template-orientation');
    var templateDpiEl = document.getElementById('template-dpi');
    
    if (!templateNameEl || !templateDescriptionEl || !templateCategoryEl || 
        !templateFormatEl || !templateOrientationEl || !templateDpiEl) {
        console.error('[DEBUG] Un ou plusieurs éléments du formulaire sont manquants');
        if (typeof window.showErrorNotification !== 'undefined') {
            window.showErrorNotification('Erreur: Formulaire incomplet');
        }
        return;
    }
    
    // Récupérer les valeurs du formulaire
    var formData = new FormData();
    formData.append('action', 'pdf_builder_save_template_settings');
    formData.append('template_id', currentTemplateId);
    formData.append('nonce', pdfBuilderTemplatesNonce);
    formData.append('template_name', templateNameEl.value);
    formData.append('template_description', templateDescriptionEl.value);
    formData.append('template_category', templateCategoryEl.value);
    formData.append('is_default', templateIsDefaultEl && templateIsDefaultEl.checked ? '1' : '0');
    
    // Ajouter les paramètres canvas
    formData.append('canvas_format', templateFormatEl.value);
    formData.append('canvas_orientation', templateOrientationEl.value);
    formData.append('canvas_dpi', templateDpiEl.value);
    
    // Désactiver le bouton de sauvegarde
    var saveButton = document.querySelector('#template-settings-modal .canvas-modal-btn-primary');
    if (!saveButton) {
        saveButton = document.querySelector('#template-settings-modal .pdfb-canvas-modal-btn-primary');
    }
    if (!saveButton) {
        saveButton = document.querySelector('#template-settings-modal .button-primary');
    }
    if (!saveButton) {
        // Fallback : trouver n'importe quel bouton dans le modal
        saveButton = document.querySelector('#template-settings-modal button[onclick*="saveTemplateSettings"]');
    }
    // Si toujours pas trouvé, continuer quand même sans désactiver le bouton
    var originalText = saveButton ? saveButton.innerHTML : null;
    
    // Désactiver le bouton pendant la sauvegarde
    if (saveButton) {
        saveButton.innerHTML = 'Sauvegarde en cours...';
        saveButton.disabled = true;
    }
    
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
            
            // Stocker le message de succès pour après le rechargement
            localStorage.setItem('pdfBuilderTemplateSuccess', 'Paramètres du template sauvegardés avec succès');
            
            // Afficher une notification de succès
            if (typeof window.showSuccessNotification !== 'undefined') {
                window.showSuccessNotification('Paramètres du template sauvegardés avec succès');
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
            }
        }
    })
    .catch(function(error) {
        
        
        // Afficher une notification d'erreur
        if (typeof window.showErrorNotification !== 'undefined') {
            window.showErrorNotification('Erreur de communication lors de la sauvegarde');
        }
    })
    .finally(function() {
        // Réactiver le bouton
        if (saveButton && originalText !== null) {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
        }
    });
}

// Initialisation - Vérifier les notifications après rechargement
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier s'il y a une notification de succès à afficher après rechargement
    var successMessage = localStorage.getItem('pdfBuilderTemplateSuccess');
    if (successMessage && typeof window.showSuccessNotification !== 'undefined') {
        window.showSuccessNotification(successMessage);
        localStorage.removeItem('pdfBuilderTemplateSuccess');
    }
});

</script>

