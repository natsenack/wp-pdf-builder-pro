<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * PDF Builder Sanitizer
 *
 * Centralizes data sanitization operations
 */

class PDF_Builder_Sanitizer {

    /**
     * Sanitize text field
     *
     * @param string $value The value to sanitize
     * @return string Sanitized value
     */
    public static function text($value) {
        return sanitize_text_field($value);
    }

    /**
     * Sanitize textarea
     *
     * @param string $value The value to sanitize
     * @return string Sanitized value
     */
    public static function textarea($value) {
        return sanitize_textarea_field($value);
    }

    /**
     * Sanitize integer
     *
     * @param mixed $value The value to sanitize
     * @return int Sanitized integer
     */
    public static function int($value) {
        return intval($value);
    }

    /**
     * Sanitize float
     *
     * @param mixed $value The value to sanitize
     * @return float Sanitized float
     */
    public static function float($value) {
        return floatval($value);
    }

    /**
     * Sanitize email
     *
     * @param string $value The value to sanitize
     * @return string Sanitized email
     */
    public static function email($value) {
        return sanitize_email($value);
    }

    /**
     * Sanitize URL
     *
     * @param string $value The value to sanitize
     * @return string Sanitized URL
     */
    public static function url($value) {
        return esc_url_raw($value);
    }

    /**
     * Sanitize boolean
     *
     * @param mixed $value The value to sanitize
     * @return bool Sanitized boolean
     */
    public static function bool($value) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * Sanitize array of values
     *
     * @param array $values Array of values to sanitize
     * @param string $type The type to sanitize each value as
     * @return array Sanitized array
     */
    public static function array_values($values, $type = 'text') {
        if (!is_array($values)) {
            return array();
        }

        $sanitized = array();
        foreach ($values as $key => $value) {
            $sanitized[$key] = self::$type($value);
        }
        return $sanitized;
    }
}



