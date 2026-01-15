/**
 * DOM utilities for React integration with WordPress
 */

export const getDOMContainer = (containerId: string): HTMLElement | null => {
  const container = document.getElementById(containerId);
  if (!container) {
    console.warn(`Container with id "${containerId}" not found in DOM`);
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

export const showInitIndicator = (): void => {
  const indicator = document.createElement('div');
  indicator.id = 'pdf-builder-init-indicator';
  indicator.style.cssText = `
    position: fixed;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 16px;
    border-radius: 6px;
    z-index: 999999;
    font-size: 12px;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    font-family: system-ui, -apple-system, sans-serif;
  `;
  indicator.textContent = '✅ PDF Builder V2 Loaded';
  
  if (document.body) {
    document.body.appendChild(indicator);
  } else {
    document.addEventListener('DOMContentLoaded', () => {
      document.body.appendChild(indicator);
    }, { once: true });
  }
};

export default {
  getDOMContainer,
  waitForDOM,
  createErrorElement,
  showInitIndicator,
};
