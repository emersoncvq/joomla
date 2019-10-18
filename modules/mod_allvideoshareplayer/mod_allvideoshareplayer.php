<?php
/*
 * @version		$Id: mod_allvideoshareplayer.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Include the syndicate functions only once
require_once( dirname( __FILE__ ) . '/helper.php' );
require_once( JPATH_ROOT . '/administrator/components/com_allvideoshare/libraries/utils.php' );
require_once( JPATH_ROOT . '/administrator/components/com_allvideoshare/libraries/player.php' );

$videoId = AllVideoSharePlayerHelper::getVideoId( $params );

$playerObj = new AllVideoSharePlayer();
$player = $playerObj->build( $videoId, $params->get( 'playerid' ), $params->get( 'autodetect' ) );

if ( $video = $playerObj->item ) {
	$doc = JFactory::getDocument();
	$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/allvideoshare.css' ), 'text/css', 'screen' );

	require( JModuleHelper::getLayoutPath( 'mod_allvideoshareplayer' ) );	
}