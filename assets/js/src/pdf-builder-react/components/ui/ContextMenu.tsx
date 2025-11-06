import React, { useEffect, useRef, useMemo } from 'react';
import './ContextMenu.css';

export interface ContextMenuItem {
  id: string;
  label?: string;
  icon?: string;
  action?: () => void;
  disabled?: boolean;
  separator?: boolean;
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
  console.log('🎛️ ContextMenu render - isVisible:', isVisible, 'position:', position, 'items:', items);
  const menuRef = useRef<HTMLDivElement>(null);

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

      console.log('🎛️ ContextMenu - adjusted position:', { original: position, adjusted: { x: adjustedX, y: adjustedY } });
    }

    return { x: adjustedX, y: adjustedY };
  }, [position, items.length]);

  useEffect(() => {
    if (!isVisible) return;

    const handleClickOutside = (event: Event) => {
      // @ts-expect-error DOM event target type checking
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        onClose();
      }
    };

    const handleEscape = (event: Event) => {
      // @ts-expect-error Keyboard event key property
      if (event.key === 'Escape') {
        onClose();
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    document.addEventListener('keydown', handleEscape);

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleEscape);
    };
  }, [isVisible, onClose]);

  useEffect(() => {
    if (!isVisible) return;
    
    const timer = setTimeout(() => {
      console.log('🎛️ ContextMenu - DOM check after render:', menuRef.current);
      if (menuRef.current) {
        const rect = menuRef.current.getBoundingClientRect();
        console.log('🎛️ ContextMenu - DOM rect:', rect);
        console.log('🎛️ ContextMenu - is visible in viewport:', 
          rect.top >= 0 && 
          rect.left >= 0 && 
          rect.bottom <= window.innerHeight && 
          rect.right <= window.innerWidth
        );
      } else {
        console.log('🎛️ ContextMenu - NO DOM ELEMENT FOUND!');
      }
    }, 200);
    return () => clearTimeout(timer);
  }, [isVisible]);

  if (!isVisible) {
    console.log('🎛️ ContextMenu - not visible, returning null');
    return null;
  }

  console.log('🎛️ ContextMenu - rendering menu at:', adjustedPosition);

  const handleItemClick = (item: ContextMenuItem) => {
    if (!item.disabled && !item.separator && item.action) {
      console.log('🎛️ ContextMenu - item clicked:', item.label);
      item.action();
      onClose();
    }
  };

  return (
    <div
      ref={menuRef}
      className="context-menu"
      style={{
        left: `${adjustedPosition.x}px`,
        top: `${adjustedPosition.y}px`,
        background: 'red',
        border: '5px solid blue',
        zIndex: 99999,
        position: 'fixed',
        minWidth: '250px',
        padding: '15px',
        boxShadow: '0 0 20px rgba(0,0,0,0.8)',
        fontSize: '16px',
        fontWeight: 'bold'
      }}
    >
      <div style={{
        padding: '10px',
        background: 'yellow',
        marginBottom: '10px',
        fontSize: '12px',
        fontFamily: 'monospace'
      }}>
        🚨 DEBUG MENU 🚨<br/>
        Position: {adjustedPosition.x}, {adjustedPosition.y}<br/>
        Items: {items.length}<br/>
        Time: {new Date().toLocaleTimeString()}
      </div>
      {items.map((item, index) => (
        <div 
          key={item.id} 
          onClick={() => handleItemClick(item)}
          style={{
            padding: '10px',
            margin: '2px 0',
            backgroundColor: index % 2 === 0 ? '#ffcccc' : '#ffaaaa',
            border: '2px solid #ff0000',
            cursor: item.separator ? 'default' : 'pointer',
            fontSize: '14px',
            display: item.separator ? 'none' : 'block'
          }}
        >
          {item.icon} {item.label}
        </div>
      ))}
    </div>
  );
};