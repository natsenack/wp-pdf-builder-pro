/**
 * PDF Builder Pro - Deactivation Feedback Modal
 * Affiche un modal de feedback lors de la désactivation du plugin
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const PLUGIN_SLUG = 'pdf-builder-pro';
        const $body = $('body');

        // Créer le modal HTML
        const modalHTML = `
            <div id="pdf-builder-deactivation-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Nous serions tristes de vous voir partir 😢</h2>
                        <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Aidez-nous à améliorer PDF Builder Pro</p>
                    </div>
                    <div class="modal-body">
                        <p style="margin: 0 0 15px 0; font-size: 14px;">Dites-nous pourquoi vous désactivez le plugin :</p>
                        
                        <div class="feedback-group">
                            <label class="feedback-option">
                                <input type="radio" name="deactivation_reason" value="dont_need">
                                <label>Je n'en ai plus besoin</label>
                            </label>
                            
                            <label class="feedback-option">
                                <input type="radio" name="deactivation_reason" value="not_working">
                                <label>Le plugin ne fonctionne pas correctement</label>
                            </label>
                            
                            <label class="feedback-option">
                                <input type="radio" name="deactivation_reason" value="slow_performance">
                                <label>Le plugin ralentit mon site</label>
                            </label>
                            
                            <label class="feedback-option">
                                <input type="radio" name="deactivation_reason" value="confusing">
                                <label>Le plugin est difficile à utiliser</label>
                            </label>
                            
                            <label class="feedback-option">
                                <input type="radio" name="deactivation_reason" value="expensive">
                                <label>C'est trop cher pour les fonctionnalités</label>
                            </label>
                            
                            <label class="feedback-option">
                                <input type="radio" name="deactivation_reason" value="found_alternative">
                                <label>J'ai trouvé une meilleure alternative</label>
                            </label>
                            
                            <label class="feedback-option">
                                <input type="radio" name="deactivation_reason" value="temporary">
                                <label>Désactivation temporaire</label>
                            </label>
                            
                            <label class="feedback-option">
                                <input type="radio" name="deactivation_reason" value="autre">
                                <label>Autre raison</label>
                            </label>
                        </div>
                        
                        <div class="email-field">
                            <label for="pdf_builder_feedback_email">Votre email (pour vous recontacter) :</label>
                            <input type="email" id="pdf_builder_feedback_email" placeholder="votre@email.com">
                        </div>
                        
                        <textarea id="pdf_builder_feedback_message" placeholder="Parlez-nous de votre expérience... (optionnel)"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="skip-button" id="pdf_builder_skip_feedback">Passer et désactiver</button>
                        <button class="deactivate-button" id="pdf_builder_send_feedback">Envoyer et désactiver</button>
                    </div>
                </div>
            </div>
        `;

        // Ajouter le modal au DOM
        $body.append(modalHTML);

        const $modal = $('#pdf-builder-deactivation-modal');
        const $skipBtn = $('#pdf_builder_skip_feedback');
        const $sendBtn = $('#pdf_builder_send_feedback');
        const $textarea = $('#pdf_builder_feedback_message');
        const $emailField = $('.email-field');
        let pluginDeactivateUrl = null;

        // Observer les changements de sélection radio
        $('input[name="deactivation_reason"]').on('change', function() {
            const value = $(this).val();
            
            // Afficher la zone de texte et d'email si une raison est sélectionnée
            if (value) {
                $textarea.addClass('show');
                $emailField.addClass('show');
            }
        });

        // Intercepter le clic sur le lien de désactivation
        $(document).on('click', 'a[href*="action=deactivate"][href*="' + PLUGIN_SLUG + '"]', function(e) {
            e.preventDefault();
            pluginDeactivateUrl = this.href;
            
            // Afficher le modal
            $modal.addClass('show');
            
            // Focus sur la première radio
            $('input[name="deactivation_reason"]').first().focus();
        });

        // Bouton "Passer et désactiver" - très discret
        $skipBtn.on('click', function() {
            // Désactiver directement sans envoyer de feedback
            if (pluginDeactivateUrl) {
                window.location.href = pluginDeactivateUrl;
            }
        });

        // Bouton "Envoyer et désactiver"
        $sendBtn.on('click', function() {
            const reason = $('input[name="deactivation_reason"]:checked').val();
            const message = $textarea.val();
            const email = $('#pdf_builder_feedback_email').val();

            // Désactiver le bouton pendant l'envoi
            $sendBtn.prop('disabled', true).addClass('loading');

            // Envoyer le feedback via AJAX
            $.ajax({
                url: pdfBuilderDeactivation.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_send_deactivation_feedback',
                    nonce: pdfBuilderDeactivation.nonce,
                    reason: reason || 'autre',
                    message: message,
                    email: email,
                },
                complete: function() {
                    // Toujours désactiver le plugin, même si l'email a échoué
                    if (pluginDeactivateUrl) {
                        window.location.href = pluginDeactivateUrl;
                    }
                }
            });
        });
    });
})(jQuery);
