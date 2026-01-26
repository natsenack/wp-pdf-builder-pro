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
        add_action('admin_print_scripts', function() {
            global $wp_scripts;
            if (isset($wp_scripts->registered['general_script'])) {
                $wp_scripts->registered['general_script']->deps[] = 'wp-util';
            }
        });
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

        // Ajouter un script pour réparer wp-api-fetch sur cette page
        add_action('admin_enqueue_scripts', function() {
            ?>
            <script type="text/javascript">
            // Réparer wp.apiFetch pour cette page
            if (typeof wp !== 'undefined' && wp.apiFetch) {
                // S'assurer que les credentials sont inclus
                wp.apiFetch.use(wp.apiFetch.createPreloadingMiddleware());
                wp.apiFetch.use(wp.apiFetch.createRootURLMiddleware(wp.apiSettings.root));
                
                // Intercepter les erreurs 404 sur users/me et les ignorer silencieusement
                const originalFetch = wp.apiFetch;
                wp.apiFetch = function(options) {
                    if (options.path && options.path.includes('/wp/v2/users/me')) {
                        // Pour cette endpoint spécifique, essayer avec credentials explicites
                        options = Object.assign({}, options, {
                            credentials: 'same-origin',
                            headers: Object.assign({}, options.headers, {
                                'X-WP-Nonce': wp.apiSettings.nonce
                            })
                        });
                    }
                    return originalFetch(options).catch(function(error) {
                        if (options.path && options.path.includes('/wp/v2/users/me') && error.code === 'invalid_json') {
                            // Ignorer silencieusement l'erreur pour users/me
                            console.warn('[PDF Builder] Ignored wp-preferences API error for users/me');
                            return Promise.resolve({}); // Retourner un objet vide pour éviter l'erreur
                        }
                        throw error;
                    });
                };
            }
            </script>
            <?php
        }, 5); // Après wp-api mais avant les autres scripts

        // Charger seulement sur la page du PDF Builder
        if ($page !== 'admin.php?page=pdf-builder-react-editor') {
            return;
        }

        // Désactiver wp-preferences qui cause des erreurs REST API sur cette page
        wp_dequeue_script('wp-preferences');
        wp_dequeue_script('wp-preferences-persistence');

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

