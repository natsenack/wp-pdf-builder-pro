# 🎨 Canvas-Renderers-Database Mapping Documentation

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                     PDF BUILDER PRO - COMPLETE FLOW                 │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  CANVAS EDITOR (Frontend)                                           │
│  ├─ useCanvasState Hook                                             │
│  ├─ Elements Array (in-memory)                                      │
│  ├─ Properties Panel (Live updates)                                 │
│  └─ Drag & Drop System                                              │
│           │                                                          │
│           ↓                                                          │
│  ELEMENT PROPERTIES (Standard Format)                               │
│  ├─ Position: x, y (pixels)                                         │
│  ├─ Size: width, height (pixels)                                    │
│  ├─ Transform: rotation, scale                                      │
│  ├─ Visibility & Opacity                                            │
│  ├─ Styling: colors, borders, shadows                               │
│  ├─ Content: text, imageUrl, etc.                                   │
│  └─ Type-Specific Properties                                        │
│           │                                                          │
│           ↓                                                          │
│  DATABASE STORAGE (WordPress Options/Meta)                          │
│  ├─ templates table (template_id, template_name, data)              │
│  ├─ template_elements table (element_id, type, properties as JSON)  │
│  └─ template_meta table (additional metadata)                       │
│           │                                                          │
│           ↓                                                          │
│  PREVIEW SYSTEM (Component Rendering)                               │
│  ├─ ElementRenderer (Router)                                        │
│  ├─ Type-Specific Renderers (7 types)                               │
│  ├─ CanvasMode (Preview display)                                    │
│  └─ PreviewModal (Modal display)                                    │
│           │                                                          │
│           └─→ PDF Output (TCPDF)                                    │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 1. CANVAS ELEMENT STRUCTURE

### Base Properties (Common to All Elements)

```javascript
{
  // Identification
  id: "elem_12345",                    // Unique identifier (string)
  type: "text|image|rectangle|...",    // Element type

  // Positioning (in pixels)
  x: 50,                               // Horizontal position (0-595)
  y: 100,                              // Vertical position (0-842)
  
  // Dimensions (in pixels)
  width: 200,                          // Width (1-595)
  height: 50,                          // Height (1-842)
  
  // Layering
  zIndex: 1,                           // Z-index (1-1000)
  
  // Visibility
  visible: true,                       // Show/hide element
  opacity: 100,                        // Opacity 0-100 (stored as 0-1 in CSS)
  
  // Transformations
  rotation: 0,                         // Rotation in degrees (-360 to 360)
  scale: 1,                            // Scale factor (0.1 to 5)
  
  // Styling
  backgroundColor: "transparent",      // Background color (#hex or name)
  borderWidth: 1,                      // Border width in pixels (0-10)
  borderColor: "#000000",              // Border color
  borderRadius: 0,                     // Border radius in pixels (0-100)
  borderStyle: "solid",                // Border style (solid, dashed, dotted)
  
  // Shadow & Effects
  shadow: false,                       // Enable shadow
  shadowOffsetX: 2,                    // Shadow X offset
  shadowOffsetY: 2,                    // Shadow Y offset
  shadowColor: "#000000",              // Shadow color
  shadowBlur: 4,                       // Shadow blur
  
  // Filters
  brightness: 100,                     // Brightness 0-200
  contrast: 100,                       // Contrast 0-200
  saturate: 100                        // Saturation 0-200
}
```

---

## 2. ELEMENT-SPECIFIC PROPERTIES BY TYPE

### 2.1 TEXT ELEMENT

```javascript
{
  ...baseProperties,
  type: "text",
  
  // Content
  content: "Hello World",              // Text content
  text: "Hello World",                 // Alternative text field
  
  // Typography
  fontSize: 12,                        // Font size in points (6-72)
  fontFamily: "Arial",                 // Font family
  fontWeight: "normal",                // Font weight (normal, bold, 700-900)
  fontStyle: "normal",                 // Font style (normal, italic)
  
  // Text Layout
  textAlign: "left",                   // Alignment (left, center, right, justify)
  textDecoration: "none",              // Text decoration (none, underline, overline, line-through)
  lineHeight: 1.5,                     // Line height (1-3)
  letterSpacing: 0,                    // Letter spacing in pixels
  
  // Color
  color: "#1e293b",                    // Text color
  
  // Advanced
  whiteSpace: "pre-wrap",              // Whitespace handling
  wordWrap: "break-word",              // Word wrapping
  textTransform: "none"                // Text transform (none, uppercase, lowercase, capitalize)
}
```

**Canvas Display:**
```css
/* Applied in CanvasElement.jsx */
position: absolute;
left: ${element.x}px;
top: ${element.y}px;
width: ${element.width}px;
height: ${element.height}px;
font-size: ${element.fontSize}px;
font-family: ${element.fontFamily};
color: ${element.color};
text-align: ${element.textAlign};
line-height: ${element.lineHeight};
```

**Preview Renderer:**
```jsx
// In TextRenderer.jsx
<div style={{
  position: 'absolute',
  left: `${x * canvasScale}px`,
  top: `${y * canvasScale}px`,
  width: `${width * canvasScale}px`,
  height: `${height * canvasScale}px`,
  fontSize: `${fontSize}px`,
  color: color,
  fontFamily: fontFamily,
  // ... all other CSS properties
}}>
  {content}
</div>
```

---

### 2.2 IMAGE ELEMENT

```javascript
{
  ...baseProperties,
  type: "image",
  
  // Image Source
  imageUrl: "https://...",             // Image URL
  src: "https://...",                  // Alternative image URL
  alt: "Image description",            // Alt text
  
  // Image Sizing
  objectFit: "contain",                // Object-fit (contain, cover, fill, none, scale-down)
  objectPosition: "center",            // Object position
  
  // Filters
  brightness: 100,                     // 0-200%
  contrast: 100,                       // 0-200%
  saturate: 100,                       // 0-200%
  
  // Advanced
  disableDrag: false,                  // Disable dragging
  useAsBackground: false               // Use as element background
}
```

**Canvas Display:**
```jsx
<img 
  src={element.src || element.imageUrl}
  alt={element.alt}
  style={{
    position: 'absolute',
    left: `${element.x}px`,
    top: `${element.y}px`,
    width: `${element.width}px`,
    height: `${element.height}px`,
    objectFit: element.objectFit,
    opacity: element.opacity / 100
  }}
/>
```

**Preview Renderer:**
```jsx
// In ImageRenderer.jsx with error handling and fallback placeholder
const [imageError, setImageError] = useState(false);

<img
  src={finalImageUrl}
  alt={alt}
  onLoad={() => setImageError(false)}
  onError={() => setImageError(true)}
/>

// Shows placeholder if error or no URL
{imageError && <div>📷 Image</div>}
```

---

### 2.3 RECTANGLE/SHAPE ELEMENT

```javascript
{
  ...baseProperties,
  type: "rectangle|shape-circle|shape-triangle|...",
  
  // Shape Properties
  shapeType: "rectangle",              // Shape type
  fillColor: "#ffffff",                // Fill color (alias for backgroundColor)
  strokeColor: "#000000",              // Stroke color (alias for borderColor)
  strokeWidth: 1,                      // Stroke width (alias for borderWidth)
  
  // Rectangle-specific
  cornerRadius: 0,                     // Corner radius
  
  // Circle-specific
  radius: 50,                          // Radius for circle
  
  // Styling inherited from base
  // backgroundColor, borderWidth, borderColor, borderRadius, shadow, etc.
}
```

**Canvas Display:**
```jsx
<div
  style={{
    position: 'absolute',
    left: `${element.x}px`,
    top: `${element.y}px`,
    width: `${element.width}px`,
    height: `${element.height}px`,
    backgroundColor: element.backgroundColor,
    border: `${element.borderWidth}px ${element.borderStyle} ${element.borderColor}`,
    borderRadius: `${element.borderRadius}px`
  }}
/>
```

**Preview Renderer:**
```jsx
// In RectangleRenderer.jsx - simple div with styling
<div style={{
  position: 'absolute',
  left: `${x * canvasScale}px`,
  top: `${y * canvasScale}px`,
  width: `${width * canvasScale}px`,
  height: `${height * canvasScale}px`,
  backgroundColor: backgroundColor,
  border: `${borderWidth}px solid ${borderColor}`,
  // ... other styles
}} />
```

---

### 2.4 TABLE ELEMENT (Product Table)

```javascript
{
  ...baseProperties,
  type: "product_table",
  
  // Data Source
  dataSource: "order_items",           // Data source (order_items, custom_data)
  
  // Column Configuration
  columns: {
    image: true,                       // Show product image
    name: true,                        // Show product name
    sku: false,                        // Show SKU
    quantity: true,                    // Show quantity
    price: true,                       // Show price
    total: true                        // Show total
  },
  
  // Display Options
  showHeaders: true,                   // Show table headers
  showBorders: false,                  // Show cell borders
  showSubtotal: false,                 // Show subtotal row
  showShipping: true,                  // Show shipping row
  showTaxes: true,                     // Show tax row
  showDiscount: false,                 // Show discount row
  showTotal: false,                    // Show total row
  
  // Styling
  tableStyle: "default",               // Table style preset
  headers: ["Produit", "Qté", "Prix"], // Custom header labels
  
  // Font
  fontSize: 11,                        // Table font size
  headerFontSize: 12,                  // Header font size
  headerFontWeight: "bold",            // Header font weight
}
```

**Database Storage (previewData):**
```javascript
{
  [`product_table_${element.id}`]: {
    rows: [
      ["Product A", "2", "50€"],
      ["Product B", "1", "75€"]
    ],
    headers: ["Produit", "Qté", "Prix"],
    tableStyleData: {
      header_bg: [248, 249, 250],
      header_border: [226, 232, 240],
      row_border: [241, 245, 249],
      alt_row_bg: [250, 251, 252]
    }
  }
}
```

**Preview Renderer:**
```jsx
// In TableRenderer.jsx
const elementKey = `product_table_${element.id}`;
const tableData = previewData[elementKey] || {};
const rows = tableData.rows || [];

<table style={{...tableStyle}}>
  <thead>
    <tr>
      {tableData.headers?.map((header, i) => <th key={i}>{header}</th>)}
    </tr>
  </thead>
  <tbody>
    {rows.map((row, idx) => (
      <tr key={idx}>
        {row.map((cell, cidx) => <td key={cidx}>{cell}</td>)}
      </tr>
    ))}
  </tbody>
</table>
```

---

### 2.5 BARCODE/QR CODE ELEMENT

```javascript
{
  ...baseProperties,
  type: "barcode|qrcode",
  
  // Code Content
  content: "123456789",                // Value to encode
  code: "123456789",                   // Alternative code field
  
  // Barcode-Specific
  format: "CODE128",                   // Barcode format (CODE128, CODE39, EAN13, etc.)
  displayValue: true,                  // Show value below barcode
  
  // QR Code-Specific
  errorCorrection: "H",                // Error correction level (L, M, Q, H)
  
  // Styling
  foreColor: "#000000",                // Barcode color
  backColor: "#ffffff",                // Background color
}
```

**Preview Renderer:**
```jsx
// In BarcodeRenderer.jsx - Real barcode generation
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

useEffect(() => {
  if (element.type === 'qrcode') {
    QRCode.toCanvas(canvasRef.current, codeValue, {
      errorCorrectionLevel: 'H',
      type: 'image/png'
    });
  } else {
    JsBarcode(svgRef.current, codeValue, {
      format: element.format || 'CODE128',
      width: 2,
      height: 40
    });
  }
}, [codeValue, element.type]);

// Renders real SVG (barcode) or Canvas (QR)
```

---

### 2.6 DYNAMIC TEXT ELEMENT

```javascript
{
  ...baseProperties,
  type: "dynamic-text",
  
  // Template Content
  content: "Order #{orderId} for {customer.name}",  // Template with variables
  template: "...",                     // Alternative template field
  
  // Variable Mapping
  variables: {
    orderId: "element.orderId",        // Variable mappings
    customer: "templateData.customer"
  },
  
  // Typography (same as TextRenderer)
  fontSize: 12,
  fontFamily: "Arial",
  color: "#000000",
  // ... all text properties
}
```

**Preview Renderer:**
```jsx
// In DynamicTextRenderer.jsx - Interpolate variables
const variables = previewData || {};
const interpolated = content.replace(/{([^}]+)}/g, (match, key) => 
  variables[key] || match
);

<div>{interpolated}</div>
```

---

### 2.7 PROGRESS BAR ELEMENT

```javascript
{
  ...baseProperties,
  type: "progress-bar",
  
  // Progress Value
  progressValue: 65,                   // Percentage 0-100
  
  // Colors
  progressColor: "#3b82f6",            // Progress bar color
  backgroundColor: "#e5e7eb",          // Background color
  
  // Animation
  animated: false,                     // Enable animation
  animationDuration: 1000              // Duration in ms
}
```

**Preview Renderer:**
```jsx
// In ProgressBarRenderer.jsx
<div style={{background: backgroundColor}}>
  <div style={{
    width: `${Math.min(100, progressValue)}%`,
    backgroundColor: progressColor,
    transition: 'width 0.3s ease'
  }} />
</div>
```

---

### 2.8 INFO ELEMENTS (Customer, Company, Order)

```javascript
{
  ...baseProperties,
  type: "customer_info|company_info|order_number|mentions",
  
  // Display Options
  showEmail: true,
  showPhone: true,
  showSiret: true,
  showVat: false,
  showAddress: false,
  showWebsite: false,
  showCustomText: false,
  customText: "",
  
  // Formatting
  separator: " • ",
  layout: "horizontal",                // horizontal or vertical
  
  // Typography
  fontSize: 10,
  fontFamily: "Arial",
  color: "#666666",
  textAlign: "center"
}
```

**Preview Renderers:**
```jsx
// In customer_info, company_info, etc.
// Data from previewData.customer, previewData.company, etc.

const customerData = previewData.customer || {};
<div>
  <strong>Client:</strong> {customerData.name}
  <strong>Email:</strong> {customerData.email}
  // ... other fields
</div>
```

---

## 3. DATABASE STORAGE FORMAT

### WordPress Table Structure

#### `wp_postmeta` Table (Templates)
```sql
-- Store entire template as JSON
{
  meta_key: "pdf_template_elements",
  meta_value: JSON([
    {
      id: "elem_1",
      type: "text",
      x: 50,
      y: 100,
      width: 200,
      height: 50,
      content: "Order Receipt",
      fontSize: 16,
      fontFamily: "Arial",
      color: "#000000",
      // ... all properties as JSON
    },
    {
      id: "elem_2",
      type: "image",
      x: 50,
      y: 20,
      width: 150,
      height: 80,
      imageUrl: "https://...",
      // ... all properties
    },
    // ... more elements
  ])
}
```

### Retrieval Flow

```php
<?php
// In PHP backend
$elements = get_post_meta($template_id, 'pdf_template_elements', true);

// Returns array:
[
  [
    'id' => 'elem_1',
    'type' => 'text',
    'x' => 50,
    'y' => 100,
    // ... all properties
  ],
  // ... more elements
]
?>
```

### JavaScript Load Flow

```javascript
// In frontend
async function loadTemplate(templateId) {
  const response = await fetch(`/wp-json/pdf-builder/v1/templates/${templateId}`);
  const data = await response.json();
  
  // Returns:
  {
    id: 123,
    name: "Invoice",
    elements: [
      {
        id: "elem_1",
        type: "text",
        x: 50,
        y: 100,
        // ... all properties
      },
      // ... more elements
    ]
  }
}
```

---

## 4. COORDINATE SYSTEM

### Canvas Units (Pixels)

```
╔═════════════════════════════════════════════════════╗
║  A4 Page (595 x 842 pixels)                        ║
║                                                     ║
║  (0,0)                                (595,0)      ║
║    ┌─────────────────────────────────┐             ║
║    │                                  │             ║
║    │  Element at (x, y)              │             ║
║    │  ┌──────────────┐               │             ║
║    │  │ width        │               │             ║
║    │  │ height       │               │             ║
║    │  └──────────────┘               │             ║
║    │                                  │             ║
║    │                                  │             ║
║    └─────────────────────────────────┘             ║
║  (0,842)                        (595,842)          ║
╚═════════════════════════════════════════════════════╝
```

### Conversion Formulas

```javascript
// Canvas coordinates (pixels)
const x = 50;      // px from left
const y = 100;     // px from top
const width = 200; // px
const height = 50; // px

// To millimeters (if needed)
const mmPerPixel = 0.264583;
const x_mm = x * mmPerPixel;     // ≈ 13.23 mm
const y_mm = y * mmPerPixel;     // ≈ 26.46 mm

// With canvas scaling (in preview mode)
const canvasScale = 0.8;  // 80% of original
const scaledX = x * canvasScale;        // 40 px
const scaledY = y * canvasScale;        // 80 px
const scaledWidth = width * canvasScale; // 160 px
const scaledHeight = height * canvasScale; // 40 px
```

---

## 5. DATA FLOW EXAMPLE: Complete Text Element

### 1. Canvas Editor Creation

```javascript
// User creates text element via canvas toolbar
const newElement = {
  id: "elem_1234567890",
  type: "text",
  x: 50,
  y: 100,
  width: 300,
  height: 40,
  content: "Invoice #123",
  fontSize: 18,
  fontFamily: "Arial",
  fontWeight: "bold",
  color: "#1a1a1a",
  textAlign: "left",
  backgroundColor: "transparent",
  borderWidth: 0,
  opacity: 100,
  rotation: 0,
  scale: 1,
  visible: true,
  shadow: false
};

// Stored in React state via useCanvasState
canvasState.addElement('text', newElement);
```

### 2. Canvas Display (CanvasElement.jsx)

```jsx
// Element rendered in canvas editor with full interactivity
<div
  style={{
    position: 'absolute',
    left: `${50}px`,
    top: `${100}px`,
    width: `${300}px`,
    height: `${40}px`,
    fontSize: '18px',
    fontWeight: 'bold',
    color: '#1a1a1a',
    textAlign: 'left'
  }}
  onMouseDown={handleMouseDown}  // Drag
  onDoubleClick={handleDoubleClick}  // Edit
>
  Invoice #123
</div>
```

### 3. Database Storage

```javascript
// When template is saved to WordPress
fetch('/wp-json/pdf-builder/v1/templates/save', {
  method: 'POST',
  body: JSON.stringify({
    id: 1,
    name: 'Invoice Template',
    elements: [
      {
        id: "elem_1234567890",
        type: "text",
        x: 50,
        y: 100,
        width: 300,
        height: 40,
        content: "Invoice #123",
        fontSize: 18,
        fontFamily: "Arial",
        fontWeight: "bold",
        color: "#1a1a1a",
        textAlign: "left",
        backgroundColor: "transparent",
        borderWidth: 0,
        opacity: 100,
        rotation: 0,
        scale: 1,
        visible: true,
        shadow: false
      }
    ]
  })
});

// Saved in WordPress:
// POST meta 'pdf_template_elements' = JSON stringify of elements array
```

### 4. Database Retrieval

```php
<?php
// In PHP backend
$template_id = 1;
$elements = get_post_meta($template_id, 'pdf_template_elements', true);

// Returns:
[
  [
    'id' => 'elem_1234567890',
    'type' => 'text',
    'x' => 50,
    'y' => 100,
    'width' => 300,
    'height' => 40,
    'content' => 'Invoice #123',
    'fontSize' => 18,
    'fontFamily' => 'Arial',
    'fontWeight' => 'bold',
    'color' => '#1a1a1a',
    // ... all properties
  ]
]
```

### 5. Preview Rendering (TextRenderer.jsx)

```jsx
// Elements fetched and passed to preview
<PreviewModal
  elements={elements}
  templateData={{
    customer: { name: "John Doe", email: "john@example.com" },
    order: { number: "123" }
  }}
/>

// ElementRenderer routes to TextRenderer
<TextRenderer
  element={{
    id: "elem_1234567890",
    type: "text",
    x: 50,
    y: 100,
    // ... all properties
  }}
  canvasScale={0.8}  // 80% for display
/>

// TextRenderer renders:
<div
  style={{
    position: 'absolute',
    left: `${50 * 0.8}px`,      // 40px
    top: `${100 * 0.8}px`,      // 80px
    width: `${300 * 0.8}px`,    // 240px
    height: `${40 * 0.8}px`,    // 32px
    fontSize: '18px',
    fontWeight: 'bold',
    color: '#1a1a1a',
    textAlign: 'left',
    overflow: 'hidden',
    whiteSpace: 'pre-wrap'
  }}
>
  Invoice #123
</div>
```

### 6. PDF Generation (TCPDF)

```php
<?php
// In PDF_Builder_PDF_Renderer class
$pdf = new TCPDF();

// For each element
foreach ($elements as $element) {
  if ($element['type'] === 'text') {
    // Set PDF properties
    $pdf->SetXY(
      $element['x'] / 2.834,  // Convert px to mm
      $element['y'] / 2.834
    );
    
    $pdf->SetFont(
      $element['fontFamily'],
      $element['fontWeight'] === 'bold' ? 'B' : '',
      $element['fontSize']
    );
    
    $pdf->SetTextColor(
      hexdec(substr($element['color'], 1, 2)),  // R
      hexdec(substr($element['color'], 3, 2)),  // G
      hexdec(substr($element['color'], 5, 2))   // B
    );
    
    $pdf->MultiCell(
      $element['width'] / 2.834,
      $element['height'] / 2.834,
      $element['content'],
      0,
      $element['textAlign'][0]  // l, c, r
    );
  }
}

$pdf->Output('invoice.pdf', 'D');
?>
```

---

## 6. PROPERTY MAPPING TABLE

| Property | Canvas | Database | Preview | PDF | Type | Min-Max |
|----------|--------|----------|---------|-----|------|---------|
| x | px | px | px*scale | mm | number | 0-595 |
| y | px | px | px*scale | mm | number | 0-842 |
| width | px | px | px*scale | mm | number | 1-595 |
| height | px | px | px*scale | mm | number | 1-842 |
| fontSize | px | px | px | pt | number | 6-72 |
| fontFamily | string | string | string | string | enum | - |
| color | #hex | #hex | #hex | RGB | color | - |
| rotation | deg | deg | deg | deg | number | -360-360 |
| scale | ratio | ratio | ratio | ratio | number | 0.1-5 |
| opacity | 0-100 | 0-100 | 0-1 | 0-100 | number | 0-100 |
| imageUrl | URL | URL | URL | embedded | string | - |
| content | text | text | text | text | string | - |

---

## 7. SPECIAL CASES & CONSIDERATIONS

### Case 1: Responsive Scaling in Preview

```javascript
// Canvas dimensions: 595x842 (A4)
// Container width: 800px
// Calculated scale: 800 / 595 ≈ 0.8

// Element position:
// Canvas: x=100, y=200
// Preview: x=100*0.8=80px, y=200*0.8=160px

// Important: transformOrigin MUST be 'top left'
// Otherwise scaling causes diagonal distortion
```

### Case 2: Units Conversion

```javascript
// Pixels ↔ Millimeters
const px_to_mm = 0.264583;
const mm_to_px = 3.779528;

const mm_value = 50;           // mm
const px_value = mm_value * 3.779528;  // ≈ 189 px

// Pixels ↔ Points (PDF)
const px_to_pt = 0.75;
const pt_to_px = 1.333333;

const pt_value = 12;           // points
const px_value = pt_value * 1.333333;  // ≈ 16 px
```

### Case 3: Z-Index Stacking

```javascript
// Elements with higher zIndex appear on top
const elements = [
  { id: 1, zIndex: 1, type: 'rectangle', ...},  // Background
  { id: 2, zIndex: 2, type: 'text', ...},       // Middle
  { id: 3, zIndex: 3, type: 'image', ...}       // Top
];

// CSS applied per element
<div style={{ zIndex: element.zIndex }}>
```

### Case 4: Dynamic Content Interpolation

```javascript
// Template: "Order #{orderId} for {customer.name}"
// Preview Data: { orderId: "123", customer: { name: "John" } }
// Result: "Order #123 for John"

// Variables accessed via dot notation
// {customer.name} → previewData['customer']['name']
// {product.0.name} → previewData['product'][0]['name']
```

---

## 8. RENDERING PIPELINE

```
┌─────────────────────────────────────────────────────┐
│ 1. LOAD TEMPLATE FROM DATABASE                      │
├─────────────────────────────────────────────────────┤
│ GET /wp-json/pdf-builder/v1/templates/{id}         │
│ ↓                                                    │
│ Returns: { id, name, elements[], templateData }    │
└─────────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────┐
│ 2. PASS TO CANVAS EDITOR (Edit Mode)               │
├─────────────────────────────────────────────────────┤
│ useCanvasState(initialElements)                    │
│ ↓                                                    │
│ Renders: CanvasElement for each element            │
│ Features: Drag, resize, edit, delete               │
└─────────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────┐
│ 3. PREVIEW MODE (Live Preview)                     │
├─────────────────────────────────────────────────────┤
│ <PreviewModal elements={elements} />               │
│ ↓                                                    │
│ CanvasMode → ElementRenderer → Type-specific       │
│ renderers (TextRenderer, ImageRenderer, etc.)      │
│ ↓                                                    │
│ Displays with canvasScale for responsiveness       │
└─────────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────┐
│ 4. SAVE TO DATABASE (WordPress)                    │
├─────────────────────────────────────────────────────┤
│ POST /wp-json/pdf-builder/v1/templates/save        │
│ ↓                                                    │
│ Serialize elements to JSON                         │
│ Save as post_meta['pdf_template_elements']         │
└─────────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────┐
│ 5. PDF GENERATION (Frontend - JavaScript)          │
├─────────────────────────────────────────────────────┤
│ OR PDF GENERATION (Backend - PHP/TCPDF)            │
├─────────────────────────────────────────────────────┤
│ Load elements from database                        │
│ ↓                                                    │
│ For each element:                                  │
│   • Convert coordinates (px → mm)                  │
│   • Set styling (fonts, colors, etc.)              │
│   • Render content (text, images, etc.)            │
│ ↓                                                    │
│ Output PDF file                                    │
└─────────────────────────────────────────────────────┘
```

---

## 9. BEST PRACTICES

### ✅ DO

```javascript
// Always use px units for coordinates
left: `${x}px`;
top: `${y}px`;
width: `${width}px`;

// Use transformOrigin: 'top left' to prevent distortion
transform: `rotate(${rotation}deg) scale(${scale})`;
transformOrigin: 'top left';

// Apply canvasScale consistently in preview
const scaledX = x * canvasScale;

// Store opacity as 0-100, convert to 0-1 for CSS
opacity: opacity / 100;

// Validate coordinates before saving
x = Math.max(0, Math.min(595, x));
y = Math.max(0, Math.min(842, y));

// Use proper line-height format
lineHeight: `${lineHeight}`;  // String, not number
```

### ❌ DON'T

```javascript
// Don't omit units
left: x;  // ❌ Wrong
left: `${x}px`;  // ✅ Right

// Don't use center origin for transforms
transformOrigin: 'center center';  // ❌ Causes distortion
transformOrigin: 'top left';  // ✅ Correct

// Don't mix scaling levels
const x = element.x * canvasScale * scale;  // ❌ Double scaling
const x = element.x * canvasScale;  // ✅ Already includes scale

// Don't store opacity as 0-1 in database
opacity: 0.7;  // ❌ Inconsistent
opacity: 70;  // ✅ Consistent

// Don't validate after display
// Validate before saving to database
```

---

## 10. TROUBLESHOOTING

### Issue: Elements misaligned in preview

```javascript
// ✅ Solution: Check canvasScale calculation
const pageWidth = 595;  // A4 width
const containerWidth = 800;  // Display container
const canvasScale = containerWidth / pageWidth;  // Should be ~1.34

// Check CSS
left: `${x * canvasScale}px`;  // Must include px
transformOrigin: 'top left';  // Must be top left
```

### Issue: Text overflowing bounds

```jsx
// ✅ Solution: Apply proper CSS constraints
<div style={{
  height: `${height}px`,        // Not minHeight
  overflow: 'hidden',           // Constrain content
  whiteSpace: 'pre-wrap',       // Preserve line breaks
  wordWrap: 'break-word'        // Wrap long words
}}>
```

### Issue: Images not loading in preview

```jsx
// ✅ Solution: Add error handling with fallback
const [imageError, setImageError] = useState(false);

<img
  src={imageUrl}
  onError={() => setImageError(true)}
/>

{imageError && <div>📷 Image</div>}
```

---

**This documentation provides a complete reference for understanding how elements flow from Canvas Editor → Database → Preview → PDF Output.**

