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



