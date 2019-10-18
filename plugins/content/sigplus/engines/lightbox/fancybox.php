<?php
/**
* @file
* @brief    sigplus Image Gallery Plus Fancybox lightweight pop-up window engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2014 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for jQuery-based Fancybox pop-up window engine.
* @see http://fancybox.net
*/
class SigPlusNovoFancyboxLightboxEngine extends SigPlusNovoLightboxEngine {
	public function getIdentifier() {
		return 'fancybox';
	}

	public function getLibrary() {
		return 'jquery';
	}

	/**
	* Adds script references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addScripts($selector, SigPlusNovoGalleryParameters $params) {
		// add main script
		parent::addScripts($selector, $params);

		// build lightbox engine options
		$jsparams = array();
		$jsparams['cyclic'] = $params->loop;
		$jsparams['autoScale'] = $params->lightbox_autofit;
		$jsparams['centerOnScroll'] = $params->lightbox_autocenter;
		$jsparams = array_merge($jsparams, $params->lightbox_params);

		// add document loaded event script with parameters
		$selector = json_encode($selector);
		$jsparams = json_encode($jsparams, JSON_FORCE_OBJECT);

		$script = "(function ($) {".
			"var anchors = $({$selector});".
			"var data = $.makeArray(anchors).map(function (anchor) {".
				"return { href: $(anchor).attr('href'), title: $('img', anchor).attr('alt') };".
			"});".
			"anchors.click(function (evt) {".
				"evt.preventDefault();".
				"$.fancybox(".
					"data, $.extend({ index: anchors.index(this) }, {$jsparams})".
				");".
			"});".
		"})(jQuery);";

		$instance = SigPlusNovoEngineServices::instance();
		$instance->addOnReadyScript($script);
	}
}
