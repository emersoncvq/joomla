<?php
/*
 * @version		$Id: user.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelUser extends AllVideoShareModel {

	public function getItems() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();	
		 
		$limit = $app->getUserStateFromRequest( 'global.list.limit', 'limit', 10, 'int' );
		$limitstart = $app->input->get( 'limitstart', '0', 'INT' );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );
 
		$this->setState( 'limit', $limit );
		$this->setState( 'limitstart', $limitstart );		 
		 
		$s = $app->getUserStateFromRequest( 'com_allvideoshare.user.search', 's', '', 'string' );	
		$s = JString::strtolower( $s );    	 	 
		$searchWord = $db->Quote( '%'.$db->escape( $s, true ).'%', false );	
			
		$query  = "SELECT v.*, c.name as category FROM #__allvideoshare_videos AS v";
		$query .= " LEFT JOIN #__allvideoshare_categories AS c ON v.catid=c.id";
		$query .= " WHERE v.user=" . $db->Quote( ALLVIDEOSHARE_USERNAME ) . " AND (v.title LIKE $searchWord OR v.tags LIKE $searchWord OR c.name LIKE $searchWord)";
		$query .= " ORDER BY v.id DESC";
		 
    	$db->setQuery ( $query, $limitstart, $limit );
    	$items = $db->loadObjectList();
		 
        return $items;
		 
	}
	
	public function getTotal() {
	
		$app = JFactory::getApplication();
        $db = JFactory::getDBO();
		 
		$s = $app->getUserStateFromRequest( 'com_allvideoshare.user.search', 's', '', 'string' );	
		$s = JString::strtolower( $s );		 
		$searchWord = $db->Quote( '%' . $db->escape( $s, true ) . '%', false );	
		
		$query  = "SELECT COUNT(v.id) FROM #__allvideoshare_videos AS v";
		$query .= " LEFT JOIN #__allvideoshare_categories AS c ON v.catid=c.id";
		$query .= " WHERE v.user=" . $db->quote( ALLVIDEOSHARE_USERNAME ) . " AND (v.title LIKE $searchWord OR v.tags LIKE $searchWord OR c.name LIKE $searchWord)";
		$query .= " ORDER BY v.id DESC";
		
        $db->setQuery( $query );
        $total = $db->loadResult();
		 
        return $total;
		 
	}
	
	public function getPagination() {
	
    	 jimport( 'joomla.html.pagination' );
		 $pagination = new JPagination( $this->getTotal(), $this->getState( 'limitstart' ), $this->getState( 'limit' ) );
		 
         return $pagination;
		 
	}
	
	public function getItem() {
		 
		 $app = JFactory::getApplication();
		 $id = $app->input->getInt( 'id' );
		 
         $row = JTable::getInstance( 'Videos', 'AllVideoShareTable' );
         $row->load( $id );

         return $row;
		 
	}
	
	public function save() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		 
		$id = $app->input->getInt( 'id', 0 );
		 
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

		if ( 'upload' == $post['type_thumb'] ) {
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
		 
		if ( ! $row->id ) {
		 	$config = AllVideoShareUtils::getConfig();
			
		 	$row->user      = ALLVIDEOSHARE_USERNAME;
			$row->access    = 'public';
			$row->published = $config->auto_approval;
		 }
		 
		 $row->reorder( 'catid=' . $row->catid );
		 
	  	 if ( ! $row->store() ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	 }

		 $itemId = '';
		 if ( $app->input->getInt( 'Itemid' ) ) {
		 	$itemId = '&Itemid=' . $app->input->getInt('Itemid');
		 }
		 $link = JRoute::_( 'index.php?option=com_allvideoshare&view=user' . $itemId, false );
		 
		 $app->redirect( $link, JText::_( 'SAVED' ) );
		 	 
	}

	public function delete() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();	
		
		$id = $app->input->getInt( 'id' );
		
		// delete media files
		$query = "SELECT video, hd, thumb, preview FROM #__allvideoshare_videos WHERE id=" . $id;
		$db->setQuery( $query );
		$item = $db->loadObject();
		
		AllVideoShareUtils::deleteFile( $item->video );
		AllVideoShareUtils::deleteFile( $item->hd );
		AllVideoShareUtils::deleteFile( $item->thumb );
		AllVideoShareUtils::deleteFile( $item->preview );
       
	   	// delete from the database
		$query = "DELETE FROM #__allvideoshare_videos WHERE id=" . $id;
        $db->setQuery( $query );
        $db->query();
		
         $itemId = '';
		 if ( $app->input->getInt( 'Itemid' ) ) {
		 	$itemId = '&Itemid=' . $app->input->getInt( 'Itemid' );
		 }
		 $link = JRoute::_( 'index.php?option=com_allvideoshare&view=user' . $itemId, false );
		 
		 $app->redirect( $link ); 
		 
	}
		
}