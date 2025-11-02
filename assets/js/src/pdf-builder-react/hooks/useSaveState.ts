import { useCallback, useRef, useState, useEffect, startTransition } from 'react';

/**
 * Hook pour gérer la sauvegarde automatique avec retry logic
 * 
 * Features:
 * - Sauvegarde automatique toutes les 2-3 secondes
 * - Retry automatique (3 tentatives) en cas d'erreur
 * - Gestion d'erreurs et feedback utilisateur
 * - Debouncing pour éviter les sauvegardes inutiles
 */

export type SaveState = 'idle' | 'saving' | 'saved' | 'error';

export interface UseSaveStateOptions {
  templateId?: number;
  elements: any[];
  nonce: string;
  autoSaveInterval?: number; // ms (défaut 2500)
  maxRetries?: number; // (défaut 3)
  onSaveStart?: () => void;
  onSaveSuccess?: (savedAt: string) => void;
  onSaveError?: (error: string) => void;
}

export interface UseSaveStateReturn {
  state: SaveState;
  isSaving: boolean;
  lastSavedAt: string | null;
  error: string | null;
  saveNow: () => Promise<void>;
  clearError: () => void;
  retryCount: number;
}

export function useSaveState({
  templateId,
  elements,
  nonce,
  autoSaveInterval = 2500,
  maxRetries = 3,
  onSaveStart,
  onSaveSuccess,
  onSaveError
}: UseSaveStateOptions): UseSaveStateReturn {
  // État
  const [state, setState] = useState<SaveState>('idle');
  const [lastSavedAt, setLastSavedAt] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [retryCount, setRetryCount] = useState(0);

  // Refs pour le debouncing et la gestion des timers
  const autoSaveTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const retryTimeoutRef = useRef<NodeJS.Timeout | null>(null);
  const lastSaveTimeRef = useRef<number>(0);
  const elementsHashRef = useRef<string>('');
  const pendingSaveRef = useRef<boolean>(false);
  const lastChangeTimeRef = useRef<number>(0);
  const inactivityTimeoutRef = useRef<NodeJS.Timeout | null>(null);

  /**
   * Calcule un hash simple des éléments pour détecter les changements
   */
  const getElementsHash = useCallback((els: any[]): string => {
    try {
      return JSON.stringify(els).substring(0, 100);
    } catch {
      return 'error';
    }
  }, []);

  /**
   * Effectue la sauvegarde via AJAX
   */
  const performSave = useCallback(
    async (retryAttempt = 0): Promise<boolean> => {
      if (!templateId) {
        console.warn('[SAVE STATE] Pas de templateId, sauvegarde annulée');
        return false;
      }

      try {
        startTransition(() => {
          setState('saving');
        });
        onSaveStart?.();

        // Nettoyage des éléments pour JSON - version plus permissive
        const cleanElements = elements.map(el => {
          const cleaned: any = {};
          Object.keys(el).forEach(key => {
            const value = el[key];
            // Skip seulement les propriétés vraiment problématiques
            if (
              typeof value !== 'function' &&
              !key.startsWith('__') && // Garde les propriétés avec un seul _
              key !== 'canvas' &&
              key !== 'context' &&
              key !== 'ref' &&
              key !== 'key' &&
              key !== 'props' &&
              key !== 'state' &&
              key !== 'updater'
            ) {
              cleaned[key] = value;
            }
          });
          return cleaned;
        });

        console.log('[SAVE STATE] Éléments originaux:', elements.length, 'éléments');
        console.log('[SAVE STATE] Premier élément original:', elements[0]);
        console.log('[SAVE STATE] Éléments nettoyés:', cleanElements.length, 'éléments');
        console.log('[SAVE STATE] Premier élément nettoyé:', cleanElements[0]);

        // Sérialisation JSON
        const serializedElements = JSON.stringify(cleanElements);

        // Vérification JSON valide
        JSON.parse(serializedElements);

        console.log(`[SAVE STATE] Tentative ${retryAttempt + 1}/${maxRetries + 1} - Envoi AJAX...`);

        const ajaxUrl = (window as any).ajaxurl || '/wp-admin/admin-ajax.php';
        const response = await fetch(ajaxUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            action: 'pdf_builder_auto_save_template',
            template_id: templateId.toString(),
            elements: serializedElements,
            nonce: nonce
          })
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
          throw new Error(data.data?.message || 'Erreur serveur inconnue');
        }

        // Succès
        const savedAt = data.data?.saved_at || new Date().toISOString();
        startTransition(() => {
          setLastSavedAt(savedAt);
          setState('saved');
          setError(null);
          setRetryCount(0);
        });
        lastSaveTimeRef.current = Date.now();
        elementsHashRef.current = getElementsHash(elements);

        console.log(`✅ [SAVE STATE] Sauvegarde réussie à ${savedAt}`);
        onSaveSuccess?.(savedAt);

        // Réinitialiser l'état saved après 2 secondes
        setTimeout(() => {
          startTransition(() => {
            setState(current => current === 'saved' ? 'idle' : current);
          });
        }, 2000);

        return true;
      } catch (err: any) {
        const errorMsg = err?.message || 'Erreur inconnue';
        console.error(`[SAVE STATE] Erreur ${retryAttempt + 1}/${maxRetries + 1}:`, errorMsg);

        // Retry logic
        if (retryAttempt < maxRetries) {
          const delayMs = Math.min(1000 * Math.pow(2, retryAttempt), 10000); // Backoff exponentiel
          console.log(`[SAVE STATE] Retry dans ${delayMs}ms...`);

          setRetryCount(retryAttempt + 1);

          // Nettoyer le timeout précédent
          if (retryTimeoutRef.current) {
            clearTimeout(retryTimeoutRef.current);
          }

          // Retry avec délai
          retryTimeoutRef.current = setTimeout(() => {
            performSave(retryAttempt + 1);
          }, delayMs);

          return false;
        }

        // Échec définitif après tous les retries
        startTransition(() => {
          setState('error');
          setError(errorMsg);
        });
        onSaveError?.(errorMsg);
        console.error(`❌ [SAVE STATE] Sauvegarde échouée après ${maxRetries + 1} tentatives`);

        return false;
      }
    },
    [templateId, elements, nonce, maxRetries, onSaveStart, onSaveSuccess, onSaveError, getElementsHash]
  );

  /**
   * Sauvegarde immédiate (appelée manuellement si besoin)
   */
  const saveNow = useCallback(async (): Promise<void> => {
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
      autoSaveTimeoutRef.current = null;
    }
    if (!pendingSaveRef.current) {
      pendingSaveRef.current = true;
      await performSave(0);
      pendingSaveRef.current = false;
    }
  }, [performSave]);

  /**
   * Efface les erreurs
   */
  const clearError = useCallback(() => {
    startTransition(() => {
      setError(null);
      setState('idle');
    });
  }, []);

  /**
   * Effect pour gérer la sauvegarde automatique avec détection d'inactivité
   */
  useEffect(() => {
    if (!templateId || elements.length === 0) {
      return;
    }

    // Mettre à jour le temps du dernier changement
    lastChangeTimeRef.current = Date.now();
    const currentHash = getElementsHash(elements);
    const hasChanged = currentHash !== elementsHashRef.current;

    if (!hasChanged) {
      return;
    }

    // Annuler le timeout d'inactivité précédent
    if (inactivityTimeoutRef.current) {
      clearTimeout(inactivityTimeoutRef.current);
    }

    // Programmer une vérification d'inactivité
    inactivityTimeoutRef.current = setTimeout(() => {
      const timeSinceLastChange = Date.now() - lastChangeTimeRef.current;
      const timeSinceLastSave = Date.now() - lastSaveTimeRef.current;

      // Sauvegarder seulement si inactif depuis 3 secondes et interval minimum écoulé
      if (timeSinceLastChange >= 3000 && timeSinceLastSave >= autoSaveInterval && !pendingSaveRef.current) {
        console.log('[SAVE STATE] Inactivité détectée, planification sauvegarde...');

        // Nettoyer le timeout précédent
        if (autoSaveTimeoutRef.current) {
          clearTimeout(autoSaveTimeoutRef.current);
        }

        // Planifier la sauvegarde
        autoSaveTimeoutRef.current = setTimeout(() => {
          if (!pendingSaveRef.current) {
            pendingSaveRef.current = true;
            performSave(0).finally(() => {
              pendingSaveRef.current = false;
            });
          }
        }, 100); // Petit délai pour éviter les conflits
      }
    }, 3000); // Attendre 3 secondes d'inactivité

    // Cleanup
    return () => {
      if (inactivityTimeoutRef.current) {
        clearTimeout(inactivityTimeoutRef.current);
      }
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }
    };
  }, [templateId, elements, autoSaveInterval, performSave, getElementsHash]);

  /**
   * Cleanup des timers à la dé-montage
   */
  useEffect(() => {
    return () => {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }
      if (retryTimeoutRef.current) {
        clearTimeout(retryTimeoutRef.current);
      }
      if (inactivityTimeoutRef.current) {
        clearTimeout(inactivityTimeoutRef.current);
      }
    };
  }, []);

  return {
    state,
    isSaving: state === 'saving',
    lastSavedAt,
    error,
    saveNow,
    clearError,
    retryCount
  };
}
