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
            <button id="open-template-gallery" class="button button-secondary" style="margin-left: 10px;">
                🎨 <?php _e('Parcourir les Modèles', 'pdf-builder-pro'); ?>
            </button>
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

        <!-- Modale de la galerie de modèles prédéfinis -->
        <div id="template-gallery-modal" class="template-gallery-modal template-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
            <div class="template-modal-content" style="background: #fff; border-radius: 12px; padding: 0; max-width: 1200px; width: 95%; max-height: 90vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.4);">
                <div class="template-modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 25px 30px; border-bottom: 1px solid #e1e8ed; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div>
                        <h2 style="margin: 0; font-size: 24px; font-weight: 600;">🎨 Galerie de Modèles Prédéfinis</h2>
                        <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">Choisissez un modèle professionnel pour commencer</p>
                    </div>
                    <button onclick="closeTemplateGallery()" style="background: rgba(255,255,255,0.2); border: none; font-size: 24px; cursor: pointer; color: white; padding: 8px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">×</button>
                </div>

                <div style="padding: 30px; max-height: calc(90vh - 120px); overflow-y: auto;">
                    <!-- Filtres de catégorie -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="margin: 0 0 15px 0; color: #23282d; font-size: 16px;">Filtrer par catégorie</h3>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button class="gallery-filter-btn button button-secondary active" data-filter="all" style="font-size: 12px; padding: 8px 16px; border-radius: 20px;">📄 Tous</button>
                            <button class="gallery-filter-btn button button-secondary" data-filter="facture" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #007cba; border-color: #007cba; color: white;">🧾 Factures</button>
                            <button class="gallery-filter-btn button button-secondary" data-filter="devis" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #28a745; border-color: #28a745; color: white;">📋 Devis</button>
                            <button class="gallery-filter-btn button button-secondary" data-filter="commande" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #ffc107; border-color: #ffc107; color: #212529;">📦 Commandes</button>
                            <button class="gallery-filter-btn button button-secondary" data-filter="contrat" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #dc3545; border-color: #dc3545; color: white;">📑 Contrats</button>
                            <button class="gallery-filter-btn button button-secondary" data-filter="newsletter" style="font-size: 12px; padding: 8px 16px; border-radius: 20px; background: #6f42c1; border-color: #6f42c1; color: white;">📰 Newsletters</button>
                        </div>
                    </div>

                    <!-- Grille des modèles prédéfinis -->
                    <div id="predefined-templates-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">

                        <!-- Template Facture Moderne -->
                        <div class="predefined-template-card" data-category="facture" style="border: 2px solid #e1e8ed; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';">
                            <div style="height: 160px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="font-size: 4rem; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">🧾</div>
                                <div style="position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: #007cba; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">FACTURE</div>
                            </div>
                            <div style="padding: 20px;">
                                <h3 style="margin: 0 0 10px 0; color: #23282d; font-size: 18px;">Facture Moderne</h3>
                                <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">Template professionnel avec en-tête société, tableau des articles détaillé et totaux automatiques.</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                    <span style="background: #f0f8ff; color: #007cba; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ En-tête société</span>
                                    <span style="background: #f0f8ff; color: #007cba; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Tableau articles</span>
                                    <span style="background: #f0f8ff; color: #007cba; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Totaux & TVA</span>
                                </div>
                                <button class="button button-primary" style="width: 100%; border-radius: 6px;" onclick="selectPredefinedTemplate('facture-moderne')">Utiliser ce modèle</button>
                            </div>
                        </div>

                        <!-- Template Devis Élégant -->
                        <div class="predefined-template-card" data-category="devis" style="border: 2px solid #e1e8ed; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';">
                            <div style="height: 160px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="font-size: 4rem; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">📋</div>
                                <div style="position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: #28a745; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">DEVIS</div>
                            </div>
                            <div style="padding: 20px;">
                                <h3 style="margin: 0 0 10px 0; color: #23282d; font-size: 18px;">Devis Élégant</h3>
                                <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">Présentation professionnelle avec conditions, validité et signature numérique.</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                    <span style="background: #f0fff0; color: #28a745; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Présentation entreprise</span>
                                    <span style="background: #f0fff0; color: #28a745; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Conditions & validité</span>
                                    <span style="background: #f0fff0; color: #28a745; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Signature numérique</span>
                                </div>
                                <button class="button button-primary" style="width: 100%; border-radius: 6px;" onclick="selectPredefinedTemplate('devis-elegant')">Utiliser ce modèle</button>
                            </div>
                        </div>

                        <!-- Template Bon de Commande -->
                        <div class="predefined-template-card" data-category="commande" style="border: 2px solid #e1e8ed; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';">
                            <div style="height: 160px; background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="font-size: 4rem; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">📦</div>
                                <div style="position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: #ffc107; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">COMMANDE</div>
                            </div>
                            <div style="padding: 20px;">
                                <h3 style="margin: 0 0 10px 0; color: #23282d; font-size: 18px;">Bon de Commande</h3>
                                <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">Suivi des commandes avec numéro unique, liste des produits et modalités de paiement.</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                    <span style="background: #fff8e1; color: #ffc107; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Numéro de commande</span>
                                    <span style="background: #fff8e1; color: #ffc107; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Liste des produits</span>
                                    <span style="background: #fff8e1; color: #ffc107; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Modalités paiement</span>
                                </div>
                                <button class="button button-primary" style="width: 100%; border-radius: 6px;" onclick="selectPredefinedTemplate('bon-commande')">Utiliser ce modèle</button>
                            </div>
                        </div>

                        <!-- Template Contrat Professionnel -->
                        <div class="predefined-template-card" data-category="contrat" style="border: 2px solid #e1e8ed; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';">
                            <div style="height: 160px; background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="font-size: 4rem; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">📑</div>
                                <div style="position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: #dc3545; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">CONTRAT</div>
                            </div>
                            <div style="padding: 20px;">
                                <h3 style="margin: 0 0 10px 0; color: #23282d; font-size: 18px;">Contrat Professionnel</h3>
                                <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">Document juridique complet avec parties contractantes et clauses légales.</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                    <span style="background: #ffeef0; color: #dc3545; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Parties contractantes</span>
                                    <span style="background: #ffeef0; color: #dc3545; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Objet du contrat</span>
                                    <span style="background: #ffeef0; color: #dc3545; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Clauses légales</span>
                                </div>
                                <button class="button button-primary" style="width: 100%; border-radius: 6px;" onclick="selectPredefinedTemplate('contrat-professionnel')">Utiliser ce modèle</button>
                            </div>
                        </div>

                        <!-- Template Newsletter Moderne -->
                        <div class="predefined-template-card" data-category="newsletter" style="border: 2px solid #e1e8ed; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';">
                            <div style="height: 160px; background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="font-size: 4rem; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">📰</div>
                                <div style="position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: #6f42c1; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">NEWSLETTER</div>
                            </div>
                            <div style="padding: 20px;">
                                <h3 style="margin: 0 0 10px 0; color: #23282d; font-size: 18px;">Newsletter Moderne</h3>
                                <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">Communication engageante avec en-tête accrocheur et sections d'articles.</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                    <span style="background: #f3f0ff; color: #6f42c1; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ En-tête accrocheur</span>
                                    <span style="background: #f3f0ff; color: #6f42c1; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Sections d'articles</span>
                                    <span style="background: #f3f0ff; color: #6f42c1; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Call-to-action</span>
                                </div>
                                <button class="button button-primary" style="width: 100%; border-radius: 6px;" onclick="selectPredefinedTemplate('newsletter-moderne')">Utiliser ce modèle</button>
                            </div>
                        </div>

                        <!-- Template Facture Minimaliste -->
                        <div class="predefined-template-card" data-category="facture" style="border: 2px solid #e1e8ed; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';">
                            <div style="height: 160px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="font-size: 4rem; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">📄</div>
                                <div style="position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: #6c757d; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">MINIMALISTE</div>
                            </div>
                            <div style="padding: 20px;">
                                <h3 style="margin: 0 0 10px 0; color: #23282d; font-size: 18px;">Facture Minimaliste</h3>
                                <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">Design épuré et moderne, parfait pour une image professionnelle discrète.</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                    <span style="background: #f8f9fa; color: #6c757d; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Design épuré</span>
                                    <span style="background: #f8f9fa; color: #6c757d; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Typographie claire</span>
                                    <span style="background: #f8f9fa; color: #6c757d; padding: 3px 8px; border-radius: 10px; font-size: 11px;">✓ Focus sur le contenu</span>
                                </div>
                                <button class="button button-primary" style="width: 100%; border-radius: 6px;" onclick="selectPredefinedTemplate('facture-minimaliste')">Utiliser ce modèle</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
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

// Function to select a predefined template
function selectPredefinedTemplate(templateSlug) {
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '⏳ Création en cours...';
    button.disabled = true;

    // Define template data based on slug
    const templateData = getTemplateData(templateSlug);

    // Create template via AJAX
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'pdf_builder_create_template_from_gallery',
            template_data: templateData,
            nonce: pdfBuilderTemplatesNonce
        },
        success: function(response) {
            if (response.success) {
                // Close gallery modal
                closeTemplateGallery();

                // Redirect to editor with new template
                window.location.href = '<?php echo admin_url('admin.php?page=pdf-builder-react-editor&template_id='); ?>' + response.data.template_id;
            } else {
                alert('Erreur lors de la création du template: ' + response.data.message);
                // Reset button
                button.textContent = originalText;
                button.disabled = false;
            }
        },
        error: function() {
            alert('Erreur de communication avec le serveur.');
            // Reset button
            button.textContent = originalText;
            button.disabled = false;
        }
    });
}

// Function to get template data based on slug
function getTemplateData(slug) {
    const templates = {
        'facture-moderne': {
            name: 'Facture Moderne',
            category: 'facture',
            description: 'Template professionnel avec en-tête société, tableau des articles détaillé et totaux automatiques.',
            elements: [
                // Header section
                {
                    type: 'header',
                    x: 50, y: 50, width: 500, height: 100,
                    content: {
                        company_name: '{{company_name}}',
                        company_address: '{{company_address}}',
                        company_phone: '{{company_phone}}',
                        company_email: '{{company_email}}'
                    }
                },
                // Invoice details
                {
                    type: 'text',
                    x: 400, y: 50, width: 150, height: 30,
                    content: { text: 'FACTURE N° {{invoice_number}}' },
                    style: { fontSize: 16, fontWeight: 'bold' }
                },
                // Client info
                {
                    type: 'text',
                    x: 50, y: 180, width: 250, height: 80,
                    content: { text: 'Client:\n{{client_name}}\n{{client_address}}' }
                },
                // Date
                {
                    type: 'text',
                    x: 400, y: 180, width: 150, height: 20,
                    content: { text: 'Date: {{invoice_date}}' }
                },
                // Items table
                {
                    type: 'table',
                    x: 50, y: 280, width: 500, height: 200,
                    content: {
                        headers: ['Description', 'Qté', 'Prix', 'Total'],
                        rows: '{{invoice_items}}'
                    }
                },
                // Totals
                {
                    type: 'text',
                    x: 350, y: 500, width: 200, height: 80,
                    content: { text: 'Sous-total: {{subtotal}}\nTVA: {{tax}}\nTotal: {{total}}' },
                    style: { textAlign: 'right' }
                }
            ]
        },
        'devis-elegant': {
            name: 'Devis Élégant',
            category: 'devis',
            description: 'Présentation professionnelle avec conditions, validité et signature numérique.',
            elements: [
                // Similar structure for quote template
                {
                    type: 'header',
                    x: 50, y: 50, width: 500, height: 100,
                    content: {
                        company_name: '{{company_name}}',
                        company_address: '{{company_address}}'
                    }
                },
                {
                    type: 'text',
                    x: 400, y: 50, width: 150, height: 30,
                    content: { text: 'DEVIS N° {{quote_number}}' },
                    style: { fontSize: 16, fontWeight: 'bold' }
                }
                // Add more elements as needed
            ]
        },
        'bon-commande': {
            name: 'Bon de Commande',
            category: 'commande',
            description: 'Suivi des commandes avec numéro unique, liste des produits et modalités de paiement.',
            elements: []
        },
        'contrat-professionnel': {
            name: 'Contrat Professionnel',
            category: 'contrat',
            description: 'Document juridique complet avec parties contractantes et clauses légales.',
            elements: []
        },
        'newsletter-moderne': {
            name: 'Newsletter Moderne',
            category: 'newsletter',
            description: 'Communication engageante avec en-tête accrocheur et sections d\'articles.',
            elements: []
        },
        'facture-minimaliste': {
            name: 'Facture Minimaliste',
            category: 'facture',
            description: 'Design épuré et moderne, parfait pour une image professionnelle discrète.',
            elements: []
        }
    };

    return templates[slug] || null;
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
        openGalleryBtn.addEventListener('click', openTemplateGallery);
    }
});

// Fermer les modales en cliquant en dehors
document.getElementById('template-settings-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTemplateSettings();
    }
});

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



.gallery-filter-btn.active {
    box-shadow: 0 0 0 2px rgba(0,123,186,0.5) !important;
    font-weight: bold !important;
}


</style>


