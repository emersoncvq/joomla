<?php
/**
* @file
* @brief    sigplus Image Gallery Plus Bootstrap lightweight pop-up window engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for jQuery-based Bootstrap lightweight pop-up window engine, integrated into Joomla 3.0.
* @see http://getbootstrap.com/
*/
class SigPlusNovoBootstrapLightboxEngine extends SigPlusNovoLightboxEngine {
	public function getIdentifier() {
		return 'bootstrap';
	}

	public function getLibrary() {
		return 'bootstrap';
	}

	/**
	* Adds style sheet references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addStyles($selector, SigPlusNovoGalleryParameters $params) {
		parent::addStyles($selector, $params);
	}

	/**
	* Adds script references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addScripts($selector, SigPlusNovoGalleryParameters $params) {
		static $uninitialized = true;

		parent::addScripts($selector, $params);

		// get reference to sigplus engine services
		$instance = SigPlusNovoEngineServices::instance();

		if ($uninitialized) {
			$instance->addOnReadyScript(''
				.'window.sigplus.bootstrap.initialize({'
					.'previous:'.json_encode(JText::_('SIGPLUS_PREVIOUS')).','
					.'next:'.json_encode(JText::_('SIGPLUS_NEXT')).','
					.'close:'.json_encode(JText::_('SIGPLUS_CLOSE'))
				.'});'
			);
			$uninitialized = false;
		}

		// wire Bootstrap with sigplus gallery
		$instance->addOnReadyScript(''
			.'(function ($) {'
				.'var items = $('.json_encode($selector).');'
				.'items.click(function (event) {'
					.'window.sigplus.bootstrap.show(items, $(this), '.json_encode($params->lightbox_params, JSON_FORCE_OBJECT).');'
					.'event.preventDefault();'  // prevent click event on anchor triggering default browser behavior
				.'});'
			.'})(jQuery);'
		);
	}
}
