<?php
/*
 * @version		$Id: config.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareControllerConfig extends AllVideoShareController {

	public function config() {
	
	    $model = $this->getModel( 'config' );
		
	    $view = $this->getView( 'config', 'html' );		
        $view->setModel( $model, true );
		$view->setLayout( 'default' );
		$view->display();
		
	}
	
	public function save() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'config' );
	  	$model->save();
		
	}
		
}