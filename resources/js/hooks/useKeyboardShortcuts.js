import { useEffect } from 'react';

export const useKeyboardShortcuts = ({
  onDelete,
  onCopy,
  onPaste,
  onUndo,
  onRedo,
  onSave,
  onZoomIn,
  onZoomOut,
  onSelectAll,
  onDeselectAll,
  onToolSelect,
  onToggleGrid
}) => {
  useEffect(() => {
    const handleKeyDown = (e) => {
      // Ignorer si on est dans un champ de saisie
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.contentEditable === 'true') {
        return;
      }

      const isCtrl = e.ctrlKey || e.metaKey;

      switch (e.key.toLowerCase()) {
        case 'delete':
        case 'backspace':
          if (onDelete) {
            e.preventDefault();
            onDelete();
          }
          break;

        case 'c':
          if (isCtrl && onCopy) {
            e.preventDefault();
            onCopy();
          }
          break;

        case 'v':
          if (isCtrl && onPaste) {
            e.preventDefault();
            onPaste();
          }
          break;

        case 'z':
          if (isCtrl) {
            e.preventDefault();
            if (e.shiftKey && onRedo) {
              onRedo();
            } else if (onUndo) {
              onUndo();
            }
          }
          break;

        case 'y':
          if (isCtrl && onRedo) {
            e.preventDefault();
            onRedo();
          }
          break;

        case 's':
          if (isCtrl && onSave) {
            e.preventDefault();
            onSave();
          }
          break;

        case '+':
        case '=':
          if (isCtrl && onZoomIn) {
            e.preventDefault();
            onZoomIn();
          }
          break;

        case '-':
          if (isCtrl && onZoomOut) {
            e.preventDefault();
            onZoomOut();
          }
          break;

        case 'a':
          if (isCtrl && onSelectAll) {
            e.preventDefault();
            onSelectAll();
          }
          break;

        case 'd':
          if (isCtrl && onDeselectAll) {
            e.preventDefault();
            onDeselectAll();
          }
          break;

        case 'g':
          if (isCtrl && onToggleGrid) {
            e.preventDefault();
            onToggleGrid();
          }
          break;

        // Raccourcis pour les outils
        case 'v':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('select');
          }
          break;

        case 'r':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('rectangle');
          }
          break;

        case 't':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('text');
          }
          break;

        case 'i':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('image');
          }
          break;

        case 'l':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('line');
          }
          break;

        default:
          break;
      }
    };

    document.addEventListener('keydown', handleKeyDown);

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [onDelete, onCopy, onPaste, onUndo, onRedo, onSave, onZoomIn, onZoomOut, onSelectAll, onDeselectAll, onToolSelect, onToggleGrid]);
};
