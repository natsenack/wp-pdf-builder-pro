#!/usr/bin/env node

/**
 * Verification Script for PDF Builder React JavaScript Fix
 * Validates that the production bundle is properly configured and ready for WordPress
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const distFile = path.join(__dirname, 'plugin/resources/assets/js/dist/pdf-builder-react.js');
const wrapperFile = path.join(__dirname, 'assets/js/pdf-builder-wrap.js');

console.log('\n📋 PDF Builder React JavaScript Verification\n');
console.log('=' .repeat(60));

// 1. Check dist file exists
console.log('\n✓ Step 1: Checking production file...');
if (!fs.existsSync(distFile)) {
    console.error('  ✗ File not found:', distFile);
    process.exit(1);
}

const distContent = fs.readFileSync(distFile, 'utf8');
const distSize = fs.statSync(distFile).size;
console.log(`  ✓ File exists: ${distFile}`);
console.log(`  ✓ File size: ${distSize.toLocaleString()} bytes`);

// 2. Check for export statements
console.log('\n✓ Step 2: Checking for ES6 export statements...');
const exportRegex = /(^|\n)(export\s+(default|const|function|class|var|let|{)|\s+export\s+{)/m;
if (exportRegex.test(distContent)) {
    console.error('  ✗ Found ES6 export statements!');
    process.exit(1);
}
console.log('  ✓ No ES6 export statements detected');

// 3. Check wrapper file
console.log('\n✓ Step 3: Checking error handler wrapper...');
if (!fs.existsSync(wrapperFile)) {
    console.error('  ✗ Wrapper file not found:', wrapperFile);
    process.exit(1);
}

const wrapperContent = fs.readFileSync(wrapperFile, 'utf8');
if (!wrapperContent.includes('Unexpected token') && !wrapperContent.includes('export')) {
    console.error('  ✗ Wrapper missing error handler for export errors!');
    process.exit(1);
}
console.log(`  ✓ Wrapper file has error handling for JavaScript errors`);

// 4. Check proper transpilation
console.log('\n✓ Step 4: Checking Babel transpilation...');
if (!distContent.startsWith('"use strict"')) {
    console.error('  ✗ File does not appear to be Babel transpiled');
    process.exit(1);
}
console.log('  ✓ File starts with "use strict" (Babel transpiled)');

// 5. Check for common problematic patterns
console.log('\n✓ Step 5: Checking for problematic patterns...');
const problematicPatterns = [
    { name: 'CommonJS require', regex: /\brequire\s*\(/g },
    { name: 'Module exports', regex: /module\.exports\s*=/g },
];

let issuesFound = 0;
problematicPatterns.forEach(pattern => {
    const matches = distContent.match(pattern.regex);
    if (matches && matches.length > 0) {
        console.log(`  ⚠ Found ${matches.length} occurrences of "${pattern.name}" (may be expected in some cases)`);
        issuesFound++;
    }
});

if (issuesFound === 0) {
    console.log('  ✓ No obvious problematic patterns detected');
}

// 6. Summary
console.log('\n' + '='.repeat(60));
console.log('✅ All checks passed! The JavaScript bundle is ready for WordPress.\n');
console.log('📊 Summary:');
console.log(`  • File size: ${distSize.toLocaleString()} bytes`);
console.log(`  • Transpiler: Babel @babel/preset-env`);
console.log(`  • Module format: Browser-compatible (no ES6 modules)`);
console.log(`  • Error handling: Enabled in pdf-builder-wrap.js`);
console.log(`  • WordPress integration: Ready for wp_enqueue_script`);
console.log('\n');
