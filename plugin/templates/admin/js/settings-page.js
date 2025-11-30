/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gère les interactions AJAX pour les fonctionnalités de cache
 */

jQuery(document).ready(function($) {
    'use strict';

    // ==========================================
    // FONCTIONS DE DEBUG CONDITIONNEL
    // ==========================================

    /**
     * Vérifie si un type de debug spécifique est activé
     * @param {string} type - Type de debug ('javascript', 'ajax', 'performance', etc.)
     * @returns {boolean}
     */
    function isDebugEnabled(type) {
        // Priorité à pdfBuilderCanvasSettings.debug.javascript pour le contrôle global
        if (typeof window.pdfBuilderCanvasSettings !== 'undefined' && typeof window.pdfBuilderCanvasSettings.debug !== 'undefined') {
            // Si debug.javascript est défini dans canvas settings, l'utiliser comme contrôle global
            if (!window.pdfBuilderCanvasSettings.debug.javascript) {
                return false;
            }
            // Si debug.javascript est true, vérifier le type spécifique demandé
            return !!window.pdfBuilderCanvasSettings.debug[type];
        }

        // Fallback vers pdfBuilderAjax.debug pour la compatibilité
        if (!window.pdfBuilderAjax || !window.pdfBuilderAjax.debug) {
            return false;
        }
        return !!window.pdfBuilderAjax.debug[type];
    }

    /**
     * Log conditionnel JavaScript - seulement si le debug JS est activé
     * @param {...any} args - Arguments à logger
     */
    function debugLog(...args) {
        if (isDebugEnabled('javascript')) {
            console.log('[PDF Builder Debug]', ...args);
        }
    }

    /**
     * Log verbeux JavaScript - seulement si le debug JS verbeux est activé
     * @param {...any} args - Arguments à logger
     */
    function debugLogVerbose(...args) {
        if (isDebugEnabled('javascript') && isDebugEnabled('verbose')) {
            console.log('[PDF Builder Debug Verbose]', ...args);
        }
    }

    /**
     * Log AJAX - seulement si le debug AJAX est activé
     * @param {...any} args - Arguments à logger
     */
    function debugLogAjax(...args) {
        if (isDebugEnabled('ajax')) {
            console.log('[PDF Builder AJAX]', ...args);
        }
    }

    /**
     * Log performance - seulement si le debug performance est activé
     * @param {...any} args - Arguments à logger
     */
    function debugLogPerformance(...args) {
        if (isDebugEnabled('performance')) {
            console.log('[PDF Builder Performance]', ...args);
        }
    }

    // Utilitaire pour obtenir le nonce AJAX (priorité à cacheNonce)
    function getAjaxNonce() {
        if (typeof pdfBuilderAjax === 'undefined') return null;
        // Priorité: global dispatcher object `pdf_builder_ajax`, puis ajaxNonce (explicit),
        // puis cacheNonce (maintenance), puis generic nonce
        if (typeof window.pdf_builder_ajax !== 'undefined' && window.pdf_builder_ajax.nonce) {
            return window.pdf_builder_ajax.nonce;
        }
        return pdfBuilderAjax.ajaxNonce || pdfBuilderAjax.cacheNonce || pdfBuilderAjax.nonce || null;
    }

    // Récupérer un nonce frais depuis le serveur via l'action dispatcher
    function fetchFreshAjaxNonce() {
        return new Promise(function(resolve, reject) {
            debugLogAjax('fetchFreshAjaxNonce: requesting fresh nonce from server');
            $.ajax({
                url: (typeof pdfBuilderAjax !== 'undefined' ? pdfBuilderAjax.ajaxurl : (window.pdf_builder_ajax && window.pdf_builder_ajax.ajax_url) ? window.pdf_builder_ajax.ajax_url : '/wp-admin/admin-ajax.php'),
                type: 'POST',
                data: { action: 'pdf_builder_get_fresh_nonce' },
                success: function(resp) {
                    debugLogAjax('fetchFreshAjaxNonce response', resp);
                    if (resp && resp.success && resp.data && resp.data.nonce) {
                        // store into pdfBuilderAjax if available
                        if (typeof pdfBuilderAjax !== 'undefined') {
                            pdfBuilderAjax.ajaxNonce = resp.data.nonce;
                        }
                        if (typeof window.pdf_builder_ajax !== 'undefined') {
                            window.pdf_builder_ajax.nonce = resp.data.nonce;
                        }
                        debugLogAjax('fetchFreshAjaxNonce: received nonce', resp.data.nonce);
                        resolve(resp.data.nonce);
                    } else {
                        reject(new Error('Impossible de récupérer le nonce'));
                    }
                },
                error: function(xhr, status, err) {
                    debugLogAjax('fetchFreshAjaxNonce error', status, err, xhr && xhr.responseText);
                    reject(err || status);
                }
            });
        });
    }

    // ==========================================
    // FIN FONCTIONS DE DEBUG
    // ==========================================



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

        // Faire l'appel AJAX (récupérer un nonce frais si besoin)
        fetchFreshAjaxNonce().then(function(nonce) {
            debugLogAjax('pdf_builder_test_cache_integration', { action: 'pdf_builder_test_cache_integration', nonce: nonce, url: pdfBuilderAjax.ajaxurl });
            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_test_cache_integration',
                    nonce: nonce
                },
                timeout: 30000, // 30 secondes timeout
                success: function(response) {
                    debugLogAjax('pdf_builder_test_cache_integration success', response);
                if (response.success) {
                    $results.html('<span style="color: #28a745;">✅ Test réussi</span>');
                    $output.html('<pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;">' +
                        JSON.stringify(response.data, null, 2) + '</pre>').show();
                } else {
                    $results.html('<span style="color: #dc3545;">❌ Test échoué</span>');
                    $output.html('<div style="color: #dc3545;">Erreur: ' + (response.data || 'Erreur inconnue') + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                debugLogAjax('pdf_builder_test_cache_integration error', status, error, xhr && xhr.responseText);
                $results.html('<span style="color: #dc3545;">❌ Erreur de connexion</span>');
                $output.html('<div style="color: #dc3545;">Erreur AJAX: ' + error + '</div>').show();
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).text('🧪 Tester l\'intégration du cache');
            }
            });
        }).catch(function(err){
            $results.html('<span style="color: #dc3545;">❌ Impossible d\'obtenir nonce</span>');
            $output.html('<div style="color: #dc3545;">Erreur: ' + (err && err.message ? err.message : err) + '</div>').show();
            $button.prop('disabled', false).text('🧪 Tester l\'intégration du cache');
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

        // Faire l'appel AJAX (récupérer un nonce frais si besoin)
        fetchFreshAjaxNonce().then(function(nonce) {
            debugLogAjax('pdf_builder_clear_cache', { action: 'pdf_builder_clear_cache', nonce: nonce, url: pdfBuilderAjax.ajaxurl });
            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_clear_cache',
                    nonce: nonce
                },
                timeout: 60000, // 60 secondes timeout pour le nettoyage
                success: function(response) {
                    debugLogAjax('pdf_builder_clear_cache success', response);
                if (response.success) {
                    $results.html('<span style="color: #28a745;">✅ Cache vidé avec succès</span>');

                    // Mettre à jour les métriques du cache en temps réel
                    setTimeout(function() {
                        updateCacheMetrics();
                        location.reload();
                    }, 2000);
                } else {
                    $results.html('<span style="color: #dc3545;">❌ Échec du nettoyage</span>');
                    alert('Erreur lors du nettoyage du cache: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                debugLogAjax('pdf_builder_clear_cache error', status, error, xhr && xhr.responseText);
                $results.html('<span style="color: #dc3545;">❌ Erreur de connexion</span>');
                alert('Erreur AJAX lors du nettoyage: ' + error);
            },
            complete: function() {
                // Réactiver le bouton
                $button.prop('disabled', false).text('🗑️ Vider tout le cache');
            }
            });
        }).catch(function(err){
            $results.html('<span style="color: #dc3545;">❌ Impossible d\'obtenir nonce</span>');
            alert('Erreur: ' + (err && err.message ? err.message : err));
            $button.prop('disabled', false).text('🗑️ Vider tout le cache');
        });
    });

    // Gestion des toggles avec confirmation pour les paramètres critiques
    $('#systeme input[name="cache_enabled"]').on('change', function() {
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
        const $cacheTable = $('#systeme input[name="cache_enabled"]').closest('table.form-table');
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
    const initialCacheEnabled = $('#systeme input[name="cache_enabled"]').is(':checked');
    toggleCacheOptions(initialCacheEnabled);
    updateCacheStatus(initialCacheEnabled);

    // Fonction pour initialiser les métriques du cache de manière sécurisée
    function initializeCacheMetrics() {
        debugLog('initializeCacheMetrics called');
        debugLog('pdfBuilderAjax available?', typeof pdfBuilderAjax !== 'undefined');
        if (typeof pdfBuilderAjax !== 'undefined') {
            debugLog('pdfBuilderAjax object:', pdfBuilderAjax);
        }

        if (typeof pdfBuilderAjax !== 'undefined' && pdfBuilderAjax.ajaxurl && pdfBuilderAjax.nonce) {
            debugLog('pdfBuilderAjax ready, calling updateCacheMetrics');
            updateCacheMetrics();
        } else {
            debugLog('pdfBuilderAjax not ready, retrying in 500ms...');
            setTimeout(initializeCacheMetrics, 500);
        }
    }

    // Mettre à jour les métriques du cache au chargement (avec vérification de disponibilité)
    initializeCacheMetrics();

    // Mettre à jour les métriques toutes les 30 secondes (avec vérification)
    setInterval(function() {
        if (typeof pdfBuilderAjax !== 'undefined' && pdfBuilderAjax.ajaxurl && pdfBuilderAjax.nonce) {
            updateCacheMetrics();
        } else {
            debugLog('Skipping cache metrics update - pdfBuilderAjax not available');
        }
    }, 30000);

    // Fonction pour mettre à jour les métriques du cache en temps réel
    function updateCacheMetrics() {
        debugLogAjax('updateCacheMetrics called');

        // Vérifier que pdfBuilderAjax est disponible
        if (typeof pdfBuilderAjax === 'undefined') {
            debugLog('pdfBuilderAjax not available');
            return;
        }

        debugLogAjax('Making AJAX call to:', pdfBuilderAjax.ajaxurl);
        debugLogAjax('Using nonce:', pdfBuilderAjax.nonce);

        // Faire l'appel AJAX pour récupérer les métriques
        $.ajax({
            url: pdfBuilderAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_get_cache_metrics',
                nonce: getAjaxNonce()
            },
            success: function(response) {
                debugLogAjax('updateCacheMetrics success', response);
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
                    debugLogAjax('updateCacheMetrics response not successful', response);
                }
            },
            error: function(xhr, status, error) {
                debugLogAjax('Erreur AJAX updateCacheMetrics:', status, error, xhr.responseText);
                debugLogAjax('Request details:', {
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

        console.log('[PDF Builder] 🗃️ Bouton "Optimiser la base" cliqué');

        const $button = $(this);
        const $results = $('#maintenance-results');

        console.log('[PDF Builder] 🔄 Désactivation du bouton et changement du texte');
        // Désactiver le bouton pendant l'opération
        $button.prop('disabled', true).text('🗃️ Optimisation en cours...');

        console.log('[PDF Builder] 🔑 Récupération d\'un nonce frais...');
        // Vider le cache OPcache avant la requête
        console.log('[PDF Builder] 🧹 Vidage du cache OPcache...');
        $.ajax({
            url: pdfBuilderAjax.ajaxurl.replace('admin-ajax.php', '../wp-content/plugins/wp-pdf-builder-pro/clear_opcache.php'),
            type: 'GET',
            timeout: 5000,
            success: function() {
                console.log('[PDF Builder] ✅ Cache OPcache vidé');
            },
            error: function() {
                console.log('[PDF Builder] ⚠️ Impossible de vider le cache OPcache (normal si pas activé)');
            }
        });

        // Faire l'appel AJAX (récupérer un nonce frais si besoin)
        fetchFreshAjaxNonce().then(function(nonce) {
            console.log('[PDF Builder] ✅ Nonce frais obtenu:', nonce ? nonce.substring(0, 12) + '...' : 'null');
            debugLogAjax('pdf_builder_optimize_database', { action: 'pdf_builder_optimize_database', nonce: nonce, url: pdfBuilderAjax.ajaxurl });

            console.log('[PDF Builder] 📡 Envoi de la requête AJAX pour optimiser la base...');
            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_optimize_database',
                    nonce: nonce
                },
                timeout: 60000, // 60 secondes timeout
                success: function(response) {
                    console.log('[PDF Builder] ✅ Réponse AJAX reçue:', response);
                    debugLogAjax('pdf_builder_optimize_database success', response);
                    if (response.success) {
                        console.log('[PDF Builder] 🎉 Optimisation réussie');
                        $results.html('<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">✅ Base de données optimisée</div>');
                    } else {
                        console.log('[PDF Builder] ❌ Optimisation échouée:', response);
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Échec de l\'optimisation';
                        $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ ' + msg + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('[PDF Builder] ❌ Erreur AJAX:', { status: status, error: error, xhr: xhr });
                    debugLogAjax('pdf_builder_optimize_database error', status, error, xhr && xhr.responseText);
                    var serverMsg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : (xhr.responseText || error);
                    $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Erreur: ' + serverMsg + '</div>');
                },
                complete: function() {
                    console.log('[PDF Builder] 🔄 Réactivation du bouton');
                    // Réactiver le bouton
                    $button.prop('disabled', false).text('🗃️ Optimiser la base');
                }
            });
        }).catch(function(err){
            console.log('[PDF Builder] ❌ Impossible d\'obtenir le nonce:', err);
            $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Impossible d\'obtenir nonce</div>');
            $button.prop('disabled', false).text('🗃️ Optimiser la base');
        });
    });

    // Réparer les templates
    $('#repair-templates-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#maintenance-results');

        // Désactiver le bouton pendant l'opération
        $button.prop('disabled', true).text('🔧 Réparation en cours...');


        // Faire l'appel AJAX (récupérer un nonce frais si besoin)
        fetchFreshAjaxNonce().then(function(nonce) {
            debugLogAjax('pdf_builder_repair_templates', { action: 'pdf_builder_repair_templates', nonce: nonce, url: pdfBuilderAjax.ajaxurl });
            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_repair_templates',
                    nonce: nonce
                },
                timeout: 30000, // 30 secondes timeout
                success: function(response) {
                    debugLogAjax('pdf_builder_repair_templates success', response);
                    if (response.success) {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Templates réparés';
                        $results.html('<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">✅ ' + msg + '</div>');
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Échec de la suppression';
                        $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ ' + msg + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    debugLogAjax('pdf_builder_repair_templates error', status, error, xhr && xhr.responseText);
                    var serverMsg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : (xhr.responseText || error);
                    $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Erreur: ' + serverMsg + '</div>');
                },
                complete: function() {
                    // Réactiver le bouton
                    $button.prop('disabled', false).text('🔧 Réparer les templates');
                }
            });
        }).catch(function(err){
            $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Impossible d\'obtenir nonce</div>');
            $button.prop('disabled', false).text('🔧 Réparer les templates');
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


        // Faire l'appel AJAX (récupérer un nonce frais si besoin)
        fetchFreshAjaxNonce().then(function(nonce) {
            debugLogAjax('pdf_builder_remove_temp_files', { action: 'pdf_builder_remove_temp_files', nonce: nonce, url: pdfBuilderAjax.ajaxurl });
            $.ajax({
                url: pdfBuilderAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_remove_temp_files',
                    nonce: nonce
                },
                timeout: 30000, // 30 secondes timeout
                success: function(response) {
                    if (response.success) {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Fichiers temporaires supprimés';
                        $results.html('<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">✅ ' + msg + '</div>');
                    } else {
                        var msg = (response && response.data && response.data.message) ? response.data.message : 'Échec de la suppression';
                        $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ ' + msg + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var serverMsg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : (xhr.responseText || error);
                    $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Erreur: ' + serverMsg + '</div>');
                },
                complete: function() {
                    // Réactiver le bouton
                    $button.prop('disabled', false).text('🗂️ Supprimer fichiers temp');
                }
            });
        }).catch(function(err){
            $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Impossible d\'obtenir nonce</div>');
            $button.prop('disabled', false).text('🔧 Réparer les templates');
        });
        
    });

    // Créer une sauvegarde
    $('#create-backup-btn').on('click', function(e) {
        e.preventDefault();

        const $button = $(this);
        const $results = $('#backup-results');

        // Désactiver le bouton pendant l'opération
        $button.prop('disabled', true).html('<span>⏳</span> Création en cours...');


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
                debugLogAjax('pdf_builder_create_backup success', response);
                if (response.success) {
                    $results.html('<div style="color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin-top: 10px;">✅ Sauvegarde créée avec succès</div>');
                } else {
                    $results.html('<div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin-top: 10px;">❌ Échec de la sauvegarde</div>');
                }
            },
            error: function(xhr, status, error) {
                debugLogAjax('pdf_builder_create_backup error', status, error, xhr && xhr.responseText);
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
                debugLogAjax('pdf_builder_list_backups success', response);
                if (response.success && response.data.backups && response.data.backups.length > 0) {

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
                    $results.html('<div style="color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin-top: 10px;">⚠️ Aucune sauvegarde trouvée</div>');
                }
            },
            error: function(xhr, status, error) {
                debugLogAjax('pdf_builder_list_backups error', status, error, xhr && xhr.responseText);
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
                        debugLogAjax('pdf_builder_restore_backup success', response, { filename: filename });
                        if (response.success) {
                            // Recharger la page après 2 secondes
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                        }
                    },
                    error: function(xhr, status, error) {
                        debugLogAjax('pdf_builder_restore_backup error', status, error, xhr && xhr.responseText, { filename: filename });
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
                        debugLogAjax('pdf_builder_delete_backup success', response, { filename: filename });
                        if (response.success) {
                            // Recharger la liste des sauvegardes
                            $('#list-backups-btn').trigger('click');
                        } else {
                        }
                    },
                    error: function(xhr, status, error) {
                        debugLogAjax('pdf_builder_delete_backup error', status, error, xhr && xhr.responseText, { filename: filename });
                    },
                    complete: function() {
                        $button.prop('disabled', false).html('<span>🗑️</span>');
                    }
                });
            }
        });
    }

});
