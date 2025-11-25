import { useBuilder } from '../contexts/builder/BuilderContext';
import { useSaveStateV2, SaveState } from './useSaveStateV2';
import { debugError } from '../utils/debug';

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
  isEnabled: boolean;
}

export function useAutoSave(): UseAutoSaveReturn {
  const { state, dispatch } = useBuilder();

  // Récupérer le nonce
  const nonce =
    window.pdfBuilderData?.nonce ||
    window.pdfBuilderNonce ||
    window.pdfBuilderReactData?.nonce ||
    '';

  // Récupérer l'intervalle de sauvegarde auto depuis les settings
  // Par défaut: 5 minutes si paramètre non défini
  const autoSaveIntervalSetting = (window.pdfBuilderCanvasSettings as { auto_save_interval?: number })?.auto_save_interval ||
    (window.pdfBuilderCanvasSettings as { autosave_interval?: number })?.autosave_interval ||
    window.pdfBuilderData?.auto_save_interval ||
    (window.pdfBuilderReactData as { auto_save_interval?: number })?.auto_save_interval ||
    5; // 5 minutes par défaut
  
  const autoSaveInterval = Math.max(1, autoSaveIntervalSetting) * 60 * 1000; // Convertir minutes en ms, min 1 min

  // Vérifier si l'auto-save est activé dans les settings
  // Doit être explicitement TRUE pour être activé
  const autoSaveEnabled = 
    (window.pdfBuilderCanvasSettings as { auto_save_enabled?: boolean })?.auto_save_enabled === true ||
    (window.pdfBuilderCanvasSettings as { autosave_enabled?: boolean })?.autosave_enabled === true;

  // DEBUG: Logs détaillés
  console.log('[PDF Builder] useAutoSave - Configuration:', {
    nonce: nonce ? 'DEFINED' : 'UNDEFINED',
    autoSaveIntervalSetting,
    autoSaveInterval,
    autoSaveEnabled,
    pdfBuilderCanvasSettings: window.pdfBuilderCanvasSettings,
    pdfBuilderData: window.pdfBuilderData,
    pdfBuilderReactData: window.pdfBuilderReactData
  });

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

    },
    onSaveSuccess: (savedAt: string) => {

      // ✅ Mettre à jour l'état pour indiquer que le template n'est plus modifié
      dispatch({ type: 'SET_TEMPLATE_MODIFIED', payload: false });
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
    progress,
    isEnabled: autoSaveEnabled
  };
}
