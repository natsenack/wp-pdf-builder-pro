/**
 * PDF Builder Pro - Scripts d'administration WooCommerce
 *
 * Gère les interactions JavaScript pour l'intégration WooCommerce
 */

(function($) {
    'use strict';

    // Attendre que le document soit prêt
    $(document).ready(function() {

        // Gestionnaire pour les boutons de génération PDF dans les commandes
        $('.pdf-generate-btn').on('click', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const orderId = $btn.data('order-id');
            const templateType = $btn.data('template-type');
            const originalText = $btn.html();

            // Désactiver le bouton et changer le texte
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-pdf"></span> ' + pdfBuilderWooCommerce.generating_text);

            // Faire la requête AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_generate_order_pdf',
                    order_id: orderId,
                    template_type: templateType,
                    nonce: pdfBuilderWooCommerce.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Ouvrir le PDF dans un nouvel onglet
                        window.open(response.data.pdf_url, '_blank');

                        // Afficher un message de succès
                        showNotice(pdfBuilderWooCommerce.success_message, 'success');
                    } else {
                        // Afficher le message d'erreur
                        showNotice(response.data.message || pdfBuilderWooCommerce.error_message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('PDF Builder AJAX Error:', error);
                    showNotice(pdfBuilderWooCommerce.error_message, 'error');
                },
                complete: function() {
                    // Réactiver le bouton et restaurer le texte original
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Gestionnaire pour le bouton de personnalisation PDF Builder
        $('#pdf-builder-custom-template').on('click', function(e) {
            e.preventDefault();

            const orderId = $(this).data('order-id');

            // Ouvrir PDF Builder avec les données de la commande
            const builderUrl = pdfBuilderWooCommerce.builder_url + '&order_id=' + orderId;
            window.open(builderUrl, '_blank');
        });

        // Gestionnaire pour les actions en masse (si activé)
        $('#doaction, #doaction2').on('click', function(e) {
            const action = $(this).prev('select').val();

            if (action === 'pdf_builder_bulk_generate_invoice') {
                e.preventDefault();

                const checkedBoxes = $('input[name="post[]"]:checked');
                if (checkedBoxes.length === 0) {
                    alert(pdfBuilderWooCommerce.no_orders_selected);
                    return;
                }

                if (!confirm(pdfBuilderWooCommerce.confirm_bulk_action)) {
                    return;
                }

                // Traiter les commandes en masse
                processBulkPDFGeneration(checkedBoxes, 'invoice');
            }
        });

    });

    /**
     * Traite la génération PDF en masse
     *
     * @param {jQuery} checkedBoxes - Cases cochées des commandes
     * @param {string} templateType - Type de template
     */
    function processBulkPDFGeneration(checkedBoxes, templateType) {
        const orderIds = [];
        checkedBoxes.each(function() {
            orderIds.push($(this).val());
        });

        let processed = 0;
        const total = orderIds.length;

        // Afficher la progression
        showNotice(pdfBuilderWooCommerce.processing_bulk.replace('%d', total), 'info');

        // Traiter chaque commande
        processNextOrder();

        function processNextOrder() {
            if (processed >= total) {
                showNotice(pdfBuilderWooCommerce.bulk_complete, 'success');
                return;
            }

            const orderId = orderIds[processed];

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_generate_order_pdf',
                    order_id: orderId,
                    template_type: templateType,
                    nonce: pdfBuilderWooCommerce.nonce
                },
                success: function(response) {
                    processed++;
                    updateProgress(processed, total);

                    if (response.success) {
                        console.log('PDF généré pour commande #' + orderId);
                    } else {
                        console.error('Erreur pour commande #' + orderId + ':', response.data.message);
                    }

                    // Traiter la suivante
                    setTimeout(processNextOrder, 500); // Délai pour éviter la surcharge
                },
                error: function() {
                    processed++;
                    console.error('Erreur AJAX pour commande #' + orderId);
                    setTimeout(processNextOrder, 500);
                }
            });
        }
    }

    /**
     * Met à jour la progression du traitement en masse
     *
     * @param {number} processed - Nombre traité
     * @param {number} total - Nombre total
     */
    function updateProgress(processed, total) {
        const percentage = Math.round((processed / total) * 100);
        showNotice(pdfBuilderWooCommerce.processing_progress
            .replace('%1$d', processed)
            .replace('%2$d', total)
            .replace('%3$d', percentage), 'info');
    }

    /**
     * Affiche une notice WordPress
     *
     * @param {string} message - Message à afficher
     * @param {string} type - Type de notice (success, error, warning, info)
     */
    function showNotice(message, type) {
        // Supprimer les notices existantes
        $('.pdf-builder-notice').remove();

        // Créer la nouvelle notice
        const $notice = $('<div class="notice notice-' + type + ' is-dismissible pdf-builder-notice">' +
            '<p>' + message + '</p>' +
            '<button type="button" class="notice-dismiss">' +
                '<span class="screen-reader-text">' + pdfBuilderWooCommerce.dismiss_text + '</span>' +
            '</button>' +
        '</div>');

        // Ajouter au conteneur de notices
        const $container = $('.wrap h1').first();
        if ($container.length) {
            $container.after($notice);
        } else {
            $('.wrap').prepend($notice);
        }

        // Gestionnaire pour fermer la notice
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(300, function() {
                $notice.remove();
            });
        });

        // Auto-disparition pour les notices de succès après 5 secondes
        if (type === 'success') {
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $notice.remove();
                });
            }, 5000);
        }
    }

    // Exposer les fonctions globales si nécessaire
    window.PDFBuilderWooCommerce = {
        showNotice: showNotice,
        processBulkPDFGeneration: processBulkPDFGeneration
    };

})(jQuery);