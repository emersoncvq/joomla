<?php
/**
* @file
* @brief    sigplus Image Gallery Plus plug-in for Joomla
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

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'version.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'exception.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'filesystem.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'params.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'imagegenerator.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'engines.php';

use Joomla\CMS\Filter\InputFilter;

define('SIGPLUS_TEST', 0);
define('SIGPLUS_CREATE', 1);
define('SIGPLUS_CAPTION_CLIENT', true);  // apply template to caption text on client side

define('SIGPLUS_DEFAULT_CAPTION_SOURCE', 'labels.txt');

/**
* Interface for logging services.
*/
interface SigPlusNovoLoggingService {
	public function appendStatus($message);
	public function appendError($message);
	public function appendCodeBlock($message, $block);
	public function fetch();
}

/**
* A service that compiles a dynamic HTML-based log.
*/
class SigPlusNovoHTMLLogging implements SigPlusNovoLoggingService {
	/** Error log. */
	private $log = array();

	/**
	* Appends an informational message to the log.
	*/
	public function appendStatus($message) {
		$this->log[] = $message;
	}

	/**
	* Appends a critical error message to the log.
	*/
	public function appendError($message) {
		$this->log[] = $message;
	}

	/**
	* Appends an informational message to the log with a code block.
	*/
	public function appendCodeBlock($message, $block) {
		$this->log[] = $message."\n".'<pre class="sigplus-log">'.htmlspecialchars($block).'</pre>';
	}

	public function fetch() {
		$document = JFactory::getDocument();

		//$document->addScript(JURI::base(true).'/media/sigplus/js/log.js');  // language-neutral
		$script = file_get_contents(JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.SIGPLUS_MEDIA_FOLDER.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'log.js');
		if ($script !== false) {
			$script = str_replace(array("'Show'","'Hide'"), array("'".JText::_('JSHOW')."'","'".JText::_('JHIDE')."'"), $script);
			$document->addScriptDeclaration($script);
		}

		ob_start();
			print '<ul class="sigplus-log" dir="ltr" lang="en">';
			foreach ($this->log as $logentry) {
				print '<li>'.$logentry.'</li>';
			}
			print '</ul>';
			$this->log = array();
		return ob_get_clean();
	}
}

/**
* A service that does not perform any actual logging.
*/
class SigPlusNovoNoLogging implements SigPlusNovoLoggingService {
	public function appendStatus($message) {
	}

	public function appendError($message) {
	}

	public function appendCodeBlock($message, $block) {
	}

	public function fetch() {
		return null;
	}
}

/**
* Logging services.
*/
class SigPlusNovoLogging {
	/** Singleton instance. */
	private static $instance;

	public static function setService(SigPlusNovoLoggingService $service) {
		self::$instance = $service;
	}

	public static function appendStatus($message) {
		self::$instance->appendStatus($message);
	}

	public static function appendError($message) {
		self::$instance->appendError($message);
	}

	public static function appendCodeBlock($message, $block) {
		self::$instance->appendCodeBlock($message, $block);
	}

	public static function fetch() {
		return self::$instance->fetch();
	}
}
SigPlusNovoLogging::setService(new SigPlusNovoNoLogging());  // disable logging

class SigPlusNovoUser {
	/**
	* The normalized user group title for the currently logged-in user.
	*/
	public static function getCurrentUserGroup() {
		$user = JFactory::getUser();
		if ($user->guest) {
			return false;
		}

		// get all groups the user is member of, but not inherited groups
		$groups = JAccess::getGroupsByUser($user->id, false);
		if (count($groups) < 1) {
			return false;  // not a member of any group
		}

		// get first group out of all groups the user may be a member of
		$group = $groups[0];

		// get the group title from the database
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('grp.title')
			->from('#__usergroups AS grp')
			->where('grp.id = '.$group)
		;
		$db->setQuery($query);
		$groupname = $db->loadResult();

		if ($groupname) {
			return $groupname;
		} else {
			return false;
		}
	}
}

/**
* Database layer.
*/
class SigPlusNovoDatabase {
	/**
	* Verifies if a string is encoded in UTF-8.
	* This function makes sure checks for UTF-8 are possible even if the PHP extension mbstring is not available.
	* @see https://www.w3.org/International/questions/qa-forms-utf-8.en
	* @param $str The string whose internal encoding to check.
	* @return True if the string is encoded in UTF-8.
	*/
	private static function checkUTF8Encoding($str) {
		if (extension_loaded('mbstring') && function_exists('mb_check_encoding')) {
			return mb_check_encoding($str, 'utf-8');
		} else {
			return 0 < preg_match('%^(?:
				[\x09\x0A\x0D\x20-\x7E]            # ASCII
				| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
				| \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
				| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
				| \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
				| \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
				| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
				| \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
				)*$%xs', $str);
		}
	}

	/**
	* Returns a string suitable as an SQL LIKE pattern that checks whether strings start with a prefix.
	* @param $prefix The string the database table value is expected to start with.
	*/
	public static function sqlstartswith($prefix) {
		return str_replace(array('\\','%','_'), array('\\\\','\\%','\\_'), $prefix).'%';
	}

	/**
	* Convert a wildcard pattern to an SQL LIKE pattern.
	*/
	public static function sqlpattern($pattern) {
		// replace "*" and "?" with LIKE expression equivalents "%" and "_"
		$pattern = str_replace(array('\\','%','_'), array('\\\\','\\%','\\_'), $pattern);
		$pattern = str_replace(array('*','?'), array('%','_'), $pattern);
		return $pattern;
	}

	/**
	* Convert a timestamp to "yyyy-mm-dd hh:nn:ss" format.
	*/
	public static function sqldate($timestamp) {
		if (isset($timestamp)) {
			if (is_int($timestamp)) {
				return gmdate('Y-m-d H:i:s', $timestamp);
			} else {
				return $timestamp;
			}
		} else {
			return gmdate('Y-m-d H:i:s');
		}
	}

	/**
	* Quote column identifier names.
	*/
	private static function quoteColumns(array $cols) {
		$db = JFactory::getDbo();

		// quote identifier names
		foreach ($cols as &$col) {
			$col = $db->quoteName($col);
		}
		return $cols;
	}

	/**
	* Type-safe value quoting.
	*/
	public static function quoteValue($value) {
		if (is_string($value)) {
			if (self::checkUTF8Encoding($value)) {
				$db = JFactory::getDbo();
				return $db->quote($value);
			} else {
				return '0x'.implode(unpack("H*", $value));
			}
		} elseif (is_bool($value)) {
			return $value ? 1 : 0;
		} elseif (!is_numeric($value)) {
			return 'NULL';
		} else {
			return $value;
		}
	}

	private static function quoteValues(array $row) {
		$db = JFactory::getDbo();
		foreach ($row as &$entry) {
			if (is_string($entry)) {
				if (self::checkUTF8Encoding($entry)) {
					$entry = $db->quote($entry);
				} else {
					$entry = '0x'.implode(unpack("H*", $entry));
				}
			} elseif (is_bool($entry)) {
				$entry = $entry ? 1 : 0;
			} elseif (!is_numeric($entry)) {
				$entry = 'NULL';
			}
		}
		return $row;
	}

	/**
	* The database identifier that belongs to an ISO language code.
	*/
	public static function getLanguageId($language) {
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT'.PHP_EOL.
				$db->quoteName('langid').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_language').PHP_EOL.
			'WHERE'.PHP_EOL.
				$db->quoteName('lang').' = '.$db->quote($language)
		);
		return $db->loadResult();
	}

	/**
	* The database identifier that belongs to an ISO country code.
	*/
	public static function getCountryId($country) {
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT'.PHP_EOL.
				$db->quoteName('countryid').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_country').PHP_EOL.
			'WHERE'.PHP_EOL.
				$db->quoteName('country').' = '.$db->quote($country)
		);
		return $db->loadResult();
	}

	/**
	* Inserts several rows into a table, updating duplicates if insertion fails (e.g. when a unique key matches).
	* @param {string} $table The name of the table to update or insert the row into.
	* @param {array} $match_keys A list of primary or unique key columns used to find matching table rows.
	* @param {array} $cols The name of the columns the values correspond to.
	* @param {array} $rows A collection of values for each row to insert or overwrite existing values with.
	*/
	public static function getInsertBatchStatement($table, array $match_keys, array $cols, array $rows, array $keys = null, array $constants = null) {
		$db = JFactory::getDbo();

		$table = $db->quoteName($table);

		// quote identifier names
		if (isset($keys)) {
			$keys = self::quoteColumns($keys);
		}

		// build column name array and quote column names
		if (isset($constants)) {
			$cols = array_merge(array_values($cols), array_keys($constants));  // append constant value columns
		}
		$cols = self::quoteColumns($cols);
		$columns = implode(',', $cols);

		// build clause for list of values to be inserted or updated
		foreach ($rows as &$row) {
			$row = self::quoteValues($row);

			if (isset($constants)) {
				foreach ($constants as $constant) {  // append constants
					$row[] = $constant;
				}
			}

			$row = '('.implode(',',$row).')';
		}
		unset($row);
		$values = implode(',', $rows);

		if (!empty($rows)) {
			switch ($db->getServerType()) {
				case 'mysql':
					// build update clause
					$update = array();
					foreach ($cols as $col) {
						if (!isset($keys) || !in_array($col, $keys)) {  // there are no keys or column is not a key
							$update[] = $col.' = VALUES('.$col.')';
						}
					}
					$update_clause = implode(', ', $update);

					$query =
						"INSERT INTO {$table} ({$columns})".PHP_EOL.
						"VALUES {$values}".PHP_EOL.
						"ON DUPLICATE KEY UPDATE {$update_clause}";
					return $query;
				case 'mssql':
					// build join clause for merge
					$match_criteria = array();
					foreach ($match_keys as $key) {
						$key = $db->quoteName($key);
						$match_criteria[] = "target.{$key} = source.{$key}";
					}
					$match_condition = implode(' AND ', $match_criteria);

					// build update clause
					$update = array();
					foreach ($cols as $col) {
						if (!isset($keys) || !in_array($col, $keys)) {  // there are no keys or column is not a key
							$update[] = "target.{$col} = source.{$col}";
						}
					}
					$update_clause = implode(', ', $update);

					// build insert clause
					$insert = array();
					foreach ($cols as $col) {
						$insert[] = "source.{$col}";
					}
					$insert_clause = implode(', ', $insert);

					$query =
						"MERGE INTO {$table} WITH (HOLDLOCK) AS target".PHP_EOL.
						"USING (VALUES {$values}) AS source ({$columns})".PHP_EOL.
						"ON {$match_condition}".PHP_EOL.
						"WHEN MATCHED THEN UPDATE SET {$update_clause}".PHP_EOL.
						"WHEN NOT MATCHED THEN INSERT ({$columns}) VALUES ({$insert_clause})".PHP_EOL.
						";";
					return $query;
			}
		}
		return false;
	}

	/**
	* Insert multiple rows into the database in a batch with updates.
	*/
	public static function insertBatch($table, array $match_keys, array $cols, array $rows, $keys = null, array $constants = null) {
		if (($statement = self::getInsertBatchStatement($table, $match_keys, $cols, $rows, $keys, $constants)) !== false) {
			$db = JFactory::getDbo();
			$db->setQuery($statement);
			$db->execute();
		}
	}

	/**
	* Inserts a single row into a table, or updates a duplicate if insertion fails (e.g. when a unique key matches).
	* @param {string} $table The name of the table to update or insert the row into.
	* @param {array} $match_keys A list of primary or unique key columns used to find matching table rows.
	* @param {array} $cols The name of the columns the values correspond to.
	* @param {array} $values The values to insert or overwrite existing values with.
	* @param {string} $auto_key The name of the auto-increment column.
	* @return {int} The auto-increment value for the updated or inserted row.
	*/
	public static function insertSingleUnique($table, array $match_keys, array $cols, array $values, $auto_key = null) {
		$db = JFactory::getDbo();

		$table = $db->quoteName($table);

		// quote identifier names
		$cols = self::quoteColumns($cols);
		if (isset($auto_key)) {
			$auto_key = $db->quoteName($auto_key);
		}

		// build insert clause
		$values = self::quoteValues($values);
		$values = implode(',', $values);
		$columns = implode(',', $cols);

		$auto_value = null;
		switch ($db->getServerType()) {
			case 'mysql':
				// build update clause
				$update = array();

				// If a table contains an AUTO_INCREMENT column and INSERT ... UPDATE inserts a row,
				// the LAST_INSERT_ID() function returns the AUTO_INCREMENT value but if the statement updates
				// a row instead, LAST_INSERT_ID() is not meaningful. However, you can work around this by using
				// LAST_INSERT_ID(expr). Suppose that `id` is the AUTO_INCREMENT column. To make LAST_INSERT_ID()
				// meaningful for updates, add an artificial update assignment: id=LAST_INSERT_ID(id).
				if (isset($auto_key)) {
					$update[] = $auto_key.' = LAST_INSERT_ID('.$auto_key.')';
				}
				foreach ($cols as $col) {
					$update[] = $col.' = VALUES('.$col.')';
				}
				$update_clause = implode(', ', $update);

				$db->setQuery(
					"INSERT INTO {$table} ({$columns})".PHP_EOL.
					"VALUES ({$values})".PHP_EOL.
					"ON DUPLICATE KEY UPDATE {$update_clause}"
				);
				$db->execute();
				$auto_value = $db->insertid();
				break;
			case 'mssql':
				// build join clause for merge
				$match_criteria = array();
				foreach ($match_keys as $key) {
					$key = $db->quoteName($key);
					$match_criteria[] = "target.{$key} = source.{$key}";
				}
				$match_condition = implode(' AND ', $match_criteria);

				// build update clause
				$update = array();
				foreach ($cols as $col) {
					$update[] = "target.{$col} = source.{$col}";
				}
				$update_clause = implode(', ', $update);

				$query =
					"MERGE INTO {$table} WITH (HOLDLOCK) AS target".PHP_EOL.
					"USING (VALUES ({$values})) AS source ({$columns})".PHP_EOL.
					"ON {$match_condition}".PHP_EOL.
					"WHEN MATCHED THEN UPDATE SET {$update_clause}".PHP_EOL.
					"WHEN NOT MATCHED THEN INSERT ({$columns}) VALUES ({$values})";
				if (isset($auto_key)) {
					$query .= PHP_EOL."OUTPUT INSERTED.{$auto_key}";
				}
				$query .= ";";
				$db->setQuery($query);
				if (isset($auto_key)) {
					$auto_value = $db->loadResult();
				} else {
					$db->execute();
				}
				break;
		}

		if (isset($auto_key)) {
			return $auto_value;
		}
	}

	/**
	* Deletes an existing and inserts a new row into a table.
	* @param {string} $table The name of the table to update or insert the row into.
	* @param {array} $cols The name of the columns the values correspond to.
	* @param {array} $values The values to insert or overwrite existing values with.
	* @return {int} The auto-increment value for the newly inserted row.
	*/
	public static function replaceSingle($table, array $match_keys_values, array $cols, array $values) {
		$db = JFactory::getDbo();

		$table = $db->quoteName($table);

		// quote identifier names
		$cols = self::quoteColumns($cols);
		$columns = implode(',', $cols);

		// build insert clause
		$values = self::quoteValues($values);
		$values = implode(',', $values);

		switch ($db->getServerType()) {
			case 'mysql':
				$db->setQuery(
					"REPLACE INTO {$table} ({$columns})".PHP_EOL.
					"VALUES ({$values})"
				);
				$db->execute();
				break;
			case 'mssql':
				$match_criteria = array();
				foreach ($match_keys_values as $key => $value) {
					$key = $db->quoteName($key);
					$value = self::quoteValue($value);
					$match_criteria[] = "{$key} = {$value}";
				}
				$match_condition = implode(' AND ', $match_criteria);

				$db->setQuery(
					"DELETE FROM {$table} WHERE {$match_condition}"
				);
				$db->execute();
				$db->setQuery(
					"INSERT INTO {$table} ({$columns})".PHP_EOL.
					"VALUES ({$values})"
				);
				$db->execute();
				break;
		}
		return $db->insertid();
	}

	public static function executeAll(array $queries) {
		$db = JFactory::getDbo();

		foreach ($queries as $query) {
			$db->setQuery($query);
			$db->execute();
		}
	}
}

/**
* Measures execution time and prevents time-outs.
*/
class SigPlusNovoTimer {
	private static $timeout_count = 0;

	private static function getStartedTime() {
		return time();  // save current timestamp
	}

	private static function getMaximumDuration() {
		$duration = ini_get('max_execution_time');
		if ($duration) {
			$duration = (int)$duration;
		} else {
			$duration = 0;
		}

		if ($duration >= 10) {
			return $duration - 2;
		} else {
			return 10;  // a feasible guess
		}
	}

	/**
	* Short-circuit plug-in activation if allotted execution time has already been used up.
	*/
	public static function shortcircuit() {
		return SigPlusNovoTimer::$timeout_count > 0;
	}

	/**
	* Check whether execution time is within the allotted maximum limit.
	*/
	public static function checkpoint() {
		static $started_time;
		static $maximum_duration;

		// initialize static variables
		isset($started_time) || $started_time = SigPlusNovoTimer::getStartedTime();
		isset($maximum_duration) || $maximum_duration = SigPlusNovoTimer::getMaximumDuration();

		if (time() >= $started_time + $maximum_duration) {
			SigPlusNovoTimer::$timeout_count++;
			throw new SigPlusNovoTimeoutException();
		}
	}
}

abstract class SigPlusNovoMediaTypes {
	private static function getImageFileExtensions() {
		static $extensions = array('jpg','jpeg','png','gif','webp','svg');
		return $extensions;
	}

	private static function getVideoFileExtensions() {
		static $extensions = array('mp4','mpg','mpeg','ogg','webm','avi','flv','mov');
		return $extensions;
	}

	private static function isFileOfType($file, $extensions) {
		$lowercase_extensions = array_map('strtolower', $extensions);
		$uppercase_extensions = array_map('strtoupper', $extensions);
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		return in_array($extension, $lowercase_extensions, true) || in_array($extension, $uppercase_extensions, true);
	}

	/**
	* True if the file extension indicates a recognized image format.
	*/
	public static function isImageFile($file) {
		return self::isFileOfType($file, self::getImageFileExtensions());
	}

	/**
	* True if the file extension indicates a recognized video format.
	*/
	public static function isVideoFile($file) {
		return self::isFileOfType($file, self::getVideoFileExtensions());
	}

	private static function getMatchingResource($resourcepath, $extensions) {
		$root = pathinfo($resourcepath, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR.pathinfo($resourcepath, PATHINFO_FILENAME).'.';  // path up to (and including) terminating dot character
		$matches = array_filter($extensions, function($extension) use ($root) {
			return file_exists_case_insensitive($root.$extension);
		});
		if (!empty($matches)) {
			$match = reset($matches);  // pick extension with highest precedence
			return $root.$match;
		} else {
			return false;
		}
	}

	/**
	* Returns a poster image that is paired with the moving picture.
	*/
	public static function getPosterImage($resourcepath) {
		return self::getMatchingResource($resourcepath, self::getImageFileExtensions());
	}

	/**
	* Returns a moving picture that is paired with the static image.
	*/
	public static function getMovingPicture($resourcepath) {
		return self::getMatchingResource($resourcepath, self::getVideoFileExtensions());
	}
}

class SigPlusNovoLabels {
	private $multilingual = false;
	private $caption_source = SIGPLUS_DEFAULT_CAPTION_SOURCE;

	public function __construct(SigPlusNovoConfigurationParameters $config) {
		$this->multilingual = $config->service->multilingual;
		$this->caption_source = $config->gallery->caption_source;
	}

	public function isLabelsFileAvailable($imagefolder) {
		// get labels source file name components
		$labelsname = pathinfo($this->caption_source, PATHINFO_FILENAME);
		$labelsextn = pathinfo($this->caption_source, PATHINFO_EXTENSION);
		$labelssuff = '.'.( $labelsextn ? $labelsextn : 'txt' );

		// read default (language-neutral) labels file
		$file = $imagefolder.DIRECTORY_SEPARATOR.$labelsname.$labelssuff;  // filesystem path to labels file
		if (is_file($file)) {
			return true;
		}

		if ($this->multilingual) {
			$lang = JFactory::getLanguage();
			$tag = $lang->getTag();  // use site default language
			$file = $imagefolder.DIRECTORY_SEPARATOR.$labelsname.'.'.$tag.$labelssuff;
			if (is_file($file)) {
				return true;
			}
		}

		return false;
	}

	/**
	* Finds language-specific labels files.
	* @param {string} $imagefolder An absolute path or URL to a directory with labels files.
	* @return {array} A list of full paths to the language-specific labels files.
	*/
	public function getLabelsFilePaths($imagefolder) {
		$sources = array();

		// get labels source file name components
		$labelsname = pathinfo($this->caption_source, PATHINFO_FILENAME);
		$labelsextn = pathinfo($this->caption_source, PATHINFO_EXTENSION);
		$labelssuff = '.'.( $labelsextn ? $labelsextn : 'txt' );

		// read default (language-neutral) labels file
		$file = $imagefolder.DIRECTORY_SEPARATOR.$labelsname.$labelssuff;  // filesystem path to labels file
		if (is_file($file)) {
			$lang = JFactory::getLanguage();
			$tag = $lang->getTag();  // use site default language
			$sources[$tag] = $file;  // language tag has format hu-HU or en-GB
		}

		if ($this->multilingual) {
			// look for language-specific labels files in folder
			$files = fsx::scandir($imagefolder);
			foreach ($files as $file) {
				if (preg_match('#'.preg_quote($labelsname, '#').'[.]([a-z]{2}-[A-Z]{2})'.preg_quote($labelssuff, '#').'$#Su', $file, $matches)) {
					$tag = $matches[1];
					$file = $imagefolder.DIRECTORY_SEPARATOR.$labelsname.'.'.$tag.$labelssuff;
					if (is_file($file)) {
						$sources[$tag] = $file;  // assignment may overwrite entry for default language
					}
				}
			}
		}

		return $sources;
	}

	/**
	* Extract short captions and descriptions attached to images from a "labels.txt" file.
	*/
	private function parseLabels($labelspath, &$captions, &$patterns) {
		$entries = array();
		$patterns = array();

		$imagefolder = dirname($labelspath);

		// read file contents
		$contents = file_get_contents($labelspath);
		if ($contents === false) {
			return false;
		}

		// verify file type
		if (!strcmp('{\rtf', substr($contents,0,5))) {  // file has type "rich text format" (RTF)
			throw new SigPlusNovoTextFormatException($labelspath);
		}

		// remove UTF-8 BOM and normalize line endings
		if (!strcmp("\xEF\xBB\xBF", substr($contents,0,3))) {  // file starts with UTF-8 BOM
			$contents = substr($contents, 3);  // remove UTF-8 BOM
		}
		$contents = str_replace("\r", "\n", $contents);  // normalize line endings

		// split into lines
		$matches = array();
		preg_match_all('/^([^|\n]+)(?:[|]([^|\n]*)(?:[|]([^\n]*))?)?$/mu', $contents, $matches, PREG_SET_ORDER);
		switch (preg_last_error()) {
			case PREG_BAD_UTF8_ERROR:
				throw new SigPlusNovoTextFormatException($labelspath);
		}

		// parse individual entries
		$priority = 0;
		$index = 0;  // counter for entry order
		foreach ($matches as $match) {
			$imagefile = $match[1];
			$title = count($match) > 2 ? $match[2] : null;
			$summary = count($match) > 3 ? $match[3] : null;

			if (strpos($imagefile, '*') !== false || strpos($imagefile, '?') !== false) {  // contains wildcard character
				$pattern = new stdClass;
				$pattern->match = SigPlusNovoDatabase::sqlpattern($imagefile);  // replace "*" and "?" with LIKE expression equivalents "%" and "_"
				$pattern->priority = ++$priority;
				$pattern->title = $title;
				$pattern->summary = $summary;
				$patterns[] = $pattern;
			} else {
				if (is_url_http($imagefile)) {  // a URL to a remote image
					$imagelocation = safe_url_encode($imagefile);
				} else {  // a local image
					$imagefile = str_replace('/', DIRECTORY_SEPARATOR, $imagefile);
					$imagepath = $imagefolder.DIRECTORY_SEPARATOR.$imagefile;

					// normalize image file name, comparing "labels.txt" and file system directory listing
					$imagefile = file_exists_case_insensitive($imagepath);
					if ($imagefile === false) {  // also checks that image file truly exists
						continue;
					}

					$imagelocation = $imagefolder.DIRECTORY_SEPARATOR.$imagefile;
				}

				// prepare data for injection into database
				$entry = new stdClass;
				$entry->file = $imagelocation;
				$entry->index = ++$index;
				$entry->title = $title;
				$entry->summary = $summary;
				$entries[$imagelocation] = $entry;
			}
		}

		$captions = array_values($entries);
		return true;
	}

	public function populate($imagefolder, $folderid) {
		$db = JFactory::getDbo();
		$queries = array();

		// force type to prevent SQL injection
		$folderid = (int)$folderid;

		// delete existing data
		$queries[] =
			'DELETE FROM '.$db->quoteName('#__sigplus_foldercaption').PHP_EOL.
			'WHERE'.PHP_EOL.
				$db->quoteName('folderid').' = '.$folderid
			;

		// set condition for treating a caption data entry as one that needs a check for a potential update
		$dbtype = $db->getServerType();
		switch ($dbtype) {
			case 'mssql':
				$date_condition = 'DATEADD(hour, -1, GETDATE())';
				break;
			default:
				$date_condition = 'DATE_SUB(NOW(), INTERVAL 1 HOUR)';
		}

		// invalidate existing labels data
		$queries[] =
			'DELETE c'.PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_caption').' AS c'.PHP_EOL.
				'INNER JOIN '.$db->quoteName('#__sigplus_image').' AS i'.PHP_EOL.
				'ON c.'.$db->quoteName('imageid').' = i.'.$db->quoteName('imageid').PHP_EOL.
			'WHERE'.PHP_EOL.
				'i.'.$db->quoteName('folderid').' = '.$folderid
			;

		$sources = $this->getLabelsFilePaths($imagefolder);
		foreach ($sources as $languagetag => $source) {
			// fetch language and country database identifier
			list($language, $country) = explode('-', $languagetag);
			$langid = (int)SigPlusNovoDatabase::getLanguageId($language);
			$countryid = (int)SigPlusNovoDatabase::getCountryId($country);
			if (!$langid || !$countryid) {  // language does not exist
				continue;
			}

			// extract captions and patterns from labels source
			$this->parseLabels($source, $entries, $patterns);
			SigPlusNovoLogging::appendStatus(count($entries).' caption(s) and '.count($patterns).' pattern(s) extracted from <code>'.$source.'</code>.');

			// update title and description patterns
			if (!empty($patterns)) {
				$rows = array();
				foreach ($patterns as $pattern) {
					$row = array(
						$folderid,
						$db->quote($pattern->match),
						$langid,
						$countryid,
						$pattern->priority,
						$db->quote($pattern->title),
						$db->quote($pattern->summary)
					);
					$rows[] = '('.implode(',',$row).')';
				}

				// add captions matched with patterns
				$queries[] =
					'INSERT INTO '.$db->quoteName('#__sigplus_foldercaption').' ('.
						$db->quoteName('folderid').','.
						$db->quoteName('pattern').','.
						$db->quoteName('langid').','.
						$db->quoteName('countryid').','.
						$db->quoteName('priority').','.
						$db->quoteName('title').','.
						$db->quoteName('summary').
					')'.PHP_EOL.
					'VALUES '.implode(',',$rows)
				;
			}

			// insert new labels data
			if (!empty($entries)) {
				$rows = array();
				foreach ($entries as $entry) {
					$row = array(
						'(SELECT '.$db->quoteName('imageid').' FROM '.$db->quoteName('#__sigplus_image').' WHERE '.$db->quoteName('fileurl').' = '.$db->quote($entry->file).')',  // look up image identifier that belongs to unique file URL
						$langid,
						$countryid,
						$entry->index,
						$db->quote($entry->title),
						$db->quote($entry->summary)
					);
					$rows[] = '('.implode(',',$row).')';
				}

				// add captions
				$queries[] =
					'INSERT INTO '.$db->quoteName('#__sigplus_caption').' ('.
						$db->quoteName('imageid').','.
						$db->quoteName('langid').','.
						$db->quoteName('countryid').','.
						$db->quoteName('ordnum').','.
						$db->quoteName('title').','.
						$db->quoteName('summary').
					')'.PHP_EOL.
					'VALUES '.implode(',',$rows)
				;
			}
		}

		SigPlusNovoDatabase::executeAll($queries);
	}
}

class SigPlusNovoImageMetadata {
	private $imagepath;
	private $metadata;

	/**
	* Fetches metadata associated with an image.
	*/
	public function __construct($imagepath, $type) {
		$this->imagepath = $imagepath;

		require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'metadata.php';
		$this->metadata = SigPlusNovoMetadataServices::getImageMetadata($imagepath, $type);
	}

	/**
	* Adds image metadata to the database.
	*/
	public function inject($imageid) {
		// insert image metadata
		if ($this->metadata !== false) {
			SigPlusNovoLogging::appendStatus('Metadata available in image <code>'.$this->imagepath.'</code> [id='.$imageid.'].');
			$entries = array();

			foreach ($this->metadata as $key => $metavalue) {
				$keyid = SigPlusNovoMetadataServices::getPropertyNumericKey($key);
				if ($keyid) {  // key maps to a numeric identifier
					if (is_array($metavalue)) {
						$value = implode(';', $metavalue);
					} else {
						$value = (string) $metavalue;
					}
					$value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
					$entries[] = array($keyid, $value);
				}
			}

			SigPlusNovoDatabase::insertBatch(
				'#__sigplus_data',
				array('imageid','propertyid'),
				array('propertyid','textvalue'),
				$entries,
				null,
				array('imageid' => $imageid)
			);
		}
	}
}

/**
* Base class for gallery generators.
*/
abstract class SigPlusNovoGalleryBase {
	protected $config;

	public function __construct(SigPlusNovoConfigurationParameters $config) {
		$this->config = $config;
	}

	protected static function getImageSize($image_path) {
		$width = 0;
		$height = 0;
		$mime = null;
		if ('svg' != strtolower(pathinfo($image_path, PATHINFO_EXTENSION))) {
			$image_dims = fsx::getimagesize($image_path);
			if ($image_dims !== false) {
				$width = $image_dims[0];
				$height = $image_dims[1];
				$mime = $image_dims['mime'];
			}
		} else {
			$xml = simplexml_load_file($image_path);
			if ($xml !== false) {
				$xml_attributes = $xml->attributes();
				if (isset($xml_attributes->width) && isset($xml_attributes->height)) {
					// string to number conversion in PHP drops trailing part not consisting of digits such as optional suffix "px"
					$width = (int) $xml_attributes->width;
					$height = (int) $xml_attributes->height;
				}
				$mime = 'image/svg+xml';
			}
		}
		return array(
			0 => $width,
			1 => $height,
			'mime' => $mime
		);
	}

	public function update($url, $folderparams) {
		$db = JFactory::getDbo();
		$dbtype = $db->getServerType();
		try {
			switch ($dbtype) {
				case 'mssql':
					sqlsrv_begin_transaction($db->getConnection());
					break;
				default:
					$db->transactionStart(true);
			}
			$result = $this->populate($url, $folderparams);
			switch ($dbtype) {
				case 'mssql':
					sqlsrv_commit($db->getConnection());
					break;
				default:
					$db->transactionCommit(true);
			}
			return $result;
		} catch (Exception $e) {
			switch ($dbtype) {
				case 'mssql':
					sqlsrv_rollback($db->getConnection());
					break;
				default:
					$db->transactionRollback(true);
			}
			throw $e;
		}
	}

	public abstract function populate($url, $folderparams);

	/**
	* Query a folder identifier for a folder with matching parameters.
	*/
	private function getFolder($url, $folderparams) {
		$datetime = SigPlusNovoDatabase::sqldate($folderparams->time);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select(array('folderid','foldertime','entitytag'))
			->from('#__sigplus_folder')
			->where('folderurl = '.$db->quote($url))
		;
		$db->setQuery($query);
		$row = $db->loadRow();
		if ($row !== false) {
			list($folderid, $foldertime, $entitytag) = $row;
			if ($datetime == $foldertime && $entitytag == $folderparams->entitytag) {  // no changes to folder
				return $folderid;
			}
		}
		return false;
	}

	/**
	* Insert or update data associated with a folder URL.
	*/
	private function updateFolder($url, $folderparams, $replace = false, array $ancestors = array()) {
		$datetime = SigPlusNovoDatabase::sqldate($folderparams->time);

		// insert folder data
		if ($replace) {
			// delete and insert data
			$folderid = SigPlusNovoDatabase::replaceSingle(
				'#__sigplus_folder',
				array('folderurl' => $url),
				array('folderurl', 'foldertime', 'entitytag'),
				array($url, $datetime, $folderparams->entitytag)
			);
		} else {
			if (!($folderid = $this->getFolder($url, $folderparams))) {
				// insert folder data with replacement on duplicate key
				$folderid = SigPlusNovoDatabase::insertSingleUnique(
					'#__sigplus_folder',
					array('folderurl'),
					array('folderurl', 'foldertime', 'entitytag'),
					array($url, $datetime, $folderparams->entitytag),
					'folderid'
				);
			}
		}

		// insert folder hierarchy data
		$entries = array(
			array($folderid, 0)
		);
		$ancestors = array_values($ancestors);  // re-index array
		foreach ($ancestors as $depth => $ancestor) {
			$entries[] = array($ancestor, $depth + 1);
		}
		SigPlusNovoDatabase::insertBatch(
			'#__sigplus_hierarchy',
			array('ancestorid','descendantid'),
			array(
				'ancestorid',
				'depthnum'
			),
			$entries,
			null,
			array('descendantid' => $folderid)
		);

		return $folderid;
	}

	protected function insertFolder($url, $folderparams, array $ancestors = array()) {
		return $this->updateFolder($url, $folderparams, false, $ancestors);
	}

	protected function replaceFolder($url, $folderparams, array $ancestors = array()) {
		return $this->updateFolder($url, $folderparams, true, $ancestors);
	}

	private static function getSizeHashBase($width, $height, $crop) {
		$cross = ($crop ? 'x' : 's');
		return "{$width}{$cross}{$height}";
	}

	protected function getViewHash($folderid) {
		$config = $this->config->gallery;
		$parts = array(
			$folderid,
			self::getSizeHashBase($config->preview_width, $config->preview_height, $config->preview_crop),
			self::getSizeHashBase($config->thumb_width, $config->thumb_height, $config->thumb_crop)
		);
		if ($config->watermark_position !== false) {
			$parts[] = "{$config->watermark_x}{$config->watermark_position}{$config->watermark_y}";
			$parts[] = base64_encode($config->watermark_source);  // ensure safety with special characters in file name
		}
		if ($config->caption_source !== false && $config->caption_source != SIGPLUS_DEFAULT_CAPTION_SOURCE) {
			$parts[] = base64_encode($config->caption_source);  // ensure safety with special characters in file name
		}
		return md5(implode(':', $parts), true);
	}

	protected function getView($folderid) {
		$db = JFactory::getDbo();
		$folderid = (int) $folderid;
		$hash = $this->getViewHash($folderid);

		// verify if preview image parameters for the folder have changed
		$query = $db->getQuery(true);
		$query
			->select('viewid')
			->from('#__sigplus_view')
			->where(
				array(
					'folderid = '.$folderid,
					'hash = '.SigPlusNovoDatabase::quoteValue($hash)
				)
			)
		;
		$db->setQuery($query);
		return $db->loadResult();
	}

	protected function insertView($folderid) {
		$folderid = (int) $folderid;
		if ($viewid = $this->getView($folderid)) {
			return $viewid;
		} else {
			return SigPlusNovoDatabase::insertSingleUnique(
				'#__sigplus_view',
				array('hash'),
				array('folderid', 'hash', 'preview_width', 'preview_height', 'preview_crop'),
				array($folderid, $this->getViewHash($folderid), $this->config->gallery->preview_width, $this->config->gallery->preview_height, $this->config->gallery->preview_crop),
				'viewid'
			);
		}
	}

	protected function cleanImageViews($imageid, $viewid) {
		$db = JFactory::getDbo();
		$conditions = array();

		if (is_array($imageid)) {
			$imageid = array_map('intval', $imageid);
			$conditions[] = $db->quoteName('imageid').' IN ('.implode(',',$imageid).')';
		} elseif (isset($imageid)) {
			$imageid = (int) $imageid;
			$conditions[] = $db->quoteName('imageid').' = '.$imageid;
		}

		if (is_array($viewid)) {
			$viewid = array_map('intval', $viewid);
			$conditions[] = $db->quoteName('viewid').' IN ('.implode(',',$viewid).')';
		} elseif (isset($viewid)) {
			$viewid = (int) $viewid;
			$conditions[] = $db->quoteName('viewid').' = '.$viewid;
		}

		if (is_array($imageid) && is_array($viewid)) {
			$condition = implode(' OR ', $conditions);
		} else {
			$condition = implode(' AND ', $conditions);
		}

		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__sigplus_imageview').PHP_EOL.
			'WHERE '.$condition
		);
		$db->execute();
	}

	protected function replaceView($folderid) {
		// explicitly clean dependent views; some database engines do not allow multiple cascade paths
		$viewid = $this->getView($folderid);
		if ($viewid) {
			$this->cleanImageViews(null, $viewid);
		}

		// replace view
		$hash = $this->getViewHash($folderid);
		return SigPlusNovoDatabase::replaceSingle(
			'#__sigplus_view',
			array('hash' => $hash),
			array('folderid', 'hash', 'preview_width', 'preview_height', 'preview_crop'),
			array($folderid, $hash, $this->config->gallery->preview_width, $this->config->gallery->preview_height, $this->config->gallery->preview_crop)
		);
	}

	private function unlinkGeneratedImage($path, $filetime) {
		if ($path && file_exists($path) && $filetime == fsx::filemdate($path)) {
			unlink($path);
		}
	}

	/**
	* Removes an image from the file system that has been obsoleted by updated configuration settings.
	*/
	protected function cleanGeneratedImages($imageid, $viewid = null) {
		$db = JFactory::getDbo();
		$imageid = (int) $imageid;

		if (isset($viewid)) {
			$viewid = (int) $viewid;
			$cond = ' AND '.$db->quoteName('viewid').' = '.$viewid;
		} else {
			$cond = '';
		}

		// verify if preview image parameters for the folder have changed
		$db->setQuery(
			'SELECT'.PHP_EOL.
				$db->quoteName('thumb_fileurl').','.PHP_EOL.
				$db->quoteName('thumb_filetime').','.PHP_EOL.
				$db->quoteName('preview_fileurl').','.PHP_EOL.
				$db->quoteName('preview_filetime').','.PHP_EOL.
				$db->quoteName('retina_fileurl').','.PHP_EOL.
				$db->quoteName('retina_filetime').','.PHP_EOL.
				$db->quoteName('watermark_fileurl').','.PHP_EOL.
				$db->quoteName('watermark_filetime').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_imageview').PHP_EOL.
			'WHERE'.PHP_EOL.
				$db->quoteName('imageid').' = '.$imageid.$cond
		);
		$rows = $db->loadRowList();

		if (!empty($rows)) {
			foreach ($rows as $row) {
				list(
					$thumb_path, $thumb_filetime,
					$preview_path, $preview_filetime,
					$retina_fileurl, $retina_filetime,
					$watermark_path, $watermark_filetime
				) = $row;

				// delete obsoleted images
				$this->unlinkGeneratedImage($retina_fileurl, $retina_filetime);
				$this->unlinkGeneratedImage($preview_path, $preview_filetime);
				$this->unlinkGeneratedImage($thumb_path, $thumb_filetime);
				$this->unlinkGeneratedImage($watermark_path, $watermark_filetime);
			}

			// remove entries from the database
			$this->cleanImageViews($imageid, $viewid);
		}
	}

	/**
	* Cleans the database of image files that no longer exist.
	*/
	protected function purgeFolder($folderid) {
		// purge images
		$db = JFactory::getDbo();
		$folderid = (int) $folderid;
		$db->setQuery(
			'SELECT'.PHP_EOL.
				$db->quoteName('imageid').','.PHP_EOL.
				$db->quoteName('fileurl').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_image').PHP_EOL.
			'WHERE '.$db->quoteName('folderid').' = '.$folderid
		);
		$rows = $db->loadRowList();

		if (!empty($rows)) {
			$missing = array();

			// find image entries that point to files that have been removed from the file system
			foreach ($rows as $row) {
				list($id, $url) = $row;

				if (is_absolute_path($url) && !file_exists($url)) {
					$this->cleanGeneratedImages($id);
					SigPlusNovoLogging::appendStatus('Image <code>'.$url.'</code> is about to be removed from the database.');
					$missing[] = $id;
				}
			}

			if (!empty($missing)) {
				// explicitly clean dependent views; some database engines do not allow multiple cascade paths
				$this->cleanImageViews($missing, null);

				$db->setQuery(
					'DELETE FROM '.$db->quoteName('#__sigplus_image').PHP_EOL.
					'WHERE '.$db->quoteName('imageid').' IN ('.implode(',',$missing).')'
				);
				$db->execute();
			}
		}

		// purge deleted previews and thumbnails
		$db = JFactory::getDbo();
		$folderid = (int) $folderid;
		$db->setQuery(
			'SELECT'.PHP_EOL.
				'i.'.$db->quoteName('imageid').','.PHP_EOL.
				'i.'.$db->quoteName('viewid').','.PHP_EOL.
				'i.'.$db->quoteName('thumb_fileurl').','.PHP_EOL.
				'i.'.$db->quoteName('preview_fileurl').','.PHP_EOL.
				'i.'.$db->quoteName('retina_fileurl').','.PHP_EOL.
				'i.'.$db->quoteName('watermark_fileurl').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_imageview').' AS i'.PHP_EOL.
				'INNER JOIN '.$db->quoteName('#__sigplus_view').' AS f'.PHP_EOL.
				'ON i.'.$db->quoteName('viewid').' = f.'.$db->quoteName('viewid').PHP_EOL.
			'WHERE f.'.$db->quoteName('folderid').' = '.$folderid
		);
		$rows = $db->loadRowList();

		if (!empty($rows)) {
			SigPlusNovoLogging::appendStatus('Cleaning deleted preview and thumbnail images from database.');

			// find image entries that point to files that have been removed from the file system
			foreach ($rows as $row) {
				list(
					$imageid,
					$viewid,
					$thumb_url,
					$preview_url,
					$retina_url,
					$watermark_url
				) = $row;

				$thumb_missing = is_absolute_path($thumb_url) && !file_exists($thumb_url);
				$preview_missing = is_absolute_path($preview_url) && !file_exists($preview_url);
				$retina_missing = is_absolute_path($retina_url) && !file_exists($retina_url);
				$watermark_missing = is_absolute_path($watermark_url) && !file_exists($watermark_url);

				if ($thumb_missing || $preview_missing || $retina_missing || $watermark_missing) {
					$this->cleanGeneratedImages($imageid, $viewid);
				}
			}
		}
	}

	/**
	* Remove image views that have been persisted in the cache but removed manually.
	*/
	protected function purgeCache() {
		switch ($this->config->service->cache_image) {
			case 'cache':  // images are set to be generated in the Joomla cache folder
				$thumb_folder = JPATH_CACHE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->service->folder_thumb);
				$preview_folder = JPATH_CACHE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->service->folder_preview);
				break;
			case 'media':  // images are set to be generated in the Joomla media folder
				$media_folder = JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.SIGPLUS_MEDIA_FOLDER.DIRECTORY_SEPARATOR;
				$thumb_folder = $media_folder.str_replace('/', DIRECTORY_SEPARATOR, $this->config->service->folder_thumb);
				$preview_folder = $media_folder.str_replace('/', DIRECTORY_SEPARATOR, $this->config->service->folder_preview);
				break;
			default:
				return;  // generated images are not to be cleaned automatically
		}

		if (file_exists($thumb_folder) && file_exists($preview_folder)) {
			return;  // thumb and preview folder not removed
		}

		SigPlusNovoLogging::appendStatus('Manual removal of cache folders detected.');
		$db = JFactory::getDbo();

		// escape special characters, append any character qualifier at end, quote string
		$thumb_pattern = SigPlusNovoDatabase::sqlstartswith($thumb_folder);
		$preview_pattern = SigPlusNovoDatabase::sqlstartswith($preview_folder);

		// remove views from database with deleted image files
		$db->setQuery(
			'DELETE FROM '.$db->quoteName('#__sigplus_imageview').PHP_EOL.
			'WHERE'.PHP_EOL.
				$db->quoteName('thumb_fileurl').' LIKE '.$db->quote($thumb_pattern).' OR '.
				$db->quoteName('preview_fileurl').' LIKE '.$db->quote($preview_pattern).' OR '.
				$db->quoteName('retina_fileurl').' LIKE '.$db->quote($preview_pattern)
		);
		$db->execute();
	}
}

abstract class SigPlusNovoLocalBase extends SigPlusNovoGalleryBase {
	/**
	* Creates a thumbnail image, a preview image, and a watermarked image for an original.
	* Images are generated only if they do not already exist.
	* A separate thumbnail image is generated if the preview is too large to act as a thumbnail.
	* @param {string} $resource_path An absolute file system path to an image-like resource.
	* @return {string} An absolute file system path to an image.
	*/
	private function getGeneratedImages($resource_path) {
		SigPlusNovoTimer::checkpoint();

		if (SigPlusNovoMediaTypes::isVideoFile($resource_path)) {
			$image_path = SigPlusNovoMediaTypes::getPosterImage($resource_path);
		} else {
			$image_path = $resource_path;
		}

		list($image_width, $image_height) = self::getImageSize($image_path);

		$previewparams = new SigPlusNovoPreviewParameters($this->config->gallery);  // current image generation parameters
		$thumbparams = new SigPlusNovoThumbParameters($this->config->gallery);
		$waterparams = new SigPlusNovoWatermarkParameters($this->config->gallery);

		$imagelibrary = SigPlusNovoImageLibrary::instantiate($this->config->service->library_image);

		// create watermarked image
		if ($this->config->gallery->watermark_position !== false && $resource_path == $image_path && ($watermarkpath = $this->getWatermarkPath(dirname($image_path))) !== false) {
			$watermarkedpath = $this->getWatermarkedPath($image_path, $waterparams, SIGPLUS_TEST);
			if ($watermarkedpath === false || !(fsx::filemtime($watermarkedpath) >= fsx::filemtime($image_path))) {  // watermarked image does not yet exist
				$watermarkedpath = $this->getWatermarkedPath($image_path, $waterparams, SIGPLUS_CREATE);
				$watermarkparams = new stdClass();
				$watermarkparams->position = $this->config->gallery->watermark_position;
				$watermarkparams->x = $this->config->gallery->watermark_x;
				$watermarkparams->y = $this->config->gallery->watermark_y;
				$watermarkparams->quality = $previewparams->quality;  // GD cannot extract quality parameter from stored image, use quality set by user
				$result = $imagelibrary->createWatermarked($image_path, $watermarkpath, $watermarkedpath, $watermarkparams);
				if ($result) {
					SigPlusNovoLogging::appendStatus('Saved watermarked image to <code>'.$watermarkedpath.'</code>.');
				} else {
					SigPlusNovoLogging::appendError('Unable to save watermarked image to <code>'.$watermarkedpath.'</code>.');
				}
			}
		}

		$outparams = array();

		// create thumbnail image
		$thumb_path = $this->getThumbnailPath($image_path, $thumbparams, SIGPLUS_TEST);
		if ($thumb_path === false || !(fsx::filemtime($thumb_path) >= fsx::filemtime($image_path))) {  // separate thumbnail image is required
			$thumb_path = $this->getThumbnailPath($image_path, $thumbparams, SIGPLUS_CREATE);
			$outparam = new stdClass();
			$outparam->path = $thumb_path;
			list($outparam->width, $outparam->height) = imagefitdimensions($image_width, $image_height, $thumbparams->width, $thumbparams->height, $thumbparams->crop);
			$outparam->crop = $thumbparams->crop;
			$outparam->quality = $thumbparams->quality;
			$outparams[] = $outparam;
			SigPlusNovoLogging::appendStatus('Saving thumbnail to <code>'.$thumb_path.'</code>');
		}

		// create preview image
		$preview_path = $this->getPreviewPath($image_path, $previewparams, SIGPLUS_TEST);
		if ($preview_path === false || !(fsx::filemtime($preview_path) >= fsx::filemtime($image_path))) {  // create image on-the-fly if does not exist
			$preview_path = $this->getPreviewPath($image_path, $previewparams, SIGPLUS_CREATE);
			$outparam = new stdClass();
			$outparam->path = $preview_path;
			list($outparam->width, $outparam->height) = imagefitdimensions($image_width, $image_height, $previewparams->width, $previewparams->height, $previewparams->crop);
			$outparam->crop = $previewparams->crop;
			$outparam->quality = $previewparams->quality;
			$outparams[] = $outparam;
			SigPlusNovoLogging::appendStatus('Saving preview image to <code>'.$preview_path.'</code>');
		}

		// create preview image for retina display
		$preview_retina_scale = $this->config->gallery->preview_retina_scale;
		if ($preview_retina_scale > 1) {
			$retinaparams = clone $previewparams;
			$retinaparams->width *= $preview_retina_scale;
			$retinaparams->height *= $preview_retina_scale;
			$retina_path = $this->getPreviewPath($image_path, $retinaparams, SIGPLUS_TEST);
			if ($retina_path === false || !(fsx::filemtime($retina_path) >= fsx::filemtime($image_path))) {  // create image on-the-fly if does not exist
				$retina_path = $this->getPreviewPath($image_path, $retinaparams, SIGPLUS_CREATE);
				$outparam = new stdClass();
				$outparam->path = $retina_path;
				list($outparam->width, $outparam->height) = imagefitdimensions($image_width, $image_height, $retinaparams->width, $retinaparams->height, $retinaparams->crop);
				$outparam->crop = $retinaparams->crop;
				$outparam->quality = $retinaparams->quality;
				$outparams[] = $outparam;
				SigPlusNovoLogging::appendStatus('Saving retina preview image to <code>'.$retina_path.'</code>');
			}
		}

		if (!empty($outparams)) {
			$result = $imagelibrary->createThumbnail($image_path, $outparams);
			if (!$result) {
				SigPlusNovoLogging::appendError('Some images could not be generated.');
			}
		}

		return $image_path;
	}

	/**
	* Creates a directory if it does not already exist.
	* @param {string} $directory The full path to the directory.
	*/
	private function createDirectoryOnDemand($directory) {
		if (!is_dir($directory)) {  // directory does not exist
			@mkdir($directory, 0755, true);  // try to create it
			if (!is_dir($directory)) {
				throw new SigPlusNovoFolderCreateException($directory);
			}
			// create an index.html to prevent getting a web directory listing
			@file_put_contents($directory.DIRECTORY_SEPARATOR.'index.html', '<html><body></body></html>');
		}
	}

	/**
	* The full path to an image used for watermarking.
	* @param {string} $imagedirectory The full path to a directory where images to watermark are to be found.
	* @return {string} The full path to a watermark image, or false if none is found.
	*/
	private function getWatermarkPath($imagedirectory) {
		$watermark_image = $this->config->gallery->watermark_source;
		// look inside image gallery folder (e.g. "images/stories/myfolder")
		$watermark_in_gallery = $imagedirectory.DIRECTORY_SEPARATOR.$watermark_image;
		// look inside watermark subfolder of image gallery folder (e.g. "images/stories/myfolder/watermark")
		$watermark_in_subfolder = $imagedirectory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->service->folder_watermarked).DIRECTORY_SEPARATOR.$watermark_image;
		// look inside base path (e.g. "images/stories")
		$watermark_in_base = $this->config->service->base_folder.DIRECTORY_SEPARATOR.$watermark_image;

		if (is_file($watermark_in_gallery)) {
			return $watermark_in_gallery;
		} elseif (is_file($watermark_in_subfolder)) {
			return $watermark_in_subfolder;
		} elseif (is_file($watermark_in_base)) {
			return $watermark_in_base;
		} else {
			return false;
		}
	}

	/**
	* Test or create full path to a generated image (e.g. preview image or thumbnail) based on configuration settings.
	* @param {string} $generatedfolder The folder where generated images are to be stored.
	* @return {bool|string} The path to the generated image, or false if it does not exist.
	*/
	private function getGeneratedImagePath($generatedfolder, $imagepath, SigPlusNovoImageParameters $params, $action = SIGPLUS_TEST) {
		switch ($this->config->service->cache_image) {
			case 'cache':  // images are set to be generated in the Joomla cache folder
				$directory = JPATH_CACHE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $generatedfolder);
				$path = $directory.DIRECTORY_SEPARATOR.$params->getHash($imagepath, $this->config->service->base_folder);  // hash original image file paths to avoid name conflicts
				break;
			case 'media':  // images are set to be generated in the Joomla media folder
				$directory = JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.SIGPLUS_MEDIA_FOLDER.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $generatedfolder);
				$path = $directory.DIRECTORY_SEPARATOR.$params->getHash($imagepath, $this->config->service->base_folder);  // hash original image file paths to avoid name conflicts
				break;
			case 'source':  // images are set to be generated inside folders within the directory where the images are
				$directory = dirname($imagepath).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $generatedfolder);
				$subfolder = $params->getNamingPrefix();
				if ($subfolder) {
					$directory .= DIRECTORY_SEPARATOR.$subfolder;
				}
				$path = $directory.DIRECTORY_SEPARATOR.basename($imagepath);
				break;
		}
		switch ($action) {
			case SIGPLUS_TEST:
				if (is_file($path)) {
					return $path;
				}
				break;
			case SIGPLUS_CREATE:
				$this->createDirectoryOnDemand($directory);
				return $path;
		}
		return false;
	}

	/**
	* Test or create the full path to a watermarked image based on configuration settings.
	* @param {string} $imagepath Absolute path to an image file.
	* @return The full path to a watermarked image, or false on error.
	*/
	private function getWatermarkedPath($imagepath, SigPlusNovoWatermarkParameters $params, $action = SIGPLUS_TEST) {
		return $this->getGeneratedImagePath($this->config->service->folder_watermarked, $imagepath, $params, $action);
	}

	/**
	* Test or create the full path to a preview image based on configuration settings.
	* @param {string} $imagepath Absolute path to an image file.
	* @return The full path to a preview image, or false on error.
	*/
	private function getPreviewPath($imagepath, SigPlusNovoPreviewParameters $params, $action = SIGPLUS_TEST) {
		return $this->getGeneratedImagePath($this->config->service->folder_preview, $imagepath, $params, $action);
	}

	/**
	* Test or create the full path to an image thumbnail based on configuration settings.
	* @param {string} $imageref Absolute path to an image file.
	* @return The full path to an image thumbnail, or false on error.
	*/
	private function getThumbnailPath($imagepath, SigPlusNovoThumbParameters $params, $action = SIGPLUS_TEST) {
		return $this->getGeneratedImagePath($this->config->service->folder_thumb, $imagepath, $params, $action);
	}

	/**
	* Updates an image database entry if necessary.
	* @param {string} $resourcepath An absolute path to an image-like resource (e.g. an image or video).
	* @param {string} $imagepath An absolute path to an image file (e.g. the image itself or a poster image for a video).
	*/
	protected function populateImage($resourcepath, $folderid, $imagepath = null) {
		if (empty($imagepath)) {
			$imagepath = $resourcepath;
		}

		// check if file has been modified since its data have been injected into the database
		$db = JFactory::getDbo();
		$db->setQuery('SELECT '.$db->quoteName('filetime').' FROM '.$db->quoteName('#__sigplus_image').' WHERE '.$db->quoteName('fileurl').' = '.$db->quote($resourcepath));
		$time = $db->loadResult();
		$filetime = fsx::filemdate($resourcepath);
		if ($time == $filetime) {
			SigPlusNovoLogging::appendStatus('Resource <code>'.$resourcepath.'</code> has <em>not</em> changed.');
			return false;
		}

		if ($this->config->gallery->watermark_position !== false && $this->config->gallery->watermark_source == basename($resourcepath)) {
			SigPlusNovoLogging::appendStatus('Skipping resource <code>'.$resourcepath.'</code>, which acts as a watermark image.');
			return false;
		}

		// extract image metadata from file
		$metadata = new SigPlusNovoImageMetadata($resourcepath, $this->config->service->metadata_filter);

		// image size
		list($width, $height) = self::getImageSize($imagepath);
		SigPlusNovoLogging::appendStatus('Image <code>'.$imagepath.'</code> ['.$width.'x'.$height.'] has been added or updated.');

		// image filename and size
		$filename = basename($resourcepath);
		$filesize = fsx::filesize($resourcepath);

		// insert main image data into database
		$imageid = SigPlusNovoDatabase::replaceSingle(  // deletes rows related via foreign key constraints
			'#__sigplus_image',
			array('fileurl' => $resourcepath),
			array('folderid','fileurl','filename','filetime','filesize','width','height'),
			array($folderid, $resourcepath, $filename, $filetime, $filesize, $width, $height)
		);
		SigPlusNovoLogging::appendStatus('Resource <code>'.$resourcepath.'</code> [id='.$imageid.', folder='.$folderid.'] has been recorded in the database.');

		$metadata->inject($imageid);

		return $imageid;
	}

	private function getImageData($path) {
		$time = null;
		$width = 0;
		$height = 0;
		if (isset($path) && $path !== false && file_exists($path)) {
			$time = fsx::filemdate($path);
			list($width, $height) = self::getImageSize($path);
		} else {
			$path = null;
		}
		return array($path, $time, $width, $height);
	}

	protected function populateImageView($resourcepath, $imageid, $viewid) {
		// generate missing images
		$imagepath = $this->getGeneratedImages($resourcepath);

		// image thumbnail path and parameters
		$thumbparams = new SigPlusNovoThumbParameters($this->config->gallery);
		list($thumb_path, $thumb_time, $thumb_width, $thumb_height) = $this->getImageData($this->getThumbnailPath($imagepath, $thumbparams, SIGPLUS_TEST));

		// image preview path and parameters
		$previewparams = new SigPlusNovoPreviewParameters($this->config->gallery);
		list($preview_path, $preview_time, $preview_width, $preview_height) = $this->getImageData($this->getPreviewPath($imagepath, $previewparams, SIGPLUS_TEST));

		// image preview path and parameters for retina display
		$preview_retina_scale = $this->config->gallery->preview_retina_scale;
		if ($preview_retina_scale > 1) {
			$previewparams->width *= $preview_retina_scale;
			$previewparams->height *= $preview_retina_scale;
			list($retina_path, $retina_time, $retina_width, $retina_height) = $this->getImageData($this->getPreviewPath($imagepath, $previewparams, SIGPLUS_TEST));
		} else {
			$retina_path = null;
			$retina_time = null;
			$retina_width = 0;
			$retina_height = 0;
		}

		// watermarked image
		$waterparams = new SigPlusNovoWatermarkParameters($this->config->gallery);
		if ($resourcepath == $imagepath) {
			list($watermarked_path, $watermarked_time) = $this->getImageData($this->getWatermarkedPath($imagepath, $waterparams, SIGPLUS_TEST));
		} else {
			// watermarking cannot be applied to image-like resources that are not image files (e.g. video)
			$watermarked_path = null;
			$watermarked_time = null;
		}

		// insert image view
		SigPlusNovoDatabase::insertSingleUnique(
			'#__sigplus_imageview',
			array('imageid','viewid'),
			array(
				'imageid','viewid',
				'thumb_fileurl','thumb_filetime','thumb_width','thumb_height',
				'preview_fileurl','preview_filetime','preview_width','preview_height',
				'retina_fileurl','retina_filetime','retina_width','retina_height',
				'watermark_fileurl','watermark_filetime'
			),
			array(
				$imageid, $viewid,
				$thumb_path, $thumb_time, $thumb_width, $thumb_height,
				$preview_path, $preview_time, $preview_width, $preview_height,
				$retina_path, $retina_time, $retina_width, $retina_height,
				$watermarked_path, $watermarked_time
			)
		);
	}

	/**
	* Finds images that have no preview or thumbnail image.
	*/
	protected function getMissingImageViews($folderid, $viewid) {
		// add depth condition
		if ($this->config->gallery->depth >= 0) {
			$depthcond = ' AND depthnum <= '.((int) $this->config->gallery->depth);
		} else {
			$depthcond = '';
		}

		$folderid = (int) $folderid;
		$viewid = (int) $viewid;
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT'.PHP_EOL.
				'i.'.$db->quoteName('fileurl').','.PHP_EOL.
				'i.'.$db->quoteName('imageid').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_image').' AS i'.PHP_EOL.
				'INNER JOIN '.$db->quoteName('#__sigplus_folder').' AS f'.PHP_EOL.
				'ON i.'.$db->quoteName('folderid').' = f.'.$db->quoteName('folderid').PHP_EOL.
				'INNER JOIN '.$db->quoteName('#__sigplus_hierarchy').' AS h'.PHP_EOL.
				'ON f.'.$db->quoteName('folderid').' = h.'.$db->quoteName('descendantid').PHP_EOL.
			'WHERE h.'.$db->quoteName('ancestorid').' = '.$folderid.' AND NOT EXISTS (SELECT * FROM '.$db->quoteName('#__sigplus_imageview').' AS v WHERE i.'.$db->quoteName('imageid').' = v.'.$db->quoteName('imageid').' AND v.'.$db->quoteName('viewid').' = '.$viewid.')'.$depthcond
		);
		return $db->loadRowList();
	}

	/**
	* Get last modified time of folder with consideration of changes to labels file.
	* @param {string} $folder A folder in which the labels file is to be found.
	* @param {int} $lastmod A base value for the last modified time, typically obtained with a recursive scan of descendant folders.
	*/
	protected function getLabelsLastModified($folder, $lastmod) {
		// get last modified time of labels file
		$labels = new SigPlusNovoLabels($this->config);  // get labels file manager
		$sources = $labels->getLabelsFilePaths($folder);

		// update last modified time if labels file has been changed
		foreach ($sources as $source) {
			$lastmod = max($lastmod, fsx::filemtime($source));
		}
		return gmdate('Y-m-d H:i:s', $lastmod);  // use SQL DATE format "yyyy-mm-dd hh:nn:ss"
	}
}

/**
* A gallery hosted in the file system.
*/
class SigPlusNovoLocalGallery extends SigPlusNovoLocalBase {
	/**
	* Removes all images and related generated images associated with a folder that has been deleted.
	*/
	private function purgeLocalFolder($url) {
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT '.$db->quoteName('folderid').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_folder').PHP_EOL.
			'WHERE '.$db->quoteName('folderurl').' = '.$db->quote($url)
		);
		$folderid = $db->loadResult();
		if ($folderid) {
			$this->purgeFolder($folderid);
		}
	}

	/**
	* Populates a database equivalent of a folder with images in the folder.
	*/
	public /*private*/ function populateFolder($path, $files, $folders, $ancestors) {
		// add folder
		$folderparams = new SigPlusNovoFolderParameters();
		$folderparams->time = fsx::filemtime($path);  // directory timestamp
		$folderid = $this->insertFolder($path, $folderparams, $ancestors);

		// remove entries that correspond to non-existent images
		$this->purgeFolder($folderid);

		// scan list of files
		$entries = array();
		foreach ($files as $file) {
			$resourcepath = $path.DIRECTORY_SEPARATOR.$file;
			if (SigPlusNovoMediaTypes::isImageFile($resourcepath)) {
				$moviepath = SigPlusNovoMediaTypes::getMovingPicture($resourcepath);
				if ($moviepath === false) {  // image that is not a poster image for a moving picture
					$entry = $this->populateImage($resourcepath, $folderid);
					if ($entry !== false) {
						$entries[] = $entry;
					}
				}
			} elseif (SigPlusNovoMediaTypes::isVideoFile($resourcepath)) {
				$imagepath = SigPlusNovoMediaTypes::getPosterImage($resourcepath);
				if ($imagepath !== false) {  // moving picture that has a poster image
					$entry = $this->populateImage($resourcepath, $folderid, $imagepath);
					if ($entry !== false) {
						$entries[] = $entry;
					}
				}
			}
		}

		return $folderid;
	}

	/**
	* Populates the view of a database equivalent of a folder.
	*/
	protected function populateFolderViews($folderid) {
		// add folder view
		$viewid = (int) $this->insertView($folderid);

		// collect images that have no preview or thumbnail image
		$rows = $this->getMissingImageViews($folderid, $viewid);
		if (!empty($rows)) {
			foreach ($rows as $row) {
				list($path, $imageid) = $row;

				$this->populateImageView($path, $imageid, $viewid);
			}
		} else {
			SigPlusNovoLogging::appendStatus('Folder view [id='.$viewid.'] has not changed.');
		}
		return $viewid;
	}

	/**
	* Generate an image gallery whose images come from the local file system.
	*/
	public function populate($imagefolder, $folderparams) {
		// check whether cache folder has been removed manually by user
		$this->purgeCache();

		if (!file_exists($imagefolder)) {
			$this->purgeLocalFolder($imagefolder);
			return null;
		}

		// get last modified time of folder
		$lastmod = $this->getLabelsLastModified($imagefolder, get_folder_last_modified($imagefolder, $this->config->gallery->depth));

		if (!isset($folderparams->time) || strcmp($lastmod, $folderparams->time) > 0) {
			// get list of direct child and indirect descendant folders and files inside root folder
			$exclude = array(
				$this->config->service->folder_thumb,
				$this->config->service->folder_preview,
				$this->config->service->folder_watermarked,
				$this->config->service->folder_fullsize
			);
			$exclude = array_filter($exclude);  // remove null values from array
			walkdir($imagefolder, $exclude, $this->config->gallery->depth, array($this, 'populateFolder'), array());

			// update folder entry with last modified date
			$folderparams->time = $lastmod;
			$folderid = $this->insertFolder($imagefolder, $folderparams);

			// populate labels from external file
			$labels = new SigPlusNovoLabels($this->config);  // get labels file manager
			$labels->populate($imagefolder, $folderid);
		} else {
			$folderid = $folderparams->id;
			SigPlusNovoLogging::appendStatus('Folder <code>'.$imagefolder.'</code> has not changed.');
		}

		return $this->populateFolderViews($folderid);
	}
}

abstract class SigPlusNovoXMLGallery extends SigPlusNovoGalleryBase {
	public function __construct(SigPlusNovoConfigurationParameters $config) {
		parent::__construct($config);
	}

	protected function getFolderView($url, &$folderparams) {
		// create folder if it does not yet exist
		$folderparams->id = $this->insertFolder($url, $folderparams);

		// get view identifier but do not create one if it does not already exist
		return $this->getView($folderparams->id);
	}
}

abstract class SigPlusNovoAtomFeedGallery extends SigPlusNovoXMLGallery {
	protected function requestFolder($feedurl, &$folderparams, $url, $viewid) {
		// determine whether gallery needs new view
		if ($viewid) {
			$entitytag = $folderparams->entitytag;
		} else {  // no coresponding view available, force retrieval by discarding HTTP entity tag
			SigPlusNovoLogging::appendStatus('<a href="'.$url.'">Web album</a> view is to be re-populated.');
			$entitytag = null;
		}

		// read data from URL only if modified
		list($feeddata, $response_headers) = http_get_modified($feedurl, $folderparams->time, $entitytag);
		$folderparams->time = isset($response_headers['Last-Modified']) ? $response_headers['Last-Modified'] : null;
		$entitytag = isset($response_headers['ETag']) ? $response_headers['ETag'] : null;
		if ($feeddata === true) {  // same HTTP ETag
			SigPlusNovoLogging::appendStatus('<a href="'.$url.'">Web album</a> with ETag <code>'.$folderparams->entitytag.'</code> has not changed.');
			return false;
		} elseif ($feeddata === false) {  // retrieval failure
			throw new SigPlusNovoRemoteException($url);
		}

		// get XML file of list of photos in an album
		$sxml = simplexml_load_string($feeddata);
		if ($sxml === false) {
			throw new SigPlusNovoXMLFormatException($url);
		}

		// update folder data (if necessary)
		if ($entitytag != $folderparams->entitytag) {  // update folder data
			$folderparams->entitytag = $entitytag;
			$folderparams->id = $this->replaceFolder($url, $folderparams);  // clears related image data as a side effect
			SigPlusNovoLogging::appendStatus('<a href="'.$url.'">Web album</a> feed XML has been retrieved, new ETag is <code>'.$folderparams->entitytag.'</code>.');
		} else {
			SigPlusNovoLogging::appendStatus('<a href="'.$url.'">Web album</a> feed XML has not changed.');
		}

		return $sxml;
	}
}

class SigPlusNovoFlickrGallery extends SigPlusNovoXMLGallery {
	/**
	* Generates an image gallery whose images come from Flickr.
	* @see https://www.flickr.com/services/api/
	* @param {string} $url A URL that contains Flickr API key, user ID and album ID.
	*/
	public function populate($url, $folderparams) {
		// parse album feed URL
		$urlparts = parse_url($url);
		if (!preg_match('"^/services/"', $urlparts['path'])) {
			SigPlusNovoLogging::appendError('Invalid Flickr Web Album feed URL <code>'.$url.'</code>.');
			return false;
		}

		// extract Flickr user identifier from feed URL
		$urlquery = array();
		if (isset($urlparts['query'])) {
			parse_str($urlparts['query'], $urlquery);
		}
		$api_key = $urlquery['api_key'];
		$user_id = $urlquery['user_id'];
		$photoset_id = $urlquery['photoset_id'];

		$flickr_url = "https://www.flickr.com/photos/{$user_id}/albums/{$photoset_id}";

		$viewid = $this->getFolderView($url, $folderparams);

		// build URL to check if album has changed
		$feedquery = array(
			'method' => 'flickr.photosets.getInfo',
			'api_key' => $api_key,
			'user_id' => $user_id,
			'photoset_id' => $photoset_id,
			'format' => 'rest'
		);
		$feedurl = 'https://api.flickr.com/services/rest/?'.http_build_query($feedquery);

		// send request
		list($feeddata, $response_headers) = http_get_modified($feedurl);
		$sxml = simplexml_load_string($feeddata);
		if ($sxml === false) {
			throw new SigPlusNovoXMLFormatException($url);
		}

		// check if album has been updated and skip scanning images unless album has changed
		$last_modified = false;
		if (isset($sxml->photoset)) {
			$attrs = $sxml->photoset->attributes();
			$last_modified = gmdate('Y-m-d H:i:s', (int) $attrs['date_update']);
		}
		if (!isset($viewid) || $last_modified != $folderparams->time) {  // update folder data
			$folderparams->time = $last_modified;
			$folderparams->id = $this->replaceFolder($url, $folderparams);  // clears related image data as a side effect
			SigPlusNovoLogging::appendStatus('<a href="'.$flickr_url.'" target="_blank">Flickr web album</a> XML has been updated.');
		} else {
			SigPlusNovoLogging::appendStatus('<a href="'.$flickr_url.'" target="_blank">Flickr web album</a> XML has not changed.');
			return $viewid;
		}

		// build URL to fetch list of photos in (updated) album
		$feedquery = array(
			'method' => 'flickr.photosets.getPhotos',
			'api_key' => $api_key,
			'user_id' => $user_id,
			'photoset_id' => $photoset_id,
			'extras' => 'last_update,o_dims,url_o',
			'format' => 'rest'
		);
		$feedurl = 'https://api.flickr.com/services/rest/?'.http_build_query($feedquery);

		// send request
		SigPlusNovoLogging::appendStatus('Retrieving <a href="'.$flickr_url.'" target="_blank">Flickr web album</a>.');
		list($feeddata, $response_headers) = http_get_modified($feedurl);
		$sxml = simplexml_load_string($feeddata);
		if ($sxml === false) {
			throw new SigPlusNovoXMLFormatException($url);
		}

		// parse XML response
		$entries = array();
		if (isset($sxml->photoset) && isset($sxml->photoset->photo)) {
			foreach ($sxml->photoset->photo as $photo) {  // enumerate album entries with XPath "/photoset/photo"
				$attrs = $photo->attributes();
				$image_url = (string) $attrs['url_o'];
				$width = (int) $attrs['width_o'];
				$height = (int) $attrs['height_o'];
				$last_modified = gmdate('Y-m-d H:i:s', (int) $attrs['lastupdate']);

				// try to locate best-matching preview image size for retina display
				$preview_retina_scale = $this->config->gallery->preview_retina_scale;
				list($retina_url, $retina_width, $retina_height) = self::matchPresetSize(
					$attrs, $image_url, $width, $height,
					$preview_retina_scale * $this->config->gallery->preview_width, $preview_retina_scale * $this->config->gallery->preview_height
				);

				// try to locate best-matching preview image size
				list($preview_url, $preview_width, $preview_height) = self::matchPresetSize(
					$attrs, $image_url, $width, $height,
					$this->config->gallery->preview_width, $this->config->gallery->preview_height
				);

				// try to find best-matching thumbnail image size
				list($thumb_url, $thumb_width, $thumb_height) = self::matchPresetSize(
					$attrs, $image_url, $width, $height,
					$this->config->gallery->thumb_width, $this->config->gallery->thumb_height
				);

				// insert image data
				$imageid = SigPlusNovoDatabase::insertSingleUnique(
					'#__sigplus_image',
					array('fileurl'),
					array(
						'folderid',
						'fileurl',
						'filetime',
						'filesize',
						'width',
						'height'
					),
					array(
						$folderparams->id,
						$image_url,
						$last_modified,
						0,  // information not available for Flickr albums
						$width,
						$height
					),
					'imageid'
				);

				$entries[] = array(
					$imageid,
					$thumb_url,
					$thumb_width,
					$thumb_height,
					$preview_url,
					$preview_width,
					$preview_height,
					$retina_url,
					$retina_width,
					$retina_height
				);
			}
		}

		// update folder view data
		$viewid = (int) $this->replaceView($folderparams->id);  // clears all entries related to the folder as a side effect

		if (!empty($entries)) {
			// insert image data
			SigPlusNovoDatabase::insertBatch(
				'#__sigplus_imageview',
				array('imageid','viewid'),
				array(
					'imageid',
					'thumb_fileurl',
					'thumb_width',
					'thumb_height',
					'preview_fileurl',
					'preview_width',
					'preview_height',
					'retina_fileurl',
					'retina_width',
					'retina_height'
				),
				$entries,
				array('imageid'),
				array('viewid' => $viewid)
			);
		}

		return $viewid;
	}

	private static function matchPresetSize($attrs, $image_url, $image_width, $image_height, $expected_width, $expected_height) {
		static $sizes = array(
			100 => '_t',
			240 => '_m',
			320 => '_n',
			500 => '',
			640 => '_z',
			800 => '_c',
			1024 => '_b',
			1600 => '_h',
			2048 => '_k'
		);

		$photoid = (string) $attrs['id'];
		$secret = (string) $attrs['secret'];
		$farmid = (string) $attrs['farm'];
		$serverid = (string) $attrs['server'];

		// try to locate best-matching preview image size for retina display
		$url = $image_url;
		$width = $image_width;
		$height = $image_height;
		foreach ($sizes as $size => $key) {
			if ($size >= $expected_width && $size >= $expected_height) {
				$url = "https://farm{$farmid}.staticflickr.com/{$serverid}/{$photoid}_{$secret}{$key}.jpg";
				list($width, $height) = imagescaledimensions($image_width, $image_height, $size, $size);
				break;
			}
		}
		return array($url, $width, $height);
	}
}

class SigPlusNovoPicasaGallery extends SigPlusNovoAtomFeedGallery {
	/**
	* Generates an image gallery whose images come from Picasa Web Albums.
	* @see http://picasaweb.google.com
	* @param {string} $url The Picasa album RSS feed URL.
	*/
	public function populate($url, $folderparams) {
		// parse album feed URL
		$urlparts = parse_url($url);

		// extract Picasa user identifier and album identifier from feed URL
		$urlpath = $urlparts['path'];
		$match = array();
		if (!preg_match('"^/data/feed/(?:api|base)/user/([^/?#]+)/albumid/([^/?#]+)"', $urlpath, $match)) {
			throw new SigPlusNovoFeedURLException($url);
		}
		$userid = $match[1];
		$albumid = $match[2];

		$viewid = $this->getFolderView($url, $folderparams);

		// extract feed URL parameters (including authorization key if any)
		$urlquery = array();
		if (isset($urlparts['query'])) {
			parse_str($urlparts['query'], $urlquery);
		}

		// define fixed thumbnail sizes provided by Picasa
		$sizes_cropped = array(32, 48, 64, 72, 104, 144, 150, 160);
		$sizes_uncropped = array_merge($sizes_cropped, array(94, 110, 128, 200, 220, 288, 320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440, 1600));
		sort($sizes_uncropped);

		// set preferred width and height
		$prefwidth = max(100, $this->config->gallery->preview_width);
		$prefheight = max(100, $this->config->gallery->preview_height);

		// choose cropped vs. uncropped
		if ($this->config->gallery->preview_crop) {
			$sizes = $sizes_cropped;
			$crop = 'c';
		} else {
			$sizes = $sizes_uncropped;
			$crop = 'u';
		}

		// get thumbnail size(s) that best match(es) expected preview image dimensions
		$mindim = min($prefwidth, $prefheight);  // smaller dimension
		$minsize = $sizes[0];
		for ($k = 0; $k < count($sizes) && $mindim >= $sizes[$k]; $k++) {  // smaller than both width and height
			$minsize = $sizes[$k];
		}
		$preferred = array($minsize);
		$maxdim = max($prefwidth, $prefheight);  // larger dimension
		for ($k = 0; $k < count($sizes) && $maxdim >= $sizes[$k]; $k++) {
			$preferred[] = $sizes[$k];
		}
		sort($preferred, SORT_REGULAR);
		$preferred = array_unique($preferred, SORT_REGULAR);

		// build URL query string to fetch list of photos in album
		$feedquery = array(
			'v' => '2.0',  // use Google Data Protocol v2.0
			// 'prettyprint' => 'true',  // for debugging purposes only
			'kind' => 'photo',
			'thumbsize' => implode($crop.',', $preferred).$crop,  // preferred thumb sizes
			'fields' => 'id,updated,entry(id,updated,media:group)'  // fetch only the listed XML elements
		);
		if ($this->config->gallery->maxcount > 0) {
			$feedquery['max-results'] = $this->config->gallery->maxcount;
		}
		if (isset($urlquery['authkey'])) {  // pass on authorization key
			$feedquery['authkey'] = $urlquery['authkey'];
		}

		// build URL to fetch list of photos in album
		$uri = JFactory::getURI();
		$scheme = $uri->isSSL() ? 'https:' : 'http:';
		$feedurl = $scheme.'//picasaweb.google.com/data/feed/api/user/'.$userid.'/albumid/'.$albumid.'?'.http_build_query($feedquery, '', '&');

		// send request
		if (($sxml = $this->requestFolder($feedurl, $folderparams, $url, $viewid)) === false) {  // has not changed
			return $viewid;
		}

		// parse XML response
		$entries = array();
		foreach ($sxml->entry as $entry) {  // enumerate album entries with XPath "/feed/entry"
			$time = $entry->updated;

			$media = $entry->children('http://search.yahoo.com/mrss/');  // children with namespace "media"
			$mediagroup = $media->group;

			// get image title and description
			$title = (string) $mediagroup->title;  // TODO: image title currently unused
			$summary = (string) $mediagroup->description;  // TODO: image summary currently unused

			// get image URL
			$attrs = $mediagroup->content->attributes();
			$imageurl = (string) $attrs['url'];  // <media:content url='...' height='...' width='...' type='image/jpeg' medium='image' />
			$width = (int) $attrs['width'];
			$height = (int) $attrs['height'];

			// get preview image URL
			$thumburl = null;
			$thumbwidth = 0;
			$thumbheight = 0;
			foreach ($mediagroup->thumbnail as $thumbnail) {
				$attrs = $thumbnail->attributes();
				$curwidth = (int) $attrs['width'];
				$curheight = (int) $attrs['height'];

				// update thumbnail to use if it fits in image bounds
				if ($prefwidth >= $curwidth && $prefheight >= $curheight && ($curwidth > $thumbwidth || $curheight > $thumbheight)) {
					$thumburl = (string) $attrs['url'];  // <media:thumbnail url='...' height='...' width='...' />
					$thumbwidth = $curwidth;
					$thumbheight = $curheight;
				}
			}

			// insert image data
			$imageid = SigPlusNovoDatabase::insertSingleUnique(
				'#__sigplus_image',
				array('fileurl'),
				array(
					'folderid',
					'fileurl',
					'filetime',
					'filesize',
					'width',
					'height'
				),
				array(
					$folderparams->id,
					$imageurl,
					$time,
					0,  // information not available for Picasa albums
					$width,
					$height
				),
				'imageid'
			);

			$entries[] = array(
				$imageid,
				$thumburl,
				$thumbwidth,
				$thumbheight,
				$thumburl,
				$thumbwidth,
				$thumbheight,
				$thumburl,
				$thumbwidth,
				$thumbheight
			);
		}

		// update folder view data
		$viewid = (int) $this->replaceView($folderparams->id);  // clears all entries related to the folder as a side effect

		// insert image data
		SigPlusNovoDatabase::insertBatch(
			'#__sigplus_imageview',
			array('imageid','viewid'),
			array(
				'imageid',
				'thumb_fileurl',
				'thumb_width',
				'thumb_height',
				'preview_fileurl',
				'preview_width',
				'preview_height',
				'retina_fileurl',
				'retina_width',
				'retina_height'
			),
			$entries,
			array('imageid'),
			array('viewid' => $viewid)
		);

		return $viewid;
	}
}

/**
* A single image hosted on a remote server.
* The image is downloaded to a temporary file for metadata extraction. Properly assembled HTTP
* headers ensure the image is downloaded only if the remote file has been modified.
*/
class SigPlusNovoRemoteImage extends SigPlusNovoGalleryBase {
	public function populate($url, $folderparams) {
		// update image data only if remote image has been modified
		list($imagedata, $response_headers) = http_get_modified($url, $folderparams->time);
		$folderparams->time = isset($response_headers['Last-Modified']) ? $response_headers['Last-Modified'] : null;
		if ($imagedata === true) {  // not modified since specified date
			SigPlusNovoLogging::appendStatus('<a href="'.$url.'">Remote image</a> not modified since <code>'.$folderparams->time.'</code>.');

			if ($viewid = $this->getView($folderparams->id)) {  // preview image is available for remote image
				return $viewid;
			}

			// preview image not available, retrieve image from remote server
			list($imagedata, $response_headers) = http_get_modified($url);
			if ($imagedata === true || $imagedata === false) {  // unexpected response or retrieval failure
				throw new SigPlusNovoRemoteException($url);
			}

			SigPlusNovoLogging::appendStatus('<a href="'.$url.'">Remote image</a> retrieved again as gallery parameters had changed.');
		} elseif ($imagedata === false) {  // retrieval failure
			throw new SigPlusNovoRemoteException($url);
		}

		// update folder entry with last modified date
		SigPlusNovoLogging::appendStatus('<a href="'.$url.'">Remote image</a> was last changed on <code>'.$folderparams->time.'</code>.');
		$folderid = $this->insertFolder($url, $folderparams);

		$metadata = null;
		$filesize = 0;
		$width = null;
		$height = null;

		// create temporary image file and extract metadata
		if ($imagepath = tempnam(JPATH_CACHE, 'sigplus')) {
			if (file_put_contents($imagepath, $imagedata)) {
				SigPlusNovoLogging::appendStatus('Image data has been saved to temporary file <code>'.$imagepath.'</code>.');

				// extract image metadata from file
				$metadata = new SigPlusNovoImageMetadata($imagepath, $this->config->service->metadata_filter);

				// image file size and dimensions
				$filesize = fsx::filesize($imagepath);
				$imagedims = self::getImageSize($imagepath);
				if (isset($imagedims['mime'])) {
					$width = $imagedims[0];
					$height = $imagedims[1];
					SigPlusNovoLogging::appendStatus('<a href="'.$url.'">Remote image</a> has MIME type '.$imagedims['mime'].' and dimensions '.$width.'x'.$height.'.');
				} else {
					throw new SigPlusNovoImageFormatException($url);
				}
			}
			unlink($imagepath);  // "tempnam", if succeeds, always creates the file
		}

		// insert image data into database
		$imageid = SigPlusNovoDatabase::replaceSingle(  // deletes rows related via foreign key constraints
			'#__sigplus_image',
			array('fileurl' => $url),
			array('folderid','fileurl','filename','filetime','filesize','width','height'),
			array($folderid, $url, basename($url), $folderparams->time, $filesize, $width, $height)
		);

		if (isset($metadata)) {
			$metadata->inject($imageid);
		}

		$viewid = (int) $this->insertView($folderid);
		// insert image view
		SigPlusNovoDatabase::insertSingleUnique(
			'#__sigplus_imageview',
			array('imageid','viewid'),
			array(
				'imageid','viewid',
				'preview_fileurl','preview_filetime','preview_width','preview_height'
			),
			array(
				$imageid, $viewid,
				$url, $folderparams->time, $width, $height
			)
		);

		return $viewid;
	}
}

/**
* Exposes the sigplus public services.
*/
class SigPlusNovoCore {
	/**
	* Global service configuration.
	*/
	private $config;
	/**
	* Stack of local gallery configurations.
	*/
	private $paramstack;
	/**
	* Filter to keep whitelisted HTML tags and attributes in caption text but discard others.
	*/
	private $caption_filter;

	public function __construct(SigPlusNovoConfigurationParameters $config) {
		// set global service parameters
		SigPlusNovoLogging::appendCodeBlock('Service parameters are:', print_r($config->service, true));
		$this->config = $config->service;
		$instance = SigPlusNovoEngineServices::instance();
		$instance->debug = $this->config->debug_client;

		// set default parameters for image galleries
		SigPlusNovoLogging::appendCodeBlock('Default gallery parameters are:', print_r($config->gallery, true));
		$this->paramstack = new SigPlusNovoParameterStack();
		$this->paramstack->push($config->gallery);

		$this->caption_filter = new InputFilter(
			array(
				'a','b','blockquote','br','code','del','dd','dl','dt','em','h1','h2','h3','h4','h5','h6','hr','i',
				'img','kbd','li','ol','p','pre','s','sub','sup','strong','strike','table','td','th','tr','ul'
			),
			array(
				'accesskey','align','alt','class','colspan','dir','download','draggable','dropzone','height','hidden',
				'href','hreflang','id','media','rel','rowspan','sizes','spellcheck','style','src','srcset','tabindex',
				'target','title','width'
			)
		);
	}

	public function verbosityLevel() {
		return $this->config->debug_server;
	}

	/**
	* Maps an image folder to a full file system path.
	* @param {string} $entry A simple directory entry (file or folder).
	*/
	private function getImageGalleryPath($entry) {
		$root = $this->config->base_folder;
		if (!is_absolute_path($this->config->base_folder)) {
			$root = JPATH_ROOT.DIRECTORY_SEPARATOR.$root;
		}
		if ($entry) {
			return $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $entry);  // replace '/' with platform-specific directory separator
		} else {
			return $root;
		}
	}

	/**
	* The full file system path to a high-resolution image version.
	* @param {string} $imagepath An absolute path to an image file.
	*/
	private function getFullsizeImagePath($imagepath) {
		if (!$this->config->folder_fullsize) {
			return $imagepath;
		}
		$fullsizepath = dirname($imagepath).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $this->config->folder_fullsize).DIRECTORY_SEPARATOR.basename($imagepath);
		if (!is_file($fullsizepath)) {
			return $imagepath;
		}
		return $fullsizepath;
	}

	private function getFilterExpression(SigPlusNovoFilter $filter) {
		$db = JFactory::getDbo();
		$expr = array();
		foreach ($filter->items as $item) {
			if ($item instanceof SigPlusNovoFilter && !$item->is_empty()) {
				// add filter subexpression, e.g. "b or c" in "a and (b or c)"
				$expr[] = self::getFilterExpression($item);
			} elseif (is_string($item)) {
				// add a simple filter, e.g. "b" in "a and b and c"
				$expr[] = $db->quoteName('filename').' LIKE '.$db->quote(SigPlusNovoDatabase::sqlpattern($item));
			}
		}
		return '('.implode(' '.$filter->rel.' ', $expr).')';
	}

	/**
	* Replaces special characters in an identifier with their CSS character escape sequences.
	* @param {string} $id An HTML identifier.
	* @return {string} A valid CSS identifier string that can be used as #id.
	* @see https://mathiasbynens.be/notes/css-escapes
	*/
	private static function css_escape_special_chars($id) {
		$id = preg_replace('/[-!"#$%&\'()*+,.\/:;<=>?@\[\\\\\]^`{|}~]/', '\\\\$0', $id);  // escape special characters like "+" to "\+"
		$id = str_replace(array("\t","\n","\v","\f","\r"," "), array('\t','\n','\v','\f','\r','\ '), $id);  // escape whitespace like " " and linefeed to "\ " and "\n"
		$id = preg_replace('/^\d/', '\\\\3$0 ', $id);  // replace leading digit with Unicode code point and space, e.g. "1" becomes "\31 "
		return $id;
	}

	private static function getFormattedSize($size) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$power = $size > 0 ? floor(log($size, 1000)) : 0;
		return number_format($size / pow(1000, $power), 2, '.', '') . ' ' . $units[$power];
	}

	/**
	* Get an image label with placeholder and default value substitutions.
	*/
	private static function getSubstitutedLabel($text, $default, $template, $filename, $index, $total, $filesize) {
		// use default text if no text is explicitly given
		if (!isset($text) && isset($default)) {
			$text = $default;
		}

		// replace placeholders for file name, current image number and total image count with actual values in template
		if (isset($text) && isset($template)) {
			$text = str_replace(
				array('{$text}','{$name}','{$filename}','{$current}','{$total}','{$filesize}'),
				array($text, pathinfo($filename, PATHINFO_FILENAME), $filename, (string) ($index+1), (string) $total, self::getFormattedSize($filesize)),
				$template
			);
		}

		return $text;
	}

	/**
	* Returns whether the label depends on server data not available on the client side.
	*/
	private static function isFileSizeRequired($template) {
		// check if placeholders for server-dependent values are present in template string
		if (isset($template)) {
			return strpos($template, '{$filesize}') !== false;
		} else {
			return false;
		}
	}

	/**
	* Returns whether the label depends on server data not available on the client side.
	* @param {bool} $is_transformed_image True if a new image has been generated from the original
	* image on the server side (e.g. by means of watermarking).
	*/
	private static function isFileNameRequired($is_transformed_image, $template) {
		// check if placeholders for server-dependent values are present in template string
		if (isset($template) && $is_transformed_image) {
			return strpos($template, '{$filename}') !== false;
		} else {
			return false;
		}
	}

	/**
	* Get an image label with placeholder and default substitutions as plain text with double quote escapes.
	*/
	private static function getLabel($text, $default, $template, $url, $index, $total, $filesize) {
		return self::getSubstitutedLabel($text, $default, $template, basename($url), $index, $total, $filesize);
	}

	/**
	* Ensures that a gallery identifier is unique across the page.
	* A gallery identifier is specified by the user or generated from a counter. Some extensions
	* may duplicate article content on the page (e.g. show a short article extract in a module
	* position), making an identifier no longer unique. This function adds an ordinal to prevent
	* conflicts when the same gallery would occur multiple times on the page, causing scripts
	* not to function properly.
	* @param {string} $galleryid A preferred identifier, or null to have a new identifier generated.
	*/
	public function getUniqueGalleryId($galleryid = false) {
		static $counter = 1000;
		static $galleryids = array();

		if (!$galleryid || in_array($galleryid, $galleryids)) {  // look for identifier in script-lifetime container
			do {
				$counter++;
				$gid = 'sigplus_'.$counter;
			} while (in_array($gid, $galleryids));
			$galleryid = $gid;
		}
		$galleryids[] = $galleryid;
		return $galleryid;
	}

	private function getGalleryStyle() {
		$curparams = $this->paramstack->top();

		$style = 'sigplus-gallery';

		// add custom class annotation
		if ($curparams->classname) {
			$style .= ' '.$curparams->classname;
		}

		if ($curparams->layout == 'hidden') {
			$style .= ' sigplus-hidden';
		}
		switch ($curparams->alignment) {
			case 'left': case 'left-clear': case 'left-float': $style .= ' sigplus-left'; break;
			case 'center': $style .= ' sigplus-center'; break;
			case 'right': case 'right-clear': case 'right-float': $style .= ' sigplus-right'; break;
		}
		switch ($curparams->alignment) {
			case 'left': case 'left-float': case 'right': case 'right-float': $style .= ' sigplus-float'; break;
			case 'left-clear': case 'right-clear': $style .= ' sigplus-clear'; break;
		}

		if ($curparams->lightbox !== false) {
			$instance = SigPlusNovoEngineServices::instance();
			$lightbox = $instance->getLightboxEngine($curparams->lightbox);
			$style .= ' sigplus-lightbox-'.$lightbox->getIdentifier();
		} else {
			$style .= ' sigplus-lightbox-none';
		}

		return $style;
	}

	/**
	* Transforms a file system path into a URL.
	* @param {string} $make_absolute Build absolute URL address with scheme, host and port.
	*/
	public function makeURL($url, $make_absolute = false) {
		if (is_absolute_path($url)) {
			if (strpos($url, JPATH_ROOT.DIRECTORY_SEPARATOR) === 0) {  // file is inside Joomla root folder (including cache or media cache folder)
				$path = substr($url, strlen(JPATH_ROOT.DIRECTORY_SEPARATOR));
				$url = JURI::base(true).'/'.path_url_encode($path);
			} elseif (strpos($url, $this->config->base_folder.DIRECTORY_SEPARATOR) === 0) {  // file is inside base folder
				$path = substr($url, strlen($this->config->base_folder.DIRECTORY_SEPARATOR));
				$url = $this->config->base_url.'/'.path_url_encode($path);
			} else {
				return false;
			}

			// transform relative URLs into absolute URLs if necessary
			if ($make_absolute && strpos($url, JURI::base(true).'/') === 0) {
				$url = JURI::base(false).substr($url, strlen(JURI::base(true)) + 1);
			}
		}
		return $url;
	}

	private function getDownloadAuthorization() {
		$curparams = $this->paramstack->top();

		$user = JFactory::getUser();
		if ($curparams->download !== false && in_array($curparams->download, $user->getAuthorisedViewLevels())) {  // check if user is authorized to download image
			return true;
		} else {
			return false;  // access forbidden to user
		}
	}

	/**
	* Image download URL.
	*/
	private function getImageDownloadUrl($imageid) {
		if (!$this->getDownloadAuthorization()) {
			return false;
		}

		$uri = clone JFactory::getURI();  // URL of current page
		$uri->setVar('sigplus', $imageid);  // add query parameter "sigplus"
		return $uri->toString();
	}

	public function downloadImage($imagesource) {
		$jinput = JFactory::getApplication()->input;
		$imageid = $jinput->getInt('sigplus', 0);
		if ($imageid <= 0) {
			return false;
		}

		// get active set of parameters from the top of the stack
		$curparams = $this->paramstack->top();

		// test user access level
		if (!$this->getDownloadAuthorization()) {  // authorization is required
			SigPlusNovoLogging::appendStatus('User is not authorized to download image.');
			throw new SigPlusNovoImageDownloadAccessException();
		}

		// translate image source into full source specification
		if (is_url_http($imagesource) || is_absolute_path($imagesource)) {
			$source = $imagesource;
		} else {
			$source = $this->getImageGalleryPath(trim($imagesource, '/\\'));  // remove leading and trailing slash and backslash
		}

		// add depth condition
		if ($curparams->depth >= 0) {
			$depthcond = ' AND depthnum <= '.((int) $curparams->depth);
		} else {
			$depthcond = '';
		}

		// test if source contains wildcard character
		if (strpos($source, '*') !== false) {  // contains wildcard character
			// remove file name component of path
			$source = dirname($source);
		}

		// test whether image is part of the gallery
		$db = JFactory::getDbo();
		$imageid = (int) $imageid;
		$db->setQuery(
			'SELECT'.PHP_EOL.
				$db->quoteName('fileurl').','.PHP_EOL.
				$db->quoteName('filename').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_image').' AS i'.PHP_EOL.
				'INNER JOIN '.$db->quoteName('#__sigplus_folder').' AS f'.PHP_EOL.
				'ON i.'.$db->quoteName('folderid').' = f.'.$db->quoteName('folderid').PHP_EOL.
				'INNER JOIN '.$db->quoteName('#__sigplus_hierarchy').' AS h'.PHP_EOL.
				'ON f.'.$db->quoteName('folderid').' = h.'.$db->quoteName('ancestorid').PHP_EOL.
			'WHERE '.$db->quoteName('folderurl').' = '.$db->quote($source).PHP_EOL.
				'AND '.$db->quoteName('imageid').' = '.$imageid.$depthcond
		);
		$row = $db->loadRow();
		if (!$row) {
			SigPlusNovoLogging::appendStatus('Image to download is not found in gallery database.');
			return false;
		}

		list($fileurl, $filename) = $row;
		if (headers_sent($file, $line)) {
			SigPlusNovoLogging::appendStatus('Unable to make browser download image, HTTP headers have already been sent in file "'.$file.'" line '.$line.'.');
			throw new SigPlusNovoImageDownloadHeadersSentException($fileurl);
		}

		// produce HTTP response
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		if (is_absolute_path($fileurl)) {
			$filepath = $this->getFullsizeImagePath($fileurl);

			// return image as HTTP payload
			$size = fsx::getimagesize($filepath);
			if ($size !== false) {
				header('Content-Type: '.$size['mime']);
			}
			$filesize = fsx::filesize($filepath);
			if ($filesize !== false) {
				header('Content-Length: '.$filesize);
			}
			header('Content-Disposition: attachment; filename="'.$filename.'"');

			// discard internal buffer content used for output buffering
			ob_clean();
			flush();

			@fsx::readfile($filepath);
		} else {
			// redirect to image URL
			header('Location: '.$fileurl);

			// discard internal buffer content used for output buffering
			ob_clean();
			flush();
		}
		return true;
	}

	/**
	* Generates image thumbnails with alternate text, title and lightbox pop-up activation on mouse click.
	* This method is typically called by the class plgContentSigPlusNovo, which represents the sigplus Joomla plug-in.
	* The method may modify the top of the parameter stack; the caller must provide a discardable copy.
	* @param {string|boolean} $imagesource A string that defines the gallery source. Relative paths are interpreted
	* w.r.t. the image base folder, which is passed in a configuration object to the class constructor.
	*/
	public function getGalleryHTML($imagesource, &$galleryid) {
		SigPlusNovoTimer::checkpoint();

		// get active set of parameters from the top of the stack
		$curparams = $this->paramstack->top();  // current gallery parameters

		$config = new SigPlusNovoConfigurationParameters();
		$config->gallery = $curparams;
		$config->service = $this->config;

		if ($imagesource === false) {  // use base folder as source if not set
			$imagesource = $this->config->base_folder;
		}

		// make placeholder replacement for {$username}
		if (strpos($imagesource, '{$username}') !== false) {
			$user = JFactory::getUser();
			if ($user->guest) {
				throw new SigPlusNovoLoginRequiredException();
			} else {
				$imagesource = str_replace('{$username}', $user->username, $imagesource);
			}
		}

		// make placeholder replacement for {$group}
		if (strpos($imagesource, '{$group}') !== false) {
			$user = JFactory::getUser();
			if ($user->guest) {
				throw new SigPlusNovoLoginRequiredException();
			} else {
				$groupname = SigPlusNovoUser::getCurrentUserGroup();
				if ($groupname) {
					$groupname = str_replace(' ', '', $groupname);  // normalize whitespace
				} else {
					$groupname = '.';  // no group, use current directory
				}
				$imagesource = str_replace('{$group}', $groupname, $imagesource);
			}
		}

		// set gallery identifier
		$galleryid = $curparams->id = $this->getUniqueGalleryId($curparams->id);

		// show current set of parameters for image galleries
		SigPlusNovoLogging::appendCodeBlock('Local gallery parameters for "'.$galleryid.'" are:', print_r($curparams, true));

		// instantiate image generator
		$generator = null;
		if (strip_tags($imagesource) != $imagesource) {
			throw new SigPlusNovoHTMLCodeException($imagesource);
		} else if (is_url_http($imagesource)) {  // test for Picasa galleries
			$source = $imagesource;
			SigPlusNovoLogging::appendStatus('Generating gallery "'.$galleryid.'" from URL: <code>'.$source.'</code>');
			if (preg_match('"^https?://picasaweb.google.com/"', $source)) {
				$generator = new SigPlusNovoPicasaGallery($config);
			} elseif (preg_match('"^https?://api.flickr.com/services/"', $source)) {
				$generator = new SigPlusNovoFlickrGallery($config);
			} else {
				$generator = new SigPlusNovoRemoteImage($config);
				$curparams->maxcount = 1;
			}
		} else {
			if (is_absolute_path($imagesource)) {
				$source = $imagesource;
			} else {
				$source = $this->getImageGalleryPath(trim($imagesource, '/\\'));  // remove leading and trailing slash and backslash
			}

			// parse wildcard patterns in file name component
			if (strpos($source, '*') !== false || strpos($source, '?') !== false) {  // contains wildcard character
				// add implicit include filter on file name component of path
				$filter = $curparams->filter_include;  // save current filter
				$curparams->filter_include = new SigPlusNovoFilter('and');
				$curparams->filter_include->items[] = basename($source);  // add wildcard name to include filter
				$curparams->filter_include->items[] = $filter;  // add current filter as sub-filter

				// remove file name component of path
				$source = dirname($source);

				if (is_dir($source)) {
					// set up gallery populator
					SigPlusNovoLogging::appendStatus('Generating gallery "'.$galleryid.'" from filtered folder: <code>'.$source.'</code>');
					$generator = new SigPlusNovoLocalGallery($config);
				}
			} elseif (is_dir($source)) {
				SigPlusNovoLogging::appendStatus('Generating gallery "'.$galleryid.'" from folder: <code>'.$source.'</code>');
				$generator = new SigPlusNovoLocalGallery($config);
			} elseif (is_file($source)) {
				// set implicit filter to filter exact file name
				$filter = $curparams->filter_include;  // save current filter
				$curparams->filter_include = new SigPlusNovoFilter('and');
				$curparams->filter_include->items[] = basename($source);
				$curparams->filter_include->items[] = $filter;  // add current filter as sub-filter

				// activate single image mode
				$curparams->maxcount = 1;

				// remove file name component of path
				$source = dirname($source);

				SigPlusNovoLogging::appendStatus('Generating gallery "'.$galleryid.'" from file: <code>'.$source.'</code>');
				$generator = new SigPlusNovoLocalGallery($config);
			} else {
				$path_case_sensitive = realpath($source);
				$path_case_insensitive = realpathi($source);
				if ($path_case_sensitive === false && $path_case_insensitive !== false) {
					throw new SigPlusNovoImageSourceCaseMismatchException($source, $path_case_insensitive);
				}
			}
		}
		if (!isset($generator)) {
			throw new SigPlusNovoImageSourceException($imagesource);
		}
		$curparams->validate();  // re-validate parameters to resolve inconsistencies (e.g. rotator with a single image)

		// set image gallery alignment (left, center or right) and text wrap (float or clear)
		$gallerystyle = $this->getGalleryStyle();

		// get properties of folder stored in the database
		$db = JFactory::getDbo();
		$db->setQuery('SELECT '.$db->quoteName('folderid').', '.$db->quoteName('foldertime').', '.$db->quoteName('entitytag').' FROM '.$db->quoteName('#__sigplus_folder').' WHERE '.$db->quoteName('folderurl').' = '.$db->quote($source));
		$result = $db->loadRow();

		$folderparams = new SigPlusNovoFolderParameters();
		if ($result) {
			list($folderparams->id, $folderparams->time, $folderparams->entitytag) = $result;
		}

		// populate image database
		$viewid = $generator->update($source, $folderparams);

		// apply sort criterion and sort order
		switch ($curparams->sort_criterion) {
			case SIGPLUS_SORT_LABELS:  // sort exclusively by caption source order
				switch ($curparams->sort_order) {
					case SIGPLUS_SORT_ASCENDING:
						$sortorder = $db->quoteName('ordnum').' ASC'; break;
					case SIGPLUS_SORT_DESCENDING:
						$sortorder = $db->quoteName('ordnum').' DESC'; break;
				}
				break;
			case SIGPLUS_SORT_LABELS_OR_FILENAME:  // sort by caption source order (primary), then by file name (secondary)
				switch ($curparams->sort_order) {
					case SIGPLUS_SORT_ASCENDING:
						// entries with smallest ordnum are shown first, entries without ordnum shown last
						$sortorder = '-'.$db->quoteName('ordnum').' DESC, '.$db->quoteName('filename').' ASC'; break;  // unary minus inverts sort order, NULL values presented last when doing ORDER BY ... DESC
					case SIGPLUS_SORT_DESCENDING:
						// entries with largest ordnum are shown first, entries without ordnum shown last
						$sortorder = $db->quoteName('ordnum').' DESC, '.$db->quoteName('filename').' DESC'; break;
				}
				break;
			case SIGPLUS_SORT_LABELS_OR_MTIME:  // sort by caption source order (primary), then by last modified timestamp (secondary)
				switch ($curparams->sort_order) {
					case SIGPLUS_SORT_ASCENDING:
						$sortorder = '-'.$db->quoteName('ordnum').' DESC, '.$db->quoteName('filetime').' ASC'; break;
					case SIGPLUS_SORT_DESCENDING:
						$sortorder = $db->quoteName('ordnum').' DESC, '.$db->quoteName('filetime').' DESC'; break;
				}
				break;
			case SIGPLUS_SORT_LABELS_OR_FILESIZE:  // sort by caption source order (primary), then by file size (secondary)
				switch ($curparams->sort_order) {
					case SIGPLUS_SORT_ASCENDING:
						$sortorder = '-'.$db->quoteName('ordnum').' DESC, '.$db->quoteName('filesize').' ASC'; break;
					case SIGPLUS_SORT_DESCENDING:
						$sortorder = $db->quoteName('ordnum').' DESC, '.$db->quoteName('filesize').' DESC'; break;
				}
				break;
			case SIGPLUS_SORT_LABELS_OR_RANDOM:
				switch ($curparams->sort_order) {
					case SIGPLUS_SORT_ASCENDING:
						$sortorder = '-'.$db->quoteName('ordnum').' DESC, RAND()'; break;
					case SIGPLUS_SORT_DESCENDING:
						$sortorder = $db->quoteName('ordnum').' DESC, RAND()'; break;
				}
				break;
			case SIGPLUS_SORT_MTIME:
				switch ($curparams->sort_order) {
					case SIGPLUS_SORT_ASCENDING:
						$sortorder = $db->quoteName('filetime').' ASC'; break;
					case SIGPLUS_SORT_DESCENDING:
						$sortorder = $db->quoteName('filetime').' DESC'; break;
				}
				break;
			case SIGPLUS_SORT_FILESIZE:
				switch ($curparams->sort_order) {
					case SIGPLUS_SORT_ASCENDING:
						$sortorder = $db->quoteName('filesize').' ASC'; break;
					case SIGPLUS_SORT_DESCENDING:
						$sortorder = $db->quoteName('filesize').' DESC'; break;
				}
				break;
			case SIGPLUS_SORT_RANDOM:
				$sortorder = 'RAND()';
				break;
			default:  // case SIGPLUS_SORT_FILENAME:
				switch ($curparams->sort_order) {
					case SIGPLUS_SORT_ASCENDING:
						$sortorder = $db->quoteName('filename').' ASC'; break;
					case SIGPLUS_SORT_DESCENDING:
						$sortorder = $db->quoteName('filename').' DESC'; break;
				}
		}
		$sortorder = $db->quoteName('depthnum').' ASC, '.$sortorder;  // keep descending from topmost to bottommost in hierarchy, do not mix entries from different levels

		// determine current site language
		$lang = JFactory::getLanguage();
		list($language, $country) = explode('-', $lang->getTag());  // site current language
		$langid = (int)SigPlusNovoDatabase::getLanguageId($language);
		$countryid = (int)SigPlusNovoDatabase::getCountryId($country);

		// build SQL condition for depth
		if ($curparams->depth >= 0) {
			$depthcond = ' AND '.$db->quoteName('depthnum').' <= '.$curparams->depth;
		} else {
			$depthcond = '';
		}

		// build SQL condition for file match pattern
		$patterncond = '';
		if (!$curparams->filter_include->is_empty()) {
			$patterncond .= ' AND '.self::getFilterExpression($curparams->filter_include);
		}
		if (!$curparams->filter_exclude->is_empty()) {
			$patterncond .= ' AND NOT '.self::getFilterExpression($curparams->filter_exclude);
		}

		switch ($db->getServerType()) {
			case 'mysql':
				$top1 = '';
				$limit1 = 'LIMIT 1';
				break;
			case 'mssql':
				$top1 = 'TOP 1';
				$limit1 = '';
				break;
		}

		switch ($curparams->sort_criterion) {
			case SIGPLUS_SORT_LABELS:
				$titlequery = 'c.'.$db->quoteName('title');
				$summaryquery = 'c.'.$db->quoteName('summary');
				$captioncond = ' AND (NOT ISNULL('.$db->quoteName('title').') OR NOT ISNULL('.$db->quoteName('summary').'))';

				if (is_absolute_path($source)) {
					$labels = new SigPlusNovoLabels($config);
					if (!$labels->isLabelsFileAvailable($source)) {
						// configuration says to show only images with matching entries in labels file but labels file does not exist
						$captioncond = '';  // show all images instead of uninformative "no images in gallery" message
					}
				}

				break;
			default:
				$titlequery =
					'COALESCE('.PHP_EOL.
						// use image title if set
						'c.'.$db->quoteName('title').','.PHP_EOL.
						// or use meta-data field "Headline" if no image title has been set explicitly
						'('.PHP_EOL.
							'SELECT '.$top1.' md.'.$db->quoteName('textvalue').''.PHP_EOL.
							'FROM '.$db->quoteName('#__sigplus_property').' AS mp'.PHP_EOL.
							'INNER JOIN '.$db->quoteName('#__sigplus_data').' AS md'.PHP_EOL.
							'ON mp.'.$db->quoteName('propertyid').' = md.'.$db->quoteName('propertyid').PHP_EOL.
							'WHERE mp.'.$db->quoteName('propertyname').' = '.$db->quote('Headline').' AND md.'.$db->quoteName('imageid').' = i.'.$db->quoteName('imageid').''.PHP_EOL.
							$limit1.PHP_EOL.
						'),'.PHP_EOL.
						// or use the best wild-card match for the image
						'('.PHP_EOL.
							'SELECT '.$top1.' p.'.$db->quoteName('title').PHP_EOL.
							'FROM '.$db->quoteName('#__sigplus_foldercaption').' AS p'.PHP_EOL.
							'WHERE'.PHP_EOL.
								'p.'.$db->quoteName('langid').' = '.$langid.' AND '.PHP_EOL.
								'p.'.$db->quoteName('countryid').' = '.$countryid.' AND '.PHP_EOL.
								'i.'.$db->quoteName('filename').' LIKE p.'.$db->quoteName('pattern').' AND '.PHP_EOL.
								'i.'.$db->quoteName('folderid').' = p.'.$db->quoteName('folderid').PHP_EOL.
							'ORDER BY p.'.$db->quoteName('priority').' '.$limit1.PHP_EOL.
						')'.PHP_EOL.
					')';
				$summaryquery =
					'COALESCE('.PHP_EOL.
						// use image summary if set
						'c.'.$db->quoteName('summary').','.PHP_EOL.
						// or use meta-data field "Caption-Abstract" if no image summary has been set explicitly
						'('.PHP_EOL.
							'SELECT '.$top1.' md.'.$db->quoteName('textvalue').''.PHP_EOL.
							'FROM '.$db->quoteName('#__sigplus_property').' AS mp'.PHP_EOL.
							'INNER JOIN '.$db->quoteName('#__sigplus_data').' AS md'.PHP_EOL.
							'ON mp.'.$db->quoteName('propertyid').' = md.'.$db->quoteName('propertyid').PHP_EOL.
							'WHERE mp.'.$db->quoteName('propertyname').' = '.$db->quote('Caption-Abstract').' AND md.'.$db->quoteName('imageid').' = i.'.$db->quoteName('imageid').''.PHP_EOL.
							$limit1.PHP_EOL.
						'),'.PHP_EOL.
						// or use the best wild-card match for the image
						'('.PHP_EOL.
							'SELECT '.$top1.' p.'.$db->quoteName('summary').PHP_EOL.
							'FROM '.$db->quoteName('#__sigplus_foldercaption').' AS p'.PHP_EOL.
							'WHERE'.PHP_EOL.
								'p.'.$db->quoteName('langid').' = '.$langid.' AND '.PHP_EOL.
								'p.'.$db->quoteName('countryid').' = '.$countryid.' AND '.PHP_EOL.
								'i.'.$db->quoteName('filename').' LIKE p.'.$db->quoteName('pattern').' AND '.PHP_EOL.
								'i.'.$db->quoteName('folderid').' = p.'.$db->quoteName('folderid').PHP_EOL.
							'ORDER BY p.'.$db->quoteName('priority').' '.$limit1.PHP_EOL.
						')'.PHP_EOL.
					')';
				$captioncond = '';
				break;
		}

		// build and execute SQL query
		$viewid = (int) $viewid;
		$query =
			'SELECT'.PHP_EOL.
				'i.'.$db->quoteName('imageid').','.PHP_EOL.
				'i.'.$db->quoteName('fileurl').','.PHP_EOL.
				'i.'.$db->quoteName('width').','.PHP_EOL.
				'i.'.$db->quoteName('height').','.PHP_EOL.
				'i.'.$db->quoteName('filesize').','.PHP_EOL.
				$titlequery.' AS '.$db->quoteName('title').','.PHP_EOL.
				$summaryquery.' AS '.$db->quoteName('summary').','.PHP_EOL.
				$db->quoteName('thumb_fileurl').','.PHP_EOL.
				$db->quoteName('thumb_width').','.PHP_EOL.
				$db->quoteName('thumb_height').','.PHP_EOL.
				$db->quoteName('preview_fileurl').','.PHP_EOL.
				$db->quoteName('preview_width').','.PHP_EOL.
				$db->quoteName('preview_height').','.PHP_EOL.
				$db->quoteName('retina_fileurl').','.PHP_EOL.
				$db->quoteName('retina_width').','.PHP_EOL.
				$db->quoteName('retina_height').','.PHP_EOL.
				$db->quoteName('watermark_fileurl').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_image').' AS i'.PHP_EOL.
				// folder "f" in which image is to be found
				'INNER JOIN '.$db->quoteName('#__sigplus_folder').' AS f'.PHP_EOL.
				'ON i.'.$db->quoteName('folderid').' = f.'.$db->quoteName('folderid').PHP_EOL.
				// couple folders related to folder "f" in the folder hierarchy
				'INNER JOIN '.$db->quoteName('#__sigplus_hierarchy').' AS h'.PHP_EOL.
				'ON f.'.$db->quoteName('folderid').' = h.'.$db->quoteName('descendantid').PHP_EOL.
				// topmost folder "a" in the folder hierarchy, which the user selects
				'INNER JOIN '.$db->quoteName('#__sigplus_folder').' AS a'.PHP_EOL.
				'ON a.'.$db->quoteName('folderid').' = h.'.$db->quoteName('ancestorid').PHP_EOL.
				'INNER JOIN '.$db->quoteName('#__sigplus_imageview').' AS v'.PHP_EOL.
				'ON i.'.$db->quoteName('imageid').' = v.'.$db->quoteName('imageid').PHP_EOL.
				'LEFT JOIN '.$db->quoteName('#__sigplus_caption').' AS c'.PHP_EOL.
				'ON'.PHP_EOL.
					// no caption belongs to image or caption language matches site language
					'c.'.$db->quoteName('imageid').' = i.'.$db->quoteName('imageid').' AND '.PHP_EOL.
					'c.'.$db->quoteName('langid').' = '.$langid.' AND '.PHP_EOL.
					'c.'.$db->quoteName('countryid').' = '.$countryid.PHP_EOL.
			'WHERE'.PHP_EOL.
				// condition to match folder URL with (activation tag or module) source folder
				'a.'.$db->quoteName('folderurl').' = '.$db->quote($source).' AND '.PHP_EOL.
				// condition to match folder view with activation tag or module instance
				$db->quoteName('viewid').' = '.$viewid.PHP_EOL.
				// include and exclude filters or single image selection
				$patterncond.PHP_EOL.
				// limit on hierarchical listing
				$depthcond.PHP_EOL.
				// limit display to entries explicitly listed in a "labels.txt" file (if applicable)
				$captioncond.PHP_EOL.
			'ORDER BY '.$sortorder
		;
		$db->setQuery($query);
		$cursor = $db->execute();
		if ($cursor) {
			$total = $db->getNumRows();  // get number of images in gallery
		} else {
			$total = 0;
		}
		if ($total > 0) {
			$images = $db->loadRowList();
		} else {
			$images = array();
			$galleryid = null;
		}
		$limit = $curparams->maxcount > 0 ? min($curparams->maxcount, $total) : $total;

		// check if any of the generated image sizes has to be determined automatically
		if ($curparams->preview_width == 0 || $curparams->preview_height == 0 || $curparams->thumb_width == 0 || $curparams->thumb_height == 0) {
			$sizes_query =
				'SELECT'.PHP_EOL.
					'MAX(i.'.$db->quoteName('width').'),'.PHP_EOL.
					'MAX(i.'.$db->quoteName('height').'),'.PHP_EOL.
					'MAX('.$db->quoteName('thumb_width').'),'.PHP_EOL.
					'MAX('.$db->quoteName('thumb_height').'),'.PHP_EOL.
					'MAX('.$db->quoteName('preview_width').'),'.PHP_EOL.
					'MAX('.$db->quoteName('preview_height').'),'.PHP_EOL.
					'MAX('.$db->quoteName('retina_width').'),'.PHP_EOL.
					'MAX('.$db->quoteName('retina_height').')'.PHP_EOL.
				'FROM '.$db->quoteName('#__sigplus_image').' AS i'.PHP_EOL.
					// folder "f" in which image is to be found
					'INNER JOIN '.$db->quoteName('#__sigplus_folder').' AS f'.PHP_EOL.
					'ON i.'.$db->quoteName('folderid').' = f.'.$db->quoteName('folderid').PHP_EOL.
					// couple folders related to folder "f" in the folder hierarchy
					'INNER JOIN '.$db->quoteName('#__sigplus_hierarchy').' AS h'.PHP_EOL.
					'ON f.'.$db->quoteName('folderid').' = h.'.$db->quoteName('descendantid').PHP_EOL.
					// topmost folder "a" in the folder hierarchy, which the user selects
					'INNER JOIN '.$db->quoteName('#__sigplus_folder').' AS a'.PHP_EOL.
					'ON a.'.$db->quoteName('folderid').' = h.'.$db->quoteName('ancestorid').PHP_EOL.
					'INNER JOIN '.$db->quoteName('#__sigplus_imageview').' AS v'.PHP_EOL.
					'ON i.'.$db->quoteName('imageid').' = v.'.$db->quoteName('imageid').PHP_EOL.
				'WHERE'.PHP_EOL.
					// condition to match folder URL with (activation tag or module) source folder
					'a.'.$db->quoteName('folderurl').' = '.$db->quote($source).' AND '.PHP_EOL.
					// condition to match folder view with activation tag or module instance
					$db->quoteName('viewid').' = '.$viewid.PHP_EOL.
					// include and exclude filters or single image selection
					$patterncond.PHP_EOL.
					// limit on hierarchical listing
					$depthcond.PHP_EOL.
				''
			;
			$db->setQuery($sizes_query);
			$sizes = $db->loadRow();
			list(
				$max_width, $max_height,
				$max_thumb_width, $max_thumb_height,
				$max_preview_width, $max_preview_height,
				$max_retina_width, $max_retina_height
			) = $sizes;

			list($curparams->preview_width, $curparams->preview_height) = imagefitdimensions(
				$max_preview_width, $max_preview_height,
				$curparams->preview_width, $curparams->preview_height
			);

			list($curparams->thumb_width, $curparams->thumb_height) = imagefitdimensions(
				$max_thumb_width, $max_thumb_height,
				$curparams->thumb_width, $curparams->thumb_height
			);
		}

		// add images to be used on social network sites
		$this->addOpenGraphProperties($images);

		// generate HTML code for each image
		ob_start();  // start output buffering
		$this->printGallery($galleryid, $gallerystyle, $images, $limit, $total);
		$body = ob_get_clean();  // fetch output buffer

		return $body;
	}

	private function printGallery($galleryid, $gallerystyle, array $images, $limit, $total) {
		$curparams = $this->paramstack->top();  // current gallery parameters

		$layout_path = JPluginHelper::getLayoutPath('content', SIGPLUS_PLUGIN_FOLDER, 'default');
		require($layout_path);
	}

	private function printImage($image, $index, $total, $style = null) {
		$curparams = $this->paramstack->top();  // current gallery parameters

		list(
			$imageid, $file_url, $width, $height, $filesize,
			$title, $summary,
			$thumb_url, $thumb_width, $thumb_height,
			$preview_url, $preview_width, $preview_height,
			$retina_url, $retina_width, $retina_height,
			$watermark_url
		) = $image;

		// translate paths into URLs
		$file_url = $this->makeURL($file_url);
		$thumb_url = $this->makeURL($thumb_url);
		$preview_url = $this->makeURL($preview_url);
		$retina_url = $this->makeURL($retina_url);
		$watermark_url = $this->makeURL($watermark_url);
		$download_url = $this->getImageDownloadUrl($imageid);

		// scale preview image sizes when database-stored image does not match desired dimensions
		list($preview_width, $preview_height) = imagescaledimensions($preview_width, $preview_height, $curparams->preview_width, $curparams->preview_height);

		$filename = basename($file_url);
		$is_transformed_image = isset($watermark_url);

		// this variable is not used directly in this function but in the layout template imported by `JPluginHelper::getLayoutPath`
		$url = $is_transformed_image ? $watermark_url : $file_url;

		$properties = array();
		if (SIGPLUS_CAPTION_CLIENT) {  // client-side template replacement
			$title = $title ? $title : $curparams->caption_title;
			$summary = $summary ? $summary : $curparams->caption_summary;
			if (self::isFileNameRequired($is_transformed_image, $curparams->caption_title_template) || self::isFileNameRequired($is_transformed_image, $curparams->caption_summary_template)) {
				$property = new stdClass;
				$property->key = 'image-file-name';
				$property->value = $filename;
				$properties[] = $property;
			}
			if (self::isFileSizeRequired($curparams->caption_title_template) || self::isFileSizeRequired($curparams->caption_summary_template)) {
				$property = new stdClass;
				$property->key = 'image-file-size';
				$property->value = $filesize;
				$properties[] = $property;
			}
		} else {  // server-side template replacement
			$title = self::getSubstitutedLabel($title, $curparams->caption_title, $curparams->caption_title_template, $filename, $index, $total, $filesize);
			$summary = self::getSubstitutedLabel($summary, $curparams->caption_summary, $curparams->caption_summary_template, $filename, $index, $total, $filesize);
		}

		$title = $this->caption_filter->clean($title, 'html');
		$summary = $this->caption_filter->clean($summary, 'html');

		$layout_path = JPluginHelper::getLayoutPath('content', SIGPLUS_PLUGIN_FOLDER, 'item');
		require($layout_path);
	}

	/**
	* Checks if the document already has Open Graph meta tags.
	* This helps avoid many unnecessary `og:image` meta tags when the page has multiple galleries.
	*/
	private function hasOpenGraphProperties() {
		$document = JFactory::getDocument();
		if ($document->getType() != 'html') {  // custom tags are supported by HTML document type only
			return false;
		}

		// check for existing Open Graph og:image tags
		$headData = $document->getHeadData();
		foreach ($headData['custom'] as $tag) {
			if (preg_match('/^<meta\b.*\bproperty="og:image".*>$/', $tag)) {
				return true;
			}
		}
		return false;
	}

	/**
	* Add Open Graph meta tags to tell social network sites (e.g. Facebook) which images to use as representative images for the page when the page is shared.
	*/
	private function addOpenGraphProperties(array $images) {
		if (empty($images)) {
			return;
		}

		$curparams = $this->paramstack->top();  // current gallery parameters
		if (!$curparams->open_graph) {
			return;
		}

		$document = JFactory::getDocument();
		if ($document->getType() != 'html') {  // custom tags are supported by HTML document type only
			return;
		}

		if ($this->hasOpenGraphProperties()) {
			return;
		}

		if ($curparams->index >= 1 && $curparams->index <= count($images)) {
			$image = $images[$curparams->index - 1];
		} else {
			$image = $images[0];
		}
		list($imageid, $file_url, $width, $height, $filesize, $title, $summary, $preview_url, $preview_width, $preview_height, $thumb_url, $thumb_width, $thumb_height, $watermark_url) = $image;
		$url = isset($watermark_url) ? $watermark_url : $file_url;

		// translate paths into absolute URLs
		$url = $this->makeURL($url, true);

		// add Open Graph meta tag
		$document->addCustomTag('<meta property="og:image" content="'.$url.'" />');
		if ($width && $height) {
			$document->addCustomTag('<meta property="og:image:width" content="'.$width.'" />');
			$document->addCustomTag('<meta property="og:image:height" content="'.$height.'" />');
		}
		if ($title) {
			$document->addCustomTag('<meta property="og:image:alt" content="'.htmlspecialchars(strip_tags($title)).'" />');
		}
	}

	public function addStyles($id = null) {
		$curparams = $this->paramstack->top();  // current gallery parameters

		$instance = SigPlusNovoEngineServices::instance();
		$instance->addStandardStyles();
		if (isset($id)) {
			// add custom style declaration based on back-end and inline settings
			$slotrules = array();
			$imagerules = array();
			if ($curparams->preview_margin !== false) {
				if ($curparams->rotator === 'slideplus' || $curparams->caption !== false) {
					$slotrules['margin'] = $curparams->preview_margin.' !important';
					$imagerules['margin'] = '0 !important';
				} else {
					$imagerules['margin'] = $curparams->preview_margin.' !important';
				}
			}
			if ($curparams->preview_border_width !== false && $curparams->preview_border_style !== false && $curparams->preview_border_color !== false) {
				$imagerules['border'] = $curparams->preview_border_width.' '.$curparams->preview_border_style.' '.$curparams->preview_border_color.' !important';
			} else {
				if ($curparams->preview_border_width !== false) {
					$imagerules['border-width'] = $curparams->preview_border_width.' !important';
				}
				if ($curparams->preview_border_style !== false) {
					$imagerules['border-style'] = $curparams->preview_border_style.' !important';
				}
				if ($curparams->preview_border_color !== false) {
					$imagerules['border-color'] = $curparams->preview_border_color.' !important';
				}
			}
			if ($curparams->preview_padding !== false) {
				$imagerules['padding'] = $curparams->preview_padding.' !important';
			}
			$selectors = array(
				'#'.$id.' a.sigplus-image > img' => $imagerules
			);
			if ($curparams->rotator === 'slideplus') {
				$selectors['#'.$id.' .slideplus-slot'] = $slotrules;
			} elseif ($curparams->caption !== false) {
				$selectors['#'.$id.' .captionplus'] = $slotrules;
			}
			$instance->addStyles($selectors);
		}
	}

	public function addScripts($id = null) {
		if (isset($id)) {
			$curparams = $this->paramstack->top();  // current gallery parameters

			$instance = SigPlusNovoEngineServices::instance();
			$instance->addScript('/media/sigplus/js/initialization.js');  // unwrap all galleries from protective <noscript> container

			$jsid = json_encode($id);
			$instance->addOnReadyScript("__sigplusInitialize({$jsid});");
			if (SIGPLUS_CAPTION_CLIENT) {  // client-side template replacement
				$js_title_template = json_encode($curparams->caption_title_template);
				$js_summary_template = json_encode($curparams->caption_summary_template);
				$instance->addOnReadyScript("__sigplusCaption({$jsid}, {$js_title_template}, {$js_summary_template});");
			}

			if ($curparams->layout == 'flow' && $curparams->limit > 0) {
				$jsparams['limit'] = $curparams->limit;
				$jsparams['show_more'] = JText::_('SIGPLUS_SHOW_MORE');
				$jsparams['no_more'] = JText::_('SIGPLUS_NO_MORE');
				$jsparams = json_encode($jsparams, JSON_FORCE_OBJECT);
				$instance->addScript('/media/sigplus/js/progressive.js');
				$instance->addOnReadyScript("new ProgressiveGallery(document.getElementById({$jsid}),{$jsparams});");
			}

			$lightbox = $curparams->lightbox !== false ? $instance->getLightboxEngine($curparams->lightbox) : null;
			$caption = $curparams->caption !== false ? $instance->getCaptionEngine($curparams->caption) : null;
			$rotator = $curparams->rotator !== false ? $instance->getRotatorEngine($curparams->rotator) : null;
			$selectorid = self::css_escape_special_chars($id);
			if ($lightbox) {
				$selector = '#'.$selectorid.' a.sigplus-image';
				$lightbox->addStyles($selector, $curparams);
				$lightbox->addScripts($selector, $curparams);
			}
			if ($caption && (!$rotator || !$rotator->isCaptionSupported())) {
				$selector = '#'.$selectorid.' ul';
				$caption->addStyles($selector, $curparams);
				$caption->addScripts($selector, $curparams);
			}
			if ($rotator) {
				$selector = '#'.$selectorid;
				$rotator->addStyles($selector, $curparams);
				$rotator->addScripts($selector, $curparams);
			}
			$instance->addOnReadyEvent();
		}
	}

	/**
	* Subscribes to the "click" event of an anchor to pop up the associated lightbox window.
	* @param {string} $linkid The HTML identifier of the anchor whose "click" event to subscribe to.
	* @param {string} $galleryid The identifier of the gallery to open in the lightbox window.
	*/
	public function addLightboxLinkScript($linkid, $galleryid) {
		$curparams = $this->paramstack->top();  // current gallery parameters
		$instance = SigPlusNovoEngineServices::instance();
		$instance->activateLightbox($linkid, '#'.$galleryid.' a.sigplus-image', $curparams->index);  // selector should be same as above
		$instance->addOnReadyEvent();
	}

	/**
	* Adds lightbox styleheet and script references to the page header.
	* This method is typically invoked to bind a lightbox to an external URL not part of a gallery.
	*/
	public function addLightboxScripts($selector) {
		$curparams = $this->paramstack->top();  // current gallery parameters

		if ($curparams->lightbox !== false) {
			$instance = SigPlusNovoEngineServices::instance();

			$lightbox = $instance->getLightboxEngine($curparams->lightbox);
			$lightbox->addStyles($selector, $curparams);
			$lightbox->addScripts($selector, $curparams);

			$instance->addOnReadyEvent();
		}
	}

	public function getParameters() {
		return $this->paramstack->top();
	}

	public function setParameterObject($object) {
		$this->paramstack->setObject($object);
	}

	/**
	* Pushes a new set of gallery parameters on the parameter stack.
	* If used as a plug-in, these would normally appear as the attribute list of the activation start tag.
	*/
	public function setParameterString($string) {
		$this->paramstack->setString($string);
	}

	/**
	* Pushes an array of gallery parameter key-value pairs on the parameter stack.
	*/
	public function setParameterArray($array) {
		$this->paramstack->setArray($array);
	}

	/**
	* Pops a set of gallery parameters from the parameter stack.
	*/
	public function resetParameters() {
		$this->paramstack->pop();
	}
}
