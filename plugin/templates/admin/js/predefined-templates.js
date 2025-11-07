/**
 * PDF Builder Pro - Predefined Templates Manager JavaScript
 */

(function($) {
    'use strict';

    let codeMirrorEditor = null;
    let currentEditingSlug = null;

    $(document).ready(function() {
        initializeInterface();
        setupEventListeners();
    });

    /**
     * Initialiser l'interface
     */
    function initializeInterface() {
        // Initialiser CodeMirror pour l'éditeur JSON
        initializeCodeMirror();

        // Masquer la section éditeur au départ
        $('.template-editor-section').hide();
    }

    /**
     * Initialiser CodeMirror
     */
    function initializeCodeMirror() {
        const textArea = document.getElementById('template-json');
        if (textArea && typeof CodeMirror !== 'undefined') {
            codeMirrorEditor = CodeMirror.fromTextArea(textArea, {
                mode: 'application/json',
                lineNumbers: true,
                theme: 'default',
                indentUnit: 2,
                smartIndent: true,
                lineWrapping: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter']
            });

            // Ajuster la taille
            codeMirrorEditor.setSize('100%', '400px');
        }
    }

    /**
     * Configurer les écouteurs d'événements
     */
    function setupEventListeners() {
        // Nouveau modèle
        $('#new-template-btn').on('click', function() {
            showTemplateEditor();
        });

        // Annuler l'édition
        $('#cancel-edit-btn').on('click', function() {
            hideTemplateEditor();
        });

        // Sauvegarder le modèle
        $('#template-form').on('submit', function(e) {
            e.preventDefault();
            saveTemplate();
        });

        // Valider le JSON
        $('#validate-json-btn').on('click', function() {
            validateJson();
        });

        // Éditer un modèle existant
        $(document).on('click', '.edit-template', function() {
            const slug = $(this).data('slug');
            console.log('Edit button clicked for template:', slug);
            console.log('Loading template data...');
            loadTemplate(slug);
        });

        // Supprimer un modèle
        $(document).on('click', '.delete-template', function() {
            const slug = $(this).data('slug');
            deleteTemplate(slug);
        });

        // Générer un aperçu
        $(document).on('click', '.generate-preview', function() {
            const slug = $(this).data('slug');
            generatePreview(slug);
        });

        // Régénérer un aperçu
        $(document).on('click', '.regenerate-preview', function() {
            const slug = $(this).data('slug');
            regeneratePreview(slug);
        });

        // Actualiser la liste
        $('#refresh-templates-btn').on('click', function() {
            refreshTemplatesList();
        });

        // Fermer la modale d'aperçu
        $(document).on('click', '.close-modal', function() {
            $('#preview-modal').hide();
        });

        // Fermer la modale en cliquant en dehors
        $('#preview-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
    }

    /**
     * Afficher l'éditeur de modèle
     */
    function showTemplateEditor(template = null) {
        $('.template-editor-section').show();
        $('#editor-title').text(template ? 'Éditer le Modèle' : 'Nouveau Modèle');

        if (!template) {
            // Nouveau modèle - vider le formulaire
            $('#template-slug').val('').prop('disabled', false);
            $('#template-name').val('');
            $('#template-category').val('facture');
            $('#template-description').val('');
            $('#template-icon').val('📄');

            // JSON par défaut
            const defaultJson = {
                elements: [],
                canvasWidth: 794,
                canvasHeight: 1123,
                version: "1.0"
            };

            if (codeMirrorEditor) {
                codeMirrorEditor.setValue(JSON.stringify(defaultJson, null, 2));
            } else {
                $('#template-json').val(JSON.stringify(defaultJson, null, 2));
            }

            currentEditingSlug = null;
        }

        // Scroll vers l'éditeur
        $('.template-editor-section')[0].scrollIntoView({ behavior: 'smooth' });
    }

    /**
     * Masquer l'éditeur de modèle
     */
    function hideTemplateEditor() {
        $('.template-editor-section').hide();
        currentEditingSlug = null;
    }

    /**
     * Charger un modèle pour l'édition
     */
    function loadTemplate(slug) {
        console.log('loadTemplate called with slug:', slug);
        console.log('Sending nonce:', pdfBuilderPredefined.nonce);
        showLoadingState();

        $.ajax({
            url: pdfBuilderPredefined.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pdf_builder_load_predefined_template',
                slug: slug,
                nonce: pdfBuilderPredefined.nonce
            },
            success: function(response) {
                console.log('AJAX success response:', response);
                hideLoadingState();

                if (response.success) {
                    console.log('Template loaded successfully, populating form...');
                    populateForm(response.data);
                    showTemplateEditor(response.data);
                } else {
                    // Nonce temporairement désactivé pour debug
                    console.error('Template load failed:', response.data.message);
                    showErrorMessage(response.data.message || pdfBuilderPredefined.strings.loadError);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr.responseText);
                hideLoadingState();
                showErrorMessage(pdfBuilderPredefined.strings.loadError);
            }
        });
    }

    /**
     * Actualiser le nonce et réessayer le chargement
     */
    function refreshNonceAndRetry(slug) {
        console.log('Refreshing nonce...');
        $.ajax({
            url: pdfBuilderPredefined.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pdf_builder_refresh_nonce'
            },
            success: function(response) {
                if (response.success) {
                    console.log('Nonce refreshed:', response.data.nonce);
                    pdfBuilderPredefined.nonce = response.data.nonce;
                    // Réessayer le chargement avec le nouveau nonce
                    loadTemplate(slug);
                } else {
                    console.error('Failed to refresh nonce');
                    showErrorMessage('Erreur de sécurité - veuillez rafraîchir la page');
                }
            },
            error: function() {
                console.error('AJAX error refreshing nonce');
                showErrorMessage('Erreur de sécurité - veuillez rafraîchir la page');
            }
        });
    }
    function populateForm(data) {
        $('#template-slug').val(data.slug).prop('disabled', false);
        $('#template-name').val(data.name);
        $('#template-category').val(data.category);
        $('#template-description').val(data.description);
        $('#template-icon').val(data.icon);

        if (codeMirrorEditor) {
            codeMirrorEditor.setValue(data.json);
        } else {
            $('#template-json').val(data.json);
        }

        currentEditingSlug = data.slug;
    }

    /**
     * Sauvegarder le modèle
     */
    function saveTemplate() {
        const formData = getFormData();

        if (!validateFormData(formData)) {
            return;
        }

        showLoadingState();

        $.ajax({
            url: pdfBuilderPredefined.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pdf_builder_save_predefined_template',
                ...formData,
                nonce: pdfBuilderPredefined.nonce
            },
            success: function(response) {
                hideLoadingState();

                if (response.success) {
                    // Mettre à jour le slug actuel si le modèle a été renommé
                    if (response.data.renamed) {
                        currentEditingSlug = response.data.slug;
                        console.log('Template renamed from', response.data.renamed, 'to', response.data.slug);
                    }

                    showSuccessMessage(pdfBuilderPredefined.strings.saveSuccess);
                    // Ne plus fermer automatiquement l'éditeur après sauvegarde
                    // hideTemplateEditor();
                    refreshTemplatesList();
                } else {
                    showErrorMessage(response.data.message || pdfBuilderPredefined.strings.saveError);
                }
            },
            error: function() {
                hideLoadingState();
                showErrorMessage(pdfBuilderPredefined.strings.saveError);
            }
        });
    }

    /**
     * Récupérer les données du formulaire
     */
    function getFormData() {
        const jsonValue = codeMirrorEditor ? codeMirrorEditor.getValue() : $('#template-json').val();

        return {
            old_slug: currentEditingSlug, // Slug original pour gérer le renommage
            slug: $('#template-slug').val().trim(),
            name: $('#template-name').val().trim(),
            category: $('#template-category').val(),
            description: $('#template-description').val().trim(),
            icon: $('#template-icon').val().trim(),
            json: jsonValue
        };
    }

    /**
     * Valider les données du formulaire
     */
    function validateFormData(data) {
        if (!data.slug || !data.name || !data.category || !data.json) {
            showErrorMessage('Tous les champs obligatoires doivent être remplis.');
            return false;
        }

        // Valider le slug (lettres minuscules, chiffres, tirets)
        if (!/^[a-z0-9-]+$/.test(data.slug)) {
            showErrorMessage('Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.');
            return false;
        }

        // Valider le JSON
        try {
            JSON.parse(data.json);
        } catch (e) {
            showErrorMessage('Le JSON n\'est pas valide: ' + e.message);
            return false;
        }

        return true;
    }

    /**
     * Valider le JSON manuellement
     */
    function validateJson() {
        const jsonValue = codeMirrorEditor ? codeMirrorEditor.getValue() : $('#template-json').val();

        try {
            const parsed = JSON.parse(jsonValue);
            showSuccessMessage('JSON valide ! Structure détectée avec ' + (parsed.elements ? parsed.elements.length : 0) + ' éléments.');
        } catch (e) {
            showErrorMessage('JSON invalide: ' + e.message);
        }
    }

    /**
     * Supprimer un modèle
     */
    function deleteTemplate(slug) {
        if (!confirm(pdfBuilderPredefined.strings.confirmDelete)) {
            return;
        }

        showLoadingState();

        $.ajax({
            url: pdfBuilderPredefined.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pdf_builder_delete_predefined_template',
                slug: slug,
                nonce: pdfBuilderPredefined.nonce
            },
            success: function(response) {
                hideLoadingState();

                if (response.success) {
                    showSuccessMessage(pdfBuilderPredefined.strings.deleteSuccess);
                    refreshTemplatesList();
                } else {
                    showErrorMessage(response.data.message || pdfBuilderPredefined.strings.deleteError);
                }
            },
            error: function() {
                hideLoadingState();
                showErrorMessage(pdfBuilderPredefined.strings.deleteError);
            }
        });
    }

    /**
     * Générer un aperçu du modèle
     */
    function generatePreview(slug) {
        showLoadingState();

        $.ajax({
            url: pdfBuilderPredefined.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pdf_builder_generate_template_preview',
                slug: slug,
                nonce: pdfBuilderPredefined.nonce
            },
            success: function(response) {
                hideLoadingState();

                if (response.success) {
                    // Afficher l'aperçu dans une modale
                    showPreviewModal(response.data.preview_svg);
                    refreshTemplatesList(); // Actualiser pour voir le nouvel aperçu
                } else {
                    showErrorMessage(response.data.message || pdfBuilderPredefined.strings.previewError);
                }
            },
            error: function() {
                hideLoadingState();
                showErrorMessage(pdfBuilderPredefined.strings.previewError);
            }
        });
    }

    /**
     * Régénérer un aperçu du modèle
     */
    function regeneratePreview(slug) {
        showLoadingState();

        $.ajax({
            url: pdfBuilderPredefined.ajaxUrl,
            type: 'POST',
            data: {
                action: 'pdf_builder_generate_template_preview',
                slug: slug,
                nonce: pdfBuilderPredefined.nonce
            },
            success: function(response) {
                hideLoadingState();

                if (response.success) {
                    // Afficher un message de succès et actualiser la liste
                    showSuccessMessage('Aperçu régénéré avec succès !');
                    refreshTemplatesList(); // Actualiser pour voir le nouvel aperçu
                } else {
                    showErrorMessage(response.data.message || pdfBuilderPredefined.strings.previewError);
                }
            },
            error: function() {
                hideLoadingState();
                showErrorMessage(pdfBuilderPredefined.strings.previewError);
            }
        });
    }

    /**
     * Afficher la modale d'aperçu
     */
    function showPreviewModal(svgContent) {
        $('#preview-container').html(svgContent);
        $('#preview-modal').show();
    }

    /**
     * Actualiser la liste des modèles
     */
    function refreshTemplatesList() {
        showLoadingState();

        // Recharger la page pour simplifier (ou implémenter un rechargement AJAX)
        location.reload();
    }

    /**
     * États de chargement
     */
    function showLoadingState() {
        $('#save-template-btn').prop('disabled', true).text('⏳ Sauvegarde...');
        $('.templates-list-section').addClass('loading');
    }

    function hideLoadingState() {
        $('#save-template-btn').prop('disabled', false).text('💾 Sauvegarder');
        $('.templates-list-section').removeClass('loading');
    }

    /**
     * Messages de succès/erreur
     */
    function showSuccessMessage(message) {
        hideMessages();
        const notice = $('<div class="admin-notice success-message"><p>' + message + '</p></div>');
        $('body').append(notice);
        setTimeout(() => notice.fadeOut(() => notice.remove()), 5000);
    }

    function showErrorMessage(message) {
        hideMessages();
        const notice = $('<div class="admin-notice error-message"><p>' + message + '</p></div>');
        $('body').append(notice);
        setTimeout(() => notice.fadeOut(() => notice.remove()), 5000);
    }

    function hideMessages() {
        $('.admin-notice').remove();
    }

})(jQuery);