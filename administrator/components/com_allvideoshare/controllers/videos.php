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
		
		$model = $this->getModel( 'videos' );
		
	    $view = $this->getView( 'videos', 'html' );		
        $view->setModel( $model, true );
		$view->setLayout( 'default' );
		$view->display();
		
	}
	
	public function add() {
		
		$model = $this->getModel( 'videos' );
		
	    $view = $this->getView( 'videos', 'html' );
        $view->setModel( $model, true );
		$view->setLayout( 'add' );
		$view->add();
		
	}
	
	public function edit() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'videos' );	
		
	    $view = $this->getView( 'videos', 'html' );
        $view->setModel( $model, true );
		$view->setLayout( 'edit' );
		$view->edit();
		
	}
	
	public function delete() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'videos' );
	 	$model->delete();
		
	}
	
	public function save() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'videos' );
	  	$model->save();
		
	}
	
	public function apply() {
		$this->save();
	}
	
	public function cancel() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'videos' );
	    $model->cancel();
		
	}
	
	public function publish() {
	
		AllVideoShareUtils::checkToken();
		
		$model = $this->getModel( 'videos' );
        $model->publish();
		
    }
	
    public function unpublish() {
        $this->publish();
    }
	
	public function saveorder() {
	
		$model = $this->getModel( 'videos' );
	  	$model->saveOrder();	
			
	}
	
	public function orderup() {
	
		$model = $this->getModel( 'videos' );
	  	$model->move( -1 );
				
	}
	
	public function orderdown() {
	
		$model = $this->getModel( 'videos' );
	  	$model->move( 1 );	
			
	}
		
}