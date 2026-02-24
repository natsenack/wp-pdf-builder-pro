<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
if ( ! defined( 'ABSPATH' ) ) exit;

// Système de gestion des propriétés d'éléments
// Définit les restrictions et validations pour chaque type d'élément

// Restrictions des propriétés par catégorie d'élément
const ELEMENT_PROPERTY_RESTRICTIONS = [
    // Éléments spéciaux - contrôle du fond autorisé mais valeur par défaut transparente
    'special' => [
        'backgroundColor' => [
            'disabled' => false, // Maintenant autorisé
            'default' => 'transparent' // Valeur par défaut transparente
        ],
        'borderColor' => [
            'disabled' => false
        ],
        'borderWidth' => [
            'disabled' => false
        ]
    ],

    // Éléments de mise en page - contrôle complet
    'layout' => [
        'backgroundColor' => [
            'disabled' => false,
            'default' => '#f8fafc'
        ],
        'borderColor' => [
            'disabled' => false
        ],
        'borderWidth' => [
            'disabled' => false
        ]
    ],

    // Éléments de texte - contrôle complet
    'text' => [
        'backgroundColor' => [
            'disabled' => false,
            'default' => 'transparent'
        ],
        'borderColor' => [
            'disabled' => false
        ],
        'borderWidth' => [
            'disabled' => false
        ]
    ],

    // Éléments graphiques - contrôle complet
    'shape' => [
        'backgroundColor' => [
            'disabled' => false,
            'default' => '#e5e7eb'
        ],
        'borderColor' => [
            'disabled' => false
        ],
        'borderWidth' => [
            'disabled' => false
        ]
    ],

    // Éléments médias - contrôle limité
    'media' => [
        'backgroundColor' => [
            'disabled' => false,
            'default' => '#f3f4f6'
        ],
        'borderColor' => [
            'disabled' => false
        ],
        'borderWidth' => [
            'disabled' => false
        ]
    ],

    // Éléments dynamiques - contrôle complet
    'dynamic' => [
        'backgroundColor' => [
            'disabled' => false,
            'default' => 'transparent'
        ],
        'borderColor' => [
            'disabled' => false
        ],
        'borderWidth' => [
            'disabled' => false
        ]
    ]
];
// Mapping des types d'éléments vers leurs catégories
const ELEMENT_TYPE_MAPPING = [
    // Spéciaux
    'product_table' => 'special',
    'customer_info' => 'special',
    'company_logo' => 'special',
    'company_info' => 'special',
    'order_number' => 'special',
    'document_type' => 'special',
    'progress_bar' => 'special',

    // Mise en page
    'layout_header' => 'layout',
    'layout_footer' => 'layout',
    'layout_sidebar' => 'layout',
    'layout_section' => 'layout',
    'layout_container' => 'layout',
    'layout_section_divider' => 'layout',
    'layout_spacer' => 'layout',
    'layout_two_column' => 'layout',
    'layout_three_column' => 'layout',

    // Texte
    'text' => 'text',
    'dynamic_text' => 'text',
    'conditional_text' => 'text',
    'counter' => 'text',
    'date_dynamic' => 'text',
    'currency' => 'text',
    'formula' => 'text',

    // Formes
    'rectangle' => 'shape',
    'line' => 'shape',
    'shape_rectangle' => 'shape',
    'shape_circle' => 'shape',
    'shape_line' => 'shape',
    'shape_arrow' => 'shape',
    'shape_triangle' => 'shape',
    'shape_star' => 'shape',
    'divider' => 'shape',

    // Médias
    'image' => 'media',
    'image_upload' => 'media',
    'logo' => 'media',
    'barcode' => 'media',
    'qrcode' => 'media',
    'qrcode_dynamic' => 'media',
    'icon' => 'media',

    // Dynamiques
    'table_dynamic' => 'dynamic',
    'gradient_box' => 'dynamic',
    'shadow_box' => 'dynamic',
    'rounded_box' => 'dynamic',
    'border_box' => 'dynamic',
    'background_pattern' => 'dynamic',
    'watermark' => 'dynamic',

    // Factures (mélange de catégories)
    'invoice_header' => 'layout',
    'invoice_address_block' => 'layout',
    'invoice_info_block' => 'layout',
    'invoice_products_table' => 'special',
    'invoice_totals_block' => 'layout',
    'invoice_payment_terms' => 'layout',
    'invoice_legal_footer' => 'layout',
    'invoice_signature_block' => 'layout'
];

/**
 * Vérifie si une propriété est autorisée pour un type d'élément
 */
function isPropertyAllowed(string $elementType, string $propertyName): bool
{

    global $ELEMENT_TYPE_MAPPING, $ELEMENT_PROPERTY_RESTRICTIONS;
    $category = $ELEMENT_TYPE_MAPPING[$elementType] ?? 'text';
// défaut texte
    $restrictions = $ELEMENT_PROPERTY_RESTRICTIONS[$category] ?? null;
    if (!$restrictions || !isset($restrictions[$propertyName])) {
        return true;
    // propriété autorisée par défaut
    }

    return !$restrictions[$propertyName]['disabled'];
}

/**
 * Obtient la valeur par défaut d'une propriété
 */
function getPropertyDefault(string $elementType, string $propertyName)
{

    global $ELEMENT_TYPE_MAPPING, $ELEMENT_PROPERTY_RESTRICTIONS;
    $category = $ELEMENT_TYPE_MAPPING[$elementType] ?? 'text';
    $restrictions = $ELEMENT_PROPERTY_RESTRICTIONS[$category] ?? null;
    if ($restrictions && isset($restrictions[$propertyName]) && isset($restrictions[$propertyName]['default'])) {
        return $restrictions[$propertyName]['default'];
    }

    return null; // pas de valeur par défaut spécifique
}

/**
 * Valide une propriété
 */
function validateProperty(string $elementType, string $propertyName, $value): array
{

    if (!isPropertyAllowed($elementType, $propertyName)) {
        global $ELEMENT_TYPE_MAPPING, $ELEMENT_PROPERTY_RESTRICTIONS;
        $category = $ELEMENT_TYPE_MAPPING[$elementType] ?? 'text';
        $restrictions = $ELEMENT_PROPERTY_RESTRICTIONS[$category] ?? null;
        $reason = 'Propriété non autorisée';
        if ($restrictions && isset($restrictions[$propertyName]) && isset($restrictions[$propertyName]['reason'])) {
            $reason = $restrictions[$propertyName]['reason'];
        }

        return [
            'valid' => false,
            'reason' => $reason
        ];
    }

    // Validations spécifiques selon le type de propriété
    switch ($propertyName) {
        case 'backgroundColor':
            if (!is_string($value)) {
                return ['valid' => false, 'reason' => 'La couleur doit être une chaîne'];
            }
            // Plus de restriction pour les éléments spéciaux - ils peuvent maintenant avoir un fond

            break;
        case 'borderWidth':
            if (!is_numeric($value) || $value < 0) {
                return ['valid' => false, 'reason' => 'La largeur de bordure doit être un nombre positif'];
            }

            break;
        case 'fontSize':
            if (!is_numeric($value) || $value <= 0) {
                return ['valid' => false, 'reason' => 'La taille de police doit être un nombre positif'];
            }

            break;
        case 'width':
        case 'height':
            if (!is_numeric($value) || $value <= 0) {
                return ['valid' => false, 'reason' => 'Les dimensions doivent être positives'];
            }

            break;
        default:
            break;
    }

    return ['valid' => true];
}

/**
 * Corrige automatiquement une propriété invalide
 */
function fixInvalidProperty(string $elementType, string $propertyName, $invalidValue)
{

    // Pour les éléments spéciaux, backgroundColor peut maintenant être contrôlé
    // (pas de forçage automatique à 'transparent')

    // Valeurs par défaut pour les propriétés numériques
    $numericDefaults = [
        'borderWidth' => 0,
        'fontSize' => 14,
        'width' => 100,
        'height' => 50,
        'padding' => 8
    ];
    if (isset($numericDefaults[$propertyName])) {
        return $numericDefaults[$propertyName];
    }

    // Valeurs par défaut pour les chaînes
    $stringDefaults = [
        'backgroundColor' => 'transparent',
        'borderColor' => 'transparent',
        'color' => '#000000',
        'fontFamily' => 'Arial, sans-serif'
    ];
    return $stringDefaults[$propertyName] ?? $invalidValue;
}



