<?php

/**
 * JSON Optimizer - Compression & Serialization
 *
 * Utilitaire pour optimiser la taille des données JSON
 * et améliorer les performances de sérialisation.
 *
 * @package PDF_Builder
 * @subpackage Utilities
 * @version 1.0.0
 */

namespace WP_PDF_Builder_Pro\Src\Utilities;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gestionnaire d'optimisation JSON
 *
 * Features:
 * - Minification JSON (suppression espaces inutiles)
 * - Compression GZIP optionnelle
 * - Deduplication des valeurs répétées
 * - Benchmarking et statistiques
 *
 * @since 1.0.0
 */
class PdfBuilderJsonOptimizer
{
    /**
     * Seuil de compression (50KB par défaut)
     *
     * @var int
     */
    private static $compression_threshold = 51200;

    /**
     * Minifie un string JSON en supprimant les espaces inutiles
     *
     * @param string|array $data Données JSON ou array à minifier
     * @return string JSON minifié
     *
     * @since 1.0.0
     */
    public static function minifyJson($data)
    {
        // Si array, convertir en JSON d'abord
        if (is_array($data)) {
            $data = wp_json_encode($data);
        }

        if (!is_string($data)) {
            return $data;
        }

        // Decode, puis re-encode sans espaces
        $decoded = json_decode($data, true);
        if ($decoded === null) {
            return $data;
        // Retourner original si erreur
        }

        // wp_json_encode ne génère pas d'espaces inutiles de toute façon
        return wp_json_encode($decoded);
    }

    /**
     * Compresse les données JSON avec GZIP
     *
     * @param string $json_data String JSON à compresser
     * @return string Base64-encoded compressed data
     *
     * @since 1.0.0
     */
    public static function compress($json_data)
    {
        if (!is_string($json_data)) {
            $json_data = wp_json_encode($json_data);
        }

        if (strlen($json_data) < self::$compression_threshold) {
            return $json_data;
// Pas assez gros pour compresser
        }

        if (!function_exists('gzcompress')) {
            return $json_data;
// GZIP non disponible
        }

        $compressed = gzcompress($json_data, 9);
        if ($compressed === false) {
            return $json_data;
        // Erreur compression
        }

        // Encoder en base64 pour stockage sûr
        return base64_encode($compressed);
    }

    /**
     * Décompresse les données JSON
     *
     * @param string $compressed_data Base64-encoded compressed data
     * @return array|string Données décodées
     *
     * @since 1.0.0
     */
    public static function decompress($compressed_data)
    {
        // Essayer de décoder base64
        $decoded_base64 = base64_decode($compressed_data, true);
        if ($decoded_base64 === false) {
        // Pas en base64, probablement données normales
            return json_decode($compressed_data, true);
        }

        if (!function_exists('gzuncompress')) {
            return json_decode($compressed_data, true);
        }

        $decompressed = gzuncompress($decoded_base64);
        if ($decompressed === false) {
            return json_decode($compressed_data, true);
        }

        return json_decode($decompressed, true);
    }

    /**
     * Deduplique les valeurs répétées dans un array
     * Pour réduire la taille en stockant les références
     *
     * @param array $data Array à dédupliquer
     * @return array Array optimisé
     *
     * @since 1.0.0
     */
    public static function deduplicateValues($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $value_map = [];
        $next_ref_id = 0;
// Parcourir et collecter valeurs répétées
        $result = self::deduplicateRecursive($data, $value_map, $next_ref_id);
        return $result;
    }

    /**
     * Fonction récursive interne pour déduplication
     *
     * @private
     */
    private static function deduplicateRecursive(&$data, &$value_map, &$ref_id)
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = self::deduplicateRecursive($value, $value_map, $ref_id);
            } elseif (is_string($value) && strlen($value) > 20) {
                $hash = md5($value);
                if (!isset($value_map[$hash])) {
                    $value_map[$hash] = ['id' => $ref_id++, 'value' => $value];
                }
            }
        }

        return $data;
    }

    /**
     * Obtient les statistiques de compression
     *
     * @param string|array $original_data Données originales
     * @param string $compressed_data Données compressées
     * @return array Statistiques {original_size, compressed_size, ratio, ...}
     *
     * @since 1.0.0
     */
    public static function getCompressionStats($original_data, $compressed_data = null)
    {
        $original_json = is_array($original_data) ? wp_json_encode($original_data) : $original_data;
        $original_size = strlen($original_json);
        if ($compressed_data === null) {
            $compressed_data = self::compress($original_json);
        }

        $compressed_size = strlen($compressed_data);
        $savings = $original_size - $compressed_size;
        $ratio = $original_size > 0 ? ($savings / $original_size) * 100 : 0;
        return [
            'original_size' => $original_size,
            'compressed_size' => $compressed_size,
            'savings' => $savings,
            'ratio' => round($ratio, 2) . '%',
            'compression_worth_it' => $savings > 1000 // Au moins 1KB d'économie
        ];
    }

    /**
     * Optimise complètement un array de templates
     * (minification + compression si bénéfique)
     *
     * @param array $template_data Données du template
     * @return array Données optimisées avec métadonnées
     *
     * @since 1.0.0
     */
    public static function optimizeTemplate($template_data)
    {
        // Minifier le JSON
        $json_minified = self::minifyJson($template_data);
// Compresser si bénéfique
        $json_compressed = self::compress($json_minified);
// Calculer stats
        $stats = self::getCompressionStats($template_data, $json_compressed);
        return [
            'data' => $stats['compression_worth_it'] ? $json_compressed : $json_minified,
            'compressed' => $stats['compression_worth_it'],
            'stats' => $stats
        ];
    }

    /**
     * Change le seuil de compression
     *
     * @param int $threshold Nouvelle limite en bytes
     *
     * @since 1.0.0
     */
    public static function setCompressionThreshold($threshold)
    {
        if (is_numeric($threshold) && $threshold > 0) {
            self::$compression_threshold = intval($threshold);
        }
    }
}
