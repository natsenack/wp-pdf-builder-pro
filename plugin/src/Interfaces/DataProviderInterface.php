<?php
/**
 * Interface pour les providers de données
 *
 * Définit le contrat pour récupérer et fournir des données
 * aux différents modes d'aperçu.
 *
 * @package PDF_Builder_Pro
 * @subpackage Interfaces
 */

namespace PDF_Builder_Pro\Interfaces;

/**
 * Interface DataProviderInterface
 *
 * Définit les méthodes communes pour tous les providers de données.
 * Permet d'abstraire la source des données (fictives ou réelles).
 */
interface DataProviderInterface
{
    /**
     * Récupère les données de base pour un contexte donné
     *
     * @param array $context Contexte de récupération (order_id, template_id, etc.)
     * @return array Données de base formatées
     */
    public function getBaseData(array $context = []): array;

    /**
     * Récupère les données client
     *
     * @param array $context Contexte de récupération
     * @return array Données client formatées
     */
    public function getCustomerData(array $context = []): array;

    /**
     * Récupère les données commande/produit
     *
     * @param array $context Contexte de récupération
     * @return array Données commande formatées
     */
    public function getOrderData(array $context = []): array;

    /**
     * Récupère les données société
     *
     * @param array $context Contexte de récupération
     * @return array Données société formatées
     */
    public function getCompanyData(array $context = []): array;

    /**
     * Récupère les données système (date, numéro, etc.)
     *
     * @param array $context Contexte de récupération
     * @return array Données système formatées
     */
    public function getSystemData(array $context = []): array;

    /**
     * Vérifie si toutes les données requises sont disponibles
     *
     * @param array $requiredKeys Clés requises
     * @param array $context Contexte de vérification
     * @return array Résultat ['complete' => bool, 'missing' => array]
     */
    public function checkDataCompleteness(array $requiredKeys, array $context = []): array;

    /**
     * Génère des données fictives cohérentes pour les tests/prévisualisation
     *
     * @param array $templateKeys Clés du template à générer
     * @return array Données fictives générées
     */
    public function generateMockData(array $templateKeys = []): array;

    /**
     * Nettoie et formate les données selon les standards
     *
     * @param array $data Données brutes à nettoyer
     * @return array Données nettoyées et formatées
     */
    public function sanitizeData(array $data): array;

    /**
     * Met en cache les données pour optimiser les performances
     *
     * @param string $key Clé de cache
     * @param array $data Données à mettre en cache
     * @param int $ttl Durée de vie en secondes
     * @return bool True si mise en cache réussie
     */
    public function cacheData(string $key, array $data, int $ttl = 300): bool;

    /**
     * Récupère les données depuis le cache
     *
     * @param string $key Clé de cache
     * @return array|null Données depuis le cache ou null si non trouvées
     */
    public function getCachedData(string $key): ?array;

    /**
     * Invalide le cache pour une clé donnée
     *
     * @param string $key Clé de cache à invalider
     * @return bool True si invalidation réussie
     */
    public function invalidateCache(string $key): bool;
}