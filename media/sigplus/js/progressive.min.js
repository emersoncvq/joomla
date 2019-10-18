(function(){/*
sigplus progressive gallery
@author  Levente Hunyadi
@version 1.5.0
@remarks Copyright (C) 2011-2018 Levente Hunyadi
@remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
@see     http://hunyadi.info.hu/projects/sigplus
*/
'use strict';var p={limit:20,show_more:"Show next {$next} of {$left} remaining...",no_more:"No more items to show"};
window.ProgressiveGallery=function(h,f){function q(a,b){b.forEach(function(c){if(a.hasAttribute(c)){var d="data-"+c,b=a.getAttribute(c);a.setAttribute(d,b);a.removeAttribute(c)}})}function r(a,b){b.forEach(function(b){var d="data-"+b;if(a.hasAttribute(d)){var c=a.getAttribute(d);a.setAttribute(b,c);a.removeAttribute(d)}})}function t(a,b){function c(){--d;0==d&&b()}var d=a.length;1>d?b():a.forEach(function(a){a.addEventListener("load",c);a.addEventListener("error",c);r(a,["src","srcset","sizes"])})}
function m(a,b,c,d){a.text=b.replace("{$next}",""+Math.min(c,d)).replace("{$left}",""+d)}var l=function(a,b){a=a||{};for(var c in JSON.parse(JSON.stringify(b)))a[c]=a[c]||b[c];return a}(f,p),g=l.limit,n=l.show_more;if(h&&(f=h.querySelectorAll("li"),f.length>g)){h.classList.add("sigplus-progressive");var e=document.createElement("a");e.href="#";e.classList.add("sigplus-more");m(e,n,g,f.length-g);h.appendChild(e);for(var k=g;k<f.length;++k)f[k].classList.add("sigplus-hidden"),[].forEach.call(f[k].querySelectorAll("img"),
function(a){q(a,["src","srcset","sizes"])});e.addEventListener("click",function(a){a.preventDefault();var b=h.querySelectorAll("li.sigplus-hidden"),c=[];for(a=0;a<g&&a<b.length;++a)[].forEach.call(b[a].querySelectorAll("img"),function(a){c.push(a)});t(c,function(){for(var a=0;a<g&&a<b.length;++a)b[a].classList.remove("sigplus-hidden");a<b.length?m(e,n,g,b.length-a):(e.classList.add("sigplus-final"),e.text=l.no_more)})})}};}).call(this);
