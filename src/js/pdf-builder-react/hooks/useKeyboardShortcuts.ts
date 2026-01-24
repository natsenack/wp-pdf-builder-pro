import { useEffect, useCallback, useRef } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext';
import { useCanvasSetting } from './useCanvasSettings';
import { debugLog } from '../utils/debug';

/**
 * Hook pour gérer les raccourcis clavier du canvas
 * Implémente les raccourcis configurables via les paramètres canvas
 */
export const useKeyboardShortcuts = () => {
  const { state, dispatch } = useBuilder();
  const keyboardShortcutsEnabled = useCanvasSetting('enable_keyboard_shortcuts', true) as boolean;

  // Références pour éviter les closures stale
  const stateRef = useRef(state);
  const dispatchRef = useRef(dispatch);

  // Mettre à jour les références
  useEffect(() => {
    stateRef.current = state;
    dispatchRef.current = dispatch;
  }, [state, dispatch]);

  /**
   * Gère les événements clavier
   */
  const handleKeyDown = useCallback((event: Event) => {
    const keyboardEvent = event as unknown as {
      ctrlKey: boolean;
      metaKey: boolean;
      key: string;
      shiftKey: boolean;
      preventDefault: () => void;
      target: object;
    };
    
    // Ne pas traiter si les raccourcis sont désactivés
    if (!keyboardShortcutsEnabled) {
      debugLog('[KeyboardShortcuts] Shortcuts disabled - ignoring key event');
      return;
    }

    // Ne pas traiter si on est dans un champ de saisie
    const target = event.target as HTMLElement;
    if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.contentEditable === 'true') {
      debugLog('[KeyboardShortcuts] Ignoring key event in input field');
      return;
    }

    const { ctrlKey, metaKey, key, shiftKey } = keyboardEvent;
    const isCtrlOrCmd = ctrlKey || metaKey;
    const shortcut = `${isCtrlOrCmd ? (ctrlKey ? 'Ctrl' : 'Cmd') : ''}${shiftKey ? '+Shift' : ''}+${key.toUpperCase()}`;

    debugLog(`[KeyboardShortcuts] Key pressed: ${shortcut}`);

    switch (key.toLowerCase()) {
      case 'z':
        if (isCtrlOrCmd) {
          event.preventDefault();
          if (shiftKey) {
            // Ctrl+Y ou Cmd+Shift+Z pour redo
            debugLog('[KeyboardShortcuts] Executing redo (Ctrl+Shift+Z)');
            dispatchRef.current({ type: 'REDO' });
          } else {
            // Ctrl+Z pour undo
            debugLog('[KeyboardShortcuts] Executing undo (Ctrl+Z)');
            dispatchRef.current({ type: 'UNDO' });
          }
        }
        break;

      case 'y':
        if (isCtrlOrCmd && !shiftKey) {
          event.preventDefault();
          // Ctrl+Y pour redo
          debugLog('[KeyboardShortcuts] Executing redo (Ctrl+Y)');
          dispatchRef.current({ type: 'REDO' });
        }
        break;

      case 'a':
        if (isCtrlOrCmd) {
          event.preventDefault();
          // Ctrl+A pour tout sélectionner
          const allElementIds = stateRef.current.elements.map(el => el.id);
          debugLog(`[KeyboardShortcuts] Selecting all elements (${allElementIds.length} elements)`);
          dispatchRef.current({
            type: 'SET_SELECTION',
            payload: allElementIds
          });
        }
        break;

      case 'delete':
      case 'backspace':
        // Supprimer les éléments sélectionnés
        if (stateRef.current.selection.selectedElements.length > 0) {
          event.preventDefault();
          debugLog(`[KeyboardShortcuts] Deleting ${stateRef.current.selection.selectedElements.length} selected elements`);
          // Supprimer tous les éléments sélectionnés
          stateRef.current.selection.selectedElements.forEach(elementId => {
            dispatchRef.current({
              type: 'REMOVE_ELEMENT',
              payload: elementId
            });
          });
          // Vider la sélection après suppression
          dispatchRef.current({ type: 'CLEAR_SELECTION' });
        }
        break;

      case 'c':
        if (isCtrlOrCmd) {
          event.preventDefault();
          // Ctrl+C pour copier (si des éléments sont sélectionnés)
          if (stateRef.current.selection.selectedElements.length > 0) {
            debugLog(`[KeyboardShortcuts] Copying ${stateRef.current.selection.selectedElements.length} selected elements`);
            // TODO: Implémenter la logique de copie
          }
        }
        break;

      case 'v':
        if (isCtrlOrCmd) {
          event.preventDefault();
          // Ctrl+V pour coller
          debugLog('[KeyboardShortcuts] Pasting elements');
          // TODO: Implémenter la logique de collage
        }
        break;

      case 'd':
        if (isCtrlOrCmd) {
          event.preventDefault();
          // Ctrl+D pour dupliquer (optionnel)
          if (stateRef.current.selection.selectedElements.length > 0) {
            debugLog(`[KeyboardShortcuts] Duplicating ${stateRef.current.selection.selectedElements.length} selected elements`);
            // TODO: Implémenter la logique de duplication
          }
        }
        break;

      default:
        // Autres raccourcis peuvent être ajoutés ici
        debugLog(`[KeyboardShortcuts] Unhandled key: ${shortcut}`);
        break;
    }
  }, [keyboardShortcutsEnabled]);

  /**
   * Configure les écouteurs d'événements
   */
  useEffect(() => {
    if (!keyboardShortcutsEnabled) {
      debugLog('[KeyboardShortcuts] Keyboard shortcuts disabled');
      return;
    }

    debugLog('[KeyboardShortcuts] Initializing keyboard shortcuts');
    // Ajouter l'écouteur d'événements
    document.addEventListener('keydown', handleKeyDown);

    // Nettoyer l'écouteur
    return () => {
      debugLog('[KeyboardShortcuts] Cleaning up keyboard shortcuts');
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [handleKeyDown, keyboardShortcutsEnabled]);

  // Retourner des informations sur l'état des raccourcis
  return {
    keyboardShortcutsEnabled,
    hasSelection: state.selection.selectedElements.length > 0,
    canUndo: state.history.past.length > 0,
    canRedo: state.history.future.length > 0,
  };
};

