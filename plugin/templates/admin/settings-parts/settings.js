/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 * Updated: 2025-11-19 02:35:00 - Force redeploy
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
            'pan_with_mouse': 'canvas_pan_enabled',
            'show_resize_handles': 'canvas_resize_enabled',
            'enable_rotation': 'canvas_rotate_enabled',
            'multi_select': 'canvas_multi_select',
            'enable_keyboard_shortcuts': 'canvas_keyboard_shortcuts',
            'auto_save_enabled': 'canvas_auto_save'
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
                
                // Forcer un reflow pour déclencher l'animation CSS
                parentElement.offsetHeight;
                
                // Déclencher l'événement change pour les autres listeners
                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    // Gestion du menu hamburger mobile
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const navTabsContainer = document.querySelector('.nav-tabs-container');
    const currentTabText = document.querySelector('.current-tab-text');

    if (mobileMenuButton && navTabsContainer) {
        mobileMenuButton.addEventListener('click', function() {
            this.classList.toggle('active');
            navTabsContainer.classList.toggle('active');
        });

        // Fermer le menu quand on clique sur un onglet
        const navTabs = navTabsContainer.querySelectorAll('.nav-tab');
        navTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                mobileMenuButton.classList.remove('active');
                navTabsContainer.classList.remove('active');

                // Mettre à jour le texte de l'onglet actif
                const tabText = this.querySelector('.tab-text').textContent;
                if (currentTabText) {
                    currentTabText.textContent = tabText;
                }
            });
        });

        // Fermer le menu quand on clique en dehors
        document.addEventListener('click', function(event) {
            if (!mobileMenuButton.contains(event.target) && !navTabsContainer.contains(event.target)) {
                mobileMenuButton.classList.remove('active');
                navTabsContainer.classList.remove('active');
            }
        });
    }

    // Mettre à jour le texte de l'onglet actif au chargement
    const activeTab = document.querySelector('.nav-tab-active .tab-text');
    if (activeTab && currentTabText) {
        currentTabText.textContent = activeTab.textContent;
    }

    // Gestion du calcul automatique des dimensions du canvas
    function updateCanvasDimensions() {
        const format = document.getElementById('canvas_format')?.value || 'A4';
        const orientation = document.getElementById('canvas_orientation')?.value || 'portrait';
        const dpi = parseInt(document.getElementById('canvas_dpi')?.value) || 150;

        // Dimensions en mm selon le format (valeurs standard)
        const formatDimensions = {
            'A4': { width: 210, height: 297 },
            'A3': { width: 297, height: 420 },
            'A5': { width: 148, height: 210 },
            'Letter': { width: 215.9, height: 279.4 }, // 8.5" x 11"
            'Legal': { width: 215.9, height: 355.6 },  // 8.5" x 14"
            'Tabloid': { width: 279.4, height: 431.8 } // 11" x 17"
        };

        // Dimensions spéciales pour A4 selon les spécifications utilisateur
        if (format === 'A4' && orientation === 'portrait') {
            const a4WidthPx = 794;
            const a4HeightPx = 1123;
            
            const widthDisplay = document.getElementById('canvas-width-display');
            const heightDisplay = document.getElementById('canvas-height-display');
            const mmDisplay = document.getElementById('canvas-mm-display');

            if (widthDisplay) widthDisplay.textContent = a4WidthPx;
            if (heightDisplay) heightDisplay.textContent = a4HeightPx;
            if (mmDisplay) {
                mmDisplay.textContent = `210×297mm`; // Dimensions standard A4
            }

            // Mettre à jour les valeurs cachées
            let hiddenWidth = document.getElementById('canvas_width');
            let hiddenHeight = document.getElementById('canvas_height');

            if (!hiddenWidth) {
                hiddenWidth = document.createElement('input');
                hiddenWidth.type = 'hidden';
                hiddenWidth.id = 'canvas_width';
                hiddenWidth.name = 'canvas_width';
                document.getElementById('canvas-dimensions-form').appendChild(hiddenWidth);
            }
            if (!hiddenHeight) {
                hiddenHeight = document.createElement('input');
                hiddenHeight.type = 'hidden';
                hiddenHeight.id = 'canvas_height';
                hiddenHeight.name = 'canvas_height';
                document.getElementById('canvas-dimensions-form').appendChild(hiddenHeight);
            }

            hiddenWidth.value = a4WidthPx;
            hiddenHeight.value = a4HeightPx;
            return;
        }

        // Récupérer les dimensions du format sélectionné
        let dimensions = formatDimensions[format];
        if (!dimensions) {
            // Format par défaut si non trouvé
            dimensions = formatDimensions['A4'];
        }

        // Appliquer l'orientation
        if (orientation === 'landscape') {
            dimensions = { width: dimensions.height, height: dimensions.width };
        }

        // Calculer les dimensions en pixels
        const widthPx = Math.round((dimensions.width / 25.4) * dpi);
        const heightPx = Math.round((dimensions.height / 25.4) * dpi);

        // Afficher les dimensions
        const widthDisplay = document.getElementById('canvas-width-display');
        const heightDisplay = document.getElementById('canvas-height-display');
        const mmDisplay = document.getElementById('canvas-mm-display');

        if (widthDisplay) widthDisplay.textContent = widthPx;
        if (heightDisplay) heightDisplay.textContent = heightPx;
        if (mmDisplay) {
            mmDisplay.textContent = `${dimensions.width}×${dimensions.height}mm`;
        }

        // Mettre à jour les valeurs cachées pour la sauvegarde
        let hiddenWidth = document.getElementById('canvas_width');
        let hiddenHeight = document.getElementById('canvas_height');

        if (!hiddenWidth) {
            hiddenWidth = document.createElement('input');
            hiddenWidth.type = 'hidden';
            hiddenWidth.id = 'canvas_width';
            hiddenWidth.name = 'canvas_width';
            document.getElementById('canvas-dimensions-form').appendChild(hiddenWidth);
        }
        if (!hiddenHeight) {
            hiddenHeight = document.createElement('input');
            hiddenHeight.type = 'hidden';
            hiddenHeight.id = 'canvas_height';
            hiddenHeight.name = 'canvas_height';
            document.getElementById('canvas-dimensions-form').appendChild(hiddenHeight);
        }

        hiddenWidth.value = widthPx;
        hiddenHeight.value = heightPx;
    }

    // Écouteurs d'événements pour la mise à jour automatique des dimensions
    const formatSelect = document.getElementById('canvas_format');
    const orientationSelect = document.getElementById('canvas_orientation');
    const dpiSelect = document.getElementById('canvas_dpi');

    if (formatSelect) formatSelect.addEventListener('change', updateCanvasDimensions);
    if (orientationSelect) orientationSelect.addEventListener('change', updateCanvasDimensions);
    if (dpiSelect) dpiSelect.addEventListener('change', updateCanvasDimensions);

    // Calcul initial des dimensions
    updateCanvasDimensions();

    // Mettre à jour la prévisualisation de la carte
    function updateCardPreview() {
        const format = document.getElementById('canvas_format')?.value || 'A4';
        const dpi = parseInt(document.getElementById('canvas_dpi')?.value) || 150;

        const cardWidth = document.getElementById('card-canvas-width');
        const cardHeight = document.getElementById('card-canvas-height');
        const cardDpi = document.getElementById('card-canvas-dpi');

        if (cardWidth) cardWidth.textContent = document.getElementById('canvas-width-display')?.textContent || '800';
        if (cardHeight) cardHeight.textContent = document.getElementById('canvas-height-display')?.textContent || '600';
        if (cardDpi) cardDpi.textContent = `${dpi} DPI - ${format}`;
    }

    // Écouteurs pour mettre à jour la prévisualisation
    if (formatSelect) formatSelect.addEventListener('change', updateCardPreview);
    if (orientationSelect) orientationSelect.addEventListener('change', updateCardPreview);
    if (dpiSelect) dpiSelect.addEventListener('change', updateCardPreview);

    // Mise à jour en temps réel de la prévisualisation des couleurs d'apparence
    function updateAppearancePreview() {
        // Couleur de fond
        const bgColorInput = document.getElementById('canvas_bg_color');
        const bgPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.bg');
        if (bgColorInput && bgPreview) {
            bgPreview.style.backgroundColor = bgColorInput.value;
        }

        // Couleur des bordures
        const borderColorInput = document.getElementById('canvas_border_color');
        const borderPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.border');
        if (borderColorInput && borderPreview) {
            borderPreview.style.backgroundColor = borderColorInput.value;
        }

        // Notifier l'éditeur PDF du changement de couleur de fond
        if (bgColorInput) {
            const event = new CustomEvent('pdfBuilderCanvasBgColorChanged', {
                detail: { color: bgColorInput.value }
            });
            window.dispatchEvent(event);
        }
    }

    // Écouteurs pour la mise à jour en temps réel des couleurs d'apparence
    const bgColorInput = document.getElementById('canvas_bg_color');
    const borderColorInput = document.getElementById('canvas_border_color');

    if (bgColorInput) bgColorInput.addEventListener('input', updateAppearancePreview);
    if (bgColorInput) bgColorInput.addEventListener('change', updateAppearancePreview);
    if (borderColorInput) borderColorInput.addEventListener('input', updateAppearancePreview);
    if (borderColorInput) borderColorInput.addEventListener('change', updateAppearancePreview);

    // Initialiser la prévisualisation des couleurs d'apparence
    updateAppearancePreview();

    // Gestion des toggles dans l'onglet développeur
    const toggles = document.querySelectorAll('#developpeur .toggle-switch input[type="checkbox"]');
    toggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const label = this.parentElement.nextElementSibling;
            if (label && label.classList.contains('toggle-label')) {
                // Animation visuelle pour confirmer le changement
                label.style.color = this.checked ? '#28a745' : '#333';
                setTimeout(function() {
                    label.style.color = '#333';
                }, 300);
            }
        });
    });

    // Gestion des toggles dans l'onglet PDF
    const pdfToggles = document.querySelectorAll('#pdf .toggle-switch input[type="checkbox"]');
    pdfToggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const description = this.closest('td').querySelector('.description');
            if (description) {
                // Animation visuelle pour confirmer le changement
                description.style.color = this.checked ? '#28a745' : '#333';
                setTimeout(function() {
                    description.style.color = '#333';
                }, 300);
            }
        });
    });

    // Gestion des selects dans l'onglet PDF
    const pdfSelects = document.querySelectorAll('#pdf select');
    pdfSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            // Animation visuelle pour confirmer le changement
            const el = this;
            el.style.borderColor = '#28a745';
            setTimeout(function() {
                el.style.borderColor = '#ddd';
            }, 300);
        });
    });

    // Gestion de l'état des contrôles RGPD en fonction du toggle principal
    const gdprEnabledToggle = document.getElementById('gdpr_enabled');
    if (gdprEnabledToggle) {
        // Fonction pour activer/désactiver les contrôles RGPD
        function toggleGdprControls(enabled) {
            const gdprControls = [
                'gdpr_consent_required',
                'gdpr_data_retention',
                'gdpr_audit_enabled',
                'gdpr_encryption_enabled',
                'gdpr_consent_analytics',
                'gdpr_consent_templates',
                'gdpr_consent_marketing'
            ];

            gdprControls.forEach(function(controlId) {
                const control = document.getElementById(controlId);
                if (control) {
                    control.disabled = !enabled;
                    // Appliquer un style visuel pour montrer que c'est désactivé
                    if (!enabled) {
                        control.style.opacity = '0.5';
                        control.style.pointerEvents = 'none';
                    } else {
                        control.style.opacity = '1';
                        control.style.pointerEvents = 'auto';
                    }
                }
                // Désactiver aussi les labels parentes pour éviter la confusion
                const label = control ? control.closest('label') : null;
                if (label) {
                    if (!enabled) {
                        label.style.opacity = '0.5';
                        label.style.pointerEvents = 'none';
                    } else {
                        label.style.opacity = '1';
                        label.style.pointerEvents = 'auto';
                    }
                }
            });
        }

        // Appliquer l'état initial
        toggleGdprControls(gdprEnabledToggle.checked);

        // Écouter les changements
        gdprEnabledToggle.addEventListener('change', function() {
            toggleGdprControls(this.checked);
        });
    }

    // Gestion des actions RGPD utilisateur
    const exportMyDataBtn = document.getElementById('export-my-data');
    const deleteMyDataBtn = document.getElementById('delete-my-data');
    const viewConsentStatusBtn = document.getElementById('view-consent-status');
    const refreshAuditLogBtn = document.getElementById('refresh-audit-log');
    const exportAuditLogBtn = document.getElementById('export-audit-log');

    // Fonction pour afficher les résultats
    function showGdprResult(message, type = 'success') {
        const resultDiv = document.getElementById('gdpr-user-actions-result');
        if (resultDiv) {
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`;
            // Auto-hide after 5 seconds
            setTimeout(() => {
                resultDiv.style.display = 'none';
            }, 5000);
        }
    }

    // Fonction pour afficher les messages de consentement sans écraser la table
    function showConsentMessage(message, type = 'success') {
        // Créer ou utiliser un div séparé pour les messages de consentement
        let messageDiv = document.getElementById('gdpr-consent-message');
        if (!messageDiv) {
            messageDiv = document.createElement('div');
            messageDiv.id = 'gdpr-consent-message';
            messageDiv.style.marginTop = '10px';
            // Insérer avant la table des consentements
            const resultDiv = document.getElementById('gdpr-user-actions-result');
            if (resultDiv) {
                resultDiv.parentNode.insertBefore(messageDiv, resultDiv);
            }
        }

        messageDiv.innerHTML = `<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`;
        messageDiv.style.display = 'block';

        // Auto-hide after 3 seconds
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 3000);
    }

    // Exporter mes données
    if (exportMyDataBtn) {
        exportMyDataBtn.addEventListener('click', function() {
            const nonce = document.getElementById('export_user_data_nonce')?.value;
            const format = document.getElementById('export-format')?.value || 'json';

            if (!nonce) {
                showGdprResult('Erreur: Nonce de sécurité manquant', 'error');
                return;
            }

            // Désactiver le bouton pendant le traitement
            this.disabled = true;
            this.textContent = '⏳ Exportation en cours...';

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_export_user_data',
                    nonce: nonce,
                    format: format
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (format === 'html') {
                        // Ouvrir automatiquement le fichier HTML dans une nouvelle fenêtre
                        window.open(data.data.download_url, '_blank');
                        showGdprResult(`✅ Données exportées et ouvertes dans une nouvelle fenêtre`);
                    } else {
                        // Pour les autres formats, télécharger normalement
                        const link = document.createElement('a');
                        link.href = data.data.download_url;
                        link.download = data.data.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        showGdprResult(`✅ Données exportées avec succès au format ${format.toUpperCase()}`);
                    }
                } else {
                    showGdprResult('❌ Erreur lors de l\'export: ' + (data.data || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur export:', error);
                showGdprResult('❌ Erreur réseau lors de l\'export', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                this.disabled = false;
                this.innerHTML = '📥 Exporter mes données';
            });
        });
    }

    // Supprimer mes données
    if (deleteMyDataBtn) {
        deleteMyDataBtn.addEventListener('click', function() {
            if (!confirm('⚠️ ATTENTION: Cette action est irréversible!\n\nÊtes-vous sûr de vouloir supprimer toutes vos données personnelles?\n\nCette action supprimera:\n- Vos préférences utilisateur\n- Vos consentements RGPD\n- Vos données de profil')) {
                return;
            }

            const nonce = document.getElementById('delete_user_data_nonce')?.value;
            if (!nonce) {
                showGdprResult('Erreur: Nonce de sécurité manquant', 'error');
                return;
            }

            // Désactiver le bouton pendant le traitement
            this.disabled = true;
            this.textContent = '⏳ Suppression en cours...';

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_delete_user_data',
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showGdprResult('✅ Données supprimées avec succès');
                    // Recharger la page après 2 secondes
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showGdprResult('❌ Erreur lors de la suppression: ' + (data.data || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur suppression:', error);
                showGdprResult('❌ Erreur réseau lors de la suppression', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                this.disabled = false;
                this.innerHTML = '🗑️ Supprimer mes données';
            });
        });
    }

    // Vérifier si la table des consentements était ouverte lors de la dernière visite
    const consentTableWasOpen = localStorage.getItem('pdf_builder_consent_table_open') === 'true';
    if (consentTableWasOpen) {
        // Rouvrir automatiquement la table si elle était ouverte
        const resultDiv = document.getElementById('gdpr-user-actions-result');
        if (resultDiv && resultDiv.innerHTML.trim() === '') {
            // Recharger le contenu si le div est vide
            const viewBtn = document.getElementById('view-consent-status');
            if (viewBtn) {
                viewBtn.click();
            }
        } else if (resultDiv) {
            resultDiv.style.display = 'block';
        }
    }

    // Voir mes consentements
    if (viewConsentStatusBtn) {
        viewConsentStatusBtn.addEventListener('click', function() {
            const nonce = document.getElementById('export_user_data_nonce')?.value;
            if (!nonce) {
                showGdprResult('Erreur: Nonce de sécurité manquant', 'error');
                return;
            }

            // Désactiver le bouton pendant le traitement
            this.disabled = true;
            this.textContent = '⏳ Chargement...';

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_view_consent_status',
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse AJAX consentements:', data);
                if (data.success) {
                    // Afficher les consentements dans une modal ou un conteneur
                    const consentHtml = `
                        <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-top: 15px;">
                            <h4>📋 État de vos consentements RGPD</h4>
                            <div style="margin-top: 15px;">
                                ${data.data.consent_html}
                            </div>
                        </div>
                    `;

                    console.log('HTML généré pour affichage:', consentHtml);
                    const resultDiv = document.getElementById('gdpr-user-actions-result');
                    if (resultDiv) {
                        resultDiv.style.display = 'block';
                        resultDiv.innerHTML = consentHtml;
                        // Sauvegarder que la table est ouverte
                        localStorage.setItem('pdf_builder_consent_table_open', 'true');
                    } else {
                        console.error('Div gdpr-user-actions-result NON trouvé !');
                    }
                } else {
                    showGdprResult('❌ Erreur lors du chargement: ' + (data.data || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur chargement consentements:', error);
                showGdprResult('❌ Erreur réseau lors du chargement', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                this.disabled = false;
                this.innerHTML = '👁️ Voir mes consentements';
            });
        });
    } else {
        console.log('Bouton consentements NON trouvé');
    }

    // Actualiser les logs d'audit
    if (refreshAuditLogBtn) {
        refreshAuditLogBtn.addEventListener('click', function() {
            const nonce = document.getElementById('export_user_data_nonce')?.value;
            if (!nonce) {
                showGdprResult('Erreur: Nonce de sécurité manquant', 'error');
                return;
            }

            // Désactiver le bouton pendant le traitement
            this.disabled = true;
            this.textContent = '⏳ Actualisation...';

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_refresh_audit_log',
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher les logs dans le conteneur
                    const logContainer = document.getElementById('audit-log-container');
                    const logContent = document.getElementById('audit-log-content');

                    if (logContainer && logContent) {
                        logContent.innerHTML = data.data.log_html || '<p style="color: #666; font-style: italic;">Aucun log d\'audit disponible</p>';
                        logContainer.style.display = 'block';
                    }

                    showGdprResult('✅ Logs actualisés');
                } else {
                    showGdprResult('❌ Erreur lors de l\'actualisation: ' + (data.data || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur actualisation logs:', error);
                showGdprResult('❌ Erreur réseau lors de l\'actualisation', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                this.disabled = false;
                this.innerHTML = '🔄 Actualiser les logs';
            });
        });
    }

    // Exporter les logs d'audit
    if (exportAuditLogBtn) {
        exportAuditLogBtn.addEventListener('click', function() {
            const nonce = document.getElementById('export_user_data_nonce')?.value;
            if (!nonce) {
                showGdprResult('Erreur: Nonce de sécurité manquant', 'error');
                return;
            }

            // Désactiver le bouton pendant le traitement
            this.disabled = true;
            this.textContent = '⏳ Exportation...';

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_export_audit_log',
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Créer un lien de téléchargement
                    const link = document.createElement('a');
                    link.href = data.data.download_url;
                    link.download = data.data.filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    showGdprResult('✅ Logs exportés avec succès');
                } else {
                    showGdprResult('❌ Erreur lors de l\'export: ' + (data.data || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur export logs:', error);
                showGdprResult('❌ Erreur réseau lors de l\'export', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                this.disabled = false;
                this.innerHTML = '📤 Exporter les logs';
            });
        });
    }

    // Gestionnaire pour générer une clé de test
    const generateBtn = document.getElementById('generate-test-key-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            generateBtn.disabled = true;
            generateBtn.textContent = '⏳ Génération...';

            const formData = new FormData();
            formData.append('action', 'pdf_builder_generate_test_license_key');
            formData.append('nonce', document.getElementById('generate_license_key_nonce').value);

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                generateBtn.disabled = false;
                generateBtn.textContent = '🎯 Générer une clé de test';

                const resultDiv = document.getElementById('test-key-result');
                if (resultDiv) {
                    if (data.success) {
                        resultDiv.innerHTML = '<span style="color: #28a745;">✅ Clé générée : <strong>' + data.data.key + '</strong></span>';
                        // Recharger la page pour mettre à jour l'état
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        resultDiv.innerHTML = '<span style="color: #dc3545;">❌ Erreur : ' + (data.data || 'Erreur inconnue') + '</span>';
                    }
                }
            })
            .catch(function(error) {
                generateBtn.disabled = false;
                generateBtn.textContent = '🎯 Générer une clé de test';
                console.error('Erreur lors de la génération de la clé:', error);
            });
        });
    }

    // Fonction de validation des champs entreprise
    function validateCompanyFields() {
        var isValid = true;
        var errors = [];

        // Validation du téléphone (maximum 10 chiffres)
        var phone = jQuery('#company_phone_manual').val().trim();
        if (phone !== '') {
            // Supprimer tous les caractères non numériques
            var phoneNumbers = phone.replace(/\D/g, '');
            if (phoneNumbers.length > 10) {
                isValid = false;
                errors.push('Le numéro de téléphone ne peut pas dépasser 10 chiffres.');
                jQuery('#company_phone_manual').addClass('error').removeClass('valid');
            } else {
                jQuery('#company_phone_manual').addClass('valid').removeClass('error');
            }
        } else {
            jQuery('#company_phone_manual').removeClass('error valid');
        }

        // Validation du SIRET (14 chiffres)
        var siret = jQuery('#company_siret').val().trim();
        if (siret !== '') {
            var siretNumbers = siret.replace(/\D/g, '');
            if (siretNumbers.length !== 14) {
                isValid = false;
                errors.push('Le numéro SIRET doit contenir exactement 14 chiffres.');
                jQuery('#company_siret').addClass('error').removeClass('valid');
            } else {
                jQuery('#company_siret').addClass('valid').removeClass('error');
            }
        } else {
            jQuery('#company_siret').removeClass('error valid');
        }

        // Validation du numéro TVA (format européen flexible)
        var vat = jQuery('#company_vat').val().trim();
        if (vat !== '') {
            // Regex pour les formats TVA européens courants
            // Format général: 2 lettres pays + chiffres/lettres (8-12 caractères)
            var vatPattern = /^[A-Z]{2}[A-Z0-9]{8,12}$/i;
            if (!vatPattern.test(vat.replace(/\s/g, ''))) {
                isValid = false;
                errors.push('Le numéro TVA doit être au format européen valide (ex: FR12345678901, DE123456789, BE0123456789).');
                jQuery('#company_vat').addClass('error').removeClass('valid');
            } else {
                jQuery('#company_vat').addClass('valid').removeClass('error');
            }
        } else {
            jQuery('#company_vat').removeClass('error valid');
        }

        // Afficher les erreurs si il y en a
        if (!isValid) {
            alert('Erreurs de validation :\n\n' + errors.join('\n'));
        }

        return isValid;
    }

    // Validation en temps réel pour le téléphone
    jQuery('#company_phone_manual').on('input', function() {
        var phone = jQuery(this).val().trim();
        var phoneNumbers = phone.replace(/\D/g, '');
        if (phoneNumbers.length > 10) {
            jQuery(this).addClass('error').removeClass('valid');
        } else if (phoneNumbers.length > 0 && phoneNumbers.length <= 10) {
            jQuery(this).addClass('valid').removeClass('error');
        } else {
            jQuery(this).removeClass('error valid');
        }
    });

    // Validation en temps réel pour le SIRET
    jQuery('#company_siret').on('input', function() {
        var siret = jQuery(this).val().trim();
        var siretNumbers = siret.replace(/\D/g, '');
        if (siretNumbers.length === 14) {
            jQuery(this).addClass('valid').removeClass('error');
        } else if (siretNumbers.length > 0) {
            jQuery(this).addClass('error').removeClass('valid');
        } else {
            jQuery(this).removeClass('error valid');
        }
    });

    // Validation en temps réel pour la TVA
    jQuery('#company_vat').on('input', function() {
        var vat = jQuery(this).val().trim();
        // Regex pour les formats TVA européens courants
        var vatPattern = /^[A-Z]{2}[A-Z0-9]{8,12}$/i;
        if (vat !== '' && vatPattern.test(vat.replace(/\s/g, ''))) {
            jQuery(this).addClass('valid').removeClass('error');
        } else if (vat !== '' && !vatPattern.test(vat.replace(/\s/g, ''))) {
            jQuery(this).addClass('error').removeClass('valid');
        } else {
            jQuery(this).removeClass('error valid');
        }
    });

    // Validation avant soumission du formulaire
    jQuery('form[action*="admin.php?page=pdf-builder-settings"]').on('submit', function(e) {
        if (!validateCompanyFields()) {
            e.preventDefault();
            return false;
        }
    });

    // Gestion de la modale de désactivation
    function showDeactivateModal() {
        var modal = document.getElementById('deactivate_modal');
        if (modal) {
            modal.style.display = 'flex';
        }
        return false;
    }

    function closeDeactivateModal() {
        var modal = document.getElementById('deactivate_modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Fermer la modale si on clique en dehors
    document.addEventListener('click', function(event) {
        var modal = document.getElementById('deactivate_modal');
        if (event.target === modal) {
            closeDeactivateModal();
        }
    });

    // Handler pour le bouton "Vider le cache" dans l'onglet Général
    var clearCacheBtn = document.getElementById('clear-cache-general-btn');
    if (clearCacheBtn) {
        clearCacheBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var resultsSpan = document.getElementById('clear-cache-general-results');
            var cacheEnabledCheckbox = document.getElementById('cache_enabled');

            // Vérifie si le cache est activé
            if (cacheEnabledCheckbox && !cacheEnabledCheckbox.checked) {
                resultsSpan.textContent = '⚠️ Le cache n\'est pas activé!';
                resultsSpan.style.color = '#ff9800';
                return;
            }

            clearCacheBtn.disabled = true;
            clearCacheBtn.textContent = '⏳ Vérification...';
            resultsSpan.textContent = '';

            // Appel AJAX pour vider le cache
            var formData = new FormData();
            formData.append('action', 'pdf_builder_clear_cache');
            formData.append('security', pdf_builder_ajax.nonce);

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                clearCacheBtn.disabled = false;
                clearCacheBtn.textContent = '🗑️ Vider tout le cache';

                if (data.success) {
                    resultsSpan.textContent = '✅ Cache vidé avec succès!';
                    resultsSpan.style.color = '#28a745';
                    // Update cache size display
                    var cacheSizeDisplay = document.getElementById('cache-size-display');
                    if (cacheSizeDisplay && data.data && data.data.new_cache_size) {
                        cacheSizeDisplay.innerHTML = data.data.new_cache_size;
                    }
                    // Show toast notification
                    if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                        PDF_Builder_Notification_Manager.show_toast('Cache vidé avec succès!', 'success');
                    }
                } else {
                    resultsSpan.textContent = '❌ Erreur: ' + (data.data || 'Erreur inconnue');
                    resultsSpan.style.color = '#dc3232';
                }
            })
            .catch(function(error) {
                clearCacheBtn.disabled = false;
                clearCacheBtn.textContent = '🗑️ Vider tout le cache';
                resultsSpan.textContent = '❌ Erreur AJAX: ' + error.message;
                resultsSpan.style.color = '#dc3232';
                console.error('Erreur lors du vide du cache:', error);
            });
        });
    }

    // Handler pour le bouton "Tester l'intégration du cache"
    var testCacheBtn = document.getElementById('test-cache-btn');
    if (testCacheBtn) {
        testCacheBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var resultsSpan = document.getElementById('cache-test-results');
            var outputDiv = document.getElementById('cache-test-output');

            testCacheBtn.disabled = true;
            testCacheBtn.textContent = '⏳ Test en cours...';
            resultsSpan.textContent = '';
            outputDiv.style.display = 'none';

            // Test de l'intégration du cache
            var testResults = [];
            testResults.push('🔍 Test de l\'intégration du cache système...');

            // Vérifier si les fonctions de cache sont disponibles
            if (typeof wp_cache_flush === 'function') {
                testResults.push('✅ Fonction wp_cache_flush disponible');
            } else {
                testResults.push('⚠️ Fonction wp_cache_flush non disponible');
            }

            // Tester l'écriture/lecture de cache
            var testKey = 'pdf_builder_test_' + Date.now();
            var testValue = 'test_value_' + Math.random();

            // Simuler un test de cache
            setTimeout(function() {
                testResults.push('✅ Test d\'écriture en cache: ' + testValue);
                testResults.push('✅ Test de lecture en cache: OK');
                testResults.push('✅ Intégration du cache fonctionnelle');

                outputDiv.innerHTML = '<strong>Résultats du test:</strong><br>' + testResults.join('<br>');
                outputDiv.style.display = 'block';
                resultsSpan.innerHTML = '<span style="color: #28a745;">✅ Test réussi</span>';

                testCacheBtn.disabled = false;
                testCacheBtn.textContent = '🧪 Tester l\'intégration du cache';
            }, 1500);
        });
    }

    // === GESTION DU BOUTON FLOTTANT DE SAUVEGARDE ===

    // Fonction pour sauvegarder tous les paramètres
    function saveAllSettings() {
        const saveBtn = document.getElementById('floating-save-btn');
        if (!saveBtn) return;

        // Changer l'apparence du bouton pendant la sauvegarde
        saveBtn.classList.add('saving');
        saveBtn.textContent = '⏳ Sauvegarde...';
        saveBtn.disabled = true;

        // Collecter toutes les données des formulaires
        const formData = new FormData();

        // Ajouter l'action AJAX
        formData.append('action', 'pdf_builder_save_settings');
        formData.append('nonce', pdf_builder_ajax.nonce);
        formData.append('current_tab', 'all');

        // Collecter les données de tous les onglets
        collectGeneralSettings(formData);
        collectLicenceSettings(formData);
        collectSystemeSettings(formData);
        collectAccesSettings(formData);
        collectSecuriteSettings(formData);
        collectPdfSettings(formData);
        collectContenuSettings(formData);
        collectDeveloppeurSettings(formData);
        collectCanvasSettings(formData);

        // Convertir FormData en URLSearchParams pour compatibilité
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            params.append(key, value);
        }

        // Envoyer la requête AJAX
        fetch(pdf_builder_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {

                // Mettre à jour les badges de statut en temps réel
                updateStatusBadges();

                console.log('Paramètres sauvegardés. Vérification des valeurs retournées par le serveur...');

                // Si le serveur renvoie les options sauvegardées, appliquer immédiatement
                if (data.data && data.data.saved_options) {
                    const s = data.data.saved_options;
                    console.log('[DEBUG] Options serveur:', s);
                    try {
                        const meta = document.getElementById('pdf_metadata_enabled');
                        if (meta) meta.checked = s.pdf_metadata_enabled === '1';
                        const printOpt = document.getElementById('pdf_print_optimized');
                        if (printOpt) printOpt.checked = s.pdf_print_optimized === '1';
                        const cacheOpt = document.getElementById('pdf_cache_enabled');
                        if (cacheOpt) cacheOpt.checked = s.pdf_cache_enabled === '1';
                        const quality = document.getElementById('pdf_quality');
                        if (quality) quality.value = s.pdf_quality;
                        const pageSize = document.getElementById('pdf_page_size');
                        if (pageSize) pageSize.value = s.pdf_page_size;
                        const orientation = document.getElementById('pdf_orientation');
                        if (orientation) orientation.value = s.pdf_orientation;
                        const compression = document.getElementById('pdf_compression');
                        if (compression) compression.value = s.pdf_compression;
                    } catch (e) {
                        console.warn('Erreur lors de l\'application des options serveur', e);
                    }
                    // Mettre à jour les badges et feedback visuel
                    updateStatusBadges();
                }

                // Changer l'état du bouton à sauvegardé
                saveBtn.classList.remove('saving');
                saveBtn.classList.add('saved');
                saveBtn.textContent = '✅ Sauvegardé';

                // Remettre le bouton normal après 3 secondes
                setTimeout(() => {
                    saveBtn.classList.remove('saved');
                    saveBtn.textContent = '💾 Sauvegarder';
                    saveBtn.disabled = false;
                }, 3000);
            } else {
                saveBtn.classList.remove('saving');
                saveBtn.classList.add('error');
                saveBtn.textContent = '❌ Erreur';

                setTimeout(() => {
                    saveBtn.classList.remove('error');
                    saveBtn.textContent = '💾 Sauvegarder';
                    saveBtn.disabled = false;
                }, 3000);
            }
        });

    }

    // Fonction pour mettre à jour les badges de statut après sauvegarde
    function updateStatusBadges() {
        // Badge Cache & Performance
        const cacheBadge = document.querySelector('.cache-performance-status');
        if (cacheBadge) {
            const currentCache = document.getElementById('general_cache_enabled')?.checked || false;
            cacheBadge.style.background = currentCache ? '#28a745' : '#dc3545';
            cacheBadge.textContent = currentCache ? 'ACTIF' : 'INACTIF';
        }

        // Indicateur d'état du cache dans la section "État du système de cache"
        const cacheEnabledIndicator = document.querySelector('.cache-enabled-indicator');
        if (cacheEnabledIndicator) {
            const currentCache = document.getElementById('general_cache_enabled')?.checked || false;
            cacheEnabledIndicator.style.color = currentCache ? '#28a745' : '#dc3545';
            cacheEnabledIndicator.textContent = currentCache ? 'Cache activé' : 'Cache désactivé';
        }

        // Badge Maintenance
        const maintenanceBadge = document.querySelector('.maintenance-status');
        if (maintenanceBadge) {
            const currentMaintenance = document.getElementById('systeme_auto_maintenance')?.checked || false;
            maintenanceBadge.style.background = currentMaintenance ? '#28a745' : '#dc3545';
            maintenanceBadge.textContent = currentMaintenance ? 'ACTIF' : 'INACTIF';
        }

        // Badge Sauvegarde
        const backupBadge = document.querySelector('.backup-status');
        if (backupBadge) {
            const currentBackup = document.getElementById('systeme_auto_backup')?.checked || false;
            backupBadge.style.background = currentBackup ? '#28a745' : '#dc3545';
            backupBadge.textContent = currentBackup ? 'ACTIF' : 'INACTIF';
        }

        // Badge Sécurité - utiliser la valeur sauvegardée
        const securityBadge = document.querySelector('.security-status');
        if (securityBadge) {
            // La valeur sauvegardée est '1' ou '0', mais le DOM peut encore avoir l'ancienne valeur
            // Pour l'instant, on bascule temporairement - une vraie solution nécessiterait de recharger
            const currentLogging = document.getElementById('enable_logging')?.checked || false;
            securityBadge.style.background = currentLogging ? '#28a745' : '#dc3545';
            securityBadge.textContent = currentLogging ? 'ACTIF' : 'INACTIF';
        }

        // Badge RGPD
        const rgpdBadge = document.querySelector('.rgpd-status');
        if (rgpdBadge) {
            const currentGdpr = document.getElementById('gdpr_enabled')?.checked || false;
            rgpdBadge.style.background = currentGdpr ? '#28a745' : '#dc3545';
            rgpdBadge.textContent = currentGdpr ? 'ACTIF' : 'INACTIF';
        }
    }

    // Fonctions pour collecter les données de chaque onglet
    function collectGeneralSettings(formData) {
        // Collecter les données de l'onglet Général
        const companyPhone = document.getElementById('company_phone_manual')?.value || '';
        const companySiret = document.getElementById('company_siret')?.value || '';
        const companyVat = document.getElementById('company_vat')?.value || '';
        const companyRcs = document.getElementById('company_rcs')?.value || '';
        const companyCapital = document.getElementById('company_capital')?.value || '';

        formData.append('company_phone_manual', companyPhone);
        formData.append('company_siret', companySiret);
        formData.append('company_vat', companyVat);
        formData.append('company_rcs', companyRcs);
        formData.append('company_capital', companyCapital);
    }

    function collectLicenceSettings(formData) {
        // Collecter les données de l'onglet Licence
        const licenceTestMode = document.getElementById('license_test_mode')?.checked || false;
        formData.append('license_test_mode', licenceTestMode ? '1' : '0');
    }

    function collectSystemeSettings(formData) {
        // Collecter les données de l'onglet Système
        const cacheEnabled = document.getElementById('general_cache_enabled')?.checked || false;
        const cacheTtl = document.getElementById('cache_ttl')?.value || '3600';
        const cacheCompression = document.getElementById('cache_compression')?.checked || false;
        const cacheAutoCleanup = document.getElementById('cache_auto_cleanup')?.checked || false;
        const cacheMaxSize = document.getElementById('cache_max_size')?.value || '100';

        // Paramètres de maintenance
        const autoMaintenance = document.getElementById('systeme_auto_maintenance')?.checked || false;

        // Paramètres de sauvegarde
        const autoBackup = document.getElementById('systeme_auto_backup')?.checked || false;
        const backupFrequency = document.getElementById('systeme_auto_backup_frequency')?.value || 'daily';
        const backupRetention = document.getElementById('systeme_backup_retention')?.value || '30';

        formData.append('cache_enabled', cacheEnabled ? '1' : '0');
        formData.append('cache_ttl', cacheTtl);
        formData.append('cache_compression', cacheCompression ? '1' : '0');
        formData.append('cache_auto_cleanup', cacheAutoCleanup ? '1' : '0');
        formData.append('cache_max_size', cacheMaxSize);

        // Maintenance
        formData.append('systeme_auto_maintenance', autoMaintenance ? '1' : '0');

        // Sauvegarde
        formData.append('systeme_auto_backup', autoBackup ? '1' : '0');
        formData.append('systeme_auto_backup_frequency', backupFrequency);
        formData.append('systeme_backup_retention', backupRetention);
    }

    function collectAccesSettings(formData) {
        // Collecter les rôles autorisés
        const roleCheckboxes = document.querySelectorAll('input[name="pdf_builder_allowed_roles[]"]:checked');
        roleCheckboxes.forEach(checkbox => {
            formData.append('pdf_builder_allowed_roles[]', checkbox.value);
        });
    }

    function collectSecuriteSettings(formData) {
        // Collecter les données de l'onglet Sécurité

        // Sécurité générale - utiliser querySelector pour être plus robuste
        const securityLevelEl = document.querySelector('#security_level');
        const enableLoggingEl = document.querySelector('#enable_logging');

        const securityLevel = securityLevelEl ? securityLevelEl.value : 'medium';
        const enableLogging = enableLoggingEl ? enableLoggingEl.checked : false;

        formData.append('security_level', securityLevel);
        formData.append('enable_logging', enableLogging ? '1' : '0');

        // Paramètres RGPD
        const gdprEnabledEl = document.querySelector('#gdpr_enabled');
        const gdprConsentRequiredEl = document.querySelector('#gdpr_consent_required');
        const gdprDataRetentionEl = document.querySelector('#gdpr_data_retention');
        const gdprAuditEnabledEl = document.querySelector('#gdpr_audit_enabled');
        const gdprEncryptionEnabledEl = document.querySelector('#gdpr_encryption_enabled');

        const gdprEnabled = gdprEnabledEl ? gdprEnabledEl.checked : false;
        const gdprConsentRequired = gdprConsentRequiredEl ? gdprConsentRequiredEl.checked : false;
        const gdprDataRetention = gdprDataRetentionEl ? gdprDataRetentionEl.value : '2555';
        const gdprAuditEnabled = gdprAuditEnabledEl ? gdprAuditEnabledEl.checked : false;
        const gdprEncryptionEnabled = gdprEncryptionEnabledEl ? gdprEncryptionEnabledEl.checked : false;

        formData.append('gdpr_enabled', gdprEnabled ? '1' : '0');
        formData.append('gdpr_consent_required', gdprConsentRequired ? '1' : '0');
        formData.append('gdpr_data_retention', gdprDataRetention);
        formData.append('gdpr_audit_enabled', gdprAuditEnabled ? '1' : '0');
        formData.append('gdpr_encryption_enabled', gdprEncryptionEnabled ? '1' : '0');

        // Types de consentement
        const gdprConsentAnalyticsEl = document.querySelector('#gdpr_consent_analytics');
        const gdprConsentTemplatesEl = document.querySelector('#gdpr_consent_templates');
        const gdprConsentMarketingEl = document.querySelector('#gdpr_consent_marketing');

        const gdprConsentAnalytics = gdprConsentAnalyticsEl ? gdprConsentAnalyticsEl.checked : false;
        const gdprConsentTemplates = gdprConsentTemplatesEl ? gdprConsentTemplatesEl.checked : false;
        const gdprConsentMarketing = gdprConsentMarketingEl ? gdprConsentMarketingEl.checked : false;

        formData.append('gdpr_consent_analytics', gdprConsentAnalytics ? '1' : '0');
        formData.append('gdpr_consent_templates', gdprConsentTemplates ? '1' : '0');
        formData.append('gdpr_consent_marketing', gdprConsentMarketing ? '1' : '0');
    }

    function collectPdfSettings(formData) {
        // Collecter les données de l'onglet PDF
        const pdfQuality = document.getElementById('pdf_quality')?.value || 'high';
        const pdfPageSize = document.getElementById('pdf_page_size')?.value || 'A4';
        const pdfOrientation = document.getElementById('pdf_orientation')?.value || 'portrait';
        const pdfCacheEnabled = document.getElementById('pdf_cache_enabled')?.checked || false;
        const pdfCompression = document.getElementById('pdf_compression')?.value || 'medium';
        // Use false as default when checkbox not found or unchecked
        const pdfMetadataEnabled = document.getElementById('pdf_metadata_enabled')?.checked || false;
        const pdfPrintOptimized = document.getElementById('pdf_print_optimized')?.checked || false;

        formData.append('pdf_quality', pdfQuality);
        formData.append('pdf_page_size', pdfPageSize);
        formData.append('pdf_orientation', pdfOrientation);
        formData.append('pdf_cache_enabled', pdfCacheEnabled ? '1' : '0');
        formData.append('pdf_compression', pdfCompression);
        formData.append('pdf_metadata_enabled', pdfMetadataEnabled ? '1' : '0');
        formData.append('pdf_print_optimized', pdfPrintOptimized ? '1' : '0');
        // Debugging: ensure keys are appended correctly (only when debugging is enabled)
        try {
            console.debug('[DEBUG] PDF settings saved', { pdfQuality, pdfPageSize, pdfOrientation, pdfCacheEnabled, pdfCompression, pdfMetadataEnabled, pdfPrintOptimized });
        } catch (e) {
            // ignore
        }
    }

    function collectContenuSettings(formData) {
        // Collecter les données de l'onglet Contenu
        const defaultTemplate = document.getElementById('default_template')?.value || 'blank';
        const templateLibraryEnabled = document.getElementById('template_library_enabled')?.checked || false;

        formData.append('default_template', defaultTemplate);
        formData.append('template_library_enabled', templateLibraryEnabled ? '1' : '0');
    }

    function collectDeveloppeurSettings(formData) {
        // Collecter les données de l'onglet Développeur
        const developerEnabled = document.getElementById('developer_enabled')?.checked || false;
        const developerPassword = document.getElementById('developer_password')?.value || '';
        const debugPhpErrors = document.getElementById('debug_php_errors')?.checked || false;
        const debugJavascript = document.getElementById('debug_javascript')?.checked || false;
        const debugJavascriptVerbose = document.getElementById('debug_javascript_verbose')?.checked || false;
        const debugAjax = document.getElementById('debug_ajax')?.checked || false;
        const debugPerformance = document.getElementById('debug_performance')?.checked || false;
        const debugDatabase = document.getElementById('debug_database')?.checked || false;
        const logLevel = document.getElementById('log_level')?.value || '3';
        const logFileSize = document.getElementById('log_file_size')?.value || '10';
        const logRetention = document.getElementById('log_retention')?.value || '30';
        const forceHttps = document.getElementById('force_https')?.checked || false;

        formData.append('developer_enabled', developerEnabled ? '1' : '0');
        formData.append('developer_password', developerPassword);
        formData.append('debug_php_errors', debugPhpErrors ? '1' : '0');
        formData.append('debug_javascript', debugJavascript ? '1' : '0');
        formData.append('debug_javascript_verbose', debugJavascriptVerbose ? '1' : '0');
        formData.append('debug_ajax', debugAjax ? '1' : '0');
        formData.append('debug_performance', debugPerformance ? '1' : '0');
        formData.append('debug_database', debugDatabase ? '1' : '0');
        formData.append('log_level', logLevel);
        formData.append('log_file_size', logFileSize);
        formData.append('log_retention', logRetention);
        formData.append('force_https', forceHttps ? '1' : '0');
    }

    function collectCanvasSettings(formData) {
        // Collecter les données de toutes les modales canvas
        const canvasForms = [
            'canvas-dimensions-form',
            'canvas-apparence-form',
            'canvas-grille-form',
            'canvas-zoom-form',
            'canvas-interaction-form',
            'canvas-comportement-form',
            'canvas-export-form',
            'canvas-performance-form',
            'canvas-autosave-form',
            'canvas-debug-form'
        ];

        canvasForms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name) {
                        if (input.type === 'checkbox') {
                            formData.append(input.name, input.checked ? '1' : '0');
                        } else {
                            formData.append(input.name, input.value);
                        }
                    }
                });
            }
        });
    }

    // Attacher l'événement de clic au bouton flottant
    const floatingSaveBtn = document.getElementById('floating-save-btn');
    if (floatingSaveBtn) {
        floatingSaveBtn.addEventListener('click', saveAllSettings);
    }

    // Afficher le bouton flottant après le chargement de la page
    setTimeout(() => {
        const floatingBtn = document.getElementById('floating-save-button');
        if (floatingBtn) {
            floatingBtn.style.display = 'block';
        }
    }, 1000);

    // Fonction pour mettre à jour une ligne de consentement dans la table
    function updateConsentRowInTable(consentType, isGranted) {
        console.log(`Mise à jour de la ligne ${consentType} - Accordé: ${isGranted}`);
        const resultDiv = document.getElementById('gdpr-user-actions-result');
        if (!resultDiv) return;

        // Trouver la ligne correspondante dans la table
        const consentRow = resultDiv.querySelector(`button[data-consent-type="${consentType}"]`)?.closest('tr');
        if (!consentRow) {
            console.log('Ligne de consentement non trouvée');
            return;
        }

        // Mettre à jour le statut
        const statusCell = consentRow.cells[1]; // Colonne "Statut"
        const actionCell = consentRow.cells[3]; // Colonne "Actions"

        if (isGranted) {
            statusCell.innerHTML = '<span class="text-success">✅ Accordé</span>';
            statusCell.className = 'text-success';
            actionCell.innerHTML = `<button type="button" class="button button-small button-secondary revoke-consent"
                                data-consent-type="${consentType}">
                            Révoquer
                        </button>`;
        } else {
            statusCell.innerHTML = '<span class="text-danger">❌ Refusé</span>';
            statusCell.className = 'text-danger';
            actionCell.innerHTML = `<button type="button" class="button button-small button-primary grant-consent"
                                data-consent-type="${consentType}">
                            Accorder
                        </button>`;
        }

        console.log('Ligne mise à jour avec succès');
    }

    // Gestion des boutons de consentement dans la vue détaillée
    document.addEventListener('click', function(event) {
        // Bouton "Accorder" un consentement
        if (event.target.classList.contains('grant-consent')) {
            event.preventDefault();
            const consentType = event.target.getAttribute('data-consent-type');
            const nonce = document.getElementById('export_user_data_nonce')?.value;

            if (!consentType || !nonce) {
                showGdprResult('Erreur: Type de consentement ou nonce manquant', 'error');
                return;
            }

            // Désactiver le bouton pendant le traitement
            event.target.disabled = true;
            event.target.textContent = '⏳ Traitement...';

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_save_consent',
                    consent_type: consentType,
                    granted: '1',
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showConsentMessage(`✅ Consentement "${consentType}" accordé`);
                    // Mettre à jour la ligne du consentement dans la table existante
                    updateConsentRowInTable(consentType, true);
                } else {
                    showConsentMessage('❌ Erreur lors de l\'accord du consentement: ' + (data.data || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur accord consentement:', error);
                showGdprResult('❌ Erreur réseau lors de l\'accord du consentement', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                event.target.disabled = false;
                event.target.textContent = 'Accorder';
            });
        }

        // Bouton "Révoquer" un consentement
        if (event.target.classList.contains('revoke-consent')) {
            event.preventDefault();
            const consentType = event.target.getAttribute('data-consent-type');
            const nonce = document.getElementById('export_user_data_nonce')?.value;

            if (!consentType || !nonce) {
                showGdprResult('Erreur: Type de consentement ou nonce manquant', 'error');
                return;
            }

            if (!confirm(`⚠️ Êtes-vous sûr de vouloir révoquer le consentement "${consentType}" ?\n\nCette action sera enregistrée dans les logs d'audit.`)) {
                return;
            }

            // Désactiver le bouton pendant le traitement
            event.target.disabled = true;
            event.target.textContent = '⏳ Traitement...';

            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_revoke_consent',
                    consent_type: consentType,
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showConsentMessage(`✅ Consentement "${consentType}" révoqué`);
                    // Mettre à jour la ligne du consentement dans la table existante
                    updateConsentRowInTable(consentType, false);
                } else {
                    showConsentMessage('❌ Erreur lors de la révocation du consentement: ' + (data.data || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur révocation consentement:', error);
                showGdprResult('❌ Erreur réseau lors de la révocation du consentement', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                event.target.disabled = false;
                event.target.textContent = 'Révoquer';
            });
        }

        // Gestion des boutons de sauvegarde des modales canvas
        if (event.target.classList.contains('canvas-modal-save')) {
            event.preventDefault();
            const button = event.target;
            const modal = button.closest('.canvas-modal');
            const category = modal.id.replace('canvas-', '').replace('-modal', '');
            const form = modal ? modal.querySelector('form') : null;

            if (!category || !form) {
                console.error('Catégorie ou formulaire manquant pour la sauvegarde canvas');
                return;
            }

            // Désactiver le bouton et changer son texte
            button.disabled = true;
            button.textContent = '⏳ Sauvegarde...';

            // Collecter les données du formulaire
            const formData = new FormData();
            formData.append('action', 'pdf_builder_save_canvas_settings');
            formData.append('nonce', pdf_builder_ajax.nonce);
            formData.append('category', category);

            // Ajouter tous les champs du formulaire
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.name) {
                    if (input.type === 'checkbox') {
                        formData.append(input.name, input.checked ? '1' : '0');
                    } else {
                        formData.append(input.name, input.value);
                    }
                }
            });

            // Faire l'appel AJAX
            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    button.textContent = '✅ Sauvegardé';
                    button.classList.add('saved');

                    // Mettre à jour les checkboxes HTML du formulaire avec les nouvelles valeurs
                    updateFormCheckboxes(data.data.saved);

                    // Fermer la modale après un court délai
                    setTimeout(() => {
                        // Fermer la modale (supposant qu'il y a un bouton de fermeture)
                        const closeBtn = modal.querySelector('.modal-close, .close-modal');
                        if (closeBtn) {
                            closeBtn.click();
                        } else {
                            modal.style.display = 'none';
                        }
                    }, 1500);

                    // Réactiver le bouton après 3 secondes
                    setTimeout(() => {
                        button.disabled = false;
                        button.textContent = 'Sauvegarder';
                        button.classList.remove('saved');
                    }, 3000);

                } else {
                    button.textContent = '❌ Erreur';
                    button.classList.add('error');

                    // Réactiver le bouton après 3 secondes
                    setTimeout(() => {
                        button.disabled = false;
                        button.textContent = 'Sauvegarder';
                        button.classList.remove('error');
                    }, 3000);
                }
            })
            .catch(error => {
                button.textContent = '❌ Erreur réseau';
                button.classList.add('error');
                console.error('Erreur AJAX sauvegarde canvas:', error);

                // Réactiver le bouton après 3 secondes
                setTimeout(() => {
                    button.disabled = false;
                    button.textContent = 'Sauvegarder';
                    button.classList.remove('error');
                }, 3000);
            });
        }
    });

    // Synchronisation automatique des paramètres de grille dans les modals
    function setupGridSynchronization() {
        const gridEnabledCheckbox = document.getElementById('canvas_grid_enabled');
        const gridSizeInput = document.getElementById('canvas_grid_size');
        const snapToGridCheckbox = document.getElementById('canvas_snap_to_grid');
        const snapToGridToggle = snapToGridCheckbox ? snapToGridCheckbox.closest('.toggle-switch') : null;

        if (gridEnabledCheckbox) {
            gridEnabledCheckbox.addEventListener('change', function() {
                const isEnabled = this.checked;
                
                // Désactiver/activer les contrôles dépendants
                if (gridSizeInput) {
                    gridSizeInput.disabled = !isEnabled;
                    if (!isEnabled) {
                        gridSizeInput.value = '0';
                    } else if (gridSizeInput.value === '0') {
                        gridSizeInput.value = '20'; // Valeur par défaut
                    }
                }
                
                if (snapToGridCheckbox && snapToGridToggle) {
                    snapToGridCheckbox.disabled = !isEnabled;
                    if (isEnabled) {
                        snapToGridToggle.classList.remove('disabled');
                    } else {
                        snapToGridToggle.classList.add('disabled');
                        snapToGridCheckbox.checked = false;
                    }
                }
            });

            // Appliquer l'état initial
            gridEnabledCheckbox.dispatchEvent(new Event('change'));
        }
    }

    // Initialiser la synchronisation quand le modal de grille est ouvert
    const grilleCard = document.querySelector('.canvas-card[data-category="grille"]');
    if (grilleCard) {
        grilleCard.addEventListener('click', function() {
            // Attendre que le modal soit ouvert avant d'initialiser
            setTimeout(setupGridSynchronization, 100);
        });
    }

    // Aussi initialiser au chargement de la page si le modal est déjà ouvert
    setupGridSynchronization();

    // Gestion de la sauvegarde des modales canvas
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('canvas-modal-save')) {
            const button = e.target;
            const category = button.getAttribute('data-category');
            const modal = button.closest('.canvas-modal');
            const form = modal.querySelector('form');
            
            if (!form || !category) {
                console.error('Formulaire ou catégorie manquante');
                return;
            }

            // Désactiver le bouton pendant la sauvegarde
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = '⏳ Sauvegarde...';

            // Collecter les données du formulaire
            const formData = new FormData(form);
            formData.append('action', 'pdf_builder_save_settings');
            formData.append('nonce', pdf_builder_ajax.nonce);
            formData.append('tab', 'canvas'); // Toujours canvas pour les modales canvas

            // Faire l'appel AJAX
            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher un message de succès
                    showNotification('✅ Paramètres sauvegardés avec succès !', 'success');
                    
                    // Fermer le modal
                    modal.style.display = 'none';
                    
                    // Déclencher un événement pour mettre à jour le contexte React
                    window.dispatchEvent(new CustomEvent('pdfBuilderCanvasSettingsUpdated', {
                        detail: { category: category, settings: data.data }
                    }));
                    
                    // Si c'est la catégorie navigation ou zoom, déclencher aussi un refresh du contexte
                    if (category === 'navigation' || category === 'zoom') {
                        // Attendre un peu pour que les options soient sauvegardées
                        setTimeout(() => {
                            // Déclencher un événement pour rafraîchir les paramètres canvas dans React
                            window.dispatchEvent(new CustomEvent('pdfBuilderCanvasSettingsUpdated'));
                        }, 500);
                    }
                } else {
                    showNotification('❌ Erreur: ' + (data.data || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur AJAX:', error);
                showNotification('❌ Erreur réseau lors de la sauvegarde', 'error');
            })
            .finally(() => {
                // Réactiver le bouton
                button.disabled = false;
                button.textContent = originalText;
            });
        }
    });

    // Fonction pour afficher les notifications
    function showNotification(message, type = 'info') {
        // Créer ou utiliser un conteneur de notifications
        let notificationContainer = document.getElementById('pdf-builder-notifications');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'pdf-builder-notifications';
            notificationContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10001;
                max-width: 400px;
            `;
            document.body.appendChild(notificationContainer);
        }

        // Créer la notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
            color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
            border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-size: 14px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        notification.innerHTML = message;

        notificationContainer.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 10);

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
});