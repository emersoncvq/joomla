/**
* @file
* @brief    sigplus Image Gallery Plus logging simple expand/collapse script
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// BEGIN sigplus logging
document.addEventListener('DOMContentLoaded', function () {
	[].forEach.call(document.querySelectorAll('pre.sigplus-log'), function (block) {
		var linkshow = document.createElement('a');
		linkshow.href = '#';
		linkshow.classList.add('sigplus-log-show');
		linkshow.innerHTML = 'Show';
		block.parentNode.insertBefore(linkshow, block);

		var linkhide = document.createElement('a');
		linkhide.href= '#';
		linkhide.classList.add('sigplus-log-hide');
		linkhide.innerHTML = 'Hide';
		linkhide.style.setProperty('display', 'none');
		block.parentNode.insertBefore(linkhide, block);

		block.style.setProperty('display', 'none');

		linkshow.addEventListener('click', function (event) {
			linkshow.style.setProperty('display', 'none');
			linkhide.style.removeProperty('display');
			block.style.removeProperty('display');
			event.preventDefault();
		});
		linkhide.addEventListener('click', function (event) {
			linkhide.style.setProperty('display', 'none');
			linkshow.style.removeProperty('display');
			block.style.setProperty('display', 'none');
			event.preventDefault();
		});
	});
});
// END sigplus logging