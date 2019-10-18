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

// HTML attribute for CSS style override
$style_attr = $style ? ' style="'.$style.'"' : '';

// HTML data attributes
$data_attr = '';
foreach ($properties as $property) {
	$data_attr .= ' data-'.$property->key.'="'.htmlspecialchars($property->value).'"';
}

// HTML for a single image in the gallery
print '<a class="sigplus-image"'.$style_attr.' href="'.$url.'"'.$data_attr.'>';
$title_text = strip_tags($title);
print '<img class="sigplus-preview" src="'.htmlspecialchars($preview_url).'" width="'.$preview_width.'" height="'.$preview_height.'" alt="'.htmlspecialchars($title_text).'"';
if (version_compare(JVERSION, '3.8.0') != 0) {  // address a bug in Joomla 3.8.0 SEF URL regular expression
	$srcset = array();
	if (isset($retina_url) && !empty($retina_width)) {
		$srcset[] = htmlspecialchars($retina_url).' '.$retina_width.'w';
	}
	if (isset($preview_url) && !empty($preview_width)) {
		$srcset[] = htmlspecialchars($preview_url).' '.$preview_width.'w';
	}
	if (isset($thumb_url) && !empty($thumb_width)) {
		$srcset[] = htmlspecialchars($thumb_url).' '.$thumb_width.'w';
	}
	if (!empty($srcset)) {
		print ' srcset="'.implode(', ', $srcset).'" sizes="'.$preview_width.'px"';
	}
}
print ' />';
print '</a>';
print '<div class="sigplus-summary">'.$summary.'</div>';
if ($download_url) {
	print '<a class="sigplus-download"'.$style_attr.' aria-hidden="true" href="'.htmlspecialchars($download_url).'"></a>';
}
if ($title != $title_text) {
	print '<div class="sigplus-title">'.$title.'</div>';
}
