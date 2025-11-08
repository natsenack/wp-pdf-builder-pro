<?php
/**
 * PDF Builder Pro - Renderer Cache
 * Phase 3.3.6 - Système de cache pour optimiser les performances de rendu
 *
 * Cache intelligent pour :
 * - Styles CSS générés
 * - Variables système
 * - Résultats de calculs répétitifs
 * - Templates HTML fréquents
 */

namespace PDF_Builder\Cache;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class RendererCache {

    /**
     * Cache statique en mémoire
     */
    private static $cache = [];

    /**
     * Durée de vie du cache en secondes (5 minutes par défaut)
     */
    const CACHE_TTL = 300;

    /**
     * Métriques de performance
     */
    private static $metrics = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'clears' => 0
    ];

    /**
     * Récupère les paramètres de cache depuis la configuration
     *
     * @return array Paramètres de cache
     */
    private static function getCacheSettings(): array {
        static $settings = null;
        
        if ($settings === null) {
            $settings = get_option('pdf_builder_settings', []);
        }
        
        return $settings;
    }

    /**
     * Vérifie si le cache est activé
     *
     * @return bool True si le cache est activé
     */
    private static function isCacheEnabled(): bool {
        $settings = self::getCacheSettings();
        return !empty($settings['cache_enabled']);
    }

    /**
     * Récupère la durée de vie du cache depuis la configuration
     *
     * @return int TTL en secondes
     */
    private static function getCacheTTL(): int {
        $settings = self::getCacheSettings();
        return intval($settings['cache_ttl'] ?? self::CACHE_TTL);
    }

    /**
     * Récupère une valeur du cache
     *
     * @param string $key Clé du cache
     * @return mixed Valeur ou null si expirée/absente
     */
    public static function get(string $key) {
        // Vérifier si le cache est activé
        if (!self::isCacheEnabled()) {
            return null;
        }

        if (!isset(self::$cache[$key])) {
            self::$metrics['misses']++;
            return null;
        }

        $entry = self::$cache[$key];

        // Vérifier si expiré
        if (time() > $entry['expires']) {
            unset(self::$cache[$key]);
            self::$metrics['misses']++;
            return null;
        }

        self::$metrics['hits']++;
        return $entry['value'];
    }

    /**
     * Stocke une valeur dans le cache
     *
     * @param string $key Clé du cache
     * @param mixed $value Valeur à stocker
     * @param int $ttl Durée de vie en secondes (optionnel)
     */
    public static function set(string $key, $value, int $ttl = null): void {
        // Vérifier si le cache est activé
        if (!self::isCacheEnabled()) {
            return;
        }

        $ttl = $ttl ?? self::getCacheTTL();

        self::$cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        self::$metrics['sets']++;
    }

    /**
     * Vérifie si une clé existe dans le cache
     *
     * @param string $key Clé du cache
     * @return bool True si la clé existe et n'est pas expirée
     */
    public static function has(string $key): bool {
        // Vérifier si le cache est activé
        if (!self::isCacheEnabled()) {
            return false;
        }

        if (!isset(self::$cache[$key])) {
            return false;
        }

        $entry = self::$cache[$key];
        return time() <= $entry['expires'];
    }

    /**
     * Supprime une clé du cache
     *
     * @param string $key Clé du cache
     */
    public static function delete(string $key): void {
        if (isset(self::$cache[$key])) {
            unset(self::$cache[$key]);
        }
    }

    /**
     * Vide complètement le cache
     */
    public static function clear(): void {
        self::$cache = [];
        self::$metrics['clears']++;
    }

    /**
     * Nettoie les entrées expirées
     */
    public static function cleanup(): void {
        $now = time();
        $cleaned = 0;

        foreach (self::$cache as $key => $entry) {
            if ($now > $entry['expires']) {
                unset(self::$cache[$key]);
                $cleaned++;
            }
        }

        if ($cleaned > 0) {
            // Cache nettoyé
        }
    }

    /**
     * Récupère les métriques de performance
     *
     * @return array Métriques actuelles
     */
    public static function getMetrics(): array {
        $total = self::$metrics['hits'] + self::$metrics['misses'];
        $hitRate = $total > 0 ? (self::$metrics['hits'] / $total) * 100 : 0;

        return array_merge(self::$metrics, [
            'total_requests' => $total,
            'hit_rate' => round($hitRate, 2),
            'cache_size' => count(self::$cache),
            'memory_usage' => self::getMemoryUsage()
        ]);
    }

    /**
     * Génère une clé de cache pour les styles CSS
     *
     * @param array $properties Propriétés CSS
     * @param string $prefix Préfixe pour la clé
     * @return string Clé de cache
     */
    public static function generateStyleKey(array $properties, string $prefix = 'css'): string {
        // Trier les propriétés pour cohérence
        ksort($properties);
        $hash = md5(serialize($properties));
        return "{$prefix}_styles_{$hash}";
    }

    /**
     * Génère une clé de cache pour les variables
     *
     * @param string $variable Nom de la variable
     * @param array $context Contexte (optionnel)
     * @return string Clé de cache
     */
    public static function generateVariableKey(string $variable, array $context = []): string {
        $contextHash = !empty($context) ? '_' . md5(serialize($context)) : '';
        return "var_{$variable}{$contextHash}";
    }

    /**
     * Génère une clé de cache pour les templates HTML
     *
     * @param string $template Nom du template
     * @param array $data Données du template
     * @return string Clé de cache
     */
    public static function generateTemplateKey(string $template, array $data = []): string {
        $dataHash = !empty($data) ? '_' . md5(serialize($data)) : '';
        return "template_{$template}{$dataHash}";
    }

    /**
     * Obtient l'utilisation mémoire du cache
     *
     * @return string Utilisation formatée
     */
    private static function getMemoryUsage(): string {
        $bytes = strlen(serialize(self::$cache));
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Cache une fonction avec ses paramètres
     *
     * @param callable $callback Fonction à mettre en cache
     * @param array $args Arguments de la fonction
     * @param string $key Clé de cache personnalisée (optionnel)
     * @param int $ttl Durée de vie (optionnel)
     * @return mixed Résultat de la fonction
     */
    public static function remember(callable $callback, array $args = [], string $key = null, int $ttl = null) {
        $key = $key ?? 'func_' . md5(serialize($args));

        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $result = call_user_func_array($callback, $args);
        self::set($key, $result, $ttl);

        return $result;
    }
}
?>