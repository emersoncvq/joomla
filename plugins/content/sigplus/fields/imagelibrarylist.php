<?php
/**
* @file
* @brief    sigplus Image Gallery Plus image processing library list control
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2017 Levente Hunyadi
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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'constants.php';

jimport('joomla.form.formfield');

require_once JPATH_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.SIGPLUS_PLUGIN_FOLDER.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'librarian.php';

/**
* Renders a control that lists all supported image processing libraries.
* This class represents a user-defined control in the administration backend.
*/
class JFormFieldImageLibraryList extends JFormField {
	protected $type = 'ImageLibraryList';

	/**
	* Generates an HTML @c select list with options.
	* @param name The value of the HTML name attribute.
	* @param attribs Additional HTML attributes for the <select> tag.
	* @param selected The key that is selected.
	* @return HTML for the select list.
	*/
	private function renderHtmlSelect($options, $name, $attribs = null, $selected = null) {
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		$html = '<select name="'. $name .'" '. $attribs .'>';
		foreach ($options as $value => $textkey) {
			$html .= '<option '.( $selected == $value ? 'selected="selected" ' : '' ).'value="'.$value.'">'.JText::_($textkey).'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	private function renderNone($text, $name, $attribs = null) {
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		return '<span style="color:red" '.$attribs.'><input type="hidden" name="'.$name.'" value="none" />'.JText::_($text).'</span>';
	}

	public function getInput() {
		$class = ( isset($this->element['class']) ? 'class="'.(string)$this->element['class'].'"' : 'class="inputbox"' );

		// user-friendly names for image processing libraries
		$items = array();
		foreach ($this->element->option as $o) {
			$val = (string)$o['value'];  // attribute "value"
			$textkey = (string)$o;  // element text content
			$items[$val] = $textkey;
		}

		// test which image processing libraries are supported
		$supported = array();
		if (is_gd_supported()) {
			$supported['gd'] = 'GD';  // localized when control HTML code is generated
		}
		if (is_gmagick_supported()) {
			$supported['gmagick'] = 'GraphicsMagick';
		}
		if (is_imagick_supported()) {
			$supported['imagick'] = 'ImageMagick';
		}

		if (empty($supported)) {  // no library is supported
			if (isset($items['none'])) {
				$textkey = $items['none'];
			} else {
				$textkey = 'none';
			}
			return $this->renderNone($textkey, $this->name, $class);
		} else {  // at least a single library is supported
			$supported['default'] = 'default';
			foreach ($items as $key => $textkey) {
				if (isset($supported[$key])) {
					$supported[$key] = $textkey;
				}
			}
			return $this->renderHtmlSelect($supported, $this->name, $class, $this->value);
		}
	}
}
