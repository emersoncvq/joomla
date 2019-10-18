<?php
/**
* @file
* @brief    sigplus Image Gallery Plus installer script
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus module for Joomla
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('SIGPLUS_PLUGIN_FOLDER')) {
	define('SIGPLUS_PLUGIN_FOLDER', 'sigplus');
}
if (!defined('SIGPLUS_MEDIA_FOLDER')) {
	define('SIGPLUS_MEDIA_FOLDER', 'sigplus');
}

// protect duplicate class defintion when file has already been included from the installation (temporary) directory (e.g. by the plug-in installer)
if (!class_exists('SigPlusNovoDatabaseSetup')) {
	require_once JPATH_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.SIGPLUS_PLUGIN_FOLDER.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'setup.php';
}

class mod_SigPlusNovoInstallerScript {
	function __construct($parent) { }

	function install($parent) { }

	function uninstall($parent) { }

	function update($parent) { }

	function preflight($type, $parent) { }

	function postflight($type, $parent) {
		// copy language file
		self::copyLanguageFiles();

		// copy back-end controls
		$sourcepath = JPATH_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.SIGPLUS_PLUGIN_FOLDER.DIRECTORY_SEPARATOR.'fields';
		$targetpath = JPATH_ROOT.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_'.SIGPLUS_PLUGIN_FOLDER.DIRECTORY_SEPARATOR.'fields';
		$fieldfiles = scandir($sourcepath);
		foreach ($fieldfiles as $fieldfile) {
			if (pathinfo($sourcepath.DIRECTORY_SEPARATOR.$fieldfile, PATHINFO_EXTENSION) == 'php') {
				@copy($sourcepath.DIRECTORY_SEPARATOR.$fieldfile, $targetpath.DIRECTORY_SEPARATOR.$fieldfile);
			}
		}

		switch ($type) {
			case 'update':
				self::migrateConfiguration();
				break;
		}
	}

	/**
	* Appends plug-in language strings to the module language file.
	*/
	private static function copyLanguageFiles() {
		$admin_language_root = JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'language';
		$site_language_root = JPATH_ROOT.DIRECTORY_SEPARATOR.'language';
		foreach (scandir($admin_language_root) as $language_code) {  // iterate over installed plug-in languages
			if (!preg_match('/^[a-z]{2}-[A-Z]{2}$/', $language_code) || !is_dir($admin_language_root.DIRECTORY_SEPARATOR.$language_code)) {
				continue;
			}

			$admin_language_folder = $admin_language_root.DIRECTORY_SEPARATOR.$language_code;
			$plugin_language_file = $admin_language_folder.DIRECTORY_SEPARATOR.$language_code.'.plg_content_'.SIGPLUS_PLUGIN_FOLDER.'.ini';
			$site_language_folder = $site_language_root.DIRECTORY_SEPARATOR.$language_code;
			$module_language_file = $site_language_folder.DIRECTORY_SEPARATOR.$language_code.'.mod_'.SIGPLUS_PLUGIN_FOLDER.'.ini';

			if (!is_file($plugin_language_file) || !is_file($module_language_file)) {
				continue;
			}

			if (($data = file_get_contents($plugin_language_file)) !== false && ($handle = fopen($module_language_file, 'a')) !== false) {
				fwrite($handle, "\n\n");
				fwrite($handle, $data);
				fclose($handle);
			}
		}
	}

	private static function migrateConfiguration() {
		$db = JFactory::getDbo();

		// iterate over existing module configuration settings
		$db->setQuery('SELECT id FROM #__modules WHERE module = '.$db->quote('mod_sigplus'));
		$modules = $db->loadColumn();
		foreach ($modules as $id) {
			// read existing module configuration settings
			$db->setQuery('SELECT params FROM #__modules WHERE module = '.$db->quote('mod_sigplus').' AND id = ' . $id);
			$oldparams = json_decode($db->loadResult(), true);

			// make sure we are migrating a 1.4.x installation to a 1.5.x installation
			if (!empty($oldparams) && !isset($oldparams['source'])) {
				$newparams = SigPlusNovoDatabaseSetup::migrateExtensionConfiguration($oldparams);

				// store the combined new and existing module settings back as a JSON string
				$paramstring = json_encode($newparams);
				$db->setQuery('UPDATE #__modules SET params = ' . $db->quote($paramstring) . ' WHERE id = ' . $id);
				$db->execute();
			}
		}
	}
}

class mod_SIGPlusInstallerScript extends mod_SigPlusNovoInstallerScript {

}
