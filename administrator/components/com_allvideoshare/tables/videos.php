<?php
/*
 * @version		$Id: videos.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// include library dependencies
jimport( 'joomla.filter.input' );

class AllVideoShareTableVideos extends JTable {

	var $id = null;
	var $title = null;
	var $slug = null;
	var $catid = null;
	var $category = null;
	var $user = null;
	var $type = null;
	var $streamer = null;
	var $dvr = null;
	var $token = null;
	var $video = null;
	var $hd = null;
	var $hls = null;
	var $thumb = null;
	var $preview = null;
	var $thirdparty = null;	
	var $featured = null;	
	var $description = null;
	var $tags = null;
	var $metadescription = null;
	var $views = null;
	var $access = null;
	var $ordering = null;	
	var $published = null;
	var $created_date = null;	

	public function __construct( &$db ) {
		parent::__construct( '#__allvideoshare_videos', 'id', $db );
	}

	public function check() {
		return true;
	}
	
}