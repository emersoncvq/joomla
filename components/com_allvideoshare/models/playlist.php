<?php
/*
 * @version		$Id: playlist.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelPlayList extends AllVideoShareModel {

	public function buildXml() {
	
		ob_clean();
		header( "content-type:text/xml;charset=utf-8" );
		echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		echo '<playlist>' . "\n";
		echo $this->buildNodes();
		echo '</playlist>' . "\n";
		exit();
		
	}
	
	public function buildNodes() {
	
		$items = $this->getItems();	
		$link  = $this->getLink();	
		$node  = '';
		
		foreach ( $items as $item ) {
			$node .= '<item>' . "\n";
			$node .= '<thumb>' . AllVideoShareUtils::getImage( $item->thumb ). '</thumb>' . "\n";
			$node .= '<title><![CDATA[' . $item->title . ']]></title>' . "\n";
			$node .= '<link>' . JRoute::_( $link . 'slg=' . $item->slug ) . '</link>' . "\n";
			$node .= '</item>' . "\n";
		}
		
		return $node;
		
	}
	
	public function getItems() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$catid = $this->getCatid();		
        $query = "SELECT * FROM #__allvideoshare_videos WHERE published=1 AND catid=" . $catid . " AND id!=" . $app->input->getInt( 'vid' );
        $db->setQuery( $query );
        $items = $db->loadObjectList();
        
		return $items;
		
	}
	
	public function getCatid() {
	
		$app = JFactory::getApplication();
        $db = JFactory::getDBO();
		 
        $query = "SELECT catid FROM #__allvideoshare_videos WHERE id=" . $app->input->getInt( 'vid' );
        $db->setQuery( $query );
        $item = $db->loadResult();
		 
        return $item;
		 
	}
	
	public function getLink() {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

        $query = "SELECT * FROM #__allvideoshare_players WHERE id=" . $app->input->getInt( 'vid' );
        $db->setQuery( $query );
        $item = $db->loadObject();
		 
		$link = '';
		 
		if ( $item->customplayerpage ) {
		 	$link = $item->customplayerpage;
		} else {
		 	$link = 'index.php?option=com_allvideoshare&view=video';
			$link.= $app->input->getInt( 'Itemid' ) ? '&Itemid=' . $app->input->getInt( 'Itemid' ) : '';
		}
		 
		$qs = ( ! strpos( $link, '?' ) ) ? '?' : '&';
		 
		return $link . $qs;
		 
	}

}