(function(){/*
scrollplus: a custom scrollbar for your website
@author  Levente Hunyadi
@version 1.0
@remarks Copyright (C) 2017-2018 Levente Hunyadi
@remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
@see     http://hunyadi.info.hu/projects/scrollplus
*/
'use strict';var k={orientation:"vertical"};function r(a){a=a||{};for(var b in k)!Object.prototype.hasOwnProperty.call(k,b)||b in a||(a[b]=k[b]);return a}
function v(a,b){function e(c){var a=document.createElement("div");a.classList.add(c);return a}function t(c){var a=g.getBoundingClientRect();d.scrollLeft=(d.scrollWidth-d.clientWidth)*(c.clientX-a.left-.5*h.offsetWidth)/(g.offsetWidth-h.offsetWidth);d.scrollTop=(d.scrollHeight-d.clientHeight)*(c.clientY-a.top-.5*h.offsetHeight)/(g.offsetHeight-h.offsetHeight)}function l(){var c=h.style,a=g.style,b=1,m=1,f=1,e=0;switch(n){case "vertical":b=d.scrollHeight;m=d.clientHeight;f=g.offsetHeight;e=d.scrollTop;
break;case "horizontal":b=d.scrollWidth,m=d.clientWidth,f=g.offsetWidth,e=d.scrollLeft}f=Math.max(8,m/b*.5*f);var l=b>m?100*e/(b-m)+"%":"0";switch(n){case "vertical":c.top=l;c.margin=-f+"px 0";c.padding=f+"px 0";a.padding=f+"px 0";break;case "horizontal":c.left=l,c.margin="0 "+-f+"px",c.padding="0 "+f+"px",a.padding="0 "+f+"px"}c=p.classList;c.remove("scrollplus-start");c.remove("scrollplus-end");0==e&&c.add("scrollplus-start");e==b-m&&c.add("scrollplus-end")}function q(a){a.preventDefault()}if(a){var n=
r(b).orientation;a.classList.add("scrollplus-container");a.classList.contains("scrollplus-vertical")?n="vertical":a.classList.contains("scrollplus-horizontal")?n="horizontal":a.classList.add("scrollplus-"+n);var p=e("scrollplus-dock"),d=e("scrollplus-view");b=e("scrollplus-content");for(var g=e("scrollplus-track"),h=e("scrollplus-thumb");a.firstChild;)b.appendChild(a.firstChild);d.appendChild(b);g.appendChild(h);p.appendChild(d);p.appendChild(g);a.appendChild(p);d.addEventListener("scroll",l);window.addEventListener("resize",
l);g.addEventListener("click",function(a){t(a);q(a)});var u=!1;h.addEventListener("mousedown",function(a){1==a.buttons&&(u=!0,document.addEventListener("selectstart",q),t(a))});h.addEventListener("dragstart",q);document.addEventListener("mouseup",function(){u=!1;document.removeEventListener("selectstart",q)});document.addEventListener("mousemove",function(a){u&&t(a)});this.view=d;l();window.requestAnimationFrame(function(){l()})}}
function w(a,b){a=a.view;0<=b&&(a.scrollTop=b);a.dispatchEvent(new Event("scroll"))}v.prototype.b=function(){w(this,0)};v.prototype.a=function(){w(this,this.view.scrollHeight)};window.ScrollPlus=v;v.prototype.scrollToTop=v.prototype.b;v.prototype.scrollToBottom=v.prototype.a;}).call(this);
