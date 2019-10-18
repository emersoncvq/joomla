/**@license captionplus mouse-over image caption engine
* @author  Levente Hunyadi
* @version 1.5.0
* @remarks Copyright (C) 2009-2014 Levente Hunyadi
* @see     http://hunyadi.info.hu/projects/
**/

/*{"compilation_level":"ADVANCED_OPTIMIZATIONS"}*/
'use strict';

/**
* Position of the caption relative to the image.
* @enum {string}
*/
const CaptionPlusPosition = {
	Top: 'top',
	Bottom: 'bottom'
};

/**
* Determines when the caption is to be seen.
* @enum {string}
*/
const CaptionPlusVisibility = {
	Always: 'always',
	MouseOver: 'mouseover'
};

/**
* Horizontal and vertical alignment of text within the caption area.
* @enum {string}
*/
const CaptionPlusAlignment = {
	Center: 'center',
	Start: 'start',
	End: 'end'
};

/**
* Caption engine options.
* Unrecognized options set via the loosely-typed parameter object are silently ignored.
*
* @typedef {{
*     download: boolean,
*     overlay: boolean,
*     position: CaptionPlusPosition,
*     visibility: CaptionPlusVisibility,
*     horzalign: CaptionPlusAlignment,
*     vertalign: CaptionPlusAlignment
* }}
*/
let CaptionPlusOptions;

/** @type {!CaptionPlusOptions} */
const CaptionPlusDefaults = {
	'download': true,
	'overlay': true,
	'position': CaptionPlusPosition.Bottom,
	'visibility': CaptionPlusVisibility.MouseOver,
	'horzalign': CaptionPlusAlignment.Center,
	'vertalign': CaptionPlusAlignment.Center
};

/**
* Adds a mouse-over image caption to a single image.
* @constructor
* @param {!HTMLElement} elem
* @param {CaptionPlusOptions} opts
*/
function CaptionPlus(elem, opts) {
	/**
	* Gets the value of an attribute
	* @param {Element} elem The HTML element to inspect.
	* @param {string} attr The name of the attribute to search for.
	* @return {?string} The value of the attribute if it exists, or null.
	*/
	function getAttributeOrNull(elem, attr) {
		if (elem && elem.hasAttribute(attr)) {
			return elem.getAttribute(attr);
		} else {
			return null;
		}
	}

	/**
	* Sets all undefined properties on an object using a reference object.
	* @param {Object|null|undefined} obj
	* @param {!Object} ref
	* @return {!Object}
	*/
	function applyDefaults(obj, ref) {
		/** @type {!Object} */
		let extended = obj || {};
		for (const prop in /** @type {!Object} */ (JSON.parse(JSON.stringify(ref)))) {  // use JSON functions to clone object
			if (!Object.prototype.hasOwnProperty.call(extended, prop)) {
				extended[prop] = /** @type {*} */ (ref[prop]);
			}
		}
		return extended;
	}

	let self = this;
	let options = /** @type {!CaptionPlusOptions} */ (applyDefaults(opts, CaptionPlusDefaults));

	if (elem.querySelector('.captionplus')) {
		return;  // already has a caption, quit
	}

	let image = /** @type {HTMLImageElement} */ (elem.querySelector('img'));
	if (image) {
		let anchor = /** @type {HTMLAnchorElement} */ (elem.querySelector('a'));
		let caption = getAttributeOrNull(anchor, 'data-title') || image.alt;
		let downloadURL;
		if (options['download']) {
			downloadURL = getAttributeOrNull(anchor, 'data-download');
		}
		if (caption || downloadURL) {
			// area that contains caption text and inline action buttons
			let captionarea = /** @type {HTMLElement} */ (document.createElement('div'));
			captionarea.classList.add('captionplus-align');
			captionarea.classList.add('captionplus-horizontal-' + options['horzalign']);
			captionarea.classList.add('captionplus-vertical-' + options['vertalign']);
			if (caption) {  // text content
				let captiontext = /** @type {HTMLElement} */ (document.createElement('div'));
				captiontext.innerHTML = caption;
				captionarea.appendChild(captiontext);
			}

			// outer container for caption text and action buttons
			let captioncontainer = /** @type {HTMLElement} */ (document.createElement('div'));
			captioncontainer.classList.add(!options['overlay'] ? 'captionplus-outside' : 'captionplus-overlay');
			captioncontainer.classList.add('captionplus-' + options['position']);
			captioncontainer.classList.add('captionplus-' + options['visibility']);
			captioncontainer.appendChild(captionarea);

			// wraps all original element children and the injected caption area
			let wrapper = /** @type {HTMLElement} */ (document.createElement('div'));
			wrapper.classList.add('captionplus');
			for (let i = 0; i < elem.children.length; i++) {
				let item = elem.children[i];
				wrapper.appendChild(item);  // will remove child from original element parent
			}
			wrapper.appendChild(captioncontainer);

			if (downloadURL) {  // download button
				let downloadicon = /** @type {HTMLAnchorElement} */ (document.createElement('a'));
				downloadicon.classList.add('captionplus-button');
				downloadicon.classList.add('captionplus-download');
				downloadicon.href = downloadURL;
				captionarea.appendChild(downloadicon);
			}

			elem.appendChild(wrapper);
		}
	}
}
CaptionPlus.bind = function (elem, options) {
	// element existence test to ensure element is within DOM, some content management
	// systems may call the script even if the associated content is not on the page,
	// which is the case e.g. with Joomla category list layout or multi-page layout
	if (elem) {
		for (let i = 0; i < elem.children.length; i++) {
			let item = elem.children[i];
			if ('li' == item.tagName.toLowerCase()) {
				new CaptionPlus(item, options);
			}
		}
	}
}
CaptionPlus['bind'] = CaptionPlus.bind;
window['CaptionPlus'] = CaptionPlus;
