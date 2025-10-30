const conversions = {
    'mm': 595 / 210,  // 2.833333
    'cm': 595 / 21,   // 28.333333
    'in': 595 / 8.27, // 71.949819
    'px': 1
};

const reverseConversions = {
    'mm': 210 / 595,  // 0.353333
    'cm': 21 / 595,   // 0.035333
    'in': 8.27 / 595, // 0.013899
    'px': 1
};

// console.log('Conversions to pixels:');
Object.entries(conversions).forEach(([unit, factor]) => {
    // console.log(`${unit}: ${factor}`);
});

// console.log('\nReverse conversions from pixels:');
Object.entries(reverseConversions).forEach(([unit, factor]) => {
    // console.log(`${unit}: ${factor}`);
});

// console.log('\nTest conversion precision with higher precision for 100px:');
['mm', 'cm', 'in'].forEach(unit => {
    const toUnit = 100 * reverseConversions[unit];
    const rounded = Math.round(toUnit * 100) / 100; // 2 décimales
    const backToPx = rounded * conversions[unit];
    const finalRounded = Math.round(backToPx * 100) / 100;
    // console.log(`${unit}: 100px -> ${toUnit.toFixed(3)} -> ${rounded} -> ${backToPx.toFixed(3)}px -> ${finalRounded}px (loss: ${(100 - finalRounded).toFixed(3)}px)`);
});