<?php
/**
* @file
* @brief    sigplus Image Gallery Plus base exceptions
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

/**
* Triggered when an error occurs while generating a gallery.
* This is a base class for other exception types.
*/
class SigPlusNovoException extends Exception {
	/**
	* Creates a new exception instance.
	* @param {string} $key Error message language key.
	*/
	public function __construct($key) {
		$message = '['.$key.'] '.JText::_($key);  // get localized message text
		$search = array();
		$replace = array();
		foreach (get_object_vars($this) as $property => $value) {
			$search[] = '{$'.$property.'}';  // replace placeholders in message text
			$text = (string) $this->$property;
			if (preg_match('/^[A-Z][0-9A-Z_]*$/', $text)) {  // could be a language key
				$text = JText::_($text);
			}
			$replace[] = htmlspecialchars($text);
		}
		$message = str_replace($search, $replace, $message);
		parent::__construct($message);
	}

	/**
	* Removes the server-specific path prefix from an absolute path, and returns a relative path.
	*/
	protected static function makeRelative($path) {
		return str_replace(array(JPATH_ROOT,DIRECTORY_SEPARATOR), array(JText::_('SIGPLUS_ROOT'),'/'), $path);
	}
}

class SigPlusNovoInvalidValueException extends SigPlusNovoException {
	protected $value;

	public function __construct($key, $value) {
		$this->value = $value;
		parent::__construct($key);
	}
}

/**
* Triggered in connection with a local file system resource such as an invalid file or folder.
* This is a base class for other exception types.
*/
class SigPlusNovoFileSystemException extends SigPlusNovoException {
	protected $file;

	public function __construct($key, $file) {
		$this->file = self::makeRelative($file);
		parent::__construct($key);
	}
}

/**
* Triggered when the extension is not able to guess what the base URL prefix for image folders is.
*/
class SigPlusNovoBaseURLException extends SigPlusNovoException {
	public function __construct() {
		parent::__construct('SIGPLUS_EXCEPTION_BASEURL');
	}
}

/**
* Triggered when a URL contains invalid characters.
*/
class SigPlusNovoURLEncodingException extends SigPlusNovoException {
	protected $url;

	public function __construct($url) {
		$this->url = $url;
		parent::__construct('SIGPLUS_EXCEPTION_URLENCODING');
	}
}

/**
* Triggered when a text file is not encoded with UTF-8.
*/
class SigPlusNovoTextFormatException extends SigPlusNovoException {
	protected $textfile;

	public function __construct($textfile) {
		$this->textfile = self::makeRelative($textfile);
		parent::__construct('SIGPLUS_EXCEPTION_TEXTFORMAT');
	}
}

/**
* Triggered when an XML file or data does not validate.
*/
class SigPlusNovoXMLFormatException extends SigPlusNovoException {
	public function __construct() {
		parent::__construct('SIGPLUS_EXCEPTION_XMLFORMAT');
	}
}

/**
* Triggered when the source specified for a gallery is HTML code rather than plain text.
*/
class SigPlusNovoHTMLCodeException extends SigPlusNovoInvalidValueException {
	public function __construct($source) {
		parent::__construct('SIGPLUS_EXCEPTION_HTML', $source);
	}
}

/**
* Triggered when the source specified for a gallery is not valid.
*/
class SigPlusNovoImageSourceException extends SigPlusNovoInvalidValueException {
	public function __construct($source) {
		parent::__construct('SIGPLUS_EXCEPTION_SOURCE', $source);
	}
}

/**
* Triggered when the source folder specified for a gallery is not found with a case-sensitive
* search but a folder with the same path but mismatching character case does.
*/
class SigPlusNovoImageSourceCaseMismatchException extends SigPlusNovoException {
	protected $given;
	protected $existing;

	public function __construct($given, $existing) {
		$this->given = self::makeRelative($given);
		$this->existing = self::makeRelative($existing);
		parent::__construct('SIGPLUS_EXCEPTION_CASE_MISMATCH');
	}
}

/**
* Triggered when the source specified for a gallery is not valid.
*/
class SigPlusNovoFeedURLException extends SigPlusNovoInvalidValueException {
	public function __construct($source) {
		parent::__construct('SIGPLUS_EXCEPTION_FEED', $source);
	}
}

/**
* Triggered when a file or folder does not exist or is inaccessible.
*/
class SigPlusNovoAccessException extends SigPlusNovoFileSystemException {
	public function __construct($file) {
		parent::__construct('SIGPLUS_EXCEPTION_ACCESS', $file);
	}
}

/**
* Thrown when the extension lacks permissions to create a folder.
*/
class SigPlusNovoFolderCreateException extends SigPlusNovoFileSystemException {
	public function __construct($folder) {
		parent::__construct('SIGPLUS_EXCEPTION_CREATE', $folder);
	}
}

/**
* Triggered when a file or folder does not exist or is inaccessible.
*/
class SigPlusNovoImageFormatException extends SigPlusNovoFileSystemException {
	public function __construct($file) {
		parent::__construct('SIGPLUS_EXCEPTION_IMAGE', $file);
	}
}

/**
* Thrown when the extension cannot access a document at a remote location
*/
class SigPlusNovoRemoteException extends SigPlusNovoInvalidValueException {
	public function __construct($url) {
		if (!extension_loaded('openssl') && in_array(parse_url($url, PHP_URL_SCHEME), array('https', 'ftps'))) {
			parent::__construct('SIGPLUS_EXCEPTION_REMOTE_SSL', $url);
		} else {
			parent::__construct('SIGPLUS_EXCEPTION_REMOTE', $url);
		}
	}
}

/**
* Triggered when the image base folder is not valid.
*/
class SigPlusNovoBaseFolderException extends SigPlusNovoInvalidValueException {
	public function __construct($folder) {
		parent::__construct('SIGPLUS_EXCEPTION_FOLDER_BASE', $folder);
	}
}

/**
* Triggered when a folder specification is not valid.
*/
class SigPlusNovoInvalidFolderException extends SigPlusNovoException {
	protected $value;
	protected $type;

	public function __construct($value, $type) {
		$this->value = $value;
		$this->type = $type;
		parent::__construct('SIGPLUS_EXCEPTION_FOLDER_INVALID');
	}
}

/**
* Triggered when folders are set to point to the same directory.
*/
class SigPlusNovoFolderConflictException extends SigPlusNovoInvalidValueException {
	public function __construct($folder) {
		parent::__construct('SIGPLUS_EXCEPTION_FOLDER_CONFLICT', $folder);
	}
}

/**
* Triggered when a required engine is not available.
*/
class SigPlusNovoEngineUnavailableException extends SigPlusNovoException {
	protected $engine;
	protected $enginetype;

	public function __construct($engine, $enginetype) {
		$this->engine = $engine;
		if ($enginetype) {
			$this->enginetype = JText::_('SIGPLUS_ENGINE_'.strtoupper($enginetype));
		}
		parent::__construct('SIGPLUS_EXCEPTION_ENGINE');
	}
}

class SigPlusNovoImageProcessingException extends SigPlusNovoException {
	protected $message;

	public function __construct($message) {
		$this->message = $message;
		parent::__construct('SIGPLUS_EXCEPTION_IMAGE_PROCESSING');
	}
}

/**
* Triggered when a required image processing library dependency is not available.
*/
class SigPlusNovoImageLibraryUnavailableException extends SigPlusNovoException {
	public function __construct() {
		parent::__construct('SIGPLUS_EXCEPTION_LIBRARY_IMAGE_PROCESSING');
	}
}

/**
* Triggered when the extension attempts to allocate memory for a resource with prohibitively large memory footprint.
*/
class SigPlusNovoOutOfMemoryException extends SigPlusNovoFileSystemException {
	protected $required;
	protected $available;

	public function __construct($required, $available, $resourcefile) {
		$this->required = $required;
		$this->available = $available;
		parent::__construct('SIGPLUS_EXCEPTION_MEMORY', $resourcefile);
	}
}

class SigPlusNovoNotSupportedException extends SigPlusNovoException {
	public function __construct() {
		parent::__construct('SIGPLUS_EXCEPTION_NOTSUPPORTED');
	}
}

/**
* Triggered when a guest visitor tries to access content that is available to logged in users only.
*/
class SigPlusNovoLoginRequiredException extends SigPlusNovoException {
	public function __construct() {
		parent::__construct('JERROR_LOGIN_DENIED');
	}
}

/**
* Triggered when the script is nearing the maximum execution time the script is allowed to run.
*/
class SigPlusNovoTimeoutException extends SigPlusNovoException {
	public function __construct() {
		parent::__construct('SIGPLUS_EXCEPTION_TIMEOUT');
	}
}

/**
* Triggered when an image cannot be downloaded due to access restrictions.
*/
class SigPlusNovoImageDownloadAccessException extends SigPlusNovoException {
	public function __construct() {
		parent::__construct('SIGPLUS_EXCEPTION_DOWNLOAD_ACCESS');
	}
}

/**
* Triggered when an image cannot be downloaded due to HTTP-related issues.
*/
class SigPlusNovoImageDownloadHeadersSentException extends SigPlusNovoFileSystemException {
	public function __construct($file) {
		parent::__construct('SIGPLUS_EXCEPTION_DOWNLOAD_HEADERS', $file);
	}
}
