import { useState, useEffect, useCallback, useMemo, memo, useRef } from 'react';

// Composant Accordion pour organiser les propriétés
const Accordion = memo(({ title, icon, children, defaultOpen = false, className = '' }) => {
  const [isOpen, setIsOpen] = useState(defaultOpen);
  const [height, setHeight] = useState(defaultOpen ? 'auto' : '0px');
  const contentRef = useRef(null);
  const contentId = useMemo(() => `accordion-content-${Math.random().toString(36).substr(2, 9)}`, []);

  const toggleAccordion = useCallback(() => {
    if (isOpen) {
      // Fermer
      setHeight(`${contentRef.current.scrollHeight}px`);
      setTimeout(() => setHeight('0px'), 10);
      setTimeout(() => setIsOpen(false), 310);
    } else {
      // Ouvrir
      setIsOpen(true);
      setTimeout(() => setHeight(`${contentRef.current.scrollHeight}px`), 10);
      setTimeout(() => setHeight('auto'), 310);
    }
  }, [isOpen]);

  const handleKeyDown = useCallback((e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      toggleAccordion();
    }
  }, [toggleAccordion]);

  return (
    <div className={`accordion ${className}`}>
      <button
        className="accordion-header"
        onClick={toggleAccordion}
        onKeyDown={handleKeyDown}
        type="button"
        aria-expanded={isOpen}
        aria-controls={contentId}
      >
        <span className="accordion-title">
          {icon && <span className="accordion-icon">{icon}</span>}
          {title}
        </span>
        <span className={`accordion-arrow ${isOpen ? 'open' : ''}`}>
          ▶
        </span>
      </button>
      <div
        ref={contentRef}
        id={contentId}
        className={`accordion-content ${isOpen ? 'open' : ''}`}
        style={{ height }}
        role="region"
        aria-labelledby={`accordion-header-${contentId}`}
      >
        {children}
      </div>
    </div>
  );
});

export default Accordion;