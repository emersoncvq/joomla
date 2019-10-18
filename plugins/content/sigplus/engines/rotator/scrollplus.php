<?php
/**
* @file
* @brief    sigplus Image Gallery Plus scrollplus manual slider engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2014 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for the scrollplus manual slider engine, written in plain JavaScript.
* @see http://hunyadi.info.hu/projects/scrollplus/
*/
class SigPlusNovoScrollPlusRotatorEngine extends SigPlusNovoRotatorEngine {
	public function getIdentifier() {
		return 'scrollplus';
	}

	/**
	* Adds script references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addScripts($selector, SigPlusNovoGalleryParameters $params) {
		// add main script
		parent::addScripts($selector, $params);

		// get engine helper
		$instance = SigPlusNovoEngineServices::instance();

		// set rotator engine options
		$jsparams = array();
		$jsparams['orientation'] = $params->rotator_orientation;
		$jsparams = array_merge($jsparams, $params->rotator_params);
		$jsparams = json_encode($jsparams);
		$selector = json_encode("{$selector}");

		// add document loaded event script
		$script = "new ScrollPlus(document.querySelector({$selector}), {$jsparams});";
		$instance->addOnReadyScript($script);
	}
}
