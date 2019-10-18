<?php
/*
 * @version		$Id: router.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareRouter extends JComponentRouterBase {

	public function build( &$query ) {
	
		$segments = array();
	   
		if ( isset( $query['view'] ) ) {
			$segments[] = $query['view'];
			unset( $query['view'] );
		}
		
		if ( isset( $query['slg'] ) ) {
			$segments[] = $query['slg'];
			unset( $query['slg'] );
		}
		
		return $segments;
		
	}
	
	public function parse( &$segments ) {
	
		$vars  = array();
		$count = count( $segments );
	
		if ( $count >= 1 && $segments[0] ) {
			$vars['view'] = $segments[0];
		}
		
		if ( $count == 2 ) {
			$vars['slg'] = $segments[1];
		}
	
		return $vars;
		
	}

}