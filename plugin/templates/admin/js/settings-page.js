/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gère les interactions AJAX pour les fonctionnalités de cache
 */

jQuery(document).ready(function($) {
    'use strict';

    // Fonction de notification utilisant le système existant
    function showMaintenanceNotification(type, title, message, duration = 5000) {
        // Utiliser le système de notifications existant
        if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
            window.pdfBuilderNotifications.showToast(message, type, duration);
        } else if (window.PDF_Builder_Notification_Manager && window.PDF_Builder_Notification_Manager.show_toast) {
            window.PDF_Builder_Notification_Manager.show_toast(message, type, duration);
        } else {
            // Fallback: utiliser alert si le système de notifications n'est pas disponible
            alert(title + ': ' + message);
        }
    }

    // Test de l'intégration du cache
    $('#test-cache-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#cache-test-results');
        const $output = $('#cache-test-output');

        // Désactiver le bouton pendant le test
        $button.prop('disabled', true).text('🧪 Test en cours...');
        $results.html('<span style="color: #007cba;">Test en cours...</span>');
        $output.hide();

        // Faire l'appel AJAX
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_test_cache_integration',
                nonce: pdfBuilderAjax.nonce
            },
            timeout: 30000, // 30 secondes timeout
            success: function(response) {
                if (response.success) {
                    showMaintenanceNotification('success', 'Test du cache réussi', 'L\'intégration du cache fonctionne correctement.');
                    $results.html('<span style="color: #28a745;">✅ Test réussi</span>');
                    $output.html('<pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;">' +
                        JSON.stringify(response.data, null, 2) + '</pre>').show();
                } else {
                    showMaintenanceNotification('error', 'Test du cache échoué', response.data || 'Erreur inconnue lors du test.');
                    $results.html('<span style="color: #dc3545;">❌ Test échoué</span>');
                    $output.html('<div style="color: #dc3545;">Erreur: ' + (response.data || 'Erreur inconnue') + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de contacter le serveur pour le test du cache.');
                $results.html('<span style="color: #dc3545;">❌ Erreur de connexion</span>');
                $output.html('<div style="color: #dc3545;">Erreur AJAX: ' + error + '</div>').show();
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).text('🧪 Tester l\'intégration du cache');
            }
        });
    });

    // Vider tout le cache
    $('#clear-cache-general-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#clear-cache-general-results');

        // Confirmation
        if (!confirm('Êtes-vous sûr de vouloir vider tout le cache ? Cette action est irréversible.')) {
            return;
        }

        // Désactiver le bouton pendant le nettoyage
        $button.prop('disabled', true).text('🗑️ Nettoyage en cours...');
        $results.html('<span style="color: #007cba;">Nettoyage en cours...</span>');

        // Faire l'appel AJAX
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_clear_all_cache',
                nonce: pdfBuilderAjax.nonce
            },
            timeout: 60000, // 60 secondes timeout pour le nettoyage
            success: function(response) {
                if (response.success) {
                    showMaintenanceNotification('success', 'Cache vidé', 'Toutes les données en cache ont été supprimées avec succès.');
                    $results.html('<span style="color: #28a745;">✅ Cache vidé avec succès</span>');

                    // Mettre à jour les métriques du cache en temps réel
                    setTimeout(function() {
                        updateCacheMetrics();
                        location.reload();
                    }, 2000);
                } else {
                    showMaintenanceNotification('error', 'Échec du nettoyage', 'Erreur lors du nettoyage du cache: ' + (response.data || 'Erreur inconnue'));
                    $results.html('<span style="color: #dc3545;">❌ Échec du nettoyage</span>');
                    alert('Erreur lors du nettoyage du cache: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de contacter le serveur pour le nettoyage du cache.');
                $results.html('<span style="color: #dc3545;">❌ Erreur de connexion</span>');
                alert('Erreur AJAX lors du nettoyage: ' + error);
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).text('🗑️ Vider tout le cache');
            }
        });
    });

    // Gestion des toggles avec confirmation pour les paramètres critiques
    $('input[name="cache_enabled"]').on('change', function() {
        const isEnabled = $(this).is(':checked');
        if (!isEnabled) {
            if (!confirm('Désactiver le cache peut ralentir les performances. Continuer ?')) {
                $(this).prop('checked', true);
                return;
            }
        }

        // Cacher/afficher les options de cache avancées
        toggleCacheOptions(isEnabled);

        // Mettre à jour l'état du cache en temps réel
        updateCacheStatus(isEnabled);
    });

    // Fonction pour cacher/afficher les options de cache
    function toggleCacheOptions(isEnabled) {
        // Sélectionner toutes les lignes de la table sauf la première (Cache activé)
        const $cacheTable = $('input[name="cache_enabled"]').closest('table.form-table');
        const $allRows = $cacheTable.find('tr');
        const $cacheEnabledRow = $allRows.first();

        // Cacher/afficher toutes les lignes sauf la première
        $allRows.not($cacheEnabledRow).toggle(isEnabled);
    }

    // Fonction pour mettre à jour l'état du cache en temps réel
    function updateCacheStatus(isEnabled) {
        // Trouver la section "État du système de cache"
        const $statusSection = $('h4:contains("📊 État du système de cache")').closest('div');

        if ($statusSection.length > 0) {
            // Trouver la grille des métriques
            const $metricsGrid = $statusSection.find('div[style*="display: grid"]');

            if ($metricsGrid.length > 0) {
                // Le troisième div dans la grille est "Cache activé"
                const $cacheStatusDiv = $metricsGrid.children('div').eq(2); // Index 2 = 3ème élément (0-indexed)

                if ($cacheStatusDiv.length > 0) {
                    // Mettre à jour l'indicateur visuel (✅ ou ❌)
                    const $indicator = $cacheStatusDiv.find('div').first();
                    $indicator.css('color', isEnabled ? '#28a745' : '#dc3545');
                    $indicator.text(isEnabled ? '✅' : '❌');

                    // Mettre à jour le texte descriptif
                    const $textDiv = $cacheStatusDiv.find('div').last();
                    if ($textDiv.length > 0) {
                        $textDiv.text(isEnabled ? 'Cache activé' : 'Cache désactivé');
                    }
                }
            }
        }
    }

    // Validation des champs numériques
    $('input[name="cache_max_size"], input[name="cache_ttl"]').on('input', function() {
        const $input = $(this);
        const value = parseInt($input.val());
        const min = parseInt($input.attr('min')) || 0;
        const max = parseInt($input.attr('max')) || Number.MAX_SAFE_INTEGER;

        if (value < min) {
            $input.val(min);
        } else if (value > max) {
            $input.val(max);
        }
    });

    // État initial au chargement de la page
    const initialCacheEnabled = $('input[name="cache_enabled"]').is(':checked');
    toggleCacheOptions(initialCacheEnabled);
    updateCacheStatus(initialCacheEnabled);

    // Mettre à jour les métriques du cache au chargement
    updateCacheMetrics();

    // Mettre à jour les métriques toutes les 30 secondes
    setInterval(function() {
        updateCacheMetrics();
    }, 30000);

    // Fonction pour mettre à jour les métriques du cache en temps réel
    function updateCacheMetrics() {
        console.log('PDF Builder: updateCacheMetrics called');

        // Vérifier que pdfBuilderAjax est disponible
        if (typeof pdfBuilderAjax === 'undefined') {
            console.error('PDF Builder: pdfBuilderAjax not available');
            return;
        }

        // Faire l'appel AJAX pour récupérer les métriques
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_get_cache_metrics',
                nonce: pdfBuilderAjax.nonce
            },
            success: function(response) {
                console.log('PDF Builder: updateCacheMetrics success', response);
                if (response.success && response.data.metrics) {
                    const metrics = response.data.metrics;

                    // Mettre à jour la taille du cache
                    updateMetricValue('Taille du cache', metrics.cache_size);

                    // Mettre à jour le nombre de transients
                    updateMetricValue('Transients actifs', metrics.transient_count);

                    // Mettre à jour l'état du cache (déjà géré par updateCacheStatus)
                    // updateMetricValue('Cache activé', metrics.cache_enabled ? '✅' : '❌');

                    // Mettre à jour le dernier nettoyage
                    updateMetricValue('Dernier nettoyage', metrics.last_cleanup);
                } else {
                    console.warn('PDF Builder: updateCacheMetrics response not successful', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX updateCacheMetrics:', status, error, xhr.responseText);
                console.error('Request details:', {
                    url: pdfBuilderAjax.ajaxurl,
                    nonce: pdfBuilderAjax.nonce,
                    status: xhr.status,
                    responseText: xhr.responseText
                });
            }
        });
    }

    // Fonction utilitaire pour mettre à jour une valeur de métrique
    function updateMetricValue(label, value) {
        // Trouver la section des métriques
        const $statusSection = $('h4:contains("📊 État du système de cache")').closest('div');

        if ($statusSection.length > 0) {
            // Trouver tous les éléments de métriques
            const $metricDivs = $statusSection.find('div[style*="text-align: center"]');

            $metricDivs.each(function() {
                const $textDiv = $(this).find('div').last();
                if ($textDiv.text().trim() === label) {
                    // Mettre à jour la valeur (premier div du conteneur)
                    const $valueDiv = $(this).find('div').first();
                    $valueDiv.text(value);
                    return false; // Sortir de la boucle each
                }
            });
        }
    }

    // ===== ACTIONS DE MAINTENANCE =====

    // Optimiser la base de données
    $('#optimize-db-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#maintenance-results');

        // Désactiver le bouton pendant l'opération
        $button.prop('disabled', true).text('🗃️ Optimisation en cours...');

        showMaintenanceNotification('info', 'Optimisation en cours', 'Optimisation de la base de données en cours...');

        // Faire l'appel AJAX
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_optimize_database',
                nonce: pdfBuilderAjax.nonce
            },
            timeout: 60000, // 60 secondes timeout
            success: function(response) {
                if (response.success) {
                    showMaintenanceNotification('success', 'Base optimisée', 'La base de données a été optimisée avec succès.');
                    $results.html('<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">✅ Base de données optimisée</div>');
                } else {
                    showMaintenanceNotification('error', 'Échec de l\'optimisation', response.data || 'Erreur lors de l\'optimisation de la base.');
                    $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Échec de l\'optimisation</div>');
                }
            },
            error: function(xhr, status, error) {
                showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de contacter le serveur pour l\'optimisation.');
                $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Erreur de connexion</div>');
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).text('🗃️ Optimiser la base');
            }
        });
    });

    // Réparer les templates
    $('#repair-templates-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#maintenance-results');

        // Désactiver le bouton pendant l'opération
        $button.prop('disabled', true).text('🔧 Réparation en cours...');

        showMaintenanceNotification('info', 'Réparation en cours', 'Vérification et réparation des templates en cours...');

        // Faire l'appel AJAX
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_repair_templates',
                nonce: pdfBuilderAjax.nonce
            },
            timeout: 30000, // 30 secondes timeout
            success: function(response) {
                if (response.success) {
                    showMaintenanceNotification('success', 'Templates réparés', 'Les templates ont été vérifiés et réparés avec succès.');
                    $results.html('<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">✅ Templates réparés</div>');
                } else {
                    showMaintenanceNotification('error', 'Échec de la réparation', response.data || 'Erreur lors de la réparation des templates.');
                    $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Échec de la réparation</div>');
                }
            },
            error: function(xhr, status, error) {
                showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de contacter le serveur pour la réparation.');
                $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Erreur de connexion</div>');
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).text('🔧 Réparer les templates');
            }
        });
    });

    // Supprimer les fichiers temporaires
    $('#remove-temp-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#maintenance-results');

        // Confirmation
        if (!confirm('Êtes-vous sûr de vouloir supprimer tous les fichiers temporaires ?')) {
            return;
        }

        // Désactiver le bouton pendant l'opération
        $button.prop('disabled', true).text('🗂️ Suppression en cours...');

        showMaintenanceNotification('info', 'Suppression en cours', 'Suppression des fichiers temporaires en cours...');

        // Faire l'appel AJAX
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_remove_temp_files',
                nonce: pdfBuilderAjax.nonce
            },
            timeout: 30000, // 30 secondes timeout
            success: function(response) {
                if (response.success) {
                    showMaintenanceNotification('success', 'Fichiers supprimés', response.data || 'Les fichiers temporaires ont été supprimés avec succès.');
                    $results.html('<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">✅ Fichiers temporaires supprimés</div>');
                } else {
                    showMaintenanceNotification('error', 'Échec de la suppression', response.data || 'Erreur lors de la suppression des fichiers temporaires.');
                    $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Échec de la suppression</div>');
                }
            },
            error: function(xhr, status, error) {
                showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de contacter le serveur pour la suppression.');
                $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Erreur de connexion</div>');
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).text('🗂️ Supprimer fichiers temp');
            }
        });
    });

    // Créer une sauvegarde
    $('#create-backup-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#backup-results');

        // Désactiver le bouton pendant l'opération
        $button.prop('disabled', true).html('<span>⏳</span> Création en cours...');

        showMaintenanceNotification('info', 'Sauvegarde en cours', 'Création de la sauvegarde en cours...');

        // Faire l'appel AJAX
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_backup',
                nonce: pdfBuilderAjax.nonce
            },
            timeout: 120000, // 2 minutes timeout pour les sauvegardes
            success: function(response) {
                if (response.success) {
                    showMaintenanceNotification('success', 'Sauvegarde créée', 'La sauvegarde a été créée avec succès.');
                    $results.html('<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">✅ Sauvegarde créée avec succès</div>');
                } else {
                    showMaintenanceNotification('error', 'Échec de la sauvegarde', response.data || 'Erreur lors de la création de la sauvegarde.');
                    $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Échec de la sauvegarde</div>');
                }
            },
            error: function(xhr, status, error) {
                showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de contacter le serveur pour la sauvegarde.');
                $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Erreur de connexion</div>');
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).html('<span>📦</span> Créer une sauvegarde');
            }
        });
    });

    // Lister les sauvegardes
    $('#list-backups-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#backup-results');

        // Désactiver le bouton pendant l'opération
        $button.prop('disabled', true).html('<span>⏳</span> Chargement...');

        showMaintenanceNotification('info', 'Chargement en cours', 'Récupération de la liste des sauvegardes...');

        // Faire l'appel AJAX
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_list_backups',
                nonce: pdfBuilderAjax.nonce
            },
            timeout: 30000, // 30 secondes timeout
            success: function(response) {
                if (response.success && response.data.backups && response.data.backups.length > 0) {
                    showMaintenanceNotification('success', 'Sauvegardes listées', response.data.backups.length + ' sauvegarde(s) trouvée(s).');

                    // Créer la liste des sauvegardes
                    let html = '<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">';
                    html += '<h4 style="margin: 0 0 15px 0; color: #155724;">📋 Sauvegardes disponibles (' + response.data.backups.length + ')</h4>';
                    html += '<div style="max-height: 400px; overflow-y: auto;">';

                    response.data.backups.forEach(function(backup) {
                        html += '<div style="display: flex; align-items: center; justify-content: space-between; padding: 10px; margin-bottom: 8px; background: white; border: 1px solid #dee2e6; border-radius: 4px;">';
                        html += '<div style="flex: 1;">';
                        html += '<strong>' + backup.filename + '</strong><br>';
                        html += '<small style="color: #6c757d;">' + backup.modified_human + ' • ' + backup.size_human + ' • ' + backup.type.toUpperCase() + '</small>';
                        html += '</div>';
                        html += '<div style="display: flex; gap: 5px;">';
                        html += '<button class="button button-secondary download-backup-btn" data-filename="' + backup.filename + '" title="Télécharger"><span>📥</span></button>';
                        html += '<button class="button button-primary restore-backup-btn" data-filename="' + backup.filename + '" title="Restaurer"><span>🔄</span></button>';
                        html += '<button class="button button-danger delete-backup-btn" data-filename="' + backup.filename + '" title="Supprimer"><span>🗑️</span></button>';
                        html += '</div>';
                        html += '</div>';
                    });

                    html += '</div>';
                    html += '</div>';

                    $results.html(html);

                    // Attacher les événements aux boutons
                    attachBackupButtonEvents();

                } else {
                    showMaintenanceNotification('warning', 'Aucune sauvegarde', 'Aucune sauvegarde trouvée.');
                    $results.html('<div style="color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin-top: 10px;">⚠️ Aucune sauvegarde trouvée</div>');
                }
            },
            error: function(xhr, status, error) {
                showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de récupérer la liste des sauvegardes.');
                $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Erreur de connexion</div>');
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).html('<span>📋</span> Lister les sauvegardes');
            }
        });
    });

    // Fonction pour attacher les événements aux boutons de sauvegarde
    function attachBackupButtonEvents() {
        // Bouton Télécharger
        $('.download-backup-btn').on('click', function(e) {
            e.preventDefault();
            const filename = $(this).data('filename');
            const $button = $(this);

            if (confirm('Télécharger la sauvegarde "' + filename + '" ?')) {
                $button.prop('disabled', true).html('<span>⏳</span>');

                // Créer un formulaire temporaire pour le téléchargement
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = pdfBuilderAjax.ajaxurl;

                const fields = {
                    action: 'pdf_builder_download_backup',
                    nonce: pdfBuilderAjax.nonce,
                    filename: filename
                };

                for (const key in fields) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);

                $button.prop('disabled', false).html('<span>📥</span>');
            }
        });

        // Bouton Restaurer
        $('.restore-backup-btn').on('click', function(e) {
            e.preventDefault();
            const filename = $(this).data('filename');
            const $button = $(this);

            if (confirm('⚠️ ATTENTION: Restaurer la sauvegarde "' + filename + '" ?\n\nCela écrasera toutes les données actuelles. Êtes-vous sûr ?')) {
                $button.prop('disabled', true).html('<span>⏳</span>');

                $.ajax({
                    url: pdfBuilderAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_restore_backup',
                        nonce: pdfBuilderAjax.nonce,
                        filename: filename
                    },
                    success: function(response) {
                        if (response.success) {
                            showMaintenanceNotification('success', 'Sauvegarde restaurée', 'La sauvegarde a été restaurée avec succès.');
                            // Recharger la page après 2 secondes
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            showMaintenanceNotification('error', 'Erreur de restauration', response.data.message || 'Erreur lors de la restauration.');
                        }
                    },
                    error: function(xhr, status, error) {
                        showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de restaurer la sauvegarde.');
                    },
                    complete: function() {
                        $button.prop('disabled', false).html('<span>🔄</span>');
                    }
                });
            }
        });

        // Bouton Supprimer
        $('.delete-backup-btn').on('click', function(e) {
            e.preventDefault();
            const filename = $(this).data('filename');
            const $button = $(this);

            if (confirm('Supprimer définitivement la sauvegarde "' + filename + '" ?')) {
                $button.prop('disabled', true).html('<span>⏳</span>');

                $.ajax({
                    url: pdfBuilderAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_delete_backup',
                        nonce: pdfBuilderAjax.nonce,
                        filename: filename
                    },
                    success: function(response) {
                        if (response.success) {
                            showMaintenanceNotification('success', 'Sauvegarde supprimée', 'La sauvegarde a été supprimée avec succès.');
                            // Recharger la liste des sauvegardes
                            $('#list-backups-btn').trigger('click');
                        } else {
                            showMaintenanceNotification('error', 'Erreur de suppression', response.data.message || 'Erreur lors de la suppression.');
                        }
                    },
                    error: function(xhr, status, error) {
                        showMaintenanceNotification('error', 'Erreur de connexion', 'Impossible de supprimer la sauvegarde.');
                    },
                    complete: function() {
                        $button.prop('disabled', false).html('<span>🗑️</span>');
                    }
                });
            }
        });
    }

});
