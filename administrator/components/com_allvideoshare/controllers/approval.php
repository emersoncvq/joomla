<?php
/*
 * @version		$Id: approval.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareControllerApproval extends AllVideoShareController {

	public function approval()	{
	    
		$model = $this->getModel( 'approval' );	
		
	    $view = $this->getView( 'approval', 'html' );		
        $view->setModel( $model, true );
		$view->setLayout( 'default' );
		$view->display();
		
	}	
	
	public function edit() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'approval' );
		
	    $view = $this->getView( 'approval' , 'html' );
        $view->setModel( $model, true );
		$view->setLayout( 'edit' );
		$view->edit();
		
	}
	
	public function delete() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'approval' );
	 	$model->delete();
		
	}
	
	public function publish() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'approval' );
        $model->publish();
		
    }
	
    public function unpublish() {	
        $this->publish();
    }	
		
}