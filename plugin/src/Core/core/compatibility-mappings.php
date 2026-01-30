<?php
/**
 * PDF Builder Compatibility Mappings
 *
 * Centralise toutes les configurations de compatibilité navigateur et version
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Compatibility_Mappings {

    // ==========================================
    // COMPATIBILITÉ NAVIGATEUR
    // ==========================================

    private static $browser_compatibility = [
        'chrome' => [
            'min_version' => '70',
            'recommended_version' => '90',
            'supported' => true,
            'features' => [
                'canvas' => true,
                'webgl' => true,
                'css_grid' => true,
                'css_flexbox' => true,
                'es6_modules' => true,
                'async_await' => true,
                'web_workers' => true,
                'indexeddb' => true,
                'service_workers' => true,
                'webassembly' => true
            ]
        ],

        'firefox' => [
            'min_version' => '65',
            'recommended_version' => '85',
            'supported' => true,
            'features' => [
                'canvas' => true,
                'webgl' => true,
                'css_grid' => true,
                'css_flexbox' => true,
                'es6_modules' => true,
                'async_await' => true,
                'web_workers' => true,
                'indexeddb' => true,
                'service_workers' => true,
                'webassembly' => false
            ]
        ],

        'safari' => [
            'min_version' => '12',
            'recommended_version' => '14',
            'supported' => true,
            'features' => [
                'canvas' => true,
                'webgl' => true,
                'css_grid' => true,
                'css_flexbox' => true,
                'es6_modules' => true,
                'async_await' => true,
                'web_workers' => true,
                'indexeddb' => true,
                'service_workers' => true,
                'webassembly' => false
            ]
        ],

        'edge' => [
            'min_version' => '79',
            'recommended_version' => '90',
            'supported' => true,
            'features' => [
                'canvas' => true,
                'webgl' => true,
                'css_grid' => true,
                'css_flexbox' => true,
                'es6_modules' => true,
                'async_await' => true,
                'web_workers' => true,
                'indexeddb' => true,
                'service_workers' => true,
                'webassembly' => true
            ]
        ],

        'opera' => [
            'min_version' => '60',
            'recommended_version' => '75',
            'supported' => true,
            'features' => [
                'canvas' => true,
                'webgl' => true,
                'css_grid' => true,
                'css_flexbox' => true,
                'es6_modules' => true,
                'async_await' => true,
                'web_workers' => true,
                'indexeddb' => true,
                'service_workers' => true,
                'webassembly' => true
            ]
        ],

        'ie' => [
            'min_version' => null,
            'recommended_version' => null,
            'supported' => false,
            'features' => [
                'canvas' => false,
                'webgl' => false,
                'css_grid' => false,
                'css_flexbox' => false,
                'es6_modules' => false,
                'async_await' => false,
                'web_workers' => false,
                'indexeddb' => false,
                'service_workers' => false,
                'webassembly' => false
            ]
        ]
    ];

    // ==========================================
    // COMPATIBILITÉ WORDPRESS
    // ==========================================

    private static $wordpress_compatibility = [
        'min_version' => '5.0',
        'recommended_version' => '6.0',
        'tested_up_to' => '6.4',
        'required_php' => '7.2',
        'recommended_php' => '8.0',

        'required_extensions' => [
            'gd' => '2.0',
            'mbstring' => '1.0',
            'json' => '1.0',
            'zip' => '1.0'
        ],

        'optional_extensions' => [
            'imagick' => '3.0',
            'exif' => '1.0',
            'fileinfo' => '1.0'
        ]
    ];

    // ==========================================
    // POLYFILLS ET FALLBACKS
    // ==========================================

    private static $polyfills = [
        'es6_promise' => [
            'condition' => '!window.Promise',
            'url' => 'https://cdn.jsdelivr.net/npm/es6-promise@4/dist/es6-promise.auto.min.js',
            'fallback' => 'Polyfill pour les Promises ES6'
        ],

        'fetch' => [
            'condition' => '!window.fetch',
            'url' => 'https://cdn.jsdelivr.net/npm/whatwg-fetch@3.6.2/dist/fetch.umd.min.js',
            'fallback' => 'Polyfill pour l\'API Fetch'
        ],

        'url_search_params' => [
            'condition' => '!window.URLSearchParams',
            'url' => 'https://cdn.jsdelivr.net/npm/url-search-params-polyfill@8.1.1/index.min.js',
            'fallback' => 'Polyfill pour URLSearchParams'
        ],

        'intersection_observer' => [
            'condition' => '!window.IntersectionObserver',
            'url' => 'https://cdn.jsdelivr.net/npm/intersection-observer@0.12.0/intersection-observer.min.js',
            'fallback' => 'Polyfill pour IntersectionObserver'
        ],

        'resize_observer' => [
            'condition' => '!window.ResizeObserver',
            'url' => 'https://cdn.jsdelivr.net/npm/resize-observer-polyfill@1.5.1/dist/ResizeObserver.min.js',
            'fallback' => 'Polyfill pour ResizeObserver'
        ],

        'webgl' => [
            'condition' => '!function(){try{var c=document.createElement("canvas");return!!(c.getContext("webgl")||c.getContext("experimental-webgl"))}catch(e){return false}}()',
            'fallback' => 'WebGL non supporté, utilisation du rendu Canvas 2D'
        ]
    ];

    // ==========================================
    // FONCTIONNALITÉS DÉGRADÉES
    // ==========================================

    private static $fallback_features = [
        'webgl_accelerated_rendering' => [
            'fallback' => 'canvas_2d_rendering',
            'message' => 'Rendu accéléré WebGL non disponible, utilisation du rendu Canvas 2D'
        ],

        'css_grid_layout' => [
            'fallback' => 'flexbox_layout',
            'message' => 'CSS Grid non supporté, utilisation de Flexbox'
        ],

        'web_workers' => [
            'fallback' => 'main_thread_processing',
            'message' => 'Web Workers non supportés, traitement dans le thread principal'
        ],

        'service_workers' => [
            'fallback' => 'no_caching',
            'message' => 'Service Workers non supportés, pas de cache hors ligne'
        ],

        'webassembly' => [
            'fallback' => 'javascript_fallback',
            'message' => 'WebAssembly non supporté, utilisation des fonctions JavaScript'
        ],

        'indexeddb' => [
            'fallback' => 'localstorage_fallback',
            'message' => 'IndexedDB non supporté, utilisation de LocalStorage'
        ]
    ];

    // ==========================================
    // DETECTION DE FONCTIONNALITÉS
    // ==========================================

    private static $feature_detection = [
        'canvas_support' => [
            'test' => 'function(){var c=document.createElement("canvas");return!!(c.getContext&&c.getContext("2d"))}()',
            'fallback_message' => 'Canvas n\'est pas supporté par ce navigateur'
        ],

        'webgl_support' => [
            'test' => 'function(){try{var c=document.createElement("canvas");return!!(c.getContext("webgl")||c.getContext("experimental-webgl"))}catch(e){return false}}()',
            'fallback_message' => 'WebGL n\'est pas supporté par ce navigateur'
        ],

        'svg_support' => [
            'test' => '!!document.createElementNS&&!!document.createElementNS("http://www.w3.org/2000/svg","svg").createSVGRect',
            'fallback_message' => 'SVG n\'est pas supporté par ce navigateur'
        ],

        'css_transforms' => [
            'test' => 'function(){var p=["transform","WebkitTransform","MozTransform","OTransform","msTransform"];for(var i in p)if(document.body.style[p[i]]!==undefined)return true;return false}()',
            'fallback_message' => 'Les transformations CSS ne sont pas supportées'
        ],

        'css_gradients' => [
            'test' => 'function(){var p=["linear-gradient","-webkit-linear-gradient","-moz-linear-gradient","-o-linear-gradient"];for(var i in p)if(document.body.style.backgroundImage=p[i]+"(top,#000,#fff)")return true;return false}()',
            'fallback_message' => 'Les dégradés CSS ne sont pas supportés'
        ],

        'localstorage_support' => [
            'test' => 'function(){try{var t="test";localStorage.setItem(t,t);localStorage.removeItem(t);return true}catch(e){return false}}()',
            'fallback_message' => 'LocalStorage n\'est pas disponible'
        ],

        'websockets_support' => [
            'test' => '!!window.WebSocket',
            'fallback_message' => 'WebSockets ne sont pas supportés'
        ],

        'file_api_support' => [
            'test' => '!!(window.File&&window.FileReader&&window.FileList&&window.Blob)',
            'fallback_message' => 'L\'API File n\'est pas supportée'
        ]
    ];

    // ==========================================
    // MESSAGES DE COMPATIBILITÉ
    // ==========================================

    private static $compatibility_messages = [
        'browser_not_supported' => [
            'title' => 'Navigateur non supporté',
            'message' => 'Votre navigateur n\'est pas entièrement supporté. Certaines fonctionnalités peuvent ne pas fonctionner correctement.',
            'suggestions' => [
                'Utilisez une version plus récente de Chrome, Firefox, Safari ou Edge',
                'Mettez à jour votre navigateur vers la dernière version',
                'Activez JavaScript dans les paramètres de votre navigateur'
            ]
        ],

        'feature_not_supported' => [
            'title' => 'Fonctionnalité non supportée',
            'message' => 'Une fonctionnalité requise n\'est pas supportée par votre navigateur.',
            'suggestions' => [
                'Essayez avec un navigateur différent',
                'Mettez à jour vers une version plus récente',
                'Certaines fonctionnalités peuvent être limitées'
            ]
        ],

        'performance_warning' => [
            'title' => 'Avertissement de performance',
            'message' => 'Votre navigateur peut avoir des performances réduites avec ce contenu.',
            'suggestions' => [
                'Fermez les autres onglets du navigateur',
                'Redémarrez votre navigateur',
                'Utilisez un ordinateur plus puissant si possible'
            ]
        ],

        'mobile_warning' => [
            'title' => 'Support mobile limité',
            'message' => 'L\'éditeur n\'est pas optimisé pour les appareils mobiles.',
            'suggestions' => [
                'Utilisez un ordinateur de bureau pour une meilleure expérience',
                'Certaines fonctionnalités peuvent ne pas être disponibles',
                'L\'interface peut être difficile à utiliser sur petit écran'
            ]
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir la compatibilité navigateur
     */
    public static function get_browser_compatibility() {
        return self::$browser_compatibility;
    }

    /**
     * Obtenir la compatibilité pour un navigateur spécifique
     */
    public static function get_browser_compatibility_info($browser) {
        return self::$browser_compatibility[$browser] ?? null;
    }

    /**
     * Vérifier si un navigateur est supporté
     */
    public static function is_browser_supported($browser, $version = null) {
        $info = self::get_browser_compatibility_info($browser);

        if (!$info || !$info['supported']) {
            return false;
        }

        if ($version && $info['min_version']) {
            return version_compare($version, $info['min_version'], '>=');
        }

        return true;
    }

    /**
     * Obtenir la compatibilité WordPress
     */
    public static function get_wordpress_compatibility() {
        return self::$wordpress_compatibility;
    }

    /**
     * Vérifier la compatibilité WordPress
     */
    public static function check_wordpress_compatibility() {
        global $wp_version;

        $compat = self::$wordpress_compatibility;

        $issues = [];

        if (version_compare($wp_version, $compat['min_version'], '<')) {
            $issues[] = "WordPress version {$wp_version} is below minimum required {$compat['min_version']}";
        }

        if (version_compare(PHP_VERSION, $compat['required_php'], '<')) {
            $issues[] = "PHP version " . PHP_VERSION . " is below minimum required {$compat['required_php']}";
        }

        foreach ($compat['required_extensions'] as $ext => $min_version) {
            if (!extension_loaded($ext)) {
                $issues[] = "Required extension '{$ext}' is not loaded";
            } elseif ($min_version && function_exists($ext . '_version')) {
                $current_version = call_user_func($ext . '_version');
                if (version_compare($current_version, $min_version, '<')) {
                    $issues[] = "Extension '{$ext}' version {$current_version} is below minimum required {$min_version}";
                }
            }
        }

        return [
            'compatible' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Obtenir les polyfills
     */
    public static function get_polyfills() {
        return self::$polyfills;
    }

    /**
     * Obtenir les fallbacks de fonctionnalités
     */
    public static function get_fallback_features() {
        return self::$fallback_features;
    }

    /**
     * Obtenir la détection de fonctionnalités
     */
    public static function get_feature_detection() {
        return self::$feature_detection;
    }

    /**
     * Obtenir les messages de compatibilité
     */
    public static function get_compatibility_messages() {
        return self::$compatibility_messages;
    }

    /**
     * Générer le code JavaScript de détection de fonctionnalités
     */
    public static function generate_feature_detection_js() {
        $js = "<script>\n";
        $js .= "window.PDF_BUILDER_FEATURES = {\n";

        $features = [];
        foreach (self::$feature_detection as $feature => $config) {
            $features[] = "    {$feature}: {$config['test']}";
        }

        $js .= implode(",\n", $features);
        $js .= "\n};\n";
        $js .= "</script>\n";

        return $js;
    }

    /**
     * Générer le code JavaScript des polyfills
     */
    public static function generate_polyfills_js() {
        $js = "<script>\n";
        $js .= "(function() {\n";
        $js .= "    var polyfills = [\n";

        $polyfill_list = [];
        foreach (self::$polyfills as $name => $config) {
            if (isset($config['url'])) {
                $polyfill_list[] = "        {name: '{$name}', condition: {$config['condition']}, url: '{$config['url']}'}";
            }
        }

        $js .= implode(",\n", $polyfill_list);
        $js .= "\n    ];\n\n";
        $js .= "    function loadPolyfill(polyfill) {\n";
        $js .= "        if (eval(polyfill.condition)) {\n";
        $js .= "            var script = document.createElement('script');\n";
        $js .= "            script.src = polyfill.url;\n";
        $js .= "            document.head.appendChild(script);\n";
        $js .= "        }\n";
        $js .= "    }\n\n";
        $js .= "    polyfills.forEach(loadPolyfill);\n";
        $js .= "})();\n";
        $js .= "</script>\n";

        return $js;
    }

    /**
     * Détecter le navigateur et sa version
     */
    public static function detect_browser() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $browsers = [
            'chrome' => '/Chrome\/([0-9.]+)/',
            'firefox' => '/Firefox\/([0-9.]+)/',
            'safari' => '/Safari\/([0-9.]+)/',
            'edge' => '/Edge\/([0-9.]+)/',
            'opera' => '/OPR\/([0-9.]+)/'
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $user_agent, $matches)) {
                return [
                    'name' => $browser,
                    'version' => $matches[1],
                    'supported' => self::is_browser_supported($browser, $matches[1])
                ];
            }
        }

        return [
            'name' => 'unknown',
            'version' => '0',
            'supported' => false
        ];
    }

    /**
     * Générer un rapport de compatibilité
     */
    public static function generate_compatibility_report() {
        $browser = self::detect_browser();
        $wp_compat = self::check_wordpress_compatibility();

        $report = [
            'browser' => $browser,
            'wordpress' => $wp_compat,
            'overall_compatible' => $browser['supported'] && $wp_compat['compatible'],
            'warnings' => [],
            'errors' => []
        ];

        if (!$browser['supported']) {
            $report['errors'][] = 'Browser not supported';
        }

        if (!$wp_compat['compatible']) {
            $report['errors'] = array_merge($report['errors'], $wp_compat['issues']);
        }

        // Vérifier les fonctionnalités critiques
        $critical_features = ['canvas_support', 'localstorage_support'];
        foreach ($critical_features as $feature) {
            $config = self::$feature_detection[$feature] ?? null;
            if ($config && strpos($config['test'], 'false') !== false) {
                $report['errors'][] = $config['fallback_message'];
            }
        }

        return $report;
    }
}


