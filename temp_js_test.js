let currentTemplateId = null;

// Définir ajaxurl si pas déjà défini (nécessaire pour l'admin WordPress)
if (typeof ajaxurl === 'undefined') {
    ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
}
// console.log('ajaxurl défini comme:', ajaxurl);

// Définir le nonce pour les templates
var pdfBuilderTemplatesNonce = '<?php echo wp_create_nonce("pdf_builder_templates"); ?>';

function openTemplateSettings(templateId, templateName) {
    currentTemplateId = templateId;

    // Afficher la modale des paramètres
    document.getElementById('template-settings-modal').style.display = 'flex';

    // Pré-remplir les champs avec les données actuelles
    document.getElementById('template-name-input').value = templateName;

    // Charger les paramètres depuis la base de données via AJAX
    loadTemplateSettings(templateId);
}

function closeTemplateSettings() {
    document.getElementById('template-settings-modal').style.display = 'none';
    currentTemplateId = null;
}

function loadTemplateSettings(templateId) {
    // Simulation du chargement des paramètres (à remplacer par un vrai appel AJAX)
    // Pour l'instant, on met des valeurs par défaut
    document.getElementById('template-description-input').value = 'Description du template...';
    document.getElementById('template-public').checked = false;
    document.getElementById('template-paper-size').value = 'A4';
    document.getElementById('template-orientation').value = 'portrait';

    // Déterminer la catégorie basée sur le nom
    const templateName = document.getElementById('template-name-input').value.toLowerCase();
    let category = 'autre';
    if (templateName.includes('facture')) category = 'facture';
    else if (templateName.includes('devis')) category = 'devis';
    else if (templateName.includes('commande')) category = 'commande';
    else if (templateName.includes('contrat')) category = 'contrat';
    else if (templateName.includes('newsletter')) category = 'newsletter';

    document.getElementById('template-category').value = category;
}

function saveTemplateSettings() {
    const settings = {
        template_id: currentTemplateId,
        name: document.getElementById('template-name-input').value,
        description: document.getElementById('template-description-input').value,
        is_public: document.getElementById('template-public').checked ? 1 : 0,
        paper_size: document.getElementById('template-paper-size').value,
        orientation: document.getElementById('template-orientation').value,
        category: document.getElementById('template-category').value
    };

    // Afficher un indicateur de chargement
    const saveButton = document.querySelector('.template-modal-footer .button-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '⏳ Sauvegarde...';
    saveButton.disabled = true;

    // Faire l'appel AJAX
    jQuery.post(ajaxurl, {
        action: 'pdf_builder_save_template_settings',
        nonce: pdfBuilderTemplatesNonce,
        ...settings
    }, function(response) {
        if (response.success) {
            // Afficher un message de succès
            saveButton.innerHTML = '✅ Enregistré !';
            saveButton.style.background = '#28a745';

            // Fermer la modale après un délai
            setTimeout(() => {
                closeTemplateSettings();
                // Recharger la page pour voir les changements
                location.reload();
            }, 1500);
        } else {
            // Erreur
            saveButton.innerHTML = '❌ Erreur';
            saveButton.style.background = '#dc3545';
            alert('Erreur lors de la sauvegarde: ' + ((response.data && response.data.message) || 'Erreur inconnue'));

            // Remettre le bouton normal après un délai
            setTimeout(() => {
                saveButton.innerHTML = originalText;
                saveButton.style.background = '';
                saveButton.disabled = false;
            }, 3000);
        }
    }).fail(function(xhr, status, error) {
        // Erreur de réseau
        saveButton.innerHTML = '❌ Erreur réseau';
        saveButton.style.background = '#dc3545';
        alert('Erreur de connexion: ' + error);

        // Remettre le bouton normal
        setTimeout(() => {
            saveButton.innerHTML = originalText;
            saveButton.style.background = '';
            saveButton.disabled = false;
        }, 3000);
    });
}

function confirmDeleteTemplate(templateId, templateName) {
    if (confirm('Êtes-vous sûr de vouloir supprimer le template "' + templateName + '" ?\n\n⚠️ Cette action est irréversible.')) {
        deleteTemplate(templateId, templateName);
    }
}

function deleteTemplate(templateId, templateName) {
    // Simulation de la suppression (à remplacer par un vrai appel AJAX)
// Suppression du template

    // Afficher un message de succès temporaire
    alert('✅ Template "' + templateName + '" supprimé avec succès !');

    // Recharger la page pour voir les changements
    location.reload();
}

function toggleDefaultTemplate(templateId, templateType, templateName) {
// Toggle default template

    // Trouver l'icône du template actuel en utilisant l'attribut onclick
    const currentIcon = Array.from(document.querySelectorAll('.default-template-icon')).find(icon =>
        icon.onclick && icon.onclick.toString().includes(`toggleDefaultTemplate(${templateId}`)
    );
    const isCurrentlyDefault = currentIcon && currentIcon.style.opacity === '1';

    // Current icon found

    // Préparer les données pour AJAX
    const data = {
        action: 'pdf_builder_set_default_template',
        template_id: templateId,
        is_default: isCurrentlyDefault ? 0 : 1,
        nonce: pdfBuilderTemplatesNonce
    };

    // AJAX data
    // ajaxurl

    // Afficher un indicateur de chargement
    if (currentIcon) {
        currentIcon.style.pointerEvents = 'none';
        currentIcon.innerHTML = '⏳';
    }

    // Faire l'appel AJAX
    jQuery.post(ajaxurl, data, function(response) {
        // AJAX success
        if (response.success) {
            // Mettre à jour l'icône et le titre
            if (currentIcon) {
                const newIsDefault = !isCurrentlyDefault;
                currentIcon.innerHTML = newIsDefault ? '⭐' : '☆';
                currentIcon.style.opacity = newIsDefault ? '1' : '0.5';
                currentIcon.title = newIsDefault ? 'Template par défaut' : 'Définir comme template par défaut';
                currentIcon.style.pointerEvents = 'auto';
            }

            // Afficher un message de succès temporaire
            showSuccessMessage(response.data.message);

            // Mettre à jour les autres icônes pour retirer le statut par défaut (seulement du même type)
            if (!isCurrentlyDefault) {
                document.querySelectorAll('.default-template-icon').forEach(icon => {
                    // Vérifier si cette icône appartient à un template du même type
                    if (icon !== currentIcon && icon.onclick && icon.onclick.toString().includes(`'${templateType}'`)) {
                        icon.innerHTML = '☆';
                        icon.style.opacity = '0.5';
                        icon.title = 'Définir comme template par défaut';
                    }
                });
            }
        } else {
            // Erreur - garder l'icône décochée si c'était une tentative de définition par défaut
            if (currentIcon) {
                currentIcon.innerHTML = '☆';
                currentIcon.style.opacity = '0.5';
                currentIcon.title = 'Définir comme template par défaut';
                currentIcon.style.pointerEvents = 'auto';
            }
            showErrorMessage((response.data && response.data.message) || 'Erreur lors de la modification du statut par défaut');
        }
    }).fail(function(xhr, status, error) {
        // AJAX failed
        // Erreur de réseau
        if (currentIcon) {
            currentIcon.innerHTML = isCurrentlyDefault ? '*' : '-';
            currentIcon.style.pointerEvents = 'auto';
        }
        showErrorMessage('Erreur de connexion: ' + error);
    });
}

// Fonction pour afficher un message de succès temporaire
function showSuccessMessage(message) {
    // Supprimer les anciens messages
    const existingMessages = document.querySelectorAll('.pdf-builder-message');
    existingMessages.forEach(msg => msg.remove());

    // Créer le nouveau message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'pdf-builder-message success';
    messageDiv.style.cssText = `
        position: fixed;
        top: 40px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        z-index: 100;
        font-weight: bold;
        max-width: 400px;
    `;
    messageDiv.innerHTML = '✅ ' + message;

    document.body.appendChild(messageDiv);

    // Faire disparaître le message après 3 secondes
    setTimeout(() => {
        messageDiv.style.transition = 'opacity 0.5s';
        messageDiv.style.opacity = '0';
        setTimeout(() => messageDiv.remove(), 500);
    }, 3000);
}

// Fonction pour afficher un message d'erreur temporaire
function showErrorMessage(message) {
    // Supprimer les anciens messages
    const existingMessages = document.querySelectorAll('.pdf-builder-message');
    existingMessages.forEach(msg => msg.remove());

    // Créer le nouveau message
    const messageDiv = document.createElement('div');
    messageDiv.className = 'pdf-builder-message error';
    messageDiv.style.cssText = `
        position: fixed;
        top: 40px;
        right: 20px;
        background: #dc3545;
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        z-index: 100;
        font-weight: bold;
        max-width: 400px;
    `;
    messageDiv.innerHTML = '❌ ' + message;

    document.body.appendChild(messageDiv);

    // Faire disparaître le message après 5 secondes
    setTimeout(() => {
        messageDiv.style.transition = 'opacity 0.5s';
        messageDiv.style.opacity = '0';
        setTimeout(() => messageDiv.remove(), 500);
    }, 5000);
}

// Fonction de filtrage des templates
function filterTemplates(filterType) {
    const cards = document.querySelectorAll('.template-card');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    // Mettre à jour les boutons actifs
    filterButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-filter') === filterType) {
            btn.classList.add('active');
        }
    });
    
    // Filtrer les cartes
    cards.forEach(card => {
        if (filterType === 'all') {
            card.style.display = 'block';
        } else {
            if (card.classList.contains('template-type-' + filterType)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        }
    });
}

// Fonction pour installer un template builtin
function installBuiltinTemplate(templateName, displayName) {
    if (confirm('Voulez-vous installer le template "' + displayName + '" ?\n\nUne copie personnalisable sera créée dans vos templates.')) {
        // Afficher un indicateur de chargement
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Installation...';
        button.disabled = true;

        // Faire l'appel AJAX
        jQuery.post(ajaxurl, {
            action: 'pdf_builder_install_builtin_template',
            template_name: templateName,
            custom_name: displayName + ' (Copie)',
            nonce: pdfBuilderTemplatesNonce
        }, function(response) {
            if (response.success) {
                // Succès
                button.innerHTML = '✅ Installé !';
                button.style.background = '#28a745';

                // Afficher un message de succès
                showSuccessMessage('Template "' + displayName + '" installé avec succès !');

                // Recharger la page après un délai
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                // Erreur
                button.innerHTML = '❌ Erreur';
                button.style.background = '#dc3545';
                showErrorMessage((response.data && response.data.message) || 'Erreur lors de l\'installation du template');

                // Remettre le bouton normal
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.background = '';
                    button.disabled = false;
                }, 3000);
            }
        }).fail(function(xhr, status, error) {
            // Erreur de réseau
            button.innerHTML = '❌ Erreur réseau';
            button.style.background = '#dc3545';
            showErrorMessage('Erreur de connexion: ' + error);

            // Remettre le bouton normal
            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
                button.disabled = false;
            }, 3000);
        });
    }
}

// Fonction pour prévisualiser un template builtin
function previewBuiltinTemplate(templateName) {
    // Ouvrir une nouvelle fenêtre/onglet avec l'aperçu
    const previewUrl = '<?php echo esc_js(admin_url('admin.php?page=pdf-builder-preview&template=')); ?>' + encodeURIComponent(templateName) + '&type=builtin';
    window.open(previewUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
}

// Initialiser le filtrage au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter les event listeners aux boutons de filtrage
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const filterType = this.getAttribute('data-filter');
            filterTemplates(filterType);
        });
    });

    // Template Gallery Modal Functionality
    let currentFilter = 'all';
    let loadedTemplates = [];

    // Open gallery modal
    jQuery('#open-template-gallery').on('click', function(e) {
        e.preventDefault();
        jQuery('.template-gallery-modal').fadeIn(300);
        loadTemplates();
    });

    // Close gallery modal
    jQuery('.template-gallery-close, .template-gallery-overlay').on('click', function() {
        jQuery('.template-gallery-modal').fadeOut(300);
    });

    // Filter templates
    jQuery('.gallery-filter').on('click', function() {
        jQuery('.gallery-filter').removeClass('active');
        jQuery(this).addClass('active');
        currentFilter = jQuery(this).data('category');
        filterTemplates();
    });

    // Load templates via AJAX
    function loadTemplates() {
        jQuery('.template-gallery-grid').html('<div class="template-gallery-loading">Chargement des modèles...</div>');

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_builtin_templates',
                nonce: pdfBuilderTemplatesNonce
            },
            success: function(response) {
                if (response.success) {
                    loadedTemplates = response.data.templates;
                    renderTemplates(loadedTemplates);
                } else {
                    jQuery('.template-gallery-grid').html('<div class="notice notice-error"><p>Erreur lors du chargement des modèles.</p></div>');
                }
            },
            error: function() {
                jQuery('.template-gallery-grid').html('<div class="notice notice-error"><p>Erreur de connexion.</p></div>');
            }
        });
    }

    // Render templates in grid
    function renderTemplates(templates) {
        let html = '';

        templates.forEach(function(template, index) {
            const features = template.features || [];
            const featuresHtml = features.map(feature =>
                `<span class="template-gallery-item-feature">${feature}</span>`
            ).join('');

            html += `
                <div class="template-gallery-item" data-category="${template.category || 'general'}" style="animation-delay: ${index * 0.1}s">
                    <div class="template-gallery-item-preview">
                        <img src="${template.preview_url}" alt="${template.name}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="preview-placeholder" style="display: none;">${template.name.charAt(0).toUpperCase()}</div>
                    </div>
                    <div class="template-gallery-item-info">
                        <h3 class="template-gallery-item-title">${template.name}</h3>
                        <p class="template-gallery-item-description">${template.description}</p>
                        <div class="template-gallery-item-features">
                            ${featuresHtml}
                        </div>
                        <div class="template-gallery-item-actions">
                            <button class="button button-primary install-template" data-template-id="${template.id}">
                                Installer
                            </button>
                            <button class="button button-secondary preview-template" data-template-id="${template.id}">
                                Aperçu
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        jQuery('.template-gallery-grid').html(html);
        filterTemplates();
    }

    // Filter templates based on current filter
    function filterTemplates() {
        if (currentFilter === 'all') {
            jQuery('.template-gallery-item').show();
        } else {
            jQuery('.template-gallery-item').hide();
            jQuery(`.template-gallery-item[data-category="${currentFilter}"]`).show();
        }
    }

    // Install template
    jQuery(document).on('click', '.install-template', function() {
        const templateId = jQuery(this).data('template-id');
        const $button = jQuery(this);

        $button.prop('disabled', true).text('Installation...');

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'install_builtin_template',
                template_id: templateId,
                nonce: pdfBuilderTemplatesNonce
            },
            success: function(response) {
                if (response.success) {
                    $button.removeClass('button-primary').addClass('button-success').text('Installé ✓');
                    showNotice('Modèle installé avec succès!', 'success');

                    // Refresh the templates list
                    if (typeof loadTemplatesList === 'function') {
                        loadTemplatesList();
                    }
                } else {
                    $button.prop('disabled', false).text('Installer');
                    showNotice(response.data.message || 'Erreur lors de l\'installation.', 'error');
                }
            },
            error: function() {
                $button.prop('disabled', false).text('Installer');
                showNotice('Erreur de connexion.', 'error');
            }
        });
    });

    // Preview template
    jQuery(document).on('click', '.preview-template', function() {
        const templateId = jQuery(this).data('template-id');
        const template = loadedTemplates.find(t => t.id === templateId);

        if (template && template.preview_url) {
            window.open(template.preview_url, '_blank');
        }
    });

    // Utility function to show notices
    function showNotice(message, type = 'info') {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const $notice = jQuery(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);

        jQuery('.wp-header-end').after($notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                jQuery(this).remove();
            });
        }, 5000);
    }

    // Function to close template gallery (for HTML onclick)
    function closeTemplateGallery() {
        jQuery('.template-gallery-modal').fadeOut(300);
    }
});

// Fermer les modales en cliquant en dehors
document.getElementById('template-settings-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTemplateSettings();
    }
});

// Fermer avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('template-settings-modal').style.display === 'flex') {
            closeTemplateSettings();
        }
    }
});
</script>
