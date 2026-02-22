/**
 * PDF Builder Pro - Deactivation Feedback Modal
 * Affiche un modal de feedback lors de la désactivation du plugin
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const PLUGIN_SLUG = 'pdf-builder-pro';
        const $body = $('body');

        // Injecter les styles du modal directement
        const modalStyles = `
            #pdf-builder-deactivation-modal {
                display: none !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                z-index: 999999 !important;
                width: 100% !important;
                height: 100% !important;
                background: rgba(0, 0, 0, 0.6) !important;
                justify-content: center !important;
                align-items: center !important;
            }
            #pdf-builder-deactivation-modal.show {
                display: flex !important;
            }
            #pdf-builder-deactivation-modal .modal-content {
                background: white !important;
                border-radius: 8px !important;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
                max-width: 500px !important;
                width: 90% !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            #pdf-builder-deactivation-modal .modal-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                color: white !important;
                padding: 20px !important;
                margin: 0 !important;
            }
            #pdf-builder-deactivation-modal .modal-header h2 {
                margin: 0 !important;
                font-size: 18px !important;
            }
            #pdf-builder-deactivation-modal .modal-body {
                padding: 20px !important;
            }
            #pdf-builder-deactivation-modal .feedback-group {
                margin-bottom: 15px !important;
            }
            #pdf-builder-deactivation-modal .feedback-option {
                display: flex !important;
                align-items: flex-start !important;
                padding: 10px !important;
                border: 1px solid #e0e0e0 !important;
                border-radius: 5px !important;
                cursor: pointer !important;
                margin-bottom: 8px !important;
                transition: all 0.2s !important;
            }
            #pdf-builder-deactivation-modal .feedback-option:hover {
                background: #f9f9f9 !important;
                border-color: #667eea !important;
            }
            #pdf-builder-deactivation-modal .feedback-option input[type="radio"] {
                margin-top: 2px !important;
                margin-right: 10px !important;
                cursor: pointer !important;
            }
            #pdf-builder-deactivation-modal .feedback-option label {
                cursor: pointer !important;
                flex: 1 !important;
            }
            #pdf-builder-deactivation-modal textarea {
                width: 100% !important;
                min-height: 80px !important;
                padding: 10px !important;
                border: 1px solid #e0e0e0 !important;
                border-radius: 5px !important;
                font-family: inherit !important;
                display: none !important;
                box-sizing: border-box !important;
            }
            #pdf-builder-deactivation-modal textarea.show {
                display: block !important;
            }
            #pdf-builder-deactivation-modal .email-field {
                margin-bottom: 15px !important;
                display: none !important;
            }
            #pdf-builder-deactivation-modal .email-field.show {
                display: block !important;
            }
            #pdf-builder-deactivation-modal .email-field input {
                width: 100% !important;
                padding: 8px !important;
                border: 1px solid #e0e0e0 !important;
                border-radius: 5px !important;
                box-sizing: border-box !important;
            }
            #pdf-builder-deactivation-modal .modal-footer {
                display: flex !important;
                gap: 10px !important;
                padding: 20px !important;
                background: #f5f5f5 !important;
                justify-content: space-between !important;
            }
            #pdf-builder-deactivation-modal .skip-button {
                padding: 8px 12px !important;
                border: none !important;
                background: transparent !important;
                color: #999 !important;
                cursor: pointer !important;
                font-size: 12px !important;
                opacity: 0.6 !important;
                transition: opacity 0.2s !important;
                pointer-events: auto !important;
            }
            #pdf-builder-deactivation-modal .skip-button:hover {
                opacity: 0.8 !important;
            }
            #pdf-builder-deactivation-modal .deactivate-button {
                padding: 10px 20px !important;
                border: none !important;
                background: #dc3545 !important;
                color: white !important;
                border-radius: 5px !important;
                cursor: pointer !important;
                font-weight: 500 !important;
                transition: background 0.2s !important;
                pointer-events: auto !important;
            }
            #pdf-builder-deactivation-modal .deactivate-button:hover {
                background: #c82333 !important;
            }
            #pdf-builder-deactivation-modal .deactivate-button.loading {
                opacity: 0.6 !important;
                cursor: not-allowed !important;
            }
        `;
        
        $('<style>').text(modalStyles).appendTo('head');
        console.log('[PDF Builder] Styles injectés');

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
                            <div class="feedback-option">
                                <input type="radio" id="reason_dont_need" name="deactivation_reason" value="dont_need">
                                <label for="reason_dont_need">Je n'en ai plus besoin</label>
                            </div>
                            
                            <div class="feedback-option">
                                <input type="radio" id="reason_not_working" name="deactivation_reason" value="not_working">
                                <label for="reason_not_working">Le plugin ne fonctionne pas correctement</label>
                            </div>
                            
                            <div class="feedback-option">
                                <input type="radio" id="reason_slow" name="deactivation_reason" value="slow_performance">
                                <label for="reason_slow">Le plugin ralentit mon site</label>
                            </div>
                            
                            <div class="feedback-option">
                                <input type="radio" id="reason_confusing" name="deactivation_reason" value="confusing">
                                <label for="reason_confusing">Le plugin est difficile à utiliser</label>
                            </div>
                            
                            <div class="feedback-option">
                                <input type="radio" id="reason_expensive" name="deactivation_reason" value="expensive">
                                <label for="reason_expensive">C'est trop cher pour les fonctionnalités</label>
                            </div>
                            
                            <div class="feedback-option">
                                <input type="radio" id="reason_alternative" name="deactivation_reason" value="found_alternative">
                                <label for="reason_alternative">J'ai trouvé une meilleure alternative</label>
                            </div>
                            
                            <div class="feedback-option">
                                <input type="radio" id="reason_temporary" name="deactivation_reason" value="temporary">
                                <label for="reason_temporary">Désactivation temporaire</label>
                            </div>
                            
                            <div class="feedback-option">
                                <input type="radio" id="reason_other" name="deactivation_reason" value="autre">
                                <label for="reason_other">Autre raison</label>
                            </div>
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
        console.log('[PDF Builder] Modal HTML ajouté au DOM');

        const $modal = $('#pdf-builder-deactivation-modal');
        const $skipBtn = $('#pdf_builder_skip_feedback');
        const $sendBtn = $('#pdf_builder_send_feedback');
        const $textarea = $('#pdf_builder_feedback_message');
        const $emailField = $('.email-field');
        let pluginDeactivateUrl = null;

        console.log('[PDF Builder] Elements trouvés:', {
            modal: $modal.length > 0,
            skipBtn: $skipBtn.length > 0,
            sendBtn: $sendBtn.length > 0,
            textarea: $textarea.length > 0,
            emailField: $emailField.length > 0
        });

        // Observer les changements de sélection radio
        $(document).on('change', 'input[name="deactivation_reason"]', function() {
            const value = $(this).val();
            
            // Afficher la zone de texte et d'email si une raison est sélectionnée
            if (value) {
                $textarea.addClass('show');
                $emailField.addClass('show');
            }
        });

        // Intercepter le clic sur le lien de désactivation du plugin PDF Builder Pro
        $(document).on('click', 'a.submitdelete[href*="pdf-builder-pro"]', function(e) {
            // Vérifier que c'est bien un lien de désactivation
            if (!$(this).attr('href').includes('action=deactivate')) {
                return;
            }
            
            e.preventDefault();
            pluginDeactivateUrl = this.href;
            console.log('[PDF Builder] Intercepté désactivation:', pluginDeactivateUrl);
            console.log('[PDF Builder] Ajout de la classe show au modal');
            $modal.addClass('show');
            console.log('[PDF Builder] Modal affichage requis, CSS appliqué');
            $('input[name="deactivation_reason"]').first().focus();
        });

        // Fallback: si le sélecteur ci-dessus ne fonctionne pas, chercher tous les liens de désactivation
        if (!pluginDeactivateUrl) {
            $(document).on('click', 'a[href*="action=deactivate"][href*="pdf-builder"]', function(e) {
                e.preventDefault();
                pluginDeactivateUrl = this.href;
                console.log('[PDF Builder] Intercepté (fallback):', pluginDeactivateUrl);
                console.log('[PDF Builder] Ajout de la classe show au modal (fallback)');
                $modal.addClass('show');
                console.log('[PDF Builder] Modal affichage requis, CSS appliqué (fallback)');
                $('input[name="deactivation_reason"]').first().focus();
            });
        }

        // Bouton "Passer et désactiver" - très discret (délégation d'événements)
        $(document).on('click', '#pdf_builder_skip_feedback', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('[PDF Builder] Skip clicked, redirecting to:', pluginDeactivateUrl);
            // Désactiver directement sans envoyer de feedback
            if (pluginDeactivateUrl) {
                window.location.href = pluginDeactivateUrl;
            }
        });

        // Bouton "Envoyer et désactiver" (délégation d'événements)
        $(document).on('click', '#pdf_builder_send_feedback', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const reason = $('input[name="deactivation_reason"]:checked').val();
            const message = $textarea.val();
            const email = $('#pdf_builder_feedback_email').val();

            console.log('[PDF Builder] Send feedback:', {reason, email, messageLength: message.length});

            // Désactiver le bouton pendant l'envoi
            $(this).prop('disabled', true).addClass('loading');

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
                success: function(response) {
                    console.log('[PDF Builder] Feedback sent successfully:', response);
                },
                error: function(xhr, status, error) {
                    console.error('[PDF Builder] Feedback error:', error);
                },
                complete: function() {
                    // Toujours désactiver le plugin, même si l'email a échoué
                    console.log('[PDF Builder] Redirecting to deactivation URL:', pluginDeactivateUrl);
                    if (pluginDeactivateUrl) {
                        window.location.href = pluginDeactivateUrl;
                    }
                }
            });
        });
    });
})(jQuery);
