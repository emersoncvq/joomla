<?php
/*
 * @version		$Id: video.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelVideo extends AllVideoShareModel {
	
	public function getItem() {
	
		$app = JFactory::getApplication();
        $db = JFactory::getDBO();
		 
        $query = "SELECT v.*, c.name as category FROM #__allvideoshare_videos AS v";
		$query.= " LEFT JOIN #__allvideoshare_categories AS c ON v.catid=c.id";
		 
		if ( $app->input->getInt( 'id' ) ) {
		 	$query .= " WHERE v.id=" . $app->input->getInt( 'id' );
		} else {		 
		 	$slug = AllVideoShareUtils::getSlug();
		 	$query .= " WHERE v.slug=" . $db->Quote( $slug );
		}
		
        $db->setQuery( $query );
        $item = $db->loadObject();
		 
        return $item;
		 
	}
	
	public function getVideos( $catid, $params, $limit ) {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		 
		$limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', $limit, 'int' );
		$limitstart = $app->input->get( 'limitstart', '0', 'INT' );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );
 
		$this->setState( 'limit', $limit );
		$this->setState( 'limitstart', $limitstart );
		 
		$slug = AllVideoShareUtils::getSlug();
        $query = "SELECT * FROM #__allvideoshare_videos WHERE published=1 AND catid=" . $catid . " AND slug!=" . $db->Quote( $slug );
		 
		if ( 'featured' == $params->get( 'orderby' ) || 1 == $params->get( 'featured', 0 ) ) {
			$query .= " AND featured=1";
		}
		
		switch ( $params->get( 'orderby' ) ) {
		 	case 'latest' :
		 		$query .= " ORDER BY id DESC";
				break;
			case 'latest_by_date' :
		 		$query .= " ORDER BY created_date DESC";
				break;
			case 'popular' :
				$query .= " ORDER BY views DESC";
				break;
			case 'random' :
				$query .= " ORDER BY RAND()";
				break;
			default :
				$query .= " ORDER BY ordering";
		}
		 
        $db->setQuery( $query, $limitstart, $limit );
        $items = $db->loadObjectList();
		 
        return $items;
		
	}
	
	public function getTotal( $catid, $params ) {
	
		$db = JFactory::getDBO();
		 
		$slug = AllVideoShareUtils::getSlug();
        $query = "SELECT COUNT(id) FROM #__allvideoshare_videos WHERE published=1 AND catid=" . $catid . " AND slug!=" . $db->Quote( $slug );
		
		if ( 'featured' == $params->get( 'orderby' ) || 1 == $params->get( 'featured', 0 ) ) {
			$query .= " AND featured=1";
		}
		
        $db->setQuery( $query );
        $total = $db->loadResult();
		 
        return $total;
		
	}
	
	public function getPagination( $catid, $params ) {
	
    	 jimport( 'joomla.html.pagination' );
		 $pagination = new JPagination( $this->getTotal( $catid, $params ), $this->getState( 'limitstart' ), $this->getState( 'limit' ) );
		 
         return $pagination;
		 
	}
		
}