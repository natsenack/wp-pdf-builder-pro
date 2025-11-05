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
            <!-- Modal Template Gallery -->
            <div id="template-gallery-modal" class="template-gallery-modal" style="display: none;">
                <div class="template-gallery-overlay" onclick="closeTemplateGallery()"></div>
                <div class="template-gallery-content">
                    <div class="template-gallery-header">
                        <h2>🎨 Galerie de Templates</h2>
                        <button class="template-gallery-close" onclick="closeTemplateGallery()">&times;</button>
                    </div>

                    <div class="template-gallery-filters">
                        <button class="gallery-filter active" data-filter="all">Tous</button>
                        <button class="gallery-filter" data-filter="business">Business</button>
                        <button class="gallery-filter" data-filter="general">Général</button>
                        <button class="gallery-filter" data-filter="creative">Créatif</button>
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

// Function to close template gallery (for HTML onclick)
function closeTemplateGallery() {
    jQuery('.template-gallery-modal').fadeOut(300);
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
        currentFilter = jQuery(this).data('category');
        filterTemplates();
    });

    // Load templates via AJAX
    function loadTemplates() {
        console.log('[PDF Builder Debug] ===== LOAD TEMPLATES START =====');
        console.log('[PDF Builder Debug] loadTemplates() called');
        console.log('[PDF Builder Debug] ajaxurl:', ajaxurl);
        console.log('[PDF Builder Debug] pdfBuilderTemplatesNonce:', pdfBuilderTemplatesNonce);

        jQuery('.template-gallery-grid').html('<div class="template-gallery-loading">Chargement des modèles...</div>');

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_builtin_templates',
                nonce: pdfBuilderTemplatesNonce
            },
            success: function(response) {
                console.log('[PDF Builder Debug] ===== AJAX SUCCESS =====');
                console.log('[PDF Builder Debug] Full response:', response);
                console.log('[PDF Builder Debug] response.success:', response.success);
                console.log('[PDF Builder Debug] response.data:', response.data);

                if (response.success) {
                    console.log('[PDF Builder Debug] Templates loaded successfully');
                    console.log('[PDF Builder Debug] Number of templates:', response.data.templates ? response.data.templates.length : 'undefined');
                    loadedTemplates = response.data.templates;

                    // Log détaillé de chaque template
                    if (loadedTemplates && loadedTemplates.length > 0) {
                        console.log('[PDF Builder Debug] ===== TEMPLATE DETAILS =====');
                        loadedTemplates.forEach(function(template, index) {
                            console.log('[PDF Builder Debug] Template ' + index + ':', {
                                id: template.id,
                                name: template.name,
                                category: template.category,
                                hasPreview: template.preview_url ? 'YES' : 'NO',
                                previewUrl: template.preview_url,
                                elementCount: template.elements ? template.elements.length : 'undefined',
                                features: template.features
                            });
                        });
                    } else {
                        console.warn('[PDF Builder Debug] No templates loaded or empty array');
                    }

                    renderTemplates(loadedTemplates);
                } else {
                    console.error('[PDF Builder Debug] ===== SERVER ERROR =====');
                    console.error('[PDF Builder Debug] Server returned error:', response.data);
                    jQuery('.template-gallery-grid').html('<div class="notice notice-error"><p>Erreur lors du chargement des modèles.</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('[PDF Builder Debug] ===== AJAX ERROR =====');
                console.error('[PDF Builder Debug] AJAX error details:', {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status,
                    readyState: xhr.readyState
                });
                jQuery('.template-gallery-grid').html('<div class="notice notice-error"><p>Erreur de connexion.</p></div>');
            }
        });
    }

    // Render templates in grid
    function renderTemplates(templates) {
        console.log('[PDF Builder Debug] ===== RENDER TEMPLATES START =====');
        console.log('[PDF Builder Debug] renderTemplates called with', templates ? templates.length : 'null/undefined', 'templates');

        let html = '';

        if (!templates || templates.length === 0) {
            console.warn('[PDF Builder Debug] No templates to render');
            jQuery('.template-gallery-grid').html('<div class="notice notice-warning"><p>Aucun modèle trouvé.</p></div>');
            return;
        }

        templates.forEach(function(template, index) {
            console.log('[PDF Builder Debug] Rendering template ' + index + ':', template.name);

            var features = template.features || [];
            var featuresHtml = features.map(function(feature) {
                return '<span class="template-gallery-item-feature">' + feature + '</span>';
            }).join('');

            html += '<div class="template-gallery-item" data-category="' + (template.category || 'general') + '" style="animation-delay: ' + (index * 0.1) + 's">' +
                '<div class="template-gallery-item-preview">' +
                    '<img src="' + template.preview_url + '" alt="' + template.name + '" loading="lazy" onerror="console.error(\'Failed to load preview:\', this.src); this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';" onload="console.log(\'Preview loaded:\', this.src);">' +
                    '<div class="preview-placeholder" style="display: none;">' + template.name.charAt(0).toUpperCase() + '</div>' +
                '</div>' +
                '<div class="template-gallery-item-info">' +
                    '<h3 class="template-gallery-item-title">' + template.name + '</h3>' +
                    '<p class="template-gallery-item-description">' + template.description + '</p>' +
                    '<div class="template-gallery-item-features">' + featuresHtml + '</div>' +
                    '<div class="template-gallery-item-actions">' +
                        '<button class="button button-primary install-template" data-template-id="' + template.id + '">Utiliser ce template</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        });

        console.log('[PDF Builder Debug] ===== RENDER TEMPLATES END =====');
        console.log('[PDF Builder Debug] Generated HTML length:', html.length);
        console.log('[PDF Builder Debug] Setting HTML to .template-gallery-grid');

        jQuery('.template-gallery-grid').html(html);

        console.log('[PDF Builder Debug] HTML set, calling filterTemplates()');
        filterTemplates();

        console.log('[PDF Builder Debug] ===== RENDER COMPLETE =====');
        console.log('[PDF Builder Debug] Final template count in DOM:', jQuery('.template-gallery-item').length);
    }

    // Filter templates based on current filter
    function filterTemplates() {
        console.log('[PDF Builder Debug] ===== FILTER TEMPLATES =====');
        console.log('[PDF Builder Debug] currentFilter:', currentFilter);
        console.log('[PDF Builder Debug] Templates before filter:', jQuery('.template-gallery-item').length);

        if (currentFilter === 'all') {
            jQuery('.template-gallery-item').show();
        } else {
            jQuery('.template-gallery-item').hide();
            jQuery(`.template-gallery-item[data-category="${currentFilter}"]`).show();
        }

        console.log('[PDF Builder Debug] Templates after filter:', jQuery('.template-gallery-item:visible').length);
    }

    // Install template
    jQuery(document).on('click', '.install-template', function() {
        const templateId = jQuery(this).data('template-id');
        const $button = jQuery(this);

        $button.prop('disabled', true).text('Création du template...');

        // Create a new template based on builtin and redirect to editor
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_from_builtin',
                builtin_id: templateId,
                nonce: '<?php echo wp_create_nonce('pdf_builder_create_from_builtin'); ?>'
            },
            success: function(response) {
                if (response.success && response.data.template_id) {
                    // Redirect to editor with the new template ID
                    window.location.href = '<?php echo esc_js(admin_url('admin.php?page=pdf-builder-react-editor&template_id=')); ?>' + response.data.template_id;
                } else {
                    $button.prop('disabled', false).text('Erreur');
                    alert('Erreur lors de la création du template: ' + (response.data?.message || 'Erreur inconnue'));
                }
            },
            error: function() {
                $button.prop('disabled', false).text('Erreur');
                alert('Erreur de connexion');
            }
        });
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

    // Function to close template gallery (for HTML onclick)
    // Moved to global scope above
    // function closeTemplateGallery() {
    //     jQuery('.template-gallery-modal').fadeOut(300);
    // }
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
    padding: 15px 30px;
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
    padding: 12px 30px;
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
    max-height: 75vh;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); /* Increased from 280px to accommodate A4 proportions */
    gap: 20px;
}

.template-gallery-item {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid #e1e5e9;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    opacity: 0;
    transform: translateY(30px) scale(0.95);
    animation: itemFadeIn 0.6s ease-out forwards;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    position: relative;
}

.template-gallery-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #007cba, #28a745, #ffc107, #dc3545);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.template-gallery-item:hover {
    border-color: #007cba;
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 123, 186, 0.15);
}

.template-gallery-item:hover::before {
    opacity: 1;
}

@keyframes itemFadeIn {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.template-gallery-item-category {
    position: absolute;
    top: 12px;
    right: 12px;
    background: linear-gradient(135deg, rgba(0, 123, 186, 0.9) 0%, rgba(0, 86, 135, 0.9) 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    z-index: 2;
}

.template-gallery-item-popular {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    z-index: 2;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.template-gallery-item-preview {
    height: 400px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid #e1e5e9;
}

.template-gallery-item-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}

.template-gallery-item:hover .template-gallery-item-preview img {
    transform: scale(1.08) rotate(1deg);
    filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.15));
}

.template-gallery-item:hover .template-gallery-item-preview img {
    transform: scale(1.05);
}

.template-gallery-item-preview .preview-placeholder {
    font-size: 48px;
    color: #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    border-radius: 8px;
}

.template-gallery-item-info {
    padding: 24px;
    background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    position: relative;
}

.template-gallery-item-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 12px 0;
    letter-spacing: -0.025em;
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}

.template-gallery-item-title::after {
    content: '';
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, #007cba, transparent);
    border-radius: 1px;
}

.template-gallery-item-description {
    font-size: 14px;
    color: #4a5568;
    margin: 0 0 18px 0;
    line-height: 1.5;
    font-weight: 400;
}

.template-gallery-item-features {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 24px;
}

.template-gallery-item-feature {
    font-size: 11px;
    font-weight: 600;
    color: #2d3748;
    background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
    padding: 6px 12px;
    border-radius: 20px;
    border: 1px solid #81e6d9;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.template-gallery-item-feature::before {
    content: '✓';
    margin-right: 6px;
    color: #38b2ac;
    font-weight: bold;
}

.template-gallery-item-feature:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.template-gallery-item-actions {
    display: flex;
    gap: 12px;
}

.template-gallery-item-actions .button {
    flex: 1;
    text-align: center;
    font-size: 14px;
    padding: 12px 16px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.template-gallery-item-actions .button-primary {
    background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
    border: none;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 123, 186, 0.3);
}

.template-gallery-item-actions .button-primary:hover {
    background: linear-gradient(135deg, #005a87 0%, #004466 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 186, 0.4);
}

.template-gallery-item-actions .button-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border: none;
    color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.template-gallery-item-actions .button-secondary:hover {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

.template-gallery-footer {
    padding: 12px 30px;
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


