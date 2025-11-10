<?php

namespace WP_PDF_Builder_Pro\Elements;

/**
 * Interface TextElementInterface
 * Contrat spécifique pour les éléments texte
 */
interface TextElementInterface extends ElementInterface
{
    /**
     * Récupère le contenu texte
     *
     * @return string Contenu texte
     */
    public function getText(): string;

    /**
     * Définit le contenu texte
     *
     * @param string $text Contenu texte
     */
    public function setText(string $text): void;

    /**
     * Récupère les variables dynamiques dans le texte
     *
     * @return array Liste des variables trouvées
     */
    public function getVariables(): array;
}
