/**@license Bootstrap integration for sigplus
* @author  Levente Hunyadi
* @version 1.5.0
* @remarks Copyright (C) 2017 Levente Hunyadi
* @see     http://hunyadi.info.hu/projects/sigplus
**/

'use strict';

window.sigplus = window.sigplus || {};
window.sigplus.bootstrap = window.sigplus.bootstrap || {};
window.sigplus.bootstrap.initialize = function (labels) {
	labels = labels || {};
	var $ = jQuery;

	// append Bootstrap dialog box HTML
	var dialog = $(''
		+   '<div id="sigplus-bootstrap" class="modal fade hide" tabindex="-1" role="dialog" aria-hidden="true">'
		+       '<div class="modal-header">'
		+           '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'
		+           '<h3 class="modal-title">sigplus</h3>'
		+       '</div>'
		+       '<div class="modal-body" style="max-height:none;"><img /></div>'
		+       '<div class="modal-footer">'
		+           '<button class="btn previous" aria-hidden="true">'+ (labels.previous || 'Previous') +'</button>'
		+           '<button class="btn next" aria-hidden="true">'+ (labels.next || 'Next') +'</button>'
		+           '<button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">'+ (labels.close || 'Close') +'</button>'
		+       '</div>'
		+   '</div>'
	).appendTo('body');
	var image = $('.modal-body > img', dialog);
	var title = $('.modal-title', dialog);
	var btnPrevious = $('.btn.previous', dialog);
	var btnNext = $('.btn.next', dialog);
	var activeSet;  // the set of elements that can be navigated in the dialog
	var activeItem;  // the image currently displayed in the dialog, always a member of the element set

	function update(item) {
		activeItem = item;
		image.attr('src', activeItem.attr('href'));  // set image
		title.html($('img', activeItem).attr('alt'));  // set image caption
	}

	function navigate(offset) {
		var index = activeSet.index(activeItem) + offset;
		var size = activeSet.length;
		index = (index + size) % size;  // normalize to interval [0;size)
		update(activeSet.eq(index));
	}

	btnPrevious.click(function () {
		navigate(-1);
	});
	btnNext.click(function () {
		navigate(1);
	});

	window.sigplus.bootstrap.show = function (set, item, options) {
		activeSet = set;
		update(item);
		btnPrevious.toggleClass('sigplus-hidden', set.length < 2);
		btnNext.toggleClass('sigplus-hidden', set.length < 2);
		dialog.modal(options);  // initialize and show modal box
	}
};
