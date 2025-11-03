import { useCallback, useRef, useState, useEffect } from 'react';
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
  elements: any[];
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
  const getElementsHash = useCallback((els: any[]): string => {
    try {
      const cleanObject = (obj: any): any => {
        if (obj === null || typeof obj !== 'object') return obj;
        if (Array.isArray(obj)) return obj.map(cleanObject);

        const cleaned: any = {};
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
            typeof obj[key] === 'function'
          ) {
            continue;
          }
          cleaned[key] = cleanObject(obj[key]);
        }
        return cleaned;
      };

      const cleanedElements = els.map(cleanObject);
      return JSON.stringify(cleanedElements);
    } catch (err) {
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
        const cleaned: any = {};
        Object.keys(el).forEach(key => {
          if (typeof el[key] !== 'function' && !key.startsWith('__')) {
            cleaned[key] = el[key];
          }
        });
        return cleaned;
      });

      // Faire la requête
      debugLog('[SAVE V2] Envoi de la requête AJAX...');
      const response = await fetch((window as any).ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'pdf_builder_auto_save_template',
          template_id: templateId.toString(),
          elements: JSON.stringify(cleanElements),
          nonce: nonce
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
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
    } catch (err: any) {
      debugError('[SAVE V2] Erreur:', err.message);

      // Nettoyage
      if (progressIntervalRef.current) {
        clearInterval(progressIntervalRef.current);
        progressIntervalRef.current = null;
      }
      setProgress(0);

      setState('error');
      setError(err.message || 'Erreur inconnue');
      onSaveError?.(err.message);

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
    return () => {
      if (autoSaveTimeoutRef.current) clearTimeout(autoSaveTimeoutRef.current);
      if (saveRequestTimeoutRef.current) clearTimeout(saveRequestTimeoutRef.current);
      if (savedStateTimeoutRef.current) clearTimeout(savedStateTimeoutRef.current);
      if (progressIntervalRef.current) clearInterval(progressIntervalRef.current);
    };
  }, []);

  return {
    state,
    isSaving: state === 'saving',
    lastSavedAt,
    error,
    saveNow,
    clearError,
    progress
  };
}
