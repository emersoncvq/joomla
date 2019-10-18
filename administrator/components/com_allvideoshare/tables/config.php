<?php
/*
 * @version		$Id: config.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// include library dependencies
jimport( 'joomla.filter.input' );

class AllVideoShareTableConfig extends JTable {

	var $id = null;	
	var $rows = null;
	var $cols = null;
	var $image_ratio = null;
	var $playerid = null;
	var $layout = null;
	var $relatedvideoslimit = null;
	var $title = null;
	var $description = null;
	var $category = null;
	var $views = null;
	var $search = null;
	var $comments_type = null;
	var $fbappid = null;
	var $comments_posts = null;
	var $comments_color = null;
	var $auto_approval = null;
	var $type_youtube = null;
	var $type_vimeo = null;
	var $type_rtmp = null;
	var $type_hls = null;
	var $load_bootstrap_css = null;
	var $load_icomoon_font = null;
	var $custom_css = null;
	var $show_feed = null;
	var $feed_limit = null;
	var $show_gdpr_consent = null;
	var $itemid_video = null;
	var $is_premium = null;

	public function __construct( &$db ) {
		parent::__construct( '#__allvideoshare_config', 'id', $db );
	}

	public function check() {
		return true;
	}
	
}