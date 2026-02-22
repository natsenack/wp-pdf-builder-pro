/**
 * Normalisation robuste des éléments pour assurer la préservation complète des propriétés
 * C'est LE système central qui garantit que contentAlign, labelPosition, etc. ne sont jamais perdus
 */

import { debugWarn, debugError } from "./debug";
import type { Element } from "../types/elements";

/**
 * FONCTION CRITIQUE: Normalise les éléments sans perdre AUCUNE propriété personnalisée
 * Utilisée au chargement APRÈS le parsing JSON
 *
 * Propriétés à préserver ABSOLUMENT:
 * - contentAlign, labelPosition (order_number)
 * - Toute propriété custom ajoutée via l'éditeur
 */
export function normalizeElementsAfterLoad(elements: unknown[]): Element[] {
  if (!Array.isArray(elements)) {
    debugWarn("❌ [NORMALIZE] Elements n'est pas un array:", typeof elements);
    return [];
  }

  return elements.map((el, idx) => {
    if (!el || typeof el !== "object") {
      debugWarn(`❌ [NORMALIZE] Element ${idx} invalide:`, el);
      return {} as Element;
    }

    const element = el as Record<string, unknown>;

    // Créer une copie COMPLÈTE (spread shallow)
    // Convertir les tirets en underscores pour les types d'éléments (migration des anciennes données)
    const elementType = ((element.type as string) || "unknown").replace(
      /-/g,
      "_",
    );

    const normalized: Element = {
      ...element,
      id: (element.id as string) || `element-${idx}`,
      type: elementType,
      x: Number(element.x) || 0,
      y: Number(element.y) || 0,
      width: Number(element.width) || 100,
      height: Number(element.height) || 100,
    } as Element;

    // ============================================================
    // AJOUTER LES VALEURS PAR DÉFAUT POUR LES PROPRIÉTÉS OBLIGATOIRES
    // ============================================================
    // Cela garantit que tous les éléments chargés auront les propriétés requises

    // Propriétés communes requises (position et dimensions)
    if (typeof normalized.x !== "number" || normalized.x === undefined) {
      (normalized as any).x = element.x ? Number(element.x) : 0;
    }
    if (typeof normalized.y !== "number" || normalized.y === undefined) {
      (normalized as any).y = element.y ? Number(element.y) : 0;
    }
    if (
      typeof normalized.width !== "number" ||
      normalized.width === undefined
    ) {
      (normalized as any).width = element.width ? Number(element.width) : 100;
    }
    if (
      typeof normalized.height !== "number" ||
      normalized.height === undefined
    ) {
      (normalized as any).height = element.height
        ? Number(element.height)
        : 100;
    }

    // Propriétés obligatoires spécifiques par type d'élément
    switch (elementType) {
      case "text":
      case "dynamic_text":
      case "conditional_text":
        // Requiert: content
        if (!normalized.content) {
          (normalized as any).content = "";
        }
        break;

      case "image":
      case "logo":
      case "image_upload":
        // Requiert: src
        if (!normalized.src) {
          (normalized as any).src = "";
        }
        break;

      case "shape":
      case "shape_rectangle":
      case "shape_circle":
      case "shape_line":
      case "shape_arrow":
      case "shape_triangle":
      case "shape_star":
        // Requiert: type
        if (!normalized.type || normalized.type === "shape") {
          (normalized as any).type = "rectangle";
        }
        break;

      case "line":
        // Requiert: start_x, start_y, end_x, end_y
        if (typeof normalized.start_x !== "number") {
          (normalized as any).start_x = (element as any).start_x
            ? Number((element as any).start_x)
            : 0;
        }
        if (typeof normalized.start_y !== "number") {
          (normalized as any).start_y = (element as any).start_y
            ? Number((element as any).start_y)
            : 0;
        }
        if (typeof normalized.end_x !== "number") {
          (normalized as any).end_x = (element as any).end_x
            ? Number((element as any).end_x)
            : 100;
        }
        if (typeof normalized.end_y !== "number") {
          (normalized as any).end_y = (element as any).end_y
            ? Number((element as any).end_y)
            : 100;
        }
        break;

      case "rectangle":
        // Requiert: x, y, width, height (déjà définis plus haut)
        break;

      case "circle":
        // Requiert: cx, cy, r
        if (typeof normalized.cx !== "number") {
          (normalized as any).cx = (element as any).cx
            ? Number((element as any).cx)
            : 50;
        }
        if (typeof normalized.cy !== "number") {
          (normalized as any).cy = (element as any).cy
            ? Number((element as any).cy)
            : 50;
        }
        if (typeof normalized.r !== "number") {
          (normalized as any).r = (element as any).r
            ? Number((element as any).r)
            : 40;
        }
        break;

      case "barcode":
      case "qrcode":
      case "qrcode_dynamic":
        // Requiert: type (ou data pour code)
        if (
          !normalized.type ||
          normalized.type === "barcode" ||
          normalized.type === "qrcode"
        ) {
          (normalized as any).type =
            elementType === "barcode" ? "CODE128" : "QRCODE";
        }
        if (!normalized.data && !normalized.content) {
          (normalized as any).data = "123456789";
        }
        break;

      case "product_table":
        // Propriétés pour tableau produits
        if (!normalized.showHeaders) {
          (normalized as any).showHeaders = true;
        }
        if (!normalized.showBorders) {
          (normalized as any).showBorders = true;
        }
        if (!normalized.showAlternatingRows) {
          (normalized as any).showAlternatingRows = true;
        }
        if (!normalized.showShipping) {
          (normalized as any).showShipping = false;
        }
        if (!normalized.showTax) {
          (normalized as any).showTax = false;
        }
        if (!normalized.showGlobalDiscount) {
          (normalized as any).showGlobalDiscount = false;
        }
        if (!normalized.dataSource) {
          (normalized as any).dataSource = "order_items";
        }
        if (!normalized.columns) {
          (normalized as any).columns = {
            image: true,
            name: true,
            quantity: true,
            price: true,
            total: true,
          };
        }
        // Styles globaux
        if (!normalized.globalFontSize) {
          (normalized as any).globalFontSize = 11;
        }
        if (!normalized.globalFontFamily) {
          (normalized as any).globalFontFamily = "Arial";
        }
        if (!normalized.globalFontWeight) {
          (normalized as any).globalFontWeight = "normal";
        }
        if (!normalized.globalFontStyle) {
          (normalized as any).globalFontStyle = "normal";
        }
        // Styles lignes
        if (!normalized.rowFontSize) {
          (normalized as any).rowFontSize = 11;
        }
        if (!normalized.rowFontFamily) {
          (normalized as any).rowFontFamily = "Arial";
        }
        if (!normalized.rowFontWeight) {
          (normalized as any).rowFontWeight = "normal";
        }
        if (!normalized.rowFontStyle) {
          (normalized as any).rowFontStyle = "normal";
        }
        if (!normalized.rowTextColor) {
          (normalized as any).rowTextColor = "#374151";
        }
        // Styles totaux
        if (!normalized.totalFontSize) {
          (normalized as any).totalFontSize = 12;
        }
        if (!normalized.totalFontFamily) {
          (normalized as any).totalFontFamily = "Arial";
        }
        if (!normalized.totalFontWeight) {
          (normalized as any).totalFontWeight = "bold";
        }
        if (!normalized.totalFontStyle) {
          (normalized as any).totalFontStyle = "normal";
        }
        if (!normalized.totalTextColor) {
          (normalized as any).totalTextColor = "#111827";
        }
        if (!normalized.verticalAlign) {
          (normalized as any).verticalAlign = "top";
        }
        break;

      case "customer_info":
        // Propriétés pour infos client
        if (!normalized.showHeaders) {
          (normalized as any).showHeaders = true;
        }
        if (!normalized.showBorders) {
          (normalized as any).showBorders = false;
        }
        if (!normalized.showBackground) {
          (normalized as any).showBackground = true;
        }
        if (!normalized.showName) {
          (normalized as any).showName = true;
        }
        if (!normalized.layout) {
          (normalized as any).layout = "vertical";
        }
        if (!normalized.showLabels) {
          (normalized as any).showLabels = true;
        }
        // Styles entête
        if (!normalized.headerFontSize) {
          (normalized as any).headerFontSize = 12;
        }
        if (!normalized.headerFontFamily) {
          (normalized as any).headerFontFamily = "Arial";
        }
        if (!normalized.headerFontWeight) {
          (normalized as any).headerFontWeight = "bold";
        }
        // Styles corps
        if (!normalized.bodyFontSize) {
          (normalized as any).bodyFontSize = 11;
        }
        if (!normalized.bodyFontFamily) {
          (normalized as any).bodyFontFamily = "Arial";
        }
        if (!normalized.bodyFontWeight) {
          (normalized as any).bodyFontWeight = "normal";
        }
        break;

      case "company_info":
        // Propriétés pour infos entreprise
        if (!normalized.showHeaders) {
          (normalized as any).showHeaders = false;
        }
        if (!normalized.showBorders) {
          (normalized as any).showBorders = false;
        }
        if (!normalized.showBackground) {
          (normalized as any).showBackground = true;
        }
        break;

      case "company_logo":
        // Propriétés pour logo entreprise
        if (!normalized.objectFit) {
          (normalized as any).objectFit = "contain";
        }
        break;

      case "mentions":
      case "note":
        // Propriétés pour les mentions légales
        if (!normalized.showEmail) {
          (normalized as any).showEmail = true;
        }
        if (!normalized.showPhone) {
          (normalized as any).showPhone = true;
        }
        if (!normalized.showSiret) {
          (normalized as any).showSiret = true;
        }
        if (!normalized.showVat) {
          (normalized as any).showVat = true;
        }
        if (!normalized.separator) {
          (normalized as any).separator = " • ";
        }
        if (!normalized.showSeparator) {
          (normalized as any).showSeparator = true;
        }
        if (!normalized.separatorStyle) {
          (normalized as any).separatorStyle = "solid";
        }
        if (!normalized.mentionType) {
          (normalized as any).mentionType = "dynamic";
        }
        if (!normalized.selectedMentions) {
          (normalized as any).selectedMentions = [];
        }
        if (!normalized.medleySeparator) {
          (normalized as any).medleySeparator = "\n\n";
        }
        if (!normalized.showBackground) {
          (normalized as any).showBackground = false;
        }
        if (!normalized.text) {
          (normalized as any).text = "";
        }
        break;

      case "document_type":
        // Requiert: documentType
        if (!normalized.documentType) {
          (normalized as any).documentType = "invoice";
        }
        break;

      case "woocommerce_order_date":
      case "woocommerce_invoice_number":
        // Ces types doivent avoir au moins un contenu par défaut
        if (!normalized.content && !normalized.text) {
          (normalized as any).content =
            elementType === "woocommerce_order_date"
              ? new Date().toLocaleDateString()
              : "INV-001";
        }
        break;

      // Layouts et structures
      case "layout_header":
      case "layout_footer":
      case "layout_sidebar":
      case "layout_section":
      case "layout_container":
        if (!normalized.content) {
          (normalized as any).content = "";
        }
        break;

      // Éléments dynamiques
      case "table_dynamic":
      case "gradient_box":
      case "shadow_box":
      case "rounded_box":
      case "border_box":
      case "background_pattern":
      case "watermark":
        if (!normalized.content) {
          (normalized as any).content = "";
        }
        break;
    }

    return normalized;
  });
}

/**
 * FONCTION CRITIQUE: Prépare les éléments pour la sauvegarde
 * Assure que TOUT est sérialisable et complet
 */
export function normalizeElementsBeforeSave(elements: Element[]): Element[] {
  if (!Array.isArray(elements)) {
    debugWarn("❌ [SAVE NORMALIZE] Elements n'est pas un array");
    return [];
  }

  return elements.map((el, idx) => {
    if (!el || typeof el !== "object") {
      debugWarn(`❌ [SAVE NORMALIZE] Element ${idx} invalide`);
      return {} as Element;
    }

    // Créer une copie COMPLÈTE
    const normalized: Element = {
      ...el,
    } as Element;

    // Valider les champs critiques
    if (!normalized.id) normalized.id = `element-${idx}`;
    if (!normalized.type) normalized.type = "unknown";
    if (typeof normalized.x !== "number") normalized.x = 0;
    if (typeof normalized.y !== "number") normalized.y = 0;
    if (typeof normalized.width !== "number") normalized.width = 100;
    if (typeof normalized.height !== "number") normalized.height = 100;

    // Filtrer les propriétés non sérialisables (Date, Function, etc)
    const serializable: Record<string, unknown> = {};

    // ========== PROPRIÉTÉS CRITIQUES À PRÉSERVER ==========
    // Les styles dédans ce set ne doivent JAMAIS être perdus lors de la sauvegarde
    const styleProperties = new Set([
      // ===== STYLES TEXTE =====
      "fontFamily",
      "fontSize",
      "fontWeight",
      "fontStyle",
      "fontColor",
      "color",
      "textAlign",
      "textColor",
      "textDecoration",
      "textTransform",
      "wordSpacing",
      "lineHeight",

      // ===== STYLES FOND & BORDURES =====
      "backgroundColor",
      "bgColor",
      "showBackground",
      "border",
      "borderTop",
      "borderBottom",
      "borderLeft",
      "borderRight",
      "borderColor",
      "borderWidth",
      "borderStyle",
      "borderRadius",

      // ===== ESPACES & DIMENSIONS =====
      "padding",
      "margin",
      "paddingTop",
      "paddingRight",
      "paddingBottom",
      "paddingLeft",
      "paddingHorizontal",
      "marginTop",
      "marginRight",
      "marginBottom",
      "marginLeft",
      "width",
      "height",
      "x",
      "y",
      "display",

      // ===== PROPRIÉTÉS VISUELLES =====
      "opacity",
      "zIndex",
      "rotation",
      "scale",
      "visible",
      "locked",
      "shadowColor",
      "shadowOffsetX",
      "shadowOffsetY",
      "shadowBlur",

      // ===== SÉPARATEURS (Mentions & autres) =====
      "showSeparator",
      "separatorStyle",
      "separator",

      // ===== PROPRIÉTÉS MENTIONS =====
      "showEmail",
      "showPhone",
      "showSiret",
      "showVat",
      "mentionType",
      "selectedMentions",
      "medleySeparator",
      "theme",

      // ===== PROPRIÉTÉS COMPANY_INFO =====
      "showCompanyName",
      "showAddress",
      "showRcs",
      "showCapital",
      "layout",
      "fontSize",
      "fontFamily",
      "fontWeight",
      "fontStyle",
      "headerFontSize",
      "headerFontFamily",
      "headerFontWeight",
      "headerFontStyle",
      "bodyFontSize",
      "bodyFontFamily",
      "bodyFontWeight",
      "bodyFontStyle",
      "textColor",
      "backgroundColor",
      "borderColor",
      "borderWidth",
      "showBackground",
      "showBorders",
      "verticalAlign",
      "lineHeight",
      "paddingTop",
      "paddingHorizontal",
      "paddingBottom",

      // ===== PROPRIÉTÉS PRODUCT_TABLE =====
      "showHeaders",
      "showBorders",
      "showAlternatingRows",
      "showImage",
      "showSku",
      "showDescription",
      "showQuantity",
      "showPrice",
      "showTotal",
      "showShipping",
      "showTax",
      "showGlobalDiscount",
      "globalFontSize",
      "globalFontFamily",
      "globalFontWeight",
      "globalFontStyle",
      "headerFontSize",
      "headerFontFamily",
      "headerFontWeight",
      "headerFontStyle",
      "headerTextColor",
      "headerBackgroundColor",
      "rowFontSize",
      "rowFontFamily",
      "rowFontWeight",
      "rowFontStyle",
      "rowTextColor",
      "totalFontSize",
      "totalFontFamily",
      "totalFontWeight",
      "totalFontStyle",
      "totalTextColor",
      "alternateRowColor",
      "verticalAlign",
      "tableStyle",

      // ===== PROPRIÉTÉS CUSTOMER_INFO =====
      "showName",
      "showFullName",
      "showPaymentMethod",
      "showTransactionId",
      "fontSize",
      "fontFamily",
      "fontWeight",
      "fontStyle",
      "headerFontSize",
      "headerFontFamily",
      "headerFontWeight",
      "headerFontStyle",
      "bodyFontSize",
      "bodyFontFamily",
      "bodyFontWeight",
      "bodyFontStyle",
      "textColor",
      "backgroundColor",
      "borderColor",
      "borderWidth",
      "showBackground",
      "showBorders",
      "verticalAlign",
      "lineHeight",

      // ===== PROPRIÉTÉS COMPANY_LOGO =====
      "logoUrl",
      "fit",
      "objectFit",
      "alignment",
      "borderRadius",
      "opacity",
      "rotation",
      "shadowColor",
      "shadowOffsetX",
      "shadowOffsetY",
      "shadowBlur",

      // ===== PROPRIÉTÉS WOOCOMMERCE_ORDER_DATE =====
      "dateFormat",
      "showTime",
      "showLabel",
      "labelText",
      "labelPosition",
      "labelFontFamily",
      "labelFontSize",
      "labelFontWeight",
      "labelFontStyle",
      "labelColor",
      "labelSpacing",
      "prefix",
      "suffix",

      // ===== PROPRIÉTÉS WOOCOMMERCE_INVOICE_NUMBER =====
      // (mêmes que ORDER_DATE + prefix, suffix)

      // ===== PROPRIÉTÉS DOCUMENT_TYPE =====
      "documentType",
      "title",

      // ===== PROPRIÉTÉS DYNAMIC_TEXT =====
      "autoWrap",
      "theme",
      "textTemplate",

      // ===== PROPRIÉTÉS MENTIONS =====
      "mentionType",
      "selectedMentions",
      "medleySeparator",
      "separatorStyle",
      "separatorColor",
      "separatorWidth",
      "showEmail",
      "showPhone",
      "showSiret",
      "showVat",

      // ===== PROPRIÉTÉS IMAGES & CONTENU =====
      "objectFit",
      "fit",
      "alignment",
      "text",
      "content",
      "src",
      "alt",
      "label",
    ]);

    Object.keys(normalized).forEach((key) => {
      const value = normalized[key];
      const type = typeof value;

      // DEBUG: Log des propriétés spéciales
      if (
        key.includes("🎯") ||
        key.includes("interactions") ||
        key.includes("comportement") ||
        key.includes("behavior")
      ) {
        //
      }

      // Garder: string, number, boolean, null, undefined
      // Garder: objects simples et arrays
      // REJETER: functions, symbols, dates (sauf si sérialisées)
      if (
        value === null ||
        value === undefined ||
        type === "string" ||
        type === "number" ||
        type === "boolean"
      ) {
        serializable[key] = value;
      } else if (type === "object") {
        try {
          // Vérifier si c'est sérialisable
          JSON.stringify(value);
          serializable[key] = value;
        } catch {
          debugWarn(
            `⚠️  [SAVE NORMALIZE] Propriété non sérialisable ${key} skippée`,
            value,
          );
        }
      } else {
        // Propriétés rejetées (functions, etc.)
        debugWarn(
          `⚠️  [SAVE NORMALIZE] Propriété rejetée: ${key} (type: ${type})`,
        );
      }
    });

    // ✅ VÉRIFICATION: Log des propriétés de style critiques existantes
    if (
      normalized.type === "text" ||
      normalized.type === "mentions" ||
      normalized.type === "company_info"
    ) {
      const existingStyles = Array.from(styleProperties).filter(
        (prop) => serializable[prop] !== undefined,
      );
      //
    }

    return serializable as Element;
  }) as Element[];
}

/**
 * Valide que les propriétés critiques sont présentes
 */
export function validateElementIntegrity(
  elements: Element[],
  elementType: string,
): boolean {
  const elementsOfType = elements.filter((el) => el.type === elementType);

  if (elementsOfType.length === 0) {
    return true; // Pas d'éléments de ce type
  }

  let allValid = true;
  elementsOfType.forEach((el, idx) => {
    const required: (keyof Element)[] = [
      "id",
      "type",
      "x",
      "y",
      "width",
      "height",
    ];
    const missing = required.filter((key) => !(key in el));

    if (missing.length > 0) {
      debugError(`❌ [VALIDATE] Element ${idx} missing: ${missing.join(", ")}`);
      allValid = false;
    }
  });

  return allValid;
}

/**
 * Debug helper: affiche un rapport complet
 */
export function debugElementState(elements: Element[], label: string): void {
  // Debug function - logs removed for production
}
