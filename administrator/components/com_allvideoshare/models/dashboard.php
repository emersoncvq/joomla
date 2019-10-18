<?php
/*
 * @version		$Id: dashboard.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelDashboard extends AllVideoShareModel {
	
	public function getServerDetails() {
		
		$details = array(
			array( 
				'name'  => JText::_( 'ALLOW_FILE_UPLOADS' ), 
				'value' => ini_get( 'file_uploads' ) ? JText::_( 'ALL_VIDEO_SHARE_YES' ) : JText::_( 'ALL_VIDEO_SHARE_NO' )
			),
			array( 
				'name'  => JText::_( 'UPLOAD_MAX_FILESIZE' ), 
				'value' => ini_get( 'upload_max_filesize' )
			),
			array( 
				'name'  => JText::_( 'MAX_INPUT_TIME' ), 
				'value' => ini_get( 'max_input_time' )
			),
			array( 
				'name'  => JText::_( 'MEMORY_LIMIT' ), 
				'value' => ini_get( 'memory_limit' ) 
			),
			array( 
				'name'  => JText::_( 'MAX_EXECUTION_TIME' ), 
				'value' => ini_get( 'max_execution_time' )
			),
			array( 
				'name'  => JText::_( 'POST_MAX_SIZE' ), 
				'value' => ini_get( 'post_max_size' )
			),
			array( 
				'name'  => JText::_( 'UPLOAD_DIRECTORY_PERMISSION' ), 
				'value' => is_writable( JPATH_ROOT . '/media/' ) ? JText::_( 'ALL_VIDEO_SHARE_YES' ) : JText::_( 'ALL_VIDEO_SHARE_NO' )
			)
		);

        return $details;
		
	}
	
	public function getRecentVideos() {
	 
         $db = JFactory::getDBO();
		 		 
         $query = "SELECT * FROM #__allvideoshare_videos ORDER BY id DESC LIMIT 10";
         $db->setQuery( $query );
         $items = $db->loadObjectList();
		 
         return $items;
		 
	}
	
	public function getPopularVideos() {
	
         $db = JFactory::getDBO();	
		 	 
         $query = "SELECT * FROM #__allvideoshare_videos ORDER BY views DESC LIMIT 10";
         $db->setQuery( $query );
         $items = $db->loadObjectList();
		 
         return $items;
		 
	}
	
}