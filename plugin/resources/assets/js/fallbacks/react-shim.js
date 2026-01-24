/**
 * React Shim - Ensures all React hooks are available in the global scope
 * This must be loaded BEFORE the React bundle to make hooks available to Babel JSX
 */

if (typeof window !== 'undefined' && window.React) {
  debugLog('🔧 [react-shim] Setting up React shim for hooks...');
  
  // Get the original React object
  const OriginalReact = window.React;
  
  // Extract all hooks
  const {
    useState,
    useEffect,
    useRef,
    useCallback,
    useMemo,
    useContext,
    useReducer,
    useLayoutEffect,
    useId,
    useTransition,
    useDeferredValue,
    useImperativeHandle,
    useDebugValue,
    useSyncExternalStore,
    createElement,
    Fragment,
    cloneElement,
    isValidElement,
    createContext,
    forwardRef,
    lazy,
    Suspense,
    memo,
    createRef,
    Children,
    StrictMode,
    Profiler
  } = OriginalReact;
  
  // Create a new proxy object that intercepts all property access
  // This ensures hooks are always available even if they're called before index.js runs
  const ReactProxy = new Proxy(OriginalReact, {
    get: function(target, prop) {
      // Return the original property
      const value = Reflect.get(target, prop);
      
      // Log hook access for debugging
      if (typeof prop === 'string' && (prop.startsWith('use') || prop === 'createElement')) {
        if (!value) {
          // console.warn(`⚠️ [react-shim] Hook ${prop} is undefined on React object`, { target, value });
        }
      }
      
      return value;
    }
  });
  
  // Assign hook functions directly to the React object so they're available immediately
  window.React.useState = useState || (() => { throw new Error('useState not available'); });
  window.React.useEffect = useEffect || (() => { throw new Error('useEffect not available'); });
  window.React.useRef = useRef || (() => { throw new Error('useRef not available'); });
  window.React.useCallback = useCallback || (() => { throw new Error('useCallback not available'); });
  window.React.useMemo = useMemo || (() => { throw new Error('useMemo not available'); });
  window.React.useContext = useContext || (() => { throw new Error('useContext not available'); });
  window.React.useReducer = useReducer || (() => { throw new Error('useReducer not available'); });
  window.React.useLayoutEffect = useLayoutEffect || (() => { throw new Error('useLayoutEffect not available'); });
  window.React.useId = useId || (() => { throw new Error('useId not available'); });
  window.React.useTransition = useTransition || (() => { throw new Error('useTransition not available'); });
  window.React.useDeferredValue = useDeferredValue || (() => { throw new Error('useDeferredValue not available'); });
  window.React.useImperativeHandle = useImperativeHandle || (() => { throw new Error('useImperativeHandle not available'); });
  window.React.useDebugValue = useDebugValue || (() => { throw new Error('useDebugValue not available'); });
  window.React.useSyncExternalStore = useSyncExternalStore || (() => { throw new Error('useSyncExternalStore not available'); });
  
  // Also ensure other important React APIs are available
  window.React.createElement = createElement;
  window.React.Fragment = Fragment;
  window.React.cloneElement = cloneElement;
  window.React.isValidElement = isValidElement;
  window.React.createContext = createContext;
  window.React.forwardRef = forwardRef;
  window.React.lazy = lazy;
  window.React.Suspense = Suspense;
  window.React.memo = memo;
  window.React.createRef = createRef;
  window.React.Children = Children;
  window.React.StrictMode = StrictMode;
  window.React.Profiler = Profiler;
  
  debugLog('✅ [react-shim] React shim initialized, all hooks available on window.React');
  debugLog('✅ [react-shim] window.React.useRef:', typeof window.React.useRef);
  debugLog('✅ [react-shim] window.React.useState:', typeof window.React.useState);
  debugLog('✅ [react-shim] window.React.useEffect:', typeof window.React.useEffect);
}
