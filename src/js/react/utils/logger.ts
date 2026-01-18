/**
 * Logger utility - Disabled for production
 */

type LogLevel = 'debug' | 'info' | 'warn' | 'error';

const createLogger = (module: string) => {
  return {
    debug: (...args: any[]) => {},
    info: (...args: any[]) => {},
    warn: (...args: any[]) => {},
    error: (...args: any[]) => {},
  };
};

export default createLogger;


