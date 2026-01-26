<?php
/**
 * PDF Builder Pro V2 - Enregistrement des assets React
 * 
 * Ce fichier enregistre les scripts et styles React V2
 * pour être chargés dans WordPress admin
 */

namespace PDFBuilderPro\V2;

class ReactAssets {
    
    public static function register() {
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_scripts'], 1);

        // === DÉSACTIVATION FORTE DE WP-PREFERENCES ===
        // Désactiver wp-preferences AVANT TOUT chargement de scripts
        add_action('admin_enqueue_scripts', function($page) {
            // Désactiver immédiatement wp-preferences
            wp_deregister_script('wp-preferences');
            wp_deregister_script('wp-preferences-persistence');
            wp_dequeue_script('wp-preferences');
            wp_dequeue_script('wp-preferences-persistence');

            // Supprimer aussi les styles associés
            wp_deregister_style('wp-preferences');
            wp_dequeue_style('wp-preferences');
        }, -999); // Priorité très haute pour s'exécuter en premier

        // Filtre agressif pour supprimer wp-preferences des scripts par défaut
        add_filter('wp_default_scripts', function($scripts) {
            if (isset($scripts->registered['wp-preferences'])) {
                unset($scripts->registered['wp-preferences']);
            }
            if (isset($scripts->registered['wp-preferences-persistence'])) {
                unset($scripts->registered['wp-preferences-persistence']);
            }
            return $scripts;
        }, -999);

        // Filtre pour les styles par défaut aussi
        add_filter('wp_default_styles', function($styles) {
            if (isset($styles->registered['wp-preferences'])) {
                unset($styles->registered['wp-preferences']);
            }
            return $styles;
        }, -999);

        // Désactivation supplémentaire lors de l'impression des scripts
        add_action('admin_print_scripts', function() {
            global $wp_scripts, $wp_styles;

            // Supprimer wp-preferences des scripts en file d'attente
            if (isset($wp_scripts->queue)) {
                $wp_scripts->queue = array_filter($wp_scripts->queue, function($script) {
                    return !in_array($script, ['wp-preferences', 'wp-preferences-persistence']);
                });
            }

            // Supprimer wp-preferences des styles en file d'attente
            if (isset($wp_styles->queue)) {
                $wp_styles->queue = array_filter($wp_styles->queue, function($style) {
                    return $style !== 'wp-preferences';
                });
            }

            // Supprimer de la liste des scripts enregistrés
            if (isset($wp_scripts->registered['wp-preferences'])) {
                unset($wp_scripts->registered['wp-preferences']);
            }
            if (isset($wp_scripts->registered['wp-preferences-persistence'])) {
                unset($wp_scripts->registered['wp-preferences-persistence']);
            }

            // Supprimer de la liste des styles enregistrés
            if (isset($wp_styles->registered['wp-preferences'])) {
                unset($wp_styles->registered['wp-preferences']);
            }
        }, -999);

        add_action('admin_print_scripts', function() {
            global $wp_scripts;
            if (isset($wp_scripts->registered['general_script'])) {
                $wp_scripts->registered['general_script']->deps[] = 'wp-util';
            }
        });

        // === BLOQUER LES APPELS REST DE WP-PREFERENCES CÔTÉ SERVEUR ===
        add_filter('rest_pre_dispatch', function($result, $server, $request) {
            $route = $request->get_route();

            // Bloquer les appels à /wp/v2/users/me qui viennent de wp-preferences
            if ($route === '/wp/v2/users/me' && isset($_SERVER['HTTP_REFERER'])) {
                // Vérifier si cela vient d'une page admin
                if (strpos($_SERVER['HTTP_REFERER'], '/wp-admin/') !== false) {
                    // Retourner une réponse vide pour éviter l'erreur 404
                    return new WP_REST_Response(array(), 200);
                }
            }

            return $result;
        }, 1, 3);
    }
    
    /**
     * Enregistre les scripts et styles React V2
     */
    public static function enqueue_scripts($page) {
        // Assurer que wp-util et wp-api sont chargés sur toutes les pages admin
        wp_enqueue_script('wp-util');
        wp_enqueue_script('wp-api');

        // S'assurer que l'objet wp global est disponible
        add_action('admin_enqueue_scripts', function() {
            ?>
            <script type="text/javascript">
            // S'assurer que l'objet wp est défini avant que d'autres scripts ne s'exécutent
            if (typeof window.wp === 'undefined') {
                window.wp = window.wp || {};
                
            }
            </script>
            <?php
        }, 1); // Priorité 1 pour s'exécuter très tôt

        // === BLOQUER WP-PREFERENCES AU NIVEAU JAVASCRIPT ===
        add_action('admin_enqueue_scripts', function() {
            ?>
            <script type="text/javascript">
            // Bloquer wp-preferences au niveau JavaScript - FILET DE SÉCURITÉ
            (function() {
                'use strict';

                // Désactiver wp-preferences avant qu'il ne s'initialise
                if (typeof window.wp !== 'undefined' && typeof window.wp.preferences !== 'undefined') {
                    window.wp.preferences = {
                        get: function() { return {}; },
                        set: function() { return false; },
                        request: function() { return Promise.resolve({}); }
                    };
                }

                // Intercepter les appels à l'API REST pour wp-preferences
                var originalFetch = window.fetch;
                if (typeof window.fetch !== 'undefined') {
                    window.fetch = function(input, init) {
                        // Bloquer les appels à l'API users/me de wp-preferences
                        if (typeof input === 'string' && input.indexOf('/wp-json/wp/v2/users/me') !== -1) {
                            console.warn('[PDF Builder] Bloqué: appel wp-preferences API REST');
                            return Promise.resolve({
                                ok: true,
                                status: 200,
                                json: function() { return Promise.resolve({}); }
                            });
                        }
                        return originalFetch.apply(this, arguments);
                    };
                }

                // Intercepter les appels XMLHttpRequest pour wp-preferences
                var originalXMLHttpRequest = window.XMLHttpRequest;
                if (typeof window.XMLHttpRequest !== 'undefined') {
                    window.XMLHttpRequest = function() {
                        var xhr = new originalXMLHttpRequest();
                        var originalOpen = xhr.open;
                        xhr.open = function(method, url) {
                            // Bloquer les appels à l'API users/me
                            if (typeof url === 'string' && url.indexOf('/wp-json/wp/v2/users/me') !== -1) {
                                console.warn('[PDF Builder] Bloqué: XMLHttpRequest wp-preferences API');
                                // Ne pas faire l'appel
                                return;
                            }
                            return originalOpen.apply(this, arguments);
                        };
                        return xhr;
                    };
                }

                console.log('[PDF Builder] Protection wp-preferences activée');
            })();
            </script>
            <?php
        }, 0); // Priorité 0 pour s'exécuter très tôt

        // Charger seulement sur la page du PDF Builder
        if ($page !== 'admin.php?page=pdf-builder-react-editor') {
            return;
        }

        // Charger la médiathèque WordPress pour les composants qui en ont besoin
        wp_enqueue_media();
        
        $plugin_url = PDF_BUILDER_PLUGIN_URL;
        $version = '2.0.0';
        
        // CSS
        wp_enqueue_style(
            'pdf-builder-react-v2',
            $plugin_url . 'assets/css/pdf-builder-react.min.css',
            [],
            $version
        );
        
        // === CRÉER LE NONCE ===
        $nonce = wp_create_nonce('pdf_builder_nonce');
        
        // ✅ Le nonce est maintenant injecté dans le HEAD via bootstrap.php au hook wp_head
        // Pas besoin de l'injecter à nouveau ici
        
        // Enqueue jQuery normalement
        wp_enqueue_script('jquery');
        
        // PUIS enregistrer et enqueuer le client preview
        wp_register_script(
            'pdf-preview-api-client',
            $plugin_url . 'assets/js/pdf-preview-api-client.min.js',
            ['jquery'],
            $version,
            false  // Charger dans le HEAD
        );
        wp_enqueue_script('pdf-preview-api-client');
        
        // Vendors (React, ReactDOM)
        wp_enqueue_script(
            'pdf-builder-react-vendors-v2',
            $plugin_url . 'assets/js/react-vendor.min.js',
            ['wp-util'],
            $version,
            true
        );
        
        // App principal - AVEC pdf-preview-api-client COMME DÉPENDANCE
        wp_enqueue_script(
            'pdf-builder-react-app-v2',
            $plugin_url . 'assets/js/pdf-builder-react.min.js',
            ['pdf-builder-react-vendors-v2', 'wp-util', 'pdf-preview-api-client'],
            $version,
            true
        );
        
        // Wrapper d'initialisation
        wp_enqueue_script(
            'pdf-builder-react-wrapper-v2',
            $plugin_url . 'assets/js/pdf-builder-react-wrapper.min.js',
            ['pdf-builder-react-app-v2', 'wp-util'],
            $version,
            true
        );
        
        // Scripts utilitaires supplémentaires
        wp_enqueue_script(
            'pdf-builder-ajax-throttle',
            $plugin_url . 'assets/js/ajax-throttle.min.js',
            ['jquery'],
            $version,
            true
        );
        
        wp_enqueue_script(
            'pdf-builder-notifications',
            $plugin_url . 'assets/js/notifications.min.js',
            ['jquery'],
            $version,
            true
        );
        
        wp_enqueue_script(
            'pdf-builder-wrap',
            $plugin_url . 'assets/js/pdf-builder-wrap.min.js',
            ['jquery'],
            $version,
            true
        );
        
        wp_enqueue_script(
            'pdf-builder-init',
            $plugin_url . 'assets/js/pdf-builder-init.min.js',
            ['jquery'],
            $version,
            true
        );
        
        // Integration preview (après que pdf-preview-api-client soit enqueueé)
        wp_enqueue_script(
            'pdf-preview-integration',
            $plugin_url . 'assets/js/pdf-preview-integration.min.js',
            ['jquery', 'pdf-preview-api-client'],
            $version,
            true
        );
        
        // React init script (dépendance finale)
        wp_enqueue_script(
            'pdf-builder-react-init',
            $plugin_url . 'assets/js/pdf-builder-react-init.min.js',
            ['pdf-builder-react-wrapper-v2', 'pdf-preview-integration'],
            $version,
            true
        );
    }
}

// Auto-enregister
ReactAssets::register();

