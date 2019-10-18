/**@license sigplus editor button
* @author  Levente Hunyadi
* @version 1.5.0
* @remarks Copyright (C) 2011-2017 Levente Hunyadi
* @remarks Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see     http://hunyadi.info.hu/projects/sigplus
**/

/*{"compilation_level":"ADVANCED_OPTIMIZATIONS"}*/
'use strict';

if (!Element.prototype.matches) {
	Element.prototype.matches =
		Element.prototype.msMatchesSelector ||
		Element.prototype.webkitMatchesSelector;
}

function getJoomlaEditorInstance(editor) {
	let joomla = window.parent['Joomla'];
	if (joomla) {
		let editors = joomla['editors'];
		if (editors) {
			let instances = editors['instances'];
			if (instances && instances.hasOwnProperty(editor)) {
				return instances[editor];
			}
		}
	}
}

/**
* Inserts a sigplus activation tag into the Joomla content editor.
* @param {string} editor The identifier of the Joomla editor.
* @param {string} tag The activation tag (as a string).
* @param {string} parameters The list of parameters as key/value pairs.
*/
function insertTag(editor, tag, parameters) {
	let text = '{' + tag + parameters + '}myfolder{/' + tag + '}';
	let parent = window.parent;

	// use new API if editor supports it
	let instance = getJoomlaEditorInstance(editor);
	if (instance) {
		instance['replaceSelection'](text);
	} else {
		let insertEditorText = /** @type {function(string,string)} */ (parent['jInsertEditorText']);
		insertEditorText(text, editor);
	}

	let closeModalDialog = /** @type {function()} */ (parent['jModalClose']);
	closeModalDialog();
};

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

document.addEventListener('DOMContentLoaded', function () {
	/**
	* Extracts the Joomla parameter name from a control.
	* @param {!HTMLInputElement|!HTMLSelectElement} ctrl
	* @return {string}
	*/
	function get_param_name(ctrl) {
		let name = ctrl.getAttribute('name');
		let matches = name.match(/^params\[(.*)\]$/);
		return matches ? matches[1] : name;
	}

	let form = document.getElementById('sigplus-settings-form');  // configuration settings form
	let listitems = [].slice.call(form.querySelectorAll('li'));

	// selectors to match all user controls (only controls with the "name" attribute correspond to real plug-in settings, others are auxiliary controls)
	let checkboxselector = 'input[name][type=checkbox]';
	let radioselector = 'input[name][type=radio]';
	let textselector = 'input[name][type=text]';
	let listselector = 'select[name]';
	let ctrlselector = [checkboxselector, radioselector, textselector, listselector].join();

	// initialize parameter values to those set on content plug-in configuration page
	let options = window.parent['sigplus'];
	if (options) {  // variable that holds configuration settings as JSON object with parameter names as keys
		[].forEach.call(form.querySelectorAll(ctrlselector), function (elem) {  // enumerate form controls in order of appearance
			let ctrl = /** @type {!HTMLInputElement|!HTMLSelectElement} */ (elem);
			let name = get_param_name(ctrl);
			let value = options[name];
			if (value) {  // has a default value
				if (ctrl.matches(checkboxselector)) {  // checkbox control
					ctrl.checked = !!value;
				} else if (ctrl.matches(radioselector) && ctrl.value === '' + value) {  // related radio button (with value to assign matching button value)
					ctrl.checked = true;
				} else if (ctrl.matches([textselector, listselector].join())) {  // text and list controls
					ctrl.value = value;
				}
			}
		});
	}

	// bind event to make parameter value appear in generated activation code
	[].forEach.call(listitems, function (item) {
		// create marker control
		let updatebox = document.createElement('input');
		updatebox.setAttribute('type', 'checkbox');

		// check marker control when parameter value is to be edited
		[].forEach.call(item.querySelectorAll(ctrlselector), function (elem) {
			elem.addEventListener('focus', function () {
				updatebox.checked = true;
			});
		});

		// insert marker control before parameter name label
		item.insertBefore(updatebox, item.firstChild);  // inject as first element
	});

	// selects all user controls but omits checkboxes and radio buttons that are not checked
	let checkedselector = ':checked';
	let activectrlselector = [checkboxselector + checkedselector, radioselector + checkedselector, textselector, listselector].join();

	// process parameters when form is submitted
	document.getElementById('sigplus-settings-submit').addEventListener('click', function () {
		let params = [];  // activation code to insert
		[].forEach.call(listitems, function (item) {
			let updatebox = /** @type {HTMLInputElement} */ (item.querySelector('input[type=checkbox]'));  // retrieve as first element
			let ctrl = /** @type {!HTMLInputElement|!HTMLSelectElement} */ (item.querySelector(activectrlselector));  // first element with a form name (i.e. a control that corresponds to a real setting)
			if (ctrl && updatebox && updatebox.checked) {  // verify whether parameter value has changed
				let name = get_param_name(ctrl);
				let value = ctrl.value;
				if (value) {  // omit missing values
					if (/color$/.test(name) || !/^(0|[1-9]\d*)$/.test(value)) {  // quote color codes but not integer values
						value = '"' + value + '"';
					}
					params.push(name + '=' + value);
				}
			}
		});

		let paramtext = params.length > 0 ? ' ' + params.join(' ') : '';

		// trigger event to request the activation tag to be inserted
		insertTag(fromQueryString(window.location.search)['editor'], options['tag_gallery'], paramtext);
	});
});
