<?php

/**
 * Conventions de nommage et namespaces pour PDF Builder Pro
 *
 * Ce fichier définit les standards utilisés dans tout le projet
 */

namespace WP_PDF_Builder_Pro;

// =============================================================================
// NAMESPACES PRINCIPAUX
// =============================================================================

/**
 * Namespace racine du plugin
 */
const ROOT_NAMESPACE = 'WP_PDF_Builder_Pro';
/**
 * Sous-namespaces principaux
 */
const NAMESPACES = [
    'API' => ROOT_NAMESPACE . '\API',
    'Core' => ROOT_NAMESPACE . '\Core',
    'Data' => ROOT_NAMESPACE . '\Data',
    'Elements' => ROOT_NAMESPACE . '\Elements',
    'Generators' => ROOT_NAMESPACE . '\Generators',
    'Templates' => ROOT_NAMESPACE . '\Templates',
    'Utilities' => ROOT_NAMESPACE . '\Utilities'
];
// =============================================================================
// CONVENTIONS DE NOMMAGE
// =============================================================================

/**
 * Préfixes pour les classes selon leur rôle
 */
const CLASS_PREFIXES = [
    'interface' => 'Interface',           // DataProviderInterface
    'abstract' => 'Base',                 // BaseGenerator
    'provider' => 'DataProvider',         // SampleDataProvider
    'generator' => 'Generator',           // PDFGenerator
    'api' => 'API',                       // PreviewImageAPI
    'element' => 'Element',               // TextElement
    'utility' => 'Utility',               // FileUtility
    'exception' => 'Exception',           // ValidationException
    'service' => 'Service',               // CacheService
];
/**
 * Suffixes pour les types spécifiques
 */
const CLASS_SUFFIXES = [
    'interface' => 'Interface',           // ElementInterface
    'trait' => 'Trait',                   // LoggableTrait
    'exception' => 'Exception',           // InvalidDataException
];
/**
 * Conventions pour les méthodes
 */
const METHOD_CONVENTIONS = [
    'getter' => 'get',                    // getVariableValue()
    'setter' => 'set',                    // setPosition()
    'boolean' => 'is',                    // isSampleData()
    'action' => 'do',                     // doGenerate()
    'render' => 'render',                 // renderElement()
    'validate' => 'validate',             // validateTemplate()
    'initialize' => 'initialize',         // initialize()
    'generate' => 'generate',             // generateHTML()
];
// =============================================================================
// CONSTANTES DE CONFIGURATION
// =============================================================================

/**
 * Formats de papier supportés
 */
const PAPER_FORMATS = [
    'A4' => ['width' => 210, 'height' => 297],
    'A5' => ['width' => 148, 'height' => 210],
    'Letter' => ['width' => 216, 'height' => 279],
    'Legal' => ['width' => 216, 'height' => 356],
];
/**
 * Orientations supportées
 */
const PAPER_ORIENTATIONS = ['portrait', 'landscape'];
/**
 * Formats d'export supportés
 */
const EXPORT_FORMATS = ['pdf', 'png', 'jpg', 'jpeg'];
/**
 * Types d'éléments supportés
 */
const ELEMENT_TYPES = [
    'text',
    'image',
    'rectangle',
    'circle',
    'line',
    'barcode',
    'qrcode',
    'table',
    'divider'
];
/**
 * Contextes d'utilisation
 */
const CONTEXTS = [
    'canvas' => 'Éditeur Canvas avec données fictives',
    'metabox' => 'Metabox WooCommerce avec données réelles',
    'api' => 'API REST pour génération serveur',
    'preview' => 'Aperçu rapide côté client'
];
// =============================================================================
// CONSTANTES TECHNIQUES
// =============================================================================

/**
 * Configuration DomPDF par défaut
 */
const DOMPDF_DEFAULT_CONFIG = [
    'dpi' => 96,
    'enable_remote' => false,
    'isHtml5ParserEnabled' => true,
    'isFontSubsettingEnabled' => true,
    'defaultMediaType' => 'screen',
    'defaultPaperSize' => 'a4',
    'defaultPaperOrientation' => 'portrait',
    'defaultFont' => 'Arial',
    'debugPng' => false,
    'debugKeepTemp' => false,
    'debugCss' => false,
    'debugLayout' => false,
    'debugLayoutLines' => false,
    'debugLayoutBlocks' => false,
    'debugLayoutInline' => false,
    'debugLayoutPaddingBox' => false,
];
/**
 * Configuration de cache
 */
const CACHE_CONFIG = [
    'transient_prefix' => 'wp_pdf_builder_',
    'transient_expiry' => 3600, // 1 heure
    'file_cache_dir' => 'wp-pdf-builder-cache',
    'max_cache_size' => 100 * 1024 * 1024, // 100MB
];
/**
 * Limites de sécurité
 */
const SECURITY_LIMITS = [
    'max_template_size' => 5 * 1024 * 1024, // 5MB
    'max_elements_per_template' => 100,
    'max_image_size' => 2 * 1024 * 1024, // 2MB
    'rate_limit_per_minute' => 10,
    'max_execution_time' => 30, // secondes
];
// =============================================================================
// PATTERNS DE VALIDATION
// =============================================================================

/**
 * Patterns regex pour validation
 */
const VALIDATION_PATTERNS = [
    'variable' => '/\{\{([^}]+)\}\}/',     // Variables {{variable}}
    'color_hex' => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
    'color_rgb' => '/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/i',
    'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
    'url' => '/^https?:\/\/.+/i',
    'filename' => '/^[a-zA-Z0-9._-]+$/',
];
// =============================================================================
// CODES D'ERREUR
// =============================================================================

/**
 * Codes d'erreur standardisés
 */
const ERROR_CODES = [
    // Erreurs générales
    'GENERAL_ERROR' => 1000,
    'VALIDATION_ERROR' => 1001,
    'PERMISSION_DENIED' => 1002,
    'NOT_FOUND' => 1003,

    // Erreurs de génération
    'GENERATION_FAILED' => 2000,
    'TEMPLATE_INVALID' => 2001,
    'DATA_MISSING' => 2002,
    'PDF_RENDER_ERROR' => 2003,

    // Erreurs de données
    'DATA_PROVIDER_ERROR' => 3000,
    'VARIABLE_NOT_FOUND' => 3001,
    'INVALID_DATA_FORMAT' => 3002,

    // Erreurs système
    'FILESYSTEM_ERROR' => 4000,
    'MEMORY_LIMIT' => 4001,
    'TIMEOUT_ERROR' => 4002,
];
// =============================================================================
// ÉTATS DU SYSTÈME
// =============================================================================

/**
 * États possibles du système d'aperçu
 */
const SYSTEM_STATES = [
    'IDLE' => 'idle',
    'INITIALIZING' => 'initializing',
    'LOADING' => 'loading',
    'GENERATING' => 'generating',
    'READY' => 'ready',
    'ERROR' => 'error',
    'CANCELLED' => 'cancelled',
];
/**
 * Transitions d'état autorisées
 */
const STATE_TRANSITIONS = [
    'idle' => ['initializing', 'loading'],
    'initializing' => ['loading', 'error'],
    'loading' => ['generating', 'error', 'cancelled'],
    'generating' => ['ready', 'error', 'cancelled'],
    'ready' => ['idle'],
    'error' => ['idle'],
    'cancelled' => ['idle'],
];

// =============================================================================
// FONCTIONS UTILITAIRES
// =============================================================================

/**
 * Génère un nom de classe selon les conventions
 *
 * @param string $type Type d'élément (interface, provider, etc.)
 * @param string $name Nom de base
 * @param string $suffix Suffix optionnel
 * @return string Nom de classe formaté
 */
function generateClassName(string $type, string $name, string $suffix = ''): string
{

    $prefix = CLASS_PREFIXES[$type] ?? '';
    $finalSuffix = $suffix ?: (CLASS_SUFFIXES[$type] ?? '');
    return $prefix . $name . $finalSuffix;
}

/**
 * Génère un nom de méthode selon les conventions
 *
 * @param string $convention Type de méthode (getter, setter, etc.)
 * @param string $property Propriété concernée
 * @return string Nom de méthode formaté
 */
function generateMethodName(string $convention, string $property): string
{

    $prefix = METHOD_CONVENTIONS[$convention] ?? '';
    return $prefix . ucfirst($property);
}

/**
 * Valide un format de papier
 *
 * @param string $format Format à valider
 * @return bool true si valide
 */
function isValidPaperFormat(string $format): bool
{

    return isset(PAPER_FORMATS[$format]);
}

/**
 * Valide une orientation
 *
 * @param string $orientation Orientation à valider
 * @return bool true si valide
 */
function isValidOrientation(string $orientation): bool
{

    return in_array($orientation, PAPER_ORIENTATIONS);
}

/**
 * Valide un format d'export
 *
 * @param string $format Format à valider
 * @return bool true si valide
 */
function isValidExportFormat(string $format): bool
{

    return in_array($format, EXPORT_FORMATS);
}

/**
 * Valide un type d'élément
 *
 * @param string $type Type à valider
 * @return bool true si valide
 */
function isValidElementType(string $type): bool
{

    return in_array($type, ELEMENT_TYPES);
}
