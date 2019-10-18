<?php
/*
 * @version		$Id: allvideoshare.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

class PlgSearchAllVideoShare extends JPlugin {

	public function onContentSearchAreas() {
	
		static $areas = array(
			'videos' => 'Videos'
		);

		return $areas;
		
	}

	public function onContentSearch( $text, $phrase = '', $ordering = '', $areas = null ) {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();		

		JLoader::register( 'SearchHelper', JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php' );
		JLoader::register( 'AllVideoShareUtils', JPATH_ADMINISTRATOR . '/components/com_allvideoshare/libraries/utils.php' );

		$searchText = $text;

		if ( is_array( $areas ) ) {
			if ( ! array_intersect( $areas, array_keys( $this->onContentSearchAreas() ) ) ) {
				return array();
			}
		}

		$limit     = $this->params->def( 'search_limit', 50 );

		$nullDate  = $db->getNullDate();
		$date      = JFactory::getDate();
		$now       = $date->toSql();

		$text = trim( $text );

		if ( '' == $text ) {
			return array();
		}

		switch ( $phrase ) {
			case 'exact':
				$text      = $db->Quote( '%' . $db->escape( $text, true ) . '%', false );
				$wheres2   = array();
				$wheres2[] = 'v.title LIKE ' . $text;
				$wheres2[] = 'v.description LIKE ' . $text;
				$wheres2[] = 'v.tags LIKE ' . $text;
				$wheres2[] = 'v.metadescription LIKE ' . $text;
				$where     = '(' . implode( ') OR (', $wheres2 ) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode( ' ', $text );
				$wheres = array();

				foreach ( $words as $word ) {
					$word      = $db->Quote( '%' . $db->escape( $word, true ) . '%', false );
					$wheres2   = array();
					$wheres2[] = 'LOWER(v.title) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(v.description) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(v.tags) LIKE LOWER(' . $word . ')';
					$wheres2[] = 'LOWER(v.metadescription) LIKE LOWER(' . $word . ')';
					$wheres[]  = implode( ' OR ', $wheres2 );
				}

				$where = '(' . implode( ( $phrase == 'all' ? ') AND (' : ') OR (' ), $wheres ) . ')';
				break;
		}

		switch ( $ordering )	{
			case 'oldest':
				$order = 'v.created_date ASC';
				break;

			case 'popular':
				$order = 'v.views DESC';
				break;

			case 'alpha':
				$order = 'v.title ASC';
				break;

			case 'category':
				$order = 'v.catid ASC';
				break;

			case 'newest':
			default:
				$order = 'v.created_date DESC';
				break;
		}

		$rows = array();
		$query = $db->getQuery( true );

		// Search Videos
		if ( $limit > 0 ) {
			$query->clear();

			// SQLSRV changes.
			$query->select( 'v.id, v.title AS title, v.slug AS slug, v.description AS text, v.metadescription as metadesc, v.tags as metakey, v.created_date AS created' )
				->select( 'c.id as catid, c.name AS section, c.slug as catslug, ' . '\'2\' AS browsernav' )
				->from( '#__allvideoshare_videos AS v' )
				->join( 'INNER', '#__allvideoshare_categories AS c ON c.id=v.catid' )
				->where(
					'(' . $where . ') AND v.published=1 AND c.published=1 '
				)
				->group( 'v.id' )
				->order($order);

			$db->setQuery( $query, 0, $limit );
				
			try	{
				$list = $db->loadObjectList();
			} catch( RuntimeException $e ) {
				$list = array();
				$app->enqueueMessage( JText::_( 'JERROR_AN_ERROR_HAS_OCCURRED' ), 'error' );
			}
			
			$limit -= count( $list );

			if ( isset( $list ) ) {
				$itemId = AllVideoShareUtils::getVideoMenuId();		

				foreach ( $list as $key => $item ) {
					if ( ! empty( $itemId ) ) {
						$list[ $key ]->href = JRoute::_( "index.php?option=com_allvideoshare&view=video&slg=" . $item->slug . '&Itemid=' . $itemId );
					} else {
						$list[ $key ]->href = AllVideoShareUtils::buildRoute( $item->slug, 'video' );
					}					
				}
			}

			$rows[] = $list;
		}

		$results = array();

		if ( count( $rows ) ) {
			foreach ( $rows as $row ) {
				$new_row = array();

				foreach ( $row as $video ) {
					if ( SearchHelper::checkNoHtml( $video, $searchText, array( 'text', 'title', 'metakey', 'metadesc' ) ) ) {
						$new_row[] = $video;
					}
				}

				$results = array_merge( $results, (array) $new_row );
			}
		}

		return $results;
		
	}
	
}