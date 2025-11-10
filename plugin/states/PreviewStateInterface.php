<?php

namespace WP_PDF_Builder_Pro\States;

/**
 * Interface PreviewStateInterface
 * Définit le contrat pour les états d'aperçu
 */
interface PreviewStateInterface
{
    /**
     * Récupère le nom de l'état
     *
     * @return string Nom de l'état
     */
    public function getName(): string;

    /**
     * Vérifie si l'état permet certaines actions
     *
     * @param string $action Action à vérifier
     * @return bool true si l'action est autorisée
     */
    public function canPerformAction(string $action): bool;

    /**
     * Exécute une transition vers un nouvel état
     *
     * @param string $new_state Nouvel état souhaité
     * @return bool true si la transition est autorisée
     */
    public function canTransitionTo(string $new_state): bool;

    /**
     * Récupère les actions disponibles dans cet état
     *
     * @return array Liste des actions disponibles
     */
    public function getAvailableActions(): array;

    /**
     * Récupère le message d'état pour l'utilisateur
     *
     * @return string Message d'état
     */
    public function getUserMessage(): string;
}
