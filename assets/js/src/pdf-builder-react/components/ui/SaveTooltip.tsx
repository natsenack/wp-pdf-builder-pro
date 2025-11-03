import React, { useState } from 'react';
import './SaveTooltip.css';

export interface SaveTooltipProps {
  state: 'idle' | 'saving' | 'saved' | 'error';
  lastSavedAt?: string | null;
  error?: string | null;
  progress?: number;
  onSaveNow?: () => void;
}

export const SaveTooltip: React.FC<SaveTooltipProps> = ({
  state,
  lastSavedAt,
  error,
  progress = 0,
  onSaveNow
}) => {
  const [isHovering, setIsHovering] = useState(false);

  const getLabel = () => {
    switch (state) {
      case 'saving':
        return `Sauvegarde... ${Math.round(progress)}%`;
      case 'saved':
        return `Sauvegardé ${lastSavedAt ? new Date(lastSavedAt).toLocaleTimeString('fr-FR', {
          hour: '2-digit',
          minute: '2-digit'
        }) : ''}`;
      case 'error':
        return `Erreur: ${error || 'Inconnue'}`;
      default:
        return lastSavedAt ? `Dernière sauvegarde: ${new Date(lastSavedAt).toLocaleTimeString('fr-FR', {
          hour: '2-digit',
          minute: '2-digit'
        })}` : 'Prêt';
    }
  };

  return (
    <div
      className={`save-tooltip save-tooltip--${state}`}
      onMouseEnter={() => setIsHovering(true)}
      onMouseLeave={() => setIsHovering(false)}
    >
      {/* Indicateur (petit point/icône) */}
      <div className="save-tooltip__indicator">
        {state === 'saving' && (
          <div className="save-tooltip__spinner">
            <div className="save-tooltip__spinner-dot"></div>
          </div>
        )}
        {state === 'saved' && (
          <svg className="save-tooltip__icon" width="16" height="16" viewBox="0 0 20 20" fill="none">
            <path d="M17.25 5.25L8.75 13.75L2.75 7.75" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        )}
        {state === 'error' && (
          <svg className="save-tooltip__icon" width="16" height="16" viewBox="0 0 20 20" fill="none">
            <circle cx="10" cy="10" r="9" stroke="currentColor" strokeWidth="2" />
            <path d="M10 6V10M10 14H10.01" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
          </svg>
        )}
        {state === 'idle' && (
          <svg className="save-tooltip__icon" width="16" height="16" viewBox="0 0 20 20" fill="none">
            <path d="M5 10H15M10 5V15" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
          </svg>
        )}
      </div>

      {/* Tooltip */}
      {isHovering && (
        <div className="save-tooltip__popup">
          <div className="save-tooltip__label">{getLabel()}</div>
          
          {state === 'saving' && (
            <div className="save-tooltip__progress-bar">
              <div
                className="save-tooltip__progress-fill"
                style={{ width: `${Math.min(100, Math.max(0, progress))}%` }}
              />
            </div>
          )}

          {state === 'idle' && (
            <button
              className="save-tooltip__button"
              onClick={onSaveNow}
              title="Sauvegarder maintenant"
            >
              Sauvegarder maintenant
            </button>
          )}
        </div>
      )}
    </div>
  );
};

export default SaveTooltip;
