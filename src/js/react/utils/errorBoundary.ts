/**
 * Error Boundary Utility
 * Gracefully handles errors in React components
 */

export interface ErrorBoundaryOptions {
  onError?: (error: Error, errorInfo: React.ErrorInfo) => void;
  fallback?: React.ReactNode;
}

/**
 * Safe React initialization wrapper
 * Catches and logs initialization errors
 */
export const safeInitialize = async <T>(
  initFn: () => Promise<T>,
  logger: any,
  errorLabel: string
): Promise<T | null> => {
  try {
    logger.info(`Initializing ${errorLabel}...`);
    const result = await initFn();
    logger.info(`✅ ${errorLabel} initialized successfully`);
    return result;
  } catch (error) {
    logger.error(`❌ Failed to initialize ${errorLabel}:`, error);
    return null;
  }
};

export default safeInitialize;


