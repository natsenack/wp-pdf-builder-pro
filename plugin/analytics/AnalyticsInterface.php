<?php

/**
 * Interface file - declares symbols only
 */

namespace WP_PDF_Builder_Pro\Analytics;

/**
 * Interface AnalyticsInterface
 * D?finit le contrat pour le syst?me d'analytics et m?triques
 */
interface AnalyticsInterface
{
    /**
     * Enregistre un ?v?nement d'utilisation
     *
     * @param string $event Nom de l'?v?nement
     * @param array $data Donn?es associ?es ? l'?v?nement
     * @param int|null $user_id ID de l'utilisateur (null pour anonyme)
     */
    public function trackEvent(string $event, array $data = [], ?int $user_id = null): void;
/**
     * Enregistre les m?triques de performance
     *
     * @param string $operation Nom de l'op?ration
     * @param float $duration Dur?e en secondes
     * @param array $metadata M?tadonn?es suppl?mentaires
     */
    public function trackPerformance(string $operation, float $duration, array $metadata = []): void;
/**
     * Enregistre une erreur
     *
     * @param string $error_type Type d'erreur
     * @param string $message Message d'erreur
     * @param array $context Contexte de l'erreur
     */
    public function trackError(string $error_type, string $message, array $context = []): void;
/**
     * R?cup?re les m?triques d'utilisation
     *
     * @param string $metric_type Type de m?trique
     * @param array $filters Filtres ? appliquer
     * @return array Donn?es des m?triques
     */
    public function getMetrics(string $metric_type, array $filters = []): array;
/**
     * R?cup?re les templates les plus populaires
     *
     * @param int $limit Nombre maximum de r?sultats
     * @param string $period P?riode (day, week, month)
     * @return array Liste des templates populaires
     */
    public function getPopularTemplates(int $limit = 10, string $period = 'month'): array;
/**
     * R?cup?re les m?triques de performance
     *
     * @param string $operation Op?ration sp?cifique ou 'all'
     * @param string $period P?riode
     * @return array M?triques de performance
     */
    public function getPerformanceMetrics(string $operation = 'all', string $period = 'week'): array;
/**
     * Nettoie les anciennes donn?es d'analytics
     *
     * @param int $days_to_keep Nombre de jours ? conserver
     */
    public function cleanupOldData(int $days_to_keep = 90): void;
}
