/**
 * PDF Builder Pro - Predefined Templates Manager JavaScript
 */

(function($) {
    'use strict';

    let codeMirrorEditor = null;
    let currentEditingSlug = null;

    $(document).ready(function() {
        // Simple check if jQuery is working
        if (typeof $ !== 'undefined') {
            console.log('jQuery loaded, initializing PDF Builder templates...');
        }
        initializeInterface();
        setupEventListeners();
    });

    /**
     * Initialiser l'interface
     */
    function initializeInterface() {
        // Check if elements exist
        if (document.getElementById('new-template-btn')) {
            console.log('New template button found');
        } else {
            console.log('New template button NOT found');
        }

        // Check for edit/delete buttons
        const editButtons = document.querySelectorAll('.edit-template');
        const deleteButtons = document.querySelectorAll('.delete-template');
        const previewButtons = document.querySelectorAll('.generate-preview');

        console.log('Edit buttons found:', editButtons.length);
        console.log('Delete buttons found:', deleteButtons.length);
        console.log('Preview buttons found:', previewButtons.length);

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
        console.log('Setting up event listeners...');

        // Nouveau modèle
        $('#new-template-btn').on('click', function() {
            console.log('New template button clicked');
            showTemplateEditor();
        });

        // Annuler l'édition
        $('#cancel-edit-btn').on('click', function() {
            console.log('Cancel button clicked');
            hideTemplateEditor();
        });

        // Sauvegarder le modèle
        $('#template-form').on('submit', function(e) {
            console.log('Form submitted');
            e.preventDefault();
            saveTemplate();
        });

        // Valider le JSON
        $('#validate-json-btn').on('click', function() {
            console.log('Validate JSON button clicked');
            validateJson();
        });

        // Éditer un modèle existant
        $(document).on('click', '.edit-template', function() {
            const slug = $(this).data('slug');
            console.log('Edit template clicked for:', slug);
            loadTemplate(slug);
        });

        // Supprimer un modèle
        $(document).on('click', '.delete-template', function() {
            const slug = $(this).data('slug');
            console.log('Delete template clicked for:', slug);
            deleteTemplate(slug);
        });

        // Générer un aperçu
        $(document).on('click', '.generate-preview', function() {
            const slug = $(this).data('slug');
            console.log('Generate preview clicked for:', slug);
            generatePreview(slug);
        });

        // Actualiser la liste
        $('#refresh-templates-btn').on('click', function() {
            console.log('Refresh templates clicked');
            refreshTemplatesList();
        });

        // Fermer la modale d'aperçu
        $(document).on('click', '.close-modal', function() {
            console.log('Close modal clicked');
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
                hideLoadingState();

                if (response.success) {
                    populateForm(response.data);
                    showTemplateEditor(response.data);
                } else {
                    showErrorMessage(response.data.message || pdfBuilderPredefined.strings.loadError);
                }
            },
            error: function() {
                hideLoadingState();
                showErrorMessage(pdfBuilderPredefined.strings.loadError);
            }
        });
    }

    /**
     * Remplir le formulaire avec les données du modèle
     */
    function populateForm(data) {
        $('#template-slug').val(data.slug).prop('disabled', true);
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
                    showSuccessMessage(pdfBuilderPredefined.strings.saveSuccess);
                    hideTemplateEditor();
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
        $('.pdf-builder-predefined-container').prepend(notice);
        setTimeout(() => notice.fadeOut(), 5000);
    }

    function showErrorMessage(message) {
        hideMessages();
        const notice = $('<div class="admin-notice error-message"><p>' + message + '</p></div>');
        $('.pdf-builder-predefined-container').prepend(notice);
        setTimeout(() => notice.fadeOut(), 5000);
    }

    function hideMessages() {
        $('.admin-notice').remove();
    }

})(jQuery);