/**
 * PDF Builder Pro - Deactivation Feedback Modal
 * Affiche un modal de feedback lors de la désactivation du plugin
 */

// Fonctions globales appelées directement depuis le HTML (plus fiable)
window.pdfBuilderSkipFeedback = function() {
    console.log('[PDF Builder] Skip clicked, URL:', window._pdfBuilderDeactivateUrl);
    if (window._pdfBuilderDeactivateUrl) {
        window.location.href = window._pdfBuilderDeactivateUrl;
    }
};

window.pdfBuilderSendFeedback = function() {
    var reason = document.querySelector('input[name="deactivation_reason"]:checked');
    var message = document.getElementById('pdf_builder_feedback_message');
    var email = document.getElementById('pdf_builder_feedback_email');
    var btn = document.getElementById('pdf_builder_send_feedback');

    console.log('[PDF Builder] Send feedback clicked, reason:', reason ? reason.value : 'none');

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Envoi en cours...';
    }

    jQuery.ajax({
        url: pdfBuilderDeactivation.ajaxUrl,
        type: 'POST',
        data: {
            action: 'pdf_builder_send_deactivation_feedback',
            nonce: pdfBuilderDeactivation.nonce,
            reason: reason ? reason.value : 'autre',
            message: message ? message.value : '',
            email: email ? email.value : '',
        },
        success: function(response) {
            console.log('[PDF Builder] Feedback sent successfully:', response);
        },
        error: function(xhr, status, error) {
            console.error('[PDF Builder] Feedback error:', error);
        },
        complete: function() {
            console.log('[PDF Builder] Redirecting to:', window._pdfBuilderDeactivateUrl);
            if (window._pdfBuilderDeactivateUrl) {
                window.location.href = window._pdfBuilderDeactivateUrl;
            }
        }
    });
};

(function($) {
    'use strict';

    $(document).ready(function() {
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
                margin-top: 10px !important;
            }
            #pdf-builder-deactivation-modal textarea.show { display: block !important; }
            #pdf-builder-deactivation-modal .email-field { margin-bottom: 15px !important; display: none !important; }
            #pdf-builder-deactivation-modal .email-field.show { display: block !important; }
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
                color: #666 !important;
                cursor: pointer !important;
                font-size: 13px !important;
                text-decoration: underline !important;
            }
            #pdf-builder-deactivation-modal .deactivate-button {
                padding: 10px 20px !important;
                border: none !important;
                background: #dc3545 !important;
                color: white !important;
                border-radius: 5px !important;
                cursor: pointer !important;
                font-weight: 500 !important;
                font-size: 14px !important;
            }
            #pdf-builder-deactivation-modal .deactivate-button:disabled {
                opacity: 0.6 !important;
                cursor: not-allowed !important;
            }
        `;
        
        $('<style id="pdf-builder-modal-styles">').text(modalStyles).appendTo('head');
        console.log('[PDF Builder] Styles injectés');

        // Créer le modal HTML avec onclick natifs
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
                            <div class="feedback-option"><input type="radio" id="r1" name="deactivation_reason" value="dont_need"><label for="r1">Je n'en ai plus besoin</label></div>
                            <div class="feedback-option"><input type="radio" id="r2" name="deactivation_reason" value="not_working"><label for="r2">Le plugin ne fonctionne pas correctement</label></div>
                            <div class="feedback-option"><input type="radio" id="r3" name="deactivation_reason" value="slow_performance"><label for="r3">Le plugin ralentit mon site</label></div>
                            <div class="feedback-option"><input type="radio" id="r4" name="deactivation_reason" value="confusing"><label for="r4">Le plugin est difficile à utiliser</label></div>
                            <div class="feedback-option"><input type="radio" id="r5" name="deactivation_reason" value="expensive"><label for="r5">C'est trop cher pour les fonctionnalités</label></div>
                            <div class="feedback-option"><input type="radio" id="r6" name="deactivation_reason" value="found_alternative"><label for="r6">J'ai trouvé une meilleure alternative</label></div>
                            <div class="feedback-option"><input type="radio" id="r7" name="deactivation_reason" value="temporary"><label for="r7">Désactivation temporaire</label></div>
                            <div class="feedback-option"><input type="radio" id="r8" name="deactivation_reason" value="autre"><label for="r8">Autre raison</label></div>
                        </div>
                        <div class="email-field" id="pdf_builder_email_field">
                            <label for="pdf_builder_feedback_email">Votre email (pour vous recontacter) :</label>
                            <input type="email" id="pdf_builder_feedback_email" placeholder="votre@email.com">
                        </div>
                        <textarea id="pdf_builder_feedback_message" placeholder="Parlez-nous de votre expérience... (optionnel)"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="skip-button" onclick="pdfBuilderSkipFeedback()">Passer et désactiver</button>
                        <button type="button" id="pdf_builder_send_feedback" class="deactivate-button" onclick="pdfBuilderSendFeedback()">Envoyer et désactiver</button>
                    </div>
                </div>
            </div>
        `;

        $body.append(modalHTML);
        console.log('[PDF Builder] Modal HTML ajouté au DOM');

        const $modal = $('#pdf-builder-deactivation-modal');

        // Show/hide textarea et email quand une raison est sélectionnée
        $body.on('change', 'input[name="deactivation_reason"]', function() {
            $('#pdf_builder_feedback_message').addClass('show');
            $('#pdf_builder_email_field').addClass('show');
        });

        // Intercepter le clic de désactivation (sélecteur primaire)
        $(document).on('click', 'a[href*="action=deactivate"][href*="pdf-builder"]', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            window._pdfBuilderDeactivateUrl = this.href;
            console.log('[PDF Builder] Désactivation interceptée:', window._pdfBuilderDeactivateUrl);
            $modal.addClass('show');
        });
    });
})(jQuery);