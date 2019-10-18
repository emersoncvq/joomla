<?php
/**
* @file
* @brief    sigplus Image Gallery Plus installer script
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2014 Levente Hunyadi
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

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'constants.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'setup.php';

define('SIGPLUS_PLUGIN_PATH', JPATH_ROOT.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.SIGPLUS_PLUGIN_FOLDER);
define('SIGPLUS_VERSION_FILE', SIGPLUS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'version.php');
define('SIGPLUS_MANIFEST_FILE', SIGPLUS_PLUGIN_PATH.DIRECTORY_SEPARATOR.'sigplus.xml');

class plgContentSigPlusNovoInstallerScript {
	function __construct($parent) { }

	function install($parent) { }

	function uninstall($parent) {
		self::removeCacheFolder();
	}

	function update($parent) { }

	function preflight($type, $parent) {
		switch ($type) {
			case 'install':
			case 'discover_install':
			case 'update':
				if (defined('SIGPLUS_VERSION')) {
					$current = SIGPLUS_VERSION;
				} elseif (file_exists(SIGPLUS_VERSION_FILE)) {  // available since 1.5.0
					include_once SIGPLUS_VERSION_FILE;
					$current = SIGPLUS_VERSION;  // version number of installed plug-in
				} elseif (file_exists(SIGPLUS_MANIFEST_FILE)) {  // available since 1.4.x
					if (function_exists('simplexml_load_file')) {
						$xml = simplexml_load_file(SIGPLUS_MANIFEST_FILE);
						$current = (string) $xml->version;
					} else {
						$current = false;
					}
				} else {  // not yet installed
					$current = false;
				}

				$required = '1.5.0';  // minimum version required for silent upgrade installation to succeed
				$supported = $current === false || $current === '$__'.'VERSION'.'__$' || version_compare($current, $required) >= 0;  // allow upgrading experimental versions

				// raise a warning unless upgrading one Novo version to another Novo version
				if (!$supported) {
					$message = str_replace(array('{$current}','{$required}'), array($current, $required), JText::_('SIGPLUS_INSTALLER_MIGRATE_SETTINGS'));
					$app = JFactory::getApplication();
					$app->enqueueMessage($message, 'warning');
				}

				SigPlusNovoDatabaseSetup::update();
				break;
		}
	}

	function postflight($type, $parent) {
		switch ($type) {
			case 'update':
				self::migrateConfiguration();
				self::removeCacheFolder(false);
			case 'install':  // runs after installation is complete
				self::checkDependencies();
				self::minifyAllStylesheets();
				SigPlusNovoDatabaseSetup::populate();
				break;
		}
	}

	private static function checkDependencies() {
		require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'librarian.php';

		$app = JFactory::getApplication();
		if (!is_gd_supported() && !is_imagick_supported()) {
			$app->enqueueMessage(JText::_('SIGPLUS_INSTALLER_LIBRARY_IMAGE'), 'warning');
		}
		if (!function_exists('exif_read_data')) {
			$app->enqueueMessage(JText::_('SIGPLUS_INSTALLER_LIBRARY_EXIF'), 'warning');
		}
		if (!extension_loaded('openssl')) {
			$app->enqueueMessage(JText::_('SIGPLUS_INSTALLER_LIBRARY_OPENSSL'), 'warning');
		}
		if (!ini_get('allow_url_fopen')) {
			$app->enqueueMessage(JText::_('SIGPLUS_INSTALLER_PHP_URL_FOPEN'), 'warning');
		}
		if (!in_array('http', stream_get_wrappers(), true)) {
			$app->enqueueMessage(JText::_('SIGPLUS_INSTALLER_PHP_HTTP_WRAPPER'), 'warning');
		}
	}

	/**
	* Minifies a group of stylesheets in the specified folder.
	* To be used as a callback function to walkdir.
	*/
	public static function minifyStylesheets($path, $files, $folders) {
		foreach ($files as $file) {
			if (preg_match('/(?<!\.min)\.css$/', $file)) {
				$original_file_full_path = $path.DIRECTORY_SEPARATOR.$file;
				$minified_file_full_path = $path.DIRECTORY_SEPARATOR.basename($file,'.css').'.min.css';
				$original_file_last_modified = filemtime($original_file_full_path);
				$minified_file_last_modified = filemtime($minified_file_full_path);

				// make sure there is no corresponding minified file or the original file has been modified since the minified file was created
				if ($original_file_last_modified !== false && $minified_file_last_modified !== false && $original_file_last_modified <= $minified_file_last_modified) {
					continue;
				}

				// minify stylesheet files
				$stylesheet = Minify_CSS_Compressor::process(file_get_contents($original_file_full_path));

				// substitute image URLs with data URIs
				$stylesheet = SigPlusNovoUriSubstitution::replace($stylesheet, $path.DIRECTORY_SEPARATOR.dirname($file), $count);

				// write file
				file_put_contents($minified_file_full_path, $stylesheet);
			}
		}
	}

	/**
	* Minifies stylesheets.
	*/
	private static function minifyAllStylesheets() {
		require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'filesystem.php';

		walkdir(JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.SIGPLUS_MEDIA_FOLDER, array(), -1, array(__CLASS__, 'minifyStylesheets'));
	}

	/**
	* Cleans a cache folder.
	* @param {string} $folder The name of the folder whose contents to remove from the cache.
	*/
	private static function removeCacheFolder($complete = true) {
		$folder = JPATH_ROOT.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'sigplus';  // use site cache folder, not administrator cache folder
		if (file_exists($folder)) {
			$files = scandir($folder);
			if ($files !== false) {
				foreach ($files as $file) {
					if ($file[0] != '.') {  // skip parent directory entries and hidden files
						unlink($folder.DIRECTORY_SEPARATOR.$file);
					}
				}
				if ($complete) {
					rmdir($folder);
				}
			}
		}
	}

	private static function migrateConfiguration() {
		$db = JFactory::getDbo();

		// read existing plug-in configuration settings
		$db->setQuery('SELECT params FROM #__extensions WHERE type = '.$db->quote('plugin').' AND folder = '.$db->quote('content').' AND element = '.$db->quote('sigplus'));
		$oldparams = json_decode($db->loadResult(), true);

		// make sure we are migrating a 1.4.x installation to a 1.5.x installation
		if (   !empty($oldparams)
			&& !isset($oldparams['preview_width'])
			&& !isset($oldparams['preview_height'])
			&& !isset($oldparams['library_image'])
			&& !isset($oldparams['tag_gallery'])
		) {
			$newparams = SigPlusNovoDatabaseSetup::migrateExtensionConfiguration($oldparams);

			// store the combined new and existing plug-in settings back as a JSON string
			$paramstring = json_encode($newparams);
			$db->setQuery('UPDATE #__extensions SET params = ' . $db->quote($paramstring) . ' WHERE type = '.$db->quote('plugin').' AND folder = '.$db->quote('content').' AND element = '.$db->quote('sigplus'));
			$db->execute();
		}
	}
}

/**
* Compatibility layer for installing sigplus Novo as a next version of sigplus.
*/
class plgContentSIGPlusInstallerScript extends plgContentSigPlusNovoInstallerScript {

}

class SigPlusNovoUriSubstitution {
	/**
	* The root path w.r.t. relative URLs are to be interpreted.
	*/
	private $root;

	private function __construct($root) {
		$this->root = $root;
	}

	public static function replace($contents, $path, &$count) {
		return preg_replace_callback(
			'#url\(("(?!\w+:)[^"]+"|\'(?!\w+:)[^\']+\'|(?!\w+:)[^()]+)\)#',  // (?!\w+:) is a negative lookahead assertion to skip absolute URLs
			array(new self($path), 'preg_replace'),
			$contents, -1, $count
		);
	}

	/**
	* Replaces an image URL reference with a data URI.
	* @param {array} $match A CSS url() function match.
	* This function is to be passed to preg_replace_callback.
	*/
	public function preg_replace($match) {
		$url = $match[1];
		if ($url[0] == '"' || $url == "'") {  // unquote quoted strings
			$url = trim($url, $url[0]);
		}
		$path = $this->root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $url);
		if (file_exists($path) && ($imagedata = getimagesize($path)) !== false) {
			return 'url("'.self::datauri($imagedata['mime'], file_get_contents($path)).'")';
		} else {
			return $match[0];
		}
	}

	/**
	* Converts raw data to a data URI.
	* @param {string} $mime Content MIME type.
	* @param {string} $data Content raw data.
	*/
	private static function datauri($mime, $data) {
		return 'data:' . $mime . ';base64,' . base64_encode($data);
	}
}

/**
* Class Minify_CSS_Compressor
* @package Minify
*/

/**
* Compress CSS
*
* This is a heavy regex-based removal of whitespace, unnecessary
* comments and tokens, and some CSS value minimization, where practical.
* Many steps have been taken to avoid breaking comment-based hacks,
* including the ie5/mac filter (and its inversion), but expect tricky
* hacks involving comment tokens in 'content' value strings to break
* minimization badly. A test suite is available.
*
* @package Minify
* @author Stephen Clay <steve@mrclay.org>
* @author http://code.google.com/u/1stvamp/ (Issue 64 patch)
* @see https://code.google.com/p/minify/downloads/detail?name=minify-2.1.5.zip
*/
class Minify_CSS_Compressor {

	/**
	* Minify a CSS string
	*
	* @param string $css
	*
	* @param array $options (currently ignored)
	*
	* @return string
	*/
	public static function process($css, $options = array())
	{
		$obj = new Minify_CSS_Compressor($options);
		return $obj->_process($css);
	}

	/**
	* @var array
	*/
	protected $_options = null;

	/**
	* Are we "in" a hack? I.e. are some browsers targetted until the next comment?
	*
	* @var bool
	*/
	protected $_inHack = false;


	/**
	* Constructor
	*
	* @param array $options (currently ignored)
	*/
	private function __construct($options) {
		$this->_options = $options;
	}

	/**
	* Minify a CSS string
	*
	* @param string $css
	*
	* @return string
	*/
	protected function _process($css)
	{
		$css = str_replace("\r\n", "\n", $css);

		// preserve empty comment after '>'
		// http://www.webdevout.net/css-hacks#in_css-selectors
		$css = preg_replace('@>/\\*\\s*\\*/@', '>/*keep*/', $css);

		// preserve empty comment between property and value
		// http://css-discuss.incutio.com/?page=BoxModelHack
		$css = preg_replace('@/\\*\\s*\\*/\\s*:@', '/*keep*/:', $css);
		$css = preg_replace('@:\\s*/\\*\\s*\\*/@', ':/*keep*/', $css);

		// apply callback to all valid comments (and strip out surrounding ws
		$css = preg_replace_callback('@\\s*/\\*([\\s\\S]*?)\\*/\\s*@'
			,array($this, '_commentCB'), $css);

		// remove ws around { } and last semicolon in declaration block
		$css = preg_replace('/\\s*{\\s*/', '{', $css);
		$css = preg_replace('/;?\\s*}\\s*/', '}', $css);

		// remove ws surrounding semicolons
		$css = preg_replace('/\\s*;\\s*/', ';', $css);

		// remove ws around urls
		$css = preg_replace('/
				url\\(      # url(
				\\s*
				([^\\)]+?)  # 1 = the URL (really just a bunch of non right parenthesis)
				\\s*
				\\)         # )
			/x', 'url($1)', $css);

		// remove ws between rules and colons
		$css = preg_replace('/
				\\s*
				([{;])              # 1 = beginning of block or rule separator
				\\s*
				([\\*_]?[\\w\\-]+)  # 2 = property (and maybe IE filter)
				\\s*
				:
				\\s*
				(\\b|[#\'"-])        # 3 = first character of a value
			/x', '$1$2:$3', $css);

		// remove ws in selectors
		$css = preg_replace_callback('/
				(?:              # non-capture
					\\s*
					[^~>+,\\s]+  # selector part
					\\s*
					[,>+~]       # combinators
				)+
				\\s*
				[^~>+,\\s]+      # selector part
				{                # open declaration block
			/x'
			,array($this, '_selectorsCB'), $css);

		// minimize hex colors
		$css = preg_replace('/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i'
			, '$1#$2$3$4$5', $css);

		// remove spaces between font families
		$css = preg_replace_callback('/font-family:([^;}]+)([;}])/'
			,array($this, '_fontFamilyCB'), $css);

		$css = preg_replace('/@import\\s+url/', '@import url', $css);

		// replace any ws involving newlines with a single newline
		$css = preg_replace('/[ \\t]*\\n+\\s*/', "\n", $css);

		// separate common descendent selectors w/ newlines (to limit line lengths)
		$css = preg_replace('/([\\w#\\.\\*]+)\\s+([\\w#\\.\\*]+){/', "$1\n$2{", $css);

		// Use newline after 1st numeric value (to limit line lengths).
		$css = preg_replace('/
			((?:padding|margin|border|outline):\\d+(?:px|em)?) # 1 = prop : 1st numeric value
			\\s+
			/x'
			,"$1\n", $css);

		// prevent triggering IE6 bug: http://www.crankygeek.com/ie6pebug/
		$css = preg_replace('/:first-l(etter|ine)\\{/', ':first-l$1 {', $css);

		return trim($css);
	}

	/**
	* Replace what looks like a set of selectors
	*
	* @param array $m regex matches
	*
	* @return string
	*/
	protected function _selectorsCB($m)
	{
		// remove ws around the combinators
		return preg_replace('/\\s*([,>+~])\\s*/', '$1', $m[0]);
	}

	/**
	* Process a comment and return a replacement
	*
	* @param array $m regex matches
	*
	* @return string
	*/
	protected function _commentCB($m)
	{
		$hasSurroundingWs = (trim($m[0]) !== $m[1]);
		$m = $m[1];
		// $m is the comment content w/o the surrounding tokens,
		// but the return value will replace the entire comment.
		if ($m === 'keep') {
			return '/**/';
		}
		if ($m === '" "') {
			// component of http://tantek.com/CSS/Examples/midpass.html
			return '/*" "*/';
		}
		if (preg_match('@";\\}\\s*\\}/\\*\\s+@', $m)) {
			// component of http://tantek.com/CSS/Examples/midpass.html
			return '/*";}}/* */';
		}
		if ($this->_inHack) {
			// inversion: feeding only to one browser
			if (preg_match('@
					^/               # comment started like /*/
					\\s*
					(\\S[\\s\\S]+?)  # has at least some non-ws content
					\\s*
					/\\*             # ends like /*/ or /**/
				@x', $m, $n)) {
				// end hack mode after this comment, but preserve the hack and comment content
				$this->_inHack = false;
				return "/*/{$n[1]}/**/";
			}
		}
		if (substr($m, -1) === '\\') { // comment ends like \*/
			// begin hack mode and preserve hack
			$this->_inHack = true;
			return '/*\\*/';
		}
		if ($m !== '' && $m[0] === '/') { // comment looks like /*/ foo */
			// begin hack mode and preserve hack
			$this->_inHack = true;
			return '/*/*/';
		}
		if ($this->_inHack) {
			// a regular comment ends hack mode but should be preserved
			$this->_inHack = false;
			return '/**/';
		}
		// Issue 107: if there's any surrounding whitespace, it may be important, so
		// replace the comment with a single space
		return $hasSurroundingWs // remove all other comments
			? ' '
			: '';
	}

	/**
	* Process a font-family listing and return a replacement
	*
	* @param array $m regex matches
	*
	* @return string
	*/
	protected function _fontFamilyCB($m)
	{
		// Issue 210: must not eliminate WS between words in unquoted families
		$pieces = preg_split('/(\'[^\']+\'|"[^"]+")/', $m[1], null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		$out = 'font-family:';
		while (null !== ($piece = array_shift($pieces))) {
			if ($piece[0] !== '"' && $piece[0] !== "'") {
				$piece = preg_replace('/\\s+/', ' ', $piece);
				$piece = preg_replace('/\\s?,\\s?/', ',', $piece);
			}
			$out .= $piece;
		}
		return $out . $m[2];
	}
}
