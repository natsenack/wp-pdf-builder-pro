import React from 'react';

/**
 * Error fallback component
 */
interface ErrorFallbackProps {
  error: Error;
  resetError: () => void;
}

export const ErrorFallback: React.FC<ErrorFallbackProps> = ({ error, resetError }) => {
  return (
    <div className="pdf-builder-error">
      <h2>❌ Error Loading PDF Builder</h2>
      <details style={{ whiteSpace: 'pre-wrap', marginTop: '10px' }}>
        <summary>Error Details</summary>
        <code>{error.message}</code>
      </details>
      <button onClick={resetError} style={{ marginTop: '10px' }}>
        Retry
      </button>
    </div>
  );
};

export default ErrorFallback;


