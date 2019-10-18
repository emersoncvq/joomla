(function(){/*
sigplus editor button
@author  Levente Hunyadi
@version 1.5.0
@remarks Copyright (C) 2011-2017 Levente Hunyadi
@remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
@see     http://hunyadi.info.hu/projects/sigplus
*/
'use strict';Element.prototype.matches||(Element.prototype.matches=Element.prototype.msMatchesSelector||Element.prototype.webkitMatchesSelector);function f(e,d,b){d="{"+d+b+"}myfolder{/"+d+"}";b=window.parent;var a;a:{if((a=window.parent.Joomla)&&(a=a.editors)&&(a=a.instances)&&a.hasOwnProperty(e)){a=a[e];break a}a=void 0}a?a.replaceSelection(d):(0,b.jInsertEditorText)(d,e);(0,b.jModalClose)()}
function g(){var e=window.location.search,d={};1<e.length&&e.substr(1).split("&").forEach(function(b){var a=b.indexOf("="),c=0<=a?b.substr(a+1):"";d[decodeURIComponent(0<=a?b.substr(0,a):b)]=decodeURIComponent(c)});return d}
document.addEventListener("DOMContentLoaded",function(){function e(c){c=c.getAttribute("name");var a=c.match(/^params\[(.*)\]$/);return a?a[1]:c}var d=document.getElementById("sigplus-settings-form"),b=[].slice.call(d.querySelectorAll("li")),a=window.parent.sigplus;a&&[].forEach.call(d.querySelectorAll("input[name][type=checkbox],input[name][type=radio],input[name][type=text],select[name]"),function(c){var b=e(c);if(b=a[b])c.matches("input[name][type=checkbox]")?c.checked=!!b:c.matches("input[name][type=radio]")&&
c.value===""+b?c.checked=!0:c.matches("input[name][type=text],select[name]")&&(c.value=b)});[].forEach.call(b,function(a){var c=document.createElement("input");c.setAttribute("type","checkbox");[].forEach.call(a.querySelectorAll("input[name][type=checkbox],input[name][type=radio],input[name][type=text],select[name]"),function(a){a.addEventListener("focus",function(){c.checked=!0})});a.insertBefore(c,a.firstChild)});document.getElementById("sigplus-settings-submit").addEventListener("click",function(){var c=
[];[].forEach.call(b,function(a){var b=a.querySelector("input[type=checkbox]");if((a=a.querySelector("input[name][type=checkbox]:checked,input[name][type=radio]:checked,input[name][type=text],select[name]"))&&b&&b.checked&&(b=e(a),a=a.value)){if(/color$/.test(b)||!/^(0|[1-9]\d*)$/.test(a))a='"'+a+'"';c.push(b+"="+a)}});var d=0<c.length?" "+c.join(" "):"";f(g().editor,a.tag_gallery,d)})});}).call(this);
