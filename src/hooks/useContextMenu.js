import { useState, useCallback, useEffect } from 'react';

export const useContextMenu = () => {
  const [contextMenu, setContextMenu] = useState(null);

  const showContextMenu = useCallback((x, y, items) => {
    setContextMenu({
      x,
      y,
      items
    });
  }, []);

  const hideContextMenu = useCallback(() => {
    setContextMenu(null);
  }, []);

  const handleContextMenuAction = useCallback((action) => {
    hideContextMenu();
    return action;
  }, [hideContextMenu]);

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (contextMenu && !e.target.closest('.context-menu')) {
        hideContextMenu();
      }
    };

    const handleEscape = (e) => {
      if (e.key === 'Escape' && contextMenu) {
        hideContextMenu();
      }
    };

    if (contextMenu) {
      document.addEventListener('mousedown', handleClickOutside);
      document.addEventListener('keydown', handleEscape);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleEscape);
    };
  }, [contextMenu, hideContextMenu]);

  return {
    contextMenu,
    showContextMenu,
    hideContextMenu,
    handleContextMenuAction
  };
};