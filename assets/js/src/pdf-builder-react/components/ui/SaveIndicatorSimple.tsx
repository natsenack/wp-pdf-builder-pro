import React from 'react';

export interface SaveIndicatorProps {
  state: 'idle' | 'saving' | 'saved' | 'error';
  lastSavedAt?: string | null;
  error?: string | null;
  onRetry?: () => void;
  progress?: number;
  showProgressBar?: boolean;
}

/**
 * SaveIndicator Simple et Robuste
 * Affiche UNE notification simple en haut à droite
 */
export const SaveIndicator: React.FC<SaveIndicatorProps> = ({
  state,
  lastSavedAt,
  error,
  onRetry,
  progress = 0,
  showProgressBar = false
}) => {
  // Ne rien afficher si idle
  if (state === 'idle') {
    return null;
  }

  // Couleurs par state
  const colors: Record<string, { bg: string; text: string; border: string }> = {
    saving: { bg: '#2196F3', text: '#fff', border: '#1976D2' },
    saved: { bg: '#4CAF50', text: '#fff', border: '#388E3C' },
    error: { bg: '#F44336', text: '#fff', border: '#D32F2F' }
  };

  const color = colors[state];

  // Message
  const getMessage = (): string => {
    switch (state) {
      case 'saving':
        return `Sauvegarde en cours${progress > 0 ? ` (${Math.round(progress)}%)` : ''}...`;
      case 'saved':
        return 'Sauvegardé ✓';
      case 'error':
        return `Erreur: ${error || 'inconnue'}`;
      default:
        return '';
    }
  };

  return (
    <div
      style={{
        position: 'fixed',
        top: '50px',
        right: '20px',
        padding: '14px 20px',
        borderRadius: '6px',
        background: color.bg,
        border: `2px solid ${color.border}`,
        boxShadow: '0 4px 16px rgba(0, 0, 0, 0.3)',
        fontSize: '14px',
        fontWeight: 'bold',
        fontFamily: 'Arial, sans-serif',
        color: color.text,
        zIndex: 999999,
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        minWidth: '200px',
        animation: state === 'saving' ? 'pulse 2s infinite' : 'slideIn 0.3s ease-out'
      }}
    >
      {state === 'saving' && (
        <div style={{ fontSize: '16px', animation: 'spin 1s linear infinite' }}>⟳</div>
      )}
      {state === 'saved' && <div style={{ fontSize: '16px' }}>✓</div>}
      {state === 'error' && <div style={{ fontSize: '16px' }}>!</div>}
      
      <span>{getMessage()}</span>

      {state === 'error' && onRetry && (
        <button
          onClick={onRetry}
          style={{
            marginLeft: 'auto',
            padding: '4px 8px',
            background: 'rgba(255,255,255,0.2)',
            border: '1px solid rgba(255,255,255,0.4)',
            color: '#fff',
            borderRadius: '3px',
            cursor: 'pointer',
            fontSize: '12px',
            fontWeight: 'bold'
          }}
        >
          Réessayer
        </button>
      )}

      <style>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
        @keyframes pulse {
          0%, 100% { opacity: 1; }
          50% { opacity: 0.7; }
        }
        @keyframes slideIn {
          from {
            transform: translateX(100px);
            opacity: 0;
          }
          to {
            transform: translateX(0);
            opacity: 1;
          }
        }
      `}</style>
    </div>
  );
};

export default SaveIndicator;
