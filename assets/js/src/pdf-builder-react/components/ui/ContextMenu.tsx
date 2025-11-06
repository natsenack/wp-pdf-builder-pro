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
      console.log('ContextMenu: Click outside detected, closing menu');
      // Vérifier que l'événement n'est pas un clic droit (contextmenu)
      if ('button' in event && event.button === 2) return;

      if (menuRef.current && !(menuRef.current as HTMLElement).contains(event.target as HTMLElement)) {
        console.log('ContextMenu: Click was outside menu, calling onClose');
        onClose();
      } else {
        console.log('ContextMenu: Click was inside menu, keeping open');
      }
    };

    const handleEscape = (event: Event) => {
      console.log('ContextMenu: Escape key pressed, closing menu');
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
      const element = menuRef.current as HTMLElement;
      console.log('ContextMenu: Menu rendered successfully at position:', element.getBoundingClientRect());
    }
  }, [isVisible]);

  if (!isVisible) {
    console.log('ContextMenu not rendering - isVisible is false');
    return null;
  }

  console.log('ContextMenu rendering at position:', adjustedPosition);

  const handleItemClick = (item: ContextMenuItem) => {
    console.log('ContextMenu: Item clicked:', item.id, item.label);
    if (!item.disabled && !item.separator && item.action) {
      console.log('ContextMenu: Executing action for:', item.id);
      item.action();
      onClose();
    } else {
      console.log('ContextMenu: Item disabled or no action:', item.disabled, !!item.action);
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
        background: '#ff0000', // DEBUG: Bright red background
        border: '3px solid #000',
        borderRadius: '8px',
        padding: '10px',
        minWidth: '200px',
        color: '#fff',
        fontSize: '14px',
        fontWeight: 'bold',
      }}
    >
      <div style={{marginBottom: '10px', fontSize: '16px'}}>🚨 MENU DEBUG 🚨</div>
      {items.map((item) => (
        <div key={item.id}>
          {item.section && (
            <div style={{
              fontSize: '12px',
              fontWeight: 'bold',
              color: '#ffff00',
              marginTop: '8px',
              marginBottom: '4px',
              textTransform: 'uppercase'
            }}>
              {item.section}
            </div>
          )}
          {item.separator ? (
            <div style={{
              height: '2px',
              background: '#fff',
              margin: '4px 0'
            }}></div>
          ) : (
            <div
              className={`context-menu-item ${item.disabled ? 'disabled' : ''}`}
              onClick={() => handleItemClick(item)}
              style={{
                padding: '8px 12px',
                margin: '2px 0',
                background: item.disabled ? '#666' : '#cc0000',
                borderRadius: '4px',
                cursor: item.disabled ? 'not-allowed' : 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                border: '1px solid #fff'
              }}
            >
              <span>
                {item.icon && <span style={{marginRight: '8px'}}>{item.icon}</span>}
                {item.label && <span>{item.label}</span>}
              </span>
              {item.shortcut && <span style={{fontSize: '11px', opacity: 0.8}}>{item.shortcut}</span>}
            </div>
          )}
        </div>
      ))}
    </div>
  );

  // Utiliser un Portal pour rendre le menu au niveau du document body
  console.log('ContextMenu: About to create portal with', items.length, 'items');
  return createPortal(menuElement, document.body);
};