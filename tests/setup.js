require('@testing-library/jest-dom');

// Mock pour le Canvas API
const createCanvasMock = () => ({
  getContext: jest.fn(() => ({
    fillRect: jest.fn(),
    strokeRect: jest.fn(),
    clearRect: jest.fn(),
    beginPath: jest.fn(),
    closePath: jest.fn(),
    moveTo: jest.fn(),
    lineTo: jest.fn(),
    arc: jest.fn(),
    quadraticCurveTo: jest.fn(),
    bezierCurveTo: jest.fn(),
    fill: jest.fn(),
    stroke: jest.fn(),
    scale: jest.fn(),
    translate: jest.fn(),
    rotate: jest.fn(),
    save: jest.fn(),
    restore: jest.fn(),
    setTransform: jest.fn(),
    fillText: jest.fn(),
    strokeText: jest.fn(),
    measureText: jest.fn(() => ({ width: 100 })),
    drawImage: jest.fn(),
    createLinearGradient: jest.fn(() => ({
      addColorStop: jest.fn()
    })),
    createRadialGradient: jest.fn(() => ({
      addColorStop: jest.fn()
    })),
    shadowColor: '',
    shadowBlur: 0,
    shadowOffsetX: 0,
    shadowOffsetY: 0,
    globalAlpha: 1,
    globalCompositeOperation: 'source-over',
    lineWidth: 1,
    lineCap: 'butt',
    lineJoin: 'miter',
    miterLimit: 10,
    fillStyle: '#000000',
    strokeStyle: '#000000',
    font: '10px sans-serif'
  })),
  width: 800,
  height: 600,
  toDataURL: jest.fn(() => 'data:image/png;base64,mock'),
  toBlob: jest.fn()
});

HTMLCanvasElement.prototype.getContext = createCanvasMock().getContext;