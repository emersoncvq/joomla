<?php
/*
 * @version		$Id: commercials.php 3.3.0 2019-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AllVideoShareControllerCommercials extends AllVideoShareController {

	public function ads() {
	
	    $model = $this->getModel( 'commercials' );
		
	    $view = $this->getView( 'commercials', 'html' );	
        $view->setModel( $model, true );
		$view->setLayout( 'default' );
		$view->display();
		
	}
	
	public function add() {
		
		$model = $this->getModel( 'commercials' );
		
	    $view = $this->getView( 'commercials' , 'html' );
        $view->setModel( $model, true );
		$view->setLayout( 'add' );
		$view->add();
		
	}
	
	public function edit() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'commercials' );
		
	    $view = $this->getView( 'commercials' , 'html' );
        $view->setModel( $model, true );
		$view->setLayout( 'edit' );
		$view->edit();
		
	}
	
	public function delete() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'commercials' );
	 	$model->delete();
		
	}
	
	public function save(){
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'commercials' );
	  	$model->save();
		
	}
	
	public function apply() {
		$this->save();
	}
	
	public function cancel() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'commercials' );
	    $model->cancel();
		
	}
	
	public function publish() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'commercials' );
        $model->publish();
		
    }
	
    public function unpublish() {
        $this->publish();
    }
		
}