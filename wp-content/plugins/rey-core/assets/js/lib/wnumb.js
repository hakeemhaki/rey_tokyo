!function(e){"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?module.exports=e():window.wNumb=e()}((function(){"use strict";var e=["decimals","thousand","mark","prefix","suffix","encoder","decoder","negativeBefore","negative","edit","undo"];function t(e){return e.split("").reverse().join("")}function n(e,t){return e.substring(0,t.length)===t}function r(e,t,n){if((e[t]||e[n])&&e[t]===e[n])throw new Error(t)}function i(e){return"number"==typeof e&&isFinite(e)}function o(e,n,r,o,f,u,s,c,a,p,d,l){var h,g,v,m=l,w="",x="";return u&&(l=u(l)),!!i(l)&&(!1!==e&&0===parseFloat(l.toFixed(e))&&(l=0),l<0&&(h=!0,l=Math.abs(l)),!1!==e&&(l=function(e,t){return e=e.toString().split("e"),(+((e=(e=Math.round(+(e[0]+"e"+(e[1]?+e[1]+t:t)))).toString().split("e"))[0]+"e"+(e[1]?e[1]-t:-t))).toFixed(t)}(l,e)),-1!==(l=l.toString()).indexOf(".")?(v=(g=l.split("."))[0],r&&(w=r+g[1])):v=l,n&&(v=t((v=t(v).match(/.{1,3}/g)).join(t(n)))),h&&c&&(x+=c),o&&(x+=o),h&&a&&(x+=a),x+=v,x+=w,f&&(x+=f),p&&(x=p(x,m)),x)}function f(e,t,r,o,f,u,s,c,a,p,d,l){var h,g="";return d&&(l=d(l)),!(!l||"string"!=typeof l)&&(c&&n(l,c)&&(l=l.replace(c,""),h=!0),o&&n(l,o)&&(l=l.replace(o,"")),a&&n(l,a)&&(l=l.replace(a,""),h=!0),f&&function(e,t){return e.slice(-1*t.length)===t}(l,f)&&(l=l.slice(0,-1*f.length)),t&&(l=l.split(t).join("")),r&&(l=l.replace(r,".")),h&&(g+="-"),""!==(g=(g+=l).replace(/[^0-9\.\-.]/g,""))&&(g=Number(g),s&&(g=s(g)),!!i(g)&&g))}function u(t,n,r){var i,o=[];for(i=0;i<e.length;i+=1)o.push(t[e[i]]);return o.push(r),n.apply("",o)}return function t(n){if(!(this instanceof t))return new t(n);"object"==typeof n&&(n=function(t){var n,i,o,f={};for(void 0===t.suffix&&(t.suffix=t.postfix),n=0;n<e.length;n+=1)if(void 0===(o=t[i=e[n]]))"negative"!==i||f.negativeBefore?"mark"===i&&"."!==f.thousand?f[i]=".":f[i]=!1:f[i]="-";else if("decimals"===i){if(!(0<=o&&o<8))throw new Error(i);f[i]=o}else if("encoder"===i||"decoder"===i||"edit"===i||"undo"===i){if("function"!=typeof o)throw new Error(i);f[i]=o}else{if("string"!=typeof o)throw new Error(i);f[i]=o}return r(f,"mark","thousand"),r(f,"prefix","negative"),r(f,"prefix","negativeBefore"),f}(n),this.to=function(e){return u(n,o,e)},this.from=function(e){return u(n,f,e)})}}));