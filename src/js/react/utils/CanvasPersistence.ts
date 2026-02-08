/**
 * COUCHE UNIQUE DE PERSISTANCE CANVAS
 * 
 * Responsabilité: Gérer TOUT ce qui concerne la sérialisation/désérialisation
 * des données du canvas et de ses éléments - UN SEUL endroit pour toute la logique.
 * 
 * Principes:
 * - Pas de traitement complexe
 * - Format JSON simple et standard: { elements: [], canvas: {} }
 * - Pas de support legacy
 * - Normalisation automatique et transparente
 * - Support des valeurs fictives vs réelles via ValueResolver
 */

import type { Element } from '../types/elements';
import { ValueResolver, type RealOrderData, type ElementValueConfig } from '../persistence/ValueResolver';

export interface CanvasData {
  elements: Element[];
  canvasWidth: number;   // ✅ Propriété attendue par le validateur PHP
  canvasHeight: number;  // ✅ Propriété attendue par le validateur PHP
  version: string;
}

export interface CanvasState {
  width: number;
  height: number;
}

/**
 * SÉRIALISER: Prépare les données pour la sauvegarde
 * - Valide que c'est un array
 * - Vérifie que chaque élément a id/type/position/dimensions
 * - Ajoute les valeurs par défaut manquantes
 * - Retourne du JSON prêt à envoyer
 */
export function serializeCanvasData(
  elements: Element[],
  canvas: Partial<CanvasState> = {}
): string {
  // Valider les éléments
  if (!Array.isArray(elements)) {
    console.warn('[CanvasPersistence] Elements n\'est pas un array, utilisant []');
    elements = [];
  }

  // Nettoyer et valider chaque élément
  const cleanElements = elements.map((el, idx) => {
    if (!el || typeof el !== 'object') {
      console.warn(`[CanvasPersistence] Element ${idx} invalide`);
      return null;
    }

    // ✅ CRITICAL FIX: D'abord le spread, PUIS on écrase avec les valeurs validées
    // Cela évite que `...el` écrase les valeurs par défaut
    const serialized = {
      ...el,  // ← SPREADER EN PREMIER pour avoir toutes les propriétés
      
      // Propriétés de base (validées et garanties de présence)
      id: el.id || `element-${idx}`,
      type: el.type || 'unknown',
      x: typeof el.x === 'number' ? el.x : 0,      // ← valider ET écraser
      y: typeof el.y === 'number' ? el.y : 0,      // ← valider ET écraser
      width: typeof el.width === 'number' ? el.width : 100,
      height: typeof el.height === 'number' ? el.height : 100,
    };

    // 🔍 LOG DEBUG
    if (el.type === 'company_logo') {
      console.log(`[🔍 SERIALIZE] Element ${el.id} (${el.type}):`, {
        x: serialized.x,
        y: serialized.y,
        width: serialized.width,
        height: serialized.height,
        logoUrl: serialized.logoUrl,
        all_keys: Object.keys(serialized).sort()
      });
    }

    return serialized;
  }).filter((el): el is Element => el !== null);

  // Canvas data avec défauts
  const canvasState: CanvasState = {
    width: typeof canvas.width === 'number' ? canvas.width : 210,
    height: typeof canvas.height === 'number' ? canvas.height : 297,
  };

  // Structure finale - CORRESPONDRE AU VALIDATEUR PHP
  // PHP attend: { elements, canvasWidth, canvasHeight, version }
  // Pas: { elements, canvas: { width, height }, version }
  const data: any = {
    elements: cleanElements,
    canvasWidth: canvasState.width,    // ✅ Clé attendue par PHP
    canvasHeight: canvasState.height,  // ✅ Clé attendue par PHP
    version: '1.0',
  };

  // Retourner en JSON
  try {
    return JSON.stringify(data);
  } catch (error) {
    console.error('[CanvasPersistence] Erreur sérialisation:', error);
    return JSON.stringify({ elements: [], canvas: canvasState, version: '1.0' });
  }
}

/**
 * DÉSÉRIALISER: Charge les données depuis la DB
 * - Parse le JSON (ou supporte déjà-parsé)
 * - Valide la structure
 * - Normalise automatiquement
 * - Retourne { elements, canvas } propres
 * 
 * @param jsonData - Données JSON à désérialiser
 * @param options - Configuration optionnelle (mode, données réelles)
 */
export function deserializeCanvasData(
  jsonData: string | object,
  options?: {
    mode?: 'editor' | 'preview';
    realOrderData?: RealOrderData | null;
  }
): { elements: Element[]; canvas: CanvasState } {
  let data: any = null;

  // Parser si string
  if (typeof jsonData === 'string') {
    try {
      data = JSON.parse(jsonData);
    } catch (error) {
      console.error('[CanvasPersistence] Erreur parsing JSON:', error);
      return { elements: [], canvas: { width: 210, height: 297 } };
    }
  } else if (typeof jsonData === 'object' && jsonData !== null) {
    data = jsonData;
  } else {
    console.warn('[CanvasPersistence] Format invalide');
    return { elements: [], canvas: { width: 210, height: 297 } };
  }

  // Normaliser la structure (support de différentes clés ancien/nouveau)
  let elements: unknown[] = [];
  let canvas: Partial<CanvasState> = {};

  if (data && typeof data === 'object') {
    // Format moderne
    if (Array.isArray(data.elements)) {
      elements = data.elements;
    } else if (Array.isArray(data.elementsData)) {
      // Format alternative
      elements = data.elementsData;
    } else if (Array.isArray(data)) {
      // Super légacy: tout dans un array
      elements = data;
    }

    // Canvas data (plusieurs formats possibles)
    if (data.canvas && typeof data.canvas === 'object') {
      canvas = data.canvas;
    } else if (data.canvasData && typeof data.canvasData === 'object') {
      canvas = data.canvasData;
    }

    // Support largeur/hauteur au top level
    if (data.canvasWidth) canvas.width = data.canvasWidth;
    if (data.canvasHeight) canvas.height = data.canvasHeight;
  }

  // Créer ValueResolver pour appliquer les données réelles si mode preview
  const isEditorMode = !options || options.mode !== 'preview';
  const resolver = new ValueResolver(!isEditorMode, options?.realOrderData || null);

  // Normaliser les éléments
  const normalizedElements: Element[] = [];
  for (let idx = 0; idx < elements.length; idx++) {
    const el = elements[idx];
    if (!el || typeof el !== 'object') continue;

    const element = el as Record<string, unknown>;
    const normalizedElement: Element = {
      // Toutes les propriétés de l'élément d'abord
      ...element,
      // Puis valider/corriger les propriétés clés APRÈS le spread
      id: (element.id as string) || `element-${idx}`,
      type: ((element.type as string) || 'unknown').replace(/-/g, '_'),
      x: Number(element.x) || 0,
      y: Number(element.y) || 0,
      width: Number(element.width) || 100,
      height: Number(element.height) || 100,
    } as Element;

    // ✅ NOUVEAU: Appliquer les valeurs via ValueResolver si c'est un RealDataElement
    // En mode édition: récupère les données du canvas (getProductTableFromElement)
    // En mode preview: récupère les données réelles de WooCommerce (buildProductTableData)
    if (normalizedElement.isRealDataElement) {
      const config: ElementValueConfig = {
        elementType: normalizedElement.type,
        isRealDataElement: true,
        testValue: normalizedElement.defaultTestValue,
        realDataKey: normalizedElement.realDataKey,
        element: normalizedElement,  // ✅ Passer l'élément pour que getValue() puisse récupérer les données du canvas
      };

      const resolvedValue = resolver.getValue(config);
      
      // Injecter la valeur résolue dans l'élément selon son type
      if (normalizedElement.type === 'product_table') {
        // Pour product_table: resolvedValue est un ProductTableData={ products[], fees[], totals{} }
        const tableData = resolvedValue as any; // ProductTableData
        if (tableData && typeof tableData === 'object') {
          // Injecter les produits
          if (Array.isArray(tableData.products)) {
            normalizedElement.products = tableData.products;
          }
          // ✅ REFACTOR: Injecter les frais au même niveau que produits (pas dans totals)
          if (Array.isArray(tableData.fees)) {
            normalizedElement.fees = tableData.fees;
          }
          // Injecter les totaux
          if (tableData.totals) {
            normalizedElement.totals = tableData.totals;
            // Aussi mettre à jour les propriétés individuelles pour compatibilité
            normalizedElement.shippingCost = tableData.totals.shippingCost;
            normalizedElement.taxRate = tableData.totals.taxRate;
            normalizedElement.globalDiscount = tableData.totals.discount;
          }
        }
      } else if (normalizedElement.type === 'customer_info') {
        // Pour customer_info, mettre à jour le contenu/metadata
        normalizedElement.metadata = {
          ...(normalizedElement.metadata || {}),
          customerData: resolvedValue,
        };
      } else if (normalizedElement.type === 'company_info' || normalizedElement.type === 'order_number') {
        // Pour company_info et order_number, mettre à jour content/text
        normalizedElement.content = String(resolvedValue || normalizedElement.defaultTestValue || '');
      }
    }

    normalizedElements.push(normalizedElement);
  }

  // Canvas state normalisé
  const normalizedCanvas: CanvasState = {
    width: (typeof canvas.width === 'number' ? canvas.width : null) || 210,
    height: (typeof canvas.height === 'number' ? canvas.height : null) || 297,
  };

  return {
    elements: normalizedElements,
    canvas: normalizedCanvas,
  };
}



/**
 * VALIDER: Vérifie que les données sont complètes et valides
 * Retourne { valid: boolean, errors: string[] }
 */
export function validateCanvasData(data: CanvasData): {
  valid: boolean;
  errors: string[];
} {
  const errors: string[] = [];

  // Elements
  if (!Array.isArray(data.elements)) {
    errors.push('Elements doit être un array');
  } else {
    data.elements.forEach((el, idx) => {
      if (!el.id) errors.push(`Element ${idx}: manque id`);
      if (!el.type) errors.push(`Element ${idx}: manque type`);
      if (typeof el.x !== 'number') errors.push(`Element ${idx}: x invalide`);
      if (typeof el.y !== 'number') errors.push(`Element ${idx}: y invalide`);
      
      // ✅ NOUVEAU: Validation des éléments RealData
      if (el.isRealDataElement && !el.realDataKey) {
        console.warn(`Element ${idx} (${el.type}): RealDataElement sans realDataKey`);
      }
    });
  }

  // Canvas
  if (!data.canvas) {
    errors.push('Canvas manquant');
  } else {
    if (!data.canvas.width) errors.push('Canvas: width manquant');
    if (!data.canvas.height) errors.push('Canvas: height manquant');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}

/**
 * DEBUG: Affiche un rapport complet des données
 */
export function debugCanvasData(
  data: CanvasData,
  label: string = 'Canvas Data'
): void {
  console.group(`🔍 ${label}`);
  console.log('✅ Elements:', data.elements.length);
  
  // Compter les éléments RealData
  const realDataElements = data.elements.filter(el => el.isRealDataElement);
  if (realDataElements.length > 0) {
    console.log('  📊 RealData elements:', realDataElements.length);
    realDataElements.forEach((el, idx) => {
      console.log(
        `    ${idx}. ${el.type} (key: ${el.realDataKey})`
      );
    });
  }
  
  data.elements.slice(0, 3).forEach((el, idx) => {
    const realDataTag = el.isRealDataElement ? ' [RealData]' : '';
    console.log(
      `  ${idx}. ${el.type} (${el.width}x${el.height} @ ${el.x},${el.y})${realDataTag}`
    );
  });
  console.log('✅ Canvas:', `${data.canvas.width}x${data.canvas.height}`);
  console.log('✅ Version:', data.version);
  console.groupEnd();
}
