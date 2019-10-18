<?php
/**
* @file
* @brief    sigplus Image Gallery Plus settings export/import control
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

/**
* Renders a control for exporting and importing configuration settings.
* This class implements a user-defined control in the administration backend.
*/
class JFormFieldExporter extends JFormField {
	protected $type = 'Exporter';

	public function getInput() {
		$html = '';

		$scriptpath = JPATH_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.SIGPLUS_PLUGIN_FOLDER.DIRECTORY_SEPARATOR.'fields';
		if (file_exists($scriptpath.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'exporter.min.js') || file_exists($scriptpath.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'exporter.js')) {
			// get identifiers
			$class = ( isset($this->element['class']) ? (string)$this->element['class'] : 'inputbox' );
			$ctrlid = str_replace(array('][','[',']'), array('_','_',''), $this->name);
			$exportctrlid = $ctrlid.'-export';
			$importctrlid = $ctrlid.'-import';

			// generate HTML output
			$html .=
				'<button type="button" id="'.$exportctrlid.'">'.JText::_('SIGPLUS_SETTINGS_EXPORT').'</button>'.
				'<button type="button" id="'.$importctrlid.'">'.JText::_('SIGPLUS_SETTINGS_IMPORT').'</button>'.
				'<br /><textarea class="'.$class.'" id="'.$ctrlid.'" rows="10" cols="40"></textarea>';

			// add script declaration to header
			$document = JFactory::getDocument();
			if (file_exists($scriptpath.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'exporter.min.js') && filemtime($scriptpath.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'exporter.min.js') >= filemtime($scriptpath.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'exporter.js')) {
				$document->addScript(JURI::root(true).'/plugins/content/'.SIGPLUS_PLUGIN_FOLDER.'/fields/js/exporter.min.js');
			} else {
				$document->addScript(JURI::root(true).'/plugins/content/'.SIGPLUS_PLUGIN_FOLDER.'/fields/js/exporter.js');
			}
			$document->addScriptDeclaration('document.addEventListener("DOMContentLoaded", function () { new SettingsExporter(document.getElementById("'.$ctrlid.'"), document.getElementById("'.$exportctrlid.'"), document.getElementById("'.$importctrlid.'")); });');
		}

		// add control to page
		return $html;
	}
}
