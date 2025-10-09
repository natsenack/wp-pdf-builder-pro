import React, { useEffect, useRef } from 'react';

export const ContextMenu = ({ menu, onAction }) => {
  const menuRef = useRef(null);

  // Fermer le menu quand on clique ailleurs
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        // Le menu se ferme automatiquement via le hook useContextMenu
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Gestionnaire d'action
  const handleAction = (item) => {
    if (item.action && typeof item.action === 'function') {
      item.action();
    } else if (onAction) {
      onAction(item.action);
    }
  };

  if (!menu) return null;

  return (
    <div
      ref={menuRef}
      className="context-menu"
      style={{
        position: 'fixed',
        left: menu.x,
        top: menu.y,
        backgroundColor: 'white',
        border: '1px solid #ccc',
        borderRadius: '4px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: 1000,
        minWidth: '180px',
        padding: '4px 0'
      }}
    >
      {menu.items.map((item, index) => (
        <div key={index}>
          {item.type === 'separator' ? (
            <div
              style={{
                height: '1px',
                backgroundColor: '#f8f9fa',
                margin: '4px 0'
              }}
            />
          ) : (
            <button
              className="context-menu-item"
              onClick={() => handleAction(item)}
              disabled={item.disabled}
              style={{
                display: 'flex',
                alignItems: 'center',
                width: '100%',
                padding: '8px 16px',
                border: 'none',
                backgroundColor: 'transparent',
                cursor: item.disabled ? 'not-allowed' : 'pointer',
                fontSize: '14px',
                textAlign: 'left',
                borderRadius: '0',
                opacity: item.disabled ? 0.5 : 1
              }}
              onMouseEnter={(e) => {
                if (!item.disabled) e.target.style.backgroundColor = '#f1f3f4';
              }}
              onMouseLeave={(e) => {
                if (!item.disabled) e.target.style.backgroundColor = 'transparent';
              }}
            >
              <span style={{ marginRight: '8px' }}>
                {item.icon || getDefaultIcon(item.label)}
              </span>
              {item.label}
            </button>
          )}
        </div>
      ))}
    </div>
  );
};

// Fonction utilitaire pour obtenir une icône par défaut selon le label
const getDefaultIcon = (label) => {
  const iconMap = {
    'Dupliquer': '📋',
    'Supprimer': '🗑️',
    'Copier': '📄',
    'Coller': '📄',
    'Annuler': '↶',
    'Rétablir': '↷'
  };

  return iconMap[label] || '•';
};