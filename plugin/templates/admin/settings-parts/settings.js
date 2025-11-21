/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 * Updated: 2025-11-21 22:35:00 - Solution finale modales canvas
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour mettre à jour les checkboxes du formulaire avec les nouvelles valeurs
    function updateFormCheckboxes(settings) {

        // Mapping des paramètres vers les IDs des checkboxes
        const checkboxMappings = {
            'shadow_enabled': 'canvas_shadow_enabled',
            'show_grid': 'canvas_grid_enabled',
            'show_guides': 'canvas_guides_enabled',
            'snap_to_grid': 'canvas_snap_to_grid',
            'zoom_with_wheel': 'zoom_with_wheel',
            'show_resize_handles': 'canvas_resize_enabled',
            'enable_rotation': 'canvas_rotate_enabled',
            'multi_select': 'canvas_multi_select',
            'enable_keyboard_shortcuts': 'canvas_keyboard_shortcuts',
            'auto_save_enabled': 'canvas_auto_save',
            'debug_enabled': 'canvas_debug_enabled',
            // Paramètres de performance
            'lazy_loading_editor': 'canvas_lazy_loading_editor',
            'preload_critical': 'canvas_preload_critical',
            'lazy_loading_plugin': 'canvas_lazy_loading_plugin'
        };

        // Mettre à jour chaque checkbox
        Object.keys(checkboxMappings).forEach(settingKey => {
            const checkboxId = checkboxMappings[settingKey];
            const checkbox = document.getElementById(checkboxId);

            if (checkbox && settings[settingKey] !== undefined) {
                const shouldBeChecked = settings[settingKey] === true || settings[settingKey] === '1';
                const parentElement = checkbox.parentElement;

                // Mettre à jour l'état de la checkbox
                checkbox.checked = shouldBeChecked;

                // Mettre à jour les attributs et classes
                if (shouldBeChecked) {
                    checkbox.setAttribute('checked', 'checked');
                    parentElement.classList.add('checked');
                } else {
                    checkbox.removeAttribute('checked');
                    parentElement.classList.remove('checked');
                }
            }
        });
    }

    // === GESTION DES CARTES CANVAS (ouverture des modales) ===
    const canvasCards = document.querySelectorAll('.canvas-card');
    canvasCards.forEach(function(card) {
        card.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const modalId = 'canvas-' + category + '-modal';
            const modal = document.getElementById(modalId);

            if (modal) {
                // SOLUTION : Recréer complètement la modale canvas avec des styles inline
                const newModal = document.createElement('div');
                newModal.id = modalId;
                newModal.className = 'canvas-modal';
                newModal.innerHTML = `
                    <div class="canvas-modal-overlay" style="
                        position: fixed !important;
                        top: 0 !important;
                        left: 0 !important;
                        width: 100% !important;
                        height: 100% !important;
                        background: rgba(0,0,0,0.8) !important;
                        z-index: 1000000 !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                    ">
                        <div class="canvas-modal-content" style="
                            background: white !important;
                            border-radius: 8px !important;
                            padding: 20px !important;
                            max-width: 600px !important;
                            width: 90% !important;
                            max-height: 85vh !important;
                            overflow: hidden !important;
                            box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
                            border: 1px solid #e1e1e1 !important;
                            position: relative !important;
                            z-index: 1000001 !important;
                        ">
                            <div class="canvas-modal-header" style="
                                display: flex !important;
                                justify-content: space-between !important;
                                align-items: center !important;
                                margin-bottom: 25px !important;
                                border-bottom: 1px solid #dee2e6 !important;
                                padding-bottom: 15px !important;
                            ">
                                <h3 style="margin: 0 !important; color: #495057 !important;">📋 Configuration ${category}</h3>
                                <button type="button" class="canvas-modal-close" style="
                                    background: none !important;
                                    border: none !important;
                                    font-size: 24px !important;
                                    cursor: pointer !important;
                                    color: #6c757d !important;
                                ">&times;</button>
                            </div>
                            <div class="canvas-modal-body" style="
                                margin-bottom: 20px !important;
                                padding: 15px !important;
                                background: #f8f9fa !important;
                                border-radius: 8px !important;
                                border-left: 4px solid #007cba !important;
                            ">
                                <p style="margin: 0 !important; font-size: 14px !important; color: #495057 !important; line-height: 1.5 !important;">
                                    <strong>💡 Configuration ${category}</strong><br>
                                    Cette section permet de configurer les paramètres ${category} du canvas.
                                </p>
                            </div>
                            <div class="canvas-modal-footer" style="
                                display: flex !important;
                                justify-content: flex-end !important;
                                gap: 10px !important;
                                padding-top: 15px !important;
                                border-top: 1px solid #dee2e6 !important;
                            ">
                                <button type="button" class="canvas-modal-cancel" style="
                                    padding: 8px 16px !important;
                                    background: #6c757d !important;
                                    color: white !important;
                                    border: none !important;
                                    border-radius: 4px !important;
                                    cursor: pointer !important;
                                ">Annuler</button>
                                <button type="button" class="canvas-modal-save" style="
                                    padding: 8px 16px !important;
                                    background: #007cba !important;
                                    color: white !important;
                                    border: none !important;
                                    border-radius: 4px !important;
                                    cursor: pointer !important;
                                ">Sauvegarder</button>
                            </div>
                        </div>
                    </div>
                `;

                // Remplacer l'ancienne modale
                modal.parentNode.replaceChild(newModal, modal);

                // Ajouter les event listeners
                const closeButtons = newModal.querySelectorAll('.canvas-modal-close, .canvas-modal-cancel');
                closeButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        newModal.remove();
                        document.body.style.overflow = '';
                    });
                });

                // Fermeture en cliquant sur l'overlay
                const overlay = newModal.querySelector('.canvas-modal-overlay');
                overlay.addEventListener('click', function(event) {
                    if (event.target === overlay) {
                        newModal.remove();
                        document.body.style.overflow = '';
                    }
                });

                document.body.style.overflow = 'hidden';
            } else {
                console.error('Modal not found for category:', category, 'Expected ID:', modalId);
            }
        });
    });

    // Fonction pour gérer la sauvegarde AJAX des paramètres canvas
    function saveCanvasSettings(category, formData) {
        // Afficher un indicateur de chargement
        const saveButton = document.querySelector('.canvas-modal-save');
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.textContent = 'Sauvegarde...';
        }

        // Préparer les données AJAX
        const data = new FormData();
        data.append('action', 'pdf_builder_save_settings');
        data.append('nonce', window.pdfBuilderSettings?.nonce || '');

        // Ajouter les données du formulaire
        for (let [key, value] of formData.entries()) {
            data.append(key, value);
        }

        // Ajouter le contexte canvas
        data.append('context', 'canvas_' + category);

        // Envoyer la requête AJAX
        fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: data,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Fermer la modale
                const modal = document.getElementById('canvas-' + category + '-modal');
                if (modal) {
                    modal.remove();
                    document.body.style.overflow = '';
                }

                // Afficher un message de succès
                showAdminNotice('Paramètres sauvegardés avec succès !', 'success');
            } else {
                throw new Error(result.data?.message || 'Erreur lors de la sauvegarde');
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            showAdminNotice('Erreur lors de la sauvegarde: ' + error.message, 'error');
        })
        .finally(() => {
            // Restaurer le bouton
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.textContent = 'Sauvegarder';
            }
        });
    }

    // Fonction pour afficher les notifications admin
    function showAdminNotice(message, type = 'info') {
        // Créer la notification
        const notice = document.createElement('div');
        notice.className = `notice notice-${type} is-dismissible`;
        notice.innerHTML = `
            <p>${message}</p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Fermer cette notification.</span>
            </button>
        `;

        // Ajouter au conteneur de notifications
        const noticesContainer = document.querySelector('.wp-header-end') ||
                                document.querySelector('#wpbody-content > .wrap > h1')?.nextElementSibling ||
                                document.querySelector('#wpbody-content > .wrap');

        if (noticesContainer) {
            noticesContainer.insertAdjacentElement('afterbegin', notice);
        }

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (notice.parentNode) {
                notice.remove();
            }
        }, 5000);

        // Gestionnaire de fermeture
        const dismissBtn = notice.querySelector('.notice-dismiss');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', () => notice.remove());
        }
    }

    // Gestionnaire pour les boutons de sauvegarde des modales
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('canvas-modal-save')) {
            event.preventDefault();

            // Trouver la modale parente
            const modal = event.target.closest('.canvas-modal');
            if (!modal) return;

            // Extraire la catégorie depuis l'ID de la modale
            const modalId = modal.id;
            const categoryMatch = modalId.match(/^canvas-(.+)-modal$/);
            if (!categoryMatch) return;

            const category = categoryMatch[1];

            // Trouver le formulaire dans la modale
            const form = modal.querySelector('form');
            if (form) {
                const formData = new FormData(form);
                saveCanvasSettings(category, formData);
            } else {
                // Si pas de formulaire, sauvegarder avec des données vides pour cette catégorie
                const formData = new FormData();
                saveCanvasSettings(category, formData);
            }
        }
    });

    // === GESTION DES ACCORDEONS ===
    const accordions = document.querySelectorAll('.accordion-header');
    accordions.forEach(function(header) {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            // Fermer tous les autres accordéons du même groupe
            const group = this.closest('.accordion-group');
            if (group) {
                const siblings = group.querySelectorAll('.accordion-header');
                siblings.forEach(function(sibling) {
                    if (sibling !== this) {
                        sibling.setAttribute('aria-expanded', 'false');
                        const siblingContent = sibling.nextElementSibling;
                        if (siblingContent) {
                            siblingContent.style.display = 'none';
                        }
                    }
                }.bind(this));
            }

            // Basculer l'état de cet accordéon
            this.setAttribute('aria-expanded', !isExpanded);
            if (content) {
                content.style.display = isExpanded ? 'none' : 'block';
            }
        });
    });

    // === GESTION DES ONGLETS ===
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            const tabContainer = this.closest('.tabs-container');

            if (tabContainer) {
                // Masquer tous les contenus d'onglets
                const tabContents = tabContainer.querySelectorAll('.tab-content');
                tabContents.forEach(function(content) {
                    content.style.display = 'none';
                });

                // Désactiver tous les boutons d'onglets
                const buttons = tabContainer.querySelectorAll('.tab-button');
                buttons.forEach(function(btn) {
                    btn.classList.remove('active');
                });

                // Afficher le contenu de l'onglet sélectionné
                const selectedContent = tabContainer.querySelector('#' + tabId);
                if (selectedContent) {
                    selectedContent.style.display = 'block';
                }

                // Activer le bouton sélectionné
                this.classList.add('active');
            }
        });
    });

    // === GESTION DES CHECKBOXES À TROIS ÉTATS ===
    const triStateCheckboxes = document.querySelectorAll('.tri-state-checkbox');
    triStateCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('click', function() {
            const currentState = this.getAttribute('data-state') || 'unchecked';
            let nextState;

            switch (currentState) {
                case 'unchecked':
                    nextState = 'checked';
                    break;
                case 'checked':
                    nextState = 'indeterminate';
                    break;
                case 'indeterminate':
                    nextState = 'unchecked';
                    break;
                default:
                    nextState = 'checked';
            }

            this.setAttribute('data-state', nextState);
            this.checked = nextState === 'checked';

            // Mettre à jour l'apparence
            this.classList.remove('unchecked', 'checked', 'indeterminate');
            this.classList.add(nextState);
        });
    });

    // === VALIDATION DES FORMULAIRES ===
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            const isValid = validateForm(this);

            if (!isValid) {
                event.preventDefault();
                showAdminNotice('Veuillez corriger les erreurs dans le formulaire.', 'error');
            }
        });
    });

    // Fonction de validation de formulaire
    function validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        });

        return isValid;
    }

    // === GESTION DU DRAG & DROP ===
    const dropZones = document.querySelectorAll('.drop-zone');
    dropZones.forEach(function(zone) {
        zone.addEventListener('dragover', function(event) {
            event.preventDefault();
            this.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', function(event) {
            event.preventDefault();
            this.classList.remove('drag-over');
        });

        zone.addEventListener('drop', function(event) {
            event.preventDefault();
            this.classList.remove('drag-over');

            const files = event.dataTransfer.files;
            handleFileDrop(this, files);
        });
    });

    // Fonction de gestion du drop de fichiers
    function handleFileDrop(dropZone, files) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Vérifier le type de fichier si nécessaire
            if (dropZone.hasAttribute('data-accept')) {
                const acceptedTypes = dropZone.getAttribute('data-accept').split(',');
                if (!acceptedTypes.some(type => file.type.match(type.trim()))) {
                    showAdminNotice(`Type de fichier non accepté: ${file.type}`, 'error');
                    continue;
                }
            }

            // Traiter le fichier
            const reader = new FileReader();
            reader.onload = function(event) {
                const result = event.target.result;
                dropZone.innerHTML = `<p>Fichier chargé: ${file.name}</p>`;
                dropZone.setAttribute('data-file-data', result);
            };
            reader.readAsDataURL(file);
        }
    }

    // === GESTION DES INFOBULLes ===
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(function(element) {
        element.addEventListener('mouseenter', function() {
            showTooltip(this, this.getAttribute('data-tooltip'));
        });

        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });

    // Fonction d'affichage des infobulles
    function showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;

        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    }

    // Fonction de masquage des infobulles
    function hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    // === GESTION DES SLIDERS ===
    const sliders = document.querySelectorAll('.custom-slider');
    sliders.forEach(function(slider) {
        const input = slider.querySelector('input[type="range"]');
        const valueDisplay = slider.querySelector('.slider-value');

        if (input && valueDisplay) {
            input.addEventListener('input', function() {
                valueDisplay.textContent = this.value;
            });

            // Valeur initiale
            valueDisplay.textContent = input.value;
        }
    });

    // === GESTION DES COULEURS ===
    const colorPickers = document.querySelectorAll('.color-picker');
    colorPickers.forEach(function(picker) {
        const input = picker.querySelector('input[type="color"]');
        const preview = picker.querySelector('.color-preview');

        if (input && preview) {
            input.addEventListener('input', function() {
                preview.style.backgroundColor = this.value;
            });

            // Couleur initiale
            preview.style.backgroundColor = input.value;
        }
    });

    // === GESTION DES REQUÊTES AJAX ===
    const ajaxButtons = document.querySelectorAll('[data-ajax-action]');
    ajaxButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const action = this.getAttribute('data-ajax-action');
            const data = this.getAttribute('data-ajax-data') || '{}';

            performAjaxAction(action, JSON.parse(data), this);
        });
    });

    // Fonction d'exécution des actions AJAX
    function performAjaxAction(action, data, button) {
        // Désactiver le bouton
        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = 'Chargement...';

        // Préparer les données
        const ajaxData = {
            action: action,
            nonce: window.pdfBuilderSettings?.nonce || '',
            ...data
        };

        // Envoyer la requête
        fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(ajaxData),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showAdminNotice(result.data?.message || 'Action exécutée avec succès', 'success');
            } else {
                throw new Error(result.data?.message || 'Erreur lors de l\'action');
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            showAdminNotice('Erreur: ' + error.message, 'error');
        })
        .finally(() => {
            // Restaurer le bouton
            button.disabled = false;
            button.textContent = originalText;
        });
    }

    // === GESTION DES CONFIRMATIONS ===
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // === INITIALISATION ===
    console.log('PDF Builder Pro settings page initialized');

    // Initialiser les accordéons (premier ouvert par défaut)
    const firstAccordion = document.querySelector('.accordion-header');
    if (firstAccordion) {
        firstAccordion.setAttribute('aria-expanded', 'true');
        const firstContent = firstAccordion.nextElementSibling;
        if (firstContent) {
            firstContent.style.display = 'block';
        }
    }

    // Initialiser les onglets (premier actif par défaut)
    const firstTabButton = document.querySelector('.tab-button');
    if (firstTabButton) {
        firstTabButton.classList.add('active');
        const firstTabId = firstTabButton.getAttribute('data-tab');
        const firstTabContent = document.getElementById(firstTabId);
        if (firstTabContent) {
            firstTabContent.style.display = 'block';
        }
    }

});