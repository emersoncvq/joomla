<?php
/*
 * @version		$Id: licensing.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelLicensing extends AllVideoShareModel {
	
	public function getItem() {
	
         $row = JTable::getInstance( 'Licensing', 'AllVideoShareTable' );
         $row->load(1);

         return $row;
		 
	}
	
	public function save()	{
	
		$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = $cid[0];
		 
	  	$row = JTable::getInstance( 'Licensing', 'AllVideoShareTable' );
      	$row->load( $id );
	
		$post = $app->input->post->getArray();
      	if ( ! $row->bind( $post ) ) {
		 	$app->enqueueMessage( $row->getError(), 'error' );
	  	}

	  	if ( 'upload' == $post['type_logo'] ) {
			jimport( 'joomla.filesystem.folder' );
			
			$folder = 'system';
		 	if ( ! JFolder::exists( ALLVIDEOSHARE_UPLOAD_BASE ) ) {
				JFolder::create( ALLVIDEOSHARE_UPLOAD_BASE );
			}
		
			$row->logo = AllVideoShareUpload::doUpload( 'upload_logo', $folder, $row->logo );
	  	}
		$row->logo = AllVideoShareUtils::cleanURL( $row->logo );
	  
	  	if ( ! $row->store() ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	}

		$msg  = JText::_( 'SAVED' );
        $link = 'index.php?option=com_allvideoshare&view=licensing';
		 
		$app->redirect( $link, $msg, 'message' ); 
		 	 
	}
	
}