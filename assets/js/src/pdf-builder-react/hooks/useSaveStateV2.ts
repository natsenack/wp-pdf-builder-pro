import { useCallback, useRef, useState, useEffect } from 'react';
import { Element } from '../types/elements';
import { debugLog, debugError } from '../utils/debug';

/**
 * Hook simplifié pour auto-save
 * 
 * Logique :
 * 1. Détecte changements (hash robuste)
 * 2. Attend 3 secondes d'inactivité
 * 3. Envoie la requête AJAX
 * 4. Affiche "Sauvegarde..." pendant le saving
 * 5. Affiche "Sauvegardé" si succès (2s puis disparaît)
 * 6. Affiche "Erreur" si échec
 */

export type SaveState = 'idle' | 'saving' | 'saved' | 'error';

export interface UseSaveStateV2Options {
  templateId?: number;
  elements: Element[];
  nonce: string;
  autoSaveInterval?: number; // Minimum entre sauvegardes
  onSaveStart?: () => void;
  onSaveSuccess?: (savedAt: string) => void;
  onSaveError?: (error: string) => void;
}

export interface UseSaveStateV2Return {
  state: SaveState;
  isSaving: boolean;
  lastSavedAt: string | null;
  error: string | null;
  saveNow: () => Promise<void>;
  triggerSave: () => void; // Déclenche une sauvegarde immédiate
  clearError: () => void;
  progress: number;
}

export function useSaveStateV2({
  templateId,
  elements,
  nonce,
  autoSaveInterval = 5000,
  onSaveStart,
  onSaveSuccess,
  onSaveError
}: UseSaveStateV2Options): UseSaveStateV2Return {
  // État
  const [state, setState] = useState<SaveState>('idle');
  const [lastSavedAt, setLastSavedAt] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [progress, setProgress] = useState(0);

  // Refs pour tracking
  const elementsHashRef = useRef<string>('');
  const autoSaveTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const saveRequestTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const savedStateTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const progressIntervalRef = useRef<NodeJS.Timeout | null>(null);
  const lastSaveTimeRef = useRef<number>(0);
  const inProgressRef = useRef<boolean>(false);

  /**
   * Calcule un hash robuste des éléments
   */
  const getElementsHash = useCallback((els: Element[]): string => {
    try {
      const cleanObject = (obj: unknown): unknown => {
        if (obj === null || typeof obj !== 'object') return obj;
        if (Array.isArray(obj)) return obj.map(cleanObject);

        const cleaned: Record<string, unknown> = {};
        for (const key in obj) {
          if (
            key === 'id' ||
            key === 'key' ||
            key === 'ref' ||
            key === '_owner' ||
            key === '_store' ||
            key.startsWith('__') ||
            key === 'canvas' ||
            key === 'context' ||
            key === 'updatedAt' ||
            key === 'createdAt' ||
            key === 'timestamp' ||
            typeof (obj as Record<string, unknown>)[key] === 'function'
          ) {
            continue;
          }
          cleaned[key] = cleanObject((obj as Record<string, unknown>)[key]);
        }
        return cleaned;
      };

      const cleanedElements = els.map(cleanObject);
      return JSON.stringify(cleanedElements);
    } catch {
      return '';
    }
  }, []);

  /**
   * Effectue l'auto-save
   */
  const performSave = useCallback(async () => {
    if (!templateId || inProgressRef.current) return;
    
    inProgressRef.current = true;
    const now = Date.now();
    lastSaveTimeRef.current = now;

    try {
      // Démarrer la sauvegarde
      setState('saving');
      setError(null);
      setProgress(0);
      onSaveStart?.();

      // Animer la progression
      let currentProgress = 0;
      progressIntervalRef.current = setInterval(() => {
        currentProgress += Math.random() * 20;
        setProgress(Math.min(90, currentProgress));
      }, 150);

      // Nettoyer les éléments
      const cleanElements = elements.map(el => {
        const cleaned: Record<string, unknown> = {};
        Object.keys(el).forEach(key => {
          if (typeof el[key as keyof Element] !== 'function' && !key.startsWith('__')) {
            cleaned[key] = el[key as keyof Element];
          }
        });
        return cleaned;
      });

      // 🔍 DEBUG: Log elements being sent
      debugLog('[SAVE V2] Elements avant envoi (count=' + cleanElements.length + ')');
      if (cleanElements.length > 0) {
        debugLog('[SAVE V2] Element[0] structure: ' + JSON.stringify(cleanElements[0]));
        // Check for company_logo specifically
        cleanElements.forEach((el, idx) => {
          const elType = (el as Record<string, unknown>)?.type;
          const elSrc = (el as Record<string, unknown>)?.src;
          if (elType === 'company_logo') {
            debugLog('[SAVE V2] company_logo[' + idx + '] sent: src=' + (elSrc || 'MISSING'));
          }
        });
      }

      // Faire la requête
      debugLog('[SAVE V2] Envoi de la requête AJAX avec méthode POST...');
      const ajaxUrl = (window as any).pdfBuilderData?.ajaxUrl || '/wp-admin/admin-ajax.php';
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          'action': 'pdf_builder_auto_save_template',
          'nonce': nonce,
          'template_id': templateId.toString(),
          'elements': JSON.stringify(cleanElements)
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      debugLog('[SAVE V2] Server response: ' + JSON.stringify(data));
      if (!data.success) {
        throw new Error(data.data?.message || 'Erreur serveur');
      }

      const savedAt = data.data?.saved_at || new Date().toISOString();

      // Succès : progression à 100%
      if (progressIntervalRef.current) {
        clearInterval(progressIntervalRef.current);
        progressIntervalRef.current = null;
      }
      setProgress(100);

      setState('saved');
      setLastSavedAt(savedAt);
      elementsHashRef.current = getElementsHash(elements);
      onSaveSuccess?.(savedAt);

      debugLog('[SAVE V2] Sauvegarde réussie');

      // Retourner à idle après 2 secondes
      if (savedStateTimeoutRef.current) {
        clearTimeout(savedStateTimeoutRef.current);
      }
      savedStateTimeoutRef.current = setTimeout(() => {
        setState('idle');
        setProgress(0);
      }, 2000);
    } catch (err: unknown) {
      debugError('[SAVE V2] Erreur:', (err as Error)?.message);

      // Nettoyage
      if (progressIntervalRef.current) {
        clearInterval(progressIntervalRef.current);
        progressIntervalRef.current = null;
      }
      setProgress(0);

      setState('error');
      setError((err as Error)?.message || 'Erreur inconnue');
      onSaveError?.((err as Error)?.message);

      // Retourner à idle après 3 secondes
      if (savedStateTimeoutRef.current) {
        clearTimeout(savedStateTimeoutRef.current);
      }
      savedStateTimeoutRef.current = setTimeout(() => {
        setState('idle');
        setProgress(0);
      }, 3000);
    } finally {
      inProgressRef.current = false;
    }
  }, [templateId, elements, nonce, getElementsHash, onSaveStart, onSaveSuccess, onSaveError]);

  /**
   * Sauvegarde manuelle
   */
  const saveNow = useCallback(async () => {
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
      autoSaveTimeoutRef.current = null;
    }
    await performSave();
  }, [performSave]);

  /**
   * Déclenche une sauvegarde sans délai
   */
  const triggerSave = useCallback(() => {
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
      autoSaveTimeoutRef.current = null;
    }
    performSave();
  }, [performSave]);

  /**
   * Efface les erreurs
   */
  const clearError = useCallback(() => {
    setError(null);
    setState('idle');
    setProgress(0);
  }, []);

  /**
   * Effect : Détecte les changements et déclenche l'auto-save
   */
  useEffect(() => {
    const currentHash = getElementsHash(elements);

    // Si les éléments n'ont pas changé, ne rien faire
    if (currentHash === elementsHashRef.current) {
      return;
    }

    debugLog('[SAVE V2] Changements détectés, programmation sauvegarde...');

    // Annuler le timeout précédent
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
    }

    // Ne pas sauvegarder si une sauvegarde est en cours
    if (state === 'saving') {
      return;
    }

    // Attendre 3 secondes d'inactivité avant de sauvegarder
    autoSaveTimeoutRef.current = setTimeout(() => {
      // Ne rien faire si l'auto-save est désactivé (autoSaveInterval === 0)
      if (autoSaveInterval === 0) {
        return;
      }
      const timeSinceLastSave = Date.now() - lastSaveTimeRef.current;
      if (timeSinceLastSave >= autoSaveInterval) {
        performSave();
      }
    }, 3000);

    return () => {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }
    };
  }, [elements, getElementsHash, state, autoSaveInterval, performSave]);

  /**
   * Cleanup à la dé-montage
   */
  useEffect(() => {
    const autoSaveTimeout = autoSaveTimeoutRef.current;
    const saveRequestTimeout = saveRequestTimeoutRef.current;
    const savedStateTimeout = savedStateTimeoutRef.current;
    const progressInterval = progressIntervalRef.current;

    return () => {
      if (autoSaveTimeout) clearTimeout(autoSaveTimeout);
      if (saveRequestTimeout) clearTimeout(saveRequestTimeout);
      if (savedStateTimeout) clearTimeout(savedStateTimeout);
      if (progressInterval) clearInterval(progressInterval);
    };
  }, []);

  return {
    state,
    isSaving: state === 'saving',
    lastSavedAt,
    error,
    saveNow,
    triggerSave,
    clearError,
    progress
  };
}
