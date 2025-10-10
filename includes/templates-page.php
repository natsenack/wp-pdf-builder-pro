<?php
/**
 * Templates Page - PDF Builder Pro
 * Gestion des t                <!-- Template Bon de Commande -->
                <div class="template-card" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">plates PDF
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}
?>

<div class="wrap">
    <h1><?php _e('📄 Gestion des Templates PDF', 'pdf-builder-pro'); ?></h1>

    <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2><?php _e('Templates Disponibles', 'pdf-builder-pro'); ?></h2>

        <div style="margin: 20px 0;">
            <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&template_id=0'); ?>" class="button button-primary">
                ➕ <?php _e('Créer un nouveau template', 'pdf-builder-pro'); ?>
            </a>
        </div>

        <div id="templates-list" style="margin-top: 20px;">
            <?php
            // Récupérer les templates depuis la base de données
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $templates = $wpdb->get_results("SELECT id, name, created_at, updated_at FROM $table_templates ORDER BY id", ARRAY_A);
            
            if (!empty($templates)) {
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">';
                
                foreach ($templates as $template) {
                    $template_id = $template['id'];
                    $template_name = esc_html($template['name']);
                    $created_at = isset($template['created_at']) ? $template['created_at'] : null;
                    $updated_at = isset($template['updated_at']) ? $template['updated_at'] : null;
                    
                    $button_text = '⚙️ Paramètres';
                    $button_action = 'openTemplateSettings';
                    
                    // Déterminer l'icône basée sur le nom du template
                    $icon = '�'; // Default
                    $description = 'Template personnalisé';
                    $features = ['✓ Contenu personnalisable', '✓ Mise en page flexible', '✓ Éléments dynamiques', '✓ Export PDF'];
                    
                    if (stripos($template_name, 'facture') !== false) {
                        $icon = '📄';
                        $description = 'Template professionnel pour factures';
                        $features = ['✓ En-tête société', '✓ Informations client', '✓ Tableau des articles', '✓ Totaux & TVA'];
                    } elseif (stripos($template_name, 'devis') !== false) {
                        $icon = '📋';
                        $description = 'Template élégant pour devis';
                        $features = ['✓ Présentation entreprise', '✓ Détails du projet', '✓ Conditions & validité', '✓ Signature numérique'];
                    } elseif (stripos($template_name, 'commande') !== false) {
                        $icon = '🛒';
                        $description = 'Template structuré pour commandes';
                        $features = ['✓ Numéro de commande', '✓ Liste des produits', '✓ Modalités de paiement', '✓ Conditions générales'];
                    } elseif (stripos($template_name, 'contrat') !== false) {
                        $icon = '📝';
                        $description = 'Template juridique professionnel';
                        $features = ['✓ Parties contractantes', '✓ Objet du contrat', '✓ Conditions & obligations', '✓ Clauses légales'];
                    } elseif (stripos($template_name, 'newsletter') !== false) {
                        $icon = '📧';
                        $description = 'Template engageant pour emails';
                        $features = ['✓ En-tête accrocheur', '✓ Sections d\'articles', '✓ Call-to-action', '✓ Pied de page'];
                    }
                    
                    echo '<div class="template-card" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.1)\';">';
                    echo '<div style="text-align: center; margin-bottom: 15px;">';
                    echo '<div style="font-size: 3rem; margin-bottom: 10px;">' . $icon . '</div>';
                    echo '<h3 style="margin: 0; color: #23282d;">' . $template_name . '</h3>';
                    echo '<p style="color: #666; margin: 5px 0;">' . $description . '</p>';
                    echo '</div>';
                    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666;">';
                    foreach ($features as $feature) {
                        echo '<div>' . $feature . '</div>';
                    }
                    echo '</div>';
                    echo '<div style="display: flex; gap: 10px;">';
                    echo '<a href="' . admin_url('admin.php?page=pdf-builder-editor&template_id=' . $template_id) . '" class="button button-secondary" style="flex: 1; text-align: center;">✏️ Éditer</a>';
                    echo '<button class="button button-secondary" style="flex: 1;" onclick="' . $button_action . '(' . $template_id . ', \'' . addslashes($template_name) . '\')">' . $button_text . '</button>';
                    echo '<button class="button button-primary" style="flex: 1;" onclick="alert(\'Fonctionnalité en développement\')">📋 Utiliser</button>';
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
            } else {
                echo '<p>' . __('Aucun template trouvé. Créez votre premier template !', 'pdf-builder-pro') . '</p>';
            }
            ?>
        </div>

        <!-- Modale des paramètres du template -->
        <div id="template-settings-modal" class="template-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
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
                                <input type="checkbox" id="template-active" style="margin-right: 8px;">
                                <span>Template actif</span>
                            </label>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" id="template-public" style="margin-right: 8px;">
                                <span>Template public (visible par tous les utilisateurs)</span>
                            </label>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Format de papier</label>
                            <select id="template-paper-size" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                <option value="A4">A4 (210 × 297 mm)</option>
                                <option value="A3">A3 (297 × 420 mm)</option>
                                <option value="Letter">Letter (8.5 × 11 pouces)</option>
                                <option value="Legal">Legal (8.5 × 14 pouces)</option>
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
            <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
            <h3><?php _e('Aucun template trouvé', 'pdf-builder-pro'); ?></h3>
            <p><?php _e('Créez votre premier template pour commencer à concevoir des PDF personnalisés.', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<script>
let currentTemplateId = null;

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
    document.getElementById('template-active').checked = true;
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
        id: currentTemplateId,
        name: document.getElementById('template-name-input').value,
        description: document.getElementById('template-description-input').value,
        active: document.getElementById('template-active').checked,
        public: document.getElementById('template-public').checked,
        paperSize: document.getElementById('template-paper-size').value,
        orientation: document.getElementById('template-orientation').value,
        category: document.getElementById('template-category').value
    };

    // Simulation de la sauvegarde (à remplacer par un vrai appel AJAX)
    console.log('Sauvegarde des paramètres:', settings);

    // Afficher un message de succès temporaire
    const saveButton = document.querySelector('.template-modal-footer .button-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '✅ Enregistré !';
    saveButton.style.background = '#28a745';

    setTimeout(() => {
        saveButton.innerHTML = originalText;
        saveButton.style.background = '';
        closeTemplateSettings();
    }, 1500);
}

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
</style>

