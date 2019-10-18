<?php
/*
 * @version		$Id: player.php 3.3.0 2019-01-10 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareModelPlayer extends AllVideoShareModel {
    
    public function getVideo( $id ) {
        $db = JFactory::getDBO();        
        
        $query = 'SELECT * FROM #__allvideoshare_videos WHERE published=1 AND id=' . (int) $id;
        $db->setQuery( $query );
        $video = $db->loadObject();
        
        if ( ! empty( $video->preview ) ) {
            $video->thumb = $video->preview;
        }
        
        return $video;        
    }

    public function getPlayer( $id ) {	
        $db = JFactory::getDBO();
        
        $query = 'SELECT * FROM #__allvideoshare_players WHERE published=1 AND id=' . (int) $id;
        $db->setQuery( $query );
        $player = $db->loadObject();
        
        // Fallback to the default player profile
        if ( empty( $player ) ) {
            $query = 'SELECT * FROM #__allvideoshare_players WHERE id=1';
            $db->setQuery( $query );
            $player = $db->loadObject();
        }
        
        // Set player scaling ratio
        if ( empty( $player->ratio ) ) {
            $player->ratio = 56.25;
        }
        
        return $player;        
    }

    public function getLicense() {	
        $db = JFactory::getDBO();	
             
        $query = 'SELECT * FROM #__allvideoshare_licensing WHERE id=1';
        $db->setQuery( $query );
        $license = $db->loadObject();

        return $license;        
    }

    public function gdpr() {
        $app = JFactory::getApplication();

        // Set the cookie
        $time = time() + 604800; // 1 week
        $app->input->cookie->set( 'avs_gdpr_consent', 1, $time, $app->get( 'cookie_path', '/' ), $app->get( 'cookie_domain' ), $app->isSSLConnection() );

        echo 'success';
        exit;
    }
   
}