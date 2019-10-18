<?php
/**
* @file
* @brief    sigplus Image Gallery Plus editor plug-in localization forwarder
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2014 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

if (isset($_GET['lang'])) {
	$lang = $_GET['lang'];
	$path = dirname(__FILE__).DIRECTORY_SEPARATOR."button.{$lang}.html";
	if (file_exists($path)) {
		// ensure the content is served with the appropriate encoding, irrespective of global server settings
		$size = filesize($path);
		header("Content-Length: {$size}");
		header("Content-type: text/html; charset=utf-8");
		readfile($path);
		return;
	}
}
print '<html><body></body></html>';
