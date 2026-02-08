# Analysis Summary: Header.tsx Preview System Issues

## 1. **CRITICAL ISSUE: Missing Variable Definitions**

### Location: [Header.tsx](src/js/react/components/header/Header.tsx#L1987-L2002)

The "Aperçu" (Preview) button click handler (lines 1987-2002) references variables that are **NOT defined** in the component:

#### Undefined Variables Being Used:
- **`isGeneratingPreview`** - Used at lines 1991, 2961, 2979, 2982, 2986, 2994
- **`previewImageUrl`** - Used at lines 1992, 3025, 3028, 3040
- **`previewFormat`** - Used at lines 1994, 2922, 2927, 2931, 2933, 2960, 2972, 3043
- **`generatePreview`** - Used/called at lines 1997, 2962, 2965

### The Problem:
These variables are referenced in console.log statements and function calls, but they are **never declared** with `useState()` in the Header component.

**Confirmed defined state variables:**
- Line 106: `previewError` ✓ (defined)
- Line 104: `previewOrderId` ✓ (defined)
- Line 105: `isLoadingPreview` ✓ (defined)

---

## 2. **Code Structure: "Aperçu" Button Click Handler**

### Location: [Header.tsx lines 1986-2003](src/js/react/components/header/Header.tsx#L1986-L2003)

```tsx
<button
  onClick={() => {
    console.log('[REACT HEADER] ===== APERÇU BUTTON CLICKED =====');
    console.log('[REACT HEADER] Aperçu button clicked - opening preview modal');
    console.log('[REACT HEADER] Current state before opening modal:');
    console.log('[REACT HEADER] - showPreviewModal:', showPreviewModal);
    console.log('[REACT HEADER] - isGeneratingPreview:', isGeneratingPreview);  // ⚠️ UNDEFINED
    console.log('[REACT HEADER] - previewImageUrl:', previewImageUrl);          // ⚠️ UNDEFINED
    console.log('[REACT HEADER] - previewError:', previewError);                 // ✓ defined
    console.log('[REACT HEADER] - previewFormat:', previewFormat);              // ⚠️ UNDEFINED
    console.log('[REACT HEADER] - Template state elements count:', state.elements?.length || 0);
    console.log('[REACT HEADER] - Template state has content:', !!(state.elements && state.elements.length > 0));
    console.log('[REACT HEADER] - usePreview hook available:', typeof usePreview);
    console.log('[REACT HEADER] - openModal function available:', typeof openPreviewModal);
    console.log('[REACT HEADER] About to call openPreviewModal()');
    openPreviewModal();
    console.log('[REACT HEADER] openPreviewModal() called successfully');
    console.log('[REACT HEADER] ===== APERÇU BUTTON CLICK HANDLER COMPLETED =====');
  }}
  // ... button props
/>
```

---

## 3. **ALL Console.log Statements with Debug Tags**

### In React Files:

#### [src/js/react/index.tsx](src/js/react/index.tsx)
- Line 57: `[PDF Builder] React app destroyed`
- Line 71: `[PDF Builder] window.pdfBuilderReact API assigned`
- Line 76: `[PDF Builder] ===== REACT APP INITIALIZATION COMPLETE =====`

#### [src/js/react/components/header/Header.tsx](src/js/react/components/header/Header.tsx)
- Lines 1987-2002: 16 console.log statements with `[REACT HEADER]` tag
- Lines 2950-2977: Multiple `[HEADER COMPONENT]` console.log statements
  - Line 2960: Preview format logging
  - Line 2961: Is generating preview logging
  - Line 2962: generatePreview function availability check
  - Line 2963: About to call generatePreview
  - Line 2977: generatePreview call completed
- Lines 2922: `setPreviewFormat` usage

#### **No instances of:** `[FOOTER INIT CHECK]`, `[DOM CHECK]` found in React files

### In PHP/Template Files:

#### [plugin/pdf-builder-pro.php](plugin/pdf-builder-pro.php)
- Lines 2540-2550: Multiple `[DEBUG]` tags in error_log statements (11 entries)

#### [plugin/templates/admin/templates-page.php](plugin/templates/admin/templates-page.php)
- Line 359: `[PDF Builder]` in inline onclick handler
- Line 1184: `[DEBUG]` console.log
- Line 1188: `[DEBUG]` console.log

---

## 4. **Summary Table**

| Variable | Defined? | Line(s) Used | Type |
|----------|-----------|-------------|------|
| `isGeneratingPreview` | ❌ NO | 1991, 2961, 2979, 2982, 2986, 2994 | should be boolean state |
| `previewImageUrl` | ❌ NO | 1992, 3025, 3028, 3040 | should be string state |
| `previewFormat` | ❌ NO | 1994, 2922, 2927, 2931, 2933, 2960, 2972, 3043 | should be enum state |
| `generatePreview` | ❌ NO | 1997, 2962, 2965 | should be async function |
| `previewError` | ✓ YES | 1993, 3019 | defined at line 106 |
| `showPreviewModal` | ✓ YES (via context) | 1990, 2899 | from builderState |

---

## 5. **Required Fixes**

### Add Missing State Declarations in Header.tsx (after line 106):
```tsx
const [isGeneratingPreview, setIsGeneratingPreview] = useState(false);
const [previewImageUrl, setPreviewImageUrl] = useState<string | null>(null);
const [previewFormat, setPreviewFormat] = useState<'png' | 'jpg' | 'pdf'>('png');
```

### Add Missing generatePreview Function:
- This function needs to be either:
  - Imported from a hook or utility
  - Defined as a useCallback in the component
  - Accessed from window object/context

---

## 6. **Console.log Removal Checklist**

Files with debug console.log statements to clean:

- [ ] [src/js/react/index.tsx](src/js/react/index.tsx) - 3 statements with `[PDF Builder]`
- [ ] [src/js/react/components/header/Header.tsx](src/js/react/components/header/Header.tsx) - 16+ statements with `[REACT HEADER]` and `[HEADER COMPONENT]`
- [ ] [plugin/pdf-builder-pro.php](plugin/pdf-builder-pro.php) - 11 statements with `[DEBUG]`
- [ ] [plugin/templates/admin/templates-page.php](plugin/templates/admin/templates-page.php) - 3 statements with `[DEBUG]` and `[PDF Builder]`

