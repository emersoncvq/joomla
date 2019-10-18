/**@license scrollplus: a custom scrollbar for your website
* @author  Levente Hunyadi
* @version 1.0
* @remarks Copyright (C) 2017-2018 Levente Hunyadi
* @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see     http://hunyadi.info.hu/projects/scrollplus
**/

/*
* scrollplus: a custom scrollbar for your website
* Copyright 2017-2018 Levente Hunyadi
*
* scrollplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* scrollplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with scrollplus.  If not, see <http://www.gnu.org/licenses/>.
*/

/*{"compilation_level":"ADVANCED_OPTIMIZATIONS"}*/
'use strict';

/**
* Content type shown in the pop-up window.
* @enum {string}
*/
const ScrollPlusOrientation = {
	Vertical: 'vertical',
	Horizontal: 'horizontal'
};

/**
* Options for the scroll-bar.
*
* @typedef {{
*     orientation: (ScrollPlusOrientation|undefined)
* }}
*/
let ScrollPlusOptions;

/** @type {!ScrollPlusOptions} */
const ScrollPlusDefaults = {
	'orientation': ScrollPlusOrientation.Vertical
};

/**
* Sets all undefined properties on an object using a reference object.
* @param {Object|null|undefined} obj The original object whose keys are to be enumerated.
* @param {!Object} ref The reference object to provide a default value for unset keys.
* @return {!Object} The original object (or a new object is the original is null) with a value for all keys.
*/
function applyDefaults(obj, ref) {
	/** @type {!Object} */
	let ret = obj || {};
	for (const prop in /** @type {!Object} */ (ref)) {
		if (Object.prototype.hasOwnProperty.call(ref, prop) && !(prop in ret)) {
			ret[prop] = ref[prop];
		}
	}
	return ret;
}

/**
* Gets the outer height of an element, including margin, border and padding.
* @param {!HTMLElement} elem
* @return {string} A CSS length value, possibly involving `calc`.
*/
function getOuterHeight(elem) {
	function isCSSLength(value) {
		return /ch|e[mx]|rem|v([hw]|min|max)|px|[cm]m|in|p[ct]$/.test(value);
	}

	let style = window.getComputedStyle(elem);
	let values = ['margin-top','margin-bottom'].map(function (key) {
		return style[key];
	}).filter(function (value) {
		return isCSSLength(value);
	});
	let css = elem.offsetHeight + 'px';
	if (values.length > 0) {
		css = 'calc(' + css + ' + ' + values.join(' + ') + ')';
	}
	return css;
}

/**
* Adds scrollbar behavior to an HTML parent element.
* The following HTML structure is created where the root element corresponds to the original parent element:
* ```
*   <div class="scrollplus-container">
*     <div class="scrollplus-dock">
*       <div class="scrollplus-view">
*         <div class="scrollplus-content">
*           <!-- original element content -->
*         </div>
*       </div>
*       <div class="scrollplus-track">
*         <div class="scrollplus-thumb"></div>
*       </div>
*     </div>
*   </div>
* ```
*
* @constructor
* @param {HTMLElement} elem An element to be made scrollable.
* @param {ScrollPlusOptions=} options
*/
function ScrollPlus(elem, options) {
	// element existence check to ensure element is within DOM, some content management
	// systems may call the script even if the associated content is not on the page,
	// which is the case e.g. with Joomla category list layout or multi-page layout
	if (!elem) {
		return;
	}

	let opts = /** @type {!ScrollPlusOptions} */ (applyDefaults(options, ScrollPlusDefaults));
	let orientation = opts['orientation'];

	elem.classList.add('scrollplus-container');
	if (elem.classList.contains('scrollplus-vertical')) {
		orientation = ScrollPlusOrientation.Vertical;
	} else if (elem.classList.contains('scrollplus-horizontal')) {
		orientation = ScrollPlusOrientation.Horizontal;
	} else {
		elem.classList.add('scrollplus-' + /** @type {string} */ (orientation));
	}

	/**
	* @param {string} cls A CSS class name the newly created element takes.
	* @return {!HTMLElement}
	*/
	function createElement(cls) {
		let elem = /** @type {!HTMLElement} */ (document.createElement('div'));
		elem.classList.add(cls);
		return elem;
	}

	// create HTML DOM structure
	let dock = createElement('scrollplus-dock');
	let view = createElement('scrollplus-view');
	let content = createElement('scrollplus-content');
	let track = createElement('scrollplus-track');
	let thumb = createElement('scrollplus-thumb');

	// adopt all children of original element
	while (elem.firstChild) {
		content.appendChild(elem.firstChild);  // causes element to be removed from original parent
	}
	view.appendChild(content);
	track.appendChild(thumb);
	dock.appendChild(view);
	dock.appendChild(track);
	elem.appendChild(dock);

	/**
	* @param {!MouseEvent} evt
	*/
	function scrollToMousePosition(evt) {
		let trackClientRect = track.getBoundingClientRect();
		view.scrollLeft = (view.scrollWidth - view.clientWidth) * (evt.clientX - trackClientRect.left - 0.5 * thumb.offsetWidth) / (track.offsetWidth - thumb.offsetWidth);
		view.scrollTop = (view.scrollHeight - view.clientHeight) * (evt.clientY - trackClientRect.top - 0.5 * thumb.offsetHeight) / (track.offsetHeight - thumb.offsetHeight);
	}

	function updateThumbPosition() {
		let thumbStyle = thumb.style;
		let trackStyle = track.style;

		/** @type {number} */
		let scrollSize = 1;
		/** @type {number} */
		let clientSize = 1;
		/** @type {number} */
		let trackSize = 1;
		/** @type {number} */
		let offset = 0;

		switch (orientation) {
			case ScrollPlusOrientation.Vertical:
				scrollSize = view.scrollHeight;
				clientSize = view.clientHeight;
				trackSize = track.offsetHeight;
				offset = view.scrollTop;
				break;
			case ScrollPlusOrientation.Horizontal:
				scrollSize = view.scrollWidth;
				clientSize = view.clientWidth;
				trackSize = track.offsetWidth;
				offset = view.scrollLeft;
				break;
		}

		/** @type {number} */
		let ratioViewToContent = clientSize / scrollSize;
		/** @type {number} */
		let extent = Math.max(8, 0.5 * ratioViewToContent * trackSize);  // multiply by 0.5 because thumb extends in both directions
		/** @type {string} */
		let position = scrollSize > clientSize ? 100 * offset / (scrollSize - clientSize) + '%' : '0';

		switch (orientation) {
			case ScrollPlusOrientation.Vertical:
				thumbStyle.top = position;
				thumbStyle.margin = (-extent) + 'px 0';
				thumbStyle.padding = extent + 'px 0';
				trackStyle.padding = extent + 'px 0';
				break;
			case ScrollPlusOrientation.Horizontal:
				thumbStyle.left = position;
				thumbStyle.margin = '0 ' + (-extent) + 'px';
				thumbStyle.padding = '0 ' + extent + 'px';
				trackStyle.padding = '0 ' + extent + 'px';
				break;
		}

		let dockClass = dock.classList;
		dockClass.remove('scrollplus-start');
		dockClass.remove('scrollplus-end');
		if (offset == 0) {  // scrolled to top
			dockClass.add('scrollplus-start');
		}
		if (offset == scrollSize - clientSize) {  // scrolled to bottom
			dockClass.add('scrollplus-end');
		}
	}

	/**
	* Prevents the default action associated with an event.
	* @param {Event} evt
	*/
	function preventDefault(evt) {
		evt.preventDefault();
	}

	view.addEventListener('scroll', updateThumbPosition);
	window.addEventListener('resize', updateThumbPosition);
	track.addEventListener('click', function (event) {
		let mouseEvent = /** @type {!MouseEvent} */ (event);
		scrollToMousePosition(mouseEvent);
		preventDefault(mouseEvent);
	});

	/** @type {boolean} */
	let depressed = false;
	thumb.addEventListener('mousedown', function (event) {
		let mouseEvent = /** @type {!MouseEvent} */ (event);
		if (mouseEvent.buttons == 1) {
			depressed = true;
			document.addEventListener('selectstart', preventDefault);
			scrollToMousePosition(mouseEvent);
		}
	});
	thumb.addEventListener('dragstart', preventDefault);
	document.addEventListener('mouseup', function () {
		depressed = false;
		document.removeEventListener('selectstart', preventDefault);
	});
	document.addEventListener('mousemove', function (event) {
		if (depressed) {
			scrollToMousePosition(/** @type {!MouseEvent} */ (event));
		}
	});

	/** @type {!HTMLElement} */
	this.view = view;
	updateThumbPosition();
	window.requestAnimationFrame(function () {  // address an issue in Firefox
		updateThumbPosition();
	});
}
/**
* @param {number} pos_x
* @param {number} pos_y
*/
ScrollPlus.prototype.scrollTo = function (pos_x, pos_y) {
	let view = this.view;
	if (pos_x >= 0) {
		view.scrollLeft = pos_x;
	}
	if (pos_y >= 0) {
		view.scrollTop = pos_y;
	}
	view.dispatchEvent(new Event('scroll'));
}
ScrollPlus.prototype.scrollToTop = function () {
	this.scrollTo(-1,0);
}
ScrollPlus.prototype.scrollToBottom = function () {
	this.scrollTo(-1,this.view.scrollHeight);
}

// export symbols
window['ScrollPlus'] = ScrollPlus;
ScrollPlus.prototype['scrollToTop'] = ScrollPlus.prototype.scrollToTop;
ScrollPlus.prototype['scrollToBottom'] = ScrollPlus.prototype.scrollToBottom;
