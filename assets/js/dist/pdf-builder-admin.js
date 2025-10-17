/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 287:
/***/ ((__unused_webpack_module, exports) => {

/**
 * @license React
 * react.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
var l=Symbol.for("react.element"),n=Symbol.for("react.portal"),p=Symbol.for("react.fragment"),q=Symbol.for("react.strict_mode"),r=Symbol.for("react.profiler"),t=Symbol.for("react.provider"),u=Symbol.for("react.context"),v=Symbol.for("react.forward_ref"),w=Symbol.for("react.suspense"),x=Symbol.for("react.memo"),y=Symbol.for("react.lazy"),z=Symbol.iterator;function A(a){if(null===a||"object"!==typeof a)return null;a=z&&a[z]||a["@@iterator"];return"function"===typeof a?a:null}
var B={isMounted:function(){return!1},enqueueForceUpdate:function(){},enqueueReplaceState:function(){},enqueueSetState:function(){}},C=Object.assign,D={};function E(a,b,e){this.props=a;this.context=b;this.refs=D;this.updater=e||B}E.prototype.isReactComponent={};
E.prototype.setState=function(a,b){if("object"!==typeof a&&"function"!==typeof a&&null!=a)throw Error("setState(...): takes an object of state variables to update or a function which returns an object of state variables.");this.updater.enqueueSetState(this,a,b,"setState")};E.prototype.forceUpdate=function(a){this.updater.enqueueForceUpdate(this,a,"forceUpdate")};function F(){}F.prototype=E.prototype;function G(a,b,e){this.props=a;this.context=b;this.refs=D;this.updater=e||B}var H=G.prototype=new F;
H.constructor=G;C(H,E.prototype);H.isPureReactComponent=!0;var I=Array.isArray,J=Object.prototype.hasOwnProperty,K={current:null},L={key:!0,ref:!0,__self:!0,__source:!0};
function M(a,b,e){var d,c={},k=null,h=null;if(null!=b)for(d in void 0!==b.ref&&(h=b.ref),void 0!==b.key&&(k=""+b.key),b)J.call(b,d)&&!L.hasOwnProperty(d)&&(c[d]=b[d]);var g=arguments.length-2;if(1===g)c.children=e;else if(1<g){for(var f=Array(g),m=0;m<g;m++)f[m]=arguments[m+2];c.children=f}if(a&&a.defaultProps)for(d in g=a.defaultProps,g)void 0===c[d]&&(c[d]=g[d]);return{$$typeof:l,type:a,key:k,ref:h,props:c,_owner:K.current}}
function N(a,b){return{$$typeof:l,type:a.type,key:b,ref:a.ref,props:a.props,_owner:a._owner}}function O(a){return"object"===typeof a&&null!==a&&a.$$typeof===l}function escape(a){var b={"=":"=0",":":"=2"};return"$"+a.replace(/[=:]/g,function(a){return b[a]})}var P=/\/+/g;function Q(a,b){return"object"===typeof a&&null!==a&&null!=a.key?escape(""+a.key):b.toString(36)}
function R(a,b,e,d,c){var k=typeof a;if("undefined"===k||"boolean"===k)a=null;var h=!1;if(null===a)h=!0;else switch(k){case "string":case "number":h=!0;break;case "object":switch(a.$$typeof){case l:case n:h=!0}}if(h)return h=a,c=c(h),a=""===d?"."+Q(h,0):d,I(c)?(e="",null!=a&&(e=a.replace(P,"$&/")+"/"),R(c,b,e,"",function(a){return a})):null!=c&&(O(c)&&(c=N(c,e+(!c.key||h&&h.key===c.key?"":(""+c.key).replace(P,"$&/")+"/")+a)),b.push(c)),1;h=0;d=""===d?".":d+":";if(I(a))for(var g=0;g<a.length;g++){k=
a[g];var f=d+Q(k,g);h+=R(k,b,e,f,c)}else if(f=A(a),"function"===typeof f)for(a=f.call(a),g=0;!(k=a.next()).done;)k=k.value,f=d+Q(k,g++),h+=R(k,b,e,f,c);else if("object"===k)throw b=String(a),Error("Objects are not valid as a React child (found: "+("[object Object]"===b?"object with keys {"+Object.keys(a).join(", ")+"}":b)+"). If you meant to render a collection of children, use an array instead.");return h}
function S(a,b,e){if(null==a)return a;var d=[],c=0;R(a,d,"","",function(a){return b.call(e,a,c++)});return d}function T(a){if(-1===a._status){var b=a._result;b=b();b.then(function(b){if(0===a._status||-1===a._status)a._status=1,a._result=b},function(b){if(0===a._status||-1===a._status)a._status=2,a._result=b});-1===a._status&&(a._status=0,a._result=b)}if(1===a._status)return a._result.default;throw a._result;}
var U={current:null},V={transition:null},W={ReactCurrentDispatcher:U,ReactCurrentBatchConfig:V,ReactCurrentOwner:K};function X(){throw Error("act(...) is not supported in production builds of React.");}
exports.Children={map:S,forEach:function(a,b,e){S(a,function(){b.apply(this,arguments)},e)},count:function(a){var b=0;S(a,function(){b++});return b},toArray:function(a){return S(a,function(a){return a})||[]},only:function(a){if(!O(a))throw Error("React.Children.only expected to receive a single React element child.");return a}};exports.Component=E;exports.Fragment=p;exports.Profiler=r;exports.PureComponent=G;exports.StrictMode=q;exports.Suspense=w;
exports.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED=W;exports.act=X;
exports.cloneElement=function(a,b,e){if(null===a||void 0===a)throw Error("React.cloneElement(...): The argument must be a React element, but you passed "+a+".");var d=C({},a.props),c=a.key,k=a.ref,h=a._owner;if(null!=b){void 0!==b.ref&&(k=b.ref,h=K.current);void 0!==b.key&&(c=""+b.key);if(a.type&&a.type.defaultProps)var g=a.type.defaultProps;for(f in b)J.call(b,f)&&!L.hasOwnProperty(f)&&(d[f]=void 0===b[f]&&void 0!==g?g[f]:b[f])}var f=arguments.length-2;if(1===f)d.children=e;else if(1<f){g=Array(f);
for(var m=0;m<f;m++)g[m]=arguments[m+2];d.children=g}return{$$typeof:l,type:a.type,key:c,ref:k,props:d,_owner:h}};exports.createContext=function(a){a={$$typeof:u,_currentValue:a,_currentValue2:a,_threadCount:0,Provider:null,Consumer:null,_defaultValue:null,_globalName:null};a.Provider={$$typeof:t,_context:a};return a.Consumer=a};exports.createElement=M;exports.createFactory=function(a){var b=M.bind(null,a);b.type=a;return b};exports.createRef=function(){return{current:null}};
exports.forwardRef=function(a){return{$$typeof:v,render:a}};exports.isValidElement=O;exports.lazy=function(a){return{$$typeof:y,_payload:{_status:-1,_result:a},_init:T}};exports.memo=function(a,b){return{$$typeof:x,type:a,compare:void 0===b?null:b}};exports.startTransition=function(a){var b=V.transition;V.transition={};try{a()}finally{V.transition=b}};exports.unstable_act=X;exports.useCallback=function(a,b){return U.current.useCallback(a,b)};exports.useContext=function(a){return U.current.useContext(a)};
exports.useDebugValue=function(){};exports.useDeferredValue=function(a){return U.current.useDeferredValue(a)};exports.useEffect=function(a,b){return U.current.useEffect(a,b)};exports.useId=function(){return U.current.useId()};exports.useImperativeHandle=function(a,b,e){return U.current.useImperativeHandle(a,b,e)};exports.useInsertionEffect=function(a,b){return U.current.useInsertionEffect(a,b)};exports.useLayoutEffect=function(a,b){return U.current.useLayoutEffect(a,b)};
exports.useMemo=function(a,b){return U.current.useMemo(a,b)};exports.useReducer=function(a,b,e){return U.current.useReducer(a,b,e)};exports.useRef=function(a){return U.current.useRef(a)};exports.useState=function(a){return U.current.useState(a)};exports.useSyncExternalStore=function(a,b,e){return U.current.useSyncExternalStore(a,b,e)};exports.useTransition=function(){return U.current.useTransition()};exports.version="18.3.1";


/***/ }),

/***/ 463:
/***/ ((__unused_webpack_module, exports) => {

/**
 * @license React
 * scheduler.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
function f(a,b){var c=a.length;a.push(b);a:for(;0<c;){var d=c-1>>>1,e=a[d];if(0<g(e,b))a[d]=b,a[c]=e,c=d;else break a}}function h(a){return 0===a.length?null:a[0]}function k(a){if(0===a.length)return null;var b=a[0],c=a.pop();if(c!==b){a[0]=c;a:for(var d=0,e=a.length,w=e>>>1;d<w;){var m=2*(d+1)-1,C=a[m],n=m+1,x=a[n];if(0>g(C,c))n<e&&0>g(x,C)?(a[d]=x,a[n]=c,d=n):(a[d]=C,a[m]=c,d=m);else if(n<e&&0>g(x,c))a[d]=x,a[n]=c,d=n;else break a}}return b}
function g(a,b){var c=a.sortIndex-b.sortIndex;return 0!==c?c:a.id-b.id}if("object"===typeof performance&&"function"===typeof performance.now){var l=performance;exports.unstable_now=function(){return l.now()}}else{var p=Date,q=p.now();exports.unstable_now=function(){return p.now()-q}}var r=[],t=[],u=1,v=null,y=3,z=!1,A=!1,B=!1,D="function"===typeof setTimeout?setTimeout:null,E="function"===typeof clearTimeout?clearTimeout:null,F="undefined"!==typeof setImmediate?setImmediate:null;
"undefined"!==typeof navigator&&void 0!==navigator.scheduling&&void 0!==navigator.scheduling.isInputPending&&navigator.scheduling.isInputPending.bind(navigator.scheduling);function G(a){for(var b=h(t);null!==b;){if(null===b.callback)k(t);else if(b.startTime<=a)k(t),b.sortIndex=b.expirationTime,f(r,b);else break;b=h(t)}}function H(a){B=!1;G(a);if(!A)if(null!==h(r))A=!0,I(J);else{var b=h(t);null!==b&&K(H,b.startTime-a)}}
function J(a,b){A=!1;B&&(B=!1,E(L),L=-1);z=!0;var c=y;try{G(b);for(v=h(r);null!==v&&(!(v.expirationTime>b)||a&&!M());){var d=v.callback;if("function"===typeof d){v.callback=null;y=v.priorityLevel;var e=d(v.expirationTime<=b);b=exports.unstable_now();"function"===typeof e?v.callback=e:v===h(r)&&k(r);G(b)}else k(r);v=h(r)}if(null!==v)var w=!0;else{var m=h(t);null!==m&&K(H,m.startTime-b);w=!1}return w}finally{v=null,y=c,z=!1}}var N=!1,O=null,L=-1,P=5,Q=-1;
function M(){return exports.unstable_now()-Q<P?!1:!0}function R(){if(null!==O){var a=exports.unstable_now();Q=a;var b=!0;try{b=O(!0,a)}finally{b?S():(N=!1,O=null)}}else N=!1}var S;if("function"===typeof F)S=function(){F(R)};else if("undefined"!==typeof MessageChannel){var T=new MessageChannel,U=T.port2;T.port1.onmessage=R;S=function(){U.postMessage(null)}}else S=function(){D(R,0)};function I(a){O=a;N||(N=!0,S())}function K(a,b){L=D(function(){a(exports.unstable_now())},b)}
exports.unstable_IdlePriority=5;exports.unstable_ImmediatePriority=1;exports.unstable_LowPriority=4;exports.unstable_NormalPriority=3;exports.unstable_Profiling=null;exports.unstable_UserBlockingPriority=2;exports.unstable_cancelCallback=function(a){a.callback=null};exports.unstable_continueExecution=function(){A||z||(A=!0,I(J))};
exports.unstable_forceFrameRate=function(a){0>a||125<a?console.error("forceFrameRate takes a positive int between 0 and 125, forcing frame rates higher than 125 fps is not supported"):P=0<a?Math.floor(1E3/a):5};exports.unstable_getCurrentPriorityLevel=function(){return y};exports.unstable_getFirstCallbackNode=function(){return h(r)};exports.unstable_next=function(a){switch(y){case 1:case 2:case 3:var b=3;break;default:b=y}var c=y;y=b;try{return a()}finally{y=c}};exports.unstable_pauseExecution=function(){};
exports.unstable_requestPaint=function(){};exports.unstable_runWithPriority=function(a,b){switch(a){case 1:case 2:case 3:case 4:case 5:break;default:a=3}var c=y;y=a;try{return b()}finally{y=c}};
exports.unstable_scheduleCallback=function(a,b,c){var d=exports.unstable_now();"object"===typeof c&&null!==c?(c=c.delay,c="number"===typeof c&&0<c?d+c:d):c=d;switch(a){case 1:var e=-1;break;case 2:e=250;break;case 5:e=1073741823;break;case 4:e=1E4;break;default:e=5E3}e=c+e;a={id:u++,callback:b,priorityLevel:a,startTime:c,expirationTime:e,sortIndex:-1};c>d?(a.sortIndex=c,f(t,a),null===h(r)&&a===h(t)&&(B?(E(L),L=-1):B=!0,K(H,c-d))):(a.sortIndex=e,f(r,a),A||z||(A=!0,I(J)));return a};
exports.unstable_shouldYield=M;exports.unstable_wrapCallback=function(a){var b=y;return function(){var c=y;y=b;try{return a.apply(this,arguments)}finally{y=c}}};


/***/ }),

/***/ 540:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



if (true) {
  module.exports = __webpack_require__(287);
} else // removed by dead control flow
{}


/***/ }),

/***/ 551:
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {

/**
 * @license React
 * react-dom.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */
/*
 Modernizr 3.0.0pre (Custom Build) | MIT
*/
var aa=__webpack_require__(540),ca=__webpack_require__(982);function p(a){for(var b="https://reactjs.org/docs/error-decoder.html?invariant="+a,c=1;c<arguments.length;c++)b+="&args[]="+encodeURIComponent(arguments[c]);return"Minified React error #"+a+"; visit "+b+" for the full message or use the non-minified dev environment for full errors and additional helpful warnings."}var da=new Set,ea={};function fa(a,b){ha(a,b);ha(a+"Capture",b)}
function ha(a,b){ea[a]=b;for(a=0;a<b.length;a++)da.add(b[a])}
var ia=!("undefined"===typeof window||"undefined"===typeof window.document||"undefined"===typeof window.document.createElement),ja=Object.prototype.hasOwnProperty,ka=/^[:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD][:A-Z_a-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02FF\u0370-\u037D\u037F-\u1FFF\u200C-\u200D\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD\-.0-9\u00B7\u0300-\u036F\u203F-\u2040]*$/,la=
{},ma={};function oa(a){if(ja.call(ma,a))return!0;if(ja.call(la,a))return!1;if(ka.test(a))return ma[a]=!0;la[a]=!0;return!1}function pa(a,b,c,d){if(null!==c&&0===c.type)return!1;switch(typeof b){case "function":case "symbol":return!0;case "boolean":if(d)return!1;if(null!==c)return!c.acceptsBooleans;a=a.toLowerCase().slice(0,5);return"data-"!==a&&"aria-"!==a;default:return!1}}
function qa(a,b,c,d){if(null===b||"undefined"===typeof b||pa(a,b,c,d))return!0;if(d)return!1;if(null!==c)switch(c.type){case 3:return!b;case 4:return!1===b;case 5:return isNaN(b);case 6:return isNaN(b)||1>b}return!1}function v(a,b,c,d,e,f,g){this.acceptsBooleans=2===b||3===b||4===b;this.attributeName=d;this.attributeNamespace=e;this.mustUseProperty=c;this.propertyName=a;this.type=b;this.sanitizeURL=f;this.removeEmptyString=g}var z={};
"children dangerouslySetInnerHTML defaultValue defaultChecked innerHTML suppressContentEditableWarning suppressHydrationWarning style".split(" ").forEach(function(a){z[a]=new v(a,0,!1,a,null,!1,!1)});[["acceptCharset","accept-charset"],["className","class"],["htmlFor","for"],["httpEquiv","http-equiv"]].forEach(function(a){var b=a[0];z[b]=new v(b,1,!1,a[1],null,!1,!1)});["contentEditable","draggable","spellCheck","value"].forEach(function(a){z[a]=new v(a,2,!1,a.toLowerCase(),null,!1,!1)});
["autoReverse","externalResourcesRequired","focusable","preserveAlpha"].forEach(function(a){z[a]=new v(a,2,!1,a,null,!1,!1)});"allowFullScreen async autoFocus autoPlay controls default defer disabled disablePictureInPicture disableRemotePlayback formNoValidate hidden loop noModule noValidate open playsInline readOnly required reversed scoped seamless itemScope".split(" ").forEach(function(a){z[a]=new v(a,3,!1,a.toLowerCase(),null,!1,!1)});
["checked","multiple","muted","selected"].forEach(function(a){z[a]=new v(a,3,!0,a,null,!1,!1)});["capture","download"].forEach(function(a){z[a]=new v(a,4,!1,a,null,!1,!1)});["cols","rows","size","span"].forEach(function(a){z[a]=new v(a,6,!1,a,null,!1,!1)});["rowSpan","start"].forEach(function(a){z[a]=new v(a,5,!1,a.toLowerCase(),null,!1,!1)});var ra=/[\-:]([a-z])/g;function sa(a){return a[1].toUpperCase()}
"accent-height alignment-baseline arabic-form baseline-shift cap-height clip-path clip-rule color-interpolation color-interpolation-filters color-profile color-rendering dominant-baseline enable-background fill-opacity fill-rule flood-color flood-opacity font-family font-size font-size-adjust font-stretch font-style font-variant font-weight glyph-name glyph-orientation-horizontal glyph-orientation-vertical horiz-adv-x horiz-origin-x image-rendering letter-spacing lighting-color marker-end marker-mid marker-start overline-position overline-thickness paint-order panose-1 pointer-events rendering-intent shape-rendering stop-color stop-opacity strikethrough-position strikethrough-thickness stroke-dasharray stroke-dashoffset stroke-linecap stroke-linejoin stroke-miterlimit stroke-opacity stroke-width text-anchor text-decoration text-rendering underline-position underline-thickness unicode-bidi unicode-range units-per-em v-alphabetic v-hanging v-ideographic v-mathematical vector-effect vert-adv-y vert-origin-x vert-origin-y word-spacing writing-mode xmlns:xlink x-height".split(" ").forEach(function(a){var b=a.replace(ra,
sa);z[b]=new v(b,1,!1,a,null,!1,!1)});"xlink:actuate xlink:arcrole xlink:role xlink:show xlink:title xlink:type".split(" ").forEach(function(a){var b=a.replace(ra,sa);z[b]=new v(b,1,!1,a,"http://www.w3.org/1999/xlink",!1,!1)});["xml:base","xml:lang","xml:space"].forEach(function(a){var b=a.replace(ra,sa);z[b]=new v(b,1,!1,a,"http://www.w3.org/XML/1998/namespace",!1,!1)});["tabIndex","crossOrigin"].forEach(function(a){z[a]=new v(a,1,!1,a.toLowerCase(),null,!1,!1)});
z.xlinkHref=new v("xlinkHref",1,!1,"xlink:href","http://www.w3.org/1999/xlink",!0,!1);["src","href","action","formAction"].forEach(function(a){z[a]=new v(a,1,!1,a.toLowerCase(),null,!0,!0)});
function ta(a,b,c,d){var e=z.hasOwnProperty(b)?z[b]:null;if(null!==e?0!==e.type:d||!(2<b.length)||"o"!==b[0]&&"O"!==b[0]||"n"!==b[1]&&"N"!==b[1])qa(b,c,e,d)&&(c=null),d||null===e?oa(b)&&(null===c?a.removeAttribute(b):a.setAttribute(b,""+c)):e.mustUseProperty?a[e.propertyName]=null===c?3===e.type?!1:"":c:(b=e.attributeName,d=e.attributeNamespace,null===c?a.removeAttribute(b):(e=e.type,c=3===e||4===e&&!0===c?"":""+c,d?a.setAttributeNS(d,b,c):a.setAttribute(b,c)))}
var ua=aa.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED,va=Symbol.for("react.element"),wa=Symbol.for("react.portal"),ya=Symbol.for("react.fragment"),za=Symbol.for("react.strict_mode"),Aa=Symbol.for("react.profiler"),Ba=Symbol.for("react.provider"),Ca=Symbol.for("react.context"),Da=Symbol.for("react.forward_ref"),Ea=Symbol.for("react.suspense"),Fa=Symbol.for("react.suspense_list"),Ga=Symbol.for("react.memo"),Ha=Symbol.for("react.lazy");Symbol.for("react.scope");Symbol.for("react.debug_trace_mode");
var Ia=Symbol.for("react.offscreen");Symbol.for("react.legacy_hidden");Symbol.for("react.cache");Symbol.for("react.tracing_marker");var Ja=Symbol.iterator;function Ka(a){if(null===a||"object"!==typeof a)return null;a=Ja&&a[Ja]||a["@@iterator"];return"function"===typeof a?a:null}var A=Object.assign,La;function Ma(a){if(void 0===La)try{throw Error();}catch(c){var b=c.stack.trim().match(/\n( *(at )?)/);La=b&&b[1]||""}return"\n"+La+a}var Na=!1;
function Oa(a,b){if(!a||Na)return"";Na=!0;var c=Error.prepareStackTrace;Error.prepareStackTrace=void 0;try{if(b)if(b=function(){throw Error();},Object.defineProperty(b.prototype,"props",{set:function(){throw Error();}}),"object"===typeof Reflect&&Reflect.construct){try{Reflect.construct(b,[])}catch(l){var d=l}Reflect.construct(a,[],b)}else{try{b.call()}catch(l){d=l}a.call(b.prototype)}else{try{throw Error();}catch(l){d=l}a()}}catch(l){if(l&&d&&"string"===typeof l.stack){for(var e=l.stack.split("\n"),
f=d.stack.split("\n"),g=e.length-1,h=f.length-1;1<=g&&0<=h&&e[g]!==f[h];)h--;for(;1<=g&&0<=h;g--,h--)if(e[g]!==f[h]){if(1!==g||1!==h){do if(g--,h--,0>h||e[g]!==f[h]){var k="\n"+e[g].replace(" at new "," at ");a.displayName&&k.includes("<anonymous>")&&(k=k.replace("<anonymous>",a.displayName));return k}while(1<=g&&0<=h)}break}}}finally{Na=!1,Error.prepareStackTrace=c}return(a=a?a.displayName||a.name:"")?Ma(a):""}
function Pa(a){switch(a.tag){case 5:return Ma(a.type);case 16:return Ma("Lazy");case 13:return Ma("Suspense");case 19:return Ma("SuspenseList");case 0:case 2:case 15:return a=Oa(a.type,!1),a;case 11:return a=Oa(a.type.render,!1),a;case 1:return a=Oa(a.type,!0),a;default:return""}}
function Qa(a){if(null==a)return null;if("function"===typeof a)return a.displayName||a.name||null;if("string"===typeof a)return a;switch(a){case ya:return"Fragment";case wa:return"Portal";case Aa:return"Profiler";case za:return"StrictMode";case Ea:return"Suspense";case Fa:return"SuspenseList"}if("object"===typeof a)switch(a.$$typeof){case Ca:return(a.displayName||"Context")+".Consumer";case Ba:return(a._context.displayName||"Context")+".Provider";case Da:var b=a.render;a=a.displayName;a||(a=b.displayName||
b.name||"",a=""!==a?"ForwardRef("+a+")":"ForwardRef");return a;case Ga:return b=a.displayName||null,null!==b?b:Qa(a.type)||"Memo";case Ha:b=a._payload;a=a._init;try{return Qa(a(b))}catch(c){}}return null}
function Ra(a){var b=a.type;switch(a.tag){case 24:return"Cache";case 9:return(b.displayName||"Context")+".Consumer";case 10:return(b._context.displayName||"Context")+".Provider";case 18:return"DehydratedFragment";case 11:return a=b.render,a=a.displayName||a.name||"",b.displayName||(""!==a?"ForwardRef("+a+")":"ForwardRef");case 7:return"Fragment";case 5:return b;case 4:return"Portal";case 3:return"Root";case 6:return"Text";case 16:return Qa(b);case 8:return b===za?"StrictMode":"Mode";case 22:return"Offscreen";
case 12:return"Profiler";case 21:return"Scope";case 13:return"Suspense";case 19:return"SuspenseList";case 25:return"TracingMarker";case 1:case 0:case 17:case 2:case 14:case 15:if("function"===typeof b)return b.displayName||b.name||null;if("string"===typeof b)return b}return null}function Sa(a){switch(typeof a){case "boolean":case "number":case "string":case "undefined":return a;case "object":return a;default:return""}}
function Ta(a){var b=a.type;return(a=a.nodeName)&&"input"===a.toLowerCase()&&("checkbox"===b||"radio"===b)}
function Ua(a){var b=Ta(a)?"checked":"value",c=Object.getOwnPropertyDescriptor(a.constructor.prototype,b),d=""+a[b];if(!a.hasOwnProperty(b)&&"undefined"!==typeof c&&"function"===typeof c.get&&"function"===typeof c.set){var e=c.get,f=c.set;Object.defineProperty(a,b,{configurable:!0,get:function(){return e.call(this)},set:function(a){d=""+a;f.call(this,a)}});Object.defineProperty(a,b,{enumerable:c.enumerable});return{getValue:function(){return d},setValue:function(a){d=""+a},stopTracking:function(){a._valueTracker=
null;delete a[b]}}}}function Va(a){a._valueTracker||(a._valueTracker=Ua(a))}function Wa(a){if(!a)return!1;var b=a._valueTracker;if(!b)return!0;var c=b.getValue();var d="";a&&(d=Ta(a)?a.checked?"true":"false":a.value);a=d;return a!==c?(b.setValue(a),!0):!1}function Xa(a){a=a||("undefined"!==typeof document?document:void 0);if("undefined"===typeof a)return null;try{return a.activeElement||a.body}catch(b){return a.body}}
function Ya(a,b){var c=b.checked;return A({},b,{defaultChecked:void 0,defaultValue:void 0,value:void 0,checked:null!=c?c:a._wrapperState.initialChecked})}function Za(a,b){var c=null==b.defaultValue?"":b.defaultValue,d=null!=b.checked?b.checked:b.defaultChecked;c=Sa(null!=b.value?b.value:c);a._wrapperState={initialChecked:d,initialValue:c,controlled:"checkbox"===b.type||"radio"===b.type?null!=b.checked:null!=b.value}}function ab(a,b){b=b.checked;null!=b&&ta(a,"checked",b,!1)}
function bb(a,b){ab(a,b);var c=Sa(b.value),d=b.type;if(null!=c)if("number"===d){if(0===c&&""===a.value||a.value!=c)a.value=""+c}else a.value!==""+c&&(a.value=""+c);else if("submit"===d||"reset"===d){a.removeAttribute("value");return}b.hasOwnProperty("value")?cb(a,b.type,c):b.hasOwnProperty("defaultValue")&&cb(a,b.type,Sa(b.defaultValue));null==b.checked&&null!=b.defaultChecked&&(a.defaultChecked=!!b.defaultChecked)}
function db(a,b,c){if(b.hasOwnProperty("value")||b.hasOwnProperty("defaultValue")){var d=b.type;if(!("submit"!==d&&"reset"!==d||void 0!==b.value&&null!==b.value))return;b=""+a._wrapperState.initialValue;c||b===a.value||(a.value=b);a.defaultValue=b}c=a.name;""!==c&&(a.name="");a.defaultChecked=!!a._wrapperState.initialChecked;""!==c&&(a.name=c)}
function cb(a,b,c){if("number"!==b||Xa(a.ownerDocument)!==a)null==c?a.defaultValue=""+a._wrapperState.initialValue:a.defaultValue!==""+c&&(a.defaultValue=""+c)}var eb=Array.isArray;
function fb(a,b,c,d){a=a.options;if(b){b={};for(var e=0;e<c.length;e++)b["$"+c[e]]=!0;for(c=0;c<a.length;c++)e=b.hasOwnProperty("$"+a[c].value),a[c].selected!==e&&(a[c].selected=e),e&&d&&(a[c].defaultSelected=!0)}else{c=""+Sa(c);b=null;for(e=0;e<a.length;e++){if(a[e].value===c){a[e].selected=!0;d&&(a[e].defaultSelected=!0);return}null!==b||a[e].disabled||(b=a[e])}null!==b&&(b.selected=!0)}}
function gb(a,b){if(null!=b.dangerouslySetInnerHTML)throw Error(p(91));return A({},b,{value:void 0,defaultValue:void 0,children:""+a._wrapperState.initialValue})}function hb(a,b){var c=b.value;if(null==c){c=b.children;b=b.defaultValue;if(null!=c){if(null!=b)throw Error(p(92));if(eb(c)){if(1<c.length)throw Error(p(93));c=c[0]}b=c}null==b&&(b="");c=b}a._wrapperState={initialValue:Sa(c)}}
function ib(a,b){var c=Sa(b.value),d=Sa(b.defaultValue);null!=c&&(c=""+c,c!==a.value&&(a.value=c),null==b.defaultValue&&a.defaultValue!==c&&(a.defaultValue=c));null!=d&&(a.defaultValue=""+d)}function jb(a){var b=a.textContent;b===a._wrapperState.initialValue&&""!==b&&null!==b&&(a.value=b)}function kb(a){switch(a){case "svg":return"http://www.w3.org/2000/svg";case "math":return"http://www.w3.org/1998/Math/MathML";default:return"http://www.w3.org/1999/xhtml"}}
function lb(a,b){return null==a||"http://www.w3.org/1999/xhtml"===a?kb(b):"http://www.w3.org/2000/svg"===a&&"foreignObject"===b?"http://www.w3.org/1999/xhtml":a}
var mb,nb=function(a){return"undefined"!==typeof MSApp&&MSApp.execUnsafeLocalFunction?function(b,c,d,e){MSApp.execUnsafeLocalFunction(function(){return a(b,c,d,e)})}:a}(function(a,b){if("http://www.w3.org/2000/svg"!==a.namespaceURI||"innerHTML"in a)a.innerHTML=b;else{mb=mb||document.createElement("div");mb.innerHTML="<svg>"+b.valueOf().toString()+"</svg>";for(b=mb.firstChild;a.firstChild;)a.removeChild(a.firstChild);for(;b.firstChild;)a.appendChild(b.firstChild)}});
function ob(a,b){if(b){var c=a.firstChild;if(c&&c===a.lastChild&&3===c.nodeType){c.nodeValue=b;return}}a.textContent=b}
var pb={animationIterationCount:!0,aspectRatio:!0,borderImageOutset:!0,borderImageSlice:!0,borderImageWidth:!0,boxFlex:!0,boxFlexGroup:!0,boxOrdinalGroup:!0,columnCount:!0,columns:!0,flex:!0,flexGrow:!0,flexPositive:!0,flexShrink:!0,flexNegative:!0,flexOrder:!0,gridArea:!0,gridRow:!0,gridRowEnd:!0,gridRowSpan:!0,gridRowStart:!0,gridColumn:!0,gridColumnEnd:!0,gridColumnSpan:!0,gridColumnStart:!0,fontWeight:!0,lineClamp:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,tabSize:!0,widows:!0,zIndex:!0,
zoom:!0,fillOpacity:!0,floodOpacity:!0,stopOpacity:!0,strokeDasharray:!0,strokeDashoffset:!0,strokeMiterlimit:!0,strokeOpacity:!0,strokeWidth:!0},qb=["Webkit","ms","Moz","O"];Object.keys(pb).forEach(function(a){qb.forEach(function(b){b=b+a.charAt(0).toUpperCase()+a.substring(1);pb[b]=pb[a]})});function rb(a,b,c){return null==b||"boolean"===typeof b||""===b?"":c||"number"!==typeof b||0===b||pb.hasOwnProperty(a)&&pb[a]?(""+b).trim():b+"px"}
function sb(a,b){a=a.style;for(var c in b)if(b.hasOwnProperty(c)){var d=0===c.indexOf("--"),e=rb(c,b[c],d);"float"===c&&(c="cssFloat");d?a.setProperty(c,e):a[c]=e}}var tb=A({menuitem:!0},{area:!0,base:!0,br:!0,col:!0,embed:!0,hr:!0,img:!0,input:!0,keygen:!0,link:!0,meta:!0,param:!0,source:!0,track:!0,wbr:!0});
function ub(a,b){if(b){if(tb[a]&&(null!=b.children||null!=b.dangerouslySetInnerHTML))throw Error(p(137,a));if(null!=b.dangerouslySetInnerHTML){if(null!=b.children)throw Error(p(60));if("object"!==typeof b.dangerouslySetInnerHTML||!("__html"in b.dangerouslySetInnerHTML))throw Error(p(61));}if(null!=b.style&&"object"!==typeof b.style)throw Error(p(62));}}
function vb(a,b){if(-1===a.indexOf("-"))return"string"===typeof b.is;switch(a){case "annotation-xml":case "color-profile":case "font-face":case "font-face-src":case "font-face-uri":case "font-face-format":case "font-face-name":case "missing-glyph":return!1;default:return!0}}var wb=null;function xb(a){a=a.target||a.srcElement||window;a.correspondingUseElement&&(a=a.correspondingUseElement);return 3===a.nodeType?a.parentNode:a}var yb=null,zb=null,Ab=null;
function Bb(a){if(a=Cb(a)){if("function"!==typeof yb)throw Error(p(280));var b=a.stateNode;b&&(b=Db(b),yb(a.stateNode,a.type,b))}}function Eb(a){zb?Ab?Ab.push(a):Ab=[a]:zb=a}function Fb(){if(zb){var a=zb,b=Ab;Ab=zb=null;Bb(a);if(b)for(a=0;a<b.length;a++)Bb(b[a])}}function Gb(a,b){return a(b)}function Hb(){}var Ib=!1;function Jb(a,b,c){if(Ib)return a(b,c);Ib=!0;try{return Gb(a,b,c)}finally{if(Ib=!1,null!==zb||null!==Ab)Hb(),Fb()}}
function Kb(a,b){var c=a.stateNode;if(null===c)return null;var d=Db(c);if(null===d)return null;c=d[b];a:switch(b){case "onClick":case "onClickCapture":case "onDoubleClick":case "onDoubleClickCapture":case "onMouseDown":case "onMouseDownCapture":case "onMouseMove":case "onMouseMoveCapture":case "onMouseUp":case "onMouseUpCapture":case "onMouseEnter":(d=!d.disabled)||(a=a.type,d=!("button"===a||"input"===a||"select"===a||"textarea"===a));a=!d;break a;default:a=!1}if(a)return null;if(c&&"function"!==
typeof c)throw Error(p(231,b,typeof c));return c}var Lb=!1;if(ia)try{var Mb={};Object.defineProperty(Mb,"passive",{get:function(){Lb=!0}});window.addEventListener("test",Mb,Mb);window.removeEventListener("test",Mb,Mb)}catch(a){Lb=!1}function Nb(a,b,c,d,e,f,g,h,k){var l=Array.prototype.slice.call(arguments,3);try{b.apply(c,l)}catch(m){this.onError(m)}}var Ob=!1,Pb=null,Qb=!1,Rb=null,Sb={onError:function(a){Ob=!0;Pb=a}};function Tb(a,b,c,d,e,f,g,h,k){Ob=!1;Pb=null;Nb.apply(Sb,arguments)}
function Ub(a,b,c,d,e,f,g,h,k){Tb.apply(this,arguments);if(Ob){if(Ob){var l=Pb;Ob=!1;Pb=null}else throw Error(p(198));Qb||(Qb=!0,Rb=l)}}function Vb(a){var b=a,c=a;if(a.alternate)for(;b.return;)b=b.return;else{a=b;do b=a,0!==(b.flags&4098)&&(c=b.return),a=b.return;while(a)}return 3===b.tag?c:null}function Wb(a){if(13===a.tag){var b=a.memoizedState;null===b&&(a=a.alternate,null!==a&&(b=a.memoizedState));if(null!==b)return b.dehydrated}return null}function Xb(a){if(Vb(a)!==a)throw Error(p(188));}
function Yb(a){var b=a.alternate;if(!b){b=Vb(a);if(null===b)throw Error(p(188));return b!==a?null:a}for(var c=a,d=b;;){var e=c.return;if(null===e)break;var f=e.alternate;if(null===f){d=e.return;if(null!==d){c=d;continue}break}if(e.child===f.child){for(f=e.child;f;){if(f===c)return Xb(e),a;if(f===d)return Xb(e),b;f=f.sibling}throw Error(p(188));}if(c.return!==d.return)c=e,d=f;else{for(var g=!1,h=e.child;h;){if(h===c){g=!0;c=e;d=f;break}if(h===d){g=!0;d=e;c=f;break}h=h.sibling}if(!g){for(h=f.child;h;){if(h===
c){g=!0;c=f;d=e;break}if(h===d){g=!0;d=f;c=e;break}h=h.sibling}if(!g)throw Error(p(189));}}if(c.alternate!==d)throw Error(p(190));}if(3!==c.tag)throw Error(p(188));return c.stateNode.current===c?a:b}function Zb(a){a=Yb(a);return null!==a?$b(a):null}function $b(a){if(5===a.tag||6===a.tag)return a;for(a=a.child;null!==a;){var b=$b(a);if(null!==b)return b;a=a.sibling}return null}
var ac=ca.unstable_scheduleCallback,bc=ca.unstable_cancelCallback,cc=ca.unstable_shouldYield,dc=ca.unstable_requestPaint,B=ca.unstable_now,ec=ca.unstable_getCurrentPriorityLevel,fc=ca.unstable_ImmediatePriority,gc=ca.unstable_UserBlockingPriority,hc=ca.unstable_NormalPriority,ic=ca.unstable_LowPriority,jc=ca.unstable_IdlePriority,kc=null,lc=null;function mc(a){if(lc&&"function"===typeof lc.onCommitFiberRoot)try{lc.onCommitFiberRoot(kc,a,void 0,128===(a.current.flags&128))}catch(b){}}
var oc=Math.clz32?Math.clz32:nc,pc=Math.log,qc=Math.LN2;function nc(a){a>>>=0;return 0===a?32:31-(pc(a)/qc|0)|0}var rc=64,sc=4194304;
function tc(a){switch(a&-a){case 1:return 1;case 2:return 2;case 4:return 4;case 8:return 8;case 16:return 16;case 32:return 32;case 64:case 128:case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:case 262144:case 524288:case 1048576:case 2097152:return a&4194240;case 4194304:case 8388608:case 16777216:case 33554432:case 67108864:return a&130023424;case 134217728:return 134217728;case 268435456:return 268435456;case 536870912:return 536870912;case 1073741824:return 1073741824;
default:return a}}function uc(a,b){var c=a.pendingLanes;if(0===c)return 0;var d=0,e=a.suspendedLanes,f=a.pingedLanes,g=c&268435455;if(0!==g){var h=g&~e;0!==h?d=tc(h):(f&=g,0!==f&&(d=tc(f)))}else g=c&~e,0!==g?d=tc(g):0!==f&&(d=tc(f));if(0===d)return 0;if(0!==b&&b!==d&&0===(b&e)&&(e=d&-d,f=b&-b,e>=f||16===e&&0!==(f&4194240)))return b;0!==(d&4)&&(d|=c&16);b=a.entangledLanes;if(0!==b)for(a=a.entanglements,b&=d;0<b;)c=31-oc(b),e=1<<c,d|=a[c],b&=~e;return d}
function vc(a,b){switch(a){case 1:case 2:case 4:return b+250;case 8:case 16:case 32:case 64:case 128:case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:case 262144:case 524288:case 1048576:case 2097152:return b+5E3;case 4194304:case 8388608:case 16777216:case 33554432:case 67108864:return-1;case 134217728:case 268435456:case 536870912:case 1073741824:return-1;default:return-1}}
function wc(a,b){for(var c=a.suspendedLanes,d=a.pingedLanes,e=a.expirationTimes,f=a.pendingLanes;0<f;){var g=31-oc(f),h=1<<g,k=e[g];if(-1===k){if(0===(h&c)||0!==(h&d))e[g]=vc(h,b)}else k<=b&&(a.expiredLanes|=h);f&=~h}}function xc(a){a=a.pendingLanes&-1073741825;return 0!==a?a:a&1073741824?1073741824:0}function yc(){var a=rc;rc<<=1;0===(rc&4194240)&&(rc=64);return a}function zc(a){for(var b=[],c=0;31>c;c++)b.push(a);return b}
function Ac(a,b,c){a.pendingLanes|=b;536870912!==b&&(a.suspendedLanes=0,a.pingedLanes=0);a=a.eventTimes;b=31-oc(b);a[b]=c}function Bc(a,b){var c=a.pendingLanes&~b;a.pendingLanes=b;a.suspendedLanes=0;a.pingedLanes=0;a.expiredLanes&=b;a.mutableReadLanes&=b;a.entangledLanes&=b;b=a.entanglements;var d=a.eventTimes;for(a=a.expirationTimes;0<c;){var e=31-oc(c),f=1<<e;b[e]=0;d[e]=-1;a[e]=-1;c&=~f}}
function Cc(a,b){var c=a.entangledLanes|=b;for(a=a.entanglements;c;){var d=31-oc(c),e=1<<d;e&b|a[d]&b&&(a[d]|=b);c&=~e}}var C=0;function Dc(a){a&=-a;return 1<a?4<a?0!==(a&268435455)?16:536870912:4:1}var Ec,Fc,Gc,Hc,Ic,Jc=!1,Kc=[],Lc=null,Mc=null,Nc=null,Oc=new Map,Pc=new Map,Qc=[],Rc="mousedown mouseup touchcancel touchend touchstart auxclick dblclick pointercancel pointerdown pointerup dragend dragstart drop compositionend compositionstart keydown keypress keyup input textInput copy cut paste click change contextmenu reset submit".split(" ");
function Sc(a,b){switch(a){case "focusin":case "focusout":Lc=null;break;case "dragenter":case "dragleave":Mc=null;break;case "mouseover":case "mouseout":Nc=null;break;case "pointerover":case "pointerout":Oc.delete(b.pointerId);break;case "gotpointercapture":case "lostpointercapture":Pc.delete(b.pointerId)}}
function Tc(a,b,c,d,e,f){if(null===a||a.nativeEvent!==f)return a={blockedOn:b,domEventName:c,eventSystemFlags:d,nativeEvent:f,targetContainers:[e]},null!==b&&(b=Cb(b),null!==b&&Fc(b)),a;a.eventSystemFlags|=d;b=a.targetContainers;null!==e&&-1===b.indexOf(e)&&b.push(e);return a}
function Uc(a,b,c,d,e){switch(b){case "focusin":return Lc=Tc(Lc,a,b,c,d,e),!0;case "dragenter":return Mc=Tc(Mc,a,b,c,d,e),!0;case "mouseover":return Nc=Tc(Nc,a,b,c,d,e),!0;case "pointerover":var f=e.pointerId;Oc.set(f,Tc(Oc.get(f)||null,a,b,c,d,e));return!0;case "gotpointercapture":return f=e.pointerId,Pc.set(f,Tc(Pc.get(f)||null,a,b,c,d,e)),!0}return!1}
function Vc(a){var b=Wc(a.target);if(null!==b){var c=Vb(b);if(null!==c)if(b=c.tag,13===b){if(b=Wb(c),null!==b){a.blockedOn=b;Ic(a.priority,function(){Gc(c)});return}}else if(3===b&&c.stateNode.current.memoizedState.isDehydrated){a.blockedOn=3===c.tag?c.stateNode.containerInfo:null;return}}a.blockedOn=null}
function Xc(a){if(null!==a.blockedOn)return!1;for(var b=a.targetContainers;0<b.length;){var c=Yc(a.domEventName,a.eventSystemFlags,b[0],a.nativeEvent);if(null===c){c=a.nativeEvent;var d=new c.constructor(c.type,c);wb=d;c.target.dispatchEvent(d);wb=null}else return b=Cb(c),null!==b&&Fc(b),a.blockedOn=c,!1;b.shift()}return!0}function Zc(a,b,c){Xc(a)&&c.delete(b)}function $c(){Jc=!1;null!==Lc&&Xc(Lc)&&(Lc=null);null!==Mc&&Xc(Mc)&&(Mc=null);null!==Nc&&Xc(Nc)&&(Nc=null);Oc.forEach(Zc);Pc.forEach(Zc)}
function ad(a,b){a.blockedOn===b&&(a.blockedOn=null,Jc||(Jc=!0,ca.unstable_scheduleCallback(ca.unstable_NormalPriority,$c)))}
function bd(a){function b(b){return ad(b,a)}if(0<Kc.length){ad(Kc[0],a);for(var c=1;c<Kc.length;c++){var d=Kc[c];d.blockedOn===a&&(d.blockedOn=null)}}null!==Lc&&ad(Lc,a);null!==Mc&&ad(Mc,a);null!==Nc&&ad(Nc,a);Oc.forEach(b);Pc.forEach(b);for(c=0;c<Qc.length;c++)d=Qc[c],d.blockedOn===a&&(d.blockedOn=null);for(;0<Qc.length&&(c=Qc[0],null===c.blockedOn);)Vc(c),null===c.blockedOn&&Qc.shift()}var cd=ua.ReactCurrentBatchConfig,dd=!0;
function ed(a,b,c,d){var e=C,f=cd.transition;cd.transition=null;try{C=1,fd(a,b,c,d)}finally{C=e,cd.transition=f}}function gd(a,b,c,d){var e=C,f=cd.transition;cd.transition=null;try{C=4,fd(a,b,c,d)}finally{C=e,cd.transition=f}}
function fd(a,b,c,d){if(dd){var e=Yc(a,b,c,d);if(null===e)hd(a,b,d,id,c),Sc(a,d);else if(Uc(e,a,b,c,d))d.stopPropagation();else if(Sc(a,d),b&4&&-1<Rc.indexOf(a)){for(;null!==e;){var f=Cb(e);null!==f&&Ec(f);f=Yc(a,b,c,d);null===f&&hd(a,b,d,id,c);if(f===e)break;e=f}null!==e&&d.stopPropagation()}else hd(a,b,d,null,c)}}var id=null;
function Yc(a,b,c,d){id=null;a=xb(d);a=Wc(a);if(null!==a)if(b=Vb(a),null===b)a=null;else if(c=b.tag,13===c){a=Wb(b);if(null!==a)return a;a=null}else if(3===c){if(b.stateNode.current.memoizedState.isDehydrated)return 3===b.tag?b.stateNode.containerInfo:null;a=null}else b!==a&&(a=null);id=a;return null}
function jd(a){switch(a){case "cancel":case "click":case "close":case "contextmenu":case "copy":case "cut":case "auxclick":case "dblclick":case "dragend":case "dragstart":case "drop":case "focusin":case "focusout":case "input":case "invalid":case "keydown":case "keypress":case "keyup":case "mousedown":case "mouseup":case "paste":case "pause":case "play":case "pointercancel":case "pointerdown":case "pointerup":case "ratechange":case "reset":case "resize":case "seeked":case "submit":case "touchcancel":case "touchend":case "touchstart":case "volumechange":case "change":case "selectionchange":case "textInput":case "compositionstart":case "compositionend":case "compositionupdate":case "beforeblur":case "afterblur":case "beforeinput":case "blur":case "fullscreenchange":case "focus":case "hashchange":case "popstate":case "select":case "selectstart":return 1;case "drag":case "dragenter":case "dragexit":case "dragleave":case "dragover":case "mousemove":case "mouseout":case "mouseover":case "pointermove":case "pointerout":case "pointerover":case "scroll":case "toggle":case "touchmove":case "wheel":case "mouseenter":case "mouseleave":case "pointerenter":case "pointerleave":return 4;
case "message":switch(ec()){case fc:return 1;case gc:return 4;case hc:case ic:return 16;case jc:return 536870912;default:return 16}default:return 16}}var kd=null,ld=null,md=null;function nd(){if(md)return md;var a,b=ld,c=b.length,d,e="value"in kd?kd.value:kd.textContent,f=e.length;for(a=0;a<c&&b[a]===e[a];a++);var g=c-a;for(d=1;d<=g&&b[c-d]===e[f-d];d++);return md=e.slice(a,1<d?1-d:void 0)}
function od(a){var b=a.keyCode;"charCode"in a?(a=a.charCode,0===a&&13===b&&(a=13)):a=b;10===a&&(a=13);return 32<=a||13===a?a:0}function pd(){return!0}function qd(){return!1}
function rd(a){function b(b,d,e,f,g){this._reactName=b;this._targetInst=e;this.type=d;this.nativeEvent=f;this.target=g;this.currentTarget=null;for(var c in a)a.hasOwnProperty(c)&&(b=a[c],this[c]=b?b(f):f[c]);this.isDefaultPrevented=(null!=f.defaultPrevented?f.defaultPrevented:!1===f.returnValue)?pd:qd;this.isPropagationStopped=qd;return this}A(b.prototype,{preventDefault:function(){this.defaultPrevented=!0;var a=this.nativeEvent;a&&(a.preventDefault?a.preventDefault():"unknown"!==typeof a.returnValue&&
(a.returnValue=!1),this.isDefaultPrevented=pd)},stopPropagation:function(){var a=this.nativeEvent;a&&(a.stopPropagation?a.stopPropagation():"unknown"!==typeof a.cancelBubble&&(a.cancelBubble=!0),this.isPropagationStopped=pd)},persist:function(){},isPersistent:pd});return b}
var sd={eventPhase:0,bubbles:0,cancelable:0,timeStamp:function(a){return a.timeStamp||Date.now()},defaultPrevented:0,isTrusted:0},td=rd(sd),ud=A({},sd,{view:0,detail:0}),vd=rd(ud),wd,xd,yd,Ad=A({},ud,{screenX:0,screenY:0,clientX:0,clientY:0,pageX:0,pageY:0,ctrlKey:0,shiftKey:0,altKey:0,metaKey:0,getModifierState:zd,button:0,buttons:0,relatedTarget:function(a){return void 0===a.relatedTarget?a.fromElement===a.srcElement?a.toElement:a.fromElement:a.relatedTarget},movementX:function(a){if("movementX"in
a)return a.movementX;a!==yd&&(yd&&"mousemove"===a.type?(wd=a.screenX-yd.screenX,xd=a.screenY-yd.screenY):xd=wd=0,yd=a);return wd},movementY:function(a){return"movementY"in a?a.movementY:xd}}),Bd=rd(Ad),Cd=A({},Ad,{dataTransfer:0}),Dd=rd(Cd),Ed=A({},ud,{relatedTarget:0}),Fd=rd(Ed),Gd=A({},sd,{animationName:0,elapsedTime:0,pseudoElement:0}),Hd=rd(Gd),Id=A({},sd,{clipboardData:function(a){return"clipboardData"in a?a.clipboardData:window.clipboardData}}),Jd=rd(Id),Kd=A({},sd,{data:0}),Ld=rd(Kd),Md={Esc:"Escape",
Spacebar:" ",Left:"ArrowLeft",Up:"ArrowUp",Right:"ArrowRight",Down:"ArrowDown",Del:"Delete",Win:"OS",Menu:"ContextMenu",Apps:"ContextMenu",Scroll:"ScrollLock",MozPrintableKey:"Unidentified"},Nd={8:"Backspace",9:"Tab",12:"Clear",13:"Enter",16:"Shift",17:"Control",18:"Alt",19:"Pause",20:"CapsLock",27:"Escape",32:" ",33:"PageUp",34:"PageDown",35:"End",36:"Home",37:"ArrowLeft",38:"ArrowUp",39:"ArrowRight",40:"ArrowDown",45:"Insert",46:"Delete",112:"F1",113:"F2",114:"F3",115:"F4",116:"F5",117:"F6",118:"F7",
119:"F8",120:"F9",121:"F10",122:"F11",123:"F12",144:"NumLock",145:"ScrollLock",224:"Meta"},Od={Alt:"altKey",Control:"ctrlKey",Meta:"metaKey",Shift:"shiftKey"};function Pd(a){var b=this.nativeEvent;return b.getModifierState?b.getModifierState(a):(a=Od[a])?!!b[a]:!1}function zd(){return Pd}
var Qd=A({},ud,{key:function(a){if(a.key){var b=Md[a.key]||a.key;if("Unidentified"!==b)return b}return"keypress"===a.type?(a=od(a),13===a?"Enter":String.fromCharCode(a)):"keydown"===a.type||"keyup"===a.type?Nd[a.keyCode]||"Unidentified":""},code:0,location:0,ctrlKey:0,shiftKey:0,altKey:0,metaKey:0,repeat:0,locale:0,getModifierState:zd,charCode:function(a){return"keypress"===a.type?od(a):0},keyCode:function(a){return"keydown"===a.type||"keyup"===a.type?a.keyCode:0},which:function(a){return"keypress"===
a.type?od(a):"keydown"===a.type||"keyup"===a.type?a.keyCode:0}}),Rd=rd(Qd),Sd=A({},Ad,{pointerId:0,width:0,height:0,pressure:0,tangentialPressure:0,tiltX:0,tiltY:0,twist:0,pointerType:0,isPrimary:0}),Td=rd(Sd),Ud=A({},ud,{touches:0,targetTouches:0,changedTouches:0,altKey:0,metaKey:0,ctrlKey:0,shiftKey:0,getModifierState:zd}),Vd=rd(Ud),Wd=A({},sd,{propertyName:0,elapsedTime:0,pseudoElement:0}),Xd=rd(Wd),Yd=A({},Ad,{deltaX:function(a){return"deltaX"in a?a.deltaX:"wheelDeltaX"in a?-a.wheelDeltaX:0},
deltaY:function(a){return"deltaY"in a?a.deltaY:"wheelDeltaY"in a?-a.wheelDeltaY:"wheelDelta"in a?-a.wheelDelta:0},deltaZ:0,deltaMode:0}),Zd=rd(Yd),$d=[9,13,27,32],ae=ia&&"CompositionEvent"in window,be=null;ia&&"documentMode"in document&&(be=document.documentMode);var ce=ia&&"TextEvent"in window&&!be,de=ia&&(!ae||be&&8<be&&11>=be),ee=String.fromCharCode(32),fe=!1;
function ge(a,b){switch(a){case "keyup":return-1!==$d.indexOf(b.keyCode);case "keydown":return 229!==b.keyCode;case "keypress":case "mousedown":case "focusout":return!0;default:return!1}}function he(a){a=a.detail;return"object"===typeof a&&"data"in a?a.data:null}var ie=!1;function je(a,b){switch(a){case "compositionend":return he(b);case "keypress":if(32!==b.which)return null;fe=!0;return ee;case "textInput":return a=b.data,a===ee&&fe?null:a;default:return null}}
function ke(a,b){if(ie)return"compositionend"===a||!ae&&ge(a,b)?(a=nd(),md=ld=kd=null,ie=!1,a):null;switch(a){case "paste":return null;case "keypress":if(!(b.ctrlKey||b.altKey||b.metaKey)||b.ctrlKey&&b.altKey){if(b.char&&1<b.char.length)return b.char;if(b.which)return String.fromCharCode(b.which)}return null;case "compositionend":return de&&"ko"!==b.locale?null:b.data;default:return null}}
var le={color:!0,date:!0,datetime:!0,"datetime-local":!0,email:!0,month:!0,number:!0,password:!0,range:!0,search:!0,tel:!0,text:!0,time:!0,url:!0,week:!0};function me(a){var b=a&&a.nodeName&&a.nodeName.toLowerCase();return"input"===b?!!le[a.type]:"textarea"===b?!0:!1}function ne(a,b,c,d){Eb(d);b=oe(b,"onChange");0<b.length&&(c=new td("onChange","change",null,c,d),a.push({event:c,listeners:b}))}var pe=null,qe=null;function re(a){se(a,0)}function te(a){var b=ue(a);if(Wa(b))return a}
function ve(a,b){if("change"===a)return b}var we=!1;if(ia){var xe;if(ia){var ye="oninput"in document;if(!ye){var ze=document.createElement("div");ze.setAttribute("oninput","return;");ye="function"===typeof ze.oninput}xe=ye}else xe=!1;we=xe&&(!document.documentMode||9<document.documentMode)}function Ae(){pe&&(pe.detachEvent("onpropertychange",Be),qe=pe=null)}function Be(a){if("value"===a.propertyName&&te(qe)){var b=[];ne(b,qe,a,xb(a));Jb(re,b)}}
function Ce(a,b,c){"focusin"===a?(Ae(),pe=b,qe=c,pe.attachEvent("onpropertychange",Be)):"focusout"===a&&Ae()}function De(a){if("selectionchange"===a||"keyup"===a||"keydown"===a)return te(qe)}function Ee(a,b){if("click"===a)return te(b)}function Fe(a,b){if("input"===a||"change"===a)return te(b)}function Ge(a,b){return a===b&&(0!==a||1/a===1/b)||a!==a&&b!==b}var He="function"===typeof Object.is?Object.is:Ge;
function Ie(a,b){if(He(a,b))return!0;if("object"!==typeof a||null===a||"object"!==typeof b||null===b)return!1;var c=Object.keys(a),d=Object.keys(b);if(c.length!==d.length)return!1;for(d=0;d<c.length;d++){var e=c[d];if(!ja.call(b,e)||!He(a[e],b[e]))return!1}return!0}function Je(a){for(;a&&a.firstChild;)a=a.firstChild;return a}
function Ke(a,b){var c=Je(a);a=0;for(var d;c;){if(3===c.nodeType){d=a+c.textContent.length;if(a<=b&&d>=b)return{node:c,offset:b-a};a=d}a:{for(;c;){if(c.nextSibling){c=c.nextSibling;break a}c=c.parentNode}c=void 0}c=Je(c)}}function Le(a,b){return a&&b?a===b?!0:a&&3===a.nodeType?!1:b&&3===b.nodeType?Le(a,b.parentNode):"contains"in a?a.contains(b):a.compareDocumentPosition?!!(a.compareDocumentPosition(b)&16):!1:!1}
function Me(){for(var a=window,b=Xa();b instanceof a.HTMLIFrameElement;){try{var c="string"===typeof b.contentWindow.location.href}catch(d){c=!1}if(c)a=b.contentWindow;else break;b=Xa(a.document)}return b}function Ne(a){var b=a&&a.nodeName&&a.nodeName.toLowerCase();return b&&("input"===b&&("text"===a.type||"search"===a.type||"tel"===a.type||"url"===a.type||"password"===a.type)||"textarea"===b||"true"===a.contentEditable)}
function Oe(a){var b=Me(),c=a.focusedElem,d=a.selectionRange;if(b!==c&&c&&c.ownerDocument&&Le(c.ownerDocument.documentElement,c)){if(null!==d&&Ne(c))if(b=d.start,a=d.end,void 0===a&&(a=b),"selectionStart"in c)c.selectionStart=b,c.selectionEnd=Math.min(a,c.value.length);else if(a=(b=c.ownerDocument||document)&&b.defaultView||window,a.getSelection){a=a.getSelection();var e=c.textContent.length,f=Math.min(d.start,e);d=void 0===d.end?f:Math.min(d.end,e);!a.extend&&f>d&&(e=d,d=f,f=e);e=Ke(c,f);var g=Ke(c,
d);e&&g&&(1!==a.rangeCount||a.anchorNode!==e.node||a.anchorOffset!==e.offset||a.focusNode!==g.node||a.focusOffset!==g.offset)&&(b=b.createRange(),b.setStart(e.node,e.offset),a.removeAllRanges(),f>d?(a.addRange(b),a.extend(g.node,g.offset)):(b.setEnd(g.node,g.offset),a.addRange(b)))}b=[];for(a=c;a=a.parentNode;)1===a.nodeType&&b.push({element:a,left:a.scrollLeft,top:a.scrollTop});"function"===typeof c.focus&&c.focus();for(c=0;c<b.length;c++)a=b[c],a.element.scrollLeft=a.left,a.element.scrollTop=a.top}}
var Pe=ia&&"documentMode"in document&&11>=document.documentMode,Qe=null,Re=null,Se=null,Te=!1;
function Ue(a,b,c){var d=c.window===c?c.document:9===c.nodeType?c:c.ownerDocument;Te||null==Qe||Qe!==Xa(d)||(d=Qe,"selectionStart"in d&&Ne(d)?d={start:d.selectionStart,end:d.selectionEnd}:(d=(d.ownerDocument&&d.ownerDocument.defaultView||window).getSelection(),d={anchorNode:d.anchorNode,anchorOffset:d.anchorOffset,focusNode:d.focusNode,focusOffset:d.focusOffset}),Se&&Ie(Se,d)||(Se=d,d=oe(Re,"onSelect"),0<d.length&&(b=new td("onSelect","select",null,b,c),a.push({event:b,listeners:d}),b.target=Qe)))}
function Ve(a,b){var c={};c[a.toLowerCase()]=b.toLowerCase();c["Webkit"+a]="webkit"+b;c["Moz"+a]="moz"+b;return c}var We={animationend:Ve("Animation","AnimationEnd"),animationiteration:Ve("Animation","AnimationIteration"),animationstart:Ve("Animation","AnimationStart"),transitionend:Ve("Transition","TransitionEnd")},Xe={},Ye={};
ia&&(Ye=document.createElement("div").style,"AnimationEvent"in window||(delete We.animationend.animation,delete We.animationiteration.animation,delete We.animationstart.animation),"TransitionEvent"in window||delete We.transitionend.transition);function Ze(a){if(Xe[a])return Xe[a];if(!We[a])return a;var b=We[a],c;for(c in b)if(b.hasOwnProperty(c)&&c in Ye)return Xe[a]=b[c];return a}var $e=Ze("animationend"),af=Ze("animationiteration"),bf=Ze("animationstart"),cf=Ze("transitionend"),df=new Map,ef="abort auxClick cancel canPlay canPlayThrough click close contextMenu copy cut drag dragEnd dragEnter dragExit dragLeave dragOver dragStart drop durationChange emptied encrypted ended error gotPointerCapture input invalid keyDown keyPress keyUp load loadedData loadedMetadata loadStart lostPointerCapture mouseDown mouseMove mouseOut mouseOver mouseUp paste pause play playing pointerCancel pointerDown pointerMove pointerOut pointerOver pointerUp progress rateChange reset resize seeked seeking stalled submit suspend timeUpdate touchCancel touchEnd touchStart volumeChange scroll toggle touchMove waiting wheel".split(" ");
function ff(a,b){df.set(a,b);fa(b,[a])}for(var gf=0;gf<ef.length;gf++){var hf=ef[gf],jf=hf.toLowerCase(),kf=hf[0].toUpperCase()+hf.slice(1);ff(jf,"on"+kf)}ff($e,"onAnimationEnd");ff(af,"onAnimationIteration");ff(bf,"onAnimationStart");ff("dblclick","onDoubleClick");ff("focusin","onFocus");ff("focusout","onBlur");ff(cf,"onTransitionEnd");ha("onMouseEnter",["mouseout","mouseover"]);ha("onMouseLeave",["mouseout","mouseover"]);ha("onPointerEnter",["pointerout","pointerover"]);
ha("onPointerLeave",["pointerout","pointerover"]);fa("onChange","change click focusin focusout input keydown keyup selectionchange".split(" "));fa("onSelect","focusout contextmenu dragend focusin keydown keyup mousedown mouseup selectionchange".split(" "));fa("onBeforeInput",["compositionend","keypress","textInput","paste"]);fa("onCompositionEnd","compositionend focusout keydown keypress keyup mousedown".split(" "));fa("onCompositionStart","compositionstart focusout keydown keypress keyup mousedown".split(" "));
fa("onCompositionUpdate","compositionupdate focusout keydown keypress keyup mousedown".split(" "));var lf="abort canplay canplaythrough durationchange emptied encrypted ended error loadeddata loadedmetadata loadstart pause play playing progress ratechange resize seeked seeking stalled suspend timeupdate volumechange waiting".split(" "),mf=new Set("cancel close invalid load scroll toggle".split(" ").concat(lf));
function nf(a,b,c){var d=a.type||"unknown-event";a.currentTarget=c;Ub(d,b,void 0,a);a.currentTarget=null}
function se(a,b){b=0!==(b&4);for(var c=0;c<a.length;c++){var d=a[c],e=d.event;d=d.listeners;a:{var f=void 0;if(b)for(var g=d.length-1;0<=g;g--){var h=d[g],k=h.instance,l=h.currentTarget;h=h.listener;if(k!==f&&e.isPropagationStopped())break a;nf(e,h,l);f=k}else for(g=0;g<d.length;g++){h=d[g];k=h.instance;l=h.currentTarget;h=h.listener;if(k!==f&&e.isPropagationStopped())break a;nf(e,h,l);f=k}}}if(Qb)throw a=Rb,Qb=!1,Rb=null,a;}
function D(a,b){var c=b[of];void 0===c&&(c=b[of]=new Set);var d=a+"__bubble";c.has(d)||(pf(b,a,2,!1),c.add(d))}function qf(a,b,c){var d=0;b&&(d|=4);pf(c,a,d,b)}var rf="_reactListening"+Math.random().toString(36).slice(2);function sf(a){if(!a[rf]){a[rf]=!0;da.forEach(function(b){"selectionchange"!==b&&(mf.has(b)||qf(b,!1,a),qf(b,!0,a))});var b=9===a.nodeType?a:a.ownerDocument;null===b||b[rf]||(b[rf]=!0,qf("selectionchange",!1,b))}}
function pf(a,b,c,d){switch(jd(b)){case 1:var e=ed;break;case 4:e=gd;break;default:e=fd}c=e.bind(null,b,c,a);e=void 0;!Lb||"touchstart"!==b&&"touchmove"!==b&&"wheel"!==b||(e=!0);d?void 0!==e?a.addEventListener(b,c,{capture:!0,passive:e}):a.addEventListener(b,c,!0):void 0!==e?a.addEventListener(b,c,{passive:e}):a.addEventListener(b,c,!1)}
function hd(a,b,c,d,e){var f=d;if(0===(b&1)&&0===(b&2)&&null!==d)a:for(;;){if(null===d)return;var g=d.tag;if(3===g||4===g){var h=d.stateNode.containerInfo;if(h===e||8===h.nodeType&&h.parentNode===e)break;if(4===g)for(g=d.return;null!==g;){var k=g.tag;if(3===k||4===k)if(k=g.stateNode.containerInfo,k===e||8===k.nodeType&&k.parentNode===e)return;g=g.return}for(;null!==h;){g=Wc(h);if(null===g)return;k=g.tag;if(5===k||6===k){d=f=g;continue a}h=h.parentNode}}d=d.return}Jb(function(){var d=f,e=xb(c),g=[];
a:{var h=df.get(a);if(void 0!==h){var k=td,n=a;switch(a){case "keypress":if(0===od(c))break a;case "keydown":case "keyup":k=Rd;break;case "focusin":n="focus";k=Fd;break;case "focusout":n="blur";k=Fd;break;case "beforeblur":case "afterblur":k=Fd;break;case "click":if(2===c.button)break a;case "auxclick":case "dblclick":case "mousedown":case "mousemove":case "mouseup":case "mouseout":case "mouseover":case "contextmenu":k=Bd;break;case "drag":case "dragend":case "dragenter":case "dragexit":case "dragleave":case "dragover":case "dragstart":case "drop":k=
Dd;break;case "touchcancel":case "touchend":case "touchmove":case "touchstart":k=Vd;break;case $e:case af:case bf:k=Hd;break;case cf:k=Xd;break;case "scroll":k=vd;break;case "wheel":k=Zd;break;case "copy":case "cut":case "paste":k=Jd;break;case "gotpointercapture":case "lostpointercapture":case "pointercancel":case "pointerdown":case "pointermove":case "pointerout":case "pointerover":case "pointerup":k=Td}var t=0!==(b&4),J=!t&&"scroll"===a,x=t?null!==h?h+"Capture":null:h;t=[];for(var w=d,u;null!==
w;){u=w;var F=u.stateNode;5===u.tag&&null!==F&&(u=F,null!==x&&(F=Kb(w,x),null!=F&&t.push(tf(w,F,u))));if(J)break;w=w.return}0<t.length&&(h=new k(h,n,null,c,e),g.push({event:h,listeners:t}))}}if(0===(b&7)){a:{h="mouseover"===a||"pointerover"===a;k="mouseout"===a||"pointerout"===a;if(h&&c!==wb&&(n=c.relatedTarget||c.fromElement)&&(Wc(n)||n[uf]))break a;if(k||h){h=e.window===e?e:(h=e.ownerDocument)?h.defaultView||h.parentWindow:window;if(k){if(n=c.relatedTarget||c.toElement,k=d,n=n?Wc(n):null,null!==
n&&(J=Vb(n),n!==J||5!==n.tag&&6!==n.tag))n=null}else k=null,n=d;if(k!==n){t=Bd;F="onMouseLeave";x="onMouseEnter";w="mouse";if("pointerout"===a||"pointerover"===a)t=Td,F="onPointerLeave",x="onPointerEnter",w="pointer";J=null==k?h:ue(k);u=null==n?h:ue(n);h=new t(F,w+"leave",k,c,e);h.target=J;h.relatedTarget=u;F=null;Wc(e)===d&&(t=new t(x,w+"enter",n,c,e),t.target=u,t.relatedTarget=J,F=t);J=F;if(k&&n)b:{t=k;x=n;w=0;for(u=t;u;u=vf(u))w++;u=0;for(F=x;F;F=vf(F))u++;for(;0<w-u;)t=vf(t),w--;for(;0<u-w;)x=
vf(x),u--;for(;w--;){if(t===x||null!==x&&t===x.alternate)break b;t=vf(t);x=vf(x)}t=null}else t=null;null!==k&&wf(g,h,k,t,!1);null!==n&&null!==J&&wf(g,J,n,t,!0)}}}a:{h=d?ue(d):window;k=h.nodeName&&h.nodeName.toLowerCase();if("select"===k||"input"===k&&"file"===h.type)var na=ve;else if(me(h))if(we)na=Fe;else{na=De;var xa=Ce}else(k=h.nodeName)&&"input"===k.toLowerCase()&&("checkbox"===h.type||"radio"===h.type)&&(na=Ee);if(na&&(na=na(a,d))){ne(g,na,c,e);break a}xa&&xa(a,h,d);"focusout"===a&&(xa=h._wrapperState)&&
xa.controlled&&"number"===h.type&&cb(h,"number",h.value)}xa=d?ue(d):window;switch(a){case "focusin":if(me(xa)||"true"===xa.contentEditable)Qe=xa,Re=d,Se=null;break;case "focusout":Se=Re=Qe=null;break;case "mousedown":Te=!0;break;case "contextmenu":case "mouseup":case "dragend":Te=!1;Ue(g,c,e);break;case "selectionchange":if(Pe)break;case "keydown":case "keyup":Ue(g,c,e)}var $a;if(ae)b:{switch(a){case "compositionstart":var ba="onCompositionStart";break b;case "compositionend":ba="onCompositionEnd";
break b;case "compositionupdate":ba="onCompositionUpdate";break b}ba=void 0}else ie?ge(a,c)&&(ba="onCompositionEnd"):"keydown"===a&&229===c.keyCode&&(ba="onCompositionStart");ba&&(de&&"ko"!==c.locale&&(ie||"onCompositionStart"!==ba?"onCompositionEnd"===ba&&ie&&($a=nd()):(kd=e,ld="value"in kd?kd.value:kd.textContent,ie=!0)),xa=oe(d,ba),0<xa.length&&(ba=new Ld(ba,a,null,c,e),g.push({event:ba,listeners:xa}),$a?ba.data=$a:($a=he(c),null!==$a&&(ba.data=$a))));if($a=ce?je(a,c):ke(a,c))d=oe(d,"onBeforeInput"),
0<d.length&&(e=new Ld("onBeforeInput","beforeinput",null,c,e),g.push({event:e,listeners:d}),e.data=$a)}se(g,b)})}function tf(a,b,c){return{instance:a,listener:b,currentTarget:c}}function oe(a,b){for(var c=b+"Capture",d=[];null!==a;){var e=a,f=e.stateNode;5===e.tag&&null!==f&&(e=f,f=Kb(a,c),null!=f&&d.unshift(tf(a,f,e)),f=Kb(a,b),null!=f&&d.push(tf(a,f,e)));a=a.return}return d}function vf(a){if(null===a)return null;do a=a.return;while(a&&5!==a.tag);return a?a:null}
function wf(a,b,c,d,e){for(var f=b._reactName,g=[];null!==c&&c!==d;){var h=c,k=h.alternate,l=h.stateNode;if(null!==k&&k===d)break;5===h.tag&&null!==l&&(h=l,e?(k=Kb(c,f),null!=k&&g.unshift(tf(c,k,h))):e||(k=Kb(c,f),null!=k&&g.push(tf(c,k,h))));c=c.return}0!==g.length&&a.push({event:b,listeners:g})}var xf=/\r\n?/g,yf=/\u0000|\uFFFD/g;function zf(a){return("string"===typeof a?a:""+a).replace(xf,"\n").replace(yf,"")}function Af(a,b,c){b=zf(b);if(zf(a)!==b&&c)throw Error(p(425));}function Bf(){}
var Cf=null,Df=null;function Ef(a,b){return"textarea"===a||"noscript"===a||"string"===typeof b.children||"number"===typeof b.children||"object"===typeof b.dangerouslySetInnerHTML&&null!==b.dangerouslySetInnerHTML&&null!=b.dangerouslySetInnerHTML.__html}
var Ff="function"===typeof setTimeout?setTimeout:void 0,Gf="function"===typeof clearTimeout?clearTimeout:void 0,Hf="function"===typeof Promise?Promise:void 0,Jf="function"===typeof queueMicrotask?queueMicrotask:"undefined"!==typeof Hf?function(a){return Hf.resolve(null).then(a).catch(If)}:Ff;function If(a){setTimeout(function(){throw a;})}
function Kf(a,b){var c=b,d=0;do{var e=c.nextSibling;a.removeChild(c);if(e&&8===e.nodeType)if(c=e.data,"/$"===c){if(0===d){a.removeChild(e);bd(b);return}d--}else"$"!==c&&"$?"!==c&&"$!"!==c||d++;c=e}while(c);bd(b)}function Lf(a){for(;null!=a;a=a.nextSibling){var b=a.nodeType;if(1===b||3===b)break;if(8===b){b=a.data;if("$"===b||"$!"===b||"$?"===b)break;if("/$"===b)return null}}return a}
function Mf(a){a=a.previousSibling;for(var b=0;a;){if(8===a.nodeType){var c=a.data;if("$"===c||"$!"===c||"$?"===c){if(0===b)return a;b--}else"/$"===c&&b++}a=a.previousSibling}return null}var Nf=Math.random().toString(36).slice(2),Of="__reactFiber$"+Nf,Pf="__reactProps$"+Nf,uf="__reactContainer$"+Nf,of="__reactEvents$"+Nf,Qf="__reactListeners$"+Nf,Rf="__reactHandles$"+Nf;
function Wc(a){var b=a[Of];if(b)return b;for(var c=a.parentNode;c;){if(b=c[uf]||c[Of]){c=b.alternate;if(null!==b.child||null!==c&&null!==c.child)for(a=Mf(a);null!==a;){if(c=a[Of])return c;a=Mf(a)}return b}a=c;c=a.parentNode}return null}function Cb(a){a=a[Of]||a[uf];return!a||5!==a.tag&&6!==a.tag&&13!==a.tag&&3!==a.tag?null:a}function ue(a){if(5===a.tag||6===a.tag)return a.stateNode;throw Error(p(33));}function Db(a){return a[Pf]||null}var Sf=[],Tf=-1;function Uf(a){return{current:a}}
function E(a){0>Tf||(a.current=Sf[Tf],Sf[Tf]=null,Tf--)}function G(a,b){Tf++;Sf[Tf]=a.current;a.current=b}var Vf={},H=Uf(Vf),Wf=Uf(!1),Xf=Vf;function Yf(a,b){var c=a.type.contextTypes;if(!c)return Vf;var d=a.stateNode;if(d&&d.__reactInternalMemoizedUnmaskedChildContext===b)return d.__reactInternalMemoizedMaskedChildContext;var e={},f;for(f in c)e[f]=b[f];d&&(a=a.stateNode,a.__reactInternalMemoizedUnmaskedChildContext=b,a.__reactInternalMemoizedMaskedChildContext=e);return e}
function Zf(a){a=a.childContextTypes;return null!==a&&void 0!==a}function $f(){E(Wf);E(H)}function ag(a,b,c){if(H.current!==Vf)throw Error(p(168));G(H,b);G(Wf,c)}function bg(a,b,c){var d=a.stateNode;b=b.childContextTypes;if("function"!==typeof d.getChildContext)return c;d=d.getChildContext();for(var e in d)if(!(e in b))throw Error(p(108,Ra(a)||"Unknown",e));return A({},c,d)}
function cg(a){a=(a=a.stateNode)&&a.__reactInternalMemoizedMergedChildContext||Vf;Xf=H.current;G(H,a);G(Wf,Wf.current);return!0}function dg(a,b,c){var d=a.stateNode;if(!d)throw Error(p(169));c?(a=bg(a,b,Xf),d.__reactInternalMemoizedMergedChildContext=a,E(Wf),E(H),G(H,a)):E(Wf);G(Wf,c)}var eg=null,fg=!1,gg=!1;function hg(a){null===eg?eg=[a]:eg.push(a)}function ig(a){fg=!0;hg(a)}
function jg(){if(!gg&&null!==eg){gg=!0;var a=0,b=C;try{var c=eg;for(C=1;a<c.length;a++){var d=c[a];do d=d(!0);while(null!==d)}eg=null;fg=!1}catch(e){throw null!==eg&&(eg=eg.slice(a+1)),ac(fc,jg),e;}finally{C=b,gg=!1}}return null}var kg=[],lg=0,mg=null,ng=0,og=[],pg=0,qg=null,rg=1,sg="";function tg(a,b){kg[lg++]=ng;kg[lg++]=mg;mg=a;ng=b}
function ug(a,b,c){og[pg++]=rg;og[pg++]=sg;og[pg++]=qg;qg=a;var d=rg;a=sg;var e=32-oc(d)-1;d&=~(1<<e);c+=1;var f=32-oc(b)+e;if(30<f){var g=e-e%5;f=(d&(1<<g)-1).toString(32);d>>=g;e-=g;rg=1<<32-oc(b)+e|c<<e|d;sg=f+a}else rg=1<<f|c<<e|d,sg=a}function vg(a){null!==a.return&&(tg(a,1),ug(a,1,0))}function wg(a){for(;a===mg;)mg=kg[--lg],kg[lg]=null,ng=kg[--lg],kg[lg]=null;for(;a===qg;)qg=og[--pg],og[pg]=null,sg=og[--pg],og[pg]=null,rg=og[--pg],og[pg]=null}var xg=null,yg=null,I=!1,zg=null;
function Ag(a,b){var c=Bg(5,null,null,0);c.elementType="DELETED";c.stateNode=b;c.return=a;b=a.deletions;null===b?(a.deletions=[c],a.flags|=16):b.push(c)}
function Cg(a,b){switch(a.tag){case 5:var c=a.type;b=1!==b.nodeType||c.toLowerCase()!==b.nodeName.toLowerCase()?null:b;return null!==b?(a.stateNode=b,xg=a,yg=Lf(b.firstChild),!0):!1;case 6:return b=""===a.pendingProps||3!==b.nodeType?null:b,null!==b?(a.stateNode=b,xg=a,yg=null,!0):!1;case 13:return b=8!==b.nodeType?null:b,null!==b?(c=null!==qg?{id:rg,overflow:sg}:null,a.memoizedState={dehydrated:b,treeContext:c,retryLane:1073741824},c=Bg(18,null,null,0),c.stateNode=b,c.return=a,a.child=c,xg=a,yg=
null,!0):!1;default:return!1}}function Dg(a){return 0!==(a.mode&1)&&0===(a.flags&128)}function Eg(a){if(I){var b=yg;if(b){var c=b;if(!Cg(a,b)){if(Dg(a))throw Error(p(418));b=Lf(c.nextSibling);var d=xg;b&&Cg(a,b)?Ag(d,c):(a.flags=a.flags&-4097|2,I=!1,xg=a)}}else{if(Dg(a))throw Error(p(418));a.flags=a.flags&-4097|2;I=!1;xg=a}}}function Fg(a){for(a=a.return;null!==a&&5!==a.tag&&3!==a.tag&&13!==a.tag;)a=a.return;xg=a}
function Gg(a){if(a!==xg)return!1;if(!I)return Fg(a),I=!0,!1;var b;(b=3!==a.tag)&&!(b=5!==a.tag)&&(b=a.type,b="head"!==b&&"body"!==b&&!Ef(a.type,a.memoizedProps));if(b&&(b=yg)){if(Dg(a))throw Hg(),Error(p(418));for(;b;)Ag(a,b),b=Lf(b.nextSibling)}Fg(a);if(13===a.tag){a=a.memoizedState;a=null!==a?a.dehydrated:null;if(!a)throw Error(p(317));a:{a=a.nextSibling;for(b=0;a;){if(8===a.nodeType){var c=a.data;if("/$"===c){if(0===b){yg=Lf(a.nextSibling);break a}b--}else"$"!==c&&"$!"!==c&&"$?"!==c||b++}a=a.nextSibling}yg=
null}}else yg=xg?Lf(a.stateNode.nextSibling):null;return!0}function Hg(){for(var a=yg;a;)a=Lf(a.nextSibling)}function Ig(){yg=xg=null;I=!1}function Jg(a){null===zg?zg=[a]:zg.push(a)}var Kg=ua.ReactCurrentBatchConfig;
function Lg(a,b,c){a=c.ref;if(null!==a&&"function"!==typeof a&&"object"!==typeof a){if(c._owner){c=c._owner;if(c){if(1!==c.tag)throw Error(p(309));var d=c.stateNode}if(!d)throw Error(p(147,a));var e=d,f=""+a;if(null!==b&&null!==b.ref&&"function"===typeof b.ref&&b.ref._stringRef===f)return b.ref;b=function(a){var b=e.refs;null===a?delete b[f]:b[f]=a};b._stringRef=f;return b}if("string"!==typeof a)throw Error(p(284));if(!c._owner)throw Error(p(290,a));}return a}
function Mg(a,b){a=Object.prototype.toString.call(b);throw Error(p(31,"[object Object]"===a?"object with keys {"+Object.keys(b).join(", ")+"}":a));}function Ng(a){var b=a._init;return b(a._payload)}
function Og(a){function b(b,c){if(a){var d=b.deletions;null===d?(b.deletions=[c],b.flags|=16):d.push(c)}}function c(c,d){if(!a)return null;for(;null!==d;)b(c,d),d=d.sibling;return null}function d(a,b){for(a=new Map;null!==b;)null!==b.key?a.set(b.key,b):a.set(b.index,b),b=b.sibling;return a}function e(a,b){a=Pg(a,b);a.index=0;a.sibling=null;return a}function f(b,c,d){b.index=d;if(!a)return b.flags|=1048576,c;d=b.alternate;if(null!==d)return d=d.index,d<c?(b.flags|=2,c):d;b.flags|=2;return c}function g(b){a&&
null===b.alternate&&(b.flags|=2);return b}function h(a,b,c,d){if(null===b||6!==b.tag)return b=Qg(c,a.mode,d),b.return=a,b;b=e(b,c);b.return=a;return b}function k(a,b,c,d){var f=c.type;if(f===ya)return m(a,b,c.props.children,d,c.key);if(null!==b&&(b.elementType===f||"object"===typeof f&&null!==f&&f.$$typeof===Ha&&Ng(f)===b.type))return d=e(b,c.props),d.ref=Lg(a,b,c),d.return=a,d;d=Rg(c.type,c.key,c.props,null,a.mode,d);d.ref=Lg(a,b,c);d.return=a;return d}function l(a,b,c,d){if(null===b||4!==b.tag||
b.stateNode.containerInfo!==c.containerInfo||b.stateNode.implementation!==c.implementation)return b=Sg(c,a.mode,d),b.return=a,b;b=e(b,c.children||[]);b.return=a;return b}function m(a,b,c,d,f){if(null===b||7!==b.tag)return b=Tg(c,a.mode,d,f),b.return=a,b;b=e(b,c);b.return=a;return b}function q(a,b,c){if("string"===typeof b&&""!==b||"number"===typeof b)return b=Qg(""+b,a.mode,c),b.return=a,b;if("object"===typeof b&&null!==b){switch(b.$$typeof){case va:return c=Rg(b.type,b.key,b.props,null,a.mode,c),
c.ref=Lg(a,null,b),c.return=a,c;case wa:return b=Sg(b,a.mode,c),b.return=a,b;case Ha:var d=b._init;return q(a,d(b._payload),c)}if(eb(b)||Ka(b))return b=Tg(b,a.mode,c,null),b.return=a,b;Mg(a,b)}return null}function r(a,b,c,d){var e=null!==b?b.key:null;if("string"===typeof c&&""!==c||"number"===typeof c)return null!==e?null:h(a,b,""+c,d);if("object"===typeof c&&null!==c){switch(c.$$typeof){case va:return c.key===e?k(a,b,c,d):null;case wa:return c.key===e?l(a,b,c,d):null;case Ha:return e=c._init,r(a,
b,e(c._payload),d)}if(eb(c)||Ka(c))return null!==e?null:m(a,b,c,d,null);Mg(a,c)}return null}function y(a,b,c,d,e){if("string"===typeof d&&""!==d||"number"===typeof d)return a=a.get(c)||null,h(b,a,""+d,e);if("object"===typeof d&&null!==d){switch(d.$$typeof){case va:return a=a.get(null===d.key?c:d.key)||null,k(b,a,d,e);case wa:return a=a.get(null===d.key?c:d.key)||null,l(b,a,d,e);case Ha:var f=d._init;return y(a,b,c,f(d._payload),e)}if(eb(d)||Ka(d))return a=a.get(c)||null,m(b,a,d,e,null);Mg(b,d)}return null}
function n(e,g,h,k){for(var l=null,m=null,u=g,w=g=0,x=null;null!==u&&w<h.length;w++){u.index>w?(x=u,u=null):x=u.sibling;var n=r(e,u,h[w],k);if(null===n){null===u&&(u=x);break}a&&u&&null===n.alternate&&b(e,u);g=f(n,g,w);null===m?l=n:m.sibling=n;m=n;u=x}if(w===h.length)return c(e,u),I&&tg(e,w),l;if(null===u){for(;w<h.length;w++)u=q(e,h[w],k),null!==u&&(g=f(u,g,w),null===m?l=u:m.sibling=u,m=u);I&&tg(e,w);return l}for(u=d(e,u);w<h.length;w++)x=y(u,e,w,h[w],k),null!==x&&(a&&null!==x.alternate&&u.delete(null===
x.key?w:x.key),g=f(x,g,w),null===m?l=x:m.sibling=x,m=x);a&&u.forEach(function(a){return b(e,a)});I&&tg(e,w);return l}function t(e,g,h,k){var l=Ka(h);if("function"!==typeof l)throw Error(p(150));h=l.call(h);if(null==h)throw Error(p(151));for(var u=l=null,m=g,w=g=0,x=null,n=h.next();null!==m&&!n.done;w++,n=h.next()){m.index>w?(x=m,m=null):x=m.sibling;var t=r(e,m,n.value,k);if(null===t){null===m&&(m=x);break}a&&m&&null===t.alternate&&b(e,m);g=f(t,g,w);null===u?l=t:u.sibling=t;u=t;m=x}if(n.done)return c(e,
m),I&&tg(e,w),l;if(null===m){for(;!n.done;w++,n=h.next())n=q(e,n.value,k),null!==n&&(g=f(n,g,w),null===u?l=n:u.sibling=n,u=n);I&&tg(e,w);return l}for(m=d(e,m);!n.done;w++,n=h.next())n=y(m,e,w,n.value,k),null!==n&&(a&&null!==n.alternate&&m.delete(null===n.key?w:n.key),g=f(n,g,w),null===u?l=n:u.sibling=n,u=n);a&&m.forEach(function(a){return b(e,a)});I&&tg(e,w);return l}function J(a,d,f,h){"object"===typeof f&&null!==f&&f.type===ya&&null===f.key&&(f=f.props.children);if("object"===typeof f&&null!==f){switch(f.$$typeof){case va:a:{for(var k=
f.key,l=d;null!==l;){if(l.key===k){k=f.type;if(k===ya){if(7===l.tag){c(a,l.sibling);d=e(l,f.props.children);d.return=a;a=d;break a}}else if(l.elementType===k||"object"===typeof k&&null!==k&&k.$$typeof===Ha&&Ng(k)===l.type){c(a,l.sibling);d=e(l,f.props);d.ref=Lg(a,l,f);d.return=a;a=d;break a}c(a,l);break}else b(a,l);l=l.sibling}f.type===ya?(d=Tg(f.props.children,a.mode,h,f.key),d.return=a,a=d):(h=Rg(f.type,f.key,f.props,null,a.mode,h),h.ref=Lg(a,d,f),h.return=a,a=h)}return g(a);case wa:a:{for(l=f.key;null!==
d;){if(d.key===l)if(4===d.tag&&d.stateNode.containerInfo===f.containerInfo&&d.stateNode.implementation===f.implementation){c(a,d.sibling);d=e(d,f.children||[]);d.return=a;a=d;break a}else{c(a,d);break}else b(a,d);d=d.sibling}d=Sg(f,a.mode,h);d.return=a;a=d}return g(a);case Ha:return l=f._init,J(a,d,l(f._payload),h)}if(eb(f))return n(a,d,f,h);if(Ka(f))return t(a,d,f,h);Mg(a,f)}return"string"===typeof f&&""!==f||"number"===typeof f?(f=""+f,null!==d&&6===d.tag?(c(a,d.sibling),d=e(d,f),d.return=a,a=d):
(c(a,d),d=Qg(f,a.mode,h),d.return=a,a=d),g(a)):c(a,d)}return J}var Ug=Og(!0),Vg=Og(!1),Wg=Uf(null),Xg=null,Yg=null,Zg=null;function $g(){Zg=Yg=Xg=null}function ah(a){var b=Wg.current;E(Wg);a._currentValue=b}function bh(a,b,c){for(;null!==a;){var d=a.alternate;(a.childLanes&b)!==b?(a.childLanes|=b,null!==d&&(d.childLanes|=b)):null!==d&&(d.childLanes&b)!==b&&(d.childLanes|=b);if(a===c)break;a=a.return}}
function ch(a,b){Xg=a;Zg=Yg=null;a=a.dependencies;null!==a&&null!==a.firstContext&&(0!==(a.lanes&b)&&(dh=!0),a.firstContext=null)}function eh(a){var b=a._currentValue;if(Zg!==a)if(a={context:a,memoizedValue:b,next:null},null===Yg){if(null===Xg)throw Error(p(308));Yg=a;Xg.dependencies={lanes:0,firstContext:a}}else Yg=Yg.next=a;return b}var fh=null;function gh(a){null===fh?fh=[a]:fh.push(a)}
function hh(a,b,c,d){var e=b.interleaved;null===e?(c.next=c,gh(b)):(c.next=e.next,e.next=c);b.interleaved=c;return ih(a,d)}function ih(a,b){a.lanes|=b;var c=a.alternate;null!==c&&(c.lanes|=b);c=a;for(a=a.return;null!==a;)a.childLanes|=b,c=a.alternate,null!==c&&(c.childLanes|=b),c=a,a=a.return;return 3===c.tag?c.stateNode:null}var jh=!1;function kh(a){a.updateQueue={baseState:a.memoizedState,firstBaseUpdate:null,lastBaseUpdate:null,shared:{pending:null,interleaved:null,lanes:0},effects:null}}
function lh(a,b){a=a.updateQueue;b.updateQueue===a&&(b.updateQueue={baseState:a.baseState,firstBaseUpdate:a.firstBaseUpdate,lastBaseUpdate:a.lastBaseUpdate,shared:a.shared,effects:a.effects})}function mh(a,b){return{eventTime:a,lane:b,tag:0,payload:null,callback:null,next:null}}
function nh(a,b,c){var d=a.updateQueue;if(null===d)return null;d=d.shared;if(0!==(K&2)){var e=d.pending;null===e?b.next=b:(b.next=e.next,e.next=b);d.pending=b;return ih(a,c)}e=d.interleaved;null===e?(b.next=b,gh(d)):(b.next=e.next,e.next=b);d.interleaved=b;return ih(a,c)}function oh(a,b,c){b=b.updateQueue;if(null!==b&&(b=b.shared,0!==(c&4194240))){var d=b.lanes;d&=a.pendingLanes;c|=d;b.lanes=c;Cc(a,c)}}
function ph(a,b){var c=a.updateQueue,d=a.alternate;if(null!==d&&(d=d.updateQueue,c===d)){var e=null,f=null;c=c.firstBaseUpdate;if(null!==c){do{var g={eventTime:c.eventTime,lane:c.lane,tag:c.tag,payload:c.payload,callback:c.callback,next:null};null===f?e=f=g:f=f.next=g;c=c.next}while(null!==c);null===f?e=f=b:f=f.next=b}else e=f=b;c={baseState:d.baseState,firstBaseUpdate:e,lastBaseUpdate:f,shared:d.shared,effects:d.effects};a.updateQueue=c;return}a=c.lastBaseUpdate;null===a?c.firstBaseUpdate=b:a.next=
b;c.lastBaseUpdate=b}
function qh(a,b,c,d){var e=a.updateQueue;jh=!1;var f=e.firstBaseUpdate,g=e.lastBaseUpdate,h=e.shared.pending;if(null!==h){e.shared.pending=null;var k=h,l=k.next;k.next=null;null===g?f=l:g.next=l;g=k;var m=a.alternate;null!==m&&(m=m.updateQueue,h=m.lastBaseUpdate,h!==g&&(null===h?m.firstBaseUpdate=l:h.next=l,m.lastBaseUpdate=k))}if(null!==f){var q=e.baseState;g=0;m=l=k=null;h=f;do{var r=h.lane,y=h.eventTime;if((d&r)===r){null!==m&&(m=m.next={eventTime:y,lane:0,tag:h.tag,payload:h.payload,callback:h.callback,
next:null});a:{var n=a,t=h;r=b;y=c;switch(t.tag){case 1:n=t.payload;if("function"===typeof n){q=n.call(y,q,r);break a}q=n;break a;case 3:n.flags=n.flags&-65537|128;case 0:n=t.payload;r="function"===typeof n?n.call(y,q,r):n;if(null===r||void 0===r)break a;q=A({},q,r);break a;case 2:jh=!0}}null!==h.callback&&0!==h.lane&&(a.flags|=64,r=e.effects,null===r?e.effects=[h]:r.push(h))}else y={eventTime:y,lane:r,tag:h.tag,payload:h.payload,callback:h.callback,next:null},null===m?(l=m=y,k=q):m=m.next=y,g|=r;
h=h.next;if(null===h)if(h=e.shared.pending,null===h)break;else r=h,h=r.next,r.next=null,e.lastBaseUpdate=r,e.shared.pending=null}while(1);null===m&&(k=q);e.baseState=k;e.firstBaseUpdate=l;e.lastBaseUpdate=m;b=e.shared.interleaved;if(null!==b){e=b;do g|=e.lane,e=e.next;while(e!==b)}else null===f&&(e.shared.lanes=0);rh|=g;a.lanes=g;a.memoizedState=q}}
function sh(a,b,c){a=b.effects;b.effects=null;if(null!==a)for(b=0;b<a.length;b++){var d=a[b],e=d.callback;if(null!==e){d.callback=null;d=c;if("function"!==typeof e)throw Error(p(191,e));e.call(d)}}}var th={},uh=Uf(th),vh=Uf(th),wh=Uf(th);function xh(a){if(a===th)throw Error(p(174));return a}
function yh(a,b){G(wh,b);G(vh,a);G(uh,th);a=b.nodeType;switch(a){case 9:case 11:b=(b=b.documentElement)?b.namespaceURI:lb(null,"");break;default:a=8===a?b.parentNode:b,b=a.namespaceURI||null,a=a.tagName,b=lb(b,a)}E(uh);G(uh,b)}function zh(){E(uh);E(vh);E(wh)}function Ah(a){xh(wh.current);var b=xh(uh.current);var c=lb(b,a.type);b!==c&&(G(vh,a),G(uh,c))}function Bh(a){vh.current===a&&(E(uh),E(vh))}var L=Uf(0);
function Ch(a){for(var b=a;null!==b;){if(13===b.tag){var c=b.memoizedState;if(null!==c&&(c=c.dehydrated,null===c||"$?"===c.data||"$!"===c.data))return b}else if(19===b.tag&&void 0!==b.memoizedProps.revealOrder){if(0!==(b.flags&128))return b}else if(null!==b.child){b.child.return=b;b=b.child;continue}if(b===a)break;for(;null===b.sibling;){if(null===b.return||b.return===a)return null;b=b.return}b.sibling.return=b.return;b=b.sibling}return null}var Dh=[];
function Eh(){for(var a=0;a<Dh.length;a++)Dh[a]._workInProgressVersionPrimary=null;Dh.length=0}var Fh=ua.ReactCurrentDispatcher,Gh=ua.ReactCurrentBatchConfig,Hh=0,M=null,N=null,O=null,Ih=!1,Jh=!1,Kh=0,Lh=0;function P(){throw Error(p(321));}function Mh(a,b){if(null===b)return!1;for(var c=0;c<b.length&&c<a.length;c++)if(!He(a[c],b[c]))return!1;return!0}
function Nh(a,b,c,d,e,f){Hh=f;M=b;b.memoizedState=null;b.updateQueue=null;b.lanes=0;Fh.current=null===a||null===a.memoizedState?Oh:Ph;a=c(d,e);if(Jh){f=0;do{Jh=!1;Kh=0;if(25<=f)throw Error(p(301));f+=1;O=N=null;b.updateQueue=null;Fh.current=Qh;a=c(d,e)}while(Jh)}Fh.current=Rh;b=null!==N&&null!==N.next;Hh=0;O=N=M=null;Ih=!1;if(b)throw Error(p(300));return a}function Sh(){var a=0!==Kh;Kh=0;return a}
function Th(){var a={memoizedState:null,baseState:null,baseQueue:null,queue:null,next:null};null===O?M.memoizedState=O=a:O=O.next=a;return O}function Uh(){if(null===N){var a=M.alternate;a=null!==a?a.memoizedState:null}else a=N.next;var b=null===O?M.memoizedState:O.next;if(null!==b)O=b,N=a;else{if(null===a)throw Error(p(310));N=a;a={memoizedState:N.memoizedState,baseState:N.baseState,baseQueue:N.baseQueue,queue:N.queue,next:null};null===O?M.memoizedState=O=a:O=O.next=a}return O}
function Vh(a,b){return"function"===typeof b?b(a):b}
function Wh(a){var b=Uh(),c=b.queue;if(null===c)throw Error(p(311));c.lastRenderedReducer=a;var d=N,e=d.baseQueue,f=c.pending;if(null!==f){if(null!==e){var g=e.next;e.next=f.next;f.next=g}d.baseQueue=e=f;c.pending=null}if(null!==e){f=e.next;d=d.baseState;var h=g=null,k=null,l=f;do{var m=l.lane;if((Hh&m)===m)null!==k&&(k=k.next={lane:0,action:l.action,hasEagerState:l.hasEagerState,eagerState:l.eagerState,next:null}),d=l.hasEagerState?l.eagerState:a(d,l.action);else{var q={lane:m,action:l.action,hasEagerState:l.hasEagerState,
eagerState:l.eagerState,next:null};null===k?(h=k=q,g=d):k=k.next=q;M.lanes|=m;rh|=m}l=l.next}while(null!==l&&l!==f);null===k?g=d:k.next=h;He(d,b.memoizedState)||(dh=!0);b.memoizedState=d;b.baseState=g;b.baseQueue=k;c.lastRenderedState=d}a=c.interleaved;if(null!==a){e=a;do f=e.lane,M.lanes|=f,rh|=f,e=e.next;while(e!==a)}else null===e&&(c.lanes=0);return[b.memoizedState,c.dispatch]}
function Xh(a){var b=Uh(),c=b.queue;if(null===c)throw Error(p(311));c.lastRenderedReducer=a;var d=c.dispatch,e=c.pending,f=b.memoizedState;if(null!==e){c.pending=null;var g=e=e.next;do f=a(f,g.action),g=g.next;while(g!==e);He(f,b.memoizedState)||(dh=!0);b.memoizedState=f;null===b.baseQueue&&(b.baseState=f);c.lastRenderedState=f}return[f,d]}function Yh(){}
function Zh(a,b){var c=M,d=Uh(),e=b(),f=!He(d.memoizedState,e);f&&(d.memoizedState=e,dh=!0);d=d.queue;$h(ai.bind(null,c,d,a),[a]);if(d.getSnapshot!==b||f||null!==O&&O.memoizedState.tag&1){c.flags|=2048;bi(9,ci.bind(null,c,d,e,b),void 0,null);if(null===Q)throw Error(p(349));0!==(Hh&30)||di(c,b,e)}return e}function di(a,b,c){a.flags|=16384;a={getSnapshot:b,value:c};b=M.updateQueue;null===b?(b={lastEffect:null,stores:null},M.updateQueue=b,b.stores=[a]):(c=b.stores,null===c?b.stores=[a]:c.push(a))}
function ci(a,b,c,d){b.value=c;b.getSnapshot=d;ei(b)&&fi(a)}function ai(a,b,c){return c(function(){ei(b)&&fi(a)})}function ei(a){var b=a.getSnapshot;a=a.value;try{var c=b();return!He(a,c)}catch(d){return!0}}function fi(a){var b=ih(a,1);null!==b&&gi(b,a,1,-1)}
function hi(a){var b=Th();"function"===typeof a&&(a=a());b.memoizedState=b.baseState=a;a={pending:null,interleaved:null,lanes:0,dispatch:null,lastRenderedReducer:Vh,lastRenderedState:a};b.queue=a;a=a.dispatch=ii.bind(null,M,a);return[b.memoizedState,a]}
function bi(a,b,c,d){a={tag:a,create:b,destroy:c,deps:d,next:null};b=M.updateQueue;null===b?(b={lastEffect:null,stores:null},M.updateQueue=b,b.lastEffect=a.next=a):(c=b.lastEffect,null===c?b.lastEffect=a.next=a:(d=c.next,c.next=a,a.next=d,b.lastEffect=a));return a}function ji(){return Uh().memoizedState}function ki(a,b,c,d){var e=Th();M.flags|=a;e.memoizedState=bi(1|b,c,void 0,void 0===d?null:d)}
function li(a,b,c,d){var e=Uh();d=void 0===d?null:d;var f=void 0;if(null!==N){var g=N.memoizedState;f=g.destroy;if(null!==d&&Mh(d,g.deps)){e.memoizedState=bi(b,c,f,d);return}}M.flags|=a;e.memoizedState=bi(1|b,c,f,d)}function mi(a,b){return ki(8390656,8,a,b)}function $h(a,b){return li(2048,8,a,b)}function ni(a,b){return li(4,2,a,b)}function oi(a,b){return li(4,4,a,b)}
function pi(a,b){if("function"===typeof b)return a=a(),b(a),function(){b(null)};if(null!==b&&void 0!==b)return a=a(),b.current=a,function(){b.current=null}}function qi(a,b,c){c=null!==c&&void 0!==c?c.concat([a]):null;return li(4,4,pi.bind(null,b,a),c)}function ri(){}function si(a,b){var c=Uh();b=void 0===b?null:b;var d=c.memoizedState;if(null!==d&&null!==b&&Mh(b,d[1]))return d[0];c.memoizedState=[a,b];return a}
function ti(a,b){var c=Uh();b=void 0===b?null:b;var d=c.memoizedState;if(null!==d&&null!==b&&Mh(b,d[1]))return d[0];a=a();c.memoizedState=[a,b];return a}function ui(a,b,c){if(0===(Hh&21))return a.baseState&&(a.baseState=!1,dh=!0),a.memoizedState=c;He(c,b)||(c=yc(),M.lanes|=c,rh|=c,a.baseState=!0);return b}function vi(a,b){var c=C;C=0!==c&&4>c?c:4;a(!0);var d=Gh.transition;Gh.transition={};try{a(!1),b()}finally{C=c,Gh.transition=d}}function wi(){return Uh().memoizedState}
function xi(a,b,c){var d=yi(a);c={lane:d,action:c,hasEagerState:!1,eagerState:null,next:null};if(zi(a))Ai(b,c);else if(c=hh(a,b,c,d),null!==c){var e=R();gi(c,a,d,e);Bi(c,b,d)}}
function ii(a,b,c){var d=yi(a),e={lane:d,action:c,hasEagerState:!1,eagerState:null,next:null};if(zi(a))Ai(b,e);else{var f=a.alternate;if(0===a.lanes&&(null===f||0===f.lanes)&&(f=b.lastRenderedReducer,null!==f))try{var g=b.lastRenderedState,h=f(g,c);e.hasEagerState=!0;e.eagerState=h;if(He(h,g)){var k=b.interleaved;null===k?(e.next=e,gh(b)):(e.next=k.next,k.next=e);b.interleaved=e;return}}catch(l){}finally{}c=hh(a,b,e,d);null!==c&&(e=R(),gi(c,a,d,e),Bi(c,b,d))}}
function zi(a){var b=a.alternate;return a===M||null!==b&&b===M}function Ai(a,b){Jh=Ih=!0;var c=a.pending;null===c?b.next=b:(b.next=c.next,c.next=b);a.pending=b}function Bi(a,b,c){if(0!==(c&4194240)){var d=b.lanes;d&=a.pendingLanes;c|=d;b.lanes=c;Cc(a,c)}}
var Rh={readContext:eh,useCallback:P,useContext:P,useEffect:P,useImperativeHandle:P,useInsertionEffect:P,useLayoutEffect:P,useMemo:P,useReducer:P,useRef:P,useState:P,useDebugValue:P,useDeferredValue:P,useTransition:P,useMutableSource:P,useSyncExternalStore:P,useId:P,unstable_isNewReconciler:!1},Oh={readContext:eh,useCallback:function(a,b){Th().memoizedState=[a,void 0===b?null:b];return a},useContext:eh,useEffect:mi,useImperativeHandle:function(a,b,c){c=null!==c&&void 0!==c?c.concat([a]):null;return ki(4194308,
4,pi.bind(null,b,a),c)},useLayoutEffect:function(a,b){return ki(4194308,4,a,b)},useInsertionEffect:function(a,b){return ki(4,2,a,b)},useMemo:function(a,b){var c=Th();b=void 0===b?null:b;a=a();c.memoizedState=[a,b];return a},useReducer:function(a,b,c){var d=Th();b=void 0!==c?c(b):b;d.memoizedState=d.baseState=b;a={pending:null,interleaved:null,lanes:0,dispatch:null,lastRenderedReducer:a,lastRenderedState:b};d.queue=a;a=a.dispatch=xi.bind(null,M,a);return[d.memoizedState,a]},useRef:function(a){var b=
Th();a={current:a};return b.memoizedState=a},useState:hi,useDebugValue:ri,useDeferredValue:function(a){return Th().memoizedState=a},useTransition:function(){var a=hi(!1),b=a[0];a=vi.bind(null,a[1]);Th().memoizedState=a;return[b,a]},useMutableSource:function(){},useSyncExternalStore:function(a,b,c){var d=M,e=Th();if(I){if(void 0===c)throw Error(p(407));c=c()}else{c=b();if(null===Q)throw Error(p(349));0!==(Hh&30)||di(d,b,c)}e.memoizedState=c;var f={value:c,getSnapshot:b};e.queue=f;mi(ai.bind(null,d,
f,a),[a]);d.flags|=2048;bi(9,ci.bind(null,d,f,c,b),void 0,null);return c},useId:function(){var a=Th(),b=Q.identifierPrefix;if(I){var c=sg;var d=rg;c=(d&~(1<<32-oc(d)-1)).toString(32)+c;b=":"+b+"R"+c;c=Kh++;0<c&&(b+="H"+c.toString(32));b+=":"}else c=Lh++,b=":"+b+"r"+c.toString(32)+":";return a.memoizedState=b},unstable_isNewReconciler:!1},Ph={readContext:eh,useCallback:si,useContext:eh,useEffect:$h,useImperativeHandle:qi,useInsertionEffect:ni,useLayoutEffect:oi,useMemo:ti,useReducer:Wh,useRef:ji,useState:function(){return Wh(Vh)},
useDebugValue:ri,useDeferredValue:function(a){var b=Uh();return ui(b,N.memoizedState,a)},useTransition:function(){var a=Wh(Vh)[0],b=Uh().memoizedState;return[a,b]},useMutableSource:Yh,useSyncExternalStore:Zh,useId:wi,unstable_isNewReconciler:!1},Qh={readContext:eh,useCallback:si,useContext:eh,useEffect:$h,useImperativeHandle:qi,useInsertionEffect:ni,useLayoutEffect:oi,useMemo:ti,useReducer:Xh,useRef:ji,useState:function(){return Xh(Vh)},useDebugValue:ri,useDeferredValue:function(a){var b=Uh();return null===
N?b.memoizedState=a:ui(b,N.memoizedState,a)},useTransition:function(){var a=Xh(Vh)[0],b=Uh().memoizedState;return[a,b]},useMutableSource:Yh,useSyncExternalStore:Zh,useId:wi,unstable_isNewReconciler:!1};function Ci(a,b){if(a&&a.defaultProps){b=A({},b);a=a.defaultProps;for(var c in a)void 0===b[c]&&(b[c]=a[c]);return b}return b}function Di(a,b,c,d){b=a.memoizedState;c=c(d,b);c=null===c||void 0===c?b:A({},b,c);a.memoizedState=c;0===a.lanes&&(a.updateQueue.baseState=c)}
var Ei={isMounted:function(a){return(a=a._reactInternals)?Vb(a)===a:!1},enqueueSetState:function(a,b,c){a=a._reactInternals;var d=R(),e=yi(a),f=mh(d,e);f.payload=b;void 0!==c&&null!==c&&(f.callback=c);b=nh(a,f,e);null!==b&&(gi(b,a,e,d),oh(b,a,e))},enqueueReplaceState:function(a,b,c){a=a._reactInternals;var d=R(),e=yi(a),f=mh(d,e);f.tag=1;f.payload=b;void 0!==c&&null!==c&&(f.callback=c);b=nh(a,f,e);null!==b&&(gi(b,a,e,d),oh(b,a,e))},enqueueForceUpdate:function(a,b){a=a._reactInternals;var c=R(),d=
yi(a),e=mh(c,d);e.tag=2;void 0!==b&&null!==b&&(e.callback=b);b=nh(a,e,d);null!==b&&(gi(b,a,d,c),oh(b,a,d))}};function Fi(a,b,c,d,e,f,g){a=a.stateNode;return"function"===typeof a.shouldComponentUpdate?a.shouldComponentUpdate(d,f,g):b.prototype&&b.prototype.isPureReactComponent?!Ie(c,d)||!Ie(e,f):!0}
function Gi(a,b,c){var d=!1,e=Vf;var f=b.contextType;"object"===typeof f&&null!==f?f=eh(f):(e=Zf(b)?Xf:H.current,d=b.contextTypes,f=(d=null!==d&&void 0!==d)?Yf(a,e):Vf);b=new b(c,f);a.memoizedState=null!==b.state&&void 0!==b.state?b.state:null;b.updater=Ei;a.stateNode=b;b._reactInternals=a;d&&(a=a.stateNode,a.__reactInternalMemoizedUnmaskedChildContext=e,a.__reactInternalMemoizedMaskedChildContext=f);return b}
function Hi(a,b,c,d){a=b.state;"function"===typeof b.componentWillReceiveProps&&b.componentWillReceiveProps(c,d);"function"===typeof b.UNSAFE_componentWillReceiveProps&&b.UNSAFE_componentWillReceiveProps(c,d);b.state!==a&&Ei.enqueueReplaceState(b,b.state,null)}
function Ii(a,b,c,d){var e=a.stateNode;e.props=c;e.state=a.memoizedState;e.refs={};kh(a);var f=b.contextType;"object"===typeof f&&null!==f?e.context=eh(f):(f=Zf(b)?Xf:H.current,e.context=Yf(a,f));e.state=a.memoizedState;f=b.getDerivedStateFromProps;"function"===typeof f&&(Di(a,b,f,c),e.state=a.memoizedState);"function"===typeof b.getDerivedStateFromProps||"function"===typeof e.getSnapshotBeforeUpdate||"function"!==typeof e.UNSAFE_componentWillMount&&"function"!==typeof e.componentWillMount||(b=e.state,
"function"===typeof e.componentWillMount&&e.componentWillMount(),"function"===typeof e.UNSAFE_componentWillMount&&e.UNSAFE_componentWillMount(),b!==e.state&&Ei.enqueueReplaceState(e,e.state,null),qh(a,c,e,d),e.state=a.memoizedState);"function"===typeof e.componentDidMount&&(a.flags|=4194308)}function Ji(a,b){try{var c="",d=b;do c+=Pa(d),d=d.return;while(d);var e=c}catch(f){e="\nError generating stack: "+f.message+"\n"+f.stack}return{value:a,source:b,stack:e,digest:null}}
function Ki(a,b,c){return{value:a,source:null,stack:null!=c?c:null,digest:null!=b?b:null}}function Li(a,b){try{console.error(b.value)}catch(c){setTimeout(function(){throw c;})}}var Mi="function"===typeof WeakMap?WeakMap:Map;function Ni(a,b,c){c=mh(-1,c);c.tag=3;c.payload={element:null};var d=b.value;c.callback=function(){Oi||(Oi=!0,Pi=d);Li(a,b)};return c}
function Qi(a,b,c){c=mh(-1,c);c.tag=3;var d=a.type.getDerivedStateFromError;if("function"===typeof d){var e=b.value;c.payload=function(){return d(e)};c.callback=function(){Li(a,b)}}var f=a.stateNode;null!==f&&"function"===typeof f.componentDidCatch&&(c.callback=function(){Li(a,b);"function"!==typeof d&&(null===Ri?Ri=new Set([this]):Ri.add(this));var c=b.stack;this.componentDidCatch(b.value,{componentStack:null!==c?c:""})});return c}
function Si(a,b,c){var d=a.pingCache;if(null===d){d=a.pingCache=new Mi;var e=new Set;d.set(b,e)}else e=d.get(b),void 0===e&&(e=new Set,d.set(b,e));e.has(c)||(e.add(c),a=Ti.bind(null,a,b,c),b.then(a,a))}function Ui(a){do{var b;if(b=13===a.tag)b=a.memoizedState,b=null!==b?null!==b.dehydrated?!0:!1:!0;if(b)return a;a=a.return}while(null!==a);return null}
function Vi(a,b,c,d,e){if(0===(a.mode&1))return a===b?a.flags|=65536:(a.flags|=128,c.flags|=131072,c.flags&=-52805,1===c.tag&&(null===c.alternate?c.tag=17:(b=mh(-1,1),b.tag=2,nh(c,b,1))),c.lanes|=1),a;a.flags|=65536;a.lanes=e;return a}var Wi=ua.ReactCurrentOwner,dh=!1;function Xi(a,b,c,d){b.child=null===a?Vg(b,null,c,d):Ug(b,a.child,c,d)}
function Yi(a,b,c,d,e){c=c.render;var f=b.ref;ch(b,e);d=Nh(a,b,c,d,f,e);c=Sh();if(null!==a&&!dh)return b.updateQueue=a.updateQueue,b.flags&=-2053,a.lanes&=~e,Zi(a,b,e);I&&c&&vg(b);b.flags|=1;Xi(a,b,d,e);return b.child}
function $i(a,b,c,d,e){if(null===a){var f=c.type;if("function"===typeof f&&!aj(f)&&void 0===f.defaultProps&&null===c.compare&&void 0===c.defaultProps)return b.tag=15,b.type=f,bj(a,b,f,d,e);a=Rg(c.type,null,d,b,b.mode,e);a.ref=b.ref;a.return=b;return b.child=a}f=a.child;if(0===(a.lanes&e)){var g=f.memoizedProps;c=c.compare;c=null!==c?c:Ie;if(c(g,d)&&a.ref===b.ref)return Zi(a,b,e)}b.flags|=1;a=Pg(f,d);a.ref=b.ref;a.return=b;return b.child=a}
function bj(a,b,c,d,e){if(null!==a){var f=a.memoizedProps;if(Ie(f,d)&&a.ref===b.ref)if(dh=!1,b.pendingProps=d=f,0!==(a.lanes&e))0!==(a.flags&131072)&&(dh=!0);else return b.lanes=a.lanes,Zi(a,b,e)}return cj(a,b,c,d,e)}
function dj(a,b,c){var d=b.pendingProps,e=d.children,f=null!==a?a.memoizedState:null;if("hidden"===d.mode)if(0===(b.mode&1))b.memoizedState={baseLanes:0,cachePool:null,transitions:null},G(ej,fj),fj|=c;else{if(0===(c&1073741824))return a=null!==f?f.baseLanes|c:c,b.lanes=b.childLanes=1073741824,b.memoizedState={baseLanes:a,cachePool:null,transitions:null},b.updateQueue=null,G(ej,fj),fj|=a,null;b.memoizedState={baseLanes:0,cachePool:null,transitions:null};d=null!==f?f.baseLanes:c;G(ej,fj);fj|=d}else null!==
f?(d=f.baseLanes|c,b.memoizedState=null):d=c,G(ej,fj),fj|=d;Xi(a,b,e,c);return b.child}function gj(a,b){var c=b.ref;if(null===a&&null!==c||null!==a&&a.ref!==c)b.flags|=512,b.flags|=2097152}function cj(a,b,c,d,e){var f=Zf(c)?Xf:H.current;f=Yf(b,f);ch(b,e);c=Nh(a,b,c,d,f,e);d=Sh();if(null!==a&&!dh)return b.updateQueue=a.updateQueue,b.flags&=-2053,a.lanes&=~e,Zi(a,b,e);I&&d&&vg(b);b.flags|=1;Xi(a,b,c,e);return b.child}
function hj(a,b,c,d,e){if(Zf(c)){var f=!0;cg(b)}else f=!1;ch(b,e);if(null===b.stateNode)ij(a,b),Gi(b,c,d),Ii(b,c,d,e),d=!0;else if(null===a){var g=b.stateNode,h=b.memoizedProps;g.props=h;var k=g.context,l=c.contextType;"object"===typeof l&&null!==l?l=eh(l):(l=Zf(c)?Xf:H.current,l=Yf(b,l));var m=c.getDerivedStateFromProps,q="function"===typeof m||"function"===typeof g.getSnapshotBeforeUpdate;q||"function"!==typeof g.UNSAFE_componentWillReceiveProps&&"function"!==typeof g.componentWillReceiveProps||
(h!==d||k!==l)&&Hi(b,g,d,l);jh=!1;var r=b.memoizedState;g.state=r;qh(b,d,g,e);k=b.memoizedState;h!==d||r!==k||Wf.current||jh?("function"===typeof m&&(Di(b,c,m,d),k=b.memoizedState),(h=jh||Fi(b,c,h,d,r,k,l))?(q||"function"!==typeof g.UNSAFE_componentWillMount&&"function"!==typeof g.componentWillMount||("function"===typeof g.componentWillMount&&g.componentWillMount(),"function"===typeof g.UNSAFE_componentWillMount&&g.UNSAFE_componentWillMount()),"function"===typeof g.componentDidMount&&(b.flags|=4194308)):
("function"===typeof g.componentDidMount&&(b.flags|=4194308),b.memoizedProps=d,b.memoizedState=k),g.props=d,g.state=k,g.context=l,d=h):("function"===typeof g.componentDidMount&&(b.flags|=4194308),d=!1)}else{g=b.stateNode;lh(a,b);h=b.memoizedProps;l=b.type===b.elementType?h:Ci(b.type,h);g.props=l;q=b.pendingProps;r=g.context;k=c.contextType;"object"===typeof k&&null!==k?k=eh(k):(k=Zf(c)?Xf:H.current,k=Yf(b,k));var y=c.getDerivedStateFromProps;(m="function"===typeof y||"function"===typeof g.getSnapshotBeforeUpdate)||
"function"!==typeof g.UNSAFE_componentWillReceiveProps&&"function"!==typeof g.componentWillReceiveProps||(h!==q||r!==k)&&Hi(b,g,d,k);jh=!1;r=b.memoizedState;g.state=r;qh(b,d,g,e);var n=b.memoizedState;h!==q||r!==n||Wf.current||jh?("function"===typeof y&&(Di(b,c,y,d),n=b.memoizedState),(l=jh||Fi(b,c,l,d,r,n,k)||!1)?(m||"function"!==typeof g.UNSAFE_componentWillUpdate&&"function"!==typeof g.componentWillUpdate||("function"===typeof g.componentWillUpdate&&g.componentWillUpdate(d,n,k),"function"===typeof g.UNSAFE_componentWillUpdate&&
g.UNSAFE_componentWillUpdate(d,n,k)),"function"===typeof g.componentDidUpdate&&(b.flags|=4),"function"===typeof g.getSnapshotBeforeUpdate&&(b.flags|=1024)):("function"!==typeof g.componentDidUpdate||h===a.memoizedProps&&r===a.memoizedState||(b.flags|=4),"function"!==typeof g.getSnapshotBeforeUpdate||h===a.memoizedProps&&r===a.memoizedState||(b.flags|=1024),b.memoizedProps=d,b.memoizedState=n),g.props=d,g.state=n,g.context=k,d=l):("function"!==typeof g.componentDidUpdate||h===a.memoizedProps&&r===
a.memoizedState||(b.flags|=4),"function"!==typeof g.getSnapshotBeforeUpdate||h===a.memoizedProps&&r===a.memoizedState||(b.flags|=1024),d=!1)}return jj(a,b,c,d,f,e)}
function jj(a,b,c,d,e,f){gj(a,b);var g=0!==(b.flags&128);if(!d&&!g)return e&&dg(b,c,!1),Zi(a,b,f);d=b.stateNode;Wi.current=b;var h=g&&"function"!==typeof c.getDerivedStateFromError?null:d.render();b.flags|=1;null!==a&&g?(b.child=Ug(b,a.child,null,f),b.child=Ug(b,null,h,f)):Xi(a,b,h,f);b.memoizedState=d.state;e&&dg(b,c,!0);return b.child}function kj(a){var b=a.stateNode;b.pendingContext?ag(a,b.pendingContext,b.pendingContext!==b.context):b.context&&ag(a,b.context,!1);yh(a,b.containerInfo)}
function lj(a,b,c,d,e){Ig();Jg(e);b.flags|=256;Xi(a,b,c,d);return b.child}var mj={dehydrated:null,treeContext:null,retryLane:0};function nj(a){return{baseLanes:a,cachePool:null,transitions:null}}
function oj(a,b,c){var d=b.pendingProps,e=L.current,f=!1,g=0!==(b.flags&128),h;(h=g)||(h=null!==a&&null===a.memoizedState?!1:0!==(e&2));if(h)f=!0,b.flags&=-129;else if(null===a||null!==a.memoizedState)e|=1;G(L,e&1);if(null===a){Eg(b);a=b.memoizedState;if(null!==a&&(a=a.dehydrated,null!==a))return 0===(b.mode&1)?b.lanes=1:"$!"===a.data?b.lanes=8:b.lanes=1073741824,null;g=d.children;a=d.fallback;return f?(d=b.mode,f=b.child,g={mode:"hidden",children:g},0===(d&1)&&null!==f?(f.childLanes=0,f.pendingProps=
g):f=pj(g,d,0,null),a=Tg(a,d,c,null),f.return=b,a.return=b,f.sibling=a,b.child=f,b.child.memoizedState=nj(c),b.memoizedState=mj,a):qj(b,g)}e=a.memoizedState;if(null!==e&&(h=e.dehydrated,null!==h))return rj(a,b,g,d,h,e,c);if(f){f=d.fallback;g=b.mode;e=a.child;h=e.sibling;var k={mode:"hidden",children:d.children};0===(g&1)&&b.child!==e?(d=b.child,d.childLanes=0,d.pendingProps=k,b.deletions=null):(d=Pg(e,k),d.subtreeFlags=e.subtreeFlags&14680064);null!==h?f=Pg(h,f):(f=Tg(f,g,c,null),f.flags|=2);f.return=
b;d.return=b;d.sibling=f;b.child=d;d=f;f=b.child;g=a.child.memoizedState;g=null===g?nj(c):{baseLanes:g.baseLanes|c,cachePool:null,transitions:g.transitions};f.memoizedState=g;f.childLanes=a.childLanes&~c;b.memoizedState=mj;return d}f=a.child;a=f.sibling;d=Pg(f,{mode:"visible",children:d.children});0===(b.mode&1)&&(d.lanes=c);d.return=b;d.sibling=null;null!==a&&(c=b.deletions,null===c?(b.deletions=[a],b.flags|=16):c.push(a));b.child=d;b.memoizedState=null;return d}
function qj(a,b){b=pj({mode:"visible",children:b},a.mode,0,null);b.return=a;return a.child=b}function sj(a,b,c,d){null!==d&&Jg(d);Ug(b,a.child,null,c);a=qj(b,b.pendingProps.children);a.flags|=2;b.memoizedState=null;return a}
function rj(a,b,c,d,e,f,g){if(c){if(b.flags&256)return b.flags&=-257,d=Ki(Error(p(422))),sj(a,b,g,d);if(null!==b.memoizedState)return b.child=a.child,b.flags|=128,null;f=d.fallback;e=b.mode;d=pj({mode:"visible",children:d.children},e,0,null);f=Tg(f,e,g,null);f.flags|=2;d.return=b;f.return=b;d.sibling=f;b.child=d;0!==(b.mode&1)&&Ug(b,a.child,null,g);b.child.memoizedState=nj(g);b.memoizedState=mj;return f}if(0===(b.mode&1))return sj(a,b,g,null);if("$!"===e.data){d=e.nextSibling&&e.nextSibling.dataset;
if(d)var h=d.dgst;d=h;f=Error(p(419));d=Ki(f,d,void 0);return sj(a,b,g,d)}h=0!==(g&a.childLanes);if(dh||h){d=Q;if(null!==d){switch(g&-g){case 4:e=2;break;case 16:e=8;break;case 64:case 128:case 256:case 512:case 1024:case 2048:case 4096:case 8192:case 16384:case 32768:case 65536:case 131072:case 262144:case 524288:case 1048576:case 2097152:case 4194304:case 8388608:case 16777216:case 33554432:case 67108864:e=32;break;case 536870912:e=268435456;break;default:e=0}e=0!==(e&(d.suspendedLanes|g))?0:e;
0!==e&&e!==f.retryLane&&(f.retryLane=e,ih(a,e),gi(d,a,e,-1))}tj();d=Ki(Error(p(421)));return sj(a,b,g,d)}if("$?"===e.data)return b.flags|=128,b.child=a.child,b=uj.bind(null,a),e._reactRetry=b,null;a=f.treeContext;yg=Lf(e.nextSibling);xg=b;I=!0;zg=null;null!==a&&(og[pg++]=rg,og[pg++]=sg,og[pg++]=qg,rg=a.id,sg=a.overflow,qg=b);b=qj(b,d.children);b.flags|=4096;return b}function vj(a,b,c){a.lanes|=b;var d=a.alternate;null!==d&&(d.lanes|=b);bh(a.return,b,c)}
function wj(a,b,c,d,e){var f=a.memoizedState;null===f?a.memoizedState={isBackwards:b,rendering:null,renderingStartTime:0,last:d,tail:c,tailMode:e}:(f.isBackwards=b,f.rendering=null,f.renderingStartTime=0,f.last=d,f.tail=c,f.tailMode=e)}
function xj(a,b,c){var d=b.pendingProps,e=d.revealOrder,f=d.tail;Xi(a,b,d.children,c);d=L.current;if(0!==(d&2))d=d&1|2,b.flags|=128;else{if(null!==a&&0!==(a.flags&128))a:for(a=b.child;null!==a;){if(13===a.tag)null!==a.memoizedState&&vj(a,c,b);else if(19===a.tag)vj(a,c,b);else if(null!==a.child){a.child.return=a;a=a.child;continue}if(a===b)break a;for(;null===a.sibling;){if(null===a.return||a.return===b)break a;a=a.return}a.sibling.return=a.return;a=a.sibling}d&=1}G(L,d);if(0===(b.mode&1))b.memoizedState=
null;else switch(e){case "forwards":c=b.child;for(e=null;null!==c;)a=c.alternate,null!==a&&null===Ch(a)&&(e=c),c=c.sibling;c=e;null===c?(e=b.child,b.child=null):(e=c.sibling,c.sibling=null);wj(b,!1,e,c,f);break;case "backwards":c=null;e=b.child;for(b.child=null;null!==e;){a=e.alternate;if(null!==a&&null===Ch(a)){b.child=e;break}a=e.sibling;e.sibling=c;c=e;e=a}wj(b,!0,c,null,f);break;case "together":wj(b,!1,null,null,void 0);break;default:b.memoizedState=null}return b.child}
function ij(a,b){0===(b.mode&1)&&null!==a&&(a.alternate=null,b.alternate=null,b.flags|=2)}function Zi(a,b,c){null!==a&&(b.dependencies=a.dependencies);rh|=b.lanes;if(0===(c&b.childLanes))return null;if(null!==a&&b.child!==a.child)throw Error(p(153));if(null!==b.child){a=b.child;c=Pg(a,a.pendingProps);b.child=c;for(c.return=b;null!==a.sibling;)a=a.sibling,c=c.sibling=Pg(a,a.pendingProps),c.return=b;c.sibling=null}return b.child}
function yj(a,b,c){switch(b.tag){case 3:kj(b);Ig();break;case 5:Ah(b);break;case 1:Zf(b.type)&&cg(b);break;case 4:yh(b,b.stateNode.containerInfo);break;case 10:var d=b.type._context,e=b.memoizedProps.value;G(Wg,d._currentValue);d._currentValue=e;break;case 13:d=b.memoizedState;if(null!==d){if(null!==d.dehydrated)return G(L,L.current&1),b.flags|=128,null;if(0!==(c&b.child.childLanes))return oj(a,b,c);G(L,L.current&1);a=Zi(a,b,c);return null!==a?a.sibling:null}G(L,L.current&1);break;case 19:d=0!==(c&
b.childLanes);if(0!==(a.flags&128)){if(d)return xj(a,b,c);b.flags|=128}e=b.memoizedState;null!==e&&(e.rendering=null,e.tail=null,e.lastEffect=null);G(L,L.current);if(d)break;else return null;case 22:case 23:return b.lanes=0,dj(a,b,c)}return Zi(a,b,c)}var zj,Aj,Bj,Cj;
zj=function(a,b){for(var c=b.child;null!==c;){if(5===c.tag||6===c.tag)a.appendChild(c.stateNode);else if(4!==c.tag&&null!==c.child){c.child.return=c;c=c.child;continue}if(c===b)break;for(;null===c.sibling;){if(null===c.return||c.return===b)return;c=c.return}c.sibling.return=c.return;c=c.sibling}};Aj=function(){};
Bj=function(a,b,c,d){var e=a.memoizedProps;if(e!==d){a=b.stateNode;xh(uh.current);var f=null;switch(c){case "input":e=Ya(a,e);d=Ya(a,d);f=[];break;case "select":e=A({},e,{value:void 0});d=A({},d,{value:void 0});f=[];break;case "textarea":e=gb(a,e);d=gb(a,d);f=[];break;default:"function"!==typeof e.onClick&&"function"===typeof d.onClick&&(a.onclick=Bf)}ub(c,d);var g;c=null;for(l in e)if(!d.hasOwnProperty(l)&&e.hasOwnProperty(l)&&null!=e[l])if("style"===l){var h=e[l];for(g in h)h.hasOwnProperty(g)&&
(c||(c={}),c[g]="")}else"dangerouslySetInnerHTML"!==l&&"children"!==l&&"suppressContentEditableWarning"!==l&&"suppressHydrationWarning"!==l&&"autoFocus"!==l&&(ea.hasOwnProperty(l)?f||(f=[]):(f=f||[]).push(l,null));for(l in d){var k=d[l];h=null!=e?e[l]:void 0;if(d.hasOwnProperty(l)&&k!==h&&(null!=k||null!=h))if("style"===l)if(h){for(g in h)!h.hasOwnProperty(g)||k&&k.hasOwnProperty(g)||(c||(c={}),c[g]="");for(g in k)k.hasOwnProperty(g)&&h[g]!==k[g]&&(c||(c={}),c[g]=k[g])}else c||(f||(f=[]),f.push(l,
c)),c=k;else"dangerouslySetInnerHTML"===l?(k=k?k.__html:void 0,h=h?h.__html:void 0,null!=k&&h!==k&&(f=f||[]).push(l,k)):"children"===l?"string"!==typeof k&&"number"!==typeof k||(f=f||[]).push(l,""+k):"suppressContentEditableWarning"!==l&&"suppressHydrationWarning"!==l&&(ea.hasOwnProperty(l)?(null!=k&&"onScroll"===l&&D("scroll",a),f||h===k||(f=[])):(f=f||[]).push(l,k))}c&&(f=f||[]).push("style",c);var l=f;if(b.updateQueue=l)b.flags|=4}};Cj=function(a,b,c,d){c!==d&&(b.flags|=4)};
function Dj(a,b){if(!I)switch(a.tailMode){case "hidden":b=a.tail;for(var c=null;null!==b;)null!==b.alternate&&(c=b),b=b.sibling;null===c?a.tail=null:c.sibling=null;break;case "collapsed":c=a.tail;for(var d=null;null!==c;)null!==c.alternate&&(d=c),c=c.sibling;null===d?b||null===a.tail?a.tail=null:a.tail.sibling=null:d.sibling=null}}
function S(a){var b=null!==a.alternate&&a.alternate.child===a.child,c=0,d=0;if(b)for(var e=a.child;null!==e;)c|=e.lanes|e.childLanes,d|=e.subtreeFlags&14680064,d|=e.flags&14680064,e.return=a,e=e.sibling;else for(e=a.child;null!==e;)c|=e.lanes|e.childLanes,d|=e.subtreeFlags,d|=e.flags,e.return=a,e=e.sibling;a.subtreeFlags|=d;a.childLanes=c;return b}
function Ej(a,b,c){var d=b.pendingProps;wg(b);switch(b.tag){case 2:case 16:case 15:case 0:case 11:case 7:case 8:case 12:case 9:case 14:return S(b),null;case 1:return Zf(b.type)&&$f(),S(b),null;case 3:d=b.stateNode;zh();E(Wf);E(H);Eh();d.pendingContext&&(d.context=d.pendingContext,d.pendingContext=null);if(null===a||null===a.child)Gg(b)?b.flags|=4:null===a||a.memoizedState.isDehydrated&&0===(b.flags&256)||(b.flags|=1024,null!==zg&&(Fj(zg),zg=null));Aj(a,b);S(b);return null;case 5:Bh(b);var e=xh(wh.current);
c=b.type;if(null!==a&&null!=b.stateNode)Bj(a,b,c,d,e),a.ref!==b.ref&&(b.flags|=512,b.flags|=2097152);else{if(!d){if(null===b.stateNode)throw Error(p(166));S(b);return null}a=xh(uh.current);if(Gg(b)){d=b.stateNode;c=b.type;var f=b.memoizedProps;d[Of]=b;d[Pf]=f;a=0!==(b.mode&1);switch(c){case "dialog":D("cancel",d);D("close",d);break;case "iframe":case "object":case "embed":D("load",d);break;case "video":case "audio":for(e=0;e<lf.length;e++)D(lf[e],d);break;case "source":D("error",d);break;case "img":case "image":case "link":D("error",
d);D("load",d);break;case "details":D("toggle",d);break;case "input":Za(d,f);D("invalid",d);break;case "select":d._wrapperState={wasMultiple:!!f.multiple};D("invalid",d);break;case "textarea":hb(d,f),D("invalid",d)}ub(c,f);e=null;for(var g in f)if(f.hasOwnProperty(g)){var h=f[g];"children"===g?"string"===typeof h?d.textContent!==h&&(!0!==f.suppressHydrationWarning&&Af(d.textContent,h,a),e=["children",h]):"number"===typeof h&&d.textContent!==""+h&&(!0!==f.suppressHydrationWarning&&Af(d.textContent,
h,a),e=["children",""+h]):ea.hasOwnProperty(g)&&null!=h&&"onScroll"===g&&D("scroll",d)}switch(c){case "input":Va(d);db(d,f,!0);break;case "textarea":Va(d);jb(d);break;case "select":case "option":break;default:"function"===typeof f.onClick&&(d.onclick=Bf)}d=e;b.updateQueue=d;null!==d&&(b.flags|=4)}else{g=9===e.nodeType?e:e.ownerDocument;"http://www.w3.org/1999/xhtml"===a&&(a=kb(c));"http://www.w3.org/1999/xhtml"===a?"script"===c?(a=g.createElement("div"),a.innerHTML="<script>\x3c/script>",a=a.removeChild(a.firstChild)):
"string"===typeof d.is?a=g.createElement(c,{is:d.is}):(a=g.createElement(c),"select"===c&&(g=a,d.multiple?g.multiple=!0:d.size&&(g.size=d.size))):a=g.createElementNS(a,c);a[Of]=b;a[Pf]=d;zj(a,b,!1,!1);b.stateNode=a;a:{g=vb(c,d);switch(c){case "dialog":D("cancel",a);D("close",a);e=d;break;case "iframe":case "object":case "embed":D("load",a);e=d;break;case "video":case "audio":for(e=0;e<lf.length;e++)D(lf[e],a);e=d;break;case "source":D("error",a);e=d;break;case "img":case "image":case "link":D("error",
a);D("load",a);e=d;break;case "details":D("toggle",a);e=d;break;case "input":Za(a,d);e=Ya(a,d);D("invalid",a);break;case "option":e=d;break;case "select":a._wrapperState={wasMultiple:!!d.multiple};e=A({},d,{value:void 0});D("invalid",a);break;case "textarea":hb(a,d);e=gb(a,d);D("invalid",a);break;default:e=d}ub(c,e);h=e;for(f in h)if(h.hasOwnProperty(f)){var k=h[f];"style"===f?sb(a,k):"dangerouslySetInnerHTML"===f?(k=k?k.__html:void 0,null!=k&&nb(a,k)):"children"===f?"string"===typeof k?("textarea"!==
c||""!==k)&&ob(a,k):"number"===typeof k&&ob(a,""+k):"suppressContentEditableWarning"!==f&&"suppressHydrationWarning"!==f&&"autoFocus"!==f&&(ea.hasOwnProperty(f)?null!=k&&"onScroll"===f&&D("scroll",a):null!=k&&ta(a,f,k,g))}switch(c){case "input":Va(a);db(a,d,!1);break;case "textarea":Va(a);jb(a);break;case "option":null!=d.value&&a.setAttribute("value",""+Sa(d.value));break;case "select":a.multiple=!!d.multiple;f=d.value;null!=f?fb(a,!!d.multiple,f,!1):null!=d.defaultValue&&fb(a,!!d.multiple,d.defaultValue,
!0);break;default:"function"===typeof e.onClick&&(a.onclick=Bf)}switch(c){case "button":case "input":case "select":case "textarea":d=!!d.autoFocus;break a;case "img":d=!0;break a;default:d=!1}}d&&(b.flags|=4)}null!==b.ref&&(b.flags|=512,b.flags|=2097152)}S(b);return null;case 6:if(a&&null!=b.stateNode)Cj(a,b,a.memoizedProps,d);else{if("string"!==typeof d&&null===b.stateNode)throw Error(p(166));c=xh(wh.current);xh(uh.current);if(Gg(b)){d=b.stateNode;c=b.memoizedProps;d[Of]=b;if(f=d.nodeValue!==c)if(a=
xg,null!==a)switch(a.tag){case 3:Af(d.nodeValue,c,0!==(a.mode&1));break;case 5:!0!==a.memoizedProps.suppressHydrationWarning&&Af(d.nodeValue,c,0!==(a.mode&1))}f&&(b.flags|=4)}else d=(9===c.nodeType?c:c.ownerDocument).createTextNode(d),d[Of]=b,b.stateNode=d}S(b);return null;case 13:E(L);d=b.memoizedState;if(null===a||null!==a.memoizedState&&null!==a.memoizedState.dehydrated){if(I&&null!==yg&&0!==(b.mode&1)&&0===(b.flags&128))Hg(),Ig(),b.flags|=98560,f=!1;else if(f=Gg(b),null!==d&&null!==d.dehydrated){if(null===
a){if(!f)throw Error(p(318));f=b.memoizedState;f=null!==f?f.dehydrated:null;if(!f)throw Error(p(317));f[Of]=b}else Ig(),0===(b.flags&128)&&(b.memoizedState=null),b.flags|=4;S(b);f=!1}else null!==zg&&(Fj(zg),zg=null),f=!0;if(!f)return b.flags&65536?b:null}if(0!==(b.flags&128))return b.lanes=c,b;d=null!==d;d!==(null!==a&&null!==a.memoizedState)&&d&&(b.child.flags|=8192,0!==(b.mode&1)&&(null===a||0!==(L.current&1)?0===T&&(T=3):tj()));null!==b.updateQueue&&(b.flags|=4);S(b);return null;case 4:return zh(),
Aj(a,b),null===a&&sf(b.stateNode.containerInfo),S(b),null;case 10:return ah(b.type._context),S(b),null;case 17:return Zf(b.type)&&$f(),S(b),null;case 19:E(L);f=b.memoizedState;if(null===f)return S(b),null;d=0!==(b.flags&128);g=f.rendering;if(null===g)if(d)Dj(f,!1);else{if(0!==T||null!==a&&0!==(a.flags&128))for(a=b.child;null!==a;){g=Ch(a);if(null!==g){b.flags|=128;Dj(f,!1);d=g.updateQueue;null!==d&&(b.updateQueue=d,b.flags|=4);b.subtreeFlags=0;d=c;for(c=b.child;null!==c;)f=c,a=d,f.flags&=14680066,
g=f.alternate,null===g?(f.childLanes=0,f.lanes=a,f.child=null,f.subtreeFlags=0,f.memoizedProps=null,f.memoizedState=null,f.updateQueue=null,f.dependencies=null,f.stateNode=null):(f.childLanes=g.childLanes,f.lanes=g.lanes,f.child=g.child,f.subtreeFlags=0,f.deletions=null,f.memoizedProps=g.memoizedProps,f.memoizedState=g.memoizedState,f.updateQueue=g.updateQueue,f.type=g.type,a=g.dependencies,f.dependencies=null===a?null:{lanes:a.lanes,firstContext:a.firstContext}),c=c.sibling;G(L,L.current&1|2);return b.child}a=
a.sibling}null!==f.tail&&B()>Gj&&(b.flags|=128,d=!0,Dj(f,!1),b.lanes=4194304)}else{if(!d)if(a=Ch(g),null!==a){if(b.flags|=128,d=!0,c=a.updateQueue,null!==c&&(b.updateQueue=c,b.flags|=4),Dj(f,!0),null===f.tail&&"hidden"===f.tailMode&&!g.alternate&&!I)return S(b),null}else 2*B()-f.renderingStartTime>Gj&&1073741824!==c&&(b.flags|=128,d=!0,Dj(f,!1),b.lanes=4194304);f.isBackwards?(g.sibling=b.child,b.child=g):(c=f.last,null!==c?c.sibling=g:b.child=g,f.last=g)}if(null!==f.tail)return b=f.tail,f.rendering=
b,f.tail=b.sibling,f.renderingStartTime=B(),b.sibling=null,c=L.current,G(L,d?c&1|2:c&1),b;S(b);return null;case 22:case 23:return Hj(),d=null!==b.memoizedState,null!==a&&null!==a.memoizedState!==d&&(b.flags|=8192),d&&0!==(b.mode&1)?0!==(fj&1073741824)&&(S(b),b.subtreeFlags&6&&(b.flags|=8192)):S(b),null;case 24:return null;case 25:return null}throw Error(p(156,b.tag));}
function Ij(a,b){wg(b);switch(b.tag){case 1:return Zf(b.type)&&$f(),a=b.flags,a&65536?(b.flags=a&-65537|128,b):null;case 3:return zh(),E(Wf),E(H),Eh(),a=b.flags,0!==(a&65536)&&0===(a&128)?(b.flags=a&-65537|128,b):null;case 5:return Bh(b),null;case 13:E(L);a=b.memoizedState;if(null!==a&&null!==a.dehydrated){if(null===b.alternate)throw Error(p(340));Ig()}a=b.flags;return a&65536?(b.flags=a&-65537|128,b):null;case 19:return E(L),null;case 4:return zh(),null;case 10:return ah(b.type._context),null;case 22:case 23:return Hj(),
null;case 24:return null;default:return null}}var Jj=!1,U=!1,Kj="function"===typeof WeakSet?WeakSet:Set,V=null;function Lj(a,b){var c=a.ref;if(null!==c)if("function"===typeof c)try{c(null)}catch(d){W(a,b,d)}else c.current=null}function Mj(a,b,c){try{c()}catch(d){W(a,b,d)}}var Nj=!1;
function Oj(a,b){Cf=dd;a=Me();if(Ne(a)){if("selectionStart"in a)var c={start:a.selectionStart,end:a.selectionEnd};else a:{c=(c=a.ownerDocument)&&c.defaultView||window;var d=c.getSelection&&c.getSelection();if(d&&0!==d.rangeCount){c=d.anchorNode;var e=d.anchorOffset,f=d.focusNode;d=d.focusOffset;try{c.nodeType,f.nodeType}catch(F){c=null;break a}var g=0,h=-1,k=-1,l=0,m=0,q=a,r=null;b:for(;;){for(var y;;){q!==c||0!==e&&3!==q.nodeType||(h=g+e);q!==f||0!==d&&3!==q.nodeType||(k=g+d);3===q.nodeType&&(g+=
q.nodeValue.length);if(null===(y=q.firstChild))break;r=q;q=y}for(;;){if(q===a)break b;r===c&&++l===e&&(h=g);r===f&&++m===d&&(k=g);if(null!==(y=q.nextSibling))break;q=r;r=q.parentNode}q=y}c=-1===h||-1===k?null:{start:h,end:k}}else c=null}c=c||{start:0,end:0}}else c=null;Df={focusedElem:a,selectionRange:c};dd=!1;for(V=b;null!==V;)if(b=V,a=b.child,0!==(b.subtreeFlags&1028)&&null!==a)a.return=b,V=a;else for(;null!==V;){b=V;try{var n=b.alternate;if(0!==(b.flags&1024))switch(b.tag){case 0:case 11:case 15:break;
case 1:if(null!==n){var t=n.memoizedProps,J=n.memoizedState,x=b.stateNode,w=x.getSnapshotBeforeUpdate(b.elementType===b.type?t:Ci(b.type,t),J);x.__reactInternalSnapshotBeforeUpdate=w}break;case 3:var u=b.stateNode.containerInfo;1===u.nodeType?u.textContent="":9===u.nodeType&&u.documentElement&&u.removeChild(u.documentElement);break;case 5:case 6:case 4:case 17:break;default:throw Error(p(163));}}catch(F){W(b,b.return,F)}a=b.sibling;if(null!==a){a.return=b.return;V=a;break}V=b.return}n=Nj;Nj=!1;return n}
function Pj(a,b,c){var d=b.updateQueue;d=null!==d?d.lastEffect:null;if(null!==d){var e=d=d.next;do{if((e.tag&a)===a){var f=e.destroy;e.destroy=void 0;void 0!==f&&Mj(b,c,f)}e=e.next}while(e!==d)}}function Qj(a,b){b=b.updateQueue;b=null!==b?b.lastEffect:null;if(null!==b){var c=b=b.next;do{if((c.tag&a)===a){var d=c.create;c.destroy=d()}c=c.next}while(c!==b)}}function Rj(a){var b=a.ref;if(null!==b){var c=a.stateNode;switch(a.tag){case 5:a=c;break;default:a=c}"function"===typeof b?b(a):b.current=a}}
function Sj(a){var b=a.alternate;null!==b&&(a.alternate=null,Sj(b));a.child=null;a.deletions=null;a.sibling=null;5===a.tag&&(b=a.stateNode,null!==b&&(delete b[Of],delete b[Pf],delete b[of],delete b[Qf],delete b[Rf]));a.stateNode=null;a.return=null;a.dependencies=null;a.memoizedProps=null;a.memoizedState=null;a.pendingProps=null;a.stateNode=null;a.updateQueue=null}function Tj(a){return 5===a.tag||3===a.tag||4===a.tag}
function Uj(a){a:for(;;){for(;null===a.sibling;){if(null===a.return||Tj(a.return))return null;a=a.return}a.sibling.return=a.return;for(a=a.sibling;5!==a.tag&&6!==a.tag&&18!==a.tag;){if(a.flags&2)continue a;if(null===a.child||4===a.tag)continue a;else a.child.return=a,a=a.child}if(!(a.flags&2))return a.stateNode}}
function Vj(a,b,c){var d=a.tag;if(5===d||6===d)a=a.stateNode,b?8===c.nodeType?c.parentNode.insertBefore(a,b):c.insertBefore(a,b):(8===c.nodeType?(b=c.parentNode,b.insertBefore(a,c)):(b=c,b.appendChild(a)),c=c._reactRootContainer,null!==c&&void 0!==c||null!==b.onclick||(b.onclick=Bf));else if(4!==d&&(a=a.child,null!==a))for(Vj(a,b,c),a=a.sibling;null!==a;)Vj(a,b,c),a=a.sibling}
function Wj(a,b,c){var d=a.tag;if(5===d||6===d)a=a.stateNode,b?c.insertBefore(a,b):c.appendChild(a);else if(4!==d&&(a=a.child,null!==a))for(Wj(a,b,c),a=a.sibling;null!==a;)Wj(a,b,c),a=a.sibling}var X=null,Xj=!1;function Yj(a,b,c){for(c=c.child;null!==c;)Zj(a,b,c),c=c.sibling}
function Zj(a,b,c){if(lc&&"function"===typeof lc.onCommitFiberUnmount)try{lc.onCommitFiberUnmount(kc,c)}catch(h){}switch(c.tag){case 5:U||Lj(c,b);case 6:var d=X,e=Xj;X=null;Yj(a,b,c);X=d;Xj=e;null!==X&&(Xj?(a=X,c=c.stateNode,8===a.nodeType?a.parentNode.removeChild(c):a.removeChild(c)):X.removeChild(c.stateNode));break;case 18:null!==X&&(Xj?(a=X,c=c.stateNode,8===a.nodeType?Kf(a.parentNode,c):1===a.nodeType&&Kf(a,c),bd(a)):Kf(X,c.stateNode));break;case 4:d=X;e=Xj;X=c.stateNode.containerInfo;Xj=!0;
Yj(a,b,c);X=d;Xj=e;break;case 0:case 11:case 14:case 15:if(!U&&(d=c.updateQueue,null!==d&&(d=d.lastEffect,null!==d))){e=d=d.next;do{var f=e,g=f.destroy;f=f.tag;void 0!==g&&(0!==(f&2)?Mj(c,b,g):0!==(f&4)&&Mj(c,b,g));e=e.next}while(e!==d)}Yj(a,b,c);break;case 1:if(!U&&(Lj(c,b),d=c.stateNode,"function"===typeof d.componentWillUnmount))try{d.props=c.memoizedProps,d.state=c.memoizedState,d.componentWillUnmount()}catch(h){W(c,b,h)}Yj(a,b,c);break;case 21:Yj(a,b,c);break;case 22:c.mode&1?(U=(d=U)||null!==
c.memoizedState,Yj(a,b,c),U=d):Yj(a,b,c);break;default:Yj(a,b,c)}}function ak(a){var b=a.updateQueue;if(null!==b){a.updateQueue=null;var c=a.stateNode;null===c&&(c=a.stateNode=new Kj);b.forEach(function(b){var d=bk.bind(null,a,b);c.has(b)||(c.add(b),b.then(d,d))})}}
function ck(a,b){var c=b.deletions;if(null!==c)for(var d=0;d<c.length;d++){var e=c[d];try{var f=a,g=b,h=g;a:for(;null!==h;){switch(h.tag){case 5:X=h.stateNode;Xj=!1;break a;case 3:X=h.stateNode.containerInfo;Xj=!0;break a;case 4:X=h.stateNode.containerInfo;Xj=!0;break a}h=h.return}if(null===X)throw Error(p(160));Zj(f,g,e);X=null;Xj=!1;var k=e.alternate;null!==k&&(k.return=null);e.return=null}catch(l){W(e,b,l)}}if(b.subtreeFlags&12854)for(b=b.child;null!==b;)dk(b,a),b=b.sibling}
function dk(a,b){var c=a.alternate,d=a.flags;switch(a.tag){case 0:case 11:case 14:case 15:ck(b,a);ek(a);if(d&4){try{Pj(3,a,a.return),Qj(3,a)}catch(t){W(a,a.return,t)}try{Pj(5,a,a.return)}catch(t){W(a,a.return,t)}}break;case 1:ck(b,a);ek(a);d&512&&null!==c&&Lj(c,c.return);break;case 5:ck(b,a);ek(a);d&512&&null!==c&&Lj(c,c.return);if(a.flags&32){var e=a.stateNode;try{ob(e,"")}catch(t){W(a,a.return,t)}}if(d&4&&(e=a.stateNode,null!=e)){var f=a.memoizedProps,g=null!==c?c.memoizedProps:f,h=a.type,k=a.updateQueue;
a.updateQueue=null;if(null!==k)try{"input"===h&&"radio"===f.type&&null!=f.name&&ab(e,f);vb(h,g);var l=vb(h,f);for(g=0;g<k.length;g+=2){var m=k[g],q=k[g+1];"style"===m?sb(e,q):"dangerouslySetInnerHTML"===m?nb(e,q):"children"===m?ob(e,q):ta(e,m,q,l)}switch(h){case "input":bb(e,f);break;case "textarea":ib(e,f);break;case "select":var r=e._wrapperState.wasMultiple;e._wrapperState.wasMultiple=!!f.multiple;var y=f.value;null!=y?fb(e,!!f.multiple,y,!1):r!==!!f.multiple&&(null!=f.defaultValue?fb(e,!!f.multiple,
f.defaultValue,!0):fb(e,!!f.multiple,f.multiple?[]:"",!1))}e[Pf]=f}catch(t){W(a,a.return,t)}}break;case 6:ck(b,a);ek(a);if(d&4){if(null===a.stateNode)throw Error(p(162));e=a.stateNode;f=a.memoizedProps;try{e.nodeValue=f}catch(t){W(a,a.return,t)}}break;case 3:ck(b,a);ek(a);if(d&4&&null!==c&&c.memoizedState.isDehydrated)try{bd(b.containerInfo)}catch(t){W(a,a.return,t)}break;case 4:ck(b,a);ek(a);break;case 13:ck(b,a);ek(a);e=a.child;e.flags&8192&&(f=null!==e.memoizedState,e.stateNode.isHidden=f,!f||
null!==e.alternate&&null!==e.alternate.memoizedState||(fk=B()));d&4&&ak(a);break;case 22:m=null!==c&&null!==c.memoizedState;a.mode&1?(U=(l=U)||m,ck(b,a),U=l):ck(b,a);ek(a);if(d&8192){l=null!==a.memoizedState;if((a.stateNode.isHidden=l)&&!m&&0!==(a.mode&1))for(V=a,m=a.child;null!==m;){for(q=V=m;null!==V;){r=V;y=r.child;switch(r.tag){case 0:case 11:case 14:case 15:Pj(4,r,r.return);break;case 1:Lj(r,r.return);var n=r.stateNode;if("function"===typeof n.componentWillUnmount){d=r;c=r.return;try{b=d,n.props=
b.memoizedProps,n.state=b.memoizedState,n.componentWillUnmount()}catch(t){W(d,c,t)}}break;case 5:Lj(r,r.return);break;case 22:if(null!==r.memoizedState){gk(q);continue}}null!==y?(y.return=r,V=y):gk(q)}m=m.sibling}a:for(m=null,q=a;;){if(5===q.tag){if(null===m){m=q;try{e=q.stateNode,l?(f=e.style,"function"===typeof f.setProperty?f.setProperty("display","none","important"):f.display="none"):(h=q.stateNode,k=q.memoizedProps.style,g=void 0!==k&&null!==k&&k.hasOwnProperty("display")?k.display:null,h.style.display=
rb("display",g))}catch(t){W(a,a.return,t)}}}else if(6===q.tag){if(null===m)try{q.stateNode.nodeValue=l?"":q.memoizedProps}catch(t){W(a,a.return,t)}}else if((22!==q.tag&&23!==q.tag||null===q.memoizedState||q===a)&&null!==q.child){q.child.return=q;q=q.child;continue}if(q===a)break a;for(;null===q.sibling;){if(null===q.return||q.return===a)break a;m===q&&(m=null);q=q.return}m===q&&(m=null);q.sibling.return=q.return;q=q.sibling}}break;case 19:ck(b,a);ek(a);d&4&&ak(a);break;case 21:break;default:ck(b,
a),ek(a)}}function ek(a){var b=a.flags;if(b&2){try{a:{for(var c=a.return;null!==c;){if(Tj(c)){var d=c;break a}c=c.return}throw Error(p(160));}switch(d.tag){case 5:var e=d.stateNode;d.flags&32&&(ob(e,""),d.flags&=-33);var f=Uj(a);Wj(a,f,e);break;case 3:case 4:var g=d.stateNode.containerInfo,h=Uj(a);Vj(a,h,g);break;default:throw Error(p(161));}}catch(k){W(a,a.return,k)}a.flags&=-3}b&4096&&(a.flags&=-4097)}function hk(a,b,c){V=a;ik(a,b,c)}
function ik(a,b,c){for(var d=0!==(a.mode&1);null!==V;){var e=V,f=e.child;if(22===e.tag&&d){var g=null!==e.memoizedState||Jj;if(!g){var h=e.alternate,k=null!==h&&null!==h.memoizedState||U;h=Jj;var l=U;Jj=g;if((U=k)&&!l)for(V=e;null!==V;)g=V,k=g.child,22===g.tag&&null!==g.memoizedState?jk(e):null!==k?(k.return=g,V=k):jk(e);for(;null!==f;)V=f,ik(f,b,c),f=f.sibling;V=e;Jj=h;U=l}kk(a,b,c)}else 0!==(e.subtreeFlags&8772)&&null!==f?(f.return=e,V=f):kk(a,b,c)}}
function kk(a){for(;null!==V;){var b=V;if(0!==(b.flags&8772)){var c=b.alternate;try{if(0!==(b.flags&8772))switch(b.tag){case 0:case 11:case 15:U||Qj(5,b);break;case 1:var d=b.stateNode;if(b.flags&4&&!U)if(null===c)d.componentDidMount();else{var e=b.elementType===b.type?c.memoizedProps:Ci(b.type,c.memoizedProps);d.componentDidUpdate(e,c.memoizedState,d.__reactInternalSnapshotBeforeUpdate)}var f=b.updateQueue;null!==f&&sh(b,f,d);break;case 3:var g=b.updateQueue;if(null!==g){c=null;if(null!==b.child)switch(b.child.tag){case 5:c=
b.child.stateNode;break;case 1:c=b.child.stateNode}sh(b,g,c)}break;case 5:var h=b.stateNode;if(null===c&&b.flags&4){c=h;var k=b.memoizedProps;switch(b.type){case "button":case "input":case "select":case "textarea":k.autoFocus&&c.focus();break;case "img":k.src&&(c.src=k.src)}}break;case 6:break;case 4:break;case 12:break;case 13:if(null===b.memoizedState){var l=b.alternate;if(null!==l){var m=l.memoizedState;if(null!==m){var q=m.dehydrated;null!==q&&bd(q)}}}break;case 19:case 17:case 21:case 22:case 23:case 25:break;
default:throw Error(p(163));}U||b.flags&512&&Rj(b)}catch(r){W(b,b.return,r)}}if(b===a){V=null;break}c=b.sibling;if(null!==c){c.return=b.return;V=c;break}V=b.return}}function gk(a){for(;null!==V;){var b=V;if(b===a){V=null;break}var c=b.sibling;if(null!==c){c.return=b.return;V=c;break}V=b.return}}
function jk(a){for(;null!==V;){var b=V;try{switch(b.tag){case 0:case 11:case 15:var c=b.return;try{Qj(4,b)}catch(k){W(b,c,k)}break;case 1:var d=b.stateNode;if("function"===typeof d.componentDidMount){var e=b.return;try{d.componentDidMount()}catch(k){W(b,e,k)}}var f=b.return;try{Rj(b)}catch(k){W(b,f,k)}break;case 5:var g=b.return;try{Rj(b)}catch(k){W(b,g,k)}}}catch(k){W(b,b.return,k)}if(b===a){V=null;break}var h=b.sibling;if(null!==h){h.return=b.return;V=h;break}V=b.return}}
var lk=Math.ceil,mk=ua.ReactCurrentDispatcher,nk=ua.ReactCurrentOwner,ok=ua.ReactCurrentBatchConfig,K=0,Q=null,Y=null,Z=0,fj=0,ej=Uf(0),T=0,pk=null,rh=0,qk=0,rk=0,sk=null,tk=null,fk=0,Gj=Infinity,uk=null,Oi=!1,Pi=null,Ri=null,vk=!1,wk=null,xk=0,yk=0,zk=null,Ak=-1,Bk=0;function R(){return 0!==(K&6)?B():-1!==Ak?Ak:Ak=B()}
function yi(a){if(0===(a.mode&1))return 1;if(0!==(K&2)&&0!==Z)return Z&-Z;if(null!==Kg.transition)return 0===Bk&&(Bk=yc()),Bk;a=C;if(0!==a)return a;a=window.event;a=void 0===a?16:jd(a.type);return a}function gi(a,b,c,d){if(50<yk)throw yk=0,zk=null,Error(p(185));Ac(a,c,d);if(0===(K&2)||a!==Q)a===Q&&(0===(K&2)&&(qk|=c),4===T&&Ck(a,Z)),Dk(a,d),1===c&&0===K&&0===(b.mode&1)&&(Gj=B()+500,fg&&jg())}
function Dk(a,b){var c=a.callbackNode;wc(a,b);var d=uc(a,a===Q?Z:0);if(0===d)null!==c&&bc(c),a.callbackNode=null,a.callbackPriority=0;else if(b=d&-d,a.callbackPriority!==b){null!=c&&bc(c);if(1===b)0===a.tag?ig(Ek.bind(null,a)):hg(Ek.bind(null,a)),Jf(function(){0===(K&6)&&jg()}),c=null;else{switch(Dc(d)){case 1:c=fc;break;case 4:c=gc;break;case 16:c=hc;break;case 536870912:c=jc;break;default:c=hc}c=Fk(c,Gk.bind(null,a))}a.callbackPriority=b;a.callbackNode=c}}
function Gk(a,b){Ak=-1;Bk=0;if(0!==(K&6))throw Error(p(327));var c=a.callbackNode;if(Hk()&&a.callbackNode!==c)return null;var d=uc(a,a===Q?Z:0);if(0===d)return null;if(0!==(d&30)||0!==(d&a.expiredLanes)||b)b=Ik(a,d);else{b=d;var e=K;K|=2;var f=Jk();if(Q!==a||Z!==b)uk=null,Gj=B()+500,Kk(a,b);do try{Lk();break}catch(h){Mk(a,h)}while(1);$g();mk.current=f;K=e;null!==Y?b=0:(Q=null,Z=0,b=T)}if(0!==b){2===b&&(e=xc(a),0!==e&&(d=e,b=Nk(a,e)));if(1===b)throw c=pk,Kk(a,0),Ck(a,d),Dk(a,B()),c;if(6===b)Ck(a,d);
else{e=a.current.alternate;if(0===(d&30)&&!Ok(e)&&(b=Ik(a,d),2===b&&(f=xc(a),0!==f&&(d=f,b=Nk(a,f))),1===b))throw c=pk,Kk(a,0),Ck(a,d),Dk(a,B()),c;a.finishedWork=e;a.finishedLanes=d;switch(b){case 0:case 1:throw Error(p(345));case 2:Pk(a,tk,uk);break;case 3:Ck(a,d);if((d&130023424)===d&&(b=fk+500-B(),10<b)){if(0!==uc(a,0))break;e=a.suspendedLanes;if((e&d)!==d){R();a.pingedLanes|=a.suspendedLanes&e;break}a.timeoutHandle=Ff(Pk.bind(null,a,tk,uk),b);break}Pk(a,tk,uk);break;case 4:Ck(a,d);if((d&4194240)===
d)break;b=a.eventTimes;for(e=-1;0<d;){var g=31-oc(d);f=1<<g;g=b[g];g>e&&(e=g);d&=~f}d=e;d=B()-d;d=(120>d?120:480>d?480:1080>d?1080:1920>d?1920:3E3>d?3E3:4320>d?4320:1960*lk(d/1960))-d;if(10<d){a.timeoutHandle=Ff(Pk.bind(null,a,tk,uk),d);break}Pk(a,tk,uk);break;case 5:Pk(a,tk,uk);break;default:throw Error(p(329));}}}Dk(a,B());return a.callbackNode===c?Gk.bind(null,a):null}
function Nk(a,b){var c=sk;a.current.memoizedState.isDehydrated&&(Kk(a,b).flags|=256);a=Ik(a,b);2!==a&&(b=tk,tk=c,null!==b&&Fj(b));return a}function Fj(a){null===tk?tk=a:tk.push.apply(tk,a)}
function Ok(a){for(var b=a;;){if(b.flags&16384){var c=b.updateQueue;if(null!==c&&(c=c.stores,null!==c))for(var d=0;d<c.length;d++){var e=c[d],f=e.getSnapshot;e=e.value;try{if(!He(f(),e))return!1}catch(g){return!1}}}c=b.child;if(b.subtreeFlags&16384&&null!==c)c.return=b,b=c;else{if(b===a)break;for(;null===b.sibling;){if(null===b.return||b.return===a)return!0;b=b.return}b.sibling.return=b.return;b=b.sibling}}return!0}
function Ck(a,b){b&=~rk;b&=~qk;a.suspendedLanes|=b;a.pingedLanes&=~b;for(a=a.expirationTimes;0<b;){var c=31-oc(b),d=1<<c;a[c]=-1;b&=~d}}function Ek(a){if(0!==(K&6))throw Error(p(327));Hk();var b=uc(a,0);if(0===(b&1))return Dk(a,B()),null;var c=Ik(a,b);if(0!==a.tag&&2===c){var d=xc(a);0!==d&&(b=d,c=Nk(a,d))}if(1===c)throw c=pk,Kk(a,0),Ck(a,b),Dk(a,B()),c;if(6===c)throw Error(p(345));a.finishedWork=a.current.alternate;a.finishedLanes=b;Pk(a,tk,uk);Dk(a,B());return null}
function Qk(a,b){var c=K;K|=1;try{return a(b)}finally{K=c,0===K&&(Gj=B()+500,fg&&jg())}}function Rk(a){null!==wk&&0===wk.tag&&0===(K&6)&&Hk();var b=K;K|=1;var c=ok.transition,d=C;try{if(ok.transition=null,C=1,a)return a()}finally{C=d,ok.transition=c,K=b,0===(K&6)&&jg()}}function Hj(){fj=ej.current;E(ej)}
function Kk(a,b){a.finishedWork=null;a.finishedLanes=0;var c=a.timeoutHandle;-1!==c&&(a.timeoutHandle=-1,Gf(c));if(null!==Y)for(c=Y.return;null!==c;){var d=c;wg(d);switch(d.tag){case 1:d=d.type.childContextTypes;null!==d&&void 0!==d&&$f();break;case 3:zh();E(Wf);E(H);Eh();break;case 5:Bh(d);break;case 4:zh();break;case 13:E(L);break;case 19:E(L);break;case 10:ah(d.type._context);break;case 22:case 23:Hj()}c=c.return}Q=a;Y=a=Pg(a.current,null);Z=fj=b;T=0;pk=null;rk=qk=rh=0;tk=sk=null;if(null!==fh){for(b=
0;b<fh.length;b++)if(c=fh[b],d=c.interleaved,null!==d){c.interleaved=null;var e=d.next,f=c.pending;if(null!==f){var g=f.next;f.next=e;d.next=g}c.pending=d}fh=null}return a}
function Mk(a,b){do{var c=Y;try{$g();Fh.current=Rh;if(Ih){for(var d=M.memoizedState;null!==d;){var e=d.queue;null!==e&&(e.pending=null);d=d.next}Ih=!1}Hh=0;O=N=M=null;Jh=!1;Kh=0;nk.current=null;if(null===c||null===c.return){T=1;pk=b;Y=null;break}a:{var f=a,g=c.return,h=c,k=b;b=Z;h.flags|=32768;if(null!==k&&"object"===typeof k&&"function"===typeof k.then){var l=k,m=h,q=m.tag;if(0===(m.mode&1)&&(0===q||11===q||15===q)){var r=m.alternate;r?(m.updateQueue=r.updateQueue,m.memoizedState=r.memoizedState,
m.lanes=r.lanes):(m.updateQueue=null,m.memoizedState=null)}var y=Ui(g);if(null!==y){y.flags&=-257;Vi(y,g,h,f,b);y.mode&1&&Si(f,l,b);b=y;k=l;var n=b.updateQueue;if(null===n){var t=new Set;t.add(k);b.updateQueue=t}else n.add(k);break a}else{if(0===(b&1)){Si(f,l,b);tj();break a}k=Error(p(426))}}else if(I&&h.mode&1){var J=Ui(g);if(null!==J){0===(J.flags&65536)&&(J.flags|=256);Vi(J,g,h,f,b);Jg(Ji(k,h));break a}}f=k=Ji(k,h);4!==T&&(T=2);null===sk?sk=[f]:sk.push(f);f=g;do{switch(f.tag){case 3:f.flags|=65536;
b&=-b;f.lanes|=b;var x=Ni(f,k,b);ph(f,x);break a;case 1:h=k;var w=f.type,u=f.stateNode;if(0===(f.flags&128)&&("function"===typeof w.getDerivedStateFromError||null!==u&&"function"===typeof u.componentDidCatch&&(null===Ri||!Ri.has(u)))){f.flags|=65536;b&=-b;f.lanes|=b;var F=Qi(f,h,b);ph(f,F);break a}}f=f.return}while(null!==f)}Sk(c)}catch(na){b=na;Y===c&&null!==c&&(Y=c=c.return);continue}break}while(1)}function Jk(){var a=mk.current;mk.current=Rh;return null===a?Rh:a}
function tj(){if(0===T||3===T||2===T)T=4;null===Q||0===(rh&268435455)&&0===(qk&268435455)||Ck(Q,Z)}function Ik(a,b){var c=K;K|=2;var d=Jk();if(Q!==a||Z!==b)uk=null,Kk(a,b);do try{Tk();break}catch(e){Mk(a,e)}while(1);$g();K=c;mk.current=d;if(null!==Y)throw Error(p(261));Q=null;Z=0;return T}function Tk(){for(;null!==Y;)Uk(Y)}function Lk(){for(;null!==Y&&!cc();)Uk(Y)}function Uk(a){var b=Vk(a.alternate,a,fj);a.memoizedProps=a.pendingProps;null===b?Sk(a):Y=b;nk.current=null}
function Sk(a){var b=a;do{var c=b.alternate;a=b.return;if(0===(b.flags&32768)){if(c=Ej(c,b,fj),null!==c){Y=c;return}}else{c=Ij(c,b);if(null!==c){c.flags&=32767;Y=c;return}if(null!==a)a.flags|=32768,a.subtreeFlags=0,a.deletions=null;else{T=6;Y=null;return}}b=b.sibling;if(null!==b){Y=b;return}Y=b=a}while(null!==b);0===T&&(T=5)}function Pk(a,b,c){var d=C,e=ok.transition;try{ok.transition=null,C=1,Wk(a,b,c,d)}finally{ok.transition=e,C=d}return null}
function Wk(a,b,c,d){do Hk();while(null!==wk);if(0!==(K&6))throw Error(p(327));c=a.finishedWork;var e=a.finishedLanes;if(null===c)return null;a.finishedWork=null;a.finishedLanes=0;if(c===a.current)throw Error(p(177));a.callbackNode=null;a.callbackPriority=0;var f=c.lanes|c.childLanes;Bc(a,f);a===Q&&(Y=Q=null,Z=0);0===(c.subtreeFlags&2064)&&0===(c.flags&2064)||vk||(vk=!0,Fk(hc,function(){Hk();return null}));f=0!==(c.flags&15990);if(0!==(c.subtreeFlags&15990)||f){f=ok.transition;ok.transition=null;
var g=C;C=1;var h=K;K|=4;nk.current=null;Oj(a,c);dk(c,a);Oe(Df);dd=!!Cf;Df=Cf=null;a.current=c;hk(c,a,e);dc();K=h;C=g;ok.transition=f}else a.current=c;vk&&(vk=!1,wk=a,xk=e);f=a.pendingLanes;0===f&&(Ri=null);mc(c.stateNode,d);Dk(a,B());if(null!==b)for(d=a.onRecoverableError,c=0;c<b.length;c++)e=b[c],d(e.value,{componentStack:e.stack,digest:e.digest});if(Oi)throw Oi=!1,a=Pi,Pi=null,a;0!==(xk&1)&&0!==a.tag&&Hk();f=a.pendingLanes;0!==(f&1)?a===zk?yk++:(yk=0,zk=a):yk=0;jg();return null}
function Hk(){if(null!==wk){var a=Dc(xk),b=ok.transition,c=C;try{ok.transition=null;C=16>a?16:a;if(null===wk)var d=!1;else{a=wk;wk=null;xk=0;if(0!==(K&6))throw Error(p(331));var e=K;K|=4;for(V=a.current;null!==V;){var f=V,g=f.child;if(0!==(V.flags&16)){var h=f.deletions;if(null!==h){for(var k=0;k<h.length;k++){var l=h[k];for(V=l;null!==V;){var m=V;switch(m.tag){case 0:case 11:case 15:Pj(8,m,f)}var q=m.child;if(null!==q)q.return=m,V=q;else for(;null!==V;){m=V;var r=m.sibling,y=m.return;Sj(m);if(m===
l){V=null;break}if(null!==r){r.return=y;V=r;break}V=y}}}var n=f.alternate;if(null!==n){var t=n.child;if(null!==t){n.child=null;do{var J=t.sibling;t.sibling=null;t=J}while(null!==t)}}V=f}}if(0!==(f.subtreeFlags&2064)&&null!==g)g.return=f,V=g;else b:for(;null!==V;){f=V;if(0!==(f.flags&2048))switch(f.tag){case 0:case 11:case 15:Pj(9,f,f.return)}var x=f.sibling;if(null!==x){x.return=f.return;V=x;break b}V=f.return}}var w=a.current;for(V=w;null!==V;){g=V;var u=g.child;if(0!==(g.subtreeFlags&2064)&&null!==
u)u.return=g,V=u;else b:for(g=w;null!==V;){h=V;if(0!==(h.flags&2048))try{switch(h.tag){case 0:case 11:case 15:Qj(9,h)}}catch(na){W(h,h.return,na)}if(h===g){V=null;break b}var F=h.sibling;if(null!==F){F.return=h.return;V=F;break b}V=h.return}}K=e;jg();if(lc&&"function"===typeof lc.onPostCommitFiberRoot)try{lc.onPostCommitFiberRoot(kc,a)}catch(na){}d=!0}return d}finally{C=c,ok.transition=b}}return!1}function Xk(a,b,c){b=Ji(c,b);b=Ni(a,b,1);a=nh(a,b,1);b=R();null!==a&&(Ac(a,1,b),Dk(a,b))}
function W(a,b,c){if(3===a.tag)Xk(a,a,c);else for(;null!==b;){if(3===b.tag){Xk(b,a,c);break}else if(1===b.tag){var d=b.stateNode;if("function"===typeof b.type.getDerivedStateFromError||"function"===typeof d.componentDidCatch&&(null===Ri||!Ri.has(d))){a=Ji(c,a);a=Qi(b,a,1);b=nh(b,a,1);a=R();null!==b&&(Ac(b,1,a),Dk(b,a));break}}b=b.return}}
function Ti(a,b,c){var d=a.pingCache;null!==d&&d.delete(b);b=R();a.pingedLanes|=a.suspendedLanes&c;Q===a&&(Z&c)===c&&(4===T||3===T&&(Z&130023424)===Z&&500>B()-fk?Kk(a,0):rk|=c);Dk(a,b)}function Yk(a,b){0===b&&(0===(a.mode&1)?b=1:(b=sc,sc<<=1,0===(sc&130023424)&&(sc=4194304)));var c=R();a=ih(a,b);null!==a&&(Ac(a,b,c),Dk(a,c))}function uj(a){var b=a.memoizedState,c=0;null!==b&&(c=b.retryLane);Yk(a,c)}
function bk(a,b){var c=0;switch(a.tag){case 13:var d=a.stateNode;var e=a.memoizedState;null!==e&&(c=e.retryLane);break;case 19:d=a.stateNode;break;default:throw Error(p(314));}null!==d&&d.delete(b);Yk(a,c)}var Vk;
Vk=function(a,b,c){if(null!==a)if(a.memoizedProps!==b.pendingProps||Wf.current)dh=!0;else{if(0===(a.lanes&c)&&0===(b.flags&128))return dh=!1,yj(a,b,c);dh=0!==(a.flags&131072)?!0:!1}else dh=!1,I&&0!==(b.flags&1048576)&&ug(b,ng,b.index);b.lanes=0;switch(b.tag){case 2:var d=b.type;ij(a,b);a=b.pendingProps;var e=Yf(b,H.current);ch(b,c);e=Nh(null,b,d,a,e,c);var f=Sh();b.flags|=1;"object"===typeof e&&null!==e&&"function"===typeof e.render&&void 0===e.$$typeof?(b.tag=1,b.memoizedState=null,b.updateQueue=
null,Zf(d)?(f=!0,cg(b)):f=!1,b.memoizedState=null!==e.state&&void 0!==e.state?e.state:null,kh(b),e.updater=Ei,b.stateNode=e,e._reactInternals=b,Ii(b,d,a,c),b=jj(null,b,d,!0,f,c)):(b.tag=0,I&&f&&vg(b),Xi(null,b,e,c),b=b.child);return b;case 16:d=b.elementType;a:{ij(a,b);a=b.pendingProps;e=d._init;d=e(d._payload);b.type=d;e=b.tag=Zk(d);a=Ci(d,a);switch(e){case 0:b=cj(null,b,d,a,c);break a;case 1:b=hj(null,b,d,a,c);break a;case 11:b=Yi(null,b,d,a,c);break a;case 14:b=$i(null,b,d,Ci(d.type,a),c);break a}throw Error(p(306,
d,""));}return b;case 0:return d=b.type,e=b.pendingProps,e=b.elementType===d?e:Ci(d,e),cj(a,b,d,e,c);case 1:return d=b.type,e=b.pendingProps,e=b.elementType===d?e:Ci(d,e),hj(a,b,d,e,c);case 3:a:{kj(b);if(null===a)throw Error(p(387));d=b.pendingProps;f=b.memoizedState;e=f.element;lh(a,b);qh(b,d,null,c);var g=b.memoizedState;d=g.element;if(f.isDehydrated)if(f={element:d,isDehydrated:!1,cache:g.cache,pendingSuspenseBoundaries:g.pendingSuspenseBoundaries,transitions:g.transitions},b.updateQueue.baseState=
f,b.memoizedState=f,b.flags&256){e=Ji(Error(p(423)),b);b=lj(a,b,d,c,e);break a}else if(d!==e){e=Ji(Error(p(424)),b);b=lj(a,b,d,c,e);break a}else for(yg=Lf(b.stateNode.containerInfo.firstChild),xg=b,I=!0,zg=null,c=Vg(b,null,d,c),b.child=c;c;)c.flags=c.flags&-3|4096,c=c.sibling;else{Ig();if(d===e){b=Zi(a,b,c);break a}Xi(a,b,d,c)}b=b.child}return b;case 5:return Ah(b),null===a&&Eg(b),d=b.type,e=b.pendingProps,f=null!==a?a.memoizedProps:null,g=e.children,Ef(d,e)?g=null:null!==f&&Ef(d,f)&&(b.flags|=32),
gj(a,b),Xi(a,b,g,c),b.child;case 6:return null===a&&Eg(b),null;case 13:return oj(a,b,c);case 4:return yh(b,b.stateNode.containerInfo),d=b.pendingProps,null===a?b.child=Ug(b,null,d,c):Xi(a,b,d,c),b.child;case 11:return d=b.type,e=b.pendingProps,e=b.elementType===d?e:Ci(d,e),Yi(a,b,d,e,c);case 7:return Xi(a,b,b.pendingProps,c),b.child;case 8:return Xi(a,b,b.pendingProps.children,c),b.child;case 12:return Xi(a,b,b.pendingProps.children,c),b.child;case 10:a:{d=b.type._context;e=b.pendingProps;f=b.memoizedProps;
g=e.value;G(Wg,d._currentValue);d._currentValue=g;if(null!==f)if(He(f.value,g)){if(f.children===e.children&&!Wf.current){b=Zi(a,b,c);break a}}else for(f=b.child,null!==f&&(f.return=b);null!==f;){var h=f.dependencies;if(null!==h){g=f.child;for(var k=h.firstContext;null!==k;){if(k.context===d){if(1===f.tag){k=mh(-1,c&-c);k.tag=2;var l=f.updateQueue;if(null!==l){l=l.shared;var m=l.pending;null===m?k.next=k:(k.next=m.next,m.next=k);l.pending=k}}f.lanes|=c;k=f.alternate;null!==k&&(k.lanes|=c);bh(f.return,
c,b);h.lanes|=c;break}k=k.next}}else if(10===f.tag)g=f.type===b.type?null:f.child;else if(18===f.tag){g=f.return;if(null===g)throw Error(p(341));g.lanes|=c;h=g.alternate;null!==h&&(h.lanes|=c);bh(g,c,b);g=f.sibling}else g=f.child;if(null!==g)g.return=f;else for(g=f;null!==g;){if(g===b){g=null;break}f=g.sibling;if(null!==f){f.return=g.return;g=f;break}g=g.return}f=g}Xi(a,b,e.children,c);b=b.child}return b;case 9:return e=b.type,d=b.pendingProps.children,ch(b,c),e=eh(e),d=d(e),b.flags|=1,Xi(a,b,d,c),
b.child;case 14:return d=b.type,e=Ci(d,b.pendingProps),e=Ci(d.type,e),$i(a,b,d,e,c);case 15:return bj(a,b,b.type,b.pendingProps,c);case 17:return d=b.type,e=b.pendingProps,e=b.elementType===d?e:Ci(d,e),ij(a,b),b.tag=1,Zf(d)?(a=!0,cg(b)):a=!1,ch(b,c),Gi(b,d,e),Ii(b,d,e,c),jj(null,b,d,!0,a,c);case 19:return xj(a,b,c);case 22:return dj(a,b,c)}throw Error(p(156,b.tag));};function Fk(a,b){return ac(a,b)}
function $k(a,b,c,d){this.tag=a;this.key=c;this.sibling=this.child=this.return=this.stateNode=this.type=this.elementType=null;this.index=0;this.ref=null;this.pendingProps=b;this.dependencies=this.memoizedState=this.updateQueue=this.memoizedProps=null;this.mode=d;this.subtreeFlags=this.flags=0;this.deletions=null;this.childLanes=this.lanes=0;this.alternate=null}function Bg(a,b,c,d){return new $k(a,b,c,d)}function aj(a){a=a.prototype;return!(!a||!a.isReactComponent)}
function Zk(a){if("function"===typeof a)return aj(a)?1:0;if(void 0!==a&&null!==a){a=a.$$typeof;if(a===Da)return 11;if(a===Ga)return 14}return 2}
function Pg(a,b){var c=a.alternate;null===c?(c=Bg(a.tag,b,a.key,a.mode),c.elementType=a.elementType,c.type=a.type,c.stateNode=a.stateNode,c.alternate=a,a.alternate=c):(c.pendingProps=b,c.type=a.type,c.flags=0,c.subtreeFlags=0,c.deletions=null);c.flags=a.flags&14680064;c.childLanes=a.childLanes;c.lanes=a.lanes;c.child=a.child;c.memoizedProps=a.memoizedProps;c.memoizedState=a.memoizedState;c.updateQueue=a.updateQueue;b=a.dependencies;c.dependencies=null===b?null:{lanes:b.lanes,firstContext:b.firstContext};
c.sibling=a.sibling;c.index=a.index;c.ref=a.ref;return c}
function Rg(a,b,c,d,e,f){var g=2;d=a;if("function"===typeof a)aj(a)&&(g=1);else if("string"===typeof a)g=5;else a:switch(a){case ya:return Tg(c.children,e,f,b);case za:g=8;e|=8;break;case Aa:return a=Bg(12,c,b,e|2),a.elementType=Aa,a.lanes=f,a;case Ea:return a=Bg(13,c,b,e),a.elementType=Ea,a.lanes=f,a;case Fa:return a=Bg(19,c,b,e),a.elementType=Fa,a.lanes=f,a;case Ia:return pj(c,e,f,b);default:if("object"===typeof a&&null!==a)switch(a.$$typeof){case Ba:g=10;break a;case Ca:g=9;break a;case Da:g=11;
break a;case Ga:g=14;break a;case Ha:g=16;d=null;break a}throw Error(p(130,null==a?a:typeof a,""));}b=Bg(g,c,b,e);b.elementType=a;b.type=d;b.lanes=f;return b}function Tg(a,b,c,d){a=Bg(7,a,d,b);a.lanes=c;return a}function pj(a,b,c,d){a=Bg(22,a,d,b);a.elementType=Ia;a.lanes=c;a.stateNode={isHidden:!1};return a}function Qg(a,b,c){a=Bg(6,a,null,b);a.lanes=c;return a}
function Sg(a,b,c){b=Bg(4,null!==a.children?a.children:[],a.key,b);b.lanes=c;b.stateNode={containerInfo:a.containerInfo,pendingChildren:null,implementation:a.implementation};return b}
function al(a,b,c,d,e){this.tag=b;this.containerInfo=a;this.finishedWork=this.pingCache=this.current=this.pendingChildren=null;this.timeoutHandle=-1;this.callbackNode=this.pendingContext=this.context=null;this.callbackPriority=0;this.eventTimes=zc(0);this.expirationTimes=zc(-1);this.entangledLanes=this.finishedLanes=this.mutableReadLanes=this.expiredLanes=this.pingedLanes=this.suspendedLanes=this.pendingLanes=0;this.entanglements=zc(0);this.identifierPrefix=d;this.onRecoverableError=e;this.mutableSourceEagerHydrationData=
null}function bl(a,b,c,d,e,f,g,h,k){a=new al(a,b,c,h,k);1===b?(b=1,!0===f&&(b|=8)):b=0;f=Bg(3,null,null,b);a.current=f;f.stateNode=a;f.memoizedState={element:d,isDehydrated:c,cache:null,transitions:null,pendingSuspenseBoundaries:null};kh(f);return a}function cl(a,b,c){var d=3<arguments.length&&void 0!==arguments[3]?arguments[3]:null;return{$$typeof:wa,key:null==d?null:""+d,children:a,containerInfo:b,implementation:c}}
function dl(a){if(!a)return Vf;a=a._reactInternals;a:{if(Vb(a)!==a||1!==a.tag)throw Error(p(170));var b=a;do{switch(b.tag){case 3:b=b.stateNode.context;break a;case 1:if(Zf(b.type)){b=b.stateNode.__reactInternalMemoizedMergedChildContext;break a}}b=b.return}while(null!==b);throw Error(p(171));}if(1===a.tag){var c=a.type;if(Zf(c))return bg(a,c,b)}return b}
function el(a,b,c,d,e,f,g,h,k){a=bl(c,d,!0,a,e,f,g,h,k);a.context=dl(null);c=a.current;d=R();e=yi(c);f=mh(d,e);f.callback=void 0!==b&&null!==b?b:null;nh(c,f,e);a.current.lanes=e;Ac(a,e,d);Dk(a,d);return a}function fl(a,b,c,d){var e=b.current,f=R(),g=yi(e);c=dl(c);null===b.context?b.context=c:b.pendingContext=c;b=mh(f,g);b.payload={element:a};d=void 0===d?null:d;null!==d&&(b.callback=d);a=nh(e,b,g);null!==a&&(gi(a,e,g,f),oh(a,e,g));return g}
function gl(a){a=a.current;if(!a.child)return null;switch(a.child.tag){case 5:return a.child.stateNode;default:return a.child.stateNode}}function hl(a,b){a=a.memoizedState;if(null!==a&&null!==a.dehydrated){var c=a.retryLane;a.retryLane=0!==c&&c<b?c:b}}function il(a,b){hl(a,b);(a=a.alternate)&&hl(a,b)}function jl(){return null}var kl="function"===typeof reportError?reportError:function(a){console.error(a)};function ll(a){this._internalRoot=a}
ml.prototype.render=ll.prototype.render=function(a){var b=this._internalRoot;if(null===b)throw Error(p(409));fl(a,b,null,null)};ml.prototype.unmount=ll.prototype.unmount=function(){var a=this._internalRoot;if(null!==a){this._internalRoot=null;var b=a.containerInfo;Rk(function(){fl(null,a,null,null)});b[uf]=null}};function ml(a){this._internalRoot=a}
ml.prototype.unstable_scheduleHydration=function(a){if(a){var b=Hc();a={blockedOn:null,target:a,priority:b};for(var c=0;c<Qc.length&&0!==b&&b<Qc[c].priority;c++);Qc.splice(c,0,a);0===c&&Vc(a)}};function nl(a){return!(!a||1!==a.nodeType&&9!==a.nodeType&&11!==a.nodeType)}function ol(a){return!(!a||1!==a.nodeType&&9!==a.nodeType&&11!==a.nodeType&&(8!==a.nodeType||" react-mount-point-unstable "!==a.nodeValue))}function pl(){}
function ql(a,b,c,d,e){if(e){if("function"===typeof d){var f=d;d=function(){var a=gl(g);f.call(a)}}var g=el(b,d,a,0,null,!1,!1,"",pl);a._reactRootContainer=g;a[uf]=g.current;sf(8===a.nodeType?a.parentNode:a);Rk();return g}for(;e=a.lastChild;)a.removeChild(e);if("function"===typeof d){var h=d;d=function(){var a=gl(k);h.call(a)}}var k=bl(a,0,!1,null,null,!1,!1,"",pl);a._reactRootContainer=k;a[uf]=k.current;sf(8===a.nodeType?a.parentNode:a);Rk(function(){fl(b,k,c,d)});return k}
function rl(a,b,c,d,e){var f=c._reactRootContainer;if(f){var g=f;if("function"===typeof e){var h=e;e=function(){var a=gl(g);h.call(a)}}fl(b,g,a,e)}else g=ql(c,b,a,e,d);return gl(g)}Ec=function(a){switch(a.tag){case 3:var b=a.stateNode;if(b.current.memoizedState.isDehydrated){var c=tc(b.pendingLanes);0!==c&&(Cc(b,c|1),Dk(b,B()),0===(K&6)&&(Gj=B()+500,jg()))}break;case 13:Rk(function(){var b=ih(a,1);if(null!==b){var c=R();gi(b,a,1,c)}}),il(a,1)}};
Fc=function(a){if(13===a.tag){var b=ih(a,134217728);if(null!==b){var c=R();gi(b,a,134217728,c)}il(a,134217728)}};Gc=function(a){if(13===a.tag){var b=yi(a),c=ih(a,b);if(null!==c){var d=R();gi(c,a,b,d)}il(a,b)}};Hc=function(){return C};Ic=function(a,b){var c=C;try{return C=a,b()}finally{C=c}};
yb=function(a,b,c){switch(b){case "input":bb(a,c);b=c.name;if("radio"===c.type&&null!=b){for(c=a;c.parentNode;)c=c.parentNode;c=c.querySelectorAll("input[name="+JSON.stringify(""+b)+'][type="radio"]');for(b=0;b<c.length;b++){var d=c[b];if(d!==a&&d.form===a.form){var e=Db(d);if(!e)throw Error(p(90));Wa(d);bb(d,e)}}}break;case "textarea":ib(a,c);break;case "select":b=c.value,null!=b&&fb(a,!!c.multiple,b,!1)}};Gb=Qk;Hb=Rk;
var sl={usingClientEntryPoint:!1,Events:[Cb,ue,Db,Eb,Fb,Qk]},tl={findFiberByHostInstance:Wc,bundleType:0,version:"18.3.1",rendererPackageName:"react-dom"};
var ul={bundleType:tl.bundleType,version:tl.version,rendererPackageName:tl.rendererPackageName,rendererConfig:tl.rendererConfig,overrideHookState:null,overrideHookStateDeletePath:null,overrideHookStateRenamePath:null,overrideProps:null,overridePropsDeletePath:null,overridePropsRenamePath:null,setErrorHandler:null,setSuspenseHandler:null,scheduleUpdate:null,currentDispatcherRef:ua.ReactCurrentDispatcher,findHostInstanceByFiber:function(a){a=Zb(a);return null===a?null:a.stateNode},findFiberByHostInstance:tl.findFiberByHostInstance||
jl,findHostInstancesForRefresh:null,scheduleRefresh:null,scheduleRoot:null,setRefreshHandler:null,getCurrentFiber:null,reconcilerVersion:"18.3.1-next-f1338f8080-20240426"};if("undefined"!==typeof __REACT_DEVTOOLS_GLOBAL_HOOK__){var vl=__REACT_DEVTOOLS_GLOBAL_HOOK__;if(!vl.isDisabled&&vl.supportsFiber)try{kc=vl.inject(ul),lc=vl}catch(a){}}exports.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED=sl;
exports.createPortal=function(a,b){var c=2<arguments.length&&void 0!==arguments[2]?arguments[2]:null;if(!nl(b))throw Error(p(200));return cl(a,b,null,c)};exports.createRoot=function(a,b){if(!nl(a))throw Error(p(299));var c=!1,d="",e=kl;null!==b&&void 0!==b&&(!0===b.unstable_strictMode&&(c=!0),void 0!==b.identifierPrefix&&(d=b.identifierPrefix),void 0!==b.onRecoverableError&&(e=b.onRecoverableError));b=bl(a,1,!1,null,null,c,!1,d,e);a[uf]=b.current;sf(8===a.nodeType?a.parentNode:a);return new ll(b)};
exports.findDOMNode=function(a){if(null==a)return null;if(1===a.nodeType)return a;var b=a._reactInternals;if(void 0===b){if("function"===typeof a.render)throw Error(p(188));a=Object.keys(a).join(",");throw Error(p(268,a));}a=Zb(b);a=null===a?null:a.stateNode;return a};exports.flushSync=function(a){return Rk(a)};exports.hydrate=function(a,b,c){if(!ol(b))throw Error(p(200));return rl(null,a,b,!0,c)};
exports.hydrateRoot=function(a,b,c){if(!nl(a))throw Error(p(405));var d=null!=c&&c.hydratedSources||null,e=!1,f="",g=kl;null!==c&&void 0!==c&&(!0===c.unstable_strictMode&&(e=!0),void 0!==c.identifierPrefix&&(f=c.identifierPrefix),void 0!==c.onRecoverableError&&(g=c.onRecoverableError));b=el(b,null,a,1,null!=c?c:null,e,!1,f,g);a[uf]=b.current;sf(a);if(d)for(a=0;a<d.length;a++)c=d[a],e=c._getVersion,e=e(c._source),null==b.mutableSourceEagerHydrationData?b.mutableSourceEagerHydrationData=[c,e]:b.mutableSourceEagerHydrationData.push(c,
e);return new ml(b)};exports.render=function(a,b,c){if(!ol(b))throw Error(p(200));return rl(null,a,b,!1,c)};exports.unmountComponentAtNode=function(a){if(!ol(a))throw Error(p(40));return a._reactRootContainer?(Rk(function(){rl(null,null,a,!1,function(){a._reactRootContainer=null;a[uf]=null})}),!0):!1};exports.unstable_batchedUpdates=Qk;
exports.unstable_renderSubtreeIntoContainer=function(a,b,c,d){if(!ol(c))throw Error(p(200));if(null==a||void 0===a._reactInternals)throw Error(p(38));return rl(a,b,c,!1,d)};exports.version="18.3.1-next-f1338f8080-20240426";


/***/ }),

/***/ 961:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



function checkDCE() {
  /* global __REACT_DEVTOOLS_GLOBAL_HOOK__ */
  if (
    typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ === 'undefined' ||
    typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE !== 'function'
  ) {
    return;
  }
  if (false) // removed by dead control flow
{}
  try {
    // Verify that the code above has been dead code eliminated (DCE'd).
    __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(checkDCE);
  } catch (err) {
    // DevTools shouldn't crash React, no matter what.
    // We should still report in case we break this code.
    console.error(err);
  }
}

if (true) {
  // DCE check should happen before ReactDOM bundle executes so that
  // DevTools can report bad minification during injection.
  checkDCE();
  module.exports = __webpack_require__(551);
} else // removed by dead control flow
{}


/***/ }),

/***/ 982:
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



if (true) {
  module.exports = __webpack_require__(463);
} else // removed by dead control flow
{}


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};

// UNUSED EXPORTS: default

// NAMESPACE OBJECT: ./src/hooks/index.js
var hooks_namespaceObject = {};
__webpack_require__.r(hooks_namespaceObject);
__webpack_require__.d(hooks_namespaceObject, {
  useCanvasState: () => (useCanvasState),
  useClipboard: () => (useClipboard),
  useContextMenu: () => (useContextMenu),
  useDragAndDrop: () => (useDragAndDrop),
  useHistory: () => (useHistory),
  useKeyboardShortcuts: () => (useKeyboardShortcuts),
  useResize: () => (useResize),
  useRotation: () => (useRotation),
  useSelection: () => (useSelection),
  useZoom: () => (useZoom)
});

// EXTERNAL MODULE: ./node_modules/react/index.js
var react = __webpack_require__(540);
// EXTERNAL MODULE: ./node_modules/react-dom/index.js
var react_dom = __webpack_require__(961);
;// ./src/hooks/useResize.js
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useResize = function useResize(_ref) {
  var onElementResize = _ref.onElementResize,
    _ref$snapToGrid = _ref.snapToGrid,
    snapToGrid = _ref$snapToGrid === void 0 ? true : _ref$snapToGrid,
    _ref$gridSize = _ref.gridSize,
    gridSize = _ref$gridSize === void 0 ? 10 : _ref$gridSize,
    _ref$minWidth = _ref.minWidth,
    minWidth = _ref$minWidth === void 0 ? 20 : _ref$minWidth,
    _ref$minHeight = _ref.minHeight,
    minHeight = _ref$minHeight === void 0 ? 20 : _ref$minHeight,
    _ref$zoom = _ref.zoom,
    zoom = _ref$zoom === void 0 ? 1 : _ref$zoom,
    _ref$canvasRect = _ref.canvasRect,
    canvasRect = _ref$canvasRect === void 0 ? null : _ref$canvasRect,
    _ref$canvasWidth = _ref.canvasWidth,
    canvasWidth = _ref$canvasWidth === void 0 ? 595 : _ref$canvasWidth,
    _ref$canvasHeight = _ref.canvasHeight,
    canvasHeight = _ref$canvasHeight === void 0 ? 842 : _ref$canvasHeight,
    _ref$guides = _ref.guides,
    guides = _ref$guides === void 0 ? {
      horizontal: [],
      vertical: []
    } : _ref$guides,
    _ref$snapToGuides = _ref.snapToGuides,
    snapToGuides = _ref$snapToGuides === void 0 ? true : _ref$snapToGuides,
    _ref$elementType = _ref.elementType,
    elementType = _ref$elementType === void 0 ? null : _ref$elementType;
  var _useState = (0,react.useState)(false),
    _useState2 = _slicedToArray(_useState, 2),
    isResizing = _useState2[0],
    setIsResizing = _useState2[1];
  var _useState3 = (0,react.useState)(null),
    _useState4 = _slicedToArray(_useState3, 2),
    resizeHandle = _useState4[0],
    setResizeHandle = _useState4[1];
  var resizeStartPos = (0,react.useRef)({
    x: 0,
    y: 0
  });
  var originalRect = (0,react.useRef)({
    x: 0,
    y: 0,
    width: 0,
    height: 0
  });
  var snapToGridValue = (0,react.useCallback)(function (value) {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);
  var snapToGuidesValue = (0,react.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    if (!snapToGuides) return value;
    var guideArray = isHorizontal ? guides.horizontal : guides.vertical;
    var snapTolerance = 5; // pixels
    var _iterator = _createForOfIteratorHelper(guideArray),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var guide = _step.value;
        if (Math.abs(value - guide) <= snapTolerance) {
          return guide;
        }
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
    return value;
  }, [snapToGuides, guides]);
  var snapValue = (0,react.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    var snapped = value;

    // Appliquer l'aimantation à la grille d'abord
    snapped = snapToGridValue(snapped);

    // Puis appliquer l'aimantation aux guides
    snapped = snapToGuidesValue(snapped, isHorizontal);
    return snapped;
  }, [snapToGridValue, snapToGuidesValue]);
  var handleResizeStart = (0,react.useCallback)(function (e, handle, elementRect) {
    var canvasRectParam = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
    var zoomLevel = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
    e.preventDefault();
    e.stopPropagation();

    // Vérifier que l'élément source existe encore dans le DOM
    if (!e.target || !e.target.isConnected) {
      console.warn('Resize handle target no longer exists in DOM');
      return;
    }

    // Vérifier que l'élément parent existe encore
    if (!e.target.parentNode || !e.target.parentNode.isConnected) {
      console.warn('Resize handle parent no longer exists in DOM');
      return;
    }
    setIsResizing(true);
    setResizeHandle(handle);

    // Ajuster les coordonnées pour le zoom
    var currentCanvasRect = canvasRectParam || canvasRect || {
      left: 0,
      top: 0
    };
    var currentZoom = zoomLevel || zoom || 1;
    resizeStartPos.current = {
      x: (e.clientX - currentCanvasRect.left) / currentZoom,
      y: (e.clientY - currentCanvasRect.top) / currentZoom
    };
    originalRect.current = _objectSpread({}, elementRect);
    var handleMouseMove = function handleMouseMove(moveEvent) {
      var mouseX = (moveEvent.clientX - currentCanvasRect.left) / currentZoom;
      var mouseY = (moveEvent.clientY - currentCanvasRect.top) / currentZoom;
      var deltaX = mouseX - resizeStartPos.current.x;
      var deltaY = mouseY - resizeStartPos.current.y;
      var newRect = _objectSpread({}, originalRect.current);

      // Pour les dividers et lignes, empêcher la modification de la hauteur seulement
      var isFixedHeight = elementType === 'divider' || elementType === 'line';
      switch (handle) {
        case 'nw':
          if (!isFixedHeight) {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          } else {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
          }
          break;
        case 'ne':
          if (!isFixedHeight) {
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          } else {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
          }
          break;
        case 'sw':
          if (!isFixedHeight) {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          } else {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
          }
          break;
        case 'se':
          if (!isFixedHeight) {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          } else {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
          }
          break;
        case 'n':
          if (!isFixedHeight) {
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          }
          break;
        case 's':
          if (!isFixedHeight) {
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          }
          break;
        case 'w':
          newRect.x = snapValue(originalRect.current.x + deltaX, false);
          newRect.width = snapValue(originalRect.current.width - deltaX, false);
          break;
        case 'e':
          newRect.width = snapValue(originalRect.current.width + deltaX, false);
          break;
        default:
          break;
      }

      // Appliquer les contraintes de taille minimale
      if (newRect.width < minWidth) {
        if (handle.includes('w')) {
          newRect.x = originalRect.current.x + originalRect.current.width - minWidth;
        }
        newRect.width = minWidth;
      }
      if (newRect.height < minHeight) {
        if (handle.includes('n')) {
          newRect.y = originalRect.current.y + originalRect.current.height - minHeight;
        }
        newRect.height = minHeight;
      }

      // Appliquer les contraintes du canvas
      var effectiveCanvasWidth = canvasRectParam ? canvasRectParam.width / currentZoom : canvasWidth;
      var effectiveCanvasHeight = canvasRectParam ? canvasRectParam.height / currentZoom : canvasHeight;
      newRect.x = Math.max(0, Math.min(effectiveCanvasWidth - newRect.width, newRect.x));
      newRect.y = Math.max(0, Math.min(effectiveCanvasHeight - newRect.height, newRect.y));
      if (onElementResize) {
        onElementResize(newRect);
      }
    };
    var _handleMouseUp = function handleMouseUp() {
      setIsResizing(false);
      setResizeHandle(null);
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', _handleMouseUp);
    };
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', _handleMouseUp);
  }, [snapToGridValue, minWidth, minHeight, onElementResize, zoom, canvasRect, canvasWidth, canvasHeight]);
  return {
    isResizing: isResizing,
    resizeHandle: resizeHandle,
    handleResizeStart: handleResizeStart
  };
};
;// ./src/hooks/useRotation.js
function useRotation_slicedToArray(r, e) { return useRotation_arrayWithHoles(r) || useRotation_iterableToArrayLimit(r, e) || useRotation_unsupportedIterableToArray(r, e) || useRotation_nonIterableRest(); }
function useRotation_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useRotation_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useRotation_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useRotation_arrayLikeToArray(r, a) : void 0; } }
function useRotation_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useRotation_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useRotation_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useRotation = function useRotation(onElementRotate) {
  var rotationStep = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 15;
  var rotationSnap = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
  var _useState = (0,react.useState)(false),
    _useState2 = useRotation_slicedToArray(_useState, 2),
    isRotating = _useState2[0],
    setIsRotating = _useState2[1];
  var _useState3 = (0,react.useState)({
      angle: 0,
      centerX: 0,
      centerY: 0
    }),
    _useState4 = useRotation_slicedToArray(_useState3, 2),
    rotationStart = _useState4[0],
    setRotationStart = _useState4[1];
  var handleRotationStart = (0,react.useCallback)(function (e, element) {
    e.preventDefault();
    e.stopPropagation();
    setIsRotating(true);
    var rect = e.currentTarget.getBoundingClientRect();
    var centerX = rect.left + rect.width / 2;
    var centerY = rect.top + rect.height / 2;

    // Calculer l'angle initial entre le centre de l'élément et la position de la souris
    var deltaX = e.clientX - centerX;
    var deltaY = e.clientY - centerY;
    var initialAngle = Math.atan2(deltaY, deltaX) * (180 / Math.PI);
    setRotationStart({
      angle: initialAngle - (element.rotation || 0),
      centerX: centerX,
      centerY: centerY
    });

    // Ajouter les écouteurs d'événements globaux
    var handleRotationMove = function handleRotationMove(moveEvent) {
      if (!isRotating) return;
      var deltaX = moveEvent.clientX - centerX;
      var deltaY = moveEvent.clientY - centerY;
      var newAngle = Math.atan2(deltaY, deltaX) * (180 / Math.PI);

      // Calculer la rotation relative
      newAngle = newAngle - rotationStart.angle;

      // Appliquer l'aimantation si activée
      if (rotationSnap) {
        newAngle = Math.round(newAngle / rotationStep) * rotationStep;
      }

      // Normaliser l'angle entre 0 et 360
      newAngle = (newAngle % 360 + 360) % 360;
      onElementRotate(newAngle);
    };
    var _handleRotationEnd = function handleRotationEnd() {
      setIsRotating(false);
      document.removeEventListener('mousemove', handleRotationMove);
      document.removeEventListener('mouseup', _handleRotationEnd);
    };
    document.addEventListener('mousemove', handleRotationMove);
    document.addEventListener('mouseup', _handleRotationEnd);
  }, [isRotating, rotationStart, rotationStep, rotationSnap, onElementRotate]);
  return {
    isRotating: isRotating,
    handleRotationStart: handleRotationStart
  };
};
;// ./src/components/CanvasElement.jsx
function CanvasElement_typeof(o) { "@babel/helpers - typeof"; return CanvasElement_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, CanvasElement_typeof(o); }
function CanvasElement_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function CanvasElement_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? CanvasElement_ownKeys(Object(t), !0).forEach(function (r) { CanvasElement_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : CanvasElement_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function CanvasElement_defineProperty(e, r, t) { return (r = CanvasElement_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function CanvasElement_toPropertyKey(t) { var i = CanvasElement_toPrimitive(t, "string"); return "symbol" == CanvasElement_typeof(i) ? i : i + ""; }
function CanvasElement_toPrimitive(t, r) { if ("object" != CanvasElement_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != CanvasElement_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }



var CanvasElement = function CanvasElement(_ref) {
  var _element$fields, _element$fields2, _element$fields3, _element$fields4, _element$fields5, _element$fields6, _element$fields7, _element$fields8, _element$fields9, _element$fields0, _element$fields1, _element$fields10, _element$fields11, _element$fields12, _element$fields13;
  var element = _ref.element,
    isSelected = _ref.isSelected,
    zoom = _ref.zoom,
    snapToGrid = _ref.snapToGrid,
    gridSize = _ref.gridSize,
    canvasWidth = _ref.canvasWidth,
    canvasHeight = _ref.canvasHeight,
    onSelect = _ref.onSelect,
    onUpdate = _ref.onUpdate,
    onRemove = _ref.onRemove,
    onContextMenu = _ref.onContextMenu,
    dragAndDrop = _ref.dragAndDrop,
    _ref$enableRotation = _ref.enableRotation,
    enableRotation = _ref$enableRotation === void 0 ? true : _ref$enableRotation,
    _ref$rotationStep = _ref.rotationStep,
    rotationStep = _ref$rotationStep === void 0 ? 15 : _ref$rotationStep,
    _ref$rotationSnap = _ref.rotationSnap,
    rotationSnap = _ref$rotationSnap === void 0 ? true : _ref$rotationSnap,
    _ref$guides = _ref.guides,
    guides = _ref$guides === void 0 ? {
      horizontal: [],
      vertical: []
    } : _ref$guides,
    _ref$snapToGuides = _ref.snapToGuides,
    snapToGuides = _ref$snapToGuides === void 0 ? true : _ref$snapToGuides;
  var elementRef = (0,react.useRef)(null);
  var canvasRectRef = (0,react.useRef)(null);

  // DEBUG: Logger les positions des éléments dans l'éditeur
  (0,react.useEffect)(function () {
    if (element && element.id) {
      var displayX = element.x * zoom;
      var displayY = element.y * zoom;
      var displayWidth = element.width * zoom;
      var displayHeight = element.height * zoom;

      // Log supprimé pour la production
    }
  }, [element.x, element.y, element.width, element.height, zoom, element.id, element.type]);
  var resize = useResize({
    onElementResize: function onElementResize(newRect) {
      onUpdate({
        x: newRect.x,
        y: newRect.y,
        width: newRect.width,
        height: newRect.height
      });
    },
    snapToGrid: snapToGrid,
    gridSize: gridSize,
    canvasWidth: canvasWidth,
    canvasHeight: canvasHeight,
    guides: guides,
    snapToGuides: snapToGuides,
    elementType: element.type
  });
  var rotation = useRotation(function (newRotation) {
    onUpdate({
      rotation: newRotation
    });
  }, rotationStep, rotationSnap);

  // Fonction helper pour déterminer si un élément est spécial
  var isSpecialElement = function isSpecialElement(type) {
    return ['product_table', 'customer_info', 'company_logo', 'company_info', 'order_number', 'document_type', 'progress-bar'].includes(type);
  };

  // Fonction helper pour gérer les styles de bordure des éléments spéciaux
  var getSpecialElementBorderStyle = function getSpecialElementBorderStyle(element) {
    // Pour les éléments spéciaux, forcer toujours un fond transparent
    // indépendamment des propriétés de l'élément
    return CanvasElement_objectSpread({
      backgroundColor: 'transparent',
      // Utiliser box-sizing pour que les bordures soient incluses dans les dimensions
      boxSizing: 'border-box'
    }, element.borderWidth && element.borderWidth > 0 ? {
      border: "".concat(element.borderWidth * zoom, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || '#e5e7eb')
    } : {});
  };

  // Fonction helper pour obtenir les styles de tableau selon le style choisi
  var getTableStyles = function getTableStyles() {
    var tableStyle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';
    var baseStyles = {
      "default": {
        headerBg: '#f8fafc',
        headerBorder: '#e2e8f0',
        rowBorder: '#000000',
        altRowBg: '#fafbfc',
        borderWidth: 2,
        headerTextColor: '#334155',
        rowTextColor: '#334155',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 1px 3px rgba(0, 0, 0, 0.1)',
        borderRadius: '4px'
      },
      classic: {
        headerBg: '#1e293b',
        headerBorder: '#334155',
        rowBorder: '#334155',
        altRowBg: '#ffffff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#1e293b',
        headerFontWeight: '700',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
        borderRadius: '0px'
      },
      striped: {
        headerBg: '#3b82f6',
        headerBorder: '#2563eb',
        rowBorder: '#e2e8f0',
        altRowBg: '#f8fafc',
        borderWidth: 1,
        headerTextColor: '#ffffff',
        rowTextColor: '#334155',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 1px 4px rgba(59, 130, 246, 0.2)',
        borderRadius: '6px'
      },
      bordered: {
        headerBg: '#ffffff',
        headerBorder: '#374151',
        rowBorder: '#d1d5db',
        altRowBg: '#ffffff',
        borderWidth: 2,
        headerTextColor: '#111827',
        rowTextColor: '#111827',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 12px rgba(0, 0, 0, 0.1), inset 0 0 0 1px #e5e7eb',
        borderRadius: '8px'
      },
      minimal: {
        headerBg: '#ffffff',
        headerBorder: '#d1d5db',
        rowBorder: '#f3f4f6',
        altRowBg: '#ffffff',
        borderWidth: 0.5,
        headerTextColor: '#6b7280',
        rowTextColor: '#6b7280',
        headerFontWeight: '500',
        headerFontSize: '10px',
        rowFontSize: '9px',
        shadow: 'none',
        borderRadius: '0px'
      },
      modern: {
        headerBg: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        headerBorder: '#5b21b6',
        rowBorder: '#e9d5ff',
        altRowBg: '#faf5ff',
        borderWidth: 1,
        headerTextColor: '#ffffff',
        rowTextColor: '#6b21a8',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 20px rgba(102, 126, 234, 0.25)',
        borderRadius: '8px'
      },
      // Nouveaux styles colorés
      blue_ocean: {
        headerBg: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
        headerBorder: '#1e40af',
        rowBorder: '#dbeafe',
        altRowBg: '#eff6ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#1e3a8a',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(59, 130, 246, 0.3)',
        borderRadius: '6px'
      },
      emerald_forest: {
        headerBg: 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
        headerBorder: '#065f46',
        rowBorder: '#d1fae5',
        altRowBg: '#ecfdf5',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#064e3b',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(16, 185, 129, 0.3)',
        borderRadius: '6px'
      },
      sunset_orange: {
        headerBg: 'linear-gradient(135deg, #9a3412 0%, #f97316 100%)',
        headerBorder: '#c2410c',
        rowBorder: '#fed7aa',
        altRowBg: '#fff7ed',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#9a3412',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(249, 115, 22, 0.3)',
        borderRadius: '6px'
      },
      royal_purple: {
        headerBg: 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)',
        headerBorder: '#7c3aed',
        rowBorder: '#e9d5ff',
        altRowBg: '#faf5ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#581c87',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(168, 85, 247, 0.3)',
        borderRadius: '6px'
      },
      rose_pink: {
        headerBg: 'linear-gradient(135deg, #be185d 0%, #f472b6 100%)',
        headerBorder: '#db2777',
        rowBorder: '#fce7f3',
        altRowBg: '#fdf2f8',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#be185d',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(244, 114, 182, 0.3)',
        borderRadius: '6px'
      },
      teal_aqua: {
        headerBg: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
        headerBorder: '#0d9488',
        rowBorder: '#ccfbf1',
        altRowBg: '#f0fdfa',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#0f766e',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(20, 184, 166, 0.3)',
        borderRadius: '6px'
      },
      crimson_red: {
        headerBg: 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)',
        headerBorder: '#dc2626',
        rowBorder: '#fecaca',
        altRowBg: '#fef2f2',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#991b1b',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(239, 68, 68, 0.3)',
        borderRadius: '6px'
      },
      amber_gold: {
        headerBg: 'linear-gradient(135deg, #92400e 0%, #f59e0b 100%)',
        headerBorder: '#d97706',
        rowBorder: '#fef3c7',
        altRowBg: '#fffbeb',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#92400e',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(245, 158, 11, 0.3)',
        borderRadius: '6px'
      },
      indigo_night: {
        headerBg: 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)',
        headerBorder: '#4338ca',
        rowBorder: '#e0e7ff',
        altRowBg: '#eef2ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#312e81',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(99, 102, 241, 0.3)',
        borderRadius: '6px'
      },
      slate_gray: {
        headerBg: 'linear-gradient(135deg, #374151 0%, #6b7280 100%)',
        headerBorder: '#4b5563',
        rowBorder: '#f3f4f6',
        altRowBg: '#f9fafb',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#374151',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(107, 114, 128, 0.3)',
        borderRadius: '6px'
      },
      coral_sunset: {
        headerBg: 'linear-gradient(135deg, #c2410c 0%, #fb7185 100%)',
        headerBorder: '#ea580c',
        rowBorder: '#fed7d7',
        altRowBg: '#fef7f7',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#c2410c',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(251, 113, 133, 0.3)',
        borderRadius: '6px'
      },
      mint_green: {
        headerBg: 'linear-gradient(135deg, #065f46 0%, #34d399 100%)',
        headerBorder: '#047857',
        rowBorder: '#d1fae5',
        altRowBg: '#ecfdf5',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#065f46',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(52, 211, 153, 0.3)',
        borderRadius: '6px'
      },
      violet_dream: {
        headerBg: 'linear-gradient(135deg, #6d28d9 0%, #c084fc 100%)',
        headerBorder: '#8b5cf6',
        rowBorder: '#ede9fe',
        altRowBg: '#f5f3ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#6d28d9',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(192, 132, 252, 0.3)',
        borderRadius: '6px'
      },
      sky_blue: {
        headerBg: 'linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%)',
        headerBorder: '#0284c7',
        rowBorder: '#bae6fd',
        altRowBg: '#f0f9ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#0369a1',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(14, 165, 233, 0.3)',
        borderRadius: '6px'
      },
      forest_green: {
        headerBg: 'linear-gradient(135deg, #14532d 0%, #22c55e 100%)',
        headerBorder: '#15803d',
        rowBorder: '#bbf7d0',
        altRowBg: '#f0fdf4',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#14532d',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(34, 197, 94, 0.3)',
        borderRadius: '6px'
      },
      ruby_red: {
        headerBg: 'linear-gradient(135deg, #b91c1b 0%, #f87171 100%)',
        headerBorder: '#dc2626',
        rowBorder: '#fecaca',
        altRowBg: '#fef2f2',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#b91c1b',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(248, 113, 113, 0.3)',
        borderRadius: '6px'
      },
      golden_yellow: {
        headerBg: 'linear-gradient(135deg, #a16207 0%, #eab308 100%)',
        headerBorder: '#ca8a04',
        rowBorder: '#fef08a',
        altRowBg: '#fefce8',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#a16207',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(234, 179, 8, 0.3)',
        borderRadius: '6px'
      },
      navy_blue: {
        headerBg: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
        headerBorder: '#1e40af',
        rowBorder: '#dbeafe',
        altRowBg: '#eff6ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#1e3a8a',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(59, 130, 246, 0.3)',
        borderRadius: '6px'
      },
      burgundy_wine: {
        headerBg: 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
        headerBorder: '#991b1b',
        rowBorder: '#fecaca',
        altRowBg: '#fef2f2',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#7f1d1d',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(220, 38, 38, 0.3)',
        borderRadius: '6px'
      },
      lavender_purple: {
        headerBg: 'linear-gradient(135deg, #7c2d12 0%, #a855f7 100%)',
        headerBorder: '#9333ea',
        rowBorder: '#e9d5ff',
        altRowBg: '#faf5ff',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#7c2d12',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(168, 85, 247, 0.3)',
        borderRadius: '6px'
      },
      ocean_teal: {
        headerBg: 'linear-gradient(135deg, #134e4a 0%, #14b8a6 100%)',
        headerBorder: '#0f766e',
        rowBorder: '#ccfbf1',
        altRowBg: '#f0fdfa',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#134e4a',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(20, 184, 166, 0.3)',
        borderRadius: '6px'
      },
      cherry_blossom: {
        headerBg: 'linear-gradient(135deg, #be185d 0%, #fb7185 100%)',
        headerBorder: '#db2777',
        rowBorder: '#fce7f3',
        altRowBg: '#fdf2f8',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#be185d',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(251, 113, 133, 0.3)',
        borderRadius: '6px'
      },
      autumn_orange: {
        headerBg: 'linear-gradient(135deg, #9a3412 0%, #fb923c 100%)',
        headerBorder: '#ea580c',
        rowBorder: '#fed7aa',
        altRowBg: '#fff7ed',
        borderWidth: 1.5,
        headerTextColor: '#ffffff',
        rowTextColor: '#9a3412',
        headerFontWeight: '600',
        headerFontSize: '11px',
        rowFontSize: '10px',
        shadow: '0 4px 16px rgba(251, 146, 60, 0.3)',
        borderRadius: '6px'
      }
    };
    return baseStyles[tableStyle] || baseStyles["default"];
  };

  // Gestionnaire de clic sur l'élément
  var handleMouseDown = (0,react.useCallback)(function (e) {
    e.stopPropagation();
    if (!isSelected) {
      onSelect();
      return;
    }

    // Calculer les coordonnées relatives au canvas (en tenant compte du zoom)
    var canvas = elementRef.current.closest('.canvas-zoom-wrapper');
    if (!canvas) return;
    var canvasRect = canvas.getBoundingClientRect();
    var elementRect = elementRef.current.getBoundingClientRect();

    // Ajuster pour le zoom - les coordonnées doivent être relatives au canvas non-zoomé
    var relativeRect = {
      left: (elementRect.left - canvasRect.left) / zoom,
      top: (elementRect.top - canvasRect.top) / zoom,
      width: elementRect.width / zoom,
      height: elementRect.height / zoom
    };

    // Vérifier si on clique sur une poignée de redimensionnement
    var clickX = (e.clientX - canvasRect.left) / zoom;
    var clickY = (e.clientY - canvasRect.top) / zoom;
    var handleSize = 8 / zoom; // Ajuster la taille des poignées pour le zoom
    var elementLeft = element.x;
    var elementTop = element.y;
    var elementRight = element.x + element.width;
    var elementBottom = element.y + element.height;

    // Poignées de redimensionnement (coordonnées relatives au canvas)
    var handles = [{
      name: 'nw',
      x: elementLeft,
      y: elementTop
    }, {
      name: 'ne',
      x: elementRight,
      y: elementTop
    }, {
      name: 'sw',
      x: elementLeft,
      y: elementBottom
    }, {
      name: 'se',
      x: elementRight,
      y: elementBottom
    }, {
      name: 'n',
      x: elementLeft + element.width / 2,
      y: elementTop
    }, {
      name: 's',
      x: elementLeft + element.width / 2,
      y: elementBottom
    }, {
      name: 'w',
      x: elementLeft,
      y: elementTop + element.height / 2
    }, {
      name: 'e',
      x: elementRight,
      y: elementTop + element.height / 2
    }];
    var clickedHandle = handles.find(function (handle) {
      return clickX >= handle.x - handleSize / 2 && clickX <= handle.x + handleSize / 2 && clickY >= handle.y - handleSize / 2 && clickY <= handle.y + handleSize / 2;
    });
    if (clickedHandle) {
      var _canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      var _canvasRect = _canvas.getBoundingClientRect();
      resize.handleResizeStart(e, clickedHandle.name, {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, _canvasRect, zoom);
    } else {
      // Démarrer le drag avec les coordonnées relatives au canvas
      var _canvas2 = elementRef.current.closest('.canvas-zoom-wrapper');
      var _canvasRect2 = _canvas2.getBoundingClientRect();

      // Mettre à jour la référence du canvas
      canvasRectRef.current = _canvasRect2;
      dragAndDrop.handleMouseDown(e, element.id, {
        left: element.x,
        top: element.y,
        width: element.width,
        height: element.height
      }, _canvasRect2, zoom, element.type);
    }
  }, [isSelected, onSelect, element, zoom, resize, dragAndDrop]);

  // Gestionnaire de double-clic pour édition
  var handleDoubleClick = (0,react.useCallback)(function (e) {
    e.stopPropagation();
    if (element.type === 'text') {
      var currentText = element.content || element.text || '';
      var newText = prompt('Modifier le texte:', currentText);

      // Annuler si l'utilisateur clique sur "Annuler" ou laisse vide
      if (newText === null) {
        return;
      }

      // Utiliser la même propriété que celle actuellement utilisée par l'élément
      var textProperty = element.content !== undefined ? 'content' : 'text';
      var updates = CanvasElement_defineProperty({}, textProperty, newText);
      onUpdate(updates);
    }
  }, [element, onUpdate]);

  // Gestionnaire de clic droit
  var handleContextMenuEvent = (0,react.useCallback)(function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (onContextMenu) {
      onContextMenu(e, element.id);
    }
  }, [onContextMenu, element.id]);

  // Fonction helper pour obtenir les styles spécifiques au type d'élément
  var getElementTypeStyles = function getElementTypeStyles(element, zoom) {
    switch (element.type) {
      case 'text':
        return {
          fontSize: (element.fontSize || 14) * zoom,
          fontFamily: element.fontFamily || 'Arial',
          color: element.color || '#1e293b',
          fontWeight: element.fontWeight || 'normal',
          fontStyle: element.fontStyle || 'normal',
          textAlign: element.textAlign || 'left',
          textDecoration: element.textDecoration || 'none',
          lineHeight: element.lineHeight || 'normal',
          display: 'flex',
          alignItems: 'center',
          justifyContent: element.textAlign === 'center' ? 'center' : element.textAlign === 'right' ? 'flex-end' : 'flex-start',
          wordBreak: 'break-word',
          overflow: 'hidden'
        };
      case 'rectangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius, "px") : '0'
        };
      case 'image':
        if (element.src || element.imageUrl) {
          return {
            backgroundImage: "url(".concat(element.src || element.imageUrl, ")"),
            backgroundSize: element.objectFit || element.fit || 'cover',
            backgroundPosition: 'center',
            backgroundRepeat: 'no-repeat'
          };
        }
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          color: '#9ca3af',
          fontSize: 12 * zoom
        };
      case 'line':
        return {
          borderTop: "".concat(element.lineWidth || 1, "px solid ").concat(element.lineColor || '#6b7280'),
          height: "".concat(Math.max(element.lineWidth || 1, 12), "px"),
          // Hauteur augmentée à 12px minimum pour faciliter le clic
          width: '100%',
          cursor: 'pointer',
          backgroundColor: 'transparent' // S'assurer qu'il n'y a pas de fond qui cache
        };
      case 'layout-header':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 14 * zoom,
          fontWeight: 'bold',
          color: element.color || '#64748b'
        };
      case 'layout-footer':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };
      case 'layout-sidebar':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };
      case 'layout-section':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#64748b'
        };
      case 'layout-container':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#94a3b8'
        };
      case 'shape-rectangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '0'
        };
      case 'shape-circle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          borderRadius: '50%'
        };
      case 'shape-line':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          height: '100%'
        };
      case 'shape-arrow':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(0% 50%, 70% 0%, 70% 40%, 100% 40%, 100% 60%, 70% 60%, 70% 100%)'
        };
      case 'shape-triangle':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)'
        };
      case 'shape-star':
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          clipPath: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)'
        };
      case 'divider':
        return {
          backgroundColor: element.backgroundColor || '#e5e7eb',
          height: '16px',
          // Hauteur fixe pour les séparateurs
          cursor: 'pointer',
          borderRadius: '2px'
        };
      case 'line':
        return {
          backgroundColor: element.backgroundColor || '#64748b',
          height: '12px',
          // Hauteur fixe pour les lignes
          cursor: 'pointer'
        };

      // Styles par défaut pour les autres types
      default:
        return {
          backgroundColor: element.backgroundColor || 'transparent',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: 12 * zoom,
          color: element.color || '#333333'
        };
    }
  };

  // Calcul du padding pour cohérence avec le PDF
  var elementPadding = element.padding || 0;

  // Styles élément optimisés avec useMemo pour éviter les recalculs inutiles
  var elementStyles = (0,react.useMemo)(function () {
    return CanvasElement_objectSpread(CanvasElement_objectSpread({
      position: 'absolute',
      left: (element.x + elementPadding) * zoom,
      top: (element.y + elementPadding) * zoom,
      width: Math.max(1, element.width - elementPadding * 2) * zoom,
      height: Math.max(1, element.height - elementPadding * 2) * zoom,
      cursor: dragAndDrop.isDragging ? 'grabbing' : 'grab',
      userSelect: 'none',
      '--selection-border-width': '2px',
      '--selection-border-color': '#3b82f6',
      '--selection-border-spacing': '2px',
      '--selection-shadow-opacity': '0.1',
      '--show-resize-handles': isSelected ? 'block' : 'none',
      '--resize-handle-size': "".concat(10 * zoom, "px"),
      '--resize-handle-color': '#3b82f6',
      '--resize-handle-border-color': 'white',
      '--resize-handle-offset': "".concat(-5 * zoom, "px"),
      '--element-border-width': '2px',
      '--resize-zone-size': '16px',
      '--show-resize-zones': isSelected ? 'auto' : 'none'
    }, isSpecialElement(element.type) ? getSpecialElementBorderStyle(element) : {
      // Styles de base communs à tous les éléments non-spéciaux
      backgroundColor: element.backgroundOpacity && element.backgroundColor && element.backgroundColor !== 'transparent' ? element.backgroundColor + Math.round(element.backgroundOpacity * 255).toString(16).padStart(2, '0') : element.backgroundColor || 'transparent',
      border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || 'transparent') : 'none'
    }), {}, {
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '0px',
      opacity: (element.opacity || 100) / 100,
      transform: "".concat(dragAndDrop.draggedElementId === element.id ? "translate(".concat(dragAndDrop.dragOffset.x * zoom, "px, ").concat(dragAndDrop.dragOffset.y * zoom, "px) ") : '', "rotate(").concat(element.rotation || 0, "deg) scale(").concat(element.scale || 100, "%)"),
      filter: "brightness(".concat(element.brightness || 100, "%) contrast(").concat(element.contrast || 100, "%) saturate(").concat(element.saturate || 100, "%)"),
      boxShadow: element.boxShadowColor ? "0px ".concat(element.boxShadowSpread || 0, "px ").concat(element.boxShadowBlur || 0, "px ").concat(element.boxShadowColor) : element.shadow ? "".concat(element.shadowOffsetX || 2, "px ").concat(element.shadowOffsetY || 2, "px 4px ").concat(element.shadowColor || '#000000', "40") : 'none'
    }, getElementTypeStyles(element, zoom));
  }, [element.x, element.y, element.width, element.height, element.rotation, element.scale, element.backgroundColor, element.backgroundOpacity, element.borderWidth, element.borderStyle, element.borderColor, element.borderRadius, element.opacity, element.brightness, element.contrast, element.saturate, element.boxShadowColor, element.boxShadowSpread, element.boxShadowBlur, element.shadow, element.shadowOffsetX, element.shadowOffsetY, element.shadowColor, element.type, elementPadding, zoom, isSelected, dragAndDrop.isDragging, dragAndDrop.draggedElementId, dragAndDrop.dragOffset]);
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    ref: elementRef,
    "data-element-id": element.id,
    className: "canvas-element ".concat(isSelected ? 'selected' : ''),
    style: elementStyles,
    onMouseDown: handleMouseDown,
    onDoubleClick: handleDoubleClick,
    onContextMenu: handleContextMenuEvent,
    draggable: false
  }, element.type === 'text' ? element.content || element.text || 'Texte' : element.type === 'product_table' ? null :
  // Le contenu sera rendu plus bas pour les tableaux
  element.type === 'image' && !element.src ? '📷 Image' : element.type === 'line' ? null : element.type === 'layout-header' ? '[H] En-tête' : element.type === 'layout-footer' ? '📄 Pied de Page' : element.type === 'layout-sidebar' ? '📄 Barre Latérale' : element.type === 'layout-section' ? '📄 Section' : element.type === 'layout-container' ? '📦 Conteneur' : element.type === 'shape-rectangle' ? '▭' : element.type === 'shape-circle' ? '○' : element.type === 'shape-line' ? null : element.type === 'shape-arrow' ? '→' : element.type === 'shape-triangle' ? '△' : element.type === 'shape-star' ? '⭐' : element.type === 'divider' ? null : element.type === 'image-upload' ? '📤 Télécharger' : element.type === 'logo' ? '🏷️ Logo' : element.type === 'barcode' ? '📊 123456' : element.type === 'qrcode' || element.type === 'qrcode-dynamic' ? '📱 QR' : element.type === 'icon' ? element.content || '🎯' : element.type === 'dynamic-text' ? element.content || '{{variable}}' : element.type === 'formula' ? element.content || '{{prix * quantite}}' : element.type === 'conditional-text' ? element.content || '{{condition ? "Oui" : "Non"}}' : element.type === 'counter' ? element.content || '1' : element.type === 'date-dynamic' ? element.content || '{{date|format:Y-m-d}}' : element.type === 'currency' ? element.content || '{{montant|currency:EUR}}' : element.type === 'table-dynamic' ? '📊 Tableau' : element.type === 'gradient-box' ? '🌈 Dégradé' : element.type === 'shadow-box' ? '📦 Ombre' : element.type === 'rounded-box' ? '🔄 Arrondi' : element.type === 'border-box' ? '🔲 Bordure' : element.type === 'background-pattern' ? '🎨 Motif' : element.type === 'watermark' ? element.content || 'CONFIDENTIEL' : element.type === 'progress-bar' ? null : element.type === 'product_table' ? null :
  // Le contenu sera rendu plus bas dans le même conteneur
  element.type === 'customer_info' ? null :
  // Le contenu sera rendu plus bas dans le même conteneur
  element.type !== 'image' && element.type !== 'rectangle' && element.type !== 'company_logo' && element.type !== 'order_number' && element.type !== 'company_info' && element.type !== 'document_type' ? element.type : null, element.type === 'product_table' && function (_element$columns2, _element$columns3, _element$columns4, _element$columns5, _element$columns6, _element$columns7, _element$columns8, _element$columns9, _element$columns0, _element$columns1, _element$columns10, _element$columns11, _element$columns12, _element$columns13, _element$columns14, _element$columns15, _element$columns16, _element$columns17, _element$columns18, _element$columns19, _element$columns20, _element$columns21, _element$columns22, _element$columns23, _element$columns24, _element$columns25, _element$columns26, _element$columns27, _element$columns28, _element$columns29, _element$columns30) {
    // Données des produits (pourrait venir de props ou d'un état global)
    var products = [{
      name: 'Produit A - Description du produit',
      sku: 'SKU001',
      quantity: 2,
      price: 19.99,
      total: 39.98
    }, {
      name: 'Produit B - Un autre article',
      sku: 'SKU002',
      quantity: 1,
      price: 29.99,
      total: 29.99
    }];

    // Calcul des totaux dynamiques
    var subtotal = products.reduce(function (sum, product) {
      return sum + product.total;
    }, 0);
    var shipping = element.showShipping ? 5.00 : 0;
    var tax = element.showTaxes ? 2.25 : 0;
    var discount = element.showDiscount ? -5.00 : 0;
    var total = subtotal + shipping + tax + discount;

    // Déterminer la dernière colonne visible pour afficher les totaux
    var getLastVisibleColumn = function getLastVisibleColumn() {
      var columns = ['image', 'name', 'sku', 'quantity', 'price', 'total'];
      for (var i = columns.length - 1; i >= 0; i--) {
        var _element$columns;
        if (((_element$columns = element.columns) === null || _element$columns === void 0 ? void 0 : _element$columns[columns[i]]) !== false) {
          return columns[i];
        }
      }
      return 'total'; // fallback
    };
    var lastVisibleColumn = getLastVisibleColumn();
    var tableStyles = getTableStyles(element.tableStyle);
    // Forcer les bordures pour les tableaux de produits (correction du bug d'affichage)
    var showBorders = element.showBorders !== false; // Utiliser la propriété showBorders de l'élément
    return /*#__PURE__*/React.createElement("div", {
      style: {
        width: '100%',
        height: '100%',
        display: 'flex',
        flexDirection: 'column',
        fontSize: 10 * zoom,
        fontFamily: '"Inter", "Segoe UI", Roboto, -apple-system, BlinkMacSystemFont, sans-serif',
        // Utiliser les bordures du style de tableau si showBorders est activé
        border: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
        borderRadius: tableStyles.borderRadius ? "".concat(tableStyles.borderRadius * zoom, "px") : '2px',
        overflow: 'hidden',
        // Assurer que le background ne cache pas les bordures
        backgroundColor: element.backgroundColor || 'transparent',
        boxSizing: 'border-box',
        boxShadow: tableStyles.shadow,
        // Améliorer la lisibilité globale
        lineHeight: '1.4',
        color: tableStyles.rowTextColor
      }
    }, element.showHeaders !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        background: tableStyles.headerBg,
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontWeight: tableStyles.headerFontWeight,
        color: tableStyles.headerTextColor,
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px"),
        textTransform: 'uppercase',
        letterSpacing: '0.025em'
      }
    }, ((_element$columns2 = element.columns) === null || _element$columns2 === void 0 ? void 0 : _element$columns2.image) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-image",
      style: {
        flex: '0 0 40px',
        padding: "".concat(6 * zoom, "px ").concat(4 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom * 0.9, "px"),
        opacity: 0.9
      }
    }, "Img"), ((_element$columns3 = element.columns) === null || _element$columns3 === void 0 ? void 0 : _element$columns3.name) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-name",
      style: {
        flex: 1,
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'left',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "Produit"), ((_element$columns4 = element.columns) === null || _element$columns4 === void 0 ? void 0 : _element$columns4.sku) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-sku",
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'left',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "SKU"), ((_element$columns5 = element.columns) === null || _element$columns5 === void 0 ? void 0 : _element$columns5.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-quantity",
      style: {
        flex: '0 0 60px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "Qt\xE9"), ((_element$columns6 = element.columns) === null || _element$columns6 === void 0 ? void 0 : _element$columns6.price) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-price",
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "Prix"), ((_element$columns7 = element.columns) === null || _element$columns7 === void 0 ? void 0 : _element$columns7.total) !== false && /*#__PURE__*/React.createElement("div", {
      key: "header-total",
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, "Total")), /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        display: 'flex',
        flexDirection: 'column'
      }
    }, /*#__PURE__*/React.createElement("div", {
      key: "row-1",
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: tableStyles.altRowBg,
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px"),
        transition: 'background-color 0.15s ease'
      }
    }, ((_element$columns8 = element.columns) === null || _element$columns8 === void 0 ? void 0 : _element$columns8.image) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-image",
      style: {
        flex: '0 0 40px',
        padding: "".concat(5 * zoom, "px ").concat(4 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        opacity: 0.7,
        fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
      }
    }, "\uD83D\uDCF7"), ((_element$columns9 = element.columns) === null || _element$columns9 === void 0 ? void 0 : _element$columns9.name) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-name",
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '500',
        lineHeight: '1.3'
      }
    }, "Produit A - Description du produit"), ((_element$columns0 = element.columns) === null || _element$columns0 === void 0 ? void 0 : _element$columns0.sku) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-sku",
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        opacity: 0.8,
        fontFamily: 'monospace',
        fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
      }
    }, "SKU001"), ((_element$columns1 = element.columns) === null || _element$columns1 === void 0 ? void 0 : _element$columns1.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '600'
      }
    }, "2"), ((_element$columns10 = element.columns) === null || _element$columns10 === void 0 ? void 0 : _element$columns10.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '500',
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC19.99"), ((_element$columns11 = element.columns) === null || _element$columns11 === void 0 ? void 0 : _element$columns11.total) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '600',
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC39.98")), /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: 'transparent',
        // Ligne normale sans alternance
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px"),
        transition: 'background-color 0.15s ease'
      }
    }, ((_element$columns12 = element.columns) === null || _element$columns12 === void 0 ? void 0 : _element$columns12.image) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 40px',
        padding: "".concat(5 * zoom, "px ").concat(4 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        opacity: 0.7,
        fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
      }
    }, "\uD83D\uDCF7"), ((_element$columns13 = element.columns) === null || _element$columns13 === void 0 ? void 0 : _element$columns13.name) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '500',
        lineHeight: '1.3'
      }
    }, "Produit B - Un autre article"), ((_element$columns14 = element.columns) === null || _element$columns14 === void 0 ? void 0 : _element$columns14.sku) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        opacity: 0.8,
        fontFamily: 'monospace',
        fontSize: "".concat(tableStyles.rowFontSize * zoom * 0.9, "px")
      }
    }, "SKU002"), ((_element$columns15 = element.columns) === null || _element$columns15 === void 0 ? void 0 : _element$columns15.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-quantity",
      style: {
        flex: '0 0 60px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'center',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '600'
      }
    }, "1"), ((_element$columns16 = element.columns) === null || _element$columns16 === void 0 ? void 0 : _element$columns16.price) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-price",
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        color: tableStyles.rowTextColor,
        fontWeight: '500',
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC29.99"), ((_element$columns17 = element.columns) === null || _element$columns17 === void 0 ? void 0 : _element$columns17.total) !== false && /*#__PURE__*/React.createElement("div", {
      key: "row-1-total",
      style: {
        flex: '0 0 80px',
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '600',
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC29.99"))), (element.showSubtotal || element.showShipping || element.showTaxes || element.showDiscount || element.showTotal) && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        display: 'flex',
        flexDirection: 'column'
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderTop: showBorders ? "".concat(tableStyles.borderWidth * 2 * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        marginTop: "".concat(10 * zoom, "px"),
        paddingTop: "".concat(10 * zoom, "px"),
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        fontWeight: 'bold',
        color: element.color || '#666'
      }
    }), ((_element$columns18 = element.columns) === null || _element$columns18 === void 0 ? void 0 : _element$columns18.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns19 = element.columns) === null || _element$columns19 === void 0 ? void 0 : _element$columns19.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        fontWeight: 'bold',
        color: element.color || '#666'
      }
    }), ((_element$columns20 = element.columns) === null || _element$columns20 === void 0 ? void 0 : _element$columns20.total) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        fontWeight: 'bold',
        color: element.color || '#666'
      }
    }, "Total")), element.showSubtotal && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: tableStyles.altRowBg,
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '600'
      }
    }, "Sous-total"), ((_element$columns21 = element.columns) === null || _element$columns21 === void 0 ? void 0 : _element$columns21.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns22 = element.columns) === null || _element$columns22 === void 0 ? void 0 : _element$columns22.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        fontWeight: 'bold'
      }
    }, "\u20AC", subtotal.toFixed(2))), element.showShipping && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: 'transparent',
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '500'
      }
    }, "Frais de port"), ((_element$columns23 = element.columns) === null || _element$columns23 === void 0 ? void 0 : _element$columns23.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns24 = element.columns) === null || _element$columns24 === void 0 ? void 0 : _element$columns24.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }, "\u20AC", shipping.toFixed(2))), element.showTaxes && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: 'transparent',
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: tableStyles.rowTextColor,
        fontWeight: '500'
      }
    }, "Taxes (TVA 20%)"), ((_element$columns25 = element.columns) === null || _element$columns25 === void 0 ? void 0 : _element$columns25.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns26 = element.columns) === null || _element$columns26 === void 0 ? void 0 : _element$columns26.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }, "\u20AC", tax.toFixed(2))), element.showDiscount && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none',
        backgroundColor: 'transparent',
        fontSize: "".concat(tableStyles.rowFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(5 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        color: '#dc3545',
        fontWeight: '500'
      }
    }, "Remise"), ((_element$columns27 = element.columns) === null || _element$columns27 === void 0 ? void 0 : _element$columns27.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns28 = element.columns) === null || _element$columns28 === void 0 ? void 0 : _element$columns28.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
        textAlign: 'right',
        color: '#d32f2f'
      }
    }, "\u20AC", discount.toFixed(2))), element.showTotal && /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        borderTop: showBorders ? "".concat(tableStyles.borderWidth * 2 * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
        backgroundColor: tableStyles.headerBg,
        marginTop: "".concat(6 * zoom, "px"),
        paddingTop: "".concat(6 * zoom, "px"),
        color: tableStyles.headerTextColor,
        fontWeight: tableStyles.headerFontWeight,
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px")
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        flex: 1,
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        fontSize: "".concat(tableStyles.headerFontSize * zoom, "px"),
        textTransform: 'uppercase',
        letterSpacing: '0.05em'
      }
    }, "TOTAL TTC"), ((_element$columns29 = element.columns) === null || _element$columns29 === void 0 ? void 0 : _element$columns29.quantity) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 60px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'center'
      }
    }), ((_element$columns30 = element.columns) === null || _element$columns30 === void 0 ? void 0 : _element$columns30.price) !== false && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right'
      }
    }), lastVisibleColumn === 'total' && /*#__PURE__*/React.createElement("div", {
      style: {
        flex: '0 0 80px',
        padding: "".concat(6 * zoom, "px ").concat(8 * zoom, "px"),
        textAlign: 'right',
        fontSize: "".concat(tableStyles.headerFontSize * zoom * 1.2, "px"),
        fontWeight: '700',
        color: tableStyles.headerTextColor,
        fontFamily: '"Inter", system-ui, sans-serif'
      }
    }, "\u20AC", total.toFixed(2)))));
  }(), element.type === 'customer_info' && /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      padding: "".concat(8 * zoom, "px"),
      fontSize: "".concat((element.fontSize || 12) * zoom, "px"),
      fontFamily: element.fontFamily || 'Arial, sans-serif',
      fontWeight: element.fontWeight || 'normal',
      fontStyle: element.fontStyle || 'normal',
      textDecoration: element.textDecoration || 'none',
      color: element.color || '#333',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
      boxSizing: 'border-box'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'row' : 'column',
      gap: "".concat(element.spacing * zoom || 8 * zoom, "px"),
      height: '100%'
    }
  }, ((_element$fields = element.fields) === null || _element$fields === void 0 ? void 0 : _element$fields.includes('name')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Nom :"), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      color: element.color || '#333'
    }
  }, "Jean Dupont")), ((_element$fields2 = element.fields) === null || _element$fields2 === void 0 ? void 0 : _element$fields2.includes('email')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Email :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: '#1976d2'
    }
  }, "jean.dupont@email.com")), ((_element$fields3 = element.fields) === null || _element$fields3 === void 0 ? void 0 : _element$fields3.includes('phone')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "T\xE9l\xE9phone :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "+33 6 12 34 56 78")), ((_element$fields4 = element.fields) === null || _element$fields4 === void 0 ? void 0 : _element$fields4.includes('address')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Adresse :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333',
      lineHeight: '1.4'
    }
  }, "123 Rue de la Paix", /*#__PURE__*/React.createElement("br", null), "75001 Paris", /*#__PURE__*/React.createElement("br", null), "France")), ((_element$fields5 = element.fields) === null || _element$fields5 === void 0 ? void 0 : _element$fields5.includes('company')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Soci\xE9t\xE9 :"), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      color: element.color || '#333'
    }
  }, "ABC Company SARL")), ((_element$fields6 = element.fields) === null || _element$fields6 === void 0 ? void 0 : _element$fields6.includes('vat')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "N\xB0 TVA :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "FR 12 345 678 901")), ((_element$fields7 = element.fields) === null || _element$fields7 === void 0 ? void 0 : _element$fields7.includes('siret')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "SIRET :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "123 456 789 00012")))), element.type === 'company_logo' && /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      display: 'flex',
      alignItems: 'center',
      justifyContent: element.alignment === 'center' ? 'center' : element.alignment === 'right' ? 'flex-end' : 'flex-start',
      padding: '8px',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
      boxSizing: 'border-box'
    }
  }, element.imageUrl ? /*#__PURE__*/React.createElement("img", {
    src: element.imageUrl,
    alt: "Logo entreprise",
    style: {
      width: "".concat(element.width || 150, "px"),
      height: "".concat(element.height || 80, "px"),
      objectFit: element.fit || 'contain',
      borderRadius: element.borderRadius || 0,
      border: element.borderWidth ? "".concat(element.borderWidth, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || 'transparent') : element.showBorder ? '1px solid transparent' : 'none'
    }
  }) : /*#__PURE__*/React.createElement("div", {
    style: {
      width: "".concat(element.width || 150, "px"),
      height: "".concat(element.height || 80, "px"),
      backgroundColor: '#f5f5f5',
      border: element.borderWidth ? "".concat(element.borderWidth, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || 'transparent') : element.showBorder ? '1px solid transparent' : 'none',
      borderRadius: element.borderRadius || '4px',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: '#999',
      fontSize: "".concat(12 * zoom, "px")
    }
  }, "\uD83C\uDFE2 Logo")), element.type === 'company_info' && /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      padding: "".concat(8 * zoom, "px"),
      fontSize: "".concat((element.fontSize || 12) * zoom, "px"),
      fontFamily: element.fontFamily || 'Arial, sans-serif',
      fontWeight: element.fontWeight || 'normal',
      fontStyle: element.fontStyle || 'normal',
      textDecoration: element.textDecoration || 'none',
      color: element.color || '#333',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
      boxSizing: 'border-box'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'row' : 'column',
      gap: "".concat(element.spacing * zoom || 8 * zoom, "px"),
      height: '100%'
    }
  }, ((_element$fields8 = element.fields) === null || _element$fields8 === void 0 ? void 0 : _element$fields8.includes('name')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Nom :"), /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      color: element.color || '#333'
    }
  }, "Ma Soci\xE9t\xE9 SARL")), ((_element$fields9 = element.fields) === null || _element$fields9 === void 0 ? void 0 : _element$fields9.includes('address')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Adresse :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333',
      lineHeight: '1.4'
    }
  }, "123 Rue de l'Entreprise", /*#__PURE__*/React.createElement("br", null), "75001 Paris - France")), ((_element$fields0 = element.fields) === null || _element$fields0 === void 0 ? void 0 : _element$fields0.includes('phone')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "T\xE9l\xE9phone :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "+33 1 23 45 67 89")), ((_element$fields1 = element.fields) === null || _element$fields1 === void 0 ? void 0 : _element$fields1.includes('email')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Email :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: '#1976d2'
    }
  }, "contact@masociete.com")), ((_element$fields10 = element.fields) === null || _element$fields10 === void 0 ? void 0 : _element$fields10.includes('website')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "Site web :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: '#1976d2'
    }
  }, "www.masociete.com")), ((_element$fields11 = element.fields) === null || _element$fields11 === void 0 ? void 0 : _element$fields11.includes('vat')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "N\xB0 TVA :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "FR 12 345 678 901")), ((_element$fields12 = element.fields) === null || _element$fields12 === void 0 ? void 0 : _element$fields12.includes('rcs')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'bold',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "RCS :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "Paris B 123 456 789")), ((_element$fields13 = element.fields) === null || _element$fields13 === void 0 ? void 0 : _element$fields13.includes('siret')) && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      flexDirection: element.layout === 'horizontal' ? 'column' : 'row',
      alignItems: element.layout === 'horizontal' ? 'flex-start' : 'center',
      gap: "".concat(4 * zoom, "px"),
      flex: element.layout === 'horizontal' ? '1' : 'none'
    }
  }, element.showLabels && /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
      textTransform: element.labelStyle === 'uppercase' ? 'uppercase' : 'none',
      color: element.color || '#666',
      minWidth: element.layout === 'horizontal' ? 'auto' : '80px',
      fontSize: "".concat(11 * zoom, "px")
    }
  }, "SIRET :"), /*#__PURE__*/React.createElement("div", {
    style: {
      color: element.color || '#333'
    }
  }, "123 456 789 00012")))), element.type === 'order_number' && /*#__PURE__*/React.createElement("div", {
    style: {
      width: '100%',
      height: '100%',
      display: 'flex',
      flexDirection: 'column',
      justifyContent: 'center',
      alignItems: element.textAlign === 'center' ? 'center' : element.textAlign === 'right' ? 'flex-end' : 'flex-start',
      padding: "".concat(8 * zoom, "px"),
      fontSize: "".concat((element.fontSize || 14) * zoom, "px"),
      fontFamily: element.fontFamily || 'Arial',
      fontWeight: element.fontWeight || 'bold',
      color: element.color || '#333333',
      textAlign: element.textAlign || 'right',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '2px',
      boxSizing: 'border-box'
    }
  }, element.showLabel && /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: "".concat(12 * zoom, "px"),
      fontWeight: 'normal',
      color: element.color || '#666',
      marginBottom: "".concat(4 * zoom, "px")
    }
  }, element.labelText || 'N° de commande:'), /*#__PURE__*/React.createElement("div", null, function () {
    // Utiliser le format défini ou une valeur par défaut
    var format = element.format || 'Commande #{order_number} - {order_date}';

    // Données de test pour l'aperçu (seront remplacées par les vraies données lors de la génération)
    var testData = {
      order_number: '12345',
      order_date: '15/10/2025'
    };

    // Remplacer les variables dans le format
    return format.replace(/{order_number}/g, testData.order_number).replace(/{order_date}/g, testData.order_date);
  }())), element.type === 'document_type' && /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'inline-block',
      padding: "".concat(8 * zoom, "px"),
      fontSize: "".concat((element.fontSize || 18) * zoom, "px"),
      fontFamily: element.fontFamily || 'Arial',
      fontWeight: element.fontWeight || 'bold',
      color: element.color || '#1e293b',
      textAlign: element.textAlign || 'center',
      backgroundColor: element.backgroundColor || 'transparent',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      borderRadius: element.borderRadius ? "".concat(element.borderRadius * zoom, "px") : '4px',
      whiteSpace: 'nowrap',
      boxSizing: 'border-box'
    }
  }, element.documentType === 'invoice' ? 'FACTURE' : element.documentType === 'quote' ? 'DEVIS' : element.documentType === 'receipt' ? 'REÇU' : element.documentType === 'order' ? 'COMMANDE' : element.documentType === 'credit_note' ? 'AVOIR' : 'DOCUMENT'), isSelected && /*#__PURE__*/React.createElement(React.Fragment, null, element.type !== 'line' && element.type !== 'divider' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-nw-".concat(element.id),
    className: "resize-handle nw",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current;
      e.stopPropagation();
      var canvas = (_elementRef$current = elementRef.current) === null || _elementRef$current === void 0 ? void 0 : _elementRef$current.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'nw', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-ne-".concat(element.id),
    className: "resize-handle ne",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current2;
      e.stopPropagation();
      var canvas = (_elementRef$current2 = elementRef.current) === null || _elementRef$current2 === void 0 ? void 0 : _elementRef$current2.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'ne', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-sw-".concat(element.id),
    className: "resize-handle sw",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current3;
      e.stopPropagation();
      var canvas = (_elementRef$current3 = elementRef.current) === null || _elementRef$current3 === void 0 ? void 0 : _elementRef$current3.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'sw', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  })), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-se-".concat(element.id),
    className: "resize-handle se",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current4;
      e.stopPropagation();
      var canvas = (_elementRef$current4 = elementRef.current) === null || _elementRef$current4 === void 0 ? void 0 : _elementRef$current4.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'se', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), element.type !== 'line' && element.type !== 'divider' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-n-".concat(element.id),
    className: "resize-handle n",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current5;
      e.stopPropagation();
      var canvas = (_elementRef$current5 = elementRef.current) === null || _elementRef$current5 === void 0 ? void 0 : _elementRef$current5.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'n', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-s-".concat(element.id),
    className: "resize-handle s",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current6;
      e.stopPropagation();
      var canvas = (_elementRef$current6 = elementRef.current) === null || _elementRef$current6 === void 0 ? void 0 : _elementRef$current6.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 's', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  })), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-w-".concat(element.id),
    className: "resize-handle w",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current7;
      e.stopPropagation();
      var canvas = (_elementRef$current7 = elementRef.current) === null || _elementRef$current7 === void 0 ? void 0 : _elementRef$current7.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'w', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-handle-e-".concat(element.id),
    className: "resize-handle e",
    onMouseDown: function onMouseDown(e) {
      var _elementRef$current8;
      e.stopPropagation();
      var canvas = (_elementRef$current8 = elementRef.current) === null || _elementRef$current8 === void 0 ? void 0 : _elementRef$current8.closest('.canvas-zoom-wrapper');
      var canvasRect = canvas ? canvas.getBoundingClientRect() : null;
      resize.handleResizeStart(e, 'e', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    },
    onContextMenu: handleContextMenuEvent
  }), element.type !== 'line' && element.type !== 'divider' && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    key: "resize-zone-n-".concat(element.id),
    className: "resize-zone resize-zone-n",
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      resize.handleResizeStart(e, 'n', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-zone-s-".concat(element.id),
    className: "resize-zone resize-zone-s",
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      resize.handleResizeStart(e, 's', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  })), /*#__PURE__*/React.createElement("div", {
    key: "resize-zone-w-".concat(element.id),
    className: "resize-zone resize-zone-w",
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      resize.handleResizeStart(e, 'w', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    key: "resize-zone-e-".concat(element.id),
    className: "resize-zone resize-zone-e",
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      resize.handleResizeStart(e, 'e', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  })), isSelected && enableRotation && /*#__PURE__*/React.createElement("div", {
    key: "rotation-handle-".concat(element.id),
    className: "rotation-handle",
    style: {
      position: 'absolute',
      top: "".concat(-20 * zoom, "px"),
      left: '50%',
      transform: 'translateX(-50%)',
      width: "".concat(12 * zoom, "px"),
      height: "".concat(12 * zoom, "px"),
      backgroundColor: '#3b82f6',
      border: "".concat(2 * zoom, "px solid white"),
      borderRadius: '50%',
      cursor: 'alias',
      zIndex: 1000,
      boxShadow: '0 2px 4px rgba(0,0,0,0.2)'
    },
    onMouseDown: function onMouseDown(e) {
      e.stopPropagation();
      rotation.handleRotationStart(e, element);
    },
    title: "Faire pivoter l'\xE9l\xE9ment"
  })), element.type === 'progress-bar' && /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      height: '100%',
      width: "".concat(element.progressValue || 75, "%"),
      backgroundColor: element.progressColor || '#3b82f6',
      borderRadius: '10px',
      transition: 'width 0.3s ease',
      // Bordures subtiles pour les éléments spéciaux
      border: element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * zoom * 0.5), "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
      boxSizing: 'border-box'
    }
  }));
};
;// ./src/hooks/useDragAndDrop.js
function useDragAndDrop_createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = useDragAndDrop_unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function useDragAndDrop_slicedToArray(r, e) { return useDragAndDrop_arrayWithHoles(r) || useDragAndDrop_iterableToArrayLimit(r, e) || useDragAndDrop_unsupportedIterableToArray(r, e) || useDragAndDrop_nonIterableRest(); }
function useDragAndDrop_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useDragAndDrop_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useDragAndDrop_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useDragAndDrop_arrayLikeToArray(r, a) : void 0; } }
function useDragAndDrop_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useDragAndDrop_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useDragAndDrop_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useDragAndDrop = function useDragAndDrop(_ref) {
  var onElementMove = _ref.onElementMove,
    onElementDrop = _ref.onElementDrop,
    _ref$snapToGrid = _ref.snapToGrid,
    snapToGrid = _ref$snapToGrid === void 0 ? true : _ref$snapToGrid,
    _ref$gridSize = _ref.gridSize,
    gridSize = _ref$gridSize === void 0 ? 10 : _ref$gridSize,
    _ref$zoom = _ref.zoom,
    zoom = _ref$zoom === void 0 ? 1 : _ref$zoom,
    _ref$canvasWidth = _ref.canvasWidth,
    canvasWidth = _ref$canvasWidth === void 0 ? 595 : _ref$canvasWidth,
    _ref$canvasHeight = _ref.canvasHeight,
    canvasHeight = _ref$canvasHeight === void 0 ? 842 : _ref$canvasHeight,
    _ref$guides = _ref.guides,
    guides = _ref$guides === void 0 ? {
      horizontal: [],
      vertical: []
    } : _ref$guides,
    _ref$snapToGuides = _ref.snapToGuides,
    snapToGuides = _ref$snapToGuides === void 0 ? true : _ref$snapToGuides;
  var _useState = (0,react.useState)(false),
    _useState2 = useDragAndDrop_slicedToArray(_useState, 2),
    isDragging = _useState2[0],
    setIsDragging = _useState2[1];
  var _useState3 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState4 = useDragAndDrop_slicedToArray(_useState3, 2),
    dragOffset = _useState4[0],
    setDragOffset = _useState4[1];
  var _useState5 = (0,react.useState)(null),
    _useState6 = useDragAndDrop_slicedToArray(_useState5, 2),
    draggedElementId = _useState6[0],
    setDraggedElementId = _useState6[1];
  var dragStartPos = (0,react.useRef)({
    x: 0,
    y: 0
  });
  var elementStartPos = (0,react.useRef)({
    x: 0,
    y: 0
  });
  var currentDragData = (0,react.useRef)(null);
  var currentDragOffset = (0,react.useRef)({
    x: 0,
    y: 0
  });
  var snapToGridValue = (0,react.useCallback)(function (value) {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);
  var snapToGuidesValue = (0,react.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    if (!snapToGuides) return value;
    var guideArray = isHorizontal ? guides.horizontal : guides.vertical;
    var snapTolerance = 5; // pixels
    var _iterator = useDragAndDrop_createForOfIteratorHelper(guideArray),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var guide = _step.value;
        if (Math.abs(value - guide) <= snapTolerance) {
          return guide;
        }
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
    return value;
  }, [snapToGuides, guides]);
  var snapValue = (0,react.useCallback)(function (value) {
    var isHorizontal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
    var elementType = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    // Pour les lignes, désactiver le snap horizontal pour permettre le déplacement libre
    if (elementType === 'line' && !isHorizontal) {
      return value;
    }
    var snapped = value;

    // Appliquer l'aimantation à la grille d'abord
    snapped = snapToGridValue(snapped);

    // Puis appliquer l'aimantation aux guides
    snapped = snapToGuidesValue(snapped, isHorizontal);
    return snapped;
  }, [snapToGridValue, snapToGuidesValue]);

  // Nettoyer les event listeners quand le composant se démonte
  (0,react.useEffect)(function () {
    return function () {
      if (currentDragData.current) {
        document.removeEventListener('mousemove', currentDragData.current.handleMouseMove);
        document.removeEventListener('mouseup', currentDragData.current.handleMouseUp);
        currentDragData.current = null;
      }
    };
  }, []);
  var handleMouseDown = (0,react.useCallback)(function (e, elementId, elementRect) {
    var canvasRect = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
    var zoomLevel = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
    var elementType = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : null;
    if (e.button !== 0) return; // Only left mouse button

    // Vérifier que l'élément source existe encore dans le DOM
    if (!e.target || !e.target.isConnected) {
      console.warn('Drag target no longer exists in DOM');
      return;
    }

    // Vérifier que l'élément parent existe encore
    if (!e.target.parentNode || !e.target.parentNode.isConnected) {
      console.warn('Drag target parent no longer exists in DOM');
      return;
    }

    // Log spécifique pour l'outil séparateur
    var isSeparator = elementType === 'divider';
    if (isSeparator) {
      // console.log(`[SEPARATOR LOG] Début du drag pour élément: ${elementId} (type: ${elementType})`);
      // console.log(`[SEPARATOR LOG] Position initiale élément: x=${elementRect.left}, y=${elementRect.top}`);
      // console.log(`[SEPARATOR LOG] Dimensions élément: width=${elementRect.width}, height=${elementRect.height}`);
    }
    e.preventDefault();
    setIsDragging(true);
    setDraggedElementId(elementId);

    // Obtenir le canvasRect dynamiquement si non fourni
    var currentCanvasRect = canvasRect || {
      left: 0,
      top: 0,
      width: canvasWidth,
      height: canvasHeight
    };
    var currentZoom = zoomLevel || zoom || 1;
    var startX = (e.clientX - currentCanvasRect.left) / currentZoom;
    var startY = (e.clientY - currentCanvasRect.top) / currentZoom;
    var lastMouseX = startX;
    var lastMouseY = startY;
    dragStartPos.current = {
      x: startX - elementRect.left,
      y: startY - elementRect.top
    };

    // Stocker les coordonnées initiales de l'élément pour le calcul de la position finale
    elementStartPos.current = {
      x: elementRect.left,
      y: elementRect.top
    };
    var handleMouseMove = function handleMouseMove(moveEvent) {
      // Vérifier que les données de drag existent toujours
      if (!currentDragData.current) {
        console.warn('Drag data no longer exists during move');
        return;
      }
      var mouseX = (moveEvent.clientX - currentCanvasRect.left) / currentZoom;
      var mouseY = (moveEvent.clientY - currentCanvasRect.top) / currentZoom;
      lastMouseX = mouseX;
      lastMouseY = mouseY;
      var deltaX = mouseX - startX;
      var deltaY = mouseY - startY;
      var effectiveCanvasWidth = canvasRect ? canvasRect.width / zoomLevel : canvasWidth;
      var effectiveCanvasHeight = canvasRect ? canvasRect.height / zoomLevel : canvasHeight;
      var newX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapValue(elementRect.left + deltaX, false, elementType)));
      var newY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapValue(elementRect.top + deltaY, true, elementType)));

      // Log pour le séparateur pendant le mouvement
      var elementType = currentDragData.current.elementType;
      var isSeparator = elementType === 'divider';
      var isLine = elementType === 'line';
      if (isSeparator) {
        // console.log(`[SEPARATOR LOG] Mouvement - Position calculée: x=${newX}, y=${newY}, delta: x=${deltaX}, y=${deltaY}`);
      }
      if (isLine) {
        // console.log(`[LINE LOG] Mouvement - Position calculée: x=${newX}, y=${newY}, delta: x=${deltaX}, y=${deltaY}, startX: ${startX}, mouseX: ${mouseX}`);
      }
      var newOffset = {
        x: newX - elementRect.left,
        y: newY - elementRect.top
      };
      setDragOffset(newOffset);
      currentDragOffset.current = newOffset;

      // Removed onElementMove call for performance - visual feedback via transform
    };
    var _handleMouseUp = function handleMouseUp() {
      // Vérifier que les données de drag existent toujours
      if (!currentDragData.current) {
        console.warn('Drag data no longer exists during drop');
        setIsDragging(false);
        setDragOffset({
          x: 0,
          y: 0
        });
        setDraggedElementId(null);
        return;
      }
      var _currentDragData$curr = currentDragData.current,
        elementId = _currentDragData$curr.elementId,
        elementType = _currentDragData$curr.elementType;
      setIsDragging(false);
      if (onElementDrop && elementId) {
        // Calculer la position finale en utilisant les coordonnées initiales de l'élément + le déplacement
        var finalX = elementStartPos.current.x + currentDragOffset.current.x;
        var finalY = elementStartPos.current.y + currentDragOffset.current.y;

        // Log pour le séparateur lors du drop
        var _isSeparator = elementType === 'divider';
        var isLine = elementType === 'line';
        if (_isSeparator) {
          // console.log(`[SEPARATOR LOG] Drop - Position initiale stockée: x=${elementStartPos.current.x}, y=${elementStartPos.current.y}`);
          // console.log(`[SEPARATOR LOG] Drop - Offset appliqué: x=${currentDragOffset.current.x}, y=${currentDragOffset.current.y}`);
          // console.log(`[SEPARATOR LOG] Drop - Position finale calculée: x=${finalX}, y=${finalY}`);
          // console.log(`[SEPARATOR LOG] Drop - Appel onElementDrop avec élément: ${elementId}`);
        }
        if (isLine) {
          // console.log(`[LINE LOG] Drop - Position initiale stockée: x=${elementStartPos.current.x}, y=${elementStartPos.current.y}`);
          // console.log(`[LINE LOG] Drop - Offset appliqué: x=${currentDragOffset.current.x}, y=${currentDragOffset.current.y}`);
          // console.log(`[LINE LOG] Drop - Position finale calculée: x=${finalX}, y=${finalY}`);
          // console.log(`[LINE LOG] Drop - Appel onElementDrop avec élément: ${elementId}`);
        }
        onElementDrop(elementId, {
          x: finalX,
          y: finalY
        });
      } else {
        console.warn('[DEBUG] onElementDrop not called - elementId:', elementId, 'onElementDrop:', !!onElementDrop);
      }
      setDragOffset({
        x: 0,
        y: 0
      });
      setDraggedElementId(null);
      elementStartPos.current = {
        x: 0,
        y: 0
      };
      currentDragOffset.current = {
        x: 0,
        y: 0
      };

      // Nettoyer les event listeners
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', _handleMouseUp);
      currentDragData.current = null;
    };

    // Stocker les références pour le nettoyage
    currentDragData.current = {
      handleMouseMove: handleMouseMove,
      handleMouseUp: _handleMouseUp,
      elementId: elementId,
      elementType: elementType
    };
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', _handleMouseUp);
  }, [snapToGridValue, onElementMove, onElementDrop, zoom, canvasWidth, canvasHeight]);
  var handleDragStart = (0,react.useCallback)(function (e, elementId, elementRect) {
    e.dataTransfer.setData('text/plain', elementId);
    e.dataTransfer.effectAllowed = 'move';
    dragStartPos.current = {
      x: e.clientX - elementRect.left,
      y: e.clientY - elementRect.top
    };
  }, []);
  var handleDragOver = (0,react.useCallback)(function (e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  }, []);
  var handleDrop = (0,react.useCallback)(function (e, canvasRect, elementRect) {
    e.preventDefault();
    var elementId = e.dataTransfer.getData('text/plain');
    if (!elementId) return;
    var dropX = (e.clientX - canvasRect.left - dragStartPos.current.x) / zoom;
    var dropY = (e.clientY - canvasRect.top - dragStartPos.current.y) / zoom;
    var effectiveCanvasWidth = canvasRect ? canvasRect.width / zoom : canvasWidth;
    var effectiveCanvasHeight = canvasRect ? canvasRect.height / zoom : canvasHeight;
    var snappedX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapValue(dropX, false)));
    var snappedY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapValue(dropY, true)));
    if (onElementDrop) {
      onElementDrop(elementId, {
        x: snappedX,
        y: snappedY
      });
    }
  }, [snapToGridValue, onElementDrop, zoom, canvasWidth, canvasHeight]);
  return {
    isDragging: isDragging,
    dragOffset: dragOffset,
    draggedElementId: draggedElementId,
    handleMouseDown: handleMouseDown,
    handleDragStart: handleDragStart,
    handleDragOver: handleDragOver,
    handleDrop: handleDrop
  };
};
;// ./src/components/Toolbar.jsx
function Toolbar_slicedToArray(r, e) { return Toolbar_arrayWithHoles(r) || Toolbar_iterableToArrayLimit(r, e) || Toolbar_unsupportedIterableToArray(r, e) || Toolbar_nonIterableRest(); }
function Toolbar_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function Toolbar_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return Toolbar_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? Toolbar_arrayLikeToArray(r, a) : void 0; } }
function Toolbar_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function Toolbar_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function Toolbar_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var Toolbar = function Toolbar(_ref) {
  var selectedTool = _ref.selectedTool,
    onToolSelect = _ref.onToolSelect,
    zoom = _ref.zoom,
    onZoomChange = _ref.onZoomChange,
    showGrid = _ref.showGrid,
    onShowGridChange = _ref.onShowGridChange,
    snapToGrid = _ref.snapToGrid,
    onSnapToGridChange = _ref.onSnapToGridChange,
    onUndo = _ref.onUndo,
    onRedo = _ref.onRedo,
    canUndo = _ref.canUndo,
    canRedo = _ref.canRedo,
    onPreview = _ref.onPreview;
  var _React$useState = react.useState('home'),
    _React$useState2 = Toolbar_slicedToArray(_React$useState, 2),
    activeTab = _React$useState2[0],
    setActiveTab = _React$useState2[1];
  var textTools = [{
    id: 'select',
    label: 'Sélection (V)',
    icon: '👆',
    shortcut: 'V'
  }, {
    id: 'add-text',
    label: 'Texte Simple (T)',
    icon: '📝',
    shortcut: 'T'
  }, {
    id: 'add-text-title',
    label: 'Titre (H)',
    icon: '📄',
    shortcut: 'H'
  }, {
    id: 'add-text-subtitle',
    label: 'Sous-titre (S)',
    icon: '📋',
    shortcut: 'S'
  }];
  var shapeTools = [{
    id: 'add-rectangle',
    label: 'Rectangle (R)',
    icon: '▭',
    shortcut: 'R'
  }, {
    id: 'add-circle',
    label: 'Cercle (C)',
    icon: '○',
    shortcut: 'C'
  }, {
    id: 'add-line',
    label: 'Ligne (L)',
    icon: '━',
    shortcut: 'L'
  }, {
    id: 'add-arrow',
    label: 'Flèche (A)',
    icon: '➤',
    shortcut: 'A'
  }, {
    id: 'add-triangle',
    label: 'Triangle (3)',
    icon: '△',
    shortcut: '3'
  }, {
    id: 'add-star',
    label: 'Étoile (5)',
    icon: '⭐',
    shortcut: '5'
  }];
  var insertTools = [{
    id: 'add-divider',
    label: 'Séparateur (D)',
    icon: '⎯',
    shortcut: 'D'
  }, {
    id: 'add-image',
    label: 'Image (I)',
    icon: '🖼️',
    shortcut: 'I'
  }];
  var tabs = [{
    id: 'home',
    label: 'Accueil',
    icon: '🏠'
  }, {
    id: 'insert',
    label: 'Insertion',
    icon: '➕'
  }, {
    id: 'view',
    label: 'Affichage',
    icon: '👁️'
  }];
  return /*#__PURE__*/react.createElement("div", {
    className: "toolbar ribbon-toolbar"
  }, /*#__PURE__*/react.createElement("div", {
    className: "toolbar-tabs"
  }, tabs.map(function (tab) {
    return /*#__PURE__*/react.createElement("button", {
      key: tab.id,
      className: "tab-button ".concat(activeTab === tab.id ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab(tab.id);
      }
    }, /*#__PURE__*/react.createElement("span", {
      className: "tab-icon"
    }, tab.icon), /*#__PURE__*/react.createElement("span", {
      className: "tab-label"
    }, tab.label));
  })), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-content"
  }, activeTab === 'home' && /*#__PURE__*/react.createElement("div", {
    className: "tab-content"
  }, /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Actions"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, /*#__PURE__*/react.createElement("button", {
    className: "tool-button",
    onClick: onPreview,
    title: "Aper\xE7u du PDF"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\uD83D\uDC41\uFE0F"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "Aper\xE7u")))), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Presse-papiers"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, /*#__PURE__*/react.createElement("button", {
    className: "edit-button",
    onClick: onUndo,
    disabled: !canUndo,
    title: "Annuler (Ctrl+Z)"
  }, /*#__PURE__*/react.createElement("div", {
    className: "button-content"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\u21B6"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "Annuler"))), /*#__PURE__*/react.createElement("button", {
    className: "edit-button",
    onClick: onRedo,
    disabled: !canRedo,
    title: "R\xE9tablir (Ctrl+Y)"
  }, /*#__PURE__*/react.createElement("div", {
    className: "button-content"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\u21B7"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "R\xE9tablir"))))), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Outils"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, textTools.map(function (tool) {
    return /*#__PURE__*/react.createElement("button", {
      key: tool.id,
      className: "tool-button ".concat(selectedTool === tool.id ? 'active' : ''),
      onClick: function onClick() {
        return onToolSelect(tool.id);
      },
      title: tool.label
    }, /*#__PURE__*/react.createElement("div", {
      className: "tool-content"
    }, /*#__PURE__*/react.createElement("span", {
      className: "tool-icon"
    }, tool.icon), /*#__PURE__*/react.createElement("span", {
      className: "tool-label"
    }, tool.shortcut)));
  }))), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Formes"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons shapes-grid"
  }, shapeTools.map(function (tool) {
    return /*#__PURE__*/react.createElement("button", {
      key: tool.id,
      className: "tool-button ".concat(selectedTool === tool.id ? 'active' : ''),
      onClick: function onClick() {
        return onToolSelect(tool.id);
      },
      title: tool.label
    }, /*#__PURE__*/react.createElement("div", {
      className: "tool-content"
    }, /*#__PURE__*/react.createElement("span", {
      className: "tool-icon"
    }, tool.icon), /*#__PURE__*/react.createElement("span", {
      className: "tool-label"
    }, tool.shortcut)));
  })))), activeTab === 'insert' && /*#__PURE__*/react.createElement("div", {
    className: "tab-content"
  }, /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "\xC9l\xE9ments"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, insertTools.map(function (tool) {
    return /*#__PURE__*/react.createElement("button", {
      key: tool.id,
      className: "tool-button ".concat(selectedTool === tool.id ? 'active' : ''),
      onClick: function onClick() {
        return onToolSelect(tool.id);
      },
      title: tool.label
    }, /*#__PURE__*/react.createElement("div", {
      className: "tool-content"
    }, /*#__PURE__*/react.createElement("span", {
      className: "tool-icon"
    }, tool.icon), /*#__PURE__*/react.createElement("span", {
      className: "tool-label"
    }, tool.shortcut)));
  })))), activeTab === 'view' && /*#__PURE__*/react.createElement("div", {
    className: "tab-content"
  }, /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Zoom"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, /*#__PURE__*/react.createElement("div", {
    className: "zoom-controls"
  }, /*#__PURE__*/react.createElement("button", {
    className: "zoom-button",
    onClick: function onClick() {
      return onZoomChange(Math.max(0.1, zoom - 0.1));
    },
    title: "Zoom arri\xE8re (Ctrl+-)"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\uD83D\uDD0D"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "-")), /*#__PURE__*/react.createElement("span", {
    className: "zoom-value"
  }, Math.round(zoom * 100), "%"), /*#__PURE__*/react.createElement("button", {
    className: "zoom-button",
    onClick: function onClick() {
      return onZoomChange(Math.min(3, zoom + 0.1));
    },
    title: "Zoom avant (Ctrl+=)"
  }, /*#__PURE__*/react.createElement("span", {
    className: "button-icon"
  }, "\uD83D\uDD0D"), /*#__PURE__*/react.createElement("span", {
    className: "button-text"
  }, "+"))))), /*#__PURE__*/react.createElement("div", {
    className: "toolbar-group"
  }, /*#__PURE__*/react.createElement("h5", null, "Affichage"), /*#__PURE__*/react.createElement("div", {
    className: "group-buttons"
  }, /*#__PURE__*/react.createElement("div", {
    className: "display-options"
  }, /*#__PURE__*/react.createElement("label", {
    className: "toggle-label"
  }, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    checked: showGrid,
    onChange: function onChange(e) {
      return onShowGridChange(e.target.checked);
    }
  }), /*#__PURE__*/react.createElement("span", {
    className: "toggle-text"
  }, "Grille"), /*#__PURE__*/react.createElement("span", {
    className: "toggle-shortcut"
  }, "(G)")), /*#__PURE__*/react.createElement("label", {
    className: "toggle-label"
  }, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    checked: snapToGrid,
    onChange: function onChange(e) {
      return onSnapToGridChange(e.target.checked);
    }
  }), /*#__PURE__*/react.createElement("span", {
    className: "toggle-text"
  }, "Aimantation"), /*#__PURE__*/react.createElement("span", {
    className: "toggle-shortcut"
  }, "(M)"))))))));
};
;// ./src/hooks/useHistory.js
function useHistory_slicedToArray(r, e) { return useHistory_arrayWithHoles(r) || useHistory_iterableToArrayLimit(r, e) || useHistory_unsupportedIterableToArray(r, e) || useHistory_nonIterableRest(); }
function useHistory_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useHistory_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useHistory_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useHistory_arrayLikeToArray(r, a) : void 0; } }
function useHistory_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useHistory_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useHistory_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useHistory = function useHistory() {
  var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var _options$maxHistorySi = options.maxHistorySize,
    maxHistorySize = _options$maxHistorySi === void 0 ? 50 : _options$maxHistorySi;
  var _useState = (0,react.useState)([]),
    _useState2 = useHistory_slicedToArray(_useState, 2),
    history = _useState2[0],
    setHistory = _useState2[1];
  var _useState3 = (0,react.useState)(-1),
    _useState4 = useHistory_slicedToArray(_useState3, 2),
    currentIndex = _useState4[0],
    setCurrentIndex = _useState4[1];
  var isUndoRedoRef = (0,react.useRef)(false);
  var addToHistory = (0,react.useCallback)(function (state) {
    if (isUndoRedoRef.current) {
      isUndoRedoRef.current = false;
      return;
    }
    setHistory(function (prevHistory) {
      // Supprimer tout ce qui vient après l'index actuel
      var newHistory = prevHistory.slice(0, currentIndex + 1);

      // Ajouter le nouvel état
      newHistory.push(state);

      // Limiter la taille de l'historique
      if (newHistory.length > maxHistorySize) {
        newHistory.shift();
        setCurrentIndex(newHistory.length - 1);
        return newHistory;
      }
      setCurrentIndex(newHistory.length - 1);
      return newHistory;
    });
  }, [currentIndex, maxHistorySize]);
  var undo = (0,react.useCallback)(function () {
    if (currentIndex > 0) {
      isUndoRedoRef.current = true;
      setCurrentIndex(function (prev) {
        return prev - 1;
      });
      return history[currentIndex - 1];
    }
    return null;
  }, [currentIndex, history]);
  var redo = (0,react.useCallback)(function () {
    if (currentIndex < history.length - 1) {
      isUndoRedoRef.current = true;
      setCurrentIndex(function (prev) {
        return prev + 1;
      });
      return history[currentIndex + 1];
    }
    return null;
  }, [currentIndex, history]);
  var canUndo = (0,react.useCallback)(function () {
    return currentIndex > 0;
  }, [currentIndex]);
  var canRedo = (0,react.useCallback)(function () {
    return currentIndex < history.length - 1;
  }, [currentIndex]);
  var clearHistory = (0,react.useCallback)(function () {
    setHistory([]);
    setCurrentIndex(-1);
  }, []);
  var getCurrentState = (0,react.useCallback)(function () {
    return currentIndex >= 0 ? history[currentIndex] : null;
  }, [currentIndex, history]);
  return {
    addToHistory: addToHistory,
    undo: undo,
    redo: redo,
    canUndo: canUndo,
    canRedo: canRedo,
    clearHistory: clearHistory,
    getCurrentState: getCurrentState,
    historySize: history.length,
    currentIndex: currentIndex
  };
};
;// ./src/hooks/useSelection.js
function useSelection_typeof(o) { "@babel/helpers - typeof"; return useSelection_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, useSelection_typeof(o); }
function useSelection_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function useSelection_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? useSelection_ownKeys(Object(t), !0).forEach(function (r) { useSelection_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : useSelection_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function useSelection_defineProperty(e, r, t) { return (r = useSelection_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function useSelection_toPropertyKey(t) { var i = useSelection_toPrimitive(t, "string"); return "symbol" == useSelection_typeof(i) ? i : i + ""; }
function useSelection_toPrimitive(t, r) { if ("object" != useSelection_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != useSelection_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || useSelection_unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return useSelection_arrayLikeToArray(r); }
function useSelection_slicedToArray(r, e) { return useSelection_arrayWithHoles(r) || useSelection_iterableToArrayLimit(r, e) || useSelection_unsupportedIterableToArray(r, e) || useSelection_nonIterableRest(); }
function useSelection_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useSelection_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useSelection_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useSelection_arrayLikeToArray(r, a) : void 0; } }
function useSelection_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useSelection_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useSelection_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useSelection = function useSelection(_ref) {
  var onSelectionChange = _ref.onSelectionChange,
    _ref$multiSelect = _ref.multiSelect,
    multiSelect = _ref$multiSelect === void 0 ? true : _ref$multiSelect;
  var _useState = (0,react.useState)([]),
    _useState2 = useSelection_slicedToArray(_useState, 2),
    selectedElements = _useState2[0],
    setSelectedElements = _useState2[1];
  var _useState3 = (0,react.useState)(null),
    _useState4 = useSelection_slicedToArray(_useState3, 2),
    selectionBox = _useState4[0],
    setSelectionBox = _useState4[1];
  var selectElement = (0,react.useCallback)(function (elementId) {
    var addToSelection = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    if (multiSelect && addToSelection) {
      setSelectedElements(function (prev) {
        var isAlreadySelected = prev.includes(elementId);
        var newSelection = isAlreadySelected ? prev.filter(function (id) {
          return id !== elementId;
        }) : [].concat(_toConsumableArray(prev), [elementId]);
        onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(newSelection);
        return newSelection;
      });
    } else {
      setSelectedElements([elementId]);
      onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange([elementId]);
    }
  }, [multiSelect, onSelectionChange]);
  var selectAll = (0,react.useCallback)(function (elementIds) {
    setSelectedElements(elementIds);
    onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(elementIds);
  }, [onSelectionChange]);
  var clearSelection = (0,react.useCallback)(function () {
    setSelectedElements([]);
    onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange([]);
  }, [onSelectionChange]);
  var isSelected = (0,react.useCallback)(function (elementId) {
    return selectedElements.includes(elementId);
  }, [selectedElements]);
  var startSelectionBox = (0,react.useCallback)(function (startX, startY) {
    setSelectionBox({
      startX: startX,
      startY: startY,
      endX: startX,
      endY: startY
    });
  }, []);
  var updateSelectionBox = (0,react.useCallback)(function (endX, endY) {
    setSelectionBox(function (prev) {
      return prev ? useSelection_objectSpread(useSelection_objectSpread({}, prev), {}, {
        endX: endX,
        endY: endY
      }) : null;
    });
  }, []);
  var endSelectionBox = (0,react.useCallback)(function (elements) {
    if (!selectionBox) return;
    var startX = selectionBox.startX,
      startY = selectionBox.startY,
      endX = selectionBox.endX,
      endY = selectionBox.endY;
    var minX = Math.min(startX, endX);
    var maxX = Math.max(startX, endX);
    var minY = Math.min(startY, endY);
    var maxY = Math.max(startY, endY);
    var selectedInBox = elements.filter(function (element) {
      var elementCenterX = element.x + element.width / 2;
      var elementCenterY = element.y + element.height / 2;
      return elementCenterX >= minX && elementCenterX <= maxX && elementCenterY >= minY && elementCenterY <= maxY;
    }).map(function (element) {
      return element.id;
    });
    if (selectedInBox.length > 0) {
      if (multiSelect) {
        setSelectedElements(function (prev) {
          var newSelection = _toConsumableArray(new Set([].concat(_toConsumableArray(prev), _toConsumableArray(selectedInBox))));
          onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(newSelection);
          return newSelection;
        });
      } else {
        setSelectedElements(selectedInBox);
        onSelectionChange === null || onSelectionChange === void 0 || onSelectionChange(selectedInBox);
      }
    }
    setSelectionBox(null);
  }, [selectionBox, multiSelect, onSelectionChange]);
  var deleteSelected = (0,react.useCallback)(function () {
    // Cette fonction retourne les IDs à supprimer, la logique de suppression
    // sera gérée par le composant parent
    return _toConsumableArray(selectedElements);
  }, [selectedElements]);
  var duplicateSelected = (0,react.useCallback)(function () {
    // Cette fonction retourne les IDs à dupliquer, la logique de duplication
    // sera gérée par le composant parent
    return _toConsumableArray(selectedElements);
  }, [selectedElements]);
  return {
    selectedElements: selectedElements,
    selectionBox: selectionBox,
    selectElement: selectElement,
    selectAll: selectAll,
    clearSelection: clearSelection,
    isSelected: isSelected,
    startSelectionBox: startSelectionBox,
    updateSelectionBox: updateSelectionBox,
    endSelectionBox: endSelectionBox,
    deleteSelected: deleteSelected,
    duplicateSelected: duplicateSelected
  };
};
;// ./src/hooks/useClipboard.js
function useClipboard_slicedToArray(r, e) { return useClipboard_arrayWithHoles(r) || useClipboard_iterableToArrayLimit(r, e) || useClipboard_unsupportedIterableToArray(r, e) || useClipboard_nonIterableRest(); }
function useClipboard_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useClipboard_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useClipboard_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useClipboard_arrayLikeToArray(r, a) : void 0; } }
function useClipboard_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useClipboard_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useClipboard_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useClipboard = function useClipboard(_ref) {
  var onPaste = _ref.onPaste;
  var _useState = (0,react.useState)(null),
    _useState2 = useClipboard_slicedToArray(_useState, 2),
    clipboardData = _useState2[0],
    setClipboardData = _useState2[1];
  var copy = (0,react.useCallback)(function (data) {
    setClipboardData(data);
    // Ici on pourrait aussi utiliser l'API Clipboard du navigateur
    // mais pour la compatibilité, on utilise un état interne
  }, []);
  var paste = (0,react.useCallback)(function () {
    if (clipboardData && onPaste) {
      onPaste(clipboardData);
    }
    return clipboardData;
  }, [clipboardData, onPaste]);
  var cut = (0,react.useCallback)(function (data) {
    copy(data);
    // La suppression sera gérée par le composant parent
    return data;
  }, [copy]);
  var hasData = (0,react.useCallback)(function () {
    return clipboardData !== null;
  }, [clipboardData]);
  var clear = (0,react.useCallback)(function () {
    setClipboardData(null);
  }, []);
  return {
    copy: copy,
    paste: paste,
    cut: cut,
    hasData: hasData,
    clear: clear,
    clipboardData: clipboardData
  };
};
;// ./src/hooks/useZoom.js
function useZoom_slicedToArray(r, e) { return useZoom_arrayWithHoles(r) || useZoom_iterableToArrayLimit(r, e) || useZoom_unsupportedIterableToArray(r, e) || useZoom_nonIterableRest(); }
function useZoom_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useZoom_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useZoom_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useZoom_arrayLikeToArray(r, a) : void 0; } }
function useZoom_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useZoom_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useZoom_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useZoom = function useZoom(_ref) {
  var _ref$initialZoom = _ref.initialZoom,
    initialZoom = _ref$initialZoom === void 0 ? 1 : _ref$initialZoom,
    _ref$minZoom = _ref.minZoom,
    minZoom = _ref$minZoom === void 0 ? 0.1 : _ref$minZoom,
    _ref$maxZoom = _ref.maxZoom,
    maxZoom = _ref$maxZoom === void 0 ? 3 : _ref$maxZoom,
    _ref$zoomStep = _ref.zoomStep,
    zoomStep = _ref$zoomStep === void 0 ? 0.1 : _ref$zoomStep;
  var _useState = (0,react.useState)(initialZoom),
    _useState2 = useZoom_slicedToArray(_useState, 2),
    zoom = _useState2[0],
    setZoom = _useState2[1];
  var _useState3 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState4 = useZoom_slicedToArray(_useState3, 2),
    zoomOrigin = _useState4[0],
    setZoomOrigin = _useState4[1];
  var zoomIn = (0,react.useCallback)(function () {
    setZoom(function (prevZoom) {
      return Math.min(prevZoom + zoomStep, maxZoom);
    });
  }, [maxZoom, zoomStep]);
  var zoomOut = (0,react.useCallback)(function () {
    setZoom(function (prevZoom) {
      return Math.max(prevZoom - zoomStep, minZoom);
    });
  }, [minZoom, zoomStep]);
  var setZoomLevel = (0,react.useCallback)(function (newZoom) {
    var clampedZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));
    setZoom(clampedZoom);
  }, [minZoom, maxZoom]);
  var resetZoom = (0,react.useCallback)(function () {
    setZoom(initialZoom);
  }, [initialZoom]);
  var zoomToFit = (0,react.useCallback)(function (canvasWidth, canvasHeight, containerWidth, containerHeight) {
    var scaleX = containerWidth / canvasWidth;
    var scaleY = containerHeight / canvasHeight;
    var newZoom = Math.min(scaleX, scaleY, maxZoom);
    setZoom(Math.max(newZoom, minZoom));
  }, [minZoom, maxZoom]);
  var zoomToPoint = (0,react.useCallback)(function (pointX, pointY, zoomFactor) {
    var newZoom = Math.max(minZoom, Math.min(maxZoom, zoom * zoomFactor));
    setZoomOrigin({
      x: pointX,
      y: pointY
    });
    setZoom(newZoom);
  }, [zoom, minZoom, maxZoom]);
  var zoomToSelection = (0,react.useCallback)(function (selectedElements, canvasWidth, canvasHeight, containerWidth, containerHeight) {
    if (!selectedElements || selectedElements.length === 0) return;

    // Calculer le rectangle englobant de tous les éléments sélectionnés
    var minX = Infinity,
      minY = Infinity,
      maxX = -Infinity,
      maxY = -Infinity;
    selectedElements.forEach(function (element) {
      minX = Math.min(minX, element.x);
      minY = Math.min(minY, element.y);
      maxX = Math.max(maxX, element.x + element.width);
      maxY = Math.max(maxY, element.y + element.height);
    });
    var selectionWidth = maxX - minX;
    var selectionHeight = maxY - minY;

    // Ajouter une marge de 20% autour de la sélection
    var margin = 0.2;
    var paddedWidth = selectionWidth * (1 + margin);
    var paddedHeight = selectionHeight * (1 + margin);

    // Calculer le zoom nécessaire pour faire tenir la sélection dans le conteneur
    var scaleX = containerWidth / paddedWidth;
    var scaleY = containerHeight / paddedHeight;
    var newZoom = Math.min(scaleX, scaleY, maxZoom);

    // S'assurer que le zoom est dans les limites
    var clampedZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));

    // Calculer le centre de la sélection
    var selectionCenterX = minX + selectionWidth / 2;
    var selectionCenterY = minY + selectionHeight / 2;

    // Calculer le décalage pour centrer la sélection dans le conteneur
    var containerCenterX = containerWidth / 2;
    var containerCenterY = containerHeight / 2;

    // Le décalage nécessaire pour centrer la sélection
    var offsetX = containerCenterX - selectionCenterX * clampedZoom;
    var offsetY = containerCenterY - selectionCenterY * clampedZoom;
    setZoom(clampedZoom);
    setZoomOrigin({
      x: offsetX,
      y: offsetY
    });
  }, [minZoom, maxZoom]);
  var getTransformStyle = (0,react.useCallback)(function () {
    return {
      transform: "scale(".concat(zoom, ")"),
      transformOrigin: "".concat(zoomOrigin.x, "px ").concat(zoomOrigin.y, "px")
    };
  }, [zoom, zoomOrigin]);
  var screenToCanvas = (0,react.useCallback)(function (screenX, screenY) {
    return {
      x: (screenX - zoomOrigin.x) / zoom,
      y: (screenY - zoomOrigin.y) / zoom
    };
  }, [zoom, zoomOrigin]);
  var canvasToScreen = (0,react.useCallback)(function (canvasX, canvasY) {
    return {
      x: canvasX * zoom + zoomOrigin.x,
      y: canvasY * zoom + zoomOrigin.y
    };
  }, [zoom, zoomOrigin]);
  return {
    zoom: zoom,
    zoomOrigin: zoomOrigin,
    zoomIn: zoomIn,
    zoomOut: zoomOut,
    setZoomLevel: setZoomLevel,
    resetZoom: resetZoom,
    zoomToFit: zoomToFit,
    zoomToPoint: zoomToPoint,
    zoomToSelection: zoomToSelection,
    getTransformStyle: getTransformStyle,
    screenToCanvas: screenToCanvas,
    canvasToScreen: canvasToScreen
  };
};
;// ./src/hooks/useContextMenu.js
function useContextMenu_slicedToArray(r, e) { return useContextMenu_arrayWithHoles(r) || useContextMenu_iterableToArrayLimit(r, e) || useContextMenu_unsupportedIterableToArray(r, e) || useContextMenu_nonIterableRest(); }
function useContextMenu_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useContextMenu_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useContextMenu_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useContextMenu_arrayLikeToArray(r, a) : void 0; } }
function useContextMenu_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useContextMenu_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useContextMenu_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useContextMenu = function useContextMenu() {
  var _useState = (0,react.useState)(null),
    _useState2 = useContextMenu_slicedToArray(_useState, 2),
    contextMenu = _useState2[0],
    setContextMenu = _useState2[1];
  var _useState3 = (0,react.useState)(false),
    _useState4 = useContextMenu_slicedToArray(_useState3, 2),
    isAnimating = _useState4[0],
    setIsAnimating = _useState4[1];
  var showContextMenu = (0,react.useCallback)(function (x, y, items) {
    // Ajuster la position pour éviter que le menu sorte de l'écran
    var menuWidth = 180; // Largeur approximative du menu
    var menuHeight = items.length * 36; // Hauteur approximative

    var adjustedX = x;
    var adjustedY = y;

    // Ajuster horizontalement
    if (x + menuWidth > window.innerWidth) {
      adjustedX = x - menuWidth;
    }

    // Ajuster verticalement
    if (y + menuHeight > window.innerHeight) {
      adjustedY = y - menuHeight;
    }

    // S'assurer que le menu reste dans les limites
    adjustedX = Math.max(0, Math.min(adjustedX, window.innerWidth - menuWidth));
    adjustedY = Math.max(0, Math.min(adjustedY, window.innerHeight - menuHeight));
    setContextMenu({
      x: adjustedX,
      y: adjustedY,
      items: items
    });
  }, []);
  var hideContextMenu = (0,react.useCallback)(function () {
    if (contextMenu) {
      setIsAnimating(true);
      // Attendre la fin de l'animation avant de masquer complètement
      setTimeout(function () {
        setContextMenu(null);
        setIsAnimating(false);
      }, 150); // Durée de l'animation
    }
  }, [contextMenu]);
  var handleContextMenuAction = (0,react.useCallback)(function (action) {
    hideContextMenu();
    return action;
  }, [hideContextMenu]);
  (0,react.useEffect)(function () {
    var handleClickOutside = function handleClickOutside(e) {
      if (contextMenu && !e.target.closest('.context-menu')) {
        hideContextMenu();
      }
    };
    var handleEscape = function handleEscape(e) {
      if (e.key === 'Escape' && contextMenu) {
        hideContextMenu();
      }
    };
    if (contextMenu) {
      document.addEventListener('mousedown', handleClickOutside);
      document.addEventListener('keydown', handleEscape);
    }
    return function () {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleEscape);
    };
  }, [contextMenu, hideContextMenu]);
  return {
    contextMenu: contextMenu,
    showContextMenu: showContextMenu,
    hideContextMenu: hideContextMenu,
    handleContextMenuAction: handleContextMenuAction,
    isAnimating: isAnimating
  };
};
;// ./src/hooks/useCanvasState.js
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function useCanvasState_createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = useCanvasState_unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function useCanvasState_typeof(o) { "@babel/helpers - typeof"; return useCanvasState_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, useCanvasState_typeof(o); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function useCanvasState_toConsumableArray(r) { return useCanvasState_arrayWithoutHoles(r) || useCanvasState_iterableToArray(r) || useCanvasState_unsupportedIterableToArray(r) || useCanvasState_nonIterableSpread(); }
function useCanvasState_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useCanvasState_iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function useCanvasState_arrayWithoutHoles(r) { if (Array.isArray(r)) return useCanvasState_arrayLikeToArray(r); }
function useCanvasState_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function useCanvasState_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? useCanvasState_ownKeys(Object(t), !0).forEach(function (r) { useCanvasState_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : useCanvasState_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function useCanvasState_defineProperty(e, r, t) { return (r = useCanvasState_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function useCanvasState_toPropertyKey(t) { var i = useCanvasState_toPrimitive(t, "string"); return "symbol" == useCanvasState_typeof(i) ? i : i + ""; }
function useCanvasState_toPrimitive(t, r) { if ("object" != useCanvasState_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != useCanvasState_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function useCanvasState_slicedToArray(r, e) { return useCanvasState_arrayWithHoles(r) || useCanvasState_iterableToArrayLimit(r, e) || useCanvasState_unsupportedIterableToArray(r, e) || useCanvasState_nonIterableRest(); }
function useCanvasState_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useCanvasState_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useCanvasState_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useCanvasState_arrayLikeToArray(r, a) : void 0; } }
function useCanvasState_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useCanvasState_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useCanvasState_arrayWithHoles(r) { if (Array.isArray(r)) return r; }









// Hook utilitaire pour synchroniser les refs
var useLatest = function useLatest(value) {
  var ref = (0,react.useRef)(value);
  ref.current = value;
  return ref;
};

// Fallback notification system in case Toastr is not available
if (typeof window !== 'undefined' && typeof window.toastr === 'undefined') {
  // Simple notification system
  var createNotification = function createNotification(type, title, message) {
    var notification = document.createElement('div');
    notification.style.cssText = "\n      position: fixed;\n      top: 20px;\n      right: 20px;\n      z-index: 100;\n      padding: 15px 20px;\n      margin-bottom: 10px;\n      border-radius: 5px;\n      color: white;\n      font-family: Arial, sans-serif;\n      font-size: 14px;\n      box-shadow: 0 4px 12px rgba(0,0,0,0.3);\n      max-width: 300px;\n      opacity: 0;\n      transform: translateX(100%);\n      transition: all 0.3s ease;\n    ";

    // Set colors based on type
    switch (type) {
      case 'success':
        notification.style.backgroundColor = '#51A351';
        break;
      case 'error':
        notification.style.backgroundColor = '#BD362F';
        break;
      case 'warning':
        notification.style.backgroundColor = '#F89406';
        break;
      case 'info':
      default:
        notification.style.backgroundColor = '#2F96B4';
        break;
    }

    // Create content
    var titleElement = title ? "<strong>".concat(title, "</strong><br>") : '';
    notification.innerHTML = "".concat(titleElement).concat(message);

    // Add close button
    var closeButton = document.createElement('button');
    closeButton.innerHTML = '×';
    closeButton.style.cssText = "\n      position: absolute;\n      top: 5px;\n      right: 10px;\n      background: none;\n      border: none;\n      color: white;\n      font-size: 20px;\n      cursor: pointer;\n      opacity: 0.8;\n    ";
    closeButton.onclick = function () {
      return removeNotification(notification);
    };
    notification.appendChild(closeButton);

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(function () {
      notification.style.opacity = '1';
      notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto remove after 5 seconds
    setTimeout(function () {
      return removeNotification(notification);
    }, 5000);
    function removeNotification(el) {
      el.style.opacity = '0';
      el.style.transform = 'translateX(100%)';
      setTimeout(function () {
        if (el.parentNode) {
          el.parentNode.removeChild(el);
        }
      }, 300);
    }
  };

  // Create fallback toastr object
  window.toastr = {
    success: function success(message, title) {
      createNotification('success', title, message);
    },
    error: function error(message, title) {
      createNotification('error', title, message);
    },
    warning: function warning(message, title) {
      createNotification('warning', title, message);
    },
    info: function info(message, title) {
      createNotification('info', title, message);
    },
    options: {} // Placeholder for options
  };
}
var useCanvasState = function useCanvasState(_ref) {
  var _globalSettings$multi;
  var _ref$initialElements = _ref.initialElements,
    initialElements = _ref$initialElements === void 0 ? [] : _ref$initialElements,
    _ref$templateId = _ref.templateId,
    templateId = _ref$templateId === void 0 ? null : _ref$templateId,
    _ref$canvasWidth = _ref.canvasWidth,
    canvasWidth = _ref$canvasWidth === void 0 ? 595 : _ref$canvasWidth,
    _ref$canvasHeight = _ref.canvasHeight,
    canvasHeight = _ref$canvasHeight === void 0 ? 842 : _ref$canvasHeight,
    _ref$globalSettings = _ref.globalSettings,
    globalSettings = _ref$globalSettings === void 0 ? null : _ref$globalSettings;
  // Logs conditionnels selon l'environnement
  var isDevelopment = "production" === 'development';
  var _useState = (0,react.useState)(initialElements),
    _useState2 = useCanvasState_slicedToArray(_useState, 2),
    elements = _useState2[0],
    setElements = _useState2[1];
  var _useState3 = (0,react.useState)(1),
    _useState4 = useCanvasState_slicedToArray(_useState3, 2),
    nextId = _useState4[0],
    setNextId = _useState4[1];
  var _useState5 = (0,react.useState)(false),
    _useState6 = useCanvasState_slicedToArray(_useState5, 2),
    isSaving = _useState6[0],
    setIsSaving = _useState6[1];

  // États de chargement granulaires pour meilleure UX
  var _useState7 = (0,react.useState)({
      saving: false,
      loading: false,
      duplicating: false,
      deleting: false
    }),
    _useState8 = useCanvasState_slicedToArray(_useState7, 2),
    loadingStates = _useState8[0],
    setLoadingStates = _useState8[1];
  var history = useHistory();
  var selection = useSelection({
    onSelectionChange: (0,react.useCallback)(function (selectedIds) {
      // Callback pour les changements de sélection
    }, []),
    multiSelect: (_globalSettings$multi = globalSettings === null || globalSettings === void 0 ? void 0 : globalSettings.multiSelect) !== null && _globalSettings$multi !== void 0 ? _globalSettings$multi : true
  });

  // Synchronisation parfaite des refs avec useLatest
  var historyRef = useLatest(history);
  var selectionRef = useLatest(selection);
  var clipboard = useClipboard({
    onPaste: (0,react.useCallback)(function (data) {
      if (data.type === 'elements') {
        var pastedElements = data.elements.map(function (element) {
          return useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), {}, {
            id: "element_".concat(nextId + data.elements.indexOf(element)),
            x: element.x + 20,
            // Offset pour éviter la superposition
            y: element.y + 20
          });
        });
        setElements(function (prev) {
          return [].concat(useCanvasState_toConsumableArray(prev), useCanvasState_toConsumableArray(pastedElements));
        });
        setNextId(function (prev) {
          return prev + pastedElements.length;
        });
        selection.selectAll(pastedElements.map(function (el) {
          return el.id;
        }));
      }
    }, [nextId, selection])
  });
  var zoom = useZoom({
    initialZoom: globalSettings ? parseFloat(globalSettings.defaultZoom) / 100 : 1,
    minZoom: globalSettings ? globalSettings.minZoom / 100 : 0.25,
    maxZoom: globalSettings ? globalSettings.maxZoom / 100 : 3,
    zoomStep: globalSettings ? globalSettings.zoomStep / 100 : 0.1
  });
  var contextMenu = useContextMenu();

  // Fonction updateElement définie après history
  var updateElement = (0,react.useCallback)(function (elementId, updates) {
    setElements(function (prev) {
      var newElements = prev.map(function (element) {
        if (element.id === elementId) {
          var updatedElement = useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), updates);
          // Pour les dividers, forcer la hauteur à 16px
          if (element.type === 'divider') {
            updatedElement.height = 16;
          }
          // Pour les lignes, forcer la hauteur à 12px
          if (element.type === 'line') {
            updatedElement.height = 12;
          }
          return updatedElement;
        }
        return element;
      });
      return newElements;
    });
  }, []); // Retirer les dépendances pour éviter les re-renders inutiles

  // Effet séparé pour l'historique - optimisation des performances
  (0,react.useEffect)(function () {
    if (elements.length > 0) {
      try {
        if (historyRef.current && typeof historyRef.current.addToHistory === 'function') {
          historyRef.current.addToHistory({
            elements: elements,
            nextId: nextId
          });
        }
      } catch (error) {
        // Continuer l'exécution malgré l'erreur d'historique
      }
    }
  }, [elements, nextId]);

  // Validation des données d'entrée (initialElements)
  var validateInitialElements = (0,react.useCallback)(function (elements) {
    if (!Array.isArray(elements)) {
      return [];
    }
    return elements.map(function (element) {
      if (!element.id || !element.type) {
        return null;
      }
      return element;
    }).filter(Boolean);
  }, []);

  // Calculer le prochain ID basé sur les éléments initiaux validés
  (0,react.useEffect)(function () {
    var validatedElements = validateInitialElements(initialElements);
    setElements(validatedElements);
    if (validatedElements && validatedElements.length > 0) {
      var maxId = Math.max.apply(Math, useCanvasState_toConsumableArray(validatedElements.map(function (el) {
        var _el$id;
        var idParts = ((_el$id = el.id) === null || _el$id === void 0 ? void 0 : _el$id.split('_')) || [];
        return parseInt(idParts[1] || 0);
      })));
      setNextId(maxId + 1);
    } else {
      setNextId(1);
    }
  }, [initialElements, validateInitialElements]);
  var dragAndDrop = useDragAndDrop({
    onElementMove: (0,react.useCallback)(function (elementId, position) {
      updateElement(elementId, position);
    }, [updateElement]),
    onElementDrop: (0,react.useCallback)(function (elementId, position) {
      updateElement(elementId, position);
      history.addToHistory({
        elements: elements.map(function (el) {
          return el.id === elementId ? useCanvasState_objectSpread(useCanvasState_objectSpread({}, el), position) : el;
        }),
        nextId: nextId
      });
    }, [updateElement, history, elements, nextId])
  });

  // Fonction utilitaire pour nettoyer les éléments avant sauvegarde (éviter les références DOM)
  var cleanElementsForHistory = (0,react.useCallback)(function (elementsToClean) {
    return elementsToClean.map(function (element) {
      var cleaned = useCanvasState_objectSpread({}, element);
      // Supprimer les propriétés non sérialisables qui pourraient contenir des références DOM
      var nonSerializableProps = ['domElement', 'ref', 'eventListeners', 'component', 'render'];
      nonSerializableProps.forEach(function (prop) {
        delete cleaned[prop];
      });
      return cleaned;
    });
  }, []);

  // Sauvegarder l'état dans l'historique à chaque changement
  (0,react.useEffect)(function () {
    if (elements.length > 0 || history.historySize === 0) {
      var cleanedElements = cleanElementsForHistory(elements);
      history.addToHistory({
        elements: cleanedElements,
        nextId: nextId
      });
    }
  }, [elements, nextId, history, cleanElementsForHistory]);

  // Correction automatique des éléments spéciaux existants
  (0,react.useEffect)(function () {
    var specialElements = ['product_table', 'customer_info', 'company_logo', 'company_info', 'order_number', 'document_type', 'progress-bar'];
    var needsCorrection = elements.some(function (element) {
      return specialElements.includes(element.type) && element.backgroundColor !== 'transparent';
    });
    if (needsCorrection) {
      setElements(function (prevElements) {
        return prevElements.map(function (element) {
          if (specialElements.includes(element.type) && element.backgroundColor !== 'transparent') {
            return useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), {}, {
              backgroundColor: 'transparent'
            });
          }
          return element;
        });
      });
    }
  }, []); // Uniquement au montage du composant

  var addElement = (0,react.useCallback)(function (elementType) {
    var properties = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    // Utiliser les refs pour accéder aux valeurs actuelles
    var currentHistory = historyRef.current;
    var currentSelection = selectionRef.current;

    // Vérifications de sécurité
    if (!currentSelection || !currentHistory) {
      return;
    }
    if (typeof currentSelection.selectElement !== 'function') {
      return;
    }
    if (typeof currentHistory.addToHistory !== 'function') {
      return;
    }

    // Propriétés par défaut complètes et synchronisées
    var defaultProps = {
      // Position et dimensions
      x: 50,
      y: 50,
      width: 100,
      height: 50,
      // Apparence de base
      backgroundColor: 'transparent',
      borderColor: 'transparent',
      borderWidth: 0,
      borderStyle: 'solid',
      borderRadius: 0,
      // Typographie
      color: '#1e293b',
      fontFamily: 'Inter, sans-serif',
      fontSize: 14,
      fontWeight: 'normal',
      fontStyle: 'normal',
      textAlign: 'left',
      textDecoration: 'none',
      // Contenu
      text: 'Texte',
      // Propriétés avancées
      opacity: 100,
      rotation: 0,
      scale: 100,
      visible: true,
      // Images et médias
      src: '',
      alt: '',
      objectFit: 'cover',
      brightness: 100,
      contrast: 100,
      saturate: 100,
      // Effets
      shadow: false,
      shadowColor: '#000000',
      shadowOffsetX: 2,
      shadowOffsetY: 2,
      // Propriétés spécifiques aux tableaux
      showHeaders: true,
      showBorders: true,
      dataSource: 'order_items',
      showSubtotal: false,
      showShipping: true,
      showTaxes: true,
      showDiscount: false,
      showTotal: false,
      // Propriétés de barre de progression
      progressColor: '#3b82f6',
      progressValue: 75,
      // Propriétés de code et lignes
      lineColor: '#64748b',
      lineWidth: 2,
      // Propriétés de document
      documentType: 'invoice',
      imageUrl: '',
      // Propriétés de mise en page
      spacing: 8,
      layout: 'vertical',
      alignment: 'left',
      fit: 'contain'
    };
    var newElement = useCanvasState_objectSpread(useCanvasState_objectSpread({
      id: "element_".concat(nextId),
      type: elementType
    }, defaultProps), properties);

    // Ajustements spécifiques selon le type d'élément
    if (elementType === 'line') {
      newElement.height = 12; // Hauteur fixe pour les lignes
      newElement.width = 200; // Largeur par défaut pour les lignes
    } else if (elementType === 'divider') {
      newElement.height = 16; // Hauteur fixe pour les séparateurs
      newElement.width = 200; // Largeur par défaut pour les séparateurs
    }
    setElements(function (prev) {
      var newElements = [].concat(useCanvasState_toConsumableArray(prev), [newElement]);
      // Sauvegarder dans l'historique
      try {
        if (currentHistory && typeof currentHistory.addToHistory === 'function') {
          currentHistory.addToHistory({
            elements: newElements,
            nextId: nextId + 1
          });
        }
      } catch (error) {}
      return newElements;
    });
    setNextId(function (prev) {
      return prev + 1;
    });
    try {
      if (currentSelection && typeof currentSelection.selectElement === 'function') {
        currentSelection.selectElement(newElement.id);
      }
    } catch (error) {}
  }, [nextId]); // Retirer selection et history des dépendances

  var deleteElement = (0,react.useCallback)(function (elementId) {
    setElements(function (prev) {
      var newElements = prev.filter(function (element) {
        return element.id !== elementId;
      });
      // Sauvegarder dans l'historique
      history.addToHistory({
        elements: newElements,
        nextId: nextId
      });
      return newElements;
    });
    selection.clearSelection();
  }, [selection, history, nextId]);
  var deleteSelectedElements = (0,react.useCallback)(function () {
    var elementsToDelete = selection.deleteSelected();
    setElements(function (prev) {
      var newElements = prev.filter(function (element) {
        return !elementsToDelete.includes(element.id);
      });
      // Sauvegarder dans l'historique
      history.addToHistory({
        elements: newElements,
        nextId: nextId
      });
      return newElements;
    });
    selection.clearSelection();
  }, [selection, history, nextId]);
  var duplicateElement = (0,react.useCallback)(function (elementId) {
    var element = elements.find(function (el) {
      return el.id === elementId;
    });
    if (element) {
      var duplicatedElement = useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), {}, {
        id: "element_".concat(nextId),
        x: element.x + 20,
        y: element.y + 20
      });
      setElements(function (prev) {
        var newElements = [].concat(useCanvasState_toConsumableArray(prev), [duplicatedElement]);
        // Sauvegarder dans l'historique
        history.addToHistory({
          elements: newElements,
          nextId: nextId + 1
        });
        return newElements;
      });
      setNextId(function (prev) {
        return prev + 1;
      });
      selection.selectElement(duplicatedElement.id);
    }
  }, [elements, nextId, selection, history]);
  var duplicateSelectedElements = (0,react.useCallback)(function () {
    var elementsToDuplicate = selection.duplicateSelected();
    var duplicatedElements = [];
    elementsToDuplicate.forEach(function (elementId) {
      var element = elements.find(function (el) {
        return el.id === elementId;
      });
      if (element) {
        var duplicatedElement = useCanvasState_objectSpread(useCanvasState_objectSpread({}, element), {}, {
          id: "element_".concat(nextId + duplicatedElements.length),
          x: element.x + 20,
          y: element.y + 20
        });
        duplicatedElements.push(duplicatedElement);
      }
    });
    if (duplicatedElements.length > 0) {
      setElements(function (prev) {
        var newElements = [].concat(useCanvasState_toConsumableArray(prev), duplicatedElements);
        // Sauvegarder dans l'historique
        history.addToHistory({
          elements: newElements,
          nextId: nextId + duplicatedElements.length
        });
        return newElements;
      });
      setNextId(function (prev) {
        return prev + duplicatedElements.length;
      });
      selection.selectAll(duplicatedElements.map(function (el) {
        return el.id;
      }));
    }
  }, [elements, nextId, selection, history]);
  var copySelectedElements = (0,react.useCallback)(function () {
    var selectedIds = selection.selectedElements;
    var selectedElementsData = elements.filter(function (el) {
      return selectedIds.includes(el.id);
    });
    if (selectedElementsData.length > 0) {
      clipboard.copy({
        type: 'elements',
        elements: selectedElementsData
      });
    }
  }, [elements, selection, clipboard]);
  var pasteElements = (0,react.useCallback)(function () {
    clipboard.paste();
  }, [clipboard]);
  var undo = (0,react.useCallback)(function () {
    var previousState = history.undo();
    if (previousState) {
      setElements(previousState.elements);
      setNextId(previousState.nextId);
      selection.clearSelection();
    }
  }, [history, selection]);
  var redo = (0,react.useCallback)(function () {
    var nextState = history.redo();
    if (nextState) {
      setElements(nextState.elements);
      setNextId(nextState.nextId);
      selection.clearSelection();
    }
  }, [history, selection]);
  var saveTemplate = (0,react.useCallback)(/*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
    var isExistingTemplate, checkToastrAvailability, toastrAvailable, _window$pdfBuilderDat, _window$pdfBuilderDat2, _window$pdfBuilderDat3, _window$pdfBuilderAja, _window$pdfBuilderDat4, _window$pdfBuilderAja2, cleanElementForSerialization, cleanedElements, templateData, jsonString, testParse, _iterator, _step, element, formData, response, result, _result$data, errorMessage, _t, _t2, _t3;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          if (!loadingStates.saving) {
            _context.n = 1;
            break;
          }
          return _context.a(2);
        case 1:
          // console.log('🚀 PDF Builder SAVE - Démarrage de la sauvegarde');
          setLoadingStates(function (prev) {
            return useCanvasState_objectSpread(useCanvasState_objectSpread({}, prev), {}, {
              saving: true
            });
          });

          // Déterminer si c'est un template existant
          isExistingTemplate = templateId && templateId !== '0' && templateId !== 0; // Fonction pour vérifier la disponibilité de Toastr avec retry
          checkToastrAvailability = function checkToastrAvailability() {
            return Promise.resolve(true); // Toastr is now always available (real or fallback)
          };
          _context.n = 2;
          return checkToastrAvailability();
        case 2:
          toastrAvailable = _context.v;
          _context.p = 3;
          // Fonction pour nettoyer et valider les données avant sérialisation
          cleanElementForSerialization = function cleanElementForSerialization(element) {
            // Liste des propriétés à exclure car elles ne sont pas sérialisables
            var excludedProps = ['domElement', 'eventListeners', 'ref', 'onClick', 'onMouseDown', 'onMouseUp', 'onMouseMove', 'onContextMenu', 'onDoubleClick', 'onDragStart', 'onDragEnd', 'onResize', 'component', 'render', 'props', 'state', 'context', 'refs', '_reactInternalInstance', '_reactInternals', '$$typeof', 'constructor', 'prototype',
            // Propriétés React spécifiques
            '_owner', '_store', 'key', 'ref', '_self', '_source'];
            var cleaned = {};
            for (var _i = 0, _Object$entries = Object.entries(element); _i < _Object$entries.length; _i++) {
              var _Object$entries$_i = useCanvasState_slicedToArray(_Object$entries[_i], 2),
                key = _Object$entries$_i[0],
                value = _Object$entries$_i[1];
              // Exclure les propriétés problématiques
              if (excludedProps.includes(key)) {
                continue;
              }

              // Exclure les propriétés qui commencent par underscore (privées React)
              if (key.startsWith('_')) {
                continue;
              }

              // Validation et correction selon le type de propriété
              var validatedValue = value;

              // Propriétés numériques
              var numericProps = ['x', 'y', 'width', 'height', 'fontSize', 'opacity', 'lineHeight', 'letterSpacing', 'zIndex', 'borderWidth', 'borderRadius', 'rotation', 'padding', 'scale', 'shadowOffsetX', 'shadowOffsetY', 'brightness', 'contrast', 'saturate', 'progressValue', 'lineWidth', 'spacing'];
              if (numericProps.includes(key)) {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  // Valeurs par défaut
                  var defaults = {
                    x: 0,
                    y: 0,
                    width: 100,
                    height: 50,
                    fontSize: 14,
                    opacity: 1,
                    lineHeight: 1.2,
                    letterSpacing: 0,
                    zIndex: 0,
                    borderWidth: 0,
                    borderRadius: 0,
                    rotation: 0,
                    padding: 0
                  };
                  validatedValue = defaults[key] || 0;
                }
              }

              // Propriétés de couleur
              var colorProps = ['color', 'backgroundColor', 'borderColor', 'shadowColor', 'progressColor', 'lineColor'];
              if (colorProps.includes(key)) {
                if (value && value !== 'transparent') {
                  // Normaliser les couleurs
                  if (!/^#[0-9A-Fa-f]{3,6}$/i.test(value)) {
                    // Couleurs nommées communes
                    var namedColors = {
                      'black': '#000000',
                      'white': '#ffffff',
                      'red': '#ff0000',
                      'green': '#008000',
                      'blue': '#0000ff',
                      'gray': '#808080',
                      'grey': '#808080',
                      'transparent': 'transparent'
                    };
                    validatedValue = namedColors[value.toLowerCase()] || '#000000';
                  }
                }
              }

              // Propriétés de style de texte
              if (key === 'fontWeight') {
                var validWeights = ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'];
                if (!validWeights.includes(value)) {
                  validatedValue = 'normal';
                }
              }
              if (key === 'textAlign') {
                var validAligns = ['left', 'center', 'right', 'justify'];
                if (!validAligns.includes(value)) {
                  validatedValue = 'left';
                }
              }
              if (key === 'textDecoration') {
                var validDecorations = ['none', 'underline', 'overline', 'line-through'];
                if (!validDecorations.includes(value)) {
                  validatedValue = 'none';
                }
              }
              if (key === 'textTransform') {
                var validTransforms = ['none', 'capitalize', 'uppercase', 'lowercase'];
                if (!validTransforms.includes(value)) {
                  validatedValue = 'none';
                }
              }
              if (key === 'borderStyle') {
                var validStyles = ['solid', 'dashed', 'dotted', 'double', 'none'];
                if (!validStyles.includes(value)) {
                  validatedValue = 'solid';
                }
              }

              // Propriétés de texte et contenu
              if (key === 'text' || key === 'content') {
                if (typeof value !== 'string') {
                  validatedValue = '';
                }
              }

              // Propriétés de police
              if (key === 'fontFamily') {
                if (typeof value !== 'string' || value.trim() === '') {
                  validatedValue = 'Inter, sans-serif';
                }
              }
              if (key === 'fontStyle') {
                var _validStyles = ['normal', 'italic', 'oblique'];
                if (!_validStyles.includes(value)) {
                  validatedValue = 'normal';
                }
              }

              // Propriétés de visibilité et transformation
              if (key === 'visible') {
                if (typeof value !== 'boolean') {
                  validatedValue = true;
                }
              }
              if (key === 'scale') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 100;
                }
                // Limiter la scale entre 10 et 500
                validatedValue = Math.max(10, Math.min(500, validatedValue));
              }

              // Propriétés d'ombre
              if (key === 'shadow') {
                if (typeof value !== 'boolean') {
                  validatedValue = false;
                }
              }
              if (key === 'shadowColor') {
                if (value && value !== 'transparent') {
                  if (!/^#[0-9A-Fa-f]{3,6}$/i.test(value)) {
                    var _namedColors = {
                      'black': '#000000',
                      'white': '#ffffff',
                      'red': '#ff0000',
                      'green': '#008000',
                      'blue': '#0000ff',
                      'gray': '#808080',
                      'grey': '#808080',
                      'transparent': 'transparent'
                    };
                    validatedValue = _namedColors[value.toLowerCase()] || '#000000';
                  }
                }
              }
              var shadowOffsetProps = ['shadowOffsetX', 'shadowOffsetY'];
              if (shadowOffsetProps.includes(key)) {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 2;
                }
              }

              // Propriétés d'image et médias
              if (key === 'brightness' || key === 'contrast' || key === 'saturate') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 100;
                }
                // Limiter entre 0 et 200
                validatedValue = Math.max(0, Math.min(200, validatedValue));
              }
              if (key === 'objectFit') {
                var validFits = ['fill', 'contain', 'cover', 'none', 'scale-down'];
                if (!validFits.includes(value)) {
                  validatedValue = 'cover';
                }
              }

              // Propriétés de tableau
              var booleanTableProps = ['showHeaders', 'showBorders', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'];
              if (booleanTableProps.includes(key)) {
                if (typeof value !== 'boolean') {
                  validatedValue = false;
                }
              }
              if (key === 'dataSource') {
                var validSources = ['order_items', 'cart_items', 'custom'];
                if (!validSources.includes(value)) {
                  validatedValue = 'order_items';
                }
              }

              // Propriétés de barre de progression
              if (key === 'progressValue') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 0;
                }
                validatedValue = Math.max(0, Math.min(100, validatedValue));
              }

              // Propriétés de ligne/code
              if (key === 'lineWidth') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 2;
                }
                validatedValue = Math.max(1, Math.min(10, validatedValue));
              }

              // Propriétés de mise en page
              if (key === 'spacing') {
                if (typeof value === 'string' && value !== '' && !isNaN(value)) {
                  validatedValue = parseFloat(value);
                } else if (typeof value !== 'number') {
                  validatedValue = 8;
                }
              }
              if (key === 'layout') {
                var validLayouts = ['vertical', 'horizontal', 'grid'];
                if (!validLayouts.includes(value)) {
                  validatedValue = 'vertical';
                }
              }
              if (key === 'alignment') {
                var validAlignments = ['left', 'center', 'right', 'justify'];
                if (!validAlignments.includes(value)) {
                  validatedValue = 'left';
                }
              }
              if (key === 'fit') {
                var _validFits = ['contain', 'cover', 'fill', 'none'];
                if (!_validFits.includes(value)) {
                  validatedValue = 'contain';
                }
              }

              // Propriétés de document
              if (key === 'documentType') {
                var validTypes = ['invoice', 'quote', 'receipt', 'order'];
                if (!validTypes.includes(value)) {
                  validatedValue = 'invoice';
                }
              }

              // Propriétés d'objet complexes
              if (key === 'columns') {
                if (useCanvasState_typeof(value) === 'object' && value !== null) {
                  var _value$image, _value$name, _value$sku, _value$quantity, _value$price, _value$total;
                  validatedValue = {
                    image: (_value$image = value.image) !== null && _value$image !== void 0 ? _value$image : true,
                    name: (_value$name = value.name) !== null && _value$name !== void 0 ? _value$name : true,
                    sku: (_value$sku = value.sku) !== null && _value$sku !== void 0 ? _value$sku : false,
                    quantity: (_value$quantity = value.quantity) !== null && _value$quantity !== void 0 ? _value$quantity : true,
                    price: (_value$price = value.price) !== null && _value$price !== void 0 ? _value$price : true,
                    total: (_value$total = value.total) !== null && _value$total !== void 0 ? _value$total : true
                  };
                } else {
                  validatedValue = {
                    image: true,
                    name: true,
                    sku: false,
                    quantity: true,
                    price: true,
                    total: true
                  };
                }
              }

              // Vérifier le type de valeur
              if (validatedValue === null || validatedValue === undefined) {
                // Ne pas inclure les propriétés null/undefined
                continue; // Skip this property but continue cleaning the rest
              } else if (typeof validatedValue === 'string' || typeof validatedValue === 'number' || typeof validatedValue === 'boolean') {
                cleaned[key] = validatedValue;
              } else if (Array.isArray(validatedValue)) {
                // Pour les tableaux, nettoyer chaque élément de manière très stricte
                try {
                  var cleanedArray = validatedValue.filter(function (item) {
                    return item !== null && item !== undefined;
                  }) // Filtrer les valeurs null/undefined
                  .map(function (item) {
                    if (useCanvasState_typeof(item) === 'object' && item !== null) {
                      // Pour les objets dans les tableaux, seulement garder les propriétés primitives
                      var cleanedItem = {};
                      for (var _i2 = 0, _Object$entries2 = Object.entries(item); _i2 < _Object$entries2.length; _i2++) {
                        var _Object$entries2$_i = useCanvasState_slicedToArray(_Object$entries2[_i2], 2),
                          itemKey = _Object$entries2$_i[0],
                          itemValue = _Object$entries2$_i[1];
                        if (typeof itemValue === 'string' || typeof itemValue === 'number' || typeof itemValue === 'boolean') {
                          cleanedItem[itemKey] = itemValue;
                        }
                      }
                      return cleanedItem;
                    }
                    return typeof item === 'string' || typeof item === 'number' || typeof item === 'boolean' ? item : null;
                  }).filter(function (item) {
                    return item !== null;
                  }); // Retirer les éléments null

                  // Test final de sérialisation du tableau complet
                  JSON.stringify(cleanedArray);
                  cleaned[key] = cleanedArray;
                } catch (e) {
                  console.warn("Impossible de s\xE9rialiser le tableau pour ".concat(key, ", utilisation tableau vide:"), e);
                  cleaned[key] = [];
                }
              } else if (useCanvasState_typeof(validatedValue) === 'object') {
                // Pour les objets, nettoyer récursivement mais de manière très stricte
                try {
                  var cleanedObj = {};
                  for (var _i3 = 0, _Object$entries3 = Object.entries(validatedValue); _i3 < _Object$entries3.length; _i3++) {
                    var _Object$entries3$_i = useCanvasState_slicedToArray(_Object$entries3[_i3], 2),
                      objKey = _Object$entries3$_i[0],
                      objValue = _Object$entries3$_i[1];
                    // Exclure les propriétés problématiques des objets imbriqués
                    if (objKey.startsWith('_') || excludedProps.includes(objKey)) {
                      continue;
                    }
                    if (typeof objValue === 'string' || typeof objValue === 'number' || typeof objValue === 'boolean') {
                      cleanedObj[objKey] = objValue;
                    }
                  }
                  // Test de sérialisation de l'objet nettoyé
                  JSON.stringify(cleanedObj);
                  cleaned[key] = cleanedObj;
                } catch (e) {
                  console.warn("Impossible de s\xE9rialiser l'objet pour ".concat(key, ", utilisation objet vide:"), e);
                  cleaned[key] = {};
                }
              } else {
                // Pour les autres types (functions, symbols, etc.), ignorer silencieusement
                console.warn("Type non support\xE9 ignor\xE9 pour ".concat(key, ": ").concat(useCanvasState_typeof(validatedValue)));
              }
            }
            return cleaned;
          }; // Nettoyer tous les éléments avec protection contre les erreurs
          cleanedElements = [];
          try {
            // console.log('🧹 PDF Builder SAVE - Nettoyage des éléments commencé, éléments bruts:', elements.length);

            // Log détaillé de chaque élément avant filtrage
            elements.forEach(function (element, index) {
              var hasElement = element && useCanvasState_typeof(element) === 'object';
              var hasId = element && element.id;
              var hasType = element && element.type;
              // console.log(`📋 Élément ${index} - valide: ${hasElement}, id: ${hasId ? element.id : 'MISSING'}, type: ${hasType ? element.type : 'MISSING'}`);
              if (!hasElement || !hasId || !hasType) {
                // console.log(`❌ Élément ${index} sera filtré:`, element);
              }
            });
            cleanedElements = elements.filter(function (element) {
              return element && useCanvasState_typeof(element) === 'object' && element.id && element.type;
            }) // Filtrer les éléments invalides
            .map(cleanElementForSerialization).filter(function (element) {
              return element && element.id && element.type;
            }); // Filtrer après nettoyage

            // console.log('✅ PDF Builder SAVE - Nettoyage terminé, éléments nettoyés:', cleanedElements.length);

            // Test de sérialisation de tous les éléments
            JSON.stringify(cleanedElements);
            // console.log('✅ PDF Builder SAVE - Test de sérialisation réussi');
          } catch (e) {
            console.error('❌ PDF Builder SAVE - Erreur lors du nettoyage des éléments:', e);
            console.error('❌ PDF Builder SAVE - Éléments originaux qui ont causé l\'erreur:', elements);
            // En cas d'erreur, utiliser un tableau vide pour éviter les crashes
            cleanedElements = [];
          }

          // Log détaillé des propriétés de chaque élément (mode développement uniquement)
          if (isDevelopment) {
            elements.forEach(function (element, index) {
              // console.log(`Élément ${index} (${element.type}) propriétés avant nettoyage:`, Object.keys(element));
              if (element.type === 'product_table') {
                // console.log(`Tableau ${index} - paramètres:`, {
                //   showHeaders: element.showHeaders,
                //   showBorders: element.showBorders,
                //   columns: element.columns,
                //   tableStyle: element.tableStyle,
                //   showSubtotal: element.showSubtotal,
                //   showShipping: element.showShipping,
                //   showTaxes: element.showTaxes,
                //   showDiscount: element.showDiscount,
                //   showTotal: element.showTotal
                // });
              }
            });

            // console.log('Éléments nettoyés pour sauvegarde:', cleanedElements);
          }
          templateData = {
            elements: cleanedElements,
            canvasWidth: canvasWidth,
            canvasHeight: canvasHeight,
            version: '1.0'
          }; // Log des données en mode développement uniquement
          if (isDevelopment) {
            // console.log('Données template à sauvegarder:', templateData);
          }

          // Valider le JSON avant envoi avec protection renforcée
          _context.p = 4;
          jsonString = JSON.stringify(templateData);

          // Tester le parsing pour valider
          testParse = JSON.parse(jsonString); // Vérifier que les données essentielles sont présentes
          if (!(!testParse.elements || !Array.isArray(testParse.elements))) {
            _context.n = 5;
            break;
          }
          throw new Error('Structure de données invalide: éléments manquants ou incorrects');
        case 5:
          // Vérifier que chaque élément a au moins un ID et un type
          _iterator = useCanvasState_createForOfIteratorHelper(testParse.elements);
          _context.p = 6;
          _iterator.s();
        case 7:
          if ((_step = _iterator.n()).done) {
            _context.n = 9;
            break;
          }
          element = _step.value;
          if (!(!element.id || !element.type)) {
            _context.n = 8;
            break;
          }
          throw new Error("\xC9l\xE9ment invalide d\xE9tect\xE9: ID ou type manquant pour ".concat(JSON.stringify(element)));
        case 8:
          _context.n = 7;
          break;
        case 9:
          _context.n = 11;
          break;
        case 10:
          _context.p = 10;
          _t = _context.v;
          _iterator.e(_t);
        case 11:
          _context.p = 11;
          _iterator.f();
          return _context.f(11);
        case 12:
          _context.n = 14;
          break;
        case 13:
          _context.p = 13;
          _t2 = _context.v;
          console.error('Erreur de validation JSON côté client:', _t2);
          console.error('Données templateData qui ont causé l\'erreur:', templateData);
          throw new Error('Données JSON invalides côté client: ' + _t2.message);
        case 14:
          // Sauvegarde directe via AJAX avec FormData pour les données volumineuses
          // console.log('📤 PDF Builder SAVE - Préparation des données pour envoi au serveur');
          formData = new FormData();
          formData.append('action', 'pdf_builder_pro_save_template');
          formData.append('template_data', jsonString);
          formData.append('template_name', ((_window$pdfBuilderDat = window.pdfBuilderData) === null || _window$pdfBuilderDat === void 0 ? void 0 : _window$pdfBuilderDat.templateName) || "Template ".concat(((_window$pdfBuilderDat2 = window.pdfBuilderData) === null || _window$pdfBuilderDat2 === void 0 ? void 0 : _window$pdfBuilderDat2.templateId) || 'New'));
          formData.append('template_id', ((_window$pdfBuilderDat3 = window.pdfBuilderData) === null || _window$pdfBuilderDat3 === void 0 ? void 0 : _window$pdfBuilderDat3.templateId) || '0');
          formData.append('nonce', ((_window$pdfBuilderAja = window.pdfBuilderAjax) === null || _window$pdfBuilderAja === void 0 ? void 0 : _window$pdfBuilderAja.nonce) || ((_window$pdfBuilderDat4 = window.pdfBuilderData) === null || _window$pdfBuilderDat4 === void 0 ? void 0 : _window$pdfBuilderDat4.nonce) || '');

          // console.log('📤 PDF Builder SAVE - Données FormData préparées:', {
          //   action: 'pdf_builder_pro_save_template',
          //   templateName: window.pdfBuilderData?.templateName || `Template ${window.pdfBuilderData?.templateId || 'New'}`,
          //   templateId: window.pdfBuilderData?.templateId || '0',
          //   nonce: window.pdfBuilderAjax?.nonce || window.pdfBuilderData?.nonce || '',
          //   jsonLength: jsonString.length
          // });

          // console.log('🌐 PDF Builder SAVE - Envoi de la requête AJAX...');
          _context.n = 15;
          return fetch(((_window$pdfBuilderAja2 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja2 === void 0 ? void 0 : _window$pdfBuilderAja2.ajaxurl) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
          });
        case 15:
          response = _context.v;
          _context.n = 16;
          return response.json();
        case 16:
          result = _context.v;
          if (result.success) {
            _context.n = 17;
            break;
          }
          throw new Error(((_result$data = result.data) === null || _result$data === void 0 ? void 0 : _result$data.message) || 'Erreur lors de la sauvegarde');
        case 17:
          // Notification de succès pour les templates existants
          if (isExistingTemplate) {
            if (toastrAvailable) {
              toastr.success('Modifications du canvas sauvegardées avec succès !');
            } else {
              alert('Modifications du canvas sauvegardées avec succès !');
            }
          }
          return _context.a(2, templateData);
        case 18:
          _context.p = 18;
          _t3 = _context.v;
          console.error('❌ PDF Builder SAVE - Erreur lors de la sauvegarde:', _t3);
          console.error('❌ PDF Builder SAVE - Détails de l\'erreur:', {
            message: _t3.message,
            stack: _t3.stack,
            name: _t3.name
          });

          // Notification d'erreur
          errorMessage = _t3.message || 'Erreur inconnue lors de la sauvegarde';
          if (toastrAvailable) {
            toastr.error("Erreur lors de la sauvegarde: ".concat(errorMessage));
          } else {
            alert("Erreur lors de la sauvegarde: ".concat(errorMessage));
          }
          throw _t3;
        case 19:
          _context.p = 19;
          // console.log('🏁 PDF Builder SAVE - Fin du processus de sauvegarde');
          setLoadingStates(function (prev) {
            return useCanvasState_objectSpread(useCanvasState_objectSpread({}, prev), {}, {
              saving: false
            });
          });
          return _context.f(19);
        case 20:
          return _context.a(2);
      }
    }, _callee, null, [[6, 10, 11, 12], [4, 13], [3, 18, 19, 20]]);
  })), [elements, canvasWidth, canvasHeight, isSaving, templateId]);
  var loadTemplate = (0,react.useCallback)(function (templateData) {
    if (templateData.elements) {
      setElements(templateData.elements);
      setNextId(templateData.nextId || Math.max.apply(Math, useCanvasState_toConsumableArray(templateData.elements.map(function (el) {
        return parseInt(el.id.split('_')[1]);
      }) || [0])) + 1);
      selection.clearSelection();
      history.clearHistory();
    }
  }, [selection, history]);
  var showContextMenu = (0,react.useCallback)(function (x, y) {
    var targetElementId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    var menuItems = [];
    if (targetElementId) {
      menuItems.push({
        label: 'Dupliquer',
        action: function action() {
          return duplicateElement(targetElementId);
        }
      }, {
        label: 'Supprimer',
        action: function action() {
          return deleteElement(targetElementId);
        }
      }, {
        type: 'separator'
      }, {
        label: 'Copier',
        action: copySelectedElements
      }, {
        label: 'Coller',
        action: pasteElements,
        disabled: !clipboard.hasData()
      });
    } else if (selection.selectedElements.length > 0) {
      menuItems.push({
        label: 'Dupliquer',
        action: duplicateSelectedElements
      }, {
        label: 'Supprimer',
        action: deleteSelectedElements
      }, {
        type: 'separator'
      }, {
        label: 'Copier',
        action: copySelectedElements
      }, {
        label: 'Coller',
        action: pasteElements,
        disabled: !clipboard.hasData()
      });
    } else {
      menuItems.push({
        label: 'Coller',
        action: pasteElements,
        disabled: !clipboard.hasData()
      });
    }
    contextMenu.showContextMenu(x, y, menuItems);
  }, [selection, contextMenu, duplicateElement, deleteElement, copySelectedElements, pasteElements, clipboard, duplicateSelectedElements, deleteSelectedElements]);
  var zoomToSelection = (0,react.useCallback)(function () {
    var selectedElementIds = selection.selectedElements;
    if (selectedElementIds.length === 0) return;
    var selectedElements = elements.filter(function (el) {
      return selectedElementIds.includes(el.id);
    });
    if (selectedElements.length === 0) return;

    // Obtenir les dimensions du conteneur (viewport du canvas)
    var containerElement = document.querySelector('.canvas-container');
    if (!containerElement) return;
    var containerRect = containerElement.getBoundingClientRect();
    var containerWidth = containerRect.width;
    var containerHeight = containerRect.height;
    zoom.zoomToSelection(selectedElements, canvasWidth, canvasHeight, containerWidth, containerHeight);
  }, [selection.selectedElements, elements, zoom, canvasWidth, canvasHeight]);
  return (0,react.useMemo)(function () {
    return {
      // État
      elements: elements,
      canvasWidth: canvasWidth,
      canvasHeight: canvasHeight,
      // Hooks intégrés
      selection: selection,
      zoom: zoom,
      contextMenu: contextMenu,
      dragAndDrop: dragAndDrop,
      // Actions sur les éléments
      addElement: addElement,
      updateElement: updateElement,
      deleteElement: deleteElement,
      deleteSelectedElements: deleteSelectedElements,
      duplicateElement: duplicateElement,
      duplicateSelectedElements: duplicateSelectedElements,
      // Presse-papiers
      copySelectedElements: copySelectedElements,
      pasteElements: pasteElements,
      // Historique
      undo: undo,
      redo: redo,
      canUndo: history.canUndo(),
      canRedo: history.canRedo(),
      history: history,
      // Zoom
      zoomToSelection: zoomToSelection,
      // Sauvegarde
      saveTemplate: saveTemplate,
      // États de chargement pour feedback visuel
      loadingStates: loadingStates,
      isSaving: loadingStates.saving,
      // Alias pour compatibilité

      // Menu contextuel
      showContextMenu: showContextMenu,
      // Utilitaires
      getAllElements: (0,react.useCallback)(function () {
        return elements;
      }, [elements]),
      getElementById: (0,react.useCallback)(function (id) {
        return elements.find(function (el) {
          return el.id === id;
        });
      }, [elements])
    };
  }, [elements, canvasWidth, canvasHeight, selection, zoom, contextMenu, dragAndDrop, addElement, updateElement, deleteElement, deleteSelectedElements, duplicateElement, duplicateSelectedElements, copySelectedElements, pasteElements, undo, redo, history, showContextMenu, zoomToSelection, saveTemplate]);

  // Nettoyage mémoire au démontage
  // removed by dead control flow


  // Synchronisation temps réel entre onglets via localStorage
  // removed by dead control flow

  // removed by dead control flow

};
;// ./src/hooks/useKeyboardShortcuts.js

var useKeyboardShortcuts = function useKeyboardShortcuts(_ref) {
  var onDelete = _ref.onDelete,
    onCopy = _ref.onCopy,
    onPaste = _ref.onPaste,
    onUndo = _ref.onUndo,
    onRedo = _ref.onRedo,
    onSave = _ref.onSave,
    onZoomIn = _ref.onZoomIn,
    onZoomOut = _ref.onZoomOut,
    onSelectAll = _ref.onSelectAll,
    onDeselectAll = _ref.onDeselectAll,
    onToolSelect = _ref.onToolSelect,
    onToggleGrid = _ref.onToggleGrid,
    onTogglePreview = _ref.onTogglePreview;
  (0,react.useEffect)(function () {
    var handleKeyDown = function handleKeyDown(e) {
      // Ignorer si on est dans un champ de saisie
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.contentEditable === 'true') {
        return;
      }
      var isCtrl = e.ctrlKey || e.metaKey;
      switch (e.key.toLowerCase()) {
        case 'delete':
        case 'backspace':
          if (onDelete) {
            e.preventDefault();
            onDelete();
          }
          break;
        case 'c':
          if (isCtrl && onCopy) {
            e.preventDefault();
            onCopy();
          }
          break;
        case 'v':
          if (isCtrl && onPaste) {
            e.preventDefault();
            onPaste();
          }
          break;
        case 'z':
          if (isCtrl) {
            e.preventDefault();
            if (e.shiftKey && onRedo) {
              onRedo();
            } else if (onUndo) {
              onUndo();
            }
          }
          break;
        case 'y':
          if (isCtrl && onRedo) {
            e.preventDefault();
            onRedo();
          }
          break;
        case 's':
          if (isCtrl && onSave) {
            e.preventDefault();
            onSave();
          }
          break;
        case '+':
        case '=':
          if (isCtrl && onZoomIn) {
            e.preventDefault();
            onZoomIn();
          }
          break;
        case '-':
          if (isCtrl && onZoomOut) {
            e.preventDefault();
            onZoomOut();
          }
          break;
        case 'a':
          if (isCtrl && onSelectAll) {
            e.preventDefault();
            onSelectAll();
          }
          break;
        case 'd':
          if (isCtrl && onDeselectAll) {
            e.preventDefault();
            onDeselectAll();
          }
          break;
        case 'g':
          if (isCtrl && onToggleGrid) {
            e.preventDefault();
            onToggleGrid();
          }
          break;
        case 'p':
          if (isCtrl && onTogglePreview) {
            e.preventDefault();
            onTogglePreview();
          }
          break;

        // Raccourcis pour les outils
        case 'v':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('select');
          }
          break;
        case 'r':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('rectangle');
          }
          break;
        case 't':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('text');
          }
          break;
        case 'i':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('image');
          }
          break;
        case 'l':
          if (!isCtrl && onToolSelect) {
            e.preventDefault();
            onToolSelect('line');
          }
          break;
        default:
          break;
      }
    };
    document.addEventListener('keydown', handleKeyDown);
    return function () {
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [onDelete, onCopy, onPaste, onUndo, onRedo, onSave, onZoomIn, onZoomOut, onSelectAll, onDeselectAll, onToolSelect, onToggleGrid, onTogglePreview]);
};
;// ./src/hooks/useGlobalSettings.js
function useGlobalSettings_typeof(o) { "@babel/helpers - typeof"; return useGlobalSettings_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, useGlobalSettings_typeof(o); }
function useGlobalSettings_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return useGlobalSettings_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (useGlobalSettings_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, useGlobalSettings_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, useGlobalSettings_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), useGlobalSettings_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", useGlobalSettings_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), useGlobalSettings_regeneratorDefine2(u), useGlobalSettings_regeneratorDefine2(u, o, "Generator"), useGlobalSettings_regeneratorDefine2(u, n, function () { return this; }), useGlobalSettings_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (useGlobalSettings_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function useGlobalSettings_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } useGlobalSettings_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { useGlobalSettings_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, useGlobalSettings_regeneratorDefine2(e, r, n, t); }
function useGlobalSettings_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function useGlobalSettings_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { useGlobalSettings_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { useGlobalSettings_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function useGlobalSettings_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function useGlobalSettings_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? useGlobalSettings_ownKeys(Object(t), !0).forEach(function (r) { useGlobalSettings_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : useGlobalSettings_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function useGlobalSettings_defineProperty(e, r, t) { return (r = useGlobalSettings_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function useGlobalSettings_toPropertyKey(t) { var i = useGlobalSettings_toPrimitive(t, "string"); return "symbol" == useGlobalSettings_typeof(i) ? i : i + ""; }
function useGlobalSettings_toPrimitive(t, r) { if ("object" != useGlobalSettings_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != useGlobalSettings_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function useGlobalSettings_slicedToArray(r, e) { return useGlobalSettings_arrayWithHoles(r) || useGlobalSettings_iterableToArrayLimit(r, e) || useGlobalSettings_unsupportedIterableToArray(r, e) || useGlobalSettings_nonIterableRest(); }
function useGlobalSettings_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useGlobalSettings_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useGlobalSettings_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useGlobalSettings_arrayLikeToArray(r, a) : void 0; } }
function useGlobalSettings_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useGlobalSettings_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useGlobalSettings_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var useGlobalSettings = function useGlobalSettings() {
  var _useState = (0,react.useState)({
      // Paramètres généraux du canvas
      defaultCanvasWidth: 210,
      defaultCanvasHeight: 297,
      defaultCanvasUnit: 'mm',
      defaultOrientation: 'portrait',
      canvasBackgroundColor: '#ffffff',
      canvasShowTransparency: false,
      containerBackgroundColor: '#f8f9fa',
      containerShowTransparency: false,
      // Marges de sécurité
      marginTop: 10,
      marginRight: 10,
      marginBottom: 10,
      marginLeft: 10,
      showMargins: true,
      // Paramètres de grille
      showGrid: true,
      gridSize: 10,
      gridColor: '#e0e0e0',
      gridOpacity: 30,
      // Aimantation
      snapToGrid: true,
      snapToElements: true,
      snapToMargins: true,
      snapTolerance: 5,
      // Lignes guides
      showGuides: true,
      lockGuides: false,
      // Paramètres de zoom et navigation
      defaultZoom: '100',
      minZoom: 10,
      maxZoom: 500,
      zoomStep: 25,
      panWithMouse: true,
      smoothZoom: true,
      showZoomIndicator: true,
      zoomWithWheel: false,
      zoomToSelection: true,
      // Paramètres de sélection et manipulation
      showResizeHandles: true,
      handleSize: 8,
      handleColor: '#007cba',
      enableRotation: true,
      rotationStep: 15,
      rotationSnap: true,
      multiSelect: true,
      selectAllShortcut: true,
      showSelectionBounds: true,
      copyPasteEnabled: true,
      duplicateOnDrag: false,
      // Paramètres d'export et qualité
      exportQuality: 'print',
      exportFormat: 'pdf',
      compressImages: true,
      imageQuality: 85,
      maxImageSize: 2048,
      includeMetadata: true,
      pdfAuthor: '',
      pdfSubject: '',
      autoCrop: false,
      embedFonts: true,
      optimizeForWeb: true,
      // Paramètres avancés
      enableHardwareAcceleration: true,
      limitFps: true,
      maxFps: 60,
      autoSaveEnabled: true,
      autoSaveInterval: 30,
      autoSaveVersions: 10,
      undoLevels: 50,
      redoLevels: 50,
      enableKeyboardShortcuts: true,
      debugMode: false,
      showFps: false,
      // Anciens paramètres (pour compatibilité)
      resizeHandleSize: 12,
      resizeHandleColor: '#007cba',
      resizeHandleBorderColor: '#ffffff',
      resizeHandleOffset: -6,
      selectionBorderWidth: 2,
      selectionBorderColor: 'var(--primary-color)',
      selectionShadowOpacity: 0.1,
      selectionBorderSpacing: 2,
      resizeZoneSize: 16,
      resizeZoneOpacity: 0.1,
      showResizeHandlesLegacy: true,
      showResizeZones: true,
      selectionAnimation: true,
      defaultTextColor: '#000000',
      defaultBackgroundColor: '#ffffff',
      defaultFontSize: 14,
      // Paramètres AJAX
      ajaxurl: window.ajaxurl || '',
      nonce: window.pdfBuilderNonce || ''
    }),
    _useState2 = useGlobalSettings_slicedToArray(_useState, 2),
    settings = _useState2[0],
    setSettings = _useState2[1];

  // Fonction pour récupérer les paramètres WordPress
  var getWordPressSettings = function getWordPressSettings() {
    if (window.pdfBuilderCanvasSettings) {
      return window.pdfBuilderCanvasSettings;
    }
    return null;
  };

  // Fonction pour récupérer ajaxurl et nonce
  var getAjaxSettings = function getAjaxSettings() {
    // Essayer d'abord les variables globales définies par WordPress
    if (window.pdfBuilderAjax) {
      return {
        ajaxurl: window.pdfBuilderAjax.ajaxurl || '',
        nonce: window.pdfBuilderAjax.nonce || ''
      };
    }
    // Fallback vers les variables individuelles
    return {
      ajaxurl: window.ajaxurl || '',
      nonce: window.pdfBuilderNonce || window.pdf_builder_nonce || ''
    };
  };

  // Charger les paramètres depuis WordPress ou localStorage au montage
  (0,react.useEffect)(function () {
    var wpSettings = getWordPressSettings();
    var ajaxSettings = getAjaxSettings();
    if (wpSettings) {
      // Utiliser les paramètres WordPress
      setSettings(function (prev) {
        return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), ajaxSettings), {}, {
          // Ajouter ajaxurl et nonce

          // Paramètres généraux du canvas
          defaultCanvasWidth: wpSettings.default_canvas_width || prev.defaultCanvasWidth,
          defaultCanvasHeight: wpSettings.default_canvas_height || prev.defaultCanvasHeight,
          defaultCanvasUnit: wpSettings.default_canvas_unit || prev.defaultCanvasUnit,
          defaultOrientation: wpSettings.default_orientation || prev.defaultOrientation,
          canvasBackgroundColor: wpSettings.canvas_background_color || prev.canvasBackgroundColor,
          canvasShowTransparency: wpSettings.canvas_show_transparency !== undefined ? wpSettings.canvas_show_transparency : prev.canvasShowTransparency,
          containerBackgroundColor: wpSettings.container_background_color || prev.containerBackgroundColor,
          containerShowTransparency: wpSettings.container_show_transparency !== undefined ? wpSettings.container_show_transparency : prev.containerShowTransparency,
          // Marges de sécurité
          marginTop: wpSettings.margin_top || prev.marginTop,
          marginRight: wpSettings.margin_right || prev.marginRight,
          marginBottom: wpSettings.margin_bottom || prev.marginBottom,
          marginLeft: wpSettings.margin_left || prev.marginLeft,
          showMargins: wpSettings.show_margins !== undefined ? wpSettings.show_margins : prev.showMargins,
          // Paramètres de grille
          showGrid: wpSettings.show_grid !== undefined ? wpSettings.show_grid : prev.showGrid,
          gridSize: wpSettings.grid_size || prev.gridSize,
          gridColor: wpSettings.grid_color || prev.gridColor,
          gridOpacity: wpSettings.grid_opacity || prev.gridOpacity,
          // Aimantation
          snapToGrid: wpSettings.snap_to_grid !== undefined ? wpSettings.snap_to_grid : prev.snapToGrid,
          snapToElements: wpSettings.snap_to_elements !== undefined ? wpSettings.snap_to_elements : prev.snapToElements,
          snapToMargins: wpSettings.snap_to_margins !== undefined ? wpSettings.snap_to_margins : prev.snapToMargins,
          snapTolerance: wpSettings.snap_tolerance || prev.snapTolerance,
          // Lignes guides
          showGuides: wpSettings.show_guides !== undefined ? wpSettings.show_guides : prev.showGuides,
          lockGuides: wpSettings.lock_guides !== undefined ? wpSettings.lock_guides : prev.lockGuides,
          // Paramètres de zoom et navigation
          defaultZoom: wpSettings.default_zoom || prev.defaultZoom,
          minZoom: wpSettings.min_zoom || prev.minZoom,
          maxZoom: wpSettings.max_zoom || prev.maxZoom,
          zoomStep: wpSettings.zoom_step || prev.zoomStep,
          panWithMouse: wpSettings.pan_with_mouse !== undefined ? wpSettings.pan_with_mouse : prev.panWithMouse,
          smoothZoom: wpSettings.smooth_zoom !== undefined ? wpSettings.smooth_zoom : prev.smoothZoom,
          showZoomIndicator: wpSettings.show_zoom_indicator !== undefined ? wpSettings.show_zoom_indicator : prev.showZoomIndicator,
          zoomWithWheel: wpSettings.zoom_with_wheel !== undefined ? wpSettings.zoom_with_wheel : prev.zoomWithWheel,
          zoomToSelection: wpSettings.zoom_to_selection !== undefined ? wpSettings.zoom_to_selection : prev.zoomToSelection,
          // Paramètres de sélection et manipulation
          showResizeHandles: true,
          // Forcer à true pour corriger le bug des poignées
          handleSize: wpSettings.handle_size || prev.handleSize,
          handleColor: wpSettings.handle_color || prev.handleColor,
          enableRotation: wpSettings.enable_rotation !== undefined ? wpSettings.enable_rotation : prev.enableRotation,
          rotationStep: wpSettings.rotation_step || prev.rotationStep,
          rotationSnap: wpSettings.rotation_snap !== undefined ? wpSettings.rotation_snap : prev.rotationSnap,
          multiSelect: wpSettings.multi_select !== undefined ? wpSettings.multi_select : prev.multiSelect,
          selectAllShortcut: wpSettings.select_all_shortcut !== undefined ? wpSettings.select_all_shortcut : prev.selectAllShortcut,
          showSelectionBounds: wpSettings.show_selection_bounds !== undefined ? wpSettings.show_selection_bounds : prev.showSelectionBounds,
          copyPasteEnabled: wpSettings.copy_paste_enabled !== undefined ? wpSettings.copy_paste_enabled : prev.copyPasteEnabled,
          duplicateOnDrag: wpSettings.duplicate_on_drag !== undefined ? wpSettings.duplicate_on_drag : prev.duplicateOnDrag,
          // Paramètres d'export et qualité
          exportQuality: wpSettings.export_quality || prev.exportQuality,
          exportFormat: wpSettings.export_format || prev.exportFormat,
          compressImages: wpSettings.compress_images !== undefined ? wpSettings.compress_images : prev.compressImages,
          imageQuality: wpSettings.image_quality || prev.imageQuality,
          maxImageSize: wpSettings.max_image_size || prev.maxImageSize,
          includeMetadata: wpSettings.include_metadata !== undefined ? wpSettings.include_metadata : prev.includeMetadata,
          pdfAuthor: wpSettings.pdf_author || prev.pdfAuthor,
          pdfSubject: wpSettings.pdf_subject || prev.pdfSubject,
          autoCrop: wpSettings.auto_crop !== undefined ? wpSettings.auto_crop : prev.autoCrop,
          embedFonts: wpSettings.embed_fonts !== undefined ? wpSettings.embed_fonts : prev.embedFonts,
          optimizeForWeb: wpSettings.optimize_for_web !== undefined ? wpSettings.optimize_for_web : prev.optimizeForWeb,
          // Paramètres avancés
          enableHardwareAcceleration: wpSettings.enable_hardware_acceleration !== undefined ? wpSettings.enable_hardware_acceleration : prev.enableHardwareAcceleration,
          limitFps: wpSettings.limit_fps !== undefined ? wpSettings.limit_fps : prev.limitFps,
          maxFps: wpSettings.max_fps || prev.maxFps,
          autoSaveEnabled: wpSettings.auto_save_enabled !== undefined ? wpSettings.auto_save_enabled : prev.autoSaveEnabled,
          autoSaveInterval: wpSettings.auto_save_interval || prev.autoSaveInterval,
          autoSaveVersions: wpSettings.auto_save_versions || prev.autoSaveVersions,
          undoLevels: wpSettings.undo_levels || prev.undoLevels,
          redoLevels: wpSettings.redo_levels || prev.redoLevels,
          enableKeyboardShortcuts: wpSettings.enable_keyboard_shortcuts !== undefined ? wpSettings.enable_keyboard_shortcuts : prev.enableKeyboardShortcuts,
          debugMode: wpSettings.debug_mode !== undefined ? wpSettings.debug_mode : prev.debugMode,
          showFps: wpSettings.show_fps !== undefined ? wpSettings.show_fps : prev.showFps,
          // Anciens paramètres (pour compatibilité)
          resizeHandleSize: wpSettings.canvas_handle_size || prev.resizeHandleSize,
          resizeHandleColor: wpSettings.canvas_handle_color || prev.resizeHandleColor,
          resizeHandleBorderColor: wpSettings.canvas_handle_hover_color || prev.resizeHandleBorderColor,
          selectionBorderWidth: wpSettings.canvas_border_width || prev.selectionBorderWidth,
          selectionBorderColor: wpSettings.canvas_border_color || prev.selectionBorderColor,
          selectionBorderSpacing: wpSettings.canvas_border_spacing || prev.selectionBorderSpacing,
          showResizeHandlesLegacy: wpSettings.canvas_resize_handles_enabled !== undefined ? wpSettings.canvas_resize_handles_enabled : prev.showResizeHandlesLegacy,
          showResizeZones: wpSettings.canvas_element_borders_enabled !== undefined ? wpSettings.canvas_element_borders_enabled : prev.showResizeZones,
          defaultTextColor: wpSettings.default_text_color || prev.defaultTextColor,
          defaultBackgroundColor: wpSettings.default_background_color || prev.defaultBackgroundColor,
          defaultFontSize: wpSettings.default_font_size || prev.defaultFontSize
        });
      });
    } else {
      // Fallback vers localStorage si les paramètres WordPress ne sont pas disponibles
      var _ajaxSettings = getAjaxSettings();
      var savedSettings = localStorage.getItem('pdf-builder-global-settings');
      if (savedSettings) {
        try {
          var parsedSettings = JSON.parse(savedSettings);
          // Forcer showResizeHandles à true pour corriger le bug des poignées
          parsedSettings.showResizeHandles = true;
          setSettings(function (prev) {
            return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), _ajaxSettings), parsedSettings);
          });
        } catch (error) {
          setSettings(function (prev) {
            return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), _ajaxSettings);
          });
        }
      } else {
        setSettings(function (prev) {
          return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), _ajaxSettings);
        });
      }
    }
  }, []);

  // Écouter les mises à jour de paramètres en temps réel
  (0,react.useEffect)(function () {
    var handleSettingsUpdate = function handleSettingsUpdate(event) {
      refreshSettings();
    };
    window.addEventListener('pdfBuilderSettingsUpdated', handleSettingsUpdate);
    return function () {
      window.removeEventListener('pdfBuilderSettingsUpdated', handleSettingsUpdate);
    };
  }, []);

  // Appliquer les paramètres aux variables CSS
  (0,react.useEffect)(function () {
    var root = document.documentElement;

    // Appliquer les paramètres des points de redimensionnement
    root.style.setProperty('--resize-handle-size', "".concat(settings.resizeHandleSize, "px"));
    root.style.setProperty('--resize-handle-color', settings.resizeHandleColor);
    root.style.setProperty('--resize-handle-border-color', settings.resizeHandleBorderColor);
    root.style.setProperty('--resize-handle-offset', "".concat(settings.resizeHandleOffset, "px"));

    // Appliquer les paramètres des bordures de sélection
    root.style.setProperty('--selection-border-width', "".concat(settings.selectionBorderWidth, "px"));
    root.style.setProperty('--selection-border-color', settings.selectionBorderColor);
    root.style.setProperty('--selection-border-spacing', "".concat(settings.selectionBorderSpacing, "px"));
    root.style.setProperty('--selection-shadow-opacity', settings.selectionShadowOpacity);

    // Appliquer les paramètres des zones de redimensionnement
    root.style.setProperty('--resize-zone-size', "".concat(settings.resizeZoneSize, "px"));
    root.style.setProperty('--resize-zone-opacity', settings.resizeZoneOpacity);

    // Appliquer les paramètres généraux
    root.style.setProperty('--show-resize-handles', settings.showResizeHandles ? 'block' : 'none');
    root.style.setProperty('--show-resize-zones', settings.showResizeZones ? 'auto' : 'none');
    root.style.setProperty('--selection-animation', settings.selectionAnimation ? 'all 0.2s ease' : 'none');
  }, [settings]);
  var updateSettings = /*#__PURE__*/function () {
    var _ref = useGlobalSettings_asyncToGenerator(/*#__PURE__*/useGlobalSettings_regenerator().m(function _callee(newSettings) {
      return useGlobalSettings_regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            setSettings(function (prev) {
              var updated = useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), newSettings);
              // Forcer showResizeHandles à true pour corriger le bug des poignées
              updated.showResizeHandles = true;
              // Sauvegarder dans localStorage pour la compatibilité
              localStorage.setItem('pdf-builder-global-settings', JSON.stringify(updated));

              // Sauvegarder dans WordPress via AJAX
              var ajaxSettings = getAjaxSettings();
              if (ajaxSettings.ajaxurl && ajaxSettings.nonce) {
                // Convertir les paramètres camelCase vers snake_case pour WordPress
                var wpSettings = {
                  show_grid: updated.showGrid,
                  grid_size: updated.gridSize,
                  grid_color: updated.gridColor,
                  grid_opacity: updated.gridOpacity,
                  snap_to_grid: updated.snapToGrid,
                  snap_to_elements: updated.snapToElements,
                  snap_to_margins: updated.snapToMargins,
                  snap_tolerance: updated.snapTolerance,
                  show_guides: updated.showGuides,
                  lock_guides: updated.lockGuides,
                  default_zoom: updated.defaultZoom,
                  min_zoom: updated.minZoom,
                  max_zoom: updated.maxZoom,
                  zoom_step: updated.zoomStep,
                  pan_with_mouse: updated.panWithMouse,
                  smooth_zoom: updated.smoothZoom,
                  show_zoom_indicator: updated.showZoomIndicator,
                  zoom_with_wheel: updated.zoomWithWheel,
                  zoom_to_selection: updated.zoomToSelection,
                  show_resize_handles: updated.showResizeHandles,
                  handle_size: updated.handleSize,
                  handle_color: updated.handleColor,
                  enable_rotation: updated.enableRotation,
                  rotation_step: updated.rotationStep,
                  rotation_snap: updated.rotationSnap,
                  multi_select: updated.multiSelect,
                  select_all_shortcut: updated.selectAllShortcut,
                  show_selection_bounds: updated.showSelectionBounds,
                  copy_paste_enabled: updated.copyPasteEnabled,
                  duplicate_on_drag: updated.duplicateOnDrag,
                  export_quality: updated.exportQuality,
                  export_format: updated.exportFormat,
                  compress_images: updated.compressImages,
                  image_quality: updated.imageQuality,
                  max_image_size: updated.maxImageSize,
                  include_metadata: updated.includeMetadata,
                  pdf_author: updated.pdfAuthor,
                  pdf_subject: updated.pdfSubject,
                  auto_crop: updated.autoCrop,
                  embed_fonts: updated.embedFonts,
                  optimize_for_web: updated.optimizeForWeb,
                  enable_hardware_acceleration: updated.enableHardwareAcceleration,
                  limit_fps: updated.limitFps,
                  max_fps: updated.maxFps,
                  auto_save_enabled: updated.autoSaveEnabled,
                  auto_save_interval: updated.autoSaveInterval,
                  auto_save_versions: updated.autoSaveVersions,
                  undo_levels: updated.undoLevels,
                  redo_levels: updated.redoLevels,
                  enable_keyboard_shortcuts: updated.enableKeyboardShortcuts,
                  debug_mode: updated.debugMode,
                  show_fps: updated.showFps,
                  // Paramètres canvas
                  default_canvas_width: updated.defaultCanvasWidth,
                  default_canvas_height: updated.defaultCanvasHeight,
                  default_canvas_unit: updated.defaultCanvasUnit,
                  canvas_background_color: updated.canvasBackgroundColor,
                  canvas_show_transparency: updated.canvasShowTransparency,
                  margin_top: updated.marginTop,
                  margin_right: updated.marginRight,
                  margin_bottom: updated.marginBottom,
                  margin_left: updated.marginLeft,
                  show_margins: updated.showMargins
                };

                // Faire l'appel AJAX en arrière-plan (sans bloquer)
                fetch(ajaxSettings.ajaxurl + '?action=pdf_builder_save_settings_page', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: new URLSearchParams(useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, wpSettings), {}, {
                    nonce: ajaxSettings.nonce
                  }))
                }).then(function (response) {
                  if (!response.ok) {
                    console.warn('Erreur lors de la sauvegarde des paramètres dans WordPress:', response.status);
                  } else {
                    // console.log('Paramètres sauvegardés avec succès dans WordPress');
                  }
                })["catch"](function (error) {
                  console.warn('Erreur AJAX lors de la sauvegarde des paramètres:', error);
                });
              }
              return updated;
            });
          case 1:
            return _context.a(2);
        }
      }, _callee);
    }));
    return function updateSettings(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  var resetToDefaults = function resetToDefaults() {
    var defaultSettings = {
      resizeHandleSize: 12,
      resizeHandleColor: '#007cba',
      resizeHandleBorderColor: '#ffffff',
      selectionBorderWidth: 2,
      selectionBorderColor: 'var(--primary-color)',
      selectionBorderSpacing: 2,
      selectionShadowOpacity: 0.1,
      resizeZoneSize: 16,
      resizeZoneOpacity: 0.1,
      showResizeHandles: true,
      showResizeZones: true,
      selectionAnimation: true
    };
    setSettings(defaultSettings);
  };

  // Fonction pour rafraîchir les paramètres depuis WordPress (pour mise à jour en temps réel)
  var refreshSettings = function refreshSettings() {
    var wpSettings = getWordPressSettings();
    var ajaxSettings = getAjaxSettings();
    if (wpSettings) {
      setSettings(function (prev) {
        return useGlobalSettings_objectSpread(useGlobalSettings_objectSpread(useGlobalSettings_objectSpread({}, prev), ajaxSettings), {}, {
          // Ajouter ajaxurl et nonce

          // Paramètres généraux du canvas
          defaultCanvasWidth: wpSettings.default_canvas_width || prev.defaultCanvasWidth,
          defaultCanvasHeight: wpSettings.default_canvas_height || prev.defaultCanvasHeight,
          defaultCanvasUnit: wpSettings.default_canvas_unit || prev.defaultCanvasUnit,
          defaultOrientation: wpSettings.default_orientation || prev.defaultOrientation,
          canvasBackgroundColor: wpSettings.canvas_background_color || prev.canvasBackgroundColor,
          canvasShowTransparency: wpSettings.canvas_show_transparency !== undefined ? wpSettings.canvas_show_transparency : prev.canvasShowTransparency,
          containerBackgroundColor: wpSettings.container_background_color || prev.containerBackgroundColor,
          containerShowTransparency: wpSettings.container_show_transparency !== undefined ? wpSettings.container_show_transparency : prev.containerShowTransparency,
          // Marges de sécurité
          marginTop: wpSettings.margin_top || prev.marginTop,
          marginRight: wpSettings.margin_right || prev.marginRight,
          marginBottom: wpSettings.margin_bottom || prev.marginBottom,
          marginLeft: wpSettings.margin_left || prev.marginLeft,
          showMargins: wpSettings.show_margins !== undefined ? wpSettings.show_margins : prev.showMargins,
          // Paramètres de grille
          showGrid: wpSettings.show_grid !== undefined ? wpSettings.show_grid : prev.showGrid,
          gridSize: wpSettings.grid_size || prev.gridSize,
          gridColor: wpSettings.grid_color || prev.gridColor,
          gridOpacity: wpSettings.grid_opacity || prev.gridOpacity,
          // Aimantation
          snapToGrid: wpSettings.snap_to_grid !== undefined ? wpSettings.snap_to_grid : prev.snapToGrid,
          snapToElements: wpSettings.snap_to_elements !== undefined ? wpSettings.snap_to_elements : prev.snapToElements,
          snapToMargins: wpSettings.snap_to_margins !== undefined ? wpSettings.snap_to_margins : prev.snapToMargins,
          snapTolerance: wpSettings.snap_tolerance || prev.snapTolerance,
          // Lignes guides
          showGuides: wpSettings.show_guides !== undefined ? wpSettings.show_guides : prev.showGuides,
          lockGuides: wpSettings.lock_guides !== undefined ? wpSettings.lock_guides : prev.lockGuides,
          // Paramètres de zoom et navigation
          defaultZoom: wpSettings.default_zoom || prev.defaultZoom,
          minZoom: wpSettings.min_zoom || prev.minZoom,
          maxZoom: wpSettings.max_zoom || prev.maxZoom,
          zoomStep: wpSettings.zoom_step || prev.zoomStep,
          panWithMouse: wpSettings.pan_with_mouse !== undefined ? wpSettings.pan_with_mouse : prev.panWithMouse,
          smoothZoom: wpSettings.smooth_zoom !== undefined ? wpSettings.smooth_zoom : prev.smoothZoom,
          showZoomIndicator: wpSettings.show_zoom_indicator !== undefined ? wpSettings.show_zoom_indicator : prev.showZoomIndicator,
          zoomWithWheel: wpSettings.zoom_with_wheel !== undefined ? wpSettings.zoom_with_wheel : prev.zoomWithWheel,
          zoomToSelection: wpSettings.zoom_to_selection !== undefined ? wpSettings.zoom_to_selection : prev.zoomToSelection,
          // Paramètres de sélection et manipulation
          showResizeHandles: true,
          // Forcer à true pour corriger le bug des poignées
          handleSize: wpSettings.handle_size || prev.handleSize,
          handleColor: wpSettings.handle_color || prev.handleColor,
          enableRotation: wpSettings.enable_rotation !== undefined ? wpSettings.enable_rotation : prev.enableRotation,
          rotationStep: wpSettings.rotation_step || prev.rotationStep,
          rotationSnap: wpSettings.rotation_snap !== undefined ? wpSettings.rotation_snap : prev.rotationSnap,
          multiSelect: wpSettings.multi_select !== undefined ? wpSettings.multi_select : prev.multiSelect,
          selectAllShortcut: wpSettings.select_all_shortcut !== undefined ? wpSettings.select_all_shortcut : prev.selectAllShortcut,
          showSelectionBounds: wpSettings.show_selection_bounds !== undefined ? wpSettings.show_selection_bounds : prev.showSelectionBounds,
          copyPasteEnabled: wpSettings.copy_paste_enabled !== undefined ? wpSettings.copy_paste_enabled : prev.copyPasteEnabled,
          duplicateOnDrag: wpSettings.duplicate_on_drag !== undefined ? wpSettings.duplicate_on_drag : prev.duplicateOnDrag,
          // Paramètres d'export et qualité
          exportQuality: wpSettings.export_quality || prev.exportQuality,
          exportFormat: wpSettings.export_format || prev.exportFormat,
          compressImages: wpSettings.compress_images !== undefined ? wpSettings.compress_images : prev.compressImages,
          imageQuality: wpSettings.image_quality || prev.imageQuality,
          maxImageSize: wpSettings.max_image_size || prev.maxImageSize,
          includeMetadata: wpSettings.include_metadata !== undefined ? wpSettings.include_metadata : prev.includeMetadata,
          pdfAuthor: wpSettings.pdf_author || prev.pdfAuthor,
          pdfSubject: wpSettings.pdf_subject || prev.pdfSubject,
          autoCrop: wpSettings.auto_crop !== undefined ? wpSettings.auto_crop : prev.autoCrop,
          embedFonts: wpSettings.embed_fonts !== undefined ? wpSettings.embed_fonts : prev.embedFonts,
          optimizeForWeb: wpSettings.optimize_for_web !== undefined ? wpSettings.optimize_for_web : prev.optimizeForWeb,
          // Paramètres avancés
          enableHardwareAcceleration: wpSettings.enable_hardware_acceleration !== undefined ? wpSettings.enable_hardware_acceleration : prev.enableHardwareAcceleration,
          limitFps: wpSettings.limit_fps !== undefined ? wpSettings.limit_fps : prev.limitFps,
          maxFps: wpSettings.max_fps || prev.maxFps,
          autoSaveEnabled: wpSettings.auto_save_enabled !== undefined ? wpSettings.auto_save_enabled : prev.autoSaveEnabled,
          autoSaveInterval: wpSettings.auto_save_interval || prev.autoSaveInterval,
          autoSaveVersions: wpSettings.auto_save_versions || prev.autoSaveVersions,
          undoLevels: wpSettings.undo_levels || prev.undoLevels,
          redoLevels: wpSettings.redo_levels || prev.redoLevels,
          enableKeyboardShortcuts: wpSettings.enable_keyboard_shortcuts !== undefined ? wpSettings.enable_keyboard_shortcuts : prev.enableKeyboardShortcuts,
          debugMode: wpSettings.debug_mode !== undefined ? wpSettings.debug_mode : prev.debugMode,
          showFps: wpSettings.show_fps !== undefined ? wpSettings.show_fps : prev.showFps,
          // Anciens paramètres (pour compatibilité)
          resizeHandleSize: wpSettings.canvas_handle_size || prev.resizeHandleSize,
          resizeHandleColor: wpSettings.canvas_handle_color || prev.resizeHandleColor,
          resizeHandleBorderColor: wpSettings.canvas_handle_hover_color || prev.resizeHandleBorderColor,
          selectionBorderWidth: wpSettings.canvas_border_width || prev.selectionBorderWidth,
          selectionBorderColor: wpSettings.canvas_border_color || prev.selectionBorderColor,
          selectionBorderSpacing: wpSettings.canvas_border_spacing || prev.selectionBorderSpacing,
          showResizeHandlesLegacy: wpSettings.canvas_resize_handles_enabled !== undefined ? wpSettings.canvas_resize_handles_enabled : prev.showResizeHandlesLegacy,
          showResizeZones: wpSettings.canvas_element_borders_enabled !== undefined ? wpSettings.canvas_element_borders_enabled : prev.showResizeZones,
          defaultTextColor: wpSettings.default_text_color || prev.defaultTextColor,
          defaultBackgroundColor: wpSettings.default_background_color || prev.defaultBackgroundColor,
          defaultFontSize: wpSettings.default_font_size || prev.defaultFontSize
        });
      });
    }
  };
  return {
    settings: settings,
    updateSettings: updateSettings,
    resetToDefaults: resetToDefaults,
    refreshSettings: refreshSettings
  };
};
;// ./src/components/FPSCounter.jsx
function FPSCounter_slicedToArray(r, e) { return FPSCounter_arrayWithHoles(r) || FPSCounter_iterableToArrayLimit(r, e) || FPSCounter_unsupportedIterableToArray(r, e) || FPSCounter_nonIterableRest(); }
function FPSCounter_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function FPSCounter_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return FPSCounter_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? FPSCounter_arrayLikeToArray(r, a) : void 0; } }
function FPSCounter_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function FPSCounter_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function FPSCounter_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var FPSCounter = function FPSCounter(_ref) {
  var showFps = _ref.showFps;
  var _useState = (0,react.useState)(0),
    _useState2 = FPSCounter_slicedToArray(_useState, 2),
    fps = _useState2[0],
    setFps = _useState2[1];
  var frameCountRef = (0,react.useRef)(0);
  var lastTimeRef = (0,react.useRef)(performance.now());
  var animationFrameRef = (0,react.useRef)(null);
  (0,react.useEffect)(function () {
    if (!showFps) return;
    var _updateFPS = function updateFPS() {
      var now = performance.now();
      frameCountRef.current++;

      // Mettre à jour les FPS toutes les secondes
      if (now - lastTimeRef.current >= 1000) {
        setFps(Math.round(frameCountRef.current * 1000 / (now - lastTimeRef.current)));
        frameCountRef.current = 0;
        lastTimeRef.current = now;
      }
      animationFrameRef.current = requestAnimationFrame(_updateFPS);
    };
    animationFrameRef.current = requestAnimationFrame(_updateFPS);
    return function () {
      if (animationFrameRef.current) {
        cancelAnimationFrame(animationFrameRef.current);
      }
    };
  }, [showFps]);
  if (!showFps) return null;
  return /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'fixed',
      top: '10px',
      right: '10px',
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      color: '#00ff00',
      padding: '5px 10px',
      borderRadius: '4px',
      fontFamily: 'monospace',
      fontSize: '12px',
      fontWeight: 'bold',
      zIndex: 9999,
      pointerEvents: 'none',
      userSelect: 'none'
    }
  }, "FPS: ", fps);
};
;// ./src/components/ContextMenu.jsx

var ContextMenu = function ContextMenu(_ref) {
  var menu = _ref.menu,
    onAction = _ref.onAction,
    _ref$isAnimating = _ref.isAnimating,
    isAnimating = _ref$isAnimating === void 0 ? false : _ref$isAnimating,
    onClose = _ref.onClose;
  var menuRef = (0,react.useRef)(null);

  // Fermer le menu quand on clique ailleurs
  (0,react.useEffect)(function () {
    var handleClickOutside = function handleClickOutside(e) {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        if (onClose) {
          onClose();
        }
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return function () {
      return document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [onClose]);

  // Gestionnaire d'action
  var handleAction = function handleAction(item) {
    if (item.action && typeof item.action === 'function') {
      item.action();
    } else if (onAction) {
      onAction(item.action);
    }
  };
  if (!menu) return null;
  return /*#__PURE__*/React.createElement("div", {
    ref: menuRef,
    className: "context-menu",
    style: {
      position: 'fixed',
      left: menu.x,
      top: menu.y,
      backgroundColor: 'white',
      border: '1px solid #ccc',
      borderRadius: '6px',
      boxShadow: '0 8px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.08)',
      zIndex: 1000,
      minWidth: '200px',
      padding: '4px 0',
      animation: isAnimating ? 'contextMenuFadeOut 0.15s cubic-bezier(0.4, 0, 1, 1)' : 'contextMenuFadeIn 0.15s cubic-bezier(0.16, 1, 0.3, 1)',
      transformOrigin: 'top left'
    }
  }, menu.items.map(function (item, index) {
    return /*#__PURE__*/React.createElement("div", {
      key: index
    }, item.type === 'separator' ? /*#__PURE__*/React.createElement("div", {
      style: {
        height: '1px',
        backgroundColor: '#f8f9fa',
        margin: '4px 0'
      }
    }) : /*#__PURE__*/React.createElement("button", {
      className: "context-menu-item",
      onClick: function onClick() {
        return handleAction(item);
      },
      disabled: item.disabled,
      style: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        width: '100%',
        padding: '10px 16px',
        border: 'none',
        backgroundColor: 'transparent',
        cursor: item.disabled ? 'not-allowed' : 'pointer',
        fontSize: '14px',
        textAlign: 'left',
        borderRadius: '0',
        opacity: item.disabled ? 0.5 : 1,
        transition: 'background-color 0.15s ease, color 0.15s ease'
      },
      onMouseEnter: function onMouseEnter(e) {
        if (!item.disabled) {
          e.target.style.backgroundColor = '#f8f9fa';
          e.target.style.color = '#1e293b';
        }
      },
      onMouseLeave: function onMouseLeave(e) {
        if (!item.disabled) {
          e.target.style.backgroundColor = 'transparent';
          e.target.style.color = 'inherit';
        }
      }
    }, /*#__PURE__*/React.createElement("div", {
      style: {
        display: 'flex',
        alignItems: 'center'
      }
    }, /*#__PURE__*/React.createElement("span", {
      style: {
        marginRight: '8px'
      }
    }, item.icon || getDefaultIcon(item.label)), item.label), getKeyboardShortcut(item.label) && /*#__PURE__*/React.createElement("span", {
      style: {
        fontSize: '12px',
        color: '#666',
        marginLeft: '16px'
      }
    }, getKeyboardShortcut(item.label))));
  }));
};

// Fonction utilitaire pour obtenir une icône par défaut selon le label
var getDefaultIcon = function getDefaultIcon(label) {
  var iconMap = {
    'Dupliquer': '📋',
    'Supprimer': '🗑️',
    'Copier': '[C]',
    'Coller': '[V]',
    'Annuler': '↶',
    'Rétablir': '↷',
    'Tout sélectionner': '☑️',
    'Désélectionner': '☐'
  };
  return iconMap[label] || '•';
};

// Fonction utilitaire pour obtenir un raccourci clavier selon le label
var getKeyboardShortcut = function getKeyboardShortcut(label) {
  var shortcutMap = {
    'Copier': 'Ctrl+C',
    'Coller': 'Ctrl+V',
    'Dupliquer': 'Ctrl+D',
    'Supprimer': 'Del',
    'Annuler': 'Ctrl+Z',
    'Rétablir': 'Ctrl+Y',
    'Tout sélectionner': 'Ctrl+A',
    'Désélectionner': 'Esc'
  };
  return shortcutMap[label];
};
/* harmony default export */ const components_ContextMenu = (ContextMenu);
;// ./src/components/PreviewModal.jsx
function PreviewModal_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return PreviewModal_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (PreviewModal_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, PreviewModal_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, PreviewModal_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), PreviewModal_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", PreviewModal_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), PreviewModal_regeneratorDefine2(u), PreviewModal_regeneratorDefine2(u, o, "Generator"), PreviewModal_regeneratorDefine2(u, n, function () { return this; }), PreviewModal_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (PreviewModal_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function PreviewModal_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } PreviewModal_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { PreviewModal_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, PreviewModal_regeneratorDefine2(e, r, n, t); }
function PreviewModal_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function PreviewModal_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? PreviewModal_ownKeys(Object(t), !0).forEach(function (r) { PreviewModal_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : PreviewModal_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function PreviewModal_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function PreviewModal_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { PreviewModal_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { PreviewModal_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function PreviewModal_defineProperty(e, r, t) { return (r = PreviewModal_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function PreviewModal_toPropertyKey(t) { var i = PreviewModal_toPrimitive(t, "string"); return "symbol" == PreviewModal_typeof(i) ? i : i + ""; }
function PreviewModal_toPrimitive(t, r) { if ("object" != PreviewModal_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != PreviewModal_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function PreviewModal_typeof(o) { "@babel/helpers - typeof"; return PreviewModal_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, PreviewModal_typeof(o); }
function PreviewModal_slicedToArray(r, e) { return PreviewModal_arrayWithHoles(r) || PreviewModal_iterableToArrayLimit(r, e) || PreviewModal_unsupportedIterableToArray(r, e) || PreviewModal_nonIterableRest(); }
function PreviewModal_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PreviewModal_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return PreviewModal_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? PreviewModal_arrayLikeToArray(r, a) : void 0; } }
function PreviewModal_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function PreviewModal_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function PreviewModal_arrayWithHoles(r) { if (Array.isArray(r)) return r; }


// Nouveau système d'aperçu côté serveur avec TCPDF

var PreviewModal = function PreviewModal(_ref) {
  var isOpen = _ref.isOpen,
    onClose = _ref.onClose,
    _ref$elements = _ref.elements,
    elements = _ref$elements === void 0 ? [] : _ref$elements,
    _ref$canvasWidth = _ref.canvasWidth,
    canvasWidth = _ref$canvasWidth === void 0 ? 595 : _ref$canvasWidth,
    _ref$canvasHeight = _ref.canvasHeight,
    canvasHeight = _ref$canvasHeight === void 0 ? 842 : _ref$canvasHeight,
    _ref$zoom = _ref.zoom,
    zoom = _ref$zoom === void 0 ? 1 : _ref$zoom,
    ajaxurl = _ref.ajaxurl,
    pdfBuilderNonce = _ref.pdfBuilderNonce,
    _ref$onOpenPDFModal = _ref.onOpenPDFModal,
    onOpenPDFModal = _ref$onOpenPDFModal === void 0 ? null : _ref$onOpenPDFModal,
    _ref$useServerPreview = _ref.useServerPreview,
    useServerPreview = _ref$useServerPreview === void 0 ? false : _ref$useServerPreview;
  var _useState = (0,react.useState)(null),
    _useState2 = PreviewModal_slicedToArray(_useState, 2),
    previewData = _useState2[0],
    setPreviewData = _useState2[1];
  var _useState3 = (0,react.useState)(false),
    _useState4 = PreviewModal_slicedToArray(_useState3, 2),
    loading = _useState4[0],
    setLoading = _useState4[1];
  var _useState5 = (0,react.useState)(null),
    _useState6 = PreviewModal_slicedToArray(_useState5, 2),
    error = _useState6[0],
    setError = _useState6[1];

  // Fonction pour nettoyer les éléments avant sérialisation JSON
  var cleanElementsForJSON = function cleanElementsForJSON(elements) {
    if (!Array.isArray(elements)) {
      throw new Error('Les éléments doivent être un tableau');
    }
    return elements.map(function (element) {
      if (!element || PreviewModal_typeof(element) !== 'object') {
        throw new Error('Chaque élément doit être un objet valide');
      }

      // Créer une copie profonde de l'élément
      var cleaned = JSON.parse(JSON.stringify(element));

      // S'assurer que les propriétés numériques sont des nombres
      var numericProps = ['x', 'y', 'width', 'height', 'fontSize', 'padding', 'zIndex', 'borderWidth'];
      numericProps.forEach(function (prop) {
        if (cleaned[prop] !== undefined) {
          var numValue = parseFloat(cleaned[prop]);
          if (isNaN(numValue)) {
            throw new Error("Propri\xE9t\xE9 ".concat(prop, " doit \xEAtre un nombre valide"));
          }
          cleaned[prop] = numValue;
        }
      });

      // Valider les propriétés requises
      if (typeof cleaned.type !== 'string') {
        throw new Error('Chaque élément doit avoir un type string');
      }

      // Nettoyer les propriétés potentiellement problématiques
      delete cleaned.tempId; // Supprimer les IDs temporaires si présents
      delete cleaned.isDragging; // Supprimer les états d'interaction
      delete cleaned.isResizing; // Supprimer les états d'interaction

      return cleaned;
    });
  };

  // Fonction de validation des éléments avant envoi
  var validateElementsBeforeSend = function validateElementsBeforeSend(elements) {
    try {
      var cleanedElements = cleanElementsForJSON(elements);
      var jsonString = JSON.stringify(cleanedElements);

      // Vérifier que le JSON est valide
      JSON.parse(jsonString);

      // Vérifier la longueur raisonnable
      if (jsonString.length > 10000000) {
        // 10MB max
        throw new Error('JSON trop volumineux');
      }
      return {
        success: true,
        jsonString: jsonString,
        cleanedElements: cleanedElements
      };
    } catch (error) {
      console.error('Client-side validation failed:', error);
      return {
        success: false,
        error: error.message
      };
    }
  };

  // Fonction pour rendre le contenu du canvas en HTML
  var renderCanvasContent = (0,react.useCallback)(function (elements) {
    // Réduire les logs pour éviter la boucle infinie - n'afficher que les erreurs importantes
    if (!elements || elements.length === 0) {
      return /*#__PURE__*/React.createElement("div", {
        style: {
          padding: '20px',
          textAlign: 'center',
          color: '#666'
        }
      }, "Aucun \xE9l\xE9ment \xE0 afficher");
    }

    // Vérifier que zoom est valide
    var validZoom = typeof zoom === 'number' && !isNaN(zoom) && zoom > 0 ? zoom : 1;
    return /*#__PURE__*/React.createElement("div", {
      style: {
        position: 'relative',
        width: canvasWidth * validZoom,
        height: canvasHeight * validZoom,
        backgroundColor: 'white',
        border: '1px solid #e2e8f0',
        borderRadius: '4px',
        overflow: 'hidden',
        margin: '0 auto'
      }
    }, elements.map(function (element, index) {
      // Vérifier que les propriétés essentielles existent
      if (typeof element.x !== 'number' || typeof element.y !== 'number' || typeof element.width !== 'number' || typeof element.height !== 'number') {
        console.error('❌ Element missing required properties:', element);
        return null;
      }
      var elementPadding = element.padding || 0;
      var baseStyle = {
        position: 'absolute',
        left: (element.x + elementPadding) * validZoom,
        top: (element.y + elementPadding) * validZoom,
        width: Math.max(1, element.width - elementPadding * 2) * validZoom,
        height: Math.max(1, element.height - elementPadding * 2) * validZoom,
        zIndex: element.zIndex || index + 1
      };
      return /*#__PURE__*/React.createElement("div", {
        key: index,
        style: baseStyle
      }, renderSpecialElement(element, validZoom));
    }));
  }, [zoom, canvasWidth, canvasHeight]);

  // Fonction pour rendre un élément spécial (basée sur CanvasElement.jsx)
  var renderSpecialElement = (0,react.useCallback)(function (element, zoom) {
    // Réduire les logs - n'afficher que les erreurs importantes
    switch (element.type) {
      case 'text':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            fontSize: (element.fontSize || 16) * zoom,
            color: element.color || '#000000',
            fontWeight: element.fontWeight === 'bold' ? 'bold' : 'normal',
            fontStyle: element.fontStyle === 'italic' ? 'italic' : 'normal',
            textDecoration: element.textDecoration || 'none',
            textAlign: element.textAlign || 'left',
            lineHeight: element.lineHeight || '1.2',
            whiteSpace: 'pre-wrap',
            overflow: 'hidden',
            padding: "".concat(4 * zoom, "px"),
            boxSizing: 'border-box'
          }
        }, element.content || element.text || 'Texte');
      case 'rectangle':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            backgroundColor: element.fillColor || 'transparent',
            border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || '#000000') : 'none',
            borderRadius: (element.borderRadius || 0) * zoom
          }
        });
      case 'image':
        return /*#__PURE__*/React.createElement("img", {
          src: element.src || element.imageUrl || '',
          alt: element.alt || 'Image',
          style: {
            width: '100%',
            height: '100%',
            objectFit: element.objectFit || 'cover',
            borderRadius: (element.borderRadius || 0) * zoom
          },
          onError: function onError(e) {
            e.target.style.display = 'none';
          }
        });
      case 'line':
        return /*#__PURE__*/React.createElement("div", {
          style: PreviewModal_defineProperty({
            width: '100%',
            height: (element.height || element.strokeWidth || 1) * zoom,
            borderTop: "".concat((element.strokeWidth || 1) * zoom, "px solid ").concat(element.strokeColor || '#000000')
          }, "height", 0)
        });
      case 'divider':
        return /*#__PURE__*/React.createElement("div", {
          style: PreviewModal_defineProperty(PreviewModal_defineProperty(PreviewModal_defineProperty({
            width: '100%',
            height: '100%',
            backgroundColor: element.color || element.fillColor || '#cccccc'
          }, "height", "".concat((element.thickness || element.height || 2) * zoom, "px")), "margin", "".concat((element.margin || 10) * zoom, "px 0")), "borderRadius", (element.borderRadius || 0) * zoom)
        });
      case 'product_table':
        // Rendu dynamique du tableau de produits utilisant les propriétés de l'élément
        var getTableStyles = function getTableStyles() {
          var tableStyle = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';
          var baseStyles = {
            "default": {
              headerBg: '#f8fafc',
              headerBorder: '#e2e8f0',
              rowBorder: '#f1f5f9',
              altRowBg: '#fafbfc',
              borderWidth: 1,
              headerTextColor: '#334155',
              rowTextColor: '#334155',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 1px 3px rgba(0, 0, 0, 0.1)',
              borderRadius: '4px'
            },
            classic: {
              headerBg: '#1e293b',
              headerBorder: '#334155',
              rowBorder: '#334155',
              altRowBg: '#ffffff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#1e293b',
              headerFontWeight: '700',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
              borderRadius: '0px'
            },
            striped: {
              headerBg: '#3b82f6',
              headerBorder: '#2563eb',
              rowBorder: '#e2e8f0',
              altRowBg: '#f8fafc',
              borderWidth: 1,
              headerTextColor: '#ffffff',
              rowTextColor: '#334155',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 1px 4px rgba(59, 130, 246, 0.2)',
              borderRadius: '6px'
            },
            bordered: {
              headerBg: '#ffffff',
              headerBorder: '#374151',
              rowBorder: '#d1d5db',
              altRowBg: '#ffffff',
              borderWidth: 2,
              headerTextColor: '#111827',
              rowTextColor: '#111827',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 12px rgba(0, 0, 0, 0.1), inset 0 0 0 1px #e5e7eb',
              borderRadius: '8px'
            },
            minimal: {
              headerBg: '#ffffff',
              headerBorder: '#d1d5db',
              rowBorder: '#f3f4f6',
              altRowBg: '#ffffff',
              borderWidth: 0.5,
              headerTextColor: '#6b7280',
              rowTextColor: '#6b7280',
              headerFontWeight: '500',
              headerFontSize: '10px',
              rowFontSize: '9px',
              shadow: 'none',
              borderRadius: '0px'
            },
            modern: {
              gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
              headerBorder: '#5b21b6',
              rowBorder: '#e9d5ff',
              altRowBg: '#faf5ff',
              borderWidth: 1,
              headerTextColor: '#ffffff',
              rowTextColor: '#6b21a8',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 20px rgba(102, 126, 234, 0.25)',
              borderRadius: '8px'
            },
            // Nouveaux styles colorés
            blue_ocean: {
              gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
              headerBorder: '#1e40af',
              rowBorder: '#dbeafe',
              altRowBg: '#eff6ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#1e3a8a',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(59, 130, 246, 0.3)',
              borderRadius: '6px'
            },
            emerald_forest: {
              gradient: 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
              headerBorder: '#065f46',
              rowBorder: '#d1fae5',
              altRowBg: '#ecfdf5',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#064e3b',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(16, 185, 129, 0.3)',
              borderRadius: '6px'
            },
            sunset_orange: {
              gradient: 'linear-gradient(135deg, #9a3412 0%, #f97316 100%)',
              headerBorder: '#c2410c',
              rowBorder: '#fed7aa',
              altRowBg: '#fff7ed',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#9a3412',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(249, 115, 22, 0.3)',
              borderRadius: '6px'
            },
            royal_purple: {
              gradient: 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)',
              headerBorder: '#7c3aed',
              rowBorder: '#e9d5ff',
              altRowBg: '#faf5ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#581c87',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(168, 85, 247, 0.3)',
              borderRadius: '6px'
            },
            rose_pink: {
              gradient: 'linear-gradient(135deg, #be185d 0%, #f472b6 100%)',
              headerBorder: '#db2777',
              rowBorder: '#fce7f3',
              altRowBg: '#fdf2f8',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#be185d',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(244, 114, 182, 0.3)',
              borderRadius: '6px'
            },
            teal_aqua: {
              gradient: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
              headerBorder: '#0d9488',
              rowBorder: '#ccfbf1',
              altRowBg: '#f0fdfa',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#0f766e',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(20, 184, 166, 0.3)',
              borderRadius: '6px'
            },
            crimson_red: {
              gradient: 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)',
              headerBorder: '#dc2626',
              rowBorder: '#fecaca',
              altRowBg: '#fef2f2',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#991b1b',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(239, 68, 68, 0.3)',
              borderRadius: '6px'
            },
            amber_gold: {
              gradient: 'linear-gradient(135deg, #92400e 0%, #f59e0b 100%)',
              headerBorder: '#d97706',
              rowBorder: '#fef3c7',
              altRowBg: '#fffbeb',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#92400e',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(245, 158, 11, 0.3)',
              borderRadius: '6px'
            },
            indigo_night: {
              gradient: 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)',
              headerBorder: '#4338ca',
              rowBorder: '#e0e7ff',
              altRowBg: '#eef2ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#312e81',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(99, 102, 241, 0.3)',
              borderRadius: '6px'
            },
            slate_gray: {
              gradient: 'linear-gradient(135deg, #374151 0%, #6b7280 100%)',
              headerBorder: '#4b5563',
              rowBorder: '#f3f4f6',
              altRowBg: '#f9fafb',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#374151',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(107, 114, 128, 0.3)',
              borderRadius: '6px'
            },
            coral_sunset: {
              gradient: 'linear-gradient(135deg, #c2410c 0%, #fb7185 100%)',
              headerBorder: '#ea580c',
              rowBorder: '#fed7d7',
              altRowBg: '#fef7f7',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#c2410c',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(251, 113, 133, 0.3)',
              borderRadius: '6px'
            },
            mint_green: {
              gradient: 'linear-gradient(135deg, #065f46 0%, #34d399 100%)',
              headerBorder: '#047857',
              rowBorder: '#d1fae5',
              altRowBg: '#ecfdf5',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#065f46',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(52, 211, 153, 0.3)',
              borderRadius: '6px'
            },
            violet_dream: {
              gradient: 'linear-gradient(135deg, #6d28d9 0%, #c084fc 100%)',
              headerBorder: '#8b5cf6',
              rowBorder: '#ede9fe',
              altRowBg: '#f5f3ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#6d28d9',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(192, 132, 252, 0.3)',
              borderRadius: '6px'
            },
            sky_blue: {
              gradient: 'linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%)',
              headerBorder: '#0284c7',
              rowBorder: '#bae6fd',
              altRowBg: '#f0f9ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#0369a1',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(14, 165, 233, 0.3)',
              borderRadius: '6px'
            },
            forest_green: {
              gradient: 'linear-gradient(135deg, #14532d 0%, #22c55e 100%)',
              headerBorder: '#15803d',
              rowBorder: '#bbf7d0',
              altRowBg: '#f0fdf4',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#14532d',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(34, 197, 94, 0.3)',
              borderRadius: '6px'
            },
            ruby_red: {
              gradient: 'linear-gradient(135deg, #b91c1c 0%, #f87171 100%)',
              headerBorder: '#dc2626',
              rowBorder: '#fecaca',
              altRowBg: '#fef2f2',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#b91c1b',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(248, 113, 113, 0.3)',
              borderRadius: '6px'
            },
            golden_yellow: {
              gradient: 'linear-gradient(135deg, #a16207 0%, #eab308 100%)',
              headerBorder: '#ca8a04',
              rowBorder: '#fef08a',
              altRowBg: '#fefce8',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#a16207',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(234, 179, 8, 0.3)',
              borderRadius: '6px'
            },
            navy_blue: {
              gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
              headerBorder: '#1e40af',
              rowBorder: '#dbeafe',
              altRowBg: '#eff6ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#1e3a8a',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(59, 130, 246, 0.3)',
              borderRadius: '6px'
            },
            burgundy_wine: {
              gradient: 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
              headerBorder: '#991b1b',
              rowBorder: '#fecaca',
              altRowBg: '#fef2f2',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#7f1d1d',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(220, 38, 38, 0.3)',
              borderRadius: '6px'
            },
            lavender_purple: {
              gradient: 'linear-gradient(135deg, #7c2d12 0%, #a855f7 100%)',
              headerBorder: '#9333ea',
              rowBorder: '#e9d5ff',
              altRowBg: '#faf5ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#7c2d12',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(168, 85, 247, 0.3)',
              borderRadius: '6px'
            },
            ocean_teal: {
              gradient: 'linear-gradient(135deg, #134e4a 0%, #14b8a6 100%)',
              headerBorder: '#0f766e',
              rowBorder: '#ccfbf1',
              altRowBg: '#f0fdfa',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#134e4a',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(20, 184, 166, 0.3)',
              borderRadius: '6px'
            },
            cherry_blossom: {
              gradient: 'linear-gradient(135deg, #be185d 0%, #fb7185 100%)',
              headerBorder: '#db2777',
              rowBorder: '#fce7f3',
              altRowBg: '#fdf2f8',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#be185d',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(251, 113, 133, 0.3)',
              borderRadius: '6px'
            },
            autumn_orange: {
              gradient: 'linear-gradient(135deg, #9a3412 0%, #fb923c 100%)',
              headerBorder: '#ea580c',
              rowBorder: '#fed7aa',
              altRowBg: '#fff7ed',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#9a3412',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(251, 146, 60, 0.3)',
              borderRadius: '6px'
            }
          };
          return baseStyles[tableStyle] || baseStyles["default"];
        };
        var tableStyles = getTableStyles(element.tableStyle);
        var showHeaders = element.showHeaders !== false;
        var showBorders = element.showBorders !== false;
        var columns = element.columns || {
          image: false,
          name: true,
          sku: false,
          quantity: true,
          price: true,
          total: true
        };
        var headers = element.headers || ['Produit', 'Qté', 'Prix'];

        // Fonction pour obtenir l'en-tête d'une colonne
        var getColumnHeader = function getColumnHeader(columnType) {
          var defaultHeaders = {
            image: 'Img',
            name: headers[0] || 'Produit',
            sku: 'SKU',
            quantity: headers[1] || 'Qté',
            price: headers[2] || 'Prix',
            total: 'Total'
          };
          return defaultHeaders[columnType] || columnType;
        };

        // Données d'exemple pour l'aperçu
        var products = [{
          name: 'Produit A - Description',
          sku: 'SKU001',
          quantity: 2,
          price: 19.99,
          total: 39.98
        }, {
          name: 'Produit B - Article',
          sku: 'SKU002',
          quantity: 1,
          price: 29.99,
          total: 29.99
        }];

        // Calcul des totaux
        var subtotal = products.reduce(function (sum, product) {
          return sum + product.total;
        }, 0);
        var shipping = element.showShipping ? 5.00 : 0;
        var tax = element.showTaxes ? 2.25 : 0;
        var discount = element.showDiscount ? -5.00 : 0;
        var total = subtotal + shipping + tax + discount;

        // Déterminer la dernière colonne visible pour les totaux
        var getLastVisibleColumn = function getLastVisibleColumn() {
          var columnKeys = ['image', 'name', 'sku', 'quantity', 'price', 'total'];
          for (var i = columnKeys.length - 1; i >= 0; i--) {
            if (columns[columnKeys[i]] !== false) {
              return columnKeys[i];
            }
          }
          return 'total';
        };
        var lastVisibleColumn = getLastVisibleColumn();
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            display: 'flex',
            flexDirection: 'column',
            fontSize: "".concat((element.fontSize || 10) * zoom, "px"),
            fontFamily: element.fontFamily || 'Arial, sans-serif',
            border: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : element.borderWidth && element.borderWidth > 0 ? "".concat(Math.max(1, element.borderWidth * 0.5) * zoom, "px solid ").concat(element.borderColor || '#e5e7eb') : 'none',
            borderRadius: "".concat((element.borderRadius || 2) * zoom, "px"),
            overflow: 'hidden',
            backgroundColor: element.backgroundColor || 'transparent',
            boxSizing: 'border-box',
            boxShadow: tableStyles.shadow && element.tableStyle === 'modern' ? "0 4px 8px ".concat(tableStyles.shadow) : 'none'
          }
        }, showHeaders && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            background: tableStyles.gradient || tableStyles.headerBg,
            borderBottom: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none',
            fontWeight: 'bold',
            color: tableStyles.headerTextColor || (element.tableStyle === 'modern' ? '#ffffff' : '#000000'),
            boxShadow: tableStyles.shadow ? "0 2px 4px ".concat(tableStyles.shadow) : 'none'
          }
        }, columns.image && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: "0 0 ".concat(40 * zoom, "px"),
            padding: "".concat(4 * zoom, "px"),
            textAlign: 'center',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('image')), columns.name && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: 1,
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'left',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('name')), columns.sku && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: "0 0 ".concat(80 * zoom, "px"),
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'left',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('sku')), columns.quantity && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: "0 0 ".concat(60 * zoom, "px"),
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'center',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('quantity')), columns.price && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: "0 0 ".concat(80 * zoom, "px"),
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'right',
            borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, getColumnHeader('price')), columns.total && /*#__PURE__*/React.createElement("div", {
          style: {
            flex: '0 0 80px',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            textAlign: 'right'
          }
        }, getColumnHeader('total'))), /*#__PURE__*/React.createElement("div", {
          style: {
            flex: 1,
            display: 'flex',
            flexDirection: 'column'
          }
        }, products.map(function (product, index) {
          return /*#__PURE__*/React.createElement("div", {
            key: index,
            style: {
              display: 'flex',
              borderBottom: showBorders ? "".concat(tableStyles.borderWidth, "px solid ").concat(tableStyles.rowBorder) : 'none',
              backgroundColor: element.tableStyle === 'striped' && index % 2 === 1 ? tableStyles.altRowBg : 'transparent',
              color: tableStyles.rowTextColor || '#000000',
              boxShadow: tableStyles.shadow ? "0 1px 2px ".concat(tableStyles.shadow) : 'none'
            }
          }, columns.image && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(40 * zoom, "px"),
              padding: "".concat(4 * zoom, "px"),
              textAlign: 'center',
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, "\uD83D\uDCF7"), columns.name && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: 1,
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, product.name), columns.sku && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(80 * zoom, "px"),
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, product.sku), columns.quantity && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(60 * zoom, "px"),
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              textAlign: 'center',
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, product.quantity), columns.price && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(80 * zoom, "px"),
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              textAlign: 'right',
              borderRight: showBorders ? "".concat(tableStyles.borderWidth * zoom, "px solid ").concat(tableStyles.rowBorder) : 'none'
            }
          }, product.price.toFixed(2), "\u20AC"), columns.total && /*#__PURE__*/React.createElement("div", {
            style: {
              flex: "0 0 ".concat(80 * zoom, "px"),
              padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
              textAlign: 'right'
            }
          }, product.total.toFixed(2), "\u20AC"));
        })), /*#__PURE__*/React.createElement("div", {
          style: {
            borderTop: showBorders ? "".concat(tableStyles.borderWidth, "px solid ").concat(tableStyles.headerBorder) : 'none'
          }
        }, element.showSubtotal && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            fontWeight: 'bold'
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "Sous-total: ", subtotal.toFixed(2), "\u20AC")), element.showShipping && shipping > 0 && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "Port: ", shipping.toFixed(2), "\u20AC")), element.showTaxes && tax > 0 && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "TVA: ", tax.toFixed(2), "\u20AC")), element.showDiscount && discount < 0 && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px")
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "Remise: ", Math.abs(discount).toFixed(2), "\u20AC")), element.showTotal && /*#__PURE__*/React.createElement("div", {
          style: {
            display: 'flex',
            justifyContent: 'flex-end',
            padding: "".concat(4 * zoom, "px ").concat(6 * zoom, "px"),
            fontWeight: 'bold',
            background: tableStyles.gradient || tableStyles.headerBg,
            color: tableStyles.headerTextColor || (element.tableStyle === 'modern' ? '#ffffff' : '#000000'),
            boxShadow: tableStyles.shadow ? "0 2px 4px ".concat(tableStyles.shadow) : 'none'
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(80 * zoom, "px"),
            textAlign: 'right'
          }
        }, "TOTAL: ", total.toFixed(2), "\u20AC"))));
      case 'customer_info':
        // Rendu dynamique des informations client utilisant les propriétés de l'élément
        var customerFields = element.fields || ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'];
        var showLabels = element.showLabels !== false;
        var layout = element.layout || 'vertical';
        var alignment = element.alignment || 'left';
        var spacing = element.spacing || 3;

        // Données fictives pour l'aperçu (seront remplacées par les vraies données lors de la génération)
        var customerData = {
          name: 'Jean Dupont',
          company: 'ABC Company SARL',
          address: '123 Rue de la Paix\n75001 Paris\nFrance',
          email: 'jean.dupont@email.com',
          phone: '+33 6 12 34 56 78',
          tva: 'FR 12 345 678 901',
          siret: '123 456 789 00012',
          website: 'www.abc-company.com'
        };
        var containerStyle = {
          padding: "".concat(8 * zoom, "px"),
          fontSize: (element.fontSize || 12) * zoom,
          lineHeight: element.lineHeight || '1.4',
          color: element.color || '#1e293b',
          fontFamily: element.fontFamily || 'Inter, sans-serif',
          textAlign: alignment,
          display: layout === 'horizontal' ? 'flex' : 'block',
          flexWrap: layout === 'horizontal' ? 'wrap' : 'nowrap',
          gap: layout === 'horizontal' ? "".concat(spacing * zoom, "px") : '0'
        };
        return /*#__PURE__*/React.createElement("div", {
          style: containerStyle
        }, customerFields.map(function (field, index) {
          var fieldData = customerData[field];
          if (!fieldData) return null;
          var fieldStyle = layout === 'horizontal' ? {
            flex: '1',
            minWidth: "".concat(120 * zoom, "px")
          } : {
            marginBottom: index < customerFields.length - 1 ? "".concat(spacing * zoom, "px") : '0',
            display: 'flex',
            alignItems: 'flex-start'
          };
          return /*#__PURE__*/React.createElement("div", {
            key: field,
            style: fieldStyle
          }, showLabels && /*#__PURE__*/React.createElement("div", {
            style: {
              fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
              marginBottom: layout === 'horizontal' ? "".concat(2 * zoom, "px") : '0',
              marginRight: layout === 'horizontal' ? '0' : "".concat(8 * zoom, "px"),
              fontSize: "".concat(11 * zoom, "px"),
              opacity: 0.8,
              minWidth: layout === 'horizontal' ? 'auto' : "".concat(80 * zoom, "px"),
              flexShrink: 0
            }
          }, field === 'name' && 'Client', field === 'company' && 'Entreprise', field === 'address' && 'Adresse', field === 'email' && 'Email', field === 'phone' && 'Téléphone', field === 'tva' && 'N° TVA', field === 'siret' && 'SIRET', field === 'website' && 'Site web', ":"), /*#__PURE__*/React.createElement("div", {
            style: {
              whiteSpace: 'pre-line',
              fontSize: (element.fontSize || 12) * zoom,
              flex: layout === 'horizontal' ? '1' : 'auto'
            }
          }, fieldData));
        }));
      case 'company_info':
        // Rendu dynamique des informations entreprise utilisant les propriétés de l'élément
        var companyFields = element.fields || ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
        var showCompanyLabels = element.showLabels !== false;
        var companyLayout = element.layout || 'vertical';
        var companyAlignment = element.alignment || 'left';
        var companySpacing = element.spacing || 3;

        // Données fictives pour l'aperçu (seront remplacées par les vraies données lors de la génération)
        var companyData = {
          name: 'ABC Company SARL',
          address: '456 Avenue des Champs\n75008 Paris\nFrance',
          phone: '01 23 45 67 89',
          email: 'contact@abc-company.com',
          tva: 'FR 98 765 432 109',
          siret: '987 654 321 00098',
          rcs: 'Paris B 123 456 789',
          website: 'www.abc-company.com'
        };
        var companyContainerStyle = {
          padding: "".concat(8 * zoom, "px"),
          fontSize: (element.fontSize || 12) * zoom,
          lineHeight: element.lineHeight || '1.4',
          color: element.color || '#1e293b',
          fontFamily: element.fontFamily || 'Inter, sans-serif',
          textAlign: companyAlignment,
          display: companyLayout === 'horizontal' ? 'flex' : 'block',
          flexWrap: companyLayout === 'horizontal' ? 'wrap' : 'nowrap',
          gap: companyLayout === 'horizontal' ? "".concat(companySpacing * zoom, "px") : '0'
        };
        return /*#__PURE__*/React.createElement("div", {
          style: companyContainerStyle
        }, companyFields.map(function (field, index) {
          var fieldData = companyData[field];
          if (!fieldData) return null;
          var companyFieldStyle = companyLayout === 'horizontal' ? {
            flex: '1',
            minWidth: "".concat(120 * zoom, "px")
          } : {
            marginBottom: index < companyFields.length - 1 ? "".concat(companySpacing * zoom, "px") : '0',
            display: 'flex',
            alignItems: 'flex-start'
          };
          return /*#__PURE__*/React.createElement("div", {
            key: field,
            style: companyFieldStyle
          }, showCompanyLabels && /*#__PURE__*/React.createElement("div", {
            style: {
              fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
              marginBottom: companyLayout === 'horizontal' ? "".concat(2 * zoom, "px") : '0',
              marginRight: companyLayout === 'horizontal' ? '0' : "".concat(8 * zoom, "px"),
              fontSize: "".concat(11 * zoom, "px"),
              opacity: 0.8,
              minWidth: companyLayout === 'horizontal' ? 'auto' : "".concat(80 * zoom, "px"),
              flexShrink: 0
            }
          }, field === 'name' && 'Entreprise', field === 'address' && 'Adresse', field === 'phone' && 'Téléphone', field === 'email' && 'Email', field === 'tva' && 'N° TVA', field === 'siret' && 'SIRET', field === 'rcs' && 'RCS', field === 'website' && 'Site web', ":"), /*#__PURE__*/React.createElement("div", {
            style: {
              whiteSpace: 'pre-line',
              fontSize: (element.fontSize || 12) * zoom,
              flex: companyLayout === 'horizontal' ? '1' : 'auto'
            }
          }, fieldData));
        }));
      case 'company_logo':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: "".concat((element.padding || 8) * zoom, "px"),
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom,
            border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px solid ").concat(element.borderColor || '#e5e7eb') : 'none'
          }
        }, element.imageUrl || element.src ? /*#__PURE__*/React.createElement("img", {
          src: element.imageUrl || element.src,
          alt: element.alt || "Logo entreprise",
          style: {
            maxWidth: '100%',
            maxHeight: '100%',
            objectFit: element.objectFit || 'contain'
          }
        }) : /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            backgroundColor: '#f0f0f0',
            border: "".concat(2 * zoom, "px dashed #ccc"),
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#666',
            fontSize: (element.fontSize || 12) * zoom
          }
        }, "\uD83C\uDFE2 Logo"));
      case 'order_number':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            padding: "".concat((element.padding || 8) * zoom, "px"),
            fontSize: (element.fontSize || 14) * zoom,
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#333',
            fontFamily: element.fontFamily || 'Inter, sans-serif',
            textAlign: element.textAlign || 'left',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom,
            border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px solid ").concat(element.borderColor || '#e5e7eb') : 'none'
          }
        }, element.showLabel !== false && /*#__PURE__*/React.createElement("div", {
          style: {
            fontSize: (element.fontSize || 14) * 0.8 * zoom,
            color: element.labelColor || '#666',
            marginBottom: "".concat(2 * zoom, "px"),
            fontWeight: 'normal'
          }
        }, element.label || 'N° de commande', ":"), /*#__PURE__*/React.createElement("div", {
          style: {
            fontSize: (element.fontSize || 14) * zoom,
            fontWeight: element.fontWeight || 'bold'
          }
        }, element.prefix || 'CMD-', element.orderNumber || '2025-00123'));
      case 'document_type':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            padding: "".concat((element.padding || 8) * zoom, "px"),
            fontSize: "".concat((element.fontSize || 18) * zoom, "px"),
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#1e293b',
            fontFamily: element.fontFamily || 'Inter, sans-serif',
            textAlign: element.textAlign || 'center',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom
          }
        }, element.documentType === 'invoice' ? 'FACTURE' : element.documentType === 'quote' ? 'DEVIS' : element.documentType === 'receipt' ? 'REÇU' : element.documentType === 'order' ? 'COMMANDE' : element.documentType === 'credit_note' ? 'AVOIR' : 'DOCUMENT');
      case 'progress-bar':
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: (element.height || 20) * zoom,
            backgroundColor: element.backgroundColor || '#e5e7eb',
            borderRadius: (element.borderRadius || 10) * zoom,
            overflow: 'hidden',
            border: element.borderWidth ? "".concat(element.borderWidth * zoom, "px solid ").concat(element.borderColor || '#d1d5db') : 'none'
          }
        }, /*#__PURE__*/React.createElement("div", {
          style: {
            width: "".concat(Math.min(100, Math.max(0, element.progressValue || 75)), "%"),
            height: '100%',
            backgroundColor: element.progressColor || '#3b82f6',
            borderRadius: (element.borderRadius || 10) * zoom,
            transition: element.animate !== false ? 'width 0.3s ease' : 'none'
          }
        }));
      default:
        return /*#__PURE__*/React.createElement("div", {
          style: {
            width: '100%',
            height: '100%',
            backgroundColor: '#f0f0f0',
            border: "".concat(1 * zoom, "px dashed #ccc"),
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: "".concat(12 * zoom, "px"),
            color: '#666',
            padding: "".concat(4 * zoom, "px"),
            boxSizing: 'border-box'
          }
        }, element.type || 'Élément inconnu');
    }
  }, []);

  // Générer l'aperçu quand la modale s'ouvre
  (0,react.useEffect)(function () {
    if (isOpen && elements.length > 0) {
      if (useServerPreview) {
        // Utiliser l'aperçu unifié côté serveur
        generateServerPreview();
      } else {
        // Afficher immédiatement le contenu du canvas
        setPreviewData({
          success: true,
          elements_count: elements.length,
          width: 400,
          height: 566,
          fallback: false
        });
        // Puis générer l'aperçu côté serveur en arrière-plan
        generatePreview();
      }
    } else if (isOpen && elements.length === 0) {
      setPreviewData({
        success: true,
        elements_count: 0,
        width: 400,
        height: 566,
        fallback: false
      });
    }
  }, [isOpen, elements.length, useServerPreview]);
  var generatePreview = /*#__PURE__*/function () {
    var _ref4 = PreviewModal_asyncToGenerator(/*#__PURE__*/PreviewModal_regenerator().m(function _callee() {
      var _window$pdfBuilderAja, ajaxUrl, nonceFormData, nonceResponse, nonceData, freshNonce, _cleanElementsForJSON, validationResult, jsonString, cleanedElements, formData, response, data, responseText, errorMessage, _t, _t2;
      return PreviewModal_regenerator().w(function (_context) {
        while (1) switch (_context.p = _context.n) {
          case 0:
            // Ne pas définir loading=true car l'aperçu s'affiche déjà
            setError(null);
            _context.p = 1;
            // Vérifier que les variables AJAX sont disponibles
            ajaxUrl = ((_window$pdfBuilderAja = window.pdfBuilderAjax) === null || _window$pdfBuilderAja === void 0 ? void 0 : _window$pdfBuilderAja.ajaxurl) || ajaxurl;
            if (ajaxUrl) {
              _context.n = 2;
              break;
            }
            console.warn('Variables AJAX non disponibles pour validation côté serveur');
            return _context.a(2);
          case 2:
            // Obtenir un nonce frais
            nonceFormData = new FormData();
            nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');
            _context.n = 3;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: nonceFormData
            });
          case 3:
            nonceResponse = _context.v;
            if (nonceResponse.ok) {
              _context.n = 4;
              break;
            }
            console.warn('Erreur obtention nonce pour validation:', nonceResponse.status);
            return _context.a(2);
          case 4:
            _context.n = 5;
            return nonceResponse.json();
          case 5:
            nonceData = _context.v;
            if (nonceData.success) {
              _context.n = 6;
              break;
            }
            console.warn('Impossible d\'obtenir un nonce frais pour validation');
            return _context.a(2);
          case 6:
            freshNonce = nonceData.data.nonce; // Fonction pour nettoyer les éléments avant sérialisation JSON
            _cleanElementsForJSON = function _cleanElementsForJSON(elements) {
              return elements.map(function (element) {
                var cleaned = PreviewModal_objectSpread({}, element);

                // Supprimer les propriétés non sérialisables
                var propertiesToRemove = ['reactKey', 'tempId', 'style', '_internalId', 'ref', 'key'];
                propertiesToRemove.forEach(function (prop) {
                  delete cleaned[prop];
                });

                // Nettoyer récursivement tous les objets imbriqués
                var _cleanObject = function cleanObject(obj) {
                  if (obj === null || PreviewModal_typeof(obj) !== 'object') {
                    return obj;
                  }
                  if (Array.isArray(obj)) {
                    return obj.map(_cleanObject);
                  }
                  var cleanedObj = {};
                  for (var key in obj) {
                    if (obj.hasOwnProperty(key)) {
                      var value = obj[key];

                      // Ignorer les fonctions, symboles, et objets complexes
                      if (typeof value === 'function' || PreviewModal_typeof(value) === 'symbol' || PreviewModal_typeof(value) === 'object' && value !== null && !Array.isArray(value) && !(value instanceof Date) && !(value instanceof RegExp)) {
                        continue; // Skip this property
                      }

                      // Nettoyer récursivement
                      cleanedObj[key] = _cleanObject(value);
                    }
                  }
                  return cleanedObj;
                };

                // Appliquer le nettoyage récursif
                var fullyCleaned = _cleanObject(cleaned);

                // S'assurer que les propriétés numériques sont des nombres
                ['x', 'y', 'width', 'height', 'fontSize', 'borderWidth', 'borderRadius'].forEach(function (prop) {
                  if (fullyCleaned[prop] !== undefined && fullyCleaned[prop] !== null) {
                    var num = parseFloat(fullyCleaned[prop]);
                    if (!isNaN(num)) {
                      fullyCleaned[prop] = num;
                    } else {
                      delete fullyCleaned[prop]; // Supprimer si pas un nombre valide
                    }
                  }
                });

                // S'assurer que les propriétés boolean sont des booléens
                ['showLabels', 'showHeaders', 'showBorders', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'].forEach(function (prop) {
                  if (fullyCleaned[prop] !== undefined) {
                    fullyCleaned[prop] = Boolean(fullyCleaned[prop]);
                  }
                });

                // S'assurer que les chaînes sont des chaînes
                ['id', 'type', 'content', 'text', 'color', 'backgroundColor', 'borderColor', 'fontFamily', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'borderStyle'].forEach(function (prop) {
                  if (fullyCleaned[prop] !== undefined && fullyCleaned[prop] !== null) {
                    fullyCleaned[prop] = String(fullyCleaned[prop]);
                  }
                });
                return fullyCleaned;
              });
            }; // Validation côté client avant envoi
            validationResult = validateElementsBeforeSend(elements);
            if (validationResult.success) {
              _context.n = 7;
              break;
            }
            console.error('❌ Validation côté client échouée:', validationResult.error);
            setPreviewData(function (prev) {
              return PreviewModal_objectSpread(PreviewModal_objectSpread({}, prev), {}, {
                error: "Erreur de validation c\xF4t\xE9 client: ".concat(validationResult.error),
                isLoading: false
              });
            });
            return _context.a(2);
          case 7:
            jsonString = validationResult.jsonString, cleanedElements = validationResult.cleanedElements; // Préparer les données pour l'AJAX
            formData = new FormData();
            formData.append('action', 'pdf_builder_validate_preview');
            formData.append('nonce', freshNonce);
            formData.append('elements', jsonString);

            // Faire l'appel AJAX en arrière-plan
            _context.n = 8;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: formData
            });
          case 8:
            response = _context.v;
            if (response.ok) {
              _context.n = 9;
              break;
            }
            console.warn('Erreur HTTP validation aperçu:', response.status);
            return _context.a(2);
          case 9:
            _context.p = 9;
            _context.n = 10;
            return response.json();
          case 10:
            data = _context.v;
            _context.n = 13;
            break;
          case 11:
            _context.p = 11;
            _t = _context.v;
            console.error('❌ Erreur parsing JSON réponse serveur:', _t);
            _context.n = 12;
            return response.text();
          case 12:
            responseText = _context.v;
            console.error('Contenu brut de la réponse:', responseText.substring(0, 500));
            // Garder l'aperçu local mais marquer l'erreur
            setPreviewData(function (prev) {
              return PreviewModal_objectSpread(PreviewModal_objectSpread({}, prev), {}, {
                server_error: 'Réponse serveur invalide (pas du JSON)'
              });
            });
            return _context.a(2);
          case 13:
            if (data.success) {
              // Mettre à jour previewData avec les données du serveur si nécessaire
              setPreviewData(function (prev) {
                return PreviewModal_objectSpread(PreviewModal_objectSpread(PreviewModal_objectSpread({}, prev), data.data), {}, {
                  server_validated: true
                });
              });
            } else {
              console.warn('⚠️ Validation aperçu côté serveur échouée:', data.data);
              // Garder l'aperçu local mais marquer qu'il y a un problème serveur
              // S'assurer que server_error est toujours une chaîne
              errorMessage = 'Erreur validation serveur';
              if (typeof data.data === 'string') {
                errorMessage = data.data;
              } else if (data.data && PreviewModal_typeof(data.data) === 'object' && data.data.message) {
                errorMessage = data.data.message;
              } else if (data.data && PreviewModal_typeof(data.data) === 'object') {
                errorMessage = JSON.stringify(data.data);
              }
              setPreviewData(function (prev) {
                return PreviewModal_objectSpread(PreviewModal_objectSpread({}, prev), {}, {
                  server_error: errorMessage
                });
              });
            }
            _context.n = 15;
            break;
          case 14:
            _context.p = 14;
            _t2 = _context.v;
            console.warn('Erreur validation aperçu côté serveur:', _t2);
            // Ne pas afficher d'erreur car l'aperçu local fonctionne
            setPreviewData(function (prev) {
              return PreviewModal_objectSpread(PreviewModal_objectSpread({}, prev), {}, {
                server_error: _t2.message || 'Erreur inconnue côté serveur'
              });
            });
          case 15:
            return _context.a(2);
        }
      }, _callee, null, [[9, 11], [1, 14]]);
    }));
    return function generatePreview() {
      return _ref4.apply(this, arguments);
    };
  }();
  var generateServerPreview = /*#__PURE__*/function () {
    var _ref5 = PreviewModal_asyncToGenerator(/*#__PURE__*/PreviewModal_regenerator().m(function _callee2() {
      var fallbackTimeout, _window$pdfBuilderAja2, _window$pdfBuilderAja3, validationResult, jsonString, ajaxUrl, nonceFormData, nonceResponse, nonceData, freshNonce, formData, response, data, _t3;
      return PreviewModal_regenerator().w(function (_context2) {
        while (1) switch (_context2.p = _context2.n) {
          case 0:
            setLoading(true);
            setError(null);
            setPreviewData(null);

            // Timeout de fallback - si l'aperçu côté serveur prend trop de temps, afficher l'aperçu côté client
            fallbackTimeout = setTimeout(function () {
              setPreviewData({
                success: true,
                elements_count: elements.length,
                width: canvasWidth,
                height: canvasHeight,
                fallback: true,
                server_timeout: true
              });
              setLoading(false);
            }, 10000); // 10 secondes timeout
            _context2.p = 1;
            // Validation côté client avant envoi
            validationResult = validateElementsBeforeSend(elements);
            if (validationResult.success) {
              _context2.n = 2;
              break;
            }
            console.error('❌ Validation côté client échouée:', validationResult.error);
            setPreviewData(function (prev) {
              return PreviewModal_objectSpread(PreviewModal_objectSpread({}, prev), {}, {
                error: "Erreur de validation c\xF4t\xE9 client: ".concat(validationResult.error),
                isLoading: false
              });
            });
            return _context2.a(2);
          case 2:
            jsonString = validationResult.jsonString; // Vérifier que les variables AJAX sont disponibles
            ajaxUrl = ((_window$pdfBuilderAja2 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja2 === void 0 ? void 0 : _window$pdfBuilderAja2.ajaxurl) || ajaxurl;
            if (ajaxUrl) {
              _context2.n = 3;
              break;
            }
            alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
            return _context2.a(2);
          case 3:
            // Obtenir un nonce frais pour l'aperçu
            nonceFormData = new FormData();
            nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');
            _context2.n = 4;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: nonceFormData
            });
          case 4:
            nonceResponse = _context2.v;
            if (nonceResponse.ok) {
              _context2.n = 5;
              break;
            }
            throw new Error("Erreur HTTP nonce: ".concat(nonceResponse.status));
          case 5:
            _context2.n = 6;
            return nonceResponse.json();
          case 6:
            nonceData = _context2.v;
            if (nonceData.success) {
              _context2.n = 7;
              break;
            }
            throw new Error('Impossible d\'obtenir un nonce frais');
          case 7:
            freshNonce = nonceData.data.nonce; // Préparer les données pour l'AJAX unifié
            formData = new FormData();
            formData.append('action', 'pdf_builder_unified_preview');
            formData.append('nonce', freshNonce);
            formData.append('elements', jsonString);
            _context2.n = 8;
            return fetch(ajaxurl || ((_window$pdfBuilderAja3 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja3 === void 0 ? void 0 : _window$pdfBuilderAja3.ajaxurl) || '/wp-admin/admin-ajax.php', {
              method: 'POST',
              body: formData
            });
          case 8:
            response = _context2.v;
            if (response.ok) {
              _context2.n = 9;
              break;
            }
            throw new Error("Erreur HTTP: ".concat(response.status));
          case 9:
            _context2.n = 10;
            return response.json();
          case 10:
            data = _context2.v;
            if (!(data.success && data.data && data.data.url)) {
              _context2.n = 11;
              break;
            }
            // Nettoyer le timeout de fallback
            clearTimeout(fallbackTimeout);

            // Mettre à jour l'état pour afficher le PDF dans la modale
            setPreviewData({
              url: data.data.url,
              server_validated: true,
              elements_count: elements.length,
              width: canvasWidth,
              height: canvasHeight,
              zoom: zoom
            });
            setLoading(false);
            setError(null);

            // Ne pas ouvrir de nouvel onglet - le PDF s'affichera dans la modale
            return _context2.a(2);
          case 11:
            throw new Error(data.data || 'Erreur génération aperçu côté serveur');
          case 12:
            _context2.n = 14;
            break;
          case 13:
            _context2.p = 13;
            _t3 = _context2.v;
            console.error('❌ Erreur génération aperçu côté serveur:', _t3);
            // Nettoyer le timeout de fallback
            clearTimeout(fallbackTimeout);
            setError("Erreur aper\xE7u c\xF4t\xE9 serveur: ".concat(_t3.message));
            setLoading(false);
          case 14:
            return _context2.a(2);
        }
      }, _callee2, null, [[1, 13]]);
    }));
    return function generateServerPreview() {
      return _ref5.apply(this, arguments);
    };
  }();
  var handlePrint = /*#__PURE__*/function () {
    var _ref6 = PreviewModal_asyncToGenerator(/*#__PURE__*/PreviewModal_regenerator().m(function _callee3() {
      var printButton, _window$pdfBuilderAja4, ajaxUrl, nonceFormData, nonceResponse, nonceData, freshNonce, formData, originalText, response, data, errorMessage, pdfBase64, pdfBlob, pdfUrl, previewWindow, link, _t4;
      return PreviewModal_regenerator().w(function (_context3) {
        while (1) switch (_context3.p = _context3.n) {
          case 0:
            printButton = null;
            _context3.p = 1;
            // Vérifier que les variables AJAX sont disponibles
            ajaxUrl = ((_window$pdfBuilderAja4 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja4 === void 0 ? void 0 : _window$pdfBuilderAja4.ajaxurl) || ajaxurl;
            if (ajaxUrl) {
              _context3.n = 2;
              break;
            }
            alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
            return _context3.a(2);
          case 2:
            // Obtenir un nonce frais
            nonceFormData = new FormData();
            nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');
            _context3.n = 3;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: nonceFormData
            });
          case 3:
            nonceResponse = _context3.v;
            if (nonceResponse.ok) {
              _context3.n = 4;
              break;
            }
            throw new Error("Erreur HTTP nonce: ".concat(nonceResponse.status));
          case 4:
            _context3.n = 5;
            return nonceResponse.json();
          case 5:
            nonceData = _context3.v;
            if (nonceData.success) {
              _context3.n = 6;
              break;
            }
            throw new Error('Impossible d\'obtenir un nonce frais');
          case 6:
            freshNonce = nonceData.data.nonce; // Préparer les données pour l'AJAX
            formData = new FormData();
            formData.append('action', 'pdf_builder_generate_pdf');
            formData.append('nonce', freshNonce);
            formData.append('elements', JSON.stringify(elements));

            // Afficher un indicateur de chargement
            printButton = document.querySelector('.btn-primary');
            if (printButton) {
              originalText = printButton.textContent;
              printButton.textContent = '⏳ Génération PDF...';
              printButton.disabled = true;
            }

            // Envoyer la requête AJAX
            _context3.n = 7;
            return fetch(ajaxUrl, {
              method: 'POST',
              body: formData
            });
          case 7:
            response = _context3.v;
            if (response.ok) {
              _context3.n = 8;
              break;
            }
            throw new Error('Erreur réseau: ' + response.status);
          case 8:
            _context3.n = 9;
            return response.json()["catch"](function (jsonError) {
              console.error('Erreur parsing JSON:', jsonError);
              throw new Error('Réponse invalide du serveur (pas du JSON)');
            });
          case 9:
            data = _context3.v;
            if (data.success) {
              _context3.n = 10;
              break;
            }
            errorMessage = 'Erreur inconnue lors de la génération du PDF';
            if (typeof data.data === 'string') {
              errorMessage = data.data;
            } else if (PreviewModal_typeof(data.data) === 'object' && data.data !== null) {
              errorMessage = data.data.message || JSON.stringify(data.data);
            }
            throw new Error(errorMessage);
          case 10:
            if (!(!data.data || !data.data.pdf)) {
              _context3.n = 11;
              break;
            }
            throw new Error('Données PDF manquantes dans la réponse');
          case 11:
            // Convertir le PDF base64 en blob
            pdfBase64 = data.data.pdf;
            pdfBlob = new Blob([Uint8Array.from(atob(pdfBase64), function (c) {
              return c.charCodeAt(0);
            })], {
              type: 'application/pdf'
            });
            if (!(pdfBlob.size === 0)) {
              _context3.n = 12;
              break;
            }
            throw new Error('Le PDF généré est vide');
          case 12:
            // Créer un URL pour le blob PDF
            pdfUrl = URL.createObjectURL(pdfBlob); // Ouvrir le PDF dans une modale si la prop est fournie, sinon dans une nouvelle fenêtre
            if (onOpenPDFModal) {
              onOpenPDFModal(pdfUrl);
            } else {
              // Fallback vers l'ancienne méthode
              previewWindow = window.open(pdfUrl, '_blank');
              if (!previewWindow) {
                // Fallback si le popup est bloqué
                link = document.createElement('a');
                link.href = pdfUrl;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                document.body.appendChild(link);
                link.click();
                // Vérifier que l'élément existe encore avant de le supprimer
                if (link.parentNode === document.body) {
                  document.body.removeChild(link);
                }
              }
            }

            // Libérer l'URL du blob après un délai (seulement si pas en modale)
            if (!onOpenPDFModal) {
              setTimeout(function () {
                URL.revokeObjectURL(pdfUrl);
              }, 1000);
            }
            _context3.n = 14;
            break;
          case 13:
            _context3.p = 13;
            _t4 = _context3.v;
            console.error('Erreur génération PDF:', _t4);
            alert('Erreur lors de la génération du PDF: ' + _t4.message);
          case 14:
            _context3.p = 14;
            // Restaurer le bouton
            if (printButton) {
              printButton.textContent = '👁️ Imprimer PDF';
              printButton.disabled = false;
            }
            return _context3.f(14);
          case 15:
            return _context3.a(2);
        }
      }, _callee3, null, [[1, 13, 14, 15]]);
    }));
    return function handlePrint() {
      return _ref6.apply(this, arguments);
    };
  }();
  if (!isOpen) return null;
  return /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-overlay",
    onClick: onClose
  }, /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-content",
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-header"
  }, /*#__PURE__*/React.createElement("h3", null, "\uD83C\uDFA8 Aper\xE7u Canvas - PDF Builder Pro v2.0"), /*#__PURE__*/React.createElement("button", {
    className: "preview-modal-close",
    onClick: onClose
  }, "\xD7")), /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-body"
  }, loading && /*#__PURE__*/React.createElement("div", {
    className: "preview-loading"
  }, /*#__PURE__*/React.createElement("div", {
    className: "preview-spinner"
  }), /*#__PURE__*/React.createElement("p", null, "G\xE9n\xE9ration de l'aper\xE7u...")), error && /*#__PURE__*/React.createElement("div", {
    className: "preview-error"
  }, /*#__PURE__*/React.createElement("h4", null, "\u274C Erreur d'aper\xE7u"), /*#__PURE__*/React.createElement("p", null, error), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("small", null, "Le PDF pourra quand m\xEAme \xEAtre g\xE9n\xE9r\xE9 normalement."))), previewData && /*#__PURE__*/React.createElement("div", {
    className: "preview-content"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      marginBottom: '20px',
      padding: '10px',
      background: previewData.server_validated ? '#e8f5e8' : '#fff3cd',
      borderRadius: '4px',
      border: "1px solid ".concat(previewData.server_validated ? '#c3e6c3' : '#ffeaa7')
    }
  }, /*#__PURE__*/React.createElement("strong", null, previewData.server_validated ? '✅' : '⚡', " Aper\xE7u g\xE9n\xE9r\xE9"), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("small", null, previewData.elements_count, " \xE9l\xE9ment", previewData.elements_count !== 1 ? 's' : '', " \u2022 ", previewData.width, "\xD7", previewData.height, "px", previewData.server_validated && ' • Serveur validé', previewData.server_error && ' • ⚠️ Problème serveur')), /*#__PURE__*/React.createElement("div", {
    style: {
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'flex-start',
      minHeight: '400px',
      backgroundColor: '#f8f9fa',
      borderRadius: '8px',
      padding: '20px'
    }
  }, previewData.url ?
  /*#__PURE__*/
  // Aperçu côté serveur - afficher le PDF dans un iframe
  React.createElement("iframe", {
    src: previewData.url,
    style: {
      width: '100%',
      height: '600px',
      border: '1px solid #dee2e6',
      borderRadius: '4px',
      backgroundColor: 'white'
    },
    title: "Aper\xE7u PDF c\xF4t\xE9 serveur"
  }) :
  // Aperçu côté client - rendre le HTML
  renderCanvasContent(elements)), previewData.server_error && /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: '20px',
      padding: '15px',
      backgroundColor: '#ffeaa7',
      borderRadius: '6px',
      border: '1px solid #d4a574'
    }
  }, /*#__PURE__*/React.createElement("h5", {
    style: {
      margin: '0 0 10px 0',
      color: '#856404'
    }
  }, "\u26A0\uFE0F Note"), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: '0',
      fontSize: '14px',
      color: '#333'
    }
  }, "L'aper\xE7u s'affiche correctement, mais il y a un probl\xE8me de validation c\xF4t\xE9 serveur: ", previewData.server_error)), /*#__PURE__*/React.createElement("div", {
    style: {
      marginTop: '20px',
      padding: '15px',
      backgroundColor: '#e8f4fd',
      borderRadius: '6px',
      border: '1px solid #b3d9ff'
    }
  }, /*#__PURE__*/React.createElement("h5", {
    style: {
      margin: '0 0 10px 0',
      color: '#0066cc'
    }
  }, "\u2139\uFE0F Informations du Canvas"), /*#__PURE__*/React.createElement("p", {
    style: {
      margin: '0',
      fontSize: '14px',
      color: '#333'
    }
  }, /*#__PURE__*/React.createElement("strong", null, "Dimensions:"), " ", canvasWidth, " \xD7 ", canvasHeight, " pixels", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("strong", null, "\xC9l\xE9ments:"), " ", elements.length, /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("strong", null, "Zoom:"), " ", Math.round(zoom * 100), "%", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("strong", null, "Status:"), " ", previewData.server_validated ? 'Validé côté serveur' : 'Aperçu local'))), !loading && !error && !previewData && /*#__PURE__*/React.createElement("div", {
    className: "preview-loading"
  }, /*#__PURE__*/React.createElement("p", null, "Pr\xE9paration de l'aper\xE7u..."))), /*#__PURE__*/React.createElement("div", {
    className: "preview-modal-footer"
  }, /*#__PURE__*/React.createElement("button", {
    className: "btn btn-secondary",
    onClick: onClose
  }, "\u274C Fermer"), /*#__PURE__*/React.createElement("button", {
    className: "btn btn-primary",
    onClick: handlePrint
  }, "\uD83D\uDC41\uFE0F Imprimer PDF"))));
};
/* harmony default export */ const components_PreviewModal = (PreviewModal);
;// ./src/components/ModalPDFViewer.jsx
var ModalPDFViewer = function ModalPDFViewer(_ref) {
  var isOpen = _ref.isOpen,
    onClose = _ref.onClose,
    pdfUrl = _ref.pdfUrl,
    _ref$title = _ref.title,
    title = _ref$title === void 0 ? 'Aperçu PDF' : _ref$title;
  if (!isOpen) return null;
  return /*#__PURE__*/React.createElement("div", {
    style: {
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      backgroundColor: 'rgba(0, 0, 0, 0.7)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 9999
    },
    onClick: onClose
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      backgroundColor: 'white',
      borderRadius: '8px',
      width: '90%',
      height: '90%',
      maxWidth: '1200px',
      maxHeight: '800px',
      display: 'flex',
      flexDirection: 'column',
      boxShadow: '0 10px 30px rgba(0, 0, 0, 0.3)'
    },
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '16px 20px',
      borderBottom: '1px solid #e2e8f0',
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      backgroundColor: '#f8f9fa',
      borderRadius: '8px 8px 0 0'
    }
  }, /*#__PURE__*/React.createElement("h3", {
    style: {
      margin: 0,
      color: '#1a202c',
      fontSize: '18px',
      fontWeight: '600'
    }
  }, title), /*#__PURE__*/React.createElement("button", {
    onClick: onClose,
    style: {
      background: 'none',
      border: 'none',
      fontSize: '24px',
      cursor: 'pointer',
      color: '#718096',
      padding: '4px 8px',
      borderRadius: '4px',
      transition: 'all 0.2s'
    },
    onMouseOver: function onMouseOver(e) {
      return e.target.style.backgroundColor = '#e2e8f0';
    },
    onMouseOut: function onMouseOut(e) {
      return e.target.style.backgroundColor = 'transparent';
    }
  }, "\xD7")), /*#__PURE__*/React.createElement("div", {
    style: {
      flex: 1,
      padding: '0',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: '#f5f5f5'
    }
  }, pdfUrl ? /*#__PURE__*/React.createElement("iframe", {
    src: pdfUrl,
    style: {
      width: '100%',
      height: '100%',
      border: 'none',
      borderRadius: '0 0 8px 8px'
    },
    title: title
  }) : /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: 'center',
      color: '#718096',
      fontSize: '16px'
    }
  }, "Chargement du PDF...")), /*#__PURE__*/React.createElement("div", {
    style: {
      padding: '16px 20px',
      borderTop: '1px solid #e2e8f0',
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      backgroundColor: '#f8f9fa',
      borderRadius: '0 0 8px 8px'
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontSize: '14px',
      color: '#718096'
    }
  }, "Utilisez Ctrl+P (Cmd+P sur Mac) pour imprimer"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("button", {
    onClick: function onClick() {
      if (pdfUrl) {
        var link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'document.pdf';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        // Vérifier que l'élément existe encore avant de le supprimer
        if (link.parentNode === document.body) {
          document.body.removeChild(link);
        }
      }
    },
    style: {
      backgroundColor: '#4299e1',
      color: 'white',
      border: 'none',
      padding: '8px 16px',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '14px',
      marginRight: '8px',
      transition: 'background-color 0.2s'
    },
    onMouseOver: function onMouseOver(e) {
      return e.target.style.backgroundColor = '#3182ce';
    },
    onMouseOut: function onMouseOut(e) {
      return e.target.style.backgroundColor = '#4299e1';
    }
  }, "\uD83D\uDCE5 T\xE9l\xE9charger"), /*#__PURE__*/React.createElement("button", {
    onClick: onClose,
    style: {
      backgroundColor: '#e2e8f0',
      color: '#4a5568',
      border: 'none',
      padding: '8px 16px',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '14px'
    }
  }, "Fermer")))));
};
/* harmony default export */ const components_ModalPDFViewer = (ModalPDFViewer);
;// ./src/components/WooCommerceElements.jsx



/**
 * Fonction utilitaire pour obtenir le label d'un élément WooCommerce
 */
var getElementLabel = function getElementLabel(type) {
  var labels = {
    'woocommerce-invoice-number': 'Numéro Facture',
    'woocommerce-invoice-date': 'Date Facture',
    'woocommerce-order-number': 'N° Commande',
    'woocommerce-order-date': 'Date Commande',
    'woocommerce-billing-address': 'Adresse Facturation',
    'woocommerce-shipping-address': 'Adresse Livraison',
    'woocommerce-customer-name': 'Nom Client',
    'woocommerce-customer-email': 'Email Client',
    'woocommerce-payment-method': 'Paiement',
    'woocommerce-order-status': 'Statut',
    'woocommerce-products-table': 'Tableau Produits',
    'woocommerce-products-simple': 'Liste Produits',
    'woocommerce-subtotal': 'Sous-total',
    'woocommerce-discount': 'Remise',
    'woocommerce-shipping': 'Livraison',
    'woocommerce-taxes': 'Taxes',
    'woocommerce-total': 'Total',
    'woocommerce-refund': 'Remboursement',
    'woocommerce-fees': 'Frais',
    'woocommerce-quote-number': 'N° Devis',
    'woocommerce-quote-date': 'Date Devis',
    'woocommerce-quote-validity': 'Validité',
    'woocommerce-quote-notes': 'Notes Devis'
  };
  return labels[type] || 'Élément WC';
};

/**
 * Hook personnalisé pour gérer les éléments WooCommerce
 */
var useWooCommerceElements = function useWooCommerceElements() {
  var getElementDefaults = function getElementDefaults(type) {
    var defaults = {
      width: 200,
      height: 60,
      fontSize: 14,
      fontFamily: 'Arial, sans-serif',
      color: '#333333',
      backgroundColor: '#ffffff',
      borderColor: '#dddddd',
      borderWidth: 1,
      borderStyle: 'solid',
      borderRadius: 4,
      padding: 8
    };

    // Ajustements spécifiques selon le type
    switch (type) {
      case 'woocommerce-billing-address':
      case 'woocommerce-shipping-address':
        defaults.height = 100;
        break;
      case 'woocommerce-products-table':
        defaults.width = 400;
        defaults.height = 150;
        defaults.columns = {
          image: true,
          name: true,
          sku: true,
          quantity: true,
          price: true,
          total: true
        };
        break;
      case 'woocommerce-products-simple':
        defaults.width = 350;
        defaults.height = 120;
        break;
      case 'woocommerce-invoice-number':
      case 'woocommerce-order-number':
      case 'woocommerce-quote-number':
        defaults.width = 150;
        defaults.height = 40;
        break;
      default:
        break;
    }
    return defaults;
  };
  var validateElement = function validateElement(element) {
    // Validation basique des propriétés requises
    return element && element.type && element.id;
  };
  return {
    getElementDefaults: getElementDefaults,
    validateElement: validateElement,
    getElementLabel: getElementLabel
  };
};

/**
 * Composant pour gérer les éléments WooCommerce dans le canvas
 * Ce composant gère l'affichage et le rendu des éléments WooCommerce
 */
var WooCommerceElement = function WooCommerceElement(_ref) {
  var element = _ref.element,
    isSelected = _ref.isSelected,
    onSelect = _ref.onSelect,
    onUpdate = _ref.onUpdate,
    dragAndDrop = _ref.dragAndDrop,
    _ref$zoom = _ref.zoom,
    zoom = _ref$zoom === void 0 ? 1 : _ref$zoom,
    canvasWidth = _ref.canvasWidth,
    canvasHeight = _ref.canvasHeight,
    _ref$orderData = _ref.orderData,
    orderData = _ref$orderData === void 0 ? {} : _ref$orderData,
    onContextMenu = _ref.onContextMenu,
    _ref$snapToGrid = _ref.snapToGrid,
    snapToGrid = _ref$snapToGrid === void 0 ? true : _ref$snapToGrid,
    _ref$gridSize = _ref.gridSize,
    gridSize = _ref$gridSize === void 0 ? 10 : _ref$gridSize;
  var elementRef = (0,react.useRef)(null);
  var resize = useResize({
    onElementResize: function onElementResize(newRect) {
      onUpdate({
        x: newRect.x,
        y: newRect.y,
        width: newRect.width,
        height: newRect.height
      });
    },
    snapToGrid: snapToGrid,
    gridSize: gridSize,
    canvasWidth: canvasWidth,
    canvasHeight: canvasHeight
  });
  var handleMouseDown = function handleMouseDown(e) {
    e.stopPropagation();

    // Calculer les coordonnées relatives au canvas (en tenant compte du zoom)
    var canvas = elementRef.current.closest('.canvas-zoom-wrapper');
    if (!canvas) return;
    var canvasRect = canvas.getBoundingClientRect();
    var elementRect = elementRef.current.getBoundingClientRect();

    // Ajuster pour le zoom - les coordonnées doivent être relatives au canvas non-zoomé
    var clickX = (e.clientX - canvasRect.left) / zoom;
    var clickY = (e.clientY - canvasRect.top) / zoom;
    var handleSize = 8 / zoom; // Ajuster la taille des poignées pour le zoom

    // Poignées de redimensionnement (coordonnées relatives au canvas)
    var handles = [{
      name: 'nw',
      x: element.x,
      y: element.y
    }, {
      name: 'ne',
      x: element.x + element.width,
      y: element.y
    }, {
      name: 'sw',
      x: element.x,
      y: element.y + element.height
    }, {
      name: 'se',
      x: element.x + element.width,
      y: element.y + element.height
    }, {
      name: 'n',
      x: element.x + element.width / 2,
      y: element.y
    }, {
      name: 's',
      x: element.x + element.width / 2,
      y: element.y + element.height
    }, {
      name: 'w',
      x: element.x,
      y: element.y + element.height / 2
    }, {
      name: 'e',
      x: element.x + element.width,
      y: element.y + element.height / 2
    }];
    for (var _i = 0, _handles = handles; _i < _handles.length; _i++) {
      var handle = _handles[_i];
      if (clickX >= handle.x - handleSize / 2 && clickX <= handle.x + handleSize / 2 && clickY >= handle.y - handleSize / 2 && clickY <= handle.y + handleSize / 2) {
        resize.handleResizeStart(e, handle.name, {
          x: element.x,
          y: element.y,
          width: element.width,
          height: element.height
        }, canvasRect, zoom);
        return;
      }
    }

    // Si on clique ailleurs sur l'élément, commencer le drag
    if (dragAndDrop && dragAndDrop.handleMouseDown) {
      var _canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      var _canvasRect = _canvas.getBoundingClientRect();
      dragAndDrop.handleMouseDown(e, element.id, {
        left: element.x,
        top: element.y,
        width: element.width,
        height: element.height
      }, _canvasRect, zoom);
    }
  };
  var handleClick = function handleClick(e) {
    e.stopPropagation();
    if (!isSelected) {
      onSelect(element.id);
    }
  };
  var baseStyle = {
    position: 'absolute',
    left: element.x * zoom,
    top: element.y * zoom,
    width: element.width * zoom,
    height: element.height * zoom,
    cursor: isSelected ? 'move' : 'pointer',
    userSelect: 'none',
    border: isSelected ? "2px solid #007cba" : element.borderWidth > 0 ? "".concat(element.borderWidth || 1, "px ").concat(element.borderStyle || 'solid', " ").concat(element.borderColor || 'transparent') : 'none',
    backgroundColor: element.backgroundColor || 'transparent',
    color: element.color || '#333333',
    fontSize: (element.fontSize || 14) * zoom,
    fontFamily: element.fontFamily || 'Arial, sans-serif',
    padding: (element.padding || 8) * zoom,
    borderRadius: (element.borderRadius || 4) * zoom,
    boxSizing: 'border-box',
    overflow: 'hidden',
    '--element-border-width': isSelected ? '2px' : element.borderWidth > 0 ? "".concat(element.borderWidth || 1, "px") : '0px'
  };

  // Fonction pour obtenir le contenu dynamique selon le type d'élément
  var getElementContent = function getElementContent(type) {
    switch (type) {
      case 'woocommerce-invoice-number':
        return orderData.invoice_number || 'INV-001';
      case 'woocommerce-invoice-date':
        return orderData.invoice_date || '15/10/2025';
      case 'woocommerce-order-number':
        return orderData.order_number || '#12345';
      case 'woocommerce-order-date':
        return orderData.order_date || '15/10/2025';
      case 'woocommerce-customer-name':
        return orderData.customer_name || 'John Doe';
      case 'woocommerce-customer-email':
        return orderData.customer_email || 'john.doe@example.com';
      case 'woocommerce-billing-address':
        return orderData.billing_address || '123 Rue de Test\n75001 Paris\nFrance';
      case 'woocommerce-shipping-address':
        return orderData.shipping_address || '456 Rue de Livraison\n75002 Paris\nFrance';
      case 'woocommerce-payment-method':
        return orderData.payment_method || 'Carte bancaire';
      case 'woocommerce-order-status':
        return orderData.order_status || 'Traitée';
      case 'woocommerce-subtotal':
        return orderData.subtotal || '45,00 €';
      case 'woocommerce-discount':
        return orderData.discount || '-5,00 €';
      case 'woocommerce-shipping':
        return orderData.shipping || '5,00 €';
      case 'woocommerce-taxes':
        return orderData.tax || '9,00 €';
      case 'woocommerce-total':
        return orderData.total || '54,00 €';
      case 'woocommerce-refund':
        return orderData.refund || '0,00 €';
      case 'woocommerce-fees':
        return orderData.fees || '1,50 €';
      case 'woocommerce-quote-number':
        return orderData.quote_number || 'QUO-001';
      case 'woocommerce-quote-date':
        return orderData.quote_date || '15/10/2025';
      case 'woocommerce-quote-validity':
        return orderData.quote_validity || '30 jours';
      case 'woocommerce-quote-notes':
        return orderData.quote_notes || 'Conditions spéciales : paiement à 30 jours.';
      case 'woocommerce-products-table':
        if (orderData.products && orderData.products.length > 0) {
          return orderData.products.map(function (product) {
            return "".concat(product.name, " x").concat(product.quantity, " - ").concat(product.total);
          }).join('\n');
        }
        return 'Produit Test 1 x1 - 25,00 €\nProduit Test 2 x2 - 20,00 €';
      case 'woocommerce-products-simple':
        if (orderData.products && orderData.products.length > 0) {
          return orderData.products.map(function (product) {
            return "".concat(product.quantity, "x ").concat(product.name);
          }).join('\n');
        }
        return '1x Produit Test 1\n2x Produit Test 2';
      default:
        return '[Contenu dynamique WooCommerce]';
    }
  };
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    ref: elementRef,
    style: baseStyle,
    onClick: handleClick,
    onMouseDown: handleMouseDown,
    onContextMenu: onContextMenu
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      fontWeight: 'bold',
      textTransform: 'uppercase',
      letterSpacing: '0.5px'
    }
  }, getElementLabel(element.type)), /*#__PURE__*/React.createElement("div", {
    style: {
      whiteSpace: 'pre-line',
      lineHeight: '1.4',
      color: '#666'
    }
  }, getElementContent(element.type))), isSelected && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "resize-handle nw",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'nw', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle ne",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'ne', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle sw",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'sw', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle se",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'se', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle n",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'n', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle s",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 's', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle w",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'w', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "resize-handle e",
    style: {
      position: 'absolute',
      width: 8,
      height: 8,
      backgroundColor: '#007cba',
      border: '1px solid white',
      pointerEvents: 'auto'
    },
    onMouseDown: function onMouseDown(e) {
      return resize.handleResizeStart(e, 'e', {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      });
    }
  })));
};
/* harmony default export */ const WooCommerceElements = (WooCommerceElement);
;// ./src/components/ElementLibrary.jsx
function ElementLibrary_typeof(o) { "@babel/helpers - typeof"; return ElementLibrary_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, ElementLibrary_typeof(o); }
function ElementLibrary_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function ElementLibrary_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ElementLibrary_ownKeys(Object(t), !0).forEach(function (r) { ElementLibrary_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ElementLibrary_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function ElementLibrary_defineProperty(e, r, t) { return (r = ElementLibrary_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function ElementLibrary_toPropertyKey(t) { var i = ElementLibrary_toPrimitive(t, "string"); return "symbol" == ElementLibrary_typeof(i) ? i : i + ""; }
function ElementLibrary_toPrimitive(t, r) { if ("object" != ElementLibrary_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != ElementLibrary_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function ElementLibrary_slicedToArray(r, e) { return ElementLibrary_arrayWithHoles(r) || ElementLibrary_iterableToArrayLimit(r, e) || ElementLibrary_unsupportedIterableToArray(r, e) || ElementLibrary_nonIterableRest(); }
function ElementLibrary_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function ElementLibrary_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return ElementLibrary_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? ElementLibrary_arrayLikeToArray(r, a) : void 0; } }
function ElementLibrary_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function ElementLibrary_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function ElementLibrary_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var ElementLibrary = function ElementLibrary(_ref) {
  var onAddElement = _ref.onAddElement,
    selectedTool = _ref.selectedTool,
    onToolSelect = _ref.onToolSelect;
  var _useState = (0,react.useState)(false),
    _useState2 = ElementLibrary_slicedToArray(_useState, 2),
    showHeaderTemplatesModal = _useState2[0],
    setShowHeaderTemplatesModal = _useState2[1];

  // Bibliothèque d'éléments - boutons simples
  var elements = [{
    type: 'product_table',
    fieldID: 'products_table',
    label: 'Tableau Produits',
    icon: '📋',
    description: 'Tableau des produits commandés',
    defaultProperties: {
      showHeaders: true,
      showBorders: false,
      headers: ['Produit', 'Qté', 'Prix'],
      dataSource: 'order_items',
      tableStyle: 'default',
      columns: {
        image: true,
        name: true,
        sku: false,
        quantity: true,
        price: true,
        total: true
      },
      showSubtotal: false,
      showShipping: true,
      showTaxes: true,
      showDiscount: false,
      showTotal: false
    }
  }, {
    type: 'customer_info',
    fieldID: 'customer_info',
    label: 'Fiche Client',
    icon: '👤',
    description: 'Informations détaillées du client',
    defaultProperties: {
      showHeaders: true,
      showBorders: false,
      fields: ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'],
      layout: 'vertical',
      // 'vertical' ou 'horizontal'
      showLabels: true,
      labelStyle: 'bold',
      // 'normal', 'bold', 'uppercase'
      spacing: 8 // espacement en pixels
    }
  }, {
    type: 'company_logo',
    fieldID: 'company_logo',
    label: 'Logo Entreprise',
    icon: '🏢',
    description: 'Logo et identité visuelle de l\'entreprise',
    defaultProperties: {
      imageUrl: '',
      width: 150,
      height: 80,
      alignment: 'left',
      // 'left', 'center', 'right'
      fit: 'contain',
      // 'contain', 'cover', 'fill'
      showBorder: false,
      borderRadius: 0
    }
  }, {
    type: 'company_info',
    fieldID: 'company_info',
    label: 'Informations Entreprise',
    icon: '[D]',
    description: 'Nom, adresse, contact et TVA de l\'entreprise',
    defaultProperties: {
      showHeaders: false,
      showBorders: false,
      fields: ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'],
      layout: 'vertical',
      showLabels: false,
      labelStyle: 'normal',
      spacing: 4,
      fontSize: 12,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      textAlign: 'left' // 'left', 'center', 'right'
    }
  }, {
    type: 'order_number',
    fieldID: 'order_number',
    label: 'Numéro de Commande',
    icon: '🔢',
    description: 'Référence de commande avec date',
    defaultProperties: {
      showHeaders: false,
      showBorders: false,
      format: 'Commande #{order_number} - {order_date}',
      fontSize: 14,
      fontFamily: 'Arial',
      fontWeight: 'bold',
      textAlign: 'right',
      color: '#333333',
      showLabel: true,
      labelText: 'N° de commande:'
    }
  }, {
    type: 'dynamic-text',
    fieldID: 'dynamic_text',
    label: 'Texte Dynamique',
    icon: '�',
    description: 'Texte avec variables dynamiques',
    defaultProperties: {
      content: '{{order_total}} €',
      fontSize: 14,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      textAlign: 'left',
      color: '#333333'
    }
  }, {
    type: 'order_totals',
    fieldID: 'order_totals',
    label: 'Totaux Commande',
    icon: '💰',
    description: 'Sous-total, TVA, frais de port, total',
    defaultProperties: {
      showSubtotal: true,
      showShipping: true,
      showTaxes: true,
      showDiscount: true,
      showTotal: true,
      layout: 'vertical',
      fontSize: 12,
      fontFamily: 'Arial',
      fontWeight: 'normal',
      labelAlign: 'left',
      valueAlign: 'right',
      spacing: 4
    }
  }];
  var handleHeaderTemplateSelect = function handleHeaderTemplateSelect(template) {
    // Ici on peut ajouter la logique pour appliquer le modèle sélectionné
    setShowHeaderTemplatesModal(false);
    // Appliquer le contenu du modèle sélectionné
    onAddElement('text', {
      x: 50,
      y: 50,
      width: 300,
      height: 60,
      text: template.preview.replace('\\n', '\n'),
      fontSize: template.fontSize || 16,
      fontWeight: template.fontWeight || 'normal'
    });
  };
  var headerTemplates = [{
    id: 'classic',
    name: 'Classique',
    preview: '🏢 ENTREPRISE\n123 Rue de la Paix\n75000 Paris',
    fontSize: 14,
    fontWeight: 'bold'
  }, {
    id: 'modern',
    name: 'Moderne',
    preview: '✨ ENTREPRISE MODERNE\nInnovation & Qualité\ncontact@entreprise.com',
    fontSize: 16,
    fontWeight: 'bold'
  }, {
    id: 'minimal',
    name: 'Minimal',
    preview: 'ENTREPRISE\nAdresse • Téléphone • Email',
    fontSize: 12,
    fontWeight: 'normal'
  }, {
    id: 'elegant',
    name: 'Élégant',
    preview: '🎩 Maison Élégante\nParis, France\nwww.entreprise.com',
    fontSize: 15,
    fontWeight: 'bold'
  }];

  // Gestionnaire pour le drag start - REMOVED: plus d'éléments à dragger

  return /*#__PURE__*/React.createElement(React.Fragment, null, showHeaderTemplatesModal && /*#__PURE__*/React.createElement("div", {
    className: "modal-overlay",
    onClick: function onClick() {
      return setShowHeaderTemplatesModal(false);
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal-content",
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "modal-header"
  }, /*#__PURE__*/React.createElement("h3", null, "\uD83C\uDFA8 Choisir un mod\xE8le d'en-t\xEAte"), /*#__PURE__*/React.createElement("button", {
    className: "modal-close",
    onClick: function onClick() {
      return setShowHeaderTemplatesModal(false);
    }
  }, "\xD7")), /*#__PURE__*/React.createElement("div", {
    className: "modal-body"
  }, /*#__PURE__*/React.createElement("div", {
    className: "templates-grid"
  }, headerTemplates.map(function (template) {
    return /*#__PURE__*/React.createElement("div", {
      key: template.id,
      className: "template-item",
      onClick: function onClick() {
        return handleHeaderTemplateSelect(template);
      }
    }, /*#__PURE__*/React.createElement("div", {
      className: "template-preview"
    }, template.preview.split('\n').map(function (line, index) {
      return /*#__PURE__*/React.createElement("div", {
        key: index,
        style: {
          fontSize: template.fontSize,
          fontWeight: template.fontWeight,
          marginBottom: '4px',
          whiteSpace: 'pre-wrap'
        }
      }, line);
    })), /*#__PURE__*/React.createElement("div", {
      className: "template-name"
    }, template.name));
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "element-library"
  }, /*#__PURE__*/React.createElement("div", {
    className: "library-header"
  }, /*#__PURE__*/React.createElement("h3", null, "\uD83D\uDCDA Biblioth\xE8que d'\xC9l\xE9ments"), /*#__PURE__*/React.createElement("p", {
    className: "library-subtitle"
  }, "Cliquez sur les blocs pour les ajouter au canvas")), /*#__PURE__*/React.createElement("div", {
    className: "library-content"
  }, /*#__PURE__*/React.createElement("div", {
    className: "elements-list"
  }, elements.map(function (element, index) {
    return /*#__PURE__*/React.createElement("button", {
      key: index,
      className: "element-button",
      onClick: function onClick() {
        onAddElement(element.type, ElementLibrary_objectSpread({
          x: 50 + index * 20,
          y: 100 + index * 20,
          width: 300,
          height: 150
        }, element.defaultProperties));
      },
      title: element.description
    }, /*#__PURE__*/React.createElement("span", {
      className: "element-icon"
    }, element.icon), /*#__PURE__*/React.createElement("div", {
      className: "element-info"
    }, /*#__PURE__*/React.createElement("div", {
      className: "element-label"
    }, element.label), /*#__PURE__*/React.createElement("div", {
      className: "element-description"
    }, element.description)));
  })))));
};
/* harmony default export */ const components_ElementLibrary = (ElementLibrary);
;// ./src/services/ElementCustomizationService.js
function ElementCustomizationService_typeof(o) { "@babel/helpers - typeof"; return ElementCustomizationService_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, ElementCustomizationService_typeof(o); }
function ElementCustomizationService_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function ElementCustomizationService_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ElementCustomizationService_ownKeys(Object(t), !0).forEach(function (r) { ElementCustomizationService_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ElementCustomizationService_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function ElementCustomizationService_defineProperty(e, r, t) { return (r = ElementCustomizationService_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, ElementCustomizationService_toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function ElementCustomizationService_toPropertyKey(t) { var i = ElementCustomizationService_toPrimitive(t, "string"); return "symbol" == ElementCustomizationService_typeof(i) ? i : i + ""; }
function ElementCustomizationService_toPrimitive(t, r) { if ("object" != ElementCustomizationService_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != ElementCustomizationService_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * Service de personnalisation des éléments
 * Fournit des utilitaires pour la gestion des propriétés d'éléments
 */
var ElementCustomizationService = /*#__PURE__*/function () {
  function ElementCustomizationService() {
    _classCallCheck(this, ElementCustomizationService);
    this.propertyValidators = new Map();
    this.propertyPresets = new Map();
    this.propertyGroups = new Map();
    this.initDefaults();
  }

  /**
   * Initialise les validateurs, presets et groupes par défaut
   */
  return _createClass(ElementCustomizationService, [{
    key: "initDefaults",
    value: function initDefaults() {
      var _this = this;
      // Validateurs de propriétés
      this.propertyValidators.set('numeric', function (value) {
        return parseFloat(value) || 0;
      });
      this.propertyValidators.set('positiveNumeric', function (value) {
        return Math.max(0, parseFloat(value) || 0);
      });
      this.propertyValidators.set('percentage', function (value) {
        return Math.max(0, Math.min(100, parseFloat(value) || 100));
      });
      this.propertyValidators.set('angle', function (value) {
        return ((parseFloat(value) || 0) % 360 + 360) % 360;
      });
      this.propertyValidators.set('color', function (value) {
        return _this.validateColor(value);
      });
      this.propertyValidators.set('fontSize', function (value) {
        return Math.max(8, Math.min(72, parseInt(value) || 14));
      });
      this.propertyValidators.set('borderWidth', function (value) {
        return Math.max(0, Math.min(20, parseInt(value) || 0));
      });
      this.propertyValidators.set('borderStyle', function (value) {
        var validStyles = ['solid', 'dashed', 'dotted', 'double'];
        return validStyles.includes(value) ? value : 'solid';
      });
      this.propertyValidators.set('borderRadius', function (value) {
        return Math.max(0, Math.min(100, parseInt(value) || 0));
      });

      // Presets de couleurs
      this.propertyPresets.set('colors', {
        slate: ['#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155', '#1e293b'],
        blue: ['#eff6ff', '#dbeafe', '#bfdbfe', '#93c5fd', '#60a5fa', '#3b82f6', '#2563eb', '#1d4ed8', '#1e40af'],
        green: ['#f0fdf4', '#dcfce7', '#bbf7d0', '#86efac', '#4ade80', '#22c55e', '#16a34a', '#15803d', '#166534'],
        red: ['#fef2f2', '#fee2e2', '#fecaca', '#fca5a5', '#f87171', '#ef4444', '#dc2626', '#b91c1c', '#991b1b']
      });

      // Groupes de propriétés
      this.propertyGroups.set('position', ['x', 'y']);
      this.propertyGroups.set('dimensions', ['width', 'height']);
      this.propertyGroups.set('typography', ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'color', 'textAlign']);
      this.propertyGroups.set('appearance', ['backgroundColor', 'borderColor', 'borderWidth', 'borderRadius']);
      this.propertyGroups.set('effects', ['opacity', 'shadow', 'brightness', 'contrast', 'saturate']);
      this.propertyGroups.set('transform', ['rotation', 'scale']);
    }

    /**
     * Valide une valeur de couleur
     */
  }, {
    key: "validateColor",
    value: function validateColor(value) {
      if (!value) return '#000000';

      // Vérifier si c'est un code hex valide
      if (/^#[0-9A-Fa-f]{6}$/.test(value) || /^#[0-9A-Fa-f]{3}$/.test(value)) {
        return value;
      }

      // Vérifier si c'est un nom de couleur CSS valide
      var tempElement = document.createElement('div');
      tempElement.style.color = value;
      return tempElement.style.color || '#000000';
    }

    /**
     * Valide une propriété selon son type et applique des corrections automatiques
     */
  }, {
    key: "validateProperty",
    value: function validateProperty(property, value) {
      // Pour les propriétés boolean, retourner la valeur telle quelle
      if (typeof value === 'boolean') {
        return value;
      }

      // Pour les propriétés de colonnes (tableaux), retourner la valeur telle quelle
      if (property.startsWith('columns.')) {
        return value;
      }

      // Chercher un validateur pour cette propriété
      var validator = this.propertyValidators.get(property);
      if (validator) {
        try {
          return validator(value);
        } catch (error) {
          return this.getDefaultValue(property);
        }
      }

      // Validation spécifique selon le type de propriété
      if (this.isNumericProperty(property)) {
        return this.validateNumericProperty(property, value);
      }
      if (this.isColorProperty(property)) {
        return this.validateColorProperty(value);
      }
      if (this.isTextStyleProperty(property)) {
        return this.validateTextStyleProperty(property, value);
      }

      // Si pas de validateur spécifique, retourner la valeur telle quelle
      return value;
    }

    /**
     * Vérifie si une propriété est numérique
     */
  }, {
    key: "isNumericProperty",
    value: function isNumericProperty(property) {
      var numericProps = ['x', 'y', 'width', 'height', 'fontSize', 'opacity', 'lineHeight', 'letterSpacing', 'zIndex', 'borderWidth', 'borderRadius', 'rotation', 'padding'];
      return numericProps.includes(property);
    }

    /**
     * Vérifie si une propriété est une couleur
     */
  }, {
    key: "isColorProperty",
    value: function isColorProperty(property) {
      var colorProps = ['color', 'backgroundColor', 'borderColor'];
      return colorProps.includes(property);
    }

    /**
     * Vérifie si une propriété est un style de texte
     */
  }, {
    key: "isTextStyleProperty",
    value: function isTextStyleProperty(property) {
      var textProps = ['fontWeight', 'textAlign', 'textDecoration', 'textTransform', 'borderStyle'];
      return textProps.includes(property);
    }

    /**
     * Valide une propriété numérique
     */
  }, {
    key: "validateNumericProperty",
    value: function validateNumericProperty(property, value) {
      if (value === null || value === undefined || value === '') {
        return this.getDefaultValue(property);
      }
      var numericValue;
      if (typeof value === 'string') {
        numericValue = parseFloat(value);
        if (isNaN(numericValue)) {
          return this.getDefaultValue(property);
        }
      } else if (typeof value === 'number') {
        numericValue = value;
      } else {
        return this.getDefaultValue(property);
      }

      // Appliquer les contraintes selon la propriété
      var constraints = {
        fontSize: {
          min: 8,
          max: 72
        },
        opacity: {
          min: 0,
          max: 1
        },
        lineHeight: {
          min: 0.5,
          max: 3
        },
        letterSpacing: {
          min: -5,
          max: 10
        },
        zIndex: {
          min: -100,
          max: 1000
        },
        borderWidth: {
          min: 0,
          max: 20
        },
        borderRadius: {
          min: 0,
          max: 100
        },
        rotation: {
          min: -180,
          max: 180
        },
        padding: {
          min: 0,
          max: 100
        }
      };
      if (constraints[property]) {
        var _constraints$property = constraints[property],
          min = _constraints$property.min,
          max = _constraints$property.max;
        numericValue = Math.max(min, Math.min(max, numericValue));
      }
      return numericValue;
    }

    /**
     * Valide une propriété de couleur
     */
  }, {
    key: "validateColorProperty",
    value: function validateColorProperty(value) {
      if (!value) return '#000000';
      if (value === 'transparent') return value;

      // Vérifier si c'est un code hex valide
      if (/^#[0-9A-Fa-f]{6}$/.test(value) || /^#[0-9A-Fa-f]{3}$/.test(value)) {
        return value;
      }

      // Vérifier si c'est un nom de couleur CSS valide
      var tempElement = document.createElement('div');
      tempElement.style.color = value;
      var computedColor = tempElement.style.color;

      // Si le navigateur reconnaît la couleur, la retourner
      if (computedColor && computedColor !== '') {
        return value;
      }
      return '#000000';
    }

    /**
     * Valide une propriété de style de texte
     */
  }, {
    key: "validateTextStyleProperty",
    value: function validateTextStyleProperty(property, value) {
      var validations = {
        fontWeight: ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'],
        textAlign: ['left', 'center', 'right', 'justify'],
        textDecoration: ['none', 'underline', 'overline', 'line-through'],
        textTransform: ['none', 'capitalize', 'uppercase', 'lowercase'],
        borderStyle: ['solid', 'dashed', 'dotted', 'double', 'none']
      };
      if (validations[property] && validations[property].includes(value)) {
        return value;
      }

      // Valeurs par défaut
      var defaults = {
        fontWeight: 'normal',
        textAlign: 'left',
        textDecoration: 'none',
        textTransform: 'none',
        borderStyle: 'solid'
      };
      return defaults[property] || value;
    }

    /**
     * Obtient la valeur par défaut pour une propriété
     */
  }, {
    key: "getDefaultValue",
    value: function getDefaultValue(property) {
      var defaults = {
        x: 0,
        y: 0,
        width: 100,
        height: 50,
        fontSize: 14,
        opacity: 1,
        lineHeight: 1.2,
        letterSpacing: 0,
        zIndex: 0,
        borderWidth: 0,
        borderRadius: 0,
        rotation: 0,
        padding: 0,
        color: '#333333',
        backgroundColor: 'transparent',
        borderColor: '#dddddd',
        fontWeight: 'normal',
        textAlign: 'left',
        textDecoration: 'none',
        textTransform: 'none',
        borderStyle: 'solid'
      };
      return defaults[property] || null;
    }

    /**
     * Obtient les presets pour une catégorie
     */
  }, {
    key: "getPresets",
    value: function getPresets(category) {
      return this.propertyPresets.get(category) || {};
    }

    /**
     * Obtient les propriétés d'un groupe
     */
  }, {
    key: "getPropertiesInGroup",
    value: function getPropertiesInGroup(groupName) {
      return this.propertyGroups.get(groupName) || [];
    }

    /**
     * Applique un preset à un ensemble de propriétés
     */
  }, {
    key: "applyPreset",
    value: function applyPreset(presetName, currentProperties) {
      var presets = {
        // Presets de style de texte
        'text-title': {
          fontSize: 24,
          fontWeight: 'bold',
          textAlign: 'center',
          color: '#1e293b'
        },
        'text-subtitle': {
          fontSize: 18,
          fontWeight: 'bold',
          textAlign: 'left',
          color: '#334155'
        },
        'text-body': {
          fontSize: 14,
          fontWeight: 'normal',
          textAlign: 'left',
          color: '#475569'
        },
        'text-caption': {
          fontSize: 12,
          fontWeight: 'normal',
          textAlign: 'left',
          color: '#64748b'
        },
        // Presets de formes
        'shape-rounded': {
          borderRadius: 8,
          borderWidth: 1,
          borderColor: 'transparent'
        },
        'shape-circle': {
          borderRadius: 50,
          borderWidth: 1,
          borderColor: 'transparent'
        },
        'shape-square': {
          borderRadius: 0,
          borderWidth: 1,
          borderColor: 'transparent'
        },
        // Presets d'effets
        'effect-shadow-soft': {
          shadow: true,
          shadowColor: '#000000',
          shadowOffsetX: 1,
          shadowOffsetY: 1,
          opacity: 90
        },
        'effect-shadow-strong': {
          shadow: true,
          shadowColor: '#000000',
          shadowOffsetX: 3,
          shadowOffsetY: 3,
          opacity: 85
        },
        'effect-glow': {
          shadow: true,
          shadowColor: '#2563eb',
          shadowOffsetX: 0,
          shadowOffsetY: 0,
          opacity: 95
        },
        // Presets de couleurs
        'color-primary': {
          backgroundColor: '#2563eb',
          color: '#ffffff'
        },
        'color-secondary': {
          backgroundColor: '#64748b',
          color: '#ffffff'
        },
        'color-success': {
          backgroundColor: '#16a34a',
          color: '#ffffff'
        },
        'color-warning': {
          backgroundColor: '#ca8a04',
          color: '#ffffff'
        },
        'color-error': {
          backgroundColor: '#dc2626',
          color: '#ffffff'
        }
      };
      return presets[presetName] || {};
    }

    /**
     * Réinitialise les propriétés aux valeurs par défaut
     */
  }, {
    key: "getDefaultProperties",
    value: function getDefaultProperties() {
      var elementType = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'text';
      // Propriétés communes à tous les éléments
      var defaults = {
        // Propriétés communes
        x: 50,
        y: 50,
        width: 100,
        height: 50,
        opacity: 100,
        rotation: 0,
        scale: 100,
        visible: true,
        // Apparence
        backgroundColor: 'transparent',
        borderColor: 'transparent',
        borderWidth: 0,
        borderStyle: 'solid',
        borderRadius: 0,
        // Typographie (disponible pour tous les éléments)
        color: '#1e293b',
        fontFamily: 'Inter, sans-serif',
        fontSize: 14,
        fontWeight: 'normal',
        fontStyle: 'normal',
        textAlign: 'left',
        textDecoration: 'none',
        // Contenu (pour éléments texte)
        text: 'Texte',
        // Images
        src: '',
        alt: '',
        objectFit: 'cover',
        // Effets
        shadow: false,
        shadowColor: '#000000',
        shadowOffsetX: 2,
        shadowOffsetY: 2,
        brightness: 100,
        contrast: 100,
        saturate: 100,
        // Propriétés spécifiques aux tableaux
        showHeaders: true,
        showBorders: true,
        headers: ['Produit', 'Qté', 'Prix'],
        dataSource: 'order_items',
        columns: {
          image: true,
          name: true,
          sku: false,
          quantity: true,
          price: true,
          total: true
        },
        showSubtotal: false,
        showShipping: true,
        showTaxes: true,
        showDiscount: false,
        showTotal: false,
        // Propriétés pour les barres de progression
        progressColor: '#3b82f6',
        progressValue: 75,
        // Propriétés pour les codes
        lineColor: '#64748b',
        lineWidth: 2,
        // Propriétés pour les types de document
        documentType: 'invoice',
        // Propriétés pour les logos et images
        imageUrl: '',
        // Propriétés d'espacement et mise en page
        spacing: 8,
        layout: 'vertical',
        alignment: 'left',
        fit: 'contain'
      };

      // Ajustements mineurs selon le type pour une meilleure UX
      var typeAdjustments = {
        'text': {
          width: 150,
          height: 30
        },
        'image': {
          width: 150,
          height: 100
        },
        'rectangle': {
          backgroundColor: '#f1f5f9',
          borderWidth: 1,
          width: 150,
          height: 80
        },
        'product_table': {
          width: 300,
          height: 150
        },
        'customer_info': {
          width: 200,
          height: 100
        },
        'company_logo': {
          width: 100,
          height: 60
        },
        'order_number': {
          width: 150,
          height: 30
        },
        'company_info': {
          width: 200,
          height: 80
        },
        'document_type': {
          width: 120,
          height: 40
        },
        'watermark': {
          width: 300,
          height: 200,
          opacity: 10,
          text: 'CONFIDENTIEL'
        },
        'progress-bar': {
          width: 200,
          height: 20
        },
        'barcode': {
          width: 150,
          height: 60
        },
        'qrcode': {
          width: 80,
          height: 80
        },
        'icon': {
          width: 50,
          height: 50
        },
        'line': {
          width: 200,
          height: 2
        }
      };
      return ElementCustomizationService_objectSpread(ElementCustomizationService_objectSpread({}, defaults), typeAdjustments[elementType] || {});
    }

    /**
     * Calcule les propriétés calculées (readonly)
     */
  }, {
    key: "getComputedProperties",
    value: function getComputedProperties(properties) {
      return {
        // Position absolue avec rotation
        absoluteX: properties.x + properties.width / 2,
        absoluteY: properties.y + properties.height / 2,
        // Dimensions avec échelle
        scaledWidth: properties.width * (properties.scale / 100),
        scaledHeight: properties.height * (properties.scale / 100),
        // Styles CSS calculés
        cssTransform: "rotate(".concat(properties.rotation, "deg) scale(").concat(properties.scale / 100, ")"),
        cssFilter: "brightness(".concat(properties.brightness, "%) contrast(").concat(properties.contrast, "%) saturate(").concat(properties.saturate, "%)"),
        cssBoxShadow: properties.shadow ? "".concat(properties.shadowOffsetX || 0, "px ").concat(properties.shadowOffsetY || 0, "px 4px ").concat(properties.shadowColor || '#000000') : 'none'
      };
    }

    /**
     * Vérifie si une propriété peut être animée
     */
  }, {
    key: "isAnimatable",
    value: function isAnimatable(property) {
      var animatableProperties = ['x', 'y', 'width', 'height', 'rotation', 'scale', 'opacity', 'brightness', 'contrast', 'saturate'];
      return animatableProperties.includes(property);
    }

    /**
     * Obtient les contraintes d'une propriété
     */
  }, {
    key: "getPropertyConstraints",
    value: function getPropertyConstraints(property) {
      var constraints = {
        x: {
          min: -1000,
          max: 2000,
          step: 1
        },
        y: {
          min: -1000,
          max: 2000,
          step: 1
        },
        width: {
          min: 1,
          max: 2000,
          step: 1
        },
        height: {
          min: 1,
          max: 2000,
          step: 1
        },
        fontSize: {
          min: 8,
          max: 72,
          step: 1
        },
        borderWidth: {
          min: 0,
          max: 20,
          step: 1
        },
        borderRadius: {
          min: 0,
          max: 100,
          step: 1
        },
        rotation: {
          min: -180,
          max: 180,
          step: 1
        },
        scale: {
          min: 10,
          max: 200,
          step: 5
        },
        opacity: {
          min: 0,
          max: 100,
          step: 1
        },
        brightness: {
          min: 0,
          max: 200,
          step: 5
        },
        contrast: {
          min: 0,
          max: 200,
          step: 5
        },
        saturate: {
          min: 0,
          max: 200,
          step: 5
        },
        shadowOffsetX: {
          min: -50,
          max: 50,
          step: 1
        },
        shadowOffsetY: {
          min: -50,
          max: 50,
          step: 1
        }
      };
      return constraints[property] || {};
    }
  }]);
}();

// Instance singleton du service
var elementCustomizationService = new ElementCustomizationService();
;// ./src/utilities/elementPropertyRestrictions.js
// Système de gestion des propriétés d'éléments
// Définit les restrictions et validations pour chaque type d'élément

var ELEMENT_PROPERTY_RESTRICTIONS = {
  // Éléments spéciaux - contrôle du fond autorisé mais valeur par défaut transparente
  special: {
    backgroundColor: {
      disabled: false,
      // Maintenant autorisé
      "default": 'transparent' // Valeur par défaut transparente
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments de mise en page - contrôle complet
  layout: {
    backgroundColor: {
      disabled: false,
      "default": '#f8fafc'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments de texte - contrôle complet
  text: {
    backgroundColor: {
      disabled: false,
      "default": 'transparent'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments graphiques - contrôle complet
  shape: {
    backgroundColor: {
      disabled: false,
      "default": '#e5e7eb'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments médias - contrôle limité
  media: {
    backgroundColor: {
      disabled: false,
      "default": '#f3f4f6'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },
  // Éléments dynamiques - contrôle complet
  dynamic: {
    backgroundColor: {
      disabled: false,
      "default": 'transparent'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  }
};

// Mapping des types d'éléments vers leurs catégories
var ELEMENT_TYPE_MAPPING = {
  // Spéciaux
  'product_table': 'special',
  'customer_info': 'special',
  'company_logo': 'special',
  'company_info': 'special',
  'order_number': 'special',
  'document_type': 'special',
  'progress-bar': 'special',
  // Mise en page
  'layout-header': 'layout',
  'layout-footer': 'layout',
  'layout-sidebar': 'layout',
  'layout-section': 'layout',
  'layout-container': 'layout',
  'layout-section-divider': 'layout',
  'layout-spacer': 'layout',
  'layout-two-column': 'layout',
  'layout-three-column': 'layout',
  // Texte
  'text': 'text',
  'dynamic-text': 'text',
  'conditional-text': 'text',
  'counter': 'text',
  'date-dynamic': 'text',
  'currency': 'text',
  'formula': 'text',
  // Formes
  'rectangle': 'shape',
  'line': 'shape',
  'shape-rectangle': 'shape',
  'shape-circle': 'shape',
  'shape-line': 'shape',
  'shape-arrow': 'shape',
  'shape-triangle': 'shape',
  'shape-star': 'shape',
  'divider': 'shape',
  // Médias
  'image': 'media',
  'image-upload': 'media',
  'logo': 'media',
  'barcode': 'media',
  'qrcode': 'media',
  'qrcode-dynamic': 'media',
  'icon': 'media',
  // Dynamiques
  'table-dynamic': 'dynamic',
  'gradient-box': 'dynamic',
  'shadow-box': 'dynamic',
  'rounded-box': 'dynamic',
  'border-box': 'dynamic',
  'background-pattern': 'dynamic',
  'watermark': 'dynamic',
  // Factures (mélange de catégories)
  'invoice-header': 'layout',
  'invoice-address-block': 'layout',
  'invoice-info-block': 'layout',
  'invoice-products-table': 'special',
  'invoice-totals-block': 'layout',
  'invoice-payment-terms': 'layout',
  'invoice-legal-footer': 'layout',
  'invoice-signature-block': 'layout'
};

// Fonction pour vérifier si une propriété est autorisée pour un type d'élément
var isPropertyAllowed = function isPropertyAllowed(elementType, propertyName) {
  var category = ELEMENT_TYPE_MAPPING[elementType] || 'text'; // défaut texte
  var restrictions = ELEMENT_PROPERTY_RESTRICTIONS[category];
  if (!restrictions || !restrictions[propertyName]) {
    return true; // propriété autorisée par défaut
  }
  return !restrictions[propertyName].disabled;
};

// Fonction pour obtenir la valeur par défaut d'une propriété
var getPropertyDefault = function getPropertyDefault(elementType, propertyName) {
  var category = ELEMENT_TYPE_MAPPING[elementType] || 'text';
  var restrictions = ELEMENT_PROPERTY_RESTRICTIONS[category];
  if (restrictions && restrictions[propertyName] && restrictions[propertyName]["default"] !== undefined) {
    return restrictions[propertyName]["default"];
  }
  return null; // pas de valeur par défaut spécifique
};

// Fonction pour valider une propriété
var validateProperty = function validateProperty(elementType, propertyName, value) {
  if (!isPropertyAllowed(elementType, propertyName)) {
    var _ELEMENT_PROPERTY_RES;
    return {
      valid: false,
      reason: ((_ELEMENT_PROPERTY_RES = ELEMENT_PROPERTY_RESTRICTIONS[ELEMENT_TYPE_MAPPING[elementType] || 'text'][propertyName]) === null || _ELEMENT_PROPERTY_RES === void 0 ? void 0 : _ELEMENT_PROPERTY_RES.reason) || 'Propriété non autorisée'
    };
  }

  // Validations spécifiques selon le type de propriété
  switch (propertyName) {
    case 'backgroundColor':
      if (typeof value !== 'string') {
        return {
          valid: false,
          reason: 'La couleur doit être une chaîne'
        };
      }
      // Plus de restriction pour les éléments spéciaux - ils peuvent maintenant avoir un fond
      break;
    case 'borderWidth':
      if (typeof value !== 'number' || value < 0) {
        return {
          valid: false,
          reason: 'La largeur de bordure doit être un nombre positif'
        };
      }
      break;
    case 'fontSize':
      if (typeof value !== 'number' || value <= 0) {
        return {
          valid: false,
          reason: 'La taille de police doit être un nombre positif'
        };
      }
      break;
    case 'width':
    case 'height':
      if (typeof value !== 'number' || value <= 0) {
        return {
          valid: false,
          reason: 'Les dimensions doivent être positives'
        };
      }
      break;
    default:
      break;
  }
  return {
    valid: true
  };
};

// Fonction pour corriger automatiquement une propriété invalide
var fixInvalidProperty = function fixInvalidProperty(elementType, propertyName, invalidValue) {
  // Pour les éléments spéciaux, backgroundColor peut maintenant être contrôlé
  // (pas de forçage automatique à 'transparent')

  // Valeurs par défaut pour les propriétés numériques
  var numericDefaults = {
    borderWidth: 0,
    fontSize: 14,
    width: 100,
    height: 50,
    padding: 8
  };
  if (numericDefaults[propertyName] !== undefined) {
    return numericDefaults[propertyName];
  }

  // Valeurs par défaut pour les chaînes
  var stringDefaults = {
    backgroundColor: 'transparent',
    borderColor: 'transparent',
    color: '#000000',
    fontFamily: 'Arial, sans-serif'
  };
  return stringDefaults[propertyName] || invalidValue;
};
;// ./src/hooks/useElementCustomization.js
function useElementCustomization_typeof(o) { "@babel/helpers - typeof"; return useElementCustomization_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, useElementCustomization_typeof(o); }
function useElementCustomization_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function useElementCustomization_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? useElementCustomization_ownKeys(Object(t), !0).forEach(function (r) { useElementCustomization_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : useElementCustomization_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function useElementCustomization_defineProperty(e, r, t) { return (r = useElementCustomization_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function useElementCustomization_toPropertyKey(t) { var i = useElementCustomization_toPrimitive(t, "string"); return "symbol" == useElementCustomization_typeof(i) ? i : i + ""; }
function useElementCustomization_toPrimitive(t, r) { if ("object" != useElementCustomization_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != useElementCustomization_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function useElementCustomization_slicedToArray(r, e) { return useElementCustomization_arrayWithHoles(r) || useElementCustomization_iterableToArrayLimit(r, e) || useElementCustomization_unsupportedIterableToArray(r, e) || useElementCustomization_nonIterableRest(); }
function useElementCustomization_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function useElementCustomization_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return useElementCustomization_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? useElementCustomization_arrayLikeToArray(r, a) : void 0; } }
function useElementCustomization_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function useElementCustomization_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function useElementCustomization_arrayWithHoles(r) { if (Array.isArray(r)) return r; }




/**
 * Hook pour gérer la personnalisation des éléments
 * Gère l'état local des propriétés et les changements en temps réel
 */
var useElementCustomization = function useElementCustomization(selectedElements, elements, onPropertyChange) {
  var _useState = (0,react.useState)({}),
    _useState2 = useElementCustomization_slicedToArray(_useState, 2),
    localProperties = _useState2[0],
    setLocalProperties = _useState2[1];
  var _useState3 = (0,react.useState)('appearance'),
    _useState4 = useElementCustomization_slicedToArray(_useState3, 2),
    activeTab = _useState4[0],
    setActiveTab = _useState4[1];

  // Obtenir l'élément sélectionné (mémorisé pour éviter les re-renders)
  var selectedElement = (0,react.useMemo)(function () {
    return selectedElements.length > 0 ? elements.find(function (el) {
      return el.id === selectedElements[0];
    }) : null;
  }, [selectedElements, elements]);

  // Synchroniser les propriétés locales avec l'élément sélectionné
  (0,react.useEffect)(function () {
    if (selectedElement) {
      var defaultProperties = {
        // Valeurs par défaut de base
        color: '#333333',
        backgroundColor: 'transparent',
        borderColor: '#dddddd',
        borderWidth: 0,
        borderStyle: 'solid',
        borderRadius: 4,
        fontSize: 14,
        fontFamily: 'Inter',
        fontWeight: 'normal',
        fontStyle: 'normal',
        textDecoration: 'none',
        textAlign: 'left',
        lineHeight: 1.2,
        letterSpacing: 0
      };

      // Appliquer les restrictions selon le type d'élément
      var elementType = selectedElement.type;
      var restrictedDefaults = useElementCustomization_objectSpread({}, defaultProperties);

      // Pour les propriétés restreintes, utiliser les valeurs par défaut spécifiques
      Object.keys(defaultProperties).forEach(function (property) {
        var specificDefault = getPropertyDefault(elementType, property);
        if (specificDefault !== null) {
          restrictedDefaults[property] = specificDefault;
        }

        // Pour les éléments spéciaux, forcer backgroundColor à transparent
        if (ELEMENT_TYPE_MAPPING[elementType] === 'special' && property === 'backgroundColor') {
          restrictedDefaults[property] = 'transparent';
        }
      });
      var newProperties = useElementCustomization_objectSpread(useElementCustomization_objectSpread({}, restrictedDefaults), selectedElement);

      // Pour les propriétés restreintes, forcer les valeurs corrigées
      Object.keys(restrictedDefaults).forEach(function (property) {
        var isRestricted = !isPropertyAllowed(elementType, property);
        if (isRestricted) {
          var correctedValue = fixInvalidProperty(elementType, property, newProperties[property]);
          if (correctedValue !== newProperties[property]) {
            newProperties[property] = correctedValue;
          }
        }
        // Pour les éléments spéciaux, garder backgroundColor à transparent par défaut seulement si pas défini
        else if (ELEMENT_TYPE_MAPPING[elementType] === 'special' && property === 'backgroundColor' && !newProperties[property]) {
          newProperties[property] = 'transparent';
        }
      });

      // Validation finale des propriétés
      Object.keys(newProperties).forEach(function (property) {
        var validation = validateProperty(elementType, property, newProperties[property]);
        if (!validation.valid) {
          newProperties[property] = fixInvalidProperty(elementType, property, newProperties[property]);
        }
      });
      setLocalProperties(newProperties);
    } else {
      setLocalProperties({});
    }
  }, [selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.id]); // Ne dépendre que de l'ID pour éviter les re-renders inutiles

  // Gestionnaire de changement de propriété avec validation
  var handlePropertyChange = (0,react.useCallback)(function (elementId, property, value) {
    var element = elements.find(function (el) {
      return el.id === elementId;
    });
    if (!element) return;
    var validatedValue = value;

    // Validation selon le système de restrictions
    var validation = validateProperty(element.type, property, value);
    if (!validation.valid) {
      // Ne pas appliquer le changement si la propriété n'est pas autorisée
      return;
    }

    // Validation supplémentaire selon le type de propriété (service existant)
    if (typeof value !== 'boolean' && !property.startsWith('columns.')) {
      try {
        var serviceValidated = elementCustomizationService.validateProperty(property, value);
        if (serviceValidated !== undefined) {
          validatedValue = serviceValidated;
        }
      } catch (error) {}
    }

    // Mettre à jour l'état local immédiatement pour l'UI
    setLocalProperties(function (prev) {
      var newProperties;
      if (property.includes('.')) {
        // Gérer les propriétés imbriquées (ex: "columns.image")
        var updateNestedProperty = function updateNestedProperty(obj, path, value) {
          var keys = path.split('.');
          var lastKey = keys.pop();
          var target = keys.reduce(function (current, key) {
            if (!current[key] || useElementCustomization_typeof(current[key]) !== 'object') {
              current[key] = {};
            } else {
              current[key] = useElementCustomization_objectSpread({}, current[key]); // Créer une copie pour éviter de modifier l'original
            }
            return current[key];
          }, obj);
          target[lastKey] = value;
          return obj;
        };
        newProperties = useElementCustomization_objectSpread({}, prev);
        updateNestedProperty(newProperties, property, validatedValue);
      } else {
        newProperties = useElementCustomization_objectSpread(useElementCustomization_objectSpread({}, prev), {}, useElementCustomization_defineProperty({}, property, validatedValue));
      }
      return newProperties;
    });

    // Notifier le parent pour la persistance
    onPropertyChange(elementId, property, validatedValue);
  }, [onPropertyChange, elements]);

  // Validation des valeurs de propriétés
  var validatePropertyValue = function validatePropertyValue(property, value) {
    switch (property) {
      case 'x':
      case 'y':
      case 'width':
      case 'height':
        return Math.max(0, parseInt(value) || 0);
      case 'fontSize':
        return Math.max(8, Math.min(72, parseInt(value) || 14));
      case 'lineHeight':
        return Math.max(0.5, Math.min(5, parseFloat(value) || 1.2));
      case 'letterSpacing':
        return Math.max(-5, Math.min(20, parseFloat(value) || 0));
      case 'borderWidth':
        return Math.max(0, Math.min(20, parseInt(value) || 0));
      case 'borderStyle':
        var validStyles = ['solid', 'dashed', 'dotted', 'double'];
        return validStyles.includes(value) ? value : 'solid';
      case 'borderRadius':
        return Math.max(0, Math.min(100, parseInt(value) || 0));
      case 'rotation':
        return ((parseInt(value) || 0) % 360 + 360) % 360;
      // Normaliser entre 0-359

      case 'scale':
        return Math.max(10, Math.min(200, parseInt(value) || 100));
      case 'opacity':
        return Math.max(0, Math.min(100, parseInt(value) || 100));
      case 'brightness':
      case 'contrast':
      case 'saturate':
        return Math.max(0, Math.min(200, parseInt(value) || 100));
      case 'shadowOffsetX':
      case 'shadowOffsetY':
        return Math.max(-50, Math.min(50, parseInt(value) || 0));
      default:
        return value;
    }
  };

  // Appliquer des presets de propriétés
  var applyPropertyPreset = (0,react.useCallback)(function (elementId, preset) {
    var presets = {
      // Presets de couleurs
      'color-dark': {
        color: '#1e293b'
      },
      'color-light': {
        color: '#f8fafc'
      },
      'color-primary': {
        color: '#2563eb'
      },
      'color-success': {
        color: '#16a34a'
      },
      'color-warning': {
        color: '#ca8a04'
      },
      'color-error': {
        color: '#dc2626'
      },
      // Presets de style de texte
      'text-title': {
        fontSize: 24,
        fontWeight: 'bold',
        textAlign: 'center'
      },
      'text-subtitle': {
        fontSize: 18,
        fontWeight: 'bold',
        textAlign: 'left'
      },
      'text-body': {
        fontSize: 14,
        fontWeight: 'normal',
        textAlign: 'left'
      },
      'text-caption': {
        fontSize: 12,
        fontWeight: 'normal',
        textAlign: 'left'
      },
      // Presets de formes
      'shape-rounded': {
        borderRadius: 8
      },
      'shape-circle': {
        borderRadius: 50
      },
      'shape-square': {
        borderRadius: 0
      },
      // Presets d'effets
      'effect-shadow': {
        shadow: true,
        shadowColor: '#000000',
        shadowOffsetX: 2,
        shadowOffsetY: 2
      },
      'effect-glow': {
        shadow: true,
        shadowColor: '#2563eb',
        shadowOffsetX: 0,
        shadowOffsetY: 0
      },
      'effect-none': {
        shadow: false,
        brightness: 100,
        contrast: 100,
        saturate: 100
      }
    };
    if (presets[preset]) {
      Object.entries(presets[preset]).forEach(function (_ref) {
        var _ref2 = useElementCustomization_slicedToArray(_ref, 2),
          property = _ref2[0],
          value = _ref2[1];
        handlePropertyChange(elementId, property, value);
      });
    }
  }, [handlePropertyChange]);

  // Réinitialiser les propriétés aux valeurs par défaut
  var resetProperties = (0,react.useCallback)(function (elementId) {
    var defaultProperties = {
      x: 50,
      y: 50,
      width: 100,
      height: 50,
      backgroundColor: '#ffffff',
      borderColor: '#dddddd',
      borderWidth: 1,
      borderStyle: 'solid',
      borderRadius: 4,
      color: '#333333',
      fontSize: 14,
      fontFamily: 'Inter',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textAlign: 'left',
      textDecoration: 'none',
      lineHeight: 1.2,
      letterSpacing: 0,
      opacity: 100,
      rotation: 0,
      scale: 100,
      shadow: false,
      brightness: 100,
      contrast: 100,
      saturate: 100
    };
    Object.entries(defaultProperties).forEach(function (_ref3) {
      var _ref4 = useElementCustomization_slicedToArray(_ref3, 2),
        property = _ref4[0],
        value = _ref4[1];
      handlePropertyChange(elementId, property, value);
    });
  }, [handlePropertyChange]);
  return {
    localProperties: localProperties,
    activeTab: activeTab,
    setActiveTab: setActiveTab,
    selectedElement: selectedElement,
    handlePropertyChange: handlePropertyChange,
    applyPropertyPreset: applyPropertyPreset,
    resetProperties: resetProperties,
    validatePropertyValue: validatePropertyValue
  };
};
;// ./src/hooks/useElementSynchronization.js


/**
 * Hook pour gérer la synchronisation des personnalisations d'éléments
 * Gère la persistance, la validation et la synchronisation avec le backend
 */
var useElementSynchronization = function useElementSynchronization(elements, onPropertyChange, onBatchUpdate) {
  var autoSave = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : true;
  var autoSaveDelay = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 3000;
  var pendingChangesRef = (0,react.useRef)(new Map());
  var autoSaveTimeoutRef = (0,react.useRef)(null);
  var lastSavedRef = (0,react.useRef)(new Map());

  // Synchronisation différée pour éviter les appels trop fréquents
  var debouncedSync = (0,react.useCallback)(function (elementId, property, value) {
    // Annuler la sauvegarde automatique précédente
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
    }

    // Ajouter le changement aux modifications en attente
    var key = "".concat(elementId, ".").concat(property);
    pendingChangesRef.current.set(key, {
      elementId: elementId,
      property: property,
      value: value
    });

    // Programmer une sauvegarde automatique
    if (autoSave) {
      autoSaveTimeoutRef.current = setTimeout(function () {
        syncPendingChanges();
      }, autoSaveDelay);
    }
  }, [autoSave, autoSaveDelay]);

  // Synchroniser immédiatement
  var immediateSync = (0,react.useCallback)(function (elementId, property, value) {
    // Annuler la sauvegarde automatique
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
    }

    // Synchroniser immédiatement
    onPropertyChange(elementId, property, value);

    // Mettre à jour la référence de dernière sauvegarde
    var key = "".concat(elementId, ".").concat(property);
    lastSavedRef.current.set(key, value);

    // Vider les changements en attente pour cette propriété
    pendingChangesRef.current["delete"](key);
  }, [onPropertyChange]);

  // Synchroniser tous les changements en attente
  var syncPendingChanges = (0,react.useCallback)(function () {
    if (pendingChangesRef.current.size === 0) return;
    var changes = Array.from(pendingChangesRef.current.values());

    // Grouper les changements par élément pour optimiser
    var changesByElement = changes.reduce(function (acc, change) {
      if (!acc[change.elementId]) {
        acc[change.elementId] = {};
      }
      acc[change.elementId][change.property] = change.value;
      return acc;
    }, {});

    // Si on a une fonction de mise à jour par lot, l'utiliser
    if (onBatchUpdate && Object.keys(changesByElement).length > 1) {
      onBatchUpdate(changesByElement);
    } else {
      // Sinon, mettre à jour élément par élément
      changes.forEach(function (_ref) {
        var elementId = _ref.elementId,
          property = _ref.property,
          value = _ref.value;
        onPropertyChange(elementId, property, value);
      });
    }

    // Mettre à jour les références de dernière sauvegarde
    changes.forEach(function (_ref2) {
      var elementId = _ref2.elementId,
        property = _ref2.property,
        value = _ref2.value;
      var key = "".concat(elementId, ".").concat(property);
      lastSavedRef.current.set(key, value);
    });

    // Vider les changements en attente
    pendingChangesRef.current.clear();
  }, [onPropertyChange, onBatchUpdate]);

  // Forcer la synchronisation immédiate
  var forceSync = (0,react.useCallback)(function () {
    syncPendingChanges();
  }, [syncPendingChanges]);

  // Vérifier si des changements sont en attente
  var hasPendingChanges = (0,react.useCallback)(function () {
    return pendingChangesRef.current.size > 0;
  }, []);

  // Obtenir les changements en attente pour un élément
  var getPendingChanges = (0,react.useCallback)(function (elementId) {
    var changes = [];
    pendingChangesRef.current.forEach(function (change, key) {
      if (change.elementId === elementId) {
        changes.push(change);
      }
    });
    return changes;
  }, []);

  // Annuler les changements en attente pour un élément
  var cancelPendingChanges = (0,react.useCallback)(function (elementId) {
    var keysToDelete = [];
    pendingChangesRef.current.forEach(function (change, key) {
      if (change.elementId === elementId) {
        keysToDelete.push(key);
      }
    });
    keysToDelete.forEach(function (key) {
      return pendingChangesRef.current["delete"](key);
    });
  }, []);

  // Restaurer les dernières valeurs sauvegardées
  var restoreLastSaved = (0,react.useCallback)(function (elementId, property) {
    var key = "".concat(elementId, ".").concat(property);
    var lastSavedValue = lastSavedRef.current.get(key);
    if (lastSavedValue !== undefined) {
      // Annuler le changement en attente
      pendingChangesRef.current["delete"](key);

      // Restaurer la valeur
      return lastSavedValue;
    }
    return null;
  }, []);

  // Validation des propriétés avant synchronisation
  var validateAndSync = (0,react.useCallback)(function (elementId, property, value, validator) {
    var validatedValue = value;

    // Appliquer la validation si fournie
    if (validator) {
      validatedValue = validator(value);
    }

    // Appliquer la validation par défaut selon le type de propriété
    validatedValue = validatePropertyValue(property, validatedValue);

    // Synchroniser
    debouncedSync(elementId, property, validatedValue);
    return validatedValue;
  }, [debouncedSync]);

  // Validation des valeurs de propriétés
  var validatePropertyValue = function validatePropertyValue(property, value) {
    switch (property) {
      case 'x':
      case 'y':
      case 'width':
      case 'height':
        return Math.max(0, parseInt(value) || 0);
      case 'fontSize':
        return Math.max(8, Math.min(72, parseInt(value) || 14));
      case 'borderWidth':
        return Math.max(0, Math.min(20, parseInt(value) || 0));
      case 'borderRadius':
        return Math.max(0, Math.min(100, parseInt(value) || 0));
      case 'rotation':
        return ((parseInt(value) || 0) % 360 + 360) % 360;
      case 'scale':
        return Math.max(10, Math.min(200, parseInt(value) || 100));
      case 'opacity':
        return Math.max(0, Math.min(100, parseInt(value) || 100));
      case 'brightness':
      case 'contrast':
      case 'saturate':
        return Math.max(0, Math.min(200, parseInt(value) || 100));
      case 'shadowOffsetX':
      case 'shadowOffsetY':
        return Math.max(-50, Math.min(50, parseInt(value) || 0));
      default:
        return value;
    }
  };

  // Nettoyer les timeouts au démontage
  (0,react.useEffect)(function () {
    return function () {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }
    };
  }, []);

  // Synchronisation automatique lors des changements d'éléments
  (0,react.useEffect)(function () {
    // Sauvegarder automatiquement quand les éléments changent
    if (autoSave && pendingChangesRef.current.size > 0) {
      syncPendingChanges();
    }
  }, [elements, autoSave, syncPendingChanges]);
  return {
    // Méthodes de synchronisation
    sync: debouncedSync,
    syncImmediate: immediateSync,
    syncPending: syncPendingChanges,
    forceSync: forceSync,
    // Gestion des changements en attente
    hasPendingChanges: hasPendingChanges,
    getPendingChanges: getPendingChanges,
    cancelPendingChanges: cancelPendingChanges,
    // Validation et restauration
    validateAndSync: validateAndSync,
    restoreLastSaved: restoreLastSaved,
    // État
    pendingChangesCount: pendingChangesRef.current.size
  };
};
;// ./src/components/PropertiesPanel.jsx
function PropertiesPanel_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return PropertiesPanel_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (PropertiesPanel_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, PropertiesPanel_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, PropertiesPanel_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), PropertiesPanel_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", PropertiesPanel_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), PropertiesPanel_regeneratorDefine2(u), PropertiesPanel_regeneratorDefine2(u, o, "Generator"), PropertiesPanel_regeneratorDefine2(u, n, function () { return this; }), PropertiesPanel_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (PropertiesPanel_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function PropertiesPanel_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } PropertiesPanel_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { PropertiesPanel_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, PropertiesPanel_regeneratorDefine2(e, r, n, t); }
function PropertiesPanel_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function PropertiesPanel_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { PropertiesPanel_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { PropertiesPanel_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function PropertiesPanel_slicedToArray(r, e) { return PropertiesPanel_arrayWithHoles(r) || PropertiesPanel_iterableToArrayLimit(r, e) || PropertiesPanel_unsupportedIterableToArray(r, e) || PropertiesPanel_nonIterableRest(); }
function PropertiesPanel_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PropertiesPanel_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function PropertiesPanel_arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function PropertiesPanel_toConsumableArray(r) { return PropertiesPanel_arrayWithoutHoles(r) || PropertiesPanel_iterableToArray(r) || PropertiesPanel_unsupportedIterableToArray(r) || PropertiesPanel_nonIterableSpread(); }
function PropertiesPanel_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PropertiesPanel_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return PropertiesPanel_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? PropertiesPanel_arrayLikeToArray(r, a) : void 0; } }
function PropertiesPanel_iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function PropertiesPanel_arrayWithoutHoles(r) { if (Array.isArray(r)) return PropertiesPanel_arrayLikeToArray(r); }
function PropertiesPanel_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }






// Profils de propriétés contextuelles par type d'élément
var ELEMENT_PROPERTY_PROFILES = {
  // Éléments texte
  text: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-header': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-footer': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  'layout-section': {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['text', 'variables'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Éléments image/logo
  logo: {
    appearance: ['colors', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['image'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Logo entreprise (même propriétés que logo)
  company_logo: {
    appearance: ['colors', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['image'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Tableaux produits
  product_table: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['table'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Informations client
  customer_info: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['customer_fields'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Informations entreprise
  company_info: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['company_fields'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Type de document
  document_type: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['document_type'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Numéro de commande
  order_number: {
    appearance: ['colors', 'font', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: ['order_number'],
    effects: ['opacity', 'shadows', 'filters']
  },
  // Éléments par défaut (forme géométrique)
  "default": {
    appearance: ['colors', 'borders', 'effects'],
    layout: ['position', 'dimensions', 'transform', 'layers'],
    content: [],
    effects: ['opacity', 'shadows', 'filters']
  }
};

// Fonction helper pour parser les valeurs numériques de manière sécurisée
var safeParseInt = function safeParseInt(value) {
  var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  if (value === null || value === undefined || value === '') return defaultValue;
  var parsed = parseInt(value, 10);
  return isNaN(parsed) ? defaultValue : parsed;
};
var safeParseFloat = function safeParseFloat(value) {
  var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  if (value === null || value === undefined || value === '') return defaultValue;
  var parsed = parseFloat(value);
  return isNaN(parsed) ? defaultValue : parsed;
};

// Composant amélioré pour les contrôles de couleur avec presets
var ColorPicker = function ColorPicker(_ref) {
  var label = _ref.label,
    value = _ref.value,
    _onChange = _ref.onChange,
    _ref$presets = _ref.presets,
    presets = _ref$presets === void 0 ? [] : _ref$presets,
    _ref$defaultColor = _ref.defaultColor,
    defaultColor = _ref$defaultColor === void 0 ? '#ffffff' : _ref$defaultColor;
  // Fonction pour valider et normaliser une couleur hex
  var normalizeColor = function normalizeColor(color) {
    if (!color || color === 'transparent') return defaultColor;
    if (color.startsWith('#') && (color.length === 4 || color.length === 7)) return color;
    return defaultColor; // fallback
  };

  // Valeur normalisée pour l'input color
  var inputValue = normalizeColor(value);

  // Fonction pour vérifier si une couleur est valide pour les presets
  var isValidColor = function isValidColor(color) {
    return color && color !== 'transparent' && color.startsWith('#');
  };
  return /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, label, ":"), /*#__PURE__*/React.createElement("div", {
    className: "color-picker-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "color",
    value: inputValue,
    onChange: function onChange(e) {
      var newColor = e.target.value;
      _onChange(newColor);
    },
    className: "color-input",
    title: "Couleur actuelle: ".concat(value || 'transparent')
  }), /*#__PURE__*/React.createElement("div", {
    className: "color-presets"
  }, presets.filter(isValidColor).map(function (preset, index) {
    return /*#__PURE__*/React.createElement("button", {
      key: index,
      className: "color-preset ".concat(value === preset ? 'active' : ''),
      style: {
        backgroundColor: preset,
        border: value === preset ? '2px solid #2563eb' : '1px solid #e2e8f0'
      },
      onClick: function onClick() {
        return _onChange(preset);
      },
      title: "".concat(label, ": ").concat(preset),
      "aria-label": "S\xE9lectionner la couleur ".concat(preset)
    });
  }), presets.includes('transparent') && /*#__PURE__*/React.createElement("button", {
    className: "color-preset transparent ".concat(value === 'transparent' ? 'active' : ''),
    style: {
      background: value === 'transparent' ? 'repeating-conic-gradient(#f0f0f0 0% 25%, #ffffff 0% 50%) 50% / 10px 10px' : 'repeating-conic-gradient(#e2e8f0 0% 25%, #ffffff 0% 50%) 50% / 10px 10px',
      border: value === 'transparent' ? '2px solid #2563eb' : '1px solid #e2e8f0'
    },
    onClick: function onClick() {
      return _onChange('transparent');
    },
    title: "".concat(label, ": Transparent"),
    "aria-label": "Rendre transparent"
  }))));
};

// Composant pour les contrôles de police
var FontControls = function FontControls(_ref2) {
  var _properties$fontSize, _properties$fontSize2, _properties$lineHeigh, _properties$lineHeigh2, _properties$letterSpa, _properties$letterSpa2, _properties$opacity, _properties$opacity2, _properties$textShado, _properties$textShado2;
  var elementId = _ref2.elementId,
    properties = _ref2.properties,
    onPropertyChange = _ref2.onPropertyChange;
  return /*#__PURE__*/React.createElement("div", {
    className: "properties-group"
  }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFA8 Police & Style"), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Famille:"), /*#__PURE__*/React.createElement("select", {
    value: properties.fontFamily || 'Inter',
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'fontFamily', e.target.value);
    }
  }, /*#__PURE__*/React.createElement("option", {
    value: "Inter"
  }, "Inter"), /*#__PURE__*/React.createElement("option", {
    value: "Arial"
  }, "Arial"), /*#__PURE__*/React.createElement("option", {
    value: "Helvetica"
  }, "Helvetica"), /*#__PURE__*/React.createElement("option", {
    value: "Times New Roman"
  }, "Times New Roman"), /*#__PURE__*/React.createElement("option", {
    value: "Courier New"
  }, "Courier New"), /*#__PURE__*/React.createElement("option", {
    value: "Georgia"
  }, "Georgia"), /*#__PURE__*/React.createElement("option", {
    value: "Verdana"
  }, "Verdana"), /*#__PURE__*/React.createElement("option", {
    value: "Roboto"
  }, "Roboto"), /*#__PURE__*/React.createElement("option", {
    value: "Open Sans"
  }, "Open Sans"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Taille:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "8",
    max: "72",
    value: (_properties$fontSize = properties.fontSize) !== null && _properties$fontSize !== void 0 ? _properties$fontSize : 14,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'fontSize', safeParseInt(e.target.value, 14));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_properties$fontSize2 = properties.fontSize) !== null && _properties$fontSize2 !== void 0 ? _properties$fontSize2 : 14, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Interligne:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0.8",
    max: "3",
    step: "0.1",
    value: (_properties$lineHeigh = properties.lineHeight) !== null && _properties$lineHeigh !== void 0 ? _properties$lineHeigh : 1.2,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'lineHeight', safeParseFloat(e.target.value, 1.2));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_properties$lineHeigh2 = properties.lineHeight) !== null && _properties$lineHeigh2 !== void 0 ? _properties$lineHeigh2 : 1.2))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Espacement lettres:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "-2",
    max: "10",
    step: "0.1",
    value: (_properties$letterSpa = properties.letterSpacing) !== null && _properties$letterSpa !== void 0 ? _properties$letterSpa : 0,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'letterSpacing', safeParseFloat(e.target.value, 0));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_properties$letterSpa2 = properties.letterSpacing) !== null && _properties$letterSpa2 !== void 0 ? _properties$letterSpa2 : 0, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Opacit\xE9 texte:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "1",
    step: "0.1",
    value: (_properties$opacity = properties.opacity) !== null && _properties$opacity !== void 0 ? _properties$opacity : 1,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'opacity', safeParseFloat(e.target.value, 1));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, Math.round(((_properties$opacity2 = properties.opacity) !== null && _properties$opacity2 !== void 0 ? _properties$opacity2 : 1) * 100), "%"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Ombre texte:"), /*#__PURE__*/React.createElement("div", {
    className: "slider-container"
  }, /*#__PURE__*/React.createElement("input", {
    type: "range",
    min: "0",
    max: "5",
    step: "0.1",
    value: (_properties$textShado = properties.textShadowBlur) !== null && _properties$textShado !== void 0 ? _properties$textShado : 0,
    onChange: function onChange(e) {
      return onPropertyChange(elementId, 'textShadowBlur', safeParseFloat(e.target.value, 0));
    },
    className: "slider"
  }), /*#__PURE__*/React.createElement("span", {
    className: "slider-value"
  }, (_properties$textShado2 = properties.textShadowBlur) !== null && _properties$textShado2 !== void 0 ? _properties$textShado2 : 0, "px"))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Style du texte:"), /*#__PURE__*/React.createElement("div", {
    className: "style-buttons-grid"
  }, /*#__PURE__*/React.createElement("button", {
    className: "style-btn ".concat(properties.fontWeight === 'bold' ? 'active' : ''),
    onClick: function onClick() {
      return onPropertyChange(elementId, 'fontWeight', properties.fontWeight === 'bold' ? 'normal' : 'bold');
    },
    title: "Gras"
  }, /*#__PURE__*/React.createElement("strong", null, "B")), /*#__PURE__*/React.createElement("button", {
    className: "style-btn ".concat(properties.fontStyle === 'italic' ? 'active' : ''),
    onClick: function onClick() {
      return onPropertyChange(elementId, 'fontStyle', properties.fontStyle === 'italic' ? 'normal' : 'italic');
    },
    title: "Italique"
  }, /*#__PURE__*/React.createElement("em", null, "I")), /*#__PURE__*/React.createElement("button", {
    className: "style-btn ".concat((properties.textDecoration || '').includes('underline') ? 'active' : ''),
    onClick: function onClick() {
      var currentDecorations = properties.textDecoration ? properties.textDecoration.split(' ') : [];
      var hasUnderline = currentDecorations.includes('underline');
      var newDecorations = hasUnderline ? currentDecorations.filter(function (d) {
        return d !== 'underline';
      }) : [].concat(PropertiesPanel_toConsumableArray(currentDecorations), ['underline']);
      onPropertyChange(elementId, 'textDecoration', newDecorations.join(' ') || 'none');
    },
    title: "Soulign\xE9"
  }, /*#__PURE__*/React.createElement("u", null, "U")), /*#__PURE__*/React.createElement("button", {
    className: "style-btn ".concat((properties.textDecoration || '').includes('line-through') ? 'active' : ''),
    onClick: function onClick() {
      var currentDecorations = properties.textDecoration ? properties.textDecoration.split(' ') : [];
      var hasLineThrough = currentDecorations.includes('line-through');
      var newDecorations = hasLineThrough ? currentDecorations.filter(function (d) {
        return d !== 'line-through';
      }) : [].concat(PropertiesPanel_toConsumableArray(currentDecorations), ['line-through']);
      onPropertyChange(elementId, 'textDecoration', newDecorations.join(' ') || 'none');
    },
    title: "Barr\xE9"
  }, /*#__PURE__*/React.createElement("s", null, "S")))), /*#__PURE__*/React.createElement("div", {
    className: "property-row"
  }, /*#__PURE__*/React.createElement("label", null, "Alignement:"), /*#__PURE__*/React.createElement("div", {
    className: "alignment-buttons"
  }, [{
    value: 'left',
    icon: '⬅️',
    label: 'Gauche'
  }, {
    value: 'center',
    icon: '⬌',
    label: 'Centre'
  }, {
    value: 'right',
    icon: '➡️',
    label: 'Droite'
  }, {
    value: 'justify',
    icon: '⬌⬅️',
    label: 'Justifié'
  }].map(function (_ref3) {
    var value = _ref3.value,
      icon = _ref3.icon,
      label = _ref3.label;
    return /*#__PURE__*/React.createElement("button", {
      key: value,
      className: "align-btn ".concat(properties.textAlign === value ? 'active' : ''),
      onClick: function onClick() {
        return onPropertyChange(elementId, 'textAlign', value);
      },
      title: label
    }, icon);
  }))));
};
var PropertiesPanel = /*#__PURE__*/(0,react.memo)(function (_ref4) {
  var selectedElements = _ref4.selectedElements,
    elements = _ref4.elements,
    onPropertyChange = _ref4.onPropertyChange,
    onBatchUpdate = _ref4.onBatchUpdate;
  // États pour mémoriser les valeurs précédentes
  var _useState = (0,react.useState)('#ffffff'),
    _useState2 = PropertiesPanel_slicedToArray(_useState, 2),
    previousBackgroundColor = _useState2[0],
    setPreviousBackgroundColor = _useState2[1];
  var _useState3 = (0,react.useState)(0),
    _useState4 = PropertiesPanel_slicedToArray(_useState3, 2),
    previousBorderWidth = _useState4[0],
    setPreviousBorderWidth = _useState4[1];
  var _useState5 = (0,react.useState)('#000000'),
    _useState6 = PropertiesPanel_slicedToArray(_useState5, 2),
    previousBorderColor = _useState6[0],
    setPreviousBorderColor = _useState6[1];
  var _useState7 = (0,react.useState)(false),
    _useState8 = PropertiesPanel_slicedToArray(_useState7, 2),
    isBackgroundEnabled = _useState8[0],
    setIsBackgroundEnabled = _useState8[1];
  var _useState9 = (0,react.useState)(false),
    _useState0 = PropertiesPanel_slicedToArray(_useState9, 2),
    isBorderEnabled = _useState0[0],
    setIsBorderEnabled = _useState0[1];

  // Utiliser les hooks de personnalisation et synchronisation
  var _useElementCustomizat = useElementCustomization(selectedElements, elements, onPropertyChange),
    localProperties = _useElementCustomizat.localProperties,
    activeTab = _useElementCustomizat.activeTab,
    setActiveTab = _useElementCustomizat.setActiveTab,
    customizationChange = _useElementCustomizat.handlePropertyChange;
  var _useElementSynchroniz = useElementSynchronization(elements, onPropertyChange, onBatchUpdate, true,
    // autoSave
    3000 // autoSaveDelay - increased to reduce AJAX calls
    ),
    syncImmediate = _useElementSynchroniz.syncImmediate,
    syncBatch = _useElementSynchroniz.syncBatch;

  // Obtenir l'élément sélectionné (mémorisé pour éviter les re-renders)
  var selectedElement = (0,react.useMemo)(function () {
    return selectedElements.length > 0 ? elements.find(function (el) {
      return el.id === selectedElements[0];
    }) : null;
  }, [selectedElements, elements]);

  // Mettre à jour les valeurs précédentes quand l'élément change
  (0,react.useEffect)(function () {
    if (selectedElement) {
      // Initialiser les valeurs précédentes avec les valeurs actuelles de l'élément
      setPreviousBackgroundColor(selectedElement.backgroundColor || '#ffffff');
      // Pour borderWidth, s'assurer qu'on a au moins 1 pour la restauration
      var initialBorderWidth = selectedElement.borderWidth && selectedElement.borderWidth > 0 ? selectedElement.borderWidth : 1;
      setPreviousBorderWidth(initialBorderWidth);
      setPreviousBorderColor(selectedElement.borderColor || '#000000');
    }
  }, [selectedElement]); // Ne dépendre que de selectedElement pour éviter les boucles

  // Synchroniser l'état du toggle fond
  (0,react.useEffect)(function () {
    var shouldBeEnabled = !!localProperties.backgroundColor && localProperties.backgroundColor !== 'transparent';
    setIsBackgroundEnabled(shouldBeEnabled);
  }, [localProperties.backgroundColor]);

  // Synchroniser l'état du toggle bordures
  (0,react.useEffect)(function () {
    setIsBorderEnabled(!!localProperties.border && (localProperties.borderWidth || 0) > 0);
  }, [localProperties.border, localProperties.borderWidth]);

  // Gestionnaire unifié de changement de propriété
  var handlePropertyChange = (0,react.useCallback)(function (elementId, property, value) {
    // Empêcher la couleur du texte d'être transparente
    if (property === 'color' && value === 'transparent') {
      value = '#333333';
    }

    // Validation via le service (sauf pour les propriétés boolean qui sont toujours valides)
    var isBooleanProperty = typeof value === 'boolean' || property.startsWith('columns.');
    var validatedValue = value; // Valeur par défaut

    if (!isBooleanProperty) {
      try {
        validatedValue = elementCustomizationService.validateProperty(property, value);
        if (validatedValue === undefined || validatedValue === null) {
          console.warn("Propri\xE9t\xE9 invalide: ".concat(property, " = ").concat(value));
          return;
        }
      } catch (error) {
        console.warn("Erreur de validation pour ".concat(property, ":"), error);
        return;
      }
    }

    // Utiliser le hook de personnalisation pour la gestion locale
    customizationChange(elementId, property, validatedValue);

    // Synchronisation immédiate pour les changements critiques
    if (['x', 'y', 'width', 'height'].includes(property)) {
      syncImmediate(elementId, property, validatedValue);
    }
  }, [customizationChange, syncImmediate]);

  // Gestionnaire pour le toggle "Aucun fond"
  var handleNoBackgroundToggle = (0,react.useCallback)(function (elementId, checked) {
    // Vérifier si la propriété backgroundColor est autorisée pour ce type d'élément
    var isBackgroundAllowed = selectedElement !== null && selectedElement !== void 0 && selectedElement.type ? isPropertyAllowed(selectedElement.type, 'backgroundColor') : true;
    if (!isBackgroundAllowed) {
      console.warn('Fond non contrôlable pour ce type d\'élément');
      return;
    }
    if (checked) {
      // Sauvegarder la couleur actuelle avant de la désactiver
      if (selectedElement !== null && selectedElement !== void 0 && selectedElement.backgroundColor && selectedElement.backgroundColor !== 'transparent') {
        setPreviousBackgroundColor(selectedElement.backgroundColor);
      } else if (!previousBackgroundColor) {
        // Si pas de couleur précédente sauvegardée, utiliser la valeur par défaut
        setPreviousBackgroundColor('#ffffff');
      }
      handlePropertyChange(elementId, 'backgroundColor', 'transparent');
    } else {
      // Restaurer la couleur précédente (avec fallback)
      var colorToRestore = previousBackgroundColor || '#ffffff';
      handlePropertyChange(elementId, 'backgroundColor', colorToRestore);
    }
  }, [selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.backgroundColor, previousBackgroundColor, handlePropertyChange, selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.type]);

  // Gestionnaire pour le toggle "Aucune bordure"
  var handleNoBorderToggle = (0,react.useCallback)(function (elementId, checked) {
    if (checked) {
      // Sauvegarder l'épaisseur actuelle avant de la désactiver
      if (selectedElement !== null && selectedElement !== void 0 && selectedElement.borderWidth && selectedElement.borderWidth > 0) {
        setPreviousBorderWidth(selectedElement.borderWidth);
      } else {
        // Si pas de bordure ou bordure = 0, sauvegarder 2 comme valeur par défaut (plus visible)
        setPreviousBorderWidth(2);
      }
      handlePropertyChange(elementId, 'borderWidth', 0);
    } else {
      // Restaurer l'épaisseur précédente, au minimum 2
      var widthToRestore = Math.max(previousBorderWidth || 2, 2);
      handlePropertyChange(elementId, 'borderWidth', widthToRestore);
    }
  }, [selectedElement === null || selectedElement === void 0 ? void 0 : selectedElement.borderWidth, previousBorderWidth, handlePropertyChange]);

  // Rendu des onglets
  var renderTabs = (0,react.useCallback)(function () {
    return /*#__PURE__*/React.createElement("div", {
      className: "properties-tabs"
    }, /*#__PURE__*/React.createElement("button", {
      className: "tab-btn ".concat(activeTab === 'appearance' ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab('appearance');
      }
    }, "\uD83C\uDFA8 Apparence"), /*#__PURE__*/React.createElement("button", {
      className: "tab-btn ".concat(activeTab === 'layout' ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab('layout');
      }
    }, "\uD83D\uDCD0 Mise en page"), /*#__PURE__*/React.createElement("button", {
      className: "tab-btn ".concat(activeTab === 'content' ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab('content');
      }
    }, "\uD83D\uDCDD Contenu"), /*#__PURE__*/React.createElement("button", {
      className: "tab-btn ".concat(activeTab === 'effects' ? 'active' : ''),
      onClick: function onClick() {
        return setActiveTab('effects');
      }
    }, "\u2728 Effets"));
  }, [activeTab]);

  // Rendu du contenu selon l'onglet actif
  var renderTabContent = (0,react.useCallback)(function () {
    var _localProperties$back, _localProperties$back2, _localProperties$bord, _localProperties$bord2, _localProperties$bord3, _localProperties$bord4, _localProperties$boxS, _localProperties$boxS2, _localProperties$boxS3, _localProperties$boxS4, _localProperties$show, _localProperties$show2, _localProperties$show3, _localProperties$show4, _localProperties$show5, _localProperties$show6, _localProperties$show7;
    if (!selectedElement) {
      return /*#__PURE__*/React.createElement("div", {
        className: "no-selection"
      }, /*#__PURE__*/React.createElement("div", {
        className: "no-selection-icon"
      }, "\uD83D\uDC46"), /*#__PURE__*/React.createElement("p", null, "S\xE9lectionnez un \xE9l\xE9ment pour modifier ses propri\xE9t\xE9s"), selectedElements.length > 1 && /*#__PURE__*/React.createElement("p", {
        className: "selection-info"
      }, selectedElements.length, " \xE9l\xE9ments s\xE9lectionn\xE9s"));
    }

    // Obtenir le profil de propriétés pour ce type d'élément
    var elementProfile = ELEMENT_PROPERTY_PROFILES[selectedElement.type] || ELEMENT_PROPERTY_PROFILES["default"];
    var allowedControls = elementProfile[activeTab] || [];
    switch (activeTab) {
      case 'appearance':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFA8 Couleurs & Apparence"), /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Texte",
          value: localProperties.color,
          onChange: function onChange(value) {
            handlePropertyChange(selectedElement.id, 'color', value);
          },
          presets: ['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#000000'],
          defaultColor: "#333333"
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("span", null, "Fond activ\xE9:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: isBackgroundEnabled,
          disabled: false,
          onChange: function onChange(e) {
            if (e.target.checked) {
              handlePropertyChange(selectedElement.id, 'backgroundColor', '#ffffff');
            } else {
              handlePropertyChange(selectedElement.id, 'backgroundColor', 'transparent');
            }
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), /*#__PURE__*/React.createElement("div", {
          style: {
            display: isBackgroundEnabled ? 'block' : 'none',
            transition: 'opacity 0.3s ease'
          }
        }, /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Fond",
          value: localProperties.backgroundColor === 'transparent' ? '#ffffff' : localProperties.backgroundColor,
          onChange: function onChange(value) {
            handlePropertyChange(selectedElement.id, 'backgroundColor', value);
          },
          presets: ['transparent', '#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8']
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Opacit\xE9 fond:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "1",
          step: "0.1",
          value: (_localProperties$back = localProperties.backgroundOpacity) !== null && _localProperties$back !== void 0 ? _localProperties$back : 1,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'backgroundOpacity', safeParseFloat(e.target.value, 1));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, Math.round(((_localProperties$back2 = localProperties.backgroundOpacity) !== null && _localProperties$back2 !== void 0 ? _localProperties$back2 : 1) * 100), "%"))))), allowedControls.includes('font') && selectedElement.type !== 'product_table' && /*#__PURE__*/React.createElement(FontControls, {
          elementId: selectedElement.id,
          properties: localProperties,
          onPropertyChange: handlePropertyChange
        }), allowedControls.includes('borders') && localProperties.borderWidth >= 0 && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDD32 Bordures & Coins Arrondis"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("span", null, "Bordures activ\xE9es:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: isBorderEnabled,
          onChange: function onChange(e) {
            if (e.target.checked) {
              var widthToSet = previousBorderWidth || 1;
              var colorToSet = previousBorderColor || '#000000';
              handlePropertyChange(selectedElement.id, 'border', true);
              handlePropertyChange(selectedElement.id, 'borderWidth', widthToSet);
              handlePropertyChange(selectedElement.id, 'borderColor', colorToSet);
              setIsBorderEnabled(true);
            } else {
              setPreviousBorderWidth(localProperties.borderWidth || 1);
              setPreviousBorderColor(localProperties.borderColor || '#000000');
              handlePropertyChange(selectedElement.id, 'border', false);
              handlePropertyChange(selectedElement.id, 'borderWidth', 0);
              setIsBorderEnabled(false);
            }
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), /*#__PURE__*/React.createElement("div", {
          style: {
            display: localProperties.borderWidth > 0 ? 'block' : 'none',
            transition: 'opacity 0.3s ease'
          }
        }, /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Couleur bordure",
          value: localProperties.borderColor || '#000000',
          onChange: function onChange(value) {
            return handlePropertyChange(selectedElement.id, 'borderColor', value);
          },
          presets: ['#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155', '#000000']
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Style bordure:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.borderStyle || 'solid',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'borderStyle', e.target.value);
          },
          className: "styled-select"
        }, /*#__PURE__*/React.createElement("option", {
          value: "solid"
        }, "Continue"), /*#__PURE__*/React.createElement("option", {
          value: "dashed"
        }, "Tirets"), /*#__PURE__*/React.createElement("option", {
          value: "dotted"
        }, "Pointill\xE9s"), /*#__PURE__*/React.createElement("option", {
          value: "double"
        }, "Double"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "\xC9paisseur bordure:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "10",
          value: (_localProperties$bord = localProperties.borderWidth) !== null && _localProperties$bord !== void 0 ? _localProperties$bord : 1,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'borderWidth', safeParseInt(e.target.value, 1));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, (_localProperties$bord2 = localProperties.borderWidth) !== null && _localProperties$bord2 !== void 0 ? _localProperties$bord2 : 1, "px"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Coins arrondis:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "50",
          value: (_localProperties$bord3 = localProperties.borderRadius) !== null && _localProperties$bord3 !== void 0 ? _localProperties$bord3 : 4,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'borderRadius', safeParseInt(e.target.value, 0));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, (_localProperties$bord4 = localProperties.borderRadius) !== null && _localProperties$bord4 !== void 0 ? _localProperties$bord4 : 4, "px"))))), allowedControls.includes('effects') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\u2728 Effets"), /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Ombre",
          value: localProperties.boxShadowColor || '#000000',
          onChange: function onChange(value) {
            return handlePropertyChange(selectedElement.id, 'boxShadowColor', value);
          },
          presets: ['#000000', '#ffffff', '#64748b', '#ef4444', '#3b82f6']
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Flou ombre:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "20",
          value: (_localProperties$boxS = localProperties.boxShadowBlur) !== null && _localProperties$boxS !== void 0 ? _localProperties$boxS : 0,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'boxShadowBlur', safeParseInt(e.target.value, 0));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, (_localProperties$boxS2 = localProperties.boxShadowBlur) !== null && _localProperties$boxS2 !== void 0 ? _localProperties$boxS2 : 0, "px"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "D\xE9calage ombre:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "10",
          value: (_localProperties$boxS3 = localProperties.boxShadowSpread) !== null && _localProperties$boxS3 !== void 0 ? _localProperties$boxS3 : 0,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'boxShadowSpread', safeParseInt(e.target.value, 0));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, (_localProperties$boxS4 = localProperties.boxShadowSpread) !== null && _localProperties$boxS4 !== void 0 ? _localProperties$boxS4 : 0, "px")))));
      case 'layout':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, allowedControls.includes('position') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCD Position Pr\xE9cise"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "X:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-unit"
        }, /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: Math.round(localProperties.x || 0),
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'x', safeParseInt(e.target.value, 0));
          },
          step: "1"
        }), /*#__PURE__*/React.createElement("span", {
          className: "unit"
        }, "mm"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Y:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-unit"
        }, /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: Math.round(localProperties.y || 0),
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'y', safeParseInt(e.target.value, 0));
          },
          step: "1"
        }), /*#__PURE__*/React.createElement("span", {
          className: "unit"
        }, "mm")))), allowedControls.includes('dimensions') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCF Dimensions"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Largeur:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-unit"
        }, /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: Math.round(localProperties.width || 100),
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'width', safeParseInt(e.target.value, 100));
          },
          min: "1",
          step: "1"
        }), /*#__PURE__*/React.createElement("span", {
          className: "unit"
        }, "mm"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Hauteur:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-unit"
        }, /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: Math.round(localProperties.height || 50),
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'height', safeParseInt(e.target.value, 50));
          },
          min: "1",
          step: "1"
        }), /*#__PURE__*/React.createElement("span", {
          className: "unit"
        }, "mm"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Ratio:"), /*#__PURE__*/React.createElement("div", {
          className: "ratio-buttons"
        }, /*#__PURE__*/React.createElement("button", {
          className: "ratio-btn",
          onClick: function onClick() {
            var newHeight = (localProperties.width || 100) * 0.75;
            handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
          },
          title: "Format 4:3"
        }, "4:3"), /*#__PURE__*/React.createElement("button", {
          className: "ratio-btn",
          onClick: function onClick() {
            var newHeight = (localProperties.width || 100) * (297 / 210);
            handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
          },
          title: "Format A4"
        }, "A4"), /*#__PURE__*/React.createElement("button", {
          className: "ratio-btn",
          onClick: function onClick() {
            var newHeight = localProperties.width || 100;
            handlePropertyChange(selectedElement.id, 'height', Math.round(newHeight));
          },
          title: "Carr\xE9"
        }, "1:1")))), allowedControls.includes('transform') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDD04 Transformation"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Rotation:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "-180",
          max: "180",
          value: localProperties.rotation || 0,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'rotation', safeParseInt(e.target.value, 0));
          },
          onDoubleClick: function onDoubleClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', 0);
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.rotation || 0, "\xB0"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Rotation rapide:"), /*#__PURE__*/React.createElement("div", {
          className: "rotation-buttons"
        }, /*#__PURE__*/React.createElement("button", {
          className: "rotation-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', 0);
          },
          title: "Rotation 0\xB0"
        }, "\u21BB 0\xB0"), /*#__PURE__*/React.createElement("button", {
          className: "rotation-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', 90);
          },
          title: "Rotation 90\xB0"
        }, "\u21BB 90\xB0"), /*#__PURE__*/React.createElement("button", {
          className: "rotation-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', 180);
          },
          title: "Rotation 180\xB0"
        }, "\u21BB 180\xB0"), /*#__PURE__*/React.createElement("button", {
          className: "rotation-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'rotation', -90);
          },
          title: "Rotation -90\xB0"
        }, "\u21BA -90\xB0")))), allowedControls.includes('layers') && selectedElement.type !== 'product_table' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCDA Calques"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Profondeur (Z-index):"), /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: localProperties.zIndex || 0,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'zIndex', safeParseInt(e.target.value, 0));
          },
          min: "0",
          max: "100",
          step: "1"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Actions:"), /*#__PURE__*/React.createElement("div", {
          className: "layer-actions"
        }, /*#__PURE__*/React.createElement("button", {
          className: "layer-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'zIndex', (localProperties.zIndex || 0) + 1);
          },
          title: "Mettre devant"
        }, "\u2B06\uFE0F Devant"), /*#__PURE__*/React.createElement("button", {
          className: "layer-btn",
          onClick: function onClick() {
            return handlePropertyChange(selectedElement.id, 'zIndex', Math.max(0, (localProperties.zIndex || 0) - 1));
          },
          title: "Mettre derri\xE8re"
        }, "\u2B07\uFE0F Derri\xE8re")))));
      case 'content':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, allowedControls.includes('text') && selectedElement.type === 'text' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCDD Contenu texte"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte:"), /*#__PURE__*/React.createElement("textarea", {
          value: localProperties.text || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'text', e.target.value);
          },
          rows: 4,
          placeholder: "Saisissez votre texte ici..."
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Variables dynamiques:"), /*#__PURE__*/React.createElement("div", {
          className: "variables-list"
        }, /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{date}}');
          }
        }, "\uD83D\uDCC5 Date"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{order_number}}');
          }
        }, "[Ord] N\xB0 commande"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{customer_name}}');
          }
        }, "\uD83D\uDC64 Client"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{total}}');
          }
        }, "\uD83D\uDCB0 Total")))), allowedControls.includes('variables') && (selectedElement.type === 'layout-header' || selectedElement.type === 'layout-footer' || selectedElement.type === 'layout-section') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDD04 Variables dynamiques"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Variables disponibles:"), /*#__PURE__*/React.createElement("div", {
          className: "variables-list"
        }, /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{date}}');
          }
        }, "\uD83D\uDCC5 Date"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{order_number}}');
          }
        }, "[Ord] N\xB0 commande"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{customer_name}}');
          }
        }, "\uD83D\uDC64 Client"), /*#__PURE__*/React.createElement("button", {
          className: "variable-btn",
          onClick: function onClick() {
            var currentText = localProperties.text || '';
            handlePropertyChange(selectedElement.id, 'text', currentText + '{{total}}');
          }
        }, "\uD83D\uDCB0 Total")))), allowedControls.includes('image') && (selectedElement.type === 'logo' || selectedElement.type === 'company_logo') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "[Img] Image"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "URL de l'image:"), /*#__PURE__*/React.createElement("input", {
          type: "url",
          value: localProperties.imageUrl || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'imageUrl', e.target.value);
          },
          placeholder: "https://exemple.com/logo.jpg"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Alt text:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.alt || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'alt', e.target.value);
          },
          placeholder: "Logo de l'entreprise"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Adaptation:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.fit || 'contain',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'fit', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "contain"
        }, "Contenir (int\xE9gral)"), /*#__PURE__*/React.createElement("option", {
          value: "cover"
        }, "Couvrir (zoom)"), /*#__PURE__*/React.createElement("option", {
          value: "fill"
        }, "Remplir"), /*#__PURE__*/React.createElement("option", {
          value: "none"
        }, "Aucune")))), allowedControls.includes('table') && selectedElement.type === 'product_table' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCA Tableau produits"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Colonnes \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'image',
          label: 'Image'
        }, {
          key: 'name',
          label: 'Nom'
        }, {
          key: 'sku',
          label: 'SKU'
        }, {
          key: 'quantity',
          label: 'Quantité'
        }, {
          key: 'price',
          label: 'Prix'
        }, {
          key: 'total',
          label: 'Total'
        }].map(function (_ref5) {
          var _localProperties$colu, _localProperties$colu2;
          var key = _ref5.key,
            label = _ref5.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$colu = (_localProperties$colu2 = localProperties.columns) === null || _localProperties$colu2 === void 0 ? void 0 : _localProperties$colu2[key]) !== null && _localProperties$colu !== void 0 ? _localProperties$colu : true,
            onChange: function onChange(e) {
              handlePropertyChange(selectedElement.id, "columns.".concat(key), e.target.checked);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Style du tableau:"), /*#__PURE__*/React.createElement("div", {
          className: "table-style-selector"
        }, [{
          value: 'default',
          label: 'Défaut',
          headerBg: '#f8fafc',
          headerBorder: '#e2e8f0',
          rowBorder: '#f1f5f9',
          altRowBg: '#fafbfc',
          borderWidth: 1,
          textColor: '#334155'
        }, {
          value: 'classic',
          label: 'Classique',
          headerBg: '#1e293b',
          headerBorder: '#334155',
          rowBorder: '#334155',
          altRowBg: '#ffffff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'striped',
          label: 'Alterné',
          headerBg: '#3b82f6',
          headerBorder: '#2563eb',
          rowBorder: '#e2e8f0',
          altRowBg: '#f8fafc',
          borderWidth: 1,
          textColor: '#ffffff'
        }, {
          value: 'bordered',
          label: 'Encadré',
          headerBg: '#ffffff',
          headerBorder: '#374151',
          rowBorder: '#d1d5db',
          altRowBg: '#ffffff',
          borderWidth: 2,
          textColor: '#111827'
        }, {
          value: 'minimal',
          label: 'Minimal',
          headerBg: '#ffffff',
          headerBorder: '#f3f4f6',
          rowBorder: '#f9fafb',
          altRowBg: '#ffffff',
          borderWidth: 0.5,
          textColor: '#6b7280'
        }, {
          value: 'modern',
          label: 'Moderne',
          gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          headerBorder: '#5b21b6',
          rowBorder: '#e9d5ff',
          altRowBg: '#faf5ff',
          borderWidth: 1,
          textColor: '#ffffff'
        }, {
          value: 'blue_ocean',
          label: 'Océan Bleu',
          gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
          headerBorder: '#1e40af',
          rowBorder: '#dbeafe',
          altRowBg: '#eff6ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'emerald_forest',
          label: 'Forêt Émeraude',
          gradient: 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
          headerBorder: '#065f46',
          rowBorder: '#d1fae5',
          altRowBg: '#ecfdf5',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'sunset_orange',
          label: 'Coucher Orange',
          gradient: 'linear-gradient(135deg, #9a3412 0%, #f97316 100%)',
          headerBorder: '#c2410c',
          rowBorder: '#fed7aa',
          altRowBg: '#fff7ed',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'royal_purple',
          label: 'Royal Violet',
          gradient: 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)',
          headerBorder: '#7c3aed',
          rowBorder: '#e9d5ff',
          altRowBg: '#faf5ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'rose_pink',
          label: 'Rose Bonbon',
          gradient: 'linear-gradient(135deg, #be185d 0%, #f472b6 100%)',
          headerBorder: '#db2777',
          rowBorder: '#fce7f3',
          altRowBg: '#fdf2f8',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'teal_aqua',
          label: 'Aigue-marine',
          gradient: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
          headerBorder: '#0d9488',
          rowBorder: '#ccfbf1',
          altRowBg: '#f0fdfa',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'crimson_red',
          label: 'Rouge Cramoisi',
          gradient: 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)',
          headerBorder: '#dc2626',
          rowBorder: '#fecaca',
          altRowBg: '#fef2f2',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'amber_gold',
          label: 'Or Ambré',
          gradient: 'linear-gradient(135deg, #92400e 0%, #f59e0b 100%)',
          headerBorder: '#d97706',
          rowBorder: '#fef3c7',
          altRowBg: '#fffbeb',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'indigo_night',
          label: 'Nuit Indigo',
          gradient: 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)',
          headerBorder: '#4338ca',
          rowBorder: '#e0e7ff',
          altRowBg: '#eef2ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'slate_gray',
          label: 'Ardoise',
          gradient: 'linear-gradient(135deg, #374151 0%, #6b7280 100%)',
          headerBorder: '#4b5563',
          rowBorder: '#f3f4f6',
          altRowBg: '#f9fafb',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'coral_sunset',
          label: 'Corail Couchant',
          gradient: 'linear-gradient(135deg, #c2410c 0%, #fb7185 100%)',
          headerBorder: '#ea580c',
          rowBorder: '#fed7d7',
          altRowBg: '#fef7f7',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'mint_green',
          label: 'Menthe Fraîche',
          gradient: 'linear-gradient(135deg, #065f46 0%, #34d399 100%)',
          headerBorder: '#047857',
          rowBorder: '#d1fae5',
          altRowBg: '#ecfdf5',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'violet_dream',
          label: 'Rêve Violet',
          gradient: 'linear-gradient(135deg, #6d28d9 0%, #c084fc 100%)',
          headerBorder: '#8b5cf6',
          rowBorder: '#ede9fe',
          altRowBg: '#f5f3ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'sky_blue',
          label: 'Ciel Bleu',
          gradient: 'linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%)',
          headerBorder: '#0284c7',
          rowBorder: '#bae6fd',
          altRowBg: '#f0f9ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'forest_green',
          label: 'Vert Forêt',
          gradient: 'linear-gradient(135deg, #14532d 0%, #22c55e 100%)',
          headerBorder: '#15803d',
          rowBorder: '#bbf7d0',
          altRowBg: '#f0fdf4',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'ruby_red',
          label: 'Rouge Rubis',
          gradient: 'linear-gradient(135deg, #b91c1c 0%, #f87171 100%)',
          headerBorder: '#dc2626',
          rowBorder: '#fecaca',
          altRowBg: '#fef2f2',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'golden_yellow',
          label: 'Jaune Doré',
          gradient: 'linear-gradient(135deg, #a16207 0%, #eab308 100%)',
          headerBorder: '#ca8a04',
          rowBorder: '#fef08a',
          altRowBg: '#fefce8',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'navy_blue',
          label: 'Bleu Marine',
          gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
          headerBorder: '#1e40af',
          rowBorder: '#dbeafe',
          altRowBg: '#eff6ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'burgundy_wine',
          label: 'Vin Bordeaux',
          gradient: 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
          headerBorder: '#991b1b',
          rowBorder: '#fecaca',
          altRowBg: '#fef2f2',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'lavender_purple',
          label: 'Lavande',
          gradient: 'linear-gradient(135deg, #7c2d12 0%, #a855f7 100%)',
          headerBorder: '#9333ea',
          rowBorder: '#e9d5ff',
          altRowBg: '#faf5ff',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'ocean_teal',
          label: 'Océan Sarcelle',
          gradient: 'linear-gradient(135deg, #134e4a 0%, #14b8a6 100%)',
          headerBorder: '#0f766e',
          rowBorder: '#ccfbf1',
          altRowBg: '#f0fdfa',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'cherry_blossom',
          label: 'Cerisier',
          gradient: 'linear-gradient(135deg, #be185d 0%, #fb7185 100%)',
          headerBorder: '#db2777',
          rowBorder: '#fce7f3',
          altRowBg: '#fdf2f8',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }, {
          value: 'autumn_orange',
          label: 'Automne',
          gradient: 'linear-gradient(135deg, #9a3412 0%, #fb923c 100%)',
          headerBorder: '#ea580c',
          rowBorder: '#fed7aa',
          altRowBg: '#fff7ed',
          borderWidth: 1.5,
          textColor: '#ffffff'
        }].map(function (style) {
          return /*#__PURE__*/React.createElement("button", {
            key: style.value,
            type: "button",
            className: "table-style-option ".concat(localProperties.tableStyle === style.value ? 'active' : ''),
            onClick: function onClick() {
              return handlePropertyChange(selectedElement.id, 'tableStyle', style.value);
            },
            title: "".concat(style.label, " - Style ").concat(style.label.toLowerCase(), " avec d\xE9grad\xE9 moderne")
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-preview",
            style: {
              maxHeight: '60px',
              overflow: 'hidden'
            }
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-header",
            style: {
              background: style.gradient || style.headerBg,
              border: "".concat(style.borderWidth, "px solid ").concat(style.headerBorder),
              borderBottom: 'none',
              color: style.textColor
            }
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.headerBorder)
            }
          }, "Produit"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.headerBorder)
            }
          }, "Qt\xE9"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell"
          }, "Prix")), /*#__PURE__*/React.createElement("div", {
            className: "table-row",
            style: {
              backgroundColor: style.altRowBg,
              border: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder),
              borderTop: 'none',
              color: style.textColor
            }
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder)
            }
          }, "Article 1"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder)
            }
          }, "2"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell"
          }, "15.99\u20AC")), /*#__PURE__*/React.createElement("div", {
            className: "table-row",
            style: {
              backgroundColor: 'white',
              border: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder),
              borderTop: 'none',
              color: style.textColor
            }
          }, /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder)
            }
          }, "Article 2"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell",
            style: {
              borderRight: "".concat(style.borderWidth, "px solid ").concat(style.rowBorder)
            }
          }, "1"), /*#__PURE__*/React.createElement("div", {
            className: "table-cell"
          }, "8.50\u20AC"))), /*#__PURE__*/React.createElement("span", {
            className: "style-label"
          }, style.label));
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Lignes de totaux:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'showSubtotal',
          label: 'Sous-total'
        }, {
          key: 'showShipping',
          label: 'Frais de port'
        }, {
          key: 'showTaxes',
          label: 'Taxes'
        }, {
          key: 'showDiscount',
          label: 'Remise'
        }, {
          key: 'showTotal',
          label: 'Total général'
        }].map(function (_ref6) {
          var key = _ref6.key,
            label = _ref6.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: localProperties[key] || false,
            onChange: function onChange(e) {
              return handlePropertyChange(selectedElement.id, key, e.target.checked);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les bordures des cellules:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show = localProperties.showBorders) !== null && _localProperties$show !== void 0 ? _localProperties$show : true,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showBorders', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        })))), allowedControls.includes('customer_fields') && selectedElement.type === 'customer_info' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDC64 Informations client"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Champs \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'name',
          label: 'Nom'
        }, {
          key: 'email',
          label: 'Email'
        }, {
          key: 'phone',
          label: 'Téléphone'
        }, {
          key: 'address',
          label: 'Adresse'
        }, {
          key: 'company',
          label: 'Société'
        }, {
          key: 'vat',
          label: 'N° TVA'
        }, {
          key: 'siret',
          label: 'SIRET'
        }].map(function (_ref7) {
          var _localProperties$fiel, _localProperties$fiel2;
          var key = _ref7.key,
            label = _ref7.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$fiel = (_localProperties$fiel2 = localProperties.fields) === null || _localProperties$fiel2 === void 0 ? void 0 : _localProperties$fiel2.includes(key)) !== null && _localProperties$fiel !== void 0 ? _localProperties$fiel : true,
            onChange: function onChange(e) {
              var currentFields = localProperties.fields || ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'];
              var newFields = e.target.checked ? [].concat(PropertiesPanel_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
                return f !== key;
              });
              handlePropertyChange(selectedElement.id, 'fields', newFields);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Disposition:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.layout || 'vertical',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'layout', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "vertical"
        }, "Verticale"), /*#__PURE__*/React.createElement("option", {
          value: "horizontal"
        }, "Horizontale"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les \xE9tiquettes:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show2 = localProperties.showLabels) !== null && _localProperties$show2 !== void 0 ? _localProperties$show2 : true,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showLabels', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.showLabels && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Style des \xE9tiquettes:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.labelStyle || 'normal',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'labelStyle', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "normal"
        }, "Normal"), /*#__PURE__*/React.createElement("option", {
          value: "bold"
        }, "Gras"), /*#__PURE__*/React.createElement("option", {
          value: "uppercase"
        }, "Majuscules"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Espacement:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "20",
          value: localProperties.spacing || 8,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'spacing', safeParseInt(e.target.value, 10));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.spacing || 8, "px")))), allowedControls.includes('company_fields') && selectedElement.type === 'company_info' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFE2 Informations Entreprise"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Champs \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'name',
          label: 'Nom'
        }, {
          key: 'address',
          label: 'Adresse'
        }, {
          key: 'phone',
          label: 'Téléphone'
        }, {
          key: 'email',
          label: 'Email'
        }, {
          key: 'website',
          label: 'Site web'
        }, {
          key: 'vat',
          label: 'N° TVA'
        }, {
          key: 'rcs',
          label: 'RCS'
        }, {
          key: 'siret',
          label: 'SIRET'
        }].map(function (_ref8) {
          var _localProperties$fiel3, _localProperties$fiel4;
          var key = _ref8.key,
            label = _ref8.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$fiel3 = (_localProperties$fiel4 = localProperties.fields) === null || _localProperties$fiel4 === void 0 ? void 0 : _localProperties$fiel4.includes(key)) !== null && _localProperties$fiel3 !== void 0 ? _localProperties$fiel3 : true,
            onChange: function onChange(e) {
              var currentFields = localProperties.fields || ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
              var newFields = e.target.checked ? [].concat(PropertiesPanel_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
                return f !== key;
              });
              handlePropertyChange(selectedElement.id, 'fields', newFields);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Disposition:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.layout || 'vertical',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'layout', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "vertical"
        }, "Verticale"), /*#__PURE__*/React.createElement("option", {
          value: "horizontal"
        }, "Horizontale"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les \xE9tiquettes:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show3 = localProperties.showLabels) !== null && _localProperties$show3 !== void 0 ? _localProperties$show3 : false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showLabels', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.showLabels && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Style des \xE9tiquettes:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.labelStyle || 'normal',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'labelStyle', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "normal"
        }, "Normal"), /*#__PURE__*/React.createElement("option", {
          value: "bold"
        }, "Gras"), /*#__PURE__*/React.createElement("option", {
          value: "uppercase"
        }, "Majuscules"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Espacement:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "20",
          value: localProperties.spacing || 8,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'spacing', safeParseInt(e.target.value, 10));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.spacing || 8, "px")))), allowedControls.includes('document_type') && selectedElement.type === 'document_type' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCB Type de Document"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Type de document:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.documentType || 'invoice',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'documentType', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "invoice"
        }, "Facture"), /*#__PURE__*/React.createElement("option", {
          value: "quote"
        }, "Devis"), /*#__PURE__*/React.createElement("option", {
          value: "receipt"
        }, "Re\xE7u"), /*#__PURE__*/React.createElement("option", {
          value: "order"
        }, "Commande"), /*#__PURE__*/React.createElement("option", {
          value: "credit_note"
        }, "Avoir")))), allowedControls.includes('order_number') && selectedElement.type === 'order_number' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDD22 Num\xE9ro de Commande"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Format d'affichage:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.format || 'Commande #{order_number} - {order_date}',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'format', e.target.value);
          },
          placeholder: "Commande #{order_number} - {order_date}"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher l'\xE9tiquette:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show4 = localProperties.showLabel) !== null && _localProperties$show4 !== void 0 ? _localProperties$show4 : true,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showLabel', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.showLabel && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte de l'\xE9tiquette:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.labelText || 'N° de commande:',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'labelText', e.target.value);
          },
          placeholder: "N\xB0 de commande:"
        }))), allowedControls.includes('font') && /*#__PURE__*/React.createElement(FontControls, {
          elementId: selectedElement.id,
          properties: localProperties,
          onPropertyChange: handlePropertyChange
        }), allowedControls.includes('image') && (selectedElement.type === 'logo' || selectedElement.type === 'company_logo') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "[Img] Image"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "URL de l'image:"), /*#__PURE__*/React.createElement("div", {
          className: "input-with-button"
        }, /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.imageUrl || localProperties.src || '',
          onChange: function onChange(e) {
            handlePropertyChange(selectedElement.id, 'imageUrl', e.target.value);
            handlePropertyChange(selectedElement.id, 'src', e.target.value);
          },
          placeholder: "https://exemple.com/image.png"
        }), /*#__PURE__*/React.createElement("button", {
          type: "button",
          className: "media-button",
          onClick: /*#__PURE__*/PropertiesPanel_asyncToGenerator(/*#__PURE__*/PropertiesPanel_regenerator().m(function _callee() {
            var response, media, modal, modalContent, title, closeBtn, grid, _t;
            return PropertiesPanel_regenerator().w(function (_context) {
              while (1) switch (_context.p = _context.n) {
                case 0:
                  _context.p = 0;
                  _context.n = 1;
                  return fetch('/wp-json/wp/v2/media?media_type=image&per_page=50&_embed');
                case 1:
                  response = _context.v;
                  _context.n = 2;
                  return response.json();
                case 2:
                  media = _context.v;
                  // Créer une modale simple pour sélectionner l'image
                  modal = document.createElement('div');
                  modal.style.cssText = "\n                          position: fixed;\n                          top: 0;\n                          left: 0;\n                          width: 100%;\n                          height: 100%;\n                          background: rgba(0,0,0,0.8);\n                          z-index: 100;\n                          display: flex;\n                          align-items: center;\n                          justify-content: center;\n                        ";
                  modalContent = document.createElement('div');
                  modalContent.style.cssText = "\n                          background: white;\n                          padding: 20px;\n                          border-radius: 8px;\n                          max-width: 600px;\n                          max-height: 80vh;\n                          overflow-y: auto;\n                          width: 90%;\n                        ";
                  title = document.createElement('h3');
                  title.textContent = 'Sélectionner une image depuis la médiathèque';
                  title.style.marginBottom = '15px';
                  closeBtn = document.createElement('button');
                  closeBtn.textContent = '✕';
                  closeBtn.style.cssText = "\n                          position: absolute;\n                          top: 10px;\n                          right: 10px;\n                          background: none;\n                          border: none;\n                          font-size: 20px;\n                          cursor: pointer;\n                        ";
                  closeBtn.onclick = function () {
                    // Vérifier que la modale existe encore avant de la supprimer
                    if (modal && modal.parentNode === document.body) {
                      document.body.removeChild(modal);
                    }
                  };
                  grid = document.createElement('div');
                  grid.style.cssText = "\n                          display: grid;\n                          grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));\n                          gap: 10px;\n                          margin-top: 15px;\n                        ";
                  media.forEach(function (item) {
                    var imgContainer = document.createElement('div');
                    imgContainer.style.cssText = "\n                            border: 2px solid #ddd;\n                            border-radius: 4px;\n                            padding: 5px;\n                            cursor: pointer;\n                            transition: border-color 0.2s;\n                          ";
                    imgContainer.onmouseover = function () {
                      return imgContainer.style.borderColor = '#007cba';
                    };
                    imgContainer.onmouseout = function () {
                      return imgContainer.style.borderColor = '#ddd';
                    };
                    var img = document.createElement('img');
                    img.src = item.source_url;
                    img.style.cssText = "\n                            width: 100%;\n                            height: 80px;\n                            object-fit: cover;\n                            border-radius: 2px;\n                          ";
                    var name = document.createElement('div');
                    name.textContent = item.title.rendered.length > 15 ? item.title.rendered.substring(0, 15) + '...' : item.title.rendered;
                    name.style.cssText = "\n                            font-size: 11px;\n                            text-align: center;\n                            margin-top: 5px;\n                            color: #666;\n                          ";
                    imgContainer.onclick = function () {
                      handlePropertyChange(selectedElement.id, 'imageUrl', item.source_url);
                      handlePropertyChange(selectedElement.id, 'src', item.source_url);
                      // Vérifier que la modale existe encore avant de la supprimer
                      if (modal && modal.parentNode === document.body) {
                        document.body.removeChild(modal);
                      }
                    };
                    imgContainer.appendChild(img);
                    imgContainer.appendChild(name);
                    grid.appendChild(imgContainer);
                  });
                  modalContent.appendChild(title);
                  modalContent.appendChild(closeBtn);
                  modalContent.appendChild(grid);
                  modal.appendChild(modalContent);
                  document.body.appendChild(modal);
                  _context.n = 4;
                  break;
                case 3:
                  _context.p = 3;
                  _t = _context.v;
                  console.error('Erreur lors de la récupération des médias:', _t);
                  alert('Erreur lors de l\'accès à la médiathèque WordPress');
                case 4:
                  return _context.a(2);
              }
            }, _callee, null, [[0, 3]]);
          }))
        }, "\uD83D\uDCC1 M\xE9diath\xE8que"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte alternatif:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.alt || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'alt', e.target.value);
          },
          placeholder: "Description de l'image"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Ajustement:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.objectFit || localProperties.fit || 'cover',
          onChange: function onChange(e) {
            handlePropertyChange(selectedElement.id, 'objectFit', e.target.value);
            handlePropertyChange(selectedElement.id, 'fit', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "cover"
        }, "Couvrir"), /*#__PURE__*/React.createElement("option", {
          value: "contain"
        }, "Contenir"), /*#__PURE__*/React.createElement("option", {
          value: "fill"
        }, "Remplir"), /*#__PURE__*/React.createElement("option", {
          value: "none"
        }, "Aucun"), /*#__PURE__*/React.createElement("option", {
          value: "scale-down"
        }, "R\xE9duire")))), selectedElement.type === 'document_type' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCB Type de Document"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Type de document:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.documentType || 'invoice',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'documentType', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "invoice"
        }, "Facture"), /*#__PURE__*/React.createElement("option", {
          value: "quote"
        }, "Devis"), /*#__PURE__*/React.createElement("option", {
          value: "receipt"
        }, "Re\xE7u"), /*#__PURE__*/React.createElement("option", {
          value: "order"
        }, "Commande"), /*#__PURE__*/React.createElement("option", {
          value: "credit_note"
        }, "Avoir"))), /*#__PURE__*/React.createElement(FontControls, {
          elementId: selectedElement.id,
          properties: localProperties,
          onPropertyChange: handlePropertyChange
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Alignement du texte:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.textAlign || 'center',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'textAlign', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "left"
        }, "Gauche"), /*#__PURE__*/React.createElement("option", {
          value: "center"
        }, "Centre"), /*#__PURE__*/React.createElement("option", {
          value: "right"
        }, "Droite"))), /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Couleur du texte",
          value: localProperties.color,
          onChange: function onChange(value) {
            return handlePropertyChange(selectedElement.id, 'color', value);
          },
          presets: ['#1e293b', '#334155', '#475569', '#64748b', '#000000', '#dc2626', '#059669', '#7c3aed'],
          defaultColor: "#333333"
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher la bordure:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: localProperties.showBorder || false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showBorder', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Couleur de fond",
          value: localProperties.backgroundColor,
          onChange: function onChange(value) {
            return handlePropertyChange(selectedElement.id, 'backgroundColor', value);
          },
          presets: ['transparent', '#ffffff', '#f8fafc', '#fef3c7', '#ecfdf5', '#f0f9ff']
        })), selectedElement.type !== 'product_table' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uFFFD Contenu"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte/Contenu:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.content || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'content', e.target.value);
          },
          placeholder: "Texte \xE0 afficher"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Format:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.format || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'format', e.target.value);
          },
          placeholder: "Format d'affichage (optionnel)"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Type de document:"), /*#__PURE__*/React.createElement("select", {
          value: localProperties.documentType || 'invoice',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'documentType', e.target.value);
          }
        }, /*#__PURE__*/React.createElement("option", {
          value: "invoice"
        }, "Facture"), /*#__PURE__*/React.createElement("option", {
          value: "quote"
        }, "Devis"), /*#__PURE__*/React.createElement("option", {
          value: "receipt"
        }, "Re\xE7u"), /*#__PURE__*/React.createElement("option", {
          value: "order"
        }, "Commande"), /*#__PURE__*/React.createElement("option", {
          value: "credit_note"
        }, "Avoir")))), selectedElement.type !== 'product_table' && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83D\uDCCB Champs & Options"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Champs \xE0 afficher:"), /*#__PURE__*/React.createElement("div", {
          className: "checkbox-group"
        }, [{
          key: 'name',
          label: 'Nom'
        }, {
          key: 'address',
          label: 'Adresse'
        }, {
          key: 'phone',
          label: 'Téléphone'
        }, {
          key: 'email',
          label: 'Email'
        }, {
          key: 'website',
          label: 'Site web'
        }, {
          key: 'vat',
          label: 'N° TVA'
        }, {
          key: 'image',
          label: 'Image'
        }, {
          key: 'sku',
          label: 'SKU'
        }, {
          key: 'quantity',
          label: 'Quantité'
        }, {
          key: 'price',
          label: 'Prix'
        }, {
          key: 'total',
          label: 'Total'
        }].map(function (_ref0) {
          var _localProperties$fiel5, _localProperties$fiel6;
          var key = _ref0.key,
            label = _ref0.label;
          return /*#__PURE__*/React.createElement("label", {
            key: key,
            className: "checkbox-item"
          }, /*#__PURE__*/React.createElement("input", {
            type: "checkbox",
            checked: (_localProperties$fiel5 = (_localProperties$fiel6 = localProperties.fields) === null || _localProperties$fiel6 === void 0 ? void 0 : _localProperties$fiel6.includes(key)) !== null && _localProperties$fiel5 !== void 0 ? _localProperties$fiel5 : false,
            onChange: function onChange(e) {
              var currentFields = localProperties.fields || [];
              var newFields = e.target.checked ? [].concat(PropertiesPanel_toConsumableArray(currentFields), [key]) : currentFields.filter(function (f) {
                return f !== key;
              });
              handlePropertyChange(selectedElement.id, 'fields', newFields);
            }
          }), label);
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher l'\xE9tiquette:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show5 = localProperties.showLabel) !== null && _localProperties$show5 !== void 0 ? _localProperties$show5 : false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showLabel', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.showLabel && /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Texte de l'\xE9tiquette:"), /*#__PURE__*/React.createElement("input", {
          type: "text",
          value: localProperties.labelText || '',
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'labelText', e.target.value);
          },
          placeholder: "Texte de l'\xE9tiquette"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les bordures:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show6 = localProperties.showBorders) !== null && _localProperties$show6 !== void 0 ? _localProperties$show6 : true,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showBorders', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Afficher les en-t\xEAtes:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (_localProperties$show7 = localProperties.showHeaders) !== null && _localProperties$show7 !== void 0 ? _localProperties$show7 : false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'showHeaders', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        })))));
      case 'effects':
        return /*#__PURE__*/React.createElement("div", {
          className: "tab-content"
        }, allowedControls.includes('opacity') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDF1F Transparence & Visibilit\xE9"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Opacit\xE9:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "100",
          value: localProperties.opacity || 100,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'opacity', safeParseInt(e.target.value, 100));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.opacity || 100, "%"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Visibilit\xE9:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: localProperties.visible !== false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'visible', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        })))), allowedControls.includes('shadows') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\u2728 Ombres & Effets"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Ombre:"), /*#__PURE__*/React.createElement("label", {
          className: "toggle"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: localProperties.shadow || false,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'shadow', e.target.checked);
          }
        }), /*#__PURE__*/React.createElement("span", {
          className: "toggle-slider"
        }))), localProperties.shadow && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement(ColorPicker, {
          label: "Couleur ombre",
          value: localProperties.shadowColor,
          onChange: function onChange(value) {
            return handlePropertyChange(selectedElement.id, 'shadowColor', value);
          },
          presets: ['#000000', '#374151', '#6b7280', '#9ca3af']
        }), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "D\xE9calage X:"), /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: localProperties.shadowOffsetX || 2,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'shadowOffsetX', safeParseInt(e.target.value, 0));
          },
          min: "-20",
          max: "20"
        })), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "D\xE9calage Y:"), /*#__PURE__*/React.createElement("input", {
          type: "number",
          value: localProperties.shadowOffsetY || 2,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'shadowOffsetY', safeParseInt(e.target.value, 0));
          },
          min: "-20",
          max: "20"
        })))), allowedControls.includes('filters') && /*#__PURE__*/React.createElement("div", {
          className: "properties-group"
        }, /*#__PURE__*/React.createElement("h4", null, "\uD83C\uDFAD Filtres visuels"), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Luminosit\xE9:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "200",
          value: localProperties.brightness || 100,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'brightness', safeParseInt(e.target.value, 100));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.brightness || 100, "%"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Contraste:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "200",
          value: localProperties.contrast || 100,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'contrast', parseInt(e.target.value));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.contrast || 100, "%"))), /*#__PURE__*/React.createElement("div", {
          className: "property-row"
        }, /*#__PURE__*/React.createElement("label", null, "Saturation:"), /*#__PURE__*/React.createElement("div", {
          className: "slider-container"
        }, /*#__PURE__*/React.createElement("input", {
          type: "range",
          min: "0",
          max: "200",
          value: localProperties.saturate || 100,
          onChange: function onChange(e) {
            return handlePropertyChange(selectedElement.id, 'saturate', parseInt(e.target.value));
          },
          className: "slider"
        }), /*#__PURE__*/React.createElement("span", {
          className: "slider-value"
        }, localProperties.saturate || 100, "%")))));
      default:
        return null;
    }
  }, [activeTab, selectedElement, localProperties, handlePropertyChange, selectedElements.length]);
  return /*#__PURE__*/React.createElement("div", {
    className: "properties-panel"
  }, /*#__PURE__*/React.createElement("div", {
    className: "properties-header"
  }, /*#__PURE__*/React.createElement("h3", null, "Propri\xE9t\xE9s"), selectedElement && /*#__PURE__*/React.createElement("div", {
    className: "element-info"
  }, /*#__PURE__*/React.createElement("span", {
    className: "element-type"
  }, selectedElement.type), /*#__PURE__*/React.createElement("span", {
    className: "element-id"
  }, "#", selectedElement.id))), renderTabs(), /*#__PURE__*/React.createElement("div", {
    className: "properties-content"
  }, renderTabContent()));
});
/* harmony default export */ const components_PropertiesPanel = (PropertiesPanel);
;// ./src/components/NewTemplateModal.jsx
function NewTemplateModal_typeof(o) { "@babel/helpers - typeof"; return NewTemplateModal_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, NewTemplateModal_typeof(o); }
function NewTemplateModal_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function NewTemplateModal_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? NewTemplateModal_ownKeys(Object(t), !0).forEach(function (r) { NewTemplateModal_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : NewTemplateModal_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function NewTemplateModal_defineProperty(e, r, t) { return (r = NewTemplateModal_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function NewTemplateModal_toPropertyKey(t) { var i = NewTemplateModal_toPrimitive(t, "string"); return "symbol" == NewTemplateModal_typeof(i) ? i : i + ""; }
function NewTemplateModal_toPrimitive(t, r) { if ("object" != NewTemplateModal_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != NewTemplateModal_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function NewTemplateModal_slicedToArray(r, e) { return NewTemplateModal_arrayWithHoles(r) || NewTemplateModal_iterableToArrayLimit(r, e) || NewTemplateModal_unsupportedIterableToArray(r, e) || NewTemplateModal_nonIterableRest(); }
function NewTemplateModal_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function NewTemplateModal_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return NewTemplateModal_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? NewTemplateModal_arrayLikeToArray(r, a) : void 0; } }
function NewTemplateModal_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function NewTemplateModal_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function NewTemplateModal_arrayWithHoles(r) { if (Array.isArray(r)) return r; }

var NewTemplateModal = function NewTemplateModal(_ref) {
  var isOpen = _ref.isOpen,
    onClose = _ref.onClose,
    onCreateTemplate = _ref.onCreateTemplate;
  var _useState = (0,react.useState)({
      name: '',
      defaultModel: 'Facture',
      description: '',
      isPublic: false,
      paperFormat: 'A4 (210 × 297 mm)',
      orientation: 'Portrait',
      category: 'Facture'
    }),
    _useState2 = NewTemplateModal_slicedToArray(_useState, 2),
    formData = _useState2[0],
    setFormData = _useState2[1];
  var _useState3 = (0,react.useState)(false),
    _useState4 = NewTemplateModal_slicedToArray(_useState3, 2),
    showAdvanced = _useState4[0],
    setShowAdvanced = _useState4[1];
  var _useState5 = (0,react.useState)({}),
    _useState6 = NewTemplateModal_slicedToArray(_useState5, 2),
    errors = _useState6[0],
    setErrors = _useState6[1];
  var handleInputChange = function handleInputChange(field, value) {
    setFormData(function (prev) {
      return NewTemplateModal_objectSpread(NewTemplateModal_objectSpread({}, prev), {}, NewTemplateModal_defineProperty({}, field, value));
    });
    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(function (prev) {
        return NewTemplateModal_objectSpread(NewTemplateModal_objectSpread({}, prev), {}, NewTemplateModal_defineProperty({}, field, ''));
      });
    }
  };
  var validateForm = function validateForm() {
    var newErrors = {};
    if (!formData.name.trim()) {
      newErrors.name = 'Le nom du template est obligatoire';
    }
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };
  var handleSubmit = function handleSubmit(e) {
    e.preventDefault();
    if (validateForm()) {
      onCreateTemplate(formData);
      onClose();
      // Reset form
      setFormData({
        name: '',
        defaultModel: 'Facture',
        description: '',
        isPublic: false,
        paperFormat: 'A4 (210 × 297 mm)',
        orientation: 'Portrait',
        category: 'Facture'
      });
      setShowAdvanced(false);
    }
  };
  if (!isOpen) return null;
  return /*#__PURE__*/react.createElement("div", {
    className: "modal-overlay",
    onClick: onClose
  }, /*#__PURE__*/react.createElement("div", {
    className: "modal-content new-template-modal",
    onClick: function onClick(e) {
      return e.stopPropagation();
    }
  }, /*#__PURE__*/react.createElement("div", {
    className: "modal-header"
  }, /*#__PURE__*/react.createElement("h3", null, "Nouveau template"), /*#__PURE__*/react.createElement("button", {
    className: "modal-close",
    onClick: onClose
  }, "\xD7")), /*#__PURE__*/react.createElement("form", {
    onSubmit: handleSubmit,
    className: "modal-body"
  }, /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "template-name"
  }, "Nom du template *"), /*#__PURE__*/react.createElement("input", {
    id: "template-name",
    type: "text",
    value: formData.name,
    onChange: function onChange(e) {
      return handleInputChange('name', e.target.value);
    },
    className: errors.name ? 'error' : '',
    placeholder: "Ex: Facture Standard"
  }), errors.name && /*#__PURE__*/react.createElement("span", {
    className: "error-message"
  }, errors.name)), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "default-model"
  }, "Mod\xE8le par d\xE9faut"), /*#__PURE__*/react.createElement("select", {
    id: "default-model",
    value: formData.defaultModel,
    onChange: function onChange(e) {
      return handleInputChange('defaultModel', e.target.value);
    }
  }, /*#__PURE__*/react.createElement("option", {
    value: "Facture"
  }, "Facture"), /*#__PURE__*/react.createElement("option", {
    value: "Devis"
  }, "Devis"), /*#__PURE__*/react.createElement("option", {
    value: "Bon de commande"
  }, "Bon de commande"), /*#__PURE__*/react.createElement("option", {
    value: "Bon de livraison"
  }, "Bon de livraison"))), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "description"
  }, "Description"), /*#__PURE__*/react.createElement("textarea", {
    id: "description",
    value: formData.description,
    onChange: function onChange(e) {
      return handleInputChange('description', e.target.value);
    },
    placeholder: "Description du template...",
    rows: 3
  })), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("button", {
    type: "button",
    className: "advanced-toggle",
    onClick: function onClick() {
      return setShowAdvanced(!showAdvanced);
    }
  }, "Param\xE8tres avanc\xE9s ", showAdvanced ? '▼' : '▶')), showAdvanced && /*#__PURE__*/react.createElement("div", {
    className: "advanced-settings"
  }, /*#__PURE__*/react.createElement("div", {
    className: "form-group checkbox-group"
  }, /*#__PURE__*/react.createElement("label", {
    className: "checkbox-label"
  }, /*#__PURE__*/react.createElement("input", {
    type: "checkbox",
    checked: formData.isPublic,
    onChange: function onChange(e) {
      return handleInputChange('isPublic', e.target.checked);
    }
  }), /*#__PURE__*/react.createElement("span", null, "Template public (visible par tous les utilisateurs)"))), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "paper-format"
  }, "Format de papier"), /*#__PURE__*/react.createElement("select", {
    id: "paper-format",
    value: formData.paperFormat,
    onChange: function onChange(e) {
      return handleInputChange('paperFormat', e.target.value);
    }
  }, /*#__PURE__*/react.createElement("option", {
    value: "A4 (210 \xD7 297 mm)"
  }, "A4 (210 \xD7 297 mm)"), /*#__PURE__*/react.createElement("option", {
    value: "A5 (148 \xD7 210 mm)"
  }, "A5 (148 \xD7 210 mm)"), /*#__PURE__*/react.createElement("option", {
    value: "Lettre (8.5 \xD7 11 pouces)"
  }, "Lettre (8.5 \xD7 11 pouces)"), /*#__PURE__*/react.createElement("option", {
    value: "Legal (8.5 \xD7 14 pouces)"
  }, "Legal (8.5 \xD7 14 pouces)"))), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "orientation"
  }, "Orientation"), /*#__PURE__*/react.createElement("select", {
    id: "orientation",
    value: formData.orientation,
    onChange: function onChange(e) {
      return handleInputChange('orientation', e.target.value);
    }
  }, /*#__PURE__*/react.createElement("option", {
    value: "Portrait"
  }, "Portrait"), /*#__PURE__*/react.createElement("option", {
    value: "Paysage"
  }, "Paysage"))), /*#__PURE__*/react.createElement("div", {
    className: "form-group"
  }, /*#__PURE__*/react.createElement("label", {
    htmlFor: "category"
  }, "Cat\xE9gorie"), /*#__PURE__*/react.createElement("select", {
    id: "category",
    value: formData.category,
    onChange: function onChange(e) {
      return handleInputChange('category', e.target.value);
    }
  }, /*#__PURE__*/react.createElement("option", {
    value: "Facture"
  }, "Facture"), /*#__PURE__*/react.createElement("option", {
    value: "Devis"
  }, "Devis"), /*#__PURE__*/react.createElement("option", {
    value: "Bon de commande"
  }, "Bon de commande"), /*#__PURE__*/react.createElement("option", {
    value: "Bon de livraison"
  }, "Bon de livraison"), /*#__PURE__*/react.createElement("option", {
    value: "Re\xE7u"
  }, "Re\xE7u"), /*#__PURE__*/react.createElement("option", {
    value: "Autre"
  }, "Autre")))), /*#__PURE__*/react.createElement("div", {
    className: "modal-footer"
  }, /*#__PURE__*/react.createElement("button", {
    type: "button",
    className: "btn-secondary",
    onClick: onClose
  }, "Annuler"), /*#__PURE__*/react.createElement("button", {
    type: "submit",
    className: "btn-primary"
  }, "Ouvrir le template")))));
};
/* harmony default export */ const components_NewTemplateModal = (NewTemplateModal);
;// ./src/components/PDFCanvasEditor.jsx
function PDFCanvasEditor_typeof(o) { "@babel/helpers - typeof"; return PDFCanvasEditor_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, PDFCanvasEditor_typeof(o); }
function PDFCanvasEditor_regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return PDFCanvasEditor_regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (PDFCanvasEditor_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, PDFCanvasEditor_regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, PDFCanvasEditor_regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), PDFCanvasEditor_regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", PDFCanvasEditor_regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), PDFCanvasEditor_regeneratorDefine2(u), PDFCanvasEditor_regeneratorDefine2(u, o, "Generator"), PDFCanvasEditor_regeneratorDefine2(u, n, function () { return this; }), PDFCanvasEditor_regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (PDFCanvasEditor_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function PDFCanvasEditor_regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } PDFCanvasEditor_regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { PDFCanvasEditor_regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, PDFCanvasEditor_regeneratorDefine2(e, r, n, t); }
function PDFCanvasEditor_asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function PDFCanvasEditor_asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { PDFCanvasEditor_asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { PDFCanvasEditor_asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function PDFCanvasEditor_toConsumableArray(r) { return PDFCanvasEditor_arrayWithoutHoles(r) || PDFCanvasEditor_iterableToArray(r) || PDFCanvasEditor_unsupportedIterableToArray(r) || PDFCanvasEditor_nonIterableSpread(); }
function PDFCanvasEditor_nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PDFCanvasEditor_iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function PDFCanvasEditor_arrayWithoutHoles(r) { if (Array.isArray(r)) return PDFCanvasEditor_arrayLikeToArray(r); }
function PDFCanvasEditor_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function PDFCanvasEditor_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? PDFCanvasEditor_ownKeys(Object(t), !0).forEach(function (r) { PDFCanvasEditor_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : PDFCanvasEditor_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function PDFCanvasEditor_defineProperty(e, r, t) { return (r = PDFCanvasEditor_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function PDFCanvasEditor_toPropertyKey(t) { var i = PDFCanvasEditor_toPrimitive(t, "string"); return "symbol" == PDFCanvasEditor_typeof(i) ? i : i + ""; }
function PDFCanvasEditor_toPrimitive(t, r) { if ("object" != PDFCanvasEditor_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != PDFCanvasEditor_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function PDFCanvasEditor_slicedToArray(r, e) { return PDFCanvasEditor_arrayWithHoles(r) || PDFCanvasEditor_iterableToArrayLimit(r, e) || PDFCanvasEditor_unsupportedIterableToArray(r, e) || PDFCanvasEditor_nonIterableRest(); }
function PDFCanvasEditor_nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function PDFCanvasEditor_unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return PDFCanvasEditor_arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? PDFCanvasEditor_arrayLikeToArray(r, a) : void 0; } }
function PDFCanvasEditor_arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function PDFCanvasEditor_iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function PDFCanvasEditor_arrayWithHoles(r) { if (Array.isArray(r)) return r; }









// Import direct des composants (plus de lazy loading)







var PDFCanvasEditor = function PDFCanvasEditor(_ref) {
  var _window$pdfBuilderAja3, _window$pdfBuilderAja4;
  var options = _ref.options;
  var _useState = (0,react.useState)('select'),
    _useState2 = PDFCanvasEditor_slicedToArray(_useState, 2),
    tool = _useState2[0],
    setTool = _useState2[1];
  var _useState3 = (0,react.useState)(false),
    _useState4 = PDFCanvasEditor_slicedToArray(_useState3, 2),
    showPreviewModal = _useState4[0],
    setShowPreviewModal = _useState4[1];
  var _useState5 = (0,react.useState)(false),
    _useState6 = PDFCanvasEditor_slicedToArray(_useState5, 2),
    showNewTemplateModal = _useState6[0],
    setShowNewTemplateModal = _useState6[1];
  var _useState7 = (0,react.useState)(false),
    _useState8 = PDFCanvasEditor_slicedToArray(_useState7, 2),
    showPDFModal = _useState8[0],
    setShowPDFModal = _useState8[1];
  var _useState9 = (0,react.useState)(null),
    _useState0 = PDFCanvasEditor_slicedToArray(_useState9, 2),
    pdfModalUrl = _useState0[0],
    setPdfModalUrl = _useState0[1];
  var _useState1 = (0,react.useState)(false),
    _useState10 = PDFCanvasEditor_slicedToArray(_useState1, 2),
    isPropertiesCollapsed = _useState10[0],
    setIsPropertiesCollapsed = _useState10[1];

  // États pour le pan et la navigation
  var _useState11 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState12 = PDFCanvasEditor_slicedToArray(_useState11, 2),
    panOffset = _useState12[0],
    setPanOffset = _useState12[1];
  var _useState13 = (0,react.useState)(false),
    _useState14 = PDFCanvasEditor_slicedToArray(_useState13, 2),
    isPanning = _useState14[0],
    setIsPanning = _useState14[1];
  var _useState15 = (0,react.useState)({
      x: 0,
      y: 0
    }),
    _useState16 = PDFCanvasEditor_slicedToArray(_useState15, 2),
    lastPanPoint = _useState16[0],
    setLastPanPoint = _useState16[1];

  // États pour les guides
  var _useState17 = (0,react.useState)({
      horizontal: [],
      vertical: []
    }),
    _useState18 = PDFCanvasEditor_slicedToArray(_useState17, 2),
    guides = _useState18[0],
    setGuides = _useState18[1];
  var _useState19 = (0,react.useState)(false),
    _useState20 = PDFCanvasEditor_slicedToArray(_useState19, 2),
    isCreatingGuide = _useState20[0],
    setIsCreatingGuide = _useState20[1];
  var _useState21 = (0,react.useState)(null),
    _useState22 = PDFCanvasEditor_slicedToArray(_useState21, 2),
    guideCreationType = _useState22[0],
    setGuideCreationType = _useState22[1]; // 'horizontal' or 'vertical'

  // Hook pour les paramètres globaux
  var globalSettings = useGlobalSettings();

  // Fonctions pour gérer les guides
  var addHorizontalGuide = (0,react.useCallback)(function (y) {
    if (!globalSettings.settings.lockGuides) {
      setGuides(function (prev) {
        return PDFCanvasEditor_objectSpread(PDFCanvasEditor_objectSpread({}, prev), {}, {
          horizontal: [].concat(PDFCanvasEditor_toConsumableArray(prev.horizontal), [y]).sort(function (a, b) {
            return a - b;
          })
        });
      });
    }
  }, [globalSettings.settings.lockGuides]);
  var addVerticalGuide = (0,react.useCallback)(function (x) {
    if (!globalSettings.settings.lockGuides) {
      setGuides(function (prev) {
        return PDFCanvasEditor_objectSpread(PDFCanvasEditor_objectSpread({}, prev), {}, {
          vertical: [].concat(PDFCanvasEditor_toConsumableArray(prev.vertical), [x]).sort(function (a, b) {
            return a - b;
          })
        });
      });
    }
  }, [globalSettings.settings.lockGuides]);
  var removeGuide = (0,react.useCallback)(function (type, position) {
    if (!globalSettings.settings.lockGuides) {
      setGuides(function (prev) {
        return PDFCanvasEditor_objectSpread(PDFCanvasEditor_objectSpread({}, prev), {}, PDFCanvasEditor_defineProperty({}, type, prev[type].filter(function (pos) {
          return pos !== position;
        })));
      });
    }
  }, [globalSettings.settings.lockGuides]);

  // Données de commande WooCommerce (passées via options ou données de test)
  var orderData = options.orderData || {
    invoice_number: 'INV-001',
    invoice_date: '15/10/2025',
    order_number: '#12345',
    order_date: '15/10/2025',
    customer_name: 'John Doe',
    customer_email: 'john.doe@example.com',
    billing_address: '123 Rue de Test\n75001 Paris\nFrance',
    shipping_address: '456 Rue de Livraison\n75002 Paris\nFrance',
    payment_method: 'Carte bancaire',
    order_status: 'Traitée',
    subtotal: '45,00 €',
    discount: '-5,00 €',
    shipping: '5,00 €',
    tax: '9,00 €',
    total: '54,00 €',
    refund: '0,00 €',
    fees: '1,50 €',
    quote_number: 'QUO-001',
    quote_date: '15/10/2025',
    quote_validity: '30 jours',
    quote_notes: 'Conditions spéciales : paiement à 30 jours.',
    products: [{
      name: 'Produit Test 1',
      quantity: 1,
      price: '25,00 €',
      total: '25,00 €'
    }, {
      name: 'Produit Test 2',
      quantity: 2,
      price: '10,00 €',
      total: '20,00 €'
    }]
  };
  var canvasState = useCanvasState({
    initialElements: options.initialElements || [],
    templateId: options.templateId || null,
    canvasWidth: options.width || 595,
    canvasHeight: options.height || 842,
    globalSettings: globalSettings.settings
  });

  // Hook pour l'historique Undo/Redo - REMOVED: utilise maintenant canvasState.history
  // const history = useHistory({ maxHistorySize: globalSettings.settings.undoLevels || 50 });

  // Fonction wrapper pour les mises à jour avec historique
  var updateElementWithHistory = (0,react.useCallback)(function (elementId, updates) {
    var description = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'Modifier élément';
    // Sauvegarder l'état actuel avant modification
    var currentElements = canvasState.getAllElements();
    canvasState.history.addToHistory(currentElements, description);

    // Appliquer la mise à jour
    canvasState.updateElement(elementId, updates);
  }, [canvasState]);

  // Fonctions Undo/Redo
  var handleUndo = (0,react.useCallback)(function () {
    var previousState = canvasState.history.undo();
    if (previousState) {
      canvasState.setElements(previousState);
    }
  }, [canvasState]);
  var handleRedo = (0,react.useCallback)(function () {
    var nextState = canvasState.history.redo();
    if (nextState) {
      canvasState.setElements(nextState);
    }
  }, [canvasState]);

  // Handler pour créer un nouveau template
  var handleCreateTemplate = (0,react.useCallback)(function (templateData) {
    // TODO: Implémenter la logique de création du template
    // Pour l'instant, on ferme juste la modale et on affiche les données
    console.log('Création du template:', templateData);
    alert("Template \"".concat(templateData.name, "\" cr\xE9\xE9 avec succ\xE8s!\n\nMod\xE8le: ").concat(templateData.defaultModel, "\nFormat: ").concat(templateData.paperFormat, "\nOrientation: ").concat(templateData.orientation, "\nCat\xE9gorie: ").concat(templateData.category));
  }, []);

  // Handlers pour les paramètres de grille
  var handleShowGridChange = (0,react.useCallback)(function (showGrid) {
    globalSettings.updateSettings({
      showGrid: showGrid
    });
  }, [globalSettings]);
  var handleSnapToGridChange = (0,react.useCallback)(function (snapToGrid) {
    globalSettings.updateSettings({
      snapToGrid: snapToGrid
    });
  }, [globalSettings]);
  var editorRef = (0,react.useRef)(null);
  var canvasRef = (0,react.useRef)(null);
  var canvasContainerRef = (0,react.useRef)(null);

  // Hook pour le drag and drop
  var dragAndDrop = useDragAndDrop({
    onElementMove: function onElementMove(elementId, position) {
      updateElementWithHistory(elementId, position, 'Déplacer élément');
    },
    onElementDrop: function onElementDrop(elementId, position) {
      updateElementWithHistory(elementId, position, 'Déposer élément');
    },
    snapToGrid: globalSettings.settings.snapToGrid,
    gridSize: globalSettings.settings.gridSize,
    zoom: canvasState.zoom.zoom,
    canvasWidth: canvasState.canvasWidth,
    canvasHeight: canvasState.canvasHeight,
    guides: guides,
    snapToGuides: globalSettings.settings.snapToElements
  });

  // Gestion des raccourcis clavier
  useKeyboardShortcuts({
    onDelete: canvasState.deleteSelectedElements,
    onCopy: canvasState.copySelectedElements,
    onPaste: canvasState.pasteElements,
    onUndo: handleUndo,
    onRedo: handleRedo,
    onSave: canvasState.saveTemplate,
    onZoomIn: canvasState.zoom.zoomIn,
    onZoomOut: canvasState.zoom.zoomOut
  });

  // Gestionnaire pour ajouter un élément depuis la bibliothèque
  var handleAddElement = (0,react.useCallback)(function (elementType) {
    var properties = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    canvasState.addElement(elementType, properties);
    setTool('select');
  }, [canvasState]);

  // Gestionnaire pour la sélection d'élément
  var handleElementSelect = (0,react.useCallback)(function (elementId, event) {
    var addToSelection = (event === null || event === void 0 ? void 0 : event.ctrlKey) || (event === null || event === void 0 ? void 0 : event.metaKey); // Ctrl ou Cmd pour multi-sélection
    canvasState.selection.selectElement(elementId, addToSelection);
  }, [canvasState.selection]);

  // Gestionnaire pour l'impression
  var handlePrint = (0,react.useCallback)(/*#__PURE__*/PDFCanvasEditor_asyncToGenerator(/*#__PURE__*/PDFCanvasEditor_regenerator().m(function _callee() {
    var _window$pdfBuilderAja, _window$pdfBuilderAja2, elements, jsonString, formData, response, data, _data$data, _t, _t2;
    return PDFCanvasEditor_regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          _context.p = 0;
          // Récupérer tous les éléments du canvas
          elements = canvasState.getAllElements();
          if (!(elements.length === 0)) {
            _context.n = 1;
            break;
          }
          alert('Aucun élément à imprimer. Ajoutez des éléments au canvas d\'abord.');
          return _context.a(2);
        case 1:
          _context.p = 1;
          jsonString = JSON.stringify(elements);
          _context.n = 3;
          break;
        case 2:
          _context.p = 2;
          _t = _context.v;
          console.error('❌ Erreur lors de JSON.stringify:', _t);
          console.error('Éléments problématiques:', elements);
          alert('Erreur de sérialisation des éléments. Vérifiez la console pour plus de détails.');
          return _context.a(2);
        case 3:
          // Préparer les données pour l'AJAX
          formData = new FormData();
          formData.append('action', 'pdf_builder_generate_pdf');
          formData.append('nonce', (_window$pdfBuilderAja = window.pdfBuilderAjax) === null || _window$pdfBuilderAja === void 0 ? void 0 : _window$pdfBuilderAja.nonce);
          formData.append('elements', jsonString);

          // Faire l'appel AJAX
          _context.n = 4;
          return fetch((_window$pdfBuilderAja2 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja2 === void 0 ? void 0 : _window$pdfBuilderAja2.ajaxurl, {
            method: 'POST',
            body: formData
          });
        case 4:
          response = _context.v;
          if (response.ok) {
            _context.n = 5;
            break;
          }
          throw new Error("Erreur HTTP: ".concat(response.status));
        case 5:
          _context.n = 6;
          return response.json();
        case 6:
          data = _context.v;
          if (!data.success) {
            _context.n = 7;
            break;
          }
          // Ouvrir le PDF dans une modale
          // console.log('Ouverture du PDF dans une modale...');
          setPdfModalUrl(pdfDataUrl);
          setShowPDFModal(true);
          _context.n = 8;
          break;
        case 7:
          console.error('Erreur serveur:', data.data);
          throw new Error(((_data$data = data.data) === null || _data$data === void 0 ? void 0 : _data$data.message) || 'Erreur lors de la génération du PDF');
        case 8:
          _context.n = 10;
          break;
        case 9:
          _context.p = 9;
          _t2 = _context.v;
          console.error('Erreur lors de l\'impression:', _t2);
          alert('Erreur lors de la génération du PDF: ' + _t2.message);
        case 10:
          return _context.a(2);
      }
    }, _callee, null, [[1, 2], [0, 9]]);
  })), [canvasState]);

  // Gestionnaire pour la désélection et création d'éléments
  var handleCanvasClick = (0,react.useCallback)(function (e) {
    // Vérifier si c'est un Ctrl+clic pour créer un guide
    if (e.ctrlKey && globalSettings.settings.showGuides && !globalSettings.settings.lockGuides) {
      var canvasRect = e.currentTarget.getBoundingClientRect();
      var clickX = e.clientX - canvasRect.left;
      var clickY = e.clientY - canvasRect.top;

      // Ajuster pour le zoom et le pan
      var adjustedX = (clickX - panOffset.x) / canvasState.zoom.zoom;
      var adjustedY = (clickY - panOffset.y) / canvasState.zoom.zoom;

      // Créer un guide horizontal ou vertical selon la position relative au centre
      var centerX = canvasState.canvasWidth / 2;
      var centerY = canvasState.canvasHeight / 2;
      if (Math.abs(adjustedX - centerX) < Math.abs(adjustedY - centerY)) {
        // Plus proche verticalement, créer guide horizontal
        addHorizontalGuide(Math.round(adjustedY));
      } else {
        // Plus proche horizontalement, créer guide vertical
        addVerticalGuide(Math.round(adjustedX));
      }
      return;
    }

    // Vérifier si le clic vient de la zone vide du canvas (pas d'un élément)
    var clickedElement = e.target.closest('[data-element-id]');
    if (clickedElement) {
      // Si on clique sur un élément, ne rien faire ici (laissé à CanvasElement)
      return;
    }

    // Si un outil d'ajout est sélectionné, créer l'élément
    if (tool.startsWith('add-')) {
      var _canvasRect = e.currentTarget.getBoundingClientRect();
      var _clickX = e.clientX - _canvasRect.left;
      var _clickY = e.clientY - _canvasRect.top;

      // Ajuster pour le zoom
      var _adjustedX = _clickX / canvasState.zoom.zoom;
      var _adjustedY = _clickY / canvasState.zoom.zoom;
      var elementType = 'text';
      var defaultProps = {};

      // Déterminer le type d'élément selon l'outil
      switch (tool) {
        case 'add-text':
          elementType = 'text';
          break;
        case 'add-text-title':
          elementType = 'text';
          defaultProps = {
            fontSize: 24,
            fontWeight: 'bold'
          };
          break;
        case 'add-text-subtitle':
          elementType = 'text';
          defaultProps = {
            fontSize: 18,
            fontWeight: 'bold'
          };
          break;
        case 'add-rectangle':
          elementType = 'rectangle';
          break;
        case 'add-circle':
          elementType = 'shape-circle';
          break;
        case 'add-line':
          elementType = 'line';
          break;
        case 'add-arrow':
          elementType = 'shape-arrow';
          break;
        case 'add-triangle':
          elementType = 'shape-triangle';
          break;
        case 'add-star':
          elementType = 'shape-star';
          break;
        case 'add-divider':
          elementType = 'divider';
          break;
        case 'add-image':
          elementType = 'image';
          break;
        default:
          // Pour les autres outils de la bibliothèque
          if (tool.startsWith('add-')) {
            elementType = tool.replace('add-', '');
          }
          break;
      }
      canvasState.addElement(elementType, PDFCanvasEditor_objectSpread({
        x: Math.max(0, _adjustedX - 50),
        y: Math.max(0, _adjustedY - 25)
      }, defaultProps));

      // Remettre l'outil de sélection après ajout
      setTool('select');
      return;
    }

    // Sinon, désélectionner
    canvasState.selection.clearSelection();
  }, [canvasState, tool]);

  // Gestionnaire pour les changements de propriétés
  var handlePropertyChange = (0,react.useCallback)(function (elementId, property, value) {
    // Récupérer l'élément actuel pour connaître les valeurs existantes
    var currentElement = canvasState.getElementById(elementId);
    if (!currentElement) return;

    // Gérer les propriétés imbriquées (ex: "columns.image" -> { columns: { image: value } })
    var updates = {};
    if (property.includes('.')) {
      // Fonction récursive pour mettre à jour les propriétés imbriquées
      // en préservant toutes les valeurs existantes
      var updateNestedProperty = function updateNestedProperty(existingObj, path, val) {
        var keys = path.split('.');
        var lastKey = keys.pop();

        // Commencer avec une copie complète de l'objet existant
        var result = PDFCanvasEditor_objectSpread({}, existingObj);
        var current = result;

        // Naviguer jusqu'à l'avant-dernier niveau en préservant les objets existants
        for (var i = 0; i < keys.length - 1; i++) {
          var key = keys[i];
          if (!current[key] || PDFCanvasEditor_typeof(current[key]) !== 'object') {
            current[key] = {};
          } else {
            current[key] = PDFCanvasEditor_objectSpread({}, current[key]);
          }
          current = current[key];
        }

        // Pour le dernier niveau (avant la propriété finale)
        var parentKey = keys[keys.length - 1];
        if (parentKey) {
          if (!current[parentKey] || PDFCanvasEditor_typeof(current[parentKey]) !== 'object') {
            current[parentKey] = {};
          } else {
            current[parentKey] = PDFCanvasEditor_objectSpread({}, current[parentKey]);
          }
          current[parentKey][lastKey] = val;
        } else {
          // Propriété directement sur l'objet racine
          current[lastKey] = val;
        }
        return result;
      };

      // Créer l'update en préservant toutes les propriétés existantes
      var fullUpdate = updateNestedProperty(currentElement, property, value);
      Object.assign(updates, fullUpdate);
    } else {
      updates[property] = value;
    }
    updateElementWithHistory(elementId, updates, "Modifier ".concat(property));
  }, [canvasState, updateElementWithHistory]);

  // Gestionnaire pour les mises à jour par lot
  var handleBatchUpdate = (0,react.useCallback)(function (updates) {
    updates.forEach(function (_ref3) {
      var elementId = _ref3.elementId,
        property = _ref3.property,
        value = _ref3.value;
      canvasState.updateElement(elementId, PDFCanvasEditor_defineProperty({}, property, value));
    });
  }, [canvasState]);

  // Gestionnaire du menu contextuel
  var handleContextMenu = (0,react.useCallback)(function (e) {
    var elementId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
    e.preventDefault();
    var menuItems = [];
    if (elementId) {
      // Menu contextuel pour un élément spécifique
      var element = canvasState.getElementById(elementId);
      if (element) {
        menuItems.push({
          label: 'Copier',
          action: function action() {
            return canvasState.copySelectedElements();
          }
        }, {
          label: 'Dupliquer',
          action: function action() {
            return canvasState.duplicateElement(elementId);
          }
        }, {
          type: 'separator'
        }, {
          label: 'Supprimer',
          action: function action() {
            return canvasState.deleteElement(elementId);
          }
        });
      }
    } else {
      // Menu contextuel pour le canvas vide
      var hasSelection = canvasState.selection.selectedElements.length > 0;
      if (hasSelection) {
        menuItems.push({
          label: 'Copier',
          action: function action() {
            return canvasState.copySelectedElements();
          }
        }, {
          label: 'Dupliquer',
          action: function action() {
            return canvasState.duplicateSelectedElements();
          }
        }, {
          type: 'separator'
        }, {
          label: 'Supprimer',
          action: function action() {
            return canvasState.deleteSelectedElements();
          }
        });
      }
      menuItems.push({
        type: 'separator'
      }, {
        label: 'Coller',
        action: function action() {
          return canvasState.pasteElements();
        }
      }, {
        type: 'separator'
      }, {
        label: 'Tout sélectionner',
        action: function action() {
          return canvasState.selectAll();
        }
      }, {
        label: 'Désélectionner',
        action: function action() {
          return canvasState.selection.clearSelection();
        }
      });
    }
    canvasState.showContextMenu(e.clientX, e.clientY, menuItems);
  }, [canvasState]);

  // Gestionnaire pour les actions du menu contextuel
  var handleContextMenuAction = (0,react.useCallback)(function (action) {
    if (typeof action === 'function') {
      action();
    }
  }, []);

  // Fonction pour déterminer le curseur selon l'outil sélectionné
  var getCursorStyle = (0,react.useCallback)(function () {
    if (isPanning) return 'grabbing';
    switch (tool) {
      case 'select':
        return 'default';
      case 'add-text':
      case 'add-text-title':
      case 'add-text-subtitle':
        return 'text';
      case 'add-rectangle':
      case 'add-circle':
      case 'add-line':
      case 'add-arrow':
      case 'add-triangle':
      case 'add-star':
      case 'add-divider':
      case 'add-image':
        return 'crosshair';
      default:
        return 'default';
    }
  }, [tool, isPanning]);

  // Gestionnaire pour le drag over
  var handleDragOver = (0,react.useCallback)(function (e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  // Gestionnaire pour le drop
  var handleDrop = (0,react.useCallback)(function (e) {
    e.preventDefault();
    try {
      var jsonData = e.dataTransfer.getData('application/json');

      // Vérifier si les données existent et ne sont pas vides
      if (!jsonData || jsonData.trim() === '') {
        // C'est probablement un drop normal (image, fichier, etc.) - ignorer silencieusement
        return;
      }
      var data = JSON.parse(jsonData);
      if (data.type === 'new-element') {
        var canvasRect = e.currentTarget.getBoundingClientRect();
        var dropX = e.clientX - canvasRect.left;
        var dropY = e.clientY - canvasRect.top;

        // Ajuster pour le zoom
        var adjustedX = dropX / canvasState.zoom.zoom;
        var adjustedY = dropY / canvasState.zoom.zoom;
        canvasState.addElement(data.elementType, PDFCanvasEditor_objectSpread({
          x: Math.max(0, adjustedX - 50),
          // Centrer l'élément sur le point de drop
          y: Math.max(0, adjustedY - 25)
        }, data.defaultProps));
      }
    } catch (error) {
      // Ne logger que les vraies erreurs (pas les drops normaux)
      if (error instanceof SyntaxError && e.dataTransfer.getData('application/json')) {
        console.error('Erreur lors du parsing des données de drop:', error);
      }
      // Pour les autres types de drop (fichiers, images, etc.), ignorer silencieusement
    }
  }, [canvasState]);

  // Gestionnaire pour le zoom avec la molette
  var handleWheel = (0,react.useCallback)(function (e) {
    if (!globalSettings.settings.zoomWithWheel) return;
    e.preventDefault();

    // Calculer le facteur de zoom basé sur les paramètres globaux
    var zoomFactor = 1 + globalSettings.settings.zoomStep / 100;

    // Déterminer si on zoome ou dézoome
    var delta = e.deltaY > 0 ? -1 : 1;

    // Calculer les coordonnées de la souris relatives au conteneur
    var container = canvasContainerRef.current;
    if (!container) return;
    var rect = container.getBoundingClientRect();
    var mouseX = e.clientX - rect.left;
    var mouseY = e.clientY - rect.top;

    // Appliquer le zoom vers le point de la souris
    var finalZoomFactor = delta > 0 ? zoomFactor : 1 / zoomFactor;
    canvasState.zoom.zoomToPoint(mouseX, mouseY, finalZoomFactor);
  }, [globalSettings.settings.zoomWithWheel, globalSettings.settings.zoomStep, canvasState.zoom]);

  // Attacher le gestionnaire de roue de manière non-passive pour permettre preventDefault
  (0,react.useEffect)(function () {
    var container = canvasContainerRef.current;
    if (!container || !globalSettings.settings.zoomWithWheel) return;
    var handleWheelEvent = function handleWheelEvent(e) {
      handleWheel(e);
    };
    container.addEventListener('wheel', handleWheelEvent, {
      passive: false
    });
    return function () {
      container.removeEventListener('wheel', handleWheelEvent);
    };
  }, [handleWheel, globalSettings.settings.zoomWithWheel]);

  // Gestionnaire pour le pan avec la souris (clic milieu ou espace + drag)
  var handleMouseDown = (0,react.useCallback)(function (e) {
    if (!globalSettings.settings.panWithMouse) return;

    // Pan avec le bouton du milieu ou espace + clic gauche
    if (e.button === 1 || e.button === 0 && e.altKey) {
      e.preventDefault();
      setIsPanning(true);
      setLastPanPoint({
        x: e.clientX,
        y: e.clientY
      });
    }
  }, [globalSettings.settings.panWithMouse]);
  var handleMouseMove = (0,react.useCallback)(function (e) {
    if (!isPanning) return;
    var deltaX = e.clientX - lastPanPoint.x;
    var deltaY = e.clientY - lastPanPoint.y;
    setPanOffset(function (prev) {
      return {
        x: prev.x + deltaX,
        y: prev.y + deltaY
      };
    });
    setLastPanPoint({
      x: e.clientX,
      y: e.clientY
    });
  }, [isPanning, lastPanPoint]);
  var handleMouseUp = (0,react.useCallback)(function () {
    setIsPanning(false);
  }, []);

  // Gestionnaire pour double-clic
  var handleDoubleClick = (0,react.useCallback)(function (e) {
    if (!globalSettings.settings.zoomToSelection) return;

    // Vérifier qu'il n'y a pas d'élément cliqué (double-clic sur le fond)
    var clickedElement = e.target.closest('[data-element-id]');
    if (clickedElement) return;

    // Si des éléments sont sélectionnés, zoomer dessus
    if (canvasState.selection.selectedElements.length > 0) {
      canvasState.zoomToSelection();
    }
  }, [globalSettings.settings.zoomToSelection, canvasState]);
  return /*#__PURE__*/react.createElement("div", {
    className: "pdf-canvas-editor",
    ref: editorRef
  }, /*#__PURE__*/react.createElement("header", {
    className: "editor-header"
  }, /*#__PURE__*/react.createElement("h2", null, "\xC9diteur PDF - ", options.isNew ? 'Nouveau Template' : options.templateName), /*#__PURE__*/react.createElement("nav", {
    className: "editor-actions"
  }, /*#__PURE__*/react.createElement("button", {
    className: "btn btn-outline",
    onClick: function onClick() {
      return setShowNewTemplateModal(true);
    },
    title: "Cr\xE9er un nouveau template"
  }, "\u2795 Nouveau template"), /*#__PURE__*/react.createElement("button", {
    className: "btn btn-secondary",
    onClick: function onClick() {
      return setShowPreviewModal(true);
    }
  }, "\uD83D\uDC41\uFE0F Aper\xE7u"), /*#__PURE__*/react.createElement("button", {
    className: "btn btn-primary",
    onClick: function onClick() {
      return canvasState.saveTemplate();
    },
    disabled: canvasState.isSaving
  }, canvasState.isSaving ? '⏳ Sauvegarde...' : options.isNew ? '💾 Sauvegarder' : '✏️ Modifier'))), /*#__PURE__*/react.createElement(Toolbar, {
    selectedTool: tool,
    onToolSelect: setTool,
    zoom: canvasState.zoom.zoom,
    onZoomChange: canvasState.zoom.setZoomLevel,
    showGrid: globalSettings.settings.showGrid,
    onShowGridChange: handleShowGridChange,
    snapToGrid: globalSettings.settings.snapToGrid,
    onSnapToGridChange: handleSnapToGridChange,
    onUndo: handleUndo,
    onRedo: handleRedo,
    canUndo: canvasState.history.canUndo(),
    canRedo: canvasState.history.canRedo(),
    onPreview: function onPreview() {
      return setShowPreviewModal(true);
    }
  }), /*#__PURE__*/react.createElement("main", {
    className: "editor-workspace"
  }, !showPreviewModal && /*#__PURE__*/react.createElement("aside", {
    className: "editor-sidebar left-sidebar"
  }, /*#__PURE__*/react.createElement(components_ElementLibrary, {
    onAddElement: handleAddElement,
    selectedTool: tool,
    onToolSelect: setTool
  })), /*#__PURE__*/react.createElement("section", {
    className: "canvas-section",
    ref: canvasContainerRef,
    onContextMenu: handleContextMenu,
    onDragOver: handleDragOver,
    onDrop: handleDrop,
    onMouseDown: handleMouseDown,
    onMouseMove: handleMouseMove,
    onMouseUp: handleMouseUp,
    onMouseLeave: handleMouseUp,
    onDoubleClick: handleDoubleClick,
    style: {
      cursor: getCursorStyle(),
      backgroundColor: globalSettings.settings.containerShowTransparency ? 'transparent' : globalSettings.settings.containerBackgroundColor || '#f8f9fa',
      backgroundImage: globalSettings.settings.containerShowTransparency ? "linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%)" : 'none',
      backgroundSize: globalSettings.settings.containerShowTransparency ? '20px 20px' : 'auto',
      backgroundPosition: globalSettings.settings.containerShowTransparency ? '0 0, 0 10px, 10px -10px, -10px 0px' : '0 0'
    }
  }, /*#__PURE__*/react.createElement("div", {
    className: "canvas-zoom-wrapper",
    style: {
      transform: "translate(".concat(panOffset.x, "px, ").concat(panOffset.y, "px) scale(").concat(canvasState.zoom.zoom, ")"),
      transformOrigin: 'center',
      cursor: isPanning ? 'grabbing' : 'default',
      transition: globalSettings.settings.smoothZoom ? 'transform 0.2s ease-out' : 'none',
      willChange: globalSettings.settings.enableHardwareAcceleration ? 'transform' : 'auto'
    }
  }, /*#__PURE__*/react.createElement("div", {
    className: "canvas",
    ref: canvasRef,
    onClick: handleCanvasClick,
    style: {
      width: canvasState.canvasWidth,
      height: 'auto',
      // Laisser le CSS contrôler la hauteur pour s'adapter au conteneur 130vh
      minHeight: canvasState.canvasHeight,
      // Hauteur minimale pour éviter la compression excessive
      position: 'relative',
      backgroundColor: globalSettings.settings.canvasShowTransparency ? 'transparent' : globalSettings.settings.canvasBackgroundColor || '#ffffff',
      backgroundImage: globalSettings.settings.canvasShowTransparency ? "linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%)" : 'none',
      backgroundSize: globalSettings.settings.canvasShowTransparency ? '20px 20px' : 'auto',
      backgroundPosition: globalSettings.settings.canvasShowTransparency ? '0 0, 0 10px, 10px -10px, -10px 0px' : '0 0'
    }
  }, globalSettings.settings.showGrid && /*#__PURE__*/react.createElement("div", {
    className: "canvas-grid",
    style: {
      position: 'absolute',
      top: 0,
      left: 0,
      width: '100%',
      height: '100%',
      backgroundImage: "\n                      linear-gradient(to right, ".concat(globalSettings.settings.gridColor).concat(Math.round(globalSettings.settings.gridOpacity * 2.55).toString(16).padStart(2, '0'), " 1px, transparent 1px),\n                      linear-gradient(to bottom, ").concat(globalSettings.settings.gridColor).concat(Math.round(globalSettings.settings.gridOpacity * 2.55).toString(16).padStart(2, '0'), " 1px, transparent 1px)\n                    "),
      backgroundSize: "".concat(globalSettings.settings.gridSize, "px ").concat(globalSettings.settings.gridSize, "px"),
      pointerEvents: 'none',
      zIndex: 1
    }
  }), globalSettings.settings.showGuides && /*#__PURE__*/react.createElement("div", {
    className: "canvas-guides"
  }, guides.horizontal.map(function (y, index) {
    return /*#__PURE__*/react.createElement("div", {
      key: "h-guide-".concat(index),
      className: "canvas-guide horizontal-guide",
      onClick: function onClick(e) {
        e.stopPropagation();
        if (!globalSettings.settings.lockGuides) {
          removeGuide('horizontal', y);
        }
      },
      style: {
        position: 'absolute',
        top: "".concat(y, "px"),
        left: 0,
        width: '100%',
        height: '2px',
        backgroundColor: '#007cba',
        cursor: globalSettings.settings.lockGuides ? 'default' : 'pointer',
        zIndex: 2,
        opacity: 0.7
      },
      title: "Guide horizontal \xE0 ".concat(y, "px - ").concat(globalSettings.settings.lockGuides ? 'Verrouillé' : 'Cliquer pour supprimer')
    });
  }), guides.vertical.map(function (x, index) {
    return /*#__PURE__*/react.createElement("div", {
      key: "v-guide-".concat(index),
      className: "canvas-guide vertical-guide",
      onClick: function onClick(e) {
        e.stopPropagation();
        if (!globalSettings.settings.lockGuides) {
          removeGuide('vertical', x);
        }
      },
      style: {
        position: 'absolute',
        top: 0,
        left: "".concat(x, "px"),
        height: '100%',
        width: '2px',
        backgroundColor: '#007cba',
        cursor: globalSettings.settings.lockGuides ? 'default' : 'pointer',
        zIndex: 2,
        opacity: 0.7
      },
      title: "Guide vertical \xE0 ".concat(x, "px - ").concat(globalSettings.settings.lockGuides ? 'Verrouillé' : 'Cliquer pour supprimer')
    });
  })), canvasState.elements.filter(function (el) {
    return !el.type.startsWith('woocommerce-');
  }).map(function (element) {
    return /*#__PURE__*/react.createElement(CanvasElement, {
      key: element.id,
      element: element,
      isSelected: canvasState.selection.selectedElements.includes(element.id),
      zoom: 1,
      snapToGrid: globalSettings.settings.snapToGrid,
      gridSize: globalSettings.settings.gridSize,
      canvasWidth: canvasState.canvasWidth,
      canvasHeight: canvasState.canvasHeight,
      onSelect: function onSelect() {
        return handleElementSelect(element.id);
      },
      onUpdate: function onUpdate(updates) {
        return canvasState.updateElement(element.id, updates);
      },
      onRemove: function onRemove() {
        return canvasState.deleteElement(element.id);
      },
      onContextMenu: function onContextMenu(e) {
        return handleContextMenu(e, element.id);
      },
      dragAndDrop: dragAndDrop,
      enableRotation: globalSettings.settings.enableRotation,
      rotationStep: globalSettings.settings.rotationStep,
      rotationSnap: globalSettings.settings.rotationSnap,
      guides: guides,
      snapToGuides: globalSettings.settings.snapToElements
    });
  }), canvasState.elements.filter(function (el) {
    return el.type.startsWith('woocommerce-');
  }).map(function (element) {
    return /*#__PURE__*/react.createElement(WooCommerceElements, {
      key: element.id,
      element: element,
      isSelected: canvasState.selection.selectedElements.includes(element.id),
      onSelect: handleElementSelect,
      onUpdate: canvasState.updateElement,
      dragAndDrop: dragAndDrop,
      zoom: 1,
      canvasWidth: canvasState.canvasWidth,
      canvasHeight: canvasState.canvasHeight,
      orderData: orderData,
      onContextMenu: function onContextMenu(e) {
        return handleContextMenu(e, element.id);
      },
      snapToGrid: globalSettings.settings.snapToGrid,
      gridSize: globalSettings.settings.gridSize,
      guides: guides,
      snapToGuides: globalSettings.settings.snapToElements
    });
  })))), !showPreviewModal && /*#__PURE__*/react.createElement("aside", {
    className: "editor-sidebar right-sidebar ".concat(isPropertiesCollapsed ? 'collapsed' : '')
  }, !isPropertiesCollapsed && /*#__PURE__*/react.createElement(components_PropertiesPanel, {
    selectedElements: canvasState.selection.selectedElements,
    elements: canvasState.elements,
    onPropertyChange: handlePropertyChange,
    onBatchUpdate: handleBatchUpdate
  }))), !showPreviewModal && /*#__PURE__*/react.createElement("button", {
    className: "sidebar-toggle-fixed",
    onClick: function onClick() {
      return setIsPropertiesCollapsed(!isPropertiesCollapsed);
    },
    title: isPropertiesCollapsed ? 'Agrandir le panneau' : 'Réduire le panneau',
    style: {
      position: 'fixed',
      top: '50%',
      right: isPropertiesCollapsed ? '80px' : '420px',
      transform: 'translateY(-50%)',
      zIndex: 999999
    }
  }, isPropertiesCollapsed ? '◀' : '▶'), canvasState.contextMenu.contextMenu && /*#__PURE__*/react.createElement(components_ContextMenu, {
    menu: canvasState.contextMenu.contextMenu,
    onAction: handleContextMenuAction,
    isAnimating: canvasState.contextMenu.isAnimating || false,
    onClose: canvasState.contextMenu.hideContextMenu
  }), /*#__PURE__*/react.createElement("footer", {
    className: "editor-status"
  }, /*#__PURE__*/react.createElement("span", null, "\xC9l\xE9ments: ", canvasState.elements.length), /*#__PURE__*/react.createElement("span", null, "|"), globalSettings.settings.showZoomIndicator && /*#__PURE__*/react.createElement(react.Fragment, null, /*#__PURE__*/react.createElement("span", null, "Zoom: ", Math.round(canvasState.zoom.zoom * 100), "%"), /*#__PURE__*/react.createElement("span", null, "|")), /*#__PURE__*/react.createElement("span", null, "Outil: ", tool), canvasState.selection.selectedElements.length > 0 && /*#__PURE__*/react.createElement(react.Fragment, null, /*#__PURE__*/react.createElement("span", null, "|"), /*#__PURE__*/react.createElement("span", null, "\xC9l\xE9ments s\xE9lectionn\xE9s: ", canvasState.selection.selectedElements.length))), /*#__PURE__*/react.createElement(components_PreviewModal, {
    isOpen: showPreviewModal,
    onClose: function onClose() {
      setShowPreviewModal(false);
    },
    elements: canvasState.elements,
    canvasWidth: canvasState.canvasWidth,
    canvasHeight: canvasState.canvasHeight,
    ajaxurl: (_window$pdfBuilderAja3 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja3 === void 0 ? void 0 : _window$pdfBuilderAja3.ajaxurl,
    pdfBuilderNonce: (_window$pdfBuilderAja4 = window.pdfBuilderAjax) === null || _window$pdfBuilderAja4 === void 0 ? void 0 : _window$pdfBuilderAja4.nonce,
    useServerPreview: false,
    onOpenPDFModal: function onOpenPDFModal(pdfUrl) {
      setPdfModalUrl(pdfUrl);
      setShowPDFModal(true);
      setShowPreviewModal(false);
    }
  }), /*#__PURE__*/react.createElement(components_ModalPDFViewer, {
    isOpen: showPDFModal,
    onClose: function onClose() {
      setShowPDFModal(false);
      if (pdfModalUrl && pdfModalUrl.startsWith('blob:')) {
        setTimeout(function () {
          URL.revokeObjectURL(pdfModalUrl);
        }, 100);
      }
      setPdfModalUrl(null);
    },
    pdfUrl: pdfModalUrl,
    title: "PDF G\xE9n\xE9r\xE9"
  }), /*#__PURE__*/react.createElement(components_NewTemplateModal, {
    isOpen: showNewTemplateModal,
    onClose: function onClose() {
      return setShowNewTemplateModal(false);
    },
    onCreateTemplate: handleCreateTemplate
  }), /*#__PURE__*/react.createElement(FPSCounter, {
    showFps: globalSettings.settings.showFps
  }));
};

// Optimisation : éviter les re-renders inutiles
/* harmony default export */ const components_PDFCanvasEditor = (/*#__PURE__*/(/* unused pure expression or super */ null && (React.memo(PDFCanvasEditor))));
;// ./src/hooks/index.js










;// ./src/index.js
function src_ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function src_objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? src_ownKeys(Object(t), !0).forEach(function (r) { src_defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : src_ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function src_defineProperty(e, r, t) { return (r = src_toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function src_classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function src_defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, src_toPropertyKey(o.key), o); } }
function src_createClass(e, r, t) { return r && src_defineProperties(e.prototype, r), t && src_defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function src_toPropertyKey(t) { var i = src_toPrimitive(t, "string"); return "symbol" == src_typeof(i) ? i : i + ""; }
function src_toPrimitive(t, r) { if ("object" != src_typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != src_typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function src_typeof(o) { "@babel/helpers - typeof"; return src_typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, src_typeof(o); }
// Tous les imports doivent être au niveau supérieur du module





// Forcer l'inclusion de tous les hooks personnalisés

console.log('PDF Builder Pro: Script execution started - with proper imports');

// Système de protection et monitoring
var PDFBuilderSecurity = {
  healthChecks: [],
  errors: [],
  initialized: false,
  // Health check pour vérifier que toutes les dépendances sont disponibles
  performHealthCheck: function performHealthCheck() {
    console.log('PDF Builder Pro: Performing health check...');
    var checks = {
      react: src_typeof(react) === 'object' && react.version,
      reactDom: src_typeof(react_dom) === 'object',
      pdfCanvasEditor: typeof PDFCanvasEditor === 'function',
      hooks: src_typeof(hooks_namespaceObject) === 'object',
      window: typeof window !== 'undefined',
      document: typeof document !== 'undefined'
    };
    this.healthChecks = checks;
    var allHealthy = Object.values(checks).every(Boolean);
    if (allHealthy) {
      console.log('PDF Builder Pro: All health checks passed ✅');
      this.initialized = true;
    } else {
      console.error('PDF Builder Pro: Health check failed ❌', checks);
      this.initialized = false;
    }
    return allHealthy;
  },
  // Log sécurisé des erreurs
  logError: function logError(error) {
    var _navigator, _window;
    var context = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    var errorInfo = {
      message: error.message,
      stack: error.stack,
      context: context,
      timestamp: new Date().toISOString(),
      userAgent: (_navigator = navigator) === null || _navigator === void 0 ? void 0 : _navigator.userAgent,
      url: (_window = window) === null || _window === void 0 || (_window = _window.location) === null || _window === void 0 ? void 0 : _window.href
    };
    this.errors.push(errorInfo);
    console.error('PDF Builder Pro Security Error:', errorInfo);
  },
  // Protection contre les appels multiples
  preventMultipleInit: function preventMultipleInit() {
    if (window._pdfBuilderInitialized) {
      console.warn('PDF Builder Pro: Multiple initialization attempt prevented');
      return false;
    }
    window._pdfBuilderInitialized = true;
    return true;
  }
};

// Test des imports de base avec protection
try {
  console.log('Testing React availability...');
  console.log('React version:', react.version);
  console.log('ReactDOM available:', src_typeof(react_dom));

  // Exposer React globalement pour compatibilité
  if (typeof window !== 'undefined') {
    window.React = react;
    window.ReactDOM = react_dom;
    console.log('PDF Builder Pro: React exposed globally');
  }
} catch (error) {
  PDFBuilderSecurity.logError(error, 'React initialization');
  console.error('React test failed:', error);
}

// Classe principale pour l'éditeur PDF
var PDFBuilderPro = /*#__PURE__*/function () {
  function PDFBuilderPro() {
    src_classCallCheck(this, PDFBuilderPro);
    this.version = '2.0.0';
    this.editors = new Map();

    // Forcer l'inclusion des hooks (ne pas supprimer cette ligne)
    this._hooks = hooks_namespaceObject;

    // Références explicites pour forcer l'inclusion
    this._forceInclude = {
      useHistory: useHistory,
      useRotation: useRotation,
      useResize: useResize
    };

    // Forcer l'appel des hooks pour éviter le tree shaking
    try {
      var dummyHistory = useHistory();
      var dummyRotation = useRotation(function () {});
      var dummyResize = useResize();
      this._dummyInstances = {
        dummyHistory: dummyHistory,
        dummyRotation: dummyRotation,
        dummyResize: dummyResize
      };
    } catch (e) {
      // Ignorer les erreurs en mode SSR
    }
  }

  // Initialiser l'éditeur dans un conteneur
  return src_createClass(PDFBuilderPro, [{
    key: "init",
    value: function init(containerId) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      console.log('PDFBuilderPro.init called with:', containerId, options);
      try {
        // Vérification stricte du containerId
        if (!containerId || typeof containerId !== 'string') {
          throw new Error('ContainerId must be a non-empty string');
        }
        var container = document.getElementById(containerId);
        if (!container) {
          throw new Error("Container with ID \"".concat(containerId, "\" does not exist in the DOM"));
        }

        // Vérifier la disponibilité de React et ReactDOM
        if (!react || !react_dom) {
          throw new Error('React or ReactDOM is not available. Make sure the scripts are loaded properly.');
        }

        // Vérifier que PDFCanvasEditor est disponible
        if (!PDFCanvasEditor) {
          throw new Error('PDFCanvasEditor component is not available. Check for compilation errors.');
        }

        // Options par défaut avec validation
        var defaultOptions = src_objectSpread({
          templateId: null,
          templateName: null,
          isNew: true,
          initialElements: [],
          width: 595,
          // A4 width in points
          height: 842,
          // A4 height in points
          zoom: 1,
          gridSize: 10,
          snapToGrid: true
        }, options);

        // Validation des options critiques
        if (typeof defaultOptions.width !== 'number' || defaultOptions.width <= 0) {
          console.warn('PDFBuilderPro: Invalid width, using default A4 width');
          defaultOptions.width = 595;
        }
        if (typeof defaultOptions.height !== 'number' || defaultOptions.height <= 0) {
          console.warn('PDFBuilderPro: Invalid height, using default A4 height');
          defaultOptions.height = 842;
        }

        // Créer l'éditeur React avec protection
        var editorElement = /*#__PURE__*/(0,react.createElement)(PDFCanvasEditor, {
          options: defaultOptions
        });

        // Vérifier que l'élément a été créé correctement
        if (!editorElement) {
          throw new Error('Failed to create React element for PDFCanvasEditor');
        }
        react_dom.render(editorElement, container);
        this.editors.set(containerId, {
          container: container,
          options: defaultOptions
        });
        console.log('PDFBuilderPro: Editor initialized successfully for container:', containerId);
      } catch (error) {
        console.error('PDFBuilderPro: Failed to initialize editor:', error);

        // Fallback visuel pour l'utilisateur
        var _container = document.getElementById(containerId);
        if (_container) {
          _container.innerHTML = "\n                    <div style=\"\n                        color: #721c24;\n                        background-color: #f8d7da;\n                        border: 1px solid #f5c6cb;\n                        border-radius: 4px;\n                        padding: 15px;\n                        margin: 10px 0;\n                        font-family: Arial, sans-serif;\n                        font-size: 14px;\n                    \">\n                        <strong>Erreur PDF Builder Pro</strong><br>\n                        Impossible d'initialiser l'\xE9diteur. V\xE9rifiez la console pour plus de d\xE9tails.<br>\n                        <small>Erreur: ".concat(error.message, "</small>\n                    </div>\n                ");
        }

        // Re-throw pour permettre la gestion en amont si nécessaire
        throw error;
      }
    }

    // Détruire un éditeur
  }, {
    key: "destroy",
    value: function destroy(containerId) {
      try {
        var editor = this.editors.get(containerId);
        if (editor) {
          // Vérifier que ReactDOM est disponible avant de démonter
          if (react_dom && react_dom.unmountComponentAtNode) {
            react_dom.unmountComponentAtNode(editor.container);
          }
          this.editors["delete"](containerId);
          console.log('PDFBuilderPro: Editor destroyed for container:', containerId);
        }
      } catch (error) {
        console.error('PDFBuilderPro: Error during destroy:', error);
        // Forcer la suppression même en cas d'erreur
        this.editors["delete"](containerId);
      }
    }

    // Obtenir les données d'un éditeur
  }, {
    key: "getData",
    value: function getData(containerId) {
      // Cette méthode pourrait être étendue pour récupérer l'état actuel
      return null;
    }
  }]);
}(); // Instance globale
var pdfBuilderPro = new PDFBuilderPro();
console.log('PDF Builder Pro: PDFBuilderPro instance created');

// Attacher à window pour WordPress - avec vérification et protection
if (typeof window !== 'undefined') {
  // Effectuer le health check avant d'exposer l'instance
  if (PDFBuilderSecurity.performHealthCheck()) {
    window.PDFBuilderPro = pdfBuilderPro;
    // Alias pour compatibilité
    window.pdfBuilderPro = pdfBuilderPro;
    console.log('PDF Builder Pro: PDFBuilderPro attached to window');

    // Marquer comme initialisé pour éviter les conflits
    PDFBuilderSecurity.preventMultipleInit();
  } else {
    console.error('PDF Builder Pro: Not attaching to window due to health check failure');
    // Exposer quand même une version limitée pour le debugging
    window.PDFBuilderPro = {
      version: '2.0.0',
      status: 'unhealthy',
      errors: PDFBuilderSecurity.errors,
      healthChecks: PDFBuilderSecurity.healthChecks
    };
  }
}

// Export par défaut pour webpack
/* harmony default export */ const src = ((/* unused pure expression or super */ null && (pdfBuilderPro)));
/******/ })()
;