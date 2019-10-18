<?php
/**
* @file
* @brief    sigplus Image Gallery Plus hoverplus lightweight pop-up window engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2014 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for MooTools-based hoverplus lightweight pop-up window on mouse-over engine.
* @see http://hunyadi.info.hu/projects/hoverplus/
*/
class SigPlusNovoHoverPlusLightboxEngine extends SigPlusNovoLightboxEngine {
	private $theme = 'light';

	public function getIdentifier() {
		return 'hoverplus';
	}

	public function getLibrary() {
		return 'mootools';
	}

	public function __construct($params = false) {
		parent::__construct($params);
		if (isset($params['theme'])) {
			$this->theme = $params['theme'];
		}
	}

	/**
	* Adds style sheet references to the HTML head element.
	*/
	public function addStyles($selector, SigPlusNovoGalleryParameters $params) {
		// add main stylesheet
		parent::addStyles($selector, $params);

		// add theme stylesheet
		$instance = SigPlusNovoEngineServices::instance();
		$instance->addStylesheet('/media/sigplus/engines/'.$this->getIdentifier().'/css/'.$this->getIdentifier().'.'.$this->theme.'.css', array('title'=>$this->getIdentifier().'-'.$this->theme));
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
		$jsparams['autocenter'] = $params->lightbox_autocenter;
		$jsparams = array_merge($jsparams, $params->lightbox_params);

		// add document loaded event script with parameters
		$script = 'hoverplus.bind(document.getElements('.json_encode($selector).'), '.json_encode($jsparams, JSON_FORCE_OBJECT).');';
		$instance = SigPlusNovoEngineServices::instance();
		$instance->addOnReadyScript($script);
	}
}
