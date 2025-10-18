import { useState, useCallback, useEffect } from 'react';

export const useContextMenu = () => {
  const [contextMenu, setContextMenu] = useState(null);
  const [isAnimating, setIsAnimating] = useState(false);

  const showContextMenu = useCallback((x, y, items) => {
    // Ajuster la position pour éviter que le menu sorte de l'écran
    const menuWidth = 180; // Largeur approximative du menu
    const menuHeight = items.length * 36; // Hauteur approximative

    let adjustedX = x;
    let adjustedY = y;

    // Ajuster horizontalement
    if (x + menuWidth > window.innerWidth) {
      adjustedX = x - menuWidth;
    }

    // Ajuster verticalement
    if (y + menuHeight > window.innerHeight) {
      adjustedY = y - menuHeight;
    }

    // S'assurer que le menu reste dans les limites
    adjustedX = Math.max(0, Math.min(adjustedX, window.innerWidth - menuWidth));
    adjustedY = Math.max(0, Math.min(adjustedY, window.innerHeight - menuHeight));

    setContextMenu({
      x: adjustedX,
      y: adjustedY,
      items
    });
  }, []);

  const hideContextMenu = useCallback(() => {
    if (contextMenu) {
      setIsAnimating(true);
      // Attendre la fin de l'animation avant de masquer complètement
      setTimeout(() => {
        setContextMenu(null);
        setIsAnimating(false);
      }, 150); // Durée de l'animation
    }
  }, [contextMenu]);

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
    handleContextMenuAction,
    isAnimating
  };
};
