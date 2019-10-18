<?php
/*
 * @version		$Id: players.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// include library dependencies
jimport( 'joomla.filter.input' );

class AllVideoShareTablePlayers extends JTable {

	var $id = null;
	var $type = null;
	var $name = null;
	var $ratio = null;
	var $loop = null;
	var $autostart = null;
	var $buffer = null;
	var $volumelevel = null;
	var $stretch = null;
	var $controlbar = null;
	var $playlist = null;
	var $durationdock = null;
	var $timerdock = null; 
	var $fullscreendock = null;
	var $hddock = null;
	var $embeddock = null;
	var $sharedock = null;
	var $facebookdock = null;
	var $twitterdock = null;	
	var $controlbaroutlinecolor = null;
	var $controlbarbgcolor = null;
	var $controlbaroverlaycolor = null;
	var $controlbaroverlayalpha = null;
	var $iconcolor = null;
	var $progressbarbgcolor = null;
	var $progressbarbuffercolor = null;
	var $progressbarseekcolor = null;
	var $volumebarbgcolor = null;
	var $volumebarseekcolor = null;
	var $playlistbgcolor = null;
	var $customplayerpage = null;
	var $ad_engine = null;
	var $preroll = null;
	var $postroll = null;
	var $vast_url = null;
	var $vpaid_mode = null;
	var $livestream_ad_interval = null;
	var $published = null;	

	public function __construct( &$db ) {
		parent::__construct( '#__allvideoshare_players', 'id', $db );
	}

	public function check() {
		return true;
	}

}