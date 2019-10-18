<?php
/**
* @file
* @brief    sigplus Image Gallery Plus installation and update utilities
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

jimport('joomla.database.database');
jimport('joomla.database.table');

class SigPlusNovoDatabaseSetup {
	/**
	* Drops and re-creates all tables in the database.
	*/
	public static function update() {
		$db = JFactory::getDBO();
		self::executeQueryInFile(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'uninstall.'.$db->getServerType().'.utf8.sql');
		self::executeQueryInFile(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'install.'.$db->getServerType().'.utf8.sql');
	}

	private static function executeQueryInFile($file) {
		$db = JFactory::getDBO();
		$contents = file_get_contents($file);
		if ($contents !== false) {
			$queries = $db->splitSql($contents);
			foreach ($queries as $query) {
				$query = trim($query);
				if ($query) {  // skip empty queries
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	/**
	* Populates the database.
	*/
	public static function populate() {
		$db = JFactory::getDBO();

		// language codes
		$languages = array(
			1 => 'aa','ab','ae','af','ak','am','an','ar','as','av','ay','az','ba','be','bg','bh','bi','bm','bn','bo',
			'br','bs','ca','ce','ch','co','cr','cs','cu','cv','cy','da','de','dv','dz','ee','el','en','eo','es',
			'et','eu','fa','ff','fi','fj','fo','fr','fy','ga','gd','gl','gn','gu','gv','ha','he','hi','ho','hr',
			'ht','hu','hy','hz','ia','id','ie','ig','ii','ik','io','is','it','iu','ja','jv','ka','kg','ki','kj',
			'kk','kl','km','kn','ko','kr','ks','ku','kv','kw','ky','la','lb','lg','li','ln','lo','lt','lu','lv',
			'mg','mh','mi','mk','ml','mn','mr','ms','mt','my','na','nb','nd','ne','ng','nl','nn','no','nr','nv',
			'ny','oc','oj','om','or','os','pa','pi','pl','ps','pt','qu','rm','rn','ro','ru','rw','sa','sc','sd',
			'se','sg','si','sk','sl','sm','sn','so','sq','sr','ss','st','su','sv','sw','sy','ta','te','tg','th',
			'ti','tk','tl','tn','to','tr','ts','tt','tw','ty','ug','uk','ur','uz','ve','vi','vo','wa','wo','xh',
			'yi','yo','za','zh','zu'
		);
		self::populateTable($languages, '#__sigplus_language', 'langid', 'lang');

		// country codes
		$countries = array(
			1 => 'AA','AD','AE','AF','AG','AL','AM','AO','AQ','AR','AT','AU','AW','AZ','BA','BB','BD','BE','BF','BG',
			'BH','BI','BJ','BM','BN','BO','BR','BS','BT','BW','BY','BZ','CA','CD','CF','CG','CH','CI','CL','CM',
			'CN','CO','CR','CU','CV','CY','CZ','DE','DJ','DK','DM','DO','DZ','EC','EE','EG','EH','ER','ES','ET',
			'FI','FJ','FM','FO','FR','GA','GB','GD','GE','GF','GH','GM','GN','GP','GQ','GR','GT','GW','GY','HN',
			'HR','HT','HU','ID','IE','IL','IN','IO','IQ','IR','IS','IT','JE','JM','JO','JP','KE','KG','KH','KI',
			'KM','KN','KP','KR','KW','KZ','LA','LB','LC','LI','LK','LR','LS','LT','LU','LV','LY','MA','MC','MD',
			'ME','MG','MK','ML','MM','MN','MO','MQ','MR','MS','MU','MV','MW','MX','MY','MZ','NA','NE','NG','NI',
			'NL','NO','NP','NZ','OM','PA','PE','PG','PH','PK','PL','PS','PT','PW','PY','QA','RE','RO','RS','RU',
			'RW','SA','SB','SC','SD','SE','SH','SI','SK','SL','SM','SN','SO','SR','ST','SV','SY','SZ','TD','TF',
			'TG','TH','TJ','TL','TM','TN','TR','TT','TW','TZ','UA','UG','US','UY','UZ','VC','VE','VN','VU','XX',
			'YE','YU','ZA','ZM','ZW'
		);
		self::populateTable($countries, '#__sigplus_country', 'countryid', 'country');

		// discard existing metadata
		$db->setQuery('DELETE FROM '.$db->quoteName('#__sigplus_data'));
		$db->execute();
		$db->setQuery('DELETE FROM '.$db->quoteName('#__sigplus_property'));  // make sure there are no FOREIGN KEY references
		$db->execute();

		// populate metadata store with properties
		require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'metadata.php';
		$properties = SigPlusNovoMetadataServices::getProperties();
		self::populateTable($properties, '#__sigplus_property', 'propertyid', 'propertyname');
	}

	private static function populateTable($values, $table, $key_field, $value_field) {
		$db = JFactory::getDBO();
		foreach ($values as $index => &$item) {
			$item = '('.$index.','.$db->quote($item).')';  // e.g. ('Title')
		}
		$db->setQuery('INSERT INTO '.$db->quoteName($table).' ('.$db->quoteName($key_field).','.$db->quoteName($value_field).') VALUES '.implode(',', $values));
		$db->execute();
	}

	/**
	* Updates old plug-in and module configuration settings to their new equivalents.
	*/
	public static function migrateExtensionConfiguration($oldparams) {
		$newparams = $oldparams;

		$newparams['maxcount']             = $oldparams['thumb_count'];
		$newparams['preview_width']        = $oldparams['thumb_width'];
		$newparams['preview_height']       = $oldparams['thumb_height'];
		$newparams['preview_crop']         = $oldparams['thumb_crop'];
		$newparams['thumb_width']          = 60;  // cannot set value in older version
		$newparams['thumb_height']         = 60;  // cannot set value in older version

		switch ($oldparams['lightbox']) {
			case 'boxplus/darksquare':
			case 'boxplus/darkrounded':
				$newparams['lightbox'] = 'boxplus/dark';
				break;
			case 'boxplus/lightsquare':
			case 'boxplus/lightrounded':
				$newparams['lightbox'] = 'boxplus/light';
				break;
			default:
				$newparams['lightbox'] = $oldparams['lightbox'];
		}

		$newparams['rotator_orientation']  = $oldparams['slider_orientation'];
		$newparams['rotator_buttons']      = $oldparams['slider_buttons'];
		$newparams['rotator_navigation']   = $oldparams['slider_navigation'];
		$newparams['rotator_links']        = $oldparams['slider_links'];
		$newparams['rotator_duration']     = $oldparams['slider_duration'];
		$newparams['rotator_delay']        = $oldparams['slider_animation'];

		$newparams['preview_margin']       = $oldparams['margin'];
		$newparams['preview_border_style'] = $oldparams['border_style'];
		$newparams['preview_border_width'] = $oldparams['border_width'];
		$newparams['preview_border_color'] = $oldparams['border_color'];
		$newparams['preview_padding']      = $oldparams['padding'];

		if (!empty($oldparams['labels'])) {
			$newparams['caption_source'] = $oldparams['labels'] . '.txt';
		}
		$newparams['caption_summary']      = $oldparams['caption_description'];

		$newparams['folder_thumb']         = $oldparams['thumb_folder'];
		$newparams['folder_preview']       = $oldparams['preview_folder'];
		$newparams['folder_fullsize']      = $oldparams['fullsize_folder'];

		$newparams['quality']              = $oldparams['thumb_quality'];
		$newparams['library_image']        = $oldparams['library'];

		if (isset($oldparams['activationtag'])) {  // applicable to plug-in only
			$newparams['tag_gallery'] = $oldparams['activationtag'];
		}

		if (isset($oldparams['images_folder'])) {  // applicable to module only
			$newparams['source'] = $oldparams['images_folder'];
		}

		return $newparams;
	}
}
