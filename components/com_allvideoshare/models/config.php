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
	
	public function buildXml() {
	
		ob_clean();
		header( "content-type:text/xml;charset=utf-8" );
		echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		echo '<config>' . "\n";
		echo $this->buildNodes();
		echo '</config>' . "\n";
		exit();
		
	}
	
	public function buildNodes() {
	
		$video     = $this->getVideo();
		$player    = $this->getPlayer();
		$licensing = $this->getLicensing();
		
		$node  = '';
		$node .= '<loop>' . $this->castAsBoolean( $player->loop ) . '</loop>' . "\n";
		$node .= '<autoStart>' . $this->castAsBoolean( $player->autostart ) . '</autoStart>' . "\n";
		$node .= '<buffer>' . $player->buffer . '</buffer>' . "\n";
		$node .= '<volumeLevel>' . $player->volumelevel . '</volumeLevel>' . "\n";
		$node .= '<stretch>' . $player->stretch . '</stretch>' . "\n";
		$node .= '<controlBar>' . $this->castAsBoolean( $player->controlbar ) . '</controlBar>' . "\n";
		$node .= '<playList>' . $this->castAsBoolean( $player->playlist ) . '</playList>' . "\n";
		$node .= '<durationDock>' . $this->castAsBoolean( $player->durationdock ) . '</durationDock>' . "\n";
		$node .= '<timerDock>' . $this->castAsBoolean( $player->timerdock ) . '</timerDock>' . "\n";		
		$node .= '<fullScreenDock>' . $this->castAsBoolean( $player->fullscreendock ) . '</fullScreenDock>' . "\n";
		$node .= '<hdDock>' . $this->castAsBoolean( $player->hddock ) . '</hdDock>' . "\n";
		$node .= '<embedDock>' . $this->castAsBoolean( $player->embeddock ) . '</embedDock>' . "\n";
		$node .= '<facebookDock>' . $this->castAsBoolean( $player->facebookdock ) . '</facebookDock>' . "\n";
		$node .= '<twitterDock>' . $this->castAsBoolean( $player->twitterdock ) . '</twitterDock>' . "\n";
		$node .= '<controlBarOutlineColor>' . $player->controlbaroutlinecolor . '</controlBarOutlineColor>' . "\n";
		$node .= '<controlBarBgColor>' . $player->controlbarbgcolor . '</controlBarBgColor>' . "\n";
		$node .= '<controlBarOverlayColor>' . $player->controlbaroverlaycolor . '</controlBarOverlayColor>' . "\n";
		$node .= '<controlBarOverlayAlpha>' . $player->controlbaroverlayalpha . '</controlBarOverlayAlpha>' . "\n";
		$node .= '<iconColor>' . $player->iconcolor . '</iconColor>' . "\n";
		$node .= '<progressBarBgColor>' . $player->progressbarbgcolor . '</progressBarBgColor>' . "\n";
		$node .= '<progressBarBufferColor>' . $player->progressbarbuffercolor . '</progressBarBufferColor>' . "\n";
		$node .= '<progressBarSeekColor>' . $player->progressbarseekcolor . '</progressBarSeekColor>' . "\n";
		$node .= '<volumeBarBgColor>' . $player->volumebarbgcolor . '</volumeBarBgColor>' . "\n";
		$node .= '<volumeBarSeekColor>' . $player->volumebarseekcolor . '</volumeBarSeekColor>' . "\n";
		$node .= '<playListBgColor>' . $player->playlistbgcolor . '</playListBgColor>' . "\n";
		$node .= '<type>' . $video->type . '</type>' . "\n";
		$image = ! empty( $video->preview ) ? $video->preview : $video->thumb;
		$node .= '<preview>' . $image . '</preview>' . "\n";
		$node .= '<streamer>' . $video->streamer . '</streamer>' . "\n";
		$node .= '<token>' . $video->token . '</token>' . "\n";
		if ( $player->preroll == 1 ) {
			$node .= $this->generatePreroll();			
		}
		$node .= '<video>' . $video->video . '</video>' . "\n";
		if ( $video->hd ) {
			$node .= '<hd>' . $video->hd . '</hd>' . "\n";
		}
		if ( $player->postroll == 1 ) {
			$node .= $this->generatePostroll();			
		}
		$node .= '<dvr>' . $this->castAsBoolean( $video->dvr ) . '</dvr>' . "\n";
		$node .= '<license>' . $licensing->licensekey . '</license>' . "\n";
		$node .= '<displayLogo>' . $this->castAsBoolean( $licensing->displaylogo ) . '</displayLogo>' . "\n";
		$node .= '<logo>' . $licensing->logo . '</logo>' . "\n";
		$node .= '<logoAlpha>' . $licensing->logoalpha . '</logoAlpha>' . "\n";
		$node .= '<logoPosition>' . $licensing->logoposition . '</logoPosition>' . "\n";
		$node .= '<logoTarget>' . $licensing->logotarget . '</logoTarget>' . "\n";
		
		return $node;
		
	}
	
	public function getVideo() {
	
		$app = JFactory::getApplication();
        $db = JFactory::getDBO();
		 
        $query = "SELECT * FROM #__allvideoshare_videos WHERE id=" . $app->input->getInt( 'vid' );
        $db->setQuery( $query );
        $item = $db->loadObject();
		 
        return $item;
		 
	}
	
	public function getPlayer() {
	
		$app = JFactory::getApplication();
        $db = JFactory::getDBO();
		 
        $query = "SELECT * FROM #__allvideoshare_players WHERE id=" . $app->input->getInt( 'pid' );
        $db->setQuery( $query );
        $item = $db->loadObject();
		 
		if ( 'original' == $item->stretch ) {
			$item->stretch = 'none';
		}
		 
        return $item;
		 
	}
	
	public function generatePreroll() {
	
         $db = JFactory::getDBO();
		 
         $query  = "SELECT id, video, link FROM #__allvideoshare_adverts WHERE published=1 AND (type=" . $db->Quote( 'preroll' ) . " OR type=" . $db->Quote( 'both' ) . ")";
		 $query .= " ORDER BY RAND() LIMIT 1";
         $db->setQuery( $query );
         $item = $db->loadObject();
		 
		 $node = '';
		 
		 if ( $item ) { 
			$node  = '<preroll>' . $item->video . '</preroll>' . "\n";
			$node .= '<prerollID>' . $item->id . '</prerollID>' . "\n";
			$node .= '<prerollLink>' . $item->link . '</prerollLink>' . "\n";
			$node .= '<prerollMsg><![CDATA[' . JText::_( 'PREROLL_MESSAGE' ) . ']]></prerollMsg>' . "\n";
		}
		
		return $node;
		
	}
	
	public function generatePostroll() {
	
        $db = JFactory::getDBO();
		 
        $query  = "SELECT id, video, link FROM #__allvideoshare_adverts WHERE published=1 AND (type=" . $db->Quote( 'postroll' ). " OR type=" . $db->Quote( 'both' ) . ")";
		$query .= " ORDER BY RAND() LIMIT 1";
        $db->setQuery( $query );
        $item = $db->loadObject();		 

		$node = '';
		
		 if ( $item ) {
		 	$node  = '<postroll>' . $item->video . '</postroll>' . "\n";
			$node .= '<postrollID>' . $item->id . '</postrollID>' . "\n";
			$node .= '<postrollLink>' . $item->link . '</postrollLink>' . "\n";
			$node .= '<postrollMsg><![CDATA[' . JText::_( 'POSTROLL_MESSAGE' ) . ']]></postrollMsg>' . "\n";
		 }
		 
		 return $node;
		 
	}

	public function getLicensing() {
	
         $db = JFactory::getDBO();
		 
         $query = "SELECT * FROM #__allvideoshare_licensing WHERE id=1";
         $db->setQuery( $query );
         $item = $db->loadObject();

         return $item;
		 
	}

	public function castAsBoolean( $val ) {	
		return ( 1 == $val ) ? 'true' : 'false';
	}

}