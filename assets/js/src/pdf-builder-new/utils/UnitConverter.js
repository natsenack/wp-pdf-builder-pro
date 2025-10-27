/**
 * Unit Converter - Convertisseur d'unités pour le PDF
 * Gestion précise des conversions mm/cm/in/px
 */

export class UnitConverter {
    constructor() {
        // Facteurs de conversion vers pixels (base 595px = 210mm à 72 DPI)
        this.conversionFactors = {
            mm: 595 / 210,      // ~2.833px par mm
            cm: 595 / 21,       // ~28.333px par cm
            in: 595 / 8.27,     // ~72px par pouce (DPI standard)
            px: 1               // Pixel de base
        };

        // Précision des calculs
        this.precision = 100; // 2 décimales
    }

    /**
     * Conversion d'une valeur vers pixels
     */
    toPixels(value, fromUnit = 'px') {
        if (typeof value !== 'number' || value < 0) {
            throw new Error('Valeur invalide pour la conversion');
        }

        const factor = this.conversionFactors[fromUnit.toLowerCase()];
        if (!factor) {
            throw new Error(`Unité non supportée: ${fromUnit}`);
        }

        return Math.round(value * factor * this.precision) / this.precision;
    }

    /**
     * Conversion depuis pixels vers une unité
     */
    fromPixels(pxValue, toUnit = 'px') {
        if (typeof pxValue !== 'number' || pxValue < 0) {
            throw new Error('Valeur en pixels invalide');
        }

        const factor = this.conversionFactors[toUnit.toLowerCase()];
        if (!factor) {
            throw new Error(`Unité non supportée: ${toUnit}`);
        }

        return Math.round(pxValue / factor * this.precision) / this.precision;
    }

    /**
     * Conversion directe entre deux unités
     */
    convert(value, fromUnit, toUnit) {
        const pxValue = this.toPixels(value, fromUnit);
        return this.fromPixels(pxValue, toUnit);
    }

    /**
     * Formatage d'une valeur avec son unité
     */
    format(value, unit = 'px', decimals = 2) {
        return `${value.toFixed(decimals)} ${unit}`;
    }

    /**
     * Validation d'une unité
     */
    isValidUnit(unit) {
        return unit.toLowerCase() in this.conversionFactors;
    }

    /**
     * Liste des unités supportées
     */
    getSupportedUnits() {
        return Object.keys(this.conversionFactors);
    }

    /**
     * Informations sur une unité
     */
    getUnitInfo(unit) {
        const lowerUnit = unit.toLowerCase();
        const factor = this.conversionFactors[lowerUnit];

        if (!factor) return null;

        return {
            unit: lowerUnit,
            factor,
            pixelsPerUnit: factor,
            description: this._getUnitDescription(lowerUnit)
        };
    }

    /**
     * Description d'une unité
     * @private
     */
    _getUnitDescription(unit) {
        const descriptions = {
            mm: 'Millimètre - Unité métrique standard',
            cm: 'Centimètre - Unité métrique pratique',
            in: 'Pouce - Unité impériale standard',
            px: 'Pixel - Unité numérique de base'
        };
        return descriptions[unit] || 'Unité inconnue';
    }

    /**
     * Conversion de coordonnées (x, y)
     */
    convertPoint(point, fromUnit, toUnit) {
        return {
            x: this.convert(point.x, fromUnit, toUnit),
            y: this.convert(point.y, fromUnit, toUnit)
        };
    }

    /**
     * Conversion de dimensions (width, height)
     */
    convertSize(size, fromUnit, toUnit) {
        return {
            width: this.convert(size.width, fromUnit, toUnit),
            height: this.convert(size.height, fromUnit, toUnit)
        };
    }

    /**
     * Conversion de rectangle (x, y, width, height)
     */
    convertRect(rect, fromUnit, toUnit) {
        return {
            x: this.convert(rect.x, fromUnit, toUnit),
            y: this.convert(rect.y, fromUnit, toUnit),
            width: this.convert(rect.width, fromUnit, toUnit),
            height: this.convert(rect.height, fromUnit, toUnit)
        };
    }

    /**
     * Normalisation d'une valeur (assure la cohérence)
     */
    normalize(value, unit = 'px') {
        // Conversion vers pixels puis retour pour normaliser
        const px = this.toPixels(value, unit);
        return this.fromPixels(px, unit);
    }

    /**
     * Calcul de ratio d'échelle entre deux unités
     */
    getScaleRatio(fromUnit, toUnit) {
        const fromFactor = this.conversionFactors[fromUnit.toLowerCase()];
        const toFactor = this.conversionFactors[toUnit.toLowerCase()];

        if (!fromFactor || !toFactor) {
            throw new Error('Unités non supportées');
        }

        return fromFactor / toFactor;
    }

    /**
     * Vérification de précision (évite les erreurs d'arrondi)
     */
    checkPrecision(value, unit = 'px') {
        const normalized = this.normalize(value, unit);
        const diff = Math.abs(value - normalized);

        return {
            original: value,
            normalized,
            difference: diff,
            isPrecise: diff < (1 / this.precision)
        };
    }

    /**
     * Configuration de la précision
     */
    setPrecision(decimals) {
        if (decimals < 0 || decimals > 10) {
            throw new Error('Précision invalide (0-10 décimales)');
        }
        this.precision = Math.pow(10, decimals);
    }

    /**
     * Réinitialisation aux valeurs par défaut
     */
    reset() {
        this.precision = 100; // 2 décimales
    }
}

// Instance globale pour utilisation facile
export const unitConverter = new UnitConverter();