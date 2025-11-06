/**
 * PDF Builder Pro - Builtin Templates Editor JavaScript
 */

(function($) {
    'use strict';

    // No longer need editor-related variables since editing is done in React

    $(document).ready(function() {
        console.log('🔍 [BUILTIN EDITOR] Page loaded, initializing...');
        console.log('🔍 [BUILTIN EDITOR] pdfBuilderBuiltinEditor:', typeof pdfBuilderBuiltinEditor, pdfBuilderBuiltinEditor);

        if (typeof pdfBuilderBuiltinEditor === 'undefined') {
            console.error('❌ [BUILTIN EDITOR] pdfBuilderBuiltinEditor is not defined!');
            return;
        }

        loadTemplatesList();
        setupEventHandlers();
    });

    /**
     * Load the list of builtin templates
     */
    function loadTemplatesList() {
        console.log('🔍 [BUILTIN EDITOR] Loading templates list...');

        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_load_builtin_templates',
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                console.log('✅ [BUILTIN EDITOR] AJAX success:', response);
                if (response.success) {
                    console.log('📋 [BUILTIN EDITOR] Rendering', response.data.templates.length, 'templates');
                    renderTemplatesList(response.data.templates);
                } else {
                    console.error('❌ [BUILTIN EDITOR] AJAX error:', response.data);
                    showError('Erreur lors du chargement des templates: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ [BUILTIN EDITOR] AJAX failed:', status, error);
                showError('Erreur de connexion lors du chargement des templates');
            }
        });
    }

    /**
     * Render the templates list
     */
    function renderTemplatesList(templates) {
        console.log('🎨 [BUILTIN EDITOR] Rendering templates:', templates);

        const container = $('#templates-list');
        console.log('📦 [BUILTIN EDITOR] Container found:', container.length, 'elements');

        container.empty();

        if (templates.length === 0) {
            container.append('<p style="color: #666; font-style: italic;">Aucun template trouvé</p>');
            return;
        }

        templates.forEach(function(template) {
            console.log('🔧 [BUILTIN EDITOR] Processing template:', template.id, template.name);

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
            console.log('📝 [BUILTIN EDITOR] Generated HTML:', item.html());
            console.log('🔍 [BUILTIN EDITOR] .template-actions found in item:', item.find('.template-actions').length, 'elements');
            console.log('🔍 [BUILTIN EDITOR] .template-edit-btn found in item:', item.find('.template-edit-btn').length, 'elements');
            console.log('🔍 [BUILTIN EDITOR] .template-delete-btn found in item:', item.find('.template-delete-btn').length, 'elements');

            // Click handler for the template item - but NOT on action buttons
            item.on('click', function(e) {
                // Don't navigate if clicking on buttons
                if ($(e.target).closest('.template-actions').length) {
                    console.log('🚫 [BUILTIN EDITOR] Click was on action buttons, ignoring');
                    return false;
                }
                console.log('📂 [BUILTIN EDITOR] Template item clicked, loading template');
                loadTemplate(template.id);
            });

            container.append(item);
            console.log('✅ [BUILTIN EDITOR] Template added to container:', template.id);
            console.log('✅ [BUILTIN EDITOR] Template item HTML after append:', item.html());
            console.log('🔍 [BUILTIN EDITOR] .template-actions in DOM:', container.find('.template-actions').length, 'elements');
            console.log('🔍 [BUILTIN EDITOR] .template-edit-btn in DOM:', container.find('.template-edit-btn').length, 'elements');
            console.log('🔍 [BUILTIN EDITOR] .template-delete-btn in DOM:', container.find('.template-delete-btn').length, 'elements');
        });

        console.log('🎉 [BUILTIN EDITOR] All templates rendered, container has', container.children().length, 'children');
        console.log('📊 [BUILTIN EDITOR] Final DOM check:');
        console.log('   - .template-actions:', $('.template-actions').length);
        console.log('   - .template-edit-btn:', $('.template-edit-btn').length);
        console.log('   - .template-delete-btn:', $('.template-delete-btn').length);
        console.log('   - All .template-list-item:', $('.template-list-item').length);
        
        // Log CSS computed styles for debugging
        const firstEditBtn = $('.template-edit-btn').first();
        if (firstEditBtn.length) {
            const computed = window.getComputedStyle(firstEditBtn[0]);
            console.log('🎨 [BUILTIN EDITOR] First .template-edit-btn computed styles:');
            console.log('   - display:', computed.display);
            console.log('   - visibility:', computed.visibility);
            console.log('   - opacity:', computed.opacity);
            console.log('   - width:', computed.width);
            console.log('   - height:', computed.height);
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
        console.log('🎯 [BUILTIN EDITOR] Setting up event handlers...');
        
        // New template button
        $('#new-template-btn').on('click', function() {
            console.log('👆 [BUILTIN EDITOR] New template button clicked');
            showNewTemplateModal();
        });

        // Edit template parameters buttons
        $(document).on('click', '.template-edit-btn', function(e) {
            e.stopPropagation();
            const templateId = $(this).data('template-id');
            console.log('✏️ [BUILTIN EDITOR] Edit button clicked for template:', templateId);
            console.log('🔍 [BUILTIN EDITOR] Button element:', this);
            showEditTemplateModal(templateId);
        });

        // Delete template buttons
        $(document).on('click', '.template-delete-btn', function(e) {
            e.stopPropagation();
            const templateId = $(this).data('template-id');
            console.log('🗑️ [BUILTIN EDITOR] Delete button clicked for template:', templateId);
            console.log('🔍 [BUILTIN EDITOR] Button element:', this);
            if (confirm(pdfBuilderBuiltinEditor.strings.confirm_delete)) {
                deleteTemplate(templateId);
            }
        });

        // Update template confirm button
        $('#update-template-confirm').on('click', function() {
            console.log('💾 [BUILTIN EDITOR] Update template confirm clicked');
            updateTemplateParameters();
        });

        // Close edit modal events
        $('#edit-template-modal .pdf-modal-close, #edit-template-modal .pdf-modal-backdrop').on('click', function() {
            console.log('❌ [BUILTIN EDITOR] Edit modal close clicked');
            hideEditTemplateModal();
        });
        
        console.log('✅ [BUILTIN EDITOR] Event handlers setup completed');
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
        console.log('🔄 [BUILTIN EDITOR] Loading template modal for:', templateId);
        
        // Load template data using NEW action without nonce checks
        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_load_template_for_modal',
                template_id: templateId
            },
            success: function(response) {
                console.log('✅ [BUILTIN EDITOR] Template loaded:', response);
                if (response.success) {
                    const template = response.data.template;

                    // Fill form
                    $('#edit-template-id').val(templateId);
                    $('#edit-template-name').val(template.name || '');
                    $('#edit-template-description').val(template.description || '');
                    $('#edit-template-category').val(template.category || 'general');

                    console.log('📝 [BUILTIN EDITOR] Form filled with template data');
                    
                    // Show modal
                    $('#edit-template-modal').show();
                    console.log('👁️ [BUILTIN EDITOR] Modal shown');
                } else {
                    console.error('❌ [BUILTIN EDITOR] AJAX error:', response.data);
                    showError('Erreur lors du chargement du template: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ [BUILTIN EDITOR] AJAX request failed:', status, error, xhr.responseText);
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

        console.log('💾 [BUILTIN EDITOR] Updating template:', templateId);
        console.log('   - Name:', name);
        console.log('   - Description:', description);
        console.log('   - Category:', category);

        if (!name) {
            console.warn('⚠️ [BUILTIN EDITOR] Template name is required');
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
                console.log('✅ [BUILTIN EDITOR] Update response:', response);
                if (response.success) {
                    console.log('🎉 [BUILTIN EDITOR] Template updated successfully');
                    showSuccess('Paramètres mis à jour avec succès');
                    loadTemplatesList();
                    hideEditTemplateModal();
                } else {
                    console.error('❌ [BUILTIN EDITOR] Update error:', response.data);
                    showError('Erreur lors de la mise à jour: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ [BUILTIN EDITOR] Update AJAX failed:', status, error, xhr.responseText);
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
        console.log('🗑️ [BUILTIN EDITOR] Deleting template:', templateId);
        
        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_delete_builtin_template',
                template_id: templateId,
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                console.log('✅ [BUILTIN EDITOR] Delete response:', response);
                if (response.success) {
                    console.log('🎉 [BUILTIN EDITOR] Template deleted successfully');
                    showSuccess(pdfBuilderBuiltinEditor.strings.template_deleted);
                    // Reload the templates list
                    loadTemplatesList();
                } else {
                    console.error('❌ [BUILTIN EDITOR] Delete error:', response.data);
                    showError('Erreur lors de la suppression: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ [BUILTIN EDITOR] Delete AJAX failed:', status, error, xhr.responseText);
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