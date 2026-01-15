import { useBuilder } from '../contexts/builder/BuilderContext';
import { SaveState } from './useSaveStateV2';
import { debugError, debugLog } from '../utils/debug';

/**
 * Hook useAutoSave - DÉSACTIVÉ
 * Le système d'auto-save a été supprimé
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
  // Auto-save désactivé - retourner un état fixe
  debugLog('[PDF Builder] useAutoSave - Auto-save is DISABLED');

  return {
    state: 'idle' as SaveState,
    isSaving: false,
    lastSavedAt: null,
    error: null,
    saveNow: async () => {
      // Ne fait rien - auto-save désactivé
      debugLog('[PDF Builder] useAutoSave - saveNow called but auto-save is disabled');
    },
    triggerSave: () => {
      // Ne fait rien - auto-save désactivé
      debugLog('[PDF Builder] useAutoSave - triggerSave called but auto-save is disabled');
    },
    clearError: () => {
      // Ne fait rien - auto-save désactivé
    },
    progress: 0,
    isEnabled: false
  };
}

