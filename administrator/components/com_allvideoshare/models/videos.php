<?php
/*
 * @version		$Id: videos.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelVideos extends AllVideoShareModel {

	public function getItem() {
	
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = $cid[0];
		 
        $row = JTable::getInstance( 'Videos', 'AllVideoShareTable' );
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
		$filter_category = $app->getUserStateFromRequest( $option . $view . 'filter_category', 'filter_category', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );
	
        $query  = "SELECT v.*, c.name as category FROM #__allvideoshare_videos AS v";
		$query .= " LEFT JOIN #__allvideoshare_categories AS c ON v.catid=c.id";
		
		$where = array();
		 
		if ( $filter_state > -1 ) {
			$where[] = "v.published={$filter_state}";
		}
		 
		if ( $filter_category ) {
			$where[] = "v.catid={$filter_category}";
		}
		
		if ( $search ) {
		 	$escaped = $db->escape( $search, true );
			$where[] = "LOWER(v.title) LIKE  " . $db->Quote( '%' . $escaped . '%', false );
		}

		$where = ( count( $where ) ? " WHERE " . implode( ' AND ', $where ) : '' );
		$query .= $where;
		 
		$query .= " ORDER BY v.catid, v.ordering";
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
		$filter_category = $app->getUserStateFromRequest( $option . $view . 'filter_category', 'filter_category', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );		 
         
        $query = "SELECT COUNT(id) FROM #__allvideoshare_videos";
		$where = array();
		 
		if ( $filter_state > -1 ) {
			$where[] = "published={$filter_state}";
		}

		if ( $filter_category ) {
			$where[] = "catid={$filter_category}";
		}
		 
		if ( $search ) {
		 	$escaped = $db->escape( $search, true );
			$where[] = "LOWER(title) LIKE " . $db->Quote( '%' . $escaped . '%', false );
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
		 
		$option = $app->input->get('option');
		$view = $app->input->get('view');
		 
		$filter_state = $app->getUserStateFromRequest( $option . $view . 'filter_state', 'filter_state', -1, 'int' );
		$filter_category = $app->getUserStateFromRequest( $option . $view . 'filter_category', 'filter_category', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );
     
    	$lists = array();
		$lists['search'] = $search;
            
		$filter_state_options[] = JHTML::_( 'select.option', -1, '-- ' . JText::_( 'SELECT_PUBLISHING_STATE' ) . ' --' );
		$filter_state_options[] = JHTML::_( 'select.option', 1, JText::_('PUBLISHED' ) );
		$filter_state_options[] = JHTML::_( 'select.option', 0, JText::_( 'UNPUBLISHED' ) );
		$lists['state'] = JHTML::_( 'select.genericlist', $filter_state_options, 'filter_state', 'onchange="this.form.submit();"', 'value', 'text', $filter_state );

		$lists['categories'] = AllVideoShareHtml::ListCategories( 'filter_category', $filter_category, 'onchange="this.form.submit();"' );	
		 
        return $lists;
		
	}

	public function save() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		 
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = $cid[0];
		 
	  	$row = JTable::getInstance( 'Videos', 'AllVideoShareTable' );
      	$row->load( $id );
	
		$post = $app->input->post->getArray();
      	if ( ! $row->bind( $post ) ) {
		 	$app->enqueueMessage( $row->getError(), 'error' );
	  	}
		
		jimport( 'joomla.filesystem.folder' );
			
		$folder = 'videos/' . JHTML::_( 'date', 'now', 'Y-m', false );	
		if ( ! JFolder::exists( ALLVIDEOSHARE_UPLOAD_BASE . $folder . '/' ) ) {
			JFolder::create( ALLVIDEOSHARE_UPLOAD_BASE . $folder . '/' );
		}

		$row->title = AllVideoShareUtils::safeString( $row->title );
		$row->slug  = AllVideoShareUtils::getVideoSlug( $row );		 
	  	$row->description = $app->input->post->get( 'description', '', 'RAW' );
		$row->thirdparty  = $app->input->post->get( 'thirdparty', '', 'RAW' );

		if ( $post['type_thumb'] == 'upload' ) {
	  		$row->thumb = AllVideoShareUpload::doUpload( 'upload_thumb', $folder, $row->thumb );
	  	}
		
		switch ( $row->type ) {
			case 'general':
				if ( 'upload' == $post['type_video'] ) {
					$row->video = AllVideoShareUpload::doUpload( 'upload_video', $folder, $row->video );
				}
				
				if ( 'upload' == $post['type_hd'] ) {
					$row->hd = AllVideoShareUpload::doUpload( 'upload_hd', $folder, $row->hd );
				}
				break;
			case 'youtube':
				if ( ! empty( $post['external'] ) ) {
					$row->video = $post['external'];
				}
				
				$v = AllVideoShareUtils::getYouTubeVideoId( $row->video );
				
				$row->video = 'https://www.youtube.com/watch?v=' . $v;
				if ( ! $row->thumb ) {
					$row->thumb = AllVideoShareUtils::getYouTubeVideoImg( $v );
				}				
				break;
			case 'vimeo':
				if ( ! empty( $post['external'] ) ) {
					$row->video = $post['external'];
				}
				
				if ( ! $row->thumb ) {
					$v = AllVideoShareUtils::getVimeoVideoId( $row->video );
					$row->thumb = AllVideoShareUtils::getVimeoVideoImg( $v );
				}	
				break;
			case 'rtmp':
				if ( ! empty( $post['external'] ) ) {
					$row->video = $post['external'];
				}
				break;
			case 'hls':	
			case 'thirdparty':
				// Do nothing here
				break;
		}
		
		$row->video = AllVideoShareUtils::cleanURL( $row->video ); 
		$row->hd = AllVideoShareUtils::cleanURL( $row->hd ); 
		$row->hls = AllVideoShareUtils::cleanURL( $row->hls ); 
		$row->thumb = AllVideoShareUtils::cleanURL( $row->thumb ); 
		$row->preview = ''; 
		$row->streamer = AllVideoShareUtils::cleanURL( $row->streamer ); 
		
		$row->reorder( 'catid=' . $row->catid );
		
	  	if ( ! $row->store() ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	}

		$task = $app->input->get('task');
	  	switch ( $task ) {
        	case 'apply':
            	$msg  = JText::_( 'CHANGES_SAVED' );
             	$link = 'index.php?option=com_allvideoshare&view=videos&task=edit&' . JSession::getFormToken() . '=1&cid[]=' . $row->id;				
             	break;
        	case 'save':
        	default:
				$msg  = JText::_( 'SAVED' );
             	$link = 'index.php?option=com_allvideoshare&view=videos';
              	break;
      	}
		 
		$app->redirect( $link, $msg, 'message' ); 	
		  
	}
	
	public function cancel() {
	
		$app = JFactory::getApplication();
		 
		$link = 'index.php?option=com_allvideoshare&view=videos';
	    $app->redirect( $link );
		 
	}	

	public function delete() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		 
        $cid = $app->input->get( 'cid', array(), 'ARRAY' );
        $cids = implode( ',', $cid );
		 
        if ( count( $cid ) ) {
			// delete media files
			$query = "SELECT video, hd, thumb, preview FROM #__allvideoshare_videos WHERE id IN ( $cids )";
            $db->setQuery( $query );
			$items = $db->loadObjectList();
			
			foreach ( $items as $item ) {
				AllVideoShareUtils::deleteFile( $item->video );
				AllVideoShareUtils::deleteFile( $item->hd );
				AllVideoShareUtils::deleteFile( $item->thumb );
				AllVideoShareUtils::deleteFile( $item->preview );
			}
			
			// delete from the database
            $query = "DELETE FROM #__allvideoshare_videos WHERE id IN ( $cids )";
            $db->setQuery( $query );
            if ( ! $db->query() ) {
                echo "<script> alert('" . $db->getErrorMsg() . "');window.history.go(-1); </script>\n";
            }
        }
		
        $app->redirect( 'index.php?option=com_allvideoshare&view=videos' );
		 
	}
	
	public function publish() {
	
		$app = JFactory::getApplication();
		 
		$cid = $app->input->get( 'cid', array(), 'ARRAY' );
        $publish = ( 'publish' == $app->input->get('task') ) ? 1 : 0;
			
        $row = JTable::getInstance( 'Videos', 'AllVideoShareTable' );
        $row->publish( $cid, $publish );
		 
        $app->redirect( 'index.php?option=com_allvideoshare&view=videos' );
		 
    }
	
	public function saveOrder() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$total = count( $cid );
		 
		$order = $app->input->get( 'order', array(0), 'ARRAY' );
		JArrayHelper::toInteger( $order, array(0) );
		 
		$row = JTable::getInstance( 'Videos', 'AllVideoShareTable' );
		 
		$groupings = array();
		for ( $i = 0; $i < $total; $i++ ) {
			$row->load( (int) $cid[ $i ] );
			$groupings[] = $row->catid;
 			if ( $row->ordering != $order[ $i ] ) {
				$row->ordering  = $order[ $i ];
				if ( ! $row->store() ) {
					$app->enqueueMessage( $db->getErrorMsg(), 'error' );
				}
			}
		}
 
		$groupings = array_unique( $groupings );
		foreach ( $groupings as $group ) {
			$row->reorder( 'catid=' . $group );
		}
 
		$app->redirect( 'index.php?option=com_allvideoshare&view=videos', JText::_( 'NEW_ORDERING_SAVED' ), 'message' );
		 
	}
	
	public function move( $direction ) {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		 
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = (int) $cid[0];
		 
		$row = JTable::getInstance( 'Videos', 'AllVideoShareTable' );
		$row->load( $id );		 
		$row->move( $direction, 'catid=' . $row->catid );
		$row->reorder( 'catid=' . $row->catid );
		 
	  	$app->redirect( 'index.php?option=com_allvideoshare&view=videos', JText::_( 'NEW_ORDERING_SAVED' ), 'message' );
		 
	}
	
}