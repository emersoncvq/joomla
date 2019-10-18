<?php
/*
 * @version		$Id: videos.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareControllerVideos extends AllVideoShareController {

	public function videos() {	
	
		$doc = JFactory::getDocument();
		$type = $doc->getType();
		
		$model = $this->getModel( 'videos' );
		
	    $view = $this->getView( 'videos', $type );	
        $view->setModel( $model, true );
		$view->setLayout( 'default' );
		$view->display();
		
	}
			
}