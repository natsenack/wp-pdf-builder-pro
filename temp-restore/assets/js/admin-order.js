/**
 * PDF Builder Pro - Scripts d'administration pour les commandes WooCommerce
 * Version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Gestionnaire des PDFs dans l'administration des commandes
     */
    var PDFBuilderOrderAdmin = {

        /**
         * Initialisation
         */
        init: function() {
            this.bindEvents();
            this.initOrderPDFs();
        },

        /**
         * Liaison des événements
         */
        bindEvents: function() {
            // Régénération de PDF
            $(document).on('click', '.pdf-builder-regenerate-pdf', this.regeneratePDF.bind(this));

            // Téléchargement de PDF
            $(document).on('click', '.pdf-builder-download-pdf', this.downloadPDF.bind(this));

            // Actualisation de la liste des PDFs
            $(document).on('click', '.pdf-builder-refresh-pdfs', this.refreshPDFs.bind(this));
        },

        /**
         * Initialisation des PDFs de commande
         */
        initOrderPDFs: function() {
            var self = this;

            // Ajouter des boutons aux actions de commande si nécessaire
            if ($('#woocommerce-order-actions').length) {
                this.addOrderActionButtons();
            }

            // Charger les PDFs existants pour cette commande
            this.loadOrderPDFs();
        },

        /**
         * Ajouter des boutons d'action aux commandes
         */
        addOrderActionButtons: function() {
            var orderId = this.getOrderId();

            if (!orderId) return;

            var actionsContainer = $('#woocommerce-order-actions .inside');

            // Bouton pour rafraîchir les PDFs
            var refreshButton = $('<button>')
                .addClass('button pdf-builder-refresh-pdfs')
                .attr('type', 'button')
                .html('<span class="dashicons dashicons-update"></span> ' + pdfBuilderAjax.strings.refreshPDFs || 'Actualiser PDFs');

            actionsContainer.append(refreshButton);
        },

        /**
         * Charger les PDFs de la commande
         */
        loadOrderPDFs: function() {
            var orderId = this.getOrderId();

            if (!orderId) return;

            var self = this;

            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_load_order_pdfs',
                    order_id: orderId,
                    nonce: pdfBuilderAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.displayOrderPDFs(response.data.pdfs);
                    }
                },
                error: function() {
                    console.error('Erreur lors du chargement des PDFs de commande');
                }
            });
        },

        /**
         * Afficher les PDFs de commande
         */
        displayOrderPDFs: function(pdfs) {
            var container = $('#pdf-builder-order-pdfs');

            if (!container.length) {
                // Créer le conteneur s'il n'existe pas
                var metabox = $('<div class="postbox">')
                    .attr('id', 'pdf-builder-order-pdfs-box')
                    .append(
                        $('<h2 class="hndle">')
                            .html('<span>PDFs de la commande</span>')
                    )
                    .append(
                        $('<div class="inside">')
                            .attr('id', 'pdf-builder-order-pdfs')
                    );

                $('#normal-sortables').prepend(metabox);
                container = $('#pdf-builder-order-pdfs');
            }

            container.empty();

            if (!pdfs || pdfs.length === 0) {
                container.html('<p>' + (pdfBuilderAjax.strings.noPDFs || 'Aucun PDF généré pour cette commande.') + '</p>');
                return;
            }

            var pdfsList = $('<div class="pdf-builder-pdfs-list">');

            pdfs.forEach(function(pdf) {
                var pdfItem = $('<div class="pdf-builder-pdf-item">')
                    .addClass('pdf-item')
                    .css({
                        'margin-bottom': '10px',
                        'padding': '10px',
                        'border': '1px solid #ddd',
                        'border-radius': '4px',
                        'background': '#f9f9f9'
                    });

                var pdfHeader = $('<div class="pdf-header">')
                    .css({
                        'display': 'flex',
                        'justify-content': 'space-between',
                        'align-items': 'center',
                        'margin-bottom': '8px'
                    });

                pdfHeader.append(
                    $('<strong>').text(pdf.template_name || 'Template inconnu')
                );

                pdfHeader.append(
                    $('<small>')
                        .css('color', '#666')
                        .text(pdf.created_at_formatted || pdf.created_at)
                );

                var pdfActions = $('<div class="pdf-actions">')
                    .css({
                        'display': 'flex',
                        'gap': '5px'
                    });

                // Bouton Voir
                pdfActions.append(
                    $('<a>')
                        .addClass('button button-small pdf-builder-view-pdf')
                        .attr('href', pdf.url)
                        .attr('target', '_blank')
                        .text(pdfBuilderAjax.strings.view || 'Voir')
                );

                // Bouton Télécharger
                pdfActions.append(
                    $('<a>')
                        .addClass('button button-small button-primary pdf-builder-download-pdf')
                        .attr('href', pdf.url)
                        .attr('download', pdf.filename || 'document.pdf')
                        .text(pdfBuilderAjax.strings.download || 'Télécharger')
                );

                // Bouton Régénérer
                pdfActions.append(
                    $('<button>')
                        .addClass('button button-small pdf-builder-regenerate-pdf')
                        .attr('data-template-id', pdf.template_id)
                        .attr('data-pdf-id', pdf.id)
                        .text(pdfBuilderAjax.strings.regenerate || 'Régénérer')
                );

                pdfItem.append(pdfHeader);
                pdfItem.append(pdfActions);
                pdfsList.append(pdfItem);
            });

            container.append(pdfsList);
        },

        /**
         * Régénérer un PDF
         */
        regeneratePDF: function(e) {
            e.preventDefault();

            var button = $(e.target).closest('.pdf-builder-regenerate-pdf');
            var templateId = button.data('template-id');
            var pdfId = button.data('pdf-id');
            var orderId = this.getOrderId();

            if (!templateId || !orderId) return;

            var originalText = button.text();
            button.prop('disabled', true).text(pdfBuilderAjax.strings.regenerating || 'Régénération...');

            var self = this;

            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_regenerate_order_pdf',
                    order_id: orderId,
                    template_id: templateId,
                    pdf_id: pdfId,
                    nonce: pdfBuilderAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Recharger la liste des PDFs
                        self.loadOrderPDFs();

                        // Notification de succès
                        self.showNotice(
                            pdfBuilderAjax.strings.regenerated || 'PDF régénéré avec succès',
                            'success'
                        );
                    } else {
                        self.showNotice(
                            response.data.message || 'Erreur lors de la régénération',
                            'error'
                        );
                    }
                },
                error: function() {
                    self.showNotice('Erreur lors de la régénération du PDF', 'error');
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Télécharger un PDF
         */
        downloadPDF: function(e) {
            e.preventDefault();

            var link = $(e.target).closest('.pdf-builder-download-pdf');
            var url = link.attr('href');
            var filename = link.attr('download');

            // Créer un lien temporaire pour le téléchargement
            var tempLink = document.createElement('a');
            tempLink.href = url;
            tempLink.download = filename;
            tempLink.style.display = 'none';

            document.body.appendChild(tempLink);
            tempLink.click();
            document.body.removeChild(tempLink);
        },

        /**
         * Rafraîchir la liste des PDFs
         */
        refreshPDFs: function(e) {
            e.preventDefault();

            var button = $(e.target).closest('.pdf-builder-refresh-pdfs');
            var originalText = button.html();

            button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Actualisation...');

            var self = this;

            setTimeout(function() {
                self.loadOrderPDFs();
                button.prop('disabled', false).html(originalText);
            }, 500);
        },

        /**
         * Obtenir l'ID de la commande actuelle
         */
        getOrderId: function() {
            // Essayer différentes méthodes pour obtenir l'ID de commande
            var orderId = $('#post_ID').val();

            if (!orderId && typeof postId !== 'undefined') {
                orderId = postId;
            }

            if (!orderId) {
                // Extraire de l'URL
                var urlMatch = window.location.href.match(/post=(\d+)/);
                if (urlMatch) {
                    orderId = urlMatch[1];
                }
            }

            return orderId;
        },

        /**
         * Afficher une notification
         */
        showNotice: function(message, type) {
            // Utiliser l'API de notifications WordPress si disponible
            if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
                wp.data.dispatch('core/notices').createNotice(
                    type === 'error' ? 'error' : 'success',
                    message,
                    {
                        isDismissible: true,
                        type: type === 'error' ? 'error' : 'success'
                    }
                );
            } else {
                // Fallback: alerte simple
                alert(message);
            }
        }
    };

    // Initialisation quand le DOM est prêt
    $(document).ready(function() {
        PDFBuilderOrderAdmin.init();
    });

    // Exposition globale pour le débogage
    window.PDFBuilderOrderAdmin = PDFBuilderOrderAdmin;

})(jQuery);