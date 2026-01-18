<?php
/**
 * PDF Builder Internationalization Mappings
 *
 * Centralise toutes les chaînes de traduction et configurations i18n
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_I18n_Mappings {

    // ==========================================
    // DOMAINES DE TRADUCTION
    // ==========================================

    private static $text_domains = [
        'pdf_builder' => 'pdf-builder-pro',
        'admin' => 'pdf-builder-pro-admin',
        'frontend' => 'pdf-builder-pro-frontend',
        'ajax' => 'pdf-builder-pro-ajax'
    ];

    // ==========================================
    // CHAÎNES DE TRADUCTION PRINCIPALES
    // ==========================================

    private static $translation_strings = [
        // Interface générale
        'pdf_builder_title' => [
            'en' => 'PDF Builder Pro',
            'fr' => 'PDF Builder Pro',
            'es' => 'PDF Builder Pro',
            'de' => 'PDF Builder Pro'
        ],

        'loading' => [
            'en' => 'Loading...',
            'fr' => 'Chargement...',
            'es' => 'Cargando...',
            'de' => 'Laden...'
        ],

        'save' => [
            'en' => 'Save',
            'fr' => 'Enregistrer',
            'es' => 'Guardar',
            'de' => 'Speichern'
        ],

        'cancel' => [
            'en' => 'Cancel',
            'fr' => 'Annuler',
            'es' => 'Cancelar',
            'de' => 'Abbrechen'
        ],

        'delete' => [
            'en' => 'Delete',
            'fr' => 'Supprimer',
            'es' => 'Eliminar',
            'de' => 'Löschen'
        ],

        'edit' => [
            'en' => 'Edit',
            'fr' => 'Modifier',
            'es' => 'Editar',
            'de' => 'Bearbeiten'
        ],

        'add' => [
            'en' => 'Add',
            'fr' => 'Ajouter',
            'es' => 'Agregar',
            'de' => 'Hinzufügen'
        ],

        // Canvas
        'canvas' => [
            'en' => 'Canvas',
            'fr' => 'Canvas',
            'es' => 'Canvas',
            'de' => 'Canvas'
        ],

        'canvas_settings' => [
            'en' => 'Canvas Settings',
            'fr' => 'Paramètres du Canvas',
            'es' => 'Configuración del Canvas',
            'de' => 'Canvas-Einstellungen'
        ],

        'canvas_width' => [
            'en' => 'Width',
            'fr' => 'Largeur',
            'es' => 'Ancho',
            'de' => 'Breite'
        ],

        'canvas_height' => [
            'en' => 'Height',
            'fr' => 'Hauteur',
            'es' => 'Alto',
            'de' => 'Höhe'
        ],

        'canvas_background' => [
            'en' => 'Background',
            'fr' => 'Arrière-plan',
            'es' => 'Fondo',
            'de' => 'Hintergrund'
        ],

        // Éléments
        'elements' => [
            'en' => 'Elements',
            'fr' => 'Éléments',
            'es' => 'Elementos',
            'de' => 'Elemente'
        ],

        'text_element' => [
            'en' => 'Text',
            'fr' => 'Texte',
            'es' => 'Texto',
            'de' => 'Text'
        ],

        'image_element' => [
            'en' => 'Image',
            'fr' => 'Image',
            'es' => 'Imagen',
            'de' => 'Bild'
        ],

        'shape_element' => [
            'en' => 'Shape',
            'fr' => 'Forme',
            'es' => 'Forma',
            'de' => 'Form'
        ],

        'line_element' => [
            'en' => 'Line',
            'fr' => 'Ligne',
            'es' => 'Línea',
            'de' => 'Linie'
        ],

        // Templates
        'templates' => [
            'en' => 'Templates',
            'fr' => 'Modèles',
            'es' => 'Plantillas',
            'de' => 'Vorlagen'
        ],

        'new_template' => [
            'en' => 'New Template',
            'fr' => 'Nouveau modèle',
            'es' => 'Nueva plantilla',
            'de' => 'Neue Vorlage'
        ],

        'save_template' => [
            'en' => 'Save Template',
            'fr' => 'Enregistrer le modèle',
            'es' => 'Guardar plantilla',
            'de' => 'Vorlage speichern'
        ],

        'load_template' => [
            'en' => 'Load Template',
            'fr' => 'Charger le modèle',
            'es' => 'Cargar plantilla',
            'de' => 'Vorlage laden'
        ],

        'delete_template' => [
            'en' => 'Delete Template',
            'fr' => 'Supprimer le modèle',
            'es' => 'Eliminar plantilla',
            'de' => 'Vorlage löschen'
        ],

        // Outils
        'tools' => [
            'en' => 'Tools',
            'fr' => 'Outils',
            'es' => 'Herramientas',
            'de' => 'Werkzeuge'
        ],

        'select_tool' => [
            'en' => 'Select',
            'fr' => 'Sélectionner',
            'es' => 'Seleccionar',
            'de' => 'Auswählen'
        ],

        'move_tool' => [
            'en' => 'Move',
            'fr' => 'Déplacer',
            'es' => 'Mover',
            'de' => 'Verschieben'
        ],

        'resize_tool' => [
            'en' => 'Resize',
            'fr' => 'Redimensionner',
            'es' => 'Redimensionar',
            'de' => 'Größe ändern'
        ],

        'rotate_tool' => [
            'en' => 'Rotate',
            'fr' => 'Pivoter',
            'es' => 'Rotar',
            'de' => 'Drehen'
        ],

        // Propriétés
        'properties' => [
            'en' => 'Properties',
            'fr' => 'Propriétés',
            'es' => 'Propiedades',
            'de' => 'Eigenschaften'
        ],

        'position' => [
            'en' => 'Position',
            'fr' => 'Position',
            'es' => 'Posición',
            'de' => 'Position'
        ],

        'size' => [
            'en' => 'Size',
            'fr' => 'Taille',
            'es' => 'Tamaño',
            'de' => 'Größe'
        ],

        'color' => [
            'en' => 'Color',
            'fr' => 'Couleur',
            'es' => 'Color',
            'de' => 'Farbe'
        ],

        'font' => [
            'en' => 'Font',
            'fr' => 'Police',
            'es' => 'Fuente',
            'de' => 'Schriftart'
        ],

        'opacity' => [
            'en' => 'Opacity',
            'fr' => 'Opacité',
            'es' => 'Opacidad',
            'de' => 'Transparenz'
        ],

        // Export
        'export' => [
            'en' => 'Export',
            'fr' => 'Exporter',
            'es' => 'Exportar',
            'de' => 'Exportieren'
        ],

        'export_pdf' => [
            'en' => 'Export PDF',
            'fr' => 'Exporter PDF',
            'es' => 'Exportar PDF',
            'de' => 'PDF exportieren'
        ],

        'export_png' => [
            'en' => 'Export PNG',
            'fr' => 'Exporter PNG',
            'es' => 'Exportar PNG',
            'de' => 'PNG exportieren'
        ],

        'export_jpg' => [
            'en' => 'Export JPG',
            'fr' => 'Exporter JPG',
            'es' => 'Exportar JPG',
            'de' => 'JPG exportieren'
        ],

        // Messages d'erreur
        'error_generic' => [
            'en' => 'An error occurred',
            'fr' => 'Une erreur s\'est produite',
            'es' => 'Ocurrió un error',
            'de' => 'Ein Fehler ist aufgetreten'
        ],

        'error_save_failed' => [
            'en' => 'Save failed',
            'fr' => 'Échec de la sauvegarde',
            'es' => 'Error al guardar',
            'de' => 'Speichern fehlgeschlagen'
        ],

        'error_load_failed' => [
            'en' => 'Load failed',
            'fr' => 'Échec du chargement',
            'es' => 'Error al cargar',
            'de' => 'Laden fehlgeschlagen'
        ],

        'error_delete_failed' => [
            'en' => 'Delete failed',
            'fr' => 'Échec de la suppression',
            'es' => 'Error al eliminar',
            'de' => 'Löschen fehlgeschlagen'
        ],

        // Messages de succès
        'success_saved' => [
            'en' => 'Successfully saved',
            'fr' => 'Enregistré avec succès',
            'es' => 'Guardado exitosamente',
            'de' => 'Erfolgreich gespeichert'
        ],

        'success_loaded' => [
            'en' => 'Successfully loaded',
            'fr' => 'Chargé avec succès',
            'es' => 'Cargado exitosamente',
            'de' => 'Erfolgreich geladen'
        ],

        'success_deleted' => [
            'en' => 'Successfully deleted',
            'fr' => 'Supprimé avec succès',
            'es' => 'Eliminado exitosamente',
            'de' => 'Erfolgreich gelöscht'
        ]
    ];

    // ==========================================
    // CONFIGURATIONS RÉGIONALES
    // ==========================================

    private static $locale_configs = [
        'en_US' => [
            'date_format' => 'm/d/Y',
            'time_format' => 'g:i A',
            'number_format' => [
                'decimal_point' => '.',
                'thousands_sep' => ',',
                'decimal_places' => 2
            ],
            'currency' => [
                'symbol' => '$',
                'position' => 'before',
                'code' => 'USD'
            ],
            'text_direction' => 'ltr'
        ],

        'fr_FR' => [
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'number_format' => [
                'decimal_point' => ',',
                'thousands_sep' => ' ',
                'decimal_places' => 2
            ],
            'currency' => [
                'symbol' => '€',
                'position' => 'after',
                'code' => 'EUR'
            ],
            'text_direction' => 'ltr'
        ],

        'es_ES' => [
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'number_format' => [
                'decimal_point' => ',',
                'thousands_sep' => '.',
                'decimal_places' => 2
            ],
            'currency' => [
                'symbol' => '€',
                'position' => 'before',
                'code' => 'EUR'
            ],
            'text_direction' => 'ltr'
        ],

        'de_DE' => [
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
            'number_format' => [
                'decimal_point' => ',',
                'thousands_sep' => '.',
                'decimal_places' => 2
            ],
            'currency' => [
                'symbol' => '€',
                'position' => 'before',
                'code' => 'EUR'
            ],
            'text_direction' => 'ltr'
        ]
    ];

    // ==========================================
    // PLURIELS
    // ==========================================

    private static $plural_forms = [
        'element' => [
            'en' => ['element', 'elements'],
            'fr' => ['élément', 'éléments'],
            'es' => ['elemento', 'elementos'],
            'de' => ['Element', 'Elemente']
        ],

        'template' => [
            'en' => ['template', 'templates'],
            'fr' => ['modèle', 'modèles'],
            'es' => ['plantilla', 'plantillas'],
            'de' => ['Vorlage', 'Vorlagen']
        ],

        'page' => [
            'en' => ['page', 'pages'],
            'fr' => ['page', 'pages'],
            'es' => ['página', 'páginas'],
            'de' => ['Seite', 'Seiten']
        ],

        'file' => [
            'en' => ['file', 'files'],
            'fr' => ['fichier', 'fichiers'],
            'es' => ['archivo', 'archivos'],
            'de' => ['Datei', 'Dateien']
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir les domaines de traduction
     */
    public static function get_text_domains() {
        return self::$text_domains;
    }

    /**
     * Obtenir un domaine de traduction spécifique
     */
    public static function get_text_domain($key) {
        return self::$text_domains[$key] ?? self::$text_domains['pdf_builder'];
    }

    /**
     * Obtenir toutes les chaînes de traduction
     */
    public static function get_translation_strings() {
        return self::$translation_strings;
    }

    /**
     * Obtenir une chaîne de traduction
     */
    public static function get_translation_string($key, $locale = null) {
        $locale = $locale ?: self::get_current_locale();
        $strings = self::$translation_strings[$key] ?? null;

        if (!$strings) {
            return $key; // Retourner la clé si pas trouvée
        }

        return $strings[$locale] ?? $strings['en'] ?? $key;
    }

    /**
     * Obtenir la configuration régionale
     */
    public static function get_locale_config($locale = null) {
        $locale = $locale ?: self::get_current_locale();
        return self::$locale_configs[$locale] ?? self::$locale_configs['en_US'];
    }

    /**
     * Obtenir les formes plurielles
     */
    public static function get_plural_forms() {
        return self::$plural_forms;
    }

    /**
     * Obtenir une forme plurielle
     */
    public static function get_plural_form($key, $count = 1, $locale = null) {
        $locale = $locale ?: self::get_current_locale();
        $forms = self::$plural_forms[$key] ?? null;

        if (!$forms) {
            return $key;
        }

        $locale_forms = $forms[$locale] ?? $forms['en'] ?? [$key, $key . 's'];
        $index = ($count === 1) ? 0 : 1;

        return $locale_forms[$index] ?? $locale_forms[0];
    }

    /**
     * Obtenir la locale actuelle
     */
    public static function get_current_locale() {
        return get_locale() ?: 'en_US';
    }

    /**
     * Formater un nombre selon la locale
     */
    public static function format_number($number, $locale = null) {
        $config = self::get_locale_config($locale);
        $format = $config['number_format'];

        return number_format(
            $number,
            $format['decimal_places'],
            $format['decimal_point'],
            $format['thousands_sep']
        );
    }

    /**
     * Formater une monnaie selon la locale
     */
    public static function format_currency($amount, $locale = null) {
        $config = self::get_locale_config($locale);
        $currency = $config['currency'];

        $formatted = self::format_number($amount, $locale);

        if ($currency['position'] === 'before') {
            return $currency['symbol'] . $formatted;
        } else {
            return $formatted . $currency['symbol'];
        }
    }

    /**
     * Formater une date selon la locale
     */
    public static function format_date($timestamp, $locale = null) {
        $config = self::get_locale_config($locale);
        return date($config['date_format'], $timestamp);
    }

    /**
     * Formater une heure selon la locale
     */
    public static function format_time($timestamp, $locale = null) {
        $config = self::get_locale_config($locale);
        return date($config['time_format'], $timestamp);
    }

    /**
     * Traduire une chaîne avec WordPress
     */
    public static function __($key, $domain = null) {
        $domain = $domain ?: self::get_text_domain('pdf_builder');
        $text = self::get_translation_string($key);

        return __($text, $domain);
    }

    /**
     * Traduire une chaîne plurielle avec WordPress
     */
    public static function _n($key, $count, $domain = null) {
        $domain = $domain ?: self::get_text_domain('pdf_builder');
        $singular = self::get_translation_string($key);
        $plural = self::get_plural_form($key, 2); // Obtenir la forme plurielle

        return _n($singular, $plural, $count, $domain);
    }

    /**
     * Générer le fichier POT pour les traductions
     */
    public static function generate_pot_file($output_path) {
        $pot_content = "# PDF Builder Pro Translation Template\n";
        $pot_content .= "# Copyright (C) " . date('Y') . " PDF Builder Pro\n";
        $pot_content .= "# This file is distributed under the same license as the PDF Builder Pro package.\n";
        $pot_content .= "msgid \"\"\n";
        $pot_content .= "msgstr \"\"\n";
        $pot_content .= "\"Project-Id-Version: PDF Builder Pro\\n\"\n";
        $pot_content .= "\"Report-Msgid-Bugs-To: \\n\"\n";
        $pot_content .= "\"POT-Creation-Date: " . date('c') . "\\n\"\n";
        $pot_content .= "\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"\n";
        $pot_content .= "\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n\"\n";
        $pot_content .= "\"Language-Team: LANGUAGE <LL@li.org>\\n\"\n";
        $pot_content .= "\"MIME-Version: 1.0\\n\"\n";
        $pot_content .= "\"Content-Type: text/plain; charset=UTF-8\\n\"\n";
        $pot_content .= "\"Content-Transfer-Encoding: 8bit\\n\"\n";
        $pot_content .= "\"Plural-Forms: nplurals=2; plural=(n != 1);\\n\"\n\n";

        foreach (self::$translation_strings as $key => $translations) {
            $pot_content .= "#: {$key}\n";
            $pot_content .= "msgid \"" . addslashes($translations['en']) . "\"\n";
            $pot_content .= "msgstr \"\"\n\n";
        }

        foreach (self::$plural_forms as $key => $forms) {
            $pot_content .= "#: {$key}\n";
            $pot_content .= "msgid \"" . addslashes($forms['en'][0]) . "\"\n";
            $pot_content .= "msgid_plural \"" . addslashes($forms['en'][1]) . "\"\n";
            $pot_content .= "msgstr[0] \"\"\n";
            $pot_content .= "msgstr[1] \"\"\n\n";
        }

        file_put_contents($output_path, $pot_content);
        return true;
    }

    /**
     * Charger les traductions depuis un fichier MO
     */
    public static function load_translations($locale, $domain = null) {
        $domain = $domain ?: self::get_text_domain('pdf_builder');
        $mo_file = WP_LANG_DIR . "/plugins/{$domain}-{$locale}.mo";

        if (file_exists($mo_file)) {
            load_textdomain($domain, $mo_file);
            return true;
        }

        return false;
    }
}

