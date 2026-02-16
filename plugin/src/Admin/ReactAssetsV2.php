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
        \add_action('admin_enqueue_scripts', [self::class, 'enqueue_scripts'], 1);

        // === DÉSACTIVATION FORTE DE WP-PREFERENCES ===
        // Désactiver wp-preferences AVANT TOUT chargement de scripts
        \add_action('admin_enqueue_scripts', function($page) {
            // Désactiver immédiatement wp-preferences
            \wp_deregister_script('wp-preferences');
            \wp_deregister_script('wp-preferences-persistence');
            \wp_dequeue_script('wp-preferences');
            \wp_dequeue_script('wp-preferences-persistence');

            // Supprimer aussi les styles associés
            \wp_deregister_style('wp-preferences');
            \wp_dequeue_style('wp-preferences');
        }, -999); // Priorité très haute pour s'exécuter en premier

        // Filtre agressif pour supprimer wp-preferences des scripts par défaut
        \add_filter('wp_default_scripts', function($scripts) {
            if (isset($scripts->registered['wp-preferences'])) {
                unset($scripts->registered['wp-preferences']);
            }
            if (isset($scripts->registered['wp-preferences-persistence'])) {
                unset($scripts->registered['wp-preferences-persistence']);
            }
            return $scripts;
        }, -999);

        // Filtre pour les styles par défaut aussi
        \add_filter('wp_default_styles', function($styles) {
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

        \add_action('admin_print_scripts', function() {
            global $wp_scripts;
            if (isset($wp_scripts->registered['general_script'])) {
                $wp_scripts->registered['general_script']->deps[] = 'wp-util';
            }
        });

        // === BLOQUER LES APPELS REST DE WP-PREFERENCES CÔTÉ SERVEUR ===
        \add_filter('rest_pre_dispatch', function($result, $server, $request) {
            $route = $request->get_route();

            // Bloquer les appels à /wp/v2/users/me qui viennent de wp-preferences
            if ($route === '/wp/v2/users/me' && isset($_SERVER['HTTP_REFERER'])) {
                // Vérifier si cela vient d'une page admin
                if (strpos($_SERVER['HTTP_REFERER'], '/wp-admin/') !== false) {
                    // Retourner une réponse vide pour éviter l'erreur 404
                    return new \WP_REST_Response(array(), 200);
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
        \wp_enqueue_script('wp-util');
        \wp_enqueue_script('wp-api');

        // S'assurer que l'objet wp global est disponible
        \add_action('admin_enqueue_scripts', function() {
            ?>
            <script type="text/javascript">
            // S'assurer que l'objet wp est défini avant que d'autres scripts ne s'exécutent
            if (typeof window.wp === 'undefined') {
                window.wp = window.wp || {};
                
            }
            </script>
            <?php
        }, 1); // Priorité 1 pour s'exécuter très tôt

        // === REMPLACEMENT COMPLET DE WP-PREFERENCES ===
        add_action('admin_enqueue_scripts', function() {
            ?>
            <script type="text/javascript">
            // REMPLACEMENT COMPLET DE WP-PREFERENCES - approche agressive
            (function() {
                'use strict';

                console.log('[PDF Builder] Remplacement wp-preferences activé');

                // Fonction pour remplacer wp.preferences
                function replaceWpPreferences() {
                    if (typeof window.wp === 'undefined') {
                        window.wp = {};
                    }

                    // Remplacement complet de wp.preferences
                    window.wp.preferences = {
                        // Méthodes principales
                        get: function(key, defaultValue) {
                            console.log('[PDF Builder] wp.preferences.get appelé:', key);
                            // Utiliser notre système de préférences personnalisé
                            if (typeof window.PDFEditorPreferences !== 'undefined') {
                                return window.PDFEditorPreferences.getPreference(key, defaultValue);
                            }
                            return defaultValue;
                        },

                        set: function(key, value) {
                            console.log('[PDF Builder] wp.preferences.set appelé:', key, value);
                            // Utiliser notre système de préférences personnalisé
                            if (typeof window.PDFEditorPreferences !== 'undefined') {
                                window.PDFEditorPreferences.setPreference(key, value);
                                return window.PDFEditorPreferences.savePreferences();
                            }
                            return Promise.resolve(false);
                        },

                        request: function() {
                            console.log('[PDF Builder] wp.preferences.request appelé - bloqué');
                            // Retourner une promesse résolue vide
                            return Promise.resolve({});
                        },

                        // Autres méthodes pour compatibilité
                        getAll: function() {
                            console.log('[PDF Builder] wp.preferences.getAll appelé');
                            if (typeof window.PDFEditorPreferences !== 'undefined') {
                                return window.PDFEditorPreferences.getAllPreferences();
                            }
                            return {};
                        },

                        save: function() {
                            console.log('[PDF Builder] wp.preferences.save appelé');
                            if (typeof window.PDFEditorPreferences !== 'undefined') {
                                return window.PDFEditorPreferences.savePreferences();
                            }
                            return Promise.resolve(false);
                        }
                    };
                }

                // Remplacer immédiatement
                replaceWpPreferences();

                // Surveiller et remplacer en continu (au cas où wp-preferences se charge après)
                var checkInterval = setInterval(function() {
                    if (window.wp && window.wp.preferences && typeof window.wp.preferences.get !== 'function') {
                        console.log('[PDF Builder] wp.preferences détecté et remplacé');
                        replaceWpPreferences();
                    }
                }, 100);

                // Arrêter la surveillance après 10 secondes
                setTimeout(function() {
                    clearInterval(checkInterval);
                }, 10000);

                // 2. Intercepter TOUS les appels API REST liés aux préférences
                var originalApiFetch = window.wp && window.wp.apiFetch ? window.wp.apiFetch : null;
                if (originalApiFetch) {
                    window.wp.apiFetch = function(options) {
                        if (options && options.path && options.path.indexOf('/wp/v2/users/me') !== -1) {
                            console.log('[PDF Builder] API Fetch bloqué:', options.path);
                            return Promise.resolve({});
                        }
                        return originalApiFetch.apply(this, arguments);
                    };
                }

                // 3. Bloquer les appels fetch vers l'API users
                var originalFetch = window.fetch;
                window.fetch = function(input, init) {
                    if (typeof input === 'string' && input.indexOf('/wp-json/wp/v2/users/me') !== -1) {
                        console.log('[PDF Builder] Fetch bloqué:', input);
                        return Promise.resolve({
                            ok: true,
                            status: 200,
                            json: function() { return Promise.resolve({}); },
                            text: function() { return Promise.resolve('{}'); }
                        });
                    }
                    return originalFetch.apply(this, arguments);
                };

                // 4. Bloquer XMLHttpRequest vers l'API users
                var originalXMLHttpRequest = window.XMLHttpRequest;
                window.XMLHttpRequest = function() {
                    var xhr = new originalXMLHttpRequest();
                    var originalOpen = xhr.open;
                    xhr.open = function(method, url) {
                        if (typeof url === 'string' && url.indexOf('/wp-json/wp/v2/users/me') !== -1) {
                            console.log('[PDF Builder] XMLHttpRequest bloqué:', url);
                            // Ne pas faire l'appel
                            return;
                        }
                        return originalOpen.apply(this, arguments);
                    };
                    return xhr;
                };

                // 5. Désactiver les événements liés aux préférences
                var originalDispatch = window.dispatchEvent;
                window.dispatchEvent = function(event) {
                    if (event && event.type && event.type.indexOf('wp-preferences') !== -1) {
                        console.log('[PDF Builder] Événement wp-preferences bloqué:', event.type);
                        return true; // Prétendre que c'est réussi
                    }
                    return originalDispatch.apply(this, arguments);
                };

                console.log('[PDF Builder] Remplacement wp-preferences terminé');

            })();
            </script>
            <?php
        }, -1000); // Priorité ultra-haute pour s'exécuter en premier

        // Charger seulement sur la page du PDF Builder
        if ($page !== 'admin.php?page=pdf-builder-react-editor') {
            return;
        }

        // Charger la médiathèque WordPress pour les composants qui en ont besoin
        \wp_enqueue_media();
        
        $plugin_url = PDF_BUILDER_PLUGIN_URL;
        $version = '2.0.0';
        
        // CSS
        \wp_enqueue_style(
            'pdf-builder-react-v2',
            $plugin_url . 'assets/css/pdf-builder-react.min.css',
            [],
            $version
        );
        
        // === CRÉER LE NONCE ===
        $nonce = \wp_create_nonce('pdf_builder_nonce');
        
        // ✅ Le nonce est maintenant injecté dans le HEAD via bootstrap.php au hook wp_head
        // Pas besoin de l'injecter à nouveau ici
        
        // Enqueue jQuery normalement
        \wp_enqueue_script('jquery');
        
        // PUIS enregistrer et enqueuer le client preview
        \wp_register_script(
            'pdf-preview-api-client',
            $plugin_url . 'assets/js/pdf-preview-api-client.min.js',
            ['jquery'],
            $version,
            false  // Charger dans le HEAD
        );
        \wp_enqueue_script('pdf-preview-api-client');
        
        // Vendors (React, ReactDOM)
        \wp_enqueue_script(
            'pdf-builder-react-vendors-v2',
            $plugin_url . 'assets/js/react-vendor.min.js',
            ['wp-util'],
            $version,
            true
        );
        
        // App principal - AVEC pdf-preview-api-client COMME DÉPENDANCE
        \wp_enqueue_script(
            'pdf-builder-react-app-v2',
            $plugin_url . 'assets/js/pdf-builder-react.min.js',
            ['pdf-builder-react-vendors-v2', 'wp-util', 'pdf-preview-api-client'],
            $version,
            true
        );
        
        // Wrapper d'initialisation
        \wp_enqueue_script(
            'pdf-builder-react-wrapper-v2',
            $plugin_url . 'assets/js/pdf-builder-react-wrapper.min.js',
            ['pdf-builder-react-app-v2', 'wp-util'],
            $version,
            true
        );
        
        // Scripts utilitaires supplémentaires
        \wp_enqueue_script(
            'pdf-builder-ajax-throttle',
            $plugin_url . 'assets/js/ajax-throttle.min.js',
            ['jquery'],
            $version,
            true
        );
        
        \wp_enqueue_script(
            'pdf-builder-notifications',
            $plugin_url . 'assets/js/notifications.min.js',
            ['jquery'],
            $version,
            true
        );
        
        \wp_enqueue_script(
            'pdf-builder-wrap',
            $plugin_url . 'assets/js/pdf-builder-wrap.min.js',
            ['jquery'],
            $version,
            true
        );
        
        \wp_enqueue_script(
            'pdf-builder-init',
            $plugin_url . 'assets/js/pdf-builder-init.min.js',
            ['jquery'],
            $version,
            true
        );
        
        // Preview system removed - integration disabled
        
        // React init script (dépendance finale)
        \wp_enqueue_script(
            'pdf-builder-react-init',
            $plugin_url . 'assets/js/pdf-builder-react-init.min.js',
            ['pdf-builder-react-wrapper-v2'],
            $version,
            true
        );
    }
}

// Auto-enregister
ReactAssets::register();



