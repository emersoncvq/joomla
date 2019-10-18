<?php
/**
* @file
* @brief    sigplus Image Gallery Plus javascript engine service classes
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2017 Levente Hunyadi
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
defined( '_JEXEC' ) or die( 'Restricted access' );

define('SIGPLUS_RESOURCE_DATABASE', JPATH_CACHE.DIRECTORY_SEPARATOR.'sigplus.json');

/**
* Stores meta-information about resource files such as last modified time and content hash of CSS or JavaScript files.
* This prevents stale CSS and JavaScript resources from being delivered to the browser after an extension update by
* augmenting resource file URLs with version information, e.g. sigplus.min.css?v=123456abcdef
*/
class SigPlusNovoResourceCache {
	/**
	* A JSON database of file meta-information.
	*/
	private $manifest;
	/**
	* The global MD5 hash of the meta-information manifest file.
	*/
	private $hash;

	public function __construct() {
		if (file_exists(SIGPLUS_RESOURCE_DATABASE)) {
			$data = file_get_contents(SIGPLUS_RESOURCE_DATABASE);
		}
		if (isset($data)) {
			$this->manifest = json_decode($data);
			if (isset($this->manifest)) {
				$this->hash = md5($data);
			}
		} else {
			$this->manifest = new stdClass;
		}
	}

	public function __destruct() {
		$data = json_encode($this->manifest, JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES);
		if ($this->hash != md5($data)) {  // entries have been updated in the manifest
			file_put_contents(SIGPLUS_RESOURCE_DATABASE, $data);
		}
	}

	public function lookup($relpath) {
		if (!isset($this->manifest->{$relpath})) {
			$this->manifest->{$relpath} = new stdClass;
		}
		$item =& $this->manifest->{$relpath};

		$abspath = JPATH_ROOT.str_replace('/', DIRECTORY_SEPARATOR, $relpath);
		if (!isset($item->hash) || !isset($item->mtime) || filemtime($abspath) >= $item->mtime) {
			$item->hash = md5_file($abspath);
			$item->mtime = filemtime($abspath);
		}

		return $item;
	}
}

/**
* Service class for JavaScript code management.
*/
class SigPlusNovoEngineServices {
	/** True if one of the engines uses the MooTools library. */
	private $mootools = false;
	/** True if one of the engines uses the jQuery library. */
	private $jquery = false;
	/** True if one of the engines uses the Bootstrap library. */
	private $bootstrap = false;
	/** JavaScript snippets to run on HTML DOM ready event. */
	private $scripts = array();

	/** Engine directory. */
	private $engines = array();

	/** Whether to use uncompressed versions of scripts. */
	public $debug = null;  // true = enabled, false = disabled, null = not set (disabled)

	private $cache;

	/** Singleton instance. */
	private static $object = null;

	public static function initialize() {
		if (!isset(self::$object)) {
			self::$object = new SigPlusNovoEngineServices();
		}
	}

	public static function instance() {
		return self::$object;
	}

	public function __construct() {
		$this->cache = new SigPlusNovoResourceCache();
	}

	/**
	* Adds MooTools support.
	*/
	public function addMooTools() {
		if ($this->mootools) {
			return;
		}

		// MooTools Core is native to Joomla, modify Joomla if you wish to load it from a CDN
		JHTML::_('behavior.framework');

		$this->mootools = true;
	}

	/**
	* Adds jQuery support.
	*/
	public function addJQuery() {
		if ($this->jquery) {
			return;
		}

		JHTML::_('jquery.framework');

		$this->jquery = true;
	}

	public function addBootstrap() {
		if ($this->bootstrap) {
			return;
		}

		$this->addJQuery();
		JHTML::_('bootstrap.framework');

		$this->bootstrap = true;
	}

	/**
	* Fetch an engine from the engine registry, adding a new instance if necessary.
	* @param {string} $enginetype Engine type (e.g. "lightbox" or "rotator").
	* @param {string} $engine A unique name used to instantiate the engine.
	*/
	private function getEngine($enginetype, $engine) {
		if (isset($this->engines[$engine])) {
			return $this->engines[$engine];
		} else {
			return $engines[$engine] = SigPlusNovoEngine::create($enginetype, $engine);
		}
	}

	public function getLightboxEngine($lightboxengine) {
		return $this->getEngine('lightbox', $lightboxengine);
	}

	public function getRotatorEngine($rotatorengine) {
		return $this->getEngine('rotator', $rotatorengine);
	}

	public function getCaptionEngine($captionengine) {
		return $this->getEngine('caption', $captionengine);
	}

	public function addCustomTag($tag) {
		/** Custom tags added to page header. */
		static $customtags = array();

		if (!in_array($tag, $customtags)) {
			$document = JFactory::getDocument();
			if ($document->getType() == 'html') {  // custom tags are supported by HTML document type only
				$document->addCustomTag($tag);
			}
			$customtags[] = $tag;
		}
	}

	/**
	* Returns a path to the minified version of a style or script file if available and newer than the original.
	* @param {string} $relpath A path relative to the Joomla root.
	*/
	private function getResourceRelativePath($relpath) {
		$basename = pathinfo($relpath, PATHINFO_BASENAME);  // e.g. "sigplus.css"
		$folder = pathinfo($relpath, PATHINFO_DIRNAME);  // e.g. "/plugins/content/sigplus/css"
		$p = strrpos($basename, '.');  // search from backwards
		if ($p !== false) {
			$filename = substr($basename, 0, $p);  // drop extension from filename
			$extension = substr($basename, $p);
		} else {
			$filename = $basename;
			$extension = '';
		}

		$path = JPATH_ROOT.str_replace('/', DIRECTORY_SEPARATOR, $relpath);
		$dir = pathinfo($path, PATHINFO_DIRNAME);
		$original = $dir.DIRECTORY_SEPARATOR.$basename;
		$minified = $dir.DIRECTORY_SEPARATOR.$filename.'.min'.$extension;
		if (!$this->debug && (!file_exists($original) || file_exists($minified) && filemtime($minified) >= filemtime($original))) {
			return $folder.'/'.$filename.'.min'.$extension;
		} else {
			return $relpath;
		}
	}

	/**
	* Returns an URL to the minified version of a style or script file if available and newer than the original.
	* @param {string} $relpath A path relative to the Joomla root.
	*/
	private function getResourceURL($relpath) {
		$relpath = $this->getResourceRelativePath($relpath);
		if (parse_url(JURI::base(false), PHP_URL_HOST) != 'localhost') {
			$item = $this->cache->lookup($relpath);
			if (!empty($item->hash)) {
				$relpath .= "?v={$item->hash}";  // ensures that the URL is unique
			}
		}
		return JURI::base(true).$relpath;
	}

	/**
	* Adds standard stylesheet references to the HTML head.
	*/
	public function addStandardStyles() {
		$document = JFactory::getDocument();
		$document->addStyleSheet($this->getResourceURL('/media/sigplus/css/sigplus.css'));
	}

	/**
	* Adds a stylesheet reference to the HTML head.
	*/
	public function addStylesheet($path, $attrs = null) {
		$url = $this->getResourceURL($path);
		$document = JFactory::getDocument();
		if (isset($attrs)) {
			$document->addStyleSheet($url, 'text/css', null, $attrs);
		} else {
			$document->addStyleSheet($url);
		}
	}

	public function addConditionalStylesheet($path, $version = 9, array $attrs = array()) {
		$attrstring = '';
		foreach ($attrs as $key => $value) {
			$attrstring .= ' '.$key.'="'.htmlspecialchars($value).'"';
		}
		$this->addCustomTag('<!--[if lt IE '.$version.']><link rel="stylesheet" href="'.$this->getResourceURL($path).'" type="text/css"'.$attrstring.' /><![endif]-->');
	}

	public function addStyles($selectors) {
		$css = '';
		foreach ($selectors as $selector => $rules) {
			if (!empty($rules)) {
				$css .= $selector." {\n";
				foreach ($rules as $name => $value) {
					$css .= $name.':'.$value.";\n";
				}
				$css .= "}\n";
			}
		}

		if (!empty($css)) {
			$document = JFactory::getDocument();
			$document->addStyleDeclaration($css);
		}
	}

	/**
	* Adds a script reference to the HTML head.
	*/
	public function addScript($path) {
		$document = JFactory::getDocument();
		$document->addScript($this->getResourceURL($path), array(), array('defer' => true));
	}

	/**
	* Appends a JavaScript snippet to the code to be run on the HTML DOM ready event.
	* This method is usually invoked by engines.
	*/
	public function addOnReadyScript($script) {
		$this->scripts[] = $script;
	}

	/**
	* Appends the contents of a JavaScript file to the code to be run on the HTML DOM ready event.
	*/
	public function addOnReadyScriptFile($path, array $map = array()) {
		$path = str_replace('/', DIRECTORY_SEPARATOR, $this->getResourceRelativePath($path));
		if ($contents = file_get_contents(JPATH_BASE.DIRECTORY_SEPARATOR.$path)) {
			$searchmap = array();
			foreach ($map as $key => $value) {
				$searchmap['{$__'.$key.'__$}'] = addslashes($value);
				$searchmap['$__'.$key.'__$'] = '"'.addslashes($value).'"';  // wrap into string
			}
			$this->addOnReadyScript(str_replace(array_keys($searchmap), array_values($searchmap), $contents));
		}
	}

	/**
	* Adds all HTML DOM ready event scripts to the page in an HTML script block.
	* This method is usually invoked in the HTML content generation phase.
	*/
	public function addOnReadyEvent() {
		if (!empty($this->scripts)) {
			// add script block to page
			$document = JFactory::getDocument();
			$document->addScriptDeclaration('document.addEventListener("DOMContentLoaded", function () {'."\n".implode("\n", $this->scripts)."\n".'}, false);');

			// clear "on ready" event scripts
			$this->scripts = array();  // clear scripts added to document
		}
	}

	/**
	* Saves the JavaScript variable "lightbox" in the current scope to the elements storage.
	* @see self::activateLightbox
	* @param {string} $displayfunc A JavaScript function that takes an index, and displays the pop-up window.
	*/
	public function storeLightbox($selector, $displayfunc) {
		$selector = json_encode($selector);
		$script = "window.sigplus=window.sigplus||{};window.sigplus.lightbox=window.sigplus.lightbox||{};window.sigplus.lightbox[{$selector}]={$displayfunc};";
		$instance = SigPlusNovoEngineServices::instance();
		$instance->addOnReadyScript($script);
	}

	/**
	* Adds JavaScript code subscribed to an anchor click event to programmatically activate a gallery.
	* @param {int} $index The (one-based) index of the image within the gallery to show.
	* @see self::storeLightbox
	*/
	public function activateLightbox($linkid, $selector, $index = 1) {
		$linkid = json_encode($linkid);
		$selector = json_encode($selector);
		$zeroindex = $index - 1;
		$script =
			"(function (e) {".
				"e && e.addEventListener('click', function () {".
					"var fn = window.sigplus.lightbox[{$selector}];".
					"fn && fn({$zeroindex});".
				"});".
			"})(document.getElementById({$linkid}));";
		$instance = SigPlusNovoEngineServices::instance();
		$instance->addOnReadyScript($script);
		return false;
	}
}
SigPlusNovoEngineServices::initialize();

/**
* Base class for engines based on a javascript framework.
*/
abstract class SigPlusNovoEngine {
	abstract public function getIdentifier();

	public function getLibrary() {
		return null;
	}

	/**
	* Adds style sheet references to the HTML @c head element.
	*/
	public function addStyles($selector, SigPlusNovoGalleryParameters $params) {
		$instance = SigPlusNovoEngineServices::instance();
		if (file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.SIGPLUS_MEDIA_FOLDER.DIRECTORY_SEPARATOR.'engines'.DIRECTORY_SEPARATOR.$this->getIdentifier().DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$this->getIdentifier().'.css')) {
			$instance->addStylesheet('/media/sigplus/engines/'.$this->getIdentifier().'/css/'.$this->getIdentifier().'.css');
		}

		// add right-to-left reading order stylesheet (if available)
		$language = JFactory::getLanguage();
		if ($language->isRTL() && file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.SIGPLUS_MEDIA_FOLDER.DIRECTORY_SEPARATOR.'engines'.DIRECTORY_SEPARATOR.$this->getIdentifier().DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$this->getIdentifier().'.rtl.css')) {
			$instance->addStylesheet('/media/sigplus/engines/'.$this->getIdentifier().'/css/'.$this->getIdentifier().'.rtl.css');
		}
	}

	/**
	* Adds script references to the HTML @c head element.
	* @param {string} $selector A CSS selector.
	* @param $params Gallery parameters.
	*/
	public function addScripts($selector, SigPlusNovoGalleryParameters $params) {
		$instance = SigPlusNovoEngineServices::instance();

		// add script library dependency
		switch ($this->getLibrary()) {
			case 'mootools':  $instance->addMooTools();  break;
			case 'jquery':    $instance->addJQuery();    break;
			case 'bootstrap': $instance->addBootstrap(); break;
		}

		$instance->addScript('/media/sigplus/engines/'.$this->getIdentifier().'/js/'.$this->getIdentifier().'.js');
	}

	/**
	* Factory method for engine instantiation.
	*/
	public static function create($enginetype, $engine) {
		// check for parameters passed to engine
		$pos = strpos($engine, '/');
		if ($pos !== false) {
			$params = array('theme'=>substr($engine, $pos+1));
			$engine = substr($engine, 0, $pos);
		} else {
			$params = array();
		}

		if (!ctype_alnum($engine)) {  // simple name required
			throw new SigPlusNovoEngineUnavailableException($engine, $enginetype);  // naming failure
		}

		$engineclass = 'SigPlusNovo'.$engine.$enginetype.'Engine';
		$enginedir = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'engines';
		if (is_file($enginefile = $enginedir.DIRECTORY_SEPARATOR.$enginetype.DIRECTORY_SEPARATOR.$engine.'.php')) {
			require_once $enginefile;
		}
		if (class_exists($engineclass)) {
			return new $engineclass($params);
		} else {
			throw new SigPlusNovoEngineUnavailableException($engine, $enginetype);  // inclusion failure
		}
	}
}

/**
* Base class for pop-up window (lightbox-clone) support.
*/
abstract class SigPlusNovoLightboxEngine extends SigPlusNovoEngine {
	/**
	* A default constructor that ignores all optional arguments.
	*/
	public function __construct($params = false) { }

	/**
	* Whether the pop-up window supports displaying arbitrary HTML content.
	* @return True if the pop-up window is not restricted to displaying images only.
	* @deprecated
	*/
	public function isInlineContentSupported() {
		return false;
	}

	/**
	* Whether the pop-up window supports fast navigation by displaying a ribbon of thumbnails
	* the user can click and jump to a particular image.
	* @deprecated
	*/
	public function isQuickNavigationSupported() {
		return false;
	}

	/**
	* Adds script references to the HTML head to bind the click event to lightbox pop-up activation.
	* @deprecated
	*/
	public function addInitializationScripts($selector, $params) {
		$this->addScripts($selector, $params);
		$instance = SigPlusNovoEngineServices::instance();
		$instance->addScript('/plugins/content/sigplus/engines/'.$this->getIdentifier().'/js/initialization.js');
	}

	/**
	* Adds script references to the HTML head to support fully customized gallery initialization.
	* @remark When overriding this method, the base method should normally NOT be called.
	* @deprecated
	*/
	public function addActivationScripts($selector, $params) {
		$this->addScripts($selector, $params);
		$instance = SigPlusNovoEngineServices::instance();
		$instance->addScript('/plugins/content/sigplus/engines/'.$this->getIdentifier().'/js/activation.js');
	}

	/**
	* The value to use in the attribute "rel" of anchor elements to bind the lightbox-clone.
	* @param gallery The unique identifier for the image gallery. Images in the same gallery are grouped together.
	* @return A valid value for the attribute "rel" of an element "a".
	* @deprecated
	*/
	public function getLinkAttribute($gallery = false) {
		if ($gallery !== false) {
			return $this->getIdentifier().'-'.$gallery;
		} else {
			return $this->getIdentifier();
		}
	}
}

/**
* Base class for image rotator support.
*/
abstract class SigPlusNovoRotatorEngine extends SigPlusNovoEngine {
	/**
	* Whether the rotator engine has its own built-in way to display image captions.
	*/
	public function isCaptionSupported() {
		return false;
	}
}

/**
* Base class for image caption support.
*/
abstract class SigPlusNovoCaptionEngine extends SigPlusNovoEngine {

}
