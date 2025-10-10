/**
 * PDF Builder Pro - Freemium JavaScript
 * Gestion des interactions freemium côté client
 */

(function($) {
    'use strict';

    // Vérifier si nous sommes sur une page PDF Builder
    if (typeof pdf_builder_freemium === 'undefined') {
        return;
    }

    const FreemiumManager = {

        init: function() {
            this.bindEvents();
            this.checkRestrictions();
            this.showUsageWarnings();
        },

        bindEvents: function() {
            // Gestion des clics sur les éléments premium
            $(document).on('click', '.premium-locked', this.handlePremiumClick);

            // Gestion des badges premium
            $(document).on('mouseenter', '.premium-badge', this.showPremiumTooltip);
            $(document).on('mouseleave', '.premium-badge', this.hidePremiumTooltip);

            // Gestion des modals d'upgrade
            $(document).on('click', '.upgrade-modal-trigger', this.showUpgradeModal);
            $(document).on('click', '.upgrade-modal-close', this.hideUpgradeModal);

            // Gestion des dismiss notices
            $(document).on('click', '.pdf-builder-upgrade-notice .notice-dismiss', this.dismissUpgradeNotice);
        },

        checkRestrictions: function() {
            // Désactiver les fonctionnalités premium dans l'interface
            if (!pdf_builder_freemium.is_premium) {
                this.disablePremiumFeatures();
            }
        },

        disablePremiumFeatures: function() {
            // Désactiver les boutons premium
            $('.premium-feature').prop('disabled', true).addClass('disabled');

            // Ajouter des indicateurs visuels
            $('.premium-feature').each(function() {
                const $button = $(this);
                if (!$button.find('.premium-indicator').length) {
                    $button.append('<span class="premium-indicator">🔒</span>');
                }
            });

            // Désactiver les éléments de formulaire premium
            $('.premium-only').prop('disabled', true).addClass('premium-disabled');
        },

        handlePremiumClick: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $element = $(this);
            const featureName = $element.data('feature') || $element.attr('data-element') || 'fonctionnalité';

            FreemiumManager.showUpgradeModalForFeature(featureName);
        },

        showPremiumTooltip: function() {
            const $badge = $(this);
            const $button = $badge.closest('.premium-locked, .premium-feature');

            if (!$button.find('.premium-tooltip').length) {
                const featureName = $button.data('feature') || $button.data('element') || 'cette fonctionnalité';
                const tooltip = `
                    <div class="premium-tooltip">
                        <strong>${featureName}</strong> est réservé aux utilisateurs Premium
                        <br>
                        <a href="#" class="upgrade-link">Passer à Premium</a>
                    </div>
                `;
                $button.append(tooltip);
            }

            $button.find('.premium-tooltip').fadeIn(200);
        },

        hidePremiumTooltip: function() {
            $('.premium-tooltip').fadeOut(200);
        },

        showUpgradeModalForFeature: function(featureName) {
            const modal = `
                <div class="pdf-builder-modal-overlay" id="upgrade-modal">
                    <div class="pdf-builder-modal">
                        <div class="modal-header">
                            <h2>Débloquer ${featureName}</h2>
                            <button class="modal-close upgrade-modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="upgrade-benefits">
                                <h3>🔥 Avantages Premium</h3>
                                <ul>
                                    <li>✅ Génération PDF illimitée</li>
                                    <li>✅ Templates avancés et personnalisables</li>
                                    <li>✅ Éléments premium (codes-barres, QR codes)</li>
                                    <li>✅ API développeur complète</li>
                                    <li>✅ Support prioritaire 24/7</li>
                                    <li>✅ White-label et rebranding</li>
                                </ul>
                            </div>
                            <div class="pricing-highlight">
                                <div class="price">€49<span class="period">/an</span></div>
                                <div class="price-note">Paiement unique, pas d'abonnement</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="button upgrade-modal-close">Plus tard</button>
                            <a href="https://pdfbuilderpro.com/pricing" class="button button-primary" target="_blank">
                                Passer à Premium
                            </a>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modal);
            $('#upgrade-modal').fadeIn(300);
        },

        showUpgradeModal: function(e) {
            e.preventDefault();
            const featureName = $(this).data('feature') || 'cette fonctionnalité';
            FreemiumManager.showUpgradeModalForFeature(featureName);
        },

        hideUpgradeModal: function() {
            $('#upgrade-modal').fadeOut(300, function() {
                $(this).remove();
            });
        },

        showUsageWarnings: function() {
            // Afficher des avertissements quand l'utilisateur approche des limites
            this.checkPdfGenerationLimit();
        },

        checkPdfGenerationLimit: function() {
            // Cette fonction pourrait faire un appel AJAX pour vérifier l'usage actuel
            // Pour l'exemple, on simule
            $.ajax({
                url: pdf_builder_freemium.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_check_usage',
                    nonce: pdf_builder_freemium.nonce,
                    feature: 'pdf_generation'
                },
                success: function(response) {
                    if (response.success && response.data.near_limit) {
                        FreemiumManager.showLimitWarning(response.data);
                    }
                }
            });
        },

        showLimitWarning: function(data) {
            const warning = `
                <div class="notice notice-warning pdf-limit-warning">
                    <p>
                        <strong>Attention :</strong> Vous avez utilisé ${data.used}/${data.limit} PDFs ce mois-ci.
                        <a href="${pdf_builder_freemium.settings_url}&tab=license">Passer à Premium</a> pour génération illimitée.
                    </p>
                </div>
            `;

            if (!$('.pdf-limit-warning').length) {
                $('.wp-header-end').after(warning);
            }
        },

        dismissUpgradeNotice: function() {
            // Marquer la notice comme masquée
            $.ajax({
                url: pdf_builder_freemium.ajax_url,
                type: 'POST',
                data: {
                    action: 'pdf_builder_dismiss_upgrade_notice',
                    nonce: pdf_builder_freemium.nonce
                }
            });
        }
    };

    // Initialisation quand le DOM est prêt
    $(document).ready(function() {
        FreemiumManager.init();
    });

    // Gestionnaire pour les clics sur les éléments canvas
    $(document).on('click', '.canvas-element', function() {
        const elementType = $(this).data('type');

        // Vérifier si c'est un élément premium
        const premiumElements = ['barcode', 'qrcode', 'chart', 'signature', 'table'];

        if (premiumElements.includes(elementType) && !pdf_builder_freemium.is_premium) {
            FreemiumManager.showUpgradeModalForFeature(`l'élément ${elementType}`);
            return false;
        }
    });

    // Gestionnaire pour les actions bulk
    $(document).on('click', '.bulk-action', function() {
        if (!pdf_builder_freemium.is_premium) {
            FreemiumManager.showUpgradeModalForFeature('les actions groupées');
            return false;
        }
    });

    // Gestionnaire pour l'export avancé
    $(document).on('click', '.export-advanced', function() {
        if (!pdf_builder_freemium.is_premium) {
            const format = $(this).data('format') || 'ce format';
            FreemiumManager.showUpgradeModalForFeature(`l'export ${format}`);
            return false;
        }
    });

})(jQuery);

// =============================================================================
// CSS pour les éléments freemium
// =============================================================================

/*
.pdf-builder-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pdf-builder-modal {
    background: white;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e5e5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: #23282d;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 20px;
}

.upgrade-benefits ul {
    list-style: none;
    padding: 0;
}

.upgrade-benefits li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.pricing-highlight {
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.price {
    font-size: 36px;
    font-weight: bold;
}

.price .period {
    font-size: 16px;
    opacity: 0.8;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e5e5e5;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.premium-locked {
    position: relative;
    opacity: 0.6;
    cursor: not-allowed;
}

.premium-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: bold;
    z-index: 1;
}

.premium-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
    display: none;
}

.premium-tooltip .upgrade-link {
    color: #667eea;
    text-decoration: none;
    font-weight: bold;
}

.premium-indicator {
    margin-left: 8px;
    color: #ffb900;
}

.premium-disabled {
    background: #f8f9fa !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
}

.usage-counter {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 10px;
    margin: 10px 0;
}

.usage-bar {
    background: #e9ecef;
    border-radius: 4px;
    height: 20px;
    overflow: hidden;
}

.usage-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745 0%, #ffc107 70%, #dc3545 90%);
    transition: width 0.3s ease;
}
*/