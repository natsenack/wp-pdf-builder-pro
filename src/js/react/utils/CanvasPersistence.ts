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

import type { Element } from "../types/elements";
import {
  ValueResolver,
  type RealOrderData,
  type ElementValueConfig,
} from "../persistence/ValueResolver";

export interface CanvasData {
  elements: Element[];
  canvasWidth: number; // ✅ Propriété attendue par le validateur PHP
  canvasHeight: number; // ✅ Propriété attendue par le validateur PHP
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
  canvas: Partial<CanvasState> = {},
): string {
  if (!Array.isArray(elements)) {
    elements = [];
  }

  const cleanElements = elements
    .map((el, idx) => {
      if (!el || typeof el !== "object") {
        return null;
      }

      // Copy TOUTES les propriétés d'abord
      const serialized: any = { ...el };

      // Log pour vérifier que layout, textAlign, etc. sont bien sérialisés
      if (el.type === "customer_info" || el.type === "company_info") {
        console.log(`[CanvasPersistence] Serializing ${el.type}:`, {
          id: el.id,
          layout: (el as any).layout,
          textAlign: (el as any).textAlign,
          verticalAlign: (el as any).verticalAlign,
          paddingHorizontal: (el as any).paddingHorizontal,
          paddingVertical: (el as any).paddingVertical,
        });
      }

      // Valider et fixer les propriétés critiques
      serialized.id = String(el.id || `element-${idx}`);
      serialized.type = String(el.type || "unknown");
      serialized.x = typeof el.x === "number" ? el.x : Number(el.x) || 0;
      serialized.y = typeof el.y === "number" ? el.y : Number(el.y) || 0;
      serialized.width =
        typeof el.width === "number" ? el.width : Number(el.width) || 100;
      serialized.height =
        typeof el.height === "number" ? el.height : Number(el.height) || 100;
      serialized.visible = el.visible !== false;
      serialized.locked = el.locked === true;
      serialized.rotation = typeof el.rotation === "number" ? el.rotation : 0;
      serialized.opacity = typeof el.opacity === "number" ? el.opacity : 1;

      // ✅ CRITICAL FIX: Convert Date objects to ISO strings
      if (serialized.createdAt instanceof Date) {
        serialized.createdAt = serialized.createdAt.toISOString();
      }
      if (serialized.updatedAt instanceof Date) {
        serialized.updatedAt = serialized.updatedAt.toISOString();
      }

      // Supprimer propriétés non-sérialisables
      Object.keys(serialized).forEach((key) => {
        const val = serialized[key];
        if (
          typeof val === "function" ||
          val instanceof Date ||
          val === undefined
        ) {
          delete serialized[key];
        }
      });

      return serialized;
    })
    .filter((el): el is Element => el !== null);

  const canvasState: CanvasState = {
    width: typeof canvas.width === "number" ? canvas.width : 210,
    height: typeof canvas.height === "number" ? canvas.height : 297,
  };

  const data: any = {
    elements: cleanElements,
    canvasWidth: canvasState.width,
    canvasHeight: canvasState.height,
    version: "1.0",
  };

  try {
    const jsonString = JSON.stringify(data);

    return jsonString;
  } catch (error) {
    return JSON.stringify({
      elements: [],
      canvasWidth: canvasState.width,
      canvasHeight: canvasState.height,
      version: "1.0",
    });
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
    mode?: "editor" | "preview";
    realOrderData?: RealOrderData | null;
  },
): { elements: Element[]; canvas: CanvasState } {
  let data: any = null;

  // Parse JSON string or use object directly
  if (typeof jsonData === "string") {
    try {
      data = JSON.parse(jsonData);
    } catch (error) {
      console.error("[CanvasPersistence] Failed to parse JSON data:", error);
      data = {};
    }
  } else if (typeof jsonData === "object" && jsonData !== null) {
    data = jsonData;
  }

  // Normaliser la structure (support de différentes clés ancien/nouveau)
  let elements: unknown[] = [];
  let canvas: Partial<CanvasState> = {};

  if (data && typeof data === "object") {
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
    if (data.canvas && typeof data.canvas === "object") {
      canvas = data.canvas;
    } else if (data.canvasData && typeof data.canvasData === "object") {
      canvas = data.canvasData;
    }

    // Support largeur/hauteur au top level
    if (data.canvasWidth) canvas.width = data.canvasWidth;
    if (data.canvasHeight) canvas.height = data.canvasHeight;
  }

  // Créer ValueResolver pour appliquer les données réelles si mode preview
  const isEditorMode = !options || options.mode !== "preview";
  const resolver = new ValueResolver(
    !isEditorMode,
    options?.realOrderData || null,
  );

  // Normaliser les éléments
  const normalizedElements: Element[] = [];
  for (let idx = 0; idx < elements.length; idx++) {
    const el = elements[idx];
    if (!el || typeof el !== "object") continue;

    const element = el as Record<string, unknown>;

    // Build with spread FIRST to keep all properties
    const normalizedElement: any = { ...element };

    // Then OVERRIDE critical properties with validated values
    normalizedElement.id = (element.id as string) || `element-${idx}`;
    normalizedElement.type = ((element.type as string) || "unknown").replace(
      /-/g,
      "_",
    );
    normalizedElement.x = Number(element.x) || 0;
    normalizedElement.y = Number(element.y) || 0;
    normalizedElement.width = Number(element.width) || 100;
    normalizedElement.height = Number(element.height) || 100;
    normalizedElement.visible = element.visible !== false;
    normalizedElement.locked = element.locked === true;
    normalizedElement.rotation = Number(element.rotation) || 0;
    normalizedElement.opacity = Number(element.opacity) || 1;

    // ✅ CRITICAL FIX: Convert ISO date strings back to Date objects
    if (typeof element.createdAt === "string") {
      normalizedElement.createdAt = new Date(element.createdAt);
    } else if (!(element.createdAt instanceof Date)) {
      normalizedElement.createdAt = new Date();
    }

    if (typeof element.updatedAt === "string") {
      normalizedElement.updatedAt = new Date(element.updatedAt);
    } else if (!(element.updatedAt instanceof Date)) {
      normalizedElement.updatedAt = new Date();
    }

    // ✅ ENRICHISSEMENT: Ajouter les données réelles de l'entreprise pour company_info si manquantes
    if (normalizedElement.type === "company_info") {
      const pdfBuilderData = (window as any).pdfBuilderData;
      if (pdfBuilderData && pdfBuilderData.company) {
        const company = pdfBuilderData.company;

        // Enrichir seulement si les propriétés manquent ou sont vides
        if (
          !normalizedElement.companyName ||
          normalizedElement.companyName === "Non indiqué"
        ) {
          normalizedElement.companyName = company.name || "";
        }
        if (
          !normalizedElement.companyAddress ||
          normalizedElement.companyAddress === "Non indiqué"
        ) {
          normalizedElement.companyAddress = company.address || "";
        }
        if (!normalizedElement.companyCity) {
          normalizedElement.companyCity = company.city || "";
        }
        if (
          !normalizedElement.companyPhone ||
          normalizedElement.companyPhone === "Non indiqué"
        ) {
          normalizedElement.companyPhone = company.phone || "";
        }
        if (
          !normalizedElement.companyEmail ||
          normalizedElement.companyEmail === "Non indiqué"
        ) {
          normalizedElement.companyEmail = company.email || "";
        }
        if (
          !normalizedElement.companySiret ||
          normalizedElement.companySiret === "Non indiqué"
        ) {
          normalizedElement.companySiret = company.siret || "";
        }
        if (
          !normalizedElement.companyTva ||
          normalizedElement.companyTva === "Non indiqué"
        ) {
          normalizedElement.companyTva = company.tva || "";
        }
        if (
          !normalizedElement.companyRcs ||
          normalizedElement.companyRcs === "Non indiqué"
        ) {
          normalizedElement.companyRcs = company.rcs || "";
        }
        if (
          !normalizedElement.companyCapital ||
          normalizedElement.companyCapital === "Non indiqué"
        ) {
          normalizedElement.companyCapital = company.capital || "";
        }
      }
    }

    // ✅ NOUVEAU: Appliquer les valeurs via ValueResolver si c'est un RealDataElement
    // En mode édition: récupère les données du canvas (getProductTableFromElement)
    // En mode preview: récupère les données réelles de WooCommerce (buildProductTableData)
    if (normalizedElement.isRealDataElement) {
      const config: ElementValueConfig = {
        elementType: normalizedElement.type,
        isRealDataElement: true,
        testValue: normalizedElement.defaultTestValue,
        realDataKey: normalizedElement.realDataKey,
        element: normalizedElement as any, // ✅ Cast to any for flexibility with different element types
      };

      const resolvedValue = resolver.getValue(config);

      // Injecter la valeur résolue dans l'élément selon son type
      if (normalizedElement.type === "product_table") {
        // Pour product_table: resolvedValue est un ProductTableData={ products[], fees[], totals{} }
        const tableData = resolvedValue as any; // ProductTableData
        if (tableData && typeof tableData === "object") {
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
            (normalizedElement as any).shippingCost =
              tableData.totals.shippingCost;
            (normalizedElement as any).taxRate = tableData.totals.taxRate;
            (normalizedElement as any).globalDiscount =
              tableData.totals.discount;
          }
        }
      } else if (normalizedElement.type === "customer_info") {
        // Pour customer_info, mettre à jour le contenu/metadata
        (normalizedElement as any).metadata = {
          ...((normalizedElement as any).metadata || {}),
          customerData: resolvedValue,
        };
      } else if (
        normalizedElement.type === "company_info" ||
        normalizedElement.type === "order_number"
      ) {
        // Pour company_info et order_number, mettre à jour content/text
        (normalizedElement as any).content = String(
          resolvedValue || normalizedElement.defaultTestValue || "",
        );
      }
    }

    normalizedElements.push(normalizedElement);
  }

  // Canvas state normalisé
  const normalizedCanvas: CanvasState = {
    width: (typeof canvas.width === "number" ? canvas.width : null) || 210,
    height: (typeof canvas.height === "number" ? canvas.height : null) || 297,
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
    errors.push("Elements doit être un array");
  } else {
    data.elements.forEach((el, idx) => {
      if (!el.id) errors.push(`Element ${idx}: manque id`);
      if (!el.type) errors.push(`Element ${idx}: manque type`);
      if (typeof el.x !== "number") errors.push(`Element ${idx}: x invalide`);
      if (typeof el.y !== "number") errors.push(`Element ${idx}: y invalide`);

      // ✅ NOUVEAU: Validation des éléments RealData
      if (el.isRealDataElement && !el.realDataKey) {
        errors.push(
          `Element ${idx} (${el.type}): RealDataElement sans realDataKey`,
        );
      }
    });
  }

  // Canvas - Utiliser les bonnes clés (canvasWidth, canvasHeight, pas canvas.width/height)
  const anyData = data as any;
  if (typeof anyData.canvasWidth !== "number") {
    errors.push("Canvas: canvasWidth invalide");
  }
  if (typeof anyData.canvasHeight !== "number") {
    errors.push("Canvas: canvasHeight invalide");
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
  label: string = "Canvas Data",
): void {
  // Nettoyage: fonction de debug silencieuse - les logs vrais vont via console.error seulement
}
