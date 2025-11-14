<?php

namespace WP_PDF_Builder_Pro\Src;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * PDF Builder Pro - Advanced Localization Manager
 * Gestion avancée de la localisation (dates, nombres, devises)
 */

class PdfBuilderAdvancedLocalization
{
    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Locale actuelle
     */
    private $current_locale = 'fr_FR';

    /**
     * Configuration de localisation
     */
    private $locale_config = [];

    /**
     * Constructeur privé
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation
     */
    private function init()
    {
        $this->current_locale = get_locale();
        $this->load_locale_config();

        // Hooks pour la localisation
        add_filter('pdf_builder_format_date', [$this, 'format_date'], 10, 2);
        add_filter('pdf_builder_format_number', [$this, 'format_number'], 10, 2);
        add_filter('pdf_builder_format_currency', [$this, 'format_currency'], 10, 2);
        add_filter('pdf_builder_format_address', [$this, 'format_address'], 10, 1);
    }

    /**
     * Charger la configuration de localisation
     */
    private function load_locale_config()
    {
        $this->locale_config = [
            'fr_FR' => [
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i:s',
                'datetime_format' => 'd/m/Y H:i',
                'number_decimal_separator' => ',',
                'number_thousands_separator' => ' ',
                'currency_symbol' => '€',
                'currency_position' => 'after', // before | after
                'currency_space' => true,
                'address_format' => [
                    'name',
                    'street',
                    'postal_code city',
                    'country'
                ]
            ],
            'en_US' => [
                'date_format' => 'm/d/Y',
                'time_format' => 'g:i:s A',
                'datetime_format' => 'm/d/Y g:i A',
                'number_decimal_separator' => '.',
                'number_thousands_separator' => ',',
                'currency_symbol' => '$',
                'currency_position' => 'before',
                'currency_space' => false,
                'address_format' => [
                    'name',
                    'street',
                    'city, state postal_code',
                    'country'
                ]
            ],
            'de_DE' => [
                'date_format' => 'd.m.Y',
                'time_format' => 'H:i:s',
                'datetime_format' => 'd.m.Y H:i',
                'number_decimal_separator' => ',',
                'number_thousands_separator' => '.',
                'currency_symbol' => '€',
                'currency_position' => 'after',
                'currency_space' => true,
                'address_format' => [
                    'name',
                    'street',
                    'postal_code city',
                    'country'
                ]
            ],
            'es_ES' => [
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i:s',
                'datetime_format' => 'd/m/Y H:i',
                'number_decimal_separator' => ',',
                'number_thousands_separator' => '.',
                'currency_symbol' => '€',
                'currency_position' => 'after',
                'currency_space' => true,
                'address_format' => [
                    'name',
                    'street',
                    'postal_code city',
                    'country'
                ]
            ]
        ];
    }

    /**
     * Formater une date selon la locale
     *
     * @param string $date Date à formater (timestamp ou string)
     * @param string $format Type de format ('date', 'time', 'datetime')
     * @return string Date formatée
     */
    public function format_date($date, $format = 'date')
    {
        if (empty($date)) {
            return '';
        }

        // Convertir en timestamp si nécessaire
        if (!is_numeric($date)) {
            $timestamp = strtotime($date);
        } else {
            $timestamp = $date;
        }

        if (!$timestamp) {
            return $date; // Retourner la date originale si invalide
        }

        $config = $this->get_locale_config();
        $format_string = $config[$format . '_format'] ?? $config['date_format'];

        return date_i18n($format_string, $timestamp);
    }

    /**
     * Formater un nombre selon la locale
     *
     * @param float $number Nombre à formater
     * @param int $decimals Nombre de décimales
     * @return string Nombre formaté
     */
    public function format_number($number, $decimals = 2)
    {
        if (!is_numeric($number)) {
            return $number;
        }

        $config = $this->get_locale_config();

        return number_format(
            $number,
            $decimals,
            $config['number_decimal_separator'],
            $config['number_thousands_separator']
        );
    }

    /**
     * Formater une devise selon la locale
     *
     * @param float $amount Montant
     * @param string $currency_code Code de la devise (optionnel)
     * @return string Montant formaté avec devise
     */
    public function format_currency($amount, $currency_code = null)
    {
        if (!is_numeric($amount)) {
            return $amount;
        }

        $config = $this->get_locale_config();

        // Utiliser WooCommerce si disponible pour la devise
        if (function_exists('wc_price') && $currency_code === null) {
            return wc_price($amount);
        }

        // Formatage manuel
        $formatted_number = $this->format_number($amount, 2);
        $symbol = $currency_code ?: $config['currency_symbol'];
        $space = $config['currency_space'] ? ' ' : '';

        if ($config['currency_position'] === 'before') {
            return $symbol . $space . $formatted_number;
        } else {
            return $formatted_number . $space . $symbol;
        }
    }

    /**
     * Formater une adresse selon la locale
     *
     * @param array $address Données d'adresse
     * @return string Adresse formatée
     */
    public function format_address($address)
    {
        if (!is_array($address) || empty($address)) {
            return '';
        }

        $config = $this->get_locale_config();
        $format = $config['address_format'];
        $lines = [];

        foreach ($format as $line) {
            $line_content = '';

            // Remplacer les placeholders par les valeurs
            $placeholders = [
                'name' => $address['name'] ?? '',
                'street' => $address['street'] ?? '',
                'city' => $address['city'] ?? '',
                'state' => $address['state'] ?? '',
                'postal_code' => $address['postal_code'] ?? '',
                'country' => $address['country'] ?? ''
            ];

            $line_content = str_replace(
                array_keys($placeholders),
                array_values($placeholders),
                $line
            );

            // Nettoyer les espaces multiples et les virgules
            $line_content = preg_replace('/\s+/', ' ', $line_content);
            $line_content = preg_replace('/,\s*,/', ',', $line_content);
            $line_content = trim($line_content, ', ');

            if (!empty($line_content)) {
                $lines[] = $line_content;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Obtenir la configuration de la locale actuelle
     *
     * @return array Configuration de locale
     */
    private function get_locale_config()
    {
        $locale = substr($this->current_locale, 0, 5); // ex: fr_FR -> fr_FR

        if (isset($this->locale_config[$locale])) {
            return $this->locale_config[$locale];
        }

        // Fallback vers fr_FR si locale inconnue
        return $this->locale_config['fr_FR'];
    }

    /**
     * Détecter si la langue est RTL
     *
     * @return bool True si RTL
     */
    public function is_rtl()
    {
        $rtl_languages = ['ar', 'he', 'fa', 'ur', 'yi'];
        $lang_code = substr($this->current_locale, 0, 2);

        return in_array($lang_code, $rtl_languages);
    }

    /**
     * Obtenir la locale actuelle
     *
     * @return string Locale
     */
    public function get_current_locale()
    {
        return $this->current_locale;
    }

    /**
     * Changer la locale (pour les tests)
     *
     * @param string $locale Nouvelle locale
     */
    public function set_locale($locale)
    {
        $this->current_locale = $locale;
        $this->load_locale_config();
    }
}