<?php
/*
 * @version		$Id: search.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelSearch extends AllVideoShareModel {

	public function getItems( $limit ) {
	
		$app = JFactory::getApplication();	
		$db = JFactory::getDBO();
			  
		$limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', $limit, 'int' );
		$limitstart = $app->input->get( 'limitstart', '0', 'INT' );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );
 
		$this->setState( 'limit', $limit );
		$this->setState( 'limitstart', $limitstart );
		
		$q = $app->getUserStateFromRequest( 'com_allvideoshare.search', 'q', '', 'string' );	
		$q = JString::strtolower( $q );		 	
		$searchWord = $db->Quote( '%' . $db->escape( $q, true ) . '%', false );
		
        $query  = "SELECT v.* FROM #__allvideoshare_videos AS v";
		$query .= " LEFT JOIN #__allvideoshare_categories AS c ON v.catid=c.id";
		$query .= " WHERE v.published=1 AND (v.title LIKE $searchWord OR v.tags LIKE $searchWord OR c.name LIKE $searchWord)";
		$query .= " ORDER BY v.ordering";
		
        $db->setQuery( $query, $limitstart, $limit  );
        $items = $db->loadObjectList();	
		 
		return $items;
		 
    }
	
	public function getTotal() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$q = $app->getUserStateFromRequest( 'com_allvideoshare.search', 'q', '', 'string' );	
		$q = JString::strtolower( $q );		 	
		$searchWord = $db->Quote( '%' . $db->escape( $q, true ) . '%', false );
		
        $query  = "SELECT COUNT(v.id) FROM #__allvideoshare_videos AS v";
		$query .= " LEFT JOIN #__allvideoshare_categories AS c ON v.catid=c.id";
		$query .= " WHERE v.published=1 AND (v.title LIKE $searchWord OR v.tags LIKE $searchWord OR c.name LIKE $searchWord)";
		
        $db->setQuery( $query );
        $total = $db->loadResult();	
		 
		return $total;
		 
    }
	
	public function getPagination() {
	
    	 jimport( 'joomla.html.pagination' );
		 $pagination = new JPagination( $this->getTotal(), $this->getState( 'limitstart' ), $this->getState( 'limit' ) );
		 
         return $pagination;
		 
	}
			
}