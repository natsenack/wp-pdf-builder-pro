/**
 * DOM utilities for React integration with WordPress
 */

export const getDOMContainer = (containerId: string): HTMLElement | null => {
  const container = document.getElementById(containerId);
  if (!container) {
    
    return null;
  }
  return container;
};

export const waitForDOM = (): Promise<Document> => {
  return new Promise((resolve) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => resolve(document), { once: true });
    } else {
      resolve(document);
    }
  });
};

export const createErrorElement = (message: string): HTMLElement => {
  const div = document.createElement('div');
  div.style.cssText = `
    padding: 20px;
    margin: 20px;
    background: #fee;
    border: 1px solid #f00;
    border-radius: 4px;
    color: #c00;
    font-family: monospace;
  `;
  div.textContent = `Error: ${message}`;
  return div;
};

export default {
  getDOMContainer,
  waitForDOM,
  createErrorElement,
};


