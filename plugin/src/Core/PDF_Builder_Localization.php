<?php
/**
 * PDF Builder Pro - Système de localisation et internationalisation
 * Gère les traductions, langues et localisation du contenu
 */

class PDF_Builder_Localization {
    private static $instance = null;

    // Domaine de texte
    const TEXT_DOMAIN = 'pdf-builder-pro';

    // Langues supportées
    private $supported_languages = [
        'en_US' => 'English',
        'fr_FR' => 'Français',
        'es_ES' => 'Español',
        'de_DE' => 'Deutsch',
        'it_IT' => 'Italiano',
        'pt_BR' => 'Português (Brasil)',
        'ru_RU' => 'Русский',
        'ja_JP' => '日本語',
        'zh_CN' => '中文 (简体)',
        'ar_AR' => 'العربية'
    ];

    // Locale actuelle
    private $current_locale;

    // Cache des traductions
    private $translation_cache = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->current_locale = get_locale();
        $this->init_hooks();
        // $this->load_textdomain(); // REMOVED: Ne pas charger trop tôt
    }

    private function init_hooks() {
        // Chargement des langues
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('wp_ajax_pdf_builder_change_locale', [$this, 'change_locale_ajax']);
        add_action('wp_ajax_pdf_builder_get_translations', [$this, 'get_translations_ajax']);

        // Filtres de localisation
        add_filter('locale', [$this, 'filter_locale']);
        add_filter('pdf_builder_translate', [$this, 'translate_text'], 10, 3);

        // Actions d'administration
        add_action('admin_init', [$this, 'register_settings']);
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_translation_cache']);
    }

    /**
     * Charge le domaine de texte
     */
    public function load_textdomain() {
        $locale = apply_filters('plugin_locale', $this->current_locale, self::TEXT_DOMAIN);

        $mofile = PDF_BUILDER_PLUGIN_DIR . 'resources/languages/' . self::TEXT_DOMAIN . '-' . $locale . '.mo';

        if (file_exists($mofile)) {
            load_textdomain(self::TEXT_DOMAIN, $mofile);
        } else {
            // Fallback vers la langue par défaut
            load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(plugin_basename(PDF_BUILDER_PLUGIN_FILE)) . '/resources/languages/');
        }
    }

    /**
     * Filtre la locale
     */
    public function filter_locale($locale) {
        $user_locale = get_user_meta(get_current_user_id(), 'pdf_builder_locale', true);

        if ($user_locale && isset($this->supported_languages[$user_locale])) {
            return $user_locale;
        }

        $default_locale = pdf_builder_get_option('pdf_builder_default_locale', 'en_US');

        return $default_locale ?: $locale;
    }

    /**
     * Traduit un texte
     */
    public function translate_text($text, $context = '', $domain = null) {
        $domain = $domain ?: self::TEXT_DOMAIN;

        // Vérifier le cache
        $cache_key = md5($text . $context . $domain . $this->current_locale);

        if (isset($this->translation_cache[$cache_key])) {
            return $this->translation_cache[$cache_key];
        }

        // Traduire le texte
        $translated = __($text, $domain);

        if ($context) {
            $translated = _x($translated, $context, $domain);
        }

        // Mettre en cache
        $this->translation_cache[$cache_key] = $translated;

        return $translated;
    }

    /**
     * Traduit un texte au pluriel
     */
    public function translate_plural($single, $plural, $number, $context = '', $domain = null) {
        $domain = $domain ?: self::TEXT_DOMAIN;

        if ($context) {
            return _nx($single, $plural, $number, $context, $domain);
        }

        return _n($single, $plural, $number, $domain);
    }

    /**
     * Obtient les langues supportées
     */
    public function get_supported_languages() {
        return $this->supported_languages;
    }

    /**
     * Vérifie si une langue est supportée
     */
    public function is_language_supported($locale) {
        return isset($this->supported_languages[$locale]);
    }

    /**
     * Obtient le nom d'une langue
     */
    public function get_language_name($locale) {
        return $this->supported_languages[$locale] ?? $locale;
    }

    /**
     * Change la locale pour l'utilisateur actuel
     */
    public function change_user_locale($locale) {
        if (!$this->is_language_supported($locale)) {
            return false;
        }

        update_user_meta(get_current_user_id(), 'pdf_builder_locale', $locale);

        // Recharger le domaine de texte
        $this->current_locale = $locale;
        $this->load_textdomain();

        // Vider le cache de traductions
        $this->translation_cache = [];

        return true;
    }

    /**
     * Formate une date selon la locale
     */
    public function format_date($timestamp, $format = null) {
        $format = $format ?: pdf_builder_get_option('pdf_builder_date_format', 'Y-m-d');

        // Utiliser date_i18n de WordPress pour la localisation
        return date_i18n($format, $timestamp);
    }

    /**
     * Formate une heure selon la locale
     */
    public function format_time($timestamp, $format = null) {
        $format = $format ?: pdf_builder_get_option('pdf_builder_time_format', 'H:i:s');

        return date_i18n($format, $timestamp);
    }

    /**
     * Formate un nombre selon la locale
     */
    public function format_number($number, $decimals = 0) {
        $locale = pdf_builder_get_option('pdf_builder_number_format', 'en_US');

        // Utiliser number_format_i18n de WordPress
        return number_format_i18n($number, $decimals);
    }

    /**
     * Formate une monnaie selon la locale
     */
    public function format_currency($amount, $currency = 'USD') {
        $formatted = $this->format_number($amount, 2);

        // Ajouter le symbole de devise selon la locale
        $currency_symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$'
        ];

        $symbol = $currency_symbols[$currency] ?? $currency;

        // Positionner le symbole selon la locale
        if (in_array($this->current_locale, ['fr_FR', 'de_DE', 'it_IT', 'es_ES', 'pt_BR'])) {
            return $formatted . ' ' . $symbol;
        }

        return $symbol . $formatted;
    }

    /**
     * Obtient la direction du texte (RTL/LTR)
     */
    public function get_text_direction() {
        $rtl_languages = ['ar_AR', 'he_IL', 'fa_IR', 'ur_PK'];

        if (in_array($this->current_locale, $rtl_languages)) {
            return 'rtl';
        }

        return 'ltr';
    }

    /**
     * Vérifie si le support RTL est activé
     */
    public function is_rtl_enabled() {
        return pdf_builder_get_option('pdf_builder_rtl_support', true);
    }

    /**
     * Obtient les traductions pour JavaScript
     */
    public function get_js_translations() {
        return [
            // Messages d'interface
            'confirm_delete' => $this->translate_text('Êtes-vous sûr de vouloir supprimer cet élément ?', 'confirmation'),
            'loading' => $this->translate_text('Chargement...', 'status'),
            'error' => $this->translate_text('Erreur', 'status'),
            'success' => $this->translate_text('Succès', 'status'),
            'warning' => $this->translate_text('Avertissement', 'status'),
            'info' => $this->translate_text('Information', 'status'),

            // Actions
            'save' => $this->translate_text('Enregistrer', 'action'),
            'cancel' => $this->translate_text('Annuler', 'action'),
            'delete' => $this->translate_text('Supprimer', 'action'),
            'edit' => $this->translate_text('Modifier', 'action'),
            'add' => $this->translate_text('Ajouter', 'action'),
            'close' => $this->translate_text('Fermer', 'action'),

            // Messages de validation
            'required_field' => $this->translate_text('Ce champ est obligatoire', 'validation'),
            'invalid_email' => $this->translate_text('Adresse email invalide', 'validation'),
            'invalid_number' => $this->translate_text('Nombre invalide', 'validation'),
            'too_short' => $this->translate_text('Texte trop court', 'validation'),
            'too_long' => $this->translate_text('Texte trop long', 'validation'),

            // Messages PDF
            'generating_pdf' => $this->translate_text('Génération du PDF...', 'pdf'),
            'pdf_ready' => $this->translate_text('PDF prêt', 'pdf'),
            'pdf_error' => $this->translate_text('Erreur lors de la génération du PDF', 'pdf'),
            'download_pdf' => $this->translate_text('Télécharger le PDF', 'pdf'),

            // Messages de déploiement
            'deploying' => $this->translate_text('Déploiement en cours...', 'deployment'),
            'deploy_success' => $this->translate_text('Déploiement réussi', 'deployment'),
            'deploy_error' => $this->translate_text('Erreur de déploiement', 'deployment'),

            // Messages de sauvegarde
            'backup_progress' => $this->translate_text('Sauvegarde en cours...', 'backup'),
            'backup_complete' => $this->translate_text('Sauvegarde terminée', 'backup'),
            'restore_progress' => $this->translate_text('Restauration en cours...', 'restore'),
            'restore_complete' => $this->translate_text('Restauration terminée', 'restore')
        ];
    }

    /**
     * Exporte les traductions pour un thème enfant ou un plugin
     */
    public function export_translations($locale = null) {
        $locale = $locale ?: $this->current_locale;

        $translations = [];

        // Collecter toutes les chaînes de traduction du code
        $translations = $this->scan_for_translatable_strings();

        // Créer le fichier .po
        $po_content = $this->generate_po_file($translations, $locale);

        return $po_content;
    }

    /**
     * Analyse le code pour trouver les chaînes traduisibles
     */
    private function scan_for_translatable_strings() {
        $strings = [];

        // Analyser les fichiers PHP du plugin
        $files = $this->get_plugin_php_files();

        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Chercher les fonctions de traduction
            $patterns = [
                '/__\s*\(\s*[\'"](.*?)[\'"]\s*,?\s*[\'"]?' . preg_quote(self::TEXT_DOMAIN, '/') . '[\'"]?\s*\)/s',
                '/_e\s*\(\s*[\'"](.*?)[\'"]\s*,?\s*[\'"]?' . preg_quote(self::TEXT_DOMAIN, '/') . '[\'"]?\s*\)/s',
                '/_x\s*\(\s*[\'"](.*?)[\'"]\s*,?\s*[\'"](.*?)[\'"]\s*,?\s*[\'"]?' . preg_quote(self::TEXT_DOMAIN, '/') . '[\'"]?\s*\)/s',
                '/_n\s*\(\s*[\'"](.*?)[\'"]\s*,?\s*[\'"](.*?)[\'"]\s*,?\s*\$\w+\s*,?\s*[\'"]?' . preg_quote(self::TEXT_DOMAIN, '/') . '[\'"]?\s*\)/s'
            ];

            foreach ($patterns as $pattern) {
                preg_match_all($pattern, $content, $matches);

                foreach ($matches[1] as $key => $string) {
                    $context = $matches[2][$key] ?? '';
                    $strings[$string] = [
                        'string' => $string,
                        'context' => $context,
                        'file' => basename($file),
                        'line' => 0 // Pourrait être calculé avec plus de précision
                    ];
                }
            }
        }

        return $strings;
    }

    /**
     * Obtient la liste des fichiers PHP du plugin
     */
    private function get_plugin_php_files() {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(PDF_BUILDER_PLUGIN_DIR, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Génère un fichier .po
     */
    private function generate_po_file($translations, $locale) {
        $po_content = "# PDF Builder Pro translations for {$locale}\n";
        $po_content .= "# Generated on " . date('Y-m-d H:i:s') . "\n";
        $po_content .= "\"Language: {$locale}\\n\"\n";
        $po_content .= "\"Plural-Forms: nplurals=2; plural=n != 1;\\n\"\n\n";

        foreach ($translations as $translation) {
            $po_content .= "#: {$translation['file']}\n";

            if ($translation['context']) {
                $po_content .= "msgctxt \"{$translation['context']}\"\n";
            }

            $po_content .= "msgid \"" . addslashes($translation['string']) . "\"\n";
            $po_content .= "msgstr \"\"\n\n";
        }

        return $po_content;
    }

    /**
     * Nettoie le cache de traductions
     */
    public function cleanup_translation_cache() {
        $this->translation_cache = [];

        // Nettoyer les fichiers de cache de traduction si nécessaire
        $cache_dir = WP_CONTENT_DIR . '/cache/pdf-builder-translations/';

        if (is_dir($cache_dir)) {
            $this->delete_directory_recursive($cache_dir);
        }
    }

    /**
     * Supprime un dossier récursivement
     */
    private function delete_directory_recursive($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->delete_directory_recursive($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Sanitise une locale
     */
    public function sanitize_locale($locale) {
        if ($this->is_language_supported($locale)) {
            return $locale;
        }

        return 'en_US'; // Fallback
    }

    /**
     * AJAX - Change la locale
     */
    public function change_locale_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $locale = sanitize_text_field($_POST['locale'] ?? '');

            if (empty($locale)) {
                wp_send_json_error(['message' => 'Locale manquante']);
                return;
            }

            $success = $this->change_user_locale($locale);

            if ($success) {
                wp_send_json_success([
                    'message' => 'Locale changée avec succès',
                    'locale' => $locale,
                    'language_name' => $this->get_language_name($locale)
                ]);
            } else {
                wp_send_json_error(['message' => 'Locale non supportée']);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient les traductions
     */
    public function get_translations_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $translations = $this->get_js_translations();
            $locale_info = [
                'current_locale' => $this->current_locale,
                'language_name' => $this->get_language_name($this->current_locale),
                'text_direction' => $this->get_text_direction(),
                'is_rtl' => $this->get_text_direction() === 'rtl'
            ];

            wp_send_json_success([
                'message' => 'Traductions récupérées',
                'translations' => $translations,
                'locale' => $locale_info
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}

// Fonctions globales de traduction
function pdf_builder_translate($text, $context = '', $domain = null) {
    return PDF_Builder_Localization::get_instance()->translate_text($text, $context, $domain);
}

function pdf_builder_translate_plural($single, $plural, $number, $context = '', $domain = null) {
    return PDF_Builder_Localization::get_instance()->translate_plural($single, $plural, $number, $context, $domain);
}

function pdf_builder_get_supported_languages() {
    return PDF_Builder_Localization::get_instance()->get_supported_languages();
}

function pdf_builder_format_date($timestamp, $format = null) {
    return PDF_Builder_Localization::get_instance()->format_date($timestamp, $format);
}

function pdf_builder_format_time($timestamp, $format = null) {
    return PDF_Builder_Localization::get_instance()->format_time($timestamp, $format);
}

function pdf_builder_format_number($number, $decimals = 0) {
    return PDF_Builder_Localization::get_instance()->format_number($number, $decimals);
}

function pdf_builder_format_currency($amount, $currency = 'USD') {
    return PDF_Builder_Localization::get_instance()->format_currency($amount, $currency);
}

function pdf_builder_get_text_direction() {
    return PDF_Builder_Localization::get_instance()->get_text_direction();
}

function pdf_builder_get_js_translations() {
    return PDF_Builder_Localization::get_instance()->get_js_translations();
}

function pdf_builder_change_locale($locale) {
    return PDF_Builder_Localization::get_instance()->change_user_locale($locale);
}

// Alias pour la compatibilité
function __pdf_builder($text, $context = '') {
    return pdf_builder_translate($text, $context);
}

function _e_pdf_builder($text, $context = '') {
    echo pdf_builder_translate($text, $context);
}

function _n_pdf_builder($single, $plural, $number, $context = '') {
    return pdf_builder_translate_plural($single, $plural, $number, $context);
}

// Initialiser le système de localisation
add_action('plugins_loaded', function() {
    PDF_Builder_Localization::get_instance();
});




