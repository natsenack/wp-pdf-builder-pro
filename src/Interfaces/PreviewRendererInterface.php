<?php
/**
 * Interface pour le système de rendu d'aperçu
 *
 * Définit le contrat pour tous les renderers d'aperçu PDF.
 * Permet d'abstraire le moteur de rendu et faciliter les tests.
 *
 * @package PDF_Builder_Pro
 * @subpackage Interfaces
 */

namespace PDF_Builder_Pro\Interfaces;

/**
 * Interface PreviewRendererInterface
 *
 * Définit les méthodes communes pour tous les renderers d'aperçu.
 * Abstrait le moteur de rendu pour permettre différents backends.
 */
interface PreviewRendererInterface
{
    /**
     * Initialise le renderer avec ses options
     *
     * @param array $options Options d'initialisation
     * @return bool True si initialisation réussie
     */
    public function initialize(array $options = []): bool;

    /**
     * Définit les dimensions du canvas de rendu
     *
     * @param int $width Largeur en pixels
     * @param int $height Hauteur en pixels
     * @return bool True si dimensions définies avec succès
     */
    public function setDimensions(int $width, int $height): bool;

    /**
     * Définit le niveau de zoom
     *
     * @param int $zoom Niveau de zoom demandé (50, 75, 100, 125, 150)
     * @return bool True si zoom défini avec succès
     */
    public function setZoom(int $zoom): bool;

    /**
     * Active/désactive le mode responsive
     *
     * @param bool $responsive True pour activer le responsive
     * @return bool True si mode défini avec succès
     */
    public function setResponsive(bool $responsive): bool;

    /**
     * Définit les dimensions du conteneur parent pour le responsive
     *
     * @param int $width Largeur du conteneur
     * @param int $height Hauteur du conteneur
     * @return bool True si dimensions définies avec succès
     */
    public function setContainerDimensions(int $width, int $height): bool;

    /**
     * Rend un élément spécifique
     *
     * @param array $elementData Données de l'élément à rendre
     * @return array Résultat du rendu ['html' => string, 'css' => string, 'x' => int, 'y' => int, 'width' => int, 'height' => int]
     */
    public function renderElement(array $elementData): array;

    /**
     * Rend un template complet
     *
     * @param array $templateData Données du template
     * @param array $context Contexte de rendu
     * @return array Résultat du rendu complet
     */
    public function renderTemplate(array $templateData, array $context = []): array;

    /**
     * Applique le zoom aux dimensions et positions d'un élément rendu
     *
     * @param array $renderResult Résultat de rendu à ajuster
     * @return array Résultat ajusté avec zoom appliqué
     */
    public function applyZoomToElement(array $renderResult): array;

    /**
     * Applique les calculs responsive aux dimensions et positions
     *
     * @param array $renderResult Résultat de rendu à ajuster
     * @return array Résultat ajusté avec responsive appliqué
     */
    public function applyResponsivePositioning(array $renderResult): array;

    /**
     * Valide les données d'un élément avant rendu
     *
     * @param array $elementData Données à valider
     * @return array Résultat de validation ['valid' => bool, 'errors' => array]
     */
    public function validateElementData(array $elementData): array;

    /**
     * Récupère les options actuelles du renderer
     *
     * @return array Options actuelles
     */
    public function getOptions(): array;

    /**
     * Récupère les dimensions actuelles
     *
     * @return array ['width' => int, 'height' => int]
     */
    public function getDimensions(): array;

    /**
     * Récupère le niveau de zoom actuel
     *
     * @return int Niveau de zoom actuel
     */
    public function getZoom(): int;

    /**
     * Vérifie si le mode responsive est actif
     *
     * @return bool True si responsive actif
     */
    public function isResponsive(): bool;

    /**
     * Récupère l'état des barres de défilement
     *
     * @return array ['horizontal' => bool, 'vertical' => bool]
     */
    public function getScrollbarState(): array;

    /**
     * Nettoie les ressources utilisées par le renderer
     *
     * @return void
     */
    public function cleanup(): void;

    /**
     * Réinitialise le renderer à ses valeurs par défaut
     *
     * @return bool True si réinitialisation réussie
     */
    public function reset(): bool;
}