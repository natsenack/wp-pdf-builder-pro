import { useState, useEffect, useCallback, useMemo, memo } from 'react';

// Composant Accordion pour organiser les propriétés
const Accordion = memo(({ title, icon, children, defaultOpen = false, className = '' }) => {
  const [isOpen, setIsOpen] = useState(defaultOpen);
  const contentId = useMemo(() => `accordion-content-${Math.random().toString(36).substr(2, 9)}`, []);

  const toggleAccordion = useCallback(() => {
    setIsOpen(prev => !prev);
  }, []);

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
        id={contentId}
        className={`accordion-content ${isOpen ? 'open' : ''}`}
        role="region"
        aria-labelledby={`accordion-header-${contentId}`}
      >
        {children}
      </div>
    </div>
  );
});

export default Accordion;