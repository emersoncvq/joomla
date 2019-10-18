<?php
/**
* @file
* @brief    sigplus Image Gallery Plus module for Joomla
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2014 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus module for Joomla
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
defined('_JEXEC') or die('Restricted access');

if (!defined('SIGPLUS_VERSION_MODULE')) {
	define('SIGPLUS_VERSION_MODULE', '1.5.0');
}

if (!defined('SIGPLUS_PLUGIN_FOLDER')) {
	define('SIGPLUS_PLUGIN_FOLDER', 'sigplus');
}

if (!defined('SIGPLUS_DEBUG')) {
	// Triggers debug mode. Debug uses uncompressed version of scripts rather than the bandwidth-saving minified versions.
	define('SIGPLUS_DEBUG', false);
}
if (!defined('SIGPLUS_LOGGING')) {
	// Triggers logging mode. Verbose status messages are printed to the output.
	define('SIGPLUS_LOGGING', false);
}

// include the helper file
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php';

$gallery_html = false;

try {
	// import dependencies
	if (($core = SigPlusNovoModuleHelper::import()) !== false) {
		$core->setParameterObject($params);  // get parameters from the module's configuration

		try {
			if ($params instanceof stdClass) {
				$imagesource = $params->source;
			} else if ($params instanceof JRegistry) {  // Joomla 2.5 and earlier
				$imagesource = $params->get('source');
			}

			// download image
			try {
				if ($core->downloadImage($imagesource)) {  // an image has been requested for download
					jexit();  // do not produce a page
				}
			} catch (SigPlusNovoImageDownloadAccessException $e) {  // signal download errors but do not stop page processing
				$app = JFactory::getApplication();
				$app->enqueueMessage($e->getMessage(), 'error');
			}

			// generate image gallery
			$gallery_html = $core->getGalleryHTML($imagesource, $id);
			$core->addStyles($id);
			$core->addScripts($id);

			$core->resetParameters();
		} catch (Exception $e) {
			$core->resetParameters();
			throw $e;
		}
	}  // an error message has already been printed by another module instance
} catch (Exception $e) {
	$app = JFactory::getApplication();
	$app->enqueueMessage($e->getMessage(), 'error');
	$gallery_html = $e->getMessage();
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');
require JModuleHelper::getLayoutPath('mod_sigplus', $params->get('layout', 'default'));
