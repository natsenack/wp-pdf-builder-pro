/**
 * PDF Builder Pro - Configuration Unifiée v6.0.0
 * Système centralisé pour assurer la cohérence canvas/aperçu
 */

if (typeof window.PDF_BUILDER_UNIFIED_CONFIG !== 'undefined') {
    console.warn('PDF_BUILDER_UNIFIED_CONFIG déjà déclaré');
} else {

const PDF_BUILDER_UNIFIED_CONFIG = {
    VERSION: '6.0.0',
    DEBUG: true,

    // DIMENSIONS UNIFIÉES - Dynamiques basées sur les options
    CANVAS: {
        // Dimensions par défaut A4 (seront remplacées dynamiquement)
        WIDTH: 595,
        HEIGHT: 842,
        WIDTH_MM: 210,
        HEIGHT_MM: 297,
        DPI: 96,
        MM_TO_PX: 3.779527559,
        PX_TO_MM: 0.264583333,
        MARGINS: {
            TOP: 57,
            RIGHT: 57,
            BOTTOM: 57,
            LEFT: 57
        }
    },

    // Méthode pour initialiser les dimensions dynamiquement
    initDimensions: function(options) {
        if (!options || !options.pageSize) return;
        
        const pageSize = options.pageSize;
        if (pageSize.type === 'custom') {
            this.CANVAS.WIDTH = pageSize.width;
            this.CANVAS.HEIGHT = pageSize.height;
            this.CANVAS.WIDTH_MM = Math.round(pageSize.width * this.CANVAS.PX_TO_MM);
            this.CANVAS.HEIGHT_MM = Math.round(pageSize.height * this.CANVAS.PX_TO_MM);
        } else {
            // Utiliser les formats prédéfinis
            const formats = {
                'A4': { width: 595, height: 842, width_mm: 210, height_mm: 297 },
                'A3': { width: 842, height: 1191, width_mm: 297, height_mm: 420 },
                'LETTER': { width: 612, height: 792, width_mm: 216, height_mm: 279 },
                'LEGAL': { width: 612, height: 1008, width_mm: 216, height_mm: 356 }
            };
            const format = formats[pageSize.type] || formats['A4'];
            this.CANVAS.WIDTH = format.width;
            this.CANVAS.HEIGHT = format.height;
            this.CANVAS.WIDTH_MM = format.width_mm;
            this.CANVAS.HEIGHT_MM = format.height_mm;
        }
        
        // Recalculer les marges si nécessaire
        if (options.margins) {
            this.CANVAS.MARGINS = {
                TOP: options.margins.top || 57,
                RIGHT: options.margins.right || 57,
                BOTTOM: options.margins.bottom || 57,
                LEFT: options.margins.left || 57
            };
        }
        
        this.utils.debug('Dimensions canvas initialisées', this.CANVAS);
    },

    // ÉCHELLES ET ZOOM UNIFIÉS
    SCALE: {
        // Facteur d'échelle principal (1.0 = taille réelle)
        BASE_SCALE: 1.0,
        
        // Échelles d'aperçu prédéfinies
        PREVIEW_SCALES: {
            MODAL: 0.85,      // Pour la modale WooCommerce
            SIDEBAR: 0.75,    // Pour l'aperçu latéral
            THUMBNAIL: 0.25   // Pour les miniatures
        },
        
        // Limites de zoom
        MIN_ZOOM: 0.25,
        MAX_ZOOM: 3.0,
        ZOOM_STEP: 0.25,
        
        // Zoom par défaut
        DEFAULT_ZOOM: 1.0,
        FIT_ZOOM: 'auto' // Calculé automatiquement
    },

    // SYSTÈME DE POSITIONNEMENT UNIFIÉ
    POSITIONING: {
        // Propriétés de position utilisées partout
        POSITION_PROPS: ['left', 'top', 'width', 'height'],
        
        // Fallback pour anciennes versions
        LEGACY_PROPS: ['x', 'y'],
        
        // Grille d'alignement
        GRID_SIZE: 10,
        SNAP_THRESHOLD: 5,
        
        // Z-index par défaut
        BASE_Z_INDEX: 1
    },

    // CONVERSION CANVAS <-> APERÇU
    CONVERSION: {
        // Facteur de conversion unifié
        CANVAS_TO_PREVIEW: 1.0,
        PREVIEW_TO_CANVAS: 1.0,
        
        // Propriétés à préserver lors de la conversion
        PRESERVED_PROPS: [
            'id', 'type', 'content', 'styles',
            'left', 'top', 'width', 'height',
            'zIndex', 'visible', 'locked'
        ],
        
        // Styles par défaut pour les éléments
        DEFAULT_STYLES: {
            position: 'absolute',
            fontFamily: 'Arial, sans-serif',
            fontSize: '14px',
            color: '#1e293b',
            backgroundColor: 'transparent',
            textAlign: 'left',
            padding: '4px',
            boxSizing: 'border-box',
            overflow: 'hidden',
            wordWrap: 'break-word',
            whiteSpace: 'pre-wrap'
        }
    },

    // PERFORMANCE ET CACHE
    PERFORMANCE: {
        PREVIEW_DEBOUNCE: 300,    // ms
        CANVAS_THROTTLE: 100,     // ms
        CACHE_DURATION: 300000,   // 5 minutes
        MAX_CACHE_SIZE: 50,       // éléments
        LAZY_LOAD_THRESHOLD: 20   // éléments
    },

    // CSS UNIFIÉ POUR APERÇU
    PREVIEW_CSS: `
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: white;
            width: 794px;
            height: 1123px;
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        .pdf-container {
            position: relative;
            width: 794px;
            height: 1123px;
            background: white;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .pdf-element {
            position: absolute;
            box-sizing: border-box;
            word-wrap: break-word;
            overflow: hidden;
        }
        
        /* Masquer les éléments d'édition dans l'aperçu */
        .element-handle,
        .element-toolbar,
        .resize-handles,
        .resize-handle,
        .element-controls {
            display: none !important;
        }
        
        /* Styles par type d'élément */
        .pdf-element.text {
            white-space: pre-wrap;
            word-break: break-word;
        }
        
        .pdf-element.title {
            font-weight: bold;
        }
        
        .pdf-element.image img {
            max-width: 100%;
            height: auto;
        }
        
        .pdf-element.table table {
            border-collapse: collapse;
            width: 100%;
        }
        
        .pdf-element.table td,
        .pdf-element.table th {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
    `,

    // MESSAGES D'ERREUR ET DEBUG
    MESSAGES: {
        CANVAS_NOT_FOUND: '❌ Canvas introuvable',
        PREVIEW_ERROR: '❌ Erreur lors de la génération de l\'aperçu',
        SCALE_ERROR: '❌ Erreur de facteur d\'échelle',
        CONVERSION_ERROR: '❌ Erreur de conversion canvas/aperçu',
        SUCCESS: '✅ Opération réussie',
        LOADING: '⏳ Chargement...'
    },

    // UTILITAIRES DE CONVERSION
    utils: {
        /**
         * Convertit des millimètres en pixels
         */
        mmToPx: function(mm) {
            return Math.round(mm * this.CANVAS.MM_TO_PX);
        },

        /**
         * Convertit des pixels en millimètres
         */
        pxToMm: function(px) {
            return Math.round(px / this.CANVAS.MM_TO_PX * 100) / 100;
        },

        /**
         * Calcule l'échelle pour s'adapter à un conteneur
         */
        calculateFitScale: function(containerWidth, containerHeight, padding = 40) {
            const availableWidth = containerWidth - padding;
            const availableHeight = containerHeight - padding;

            const scaleX = availableWidth / this.CANVAS.WIDTH;
            const scaleY = availableHeight / this.CANVAS.HEIGHT;

            return Math.min(scaleX, scaleY, this.SCALE.MAX_ZOOM);
        },

        /**
         * Applique le facteur d'échelle à des dimensions
         */
        scaleElement: function(element, scale = 1.0) {
            const scaled = { ...element };

            // Appliquer l'échelle aux propriétés de position et taille
            if (scaled.left !== undefined) scaled.left *= scale;
            if (scaled.top !== undefined) scaled.top *= scale;
            if (scaled.width !== undefined) scaled.width *= scale;
            if (scaled.height !== undefined) scaled.height *= scale;

            // Fallback pour anciennes propriétés
            if (scaled.x !== undefined) scaled.x *= scale;
            if (scaled.y !== undefined) scaled.y *= scale;

            return scaled;
        },

        /**
         * Normalise les propriétés d'un élément
         */
        normalizeElement: function(element) {
            const normalized = { ...element };

            // Utiliser les nouvelles propriétés si disponibles
            if (normalized.x !== undefined && normalized.left === undefined) {
                normalized.left = normalized.x;
                delete normalized.x;
            }
            if (normalized.y !== undefined && normalized.top === undefined) {
                normalized.top = normalized.y;
                delete normalized.y;
            }

            // Valeurs par défaut
            normalized.left = normalized.left || 0;
            normalized.top = normalized.top || 0;
            normalized.width = normalized.width || 100;
            normalized.height = normalized.height || 20;
            normalized.zIndex = normalized.zIndex || this.POSITIONING.BASE_Z_INDEX;

            return normalized;
        },

        /**
         * Log de debug unifié
         */
        debug: function(message, data = null) {
            if (this.DEBUG) {
                console.log(`[PDF Builder Unified] ${message}`, data);
            }
        },

        /**
         * Log d'erreur unifié
         */
        error: function(message, error = null) {
            console.error(`[PDF Builder Unified ERROR] ${message}`, error);
        }
    }
};

// Exposer globalement
window.PDF_BUILDER_UNIFIED_CONFIG = PDF_BUILDER_UNIFIED_CONFIG;

// Alias pour compatibilité
window.PDF_DIMENSIONS = PDF_BUILDER_UNIFIED_CONFIG.CANVAS;
window.PDF_SCALE_CONFIG = PDF_BUILDER_UNIFIED_CONFIG.SCALE;

console.log('✅ PDF Builder Unified Config initialisé v6.0.0');

}