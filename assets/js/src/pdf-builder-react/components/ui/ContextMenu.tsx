import React, { useEffect, useRef, useMemo } from 'react';
import { createPortal } from 'react-dom';
import './ContextMenu.css';

export interface ContextMenuItem {
  id: string;
  label?: string;
  icon?: string;
  shortcut?: string;
  action?: () => void;
  disabled?: boolean;
  separator?: boolean;
  section?: string;
}

interface ContextMenuProps {
  items: ContextMenuItem[];
  position: { x: number; y: number };
  onClose: () => void;
  isVisible: boolean;
}

export const ContextMenu: React.FC<ContextMenuProps> = ({
  items,
  position,
  onClose,
  isVisible
}) => {
  const menuRef = useRef(null);

  // Calculer la position corrigée pour garder le menu à l'écran
  const adjustedPosition = useMemo(() => {
    let adjustedX = position.x;
    let adjustedY = position.y;

    if (typeof window !== 'undefined') {
      // Largeur et hauteur estimées du menu
      const menuWidth = 250;
      const menuHeight = items.length * 40 + 50; // Estimation

      // Vérifier si le menu sort à droite
      if (adjustedX + menuWidth > window.innerWidth) {
        adjustedX = window.innerWidth - menuWidth - 10;
      }

      // Vérifier si le menu sort en bas
      if (adjustedY + menuHeight > window.innerHeight) {
        adjustedY = window.innerHeight - menuHeight - 10;
      }

      // Vérifier les limites à gauche et haut
      if (adjustedX < 0) adjustedX = 5;
      if (adjustedY < 0) adjustedY = 5;
    }

    return { x: adjustedX, y: adjustedY };
  }, [position, items.length]);

  useEffect(() => {
    if (!isVisible) return;

    const handleClickOutside = (event: React.MouseEvent | Event) => {
      // Vérifier que l'événement n'est pas un clic droit (contextmenu)
      if ('button' in event && event.button === 2) return;

      if (menuRef.current && !(menuRef.current as HTMLElement).contains(event.target as HTMLElement)) {
        onClose();
      }
    };

    const handleEscape = (event: Event) => {
      // @ts-expect-error Keyboard event key property
      if (event.key === 'Escape') {
        onClose();
      }
    };

    // Délai pour éviter que le clic droit qui ouvre le menu ne le ferme immédiatement
    const timer = setTimeout(() => {
      document.addEventListener('mousedown', handleClickOutside);
      document.addEventListener('keydown', handleEscape);
    }, 100);

    return () => {
      clearTimeout(timer);
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleEscape);
    };
  }, [isVisible, onClose]);

  useEffect(() => {
    if (!isVisible) return;
    
    // Petit délai pour permettre au DOM de se stabiliser
    const timer = setTimeout(() => {
      // Pour l'instant, pas d'action spécifique après le rendu
    }, 200);
    return () => clearTimeout(timer);
  }, [isVisible]);

  useEffect(() => {
    if (isVisible && menuRef.current) {
      // Menu rendu avec succès - pas besoin de log en production
    }
  }, [isVisible]);

  if (!isVisible) {
    return null;
  }

  const handleItemClick = (item: ContextMenuItem) => {
    if (!item.disabled && !item.separator && item.action) {
      item.action();
      onClose();
    }
  };

  const menuElement = (
    <div
      ref={menuRef}
      className="context-menu"
      style={{
        position: 'fixed',
        left: `${adjustedPosition.x}px`,
        top: `${adjustedPosition.y}px`,
        opacity: 1,
        visibility: 'visible',
        pointerEvents: 'auto',
        zIndex: 999999,
      }}
    >
      {items.map((item) => (
        <div key={item.id}>
          {item.section && (
            <div className="context-menu-section">
              <div className="context-menu-section-title">{item.section}</div>
            </div>
          )}
          {item.separator ? (
            <div className="context-menu-separator"></div>
          ) : (
            <div
              className={`context-menu-item ${item.disabled ? 'disabled' : ''}`}
              onClick={() => handleItemClick(item)}
            >
              {item.icon && <span className="context-menu-item-icon">{item.icon}</span>}
              {item.label && <span className="context-menu-item-text">{item.label}</span>}
              {item.shortcut && <span className="context-menu-item-shortcut">{item.shortcut}</span>}
            </div>
          )}
        </div>
      ))}
    </div>
  );

  // Utiliser un Portal pour rendre le menu au niveau du document body
  return createPortal(menuElement, document.body);
};