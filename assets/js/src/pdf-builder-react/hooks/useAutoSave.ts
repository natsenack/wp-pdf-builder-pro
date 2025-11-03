import { useBuilder } from '../contexts/builder/BuilderContext';
import { useSaveStateV2, SaveState } from './useSaveStateV2';
import { debugLog, debugError } from '../utils/debug';

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
  saveNow: () => Promise<void>;
  triggerSave: () => void;
  clearError: () => void;
  progress: number;
}

export function useAutoSave(): UseAutoSaveReturn {
  const { state } = useBuilder();

  // Récupérer le nonce
  const nonce =
    (window as any).pdfBuilderData?.nonce ||
    (window as any).pdfBuilderNonce ||
    (window as any).pdfBuilderReactData?.nonce ||
    '';

  // Utiliser useSaveStateV2
  const {
    state: saveState,
    isSaving,
    lastSavedAt,
    error,
    saveNow,
    triggerSave,
    clearError,
    progress
  } = useSaveStateV2({
    templateId: state.template.id as number | undefined,
    elements: state.elements,
    nonce,
    autoSaveInterval: 5000,
    onSaveStart: () => {
      debugLog('[AUTO SAVE] Sauvegarde commencée');
    },
    onSaveSuccess: (savedAt: string) => {
      debugLog(`[AUTO SAVE] Succès le ${savedAt}`);
    },
    onSaveError: (error: string) => {
      debugError(`[AUTO SAVE] Erreur: ${error}`);
    }
  });

  return {
    state: saveState,
    isSaving,
    lastSavedAt,
    error,
    saveNow,
    triggerSave,
    clearError,
    progress
  };
}
