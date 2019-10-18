<?php
/**
* @file
* @brief    sigplus Image Gallery Plus slideplus image rotator engine
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Support class for the slideplus rotator engine, written in plain JavaScript.
* @see http://hunyadi.info.hu/projects/slideplus/
*/
class SigPlusNovoSlidePlusRotatorEngine extends SigPlusNovoRotatorEngine {
	public function getIdentifier() {
		return 'slideplus';
	}

	public function isCaptionSupported() {
		return true;
	}

	private static function cssCalc($values) {
		$values = array_filter($values, 'strlen');  // removes all NULL, FALSE and empty strings but leaves 0 (zero) values
		switch (count($values)) {
			case 0:
				return false;
			case 1:
				$total_length = reset($values);
				return "{$total_length}";
			default:
				$total_length = implode(' + ', $values);  // spaces around plus sign are mandatory in CSS
				return "calc({$total_length})";
		}
	}

	private static function cssPadding($padding) {
		// check if some elements are missing
		if ($padding['top'] === false || $padding['bottom'] === false || $padding['left'] === false || $padding['right'] === false) {
			$css = array();
			if ($padding['top'] !== false) {
				$css['padding-top'] = $padding['top'];
			}
			if ($padding['bottom'] !== false) {
				$css['padding-bottom'] = $padding['bottom'];
			}
			if ($padding['left'] !== false) {
				$css['padding-left'] = $padding['left'];
			}
			if ($padding['right'] !== false) {
				$css['padding-right'] = $padding['right'];
			}
			return $css;
		}

		// all values are set
		if ($padding['left'] == $padding['right']) {
			if ($padding['top'] == $padding['bottom']) {
				if ($padding['top'] == $padding['left']) {
					// all four values are equal
					return array('padding' => $padding['top']);
				} else {
					// top and bottom, and left and right values are pairwise equal
					return array('padding' => "{$padding['top']} {$padding['left']}");
				}
			} else {
				// left and right values are equal
				return array('padding' => "{$padding['top']} {$padding['left']} {$padding['bottom']}");
			}
		} else {
			return array('padding' => "{$padding['top']} {$padding['right']} {$padding['bottom']} {$padding['left']}");
		}
	}

	public function addStyles($selector, SigPlusNovoGalleryParameters $params) {
		// add main stylesheet
		parent::addStyles($selector, $params);

		$css = array();

		// preferred width for items
		$css["{$selector} .slideplus-slot"] = array('width' => $params->preview_width.'px');

		// scalable component of item height
		$scalable_size = (100*$params->preview_height/$params->preview_width).'%';  // length proportional to image dimensions

		// caption height does not affect image aspect unless caption is shown above or below image
		$caption_height_above = false;
		$caption_height_below = false;
		switch ($params->caption_position) {
			case 'above':
				$caption_height_above = $params->caption_height;
				break;
			case 'below':
				$caption_height_below = $params->caption_height;
				break;
		}

		// aspect space allocation calculated in terms of preferred width, taking into account fixed-size length components
		$aspect_padding = array(
			// value for CSS padding top includes space reserved for captions above images
			'top' => self::cssCalc(array($params->preview_border_width, $params->preview_padding, $caption_height_above)),
			// value for CSS padding bottom includes artificial height to help maintain image aspect ratio and space reserved for captions below images
			'bottom' => self::cssCalc(array($params->preview_border_width, $params->preview_padding, $scalable_size, $caption_height_below)),
			'left' => self::cssCalc(array($params->preview_border_width, $params->preview_padding)),
			'right' => self::cssCalc(array($params->preview_border_width, $params->preview_padding))
		);
		$css["{$selector} .slideplus-aspect"] = self::cssPadding($aspect_padding);

		// navigation buttons
		if (!$params->rotator_buttons) {
			$css["{$selector} .slideplus-viewport .slideplus-previous"] = array('display' => 'none');
			$css["{$selector} .slideplus-viewport .slideplus-next"] = array('display' => 'none');
		}
		switch ($params->rotator_navigation) {
			case 'top':
				$css["{$selector} .slideplus-navigation.slideplus-bottom"] = array('display' => 'none');
				break;
			case 'bottom':
				$css["{$selector} .slideplus-navigation.slideplus-top"] = array('display' => 'none');
				break;
			case 'none':
				$css["{$selector} .slideplus-navigation.slideplus-top, {$selector} .slideplus-navigation.slideplus-bottom"] = array('display' => 'none');
				break;
			case 'both':
				break;
		}

		// navigation paging
		if (!$params->rotator_links) {
			$css["{$selector} .slideplus-pager"] = array('display' => 'none');
		}

		// item alignment
		$alignment = array();
		$horzalign = false;
		switch ($params->rotator_alignment) {
			case 'w': case 'c': case 'e': $horzalign = 'center'; break;
			case 'nw': case 'w': case 'sw': $horzalign = 'flex-start'; break;
			case 'ne': case 'e': case 'se': $horzalign = 'flex-end'; break;
		}
		if ($horzalign !== false) {
			$alignment['justify-content'] = $horzalign;
		}
		$vertalign = false;
		switch ($params->rotator_alignment) {
			case 'w': case 'c': case 'e': $vertalign = 'center'; break;
			case 'nw': case 'n': case 'ne': $vertalign = 'flex-start'; break;
			case 'sw': case 's': case 'se': $vertalign = 'flex-end'; break;
		}
		if ($vertalign !== false) {
			$alignment['align-items'] = $vertalign;
		}
		if ($params->caption_height !== false) {
			switch ($params->caption_position) {
				case 'above':
					$alignment['padding-top'] = $params->caption_height;
					break;
				case 'below':
					$alignment['padding-bottom'] = $params->caption_height;
					break;
			}
		}
		if (!empty($alignment)) {
			$css["{$selector} .slideplus-content"] = $alignment;
		}

		// caption visibility
		$caption_attributes = array();
		switch ($params->caption_visibility) {
			case 'none':
				$caption_attributes['display'] = 'none';
				break;
			case 'always':
				$caption_attributes['visibility'] = 'visible';
				break;
		}
		if ($params->caption_height !== false) {
			$caption_attributes['height'] = $params->caption_height;
		}
		if (!empty($caption_attributes)) {
			$css["{$selector} .slideplus-caption"] = $caption_attributes;
		}

		// transition animation
		$animation = array();
		if ($params->rotator_duration > 0) {
			$animation['animation-duration'] = $params->rotator_duration.'ms';
		}
		$easing = false;
		switch ($params->rotator_transition) {
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
				break;
		}
		if ($easing !== false) {
			$animation['animation-timing-function'] = $easing;
		}
		$css["{$selector} .slideplus-stripe"] = $animation;

		$instance = SigPlusNovoEngineServices::instance();
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

		// set rotator engine options
		$jsparams = array();
		$jsparams['rows'] = $params->rows;
		$jsparams['cols'] = $params->cols;
		$jsparams['loop'] = $params->loop;
		$jsparams['orientation'] = $params->rotator_orientation;
		$jsparams['step'] = $params->rotator_step;
		$jsparams['links'] = $params->rotator_links;
		$jsparams['delay'] = $params->rotator_delay;
		if ($params->sort_criterion == SIGPLUS_SORT_RANDOM) {
			$jsparams['random'] = true;
		}
		$caption_position = $params->caption_position;
		switch ($caption_position) {  // map some position constants
			case 'overlay-bottom': $caption_position = 'bottom'; break;
			case 'overlay-top': $caption_position = 'top'; break;
		}
		$jsparams['captions'] = $caption_position;
		$jsparams['protection'] = $params->protection;
		$language = JFactory::getLanguage();
		$jsparams['dir'] = $language->isRtl() ? 'rtl' : 'ltr';
		$jsparams = array_merge($jsparams, $params->rotator_params);
		$jsparams = json_encode($jsparams);
		$selector = json_encode("{$selector} ul");

		$script = "new SlidePlusSlider(document.querySelector({$selector}), {$jsparams}, function (el) { return el.querySelector('.sigplus-image').getAttribute('data-title') || el.querySelector('.sigplus-image img').alt; });";
		$instance->addOnReadyScript($script);
	}
}
