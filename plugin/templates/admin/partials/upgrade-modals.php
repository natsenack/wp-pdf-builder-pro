<?php
/**
 * Partial partagé — Modaux d'upgrade premium
 * Inclus dans : templates-page.php ET admin-editor.php
 */
if (!defined('ABSPATH')) exit;
?>

<!-- Modal d'upgrade pour création de templates -->
<div id="upgrade-modal-template" class="pdfb-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 99999; justify-content: center; align-items: center;">
    <div class="pdfb-modal-content" style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div class="pdfb-modal-header" style="padding: 20px 30px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #23282d; font-size: 20px;">🚀 Débloquer les Templates</h3>
            <button class="pdfb-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
        </div>
        <div class="pdfb-modal-body" style="padding: 30px;">
            <div class="upgrade-feature" style="text-align: center; margin-bottom: 30px;">
                <div class="pdfb-feature-icon" style="font-size: 64px; margin-bottom: 20px;">🎨</div>
                <h4 style="color: #23282d; font-size: 20px; margin-bottom: 15px;">Templates Illimités &amp; Personnalisés</h4>
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
                <div class="price" style="font-size: 36px; font-weight: bold; margin-bottom: 10px;">79.99€ <span style="font-size: 16px; font-weight: normal;">à vie</span></div>
                <p style="margin: 10px 0 20px 0; opacity: 0.9;">✨ Accès à vie ou abonnement flexible — sans engagement !</p>
                <a href="https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/" class="button button-primary" target="_blank" style="background: white; color: #667eea; border: none; padding: 12px 30px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; border-radius: 6px;">
                    <span class="dashicons dashicons-cart" style="margin-right: 5px;"></span>
                    Commander Maintenant
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'upgrade pour galerie de modèles -->
<div id="upgrade-modal-gallery" class="pdfb-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 99999; justify-content: center; align-items: center;">
    <div class="pdfb-modal-content" style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div class="pdfb-modal-header" style="padding: 20px 30px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #23282d; font-size: 24px;">🎨 Modèles Prédéfinis Premium</h3>
            <button class="pdfb-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
        </div>
        <div class="pdfb-modal-body" style="padding: 30px;">
            <div class="upgrade-feature" style="text-align: center; margin-bottom: 30px;">
                <div class="pdfb-feature-icon" style="font-size: 64px; margin-bottom: 20px;">🖼️</div>
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
                <div class="price" style="font-size: 36px; font-weight: bold; margin-bottom: 10px;">79.99€ <span style="font-size: 16px; font-weight: normal;">à vie</span></div>
                <p style="margin: 10px 0 20px 0; opacity: 0.9;">✨ Accès à vie ou abonnement flexible — sans engagement !</p>
                <a href="https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/" class="button button-primary" target="_blank" style="background: white; color: #667eea; border: none; padding: 12px 30px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; border-radius: 6px;">
                    <span class="dashicons dashicons-cart" style="margin-right: 5px;"></span>
                    Commander Maintenant
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction globale partagée — afficher modal upgrade premium
if (typeof window.showUpgradeModal === 'undefined') {
    window.showUpgradeModal = function(reason) {
        var modal = document.getElementById('upgrade-modal-' + reason);
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    // Fermer modal au clic sur overlay ou bouton close
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('pdfb-modal-overlay') ||
            e.target.classList.contains('pdfb-modal-close')) {
            var modal = e.target.closest('.pdfb-modal-overlay');
            if (modal) {
                modal.style.display = 'none';
            }
        }
    });
}
</script>


