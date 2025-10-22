<?php
/**
 * Interface pour le gestionnaire d'événements
 *
 * Définit le contrat pour le système d'événements asynchrones
 * du système d'aperçu PDF.
 *
 * @package PDF_Builder_Pro
 * @subpackage Interfaces
 */

namespace PDF_Builder_Pro\Interfaces;

/**
 * Interface EventHandlerInterface
 *
 * Définit les méthodes pour gérer les événements du système d'aperçu.
 * Permet de découpler les composants via un système d'événements.
 */
interface EventHandlerInterface
{
    /**
     * Enregistre un écouteur pour un événement spécifique
     *
     * @param string $eventName Nom de l'événement
     * @param callable $listener Fonction écouteur
     * @param int $priority Priorité d'exécution (plus élevé = exécuté en premier)
     * @return bool True si enregistrement réussi
     */
    public function on(string $eventName, callable $listener, int $priority = 10): bool;

    /**
     * Supprime un écouteur pour un événement spécifique
     *
     * @param string $eventName Nom de l'événement
     * @param callable $listener Fonction écouteur à supprimer
     * @return bool True si suppression réussie
     */
    public function off(string $eventName, callable $listener): bool;

    /**
     * Déclenche un événement avec des données optionnelles
     *
     * @param string $eventName Nom de l'événement
     * @param array $data Données à passer aux écouteurs
     * @return bool True si événement déclenché avec succès
     */
    public function trigger(string $eventName, array $data = []): bool;

    /**
     * Vérifie si un événement a des écouteurs enregistrés
     *
     * @param string $eventName Nom de l'événement
     * @return bool True si des écouteurs sont enregistrés
     */
    public function hasListeners(string $eventName): bool;

    /**
     * Récupère le nombre d'écouteurs pour un événement
     *
     * @param string $eventName Nom de l'événement
     * @return int Nombre d'écouteurs
     */
    public function getListenerCount(string $eventName): int;

    /**
     * Nettoie tous les écouteurs (utile pour les tests)
     *
     * @return void
     */
    public function clearAllListeners(): void;

    /**
     * Récupère la liste des événements disponibles
     *
     * @return array Liste des noms d'événements
     */
    public function getAvailableEvents(): array;
}