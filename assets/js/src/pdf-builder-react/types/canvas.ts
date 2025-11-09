/**
 * PDF Builder Canvas - Type Definitions & Interfaces
 * 
 * Définitions TypeScript complètes avec JSDoc pour toutes
 * les structures de données du système canvas.
 * 
 * @packageDocumentation
 * @module types/canvas
 * @version 1.0.0
 */

/**
 * Élément du canvas PDF Builder
 * 
 * Représente un élément dessinable sur le canvas
 * (texte, image, ligne, table, etc)
 * 
 * @interface Element
 * @since 1.0.0
 */
export interface Element {
    /** Identifiant unique de l'élément */
    id: string;
    
    /** Type d'élément (text, image, line, table, etc) */
    type: string;
    
    /** Position X en pixels */
    x: number;
    
    /** Position Y en pixels */
    y: number;
    
    /** Largeur en pixels */
    width: number;
    
    /** Hauteur en pixels */
    height: number;
    
    /** Contenu textuel (pour éléments texte) */
    content?: string;
    
    /** URL de l'image (pour éléments image) */
    src?: string;
    
    /** Styles CSS appliqués */
    style?: CSSStyle;
    
    /** Visibilité de l'élément */
    visible?: boolean;
    
    /** Élément verrouillé (non-modifiable) */
    locked?: boolean;
    
    /** Index de couche (z-index) */
    zIndex?: number;
    
    /** Rotation en degrés */
    rotation?: number;
    
    /** Opacité (0-1) */
    opacity?: number;
    
    /** Métadonnées personnalisées */
    metadata?: Record<string, any>;
}

/**
 * Styles CSS pour un élément
 * 
 * Propriétés de style appliquées au rendu de l'élément
 * 
 * @interface CSSStyle
 * @since 1.0.0
 */
export interface CSSStyle {
    /** Taille de police en pixels */
    fontSize?: number;
    
    /** Poids de police (normal, bold, 700, etc) */
    fontWeight?: string | number;
    
    /** Couleur du texte (hex, rgb, etc) */
    color?: string;
    
    /** Alignement du texte */
    textAlign?: 'left' | 'center' | 'right' | 'justify';
    
    /** Alignement vertical */
    verticalAlign?: 'top' | 'middle' | 'bottom';
    
    /** Couleur de fond */
    backgroundColor?: string;
    
    /** Couleur de la bordure */
    borderColor?: string;
    
    /** Épaisseur de la bordure en pixels */
    borderWidth?: number;
    
    /** Style de la bordure */
    borderStyle?: 'solid' | 'dashed' | 'dotted';
    
    /** Famille de police */
    fontFamily?: string;
    
    /** Transformation CSS */
    transform?: string;
    
    /** Autres styles personnalisés */
    [key: string]: any;
}

/**
 * État du canvas
 * 
 * Propriétés de configuration du canvas
 * (zoom, pan, grid, etc)
 * 
 * @interface CanvasState
 * @since 1.0.0
 */
export interface CanvasState {
    /** Niveau de zoom (100 = 100%) */
    zoom: number;
    
    /** Position de pan */
    pan: PanPosition;
    
    /** Largeur du canvas en pixels */
    width: number;
    
    /** Hauteur du canvas en pixels */
    height: number;
    
    /** Afficher la grille */
    showGrid?: boolean;
    
    /** Activer le magnétisme à la grille */
    snapToGrid?: boolean;
    
    /** Espacement de la grille en pixels */
    gridSize?: number;
    
    /** Format de page (A4, Letter, etc) */
    format?: string;
    
    /** Orientation (portrait, landscape) */
    orientation?: 'portrait' | 'landscape';
    
    /** Unité de mesure (px, cm, mm, inch) */
    unit?: string;
    
    /** Afficher les guides */
    showGuides?: boolean;
    
    /** Guides personnalisés */
    guides?: number[];
}

/**
 * Position de pan/translation
 * 
 * @interface PanPosition
 * @since 1.0.0
 */
export interface PanPosition {
    /** Translation X en pixels */
    x: number;
    
    /** Translation Y en pixels */
    y: number;
}

/**
 * Template PDF Builder
 * 
 * Structure complète d'un template avec tous les éléments
 * 
 * @interface Template
 * @since 1.0.0
 */
export interface Template {
    /** ID unique du template */
    id: number;
    
    /** Nom du template */
    name: string;
    
    /** Description du template */
    description?: string;
    
    /** Array de tous les éléments du canvas */
    elements: Element[];
    
    /** Configuration du canvas */
    canvas: CanvasState;
    
    /** Métadonnées du template */
    metadata?: TemplateMetadata;
    
    /** Timestamp de création (Unix timestamp) */
    created_at?: number;
    
    /** Timestamp de dernière modification (Unix timestamp) */
    updated_at?: number;
    
    /** Version du format de template */
    version?: string;
}

/**
 * Métadonnées du template
 * 
 * @interface TemplateMetadata
 * @since 1.0.0
 */
export interface TemplateMetadata {
    /** Auteur du template */
    author?: string;
    
    /** Balises/tags du template */
    tags?: string[];
    
    /** Statut de publication */
    status?: 'draft' | 'published' | 'archived';
    
    /** Nombre de téléchargements/utilisations */
    usage_count?: number;
    
    /** Notes/commentaires privés */
    notes?: string;
    
    /** Propriétés personnalisées */
    [key: string]: any;
}

/**
 * Réponse AJAX pour le chargement de template
 * 
 * @interface LoadTemplateResponse
 * @since 1.0.0
 */
export interface LoadTemplateResponse {
    /** Succès de l'opération */
    success: boolean;
    
    /** Données du template chargé */
    data?: Template;
    
    /** Message d'erreur si échoué */
    error?: string;
    
    /** Timestamp de la réponse */
    timestamp?: number;
    
    /** Métadonnées de la réponse */
    meta?: {
        from_cache?: boolean;
        cached_at?: number;
        processing_time_ms?: number;
    };
}

/**
 * Réponse AJAX pour la sauvegarde de template
 * 
 * @interface SaveTemplateResponse
 * @since 1.0.0
 */
export interface SaveTemplateResponse {
    /** Succès de l'opération */
    success: boolean;
    
    /** Données retournées */
    data?: {
        id: number;
        name: string;
        timestamp: number;
        elementCount: number;
        message: string;
    };
    
    /** Message d'erreur si échoué */
    error?: string;
}

/**
 * Options de rendu canvas
 * 
 * @interface RenderOptions
 * @since 1.0.0
 */
export interface RenderOptions {
    /** Afficher la grille */
    showGrid?: boolean;
    
    /** Afficher les guides */
    showGuides?: boolean;
    
    /** Afficher la sélection */
    showSelection?: boolean;
    
    /** Niveau de qualité de rendu (1-3) */
    quality?: 1 | 2 | 3;
    
    /** Appliquer les transformations */
    applyTransforms?: boolean;
}

/**
 * Options d'export PDF
 * 
 * @interface ExportOptions
 * @since 1.0.0
 */
export interface ExportOptions {
    /** Format de page (A4, Letter, etc) */
    format: string;
    
    /** Orientation */
    orientation: 'portrait' | 'landscape';
    
    /** Qualité d'export (72, 150, 300 dpi) */
    quality: 72 | 150 | 300;
    
    /** Compression */
    compress?: boolean;
    
    /** Ajouter numéro de page */
    addPageNumbers?: boolean;
    
    /** Ajouter un pied de page */
    footer?: string;
}

/**
 * Notification d'événement canvas
 * 
 * @interface CanvasEvent
 * @since 1.0.0
 */
export interface CanvasEvent {
    /** Type d'événement */
    type: 'element_added' | 'element_removed' | 'element_updated' | 'canvas_changed' | 'save';
    
    /** Données associées à l'événement */
    data: any;
    
    /** Timestamp de l'événement */
    timestamp: number;
    
    /** Utilisateur qui a déclenché l'événement */
    user_id?: number;
}

/**
 * Erreur de validation
 * 
 * @interface ValidationError
 * @since 1.0.0
 */
export interface ValidationError {
    /** Champ en erreur */
    field: string;
    
    /** Message d'erreur */
    message: string;
    
    /** Valeur rejetée */
    value?: any;
    
    /** Code d'erreur */
    code?: string;
}

/**
 * Résultat de validation
 * 
 * @interface ValidationResult
 * @since 1.0.0
 */
export interface ValidationResult {
    /** Est valide */
    valid: boolean;
    
    /** Erreurs trouvées */
    errors: ValidationError[];
    
    /** Avertissements */
    warnings?: string[];
}
