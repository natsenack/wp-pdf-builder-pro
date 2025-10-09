import { useEffect } from 'react';

export const useKeyboardShortcuts = ({
  onDelete,
  onCopy,
  onPaste,
  onUndo,
  onRedo,
  onSave,
  onZoomIn,
  onZoomOut
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

        default:
          break;
      }
    };

    document.addEventListener('keydown', handleKeyDown);

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [onDelete, onCopy, onPaste, onUndo, onRedo, onSave, onZoomIn, onZoomOut]);
};