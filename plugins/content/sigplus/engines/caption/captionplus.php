<?php
/**
* @file
* @brief    sigplus Image Gallery Plus captionplus image caption engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2014 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for the engine captionplus.
* @see http://hunyadi.info.hu/projects/
*/
class SigPlusNovoCaptionPlusCaptionEngine extends SigPlusNovoCaptionEngine {
	public function getIdentifier() {
		return 'captionplus';
	}

	/**
	* Adds script references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addScripts($selector, SigPlusNovoGalleryParameters $params) {
		// add main script
		parent::addScripts($selector, $params);

		// build rotator engine options
		$jsparams = array();
		$jsparams['download'] = $params->lightbox === false;  // show icon to download full-size image when there is no lightbox engine
		switch ($params->caption_position) {
			case 'below': case 'above':
				$jsparams['overlay'] = false;
				break;
			default:
				$jsparams['overlay'] = true;
				break;
		}
		switch ($params->caption_position) {
			case 'overlay-top': case 'above':
				$jsparams['position'] = 'top';
				break;
			default:
				$jsparams['position'] = 'bottom';
				break;
		}
		$jsparams['visibility'] = $params->caption_visibility;
		$jsparams = array_merge($jsparams, $params->caption_params);
		$jsparams = json_encode($jsparams);
		$selector = json_encode($selector);

		// add document loaded event script with parameters
		$script = "CaptionPlus.bind(document.querySelector({$selector}), {$jsparams});";
		$instance = SigPlusNovoEngineServices::instance();
		$instance->addOnReadyScript($script);
	}
}
