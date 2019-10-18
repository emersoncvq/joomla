<?php
/*
 * @version		$Id: config.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelConfig extends AllVideoShareModel {

	public function getItem() {
	
        $db  = JFactory::getDBO();
				
        $query = "SELECT * FROM #__allvideoshare_config WHERE id=1";
        $db->setQuery( $query );
        $item = $db->loadObject();
		 
        return $item;
		
	}
	
	public function save() {
	
	  	$app = JFactory::getApplication();
		
		$cid = $app->input->get( 'cid', array(0), 'ARRAY' );
		$id = $cid[0];
		
	  	$row = JTable::getInstance( 'Config', 'AllVideoShareTable' );
      	$row->load( $id );

		$post = $app->input->post->getArray();
      	if ( ! $row->bind( $post ) ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	}
	
		$row->custom_css = $app->input->post->get( 'custom_css', '', 'RAW' );
		$row->is_premium = 0;
	  
	  	if ( ! $row->store() ) {
			$app->enqueueMessage( $row->getError(), 'error' );
	  	}

      	$msg = JText::_( 'SAVED' );
      	$link = 'index.php?option=com_allvideoshare&view=config';
  
	  	$app->redirect( $link, $msg, 'message' );
		
	}
	
}