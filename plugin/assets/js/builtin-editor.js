/**
 * PDF Builder Pro - Builtin Templates Editor JavaScript
 */

(function($) {
    'use strict';

    // No longer need editor-related variables since editing is done in React

    $(document).ready(function() {



        if (typeof pdfBuilderBuiltinEditor === 'undefined') {

            return;
        }

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
            error: function(xhr, status, error) {

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
                    <div class="template-actions">
                        <button class="template-edit-btn" data-template-id="${template.id}" title="Modifier les paramètres">
                            <span class="dashicons dashicons-admin-generic"></span>
                        </button>
                        <button class="template-delete-btn" data-template-id="${template.id}" title="Supprimer le template">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                        <a href="admin.php?page=pdf-builder-builtin-editor&template=${template.id}" class="button button-primary button-small">
                            <span class="dashicons dashicons-edit"></span>
                            Éditer
                        </a>
                    </div>
                </div>
            `);
            
            // Log the HTML generated





            // Click handler for non-button clicks (info area only)
            item.on('click', function(e) {
                // Only trigger if clicking directly on template-info or template-thumbnail
                if ($(e.target).closest('.template-info, .template-thumbnail').length && !$(e.target).closest('button, a').length) {

                    loadTemplate(template.id);
                }
            });

            container.append(item);





        });







        
        // Log CSS computed styles for debugging
        const firstEditBtn = $('.template-edit-btn').first();
        if (firstEditBtn.length) {
            const computed = window.getComputedStyle(firstEditBtn[0]);






        }
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
        $(document).on('click', '.template-edit-btn', function(e) {
            e.stopPropagation();
            const templateId = $(this).data('template-id');


            showEditTemplateModal(templateId);
        });

        // Delete template buttons
        $(document).on('click', '.template-delete-btn', function(e) {
            e.stopPropagation();
            const templateId = $(this).data('template-id');


            if (confirm(pdfBuilderBuiltinEditor.strings.confirm_delete)) {
                deleteTemplate(templateId);
            }
        });

        // New template modal - close buttons
        $('#new-template-modal .pdf-modal-close, #new-template-modal .pdf-modal-backdrop').on('click', function() {

            hideNewTemplateModal();
        });

        // New template create button
        $('#create-template-confirm').on('click', function() {

            const name = $('#template-name').val().trim();
            const description = $('#template-description').val().trim();
            const category = $('#template-category').val();





            if (!name) {

                alert('Le nom du template est requis');
                return;
            }

            createNewTemplate(name, description, category);
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

        
        // Show the modal
        $('#new-template-modal').show();
        
        // Reset form
        $('#new-template-form')[0].reset();
        

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

        
        // Load template data using NEW action without nonce checks
        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_load_template_for_modal',
                template_id: templateId
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
            error: function(xhr, status, error) {

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
            error: function(xhr, status, error) {

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
            error: function(xhr, status, error) {

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
