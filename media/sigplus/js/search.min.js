(function(){/*
sigplus Image Gallery Plus index image population for search results script
@author  Levente Hunyadi
@version 1.5.0
@remarks Copyright (C) 2009-2018 Levente Hunyadi
@remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
@see     http://hunyadi.info.hu/projects/sigplus
*/
'use strict';window.__sigplusSearch=function(d,g,h,k){function l(a,b){for(var c=null;a;){if(f.call(a,b)){c=a;break}a=a.parentElement}return c}function m(a,b){var c=null;if(a)for(a=a.nextElementSibling;a;){if(f.call(a,b)){c=a;break}a=a.nextElementSibling}return c}function n(a,b,c){b.addEventListener(a,function(a){c.dispatchEvent(new a.constructor(a.type,a));a.preventDefault();a.stopPropagation()})}var f=Element.prototype.matches||Element.prototype.msMatchesSelector;if(d=document.querySelector('.result-title a[href="'+
d+'"]')){var e=m(l(d,".result-title"),".result-text");if(e){var b=document.createElement("img");b.classList.add("sigplus-search-preview");b.src=g;b.width=h;b.height=k;n("click",b,d);e.insertBefore(b,e.firstChild)}}};}).call(this);
