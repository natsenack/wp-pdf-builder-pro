import React, { useEffect, useState } from 'react';
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
  position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left';
  progress?: number;
  showProgressBar?: boolean;
}

export const SaveIndicator: React.FC<SaveIndicatorProps> = ({
  state,
  lastSavedAt,
  error,
  onRetry,
  position = 'top-right',
  progress = 0,
  showProgressBar = false
}) => {
  const [visible, setVisible] = useState(false);
  const [autoHideTimer, setAutoHideTimer] = useState<NodeJS.Timeout | null>(null);

  // Gérer la visibilité automatique
  useEffect(() => {
    if (state === 'idle') {
      // Masquer immédiatement quand on revient à idle
      setVisible(false);
      if (autoHideTimer) {
        clearTimeout(autoHideTimer);
        setAutoHideTimer(null);
      }
    } else {
      setVisible(true);

      // Masquer automatiquement après 2 secondes pour 'saved', 3 secondes pour 'error', 10 secondes pour 'saving'
      if (autoHideTimer) clearTimeout(autoHideTimer);
      const hideDelay = state === 'saved' ? 2000 : state === 'error' ? 3000 : 10000;
      const timer = setTimeout(() => {
        setVisible(false);
      }, hideDelay);
      setAutoHideTimer(timer);
    }

    return () => {
      if (autoHideTimer) clearTimeout(autoHideTimer);
    };
  }, [state, autoHideTimer]);

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
    <div
      className={`save-indicator save-indicator--${state} save-indicator--${position}`}
      title={getTitle()}
      aria-label={getTitle()}
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
  );
};

export default SaveIndicator;
