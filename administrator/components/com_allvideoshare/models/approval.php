<?php
/*
 * @version		$Id: approval.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelApproval extends AllVideoShareModel {
	
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
		$filter_category = $app->getUserStateFromRequest( $option . $view . 'filter_category', 'filter_category', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );

        $query = "SELECT v.*, c.name as category FROM #__allvideoshare_videos AS v";
		$query.= " LEFT JOIN #__allvideoshare_categories AS c ON v.catid = c.id";
		 
		$where = array();		 
		$where[] = "v.published=0";
		 
		if ( $filter_category ) {
			$where[] = "v.catid={$filter_category}";
		}
		
		if ( $search ) {
		 	$escaped = $db->escape( $search, true );
			$where[] = "LOWER(v.title) LIKE " . $db->Quote( '%' . $escaped . '%', false );
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
		 
		$filter_category = $app->getUserStateFromRequest( $option . $view . 'filter_category', 'filter_category', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower( $search );
         
        $query = "SELECT COUNT(id) FROM #__allvideoshare_videos";
		 
		$where = array();
		$where[] = "published=0";

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
		 
		$option = $app->input->get('option');
		$view = $app->input->get('view');
		 
		$total = $this->getTotal();
		$limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg( 'list_limit' ), 'int' );
		$limitstart = $app->getUserStateFromRequest( $option.$view.'.limitstart', 'limitstart', 0, 'int' );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );
     
    	jimport( 'joomla.html.pagination' );
		$pagination = new JPagination( $total, $limitstart, $limit );
		 
        return $pagination;
		 
	}
	
	public function getLists() {
	
		$app = JFactory::getApplication();	
		 
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		 
		$filter_category = $app->getUserStateFromRequest( $option . $view . 'filter_category', 'filter_category', 0, 'int' );
		$search = $app->getUserStateFromRequest( $option . $view . 'search', 'search', '', 'string' );
		$search = JString::strtolower ( $search );
     
    	$lists = array();
		$lists['search'] = $search;
		$lists['categories'] = AllVideoShareHtml::ListCategories( 'filter_category', $filter_category, 'onchange="this.form.submit();"' );	
		 
        return $lists;
		 
	}
	
	public function getVideosCountWaitingApproval() {
	
        $db = JFactory::getDBO();
				 
        $query  = "SELECT COUNT(id) FROM #__allvideoshare_videos WHERE published=0";
        $db->setQuery( $query );
        $count = $db->loadResult();
		 
        return $count;
		 
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
		if ( ! JFolder::exists( ALLVIDEOSHARE_UPLOAD_BASE ) ) {
			JFolder::create( ALLVIDEOSHARE_UPLOAD_BASE );
		}
			
		$row->title = AllVideoShareUtils::safeString( $row->title );
		$row->slug  = AllVideoShareUtils::getVideoSlug( $row );		 
	  	$row->description = $app->input->post->get( 'description', '', 'RAW' );
		$row->thirdparty = $app->input->post->get( 'thirdparty', '', 'RAW' );
	  
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

      	$msg = JText::_( 'ADDED_TO_VIDEOS_SECTION' );
        $link = 'index.php?option=com_allvideoshare&view=approval';

	  	$app->redirect( $link, $msg, 'message' );
		 
	}
	
	public function cancel() {
	
		 $app = JFactory::getApplication();
		 
		 $link = 'index.php?option=com_allvideoshare&view=approval';
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
                echo "<script> alert('".$db->getErrorMsg()."');window.history.go(-1); </script>\n";
            }
        }
		
        $app->redirect( 'index.php?option=com_allvideoshare&view=approval' );
		
	}
	
	public function publish() {
	
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(), 'ARRAY' ); 
        $publish = ( 'publish' == $app->input->get('task') ) ? 1 : 0;
			
        $row = JTable::getInstance( 'Videos', 'AllVideoShareTable' );
        $row->publish( $cid, $publish );
		
        $app->redirect( 'index.php?option=com_allvideoshare&view=approval' );
		
    }
		
}