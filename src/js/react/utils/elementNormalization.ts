/**
 * Normalisation robuste des éléments pour assurer la préservation complète des propriétés
 * C'est LE système central qui garantit que contentAlign, labelPosition, etc. ne sont jamais perdus
 */

import { debugWarn, debugError } from './debug';
import type { Element } from '../types/elements';

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
    debugWarn('❌ [NORMALIZE] Elements n\'est pas un array:', typeof elements);
    return [];
  }

  return elements.map((el, idx) => {
    if (!el || typeof el !== 'object') {
      debugWarn(`❌ [NORMALIZE] Element ${idx} invalide:`, el);
      return {} as Element;
    }

    const element = el as Record<string, unknown>;

    // Créer une copie COMPLÈTE (spread shallow)
    // Convertir les tirets en underscores pour les types d'éléments (migration des anciennes données)
    const elementType = (element.type as string || 'unknown').replace(/-/g, '_');
    
    const normalized: Element = {
      ...element,
      id: element.id as string || `element-${idx}`,
      type: elementType,
      x: Number(element.x) || 0,
      y: Number(element.y) || 0,
      width: Number(element.width) || 100,
      height: Number(element.height) || 100
    } as Element;

    // ============================================================
    // AJOUTER LES VALEURS PAR DÉFAUT POUR LES PROPRIÉTÉS OBLIGATOIRES
    // ============================================================
    // Cela garantit que tous les éléments chargés auront les propriétés requises
    
    // Propriétés communes requises (position et dimensions)
    if (typeof normalized.x !== 'number' || normalized.x === undefined) {
      (normalized as any).x = element.x ? Number(element.x) : 0;
    }
    if (typeof normalized.y !== 'number' || normalized.y === undefined) {
      (normalized as any).y = element.y ? Number(element.y) : 0;
    }
    if (typeof normalized.width !== 'number' || normalized.width === undefined) {
      (normalized as any).width = element.width ? Number(element.width) : 100;
    }
    if (typeof normalized.height !== 'number' || normalized.height === undefined) {
      (normalized as any).height = element.height ? Number(element.height) : 100;
    }

    // Propriétés obligatoires spécifiques par type d'élément
    switch (elementType) {
      case 'text':
      case 'dynamic_text':
      case 'conditional_text':
        // Requiert: content
        if (!normalized.content) {
          (normalized as any).content = '';
        }
        break;

      case 'image':
      case 'logo':
      case 'image_upload':
        // Requiert: src
        if (!normalized.src) {
          (normalized as any).src = '';
        }
        break;

      case 'shape':
      case 'shape_rectangle':
      case 'shape_circle':
      case 'shape_line':
      case 'shape_arrow':
      case 'shape_triangle':
      case 'shape_star':
        // Requiert: type
        if (!normalized.type || normalized.type === 'shape') {
          (normalized as any).type = 'rectangle';
        }
        break;

      case 'line':
        // Requiert: start_x, start_y, end_x, end_y
        if (typeof normalized.start_x !== 'number') {
          (normalized as any).start_x = (element as any).start_x ? Number((element as any).start_x) : 0;
        }
        if (typeof normalized.start_y !== 'number') {
          (normalized as any).start_y = (element as any).start_y ? Number((element as any).start_y) : 0;
        }
        if (typeof normalized.end_x !== 'number') {
          (normalized as any).end_x = (element as any).end_x ? Number((element as any).end_x) : 100;
        }
        if (typeof normalized.end_y !== 'number') {
          (normalized as any).end_y = (element as any).end_y ? Number((element as any).end_y) : 100;
        }
        break;

      case 'rectangle':
        // Requiert: x, y, width, height (déjà définis plus haut)
        break;

      case 'circle':
        // Requiert: cx, cy, r
        if (typeof normalized.cx !== 'number') {
          (normalized as any).cx = (element as any).cx ? Number((element as any).cx) : 50;
        }
        if (typeof normalized.cy !== 'number') {
          (normalized as any).cy = (element as any).cy ? Number((element as any).cy) : 50;
        }
        if (typeof normalized.r !== 'number') {
          (normalized as any).r = (element as any).r ? Number((element as any).r) : 40;
        }
        break;

      case 'order_number':
        // Requiert: format
        if (!normalized.format) {
          (normalized as any).format = 'CMD-{order_number}';
        }
        break;

      case 'barcode':
      case 'qrcode':
      case 'qrcode_dynamic':
        // Requiert: type (ou data pour code)
        if (!normalized.type || normalized.type === 'barcode' || normalized.type === 'qrcode') {
          (normalized as any).type = elementType === 'barcode' ? 'CODE128' : 'QRCODE';
        }
        if (!normalized.data && !normalized.content) {
          (normalized as any).data = '123456789';
        }
        break;

      case 'product_table':
        // Propriétés pour tableau produits
        if (!normalized.showHeaders) {
          (normalized as any).showHeaders = true;
        }
        if (!normalized.showBorders) {
          (normalized as any).showBorders = true;
        }
        if (!normalized.dataSource) {
          (normalized as any).dataSource = 'order_items';
        }
        if (!normalized.columns) {
          (normalized as any).columns = {
            image: true,
            name: true,
            quantity: true,
            price: true,
            total: true
          };
        }
        break;

      case 'customer_info':
        // Propriétés pour infos client
        if (!normalized.showHeaders) {
          (normalized as any).showHeaders = true;
        }
        if (!normalized.showBorders) {
          (normalized as any).showBorders = false;
        }
        if (!normalized.layout) {
          (normalized as any).layout = 'vertical';
        }
        if (!normalized.showLabels) {
          (normalized as any).showLabels = true;
        }
        break;

      case 'company_info':
      case 'company_logo':
        // Propriétés pour infos entreprise
        if (!normalized.showHeaders) {
          (normalized as any).showHeaders = false;
        }
        if (!normalized.showBorders) {
          (normalized as any).showBorders = false;
        }
        break;

      case 'document_type':
        // Requiert: documentType
        if (!normalized.documentType) {
          (normalized as any).documentType = 'invoice';
        }
        break;

      case 'woocommerce_order_date':
      case 'woocommerce_invoice_number':
        // Ces types doivent avoir au moins un contenu par défaut
        if (!normalized.content && !normalized.text) {
          (normalized as any).content = elementType === 'woocommerce_order_date' ? 
            new Date().toLocaleDateString() : 
            'INV-001';
        }
        break;

      // Layouts et structures
      case 'layout_header':
      case 'layout_footer':
      case 'layout_sidebar':
      case 'layout_section':
      case 'layout_container':
        if (!normalized.content) {
          (normalized as any).content = '';
        }
        break;

      // Éléments dynamiques
      case 'table_dynamic':
      case 'gradient_box':
      case 'shadow_box':
      case 'rounded_box':
      case 'border_box':
      case 'background_pattern':
      case 'watermark':
        if (!normalized.content) {
          (normalized as any).content = '';
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
    debugWarn('❌ [SAVE NORMALIZE] Elements n\'est pas un array');
    return [];
  }

  return elements.map((el, idx) => {
    if (!el || typeof el !== 'object') {
      debugWarn(`❌ [SAVE NORMALIZE] Element ${idx} invalide`);
      return {} as Element;
    }

    // Créer une copie COMPLÈTE
    const normalized: Element = {
      ...el
    } as Element;

    // Valider les champs critiques
    if (!normalized.id) normalized.id = `element-${idx}`;
    if (!normalized.type) normalized.type = 'unknown';
    if (typeof normalized.x !== 'number') normalized.x = 0;
    if (typeof normalized.y !== 'number') normalized.y = 0;
    if (typeof normalized.width !== 'number') normalized.width = 100;
    if (typeof normalized.height !== 'number') normalized.height = 100;

    // CRITICAL: Log les propriétés order_number avant sauvegarde
    if (normalized.type === 'order_number') {
    }

    // Filtrer les propriétés non sérialisables (Date, Function, etc)
    const serializable: Record<string, unknown> = {};
    
    // ========== PROPRIÉTÉS CRITIQUES À PRÉSERVER ==========
    // Les styles dédans ce set ne doivent JAMAIS être perdus lors de la sauvegarde
    const styleProperties = new Set([
      'fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'fontColor', 'color',
      'backgroundColor', 'bgColor', 'textAlign', 'textDecoration', 'textTransform',
      'letterSpacing', 'wordSpacing', 'lineHeight', 'opacity', 'zIndex',
      'border', 'borderTop', 'borderBottom', 'borderLeft', 'borderRight', 'borderColor', 'borderWidth', 'borderStyle',
      'padding', 'margin', 'display', 'width', 'height', 'x', 'y',
      'showEmail', 'showPhone', 'showSiret', 'showVat', 'separator', // mentions properties
      'showCompanyName', 'showAddress', 'showRcs', 'showCapital', // company_info properties
      'text', 'content', 'src', 'alt' // Contenu
    ]);
    
    Object.keys(normalized).forEach(key => {
      const value = normalized[key];
      const type = typeof value;

      // DEBUG: Log des propriétés spéciales
      if (key.includes('🎯') || key.includes('interactions') || key.includes('comportement') || key.includes('behavior')) {
        // 
      }

      // Garder: string, number, boolean, null, undefined
      // Garder: objects simples et arrays
      // REJETER: functions, symbols, dates (sauf si sérialisées)
      if (
        value === null || 
        value === undefined ||
        type === 'string' || 
        type === 'number' || 
        type === 'boolean'
      ) {
        serializable[key] = value;
      } else if (type === 'object') {
        try {
          // Vérifier si c'est sérialisable
          JSON.stringify(value);
          serializable[key] = value;
        } catch {
          debugWarn(`⚠️  [SAVE NORMALIZE] Propriété non sérialisable ${key} skippée`, value);
        }
      } else {
        // Propriétés rejetées (functions, etc.)
        debugWarn(`⚠️  [SAVE NORMALIZE] Propriété rejetée: ${key} (type: ${type})`);
      }
    });

    // ✅ VÉRIFICATION: Log des propriétés de style critiques existantes
    if (normalized.type === 'text' || normalized.type === 'mentions' || normalized.type === 'company_info') {
      const existingStyles = Array.from(styleProperties).filter(prop => serializable[prop] !== undefined);
      // 
    }

    return serializable as Element;
  }) as Element[];
}

/**
 * Valide que les propriétés critiques sont présentes
 */
export function validateElementIntegrity(elements: Element[], elementType: string): boolean {
  const elementsOfType = elements.filter(el => el.type === elementType);
  
  if (elementsOfType.length === 0) {
    return true; // Pas d'éléments de ce type
  }

  let allValid = true;
  elementsOfType.forEach((el, idx) => {
    const required: (keyof Element)[] = ['id', 'type', 'x', 'y', 'width', 'height'];
    const missing = required.filter(key => !(key in el));

    if (missing.length > 0) {
      debugError(`❌ [VALIDATE] Element ${idx} missing: ${missing.join(', ')}`);
      allValid = false;
    }

    if (elementType === 'order_number') {
      const hasContentAlign = 'contentAlign' in el;
      const hasLabelPosition = 'labelPosition' in el;

      if (!hasContentAlign || !hasLabelPosition) {
        allValid = false;
      }
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



