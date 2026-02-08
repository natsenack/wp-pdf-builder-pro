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
    elements = [];
  }

  // Nettoyer et valider chaque élément
  const cleanElements = elements.map((el, idx) => {
    if (!el || typeof el !== 'object') {
      return null;
    }

    // ✅ EXPLICIT serialization: Only copy properties we want, ensure x/y are numbers
    // Don't use spread ...el because it can copy corrupted properties
    const elX = typeof el.x === 'number' ? el.x : (Number(el.x) || 0);
    const elY = typeof el.y === 'number' ? el.y : (Number(el.y) || 0);
    
    // 🔍 LOG: Positions avant sérialisation
    console.log(`[SERIALIZE] El ${idx} (${String(el.id)}) BEFORE: x=${el.x} (type: ${typeof el.x}), y=${el.y} (type: ${typeof el.y})`);
    console.log(`[SERIALIZE] El ${idx} AFTER CONVERSION: x=${elX}, y=${elY}`);
    
    const serialized: any = {
      // Propriétés requises
      id: String(el.id || `element-${idx}`),
      type: String(el.type || 'unknown'),
      
      // Positions - CRITICAL: Must be numbers, never undefined or NaN
      x: elX,
      y: elY,
      width: typeof el.width === 'number' ? el.width : (Number(el.width) || 100),
      height: typeof el.height === 'number' ? el.height : (Number(el.height) || 100),
      
      // Propriétés visibilité/interaction
      visible: el.visible !== false,
      locked: el.locked === true,
      rotation: typeof el.rotation === 'number' ? el.rotation : 0,
      opacity: typeof el.opacity === 'number' ? el.opacity : 1,
    };

    // Copier TOUTES les autres propriétés de el (styles, contenu, etc)
    // mais sans surcharger les propriétés critiques ci-dessus
    for (const key in el) {
      if (el.hasOwnProperty(key)) {
        // Skip if already set (critical properties)
        if (['id', 'type', 'x', 'y', 'width', 'height', 'visible', 'locked', 'rotation', 'opacity'].includes(key)) {
          continue;
        }
        // Skip functions and complex objects that can't be JSON serialized
        const val = (el as any)[key];
        if (typeof val === 'function') {
          continue;
        }
        // Copy the property
        serialized[key] = val;
      }
    }

    // 🔍 LOG: Positions après sérialisation
    console.log(`[SERIALIZE] El ${idx} FINAL JSON: x=${serialized.x}, y=${serialized.y}`);

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
    const json = JSON.stringify(data);
    return json;
  } catch (error) {
    return JSON.stringify({ elements: [], canvasWidth: canvasState.width, canvasHeight: canvasState.height, version: '1.0' });
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
    
    // 🔍 LOG DESERIALIZE: Check positions avant normalisation
    console.log(`[DESERIALIZE] El ${idx} RAW from JSON: x=${element.x} (type: ${typeof element.x}), y=${element.y} (type: ${typeof element.y})`);
    
    const normalizedX = Number(element.x) || 0;
    const normalizedY = Number(element.y) || 0;
    
    console.log(`[DESERIALIZE] El ${idx} AFTER Number(): x=${normalizedX}, y=${normalizedY}`);
    
    const normalizedElement: Element = {
      // Toutes les propriétés de l'élément d'abord
      ...element,
      // Puis valider/corriger les propriétés clés APRÈS le spread
      id: (element.id as string) || `element-${idx}`,
      type: ((element.type as string) || 'unknown').replace(/-/g, '_'),
      x: normalizedX,
      y: normalizedY,
      width: Number(element.width) || 100,
      height: Number(element.height) || 100,
    } as Element;

    console.log(`[DESERIALIZE] El ${idx} FINAL after spread: x=${normalizedElement.x}, y=${normalizedElement.y}`);

    // ✅ NOUVEAU: Appliquer les valeurs via ValueResolver si c'est un RealDataElement
    // En mode édition: récupère les données du canvas (getProductTableFromElement)
    // En mode preview: récupère les données réelles de WooCommerce (buildProductTableData)
    if (normalizedElement.isRealDataElement) {
      const config: ElementValueConfig = {
        elementType: normalizedElement.type,
        isRealDataElement: true,
        testValue: normalizedElement.defaultTestValue,
        realDataKey: normalizedElement.realDataKey,
        element: normalizedElement as any,  // ✅ Cast to any for flexibility with different element types
      };

      const resolvedValue = resolver.getValue(config);
      
      // Injecter la valeur résolue dans l'élément selon son type
      if (normalizedElement.type === 'product_table') {
        // Pour product_table: resolvedValue est un ProductTableData={ products[], fees[], totals{} }
        const tableData = resolvedValue as any; // ProductTableData
        if (tableData && typeof tableData === 'object') {
          // Injecter les produits
          if (Array.isArray(tableData.products)) {
            (normalizedElement as any).products = tableData.products;
          }
          // ✅ REFACTOR: Injecter les frais au même niveau que produits (pas dans totals)
          if (Array.isArray(tableData.fees)) {
            (normalizedElement as any).fees = tableData.fees;
          }
          // Injecter les totaux
          if (tableData.totals) {
            (normalizedElement as any).totals = tableData.totals;
            // Aussi mettre à jour les propriétés individuelles pour compatibilité
            (normalizedElement as any).shippingCost = tableData.totals.shippingCost;
            (normalizedElement as any).taxRate = tableData.totals.taxRate;
            (normalizedElement as any).globalDiscount = tableData.totals.discount;
          }
        }
      } else if (normalizedElement.type === 'customer_info') {
        // Pour customer_info, mettre à jour le contenu/metadata
        (normalizedElement as any).metadata = {
          ...(normalizedElement as any).metadata || {},
          customerData: resolvedValue,
        };
      } else if (normalizedElement.type === 'company_info' || normalizedElement.type === 'order_number') {
        // Pour company_info et order_number, mettre à jour content/text
        (normalizedElement as any).content = String(resolvedValue || normalizedElement.defaultTestValue || '');
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
        errors.push(`Element ${idx} (${el.type}): RealDataElement sans realDataKey`);
      }
    });
  }

  // Canvas - Utiliser les bonnes clés (canvasWidth, canvasHeight, pas canvas.width/height)
  const anyData = data as any;
  if (typeof anyData.canvasWidth !== 'number') {
    errors.push('Canvas: canvasWidth invalide');
  }
  if (typeof anyData.canvasHeight !== 'number') {
    errors.push('Canvas: canvasHeight invalide');
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
  // Nettoyage: fonction de debug silencieuse - les logs vrais vont via console.error seulement
}
