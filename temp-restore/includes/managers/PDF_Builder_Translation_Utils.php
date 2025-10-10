<?php
/**
 * Utilitaire de traduction pour PDF Builder Pro
 * Charge les traductions depuis les fichiers .po si .mo n'est pas disponible
 */

class PDF_Builder_Translation_Utils {

    /**
     * Charger une traduction depuis un fichier .po
     *
     * @param string $po_file Chemin vers le fichier .po
     * @return array Tableau des traductions
     */
    public static function load_po_file($po_file) {
        if (!file_exists($po_file)) {
            return array();
        }

        $translations = array();
        $content = file_get_contents($po_file);
        $lines = explode("\n", $content);

        $current_msgid = '';
        $current_msgstr = '';

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'msgid "') === 0) {
                // Sauvegarder la traduction précédente
                if ($current_msgid && $current_msgstr) {
                    $translations[$current_msgid] = $current_msgstr;
                }

                // Extraire le msgid
                $current_msgid = substr($line, 7, -1); // Enlever msgid " et "
                $current_msgstr = '';
            } elseif (strpos($line, 'msgstr "') === 0) {
                // Extraire le msgstr
                $current_msgstr = substr($line, 8, -1); // Enlever msgstr " et "
            } elseif (strpos($line, '"') === 0 && $current_msgstr !== '') {
                // Continuation de msgstr
                $current_msgstr .= substr($line, 1, -1);
            }
        }

        // Sauvegarder la dernière traduction
        if ($current_msgid && $current_msgstr) {
            $translations[$current_msgid] = $current_msgstr;
        }

        return $translations;
    }

    /**
     * Obtenir la traduction pour une chaîne
     *
     * @param string $text Texte à traduire
     * @param string $domain Domaine de traduction
     * @return string Texte traduit ou original
     */
    public static function translate($text, $domain = 'pdf-builder-pro') {
        // Essayer d'abord la fonction WordPress normale
        if (function_exists('__')) {
            $translated = __($text, $domain);
            if ($translated !== $text) {
                return $translated;
            }
        }

        // Fallback vers nos fichiers .po
        $locale = self::get_current_locale();
        $po_file = PDF_BUILDER_PLUGIN_DIR . '/languages/' . $domain . '-' . $locale . '.po';

        $translations = self::load_po_file($po_file);
        return isset($translations[$text]) ? $translations[$text] : $text;
    }

    /**
     * Obtenir la locale actuelle
     *
     * @return string
     */
    private static function get_current_locale() {
        if (function_exists('get_locale')) {
            return get_locale();
        }

        if (defined('WPLANG') && WPLANG) {
            return WPLANG;
        }

        // Pour les tests ou environnement non-WordPress, utiliser HTTP_ACCEPT_LANGUAGE
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $locale = self::parse_accept_language($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if ($locale) {
                return $locale;
            }
        }

        return 'en_US';
    }

    /**
     * Parser la chaîne Accept-Language pour obtenir la locale
     *
     * @param string $accept_language
     * @return string|null
     */
    private static function parse_accept_language($accept_language) {
        // Prendre la première langue préférée
        $languages = explode(',', $accept_language);
        $primary = trim($languages[0]);

        // Extraire la partie langue (avant le tiret)
        $parts = explode('-', $primary);
        $lang = strtolower($parts[0]);

        // Convertir en format WordPress (ex: fr-FR -> fr_FR)
        if (count($parts) > 1) {
            $country = strtoupper($parts[1]);
            return $lang . '_' . $country;
        }

        return $lang . '_' . strtoupper($lang);
    }
}

// Fonctions d'aide pour utiliser les traductions
if (!function_exists('pdf_builder_pro__')) {
    function pdf_builder_pro__($text) {
        return PDF_Builder_Translation_Utils::translate($text, 'pdf-builder-pro');
    }
}

if (!function_exists('pdf_builder_pro_e')) {
    function pdf_builder_pro_e($text) {
        echo PDF_Builder_Translation_Utils::translate($text, 'pdf-builder-pro');
    }
}