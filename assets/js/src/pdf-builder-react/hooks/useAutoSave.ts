import { useBuilder } from '../contexts/builder/BuilderContext';
import { useSaveState, SaveState } from './useSaveState';

/**
 * Hook useAutoSave
 * Enveloppe useSaveState pour l'intégration avec BuilderContext
 * Expose l'état de sauvegarde automatique
 */

export interface UseAutoSaveReturn {
  state: SaveState;
  isSaving: boolean;
  lastSavedAt: string | null;
  error: string | null;
  retryCount: number;
  saveNow: () => Promise<void>;
  clearError: () => void;
}

export function useAutoSave(): UseAutoSaveReturn {
  const { state } = useBuilder();

  // Récupérer le nonce
  const nonce =
    (window as any).pdfBuilderData?.nonce ||
    (window as any).pdfBuilderNonce ||
    (window as any).pdfBuilderReactData?.nonce ||
    '';

  // Utiliser useSaveState
  const {
    state: saveState,
    isSaving,
    lastSavedAt,
    error,
    saveNow,
    clearError,
    retryCount
  } = useSaveState({
    templateId: state.template.id as number | undefined,
    elements: state.elements,
    nonce,
    autoSaveInterval: 2500, // 2.5 secondes
    maxRetries: 3,
    onSaveStart: () => {
      console.log('[AUTO SAVE] Sauvegarde commencée');
    },
    onSaveSuccess: (savedAt: string) => {
      console.log(`[AUTO SAVE] Succès le ${savedAt}`);
    },
    onSaveError: (error: string) => {
      console.error(`[AUTO SAVE] Erreur: ${error}`);
    }
  });

  return {
    state: saveState,
    isSaving,
    lastSavedAt,
    error,
    retryCount,
    saveNow,
    clearError
  };
}
