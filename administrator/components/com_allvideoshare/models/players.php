<?php
/*
 * @version		$Id: players.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelPlayers extends AllVideoShareModel {
	
	public function getItem() {
	
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(1), 'ARRAY' );
		$id = $cid[0];
		 
        $row = JTable::getInstance( 'Players', 'AllVideoShareTable' );
        $row->load( $id );

        return $row;
		 
	}
	
	public function getItems() {
	
		$app = JFactory::getApplication();	
		$db = JFactory::getDBO();
		 
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		 
		$limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg( 'list_limit' ), 'int' );
		$limitstart = $app->getUserStateFromRequest( $option . $view . '.limitstart', 'limitstart', 0, 'int' );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );
		$filter_state = $app->getUserStateFromRequest( $option . $view . 'filter_state', 'filter_state', -1, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );

        $query = "SELECT * FROM #__allvideoshare_players";
		$where = array();
		 
		if ( $filter_state > - 1 ) {
			$where[] = "published={$filter_state}";
		}
		
		if ( $search ) {
		 	$escaped = $db->escape( $search, true );
			$where[] = "LOWER(name) LIKE " . $db->Quote( '%' . $escaped . '%', false );
		}

		$where = ( count( $where ) ? " WHERE " . implode( ' AND ', $where ) : '' );		 
		$query .= $where;
		 
        $db->setQuery( $query, $limitstart, $limit );
        $items = $db->loadObjectList();
		 
        return $items;
		 
	}
	
	public function getTotal() {
	
		$app = JFactory::getApplication();	
		$db = JFactory::getDBO();
		 
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		 
		$filter_state = $app->getUserStateFromRequest( $option . $view . 'filter_state', 'filter_state', -1, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );

        $query = "SELECT COUNT(*) FROM #__allvideoshare_players";
		$where = array();
		 
		if ( $filter_state > - 1 ) {
			$where[] = "published={$filter_state}";
		}

		if ( $search ) {
		 	$escaped = $db->escape( $search, true );
			$where[] = "LOWER(name) LIKE " . $db->Quote( '%' . $escaped . '%', false );
		}

		$where = ( count( $where ) ? " WHERE " . implode( ' AND ', $where ) : '' );		 
		$query .= $where;

        $db->setQuery( $query );
        $count = $db->loadResult();
		 
        return $count;
		 
	}
	
	public function getPagination() {

		$app = JFactory::getApplication();	
		 
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		 
		$total = $this->getTotal();
		$limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg( 'list_limit' ), 'int' );
		$limitstart = $app->getUserStateFromRequest( $option . $view . '.limitstart', 'limitstart', 0, 'int' );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );
     
    	jimport( 'joomla.html.pagination' );
		$pagination = new JPagination( $total, $limitstart, $limit );
		 
        return $pagination;
		 
	}
	
	public function getLists() {
	
		$app = JFactory::getApplication();	
		 
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		 
		$filter_state = $app->getUserStateFromRequest( $option . $view . 'filter_state', 'filter_state', -1, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower ( $search );
     
    	$lists = array ();
		$lists ['search'] = $search;
            
		$filter_state_options[] = JHTML::_( 'select.option', -1, '-- '.JText::_( 'SELECT_PUBLISHING_STATE' ).' --' );
		$filter_state_options[] = JHTML::_( 'select.option', 1, JText::_( 'PUBLISHED' ) );
		$filter_state_options[] = JHTML::_( 'select.option', 0, JText::_( 'UNPUBLISHED' ) );
		$lists['state'] = JHTML::_( 'select.genericlist', $filter_state_options, 'filter_state', 'onchange="this.form.submit();"', 'value', 'text', $filter_state );
		 
        return $lists;
		 
	}
	
	public function save() {
	
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = $cid[0];
		
	  	$row = JTable::getInstance( 'Players', 'AllVideoShareTable' );
      	$row->load( $id );	    
		
		$post = $app->input->post->getArray();
      	if ( ! $row->bind( $post ) ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	}
		
		if ( $row->id == 1 ) {
			$row->name = 'Default';
			$row->published = 1;
		}
	  
	  	if ( ! $row->store() ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	}

		$task = $app->input->get('task');
	  	switch ( $task ) {
        	case 'apply':
            	$msg  = JText::_( 'CHANGES_SAVED' );
             	$link = 'index.php?option=com_allvideoshare&view=players&task=edit&' . JSession::getFormToken() . '=1&cid[]=' . $row->id;
             	break;
        	case 'save':
        	default:
              	$msg  = JText::_( 'SAVED' );
              	$link = 'index.php?option=com_allvideoshare&view=players';
              	break;
      	}
	  
	  	$app->redirect( $link, $msg, 'message' );
		
	}
	
	public function cancel() {
	
		 $app = JFactory::getApplication();
		 
		 $link = 'index.php?option=com_allvideoshare&view=players';
	     $app->redirect( $link );
		 
	}	

	public function delete() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$cid = $app->input->get( 'cid', array(), 'ARRAY' );
        $cids = implode( ',', $cid );
		
        if ( count( $cid ) ) {
            $query = "DELETE FROM #__allvideoshare_players WHERE id IN ( $cids )";
            $db->setQuery( $query );
            if ( ! $db->query() ) {
                echo "<script> alert('" . $db->getErrorMsg() . "');window.history.go(-1); </script>\n";
            }
        }
		
        $app->redirect( 'index.php?option=com_allvideoshare&view=players' );
		
	}
	
	public function publish() {
	
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(), 'ARRAY' );
        $publish = ( 'publish' == $app->input->get('task') ) ? 1 : 0;
			
        $row = JTable::getInstance( 'Players', 'AllVideoShareTable' );
        $row->publish( $cid, $publish );
		
        $app->redirect( 'index.php?option=com_allvideoshare&view=players' );
		
    }

}