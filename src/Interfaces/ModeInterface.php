<?php
/**
 * Interface commune pour les modes d'aperçu (Canvas/Metabox)
 *
 * Définit les méthodes communes que doivent implémenter tous les modes
 * d'aperçu du système PDF Builder.
 *
 * @package PDF_Builder_Pro
 * @subpackage Interfaces
 */

namespace PDF_Builder_Pro\Interfaces;

/**
 * Interface ModeInterface
 *
 * Définit le contrat commun pour tous les modes d'aperçu.
 * Permet de séparer la logique métier des modes Canvas et Metabox.
 */
interface ModeInterface
{
    /**
     * Initialise le mode avec ses paramètres spécifiques
     *
     * @param array $options Options d'initialisation spécifiques au mode
     * @return bool True si initialisation réussie
     */
    public function initialize(array $options = []): bool;

    /**
     * Récupère le nom du mode (canvas|metabox)
     *
     * @return string Nom du mode
     */
    public function getModeName(): string;

    /**
     * Vérifie si le mode est actif et prêt à être utilisé
     *
     * @return bool True si le mode est actif
     */
    public function isActive(): bool;

    /**
     * Récupère les données spécifiques au mode
     *
     * Pour CanvasMode : données fictives cohérentes
     * Pour MetaboxMode : données WooCommerce réelles
     *
     * @param array $context Contexte de récupération (order_id, template_id, etc.)
     * @return array Données formatées pour le rendu
     */
    public function getData(array $context = []): array;

    /**
     * Valide les données selon les règles du mode
     *
     * @param array $data Données à valider
     * @return array Résultat de validation ['valid' => bool, 'errors' => array]
     */
    public function validateData(array $data): array;

    /**
     * Nettoie les ressources utilisées par le mode
     *
     * @return void
     */
    public function cleanup(): void;

    /**
     * Récupère les options de configuration du mode
     *
     * @return array Options de configuration
     */
    public function getOptions(): array;

    /**
     * Définit une option de configuration
     *
     * @param string $key Clé de l'option
     * @param mixed $value Valeur de l'option
     * @return bool True si option définie avec succès
     */
    public function setOption(string $key, $value): bool;
}