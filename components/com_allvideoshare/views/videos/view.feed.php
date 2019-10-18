<?php
/*
 * @version		$Id: view.feed.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareViewVideos extends AllVideoShareView {

	public function display( $tpl = null ) {
		
		$app = JFactory::getApplication();	
		$doc = JFactory::getDocument();	
			
		$model = $this->getModel();
		
		$config = AllVideoShareUtils::getConfig();
		
		$doc->editor = $app->getCfg( 'fromname' );
		$doc->editorEmail = $app->getCfg( 'mailfrom' );	

		$params = $app->getParams();
		
		$items = $model->getItems( $params, 20 );
		
		$itemId = AllVideoShareUtils::getVideoMenuId( $config );		
		$itemId = ! empty( $itemId ) ? '&Itemid=' . $itemId : '';
				 
		foreach ( $items as $item ) {
			$title = $this->escape( $item->title );
			$title = html_entity_decode( $title, ENT_COMPAT, 'UTF-8' );
			
			$target = JRoute::_( "index.php?option=com_allvideoshare&view=video&slg=" . $item->slug . $itemId );
			
			$description  = $item->description;
			
			$image = ! empty( $item->preview ) ? $item->preview : $item->thumb;
			if ( ! empty( $image ) ) {
				$description .= '<img src="' . $image . '" />';
			}				
							
			// load individual item creator class
			$feeditem = new JFeedItem();
			
			$feeditem->title	   = $title;
			$feeditem->link		   = $target;				
			$feeditem->description = $description;			
			$feeditem->category	   = $item->category;	
			$feeditem->date		   = $item->created_date;	
										
			// loads item info into rss array
			$doc->addItem( $feeditem );									
		}				
		
    }
	
}