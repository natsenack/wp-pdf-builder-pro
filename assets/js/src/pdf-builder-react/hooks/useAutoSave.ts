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

  // Récupérer l'intervalle de sauvegarde auto depuis les settings
  // Par défaut: 30 secondes si paramètre non défini
  const autoSaveIntervalSetting = (window as any).pdfBuilderCanvasSettings?.auto_save_interval ||
    (window as any).pdfBuilderData?.auto_save_interval ||
    (window as any).pdfBuilderReactData?.auto_save_interval ||
    30; // 30 secondes par défaut
  
  const autoSaveInterval = Math.max(10, autoSaveIntervalSetting) * 1000; // Convertir en ms, min 10s

  // Vérifier si l'auto-save est activé dans les settings
  // Doit être explicitement TRUE pour être activé
  const autoSaveEnabled = 
    (window as any).pdfBuilderCanvasSettings?.auto_save_enabled === true;

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
    autoSaveInterval: autoSaveEnabled ? autoSaveInterval : 0, // 0 désactive l'auto-save
    onSaveStart: () => {
      debugLog('[AUTO SAVE] Sauvegarde commencée (intervalle: ' + autoSaveInterval + 'ms)');
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
