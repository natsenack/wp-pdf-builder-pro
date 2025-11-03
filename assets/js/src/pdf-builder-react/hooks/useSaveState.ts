import { useCallback, useRef, useState, useEffect, startTransition } from 'react';
import { debugLog, debugError, debugWarn } from '../utils/debug';

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
  const saveCooldownRef = useRef<number>(0);
  const saveOperationIdRef = useRef<number>(0);
  const savedStateTimeoutRef = useRef<NodeJS.Timeout | null>(null); // Timer pour revenir à idle

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
      debugWarn('[SAVE STATE] Pas de templateId, sauvegarde annulée');
        return false;
      }

      // Annuler les timers d'état sauvegardé avant de commencer une nouvelle sauvegarde
      if (savedStateTimeoutRef.current) {
        clearTimeout(savedStateTimeoutRef.current);
        savedStateTimeoutRef.current = null;
      }

      // Timeout global de 15 secondes pour éviter le blocage
      const saveTimeout = setTimeout(() => {
        debugError('[SAVE STATE] Timeout global dépassé, forçage de l\'état error');
        startTransition(() => {
          setState('error');
          setError('Timeout de sauvegarde');
        });
        onSaveError?.('Timeout de sauvegarde');
      }, 15000);

      try {
        // Set saving state
        startTransition(() => {
          setState('saving');
        });
        console.log('[SAVE STATE] État changé à saving');
        
        // Annuler le timer de retour à idle s'il existe (nouvelle sauvegarde commence)
        if (savedStateTimeoutRef.current) {
          clearTimeout(savedStateTimeoutRef.current);
          savedStateTimeoutRef.current = null;
        }
        
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

        debugLog('[SAVE STATE] Éléments originaux:', elements.length, 'éléments');
        debugLog('[SAVE STATE] Premier élément original:', elements[0]);
        debugLog('[SAVE STATE] Éléments nettoyés:', cleanElements.length, 'éléments');
        debugLog('[SAVE STATE] Premier élément nettoyé:', cleanElements[0]);

        // Sérialisation JSON
        const serializedElements = JSON.stringify(cleanElements);

        // Vérification JSON valide
        JSON.parse(serializedElements);

        debugLog(`[SAVE STATE] Tentative ${retryAttempt + 1}/${maxRetries + 1} - Envoi AJAX...`);

        const ajaxUrl = (window as any).ajaxurl || '/wp-admin/admin-ajax.php';
        
        // Créer un AbortController pour timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 secondes timeout
        
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
          }),
          signal: controller.signal
        });
        
        clearTimeout(timeoutId); // Nettoyer le timeout si la requête réussit

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
          throw new Error(data.data?.message || 'Erreur serveur inconnue');
        }

        // Succès
        const savedAt = data.data?.saved_at || new Date().toISOString();

        // Update state
        startTransition(() => {
          setLastSavedAt(savedAt);
          setState('saved');
          setError(null);
          setRetryCount(0);
        });

        console.log('[SAVE STATE] État changé à saved', { savedAt, state });

        debugLog('[SAVE STATE] État changé à saved');

        lastSaveTimeRef.current = Date.now();
        elementsHashRef.current = getElementsHash(elements);

          debugLog(`✅ [SAVE STATE] Sauvegarde réussie à ${savedAt}`);
        onSaveSuccess?.(savedAt);

        // Nettoyer le timer précédent pour revenir à idle
        if (savedStateTimeoutRef.current) {
          clearTimeout(savedStateTimeoutRef.current);
        }

        // Réinitialiser l'état saved après 3 secondes
        savedStateTimeoutRef.current = setTimeout(() => {
          console.log('[SAVE STATE] Remise à idle après succès');
          startTransition(() => {
            // Vérifier que l'état est toujours 'saved' avant de le changer
            // (évite les conflits si une nouvelle sauvegarde a commencé)
            if (state === 'saved') {
              setState('idle');
            }
          });
        }, 3000);

        clearTimeout(saveTimeout); // Nettoyer le timeout global
        return true;
      } catch (err: any) {
        clearTimeout(saveTimeout); // Nettoyer le timeout global
        
        const errorMsg = err?.name === 'AbortError' 
          ? 'Timeout de la requête (10s)' 
          : (err?.message || 'Erreur inconnue');
        debugError(`[SAVE STATE] Erreur ${retryAttempt + 1}/${maxRetries + 1}:`, errorMsg);

        // Retry logic
        if (retryAttempt < maxRetries) {
          const delayMs = Math.min(1000 * Math.pow(2, retryAttempt), 10000); // Backoff exponentiel
          debugLog(`[SAVE STATE] Retry dans ${delayMs}ms...`);

          startTransition(() => {
            setRetryCount(retryAttempt + 1);
          });

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
        console.log('[SAVE STATE] État changé à error');
        
        // Annuler le timer de retour à idle s'il existe
        if (savedStateTimeoutRef.current) {
          clearTimeout(savedStateTimeoutRef.current);
          savedStateTimeoutRef.current = null;
        }
        
        onSaveError?.(errorMsg);
        debugError(`❌ [SAVE STATE] Sauvegarde échouée après ${maxRetries + 1} tentatives`);

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
    
    // Nettoyer tous les timers liés aux états
    if (savedStateTimeoutRef.current) {
      clearTimeout(savedStateTimeoutRef.current);
      savedStateTimeoutRef.current = null;
    }
    if (retryTimeoutRef.current) {
      clearTimeout(retryTimeoutRef.current);
      retryTimeoutRef.current = null;
    }
    
    console.log('[SAVE STATE] État changé à idle (clearError)');
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
    const timeSinceLastCooldown = Date.now() - saveCooldownRef.current;

    // Ne pas programmer de timeout si une sauvegarde est en cours ou récemment faite
    if (pendingSaveRef.current || timeSinceLastCooldown < 5000) {
      return;
    }

    if (!hasChanged) {
      return;
    }

    // Annuler le timeout d'inactivité précédent
    if (inactivityTimeoutRef.current) {
      clearTimeout(inactivityTimeoutRef.current);
    }

    // Générer un ID unique pour cette opération de sauvegarde
    const operationId = ++saveOperationIdRef.current;

    // Programmer une vérification d'inactivité
    inactivityTimeoutRef.current = setTimeout(() => {
      const timeSinceLastChange = Date.now() - lastChangeTimeRef.current;
      const timeSinceLastSave = Date.now() - lastSaveTimeRef.current;
      const timeSinceLastCooldown = Date.now() - saveCooldownRef.current;

      // Sauvegarder seulement si inactif depuis 3 secondes, interval minimum écoulé,
      // pas de sauvegarde en cours, et cooldown écoulé (5 secondes minimum entre sauvegardes)
      // Vérifier aussi que cette opération est toujours la plus récente
      if (timeSinceLastChange >= 3000 &&
          timeSinceLastSave >= autoSaveInterval &&
          timeSinceLastCooldown >= 5000 &&
          !pendingSaveRef.current &&
          operationId === saveOperationIdRef.current) {

        debugLog(`[SAVE STATE] Inactivité détectée (opération ${operationId}), planification sauvegarde...`);

        // Marquer le cooldown
        saveCooldownRef.current = Date.now();

        // Nettoyer le timeout précédent
        if (autoSaveTimeoutRef.current) {
          clearTimeout(autoSaveTimeoutRef.current);
        }

        // Planifier la sauvegarde avec un délai plus long pour laisser React finir
        autoSaveTimeoutRef.current = setTimeout(() => {
          if (!pendingSaveRef.current && operationId === saveOperationIdRef.current) {
            pendingSaveRef.current = true;
            // Utiliser queueMicrotask pour s'assurer que React a fini ses updates
            queueMicrotask(() => performSave(0).finally(() => {
              pendingSaveRef.current = false;
            }));
          }
        }, 500); // Augmenter à 500ms
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
      if (savedStateTimeoutRef.current) {
        clearTimeout(savedStateTimeoutRef.current);
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
      if (savedStateTimeoutRef.current) {
        clearTimeout(savedStateTimeoutRef.current);
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
