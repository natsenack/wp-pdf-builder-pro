// Test file for optional chaining transpilation
const obj = { prop: 'value' };
const result = obj?.prop || 'default';
console.log(result);

export default result;