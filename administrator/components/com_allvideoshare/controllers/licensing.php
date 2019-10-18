<?php
/*
 * @version		$Id: licensing.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareControllerLicensing extends AllVideoShareController {

	public function licensing() {
	
	   	$model = $this->getModel( 'licensing' );
		
	    $view = $this->getView( 'licensing', 'html' );        		
        $view->setModel( $model, true );
		$view->setLayout( 'default' );
		$view->display();
		
	}
	
	public function save(){
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'licensing' );
	  	$model->save();
		
	}
		
}