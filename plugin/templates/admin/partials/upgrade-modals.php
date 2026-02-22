<?php
/**
 * Partial partagé — Modaux d'upgrade premium
 * Inclus dans : templates-page.php ET admin-editor.php
 */
if (!defined('ABSPATH')) exit;
?>

<!-- Modal d'upgrade pour création de templates -->
<div id="upgrade-modal-template" class="pdfb-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 99999; justify-content: center; align-items: center;">
    <div class="pdfb-modal-content" style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div class="pdfb-modal-header" style="padding: 20px 30px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #23282d; font-size: 20px;">🚀 Débloquer les Templates</h3>
            <button class="pdfb-modal-close" onclick="closeUpgradeModal('upgrade-modal-template')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
        </div>
        <div class="pdfb-modal-body" style="padding: 30px;">
            <div class="upgrade-feature" style="text-align: center; margin-bottom: 30px;">
                <div class="pdfb-feature-icon" style="font-size: 64px; margin-bottom: 20px;">🎨</div>
                <h4 style="color: #23282d; font-size: 20px; margin-bottom: 15px;">Templates Illimités &amp; Personnalisés</h4>
                <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                    Créez autant de templates PDF que vous voulez avec votre propre design et branding.
                </p>
                <ul style="text-align: left; background: #f8f9fa; padding: 20px; border-radius: 8px; list-style: none; margin: 0;">
                    <li style="margin: 8px 0; color: #23282d;">📄 <strong>Templates illimités</strong> — créez sans aucune limite</li>
                    <li style="margin: 8px 0; color: #23282d;">🖼️ <strong>Génération PDF, PNG & JPG prioritaire</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">📤 <strong>Export PDF, PNG & JPG</strong> en haute qualité</li>
                    <li style="margin: 8px 0; color: #23282d;">🎯 <strong>Haute résolution</strong> 300 & 600 DPI</li>
                    <li style="margin: 8px 0; color: #23282d;">🎨 <strong>Couleurs & fonds personnalisés</strong> du canvas</li>
                    <li style="margin: 8px 0; color: #23282d;">📐 <strong>Grille, guides & accrochage</strong> magnétique</li>
                    <li style="margin: 8px 0; color: #23282d;">🔄 <strong>Mises à jour gratuites</strong> à vie</li>
                    <li style="margin: 8px 0; color: #23282d;">💬 <strong>Support prioritaire</strong> avec réponse garantie</li>
                </ul>
            </div>
            <div class="pricing" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 8px; color: white;">
                <div class="price" style="font-size: 36px; font-weight: bold; margin-bottom: 10px;">69.99€ <span style="font-size: 16px; font-weight: normal;">à vie</span></div>
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
    <div class="pdfb-modal-content" style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div class="pdfb-modal-header" style="padding: 20px 30px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #23282d; font-size: 24px;">🎨 Modèles Prédéfinis Premium</h3>
            <button class="pdfb-modal-close" onclick="closeUpgradeModal('upgrade-modal-gallery')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
        </div>
        <div class="pdfb-modal-body" style="padding: 30px;">
            <div class="upgrade-feature" style="text-align: center; margin-bottom: 30px;">
                <div class="pdfb-feature-icon" style="font-size: 64px; margin-bottom: 20px;">🖼️</div>
                <h4 style="color: #23282d; font-size: 20px; margin-bottom: 15px;">Galerie de Modèles Professionnels</h4>
                <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                    Accédez à notre collection de templates professionnels prédéfinis pour factures, devis et plus encore.
                </p>
                <ul style="text-align: left; background: #f8f9fa; padding: 20px; border-radius: 8px; list-style: none; margin: 0;">
                    <li style="margin: 8px 0; color: #23282d;">📄 <strong>Templates illimités</strong> — créez sans aucune limite</li>
                    <li style="margin: 8px 0; color: #23282d;">🖼️ <strong>Génération PDF, PNG & JPG prioritaire</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">📤 <strong>Export PDF, PNG & JPG</strong> en haute qualité</li>
                    <li style="margin: 8px 0; color: #23282d;">🎯 <strong>Haute résolution</strong> 300 & 600 DPI</li>
                    <li style="margin: 8px 0; color: #23282d;">🎨 <strong>Couleurs & fonds personnalisés</strong> du canvas</li>
                    <li style="margin: 8px 0; color: #23282d;">📐 <strong>Grille, guides & accrochage</strong> magnétique</li>
                    <li style="margin: 8px 0; color: #23282d;">🔄 <strong>Mises à jour gratuites</strong> à vie</li>
                    <li style="margin: 8px 0; color: #23282d;">💬 <strong>Support prioritaire</strong> avec réponse garantie</li>
                </ul>
            </div>
            <div class="pricing" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 8px; color: white;">
                <div class="price" style="font-size: 36px; font-weight: bold; margin-bottom: 10px;">69.99€ <span style="font-size: 16px; font-weight: normal;">à vie</span></div>
                <p style="margin: 10px 0 20px 0; opacity: 0.9;">✨ Accès à vie ou abonnement flexible — sans engagement !</p>
                <a href="https://hub.threeaxe.fr/index.php/downloads/pdf-builder-pro/" class="button button-primary" target="_blank" style="background: white; color: #667eea; border: none; padding: 12px 30px; font-size: 16px; font-weight: bold; text-decoration: none; display: inline-block; border-radius: 6px;">
                    <span class="dashicons dashicons-cart" style="margin-right: 5px;"></span>
                    Commander Maintenant
                </a>
            </div>
        </div>
    </div>
</div>

<div id="upgrade-modal-license_tab" class="pdfb-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 99999; justify-content: center; align-items: center;">
    <div class="pdfb-modal-content" style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div class="pdfb-modal-header" style="padding: 20px 30px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #23282d; font-size: 24px;">💎 Licence Premium</h3>
            <button class="pdfb-modal-close" onclick="closeUpgradeModal('upgrade-modal-license_tab')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6c757d;">&times;</button>
        </div>
        <div class="pdfb-modal-body" style="padding: 30px;">
            <div class="upgrade-feature" style="text-align: center; margin-bottom: 30px;">
                <div class="pdfb-feature-icon" style="font-size: 64px; margin-bottom: 20px;">💎</div>
                <h4 style="color: #23282d; font-size: 20px; margin-bottom: 15px;">Licence PDF Builder Pro Premium</h4>
                <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                    Débloquez toutes les fonctionnalités premium de PDF Builder Pro pour créer des documents professionnels illimités.
                </p>
                <ul style="text-align: left; background: #f8f9fa; padding: 20px; border-radius: 8px; list-style: none; margin: 0;">
                    <li style="margin: 8px 0; color: #23282d;">📄 <strong>Templates illimités</strong> — créez sans aucune limite</li>
                    <li style="margin: 8px 0; color: #23282d;">🖼️ <strong>Génération PDF, PNG & JPG prioritaire</strong></li>
                    <li style="margin: 8px 0; color: #23282d;">📤 <strong>Export PDF, PNG & JPG</strong> en haute qualité</li>
                    <li style="margin: 8px 0; color: #23282d;">🎯 <strong>Haute résolution</strong> 300 & 600 DPI</li>
                    <li style="margin: 8px 0; color: #23282d;">🎨 <strong>Couleurs & fonds personnalisés</strong> du canvas</li>
                    <li style="margin: 8px 0; color: #23282d;">📐 <strong>Grille, guides & accrochage</strong> magnétique</li>
                    <li style="margin: 8px 0; color: #23282d;">🔄 <strong>Mises à jour gratuites</strong> à vie</li>
                    <li style="margin: 8px 0; color: #23282d;">💬 <strong>Support prioritaire</strong> avec réponse garantie</li>
                </ul>
            </div>
            <div class="pricing" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 8px; color: white;">
                <div class="price" style="font-size: 36px; font-weight: bold; margin-bottom: 10px;">69.99€ <span style="font-size: 16px; font-weight: normal;">à vie</span></div>
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
// Fonction pour fermer le modal upgrade premium
window.closeUpgradeModal = function(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
};

// Fonction globale partagée — afficher modal upgrade premium
if (typeof window.showUpgradeModal === 'undefined') {
    window.showUpgradeModal = function(reason) {
        var modal = document.getElementById('upgrade-modal-' + reason);
        if (modal) {
            modal.style.display = 'flex';
        }
    };
}
</script>


