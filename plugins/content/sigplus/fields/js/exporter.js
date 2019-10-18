/**
* @file
* @brief    sigplus Image Gallery Plus settings export/import control
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

'use strict';

if (!Element.prototype.matches) {
	Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
}

if (!Element.prototype.closest) {
	Element.prototype.closest = function (s) {
		var el = this;
		var ancestor = this;
		if (!document.documentElement.contains(el)) {
			return null;
		}
		do {
			if (ancestor.matches(s)) {
				return ancestor;
			}
			ancestor = ancestor.parentElement;
		} while (ancestor !== null);
		return null;
	};
}

function SettingsExporter(textarea, exportbtn, importbtn) {
	// get parent HTML form
	var form = textarea.closest('form');
	if (form) {
		function _getUserControls(items) {
			var selector = items.map(function (item) {
				return item + '[name^="jform[params]"]';  // settings have attribute "name" set to "jform[param][...]"
			}).join(',');
			return form.querySelectorAll(selector);
		}

		function _getUserControlKey(elem) {
			return elem.name.match(/^jform\[params\]\[(\w+)]$/)[1];  // settings have attribute "name" set to "jform[param][...]"
		}

		// register click event for export (save) function
		exportbtn.addEventListener('click', function () {
			// traverse elements that store settings
			var settings = {};
			[].forEach.call(_getUserControls(['input[type=text]','input[type=radio]','select','textarea']), function (elem) {
				if (elem.type != 'radio' || elem.checked) {  // omit radio buttons that are not checked
					var value = elem.tagName != 'select' ? elem.value : elem.options[elem.selectedIndex].value;

					// a single JSON object key/value pair
					settings[_getUserControlKey(elem)] = value;
				}
			});

			// show generated JSON object string in text area
			textarea.value = JSON.stringify(settings, null, 1);  // use line breaks for pretty output
		});

		// register click event for import (restore) function
		importbtn.addEventListener('click', function () {
			// decode JSON object string into parameter object
			var params;
			try {
				params = JSON.parse(textarea.value);
			} catch (e) { }
			if (params) {
				// traverse elements that store settings
				[].forEach.call(_getUserControls(['input[type=text]','textarea']), function (elem) {
					// update text field value
					var key = _getUserControlKey(elem);
					if (params.hasOwnProperty(key)) {
						elem.value = params[key];
					}
				});
				[].forEach.call(_getUserControls(['select']), function (elem) {
					// update selected element
					var key = _getUserControlKey(elem);
					if (params.hasOwnProperty(key)) {
						// Joomla uses Chosen as a list box: https://harvesthq.github.io/chosen/
						jQuery(elem).val(params[key]).trigger('liszt:updated');
					}
				});
				[].forEach.call(_getUserControls(['input[type=radio]']), function (elem) {
					// check radio button if its value matches the value stored in the parameter object
					if (elem.value == params[_getUserControlKey(elem)]) {
						elem.checked = true;
					}
				});
			}
		});
	}
}
