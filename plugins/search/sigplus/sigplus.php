<?php
/**
* @file
* @brief    sigplus Image Gallery Plus image search plug-in
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2017 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
* @see      http://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('SIGPLUS_PLUGIN_FOLDER')) {
	define('SIGPLUS_PLUGIN_FOLDER', 'sigplus');
}
if (!defined('SIGPLUS_MEDIA_FOLDER')) {
	define('SIGPLUS_MEDIA_FOLDER', 'sigplus');
}

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

/**
* Triggered when the sigplus content plug-in is unavailable or there is a version mismatch.
*/
class SigPlusNovoSearchDependencyException extends Exception {
	/**
	* Creates a new exception instance.
	* @param {string} $key Error message language key.
	*/
	public function __construct() {
		$key = 'SIGPLUS_EXCEPTION_EXTENSION';
		$message = '['.$key.'] '.JText::_($key);  // get localized message text
		parent::__construct($message);
	}
}

/**
* sigplus image search plug-in.
*/
class plgSearchSigPlusNovo extends JPlugin {
	private $limit = 50;
	private $core;

	public function __construct( &$subject, $config ) {
		parent::__construct( $subject, $config );
		$this->limit = (int) $this->params->get('search_limit');
		if ($this->limit < 1) {
			$this->limit = 50;
		}
	}

	/**
	* Metadata search method.
	* The SQL must return the following fields that are used in a common display
	* routine: href, title, section, created, text, browsernav
	* @param {string} $text Target search string
	* @param {string} $phrase Matching option [exact|any|all]
	* @param {string} $ordering Ordering option [newest|oldest|popular|alpha|category]
	* @param {mixed} $areas An array if the search it to be restricted to areas, null if search all
	*/
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null) {
		// skip for empty search phrase
		if (strlen($text) == 0 || ctype_space($text)) {
			return array();
		}

		// skip if not searching inside image metadata
		if (is_array($areas) && !array_intersect($areas, array_keys(self::onContentSearchAreas()))) {
			return array();
		}

		// load language file for internationalized labels and error messages
		$lang = JFactory::getLanguage();
		$lang->load('plg_search_'.SIGPLUS_PLUGIN_FOLDER, JPATH_ADMINISTRATOR);

		if (!isset($this->core)) {
			// load sigplus content plug-in
			if (!JPluginHelper::importPlugin('content', SIGPLUS_PLUGIN_FOLDER)) {
				throw new SigPlusNovoSearchDependencyException();
			}

			// load sigplus content plug-in parameters
			$plugin = JPluginHelper::getPlugin('content', SIGPLUS_PLUGIN_FOLDER);
			$params = json_decode($plugin->params);
			$params->lightbox_thumbs = false;

			// create configuration parameter objects
			$configuration = new SigPlusNovoConfigurationParameters();
			$configuration->service = new SigPlusNovoServiceParameters();
			$configuration->service->setParameters($params);
			$configuration->gallery = new SigPlusNovoGalleryParameters();
			$configuration->gallery->setParameters($params);

			if (SIGPLUS_LOGGING || $configuration->service->debug_server) {
				SigPlusNovoLogging::setService(new SigPlusNovoHTMLLogging());
			} else {
				SigPlusNovoLogging::setService(new SigPlusNovoNoLogging());
			}

			$this->core = new SigPlusNovoCore($configuration);
		}

		$db = JFactory::getDbo();

		// determine current site language
		$lang = JFactory::getLanguage();
		list($language, $country) = explode('-', $lang->getTag());  // site current language

		// get the database identifier that belongs to an ISO language code
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT'.PHP_EOL.
				$db->quoteName('langid').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_language').PHP_EOL.
			'WHERE'.PHP_EOL.
				$db->quoteName('lang').' = '.$db->quote($language)
		);
		$langid = $db->loadResult();

		// get the database identifier that belongs to an ISO country code
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT'.PHP_EOL.
				$db->quoteName('countryid').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_country').PHP_EOL.
			'WHERE'.PHP_EOL.
				$db->quoteName('country').' = '.$db->quote($country)
		);
		$countryid = $db->loadResult();

		// build SQL WHERE clause
		switch ($phrase) {
			case 'all':
			case 'any':
				$text = preg_replace('#\s+#', ' ', trim($text));  // collapse multiple spaces
				$words = explode(' ', $text);
				break;
			case 'exact':
			default:
				$words = array($text);
		}
		$wherewords = array();
		foreach ($words as $word) {
			// images whose metadata contain the given word
			$wherewords[] =
				'i.'.$db->quoteName('imageid').' IN ('.PHP_EOL.
					'SELECT wi.'.$db->quoteName('imageid').PHP_EOL.
					'FROM '.$db->quoteName('#__sigplus_image').' AS wi'.PHP_EOL.
						'LEFT JOIN '.$db->quoteName('#__sigplus_data').' AS wd'.PHP_EOL.
						'ON wi.'.$db->quoteName('imageid').' = wd.'.$db->quoteName('imageid').PHP_EOL.
						'LEFT JOIN '.$db->quoteName('#__sigplus_caption').' AS wc'.PHP_EOL.
						'ON'.PHP_EOL.
							// no caption belongs to image or caption language matches site language
							'wc.'.$db->quoteName('imageid').' = wi.'.$db->quoteName('imageid').' AND '.PHP_EOL.
							'wc.'.$db->quoteName('langid').' = '.$langid.' AND '.PHP_EOL.
							'wc.'.$db->quoteName('countryid').' = '.$countryid.PHP_EOL.
					'WHERE'.PHP_EOL.
						'(wi.'.$db->quoteName('filename').' LIKE '.$db->quote('%'.$db->escape($word).'%', false).' OR '.
						' wc.'.$db->quoteName('title').' LIKE '.$db->quote('%'.$db->escape($word).'%', false).' OR '.
						' wc.'.$db->quoteName('summary').' LIKE '.$db->quote('%'.$db->escape($word).'%', false).' OR '.
						' wd.'.$db->quoteName('textvalue').' LIKE '.$db->quote('%'.$db->escape($word).'%', false).')'.PHP_EOL.
				')';
		}
		switch ($phrase) {
			case 'any':  // images at least one of whose metadata fields contain one of the words
				$implodephrase = 'OR';
				break;
			case 'all':  // images whose metadata fields contain all of the words
			case 'exact':
			default:
				$implodephrase = 'AND';
		}
		$where = '('.implode(PHP_EOL.$implodephrase.PHP_EOL, $wherewords).')';

		// build SQL ORDER BY clause
		$orderby = '';
		switch ($ordering) {
			case 'oldest':
				$orderby = 'filetime ASC';
				break;
			case 'newest':
				$orderby = 'filetime DESC';
				break;
			case 'category':
				$orderby = 'folderurl';
				break;
			case 'alpha':
			case 'popular':  // ignored
			default:
				$orderby = 'filename';
				break;
		}

		// build database query
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
		$query =
			'SELECT'.PHP_EOL.
				$db->quoteName('fileurl').' AS url,'.PHP_EOL.
				$db->quoteName('filename').','.PHP_EOL.
				$db->quoteName('filetime').','.PHP_EOL.
				$db->quoteName('width').','.PHP_EOL.
				$db->quoteName('height').','.PHP_EOL.
				'COALESCE('.PHP_EOL.
					'c.'.$db->quoteName('title').','.PHP_EOL.
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
				') AS '.$db->quoteName('title').','.PHP_EOL.
				'COALESCE('.PHP_EOL.
					'c.'.$db->quoteName('summary').','.PHP_EOL.
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
				') AS '.$db->quoteName('summary').','.PHP_EOL.
				$db->quoteName('preview_fileurl').','.PHP_EOL.
				$db->quoteName('preview_width').','.PHP_EOL.
				$db->quoteName('preview_height').PHP_EOL.
			'FROM '.$db->quoteName('#__sigplus_image').' AS i'.PHP_EOL.
				// join with table `folder` to permit ordering by category (join not used otherwise)
				'INNER JOIN '.$db->quoteName('#__sigplus_folder').' AS f'.PHP_EOL.
				'ON i.'.$db->quoteName('folderid').' = f.'.$db->quoteName('folderid').PHP_EOL.
				// each image turns up in at least one image view
				'INNER JOIN '.$db->quoteName('#__sigplus_imageview').' AS v'.PHP_EOL.
				'ON i.'.$db->quoteName('imageid').' = v.'.$db->quoteName('imageid').PHP_EOL.
				// if the image has multiple preview images in different views, keep the one with the maximum preview image width
				'INNER JOIN ('.PHP_EOL.
					'SELECT imageid, MAX(preview_width) AS max_preview_width'.PHP_EOL.
					'FROM #__sigplus_imageview'.PHP_EOL.
					'GROUP BY imageid'.PHP_EOL.
				') AS m'.PHP_EOL.
				'ON v.'.$db->quoteName('imageid').' = m.'.$db->quoteName('imageid').' AND v.'.$db->quoteName('preview_width').' = m.'.$db->quoteName('max_preview_width').PHP_EOL.
				// get caption assigned to image (if any)
				'LEFT JOIN '.$db->quoteName('#__sigplus_caption').' AS c'.PHP_EOL.
				'ON'.PHP_EOL.
					// no caption belongs to image or caption language matches site language
					'c.'.$db->quoteName('imageid').' = i.'.$db->quoteName('imageid').' AND '.PHP_EOL.
					'c.'.$db->quoteName('langid').' = '.$langid.' AND '.PHP_EOL.
					'c.'.$db->quoteName('countryid').' = '.$countryid.PHP_EOL.
			'WHERE'.PHP_EOL.
				$where.PHP_EOL.
			'ORDER BY '.$orderby;
		$db->setQuery($query, 0, $this->limit);

		$rows = $db->loadAssocList();

		$show_thumbnails = (bool) $this->params->get('search_thumbnail');
		$show_lightbox = (bool) $this->params->get('search_lightbox');

		// fetch database results
		$results = array();
		if ($rows) {
			$instance = SigPlusNovoEngineServices::instance();

			if ($show_thumbnails) {
				// import script services to add thumbnail images to image description text
				$instance->addScript('/media/'.SIGPLUS_MEDIA_FOLDER.'/js/search.js');
			}

			if ($show_lightbox) {
				// include lightbox script only if there are image results
				$this->core->addLightboxScripts('.search-results > .result-title > a');
			}

			foreach ($rows as $row) {
				if ($row['title']) {
					$title = $row['title'];
				} else {
					$title = $row['filename'];
				}

				$url = $this->core->makeURL($row['url']);
				$results[] = (object) array(
					'href'        => $url,
					// standard Joomla search code strips HTML tags from search result text
					'text'        => '('.$row['width'].'x'.$row['height'].') '.htmlspecialchars($row['summary']),
					'title'       => html_entity_decode(strip_tags($title), ENT_QUOTES),
					'section'     => JText::_('SIGPLUS_IMAGES'),
					'created'     => $row['filetime'],
					'browsernav'  => '1'
				);

				if ($show_thumbnails) {
					// add thumbnail image to image description text
					// <img src="preview_fileurl" width="preview_width" height="preview_height" />
					$target_url = json_encode($url);
					$preview_url = json_encode($this->core->makeURL($row['preview_fileurl']));
					$width = (int) $row['preview_width'];
					$height = (int) $row['preview_height'];
					$instance->addOnReadyScript("__sigplusSearch({$target_url}, {$preview_url}, {$width}, {$height});");
				}
			}

			$instance->addOnReadyEvent();
		}

		return $results;
	}

	/**
	* @return {array} An array of search areas.
	*/
	public function onContentSearchAreas() {
		// load language file for internationalized labels
		$lang = JFactory::getLanguage();
		$lang->load('plg_search_'.SIGPLUS_PLUGIN_FOLDER, JPATH_ADMINISTRATOR);

		$areas = array(
			'sigplus' => JText::_('SIGPLUS_IMAGES')
		);
		return $areas;
	}
}

class plgSearchSIGPlus extends plgSearchSigPlusNovo {

}
