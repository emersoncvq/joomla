<?php
/**
* @file
* @brief    sigplus Image Gallery Plus plug-in for Joomla
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2014 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!empty($images)) {
	// Gallery wrapper prologue
	print '<div id="'.$galleryid.'" class="'.$gallerystyle.'">';

	// List of images shown directly on the page
	print '<noscript class="sigplus-gallery">';  // provide fall-back to a bare-bone gallery implementation when scripting is disabled in the browser
	print '<ul>';
	for ($index = 0; $index < $limit; $index++) {
		// no maximum preview image count set or current image index is within maximum limit
		print '<li>';
		$this->printImage($images[$index], $index, $total);
		print '</li>';
	}
	print '</ul>';
	print '</noscript>';

	// List of images that appear only in the lightbox pop-up window
	if ($curparams->maxcount > 0 && $curparams->lightbox !== false) {
		// if lightbox is disabled, user cannot navigate to images beyond maximum image count
		for (; $index < $total; $index++) {
			$this->printImage($images[$index], $index, $total, 'display:none !important;');
		}
	}

	// Gallery wrapper epilogue
	print '</div>';
} else {
	print JText::_('SIGPLUS_GALLERY_EMPTY');
}
