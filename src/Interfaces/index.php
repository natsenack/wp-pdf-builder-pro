<?php
/**
 * Export des interfaces du système d'aperçu PDF
 *
 * Ce fichier permet d'importer facilement toutes les interfaces
 * définies pour le système modulaire d'aperçu.
 *
 * @package PDF_Builder_Pro
 */

// Interfaces principales
require_once __DIR__ . '/ModeInterface.php';
require_once __DIR__ . '/DataProviderInterface.php';
require_once __DIR__ . '/PreviewRendererInterface.php';
require_once __DIR__ . '/EventHandlerInterface.php';

// Définition des constantes pour les noms d'interfaces
const PDF_BUILDER_INTERFACES = [
    'ModeInterface' => \PDF_Builder_Pro\Interfaces\ModeInterface::class,
    'DataProviderInterface' => \PDF_Builder_Pro\Interfaces\DataProviderInterface::class,
    'PreviewRendererInterface' => \PDF_Builder_Pro\Interfaces\PreviewRendererInterface::class,
    'EventHandlerInterface' => \PDF_Builder_Pro\Interfaces\EventHandlerInterface::class,
];