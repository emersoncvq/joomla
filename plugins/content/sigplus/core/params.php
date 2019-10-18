<?php
/**
* @file
* @brief    sigplus Image Gallery Plus global and local parameters
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

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'filesystem.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'useragent.php';

define('SIGPLUS_RETINA_SCALE', 2);  // default scale factor for preview images targeting retina displays

define('SIGPLUS_SORT_LABELS', 1);

// sort criterion override modes
define('SIGPLUS_SORT_FILENAME', 2);            // sort based on file name ignoring order in labels file
define('SIGPLUS_SORT_MTIME', 4);               // sort based on last modified time ignoring order in labels file
define('SIGPLUS_SORT_FILESIZE', 8);            // sort based on file size ignoring order in labels file
define('SIGPLUS_SORT_RANDOM', 128);            // random order

define('SIGPLUS_SORT_LABELS_OR_FILENAME', SIGPLUS_SORT_LABELS | SIGPLUS_SORT_FILENAME);  // sort based on labels file with fallback to file name
define('SIGPLUS_SORT_LABELS_OR_MTIME', SIGPLUS_SORT_LABELS | SIGPLUS_SORT_MTIME);        // sort based on labels file with fallback to last modified time
define('SIGPLUS_SORT_LABELS_OR_FILESIZE', SIGPLUS_SORT_LABELS | SIGPLUS_SORT_FILESIZE);  // sort based on labels file with fallback to file size
define('SIGPLUS_SORT_LABELS_OR_RANDOM', SIGPLUS_SORT_LABELS | SIGPLUS_SORT_RANDOM);      // sort based on labels file with fallback to random order

// sort order
define('SIGPLUS_SORT_ASCENDING', 0);
define('SIGPLUS_SORT_DESCENDING', 1);

class SigPlusNovoColors {
	/** Maps color names to color codes. */
	private static $colors;

	public static function translate($value) {
		if (!isset(self::$colors)) {
			$colors = array(
				'AliceBlue'=>0xF0F8FF,
				'AntiqueWhite'=>0xFAEBD7,
				'Aqua'=>0x00FFFF,
				'Aquamarine'=>0x7FFFD4,
				'Azure'=>0xF0FFFF,
				'Beige'=>0xF5F5DC,
				'Bisque'=>0xFFE4C4,
				'Black'=>0x000000,
				'BlanchedAlmond'=>0xFFEBCD,
				'Blue'=>0x0000FF,
				'BlueViolet'=>0x8A2BE2,
				'Brown'=>0xA52A2A,
				'BurlyWood'=>0xDEB887,
				'CadetBlue'=>0x5F9EA0,
				'Chartreuse'=>0x7FFF00,
				'Chocolate'=>0xD2691E,
				'Coral'=>0xFF7F50,
				'CornflowerBlue'=>0x6495ED,
				'Cornsilk'=>0xFFF8DC,
				'Crimson'=>0xDC143C,
				'Cyan'=>0x00FFFF,
				'DarkBlue'=>0x00008B,
				'DarkCyan'=>0x008B8B,
				'DarkGoldenRod'=>0xB8860B,
				'DarkGray'=>0xA9A9A9,
				'DarkGrey'=>0xA9A9A9,
				'DarkGreen'=>0x006400,
				'DarkKhaki'=>0xBDB76B,
				'DarkMagenta'=>0x8B008B,
				'DarkOliveGreen'=>0x556B2F,
				'Darkorange'=>0xFF8C00,
				'DarkOrchid'=>0x9932CC,
				'DarkRed'=>0x8B0000,
				'DarkSalmon'=>0xE9967A,
				'DarkSeaGreen'=>0x8FBC8F,
				'DarkSlateBlue'=>0x483D8B,
				'DarkSlateGray'=>0x2F4F4F,
				'DarkSlateGrey'=>0x2F4F4F,
				'DarkTurquoise'=>0x00CED1,
				'DarkViolet'=>0x9400D3,
				'DeepPink'=>0xFF1493,
				'DeepSkyBlue'=>0x00BFFF,
				'DimGray'=>0x696969,
				'DimGrey'=>0x696969,
				'DodgerBlue'=>0x1E90FF,
				'FireBrick'=>0xB22222,
				'FloralWhite'=>0xFFFAF0,
				'ForestGreen'=>0x228B22,
				'Fuchsia'=>0xFF00FF,
				'Gainsboro'=>0xDCDCDC,
				'GhostWhite'=>0xF8F8FF,
				'Gold'=>0xFFD700,
				'GoldenRod'=>0xDAA520,
				'Gray'=>0x808080,
				'Grey'=>0x808080,
				'Green'=>0x008000,
				'GreenYellow'=>0xADFF2F,
				'HoneyDew'=>0xF0FFF0,
				'HotPink'=>0xFF69B4,
				'IndianRed'=>0xCD5C5C,
				'Indigo'=>0x4B0082,
				'Ivory'=>0xFFFFF0,
				'Khaki'=>0xF0E68C,
				'Lavender'=>0xE6E6FA,
				'LavenderBlush'=>0xFFF0F5,
				'LawnGreen'=>0x7CFC00,
				'LemonChiffon'=>0xFFFACD,
				'LightBlue'=>0xADD8E6,
				'LightCoral'=>0xF08080,
				'LightCyan'=>0xE0FFFF,
				'LightGoldenRodYellow'=>0xFAFAD2,
				'LightGray'=>0xD3D3D3,
				'LightGrey'=>0xD3D3D3,
				'LightGreen'=>0x90EE90,
				'LightPink'=>0xFFB6C1,
				'LightSalmon'=>0xFFA07A,
				'LightSeaGreen'=>0x20B2AA,
				'LightSkyBlue'=>0x87CEFA,
				'LightSlateGray'=>0x778899,
				'LightSlateGrey'=>0x778899,
				'LightSteelBlue'=>0xB0C4DE,
				'LightYellow'=>0xFFFFE0,
				'Lime'=>0x00FF00,
				'LimeGreen'=>0x32CD32,
				'Linen'=>0xFAF0E6,
				'Magenta'=>0xFF00FF,
				'Maroon'=>0x800000,
				'MediumAquaMarine'=>0x66CDAA,
				'MediumBlue'=>0x0000CD,
				'MediumOrchid'=>0xBA55D3,
				'MediumPurple'=>0x9370D8,
				'MediumSeaGreen'=>0x3CB371,
				'MediumSlateBlue'=>0x7B68EE,
				'MediumSpringGreen'=>0x00FA9A,
				'MediumTurquoise'=>0x48D1CC,
				'MediumVioletRed'=>0xC71585,
				'MidnightBlue'=>0x191970,
				'MintCream'=>0xF5FFFA,
				'MistyRose'=>0xFFE4E1,
				'Moccasin'=>0xFFE4B5,
				'NavajoWhite'=>0xFFDEAD,
				'Navy'=>0x000080,
				'OldLace'=>0xFDF5E6,
				'Olive'=>0x808000,
				'OliveDrab'=>0x6B8E23,
				'Orange'=>0xFFA500,
				'OrangeRed'=>0xFF4500,
				'Orchid'=>0xDA70D6,
				'PaleGoldenRod'=>0xEEE8AA,
				'PaleGreen'=>0x98FB98,
				'PaleTurquoise'=>0xAFEEEE,
				'PaleVioletRed'=>0xD87093,
				'PapayaWhip'=>0xFFEFD5,
				'PeachPuff'=>0xFFDAB9,
				'Peru'=>0xCD853F,
				'Pink'=>0xFFC0CB,
				'Plum'=>0xDDA0DD,
				'PowderBlue'=>0xB0E0E6,
				'Purple'=>0x800080,
				'Red'=>0xFF0000,
				'RosyBrown'=>0xBC8F8F,
				'RoyalBlue'=>0x4169E1,
				'SaddleBrown'=>0x8B4513,
				'Salmon'=>0xFA8072,
				'SandyBrown'=>0xF4A460,
				'SeaGreen'=>0x2E8B57,
				'SeaShell'=>0xFFF5EE,
				'Sienna'=>0xA0522D,
				'Silver'=>0xC0C0C0,
				'SkyBlue'=>0x87CEEB,
				'SlateBlue'=>0x6A5ACD,
				'SlateGray'=>0x708090,
				'SlateGrey'=>0x708090,
				'Snow'=>0xFFFAFA,
				'SpringGreen'=>0x00FF7F,
				'SteelBlue'=>0x4682B4,
				'Tan'=>0xD2B48C,
				'Teal'=>0x008080,
				'Thistle'=>0xD8BFD8,
				'Tomato'=>0xFF6347,
				'Turquoise'=>0x40E0D0,
				'Violet'=>0xEE82EE,
				'Wheat'=>0xF5DEB3,
				'White'=>0xFFFFFF,
				'WhiteSmoke'=>0xF5F5F5,
				'Yellow'=>0xFFFF00,
				'YellowGreen'=>0x9ACD32
			);
			self::$colors = array_merge($colors, array_combine(array_map('strtolower', array_keys($colors)), array_values($colors)));
		}

		if (isset(self::$colors[$value])) {
			return sprintf('#%06x', self::$colors[$value]);  // translate color name to color code
		} else {
			return false;
		}
	}
}

class SigPlusNovoFilter {
	/** Relationship between items. */
	public $rel;
	/** An array of items to combine. */
	public $items;

	public function __construct($rel = 'and') {
		$this->rel = $rel;
		$this->items = array();
	}

	public function is_empty() {
		return empty($this->items);
	}
}

class SigPlusNovoFolderParameters {
	public $id;
	public $time;
	public $entitytag;
}

class SigPlusNovoImageParameters {
	/** Width of preview/thumbnail image (px). */
	public $width = 100;
	/** Height of preview/thumbnail image (px). */
	public $height = 100;
	/** Whether the original images was cropped when the preview/thumbnail was generated. */
	public $crop = true;
	/** JPEG quality measure. */
	public $quality = 85;
	/** Horizontal offset. Used with watermark images. */
	public $x = 0;
	/** Vertical offset. Used with watermark images. */
	public $y = 0;
	/** Image position. Used with watermark images. */
	public $position = false;

	/**
	* The name prefix for generated images.
	* @return {string} A string that looks like "120x60" or "90s90".
	*/
	public function getNamingPrefix() {
		if ($this->width > 0 && $this->height > 0) {
			if ($this->crop) {
				$fitcode = 'x';  // center and crop
			} else {
				$fitcode = 's';  // scale to dimensions
			}
			return $this->width.$fitcode.$this->height;
		} else {
			return false;
		}
	}

	/**
	* A unique filename for a generated image avoiding name conflicts.
	* Absolute paths are converted into relative paths so that a site backup restored on another system preserves hashes.
	* @param {string} $imageref Absolute path or URL to an image file.
	* @param {bool|string} $base A base directory w.r.t. which absolute paths are converted to a relative path.
	*/
	public function getHash($imageref, $base) {
		if (is_url_http($imageref)) {
			$imagepath = parse_url($imageref, PHP_URL_PATH);
			$imagehashbase = $imageref;
		} elseif (strpos($imageref, JPATH_ROOT.DIRECTORY_SEPARATOR) === 0) {  // file is inside Joomla root folder
			$imagepath = $imageref;
			$imagehashbase = '@root/'.str_replace(DIRECTORY_SEPARATOR, '/', substr($imageref, strlen(JPATH_ROOT.DIRECTORY_SEPARATOR)));
		} elseif (is_absolute_path($base) && strpos($imageref, $base.DIRECTORY_SEPARATOR) === 0) {
			$imagepath = $imageref;
			$imagehashbase = '@base/'.str_replace(DIRECTORY_SEPARATOR, '/', substr($imageref, strlen($base.DIRECTORY_SEPARATOR)));
		} else {
			$imagepath = $imageref;
			$imagehashbase = str_replace(DIRECTORY_SEPARATOR, '/', $imageref);
		}

		$extension = pathinfo($imagepath, PATHINFO_EXTENSION);
		if ($extension) {
			$extension = '.'.$extension;
		}

		switch ($extension) {
			case '.jpg': case '.jpeg': case '.JPG': case '.JPEG':
				$quality = '@'.$this->quality; break;
			default:
				$quality = '';
		}
		if ($this->position !== false) {
			$position = '#'.$this->x.$this->position.$this->y;
		} else {
			$position = '';
		}

		$hashbase = 'sigplus_'.$this->getNamingPrefix().$quality.$position.'_'.$imagehashbase;
		return md5($hashbase).$extension;
	}
}

class SigPlusNovoPreviewParameters extends SigPlusNovoImageParameters {
	public function __construct(SigPlusNovoGalleryParameters $params = null) {
		if ($params) {
			$this->width = $params->preview_width;
			$this->height = $params->preview_height;
			$this->crop = $params->preview_crop;
			$this->quality = $params->quality;
		}
	}
}

class SigPlusNovoThumbParameters extends SigPlusNovoImageParameters {
	public function __construct(SigPlusNovoGalleryParameters $params = null) {
		if ($params) {
			$this->width = $params->thumb_width;
			$this->height = $params->thumb_height;
			$this->crop = $params->thumb_crop;
			$this->quality = $params->quality;
		}
	}
}

class SigPlusNovoWatermarkParameters extends SigPlusNovoImageParameters {
	public function __construct(SigPlusNovoGalleryParameters $params = null) {
		$this->width = 0;
		$this->height = 0;
		$this->crop = false;
		if ($params) {
			$this->x = $params->watermark_x;   // special value for watermarked image
			$this->y = $params->watermark_y;  // special value for watermarked image
			$this->quality = $params->quality;
			$this->position = $params->watermark_position;
		}
	}
}

/**
* Base class for configuration objects.
*/
class SigPlusNovoConfigurationBase {
	/** Settings override, provided as a string of key=value pairs. */
	public $settings = null;

	/**
	* Set parameters from a JRegistry or a JSON object (typically synthesized from a JSON string).
	* @param $params A value of a type such as boolean or stdClass.
	*/
	public function setParameters($params) {
		if (isset($params)) {
			if ($params instanceof stdClass) {
				foreach (get_object_vars($this) as $name => $value) {  // enumerate properties in class
					if (isset($params->$name)) {
						$this->setValue($name, $params->$name);
					}
				}
			} else if ($params instanceof JRegistry) {  // Joomla 2.5 and earlier
				foreach (get_object_vars($this) as $name => $value) {  // enumerate properties in class
					$paramvalue = $params->get($name);
					if (isset($paramvalue)) {
						$this->setValue($name, $paramvalue);
					}
				}
			}
			$this->validate();
			return true;
		} else {
			return false;
		}
	}

	private function setValue($key, $value) {
		// skip setting parameters with empty value (such parameters will retain their current state)
		if ($value !== false && !is_null($value) && (!is_string($value) || !ctype_space($value))) {
			$this->$key = $value;
		}
	}

	/**
	* Return the natural typed representation of a value, guessing at its type.
	*/
	private function getValue($value) {
		if (ctype_digit($value)) {  // digits only, treat as integer
			return (int) $value;
		} elseif (is_numeric($value)) {  // can represent a number, treat as floating-point
			return (float) $value;
		} else {
			return (string) $value;
		}
	}

	/**
	* Set parameters based on an associative array where the keys are parameter names.
	*/
	public function setArray(array $params, $exact = true) {
		foreach ($params as $key => $value) {
			$key = str_replace('-', '_', $key);  // PHP identifiers do not allow hyphens
			$exactvalue = $exact ? $value : $this->getValue($value);
			if (property_exists($this, $key)) {
				$this->setValue($key, $exactvalue);
			} elseif (strpos($key, ':') !== false) {  // contains special instruction for pop-up window or rotator engine
				list($engine, $key) = explode(':', $key, 2);
				$property = $engine.'_params';  // e.g. 'mobile_params', 'lightbox_params', 'rotator_params' or 'caption_params'
				if (property_exists($this, $property) && is_array($this->{$property})) {
					$this->{$property}[$key] = $exactvalue;
				}
			}
		}
		$this->validate();
	}

	/**
	* Set parameters based on a string with whitespace-delimited list of "key=value" pairs.
	*/
	public function setString($paramstring) {
		$params = self::string_to_array($paramstring);
		if ($params !== false) {
			$this->setArray($params, false);
		}
	}

	/**
	* Converts a string containing key-value pairs into an associative array.
	* @param {string} $string The string to split into key-value pairs.
	* @param {string} $separator The optional string that separates the key from the value.
	* @param {string} $quotechars Quote characters for values that contain special characters.
	* @return array An associative array that maps keys to values.
	*/
	public static function string_to_array($string, $separator = '=', $quotechars = '\'\"|') {
		$separator = preg_quote($separator, '#');

		$valuepatterns = array();
		for ($i = 0; $i < strlen($quotechars); $i++) {
			$quotechar = preg_quote($quotechars{$i}, '#');  // escape characters with special meaning to regex
			$valuepatterns[] = $quotechar.'[^'.$quotechar.']*'.$quotechar;
		}

		$regularchar = '[A-Za-z0-9:._/-]';
		$namepattern = '([A-Za-z_]'.$regularchar.'*)';  // html attribute name
		$valuepatterns[] = '-?[0-9]+(?:[.][0-9]+)?';
		$valuepatterns[] = $regularchar.'+';
		$valuepattern = '('.implode('|',$valuepatterns).')';
		$pattern = '#(?<=\s|^)(?:'.$namepattern.$separator.')?'.$valuepattern.'(?=\s|$)#';

		$array = array();
		$matches = array();
		$result = preg_match_all($pattern, $string, $matches, PREG_SET_ORDER);
		if (!$result) {
			return false;
		}
		foreach ($matches as $match) {
			$name = $match[1];
			$value = trim($match[2], $quotechars);
			if (strlen($name) > 0) {
				$array[$name] = $value;
			} else {
				$array[] = $value;
			}
		}
		return $array;
	}

	/**
	* Casts a value to a true or false value.
	*/
	protected static function as_boolean($value) {
		if (is_string($value)) {
			switch ($value) {
				case 'true':
				case 'on':
				case 'yes':
				case '1':
					return true;
				case 'false':
				case 'off':
				case 'no':
				case '0':
					return false;
			}
			return false;
		} else {
			return (bool) $value;
		}
	}

	/**
	* Casts a value to one of the specified set of values.
	*/
	protected static function as_one_of($value, array $list, $default = null) {
		if (!isset($default)) {
			$default = $list[0];
		}
		$key = array_search($value, $list);
		if ($key !== false) {
			return $list[$key];  // equal but not necessarily identical to $value
		} else {
			return $default;
		}
	}

	/**
	* Casts a value to a nonnegative integer.
	*/
	protected static function as_nonnegative_integer($value, $default = 0) {
		if (is_null($value) || $value === '') {
			return false;
		} elseif ($value !== false) {
			$value = (int) $value;
			if ($value < 0) {
				$value = $default;
			}
		}
		return $value;
	}

	/**
	* Casts a value to a positive integer.
	*/
	protected static function as_positive_integer($value, $default = 1) {
		if (is_null($value) || $value === false || $value === '') {
			return $default;
		} else {
			$value = (int) $value;
			if ($value <= 0) {
				$value = $default;
			}
			return $value;
		}
	}

	/**
	* Casts a value to a percentage value.
	*/
	protected static function as_percentage($value) {
		$value = (int) $value;
		if ($value < 0) {
			$value = 0;
		}
		if ($value > 100) {
			$value = 100;
		}
		return $value;
	}

	/**
	* Casts a value to a CSS hexadecimal color specification.
	*/
	protected static function as_color($value) {
		if (is_string($value)) {
			if (preg_match('/^#?([0-9A-Fa-f]{6}|[0-9A-Fa-f]{3})$/', $value)) {  // a hexadecimal color code
				return '#'.ltrim($value, '#');
			} else {  //  a color name
				return SigPlusNovoColors::translate($value);
			}
		} elseif (is_int($value)) {
			return sprintf('#%06x', $value);  // convert integer into hexadecimal digits
		} else {
			return false;
		}
	}

	/**
	* Casts a value to a CSS dimension measure with a unit.
	*/
	protected static function as_css_measure($value) {
		if (!isset($value) || $value === false) {
			return false;
		} elseif (is_numeric($value)) {
			return $value.'px';
		} elseif (preg_match('#^(?:(?:(?:0|[1-9][0-9]*)(?:[.][0-9]+)?(?:%|in|[cm]m|e[mx]|p[tcx])|0)\\b\\s*){1,4}$#', $value)) {  // "1px" or "1px 2em" or "1px 2em 3pt" or "1px 2em 3pt 4cm" or "1px 0 0 4cm"
			return $value;
		} else {
			return 0;
		}
	}

	/**
	* Converts bulletin board code into HTML markup code.
	*/
	protected static function as_bbcode($value) {
		$value = (string) $value;
		$value = preg_replace('#\[url\](.+?)\[/url\]#S', '<a href="$1">$1</a>', $value);
		$value = preg_replace('#\[url=(.+?)\](.+?)\[/url\]#S', '<a href="$1">$2</a>', $value);
		$value = str_replace(
			array("\r\n","\r","\n",'[b]','[/b]','[i]','[/i]','[u]','[/u]','[s]','[/s]','[sub]','[/sub]','[sup]','[/sup]'),
			array('<br/>','<br/>','<br/>','<b>','</b>','<i>','</i>','<u>','</u>','<strike>','</strike>','<sub>','</sub>','<sup>','</sup>'),
			$value
		);
		return $value;
	}

	protected static function as_accesslevel($value) {
		if (empty($value)) {
			return false;
		} elseif (is_numeric($value)) {  // numeric access level
			$result = (int)$value;
		} else {  // access level as string
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id');
			$query->from('#__viewlevels AS a');
			$query->where('a.title = '.$db->quote($value));
			$db->setQuery($query);
			$result = $db->loadResult();  // may return null
			if (isset($result)) {
				$result = (int)$result;
			}
		}

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	protected static function as_filter($expression) {
		if ($expression instanceof SigPlusNovoFilter) {
			return $expression;
		} elseif (is_string($expression)) {
			$filter = new SigPlusNovoFilter('or');
			$disjunction = explode(';', $expression);  // a;b;c --> a or b or c
			foreach ($disjunction as $subexpression) {
				$subfilter = new SigPlusNovoFilter('and');
				$subfilter->items = explode(',', $subexpression);  // a,b,c --> a and b and c
				$filter->items[] = $subfilter;
			}
			return $filter;
		} else {
			return new SigPlusNovoFilter('and');  // empty filter
		}
	}

	public function validate() {
		// apply in-place override for configuration settings
		if (!empty($this->settings)) {
			$settings = $this->settings;
			$this->settings = null;
			$this->setString($settings);
		}
	}
}

/**
* System-wide image gallery generation configuration parameters.
*/
class SigPlusNovoServiceParameters extends SigPlusNovoConfigurationBase {
	/** Whether to support multilingual labeling. */
	public $multilingual = false;
	/**
	* Base directory for images.
	* @type {string}
	*/
	public $base_folder = 'images';
	/** Base URL the directory for images corresponds to. */
	public $base_url = false;
	/** Subdirectory for thumbnail images. */
	public $folder_thumb = 'thumb';
	/** Subdirectory for preview images. */
	public $folder_preview = 'preview';
	/** Subdirectory for full-size images. */
	public $folder_fullsize = false;
	/** Subdirectory for watermarked images. */
	public $folder_watermarked = 'watermarked';
	/** Subdirectory for external script files. */
	public $folder_script = 'script';
	/**
	* Whether to use Joomla cache folder, the Joomla media folder or the image source folder for storing generated images.
	* @type {bool|'cache'|'media'|'source'}
	*/
	public $cache_image = 'cache';
	/**
	* Whether to use Joomla cache folder for storing temporary generated content.
	* @type {bool}
	*/
	public $cache_content = true;
	/**
	* Metadata to extract from image files.
	* @type {'default'|'all'|'iptc'|'minimal'|'exif'|'none'}
	*/
	public $metadata_filter = 'default';
	/**
	* Image processing library to use.
	* @type {'default'|'none'|'gd'|'gmagick'|'imagick'}
	*/
	public $library_image = 'default';
	/**
	* Whether to use uncompressed versions of lightbox and rotator engine scripts.
	* @type {bool}
	*/
	public $debug_client = false;
	/**
	* Whether to print verbose status messages of actions performed on the server.
	* @type {'default'|'laconic'|'verbose'}
	*/
	public $debug_server = 'default';

	/**
	* Whether database tables are flushed (truncated) when extension configuration is saved.
	* @type {bool}
	*/
	public $clean_database = false;

	public function validate() {
		parent::validate();

		$this->multilingual = (bool) $this->multilingual;
		$this->cache_image = (string) $this->cache_image;
		switch ($this->cache_image) {
			case 'cache':
			case 'media':
			case 'source':
				break;
			case '0':
				$this->cache_image = 'source';
				break;
			case '1':
			default:
				$this->cache_image = 'cache';
				break;
		}

		$this->cache_content = (bool) $this->cache_content;
		$this->metadata_filter = self::as_one_of($this->metadata_filter, array('default','all','iptc','minimal','exif','none'));
		switch ($this->library_image) {
			case 'gd':
				if (!is_gd_supported()) {
					$this->library_image = 'none';
				}
				break;
			case 'imagick':
				if (!is_imagick_supported()) {
					$this->library_image = 'none';
				}
				break;
			default:
				if (is_gd_supported()) {
					$this->library_image = 'gd';
				} elseif (is_imagick_supported()) {
					$this->library_image = 'imagick';
				} elseif (is_gmagick_supported()) {
					$this->library_image = 'gmagick';
				} else {
					$this->library_image = 'none';
				}
		}
		$this->debug_client = self::as_boolean($this->debug_client);
		$this->debug_server = self::as_one_of($this->debug_server, array('default','laconic','verbose'));
		$this->clean_database = self::as_boolean($this->clean_database);
		$this->checkFolders();
	}

	private function checkFolders() {
		// determine if image base folder is absolute or relative
		if (preg_match('#^(?:[a-zA-Z]+:)?[/\\\\]#', $this->base_folder)) {  // an absolute path, which starts with a leading slash (UNIX) or a drive letter designation and a backslash (Windows)
			$folder = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $this->base_folder), DIRECTORY_SEPARATOR);  // remove leading and trailing slashes
			$path = $folder;
		} else {  // a path relative to the Joomla root
			$folder = rtrim(str_replace('/', DIRECTORY_SEPARATOR, $this->base_folder), DIRECTORY_SEPARATOR);  // remove leading and trailing slashes
			$path = JPATH_ROOT.DIRECTORY_SEPARATOR.$folder;
		}

		// verify validity of path
		$path = realpath($path);
		if ($path === false) {
			throw new SigPlusNovoBaseFolderException($this->base_folder);
		}
		$this->base_folder = $path;

		// deduce base URL from base folder if not set
		if ($this->base_url === '') {
			$this->base_url = false;
		}
		if ($this->base_url === false && strpos($path, JPATH_ROOT.DIRECTORY_SEPARATOR) === 0) {  // starts with Joomla root folder
			$this->base_url = JURI::base(true).str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen(JPATH_ROOT)));  // build path relative to Joomla root
		}

		// verify presence of base URL
		if ($this->base_url === false) {
			throw new SigPlusNovoBaseURLException();
		}

		// trim excess trailing slash
		$this->base_url = rtrim($this->base_url, '/');

		// thumbnail folder (either inside image folder or cache folder)
		if (!is_filename($this->folder_thumb)) {
			throw new SigPlusNovoInvalidFolderException($this->folder_thumb, 'SIGPLUS_IMAGETYPE_THUMB');
		}

		// preview image folder (either inside image folder or cache folder)
		if (!is_filename($this->folder_preview)) {
			throw new SigPlusNovoInvalidFolderException($this->folder_preview, 'SIGPLUS_IMAGETYPE_PREVIEW');
		}

		// full size image folder
		if ($this->folder_fullsize) {
			if (!is_filename($this->folder_fullsize)) {
				throw new SigPlusNovoInvalidFolderException($this->folder_fullsize, 'SIGPLUS_IMAGETYPE_FULLSIZE');
			}
		} else {  // no folder available for high-resolution images
			$this->folder_fullsize = false;
		}

		// watermarked image folder (either inside image folder or cache folder)
		if (!is_filename($this->folder_watermarked)) {
			throw new SigPlusNovoInvalidFolderException($this->folder_watermarked, 'SIGPLUS_IMAGETYPE_WATERMARKED');
		}

		// check that generated images folders are all different
		$foldercounts = array_count_values(
			array_filter(
				array(
					$this->folder_thumb,
					$this->folder_preview,
					$this->folder_fullsize,
					$this->folder_watermarked
				)
			)
		);
		foreach ($foldercounts as $folder => $count) {
			if ($count > 1) {
				throw new SigPlusNovoFolderConflictException($folder);
			}
		}
	}
}

/**
* Parameter values for images galleries.
*
* Parameters have a priority which is enforced with a stack mechanism. Settings with higher
* priority (near top of stack) override settings with lower priority (near bottom of stack).
* (1) Factory values, as set in class property initializers, have the lowest precedence.
* (2) Global values are defined in the administration back-end, and typically set using
*     a JRegistry object.
* (3) Local values are usually specified directly in the article body, with activation tag
*     attribute values, and typically set using a parameter string of name-value pairs.
*/
class SigPlusNovoGalleryParameters extends SigPlusNovoConfigurationBase {
	/**
	* Title of Joomla module that acts as a base template for parameter values.
	*
	* This parameter accepts a <em>mod_sigplus</em> module title, whose configuration parameters act as a base for
	* plug-in parameters. In-line parameters specified in the plug-in activation tag override those parameters
	* inherited from the base module. This lets users set some default parameter values on the configuration page of
	* a module, and specify the module title in the plug-in activation tag as a single parameter or add some further
	* overrides. The module must be accessible in the context where it is referenced, i.e. it must be enabled,
	* assigned to the same menu item that the content is assigned to in which the activation tag is typed (or assigned
	* to all pages), and the user must have the appropriate access rights.
	*/
	public $base_module = null;
	/**
	* The JavaScript lightbox engine to use, or false to disable the lightbox engine.
	* @type {string}
	* @example <kbd>{gallery lightbox=boxplus/dark}myfolder{/gallery}</kbd> uses the boxplus pop-up window dark theme to display images when a preview image is clicked.
	*/
	public $lightbox = 'boxplusx';
	/**
	* The JavaScript image rotator engine to use, or false to disable the rotator engine.
	* @type {string}
	*/
	public $rotator = 'slideplus';
	/**
	* The JavaScript image caption engine to use, or false to disable the caption engine.
	* @type {string}
	*/
	public $caption = 'captionplus';
	/**
	* Unique identifier to use for gallery.
	* @type {string}
	*/
	public $id = null;
	/**
	* Image gallery source.
	* @type {string}
	*/
	public $source = null;

	/**
	* The way the gallery is rendered in HTML.
	* @type {'fixed'|'flow'|'packed'|'hidden'}
	*/
	public $layout = 'fixed';
	/**
	* Number of rows per rotator page.
	* Applicable to fixed (grid) layout.
	* @type {positive_integer}
	* @example <kbd>{gallery layout=fixed rows=2 cols=3}myfolder{/gallery}</kbd> shows a gallery in a 2-by-3 grid arrangement.
	*/
	public $rows = false;
	/**
	* Number of columns per rotator page.
	* Applicable to fixed (grid) layout.
	* @type {positive_integer}
	*/
	public $cols = false;
	/**
	* Number of items to show in a single layout unit.
	* Applicable to flow and packed layout.
	* @type {positive_integer}
	*/
	public $limit = false;
	/**
	* Maximum number of preview images to show in the gallery.
	* @type {integer}
	* @example <kbd>{gallery rows=2 cols=3 maxcount=5}myfolder{/gallery}</kbd> shows at most 5 preview images arranged in a 2-by-3 grid.
	*/
	public $maxcount = 0;
	/**
	* Width of preview images [px].
	* @type {nonnegative_integer}
	*/
	public $preview_width = 100;
	/**
	* Height of preview images [px].
	* @type {nonnegative_integer}
	*/
	public $preview_height = 100;
	/**
	* Whether to allow cropping images for more aesthetic preview images.
	* @type {boolean}
	*/
	public $preview_crop = true;
	/**
	* Scale factor for preview images targeted at retina displays.
	*/
	public $preview_retina_scale = SIGPLUS_RETINA_SCALE;
	/**
	* Width of thumbnail images [px].
	* @type {positive_integer}
	*/
	public $thumb_width = 100;
	/**
	* Height of thumbnail images [px].
	* @type {positive_integer}
	*/
	public $thumb_height = 100;
	/**
	* Whether to allow cropping images for more aesthetic thumbnails.
	* @type {boolean}
	*/
	public $thumb_crop = true;
	/**
	* Scale factor for thumbnail images targeted at retina displays.
	*/
	public $thumb_retina_scale = SIGPLUS_RETINA_SCALE;
	/**
	* JPEG quality.
	* @type {percentage}
	*/
	public $quality = 85;
	/**
	* Alignment of image gallery.
	* @type {'left'|'center'|'right'|'before'|'after'|'left-float'|'right-float'|'before-float'|'after-float'|'left-clear'|'right-clear'|'before-clear'|'after-clear'}
	* @example <kbd>{gallery alignment=before-float}fruit{/gallery}</kbd> left-aligns the gallery on an English site, allowing text to wrap around.
	*/
	public $alignment = 'before';
	/**
	* Whether the lightbox engine automatically centers the image in the browser window.
	* @type {boolean}
	*/
	public $lightbox_autocenter = true;
	/**
	* Whether the lightbox engine automatically reduces oversized images.
	* @type {boolean}
	*/
	public $lightbox_autofit = true;
	/**
	* Position to show small thumbnails for faster navigation inside the lightbox.
	* @type {'none'|'inside'|'outside'}
	*/
	public $lightbox_thumbs = false;
	/**
	* Time an image is shown before navigating to the next in a slideshow.
	* @type {nonnegative_integer}
	* @example <kbd>{gallery lightbox-slideshow=4000}fruit{/gallery}</kbd> makes a slideshow control button appear in the pop-up window; pressing the button will trigger a slideshow, automatically showing the next image after 4 seconds of delay.
	*/
	public $lightbox_slideshow = 0;
	/**
	* Whether to automatically activate slideshow mode when the lightbox opens.
	* @example <kbd>{gallery lightbox-slideshow=4000 lightbox-autostart=1}fruit{/gallery}</kbd> triggers a slideshow when the pop-up window appears, automatically showing the next image after 4 seconds of delay.
	* @type {boolean}
	*/
	public $lightbox_autostart = false;
	/**
	* Lightbox transition effect easing equation.
	* @type {string}
	*/
	public $lightbox_transition = 'linear';
	/**
	* Orientation of image gallery viewport.
	* @type {'horizontal'|'vertical'}
	*/
	public $rotator_orientation = 'horizontal';
	/**
	* Position of navigation and paging controls.
	* @type {'bottom'|'top'|'none'|'both'}
	*/
	public $rotator_navigation = 'bottom';
	/**
	* Show control buttons in navigation bar.
	* @type {boolean}
	*/
	public $rotator_buttons = true;
	/**
	* Show page links in navigation bar.
	* @type {boolean}
	*/
	public $rotator_links = true;
	/**
	* User action to advance the rotator.
	* @type {'click'|'mouseover'}
	*/
	public $rotator_trigger = 'click';
	/**
	* Unit the rotator advances upon a single mouse click.
	* @type {'single'|'page'}
	* @example <kbd>{gallery rows=2 cols=3 rotator-orientation=horizontal rotator-step=single}mygallery{/gallery}</kbd> causes the rotator to advance by a single column when using navigation controls <em>Previous</em> and <em>Next</em>.
	*/
	public $rotator_step = 'single';
	/**
	* Time taken for the rotator to move from one page to another [ms].
	* @type {nonnegative_integer}
	* @example <kbd>{gallery rotator-duration=800}fruit{/gallery}</kbd> makes the slide animation between pages take 0.8 seconds.
	*/
	public $rotator_duration = 800;
	/**
	* Animation delay.
	* @type {nonnegative_integer}
	*/
	public $rotator_delay = 0;
	/**
	* Alignment of rotator items within their container.
	* @type {'c'|'n'|'ne'|'e'|'se'|'s'|'sw'|'w'|'nw'}
	*/
	public $rotator_alignment = 'c';
	/**
	* Rotator transition effect easing equation.
	* @type {string}
	*/
	public $rotator_transition = 'linear';
	/**
	* Whether the rotator (and the lightbox) engine wraps around.
	* @type {boolean}
	*/
	public $loop = true;
	/**
	* Caption visibility.
	* @type {'none'|'mouseover'|'always'}
	*/
	public $caption_visibility = 'mouseover';
	/**
	* Position of image captions.
	* @type {'overlay-bottom'|'overlay-top'|'bottom'|'top'|'below'|'above'}
	*/
	public $caption_position = 'overlay-bottom';
	/**
	* Caption area height in CSS length units, or false for default (inherit from sigplus.css).
	*/
	public $caption_height = false;
	/**
	* The name of the file from where text for captions is drawn.
	* @type {string}
	*/
	public $caption_source = 'labels.txt';
	/**
	* Default title to assign to images.
	* @type {string}
	*/
	public $caption_title = null;
	/**
	* Default description to assign to images.
	* @type {string}
	*/
	public $caption_summary = null;
	/**
	* Title template used to build the image title.
	* @type {string}
	*/
	public $caption_title_template = null;
	/**
	* Description template used to build the image description.
	* @type {string}
	*/
	public $caption_summary_template = null;
	/**
	* Access level required to download original image.
	* @example <kbd>{gallery download="Registered"}myfolder{/gallery}</kbd> makes the gallery accessible to registered users only (on a standard Joomla installation).
	*/
	public $download = false;
	/**
	* Access level required to display metadata information.
	*/
	public $metadata = false;
	/**
	* Client-side protection for images.
	* @type {boolean}
	*/
	public $protection = false;
	/**
	* Margin [px] (with or without unit), or false for default (inherit from sigplus.css).
	* @example <kbd>{gallery preview-margin=4}myfolder{/gallery}</kbd> adds a margin of 4&nbsp;pixels around the border of each image in the gallery.</td>
	*/
	public $preview_margin = false;
	/** Border width [px] (with or without unit), or false for default (inherit from sigplus.css). */
	public $preview_border_width = false;
	/** Border style, or false for default (inherit from sigplus.css). */
	public $preview_border_style = false;
	/**
	* Border color as a hexadecimal value in between "#000000" and "#ffffff" inclusive, a standard color name, or false for default (inherit from sigplus.css).
	* @example <kbd>{gallery preview-border-width=1 preview-border-style=dotted preview-border-color="#000000"}myfolder{/gallery}</kbd> adds a black dotted border of a single pixel width around each image in the gallery.
	*/
	public $preview_border_color = false;
	/** Padding [px] (with or without unit), or false for default (inherit from sigplus.css). */
	public $preview_padding = false;
	/**
	* Sort criterion.
	* @example <kbd>{gallery sort-criterion=filename sort-order=asc}myfolder{/gallery}</kbd> sorts images in the gallery by filename in ascending order (A to Z).
	*/
	public $sort_criterion = SIGPLUS_SORT_LABELS_OR_FILENAME;
	/**
	* Sort order, ascending or descending.
	* @example <kbd>{gallery sort-criterion=mtime sort-order=desc}mygallery{/gallery}</kbd> sorts images in the gallery by last modification time in descending order (image last uploaded first).
	*/
	public $sort_order = SIGPLUS_SORT_ASCENDING;
	/**
	* Depth limit for scanning directory hierarchies recursively. Use -1 to set no recursion limit.
	* @type {nonnegative_integer}
	*/
	public $depth = 0;
	/**
	* The position of the watermark within the image [n|ne|e|se|s|sw|w|nw|c], or false for no watermark.
	* @type {''|'n'|'ne'|'e'|'se'|'s'|'sw'|'w'|'nw'|'c'}
	* @example <kbd>{gallery watermark-position=se watermark-x=15 watermark-y=10}buildings{/gallery}</kbd> applies a watermark to each image in the folder <kbd>buildings</kbd>. The watermark is placed in the bottom right (southeast) corner, 15 pixels from the right edge and 10 pixels from the bottom edge.
	*/
	public $watermark_position = false;
	/**
	* The distance to keep from the left or right edge, as appropriate.
	* @type {nonnegative_integer}
	*/
	public $watermark_x = 0;
	/**
	* The distance to keep from the top or bottom edge, as appropriate.
	* @type {nonnegative_integer}
	*/
	public $watermark_y = 0;
	/**
	* Image file to use for watermarking.
	* @type {string}
	*/
	public $watermark_source = 'watermark.png';
	/**
	* One-based index of representative image in the gallery.
	* @type {positive_integer}
	*/
	public $index = 1;
	/**
	* Files to include in a filtered gallery.
	* @type {array|boolean}
	*/
	public $filter_include = false;
	/**
	* Files to exclude in a filtered gallery.
	* @type {array|boolean}
	*/
	public $filter_exclude = false;
	/**
	* Advertise gallery images with Open Graph meta tags.
	* @type {boolean}
	*/
	public $open_graph = true;
	/**
	* Custom CSS class to annotate the generated gallery with.
	*/
	public $classname = false;

	/** Parameter overrides for handheld devices. */
	public $mobile_params = array();
	/** Additional parameters to pass to the lightbox engine. */
	public $lightbox_params = array();
	/** Additional parameters to pass to the rotator engine. */
	public $rotator_params = array();
	/** Additional parameters to pass to the caption engine. */
	public $caption_params = array();

	/**
	* Enforces that parameters are of the valid type and value.
	*/
	public function validate() {
		parent::validate();

		// apply parameter overrides for handheld devices
		if (!empty($this->mobile_params) && SigPlusNovoUserAgent::handheld()) {  // settings for mobile devices
			foreach ($this->mobile_params as $key => $value) {
				$this->$key = $value;  // override values set for desktop computers
			}
		}

		// force type for gallery identifier
		$this->id = !empty($this->id) ? (string) $this->id : null;

		// get engines to use
		if ($this->lightbox !== false) {
			switch ($this->lightbox) {
				case 'none': $this->lightbox = false; break;
				default: $this->lightbox = (string) $this->lightbox;
			}
		}
		if ($this->rotator !== false) {
			switch ($this->rotator) {
				case 'none': $this->rotator = false; break;
				default: $this->rotator = (string) $this->rotator;
			}
		}
		if ($this->caption !== false) {
			switch ($this->caption) {
				case 'none': $this->caption = false; break;
				default: $this->caption = (string) $this->caption;
			}
		}

		// gallery layout, desired preview image count, dimensions and other preview image properties
		switch ($this->layout) {
			case 'hidden':
				$this->caption_visibility = false;
				$this->caption_position = false;
				$this->caption = false;
				$this->rotator = false;
				$this->rows = false;
				$this->cols = false;
				$this->limit = false;
				break;
			case 'flow':
				$this->rotator = false;
				$this->rows = false;
				$this->cols = false;
				$this->limit = self::as_nonnegative_integer($this->limit);
				break;
			case 'packed':
				$this->rotator = 'scrollplus';  // manual scrolling
				$this->rows = false;
				$this->cols = false;
				$this->limit = self::as_nonnegative_integer($this->limit);
				break;
			default:  // case 'fixed':
				$this->layout = 'fixed';
				if ($this->rotator === false) {
					$properties = get_class_vars(__CLASS__);
					$this->rotator = $properties['rotator'];  // get default rotator
				}
				$this->rows = self::as_positive_integer($this->rows);
				$this->cols = self::as_positive_integer($this->cols);
				$this->limit = false;
		}
		$this->alignment = self::as_one_of($this->alignment,
			array(
				'before',  // 'left' (LTR) or 'right' (RTL) depending on language
				'after',   // 'right' (LTR) or 'left' (RTL) depending on language
				'before-clear',
				'after-clear',
				'before-float',
				'after-float',
				'center',
				'left',
				'right',  // 'left' or 'right' independent of language
				'left-clear',
				'right-clear',
				'left-float',
				'right-float'
			)
		);
		$language = JFactory::getLanguage();
		$this->alignment = str_replace(array('after','before'), $language->isRTL() ? array('left','right') : array('right','left'), $this->alignment);

		$this->maxcount = self::as_nonnegative_integer($this->maxcount);
		$this->preview_width = self::as_nonnegative_integer($this->preview_width, 0);
		$this->preview_height = self::as_nonnegative_integer($this->preview_height, 0);
		$this->preview_retina_scale = floatval($this->preview_retina_scale);
		$this->preview_crop = self::as_boolean($this->preview_crop);
		$this->quality = self::as_percentage($this->quality);
		if ($this->preview_crop) {  // cropping enabled, both width and height are required
			if ($this->preview_width == 0) {
				$this->preview_width = 100;
			}
			if ($this->preview_height == 0) {
				$this->preview_height = 100;
			}
		} else {  // cropping disabled, at least width or height is required
			if ($this->preview_width == 0 && $this->preview_height == 0) {  // both width and height is set to be determined automatically
				$this->preview_width = 100;
			}
		}
		$this->thumb_width = self::as_nonnegative_integer($this->thumb_width, 0);
		$this->thumb_height = self::as_nonnegative_integer($this->thumb_height, 0);
		$this->thumb_retina_scale = floatval($this->thumb_retina_scale);
		$this->thumb_crop = self::as_boolean($this->thumb_crop);
		if ($this->thumb_crop) {  // cropping enabled, both width and height are required
			if ($this->thumb_width == 0) {
				$this->thumb_width = 100;
			}
			if ($this->thumb_height == 0) {
				$this->thumb_height = 100;
			}
		} else {  // cropping disabled, at least width or height is required
			if ($this->thumb_width == 0 && $this->thumb_height == 0) {  // both width and height is set to be determined automatically
				$this->thumb_width = 100;
			}
		}

		// lightbox properties
		$this->lightbox_autocenter = self::as_boolean($this->lightbox_autocenter);
		$this->lightbox_autofit = self::as_boolean($this->lightbox_autofit);
		$this->lightbox_thumbs = self::as_one_of($this->lightbox_thumbs, array(false,'inside','outside'));

		// lightbox animation and transition effects
		$this->lightbox_slideshow = self::as_nonnegative_integer($this->lightbox_slideshow);
		$this->lightbox_autostart = self::as_boolean($this->lightbox_autostart);
		$this->lightbox_transition = self::as_one_of($this->lightbox_transition, array('linear','quad','cubic','quart','quint','expo','circ','sine','back','bounce','elastic'));

		// image rotator alignment, navigation bar positioning, and navigation control settings
		$this->rotator_orientation = self::as_one_of($this->rotator_orientation, array('horizontal','vertical'));
		$this->rotator_navigation = self::as_one_of($this->rotator_navigation, array('bottom','top','none','both'));
		$this->rotator_buttons = self::as_boolean($this->rotator_buttons);
		$this->rotator_links = self::as_boolean($this->rotator_links);

		// image rotator advancement
		$this->rotator_trigger = self::as_one_of($this->rotator_trigger, array('click','mouseover'));
		$this->rotator_step = self::as_one_of($this->rotator_step, array('single','page'));

		// miscellaneous visual clues for the image rotator
		$this->rotator_duration = self::as_nonnegative_integer($this->rotator_duration);
		$this->rotator_delay = self::as_nonnegative_integer($this->rotator_delay);
		$this->rotator_transition = self::as_one_of($this->rotator_transition, array('linear','quad','cubic','quart','quint','expo','circ','sine','back','bounce','elastic'));
		$this->rotator_alignment = self::as_one_of($this->rotator_alignment, array('c','n','ne','e','se','s','sw','w','nw'));
		$this->loop = self::as_boolean($this->loop);  // also applicable to lightbox

		// image labeling
		$this->caption_visibility = self::as_one_of($this->caption_visibility, array('none','mouseover','always'));
		if ($this->caption_visibility === 'none') {
			$this->caption = false;
		}
		$this->caption_position = self::as_one_of($this->caption_position, array('overlay-bottom','overlay-top','bottom','top','below','above'));
		$this->caption_height = self::as_css_measure($this->caption_height);
		switch ($this->caption_position) {
			case 'bottom': $this->caption_position = 'overlay-bottom'; break;
			case 'top': $this->caption_position = 'overlay-top'; break;
		}
		if ($this->caption_visibility === false) {
			$this->caption_position = false;
		}
		if (isset($this->caption_title)) {
			$this->caption_title = self::as_bbcode($this->caption_title);
		}
		if (isset($this->caption_summary)) {
			$this->caption_summary = self::as_bbcode($this->caption_summary);
		}
		if (empty($this->caption_title_template)) {
			$this->caption_title_template = null;
		} else {
			$this->caption_title_template = self::as_bbcode($this->caption_title_template);
		}
		if (empty($this->caption_summary_template)) {
			$this->caption_summary_template = null;
		} else {
			$this->caption_summary_template = self::as_bbcode($this->caption_summary_template);
		}

		// download and metadata
		$this->download = self::as_accesslevel($this->download);
		$this->metadata = self::as_accesslevel($this->metadata);

		// client-side protection measures
		$this->protection = self::as_boolean($this->protection);

		// image styling
		$this->preview_margin = self::as_css_measure($this->preview_margin);
		$this->preview_border_width = self::as_css_measure($this->preview_border_width);
		$this->preview_border_style = self::as_one_of($this->preview_border_style, array(false,'none','dotted','dashed','solid','double','groove','ridge','inset','outset'));
		$this->preview_border_color = self::as_color($this->preview_border_color);
		$this->preview_padding = self::as_css_measure($this->preview_padding);

		// sort criterion and sort order
		if (is_numeric($this->sort_criterion)) {
			$this->sort_criterion = self::as_one_of((int) $this->sort_criterion, array(SIGPLUS_SORT_LABELS_OR_FILENAME,SIGPLUS_SORT_FILENAME,SIGPLUS_SORT_LABELS_OR_MTIME,SIGPLUS_SORT_LABELS,SIGPLUS_SORT_MTIME,SIGPLUS_SORT_LABELS_OR_FILESIZE,SIGPLUS_SORT_FILESIZE,SIGPLUS_SORT_LABELS_OR_RANDOM,SIGPLUS_SORT_RANDOM));
		} else {
			switch ($this->sort_criterion) {
				case 'labels':
					$this->sort_criterion = SIGPLUS_SORT_LABELS; break;
				case 'labels-filename':
				case 'labels-fname':
					$this->sort_criterion = SIGPLUS_SORT_LABELS_OR_FILENAME; break;
				case 'labels-filemtime':
				case 'labels-mtime':
					$this->sort_criterion = SIGPLUS_SORT_LABELS_OR_MTIME; break;
				case 'labels-filesize':
				case 'labels-fsize':
					$this->sort_criterion = SIGPLUS_SORT_LABELS_OR_FILESIZE; break;
				case 'filename':
				case 'fname':
					$this->sort_criterion = SIGPLUS_SORT_FILENAME; break;
				case 'filemtime':
				case 'mtime':
					$this->sort_criterion = SIGPLUS_SORT_MTIME; break;
				case 'filesize':
				case 'fsize':
					$this->sort_criterion = SIGPLUS_SORT_FILESIZE; break;
				case 'random':
					$this->sort_criterion = SIGPLUS_SORT_RANDOM; break;
				case 'labels-random':
				case 'randomlabels':
					$this->sort_criterion = SIGPLUS_SORT_LABELS_OR_RANDOM; break;
				default:
					$this->sort_criterion = SIGPLUS_SORT_LABELS_OR_FILENAME;
			}
		}
		if (is_numeric($this->sort_order)) {
			$this->sort_order = self::as_one_of((int) $this->sort_order, array(SIGPLUS_SORT_ASCENDING,SIGPLUS_SORT_DESCENDING));
		} else {
			switch ($this->sort_order) {
				case 'asc':  case 'ascending':  $this->sort_order = SIGPLUS_SORT_ASCENDING;  break;
				case 'desc': case 'descending': $this->sort_order = SIGPLUS_SORT_DESCENDING; break;
				default: $this->sort_order = SIGPLUS_SORT_ASCENDING;
			}
		}

		// watermarking
		$this->watermark_position = self::as_one_of($this->watermark_position, array('n','ne','e','se','s','sw','w','nw','c'), false);
		$this->watermark_x = self::as_nonnegative_integer($this->watermark_x);
		$this->watermark_y = self::as_nonnegative_integer($this->watermark_y);

		// representative image index
		$this->index = self::as_positive_integer($this->index);

		// filters
		$this->filter_include = self::as_filter($this->filter_include);
		$this->filter_exclude = self::as_filter($this->filter_exclude);

		// Open Graph support
		$this->open_graph = self::as_boolean($this->open_graph);

		// miscellaneous advanced settings
		$this->depth = (int) $this->depth;
		if ($this->depth < -1) {  // -1 for recursive listing with no limit, 0 for flat listing (no subdirectories), >0 for recursive listing with limit
			$this->depth = 0;
		}

		// force meaningful settings for single-image view (disable slider and activate flow layout)
		if ($this->layout != 'hidden' && $this->maxcount == 1) {
			$this->layout = 'flow';
			$this->rows = false;
			$this->cols = false;
			$this->rotator = false;
		}
	}
}

class SigPlusNovoConfigurationParameters {
	public $gallery;
	public $service;
}

/**
* A parameter stack.
*/
class SigPlusNovoParameterStack {
	private $stack;

	public function top() {
		return end($this->stack);
	}

	public function push($value) {
		$this->stack[] = $value;
	}

	public function dup() {
		$this->stack[] = clone end($this->stack);
	}

	public function pop() {
		return array_pop($this->stack);
	}

	/**
	* Set parameters from a JSON object (typically synthesized from a JSON string).
	*/
	public function setObject($object) {
		$this->dup();
		$param = $this->top();
		$param->setParameters($object);
	}

	public function setArray(array $array) {
		$this->dup();
		$param = $this->top();
		$param->setArray($array);
	}

	public function setString($string) {
		$this->dup();
		$param = $this->top();
		$param->setString($string);
	}
}
