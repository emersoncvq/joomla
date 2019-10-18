<?php
/*
 * @version		$Id: ads.php 3.3.0 2019-01-10 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelAds extends AllVideoShareModel {

    public function getPlayer() {
        $app = JFactory::getApplication();	
        $db = JFactory::getDBO();
        
        $query = 'SELECT * FROM #__allvideoshare_players WHERE published=1 AND id=' . $app->input->getInt( 'id' );
        $db->setQuery( $query );
        $player = $db->loadObject();
        
        // Fallback to the default player profile
        if ( empty( $player ) ) {
            $query = 'SELECT * FROM #__allvideoshare_players WHERE id=1';
            $db->setQuery( $query );
            $player = $db->loadObject();
        }
        
        return $player;        
    }

    public function getPrerollId() {	
        $db = JFactory::getDBO();
				 
        $query = "SELECT id FROM #__allvideoshare_adverts WHERE published=1 AND (type=" . $db->Quote( 'preroll' ) . " OR type=" . $db->Quote( 'both' ) . ") ORDER BY RAND() LIMIT 1";
        $db->setQuery( $query );
        $id = $db->loadResult();
		
		return $id;  
    }

    public function getPostrollId() {	
        $db = JFactory::getDBO();
				 
        $query = "SELECT id FROM #__allvideoshare_adverts WHERE published=1 AND (type=" . $db->Quote( 'postroll' ) . " OR type=" . $db->Quote( 'both' ) . ") ORDER BY RAND() LIMIT 1";
        $db->setQuery( $query );
        $id = $db->loadResult();
		
		return $id;  
    }

    public function getAd() {	
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
                
        $query = 'SELECT * FROM #__allvideoshare_adverts WHERE published=1 AND id=' . $app->input->getInt( 'id' );
        $db->setQuery( $query );
        $ad = $db->loadObject();
    
        return $ad;   
    }

    public function impression() {
        $app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$query = 'UPDATE #__allvideoshare_adverts SET impressions=impressions+1 WHERE id=' . $app->input->getInt( 'id' );
    	$db->setQuery( $query );
		$db->query();
    }

    public function click() {	
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();	
			
		$query = 'UPDATE #__allvideoshare_adverts SET clicks=clicks+1 WHERE id=' . $app->input->getInt( 'id' );
    	$db->setQuery( $query );
		$db->query();		
	}
   
}