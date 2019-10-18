<?php
/*
 * @version		$Id: allvideoshareplayer.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// import required libraries
jimport( 'joomla.plugin.plugin' );
require_once( JPATH_ROOT . '/administrator/components/com_allvideoshare/libraries/utils.php' );
require_once( JPATH_ROOT . '/administrator/components/com_allvideoshare/libraries/player.php' );

class plgContentAllVideoSharePlayer extends JPlugin {

	protected $autoloadLanguage = true;
	
	public function onContentPrepare( $context, &$article, &$params, $page = 0 ) {
		$this->onPrepareContent( $article, $params, $page );
	}

	public function onPrepareContent( &$row, &$params, $limitstart ) {
	
		// simple performance check to determine whether bot should process further
		if ( JString::strpos( $row->text, 'avsplayer' ) === false ) {
			return true;
		}
		
		// expression to search for
 		$regex = '/{avsplayer\s*.*?}/i';
		
		// find all instances of plugin and put in $matches
		preg_match_all( $regex, $row->text, $matches );

		// Number of plugins
 		$count = count( $matches[0] );
		
		$this->plgContentProcessPositions( $row, $matches, $count, $regex );

	}
	
	public function plgContentProcessPositions( $row, $matches, $count, $regex ) {
	
 		for ( $i = 0; $i < $count; $i++ ) {
 			$load = str_replace( '{avsplayer', '', $matches[0][ $i ] );
 			$load = str_replace( '}', '', $load );
			$load = trim( $load );
			$load = explode( " ", $load );
			$load = implode( "&", $load );
 			
			$modules   = $this->plgContentLoadPosition( $load );
			$row->text = str_replace( $matches[0][ $i ], $modules, $row->text );
 		}

  		// removes tags without matching module positions
		$row->text = preg_replace( $regex, '', $row->text );
		
	}
	
	public function plgContentLoadPosition( $load ) {		
		
		$doc = JFactory::getDocument();
		$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/allvideoshare.css' ), 'text/css', 'screen' );
		
		parse_str( $load, $params );
		
		$params = array_merge(
			array(
				'videoid'    => 1,
				'playerid'   => 1,
				'autodetect' => 0
			),
			$params
		);
		
		$playerObj = new AllVideoSharePlayer();
		return $playerObj->build( $params['videoid'], $params['playerid'], $params['autodetect'] );
		
	}

}