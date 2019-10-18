<?php
/**
* @file
* @brief    sigplus Image Gallery Plus boxplusx lightweight pop-up window engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for the boxplusx lightweight pop-up window engine.
* @see http://hunyadi.info.hu/projects/boxplusx/
*/
class SigPlusNovoBoxPlusLightboxEngine extends SigPlusNovoLightboxEngine {
	public function getIdentifier() {
		return 'boxplusx';
	}

	/**
	* Adds style sheet references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addStyles($selector, SigPlusNovoGalleryParameters $params) {
		// add main stylesheet
		parent::addStyles($selector, $params);
		$instance = SigPlusNovoEngineServices::instance();
		$id = '#boxplusx_'.$params->id;
		$css = array();

		// lightbox color theme
		$parts = explode('/', $params->lightbox, 2);
		if (count($parts) > 1) {
			$theme = $parts[1];
			$selectorMain = "{$id} .boxplusx-dialog, {$id} .boxplusx-detail";
			$selectorData = "{$id} .boxplusx-detail td";
			switch ($theme) {
				case 'dark':  // a dark theme for the dialog box
					$css[$selectorMain] = array(
						'background-color' => 'rgba(0,0,0,0.8)',
						'color' => '#fff'
					);
					$css[$selectorData] = array(
						'border-color' => '#fff'
					);
					break;
				case 'light':  // a light theme for the dialog box
					$css[$selectorMain] = array(
						'background-color' => 'rgba(255,255,255,0.8)',
						'color' => '#000'
					);
					$css[$selectorData] = array(
						'border-color' => '#000'
					);
					break;
			}
		}

		// quick-access navigation bar
		$navigation = array();
		switch ($params->lightbox_thumbs) {
			case 'inside':
			case 'outside':
				$navigation['height'] = $params->thumb_height.'px';
				break;
			case 'none':
			default:
				$navigation['display'] = 'none';
				break;
		}
		$css[$id.' .boxplusx-navigation'] = $navigation;

		$css[$id.' .boxplusx-navitem'] = array('width' => $params->thumb_width.'px');

		// transition animation
		switch ($params->lightbox_transition) {
			case 'linear':
				$easing = 'linear'; break;
			case 'quad':   // http://easings.net/#easeInOutQuad
				$easing = 'cubic-bezier(0.455, 0.03, 0.515, 0.955)'; break;
			case 'cubic':  // http://easings.net/#easeInOutCubic
				$easing = 'cubic-bezier(0.645, 0.045, 0.355, 1)'; break;
			case 'quart':  // http://easings.net/#easeInOutQuart
				$easing = 'cubic-bezier(0.77, 0, 0.175, 1)'; break;
			case 'quint':  // http://easings.net/#easeInOutQuint
				$easing = 'cubic-bezier(0.86, 0, 0.07, 1)'; break;
			case 'expo':   // http://easings.net/#easeInOutExpo
				$easing = 'cubic-bezier(1, 0, 0, 1)'; break;
			case 'circ':   // http://easings.net/#easeInOutCirc
				$easing = 'cubic-bezier(0.785, 0.135, 0.15, 0.86)'; break;
			case 'sine':   // http://easings.net/#easeInOutSine
				$easing = 'cubic-bezier(0.445, 0.05, 0.55, 0.95)'; break;
			case 'back':   // http://easings.net/#easeInOutBack
				$easing = 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'; break;
			case 'bounce':   // not supported in CSS
			case 'elastic':  // not supported in CSS
			default:
				$easing = 'linear'; break;
		}
		$css[$id.' .boxplusx-dialog.boxplusx-animation'] = array('transition-timing-function' => $easing);

		$instance->addStyles($css);
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

		// build lightbox engine options
		$jsparams = array();
		if ($params->id) {
			$jsparams['id'] = 'boxplusx_'.$params->id;
		}
		$jsparams['slideshow'] = $params->lightbox_slideshow;
		$jsparams['autostart'] = $params->lightbox_autostart;
		$jsparams['loop'] = $params->loop;
		switch ($params->lightbox_thumbs) {
			case 'inside':
				$position = 'bottom'; break;
			case 'outside':
				$position = 'below';  break;
			case 'none':
			default:
				$position = 'hidden'; break;
		}
		$jsparams['navigation'] = $position;
		$jsparams['protection'] = $params->protection;
		$user = JFactory::getUser();
		if ($params->metadata !== false && in_array($params->metadata, $user->getAuthorisedViewLevels())) {  // check if user is authorized to view metadata
			// add Exif.js third-party plug-in to parse EXIF and IPTC metadata
			$instance->addScript('/media/sigplus/engines/'.$this->getIdentifier().'/js/exif.js');

			$jsparams['metadata'] = true;
		}

		$language = JFactory::getLanguage();
		$jsparams['dir'] = $language->isRtl() ? 'rtl' : 'ltr';
		$jsparams = array_merge($jsparams, $params->lightbox_params);
		$jsselector = json_encode($selector);
		$jsparams = json_encode($jsparams, JSON_FORCE_OBJECT);

		// add document loaded event script with parameters
		$displayfunc = "(new BoxPlusXDialog({$jsparams})).bind(document.querySelectorAll({$jsselector}))";
		$instance->storeLightbox($selector, $displayfunc);
	}
}
