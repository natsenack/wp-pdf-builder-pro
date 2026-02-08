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

        // Contrôles de zoom
        $('#zoom-in').on('click', function() {
            adjustZoom(25);
        });

        $('#zoom-out').on('click', function() {
            adjustZoom(-25);
        });

        $('#zoom-fit').on('click', function() {
            fitToWindow();
        });

        // Contrôles de rotation
        $('#rotate-left').on('click', function() {
            adjustRotation(-90);
        });

        $('#rotate-right').on('click', function() {
            adjustRotation(90);
        });

        // Contrôles de téléchargement
        $('#download-pdf').on('click', function() {
            downloadPreview('pdf');
        });

        $('#download-png').on('click', function() {
            downloadPreview('png');
        });

        $('#download-jpg').on('click', function() {
            downloadPreview('jpg');
        });

        // Gestion du drag sur l'image
        let isDragging = false;
        let startX, startY, scrollLeft, scrollTop;

        $(document).on('mousedown', '#preview-container img', function(e) {
            isDragging = true;
            startX = e.pageX - $(this).offset().left;
            startY = e.pageY - $(this).offset().top;
            $(this).css('cursor', 'grabbing');
        });

        $(document).on('mousemove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
            const img = $('#preview-container img');
            if (img.length) {
                const x = e.pageX - img.offset().left;
                const y = e.pageY - img.offset().top;
                const walkX = (x - startX) * 2;
                const walkY = (y - startY) * 2;
                img.css('transform', `translate(${walkX}px, ${walkY}px) ${getCurrentTransform()}`);
            }
        });

        $(document).on('mouseup', function() {
            isDragging = false;
            $('#preview-container img').css('cursor', 'move');
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
                    // Nonce temporairement désactivé pour debug
                    showErrorMessage(response.data.message || pdfBuilderPredefined.strings.loadError);
                }
            },
            error: function(xhr, status, error) {
                hideLoadingState();
                showErrorMessage(pdfBuilderPredefined.strings.loadError);
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
        showErrorMessage('Preview generation has been disabled');
    }

    /**
     * Régénérer un aperçu du modèle
     */
    function regeneratePreview(slug) {
        showErrorMessage('Preview generation has been disabled');
    }

    /**
     * Afficher la modale d'aperçu
     */
    function showPreviewModal(svgContent, templateSlug = null) {
        // Définir le template actuel
        if (templateSlug) {
            setCurrentPreviewTemplate(templateSlug);
        }

        // Réinitialiser les contrôles
        currentZoom = 100;
        currentRotation = 0;
        $('#zoom-level').text('100%');
        $('#rotation-angle').text('0°');

        // Afficher le contenu
        $('#preview-container').html(svgContent);
        $('#preview-modal').show();

        // Ajuster automatiquement au chargement
        setTimeout(() => {
            fitToWindow();
        }, 100);
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

    // Variables pour les contrôles d'aperçu
    let currentZoom = 100;
    let currentRotation = 0;
    let currentTemplateSlug = null;

    /**
     * Ajuster le zoom de l'aperçu
     */
    function adjustZoom(delta) {
        const img = $('#preview-container img');
        if (img.length) {
            currentZoom = Math.max(25, Math.min(500, currentZoom + delta));
            updateImageTransform();
            $('#zoom-level').text(currentZoom + '%');
        }
    }

    /**
     * Ajuster la rotation de l'aperçu
     */
    function adjustRotation(delta) {
        currentRotation = (currentRotation + delta) % 360;
        updateImageTransform();
        $('#rotation-angle').text(currentRotation + '°');
    }

    /**
     * Ajuster l'image à la fenêtre
     */
    function fitToWindow() {
        const container = $('#preview-container');
        const img = container.find('img');
        if (img.length) {
            const containerWidth = container.width();
            const containerHeight = container.height();
            const imgWidth = img[0].naturalWidth;
            const imgHeight = img[0].naturalHeight;

            const scaleX = containerWidth / imgWidth;
            const scaleY = containerHeight / imgHeight;
            const scale = Math.min(scaleX, scaleY) * 100;

            currentZoom = Math.max(25, Math.min(500, scale));
            updateImageTransform();
            $('#zoom-level').text(Math.round(currentZoom) + '%');
        }
    }

    /**
     * Mettre à jour la transformation de l'image
     */
    function updateImageTransform() {
        const img = $('#preview-container img');
        if (img.length) {
            const transform = `scale(${currentZoom / 100}) rotate(${currentRotation}deg)`;
            img.css('transform', transform);
        }
    }

    /**
     * Obtenir la transformation actuelle pour le drag
     */
    function getCurrentTransform() {
        return `scale(${currentZoom / 100}) rotate(${currentRotation}deg)`;
    }

    /**
     * Télécharger l'aperçu dans le format spécifié
     */
    function downloadPreview(format) {
        if (!currentTemplateSlug) {
            showErrorMessage('Aucun modèle sélectionné pour le téléchargement.');
            return;
        }

        // Créer un lien temporaire pour le téléchargement
        const downloadUrl = pdfBuilderPredefined.ajaxUrl + '?action=pdf_builder_download_preview&slug=' + currentTemplateSlug + '&format=' + format + '&nonce=' + pdfBuilderPredefined.nonce;

        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = 'preview.' + format;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showSuccessMessage('Téléchargement démarré pour le format ' + format.toUpperCase());
    }

    /**
     * Définir le modèle actuel pour l'aperçu
     */
    function setCurrentPreviewTemplate(slug) {
        currentTemplateSlug = slug;
    }

})(jQuery);

