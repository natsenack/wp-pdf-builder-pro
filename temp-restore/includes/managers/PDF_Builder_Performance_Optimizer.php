<?php
/**
 * Optimisation des requêtes WordPress pour réduire le rate limiting
 */

// Réduire le nombre de requêtes simultanées en optimisant le chargement des scripts
add_action('init', function() {
    // Désactiver les scripts non essentiels en admin si pas nécessaire
    if (is_admin() && !wp_doing_ajax()) {
        // Optimiser le chargement des scripts
        add_action('admin_enqueue_scripts', function() {
            // Différer les scripts non critiques
            global $wp_scripts;
            if (isset($wp_scripts->registered['hoverIntent'])) {
                $wp_scripts->registered['hoverIntent']->extra['defer'] = true;
            }
        }, 999);
    }
}, 1);

// Réduire les requêtes AJAX automatiques
add_action('admin_init', function() {
    // Augmenter l'intervalle des heartbeats pour réduire les requêtes
    add_filter('heartbeat_settings', function($settings) {
        $settings['interval'] = 60; // 60 secondes au lieu de 15
        return $settings;
    });
});

// Optimiser les requêtes de votre plugin
add_action('wp_loaded', function() {
    // Mettre en cache certaines requêtes pour éviter les répétitions
    if (!wp_next_scheduled('pdf_builder_cache_cleanup')) {
        wp_schedule_event(time(), 'hourly', 'pdf_builder_cache_cleanup');
    }
});

add_action('pdf_builder_cache_cleanup', function() {
    // Nettoyer les caches temporaires pour éviter l'accumulation
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
});
?>

