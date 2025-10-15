import React, { useEffect, useRef } from 'react';

const ContextMenu = ({ menu, onAction, isAnimating = false, onClose }) => {
  const menuRef = useRef(null);

  // Fermer le menu quand on clique ailleurs
  useEffect(() => {
    const handleClickOutside = (e) => {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        if (onClose) {
          onClose();
        }
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [onClose]);

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
        borderRadius: '6px',
        boxShadow: '0 8px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.08)',
        zIndex: 1000,
        minWidth: '200px',
        padding: '4px 0',
        animation: isAnimating
          ? 'contextMenuFadeOut 0.15s cubic-bezier(0.4, 0, 1, 1)'
          : 'contextMenuFadeIn 0.15s cubic-bezier(0.16, 1, 0.3, 1)',
        transformOrigin: 'top left'
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
                justifyContent: 'space-between',
                width: '100%',
                padding: '10px 16px',
                border: 'none',
                backgroundColor: 'transparent',
                cursor: item.disabled ? 'not-allowed' : 'pointer',
                fontSize: '14px',
                textAlign: 'left',
                borderRadius: '0',
                opacity: item.disabled ? 0.5 : 1,
                transition: 'background-color 0.15s ease, color 0.15s ease'
              }}
              onMouseEnter={(e) => {
                if (!item.disabled) {
                  e.target.style.backgroundColor = '#f8f9fa';
                  e.target.style.color = '#1e293b';
                }
              }}
              onMouseLeave={(e) => {
                if (!item.disabled) {
                  e.target.style.backgroundColor = 'transparent';
                  e.target.style.color = 'inherit';
                }
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center' }}>
                <span style={{ marginRight: '8px' }}>
                  {item.icon || getDefaultIcon(item.label)}
                </span>
                {item.label}
              </div>
              {getKeyboardShortcut(item.label) && (
                <span style={{
                  fontSize: '12px',
                  color: '#666',
                  marginLeft: '16px'
                }}>
                  {getKeyboardShortcut(item.label)}
                </span>
              )}
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
    'Copier': '[C]',
    'Coller': '[V]',
    'Annuler': '↶',
    'Rétablir': '↷',
    'Tout sélectionner': '☑️',
    'Désélectionner': '☐'
  };

  return iconMap[label] || '•';
};

// Fonction utilitaire pour obtenir un raccourci clavier selon le label
const getKeyboardShortcut = (label) => {
  const shortcutMap = {
    'Copier': 'Ctrl+C',
    'Coller': 'Ctrl+V',
    'Dupliquer': 'Ctrl+D',
    'Supprimer': 'Del',
    'Annuler': 'Ctrl+Z',
    'Rétablir': 'Ctrl+Y',
    'Tout sélectionner': 'Ctrl+A',
    'Désélectionner': 'Esc'
  };

  return shortcutMap[label];
};

export default ContextMenu;