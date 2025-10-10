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
            <!-- Templates temporaires pour démonstration -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">

                <!-- Template Facture -->
                <div class="template-card" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📄</div>
                        <h3 style="margin: 0; color: #23282d;">Facture Standard</h3>
                        <p style="color: #666; margin: 5px 0;">Template professionnel pour factures</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666;">
                        <div>✓ En-tête société</div>
                        <div>✓ Informations client</div>
                        <div>✓ Tableau des articles</div>
                        <div>✓ Totaux & TVA</div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&template_id=1'); ?>" class="button button-secondary" style="flex: 1; text-align: center;">✏️ Éditer</a>
                        <button class="button button-primary" style="flex: 1;" onclick="alert('Fonctionnalité en développement')">📋 Utiliser</button>
                    </div>
                </div>

                <!-- Template Devis -->
                <div class="template-card" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📋</div>
                        <h3 style="margin: 0; color: #23282d;">Devis Commercial</h3>
                        <p style="color: #666; margin: 5px 0;">Template élégant pour devis</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666;">
                        <div>✓ Présentation entreprise</div>
                        <div>✓ Détails du projet</div>
                        <div>✓ Conditions & validité</div>
                        <div>✓ Signature numérique</div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&template_id=2'); ?>" class="button button-secondary" style="flex: 1; text-align: center;">✏️ Éditer</a>
                        <button class="button button-primary" style="flex: 1;" onclick="alert('Fonctionnalité en développement')">📋 Utiliser</button>
                    </div>
                </div>

                <!-- Template Bon de commande -->
                <div class="template-card" style="border: 2px solid #e1e1e1; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">🛒</div>
                        <h3 style="margin: 0; color: #23282d;">Bon de Commande</h3>
                        <p style="color: #666; margin: 5px 0;">Template structuré pour commandes</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666;">
                        <div>✓ Numéro de commande</div>
                        <div>✓ Liste des produits</div>
                        <div>✓ Modalités de paiement</div>
                        <div>✓ Conditions générales</div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&template_id=3'); ?>" class="button button-secondary" style="flex: 1; text-align: center;">✏️ Éditer</a>
                        <button class="button button-primary" style="flex: 1;" onclick="alert('Fonctionnalité en développement')">📋 Utiliser</button>
                    </div>
                </div>

                <!-- Template Contrat -->
                <div class="template-card" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📝</div>
                        <h3 style="margin: 0; color: #23282d;">Contrat de Service</h3>
                        <p style="color: #666; margin: 5px 0;">Template juridique professionnel</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666;">
                        <div>✓ Parties contractantes</div>
                        <div>✓ Objet du contrat</div>
                        <div>✓ Conditions & obligations</div>
                        <div>✓ Clauses légales</div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&template_id=4'); ?>" class="button button-secondary" style="flex: 1; text-align: center;">✏️ Éditer</a>
                        <button class="button button-primary" style="flex: 1;" onclick="alert('Fonctionnalité en développement')">📋 Utiliser</button>
                    </div>
                </div>

                <!-- Template CV -->
                <div class="template-card" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">👤</div>
                        <h3 style="margin: 0; color: #23282d;">CV Moderne</h3>
                        <p style="color: #666; margin: 5px 0;">Template attractif pour CV</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666;">
                        <div>✓ Photo & coordonnées</div>
                        <div>✓ Expérience professionnelle</div>
                        <div>✓ Formation & compétences</div>
                        <div>✓ Centres d'intérêt</div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&template_id=5'); ?>" class="button button-secondary" style="flex: 1; text-align: center;">✏️ Éditer</a>
                        <button class="button button-primary" style="flex: 1;" onclick="alert('Fonctionnalité en développement')">📋 Utiliser</button>
                    </div>
                </div>

                <!-- Template Newsletter -->
                <div class="template-card" style="border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📧</div>
                        <h3 style="margin: 0; color: #23282d;">Newsletter</h3>
                        <p style="color: #666; margin: 5px 0;">Template engageant pour emails</p>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px; font-size: 12px; color: #666;">
                        <div>✓ En-tête accrocheur</div>
                        <div>✓ Sections d'articles</div>
                        <div>✓ Call-to-action</div>
                        <div>✓ Pied de page</div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&template_id=6'); ?>" class="button button-secondary" style="flex: 1; text-align: center;">✏️ Éditer</a>
                        <button class="button button-primary" style="flex: 1;" onclick="alert('Fonctionnalité en développement')">📋 Utiliser</button>
                    </div>
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