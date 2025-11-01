/**
 * Utilitaires de conversion d'unités (PX <-> MM)
 * A4: 595px = 210mm (largeur), 1123px = 297mm (hauteur)
 */

export const MM_TO_PX = 595 / 210; // ≈ 2.833
export const PX_TO_MM = 210 / 595; // ≈ 0.353

/**
 * Convertir millimètres en pixels
 */
export const mmToPx = (mm: number): number => Math.round(mm * MM_TO_PX);

/**
 * Convertir pixels en millimètres
 */
export const pxToMm = (px: number): number => Math.round(px * PX_TO_MM * 10) / 10; // Arrondir à 1 décimale

/**
 * Formater une valeur en MM avec unité
 */
export const formatMM = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return '0 mm';
  return `${pxToMm(value)} mm`;
};

/**
 * Formater une valeur en MM avec 2 décimales
 */
export const formatMM2 = (value: number | null | undefined): string => {
  if (value === null || value === undefined) return '0.00 mm';
  const mm = pxToMm(value);
  return `${mm.toFixed(2)} mm`;
};

/**
 * Convertir une valeur MM entrée en pixels
 */
export const mmValueToPx = (mmValue: number): number => {
  return Math.round(mmValue * MM_TO_PX);
};

/**
 * Convertir tous les éléments de PX à MM
 */
export const convertElementsToMM = (elements: any[]): any[] => {
  return elements.map(element => ({
    ...element,
    x: pxToMm(element.x),
    y: pxToMm(element.y),
    width: pxToMm(element.width),
    height: pxToMm(element.height),
    _unitConverted: true
  }));
};
