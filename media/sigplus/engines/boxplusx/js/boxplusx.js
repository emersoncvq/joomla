/**@license boxplusx: a versatile lightweight pop-up window engine
* @author  Levente Hunyadi
* @version 1.0
* @remarks Copyright (C) 2009-2017 Levente Hunyadi
* @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see     http://hunyadi.info.hu/projects/boxplusx
**/

/*
* boxplusx: a versatile lightweight pop-up window engine
* Copyright 2009-2017 Levente Hunyadi
*
* boxplusx is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* boxplusx is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with boxplusx.  If not, see <http://www.gnu.org/licenses/>.
*/

/*{"compilation_level":"ADVANCED_OPTIMIZATIONS"}*/
'use strict';

/**
* Attributes for an item.
* The object has the following properties:
* + url: URL pointing to the item to display (either relative or absolute).
* + image: Optional placeholder image for the item.
* + title: Short caption text associated with the item. May contain HTML tags.
* + description: Longer description text associated with the item. May contain HTML tags.
* + download: URL pointing to a high-resolution original to be downloaded.
*
* @typedef {{
*     url: string,
*     image: (HTMLImageElement|undefined),
*     title: string,
*     description: string,
*     download: string
* }}
*/
let BoxPlusXItemProperties;

/**
* Position of control with respect to the viewport area.
* @enum {string}
*/
const BoxPlusXPosition = {
	Hidden: 'hidden',
	Above: 'above',
	Top: 'top',
	Bottom: 'bottom',
	Below: 'below'
};

/**
* Text writing system.
* @enum {string}
*/
const BoxPlusXWritingSystem = {
	LeftToRight: 'ltr',
	RightToLeft: 'rtl'
};

/**
* Options for the boxplusx lightbox pop-up window.
* Unrecognized options set via the loosely-typed parameter object are silently ignored.
* The object has the following properties:
* + id: A unique identifier to assign to the pop-up window container. This helps add individual styling to dialog instances.
* + slideshow: Time spent viewing an image when slideshow mode is active, or 0 to disable slideshow mode.
* + autostart: Whether to start a slideshow when the dialog opens.
* + loop: Whether the image/content sequence loops such that the first image/content follows the last.
* + preferredWidth: Preferred default width for the content shown in the dialog.
* + preferredHeight: Preferred default height for the content shown in the dialog.
* + navigation: Position of quick-access navigation bar w.r.t. main viewport area.
* + controls: Position of control buttons w.r.t. main viewport area.
* + captions: Position of captions w.r.t. main viewport area.
* + metadata: Whether to show image metadata. (Requires third-party plugin Exif.js, see <https://github.com/exif-js/exif-js>.)
* + contextmenu: Whether to permit the user to open the context menu inside the dialog.
*
* @typedef {{
*     id: ?string,
*     slideshow: number,
*     autostart: boolean,
*     loop: boolean,
*     preferredWidth: number,
*     preferredHeight: number,
*     useDevicePixelRatio: boolean,
*     navigation: !BoxPlusXPosition,
*     controls: !BoxPlusXPosition,
*     captions: !BoxPlusXPosition,
*     contextmenu: boolean,
*     metadata: boolean,
*     dir: BoxPlusXWritingSystem
* }}
*/
let BoxPlusXOptions;

/** @type {!BoxPlusXOptions} */
const boxplusDefaults = {
	'id': null,
	'slideshow': 0,
	'autostart': false,
	'loop': false,
	'preferredWidth': 800,
	'preferredHeight': 600,
	'useDevicePixelRatio': true,
	'navigation': BoxPlusXPosition.Bottom,
	'controls': BoxPlusXPosition.Below,
	'captions': BoxPlusXPosition.Below,
	'contextmenu': true,
	'metadata': false,
	'dir': BoxPlusXWritingSystem.LeftToRight
};

/**
* Content type shown in the pop-up window.
* @enum {number}
*/
const BoxPlusXContentType = {
	None: -1,
	Unavailable: 0,
	Image: 1,
	Video: 2,
	EmbeddedContent: 3,
	DocumentFragment: 4,
	Frame: 5
};

/**
* Determine how content behaves when the container is resized.
* @enum {number}
*/
const BoxPlusXDimensionBehavior = {
	/** The item does not permit resizing (e.g. HTML <object> element with fixed width and height). */
	FixedSize: 1,
	/** The item has fixed aspect ratio (e.g. HTML <video> element). */
	FixedAspectRatio: 2,
	/** The item width and height can be set independently. */
	Resizable: 3,
	/** The item has an intrinsic width and height but either of these may be set to a smaller value when there is insufficient space. */
	ResizableBestFit: 4
};

/**
* Orientation constants.
* Position names represent how row #0 and column #0 are oriented, e.g. TopLeft is the upright orientation.
* @enum {number}
*/
const BoxPlusXOrientation = {
	WrongImageType: -2,  // image type cannot have EXIF information; e.g. GIF and PNG do not support orientation
	NoInformation: -1,  // EXIF information is missing from file or cannot be retrieved
	Unknown: 0,
	TopLeft: 1,
	TopRight: 2,
	BottomRight: 3,
	BottomLeft: 4,
	LeftTop: 5,
	RightTop: 6,
	RightBottom: 7,
	LeftBottom: 8
};

/**
* @constructor
* Allows viewing obscured parts of a scrollable element by making drag gestures with the mouse.
* @param {!HTMLElement} interceptor The element that intercepts drag events.
* @param {!HTMLElement} scrollable The element that scrolls in response to mouse movement.
*/
function BoxPlusXDraggable(interceptor, scrollable) {
	/** @type {boolean} */
	let dragged = false;
	/** @type {number} */
	let lastClientX = 0;
	/** @type {number} */
	let lastClientY = 0;
	/** @type {!Array<string>} */
	let scrollablePropertyValues = ['auto','scroll'];

	function dragStart(/** @type {Event} */ event) {
		let style = window.getComputedStyle(scrollable);
		let canScroll = scrollablePropertyValues.indexOf(style['overflowX']) >= 0 || scrollablePropertyValues.indexOf(style['overflowY']) >= 0;
		if (canScroll) {
			let mouseEvent = /** @type {MouseEvent} */ (event);
			lastClientX = mouseEvent.clientX;
			lastClientY = mouseEvent.clientY;
			dragged = true;
			mouseEvent.preventDefault();
		}
	}
	function dragEnd(/** @type {Event} */ event) {
		dragged = false;
	}
	function dragMove(/** @type {Event} */ event) {
		if (dragged) {
			let mouseEvent = /** @type {MouseEvent} */ (event);
			scrollable.scrollLeft -= mouseEvent.clientX - lastClientX;
			scrollable.scrollTop -= mouseEvent.clientY - lastClientY;
			lastClientX = mouseEvent.clientX;
			lastClientY = mouseEvent.clientY;
		}
	}

	interceptor.addEventListener('mousedown', dragStart);
	interceptor.addEventListener('mouseup', dragEnd);
	interceptor.addEventListener('mouseout', dragEnd);
	interceptor.addEventListener('mousemove', dragMove);
}

(function () {  // use anonymous wrapper to make sure we do not pollute global namespace whether we use the closure compiler or not
	/**
	* The boxplusx lightbox pop-up window instance.
	* Though typically used as a singleton, the interface permits instantiating multiple instances.
	* @constructor
	* @param {Object=} options
	*/
	function BoxPlusXDialog(options) {
		this.initialize(options);
	}
	window['BoxPlusXDialog'] = BoxPlusXDialog;

	/**
	* Record pushed into the browser history.
	* @constructor
	*/
	function BoxPlusXHistoryState() { }

	/** @type {string} */
	BoxPlusXHistoryState.prototype.agent = 'boxplusx';

	//
	// Private static functions
	//

	/**
	* Parses a query string into name/value pairs.
	* @param {string} querystring A string of "name=value" pairs, separated by "&".
	* @return {!Object<string>} An object where keys are parameter names, and value are parameter values.
	*/
	function fromQueryString(querystring) {
		let parameters = {};
		if (querystring.length > 1) {
			querystring.substr(1).split('&').forEach(function (keyvalue) {
				let index = keyvalue.indexOf('=');
				let key = index >= 0 ? keyvalue.substr(0, index) : keyvalue;
				let value = index >= 0 ? keyvalue.substr(index + 1) : '';
				parameters[decodeURIComponent(key)] = decodeURIComponent(value);
			});
		}
		return parameters;
	}

	/**
	* Parses a URL string into URL components.
	* @param {string} url A URL string.
	* @return {{
	*     protocol: string,
	*     host: string,
	*     hostname: string,
	*     port: string,
	*     pathname: string,
	*     search: string,
	*     queryparams: !Object<string>,
	*     hash: string,
	*     id: string,
	*     fragmentparams: !Object<string>
	* }}
	*/
	function parseURL(url) {
		let parser = /** @type {!HTMLAnchorElement} */ (document.createElement('a'));
		parser.href = url;
		const hashBangIndex = parser.hash.indexOf('!');

		return {
			protocol: parser.protocol,
			host: parser.host,
			hostname: parser.hostname,
			port: parser.port,
			pathname: parser.pathname,
			search: parser.search,  // starts with & unless empty
			queryparams: fromQueryString(parser.search),
			hash: parser.hash,  // starts with # unless empty
			id: parser.hash.substr(1, (hashBangIndex >= 0 ? hashBangIndex : parser.hash.length) - 1),
			/**
			* Fragment parameters. Recognizes any of the following syntax:
			* #key1=value1&key2=value2
			* #id!key1=value1&key2=value2
			*/
			fragmentparams: fromQueryString(parser.hash.substr(Math.max(0, hashBangIndex)))
		};
	}

	/**
	* Determines whether navigating to a URL would entail only a hash change.
	* @param {string} url A URL string.
	* @return {boolean} True if changing the location would trigger only an onhashchange event.
	*/
	function isHashChange(url) {
		let actual = parseURL(url);
		let expected = parseURL(location.href);  // parse location URL for compatibility with Internet Explorer

		return actual.protocol === expected.protocol
			&& actual.host === expected.host
			&& actual.pathname === expected.pathname  // compare path
			&& actual.search === expected.search;     // compare query string
	}

	/**
	* Builds a query string from an object.
	* @param {!Object<string,string>} parameters An object where keys are parameter names, and values are parameter values.
	* @return {string} A URL query string.
	*/
	function buildQuery(parameters) {
		return Object.keys(parameters).map(function (key) {
			return encodeURIComponent(key) + '=' + encodeURIComponent(parameters[key]);
		}).join('&');
	}

	/**
	* Checks if a location identifies an image.
	* @param {string} path A path or the path component of a URL.
	* @return {boolean} True if the path is likely to identify an image.
	*/
	function isImageFile(path) {
		return /\.(gif|jpe?g|png|svg)$/i.test(path);
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

	/**
	* Gets the value of an attribute with a fallback.
	* @param {!Element} elem The HTML element to inspect.
	* @param {string} attr The name of the attribute to search for.
	* @param {string} def The default value to return if the element does not have the given attribute.
	* @return {string} The value of the attribute if it exists, or the default value.
	*/
	function getAttributeOrDefault(elem, attr, def) {
		if (elem.hasAttribute(attr)) {
			return elem.getAttribute(attr);
		} else {
			return def;
		}
	}

	/**
	* Gets the value of an attribute
	* @param {!Element} elem The HTML element to inspect.
	* @param {string} attr The name of the attribute to search for.
	* @return {?string} The value of the attribute if it exists, or null.
	*/
	function getAttributeOrNull(elem, attr) {
		if (elem.hasAttribute(attr)) {
			return elem.getAttribute(attr);
		} else {
			return null;
		}
	}

	/**
	* Removes all children of an HTML element.
	* @param {!Node} elem The HTML element whose children to remove.
	*/
	function removeChildNodes(elem) {
		while (elem.hasChildNodes()) {
			elem.removeChild(elem.lastChild);
		}
	}

	/**
	* Determines whether the element is of the specified HTML element type.
	* @param {Element} elem The HTML element to test.
	* @param {string} type The tag name to test against.
	* @return {boolean}
	*/
	function hasElementType(elem, type) {
		return elem !== null && type === elem.tagName.toLowerCase();
	}

	/**
	* Determines whether an element is either of the listed HTML element types.
	* @param {Element} elem The HTML element to test.
	* @param {!Array<string>} types The tag names to test against.
	* @return {boolean}
	*/
	function hasElementEitherType(elem, types) {
		return elem !== null && types.indexOf(elem.tagName.toLowerCase()) >= 0;
	}

	/**
	* Creates an HTML element.
	* @param {string} tagName The HTML tag name such as "div" or "table".
	* @return {!HTMLElement} The newly created element, ready for injection into the DOM.
	*/
	function createHTMLElement(tagName) {
		return /** @type {!HTMLElement} */ (document.createElement(tagName));
	}

	/**
	* @param {!NodeList<!HTMLElement>|!Array<!HTMLElement>} nodeList
	* @return {!Array<!HTMLElement>}
	*/
	function convertToArray(nodeList) {
		return [].slice.call(nodeList);
	}

	/**
	* Sets the visibility of an HTML element.
	* @param {!Element} elem The HTML element to inspect.
	* @param {boolean} state True if the object is to be made visible.
	*/
	function setVisible(elem, state) {
		if (state) {
			elem.classList.remove('boxplusx-hidden');
		} else {
			elem.classList.add('boxplusx-hidden');
		}
	}

	/**
	* Determines the visibility of an HTML element.
	* @param {!Element} elem The HTML element to inspect.
	* @return {boolean} True if the object is visible.
	*/
	function isVisible(elem) {
		return !elem.classList.contains('boxplusx-hidden');
	}

	/**
	* Toggles a CSS class on an element.
	* @param {!Element} elem The HTML element to add the class to or remove the class from.
	* @param {string} cls The CSS class name.
	* @param {boolean} state If true, the class is added; if false, removed.
	*/
	function toggleClass(elem, cls, state) {
		let classList = elem.classList;
		if (state) {
			classList.add(cls);
		} else {
			classList.remove(cls);
		}
	}

	/**
	* Creates a HTML <div> element, acting as a building block for the dialog.
	* @param {string} name The class name the element gets.
	* @param {boolean=} hidden Whether the element is initially hidden.
	* @param {!Array<!Element>=} children Any children the element should have.
	* @return {!HTMLDivElement} The newly created element, ready for injection into the DOM.
	*/
	function createElement(name, hidden, children) {
		let elem = /** @type {!HTMLDivElement} */ (createHTMLElement('div'));
		elem.classList.add('boxplusx-' + name);
		if (hidden) {
			elem.classList.add('boxplusx-hidden');
		}
		if (children) {
			children.forEach(function (child) {
				elem.appendChild(child);
			});
		}
		return elem;
	}

	/**
	* Creates several HTML <div> elements, acting as building blocks for the dialog.
	* @param {!Array<string>} names
	* @return {!Array<!HTMLDivElement>}
	*/
	function createElements(names) {
		return names.map(function (name) {
			return createElement(name);
		});
	}

	/**
	* Retrieves image EXIF orientation of the camera relative to the scene.
	* @param {string} url The image URL.
	* @param {function(BoxPlusXOrientation):void} callback Invoked passing the EXIF orientation.
	*/
	function getImageOrientationFromURL(url, callback) {
		if (!/\.jpe?g$/i.test(url)) {
			callback(BoxPlusXOrientation.WrongImageType);  // wrong image format, no EXIF data present in image formats GIF or PNG
		} else {
			var xhr = new XMLHttpRequest();
			xhr.open('get', url);
			xhr.responseType = 'blob';
			xhr.onload = function () {
				getImageOrientationFromBlob(/** @type {!Blob} */ (xhr.response), callback);
			};
			xhr.onerror = function () {
				callback(BoxPlusXOrientation.NoInformation);
			}
			xhr.send();
		}
	}

	/**
	* Retrieves image EXIF orientation of the camera relative to the scene.
	* @param {!Blob} blob The image data as a binary large object.
	* @param {function(BoxPlusXOrientation):void} callback Invoked passing the EXIF orientation.
	*/
	function getImageOrientationFromBlob(blob, callback) {
		let reader = new FileReader();
		reader.onload = function () {
			let view = new DataView(/** @type {!ArrayBuffer} */ (reader.result));
			if (view.getUint16(0) != 0xFFD8) {
				callback(BoxPlusXOrientation.WrongImageType);  // wrong image format, not a JPEG image
				return;
			}

			let length = view.byteLength;
			let offset = 2;
			while (offset < length) {
				let marker = view.getUint16(offset);
				offset += 2;
				if (marker == 0xFFE1) {  // application marker APP1
					// EXIF header
					if (view.getUint32(offset += 2) != 0x45786966) {  // corresponds to string "Exif"
						callback(BoxPlusXOrientation.NoInformation);  // EXIF data absent
						return;
					}

					// TIFF header
					let little = view.getUint16(offset += 6) == 0x4949;  // check if "Intel" (little-endian) byte alignment is used
					offset += view.getUint32(offset + 4, little);  // last four bytes are offset to Image file directory (IFD)

					// IFD (Image file directory)
					let tags = view.getUint16(offset, little);
					offset += 2;
					for (let i = 0; i < tags; i++) {
						if (view.getUint16(offset + (i * 12), little) == 0x0112) {  // corresponds to IFD0 (main image) Orientation
							return callback(/** @type {BoxPlusXOrientation} */ (view.getUint16(offset + (i * 12) + 8, little)));
						}
					}
				} else if ((marker & 0xFF00) != 0xFF00) {  // not an application marker
					break;
				} else {
					offset += view.getUint16(offset);
				}
			}
			return callback(BoxPlusXOrientation.NoInformation);  // application marker APP1 not found
		};
		reader.readAsArrayBuffer(blob);
	}

	/**
	* Returns the title text for a content item.
	* @param {!Element} elem The HTML element whose short textual description to extract.
	* @return {string} A text suitable for a caption or the title of a browser tab or window.
	*/
	function getItemTitle(elem) {
		if (hasElementType(elem, 'a')) {
			const title = getAttributeOrNull(elem, 'data-title');
			if (null !== title) {
				return title;
			}

			// an HTML anchor element that nests an HTML image element with an "alt" attribute
			const image = /** @type {HTMLImageElement} */ (elem.querySelector('img'));
			if (image) {
				const alternateText = getAttributeOrNull(image, 'alt');
				if (null !== alternateText) {
					return alternateText;
				}
			}
		}

		return '';
	}

	/**
	* Returns the description text for a content item.
	* @param {!Element} elem The HTML element whose longer textual description to extract.
	* @return {string} A text suitable for a caption or description text.
	*/
	function getItemDescription(elem) {
		if (hasElementType(elem, 'a')) {
			const description = getAttributeOrNull(elem, 'data-summary');
			if (null !== description) {
				return description;
			}

			// an HTML anchor element with a "title" attribute
			const title = getAttributeOrNull(elem, 'title');
			if (null !== title) {
				return title;
			}
		}

		return '';
	}

	/**
	* Generates item properties from an HTML element collection.
	* @param {!NodeList<!HTMLElement>|!Array<!HTMLElement>} items
	* @return {!Array<!BoxPlusXItemProperties>}
	*/
	function elementsToProperties(items) {
		let elems = convertToArray(items);
		return elems.map(function (/** @type {!HTMLElement} */ elem) {
			let title = getItemTitle(elem);
			let description = getItemDescription(elem);
			if (title === description) {
				description = '';
			}

			let url = '';
			if (hasElementType(elem, 'a')) {
				let anchor = /** @type {HTMLAnchorElement} */ (elem);
				url = anchor.href;
			}

			// extract the HTML data attribute "download", which tells the engine where to look for the high-resolution
			// original, should the visitor choose to save a copy of the image to their computer
			let download = (elem.dataset && elem.dataset['download']) || '';

			/** @type {HTMLImageElement} */
			let image;
			let images = /** @type {!NodeList<!HTMLImageElement>} */ (elem.getElementsByTagName('img'));
			if (images.length > 0) {
				image = /** @type {!HTMLImageElement} */ (images[0]);
			}

			return {
				'url': url,
				'image': image,
				'title': title,
				'description': description,
				'download': download
			};
		});
	}

	//
	// Public instance functions
	//

	/**
	* Binds a set of elements to this dialog instance.
	* @param {!NodeList<!HTMLElement>|!Array<!HTMLElement>} items
	* @return {function(number)}
	*/
	BoxPlusXDialog.prototype.bind = function (items) {
		let self = this;
		let elems = convertToArray(items);
		let properties = elementsToProperties(elems);
		let openfun = function (/** @type {number} */ index) {
			self.open(properties, index);
		};
		elems.forEach(function (elem, index) {
			elem.addEventListener('click', function (event) {
				event.preventDefault();
				openfun(index);
			}, false);
		});
		return openfun;
	};

	/**
	* Initializes the layout and behavior of the pop-up dialog.
	* @param {Object=} options
	*/
	BoxPlusXDialog.prototype.initialize = function (options) {
		/** @type {!BoxPlusXOptions} */
		this.options = /** @type {!BoxPlusXOptions} */ (applyDefaults(options, boxplusDefaults));

		// builds the boxplusx pop-up window HTML structure, as if by injecting the following into the DOM:
		//
		// <div class="boxplusx-container boxplusx-hidden">
		//     <div class="boxplusx-dialog">
		//         <div class="boxplusx-wrapper boxplusx-hidden">
		//             <div class="boxplusx-wrapper">
		//                 <div class="boxplusx-wrapper">
		//                     <div class="boxplusx-viewport">
		//                         <div class="boxplusx-aspect"></div>
		//                         <div class="boxplusx-content"></div>
		//                         <div class="boxplusx-expander"></div>
		//                         <div class="boxplusx-previous"></div>
		//                         <div class="boxplusx-next"></div>
		//                     </div>
		//                     <div class="boxplusx-navigation">
		//                         <div class="boxplusx-navbar">
		//                             <div class="boxplusx-navitem">
		//                                 <div class="boxplusx-aspect"></div>
		//                                 <div class="boxplusx-navimage"></div>
		//                             </div>
		//                         </div>
		//                         <div class="boxplusx-rewind"></div>
		//                         <div class="boxplusx-forward"></div>
		//                     </div>
		//                 </div>
		//                 <div class="boxplusx-controls">
		//                     <div class="boxplusx-previous"></div>
		//                     <div class="boxplusx-next"></div>
		//                     <div class="boxplusx-close"></div>
		//                     <div class="boxplusx-start"></div>
		//                     <div class="boxplusx-stop"></div>
		//                     <div class="boxplusx-download"></div>
		//                     <div class="boxplusx-metadata"></div>
		//                 </div>
		//             </div>
		//             <div class="boxplusx-caption">
		//                 <div class="boxplusx-title"></div>
		//                 <div class="boxplusx-description"></div>
		//             </div>
		//         </div>
		//         <div class="boxplusx-progress boxplusx-hidden"></div>
		//     </div>
		// </div>

		// create elements
		let aspectHolder = createElement('aspect');
		let innerContainer = createElement('content');
		let expander = createElement('expander');
		let navigationBar = createElement('navbar');
		let navigationArea = createElement('navigation', false, [navigationBar].concat(createElements(['rewind','forward'])));
		let viewport = createElement('viewport', false, [aspectHolder,innerContainer,expander].concat(createElements(['previous','next'])));
		let controls = createElement('controls', false, createElements(['previous','next','close','start','stop','download','metadata']));
		let captionTitle = createElement('title');
		let captionDescription = createElement('description');
		let caption = createElement('caption', false, [captionTitle,captionDescription]);
		let innerWrapper = createElement('wrapper', false, [viewport,navigationArea]);
		let outerWrapper = createElement('wrapper', false, [innerWrapper,controls]);
		let contentWrapper = createElement('wrapper', true, [outerWrapper,caption]);
		let progressIndicator = createElement('progress', true);
		let dialog = createElement('dialog', false, [contentWrapper, progressIndicator]);
		let outerContainer = createElement('container', true, [dialog]);
		if (this.options.id) {
			outerContainer.id = this.options.id;
		}

		// arrange layout
		caption.classList.add('boxplusx-' + /** @type {string} */ (this.options['captions']));
		controls.classList.add('boxplusx-' + /** @type {string} */ (this.options['controls']));
		navigationArea.classList.add('boxplusx-' + /** @type {string} */ (this.options['navigation']));

		document.body.appendChild(outerContainer);

		/** @type {!HTMLDivElement} */
		this.outerContainer = outerContainer;
		/** @type {!HTMLDivElement} */
		this.dialog = dialog;
		/** @type {!HTMLDivElement} */
		this.contentWrapper = contentWrapper;
		/** @type {!HTMLDivElement} */
		this.viewport = viewport;
		/** @type {!HTMLDivElement} */
		this.caption = caption;
		/** @type {!HTMLDivElement} */
		this.captionTitle = captionTitle;
		/** @type {!HTMLDivElement} */
		this.captionDescription = captionDescription;
		/** @type {!HTMLDivElement} */
		this.aspectHolder = aspectHolder;
		/** @type {!HTMLDivElement} */
		this.innerContainer = innerContainer;
		/** @type {!HTMLDivElement} */
		this.expander = expander;
		/** @type {!HTMLDivElement} */
		this.navigationArea = navigationArea;
		/** @type {!HTMLDivElement} */
		this.navigationBar = navigationBar;
		/** @type {!HTMLDivElement} */
		this.progressIndicator = progressIndicator;

		/**
		* Information about elements, part of the same group, to be displayed in the pop-up window.
		* @type {!Array<!BoxPlusXItemProperties>}
		*/
		this.members = [];

		/**
		* A sequence of integers corresponding to item indexes previously seen since the pop-up window was opened
		* @type {!Array<number>}
		*/
		this.trail = [];

		/**
		* Timer to track when the slideshow delay expires.
		* @type {?number}
		*/
		this.timer = null;
		/**
		* True if a slideshow is currently activated.
		* @type {boolean}
		*/
		this.isSlideshowRunning = false;

		/**
		* Aspect behavior for the item currently displayed.
		* @type {!BoxPlusXDimensionBehavior}
		*/
		this.aspect = BoxPlusXDimensionBehavior.FixedAspectRatio;
		/**
		* Preferred width for the item currently displayed.
		* @type {number}
		*/
		this.preferredWidth = this.options.preferredWidth;
		/**
		* Preferred height for the item currently displayed.
		* @type {number}
		*/
		this.preferredHeight = this.options.preferredHeight;
		/**
		* Content type currently shown in the pop-up window.
		* @type {BoxPlusXContentType}
		*/
		this.contentType = BoxPlusXContentType.None;
		/**
		* Whether content size is reduced to fit available space.
		* @type {boolean}
		*/
		this.shrinkToFit = true;

		let self = this;

		this.outerContainer.addEventListener('click', function (event) {
			if (event.target === self.outerContainer) {
				self.close.call(self);
			}
		}, false);

		addEventToAllElements.call(this, 'click', {
			'previous': this.previous,
			'next': this.next,
			'close': this.close,
			'start': this.start,
			'stop': this.stop,
			'metadata': this.metadata,
			'download': this.download,
			'rewind': stopNavigationBar,
			'forward': stopNavigationBar
		});
		addEventToAllElements.call(this, 'mouseover', {
			'rewind': rewindNavigationBar,
			'forward': forwardNavigationBar
		});
		addEventToAllElements.call(this, 'mouseout', {
			'rewind': stopNavigationBar,
			'forward': stopNavigationBar
		});

		if (!this.options['contextmenu']) {
			dialog.addEventListener('contextmenu', function (/** @type {Event} */ event) {
				event.preventDefault();
			});
		}

		const writingsystem = /** @type {BoxPlusXWritingSystem} */ (this.options['dir']);
		this.outerContainer.dir = writingsystem;

		new BoxPlusXDraggable(viewport, innerContainer);

		function toggleShrinkToFit() {
			if (self.preferredWidth > self.viewport.clientWidth || self.preferredHeight > self.viewport.clientHeight) {
				self.shrinkToFit = !self.shrinkToFit;
				const index = getCurrentIndex.call(self);
				navigateToIndex.call(self, index);
			}
		}
		expander.addEventListener('click', toggleShrinkToFit);
		viewport.addEventListener('dblclick', toggleShrinkToFit);

		// prevent mouse wheel events from view area from propagating to document view
		innerContainer.addEventListener('mousewheel', function (/** @type {Event} */ event) {
			let wheelEvent = /** @type {WheelEvent} */ (event);
			let canScroll = window.getComputedStyle(innerContainer).overflowY != 'hidden';
			let maxScroll = innerContainer.scrollHeight - innerContainer.clientHeight;
			if (canScroll && maxScroll > 0) {
				let scrollTop = innerContainer.scrollTop;
				let deltaY = wheelEvent.deltaY;
				if ((scrollTop === maxScroll && deltaY > 0) || (scrollTop === 0 && deltaY < 0)) {
					wheelEvent.preventDefault();
				}
			}
		});

		// pressing a key
		window.addEventListener('keydown', function (/** @type {Event} */ event) {
			let keyboardEvent = /** @type {KeyboardEvent} */ (event);
			if (isVisible(self.outerContainer)) {
				// let form elements handle their own input
				if (hasElementEitherType(/** @type {Element} */ (keyboardEvent.target), ['input','select','textarea'])) {
					return;
				}

				const keys = [27,36,35];  // keys are [ESC, home, end]
				switch (writingsystem) {
					case 'ltr':
						keys.push(37,39);  // keys are [ESC, home, end, left arrow, right arrow]
						break;
					case 'rtl':
						keys.push(39, 37);  // keys are [ESC, home, end, right arrow, left arrow]
						break;
				}

				/** @type {number} */
				const keyindex = keys.indexOf(keyboardEvent.which || keyboardEvent.keyCode);
				if (keyindex >= 0) {
					/** @type function(this:BoxPlusXDialog) */
					let func = [self.close,self.first,self.last,self.previous,self.next][keyindex];
					func.call(self);  // call function with proper context for "this"
					keyboardEvent.preventDefault();
				}
			}
		}, false);

		// navigation by swipe
		/** @type {number} */
		let touchStartX;
		/** @type {number} */
		let lastTouch = 0;
		viewport.addEventListener('touchstart', function (/** @type {Event} */ event) {
			let touchEvent = /** @type {TouchEvent} */ (event);
			touchStartX = touchEvent.changedTouches[0].pageX;
		});
		viewport.addEventListener('touchend', function (/** @type {Event} */ event) {
			let touchEvent = /** @type {TouchEvent} */ (event);
			let now = new Date().getTime();
			let delta = now - lastTouch;
			if (delta > 0 && delta < 500) {  // double tap (two successive taps one shortly after the other)
				touchEvent.preventDefault();
			} else if (self.shrinkToFit) {  // single tap
				/** @type {number} */
				let x = touchEvent.changedTouches[0].pageX;
				if (x - touchStartX >= 50) {  // swipe to the right
					self.previous.call(self);
				} else if (touchStartX - x >= 50) {  // swipe to the left
					self.next.call(self);
				}
			}
			lastTouch = now;
		});

		// mobile-friendly forward and rewind for quick-access navigation bar
		navigationBar.addEventListener('touchstart', function (/** @type {Event} */ event) {
			let touchEvent = /** @type {TouchEvent} */ (event);
			touchStartX = touchEvent.changedTouches[0].pageX;
			stopNavigationBar.call(self);
		});
		navigationBar.addEventListener('touchend', function (/** @type {Event} */ event) {
			let touchEvent = /** @type {TouchEvent} */ (event);
			/** @type {number} */
			let x = touchEvent.changedTouches[0].pageX;
			if (x - touchStartX >= 50) {  // swipe to the right
				rewindNavigationBar.call(self);
			} else if (touchStartX - x >= 50) {  // swipe to the left
				forwardNavigationBar.call(self);
			}
		});

		// history (browser back and forward buttons)
		window.addEventListener('popstate', function (/** @type {Event} */ event) {
			if (isVisible(self.outerContainer)) {
				self.trail.pop();  // discard internal state that has been the active state

				if (self.trail.length > 0) {
					// re-inject popped artificial state into the history stack
					window.history.pushState({
						agent: 'boxplusx'
					}, '');

					const index = getCurrentIndex.call(self);
					self.trail.pop();  // pop off state that will be pushed as the active state shortly
					navigateToIndex.call(self, index);
				} else {
					hideWindow.call(self);
				}
			}
		}, false);

		// window resize
		window.addEventListener('resize', function (/** @type {Event} */ event){
			if (isVisible(self.outerContainer)) {
				setMaximumDialogSize.call(self);
				repositionNavigationBar.call(self);
				updateExpanderState.call(self);
			}
		});
	};

	/**
	* @param {!Array<!BoxPlusXItemProperties>} members
	* @param {number} index
	*/
	BoxPlusXDialog.prototype.open = function (members, index) {
		this.members = members;

		// populate quick-access navigation bar
		let self = this;
		const isNavigationVisible = members.length > 1 && this.options['navigation'] != BoxPlusXPosition.Hidden;
		setVisible(this.navigationArea, isNavigationVisible);
		if (isNavigationVisible) {
			members.forEach(function (member, i) {
				let navigationAspect = createElement('aspect');
				let navigationImage = createElement('navimage');
				let navigationItem = createElement('navitem', false, [navigationAspect,navigationImage]);
				let allowAction = true;
				navigationItem.addEventListener('touchstart', function () {
					if (isNavigationBarSliding.call(self)) {
						allowAction = false;
					}
				});
				navigationItem.addEventListener('click', function () {
					if (allowAction) {
						self.navigate.call(self, i);
					}
					allowAction = true;
				});

				let image = /** @type {HTMLImageElement} */ (member['image']);
				if (image) {
					let setNavigationImage = function () {
						let aspectStyle = navigationAspect.style;
						aspectStyle.setProperty('width', image.naturalWidth + 'px');
						aspectStyle.setProperty('padding-top', (100.0 * image.naturalHeight / image.naturalWidth) + '%');
						navigationImage.style.setProperty('background-image', 'url("' + image.src + '")');
					};
					if (image.src && image.complete) {  // make sure the image is available
						setNavigationImage();
					} else {
						// set aspect properties immediately when the image is loaded
						image.addEventListener('load', setNavigationImage);

						// trigger pre-loader service if registered by another script
						if (image['preloader']) {
							let preloader = image['preloader'];
							if (preloader['load']) {
								preloader['load']();
							}
						}
					}
				}

				navigationImage.innerText = (i + 1) + '';
				self.navigationBar.appendChild(navigationItem);
			});
		}

		this.show(index);
	};

	/**
	* @param {number} index
	*/
	BoxPlusXDialog.prototype.show = function (index) {
		this.trail = [];

		// push boxplusx-specific single artificial state to history stack
		if (window.history.state && (/** @type {!BoxPlusXHistoryState} */ (window.history.state)).agent === 'boxplusx') {
			window.history.replaceState(new BoxPlusXHistoryState(), '');
		} else {
			window.history.pushState(new BoxPlusXHistoryState(), '');
		}

		if (/** @type {boolean} */ (this.options['autostart']) && /** @type {number} */ (this.options['slideshow']) > 0) {
			this.isSlideshowRunning = true;
		}

		setVisible(this.outerContainer, true);
		setVisible(this.progressIndicator, true);
		this.navigate(index);
	};

	BoxPlusXDialog.prototype.close = function () {
		stopSlideshow.call(this);

		// call private method that does not manipulate history
		hideWindow.call(this);

		// clear history track
		this.trail = [];

		// discard artificial state on the history stack that corresponds to boxplusx
		window.history.go(-1);
	};

	/**
	* @param {number} index
	*/
	BoxPlusXDialog.prototype.navigate = function (index) {
		const current = getCurrentIndex.call(this);
		if (index != current) {
			navigateToIndex.call(this, index);
		}
	};

	BoxPlusXDialog.prototype.first = function () {
		this.navigate(0);
	};

	BoxPlusXDialog.prototype.previous = function () {
		const index = getCurrentIndex.call(this);
		if (index > 0) {
			this.navigate(index - 1);
		} else if (this.options['loop']) {
			this.last();
		}
	};

	BoxPlusXDialog.prototype.next = function () {
		const index = getCurrentIndex.call(this);
		if (index < this.members.length - 1) {
			this.navigate(index + 1);
		} else if (this.options['loop']) {
			this.first();
		}
	};

	BoxPlusXDialog.prototype.last = function () {
		this.navigate(this.members.length - 1);
	};

	BoxPlusXDialog.prototype.start = function () {
		if (this.options['slideshow'] > 0) {
			this.isSlideshowRunning = true;
			startSlideshow.call(this);
			updateControls.call(this);
		}
	};

	BoxPlusXDialog.prototype.stop = function () {
		if (this.options['slideshow'] > 0) {
			this.isSlideshowRunning = false;
			stopSlideshow.call(this);
			updateControls.call(this);
		}
	};

	BoxPlusXDialog.prototype.metadata = function () {
		let metadata = queryElement.call(this, 'detail');
		if (metadata) {
			setVisible(metadata, !isVisible(metadata));
		}
	};

	BoxPlusXDialog.prototype.download = function () {
		const index = getCurrentIndex.call(this);
		let anchor = /** @type {HTMLAnchorElement} */ (document.createElement('a'));
		anchor.href = this.members[index].download;
		document.body.appendChild(anchor);
		anchor.click();
		document.body.removeChild(anchor);
	};

	//
	// Private instance functions
	//

	/**
	* @param {string} identifier
	* @return {Element}
	* @this {BoxPlusXDialog}
	*/
	function queryElement(identifier) {
		return this.dialog.querySelector('.boxplusx-' + identifier);
	}

	/**
	* @param {string} identifier
	* @return {NodeList}
	* @this {BoxPlusXDialog}
	*/
	function queryAllElements(identifier) {
		return this.dialog.querySelectorAll('.boxplusx-' + identifier);
	}

	/**
	* @param {string} identifier
	* @param {function(!Element)} func
	* @this {BoxPlusXDialog}
	*/
	function applyAllElements(identifier, func) {
		// Microsoft Edge does not support function forEach (or iterator) on NodeList objects, i.e. the following does not work:
		// queryAllElements.call(this, identifier).forEach(func);

		let elems = queryAllElements.call(this, identifier);
		for (let i = 0; i < elems.length; ++i) {
			func(elems[i]);
		}
	}

	/**
	* @param {string} eventName
	* @param {!Object<string,function(this:BoxPlusXDialog)>} map
	* @this {BoxPlusXDialog}
	*/
	function addEventToAllElements(eventName, map) {
		let self = this;
		Object.keys(map).forEach(function (identifier) {
			applyAllElements.call(self, identifier, function (elem) {
				elem.addEventListener(eventName, map[identifier].bind(self), false);
			});
		});
	}

	/**
	* @param {BoxPlusXContentType} type
	* @return {boolean}
	*/
	function isContentInteractive(type) {
		switch (type) {
			case BoxPlusXContentType.Unavailable:
			case BoxPlusXContentType.Image:
				return false;
		}
		return true;
	}

	/**
	* Sets a content type that helps identify what is shown in the pop-up window viewport area.
	* @param {BoxPlusXContentType} contentType
	* @this {BoxPlusXDialog}
	*/
	function setContentType(contentType) {
		/**
		* @param {BoxPlusXContentType} type
		* @return {string}
		*/
		function getContentTypeString(type) {
			switch (type) {
				case BoxPlusXContentType.Unavailable:
					return 'unavailable';
				case BoxPlusXContentType.Image:
					return 'image';
				case BoxPlusXContentType.Video:
					return 'video';
				case BoxPlusXContentType.EmbeddedContent:
					return 'embed';
				case BoxPlusXContentType.DocumentFragment:
					return 'document';
				case BoxPlusXContentType.Frame:
					return 'frame';
				case BoxPlusXContentType.None:
				default:
					return 'none';
			}
		}

		let classList = this.innerContainer.classList;
		classList.remove('boxplusx-' + getContentTypeString(this.contentType));
		classList.remove('boxplusx-interactive');
		this.contentType = contentType;
		classList.add('boxplusx-' + getContentTypeString(contentType));
		if (isContentInteractive(contentType)) {
			classList.add('boxplusx-interactive');
		}
	}

	/**
	* @this {BoxPlusXDialog}
	*/
	function updateControls() {
		let self = this;
		let index = getCurrentIndex.call(this);
		let isFirstItem = index == 0;
		let members = this.members;
		let isLastItem = index >= members.length - 1;
		let loop = /** @type {boolean} */ (this.options['loop']) && !(isFirstItem && isLastItem);
		let slideshow = this.options['slideshow'] > 0;

		applyAllElements.call(this, 'previous', function (elem) {
			setVisible(elem, loop || !isFirstItem);
		});
		applyAllElements.call(this, 'next', function (elem) {
			setVisible(elem, loop || !isLastItem);
		});
		applyAllElements.call(this, 'start', function (elem) {
			setVisible(elem, slideshow && !self.isSlideshowRunning && !isLastItem);
		});
		applyAllElements.call(this, 'stop', function (elem) {
			setVisible(elem, slideshow && self.isSlideshowRunning);
		});
		applyAllElements.call(this, 'download', function (elem) {
			setVisible(elem, !!members[index].download);
		});
		applyAllElements.call(this, 'metadata', function (elem) {
			setVisible(elem, /** @type {boolean} */ (self.options['metadata']) && !!queryElement.call(self, 'detail'));
		});
	}

	/**
	* @this {BoxPlusXDialog}
	*/
	function updateExpanderState() {
		let isOversize = this.preferredWidth > this.viewport.clientWidth || this.preferredHeight > this.viewport.clientHeight;
		setVisible(this.expander, isOversize && !isContentInteractive(this.contentType));
		toggleClass(this.expander, 'boxplusx-collapse', !this.shrinkToFit);
		toggleClass(this.expander, 'boxplusx-expand', this.shrinkToFit);
	}

	/**
	* @this {BoxPlusXDialog}
	*/
	function hideWindow() {
		removeAnimationProperties.call(this);
		clearContent.call(this);
		setContentType.call(this, BoxPlusXContentType.None);
		removeChildNodes(this.navigationBar);
		setVisible(this.contentWrapper, false);
		setVisible(this.outerContainer, false);  // must come before manipulating history
	}

	/**
	* Gets the currently shown item.
	* @return {number} The zero-based index of the item currently displayed.
	* @this {BoxPlusXDialog}
	*/
	function getCurrentIndex() {
		return this.trail[this.trail.length - 1];
	}

	/**
	* Reveals the content to be displayed.
	* @this {BoxPlusXDialog}
	*/
	function showContent() {
		removeAnimationProperties.call(this);
		setVisible(this.progressIndicator, false);

		let index = getCurrentIndex.call(this);
		if (index >= this.members.length - 1) {
			this.isSlideshowRunning = false;
		}
		updateControls.call(this);

		setVisible(this.contentWrapper, true);

		// dialog must be visible to have valid offset values
		repositionNavigationBar.call(this);
		updateExpanderState.call(this);

		if (this.isSlideshowRunning) {
			startSlideshow.call(this);
		}
	}

	/**
	* Trigger dialog animation to morph into a size suitable for the next item.
	* @param {!BoxPlusXDimensionBehavior} aspect Specifies how the dialog should respond when resized.
	* @param {string=} originalWidth The original dialog CSS width to start with.
	* @param {string=} originalHeight The original dialog CSS height to start with.
	* @this {BoxPlusXDialog}
	*/
	function morphDialog(aspect, originalWidth, originalHeight) {
		this.aspect = aspect;

		// save current dialog dimensions and aspect ratio
		let computedStyle = window.getComputedStyle(this.dialog);
		const currentWidth = originalWidth || computedStyle.getPropertyValue('width');
		const currentHeight = originalHeight || computedStyle.getPropertyValue('height');
		removeAnimationProperties.call(this);

		// use temporarily exposed elements for calculations
		setVisible(this.contentWrapper, true);

		let viewportClassList = this.viewport.classList;
		viewportClassList.remove('boxplusx-fixedaspect');
		viewportClassList.remove('boxplusx-draggable');
		if (BoxPlusXDimensionBehavior.FixedSize === aspect || BoxPlusXDimensionBehavior.FixedAspectRatio === aspect) {
			// set new aspect ratio
			// if specified as a percentage, CSS padding is expressed in terms of container width (even for top
			// and bottom padding), which we utilize here to make item grow/shrink vertically as it grows/shrinks
			// horizontally
			let aspectStyle = this.aspectHolder.style;
			aspectStyle.setProperty('width', this.preferredWidth + 'px');
			aspectStyle.setProperty('padding-top', (100.0 * this.preferredHeight / this.preferredWidth) + '%');
			viewportClassList.add('boxplusx-fixedaspect');
		} else if (BoxPlusXDimensionBehavior.ResizableBestFit === aspect) {
			viewportClassList.add('boxplusx-draggable');
		} else if (BoxPlusXDimensionBehavior.Resizable === aspect) {
			let containerStyle = this.innerContainer.style;
			containerStyle.setProperty('width', this.preferredWidth + 'px');
			containerStyle.setProperty('max-height', this.preferredHeight + 'px');
		}

		setMaximumDialogSize.call(this);

		// get desired target size with all inner controls temporarily visible
		/** @type {string} */
		const desiredWidth = computedStyle.getPropertyValue('width');
		/** @type {string} */
		const desiredHeight = computedStyle.getPropertyValue('height');
		/** @type {string} */
		const desiredMaxWidth = computedStyle.getPropertyValue('max-width');

		// animation transition end function
		let self = this;
		let appliedStyle = this.dialog.style;
		let fn = function () {
			if (isVisible(self.outerContainer)) {
				appliedStyle.setProperty('max-width', desiredMaxWidth);
				showContent.call(self);
			}
		}

		if (currentWidth != desiredWidth || currentHeight != desiredHeight) {  // dialog animation required to fit new content size
			// hide elements after calculations have been made
			setVisible(this.contentWrapper, false);

			// reset previous dialog dimensions
			appliedStyle.removeProperty('max-width');
			appliedStyle.setProperty('width', currentWidth);
			appliedStyle.setProperty('height', currentHeight);

			this.dialog.classList.add('boxplusx-animation');

			// determine when event "transitionend" would be fired
			// helps thwart deadlock when event "transitionend" is never fired due to race condition
			const duration = Math.max.apply(null, computedStyle.getPropertyValue('transition-duration').split(',').map(function (item) {
				let value = parseFloat(item);
				if (/\ds$/.test(item)) {
					return 1000 * value;
				} else {
					return value;
				}
			}));
			window.setTimeout(fn, duration);
		} else {  // no dialog animation required, only swap content
			fn();
		}

		// start CSS transition by setting desired size for pop-up window as transition target
		appliedStyle.setProperty('width', desiredWidth);
		appliedStyle.setProperty('height', desiredHeight);
	}

	/**
	* Removes all element properties associated with dialog animation.
	* @this {BoxPlusXDialog}
	*/
	function removeAnimationProperties() {
		this.dialog.classList.remove('boxplusx-animation');

		// remove any explicit sizes applied for the sake of the CSS transition animation
		let appliedStyle = this.dialog.style;
		appliedStyle.removeProperty('width');
		appliedStyle.removeProperty('height');
	}

	/**
	* Uses the bisection algorithm to determine the dialog size.
	* @param {number} a Lower bound (percentage) value at which the dialog fits.
	* @param {number} b Upper bound (percentage) value at which the dialog does not fit.
	* @param {function(number)} applyFun Applies a value (e.g. sets content width or height).
	* @return {number} The (percentage) value at which the dialog fits exactly.
	* @this {BoxPlusXDialog}
	*/
	function bisectionSearch(a, b, applyFun) {
		let self = this;

		/**
		* Evaluates the dialog height at a particular value.
		* @param {number} value A parameter value to apply.
		* @return {number} The dialog height in pixels (including border and padding) when the value is applied.
		*/
		function evaluateFun(value) {
			applyFun(value);
			return self.dialog.offsetHeight;
		}

		const containerHeight = this.outerContainer.clientHeight;

		let dlgHeightB = evaluateFun(b);  // no extra horizontal constraints
		if (dlgHeightB <= containerHeight) {
			return b;  // nothing to do; pop-up window fits vertically
		}

		let dlgHeightA = evaluateFun(a);  // force dialog take its minimum size
		if (dlgHeightA >= containerHeight) {
			applyFun(b);  // reset constraints
			return b;  // nothing to do; pop-up window too large to fit even with most constraints
		}

		// use bisection method to find least restrictive horizontal constraint that still allows the pop-up window
		// to fit vertically
		for (let n = 1; n < 10; ++n) {  // use a maximum iteration count to avoid problems with slow convergence
			let c = ((a + b) / 2) | 0;  // cast to integer for improved performance
			let dlgHeightC = evaluateFun(c);

			if (dlgHeightC < containerHeight) {
				a = c;  // found a better lower bound
				dlgHeightA = dlgHeightC;
			} else {
				b = c;  // found a better upper bound
				dlgHeightB = dlgHeightC;
			}
		}

		// when the algorithm terminates, lower and upper bound are close; apply the lower bound as the value we seek
		applyFun(a);
		return a;
	}

	/**
	* Set maximum width for dialog so that it does not exceed viewport dimensions.
	* CSS property max-height: 100% is not respected by browsers in this context: the height of the containing
	* block is not specified explicitly (i.e., it depends on content height), and the element is not absolutely
	* positioned, therefore the percentage value is treated as none (to avoid infinite re-calculation loops in
	* layout); as a work-around, we set an upper limit on width instead.
	* @this {BoxPlusXDialog}
	*/
	function setMaximumDialogSize() {
		if (BoxPlusXDimensionBehavior.FixedAspectRatio === this.aspect) {
			// for fixed aspect ratio, we vary the maximum dialog width in terms of the width of the container element
			// (browser viewport), expressed as a percentage value
			let dialogStyle = this.dialog.style;
			bisectionSearch.call(this, 0, 1000, function (value) {
				dialogStyle.setProperty('max-width', (value / 10) + '%');
			})
		} else if (BoxPlusXDimensionBehavior.ResizableBestFit === this.aspect || BoxPlusXDimensionBehavior.Resizable === this.aspect) {
			// for dynamic aspect ratio, we vary the content holder element pixel height
			let containerStyle = this.innerContainer.style;
			containerStyle.removeProperty('max-height');
			let value = bisectionSearch.call(this, 0, window.innerHeight, function (value) {
				containerStyle.setProperty('height', value + 'px');
			})
			containerStyle.removeProperty('height');
			containerStyle.setProperty('max-height', Math.min(value, this.preferredHeight) + 'px');
		}
	}

	/**
	* Retrieves EXIF image orientation and other metadata.
	* @param {!HTMLImageElement} image The image from which to extract information.
	* @param {function(number, !Object=)} callback Invoked passing the EXIF orientation and metadata.
	* @this {BoxPlusXDialog}
	*/
	function getImageMetadata(image, callback) {
		let url = image.src;
		if (/^file:/.test(url)) {
			callback(-3);  // cross-origin requests are only supported for protocol schemes such as 'http' and 'https'
			return;
		}

		let EXIF = window['EXIF'];
		if (/** @type {boolean} */ (this.options['metadata']) && !!EXIF) {
			// use third-party plugin Exif.js to extract orientation and metadata, see <https://github.com/exif-js/exif-js>
			EXIF.getData(image, function() {
				let orientation = 0;
				let metadata;
				let m = Object.assign({}, /** @type {!Object} */ (image['iptcdata']), /** @type {!Object} */ (image['exifdata']));
				if (Object.keys(m).length > 0) {
					metadata = m;

					let o = /** @type {string|number|undefined} */ (m['Orientation']);
					if (o) {
						orientation = +o;  // coerce to number
					}
				}
				callback(orientation, metadata);
			});
		} else {
			// use simple built-in method to extract orientation
			getImageOrientationFromURL(url, function (orientation) {
				callback(orientation);
			});
		}
	}

	/**
	* Makes the specified item currently active.
	* @param {number} index The zero-based index of the item to be displayed.
	* @this {BoxPlusXDialog}
	*/
	function navigateToIndex(index) {
		let self = this;
		const member = this.members[index];
		this.trail.push(index);

		let computedStyle = window.getComputedStyle(this.dialog);
		/** @type {string} */
		const currentWidth = computedStyle.getPropertyValue('width');
		/** @type {string} */
		const currentHeight = computedStyle.getPropertyValue('height');

		stopSlideshow.call(this);
		setVisible(this.progressIndicator, true);

		// save caption text
		let title = /** @type {string} */ (member['title']);
		let description = /** @type {string} */ (member['description']);

		const href = /** @type {string} */ (member['url']);
		const urlparts = parseURL(href);
		const path = urlparts.pathname;
		/** @type {!Object<string,string>} */
		const parameters = Object.assign({}, urlparts.queryparams, urlparts.fragmentparams);

		this.preferredWidth = parseInt(parameters['width'], 10) || /** @type {number} */ (this.options['preferredWidth']);
		this.preferredHeight = parseInt(parameters['height'], 10) || /** @type {number} */ (this.options['preferredHeight']);

		if (isHashChange(href)) {
			const target = urlparts.id ? urlparts.id : parameters['target'];
			let elem = document.getElementById(target);
			if (elem) {
				let content = elem.cloneNode(true);
				replaceContent.call(this, content, title, description);
				setContentType.call(this, BoxPlusXContentType.DocumentFragment);
				morphDialog.call(this, BoxPlusXDimensionBehavior.Resizable, currentWidth, currentHeight);
			} else {
				displayUnavailable.call(this);
			}
		} else if (isImageFile(path)) {
			// download image in the background
			let image = /** @type {!HTMLImageElement} */ (document.createElement('img'));
			image.addEventListener('load', function (event) {
				// try extracting image EXIF orientation for photos
				getImageMetadata.call(self, image, function (orientation, metadata) {
					let container = document.createDocumentFragment();

					// set image
					let rotationContainer = createHTMLElement('div');
					let imageElement = createHTMLElement('div');

					if (orientation > 0) {
						imageElement.classList.add('boxplusx-orientation-' + orientation);
					}

					let imageElementStyle = imageElement.style;
					imageElementStyle.setProperty('background-image', 'url("' + image.src + '")');

					let dpr = self.options['useDevicePixelRatio'] ? (/** @type {number} */ (window['devicePixelRatio']) || 1) : 1;
					let h = Math.floor(image.naturalHeight / dpr);
					let w = Math.floor(image.naturalWidth / dpr);
					if (orientation >= 5 && orientation <= 8) {  // image rotated by 90 or 270 degrees
						self.preferredWidth = h;
						self.preferredHeight = w;

						// CSS transform does not affect bounding box for layout, enlarge/shrink CSS width/height
						// to accommodate for transformation results
						imageElementStyle.setProperty('width', (100 * w / h) + '%');
						imageElementStyle.setProperty('height', (100 * h / w) + '%');
					} else {  // image rotated by 0 or 180 degrees
						self.preferredWidth = w;
						self.preferredHeight = h;

						// necessary when we re-use existing container accommodating previous image
						imageElementStyle.removeProperty('width');
						imageElementStyle.removeProperty('height');
					}

					if (!self.shrinkToFit) {
						let rotationContainerStyle = rotationContainer.style;
						rotationContainerStyle.setProperty('width', self.preferredWidth + 'px');
						rotationContainerStyle.setProperty('height', self.preferredHeight + 'px');
					}

					rotationContainer.appendChild(imageElement);
					container.appendChild(rotationContainer);

					// get image metadata information
					if (metadata) {
						let textElement = createElement('detail', true);
						let table = createHTMLElement('table');
						let keys = Object.keys(metadata);
						let len = keys.length;
						keys.sort();
						for (let i = 0; i < len; ++i) {
							let key = keys[i];
							let row = createHTMLElement('tr');
							let header = createHTMLElement('td');
							header.innerText = key;
							let value = createHTMLElement('td');
							value.innerText = metadata[key];
							row.appendChild(header);
							row.appendChild(value);
							table.appendChild(row);
						}
						textElement.appendChild(table);
						container.appendChild(textElement);
					}

					replaceContent.call(self, container, title, description);
					self.caption.style.setProperty('max-width', self.preferredWidth + 'px');  // must come after replacing content to have any effect
					setContentType.call(self, BoxPlusXContentType.Image);

					// start dialog animation
					morphDialog.call(self, self.shrinkToFit ? BoxPlusXDimensionBehavior.FixedAspectRatio : BoxPlusXDimensionBehavior.ResizableBestFit, currentWidth, currentHeight);
				});
			}, false);
			image.addEventListener('error', displayUnavailable.bind(this), false);
			image.src = href;

			// pre-fetch next image (unless last is shown) to speed up slideshows and viewing images one after the other
			if (index < self.members.length - 1) {
				const nextmember = self.members[index + 1];
				const nexthref = /** @type {string} */ (nextmember['url']);
				const nexturlparts = parseURL(nexthref);
				if (isImageFile(nexturlparts.pathname)) {
					let nextimage = /** @type {!HTMLImageElement} */ (document.createElement('img'));
					nextimage.src = nexthref;
				}
			}
		} else if (/\.(mov|mpe?g|mp4|ogg|webm)$/i.test(path)) {  // supported by HTML5-native <video> tag
			let video = /** @type {!HTMLVideoElement} */ (document.createElement('video'));
			video.controls = true;
			video.addEventListener('loadedmetadata', function (event) {
				// set video
				replaceContent.call(self, video, title, description);
				setContentType.call(self, BoxPlusXContentType.Video);

				self.preferredWidth = video.videoWidth;
				self.preferredHeight = video.videoHeight;
				morphDialog.call(self, BoxPlusXDimensionBehavior.FixedAspectRatio, currentWidth, currentHeight);
			}, false);
			video.addEventListener('error', displayUnavailable.bind(this), false);
			video.src = href;
		} else if (/\.pdf$/.test(path)) {
			let embed = /** @type {!HTMLEmbedElement} */ (document.createElement('embed'));
			embed.src = href;
			embed.type = 'application/pdf';

			replaceContent.call(self, embed, title, description);
			setContentType.call(self, BoxPlusXContentType.EmbeddedContent);
			morphDialog.call(self, BoxPlusXDimensionBehavior.FixedAspectRatio, currentWidth, currentHeight);
		} else {
			// check for YouTube URLs
			let match = /^https?:\/\/(?:www\.)youtu(?:\.be|be\.com)\/(?:embed\/|watch\?v=|v\/|)([-_0-9A-Z]{11,})/i.exec(href);
			if (match !== null) {
				displayFrame.call(
					this,
					'https://www.youtube.com/embed/' + match[1] + '?' + buildQuery({ rel: '0', controls: '1', showinfo: '0' }),
					title, description
				);
				return;
			}

			// URL to unrecognized target (a plain URL to an external location)
			displayFrame.call(this, href, title, description);
		}
	}

	/**
	* Clears the content in the inner container.
	* This function clears all CSS properties set from script so they revert to their values specified
	* in the stylesheet file.
	* @this {BoxPlusXDialog}
	*/
	function clearContent() {
		// remove all HTML child elements
		removeChildNodes(this.innerContainer);

		let dialogStyle = this.dialog.style;
		let aspectStyle = this.aspectHolder.style;
		let containerStyle = this.innerContainer.style;

		// remove CSS properties that force the aspect ratio
		aspectStyle.removeProperty('padding-top');
		aspectStyle.removeProperty('width');

		// remove content and content styling
		containerStyle.removeProperty('width');  // preferred width

		// remove fit to window constraints
		dialogStyle.removeProperty('max-width');
		containerStyle.removeProperty('max-height');
	}

	/**
	* Replaces the content currently displayed in the pop-up window.
	* @param {!DocumentFragment|!Element} content HTML content to place in the viewport area.
	* @param {string} title The caption text title to associate with the item.
	* @param {string} description The caption text description to associate with the item.
	* @this {BoxPlusXDialog}
	*/
	function replaceContent(content, title, description) {
		clearContent.call(this);

		this.innerContainer.appendChild(content);
		this.caption.style.removeProperty('max-width');  // reset caption style
		this.captionTitle.innerHTML = title;
		this.captionDescription.innerHTML = description;
	}

	/**
	* @this {BoxPlusXDialog}
	*/
	function displayUnavailable() {
		// set unavailable image
		setContentType.call(this, BoxPlusXContentType.Unavailable);

		// start dialog animation
		morphDialog.call(this, BoxPlusXDimensionBehavior.FixedAspectRatio);
	}

	/**
	* Displays the contents of an external page in the pop-up window.
	* @param {string} src The URL to the source to be displayed.
	* @param {string} title The caption text title to associate with the item.
	* @param {string} description The caption text description to associate with the item.
	* @this {BoxPlusXDialog}
	*/
	function displayFrame(src, title, description) {
		let self = this;

		let frame = /** @type {!HTMLIFrameElement} */ (document.createElement('iframe'));
		frame.width = '' + this.preferredWidth;
		frame.height = '' + this.preferredHeight;
		frame.src = src;

		// HTML iframe must be added to the DOM in order for the 'load' event to be triggered
		replaceContent.call(this, frame, title, description);

		// must register 'load' event after adding to the DOM to avoid the event being triggered for blank document
		let hasFired = false;
		frame.addEventListener('load', function (event) {
			// make sure spurious 'load' events are ignored
			// (the third parameter to addEventListener called 'options' is not supported in all browsers)
			if (hasFired) {
				return;
			}
			hasFired = true;

			setContentType.call(self, BoxPlusXContentType.Frame);
			morphDialog.call(self, BoxPlusXDimensionBehavior.FixedSize);
		}, false);
	}

	/**
	* Restarts the slideshow timer.
	* @this {BoxPlusXDialog}
	*/
	function startSlideshow() {
		stopSlideshow.call(this);
		this.timer = window.setTimeout(this.next.bind(this), this.options['slideshow']);
	}

	/**
	* Stops the slideshow timer.
	* @this {BoxPlusXDialog}
	*/
	function stopSlideshow() {
		if (this.timer) {
			window.clearTimeout(this.timer);
			this.timer = null;
		}
	}

	/**
	* Returns the current offset of an element from the edge, taking into account text directionality.
	* @param {!HTMLElement} item
	* @return {number}
	* @this {BoxPlusXDialog}
	*/
	function getItemEdgeOffset(item) {
		const writingsystem = /** @type {BoxPlusXWritingSystem} */ (this.options['dir']);
		switch (writingsystem) {
			case 'rtl':
				const parentItem = /** @type {!HTMLElement} */ (item.offsetParent);
				return parentItem.offsetWidth - item.offsetWidth - item.offsetLeft;  // an implementation of function offsetRight
			case 'ltr':
			default:
				return item.offsetLeft;
		}
	}

	/**
	* Returns the maximum value for positioning the quick-access navigation bar.
	* Values in the range [-maximum; 0] are permitted as pixel length values for the CSS left property in order for
	* the navigation bar to remain in view.
	* @return {number}
	* @this {BoxPlusXDialog}
	*/
	function getNavigationRange() {
		return Math.max(this.navigationBar.offsetWidth - this.navigationArea.offsetWidth, 0);
	}

	/**
	* Returns the current navigation bar position, taking into account text directionality.
	* @return {number}
	* @this {BoxPlusXDialog}
	*/
	function getNavigationPosition() {
		// negate computed value because the property offsetLeft or offsetRight takes values in the range [-maximum; 0]
		return -getItemEdgeOffset.call(this, this.navigationBar);
	}

	/**
	* Starts moving the navigation bar towards the specified target position.
	* @param {number} targetPosition A nonnegative number, indicating target position.
	* @param {number} duration A nonnegative number, indicating number of milliseconds for the animation to take.
	* @this {BoxPlusXDialog}
	*/
	function slideNavigationBar(targetPosition, duration) {
		const rtl = /** @type {BoxPlusXWritingSystem} */ (this.options['dir']) == BoxPlusXWritingSystem.RightToLeft;
		let navigationStyle = this.navigationBar.style;
		navigationStyle.setProperty(rtl ? 'right' : 'left', (-targetPosition) + 'px');
		navigationStyle.setProperty('transition-duration', duration > 0 ? (5 * duration) + 'ms' : '');
	}

	/**
	* @return {boolean}
	* @this {BoxPlusXDialog}
	*/
	function isNavigationBarSliding() {
		return !!this.navigationBar.style.getPropertyValue('transition-duration');
	}

	/**
	* Re-position the navigation bar so that the active item is aligned with the left edge of the navigation area.
	* @this {BoxPlusXDialog}
	*/
	function repositionNavigationBar() {
		if (isVisible(this.navigationArea)) {
			// remove focus from navigation item corresponding to previously active item
			for (let k = 0; k < this.navigationBar.childNodes.length; ++k) {
				/** @type {HTMLElement} */ (this.navigationBar.childNodes[k]).classList.remove('boxplusx-current');
			}

			// set focus on navigation item corresponding to currently active item
			const index = getCurrentIndex.call(this);
			const maximum = getNavigationRange.call(this);  // the maximum permitted offset
			let item = /** @type {HTMLElement} */ (this.navigationBar.childNodes[index]);
			item.classList.add('boxplusx-current');

			// get the current scroll offset, which may possibly be out of view
			let scrollPosition = getNavigationPosition.call(this);
			const itemEdgeOffset = getItemEdgeOffset.call(this, item);

			// the last position to scroll forward to before the current item goes (partially) out of view
			let lastForwardScrollFit = Math.min(maximum, itemEdgeOffset);
			if (scrollPosition > lastForwardScrollFit) {
				scrollPosition = lastForwardScrollFit;
			}

			// the last position to scroll backward to before the current item goes (partially) out of view
			// subtract item width because items are left offset-aligned
			let lastBackwardScrollFit = Math.max(0, itemEdgeOffset - this.navigationArea.offsetWidth + item.offsetWidth);
			if (scrollPosition < lastBackwardScrollFit) {
				scrollPosition = lastBackwardScrollFit;
			}

			slideNavigationBar.call(this, scrollPosition, 0);  // temporarily disable any transition animation
		}
	}

	/**
	* @this {BoxPlusXDialog}
	*/
	function rewindNavigationBar() {
		const maximum = getNavigationRange.call(this);
		const current = maximum - getNavigationPosition.call(this);

		// set target position for navigation bar, reached via CSS transition animation
		// furthermost position for rewinding corresponds to the navigation bar pushed to the rightmost permitted
		// position (left offset value 0), set transition duration depending on how far we are from the furthermost
		// position to get a constant movement speed, regardless of what the current navigation bar position is
		slideNavigationBar.call(this, 0, maximum - current);
	}

	/**
	* @this {BoxPlusXDialog}
	*/
	function forwardNavigationBar() {
		const maximum = getNavigationRange.call(this);
		const current = getNavigationPosition.call(this);

		// set target position for navigation bar, reached via CSS transition animation
		// furthermost position for forwarding corresponds to the navigation bar pushed to the leftmost permitted
		// position (greatest absolute value), set transition duration depending on how far we are from the furthermost
		// position to get a constant movement speed, regardless of what the current navigation bar position is
		slideNavigationBar.call(this, maximum, maximum - current);
	}

	/**
	* @this {BoxPlusXDialog}
	*/
	function stopNavigationBar() {
		// stop CSS transition animation by forcing the current offset values returned by computed style
		slideNavigationBar.call(this, getNavigationPosition.call(this), 0);  // temporarily disable any transition animation
	}

	//
	// Examples
	//

	/**
	* Discovers boxplusx links on a web page.
	* boxplusx links are regular HTML <a> elements whose 'rel' attribute has a value with the pattern 'boxplusx-NNN'
	* where NNN is a unique name. All items that share the same unique name are organized into the same gallery. When
	* the user clicks an item that is part of a gallery, the item opens in the pop-up window and users can navigate
	* between this and other items in the gallery without closing the pop-up window.
	* @param {boolean} strict
	* @param {string=} activator
	* @param {Object=} options
	*/
	BoxPlusXDialog['discover'] = function (strict, activator, options) {
		activator = activator || 'boxplusx';

		/**
		* Discovers groups of pop-up window display items on a web page.
		* @param {!NodeList<!HTMLAnchorElement>} items A list of elements to inspect.
		* @return {!Object<string,!Array<!HTMLAnchorElement>>}
		*/
		function findGroups(items) {
			// make groups by name
			/** @type {!Object<string,!Array<!HTMLAnchorElement>>} */
			let groups = {};
			[].forEach.call(items, function (/** @type {!HTMLAnchorElement} */ item) {
				let identifier = item.getAttribute('rel');

				if (!Object.prototype.hasOwnProperty.call(groups, identifier)) {
					groups[identifier] = [];
				}

				groups[identifier].push(item);
			});

			return groups;
		}

		let dialog = new BoxPlusXDialog(options);

		// links with "rel" attribute that start with (but are not identical to) the activation string
		const groups = findGroups(/** @type {!NodeList<!HTMLAnchorElement>} */ (document.querySelectorAll('a[href][rel^=' + activator + ']:not([rel=' + activator + '])')));
		Object.keys(groups).forEach(function (identifier) {
			dialog.bind(groups[identifier]);
		});

		[].filter.call(/** @type {!NodeList<!HTMLAnchorElement>} */ (document.querySelectorAll('a[href][rel=' + activator + ']')), function (/** @type {!HTMLAnchorElement} */ item) {
			dialog.bind([item]);
		});

		if (!strict) {
			// individual links to images or video not part of a gallery
			let items = /** @type {!NodeList<!HTMLAnchorElement>} */ (document.querySelectorAll('a[href]:not([rel^=' + activator + '])'));
			[].filter.call(items, function (/** @type {!HTMLAnchorElement} */ item) {
				return /\.(gif|jpe?g|png|svg|mov|mpe?g|ogg|webm)$/i.test(item.pathname) && !item.target;
			}).forEach(function (/** @type {!HTMLAnchorElement} */ item) {
				dialog.bind([item]);
			});
		}
	};
})();
