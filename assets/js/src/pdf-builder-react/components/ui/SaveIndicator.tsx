import React, { useEffect, useState, useRef } from 'react';
import './SaveIndicator.css';

/**
 * Composant SaveIndicator
 * Affiche l'état de la sauvegarde automatique avec feedback visuel discret
 * 
 * States:
 * - idle: Pas de sauvegarde en cours
 * - saving: Sauvegarde en cours (spinner)
 * - saved: Sauvegarde réussie (checkmark, durée 2s)
 * - error: Erreur de sauvegarde (exclamation)
 */

export interface SaveIndicatorProps {
  state: 'idle' | 'saving' | 'saved' | 'error';
  lastSavedAt?: string | null;
  error?: string | null;
  onRetry?: () => void;
  progress?: number;
  showProgressBar?: boolean;
}

export const SaveIndicator: React.FC<SaveIndicatorProps> = ({
  state,
  lastSavedAt,
  error,
  onRetry,
  progress = 0,
  showProgressBar = false
}) => {
  const [visible, setVisible] = useState(false);
  const autoHideTimerRef = useRef<NodeJS.Timeout | null>(null);

  // Gérer la visibilité automatique
  useEffect(() => {
    if (state === 'idle') {
      // Masquer immédiatement quand on revient à idle
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setVisible(_prev => false);
      if (autoHideTimerRef.current) {
        clearTimeout(autoHideTimerRef.current);
        autoHideTimerRef.current = null;
      }
    } else {
      setVisible(true);

      // Masquer automatiquement après 2 secondes pour 'saved', 3 secondes pour 'error', 10 secondes pour 'saving'
      if (autoHideTimerRef.current) clearTimeout(autoHideTimerRef.current);
      const hideDelay = state === 'saved' ? 2000 : state === 'error' ? 3000 : 10000;
      const timer = setTimeout(() => {
        setVisible(false);
      }, hideDelay);
      autoHideTimerRef.current = timer;
    }

    return () => {
      if (autoHideTimerRef.current) clearTimeout(autoHideTimerRef.current);
    };
  }, [state]);

  if (!visible) {
    return null;
  }

  const getTitle = (): string => {
    switch (state) {
      case 'saving':
        return 'Sauvegarde en cours...';
      case 'saved':
        return lastSavedAt
          ? `Sauvegardé le ${new Date(lastSavedAt).toLocaleTimeString('fr-FR', {
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit'
            })}`
          : 'Sauvegardé';
      case 'error':
        return error || 'Erreur de sauvegarde';
      default:
        return '';
    }
  };

  return (
    <>
      <div
        className={`save-indicator save-indicator--${state}`}
        title={getTitle()}
        aria-label={getTitle()}
        style={{
          position: 'fixed',
          top: '50px',
          right: '20px',
          padding: '12px 16px',
          borderRadius: '8px',
          background: state === 'saving' ? '#f0f6fc' : state === 'saved' ? '#f0fdf4' : state === 'error' ? '#fef2f2' : '#fff',
          border: state === 'saving' ? '1px solid #0073aa' : state === 'saved' ? '1px solid #46b450' : state === 'error' ? '1px solid #dc2626' : '1px solid #e0e0e0',
          boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
          fontSize: '13px',
          fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
          zIndex: 999999,
          display: 'flex',
          alignItems: 'center',
          gap: '8px',
          maxWidth: '280px',
          animation: 'slideInDown 0.3s ease-out forwards'
        }}
      >
      <div className="save-indicator__content">
        {/* Spinner pour 'saving' */}
        {state === 'saving' && (
          <div className="save-indicator__spinner">
            <div className="save-indicator__spinner-dot save-indicator__spinner-dot--1"></div>
            <div className="save-indicator__spinner-dot save-indicator__spinner-dot--2"></div>
            <div className="save-indicator__spinner-dot save-indicator__spinner-dot--3"></div>
          </div>
        )}

        {/* Barre de progression pour 'saving' */}
        {state === 'saving' && showProgressBar && (
          <div className="save-indicator__progress-bar">
            <div
              className="save-indicator__progress-fill"
              style={{ width: `${Math.min(100, Math.max(0, progress))}%` }}
            />
          </div>
        )}

        {/* Checkmark pour 'saved' */}
        {state === 'saved' && (
          <svg
            className="save-indicator__icon"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M17.25 5.25L8.75 13.75L2.75 7.75"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </svg>
        )}

        {/* Exclamation pour 'error' */}
        {state === 'error' && (
          <svg
            className="save-indicator__icon"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle cx="10" cy="10" r="9" stroke="currentColor" strokeWidth="2" />
            <path
              d="M10 6V10M10 14H10.01"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </svg>
        )}

        {/* Text label */}
        <span className="save-indicator__text">
          {state === 'saving' && 'Sauvegarde...'}
          {state === 'saved' && 'Sauvegardé'}
          {state === 'error' && 'Erreur'}
        </span>

        {/* Bouton retry pour les erreurs */}
        {state === 'error' && onRetry && (
          <button
            className="save-indicator__retry-btn"
            onClick={onRetry}
            title="Réessayer"
            aria-label="Réessayer la sauvegarde"
          >
            ↻
          </button>
        )}
      </div>

      {/* Message d'erreur détaillé */}
      {state === 'error' && error && (
        <div className="save-indicator__error-message">{error}</div>
      )}
      </div>
    </>
  );
};

export default SaveIndicator;
