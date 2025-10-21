import React, { useRef, useEffect } from 'react';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

/**
 * Renderer pour les codes-barres et QR codes
 */
export const BarcodeRenderer = ({ element, previewData, mode, canvasScale = 1 }) => {
  const {
    x = 0,
    y = 0,
    width = 150,
    height = 60,
    backgroundColor = 'transparent',
    borderColor = '#000000',
    borderWidth = 1,
    opacity = 100,
    // Propriétés avancées
    rotation = 0,
    scale = 1,
    visible = true,
    shadow = false,
    shadowColor = '#000000',
    shadowOffsetX = 2,
    shadowOffsetY = 2,
    // Contenu du code
    content = '',
    code = '',
    format = 'CODE128'
  } = element;

  const svgRef = useRef(null);
  const canvasRef = useRef(null);

  // Extraire le contenu du code à encoder
  const codeValue = content || code || 'BARCODE';

  // Générer le code-barres ou QR code
  useEffect(() => {
    if (!visible) return;

    if (element.type === 'qrcode') {
      // Générer QR code
      if (canvasRef.current) {
        QRCode.toCanvas(canvasRef.current, codeValue, {
          errorCorrectionLevel: 'H',
          type: 'image/png',
          quality: 1,
          margin: 0,
          width: Math.min(200, Math.max(50, width * canvasScale / 2))
        }).catch(err => console.error('QR Code génération échouée:', err));
      }
    } else {
      // Générer code-barres
      if (svgRef.current) {
        try {
          JsBarcode(svgRef.current, codeValue, {
            format: format || 'CODE128',
            width: 2,
            height: Math.max(40, height * canvasScale - 20),
            displayValue: true,
            fontSize: 12,
            margin: 2
          });
        } catch (err) {
          console.error('Code-barres génération échouée:', err);
        }
      }
    }
  }, [codeValue, element.type, width, height, canvasScale, format, visible]);

  const containerStyle = {
    position: 'absolute',
    left: `${x * canvasScale}px`,
    top: `${y * canvasScale}px`,
    width: `${width * canvasScale}px`,
    height: `${height * canvasScale}px`,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    opacity: opacity / 100,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'top left',
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none',
    overflow: 'hidden'
  };

  return (
    <div
      className="preview-element preview-barcode-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type={element.type}
    >
      {element.type === 'qrcode' ? (
        <canvas
          ref={canvasRef}
          style={{
            width: '100%',
            height: '100%',
            objectFit: 'contain'
          }}
        />
      ) : (
        <svg
          ref={svgRef}
          style={{
            width: '100%',
            height: 'auto',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
          }}
        />
      )}
    </div>
  );
};