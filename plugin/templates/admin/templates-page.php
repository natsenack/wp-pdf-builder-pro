<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * Templates Page - PDF Builder Pro
 * Gestion des t                <!-- Template Bon de Commande -->
                <div class="template-card" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">plates PDF
 */


?>

<div class="wrap">
    <h1><?php _e('[DOC] Gestion des Templates PDF', 'pdf-builder-pro'); ?></h1>

    <!-- Debug section removed for production: API debug UI and tests have been stripped -->

    <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2><?php _e('Templates Disponibles', 'pdf-builder-pro'); ?></h2>

        <div style="margin: 20px 0;">
            <a href="<?php echo admin_url('admin.php?page=pdf-builder-react-editor&template_id=0'); ?>" class="button button-primary">
                ➕ <?php _e('Créer un nouveau template', 'pdf-builder-pro'); ?>
            </a>
        </div>

        <!-- Section de filtrage -->
        <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
            <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">Filtrer par type</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
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
            <!-- Section Templates Builtin (Statiques) -->
            <h3 style="margin: 30px 0 15px 0; color: #23282d; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
                🎨 Templates Prédéfinis (Statiques)
            </h3>
            <p style="color: #666; margin-bottom: 20px;">
                Choisissez parmi nos templates professionnels statiques. Cliquez sur "Utiliser ce template" pour créer une copie personnalisable.
            </p>

            <?php
            // Récupérer les templates builtin
            $template_manager = $this->get_template_manager();
            $builtin_templates = $template_manager->get_builtin_templates();

            if (!empty($builtin_templates)) {
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px;">';

                foreach ($builtin_templates as $template) {
                    $template_name = isset($template['metadata']['name']) ? $template['metadata']['name'] : basename($template['_metadata']['filename']);
                    $template_description = isset($template['metadata']['description']) ? $template['metadata']['description'] : 'Template professionnel';
                    $template_category = isset($template['metadata']['category']) ? $template['metadata']['category'] : 'business';
                    $preview_url = isset($template['_metadata']['preview_url']) ? $template['_metadata']['preview_url'] : '';

                    // Déterminer l'icône basée sur le nom du template
                    $icon = '📄'; // Default
                    if (stripos($template_name, 'Modern') !== false) {
                        $icon = '🎨';
                    } elseif (stripos($template_name, 'Classic') !== false) {
                        $icon = '📜';
                    } elseif (stripos($template_name, 'Corporate') !== false) {
                        $icon = '🏢';
                    } elseif (stripos($template_name, 'Minimal') !== false) {
                        $icon = '✨';
                    }

                    // Déterminer la couleur basée sur la catégorie
                    $category_colors = [
                        'business' => '#007cba',
                        'modern' => '#28a745',
                        'creative' => '#ffc107'
                    ];
                    $category_color = isset($category_colors[$template_category]) ? $category_colors[$template_category] : '#6c757d';

                    echo '<div class="template-card builtin-template" style="border: 2px solid ' . $category_color . '; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer; min-height: 350px; position: relative;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.1)\';">';

                    // Badge "BUILTIN" en haut à droite
                    echo '<div class="builtin-badge" style="position: absolute; top: 10px; right: 10px; background: ' . $category_color . '; color: white; padding: 4px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">';
                    echo 'Prédéfini';
                    echo '</div>';

                    // Conteneur pour organiser le contenu de la carte
                    echo '<div style="display: flex; flex-direction: column; height: 100%;">';

                    echo '<div style="text-align: center; margin-bottom: 15px; margin-top: 20px;">';
                    if (!empty($preview_url)) {
                        echo '<div style="width: 100%; height: 120px; background: #f8f9fa; border-radius: 4px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; border: 1px solid #dee2e6;">';
                        echo '<img src="' . esc_url($preview_url) . '" alt="' . esc_attr($template_name) . '" style="max-width: 100%; max-height: 100%; border-radius: 4px;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';">';
                        echo '<div style="font-size: 2rem; color: #ccc; display: none;">' . $icon . '</div>';
                        echo '</div>';
                    } else {
                        echo '<div style="font-size: 3rem; margin-bottom: 10px; color: ' . $category_color . ';">' . $icon . '</div>';
                    }
                    echo '<h3 style="margin: 0; color: #23282d;">' . esc_html($template_name) . '</h3>';
                    echo '<p style="color: #666; margin: 5px 0; font-size: 14px;">' . esc_html($template_description) . '</p>';
                    echo '</div>';

                    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666; flex-grow: 1;">';
                    echo '<div>✓ Design professionnel</div>';
                    echo '<div>✓ Prêt à l\'emploi</div>';
                    echo '<div>✓ Personnalisable</div>';
                    echo '<div>✓ Aperçu statique</div>';
                    echo '</div>';

                    echo '<div style="display: flex; gap: 10px; margin-top: auto;">';
                    echo '<button class="button button-primary" style="flex: 1; font-size: 14px;" onclick="installBuiltinTemplate(\'' . $template['_metadata']['filename'] . '\', \'' . addslashes($template_name) . '\')" title="Installer ce template">📥 Utiliser ce template</button>';
                    echo '<button class="button button-secondary" style="flex: 1; font-size: 14px;" onclick="previewBuiltinTemplate(\'' . $template['_metadata']['filename'] . '\')" title="Aperçu">👁️</button>';
                    echo '</div>';

                    echo '</div>'; // Fermeture du conteneur flex
                    echo '</div>';
                }

                echo '</div>';
            } else {
                echo '<p style="color: #666; font-style: italic;">Aucun template prédéfini disponible pour le moment.</p>';
            }
            ?>

            <!-- Modal Template Gallery -->
            <div id="template-gallery-modal" class="template-gallery-modal" style="display: none;">
                <div class="template-gallery-overlay" onclick="closeTemplateGallery()"></div>
                <div class="template-gallery-content">
                    <div class="template-gallery-header">
                        <h2>🎨 Galerie de Templates</h2>
                        <button class="template-gallery-close" onclick="closeTemplateGallery()">&times;</button>
                    </div>

                    <div class="template-gallery-filters">
                        <button class="gallery-filter active" data-category="all">Tous</button>
                        <button class="gallery-filter" data-category="business">Business</button>
                        <button class="gallery-filter" data-category="modern">Moderne</button>
                        <button class="gallery-filter" data-category="creative">Créatif</button>
                    </div>

                    <div class="template-gallery-grid" id="template-gallery-grid">
                        <!-- Templates will be loaded here via JavaScript -->
                    </div>

                    <div class="template-gallery-footer">
                        <button class="button button-secondary" onclick="closeTemplateGallery()">Annuler</button>
                    </div>
                </div>
            </div>

            <!-- Section Templates Utilisateur -->
            <h3 style="margin: 30px 0 15px 0; color: #23282d; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
                📝 Mes Templates Personnalisés
            </h3>
            <p style="color: #666; margin-bottom: 20px;">
                Gérez vos templates personnalisés créés et modifiés.
            </p>

            <?php
            // Récupérer les templates depuis la base de données
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $templates = $wpdb->get_results("SELECT id, name, created_at, updated_at, is_default FROM $table_templates ORDER BY id", ARRAY_A);
            
            if (!empty($templates)) {
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">';
                
                foreach ($templates as $template) {
                    $template_id = $template['id'];
                    $template_name = esc_html($template['name']);
                    $created_at = isset($template['created_at']) ? $template['created_at'] : null;
                    $updated_at = isset($template['updated_at']) ? $template['updated_at'] : null;
                    $is_default = isset($template['is_default']) ? (bool)$template['is_default'] : false;
                    
                    // Déterminer le type de template pour l'icône par défaut
                    $template_type = 'autre';
                    if (stripos($template_name, 'facture') !== false) $template_type = 'facture';
                    elseif (stripos($template_name, 'devis') !== false) $template_type = 'devis';
                    elseif (stripos($template_name, 'commande') !== false) $template_type = 'commande';
                    elseif (stripos($template_name, 'contrat') !== false) $template_type = 'contrat';
                    elseif (stripos($template_name, 'newsletter') !== false) $template_type = 'newsletter';
                    
                    $button_text = '⚙️ Paramètres';
                    $button_action = 'openTemplateSettings';
                    
                    // Déterminer l'icône basée sur le nom du template
                    $icon = '📄'; // Default
                    $description = 'Template personnalisé';
                    $features = ['✓ Contenu personnalisable', '✓ Mise en page flexible', '✓ Éléments dynamiques', '✓ Export PDF'];
                    
                    if (stripos($template_name, 'facture') !== false) {
                        $icon = '🧾';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ En-tête société', '✓ Informations client', '✓ Tableau des articles', '✓ Totaux & TVA'];
                    } elseif (stripos($template_name, 'devis') !== false) {
                        $icon = '📋';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ Présentation entreprise', '✓ Détails du projet', '✓ Conditions & validité', '✓ Signature numérique'];
                    } elseif (stripos($template_name, 'commande') !== false) {
                        $icon = '📦';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ Numéro de commande', '✓ Liste des produits', '✓ Modalités de paiement', '✓ Conditions générales'];
                    } elseif (stripos($template_name, 'contrat') !== false) {
                        $icon = '📑';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ Parties contractantes', '✓ Objet du contrat', '✓ Conditions & obligations', '✓ Clauses légales'];
                    } elseif (stripos($template_name, 'newsletter') !== false) {
                        $icon = '📰';
                        $description = 'Template professionnel et élégant';
                        $features = ['✓ En-tête accrocheur', '✓ Sections d\'articles', '✓ Call-to-action', '✓ Pied de page'];
                    }
                    
                    echo '<div class="template-card template-type-' . $template_type . '" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer; min-height: 350px; position: relative;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.1)\';">';

                    // Conteneur pour organiser le contenu de la carte
                    echo '<div style="display: flex; flex-direction: column; height: 100%;">';

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
                    
                    echo '<div class="template-type-badge" style="position: absolute; top: 10px; left: 10px; background: ' . $type_color . '; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">';
                    echo $type_label;
                    echo '</div>';
                    
                    echo '<div style="text-align: center; margin-bottom: 15px; margin-top: 40px;">';
                    echo '<div style="font-size: 3rem; margin-bottom: 10px;">' . $icon . '</div>';
                    echo '<h3 style="margin: 0; color: #23282d;">' . $template_name . '</h3>';
                    echo '<p style="color: #666; margin: 5px 0;">' . $description . '</p>';
                    echo '</div>';
                    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666; flex-grow: 1;">';
                    foreach ($features as $feature) {
                        echo '<div>' . $feature . '</div>';
                    }
                    echo '</div>';
                    echo '<div style="display: flex; gap: 10px; margin-top: auto;">';
                    echo '<a href="' . admin_url('admin.php?page=pdf-builder-react-editor&template_id=' . $template_id) . '" class="button button-secondary" style="flex: 1; text-align: center; font-size: 16px;" title="Éditer ce template">✏️</a>';
                    echo '<button class="button button-secondary" style="flex: 1; font-size: 16px;" onclick="' . $button_action . '(' . $template_id . ', \'' . addslashes($template_name) . '\')" title="Paramètres">⚙️</button>';
                    echo '<button class="button button-primary" style="flex: 1; font-size: 16px;" onclick="alert(\'Fonctionnalité en développement\')" title="Dupliquer">📋</button>';
                    echo '<button class="button button-danger" style="flex: 1; font-size: 16px;" onclick="confirmDeleteTemplate(' . $template_id . ', \'' . addslashes($template_name) . '\')" title="Supprimer">🗑️</button>';
                    echo '</div>';
                    echo '</div>'; // Fermeture du conteneur flex
                    echo '</div>';
                }
                
                echo '</div>';
            } else {
                echo '<p>' . __('Aucun template trouvé. Créez votre premier template !', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Modale des paramètres du template -->
        <div id="template-settings-modal" class="template-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 100; align-items: center; justify-content: center;">
            <div class="template-modal-content" style="background: #fff; border-radius: 8px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <div class="template-modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #dee2e6; padding-bottom: 15px;">
                    <h2 style="margin: 0; color: #23282d;">⚙️ Paramètres du Template</h2>
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
                            <label style="display: flex; align-items: center; cursor: pointer;">
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

                <div class="template-modal-footer" style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 25px;">
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
// console.log('ajaxurl défini comme:', ajaxurl);

// Définir le nonce pour les templates
var pdfBuilderTemplatesNonce = '<?php echo wp_create_nonce("pdf_builder_templates"); ?>';

function openTemplateSettings(templateId, templateName) {
    currentTemplateId = templateId;

    // Afficher la modale des paramètres
    document.getElementById('template-settings-modal').style.display = 'flex';

    // Pré-remplir les champs avec les données actuelles
    document.getElementById('template-name-input').value = templateName;

    // Charger les paramètres depuis la base de données via AJAX
    loadTemplateSettings(templateId);
}

function closeTemplateSettings() {
    document.getElementById('template-settings-modal').style.display = 'none';
    currentTemplateId = null;
}

function loadTemplateSettings(templateId) {
    // Simulation du chargement des paramètres (à remplacer par un vrai appel AJAX)
    // Pour l'instant, on met des valeurs par défaut
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
                // Recharger la page pour voir les changements
                location.reload();
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
    // Simulation de la suppression (à remplacer par un vrai appel AJAX)
// Suppression du template

    // Afficher un message de succès temporaire
    alert('✅ Template "' + templateName + '" supprimé avec succès !');

    // Recharger la page pour voir les changements
    location.reload();
}

function toggleDefaultTemplate(templateId, templateType, templateName) {
// Toggle default template

    // Trouver l'icône du template actuel en utilisant l'attribut onclick
    const currentIcon = Array.from(document.querySelectorAll('.default-template-icon')).find(icon =>
        icon.onclick && icon.onclick.toString().includes(`toggleDefaultTemplate(${templateId}`)
    );
    const isCurrentlyDefault = currentIcon && currentIcon.style.opacity === '1';

    // Current icon found

    // Préparer les données pour AJAX
    const data = {
        action: 'pdf_builder_set_default_template',
        template_id: templateId,
        is_default: isCurrentlyDefault ? 0 : 1,
        nonce: pdfBuilderTemplatesNonce
    };

    // AJAX data
    // ajaxurl

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
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        z-index: 100;
        font-weight: bold;
        max-width: 400px;
    `;
    messageDiv.innerHTML = '✅ ' + message;

    document.body.appendChild(messageDiv);

    // Faire disparaître le message après 3 secondes
    setTimeout(() => {
        messageDiv.style.transition = 'opacity 0.5s';
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
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        z-index: 100;
        font-weight: bold;
        max-width: 400px;
    `;
    messageDiv.innerHTML = '❌ ' + message;

    document.body.appendChild(messageDiv);

    // Faire disparaître le message après 5 secondes
    setTimeout(() => {
        messageDiv.style.transition = 'opacity 0.5s';
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

// Fonction pour installer un template builtin
function installBuiltinTemplate(templateName, displayName) {
    if (confirm('Voulez-vous installer le template "' + displayName + '" ?\n\nUne copie personnalisable sera créée dans vos templates.')) {
        // Afficher un indicateur de chargement
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Installation...';
        button.disabled = true;

        // Faire l'appel AJAX
        jQuery.post(ajaxurl, {
            action: 'pdf_builder_install_builtin_template',
            template_name: templateName,
            custom_name: displayName + ' (Copie)',
            nonce: pdfBuilderTemplatesNonce
        }, function(response) {
            if (response.success) {
                // Succès
                button.innerHTML = '✅ Installé !';
                button.style.background = '#28a745';

                // Afficher un message de succès
                showSuccessMessage('Template "' + displayName + '" installé avec succès !');

                // Recharger la page après un délai
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                // Erreur
                button.innerHTML = '❌ Erreur';
                button.style.background = '#dc3545';
                showErrorMessage((response.data && response.data.message) || 'Erreur lors de l\'installation du template');

                // Remettre le bouton normal
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.background = '';
                    button.disabled = false;
                }, 3000);
            }
        }).fail(function(xhr, status, error) {
            // Erreur de réseau
            button.innerHTML = '❌ Erreur réseau';
            button.style.background = '#dc3545';
            showErrorMessage('Erreur de connexion: ' + error);

            // Remettre le bouton normal
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
                button.disabled = false;
            }, 3000);
        });
    }
}

// Fonction pour prévisualiser un template builtin
function previewBuiltinTemplate(templateName) {
    // Ouvrir une nouvelle fenêtre/onglet avec l'aperçu
    const previewUrl = '<?php echo esc_js(admin_url('admin.php?page=pdf-builder-preview&template=')); ?>' + encodeURIComponent(templateName) + '&type=builtin';
    window.open(previewUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
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

    // Template Gallery Modal Functionality
    let currentFilter = 'all';
    let loadedTemplates = [];

    // Open gallery modal
    jQuery('#open-template-gallery').on('click', function(e) {
        e.preventDefault();
        jQuery('.template-gallery-modal').fadeIn(300);
        loadTemplates();
    });

    // Close gallery modal
    jQuery('.template-gallery-close, .template-gallery-overlay').on('click', function() {
        jQuery('.template-gallery-modal').fadeOut(300);
    });

    // Filter templates
    jQuery('.gallery-filter').on('click', function() {
        jQuery('.gallery-filter').removeClass('active');
        jQuery(this).addClass('active');
        currentFilter = jQuery(this).data('filter');
        filterTemplates();
    });

    // Load templates via AJAX
    function loadTemplates() {
        jQuery('.template-gallery-grid').html('<div class="template-gallery-loading">Chargement des modèles...</div>');

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_builtin_templates',
                nonce: pdfBuilderTemplatesNonce
            },
            success: function(response) {
                if (response.success) {
                    loadedTemplates = response.data.templates;
                    renderTemplates(loadedTemplates);
                } else {
                    jQuery('.template-gallery-grid').html('<div class="notice notice-error"><p>Erreur lors du chargement des modèles.</p></div>');
                }
            },
            error: function() {
                jQuery('.template-gallery-grid').html('<div class="notice notice-error"><p>Erreur de connexion.</p></div>');
            }
        });
    }

    // Render templates in grid
    function renderTemplates(templates) {
        let html = '';

        templates.forEach(function(template, index) {
            const features = template.features || [];
            const featuresHtml = features.map(feature =>
                `<span class="template-gallery-item-feature">${feature}</span>`
            ).join('');

            html += `
                <div class="template-gallery-item" data-category="${template.category || 'general'}" style="animation-delay: ${index * 0.1}s">
                    <div class="template-gallery-item-preview">
                        <img src="${template.preview_url || '<?php echo plugin_dir_url(dirname(__FILE__, 2)); ?>assets/images/templates/placeholder.png'}" alt="${template.name}" loading="lazy">
                    </div>
                    <div class="template-gallery-item-info">
                        <h3 class="template-gallery-item-title">${template.name}</h3>
                        <p class="template-gallery-item-description">${template.description}</p>
                        <div class="template-gallery-item-features">
                            ${featuresHtml}
                        </div>
                        <div class="template-gallery-item-actions">
                            <button class="button button-primary install-template" data-template-id="${template.id}">
                                Installer
                            </button>
                            <button class="button button-secondary preview-template" data-template-id="${template.id}">
                                Aperçu
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        jQuery('.template-gallery-grid').html(html);
        filterTemplates();
    }

    // Filter templates based on current filter
    function filterTemplates() {
        if (currentFilter === 'all') {
            jQuery('.template-gallery-item').show();
        } else {
            jQuery('.template-gallery-item').hide();
            jQuery(`.template-gallery-item[data-category="${currentFilter}"]`).show();
        }
    }

    // Install template
    jQuery(document).on('click', '.install-template', function() {
        const templateId = jQuery(this).data('template-id');
        const $button = jQuery(this);

        $button.prop('disabled', true).text('Installation...');

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'install_builtin_template',
                template_id: templateId,
                nonce: pdfBuilderTemplatesNonce
            },
            success: function(response) {
                if (response.success) {
                    $button.removeClass('button-primary').addClass('button-success').text('Installé ✓');
                    showNotice('Modèle installé avec succès!', 'success');

                    // Refresh the templates list
                    if (typeof loadTemplatesList === 'function') {
                        loadTemplatesList();
                    }
                } else {
                    $button.prop('disabled', false).text('Installer');
                    showNotice(response.data.message || 'Erreur lors de l\'installation.', 'error');
                }
            },
            error: function() {
                $button.prop('disabled', false).text('Installer');
                showNotice('Erreur de connexion.', 'error');
            }
        });
    });

    // Preview template
    jQuery(document).on('click', '.preview-template', function() {
        const templateId = jQuery(this).data('template-id');
        const template = loadedTemplates.find(t => t.id === templateId);

        if (template && template.preview_url) {
            window.open(template.preview_url, '_blank');
        }
    });

    // Utility function to show notices
    function showNotice(message, type = 'info') {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const $notice = jQuery(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);

        jQuery('.wp-header-end').after($notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                jQuery(this).remove();
            });
        }, 5000);
    }
});

// Fermer les modales en cliquant en dehors
document.getElementById('template-settings-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTemplateSettings();
    }
});

// Fermer avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('template-settings-modal').style.display === 'flex') {
            closeTemplateSettings();
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
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
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

/* Template Gallery Modal Styles */
.template-gallery-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.template-gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(2px);
}

.template-gallery-content {
    position: relative;
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 90vw;
    max-height: 90vh;
    width: 1200px;
    overflow: hidden;
    animation: gallerySlideIn 0.3s ease-out;
}

@keyframes gallerySlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.template-gallery-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #e1e5e9;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.template-gallery-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.template-gallery-close {
    background: none;
    border: none;
    font-size: 28px;
    color: white;
    cursor: pointer;
    padding: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
}

.template-gallery-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.template-gallery-filters {
    padding: 20px 30px;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.gallery-filter {
    padding: 8px 16px;
    border: 2px solid #e1e5e9;
    background: white;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    color: #666;
}

.gallery-filter:hover {
    border-color: #007cba;
    color: #007cba;
}

.gallery-filter.active {
    background: #007cba;
    border-color: #007cba;
    color: white;
}

.template-gallery-grid {
    padding: 30px;
    max-height: 60vh;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.template-gallery-item {
    background: white;
    border: 2px solid #e1e5e9;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
    opacity: 0;
    transform: translateY(20px);
    animation: itemFadeIn 0.5s ease-out forwards;
}

.template-gallery-item:hover {
    border-color: #007cba;
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 123, 186, 0.2);
}

@keyframes itemFadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.template-gallery-item-preview {
    height: 180px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.template-gallery-item-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.template-gallery-item:hover .template-gallery-item-preview img {
    transform: scale(1.05);
}

.template-gallery-item-preview .preview-placeholder {
    font-size: 48px;
    color: #dee2e6;
}

.template-gallery-item-info {
    padding: 20px;
}

.template-gallery-item-title {
    font-size: 18px;
    font-weight: 600;
    color: #23282d;
    margin: 0 0 8px 0;
}

.template-gallery-item-description {
    font-size: 14px;
    color: #666;
    margin: 0 0 15px 0;
    line-height: 1.4;
}

.template-gallery-item-features {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.template-gallery-item-feature {
    font-size: 12px;
    color: #28a745;
    background: #d4edda;
    padding: 4px 8px;
    border-radius: 12px;
    border: 1px solid #c3e6cb;
}

.template-gallery-item-actions {
    display: flex;
    gap: 10px;
}

.template-gallery-item-actions .button {
    flex: 1;
    text-align: center;
    font-size: 14px;
    padding: 10px;
}

.template-gallery-footer {
    padding: 20px 30px;
    border-top: 1px solid #e1e5e9;
    display: flex;
    justify-content: flex-end;
    background: #f8f9fa;
}

.template-gallery-loading {
    text-align: center;
    padding: 60px;
    color: #666;
    font-size: 16px;
}

.template-gallery-loading::after {
    content: '';
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #007cba;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-left: 10px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.default-template-icon {
    transition: all 0.3s ease;
    user-select: none;
}

.default-template-icon:hover {
    opacity: 1 !important;
    transform: scale(1.2);
}

.template-type-badge {
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 2;
}

.filter-btn.active {
    box-shadow: 0 0 0 2px rgba(0,123,186,0.5) !important;
    font-weight: bold !important;
}


</style>


