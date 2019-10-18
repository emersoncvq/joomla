<?php
/*
 * @version		$Id: categories.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelCategories extends AllVideoShareModel {
	
	var $data = array();
	
	public function getItem() {
		
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = $cid[0];
		
        $row = JTable::getInstance( 'Categories', 'AllVideoShareTable' );
        $row->load( $id );

        return $row;
		
	}
	
	public function getItems() {
	
		$app = JFactory::getApplication();	
		
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		 
		$limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg( 'list_limit' ), 'int' );
		$limitstart = $app->getUserStateFromRequest( $option . $view . '.limitstart', 'limitstart', 0, 'int' );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );
		
		$this->buildData( $this->getParentId(), $spcr = '' );		
		if ( ! $limit ) {
			return $this->data;
		} else {
			return array_slice( $this->data, $limitstart, $limit );
		}
		
	}
	
	private function getParentId() {
	
		$app = JFactory::getApplication();
		
		$option = $app->input->get('option');
		$view = $app->input->get('view');
		
		$filter_parent = $app->getUserStateFromRequest( $option . $view . 'filter_parent', 'filter_parent', 0, 'int' );
		
		if ( ! empty( $filter_parent ) ) {
			$db = JFactory::getDBO();
			
			$query = "SELECT parent FROM #__allvideoshare_categories WHERE id=" . $filter_parent;
			$db->setQuery( $query );
			$parent = $db->loadResult();
			
			return $parent;
		}
		
		return 0;	
				
	}
	
	private function buildData( $parent, $spcr = '' ) {
			 
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		 
		$filter_state = $app->getUserStateFromRequest( $option . $view . 'filter_state', 'filter_state', -1, 'int' );
		$filter_parent = $app->getUserStateFromRequest( $option . $view . 'filter_parent', 'filter_parent', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );
		$setparent = 1;
		 
		$query = "SELECT * FROM #__allvideoshare_categories";	
				
		$where = array();		 
				 
		if ( $filter_state > - 1 ) {
			$where[] = "published={$filter_state}";
			$setparent = 0;			
		}
		
		if ( ! empty( $filter_parent ) ) {
			$tree = $this->getCategoryTree( $filter_parent );
	    	$where[] = "id IN (" . implode( ',', $tree ) . ')';
		 }
		 
		if ( $search ) {
			$escaped = $db->escape( $search, true );
			$where[] = "LOWER(name) LIKE " . $db->Quote( '%' . $escaped . '%', false );
			$setparent = 0;	
		}
		
		if ( $setparent ) {
			$where[] = "parent=$parent";
		}	

		$where = ( count( $where ) ? " WHERE ". implode( ' AND ', $where ) : '' );		 
		$query .= $where;		 
		$query .= " ORDER BY ordering ASC"; 
		 	    
        $db->setQuery( $query );
   		$cats= $db->loadObjectList();
		
		$c = 0;
		$count = count($cats);

        if ( $count ) {		
            foreach ( $cats as $cat ) {
				$cat->up = ( $c == 0 ) ? 0 : 1;
                $cat->down = ( $c + 1 == $count ) ? 0 : 1;
				$cat->spcr = $spcr . "<sup>L</sup>&nbsp;&nbsp;";
		
				$this->data[] = $cat;
				$c++;
				if ( $setparent ) {
                	$this->buildData( $cat->id, $spcr . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" );
				}
            }
        }
		
	}	

	public function getTotal() {
	
		$app = JFactory::getApplication();	
		$db = JFactory::getDBO();
		  
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		 
		$filter_state = $app->getUserStateFromRequest( $option . $view . 'filter_state', 'filter_state', -1, 'int' );
		$filter_parent = $app->getUserStateFromRequest( $option . $view . 'filter_parent', 'filter_parent', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );
		 
        $query = "SELECT COUNT(id) FROM #__allvideoshare_categories";
		$where = array();
		 
		if ( $filter_state > -1 ) {
			$where[] = "published={$filter_state}";
		}

		if ( ! empty( $filter_parent ) ) {
			$tree = $this->getCategoryTree( $filter_parent );
	    	$where[] = "id IN (" . implode( ',', $tree ) . ")";
		}
		 
		if ( $search ) {
		 	$escaped = $db->escape( $search, true );
			$where[] = "LOWER(name) LIKE " . $db->Quote( '%' . $escaped . '%', false );
		}

		$where = ( count( $where ) ? " WHERE ". implode( ' AND ', $where ) : '' );		 
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
		$filter_parent = $app->getUserStateFromRequest( $option . $view . 'filter_parent', 'filter_parent', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower ( $search );
     
    	$lists = array ();
		$lists['search'] = $search;
            
		$filter_state_options[] = JHTML::_( 'select.option', -1, '-- '.JText::_( 'SELECT_PUBLISHING_STATE' ).' --' );
		$filter_state_options[] = JHTML::_( 'select.option', 1, JText::_( 'PUBLISHED' ) );
		$filter_state_options[] = JHTML::_( 'select.option', 0, JText::_( 'UNPUBLISHED' ) );
		$lists['state'] = JHTML::_( 'select.genericlist', $filter_state_options, 'filter_state', 'onchange="this.form.submit();"', 'value', 'text', $filter_state );
		 
		$lists['categories'] = AllVideoShareHtml::ListCategories( 'filter_parent', $filter_parent, 'onchange="this.form.submit();"' );	
		 
        return $lists;
		 
	}

    public function save() {
	
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = $cid[0];
		
	  	$row = JTable::getInstance( 'Categories', 'AllVideoShareTable' );
      	$row->load( $id );
	
		$post = $app->input->post->getArray();
      	if ( ! $row->bind( $post ) ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	}

		$row->name = JString::trim( $row->name );
		$row->slug = AllVideoShareUtils::getCategorySlug( $row );
	  
	  	if ( 'upload' == $post['type_thumb'] ) {
			jimport( 'joomla.filesystem.folder' );
			
			$folder = 'categories/' . JHTML::_( 'date', 'now', 'Y-m', false );
			if ( ! JFolder::exists( ALLVIDEOSHARE_UPLOAD_BASE . $folder . '/' ) ) {
				JFolder::create( ALLVIDEOSHARE_UPLOAD_BASE . $folder . '/' );
			}
		
	  		$row->thumb = AllVideoShareUpload::doUpload( 'upload_thumb', $folder, $row->thumb );
	  	}
		$row->thumb = AllVideoShareUtils::cleanURL( $row->thumb );
		 
		$row->reorder( "parent=" . $row->parent );
		
	  	if ( ! $row->store() ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	}

		$task = $app->input->get('task');
	  	switch ( $task ) {
        	case 'apply':
            	$msg  = JText::_( 'CHANGES_SAVED' );
             	$link = 'index.php?option=com_allvideoshare&view=categories&task=edit&'. JSession::getFormToken() .'=1&cid[]=' . $row->id;
             	break;
        	case 'save':
        	default:
              	$msg  = JText::_( 'SAVED' );
              	$link = 'index.php?option=com_allvideoshare&view=categories';
              	break;
      	}

	  	$app->redirect( $link, $msg, 'message' );
		
	}

    public function cancel() {
	
		$app = JFactory::getApplication();
		 
		$link = 'index.php?option=com_allvideoshare&view=categories';
	    $app->redirect( $link );
		
    }
	
	public function delete() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$cid = $app->input->get( 'cid', array(), 'ARRAY' );
        $cids = implode( ',', $this->getCategoryTree( $cid ) );
		
        if ( count( $cid ) ) {
			// delete media files
			$query = "SELECT thumb FROM #__allvideoshare_categories WHERE id IN ( $cids )";
            $db->setQuery( $query );
			$items = $db->loadObjectList();
			
			foreach ( $items as $item ) {
				AllVideoShareUtils::deleteFile( $item->thumb );
			}
			
			// delete from the database			
            $query = "DELETE FROM #__allvideoshare_categories WHERE id IN ( $cids )";
            $db->setQuery( $query );
            if ( ! $db->query() ) {
                echo "<script>alert('" . $db->getErrorMsg() . "'); window.history.go(-1);</script>\n";
            }
        }
		
        $app->redirect( 'index.php?option=com_allvideoshare&view=categories' );	
		
	}

    public function publish() {
	
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(), 'ARRAY' );
        $publish = ( 'publish' == $app->input->get('task') ) ? 1 : 0;
			
        $row = JTable::getInstance( 'Categories', 'AllVideoShareTable' );
        $row->publish( $cid, $publish );
		
        $app->redirect( 'index.php?option=com_allvideoshare&view=categories' );	  
		      
    }

	public function move( $direction ) {
	
		$app  = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = (int) $cid[0];
		 
    	$row = JTable::getInstance( 'Categories', 'AllVideoShareTable' );
		$row->load( $id );
		$row->move( $direction, 'parent=' . $row->parent );
		
		$app->redirect( 'index.php?option=com_allvideoshare&view=categories', JText::_( 'NEW_ORDERING_SAVED' ), 'message' );
		
    }
	
    public function saveOrder() {
	
		$app = JFactory::getApplication();
		$db	= JFactory::getDBO();
		
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$total = count( $cid );
		
		$order = $app->input->get( 'order', array(0), 'ARRAY' );
		JArrayHelper::toInteger( $order, array(0) );
		 
		$row = JTable::getInstance( 'Categories', 'AllVideoShareTable' );
		$groupings = array();
		for ( $i = 0; $i < $total; $i++ ) {
			$row->load( (int) $cid[ $i ] );
			$groupings[] = $row->parent;
 			if ( $row->ordering != $order[ $i ] ) {
				$row->ordering  = $order[ $i ];
				if ( ! $row->store() ) {
					$app->enqueueMessage( $db->getErrorMsg(), 'error' );
				}
			}
		} 
		$groupings = array_unique( $groupings );
	
		foreach ( $groupings as $group ) {
			$row->reorder( 'parent="'.$group.'"' );
		}
 
		$app->redirect( 'index.php?option=com_allvideoshare&view=categories', JText::_( 'NEW_ORDERING_SAVED' ), 'message' );    
		            
    }
	
	private function getCategoryTree( $ids ) {
	
		$db = JFactory::getDBO();	
				
		$ids = (array) $ids;
		JArrayHelper::toInteger( $ids );
		$catid  = array_unique( $ids );
		sort( $ids );
		
		$array = $ids;				
		while ( count( $array ) ) {
			$query = "SELECT id FROM #__allvideoshare_categories WHERE parent IN (" . implode( ',', $array ) . ") AND id NOT IN (" . implode( ',', $array ) . ")";
			$db->setQuery( $query );
			$array = $db->loadColumn();
			$ids = array_merge( $ids, $array );
		}
		JArrayHelper::toInteger( $ids );
		$ids = array_unique( $ids );			
			
		return $ids;
		
	}

}