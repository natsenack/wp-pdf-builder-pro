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
        background: 'linear-gradient(145deg, #ffffff 0%, #f8fafc 100%)',
        border: '1px solid #e2e8f0',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04)',
        minWidth: '120px',
        maxWidth: '160px',
        padding: '1px 0',
        animation: 'contextMenuFadeIn 0.15s cubic-bezier(0.4, 0, 0.2, 1)',
        transformOrigin: 'top left',
        // Ajouter l'animation CSS inline
        WebkitAnimation: 'contextMenuFadeIn 0.15s cubic-bezier(0.4, 0, 0.2, 1)',
        MozAnimation: 'contextMenuFadeIn 0.15s cubic-bezier(0.4, 0, 0.2, 1)',
        OAnimation: 'contextMenuFadeIn 0.15s cubic-bezier(0.4, 0, 0.2, 1)',
        // Définir les keyframes inline
        animationName: 'contextMenuFadeIn',
        animationDuration: '0.15s',
        animationTimingFunction: 'cubic-bezier(0.4, 0, 0.2, 1)',
      }}
    >
      {items.map((item) => (
        <div key={item.id}>
          {item.section && (
            <div className="context-menu-section" style={{padding: '0px 0'}}>
              <div className="context-menu-section-title" style={{fontSize: '8px', fontWeight: '600', color: '#64748b', textTransform: 'uppercase', letterSpacing: '0.5px', padding: '0px 6px 0px', marginBottom: '0px'}}>{item.section}</div>
            </div>
          )}
          {item.separator ? (
            <div className="context-menu-separator" style={{height: '1px', background: 'linear-gradient(90deg, transparent 0%, #e2e8f0 20%, #e2e8f0 80%, transparent 100%)', margin: '0px 0', border: 'none'}}></div>
          ) : (
            <div
              className={`context-menu-item ${item.disabled ? 'disabled' : ''}`}
              onClick={() => handleItemClick(item)}
              style={{
                display: 'flex',
                alignItems: 'center',
                padding: '1px 4px',
                cursor: item.disabled ? 'not-allowed' : 'pointer',
                userSelect: 'none',
                transition: 'all 0.15s cubic-bezier(0.4, 0, 0.2, 1)',
                position: 'relative',
                minHeight: '16px',
                border: 'none',
                background: 'transparent',
                color: item.disabled ? '#94a3b8' : '#334155',
                fontSize: '11px',
                fontWeight: '500',
              }}
            >
              {item.icon && <span className="context-menu-item-icon" style={{width: '10px', height: '10px', marginRight: '3px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#64748b', fontSize: '9px'}}>{item.icon}</span>}
              {item.label && <span className="context-menu-item-text" style={{flex: '1', fontSize: '10px', fontWeight: '500', color: '#334155'}}>{item.label}</span>}
              {item.shortcut && <span className="context-menu-item-shortcut" style={{fontSize: '8px', fontWeight: '500', color: '#64748b', background: 'rgba(148, 163, 184, 0.1)', padding: '0px 1px', borderRadius: '1px', marginLeft: '3px'}}>{item.shortcut}</span>}
            </div>
          )}
        </div>
      ))}
    </div>
  );

  // Utiliser un Portal pour rendre le menu au niveau du document body
  return createPortal(menuElement, document.body);
};