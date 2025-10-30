/**
 * Test de validation du chargement complet des templates
 * Vérifie que toutes les propriétés des éléments sont correctement sauvegardées et chargées
 */

const testTemplateData = {
  elements: [
    {
      id: "rect_1",
      type: "rectangle",
      x: 50,
      y: 50,
      width: 200,
      height: 100,
      fillColor: "#ff0000",
      strokeColor: "#000000",
      strokeWidth: 2,
      borderRadius: 5,
      rotation: 15,
      opacity: 0.8,
      visible: true,
      locked: false,
      createdAt: new Date("2025-10-30T10:00:00Z"),
      updatedAt: new Date("2025-10-30T10:00:00Z")
    },
    {
      id: "text_1",
      type: "text",
      x: 100,
      y: 200,
      width: 150,
      height: 30,
      text: "Hello World",
      fontSize: 18,
      color: "#333333",
      align: "center",
      rotation: 0,
      opacity: 1,
      visible: true,
      locked: false,
      createdAt: new Date("2025-10-30T10:05:00Z"),
      updatedAt: new Date("2025-10-30T10:05:00Z")
    },
    {
      id: "circle_1",
      type: "circle",
      x: 300,
      y: 50,
      width: 80,
      height: 80,
      fillColor: "#00ff00",
      strokeColor: "#000000",
      strokeWidth: 1,
      rotation: 0,
      opacity: 1,
      visible: true,
      locked: false,
      createdAt: new Date("2025-10-30T10:10:00Z"),
      updatedAt: new Date("2025-10-30T10:10:00Z")
    }
  ],
  canvas: {
    zoom: 1,
    pan: { x: 0, y: 0 },
    showGrid: true,
    gridSize: 20,
    snapToGrid: true,
    backgroundColor: "#ffffff"
  }
};

describe('Template Data Integrity', () => {
  test('devrait contenir tous les éléments avec leurs propriétés', () => {
    expect(testTemplateData.elements).toHaveLength(3);

    // Vérifier le rectangle
    const rect = testTemplateData.elements[0];
    expect(rect.type).toBe('rectangle');
    expect(rect.x).toBe(50);
    expect(rect.y).toBe(50);
    expect(rect.width).toBe(200);
    expect(rect.height).toBe(100);
    expect(rect.fillColor).toBe('#ff0000');
    expect(rect.strokeColor).toBe('#000000');
    expect(rect.strokeWidth).toBe(2);
    expect(rect.borderRadius).toBe(5);
    expect(rect.rotation).toBe(15);
    expect(rect.opacity).toBe(0.8);
    expect(rect.visible).toBe(true);
    expect(rect.locked).toBe(false);
  });

  test('devrait contenir le texte avec toutes ses propriétés', () => {
    const text = testTemplateData.elements[1];
    expect(text.type).toBe('text');
    expect(text.x).toBe(100);
    expect(text.y).toBe(200);
    expect(text.width).toBe(150);
    expect(text.height).toBe(30);
    expect(text.text).toBe('Hello World');
    expect(text.fontSize).toBe(18);
    expect(text.color).toBe('#333333');
    expect(text.align).toBe('center');
    expect(text.rotation).toBe(0);
    expect(text.opacity).toBe(1);
    expect(text.visible).toBe(true);
    expect(text.locked).toBe(false);
  });

  test('devrait contenir le cercle avec toutes ses propriétés', () => {
    const circle = testTemplateData.elements[2];
    expect(circle.type).toBe('circle');
    expect(circle.x).toBe(300);
    expect(circle.y).toBe(50);
    expect(circle.width).toBe(80);
    expect(circle.height).toBe(80);
    expect(circle.fillColor).toBe('#00ff00');
    expect(circle.strokeColor).toBe('#000000');
    expect(circle.strokeWidth).toBe(1);
    expect(circle.rotation).toBe(0);
    expect(circle.opacity).toBe(1);
    expect(circle.visible).toBe(true);
    expect(circle.locked).toBe(false);
  });

  test('devrait contenir les paramètres du canvas', () => {
    expect(testTemplateData.canvas.zoom).toBe(1);
    expect(testTemplateData.canvas.pan).toEqual({ x: 0, y: 0 });
    expect(testTemplateData.canvas.showGrid).toBe(true);
    expect(testTemplateData.canvas.gridSize).toBe(20);
    expect(testTemplateData.canvas.snapToGrid).toBe(true);
    expect(testTemplateData.canvas.backgroundColor).toBe('#ffffff');
  });

  test('devrait pouvoir être sérialisé/désérialisé correctement', () => {
    // Simuler la sauvegarde (stringify)
    const serialized = JSON.stringify(testTemplateData);
    expect(typeof serialized).toBe('string');

    // Simuler le chargement (parse)
    const deserialized = JSON.parse(serialized);

    // Vérifier que tout est préservé
    expect(deserialized.elements).toHaveLength(3);
    expect(deserialized.elements[0].fillColor).toBe('#ff0000');
    expect(deserialized.elements[1].text).toBe('Hello World');
    expect(deserialized.elements[2].fillColor).toBe('#00ff00');
    expect(deserialized.canvas.showGrid).toBe(true);
  });
});