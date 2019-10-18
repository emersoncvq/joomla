/**@license sigplus progressive gallery
* @author  Levente Hunyadi
* @version 1.5.0
* @remarks Copyright (C) 2011-2018 Levente Hunyadi
* @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see     http://hunyadi.info.hu/projects/sigplus
**/

/*{"compilation_level":"ADVANCED_OPTIMIZATIONS"}*/
'use strict';

/**
* Options for the progressive gallery.
* Unrecognized options set via the loosely-typed parameter object are silently ignored.
* The object has the following properties:
* + limit: The number of images to reveal on clicking "Show more...".
* + show_more: The localized label of the "Show more..." button/link.
* + no_more: The localized label shown when there are no additional items to display.
*
* @typedef {{
*     limit: number,
*     show_more: string,
*     no_more: string
* }}
*/
let ProgressiveGalleryOptions;

/** @type {!ProgressiveGalleryOptions} */
const progressiveDefaults = {
	'limit': 20,
	'show_more': 'Show next {$next} of {$left} remaining...',
	'no_more': 'No more items to show'
};

/**
* A gallery that unveils progressively as the user scrolls to the bottom and clicks a "Show more..." link.
* @constructor
* @param {HTMLElement} gallery
* @param {Object=} opts
*/
function ProgressiveGallery(gallery, opts) {
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
			extended[prop] = /** @type {*} */ (extended[prop]) || /** @type {*} */ (ref[prop]);
		}
		return extended;
	}

	/**
	* @param {!Element} elem
	* @param {!Array<string>} attributes
	*/
	function moveAttributesToDataset(elem, attributes) {
		attributes.forEach(function (name) {
			if (elem.hasAttribute(name)) {
				let dataname = 'data-' + name;
				let value = elem.getAttribute(name);
				elem.setAttribute(dataname, value);
				elem.removeAttribute(name);
			}
		});
	}

	/**
	* @param {!Element} elem
	* @param {!Array<string>} attributes
	*/
	function moveAttributesFromDataset(elem, attributes) {
		attributes.forEach(function (name) {
			let dataname = 'data-' + name;
			if (elem.hasAttribute(dataname)) {
				let value = elem.getAttribute(dataname);
				elem.setAttribute(name, value);
				elem.removeAttribute(dataname);
			}
		});
	}

	/**
	* @param {!Array<!HTMLImageElement>} images
	* @param {function()} callback
	*/
	function loadImages(images, callback) {
		let count = images.length;

		if (count < 1) {
			callback();
			return;
		}

		function imageEventListener() {
			--count;

			// check if there are further images waiting to load
			if (count == 0) {
				callback();
			}
		}

		images.forEach(function (image) {
			image.addEventListener('load', imageEventListener);
			image.addEventListener('error', imageEventListener);

			// re-apply previously cleared native image attributes by reading data attributes
			moveAttributesFromDataset(image, ['src','srcset','sizes']);
		});
	}

	/**
	* @param {!HTMLElement} elem
	* @param {string} text
	* @param {number} next
	* @param {number} left
	*/
	function updateShowMoreLabel(elem, text, next, left) {
		elem.text = text.replace('{$next}', '' + Math.min(next, left)).replace('{$left}', '' + left);
	}

	/** @type {!ProgressiveGalleryOptions} */
	let options = /** @type {!ProgressiveGalleryOptions} */ (applyDefaults(opts, progressiveDefaults));
	let limit = options['limit'];
	let label = options['show_more'];

	if (gallery) {
		let items = gallery.querySelectorAll('li');
		if (items.length > limit) {
			gallery.classList.add('sigplus-progressive');

			// "Show more..." anchor
			let anchor = /** @type {HTMLAnchorElement} */ (document.createElement('a'));
			anchor.href = '#';
			anchor.classList.add('sigplus-more');
			updateShowMoreLabel(anchor, label, limit, items.length - limit);
			gallery.appendChild(anchor);

			// delay fetching image data for hidden elements
			for (let i = limit; i < items.length; ++i) {
				items[i].classList.add('sigplus-hidden');
				[].forEach.call(/** @type {!IArrayLike<!HTMLImageElement>} */ (items[i].querySelectorAll('img')), function (/** @type {!HTMLImageElement} */ image) {
					moveAttributesToDataset(image, ['src','srcset','sizes']);
				});
			}

			// unveil elements upon request
			anchor.addEventListener('click', function (evt) {
				evt.preventDefault();

				// find elements still hidden
				let hiddenItems = gallery.querySelectorAll('li.sigplus-hidden');

				// find images wrapped in hidden elements
				let images = [];
				for (let i = 0; i < limit && i < hiddenItems.length; ++i) {
					[].forEach.call(/** @type {!IArrayLike<!HTMLImageElement>} */ (hiddenItems[i].querySelectorAll('img')), function (/** @type {!HTMLImageElement} */ image) {
						images.push(image);
					});
				}

				loadImages(images, function () {
					// show some (but not all) hidden elements
					let i = 0;
					for (; i < limit && i < hiddenItems.length; ++i) {
						hiddenItems[i].classList.remove('sigplus-hidden');
					}
					if (i < hiddenItems.length) {
						updateShowMoreLabel(anchor, label, limit, hiddenItems.length - i);
					} else {  // all images are already visible
						anchor.classList.add('sigplus-final');
						anchor.text = options['no_more'];
					}
				});
			});
		}
	}
}
window['ProgressiveGallery'] = ProgressiveGallery;
