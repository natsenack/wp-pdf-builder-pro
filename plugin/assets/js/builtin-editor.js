/**
 * PDF Builder Pro - Builtin Templates Editor JavaScript
 */

(function($) {
    'use strict';

    // No longer need editor-related variables since editing is done in React

    $(document).ready(function() {
        loadTemplatesList();
        setupEventHandlers();
    });

    /**
     * Load the list of builtin templates
     */
    function loadTemplatesList() {
        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_load_builtin_templates',
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderTemplatesList(response.data.templates);
                } else {
                    showError('Erreur lors du chargement des templates: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function() {
                showError('Erreur de connexion lors du chargement des templates');
            }
        });
    }

    /**
     * Render the templates list
     */
    function renderTemplatesList(templates) {
        const container = $('#templates-list');
        container.empty();

        if (templates.length === 0) {
            container.append('<p style="color: #666; font-style: italic;">Aucun template trouvé</p>');
            return;
        }

        templates.forEach(function(template) {
            const item = $(`
                <div class="template-list-item" data-template-id="${template.id}">
                    <div class="template-thumbnail">
                        <div class="template-preview-mini">
                            <!-- Mini aperçu -->
                        </div>
                    </div>
                    <div class="template-info">
                        <h4>${template.name}</h4>
                        <p>${template.description}</p>
                        <small>${template.elements.length} éléments</small>
                    </div>
                </div>
            `);

            item.on('click', function() {
                loadTemplate(template.id);
            });

            container.append(item);
        });
    }

    /**
     * Load a specific template (redirect to React editor)
     */
    function loadTemplate(templateId) {
        // Redirect to the builtin editor page with template parameter to trigger React editor
        window.location.href = '?page=pdf-builder-builtin-editor&template=' + encodeURIComponent(templateId);
    }

    /**
     * Create a new template
     */
    function createNewTemplate(name, description, category) {
        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_builtin_template',
                name: name,
                description: description,
                category: category,
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(pdfBuilderBuiltinEditor.strings.template_created);
                    // Reload the templates list
                    loadTemplatesList();
                    hideNewTemplateModal();
                } else {
                    showError('Erreur lors de la création: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function() {
                showError('Erreur de connexion lors de la création');
            }
        });
    }

    /**
     * Setup event handlers
     */
    function setupEventHandlers() {
        // New template button
        $('#new-template-btn').on('click', function() {
            showNewTemplateModal();
        });

        // Edit template parameters buttons
        $(document).on('click', '.template-edit-btn', function() {
            const templateId = $(this).data('template-id');
            showEditTemplateModal(templateId);
        });

        // Delete template buttons
        $(document).on('click', '.template-delete-btn', function() {
            const templateId = $(this).data('template-id');
            if (confirm(pdfBuilderBuiltinEditor.strings.confirm_delete)) {
                deleteTemplate(templateId);
            }
        });

        // Update template confirm button
        $('#update-template-confirm').on('click', function() {
            updateTemplateParameters();
        });

        // Close edit modal events
        $('#edit-template-modal .pdf-modal-close, #edit-template-modal .pdf-modal-backdrop').on('click', function() {
            hideEditTemplateModal();
        });
    }

    /**
     * Show new template modal
     */
    function showNewTemplateModal() {
        // Add modal to page if not exists
        if (!$('#new-template-modal').length) {
            $('body').append($('#new-template-modal-template').html());
        }

        $('#new-template-modal').show();

        // Setup modal events
        $('#cancel-new-template, .pdf-modal-close').on('click', function() {
            hideNewTemplateModal();
        });

        $('#create-new-template').on('click', function() {
            const name = $('#template-name').val().trim();
            const description = $('#template-description').val().trim();
            const category = $('#template-category').val();

            if (!name) {
                alert('Le nom du template est requis');
                return;
            }

            createNewTemplate(name, description, category);
        });

        // Close on backdrop click
        $('.pdf-modal-backdrop').on('click', function() {
            hideNewTemplateModal();
        });
    }

    /**
     * Hide new template modal
     */
    function hideNewTemplateModal() {
        $('#new-template-modal').hide();
        $('#new-template-form')[0].reset();
    }

    /**
     * Show edit template modal
     */
    function showEditTemplateModal(templateId) {
        // Load template data
        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_load_builtin_template',
                template_id: templateId,
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                if (response.success) {
                    const template = response.data.template;

                    // Fill form
                    $('#edit-template-id').val(templateId);
                    $('#edit-template-name').val(template.name || '');
                    $('#edit-template-description').val(template.description || '');
                    $('#edit-template-category').val(template.category || 'general');

                    // Show modal
                    $('#edit-template-modal').show();
                } else {
                    showError('Erreur lors du chargement du template: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function() {
                showError('Erreur de connexion lors du chargement du template');
            }
        });
    }

    /**
     * Update template parameters
     */
    function updateTemplateParameters() {
        const templateId = $('#edit-template-id').val();
        const name = $('#edit-template-name').val().trim();
        const description = $('#edit-template-description').val().trim();
        const category = $('#edit-template-category').val();

        if (!name) {
            alert('Le nom du template est requis');
            return;
        }

        $('#update-template-confirm').prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + 'Mise à jour...');

        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_update_builtin_template_params',
                template_id: templateId,
                name: name,
                description: description,
                category: category,
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('Paramètres mis à jour avec succès');
                    loadTemplatesList();
                    hideEditTemplateModal();
                } else {
                    showError('Erreur lors de la mise à jour: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function() {
                showError('Erreur de connexion lors de la mise à jour');
            },
            complete: function() {
                $('#update-template-confirm').prop('disabled', false).html('Mettre à jour');
            }
        });
    }

    /**
     * Hide edit template modal
     */
    function hideEditTemplateModal() {
        $('#edit-template-modal').hide();
        $('#edit-template-form')[0].reset();
    }

    /**
     * Delete the current template
     */
    function deleteTemplate(templateId) {
        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_delete_builtin_template',
                template_id: templateId,
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(pdfBuilderBuiltinEditor.strings.template_deleted);
                    // Reload the templates list
                    loadTemplatesList();
                } else {
                    showError('Erreur lors de la suppression: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function() {
                showError('Erreur de connexion lors de la suppression');
            }
        });
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        showNotice(message, 'success');
    }

    /**
     * Show error message
     */
    function showError(message) {
        showNotice(message, 'error');
    }

    /**
     * Show notice
     */
    function showNotice(message, type) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const $notice = $(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);

        $('.wp-header-end').after($notice);

        // Auto-dismiss after 3 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);