/**@license sigplus client-side initialization script
* @author  Levente Hunyadi
* @version 1.5.0
* @remarks Copyright (C) 2011-2017 Levente Hunyadi
* @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see     http://hunyadi.info.hu/projects/sigplus
**/

/*{"compilation_level":"ADVANCED_OPTIMIZATIONS"}*/
'use strict';

let sigplus = window['sigplus'] || {};
window['sigplus'] = sigplus;
sigplus['lightbox'] = sigplus['lightbox'] || {};

/**
* Unwraps galleries protected by <noscript>, applies anchor and image attributes and removes helper elements.
* @param {string} id An HTML identifier.
*/
function __sigplusInitialize(id) {
	if (window.CSS && window.CSS.escape) {
		id = window.CSS.escape(id);
	}

	let gallery = document.querySelector('#' + id + '.sigplus-gallery');

	if (gallery) {
		// unwrap galleries from <noscript> elements
		[].forEach.call(gallery.querySelectorAll('noscript'), function (item) {
			let noscript = /** @type {!Element} */ (item);
			let elem = document.createElement('div');
			elem.innerHTML = /** @type {string} */ (noscript['innerText']);  // <noscript> elements are not parsed when javascript is enabled
			let replacement = elem.firstChild === elem.lastChild ? elem.firstChild : elem;  // unwrap child from parent element with single child
			noscript.parentNode.replaceChild(replacement, noscript);
		});

		// apply anchor and image attributes
		[].forEach.call(gallery.querySelectorAll('a'), function (item) {
			let anchor = /** @type {!HTMLAnchorElement} */ (item);
			let image = /** @type {HTMLImageElement} */ (anchor.querySelector('img'));
			if (image) {
				anchor.setAttribute('title', image.getAttribute('alt'));
			}

			// assign summary text (with HTML support)
			let parent = anchor.parentNode;
			if (parent) {  // skip anchors that are not sigplus gallery image anchors
				let summary = /** @type {HTMLElement} */ (parent.querySelector('.sigplus-summary'));
				if (summary) {
					anchor.setAttribute('title', summary['innerText']);
					let html = summary.innerHTML;
					if (html) {
						anchor.setAttribute('data-summary', html);
					}

					let targetanchor = /** @type {!HTMLAnchorElement} */ (summary.querySelector('a'));
					if (targetanchor) {  // summary contains an anchor, which should be set as a preferred target for the image
						anchor.setAttribute('data-href', targetanchor.href);
						anchor.setAttribute('data-target', targetanchor.target);
					}

					summary.parentNode.removeChild(summary);
				}

				// assign download URL
				let downloadanchor = /** @type {HTMLAnchorElement} */ (parent.querySelector('.sigplus-download'));
				if (downloadanchor) {
					anchor.setAttribute('data-download', downloadanchor.href);
					downloadanchor.parentNode.removeChild(downloadanchor);
				}

				let title = /** @type {HTMLElement} */ (parent.querySelector('.sigplus-title'));
				if (title) {
					let html = title.innerHTML;
					if (html) {
						anchor.setAttribute('data-title', html);
					}
					title.parentNode.removeChild(title);
				}
			}
		});
	}

	// apply click prevention to galleries without lightbox
	[].forEach.call(document.querySelectorAll('#' + id + '.sigplus-lightbox-none a.sigplus-image'), function (item) {
		let anchor = /** @type {!HTMLAnchorElement} */ (item);
		let link = anchor.getAttribute('data-href');
		if (link) {  // there is a preferred target for the image
			anchor.href = link;
			anchor.target = anchor.getAttribute('data-target');
			anchor.removeAttribute('data-href');
			anchor.removeAttribute('data-target');
		} else {
			anchor.addEventListener('click', function (event) {
				event.preventDefault();
			});
		}
	});
}
window['__sigplusInitialize'] = __sigplusInitialize;

/**
* Apply caption text templates.
* @param {string} id An HTML identifier.
* @param {string} titletemplate A template for titles to use for making substitutions.
* @param {string} summarytemplate A template for summary text to use for making substitutions.
*/
function __sigplusCaption(id, titletemplate, summarytemplate) {
	if (window.CSS && window.CSS.escape) {
		id = window.CSS.escape(id);
	}

	/**
	* @param {number} bytes
	* @return {string}
	*/
	function bytesToSize(bytes) {
		if (bytes == 0) {
			return '0 B';
		} else {
			let sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
			let i = (Math.log(bytes) / Math.log(1000)) | 0;  // coerce to integer
			return (bytes / Math.pow(1000, i)).toPrecision(3) + ' ' + sizes[i];
		}
	};

	/**
	* Matches a string against a regular expression and returns the first matched group if any.
	* @param {string} str A string to be matched.
	* @param {!RegExp} regexp A regular expression to match.
	* @return {string}
	*/
	function match(str, regexp) {
		let res = regexp.exec(str);
		return res ? res[1] : '';
	}

	let anchors = document.querySelectorAll('#' + id + ' a.sigplus-image');
	titletemplate = titletemplate || '{$text}';
	summarytemplate = summarytemplate || '{$text}';
	[].forEach.call(anchors, function (item, index) {
		let anchor = /** @type {!HTMLAnchorElement} */ (item);

		/**
		* @param {string} template
		* @param {string} text
		*/
		function _subs(template, text) {
			let filename = decodeURIComponent(anchor.getAttribute('data-image-file-name') || anchor.pathname || '');
			let replacement = {  // template replacement rules
				'text': text || '',
				'name': match(filename, /([^\/]+?)(\.[^.\/]+)?$/),  // keep only file name from path (excluding extension)
				'filename': match(filename, /([^\/]+)$/),  // keep only file name component from path (including extension)
				'filesize': bytesToSize(parseInt(anchor.getAttribute('data-image-file-size'), 10)),
				'current': index + 1,  // index is zero-based but user interface needs one-based counter
				'total': anchors.length
			};

			return template.replace(/\\?\{\$([^{}]+)\}/g, function (match, name) {
				return replacement[name];
			});
		}

		/**
		* @param {!HTMLElement} elem
		* @param {string} attr
		* @param {string} template
		*/
		function _subsattr(elem, attr, template) {
			elem.setAttribute(attr, _subs(template, elem.getAttribute(attr)));
		}

		// apply template to summary text
		_subsattr(anchor, 'data-summary', summarytemplate);

		// apply template to "title" attribute of anchor element
		_subsattr(anchor, 'title', summarytemplate);

		// apply template to "alt" attribute of image element wrapped in anchor
		let image = /** @type {HTMLImageElement} */ (anchor.querySelector('img'));
		if (image) {
			_subsattr(image, 'alt', titletemplate);
		}
	});
}
window['__sigplusCaption'] = __sigplusCaption;
