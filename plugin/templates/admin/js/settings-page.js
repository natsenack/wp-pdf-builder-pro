/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gère les interactions AJAX pour les fonctionnalités de cache
 */

jQuery(document).ready(function($) {
    'use strict';

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
                    $results.html('<span style="color: #28a745;">✅ Test réussi</span>');
                    $output.html('<pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;">' +
                        JSON.stringify(response.data, null, 2) + '</pre>').show();
                } else {
                    $results.html('<span style="color: #dc3545;">❌ Test échoué</span>');
                    $output.html('<div style="color: #dc3545;">Erreur: ' + (response.data || 'Erreur inconnue') + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
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
                    $results.html('<span style="color: #28a745;">✅ Cache vidé avec succès</span>');

                    // Recharger la page après 2 secondes pour mettre à jour les métriques
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $results.html('<span style="color: #dc3545;">❌ Échec du nettoyage</span>');
                    alert('Erreur lors du nettoyage du cache: ' + (response.data || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
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

    // Animation des métriques de cache
    function animateMetrics() {
        $('.cache-metric-value').each(function() {
            const $element = $(this);
            const targetValue = parseInt($element.data('value')) || 0;
            const currentValue = parseInt($element.text().replace(/[^\d]/g, '')) || 0;

            if (currentValue !== targetValue) {
                $({ count: currentValue }).animate({ count: targetValue }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $element.text(Math.floor(this.count));
                    }
                });
            }
        });
    }

    // Animer les métriques au chargement de la page
    animateMetrics();

    // Mettre à jour les métriques toutes les 30 secondes
    setInterval(function() {
        // Cette fonction pourrait être étendue pour mettre à jour les métriques en temps réel
        // Pour l'instant, on garde juste l'animation
        animateMetrics();
    }, 30000);

});