/**
 * PDF Builder Pro - Builtin Templates Editor JavaScript
 */

(function($) {
    'use strict';

    let currentTemplate = null;
    let jsonEditor = null;
    let isDirty = false;

    $(document).ready(function() {
        initializeEditor();
        loadTemplatesList();
        setupEventHandlers();
    });

    /**
     * Initialize the JSON editor
     */
    function initializeEditor() {
        // Create Monaco editor container
        const container = document.getElementById('json-editor');

        // For now, use a simple textarea with JSON validation
        // TODO: Replace with Monaco editor when available
        const textarea = document.createElement('textarea');
        textarea.id = 'json-textarea';
        textarea.style.width = '100%';
        textarea.style.height = '100%';
        textarea.style.border = 'none';
        textarea.style.outline = 'none';
        textarea.style.fontFamily = 'Monaco, Menlo, "Ubuntu Mono", monospace';
        textarea.style.fontSize = '12px';
        textarea.style.lineHeight = '1.4';
        textarea.style.resize = 'none';
        textarea.style.padding = '10px';
        textarea.placeholder = '{\n  "name": "Mon Template",\n  "elements": []\n}';

        container.appendChild(textarea);

        // Store reference
        jsonEditor = textarea;

        // Add input event listener for validation
        $(textarea).on('input', function() {
            validateJSON();
            isDirty = true;
            updateSaveButton();
        });
    }

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
     * Load a specific template
     */
    function loadTemplate(templateId) {
        // Don't load if already loading or if dirty
        if (isDirty && !confirm('Vous avez des modifications non sauvegardées. Voulez-vous continuer ?')) {
            return;
        }

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
                    currentTemplate = response.data.template_id;
                    jsonEditor.value = response.data.content;
                    validateJSON();
                    updateUI();
                    renderPreview();
                    isDirty = false;
                    updateSaveButton();
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
     * Save the current template
     */
    function saveTemplate() {
        if (!currentTemplate) {
            showError('Aucun template sélectionné');
            return;
        }

        const content = jsonEditor.value;

        $('#save-template-btn').prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> ' + pdfBuilderBuiltinEditor.strings.saving);

        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_save_builtin_template',
                template_id: currentTemplate,
                content: content,
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(pdfBuilderBuiltinEditor.strings.saved);
                    isDirty = false;
                    updateSaveButton();
                    // Reload templates list to reflect changes
                    loadTemplatesList();
                } else {
                    showError('Erreur lors de la sauvegarde: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function() {
                showError('Erreur de connexion lors de la sauvegarde');
            },
            complete: function() {
                $('#save-template-btn').prop('disabled', false).html('<span class="dashicons dashicons-save"></span> ' + pdfBuilderBuiltinEditor.strings.save);
            }
        });
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
                    currentTemplate = response.data.template_id;
                    jsonEditor.value = JSON.stringify(response.data.template, null, 2);
                    validateJSON();
                    updateUI();
                    renderPreview();
                    isDirty = false;
                    updateSaveButton();
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
     * Delete the current template
     */
    function deleteTemplate() {
        if (!currentTemplate) {
            showError('Aucun template sélectionné');
            return;
        }

        if (!confirm(pdfBuilderBuiltinEditor.strings.confirm_delete)) {
            return;
        }

        $.ajax({
            url: pdfBuilderBuiltinEditor.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_delete_builtin_template',
                template_id: currentTemplate,
                nonce: pdfBuilderBuiltinEditor.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(pdfBuilderBuiltinEditor.strings.template_deleted);
                    currentTemplate = null;
                    jsonEditor.value = '';
                    updateUI();
                    renderPreview();
                    isDirty = false;
                    updateSaveButton();
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
     * Validate JSON content
     */
    function validateJSON() {
        const content = jsonEditor.value;
        const statusElement = $('#json-status');

        try {
            JSON.parse(content);
            statusElement.removeClass('json-status-invalid').addClass('json-status-valid');
            statusElement.html('<span class="dashicons dashicons-yes"></span> ' + 'JSON valide');
            return true;
        } catch (e) {
            statusElement.removeClass('json-status-valid').addClass('json-status-invalid');
            statusElement.html('<span class="dashicons dashicons-no"></span> ' + pdfBuilderBuiltinEditor.strings.invalid_json + ': ' + e.message);
            return false;
        }
    }

    /**
     * Render template preview
     */
    function renderPreview() {
        const previewContainer = $('#template-preview');
        previewContainer.empty();

        if (!jsonEditor.value) {
            previewContainer.append('<p style="color: #666; text-align: center; padding: 50px;">Aucun template chargé</p>');
            return;
        }

        try {
            const templateData = JSON.parse(jsonEditor.value);

            if (!templateData.elements || !Array.isArray(templateData.elements)) {
                previewContainer.append('<p style="color: #666; text-align: center; padding: 50px;">Template invalide</p>');
                return;
            }

            // Render elements
            templateData.elements.forEach(function(element) {
                const elementDiv = createElementPreview(element);
                if (elementDiv) {
                    previewContainer.append(elementDiv);
                }
            });

        } catch (e) {
            previewContainer.append('<p style="color: #dc3232; text-align: center; padding: 50px;">Erreur JSON: ' + e.message + '</p>');
        }
    }

    /**
     * Create preview element
     */
    function createElementPreview(element) {
        const div = document.createElement('div');
        div.style.position = 'absolute';
        div.style.left = element.x + 'px';
        div.style.top = element.y + 'px';
        div.style.width = element.width + 'px';
        div.style.height = element.height + 'px';
        div.style.border = '1px solid #007cba';
        div.style.background = 'rgba(0, 124, 186, 0.1)';

        // Element-specific styling
        if (element.type === 'text' && element.properties) {
            div.style.fontSize = (element.properties.fontSize || 14) * 0.3 + 'px';
            div.style.fontWeight = element.properties.fontWeight || 'normal';
            div.style.color = element.properties.color || '#000';
            div.style.display = 'flex';
            div.style.alignItems = 'center';
            div.style.justifyContent = element.properties.textAlign === 'right' ? 'flex-end' :
                                      element.properties.textAlign === 'center' ? 'center' : 'flex-start';
            div.style.padding = '2px';
            div.innerText = element.properties.text || 'Texte';
            div.style.whiteSpace = 'nowrap';
            div.style.overflow = 'hidden';
        } else if (element.type === 'line') {
            div.style.background = element.properties.strokeColor || '#000';
            div.style.border = 'none';
        } else {
            // Generic element
            div.innerText = element.type || 'Élément';
            div.style.display = 'flex';
            div.style.alignItems = 'center';
            div.style.justifyContent = 'center';
            div.style.fontSize = '8px';
            div.style.color = '#666';
        }

        return div;
    }

    /**
     * Update UI elements
     */
    function updateUI() {
        const templateName = $('#current-template-name');

        if (currentTemplate) {
            templateName.text(currentTemplate);
            $('#delete-template-btn').prop('disabled', false);
            $('.template-list-item').removeClass('selected');
            $(`.template-list-item[data-template-id="${currentTemplate}"]`).addClass('selected');
        } else {
            templateName.text('Aucun template sélectionné');
            $('#delete-template-btn').prop('disabled', true);
            $('.template-list-item').removeClass('selected');
        }
    }

    /**
     * Update save button state
     */
    function updateSaveButton() {
        const saveBtn = $('#save-template-btn');
        if (isDirty && currentTemplate) {
            saveBtn.removeClass('button-secondary').addClass('button-primary');
        } else {
            saveBtn.removeClass('button-primary').addClass('button-secondary');
        }
    }

    /**
     * Setup event handlers
     */
    function setupEventHandlers() {
        // Save button
        $('#save-template-btn').on('click', function() {
            if (validateJSON()) {
                saveTemplate();
            } else {
                showError(pdfBuilderBuiltinEditor.strings.invalid_json);
            }
        });

        // New template button
        $('#new-template-btn').on('click', function() {
            showNewTemplateModal();
        });

        // Delete template button
        $('#delete-template-btn').on('click', function() {
            deleteTemplate();
        });

        // JSON editor changes trigger preview update
        $(jsonEditor).on('input', function() {
            if (validateJSON()) {
                renderPreview();
            }
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