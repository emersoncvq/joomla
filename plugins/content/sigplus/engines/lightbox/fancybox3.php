<?php
/**
* @file
* @brief    sigplus Image Gallery Plus Fancybox3 lightweight pop-up window engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for jQuery-based Fancybox3 pop-up window engine.
* @see http://fancyapps.com/fancybox/3/
*/
class SigPlusNovoFancybox3LightboxEngine extends SigPlusNovoLightboxEngine {
	public function getIdentifier() {
		return 'fancybox3';
	}

	public function getLibrary() {
		return 'jquery';
	}

	/**
	* Adds style sheet references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addStyles($selector, SigPlusNovoGalleryParameters $params) {
		$document = JFactory::getDocument();
		$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.5/jquery.fancybox.min.css');
	}

	/**
	* Adds script references to the HTML head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addScripts($selector, SigPlusNovoGalleryParameters $params) {
		$instance = SigPlusNovoEngineServices::instance();

		$document = JFactory::getDocument();
		$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.5/jquery.fancybox.min.js', array(), array('defer' => true));

		$jsparams = array();
		$jsparams['loop'] = $params->loop;
		$jsparams['protect'] = $params->protection;
		if ($params->lightbox_slideshow) {
			$slideshowparams = array();
			$slideshowparams['speed'] = $params->lightbox_slideshow;
			$slideshowparams['autoStart'] = $params->lightbox_autostart;
			$jsparams['slideShow'] = $slideshowparams;
		} else {
			$jsparams['slideShow'] = false;
		}
		if ($params->lightbox_thumbs === false) {
			$jsparams['thumbs'] = false;
		}
		$language = JFactory::getLanguage();
		$languagecode = substr($language->getTag(), 0, 2);
		$jsparams['lang'] = $languagecode;
		switch ($languagecode) {
			case 'en':
			case 'de':
				// English and German localization is included in the CDN-distributed Fancybox3 version
				break;
			default:
				// import localization for all supported languages
				$instance->addScript('/media/sigplus/engines/'.$this->getIdentifier().'/js/'.$this->getIdentifier().'.lang.js');
		}
		$buttons = array();
		$buttons[] = 'zoom';
		$user = JFactory::getUser();
		if ($params->download !== false && in_array($params->download, $user->getAuthorisedViewLevels())) {  // check if user is authorized to download image
			$buttons[] = 'download';
		}
		$buttons[] = 'slideShow';
		$buttons[] = 'fullScreen';
		$buttons[] = 'thumbs';
		$buttons[] = 'close';
		$jsparams['buttons'] = $buttons;
		$jsparams = array_merge($jsparams, $params->lightbox_params);
		$script =
			'jQuery('.json_encode($selector).')'.
				'.attr("data-fancybox", '.json_encode($params->id).')'.
				'.fancybox(jQuery.extend({'.
					'caption: function(instance,item) {'.
						'return caption = jQuery(this).data("summary") || "";'.
					'}'.
				'}, '.json_encode($jsparams, JSON_FORCE_OBJECT).'));';
		$instance->addOnReadyScript($script);
	}
}
