/**
 * Logger utility - Simple, clean logging for debugging
 */

type LogLevel = 'debug' | 'info' | 'warn' | 'error';

const createLogger = (module: string) => {
  const prefix = `[${module}]`;
  
  return {
    debug: (...args: any[]) => console.log(`${prefix} [DEBUG]`, ...args),
    info: (...args: any[]) => console.log(`${prefix} [INFO]`, ...args),
    warn: (...args: any[]) => console.warn(`${prefix} [WARN]`, ...args),
    error: (...args: any[]) => console.error(`${prefix} [ERROR]`, ...args),
  };
};

export default createLogger;
