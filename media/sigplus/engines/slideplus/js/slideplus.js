/**@license slideplus image rotator
* @author  Levente Hunyadi
* @version 1.5.0
* @remarks Copyright (C) 2011-2017 Levente Hunyadi
* @see     http://hunyadi.info.hu/projects/slideplus
**/

/*
* slideplus image rotator
* Copyright 2011-2017 Levente Hunyadi
*
* slideplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* slideplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with slideplus.  If not, see <http://www.gnu.org/licenses/>.
*/

/*{"compilation_level":"ADVANCED_OPTIMIZATIONS"}*/
'use strict';

/**
* Unit the slider advances in response to navigation buttons Previous or Next.
* @enum {string}
*/
const SlidePlusStep = {
	Single: 'single',
	Page: 'page'
};

/**
* Orientation of the sliding image strip.
* @enum {string}
*/
const SlidePlusOrientation = {
	Horizontal: 'horizontal',
	Vertical: 'vertical'
};

/**
* Layout of the sliding image strip.
* @enum {string}
*/
const SlidePlusLayout = {
	Natural: 'natural',
	Row: 'row',
	Column: 'column'
};

/**
* Position of caption with respect to container slot.
* @enum {string}
*/
const SlidePlusPosition = {
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
const SlidePlusWritingSystem = {
	LeftToRight: 'ltr',
	RightToLeft: 'rtl'
};

/**
* Options for the slideplus slider engine.
* The object has the following properties:
* + rows: The number of rows per slider page.
* + cols: The number of columns per slider page.
* + step: Unit the slider advances in response to navigation buttons Previous or Next ['single'|'page'].
* + loop: Whether the rotator loops around in a circular fashion [false|true].
* + random: Whether the rotator randomizes the order of images on startup [false|true].
* + orientation: Orientation of the sliding image strip ['horizontal'|'vertical'].
* + layout: Layout of the sliding image strip ['natural'|'row'|'column'].
* + links: Whether to show navigation links [true|false].
* + counter: Whether to show page counter [true|false].
* + protection: Whether the context menu appears when right-clicking an image [true|false].
* + delay: Time between successive automatic slide steps [ms], or 0 to disable automatic sliding.
* + dir: Text writing system, left-to-right or right-to-left ['ltr'|'rtl'].
*
* @typedef {{
*     rows: number,
*     cols: number,
*     step: SlidePlusStep,
*     loop: boolean,
*     random: boolean,
*     orientation: SlidePlusOrientation,
*     layout: SlidePlusLayout,
*     captions: SlidePlusPosition,
*     links: boolean,
*     counter: boolean,
*     protection: boolean,
*     delay: number,
*     dir: SlidePlusWritingSystem
* }}
*/
let SlidePlusOptions;

/** @type {SlidePlusOptions} */
const slidePlusDefaults = {
	'rows': 2,
	'cols': 3,
	'step': SlidePlusStep.Page,
	'loop': false,
	'random': false,
	'orientation': SlidePlusOrientation.Horizontal,
	'layout': SlidePlusLayout.Natural,
	'captions': SlidePlusPosition.Bottom,
	'links': true,
	'counter': true,
	'protection': false,
	'delay': 0,
	'dir': SlidePlusWritingSystem.LeftToRight,
	'lazyload': true
};

/**
* @param {!Element} elem
* @param {!Array<string>} attributes
*/
function moveAttributesToDataset(elem, attributes) {
	attributes.forEach(function (name) {
		if (elem.hasAttribute(name)) {
			const dataname = 'data-' + name;
			const value = elem.getAttribute(name);
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
		const dataname = 'data-' + name;
		if (elem.hasAttribute(dataname)) {
			const value = elem.getAttribute(dataname);
			elem.setAttribute(name, value);
			elem.removeAttribute(dataname);
		}
	});
}

/**
* Pre-loads one or more images in a container element.
* This function injects a user-friendly progress indicator, registers ready callbacks to be triggered when all images
* in the container have finished loading, and hides the pre-loader icon when all images are available.
*
* While the image is being loaded, the following HTML is injected into the container element:
* ```
* <div class="loadplus-progress"></div>
* ```
*
* @constructor
* @param {!HTMLElement} elem The container element in which image elements are to be pre-loaded.
*/
function Preloader(elem) {
	let self = this;

	/** @type {!HTMLElement} */
	this.host = elem;

	/** @type {!Array<!HTMLImageElement>} */
	let pending = [].filter.call(/** @type {!IArrayLike<!HTMLImageElement>} */ (elem.getElementsByTagName('img')), function (/** @type {!HTMLImageElement} */ image) {
		return !image.complete && image.src;
	});
	/** @type {!Array<!HTMLImageElement>} */
	this.pending = pending;

	if (pending.length < 1) {
		return;
	}

	// inject animated loader icon
	let icon = /** @type {!HTMLElement} */ (document.createElement('div'));
	icon.classList.add('loadplus-progress');
	elem.appendChild(icon);
	/** @type {!HTMLElement} */
	this.icon = icon;

	pending.forEach(function (image) {
		// hide image to prevent browser from displaying a partial image or broken image icon while data is being transferred
		image.classList.add('loadplus-hidden');

		// move native image attributes to data attributes to prevent the browser from fetching the image
		moveAttributesToDataset(image, ['src','srcset','sizes']);

		// add back-reference to pre-loader instance to allow other scripts to trigger the pre-loader mechanism
		image['preloader'] = self;
	});
}
Preloader.prototype.load = function () {
	let self = this;

	this.pending.forEach(function (image) {
		if (!image.src) {  // make sure the method is reentrant
			image.addEventListener('load', function () {
				image.classList.remove('loadplus-hidden');

				// remove image from list of images waiting to be loaded
				const index = self.pending.indexOf(image);
				if (index >= 0) {
					self.pending.splice(index, 1);
				}

				// check if there are further images in the container pending
				if (self.pending.length == 0) {
					// remove animated loader icon
					let parent = self.icon.parentNode;
					if (parent) {
						parent.removeChild(self.icon);
					}
				}
			});

			// re-apply previously cleared native image attributes by reading data attributes
			moveAttributesFromDataset(image, ['src','srcset','sizes']);

			// remove back-reference to pre-loader instance
			delete image['preloader'];
		}
	});
};
Preloader.prototype['load'] = Preloader.prototype.load;

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
* Fisher-Yates (aka Knuth) unbiased shuffle algorithm.
* @param {!Array<T>} array
* @return {!Array<T>}
* @template T
*/
function shuffle(array) {
	/** @type {number} */
	let currentIndex = array.length;

	while (0 !== currentIndex) {  // while there remain elements to shuffle
		// pick a remaining element
		/** @type {number} */
		let randomIndex = Math.floor(Math.random() * currentIndex);
		currentIndex -= 1;

		// swap element with the current element
		let temporaryValue = array[currentIndex];
		array[currentIndex] = array[randomIndex];
		array[randomIndex] = temporaryValue;
	}

	return array;
}

/**
* @param {!Element} element
*/
function resetPosition(element) {
	['left','right','top','bottom'].forEach(function (/** @type {string} */ dir) {
		element.style.removeProperty(dir);
	});
}

/**
* Removes all elements in a collection from the DOM tree.
* @param {Array<!Element>} items The items to remove.
*/
function removeElementsFromDOM(items) {
	if (items) {
		items.forEach(function (item) {
			let parent = item.parentNode;
			if (parent) {
				parent.removeChild(item);
			}
		});
	}
}

/**
* Clones all items in a collection.
* @param {!Array<!Element>} items An array of elements to duplicate.
* @return {!Array<!Element>} An array of duplicated elements.
*/
function cloneElements(items) {
	return items.map(function (item) {
		return item.cloneNode(true);
	});
}

function generateAnimationStyleDeclarations() {
	/**
	* @param {string} dir
	* @param {number} x
	* @param {number} y
	* @param {number} frac
	* @return {string}
	*/
	function generateKeyFrame(dir, x, y, frac) {
		return '@keyframes slideplus-push-' + dir + '-' + frac + '{' +
			'0%{transform:translate(0,0);}' +
			'100%{transform:translate(' + (100 * x / frac) + '%,' + (100 * y / frac) + '%);}' +
		'}';
	}

	/**
	* @param {number} frac
	* @return {!Array<string>}
	*/
	function generateKeyFrames(frac) {
		return [
			generateKeyFrame('left',  -1,  0, frac),
			generateKeyFrame('right',  1,  0, frac),
			generateKeyFrame('top',    0, -1, frac),
			generateKeyFrame('bottom', 0,  1, frac)
		];
	}

	/** @type {!Array<string>} */
	let keyframes = [];
	for (let k = 1; k < 10; ++k) {
		[].push.apply(keyframes, generateKeyFrames(k));
	}
	let rules = keyframes.join('');

	let stylesheet = document.createElement('link');
	stylesheet.rel = 'stylesheet';
	stylesheet.type = 'text/css';
	stylesheet.href = 'data:text/css;charset=' + (/** @type {?string} */ (document['characterSet']) || 'utf-8') + ',' + encodeURIComponent(rules);
	document.getElementsByTagName('head')[0].appendChild(stylesheet);
}
generateAnimationStyleDeclarations();

/**
* @constructor
* @param {!Element} holder
* @param {!Element} caption
*/
function SlidePlusSliderItem(holder, caption) {
	/**
	* Element holding an image or arbitrary HTML data.
	* @type {!Element}
	*/
	this.holder = holder;
	/**
	* Element with the caption text assigned to the image or HTML data.
	* @type {!Element}
	*/
	this.caption = caption;
}

/**
* @constructor
* @param {Element=} elem The placeholder element to replace with the slider.
* @param {SlidePlusOptions=} options Settings that customize the appearance and behavior of the slider.
* @param {function(!Element=):string=} titleFunc A function that extracts the caption HTML string corresponding to an element to be shown.
*/
function SlidePlusSlider(elem, options, titleFunc) {
	let self = this;

	// element existence test to ensure element is within DOM, some content management
	// systems may call the script even if the associated content is not on the page,
	// which is the case e.g. with Joomla category list layout or multi-page layout
	if (!elem) {
		return;
	}

	let titleFn = titleFunc || function () { return null; };

	// extracts the contents of a <noscript> element
	let host = elem;
	if (elem.tagName.toLowerCase() === 'noscript') {
		host = document.createElement('div');
		host.innerHTML = /** @type {string} */ (elem['innerText']);  // <noscript> elements are not parsed when javascript is enabled
	}

	/** @type {!SlidePlusOptions} */
	this.options = /** @type {!SlidePlusOptions} */ (applyDefaults(options, slidePlusDefaults));
	/** @type {number} */
	this.current = 0;

	/** @type {!Array<!HTMLElement>} */
	let listnodes = [].slice.call(/** @type {!IArrayLike<!HTMLElement>} */ (host.getElementsByTagName('li')));

	// randomize order of elements in the list
	if (this.options['random']) {
		listnodes = shuffle(listnodes);
	}

	// save captions for items
	/** @type {!Array<?string>} */
	this.captions = listnodes.map(titleFn);

	// unwrap items from <li> parent, keeping attached event handlers
	/**
	* List of DOM elements the sliding viewpane can be populated with.
	* @type {!Array<!HTMLElement>}
	*/
	let items = listnodes.map(function (/** @type {!HTMLElement} */ listitem) {
		let container = /** @type {!HTMLElement} */ (document.createElement('div'));
		for (let child = listitem.firstChild; child; child = listitem.firstChild) {
			container.appendChild(child);
		}
		return container;
	});
	/** @type {!Array<!HTMLElement>} */
	this.items = items;

	/** @type {number} */
	let rows = /** @type {number} */ (this.options['rows']);
	/** @type {number} */
	let cols = /** @type {number} */ (this.options['cols']);
	/** @type {boolean} */
	let loop = /** @type {boolean} */ (this.options['loop']);

	// ensure parameters are consistent with one another
	rows = Math.max(1, rows);
	cols = Math.max(1, cols);
	if (items.length <= rows * cols) {  // disable looping if not meaningful
		loop = false;
	}
	this.options['rows'] = rows;
	this.options['cols'] = cols;
	this.options['loop'] = loop;

	const orientation = /** @type {SlidePlusOrientation} */ (this.options['orientation']);
	const writingsystem = /** @type {SlidePlusWritingSystem} */ (self.options['dir']);

	// clone items to handle the case when the current and the successive viewport area display identical indices simultaneously
	/** @type {!Array<!Element>} */
	let clones = [];
	if (items.length < 2 * rows * cols) {
		// insufficient items to populate all slots in current and successive viewport
		while (clones.length < 2 * rows * cols) {
			clones = clones.concat(cloneElements(items));
		}
	} else if (loop) {
		// navigating to first/last page when some items from first/last page are currently visible
		clones = cloneElements(items.slice(0, rows*cols));  // append first rows*cols elements
		clones.length = items.length - rows*cols;  // skip all elements but last rows*cols
		clones = clones.concat(items.slice(-rows*cols));  // append last rows*cols elements
	}
	/** @type {!Array<!Element>} */
	this.clones = clones;

	// initiate pre-loading images after clones have been made
	if (options['lazyload']) {
		/** @type {!Array<!Preloader>} */
		this.preloaders = items.map(function (item) {
			return new Preloader(item);
		});
	}

	/**
	* @param {!Array<!SlidePlusSliderItem>} itemlist
	* @return {!HTMLElement}
	*/
	function createStripe(itemlist) {
		let grid = /** @type {!HTMLElement} */ (document.createElement('div'));
		grid.classList.add('slideplus-stripe');
		for (let i = 0; i < rows; ++i) {
			let gridrow = document.createElement('div');
			for (let j = 0; j < cols; ++j) {
				let griditem = document.createElement('div');
				griditem.classList.add('slideplus-slot');
				griditem.classList.add('slideplus-' + /** @type {SlidePlusPosition} */ (self.options['captions']));

				let aspect = document.createElement('div');
				aspect.classList.add('slideplus-aspect');
				griditem.appendChild(aspect);

				let contentholder = document.createElement('div');
				contentholder.classList.add('slideplus-content');
				griditem.appendChild(contentholder);

				let caption = document.createElement('div');
				caption.classList.add('slideplus-caption');

				gridrow.appendChild(griditem);

				itemlist.push(new SlidePlusSliderItem(contentholder, caption));
			}
			grid.appendChild(gridrow);
		}
		return grid;
	}

	/**
	* Tabular (semantically two-dimensional) array of items the sliding viewpane is currently populated with.
	* @type {!Array<!SlidePlusSliderItem>}
	*/
	let currentitems = [];
	/** @type {!Array<!SlidePlusSliderItem>} */
	this.currentitems = currentitems;
	let currentgrid = createStripe(currentitems);
	currentgrid.addEventListener('animationend', function () {
		self._layout();
	});
	/** @type {!HTMLElement} */
	this.currentgrid = currentgrid;

	/**
	* Tabular (semantically two-dimensional) array of items the upcoming sliding viewpane is to be populated with.
	* @type {!Array<!SlidePlusSliderItem>}
	*/
	let successoritems = [];
	/** @type {!Array<!SlidePlusSliderItem>} */
	this.successoritems = successoritems;
	let successorgrid = createStripe(successoritems);
	successorgrid.classList.add('slideplus-successor');
	successorgrid.classList.add('slideplus-hidden');
	/** @type {!HTMLElement} */
	this.successorgrid = successorgrid;

	let viewport = document.createElement('div');
	viewport.classList.add('slideplus-viewport');
	viewport.classList.add('slideplus-' + orientation);
	viewport.appendChild(currentgrid);
	viewport.appendChild(successorgrid);

	// navigation by swipe
	/** @type {number} */
	let touchStartX;
	/** @type {number} */
	let touchStartY;
	viewport.addEventListener('touchstart', function (/** @type {Event} */ event) {
		let touchEvent = /** @type {TouchEvent} */ (event);
		let touch = touchEvent.changedTouches[0];
		touchStartX = touch.pageX;
		touchStartY = touch.pageY;
	});
	viewport.addEventListener('touchend', function (/** @type {Event} */ event) {
		let touchEvent = /** @type {TouchEvent} */ (event);

		/**
		* Interpreting touch event locations, navigates to the previous or next set items.
		* @param {number} oldpos
		* @param {number} newpos
		*/
		function navigateOnSwipe(oldpos, newpos) {
			if (newpos - oldpos >= 50) {  // swipe to the right or bottom
				self.previous.call(self);
			} else if (oldpos - newpos >= 50) {  // swipe to the left or top
				self.next.call(self);
			}
		}

		let touch = touchEvent.changedTouches[0];
		if (SlidePlusOrientation.Horizontal === orientation) {  // moves from right to left, or left to right
			navigateOnSwipe(touchStartX, touch.pageX);
		} else if (SlidePlusOrientation.Vertical === orientation) {  // moves upwards or downwards
			navigateOnSwipe(touchStartY, touch.pageY);
		}
	});
	viewport.addEventListener('mouseenter', function (/** @type {Event} */ event) {
		self._stopTimer();
	});
	viewport.addEventListener('mouseleave', function (/** @type {Event} */ event) {
		self._startTimer();
	});

	// create navigation control buttons
	let previousbutton = document.createElement('div');
	previousbutton.classList.add('slideplus-previous');
	previousbutton.classList.add('slideplus-button');
	previousbutton.addEventListener('click', function (event) {
		event.preventDefault();
		self.previous();
	});
	viewport.appendChild(previousbutton);

	let nextbutton = document.createElement('div');
	nextbutton.classList.add('slideplus-next');
	nextbutton.classList.add('slideplus-button');
	nextbutton.addEventListener('click', function (/** @type {Event} */ event) {
		event.preventDefault();
		self.next();
	});
	viewport.appendChild(nextbutton);

	// suppress context menu and drag-and-drop
	if (this.options['protection']) {
		document.addEventListener('contextmenu', function (/** @type {Event} */ event) {  // prevent right-click on image
			let mouseEvent = /** @type {MouseEvent} */ (event);
			if (viewport.contains(/** @type {Node} */ (mouseEvent.target))) {
				mouseEvent.preventDefault();
			}
		});
		document.addEventListener('dragstart', function (/** @type {Event} */  event) {  // prevent drag-and-drop of image
			let dragEvent = /** @type {DragEvent} */ (event);
			if (viewport.contains(/** @type {Node} */ (dragEvent.target))) {
				dragEvent.preventDefault();
			}
		});
	}

	/**
	* @param {SlidePlusPosition} position
	* @return {Element}
	*/
	function createNavigationBar(position) {
		let navigationbar = document.createElement('div');
		navigationbar.classList.add('slideplus-navigation');
		navigationbar.dir = writingsystem;

		let paging = document.createElement('span');
		paging.classList.add('slideplus-paging');

		/**
		* @param {!Element} target
		* @param {string} cls
		* @param {string} content
		* @param {function():void} action
		*/
		function createLink(target, cls, content, action) {
			let link = document.createElement('a');
			link.href = '#';
			link.classList.add(cls);
			link.innerHTML = '<span>' + content + '</span>';
			link.addEventListener('click', function (event) {
				event.preventDefault();
				action();
			});
			target.appendChild(link);
			target.appendChild(document.createTextNode(' '));
		}

		/**
		* @param {string} type
		* @param {function():void} action
		*/
		function createNavigationLink(type, action) {
			createLink(navigationbar, 'slideplus-' + type, '', action);
		}

		/**
		* @param {number} index
		* @param {number} pagenumber
		*/
		function createPagingLink(index, pagenumber) {
			createLink(paging, 'slideplus-pager', '' + pagenumber, function () {
				if (index != self.current) {
					self._jump(index);
				}
			});
		}

		// populate navigation bar
		createNavigationLink('first', self.first.bind(self));
		createNavigationLink('previous', self.previous.bind(self));
		navigationbar.appendChild(paging);
		createNavigationLink('next', self.next.bind(self));
		createNavigationLink('last', self.last.bind(self));
		navigationbar.classList.add('slideplus-' + position);

		// populate paging area
		const len = self.items.length;
		const offset = self._getOffset();
		for (let i = 0, n = 1; i < len; i += offset, ++n) {
			createPagingLink(i, n);
		}

		return navigationbar;
	}

	let topnavigation = createNavigationBar(SlidePlusPosition.Top);
	let bottomnavigation = createNavigationBar(SlidePlusPosition.Bottom);

	/**
	* DOM Element that encapsules items, navigation controls, etc.
	* @type {!Element}
	*/
	let gallery = document.createElement('div');
	this.gallery = gallery;
	gallery.classList.add('slideplus-container');
	gallery.dir = writingsystem;

	const hasMultiplePages = items.length > rows * cols;
	if (hasMultiplePages) {
		gallery.appendChild(topnavigation);
	}
	gallery.appendChild(viewport);
	if (hasMultiplePages) {
		gallery.appendChild(bottomnavigation);
	}

	elem.parentNode.replaceChild(gallery, elem);

	this._updateNavigationControls();
	this._layout();

	const delay = /** @type {number} */ (this.options['delay']);
	if (delay > 0) {
		self._startTimer();
	}
}
/**
* @param {number} index A (possibly out-of-range) index.
* @return {boolean}
*/
SlidePlusSlider.prototype._inRange = function (index) {
	return index >= 0 && index < this.items.length;
}
/**
* The number of items the slider advances when navigating to the previous or next state.
* Returns (rows*cols) representing the entire viewport when the slide step is a full page.
* Returns rows or cols, whichever is appropriate, when the slider advances by a single column or row on each step.
* @return {number} An index offset.
*/
SlidePlusSlider.prototype._getOffset = function () {
	const rows = /** @type {number} */ (this.options['rows']);
	const cols = /** @type {number} */ (this.options['cols']);
	const orientation = /** @type {SlidePlusOrientation} */ (this.options['orientation']);
	const step = /** @type {SlidePlusStep} */ (this.options['step']);

	let offset = 0;
	if (SlidePlusOrientation.Horizontal === orientation) {  // moves from right to left, or left to right
		if (SlidePlusStep.Single === step) {  // advances by a single column
			offset = rows;
		} else {
			offset = rows * cols;
		}
	} else if (SlidePlusOrientation.Vertical === orientation) {  // moves upwards or downwards
		if (SlidePlusStep.Single === step) {  // advances by a single row
			offset = cols;
		} else {
			offset = rows * cols;
		}
	}
	return offset;
}
/**
* Applies an action to all control elements that match a selector.
* @param {string} selector
* @param {function(!HTMLElement):void} action
*/
SlidePlusSlider.prototype._applyAll = function (selector, action) {
	[].forEach.call(/** @type {!IArrayLike<!HTMLElement>} */ (this.gallery.querySelectorAll(selector)), function (/** @type {!HTMLElement} */ item) {
		action(item);
	});
};
/**
* Hides all control elements that match a selector.
* @param {string} selector
*/
SlidePlusSlider.prototype._hideAll = function (selector) {
	this._applyAll(selector, function (item) {
		item.classList.add('slideplus-hidden');
	});
};
/**
* Shows all control elements that match a selector.
* @param {string} selector
*/
SlidePlusSlider.prototype._showAll = function (selector) {
	this._applyAll(selector, function (item) {
		item.classList.remove('slideplus-hidden');
	});
};
/**
* Updates visibility for navigation control buttons.
*/
SlidePlusSlider.prototype._updateNavigationControls = function () {
	const len = this.items.length;
	const rows = /** @type {number} */ (this.options['rows']);
	const cols = /** @type {number} */ (this.options['cols']);
	const hasMultiplePages = len > rows * cols;
	const loop = /** @type {boolean} */ (this.options['loop']);
	const offset = this._getOffset();
	if (!loop || len < 2) {
		// cannot advance past first image or there are insufficient images
		if (hasMultiplePages && this.current > 0) {
			this._showAll('.slideplus-first');
			this._showAll('.slideplus-previous');
		} else {
			this._hideAll('.slideplus-first');
			this._hideAll('.slideplus-previous');
		}

		// cannot advance past last image or there are insufficient images
		if (hasMultiplePages && this._inRange(this.current + offset)) {
			this._showAll('.slideplus-next');
			this._showAll('.slideplus-last');
		} else {
			this._hideAll('.slideplus-next');
			this._hideAll('.slideplus-last');
		}
	}

	// highlight current item in pager control
	this._applyAll('.slideplus-pager', function (/** @type {!HTMLElement} */ pager) {
		pager.classList.remove('slideplus-current');
	});
	const pageindex = Math.floor(this.current / offset);
	[].forEach.call(/** @type {!IArrayLike<!HTMLElement>} */ (this.gallery.querySelectorAll('.slideplus-paging')), function (/** @type {!HTMLElement} */ item) {
		let pagers = item.querySelectorAll('.slideplus-pager');
		pagers[pageindex].classList.add('slideplus-current');
	});
};
/**
* Circular indexing.
* @param {number} index Index, possibly out of the range of indexable elements.
* @return {number} An index within the range of indexable elements.
*/
SlidePlusSlider.prototype._at = function (index) {
	const len = this.items.length;
	return (index % len + len) % len;
};
/**
* @param {!Array<!SlidePlusSliderItem>} target The container in which to arrange elements.
* @param {number} index An index with circular indexing semantics.
*/
SlidePlusSlider.prototype._arrange = function (target, index) {
	let layout = /** @type {SlidePlusLayout} */ (this.options['layout']);
	if (SlidePlusLayout.Natural === layout) {
		const orientation = /** @type {SlidePlusOrientation} */ (this.options['orientation']);
		if (SlidePlusOrientation.Horizontal === orientation) {
			layout = SlidePlusLayout.Column;
		} else if (SlidePlusOrientation.Vertical === orientation) {
			layout = SlidePlusLayout.Row;
		}
	}

	const rows = /** @type {number} */ (this.options['rows']);
	const cols = /** @type {number} */ (this.options['cols']);
	const loop = /** @type {boolean} */ (this.options['loop']);

	// set increments to advance with to support row-major and column-major traversal in same procedural loop
	let majorstride = 0, minorstride = 0;
	if (SlidePlusLayout.Row === layout) {
		majorstride = cols;
		minorstride = 1;
	} else if (SlidePlusLayout.Column === layout) {
		majorstride = 1;
		minorstride = rows;
	}

	// traverse items in row-major or column-major order
	// use i and j for checking limits, m and n for indexing
	for (let i = 0, m = index; i < rows; ++i, m += majorstride) {
		for (let j = 0, n = m; j < cols; ++j, n += minorstride) {
			const k = this._at(n);
			let item = this.items[k];

			// make sure we do not re-assign an item already in a designated slot
			/** @type {Element} */
			let assignable = null;
			if (item.parentNode) {  // already assigned to a slot
				// use a clone to fill slot if wrap-around is enabled (clone will be substituted with original when navigation ends)
				if (loop) {
					// iterate until we find an unassigned clone candidate
					for (let l = k; l < this.clones.length; l += this.items.length) {
						assignable = this.clones[l];

						// check if clone is already assigned (e.g. a 2x2 slider shows images #1, #2, #3 and #1)
						if (!assignable.parentNode) {
							break;  // a good candidate
						}
					}
				}
			} else {
				// leave slot empty unless wrap-around is enabled or slot in range of available item count
				if (loop || this._inRange(n)) {
					assignable = item;
				}
			}

			let targetitem = target[i*cols + j];

			// remove existing caption
			let captionholder = targetitem.caption;
			if (captionholder.parentNode) {
				captionholder.parentNode.removeChild(captionholder);
			}

			// assign new element to slot
			if (assignable) {
				let contentholder = targetitem.holder;
				contentholder.appendChild(assignable);

				// update caption
				let caption = this.captions[k];
				captionholder.innerHTML = caption || '';
				if (caption) {
					let position = /** @type {SlidePlusPosition} */ (this.options['captions']);
					let host = SlidePlusPosition.Top === position || SlidePlusPosition.Bottom === position ? contentholder.firstChild : contentholder.parentNode;
					host.appendChild(captionholder);
				}
			}
		}
	}
};
SlidePlusSlider.prototype._layout = function () {
	// stop any pending animations and reset animation state
	this.currentgrid.style.removeProperty('animation-name');
	this.successorgrid.style.removeProperty('animation-name');
	this.successorgrid.classList.add('slideplus-hidden');

	// remove all items from their current position
	removeElementsFromDOM(this.items);
	removeElementsFromDOM(this.clones);

	// arrange currently visible items in the viewport window (to be moved out on navigation)
	this._arrange(this.currentitems, this.current);

	const rows = /** @type {number} */ (this.options['rows']);
	const cols = /** @type {number} */ (this.options['cols']);
	const loop = /** @type {boolean} */ (this.options['loop']);
	const current = this.current;
	let start = current - rows*cols;
	let end = current + 2*rows*cols;
	if (!loop) {
		start = Math.max(0, start);  // cap at first image
		end = Math.min(this.items.length, end);  // cap at last image
	}
	if (this.preloaders) {
		for (let k = start; k < end; ++k) {
			this.preloaders[this._at(k)].load();
		}
	}

	this._restartTimer();
};
/**
* Moves the slider to the next set of elements.
* To be used when navigating to a neighboring set of elements.
* @param {number} direction 1 to more forward, -1 to move backward.
* @param {string} horzdir Assuming horizontal orientation, the direction towards which the slider moves.
* @param {string} vertdir Assuming vertical orientation, the direction towards which the slider moves.
*/
SlidePlusSlider.prototype._advance = function (direction, horzdir, vertdir) {
	if (!this.successorgrid.classList.contains('slideplus-hidden')) {
		return;  // animation is in progress, wait until finished
	}

	this._cancelTimer();

	const rows = /** @type {number} */ (this.options['rows']);
	const cols = /** @type {number} */ (this.options['cols']);
	const loop = /** @type {boolean} */ (this.options['loop']);

	const orientation = /** @type {SlidePlusOrientation} */ (this.options['orientation']);
	const step = /** @type {SlidePlusStep} */ (this.options['step']);

	let movedir = '', stationarydir = '';
	/**
	* The reciprocal of the fraction by which the slider advances (1 for the entire viewport).
	* @type {number}
	*/
	let fraction = 0;
	if (SlidePlusOrientation.Horizontal === orientation) {  // moves from right to left, or left to right
		if (SlidePlusStep.Single === step) {  // advances by a single column
			fraction = cols;
		} else {
			fraction = 1;
		}
		movedir = horzdir;
		stationarydir = vertdir;
	} else if (SlidePlusOrientation.Vertical === orientation) {  // moves upwards or downwards
		if (SlidePlusStep.Single === step) {  // advances by a single row
			fraction = rows;
		} else {
			fraction = 1;
		}
		movedir = vertdir;
		stationarydir = horzdir;
	}

	const targetindex = this.current + direction * this._getOffset();
	if (!loop && !this._inRange(targetindex)) {
		return;
	}

	// arrange items in the viewport that moves in
	this._arrange(this.successoritems, this.current + direction * rows * cols);

	// reset position
	resetPosition(this.successorgrid);

	// initiate animation sequence
	const animation = 'slideplus-push-' + movedir + '-' + fraction;
	this.currentgrid.style['animationName'] = animation;
	this.successorgrid.style['animationName'] = animation;
	this.successorgrid.style[movedir] = '100%';
	this.successorgrid.style[stationarydir] = '0';
	this.successorgrid.classList.remove('slideplus-hidden');

	// make sure arranging layout works with new starting offset
	this.current = this._at(targetindex);

	// update visibility for navigation buttons
	this._updateNavigationControls();
};
/**
* Switches the slider to a different set of elements.
* To be used when navigating to an arbitrary (not necessarily neighboring) set of elements.
* @param {number} index The zero-based element index to jump to.
*/
SlidePlusSlider.prototype._jump = function (index) {
	if (index == this.current) {
		return;  // nowhere to jump, already at the desired index
	}

	if (!this.successorgrid.classList.contains('slideplus-hidden')) {
		return;  // animation is in progress, wait until finished
	}

	this._cancelTimer();

	this._arrange(this.successoritems, index);

	// reset position
	resetPosition(this.successorgrid);
	this.successorgrid.style.top = '0';
	this.successorgrid.style.left = '0';

	this.currentgrid.style['animationName'] = 'slideplus-fade-out';
	this.successorgrid.style['animationName'] = 'slideplus-fade-in';
	this.successorgrid.classList.remove('slideplus-hidden');

	this.current = index;

	// update visibility for navigation buttons
	this._updateNavigationControls();
};
SlidePlusSlider.prototype.first = function () {
	this._jump(0);
};
SlidePlusSlider.prototype.previous = function () {
	const ltr = /** @type {SlidePlusWritingSystem} */ (this.options['dir']) == SlidePlusWritingSystem.LeftToRight;
	this._advance(-1, ltr ? 'right' : 'left', 'bottom');
};
SlidePlusSlider.prototype.next = function () {
	const ltr = /** @type {SlidePlusWritingSystem} */ (this.options['dir']) == SlidePlusWritingSystem.LeftToRight;
	this._advance(1, ltr ? 'left' : 'right', 'top');
};
SlidePlusSlider.prototype.last = function () {
	const offset = this._getOffset();
	const len = this.items.length;
	const mod = len % offset;
	const index = len - (mod != 0 ? mod : offset);  // check for remainder to avoid a blank page (index to position past end)
	this._jump(index);
};

SlidePlusSlider.prototype['first'] = SlidePlusSlider.prototype.first;
SlidePlusSlider.prototype['previous'] = SlidePlusSlider.prototype.previous;
SlidePlusSlider.prototype['next'] = SlidePlusSlider.prototype.next;
SlidePlusSlider.prototype['last'] = SlidePlusSlider.prototype.last;

SlidePlusSlider.prototype._startTimer = function () {
	if (!this.running) {
		/** @type {boolean} */
		this.running = true;
		this._restartTimer();
	}
}
SlidePlusSlider.prototype._stopTimer = function () {
	if (this.running) {
		this.running = false;
		this._cancelTimer();
	}
}
SlidePlusSlider.prototype._restartTimer = function () {
	let self = this;

	if (this.running && !this.timeout) {  // do not restart timer unless slideshow mode is active
		const delay = /** @type {number} */ (this.options['delay']);
		if (delay > 0) {
			/** @type {?number} */
			this.timeout = window.setInterval(function () {
				let rect = self.currentgrid.getBoundingClientRect();
				for (let i = 0; i <= 2; ++i) {  // check horizontally leftmost, center and rightmost point
					for (let j = 0; j <= 2; ++j) {  // check vertically topmost, center and bottommost point
						// check if container is visible and not covered by another element (e.g. a pop-up window)
						let elem = document.elementFromPoint(rect.left + i * rect.width / 2, rect.top + j * rect.height / 2);
						if (self.gallery.contains(elem)) {  // check self and all descendants
							self.next();
						}
					}
				}
			}, delay);
		}
	}
}
SlidePlusSlider.prototype._cancelTimer = function () {
	if (this.timeout) {
		window.clearInterval(this.timeout);
		this.timeout = null;
	}
}

/**
* Automatically discovers static image sliders wrapped in an HTML <noscript> element and transforms them into a rotating gallery.
*
* Example HTML source code:
* <noscript class="slideplus">
* <ul>
* <li><a href="images/example1.jpg"><img width="150" height="100" alt="First sample image" src="thumbs/example1.jpg" /></a></li>
* <li><img width="150" height="100" alt="Second sample image" src="thumbs/example2.jpg" /></li>
* </ul>
* </noscript>
*
* @param {SlidePlusOptions=} options
* @param {function(!Element=):string=} titleFn
*/
SlidePlusSlider['discover'] = function (options, titleFn) {
	// lists, possibly wrapped in <noscript>
	[].forEach.call(/** @type {!IArrayLike<!HTMLElement>} */ (document.querySelectorAll('ul.slideplus, noscript.slideplus')), function (/** @type {!HTMLElement} */ item) {
		new SlidePlusSlider(item, options, titleFn);
	});
};

window['SlidePlusSlider'] = SlidePlusSlider;
window['Preloader'] = Preloader;
