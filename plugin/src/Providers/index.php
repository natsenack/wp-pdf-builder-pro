<?php
/**
 * Export des providers de données et gestionnaires de modes
 *
 * @package PDF_Builder_Pro
 * @subpackage Providers
 */

// Inclure les interfaces
require_once __DIR__ . '/../Interfaces/ModeInterface.php';
require_once __DIR__ . '/../Interfaces/DataProviderInterface.php';
require_once __DIR__ . '/../Interfaces/PreviewRendererInterface.php';
require_once __DIR__ . '/../Interfaces/EventHandlerInterface.php';

// Inclure les classes de providers
require_once __DIR__ . '/CanvasModeProvider.php';
require_once __DIR__ . '/MetaboxModeProvider.php';

// Inclure les gestionnaires de modes et DI
require_once __DIR__ . '/../Managers/ModeSwitcher.php';
require_once __DIR__ . '/../Core/DIContainer.php';

// Export des classes pour utilisation
use PDF_Builder_Pro\Interfaces\ModeInterface;
use PDF_Builder_Pro\Interfaces\DataProviderInterface;
use PDF_Builder_Pro\Interfaces\PreviewRendererInterface;
use PDF_Builder_Pro\Interfaces\EventHandlerInterface;
use PDF_Builder_Pro\Providers\CanvasModeProvider;
use PDF_Builder_Pro\Providers\MetaboxModeProvider;
use PDF_Builder_Pro\Managers\ModeSwitcher;
use PDF_Builder_Pro\Core\DIContainer;