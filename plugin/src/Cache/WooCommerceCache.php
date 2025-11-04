<?php
/**
 * PDF Builder Pro - WooCommerce Cache
 * Phase 3.4.2 - Cache transients pour données WooCommerce
 *
 * Système de cache intelligent pour :
 * - Données de commandes WooCommerce
 * - Informations clients
 * - Données entreprise
 * - Invalidation automatique lors des modifications
 */

namespace PDF_Builder\Cache;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class WooCommerceCache {

    /**
     * Préfixe pour les clés de cache
     */
    const CACHE_PREFIX = 'pdf_builder_wc_';

    /**
     * Durée de vie du cache en secondes (1 heure par défaut)
     */
    const CACHE_TTL = 3600;

    /**
     * Durée de vie du cache pour les données statiques (24 heures)
     */
    const CACHE_TTL_STATIC = 86400;

    /**
     * Métriques de performance
     */
    private static $metrics = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'clears' => 0,
        'invalidations' => 0
    ];

    /**
     * Génère une clé de cache unique
     *
     * @param string $type Type de données (order, customer, company, etc.)
     * @param mixed $identifier Identifiant (order_id, user_id, etc.)
     * @param array $context Contexte supplémentaire pour la clé
     * @return string Clé de cache
     */
    private static function generateKey(string $type, $identifier, array $context = []): string {
        $key = self::CACHE_PREFIX . $type . '_' . $identifier;

        // Ajouter le contexte trié pour la cohérence
        if (!empty($context)) {
            ksort($context);
            $key .= '_' . md5(serialize($context));
        }

        return $key;
    }

    /**
     * Récupère des données de commande du cache
     *
     * @param int $orderId ID de la commande
     * @param array $fields Champs spécifiques à récupérer (optionnel)
     * @return array|null Données de commande ou null si non trouvées
     */
    public static function getOrderData(int $orderId, array $fields = []): ?array {
        $key = self::generateKey('order', $orderId, ['fields' => $fields]);
        $cached = get_transient($key);

        if ($cached !== false) {
            self::$metrics['hits']++;
            return $cached;
        }

        self::$metrics['misses']++;
        return null;
    }

    /**
     * Stocke des données de commande en cache
     *
     * @param int $orderId ID de la commande
     * @param array $data Données à stocker
     * @param array $fields Champs stockés (pour la clé)
     * @return bool Succès du stockage
     */
    public static function setOrderData(int $orderId, array $data, array $fields = []): bool {
        $key = self::generateKey('order', $orderId, ['fields' => $fields]);
        $result = set_transient($key, $data, self::CACHE_TTL);
        self::$metrics['sets']++;
        return $result;
    }

    /**
     * Récupère des données client du cache
     *
     * @param int $userId ID de l'utilisateur
     * @return array|null Données client ou null si non trouvées
     */
    public static function getCustomerData(int $userId): ?array {
        $key = self::generateKey('customer', $userId);
        $cached = get_transient($key);

        if ($cached !== false) {
            self::$metrics['hits']++;
            return $cached;
        }

        self::$metrics['misses']++;
        return null;
    }

    /**
     * Stocke des données client en cache
     *
     * @param int $userId ID de l'utilisateur
     * @param array $data Données client
     * @return bool Succès du stockage
     */
    public static function setCustomerData(int $userId, array $data): bool {
        $key = self::generateKey('customer', $userId);
        $result = set_transient($key, $data, self::CACHE_TTL);
        self::$metrics['sets']++;
        return $result;
    }

    /**
     * Récupère des données entreprise du cache
     *
     * @param string $context Contexte (woocommerce_settings, custom_settings, etc.)
     * @return array|null Données entreprise ou null si non trouvées
     */
    public static function getCompanyData(string $context = 'default'): ?array {
        $key = self::generateKey('company', $context);
        $cached = get_transient($key);

        if ($cached !== false) {
            self::$metrics['hits']++;
            return $cached;
        }

        self::$metrics['misses']++;
        return null;
    }

    /**
     * Stocke des données entreprise en cache
     *
     * @param array $data Données entreprise
     * @param string $context Contexte
     * @return bool Succès du stockage
     */
    public static function setCompanyData(array $data, string $context = 'default'): bool {
        $key = self::generateKey('company', $context);
        $result = set_transient($key, $data, self::CACHE_TTL_STATIC); // Plus long pour les données statiques
        self::$metrics['sets']++;
        return $result;
    }

    /**
     * Récupère des données de produits du cache
     *
     * @param array $productIds IDs des produits
     * @return array|null Données des produits ou null si non trouvées
     */
    public static function getProductsData(array $productIds): ?array {
        sort($productIds); // Pour cohérence de la clé
        $key = self::generateKey('products', md5(serialize($productIds)), ['count' => count($productIds)]);
        $cached = get_transient($key);

        if ($cached !== false) {
            self::$metrics['hits']++;
            return $cached;
        }

        self::$metrics['misses']++;
        return null;
    }

    /**
     * Stocke des données de produits en cache
     *
     * @param array $productIds IDs des produits
     * @param array $data Données des produits
     * @return bool Succès du stockage
     */
    public static function setProductsData(array $productIds, array $data): bool {
        sort($productIds);
        $key = self::generateKey('products', md5(serialize($productIds)), ['count' => count($productIds)]);
        $result = set_transient($key, $data, self::CACHE_TTL);
        self::$metrics['sets']++;
        return $result;
    }

    /**
     * Invalide le cache pour une commande spécifique
     *
     * @param int $orderId ID de la commande
     * @return void
     */
    public static function invalidateOrderCache(int $orderId): void {
        // Utiliser WordPress pour supprimer les transients correspondants
        if (isset($GLOBALS['wpdb'])) {
            global $wpdb;
            $transient_keys = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '_transient_' . self::CACHE_PREFIX . 'order_' . $orderId . '*'
                )
            );

            foreach ($transient_keys as $transient_key) {
                $key = str_replace('_transient_', '', $transient_key);
                delete_transient($key);
            }
        } else {
            // Fallback pour les environnements de test
            // Supprimer manuellement les clés connues
            $possible_keys = [
                self::generateKey('order', $orderId),
                self::generateKey('order', $orderId, ['fields' => []])
            ];

            foreach ($possible_keys as $key) {
                delete_transient($key);
            }
        }

        self::$metrics['invalidations']++;
    }

    /**
     * Invalide le cache pour un client spécifique
     *
     * @param int $userId ID de l'utilisateur
     * @return void
     */
    public static function invalidateCustomerCache(int $userId): void {
        $key = self::generateKey('customer', $userId);
        delete_transient($key);
        self::$metrics['invalidations']++;
    }

    /**
     * Invalide le cache des données entreprise
     *
     * @param string $context Contexte spécifique (optionnel)
     * @return void
     */
    public static function invalidateCompanyCache(string $context = ''): void {
        if (!empty($context)) {
            $key = self::generateKey('company', $context);
            delete_transient($key);
        } else {
            // Invalider tous les caches entreprise
            if (isset($GLOBALS['wpdb'])) {
                global $wpdb;
                $transient_keys = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                        '_transient_' . self::CACHE_PREFIX . 'company_%'
                    )
                );

                foreach ($transient_keys as $transient_key) {
                    $key = str_replace('_transient_', '', $transient_key);
                    delete_transient($key);
                }
            } else {
                // Fallback pour les environnements de test
                $possible_keys = [
                    self::generateKey('company', 'default'),
                    self::generateKey('company', 'woocommerce_settings')
                ];

                foreach ($possible_keys as $key) {
                    delete_transient($key);
                }
            }
        }
        self::$metrics['invalidations']++;
    }

    /**
     * Invalide le cache des produits WooCommerce
     *
     * @param int $productId ID du produit modifié/supprimé
     * @return void
     */
    public static function invalidateProductCache(int $productId): void {
        // Invalider le cache spécifique du produit
        $key = self::generateKey('product', $productId);
        delete_transient($key);

        // Invalider les caches de commandes qui pourraient contenir ce produit
        // (on invalide un échantillon représentatif plutôt que tout)
        if (isset($GLOBALS['wpdb'])) {
            global $wpdb;
            $order_transient_keys = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 50",
                    '_transient_' . self::CACHE_PREFIX . 'order_%'
                )
            );

            foreach ($order_transient_keys as $transient_key) {
                $key = str_replace('_transient_', '', $transient_key);
                delete_transient($key);
            }
        }

        self::$metrics['invalidations']++;
    }

    /**
     * Invalide tout le cache WooCommerce
     *
     * @return void
     */
    public static function clearAllCache(): void {
        if (isset($GLOBALS['wpdb'])) {
            global $wpdb;
            $transient_keys = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                    '_transient_' . self::CACHE_PREFIX . '%'
                )
            );

            foreach ($transient_keys as $transient_key) {
                $key = str_replace('_transient_', '', $transient_key);
                delete_transient($key);
            }
        } else {
            // Fallback pour les environnements de test
            // Les transients sont gérés par les fonctions mockées
        }

        self::$metrics['clears']++;
    }

    /**
     * Nettoie le cache expiré (maintenance)
     *
     * @return int Nombre d'entrées nettoyées
     */
    public static function cleanupExpiredCache(): int {
        global $wpdb;

        // Supprimer les transients expirés
        $cleaned = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
                '_transient_timeout_' . self::CACHE_PREFIX . '%',
                time()
            )
        );

        return $cleaned ?: 0;
    }

    /**
     * Récupère les métriques de performance
     *
     * @return array Métriques actuelles
     */
    public static function getMetrics(): array {
        return self::$metrics;
    }

    /**
     * Réinitialise les métriques
     *
     * @return void
     */
    public static function resetMetrics(): void {
        self::$metrics = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'clears' => 0,
            'invalidations' => 0
        ];
    }

    /**
     * Vérifie si le cache est disponible
     *
     * @return bool True si le cache est disponible
     */
    public static function isAvailable(): bool {
        // Tester avec un transient temporaire
        $test_key = self::CACHE_PREFIX . 'test_' . time();
        $test_value = 'test_value';

        $set_result = set_transient($test_key, $test_value, 60);
        $get_result = get_transient($test_key);
        delete_transient($test_key);

        return $set_result && ($get_result === $test_value);
    }

    /**
     * Hook pour l'invalidation automatique lors des modifications WooCommerce
     *
     * @return void
     */
    public static function setupAutoInvalidation(): void {
        // Invalidation lors de la modification d'une commande
        add_action('woocommerce_order_status_changed', [self::class, 'invalidateOrderCache'], 10, 1);
        add_action('woocommerce_update_order', [self::class, 'invalidateOrderCache'], 10, 1);
        add_action('woocommerce_new_order', [self::class, 'invalidateOrderCache'], 10, 1);

        // Invalidation lors de la modification d'un client
        add_action('profile_update', [self::class, 'invalidateCustomerCache'], 10, 1);
        add_action('user_register', [self::class, 'invalidateCustomerCache'], 10, 1);

        // Invalidation lors de la modification de produits (affecte les données de commande)
        add_action('woocommerce_update_product', [self::class, 'invalidateProductCache'], 10, 1);
        add_action('woocommerce_delete_product', [self::class, 'invalidateProductCache'], 10, 1);

        // Invalidation lors des changements de paramètres WooCommerce
        add_action('update_option', function($option_name) {
            if (strpos($option_name, 'woocommerce_') === 0) {
                self::invalidateCompanyCache();
            }
        });

        // Nettoyage périodique du cache
        if (!wp_next_scheduled('pdf_builder_wc_cache_cleanup')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_wc_cache_cleanup');
        }
        add_action('pdf_builder_wc_cache_cleanup', [self::class, 'cleanupExpiredCache']);
    }
}